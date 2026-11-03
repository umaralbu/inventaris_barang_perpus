@extends('layouts.app', ['title' => 'Edit Katalog: ' . $catalog->name])

@section('page_title', 'Edit Katalog Barang')
@section('page_subtitle', 'Memperbarui informasi data induk jenis inventaris')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Perbarui Katalog: {{ $catalog->name }}</h3>
                <p class="text-xs text-slate-500">Perubahan data induk akan disinkronkan ke seluruh unit fisik terkait.</p>
            </div>
            <a href="{{ route('inventory.show', $catalog) }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('inventory.update', $catalog) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Barang -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Jenis Barang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $catalog->name) }}" 
                           required 
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('name') border-rose-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kategori Klasifikasi <span class="text-rose-500">*</span>
                    </label>
                    <select id="category_id" 
                            name="category_id" 
                            required 
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white @error('category_id') border-rose-400 @enderror">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $catalog->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <!-- Merek -->
                <div>
                    <label for="brand" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Merek / Pabrikan
                    </label>
                    <input type="text" 
                           id="brand" 
                           name="brand" 
                           value="{{ old('brand', $catalog->brand) }}" 
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('brand') border-rose-400 @enderror">
                    @error('brand') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <!-- Satuan Unit -->
                <div>
                    <label for="unit_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Satuan Takaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="unit_name" 
                           name="unit_name" 
                           value="{{ old('unit_name', $catalog->unit_name) }}" 
                           required 
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('unit_name') border-rose-400 @enderror">
                    @error('unit_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <!-- Upload Foto Baru -->
                <div>
                    <label for="photo" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Ubah Foto (Opsional, Maks. 2 MB)
                    </label>
                    <input type="file" 
                           id="photo" 
                           name="photo" 
                           accept="image/jpeg,image/png,image/webp"
                           class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition @error('photo') border-rose-400 @enderror">
                    @error('photo') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <!-- Spesifikasi -->
                <div class="sm:col-span-2">
                    <label for="specification" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Spesifikasi Teknis
                    </label>
                    <textarea id="specification" 
                              name="specification" 
                              rows="2" 
                              class="block w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition">{{ old('specification', $catalog->specification) }}</textarea>
                </div>

                <!-- Deskripsi -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Deskripsi / Catatan Tambahan
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="2" 
                              class="block w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition">{{ old('description', $catalog->description) }}</textarea>
                </div>

                <!-- Status Aktif -->
                <div class="sm:col-span-2 pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $catalog->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <span class="text-xs text-slate-700 font-medium">Status Katalog Aktif (dapat dipilih pada formulir permohonan peminjaman)</span>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('inventory.show', $catalog) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
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
