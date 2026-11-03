<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Sistem Inventaris Perpustakaan</title>
    
    <!-- Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|inter:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind CSS / Vite -->
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased overflow-x-hidden" x-data="{ mobileMenuOpen: false, sidebarCollapsed: false }">
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- ==================== MOBILE DRAWER NAVIGATION (<= 1024px) ==================== -->
        <div x-show="mobileMenuOpen" 
             x-cloak 
             class="relative z-50 lg:hidden" 
             role="dialog" 
             aria-modal="true">
            <!-- Backdrop -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-linear duration-300" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="mobileMenuOpen = false" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div class="fixed inset-0 flex">
                <div x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-in-out duration-300 transform" 
                     x-transition:enter-start="-translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transition ease-in-out duration-300 transform" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="-translate-x-full" 
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    
                    <div class="flex flex-col flex-1 bg-slate-900 px-5 pb-6 pt-5 overflow-y-auto">
                        <!-- Drawer Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-sm font-bold text-white tracking-tight leading-tight">Inventaris Perpus</h1>
                                    <p class="text-[10px] text-slate-400">Panel Logistik Aset</p>
                                </div>
                            </div>
                            <button type="button" @click="mobileMenuOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Mobile User Info Card -->
                        <div class="mt-4 p-3 bg-slate-800/60 rounded-xl border border-slate-700/60 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-indigo-600/30 text-indigo-300 font-semibold flex items-center justify-center text-xs border border-indigo-500/40">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-white truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ Auth::user()->isAdmin() ? 'bg-purple-900/60 text-purple-200 border border-purple-700' : 'bg-sky-900/60 text-sky-200 border border-sky-700' }}">
                                    {{ Auth::user()->isAdmin() ? 'Administrator' : 'Pegawai' }}
                                </span>
                            </div>
                        </div>

                        <!-- Mobile Nav Links -->
                        <nav class="mt-5 flex-1 space-y-1">
                            @include('layouts.partials.nav-links', ['isMobile' => true])
                        </nav>

                        <!-- Mobile Logout -->
                        <div class="pt-4 border-t border-slate-800">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Keluar dari Akun</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== DESKTOP SIDEBAR (>= 1024px) ==================== -->
        <aside :class="sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'" 
               class="hidden lg:flex lg:flex-col shrink-0 bg-slate-900 border-r border-slate-800 transition-all duration-300 ease-in-out min-h-screen">
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center px-4 border-b border-slate-800 justify-between">
                <div class="flex items-center gap-3 overflow-hidden" x-show="!sidebarCollapsed">
                    <div class="h-9 w-9 shrink-0 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-white tracking-tight leading-tight truncate">Inventaris Perpus</h1>
                        <p class="text-xs text-slate-400 truncate">V2 Logistik & Aset</p>
                    </div>
                </div>

                <div x-show="sidebarCollapsed" class="mx-auto">
                    <div class="h-9 w-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>

                <button type="button" 
                        @click="sidebarCollapsed = !sidebarCollapsed" 
                        class="text-slate-400 hover:text-white p-1.5 rounded-lg focus:outline-none hidden lg:block"
                        :title="sidebarCollapsed ? 'Perlebar Sidebar' : 'Ciutkan Sidebar'">
                    <svg class="w-4 h-4 transform transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Items -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @include('layouts.partials.nav-links', ['isMobile' => false])
            </nav>

            <!-- Bottom User Profile & Logout -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-800/40 border border-slate-800" x-show="!sidebarCollapsed">
                    <div class="h-9 w-9 rounded-full bg-indigo-600/30 text-indigo-300 font-bold flex items-center justify-center text-xs shrink-0 border border-indigo-500/40">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold {{ Auth::user()->isAdmin() ? 'bg-purple-900/60 text-purple-200 border border-purple-700' : 'bg-sky-900/60 text-sky-200 border border-sky-700' }}">
                            {{ Auth::user()->isAdmin() ? 'Administrator' : 'Pegawai' }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="text-slate-400 hover:text-rose-400 p-2 rounded-lg hover:bg-slate-700/50 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div x-show="sidebarCollapsed" class="flex justify-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="text-slate-400 hover:text-rose-400 p-2 rounded-xl hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT AREA ==================== -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <!-- Header Topbar -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger Button -->
                    <button type="button" 
                            @click="mobileMenuOpen = true" 
                            class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page Title / Breadcrumb -->
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 leading-tight">
                            @yield('page_title', 'Dashboard')
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 hidden sm:block">
                            @yield('page_subtitle', 'Sistem Informasi Manajemen Barang Perpustakaan')
                        </p>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3">
                    <!-- Date badge -->
                    <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs sm:text-sm font-semibold border border-slate-200">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ now()->translatedFormat('l, d M Y') }}</span>
                    </div>

                    <!-- Role Badge on Mobile -->
                    <span class="inline-flex sm:hidden items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ Auth::user()->isAdmin() ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-sky-100 text-sky-700 border border-sky-200' }}">
                        {{ Auth::user()->isAdmin() ? 'Admin' : 'Pegawai' }}
                    </span>
                </div>
            </header>

            <!-- Alerts / Toast Flash Messages -->
            <div class="px-4 sm:px-6 pt-4">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 shadow-sm flex items-start justify-between gap-3 text-sm">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800 shadow-sm flex items-start justify-between gap-3 text-sm">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-amber-800 shadow-sm flex items-start justify-between gap-3 text-sm">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="font-medium">{{ session('warning') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Page Main Content Slot -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

