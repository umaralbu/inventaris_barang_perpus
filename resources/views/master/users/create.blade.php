@extends('layouts.app', ['title' => 'Tambah Pengguna Sistem'])

@section('page_title', 'Tambah Pengguna Sistem')
@section('page_subtitle', 'Membuat akun pengguna baru untuk akses Administrator atau Pegawai')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Form Akun Pengguna Baru</h3>
                <p class="text-xs text-slate-500">Lengkapi data kredensial akun sistem di bawah ini.</p>
            </div>
            <a href="{{ route('users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       placeholder="Contoh: Budi Santoso"
                       class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('name') border-rose-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Username Login <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           required 
                           autocomplete="username"
                           placeholder="Contoh: budi_perpus"
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('username') border-rose-400 @enderror">
                    @error('username')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email (Opsional) -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Email (Opsional)
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           autocomplete="email"
                           placeholder="budi@example.com"
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('email') border-rose-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="new-password"
                           placeholder="Minimal 6 karakter"
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition @error('password') border-rose-400 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Peran (Role) -->
                <div>
                    <label for="role" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Hak Akses (Role) <span class="text-rose-500">*</span>
                    </label>
                    <select id="role" 
                            name="role" 
                            required 
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none transition bg-white @error('role') border-rose-400 @enderror">
                        <option value="">Pilih Peran Akun</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', '1') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                    <span class="text-xs text-slate-700 font-medium">Akun Aktif (pengguna dapat langsung masuk)</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition">
                    Buat Akun Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

