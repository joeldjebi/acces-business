<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function organizationLogo(string $filename)
    {
        abort_unless(preg_match('/^[A-Za-z0-9._-]+$/', $filename), 404);

        $paths = [
            public_path('uploads/organizations/' . $filename),
            Storage::disk('public')->path('organization-logos/' . $filename),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return response()->file($path, [
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            }
        }

        abort(404);
    }

    public function music(string $filename)
    {
        abort_unless(preg_match('/^[A-Za-z0-9_-]+\.(mp3|m4a|aac|ogg|mp4)$/', $filename), 404);

        $path = \App\Support\InvitationExperience::musicPath($filename);
        abort_unless(is_file($path), 404);

        $types = ['mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', 'mp4' => 'audio/mp4', 'aac' => 'audio/aac', 'ogg' => 'audio/ogg'];

        return response()->file($path, [
            'Content-Type' => $types[strtolower(pathinfo($filename, PATHINFO_EXTENSION))] ?? 'audio/mpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function landingImage(string $filename)
    {
        abort_unless(preg_match('/^[A-Za-z0-9._-]+$/', $filename), 404);

        $path = public_path('images/landing/' . $filename);

        if (is_file($path)) {
            return response()->file($path, [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        abort(404);
    }

    public function eventImage(string $filename)
    {
        abort_unless(preg_match('/^[A-Za-z0-9._-]+$/', $filename), 404);

        $paths = [
            public_path('uploads/events/' . $filename),
            Storage::disk('public')->path('events/' . $filename),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return response()->file($path, [
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            }
        }

        abort(404);
    }

    public function eventProgramme(string $filename)
    {
        abort_unless(preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $filename), 404);

        $path = public_path('uploads/events/programmes/' . $filename);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
