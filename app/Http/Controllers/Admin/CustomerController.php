<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Customer;

use App\Services\CustomerService;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Validation\ValidationException;

use Illuminate\View\View;

class CustomerController extends Controller

{

    public function index(Request $request): View

    {

        $query = Customer::query()

            ->withCount('orders')

            ->withMax('orders as last_order_at', 'created_at')

            ->withSum(['orders as total_spending' => function ($q) {

                $q->where('status', '!=', 'cancelled')

                    ->where(function ($sub) {

                        $sub->where('payment_status', 'paid')

                            ->orWhere('status', 'completed');

                    });

            }], 'total');

        if ($request->filled('search')) {

            $term = trim($request->string('search'));

            $normalizedTerm = Customer::normalizePhone($term) ?? $term;

            $query->where(function ($q) use ($term, $normalizedTerm) {

                $q->where('name', 'like', '%' . $term . '%')

                    ->orWhere('phone', 'like', '%' . $term . '%')

                    ->orWhere('normalized_phone', 'like', '%' . $normalizedTerm . '%')

                    ->orWhere('email', 'like', '%' . $term . '%');

            });

        }

        if ($request->filled('status')) {

            $query->where('status', $request->string('status'));

        }

        if ($request->filled('date_from')) {

            $query->whereHas('orders', function ($q) use ($request) {

                $q->whereDate('created_at', '>=', $request->date_from);

            });

        }

        if ($request->filled('date_to')) {

            $query->whereHas('orders', function ($q) use ($request) {

                $q->whereDate('created_at', '<=', $request->date_to);

            });

        }

        $customers = $query->latest('updated_at')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));

    }

    public function store(Request $request, CustomerService $service): RedirectResponse

    {

        $data = $request->validate([

            'name' => ['required', 'string', 'max:120'],

            'phone' => ['required', 'string', 'max:30'],

            'email' => ['nullable', 'email', 'max:120'],

            'notes' => ['nullable', 'string', 'max:1000'],

            'status' => ['required', 'in:active,inactive'],

        ]);

        $normalized = Customer::normalizePhone($data['phone']);

        if (!$normalized) {

            throw ValidationException::withMessages([

                'phone' => 'Nomor telepon tidak valid. Pastikan format nomor nomor Indonesia atau internasional yang benar.',

            ]);

        }

        if (Customer::where('normalized_phone', $normalized)->exists()) {

            throw ValidationException::withMessages([

                'phone' => 'Nomor telepon sudah terdaftar pada pelanggan lain.',

            ]);

        }

        $customer = Customer::create([

            'name' => $data['name'],

            'phone' => $data['phone'],

            'normalized_phone' => $normalized,

            'email' => $data['email'] ?? null,

            'notes' => $data['notes'] ?? null,

            'status' => $data['status'],

        ]);

        return redirect()->route('admin.customers.show', $customer)

            ->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");

    }

    public function show(Customer $customer, CustomerService $service): View

    {

        $statistics = $service->getStatistics($customer);

        $orders = $service->getCustomerOrders($customer, 15);

        return view('admin.customers.show', compact('customer', 'statistics', 'orders'));

    }

    public function update(Request $request, Customer $customer): RedirectResponse

    {

        $data = $request->validate([

            'name' => ['required', 'string', 'max:120'],

            'phone' => ['required', 'string', 'max:30'],

            'email' => ['nullable', 'email', 'max:120'],

            'notes' => ['nullable', 'string', 'max:1000'],

            'status' => ['required', 'in:active,inactive'],

        ]);

        $normalized = Customer::normalizePhone($data['phone']);

        if (!$normalized) {

            throw ValidationException::withMessages([

                'phone' => 'Nomor telepon tidak valid.',

            ]);

        }

        $conflict = Customer::where('normalized_phone', $normalized)

            ->where('id', '!=', $customer->id)

            ->exists();

        if ($conflict) {

            throw ValidationException::withMessages([

                'phone' => 'Nomor telepon sudah digunakan oleh pelanggan lain.',

            ]);

        }

        $customer->update([

            'name' => $data['name'],

            'phone' => $data['phone'],

            'normalized_phone' => $normalized,

            'email' => $data['email'] ?? null,

            'notes' => $data['notes'] ?? null,

            'status' => $data['status'],

        ]);

        return back()->with('success', "Profil {$customer->name} berhasil diperbarui.");

    }

    public function toggleStatus(Customer $customer): RedirectResponse

    {

        $newStatus = $customer->status === 'active' ? 'inactive' : 'active';

        $customer->update(['status' => $newStatus]);

        return back()->with('success', "Status pelanggan diubah menjadi {$newStatus}.");

    }

    public function destroy(Customer $customer): RedirectResponse

    {

        if ($customer->orders()->exists()) {

            return back()->with('error', 'Pelanggan yang memiliki riwayat pesanan tidak dapat dihapus secara permanen.');

        }

        $customer->delete();

        return redirect()->route('admin.customers.index')

            ->with('success', 'Data pelanggan berhasil dihapus.');

    }

}

