<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        $menuItems = MenuItem::with('category')->orderBy('category_id')->orderBy('sort_order')->get();
        $chickenCuts = ChickenCut::orderBy('sort_order')->get();
        $drinkOptions = DrinkOption::orderBy('sort_order')->get();

        return view('admin.stock.index', compact('menuItems', 'chickenCuts', 'drinkOptions'));
    }

    public function toggleMenuItem(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $item = MenuItem::findOrFail($id);
        $item->is_available = !$item->is_available;
        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => $item->id,
                'is_available' => (bool) $item->is_available,
                'message' => 'Status stok menu ' . $item->name . ' diperbarui.',
            ]);
        }

        return back()->with('success', 'Status menu ' . $item->name . ' berhasil diperbarui.');
    }

    public function toggleChickenCut(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $cut = ChickenCut::findOrFail($id);
        $cut->is_available = !$cut->is_available;
        $cut->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => $cut->id,
                'is_available' => (bool) $cut->is_available,
                'message' => 'Status potongan ' . $cut->name . ' diperbarui.',
            ]);
        }

        return back()->with('success', 'Status potongan ' . $cut->name . ' berhasil diperbarui.');
    }

    public function toggleDrinkOption(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $drink = DrinkOption::findOrFail($id);
        $drink->is_available = !$drink->is_available;
        $drink->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => $drink->id,
                'is_available' => (bool) $drink->is_available,
                'message' => 'Status minuman ' . $drink->name . ' diperbarui.',
            ]);
        }

        return back()->with('success', 'Status minuman ' . $drink->name . ' berhasil diperbarui.');
    }
}
