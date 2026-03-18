@extends('layouts.app')
@section('title', 'Landing Content Management | SFHUB Admin')

@section('content')
    <div class="space-y-5">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Konten Landing Page</h2>
                <p class="text-stone-400 text-xs">Kelola konten fitur, hero, dan informasi di halaman utama</p>
            </div>
            <button onclick="document.getElementById('modal-add-content').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Konten
            </button>
        </div>

        @if (session('success'))
            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm">
                <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Content table --}}
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                        <tr>
                            @foreach (['Key', 'Section', 'Title', 'Content', 'Icon', 'Order', 'Status', 'Aksi'] as $h)
                                <th
                                    class="text-left py-3 px-4 text-stone-500 dark:text-stone-400 font-medium whitespace-nowrap">
                                    {{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                        @forelse($allContent as $c)
                            <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                <td class="py-3 px-4 font-mono text-xs text-stone-500">{{ $c->key }}</td>
                                <td class="py-3 px-4"><span
                                        class="px-2 py-0.5 rounded-full text-[11px] bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-semibold">{{ $c->section }}</span>
                                </td>
                                <td class="py-3 px-4 font-medium text-stone-800 dark:text-white max-w-32 truncate">
                                    {{ $c->title ?? '-' }}</td>
                                <td class="py-3 px-4 text-stone-500 dark:text-stone-400 max-w-48 truncate">
                                    {{ $c->content ?? '-' }}</td>
                                <td class="py-3 px-4 text-stone-500">{{ $c->icon ?? '-' }}</td>
                                <td class="py-3 px-4 text-stone-500">{{ $c->sort_order }}</td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $c->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-stone-100 dark:bg-stone-700 text-stone-500' }}">
                                        {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1">
                                        <form method="POST" action="{{ route('admin.landing.update', $c) }}"
                                            class="inline-flex gap-1">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $c->is_active ? 0 : 1 }}">
                                            <button type="submit"
                                                class="px-2 py-1 rounded-lg text-[11px] bg-stone-100 dark:bg-stone-700 text-stone-500 hover:bg-orange-100 hover:text-orange-600 transition-colors">
                                                {{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.landing.destroy', $c) }}"
                                            onsubmit="return confirm('Hapus konten ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="px-2 py-1 rounded-lg text-[11px] bg-stone-100 dark:bg-stone-700 text-stone-500 hover:bg-rose-100 hover:text-rose-600 transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-stone-400">Belum ada konten. Klik Tambah
                                    Konten untuk memulai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- MODAL: ADD CONTENT --}}
    <div id="modal-add-content"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Tambah Konten</h3>
                <button onclick="document.getElementById('modal-add-content').classList.add('hidden')"
                    class="text-stone-400 hover:text-stone-700">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.landing.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Key (unik)
                            *</label>
                        <input type="text" name="key" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="feature_academic">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Section
                            *</label>
                        <select name="section"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="features">Features</option>
                            <option value="hero">Hero</option>
                            <option value="stats">Stats</option>
                            <option value="faq">FAQ</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Title</label>
                    <input type="text" name="title"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Judul konten">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deskripsi /
                        Konten</label>
                    <textarea name="content" rows="3"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white resize-none"
                        placeholder="Deskripsi konten..."></textarea>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Icon (FA
                            class)</label>
                        <input type="text" name="icon"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm dark:bg-stone-800 dark:text-white"
                            placeholder="fa-graduation-cap">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Warna
                            (Tailwind)</label>
                        <input type="text" name="color"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm dark:bg-stone-800 dark:text-white"
                            placeholder="text-blue-600">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Urutan</label>
                        <input type="number" name="sort_order" value="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm dark:bg-stone-800 dark:text-white">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-content').classList.add('hidden')"
                        class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-semibold transition-colors">
                        Simpan Konten
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
