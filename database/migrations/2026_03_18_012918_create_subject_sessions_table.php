<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subject_sessions', function (Blueprint $table) {
            $table->id();
            // Sesuaikan 'subjects' dengan nama tabel mata kuliah Anda yang sebenarnya
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->integer('session_number'); // 1 sampai 16
            $table->date('date'); // Tanggal kelas
            $table->enum('type', ['regular', 'uts', 'uas', 'replacement'])->default('regular');
            $table->enum('status', ['scheduled', 'completed', 'holiday'])->default('scheduled');
            $table->string('title')->nullable(); // Contoh: "Pertemuan 1", "UTS", dll
            $table->text('notes')->nullable(); // Catatan materi hari itu
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subject_sessions');
    }
};
