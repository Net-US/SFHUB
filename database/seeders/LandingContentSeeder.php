<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingContent;

class LandingContentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['key' => 'hero_title',    'section' => 'hero',     'title' => 'Satu Platform untuk Semua Aktivitasmu',           'content' => 'Kelola kuliah, freelance, PKL, dan keuangan dalam satu dashboard yang terintegrasi.',    'icon' => null,                 'color' => null,                'sort_order' => 1],
            ['key' => 'hero_subtitle', 'section' => 'hero',     'title' => 'Khusus untuk Mahasiswa Aktif & Freelancer',         'content' => 'Dirancang untuk kamu yang menjalani dua peran sekaligus.',                               'icon' => null,                 'color' => null,                'sort_order' => 2],
            ['key' => 'feat_academic', 'section' => 'features', 'title' => 'Academic Hub',                                      'content' => 'Kelola mata kuliah, tugas, deadline, dan progres skripsi dalam satu tempat.',             'icon' => 'fa-graduation-cap',  'color' => 'text-blue-600',     'sort_order' => 1],
            ['key' => 'feat_creative', 'section' => 'features', 'title' => 'Creative Studio',                                   'content' => 'Kanban board untuk proyek freelance, konten YouTube, dan karya Shutterstock.',            'icon' => 'fa-film',            'color' => 'text-orange-600',   'sort_order' => 2],
            ['key' => 'feat_pkl',      'section' => 'features', 'title' => 'PKL Manager',                                       'content' => 'Catat aktivitas harian magang, jadwal, dan laporan mingguan dengan mudah.',              'icon' => 'fa-briefcase',       'color' => 'text-emerald-600',  'sort_order' => 3],
            ['key' => 'feat_finance',  'section' => 'features', 'title' => 'Finance Tracker',                                   'content' => 'Monitor pemasukan, pengeluaran, tabungan, dan investasi secara real-time.',              'icon' => 'fa-wallet',          'color' => 'text-amber-600',    'sort_order' => 4],
            ['key' => 'feat_calendar', 'section' => 'features', 'title' => 'Smart Calendar',                                    'content' => 'Kalender terintegrasi dengan jadwal PKL, kuliah, deadline, dan kegiatan rutin.',         'icon' => 'fa-calendar-days',   'color' => 'text-violet-600',   'sort_order' => 5],
            ['key' => 'feat_tracker',  'section' => 'features', 'title' => 'General Tracker',                                   'content' => 'Lacak semua aktivitas pribadi: kesehatan, perawatan, pengembangan diri, dan lainnya.',   'icon' => 'fa-list-check',      'color' => 'text-rose-600',     'sort_order' => 6],
            ['key' => 'feat_gantt',    'section' => 'features', 'title' => 'Focus Today (Gantt)',                               'content' => 'Timeline harian visual + Eisenhower Matrix untuk prioritas tugas yang lebih cerdas.',    'icon' => 'fa-timeline',        'color' => 'text-sky-600',      'sort_order' => 7],
            ['key' => 'feat_analytics','section' => 'features', 'title' => 'Analytics & Produktivitas',                        'content' => 'Grafik dan insight produktivitas mingguan berdasarkan data nyata aktivitasmu.',          'icon' => 'fa-chart-line',      'color' => 'text-indigo-600',   'sort_order' => 8],
            ['key' => 'stat_users',    'section' => 'stats',    'title' => '500+',                                              'content' => 'Mahasiswa aktif',                                                                        'icon' => 'fa-users',           'color' => 'text-orange-500',   'sort_order' => 1],
            ['key' => 'stat_tasks',    'section' => 'stats',    'title' => '10.000+',                                           'content' => 'Tugas diselesaikan',                                                                     'icon' => 'fa-check-circle',    'color' => 'text-emerald-500',  'sort_order' => 2],
            ['key' => 'stat_projects', 'section' => 'stats',    'title' => '2.500+',                                            'content' => 'Proyek freelance dikelola',                                                             'icon' => 'fa-film',            'color' => 'text-blue-500',     'sort_order' => 3],
            ['key' => 'stat_rating',   'section' => 'stats',    'title' => '4.9/5',                                             'content' => 'Rating kepuasan user',                                                                  'icon' => 'fa-star',            'color' => 'text-amber-500',    'sort_order' => 4],
        ];

        foreach ($items as $item) {
            LandingContent::firstOrCreate(['key' => $item['key']], array_merge($item, ['is_active' => true]));
        }
    }
}
