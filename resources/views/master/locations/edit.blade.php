@extends('layouts.app', ['title' => 'Edit Lokasi / Ruangan'])

@section('page_title', 'Edit Lokasi / Ruangan')
@section('page_subtitle', 'Memperbarui informasi ruangan dan rak inventaris')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Perbarui Lokasi: {{ $location->name }}</h3>
                <p class="text-xs text-slate-500">Sesuaikan data detail lokasi di bawah ini.</p>
            </div>
            <a href="{{ route('locations.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('locations.update', $location) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Nama Lokasi -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Ruangan / Rak <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $location->name) }}" 
                       required 
                       class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('name') border-rose-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Deskripsi / Letak Gedung (Opsional)
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3" 
                          class="block w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('description') border-rose-400 @enderror">{{ old('description', $location->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $location->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                    <span class="text-xs text-slate-700 font-medium">Status Aktif</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('locations.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

