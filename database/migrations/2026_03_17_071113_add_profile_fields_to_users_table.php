<?php
// ================================================================
// MIGRATION: Tambah kolom profil di tabel users
//
// Cara pakai:
// 1. Salin file ini ke: database/migrations/
//    dengan nama: 2024_01_01_000001_add_profile_fields_to_users_table.php
// 2. php artisan migrate
// ================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tambah kolom ke tabel users yang sudah ada ─────────────
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('avatar');
            }
            if (!Schema::hasColumn('users', 'plan')) {
                $table->string('plan')->default('free')->after('role');
            }
            if (!Schema::hasColumn('users', 'preferences')) {
                $table->json('preferences')->nullable()->after('plan');
            }
            if (!Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('preferences');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location', 100)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('location');
            }
        });

        // ── Buat tabel notifications jika belum ada ─────────────────
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('message');
                $table->string('type')->default('system');
                // Tipe: system | deadline | reminder | financial | academic | investment | budget
                $table->boolean('is_read')->default(false);
                $table->json('metadata')->nullable();
                // metadata bisa berisi: { action_url, extra_data }
                $table->timestamps();

                $table->index(['user_id', 'is_read']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['bio', 'location', 'phone', 'occupation', 'preferences', 'plan', 'role', 'avatar'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('notifications');
    }
};
