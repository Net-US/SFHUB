<?php
// ════════════════════════════════════════════════════════════════════════════
// PERUBAHAN PADA Task.php — tambahkan field project_mode & linked_subject_id
// ke $fillable dan $casts
// ════════════════════════════════════════════════════════════════════════════
// Di $fillable, tambahkan:
//   'project_mode',           // 'sequential' | 'simple'
//   'linked_subject_id',      // FK ke subjects (untuk academic tasks)
//   'task_type',              // 'assignment' | 'quiz' | 'uts' | 'uas' | 'skripsi'
//   'drive_link',             // link google drive per task
// ════════════════════════════════════════════════════════════════════════════

// JUGA: Pastikan relasi berikut ada di User model:
// public function subjects()  { return $this->hasMany(Subject::class); }
// public function thesisMilestones() { return $this->hasMany(ThesisMilestone::class); }

// ════════════════════════════════════════════════════════════════════════════
// MIGRATION — jalankan: php artisan migrate
// ════════════════════════════════════════════════════════════════════════════

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'project_mode')) {
                $table->string('project_mode')->default('simple')
                    ->after('project_type')
                    ->comment('sequential=berjenjang, simple=mandiri');
            }
            if (!Schema::hasColumn('tasks', 'linked_subject_id')) {
                $table->unsignedBigInteger('linked_subject_id')->nullable()
                    ->after('subject_id');
                $table->foreign('linked_subject_id')->references('id')->on('subjects')->nullOnDelete();
            }
            if (!Schema::hasColumn('tasks', 'task_type')) {
                $table->string('task_type')->nullable()
                    ->after('project_type')
                    ->comment('assignment|quiz|uts|uas|skripsi|lab');
            }
            if (!Schema::hasColumn('tasks', 'drive_link')) {
                $table->string('drive_link')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['linked_subject_id']);
            $table->dropColumn(array_filter([
                Schema::hasColumn('tasks','project_mode')      ? 'project_mode'      : null,
                Schema::hasColumn('tasks','linked_subject_id') ? 'linked_subject_id' : null,
                Schema::hasColumn('tasks','task_type')         ? 'task_type'         : null,
                Schema::hasColumn('tasks','drive_link')        ? 'drive_link'        : null,
            ]));
        });
    }
};
