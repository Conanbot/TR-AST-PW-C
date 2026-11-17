<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $table = 'store_settings';

    protected $fillable = [
        'is_open',
        'wa_number',
        'address',
        'opening_hours',
        'packaging_fee',
        'maps_url',
        'review_url',
        'store_lat',
        'store_lng',
        'delivery_enabled',
        'delivery_radius_m',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'delivery_enabled' => 'boolean',
        'packaging_fee' => 'integer',
        'delivery_radius_m' => 'integer',
        'store_lat' => 'float',
        'store_lng' => 'float',
    ];

    /**
     * Get or create the singleton instance of store settings.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'is_open' => true,
            'wa_number' => '6281268177729',
            'address' => 'Jl. Fatmawati, Blotongan, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50715',
            'opening_hours' => 'Setiap Hari: 10:00 - 21:00 WIB',
            'packaging_fee' => 1000,
            'maps_url' => 'https://maps.app.goo.gl/geprekglo',
            'review_url' => 'https://g.page/r/geprekglo/review',
            'store_lat' => -7.3117469,
            'store_lng' => 110.4851234,
            'delivery_enabled' => false,
            'delivery_radius_m' => 800,
        ]);
    }
}
