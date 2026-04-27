<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\CulturalStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin\DashboardController
 * Main admin control panel with analytics and management tools.
 */
class DashboardController extends Controller
{
    /**
     * Admin dashboard overview.
     */
    public function index()
    {
        // Stats cards
        $totalUsers     = User::where('role', '!=', 'admin')->count();
        $totalArtisans  = User::where('role', 'artisan')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts  = Product::count();
        $totalOrders    = Order::count();
        $totalRevenue   = Order::where('status', 'delivered')->sum('total_amount');
        $pendingArtisans = User::where('role', 'artisan')->where('status', 'pending')->count();

        // Orders by status
        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Recent orders
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(10)
            ->get();

        // Top products
        $topProducts = Product::withCount(['orderItems as total_sold' => function ($q) {
            $q->select(DB::raw('SUM(quantity)'));
        }])
        ->orderBy('total_sold', 'desc')
        ->take(5)
        ->get();

        // Monthly revenue (last 6 months)
        $monthlyRevenue = Order::where('status', 'delivered')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalArtisans', 'totalCustomers',
            'totalProducts', 'totalOrders', 'totalRevenue',
            'pendingArtisans', 'ordersByStatus', 'recentOrders',
            'topProducts', 'monthlyRevenue'
        ));
    }

    // ==========================================
    // USER MANAGEMENT
    // ==========================================

    public function users(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function approveArtisan(string $id)
    {
        $user = User::where('id', $id)->where('role', 'artisan')->firstOrFail();
        $user->update(['status' => 'approved']);

        // Notify the artisan via SMS + email
        app(\App\Services\NotificationService::class)->artisanApproved($user);

        return back()->with('success', "{$user->name}'s artisan account has been approved!");
    }

    public function rejectArtisan(string $id)
    {
        $user = User::where('id', $id)->where('role', 'artisan')->firstOrFail();
        $user->update(['status' => 'rejected']);
        return back()->with('success', "{$user->name}'s application has been rejected.");
    }

    public function deleteUser(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    // ==========================================
    // PRODUCT MANAGEMENT
    // ==========================================

    public function products(Request $request)
    {
        $query = Product::with(['artisan', 'category']);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::all();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function deleteProduct(string $id)
    {
        Product::findOrFail($id)->delete();
        return back()->with('success', 'Product deleted.');
    }

    // ==========================================
    // CATEGORY MANAGEMENT
    // ==========================================

    public function categories()
    {
        $categories = Category::withCount('products')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        Category::create([
            'name'        => $request->name,
            'slug'        => str()->slug($request->name),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Category created!');
    }

    public function deleteCategory(string $id)
    {
        Category::findOrFail($id)->delete();
        return back()->with('success', 'Category deleted.');
    }

    // ==========================================
    // ORDER MANAGEMENT
    // ==========================================

    public function orders(Request $request)
    {
        $query = Order::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function updateOrderStatus(Request $request, string $id)
    {
        $request->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled']);

        $order = Order::findOrFail($id);
        $data  = ['status' => $request->status];

        // Record timestamps
        match ($request->status) {
            'processing' => $data['processing_at'] = now(),
            'shipped'    => $data['shipped_at'] = now(),
            'delivered'  => $data['delivered_at'] = now(),
            default      => null,
        };

        $order->update($data);

        // Fire notification for this status change
        $notify = app(\App\Services\NotificationService::class);
        match ($request->status) {
            'processing' => $notify->orderProcessing($order->load('customer')),
            'shipped'    => $notify->orderShipped($order->load('customer')),
            'delivered'  => $notify->orderDelivered($order->load('customer')),
            'cancelled'  => $notify->orderCancelled($order->load('customer')),
            default      => null,
        };

        return back()->with('success', "Order #{$order->order_number} status updated to {$request->status}.");
    }
}