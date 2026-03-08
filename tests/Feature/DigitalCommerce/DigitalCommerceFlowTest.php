<?php

namespace Tests\Feature\DigitalCommerce;

use App\Models\Admin;
use App\Models\Digital\DigitalCategory;
use App\Models\Digital\DigitalDeliveryPayload;
use App\Models\Digital\DigitalOrder;
use App\Models\Digital\DigitalOrderItem;
use App\Models\Digital\DigitalProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DigitalCommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_to_cart_updates_session(): void
    {
        $category = DigitalCategory::create([
            'name' => 'Streaming',
            'slug' => 'streaming',
        ]);

        $product = DigitalProduct::create([
            'digital_category_id' => $category->id,
            'title' => 'Netflix 1 Month',
            'slug' => 'netflix-1-month',
            'price' => 12.99,
            'currency' => 'USD',
            'delivery_type' => 'credential',
        ]);

        $response = $this->post(route('digital.cart.add', $product), ['quantity' => 2]);

        $response->assertRedirect();
        $response->assertSessionHas('digital_cart');

        $cart = session('digital_cart', []);
        $this->assertSame(2, $cart[$product->id]['quantity']);
    }

    public function test_checkout_creates_digital_order_and_items(): void
    {
        $product = DigitalProduct::create([
            'title' => 'Amazon Prime',
            'slug' => 'amazon-prime',
            'price' => 18.50,
            'currency' => 'USD',
            'delivery_type' => 'code',
        ]);

        $this->post(route('digital.cart.add', $product), ['quantity' => 1]);

        $response = $this->post(route('digital.checkout.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567',
            'payment_method' => 'manual',
            'notes' => 'Test order',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('digital_orders', [
            'customer_email' => 'john@example.com',
            'payment_status' => 'unpaid',
        ]);

        $order = DigitalOrder::query()->where('customer_email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertDatabaseHas('digital_order_items', [
            'digital_order_id' => $order->id,
            'digital_product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_admin_can_assign_delivery_payload_to_item(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin+' . Str::random(5) . '@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $product = DigitalProduct::create([
            'title' => 'NordVPN',
            'slug' => 'nordvpn',
            'price' => 5.00,
            'currency' => 'USD',
            'delivery_type' => 'credential',
        ]);

        $order = DigitalOrder::create([
            'order_number' => 'DG-TEST-123',
            'customer_name' => 'Alex',
            'customer_email' => 'alex@example.com',
            'payment_method' => 'manual',
            'currency' => 'USD',
            'subtotal' => 5,
            'discount' => 0,
            'total' => 5,
            'status' => 'paid',
            'payment_status' => 'paid',
            'customer_access_token' => Str::random(64),
        ]);

        $item = DigitalOrderItem::create([
            'digital_order_id' => $order->id,
            'digital_product_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => 5,
            'quantity' => 1,
            'line_total' => 5,
        ]);

        $payload = DigitalDeliveryPayload::create([
            'digital_product_id' => $product->id,
            'payload_type' => 'credential',
            'payload' => ['username' => 'acc1', 'password' => 'pass1'],
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.digital-orders.assign-delivery', [$order, $item]), ['payload_id' => $payload->id]);

        $response->assertRedirect();

        $this->assertDatabaseHas('digital_order_items', [
            'id' => $item->id,
            'delivery_payload_id' => $payload->id,
            'delivery_status' => 'assigned',
        ]);

        $this->assertDatabaseHas('digital_delivery_payloads', [
            'id' => $payload->id,
            'is_assigned' => 1,
            'assigned_order_item_id' => $item->id,
        ]);
    }

    public function test_customer_can_only_view_own_order_data(): void
    {
        $orderA = DigitalOrder::create([
            'order_number' => 'DG-OWN-1',
            'customer_name' => 'Owner',
            'customer_email' => 'owner@example.com',
            'payment_method' => 'manual',
            'currency' => 'USD',
            'subtotal' => 10,
            'discount' => 0,
            'total' => 10,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'customer_access_token' => Str::random(64),
        ]);

        $orderB = DigitalOrder::create([
            'order_number' => 'DG-OTHER-1',
            'customer_name' => 'Other',
            'customer_email' => 'other@example.com',
            'payment_method' => 'manual',
            'currency' => 'USD',
            'subtotal' => 12,
            'discount' => 0,
            'total' => 12,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'customer_access_token' => Str::random(64),
        ]);

        $this->withSession(['digital_customer_email' => 'owner@example.com'])
            ->get(route('digital.orders.show', $orderA))
            ->assertOk();

        $this->withSession(['digital_customer_email' => 'owner@example.com'])
            ->get(route('digital.orders.show', $orderB))
            ->assertForbidden();
    }
}
