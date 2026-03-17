{{-- resources/views/donation/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Donasi untuk StudentHub')

@section('content')
    <section class="min-h-screen flex items-center justify-center py-20 hero-gradient">
        <div class="max-w-lg w-full mx-4">
            <div class="form-glass p-8 md:p-10 rounded-3xl shadow-xl">
                <div class="text-center mb-8">
                    <div
                        class="w-20 h-20 mx-auto mb-4 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-heart text-orange-500 text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-stone-900 dark:text-white mb-2">Terima Kasih Telah Bergabung!</h2>
                    <p class="text-stone-600 dark:text-stone-400">
                        Untuk mengaktifkan paket <strong class="text-orange-500">{{ ucfirst($plan) }}</strong>,
                        silakan lakukan donasi sukarela.
                    </p>
                </div>

                <form method="POST" action="{{ route('donation.process') }}" class="space-y-6">
                    @csrf

                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-xl">
                        <div class="text-center">
                            <p class="text-sm text-stone-600 dark:text-stone-400 mb-2">Saran Donasi:</p>
                            <p class="text-3xl font-bold text-orange-500">Rp {{ number_format($amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Untuk 30 hari akses premium</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Jumlah Donasi (Rp)
                        </label>
                        <input type="number" name="donation_amount" value="{{ $amount }}" min="1000" required
                            class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all"
                            placeholder="Masukkan jumlah donasi" />
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Minimum Rp 1.000</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Metode Pembayaran
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                class="border-2 border-stone-200 dark:border-stone-600 rounded-xl p-4 cursor-pointer hover:border-orange-300 transition-colors">
                                <input type="radio" name="payment_method" value="bank_transfer" class="sr-only" checked />
                                <div class="flex items-center justify-center">
                                    <i class="fa-solid fa-building-columns text-blue-500 text-xl mr-2"></i>
                                    <span>Transfer Bank</span>
                                </div>
                            </label>
                            <label
                                class="border-2 border-stone-200 dark:border-stone-600 rounded-xl p-4 cursor-pointer hover:border-orange-300 transition-colors">
                                <input type="radio" name="payment_method" value="ovo" class="sr-only" />
                                <div class="flex items-center justify-center">
                                    <i class="fa-solid fa-mobile-screen text-purple-500 text-xl mr-2"></i>
                                    <span>OVO</span>
                                </div>
                            </label>
                            <label
                                class="border-2 border-stone-200 dark:border-stone-600 rounded-xl p-4 cursor-pointer hover:border-orange-300 transition-colors">
                                <input type="radio" name="payment_method" value="gopay" class="sr-only" />
                                <div class="flex items-center justify-center">
                                    <i class="fa-solid fa-wallet text-green-500 text-xl mr-2"></i>
                                    <span>GoPay</span>
                                </div>
                            </label>
                            <label
                                class="border-2 border-stone-200 dark:border-stone-600 rounded-xl p-4 cursor-pointer hover:border-orange-300 transition-colors">
                                <input type="radio" name="payment_method" value="dana" class="sr-only" />
                                <div class="flex items-center justify-center">
                                    <i class="fa-solid fa-money-bill-wave text-blue-400 text-xl mr-2"></i>
                                    <span>DANA</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-stone-50 dark:bg-stone-800 p-4 rounded-xl">
                        <h4 class="font-bold text-stone-800 dark:text-white mb-2">Informasi Rekening Donasi</h4>
                        <div class="space-y-2 text-sm">
                            <p><strong>Bank:</strong> BCA (Bank Central Asia)</p>
                            <p><strong>Nomor Rekening:</strong> 123-456-7890</p>
                            <p><strong>Atas Nama:</strong> STUDENTHUB INDONESIA</p>
                            <p class="text-xs text-stone-500 dark:text-stone-400 mt-2">
                                Setelah transfer, sistem akan otomatis mendeteksi dan mengaktifkan paket Anda dalam 1x24
                                jam.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="confirm" id="confirm" required
                            class="rounded border-stone-300 text-orange-500 focus:ring-orange-500" />
                        <label for="confirm" class="ml-2 text-sm text-stone-700 dark:text-stone-400">
                            Saya memahami bahwa donasi ini bersifat sukarela dan tidak dapat dikembalikan
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 transition-all shadow-lg">
                        Lanjutkan Donasi
                    </button>

                    <div class="text-center">
                        <a href="{{ route('dashboard') }}"
                            class="text-sm text-stone-600 dark:text-stone-400 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Lewati donasi (gunakan paket gratis)
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
