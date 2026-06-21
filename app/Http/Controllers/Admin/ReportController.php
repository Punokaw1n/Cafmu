<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tenant = App::make('currentTenant');

        // Filter periode (default: hari ini)
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$start, $end] = $this->getDateRange($period, $startDate, $endDate);

        // Query orders yang sudah paid dalam periode
        $ordersQuery = Order::where('tenant_id', $tenant->id)
                            ->where('payment_status', 'paid')
                            ->whereBetween('created_at', [$start, $end]);

        // Summary stats
        $totalRevenue = (clone $ordersQuery)->sum('total_price');
        $totalOrders = (clone $ordersQuery)->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Orders per status (semua order, bukan hanya paid)
        $allOrdersQuery = Order::where('tenant_id', $tenant->id)
                                ->whereBetween('created_at', [$start, $end]);
        
        $statusBreakdown = [
            'new'        => (clone $allOrdersQuery)->where('status', 'new')->count(),
            'processing' => (clone $allOrdersQuery)->where('status', 'processing')->count(),
            'ready'      => (clone $allOrdersQuery)->where('status', 'ready')->count(),
            'completed'  => (clone $allOrdersQuery)->where('status', 'completed')->count(),
        ];

        // Produk terlaris
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('order', function ($q) use ($tenant, $start, $end) {
                $q->where('tenant_id', $tenant->id)
                  ->where('payment_status', 'paid')
                  ->whereBetween('created_at', [$start, $end]);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // Revenue per hari
        $dailyRevenue = Order::where('tenant_id', $tenant->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.reports.index', compact(
            'totalRevenue',
            'totalOrders',
            'avgOrderValue',
            'statusBreakdown',
            'topProducts',
            'dailyRevenue',
            'period',
            'start',
            'end'
        ));
    }

    private function getDateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        switch ($period) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'custom':
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today();
                $end   = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();
                return [$start, $end];
            default:
                return [Carbon::today(), Carbon::today()->endOfDay()];
        }
    }

    public function transactions(Request $request)
    {
        $tenant = App::make('currentTenant');

        $query = Order::where('tenant_id', $tenant->id)
                    ->with('table', 'items.product');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        return view('admin.reports.transactions', compact('transactions'));
    }
}
