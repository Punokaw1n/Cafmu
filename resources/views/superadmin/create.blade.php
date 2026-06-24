@extends('layouts.superadmin')

@section('title', 'Tambah Kafe Baru')

@section('content')

<div class="max-w-2xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('superadmin.index') }}" class="hover:text-blue-600 transition">Semua Kafe</a>
        <span>›</span>
        <span class="text-gray-800 font-medium">Tambah Kafe Baru</span>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Informasi Kafe Baru</h3>
            <p class="text-sm text-gray-500 mt-1">Isi data di bawah untuk mendaftarkan kafe klien baru ke platform.</p>
        </div>

        <form action="{{ route('superadmin.store') }}" method="POST" class="px-6 py-6 space-y-5">
            @csrf

            {{-- Nama Kafe --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Kafe <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                    placeholder="contoh: Kopi Kenangan Buaran"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Slug / Kode Unik <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 bg-gray-100 px-3 py-2.5 rounded-xl border border-gray-200 whitespace-nowrap">?tenant=</span>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                        placeholder="kopi-kenangan-buaran"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('slug') border-red-400 @enderror">
                </div>
                <p class="text-xs text-gray-400 mt-1">Hanya huruf kecil, angka, dan tanda hubung (-). Tidak bisa diubah setelah tersimpan.</p>
                @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-gray-100">

            <p class="text-sm font-semibold text-gray-700">Akun Admin Kafe</p>
            <p class="text-xs text-gray-400 -mt-3">Informasi login yang akan diberikan ke pemilik kafe.</p>

            {{-- Nama Admin --}}
            <div>
                <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Admin <span class="text-red-500">*</span>
                </label>
                <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}"
                    placeholder="contoh: Budi Santoso"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('admin_name') border-red-400 @enderror">
                @error('admin_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email Admin --}}
            <div>
                <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email Admin <span class="text-red-500">*</span>
                </label>
                <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}"
                    placeholder="admin@kopikenangan.com"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('admin_email') border-red-400 @enderror">
                @error('admin_email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Admin --}}
            <div>
                <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Password Admin <span class="text-red-500">*</span>
                </label>
                <input type="text" id="admin_password" name="admin_password"
                    placeholder="Minimal 8 karakter"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('admin_password') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1">Password ini yang akan diberikan ke pemilik kafe untuk pertama kali login.</p>
                @error('admin_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition shadow-sm">
                    Daftarkan Kafe
                </button>
                <a href="{{ route('superadmin.index') }}"
                    class="text-sm font-medium text-gray-600 hover:text-gray-800 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Info Box --}}
    <div class="mt-4 bg-blue-50 border border-blue-100 rounded-2xl p-5">
        <p class="text-sm font-semibold text-blue-800 mb-2">📋 Setelah kafe berhasil didaftarkan:</p>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>1. Kirimkan info berikut ke pemilik kafe via WhatsApp:</li>
            <li class="pl-4 text-blue-600 font-mono text-xs">
                URL Login: <strong>https://[domain-anda]/login?tenant=[slug]</strong>
            </li>
            <li class="pl-4 text-blue-600 font-mono text-xs">
                Email: <strong>[email yang Anda isi]</strong>
            </li>
            <li class="pl-4 text-blue-600 font-mono text-xs">
                Password: <strong>[password yang Anda isi]</strong>
            </li>
            <li>2. Pemilik kafe langsung bisa login dan mulai setup menu!</li>
        </ul>
    </div>

</div>

@endsection
