@extends('layouts.app', ['title' => 'Dashboard ' . ($user->isAdmin() ? 'Manajerial' : 'Pegawai')])

@section('page_title', 'Dashboard ' . ($user->isAdmin() ? 'Manajerial' : 'Personal'))
@section('page_subtitle', $user->isAdmin() ? 'Ringkasan operasional aset perpustakaan dan antrean sirkulasi' : 'Ringkasan peminjaman barang dan status permohonan Anda')

@section('content')
<div class="space-y-6">

    @if ($user->isAdmin())
        <!-- ============================================================== -->
        <!-- ==================== TAMPILAN ADMINISTRATOR ================== -->
        <!-- ============================================================== -->

        <!-- 1. Banner Prioritas: Antrean Menunggu Persetujuan -->
        @if ($pendingApprovalsCount > 0)
            <div class="rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 p-4 sm:p-5 text-white shadow-lg shadow-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="h-11 w-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold">Ada {{ $pendingApprovalsCount }} Permohonan Peminjaman Menunggu Persetujuan</h3>
                        <p class="text-xs text-amber-100">Segera verifikasi antrean dan alokasikan unit fisik untuk peminjam.</p>
                    </div>
                </div>
                <a href="{{ route('transactions.pending') }}" 
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-white text-xs sm:text-sm font-bold text-amber-800 hover:bg-amber-50 shadow-sm transition whitespace-nowrap">
                    Buka Antrean Persetujuan &rarr;
                </a>
            </div>
        @endif

        <!-- 2. Kartu Metrik Utama -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Total Unit -->
            <a href="{{ route('inventory.index') }}" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-indigo-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Unit Aset</span>
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalUnits) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Unit terdata aktif</span>
                </div>
            </a>

            <!-- Unit Tersedia -->
            <a href="{{ route('inventory.index', ['status' => 'Tersedia']) }}" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Tersedia</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-emerald-600 tracking-tight">{{ number_format($availableUnits) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Siap dipinjam</span>
                </div>
            </a>

            <!-- Sedang Dipinjam -->
            <a href="{{ route('transactions.index', ['status' => 'Dipinjam']) }}" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-blue-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Dipinjam</span>
                    <span class="p-2 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-blue-600 tracking-tight">{{ number_format($borrowedUnits) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Berada di peminjam</span>
                </div>
            </a>

            <!-- Rusak / Perbaikan -->
            <a href="{{ route('transactions.maintenance.index') }}" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-amber-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rusak / Servis</span>
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-amber-600 tracking-tight">{{ number_format($brokenUnits) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Butuh perbaikan</span>
                </div>
            </a>

            <!-- Overdue Alert Card -->
            <a href="{{ route('transactions.index', ['status' => 'Dipinjam']) }}" class="col-span-2 lg:col-span-1 bg-white p-4 sm:p-5 rounded-2xl border {{ $overdueCount > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80' }} shadow-xs flex flex-col justify-between hover:border-rose-400 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold {{ $overdueCount > 0 ? 'text-rose-700' : 'text-slate-500' }} uppercase tracking-wider">Overdue Alert</span>
                    <span class="p-2 rounded-xl {{ $overdueCount > 0 ? 'bg-rose-100 text-rose-700 group-hover:bg-rose-600 group-hover:text-white' : 'bg-slate-100 text-slate-500' }} transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black {{ $overdueCount > 0 ? 'text-rose-600' : 'text-slate-900' }} tracking-tight">
                        {{ number_format($overdueCount) }}
                    </span>
                    <span class="text-xs text-slate-400 block mt-0.5">Lewat tanggal kembali</span>
                </div>
            </a>
        </div>

        <!-- 3. Panel Aksi Cepat Admin -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Aksi Cepat Manajemen Inventaris:</span>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                <a href="{{ route('inventory.create') }}" 
                   class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200/60 transition text-xs font-semibold text-slate-700">
                    <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Katalog</span>
                </a>

                <a href="{{ route('transactions.pending') }}" 
                   class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 hover:text-amber-700 border border-slate-200/60 transition text-xs font-semibold text-slate-700">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Tinjau Antrean</span>
                </a>

                <a href="{{ route('transactions.borrow.create') }}" 
                   class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200/60 transition text-xs font-semibold text-slate-700">
                    <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Catat Pinjam</span>
                </a>

                <a href="{{ route('transactions.return.create') }}" 
                   class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200/60 transition text-xs font-semibold text-slate-700">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                    <span>Catat Kembali</span>
                </a>

                <a href="{{ route('transactions.transfer.create') }}" 
                   class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 hover:bg-sky-50 hover:text-sky-700 border border-slate-200/60 transition text-xs font-semibold text-slate-700">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Mutasi Lokasi</span>
                </a>
            </div>
        </div>

        <!-- 4. Grid: Low Stock Alert & Riwayat Transaksi Terkini -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Peringatan Stok Menipis (1 Col) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Stok Tersedia Menipis (&le; 5)
                    </h3>
                    <a href="{{ route('inventory.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                        Lihat Semua &rarr;
                    </a>
                </div>
                <div class="mt-4 space-y-2.5">
                    @forelse ($lowStockCatalogs as $cat)
                        <a href="{{ route('inventory.show', $cat) }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-indigo-50/50 border border-slate-100 hover:border-indigo-200 transition group">
                            <div class="min-w-0 pr-2">
                                <p class="text-xs font-bold text-slate-800 group-hover:text-indigo-700 truncate">{{ $cat->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $cat->category->name ?? 'Umum' }} @if($cat->brand) • {{ $cat->brand }} @endif</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold {{ $cat->available_count == 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $cat->available_count }} {{ $cat->unit_name ?? 'Unit' }}
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">Seluruh jenis barang memiliki stok aman.</p>
                    @endforelse
                </div>
            </div>

            <!-- Tabel Transaksi Terkini (2 Col) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                        Sirkulasi Transaksi Terkini
                    </h3>
                    <a href="{{ route('transactions.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                        Semua Transaksi &rarr;
                    </a>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-slate-100">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider">
                                <th class="pb-2.5">Kode Transaksi</th>
                                <th class="pb-2.5">Peminjam / Staf</th>
                                <th class="pb-2.5">Tipe</th>
                                <th class="pb-2.5">Status</th>
                                <th class="pb-2.5 text-right">Tanggal</th>
                                <th class="pb-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentTransactions as $tx)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 font-mono font-bold text-indigo-600">
                                        <a href="{{ route('transactions.show', $tx) }}" class="hover:underline">
                                            {{ $tx->transaction_code }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 text-slate-800 font-medium">
                                        {{ $tx->staff ? $tx->staff->name : ($tx->to_person ?? 'N/A') }}
                                    </td>
                                    <td class="py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $tx->type->badgeClass() }}">
                                            {{ $tx->type->label() }}
                                        </span>
                                    </td>
                                    <td class="py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $tx->status->badgeClass() }}">
                                            {{ $tx->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right text-slate-500">
                                        {{ $tx->transaction_date ? $tx->transaction_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <a href="{{ route('transactions.show', $tx) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">Belum ada aktivitas sirkulasi transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        <!-- ============================================================== -->
        <!-- ====================== TAMPILAN PEGAWAI ====================== -->
        <!-- ============================================================== -->

        <!-- 1. Kartu Metrik Personal -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Pinjaman Aktif Saya -->
            <a href="{{ route('transactions.index', ['status' => 'Dipinjam']) }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-indigo-300 hover:shadow-sm transition">
                <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Pinjaman Aktif Saya</span>
                    <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $myActiveBorrowsCount }}</p>
                </div>
            </a>

            <!-- Menunggu Persetujuan -->
            <a href="{{ route('transactions.index', ['status' => 'Menunggu Persetujuan']) }}" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-amber-300 hover:shadow-sm transition">
                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Menunggu Persetujuan</span>
                    <p class="text-2xl font-black text-amber-600 mt-0.5">{{ $myPendingRequestsCount }}</p>
                </div>
            </a>

            <!-- Jatuh Tempo Minggu Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl {{ $myDueThisWeekCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Jatuh Tempo Minggu Ini</span>
                    <p class="text-2xl font-black {{ $myDueThisWeekCount > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-0.5">{{ $myDueThisWeekCount }}</p>
                </div>
            </div>
        </div>

        <!-- 2. Aksi Cepat Pegawai -->
        <div class="bg-gradient-to-r from-indigo-900 to-slate-900 rounded-2xl p-6 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold">Butuh Barang untuk Kegiatan Perpustakaan?</h3>
                <p class="text-xs text-indigo-200 mt-1">Pilih barang langsung dari katalog inventaris dan tentukan kuantitas kebutuhan Anda.</p>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <a href="{{ route('inventory.index') }}" 
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/20 transition">
                    Jelajahi Katalog
                </a>
                <a href="{{ route('transactions.borrow.create') }}" 
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-xs font-bold text-white shadow-sm transition">
                    + Ajukan Pinjam
                </a>
                <a href="{{ route('transactions.return.create') }}" 
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white shadow-sm transition">
                    Pengembalian
                </a>
            </div>
        </div>

        <!-- 3. Tabel Riwayat Pinjaman Terkini Saya -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    5 Aktivitas Sirkulasi Terakhir Saya
                </h3>
                <a href="{{ route('transactions.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                    Semua Riwayat Saya &rarr;
                </a>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-100">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="pb-2.5">Kode Transaksi</th>
                            <th class="pb-2.5">Barang / Katalog</th>
                            <th class="pb-2.5">Tanggal Transaksi</th>
                            <th class="pb-2.5">Batas Pengembalian</th>
                            <th class="pb-2.5">Status</th>
                            <th class="pb-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($myRecentTransactions as $tx)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 font-mono font-bold text-indigo-600">
                                    <a href="{{ route('transactions.show', $tx) }}" class="hover:underline">
                                        {{ $tx->transaction_code }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-slate-800 font-semibold">
                                    {{ $tx->catalog->name ?? ($tx->item->name ?? '-') }}
                                </td>
                                <td class="py-2.5 text-slate-700">
                                    {{ $tx->transaction_date ? $tx->transaction_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2.5 text-slate-700">
                                    {{ $tx->expected_return_date ? $tx->expected_return_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $tx->status->badgeClass() }}">
                                        {{ $tx->status->label() }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('transactions.show', $tx) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Anda belum memiliki riwayat transaksi peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
