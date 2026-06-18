<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table;
use Illuminate\Support\Facades\App;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = App::make('currentTenant');

        $totalOrders     = Order::where('tenant_id', $tenant->id)->count();
        $totalProducts   = Product::where('tenant_id', $tenant->id)->count();
        $totalCategories = Category::where('tenant_id', $tenant->id)->count();
        $totalTables     = Table::where('tenant_id', $tenant->id)->count();
        $recentOrders    = Order::where('tenant_id', $tenant->id)
                            ->with('table', 'items.product')
                            ->latest()
                            ->take(10)
                            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalProducts',
            'totalCategories',
            'totalTables',
            'recentOrders'
        ));
    }
}
