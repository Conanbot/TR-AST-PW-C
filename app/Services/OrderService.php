<?php
namespace App\Services;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\PaymentService;
class OrderService
{
    public function create(array $data, ?User $customer, ?User $cashier, string $source): Order
    {
        if (!empty($data['checkout_key'])) {
            $existing = Order::where('checkout_key', $data['checkout_key'])->first();
            if ($existing) {
                return $existing->load(['items.modifiers', 'payments']);
            }
        }
        $settings = StoreSetting::current();
        $items = MenuItem::with(['variants', 'modifierGroups.options'])
            ->whereIn('id', collect($data['items'])->pluck('id')->unique())
            ->get()
            ->keyBy('id');
        if ($source === 'online' && !$settings->is_open) {
            throw ValidationException::withMessages([
                'items' => 'Kedai sedang tutup dan belum dapat menerima pesanan.',
            ]);
        }
        if ($data['service_method'] === 'delivery' && !$settings->delivery_enabled) {
            throw ValidationException::withMessages([
                'service_method' => 'Layanan pesan antar sedang tidak tersedia.',
            ]);
        }
        $paymentMethod = $data['payment_method'] ?? null;
        if (!$paymentMethod) {
            throw ValidationException::withMessages([
                'payment_method' => 'Pilih metode pembayaran.',
            ]);
        }
        $method = \App\Models\PaymentMethod::active()->where('code', $paymentMethod)->first();
        if (!$method) {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran tidak tersedia.',
            ]);
        }
        if ($paymentMethod === 'qris') {
            if (!$settings->qris_enabled || !$settings->qris_image || !$settings->qris_merchant_name) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Pembayaran QRIS belum tersedia.',
                ]);
            }
        }
        if ($paymentMethod === 'cod') {
            if ($data['service_method'] !== 'delivery') {
                throw ValidationException::withMessages([
                    'payment_method' => 'Cash on Delivery hanya tersedia untuk pesanan delivery.',
                ]);
            }
            $codOrder = new Order([
                'service_method' => 'delivery',
                'customer_address' => $data['customer_address'] ?? null,
                'delivery_lat' => $data['delivery_lat'] ?? null,
                'delivery_lng' => $data['delivery_lng'] ?? null,
            ]);
            app(PaymentService::class)->validateCodRadius($codOrder, $settings);
        }
        if ($paymentMethod === 'cash_pickup' && !in_array($data['service_method'], ['takeaway', 'dine_in'], true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Cash on Pickup hanya tersedia untuk pesanan pickup atau dine-in.',
            ]);
        }
        $cart = $this->calculateCart($data['items']);
        $subtotal = $cart['subtotal'];
        $rows = $cart['rows'];
        $packagingFee = $data['service_method'] === 'takeaway'
            ? (int) $settings->packaging_fee
            : 0;
        $discountAmount = 0;
        $deliveryFee = 0;
        $total = $subtotal + $packagingFee + $deliveryFee - $discountAmount;
        return DB::transaction(function () use ($data, $customer, $cashier, $source, $subtotal, $packagingFee, $discountAmount, $deliveryFee, $total, $rows) {
            $customerRecord = app(\App\Services\CustomerService::class)->resolveCustomerForOrder($data, $customer);
            $order = Order::create([
                'customer_id' => $customerRecord?->id,
                'user_id' => $customer?->id,
                'cashier_id' => $cashier?->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'service_method' => $data['service_method'],
                'table_number' => $data['table_number'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'delivery_lat' => $data['delivery_lat'] ?? null,
                'delivery_lng' => $data['delivery_lng'] ?? null,
                'order_notes' => $data['order_notes'] ?? null,
                'subtotal' => $subtotal,
                'packaging_fee' => $packagingFee,
                'discount_amount' => $discountAmount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? null,
                'promo_code' => $data['promo_code'] ?? null,
                'source' => $source,
            ]);
            $order->update([
                'order_number' => 'GLO-' . now()->format('ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'checkout_key' => $data['checkout_key'] ?? null,
            ]);
            foreach ($rows as $row) {
                $modifiers = $row['modifiers'];
                unset($row['modifiers']);
                $orderItem = $order->items()->create($row);
                foreach ($modifiers as $option) {
                    $orderItem->modifiers()->create([
                        'modifier_group_id' => $option->group->id,
                        'modifier_option_id' => $option->id,
                        'group_name' => $option->group->name,
                        'option_name' => $option->name,
                        'price_delta' => $option->price_delta,
                    ]);
                }
            }
            app(\App\Services\PromotionService::class)->applyToOrder(
                $order,
                $data['promo_code'] ?? null,
                $customer
            );
            if (!empty($data['payment_method'])) {
                app(PaymentService::class)->createForOrder($order, $data['payment_method']);
            }
            return $order->load(['items.modifiers', 'payments']);
        });
    }
    public function calculateCart(array $items): array
    {
        $menuItems = MenuItem::with(['variants', 'modifierGroups.options'])
            ->whereIn('id', collect($items)->pluck('id')->unique())
            ->get()
            ->keyBy('id');
        $subtotal = 0;
        $rows = [];
        foreach ($items as $entry) {
            $menuItem = $menuItems->get((int) $entry['id']);
            if (!$menuItem || !$menuItem->is_available) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu menu sudah tidak tersedia. Muat ulang menu lalu coba lagi.',
                ]);
            }
            $variant = null;
            if (!empty($entry['variant_id'])) {
                $variant = $menuItem->variants->firstWhere('id', (int) $entry['variant_id']);
                if (!$variant || !$variant->is_available) {
                    throw ValidationException::withMessages([
                        'items' => "Varian {$menuItem->name} sudah tidak tersedia.",
                    ]);
                }
            }
            $basePrice = $variant?->price ?? $menuItem->price;
            $selectedOptions = $this->validateModifiers($menuItem, $this->selectedOptionIds($entry));
            $modifierTotal = (int) $selectedOptions->sum('price_delta');
            $unitPrice = $basePrice + $modifierTotal;
            $lineTotal = $unitPrice * (int) $entry['qty'];
            $subtotal += $lineTotal;
            $rows[] = [
                'menu_item_id' => $menuItem->id,
                'variant_id' => $variant?->id,
                'item_name' => $menuItem->name,
                'variant_name' => $variant?->name,
                'quantity' => (int) $entry['qty'],
                'unit_price' => $unitPrice,
                'base_price' => $basePrice,
                'modifier_total' => $modifierTotal,
                'line_total' => $lineTotal,
                'chicken_cut' => $entry['cut'] ?? null,
                'drink_option' => $entry['drink'] ?? null,
                'note' => $entry['note'] ?? null,
                'modifiers' => $selectedOptions,
            ];
        }
        return ['subtotal' => $subtotal, 'rows' => $rows];
    }
    public function whatsappUrl(Order $order, ?string $trackingUrl = null): string
    {
        $settings = StoreSetting::current();
        $lines = $order->items->map(function ($item, $index) {
            $details = collect([
                $item->variant_name ? 'Varian: ' . $item->variant_name : null,
                $item->chicken_cut ? 'Potongan: ' . $item->chicken_cut : null,
                $item->drink_option ? 'Minuman: ' . $item->drink_option : null,
                $item->modifiers->isNotEmpty()
                    ? 'Pilihan: ' . $item->modifiers->map(fn ($modifier) => $modifier->option_name)->implode(', ')
                    : null,
            ])->filter()->implode('; ');
            return ($index + 1) . '. ' . $item->item_name . ' x' . $item->quantity
                . ($details ? ' (' . $details . ')' : '')
                . ' - Rp' . number_format($item->line_total, 0, ',', '.')
                . ($item->note ? "\n   Catatan: {$item->note}" : '');
        });
        $service = match ($order->service_method) {
            'dine_in' => 'Makan di tempat',
            'takeaway' => 'Bawa pulang',
            default => 'Pesan antar',
        };
        $message = "Halo Geprek GLO, saya ingin memesan:\n\n" . $lines->implode("\n")
            . "\n\nPESANAN BARU — AYAM GEPREK GLO BLOTONGAN"
            . "\nNomor order: {$order->order_number}"
            . "\nLayanan: {$service}"
            . ($order->table_number ? "\nNomor Meja: {$order->table_number}" : '')
            . "\nNama pemesan: {$order->customer_name}"
            . ($order->customer_phone ? "\nTelepon: {$order->customer_phone}" : '')
            . ($order->order_notes ? "\nCatatan Khusus: {$order->order_notes}" : '')
            . ($order->customer_address ? "\nAlamat pengantaran: {$order->customer_address}" : '')
            . "\nSubtotal: Rp" . number_format($order->subtotal, 0, ',', '.')
            . ($order->promo_code ? "\nPromo: {$order->promo_code}" : '')
            . ($order->discount_amount > 0 ? "\nDiskon: -Rp" . number_format($order->discount_amount, 0, ',', '.') : '')
            . ($order->packaging_fee > 0 ? "\nBiaya kemasan: Rp" . number_format($order->packaging_fee, 0, ',', '.') : '')
            . ($order->delivery_fee > 0 ? "\nBiaya antar: Rp" . number_format($order->delivery_fee, 0, ',', '.') : '')
            . "\nTotal: Rp" . number_format($order->total, 0, ',', '.')
            . "\nPembayaran: " . $this->paymentLabel($order->payment_method)
            . ($trackingUrl ? "\nLacak pesanan: " . $trackingUrl : '')
            . "\n\nMohon konfirmasi pesanan saya. Terima kasih!";
        return 'https://wa.me/' . $settings->wa_number . '?text=' . urlencode($message);
    }
    private function paymentLabel(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cash_pickup' => 'Cash on Pickup',
            'qris' => 'QRIS',
            'cod' => 'Cash on Delivery',
            default => 'Belum dipilih',
        };
    }
    private function selectedOptionIds(array $entry): array
    {
        if (isset($entry['modifier_option_ids'])) {
            return array_values(array_unique(array_map('intval', $entry['modifier_option_ids'])));
        }
        return collect($entry['modifiers'] ?? [])
            ->map(fn ($modifier) => is_array($modifier) ? ($modifier['option_id'] ?? null) : $modifier)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
    private function validateModifiers(MenuItem $menuItem, array $selectedOptionIds)
    {
        $groups = $menuItem->modifierGroups->where('is_active', true)->keyBy('id');
        $options = $groups->flatMap(fn ($group) => $group->options->where('is_available', true))
            ->keyBy('id');
        $selectedOptions = collect($selectedOptionIds)->map(fn ($id) => $options->get($id));
        if ($selectedOptions->contains(fn ($option) => !$option)) {
            throw ValidationException::withMessages([
                'items' => "Pilihan kustomisasi untuk {$menuItem->name} tidak valid.",
            ]);
        }
        foreach ($groups as $group) {
            $selectedForGroup = $selectedOptions->filter(fn ($option) => $option->modifier_group_id === $group->id);
            $pivot = $group->pivot;
            $required = $pivot->is_required ?? $group->is_required;
            $minimum = $pivot->min_selections ?? $group->min_selections;
            $maximum = $pivot->max_selections ?? $group->max_selections;
            if ($required && $minimum === 0) {
                $minimum = 1;
            }
            if ($selectedForGroup->count() < $minimum || ($maximum !== null && $selectedForGroup->count() > $maximum)) {
                throw ValidationException::withMessages([
                    'items' => "Pilihan untuk grup {$group->name} pada {$menuItem->name} tidak memenuhi aturan.",
                ]);
            }
        }
        return $selectedOptions->values();
    }
}
