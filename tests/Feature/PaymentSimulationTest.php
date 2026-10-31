<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_shows_maya_only_when_a_payment_mode_is_enabled(): void
    {
        config([
            'services.paymongo.use_real' => false,
            'services.paymongo.simulation_enabled' => true,
        ]);

        $user = $this->createCustomer();

        $this->actingAs($user)
            ->withSession(['cart' => [
                1 => [
                    'name' => 'Demo item',
                    'price' => 100,
                    'quantity' => 1,
                    'image' => '',
                ],
            ]])
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('Maya')
            ->assertSee('Demo only — no money will be charged');
    }

    public function test_demo_payment_does_not_mark_order_as_paid(): void
    {
        config([
            'services.paymongo.use_real' => false,
            'services.paymongo.simulation_enabled' => true,
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'maya');

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('payments.process', $order->id));

        $response->assertOk()
            ->assertSee('Order Placed — Payment Pending')
            ->assertSee('Demo only: no payment was processed. This order remains unpaid.');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_method' => 'maya',
            'payment_status' => 'pending',
            'payment_intent_id' => null,
        ]);
    }

    public function test_demo_payment_routes_are_unavailable_when_simulation_is_disabled(): void
    {
        config([
            'services.paymongo.use_real' => false,
            'services.paymongo.simulation_enabled' => false,
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'gcash');

        $this->actingAs($user)
            ->post(route('payments.process', $order->id))
            ->assertNotFound();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_demo_payment_page_is_clearly_labeled_and_collects_no_payment_details(): void
    {
        config([
            'services.paymongo.use_real' => false,
            'services.paymongo.simulation_enabled' => true,
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'bank_transfer');

        $this->actingAs($user)
            ->get(route('payments.simulate', $order->id))
            ->assertOk()
            ->assertSee('DEMO ONLY — NO REAL PAYMENT')
            ->assertSee('Do not enter payment details or send money.')
            ->assertDontSee('Account No');
    }

    public function test_demo_webhook_cannot_mark_an_order_as_paid(): void
    {
        config([
            'services.paymongo.use_real' => false,
            'services.paymongo.simulation_enabled' => true,
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'gcash', 'demo-intent');

        $this->postJson(route('webhooks.paymongo'), [
            'data' => [
                'attributes' => [
                    'type' => 'payment.paid',
                    'data' => ['id' => 'demo-intent'],
                ],
            ],
        ])->assertNotFound();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
        ]);
    }

    private function createCustomer(): User
    {
        return User::create([
            'name' => 'Test Customer',
            'email' => uniqid('customer-') . '@example.com',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'approved',
        ]);
    }

    private function createOrder(User $user, string $paymentMethod, ?string $intentId = null): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'order_number' => uniqid('KB-'),
            'recipient_name' => 'Test Customer',
            'delivery_address' => '123 Test Street',
            'contact_number' => '09123456789',
            'city' => 'Marawi',
            'province' => 'Lanao del Sur',
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'payment_intent_id' => $intentId,
            'status' => 'pending',
            'subtotal' => 100,
            'shipping_fee' => 10,
            'total_amount' => 110,
        ]);
    }
}
