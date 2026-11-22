<?php
namespace App\Services;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
class OrderAccessService
{
    public function issue(Order $order): string
    {
        $token = Str::random(64);
        $order->accessTokens()->create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => Carbon::now()->addDays(7),
        ]);
        return $token;
    }
    public function find(string $orderNumber, string $token): ?Order
    {
        $order = Order::with(['items.modifiers', 'items.variant'])
            ->where('order_number', $orderNumber)
            ->first();
        if (!$order) {
            return null;
        }
        $tokenHash = hash('sha256', $token);
        $accessToken = $order->accessTokens()
            ->where('token_hash', $tokenHash)
            ->first();
        if (!$accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
            return null;
        }
        $accessToken->update(['last_used_at' => Carbon::now()]);
        return $order;
    }
}
