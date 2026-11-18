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

    public function feed(): JsonResponse
    {
        $orders = Order::with('items.modifiers')
            ->whereIn('status', ['confirmed', 'preparing', 'ready'])
            ->latest()
            ->get()
            ->groupBy('status');

        return response()->json([
            'new' => $this->cards($orders->get('confirmed', collect())),
            'preparing' => $this->cards($orders->get('preparing', collect())),
            'ready' => $this->cards($orders->get('ready', collect())),
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

    private function cards($orders)
    {
        return $orders->map(fn (Order $order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'created_at' => $order->created_at->toIso8601String(),
            'wait_minutes' => $order->created_at->diffInMinutes(now()),
            'customer_note' => $order->order_notes,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->item_name,
                'quantity' => $item->quantity,
                'variant' => $item->variant_name,
                'cut' => $item->chicken_cut,
                'drink' => $item->drink_option,
                'modifiers' => $item->modifiers->map(fn ($modifier) => $modifier->option_name)->values(),
                'note' => $item->note,
            ])->values(),
        ])->values();
    }
}
