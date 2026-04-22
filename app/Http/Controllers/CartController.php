<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class CartController extends Controller
// {
//     //
// }

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * CartController
 * Manages the shopping cart stored in the session.
 */
class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the cart.
     */
    public function index()
    {
        $cart  = session()->get('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        return view('cart.index', compact('cart', 'total'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request, int $productId)
    {
        $product = Product::findOrFail($productId);

        if (!$product->isInStock()) {
            return back()->with('error', 'Sorry, this product is out of stock.');
        }

        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            // Check stock limit
            $newQty = $cart[$productId]['quantity'] + $quantity;
            if ($newQty > $product->stock) {
                return back()->with('error', 'Not enough stock available.');
            }
            $cart[$productId]['quantity'] = $newQty;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => $quantity,
                'image'      => $product->primary_image,
                'artisan'    => $product->artisan->name,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', "'{$product->name}' added to cart!");
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, int $productId)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:100']);

        $cart = session()->get('cart', []);

        if (!isset($cart[$productId])) {
            return back()->with('error', 'Item not found in cart.');
        }

        $product = Product::find($productId);
        if ($product && $request->quantity > $product->stock) {
            return back()->with('error', 'Not enough stock available.');
        }

        $cart[$productId]['quantity'] = $request->quantity;
        session()->put('cart', $cart);

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(int $productId)
    {
        $cart = session()->get('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared.');
    }

    /**
     * Get cart item count for navbar badge.
     */
    public static function getCount(): int
    {
        return collect(session()->get('cart', []))->sum('quantity');
    }
}
