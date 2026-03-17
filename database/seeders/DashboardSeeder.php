<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Task;
use App\Models\SubTask;
use App\Models\ProductivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DashboardSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin StudentHub',
            'email' => 'admin@studenthub.com',
            'password' => Hash::make('password123'),
            'role' => 'both',
            'plan' => 'pro',
            'preferences' => json_encode([
                'theme' => 'light',
                'notifications' => true,
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]),
            'email_verified_at' => now(),
        ]);

        // Create demo user
        $user = User::create([
            'name' => 'Mahasiswa Kreatif',
            'email' => 'demo@studenthub.com',
            'password' => Hash::make('password123'),
            'role' => 'both',
            'plan' => 'pro',
            'preferences' => json_encode([
                'theme' => 'light',
                'notifications' => true,
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]),
            'email_verified_at' => now(),
        ]);

        // Create workspaces
        $workspaces = $this->createWorkspaces($user);

        // Create tasks for each workspace
        $this->createCreativeTasks($user, $workspaces['creative']);
        $this->createAcademicTasks($user, $workspaces['academic']);
        $this->createPersonalTasks($user, $workspaces['personal']);
        $this->createHealthTasks($user, $workspaces['health']);

        // Create productivity logs
        $this->createProductivityLogs($user);

        // Create profile for user
        $this->createProfile($user);
    }

    /**
     * Create workspaces for user
     */
    private function createWorkspaces(User $user): array
    {
        $workspaces = [
            [
                'name' => 'Creative Studio',
                'slug' => 'creative-studio',
                'description' => 'Tempat untuk proyek kreatif, freelance, dan konten',
                'color' => '#f97316',
                'icon' => 'palette',
                'type' => 'creative',
                'is_default' => false,
                'settings' => json_encode([
                    'show_progress' => true,
                    'enable_kanban' => true,
                    'default_view' => 'kanban',
                    'workflow_stages' => ['planning', 'production', 'review', 'publish']
                ])
            ],
            [
                'name' => 'Academic',
                'slug' => 'academic',
                'description' => 'Tugas kuliah, skripsi, penelitian, dan akademik',
                'color' => '#10b981',
                'icon' => 'graduation-cap',
                'type' => 'academic',
                'is_default' => true,
                'settings' => json_encode([
                    'show_deadlines' => true,
                    'enable_categories' => true,
                    'default_view' => 'list',
                    'reminder_days' => 3
                ])
            ],
            [
                'name' => 'Personal Life',
                'slug' => 'personal',
                'description' => 'Kehidupan pribadi, pengembangan diri, dan organisasi',
                'color' => '#3b82f6',
                'icon' => 'user',
                'type' => 'personal',
                'is_default' => false,
                'settings' => json_encode([
                    'show_quick_add' => true,
                    'enable_habits' => true,
                    'default_view' => 'list'
                ])
            ],
            [
                'name' => 'Health & Fitness',
                'slug' => 'health',
                'description' => 'Kesehatan, olahraga, makan sehat, dan kebugaran',
                'color' => '#ef4444',
                'icon' => 'heart',
                'type' => 'health',
                'is_default' => false,
                'settings' => json_encode([
                    'track_workouts' => true,
                    'track_meals' => true,
                    'show_stats' => true
                ])
            ]
        ];

        $createdWorkspaces = [];
        foreach ($workspaces as $workspaceData) {
            $workspace = Workspace::create(array_merge($workspaceData, ['user_id' => $user->id]));
            $createdWorkspaces[$workspaceData['type']] = $workspace;
        }

        return $createdWorkspaces;
    }

    /**
     * Create creative tasks with subtasks
     */
    private function createCreativeTasks(User $user, Workspace $workspace): void
    {
        // TASK 1: Video Editing Project (YouTube)
        $videoTask = Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Buat Video Review AI Tools untuk YouTube',
            'description' => 'Video 10 menit review 5 AI tools terbaik untuk mahasiswa. Target audience: mahasiswa kreatif dan tech enthusiasts.',
            'category' => 'Creative',
            'project_type' => 'video_editing',
            'priority' => 'important-not-urgent',
            'status' => 'doing',
            'due_date' => Carbon::now()->addDays(7),
            'estimated_time' => '08:00:00',
            'progress' => 40,
            'workflow_stage' => 'editing',
            'total_subtasks' => 5,
            'completed_subtasks' => 2,
            'tags' => json_encode(['youtube', 'ai', 'review', 'video']),
            'links' => json_encode([
                [
                    'type' => 'drive',
                    'url' => 'https://drive.google.com/drive/folders/1ABC123',
                    'label' => 'Video Assets'
                ],
                [
                    'type' => 'notion',
                    'url' => 'https://notion.so/script-video-ai',
                    'label' => 'Script & Outline'
                ]
            ]),
            'client' => 'Channel Pribadi',
            'budget' => 0,
            'deliverable_format' => 'mp4',
            'started_at' => Carbon::now()->subDays(2),
        ]);

        // Subtasks for Video Project
        $videoSubtasks = [
            [
                'title' => 'Research AI Tools',
                'description' => 'Riset 5 AI tools yang akan direview',
                'type' => 'stage',
                'stage_key' => 'research',
                'stage_label' => 'Research',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 120,
                'actual_minutes' => 150,
                'order' => 1,
                'completed_at' => Carbon::now()->subDays(1)->setTime(14, 30),
            ],
            [
                'title' => 'Tulis Script Video',
                'description' => 'Buat script lengkap dengan outline dan talking points',
                'type' => 'stage',
                'stage_key' => 'script',
                'stage_label' => 'Naskah/Script',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 180,
                'actual_minutes' => 210,
                'order' => 2,
                'completed_at' => Carbon::now()->subDays(1)->setTime(17, 45),
            ],
            [
                'title' => 'Recording Voice Over',
                'description' => 'Rekam voice over dengan mic yang baik',
                'type' => 'stage',
                'stage_key' => 'recording',
                'stage_label' => 'Rekaman',
                'status' => 'in_progress',
                'progress' => 70,
                'estimated_minutes' => 90,
                'actual_minutes' => 60,
                'order' => 3,
                'started_at' => Carbon::now()->subHours(3),
            ],
            [
                'title' => 'Video Editing di Premiere Pro',
                'description' => 'Edit video, tambahkan B-roll, efek, dan musik',
                'type' => 'stage',
                'stage_key' => 'editing',
                'stage_label' => 'Editing',
                'status' => 'pending',
                'progress' => 0,
                'estimated_minutes' => 240,
                'order' => 4,
            ],
            [
                'title' => 'Upload ke YouTube',
                'description' => 'Render, upload, dan optimasi untuk YouTube',
                'type' => 'stage',
                'stage_key' => 'publish',
                'stage_label' => 'Publish',
                'status' => 'pending',
                'progress' => 0,
                'estimated_minutes' => 60,
                'order' => 5,
            ],
        ];

        foreach ($videoSubtasks as $subtaskData) {
            $videoTask->subtasks()->create(array_merge($subtaskData, ['user_id' => $user->id]));
        }

        // TASK 2: Graphic Design Project (Social Media)
        $designTask = Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Desain Banner Instagram untuk Startup Lokal',
            'description' => '5 set banner Instagram untuk campaign product launch startup teknologi.',
            'category' => 'Creative',
            'project_type' => 'graphic_design',
            'priority' => 'urgent-important',
            'status' => 'todo',
            'due_date' => Carbon::now()->addDays(3),
            'estimated_time' => '05:00:00',
            'progress' => 0,
            'workflow_stage' => 'planning',
            'total_subtasks' => 4,
            'completed_subtasks' => 0,
            'tags' => json_encode(['design', 'instagram', 'social-media', 'freelance']),
            'links' => json_encode([
                [
                    'type' => 'figma',
                    'url' => 'https://figma.com/file/design-banner',
                    'label' => 'Figma Design File'
                ],
                [
                    'type' => 'drive',
                    'url' => 'https://drive.google.com/drive/folders/2XYZ456',
                    'label' => 'Client Assets'
                ]
            ]),
            'client' => 'TechStartup ID',
            'budget' => 750000,
            'deliverable_format' => 'jpg, png',
        ]);

        // Subtasks for Design Project
        $designSubtasks = [
            [
                'title' => 'Client Brief & Requirement Gathering',
                'description' => 'Meeting dengan client untuk memahami kebutuhan',
                'type' => 'stage',
                'stage_key' => 'planning',
                'stage_label' => 'Perencanaan',
                'status' => 'pending',
                'progress' => 0,
                'estimated_minutes' => 60,
                'order' => 1,
            ],
            [
                'title' => 'Concept & Moodboard',
                'description' => 'Buat 3 konsep berbeda dengan moodboard',
                'type' => 'stage',
                'stage_key' => 'concept',
                'stage_label' => 'Konsep',
                'status' => 'pending',
                'progress' => 0,
                'estimated_minutes' => 120,
                'order' => 2,
            ],
            [
                'title' => 'Design Execution',
                'description' => 'Eksekusi desain di Figma/Photoshop',
                'type' => 'stage',
                'stage_key' => 'design',
                'stage_label' => 'Desain',
                'status' => 'pending',
                'progress' => 0,
                'estimated_minutes' => 180,
                'order' => 3,
            ],
            [
                'title' => 'Revisi & Finalisasi',
                'description' => 'Kirim ke client, revisi, dan finalisasi',
                'type' => 'stage',
                'stage_key' => 'review',
                'stage_label' => 'Review',
                'status' => 'pending',
                'progress' => 0,
                'estimated_minutes' => 60,
                'order' => 4,
            ],
        ];

        foreach ($designSubtasks as $subtaskData) {
            $designTask->subtasks()->create(array_merge($subtaskData, ['user_id' => $user->id]));
        }

        // TASK 3: Animation Project (Short Video)
        $animationTask = Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Animasi Explainer Video untuk Produk Baru',
            'description' => 'Video animasi 2D durasi 60 detik untuk menjelaskan produk baru.',
            'category' => 'Creative',
            'project_type' => 'animation',
            'priority' => 'urgent-not-important',
            'status' => 'done',
            'due_date' => Carbon::now()->subDays(2),
            'estimated_time' => '12:00:00',
            'actual_time' => '10:30:00',
            'progress' => 100,
            'workflow_stage' => 'publish',
            'total_subtasks' => 6,
            'completed_subtasks' => 6,
            'tags' => json_encode(['animation', '2d', 'explainer', 'motion-graphics']),
            'links' => json_encode([
                [
                    'type' => 'adobe',
                    'url' => 'https://adobe.com/after-effects/project',
                    'label' => 'After Effects Project'
                ]
            ]),
            'client' => 'EduTech Startup',
            'budget' => 1500000,
            'deliverable_format' => 'mp4',
            'started_at' => Carbon::now()->subDays(5),
            'completed_at' => Carbon::now()->subDays(1),
        ]);

        // Subtasks for Animation Project (completed)
        $animationSubtasks = [
            [
                'title' => 'Script & Storyboard',
                'description' => 'Tulis script dan buat storyboard',
                'type' => 'stage',
                'stage_key' => 'script',
                'stage_label' => 'Script',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 180,
                'actual_minutes' => 210,
                'order' => 1,
                'completed_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Character Design',
                'description' => 'Desain karakter utama',
                'type' => 'stage',
                'stage_key' => 'design',
                'stage_label' => 'Desain',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 240,
                'actual_minutes' => 180,
                'order' => 2,
                'completed_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Asset Preparation',
                'description' => 'Siapkan semua asset grafis',
                'type' => 'stage',
                'stage_key' => 'design',
                'stage_label' => 'Desain',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 120,
                'actual_minutes' => 150,
                'order' => 3,
                'completed_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Animation in After Effects',
                'description' => 'Animasi di After Effects',
                'type' => 'stage',
                'stage_key' => 'animation',
                'stage_label' => 'Animasi',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 360,
                'actual_minutes' => 420,
                'order' => 4,
                'completed_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Sound Design & Music',
                'description' => 'Tambahkan sound effect dan musik',
                'type' => 'stage',
                'stage_key' => 'editing',
                'stage_label' => 'Editing',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 120,
                'actual_minutes' => 90,
                'order' => 5,
                'completed_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Render & Delivery',
                'description' => 'Render final dan kirim ke client',
                'type' => 'stage',
                'stage_key' => 'publish',
                'stage_label' => 'Publish',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 60,
                'actual_minutes' => 45,
                'order' => 6,
                'completed_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($animationSubtasks as $subtaskData) {
            $animationTask->subtasks()->create(array_merge($subtaskData, ['user_id' => $user->id]));
        }
    }

    /**
     * Create academic tasks
     */
    private function createAcademicTasks(User $user, Workspace $workspace): void
    {
        // TASK 1: Research Paper
        $researchTask = Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Tugas Akhir: Implementasi AI dalam Pendidikan',
            'description' => 'Paper penelitian tentang penggunaan AI untuk personalized learning di perguruan tinggi.',
            'category' => 'Academic',
            'project_type' => 'research',
            'priority' => 'urgent-important',
            'status' => 'doing',
            'due_date' => Carbon::now()->addDays(14),
            'estimated_time' => '20:00:00',
            'progress' => 60,
            'workflow_stage' => 'writing',
            'total_subtasks' => 5,
            'completed_subtasks' => 3,
            'tags' => json_encode(['skripsi', 'research', 'ai', 'education']),
        ]);

        // Subtasks for Research
        $researchTask->subtasks()->createMany([
            [
                'user_id' => $user->id,
                'title' => 'Literature Review',
                'description' => 'Review 20 paper penelitian terkait',
                'type' => 'stage',
                'stage_key' => 'research',
                'stage_label' => 'Research',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 480,
                'actual_minutes' => 520,
                'order' => 1,
            ],
            [
                'user_id' => $user->id,
                'title' => 'Methodology Design',
                'description' => 'Desain metodologi penelitian',
                'type' => 'stage',
                'stage_key' => 'planning',
                'stage_label' => 'Perencanaan',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 180,
                'actual_minutes' => 150,
                'order' => 2,
            ],
            [
                'user_id' => $user->id,
                'title' => 'Data Collection',
                'description' => 'Kumpulkan data dari responden',
                'type' => 'stage',
                'stage_key' => 'research',
                'stage_label' => 'Research',
                'status' => 'completed',
                'progress' => 100,
                'estimated_minutes' => 300,
                'actual_minutes' => 360,
                'order' => 3,
            ],
            [
                'user_id' => $user->id,
                'title' => 'Data Analysis',
                'description' => 'Analisis data menggunakan SPSS',
                'type' => 'stage',
                'stage_key' => 'analysis',
                'stage_label' => 'Analisis',
                'status' => 'in_progress',
                'progress' => 70,
                'estimated_minutes' => 240,
                'actual_minutes' => 180,
                'order' => 4,
            ],
            [
                'user_id' => $user->id,
                'title' => 'Write Final Paper',
                'description' => 'Tulis paper final',
                'type' => 'stage',
                'stage_key' => 'writing',
                'stage_label' => 'Penulisan',
                'status' => 'pending',
                'progress' => 0,
                'estimated_minutes' => 600,
                'order' => 5,
            ],
        ]);

        // TASK 2: Weekly Assignment
        Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Tugas Mingguan: Analisis Algoritma',
            'description' => 'Analisis kompleksitas waktu dan ruang dari 5 algoritma sorting.',
            'category' => 'Academic',
            'project_type' => 'academic_assignment',
            'priority' => 'urgent-important',
            'status' => 'todo',
            'due_date' => Carbon::now()->addDays(2),
            'estimated_time' => '03:00:00',
            'progress' => 0,
            'tags' => json_encode(['algoritma', 'tugas', 'mingguan']),
        ]);
    }

    /**
     * Create personal tasks
     */
    private function createPersonalTasks(User $user, Workspace $workspace): void
    {
        Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Bersih-bersih Kamar',
            'description' => 'Bersihkan kamar, cuci pakaian, dan rapikan buku.',
            'category' => 'Personal',
            'project_type' => 'cleaning',
            'priority' => 'not-urgent-not-important',
            'status' => 'todo',
            'due_date' => Carbon::now()->addDays(1),
            'estimated_time' => '01:30:00',
            'progress' => 0,
            'tags' => json_encode(['cleaning', 'personal']),
        ]);

        Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Meeting Organisasi Kampus',
            'description' => 'Rapat bulanan organisasi mahasiswa untuk planning event.',
            'category' => 'Organization',
            'project_type' => 'meeting',
            'priority' => 'important-not-urgent',
            'status' => 'todo',
            'due_date' => Carbon::now()->addDays(5),
            'estimated_time' => '02:00:00',
            'progress' => 0,
            'tags' => json_encode(['organization', 'meeting']),
        ]);
    }

    /**
     * Create health tasks
     */
    private function createHealthTasks(User $user, Workspace $workspace): void
    {
        Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Workout Gym - Full Body',
            'description' => 'Full body workout: bench press, squats, deadlifts, pull-ups.',
            'category' => 'Health',
            'project_type' => 'workout',
            'priority' => 'important-not-urgent',
            'status' => 'todo',
            'due_date' => Carbon::tomorrow(),
            'estimated_time' => '01:30:00',
            'progress' => 0,
            'tags' => json_encode(['gym', 'workout', 'fitness']),
        ]);

        Task::create([
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'title' => 'Meal Prep Mingguan',
            'description' => 'Siapkan makanan sehat untuk seminggu ke depan.',
            'category' => 'Health',
            'project_type' => 'meal_prep',
            'priority' => 'important-not-urgent',
            'status' => 'done',
            'due_date' => Carbon::yesterday(),
            'estimated_time' => '02:00:00',
            'actual_time' => '01:45:00',
            'progress' => 100,
            'tags' => json_encode(['meal prep', 'healthy']),
            'completed_at' => Carbon::yesterday()->setTime(16, 30),
        ]);
    }

    /**
     * Create productivity logs
     */
    private function createProductivityLogs(User $user): void
    {
        // Get some tasks and subtasks
        $videoTask = Task::where('title', 'LIKE', '%YouTube%')->first();
        $designTask = Task::where('title', 'LIKE', '%Instagram%')->first();
        $researchTask = Task::where('title', 'LIKE', '%Tugas Akhir%')->first();

        $videoSubtasks = $videoTask ? $videoTask->subtasks : collect();
        $researchSubtasks = $researchTask ? $researchTask->subtasks : collect();

        // Logs for today
        ProductivityLog::create([
            'user_id' => $user->id,
            'log_date' => Carbon::today(),
            'task_id' => $videoTask ? $videoTask->id : null,
            'sub_task_id' => $videoSubtasks->where('title', 'LIKE', '%Recording%')->first()->id ?? null,
            'activity_type' => 'work_session',
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'duration_minutes' => 60,
            'focus_score' => 85,
            'energy_level' => 80,
            'description' => 'Recording voice over untuk video AI tools',
            'details' => json_encode([
                'tools_recorded' => 3,
                'duration_recorded' => '45 minutes',
                'quality' => 'good'
            ]),
            'platform' => 'desktop',
            'app_used' => 'Audacity',
            'task_snapshot' => $videoTask ? json_encode([
                'title' => $videoTask->title,
                'progress' => $videoTask->progress,
                'status' => $videoTask->status
            ]) : null,
        ]);

        ProductivityLog::create([
            'user_id' => $user->id,
            'log_date' => Carbon::today(),
            'task_id' => $researchTask ? $researchTask->id : null,
            'sub_task_id' => $researchSubtasks->where('title', 'LIKE', '%Data Analysis%')->first()->id ?? null,
            'activity_type' => 'work_session',
            'start_time' => '10:00:00',
            'end_time' => '12:30:00',
            'duration_minutes' => 150,
            'focus_score' => 90,
            'energy_level' => 75,
            'description' => 'Analisis data penelitian menggunakan SPSS',
            'details' => json_encode([
                'data_points' => 150,
                'analyses_completed' => 3,
                'insights_found' => 5
            ]),
            'platform' => 'desktop',
            'app_used' => 'SPSS',
        ]);

        // Logs for yesterday
        ProductivityLog::create([
            'user_id' => $user->id,
            'log_date' => Carbon::yesterday(),
            'task_id' => $videoTask ? $videoTask->id : null,
            'sub_task_id' => $videoSubtasks->where('title', 'LIKE', '%Script%')->first()->id ?? null,
            'activity_type' => 'subtask_completed',
            'start_time' => '15:00:00',
            'end_time' => '17:45:00',
            'duration_minutes' => 165,
            'focus_score' => 88,
            'energy_level' => 70,
            'description' => 'Menyelesaikan script video AI tools',
            'details' => json_encode([
                'script_length' => '1200 words',
                'sections' => 5,
                'reviewed_by' => 'mentor'
            ]),
            'platform' => 'web',
            'app_used' => 'Notion',
        ]);

        // Logs for 2 days ago
        ProductivityLog::create([
            'user_id' => $user->id,
            'log_date' => Carbon::now()->subDays(2),
            'task_id' => $videoTask ? $videoTask->id : null,
            'sub_task_id' => $videoSubtasks->where('title', 'LIKE', '%Research%')->first()->id ?? null,
            'activity_type' => 'subtask_completed',
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
            'duration_minutes' => 150,
            'focus_score' => 92,
            'energy_level' => 85,
            'description' => 'Research AI tools untuk video review',
            'details' => json_encode([
                'tools_researched' => 8,
                'features_compared' => 12,
                'sources' => 'blogs, youtube, documentation'
            ]),
            'platform' => 'web',
            'app_used' => 'Google Chrome',
        ]);

        // Meeting log
        ProductivityLog::create([
            'user_id' => $user->id,
            'log_date' => Carbon::now()->subDays(3),
            'task_id' => $designTask ? $designTask->id : null,
            'activity_type' => 'meeting',
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'duration_minutes' => 60,
            'focus_score' => 80,
            'energy_level' => 90,
            'description' => 'Kickoff meeting dengan client untuk project banner Instagram',
            'details' => json_encode([
                'client_name' => 'TechStartup ID',
                'requirements' => '5 banners, modern style, tech theme',
                'deadline' => '3 days'
            ]),
            'platform' => 'zoom',
            'app_used' => 'Zoom',
        ]);

        // Learning session
        ProductivityLog::create([
            'user_id' => $user->id,
            'log_date' => Carbon::now()->subDays(1),
            'activity_type' => 'learning',
            'start_time' => '20:00:00',
            'end_time' => '21:30:00',
            'duration_minutes' => 90,
            'focus_score' => 95,
            'energy_level' => 65,
            'description' => 'Belajar advanced After Effects techniques',
            'details' => json_encode([
                'topic' => 'Motion Graphics',
                'platform' => 'Skillshare',
                'instructor' => 'Jake Bartlett'
            ]),
            'platform' => 'web',
            'app_used' => 'Skillshare',
        ]);
    }

    /**
     * Create user profile
     */
    private function createProfile(User $user): void
    {
        $user->profile()->create([
            'bio' => 'Mahasiswa semester 7 yang passionate tentang teknologi, desain, dan konten kreatif. Aktif sebagai freelancer di bidang video editing dan graphic design.',
            'major' => 'Teknik Informatika',
            'university' => 'Universitas Indonesia',
            'skills' => json_encode(['Video Editing', 'Graphic Design', 'Motion Graphics', 'UI/UX Design', 'Content Creation']),
            'social_links' => json_encode([
                'youtube' => 'https://youtube.com/@mahasiswakreatif',
                'instagram' => 'https://instagram.com/mahasiswa.kreatif',
                'linkedin' => 'https://linkedin.com/in/mahasiswakreatif'
            ]),
          
        ]);
    }
}
