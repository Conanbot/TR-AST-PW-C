<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Services\OrderService;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['menuItems' => function ($query) {
            $query->where('is_available', true)->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        $orders = Order::with('items')
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->latest()
            ->take(12)
            ->get();
        $settings = StoreSetting::current();

        return view('cashier.dashboard', compact('categories', 'orders', 'settings'));
    }

    public function storeOrder(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'service_method' => ['required', 'in:dine_in,takeaway'],
            'table_number' => ['required_if:service_method,dine_in', 'nullable', 'string', 'max:20'],
            'items' => ['required', 'json'],
            'payment_method' => ['required', 'in:cash_pickup'],
        ]);
        $data['items'] = json_decode($data['items'], true);

        if (!is_array($data['items']) || count($data['items']) === 0 || count($data['items']) > 30) {
            throw ValidationException::withMessages(['items' => 'Tambahkan minimal satu menu ke pesanan.']);
        }

        foreach ($data['items'] as $item) {
            if (
                !is_array($item)
                || !isset($item['id'], $item['qty'])
                || !is_numeric($item['id'])
                || !is_numeric($item['qty'])
                || $item['qty'] < 1
                || $item['qty'] > 20
                || (isset($item['note']) && (!is_string($item['note']) || mb_strlen($item['note']) > 255))
            ) {
                throw ValidationException::withMessages(['items' => 'Rincian menu tidak valid. Periksa kembali keranjang.']);
            }
        }

        $order = $orders->create($data, null, $request->user(), 'cashier');

        return redirect()->route('cashier.dashboard')
            ->with('success', 'Pesanan ' . $order->order_number . ' berhasil dibuat.');
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:preparing,ready,completed,cancelled'],
            'cancellation_reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:255'],
        ]);
        $statuses->transition($order, $data['status'], $request->user(), $data['cancellation_reason'] ?? null);

        return back()->with('success', 'Status ' . $order->order_number . ' diperbarui.');
    }
}
