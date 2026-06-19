@extends('layouts.app')
@section('title', 'Meja')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $tables->count() }} meja terdaftar</p>
        <a href="{{ route('admin.tables.create') }}"
           class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Meja
        </a>
    </div>

    {{-- Grid Meja --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($tables as $table)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3"
             x-data="{ status: '{{ $table->status }}' }">

            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-bold text-gray-800 text-lg">Meja {{ $table->table_number }}</p>
                    <span x-text="status === 'available' ? 'Tersedia' : status === 'occupied' ? 'Ditempati' : 'Perlu Dibersihkan'"
                          :class="{
                              'bg-green-50 text-green-700': status === 'available',
                              'bg-red-50 text-red-700': status === 'occupied',
                              'bg-yellow-50 text-yellow-700': status === 'dirty'
                          }"
                          class="px-2 py-0.5 rounded-full text-xs font-medium">
                    </span>
                </div>
                @if($table->is_active)
                    <span class="w-2 h-2 rounded-full bg-green-400 mt-1.5"></span>
                @else
                    <span class="w-2 h-2 rounded-full bg-gray-300 mt-1.5"></span>
                @endif
            </div>

            {{-- QR Code --}}
            <div class="flex justify-center bg-gray-50 rounded-lg p-3">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate(url('/menu/' . $table->qr_code_string)) !!}
            </div>

            {{-- Update Status --}}
            <div class="flex flex-col gap-1.5">
                <p class="text-xs text-gray-400 font-medium">Update Status:</p>
                <div class="flex gap-1.5">
                    <button @click="updateStatus('available')"
                            :class="status === 'available' ? 'ring-2 ring-green-400' : ''"
                            class="flex-1 text-xs bg-green-50 text-green-700 py-1.5 rounded-lg transition hover:bg-green-100">
                        Kosong
                    </button>
                    <button @click="updateStatus('dirty')"
                            :class="status === 'dirty' ? 'ring-2 ring-yellow-400' : ''"
                            class="flex-1 text-xs bg-yellow-50 text-yellow-700 py-1.5 rounded-lg transition hover:bg-yellow-100">
                        Kotor
                    </button>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 pt-1 border-t border-gray-100">
                <a href="{{ route('admin.tables.edit', $table) }}"
                   class="flex-1 text-center text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-1.5 rounded-lg transition">Edit</a>
                <form method="POST" action="{{ route('admin.tables.destroy', $table) }}"
                      onsubmit="return confirm('Hapus meja ini?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full text-xs bg-red-50 hover:bg-red-100 text-red-600 py-1.5 rounded-lg transition">Hapus</button>
                </form>
            </div>

            <script>
                function updateStatus(newStatus) {
                    // Will be handled by Alpine.js
                }
            </script>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                // updateStatus handled inline
            });
        </script>

        @empty
        <div class="col-span-4 bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-12 text-center text-gray-400">
            Belum ada meja. <a href="{{ route('admin.tables.create') }}" class="text-amber-600 hover:underline">Tambah sekarang</a>
        </div>
        @endforelse
    </div>

</div>

<script>
function tableCard(tableId, initialStatus) {
    return {
        status: initialStatus,
        updateStatus(newStatus) {
            fetch(`/admin/tables/${tableId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ status: newStatus }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) this.status = data.status;
            });
        }
    }
}
</script>
@endsection
