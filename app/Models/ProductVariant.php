<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model

{

    use HasFactory;

    protected $fillable = [

        'menu_item_id',

        'name',

        'sku',

        'price',

        'is_available',

        'sort_order',

    ];

    protected $casts = [

        'menu_item_id' => 'integer',

        'price' => 'integer',

        'is_available' => 'boolean',

        'sort_order' => 'integer',

    ];

    public function menuItem(): BelongsTo

    {

        return $this->belongsTo(MenuItem::class);

    }

    public function scopeAvailable(Builder $query): Builder

    {

        return $query->where('is_available', true);

    }

}

