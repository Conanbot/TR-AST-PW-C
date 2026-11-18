<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(array $data, ?User $customer, ?User $cashier, string $source): Order
    {
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

        $subtotal = 0;
        $rows = [];

        foreach ($data['items'] as $entry) {
            $menuItem = $items->get((int) $entry['id']);
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
            $selectedOptionIds = $this->selectedOptionIds($entry);
            $selectedOptions = $this->validateModifiers($menuItem, $selectedOptionIds);
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

        $packagingFee = $data['service_method'] === 'takeaway'
            ? (int) $settings->packaging_fee
            : 0;
        $discountAmount = 0;
        $deliveryFee = 0;
        $total = $subtotal + $packagingFee + $deliveryFee - $discountAmount;

        return DB::transaction(function () use ($data, $customer, $cashier, $source, $subtotal, $packagingFee, $discountAmount, $deliveryFee, $total, $rows) {
            $order = Order::create([
                'user_id' => $customer?->id,
                'cashier_id' => $cashier?->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'service_method' => $data['service_method'],
                'table_number' => $data['table_number'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'order_notes' => $data['order_notes'] ?? null,
                'subtotal' => $subtotal,
                'packaging_fee' => $packagingFee,
                'discount_amount' => $discountAmount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'source' => $source,
            ]);

            $order->update([
                'order_number' => 'GLO-' . now()->format('ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
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

            return $order->load('items.modifiers');
        });
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
            . "\n\nNomor pesanan: {$order->order_number}"
            . "\nLayanan: {$service}"
            . ($order->table_number ? "\nNomor Meja: {$order->table_number}" : '')
            . "\nNama pemesan: {$order->customer_name}"
            . ($order->order_notes ? "\nCatatan Khusus: {$order->order_notes}" : '')
            . ($order->customer_address ? "\nAlamat pengantaran: {$order->customer_address}" : '')
            . "\nSubtotal: Rp" . number_format($order->subtotal, 0, ',', '.')
            . ($order->discount_amount > 0 ? "\nDiskon: -Rp" . number_format($order->discount_amount, 0, ',', '.') : '')
            . ($order->packaging_fee > 0 ? "\nBiaya kemasan: Rp" . number_format($order->packaging_fee, 0, ',', '.') : '')
            . ($order->delivery_fee > 0 ? "\nBiaya antar: Rp" . number_format($order->delivery_fee, 0, ',', '.') : '')
            . "\nTotal: Rp" . number_format($order->total, 0, ',', '.')
            . ($trackingUrl ? "\nLacak pesanan: " . $trackingUrl : '')
            . "\n\nMohon konfirmasi pesanan saya. Terima kasih!";

        return 'https://wa.me/' . $settings->wa_number . '?text=' . urlencode($message);
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
