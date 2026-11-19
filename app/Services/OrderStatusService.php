<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(function () use ($order, $toStatus, $actor, $note) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $lockedOrder->status ?: 'pending';
            $allowed = self::TRANSITIONS[$fromStatus] ?? [];

            if (!in_array($toStatus, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => "Status pesanan tidak dapat diubah dari {$fromStatus} menjadi {$toStatus}.",
                ]);
            }

            if ($toStatus === 'completed') {
                $paymentIsPaid = $lockedOrder->payment_status === 'paid'
                    || $lockedOrder->latestPayment()->where('status', 'paid')->exists();
                $paymentWasEstablished = $lockedOrder->payment_status !== null
                    || $lockedOrder->payments()->exists();
                if ($paymentWasEstablished && !$paymentIsPaid) {
                    throw ValidationException::withMessages([
                        'status' => 'Pesanan hanya dapat diselesaikan setelah pembayaran berstatus paid.',
                    ]);
                }

            }

            if ($toStatus === 'preparing') {
                // Inventory integration removed — stock deduction no longer performed
            }

            if ($toStatus === 'cancelled' && blank($note)) {
                throw ValidationException::withMessages([
                    'cancellation_reason' => 'Alasan pembatalan wajib diisi.',
                ]);
            }

            $lockedOrder->update([
                'status' => $toStatus,
                'cancellation_reason' => $toStatus === 'cancelled' ? $note : $lockedOrder->cancellation_reason,
            ]);
            $lockedOrder->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by_user_id' => $actor?->id,
                'note' => $note,
            ]);

            return $lockedOrder->refresh();
        });
    }

    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        return in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true);
    }
}
