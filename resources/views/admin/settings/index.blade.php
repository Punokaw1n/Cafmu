@php
use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Logo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo Kafe</label>
                <div class="flex items-center gap-4">
                    @if(!empty($settings['logo_url']))
                        <img src="{{ Storage::url($settings['logo_url']) }}" alt="Logo"
                             class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 text-xs">
                            No Logo
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*"
                           class="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <p class="text-xs text-gray-400 mt-1">Maks. 1MB. Format: JPG, PNG, WEBP</p>
                @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Business Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Kafe <span class="text-red-500">*</span></label>
                <input type="text" name="business_name" value="{{ old('business_name', $tenant->name) }}"
                       class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                @error('business_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Business Address --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Bisnis</label>
                <textarea name="business_address" rows="3"
                          class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
                          placeholder="Jl. Contoh No. 123, Kota...">{{ old('business_address', $settings['business_address'] ?? '') }}</textarea>
            </div>

            {{-- WhatsApp Number --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor WhatsApp Bisnis</label>
                <input type="text" name="wa_number" value="{{ old('wa_number', $settings['wa_number'] ?? '') }}"
                       class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                       placeholder="628123456789">
                <p class="text-xs text-gray-400 mt-1">Format: 628xxx (tanpa spasi/strip). Digunakan untuk kirim e-receipt ke pelanggan.</p>
            </div>

            {{-- Primary Color --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Warna Brand</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color'] ?? '#d97706') }}"
                           class="w-14 h-10 border border-gray-200 rounded-lg cursor-pointer">
                    <span class="text-sm text-gray-500">Pilih warna utama untuk tampilan menu & dashboard</span>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
