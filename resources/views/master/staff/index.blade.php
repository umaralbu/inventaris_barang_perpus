@extends('layouts.app', ['title' => 'Staf Perpustakaan'])

@section('page_title', 'Staf Perpustakaan')
@section('page_subtitle', 'Pengelolaan daftar pegawai dan staf penanggung jawab operasional fisik barang')

@section('content')
<div class="space-y-5">

    <!-- Header Actions & Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('staff.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative w-full sm:w-64">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama lengkap staf..." 
                       class="block w-full rounded-xl border border-slate-300 pl-9 pr-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition">
            </div>

            <select name="status" 
                    onchange="this.form.submit()" 
                    class="rounded-xl border border-slate-300 py-2 pl-3 pr-8 text-xs text-slate-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hanya Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Hanya Nonaktif (Arsip)</option>
            </select>

            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                Terapkan
            </button>

            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('staff.index') }}" class="px-3 py-2 text-xs text-rose-600 hover:text-rose-800 font-medium">
                    Reset
                </a>
            @endif
        </form>

        <!-- CTA Tambah -->
        <a href="{{ route('staff.create') }}" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Daftarkan Staf</span>
        </a>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Desktop Table (>= 640px) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Lengkap & Gelar</th>
                        <th class="py-3 px-4 text-center">Riwayat Transaksi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($staff as $index => $st)
                        <tr class="hover:bg-slate-50/60 transition {{ !$st->is_active ? 'bg-slate-50/40 text-slate-400' : '' }}">
                            <td class="py-3 px-4 text-center font-mono text-slate-400">
                                {{ $staff->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $st->name }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $st->transactions_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $st->transactions_count }} transaksi tercatat
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($st->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Toggle Status -->
                                    <form method="POST" action="{{ route('staff.toggle', $st) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                title="{{ $st->is_active ? 'Nonaktifkan (Arsipkan)' : 'Aktifkan Kembali' }}" 
                                                class="p-1.5 rounded-lg border {{ $st->is_active ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }} transition">
                                            @if ($st->is_active)
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- Edit -->
                                    <a href="{{ route('staff.edit', $st) }}" 
                                       title="Edit Data" 
                                       class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/50 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </a>

                                    <!-- Delete / Archive -->
                                    <form method="POST" 
                                          action="{{ route('staff.destroy', $st) }}" 
                                          class="inline" 
                                          onsubmit="return confirm('{{ $st->transactions_count > 0 ? "Staf ini memiliki {$st->transactions_count} transaksi riil. Akun akan dinonaktifkan (diarsipkan). Lanjutkan?" : "Hapus staf ini permanen?" }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Hapus / Arsipkan" 
                                                class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Tidak ada data staf yang sesuai filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Cards (< 640px) -->
        <div class="sm:hidden divide-y divide-slate-100">
            @forelse ($staff as $st)
                <div class="p-4 space-y-3 {{ !$st->is_active ? 'bg-slate-50/50' : '' }}">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900">{{ $st->name }}</h4>
                        @if ($st->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">
                                Nonaktif
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                        <span class="text-slate-500 font-semibold text-indigo-600">{{ $st->transactions_count }} transaksi</span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('staff.edit', $st) }}" class="text-indigo-600 font-semibold p-1">Edit</a>
                            <form method="POST" action="{{ route('staff.toggle', $st) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-amber-600 font-semibold p-1">
                                    {{ $st->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-slate-400">Tidak ada data staf.</div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($staff->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

