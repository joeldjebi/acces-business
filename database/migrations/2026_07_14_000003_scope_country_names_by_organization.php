<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('countries') || !Schema::hasColumn('countries', 'organization_id')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE countries DROP INDEX countries_nom_unique');
            } catch (\Throwable $exception) {
                // Index already absent, nothing to do.
            }
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->unique(['organization_id', 'nom'], 'countries_organization_nom_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('countries')) {
            return;
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->dropUnique('countries_organization_nom_unique');
            $table->unique('nom', 'countries_nom_unique');
        });
    }
};
