<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase3BTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_product_configuration_and_assignment(): void
    {
        $admin = $this->user('admin');
        $item = $this->item();

        $this->actingAs($admin)->post('/admin/modifier-groups', [
            'name' => 'Sambal',
            'slug' => 'sambal',
            'min_selections' => 1,
            'max_selections' => 1,
            'sort_order' => 1,
            'is_required' => 1,
            'is_active' => 1,
        ])->assertRedirect();
        $group = ModifierGroup::firstOrFail();

        $this->actingAs($admin)->post("/admin/modifier-groups/{$group->id}/options", [
            'name' => 'Original',
            'price_delta' => 1000,
            'sort_order' => 1,
            'is_available' => 1,
        ])->assertRedirect();
        $this->actingAs($admin)->put("/admin/items/{$item->id}/modifier-groups", [
            'modifier_groups' => [['id' => $group->id]],
        ])->assertRedirect();
        $this->actingAs($admin)->post('/admin/variants', [
            'menu_item_id' => $item->id,
            'name' => 'Large',
            'price' => 30000,
            'sort_order' => 1,
            'is_available' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('modifier_options', ['name' => 'Original', 'price_delta' => 1000]);
        $this->assertTrue($item->fresh()->modifierGroups->contains($group));
        $pivot = $item->fresh()->modifierGroups->find($group->id)->pivot;
        $this->assertTrue((bool) $pivot->is_required);
        $this->assertSame(1, (int) $pivot->min_selections);
        $this->assertSame(1, ProductVariant::where('menu_item_id', $item->id)->count());
    }

    public function test_non_admin_cannot_manage_product_configuration(): void
    {
        $this->actingAs($this->user('kitchen'))->get('/admin/product-configuration')->assertForbidden();
    }

    public function test_admin_can_search_orders_and_cancel_with_history(): void
    {
        $admin = $this->user('admin');
        $order = Order::create([
            'order_number' => 'GLO-3B-0001',
            'customer_name' => 'Dewi',
            'customer_phone' => '0812',
            'service_method' => 'takeaway',
            'subtotal' => 20000,
            'total' => 21000,
            'packaging_fee' => 1000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->get('/admin/orders?search=3B-0001')->assertOk()->assertSee('Dewi');
        $this->actingAs($admin)->patch("/admin/orders/{$order->id}/transition", [
            'status' => 'cancelled',
            'cancellation_reason' => 'Bahan tidak tersedia',
        ])->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'cancelled',
            'note' => 'Bahan tidak tersedia',
        ]);
    }

    public function test_kitchen_staff_can_process_confirmed_orders_but_not_admin_orders(): void
    {
        $kitchen = $this->user('kitchen');
        $order = Order::create([
            'order_number' => 'GLO-3B-0002',
            'customer_name' => 'Rina',
            'service_method' => 'dine_in',
            'subtotal' => 10000,
            'total' => 10000,
            'status' => 'confirmed',
            'kitchen_status' => 'queued',
        ]);

        $this->actingAs($kitchen)->get('/kitchen/feed')->assertOk()->assertJsonPath('new.0.order_number', 'GLO-3B-0002');
        $this->actingAs($kitchen)->post("/kitchen/orders/{$order->id}/status", ['status' => 'preparing'])->assertRedirect();
        $this->actingAs($kitchen)->post("/kitchen/orders/{$order->id}/status", ['status' => 'ready'])->assertRedirect();
        $this->assertSame('ready', $order->fresh()->status);
        $this->actingAs($kitchen)->get('/admin/product-configuration')->assertForbidden();
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'username' => $role . '-user',
            'email' => $role . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function item(): MenuItem
    {
        $category = Category::create(['name' => 'Paket', 'slug' => 'paket']);

        return MenuItem::create([
            'category_id' => $category->id,
            'name' => 'Paket Test',
            'price' => 25000,
            'is_available' => true,
        ]);
    }
}
