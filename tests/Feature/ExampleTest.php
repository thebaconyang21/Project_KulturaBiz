<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_creates_an_approved_logged_in_customer(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Maria Customer',
            'email' => 'maria@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'role' => 'customer',
        ]);

        $customer = User::where('email', 'maria@example.com')->firstOrFail();

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customer);
        $this->assertSame('customer', $customer->role);
        $this->assertSame('approved', $customer->status);
    }

    public function test_artisan_registration_waits_for_admin_approval(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Fatima Artisan',
            'email' => 'fatima@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'role' => 'artisan',
            'shop_name' => 'Fatima Weaving',
            'tribe' => 'Maranao',
            'region' => 'Lanao del Sur',
        ]);

        $artisan = User::where('email', 'fatima@example.com')->firstOrFail();

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();
        $this->assertSame('artisan', $artisan->role);
        $this->assertSame('pending', $artisan->status);
    }

    public function test_registration_cannot_assign_the_admin_role(): void
    {
        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Unauthorized Admin',
                'email' => 'not-admin@example.com',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
                'role' => 'admin',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'not-admin@example.com']);
    }

    public function test_roles_are_restricted_to_their_own_dashboard(): void
    {
        $customer = $this->createUser('customer', 'approved');
        $artisan = $this->createUser('artisan', 'approved');
        $admin = $this->createUser('admin', 'approved');

        $this->actingAs($customer)
            ->get(route('artisan.dashboard'))
            ->assertForbidden();
        $this->actingAs($customer)
            ->get(route('admin.users'))
            ->assertForbidden();

        $this->actingAs($artisan)
            ->get(route('admin.users'))
            ->assertForbidden();
        $this->actingAs($artisan)
            ->get(route('artisan.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk();
    }

    public function test_pending_artisan_cannot_open_the_artisan_dashboard(): void
    {
        $artisan = $this->createUser('artisan', 'pending');

        $this->actingAs($artisan)
            ->get(route('artisan.dashboard'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    public function test_artisan_cannot_edit_another_artisans_product(): void
    {
        $artisan = $this->createUser('artisan', 'approved');
        $anotherArtisan = $this->createUser('artisan', 'approved');
        $product = $this->createProduct($anotherArtisan);

        $this->actingAs($artisan)
            ->get(route('artisan.products.edit', $product->id))
            ->assertNotFound();
    }

    public function test_customer_can_add_only_available_quantity_to_cart(): void
    {
        $customer = $this->createUser('customer', 'approved');
        $product = $this->createProduct($this->createUser('artisan', 'approved'), stock: 2);

        $this->actingAs($customer)
            ->from(route('products.show', $product->slug))
            ->post(route('cart.add', $product->id), ['quantity' => 3])
            ->assertRedirect(route('products.show', $product->slug))
            ->assertSessionHas('error', 'Not enough stock available.');

        $this->assertEmpty(session('cart', []));

        $this->post(route('cart.add', $product->id), ['quantity' => 2])
            ->assertSessionHas('success');

        $this->assertSame(2, session('cart')[$product->id]['quantity']);
    }

    public function test_customer_can_review_a_delivered_order_and_product_rating_updates(): void
    {
        $customer = $this->createUser('customer', 'approved');
        $product = $this->createProduct($this->createUser('artisan', 'approved'));
        $order = $this->createOrderWithProduct($customer, $product, 'delivered');

        $this->actingAs($customer)
            ->from(route('orders.mine'))
            ->post(route('orders.review', [$order->id, $product->id]), [
                'rating' => 5,
                'title' => 'Beautiful craft',
                'comment' => 'Excellent quality and careful workmanship.',
            ])
            ->assertRedirect(route('orders.mine'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
        ]);
        $this->assertSame(5.0, $product->fresh()->average_rating);
        $this->assertSame(1, $product->fresh()->review_count);
    }

    public function test_review_is_limited_to_customer_who_bought_item_after_delivery(): void
    {
        $buyer = $this->createUser('customer', 'approved');
        $otherCustomer = $this->createUser('customer', 'approved');
        $product = $this->createProduct($this->createUser('artisan', 'approved'));
        $pendingOrder = $this->createOrderWithProduct($buyer, $product, 'pending');
        $deliveredOrder = $this->createOrderWithProduct($buyer, $product, 'delivered');

        $this->actingAs($buyer)
            ->post(route('orders.review', [$pendingOrder->id, $product->id]), ['rating' => 5])
            ->assertNotFound();

        $this->actingAs($otherCustomer)
            ->post(route('orders.review', [$deliveredOrder->id, $product->id]), ['rating' => 5])
            ->assertNotFound();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_checkout_creates_order_and_decrements_stock(): void
    {
        $customer = $this->createUser('customer', 'approved');
        $product = $this->createProduct($this->createUser('artisan', 'approved'), stock: 3);

        $response = $this->actingAs($customer)
            ->withSession(['cart' => [
                $product->id => [
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 2,
                    'image' => '',
                ],
            ]])
            ->post(route('orders.place'), [
                'recipient_name' => 'Maria Customer',
                'delivery_address' => '123 Test Street',
                'contact_number' => '09123456789',
                'city' => 'Marawi',
                'province' => 'Lanao del Sur',
                'payment_method' => 'cod',
            ]);

        $order = Order::where('user_id', $customer->id)->firstOrFail();

        $response->assertRedirect(route('orders.confirmation', $order->id));
        $this->assertSame('pending', $order->payment_status);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame([], session('cart', []));
    }

    private function createUser(string $role, string $status): User
    {
        static $userNumber = 0;
        $userNumber++;

        return User::create([
            'name' => ucfirst($role) . ' User',
            'email' => "{$role}-{$userNumber}@example.com",
            'password' => 'password',
            'role' => $role,
            'status' => $status,
        ]);
    }

    private function createProduct(User $artisan, int $stock = 5): Product
    {
        $category = Category::create([
            'name' => 'Test Crafts',
            'slug' => uniqid('test-crafts-'),
        ]);

        return Product::create([
            'user_id' => $artisan->id,
            'category_id' => $category->id,
            'name' => 'Test Handmade Craft',
            'slug' => uniqid('test-handmade-craft-'),
            'description' => 'A handmade craft used for workflow tests.',
            'price' => 100,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }

    private function createOrderWithProduct(User $customer, Product $product, string $status): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => uniqid('KB-'),
            'recipient_name' => $customer->name,
            'delivery_address' => '123 Test Street',
            'contact_number' => '09123456789',
            'city' => 'Marawi',
            'province' => 'Lanao del Sur',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => $status,
            'subtotal' => $product->price,
            'shipping_fee' => 10,
            'total_amount' => $product->price + 10,
            'delivered_at' => $status === 'delivered' ? now() : null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        return $order;
    }
}
