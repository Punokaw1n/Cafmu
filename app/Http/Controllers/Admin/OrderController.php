<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Events\OrderStatusUpdated;

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
}
