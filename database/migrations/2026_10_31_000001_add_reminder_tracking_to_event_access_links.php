<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_access_links', function (Blueprint $table) {
            if (!Schema::hasColumn('event_access_links', 'last_reminder_sent_at')) {
                $table->timestamp('last_reminder_sent_at')->nullable()->after('utilise_le');
            }
            if (!Schema::hasColumn('event_access_links', 'reminder_count')) {
                $table->unsignedInteger('reminder_count')->default(0)->after('last_reminder_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_access_links', function (Blueprint $table) {
            $table->dropColumn(['last_reminder_sent_at', 'reminder_count']);
        });
    }
};
