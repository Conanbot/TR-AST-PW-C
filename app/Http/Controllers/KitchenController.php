<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(): View
    {
        return view('kitchen.index');
    }

    public function feed(Request $request): JsonResponse
    {
        $allowedStatuses = ['confirmed', 'preparing', 'ready'];

        $query = Order::with('items.modifiers')
            ->whereIn('status', $allowedStatuses)
            ->latest();

        // Optional single-status filter from query string (?status=preparing)
        if ($request->filled('status') && in_array($request->string('status')->toString(), $allowedStatuses, true)) {
            $query->where('status', $request->string('status'));
        }

        $orders = $query->get()->groupBy('status');

        return response()->json([
            'new'       => $this->cards($orders->get('confirmed', collect())),
            'preparing' => $this->cards($orders->get('preparing', collect())),
            'ready'     => $this->cards($orders->get('ready', collect())),
        ]);
    }

    public function transition(Request $request, Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:preparing,ready'],
        ]);

        $statuses->transition($order, $data['status'], $request->user());

        return back()->with('success', "Pesanan {$order->order_number} diperbarui.");
    }

    private function cards($orders): array
    {
        return $orders->map(fn(Order $order) => [
            'id'            => $order->id,
            'order_number'  => $order->order_number,
            'status'        => $order->status,
            'created_at'    => $order->created_at->toIso8601String(),
            'wait_minutes'  => $order->created_at->diffInMinutes(now()),
            'customer_note' => $order->order_notes,
            'table'         => $order->table_number,
            'service_label' => ucfirst(str_replace('_', ' ', $order->service_method)),
            'items'         => $order->items->map(fn($item) => [
                'name'      => $item->item_name,
                'quantity'  => $item->quantity,
                'variant'   => $item->variant_name,
                'cut'       => $item->chicken_cut,
                'drink'     => $item->drink_option,
                'modifiers' => $item->modifiers->map(fn($m) => $m->option_name)->values()->toArray(),
                'note'      => $item->note,
            ])->values()->toArray(),
        ])->values()->toArray();
    }
}
