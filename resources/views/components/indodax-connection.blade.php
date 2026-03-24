{{--
    Indodax Connection Component
    Taruh di halaman user settings/profile untuk mengelola koneksi Indodax API
    
    Usage: @include('components.indodax-connection')
--}}

<div x-data="indodaxConnection()" x-init="init()" class="space-y-4">
    
    {{-- Status Card --}}
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                 :class="connected ? 'bg-emerald-100 text-emerald-600' : 'bg-stone-100 text-stone-400'">
                <i class="fa-brands fa-bitcoin text-lg"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-stone-900 dark:text-white">Indodax Connection</h3>
                <p class="text-xs text-stone-500" x-text="connected ? 'Terhubung' : 'Belum terhubung'"></p>
            </div>
            <span x-show="connected" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                Connected
            </span>
        </div>

        {{-- Connection Info --}}
        <div x-show="connected" x-transition class="mb-4 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl text-sm">
            <div class="flex justify-between items-center mb-2">
                <span class="text-stone-600 dark:text-stone-400">API Key:</span>
                <code class="text-xs font-mono text-stone-800 dark:text-stone-200" x-text="apiKeyPreview"></code>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-stone-600 dark:text-stone-400">Last Synced:</span>
                <span class="text-xs text-stone-700 dark:text-stone-300" x-text="lastSynced || 'Belum pernah'"></span>
            </div>
        </div>

        {{-- Form Input API Key & Secret --}}
        <div x-show="!connected || showForm" x-transition class="space-y-3 mb-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">
                    API Key
                    <a href="https://indodax.com/trade_api" target="_blank" class="text-xs text-blue-600 hover:underline ml-2">
                        <i class="fa-solid fa-external-link"></i> Dapatkan di sini
                    </a>
                </label>
                <input type="text" x-model="apiKey" placeholder="14B0C9D6-UG71XOID-SH4IB5VQ-..."
                    class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-stone-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">
                    Secret Key
                    <span class="text-xs text-stone-400">(akan dienkripsi)</span>
                </label>
                <div class="relative">
                    <input :type="showSecret ? 'text' : 'password'" x-model="apiSecret"
                        placeholder="••••••••••••••••••••"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-stone-800 dark:text-white">
                    <button type="button" @click="showSecret = !showSecret"
                        class="absolute inset-y-0 right-0 px-3 text-stone-400 hover:text-stone-600">
                        <i :class="showSecret ? 'fa-eye-slash' : 'fa-eye'" class="fa-regular"></i>
                    </button>
                </div>
            </div>
            <p class="text-xs text-amber-600 dark:text-amber-400 flex items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <span>Pastikan API Key memiliki permission <strong>view</strong> minimal. Jangan share API Secret ke siapapun.</span>
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-2">
            <button x-show="!connected" @click="connect()" :disabled="loading"
                class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                <i class="fa-solid fa-plug mr-1"></i>
                <span x-text="loading ? 'Menghubungkan...' : 'Hubungkan'"></span>
            </button>

            <button x-show="connected && !showForm" @click="showForm = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                <i class="fa-solid fa-pen mr-1"></i> Edit
            </button>

            <button x-show="connected && showForm" @click="connect()" :disabled="loading"
                class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                <i class="fa-solid fa-save mr-1"></i>
                <span x-text="loading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
            </button>

            <button x-show="connected" @click="testConnection()" :disabled="loading"
                class="px-4 py-2 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium hover:bg-stone-50 dark:hover:bg-stone-800 disabled:opacity-50 transition-colors">
                <i class="fa-solid fa-vial mr-1"></i>
                <span x-text="loading ? 'Testing...' : 'Test Koneksi'"></span>
            </button>

            <button x-show="connected" @click="syncBalances()" :disabled="loading"
                class="px-4 py-2 bg-orange-600 text-white rounded-xl text-sm font-medium hover:bg-orange-700 disabled:opacity-50 transition-colors">
                <i class="fa-solid fa-rotate mr-1" :class="loading && 'fa-spin'"></i>
                <span x-text="loading ? 'Syncing...' : 'Sync Balances'"></span>
            </button>

            <button x-show="connected" @click="disconnect()" :disabled="loading"
                class="px-4 py-2 border border-red-300 text-red-600 rounded-xl text-sm font-medium hover:bg-red-50 disabled:opacity-50 transition-colors">
                <i class="fa-solid fa-unlink mr-1"></i> Putuskan
            </button>
        </div>

        {{-- Status Messages --}}
        <div x-show="message" x-transition class="mt-4 p-3 rounded-xl text-sm"
             :class="messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'">
            <div class="flex items-start gap-2">
                <i :class="messageType === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'" class="fa-solid mt-0.5"></i>
                <span x-text="message"></span>
            </div>
        </div>

        {{-- Sync Result --}}
        <div x-show="syncResult" x-transition class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <p class="font-semibold text-blue-800 dark:text-blue-300 text-sm mb-2">
                <i class="fa-solid fa-circle-check mr-1"></i> Sync Berhasil!
            </p>
            <div class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                <p><strong>Total Coins:</strong> <span x-text="syncResult.synced_coins?.length || 0"></span></p>
                <p><strong>Total Value:</strong> Rp <span x-text="formatNumber(syncResult.total_value_idr)"></span></p>
                <p><strong>Synced At:</strong> <span x-text="new Date(syncResult.synced_at).toLocaleString('id-ID')"></span></p>
            </div>
            <a href="{{ route('dashboard.investments') }}" class="inline-block mt-3 text-xs font-medium text-blue-600 hover:underline">
                <i class="fa-solid fa-arrow-right mr-1"></i> Lihat di Investment Dashboard
            </a>
        </div>
    </div>

    {{-- Help Guide --}}
    <details class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-4">
        <summary class="text-sm font-semibold text-blue-800 dark:text-blue-300 cursor-pointer">
            <i class="fa-solid fa-circle-info mr-1"></i> Cara menghubungkan Indodax
        </summary>
        <ol class="mt-3 text-sm text-blue-700 dark:text-blue-400 space-y-2 pl-4 list-decimal">
            <li>Login ke akun Indodax kamu di <a href="https://indodax.com" target="_blank" class="underline">indodax.com</a></li>
            <li>Buka halaman <a href="https://indodax.com/trade_api" target="_blank" class="underline font-medium">Trade API</a></li>
            <li>Buat API Key baru dengan permission minimal <strong>view</strong> (untuk lihat balance)</li>
            <li>Salin <strong>API Key</strong> dan <strong>Secret Key</strong>, lalu paste di form di atas</li>
            <li>Klik <strong>Hubungkan</strong>, lalu klik <strong>Sync Balances</strong> untuk import data crypto kamu</li>
            <li>Data akan muncul otomatis di <a href="{{ route('dashboard.investments') }}" class="underline">Investment Dashboard</a></li>
        </ol>
        <p class="mt-3 text-xs text-amber-700 dark:text-amber-400">
            <i class="fa-solid fa-shield-halved mr-1"></i>
            <strong>Keamanan:</strong> Secret Key dienkripsi sebelum disimpan. Kami tidak pernah menyimpan password Indodax kamu.
        </p>
    </details>

