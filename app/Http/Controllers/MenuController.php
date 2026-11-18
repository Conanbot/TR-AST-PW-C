<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $settings = StoreSetting::current();

        $categories = Category::with(['menuItems' => function ($q) {
            $q->with(['variants', 'modifierGroups.options'])
                ->orderBy('sort_order')
                ->orderBy('id');
        }])->orderBy('sort_order')->get();

        $activeCategory = (string) $request->query('category', 'all');

        if ($activeCategory !== 'all' && !$categories->contains('slug', $activeCategory)) {
            $activeCategory = 'all';
        }

        return view('menu', compact('settings', 'categories', 'activeCategory'));
    }
}
