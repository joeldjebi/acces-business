<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('date_validation_otp');
            }
            if (!Schema::hasColumn('event_registrations', 'checked_in_by')) {
                $table->foreignId('checked_in_by')->nullable()->after('checked_in_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('event_registrations', 'checkin_ip')) {
                $table->string('checkin_ip', 64)->nullable()->after('checked_in_by');
            }
            if (!Schema::hasColumn('event_registrations', 'checkin_user_agent')) {
                $table->string('checkin_user_agent', 500)->nullable()->after('checkin_ip');
            }
            if (!Schema::hasColumn('event_registrations', 'checkin_count')) {
                $table->unsignedInteger('checkin_count')->default(0)->after('checkin_user_agent');
            }
            if (!Schema::hasColumn('event_registrations', 'last_reminder_sent_at')) {
                $table->timestamp('last_reminder_sent_at')->nullable()->after('checkin_count');
            }
            if (!Schema::hasColumn('event_registrations', 'reminder_count')) {
                $table->unsignedSmallInteger('reminder_count')->default(0)->after('last_reminder_sent_at');
            }
            if (!Schema::hasColumn('event_registrations', 'thank_you_sms_sent_at')) {
                $table->timestamp('thank_you_sms_sent_at')->nullable()->after('reminder_count');
            }
            if (!Schema::hasColumn('event_registrations', 'thank_you_sms_status')) {
                $table->string('thank_you_sms_status', 30)->nullable()->after('thank_you_sms_sent_at');
            }
            if (!Schema::hasColumn('event_registrations', 'thank_you_sms_error')) {
                $table->text('thank_you_sms_error')->nullable()->after('thank_you_sms_status');
            }
        });

        if (!Schema::hasTable('event_registration_activities')) {
            Schema::create('event_registration_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
                $table->foreignId('event_registration_id')->constrained()->cascadeOnDelete();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_type', 40)->default('system');
                $table->string('type', 80);
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamps();

                $table->index(['organization_id', 'event_id', 'type'], 'era_org_event_type_index');
                $table->index(['event_registration_id', 'created_at'], 'era_registration_created_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_activities');

        Schema::table('event_registrations', function (Blueprint $table) {
            foreach ([
                'thank_you_sms_error',
                'thank_you_sms_status',
                'thank_you_sms_sent_at',
                'reminder_count',
                'last_reminder_sent_at',
                'checkin_count',
                'checkin_user_agent',
                'checkin_ip',
                'checked_in_by',
                'checked_in_at',
            ] as $column) {
                if (Schema::hasColumn('event_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
