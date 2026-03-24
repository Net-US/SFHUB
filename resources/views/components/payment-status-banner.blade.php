{{--
    Komponen banner status pembayaran untuk dashboard.
    Taruh di awal section konten dashboard, sebelum konten utama:
    @include('components.payment-status-banner')

    Dibaca dari query string: ?payment=success|pending|error
    dan dari session flash: 'payment_status', 'payment_message'
--}}

@php
    $paymentStatus = request()->query('payment') ?? session('payment_status');
    $paymentMessage = session('payment_message');

    $config = match ($paymentStatus) {
        'success' => [
            'icon' => 'fa-circle-check',
            'title' => 'Pembayaran Berhasil!',
            'message' => $paymentMessage ?? 'Paket premium kamu sudah aktif. Selamat menikmati semua fitur SFHUB!',
            'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
            'border' => 'border-emerald-200 dark:border-emerald-800',
            'icon_cl' => 'text-emerald-500',
            'title_cl' => 'text-emerald-800 dark:text-emerald-300',
            'text_cl' => 'text-emerald-700 dark:text-emerald-400',
        ],
        'pending' => [
            'icon' => 'fa-clock',
            'title' => 'Pembayaran Sedang Diproses',
            'message' =>
                $paymentMessage ??
                'Pembayaran kamu sedang diverifikasi. Akun akan diaktifkan otomatis setelah konfirmasi.',
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'border' => 'border-amber-200 dark:border-amber-800',
            'icon_cl' => 'text-amber-500',
            'title_cl' => 'text-amber-800 dark:text-amber-300',
            'text_cl' => 'text-amber-700 dark:text-amber-400',
        ],
        'error' => [
            'icon' => 'fa-circle-xmark',
            'title' => 'Pembayaran Gagal',
            'message' =>
                $paymentMessage ?? 'Transaksi tidak berhasil. Tidak ada biaya yang dikenakan. Silakan coba lagi.',
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'icon_cl' => 'text-red-500',
            'title_cl' => 'text-red-800 dark:text-red-300',
            'text_cl' => 'text-red-700 dark:text-red-400',
        ],
        default => null,
    };
@endphp

@if ($config)
    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-250" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="relative mb-5 p-4 rounded-2xl border {{ $config['bg'] }} {{ $config['border'] }} flex items-start gap-3">

        <div class="flex-shrink-0 mt-0.5">
            <i class="fa-solid {{ $config['icon'] }} text-xl {{ $config['icon_cl'] }}"></i>
        </div>

        <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm {{ $config['title_cl'] }}">{{ $config['title'] }}</p>
            <p class="text-sm mt-0.5 {{ $config['text_cl'] }}">{{ $config['message'] }}</p>

            @if ($paymentStatus === 'success')
                <div class="mt-2 flex flex-wrap gap-2">
                    <a href="{{ route('dashboard') }}"
                        class="text-xs font-medium px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fa-solid fa-rocket mr-1"></i> Mulai pakai premium
                    </a>
                </div>
            @elseif ($paymentStatus === 'error')
                <div class="mt-2">
                    <a href="{{ route('auth.onboarding-payment') }}"
                        class="text-xs font-medium px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fa-solid fa-rotate-right mr-1"></i> Coba bayar lagi
                    </a>
                </div>
            @endif
        </div>

        <button @click="show = false; history.replaceState({}, document.title, window.location.pathname)"
            class="flex-shrink-0 p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
            aria-label="Tutup">
            <i class="fa-solid fa-xmark text-sm {{ $config['text_cl'] }}"></i>
        </button>
    </div>
@endif

{{-- Also handle session-based flash messages (general success/warning/error) --}}
@if (session('success') && !$paymentStatus)
    <div x-data="{ show: true }" x-show="show"
        class="mb-5 p-4 rounded-2xl border bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-emerald-500"></i>
        <span class="flex-1 text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</span>
        <button @click="show=false" class="p-1 hover:bg-black/5 rounded-lg"><i
                class="fa-solid fa-xmark text-sm text-emerald-600"></i></button>
    </div>
@endif

@if (session('warning'))
    <div x-data="{ show: true }" x-show="show"
        class="mb-5 p-4 rounded-2xl border bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
        <span class="flex-1 text-sm text-amber-700 dark:text-amber-400">{{ session('warning') }}</span>
        <button @click="show=false" class="p-1 hover:bg-black/5 rounded-lg"><i
                class="fa-solid fa-xmark text-sm text-amber-600"></i></button>
    </div>
@endif
