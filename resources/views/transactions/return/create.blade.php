@extends('layouts.app')

@section('title', 'Form Pengembalian Barang')

@section('header_content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir Pengembalian Barang</h1>
        <p class="text-sm text-slate-500 mt-1">
            Catat pengembalian unit inventaris perpustakaan (pengembalian penuh atau pengembalian bertahap/parsial).
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
        selectedBorrowId: '{{ old('borrow_transaction_id', $selectedBorrow->id ?? '') }}',
        onBorrowChange(e) {
            const id = e.target.value;
            if (id) {
                window.location.href = '{{ route('transactions.return.create') }}?borrow_id=' + id;
            }
        }
     }">

    <!-- Alert SOP Pengembalian & Partial Return -->
    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-start gap-3.5 text-sm">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm shadow-indigo-600/30">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="text-indigo-950 leading-relaxed">
            <h4 class="font-bold text-sm">Alur Pengembalian Penuh & Parsial (Partial Return)</h4>
            <p class="mt-0.5 text-xs text-indigo-800">
                Anda dapat mengembalikan seluruh unit sekaligus atau sebagian unit secara bertahap. Pastikan kondisi fisik diperiksa dengan jujur: jika <strong>'Baik'</strong> unit langsung siap dipinjam kembali, jika <strong>'Rusak / Perlu Perbaikan'</strong> unit akan masuk antrean perbaikan.
            </p>
        </div>
    </div>

    <!-- 1. Pemilihan Transaksi Peminjaman Asal -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
            <span>1. Pilih Transaksi Peminjaman Aktif</span>
        </h2>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                Pilih Dokumen Peminjaman <span class="text-rose-500">*</span>
            </label>
            <select name="borrow_selector" x-model="selectedBorrowId" @change="onBorrowChange($event)"
                    class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                <option value="">-- Pilih Transaksi Pinjaman --</option>
                @foreach ($activeBorrows as $b)
                    @php
                        $unreturned = $b->details->where('is_returned', false)->count();
                    @endphp
                    <option value="{{ $b->id }}" {{ (string) old('borrow_transaction_id', $selectedBorrow->id ?? '') === (string) $b->id ? 'selected' : '' }}>
                        {{ $b->transaction_code }} — {{ $b->catalog->name ?? ($b->item->name ?? 'Barang') }} (Peminjam: {{ $b->staff->name ?? '-' }}) — Sisa: {{ $unreturned }} unit
                    </option>
                @endforeach
            </select>
            @if ($activeBorrows->isEmpty())
                <p class="mt-2 text-xs text-slate-400 italic">Tidak ada transaksi peminjaman aktif yang menunggu pengembalian saat ini.</p>
            @endif
        </div>

        @if ($selectedBorrow)
            <!-- Pratinjau Info Transaksi Peminjaman -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block mb-0.5">Katalog Barang</span>
                    <strong class="text-sm text-slate-900">{{ $selectedBorrow->catalog->name ?? '-' }}</strong>
                    <div class="text-slate-500 mt-0.5">Kategori: {{ $selectedBorrow->catalog->category->name ?? 'Umum' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block mb-0.5">Staf Peminjam</span>
                    <strong class="text-sm text-slate-900">{{ $selectedBorrow->staff->name ?? '-' }}</strong>
                    <div class="text-slate-500 mt-0.5">NIP: {{ $selectedBorrow->staff->nip ?? '-' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 font-bold uppercase tracking-wider block mb-0.5">Jadwal Pinjam</span>
                    <div class="text-slate-700">Tgl: <strong>{{ $selectedBorrow->transaction_date ? $selectedBorrow->transaction_date->format('d M Y') : '-' }}</strong></div>
                    <div class="text-slate-700 mt-0.5">Batas: <strong class="{{ $selectedBorrow->isOverdue() ? 'text-rose-600 font-bold' : '' }}">{{ $selectedBorrow->expected_return_date ? $selectedBorrow->expected_return_date->format('d M Y') : '-' }}</strong></div>
                </div>
            </div>
        @endif
    </div>

    @if ($selectedBorrow)
        @php
            $unreturnedDetails = $selectedBorrow->details->where('is_returned', false);
        @endphp

        <!-- 2. Form Rincian Pengembalian Unit -->
        <form method="POST" action="{{ route('transactions.return.store') }}" @submit="submitting = true" class="space-y-6"
              x-data="{
                selectAll: true,
                unitSelections: {},
                init() {
                    @foreach ($unreturnedDetails as $d)
                        this.unitSelections[{{ $d->item_id }}] = true;
                    @endphp
                },
                toggleAll() {
                    const nextVal = !this.isAllSelected();
                    Object.keys(this.unitSelections).forEach(k => {
                        this.unitSelections[k] = nextVal;
                    });
                },
                isAllSelected() {
                    const keys = Object.keys(this.unitSelections);
                    return keys.length > 0 && keys.every(k => this.unitSelections[k]);
                },
                getSelectedCount() {
                    return Object.values(this.unitSelections).filter(Boolean).length;
                }
              }">
            @csrf
            <input type="hidden" name="borrow_transaction_id" value="{{ $selectedBorrow->id }}">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <span>2. Rincian Unit Fisik & Kondisi Saat Kembali</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Centang unit yang dikembalikan sekarang, dan tentukan kondisi fisiknya masing-masing.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="toggleAll()"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                            <span x-text="isAllSelected() ? 'Hapus Semua Pilihan' : 'Pilih Semua Unit'"></span>
                        </button>
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800">
                            <span x-text="getSelectedCount()"></span> dari {{ $unreturnedDetails->count() }} Unit Dipilih
                        </span>
                    </div>
                </div>

                @error('units')
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
                        {{ $message }}
                    </div>
                @enderror

                <!-- Tabel Rincian Unit Fisik -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">Pilih</th>
                                <th class="py-3 px-4">Kode Unit Fisik</th>
                                <th class="py-3 px-4">Kondisi Sebelum Pinjam</th>
                                <th class="py-3 px-4">Kondisi Fisik Saat Kembali <span class="text-rose-500">*</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($unreturnedDetails as $index => $detail)
                                <tr class="hover:bg-slate-50/70 transition" :class="unitSelections[{{ $detail->item_id }}] ? 'bg-indigo-50/20' : ''">
                                    <!-- Checkbox -->
                                    <td class="py-3 px-4 text-center">
                                        <input type="hidden" name="units[{{ $index }}][item_id]" value="{{ $detail->item_id }}">
                                        <input type="checkbox" name="units[{{ $index }}][selected]" value="1"
                                               x-model="unitSelections[{{ $detail->item_id }}]"
                                               class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                                    </td>

                                    <!-- Kode Unit -->
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900 text-xs sm:text-sm">
                                        {{ $detail->item->code ?? '-' }}
                                    </td>

                                    <!-- Kondisi Sebelum -->
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                            {{ $detail->condition_before->value ?? 'Baik' }}
                                        </span>
                                    </td>

                                    <!-- Kondisi Saat Kembali -->
                                    <td class="py-3 px-4">
                                        <select name="units[{{ $index }}][condition]" 
                                                class="w-full sm:w-56 px-2.5 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                            @foreach ($conditions as $c)
                                                <option value="{{ $c->value }}" {{ $c->value === 'Baik' ? 'selected' : '' }}>
                                                    {{ $c->value }} {{ $c->value === 'Baik' ? '(Siap Pakai)' : '(Perlu Perbaikan)' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Lokasi Penyimpanan & Data Pengembalian -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span>3. Lokasi Penyimpanan & Penanggung Jawab</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Lokasi Rak / Ruangan Pengembalian -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Lokasi Rak / Ruangan Penyimpanan <span class="text-rose-500">*</span>
                        </label>
                        <select name="return_location_id" required
                                class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('return_location_id') border-rose-500 @enderror">
                            <option value="">-- Pilih Lokasi Penyimpanan Kembali --</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('return_location_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('return_location_id')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Staf Penanggung Jawab / Penerima -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Staf Penyerah / Penerima Pengembalian <span class="text-rose-500">*</span>
                        </label>
                        <select name="staff_id" required
                                class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('staff_id') border-rose-500 @enderror">
                            <option value="">-- Pilih Staf Perpustakaan --</option>
                            @foreach ($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ old('staff_id', $selectedBorrow->staff_id) == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ $staff->position ?? 'Staf' }})
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Pengembalian -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Tanggal Pengembalian <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('return_date') border-rose-500 @enderror">
                        @error('return_date')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Pengembalian -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                            Catatan Pengembalian (Opsional)
                        </label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Barang dikembalikan tepat waktu dalam kondisi bersih..."
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
                        :disabled="submitting || getSelectedCount() === 0"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition shadow-sm shadow-indigo-600/30">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="submitting ? 'Sedang Menyimpan...' : 'Proses Pengembalian Barang'"></span>
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
