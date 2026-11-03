@extends('layouts.app')

@section('title', 'Envoi des emails')

@php
    $provider = old('provider', $settings['provider']);
    $field = fn (string $key) => old($key, $settings[$key]);
@endphp

@push('styles')
<style>
    .mail-page { display: grid; gap: 20px; margin: 0 auto; max-width: 820px; }
    .providers { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .provider { cursor: pointer; margin: 0; position: relative; }
    .provider input { opacity: 0; pointer-events: none; position: absolute; }
    .provider-body { border: 1px solid rgba(23, 23, 19, .13); border-radius: 14px; display: flex; flex-direction: column; gap: 4px; height: 100%; padding: 16px; transition: border-color .2s, box-shadow .2s; }
    .provider input:checked + .provider-body { border-color: var(--app-ink); box-shadow: 0 0 0 3px rgba(23, 23, 19, .06); }
    .provider input:focus-visible + .provider-body { box-shadow: 0 0 0 3px var(--app-gold-soft); }
    .provider-body i { color: var(--app-ink); font-size: 1.2rem; margin-bottom: 4px; }
    .provider-body strong { color: var(--app-ink); font-size: .92rem; }
    .provider-body small { color: var(--app-muted); font-size: .8rem; line-height: 1.45; }
    .form-actions { border-top: 1px solid var(--app-line); display: flex; justify-content: flex-end; margin-top: 22px; padding-top: 18px; }
    .test-form { align-items: flex-end; display: flex; flex-wrap: wrap; gap: 10px; }
    .test-form > div { flex: 1 1 260px; }
    @media (max-width: 575.98px) { .providers { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="mail-page">
    <x-app.page-header title="Envoi des emails" subtitle="Choisissez par quel service partent les emails de la plateforme : invitations, codes de vérification, cartes, relances et campagnes." />

    <section class="ui-card">
        <form method="POST" action="{{ route('platform.mail-settings.update') }}" class="ui-card-body" novalidate>
            @csrf
            @method('PUT')

            <span class="form-label d-block">Service d’envoi</span>
            <div class="providers">
                <label class="provider">
                    <input type="radio" name="provider" value="mailjet" @checked($provider === 'mailjet')>
                    <span class="provider-body">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <strong>Mailjet</strong>
                        <small>Système actuel. {{ $mailjetConfigured ? 'Les clés sont configurées sur le serveur.' : 'Attention : les clés Mailjet ne sont pas configurées sur le serveur.' }}</small>
                    </span>
                </label>
                <label class="provider">
                    <input type="radio" name="provider" value="smtp" @checked($provider === 'smtp')>
                    <span class="provider-body">
                        <i class="bi bi-hdd-network"></i>
                        <strong>Serveur SMTP</strong>
                        <small>Votre propre serveur de messagerie (hébergeur, Gmail, Office 365…).</small>
                    </span>
                </label>
            </div>

            <div class="mt-4" data-smtp-fields @if($provider !== 'smtp') hidden @endif>
                <h2 class="h6 fw-semibold mb-1">Paramètres SMTP</h2>
                <p class="text-muted small mb-3">Un champ laissé vide reprend la valeur déjà configurée sur le serveur, si elle existe.</p>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="host">Serveur</label>
                        <input class="form-control @error('host') is-invalid @enderror" id="host" name="host" value="{{ $field('host') }}" placeholder="{{ $envSmtp['host'] ?: 'smtp.exemple.com' }}">
                        @error('host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="port">Port</label>
                        <input type="number" class="form-control @error('port') is-invalid @enderror" id="port" name="port" value="{{ $field('port') }}" placeholder="{{ $envSmtp['port'] ?: '587' }}">
                        @error('port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Identifiant</label>
                        <input class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ $field('username') }}" placeholder="{{ $envSmtp['username'] ?: 'contact@exemple.com' }}" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ $settings['has_password'] ? '•••••••• (enregistré)' : '' }}" autocomplete="new-password">
                        @if($settings['has_password'])
                            <label class="form-check small mt-2"><input class="form-check-input" type="checkbox" name="remove_password" value="1"> Supprimer le mot de passe enregistré</label>
                        @else
                            <div class="form-text">Il est enregistré chiffré.</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="encryption">Sécurité</label>
                        <select class="form-select" id="encryption" name="encryption">
                            <option value="" @selected(!$field('encryption'))>Automatique (STARTTLS, port 587)</option>
                            <option value="ssl" @selected($field('encryption') === 'ssl')>SSL (port 465)</option>
                        </select>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-md-6">
                        <label class="form-label" for="from_email">Adresse d’expédition</label>
                        <input type="email" class="form-control @error('from_email') is-invalid @enderror" id="from_email" name="from_email" value="{{ $field('from_email') }}" placeholder="{{ $envSmtp['from_email'] ?: 'no-reply@exemple.com' }}">
                        @error('from_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="from_name">Nom d’expéditeur</label>
                        <input class="form-control" id="from_name" name="from_name" value="{{ $field('from_name') }}" placeholder="{{ $envSmtp['from_name'] }}">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="ui-btn ui-btn-primary">Enregistrer</button>
            </div>
        </form>
    </section>

    <section class="ui-card">
        <div class="ui-card-head"><div><h2>Tester l’envoi</h2><p>Envoie un email avec le service actuellement enregistré ({{ $settings['provider'] === 'smtp' ? 'serveur SMTP' : 'Mailjet' }}).</p></div></div>
        <form method="POST" action="{{ route('platform.mail-settings.test') }}" class="ui-card-body pt-0 test-form" novalidate>
            @csrf
            <div>
                <label class="form-label" for="test_email">Adresse de réception</label>
                <input type="email" class="form-control @error('test_email') is-invalid @enderror" id="test_email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" required>
                @error('test_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-ghost"><i class="bi bi-send"></i>Envoyer un email de test</button>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('input[name="provider"]').forEach((input) => input.addEventListener('change', () => {
        document.querySelector('[data-smtp-fields]').hidden = document.querySelector('input[name="provider"]:checked').value !== 'smtp';
    }));
</script>
@endpush
