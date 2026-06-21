@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
<div class="space-y-6">

    {{-- Filter Period --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.reports.index', ['period' => 'today']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ $period === 'today' ? 'bg-amber-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Hari Ini
        </a>
        <a href="{{ route('admin.reports.index', ['period' => 'week']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ $period === 'week' ? 'bg-amber-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Minggu Ini
        </a>
        <a href="{{ route('admin.reports.index', ['period' => 'month']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ $period === 'month' ? 'bg-amber-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Bulan Ini
        </a>

        <div class="ml-auto">
            <a href="{{ route('admin.reports.transactions') }}"
               class="text-sm text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1">
                Lihat Semua Transaksi
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total Pendapatan</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="text-xs text-green-600 mt-1">Dari pesanan lunas</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total Pesanan</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalOrders }}</p>
            <p class="text-xs text-amber-600 mt-1">Pesanan lunas</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Rata-rata Order</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Per transaksi</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        {{-- Status Breakdown --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Status Pesanan</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span> Baru
                    </span>
                    <span class="font-bold text-gray-800">{{ $statusBreakdown['new'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-yellow-400"></span> Diproses
                    </span>
                    <span class="font-bold text-gray-800">{{ $statusBreakdown['processing'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-400"></span> Siap
                    </span>
                    <span class="font-bold text-gray-800">{{ $statusBreakdown['ready'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-400"></span> Selesai
                    </span>
                    <span class="font-bold text-gray-800">{{ $statusBreakdown['completed'] }}</span>
                </div>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Produk Terlaris</h3>
            <div class="space-y-3">
                @forelse($topProducts as $index => $item)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-amber-50 text-amber-700 text-xs font-bold flex items-center justify-center">{{ $index + 1 }}</span>
                        <span class="text-sm text-gray-700">{{ $item->product->name ?? 'Produk dihapus' }}</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-800">{{ $item->total_qty }}x</p>
                        <p class="text-xs text-gray-400">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada data penjualan</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Daily Revenue Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Pendapatan Harian</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Tanggal</th>
                        <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($dailyRevenue as $day)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-gray-700">{{ \Carbon\Carbon::parse($day->date)->translatedFormat('l, d F Y') }}</td>
                        <td class="px-6 py-3 text-right font-medium text-gray-800">Rp {{ number_format($day->revenue, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-8 text-center text-gray-400">Belum ada data pendapatan di periode ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
