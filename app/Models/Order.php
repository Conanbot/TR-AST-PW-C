<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'cashier_id',
        'customer_name',
        'customer_phone',
        'service_method',
        'table_number',
        'customer_address',
        'order_notes',
        'staff_notes',
        'cancellation_reason',
        'subtotal',
        'packaging_fee',
        'discount_amount',
        'delivery_fee',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'kitchen_status',
        'source',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(OrderAccessToken::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp' . number_format($this->total, 0, ',', '.');
    }
}
