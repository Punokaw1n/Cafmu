<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CategoryController extends Controller
{
    public function index()
    {
        $tenant     = App::make('currentTenant');
        $categories = Category::where('tenant_id', $tenant->id)
                        ->orderBy('sort_order')
                        ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $tenant = App::make('currentTenant');

        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        Category::create([
            'tenant_id'  => $tenant->id,
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update([
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }

    public function show(Category $category) {}
}
