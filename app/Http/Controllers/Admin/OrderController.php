<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Events\OrderStatusUpdated;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $tenant = App::make('currentTenant');

        // Filter berdasarkan status dari query
        $status = request('status');

        $ordersQuery = Order::where('tenant_id', $tenant->id)
            ->with('table', 'items.product');

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery->latest()->paginate(15);

        // Count per status untuk filter tabs
        $statusCounts = [
            'new'        => Order::where('tenant_id', $tenant->id)->where('status', 'new')->count(),
            'processing' => Order::where('tenant_id', $tenant->id)->where('status', 'processing')->count(),
            'ready'      => Order::where('tenant_id', $tenant->id)->where('status', 'ready')->count(),
            'completed'  => Order::where('tenant_id', $tenant->id)->where('status', 'completed')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'status', 'statusCounts'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:new,processing,ready,completed',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Jika status berubah jadi "completed", set meja jadi "available"
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            Table::where('id', $order->table_id)->update(['status' => 'available']);
        }

        // Broadcast event ke WebSocket
        OrderStatusUpdated::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diupdate',
            'status'  => $order->status,
        ]);
    }

    /**
     * Tandai pesanan sebagai sudah dibayar tunai (tanpa Midtrans).
     */
    public function markAsPaidCash(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return back()->with('info', 'Pesanan ini sudah lunas.');
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        // Kirim E-Receipt via WhatsApp jika ada nomor HP pelanggan
        if ($order->customer_phone) {
            try {
                $waService = new WhatsAppService();
                $waService->sendEReceipt($order);
            } catch (\Exception $e) {
                Log::error("Gagal kirim WA struk tunai untuk order {$order->order_number}: " . $e->getMessage());
                // Kita tidak membatalkan proses lunas hanya karena WA gagal
            }
        }

        // Broadcast ke dapur agar tahu status terbaru
        OrderStatusUpdated::dispatch($order);

        return back()->with('success', "Pesanan {$order->order_number} ditandai lunas (tunai) ✅ dan E-Receipt dikirim.");
    }

    /**
     * Tampilkan struk kasir untuk dicetak (thermal printer).
     */
    public function printReceipt(Order $order)
    {
        $order->load(['items.product', 'table', 'tenant']);
        return view('admin.orders.print', compact('order'));
    }
}
