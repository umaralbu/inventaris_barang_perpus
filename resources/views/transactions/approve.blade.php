@extends('layouts.app')

@section('title', 'Verifikasi & Alokasi Peminjaman - ' . $transaction->transaction_code)

@section('header_content')
<div class="flex items-center justify-between">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Verifikasi & Alokasi Unit Fisik</h1>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                Menunggu Persetujuan
            </span>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Kode Permohonan: <strong class="text-indigo-600 font-mono">{{ $transaction->transaction_code }}</strong>
        </p>
    </div>
    <a href="{{ route('transactions.pending') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali ke Antrean</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{
        requiredQty: {{ (int) $transaction->quantity }},
        selectedUnits: {{ Js::from(old('unit_ids', [])) }},
        submitting: false,
        showRejectCard: false,
        toggleUnit(id) {
            const index = this.selectedUnits.indexOf(id);
            if (index > -1) {
                this.selectedUnits.splice(index, 1);
            } else {
                if (this.selectedUnits.length < this.requiredQty) {
                    this.selectedUnits.push(id);
                } else {
                    alert('Jumlah unit yang dipilih sudah memenuhi kuantitas permintaan (' + this.requiredQty + ' unit). Hapus pilihan lain jika ingin mengganti.');
                }
            }
        },
        isSelected(id) {
            return this.selectedUnits.includes(id);
        },
        autoSelectFirst() {
            const available = {{ Js::from($availableUnits->pluck('id')) }};
            this.selectedUnits = available.slice(0, this.requiredQty);
        }
     }">

    <!-- Ringkasan Permohonan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                <span>Ringkasan Pengajuan Pinjaman</span>
            </h2>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Jumlah Diminta</span>
                <span class="text-xl font-black text-indigo-600">
                    {{ $transaction->quantity }} <span class="text-xs font-normal text-slate-500">{{ $transaction->catalog->unit_name ?? 'Unit' }}</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-5 text-xs text-slate-600">
            <!-- Kolom 1: Barang -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Katalog Barang</span>
                <div class="font-bold text-slate-800 text-sm">{{ $transaction->catalog->name ?? '-' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    Kategori: <strong class="text-slate-700">{{ $transaction->catalog->category->name ?? 'Umum' }}</strong>
                </div>
                @if ($transaction->catalog->brand)
                    <div class="text-[11px] text-slate-500">
                        Merek: <strong class="text-slate-700">{{ $transaction->catalog->brand }}</strong>
                    </div>
                @endif
                <div class="mt-2 text-[11px] text-emerald-600 font-semibold">
                    Stok Fisik Siap Pakai: {{ $availableUnits->count() }} unit
                </div>
            </div>

            <!-- Kolom 2: Penanggung Jawab & Tujuan -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Staf & Lokasi Penggunaan</span>
                <div class="font-bold text-slate-800 text-sm">{{ $transaction->staff->name ?? '-' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    NIP: {{ $transaction->staff->nip ?? '-' }} ({{ $transaction->staff->position ?? 'Staf' }})
                </div>
                <div class="mt-2 text-[11px] text-slate-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <span>Lokasi Tujuan: <strong class="text-slate-800">{{ $transaction->destinationLocation->name ?? $transaction->to_location }}</strong></span>
                </div>
            </div>

            <!-- Kolom 3: Jadwal & Pemohon -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Jadwal & Akun Pemohon</span>
                <div class="text-slate-700">
                    Tgl Pinjam: <strong class="text-slate-900">{{ $transaction->transaction_date ? $transaction->transaction_date->format('d M Y') : '-' }}</strong>
                </div>
                <div class="text-slate-700 mt-0.5">
                    Batas Kembali: <strong class="text-indigo-700">{{ $transaction->expected_return_date ? $transaction->expected_return_date->format('d M Y') : '-' }}</strong>
                </div>
                <div class="mt-2 text-[11px] text-slate-400">
                    Diajukan oleh: <strong class="text-slate-600">{{ $transaction->user->name ?? 'Pengguna' }}</strong> ({{ $transaction->created_at->diffForHumans() }})
                </div>
            </div>
        </div>

        @if ($transaction->notes)
            <div class="mt-4 p-3 rounded-xl bg-amber-50/60 border border-amber-200/60 text-xs text-amber-900">
                <span class="font-bold text-[11px] uppercase tracking-wider block text-amber-800 mb-0.5">Keperluan / Catatan Pemohon:</span>
                <p>{{ $transaction->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Form Persetujuan & Alokasi Unit Fisik -->
    <form method="POST" action="{{ route('transactions.approve', $transaction) }}" @submit="submitting = true" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>Alokasikan Unit Fisik Barang</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Centang unit fisik dengan kondisi 'Baik' yang akan diserahkan kepada staf peminjam.
                    </p>
                </div>

                <!-- Status Counter Pilihan Unit -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="autoSelectFirst()" 
                            class="px-2.5 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                        Pilih Otomatis ({{ $transaction->quantity }} Unit)
                    </button>
                    <div class="px-3 py-1 rounded-xl text-xs font-bold transition"
                         :class="selectedUnits.length === requiredQty ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300'">
                        <span x-text="selectedUnits.length"></span> / <span x-text="requiredQty"></span> Unit Terpilih
                    </div>
                </div>
            </div>

            @error('unit_ids')
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
                    {{ $message }}
                </div>
            @enderror

            @if ($availableUnits->isEmpty())
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200">
                    <div class="w-10 h-10 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-800">Tidak ada unit fisik siap pakai yang tersedia saat ini!</p>
                    <p class="text-xs text-slate-500 mt-1">Seluruh unit pada katalog ini sedang dipinjam atau dalam perbaikan. Permohonan ini sebaiknya ditolak atau ditunda.</p>
                </div>
            @else
                <!-- Daftar Unit Fisik Yang Tersedia -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach ($availableUnits as $unit)
                        <div class="relative p-3.5 rounded-xl border transition cursor-pointer select-none"
                             :class="isSelected({{ $unit->id }}) ? 'bg-indigo-50/70 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50/50 border-slate-200 hover:bg-white hover:border-slate-300'"
                             @click="toggleUnit({{ $unit->id }})">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-mono font-bold text-xs text-slate-900 tracking-tight flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>{{ $unit->code }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        <span class="truncate">{{ $unit->location->name ?? 'Rak Penyimpanan' }}</span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5 text-[10px]">
                                        <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-semibold">Kondisi Baik</span>
                                    </div>
                                </div>

                                <!-- Checkbox Indicator -->
                                <div class="shrink-0 mt-0.5">
                                    <input type="checkbox" name="unit_ids[]" value="{{ $unit->id }}" 
                                           :checked="isSelected({{ $unit->id }})"
                                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 pointer-events-none">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Catatan Admin (Opsional) -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                    Catatan Verifikasi Admin (Opsional)
                </label>
                <input type="text" name="notes" value="{{ old('notes') }}" 
                       placeholder="Contoh: Unit fisik telah dicek kelengkapannya bersama charger & tas..."
                       class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <!-- Tombol Aksi Persetujuan -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
                <button type="button" @click="showRejectCard = !showRejectCard"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-800 py-2 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="showRejectCard ? 'Tutup Panel Penolakan' : 'Atau Tolak Permohonan Ini'"></span>
                </button>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a href="{{ route('transactions.pending') }}" 
                       class="w-full sm:w-auto text-center px-4 py-2.5 text-xs sm:text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" 
                            :disabled="submitting || selectedUnits.length !== requiredQty"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition shadow-sm shadow-emerald-600/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Setujui & Serahkan Unit</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Panel Penolakan Permohonan -->
    <div x-show="showRejectCard" style="display: none;" 
         class="bg-white rounded-2xl border border-rose-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center gap-3 border-b border-rose-100 pb-3">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Formulir Penolakan Permohonan Pinjam</h3>
                <p class="text-xs text-slate-500">Berikan alasan yang jelas kepada pemohon mengapa permohonan ini tidak dapat disetujui.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('transactions.reject', $transaction) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                    Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea name="rejection_reason" rows="3" required minlength="5"
                          placeholder="Tuliskan alasan penolakan secara spesifik..."
                          class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition @error('rejection_reason') border-rose-500 @enderror">{{ old('rejection_reason') }}</textarea>
                @error('rejection_reason')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menolak permohonan pinjam ini?');"
                        class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm">
                    Konfirmasi Tolak Permohonan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

