<?php
namespace App\Services;
use App\Models\RawMaterial;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class InventoryService
{
    public function consumeOrder(Order $order, ?User $user = null): void
    {
        $order->loadMissing(['items.modifiers', 'items.menuItem.recipes', 'items.modifiers.option.recipes']);
        $requirements = [];
        foreach ($order->items as $item) {
            foreach ($item->menuItem?->recipes ?? [] as $recipe) {
                $requirements[$recipe->raw_material_id] = ($requirements[$recipe->raw_material_id] ?? 0)
                    + ((float) $recipe->quantity * $item->quantity);
            }
            foreach ($item->modifiers as $modifier) {
                foreach ($modifier->option?->recipes ?? [] as $recipe) {
                    $requirements[$recipe->raw_material_id] = ($requirements[$recipe->raw_material_id] ?? 0)
                        + ((float) $recipe->quantity * $item->quantity);
                }
            }
        }
        foreach ($requirements as $rawMaterialId => $quantity) {
            $material = RawMaterial::findOrFail($rawMaterialId);
            $this->recordMovement(
                $material,
                'order_consumption',
                -$quantity,
                "Pemakaian order {$order->order_number}",
                $user,
                $order,
                "order:{$order->id}:material:{$rawMaterialId}"
            );
        }
    }
    public function recordMovement(
        RawMaterial $rawMaterial,
        string $movementType,
        float $quantityDelta,
        string $reason,
        ?User $user = null,
        ?Model $reference = null,
        ?string $idempotencyKey = null
    ): StockMovement {
        return DB::transaction(function () use (
            $rawMaterial,
            $movementType,
            $quantityDelta,
            $reason,
            $user,
            $reference,
            $idempotencyKey
        ) {
            if ($idempotencyKey) {
                $existing = StockMovement::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }
            $lockedMaterial = RawMaterial::whereKey($rawMaterial->id)->lockForUpdate()->firstOrFail();
            $newBalance = (float) $lockedMaterial->current_stock + $quantityDelta;
            if ($newBalance < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$lockedMaterial->name} tidak mencukupi.",
                ]);
            }
            $lockedMaterial->update(['current_stock' => $newBalance]);
            return StockMovement::create([
                'raw_material_id' => $lockedMaterial->id,
                'user_id' => $user?->id,
                'movement_type' => $movementType,
                'quantity_delta' => $quantityDelta,
                'balance_after' => $newBalance,
                'unit' => $lockedMaterial->usage_unit,
                'reason' => $reason,
                'reference_type' => $reference ? $reference->getMorphClass() : null,
                'reference_id' => $reference?->getKey(),
                'idempotency_key' => $idempotencyKey,
            ]);
        });
    }
}
