<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductConfigurationController extends Controller
{
    public function index(): View
    {
        return view('admin.product-config.index', [
            'groups' => ModifierGroup::with('options')->orderBy('sort_order')->orderBy('id')->get(),
            'variants' => ProductVariant::with('menuItem')->orderBy('menu_item_id')->orderBy('sort_order')->paginate(20),
            'menuItems' => MenuItem::with('modifierGroups')->orderBy('name')->get(),
        ]);
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        ModifierGroup::create($this->groupData($request));

        return back()->with('success', 'Modifier group berhasil ditambahkan.');
    }

    public function updateGroup(Request $request, ModifierGroup $group): RedirectResponse
    {
        $group->update($this->groupData($request));

        return back()->with('success', 'Modifier group berhasil diperbarui.');
    }

    public function destroyGroup(ModifierGroup $group): RedirectResponse
    {
        $group->update(['is_active' => false]);

        return back()->with('success', 'Modifier group dinonaktifkan.');
    }

    public function storeOption(Request $request, ModifierGroup $group): RedirectResponse
    {
        ModifierOption::create($this->optionData($request, $group));

        return back()->with('success', 'Pilihan modifier berhasil ditambahkan.');
    }

    public function updateOption(Request $request, ModifierOption $option): RedirectResponse
    {
        $option->update($this->optionData($request, $option->group));

        return back()->with('success', 'Pilihan modifier berhasil diperbarui.');
    }

    public function destroyOption(ModifierOption $option): RedirectResponse
    {
        $option->update(['is_available' => false]);

        return back()->with('success', 'Pilihan modifier dinonaktifkan.');
    }

    public function storeVariant(Request $request): RedirectResponse
    {
        ProductVariant::create($this->variantData($request));

        return back()->with('success', 'Varian produk berhasil ditambahkan.');
    }

    public function updateVariant(Request $request, ProductVariant $variant): RedirectResponse
    {
        $variant->update($this->variantData($request));

        return back()->with('success', 'Varian produk berhasil diperbarui.');
    }

    public function destroyVariant(ProductVariant $variant): RedirectResponse
    {
        $variant->update(['is_available' => false]);

        return back()->with('success', 'Varian produk dinonaktifkan.');
    }

    public function assignGroups(Request $request, MenuItem $item): RedirectResponse
    {
        $data = $request->validate([
            'modifier_groups' => ['nullable', 'array'],
            'modifier_groups.*.id' => ['required', 'integer', 'exists:modifier_groups,id'],
            'modifier_groups.*.is_required' => ['nullable', 'boolean'],
            'modifier_groups.*.min_selections' => ['nullable', 'integer', 'min:0', 'max:20'],
            'modifier_groups.*.max_selections' => ['nullable', 'integer', 'min:1', 'max:20'],
            'modifier_groups.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $pivot = collect($data['modifier_groups'] ?? [])
            ->mapWithKeys(function (array $selected, int $index) {
                $group = ModifierGroup::findOrFail($selected['id']);

                return [
                    $group->id => [
                        'is_required' => array_key_exists('is_required', $selected)
                            ? (bool) $selected['is_required']
                            : $group->is_required,
                        'min_selections' => $selected['min_selections'] ?? $group->min_selections,
                        'max_selections' => $selected['max_selections'] ?? $group->max_selections,
                        'sort_order' => $selected['sort_order'] ?? $index,
                    ],
                ];
            })->all();

        $item->modifierGroups()->sync($pivot);

        return back()->with('success', 'Konfigurasi modifier produk berhasil disimpan.');
    }

    private function groupData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', 'unique:modifier_groups,slug,' . optional($request->route('group'))->id],
            'is_required' => ['nullable', 'boolean'],
            'min_selections' => ['required', 'integer', 'min:0', 'max:20'],
            'max_selections' => ['nullable', 'integer', 'min:1', 'max:20', 'gte:min_selections'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function optionData(Request $request, ModifierGroup $group): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price_delta' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]) + [
            'modifier_group_id' => $group->id,
            'is_available' => $request->boolean('is_available', true),
        ];
    }

    private function variantData(Request $request): array
    {
        return $request->validate([
            'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:80', 'unique:product_variants,sku,' . optional($request->route('variant'))->id],
            'price' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]) + [
            'is_available' => $request->boolean('is_available', true),
        ];
    }
}
