<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventAccessLink;
use App\Models\Organization;
use App\Models\User;

class SaasUsage
{
    public static function forOrganization(?Organization $organization): array
    {
        $plan = SaasPlans::get($organization?->plan);
        $organizationId = $organization?->id;

        if (!$organizationId) {
            return [
                'plan' => $plan,
                'limits' => $plan['limits'],
                'usage' => ['events' => 0, 'users' => 0, 'invitations' => 0],
            ];
        }

        return [
            'plan' => $plan,
            'limits' => $plan['limits'],
            'usage' => [
                'events' => Event::forOrganization($organizationId)->count(),
                'users' => User::where('organization_id', $organizationId)->count(),
                'invitations' => EventAccessLink::forOrganization($organizationId)->count(),
            ],
        ];
    }

    public static function remaining(?Organization $organization, string $resource): ?int
    {
        $quota = self::forOrganization($organization);
        $limit = $quota['limits'][$resource] ?? null;

        if ($limit === null) {
            return null;
        }

        return max(0, (int) $limit - (int) ($quota['usage'][$resource] ?? 0));
    }

    public static function canAdd(?Organization $organization, string $resource, int $quantity = 1): bool
    {
        $remaining = self::remaining($organization, $resource);

        return $remaining === null || $remaining >= $quantity;
    }

    public static function planCanCoverCurrentUsage(?Organization $organization, array $plan): bool
    {
        if (!$organization?->id) {
            return true;
        }

        $usage = self::forOrganization($organization)['usage'];

        foreach (($plan['limits'] ?? []) as $resource => $limit) {
            if ($limit !== null && ($usage[$resource] ?? 0) > (int) $limit) {
                return false;
            }
        }

        return true;
    }

    public static function planCoverageMessage(?Organization $organization, array $plan): string
    {
        $usage = self::forOrganization($organization)['usage'];
        $labels = [
            'events' => 'événements',
            'users' => 'utilisateurs',
            'invitations' => 'invitations',
        ];

        $exceeded = collect($plan['limits'] ?? [])
            ->filter(fn ($limit, $resource) => $limit !== null && ($usage[$resource] ?? 0) > (int) $limit)
            ->map(fn ($limit, $resource) => ($labels[$resource] ?? $resource) . ': ' . ($usage[$resource] ?? 0) . '/' . $limit)
            ->values()
            ->implode(', ');

        return 'Ce forfait ne couvre pas l’usage actuel de l’organisation'
            . ($exceeded ? ' (' . $exceeded . ')' : '')
            . '. Choisissez un forfait supérieur ou réduisez les volumes avant de continuer.';
    }

    public static function limitMessage(?Organization $organization, string $resource): string
    {
        $quota = self::forOrganization($organization);
        $planName = $quota['plan']['name'] ?? 'actuel';
        $limit = $quota['limits'][$resource] ?? null;

        $labels = [
            'events' => 'événements',
            'users' => 'utilisateurs',
            'invitations' => 'invitations',
        ];

        return 'Limite atteinte pour les ' . ($labels[$resource] ?? $resource)
            . ' du plan ' . $planName
            . ($limit ? ' (' . number_format((int) $limit, 0, ',', ' ') . ')' : '')
            . '. Passez à un forfait supérieur pour continuer.';
    }
}
