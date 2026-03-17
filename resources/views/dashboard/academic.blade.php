@extends('layouts.app-dashboard')

@section('title', 'Academic Hub - StudentHub')

@push('styles')
<style>
    .chart-container {
        position: relative;
        width: 100%;
        max-width: 100%;
        height: 300px;
        max-height: 400px;
        margin: 0 auto;
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto animate-fade-in-up">
    {{-- Header Section --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-stone-800 dark:text-white mb-2">Academic Hub</h1>
        <p class="text-stone-500 dark:text-stone-400">Manajemen akademik, jadwal kuliah, dan progress studi</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                    <i class="fa-solid fa-graduation-cap text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Total SKS</span>
            </div>
            <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $totalSks ?? 0 }}</div>
            <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">{{ $completedSks ?? 0 }} SKS selesai</div>
        </div>

        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                    <i class="fa-solid fa-book text-emerald-600 dark:text-emerald-400 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Mata Kuliah</span>
            </div>
            <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $subjects->count() ?? 0 }}</div>
            <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Aktif semester ini</div>
        </div>

        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                    <i class="fa-solid fa-file-lines text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-stone-500 dark:text-stone-400">IPK</span>
            </div>
            <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ number_format($gpa ?? 3.5, 2) }}</div>
            <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Semester {{ $currentSemester ?? 5 }}</div>
        </div>

        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                    <i class="fa-solid fa-clock text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Jadwal Hari Ini</span>
            </div>
            <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $todaySubjects->count() ?? 0 }}</div>
            <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">{{ $todayName ?? 'Hari ini' }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Today's Schedule --}}
        <div class="lg:col-span-2 bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-stone-800 dark:text-white">Jadwal Kuliah Hari Ini</h3>
                <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full text-sm font-medium">{{ $todayName ?? 'Senin' }}</span>
            </div>
            
            @if(isset($todaySubjects) && $todaySubjects->count() > 0)
                <div class="space-y-3">
                    @foreach($todaySubjects as $subject)
                        <div class="flex items-center gap-4 p-4 bg-stone-50 dark:bg-stone-700/50 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-700 transition-colors">
                            <div class="flex-shrink-0 w-16 text-center">
                                <div class="text-sm font-bold text-stone-800 dark:text-white">{{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }}</div>
                                <div class="text-xs text-stone-500 dark:text-stone-400">{{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}</div>
                            </div>
                            <div class="flex-shrink-0 w-px h-10 bg-stone-300 dark:bg-stone-600"></div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-stone-800 dark:text-white truncate">{{ $subject->name }}</h4>
                                <div class="flex items-center gap-2 text-sm text-stone-500 dark:text-stone-400 mt-1">
                                    <i class="fa-solid fa-door-open text-xs"></i>
                                    <span>{{ $subject->room ?? 'TBA' }}</span>
                                    <span class="mx-1">•</span>
                                    <i class="fa-solid fa-chalkboard-user text-xs"></i>
                                    <span>{{ $subject->lecturer ?? 'TBA' }}</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <div class="text-sm font-medium text-stone-600 dark:text-stone-300">{{ $subject->sks }} SKS</div>
                                <div class="text-xs text-stone-500 dark:text-stone-400">{{ $subject->code }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                    <i class="fa-solid fa-mug-hot text-4xl mb-3"></i>
                    <p>Tidak ada jadwal kuliah hari ini</p>
                    <p class="text-sm mt-1">Waktunya istirahat atau fokus tugas lain!</p>
                </div>
            @endif
        </div>

        {{-- Weekly Schedule --}}
        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4">Jadwal Mingguan</h3>
            <div class="space-y-2">
                @php
                    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    $todayIndex = now()->dayOfWeek == 0 ? 6 : now()->dayOfWeek - 1;
                @endphp
                @foreach($days as $index => $day)
                    @php
                        $daySubjects = $subjects->where('day_of_week', $day) ?? collect();
                        $isToday = $index === $todayIndex;
                    @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $isToday ? 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800' : 'bg-stone-50 dark:bg-stone-700/30' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $isToday ? 'bg-orange-500' : 'bg-stone-300 dark:bg-stone-600' }}"></div>
                            <span class="font-medium text-stone-700 dark:text-stone-300 {{ $isToday ? 'text-orange-700 dark:text-orange-400' : '' }}">{{ $day }}</span>
                        </div>
                        <span class="text-sm text-stone-500 dark:text-stone-400">{{ $daySubjects->count() }} matkul</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- All Subjects Table --}}
    <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-stone-800 dark:text-white">Semua Mata Kuliah</h3>
            <div class="flex gap-2">
                <button onclick="toggleSubjectView('list')" id="btn-list" class="px-3 py-1.5 text-sm rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400">
                    <i class="fa-solid fa-list mr-1"></i> List
                </button>
                <button onclick="toggleSubjectView('grid')" id="btn-grid" class="px-3 py-1.5 text-sm rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-400">
                    <i class="fa-solid fa-grid-2 mr-1"></i> Grid
                </button>
            </div>
        </div>

        <div id="subject-list-view" class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-stone-200 dark:border-stone-700">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-stone-600 dark:text-stone-400">Kode</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-stone-600 dark:text-stone-400">Mata Kuliah</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-stone-600 dark:text-stone-400">Hari</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-stone-600 dark:text-stone-400">Waktu</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-stone-600 dark:text-stone-400">Ruang</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-stone-600 dark:text-stone-400">SKS</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-stone-600 dark:text-stone-400">Dosen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects ?? [] as $subject)
                        <tr class="border-b border-stone-100 dark:border-stone-700/50 hover:bg-stone-50 dark:hover:bg-stone-700/30 transition-colors">
                            <td class="py-3 px-4 text-sm text-stone-600 dark:text-stone-400 font-mono">{{ $subject->code }}</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-stone-800 dark:text-white">{{ $subject->name }}</div>
                            </td>
                            <td class="py-3 px-4 text-sm text-stone-600 dark:text-stone-400">{{ $subject->day_of_week }}</td>
                            <td class="py-3 px-4 text-sm text-stone-600 dark:text-stone-400">
                                {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                            </td>
                            <td class="py-3 px-4 text-sm text-stone-600 dark:text-stone-400">{{ $subject->room ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded text-xs font-medium">{{ $subject->sks }} SKS</span>
                            </td>
                            <td class="py-3 px-4 text-sm text-stone-600 dark:text-stone-400">{{ $subject->lecturer ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-stone-500 dark:text-stone-400">
                                <i class="fa-solid fa-inbox text-3xl mb-3"></i>
                                <p>Belum ada data mata kuliah</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="subject-grid-view" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($subjects ?? [] as $subject)
                <div class="p-4 bg-stone-50 dark:bg-stone-700/30 rounded-xl border border-stone-200 dark:border-stone-700">
                    <div class="flex items-start justify-between mb-3">
                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded text-xs font-mono">{{ $subject->code }}</span>
                        <span class="text-xs text-stone-500 dark:text-stone-400">{{ $subject->sks }} SKS</span>
                    </div>
                    <h4 class="font-semibold text-stone-800 dark:text-white mb-2">{{ $subject->name }}</h4>
                    <div class="space-y-1 text-sm text-stone-600 dark:text-stone-400">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar text-xs w-4"></i>
                            <span>{{ $subject->day_of_week }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock text-xs w-4"></i>
                            <span>{{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-door-open text-xs w-4"></i>
                            <span>{{ $subject->room ?? 'TBA' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-chalkboard-user text-xs w-4"></i>
                            <span>{{ $subject->lecturer ?? 'TBA' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-stone-500 dark:text-stone-400">
                    <i class="fa-solid fa-inbox text-3xl mb-3"></i>
                    <p>Belum ada data mata kuliah</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSubjectView(view) {
        const listView = document.getElementById('subject-list-view');
        const gridView = document.getElementById('subject-grid-view');
        const btnList = document.getElementById('btn-list');
        const btnGrid = document.getElementById('btn-grid');

        if (view === 'list') {
            listView.classList.remove('hidden');
            gridView.classList.add('hidden');
            btnList.classList.add('bg-orange-100', 'dark:bg-orange-900/30', 'text-orange-700', 'dark:text-orange-400');
            btnList.classList.remove('bg-stone-100', 'dark:bg-stone-700', 'text-stone-600', 'dark:text-stone-400');
            btnGrid.classList.remove('bg-orange-100', 'dark:bg-orange-900/30', 'text-orange-700', 'dark:text-orange-400');
            btnGrid.classList.add('bg-stone-100', 'dark:bg-stone-700', 'text-stone-600', 'dark:text-stone-400');
        } else {
            listView.classList.add('hidden');
            gridView.classList.remove('hidden');
            btnGrid.classList.add('bg-orange-100', 'dark:bg-orange-900/30', 'text-orange-700', 'dark:text-orange-400');
            btnGrid.classList.remove('bg-stone-100', 'dark:bg-stone-700', 'text-stone-600', 'dark:text-stone-400');
            btnList.classList.remove('bg-orange-100', 'dark:bg-orange-900/30', 'text-orange-700', 'dark:text-orange-400');
            btnList.classList.add('bg-stone-100', 'dark:bg-stone-700', 'text-stone-600', 'dark:text-stone-400');
        }
    }
</script>
@endpush
@endsection
