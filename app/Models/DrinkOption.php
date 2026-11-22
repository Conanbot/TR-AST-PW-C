<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DrinkOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'is_available',
        'sort_order',
    ];
    protected $casts = [
        'is_available' => 'boolean',
        'sort_order' => 'integer',
    ];
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }
}
