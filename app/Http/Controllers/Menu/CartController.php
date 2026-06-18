<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCart(): array
    {
        return session()->get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    public function index()
    {
        $cart  = $this->getCart();
        $total = collect($cart)->sum(fn($item) => $item['subtotal']);

        return response()->json(['cart' => $cart, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart    = $this->getCart();
        $key     = $request->product_id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $request->quantity;
            $cart[$key]['subtotal']  = $cart[$key]['price'] * $cart[$key]['quantity'];
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => $request->quantity,
                'subtotal'   => $product->price * $request->quantity,
                'notes'      => $request->notes,
                'image'      => $product->image_url,
            ];
        }

        $this->saveCart($cart);

        $total = collect($cart)->sum(fn($item) => $item['subtotal']);
        $count = collect($cart)->sum(fn($item) => $item['quantity']);

        return response()->json([
            'success' => true,
            'message' => $product->name . ' ditambahkan ke keranjang.',
            'total'   => $total,
            'count'   => $count,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity'   => 'required|integer|min:0',
        ]);

        $cart = $this->getCart();
        $key  = $request->product_id;

        if (isset($cart[$key])) {
            if ($request->quantity == 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['quantity'] = $request->quantity;
                $cart[$key]['subtotal'] = $cart[$key]['price'] * $request->quantity;
            }
        }

        $this->saveCart($cart);

        $total = collect($cart)->sum(fn($item) => $item['subtotal']);
        $count = collect($cart)->sum(fn($item) => $item['quantity']);

        return response()->json([
            'success' => true,
            'total'   => $total,
            'count'   => $count,
            'cart'    => $cart,
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required']);

        $cart = $this->getCart();
        unset($cart[$request->product_id]);
        $this->saveCart($cart);

        $total = collect($cart)->sum(fn($item) => $item['subtotal']);
        $count = collect($cart)->sum(fn($item) => $item['quantity']);

        return response()->json([
            'success' => true,
            'total'   => $total,
            'count'   => $count,
        ]);
    }
}
