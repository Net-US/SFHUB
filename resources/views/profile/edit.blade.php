{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app-dashboard')

@section('title', 'Profil & Pengaturan | StudentHub')
@section('page-title', 'Profil')

@push('styles')
    <style>
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        .fade-up {
            animation: fadeUp .38s ease-out both;
        }

        .slide-in {
            animation: slideIn .32s ease-out both;
        }

        /* ── Banner ─────────────────── */
        .profile-banner {
            height: 148px;
            background: linear-gradient(135deg, #f97316 0%, #ef4444 45%, #b91c1c 100%);
            position: relative;
            overflow: hidden;
        }

        .profile-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 15% 55%, rgba(255, 255, 255, .18) 0%, transparent 55%),
                radial-gradient(circle at 85% 15%, rgba(255, 255, 255, .1) 0%, transparent 45%);
        }

        .profile-banner::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 36px;
            background: white;
            clip-path: ellipse(56% 100% at 50% 100%);
        }

        .dark .profile-banner::after {
            background: #1c1917;
        }

        /* ── Avatar ─────────────────── */
        .avatar-wrap {
            box-shadow: 0 0 0 3.5px white, 0 0 0 5.5px #f97316;
        }

        .dark .avatar-wrap {
            box-shadow: 0 0 0 3.5px #1c1917, 0 0 0 5.5px #f97316;
        }

        /* ── Stat row ───────────────── */
        .stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .55rem .8rem;
            border-radius: .8rem;
            background: #f5f5f4;
            transition: background .18s;
        }

        .dark .stat-row {
            background: #292524;
        }

        .stat-row:hover {
            background: #e7e5e4;
        }

        .dark .stat-row:hover {
            background: #3d3733;
        }

        /* ── Nav pill ───────────────── */
        .nav-pill {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .52rem .85rem;
            border-radius: .8rem;
            font-size: .8125rem;
            font-weight: 500;
            color: #78716c;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            transition: all .18s;
        }

        .nav-pill:hover {
            background: #f5f5f4;
            color: #292524;
        }

        .dark .nav-pill:hover {
            background: #292524;
            color: #fafaf9;
        }

        .nav-pill.active {
            background: #fff7ed;
            color: #ea580c;
            font-weight: 600;
        }

        .dark .nav-pill.active {
            background: rgba(249, 115, 22, .13);
            color: #fb923c;
        }

        .nav-pill .nav-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #d6d3d1;
            flex-shrink: 0;
            transition: all .18s;
        }

        .nav-pill.active .nav-dot {
            background: #f97316;
            box-shadow: 0 0 0 2.5px rgba(249, 115, 22, .22);
        }

        /* ── Form ───────────────────── */
        .fi {
            width: 100%;
            border: 1.5px solid #e7e5e4;
            border-radius: .8rem;
            padding: .6rem .95rem;
            font-size: .875rem;
            background: #fafaf9;
            color: #1c1917;
            outline: none;
            transition: border .18s, box-shadow .18s, background .18s;
        }

        .fi:focus {
            border-color: #f97316;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .11);
        }

        .dark .fi {
            background: #292524;
            border-color: #44403c;
            color: #fafaf9;
        }

        .dark .fi:focus {
            border-color: #f97316;
            background: #1c1917;
        }

        /* Fix for select dropdown styling */
        select.fi {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .dark select.fi {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23d1d5db' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        .fi-label {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            color: #a8a29e;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-bottom: .35rem;
        }

        /* ── Btn ────────────────────── */
        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.2rem;
            border-radius: .8rem;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #f97316, #ef4444);
            color: #fff;
            font-size: .875rem;
            font-weight: 600;
            transition: opacity .18s, transform .14s;
        }

        .btn-save:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        /* ── Toggle ─────────────────── */
        .tog {
            position: relative;
            display: inline-flex;
            align-items: center;
            width: 42px;
            height: 23px;
            border-radius: 12px;
            cursor: pointer;
            transition: background .2s;
            flex-shrink: 0;
        }

        .tog.on {
            background: #f97316;
        }

        .tog.off {
            background: #d6d3d1;
        }

        .dark .tog.off {
            background: #57534e;
        }

        .tog-thumb {
            position: absolute;
            left: 2.5px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
            transition: transform .2s;
        }

        .tog.on .tog-thumb {
            transform: translateX(19px);
        }

        /* ── Theme card ─────────────── */
        .th-card {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .45rem;
            padding: .8rem .4rem;
            border: 2px solid #e7e5e4;
            border-radius: .9rem;
            cursor: pointer;
            transition: all .18s;
        }

        .dark .th-card {
            border-color: #44403c;
        }

        .th-card:hover {
            border-color: #f97316;
        }

        .th-card.sel {
            border-color: #f97316;
            background: #fff7ed;
        }

        .dark .th-card.sel {
            background: rgba(249, 115, 22, .1);
        }

        /* ── Password bars ──────────── */
        .pw-seg {
            height: 3px;
            border-radius: 2px;
            flex: 1;
            background: #e7e5e4;
            transition: background .25s;
        }

        .dark .pw-seg {
            background: #44403c;
        }

        /* modal */
        .modal-open {
            overflow: hidden;
        }

        /* section divider label */
        .sec-lbl {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #a8a29e;
            padding: .25rem .25rem .3rem;
        }
    </style>
