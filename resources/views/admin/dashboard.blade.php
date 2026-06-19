@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total Order</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalOrders }}</p>
            <p class="text-xs text-amber-600 mt-1">Semua waktu</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Produk</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalProducts }}</p>
            <p class="text-xs text-amber-600 mt-1">Di menu</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Kategori</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalCategories }}</p>
            <p class="text-xs text-amber-600 mt-1">Aktif</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Meja</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalTables }}</p>
            <p class="text-xs text-amber-600 mt-1">Terdaftar</p>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Pesanan Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">No. Order</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Meja</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Total</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Pembayaran</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $order->order_number }}</td>
                        <td class="px-6 py-4 text-gray-600">Meja {{ $order->table->table_number }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColor = match($order->status) {
                                    'new'        => 'bg-blue-50 text-blue-700',
                                    'processing' => 'bg-yellow-50 text-yellow-700',
                                    'ready'      => 'bg-purple-50 text-purple-700',
                                    'completed'  => 'bg-green-50 text-green-700',
                                    default      => 'bg-gray-50 text-gray-700',
                                };
                                $statusLabel = match($order->status) {
                                    'new'        => 'Baru',
                                    'processing' => 'Diproses',
                                    'ready'      => 'Siap',
                                    'completed'  => 'Selesai',
                                    default      => $order->status,
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $payColor = match($order->payment_status) {
                                    'pending'   => 'bg-orange-50 text-orange-700',
                                    'paid'      => 'bg-green-50 text-green-700',
                                    'cancelled' => 'bg-red-50 text-red-700',
                                    default     => 'bg-gray-50 text-gray-700',
                                };
                                $payLabel = match($order->payment_status) {
                                    'pending'   => 'Belum Bayar',
                                    'paid'      => 'Lunas',
                                    'cancelled' => 'Dibatalkan',
                                    default     => $order->payment_status,
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $payColor }}">{{ $payLabel }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">{{ $order->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Belum ada pesanan masuk
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
