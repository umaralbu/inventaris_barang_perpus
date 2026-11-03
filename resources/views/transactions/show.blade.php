@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $transaction->transaction_code)

@section('header_content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $transaction->transaction_code }}</h1>
            @php
                $st = $transaction->status->value ?? $transaction->status;
                $badgeClasses = match($st) {
                    'Menunggu Persetujuan'   => 'bg-amber-100 text-amber-800 border-amber-300',
                    'Dipinjam'               => 'bg-blue-100 text-blue-800 border-blue-300',
                    'Sebagian Dikembalikan' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                    'Dikembalikan', 'Selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'Ditolak'                => 'bg-rose-100 text-rose-800 border-rose-300',
                    'Dibatalkan'             => 'bg-slate-100 text-slate-700 border-slate-300',
                    default                  => 'bg-slate-100 text-slate-800 border-slate-300',
                };
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                {{ $st }}
            </span>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Tipe Transaksi: <strong class="text-slate-800">{{ $transaction->type->label() }}</strong> • Dibuat pada {{ $transaction->created_at->format('d M Y, H:i') }} WIB
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('transactions.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Daftar Transaksi</span>
        </a>

        @if (Auth::user()->isAdmin() && $transaction->isPending())
            <a href="{{ route('transactions.approve.show', $transaction) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm shadow-indigo-600/30">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Verifikasi & Setujui</span>
            </a>
        @endif

        @if ($transaction->type === \App\Enums\TransactionType::BORROW && in_array($transaction->status, [\App\Enums\TransactionStatus::DIPINJAM, \App\Enums\TransactionStatus::SEBAGIAN_DIKEMBALIKAN]) && (Auth::user()->isAdmin() || $transaction->user_id === Auth::id()))
            <a href="{{ route('transactions.return.create', ['borrow_id' => $transaction->id]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
                <span>Catat Pengembalian</span>
            </a>
        @endif

        @if ($transaction->isCancellableBy(Auth::user()))
            <form method="POST" action="{{ route('transactions.cancel', $transaction) }}" class="inline-block"
                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan pinjam ini?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Batalkan Permohonan</span>
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Overdue Alert jika terlambat -->
    @if ($transaction->isOverdue())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-xs text-rose-900">
            <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-rose-800">Peringatan: Masa Peminjaman Telah Melewati Batas Waktu</h4>
                <p class="mt-0.5">
                    Estimasi pengembalian barang adalah tanggal <strong>{{ $transaction->expected_return_date->format('d M Y') }}</strong>. Mohon segera berkoordinasi dengan staf peminjam untuk proses pengembalian unit fisik.
                </p>
            </div>
        </div>
    @endif

    <!-- Alert Alasan Penolakan jika ditolak -->
    @if ($transaction->status === \App\Enums\TransactionStatus::DITOLAK && $transaction->rejection_reason)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-xs text-rose-900">
            <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-rose-800">Permohonan Ini Ditolak Oleh Admin</h4>
                <p class="mt-0.5 font-medium text-rose-700">
                    Alasan: "{{ $transaction->rejection_reason }}"
                </p>
                <p class="text-[11px] text-rose-500 mt-1">
                    Ditolak oleh: {{ $transaction->approver->name ?? 'Admin Perpustakaan' }}
                </p>
            </div>
        </div>
    @endif

    <!-- Kartu Informasi Detail -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
            <span>Rincian Informasi Peminjaman</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-4 text-xs">
            <!-- Barang / Katalog -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Katalog Barang</span>
                @if ($transaction->catalog)
                    <a href="{{ route('inventory.show', $transaction->catalog) }}" class="font-bold text-indigo-600 hover:text-indigo-800 text-sm">
                        {{ $transaction->catalog->name }}
                    </a>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        Kategori: {{ $transaction->catalog->category->name ?? 'Umum' }}
                        @if ($transaction->catalog->brand) • {{ $transaction->catalog->brand }} @endif
                    </div>
                @else
                    <span class="text-slate-500 font-medium">{{ $transaction->item->name ?? '-' }}</span>
                @endif
            </div>

            <!-- Kuantitas -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Jumlah Unit</span>
                <span class="text-base font-black text-slate-800">
                    {{ $transaction->quantity }} {{ $transaction->catalog->unit_name ?? 'Unit' }}
                </span>
            </div>

            <!-- Staf Peminjam -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Staf Penanggung Jawab</span>
                <div class="font-bold text-slate-800 text-sm">{{ $transaction->staff->name ?? ($transaction->to_person ?? '-') }}</div>
                <div class="text-[11px] text-slate-500">
                    NIP: {{ $transaction->staff->nip ?? '-' }} ({{ $transaction->staff->position ?? 'Staf' }})
                </div>
            </div>

            <!-- Lokasi Tujuan -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Lokasi Tujuan Penggunaan</span>
                <div class="font-bold text-slate-800 text-sm flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <span>{{ $transaction->destinationLocation->name ?? ($transaction->to_location ?? '-') }}</span>
                </div>
            </div>

            <!-- Tanggal Pinjam -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Tanggal Peminjaman</span>
                <span class="font-semibold text-slate-800">
                    {{ $transaction->transaction_date ? $transaction->transaction_date->format('d F Y') : '-' }}
                </span>
            </div>

            <!-- Batas Pengembalian -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Estimasi Pengembalian</span>
                <span class="font-bold {{ $transaction->isOverdue() ? 'text-rose-600' : 'text-slate-800' }}">
                    {{ $transaction->expected_return_date ? $transaction->expected_return_date->format('d F Y') : '-' }}
                </span>
            </div>

            <!-- Pemohon -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Akun Pemohon</span>
                <span class="font-semibold text-slate-700">{{ $transaction->user->name ?? 'Pengguna' }}</span>
                <span class="text-[11px] text-slate-400 block">({{ $transaction->user->role->label() ?? 'Pegawai' }})</span>
            </div>

            <!-- Penyetujui / Admin -->
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Admin Penyetujui</span>
                @if ($transaction->approver)
                    <span class="font-semibold text-emerald-700">{{ $transaction->approver->name }}</span>
                @else
                    <span class="text-slate-400 italic">Belum disetujui</span>
                @endif
            </div>
        </div>

        @if ($transaction->notes)
            <div class="mt-5 p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan / Keperluan:</span>
                <p class="text-slate-700">{{ $transaction->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Tabel Rincian Unit Fisik Yang Dialokasikan (transaction_details) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <span>Alokasi Unit Fisik Barang (Serial / Kode Unit)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Daftar unit fisik individual yang diserahkan dan dipantau pengembaliannya.
                </p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                Total: {{ $transaction->details->count() }} Unit
            </span>
        </div>

        @if ($transaction->isPending())
            <div class="p-8 text-center bg-amber-50/50 rounded-xl border border-amber-200/70">
                <div class="w-10 h-10 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-amber-900">Unit Fisik Belum Dialokasikan</h4>
                <p class="text-xs text-amber-700 mt-1 max-w-md mx-auto">
                    Permohonan ini masih berstatus <strong class="font-semibold">"Menunggu Persetujuan"</strong>. Admin perpustakaan akan memilih dan menyerahkan kode unit fisik saat proses verifikasi disetujui.
                </p>
            </div>
        @elseif ($transaction->details->isEmpty())
            <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-100">
                Tidak ada data unit fisik yang terikat pada transaksi ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">No.</th>
                            <th class="py-3 px-4">Kode Unit Fisik</th>
                            <th class="py-3 px-4">Kondisi Awal</th>
                            <th class="py-3 px-4">Lokasi Asal</th>
                            <th class="py-3 px-4">Lokasi Sekarang</th>
                            <th class="py-3 px-4 text-center">Status Pengembalian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaction->details as $index => $detail)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 text-slate-400 font-mono">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    {{ $detail->item->code ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        {{ $detail->condition_before->value ?? 'Baik' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $detail->fromLocation->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-800 font-medium">
                                    {{ $detail->item->location->name ?? ($transaction->destinationLocation->name ?? '-') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($detail->is_returned)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Dikembalikan ({{ $detail->returned_at ? $detail->returned_at->format('d/m/Y') : '' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">
                                            Masih Dipinjam
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

