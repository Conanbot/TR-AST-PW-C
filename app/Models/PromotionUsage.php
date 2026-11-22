<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionUsage extends Model

{

    protected $fillable = [

        'promo_id',

        'order_id',

        'code',

        'discount_amount',

        'customer_user_id',

        'customer_phone',

        'status',

    ];

    protected $casts = ['discount_amount' => 'integer'];

    public function promo(): BelongsTo

    {

        return $this->belongsTo(Promo::class);

    }

    public function order(): BelongsTo

    {

        return $this->belongsTo(Order::class);

    }

    public function customer(): BelongsTo

    {

        return $this->belongsTo(User::class, 'customer_user_id');

    }

}

