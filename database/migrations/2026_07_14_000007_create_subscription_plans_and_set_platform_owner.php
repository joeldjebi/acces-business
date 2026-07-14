<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('tagline')->nullable();
                $table->unsignedInteger('monthly_price')->default(0);
                $table->unsignedInteger('yearly_price')->default(0);
                $table->string('currency', 8)->default('XOF');
                $table->unsignedInteger('events_limit')->nullable();
                $table->unsignedInteger('users_limit')->nullable();
                $table->unsignedInteger('invitations_limit')->nullable();
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $plans = [
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'tagline' => 'Pour lancer un espace événementiel propre.',
                'monthly_price' => 29000,
                'yearly_price' => 290000,
                'currency' => 'XOF',
                'events_limit' => 12,
                'users_limit' => 3,
                'invitations_limit' => 500,
                'features' => json_encode(['Événements publics et privés', 'Invitations par lien', 'OTP et cartes PDF', 'Branding léger']),
                'sort_order' => 10,
            ],
            [
                'slug' => 'business',
                'name' => 'Business',
                'tagline' => 'Pour les équipes qui produisent régulièrement.',
                'monthly_price' => 79000,
                'yearly_price' => 790000,
                'currency' => 'XOF',
                'events_limit' => 60,
                'users_limit' => 12,
                'invitations_limit' => 5000,
                'features' => json_encode(['Tout Starter', 'Branding organisation avancé', 'Historique de facturation', 'Support prioritaire']),
                'sort_order' => 20,
            ],
            [
                'slug' => 'premium',
                'name' => 'Premium',
                'tagline' => 'Pour agences, grandes équipes et événements haut volume.',
                'monthly_price' => 149000,
                'yearly_price' => 1490000,
                'currency' => 'XOF',
                'events_limit' => null,
                'users_limit' => null,
                'invitations_limit' => null,
                'features' => json_encode(['Tout Business', 'Volumes illimités', 'Accompagnement dédié', 'Préparation intégration paiement']),
                'sort_order' => 30,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                ['slug' => $plan['slug']],
                array_merge($plan, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        if (Schema::hasTable('users')) {
            DB::table('users')
                ->where('email', 'jo.djebi@gmail.com')
                ->update([
                    'organization_id' => null,
                    'role' => 'platform_admin',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
