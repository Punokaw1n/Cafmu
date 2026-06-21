@extends('layouts.menu')

@section('content')
<div x-data="cartApp()" class="min-h-screen bg-gray-50 pb-32">

    {{-- Header --}}
    <div class="bg-white sticky top-0 z-10 shadow-sm">
        <div class="px-5 py-4 pb-6 text-center">
            @php
                $logoUrl = $currentTenant->getSetting('logo_url');
            @endphp
            @if($logoUrl)
                <img src="{{ Storage::url($logoUrl) }}" alt="Logo" class="h-14 w-auto mx-auto mb-3 object-contain rounded-lg shadow-sm">
            @endif
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $currentTenant->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Meja {{ $table->table_number }}</p>
        </div>

        {{-- Category Tabs --}}
        <div class="flex gap-2 px-4 pb-3 overflow-x-auto scrollbar-hide">
            <button @click="activeCategory = null"
                    :class="activeCategory === null ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition">
                Semua
            </button>
            @foreach($categories as $category)
            <button @click="activeCategory = {{ $category->id }}"
                    :class="activeCategory === {{ $category->id }} ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition whitespace-nowrap">
                {{ $category->name }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Product List --}}
    <div class="px-4 py-4 space-y-6">
        @foreach($categories as $category)
        <div x-show="activeCategory === null || activeCategory === {{ $category->id }}">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">{{ $category->name }}</h2>
            <div class="grid grid-cols-2 gap-3">
                @foreach($category->products as $product)
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                         class="w-full h-28 object-cover">
                    <div class="p-3">
                        <p class="font-semibold text-gray-800 text-sm leading-tight">{{ $product->name }}</p>
                        @if($product->description)
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ $product->description }}</p>
                        @endif
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-amber-700 font-bold text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <button @click="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }}, '{{ $product->image_url }}')"
                                    class="w-7 h-7 bg-amber-600 hover:bg-amber-700 text-white rounded-full flex items-center justify-center transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- Floating Cart Button --}}
    <div x-show="cartCount > 0" x-transition
         class="fixed bottom-6 left-4 right-4 z-20">
        <button @click="cartOpen = true"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white rounded-2xl px-5 py-4 flex items-center justify-between shadow-lg transition">
            <div class="flex items-center gap-3">
                <span class="bg-white text-amber-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center" x-text="cartCount"></span>
                <span class="font-semibold text-sm">Lihat Pesanan</span>
            </div>
            <span class="font-bold text-sm" x-text="'Rp ' + formatPrice(cartTotal)"></span>
        </button>
    </div>

    {{-- Cart Drawer --}}
    <div x-show="cartOpen" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-30" @click="cartOpen = false">
    </div>

    <div x-show="cartOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
         class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl z-40 max-h-[80vh] flex flex-col shadow-2xl">

        {{-- Drawer Handle --}}
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
        </div>

        {{-- Drawer Header --}}
        <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100">
            <h3 class="font-bold text-gray-800">Keranjang Pesanan</h3>
            <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto px-5 py-3 space-y-3">
            <template x-for="(item, key) in cart" :key="key">
                <div class="flex items-center gap-3">
                    <img :src="item.image" :alt="item.name" class="w-12 h-12 rounded-xl object-cover bg-gray-100">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.name"></p>
                        <p class="text-xs text-amber-700 font-medium" x-text="'Rp ' + formatPrice(item.price)"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="updateQty(key, item.quantity - 1)"
                                class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="text-sm font-bold text-gray-800 w-5 text-center" x-text="item.quantity"></span>
                        <button @click="updateQty(key, item.quantity + 1)"
                                class="w-7 h-7 rounded-full bg-amber-600 hover:bg-amber-700 flex items-center justify-center text-white transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Drawer Footer --}}
        <div class="px-5 py-4 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Total</span>
                <span class="font-bold text-gray-800" x-text="'Rp ' + formatPrice(cartTotal)"></span>
            </div>
            <a :href="'/checkout?table={{ $table->qr_code_string }}'"
               class="block w-full bg-amber-600 hover:bg-amber-700 text-white text-center font-semibold py-3.5 rounded-2xl transition">
                Lanjut ke Checkout
            </a>
        </div>
    </div>

</div>

<script>
function cartApp() {
    return {
        cart: {},
        cartOpen: false,
        activeCategory: null,

        get cartCount() {
            return Object.values(this.cart).reduce((sum, item) => sum + item.quantity, 0);
        },
        get cartTotal() {
            return Object.values(this.cart).reduce((sum, item) => sum + item.subtotal, 0);
        },

        addToCart(id, name, price, image) {
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ product_id: id, quantity: 1 }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (this.cart[id]) {
                        this.cart[id].quantity++;
                        this.cart[id].subtotal = this.cart[id].price * this.cart[id].quantity;
                    } else {
                        this.cart[id] = { product_id: id, name, price, quantity: 1, subtotal: price, image };
                    }
                }
            });
        },

        updateQty(id, qty) {
            if (qty <= 0) {
                fetch('/cart/remove', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ product_id: id }),
                }).then(() => delete this.cart[id]);
                return;
            }
            fetch('/cart/update', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ product_id: id, quantity: qty }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.cart[id].quantity = qty;
                    this.cart[id].subtotal = this.cart[id].price * qty;
                }
            });
        },

        formatPrice(price) {
            return new Intl.NumberFormat('id-ID').format(price);
        }
    }
}
</script>
@endsection
