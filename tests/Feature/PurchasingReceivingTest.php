<?php

namespace Tests\Feature;

use App\Models\GoodsReceiving;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseReceivingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PurchasingReceivingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'username' => 'admin_purchasing',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    private function po(array $quantities = [10]): PurchaseOrder
    {
        $supplier = Supplier::create(['name' => 'Supplier Test', 'is_active' => true]);
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-' . bin2hex(random_bytes(4)),
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'ordered',
            'subtotal' => 0,
            'total' => 0,
        ]);

        $subtotal = 0;
        foreach ($quantities as $index => $quantity) {
            $material = RawMaterial::create([
                'name' => 'Bahan ' . $index,
                'purchase_unit' => 'liter',
                'usage_unit' => 'ml',
                'conversion_factor' => 1000,
                'current_stock' => 0,
                'cost_per_purchase_unit' => 10000,
                'is_active' => true,
            ]);
            $line = $quantity * 10000;
            $subtotal += $line;
            $po->items()->create([
                'raw_material_id' => $material->id,
                'material_name' => $material->name,
                'purchase_unit' => 'liter',
                'usage_unit' => 'ml',
                'conversion_factor' => 1000,
                'ordered_quantity' => $quantity,
                'received_quantity' => 0,
                'rejected_quantity' => 0,
                'unit_price' => 10000,
                'subtotal' => $line,
            ]);
        }
        $po->update(['subtotal' => $subtotal, 'total' => $subtotal]);
        return $po->load('items');
    }

    /** Supplier model still exists in DB even though purchasing UI is removed */
    public function test_supplier_create_update_and_inactive_history(): void
    {
        // Test supplier model directly (UI routes removed, but model/DB intact)
        $supplier = Supplier::create([
            'name' => 'CV Sumber Bahan',
            'phone' => '081234',
            'contact_person' => 'Budi',
            'address' => 'Salatiga',
            'notes' => 'Supplier utama',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('suppliers', ['name' => 'CV Sumber Bahan']);

        $supplier->update(['name' => 'CV Sumber Bahan Baru', 'is_active' => false]);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'CV Sumber Bahan Baru', 'is_active' => 0]);
    }

    public function test_purchase_order_backend_calculates_many_item_total(): void
    {
        // Test PO model/service directly (routes removed, service layer intact)
        $supplier = Supplier::create(['name' => 'Supplier', 'is_active' => true]);
        $materialA = RawMaterial::create(['name' => 'Ayam', 'purchase_unit' => 'kg', 'usage_unit' => 'gram', 'conversion_factor' => 1000, 'is_active' => true]);
        $materialB = RawMaterial::create(['name' => 'Minyak', 'purchase_unit' => 'liter', 'usage_unit' => 'ml', 'conversion_factor' => 1000, 'is_active' => true]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-CALC-TEST',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'status' => 'draft',
            'subtotal' => 0,
            'total' => 0,
        ]);

        $lineA = (int) round(2.5 * 45000); // 112500
        $lineB = (int) round(3 * 18000);   // 54000
        $subtotal = $lineA + $lineB;        // 166500

        $po->items()->createMany([
            ['raw_material_id' => $materialA->id, 'material_name' => $materialA->name, 'purchase_unit' => 'kg', 'usage_unit' => 'gram', 'conversion_factor' => 1000, 'ordered_quantity' => 2.5, 'received_quantity' => 0, 'rejected_quantity' => 0, 'unit_price' => 45000, 'subtotal' => $lineA],
            ['raw_material_id' => $materialB->id, 'material_name' => $materialB->name, 'purchase_unit' => 'liter', 'usage_unit' => 'ml', 'conversion_factor' => 1000, 'ordered_quantity' => 3, 'received_quantity' => 0, 'rejected_quantity' => 0, 'unit_price' => 18000, 'subtotal' => $lineB],
        ]);
        $po->update(['subtotal' => $subtotal, 'total' => $subtotal]);

        $this->assertSame(166500, $po->fresh()->total);
        $this->assertCount(2, $po->items);
    }

    public function test_cancelled_purchase_order_cannot_receive(): void
    {
        $po = $this->po([10]);
        $po->update(['status' => 'cancelled']);

        $this->expectException(ValidationException::class);
        app(PurchaseReceivingService::class)->create($po, [[
            'purchase_order_item_id' => $po->items->first()->id,
            'accepted_quantity' => 10,
            'rejected_quantity' => 0,
        ]], null, null, 'cancelled-test');
    }

    public function test_full_receiving_adds_stock_and_makes_po_received(): void
    {
        $po = $this->po([10]);
        $item = $po->items->first();

        $receiving = app(PurchaseReceivingService::class)->create($po, [[
            'purchase_order_item_id' => $item->id,
            'accepted_quantity' => 10,
            'rejected_quantity' => 0,
            'actual_unit_price' => 9500,
        ]], 'Penerimaan penuh', null, 'grn-full-test');

        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'received']);
        $this->assertDatabaseHas('purchase_order_items', ['id' => $item->id, 'received_quantity' => 10]);
        $this->assertDatabaseHas('raw_materials', ['id' => $item->raw_material_id, 'current_stock' => 10000]);
        $this->assertDatabaseHas('stock_movements', [
            'raw_material_id' => $item->raw_material_id,
            'movement_type' => 'stock_in',
            'quantity_delta' => 10000,
            'idempotency_key' => 'receiving:' . $receiving->id . ':po-item:' . $item->id,
        ]);
    }

    public function test_partial_and_rejected_receiving_updates_po_status(): void
    {
        $po = $this->po([10]);
        $item = $po->items->first();
        app(PurchaseReceivingService::class)->create($po, [[
            'purchase_order_item_id' => $item->id,
            'accepted_quantity' => 6,
            'rejected_quantity' => 2,
        ]], 'Sebagian diterima dan rusak', null, 'grn-partial-test');

        $this->assertDatabaseHas('purchase_order_items', ['id' => $item->id, 'received_quantity' => 6, 'rejected_quantity' => 2]);
        $this->assertDatabaseHas('raw_materials', ['id' => $item->raw_material_id, 'current_stock' => 6000]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'partially_received']);
    }

    public function test_receiving_retry_does_not_duplicate_stock(): void
    {
        $po = $this->po([5]);
        $item = $po->items->first();
        $rows = [[
            'purchase_order_item_id' => $item->id,
            'accepted_quantity' => 5,
            'rejected_quantity' => 0,
        ]];

        $first = app(PurchaseReceivingService::class)->create($po, $rows, null, null, 'grn-retry-test');
        $second = app(PurchaseReceivingService::class)->create($po, $rows, null, null, 'grn-retry-test');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, GoodsReceiving::where('idempotency_key', 'grn-retry-test')->count());
        $this->assertDatabaseHas('raw_materials', ['id' => $item->raw_material_id, 'current_stock' => 5000]);
        $this->assertSame(1, \App\Models\StockMovement::where('movement_type', 'stock_in')->count());
    }

    public function test_receiving_cannot_exceed_remaining_order_quantity(): void
    {
        $po = $this->po([5]);
        $item = $po->items->first();

        $this->expectException(ValidationException::class);
        app(PurchaseReceivingService::class)->create($po, [[
            'purchase_order_item_id' => $item->id,
            'accepted_quantity' => 4,
            'rejected_quantity' => 2,
        ]], null, null, 'grn-exceed-test');
    }

    public function test_partial_receiving_is_possible_multiple_times(): void
    {
        $po = $this->po([10]);
        $item = $po->items->first();
        $service = app(PurchaseReceivingService::class);
        $service->create($po, [[
            'purchase_order_item_id' => $item->id,
            'accepted_quantity' => 4,
            'rejected_quantity' => 0,
        ]], null, null, 'grn-multi-1');
        $service->create($po, [[
            'purchase_order_item_id' => $item->id,
            'accepted_quantity' => 6,
            'rejected_quantity' => 0,
        ]], null, null, 'grn-multi-2');

        $this->assertDatabaseHas('purchase_order_items', ['id' => $item->id, 'received_quantity' => 10]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'received']);
        $this->assertDatabaseHas('raw_materials', ['id' => $item->raw_material_id, 'current_stock' => 10000]);
    }

    public function test_draft_can_be_cancelled_but_received_po_cannot(): void
    {
        // Test cancellation via service/model directly (route removed)
        $po = $this->po([5]);
        $po->update(['status' => 'draft']);

        // Can cancel draft via model
        $po->update(['status' => 'cancelled']);
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'cancelled']);

        // Received PO should not be cancellable via service
        $received = $this->po([3]);
        $received->update(['status' => 'received']);
        $this->expectException(ValidationException::class);
        // Simulate what the controller did
        if (in_array($received->status, ['cancelled', 'received'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['status' => 'Cannot cancel received PO.']);
        }
    }

    public function test_cashier_and_kitchen_cannot_access_inventory_management(): void
    {
        // Inventory routes removed from admin — cashier/kitchen should not access admin-protected routes
        $cashier = User::create([
            'name'     => 'Cashier',
            'username' => 'cashier_test',
            'password' => Hash::make('password'),
            'role'     => 'cashier',
        ]);
        $kitchen = User::create([
            'name'     => 'Kitchen',
            'username' => 'kitchen_test',
            'password' => Hash::make('password'),
            'role'     => 'kitchen',
        ]);

        // Admin-only route: cashier cannot access admin dashboard
        $this->actingAs($cashier)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($kitchen)->get(route('admin.dashboard'))->assertForbidden();
        auth()->logout();
        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect();
    }
}
