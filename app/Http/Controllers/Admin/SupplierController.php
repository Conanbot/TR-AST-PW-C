<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Supplier;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\View\View;

class SupplierController extends Controller

{

    public function index(Request $request): View

    {

        $query = Supplier::withCount('purchaseOrders');

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where('name', 'like', '%' . $request->search . '%')

                    ->orWhere('phone', 'like', '%' . $request->search . '%')

                    ->orWhere('contact_person', 'like', '%' . $request->search . '%');

            });

        }

        if ($request->filled('status')) {

            $query->where('is_active', $request->status === 'active');

        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));

    }

    public function store(Request $request): RedirectResponse

    {

        $data = $request->validate([

            'name' => ['required', 'string', 'max:150'],

            'phone' => ['nullable', 'string', 'max:30'],

            'contact_person' => ['nullable', 'string', 'max:150'],

            'address' => ['nullable', 'string', 'max:500'],

            'notes' => ['nullable', 'string', 'max:2000'],

        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Supplier::create($data);

        return back()->with('success', 'Supplier berhasil ditambahkan.');

    }

    public function update(Request $request, Supplier $supplier): RedirectResponse

    {

        $data = $request->validate([

            'name' => ['required', 'string', 'max:150'],

            'phone' => ['nullable', 'string', 'max:30'],

            'contact_person' => ['nullable', 'string', 'max:150'],

            'address' => ['nullable', 'string', 'max:500'],

            'notes' => ['nullable', 'string', 'max:2000'],

        ]);

        $data['is_active'] = $request->boolean('is_active');

        $supplier->update($data);

        return back()->with('success', 'Data supplier berhasil diperbarui.');

    }

    public function show(Supplier $supplier): View

    {

        $supplier->load(['purchaseOrders.items', 'purchaseOrders.receivings']);

        return view('admin.suppliers.show', compact('supplier'));

    }

    public function toggle(Supplier $supplier): RedirectResponse

    {

        $supplier->update(['is_active' => !$supplier->is_active]);

        return back()->with('success', 'Status supplier berhasil diperbarui.');

    }

    public function destroy(Supplier $supplier): RedirectResponse

    {

        if ($supplier->purchaseOrders()->exists()) {

            $supplier->update(['is_active' => false]);

            return back()->with('success', 'Supplier memiliki histori transaksi sehingga dinonaktifkan, bukan dihapus.');

        }

        $supplier->delete();

        return back()->with('success', 'Supplier berhasil dihapus.');

    }

}

