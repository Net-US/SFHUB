<!-- resources/views/layouts/app.blade.php - Admin Layout -->
<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin | SFHUB')</title>

    <!-- Favicon -->
    @if (\App\Models\SiteSetting::getValue('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::getValue('site_favicon') }}">
    @else
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @endif

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        stone: {
                            850: "#1f1f1f",
                        },
                        primary: {
                            50: "#fef3e9",
                            100: "#fde4d3",
                            200: "#fbc8a7",
                            300: "#f9ab7b",
                            400: "#f78f4f",
                            500: "#f57223",
                            600: "#e65e0e",
                            700: "#c44b0b",
                            800: "#a23909",
                            900: "#802707",
                        },
                    },
                },
            },
        };
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <style>
        body {
            font-family: "Inter", sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #d6d3d1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a29e;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        /* Sidebar Transitions */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-text {
            transition:
                opacity 0.2s ease,
                transform 0.2s ease;
            white-space: nowrap;
            opacity: 1;
        }

        .sidebar-collapsed .nav-text {
            opacity: 0;
            transform: translateX(-10px);
            pointer-events: none;
            display: none;
        }

        .sidebar-collapsed .nav-header {
            display: none;
        }

        .sidebar-collapsed .logo-text {
            display: none;
        }

        .sidebar-collapsed .nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar-collapsed .nav-item i {
            margin-right: 0;
        }

        /* Active State */
        .nav-active {
            background-color: rgb(254 243 233) !important;
            color: rgb(229 88 35) !important;
        }

        .dark .nav-active {
            background-color: rgb(229 88 35 / 0.15) !important;
            color: rgb(249 171 123) !important;
        }

        /* Modal Styles */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Priority colors */
        .priority-urgent-important {
            border-left-color: #ef4444;
        }

        .priority-important-not-urgent {
            border-left-color: #3b82f6;
        }

        .priority-urgent-not-important {
            border-left-color: #f97316;
        }

        .priority-not-urgent-not-important {
            border-left-color: #6b7280;
        }
    </style>

    @stack('styles')
</head>
@include('components.payment-status-banner')

