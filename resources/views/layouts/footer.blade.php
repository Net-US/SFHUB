<footer
    class="py-16 border-t border-stone-200 dark:border-stone-800 bg-stone-900 dark:bg-black text-white transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <div>
                <div class="flex items-center mb-4">
                    @if (\App\Models\SiteSetting::getValue('site_logo'))
                        <img src="{{ \App\Models\SiteSetting::getValue('site_logo') }}" alt="StudentHub Logo"
                            class="h-10 w-auto mr-3">
                    @else
                        <i class="fa-solid fa-layer-group text-orange-400 mr-3 text-2xl"></i>
                    @endif
                    <span class="text-2xl font-black tracking-tighter">
                        Student<span class="text-orange-400">Hub</span>
                    </span>
                </div>
                <p class="text-stone-400 text-sm leading-relaxed">
                    Platform manajemen produktivitas untuk mahasiswa kreatif.
                    Membantu menemukan keseimbangan antara kuliah dan karir.
                </p>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-6">Produk</h4>
                <ul class="space-y-3 text-stone-400">
                    <li><a href="#fitur" class="hover:text-white transition-colors">Fitur</a></li>
                    <li><a href="#harga" class="hover:text-white transition-colors">Harga</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">API</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Status</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-6">Perusahaan</h4>
                <ul class="space-y-3 text-stone-400">
                    <li><a href="#" class="hover:text-white transition-colors">Tentang</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Karir</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Press Kit</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-6">Resources</h4>
                <ul class="space-y-3 text-stone-400">
                    <li><a href="#" class="hover:text-white transition-colors">Dokumentasi</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Community</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Support</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Security</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 pt-8 border-t border-stone-800 flex flex-col md:flex-row justify-between items-center">
            <p class="text-stone-500 text-sm">
                &copy; {{ date('Y') }} StudentHub. All rights reserved.
                Dibuat dengan ❤️ untuk mahasiswa kreatif Indonesia.
            </p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="text-stone-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-stone-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-stone-400 hover:text-white"><i class="fab fa-youtube"></i></a>
                <a href="#" class="text-stone-400 hover:text-white"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- Floating Action Button -->
<a href="#daftar"
    class="fixed bottom-8 right-8 w-14 h-14 bg-orange-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-orange-600 transition-all z-40">
    <i class="fa-solid fa-arrow-up text-xl"></i>
</a>