@endpush

@section('content')
    @php $prefs = auth()->user()->preferences ?? []; @endphp

    <div class="fade-up max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-5 items-start">

            {{-- ════════════ LEFT SIDEBAR ════════════ --}}
            <div class="w-full lg:w-[268px] flex-shrink-0 space-y-3.5 slide-in lg:sticky lg:top-6">

                {{-- Profile card --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">

                    {{-- Banner --}}
                    <div class="profile-banner">
                        <button
                            class="absolute top-3 right-3 z-10 flex items-center gap-1.5 px-2.5 py-1.5 bg-black/25 hover:bg-black/35 backdrop-blur-sm text-white text-[11px] font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-image text-[9px]"></i> Ganti Cover
                        </button>
                    </div>

                    <div class="px-5 pb-5">
                        {{-- Avatar --}}
                        <div class="-mt-9 mb-3 relative w-fit group">
                            @if (auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt=""
                                    class="w-[72px] h-[72px] rounded-[14px] avatar-wrap object-cover">
                            @else
                                <div
                                    class="w-[72px] h-[72px] rounded-[14px] avatar-wrap bg-gradient-to-tr from-orange-400 to-red-500 flex items-center justify-center text-white text-2xl font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                            @endif
                            <label for="avatar-input"
                                class="absolute inset-0 rounded-[14px] bg-black/45 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer flex items-center justify-center">
                                <i class="fa-solid fa-camera text-white text-sm"></i>
                            </label>
                            <input type="file" id="avatar-input" accept="image/*" class="hidden"
                                onchange="uploadAvatar(this)">
                            <span
                                class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-stone-900 ring-1 ring-emerald-400"></span>
                        </div>

                        <h2 class="font-bold text-stone-900 dark:text-white text-[15px] leading-snug">
                            {{ auth()->user()->name }}</h2>
                        <p class="text-stone-400 text-xs mt-0.5">
                            {{ auth()->user()->occupation ?? 'Mahasiswa / Freelancer' }}</p>

                        @if (auth()->user()->location)
                            <p class="flex items-center gap-1 text-stone-400 text-[11px] mt-1">
                                <i class="fa-solid fa-location-dot text-[9px]"></i> {{ auth()->user()->location }}
                            </p>
                        @endif

                        <span
                            class="inline-flex items-center gap-1.5 mt-2.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400">
                            <i class="fa-solid fa-crown text-[9px]"></i>
                            {{ ucfirst(auth()->user()->plan ?? 'Free') }} Plan
                        </span>

                        {{-- Stats --}}
                        <div class="mt-4 space-y-1.5">
                            <a href="{{ route('dashboard.finance') }}" class="stat-row block">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-wallet text-orange-400 text-xs w-3.5 text-center"></i>
                                    <span class="text-[11px] text-stone-500 dark:text-stone-400">Akun Keuangan</span>
                                </div>
                                <span
                                    class="text-sm font-bold text-stone-800 dark:text-white">{{ $stats['finance_accounts'] }}</span>
                            </a>
                            <a href="{{ route('dashboard.assets') }}" class="stat-row block">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-landmark text-blue-400 text-xs w-3.5 text-center"></i>
                                    <span class="text-[11px] text-stone-500 dark:text-stone-400">Aset Fisik</span>
                                </div>
                                <span class="text-sm font-bold text-stone-800 dark:text-white">{{ $stats['assets'] }}</span>
                            </a>
                            <a href="{{ route('dashboard.investments') }}" class="stat-row block">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-chart-line text-emerald-400 text-xs w-3.5 text-center"></i>
                                    <span class="text-[11px] text-stone-500 dark:text-stone-400">Instrumen Investasi</span>
                                </div>
                                <span
                                    class="text-sm font-bold text-stone-800 dark:text-white">{{ $stats['investments'] }}</span>
                            </a>
                            <div class="stat-row">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-coins text-amber-400 text-xs w-3.5 text-center"></i>
                                    <span class="text-[11px] text-stone-500 dark:text-stone-400">Total Saldo</span>
                                </div>
                                <span
                                    class="text-sm font-bold {{ $stats['total_net_worth'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    Rp {{ number_format($stats['total_net_worth'] / 1_000_000, 1) }}jt
                                </span>
                            </div>
                        </div>

                        @if (auth()->user()->bio)
                            <p
                                class="mt-3.5 text-[11px] text-stone-400 leading-relaxed border-t border-stone-100 dark:border-stone-800 pt-3.5">
                                {{ Str::limit(auth()->user()->bio, 90) }}</p>
                        @endif
                    </div>
                </div>

                {{-- Navigation --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-3">
                    <p class="sec-lbl px-2">Pengaturan Akun</p>
                    <div class="space-y-0.5">
                        @foreach ([['profil', 'fa-id-card', 'Profil Saya', null], ['keamanan', 'fa-shield-halved', 'Keamanan', null], ['pengaturan', 'fa-sliders', 'Preferensi', null], ['notifikasi', 'fa-bell', 'Notifikasi', $unreadCount > 0 ? $unreadCount : null]] as [$id, $icon, $label, $badge])
                            <button onclick="switchTab('{{ $id }}')" id="tab-btn-{{ $id }}"
                                class="nav-pill {{ $id === 'profil' ? 'active' : '' }}">
                                <span class="nav-dot"></span>
                                <i class="fa-solid {{ $icon }} text-[13px] w-4 text-center"></i>
                                <span class="flex-1 text-left">{{ $label }}</span>
                                @if ($badge)
                                    <span
                                        class="px-1.5 py-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full">{{ $badge }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-2 pt-2 border-t border-stone-100 dark:border-stone-800">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="nav-pill w-full text-rose-500 hover:!text-rose-600 hover:!bg-rose-50 dark:hover:!bg-rose-900/20">
                                <span class="nav-dot !bg-rose-200"></span>
                                <i class="fa-solid fa-right-from-bracket text-[13px] w-4 text-center"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>{{-- end left sidebar --}}

            {{-- ════════════ RIGHT CONTENT ════════════ --}}
            <div class="flex-1 min-w-0 space-y-4">

                {{-- Flash --}}
                @if (session('success'))
                    <div
                        class="flex items-center gap-3 px-4 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm fade-up">
                        <i class="fa-solid fa-circle-check text-emerald-500 flex-shrink-0"></i>{{ session('success') }}
                    </div>
                @endif

                {{-- ══ TAB PROFIL ══════════════════════════════════════════════════ --}}
                <div id="tab-profil" class="tab-pane space-y-4 fade-up">

                    {{-- Personal Info --}}
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        {{-- Card header --}}
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                            <div
                                class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-user text-orange-500 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-stone-800 dark:text-white text-sm">Informasi Pribadi</h3>
                                <p class="text-[11px] text-stone-400">Nama, kontak, dan bio Anda</p>
                            </div>
                        </div>

                        <form id="form-profil" enctype="multipart/form-data" class="p-6">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="fi-label">Nama Lengkap <span class="text-rose-400">*</span></label>
                                    <input type="text" name="name" value="{{ auth()->user()->name }}" required
                                        class="fi" placeholder="Nama kamu">
                                </div>
                                <div>
                                    <label class="fi-label">Email Address <span class="text-rose-400">*</span></label>
                                    <input type="email" name="email" value="{{ auth()->user()->email }}" required
                                        class="fi" placeholder="email@domain.com">
                                </div>
                                <div>
                                    <label class="fi-label">No. HP / WhatsApp</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs pointer-events-none"></i>
                                        <input type="text" name="phone" value="{{ auth()->user()->phone }}"
                                            class="fi pl-9" placeholder="+62 8xx xxxx xxxx">
                                    </div>
                                </div>
                                <div>
                                    <label class="fi-label">Pekerjaan / Status</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-briefcase absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs pointer-events-none"></i>
                                        <input type="text" name="occupation" value="{{ auth()->user()->occupation }}"
                                            class="fi pl-9" placeholder="Mahasiswa, Freelancer, dll">
                                    </div>
                                </div>
                                <div>
                                    <label class="fi-label">Lokasi</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-location-dot absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs pointer-events-none"></i>
                                        <input type="text" name="location" value="{{ auth()->user()->location }}"
                                            class="fi pl-9" placeholder="Jakarta, Indonesia">
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="fi-label">Bio <span class="text-stone-400 normal-case font-normal">(maks
                                            500 karakter)</span></label>
                                    <textarea name="bio" rows="3" class="fi resize-none" placeholder="Ceritakan sedikit tentang dirimu...">{{ auth()->user()->bio }}</textarea>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between mt-6 pt-5 border-t border-stone-100 dark:border-stone-800 flex-wrap gap-3">
                                <p class="text-[11px] text-stone-400 flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-stone-300"></i>
                                    Bergabung {{ auth()->user()->created_at->isoFormat('D MMMM YYYY') }}
                                </p>
                                <button type="button" onclick="submitProfil()" class="btn-save">
                                    <i class="fa-solid fa-floppy-disk text-sm"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Danger Zone --}}
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-rose-200 dark:border-rose-900/50 shadow-sm">
                        <div class="flex items-center justify-between px-6 py-4 flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-rose-600 dark:text-rose-400 text-sm">Hapus Akun</h3>
                                    <p class="text-[11px] text-stone-400">Permanen dan tidak bisa dibatalkan</p>
                                </div>
                            </div>
                            <button onclick="openModal('modal-delete-account')"
                                class="flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-xl transition-colors">
                                <i class="fa-solid fa-trash-can text-xs"></i> Hapus Akun
                            </button>
                        </div>
                    </div>

                </div>

                {{-- ══ TAB KEAMANAN ══════════════════════════════════════════════════ --}}
                <div id="tab-keamanan" class="tab-pane hidden space-y-4 fade-up">

                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                            <div
                                class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-lock text-blue-500 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-stone-800 dark:text-white text-sm">Ubah Password</h3>
                                <p class="text-[11px] text-stone-400">Gunakan kombinasi huruf besar, angka, dan simbol</p>
                            </div>
                        </div>
                        <form id="form-password" class="p-6">
                            @csrf
                            <div class="max-w-sm space-y-4">
                                <div>
                                    <label class="fi-label">Password Lama <span class="text-rose-400">*</span></label>
                                    <div class="relative">
                                        <input type="password" name="current_password" id="pw-old" required
                                            class="fi pr-10" placeholder="••••••••">
                                        <button type="button" onclick="togglePw('pw-old')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors"><i
                                                class="fa-solid fa-eye text-sm"></i></button>
                                    </div>
                                </div>
                                <div>
                                    <label class="fi-label">Password Baru <span class="text-rose-400">*</span></label>
                                    <div class="relative">
                                        <input type="password" name="password" id="pw-new" required
                                            oninput="checkPwStrength(this.value)" class="fi pr-10"
                                            placeholder="Min 8 karakter">
                                        <button type="button" onclick="togglePw('pw-new')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors"><i
                                                class="fa-solid fa-eye text-sm"></i></button>
                                    </div>
                                    <div class="mt-2">
                                        <div class="flex gap-1 mb-1.5">
                                            <div id="pw-bar-1" class="pw-seg"></div>
                                            <div id="pw-bar-2" class="pw-seg"></div>
                                            <div id="pw-bar-3" class="pw-seg"></div>
                                            <div id="pw-bar-4" class="pw-seg"></div>
                                        </div>
                                        <p id="pw-lbl" class="text-[11px] text-stone-400">Masukkan password baru</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="fi-label">Konfirmasi Password <span
                                            class="text-rose-400">*</span></label>
                                    <input type="password" name="password_confirmation" required class="fi"
                                        placeholder="Ulangi password baru">
                                </div>
                                <button type="button" onclick="submitPassword()" class="btn-save mt-1">
                                    <i class="fa-solid fa-key text-sm"></i> Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Account info --}}
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                            <div
                                class="w-9 h-9 rounded-xl bg-stone-100 dark:bg-stone-800 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-circle-info text-stone-400 text-sm"></i>
                            </div>
                            <h3 class="font-bold text-stone-800 dark:text-white text-sm">Informasi Akun</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach ([['Bergabung', auth()->user()->created_at->isoFormat('D MMM YYYY'), 'fa-calendar-days', 'text-orange-400'], ['Plan', ucfirst(auth()->user()->plan ?? 'free'), 'fa-crown', 'text-amber-400'], ['Role', ucfirst(auth()->user()->role ?? 'user'), 'fa-user-shield', 'text-blue-400']] as [$lbl, $val, $ic, $clr])
                                    <div class="flex items-center gap-3 p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-white dark:bg-stone-700 flex items-center justify-center shadow-sm flex-shrink-0">
                                            <i class="fa-solid {{ $ic }} {{ $clr }} text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-stone-400 uppercase font-semibold tracking-wide">
                                                {{ $lbl }}</p>
                                            <p class="font-bold text-stone-800 dark:text-white text-sm">
                                                {{ $val }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ══ TAB PENGATURAN ══════════════════════════════════════════════════ --}}
                <div id="tab-pengaturan" class="tab-pane hidden space-y-4 fade-up">

                    {{-- Tema & Bahasa --}}
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                            <div
                                class="w-9 h-9 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-palette text-violet-500 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-stone-800 dark:text-white text-sm">Tampilan & Bahasa</h3>
                                <p class="text-[11px] text-stone-400">Tema, bahasa, dan mata uang</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            {{-- Theme --}}
                            <div>
                                <p class="fi-label mb-3">Tema Tampilan</p>
                                <div class="flex gap-3">
                                    @foreach (['light' => ['fa-sun', 'Terang', 'bg-amber-100', 'text-amber-500'], 'dark' => ['fa-moon', 'Gelap', 'bg-slate-800', 'text-slate-300'], 'system' => ['fa-circle-half-stroke', 'Sistem', 'bg-gradient-to-br from-amber-100 to-slate-800', 'text-stone-500']] as $v => $c)
                                        <button type="button" onclick="setThemePref('{{ $v }}')"
                                            id="theme-opt-{{ $v }}"
                                            class="th-card {{ ($prefs['theme'] ?? 'system') === $v ? 'sel' : '' }}">
                                            <div
                                                class="w-10 h-10 rounded-xl {{ $c[2] }} flex items-center justify-center">
                                                <i class="fa-solid {{ $c[0] }} {{ $c[3] }} text-lg"></i>
                                            </div>
                                            <span
                                                class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">{{ $c[1] }}</span>
                                            <span
                                                class="w-2 h-2 rounded-full {{ ($prefs['theme'] ?? 'system') === $v ? 'bg-orange-500' : 'bg-stone-200 dark:bg-stone-700' }}"></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="fi-label">Bahasa</label>
                                    <select id="pref-language" onchange="savePref()" class="fi">
                                        <option value="id"
                                            {{ ($prefs['language'] ?? 'id') === 'id' ? 'selected' : '' }}>🇮🇩 Indonesia
                                        </option>
                                        <option value="en"
                                            {{ ($prefs['language'] ?? 'id') === 'en' ? 'selected' : '' }}>🇬🇧 English
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="fi-label">Mata Uang</label>
                                    <select id="pref-currency" onchange="savePref()" class="fi">
                                        <option value="IDR"
                                            {{ ($prefs['currency'] ?? 'IDR') === 'IDR' ? 'selected' : '' }}>IDR — Rupiah
                                        </option>
                                        <option value="USD"
                                            {{ ($prefs['currency'] ?? 'IDR') === 'USD' ? 'selected' : '' }}>USD — Dollar
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notification settings --}}
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                            <div
                                class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-bell text-amber-500 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-stone-800 dark:text-white text-sm">Notifikasi</h3>
                                <p class="text-[11px] text-stone-400">Kapan Anda ingin mendapat pemberitahuan</p>
                            </div>
                        </div>
                        <div class="divide-y divide-stone-100 dark:divide-stone-800">
                            @foreach ([['notif_budget_alert', 'fa-chart-pie', 'text-rose-500', 'Alert Budget Terlampaui', 'Saat pengeluaran melebihi budget yang diset'], ['notif_debt_reminder', 'fa-clock', 'text-amber-500', 'Pengingat Hutang Jatuh Tempo', '7 hari sebelum hutang/piutang jatuh tempo'], ['notif_investment_alert', 'fa-chart-line', 'text-emerald-500', 'Alert Portfolio Investasi', 'Jika return portfolio berubah signifikan'], ['notif_weekly_report', 'fa-file-lines', 'text-blue-500', 'Laporan Mingguan', 'Ringkasan keuangan setiap Senin pagi']] as [$key, $ic, $clr, $lbl, $desc])
                                <div class="flex items-center justify-between px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-stone-50 dark:bg-stone-800 flex items-center justify-center flex-shrink-0">
                                            <i class="fa-solid {{ $ic }} {{ $clr }} text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-stone-700 dark:text-stone-300">
                                                {{ $lbl }}</p>
                                            <p class="text-[11px] text-stone-400">{{ $desc }}</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="togglePref('{{ $key }}')"
                                        id="toggle-{{ $key }}"
                                        class="tog {{ $prefs[$key] ?? true ? 'on' : 'off' }}" role="switch">
                                        <span class="tog-thumb"></span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- ══ TAB NOTIFIKASI ══════════════════════════════════════════════════ --}}
                <div id="tab-notifikasi" class="tab-pane hidden space-y-4 fade-up">

                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        {{-- Header --}}
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800 flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-bell text-amber-500 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2">
                                        Notifikasi
                                        @if ($unreadCount > 0)
                                            <span
                                                class="px-2 py-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full">{{ $unreadCount }}
                                                baru</span>
                                        @endif
                                    </h3>
                                    <p class="text-[11px] text-stone-400">Aktivitas dan pengingat akun</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="markAllRead()"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 rounded-lg transition-colors">
                                    <i class="fa-solid fa-check-double"></i> Baca semua
                                </button>
                                <button onclick="clearAllNotifs()"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash-can"></i> Hapus semua
                                </button>
                            </div>
                        </div>

                        {{-- Filter --}}
                        <div
                            class="flex gap-1.5 px-5 py-3 bg-stone-50/60 dark:bg-stone-800/30 border-b border-stone-100 dark:border-stone-800">
                            <button onclick="filterNotif('all')" id="notif-filter-all"
                                class="px-3 py-1.5 text-[11px] font-semibold rounded-lg bg-white dark:bg-stone-800 text-stone-700 dark:text-stone-300 shadow-sm border border-stone-200 dark:border-stone-700">Semua</button>
                            <button onclick="filterNotif('unread')" id="notif-filter-unread"
                                class="px-3 py-1.5 text-[11px] font-medium rounded-lg text-stone-400 hover:bg-white dark:hover:bg-stone-800 transition-colors">
                                Belum Dibaca
                                @if ($unreadCount > 0)
                                    <span class="ml-1 text-rose-500">({{ $unreadCount }})</span>
                                @endif
                            </button>
                        </div>

                        {{-- List --}}
                        <div id="notif-list"
                            class="divide-y divide-stone-100 dark:divide-stone-800 max-h-[580px] overflow-y-auto">
                            @forelse($notifications as $n)
                                @php
                                    $imap = [
                                        'system' => ['fa-gear', 'text-blue-500', 'bg-blue-50 dark:bg-blue-900/20'],
                                        'deadline' => ['fa-clock', 'text-rose-500', 'bg-rose-50 dark:bg-rose-900/20'],
                                        'reminder' => ['fa-bell', 'text-amber-500', 'bg-amber-50 dark:bg-amber-900/20'],
                                        'financial' => [
                                            'fa-wallet',
                                            'text-emerald-500',
                                            'bg-emerald-50 dark:bg-emerald-900/20',
                                        ],
                                        'academic' => [
                                            'fa-graduation-cap',
                                            'text-purple-500',
                                            'bg-purple-50 dark:bg-purple-900/20',
                                        ],
                                        'investment' => [
                                            'fa-chart-line',
                                            'text-emerald-500',
                                            'bg-emerald-50 dark:bg-emerald-900/20',
                                        ],
                                        'budget' => [
                                            'fa-triangle-exclamation',
                                            'text-rose-500',
                                            'bg-rose-50 dark:bg-rose-900/20',
                                        ],
                                    ];
                                    $ic = $imap[$n->type] ?? [
                                        'fa-circle-info',
                                        'text-stone-400',
                                        'bg-stone-50 dark:bg-stone-800',
                                    ];
                                @endphp
                                <div id="notif-{{ $n->id }}"
                                    class="flex items-start gap-4 px-5 py-4 {{ $n->is_read ? '' : 'bg-orange-50/40 dark:bg-orange-900/5' }} hover:bg-stone-50 dark:hover:bg-stone-800/50 transition-colors group">
                                    <div
                                        class="w-10 h-10 rounded-xl {{ $ic[2] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fa-solid {{ $ic[0] }} {{ $ic[1] }} text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-stone-800 dark:text-white leading-snug">
                                                {{ $n->title }}
                                                @if (!$n->is_read)
                                                    <span
                                                        class="inline-block w-1.5 h-1.5 rounded-full bg-orange-500 mb-0.5 ml-1 align-middle"></span>
                                                @endif
                                            </p>
                                            <div
                                                class="flex items-center gap-1 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                                @if (!$n->is_read)
                                                    <button onclick="markOneRead({{ $n->id }})"
                                                        class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-stone-400 hover:text-blue-500 transition-colors flex items-center justify-center"
                                                        title="Baca">
                                                        <i class="fa-solid fa-check text-[11px]"></i>
                                                    </button>
                                                @endif
                                                <button onclick="deleteNotif({{ $n->id }})"
                                                    class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-rose-100 dark:hover:bg-rose-900/30 text-stone-400 hover:text-rose-500 transition-colors flex items-center justify-center"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-xmark text-[11px]"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5 leading-relaxed">
                                            {{ $n->message }}</p>
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <span class="text-[11px] text-stone-400 flex items-center gap-1"><i
                                                    class="fa-regular fa-clock text-[9px]"></i>
                                                {{ $n->created_at->diffForHumans() }}</span>
                                            @if ($n->getActionUrl())
                                                <a href="{{ $n->getActionUrl() }}"
                                                    onclick="markOneRead({{ $n->id }})"
                                                    class="text-[11px] font-semibold text-orange-500 hover:underline">Lihat
                                                    →</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center py-16 text-stone-400">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-stone-100 dark:bg-stone-800 flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-bell-slash text-2xl opacity-40"></i>
                                    </div>
                                    <p class="font-semibold text-stone-500 dark:text-stone-400">Tidak ada notifikasi</p>
                                    <p class="text-xs text-stone-400 mt-1">Semua aman! Tidak ada yang perlu diperhatikan.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>{{-- end right --}}
        </div>{{-- end two-col --}}
    </div>{{-- end max-w --}}

    {{-- Modal Hapus Akun --}}
    <div id="modal-delete-account"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800 fade-up">
            <div class="flex items-center gap-3 p-6 border-b border-stone-100 dark:border-stone-800">
                <div
                    class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                </div>
                <div>
                    <h3 class="font-bold text-stone-900 dark:text-white">Hapus Akun Secara Permanen</h3>
                    <p class="text-[11px] text-stone-400 mt-0.5">Tidak dapat dibatalkan setelah dikonfirmasi</p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl">
                    <p class="text-sm font-semibold text-rose-700 dark:text-rose-400 mb-2">Data yang akan terhapus:</p>
                    <ul class="text-xs text-rose-600 dark:text-rose-400 space-y-1 list-disc list-inside">
                        <li>Semua akun keuangan & transaksi</li>
                        <li>Data investasi & portfolio</li>
                        <li>Hutang, aset, dan anggaran</li>
                        <li>Notifikasi & pengaturan</li>
                    </ul>
                </div>
                <div>
                    <label class="fi-label">Konfirmasi dengan Password Anda</label>
                    <input type="password" id="delete-confirm-password" class="fi" placeholder="••••••••">
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-delete-account')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 text-stone-600 dark:text-stone-300 text-sm font-medium rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="submitDeleteAccount()"
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash-can text-xs"></i> Hapus Akun
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ── API ─────────────────────────────────────────────────────────────────
        async function api(method, url, data = null, isForm = false) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const upper = method.toUpperCase();
            const opts = {
                method: upper,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            };
            if (data) {
                if (isForm) {
                    opts.body = data;
                } else {
                    opts.headers['Content-Type'] = 'application/json';
                    opts.body = JSON.stringify(data);
                }
            }
            const res = await fetch(url, opts);
            if (res.status === 419) {
                location.reload();
                return {
                    success: false
                };
            }
            return res.json();
        }

        function toast(msg, ok = true) {
            const el = document.createElement('div');
            el.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-2.5 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold text-white ${ok ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-rose-500 to-rose-600'}`;
            el.style.cssText = 'animation:fadeUp .28s ease-out both;';
            el.innerHTML = `<i class="fa-solid ${ok ? 'fa-circle-check' : 'fa-circle-xmark'} text-base"></i>${msg}`;
            document.body.appendChild(el);
            setTimeout(() => {
                el.style.transition = 'opacity .3s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }, 3200);
        }

        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('modal-open');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }

        // ── Tabs ─────────────────────────────────────────────────────────────────
        function switchTab(id) {
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.nav-pill').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + id)?.classList.remove('hidden');
            document.getElementById('tab-btn-' + id)?.classList.add('active');
            history.replaceState(null, '', '#' + id);
        }
        document.addEventListener('DOMContentLoaded', () => {
            const h = location.hash.replace('#', '') || 'profil';
            switchTab(['profil', 'keamanan', 'pengaturan', 'notifikasi'].includes(h) ? h : 'profil');
        });

        // ── Profil ────────────────────────────────────────────────────────────────
        async function submitProfil() {
            const fd = new FormData(document.getElementById('form-profil'));
            fd.append('_method', 'PUT');
            const res = await api('POST', '{{ route('profile.update') }}', fd, true);
            res.success ? toast(res.message || 'Profil tersimpan!') : toast(res.message || 'Gagal menyimpan.', false);
        }

        async function uploadAvatar(input) {
            if (!input.files[0]) return;
            const fd = new FormData();
            fd.append('avatar', input.files[0]);
            fd.append('name', '{{ auth()->user()->name }}');
            fd.append('email', '{{ auth()->user()->email }}');
            fd.append('_method', 'PUT');
            toast('Mengupload foto...');
            const res = await api('POST', '{{ route('profile.update') }}', fd, true);
            if (res.success) {
                toast('Foto profil diperbarui!');
                setTimeout(() => location.reload(), 900);
            } else toast(res.message || 'Gagal upload.', false);
        }

        // ── Password ──────────────────────────────────────────────────────────────
        function togglePw(id) {
            const e = document.getElementById(id);
            e.type = e.type === 'password' ? 'text' : 'password';
        }

        function checkPwStrength(v) {
            let s = 0;
            if (v.length >= 8) s++;
            if (/[A-Z]/.test(v)) s++;
            if (/[0-9]/.test(v)) s++;
            if (/[^A-Za-z0-9]/.test(v)) s++;
            const cfg = [{
                c: 'bg-rose-500',
                t: 'Lemah',
                tc: 'text-rose-500'
            }, {
                c: 'bg-amber-500',
                t: 'Cukup',
                tc: 'text-amber-500'
            }, {
                c: 'bg-blue-500',
                t: 'Kuat',
                tc: 'text-blue-500'
            }, {
                c: 'bg-emerald-500',
                t: 'Sangat Kuat',
                tc: 'text-emerald-500'
            }];
            [1, 2, 3, 4].forEach(i => {
                const b = document.getElementById('pw-bar-' + i);
                if (b) b.className = `pw-seg ${i<=s&&s>0?cfg[s-1].c:''}`;
            });
            const l = document.getElementById('pw-lbl');
            if (l) {
                l.textContent = s > 0 ? cfg[s - 1].t : 'Masukkan password baru';
                l.className = `text-[11px] ${s>0?cfg[s-1].tc:'text-stone-400'}`;
            }
        }

        async function submitPassword() {
            const fd = new FormData(document.getElementById('form-password'));
            const d = {};
            fd.forEach((v, k) => {
                if (k !== '_method') d[k] = v;
            });
            const res = await api('PUT', '{{ route('profile.password') }}', d);
            if (res.success) {
                toast('Password berhasil diubah!');
                document.getElementById('form-password').reset();
                [1, 2, 3, 4].forEach(i => {
                    const b = document.getElementById('pw-bar-' + i);
                    if (b) b.className = 'pw-seg';
                });
                const l = document.getElementById('pw-lbl');
                if (l) {
                    l.textContent = 'Masukkan password baru';
                    l.className = 'text-[11px] text-stone-400';
                }
            } else toast(res.message || 'Gagal mengubah password.', false);
        }

        // ── Preferences ───────────────────────────────────────────────────────────
        let localPrefs = @json(auth()->user()->preferences ?? []);

        function setThemePref(val) {
            localPrefs.theme = val;
            document.querySelectorAll('.th-card').forEach(c => {
                const on = c.id === 'theme-opt-' + val;
                c.classList.toggle('sel', on);
                const dot = c.querySelectorAll('span').item(c.querySelectorAll('span').length - 1);
                if (dot) dot.className =
                    `w-2 h-2 rounded-full ${on?'bg-orange-500':'bg-stone-200 dark:bg-stone-700'}`;
            });
            if (val === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else if (val === 'light') {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                localStorage.removeItem('color-theme');
            }
            savePref();
        }

        function togglePref(key) {
            localPrefs[key] = !(localPrefs[key] ?? true);
            const btn = document.getElementById('toggle-' + key),
                span = btn?.querySelector('.tog-thumb');
            if (btn) {
                btn.classList.toggle('on', localPrefs[key]);
                btn.classList.toggle('off', !localPrefs[key]);
            }
            if (span) span.className = `tog-thumb`; // CSS handles transform via .on .tog-thumb
            savePref();
        }

        function savePref() {
            localPrefs.language = document.getElementById('pref-language')?.value || 'id';
            localPrefs.currency = document.getElementById('pref-currency')?.value || 'IDR';
            api('PUT', '{{ route('profile.preferences') }}', localPrefs).then(r => {
                if (r.success) toast('Pengaturan disimpan.');
            });
        }

        // ── Notifikasi ─────────────────────────────────────────────────────────────
        function filterNotif(type) {
            const on =
                'px-3 py-1.5 text-[11px] font-semibold rounded-lg bg-white dark:bg-stone-800 text-stone-700 dark:text-stone-300 shadow-sm border border-stone-200 dark:border-stone-700';
            const off =
                'px-3 py-1.5 text-[11px] font-medium rounded-lg text-stone-400 hover:bg-white dark:hover:bg-stone-800 transition-colors';
            document.getElementById('notif-filter-all').className = type === 'all' ? on : off;
            document.getElementById('notif-filter-unread').className = type === 'unread' ? on : off;
            document.querySelectorAll('#notif-list > div[id^="notif-"]').forEach(el => {
                const unread = el.classList.contains('bg-orange-50/40') || el.classList.contains(
                    'dark:bg-orange-900/5');
                el.style.display = (type === 'all' || unread) ? '' : 'none';
            });
        }

        async function markOneRead(id) {
            await api('POST', `/notifications/${id}/read`);
            const el = document.getElementById('notif-' + id);
            if (el) {
                el.classList.remove('bg-orange-50/40', 'dark:bg-orange-900/5');
                el.querySelector('span.bg-orange-500')?.remove();
            }
        }
        async function markAllRead() {
            const r = await api('POST', '{{ route('notifications.read-all') }}');
            if (r.success) {
                toast('Semua notifikasi dibaca.');
                setTimeout(() => location.reload(), 700);
            }
        }
        async function deleteNotif(id) {
            const el = document.getElementById('notif-' + id);
            if (el) {
                el.style.transition = 'all .22s';
                el.style.opacity = '0';
                el.style.transform = 'translateX(16px)';
                setTimeout(() => el.remove(), 220);
            }
            await api('DELETE', `/notifications/${id}`);
            toast('Notifikasi dihapus.');
        }
        async function clearAllNotifs() {
            if (!confirm('Hapus semua notifikasi?')) return;
            const r = await api('DELETE', '{{ route('notifications.clear') }}');
            if (r.success) {
                toast(r.message);
                setTimeout(() => location.reload(), 700);
            }
        }

        // ── Delete Account ─────────────────────────────────────────────────────────
        async function submitDeleteAccount() {
            const pw = document.getElementById('delete-confirm-password').value;
            if (!pw) {
                toast('Masukkan password dulu.', false);
                return;
            }
            const r = await api('DELETE', '{{ route('profile.destroy') }}', {
                password: pw
            });
            if (r.success) {
                toast('Akun dihapus. Sampai jumpa!');
                setTimeout(() => window.location.href = '/', 1500);
            } else toast(r.message || 'Password tidak sesuai.', false);
        }
    </script>
@endpush
