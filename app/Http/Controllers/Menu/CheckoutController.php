<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    }

    public function index(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Keranjang masih kosong.');
        }

        $total      = collect($cart)->sum(fn($item) => $item['subtotal']);
        $qrCode     = $request->query('table');
        $tenant     = App::make('currentTenant');
        $table      = Table::where('tenant_id', $tenant->id)
            ->where('qr_code_string', $qrCode)
            ->firstOrFail();

        return view('menu.checkout', compact('cart', 'total', 'table'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_id'       => 'required|exists:tables,id',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes'          => 'nullable|string',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Keranjang masih kosong.');
        }

        $tenant      = App::make('currentTenant');
        $total       = collect($cart)->sum(fn($item) => $item['subtotal']);
        $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

        $order = Order::create([
            'tenant_id'      => $tenant->id,
            'table_id'       => $request->table_id,
            'order_number'   => $orderNumber,
            'total_price'    => $total,
            'status'         => 'new',
            'payment_status' => 'pending',
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'notes'          => $request->notes,
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['subtotal'],
                'notes'      => $item['notes'] ?? null,
            ]);
        }

        // Otomatis set meja jadi "occupied" saat order masuk
        Table::where('id', $request->table_id)->update(['status' => 'occupied']);

        // Generate Midtrans payment link
        try {
            $transaction_details = [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) $order->total_price,
            ];

            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    'id'       => $item->product_id,
                    'price'    => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name'     => $item->product->name,
                ];
            }

            $customer_details = [
                'first_name' => $order->customer_name ?? 'Customer',
                'phone'      => $order->customer_phone ?? '',
            ];

            $payload = [
                'transaction_details' => $transaction_details,
                'item_details'        => $items,
                'customer_details'    => $customer_details,
            ];

            $snapToken = Snap::getSnapToken($payload);

            $order->update([
                'payment_url' => 'https://app.sandbox.midtrans.com/snap/v1/' . $snapToken,
            ]);

            session()->forget('cart');

            // Broadcast pesanan baru
            \App\Events\OrderStatusUpdated::dispatch($order);

            return redirect()->route('checkout.success', [
                'order_number' => $order->order_number,
                'snap_token'   => $snapToken,
            ]);
        } catch (\Exception $e) {
            $order->delete();
            return redirect()->back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function success(string $order_number)
    {
        $tenant = App::make('currentTenant');
        $order  = Order::where('tenant_id', $tenant->id)
            ->where('order_number', $order_number)
            ->with('items.product', 'table')
            ->firstOrFail();

        $snapToken = request('snap_token');

        return view('menu.success', compact('order', 'snapToken'));
    }
}
