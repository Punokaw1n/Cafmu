@extends('layouts.menu')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4 py-10">

    <div class="w-full max-w-sm space-y-4">
    
        {{-- Logo --}}
        <div class="text-center mb-6">
            @php
                $logoUrl = $currentTenant->getSetting('logo_url');
            @endphp
            @if($logoUrl)
                <img src="{{ Storage::url($logoUrl) }}" alt="Logo" class="h-16 w-auto mx-auto mb-3 object-contain rounded-lg shadow-sm">
            @endif
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $currentTenant->name }}</h1>
        </div>

        {{-- Success Icon --}}
        <div class="text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Pesanan Masuk!</h1>
            <p class="text-gray-500 text-sm mt-1">Pesanan kamu sedang diproses oleh dapur</p>
        </div>

        {{-- Order Info --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-500">No. Pesanan</span>
                <span class="font-bold text-[var(--color-primary-700)]">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-500">Meja</span>
                <span class="font-medium text-gray-800">{{ $order->table->table_number }}</span>
            </div>

            <div class="border-t border-gray-100 pt-4 space-y-2">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $item->quantity }}x {{ $item->product->name }}</span>
                    <span class="text-sm font-medium text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-3 mt-2 border-t border-gray-100">
                <span class="font-bold text-gray-800">Total</span>
                <span class="font-bold text-[var(--color-primary-700)]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($order->payment_status === 'paid')
            <div class="bg-green-50 border border-green-100 rounded-2xl p-4 text-center">
                <p class="text-sm text-green-800 font-medium">
                    ✅ Pembayaran Berhasil
                </p>
                <p class="text-xs text-green-600 mt-1">Terima kasih, pesanan Anda segera disiapkan.</p>
            </div>
        @else
            {{-- Payment Status --}}
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-center">
                <p class="text-sm text-blue-800">
                    💳 Silakan lanjutkan pembayaran dengan klik tombol di bawah
                </p>
            </div>

            {{-- Payment Button --}}
            <button id="pay-button"
                class="w-full bg-[var(--color-primary-600)] hover:bg-[var(--color-primary-700)] text-white font-semibold py-3.5 rounded-2xl transition shadow-sm">
                Bayar Sekarang
            </button>
        @endif

        {{-- Back Button --}}
        <a href="javascript:history.back()"
            class="block w-full text-center bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium py-3.5 rounded-2xl transition">
            Pesan Lagi
        </a>

    </div>
</div>

{{-- Midtrans Snap Script --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) {
                // Payment success
                console.log('Payment success:', result);
                window.location.href = '{{ route("checkout.success", $order->order_number) }}';
            },
            onPending: function(result) {
                // Payment pending
                console.log('Payment pending:', result);
                alert('Menunggu pembayaran Anda');
            },
            onError: function(result) {
                // Payment error
                console.log('Payment error:', result);
                alert('Pembayaran gagal!');
            },
            onClose: function() {
                console.log('Payment popup closed');
            }
        });
    });
</script>
@endsection