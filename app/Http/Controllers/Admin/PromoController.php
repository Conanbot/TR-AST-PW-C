<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\PromoRequest;

use App\Models\Promo;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

use Illuminate\View\View;

class PromoController extends Controller

{

    public function index(Request $request): View

    {

        $query = Promo::withCount('usages');

        if ($request->filled('search')) {

            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {

                $q->where('title', 'like', '%' . $search . '%')

                    ->orWhere('code', 'like', '%' . $search . '%');

            });

        }

        if ($request->filled('status')) {

            $query->where('is_active', $request->status === 'active');

        }

        if ($request->filled('from')) {

            $query->whereDate('end_date', '>=', $request->from);

        }

        if ($request->filled('to')) {

            $query->whereDate('start_date', '<=', $request->to);

        }

        $promos = $query->latest()->paginate(10)->withQueryString();

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

        $data['stackable'] = $request->boolean('stackable');

        $data['code'] = !empty($data['code']) ? strtoupper(trim($data['code'])) : null;

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

        $data['stackable'] = $request->boolean('stackable');

        $data['code'] = !empty($data['code']) ? strtoupper(trim($data['code'])) : null;

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

        $promo->delete();

        return redirect()->route('admin.promos.index')

            ->with('success', 'Promo dinonaktifkan dan histori penggunaan tetap aman.');

    }

    public function show(int $id): View

    {

        $promo = Promo::with(['usages.order', 'usages.customer'])->findOrFail($id);

        return view('admin.promos.show', compact('promo'));

    }

    public function toggle(int $id): RedirectResponse

    {

        $promo = Promo::findOrFail($id);

        $promo->update(['is_active' => !$promo->is_active]);

        return back()->with('success', 'Status promo diperbarui.');

    }

}

