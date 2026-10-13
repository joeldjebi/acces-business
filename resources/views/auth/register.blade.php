@php($authPage = 'register')
@extends('layouts.auth')

@section('title', isset($clientOrganization) ? 'Activer mon espace' : 'Créer un espace')

@section('eyebrow', isset($clientOrganization) ? 'Activation client' : 'Essai inclus au démarrage')

@section('headline')
    @isset($clientOrganization)
        Activez votre <span class="w accent">espace.</span>
    @else
        Créez votre espace <span class="w accent">en</span> <span class="w accent">2</span> <span class="w accent">minutes.</span>
    @endisset
@endsection

@section('lead')
    @isset($clientOrganization)
        Votre organisation {{ $clientOrganization->name }} a été préparée. Créez le compte administrateur principal pour ouvrir la console.
    @else
        Lancez votre organisation ou agence événementielle avec son propre espace isolé, ses équipes et ses invitations.
    @endisset
@endsection

@section('perks')
    <li><i class="bi bi-building-lock"></i>Données isolées par organisation</li>
    <li><i class="bi bi-gift"></i>14 jours d’essai inclus</li>
    <li><i class="bi bi-envelope-paper-heart"></i>Invitations, OTP, cartes et Wallet</li>
@endsection

@section('form')
    <div class="kicker">{{ isset($clientOrganization) ? 'Compte administrateur' : 'Inscription' }}</div>
    <h2>{{ isset($clientOrganization) ? 'Finaliser l’accès' : 'Créer une organisation' }}</h2>
    <p class="intro">{{ isset($clientOrganization) ? 'Définissez le premier compte administrateur de votre espace.' : 'Vous pourrez personnaliser le branding et le forfait ensuite.' }}</p>

    @if($errors->any())
        <div class="alert" role="alert"><i class="bi bi-exclamation-triangle"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        @isset($clientOrganization)
            <input type="hidden" name="onboarding_token" value="{{ $onboardingToken }}">
        @endisset

        <div class="field">
            <label for="organization_name" class="label">Organisation</label>
            <div class="control">
                <i class="bi bi-building"></i>
                <input type="text" @class(['input', 'is-invalid' => $errors->has('organization_name')]) id="organization_name" name="organization_name" value="{{ old('organization_name', $clientOrganization->name ?? '') }}" {{ isset($clientOrganization) ? 'readonly' : 'required autofocus' }} autocomplete="organization" placeholder="Nom de l'organisation">
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <label for="name" class="label">Nom complet</label>
                <div class="control">
                    <i class="bi bi-person"></i>
                    <input type="text" @class(['input', 'is-invalid' => $errors->has('name')]) id="name" name="name" value="{{ old('name') }}" required @isset($clientOrganization) autofocus @endisset autocomplete="name" placeholder="Votre nom">
                </div>
            </div>
            <div class="field">
                <label for="email" class="label">Email professionnel</label>
                <div class="control">
                    <i class="bi bi-envelope"></i>
                    <input type="email" @class(['input', 'is-invalid' => $errors->has('email')]) id="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nom@entreprise.com">
                </div>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <label for="password" class="label">Mot de passe</label>
                <div class="control">
                    <i class="bi bi-lock"></i>
                    <input type="password" @class(['input', 'is-invalid' => $errors->has('password')]) id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="8 caractères min.">
                </div>
            </div>
            <div class="field">
                <label for="password_confirmation" class="label">Confirmation</label>
                <div class="control">
                    <i class="bi bi-key"></i>
                    <input type="password" class="input" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Confirmez">
                </div>
            </div>
        </div>
        <button type="submit" class="submit">{{ isset($clientOrganization) ? 'Activer mon espace' : 'Créer mon espace' }}<i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="alt">
        <span>Déjà inscrit ?</span>
        <a href="{{ route('client.login') }}"><i class="bi bi-box-arrow-in-right"></i>Connexion</a>
    </div>
@endsection
