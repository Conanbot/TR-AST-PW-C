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

class RoleAndOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_specific_login_pages_are_separate(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Portal admin');
        $this->get('/kasir/login')->assertOk()->assertSee('Portal kasir');
        $this->get('/akun/login')->assertOk()->assertSee('Akun pelanggan');
        $this->get('/akun/daftar')->assertOk()->assertSee('Buat akun pelanggan');
    }

    public function test_customer_registration_creates_a_customer_only_account(): void
    {
        $response = $this->post('/akun/daftar', [
            'name' => 'Dina Mahasiswa',
            'email' => 'dina@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/akun');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'dina@example.test',
            'role' => 'customer',
        ]);
        $this->get('/admin')->assertForbidden();
        $this->get('/kasir')->assertForbidden();
    }

    public function test_cashier_login_cannot_use_an_admin_account(): void
    {
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $this->post('/kasir/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_customer_online_order_saves_server_priced_items_to_order_history(): void
    {
        StoreSetting::current();
        $category = Category::create(['name' => 'Paket Hemat', 'slug' => 'paket-hemat']);
        $item = MenuItem::create([
            'category_id' => $category->id,
            'name' => 'Paket Ayam Geprek',
            'price' => 13000,
            'is_available' => true,
        ]);
        $customer = User::create([
            'name' => 'Dina Mahasiswa',
            'username' => 'dina',
            'email' => 'dina@example.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->postJson('/orders', [
            'customer_name' => 'Dina',
            'service_method' => 'takeaway',
            'items' => [
                ['id' => $item->id, 'qty' => 2],
            ],
        ]);

        $response->assertCreated()->assertJsonStructure(['order_number', 'whatsapp_url']);
        $order = Order::with('items')->firstOrFail();
        $this->assertSame(27000, $order->total);
        $this->assertSame(26000, $order->subtotal);
        $this->assertSame(1000, $order->packaging_fee);
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->get('/akun')->assertOk()->assertSee($order->order_number);
    }
}
