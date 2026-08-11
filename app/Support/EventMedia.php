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

        if (str_starts_with($path, 'storage/')) {
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
