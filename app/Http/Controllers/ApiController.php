<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
class ApiController extends Controller
{
    /**
     * Return menu JSON per PRD Section 4 interface contract.
     */
    public function menu(): JsonResponse
    {
        $categories = Category::with(['menuItems' => function ($q) {
            $q->with(['variants', 'modifierGroups.options'])
                ->orderBy('sort_order')
                ->orderBy('id');
        }])->orderBy('sort_order')->get();
        $chickenCuts = ChickenCut::orderBy('sort_order')->get();
        $drinkOptions = DrinkOption::orderBy('sort_order')->get();
        $formattedCategories = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'items' => $cat->menuItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price,
                        'formatted_price' => $item->formatted_price,
                        'image' => $item->image_url,
                        'is_available' => (bool) $item->is_available,
                        'is_best_seller' => (bool) $item->is_best_seller,
                        'is_package' => (bool) $item->is_package,
                        'variants' => $item->variants->map(fn ($variant) => [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'price' => $variant->price,
                            'formatted_price' => 'Rp' . number_format($variant->price, 0, ',', '.'),
                            'is_available' => (bool) $variant->is_available,
                        ])->values(),
                        'modifier_groups' => $item->modifierGroups->where('is_active', true)->map(fn ($group) => [
                            'id' => $group->id,
                            'name' => $group->name,
                            'is_required' => (bool) ($group->pivot->is_required ?? $group->is_required),
                            'min_selections' => $group->pivot->min_selections ?? $group->min_selections,
                            'max_selections' => $group->pivot->max_selections ?? $group->max_selections,
                            'options' => $group->options->where('is_available', true)->map(fn ($option) => [
                                'id' => $option->id,
                                'name' => $option->name,
                                'price_delta' => $option->price_delta,
                            ])->values(),
                        ])->values(),
                    ];
                }),
            ];
        });
        return response()->json([
            'categories' => $formattedCategories,
            'chicken_cuts' => $chickenCuts->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'is_available' => (bool) $c->is_available,
            ]),
            'drink_options' => $drinkOptions->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'is_available' => (bool) $d->is_available,
            ]),
        ]);
    }
    /**
     * Return store settings JSON per PRD Section 4 interface contract.
     */
    public function settings(): JsonResponse
    {
        $s = StoreSetting::current();
        return response()->json([
            'is_open' => (bool) $s->is_open,
            'wa_number' => $s->wa_number,
            'address' => $s->address,
            'opening_hours' => $s->opening_hours,
            'packaging_fee' => (int) $s->packaging_fee,
            'maps_url' => $s->maps_url,
            'review_url' => $s->review_url,
            'store_lat' => (float) $s->store_lat,
            'store_lng' => (float) $s->store_lng,
            'delivery_enabled' => (bool) $s->delivery_enabled,
            'delivery_radius_m' => (int) $s->delivery_radius_m,
            'qris_enabled' => (bool) $s->qris_enabled,
            'qris_merchant_name' => $s->qris_merchant_name,
            'qris_instructions' => $s->qris_instructions,
        ]);
    }
}
