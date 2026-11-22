<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model

{

    protected $fillable = [

        'payment_reference',

        'order_id',

        'method_code',

        'amount',

        'status',

        'proof_image',

        'rejection_reason',

        'confirmed_by_user_id',

        'paid_at',

        'verified_at',

    ];

    protected $casts = [

        'amount' => 'integer',

        'paid_at' => 'datetime',

        'verified_at' => 'datetime',

    ];

    public function order(): BelongsTo

    {

        return $this->belongsTo(Order::class);

    }

    public function confirmedBy(): BelongsTo

    {

        return $this->belongsTo(User::class, 'confirmed_by_user_id');

    }

    public function histories(): HasMany

    {

        return $this->hasMany(PaymentVerificationHistory::class);

    }

}

