@extends('layouts.superadmin')

@section('title', 'Semua Kafe Klien')

@section('content')

{{-- Stats Row --}}
<div class="grid grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Kafe</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $tenants->count() }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Kafe Aktif</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $tenants->where('is_active', true)->count() }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Kafe Nonaktif</p>
        <p class="text-3xl font-bold text-red-500 mt-1">{{ $tenants->where('is_active', false)->count() }}</p>
    </div>
</div>

{{-- Tenant Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Daftar Kafe Klien</h3>
        <a href="{{ route('superadmin.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kafe Baru
        </a>
    </div>

    @if($tenants->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium">Belum ada kafe yang terdaftar</p>
            <p class="text-gray-400 text-sm mt-1">Tambahkan kafe pertama Anda dengan tombol di atas.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Kafe</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Slug / URL</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Admin</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Orders</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Status</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($tenants as $tenant)
                    <tr class="hover:bg-gray-50 transition">
                        {{-- Nama Kafe --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">
                                    {{ substr($tenant->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">{{ $tenant->name }}</p>
                                    <p class="text-xs text-gray-400">Bergabung {{ $tenant->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Slug --}}
                        <td class="px-6 py-4">
                            <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-lg">?tenant={{ $tenant->subdomain }}</code>
                        </td>

                        {{-- Admin --}}
                        <td class="px-6 py-4">
                            @php $admin = $tenant->users->first(); @endphp
                            @if($admin)
                                <p class="text-sm text-gray-700 font-medium">{{ $admin->name }}</p>
                                <p class="text-xs text-gray-400">{{ $admin->email }}</p>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum ada admin</span>
                            @endif
                        </td>

                        {{-- Orders Count --}}
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-semibold text-gray-700">{{ $tenant->orders_count }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($tenant->is_active)
                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Toggle Aktif/Nonaktif --}}
                                <form action="{{ route('superadmin.toggle', $tenant) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="text-xs font-medium px-3 py-1.5 rounded-lg border transition
                                               {{ $tenant->is_active
                                                  ? 'border-red-200 text-red-600 hover:bg-red-50'
                                                  : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                                        {{ $tenant->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                {{-- Link Masuk ke Dashboard Kafe --}}
                                @if($tenant->is_active)
                                <a href="{{ url('/admin?tenant=' . $tenant->subdomain) }}"
                                   target="_blank"
                                   class="text-xs font-medium px-3 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition">
                                    Lihat Dashboard ↗
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
