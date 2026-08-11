<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class PlatformLandingController extends Controller
{
    public function edit()
    {
        $landing = LandingPage::current();

        return view('platform.landing', [
            'landing' => $landing,
            'settings' => $landing->mergedSettings(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'logo_file' => ['nullable', 'image', 'max:3072'],
            'hero_image_file' => ['nullable', 'image', 'max:6144'],
            'wallet_image_file' => ['nullable', 'image', 'max:6144'],
        ]);

        $settings = $this->normalize($validated['settings']);

        $settings['brand']['logo'] = $this->uploadLandingImage($request, 'logo_file', $settings['brand']['logo'] ?? null);
        $settings['hero']['image'] = $this->uploadLandingImage($request, 'hero_image_file', $settings['hero']['image'] ?? null);
        $settings['wallet']['image'] = $this->uploadLandingImage($request, 'wallet_image_file', $settings['wallet']['image'] ?? null);

        LandingPage::query()->updateOrCreate(
            ['name' => 'default'],
            ['is_active' => true, 'settings' => $settings]
        );

        return redirect()->route('platform.landing.edit')->with('success', 'Landing page mise à jour.');
    }

    private function uploadLandingImage(Request $request, string $input, ?string $current): ?string
    {
        if (!$request->hasFile($input)) {
            return $current;
        }

        $directory = public_path('images/landing');
        File::ensureDirectoryExists($directory);

        $file = $request->file($input);
        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return $filename;
    }

    private function normalize(array $settings): array
    {
        foreach (['navigation', 'trust_items', 'hero_metrics', 'features', 'plans', 'security.items', 'faqs'] as $key) {
            $items = collect(Arr::get($settings, $key, []))
                ->filter(fn ($item) => is_array($item) && $this->hasVisibleValue($item))
                ->map(function ($item) {
                    $item['enabled'] = isset($item['enabled']) && (string) $item['enabled'] === '1';
                    $item['featured'] = isset($item['featured']) && (string) $item['featured'] === '1';

                    if (isset($item['items']) && is_string($item['items'])) {
                        $item['items'] = $this->lines($item['items']);
                    }

                    return $item;
                })
                ->values()
                ->all();

            Arr::set($settings, $key, $items);
        }

        if (isset($settings['representation']['items']) && is_string($settings['representation']['items'])) {
            $settings['representation']['items'] = $this->lines($settings['representation']['items']);
        }

        foreach (['hero', 'features_section', 'representation', 'checkin', 'wallet', 'pricing_section', 'security', 'faq_section', 'cta'] as $section) {
            $settings[$section]['enabled'] = isset($settings[$section]['enabled']) && (string) $settings[$section]['enabled'] === '1';
        }

        foreach (['ink', 'gold', 'background'] as $color) {
            $value = $settings['theme'][$color] ?? LandingPage::defaults()['theme'][$color];
            $settings['theme'][$color] = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $value) ? $value : LandingPage::defaults()['theme'][$color];
        }

        return $settings;
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function hasVisibleValue(array $item): bool
    {
        foreach ($item as $key => $value) {
            if (in_array($key, ['enabled', 'featured'], true)) {
                continue;
            }

            if (is_array($value) && !empty(array_filter($value))) {
                return true;
            }

            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }
}
