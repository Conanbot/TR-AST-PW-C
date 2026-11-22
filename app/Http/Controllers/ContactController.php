<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;

use Illuminate\View\View;

class ContactController extends Controller

{

    public function index(): View

    {

        $settings = StoreSetting::current();

        return view('kontak', compact('settings'));

    }

}

