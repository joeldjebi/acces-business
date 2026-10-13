@php
    $isPlatform = ($authContext ?? 'client') === 'platform';
    $authPage = $isPlatform ? 'platform' : 'login';
@endphp
@extends('layouts.auth')

@section('title', 'Connexion')

@section('eyebrow', $isPlatform ? 'Console plateforme' : (isset($clientOrganization) ? 'Espace client' : 'Console client'))

@section('headline')
    @if($isPlatform)
        Pilotage global <span class="w accent">Accès</span> <span class="w accent">Business.</span>
    @elseif(isset($clientOrganization))
        {{ $clientOrganization->name }}
    @else
        Votre espace <span class="w accent">événementiel.</span>
    @endif
@endsection

@section('lead')
    @if($isPlatform)
        Connectez-vous à la console de supervision des organisations, plans et accès clients.
    @elseif(isset($clientOrganization))
        Connectez-vous à l'espace de votre organisation pour gérer vos événements, invitations et accès.
    @else
        Retrouvez vos événements, vos invités, leurs réponses et le check-in, au même endroit.
    @endif
@endsection

@section('perks')
    <li><i class="bi bi-shield-check"></i>Comptes vérifiés et accès restreint</li>
    <li><i class="bi bi-clock-history"></i>Actions tracées et centralisées</li>
    <li><i class="bi bi-qr-code-scan"></i>Invitations, cartes et check-in</li>
@endsection

@section('form')
    <div class="kicker">Session sécurisée</div>
    <h2>Connexion</h2>
    <p class="intro">{{ $isPlatform ? 'Accès réservé au super admin plateforme.' : 'Accédez à votre espace client.' }}</p>

    @if($errors->any())
        <div class="alert" role="alert"><i class="bi bi-exclamation-triangle"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <form method="POST" action="{{ $isPlatform ? route('platform.login.store') : route('client.login.store') }}">
        @csrf
        <div class="field">
            <label for="email" class="label">Adresse email</label>
            <div class="control">
                <i class="bi bi-envelope"></i>
                <input type="email" @class(['input', 'is-invalid' => $errors->has('email')]) id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nom@entreprise.com">
            </div>
        </div>
        <div class="field">
            <label for="password" class="label">Mot de passe</label>
            <div class="control">
                <i class="bi bi-lock"></i>
                <input type="password" @class(['input', 'is-invalid' => $errors->has('password')]) id="password" name="password" required autocomplete="current-password" placeholder="Votre mot de passe">
            </div>
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>
        <label class="check"><input type="checkbox" id="remember" name="remember">Maintenir ma session active</label>
        <button type="submit" class="submit">{{ $isPlatform ? 'Accéder à la plateforme' : 'Accéder à mon espace' }}<i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="alt">
        @if(!$hasUsers)
            <span>Première installation</span>
            <a href="{{ route('register') }}"><i class="bi bi-person-plus"></i>Créer le compte Super Admin</a>
        @elseif($isPlatform)
            <span>Vous êtes client ?</span>
            <a href="{{ route('client.login') }}"><i class="bi bi-building"></i>Connexion client</a>
        @else
            <span>Pas encore d’espace ?</span>
            <a href="{{ route('client.register.self') }}">Créer mon espace<i class="bi bi-arrow-right"></i></a>
        @endif
    </div>
@endsection
