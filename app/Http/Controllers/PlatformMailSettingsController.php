<?php

namespace App\Http\Controllers;

use App\Services\MailjetService;
use App\Support\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlatformMailSettingsController extends Controller
{
    public function edit()
    {
        return view('platform.mail-settings', [
            'settings' => MailSettings::get(),
            'envSmtp' => [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'mailjetConfigured' => config('services.mailjet.api_key_public') && config('services.mailjet.api_key_private'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', Rule::in(MailSettings::PROVIDERS)],
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'remove_password' => 'nullable|boolean',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
        ]);

        if ($validated['provider'] === 'smtp' && !($validated['host'] ?? null) && !config('mail.mailers.smtp.host')) {
            return back()->withInput()->withErrors(['host' => 'Indiquez le serveur SMTP.']);
        }

        MailSettings::save($validated);

        return back()->with('success', $validated['provider'] === 'smtp'
            ? 'Les emails sont désormais envoyés par le serveur SMTP. Envoyez un email de test pour vérifier.'
            : 'Les emails sont désormais envoyés par Mailjet.');
    }

    public function test(Request $request, MailjetService $mailer)
    {
        $validated = $request->validate(['test_email' => 'required|email|max:255']);

        $provider = MailSettings::provider() === 'smtp' ? 'le serveur SMTP' : 'Mailjet';
        $html = '<p style="font-family:-apple-system,Helvetica,Arial,sans-serif;font-size:15px;color:#171713">Cet email de test confirme que l’envoi fonctionne via ' . e($provider) . '.</p>';
        $result = $mailer->sendSimpleEmail($validated['test_email'], $validated['test_email'], 'Email de test · ' . config('app.name'), 'Cet email de test confirme que l’envoi fonctionne via ' . $provider . '.', $html);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? 'Email de test envoyé à ' . $validated['test_email'] . ' via ' . $provider . '.'
                : 'L’email de test n’a pas pu être envoyé via ' . $provider . '. Vérifiez les paramètres puis réessayez.'
        );
    }
}
