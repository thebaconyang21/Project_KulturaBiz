<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Services\PaymentService;
use App\Services\CourierService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * OrderController
 * Handles checkout, order placement, tracking, and reviews.
 * Now wired to PaymentService, CourierService, and NotificationService.
 */
class OrderController extends Controller
{
    public function __construct(
        private PaymentService      $payment,
        private CourierService      $courier,
        private NotificationService $notify,
    ) {}

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal    = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        // Calculate shipping fee based on province if available, otherwise flat rate
        $shippingFee = 150.00;
        $total       = $subtotal + $shippingFee;

        // Get available payment methods
        $paymentMethods = [
            'cod'           => ['label' => 'Cash on Delivery',  'icon' => '💵', 'desc' => 'Pay when your order arrives'],
            'gcash'         => ['label' => 'GCash',             'icon' => '📱', 'desc' => 'Mobile wallet (PayMongo)'],
            'maya'          => ['label' => 'Maya',              'icon' => '💳', 'desc' => 'Maya wallet (PayMongo)'],
            'bank_transfer' => ['label' => 'Bank Transfer',     'icon' => '🏦', 'desc' => 'Direct bank deposit'],
        ];

        return view('orders.checkout', compact('cart', 'subtotal', 'shippingFee', 'total', 'paymentMethods'));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'recipient_name'   => 'required|string|max:255',
            'delivery_address' => 'required|string|max:500',
            'contact_number'   => 'required|string|max:20',
            'city'             => 'required|string|max:100',
            'province'         => 'required|string|max:100',
            'postal_code'      => 'nullable|string|max:10',
            'payment_method'   => 'required|in:cod,gcash,maya,bank_transfer',
            'notes'            => 'nullable|string|max:500',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Validate stock
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || $product->stock < $item['quantity']) {
                return back()->with('error', "'{$item['name']}' is no longer available in the requested quantity.");
            }
        }

        $order = null;

        DB::transaction(function () use ($request, $cart, &$order) {
            $subtotal    = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);

            // Get dynamic shipping fee from courier service
            $shippingFee = $this->courier->calculateFee($request->province);
            $total       = $subtotal + $shippingFee;

            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_number'     => Order::generateOrderNumber(),
                'recipient_name'   => $request->recipient_name,
                'delivery_address' => $request->delivery_address,
                'contact_number'   => $request->contact_number,
                'city'             => $request->city,
                'province'         => $request->province,
                'postal_code'      => $request->postal_code,
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'pending',
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'total_amount'     => $total,
                'notes'            => $request->notes,
                // Courier details filled in by PaymentController::finalizeOrder()
            ]);

            foreach ($cart as $productId => $item) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $productId,
                    'product_name' => $item['name'],
                    'price'        => $item['price'],
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $item['price'] * $item['quantity'],
                ]);
                Product::where('id', $productId)->decrement('stock', $item['quantity']);
            }

            session()->forget('cart');
        });

        // Hand off to PaymentController to handle gateway + courier booking + notifications
        return app(PaymentController::class)->initiate($order);
    }

    public function confirmation(string $orderId)
    {
        $order = Order::with('items.product', 'customer')
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('orders.confirmation', compact('order'));
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('orders.my-orders', compact('orders'));
    }

    public function track(string $orderId)
    {
        $order = Order::with(['items.product', 'items.product.artisan'])
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Fetch live tracking events from courier service
        $trackingEvents = $this->courier->track($order);

        return view('orders.track', compact('order', 'trackingEvents'));
    }

    public function review(Request $request, string $orderId, string $productId)
    {
        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'title'   => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->firstOrFail();

        $order->items()->where('product_id', $productId)->firstOrFail();

        Review::updateOrCreate(
            ['product_id' => $productId, 'user_id' => Auth::id(), 'order_id' => $orderId],
            ['rating' => $request->rating, 'title' => $request->title, 'comment' => $request->comment]
        );

        Product::find($productId)?->updateRating();

        return back()->with('success', 'Thank you for your review!');
    }
}