<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm()
    {
        return redirect()->route('client.login');
    }

    public function showPlatformLoginForm()
    {
        return view('auth.login', [
            'hasUsers' => User::count() > 0,
            'authContext' => 'platform',
        ]);
    }

    public function showClientLoginForm(?Organization $organization = null)
    {
        $hasUsers = User::count() > 0;

        return view('auth.login', [
            'hasUsers' => $hasUsers,
            'clientOrganization' => $organization,
            'authContext' => 'client',
        ]);
    }

    /**
     * Traite la connexion
     */
    public function login(Request $request)
    {
        return $this->attemptLogin($request, 'client');
    }

    public function platformLogin(Request $request)
    {
        return $this->attemptLogin($request, 'platform');
    }

    public function clientLogin(Request $request)
    {
        return $this->attemptLogin($request, 'client');
    }

    private function attemptLogin(Request $request, string $context)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (Auth::user()->isPlatformAdmin()) {
                if ($context !== 'platform') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    throw ValidationException::withMessages([
                        'email' => ['Utilisez la route de connexion SA plateforme.'],
                    ]);
                }

                return redirect()->intended(route('platform.dashboard'));
            }

            if ($context === 'platform') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Ce compte n’est pas un compte SA plateforme.'],
                ]);
            }

            if (!Auth::user()->organization || !in_array(Auth::user()->organization->status, ['active', 'trialing'], true)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Votre organisation est suspendue ou inactive.'],
                ]);
            }

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => ['Les identifiants fournis sont incorrects.'],
        ]);
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function showClientRegisterForm(string $token)
    {
        $organization = Organization::where('onboarding_token', $token)
            ->where(function ($query) {
                $query->whereNull('onboarding_token_expires_at')
                    ->orWhere('onboarding_token_expires_at', '>=', now());
            })
            ->firstOrFail();

        return view('auth.register', [
            'clientOrganization' => $organization,
            'onboardingToken' => $token,
        ]);
    }

    /**
     * Traite l'inscription
     */
    public function register(Request $request)
    {
        if ($request->filled('onboarding_token')) {
            return $this->registerClientAdmin($request);
        }

        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $organization = Organization::create([
            'name' => $validated['organization_name'],
            'slug' => $this->uniqueOrganizationSlug($validated['organization_name']),
            'plan' => 'starter',
            'status' => 'active',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin',
        ]);

        // Connecter automatiquement l'utilisateur
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Bienvenue ! Votre espace SaaS est prêt.');
    }

    private function registerClientAdmin(Request $request)
    {
        $validated = $request->validate([
            'onboarding_token' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $organization = Organization::where('onboarding_token', $validated['onboarding_token'])
            ->where(function ($query) {
                $query->whereNull('onboarding_token_expires_at')
                    ->orWhere('onboarding_token_expires_at', '>=', now());
            })
            ->firstOrFail();

        if ($organization->users()->where('role', 'super_admin')->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Cette organisation possède déjà un administrateur principal.',
            ]);
        }

        $user = User::create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin',
        ]);

        $organization->update([
            'status' => 'active',
            'onboarding_token' => null,
            'onboarding_token_expires_at' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Compte client activé. Bienvenue dans votre espace.');
    }

    private function uniqueOrganizationSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'organisation';
        $slug = $base;
        $suffix = 2;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
