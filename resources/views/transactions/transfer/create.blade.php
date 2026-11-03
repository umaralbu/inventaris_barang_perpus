@extends('layouts.app')

@section('title', 'Perpindahan Lokasi Massal (Mutasi)')

@section('header_content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Perpindahan Lokasi Massal (Batch Transfer)</h1>
        <p class="text-sm text-slate-500 mt-1">
            Pindahkan satu atau banyak unit fisik inventaris sekaligus ke lokasi ruangan atau rak penyimpanan baru.
        </p>
    </div>
    <a href="{{ route('transactions.index') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Daftar Transaksi</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{
        submitting: false,
        fromLocationId: '{{ old('from_location_id', $selectedFromLocationId ?? '') }}',
        selectedUnits: {{ Js::from(old('unit_ids', [])) }},
        onLocationChange(e) {
            const locId = e.target.value;
            if (locId) {
                window.location.href = '{{ route('transactions.transfer.create') }}?from_location_id=' + locId;
            }
        },
        toggleUnit(id) {
            const index = this.selectedUnits.indexOf(id);
            if (index > -1) {
                this.selectedUnits.splice(index, 1);
            } else {
                this.selectedUnits.push(id);
            }
        },
        selectAllUnits() {
            const all = {{ Js::from($availableUnits->pluck('id')) }};
            if (this.selectedUnits.length === all.length) {
                this.selectedUnits = [];
            } else {
                this.selectedUnits = [...all];
            }
        }
     }">

    <!-- Alert Aturan Mutasi Massal -->
    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-start gap-3.5 text-sm">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm shadow-indigo-600/30">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
        </div>
        <div class="text-indigo-950 leading-relaxed">
            <h4 class="font-bold text-sm">Ketentuan Perpindahan Aset Antar-Ruangan</h4>
            <p class="mt-0.5 text-xs text-indigo-800">
                Pilih lokasi asal untuk melihat seluruh unit fisik berkondisi siap pakai di ruangan tersebut. Anda dapat mencentang beberapa unit dari berbagai katalog sekaligus untuk dipindahkan bersamaan dalam satu nomor mutasi resmi.
            </p>
        </div>
    </div>

    <!-- 1. Pemilihan Lokasi Asal -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
            <span>1. Tentukan Lokasi Asal Barang</span>
        </h2>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                Ruangan / Lokasi Asal <span class="text-rose-500">*</span>
            </label>
            <select name="from_location_selector" x-model="fromLocationId" @change="onLocationChange($event)"
                    class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                <option value="">-- Pilih Lokasi Asal --</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" {{ (string) old('from_location_id', $selectedFromLocationId) === (string) $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>
            @if (!$selectedFromLocationId)
                <p class="mt-2 text-xs text-slate-400 italic">Pilih lokasi asal di atas untuk memuat daftar unit fisik yang siap dipindahkan.</p>
            @endif
        </div>
    </div>

    @if ($selectedFromLocationId)
        <!-- 2. Form Mutasi & Pilihan Unit Fisik -->
        <form method="POST" action="{{ route('transactions.transfer.store') }}" @submit="submitting = true" class="space-y-6">
            @csrf
            <input type="hidden" name="from_location_id" value="{{ $selectedFromLocationId }}">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <span>2. Pilih Unit Fisik Yang Akan Dipindahkan</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Menampilkan seluruh unit fisik berstatus 'Tersedia' yang saat ini berada di lokasi terpilih.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectAllUnits()"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                            <span x-text="selectedUnits.length === {{ $availableUnits->count() }} ? 'Batal Pilih Semua' : 'Pilih Semua Unit'"></span>
                        </button>
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800">
                            <span x-text="selectedUnits.length"></span> dari {{ $availableUnits->count() }} Unit Terpilih
                        </span>
                    </div>
                </div>

                @error('unit_ids')
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
                        {{ $message }}
                    </div>
                @enderror

                @if ($availableUnits->isEmpty())
                    <div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200">
                        <p class="text-sm font-bold text-slate-700">Tidak ada unit fisik berstatus 'Tersedia' di lokasi ini.</p>
                        <p class="text-xs text-slate-400 mt-1">Seluruh unit di lokasi ini mungkin sedang dipinjam atau tidak ada barang yang tersimpan.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($availableUnits as $unit)
                            <div class="relative p-3.5 rounded-xl border transition cursor-pointer select-none"
                                 :class="selectedUnits.includes({{ $unit->id }}) ? 'bg-indigo-50/70 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50/50 border-slate-200 hover:bg-white hover:border-slate-300'"
                                 @click="toggleUnit({{ $unit->id }})">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="font-mono font-bold text-xs text-slate-900 tracking-tight flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>{{ $unit->code }}</span>
                                        </div>
                                        <div class="text-xs font-semibold text-slate-800 mt-1 truncate">
                                            {{ $unit->catalog->name ?? $unit->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $unit->catalog->category->name ?? 'Umum' }}
                                        </div>
                                    </div>

                                    <input type="checkbox" name="unit_ids[]" value="{{ $unit->id }}"
                                           :checked="selectedUnits.includes({{ $unit->id }})"
                                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 pointer-events-none mt-0.5">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 3. Lokasi Tujuan & Detail Mutasi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span>3. Lokasi Tujuan & Penanggung Jawab Mutasi</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Lokasi Tujuan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Lokasi Tujuan Perpindahan <span class="text-rose-500">*</span>
                        </label>
                        <select name="to_location_id" required
                                class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('to_location_id') border-rose-500 @enderror">
                            <option value="">-- Pilih Lokasi Tujuan Baru --</option>
                            @foreach ($locations as $loc)
                                @if ($loc->id != $selectedFromLocationId)
                                    <option value="{{ $loc->id }}" {{ old('to_location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('to_location_id')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Staf Penanggung Jawab Pelaksana -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Staf Pelaksana Perpindahan <span class="text-rose-500">*</span>
                        </label>
                        <select name="staff_id" required
                                class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('staff_id') border-rose-500 @enderror">
                            <option value="">-- Pilih Staf Perpustakaan --</option>
                            @foreach ($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ $staff->position ?? 'Staf' }})
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Mutasi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Tanggal Perpindahan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('transaction_date') border-rose-500 @enderror">
                        @error('transaction_date')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan / Alasan Mutasi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Alasan Perpindahan (Opsional)
                        </label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Penataan ulang ruangan laboratorium multimedia..."
                               class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('notes') border-rose-500 @enderror">
                        @error('notes')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('transactions.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" 
                        :disabled="submitting || selectedUnits.length === 0"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition shadow-sm shadow-indigo-600/30">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span x-text="submitting ? 'Sedang Memproses...' : 'Proses Mutasi Massal (' + selectedUnits.length + ' Unit)'"></span>
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
