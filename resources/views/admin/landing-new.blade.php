@extends('layouts.app')

@section('title', 'Landing Content Management | SFHUB Admin')

@section('styles')
<style>
    .content-card {
        @apply bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 p-6;
    }
    .status-badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    .status-active {
        @apply bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400;
    }
    .status-inactive {
        @apply bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 dark:text-white">Landing Content Management</h1>
            <p class="text-stone-600 dark:text-stone-400 text-sm mt-0.5">Kelola konten landing page, SEO, dan pengaturan website</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="showAddContentModal()" class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Konten
            </button>
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-eye text-xs"></i> Preview Landing
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm border border-emerald-200 dark:border-emerald-800">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- SEO Settings -->
    <div class="content-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-stone-900 dark:text-white">SEO Settings</h3>
            <button onclick="showSeoModal()" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                <i class="fa-solid fa-edit mr-1"></i> Edit SEO
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">SEO Title</label>
                <div class="p-3 bg-stone-50 dark:bg-stone-700 rounded-lg border border-stone-200 dark:border-stone-600">
                    <p class="text-sm text-stone-900 dark:text-white">{{ $seoTitle->content ?? 'StudentHub | Platform All-in-One untuk Mahasiswa & Freelancer' }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">SEO Keywords</label>
                <div class="p-3 bg-stone-50 dark:bg-stone-700 rounded-lg border border-stone-200 dark:border-stone-600">
                    <p class="text-sm text-stone-900 dark:text-white">{{ $seoKeywords->content ?? 'studenthub, mahasiswa, freelancer, task management, produktivitas' }}</p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">SEO Description</label>
            <div class="p-3 bg-stone-50 dark:bg-stone-700 rounded-lg border border-stone-200 dark:border-stone-600">
                <p class="text-sm text-stone-900 dark:text-white">{{ $seoDesc->content ?? 'Platform all-in-one untuk mahasiswa dan freelancer. Kelola tugas, proyek, dan kehidupan pribadi dalam satu dashboard terintegrasi.' }}</p>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="content-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Hero Section</h3>
            <button onclick="showHeroModal()" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                <i class="fa-solid fa-edit mr-1"></i> Edit Hero
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Hero Title</label>
                <div class="p-3 bg-stone-50 dark:bg-stone-700 rounded-lg border border-stone-200 dark:border-stone-600">
                    <p class="text-sm text-stone-900 dark:text-white font-medium">{{ $heroContent->content ?? 'Seimbangkan Kuliah dan Karir Kreatif Tanpa Stress' }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Hero Subtitle</label>
                <div class="p-3 bg-stone-50 dark:bg-stone-700 rounded-lg border border-stone-200 dark:border-stone-600">
                    <p class="text-sm text-stone-900 dark:text-white">{{ $heroSubtitle->content ?? 'Platform All-in-One untuk Mahasiswa Kreatif' }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Hero Description</label>
                <div class="p-3 bg-stone-50 dark:bg-stone-700 rounded-lg border border-stone-200 dark:border-stone-600">
                    <p class="text-sm text-stone-900 dark:text-white">{{ $heroDesc->content ?? 'Sistem manajemen tugas pintar yang memahami jadwal sibuk mahasiswa.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="content-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Features Section</h3>
            <button onclick="showFeatureModal()" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Feature
            </button>
        </div>

        <div class="space-y-4">
            @foreach($features as $feature)
            <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-700 rounded-lg border border-stone-200 dark:border-stone-600">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white dark:bg-stone-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid {{ $feature->icon ?? 'fa-star' }} {{ $feature->color ?? 'text-orange-500' }}"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-stone-900 dark:text-white">{{ $feature->title }}</h4>
                        <p class="text-sm text-stone-600 dark:text-stone-400">{{ $feature->content }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="status-badge {{ $feature->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $feature->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs text-stone-500 dark:text-stone-400">Order: {{ $feature->sort_order }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="editFeature({{ $feature->id }})" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                    <button onclick="deleteFeature({{ $feature->id }})" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Stats Section -->
    <div class="content-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Stats Section</h3>
            <button onclick="showStatsModal()" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Stat
            </button>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($stats as $stat)
            <div class="text-center p-4 bg-gradient-to-br from-orange-500 to-rose-500 text-white rounded-lg">
                <p class="text-2xl font-bold">{{ $stat->title }}</p>
                <p class="text-sm opacity-90">{{ $stat->content }}</p>
                <div class="flex justify-center space-x-2 mt-2">
                    <button onclick="editStat({{ $stat->id }})" class="text-white/80 hover:text-white">
                        <i class="fa-solid fa-edit text-xs"></i>
                    </button>
                    <button onclick="deleteStat({{ $stat->id }})" class="text-white/80 hover:text-white">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Blog Management (Future Feature) -->
    <div class="content-card opacity-75">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Blog Management</h3>
                <p class="text-sm text-stone-600 dark:text-stone-400 mt-1">Coming soon - Manage blog posts and articles</p>
            </div>
            <span class="status-badge bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400">
                <i class="fa-solid fa-clock mr-1"></i> Planned
            </span>
        </div>

        <div class="text-center py-8">
            <i class="fa-solid fa-newspaper text-4xl text-stone-400 dark:text-stone-600 mb-4"></i>
            <p class="text-stone-600 dark:text-stone-400">Blog management system will be available in future updates</p>
            <p class="text-sm text-stone-500 dark:text-stone-500 mt-2">Features: Create posts, manage categories, SEO optimization, scheduling</p>
        </div>
    </div>
</div>

<!-- Modals (simplified for demo) -->
<script>
function showAddContentModal() {
    alert('Add Content Modal - Would open a form to create new landing content');
}

function showSeoModal() {
    alert('SEO Modal - Would open a form to edit SEO settings');
}

function showHeroModal() {
    alert('Hero Modal - Would open a form to edit hero section');
}

function showFeatureModal() {
    alert('Feature Modal - Would open a form to add new feature');
}

function editFeature(id) {
    alert('Edit Feature ' + id + ' - Would open edit form');
}

function deleteFeature(id) {
    if(confirm('Are you sure you want to delete this feature?')) {
        alert('Delete Feature ' + id + ' - Would send delete request');
    }
}

function showStatsModal() {
    alert('Stats Modal - Would open a form to add new stat');
}

function editStat(id) {
    alert('Edit Stat ' + id + ' - Would open edit form');
}

function deleteStat(id) {
    if(confirm('Are you sure you want to delete this stat?')) {
        alert('Delete Stat ' + id + ' - Would send delete request');
    }
}
</script>
@endsection
