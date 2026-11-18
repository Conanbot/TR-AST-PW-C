<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseCPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_pickup_confirmation_is_idempotent(): void
    {
        $this->paymentMethods();
        $cashier = $this->roleUser('cashier');
        $order = $this->order('takeaway', 'cash_pickup');
        $payment = app(PaymentService::class)->createForOrder($order, 'cash_pickup');

        app(PaymentService::class)->confirmCash($payment, $cashier, $order->total);
        app(PaymentService::class)->confirmCash($payment, $cashier, $order->total);

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(2, $payment->fresh()->histories()->count());
    }

    public function test_kitchen_staff_cannot_confirm_payment(): void
    {
        $this->paymentMethods();
        $kitchen = $this->roleUser('kitchen');
        $order = $this->order('takeaway', 'cash_pickup');
        $payment = app(PaymentService::class)->createForOrder($order, 'cash_pickup');

        $this->actingAs($kitchen)
            ->patch("/admin/payments/{$payment->id}/confirm-cash", ['amount' => $order->total])
            ->assertForbidden();
    }

    public function test_qris_proof_and_admin_verification_history(): void
    {
        Storage::fake('public');
        $this->paymentMethods();
        $settings = StoreSetting::current();
        $settings->update([
            'qris_enabled' => true,
            'qris_image' => 'qris/test.png',
        ]);

        $order = $this->order('takeaway', 'qris');
        $payment = app(PaymentService::class)->createForOrder($order, 'qris');
        app(PaymentService::class)->submitProof(
            $payment,
            UploadedFile::fake()->image('proof.jpg'),
            $order->total
        );

        $this->assertSame('pending_verification', $payment->fresh()->status);

        $admin = $this->roleUser('admin');
        app(PaymentService::class)->verifyQris($payment, $admin, true);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertGreaterThanOrEqual(2, $payment->fresh()->histories()->count());
    }

    public function test_qris_rejection_requires_reason(): void
    {
        Storage::fake('public');
        $this->paymentMethods();
        StoreSetting::current()->update(['qris_enabled' => true, 'qris_image' => 'qris/test.png']);
        $order = $this->order('takeaway', 'qris');
        $payment = app(PaymentService::class)->createForOrder($order, 'qris');
        app(PaymentService::class)->submitProof($payment, UploadedFile::fake()->image('proof.png'), $order->total);
        $admin = $this->roleUser('admin');

        try {
            app(PaymentService::class)->verifyQris($payment, $admin, false, null);
            $this->fail('Rejection should require a reason.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('rejection_reason', $exception->errors());
        }

        app(PaymentService::class)->verifyQris($payment, $admin, false, 'Nominal tidak sesuai');
        $this->assertSame('pending_verification', $payment->fresh()->status);
        $this->assertSame('Nominal tidak sesuai', $payment->fresh()->rejection_reason);
    }

    public function test_cod_only_for_delivery_and_must_fit_radius(): void
    {
        $this->paymentMethods();
        $settings = StoreSetting::current();

        $pickup = $this->order('takeaway', 'cod');
        try {
            app(PaymentService::class)->createForOrder($pickup, 'cod');
            $this->fail('COD should be rejected for pickup.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('payment_method', $exception->errors());
        }

        $nearby = $this->order('delivery', 'cod');
        $nearby->delivery_lat = (float) $settings->store_lat;
        $nearby->delivery_lng = (float) $settings->store_lng;
        app(PaymentService::class)->validateCodRadius($nearby, $settings);

        $far = $this->order('delivery', 'cod');
        $far->delivery_lat = (float) $settings->store_lat - 0.02;
        $far->delivery_lng = (float) $settings->store_lng;
        try {
            app(PaymentService::class)->validateCodRadius($far, $settings);
            $this->fail('COD should be rejected outside radius.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('customer_address', $exception->errors());
        }
    }

    private function paymentMethods(): void
    {
        foreach ([
            'cash_pickup' => 'Cash on Pickup',
            'qris' => 'QRIS',
            'cod' => 'Cash on Delivery',
        ] as $code => $name) {
            PaymentMethod::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
        }
    }

    private function roleUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'username' => $role,
            'email' => $role . '@payment.test',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function order(string $serviceMethod, string $paymentMethod): Order
    {
        return Order::create([
            'order_number' => 'GLO-PAY-' . uniqid(),
            'customer_name' => 'Pembeli Test',
            'service_method' => $serviceMethod,
            'customer_address' => $serviceMethod === 'delivery' ? 'Alamat test' : null,
            'subtotal' => 25000,
            'total' => 25000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => $paymentMethod,
        ]);
    }
}
