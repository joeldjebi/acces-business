<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('platform_admin','super_admin','admin','manager','moderateur') DEFAULT 'moderateur'");
            return;
        }

        if ($driver !== 'sqlite') {
            Schema::table('users', function ($table) {
                $table->string('role', 40)->default('moderateur')->change();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        DB::table('users')
            ->where('role', 'platform_admin')
            ->update(['role' => 'super_admin']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','manager','moderateur') DEFAULT 'moderateur'");
        }
    }
};
