<nav
    class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-stone-200 dark:bg-stone-900/80 dark:border-stone-800 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
                <a href="{{ route('home') }}"
                    class="text-2xl font-black text-stone-900 dark:text-white tracking-tighter transition-colors">
                    <i class="fa-solid fa-layer-group text-orange-500 mr-2"></i>Student<span
                        class="text-orange-500">Hub</span>
                </a>
            </div>
            <div class="hidden md:flex items-center space-x-8 text-sm font-medium text-stone-600 dark:text-stone-300">
                <a href="#fitur" class="hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Fitur</a>
                <a href="#bagaimana" class="hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Cara
                    Kerja</a>
                <a href="#testimoni"
                    class="hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Testimoni</a>
                <a href="#harga" class="hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Harga</a>

                <button id="theme-toggle"
                    class="p-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors focus:outline-none">
                    <i id="theme-toggle-dark-icon" class="hidden fa-solid fa-moon text-stone-600"></i>
                    <i id="theme-toggle-light-icon" class="hidden fa-solid fa-sun text-yellow-400"></i>
                </button>

                @guest
                    <a href="{{ route('login') }}"
                        class="hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Masuk</a>
                    <a href="{{ route('register') }}"
                        class="bg-stone-900 text-white px-5 py-2 rounded-full hover:bg-stone-800 dark:bg-orange-500 dark:hover:bg-orange-600 transition-all shadow-sm">Daftar
                        Gratis</a>
                @else
                    <div class="relative">
                        <button id="user-menu"
                            class="flex items-center gap-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-tr from-orange-400 to-red-500 flex items-center justify-center text-white text-xs font-bold shadow-md">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-sm">{{ Auth::user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        <div id="dropdown-menu"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-stone-800 rounded-xl shadow-lg border border-stone-200 dark:border-stone-700 hidden z-50">
                            <div class="p-3 border-b border-stone-100 dark:border-stone-700">
                                <p class="font-medium text-stone-800 dark:text-white">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-stone-500 dark:text-stone-400">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('dashboard') }}"
                                class="block px-4 py-2 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-700 transition-colors">
                                <i class="fa-solid fa-gauge mr-2"></i>Dashboard
                            </a>
                            <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-700 transition-colors">
                                <i class="fa-solid fa-user mr-2"></i>Profil Saya
                            </a>
                            <div class="border-t border-stone-100 dark:border-stone-700"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-rose-600 dark:text-rose-400 hover:bg-stone-100 dark:hover:bg-stone-700 transition-colors">
                                    <i class="fa-solid fa-right-from-bracket mr-2"></i>Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
            <div class="md:hidden text-stone-900 dark:text-white">
                <button id="mobile-menu-btn" class="text-xl">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="md:hidden hidden bg-white dark:bg-stone-900 border-b border-stone-200 dark:border-stone-800">
        <div class="px-4 py-3 space-y-3">
            <a href="#fitur"
                class="block py-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Fitur</a>
            <a href="#bagaimana"
                class="block py-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Cara Kerja</a>
            <a href="#testimoni"
                class="block py-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Testimoni</a>
            <a href="#harga"
                class="block py-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Harga</a>

            <div class="pt-3 border-t border-stone-200 dark:border-stone-700">
                @guest
                    <a href="{{ route('login') }}"
                        class="block py-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Masuk</a>
                    <a href="{{ route('register') }}"
                        class="block py-2 bg-stone-900 text-white text-center rounded-lg hover:bg-stone-800 dark:bg-orange-500 dark:hover:bg-orange-600 transition-all">Daftar
                        Gratis</a>
                @else
                    <a href="{{ route('dashboard') }}"
                        class="block py-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Dashboard</a>
                    <a href="{{ route('profile.edit') }}"
                        class="block py-2 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">Profil
                        Saya</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full text-left py-2 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition-colors">
                            Keluar
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });

    // User dropdown menu
    const userMenuBtn = document.getElementById('user-menu');
    const dropdownMenu = document.getElementById('dropdown-menu');

    if (userMenuBtn && dropdownMenu) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            dropdownMenu.classList.add('hidden');
        });

        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
</script>
