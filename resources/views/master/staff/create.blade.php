@extends('layouts.app', ['title' => 'Tambah Staf Perpustakaan'])

@section('page_title', 'Daftarkan Staf Perpustakaan')
@section('page_subtitle', 'Mendaftarkan pegawai/staf yang menjadi penanggung jawab fisik barang')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Form Staf Baru</h3>
                <p class="text-xs text-slate-500">Identitas staf fisik peminjam atau pemegang barang perpustakaan.</p>
            </div>
            <a href="{{ route('staff.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('staff.store') }}" class="space-y-4">
            @csrf

            <!-- Nama Staf -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       placeholder="Contoh: Muhammad Asharul Ma’ali, S.Kom."
                       class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('name') border-rose-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', '1') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                    <span class="text-xs text-slate-700 font-medium">Status Aktif (dapat dipilih di formulir transaksi peminjaman)</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('staff.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition">
                    Simpan Staf
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

