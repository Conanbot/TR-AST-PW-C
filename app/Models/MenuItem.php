<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
        'is_best_seller',
        'is_package',
        'sort_order',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'price' => 'integer',
        'is_available' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_package' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class)
            ->withPivot(['is_required', 'min_selections', 'max_selections', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeBestSeller(Builder $query): Builder
    {
        return $query->where('is_best_seller', true);
    }

    /**
     * Get the full URL to the image if exists and stored.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && file_exists(public_path('assets/img/' . $this->image))) {
            return asset('assets/img/' . $this->image);
        }

        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }

        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        return null;
    }

    /**
     * Format price to Rupiah string.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp' . number_format($this->price, 0, ',', '.');
    }
}
