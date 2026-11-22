<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;

use Illuminate\View\View;

class ProfileController extends Controller

{

    public function index(): View

    {

        $settings = StoreSetting::current();

        return view('profil', compact('settings'));

    }

}

