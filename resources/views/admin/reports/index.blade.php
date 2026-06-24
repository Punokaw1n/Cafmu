@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
<div class="space-y-6">

    {{-- Filter Period --}}
    <div class="flex flex-wrap items-center gap-2">
        @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini'] as $key => $label)
        <a href="{{ route('admin.reports.index', ['period' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ $period === $key ? 'bg-[var(--color-primary-600)] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach

        <div class="ml-auto">
            <a href="{{ route('admin.reports.transactions') }}"
               class="text-sm text-[var(--color-primary-600)] hover:underline font-medium flex items-center gap-1">
                Semua Transaksi →
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total Pendapatan</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="text-xs text-green-600 mt-1">Dari pesanan lunas</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total Pesanan</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalOrders }}</p>
            <p class="text-xs text-gray-400 mt-1">Pesanan lunas</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Rata-rata Order</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Per transaksi</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Metode Bayar</p>
            <div class="flex items-center gap-3 mt-2">
                <div class="text-center">
                    <p class="text-lg font-bold text-green-600">{{ $cashOrders ?? 0 }}</p>
                    <p class="text-xs text-gray-400">💵 Tunai</p>
                </div>
                <div class="text-gray-200 font-light text-lg">|</div>
                <div class="text-center">
                    <p class="text-lg font-bold text-blue-600">{{ $onlineOrders ?? 0 }}</p>
                    <p class="text-xs text-gray-400">💳 Online</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Pendapatan Harian --}}
    @if($dailyRevenue->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">📈 Grafik Pendapatan</h3>
        <div style="position:relative; height:220px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-6">
        {{-- Status Breakdown --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Status Pesanan</h3>
            <div class="space-y-3">
                @foreach([
                    ['label' => 'Baru', 'color' => 'bg-blue-400', 'key' => 'new'],
                    ['label' => 'Diproses', 'color' => 'bg-yellow-400', 'key' => 'processing'],
                    ['label' => 'Siap', 'color' => 'bg-purple-400', 'key' => 'ready'],
                    ['label' => 'Selesai', 'color' => 'bg-green-400', 'key' => 'completed'],
                ] as $s)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $s['color'] }}"></span> {{ $s['label'] }}
                    </span>
                    <span class="font-bold text-gray-800">{{ $statusBreakdown[$s['key']] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Top Products --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">🏆 Produk Terlaris</h3>
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

@if($dailyRevenue->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const labels = @json($dailyRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M')));
    const data   = @json($dailyRevenue->pluck('revenue'));
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary-600').trim() || '#d97706';

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data,
                backgroundColor: primaryColor + 'cc',
                borderColor: primaryColor,
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v)
                    }
                }
            }
        }
    });
</script>
@endif
@endsection
