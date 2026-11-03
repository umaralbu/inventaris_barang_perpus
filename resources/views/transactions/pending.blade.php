@extends('layouts.app')

@section('title', 'Antrean Persetujuan Peminjaman')

@section('header_content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Antrean Persetujuan Peminjaman</h1>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                {{ $pendingTransactions->total() }} Menunggu
            </span>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Daftar permohonan peminjaman aset perpustakaan yang menunggu verifikasi dan alokasi unit fisik dari Admin.
        </p>
    </div>
    <a href="{{ route('transactions.index') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Semua Riwayat Transaksi</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-5" x-data="{ rejectModalOpen: false, selectedTrxId: null, selectedTrxCode: '' }">

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('transactions.pending') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari kode permohonan, nama peminjam, atau nama katalog..." 
                       class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition">
                    Cari
                </button>
                @if (request()->filled('search'))
                    <a href="{{ route('transactions.pending') }}" class="w-full sm:w-auto text-center px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Daftar Antrean Permohonan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Desktop Table (>= 640px) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Kode Permohonan</th>
                        <th class="py-3.5 px-4">Barang & Kategori</th>
                        <th class="py-3.5 px-4 text-center">Permintaan</th>
                        <th class="py-3.5 px-4">Peminjam & Lokasi Tujuan</th>
                        <th class="py-3.5 px-4">Waktu Permohonan</th>
                        <th class="py-3.5 px-4 text-right">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pendingTransactions as $trx)
                        <tr class="hover:bg-amber-50/40 transition">
                            <!-- Kode -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('transactions.show', $trx) }}" class="font-bold text-indigo-600 hover:text-indigo-800 tracking-tight text-sm">
                                    {{ $trx->transaction_code }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Diajukan oleh: <span class="font-medium text-slate-600">{{ $trx->user->name ?? 'Pengguna' }}</span>
                                </div>
                            </td>

                            <!-- Barang & Kategori -->
                            <td class="py-3.5 px-4">
                                @if ($trx->catalog)
                                    <div class="font-bold text-slate-800 text-sm">{{ $trx->catalog->name }}</div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                        <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium">
                                            {{ $trx->catalog->category->name ?? 'Umum' }}
                                        </span>
                                        @if ($trx->catalog->brand)
                                            <span>Merek: {{ $trx->catalog->brand }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Katalog tidak ditemukan</span>
                                @endif
                            </td>

                            <!-- Kuantitas -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $trx->quantity }} {{ $trx->catalog->unit_name ?? 'Unit' }}
                                </span>
                            </td>

                            <!-- Peminjam & Lokasi -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $trx->staff->name ?? 'Staf Perpustakaan' }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <span>Tujuan: {{ $trx->destinationLocation->name ?? $trx->to_location }}</span>
                                </div>
                            </td>

                            <!-- Waktu & Batas -->
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700">
                                    Tgl Pinjam: <span class="font-medium">{{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Batas Kembali: <span class="font-medium text-slate-700">{{ $trx->expected_return_date ? $trx->expected_return_date->format('d M Y') : '-' }}</span>
                                </div>
                            </td>

                            <!-- Tindakan Admin -->
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('transactions.approve.show', $trx) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm shadow-indigo-600/30">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Verifikasi & Alokasikan</span>
                                </a>

                                <button type="button" 
                                        @click="rejectModalOpen = true; selectedTrxId = {{ $trx->id }}; selectedTrxCode = '{{ $trx->transaction_code }}'"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition">
                                    Tolak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Semua Permohonan Telah Diproses</p>
                                    <p class="text-xs text-slate-400 mt-1">Tidak ada permohonan peminjaman yang menunggu persetujuan saat ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Cards (< 640px) -->
        <div class="sm:hidden divide-y divide-slate-100">
            @forelse ($pendingTransactions as $trx)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <a href="{{ route('transactions.show', $trx) }}" class="font-bold text-xs text-indigo-600 hover:text-indigo-800">
                            {{ $trx->transaction_code }}
                        </a>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                            Menunggu Persetujuan
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                {{ $trx->catalog->name ?? 'Barang Tanpa Nama' }}
                            </h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $trx->catalog->category->name ?? 'Umum' }}
                                @if ($trx->catalog && $trx->catalog->brand) • {{ $trx->catalog->brand }} @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center justify-center font-bold px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs shrink-0">
                            {{ $trx->quantity }} {{ $trx->catalog->unit_name ?? 'Unit' }}
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded-xl space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Peminjam:</span>
                            <span class="font-medium text-slate-800">{{ $trx->staff->name ?? 'Staf Perpustakaan' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Tujuan:</span>
                            <span class="font-medium text-slate-700">{{ $trx->destinationLocation->name ?? $trx->to_location }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                            <span class="text-slate-400">Batas Kembali:</span>
                            <span class="font-medium text-slate-800">{{ $trx->expected_return_date ? $trx->expected_return_date->format('d M Y') : '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100">
                        <button type="button" 
                                @click="rejectModalOpen = true; selectedTrxId = {{ $trx->id }}; selectedTrxCode = '{{ $trx->transaction_code }}'"
                                class="px-3 py-1.5 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                            Tolak
                        </button>
                        <a href="{{ route('transactions.approve.show', $trx) }}" 
                           class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-sm">
                            Verifikasi & Alokasikan
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">
                    Semua permohonan telah diproses.
                </div>
            @endforelse
        </div>

        @if ($pendingTransactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pendingTransactions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Cepat Penolakan Permohonan -->
    <div x-show="rejectModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="rejectModalOpen = false" aria-hidden="true"></div>

            <div class="relative bg-white rounded-2xl max-w-md w-full p-6 text-left shadow-xl transform transition-all border border-slate-200">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tolak Permohonan Pinjam</h3>
                        <p class="text-xs text-slate-500" x-text="'Kode: ' + selectedTrxCode"></p>
                    </div>
                </div>

                <form :action="'/transactions/' + selectedTrxId + '/reject'" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="rejection_reason" rows="3" required minlength="5"
                                  placeholder="Jelaskan alasan penolakan secara jelas (contoh: Barang sedang dipersiapkan untuk kegiatan ujian nasional / stok fisik sedang dalam jadwal kalibrasi)..."
                                  class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="rejectModalOpen = false" 
                                class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm">
                            Konfirmasi Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

