@php
    $footerContactEmail = \App\Models\SystemSetting::get('contact_email', 'support@sfhub.id');
    $footerContactPhone = \App\Models\SystemSetting::get('contact_phone', '+62 812-0000-0000');
    $footerSiteName = \App\Models\SystemSetting::get('site_name', 'SFHUB');
@endphp

<style>
    :root {
        --surface: #ffffff;
        --border: #e7e5e4;
        --muted: #78716c;
    }

    .dark {
        --surface: #1c1917;
        --border: #292524;
        --muted: #a8a29e;
    }

    .ticker-wrap {
        overflow: hidden;
        white-space: nowrap;
    }

    .ticker-inner {
        display: inline-block;
        animation: ticker 30s linear infinite;
    }

    @keyframes ticker {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .font-display {
        font-family: 'Inter', sans-serif;
    }

    .font-800 {
        font-weight: 800;
    }

    .font-700 {
        font-weight: 700;
    }
</style>

<!-- Partner ticker -->
<div class="py-8 bg-[var(--surface)] border-y border-[var(--border)] overflow-hidden">
    <div class="ticker-wrap">
        <div class="ticker-inner">
            @php $brands = ['Android','Envato','Microsoft','Netflix','Google','LinkedIn','Android','Envato','Microsoft','Netflix','Google','LinkedIn']; @endphp
            @foreach ($brands as $b)
                <span
                    class="inline-flex items-center gap-2 mx-10 text-base font-bold text-[var(--muted)] opacity-50 hover:opacity-80 transition-opacity cursor-default">
                    <i
                        class="fa-brands fa-{{ strtolower($b === 'Android' ? 'android' : ($b === 'Envato' ? 'envato' : ($b === 'Microsoft' ? 'microsoft' : ($b === 'Netflix' ? 'square' : ($b === 'Google' ? 'google' : 'linkedin'))))) }}"></i>
                    {{ $b }}
                </span>
            @endforeach
        </div>
    </div>
</div>

<!-- Main footer -->
<footer class="bg-[#0f0e0c] dark:bg-[#080705] text-white">
    <!-- CTA bar -->
    <div class="border-b border-white/10">
        <div class="lp-container py-12 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <p class="font-display font-800 text-2xl md:text-3xl">Siap mulai produktif?</p>
                <p class="text-white/50 text-sm mt-1">Bergabung dengan ribuan mahasiswa yang sudah memakai
                    {{ $footerSiteName }}.</p>
            </div>
            @guest
                <a href="{{ route('register') }}"
                    class="shrink-0 px-8 py-3.5 rounded-full bg-orange-500 hover:bg-orange-400 text-white font-bold text-sm
                      transition-all shadow-xl shadow-orange-500/20 hover:-translate-y-0.5 active:translate-y-0">
                    Mulai Gratis Sekarang <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            @endguest
        </div>
    </div>

    <div class="lp-container py-14">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">

            <!-- Brand col -->
            <div class="lg:col-span-2 space-y-5">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    <span
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-white text-sm"></i>
                    </span>
                    <span class="font-display font-800 text-xl">Student<span class="text-orange-400">Hub</span></span>
                </a>
                <p class="text-white/50 text-sm leading-relaxed max-w-sm">
                    Platform all-in-one untuk mahasiswa Indonesia yang ingin mengelola kuliah, proyek freelance, dan
                    keuangan dalam satu alur kerja yang rapi.
                </p>
                <div class="flex gap-2.5">
                    @foreach ([['fab fa-instagram', '#'], ['fab fa-twitter', '#'], ['fab fa-linkedin-in', '#'], ['fab fa-youtube', '#']] as [$ico, $href])
                        <a href="{{ $href }}"
                            class="w-9 h-9 rounded-xl bg-white/10 hover:bg-orange-500 flex items-center justify-center
                               text-white/60 hover:text-white transition-all duration-200">
                            <i class="{{ $ico }} text-sm"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <h5 class="font-display font-700 text-sm text-white/80 uppercase tracking-widest mb-5">Company</h5>
                <ul class="space-y-3">
                    @foreach ([['About Us', route('about')], ['Contact', route('contact')], ['Blog', route('blog.index')], ['Fitur', route('home') . '#fitur']] as [$lbl, $href])
                        <li><a href="{{ $href }}"
                                class="text-sm text-white/45 hover:text-white transition-colors">{{ $lbl }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h5 class="font-display font-700 text-sm text-white/80 uppercase tracking-widest mb-5">Community</h5>
                <ul class="space-y-3">
                    @foreach ([['Cara Kerja', route('home') . '#cara-kerja'], ['Testimoni', route('home') . '#testimonials'], ['Feedback', route('home') . '#feedback'], ['Daftar', route('register')]] as [$lbl, $href])
                        <li><a href="{{ $href }}"
                                class="text-sm text-white/45 hover:text-white transition-colors">{{ $lbl }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h5 class="font-display font-700 text-sm text-white/80 uppercase tracking-widest mb-5">Contact</h5>
                <ul class="space-y-3 text-sm text-white/45">
                    <li><span class="text-white/30">Email </span><a href="mailto:{{ $footerContactEmail }}"
                            class="hover:text-white transition-colors">{{ $footerContactEmail }}</a></li>
                    <li><span class="text-white/30">WA </span><a
                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $footerContactPhone) }}"
                            target="_blank" class="hover:text-white transition-colors">{{ $footerContactPhone }}</a>
                    </li>
                    <li><span class="text-white/30">Jam </span>09:00–18:00 WIB</li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-white/30 text-xs">© {{ date('Y') }} {{ $footerSiteName }}. All rights reserved. <span
                    class="text-orange-400/60 ml-2">Beta Version</span></p>
            <a href="#top" class="text-white/30 hover:text-white text-xs transition-colors">
                Kembali ke atas <i class="fa-solid fa-arrow-up ml-1"></i>
            </a>
        </div>
    </div>
</footer>
