<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Organization;
use App\Models\PlatformSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Opening animation + music shown to invitees on the RSVP page.
 *
 * Resolution order: platform (templates, default music) -> organization (chosen template,
 * music source, custom track) -> event (optional override of template and music).
 * Custom music is a paid-plan capability and is re-checked every time it is resolved.
 */
class InvitationExperience
{
    public const SETTING_KEY = 'invitation_experience';

    public const TEMPLATES = [
        'classic' => [
            'name' => 'Classique',
            'description' => 'Enveloppe crème et cachet de cire : le rabat s’ouvre et la carte glisse hors de l’enveloppe.',
        ],
        'prestige' => [
            'name' => 'Prestige',
            'description' => 'Enveloppe noire liserée d’or, ouverture lente et reflets dorés.',
        ],
        'modern' => [
            'name' => 'Moderne',
            'description' => 'Une carte épurée qui se déplie comme un livret, sans enveloppe.',
        ],
    ];

    public const MUSIC_SOURCES = ['platform', 'custom', 'none'];

    public const AUDIO_MIMETYPES = 'audio/mpeg,audio/mp3,audio/mp4,audio/x-m4a,audio/aac,audio/ogg';

    public const AUDIO_MAX_KB = 8192;

    private const MUSIC_DIRECTORY = 'uploads/music';

    public static function platform(): array
    {
        $stored = PlatformSetting::get(self::SETTING_KEY, []);
        $templates = [];

        foreach (self::TEMPLATES as $key => $template) {
            $templates[$key] = [
                'enabled' => (bool) ($stored['templates'][$key]['enabled'] ?? true),
                'name' => trim((string) ($stored['templates'][$key]['name'] ?? '')) ?: $template['name'],
                'description' => trim((string) ($stored['templates'][$key]['description'] ?? '')) ?: $template['description'],
            ];
        }

        $default = $stored['default_template'] ?? 'classic';

        return [
            'enabled' => (bool) ($stored['enabled'] ?? true),
            'default_template' => isset($templates[$default]) ? $default : 'classic',
            'templates' => $templates,
            'music' => $stored['music'] ?? null,
            'music_title' => $stored['music_title'] ?? null,
        ];
    }

    /** Templates the platform currently offers, keyed by template key. */
    public static function templates(): array
    {
        return array_filter(self::platform()['templates'], fn ($t) => $t['enabled']);
    }

    public static function organization(?Organization $organization): array
    {
        $platform = self::platform();
        $stored = ($organization?->settings ?? [])['invitation_experience'] ?? [];
        $template = $stored['template'] ?? $platform['default_template'];

        return [
            'enabled' => (bool) ($stored['enabled'] ?? true),
            'template' => $template,
            'music_source' => in_array($stored['music_source'] ?? null, self::MUSIC_SOURCES, true) ? $stored['music_source'] : 'platform',
            'custom_music' => $stored['custom_music'] ?? null,
            'custom_music_title' => $stored['custom_music_title'] ?? null,
        ];
    }

    /** Custom music requires a plan that includes it AND a settled, non-expired subscription. */
    public static function canUseCustomMusic(?Organization $organization): bool
    {
        return $organization !== null
            && SaasPlans::allows(SaasPlans::get($organization->plan), 'custom_music')
            && SaasUsage::hasPaidSubscription($organization);
    }

    public static function customMusicDeniedReason(?Organization $organization): ?string
    {
        if (self::canUseCustomMusic($organization)) {
            return null;
        }

        if (!SaasPlans::allows(SaasPlans::get($organization?->plan), 'custom_music')) {
            return 'Votre forfait n’inclut pas la musique personnalisée.';
        }

        return 'La musique personnalisée est réservée aux abonnements payés et en cours de validité.';
    }

