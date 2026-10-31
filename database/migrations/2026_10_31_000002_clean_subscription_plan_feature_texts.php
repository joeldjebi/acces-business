<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Retire les libellés techniques ou internes des avantages affichés sur les forfaits.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_plans')) {
            return;
        }

        DB::table('subscription_plans')->get(['id', 'features'])->each(function ($plan) {
            $features = json_decode((string) $plan->features, true);
            if (!is_array($features)) {
                return;
            }

            $cleaned = collect($features)
                ->reject(fn ($feature) => $feature === 'Préparation intégration paiement')
                ->map(fn ($feature) => $feature === 'OTP et cartes PDF' ? 'Vérification par code et cartes PDF' : $feature)
                ->values()
                ->all();

            if ($cleaned !== $features) {
                DB::table('subscription_plans')->where('id', $plan->id)->update(['features' => json_encode($cleaned, JSON_UNESCAPED_UNICODE)]);
            }
        });
    }

    public function down(): void
    {
        // Libellés uniquement : pas de retour arrière.
    }
};
