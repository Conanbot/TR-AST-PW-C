<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ModifierGroup extends Model

{

    use HasFactory;

    protected $fillable = [

        'name',

        'slug',

        'is_required',

        'min_selections',

        'max_selections',

        'is_active',

        'sort_order',

    ];

    protected $casts = [

        'is_required' => 'boolean',

        'min_selections' => 'integer',

        'max_selections' => 'integer',

        'is_active' => 'boolean',

        'sort_order' => 'integer',

    ];

    public function options(): HasMany

    {

        return $this->hasMany(ModifierOption::class)->orderBy('sort_order')->orderBy('id');

    }

    public function menuItems(): BelongsToMany

    {

        return $this->belongsToMany(MenuItem::class)

            ->withPivot(['is_required', 'min_selections', 'max_selections', 'sort_order'])

            ->withTimestamps()

            ->orderByPivot('sort_order');

    }

    public function scopeActive(Builder $query): Builder

    {

        return $query->where('is_active', true);

    }

}

