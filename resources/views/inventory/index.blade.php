@extends('layouts.app', ['title' => 'Katalog & Unit Fisik Inventaris'])

@section('page_title', 'Katalog & Unit Barang Fisik')
@section('page_subtitle', 'Tingkat 1: Daftar jenis barang dan ketersediaan unit fisik perpustakaan')

@section('content')
<div class="space-y-5">

    <!-- Filter & Search Toolbar Container -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <form method="GET" action="{{ route('inventory.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- 1. Pencarian Kata Kunci / Kode Serial -->
                <div class="lg:col-span-2 relative">
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pencarian Barang / Kode Unit</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nama barang, merek, spesifikasi, atau kode unit (misal: INV-001)..." 
                               class="block w-full rounded-xl border border-slate-300 pl-9 pr-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition">
                    </div>
                </div>

                <!-- 2. Filter Kategori -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kategori</label>
                    <select name="category_id" 
                            class="block w-full rounded-xl border border-slate-300 py-2 pl-3 pr-8 text-xs text-slate-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Filter Lokasi Ruangan / Rak -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lokasi Unit Fisik</label>
                    <select name="location_id" 
                            class="block w-full rounded-xl border border-slate-300 py-2 pl-3 pr-8 text-xs text-slate-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white">
                        <option value="">Semua Lokasi Ruangan</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1 border-t border-slate-100 items-end">
                <!-- 4. Filter Kondisi Fisik Unit -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kondisi Fisik Unit</label>
                    <select name="condition" 
                            class="block w-full rounded-xl border border-slate-300 py-2 pl-3 pr-8 text-xs text-slate-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white">
                        <option value="">Semua Kondisi</option>
                        @foreach ($conditions as $cond)
                            <option value="{{ $cond->value }}" {{ request('condition') == $cond->value ? 'selected' : '' }}>
                                {{ $cond->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 5. Filter Ketersediaan Unit -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status Ketersediaan</label>
                    <select name="status" 
                            class="block w-full rounded-xl border border-slate-300 py-2 pl-3 pr-8 text-xs text-slate-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white">
                        <option value="">Semua Status Unit</option>
                        @foreach ($unitStatuses as $st)
                            <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 6. Filter Status Katalog (Aktif/Arsip) -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status Katalog</label>
                    <select name="is_active" 
                            class="block w-full rounded-xl border border-slate-300 py-2 pl-3 pr-8 text-xs text-slate-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Hanya Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Hanya Diarsipkan (Nonaktif)</option>
                    </select>
                </div>

                <!-- Tombol Submit & Reset & Tambah -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition text-center">
                        Terapkan Filter
                    </button>
                    @if (request()->hasAny(['search', 'category_id', 'location_id', 'condition', 'status', 'is_active']))
                        <a href="{{ route('inventory.index') }}" class="py-2 px-2.5 rounded-xl border border-slate-200 text-rose-600 hover:bg-rose-50 text-xs font-medium transition" title="Reset Semua Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @if (Auth::user()->isAdmin())
            <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                <span class="text-xs text-slate-500">
                    Menampilkan total <strong>{{ $catalogs->total() }}</strong> kelompok katalog jenis barang
                </span>
                <a href="{{ route('inventory.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Katalog & Generate Unit</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Desktop Table (>= 640px) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 w-14">Foto</th>
                        <th class="py-3 px-4">Nama Barang & Merek</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Spesifikasi Ringkas</th>
                        <th class="py-3 px-4 text-center">Total Unit</th>
                        <th class="py-3 px-4 text-center">Unit Tersedia</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($catalogs as $index => $cat)
                        <tr class="hover:bg-slate-50/70 transition {{ !$cat->is_active ? 'bg-slate-50/40 text-slate-400' : '' }}">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400">
                                {{ $catalogs->firstItem() + $index }}
                            </td>
                            <!-- Thumbnail Foto -->
                            <td class="py-3.5 px-4">
                                @if ($cat->photo && Storage::disk('public')->exists($cat->photo))
                                    <img src="{{ asset('storage/' . $cat->photo) }}" 
                                         alt="{{ $cat->name }}" 
                                         class="h-10 w-10 rounded-xl object-cover border border-slate-200 shadow-xs">
                                @else
                                    <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center border border-slate-200">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <!-- Nama & Merek -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('inventory.show', $cat) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                    {{ $cat->name }}
                                </a>
                                @if ($cat->brand)
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">
                                        {{ $cat->brand }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400">-</span>
                                @endif
                            </td>
                            <!-- Kategori -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $cat->category ? $cat->category->name : 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <!-- Spesifikasi -->
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $cat->specification ?: '-' }}
                            </td>
                            <!-- Total Unit -->
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ $cat->total_units_count }} {{ $cat->unit_name }}
                            </td>
                            <!-- Unit Tersedia -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($cat->available_units_count > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        {{ $cat->available_units_count }} {{ $cat->unit_name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        Habis
                                    </span>
                                @endif
                            </td>
                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Tingkat 2: Lihat Unit Fisik -->
                                    <a href="{{ route('inventory.show', $cat) }}" 
                                       title="Buka Daftar Unit Fisik (Tingkat 2)" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-[11px] border border-indigo-200/80 transition">
                                        <span>Unit Fisik</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>

                                    @if (Auth::user()->isAdmin())
                                        <!-- Edit -->
                                        <a href="{{ route('inventory.edit', $cat) }}" 
                                           title="Edit Katalog" 
                                           class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/50 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </a>

                                        <!-- Archive / Delete -->
                                        <form method="POST" 
                                              action="{{ route('inventory.destroy', $cat) }}" 
                                              class="inline" 
                                              onsubmit="return confirm('Katalog {{ $cat->name }} memiliki {{ $cat->total_units_count }} unit fisik. Jika unit pernah memiliki riwayat transaksi, sistem akan mengarsipkannya secara aman. Lanjutkan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    title="Hapus / Arsipkan" 
                                                    class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                Tidak ada jenis barang di katalog yang sesuai kriteria pencarian dan filter Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Cards (< 640px) -->
        <div class="sm:hidden divide-y divide-slate-100">
            @forelse ($catalogs as $cat)
                <div class="p-4 space-y-3 {{ !$cat->is_active ? 'bg-slate-50/60' : '' }}">
                    <div class="flex items-start gap-3">
                        @if ($cat->photo && Storage::disk('public')->exists($cat->photo))
                            <img src="{{ asset('storage/' . $cat->photo) }}" alt="{{ $cat->name }}" class="h-12 w-12 rounded-xl object-cover border border-slate-200 shrink-0">
                        @else
                            <div class="h-12 w-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center border border-slate-200 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-slate-900 leading-tight">
                                <a href="{{ route('inventory.show', $cat) }}">{{ $cat->name }}</a>
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $cat->brand ?: 'Tanpa merek' }} &bull; {{ $cat->category ? $cat->category->name : '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                        <span class="text-slate-600 font-medium">Stok: <strong>{{ $cat->total_units_count }}</strong> {{ $cat->unit_name }}</span>
                        @if ($cat->available_units_count > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                {{ $cat->available_units_count }} Tersedia
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                Stok Habis
                            </span>
                        @endif
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                        <a href="{{ route('inventory.show', $cat) }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 font-semibold text-xs border border-indigo-200">
                            Lihat Unit Fisik &rarr;
                        </a>
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('inventory.edit', $cat) }}" class="p-1.5 text-slate-600 hover:text-indigo-600">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">Tidak ada barang ditemukan.</div>
            @endforelse
        </div>

        <!-- Pagination Bar -->
        @if ($catalogs->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $catalogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
