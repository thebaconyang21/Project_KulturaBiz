<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the checkout page.
     */
    public function checkout()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal    = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        $shippingFee = 150.00;
        $total       = $subtotal + $shippingFee;

        return view('orders.checkout', compact('cart', 'subtotal', 'shippingFee', 'total'));
    }

    /**
     * Place the order.
     */
    public function placeOrder(Request $request)
    {
        $request->validate([
            'recipient_name'   => 'required|string|max:255',
            'delivery_address' => 'required|string|max:500',
            'contact_number'   => 'required|string|max:20',
            'city'             => 'required|string|max:100',
            'province'         => 'required|string|max:100',
            'postal_code'      => 'nullable|string|max:10',
            'payment_method'   => 'required|in:cod,gcash,bank_transfer',
            'notes'            => 'nullable|string|max:500',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Check stock
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || $product->stock < $item['quantity']) {
                return back()->with('error', "'{$item['name']}' is not available.");
            }
        }

        DB::transaction(function () use ($request, $cart) {

            $subtotal    = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
            $shippingFee = 150.00;
            $total       = $subtotal + $shippingFee;

            $couriers = ['J&T Express', 'LBC Express', 'Ninja Van', '2GO Express', 'Flash Express'];

            $order = Order::create([
                'user_id'           => Auth::id(),
                'order_number'      => Order::generateOrderNumber(),
                'recipient_name'    => $request->recipient_name,
                'delivery_address'  => $request->delivery_address,
                'contact_number'    => $request->contact_number,
                'city'              => $request->city,
                'province'          => $request->province,
                'postal_code'       => $request->postal_code,
                'payment_method'    => $request->payment_method,
                'payment_status'    => 'pending',
                'status'            => 'pending',
                'subtotal'          => $subtotal,
                'shipping_fee'      => $shippingFee,
                'total_amount'      => $total,
                'courier_name'      => $couriers[array_rand($couriers)],
                'tracking_number'   => 'KB' . strtoupper(substr(md5(uniqid()), 0, 10)),
                'estimated_delivery'=> now()->addDays(rand(5, 10)),
                'notes'             => $request->notes,
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

                Product::where('id', $productId)
                    ->decrement('stock', $item['quantity']);
            }

            session()->forget('cart');
            session()->put('last_order_id', $order->id);
        });

        $orderId = session()->pull('last_order_id');

        return redirect()->route('orders.confirmation', $orderId)
            ->with('success', 'Order placed successfully!');
    }

    /**
     * Order confirmation
     */
    public function confirmation($orderId)
    {
        $order = Order::with('items.product', 'customer')
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('orders.confirmation', compact('order'));
    }

    /**
     * My orders
     */
    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('orders.my-orders', compact('orders'));
    }

    /**
     * Track order
     */
    public function track($orderId)
    {
        $order = Order::with(['items.product', 'items.product.artisan'])
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('orders.track', compact('order'));
    }

    /**
     * Submit review
     */
    public function review(Request $request, $orderId, $productId)
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
            [
                'product_id' => $productId,
                'user_id'    => Auth::id(),
                'order_id'   => $orderId
            ],
            [
                'rating'  => $request->rating,
                'title'   => $request->title,
                'comment' => $request->comment,
            ]
        );

        Product::find($productId)?->updateRating();

        return back()->with('success', 'Thank you for your review!');
    }
}