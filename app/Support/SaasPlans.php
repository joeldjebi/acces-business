<?php

namespace App\Support;

class SaasPlans
{
    public static function all(): array
    {
        return [
            'starter' => [
                'name' => 'Starter',
                'tagline' => 'Pour lancer un espace événementiel propre.',
                'monthly_price' => 29000,
                'yearly_price' => 290000,
                'currency' => 'XOF',
                'limits' => [
                    'events' => 12,
                    'users' => 3,
                    'invitations' => 500,
                ],
                'features' => [
                    'Événements publics et privés',
                    'Invitations par lien',
                    'OTP et cartes PDF',
                    'Branding léger',
                ],
            ],
            'business' => [
                'name' => 'Business',
                'tagline' => 'Pour les équipes qui produisent régulièrement.',
                'monthly_price' => 79000,
                'yearly_price' => 790000,
                'currency' => 'XOF',
                'limits' => [
                    'events' => 60,
                    'users' => 12,
                    'invitations' => 5000,
                ],
                'features' => [
                    'Tout Starter',
                    'Branding organisation avancé',
                    'Historique de facturation',
                    'Support prioritaire',
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'tagline' => 'Pour agences, grandes équipes et événements haut volume.',
                'monthly_price' => 149000,
                'yearly_price' => 1490000,
                'currency' => 'XOF',
                'limits' => [
                    'events' => null,
                    'users' => null,
                    'invitations' => null,
                ],
                'features' => [
                    'Tout Business',
                    'Volumes illimités',
                    'Accompagnement dédié',
                    'Préparation intégration paiement',
                ],
            ],
        ];
    }

    public static function get(?string $key): array
    {
        $plans = self::all();

        return $plans[$key] ?? $plans['starter'];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function price(array $plan, string $cycle): int
    {
        return $cycle === 'yearly'
            ? $plan['yearly_price']
            : $plan['monthly_price'];
    }

    public static function formatPrice(int $amount, string $currency = 'XOF'): string
    {
        return number_format($amount, 0, ',', ' ') . ' ' . $currency;
    }
}
