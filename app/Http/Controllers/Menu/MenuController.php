<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Table;
use Illuminate\Support\Facades\App;

class MenuController extends Controller
{
    public function show(string $qr_code_string)
    {
        $tenant = App::make('currentTenant');

        $table = Table::where('tenant_id', $tenant->id)
                    ->where('qr_code_string', $qr_code_string)
                    ->where('is_active', true)
                    ->firstOrFail();

        $categories = Category::where('tenant_id', $tenant->id)
                        ->with(['products' => function ($q) {
                            $q->where('is_available', true)->orderBy('sort_order');
                        }])
                        ->orderBy('sort_order')
                        ->get();

        return view('menu.show', compact('table', 'categories'));
    }
}
