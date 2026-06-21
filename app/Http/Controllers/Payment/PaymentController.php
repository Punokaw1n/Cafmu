<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Setup Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    }

    public function generatePaymentLink(Order $order)
    {
        try {
            // Prepare transaction data untuk Midtrans
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

            // Generate Snap token
            $snapToken = Snap::getSnapToken($payload);

            // Update order dengan payment info
            $order->update([
                'payment_url'             => 'https://app.sandbox.midtrans.com/snap/v1/' . $snapToken,
                'midtrans_transaction_id' => null,
            ]);

            return response()->json([
                'success'   => true,
                'snap_token' => $snapToken,
                'payment_url' => 'https://app.sandbox.midtrans.com/snap/v1/' . $snapToken,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $notification = json_decode($payload);

        \Illuminate\Support\Facades\Log::info('Midtrans Webhook Received', ['payload' => $notification]);

        // Verify signature
        $serverKey = config('midtrans.server_key');
        $hashed = hash('sha512', $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey);

        if ($hashed != $notification->signature_key) {
            \Illuminate\Support\Facades\Log::error('Midtrans Webhook Invalid Signature', [
                'expected' => $hashed,
                'received' => $notification->signature_key
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Find order
        $order = Order::where('order_number', $notification->order_id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Update payment status based on transaction status
        $transactionStatus = $notification->transaction_status;
        $paymentType = $notification->payment_type ?? null;

        if ($transactionStatus == 'capture') {
            if ($paymentType == 'credit_card') {
                if ($notification->fraud_status == 'challenge') {
                    $order->update(['payment_status' => 'pending']);
                } else {
                    $order->update(['payment_status' => 'paid']);
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->update(['payment_status' => 'paid']);
        } elseif ($transactionStatus == 'pending') {
            $order->update(['payment_status' => 'pending']);
        } elseif ($transactionStatus == 'deny') {
            $order->update(['payment_status' => 'cancelled']);
        } elseif ($transactionStatus == 'expire') {
            $order->update(['payment_status' => 'cancelled']);
        } elseif ($transactionStatus == 'cancel') {
            $order->update(['payment_status' => 'cancelled']);
        }

        // Broadcast perubahan status pembayaran ke dashboard
        \App\Events\OrderStatusUpdated::dispatch($order);

        return response()->json(['status' => 'ok']);
    }
}
