<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\ModifierOption;
use App\Models\RawMaterial;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class InventoryController extends Controller
{
    public function index(): View
    {
        $materials = RawMaterial::withCount('stockMovements')->orderBy('name')->paginate(20);
        $movements = \App\Models\StockMovement::with(['rawMaterial', 'user'])->latest()->take(20)->get();
        return view('admin.inventory.index', compact('materials', 'movements'));
    }
    public function storeMaterial(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:raw_materials,sku'],
            'purchase_unit' => ['required', 'string', 'max:30'],
            'usage_unit' => ['required', 'string', 'max:30'],
            'conversion_factor' => ['required', 'numeric', 'gt:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'cost_per_purchase_unit' => ['required', 'integer', 'min:0'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        RawMaterial::create($data);
        return back()->with('success', 'Bahan baku berhasil ditambahkan.');
    }
    public function updateMaterial(Request $request, RawMaterial $rawMaterial): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:raw_materials,sku,' . $rawMaterial->id],
            'purchase_unit' => ['required', 'string', 'max:30'],
            'usage_unit' => ['required', 'string', 'max:30'],
            'conversion_factor' => ['required', 'numeric', 'gt:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'cost_per_purchase_unit' => ['required', 'integer', 'min:0'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $rawMaterial->update($data);
        return back()->with('success', 'Bahan baku berhasil diperbarui.');
    }
    public function recordMovement(Request $request, InventoryService $inventory): RedirectResponse
    {
        $data = $request->validate([
            'raw_material_id' => ['required', 'exists:raw_materials,id'],
            'movement_type' => ['required', 'in:stock_in,stock_out,adjustment'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        $delta = $data['movement_type'] === 'stock_out' ? -abs((float) $data['quantity']) : abs((float) $data['quantity']);
        $material = RawMaterial::findOrFail($data['raw_material_id']);
        $inventory->recordMovement($material, $data['movement_type'], $delta, $data['reason'], $request->user());
        return back()->with('success', 'Pergerakan stok berhasil dicatat.');
    }
    public function recipes(): View
    {
        $menuItems = MenuItem::with('recipes.rawMaterial')->orderBy('name')->get();
        $options = ModifierOption::with('group', 'recipes.rawMaterial')->orderBy('name')->get();
        $materials = RawMaterial::where('is_active', true)->orderBy('name')->get();
        return view('admin.inventory.recipes', compact('menuItems', 'options', 'materials'));
    }
    public function storeRecipe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'raw_material_id' => ['required', 'exists:raw_materials,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:30'],
        ]);
        \App\Models\Recipe::updateOrCreate(
            ['menu_item_id' => $data['menu_item_id'], 'raw_material_id' => $data['raw_material_id']],
            ['quantity' => $data['quantity'], 'unit' => $data['unit']]
        );
        return back()->with('success', 'Resep produk berhasil disimpan.');
    }
    public function storeModifierRecipe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'modifier_option_id' => ['required', 'exists:modifier_options,id'],
            'raw_material_id' => ['required', 'exists:raw_materials,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:30'],
        ]);
        \App\Models\ModifierOptionRecipe::updateOrCreate(
            ['modifier_option_id' => $data['modifier_option_id'], 'raw_material_id' => $data['raw_material_id']],
            ['quantity' => $data['quantity'], 'unit' => $data['unit']]
        );
        return back()->with('success', 'Resep modifier berhasil disimpan.');
    }
    public function destroyRecipe(string $type, int $id): RedirectResponse
    {
        abort_unless(in_array($type, ['product', 'modifier'], true), 404);
        $model = $type === 'product' ? \App\Models\Recipe::findOrFail($id) : \App\Models\ModifierOptionRecipe::findOrFail($id);
        $model->delete();
        return back()->with('success', 'Baris resep berhasil dihapus.');
    }
}
