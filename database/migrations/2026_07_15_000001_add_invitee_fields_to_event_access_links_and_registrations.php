<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_access_links', function (Blueprint $table) {
            if (!Schema::hasColumn('event_access_links', 'nom')) {
                $table->string('nom')->nullable()->after('email_destinataire');
            }
            if (!Schema::hasColumn('event_access_links', 'prenom')) {
                $table->string('prenom')->nullable()->after('nom');
            }
            if (!Schema::hasColumn('event_access_links', 'telephone')) {
                $table->string('telephone', 30)->nullable()->after('prenom');
            }
            if (!Schema::hasColumn('event_access_links', 'entreprise')) {
                $table->string('entreprise')->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('event_access_links', 'fonction')) {
                $table->string('fonction')->nullable()->after('entreprise');
            }
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'fonction')) {
                $table->string('fonction')->nullable()->after('entreprise');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_access_links', function (Blueprint $table) {
            foreach (['fonction', 'entreprise', 'telephone', 'prenom', 'nom'] as $column) {
                if (Schema::hasColumn('event_access_links', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('event_registrations', 'fonction')) {
                $table->dropColumn('fonction');
            }
        });
    }
};
