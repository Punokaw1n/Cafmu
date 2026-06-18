app/Http/Controllers/Admin/DashboardController.php
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

2. app/Http/Controllers/Admin/CategoryController.php
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

3. app/Http/Controllers/Admin/ProductController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $tenant   = App::make('currentTenant');
        $products = Product::where('tenant_id', $tenant->id)
                    ->with('category')
                    ->orderBy('sort_order')
                    ->get();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $tenant     = App::make('currentTenant');
        $categories = Category::where('tenant_id', $tenant->id)->orderBy('sort_order')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $tenant = App::make('currentTenant');

        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|max:2048',
            'is_available' => 'nullable|boolean',
            'sort_order'   => 'nullable|integer',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'tenant_id'    => $tenant->id,
            'category_id'  => $request->category_id,
            'name'         => $request->name,
            'description'  => $request->description,
            'price'        => $request->price,
            'image'        => $imagePath,
            'is_available' => $request->boolean('is_available', true),
            'sort_order'   => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $tenant     = App::make('currentTenant');
        $categories = Category::where('tenant_id', $tenant->id)->orderBy('sort_order')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|max:2048',
            'is_available' => 'nullable|boolean',
            'sort_order'   => 'nullable|integer',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'category_id'  => $request->category_id,
            'name'         => $request->name,
            'description'  => $request->description,
            'price'        => $request->price,
            'image'        => $imagePath,
            'is_available' => $request->boolean('is_available', true),
            'sort_order'   => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produk berhasil dihapus.');
    }

    public function show(Product $product) {}
}

4. app/Http/Controllers/Admin/TableController.php
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

    public function show(Table $table) {}
}

5. app/Http/Controllers/Menu/MenuController.php

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

6. app/Http/Controllers/Menu/CartController.php
<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCart(): array
    {
        return session()->get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    public function index()
    {
        $cart  = $this->getCart();
        $total = collect($cart)->sum(fn($item) => $item['subtotal']);

        return response()->json(['cart' => $cart, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart    = $this->getCart();
        $key     = $request->product_id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $request->quantity;
            $cart[$key]['subtotal']  = $cart[$key]['price'] * $cart[$key]['quantity'];
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => $request->quantity,
                'subtotal'   => $product->price * $request->quantity,
                'notes'      => $request->notes,
                'image'      => $product->image_url,
            ];
        }

        $this->saveCart($cart);

        $total = collect($cart)->sum(fn($item) => $item['subtotal']);
        $count = collect($cart)->sum(fn($item) => $item['quantity']);

        return response()->json([
            'success' => true,
            'message' => $product->name . ' ditambahkan ke keranjang.',
            'total'   => $total,
            'count'   => $count,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity'   => 'required|integer|min:0',
        ]);

        $cart = $this->getCart();
        $key  = $request->product_id;

        if (isset($cart[$key])) {
            if ($request->quantity == 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['quantity'] = $request->quantity;
                $cart[$key]['subtotal'] = $cart[$key]['price'] * $request->quantity;
            }
        }

        $this->saveCart($cart);

        $total = collect($cart)->sum(fn($item) => $item['subtotal']);
        $count = collect($cart)->sum(fn($item) => $item['quantity']);

        return response()->json([
            'success' => true,
            'total'   => $total,
            'count'   => $count,
            'cart'    => $cart,
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required']);

        $cart = $this->getCart();
        unset($cart[$request->product_id]);
        $this->saveCart($cart);

        $total = collect($cart)->sum(fn($item) => $item['subtotal']);
        $count = collect($cart)->sum(fn($item) => $item['quantity']);

        return response()->json([
            'success' => true,
            'total'   => $total,
            'count'   => $count,
        ]);
    }
}
7. app/Http/Controllers/Menu/CheckoutController.php
<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Keranjang masih kosong.');
        }

        $total  = collect($cart)->sum(fn($item) => $item['subtotal']);
        $qrCode = $request->query('table');
        $tenant = App::make('currentTenant');
        $table  = Table::where('tenant_id', $tenant->id)
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

        session()->forget('cart');

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function success(string $order_number)
    {
        $tenant = App::make('currentTenant');
        $order  = Order::where('tenant_id', $tenant->id)
                    ->where('order_number', $order_number)
                    ->with('items.product', 'table')
                    ->firstOrFail();

        return view('menu.success', compact('order'));
    }
}