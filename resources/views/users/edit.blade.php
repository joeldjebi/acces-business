@extends('layouts.app')

@section('title', 'Modifier un utilisateur')
@section('hide_errors', '1')

@push('styles')
<style>
    .user-form { margin: 0 auto; max-width: 760px; }
    .back-link { align-items: center; color: var(--app-muted); display: inline-flex; font-size: .82rem; gap: 6px; margin-bottom: 12px; text-decoration: none; }
    .back-link:hover { color: var(--app-ink); }
    .form-section + .form-section { border-top: 1px solid var(--app-line); margin-top: 22px; padding-top: 22px; }
    .form-section h2 { color: var(--app-ink); font-size: .95rem; font-weight: 650; margin: 0 0 14px; }
    .form-actions { border-top: 1px solid var(--app-line); display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; padding-top: 18px; }
</style>
@endpush

@section('content')
<div class="user-form">
    <a href="{{ route('users.index') }}" class="back-link"><i class="bi bi-arrow-left"></i>Utilisateurs</a>
    <x-app.page-header :title="$user->name" :subtitle="$user->email" />

    <section class="ui-card">
        <form action="{{ route('users.update', $user) }}" method="POST" class="ui-card-body" novalidate>
            @csrf
            @method('PUT')

            <div class="form-section">
                <h2>Informations</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nom complet</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="role" class="form-label">Rôle</label>
                        @if($user->isSuperAdmin())
                            <input class="form-control" value="Super administrateur" disabled>
                            <div class="form-text">Ce rôle ne peut pas être modifié.</div>
                        @else
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                @foreach(['admin' => 'Administrateur', 'manager' => 'Manager', 'moderateur' => 'Modérateur'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Mot de passe</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Laissez vide pour conserver l’actuel.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirmer</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('users.index') }}" class="ui-btn ui-btn-ghost">Annuler</a>
                <button type="submit" class="ui-btn ui-btn-primary">Enregistrer</button>
            </div>
        </form>
    </section>
</div>
@endsection
