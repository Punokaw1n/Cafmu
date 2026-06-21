@extends('layouts.app')
@section('title', 'Riwayat Transaksi')

@section('content')
<div class="space-y-4">

    {{-- Back Link --}}
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Laporan
    </a>

    {{-- Filter Form --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.reports.transactions') }}" class="grid grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari No. Order</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                       placeholder="ORD-...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status Pesanan</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Baru</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Diproses</option>
                    <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Siap</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status Bayar</label>
                <select name="payment_status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ request('payment_status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="col-span-5 flex gap-2">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Filter
                </button>
                <a href="{{ route('admin.reports.transactions') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">No. Order</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Meja</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Items</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Total</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Bayar</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($transactions as $order)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $order->order_number }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $order->table->table_number ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500 text-xs">{{ $order->items->count() }} item</td>
                    <td class="px-6 py-4 font-medium text-gray-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @php
                            $statusColor = match($order->status) {
                                'new' => 'bg-blue-50 text-blue-700',
                                'processing' => 'bg-yellow-50 text-yellow-700',
                                'ready' => 'bg-purple-50 text-purple-700',
                                'completed' => 'bg-green-50 text-green-700',
                                default => 'bg-gray-50 text-gray-700',
                            };
                            $statusLabel = match($order->status) {
                                'new' => 'Baru', 'processing' => 'Diproses',
                                'ready' => 'Siap', 'completed' => 'Selesai',
                                default => $order->status,
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $payColor = match($order->payment_status) {
                                'pending' => 'bg-orange-50 text-orange-700',
                                'paid' => 'bg-green-50 text-green-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                                default => 'bg-gray-50 text-gray-700',
                            };
                            $payLabel = match($order->payment_status) {
                                'pending' => 'Belum Bayar', 'paid' => 'Lunas',
                                'cancelled' => 'Dibatalkan', default => $order->payment_status,
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $payColor }}">{{ $payLabel }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $order->created_at->format('d M Y, H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">Tidak ada transaksi ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $transactions->links() }}
    </div>

</div>
@endsection
