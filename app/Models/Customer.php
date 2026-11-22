<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model

{

    use HasFactory;

    protected $fillable = [

        'name',

        'phone',

        'normalized_phone',

        'email',

        'notes',

        'status',

    ];

    protected $casts = [

        'created_at' => 'datetime',

        'updated_at' => 'datetime',

    ];

    public function orders(): HasMany

    {

        return $this->hasMany(Order::class);

    }

    public function completedOrders(): HasMany

    {

        return $this->hasMany(Order::class)->where('status', 'completed');

    }

    public function cancelledOrders(): HasMany

    {

        return $this->hasMany(Order::class)->where('status', 'cancelled');

    }

    public function successfulPaidOrders(): HasMany

    {

        return $this->hasMany(Order::class)

            ->where('status', '!=', 'cancelled')

            ->where(function ($query) {

                $query->where('payment_status', 'paid')

                    ->orWhere('status', 'completed');

            });

    }

    public static function normalizePhone(?string $phone): ?string

    {

        if ($phone === null) {

            return null;

        }

        $trimmed = trim($phone);

        if ($trimmed === '') {

            return null;

        }

        // Strip non-digits

        $digits = preg_replace('/\D+/', '', $trimmed);

        if ($digits === '' || strlen($digits) < 8 || strlen($digits) > 16) {

            return null;

        }

        // Normalize Indonesian mobile prefix

        if (str_starts_with($digits, '0')) {

            $digits = '62' . substr($digits, 1);

        } elseif (str_starts_with($digits, '8')) {

            $digits = '62' . $digits;

        }

        return $digits;

    }

    public function getFormattedPhoneAttribute(): string

    {

        return $this->phone ?: ($this->normalized_phone ?: '-');

    }

    public function getFormattedTotalSpendingAttribute(): string

    {

        $amount = isset($this->total_spending)

            ? (int) $this->total_spending

            : (int) $this->successfulPaidOrders()->sum('total');

        return 'Rp' . number_format($amount, 0, ',', '.');

    }

}

