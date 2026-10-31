@extends('layouts.app')

@section('title', 'Mon profil')

@php
    $roleLabels = ['super_admin' => 'Super administrateur', 'admin' => 'Administrateur', 'manager' => 'Manager', 'moderateur' => 'Modérateur'];
    $profileErrors = $errors->getBag('profile');
    $passwordErrors = $errors->getBag('password');
@endphp

@push('styles')
<style>
    .profile-page { display: grid; gap: 20px; margin: 0 auto; max-width: 760px; }
    .profile-id { align-items: center; display: flex; gap: 14px; }
    .profile-id .ui-avatar { flex-basis: 52px; font-size: 1.1rem; height: 52px; width: 52px; }
    .profile-id strong { color: var(--app-ink); display: block; font-size: 1rem; font-weight: 650; }
    .profile-id small { color: var(--app-muted); font-size: .84rem; }
    .form-actions { border-top: 1px solid var(--app-line); display: flex; justify-content: flex-end; margin-top: 20px; padding-top: 18px; }
</style>
@endpush

@section('content')
<div class="profile-page">
    <x-app.page-header title="Mon profil" subtitle="Vos informations de connexion à la console." />

    <section class="ui-card">
        <div class="ui-card-head has-border">
            <div class="profile-id">
                <span class="ui-avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div><strong>{{ $user->name }}</strong><small>{{ $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)) }} · {{ $user->organization?->name }}</small></div>
            </div>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="ui-card-body" novalidate>
            @csrf
            @method('PUT')
            <h2 class="h6 fw-semibold mb-3">Informations</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Nom complet</label>
                    <input @class(['form-control', 'is-invalid' => $profileErrors->has('name')]) id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                    @if($profileErrors->has('name'))<div class="invalid-feedback">{{ $profileErrors->first('name') }}</div>@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Adresse email</label>
                    <input type="email" @class(['form-control', 'is-invalid' => $profileErrors->has('email')]) id="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                    @if($profileErrors->has('email'))<div class="invalid-feedback">{{ $profileErrors->first('email') }}</div>@endif
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="ui-btn ui-btn-primary">Enregistrer</button>
            </div>
        </form>
    </section>

    <section class="ui-card">
        <form method="POST" action="{{ route('profile.password') }}" class="ui-card-body" novalidate>
            @csrf
            @method('PUT')
            <h2 class="h6 fw-semibold mb-1">Mot de passe</h2>
            <p class="text-muted small mb-3">8 caractères minimum.</p>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="current_password">Mot de passe actuel</label>
                    <input type="password" @class(['form-control', 'is-invalid' => $passwordErrors->has('current_password')]) id="current_password" name="current_password" autocomplete="current-password" required>
                    @if($passwordErrors->has('current_password'))<div class="invalid-feedback">{{ $passwordErrors->first('current_password') }}</div>@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">Nouveau mot de passe</label>
                    <input type="password" @class(['form-control', 'is-invalid' => $passwordErrors->has('password')]) id="password" name="password" autocomplete="new-password" required>
                    @if($passwordErrors->has('password'))<div class="invalid-feedback">{{ $passwordErrors->first('password') }}</div>@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="ui-btn ui-btn-primary">Changer le mot de passe</button>
            </div>
        </form>
    </section>
</div>
@endsection
