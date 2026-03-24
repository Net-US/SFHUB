{{-- 
    Komponen tombol Subscribe dengan Midtrans Snap
    Taruh di halaman pricing / plan kamu
    
    PENTING: 
    - midtrans_client_key dari DB akan di-load oleh AppServiceProvider
    - Script snap.js di-load dari CDN Midtrans
--}}

{{-- Load Midtrans Snap JS --}}
@php
    $isSandbox = \App\Models\SystemSetting::get('midtrans_sandbox', true);
    $snapUrl   = $isSandbox
        ? 'https://app.sandbox.midtrans.com/snap/snap.js'
        : 'https://app.midtrans.com/snap/snap.js';
    $clientKey = \App\Models\SystemSetting::get('midtrans_client_key');
@endphp

<script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>

{{-- Contoh tombol subscribe untuk setiap plan --}}
@foreach($plans as $plan)
<div class="plan-card border rounded-xl p-6">
    <h3>{{ $plan->name }}</h3>
    <p>Rp {{ number_format($plan->price_monthly, 0, ',', '.') }} / bulan</p>

    <button
        onclick="subscribe({{ $plan->id }}, 'monthly')"
        class="btn-subscribe px-6 py-2 bg-emerald-600 text-white rounded-xl"
        data-plan="{{ $plan->id }}"
    >
        Subscribe Bulanan
    </button>

    <button
        onclick="subscribe({{ $plan->id }}, 'yearly')"
        class="btn-subscribe px-6 py-2 bg-blue-600 text-white rounded-xl mt-2"
        data-plan="{{ $plan->id }}"
    >
        Subscribe Tahunan (Hemat 20%)
    </button>
</div>
@endforeach

<script>
async function subscribe(planId, billingCycle) {
    // Disable tombol saat proses
    document.querySelectorAll('.btn-subscribe').forEach(b => b.disabled = true);

    try {
        // 1. Minta snap token dari server kita
        const response = await fetch('/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ plan_id: planId, billing_cycle: billingCycle })
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.message || 'Terjadi kesalahan');
            return;
        }

        // 2. Buka popup pembayaran Midtrans Snap
        window.snap.pay(data.snap_token, {
            onSuccess: function(result) {
                // Pembayaran berhasil — redirect ke halaman sukses
                window.location.href = '/midtrans/finish?order_id=' + result.order_id + '&transaction_status=' + result.transaction_status;
            },
            onPending: function(result) {
                alert('Pembayaran pending. Selesaikan pembayaran kamu.');
            },
            onError: function(result) {
                alert('Pembayaran gagal: ' + result.status_message);
            },
            onClose: function() {
                // User menutup popup tanpa bayar
                console.log('Popup ditutup tanpa pembayaran');
            }
        });
    } catch (error) {
        alert('Terjadi kesalahan koneksi. Coba lagi.');
        console.error(error);
    } finally {
        document.querySelectorAll('.btn-subscribe').forEach(b => b.disabled = false);
    }
}
</script>
