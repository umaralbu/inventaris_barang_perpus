@extends('layouts.app', ['title' => 'Tambah Katalog Inventaris'])

@section('page_title', 'Tambah Katalog Barang')
@section('page_subtitle', 'Mendaftarkan jenis aset baru beserta opsi pembuatan nomor seri unit fisik otomatis')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ generateUnits: true }">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Formulir Pendaftaran Katalog Barang</h3>
                <p class="text-xs text-slate-500">Lengkapi spesifikasi induk jenis aset perpustakaan.</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('inventory.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- ==================== BAGIAN A: DATA INDUK KATALOG ==================== -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                    Informasi Data Induk (Katalog)
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Barang -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Jenis Barang <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               placeholder="Contoh: Laptop Lenovo ThinkPad E14, Proyektor Epson EB-X500"
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
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Merek -->
                    <div>
                        <label for="brand" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Merek / Pabrikan (Opsional)
                        </label>
                        <input type="text" 
                               id="brand" 
                               name="brand" 
                               value="{{ old('brand') }}" 
                               placeholder="Contoh: Lenovo, Epson, Pilot, Informa"
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
                               value="{{ old('unit_name', 'unit') }}" 
                               required 
                               placeholder="unit, pcs, buah, eksemplar, set"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('unit_name') border-rose-400 @enderror">
                        @error('unit_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Upload Foto -->
                    <div>
                        <label for="photo" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Foto Barang (Maks. 2 MB)
                        </label>
                        <input type="file" 
                               id="photo" 
                               name="photo" 
                               accept="image/jpeg,image/png,image/webp"
                               class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition @error('photo') border-rose-400 @enderror">
                        @error('photo') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Spesifikasi Teknis -->
                    <div class="sm:col-span-2">
                        <label for="specification" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Spesifikasi Teknis (Opsional)
                        </label>
                        <textarea id="specification" 
                                  name="specification" 
                                  rows="2" 
                                  placeholder="Contoh: RAM 16GB, SSD 512GB, Core i5 Generasi 12, Warna Hitam"
                                  class="block w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition">{{ old('specification') }}</textarea>
                    </div>

                    <!-- Keterangan Tambahan -->
                    <div class="sm:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Deskripsi / Catatan Tambahan (Opsional)
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="2" 
                                  placeholder="Keterangan khusus penggunaan barang atau peruntukannya..."
                                  class="block w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- ==================== BAGIAN B: GENERATOR UNIT FISIK OTOMATIS ==================== -->
            <div class="pt-4 border-t border-slate-200">
                <div class="rounded-2xl bg-indigo-50/60 border border-indigo-100 p-4 sm:p-5 space-y-4">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" 
                               name="generate_units" 
                               value="1" 
                               x-model="generateUnits"
                               class="h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-600">
                        <div>
                            <span class="text-xs font-bold text-indigo-950 block">Buat & Generate Unit Fisik Sekaligus</span>
                            <span class="text-[11px] text-indigo-700">Sistem akan langsung membuat nomor kode unit fisik dan stok awal yang siap dipinjam.</span>
                        </div>
                    </label>

                    <div x-show="generateUnits" x-cloak class="pt-3 border-t border-indigo-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Jumlah Unit -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Jumlah Stok Unit Fisik <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="initial_units_count" 
                                   value="{{ old('initial_units_count', '1') }}" 
                                   min="1" 
                                   max="100" 
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-900 bg-white focus:border-indigo-600 focus:outline-none">
                            <span class="text-[10px] text-slate-500 mt-0.5 block">Contoh: 5 untuk membuat unit 001 s.d 005</span>
                        </div>

                        <!-- Prefix Kode Serial -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Awalan Prefix Kode Serial (Opsional)
                            </label>
                            <input type="text" 
                                   name="code_prefix" 
                                   value="{{ old('code_prefix') }}" 
                                   placeholder="Contoh: INV-LAP- (Kosongkan untuk otomatis)" 
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-900 font-mono bg-white focus:border-indigo-600 focus:outline-none">
                        </div>

                        <!-- Lokasi Penempatan Awal -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Ruangan / Lokasi Penyimpanan Awal <span class="text-rose-500">*</span>
                            </label>
                            <select name="location_id" class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-700 bg-white focus:border-indigo-600 focus:outline-none">
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Pembelian -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pengadaan / Beli</label>
                            <input type="date" 
                                   name="purchase_date" 
                                   value="{{ old('purchase_date', date('Y-m-d')) }}" 
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-900 bg-white focus:border-indigo-600 focus:outline-none">
                        </div>

                        <!-- Harga Per Unit -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Beli Satuan (Rp - Opsional)</label>
                            <input type="number" 
                                   name="purchase_price" 
                                   value="{{ old('purchase_price') }}" 
                                   min="0" 
                                   placeholder="0" 
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-900 bg-white focus:border-indigo-600 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('inventory.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition">
                    Simpan Katalog & Generate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
