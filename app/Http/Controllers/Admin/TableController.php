<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function index()
    {
        $tenant = App::make('currentTenant');
        $tables = Table::where('tenant_id', $tenant->id)->orderBy('table_number')->get();

        return view('admin.tables.index', compact('tables'));
    }

    public function create()
    {
        return view('admin.tables.create');
    }

    public function store(Request $request)
    {
        $tenant = App::make('currentTenant');

        $request->validate([
            'table_number' => 'required|string|max:50',
            'is_active'    => 'nullable|boolean',
        ]);

        Table::create([
            'tenant_id'      => $tenant->id,
            'table_number'   => $request->table_number,
            'qr_code_string' => Str::uuid(),
            'is_active'      => $request->boolean('is_active', true),
            'status'         => 'available',
        ]);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(Table $table)
    {
        return view('admin.tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'table_number' => 'required|string|max:50',
            'is_active'    => 'nullable|boolean',
        ]);

        $table->update([
            'table_number' => $request->table_number,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Meja berhasil diupdate.');
    }

    public function destroy(Table $table)
    {
        $table->delete();

        return redirect()->route('admin.tables.index')
            ->with('success', 'Meja berhasil dihapus.');
    }

    // Kasir update status meja (occupied → dirty → available)
    public function updateStatus(Request $request, Table $table)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,dirty',
        ]);

        $table->update(['status' => $request->status]);

        return response()->json([
            'success'      => true,
            'status'       => $table->status,
            'status_label' => $table->status_label,
            'status_color' => $table->status_color,
        ]);
    }

    public function show(Table $table) {}
}
