<?php
namespace App\Services;
use App\Models\GoodsReceiving;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class PurchaseReceivingService
{
    public function create(
        PurchaseOrder $purchaseOrder,
        array $rows,
        ?string $notes,
        ?User $receiver,
        ?string $idempotencyKey = null,
        ?string $receivedAt = null
    ): GoodsReceiving {
        return DB::transaction(function () use ($purchaseOrder, $rows, $notes, $receiver, $idempotencyKey, $receivedAt) {
            if ($idempotencyKey) {
                $existing = GoodsReceiving::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing->load(['items', 'purchaseOrder.items']);
                }
            }
            $lockedPo = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            if (in_array($lockedPo->status, ['draft', 'cancelled', 'received'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'Purchase order tidak dapat menerima barang pada status ini.',
                ]);
            }
            $receiving = GoodsReceiving::create([
                'receiving_number' => 'GRN-' . now()->format('ymd') . '-' . str_pad((string) (GoodsReceiving::count() + 1), 4, '0', STR_PAD_LEFT),
                'purchase_order_id' => $lockedPo->id,
                'received_by' => $receiver?->id,
                'received_at' => $receivedAt ?: now()->toDateString(),
                'notes' => $notes,
                'idempotency_key' => $idempotencyKey,
            ]);
            foreach ($rows as $row) {
                $poItem = PurchaseOrderItem::whereKey((int) $row['purchase_order_item_id'])
                    ->where('purchase_order_id', $lockedPo->id)
                    ->lockForUpdate()
                    ->first();
                if (!$poItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu item tidak ditemukan pada purchase order ini.',
                    ]);
                }
                $accepted = (float) ($row['accepted_quantity'] ?? 0);
                $rejected = (float) ($row['rejected_quantity'] ?? 0);
                $actualUnitPrice = isset($row['actual_unit_price']) && $row['actual_unit_price'] !== ''
                    ? (int) $row['actual_unit_price']
                    : null;
                if ($accepted < 0 || $rejected < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Kuantitas diterima dan ditolak tidak boleh negatif.',
                    ]);
                }
                if ($accepted + $rejected <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Setiap item receiving harus memiliki kuantitas diterima atau ditolak.',
                    ]);
                }
                $remaining = (float) $poItem->remaining_quantity;
                if ($accepted + $rejected > $remaining + 0.0001) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah diterima dan ditolak {$poItem->material_name} melebihi sisa pesanan.",
                    ]);
                }
                $previousReceived = (float) $poItem->received_quantity;
                $receiving->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'ordered_quantity' => $poItem->ordered_quantity,
                    'previously_received_quantity' => $previousReceived,
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                    'purchase_unit' => $poItem->purchase_unit,
                    'conversion_factor' => $poItem->conversion_factor,
                    'actual_unit_price' => $actualUnitPrice,
                    'notes' => $row['notes'] ?? null,
                ]);
                $poItem->update([
                    'received_quantity' => (float) $poItem->received_quantity + $accepted,
                    'rejected_quantity' => (float) $poItem->rejected_quantity + $rejected,
                ]);
                if ($accepted > 0) {
                    $material = RawMaterial::findOrFail($poItem->raw_material_id);
                    $usageQuantity = $accepted * (float) $poItem->conversion_factor;
                    app(InventoryService::class)->recordMovement(
                        $material,
                        'stock_in',
                        $usageQuantity,
                        "Penerimaan barang {$receiving->receiving_number}",
                        $receiver,
                        $receiving,
                        "receiving:{$receiving->id}:po-item:{$poItem->id}"
                    );
                }
            }
            $allSettled = !$lockedPo->items()
                ->get()
                ->contains(fn (PurchaseOrderItem $item) => (float) $item->remaining_quantity > 0.0001);
            $lockedPo->update([
                'status' => $allSettled ? 'received' : 'partially_received',
            ]);
            return $receiving->load(['items.purchaseOrderItem', 'purchaseOrder.items']);
        });
    }
}
