<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class GoodsReceivingItem extends Model
{
    protected $fillable = [
        'goods_receiving_id',
        'purchase_order_item_id',
        'ordered_quantity',
        'previously_received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'purchase_unit',
        'conversion_factor',
        'actual_unit_price',
        'notes',
    ];
    protected $casts = [
        'ordered_quantity' => 'decimal:4',
        'previously_received_quantity' => 'decimal:4',
        'accepted_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'conversion_factor' => 'decimal:4',
        'actual_unit_price' => 'integer',
    ];
    public function receiving(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiving::class, 'goods_receiving_id');
    }
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
