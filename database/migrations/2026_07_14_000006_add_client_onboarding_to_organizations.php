<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'onboarding_token')) {
                $table->string('onboarding_token', 80)->nullable()->unique()->after('settings');
            }

            if (!Schema::hasColumn('organizations', 'onboarding_token_expires_at')) {
                $table->timestamp('onboarding_token_expires_at')->nullable()->after('onboarding_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'onboarding_token_expires_at')) {
                $table->dropColumn('onboarding_token_expires_at');
            }

            if (Schema::hasColumn('organizations', 'onboarding_token')) {
                $table->dropColumn('onboarding_token');
            }
        });
    }
};
