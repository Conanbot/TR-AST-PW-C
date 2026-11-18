<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request, OrderService $orders): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'service_method' => ['required', 'in:dine_in,takeaway,delivery'],
            'table_number' => ['nullable', 'string', 'max:20'],
            'order_notes' => ['nullable', 'string', 'max:255'],
            'customer_address' => ['required_if:service_method,delivery', 'nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.cut' => ['nullable', 'string', 'max:60'],
            'items.*.drink' => ['nullable', 'string', 'max:60'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = $request->user()?->role === 'customer' ? $request->user() : null;
        $order = $orders->create($data, $customer, null, 'online');

        return response()->json([
            'order_number' => $order->order_number,
            'whatsapp_url' => $orders->whatsappUrl($order),
        ], 201);
    }
}