<body
    class="bg-stone-50 text-stone-800 dark:bg-stone-950 dark:text-stone-100 h-screen flex overflow-hidden transition-colors duration-300">
    <!-- Sidebar Navigation -->
    <aside id="sidebar"
        class="bg-white dark:bg-stone-900 border-r border-stone-200 dark:border-stone-800 hidden md:flex flex-col z-20 shadow-sm w-64 sidebar-transition relative">
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()"
            class="absolute -right-3 top-8 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-full w-6 h-6 flex items-center justify-center text-stone-500 hover:text-primary-500 shadow-sm z-30 transform transition-transform hover:scale-110 focus:outline-none">
            <i id="sidebar-toggle-icon" class="fa-solid fa-chevron-left text-xs"></i>
        </button>

        <div class="p-6 border-b border-stone-100 dark:border-stone-800 flex items-center h-20">
            <i class="fa-solid fa-layer-group text-primary-500 text-2xl mr-3 flex-shrink-0"></i>
            <div class="logo-text overflow-hidden">
                <h1 class="text-lg font-bold text-stone-900 dark:text-white tracking-tight">SF<span
                        class="text-primary-500">HUB</span></h1>
                <p class="text-[10px] text-stone-500 dark:text-stone-400">Panel Admin</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <!-- Group: DASHBOARD -->
            <div class="mb-6 px-3">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">Dashboard
                </p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.index') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.index') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-chart-pie w-5 text-center mr-3"></i>
                            <span class="nav-text">Ringkasan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.analytics') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.analytics') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-chart-line w-5 text-center mr-3"></i>
                            <span class="nav-text">Analitik</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Group: USER MANAGEMENT -->
            <div class="mb-6 px-3">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">Manajemen
                    Pengguna</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.users') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.users') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-users w-5 text-center mr-3"></i>
                            <span class="nav-text">Semua Pengguna</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.subscriptions') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.subscriptions') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-crown w-5 text-center mr-3"></i>
                            <span class="nav-text">Langganan</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Group: CONTENT MANAGEMENT -->
            <div class="mb-6 px-3">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">Manajemen
                    Konten</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.landing') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.landing') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-globe w-5 text-center mr-3"></i>
                            <span class="nav-text">Halaman Utama</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.blog.index') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.blog.*') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-newspaper w-5 text-center mr-3"></i>
                            <span class="nav-text">Blog & Artikel</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.faq.index') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.faq.*') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-circle-question w-5 text-center mr-3"></i>
                            <span class="nav-text">FAQ & Bantuan</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Group: SEO & SETTINGS -->
            <div class="mb-6 px-3">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">SEO &
                    Pengaturan</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.seo.index') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.seo.*') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-magnifying-glass w-5 text-center mr-3"></i>
                            <span class="nav-text">Pengaturan SEO</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.index') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.settings.*') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-gear w-5 text-center mr-3"></i>
                            <span class="nav-text">Pengaturan Sistem</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Group: SYSTEM -->
            <div class="mb-6 px-3">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">Sistem</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.logs.index') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.logs.*') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-history w-5 text-center mr-3"></i>
                            <span class="nav-text">Log Aktivitas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.backups') }}"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-stone-900 dark:hover:text-white transition-all {{ request()->routeIs('admin.settings.backups') ? 'nav-active' : '' }}">
                            <i class="fa-solid fa-database w-5 text-center mr-3"></i>
                            <span class="nav-text">Backup & Restore</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="p-3 border-t border-stone-100 dark:border-stone-800 relative">
            <button onclick="toggleSidebarUserMenu()"
                class="w-full flex items-center nav-item p-2 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 cursor-pointer transition-colors group">
                <div
                    class="w-8 h-8 rounded-full bg-gradient-to-tr from-orange-400 to-orange-600 flex items-center justify-center text-white text-xs font-bold shadow-md flex-shrink-0">
                    <i class="fa-solid fa-shield-alt text-xs"></i>
                </div>
                <div class="ml-3 overflow-hidden nav-text flex-1 text-left">
                    <span
                        class="text-sm font-bold text-stone-800 dark:text-white truncate">{{ auth()->user()->name }}</span>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">
                        Administrator</p>
                </div>
                <i class="fa-solid fa-ellipsis-vertical text-stone-400 nav-text text-xs group-hover:text-stone-600"></i>
            </button>
            <div id="sidebar-user-menu"
                class="hidden absolute bottom-full left-3 right-3 mb-2 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl shadow-xl z-50 overflow-hidden">
                <div
                    class="px-4 py-3 border-b border-stone-100 dark:border-stone-700 bg-gradient-to-br from-orange-50 to-red-50 dark:from-stone-800 dark:to-stone-800">
                    <p class="text-sm font-bold text-stone-800 dark:text-white truncate">{{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-stone-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors"><i
                            class="fa-solid fa-user-circle w-4 text-center text-stone-400"></i>Profil Saya</a>
                    @if ((string) auth()->user()->plan === 'free')
                        <a href="{{ route('auth.onboarding-payment') }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"><i
                                class="fa-solid fa-crown w-4 text-center text-emerald-500"></i>Upgrade Plan</a>
                    @endif
                    <a href="{{ route('profile.edit') }}#pengaturan"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors"><i
                            class="fa-solid fa-sliders w-4 text-center text-stone-400"></i>Pengaturan</a>
                    <a href="{{ route('profile.edit') }}#notifikasi"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors"><i
                            class="fa-solid fa-bell w-4 text-center text-stone-400"></i>Notifikasi</a>
                    <div class="border-t border-stone-100 dark:border-stone-700 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <main
        class="flex-1 flex flex-col h-screen overflow-hidden relative bg-stone-50 dark:bg-stone-950 transition-colors duration-300">
        <!-- Top Header -->
        <header
            class="bg-white/80 dark:bg-stone-900/80 backdrop-blur-md border-b border-stone-200 dark:border-stone-800 p-4 flex justify-between items-center z-10 sticky top-0">
            <!-- Mobile Menu Button -->
            <div class="flex items-center md:hidden">
                <button onclick="toggleMobileMenu()"
                    class="text-stone-600 dark:text-stone-300 focus:outline-none mr-3">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <span class="font-bold text-lg text-stone-800 dark:text-white">
                    <i class="fa-solid fa-layer-group text-orange-500 mr-2"></i>SF Hub
                </span>
            </div>

            <!-- Desktop Title/Breadcrumb -->
            <div class="hidden md:block">
                <h2 id="page-title" class="text-lg font-bold text-stone-800 dark:text-white">
                    @yield('page-title', 'Dashboard')
                </h2>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-3">
                <!-- Time Simulation -->
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-sm text-stone-500 dark:text-stone-400">Waktu Simulasi:</span>
                    <input type="time" id="time-simulation"
                        class="bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm">
                </div>

                <!-- Dark Mode Toggle -->
                <button id="theme-toggle"
                    class="p-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-500 dark:text-stone-400 transition-colors">
                    <i id="theme-toggle-dark-icon" class="hidden fa-solid fa-moon"></i>
                    <i id="theme-toggle-light-icon" class="hidden fa-solid fa-sun text-yellow-400"></i>
                </button>

                <!-- Notifications Dropdown -->
                <div class="relative" id="notif-dropdown-wrap">
                    <button onclick="toggleNotifDropdown()" id="notif-btn"
                        class="relative p-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-500 dark:text-stone-400 transition-colors">
                        <i class="fa-solid fa-bell"></i>
                        <span id="notif-badge"
                            class="absolute top-0.5 right-0.5 min-w-[16px] h-4 px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full border border-white dark:border-stone-900 items-center justify-center hidden flex">0</span>
                    </button>
                    <div id="notif-panel"
                        class="hidden absolute right-0 top-full mt-2 w-80 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
                        <div
                            class="flex justify-between items-center px-4 py-3 border-b border-stone-100 dark:border-stone-800">
                            <h4 class="font-bold text-stone-800 dark:text-white text-sm">Notifikasi</h4>
                            <div class="flex gap-3">
                                <button onclick="quickMarkAllRead()"
                                    class="text-[11px] text-blue-600 dark:text-blue-400 hover:underline">Baca
                                    semua</button>
                                <a href="{{ route('profile.edit') }}#notifikasi"
                                    class="text-[11px] text-stone-400 hover:text-stone-600 dark:hover:text-stone-300">Kelola</a>
                            </div>
                        </div>
                        <div id="notif-dropdown-list" class="max-h-72 overflow-y-auto">
                            <p class="text-center py-6 text-stone-400 text-xs">Memuat...</p>
                        </div>
                        <a href="{{ route('profile.edit') }}#notifikasi"
                            class="block text-center py-2.5 text-xs font-medium text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 border-t border-stone-100 dark:border-stone-800 transition-colors">Lihat
                            Semua Notifikasi →</a>
                    </div>
                </div>

                <!-- Header User Avatar -->
                <div class="relative" id="header-user-wrap">
                    <button onclick="toggleHeaderUserMenu()"
                        class="flex items-center gap-2 p-1 pl-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors">
                        <div
                            class="w-8 h-8 rounded-full bg-gradient-to-tr from-orange-400 to-red-500 flex items-center justify-center text-white text-xs font-bold shadow">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] text-stone-400 hidden md:block"></i>
                    </button>
                    <div id="header-user-menu"
                        class="hidden absolute right-0 top-full mt-2 w-52 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl shadow-xl z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-stone-100 dark:border-stone-700">
                            <p class="text-sm font-bold text-stone-800 dark:text-white truncate">
                                {{ auth()->user()->name }}</p>
                            @if ((string) auth()->user()->plan === 'free')
                                <a href="{{ route('auth.onboarding-payment') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"><i
                                        class="fa-solid fa-crown w-4 text-center text-emerald-500"></i>Upgrade Plan</a>
                            @endif
                            <a href="{{ route('profile.edit') }}#pengaturan"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors"><i
                                    class="fa-solid fa-sliders w-4 text-center text-stone-400"></i>Pengaturan Akun</a>
                            <div class="border-t border-stone-100 dark:border-stone-700 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"><i
                                        class="fa-solid fa-right-from-bracket w-4 text-center"></i>Keluar dari
                                    Akun</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu"
            class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-50 hidden md:hidden transition-opacity"
            onclick="toggleMobileMenu()">
            <div class="absolute left-0 top-0 h-full w-72 max-w-[80vw] bg-white dark:bg-stone-900 shadow-xl p-4 flex flex-col transform transition-transform duration-300 ease-out"
                onclick="event.stopPropagation()">
                <div class="flex justify-between items-center mb-6">
                    <span class="font-bold text-lg text-stone-800 dark:text-white">Menu</span>
                    <button onclick="toggleMobileMenu()"
                        class="text-stone-500 hover:text-stone-800 dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <nav class="space-y-1">
                    <!-- Main Focus -->
                    <a href="{{ route('dashboard') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 {{ request()->routeIs('dashboard') || request()->path() === '/' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'text-stone-700 dark:text-stone-300' }}">
                        <i class="fa-solid fa-bolt mr-2"></i>Fokus Hari Ini</a>
                    <a href="{{ route('dashboard.smartcalendar') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 {{ request()->routeIs('calendar.*') ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400' : 'text-stone-700 dark:text-stone-300' }}">
                        <i class="fa-solid fa-calendar-day mr-2"></i>Kalender Cerdas</a>

                    <!-- Workspaces -->
                    <a href="{{ route('dashboard.creative.index') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-palette mr-2"></i>Studio Kreatif</a>
                    <a href="{{ route('dashboard.academic') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-graduation-cap mr-2"></i>HUB Akademik</a>
                    <a href="{{ route('dashboard.pkl') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-briefcase mr-2"></i>PKL / Pekerjaan</a>

                    <!-- Insights -->
                    <a href="{{ route('dashboard.productivity') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-chart-pie mr-2"></i>Analitik Produktivitas</a>
                    <a href="{{ route('dashboard.tracker') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-list-check mr-2"></i>Tracker Umum</a>

                    <!-- Finance & Assets -->
                    <div class="border-t border-stone-100 dark:border-stone-800 my-2"></div>
                    <a href="{{ route('dashboard.finance') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-wallet mr-2"></i>Manajemen Keuangan</a>
                    <a href="{{ route('dashboard.assets') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-landmark mr-2"></i>Manajemen Aset</a>
                    <a href="{{ route('dashboard.debts') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-hand-holding-usd mr-2"></i>Pelacak Utang</a>
                    <a href="{{ route('dashboard.investments') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-chart-line mr-2"></i>Portofolio Investasi</a>
                    <div class="border-t border-stone-100 dark:border-stone-800 my-2"></div>
                    <a href="{{ route('profile.edit') }}" onclick="toggleMobileMenu()"
                        class="w-full text-left p-3 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i class="fa-solid fa-user-circle mr-2"></i>Profil & Pengaturan</a>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full text-left p-3 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 dark:text-rose-400">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i>Keluar
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div id="content-area" class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar scroll-smooth">
            <!-- Dynamic Content Injected Here -->
            @yield('content')
        </div>
    </main>

    {{-- Stack untuk modal --}}
    @stack('modals')

    <!-- JavaScript Logic -->
    <script>
        // --- STATE & DATA ---
        let isSidebarCollapsed = false;

        // --- CORE FUNCTIONS ---
        function init() {
            // Theme Check
            if (localStorage.getItem("color-theme") === "dark" || (!("color-theme" in localStorage) && window
                    .matchMedia("(prefers-color-scheme: dark)").matches)) {
                document.documentElement.classList.add("dark");
                document.getElementById("theme-toggle-light-icon").classList.remove("hidden");
            } else {
                document.documentElement.classList.remove("dark");
                document.getElementById("theme-toggle-dark-icon").classList.remove("hidden");
            }

            // Set current time for time simulation
            const now = new Date();
            document.getElementById('time-simulation').value = now.toTimeString().slice(0, 5);
        }

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const icon = document.getElementById("sidebar-toggle-icon");

            isSidebarCollapsed = !isSidebarCollapsed;

            if (isSidebarCollapsed) {
                sidebar.classList.add("w-20", "sidebar-collapsed");
                sidebar.classList.remove("w-64");
                icon.classList.remove("fa-chevron-left");
                icon.classList.add("fa-chevron-right");
            } else {
                sidebar.classList.remove("w-20", "sidebar-collapsed");
                sidebar.classList.add("w-64");
                icon.classList.remove("fa-chevron-right");
                icon.classList.add("fa-chevron-left");
            }
        }

        function toggleMobileMenu() {
            const menu = document.getElementById("mobile-menu");
            menu.classList.toggle("hidden");
        }

        // --- MODAL FUNCTIONS ---
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // --- THEME TOGGLE ---
        document.getElementById('theme-toggle').addEventListener('click', function() {
            const darkIcon = document.getElementById("theme-toggle-dark-icon");
            const lightIcon = document.getElementById("theme-toggle-light-icon");

            darkIcon.classList.toggle("hidden");
            lightIcon.classList.toggle("hidden");

            if (localStorage.getItem("color-theme")) {
                if (localStorage.getItem("color-theme") === "light") {
                    document.documentElement.classList.add("dark");
                    localStorage.setItem("color-theme", "dark");
                } else {
                    document.documentElement.classList.remove("dark");
                    localStorage.setItem("color-theme", "light");
                }
            } else {
                if (document.documentElement.classList.contains("dark")) {
                    document.documentElement.classList.remove("dark");
                    localStorage.setItem("color-theme", "light");
                } else {
                    document.documentElement.classList.add("dark");
                    localStorage.setItem("color-theme", "dark");
                }
            }
        });

        // --- TIME SIMULATION ---
        document.getElementById('time-simulation')?.addEventListener('change', function() {
            // You can implement time simulation logic here
            console.log('Time simulation changed to:', this.value);
            // Update dashboard based on simulated time
        });

        // --- UTILITY FUNCTIONS ---
        function formatCurrency(amount) {
            return amount.toLocaleString("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString("id-ID", {
                day: "numeric",
                month: "long",
                year: "numeric"
            });
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 flex items-center ${
                type === 'success' ? 'bg-emerald-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-white' :
                'bg-blue-500 text-white'
            }`;

            notification.innerHTML = `
                <i class="fa-solid ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' :
                    'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // ── User dropdown toggles ────────────────────────────────────────
        function toggleSidebarUserMenu() {
            const m = document.getElementById('sidebar-user-menu');
            const h = document.getElementById('header-user-menu');
            if (m) {
                m.classList.toggle('hidden');
                h?.classList.add('hidden');
            }
        }

        function toggleHeaderUserMenu() {
            const m = document.getElementById('header-user-menu');
            const s = document.getElementById('sidebar-user-menu');
            if (m) {
                m.classList.toggle('hidden');
                s?.classList.add('hidden');
            }
        }
        document.addEventListener('click', e => {
            if (!e.target.closest('#sidebar-user-menu') && !e.target.closest('[onclick="toggleSidebarUserMenu()"]'))
                document.getElementById('sidebar-user-menu')?.classList.add('hidden');
            if (!e.target.closest('#header-user-menu') && !e.target.closest('#header-user-wrap'))
                document.getElementById('header-user-menu')?.classList.add('hidden');
        });

        // --- INIT ---
        window.addEventListener("DOMContentLoaded", init);

        // ── Notification Dropdown ─────────────────────────────────────────
        async function toggleNotifDropdown() {
            const panel = document.getElementById('notif-panel');
            const isHidden = panel.classList.toggle('hidden');
            if (!isHidden) {
                await loadNotifDropdown();
            }
        }

        async function loadNotifDropdown() {
            const list = document.getElementById('notif-dropdown-list');
            list.innerHTML = '<p class="text-center py-6 text-stone-400 text-xs">Memuat...</p>';
            try {
                const res = await fetch('/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                    }
                });
                const data = await res.json();
                if (!data.success || !data.notifications.length) {
                    list.innerHTML =
                        '<p class="text-center py-8 text-stone-400 text-sm"><i class="fa-solid fa-bell-slash block mb-2 text-2xl opacity-30"></i>Tidak ada notifikasi</p>';
                    return;
                }
                // Update badge
                const badge = document.getElementById('notif-badge');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
                const iconMap = {
                    system: 'fa-gear text-blue-500',
                    deadline: 'fa-clock text-rose-500',
                    reminder: 'fa-bell text-amber-500',
                    financial: 'fa-wallet text-emerald-500',
                    academic: 'fa-graduation-cap text-purple-500',
                    investment: 'fa-chart-line text-emerald-500',
                    budget: 'fa-triangle-exclamation text-rose-500'
                };
                list.innerHTML = data.notifications.slice(0, 8).map(n => `
                    <div id="ndrop-${n.id}" class="flex items-start gap-3 px-4 py-3 ${n.is_read ? 'opacity-60' : 'bg-orange-50/50 dark:bg-orange-900/10'} hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors border-b border-stone-50 dark:border-stone-800 last:border-0 cursor-pointer" onclick="handleNotifClick(${n.id}, '${n.action_url || ''}')">
                        <div class="w-8 h-8 rounded-full ${n.icon_bg} flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid ${n.icon} ${n.icon_color} text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-stone-800 dark:text-white truncate">${n.title}</p>
                            <p class="text-[11px] text-stone-400 mt-0.5 line-clamp-2">${n.message}</p>
                            <p class="text-[10px] text-stone-300 dark:text-stone-600 mt-1">${n.time_ago}</p>
                        </div>
                        ${!n.is_read ? '<div class="w-2 h-2 rounded-full bg-orange-500 flex-shrink-0 mt-1"></div>' : ''}
                    </div>`).join('');
            } catch (e) {
                list.innerHTML = '<p class="text-center py-6 text-rose-400 text-xs">Gagal memuat notifikasi</p>';
            }
        }

        async function handleNotifClick(id, url) {
            // Mark as read
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json'
                }
            });
            const el = document.getElementById('ndrop-' + id);
            if (el) el.classList.add('opacity-60');
            if (url) window.location.href = url;
        }

        async function quickMarkAllRead() {
            await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json'
                }
            });
            document.getElementById('notif-badge')?.classList.add('hidden');
            document.getElementById('notif-panel')?.classList.add('hidden');
        }

        // Load notif count on page load
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const res = await fetch('/notifications', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.unread_count > 0) {
                    const badge = document.getElementById('notif-badge');
                    if (badge) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.classList.remove('hidden');
                    }
                }
            } catch (e) {}
        });

        // Close notif panel on outside click
        document.addEventListener('click', e => {
            const wrap = document.getElementById('notif-dropdown-wrap');
            if (wrap && !wrap.contains(e.target)) {
                document.getElementById('notif-panel')?.classList.add('hidden');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
