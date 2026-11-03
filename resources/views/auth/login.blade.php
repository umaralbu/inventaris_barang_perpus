@extends('layouts.guest', ['title' => 'Masuk ke Sistem'])

@section('content')
<div x-data="{ submitting: false, showPassword: false }">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Selamat Datang</h3>
        <p class="text-xs text-slate-500 mt-0.5">Silakan masukkan username dan password akun Anda.</p>
    </div>

    <!-- Alert Notifikasi Flash -->
    @if (session('success'))
        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 flex items-start gap-3 text-emerald-800 text-xs">
            <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 rounded-xl bg-blue-50 border border-blue-200 p-3.5 flex items-start gap-3 text-blue-800 text-xs">
            <svg class="w-4 h-4 text-blue-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>{{ session('info') }}</div>
        </div>
    @endif

    <!-- Alert Kesalahan Validasi / Kredensial Salah -->
    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-3.5 flex items-start gap-3 text-rose-800 text-xs">
            <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                @if ($errors->has('username'))
                    <span>{{ $errors->first('username') }}</span>
                @elseif ($errors->has('password'))
                    <span>{{ $errors->first('password') }}</span>
                @else
                    <span>Terjadi kesalahan saat masuk. Silakan periksa kembali formulir.</span>
                @endif
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" @submit="submitting = true" class="space-y-4">
        @csrf

        <!-- Input Username -->
        <div>
            <label for="username" class="block text-xs font-medium text-slate-700 mb-1.5">
                Username
            </label>
            <div class="relative rounded-xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="{{ old('username') }}" 
                       required 
                       autofocus 
                       autocomplete="username"
                       placeholder="Masukkan username Anda"
                       class="block w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 focus:outline-none transition sm:leading-6 @error('username') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
            </div>
        </div>

        <!-- Input Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-medium text-slate-700">
                    Password
                </label>
            </div>
            <div class="relative rounded-xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input :type="showPassword ? 'text' : 'password'" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="block w-full rounded-xl border border-slate-300 pl-10 pr-10 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 focus:outline-none transition sm:leading-6 @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                <button type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none"
                        tabindex="-1">
                    <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Ingat Saya -->
        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" 
                       name="remember" 
                       value="1" 
                       {{ old('remember') ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600/20">
                <span class="text-xs text-slate-600">Ingat Saya di perangkat ini</span>
            </label>
        </div>

        <!-- Tombol Masuk dengan Loading State -->
        <div class="pt-2">
            <button type="submit" 
                    :disabled="submitting" 
                    class="relative w-full flex justify-center items-center gap-2 rounded-xl bg-indigo-600 py-2.5 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-75 disabled:cursor-not-allowed transition">
                <span x-show="!submitting">Masuk ke Sistem</span>
                <span x-show="submitting" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Memverifikasi...</span>
                </span>
            </button>
        </div>
    </form>
</div>
@endsection

