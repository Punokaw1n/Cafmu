@extends('layouts.app')
@section('title', 'Pesanan')

@section('content')
<div class="space-y-4">

    {{-- Status Filter Tabs --}}
    <div class="flex gap-2 pb-3 border-b border-gray-100 overflow-x-auto scrollbar-hide">
        <a href="{{ route('admin.orders.index') }}"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ !request('status') ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'text-gray-600 hover:bg-gray-50' }}">
            Semua ({{ $statusCounts['new'] + $statusCounts['processing'] + $statusCounts['ready'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ request('status') === 'new' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50' }}">
            🆕 Baru ({{ $statusCounts['new'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ request('status') === 'processing' ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : 'text-gray-600 hover:bg-gray-50' }}">
            👨‍🍳 Diproses ({{ $statusCounts['processing'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'ready']) }}"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ request('status') === 'ready' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'text-gray-600 hover:bg-gray-50' }}">
            ✅ Siap ({{ $statusCounts['ready'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
            class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ request('status') === 'completed' ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50' }}">
            ✨ Selesai
        </a>
    </div>

    {{-- Orders List --}}
    <div class="space-y-3">
        @forelse($orders as $order)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
            x-data="{ orderStatus: '{{ $order->status }}' }">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-3 pb-3 border-b border-gray-100">
                <div>
                    <p class="font-bold text-gray-800 text-base">{{ $order->order_number }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Meja {{ $order->table->table_number }} • {{ $order->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right">
                    <span class="text-lg font-bold text-amber-700 block">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    <a href="{{ route('admin.orders.print', $order) }}" target="_blank"
                       class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium mt-1 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak
                    </a>
                </div>
            </div>

            {{-- Items --}}
            <div class="space-y-2 mb-4">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">
                        <span class="font-bold text-gray-800">{{ $item->quantity }}x</span>
                        {{ $item->product->name }}
                        @if($item->notes)
                        <span class="text-xs text-gray-400">({{ $item->notes }})</span>
                        @endif
                    </span>
                    <span class="text-gray-500">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>

            {{-- Customer Info --}}
            @if($order->customer_name || $order->customer_phone || $order->notes)
            <div class="bg-gray-50 rounded-lg px-3 py-2 mb-3 text-xs space-y-1">
                @if($order->customer_name)
                <p class="text-gray-600"><span class="font-medium">Nama:</span> {{ $order->customer_name }}</p>
                @endif
                @if($order->customer_phone)
                <p class="text-gray-600"><span class="font-medium">WA:</span> {{ $order->customer_phone }}</p>
                @endif
                @if($order->notes)
                <p class="text-gray-600"><span class="font-medium">Catatan:</span> {{ $order->notes }}</p>
                @endif
            </div>
            @endif

            {{-- Status & Actions --}}
            <div class="flex items-center gap-2">
                {{-- Current Status Badge --}}
                <span x-text="getStatusLabel(orderStatus)"
                    :class="getStatusColor(orderStatus)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium">
                </span>

                {{-- Update Status Buttons --}}
                <div class="flex gap-1.5 ml-auto">
                    @if($order->status === 'new')
                    <button @click="updateOrderStatus('{{ $order->id }}', 'processing')"
                        class="text-xs bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg transition font-medium">
                        Mulai Proses
                    </button>
                    @elseif($order->status === 'processing')
                    <button @click="updateOrderStatus('{{ $order->id }}', 'ready')"
                        class="text-xs bg-purple-50 hover:bg-purple-100 text-purple-700 px-3 py-1.5 rounded-lg transition font-medium">
                        Pesanan Siap
                    </button>
                    @elseif($order->status === 'ready')
                    <button @click="updateOrderStatus('{{ $order->id }}', 'completed')"
                        class="text-xs bg-green-50 hover:bg-green-100 text-green-700 px-3 py-1.5 rounded-lg transition font-medium">
                        Selesai
                    </button>
                    @else
                    <span class="text-xs text-gray-400 italic">Pesanan telah selesai</span>
                    @endif
                </div>
            </div>

            {{-- Payment Status --}}
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <span>
                    @if($order->payment_status === 'pending')
                    <span class="text-orange-600 font-medium">💳 Menunggu Pembayaran</span>
                    @elseif($order->payment_status === 'paid')
                    <span class="text-green-600 font-medium">
                        ✅ Lunas
                        @if($order->payment_method === 'cash')
                            <span class="ml-1 bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">💵 Tunai</span>
                        @else
                            <span class="ml-1 bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-semibold">💳 Online</span>
                        @endif
                    </span>
                    @else
                    <span class="text-red-600 font-medium">❌ Dibatalkan</span>
                    @endif
                </span>

                {{-- Tombol Bayar Tunai --}}
                @if($order->payment_status === 'pending')
                <form action="{{ route('admin.orders.payCash', $order) }}" method="POST"
                    onsubmit="return confirm('Konfirmasi pelanggan sudah bayar tunai?')">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="text-xs font-semibold bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                        💵 Tandai Lunas (Tunai)
                    </button>
                </form>
                @endif
            </div>
        </div>

        @empty
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Belum ada pesanan
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $orders->links() }}
    </div>

</div>

<script>
    function getStatusLabel(status) {
        return {
            'new': '🆕 Baru',
            'processing': '👨‍🍳 Diproses',
            'ready': '✅ Siap',
            'completed': '✨ Selesai',
        } [status] || status;
    }

    function getStatusColor(status) {
        return {
            'new': 'bg-blue-50 text-blue-700',
            'processing': 'bg-yellow-50 text-yellow-700',
            'ready': 'bg-purple-50 text-purple-700',
            'completed': 'bg-green-50 text-green-700',
        } [status] || 'bg-gray-50 text-gray-700';
    }

    function updateOrderStatus(orderId, newStatus) {
        fetch(`/admin/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    status: newStatus
                }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Reload halaman atau update DOM
                    location.reload();
                }
            })
            .catch(err => console.error('Error:', err));
    }
</script>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: '{{ env("REVERB_APP_KEY") }}',
        wsHost: window.location.hostname,
        wsPort: 8080,
        wssPort: 443,
        forceTLS: false,
        encrypted: false,
        disableStats: true,
    });

    const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

    window.Echo.channel('tenant-{{ Auth::user()->tenant_id }}')
        .listen('.order.status.updated', (e) => {
            console.log('Order updated:', e);
            
            // Putar suara notifikasi jika ada order baru (status: new)
            if (e.order && e.order.status === 'new') {
                notificationSound.play().catch(err => console.log('Autoplay blocked:', err));
            } else {
                // Untuk status selain new, kita tetap putar suara sekilas
                notificationSound.play().catch(err => console.log('Autoplay blocked:', err));
            }

            // Tunggu sebentar biar suara terdengar sebelum reload
            setTimeout(() => {
                location.reload();
            }, 500);
        });
</script>
@endsection