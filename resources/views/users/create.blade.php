@extends('layouts.app')

@section('title', 'Créer un Utilisateur')

@section('content')
@php
    $quota = $quota ?? \App\Support\SaasUsage::forOrganization(auth()->user()->organization);
    $userLimit = $quota['limits']['users'] ?? null;
    $usersUsed = $quota['usage']['users'] ?? 0;
    $canCreateUser = $userLimit === null || $usersUsed < $userLimit;
@endphp
<div class="row">
    <div class="col-md-8 offset-md-2">
        @if(!$canCreateUser)
            <div class="alert alert-warning">
                Votre forfait autorise {{ number_format((int) $userLimit, 0, ',', ' ') }} utilisateur(s). Passez à un forfait supérieur pour ajouter un compte.
            </div>
        @endif
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Créer un Nouvel Utilisateur</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Minimum 8 caractères</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" 
                                id="role" 
                                name="role" 
                                required>
                            <option value="">Sélectionner un rôle</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="moderateur" {{ old('role') == 'moderateur' ? 'selected' : '' }}>Modérateur</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Le super admin ne peut pas être créé via cette interface</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour
                        </a>
                        <button type="submit" class="btn btn-primary" {{ !$canCreateUser ? 'disabled' : '' }}>
                            <i class="bi bi-check-circle me-1"></i>Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
