<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CulturalStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Artisan\ProductController
 * Handles artisan's product management (CRUD) and order tracking.
 */
class ProductController extends Controller
{
    /**
     * Artisan dashboard.
     */
    public function dashboard()
    {
        $artisan = auth()->user();

        $totalProducts = Product::where('user_id', $artisan->id)->count();
        $activeProducts = Product::where('user_id', $artisan->id)->where('status', 'active')->count();

        $myProductIds = Product::where('user_id', $artisan->id)->pluck('id');

        $totalOrders = OrderItem::whereIn('product_id', $myProductIds)->distinct('order_id')->count('order_id');
        $totalRevenue = OrderItem::whereIn('product_id', $myProductIds)
            ->whereHas('order', fn($q) => $q->where('status', 'delivered'))
            ->sum('subtotal');

        $recentOrders = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $myProductIds))
            ->with(['items' => fn($q) => $q->whereIn('product_id', $myProductIds), 'customer'])
            ->latest()
            ->take(5)
            ->get();

        return view('artisan.dashboard', compact(
            'totalProducts', 'activeProducts', 'totalOrders', 'totalRevenue', 'recentOrders'
        ));
    }

    /**
     * List artisan's products.
     */
    public function index()
    {
        $products = Product::where('user_id', auth()->id())
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('artisan.products.index', compact('products'));
    }

    /**
     * Show create product form.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('artisan.products.create', compact('categories'));
    }

    /**
     * Store a new product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,id',
            'description'         => 'required|string|min:20',
            'price'               => 'required|numeric|min:1',
            'stock'               => 'required|integer|min:0',
            'cultural_background' => 'nullable|string',
            'origin_location'     => 'nullable|string|max:255',
            'materials_used'      => 'nullable|string|max:500',
            'images.*'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }

        $product = Product::create([
            ...$validated,
            'user_id' => auth()->id(),
            'slug'    => $this->generateSlug($validated['name']),
            'images'  => $imagePaths,
            'status'  => 'active',
        ]);

        // Auto-create cultural story if data provided
        if ($request->filled('cultural_background') || $request->filled('tribe_community')) {
            CulturalStory::create([
                'product_id'           => $product->id,
                'user_id'              => auth()->id(),
                'title'                => 'The Story of ' . $product->name,
                'slug'                 => 'story-' . $product->slug,
                'story'                => $request->cultural_background ?? 'A beautiful handmade product from Mindanao.',
                'tribe_community'      => $request->tribe_community ?? auth()->user()->tribe ?? 'Mindanaoan',
                'location'             => $request->origin_location ?? auth()->user()->region ?? 'Mindanao',
                'cultural_significance'=> $request->cultural_significance,
                'is_published'         => true,
            ]);
        }

        return redirect()->route('artisan.products.index')
            ->with('success', "Product '{$product->name}' created successfully!");
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $product    = Product::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $categories = Category::where('is_active', true)->get();
        return view('artisan.products.edit', compact('product', 'categories'));
    }

    /**
     * Update a product.
     */
    public function update(Request $request, int $id)
    {
        $product = Product::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,id',
            'description'         => 'required|string|min:20',
            'price'               => 'required|numeric|min:1',
            'stock'               => 'required|integer|min:0',
            'cultural_background' => 'nullable|string',
            'origin_location'     => 'nullable|string|max:255',
            'materials_used'      => 'nullable|string|max:500',
            'status'              => 'required|in:active,inactive,out_of_stock',
            'images.*'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle new image uploads
        $imagePaths = $product->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }

        $product->update([
            ...$validated,
            'images' => $imagePaths,
        ]);

        return redirect()->route('artisan.products.index')
            ->with('success', "Product '{$product->name}' updated!");
    }

    /**
     * Delete a product.
     */
    public function destroy(int $id)
    {
        $product = Product::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Delete stored images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    /**
     * View orders for artisan's products.
     */
    public function orders()
    {
        $myProductIds = Product::where('user_id', auth()->id())->pluck('id');

        $orders = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $myProductIds))
            ->with(['items' => fn($q) => $q->whereIn('product_id', $myProductIds)->with('product'), 'customer'])
            ->latest()
            ->paginate(15);

        return view('artisan.orders', compact('orders'));
    }

    /**
     * Update order status (artisan can set to processing).
     */
    public function updateOrderStatus(Request $request, int $orderId)
    {
        $request->validate(['status' => 'required|in:processing,cancelled']);

        $myProductIds = Product::where('user_id', auth()->id())->pluck('id');
        $order = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $myProductIds))
            ->findOrFail($orderId);

        $data = ['status' => $request->status];
        if ($request->status === 'processing') {
            $data['processing_at'] = now();
        }

        $order->update($data);
        return back()->with('success', "Order #{$order->order_number} marked as {$request->status}.");
    }

    /**
     * Generate a unique slug from name.
     */
    private function generateSlug(string $name): string
    {
        $slug = str()->slug($name);
        $count = Product::where('slug', 'like', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}