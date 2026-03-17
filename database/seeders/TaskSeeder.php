<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;
use App\Models\Task;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@sfhub.dev')->first();
        if (!$user) return;

        $subjects = Subject::where('user_id', $user->id)->get()->keyBy('code');

        // Academic tasks
        $academicTasks = [
            ['title' => 'Bab 2 Review Literatur',       'category' => 'academic', 'task_type' => 'assignment', 'due_date' => now()->addDays(3),  'status' => 'doing', 'priority' => 'urgent-important',         'notes' => '',                              'linked_subject_id' => $subjects->get('IF401')?->id],
            ['title' => 'Tugas Filter Gambar (OpenCV)',  'category' => 'academic', 'task_type' => 'lab',        'due_date' => now()->addDays(5),  'status' => 'todo',  'priority' => 'urgent-important',         'notes' => 'Submit ke Google Classroom',    'linked_subject_id' => $subjects->get('IF405')?->id],
            ['title' => 'Bimbingan Bab 1',               'category' => 'skripsi',  'task_type' => 'skripsi',    'due_date' => now()->addDays(1),  'status' => 'todo',  'priority' => 'urgent-important',         'notes' => 'Siapkan revisi dosen',          'linked_subject_id' => $subjects->get('IF499')?->id],
            ['title' => 'Quiz Distributed Systems',      'category' => 'academic', 'task_type' => 'quiz',       'due_date' => now()->addDays(7),  'status' => 'todo',  'priority' => 'important-not-urgent',     'notes' => 'Bab 3-5',                       'linked_subject_id' => $subjects->get('IF403')?->id],
            ['title' => 'Proposal Penelitian',           'category' => 'academic', 'task_type' => 'assignment', 'due_date' => now()->subDays(2),  'status' => 'done',  'priority' => 'urgent-important',         'notes' => 'Sudah dikumpulkan',             'linked_subject_id' => $subjects->get('IF401')?->id],
        ];

        foreach ($academicTasks as $t) {
            if (!Task::where('user_id', $user->id)->where('title', $t['title'])->exists()) {
                $user->tasks()->create($t);
            }
        }

        // Creative tasks
        $creativeTasks = [
            ['title' => 'Explainer Video Klien A',    'category' => 'Creative', 'project_type' => 'Freelance',    'workflow_stage' => 'production', 'due_date' => now()->addDays(10), 'status' => 'doing', 'priority' => 'urgent-important',     'progress' => 60, 'tags' => ['After Effects', 'Motion']],
            ['title' => 'Intro Youtube Channel',       'category' => 'Creative', 'project_type' => 'Personal',     'workflow_stage' => 'script',     'due_date' => now()->addDays(30), 'status' => 'todo',  'priority' => 'important-not-urgent', 'progress' => 10, 'tags' => ['Premiere', 'Audio']],
            ['title' => 'Asset Microstock Pack 1',     'category' => 'Creative', 'project_type' => 'Shutterstock', 'workflow_stage' => 'revision',   'due_date' => now()->addDays(5),  'status' => 'doing', 'priority' => 'urgent-important',     'progress' => 80, 'tags' => ['Illustrator', 'EPS']],
            ['title' => 'Motion Logo Klien B',         'category' => 'Creative', 'project_type' => 'Freelance',    'workflow_stage' => 'script',     'due_date' => now()->addDays(15), 'status' => 'todo',  'priority' => 'not-urgent-not-important','progress' => 5, 'tags' => ['After Effects']],
            ['title' => 'Template Sosmed Pack',        'category' => 'Creative', 'project_type' => 'Shutterstock', 'workflow_stage' => 'done',       'due_date' => now()->subDays(5),  'status' => 'done',  'priority' => 'not-urgent-not-important','progress' => 100,'tags' => ['Figma', 'Canva']],
        ];

        foreach ($creativeTasks as $t) {
            if (!Task::where('user_id', $user->id)->where('title', $t['title'])->exists()) {
                $user->tasks()->create($t);
            }
        }

        // General / personal tasks
        $generalTasks = [
            ['title' => 'Olahraga Pagi',               'category' => 'Kesehatan',         'due_date' => now()->subDay(),    'status' => 'done', 'priority' => 'important-not-urgent', 'estimated_time' => '00:30:00'],
            ['title' => 'Baca Artikel Riset UI/UX',    'category' => 'Pengembangan Diri', 'due_date' => now()->subDay(),    'status' => 'done', 'priority' => 'important-not-urgent', 'estimated_time' => '00:45:00'],
            ['title' => 'Beli Alat Tulis',             'category' => 'Personal',          'due_date' => now(),              'status' => 'todo', 'priority' => 'not-urgent-not-important', 'estimated_time' => '01:00:00'],
            ['title' => 'Backup File Proyek',          'category' => 'Organisasi',        'due_date' => now(),              'status' => 'todo', 'priority' => 'important-not-urgent', 'estimated_time' => '00:30:00'],
            ['title' => 'Review Konten Social Media',  'category' => 'Shutterstock',      'due_date' => now(),              'status' => 'todo', 'priority' => 'urgent-not-important', 'estimated_time' => '01:00:00'],
            ['title' => 'Servis Laptop',               'category' => 'Perawatan',         'due_date' => now()->addDays(3),  'status' => 'todo', 'priority' => 'not-urgent-not-important', 'estimated_time' => '02:00:00'],
            ['title' => 'Hubungi Klien B untuk Revisi','category' => 'Freelance',         'due_date' => now(),              'status' => 'todo', 'priority' => 'urgent-important', 'estimated_time' => '00:15:00'],
        ];

        foreach ($generalTasks as $t) {
            if (!Task::where('user_id', $user->id)->where('title', $t['title'])->exists()) {
                $user->tasks()->create($t);
            }
        }
    }
}
