@extends('layouts.app', ['title' => 'Dashboard ' . ($user->isAdmin() ? 'Manajerial' : 'Pegawai')])

@section('page_title', 'Dashboard')
@section('page_subtitle', $user->isAdmin() ? 'Ringkasan operasional dan sirkulasi aset perpustakaan' : 'Ringkasan aktivitas peminjaman barang Anda')

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
                    <div class="h-10 w-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold">Ada {{ $pendingApprovalsCount }} Permintaan Peminjaman Menunggu Persetujuan</h3>
                        <p class="text-xs text-amber-100">Segera tinjau antrean dan alokasikan unit fisik untuk peminjam.</p>
                    </div>
                </div>
                <a href="{{ Route::has('transactions.pending') ? route('transactions.pending') : '#' }}" 
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white text-xs font-bold text-amber-800 hover:bg-amber-50 shadow-sm transition whitespace-nowrap">
                    Buka Antrean
                    <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        @endif

        <!-- 2. Kartu Metrik Utama -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-5">
            <!-- Total Unit -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Unit Aset</span>
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-slate-900 tracking-tight">{{ number_format($totalUnits) }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Unit terdata aktif</span>
                </div>
            </div>

            <!-- Unit Tersedia -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Unit Tersedia</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-emerald-600 tracking-tight">{{ number_format($availableUnits) }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Siap dipinjam</span>
                </div>
            </div>

            <!-- Sedang Dipinjam -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Sedang Dipinjam</span>
                    <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-blue-600 tracking-tight">{{ number_format($borrowedUnits) }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Berada di peminjam</span>
                </div>
            </div>

            <!-- Rusak / Perbaikan -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Rusak / Servis</span>
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-amber-600 tracking-tight">{{ number_format($brokenUnits) }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Butuh perbaikan</span>
                </div>
            </div>

            <!-- Overdue Alert Card -->
            <div class="col-span-2 lg:col-span-1 bg-white p-4 rounded-2xl border {{ $overdueCount > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80' }} shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium {{ $overdueCount > 0 ? 'text-rose-700 font-semibold' : 'text-slate-500' }}">Overdue Alert</span>
                    <span class="p-2 rounded-xl {{ $overdueCount > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold {{ $overdueCount > 0 ? 'text-rose-600' : 'text-slate-900' }} tracking-tight">
                        {{ number_format($overdueCount) }}
                    </span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Lewat tanggal kembali</span>
                </div>
            </div>
        </div>

        <!-- 3. Grid: Low Stock Alert & Riwayat Transaksi Terkini -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Peringatan Stok Menipis (1 Col) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Stok Tersedia Menipis (&le; 5)
                    </h3>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($lowStockCatalogs as $cat)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="min-w-0 pr-2">
                                <p class="text-xs font-semibold text-slate-800 truncate">{{ $cat->name }}</p>
                                <p class="text-[10px] text-slate-500">{{ $cat->brand ?? 'Tanpa merek' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold {{ $cat->available_count == 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $cat->available_count }} {{ $cat->unit_name }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Semua katalog memiliki stok aman.</p>
                    @endforelse
                </div>
            </div>

            <!-- Tabel Transaksi Terkini (2 Col) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                        Sirkulasi Transaksi Terkini
                    </h3>
                    <a href="{{ Route::has('transactions.index') ? route('transactions.index') : '#' }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                        Lihat Semua &rarr;
                    </a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100">
                                <th class="pb-2 font-medium">Kode Transaksi</th>
                                <th class="pb-2 font-medium">Peminjam / Staf</th>
                                <th class="pb-2 font-medium">Tipe</th>
                                <th class="pb-2 font-medium">Status</th>
                                <th class="pb-2 font-medium text-right">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentTransactions as $tx)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 font-mono font-semibold text-indigo-600">
                                        {{ $tx->transaction_code }}
                                    </td>
                                    <td class="py-2.5 text-slate-800">
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">Belum ada aktivitas transaksi.</td>
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
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium">Pinjaman Aktif Saya</span>
                    <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $myActiveBorrowsCount }}</p>
                </div>
            </div>

            <!-- Menunggu Persetujuan -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium">Menunggu Verifikasi Admin</span>
                    <p class="text-2xl font-bold text-amber-600 mt-0.5">{{ $myPendingRequestsCount }}</p>
                </div>
            </div>

            <!-- Jatuh Tempo Minggu Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl {{ $myDueThisWeekCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium">Wajib Kembali Minggu Ini</span>
                    <p class="text-2xl font-bold {{ $myDueThisWeekCount > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-0.5">{{ $myDueThisWeekCount }}</p>
                </div>
            </div>
        </div>

        <!-- 2. Aksi Cepat Pegawai -->
        <div class="bg-gradient-to-r from-indigo-900 to-slate-900 rounded-2xl p-6 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold">Butuh Barang untuk Kegiatan Perpustakaan?</h3>
                <p class="text-xs text-indigo-200 mt-1">Pilih barang langsung dari katalog inventaris dan tentukan kuantitas kebutuhan Anda.</p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ Route::has('inventory.index') ? route('inventory.index') : '#' }}" 
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold text-white border border-white/20 transition">
                    Jelajahi Katalog
                </a>
                <a href="{{ Route::has('transactions.borrow.create') ? route('transactions.borrow.create') : '#' }}" 
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-xs font-bold text-white shadow-sm transition">
                    + Ajukan Pinjam
                </a>
            </div>
        </div>

        <!-- 3. Tabel Riwayat Pinjaman Terkini Saya -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    5 Aktivitas Pinjaman Terakhir Saya
                </h3>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100">
                            <th class="pb-2 font-medium">Kode Transaksi</th>
                            <th class="pb-2 font-medium">Tanggal Pinjam</th>
                            <th class="pb-2 font-medium">Rencana Kembali</th>
                            <th class="pb-2 font-medium">Status Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($myRecentTransactions as $tx)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 font-mono font-semibold text-indigo-600">
                                    {{ $tx->transaction_code }}
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Anda belum memiliki riwayat peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection

