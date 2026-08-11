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
            if (!Schema::hasColumn('event_registrations', 'representant_token')) {
                $table->string('representant_token', 80)->nullable()->unique()->after('representant_confirme_le');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_mail_envoye')) {
                $table->boolean('representant_mail_envoye')->default(false)->after('representant_token');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_mail_envoye_le')) {
                $table->timestamp('representant_mail_envoye_le')->nullable()->after('representant_mail_envoye');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_mail_erreur')) {
                $table->text('representant_mail_erreur')->nullable()->after('representant_mail_envoye_le');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_confirmation_ip')) {
                $table->string('representant_confirmation_ip', 45)->nullable()->after('representant_mail_erreur');
            }
            if (!Schema::hasColumn('event_registrations', 'representant_confirmation_user_agent')) {
                $table->string('representant_confirmation_user_agent', 500)->nullable()->after('representant_confirmation_ip');
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
                'representant_confirmation_user_agent',
                'representant_confirmation_ip',
                'representant_mail_erreur',
                'representant_mail_envoye_le',
                'representant_mail_envoye',
                'representant_token',
            ] as $column) {
                if (Schema::hasColumn('event_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
