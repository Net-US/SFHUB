<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingHero;
use App\Models\LandingFeature;
use App\Models\LandingTestimonial;
use App\Models\LandingStat;

class LandingPageSeeder extends Seeder
{
    public function run()
    {
        // Create Hero Section
        $hero = LandingHero::create([
            'title' => 'Seimbangkan <span class="text-orange-500">Kuliah</span> dan <span class="text-orange-500">Karir Kreatif</span> Tanpa Stress',
            'subtitle' => 'Sistem manajemen tugas pintar yang memahami jadwal sibuk mahasiswa. Atur proyek kreatif, tugas kampus, freelance, dan kehidupan pribadi dalam satu dashboard terintegrasi.',
            'cta_text' => 'Mulai Gratis 30 Hari',
            'cta_link' => route('register'),
            'hero_image' => null,
            'is_active' => true,
        ]);

        // Create Features
        $features = [
            [
                'title' => 'Academic Hub',
                'description' => 'Kelola mata kuliah, tugas, deadline, dan progres skripsi dengan sistem terintegrasi.',
                'icon' => 'fa-graduation-cap',
                'is_active' => true,
            ],
            [
                'title' => 'Creative Studio',
                'description' => 'Kanban board proyek freelance, konten kreator, dan manajemen Shutterstock.',
                'icon' => 'fa-film',
                'is_active' => true,
            ],
            [
                'title' => 'PKL Manager',
                'description' => 'Catat aktivitas harian magang dan buat laporan mingguan otomatis.',
                'icon' => 'fa-briefcase',
                'is_active' => true,
            ],
            [
                'title' => 'Finance Tracker',
                'description' => 'Monitor pemasukan, pengeluaran, dan investasi dalam satu dashboard.',
                'icon' => 'fa-wallet',
                'is_active' => true,
            ],
            [
                'title' => 'Smart Calendar',
                'description' => 'Kalender terintegrasi untuk semua jadwal kuliah, deadline, dan kegiatan.',
                'icon' => 'fa-calendar-days',
                'is_active' => true,
            ],
            [
                'title' => 'General Tracker',
                'description' => 'Lacak aktivitas kesehatan, personal, dan pengembangan diri.',
                'icon' => 'fa-list-check',
                'is_active' => true,
            ],
            [
                'title' => 'Focus Today',
                'description' => 'Timeline harian + Eisenhower Matrix untuk prioritas cerdas.',
                'icon' => 'fa-timeline',
                'is_active' => true,
            ],
            [
                'title' => 'Analytics',
                'description' => 'Insight produktivitas mingguan dari data nyata aktivitasmu.',
                'icon' => 'fa-chart-line',
                'is_active' => true,
            ],
        ];

        foreach ($features as $feature) {
            LandingFeature::create($feature);
        }

        // Create Testimonials
        $testimonials = [
            [
                'name' => 'Sarah Anderson',
                'role' => 'Mahasiswa Sistem Informasi',
                'content' => 'SFHUB mengubah cara saya mengelola waktu. Dulu selalu kewalahan antara skripsi dan freelance, sekarang semua terorganisir dengan baik.',
                'rating' => 5,
                'avatar' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Budi Pratama',
                'role' => 'Freelancer Video Editor',
                'content' => 'Kanban board untuk proyek freelance sangat membantu. Saya bisa track deadline klien sambil jaga kuliah tetap on track.',
                'rating' => 5,
                'avatar' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Maya Putri',
                'role' => 'Mahasiswa PKL',
                'content' => 'Fitur laporan PKL otomatis menghemat waktu saya 10 jam per minggu. Tinggal input aktivitas, laporan jadi sendiri!',
                'rating' => 4,
                'avatar' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Rizky Ahmad',
                'role' => 'Content Creator',
                'content' => 'Sebagai content creator yang masih kuliah, SFHUB membantu saya balance antara deadline klien dan tugas kampus.',
                'rating' => 5,
                'avatar' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Lisa Wijaya',
                'role' => 'Mahasiswa Manajemen',
                'content' => 'Fitur finance tracking membantu saya mengatur uang sakuku dan income dari part-time job. Sangat recommended!',
                'rating' => 4,
                'avatar' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Kevin Chen',
                'role' => 'Web Developer Freelance',
                'content' => 'Dashboard analytics memberi insight produktivitas yang saya butuhkan untuk improve time management.',
                'rating' => 5,
                'avatar' => null,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            LandingTestimonial::create($testimonial);
        }

        // Create Stats
        $stats = [
            [
                'label' => 'Mahasiswa Aktif',
                'value' => '5,000+',
                'icon' => 'fa-users',
                'is_active' => true,
            ],
            [
                'label' => 'Tugas Diselesaikan',
                'value' => '10,000+',
                'icon' => 'fa-check-circle',
                'is_active' => true,
            ],
            [
                'label' => 'Proyek Freelance',
                'value' => '2,500+',
                'icon' => 'fa-briefcase',
                'is_active' => true,
            ],
            [
                'label' => 'Rating Kepuasan',
                'value' => '4.9/5',
                'icon' => 'fa-star',
                'is_active' => true,
            ],
        ];

        foreach ($stats as $stat) {
            LandingStat::create($stat);
        }

        $this->command->info('Landing page data seeded successfully!');
    }
}
