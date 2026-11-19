<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\OrderAccessService;
use App\Services\PaymentService;
use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request, OrderService $orders, OrderAccessService $access): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'checkout_key' => ['nullable', 'string', 'max:100'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'service_method' => ['required', 'in:dine_in,takeaway,delivery'],
            'table_number' => ['nullable', 'string', 'max:20'],
            'order_notes' => ['nullable', 'string', 'max:255'],
            'customer_address' => ['required_if:service_method,delivery', 'nullable', 'string', 'max:1000'],
            'delivery_lat' => ['required_if:payment_method,cod', 'nullable', 'numeric', 'between:-90,90'],
            'delivery_lng' => ['required_if:payment_method,cod', 'nullable', 'numeric', 'between:-180,180'],
            'payment_method' => ['nullable', 'in:cash_pickup,qris,cod'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.modifier_option_ids' => ['nullable', 'array'],
            'items.*.modifier_option_ids.*' => ['integer'],
            'items.*.modifiers' => ['nullable', 'array'],
            'items.*.modifiers.*.option_id' => ['required_with:items.*.modifiers', 'integer'],
            'items.*.cut' => ['nullable', 'string', 'max:60'],
            'items.*.drink' => ['nullable', 'string', 'max:60'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $data['payment_method'] ??= 'cash_pickup';

        $customer = $request->user()?->role === 'customer' ? $request->user() : null;
        try {
            $order = $orders->create($data, $customer, null, 'online');
        } catch (QueryException $exception) {
            $isCheckoutKeyConflict = !empty($data['checkout_key'])
                && str_contains($exception->getMessage(), 'checkout_key');

            if (!$isCheckoutKeyConflict) {
                throw $exception;
            }

            $order = Order::where('checkout_key', $data['checkout_key'])->firstOrFail()
                ->load(['items.modifiers', 'payments']);
        }

        $token = $access->issue($order);

        return response()->json([
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'whatsapp_url' => $orders->whatsappUrl($order, route('orders.track', ['orderNumber' => $order->order_number, 'token' => $token]) ),
            'tracking_url' => route('orders.track', ['orderNumber' => $order->order_number, 'token' => $token]),
        ], 201);
    }
}
