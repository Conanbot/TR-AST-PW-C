<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Promo;
use App\Models\PromotionUsage;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 4.3 — Promotion Tests
 *
 * Tests the PromotionService validation logic and applyToOrder idempotency.
 * Uses in-memory SQLite (RefreshDatabase) — safe, isolated from production DB.
 */
class PromotionTest extends TestCase
{
    use RefreshDatabase;

    protected PromotionService $service;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::current();
        $this->service = app(PromotionService::class);

        $this->admin = User::create([
            'name'     => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function makePromo(array $overrides = []): Promo
    {
        return Promo::create(array_merge([
            'title'           => 'Test Promo',
            'code'            => 'TESTPROMO',
            'discount_type'   => 'fixed',
            'discount_value'  => 5000,
            'minimum_subtotal' => 0,
            'is_active'       => true,
            'stackable'       => false,
        ], $overrides));
    }

    private function makeOrder(array $overrides = []): Order
    {
        static $seq = 0;
        $seq++;
        return Order::create(array_merge([
            'order_number'   => 'ORD-TEST-' . $seq,
            'checkout_key'   => 'ckey-' . $seq,
            'customer_name'  => 'Pembeli Test',
            'customer_phone' => '081234567890',
            'service_method' => 'dine_in',
            'subtotal'       => 50000,
            'packaging_fee'  => 0,
            'delivery_fee'   => 0,
            'discount_amount' => 0,
            'total'          => 50000,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'source'         => 'web',
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-01: Fixed discount calculation
    // ─────────────────────────────────────────────────────────────────────────

    public function test_fixed_discount_calculated_correctly(): void
    {
        $this->makePromo(['code' => 'FIXED5K', 'discount_type' => 'fixed', 'discount_value' => 5000]);

        $result = $this->service->validateCode('FIXED5K', 50000);

        $this->assertTrue($result['valid']);
        $this->assertEquals(5000, $result['discount']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-02: Percentage discount with max cap
    // ─────────────────────────────────────────────────────────────────────────

    public function test_percentage_discount_with_max_cap(): void
    {
        $this->makePromo([
            'code'             => 'PCT20',
            'discount_type'    => 'percentage',
            'discount_value'   => 20,   // 20%
            'maximum_discount' => 8000, // cap at 8000
        ]);

        // 20% of 50000 = 10000, capped at 8000
        $result = $this->service->validateCode('PCT20', 50000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(8000, $result['discount']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-03: Percentage without cap
    // ─────────────────────────────────────────────────────────────────────────

    public function test_percentage_discount_without_cap(): void
    {
        $this->makePromo([
            'code'           => 'PCT10',
            'discount_type'  => 'percentage',
            'discount_value' => 10,
        ]);

        // 10% of 60000 = 6000
        $result = $this->service->validateCode('PCT10', 60000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(6000, $result['discount']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-04: Minimum subtotal enforcement — rejected when below threshold
    // ─────────────────────────────────────────────────────────────────────────

    public function test_minimum_subtotal_rejection(): void
    {
        $this->makePromo([
            'code'             => 'MIN100K',
            'discount_type'    => 'fixed',
            'discount_value'   => 10000,
            'minimum_subtotal' => 100000,
        ]);

        $result = $this->service->validateCode('MIN100K', 50000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Minimum', $result['reason']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-05: Minimum subtotal — accepted when at threshold
    // ─────────────────────────────────────────────────────────────────────────

    public function test_minimum_subtotal_accepted_at_exact_threshold(): void
    {
        $this->makePromo([
            'code'             => 'MIN50K',
            'discount_type'    => 'fixed',
            'discount_value'   => 5000,
            'minimum_subtotal' => 50000,
        ]);

        $result = $this->service->validateCode('MIN50K', 50000);
        $this->assertTrue($result['valid']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-06: Inactive promo rejected
    // ─────────────────────────────────────────────────────────────────────────

    public function test_inactive_promo_rejected(): void
    {
        $this->makePromo(['code' => 'INACTIVE', 'is_active' => false]);

        $result = $this->service->validateCode('INACTIVE', 50000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('nonaktif', $result['reason']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-07: Promo expired (end_date in past) rejected
    // ─────────────────────────────────────────────────────────────────────────

    public function test_expired_promo_rejected(): void
    {
        $this->makePromo([
            'code'     => 'EXPIRED',
            'end_date' => now()->subDay()->toDateString(),
        ]);

        $result = $this->service->validateCode('EXPIRED', 50000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('kedaluwarsa', $result['reason']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-08: Promo not started (start_date in future) rejected
    // ─────────────────────────────────────────────────────────────────────────

    public function test_future_promo_not_started(): void
    {
        $this->makePromo([
            'code'       => 'FUTURE',
            'start_date' => now()->addDay()->toDateString(),
        ]);

        $result = $this->service->validateCode('FUTURE', 50000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('belum berlaku', $result['reason']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-09: Total usage quota exhausted
    // ─────────────────────────────────────────────────────────────────────────

    public function test_total_usage_quota_enforcement(): void
    {
        $promo = $this->makePromo(['code' => 'QUOTA1', 'usage_limit' => 1]);

        $order = $this->makeOrder();
        PromotionUsage::create([
            'promo_id'       => $promo->id,
            'order_id'       => $order->id,
            'code'           => $promo->code,
            'discount_amount' => 5000,
            'status'         => 'used',
        ]);

        $result = $this->service->validateCode('QUOTA1', 50000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Kuota', $result['reason']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-10: Per-customer quota enforcement
    // ─────────────────────────────────────────────────────────────────────────

    public function test_per_customer_quota_enforcement(): void
    {
        $promo = $this->makePromo([
            'code'               => 'PERCUST1',
            'per_customer_limit' => 1,
        ]);

        $order = $this->makeOrder(['customer_phone' => '081111111111']);
        PromotionUsage::create([
            'promo_id'        => $promo->id,
            'order_id'        => $order->id,
            'code'            => $promo->code,
            'discount_amount' => 5000,
            'customer_phone'  => '081111111111',
            'status'          => 'used',
        ]);

        $result = $this->service->validateCode('PERCUST1', 50000, 'dine_in', null, '081111111111');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Batas penggunaan', $result['reason']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-11: Per-customer quota — different customer can still use it
    // ─────────────────────────────────────────────────────────────────────────

    public function test_per_customer_quota_other_customer_allowed(): void
    {
        $promo = $this->makePromo([
            'code'               => 'PERCUST2',
            'per_customer_limit' => 1,
        ]);

        $order = $this->makeOrder(['customer_phone' => '081111111111']);
        PromotionUsage::create([
            'promo_id'        => $promo->id,
            'order_id'        => $order->id,
            'code'            => $promo->code,
            'discount_amount' => 5000,
            'customer_phone'  => '081111111111',
            'status'          => 'used',
        ]);

        // Different phone — should be allowed
        $result = $this->service->validateCode('PERCUST2', 50000, 'dine_in', null, '082222222222');
        $this->assertTrue($result['valid']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-12: Promo code case-insensitive lookup
    // ─────────────────────────────────────────────────────────────────────────

    public function test_promo_code_case_insensitive(): void
    {
        $this->makePromo(['code' => 'UPPERCASE']);

        $lower = $this->service->validateCode('uppercase', 50000);
        $mixed = $this->service->validateCode('UpperCase', 50000);

        $this->assertTrue($lower['valid'], 'lowercase should match');
        $this->assertTrue($mixed['valid'], 'mixed case should match');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-13: Empty code returns invalid gracefully
    // ─────────────────────────────────────────────────────────────────────────

    public function test_empty_promo_code_returns_invalid(): void
    {
        $result = $this->service->validateCode('', 50000);
        $this->assertFalse($result['valid']);
        $this->assertEquals(0, $result['discount']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-14: Nonexistent code returns invalid
    // ─────────────────────────────────────────────────────────────────────────

    public function test_nonexistent_promo_code_returns_invalid(): void
    {
        $result = $this->service->validateCode('DOESNOTEXIST', 50000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('tidak ditemukan', $result['reason']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-15: Discount cannot exceed subtotal (total never goes negative)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_discount_capped_at_subtotal(): void
    {
        $this->makePromo([
            'code'           => 'BIGDISCOUNT',
            'discount_type'  => 'fixed',
            'discount_value' => 999999,
        ]);

        $result = $this->service->validateCode('BIGDISCOUNT', 10000);
        $this->assertTrue($result['valid']);
        // Discount must not exceed subtotal
        $this->assertLessThanOrEqual(10000, $result['discount']);
        $this->assertGreaterThanOrEqual(0, $result['discount']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-16: applyToOrder — creates PromotionUsage record
    // ─────────────────────────────────────────────────────────────────────────

    public function test_apply_to_order_creates_usage_record(): void
    {
        $this->makePromo(['code' => 'APPLY5K', 'discount_value' => 5000]);
        $order = $this->makeOrder(['subtotal' => 60000, 'total' => 60000]);

        $result = $this->service->applyToOrder($order, 'APPLY5K');

        $this->assertTrue($result['valid']);
        $this->assertEquals(5000, $result['discount']);
        $this->assertDatabaseHas('promotion_usages', [
            'order_id' => $order->id,
            'code'     => 'APPLY5K',
            'status'   => 'used',
        ]);
        $order->refresh();
        $this->assertEquals(5000, $order->discount_amount);
        $this->assertEquals(55000, $order->total);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-17: applyToOrder idempotency — second call returns same result, no duplicate usage
    // ─────────────────────────────────────────────────────────────────────────

    public function test_apply_to_order_is_idempotent(): void
    {
        $this->makePromo(['code' => 'IDEM5K', 'discount_value' => 5000]);
        $order = $this->makeOrder(['subtotal' => 60000, 'total' => 60000]);

        $result1 = $this->service->applyToOrder($order, 'IDEM5K');
        $result2 = $this->service->applyToOrder($order, 'IDEM5K');

        $this->assertTrue($result1['valid']);
        $this->assertTrue($result2['valid']);
        $this->assertEquals($result1['discount'], $result2['discount']);

        // Only one usage record should exist
        $this->assertEquals(1, PromotionUsage::where('order_id', $order->id)->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-18: Null/blank code in applyToOrder is a no-op
    // ─────────────────────────────────────────────────────────────────────────

    public function test_apply_to_order_null_code_is_noop(): void
    {
        $order = $this->makeOrder(['subtotal' => 60000, 'total' => 60000]);
        $result = $this->service->applyToOrder($order, null);

        $this->assertTrue($result['valid']);
        $this->assertEquals(0, $result['discount']);
        $this->assertEquals(0, PromotionUsage::where('order_id', $order->id)->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PT-19: Order total never negative after applying promo via HTTP checkout
    // ─────────────────────────────────────────────────────────────────────────

    public function test_order_total_never_negative_after_apply(): void
    {
        $this->makePromo([
            'code'           => 'GIANT',
            'discount_type'  => 'fixed',
            'discount_value' => 999999,
        ]);

        $order = $this->makeOrder(['subtotal' => 10000, 'total' => 10000]);
        $result = $this->service->applyToOrder($order, 'GIANT');

        $order->refresh();
        $this->assertGreaterThanOrEqual(0, $order->total);
    }
}
