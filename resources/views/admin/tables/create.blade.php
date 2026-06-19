@extends('layouts.app')
@section('title', 'Tambah Meja')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.tables.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor / Nama Meja <span class="text-red-500">*</span></label>
                <input type="text" name="table_number" value="{{ old('table_number') }}"
                       class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent @error('table_number') border-red-400 @enderror"
                       placeholder="Contoh: 1, A1, VIP-1">
                @error('table_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500">
                    <span class="text-sm font-medium text-gray-700">Meja aktif</span>
                </label>
            </div>

            <p class="text-xs text-gray-400 bg-amber-50 px-4 py-3 rounded-lg">
                QR Code akan otomatis digenerate setelah meja disimpan.
            </p>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
                    Simpan Meja
                </button>
                <a href="{{ route('admin.tables.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
