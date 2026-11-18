<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'preparing', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function transition(Order $order, string $toStatus, ?User $actor = null, ?string $note = null): Order
    {
        $fromStatus = $order->status ?: 'pending';
        $allowed = self::TRANSITIONS[$fromStatus] ?? [];

        if (!in_array($toStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Status pesanan tidak dapat diubah dari {$fromStatus} menjadi {$toStatus}.",
            ]);
        }

        $order->update(['status' => $toStatus]);
        $order->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_user_id' => $actor?->id,
            'note' => $note,
        ]);

        return $order->refresh();
    }

    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        return in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true);
    }
}
