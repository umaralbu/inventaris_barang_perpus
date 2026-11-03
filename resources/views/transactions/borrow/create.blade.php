@extends('layouts.app')

@section('title', 'Form Pengajuan Peminjaman')

@section('header_content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir Pengajuan Peminjaman</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Pilih barang dari katalog perpustakaan dan tentukan staf penanggung jawab serta batas waktu pengembalian.
        </p>
    </div>
    <a href="{{ route('transactions.index') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali ke Riwayat</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto" 
     x-data="{
        catalogs: {{ Js::from($catalogs) }},
        selectedCatalogId: '{{ old('catalog_id', $selectedCatalogId ?? '') }}',
        quantity: {{ old('quantity', 1) }},
        submitting: false,
        get selectedCatalog() {
            return this.catalogs.find(c => c.id == this.selectedCatalogId) || null;
        },
        get maxStock() {
            return this.selectedCatalog ? (this.selectedCatalog.available_units_count || 0) : 0;
        }
     }">

    <!-- Alert Edukasi SOP PRD -->
    <div class="mb-6 p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm shadow-indigo-600/30">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="text-xs text-indigo-900 leading-relaxed">
            <p class="font-bold text-sm">Alur Pengajuan & Verifikasi Aset</p>
            <p class="mt-0.5 text-indigo-700">
                Anda cukup memilih katalog barang dan jumlah kuantitas yang dibutuhkan. Permohonan akan berstatus <strong class="text-indigo-950 font-semibold">"Menunggu Persetujuan"</strong> dan stok unit fisik akan dialokasikan oleh Admin saat disetujui.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('transactions.borrow.store') }}" @submit="submitting = true" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                <span>1. Pilih Barang dari Katalog Perpustakaan</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dropdown Katalog -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Katalog Barang <span class="text-rose-500">*</span>
                    </label>
                    <select name="catalog_id" x-model="selectedCatalogId" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('catalog_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Barang dari Katalog --</option>
                        @foreach ($catalogs as $cat)
                            <option value="{{ $cat->id }}" {{ (string) old('catalog_id', $selectedCatalogId) === (string) $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} {{ $cat->brand ? "({$cat->brand})" : '' }} — Tersedia: {{ $cat->available_units_count }} {{ $cat->unit_name ?? 'Unit' }}
                            </option>
                        @endforeach
                    </select>
                    @error('catalog_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Info Katalog Terpilih -->
                <div x-show="selectedCatalog" class="md:col-span-2 p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row gap-4 items-start">
                    <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-sm">
                        <template x-if="selectedCatalog && selectedCatalog.photo">
                            <img :src="'/' + selectedCatalog.photo" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!selectedCatalog || !selectedCatalog.photo">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </template>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700" 
                                  x-text="selectedCatalog ? (selectedCatalog.category ? selectedCatalog.category.name : 'Umum') : ''"></span>
                            <span class="text-xs text-slate-400 font-medium" x-text="selectedCatalog ? selectedCatalog.brand : ''"></span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 mt-1" x-text="selectedCatalog ? selectedCatalog.name : ''"></h4>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="selectedCatalog ? (selectedCatalog.specification || selectedCatalog.description || 'Tidak ada spesifikasi khusus.') : ''"></p>
                    </div>

                    <div class="text-right sm:border-l sm:border-slate-200 sm:pl-4 self-center">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Unit Tersedia</span>
                        <div class="text-lg font-black" :class="maxStock > 0 ? 'text-emerald-600' : 'text-rose-600'">
                            <span x-text="maxStock"></span> <span class="text-xs font-normal text-slate-500" x-text="selectedCatalog ? (selectedCatalog.unit_name || 'Unit') : ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Input Kuantitas -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Jumlah Kuantitas Pinjam <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="quantity" min="1" :max="maxStock > 0 ? maxStock : 1" x-model.number="quantity" required
                               class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('quantity') border-rose-500 @enderror">
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-semibold text-slate-400 pointer-events-none"
                              x-text="selectedCatalog ? (selectedCatalog.unit_name || 'Unit') : 'Unit'">
                        </span>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400" x-show="maxStock > 0">
                        Maksimal peminjaman: <strong class="text-slate-700" x-text="maxStock"></strong> unit.
                    </p>
                    <p class="mt-1 text-xs text-rose-600 font-semibold" x-show="selectedCatalog && maxStock === 0">
                        Stok barang ini saat ini sedang habis atau seluruh unit sedang dipinjam/dalam perbaikan.
                    </p>
                    @error('quantity')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                <span>2. Penanggung Jawab, Lokasi & Waktu Peminjaman</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Staf Peminjam / Penanggung Jawab Fisik -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Staf Peminjam / Penanggung Jawab <span class="text-rose-500">*</span>
                    </label>
                    <select name="staff_id" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('staff_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Staf Perpustakaan --</option>
                        @foreach ($staffMembers as $staff)
                            <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} {{ $staff->nip ? "({$staff->nip})" : '' }} — {{ $staff->position ?? 'Staf' }}
                            </option>
                        @endforeach
                    </select>
                    @error('staff_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lokasi Tujuan Penggunaan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Lokasi Tujuan Penggunaan <span class="text-rose-500">*</span>
                    </label>
                    <select name="to_location_id" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('to_location_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Ruangan / Lokasi --</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('to_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_location_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Peminjaman -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Tanggal Peminjaman <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('transaction_date') border-rose-500 @enderror">
                    @error('transaction_date')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estimasi Tanggal Pengembalian (expected_return_date) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Estimasi Tanggal Pengembalian <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date', date('Y-m-d', strtotime('+7 days'))) }}" required
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('expected_return_date') border-rose-500 @enderror">
                    <p class="mt-1 text-[11px] text-slate-400">Default: 7 hari dari tanggal hari ini.</p>
                    @error('expected_return_date')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keperluan / Catatan -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Keperluan / Catatan Peminjaman
                    </label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Digunakan untuk kegiatan pelatihan literasi digital di Ruang Multimedia..."
                              class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('notes') border-rose-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Submit -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('transactions.index') }}" 
               class="px-5 py-2.5 text-xs sm:text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition">
                Batal
            </a>
            <button type="submit" 
                    :disabled="submitting || (selectedCatalog && maxStock === 0)"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition shadow-sm shadow-indigo-600/30">
                <template x-if="submitting">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="submitting ? 'Sedang Memproses...' : 'Kirim Permohonan Pinjam'"></span>
            </button>
        </div>
    </form>
</div>
@endsection

