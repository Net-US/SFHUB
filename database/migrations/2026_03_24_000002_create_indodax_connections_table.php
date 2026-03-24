<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel koneksi API pihak ketiga per user.
     * Contoh provider: indodax, binance, dll.
     */
    public function up(): void
    {
        Schema::create('api_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // Nama provider API (indodax, dll)
            $table->string('api_key'); // API Key
            $table->text('api_secret'); // API Secret
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable(); // Terakhir kali sync data
            $table->json('sync_status')->nullable(); // Status sync terakhir (success/error message)
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_connections');
    }
};
