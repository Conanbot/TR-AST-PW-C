<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class OrderItem extends Model
{
    protected $fillable = [
        'menu_item_id',
        'item_name',
        'variant_id',
        'variant_name',
        'quantity',
        'unit_price',
        'base_price',
        'modifier_total',
        'line_total',
        'chicken_cut',
        'drink_option',
        'note',
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class);
    }
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
