<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Organization;
use App\Models\User;
use App\Support\SaasPlans;
use Illuminate\Http\Request;
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
        $query = Organization::withCount(['users', 'events'])->latest();

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
}
