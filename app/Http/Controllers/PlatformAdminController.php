<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Devise;
use App\Models\Event;
use App\Models\EventAccessLink;
use App\Models\EventOtpVerification;
use App\Models\EventRegistration;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\SaasPlans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformAdminController extends Controller
{
    public function dashboard()
    {
        $organizations = Organization::withCount(['users', 'events'])
            ->latest()
            ->take(10)
            ->get();

        return view('platform.dashboard', [
            'organizationsCount' => Organization::count(),
            'activeOrganizations' => Organization::where('status', 'active')->count(),
            'trialOrganizations' => Organization::whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', now())->count(),
            'usersCount' => User::count(),
            'eventsCount' => Event::count(),
            'registrationsCount' => EventRegistration::count(),
            'pendingRevenue' => BillingInvoice::where('status', 'pending')->sum('amount'),
            'organizations' => $organizations,
        ]);
    }

    public function organizations(Request $request)
    {
        $query = Organization::withCount(['users', 'events'])
            ->latest();

        if ($request->filled('search')) {
            $query->where(function ($subQuery) use ($request) {
                $subQuery->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('slug', 'like', '%' . $request->search . '%')
                    ->orWhere('domain', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('platform.organizations', [
            'organizations' => $query->paginate(15)->withQueryString(),
            'plans' => SaasPlans::all(),
        ]);
    }

    public function showOrganization(Organization $organization)
    {
        $organization->loadCount(['users', 'events']);

        $events = Event::forOrganization($organization->id)
            ->with(['category', 'devise', 'visibilite'])
            ->latest()
            ->take(10)
            ->get();

        return view('platform.organization-show', [
            'organization' => $organization,
            'plan' => SaasPlans::get($organization->plan),
            'events' => $events,
            'eventsCount' => $organization->events_count,
            'users' => User::where('organization_id', $organization->id)->latest()->get(),
            'categories' => Category::forOrganization($organization->id)->orderBy('libelle')->get(),
            'devises' => Devise::forOrganization($organization->id)->orderBy('libelle')->get(),
            'countries' => Country::forOrganization($organization->id)->withCount('cities')->orderBy('nom')->get(),
            'citiesCount' => City::forOrganization($organization->id)->count(),
            'registrationsCount' => EventRegistration::forOrganization($organization->id)->count(),
            'invoices' => BillingInvoice::forOrganization($organization->id)->latest()->take(8)->get(),
            'accessLinksCount' => EventAccessLink::forOrganization($organization->id)->count(),
            'publishedEvents' => Event::forOrganization($organization->id)->where('statut', 'publie')->count(),
            'draftEvents' => Event::forOrganization($organization->id)->where('statut', 'brouillon')->count(),
        ]);
    }

    public function plans()
    {
        return view('platform.plans', [
            'plans' => SubscriptionPlan::orderBy('sort_order')->orderBy('monthly_price')->get(),
        ]);
    }

    public function storePlan(Request $request)
    {
        $validated = $this->validatePlan($request);

        SubscriptionPlan::create($validated);

        return back()->with('success', 'Plan créé.');
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $validated = $this->validatePlan($request, $plan);

        $plan->update($validated);

        return back()->with('success', 'Plan mis à jour.');
    }

    public function storeOrganization(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('organizations', 'slug')],
            'plan' => ['required', Rule::in(SaasPlans::keys())],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('organizations', 'domain')],
        ]);

        $organization = Organization::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: $this->uniqueOrganizationSlug($validated['name']),
            'plan' => $validated['plan'],
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
            'domain' => $validated['domain'] ?? null,
            'onboarding_token' => Str::random(48),
            'onboarding_token_expires_at' => now()->addDays(14),
        ]);

        return redirect()->route('platform.organizations')
            ->with('success', 'Client créé. Lien register: ' . route('client.register', $organization->onboarding_token));
    }

    public function purgeOrganizations(Request $request)
    {
        $request->validate([
            'confirmation' => ['required', Rule::in(['VIDER'])],
        ], [
            'confirmation.in' => 'Tapez VIDER pour confirmer la suppression des organisations.',
        ]);

        $organizationIds = Organization::pluck('id');

        if ($organizationIds->isEmpty()) {
            return back()->with('success', 'Aucune organisation à supprimer.');
        }

        $eventIds = Event::withTrashed()
            ->whereIn('organization_id', $organizationIds)
            ->pluck('id');

        $userEmails = User::whereIn('organization_id', $organizationIds)
            ->pluck('email')
            ->filter()
            ->values();

        $userIds = User::whereIn('organization_id', $organizationIds)->pluck('id');

        $filesToDelete = Event::withTrashed()
            ->whereIn('organization_id', $organizationIds)
            ->whereNotNull('image')
            ->pluck('image')
            ->merge(
                EventRegistration::whereIn('organization_id', $organizationIds)
                    ->whereNotNull('qr_code_path')
                    ->pluck('qr_code_path')
            )
            ->filter()
            ->unique()
            ->values();

        $stats = [
            'organizations' => $organizationIds->count(),
            'events' => $eventIds->count(),
            'users' => $userIds->count(),
        ];

        DB::transaction(function () use ($organizationIds, $userIds, $userEmails) {
            EventAccessLink::whereIn('organization_id', $organizationIds)->delete();
            EventRegistration::whereIn('organization_id', $organizationIds)->delete();
            EventOtpVerification::whereIn('organization_id', $organizationIds)->delete();
            Event::withTrashed()->whereIn('organization_id', $organizationIds)->forceDelete();

            City::whereIn('organization_id', $organizationIds)->delete();
            Country::whereIn('organization_id', $organizationIds)->delete();
            Devise::whereIn('organization_id', $organizationIds)->delete();
            Category::whereIn('organization_id', $organizationIds)->delete();
            BillingInvoice::whereIn('organization_id', $organizationIds)->delete();

            if ($userIds->isNotEmpty()) {
                DB::table('sessions')->whereIn('user_id', $userIds)->delete();
            }

            if ($userEmails->isNotEmpty()) {
                DB::table('password_reset_tokens')->whereIn('email', $userEmails)->delete();
            }

            User::whereIn('organization_id', $organizationIds)->delete();
            Organization::whereIn('id', $organizationIds)->delete();
        });

        if ($filesToDelete->isNotEmpty()) {
            Storage::disk('public')->delete($filesToDelete->all());
        }

        return redirect()->route('platform.organizations')
            ->with('success', "{$stats['organizations']} organisation(s), {$stats['events']} événement(s) et {$stats['users']} utilisateur(s) supprimés.");
    }

    public function updateOrganization(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plan' => ['required', Rule::in(SaasPlans::keys())],
            'status' => ['required', Rule::in(['active', 'trialing', 'suspended', 'cancelled'])],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('organizations', 'domain')->ignore($organization->id)],
            'subscription_ends_at' => 'nullable|date',
        ]);

        $organization->update($validated);

        return back()->with('success', 'Organisation mise à jour.');
    }

    public function regenerateOnboardingLink(Organization $organization)
    {
        $organization->update([
            'onboarding_token' => Str::random(48),
            'onboarding_token_expires_at' => now()->addDays(14),
        ]);

        return back()->with('success', 'Nouveau lien register client: ' . route('client.register', $organization->onboarding_token));
    }

    public function updateUserRole(Request $request, Organization $organization, User $user)
    {
        abort_unless((int) $user->organization_id === (int) $organization->id, 404);

        $validated = $request->validate([
            'role' => ['required', Rule::in(['super_admin', 'admin', 'manager', 'moderateur'])],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Rôle utilisateur mis à jour.');
    }

    private function uniqueOrganizationSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'client';
        $slug = $base;
        $suffix = 2;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function validatePlan(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $validated = $request->validate([
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('subscription_plans', 'slug')->ignore($plan?->id)],
            'name' => 'required|string|max:120',
            'tagline' => 'nullable|string|max:255',
            'monthly_price' => 'required|integer|min:0',
            'yearly_price' => 'required|integer|min:0',
            'currency' => 'required|string|max:8',
            'events_limit' => 'nullable|integer|min:1',
            'users_limit' => 'nullable|integer|min:1',
            'invitations_limit' => 'nullable|integer|min:1',
            'features_text' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ]);

        $validated['features'] = collect(preg_split('/\R/', $validated['features_text'] ?? ''))
            ->map(fn ($feature) => trim($feature))
            ->filter()
            ->values()
            ->all();

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        unset($validated['features_text']);

        return $validated;
    }
}
