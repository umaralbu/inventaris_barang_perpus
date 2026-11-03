@php
    $isAdmin = Auth::user()->isAdmin();
    $pendingCount = \App\Models\Transaction::where('status', \App\Enums\TransactionStatus::MENUNGGU_PERSETUJUAN->value)->count();
@endphp

<!-- ================= 1. MENU UTAMA ================= -->
<div class="space-y-1.5">
    <a href="{{ route('dashboard') }}" 
       class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Dashboard</span>
    </a>
</div>

<!-- ================= 2. MODUL TRANSAKSI ================= -->
<div class="pt-5">
    <p class="px-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
        Sirkulasi & Transaksi
    </p>

    <div class="mt-2.5 space-y-1.5">
        <!-- Semua Riwayat Transaksi -->
        <a href="{{ route('transactions.index') }}" 
           class="group flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <div class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Riwayat Transaksi</span>
            </div>
        </a>

        <!-- Antrean Persetujuan (Khusus Admin) -->
        @if ($isAdmin)
            <a href="{{ route('transactions.pending') }}" 
               class="group flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('transactions.pending') || request()->routeIs('transactions.approve.show') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('transactions.pending') || request()->routeIs('transactions.approve.show') ? 'text-amber-300' : 'text-amber-400 group-hover:text-amber-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Antrean Persetujuan</span>
                </div>
                @if ($pendingCount > 0)
                    <span x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}" 
                          class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500 text-slate-900 font-black">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
        @endif

        <!-- Form Pengajuan Pinjam (Pegawai & Admin) -->
        <a href="{{ route('transactions.borrow.create') }}" 
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('transactions.borrow.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('transactions.borrow.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Ajukan Peminjaman</span>
        </a>

        <!-- Form Pengembalian (Pegawai & Admin) -->
        <a href="{{ Route::has('transactions.return.create') ? route('transactions.return.create') : '#' }}" 
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
            <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
            </svg>
            <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Pengembalian Barang</span>
        </a>

        <!-- Khusus Admin: Mutasi & Servis -->
        @if ($isAdmin)
            <a href="{{ Route::has('transactions.transfer.create') ? route('transactions.transfer.create') : '#' }}" 
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Mutasi Lokasi</span>
            </a>

            <a href="{{ Route::has('transactions.maintenance.create') ? route('transactions.maintenance.create') : '#' }}" 
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Servis / Pemeliharaan</span>
            </a>
        @endif
    </div>
</div>

<!-- ================= 3. MODUL INVENTARIS ================= -->
<div class="pt-5">
    <p class="px-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
        Manajemen Inventaris
    </p>

    <div class="mt-2.5 space-y-1.5">
        <!-- Katalog & Unit Barang -->
        <a href="{{ route('inventory.index') }}" 
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('inventory.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('inventory.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Katalog & Unit Fisik</span>
        </a>

        @if ($isAdmin)
            <!-- Kategori Barang -->
            <a href="{{ route('categories.index') }}" 
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Kategori Barang</span>
            </a>

            <!-- Lokasi Ruangan / Rak -->
            <a href="{{ route('locations.index') }}" 
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('locations.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('locations.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Ruangan & Lokasi</span>
            </a>
        @endif
    </div>
</div>

<!-- ================= 4. MASTER DATA (ADMIN ONLY) ================= -->
@if ($isAdmin)
    <div class="pt-5">
        <p class="px-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">
            Pengelolaan Master Data
        </p>

        <div class="mt-2.5 space-y-1.5">
            <!-- Staf Perpustakaan -->
            <a href="{{ route('staff.index') }}" 
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('staff.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('staff.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Staf Perpustakaan</span>
            </a>

            <!-- Akun Pengguna -->
            <a href="{{ route('users.index') }}" 
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}">Akun Pengguna (Users)</span>
            </a>
        </div>
    </div>
@endif
