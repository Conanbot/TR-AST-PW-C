<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreSettingRequest;

use App\Models\StoreSetting;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\RedirectResponse;

use Illuminate\View\View;

class SettingController extends Controller

{

    public function edit(): View

    {

        $settings = StoreSetting::current();

        return view('admin.settings.edit', compact('settings'));

    }

    public function update(StoreSettingRequest $request): RedirectResponse

    {

        $settings = StoreSetting::current();

        $data = $request->validated();

        $data['is_open'] = $request->boolean('is_open');

        $data['delivery_enabled'] = $request->boolean('delivery_enabled');

        $settings->update($data);

        return redirect()->route('admin.settings.edit')

            ->with('success', 'Pengaturan kedai berhasil disimpan.');

    }

    public function toggleDelivery(): JsonResponse

    {

        $settings = StoreSetting::current();

        $newState = !$settings->delivery_enabled;

        $settings->update(['delivery_enabled' => $newState]);

        return response()->json([

            'delivery_enabled' => $newState,

            'message' => $newState

                ? 'Layanan Delivery (Pesan Antar) berhasil diaktifkan.'

                : 'Layanan Delivery (Pesan Antar) berhasil dinonaktifkan.',

        ]);

    }

}

