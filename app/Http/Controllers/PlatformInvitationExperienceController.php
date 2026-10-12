<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Support\InvitationExperience;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlatformInvitationExperienceController extends Controller
{
    public function edit()
    {
        return view('platform.invitation-experience', [
            'settings' => InvitationExperience::platform(),
        ]);
    }

    public function update(Request $request)
    {
        $keys = array_keys(InvitationExperience::TEMPLATES);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'default_template' => ['required', Rule::in($keys)],
            'templates' => 'required|array',
            'templates.*.enabled' => 'nullable|boolean',
            'templates.*.name' => 'nullable|string|max:60',
            'templates.*.description' => 'nullable|string|max:200',
            'music_title' => 'nullable|string|max:120',
            'music' => 'nullable|file|mimetypes:' . InvitationExperience::AUDIO_MIMETYPES . '|max:' . InvitationExperience::AUDIO_MAX_KB,
            'remove_music' => 'nullable|boolean',
        ]);

        $current = InvitationExperience::platform();
        $templates = [];
        foreach ($keys as $key) {
            $templates[$key] = [
                'enabled' => (bool) ($validated['templates'][$key]['enabled'] ?? false),
                'name' => trim((string) ($validated['templates'][$key]['name'] ?? '')),
                'description' => trim((string) ($validated['templates'][$key]['description'] ?? '')),
            ];
        }

        if (!$templates[$validated['default_template']]['enabled']) {
            return back()->withInput()->withErrors(['default_template' => 'L’animation par défaut doit être active.']);
        }

        $music = $current['music'];
        $musicTitle = $validated['music_title'] ?? $current['music_title'];
        if ($request->boolean('remove_music')) {
            InvitationExperience::deleteMusic($music);
            $music = null;
            $musicTitle = null;
        }
        if ($request->hasFile('music')) {
            InvitationExperience::deleteMusic($music);
            $music = InvitationExperience::storeMusic($request->file('music'));
            $musicTitle = $validated['music_title'] ?: pathinfo($request->file('music')->getClientOriginalName(), PATHINFO_FILENAME);
        }

        PlatformSetting::put(InvitationExperience::SETTING_KEY, [
            'enabled' => $request->boolean('enabled'),
            'default_template' => $validated['default_template'],
            'templates' => $templates,
            'music' => $music,
            'music_title' => $music ? $musicTitle : null,
        ]);

        return back()->with('success', 'Expérience d’invitation mise à jour.');
    }
}
