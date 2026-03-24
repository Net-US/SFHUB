@extends('layouts.app-landing')

@section('title', 'Aktivasi Paket Premium — SFHUB')

@php
    $midtransClientKey = \App\Models\SystemSetting::get('midtrans_client_key', '');
    $isSandbox = (bool) \App\Models\SystemSetting::get('midtrans_sandbox', true);
    $snapUrl = $isSandbox ? 'https://app.sandbox.midtrans.com/snap/snap.js' : 'https://app.midtrans.com/snap/snap.js';
    $midtransReady = !empty($midtransClientKey);
@endphp

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');

        :root {
            --clr-bg: #f5f3ef;
            --clr-surface: rgba(255, 255, 255, .92);
            --clr-dark: #111110;
            --clr-muted: #78716c;
            --clr-brand: #f97316;
            --clr-brand2: #10b981;
            --clr-border: rgba(0, 0, 0, .09);
            --radius-xl: 24px;
            --radius-lg: 16px;
            --ease-spring: cubic-bezier(.34, 1.56, .64, 1);
            --ease-smooth: cubic-bezier(.22, 1, .36, 1);
        }

        .dark {
            --clr-bg: #0f0e0d;
            --clr-surface: rgba(26, 24, 22, .95);
            --clr-dark: #f5f3ef;
            --clr-muted: #a8a29e;
            --clr-border: rgba(255, 255, 255, .08);
        }

        body {
            font-family: 'DM Sans', sans-serif;
        }

        .font-display {
            font-family: 'Syne', sans-serif;
        }

        .pay-wrap {
            min-height: 100svh;
            background: var(--clr-bg);
            padding: 4rem 1rem;
            display: grid;
            place-items: start center;
            position: relative;
            overflow: hidden;
        }

        .pay-wrap::before {
            content: '';
            position: absolute;
            top: -200px;
            right: -150px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, .12), transparent 70%);
            pointer-events: none;
        }

        .pay-shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1000px;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 768px) {
            .pay-shell {
                grid-template-columns: 1fr;
            }
        }

        /* ── Card ── */
        .pay-card {
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-xl);
            padding: 2rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, .04), 0 24px 48px rgba(0, 0, 0, .10);
            animation: slideUp .5s cubic-bezier(.22, 1, .36, 1) both;
        }

        .pay-card:nth-child(2) {
            animation-delay: .1s;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Section heading ── */
        .step-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--clr-brand2);
            margin-bottom: .35rem;
        }

        /* ── Plan card ── */
        .plan-opt {
            border: 2px solid var(--clr-border);
            border-radius: var(--radius-lg);
            padding: 1rem 1.125rem;
            cursor: pointer;
            transition: all .22s cubic-bezier(.22, 1, .36, 1);
            display: flex;
            align-items: center;
            gap: .875rem;
            position: relative;
            overflow: hidden;
        }

        .plan-opt:hover {
            border-color: var(--clr-brand);
            transform: translateY(-1px);
        }

        .plan-opt.selected {
            border-color: var(--clr-brand2);
            background: rgba(16, 185, 129, .05);
        }

        .plan-opt.selected::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-top: 28px solid var(--clr-brand2);
            border-left: 28px solid transparent;
        }

        .plan-opt.selected::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 2px;
            right: 3px;
            font-size: .55rem;
            color: #fff;
            z-index: 1;
        }

        .plan-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* ── Billing toggle ── */
        .billing-toggle {
            display: inline-flex;
            background: rgba(0, 0, 0, .05);
            border-radius: 12px;
            padding: 3px;
            gap: 2px;
        }

        .dark .billing-toggle {
            background: rgba(255, 255, 255, .06);
        }

        .billing-btn {
            padding: .5rem 1.25rem;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            font-size: .875rem;
            font-weight: 500;
            background: transparent;
            color: var(--clr-muted);
            transition: all .2s ease;
        }

        .billing-btn.active {
            background: var(--clr-surface);
            color: var(--clr-dark);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .10);
            font-weight: 600;
        }

        /* ── Summary box ── */
        .summary-box {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            color: #fff;
            margin-top: 1.25rem;
        }

        /* ── Benefit list ── */
        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            font-size: .875rem;
            padding: .5rem 0;
            border-bottom: 1px solid var(--clr-border);
        }

        .benefit-item:last-child {
            border-bottom: none;
        }

        /* ── Pay button ── */
        .btn-pay {
            width: 100%;
            padding: 1rem;
            border-radius: var(--radius-lg);
            background: var(--clr-brand2);
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: all .22s cubic-bezier(.34, 1.56, .64, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-pay:hover {
            background: #0a9565;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, .35);
        }

        .btn-pay:active {
            transform: translateY(0);
        }

        .btn-pay:disabled {
            opacity: .5;
            pointer-events: none;
        }

        .btn-pay::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, .12), transparent 50%);
            pointer-events: none;
        }

        .btn-skip {
            width: 100%;
            padding: .875rem;
            border-radius: var(--radius-lg);
            background: transparent;
            border: 1.5px solid var(--clr-border);
            color: var(--clr-muted);
            font-size: .9rem;
            cursor: pointer;
            transition: all .2s ease;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: .625rem;
        }

        .btn-skip:hover {
            border-color: rgba(0, 0, 0, .2);
            color: var(--clr-dark);
        }

        /* ── Warning banner ── */
        .warn-banner {
            border: 1.5px solid rgba(245, 158, 11, .3);
            background: rgba(245, 158, 11, .07);
            border-radius: var(--radius-lg);
            padding: 1rem 1.125rem;
            display: flex;
            gap: .75rem;
            font-size: .875rem;
            color: #92400e;
            margin-bottom: 1.25rem;
        }

        .dark .warn-banner {
            color: #fcd34d;
        }

        /* ── Error ── */
        #pay-error {
            padding: .75rem 1rem;
            background: rgba(239, 68, 68, .08);
            border: 1px solid rgba(239, 68, 68, .2);
            border-radius: 10px;
            color: #b91c1c;
            font-size: .85rem;
            margin-top: .75rem;
        }

        /* ── Trust badges ── */
        .trust-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            flex-wrap: wrap;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--clr-border);
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: .375rem;
            font-size: .75rem;
            color: var(--clr-muted);
        }

        /* Number step */
        .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--clr-brand2);
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('content')
    <div class="pay-wrap">
        <div class="pay-shell">

            {{-- ── Left: Plan + Billing ── --}}
            <div class="space-y-5">

                {{-- Header --}}
                <div>
                    <div class="step-label">
                        <i class="fa-solid fa-circle-check mr-1"></i> Akun berhasil dibuat
                    </div>
                    <h1 class="font-display font-800 text-3xl md:text-4xl leading-tight" style="color: var(--clr-dark);">
                        Pilih Paket Premium
                    </h1>
                    <p class="mt-1 text-sm" style="color: var(--clr-muted);">
                        Aktifkan fitur lengkap SFHUB. Pembayaran aman via Midtrans.
                    </p>
                </div>

                {{-- Midtrans not ready warning --}}
                @if (!$midtransReady)
                    <div class="warn-banner">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <strong class="block">Pembayaran belum dikonfigurasi</strong>
                            Midtrans API key belum diisi. Admin perlu mengatur di
                            <strong>Admin → Settings → Payment</strong>.
                            Kamu tetap bisa lanjut ke dashboard dengan paket gratis.
                        </div>
                    </div>
                @endif

                {{-- Plan cards --}}
                <div class="pay-card">
                    <h3 class="font-display font-700 text-base mb-4" style="color: var(--clr-dark);">
                        Pilih Paket
                    </h3>
                    <div class="space-y-2.5" id="plan-list">
                        @foreach ($plans as $plan)
                            @php
                                $colors = ['#10b981', '#f97316', '#8b5cf6', '#3b82f6'];
                                $ci = $loop->index % 4;
                            @endphp
                            <div class="plan-opt {{ $selectedPlan->id === $plan->id ? 'selected' : '' }}"
                                data-plan-id="{{ $plan->id }}" data-monthly="{{ (float) $plan->price_monthly }}"
                                data-yearly="{{ (float) $plan->price_yearly }}" role="button" tabindex="0">
                                <div class="plan-icon"
                                    style="background: {{ $colors[$ci] }}1a; color: {{ $colors[$ci] }};">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-display font-700" style="color: var(--clr-dark);">{{ $plan->name }}
                                    </div>
                                    <div class="text-xs mt-0.5" style="color: var(--clr-muted);">
                                        {{ $plan->description ?: 'Akses fitur premium SFHUB' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-display font-700 text-sm plan-price-display"
                                        style="color: {{ $colors[$ci] }}">
                                        Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs plan-cycle-label" style="color: var(--clr-muted);">/bulan</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Billing cycle --}}
                <div class="pay-card">
                    <h3 class="font-display font-700 text-base mb-3" style="color: var(--clr-dark);">
                        Periode Berlangganan
                    </h3>
                    <div class="billing-toggle" id="billing-toggle">
                        <button class="billing-btn active" data-cycle="monthly">Bulanan</button>
                        <button class="billing-btn" data-cycle="yearly">
                            Tahunan
                            <span
                                style="font-size:.7rem; background:rgba(16,185,129,.15); color:#065f46;
                              padding:.1rem .4rem; border-radius:999px; margin-left:.25rem;">Hemat
                                17%</span>
                        </button>
                    </div>
                </div>

                {{-- Features list --}}
                @if ($selectedPlan->features)
                    <div class="pay-card">
                        <h3 class="font-display font-700 text-base mb-3" style="color: var(--clr-dark);">
                            Yang Kamu Dapatkan
                        </h3>
                        @foreach ($selectedPlan->features as $feature)
                            <div class="benefit-item">
                                <i class="fa-solid fa-check text-emerald-500 mt-0.5 flex-shrink-0"></i>
                                <span style="color: var(--clr-dark);">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            {{-- ── Right: Checkout ── --}}
            <div class="pay-card" style="position: sticky; top: 2rem;">

                <div class="step-label">Ringkasan Pesanan</div>
                <h2 class="font-display font-700 text-xl mb-4" style="color: var(--clr-dark);">Konfirmasi Pembayaran</h2>

                {{-- Summary --}}
                <div class="summary-box">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-display font-700 text-lg" id="summary-plan-name">
                                {{ $selectedPlan->name }}
                            </div>
                            <div class="text-sm text-slate-400" id="summary-cycle-label">Berlangganan Bulanan</div>
                        </div>
                        <div class="text-right">
                            <div class="font-display font-800 text-2xl text-emerald-400" id="summary-price">
                                Rp {{ number_format($selectedPlan->price_monthly, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-400">/periode</div>
                        </div>
                    </div>
                    <div class="border-t border-slate-700 pt-3 mt-1 text-sm text-slate-300">
                        <i class="fa-solid fa-shield-halved text-emerald-400 mr-1"></i>
                        Pembayaran aman diproses oleh Midtrans
                    </div>
                </div>

                <div class="mt-5 space-y-2.5 text-sm" style="color: var(--clr-muted);">
                    <div class="flex items-center gap-2">
                        <div class="step-num">1</div>
                        Klik "Bayar Sekarang"
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="step-num">2</div>
                        Popup Midtrans terbuka — pilih metode bayar
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="step-num">3</div>
                        Selesai! Akun premium langsung aktif
                    </div>
                </div>

                <div class="mt-5">
                    <button id="btn-pay" class="btn-pay" {{ $midtransReady ? '' : 'disabled' }}>
                        <i class="fa-solid fa-lock text-sm"></i>
                        <span id="btn-pay-text">Bayar Sekarang</span>
                    </button>

                    <div id="pay-error" class="hidden"></div>

                    <a href="{{ route('dashboard') }}" class="btn-skip">
                        Nanti saja — lanjut dengan paket gratis
                    </a>
                </div>

                <div class="trust-badges">
                    <div class="trust-badge">
                        <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                        SSL Secure
                    </div>
                    <div class="trust-badge">
                        <i class="fa-solid fa-rotate-left text-blue-500"></i>
                        Batalkan kapan saja
                    </div>
                    <div class="trust-badge">
                        <i class="fa-solid fa-headset text-orange-500"></i>
                        24/7 Support
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($midtransReady)
        <script src="{{ $snapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── State ── */
            let selectedPlanId = {{ $selectedPlan->id }};
            let selectedCycle = 'monthly';

            const planOpts = Array.from(document.querySelectorAll('.plan-opt'));
            const billingBtns = Array.from(document.querySelectorAll('.billing-btn'));
            const summaryName = document.getElementById('summary-plan-name');
            const summaryPrice = document.getElementById('summary-price');
            const summaryLabel = document.getElementById('summary-cycle-label');
            const payBtn = document.getElementById('btn-pay');
            const payBtnText = document.getElementById('btn-pay-text');
            const payError = document.getElementById('pay-error');

            /* ── Format IDR ── */
            const fmtIdr = v => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(v || 0);

            /* ── Get active plan data ── */
            function getActivePlan() {
                return planOpts.find(p => Number(p.dataset.planId) === selectedPlanId);
            }

            /* ── Render UI state ── */
            function render() {
                const plan = getActivePlan();
                const price = selectedCycle === 'monthly' ?
                    Number(plan.dataset.monthly) :
                    Number(plan.dataset.yearly);

                // Plan opts highlight
                planOpts.forEach(p => p.classList.toggle('selected', Number(p.dataset.planId) === selectedPlanId));

                // Billing buttons
                billingBtns.forEach(b => b.classList.toggle('active', b.dataset.cycle === selectedCycle));

                // Price in each plan opt
                planOpts.forEach(opt => {
                    const priceEl = opt.querySelector('.plan-price-display');
                    const cycleEl = opt.querySelector('.plan-cycle-label');
                    if (priceEl) priceEl.textContent = fmtIdr(
                        selectedCycle === 'monthly' ? Number(opt.dataset.monthly) : Number(opt.dataset
                            .yearly)
                    );
                    if (cycleEl) cycleEl.textContent = selectedCycle === 'monthly' ? '/bulan' : '/tahun';
                });

                // Summary
                const nameEl = plan.querySelector('.font-display');
                if (summaryName) summaryName.textContent = nameEl?.textContent?.trim() || '';
                if (summaryPrice) summaryPrice.textContent = fmtIdr(price);
                if (summaryLabel) summaryLabel.textContent = selectedCycle === 'monthly' ?
                    'Berlangganan Bulanan' : 'Berlangganan Tahunan';
            }

            /* ── Event listeners ── */
            planOpts.forEach(opt => {
                opt.addEventListener('click', function() {
                    selectedPlanId = Number(this.dataset.planId);
                    render();
                });
                opt.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        opt.click();
                    }
                });
            });

            billingBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    selectedCycle = this.dataset.cycle;
                    render();
                });
            });

            /* ── Pay handler ── */
            payBtn?.addEventListener('click', async function() {
                payError.classList.add('hidden');
                payBtn.disabled = true;
                payBtnText.textContent = 'Menyiapkan transaksi...';

                try {
                    const res = await fetch('{{ route('subscribe') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            plan_id: selectedPlanId,
                            billing_cycle: selectedCycle,
                        }),
                    });

                    const data = await res.json();

                    if (!res.ok || !data.snap_token) {
                        throw new Error(data.message || 'Gagal membuat transaksi.');
                    }

                    window.snap.pay(data.snap_token, {
                        onSuccess: () => {
                            window.location.href =
                                '{{ route('dashboard') }}?payment=success';
                        },
                        onPending: () => {
                            window.location.href =
                                '{{ route('dashboard') }}?payment=pending';
                        },
                        onError: () => {
                            window.location.href =
                            '{{ route('dashboard') }}?payment=error';
                        },
                        onClose: () => {
                            payBtn.disabled = false;
                            payBtnText.textContent = 'Bayar Sekarang';
                        },
                    });
                } catch (err) {
                    payError.textContent = err.message;
                    payError.classList.remove('hidden');
                    payBtn.disabled = false;
                    payBtnText.textContent = 'Bayar Sekarang';
                }
            });

            render();
        });
    </script>
@endpush
