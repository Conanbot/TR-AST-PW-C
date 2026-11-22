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
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
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
     * Pick the best-matching existing menu image based on item name.
     */
    public static function fallbackImageForName(?string $name): string
    {
        $n = mb_strtolower(trim((string) $name));
        $rules = [
            ['keywords' => ['super komplit'], 'file' => 'menu/paket_super_komplit.jpg'],
            ['keywords' => ['geprek komplit'], 'file' => 'menu/paket_geprek_komplit.jpg'],
            ['keywords' => ['geprek telur', 'telur'], 'file' => 'menu/paket_geprek_telur.jpg'],
            ['keywords' => ['pagepin'], 'file' => 'menu/pagepin.jpg'],
            ['keywords' => ['kombo hebat', 'combo hebat'], 'file' => 'menu/combo_hebat.jpg'],
            ['keywords' => ['kombo oren', 'combo oren'], 'file' => 'menu/kombo_oren.jpg'],
            ['keywords' => ['combo choco', 'kombo choco'], 'file' => 'menu/combo_choco.jpg'],
            ['keywords' => ['geprek bakar'], 'file' => 'menu/ayam_geprek_bakar.jpg'],
            ['keywords' => ['crispy bakar', 'bakar'], 'file' => 'menu/ayam_crispy_bakar_spesial.jpg'],
            ['keywords' => ['geprek'], 'file' => 'menu/ayam_geprek_cabai_segar.jpg'],
            ['keywords' => ['crispy', 'krispi', 'ayam'], 'file' => 'menu/ayam_crispy_original.jpg'],
            ['keywords' => ['ceker rempah'], 'file' => 'menu/sup_ceker_ayam_rempah.jpg'],
            ['keywords' => ['ceker'], 'file' => 'menu/sup_ceker_ayam_gurih.jpg'],
            ['keywords' => ['sayur'], 'file' => 'menu/sup_sayur_hangat.jpg'],
            ['keywords' => ['sup', 'sop', 'kaldu'], 'file' => 'menu/sup_kaldu_ayam_segar.jpg'],
            ['keywords' => ['coklat', 'choco', 'glug'], 'file' => 'menu/es_coklat_glug.jpg'],
            ['keywords' => ['jeruk peras asli'], 'file' => 'menu/es_jeruk_peras_asli.jpg'],
            ['keywords' => ['jeruk', 'oren', 'orange'], 'file' => 'menu/es_jeruk_peras.jpg'],
            ['keywords' => ['kampul', 'lemon'], 'file' => 'menu/es_teh_kampul_segar.jpg'],
            ['keywords' => ['teh hangat', 'hangat'], 'file' => 'menu/teh_hangat_manis.jpg'],
            ['keywords' => ['teh jumbo', 'jumbo'], 'file' => 'menu/es_teh_jumbo.jpg'],
            ['keywords' => ['teh'], 'file' => 'menu/es_teh_manis.jpg'],
            ['keywords' => ['air', 'mineral', 'ades', 'aqua'], 'file' => 'menu/air_mineral_botol.jpg'],
            ['keywords' => ['minuman', 'es '], 'file' => 'menu/aneka_minuman.jpg'],
            ['keywords' => ['paket', 'combo', 'kombo'], 'file' => 'menu/combo_hemat.jpg'],
        ];
        foreach ($rules as $rule) {
            foreach ($rule['keywords'] as $kw) {
                if (str_contains($n, $kw) && file_exists(public_path('assets/img/' . $rule['file']))) {
                    return $rule['file'];
                }
            }
        }
        return 'menu/ayam_geprek_cabai_segar.jpg';
    }
    /**
     * Get the full URL to the image if exists and stored, with smart name-based fallback.
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
        $fallback = self::fallbackImageForName($this->name);
        if (file_exists(public_path('assets/img/' . $fallback))) {
            return asset('assets/img/' . $fallback);
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
