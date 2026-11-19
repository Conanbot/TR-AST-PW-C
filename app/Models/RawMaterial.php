<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    protected $fillable = [
        'name',
        'category',
        'sku',
        'purchase_unit',
        'usage_unit',
        'conversion_factor',
        'current_stock',
        'minimum_stock',
        'cost_per_purchase_unit',
        'is_active',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'current_stock' => 'decimal:4',
        'minimum_stock' => 'decimal:4',
        'cost_per_purchase_unit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function modifierRecipes(): HasMany
    {
        return $this->hasMany(ModifierOptionRecipe::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return (float) $this->current_stock <= (float) $this->minimum_stock;
    }
}
