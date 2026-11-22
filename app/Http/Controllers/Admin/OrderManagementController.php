<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class OrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::withCount('items')->with('items');
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->string('search') . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('service_method')) {
            $query->where('service_method', $request->string('service_method'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }
    public function show(Order $order): View
    {
        $order->load(['items.modifiers', 'items.variant', 'statusHistories.changedBy', 'customer']);
        return view('admin.orders.show', compact('order'));
    }
    public function transition(Request $request, Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:confirmed,preparing,ready,completed,cancelled'],
            'cancellation_reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:255'],
        ]);
        $statuses->transition($order, $data['status'], $request->user(), $data['cancellation_reason'] ?? null);
        return back()->with('success', "Status {$order->order_number} berhasil diperbarui.");
    }
}