</div>

@push('scripts')
<script>
function indodaxConnection() {
    return {
        connected: false,
        loading: false,
        showForm: false,
        showSecret: false,
        apiKey: '',
        apiSecret: '',
        apiKeyPreview: '',
        lastSynced: '',
        message: '',
        messageType: 'success',
        syncResult: null,

        async init() {
            await this.checkStatus();
        },

        async checkStatus() {
            try {
                const res = await fetch('{{ route('indodax.status') }}');
                const data = await res.json();
                this.connected = data.connected || false;
                if (this.connected) {
                    this.apiKeyPreview = data.api_key_preview || '';
                    this.lastSynced = data.last_synced_at || '';
                }
            } catch (e) {
                console.error('Failed to check Indodax status', e);
            }
        },

        async connect() {
            if (!this.apiKey || !this.apiSecret) {
                this.showMessage('API Key dan Secret Key harus diisi.', 'error');
                return;
            }

            this.loading = true;
            this.message = '';

            try {
                const res = await fetch('{{ route('indodax.connect') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        api_key: this.apiKey,
                        api_secret: this.apiSecret,
                    }),
                });

                const data = await res.json();

                if (data.success) {
                    this.showMessage(data.message, 'success');
                    this.connected = true;
                    this.showForm = false;
                    this.apiKey = '';
                    this.apiSecret = '';
                    await this.checkStatus();
                } else {
                    this.showMessage(data.message || 'Gagal menyimpan koneksi.', 'error');
                }
            } catch (e) {
                this.showMessage('Error: ' + e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        async testConnection() {
            this.loading = true;
            this.message = '';

            try {
                const res = await fetch('{{ route('indodax.test') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const data = await res.json();
                this.showMessage(data.message, data.success ? 'success' : 'error');
            } catch (e) {
                this.showMessage('Error: ' + e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        async syncBalances() {
            this.loading = true;
            this.message = '';
            this.syncResult = null;

            try {
                const res = await fetch('{{ route('indodax.sync') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const data = await res.json();

                if (data.success) {
                    this.syncResult = data;
                    this.showMessage('Sync berhasil! Data crypto kamu sudah diupdate.', 'success');
                    await this.checkStatus();
                } else {
                    this.showMessage(data.error || 'Sync gagal.', 'error');
                }
            } catch (e) {
                this.showMessage('Error: ' + e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        async disconnect() {
            if (!confirm('Yakin ingin memutuskan koneksi Indodax? Data investasi yang sudah di-sync tidak akan dihapus.')) {
                return;
            }

            this.loading = true;
            this.message = '';

            try {
                const res = await fetch('{{ route('indodax.disconnect') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const data = await res.json();

                if (data.success) {
                    this.showMessage(data.message, 'success');
                    this.connected = false;
                    this.apiKeyPreview = '';
                    this.lastSynced = '';
                } else {
                    this.showMessage(data.message || 'Gagal memutuskan koneksi.', 'error');
                }
            } catch (e) {
                this.showMessage('Error: ' + e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => { this.message = ''; }, 5000);
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        },
    };
}
</script>
@endpush
