<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'purchase_date',
        'status',
        'notes',
        'subtotal',
        'total',
        'created_by',
    ];
    protected $casts = [
        'purchase_date' => 'date',
        'subtotal' => 'integer',
        'total' => 'integer',
    ];
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    public function receivings(): HasMany
    {
        return $this->hasMany(GoodsReceiving::class);
    }
}
