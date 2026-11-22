<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
class Promo extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'code',
        'discount_type',
        'discount_value',
        'minimum_subtotal',
        'maximum_discount',
        'usage_limit',
        'per_customer_limit',
        'stackable',
        'image',
        'is_active',
        'start_date',
        'end_date',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_value' => 'integer',
        'minimum_subtotal' => 'integer',
        'maximum_discount' => 'integer',
        'usage_limit' => 'integer',
        'per_customer_limit' => 'integer',
        'stackable' => 'boolean',
    ];
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today();
        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            });
    }
    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && file_exists(public_path('assets/img/' . $this->image))) {
            return asset('assets/img/' . $this->image);
        }
        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->url($this->image);
        }
        return null;
    }
}
