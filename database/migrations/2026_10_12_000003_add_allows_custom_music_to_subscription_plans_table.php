<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('allows_custom_music')->default(false)->after('features');
        });

        // Custom invitation music is a paid-plan feature: enable it on every existing paid plan.
        DB::table('subscription_plans')->where('monthly_price', '>', 0)->update(['allows_custom_music' => true]);
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('allows_custom_music');
        });
    }
};
