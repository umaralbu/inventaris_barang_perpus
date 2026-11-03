@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('header_content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Riwayat Sirkulasi & Transaksi</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            {{ Auth::user()->isAdmin() ? 'Pantau seluruh mutasi, peminjaman, dan pengembalian aset perpustakaan.' : 'Daftar permohonan dan riwayat peminjaman barang Anda.' }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if (Auth::user()->isAdmin())
            <a href="{{ route('transactions.pending') }}" 
               class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition shadow-sm">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Antrean Persetujuan</span>
            </a>
        @endif
        <a href="{{ route('transactions.borrow.create') }}" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm shadow-indigo-600/30">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Ajukan Pinjam</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5">

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 uppercase mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari kode TRX, peminjam, atau barang..." 
                           class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 uppercase mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tipe -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 uppercase mb-1">Tipe</label>
                <select name="type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Semua Tipe</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'status', 'type', 'start_date', 'end_date']))
                    <a href="{{ route('transactions.index') }}" class="px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Riwayat Transaksi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Kode & Tipe</th>
                        <th class="py-3.5 px-4">Barang / Katalog</th>
                        <th class="py-3.5 px-4 text-center">Jumlah</th>
                        <th class="py-3.5 px-4">Peminjam & Tujuan</th>
                        <th class="py-3.5 px-4">Jadwal Pinjam</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $trx)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Kode & Tipe -->
                            <td class="py-3 px-4">
                                <a href="{{ route('transactions.show', $trx) }}" class="font-bold text-indigo-600 hover:text-indigo-800 tracking-tight">
                                    {{ $trx->transaction_code ?? ('TRX-#' . $trx->id) }}
                                </a>
                                <div class="mt-0.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        {{ $trx->type->label() }}
                                    </span>
                                </div>
                            </td>

                            <!-- Barang / Katalog -->
                            <td class="py-3 px-4">
                                @if ($trx->catalog)
                                    <div class="font-semibold text-slate-800">{{ $trx->catalog->name }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $trx->catalog->category->name ?? 'Tanpa Kategori' }}
                                        @if ($trx->catalog->brand) • {{ $trx->catalog->brand }} @endif
                                    </div>
                                @elseif ($trx->item)
                                    <div class="font-semibold text-slate-800">{{ $trx->item->name }}</div>
                                    <div class="text-[11px] text-slate-400">Kode Unit: {{ $trx->item->code }}</div>
                                @else
                                    <span class="text-slate-400 italic">Barang tidak terdata</span>
                                @endif
                            </td>

                            <!-- Jumlah -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center justify-center font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800">
                                    {{ $trx->quantity }} {{ $trx->catalog->unit_name ?? 'Unit' }}
                                </span>
                            </td>

                            <!-- Peminjam & Tujuan -->
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800">
                                    {{ $trx->staff->name ?? ($trx->to_person ?? 'Tidak Tercatat') }}
                                </div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ $trx->destinationLocation->name ?? ($trx->to_location ?? 'Lokasi Perpustakaan') }}</span>
                                </div>
                            </td>

                            <!-- Jadwal Pinjam & Batas -->
                            <td class="py-3 px-4">
                                <div class="text-slate-700">
                                    Pinjam: <span class="font-medium">{{ $trx->transaction_date ? $trx->transaction_date->format('d M Y') : '-' }}</span>
                                </div>
                                @if ($trx->expected_return_date)
                                    <div class="text-[11px] mt-0.5 {{ $trx->isOverdue() ? 'text-rose-600 font-bold flex items-center gap-1' : 'text-slate-500' }}">
                                        @if ($trx->isOverdue())
                                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <span>Batas: {{ $trx->expected_return_date->format('d M Y') }} (Terlambat)</span>
                                        @else
                                            <span>Batas: {{ $trx->expected_return_date->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Status Transaksi -->
                            <td class="py-3 px-4 text-center">
                                @php
                                    $st = $trx->status->value ?? $trx->status;
                                    $badgeClasses = match($st) {
                                        'Menunggu Persetujuan'   => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'Dipinjam'               => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'Sebagian Dikembalikan' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'Dikembalikan', 'Selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'Ditolak'                => 'bg-rose-100 text-rose-800 border-rose-200',
                                        'Dibatalkan'             => 'bg-slate-100 text-slate-700 border-slate-200',
                                        default                  => 'bg-slate-100 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeClasses }}">
                                    {{ $st }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('transactions.show', $trx) }}" 
                                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition"
                                   title="Lihat Detail">
                                    Detail
                                </a>

                                @if (Auth::user()->isAdmin() && $trx->isPending())
                                    <a href="{{ route('transactions.approve.show', $trx) }}" 
                                       class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition shadow-sm"
                                       title="Verifikasi & Alokasikan Unit">
                                        Proses
                                    </a>
                                @endif

                                @if ($trx->isCancellableBy(Auth::user()))
                                    <form method="POST" action="{{ route('transactions.cancel', $trx) }}" class="inline-block"
                                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan pinjam ini?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                                            Batal
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Belum ada riwayat transaksi</p>
                                    <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Ajukan Pinjam" di atas untuk memulai transaksi peminjaman.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
