<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_registrations')) {
            return;
        }

        DB::statement("ALTER TABLE event_registrations MODIFY statut_reponse ENUM('en_attente', 'present', 'peut_etre', 'absent', 'represente') DEFAULT 'en_attente'");

        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'representant_nom')) {
                $table->string('representant_nom')->nullable()->after('date_validation_otp');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_prenoms')) {
                $table->string('representant_prenoms')->nullable()->after('representant_nom');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_fonction')) {
                $table->string('representant_fonction')->nullable()->after('representant_prenoms');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_contact')) {
                $table->string('representant_contact', 30)->nullable()->after('representant_fonction');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_email')) {
                $table->string('representant_email')->nullable()->after('representant_contact');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_statut')) {
                $table->string('representant_statut', 30)->nullable()->after('representant_email');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_confirme_le')) {
                $table->timestamp('representant_confirme_le')->nullable()->after('representant_statut');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('event_registrations')) {
            return;
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            foreach ([
                'representant_confirme_le',
                'representant_statut',
                'representant_email',
                'representant_contact',
                'representant_fonction',
                'representant_prenoms',
                'representant_nom',
            ] as $column) {
                if (Schema::hasColumn('event_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        DB::statement("ALTER TABLE event_registrations MODIFY statut_reponse ENUM('en_attente', 'present', 'peut_etre', 'absent') DEFAULT 'en_attente'");
    }
};
