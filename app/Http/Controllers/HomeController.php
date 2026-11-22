<?php

namespace App\Http\Controllers;

use App\Models\Category;

use App\Models\MenuItem;

use App\Models\Promo;

use App\Models\StoreSetting;

use Illuminate\View\View;

class HomeController extends Controller

{

    public function index(): View

    {

        $settings = StoreSetting::current();

        $promos = Promo::active()->latest()->take(3)->get();

        $featuredItems = MenuItem::available()

            ->bestSeller()

            ->with('category')

            ->orderBy('sort_order')

            ->take(6)

            ->get();

        // Fallback if no best sellers marked

        if ($featuredItems->isEmpty()) {

            $featuredItems = MenuItem::available()

                ->with('category')

                ->orderBy('sort_order')

                ->take(6)

                ->get();

        }

        $categories = Category::withCount(['menuItems' => function ($q) {

            $q->where('is_available', true);

        }])->orderBy('sort_order')->get();

        return view('home', compact('settings', 'promos', 'featuredItems', 'categories'));

    }

}

