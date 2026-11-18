<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCustomizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_modifier_is_validated_and_snapshotted_from_database_price(): void
    {
        $item = $this->customizableItem();
        $group = ModifierGroup::create([
            'name' => 'Sambal',
            'slug' => 'sambal',
            'is_required' => true,
            'min_selections' => 1,
            'max_selections' => 1,
            'is_active' => true,
        ]);
        $option = ModifierOption::create([
            'modifier_group_id' => $group->id,
            'name' => 'Sambal bawang',
            'price_delta' => 3000,
            'is_available' => true,
        ]);
        $item->modifierGroups()->attach($group);

        $this->postJson('/orders', $this->orderPayload($item, []))->assertStatus(422);

        $response = $this->postJson('/orders', $this->orderPayload($item, [$option->id]));
        $response->assertCreated()->assertJsonStructure(['order_number', 'tracking_url']);

        $order = Order::with('items.modifiers')->latest()->first();
        $this->assertSame(29000, $order->total);
        $this->assertSame(3000, $order->items->first()->modifier_total);
        $this->assertSame('Sambal bawang', $order->items->first()->modifiers->first()->option_name);
    }

    public function test_guest_tracking_requires_the_issued_token(): void
    {
        $item = $this->customizableItem();
        $response = $this->postJson('/orders', $this->orderPayload($item, []))->assertCreated();
        $trackingUrl = $response->json('tracking_url');

        $this->get($trackingUrl)->assertOk()->assertSee($response->json('order_number'));
        $this->get('/orders/' . $response->json('order_number') . '/track/invalid-token')->assertNotFound();
    }

    public function test_disabled_modifier_is_not_accepted_for_a_new_order(): void
    {
        $item = $this->customizableItem();
        $group = ModifierGroup::create([
            'name' => 'Sambal',
            'slug' => 'sambal-disabled',
            'is_required' => false,
            'min_selections' => 0,
            'max_selections' => 1,
            'is_active' => true,
        ]);
        $option = ModifierOption::create([
            'modifier_group_id' => $group->id,
            'name' => 'Sambal ijo',
            'price_delta' => 500,
            'is_available' => true,
        ]);
        $item->modifierGroups()->attach($group);
        $option->update(['is_available' => false]);

        $this->postJson('/orders', $this->orderPayload($item, [$option->id]))->assertStatus(422);
    }

    public function test_tracking_token_cannot_access_another_order(): void
    {
        $item = $this->customizableItem();
        $first = $this->postJson('/orders', $this->orderPayload($item, []))->assertCreated();
        $second = $this->postJson('/orders', $this->orderPayload($item, []))->assertCreated();

        $firstUrl = parse_url($first->json('tracking_url'), PHP_URL_PATH);
        $parts = explode('/', trim($firstUrl, '/'));
        $token = end($parts);

        $secondNumber = $second->json('order_number');
        $this->get('/orders/' . $secondNumber . '/track/' . $token)->assertNotFound();
    }

    private function customizableItem(): MenuItem
    {
        $category = Category::create(['name' => 'Paket', 'slug' => 'paket']);

        return MenuItem::create([
            'category_id' => $category->id,
            'name' => 'Paket Ayam',
            'price' => 25000,
            'is_available' => true,
        ]);
    }

    private function orderPayload(MenuItem $item, array $optionIds): array
    {
        return [
            'customer_name' => 'Guest Tester',
            'customer_phone' => '08123456789',
            'service_method' => 'takeaway',
            'items' => [[
                'id' => $item->id,
                'qty' => 1,
                'price' => 1,
                'modifier_option_ids' => $optionIds,
            ]],
        ];
    }
}
