<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- SEO Meta Tags -->
    @php
        $globalSeo = \App\Models\GlobalSeo::first();
        $currentPage = request()->route()->getName() ?? 'home';
        $pageSeo = \App\Models\SeoSetting::getForPage($currentPage);
        $metaTags = \App\Models\MetaTag::active()->get();

        $title = $pageSeo?->title ?? ($globalSeo?->default_title ?? 'Student-Freelancer Hub | Kelola Kuliah & Karirmu');
        $description =
            $pageSeo?->description ??
            ($globalSeo?->default_description ?? 'Platform terpadu untuk mahasiswa dan freelancer Indonesia');
        $keywords =
            $pageSeo?->keywords ??
            ($globalSeo?->default_keywords ?? 'mahasiswa, freelancer, manajemen tugas, keuangan, kalender');
        $author = $globalSeo?->author ?? 'SFHUB Team';
        $robots = $globalSeo?->robots ?? 'index, follow';
        $canonicalUrl = $pageSeo?->canonical_url ?? url()->current();
        $ogTitle = $pageSeo?->og_title ?? $title;
        $ogDescription = $pageSeo?->og_description ?? $description;
        $ogImage = $pageSeo?->og_image ?? asset('images/og-default.jpg');
    @endphp

    <title>{{ $title }}</title>

    <!-- Basic Meta -->
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="author" content="{{ $author }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $globalSeo?->default_title ?? 'Student-Freelancer Hub' }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Custom Meta Tags -->
    @foreach ($metaTags as $tag)
        @if ($tag->type === 'name')
            <meta name="{{ $tag->name }}" content="{{ $tag->content }}">
        @elseif($tag->type === 'property')
            <meta property="{{ $tag->name }}" content="{{ $tag->content }}">
        @elseif($tag->type === 'http-equiv')
            <meta http-equiv="{{ $tag->name }}" content="{{ $tag->content }}">
        @endif
    @endforeach

    <!-- Analytics -->
    @if ($globalSeo?->analytics_active && $globalSeo?->google_analytics_id)
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $globalSeo->google_analytics_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ $globalSeo->google_analytics_id }}');
        </script>
    @endif

    @if ($globalSeo?->analytics_active && $globalSeo?->facebook_pixel_id)
        <!-- Facebook Pixel -->
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $globalSeo->facebook_pixel_id }}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={{ $globalSeo->facebook_pixel_id }}&ev=PageView&noscript=1" /></noscript>
    @endif

    <!-- Sitemap -->
    @if (file_exists(public_path('sitemap.xml')))
        <link rel="sitemap" type="application/xml" href="{{ url('sitemap.xml') }}">
    @endif

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
        };
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <style>
        body {
            font-family: "Inter", sans-serif;
            scroll-behavior: smooth;
        }

        .hero-gradient {
            background: linear-gradient(rgba(255, 247, 237, 0.95), rgba(245, 245, 244, 0.9)), url("https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2071&auto=format&fit=crop");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .dark .hero-gradient {
            background: linear-gradient(rgba(28, 25, 23, 0.9), rgba(12, 10, 9, 0.95)), url("https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2071&auto=format&fit=crop");
        }

        .form-glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .dark .form-glass {
            background: rgba(28, 25, 23, 0.6);
            border: 1px solid rgba(68, 64, 60, 0.5);
        }

        .floating-card {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .hover-lift {
            transition: transform 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-8px);
        }
    </style>

    @stack('styles')
</head>

<body class="bg-stone-50 text-stone-800 dark:bg-stone-950 dark:text-stone-100 transition-colors duration-300">
    @include('layouts.navbar')

    <main>
        @yield('content')
    </main>

    @include('layouts.footer')

    @stack('scripts')

    <script>
        // Theme Toggle Script
        const themeToggleBtn = document.getElementById("theme-toggle");
        const darkIcon = document.getElementById("theme-toggle-dark-icon");
        const lightIcon = document.getElementById("theme-toggle-light-icon");

        if (localStorage.getItem("color-theme") === "dark" || (!("color-theme" in localStorage) && window.matchMedia(
                "(prefers-color-scheme: dark)").matches)) {
            document.documentElement.classList.add("dark");
            if (lightIcon) lightIcon.classList.remove("hidden");
        } else {
            document.documentElement.classList.remove("dark");
            if (darkIcon) darkIcon.classList.remove("hidden");
        }

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener("click", function() {
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
        }

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener("click", function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute("href"));
                if (target) {
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                    });
                }
            });
        });
    </script>
    // Tambahkan script ini di bagian scripts pada layout
    <script>
        function previewAvatar(event) {
            const input = event.target;
            const preview = document.getElementById('avatar-preview');
            const placeholder = document.getElementById('avatar-placeholder');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // Untuk form di halaman register terpisah
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.getElementById('avatar');
            if (avatarInput) {
                avatarInput.addEventListener('change', previewAvatar);
            }
        });
    </script>
</body>

</html>
