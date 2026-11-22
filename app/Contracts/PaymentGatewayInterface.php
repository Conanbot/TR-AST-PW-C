<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface

{

    public function create(Order $order, array $options = []): array;

    public function handleWebhook(array $payload): array;

    public function refund(Order $order, int $amount, ?string $reason = null): array;

}

