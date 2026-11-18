<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InformationArchitectureAndMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        StoreSetting::create([
            'id' => 1,
            'is_open' => true,
            'wa_number' => '6281268177729',
            'packaging_fee' => 1000,
            'delivery_enabled' => true,
            'delivery_radius_m' => 800,
        ]);
    }

    public function test_customer_can_register_and_is_redirected_to_login_page(): void
    {
        $response = $this->post('/akun/daftar', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/akun/login');
        $this->assertDatabaseHas('users', [
            'email' => 'budi@test.com',
            'phone' => '081234567890',
            'role' => 'customer',
        ]);
    }

    public function test_customer_can_login_and_is_redirected_to_menu(): void
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $response = $this->post('/akun/login', [
            'email' => 'budi@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/menu');
        $this->assertAuthenticatedAs($user);
    }

    public function test_order_stores_table_number_and_order_notes_and_sets_queued_status(): void
    {
        $category = Category::create(['name' => 'Paket Hemat', 'slug' => 'paket-hemat']);
        $menuItem = MenuItem::create([
            'category_id' => $category->id,
            'name' => 'Paket Super Komplit',
            'price' => 25000,
            'is_available' => true,
        ]);

        $customer = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'email' => 'budi@test.com',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->postJson('/orders', [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'service_method' => 'dine_in',
            'table_number' => 'Meja 4',
            'order_notes' => 'Priority Service, Add Cutlery',
            'items' => [
                [
                    'id' => $menuItem->id,
                    'qty' => 1,
                    'cut' => 'Dada',
                    'drink' => 'Es Teh Jumbo',
                    'note' => 'Cabai 5 pedas',
                ]
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Budi Santoso',
            'service_method' => 'dine_in',
            'table_number' => 'Meja 4',
            'order_notes' => 'Priority Service, Add Cutlery',
            'kitchen_status' => 'queued',
        ]);
    }

    public function test_admin_can_update_kitchen_status_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin GLO',
            'username' => 'admin',
            'email' => 'admin@geprekglo.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $order = Order::create([
            'order_number' => 'GLO-261118-0001',
            'customer_name' => 'Budi',
            'service_method' => 'dine_in',
            'table_number' => 'Meja 2',
            'subtotal' => 25000,
            'packaging_fee' => 0,
            'total' => 25000,
            'status' => 'pending',
            'kitchen_status' => 'queued',
        ]);

        // 1. Move to prepared
        $res1 = $this->actingAs($admin)->patchJson("/admin/orders/{$order->id}/kitchen-status", [
            'kitchen_status' => 'prepared',
        ]);
        $res1->assertOk();
        $this->assertEquals('prepared', $order->fresh()->kitchen_status);

        // 2. Move to ready
        $res2 = $this->actingAs($admin)->patchJson("/admin/orders/{$order->id}/kitchen-status", [
            'kitchen_status' => 'ready',
        ]);
        $res2->assertOk();
        $this->assertEquals('ready', $order->fresh()->kitchen_status);
        $this->assertEquals('ready', $order->fresh()->status);

        // 3. Move to completed
        $res3 = $this->actingAs($admin)->patchJson("/admin/orders/{$order->id}/kitchen-status", [
            'kitchen_status' => 'completed',
        ]);
        $res3->assertOk();
        $this->assertEquals('completed', $order->fresh()->kitchen_status);
        $this->assertEquals('completed', $order->fresh()->status);
    }
}
