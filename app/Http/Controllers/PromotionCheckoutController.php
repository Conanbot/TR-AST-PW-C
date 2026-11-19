<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionCheckoutController extends Controller
{
    public function check(Request $request, OrderService $orders, PromotionService $promotions): JsonResponse
    {
        $data = $request->validate([
            'promo_code' => ['required', 'string', 'max:50'],
            'service_method' => ['required', 'in:dine_in,takeaway,delivery'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.modifier_option_ids' => ['nullable', 'array'],
            'items.*.modifier_option_ids.*' => ['integer'],
            'items.*.modifiers' => ['nullable', 'array'],
            'items.*.cut' => ['nullable', 'string', 'max:60'],
            'items.*.drink' => ['nullable', 'string', 'max:60'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $cart = $orders->calculateCart($data['items']);
        $subtotal = (int) $cart['subtotal'];
        $packagingFee = $data['service_method'] === 'takeaway'
            ? (int) \App\Models\StoreSetting::current()->packaging_fee
            : 0;
        $deliveryFee = 0;
        $customer = $request->user()?->role === 'customer' ? $request->user() : null;
        $result = $promotions->validateCode(
            $data['promo_code'],
            $subtotal,
            $data['service_method'],
            $customer,
            $data['customer_phone'] ?? null
        );

        $discount = $result['valid'] ? (int) $result['discount'] : 0;
        $total = max(0, $subtotal + $packagingFee + $deliveryFee - $discount);

        return response()->json([
            'valid' => $result['valid'],
            'reason' => $result['reason'],
            'code' => $result['valid'] ? $result['promo']->code : strtoupper(trim($data['promo_code'])),
            'discount' => $discount,
            'subtotal' => $subtotal,
            'packaging_fee' => $packagingFee,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
        ]);
    }
}
