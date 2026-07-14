<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Organization;
use App\Models\User;
use App\Support\SaasPlans;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformAdminController extends Controller
{
    public function dashboard()
    {
        $organizations = Organization::withCount(['users', 'events'])
            ->latest()
            ->take(8)
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
        $query = Organization::with(['users' => fn ($query) => $query->orderBy('created_at')])
            ->withCount(['users', 'events'])
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
}
