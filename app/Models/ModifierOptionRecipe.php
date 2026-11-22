<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ModifierOptionRecipe extends Model
{
    protected $fillable = ['modifier_option_id', 'raw_material_id', 'quantity', 'unit'];
    protected $casts = ['quantity' => 'decimal:4'];
    public function modifierOption(): BelongsTo
    {
        return $this->belongsTo(ModifierOption::class);
    }
    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
