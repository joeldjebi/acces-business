<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events') || Schema::hasColumn('events', 'video_url')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('events') || !Schema::hasColumn('events', 'video_url')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
