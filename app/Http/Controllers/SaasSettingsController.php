<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\CommunicationCreditBalance;
use App\Models\CommunicationPackage;
use App\Support\InvitationExperience;
use App\Support\SaasPlans;
use App\Support\SaasUsage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaasSettingsController extends Controller
{
    public function plans()
    {
        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];
        $cycle = $settings['billing_cycle'] ?? 'monthly';

        return view('saas.plans', [
            'organization' => $organization,
            'plans' => SaasPlans::all(),
            'cycle' => $cycle,
            'paymentOperators' => $this->paymentOperators(),
            'quota' => SaasUsage::forOrganization($organization),
        ]);
    }

    public function updatePlan(Request $request)
    {
        $validated = $request->validate([
            'plan' => ['required', Rule::in(SaasPlans::keys())],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'payment_operator' => ['required', Rule::in(array_keys($this->paymentOperators()))],
            'payment_phone' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $organization = auth()->user()->organization;
        $plan = SaasPlans::get($validated['plan']);
        $expectedAmount = SaasPlans::price($plan, $validated['billing_cycle']);

        if (!SaasUsage::planCanCoverCurrentUsage($organization, $plan)) {
            return back()
                ->withErrors(['plan' => SaasUsage::planCoverageMessage($organization, $plan)])
                ->withInput();
        }

        if ((int) $validated['amount'] !== $expectedAmount) {
            return back()
                ->withErrors(['amount' => 'Le montant validé doit correspondre au prix du plan sélectionné.'])
                ->withInput();
        }

        $settings = $organization->settings ?? [];
        $settings['billing_cycle'] = $validated['billing_cycle'];
        $isRenewal = $organization->plan === $validated['plan'];

        $startsAt = $organization->subscription_ends_at && $organization->subscription_ends_at->isFuture()
            ? $organization->subscription_ends_at->copy()
            : now();

        $endsAt = $validated['billing_cycle'] === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        $organization->update([
            'plan' => $validated['plan'],
            'status' => 'active',
            'subscription_ends_at' => $endsAt,
            'settings' => $settings,
        ]);

        $operators = $this->paymentOperators();

        BillingInvoice::create([
            'organization_id' => $organization->id,
            'reference' => 'SUB-' . now()->format('YmdHis') . '-' . $organization->id,
            'description' => ($isRenewal ? 'Renouvellement ' : 'Souscription ') . $plan['name'] . ' - cycle ' . ($validated['billing_cycle'] === 'yearly' ? 'annuel' : 'mensuel'),
            'amount' => $expectedAmount,
            'currency' => $plan['currency'],
            'status' => 'paid',
            'period_start' => $startsAt->toDateString(),
            'period_end' => $endsAt->toDateString(),
            'due_at' => now(),
            'paid_at' => now(),
            'metadata' => [
                'type' => 'subscription',
                'plan' => $validated['plan'],
                'plan_name' => $plan['name'],
                'billing_cycle' => $validated['billing_cycle'],
                'payment_operator' => $validated['payment_operator'],
                'payment_operator_label' => $operators[$validated['payment_operator']],
                'payment_phone' => $validated['payment_phone'],
                'amount_confirmed' => (int) $validated['amount'],
                'action' => $isRenewal ? 'renewal' : 'subscription',
            ],
        ]);

        return redirect()->route('saas.billing')
            ->with('success', 'Abonnement validé. Le paiement a été ajouté à votre historique.');
    }

    public function billing()
    {
        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];

        $invoices = BillingInvoice::forOrganization($organization->id)
            ->latest()
            ->take(12)
            ->get();

        return view('saas.billing', [
            'organization' => $organization,
            'plan' => SaasPlans::get($organization->plan),
            'billing' => $settings['billing'] ?? [],
            'cycle' => $settings['billing_cycle'] ?? 'monthly',
            'invoices' => $invoices,
        ]);
    }

    public function communications()
    {
        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];

        return view('saas.communications', [
            'organization' => $organization,
            'packages' => CommunicationPackage::active()->orderBy('sort_order')->orderBy('channel')->get(),
            'balances' => CommunicationCreditBalance::forOrganization($organization->id)->get()->keyBy('channel'),
            'invoices' => BillingInvoice::forOrganization($organization->id)
                ->where('metadata->type', 'communication_credits')
                ->latest()
                ->take(10)
                ->get(),
            'paymentOperators' => $this->paymentOperators(),
            'channels' => $this->communicationChannels(),
            'reminderSettings' => $settings['response_reminders'] ?? [],
        ]);
    }

    public function updateReminderSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];
        $settings['response_reminders'] = [
            'enabled' => $request->boolean('enabled'),
            'time' => $validated['time'] ?: '08:00',
        ];

        $organization->update(['settings' => $settings]);

        return back()->with('success', 'Planification des relances mise à jour.');
    }

    public function purchaseCommunicationCredits(Request $request)
    {
        $validated = $request->validate([
            'package_id' => ['required', Rule::exists('communication_packages', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'quantity' => 'required|integer|min:1',
            'payment_operator' => ['required', Rule::in(array_keys($this->paymentOperators()))],
            'payment_phone' => ['required', 'string', 'max:30'],
        ]);

        $organization = auth()->user()->organization;
        $package = CommunicationPackage::active()->findOrFail($validated['package_id']);
        $quantity = (int) $validated['quantity'];

        if ($quantity < $package->minimum_quantity) {
            return back()
                ->withErrors(['quantity' => 'La quantité minimale pour ce package est ' . $package->minimum_quantity . '.'])
                ->withInput();
        }

        $amount = $quantity * $package->unit_price;
        $operators = $this->paymentOperators();

        $balance = CommunicationCreditBalance::firstOrCreate(
            ['organization_id' => $organization->id, 'channel' => $package->channel],
            ['purchased' => 0, 'used' => 0]
        );

        $balance->increment('purchased', $quantity);

        BillingInvoice::create([
            'organization_id' => $organization->id,
            'reference' => 'MSG-' . now()->format('YmdHis') . '-' . $organization->id,
            'description' => 'Achat crédits ' . ($this->communicationChannels()[$package->channel] ?? strtoupper($package->channel)),
            'amount' => $amount,
            'currency' => $package->currency,
            'status' => 'paid',
            'due_at' => now(),
            'paid_at' => now(),
            'metadata' => [
                'type' => 'communication_credits',
                'package_id' => $package->id,
                'package_name' => $package->name,
                'channel' => $package->channel,
                'quantity' => $quantity,
                'unit_price' => $package->unit_price,
                'payment_operator' => $validated['payment_operator'],
                'payment_operator_label' => $operators[$validated['payment_operator']],
                'payment_phone' => $validated['payment_phone'],
            ],
        ]);

        return back()->with('success', number_format($quantity, 0, ',', ' ') . ' crédit(s) ajouté(s).');
    }

    public function updateBilling(Request $request)
    {
        $validated = $request->validate([
            'billing_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_address' => 'nullable|string|max:500',
            'billing_city' => 'nullable|string|max:120',
            'billing_country' => 'nullable|string|max:120',
            'tax_number' => 'nullable|string|max:120',
            'currency' => ['required', Rule::in(['XOF', 'EUR', 'USD'])],
        ]);

        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];
        $settings['billing'] = $validated;

        $organization->update(['settings' => $settings]);

        return back()->with('success', 'Informations de facturation mises à jour.');
    }

    public function branding()
    {
        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];

        return view('saas.branding', [
            'organization' => $organization,
            'branding' => $settings['branding'] ?? [],
            'experience' => InvitationExperience::organization($organization),
            'platformExperience' => InvitationExperience::platform(),
            'templates' => InvitationExperience::templates(),
            'canUseCustomMusic' => InvitationExperience::canUseCustomMusic($organization),
            'customMusicDeniedReason' => InvitationExperience::customMusicDeniedReason($organization),
        ]);
    }

    public function updateBranding(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'nullable|string|max:255',
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];
        $branding = $settings['branding'] ?? [];

        if ($request->hasFile('logo')) {
            $this->deleteOrganizationLogo($organization->logo);
            $organization->logo = $this->storeOrganizationLogo($request->file('logo'));
        }

        $branding['brand_name'] = $validated['brand_name'] ?: $organization->name;
        $branding['primary_color'] = $validated['primary_color'];
        $branding['accent_color'] = $validated['accent_color'];
        $settings['branding'] = $branding;

        $organization->settings = $settings;
        $organization->save();

        return back()->with('success', 'Branding mis à jour.');
    }

    public function updateInvitationExperience(Request $request)
    {
        $organization = auth()->user()->organization;
        $templates = array_keys(InvitationExperience::templates());

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'template' => ['required', Rule::in($templates)],
            'music_source' => ['required', Rule::in(InvitationExperience::MUSIC_SOURCES)],
            'music' => 'nullable|file|mimetypes:' . InvitationExperience::AUDIO_MIMETYPES . '|max:' . InvitationExperience::AUDIO_MAX_KB,
            'music_title' => 'nullable|string|max:120',
            'rights_confirmed' => 'nullable|boolean',
            'remove_music' => 'nullable|boolean',
        ]);

        $canUseCustomMusic = InvitationExperience::canUseCustomMusic($organization);
        if (!$canUseCustomMusic && ($validated['music_source'] === 'custom' || $request->hasFile('music'))) {
            return back()->withInput()->withErrors(['music_source' => InvitationExperience::customMusicDeniedReason($organization)]);
        }

        if ($request->hasFile('music') && !$request->boolean('rights_confirmed')) {
            return back()->withInput()->withErrors(['rights_confirmed' => 'Confirmez que vous disposez des droits de diffusion sur ce morceau.']);
        }

        $current = InvitationExperience::organization($organization);
        $music = $current['custom_music'];
        $title = $current['custom_music_title'];

        if ($request->boolean('remove_music')) {
            InvitationExperience::deleteMusic($music);
            $music = null;
            $title = null;
        }

        if ($request->hasFile('music')) {
            InvitationExperience::deleteMusic($music);
            $music = InvitationExperience::storeMusic($request->file('music'));
            $title = $validated['music_title'] ?: pathinfo($request->file('music')->getClientOriginalName(), PATHINFO_FILENAME);
        } elseif ($music && !empty($validated['music_title'])) {
            $title = $validated['music_title'];
        }

        if ($validated['music_source'] === 'custom' && !$music) {
            return back()->withInput()->withErrors(['music' => 'Ajoutez un fichier audio pour utiliser votre propre musique.']);
        }

        $settings = $organization->settings ?? [];
        $settings['invitation_experience'] = [
            'enabled' => $request->boolean('enabled'),
            'template' => $validated['template'],
            'music_source' => $validated['music_source'],
            'custom_music' => $music,
            'custom_music_title' => $music ? $title : null,
            'rights_confirmed_at' => $request->hasFile('music') ? now()->toIso8601String() : ($settings['invitation_experience']['rights_confirmed_at'] ?? null),
        ];
        $organization->update(['settings' => $settings]);

        return redirect()->to(route('saas.branding') . '#experience')->with('success', 'Expérience d’invitation mise à jour.');
    }

    private function storeOrganizationLogo(UploadedFile $logo): string
    {
        $directory = public_path('uploads/organizations');

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw ValidationException::withMessages([
                'logo' => 'Le dossier public/uploads/organizations n’a pas pu être créé.',
            ]);
        }

        $extension = strtolower($logo->getClientOriginalExtension() ?: $logo->extension() ?: 'png');
        $filename = Str::random(40) . '.' . $extension;
        $path = 'uploads/organizations/' . $filename;

        $logo->move($directory, $filename);
        @chmod(public_path($path), 0664);

        if (!is_file(public_path($path))) {
            throw ValidationException::withMessages([
                'logo' => 'Le logo n’a pas pu être enregistré dans public/uploads/organizations.',
            ]);
        }

        return $path;
    }

    private function deleteOrganizationLogo(?string $path): void
    {
        if (!$path) {
            return;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            $fullPath = public_path($path);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
            return;
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        Storage::disk('public')->delete($path);
    }

    private function paymentOperators(): array
    {
        return [
            'orange_money' => 'Orange Money',
            'mtn_money' => 'MTN Money',
            'moov_money' => 'Moov Money',
            'wave' => 'Wave',
            'card' => 'Carte bancaire',
            'bank_transfer' => 'Virement bancaire',
        ];
    }

    private function communicationChannels(): array
    {
        return [
            'sms' => 'SMS',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
        ];
    }
}
