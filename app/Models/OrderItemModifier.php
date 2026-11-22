<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends Model

{

    protected $fillable = [

        'order_item_id',

        'modifier_group_id',

        'modifier_option_id',

        'group_name',

        'option_name',

        'price_delta',

    ];

    protected $casts = [

        'price_delta' => 'integer',

    ];

    public function orderItem(): BelongsTo

    {

        return $this->belongsTo(OrderItem::class);

    }

    public function option(): BelongsTo

    {

        return $this->belongsTo(ModifierOption::class, 'modifier_option_id');

    }

}

