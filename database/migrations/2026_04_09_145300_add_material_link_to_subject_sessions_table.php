<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subject_sessions', function (Blueprint $table) {
            $table->string('material_link')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('subject_sessions', function (Blueprint $table) {
            $table->dropColumn('material_link');
        });
    }
};
