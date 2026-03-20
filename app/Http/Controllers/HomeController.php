<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LandingHero;
use App\Models\LandingFeature;
use App\Models\LandingTestimonial;
use App\Models\LandingStat;

class HomeController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index()
    {
        // Get landing data or use defaults
        $hero = LandingHero::active()->first();

        // If no hero data, create default hero object
        if (!$hero) {
            $hero = (object) [
                'title' => 'Seimbangkan <span class="text-orange-500">Kuliah</span> dan <span class="text-orange-500">Karir Kreatif</span> Tanpa Stress',
                'subtitle' => 'Sistem manajemen tugas pintar yang memahami jadwal sibuk mahasiswa. Atur proyek kreatif, tugas kampus, freelance, dan kehidupan pribadi dalam satu dashboard terintegrasi.',
                'cta_text' => 'Mulai Gratis 30 Hari',
                'cta_link' => route('register'),
                'hero_image' => null,
            ];
        }

        $features = LandingFeature::active()->get();

        // If no features, create default features
        if ($features->isEmpty()) {
            $features = collect([
                (object) ['title' => 'Academic Hub', 'description' => 'Kelola mata kuliah, tugas, deadline, dan progres skripsi dengan sistem terintegrasi.', 'icon' => 'fa-graduation-cap'],
                (object) ['title' => 'Creative Studio', 'description' => 'Kanban board proyek freelance, konten kreator, dan manajemen Shutterstock.', 'icon' => 'fa-film'],
                (object) ['title' => 'PKL Manager', 'description' => 'Catat aktivitas harian magang dan buat laporan mingguan otomatis.', 'icon' => 'fa-briefcase'],
                (object) ['title' => 'Finance Tracker', 'description' => 'Monitor pemasukan, pengeluaran, dan investasi dalam satu dashboard.', 'icon' => 'fa-wallet'],
                (object) ['title' => 'Smart Calendar', 'description' => 'Kalender terintegrasi untuk semua jadwal kuliah, deadline, dan kegiatan.', 'icon' => 'fa-calendar-days'],
                (object) ['title' => 'General Tracker', 'description' => 'Lacak aktivitas kesehatan, personal, dan pengembangan diri.', 'icon' => 'fa-list-check'],
                (object) ['title' => 'Focus Today', 'description' => 'Timeline harian + Eisenhower Matrix untuk prioritas cerdas.', 'icon' => 'fa-timeline'],
                (object) ['title' => 'Analytics', 'description' => 'Insight produktivitas mingguan dari data nyata aktivitasmu.', 'icon' => 'fa-chart-line'],
            ]);
        }

        $testimonials = LandingTestimonial::active()->get();

        // If no testimonials, create default testimonials
        if ($testimonials->isEmpty()) {
            $testimonials = collect([
                (object) ['name' => 'Sarah Anderson', 'role' => 'Mahasiswa Sistem Informasi', 'content' => 'SFHUB mengubah cara saya mengelola waktu. Dulu selalu kewalahan antara skripsi dan freelance, sekarang semua terorganisir dengan baik.', 'rating' => 5, 'avatar' => null],
                (object) ['name' => 'Budi Pratama', 'role' => 'Freelancer Video Editor', 'content' => 'Kanban board untuk proyek freelance sangat membantu. Saya bisa track deadline klien sambil jaga kuliah tetap on track.', 'rating' => 5, 'avatar' => null],
                (object) ['name' => 'Maya Putri', 'role' => 'Mahasiswa PKL', 'content' => 'Fitur laporan PKL otomatis menghemat waktu saya 10 jam per minggu. Tinggal input aktivitas, laporan jadi sendiri!', 'rating' => 4, 'avatar' => null],
                (object) ['name' => 'Rizky Ahmad', 'role' => 'Content Creator', 'content' => 'Sebagai content creator yang masih kuliah, SFHUB membantu saya balance antara deadline klien dan tugas kampus.', 'rating' => 5, 'avatar' => null],
                (object) ['name' => 'Lisa Wijaya', 'role' => 'Mahasiswa Manajemen', 'content' => 'Fitur finance tracking membantu saya mengatur uang sakuku dan income dari part-time job. Sangat recommended!', 'rating' => 4, 'avatar' => null],
                (object) ['name' => 'Kevin Chen', 'role' => 'Web Developer Freelance', 'content' => 'Dashboard analytics memberi insight produktivitas yang saya butuhkan untuk improve time management.', 'rating' => 5, 'avatar' => null],
            ]);
        }

        $stats = LandingStat::active()->get();

        // If no stats, create default stats
        if ($stats->isEmpty()) {
            $stats = collect([
                (object) ['label' => 'Mahasiswa Aktif', 'value' => '5,000+', 'icon' => 'fa-users'],
                (object) ['label' => 'Tugas Diselesaikan', 'value' => '10,000+', 'icon' => 'fa-check-circle'],
                (object) ['label' => 'Proyek Freelance', 'value' => '2,500+', 'icon' => 'fa-briefcase'],
                (object) ['label' => 'Rating Kepuasan', 'value' => '4.9/5', 'icon' => 'fa-star'],
            ]);
        }

        return view('home', compact('hero', 'features', 'testimonials', 'stats'));
    }
}
