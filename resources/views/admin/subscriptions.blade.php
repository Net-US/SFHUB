@extends('layouts.app')
@section('title', 'Subscription Management | Admin')
@section('page-title', 'Subscription Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Subscription Management</h2>
            <p class="text-stone-500 text-sm">Kelola paket berlangganan dan monitor revenue</p>
        </div>
        <button onclick="showAddPlanModal()"
            class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors self-start">
            <i class="fa-solid fa-plus"></i> Tambah Paket
        </button>
    </div>

    @if(session('success'))<div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 rounded-xl px-5 py-3.5 text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-2"><i class="fa-solid fa-check-circle"></i>{{ session('success') }}</div>@endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            [$totalSubscriptions ?? 0,'Total Subscriptions','fa-list','text-stone-600'],
            [$activeSubscriptions ?? 0,'Aktif','fa-circle-check','text-emerald-600'],
            ['Rp '.number_format($monthlyRevenue ?? 0),'Monthly Revenue','fa-coins','text-amber-600'],
            [($churnRate ?? 0).'%','Churn Rate','fa-chart-line','text-blue-600'],
        ] as [$v,$l,$ic,$cls])
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-xs text-stone-500 mb-1">{{ $l }}</p>
            <div class="flex items-center gap-2">
                <i class="fa-solid {{ $ic }} {{ $cls }} text-lg"></i>
                <p class="text-xl font-bold {{ $cls }}">{{ $v }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Plans Grid ──────────────────────────────────────────────────── --}}
    <div>
        <h3 class="font-bold text-stone-800 dark:text-white text-sm mb-3 flex items-center gap-2">
            <i class="fa-solid fa-crown text-amber-500"></i> Paket Berlangganan
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($plans as $plan)
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border {{ $plan->is_active ? 'border-stone-200 dark:border-stone-800' : 'border-stone-300 dark:border-stone-700 opacity-60' }} relative overflow-hidden">
                {{-- Active badge --}}
                @if(!$plan->is_active)
                <div class="absolute top-2 right-2 bg-stone-400 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">NONAKTIF</div>
                @endif

                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-bold text-lg text-stone-900 dark:text-white">{{ $plan->name }}</h3>
                        <p class="text-xs text-stone-400 font-mono mt-0.5">slug: {{ $plan->slug }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-500">
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-2xl font-bold text-stone-900 dark:text-white">
                        Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-stone-400">/bulan</div>
                    @if($plan->price_yearly > 0)
                    <div class="text-xs text-stone-500 mt-0.5">
                        Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}/tahun
                    </div>
                    @endif
                </div>

                @if($plan->description)
                <p class="text-xs text-stone-500 dark:text-stone-400 mb-3">{{ Str::limit($plan->description, 80) }}</p>
                @endif

                @if(!empty($plan->features))
                <div class="mb-3 space-y-1">
                    @foreach(array_slice($plan->features, 0, 3) as $feat)
                    <div class="flex items-center gap-1.5 text-xs text-stone-600 dark:text-stone-400">
                        <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                        {{ is_string($feat) ? $feat : ($feat['label'] ?? $feat['text'] ?? $feat) }}
                    </div>
                    @endforeach
                    @if(count($plan->features) > 3)
                    <div class="text-xs text-stone-400">+{{ count($plan->features) - 3 }} fitur lagi</div>
                    @endif
                </div>
                @endif

                <div class="flex items-center justify-between pt-3 border-t border-stone-100 dark:border-stone-800">
                    <span class="text-xs text-stone-400">
                        {{ $plan->subscriptions()->where('status','active')->where('ends_at','>',now())->count() }} aktif
                    </span>
                    <div class="flex gap-1">
                        <button onclick="editPlan({{ $plan->id }})"
                            class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Edit">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <button onclick="togglePlan({{ $plan->id }}, this)"
                            class="p-1.5 {{ $plan->is_active ? 'text-amber-500 hover:text-amber-700' : 'text-emerald-500 hover:text-emerald-700' }} hover:bg-stone-50 dark:hover:bg-stone-800 rounded-lg transition-colors"
                            title="{{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                            <i class="fa-solid {{ $plan->is_active ? 'fa-eye-slash' : 'fa-eye' }} text-xs"></i>
                        </button>
                        <button onclick="deletePlan({{ $plan->id }})"
                            class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors" title="Hapus">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-4 text-center py-12 text-stone-400">
                <i class="fa-solid fa-crown text-4xl mb-3 block opacity-30"></i>
                <p class="text-sm">Belum ada paket. Klik "Tambah Paket" untuk membuat paket pertama.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Subscriptions Table ──────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 dark:border-stone-800 flex items-center justify-between">
            <h3 class="font-bold text-stone-800 dark:text-white text-sm">Daftar Subscriptions</h3>
            <div class="flex gap-2">
                @foreach([''=>'Semua','active'=>'Aktif','pending'=>'Pending','cancelled'=>'Batal'] as $s=>$l)
                <a href="{{ request()->fullUrlWithQuery(['status'=>$s]) }}"
                    class="px-3 py-1 text-xs rounded-lg {{ request('status',$s===''?'':null)===$s ? 'bg-stone-800 dark:bg-stone-700 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-stone-200' }} transition-colors">
                    {{ $l }}
                </a>
                @endforeach
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-100 dark:border-stone-800">
                        @foreach(['User','Paket','Siklus','Harga','Mulai','Berakhir','Status','Aksi'] as $h)
                        <th class="text-left py-3 px-4 text-stone-500 font-medium text-xs">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    @php
                        $statusCls = match($sub->status){
                            'active'   =>'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                            'pending'  =>'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            'cancelled'=>'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
                            default    =>'bg-stone-100 dark:bg-stone-700 text-stone-500',
                        };
                        $isExpired = $sub->status==='active' && $sub->ends_at?->isPast();
                    @endphp
                    <tr class="border-b border-stone-50 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800/50 transition-colors" id="sub-row-{{ $sub->id }}">
                        <td class="py-3 px-4">
                            <div class="font-medium text-stone-800 dark:text-white text-xs">{{ $sub->user?->name ?? '-' }}</div>
                            <div class="text-stone-400 text-[10px]">{{ $sub->user?->email }}</div>
                        </td>
                        <td class="py-3 px-4 text-stone-700 dark:text-stone-300 text-xs font-semibold">{{ $sub->plan?->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-stone-500 text-xs">{{ ucfirst($sub->billing_cycle ?? '-') }}</td>
                        <td class="py-3 px-4 text-stone-700 dark:text-stone-300 text-xs">Rp {{ number_format($sub->amount_paid, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-stone-400 text-xs">{{ $sub->starts_at?->format('d M Y') ?? '-' }}</td>
                        <td class="py-3 px-4 text-xs {{ $isExpired ? 'text-rose-500' : 'text-stone-400' }}">
                            {{ $sub->ends_at?->format('d M Y') ?? '-' }}
                            @if($isExpired)<span class="ml-1 text-[10px] bg-rose-100 text-rose-600 px-1.5 py-0.5 rounded-full">Expired</span>@endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium {{ $statusCls }}">{{ ucfirst($sub->status) }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex gap-1">
                                @if(in_array($sub->status, ['active','pending']))
                                <button onclick="extendSubscription({{ $sub->id }})"
                                    class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] rounded-lg hover:bg-blue-100 transition-colors font-medium" title="Perpanjang">
                                    +Hari
                                </button>
                                <button onclick="cancelSubscription({{ $sub->id }}, this)"
                                    class="px-2 py-1 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-[10px] rounded-lg hover:bg-rose-100 transition-colors font-medium" title="Cancel">
                                    Cancel
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-stone-400 text-sm">Belum ada subscription.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
        <div class="px-6 py-4 border-t border-stone-100 dark:border-stone-800">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal: Add Plan --}}
<div id="modal-add-plan" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
    <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl border border-stone-200 dark:border-stone-800">
        <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
            <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="plan-modal-title">Tambah Paket</h3>
            <button onclick="closePlanModal()" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="plan-edit-id">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Nama Paket <span class="text-rose-400">*</span></label>
                    <input type="text" id="plan-name" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white" placeholder="Pro / Team / Premium">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Slug</label>
                    <input type="text" id="plan-slug" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white" placeholder="pro (auto dari nama)">
                    <p class="text-[10px] text-stone-400 mt-1">Harus cocok dengan plan user: 'pro', 'team', dsb</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Harga Bulanan (Rp) <span class="text-rose-400">*</span></label>
                    <input type="number" id="plan-price-monthly" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white" placeholder="99000" min="0">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Harga Tahunan (Rp)</label>
                    <input type="number" id="plan-price-yearly" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white" placeholder="990000" min="0">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                <textarea id="plan-description" rows="2" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white resize-none" placeholder="Deskripsi singkat paket ini..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Urutan (sort order)</label>
                <input type="number" id="plan-sort" class="w-32 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white" placeholder="1" min="0">
            </div>
        </div>
        <div class="flex gap-3 px-6 pb-6">
            <button onclick="closePlanModal()" class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
            <button onclick="savePlan(this)" class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-semibold transition-colors">
                <i class="fa-solid fa-floppy-disk mr-1.5"></i><span id="plan-save-label">Simpan Paket</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function openModal(id)  { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
function closePlanModal() { closeModal('modal-add-plan'); document.getElementById('plan-edit-id').value = ''; }

function toast(msg, ok=true) {
    const t = document.createElement('div');
    t.className = `fixed bottom-4 right-4 z-[9999] flex items-center gap-2 px-4 py-3 ${ok?'bg-emerald-500':'bg-rose-500'} text-white text-sm font-semibold rounded-xl shadow-xl`;
    t.innerHTML = `<i class="fa-solid ${ok?'fa-check-circle':'fa-circle-xmark'}"></i>${msg}`;
    document.body.appendChild(t);
    setTimeout(()=>{ t.style.transition='opacity .3s'; t.style.opacity='0'; setTimeout(()=>t.remove(),300); }, 2500);
}

function showAddPlanModal() {
    document.getElementById('plan-edit-id').value = '';
    document.getElementById('plan-name').value = '';
    document.getElementById('plan-slug').value = '';
    document.getElementById('plan-price-monthly').value = '';
    document.getElementById('plan-price-yearly').value = '';
    document.getElementById('plan-description').value = '';
    document.getElementById('plan-sort').value = '';
    document.getElementById('plan-modal-title').textContent = 'Tambah Paket';
    document.getElementById('plan-save-label').textContent = 'Simpan Paket';
    openModal('modal-add-plan');
}

async function editPlan(id) {
    try {
        const res  = await fetch(`/admin/subscriptions/plans/${id}`, { headers: {'Accept':'application/json','X-CSRF-TOKEN':CSRF} });
        const data = await res.json();
        const p    = data.plan;
        document.getElementById('plan-edit-id').value        = p.id;
        document.getElementById('plan-name').value           = p.name || '';
        document.getElementById('plan-slug').value           = p.slug || '';
        document.getElementById('plan-price-monthly').value  = p.price_monthly || '';
        document.getElementById('plan-price-yearly').value   = p.price_yearly || '';
        document.getElementById('plan-description').value    = p.description || '';
        document.getElementById('plan-sort').value           = p.sort_order || '';
        document.getElementById('plan-modal-title').textContent = 'Edit Paket';
        document.getElementById('plan-save-label').textContent  = 'Simpan Perubahan';
        openModal('modal-add-plan');
    } catch(e) { toast('Gagal memuat data plan.', false); }
}

async function savePlan(btn) {
    const name    = document.getElementById('plan-name').value.trim();
    const priceM  = document.getElementById('plan-price-monthly').value;
    if (!name) { toast('Nama paket wajib diisi!', false); return; }
    if (!priceM) { toast('Harga bulanan wajib diisi!', false); return; }

    const editId = document.getElementById('plan-edit-id').value;
    const payload = {
        name:          name,
        slug:          document.getElementById('plan-slug').value.trim() || null,
        price_monthly: parseFloat(priceM) || 0,
        price_yearly:  parseFloat(document.getElementById('plan-price-yearly').value) || 0,
        description:   document.getElementById('plan-description').value.trim() || null,
        sort_order:    parseInt(document.getElementById('plan-sort').value) || null,
    };

    btn.disabled = true;
    try {
        const url    = editId ? `/admin/subscriptions/plans/${editId}` : '/admin/subscriptions/plans';
        const method = editId ? 'PUT' : 'POST';
        const res    = await fetch(url, {
            method,
            headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message || 'Paket berhasil disimpan!');
            closePlanModal();
            setTimeout(() => location.reload(), 700);
        } else {
            // Tampilkan validation errors
            const errs = data.errors ? Object.values(data.errors).flat().join(', ') : data.message;
            toast(errs || 'Gagal menyimpan.', false);
        }
    } catch(e) { toast('Gagal menghubungi server.', false); }
    finally { btn.disabled = false; }
}

async function togglePlan(id, btn) {
    try {
        const res  = await fetch(`/admin/subscriptions/plans/${id}/toggle`, {
            method:'PATCH', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}
        });
        const data = await res.json();
        if (data.success) { toast('Status paket diperbarui.'); setTimeout(()=>location.reload(),700); }
        else toast(data.message||'Gagal.', false);
    } catch(e) { toast('Gagal.', false); }
}

async function deletePlan(id) {
    if (!confirm('Hapus paket ini? Tidak bisa dibatalkan.')) return;
    try {
        const res  = await fetch(`/admin/subscriptions/plans/${id}`, {
            method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}
        });
        const data = await res.json();
        if (data.success) { toast('Paket dihapus.'); setTimeout(()=>location.reload(),700); }
        else toast(data.message||'Gagal menghapus.', false);
    } catch(e) { toast('Gagal.', false); }
}

async function extendSubscription(id) {
    const days = prompt('Perpanjang berapa hari?', '30');
    if (!days || isNaN(days) || parseInt(days) <= 0) return;
    try {
        const res  = await fetch(`/admin/subscriptions/${id}/extend`, {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({days:parseInt(days)}),
        });
        const data = await res.json();
        if (data.success) { toast(data.message); setTimeout(()=>location.reload(),700); }
        else toast(data.message||'Gagal.', false);
    } catch(e) { toast('Gagal.', false); }
}

async function cancelSubscription(id, btn) {
    if (!confirm('Batalkan subscription ini?')) return;
    btn.disabled = true;
    try {
        const res  = await fetch(`/admin/subscriptions/${id}/cancel`, {
            method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}
        });
        const data = await res.json();
        if (data.success) {
            const row = document.getElementById('sub-row-'+id);
            if (row) { row.style.opacity='.5'; row.style.transition='opacity .3s'; }
            toast(data.message || 'Subscription dibatalkan.');
            setTimeout(()=>location.reload(),700);
        } else { toast(data.message||'Gagal.', false); btn.disabled=false; }
    } catch(e) { toast('Gagal.', false); btn.disabled=false; }
}
</script>
@endpush
