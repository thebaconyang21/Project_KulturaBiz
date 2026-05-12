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
 * Handles artisan dashboard, product management, and order tracking.
 */
class ProductController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────
    public function dashboard()
    {
        $artisan      = auth()->user();
        $myProductIds = Product::where('user_id', $artisan->id)->pluck('id');

        // Stat cards
        $totalProducts  = Product::where('user_id', $artisan->id)->count();
        $activeProducts = Product::where('user_id', $artisan->id)->where('status', 'active')->count();

        $totalOrders = OrderItem::whereIn('product_id', $myProductIds)
            ->distinct('order_id')
            ->count('order_id');

        // Total revenue — all delivered orders ever
        $totalRevenue = OrderItem::whereIn('product_id', $myProductIds)
            ->whereHas('order', fn($q) => $q->where('status', 'delivered'))
            ->sum('subtotal');

        // TODAY'S revenue — delivered orders placed today
        $todayRevenue = OrderItem::whereIn('product_id', $myProductIds)
            ->whereHas('order', fn($q) => $q
                ->where('status', 'delivered')
                ->whereDate('created_at', today())
            )
            ->sum('subtotal');

        // Recent orders
        $recentOrders = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $myProductIds))
            ->with([
                'items' => fn($q) => $q->whereIn('product_id', $myProductIds),
                'customer'
            ])
            ->latest()
            ->take(5)
            ->get();

        // Stock status data
        $allProducts = Product::where('user_id', $artisan->id)
            ->with('category')
            ->orderBy('stock', 'asc')
            ->get();

        $inStockCount    = $allProducts->where('stock', '>', 5)->count();
        $lowStockCount   = $allProducts->whereBetween('stock', [1, 5])->count();
        $outOfStockCount = $allProducts->where('stock', 0)->count();

        return view('artisan.dashboard', compact(
            'totalProducts',
            'activeProducts',
            'totalOrders',
            'totalRevenue',
            'todayRevenue',
            'recentOrders',
            'allProducts',
            'inStockCount',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // PRODUCT MANAGEMENT
    // ─────────────────────────────────────────────────────────
    public function index()
    {
        $products = Product::where('user_id', auth()->id())
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('artisan.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('artisan.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'category_id'          => 'required|exists:categories,id',
            'description'          => 'required|string|min:20',
            'price'                => 'required|numeric|min:1',
            'stock'                => 'required|integer|min:0',
            'cultural_background'  => 'nullable|string',
            'origin_location'      => 'nullable|string|max:255',
            'materials_used'       => 'nullable|string|max:500',
            'images.*'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cultural_cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle product image uploads
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

            // Handle cultural story cover image
            $coverImagePath = null;
            if ($request->hasFile('cultural_cover_image')) {
                $coverImagePath = $request->file('cultural_cover_image')
                    ->store('cultural-covers', 'public');
            }

            CulturalStory::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'title'           => 'The Story of ' . $product->name,
                'slug'            => 'story-' . $product->slug . '-' . uniqid(),
                'story'           => $request->cultural_background
                                     ?? 'A beautiful handmade product from Mindanao.',
                'tribe_community' => $request->tribe_community
                                     ?? auth()->user()->tribe
                                     ?? 'Mindanaoan',
                'location'        => $request->origin_location
                                     ?? auth()->user()->region
                                     ?? 'Mindanao',
                'cover_image'     => $coverImagePath,
                'is_published'    => true,
            ]);
        }

        return redirect()->route('artisan.products.index')
            ->with('success', "Product '{$product->name}' created successfully!");
    }

    public function edit(string $id)
    {
        $product    = Product::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $categories = Category::where('is_active', true)->get();
        return view('artisan.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, string $id)
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

    public function destroy(string $id)
    {
        $product = Product::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    // ─────────────────────────────────────────────────────────
    // ORDERS
    // ─────────────────────────────────────────────────────────
    public function orders()
    {
        $myProductIds = Product::where('user_id', auth()->id())->pluck('id');

        $orders = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $myProductIds))
            ->with([
                'items' => fn($q) => $q->whereIn('product_id', $myProductIds)->with('product'),
                'customer'
            ])
            ->latest()
            ->paginate(15);

        return view('artisan.orders', compact('orders'));
    }

    public function updateOrderStatus(Request $request, string $orderId)
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

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────
    private function generateSlug(string $name): string
    {
        $slug  = str()->slug($name);
        $count = Product::where('slug', 'like', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}