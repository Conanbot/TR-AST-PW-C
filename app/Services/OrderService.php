<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ValidationException;

class OrderService
{
    public function create(array $data, ?User $customer, ?User $cashier, string $source): Order
    {
        $settings = StoreSetting::current();
        $items = MenuItem::whereIn('id', collect($data['items'])->pluck('id'))->get()->keyBy('id');

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
            $menuItem = $items->get($entry['id']);
            if (!$menuItem || !$menuItem->is_available) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu menu sudah tidak tersedia. Muat ulang menu lalu coba lagi.',
                ]);
            }

            $lineTotal = $menuItem->price * $entry['qty'];
            $subtotal += $lineTotal;
            $rows[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'quantity' => $entry['qty'],
                'unit_price' => $menuItem->price,
                'line_total' => $lineTotal,
                'chicken_cut' => $entry['cut'] ?? null,
                'drink_option' => $entry['drink'] ?? null,
                'note' => $entry['note'] ?? null,
            ];
        }

        $packagingFee = $data['service_method'] === 'takeaway'
            ? $settings->packaging_fee
            : 0;

        return DB::transaction(function () use ($data, $customer, $cashier, $source, $subtotal, $packagingFee, $rows) {
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
                'total' => $subtotal + $packagingFee,
                'kitchen_status' => 'queued',
                'source' => $source,
            ]);

            $order->update([
                'order_number' => 'GLO-' . now()->format('ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
            ]);
            $order->items()->createMany($rows);

            return $order->load('items');
        });
    }

    public function whatsappUrl(Order $order): string
    {
        $settings = StoreSetting::current();
        $lines = $order->items->map(function ($item, $index) {
            $details = collect([
                $item->chicken_cut ? 'Potongan: ' . $item->chicken_cut : null,
                $item->drink_option ? 'Minuman: ' . $item->drink_option : null,
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
            . ($order->packaging_fee > 0 ? "\nBiaya kemasan: Rp" . number_format($order->packaging_fee, 0, ',', '.') : '')
            . "\nTotal: Rp" . number_format($order->total, 0, ',', '.')
            . "\n\nMohon konfirmasi pesanan saya. Terima kasih!";

        return 'https://wa.me/' . $settings->wa_number . '?text=' . urlencode($message);
    }
}
