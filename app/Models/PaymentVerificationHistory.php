<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVerificationHistory extends Model
{
    protected $fillable = [
        'payment_id',
        'actor_user_id',
        'action',
        'from_status',
        'to_status',
        'claimed_amount',
        'note',
    ];

    protected $casts = [
        'claimed_amount' => 'integer',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
