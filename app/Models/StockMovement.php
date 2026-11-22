<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class StockMovement extends Model
{
    protected $fillable = [
        'raw_material_id',
        'user_id',
        'movement_type',
        'quantity_delta',
        'balance_after',
        'unit',
        'reason',
        'reference_type',
        'reference_id',
        'idempotency_key',
        'metadata',
    ];
    protected $casts = [
        'quantity_delta' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'metadata' => 'array',
    ];
    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
