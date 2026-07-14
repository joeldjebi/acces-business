<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm()
    {
        $hasUsers = User::count() > 0;
        return view('auth.login', compact('hasUsers'));
    }

    /**
     * Traite la connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
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
        // Vérifier si un utilisateur existe déjà
        $hasUsers = User::count() > 0;
        
        if ($hasUsers) {
            // Si des utilisateurs existent, rediriger vers la page de connexion
            return redirect('/login')->with('error', 'L\'inscription n\'est plus disponible. Veuillez contacter un administrateur.');
        }
        
        return view('auth.register');
    }

    /**
     * Traite l'inscription
     */
    public function register(Request $request)
    {
        // Vérifier si un utilisateur existe déjà
        $hasUsers = User::count() > 0;
        
        if ($hasUsers) {
            return redirect('/login')->with('error', 'L\'inscription n\'est plus disponible.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Le premier utilisateur devient automatiquement super_admin
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin', // Premier utilisateur = super admin
        ]);

        // Connecter automatiquement l'utilisateur
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Bienvenue ! Vous êtes maintenant le super administrateur.');
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
