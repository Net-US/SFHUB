@extends('layouts.app-landing')

@section('title', 'About Us — StudentHub')

@push('styles')
    <style>
        .about-hero {
            padding-top: calc(var(--nav-h, 68px) + 4rem);
            padding-bottom: 4rem;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse 70% 60% at 80% 20%, rgba(249, 115, 22, .08) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 20% 80%, rgba(16, 185, 129, .06) 0%, transparent 55%),
                var(--bg);
        }

        .value-card {
            border-radius: 20px;
            padding: 1.75rem;
            border: 1px solid var(--border);
            background: var(--surface);
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s ease;
            position: relative;
            overflow: hidden;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--brand), #fb7185);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .3s ease;
        }

        .value-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -12px rgba(249, 115, 22, .12);
        }

        .value-card:hover::before {
            transform: scaleX(1);
        }

        .dark .value-card {
            background: rgba(255, 255, 255, .04);
        }

        .team-card {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: var(--surface);
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
        }

        .team-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, .12);
        }

        .dark .team-card {
            background: rgba(255, 255, 255, .04);
        }

        .timeline-item {
            position: relative;
            padding-left: 2.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: .5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--brand);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, .2);
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 5.5px;
            top: 1.2rem;
            width: 1px;
            height: calc(100% + 1.5rem);
            background: var(--border);
        }

        .timeline-item:last-child::after {
            display: none;
        }
    </style>
@endpush

