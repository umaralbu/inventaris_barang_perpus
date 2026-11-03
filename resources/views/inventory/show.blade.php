@extends('layouts.app', ['title' => 'Detail Katalog: ' . $catalog->name])

@section('page_title', $catalog->name)
@section('page_subtitle', 'Tingkat 2: Detail data induk dan pelacakan unit fisik berkode serial')

@section('content')
<div class="space-y-6" x-data="{ openAddUnitModal: false, editingUnit: null }">

    <!-- ==================== 1. KARTU INFORMASI KATALOG INDUK ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
        <div class="flex flex-col md:flex-row items-start gap-6">
            <!-- Foto Barang -->
            <div class="w-full sm:w-48 h-48 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                @if ($catalog->photo && Storage::disk('public')->exists($catalog->photo))
                    <img src="{{ asset('storage/' . $catalog->photo) }}" 
                         alt="{{ $catalog->name }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="flex flex-col items-center justify-center text-slate-400 p-4 text-center">
                        <svg class="w-12 h-12 stroke-[1.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-[11px] mt-1">Belum ada foto</span>
                    </div>
                @endif
            </div>

            <!-- Detail Data Induk -->
            <div class="flex-1 min-w-0 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $catalog->category ? $catalog->category->name : 'Tanpa Kategori' }}
                            </span>
                            @if ($catalog->brand)
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700">
                                    Merek: {{ $catalog->brand }}
                                </span>
                            @endif
                            @if (!$catalog->is_active)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                    Katalog Diarsipkan
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mt-1.5">{{ $catalog->name }}</h3>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('inventory.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                            &larr; Kembali ke Katalog
                        </a>
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('inventory.edit', $catalog) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                Edit Katalog
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Spesifikasi & Deskripsi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 text-xs">
                    <div>
                        <span class="font-semibold text-slate-700 block mb-0.5">Spesifikasi Teknis:</span>
                        <p class="text-slate-600 leading-relaxed">{{ $catalog->specification ?: 'Tidak ada rincian spesifikasi khusus.' }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700 block mb-0.5">Catatan / Deskripsi:</span>
                        <p class="text-slate-600 leading-relaxed">{{ $catalog->description ?: 'Tidak ada keterangan tambahan.' }}</p>
                    </div>
                </div>

                <!-- Quick Inventory Stats Bar -->
                @php
                    $totalCount = $catalog->units()->count();
                    $availCount = $catalog->units()->where('status', \App\Enums\ItemUnitStatus::TERSEDIA->value)->where('condition', \App\Enums\ItemCondition::BAIK->value)->where('is_active', true)->count();
                    $borrowedCount = $catalog->units()->where('status', \App\Enums\ItemUnitStatus::DIPINJAM->value)->count();
                    $brokenCount = $catalog->units()->whereIn('condition', [\App\Enums\ItemCondition::RUSAK->value, \App\Enums\ItemCondition::PERLU_PERBAIKAN->value])->count();
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-100">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-500 font-medium block">Total Unit Fisik</span>
                        <span class="text-base font-bold text-slate-900">{{ $totalCount }} {{ $catalog->unit_name }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
                        <span class="text-[10px] text-emerald-700 font-medium block">Unit Siap Dipinjam</span>
                        <span class="text-base font-bold text-emerald-800">{{ $availCount }} {{ $catalog->unit_name }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-100">
                        <span class="text-[10px] text-blue-700 font-medium block">Sedang Dipinjam</span>
                        <span class="text-base font-bold text-blue-800">{{ $borrowedCount }} {{ $catalog->unit_name }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-100">
                        <span class="text-[10px] text-amber-700 font-medium block">Rusak / Servis</span>
                        <span class="text-base font-bold text-amber-800">{{ $brokenCount }} {{ $catalog->unit_name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 2. DAFTAR SELURUH UNIT FISIK INDIVIDUAL ==================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Toolbar Header Unit Fisik -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>Daftar Unit Fisik Berkode Serial</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        {{ $units->total() }} unit
                    </span>
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">Pelacakan nomor inventaris fisik, kondisi, dan ruangan rak penyimpanan.</p>
            </div>

            <div class="flex items-center gap-2">
                @if (Auth::user()->isAdmin())
                    <button type="button" 
                            @click="openAddUnitModal = true" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Tambah Unit Fisik</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Filter Bar Unit Fisik -->
        <div class="bg-slate-50/60 p-3.5 border-b border-slate-100">
            <form method="GET" action="{{ route('inventory.show', $catalog) }}" class="flex flex-wrap items-center gap-2.5 text-xs">
                <input type="text" 
                       name="unit_code" 
                       value="{{ request('unit_code') }}" 
                       placeholder="Cari kode serial..." 
                       class="rounded-xl border border-slate-300 py-1.5 px-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:outline-none bg-white">

                <select name="unit_location" class="rounded-xl border border-slate-300 py-1.5 px-3 text-xs text-slate-700 bg-white">
                    <option value="">Semua Lokasi</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('unit_location') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>

                <select name="unit_condition" class="rounded-xl border border-slate-300 py-1.5 px-3 text-xs text-slate-700 bg-white">
                    <option value="">Semua Kondisi</option>
                    @foreach ($conditions as $cond)
                        <option value="{{ $cond->value }}" {{ request('unit_condition') == $cond->value ? 'selected' : '' }}>
                            {{ $cond->label() }}
                        </option>
                    @endforeach
                </select>

                <select name="unit_status" class="rounded-xl border border-slate-300 py-1.5 px-3 text-xs text-slate-700 bg-white">
                    <option value="">Semua Status</option>
                    @foreach ($unitStatuses as $st)
                        <option value="{{ $st->value }}" {{ request('unit_status') == $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 font-semibold text-slate-700">
                    Filter
                </button>
                @if (request()->hasAny(['unit_code', 'unit_location', 'unit_condition', 'unit_status']))
                    <a href="{{ route('inventory.show', $catalog) }}" class="text-rose-600 hover:text-rose-800 font-medium">Reset</a>
                @endif
            </form>
        </div>

        <!-- Tabel Unit Fisik Desktop -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 font-mono">Kode Unit Fisik</th>
                        <th class="py-3 px-4">Lokasi Saat Ini</th>
                        <th class="py-3 px-4 text-center">Kondisi Fisik</th>
                        <th class="py-3 px-4 text-center">Status Ketersediaan</th>
                        <th class="py-3 px-4">Tgl Pembelian / Harga</th>
                        <th class="py-3 px-4 text-center">Aktif</th>
                        @if (Auth::user()->isAdmin())
                            <th class="py-3 px-4 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($units as $index => $unit)
                        <tr class="hover:bg-slate-50/60 transition {{ !$unit->is_active ? 'bg-slate-50/50 text-slate-400' : '' }}">
                            <td class="py-3 px-4 text-center font-mono text-slate-400">
                                {{ $units->firstItem() + $index }}
                            </td>
                            <!-- Kode Serial -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $unit->code }}
                            </td>
                            <!-- Lokasi -->
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $unit->location ? $unit->location->name : '-' }}
                            </td>
                            <!-- Kondisi -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $unit->condition->badgeClass() }}">
                                    {{ $unit->condition->label() }}
                                </span>
                            </td>
                            <!-- Status -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $unit->status->badgeClass() }}">
                                    {{ $unit->status->label() }}
                                </span>
                            </td>
                            <!-- Pengadaan -->
                            <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                <div>{{ $unit->purchase_date ? $unit->purchase_date->format('d/m/Y') : '-' }}</div>
                                @if ($unit->purchase_price)
                                    <div class="text-[10px] text-slate-400">Rp {{ number_format($unit->purchase_price, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <!-- Status Aktif -->
                            <td class="py-3 px-4 text-center">
                                @if ($unit->is_active)
                                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500" title="Aktif"></span>
                                @else
                                    <span class="inline-flex h-2 w-2 rounded-full bg-gray-400" title="Diarsipkan"></span>
                                @endif
                            </td>
                            <!-- Aksi Admin -->
                            @if (Auth::user()->isAdmin())
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- Edit Modal Trigger -->
                                        <button type="button" 
                                                @click="editingUnit = {{ json_encode($unit) }}"
                                                title="Edit Unit"
                                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/50 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>

                                        <!-- Toggle Status -->
                                        <form method="POST" action="{{ route('inventory.units.toggle', $unit) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" 
                                                    title="{{ $unit->is_active ? 'Nonaktifkan Unit' : 'Aktifkan Kembali' }}"
                                                    class="p-1.5 rounded-lg border {{ $unit->is_active ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }} transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                            </button>
                                        </form>

                                        <!-- Hapus / Arsip -->
                                        <form method="POST" 
                                              action="{{ route('inventory.units.destroy', $unit) }}" 
                                              class="inline" 
                                              onsubmit="return confirm('Hapus/arsipkan unit {{ $unit->code }}? Jika pernah ada riwayat sirkulasi, unit akan diarsipkan secara aman.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus Unit" class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->isAdmin() ? 8 : 7 }}" class="py-10 text-center text-slate-400">
                                Belum ada unit fisik yang sesuai filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Cards (< 640px) -->
        <div class="sm:hidden divide-y divide-slate-100">
            @forelse ($units as $unit)
                <div class="p-4 space-y-2.5 {{ !$unit->is_active ? 'bg-slate-50/50' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-sm text-slate-900">{{ $unit->code }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $unit->status->badgeClass() }}">
                            {{ $unit->status->label() }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>Lokasi: <strong>{{ $unit->location ? $unit->location->name : '-' }}</strong></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $unit->condition->badgeClass() }}">
                            {{ $unit->condition->label() }}
                        </span>
                    </div>
                    @if (Auth::user()->isAdmin())
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="editingUnit = {{ json_encode($unit) }}" class="text-xs text-indigo-600 font-semibold p-1">Edit</button>
                            <form method="POST" action="{{ route('inventory.units.toggle', $unit) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-amber-600 font-semibold p-1">
                                    {{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">Belum ada unit fisik.</div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($units->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $units->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== 3. MODAL TAMBAH UNIT FISIK BARU (ADMIN ONLY) ==================== -->
    @if (Auth::user()->isAdmin())
        <div x-show="openAddUnitModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openAddUnitModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-200 transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900">Tambah Unit Fisik Baru</h3>
                        <button type="button" @click="openAddUnitModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('inventory.units.store', $catalog) }}" class="mt-4 space-y-3.5">
                        @csrf

                        <!-- Kode Unit -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Kode Serial / Nomor Inventaris <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="code" 
                                   required 
                                   placeholder="Contoh: INV-LAP-001" 
                                   class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 font-mono focus:border-indigo-600 focus:outline-none">
                        </div>

                        <!-- Lokasi Ruangan / Rak -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Lokasi Ruangan / Rak <span class="text-rose-500">*</span>
                            </label>
                            <select name="location_id" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 bg-white focus:border-indigo-600 focus:outline-none">
                                <option value="">Pilih Lokasi</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- Kondisi -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Kondisi Fisik <span class="text-rose-500">*</span>
                                </label>
                                <select name="condition" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 bg-white focus:border-indigo-600 focus:outline-none">
                                    @foreach ($conditions as $c)
                                        <option value="{{ $c->value }}" {{ $c->value === 'Baik' ? 'selected' : '' }}>{{ $c->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Status Ketersediaan <span class="text-rose-500">*</span>
                                </label>
                                <select name="status" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 bg-white focus:border-indigo-600 focus:outline-none">
                                    @foreach ($unitStatuses as $st)
                                        <option value="{{ $st->value }}" {{ $st->value === 'Tersedia' ? 'selected' : '' }}>{{ $st->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- Tgl Pembelian -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pembelian</label>
                                <input type="date" name="purchase_date" class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-indigo-600 focus:outline-none">
                            </div>

                            <!-- Harga Beli -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Beli (Rp)</label>
                                <input type="number" name="purchase_price" min="0" placeholder="0" class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-indigo-600 focus:outline-none">
                            </div>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="openAddUnitModal = false" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs">
                                Simpan Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ==================== 4. MODAL EDIT UNIT FISIK ==================== -->
        <div x-show="editingUnit !== null" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="editingUnit = null"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-200 transition-all" x-show="editingUnit !== null">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900">
                            Perbarui Unit Fisik: <span class="font-mono text-indigo-600" x-text="editingUnit?.code"></span>
                        </h3>
                        <button type="button" @click="editingUnit = null" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form :action="'{{ url('inventory/units') }}/' + editingUnit?.id" method="POST" class="mt-4 space-y-3.5">
                        @csrf
                        @method('PUT')

                        <!-- Kode Unit -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Serial Unit <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" :value="editingUnit?.code" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 font-mono focus:border-indigo-600 focus:outline-none">
                        </div>

                        <!-- Lokasi -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Ruangan / Rak <span class="text-rose-500">*</span></label>
                            <select name="location_id" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 bg-white focus:border-indigo-600 focus:outline-none">
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" :selected="editingUnit?.location_id == {{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- Kondisi -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                                <select name="condition" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 bg-white focus:border-indigo-600 focus:outline-none">
                                    @foreach ($conditions as $c)
                                        <option value="{{ $c->value }}" :selected="editingUnit?.condition == '{{ $c->value }}'">{{ $c->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Ketersediaan <span class="text-rose-500">*</span></label>
                                <select name="status" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 bg-white focus:border-indigo-600 focus:outline-none">
                                    @foreach ($unitStatuses as $st)
                                        <option value="{{ $st->value }}" :selected="editingUnit?.status == '{{ $st->value }}'">{{ $st->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pembelian</label>
                                <input type="date" name="purchase_date" :value="editingUnit?.purchase_date ? editingUnit.purchase_date.substring(0,10) : ''" class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-indigo-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Beli (Rp)</label>
                                <input type="number" name="purchase_price" :value="editingUnit?.purchase_price" min="0" class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:border-indigo-600 focus:outline-none">
                            </div>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="editingUnit = null" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
