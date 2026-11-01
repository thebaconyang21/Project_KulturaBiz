<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        $product = $this->createProduct();

        $this->actingAs($user)
            ->withSession(['cart' => [
                $product->id => [
                    'name' => $product->name,
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

    public function test_checkout_refreshes_prices_from_the_product_record(): void
    {
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $product->update(['price' => 125]);

        $this->actingAs($user)
            ->withSession(['cart' => [
                $product->id => [
                    'name' => 'Old product name',
                    'price' => 1,
                    'quantity' => 2,
                    'image' => '',
                    'artisan' => 'Old artisan name',
                ],
            ]])
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('₱250.00')
            ->assertDontSee('Old product name');

        $this->assertSame(125.0, session('cart')[$product->id]['price']);
        $this->assertSame($product->name, session('cart')[$product->id]['name']);
    }

    public function test_order_uses_current_database_price_and_reserves_stock(): void
    {
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $product->update(['price' => 125]);

        $response = $this->actingAs($user)
            ->withSession(['cart' => [
                $product->id => [
                    'name' => 'Stale product name',
                    'price' => 1,
                    'quantity' => 2,
                    'image' => '',
                ],
            ]])
            ->post(route('orders.place'), $this->validOrderDetails());

        $order = Order::latest('id')->firstOrFail();

        $response->assertRedirect(route('orders.confirmation', $order->id));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 250,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 125,
            'quantity' => 2,
            'subtotal' => 250,
        ]);
        $this->assertSame(3, $product->fresh()->stock);
        $response->assertSessionMissing('cart');
    }

    public function test_checkout_rejects_stock_that_changed_after_the_checkout_page_loaded(): void
    {
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $cart = [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 3,
                'image' => '',
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->get(route('checkout'))
            ->assertOk();

        $product->update(['stock' => 2]);

        $this->from(route('checkout'))
            ->post(route('orders.place'), $this->validOrderDetails())
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertNotEmpty(session('cart'));
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

    public function test_valid_payment_webhook_marks_order_paid(): void
    {
        config([
            'services.paymongo.use_real' => true,
            'services.paymongo.webhook_secret' => 'test-webhook-secret',
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'gcash', 'paid-intent');
        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'payment.paid',
                    'data' => ['id' => 'paid-intent'],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->signedWebhook($payload)
            ->assertOk()
            ->assertExactJson(['received' => true]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_valid_failed_payment_webhook_marks_order_failed(): void
    {
        config([
            'services.paymongo.use_real' => true,
            'services.paymongo.webhook_secret' => 'test-webhook-secret',
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'gcash', 'failed-intent');
        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'payment.failed',
                    'data' => ['id' => 'failed-intent'],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->signedWebhook($payload)->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'failed',
        ]);
    }

    public function test_invalid_payment_webhook_signature_is_rejected(): void
    {
        config([
            'services.paymongo.use_real' => true,
            'services.paymongo.webhook_secret' => 'test-webhook-secret',
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'gcash', 'untrusted-intent');
        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'payment.paid',
                    'data' => ['id' => 'untrusted-intent'],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', route('webhooks.paymongo'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => 'invalid',
        ], $payload)->assertUnauthorized();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_real_payment_creation_failure_does_not_fall_back_to_demo_payment(): void
    {
        config([
            'services.paymongo.use_real' => true,
            'services.paymongo.secret_key' => 'test-secret',
        ]);
        Http::fake([
            'https://api.paymongo.com/v1/payment_intents' => Http::response(['errors' => []], 500),
        ]);

        $user = $this->createCustomer();
        $order = $this->createOrder($user, 'gcash');

        $result = app(PaymentService::class)->createPaymentIntent($order);

        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
        $this->assertNull($result['payment_intent_id'] ?? null);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.paymongo.com/v1/payment_intents');
        Http::assertSentCount(1);
    }

    private function signedWebhook(string $payload)
    {
        $signature = hash_hmac('sha256', $payload, 'test-webhook-secret');

        return $this->call('POST', route('webhooks.paymongo'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => $signature,
        ], $payload);
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

    private function createProduct(): Product
    {
        $artisan = User::create([
            'name' => 'Test Artisan',
            'email' => uniqid('artisan-') . '@example.com',
            'password' => 'password',
            'role' => 'artisan',
            'status' => 'approved',
        ]);
        $category = Category::create([
            'name' => 'Test Crafts',
            'slug' => uniqid('test-crafts-'),
        ]);

        return Product::create([
            'user_id' => $artisan->id,
            'category_id' => $category->id,
            'name' => 'Demo item',
            'slug' => uniqid('demo-item-'),
            'description' => 'A sample handmade item for checkout tests.',
            'price' => 100,
            'stock' => 5,
            'status' => 'active',
        ]);
    }

    private function validOrderDetails(): array
    {
        return [
            'recipient_name' => 'Test Customer',
            'delivery_address' => '123 Test Street',
            'contact_number' => '09123456789',
            'city' => 'Marawi',
            'province' => 'Lanao del Sur',
            'payment_method' => 'cod',
        ];
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
