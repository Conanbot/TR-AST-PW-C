<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function createForOrder(Order $order, string $methodCode): Payment
    {
        return DB::transaction(function () use ($order, $methodCode) {
        $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
        $method = PaymentMethod::active()->where('code', $methodCode)->first();
        if (!$method) {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran tidak tersedia.',
            ]);
        }

        if ($methodCode === 'qris') {
            $settings = StoreSetting::current();
            if (!$settings->qris_enabled || !$settings->qris_image) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Pembayaran QRIS belum tersedia.',
                ]);
            }
        }

        if ($methodCode === 'cod' && $lockedOrder->service_method !== 'delivery') {
            throw ValidationException::withMessages([
                'payment_method' => 'Cash on Delivery hanya tersedia untuk pesanan delivery.',
            ]);
        }

        if ($methodCode === 'cash_pickup' && !in_array($lockedOrder->service_method, ['takeaway', 'dine_in'], true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Cash on Pickup hanya tersedia untuk pesanan pickup atau dine-in.',
            ]);
        }

        $existing = $lockedOrder->payments()->where('method_code', $methodCode)->first();
        if ($existing) {
            return $existing;
        }

        $payment = $lockedOrder->payments()->create([
            'payment_reference' => (string) Str::uuid(),
            'method_code' => $methodCode,
            'amount' => $lockedOrder->total,
            'status' => $methodCode === 'qris' ? 'unpaid' : 'unpaid',
        ]);

        $lockedOrder->update([
            'payment_method' => $methodCode,
            'payment_status' => 'unpaid',
        ]);

        $this->history($payment, null, 'created', 'unpaid', 'unpaid', null, 'Transaksi pembayaran dibuat');

        return $payment;
        });
    }

    public function confirmCash(Payment $payment, User $actor, ?int $claimedAmount = null): Payment
    {
        if (!in_array($actor->role, ['admin', 'cashier'], true)) {
            throw ValidationException::withMessages(['payment' => 'Anda tidak memiliki izin mengonfirmasi pembayaran tunai.']);
        }

        if (!in_array($payment->method_code, ['cash_pickup', 'cod'], true)) {
            throw ValidationException::withMessages(['payment' => 'Metode ini bukan pembayaran tunai.']);
        }

        return DB::transaction(function () use ($payment, $actor, $claimedAmount) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === 'paid') {
                return $payment->load('histories');
            }

            $amount = $claimedAmount ?? $payment->amount;
            if ($amount !== $payment->amount) {
                throw ValidationException::withMessages(['amount' => 'Nominal pembayaran tunai tidak sesuai dengan total pesanan.']);
            }

            $fromStatus = $payment->status;
            $payment->update([
                'status' => 'paid',
                'confirmed_by_user_id' => $actor->id,
                'paid_at' => now(),
                'verified_at' => now(),
            ]);
            $payment->order->update(['payment_status' => 'paid']);
            $this->history($payment, $actor, 'confirmed_cash', $fromStatus, 'paid', $amount);

            return $payment->load('histories');
        });
    }

    public function submitProof(Payment $payment, UploadedFile $proof, ?int $claimedAmount = null): Payment
    {
        if ($payment->method_code !== 'qris') {
            throw ValidationException::withMessages(['payment' => 'Bukti hanya diperlukan untuk QRIS.']);
        }

        if ($payment->status === 'paid') {
            throw ValidationException::withMessages(['payment' => 'Pembayaran QRIS sudah lunas.']);
        }

        $amount = $claimedAmount ?? $payment->amount;
        if ($amount !== $payment->amount) {
            throw ValidationException::withMessages(['amount' => 'Nominal bukti pembayaran tidak sesuai dengan total pesanan.']);
        }

        $path = $proof->store('payment-proofs', 'public');

        return DB::transaction(function () use ($payment, $path, $amount) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $oldProof = $payment->proof_image;
            $fromStatus = $payment->status;
            $payment->update([
                'proof_image' => $path,
                'status' => 'pending_verification',
                'rejection_reason' => null,
            ]);
            $payment->order->update(['payment_status' => 'pending_verification']);
            $this->history($payment, null, 'proof_submitted', $fromStatus, 'pending_verification', $amount);

            if ($oldProof && $oldProof !== $path) {
                Storage::disk('public')->delete($oldProof);
            }

            return $payment->load('histories');
        });
    }

    public function verifyQris(Payment $payment, User $actor, bool $approved, ?string $reason = null): Payment
    {
        if (!in_array($actor->role, ['admin'], true)) {
            throw ValidationException::withMessages(['payment' => 'Hanya admin yang dapat memverifikasi QRIS.']);
        }

        if ($payment->method_code !== 'qris') {
            throw ValidationException::withMessages(['payment' => 'Metode pembayaran bukan QRIS.']);
        }

        return DB::transaction(function () use ($payment, $actor, $approved, $reason) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === 'paid' || !$payment->proof_image) {
                if ($payment->status === 'paid') {
                    return $payment->load('histories');
                }
                throw ValidationException::withMessages(['proof' => 'Bukti pembayaran belum tersedia.']);
            }

            $fromStatus = $payment->status;
            if ($approved) {
                $payment->update([
                    'status' => 'paid',
                    'confirmed_by_user_id' => $actor->id,
                    'paid_at' => now(),
                    'verified_at' => now(),
                    'rejection_reason' => null,
                ]);
                $payment->order->update(['payment_status' => 'paid']);
                $this->history($payment, $actor, 'approved', $fromStatus, 'paid', $payment->amount);
            } else {
                if (blank($reason)) {
                    throw ValidationException::withMessages(['rejection_reason' => 'Alasan penolakan wajib diisi.']);
                }
                $payment->update([
                    'status' => 'pending_verification',
                    'rejection_reason' => $reason,
                ]);
                $payment->order->update(['payment_status' => 'pending_verification']);
                $this->history($payment, $actor, 'rejected', $fromStatus, 'pending_verification', $payment->amount, $reason);
            }

            return $payment->load('histories');
        });
    }

    public function validateCodRadius(Order $order, StoreSetting $settings): void
    {
        if ($order->delivery_lat === null || $order->delivery_lng === null) {
            throw ValidationException::withMessages([
                'customer_address' => 'Lokasi pelanggan diperlukan untuk Cash on Delivery.',
            ]);
        }

        $distance = $this->haversineMeters(
            (float) $settings->store_lat,
            (float) $settings->store_lng,
            (float) $order->delivery_lat,
            (float) $order->delivery_lng
        );

        $radius = (int) $settings->delivery_radius_m;
        if ($distance > $radius) {
            throw ValidationException::withMessages([
                'customer_address' => "Alamat delivery berada {$distance} meter dari toko dan melewati radius maksimal {$radius} meter.",
            ]);
        }
    }

    public function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;
        $meters = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($meters);
    }

    private function history(Payment $payment, ?User $actor, string $action, string $from, string $to, ?int $amount, ?string $note = null): void
    {
        $payment->histories()->create([
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'claimed_amount' => $amount,
            'note' => $note,
        ]);
    }
}
