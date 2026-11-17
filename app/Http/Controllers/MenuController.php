<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
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
            $q->orderBy('sort_order')->orderBy('id');
        }])->orderBy('sort_order')->get();

        $chickenCuts = ChickenCut::orderBy('sort_order')->get();
        $drinkOptions = DrinkOption::orderBy('sort_order')->get();

        return view('menu', compact('settings', 'categories', 'chickenCuts', 'drinkOptions'));
    }
}
