<?php

namespace App\Support;

class EventMedia
{
    public static function storageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'uploads/organizations/')) {
            return route('media.organizations.logo', basename($path));
        }

        if (str_starts_with($path, 'uploads/events/programmes/')) {
            return route('media.events.programme', basename($path));
        }

        if (str_starts_with($path, 'organization-logos/')) {
            return route('media.organizations.logo', basename($path));
        }

        if (str_starts_with($path, 'storage/organization-logos/')) {
            return route('media.organizations.logo', basename($path));
        }

        if (str_starts_with($path, 'uploads/events/')) {
            return route('media.events.image', basename($path));
        }

        if (str_starts_with($path, 'events/')) {
            return route('media.events.image', basename($path));
        }

        if (str_starts_with($path, 'storage/events/')) {
            return route('media.events.image', basename($path));
        }

        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }

    public static function videoEmbedUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');

        if (str_contains($host, 'youtu.be')) {
            return 'https://www.youtube.com/embed/' . $path;
        }

        if (str_contains($host, 'youtube.com')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
            return !empty($query['v']) ? 'https://www.youtube.com/embed/' . $query['v'] : null;
        }

        if (str_contains($host, 'vimeo.com') && $path) {
            return 'https://player.vimeo.com/video/' . $path;
        }

        return null;
    }
}