@section('content')

    {{-- ── ABOUT HERO ──────────────────────────────────────────────── --}}
    <section class="about-hero">
        <div class="lp-container">
            <div class="max-w-3xl">
                <span class="lp-eyebrow reveal">
                    <i class="fa-solid fa-circle-info"></i> About StudentHub
                </span>
                <h1 class="lp-title mt-5 reveal reveal-delay-1">
                    Platform produktivitas untuk mahasiswa yang juga punya
                    <span class="gradient-text">mimpi besar</span> di luar kelas.
                </h1>
                <p class="lp-subtitle max-w-2xl reveal reveal-delay-2">
                    Kami membangun SFHUB agar mahasiswa bisa mengelola kuliah, proyek freelance, PKL, dan finansial pribadi
                    dalam satu sistem yang ringan namun powerful.
                </p>

                <div class="flex flex-wrap gap-3 mt-8 reveal reveal-delay-3">
                    @foreach ([['2.4K+', 'Pengguna Aktif'], ['35+', 'Fitur'], ['4.9', 'Rating'], ['2023', 'Berdiri']] as [$val, $lbl])
                        <div class="px-5 py-3 rounded-2xl border border-[var(--border)] bg-[var(--surface)]">
                            <p class="font-display font-800 text-xl text-orange-500">{{ $val }}</p>
                            <p class="text-xs text-[var(--muted)]">{{ $lbl }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ── WHY SFHUB ────────────────────────────────────────────────── --}}
    <section class="lp-section bg-[var(--surface)]">
        <div class="lp-container">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
                <div class="reveal">
                    <div
                        class="relative rounded-3xl overflow-hidden aspect-[4/3] bg-gradient-to-br from-orange-100 to-rose-100 dark:from-orange-950/30 dark:to-rose-950/30">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80"
                            alt="SFHUB Team"
                            class="w-full h-full object-cover mix-blend-multiply dark:mix-blend-luminosity opacity-90">

                        {{-- Overlay stat --}}
                        <div
                            class="absolute bottom-5 left-5 bg-white dark:bg-stone-900 rounded-2xl px-5 py-3.5 shadow-xl border border-[var(--border)]">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-950/40 flex items-center justify-center text-orange-600">
                                    <i class="fa-solid fa-users text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-display font-800 text-lg text-[var(--text)]">2,400+</p>
                                    <p class="text-xs text-[var(--muted)]">Mahasiswa aktif</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 reveal reveal-delay-2">
                    <span class="lp-eyebrow"><i class="fa-solid fa-lightbulb"></i> Kenapa SFHUB Ada?</span>
                    <h2 class="font-display font-800 text-3xl text-[var(--text)] leading-tight">
                        Dua dunia sekaligus —<br>kami bantu kamu
                        <span class="gradient-text">tetap seimbang</span>
                    </h2>
                    <p class="text-[var(--muted)] leading-relaxed">
                        Banyak mahasiswa Indonesia menghadapi dua dunia sekaligus: akademik dan karir awal. Namun kebanyakan
                        tools hanya fokus pada salah satunya. SFHUB hadir untuk menjembatani keduanya agar tetap sehat,
                        produktif, dan terukur.
                    </p>
                    <p class="text-[var(--muted)] leading-relaxed">
                        Fokus kami bukan sekadar to-do list, tapi sistem yang membantu kamu mengambil keputusan harian lebih
                        baik berdasarkan prioritas, waktu, dan kondisi finansial.
                    </p>

                    <div class="space-y-3 pt-2">
                        @foreach (['Terintegrasi antara akademik & karir', 'Dashboard adaptif sesuai kebutuhanmu', 'Analitik produktivitas berbasis data nyata', 'Dibangun dari masukan mahasiswa Indonesia'] as $item)
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-950/40 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-check text-orange-600 text-xs"></i>
                                </div>
                                <span class="text-sm text-[var(--text)]">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── CORE VALUES ──────────────────────────────────────────────── --}}
    <section class="lp-section bg-[var(--bg)]">
        <div class="lp-container">
            <div class="text-center mb-12">
                <span class="lp-eyebrow reveal"><i class="fa-solid fa-diamond"></i> Core Values</span>
                <h2 class="lp-title mt-4 reveal reveal-delay-1">Prinsip yang kami <span class="gradient-text">pegang
                        teguh</span></h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ([['fa-lightbulb', 'bg-amber-100 dark:bg-amber-950/40 text-amber-600', 'Clarity First', 'Setiap fitur dirancang agar keputusan harian menjadi lebih jelas dan mudah.'], ['fa-user-graduate', 'bg-blue-100 dark:bg-blue-950/40 text-blue-600', 'Student-Centric', 'Dibangun dari ritme hidup nyata mahasiswa Indonesia, bukan asumsi.'], ['fa-bolt', 'bg-orange-100 dark:bg-orange-950/40 text-orange-600', 'Actionable', 'Data bukan hiasan — setiap angka mendorong aksi nyata yang relevan.'], ['fa-seedling', 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600', 'Sustainable', 'Produktif tanpa burnout. Konsistensi jangka panjang di atas sprint sesaat.']] as $idx => [$icon, $cls, $title, $desc])
                    <div class="value-card reveal reveal-delay-{{ $idx + 1 }}">
                        <div class="w-12 h-12 rounded-xl {{ $cls }} flex items-center justify-center mb-4 text-lg">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <h3 class="font-display font-700 text-base text-[var(--text)] mb-2">{{ $title }}</h3>
                        <p class="text-sm text-[var(--muted)] leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── JOURNEY / TIMELINE ───────────────────────────────────────── --}}
    <section class="lp-section bg-[var(--surface)]">
        <div class="lp-container">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-start">
                <div>
                    <span class="lp-eyebrow reveal"><i class="fa-solid fa-timeline"></i> Perjalanan Kami</span>
                    <h2 class="font-display font-800 text-3xl text-[var(--text)] mt-4 mb-10 reveal reveal-delay-1">
                        Dari ide sederhana <span class="gradient-text">ke platform nyata</span>
                    </h2>

                    <div class="space-y-8">
                        @foreach ([['2023', 'Ide Lahir', 'Berawal dari frustrasi kami sendiri sebagai mahasiswa yang juga aktif freelance.'], ['Maret 2023', 'Beta Launch', 'Platform pertama diluncurkan ke 50 tester awal dari komunitas mahasiswa.'], ['Agst 2023', '1K Pengguna', 'Milestone pertama: 1,000 mahasiswa aktif dalam 5 bulan pertama.'], ['2024', 'Fitur Finansial', 'Modul keuangan & laporan produktivitas diluncurkan berdasarkan feedback.'], ['2025', '2K+ Pengguna', 'Komunitas berkembang ke 2,400+ mahasiswa dari 34 universitas di Indonesia.']] as $idx => [$year, $title, $desc])
                            <div class="timeline-item reveal reveal-delay-{{ ($idx % 3) + 1 }}">
                                <p class="text-xs font-bold text-orange-500 mb-1">{{ $year }}</p>
                                <h4 class="font-display font-700 text-[var(--text)] mb-1">{{ $title }}</h4>
                                <p class="text-sm text-[var(--muted)]">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="reveal reveal-delay-2">
                    <div
                        class="rounded-3xl bg-gradient-to-br from-orange-50 to-rose-50 dark:from-orange-950/20 dark:to-rose-950/20
                            border border-orange-100 dark:border-orange-900/30 p-8">
                        <h3 class="font-display font-800 text-xl text-[var(--text)] mb-6">Statistik Platform</h3>
                        @foreach ([['2,400+', 'Pengguna Aktif', 'fa-users', 'text-blue-600 bg-blue-50 dark:bg-blue-950/40'], ['34', 'Universitas', 'fa-building-columns', 'text-orange-600 bg-orange-50 dark:bg-orange-950/40'], ['4.9/5', 'Rating Rata-rata', 'fa-star', 'text-amber-600 bg-amber-50 dark:bg-amber-950/40'], ['98%', 'Satisfaction Rate', 'fa-heart', 'text-rose-600 bg-rose-50 dark:bg-rose-950/40']] as [$val, $lbl, $icon, $cls])
                            <div class="flex items-center gap-4 py-3.5 border-b border-[var(--border)] last:border-0">
                                <div
                                    class="w-10 h-10 rounded-xl {{ $cls }} flex items-center justify-center shrink-0">
                                    <i class="fa-solid {{ $icon }} text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-display font-800 text-xl text-[var(--text)]">{{ $val }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ $lbl }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── CTA ──────────────────────────────────────────────────────── --}}
    <section class="lp-section bg-[var(--bg)]">
        <div class="lp-container">
            <div
                class="text-center rounded-3xl bg-gradient-to-br from-stone-900 to-stone-800 dark:from-stone-950 dark:to-stone-900 p-12 relative overflow-hidden">
                <div class="absolute inset-0 opacity-5"
                    style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 28px 28px;">
                </div>
                <div class="relative z-10">
                    <span class="lp-eyebrow bg-white/10 border-white/20 text-orange-300 mb-5 reveal">
                        <i class="fa-solid fa-rocket"></i> Bergabung
                    </span>
                    <h2 class="font-display font-800 text-3xl md:text-4xl text-white mt-4 mb-4 reveal reveal-delay-1">
                        Jadilah bagian dari perjalanan ini
                    </h2>
                    <p class="text-white/50 text-sm max-w-md mx-auto mb-8 reveal reveal-delay-2">
                        Bergabunglah dengan 2,400+ mahasiswa yang sudah membuktikan bahwa kuliah dan karir bisa berjalan
                        beriringan.
                    </p>
                    <div class="flex flex-wrap gap-3 justify-center reveal reveal-delay-3">
                        @guest
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm transition-all shadow-xl shadow-orange-500/25 hover:-translate-y-0.5">
                                Mulai Gratis Sekarang <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        @endguest
                        <a href="{{ route('contact') }}"
                            class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full border border-white/20 text-white/80 hover:text-white font-semibold text-sm transition-all hover:border-white/40">
                            Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
