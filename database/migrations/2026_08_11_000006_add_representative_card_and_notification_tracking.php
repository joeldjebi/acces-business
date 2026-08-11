<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_registrations')) {
            return;
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'representant_carte_envoyee')) {
                $table->boolean('representant_carte_envoyee')->default(false)->after('representant_confirmation_user_agent');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_carte_envoyee_le')) {
                $table->timestamp('representant_carte_envoyee_le')->nullable()->after('representant_carte_envoyee');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_carte_erreur')) {
                $table->text('representant_carte_erreur')->nullable()->after('representant_carte_envoyee_le');
            }
            if (!Schema::hasColumn('event_registrations', 'represente_notification_envoyee')) {
                $table->boolean('represente_notification_envoyee')->default(false)->after('representant_carte_erreur');
            }
            if (!Schema::hasColumn('event_registrations', 'represente_notification_envoyee_le')) {
                $table->timestamp('represente_notification_envoyee_le')->nullable()->after('represente_notification_envoyee');
            }
            if (!Schema::hasColumn('event_registrations', 'represente_notification_erreur')) {
                $table->text('represente_notification_erreur')->nullable()->after('represente_notification_envoyee_le');
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
                'represente_notification_erreur',
                'represente_notification_envoyee_le',
                'represente_notification_envoyee',
                'representant_carte_erreur',
                'representant_carte_envoyee_le',
                'representant_carte_envoyee',
            ] as $column) {
                if (Schema::hasColumn('event_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
