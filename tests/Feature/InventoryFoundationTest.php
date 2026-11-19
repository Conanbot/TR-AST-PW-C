<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RawMaterial;
use App\Models\Recipe;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_material_and_record_audited_stock_in(): void
    {
        $material = RawMaterial::create([
            'name' => 'Keju',
            'purchase_unit' => 'kg',
            'usage_unit' => 'gram',
            'conversion_factor' => 1000,
            'minimum_stock' => 100,
            'cost_per_purchase_unit' => 90000,
            'is_active' => true,
        ]);

        app(InventoryService::class)->recordMovement($material, 'stock_in', 500, 'Penerimaan bahan awal');

        $this->assertDatabaseHas('raw_materials', ['id' => $material->id, 'current_stock' => 500]);
        $this->assertDatabaseHas('stock_movements', [
            'raw_material_id' => $material->id,
            'movement_type' => 'stock_in',
            'balance_after' => 500,
        ]);
    }

    public function test_movement_idempotency_does_not_apply_stock_twice(): void
    {
        $material = RawMaterial::create([
            'name' => 'Minyak',
            'purchase_unit' => 'liter',
            'usage_unit' => 'ml',
            'conversion_factor' => 1000,
            'minimum_stock' => 100,
            'cost_per_purchase_unit' => 18000,
            'is_active' => true,
        ]);
        $service = app(InventoryService::class);

        $first  = $service->recordMovement($material, 'stock_in', 1000, 'Penerimaan PO', null, null, 'po-receive-1');
        $second = $service->recordMovement($material->refresh(), 'stock_in', 1000, 'Penerimaan PO retry', null, null, 'po-receive-1');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, StockMovement::where('idempotency_key', 'po-receive-1')->count());
        $this->assertEquals(1000, (float) $material->refresh()->current_stock);
    }

    public function test_stock_out_cannot_make_balance_negative(): void
    {
        $material = RawMaterial::create([
            'name' => 'Ayam',
            'purchase_unit' => 'kg',
            'usage_unit' => 'gram',
            'conversion_factor' => 1000,
            'current_stock' => 200,
            'minimum_stock' => 50,
            'cost_per_purchase_unit' => 45000,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        app(InventoryService::class)->recordMovement($material, 'stock_out', -201, 'Pengeluaran produksi');
    }

    /**
     * Since Inventory integration was removed from OrderStatusService,
     * transitioning to 'preparing' no longer deducts stock.
     * Orders must still be able to reach 'preparing' without any stock validation.
     */
    public function test_preparing_transition_does_not_consume_stock(): void
    {
        $category = Category::create(['name' => 'Makanan', 'slug' => 'makanan', 'sort_order' => 1]);
        $menuItem = MenuItem::create([
            'category_id' => $category->id,
            'name'        => 'Ayam Geprek',
            'price'       => 18000,
            'is_available' => true,
        ]);
        $material = RawMaterial::create([
            'name'                   => 'Ayam',
            'purchase_unit'          => 'kg',
            'usage_unit'             => 'gram',
            'conversion_factor'      => 1000,
            'current_stock'          => 1000,
            'minimum_stock'          => 100,
            'cost_per_purchase_unit' => 45000,
            'is_active'              => true,
        ]);
        // Recipe exists but should NOT be consumed anymore
        Recipe::create(['menu_item_id' => $menuItem->id, 'raw_material_id' => $material->id, 'quantity' => 250, 'unit' => 'gram']);

        $order = Order::create([
            'customer_name'  => 'Guest',
            'service_method' => 'takeaway',
            'subtotal'       => 18000,
            'total'          => 18000,
            'status'         => 'confirmed',
            'payment_status' => 'unpaid',
            'source'         => 'online',
        ]);
        $order->items()->create([
            'menu_item_id' => $menuItem->id,
            'item_name'    => $menuItem->name,
            'quantity'     => 2,
            'unit_price'   => 18000,
            'base_price'   => 18000,
            'line_total'   => 36000,
        ]);

        // Should succeed without throwing — no stock validation
        app(\App\Services\OrderStatusService::class)->transition($order, 'preparing');

        $this->assertSame('preparing', $order->fresh()->status);
        // Stock must NOT have changed
        $this->assertEquals(1000, (float) $material->refresh()->current_stock);
        // No order_consumption movement should exist
        $this->assertSame(0, StockMovement::where('movement_type', 'order_consumption')->count());
    }
}
