<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\MenuItemRequest;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = MenuItem::with('category');
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $menuItems = $query->orderBy('category_id')->orderBy('sort_order')->orderBy('id')->paginate(15);
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.menu.index', compact('menuItems', 'categories'));
    }
    public function create(): View
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.menu.create', compact('categories'));
    }
    public function store(MenuItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_available'] = $request->boolean('is_available', true);
        $data['is_best_seller'] = $request->boolean('is_best_seller', false);
        $data['is_package'] = $request->boolean('is_package', false);
        $data['sort_order'] = $request->input('sort_order', 0);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('menu', 'public');
            $data['image'] = $path;
        } else {
            $data['image'] = null; // Uses neutral placeholder component
        }
        MenuItem::create($data);
        return redirect()->route('admin.items.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }
    public function edit(int $id): View
    {
        $item = MenuItem::findOrFail($id);
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.menu.edit', compact('item', 'categories'));
    }
    public function update(MenuItemRequest $request, int $id): RedirectResponse
    {
        $item = MenuItem::findOrFail($id);
        $data = $request->validated();
        $data['is_available'] = $request->boolean('is_available');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_package'] = $request->boolean('is_package');
        $data['sort_order'] = $request->input('sort_order', 0);
        if ($request->hasFile('image')) {
            // Delete old file if present
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $file = $request->file('image');
            $path = $file->store('menu', 'public');
            $data['image'] = $path;
        } elseif ($request->boolean('remove_image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = null;
        }
        $item->update($data);
        return redirect()->route('admin.items.index')
            ->with('success', 'Data menu berhasil diperbarui.');
    }
    public function destroy(int $id): RedirectResponse
    {
        $item = MenuItem::findOrFail($id);
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }
        $item->delete();
        return redirect()->route('admin.items.index')
            ->with('success', 'Menu ' . $item->name . ' berhasil dihapus.');
    }
}
