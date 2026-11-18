<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderAccessService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentProofController extends Controller
{
    public function store(Request $request, string $orderNumber, string $token, OrderAccessService $access, PaymentService $payments): RedirectResponse
    {
        $order = $access->find($orderNumber, $token);
        abort_unless($order, 404);

        $data = $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'claimed_amount' => ['required', 'integer', 'min:1'],
        ]);

        $payment = $order->payments()->where('method_code', 'qris')->first();
        abort_unless($payment, 404);

        $payments->submitProof($payment, $request->file('proof'), (int) $data['claimed_amount']);

        return redirect()->route('orders.track', [
            'orderNumber' => $orderNumber,
            'token' => $token,
        ])->with('success', 'Bukti pembayaran QRIS dikirim dan menunggu verifikasi admin.');
    }
}
