<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('logo')->nullable();
                $table->string('domain')->nullable()->unique();
                $table->string('plan')->default('starter');
                $table->string('status')->default('active');
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('subscription_ends_at')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        $organizationId = DB::table('organizations')->value('id');

        $defaultOrganizationName = config('app.name') === 'Laravel'
            ? 'Accès Business'
            : config('app.name', 'Accès Business');

        if (!$organizationId) {
            $organizationId = DB::table('organizations')->insertGetId([
                'name' => $defaultOrganizationName,
                'slug' => Str::slug($defaultOrganizationName) ?: 'acces-business',
                'plan' => 'starter',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->addOrganizationColumn('users', $organizationId, after: 'id', nullable: true);
        $this->addOrganizationColumn('events', $organizationId, after: 'id');
        $this->addOrganizationColumn('categories', $organizationId, after: 'id');
        $this->addOrganizationColumn('devises', $organizationId, after: 'id');
        $this->addOrganizationColumn('countries', $organizationId, after: 'id');
        $this->addOrganizationColumn('cities', $organizationId, after: 'id');
        $this->addOrganizationColumn('event_registrations', $organizationId, after: 'id');
        $this->addOrganizationColumn('event_access_links', $organizationId, after: 'id');
        $this->addOrganizationColumn('event_otp_verifications', $organizationId, after: 'id');
    }

    public function down(): void
    {
        foreach ([
            'event_otp_verifications',
            'event_access_links',
            'event_registrations',
            'cities',
            'countries',
            'devises',
            'categories',
            'events',
            'users',
        ] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'organization_id')) {
                Schema::table($table, function (Blueprint $table) {
                    try {
                        $table->dropForeign(['organization_id']);
                    } catch (\Throwable $exception) {
                        // Foreign key may already be absent after a partial migration.
                    }

                    $table->dropColumn('organization_id');
                });
            }
        }

        Schema::dropIfExists('organizations');
    }

    private function addOrganizationColumn(string $table, int $organizationId, string $after = 'id', bool $nullable = false): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!Schema::hasColumn($table, 'organization_id')) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($after) {
                $tableBlueprint->foreignId('organization_id')
                    ->nullable()
                    ->after($after);
            });
        }

        DB::table($table)
            ->where(function ($query) {
                $query->whereNull('organization_id')
                    ->orWhere('organization_id', 0);
            })
            ->update([
                'organization_id' => $organizationId,
            ]);

        try {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $exception) {
            // Foreign key already exists or cannot be added twice on a resumed migration.
        }

        if (!$nullable) {
            // Keep the column nullable at database level for safer rollout; application code always writes it.
        }
    }
};
