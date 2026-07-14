<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Support\SaasPlans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        ]);
    }

    public function updatePlan(Request $request)
    {
        $validated = $request->validate([
            'plan' => ['required', Rule::in(SaasPlans::keys())],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];
        $settings['billing_cycle'] = $validated['billing_cycle'];

        $organization->update([
            'plan' => $validated['plan'],
            'status' => 'active',
            'subscription_ends_at' => $validated['billing_cycle'] === 'yearly'
                ? now()->addYear()
                : now()->addMonth(),
            'settings' => $settings,
        ]);

        $plan = SaasPlans::get($validated['plan']);

        BillingInvoice::create([
            'organization_id' => $organization->id,
            'reference' => 'INV-' . now()->format('YmdHis') . '-' . $organization->id,
            'description' => 'Abonnement ' . $plan['name'] . ' - cycle ' . ($validated['billing_cycle'] === 'yearly' ? 'annuel' : 'mensuel'),
            'amount' => SaasPlans::price($plan, $validated['billing_cycle']),
            'currency' => $plan['currency'],
            'status' => 'pending',
            'period_start' => now()->toDateString(),
            'period_end' => $validated['billing_cycle'] === 'yearly'
                ? now()->addYear()->toDateString()
                : now()->addMonth()->toDateString(),
            'due_at' => now()->addDays(7),
        ]);

        return redirect()->route('saas.billing')
            ->with('success', 'Plan mis à jour. Une facture brouillon a été générée.');
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
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }

            $organization->logo = $request->file('logo')->store('organization-logos', 'public');
        }

        $branding['brand_name'] = $validated['brand_name'] ?: $organization->name;
        $branding['primary_color'] = $validated['primary_color'];
        $branding['accent_color'] = $validated['accent_color'];
        $settings['branding'] = $branding;

        $organization->settings = $settings;
        $organization->save();

        return back()->with('success', 'Branding mis à jour.');
    }
}
