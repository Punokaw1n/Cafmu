<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $url;

    public function __construct()
    {
        $this->token = config('services.fonnte.token') ?? '';
        $this->url   = config('services.fonnte.url') ?? 'https://api.fonnte.com/send';
    }

    public function sendEReceipt(Order $order): bool
    {
        // Pastikan ada nomor WA pelanggan
        if (!$order->customer_phone) {
            Log::info('WhatsApp e-receipt skipped: no customer phone', ['order' => $order->order_number]);
            return false;
        }

        if (empty($this->token)) {
            Log::warning('WhatsApp e-receipt skipped: FONNTE_TOKEN not set in .env', ['order' => $order->order_number]);
            return false;
        }

        $message = $this->buildReceiptMessage($order);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->url, [
                'target'  => $order->customer_phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp e-receipt sent', [
                    'order'  => $order->order_number,
                    'phone'  => $order->customer_phone,
                ]);
                return true;
            }

            Log::error('WhatsApp e-receipt failed', [
                'order'    => $order->order_number,
                'response' => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp e-receipt exception', [
                'order'   => $order->order_number,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function buildReceiptMessage(Order $order): string
    {
        $tenantName = $order->tenant->name ?? 'Sistem Kasir';
        $items = '';

        foreach ($order->items as $item) {
            $items .= "• {$item->quantity}x {$item->product->name} — Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $message = "✅ *Pembayaran Berhasil!*\n\n";
        $message .= "Terima kasih sudah memesan di *{$tenantName}* 🙏\n\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "📋 *Detail Pesanan*\n";
        $message .= "No. Order: *{$order->order_number}*\n";
        $message .= "Meja: *{$order->table->table_number}*\n\n";
        $message .= "*Item yang dipesan:*\n";
        $message .= $items;
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "💰 *Total: Rp " . number_format($order->total_price, 0, ',', '.') . "*\n";
        $message .= "━━━━━━━━━━━━━━━\n\n";
        $message .= "Pesanan kamu sedang diproses dapur. Silakan tunggu di meja ya! 😊";

        return $message;
    }
}
