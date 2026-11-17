<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
use App\Models\MenuItem;
use App\Models\Promo;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $settings = StoreSetting::current();

        $totalMenu = MenuItem::count();
        $availableMenu = MenuItem::where('is_available', true)->count();
        $outOfStockMenu = MenuItem::where('is_available', false)->count();
        $activePromos = Promo::where('is_active', true)->count();

        $outOfStockItems = MenuItem::where('is_available', false)
            ->with('category')
            ->orderBy('category_id')
            ->get();

        $chickenCuts = ChickenCut::orderBy('sort_order')->get();
        $drinkOptions = DrinkOption::orderBy('sort_order')->get();

        return view('admin.dashboard', compact(
            'settings',
            'totalMenu',
            'availableMenu',
            'outOfStockMenu',
            'activePromos',
            'outOfStockItems',
            'chickenCuts',
            'drinkOptions'
        ));
    }

    /**
     * One-tap toggle for store Open/Closed status.
     */
    public function toggleStore(Request $request): JsonResponse
    {
        $settings = StoreSetting::current();
        $settings->is_open = !$settings->is_open;
        $settings->save();

        return response()->json([
            'success' => true,
            'is_open' => (bool) $settings->is_open,
            'message' => $settings->is_open
                ? 'Status kedai berhasil diubah menjadi BUKA.'
                : 'Status kedai berhasil diubah menjadi TUTUP.',
        ]);
    }
}
