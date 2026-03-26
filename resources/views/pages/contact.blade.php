@php
    $contactEmail = \App\Models\SystemSetting::get('contact_email', 'support@sfhub.id');
    $contactPhone = \App\Models\SystemSetting::get('contact_phone', '+62 812-0000-0000');
    $siteName = \App\Models\SystemSetting::get('site_name', 'SFHUB');
@endphp

@extends('layouts.app-landing')

@section('title', 'Hubungi Kami — ' . $siteName)

@push('styles')
    <style>
        .contact-hero {
            padding-top: calc(var(--nav-h, 68px) + 3.5rem);
            padding-bottom: 3.5rem;
            background:
                radial-gradient(ellipse 60% 50% at 80% 20%, rgba(249, 115, 22, .08) 0%, transparent 60%),
                var(--bg);
        }

        .info-card {
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            background: var(--surface);
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s, border-color .25s;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, .08);
            border-color: rgba(249, 115, 22, .3);
        }

        .dark .info-card {
            background: rgba(255, 255, 255, .04);
        }

        .form-input {
            width: 100%;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: var(--bg);
            padding: .75rem 1rem;
            font-size: .875rem;
            color: var(--text);
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
            background: var(--surface);
        }

        .form-input::placeholder {
            color: var(--muted);
            opacity: .6;
        }

        .dark .form-input {
            background: rgba(255, 255, 255, .05);
        }

        .dark .form-input:focus {
            background: rgba(255, 255, 255, .08);
        }
    </style>
@endpush

@section('content')

    {{-- ── HERO ──────────────────────────────────────────────────────── --}}
    <section class="contact-hero">
        <div class="lp-container">
            <span class="lp-eyebrow reveal"><i class="fa-solid fa-envelope"></i> Hubungi Kami</span>
            <h1 class="lp-title mt-5 max-w-2xl reveal reveal-delay-1">
                Mari ngobrol tentang<br>
                <span class="gradient-text">kebutuhanmu</span>
            </h1>
            <p class="lp-subtitle max-w-xl reveal reveal-delay-2">
                Punya pertanyaan, feedback, atau ide kolaborasi? Tim {{ $siteName }} siap membantu kamu.
            </p>
        </div>
    </section>

    {{-- ── MAIN ─────────────────────────────────────────────────────── --}}
    <section class="lp-section bg-[var(--bg)]">
        <div class="lp-container">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

                {{-- Contact Info --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Email --}}
                    <div class="info-card reveal">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 flex items-center justify-center shrink-0 text-lg">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-[var(--muted)] font-bold mb-1">Email</p>
                                <p class="font-semibold text-[var(--text)] text-sm">{{ $contactEmail }}</p>
                                <a href="mailto:{{ $contactEmail }}"
                                    class="mt-1.5 inline-flex items-center gap-1.5 text-xs text-orange-500 hover:text-orange-600 font-medium transition-colors">
                                    <i class="fa-solid fa-arrow-right text-[.6rem]"></i> Kirim email
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- WhatsApp --}}
                    <div class="info-card reveal reveal-delay-1">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center shrink-0 text-lg">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-[var(--muted)] font-bold mb-1">WhatsApp</p>
                                <p class="font-semibold text-[var(--text)] text-sm">{{ $contactPhone }}</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactPhone) }}" target="_blank"
                                    class="mt-1.5 inline-flex items-center gap-1.5 text-xs text-emerald-500 hover:text-emerald-600 font-medium transition-colors">
                                    <i class="fa-solid fa-arrow-right text-[.6rem]"></i> Chat sekarang
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Hours --}}
                    <div class="info-card reveal reveal-delay-2">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center shrink-0 text-lg">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-[var(--muted)] font-bold mb-1">Jam
                                    Operasional</p>
                                <p class="font-semibold text-[var(--text)] text-sm">Senin – Jumat</p>
                                <p class="text-xs text-[var(--muted)] mt-0.5">09:00 – 18:00 WIB</p>
                            </div>
                        </div>
                    </div>

                    {{-- FAQ teaser --}}
                    <div
                        class="info-card bg-gradient-to-br from-orange-50 to-rose-50 dark:from-orange-950/20 dark:to-rose-950/20 border-orange-100 dark:border-orange-900/30 reveal reveal-delay-3">
                        <p class="font-display font-700 text-[var(--text)] mb-1.5">Pertanyaan Umum?</p>
                        <p class="text-sm text-[var(--muted)] mb-3">Cek halaman FAQ kami sebelum mengirim pesan — mungkin
                            jawabannya sudah ada.</p>
                        <a href="#"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors">
                            Lihat FAQ <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                {{-- Contact Form --}}
                <div class="lg:col-span-3 reveal reveal-delay-1">
                    <div
                        class="rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-8 shadow-sm dark:shadow-none">
                        <h2 class="font-display font-800 text-2xl text-[var(--text)] mb-6">Kirim Pesan</h2>

                        @if (session('success'))
                            <div
                                class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 text-sm flex items-center gap-2">
                                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div
                                class="mb-5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 px-4 py-3 text-sm flex items-center gap-2">
                                <i class="fa-solid fa-circle-xmark"></i> {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-[var(--muted)] mb-1.5 uppercase tracking-wider">Nama</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                        placeholder="Nama lengkapmu" class="form-input">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-[var(--muted)] mb-1.5 uppercase tracking-wider">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                        placeholder="email@contoh.com" class="form-input">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-[var(--muted)] mb-1.5 uppercase tracking-wider">Topik</label>
                                <select name="subject" class="form-input appearance-none">
                                    <option value="">Pilih topik pesan...</option>
                                    @foreach (['Pertanyaan Fitur', 'Laporan Bug', 'Saran Pengembangan', 'Kolaborasi & Partnership', 'Pertanyaan Paket & Donasi', 'Lainnya'] as $topic)
                                        <option value="{{ $topic }}"
                                            {{ old('subject') === $topic ? 'selected' : '' }}>{{ $topic }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-[var(--muted)] mb-1.5 uppercase tracking-wider">Pesan</label>
                                <textarea name="message" rows="6" required placeholder="Ceritakan keperluanmu di sini..."
                                    class="form-input resize-none">{{ old('message') }}</textarea>
                            </div>
                            <button type="submit"
                                class="w-full py-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm
                                   transition-all shadow-lg shadow-orange-500/25 hover:-translate-y-0.5 active:translate-y-0">
                                Kirim Pesan <i class="fa-solid fa-paper-plane ml-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
