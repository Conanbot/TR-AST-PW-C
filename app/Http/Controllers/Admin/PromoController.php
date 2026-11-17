<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoRequest;
use App\Models\Promo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PromoController extends Controller
{
    public function index(): View
    {
        $promos = Promo::latest()->paginate(10);

        return view('admin.promos.index', compact('promos'));
    }

    public function create(): View
    {
        return view('admin.promos.create');
    }

    public function store(PromoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('promos', 'public');
            $data['image'] = $path;
        }

        Promo::create($data);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Banner promo berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $promo = Promo::findOrFail($id);

        return view('admin.promos.edit', compact('promo'));
    }

    public function update(PromoRequest $request, int $id): RedirectResponse
    {
        $promo = Promo::findOrFail($id);
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($promo->image && Storage::disk('public')->exists($promo->image)) {
                Storage::disk('public')->delete($promo->image);
            }
            $path = $request->file('image')->store('promos', 'public');
            $data['image'] = $path;
        } elseif ($request->boolean('remove_image')) {
            if ($promo->image && Storage::disk('public')->exists($promo->image)) {
                Storage::disk('public')->delete($promo->image);
            }
            $data['image'] = null;
        }

        $promo->update($data);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Banner promo berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $promo = Promo::findOrFail($id);

        if ($promo->image && Storage::disk('public')->exists($promo->image)) {
            Storage::disk('public')->delete($promo->image);
        }

        $promo->delete();

        return redirect()->route('admin.promos.index')
            ->with('success', 'Banner promo berhasil dihapus.');
    }
}
