<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3DCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_whatsapp_checkout_persists_order_payment_and_tracking_data(): void
    {
        $item = $this->menuItem();
        $this->paymentMethods();

        $response = $this->postJson('/orders', $this->payload($item, 'phase3d-checkout-1'));

        $response->assertCreated()
            ->assertJsonStructure([
                'order_number',
                'payment_method',
                'payment_status',
                'whatsapp_url',
                'tracking_url',
            ]);

        $order = Order::with('items')->where('checkout_key', 'phase3d-checkout-1')->firstOrFail();

        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('cash_pickup', $order->payment_method);
        $this->assertSame(1, $order->items->count());
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method_code' => 'cash_pickup',
            'status' => 'unpaid',
        ]);

        $message = urldecode(parse_url($response->json('whatsapp_url'), PHP_URL_QUERY));
        $this->assertStringContainsString($order->order_number, $message);
        $this->assertStringContainsString('08123456789', $message);
        $this->assertStringContainsString('Cash on Pickup', $message);
        $this->assertStringContainsString($response->json('tracking_url'), $message);
    }

    public function test_repeated_checkout_key_returns_the_existing_order_without_duplicate_payment(): void
    {
        $item = $this->menuItem();
        $this->paymentMethods();
        $payload = $this->payload($item, 'phase3d-idempotent-1');

        $first = $this->postJson('/orders', $payload)->assertCreated();
        $second = $this->postJson('/orders', $payload)->assertCreated();

        $this->assertSame($first->json('order_number'), $second->json('order_number'));
        $this->assertSame(1, Order::where('checkout_key', 'phase3d-idempotent-1')->count());
        $this->assertSame(1, $this->orderPaymentCount($first->json('order_number')));
    }

    public function test_invalid_checkout_does_not_return_a_whatsapp_url_or_create_an_order(): void
    {
        $this->paymentMethods();

        $response = $this->postJson('/orders', [
            'checkout_key' => 'phase3d-invalid-1',
            'customer_name' => 'Guest Tester',
            'service_method' => 'takeaway',
            'payment_method' => 'cash_pickup',
            'items' => [[
                'id' => 999999,
                'qty' => 1,
            ]],
        ]);

        $response->assertStatus(422)->assertJsonMissingPath('whatsapp_url');
        $this->assertDatabaseMissing('orders', ['checkout_key' => 'phase3d-invalid-1']);
    }

    private function menuItem(): MenuItem
    {
        $category = Category::create([
            'name' => 'Paket',
            'slug' => 'paket-' . uniqid(),
        ]);

        return MenuItem::create([
            'category_id' => $category->id,
            'name' => 'Paket Ayam',
            'price' => 25000,
            'is_available' => true,
        ]);
    }

    private function paymentMethods(): void
    {
        PaymentMethod::updateOrCreate(
            ['code' => 'cash_pickup'],
            [
                'name' => 'Cash on Pickup',
                'description' => 'Bayar tunai saat mengambil pesanan.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    private function payload(MenuItem $item, string $checkoutKey): array
    {
        return [
            'checkout_key' => $checkoutKey,
            'customer_name' => 'Guest Tester',
            'customer_phone' => '08123456789',
            'service_method' => 'takeaway',
            'payment_method' => 'cash_pickup',
            'items' => [[
                'id' => $item->id,
                'qty' => 1,
                'price' => 1,
            ]],
        ];
    }

    private function orderPaymentCount(string $orderNumber): int
    {
        return (int) Order::where('order_number', $orderNumber)
            ->firstOrFail()
            ->payments()
            ->count();
    }
}
