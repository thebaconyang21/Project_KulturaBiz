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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        $products = Product::with('artisan')
            ->whereIn('id', array_map('intval', array_keys($cart)))
            ->get()
            ->keyBy('id');

        foreach ($cart as $productId => $item) {
            $product = $products->get((int) $productId);
            $quantity = is_array($item)
                ? filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT)
                : false;

            if (!$product || !is_array($item) || $product->status !== 'active') {
                return redirect()->route('cart.index')
                    ->with('error', 'A product in your cart is no longer available. Please remove it before checkout.');
            }

            if ($quantity === false || $quantity < 1 || $product->stock < $quantity) {
                return redirect()->route('cart.index')
                    ->with('error', "'{$product->name}' no longer has enough stock. Please update your cart.");
            }

            $cart[$productId] = [
                ...$item,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image' => $product->primary_image,
                'artisan' => $product->artisan?->name ?? 'Artisan',
            ];
        }

        session()->put('cart', $cart);

        $subtotal    = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        // Calculate shipping fee based on province if available, otherwise flat rate
        $shippingFee = 150.00;
        $total       = $subtotal + $shippingFee;

        $paymentMethods = $this->availablePaymentMethods();

        return view('orders.checkout', compact('cart', 'subtotal', 'shippingFee', 'total', 'paymentMethods'));
    }

    public function placeOrder(Request $request)
    {
        $paymentMethods = $this->availablePaymentMethods();

        $request->validate([
            'recipient_name'   => 'required|string|max:255',
            'delivery_address' => 'required|string|max:500',
            'contact_number'   => 'required|string|max:20',
            'city'             => 'required|string|max:100',
            'province'         => 'required|string|max:100',
            'postal_code'      => 'nullable|string|max:10',
            'payment_method'   => ['required', Rule::in(array_keys($paymentMethods))],
            'notes'            => 'nullable|string|max:500',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $quantities = [];
        foreach ($cart as $productId => $item) {
            $id = filter_var($productId, FILTER_VALIDATE_INT);
            $quantity = is_array($item)
                ? filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT)
                : false;

            if ($id === false || $id < 1 || $quantity === false || $quantity < 1 || isset($quantities[$id])) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart contains an invalid item. Please update it and try again.',
                ]);
            }

            $quantities[$id] = $quantity;
        }

        $order = DB::transaction(function () use ($request, $quantities) {
            $products = Product::whereIn('id', array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($quantities)) {
                throw ValidationException::withMessages([
                    'cart' => 'A product in your cart is no longer available. Please update your cart.',
                ]);
            }

            $subtotal = 0;
            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                if ($product->status !== 'active') {
                    throw ValidationException::withMessages([
                        'cart' => "'{$product->name}' is no longer available. Please update your cart.",
                    ]);
                }

                $reserved = Product::whereKey($productId)
                    ->where('status', 'active')
                    ->where('stock', '>=', $quantity)
                    ->decrement('stock', $quantity);

                if ($reserved !== 1) {
                    throw ValidationException::withMessages([
                        'cart' => "'{$product->name}' no longer has enough stock. Please update your cart.",
                    ]);
                }

                $subtotal += $product->price * $quantity;
            }

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

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $productId,
                    'product_name' => $product->name,
                    'price'        => $product->price,
                    'quantity'     => $quantity,
                    'subtotal'     => $product->price * $quantity,
                ]);
            }

            return $order;
        });

        session()->forget('cart');

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

    private function availablePaymentMethods(): array
    {
        $methods = [
            'cod' => [
                'label' => 'Cash on Delivery',
                'icon' => '💵',
                'desc' => 'Pay when your order arrives',
            ],
        ];

        if (!$this->payment->isRealPaymentsEnabled() && !$this->payment->isSimulationEnabled()) {
            return $methods;
        }

        $demoDescription = $this->payment->isSimulationEnabled()
            ? 'Demo only — no money will be charged'
            : 'Processed securely via PayMongo';

        return $methods + [
            'gcash' => ['label' => 'GCash', 'icon' => '📱', 'desc' => $demoDescription],
            'maya' => ['label' => 'Maya', 'icon' => '💳', 'desc' => $demoDescription],
            'bank_transfer' => ['label' => 'Bank Transfer', 'icon' => '🏦', 'desc' => $demoDescription],
        ];
    }
}