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
        };
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <style>
        :root {
            --landing-bg: #fafaf9;
            --landing-surface: #ffffff;
            --landing-text: #1c1917;
            --landing-muted: #57534e;
            --landing-brand: #f97316;
            --landing-brand-2: #fb7185;
            --bg: #fafaf9;
            --surface: #ffffff;
            --text: #1c1917;
            --muted: #57534e;
            --brand: #f97316;
            --border: #e7e5e4;
        }

        .dark {
            --bg: #0c0a09;
            --surface: #1c1917;
            --text: #f5f5f4;
            --muted: #a8a29e;
            --brand: #f97316;
            --border: #292524;
        }

        body {
            font-family: "Inter", sans-serif;
            scroll-behavior: smooth;
            background: var(--landing-bg);
            color: var(--landing-text);
        }

        .landing-container {
            max-width: 80rem;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (min-width: 640px) {
            .landing-container {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .landing-container {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }

        .lp-container {
            max-width: 80rem;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (min-width: 640px) {
            .lp-container {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .lp-container {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }

        .landing-section {
            position: relative;
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        @media (min-width: 640px) {
            .landing-section {
                padding-top: 4rem;
                padding-bottom: 4rem;
            }
        }

        @media (min-width: 1024px) {
            .landing-section {
                padding-top: 5rem;
                padding-bottom: 5rem;
            }
        }

        .lp-section {
            position: relative;
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        @media (min-width: 640px) {
            .lp-section {
                padding-top: 4rem;
                padding-bottom: 4rem;
            }
        }

        @media (min-width: 1024px) {
            .lp-section {
                padding-top: 5rem;
                padding-bottom: 5rem;
            }
        }

        .landing-section-title {
            font-size: clamp(1.6rem, 4vw, 2.6rem);
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #1c1917;
        }

        .dark .landing-section-title {
            color: #f5f5f4;
        }

        .landing-section-subtitle {
            margin-top: .75rem;
            color: #78716c;
            font-size: .95rem;
            max-width: 42rem;
            line-height: 1.6;
        }

        .dark .landing-section-subtitle {
            color: #a8a29e;
        }

        .lp-title {
            font-size: clamp(1.8rem, 5vw, 3rem);
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .lp-subtitle {
            margin-top: 1rem;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .lp-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem 1rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #c2410c;
            background: #ffedd5;
            border: 1px solid #fed7aa;
        }

        .dark .lp-eyebrow {
            background: rgba(249, 115, 22, 0.15);
            border-color: rgba(249, 115, 22, 0.3);
        }

        .gradient-text {
            background: linear-gradient(135deg, #f97316 0%, #fb7185 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .8rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #c2410c;
            background: #ffedd5;
            border: 1px solid #fed7aa;
        }

        /* Demo Banner */
        .demo-banner {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 9999;
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.4);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @media (max-width: 640px) {
            .demo-banner {
                left: 1rem;
                right: 1rem;
                bottom: 0.5rem;
                justify-content: center;
            }
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.4);
            }

            50% {
                box-shadow: 0 10px 35px -5px rgba(249, 115, 22, 0.6);
            }
        }

        .demo-banner i {
            font-size: 1rem;
        }

        /* Responsive text utilities */
        .text-responsive-sm {
            font-size: clamp(0.875rem, 2vw, 1rem);
        }

        .text-responsive-base {
            font-size: clamp(1rem, 2.5vw, 1.125rem);
        }

        /* Stats card improvements */
        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1);
        }

        /* Step arrow connector */
        .step-connector {
            position: absolute;
            top: 50%;
            right: -2rem;
            transform: translateY(-50%);
            width: 4rem;
            height: 2px;
            background: linear-gradient(90deg, #f97316, #fb7185);
        }

        .step-connector::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid #fb7185;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
        }

        @media (max-width: 768px) {
            .step-connector {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>

<body id="top"
    class="bg-stone-50 text-stone-800 dark:bg-stone-950 dark:text-stone-100 transition-colors duration-300">
    @include('layouts.navbar')

    <main>
        @yield('content')
    </main>

    @include('layouts.footer')

    {{-- Demo Status Banner --}}
    <div class="demo-banner" title="Platform masih dalam tahap pengembangan dan pengujian">
        <i class="fa-solid fa-flask"></i>
        <span>Beta / Demo Mode</span>
    </div>

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
</body>

</html>
