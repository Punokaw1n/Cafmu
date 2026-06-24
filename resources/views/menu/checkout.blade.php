@extends('layouts.menu')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white px-4 py-4 flex items-center gap-3 shadow-sm sticky top-0 z-10">
        <a href="javascript:history.back()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="font-bold text-gray-800">Konfirmasi Pesanan</h1>
    </div>

    <div class="px-4 py-4 space-y-4">

        @if(session('error'))
            <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm border border-red-100">
                {{ session('error') }}
            </div>
        @endif

        {{-- Order Summary --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <h2 class="font-semibold text-gray-800 mb-3">Ringkasan Pesanan</h2>
            <div class="space-y-3">
                @foreach($cart as $item)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="bg-amber-100 text-amber-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">{{ $item['quantity'] }}</span>
                        <span class="text-sm text-gray-700">{{ $item['name'] }}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-800">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100">
                <span class="font-bold text-gray-800">Total</span>
                <span class="font-bold text-amber-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <h2 class="font-semibold text-gray-800 mb-3">Info Pelanggan <span class="text-xs font-normal text-gray-400">(Opsional)</span></h2>
            <form method="POST" action="{{ route('checkout.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="table_id" value="{{ $table->id }}">

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="Nama kamu (opsional)">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No. WhatsApp</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="08xxxxxxxxxx (untuk e-receipt)">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"
                              placeholder="Catatan untuk dapur...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-4 rounded-2xl transition shadow-sm mt-2">
                    Pesan Sekarang 🍽️
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
