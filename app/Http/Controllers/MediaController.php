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
}
