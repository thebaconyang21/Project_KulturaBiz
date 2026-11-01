<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_product_detail_page_remains_publicly_accessible(): void
    {
        $product = $this->createProduct('active');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_inactive_product_detail_page_is_not_publicly_accessible(): void
    {
        $product = $this->createProduct('inactive');

        $this->get(route('products.show', $product->slug))
            ->assertNotFound();
    }

    private function createProduct(string $status): Product
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
            'name' => 'Visibility Test Craft',
            'slug' => uniqid('visibility-test-craft-'),
            'description' => 'A sample handmade item for product visibility tests.',
            'price' => 100,
            'stock' => 5,
            'status' => $status,
        ]);
    }
}
