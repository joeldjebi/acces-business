<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Affiche le profil de l'utilisateur connecté
     */
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Met à jour le nom et l'email
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validateWithBag('profile', [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill($validated)->save();

        return redirect()->route('profile.edit')->with('success', 'Profil mis à jour.');
    }

    /**
     * Change le mot de passe après vérification de l'actuel
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('profile.edit')->with('success', 'Mot de passe modifié.');
    }
}
