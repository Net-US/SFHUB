<!-- resources/views/layouts/app-dashboard.blade.php -->
<!doctype html>
<html lang="id">

<head>
    <!-- Dark Mode Prevention - Blocking Script (MUST BE FIRST) -->
    <script>
        (function() {
            const theme = localStorage.getItem("color-theme");
            if (theme === "dark" || (!theme && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
                document.documentElement.classList.add("dark");
            }
        })();
    </script>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard | Student-Freelancer Hub')</title>

    <!-- Favicon -->
    @if (\App\Models\SiteSetting::getValue('site_favicon'))
        <link rel="icon" type="image/x-icon"
            href="{{ \App\Helpers\StorageHelper::getImageUrl(\App\Models\SiteSetting::getValue('site_favicon'), 'site') }}">
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

        /* === CUSTOM SCROLLBAR === */
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

        /* === SIDEBAR TRANSITIONS === */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-text {
            transition: opacity 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
            opacity: 1;
        }

        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .nav-header,
        .sidebar-collapsed .logo-text,
        .sidebar-collapsed .user-details {
            opacity: 0;
            width: 0;
            padding: 0;
            margin: 0;
            pointer-events: none;
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

        /* === ACTIVE STATE === */
        .nav-active {
            background-color: rgb(254 252 232) !important;
            color: rgb(180 83 9) !important;
            box-shadow: inset 3px 0 0 #f97316;
        }

        .dark .nav-active {
            background-color: rgb(120 53 15 / 0.2) !important;
            color: rgb(254 215 170) !important;
        }

        /* === MOBILE SIDEBAR === */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                transform: translateX(-100%);
                width: 85vw;
                max-width: 280px;
                z-index: 50;
            }

            #sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            }

            #sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 45;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            #sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .sidebar-toggle-desktop {
                display: none !important;
            }

            .sidebar-toggle-mobile {
                display: flex !important;
            }

            .nav-item {
                min-height: 48px;
                padding: 0.875rem 1rem;
            }

            #sidebar .logo-container {
                padding: 1rem;
                height: auto;
            }
        }

        @media (min-width: 769px) {
            .sidebar-toggle-mobile {
                display: none !important;
            }

            .sidebar-toggle-desktop {
                display: flex !important;
            }

            /* Tooltip saat collapsed (desktop only) */
            .sidebar-collapsed .nav-item {
                position: relative;
            }

            .sidebar-collapsed .nav-item::after {
                content: attr(data-tooltip);
                position: absolute;
                left: 100%;
                top: 50%;
                transform: translateY(-50%);
                background: #1f1f1f;
                color: white;
                padding: 0.4rem 0.75rem;
                border-radius: 0.5rem;
                font-size: 0.75rem;
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transition: all 0.2s ease;
                z-index: 100;
                margin-left: 0.5rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .sidebar-collapsed .nav-item:hover::after {
                opacity: 1;
                visibility: visible;
            }
        }

        /* === MODAL STYLES === */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        /* === PRIORITY COLORS === */
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

        /* === ANIMATIONS === */
        .hamburger-icon {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .hamburger-icon.active {
            transform: rotate(90deg);
        }

        /* === SMOOTH TRANSITIONS === */
        .nav-item,
        .nav-text,
        .logo-text,
        .user-details {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>

    @stack('styles')
</head>

<body
    class="bg-stone-50 text-stone-800 dark:bg-stone-950 dark:text-stone-100 h-screen flex overflow-hidden transition-colors duration-300">

    <!-- === SIDEBAR OVERLAY (MOBILE) === -->
    <div id="sidebar-overlay" onclick="closeMobileSidebar()"></div>

    <!-- === SIDEBAR NAVIGATION === -->
    <aside id="sidebar"
        class="bg-white dark:bg-stone-900 border-r border-stone-200 dark:border-stone-800
               md:flex flex-col z-30 shadow-sm w-64 sidebar-transition relative
               md:translate-x-0 fixed md:static top-0 left-0 h-screen">

        <!-- Toggle Button Desktop -->
        <button onclick="toggleSidebar()"
            class="sidebar-toggle-desktop absolute -right-3 top-20 bg-white dark:bg-stone-800
                   border border-stone-200 dark:border-stone-700 rounded-full w-6 h-6
                   flex items-center justify-center text-stone-500 hover:text-orange-500
                   shadow-sm z-30 transform transition-transform hover:scale-110 focus:outline-none">
            <i id="sidebar-toggle-icon" class="fa-solid fa-chevron-left text-xs"></i>
        </button>

        <!-- Header Sidebar -->
        <div
            class="logo-container p-4 border-b border-stone-100 dark:border-stone-800 flex items-center justify-between h-16 md:h-20">
            <div class="flex items-center overflow-hidden">
                <i class="fa-solid fa-layer-group text-orange-500 text-2xl mr-3 flex-shrink-0"></i>
                <div class="logo-text">
                    <h1 class="text-lg font-bold text-stone-900 dark:text-white tracking-tight">
                        Student<span class="text-orange-500">Hub</span>
                    </h1>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400 hidden md:block">Workspace</p>
                </div>
            </div>

            <!-- Close Button Mobile Only -->
            <button onclick="closeMobileSidebar()"
                class="md:hidden p-2 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-500">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Navigation Content -->
        <nav class="flex-1 overflow-y-auto py-2 px-2 custom-scrollbar">
            <!-- Group: FOKUS UTAMA -->
            <div class="mb-4">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">
                    Fokus Utama
                </p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" id="nav-dashboard"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all
                                   {{ request()->routeIs('dashboard') || request()->path() === '/' ? 'nav-active' : '' }}"
                            data-tooltip="Fokus Hari Ini">
                            <i class="fa-solid fa-bolt w-5 text-center mr-3 flex-shrink-0"></i>
                            <span class="nav-text">Fokus Hari Ini</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.smartcalendar', ['month' => date('n'), 'year' => date('Y')]) }}"
                            id="nav-smartcalendar"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all
                                   {{ request()->routeIs('calendar.*') ? 'nav-active' : '' }}"
                            data-tooltip="Kalender Pintar">
                            <i class="fa-solid fa-calendar-day w-5 text-center mr-3 flex-shrink-0"></i>
                            <span class="nav-text">Kalender Pintar</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Group: WORKSPACES -->
            <div class="mb-4">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">
                    Workspace
                </p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard.creative.index') }}" id="nav-creative"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('creative.*') ? 'nav-active' : '' }}"
                            data-tooltip="Studio Kreatif">
                            <i
                                class="fa-solid fa-palette w-5 text-center mr-3 flex-shrink-0 group-hover:text-pink-500 transition-colors"></i>
                            <span class="nav-text">Studio Kreatif</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.academic') }}" id="nav-academic"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('academic.*') ? 'nav-active' : '' }}"
                            data-tooltip="Pusat Akademik">
                            <i
                                class="fa-solid fa-graduation-cap w-5 text-center mr-3 flex-shrink-0 group-hover:text-blue-500 transition-colors"></i>
                            <span class="nav-text">Pusat Akademik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.pkl') }}" id="nav-pkl"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('pkl.*') ? 'nav-active' : '' }}"
                            data-tooltip="PKL / Kerja">
                            <i
                                class="fa-solid fa-briefcase w-5 text-center mr-3 flex-shrink-0 group-hover:text-emerald-500 transition-colors"></i>
                            <span class="nav-text">PKL / Kerja</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Group: INSIGHTS -->
            <div class="mb-4">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">
                    Wawasan
                </p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard.productivity') }}" id="nav-productivity"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('productivity.*') ? 'nav-active' : '' }}"
                            data-tooltip="Analitik">
                            <i
                                class="fa-solid fa-chart-pie w-5 text-center mr-3 flex-shrink-0 group-hover:text-indigo-500 transition-colors"></i>
                            <span class="nav-text">Analitik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.tracker') }}" id="nav-tracker"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('tracker.*') ? 'nav-active' : '' }}"
                            data-tooltip="Pelacak Umum">
                            <i
                                class="fa-solid fa-list-check w-5 text-center mr-3 flex-shrink-0 group-hover:text-rose-500 transition-colors"></i>
                            <span class="nav-text">Pelacak Umum</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Group: FINANCE & ASSETS -->
            <div class="mb-4">
                <p class="nav-header text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 px-3">
                    Keuangan & Aset
                </p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard.finance') }}" id="nav-finance"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('finance.*') ? 'nav-active' : '' }}"
                            data-tooltip="Manajer Keuangan">
                            <i
                                class="fa-solid fa-wallet w-5 text-center mr-3 flex-shrink-0 group-hover:text-amber-500 transition-colors"></i>
                            <span class="nav-text">Manajer Keuangan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.assets') }}" id="nav-assets"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('assets.*') ? 'nav-active' : '' }}"
                            data-tooltip="Manajemen Aset">
                            <i
                                class="fa-solid fa-landmark w-5 text-center mr-3 flex-shrink-0 group-hover:text-blue-500 transition-colors"></i>
                            <span class="nav-text">Manajemen Aset</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.debts') }}" id="nav-debts"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('debts.*') ? 'nav-active' : '' }}"
                            data-tooltip="Pelacak Utang">
                            <i
                                class="fa-solid fa-hand-holding-usd w-5 text-center mr-3 flex-shrink-0 group-hover:text-rose-500 transition-colors"></i>
                            <span class="nav-text">Pelacak Utang</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.investments') }}" id="nav-investments"
                            class="nav-item w-full flex items-center p-3 text-sm font-medium rounded-xl
                                   text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800
                                   hover:text-stone-900 dark:hover:text-white transition-all group
                                   {{ request()->routeIs('investments.*') ? 'nav-active' : '' }}"
                            data-tooltip="Portofolio Investasi">
                            <i
                                class="fa-solid fa-chart-line w-5 text-center mr-3 flex-shrink-0 group-hover:text-emerald-500 transition-colors"></i>
                            <span class="nav-text">Portofolio Investasi</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- User Profile Section -->
        <div class="p-3 border-t border-stone-100 dark:border-stone-800 relative">
            <button onclick="toggleSidebarUserMenu()"
                class="w-full flex items-center nav-item p-2 rounded-xl hover:bg-stone-50
                       dark:hover:bg-stone-800 cursor-pointer transition-colors group">
                <div
                    class="w-8 h-8 rounded-full bg-gradient-to-tr from-orange-400 to-red-500
                            flex items-center justify-center text-white text-xs font-bold shadow-md flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="ml-3 overflow-hidden nav-text user-details flex-1 text-left">
                    <p class="text-sm font-medium text-stone-700 dark:text-stone-200 truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">
                        {{ ucfirst(auth()->user()->plan ?? 'Free') }} Plan
                    </p>
                </div>
                <i
                    class="fa-solid fa-ellipsis-vertical text-stone-400 nav-text text-xs group-hover:text-stone-600 flex-shrink-0"></i>
            </button>

            <!-- Sidebar User Menu Dropdown -->
            <div id="sidebar-user-menu"
                class="hidden absolute bottom-full left-3 right-3 mb-2
                       bg-white dark:bg-stone-800 border border-stone-200
                       dark:border-stone-700 rounded-2xl shadow-xl z-50 overflow-hidden">
                <div
                    class="px-4 py-3 border-b border-stone-100 dark:border-stone-700 bg-gradient-to-br from-orange-50 to-red-50 dark:from-stone-800 dark:to-stone-800">
                    <p class="text-sm font-bold text-stone-800 dark:text-white truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-stone-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                        <i class="fa-solid fa-user-circle w-4 text-center text-stone-400"></i>
                        Edit Profil
                    </a>
                    <a href="{{ route('profile.edit') }}#pengaturan"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                        <i class="fa-solid fa-sliders w-4 text-center text-stone-400"></i>
                        Pengaturan
                    </a>
                    <a href="{{ route('profile.edit') }}#notifikasi"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                        <i class="fa-solid fa-bell w-4 text-center text-stone-400"></i>
                        Notifikasi
                    </a>
                    <div class="border-t border-stone-100 dark:border-stone-700 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- === MAIN CONTENT === -->
    <main
        class="flex-1 flex flex-col h-screen overflow-hidden relative bg-stone-50 dark:bg-stone-950 transition-colors duration-300">

        <!-- Top Header -->
        <header
            class="bg-white/80 dark:bg-stone-900/80 backdrop-blur-md border-b border-stone-200 dark:border-stone-800 p-4 flex justify-between items-center z-10 sticky top-0">
            <!-- Mobile Menu Button -->
            <div class="flex items-center md:hidden">
                <button onclick="openMobileSidebar()"
                    class="text-stone-600 dark:text-stone-300 focus:outline-none mr-3 p-2 rounded-lg
                           hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors"
                    aria-label="Buka menu navigasi">
                    <i class="fa-solid fa-bars text-xl hamburger-icon"></i>
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
                            class="block text-center py-2.5 text-xs font-medium text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 border-t border-stone-100 dark:border-stone-800 transition-colors">
                            Lihat Semua Notifikasi →
                        </a>
                    </div>
                </div>

                <!-- Header User Avatar -->
                <div class="relative" id="header-user-wrap">
                    <button onclick="toggleHeaderUserMenu()"
                        class="flex items-center gap-2 p-1 pl-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors">
                        <div
                            class="w-8 h-8 rounded-full bg-gradient-to-tr from-orange-400 to-red-500
                                    flex items-center justify-center text-white text-xs font-bold shadow">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] text-stone-400 hidden md:block"></i>
                    </button>
                    <div id="header-user-menu"
                        class="hidden absolute right-0 top-full mt-2 w-52 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl shadow-xl z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-stone-100 dark:border-stone-700">
                            <p class="text-sm font-bold text-stone-800 dark:text-white truncate">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-stone-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                                <i class="fa-solid fa-user-circle w-4 text-center text-stone-400"></i>
                                Profil Saya
                            </a>
                            <a href="{{ route('profile.edit') }}#pengaturan"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                                <i class="fa-solid fa-sliders w-4 text-center text-stone-400"></i>
                                Pengaturan
                            </a>
                            <div class="border-t border-stone-100 dark:border-stone-700 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <div id="content-area" class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar scroll-smooth">
            @yield('content')
        </div>
    </main>

    {{-- Stack untuk modal --}}
    @stack('modals')

    <!-- === JAVASCRIPT LOGIC === -->
    <script>
        // === STATE & DATA ===
        let isSidebarCollapsed = false;
        let isMobileMenuOpen = false;

        // === CORE FUNCTIONS ===
        function init() {
            // Theme Check
            if (localStorage.getItem("color-theme") === "dark" ||
                (!("color-theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
                document.documentElement.classList.add("dark");
                document.getElementById("theme-toggle-light-icon").classList.remove("hidden");
            } else {
                document.documentElement.classList.remove("dark");
                document.getElementById("theme-toggle-dark-icon").classList.remove("hidden");
            }

            // Set current time for time simulation
            const now = new Date();
            document.getElementById('time-simulation').value = now.toTimeString().slice(0, 5);

            // Detect screen size
            checkScreenSize();
            window.addEventListener('resize', checkScreenSize);

            // Restore sidebar state
            restoreSidebarState();
        }

        // === SCREEN SIZE DETECTION ===
        function checkScreenSize() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (window.innerWidth < 768) {
                // Mobile: reset to closed state
                sidebar.classList.remove('sidebar-collapsed', 'w-20');
                sidebar.classList.add('w-64');
                sidebar.style.transform = 'translateX(-100%)';
                if (overlay) overlay.classList.remove('active');
                isMobileMenuOpen = false;
                isSidebarCollapsed = false;
            } else {
                // Desktop: ensure sidebar visible
                sidebar.style.transform = '';
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // === SIDEBAR TOGGLE (DESKTOP) ===
        function toggleSidebar() {
            if (window.innerWidth < 768) {
                // On mobile, toggle open/close
                isMobileMenuOpen ? closeMobileSidebar() : openMobileSidebar();
                return;
            }

            const sidebar = document.getElementById("sidebar");
            const icon = document.getElementById("sidebar-toggle-icon");

            isSidebarCollapsed = !isSidebarCollapsed;

            if (isSidebarCollapsed) {
                sidebar.classList.add("w-20", "sidebar-collapsed");
                sidebar.classList.remove("w-64");
                icon.classList.remove("fa-chevron-left");
                icon.classList.add("fa-chevron-right");
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                sidebar.classList.remove("w-20", "sidebar-collapsed");
                sidebar.classList.add("w-64");
                icon.classList.remove("fa-chevron-right");
                icon.classList.add("fa-chevron-left");
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }

        // === MOBILE SIDEBAR OPEN ===
        function openMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.add('mobile-open');
            sidebar.style.transform = 'translateX(0)';
            if (overlay) overlay.classList.add('active');
            isMobileMenuOpen = true;

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        // === MOBILE SIDEBAR CLOSE ===
        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.remove('mobile-open');
            sidebar.style.transform = 'translateX(-100%)';
            if (overlay) overlay.classList.remove('active');
            isMobileMenuOpen = false;

            // Restore body scroll
            document.body.style.overflow = '';
        }

        // === RESTORE SIDEBAR STATE ===
        function restoreSidebarState() {
            if (window.innerWidth >= 768) {
                const saved = localStorage.getItem('sidebarCollapsed');
                if (saved === 'true') {
                    isSidebarCollapsed = true;
                    const sidebar = document.getElementById('sidebar');
                    const icon = document.getElementById('sidebar-toggle-icon');
                    sidebar.classList.add('sidebar-collapsed', 'w-20');
                    sidebar.classList.remove('w-64');
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                }
            }
        }

        // === THEME TOGGLE ===
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

        // === TIME SIMULATION ===
        document.getElementById('time-simulation')?.addEventListener('change', function() {
            console.log('Time simulation changed to:', this.value);
        });

        // === UTILITY FUNCTIONS ===
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

        // === USER DROPDOWN TOGGLES ===
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

        // Close dropdowns on outside click
        document.addEventListener('click', e => {
            if (!e.target.closest('#sidebar-user-menu') && !e.target.closest('[onclick="toggleSidebarUserMenu()"]'))
                document.getElementById('sidebar-user-menu')?.classList.add('hidden');
            if (!e.target.closest('#header-user-menu') && !e.target.closest('#header-user-wrap'))
                document.getElementById('header-user-menu')?.classList.add('hidden');
        });

        // === NOTIFICATION DROPDOWN ===
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
                    </div>
                `).join('');
            } catch (e) {
                list.innerHTML = '<p class="text-center py-6 text-rose-400 text-xs">Gagal memuat notifikasi</p>';
            }
        }

        async function handleNotifClick(id, url) {
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

        // === KEYBOARD NAVIGATION (ACCESSIBILITY) ===
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isMobileMenuOpen) {
                closeMobileSidebar();
            }
        });

        // === INIT ===
        window.addEventListener("DOMContentLoaded", init);
    </script>

    @stack('scripts')
</body>

</html>
