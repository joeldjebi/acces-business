<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Utiliser Schema::hasColumn qui est compatible avec toutes les bases de données
        if (!Schema::hasColumn('events', 'lien_google_map')) {
            // Essayer d'abord avec Schema (méthode Laravel standard)
            try {
                Schema::table('events', function (Blueprint $table) {
                    $table->text('lien_google_map')->nullable();
                });
            } catch (\Exception $e) {
                // Si Schema échoue, utiliser SQL direct (pour MySQL)
                $driver = DB::getDriverName();
                if ($driver === 'mysql') {
                    try {
                        DB::statement("ALTER TABLE events ADD COLUMN lien_google_map TEXT NULL");
                    } catch (\Exception $e2) {
                        // Ignorer si la colonne existe déjà
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('events', 'lien_google_map')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('lien_google_map');
            });
        }
    }
};
