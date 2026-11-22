<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Recipe extends Model
{
    protected $fillable = ['menu_item_id', 'raw_material_id', 'quantity', 'unit'];
    protected $casts = ['quantity' => 'decimal:4'];
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
