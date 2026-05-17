<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * SalesController
 * Handles daily, monthly, and yearly sales reports for artisans.
 * Only shows data for orders that contain the logged-in artisan's products.
 */
class SalesController extends Controller
{
    // ─── Helper: get artisan's product IDs ────────────────────
    private function myProductIds(): \Illuminate\Support\Collection
    {
        return Product::where('user_id', Auth::id())->pluck('id');
    }

    // ─── Helper: base order query for this artisan ─────────────
    private function myOrders()
    {
        $productIds = $this->myProductIds();

        return Order::whereHas('items', function ($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        })->with(['items' => function ($q) use ($productIds) {
            $q->whereIn('product_id', $productIds);
        }, 'customer']);
    }

    // ─────────────────────────────────────────────────────────
    // DAILY
    // ─────────────────────────────────────────────────────────
    public function daily()
    {
        $productIds = $this->myProductIds();

        // All orders placed today
        $dailySales = $this->myOrders()
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        // Today's revenue from delivered orders
        $todayRevenue = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($q) {
                $q->whereDate('created_at', today());
            })
            ->sum('subtotal');

        // Count of orders today
        $todayOrders = $dailySales->count();

        // Items sold today
        $todayItemsSold = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($q) {
                $q->whereDate('created_at', today());
            })
            ->sum('quantity');

        return view('artisan.sales.daily', compact(
            'dailySales',
            'todayRevenue',
            'todayOrders',
            'todayItemsSold'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // MONTHLY
    // ─────────────────────────────────────────────────────────
    public function monthly()
    {
        $productIds = $this->myProductIds();

        // All orders this month
        $monthlySales = $this->myOrders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->latest()
            ->get();

        // This month's revenue
        $monthRevenue = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            })
            ->sum('subtotal');

        // Orders this month
        $monthOrders = $monthlySales->count();

        // Items sold this month
        $monthItemsSold = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            })
            ->sum('quantity');

        // Week by week breakdown for this month
        $weeklyBreakdown = [];
        $startOfMonth    = now()->startOfMonth();
        $endOfMonth      = now()->endOfMonth();
        $weekStart       = $startOfMonth->copy();

        while ($weekStart->lte($endOfMonth)) {
            $weekEnd = $weekStart->copy()->addDays(6)->min($endOfMonth);

            $weekRevenue = OrderItem::whereIn('product_id', $productIds)
                ->whereHas('order', function ($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('created_at', [$weekStart, $weekEnd]);
                })
                ->sum('subtotal');

            $weeklyBreakdown[] = [
                'start'   => $weekStart->format('M d'),
                'end'     => $weekEnd->format('M d'),
                'revenue' => $weekRevenue,
            ];

            $weekStart->addDays(7);
        }

        return view('artisan.sales.monthly', compact(
            'monthlySales',
            'monthRevenue',
            'monthOrders',
            'monthItemsSold',
            'weeklyBreakdown'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // YEARLY
    // ─────────────────────────────────────────────────────────
    public function yearly()
    {
        $productIds = $this->myProductIds();

        // This year's revenue
        $yearRevenue = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($q) {
                $q->whereYear('created_at', now()->year);
            })
            ->sum('subtotal');

        // Orders this year
        $yearOrders = $this->myOrders()
            ->whereYear('created_at', now()->year)
            ->count();

        // Items sold this year
        $yearItemsSold = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($q) {
                $q->whereYear('created_at', now()->year);
            })
            ->sum('quantity');

        // Month by month breakdown
        $monthlyBreakdown = [];
        $months = [
            1  => 'Jan', 2  => 'Feb', 3  => 'Mar',
            4  => 'Apr', 5  => 'May', 6  => 'Jun',
            7  => 'Jul', 8  => 'Aug', 9  => 'Sep',
            10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        foreach ($months as $num => $name) {
            $revenue = OrderItem::whereIn('product_id', $productIds)
                ->whereHas('order', function ($q) use ($num) {
                    $q->whereMonth('created_at', $num)
                      ->whereYear('created_at', now()->year);
                })
                ->sum('subtotal');

            $orders = $this->myOrders()
                ->whereMonth('created_at', $num)
                ->whereYear('created_at', now()->year)
                ->count();

            $monthlyBreakdown[] = [
                'month'   => $name,
                'revenue' => $revenue,
                'orders'  => $orders,
            ];
        }

        // Top selling products this year
        $topProducts = Product::where('user_id', Auth::id())
            ->withSum([
                'orderItems as total_sold' => function ($q) {
                    $q->whereHas('order', function ($o) {
                        $o->whereYear('created_at', now()->year);
                    });
                }
            ], 'quantity')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return view('artisan.sales.yearly', compact(
            'yearRevenue',
            'yearOrders',
            'yearItemsSold',
            'monthlyBreakdown',
            'topProducts'
        ));
    }
}