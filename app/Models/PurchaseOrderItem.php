<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model

{

    protected $fillable = [

        'purchase_order_id',

        'raw_material_id',

        'material_name',

        'purchase_unit',

        'usage_unit',

        'conversion_factor',

        'ordered_quantity',

        'received_quantity',

        'rejected_quantity',

        'unit_price',

        'subtotal',

        'notes',

    ];

    protected $casts = [

        'conversion_factor' => 'decimal:4',

        'ordered_quantity' => 'decimal:4',

        'received_quantity' => 'decimal:4',

        'rejected_quantity' => 'decimal:4',

        'unit_price' => 'integer',

        'subtotal' => 'integer',

    ];

    public function purchaseOrder(): BelongsTo

    {

        return $this->belongsTo(PurchaseOrder::class);

    }

    public function rawMaterial(): BelongsTo

    {

        return $this->belongsTo(RawMaterial::class);

    }

    public function receivingItems(): HasMany

    {

        return $this->hasMany(GoodsReceivingItem::class);

    }

    public function getRemainingQuantityAttribute(): float

    {

        return (float) $this->ordered_quantity - (float) $this->received_quantity - (float) $this->rejected_quantity;

    }

}

