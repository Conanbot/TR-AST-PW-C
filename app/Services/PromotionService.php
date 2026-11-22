<?php
namespace App\Services;
use App\Models\Order;
use App\Models\Promo;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Validation\ValidationException;
class PromotionService
{
    public function validateCode(
        ?string $code,
        int $subtotal,
        string $serviceMethod = 'dine_in',
        ?User $customer = null,
        ?string $customerPhone = null,
        bool $lock = false
    ): array {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return ['valid' => false, 'reason' => 'Kode promo belum dimasukkan.', 'discount' => 0, 'promo' => null];
        }
        $query = Promo::where('code', $code);
        if ($lock) {
            $query->lockForUpdate();
        }
        $promo = $query->first();
        if (!$promo) {
            return ['valid' => false, 'reason' => 'Kode promo tidak ditemukan.', 'discount' => 0, 'promo' => null];
        }
        if (!$promo->is_active) {
            return ['valid' => false, 'reason' => 'Promo sedang nonaktif.', 'discount' => 0, 'promo' => $promo];
        }
        $today = now()->toDateString();
        if ($promo->start_date && $today < $promo->start_date->toDateString()) {
            return ['valid' => false, 'reason' => 'Promo belum berlaku.', 'discount' => 0, 'promo' => $promo];
        }
        if ($promo->end_date && $today > $promo->end_date->toDateString()) {
            return ['valid' => false, 'reason' => 'Promo sudah kedaluwarsa.', 'discount' => 0, 'promo' => $promo];
        }
        if ($subtotal < (int) $promo->minimum_subtotal) {
            return ['valid' => false, 'reason' => 'Minimum pembelian Rp' . number_format($promo->minimum_subtotal, 0, ',', '.') . ' belum terpenuhi.', 'discount' => 0, 'promo' => $promo];
        }
        if ($promo->usage_limit !== null && PromotionUsage::where('promo_id', $promo->id)->count() >= (int) $promo->usage_limit) {
            return ['valid' => false, 'reason' => 'Kuota penggunaan promo sudah habis.', 'discount' => 0, 'promo' => $promo];
        }
        if ($promo->per_customer_limit !== null) {
            $usageQuery = PromotionUsage::where('promo_id', $promo->id);
            if ($customer) {
                $usageQuery->where('customer_user_id', $customer->id);
            } elseif ($customerPhone) {
                $usageQuery->where('customer_phone', $customerPhone);
            } else {
                $usageQuery->whereRaw('1 = 0');
            }
            if ($usageQuery->count() >= (int) $promo->per_customer_limit) {
                return ['valid' => false, 'reason' => 'Batas penggunaan promo untuk pelanggan ini sudah tercapai.', 'discount' => 0, 'promo' => $promo];
            }
        }
        $discount = match ($promo->discount_type) {
            'percentage' => (int) round($subtotal * ((int) $promo->discount_value / 100)),
            'free_delivery' => $serviceMethod === 'delivery' ? 0 : 0,
            default => (int) $promo->discount_value,
        };
        if ($promo->discount_type === 'percentage' && $promo->maximum_discount !== null) {
            $discount = min($discount, (int) $promo->maximum_discount);
        }
        $discount = max(0, min($discount, $subtotal));
        return [
            'valid' => true,
            'reason' => null,
            'discount' => $discount,
            'promo' => $promo,
        ];
    }
    public function applyToOrder(
        Order $order,
        ?string $code,
        ?User $customer = null
    ): array {
        if (blank($code)) {
            return ['valid' => true, 'discount' => 0, 'promo' => null];
        }
        $existingUsage = PromotionUsage::where('order_id', $order->id)->first();
        if ($existingUsage) {
            return [
                'valid' => true,
                'discount' => (int) $existingUsage->discount_amount,
                'promo' => $existingUsage->promo,
            ];
        }
        $result = $this->validateCode(
            $code,
            (int) $order->subtotal,
            $order->service_method,
            $customer,
            $order->customer_phone,
            true
        );
        if (!$result['valid']) {
            throw ValidationException::withMessages(['promo_code' => $result['reason']]);
        }
        $promo = $result['promo'];
        $discount = (int) $result['discount'];
        $order->update([
            'promo_id' => $promo->id,
            'promo_code' => $promo->code,
            'discount_amount' => $discount,
            'total' => max(0, (int) $order->subtotal + (int) $order->packaging_fee + (int) $order->delivery_fee - $discount),
        ]);
        PromotionUsage::create([
            'promo_id' => $promo->id,
            'order_id' => $order->id,
            'code' => $promo->code,
            'discount_amount' => $discount,
            'customer_user_id' => $customer?->id,
            'customer_phone' => $order->customer_phone,
            'status' => 'used',
        ]);
        return ['valid' => true, 'discount' => $discount, 'promo' => $promo];
    }
}
