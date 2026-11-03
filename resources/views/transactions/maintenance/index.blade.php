@extends('layouts.app')

@section('title', 'Pemeliharaan & Perbaikan Barang')

@section('header_content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Siklus Pemeliharaan & Servis Barang</h1>
        <p class="text-sm text-slate-500 mt-1">
            Kelola alur servis aset rusak, pantau pengerjaan teknisi/vendor, dan kembalikan unit yang telah diperbaiki.
        </p>
    </div>
    <a href="{{ route('transactions.index') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Semua Transaksi</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6"
     x-data="{
        activeTab: '{{ $tab }}',
        startModalOpen: false,
        completeModalOpen: false,
        selectedUnitId: null,
        selectedUnitCode: '',
        selectedUnitName: '',
        selectedTrxId: null,
        selectedTrxCode: '',
        openStartModal(id, code, name) {
            this.selectedUnitId = id;
            this.selectedUnitCode = code;
            this.selectedUnitName = name;
            this.startModalOpen = true;
        },
        openCompleteModal(trxId, trxCode, unitCode) {
            this.selectedTrxId = trxId;
            this.selectedTrxCode = trxCode;
            this.selectedUnitCode = unitCode;
            this.completeModalOpen = true;
        }
     }">

    <!-- Tab Navigation -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6">
            <a href="{{ route('transactions.maintenance.index', ['tab' => 'needed']) }}" 
               class="pb-3.5 px-1 text-sm font-bold border-b-2 flex items-center gap-2 transition {{ $tab === 'needed' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>Butuh Perbaikan</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $tab === 'needed' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $neededUnits->total() }}
                </span>
            </a>

            <a href="{{ route('transactions.maintenance.index', ['tab' => 'in_progress']) }}" 
               class="pb-3.5 px-1 text-sm font-bold border-b-2 flex items-center gap-2 transition {{ $tab === 'in_progress' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>Sedang Dalam Perbaikan</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $tab === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $inProgressUnits->total() }}
                </span>
            </a>

            <a href="{{ route('transactions.maintenance.index', ['tab' => 'history']) }}" 
               class="pb-3.5 px-1 text-sm font-bold border-b-2 flex items-center gap-2 transition {{ $tab === 'history' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>Riwayat Servis Selesai</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $tab === 'history' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $maintenanceHistory->total() }}
                </span>
            </a>
        </nav>
    </div>

    <!-- ==================== TAB 1: BUTUH PERBAIKAN ==================== -->
    @if ($tab === 'needed')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Daftar Unit Fisik Rusak / Perlu Perbaikan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Unit ini tidak dapat dipinjamkan hingga dilakukan perbaikan oleh teknisi.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Kode Unit</th>
                            <th class="py-3.5 px-4">Barang / Katalog</th>
                            <th class="py-3.5 px-4">Lokasi Saat Ini</th>
                            <th class="py-3.5 px-4">Kondisi Fisik</th>
                            <th class="py-3.5 px-4 text-right">Tindakan Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($neededUnits as $unit)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 text-sm">
                                    {{ $unit->code }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $unit->catalog->name ?? $unit->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $unit->catalog->category->name ?? 'Umum' }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    {{ $unit->location->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-100 text-rose-800">
                                        {{ $unit->condition->value }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" 
                                            @click="openStartModal({{ $unit->id }}, '{{ $unit->code }}', '{{ addslashes($unit->catalog->name ?? $unit->name) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Kirim Servis</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p class="font-bold text-slate-700 text-sm">Semua Unit Fisik Dalam Kondisi Baik</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Tidak ada barang yang tercatat rusak atau perlu perbaikan saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($neededUnits->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $neededUnits->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ==================== TAB 2: SEDANG DALAM PERBAIKAN ==================== -->
    @if ($tab === 'in_progress')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Unit Fisik Yang Sedang Dalam Perbaikan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Barang sedang ditangani oleh teknisi/vendor dan menunggu hasil perbaikan.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Kode Unit</th>
                            <th class="py-3.5 px-4">Barang & Kategori</th>
                            <th class="py-3.5 px-4">Teknisi / Vendor</th>
                            <th class="py-3.5 px-4">Keterangan Servis</th>
                            <th class="py-3.5 px-4 text-right">Tindakan Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($inProgressUnits as $unit)
                            @php
                                $activeMnt = $activeMaintenanceTransactions->get($unit->id);
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 text-sm">
                                    {{ $unit->code }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $unit->catalog->name ?? $unit->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $unit->catalog->category->name ?? 'Umum' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <strong class="text-slate-800">{{ $activeMnt->to_person ?? 'Teknisi Luar' }}</strong>
                                    <div class="text-[11px] text-slate-400">
                                        Mulai: {{ $activeMnt && $activeMnt->transaction_date ? $activeMnt->transaction_date->format('d M Y') : '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-700 max-w-xs truncate">
                                    {{ $activeMnt->notes ?? 'Sedang dalam perbaikan berkala' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if ($activeMnt)
                                        <button type="button" 
                                                @click="openCompleteModal({{ $activeMnt->id }}, '{{ $activeMnt->transaction_code }}', '{{ $unit->code }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Selesaikan Servis</span>
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Data transaksi tidak ditemukan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    Tidak ada unit fisik yang sedang dalam masa perbaikan saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($inProgressUnits->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $inProgressUnits->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ==================== TAB 3: RIWAYAT SERVIS ==================== -->
    @if ($tab === 'history')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Riwayat Seluruh Pemeliharaan Barang</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Jejak audit permanen proses perbaikan barang inventaris perpustakaan.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">Kode Transaksi</th>
                            <th class="py-3.5 px-4">Unit Barang</th>
                            <th class="py-3.5 px-4">Teknisi / Vendor</th>
                            <th class="py-3.5 px-4">Tanggal Mulai</th>
                            <th class="py-3.5 px-4">Status & Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($maintenanceHistory as $m)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                    <a href="{{ route('transactions.show', $m) }}" class="hover:underline">
                                        {{ $m->transaction_code }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $m->item->code ?? '-' }} — {{ $m->catalog->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-800 font-medium">
                                    {{ $m->to_person ?? 'Teknisi' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $m->transaction_date ? $m->transaction_date->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $m->status === \App\Enums\TransactionStatus::SELESAI ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $m->status->value }}
                                    </span>
                                    <div class="text-[11px] text-slate-500 mt-0.5 max-w-sm truncate">
                                        {{ $m->notes }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Belum ada catatan riwayat pemeliharaan barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($maintenanceHistory->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $maintenanceHistory->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Modal 1: Mulai Servis / Kirim Perbaikan -->
    <div x-show="startModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="startModalOpen = false" aria-hidden="true"></div>

            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 text-left shadow-xl transform transition-all border border-slate-200">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Pendaftaran Servis / Mulai Perbaikan</h3>
                        <p class="text-xs text-slate-500" x-text="selectedUnitCode + ' — ' + selectedUnitName"></p>
                    </div>
                </div>

                <form action="{{ route('transactions.maintenance.start') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="item_id" :value="selectedUnitId">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Teknisi / Vendor Servis <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="vendor_technician" required placeholder="Contoh: CV Prima Tech Servis / Mas Budi (Teknisi)"
                               class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Tanggal Mulai Servis <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Deskripsi Kerusakan / Kendala <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="issue_description" rows="3" required placeholder="Jelaskan kendala teknis (contoh: Layar monitor berkedip garis-garis hitam, kabel power putus)..."
                                  class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Estimasi Biaya Servis (Opsional)
                        </label>
                        <input type="number" name="estimated_cost" min="0" placeholder="Rp"
                               class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="startModalOpen = false" 
                                class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">
                            Kirim ke Servis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 2: Selesaikan Servis & Pemeliharaan -->
    <div x-show="completeModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true"
         x-data="{ resultStatus: 'success' }">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="completeModalOpen = false" aria-hidden="true"></div>

            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 text-left shadow-xl transform transition-all border border-slate-200">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Penyelesaian Servis Pemeliharaan</h3>
                        <p class="text-xs text-slate-500" x-text="selectedTrxCode + ' (' + selectedUnitCode + ')'"></p>
                    </div>
                </div>

                <form :action="'/transactions/maintenance/' + selectedTrxId + '/complete'" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Hasil Perbaikan <span class="text-rose-500">*</span>
                        </label>
                        <select name="result_status" x-model="resultStatus" required
                                class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="success">Berhasil Diperbaiki (Kondisi Baik & Siap Digunakan Kembali)</option>
                            <option value="unrepairable">Rusak Permanen / Tidak Dapat Diperbaiki (Dihapusbukukan / Nonaktif)</option>
                        </select>
                    </div>

                    <div x-show="resultStatus === 'success'">
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Lokasi Penyimpanan Barang Kembali <span class="text-rose-500">*</span>
                        </label>
                        <select name="to_location_id" :required="resultStatus === 'success'"
                                class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <option value="">-- Pilih Lokasi Rak / Ruangan --</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Tanggal Selesai Servis <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="completion_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Biaya Riil Servis (Opsional)
                        </label>
                        <input type="number" name="actual_cost" min="0" placeholder="Rp"
                               class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            Catatan Penanganan / Solusi <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="resolution_notes" rows="3" required placeholder="Contoh: Penggantian adaptor kabel power dan pengetesan ulang selama 2 jam, unit normal kembali..."
                                  class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="completeModalOpen = false" 
                                class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                            Simpan Hasil Servis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