    /**
     * Final experience for an event's RSVP page, or null when no animation should play.
     */
    public static function forEvent(Event $event): ?array
    {
        $platform = self::platform();
        if (!$platform['enabled']) {
            return null;
        }

        $event->loadMissing('organization');
        $organization = $event->organization;
        $orgSettings = self::organization($organization);
        $choice = self::eventChoice($event);
        $templates = self::templates();

        $template = $choice['template'] === 'inherit'
            ? ($orgSettings['enabled'] ? $orgSettings['template'] : 'none')
            : $choice['template'];

        if ($template === 'none' || $templates === []) {
            return null;
        }

        if (!isset($templates[$template])) {
            $template = isset($templates[$platform['default_template']]) ? $platform['default_template'] : array_key_first($templates);
        }

        $source = $choice['music'] === 'inherit' ? $orgSettings['music_source'] : $choice['music'];
        if ($source === 'custom' && (!self::canUseCustomMusic($organization) || !$orgSettings['custom_music'])) {
            $source = 'platform';
        }

        $music = match ($source) {
            'none' => null,
            'custom' => ['type' => 'file', 'url' => self::musicUrl($orgSettings['custom_music']), 'title' => $orgSettings['custom_music_title']],
            default => $platform['music']
                ? ['type' => 'file', 'url' => self::musicUrl($platform['music']), 'title' => $platform['music_title']]
                : ['type' => 'synth', 'url' => null, 'title' => 'Mélodie de la plateforme'],
        };

        return self::presentation($template, $music, $organization);
    }

    /** Experience used by the configuration previews (branding page, platform page). */
    public static function preview(string $template, ?Organization $organization, ?array $music): array
    {
        return self::presentation($template, $music, $organization);
    }

    public static function eventChoice(Event $event): array
    {
        $stored = $event->invitation_experience ?? [];

        return [
            'template' => $stored['template'] ?? 'inherit',
            'music' => $stored['music'] ?? 'inherit',
        ];
    }

    /** Allowed values for the per-event selector. */
    public static function eventTemplateOptions(): array
    {
        return array_merge(['inherit', 'none'], array_keys(self::TEMPLATES));
    }

    public static function eventMusicOptions(): array
    {
        return ['inherit', 'platform', 'custom', 'none'];
    }

    /**
     * Normalizes the event form input and enforces the paid-plan rule for custom music.
     */
    public static function sanitizeEventChoice(?array $input, ?Organization $organization): ?array
    {
        if ($input === null) {
            return null;
        }

        $template = in_array($input['template'] ?? 'inherit', self::eventTemplateOptions(), true) ? ($input['template'] ?? 'inherit') : 'inherit';
        $music = in_array($input['music'] ?? 'inherit', self::eventMusicOptions(), true) ? ($input['music'] ?? 'inherit') : 'inherit';

        if ($music === 'custom' && !self::canUseCustomMusic($organization)) {
            throw ValidationException::withMessages([
                'invitation_experience.music' => self::customMusicDeniedReason($organization),
            ]);
        }

        if ($music === 'custom' && !self::organization($organization)['custom_music']) {
            throw ValidationException::withMessages([
                'invitation_experience.music' => 'Ajoutez d’abord votre musique dans Branding › Expérience d’invitation.',
            ]);
        }

        return ['template' => $template, 'music' => $music];
    }

    public static function storeMusic(UploadedFile $file): string
    {
        $directory = public_path(self::MUSIC_DIRECTORY);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw ValidationException::withMessages(['music' => 'Le dossier des musiques n’a pas pu être créé.']);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'mp3');
        $extension = in_array($extension, ['mp3', 'm4a', 'aac', 'ogg', 'mp4'], true) ? $extension : 'mp3';
        $filename = Str::random(40) . '.' . $extension;
        $file->move($directory, $filename);
        @chmod($directory . '/' . $filename, 0664);

        return $filename;
    }

    public static function deleteMusic(?string $filename): void
    {
        if ($filename && preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            $path = public_path(self::MUSIC_DIRECTORY . '/' . $filename);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public static function musicPath(string $filename): string
    {
        return public_path(self::MUSIC_DIRECTORY . '/' . $filename);
    }

    public static function musicUrl(?string $filename): ?string
    {
        return $filename ? route('media.music', $filename) : null;
    }

    private static function presentation(string $template, ?array $music, ?Organization $organization): array
    {
        $branding = ($organization?->settings ?? [])['branding'] ?? [];
        $validHex = fn ($value, $fallback) => is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $fallback;

        return [
            'template' => $template,
            'music' => $music,
            'brand' => $branding['brand_name'] ?? $organization?->name ?? 'Accès Business',
            'logo' => EventMedia::storageUrl($organization?->logo),
            'primary' => $validHex($branding['primary_color'] ?? null, '#171713'),
            'accent' => $validHex($branding['accent_color'] ?? null, '#b98943'),
        ];
    }
}
