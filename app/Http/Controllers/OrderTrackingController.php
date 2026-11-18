<?php

namespace App\Http\Controllers;

use App\Services\OrderAccessService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(string $orderNumber, string $token, OrderAccessService $access): View|Response
    {
        $order = $access->find($orderNumber, $token);

        abort_unless($order, 404);

        return view('orders.track', compact('order'));
    }
}
