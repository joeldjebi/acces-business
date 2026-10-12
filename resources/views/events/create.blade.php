@extends('layouts.app')

@section('title', 'Créer un événement')

@php
    $steps = [
        1 => ['label' => 'Informations', 'title' => 'Informations de base', 'description' => 'Donnez une identité à votre événement : titre, catégorie et visuels.'],
        2 => ['label' => 'Dates', 'title' => 'Dates et horaires', 'description' => 'Quand votre événement commence-t-il et se termine-t-il ?'],
        3 => ['label' => 'Lieu', 'title' => 'Localisation', 'description' => 'Où se déroule l’événement ? Le lien Google Maps est généré automatiquement.'],
        4 => ['label' => 'Contacts', 'title' => 'Contacts de l’événement', 'description' => 'Les coordonnées que verront vos invités.'],
        5 => ['label' => 'Tarif', 'title' => 'Tarification', 'description' => 'Gratuit ou payant ? Précisez le prix et la devise si nécessaire.'],
        6 => ['label' => 'Accès', 'title' => 'Statut, visibilité et capacité', 'description' => 'Choisissez qui peut voir l’événement et combien de personnes peuvent y assister.'],
        7 => ['label' => 'Finalisation', 'title' => 'Expérience et finalisation', 'description' => 'Choisissez l’animation d’accueil de vos invités, ajoutez vos notes, puis créez l’événement.'],
    ];
    $visibilityIcon = function (?string $label) {
        $label = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $label));
        return match (true) {
            str_contains($label, 'invit') => 'bi-envelope-paper',
            str_contains($label, 'priv') => 'bi-lock',
            str_contains($label, 'public') => 'bi-globe2',
            default => 'bi-eye',
        };
    };
@endphp

@include('events.partials.form-kit')

@push('styles')
<style>
    .create-page { margin: 0 auto; max-width: 1240px; padding-bottom: 24px; }
    .create-page *, .create-page *::before, .create-page *::after { min-width: 0; }

    .draft-status { align-items: center; background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 999px; color: var(--app-muted); display: inline-flex; font-size: .82rem; font-weight: 500; gap: 8px; height: 38px; padding: 0 14px; white-space: nowrap; }
    .draft-status i { color: var(--app-gold); }
    .draft-status.saving i { animation: pulseDraft 1s ease-in-out infinite; }
    .draft-status.saved { border-color: rgba(46, 123, 101, .25); color: var(--app-green); }
    .draft-status.saved i { color: var(--app-green); }
    .draft-status.error { border-color: rgba(164, 81, 74, .3); color: var(--app-red); white-space: normal; }
    .draft-status.error i { color: var(--app-red); }
    @keyframes pulseDraft { 0%, 100% { opacity: .4; } 50% { opacity: 1; } }

    /* Stepper */
    .stepper { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); margin-bottom: 20px; padding: 14px 16px 0; position: relative; }
    .progress-steps { display: grid; gap: 4px; grid-template-columns: repeat(7, minmax(0, 1fr)); }
    .step-item { align-items: center; border-radius: 12px; cursor: default; display: flex; gap: 10px; padding: 6px 8px 14px; transition: background .2s; }
    .step-item.completed, .step-item.active { cursor: pointer; }
    .step-item.completed:hover { background: rgba(23, 23, 19, .035); }
    .step-circle { align-items: center; background: var(--app-panel); border: 1.5px solid rgba(23, 23, 19, .14); border-radius: 50%; color: var(--app-muted); display: flex; flex: 0 0 30px; font-size: .78rem; font-weight: 700; height: 30px; justify-content: center; transition: all .4s var(--app-ease); }
    .step-item.active .step-circle { background: var(--app-ink); border-color: var(--app-ink); box-shadow: 0 0 0 5px rgba(23, 23, 19, .08); color: #fff; }
    .step-item.completed .step-circle { background: var(--app-green); border-color: var(--app-green); color: transparent; position: relative; }
    .step-item.completed .step-circle::after { color: #fff; content: "\F26E"; font-family: "bootstrap-icons"; font-size: .95rem; position: absolute; }
    .step-label { color: var(--app-muted); font-size: .82rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .step-item.active .step-label { color: var(--app-ink); font-weight: 650; }
    .step-item.completed .step-label { color: var(--app-text); }
    .progress-line { background: rgba(23, 23, 19, .06); bottom: 0; height: 3px; left: 0; position: absolute; right: 0; border-radius: 0 0 var(--app-radius) var(--app-radius); overflow: hidden; }
    .progress-line-fill { background: linear-gradient(90deg, var(--app-gold), #d8b978); height: 100%; transition: width .6s var(--app-ease); width: 0; }

    /* Shell */
    .create-shell { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 330px; }
    .form-section { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 22px; box-shadow: var(--app-shadow); }
    .form-head { border-bottom: 1px solid var(--app-line); padding: 24px 30px 20px; }
    .form-head small { color: var(--app-gold-deep); display: block; font-size: .76rem; font-weight: 600; letter-spacing: .02em; }
    .form-section-title { color: var(--app-ink); font-size: 1.35rem; font-weight: 700; letter-spacing: -.03em; margin: 6px 0 4px; }
    .form-head p { color: var(--app-muted); font-size: .9rem; margin: 0; }
    .form-body { padding: 26px 30px 8px; }

    .form-step { display: none; }
    .form-step.active { animation: stepIn .5s var(--app-ease); display: block; }
    @keyframes stepIn { from { opacity: 0; transform: translateX(14px); } }


    .step-actions { align-items: center; -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px); background: rgba(255, 255, 255, .9); border-top: 1px solid var(--app-line); z-index: 5; bottom: 0; display: flex; gap: 12px; justify-content: space-between; margin: 12px -30px 0; padding: 18px 30px; position: sticky; border-radius: 0 0 22px 22px; }
    .btn-nav, .btn-nav-secondary { align-items: center; border-radius: 999px; display: inline-flex; font-size: .9rem; font-weight: 600; gap: 6px; height: 44px; justify-content: center; padding: 0 22px; transition: transform .35s var(--app-ease), box-shadow .35s var(--app-ease), background .2s; }
    .btn-nav { background: var(--app-ink); border: 1px solid var(--app-ink); color: #fff; }
    .btn-nav:hover { box-shadow: 0 14px 28px -12px rgba(23, 23, 19, .6); color: #fff; transform: translateY(-1px); }
    .btn-nav.is-final { background: linear-gradient(135deg, #d8b978, #b98943 50%, #8a6128); border-color: transparent; }
    .btn-nav:disabled, .btn-nav:disabled:hover { box-shadow: none; cursor: not-allowed; opacity: .38; transform: none; }
    .step-missing { align-items: center; color: var(--app-muted); display: flex; flex: 1; font-size: .78rem; gap: 6px; justify-content: flex-end; line-height: 1.35; text-align: right; }
    .step-missing i { color: var(--app-gold); }
    .step-missing:empty { display: none; }
    @media (max-width: 767.98px) { .step-missing { justify-content: center; order: -1; text-align: center; width: 100%; } .step-actions { flex-wrap: wrap; } }
    .btn-nav-secondary { background: transparent; border: 1px solid transparent; color: var(--app-muted); }
    .btn-nav-secondary:hover { background: rgba(23, 23, 19, .05); color: var(--app-ink); }

    /* Preview */
    .preview { position: sticky; top: 84px; }
    .preview-label { align-items: center; color: var(--app-muted); display: flex; font-size: .74rem; font-weight: 600; gap: 6px; letter-spacing: .06em; margin: 0 4px 10px; text-transform: uppercase; }
    .preview-card { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 22px; box-shadow: 0 1px 2px rgba(23, 23, 19, .04), 0 30px 60px -30px rgba(23, 23, 19, .35); overflow: hidden; }
    .pv-cover { aspect-ratio: 16 / 10; background: radial-gradient(90% 90% at 0% 0%, rgba(185, 137, 67, .45), transparent 60%), #171713; position: relative; }
    .pv-cover img { height: 100%; inset: 0; object-fit: cover; position: absolute; width: 100%; }
    .pv-cover .pv-empty { color: rgba(216, 185, 120, .35); display: grid; font-size: 3rem; inset: 0; place-items: center; position: absolute; }
    .pv-badges { display: flex; gap: 6px; left: 12px; position: absolute; top: 12px; }
    .pv-badge { backdrop-filter: blur(10px); background: rgba(255, 255, 255, .88); border-radius: 999px; color: var(--app-ink); font-size: .7rem; font-weight: 600; padding: 4px 10px; }
    .pv-badge.dark { background: rgba(23, 23, 19, .72); color: #fff; }
    .pv-date { backdrop-filter: blur(10px); background: rgba(255, 255, 255, .92); border-radius: 12px; bottom: -22px; box-shadow: 0 10px 24px -10px rgba(0, 0, 0, .3); left: 16px; padding: 7px 0; position: absolute; text-align: center; width: 54px; }
    .pv-date strong { color: var(--app-ink); display: block; font-size: 1.2rem; font-weight: 700; letter-spacing: -.03em; line-height: 1; }
    .pv-date span { color: var(--app-gold-deep); display: block; font-size: .62rem; font-weight: 700; letter-spacing: .06em; margin-top: 3px; text-transform: uppercase; }
    .pv-body { padding: 32px 18px 18px; }
    .pv-cat { color: var(--app-gold-deep); font-size: .74rem; font-weight: 600; }
    .pv-title { color: var(--app-ink); font-size: 1.15rem; font-weight: 700; letter-spacing: -.025em; line-height: 1.2; margin: 4px 0 14px; overflow-wrap: anywhere; }
    .pv-title.is-placeholder { color: rgba(23, 23, 19, .28); }
    .pv-rows { display: grid; gap: 9px; }
    .pv-row { align-items: flex-start; color: var(--app-text); display: flex; font-size: .84rem; gap: 10px; line-height: 1.35; }
    .pv-row i { color: var(--app-muted); margin-top: 1px; }
    .pv-row.is-empty { color: rgba(23, 23, 19, .32); }
    .pv-foot { align-items: center; border-top: 1px solid var(--app-line); display: flex; justify-content: space-between; margin-top: 16px; padding-top: 14px; }
    .pv-price { color: var(--app-ink); font-size: 1.05rem; font-weight: 700; letter-spacing: -.02em; }
    .pv-cap { color: var(--app-muted); font-size: .78rem; }
    .preview-tip { color: var(--app-muted); font-size: .78rem; line-height: 1.5; margin: 12px 4px 0; }

    @media (max-width: 1199.98px) {
        .create-shell { grid-template-columns: 1fr; }
        .preview { display: none; }
        .step-label { display: none; }
        .step-item { justify-content: center; }
        .step-item.active .step-label { display: block; }
        .progress-steps { grid-template-columns: repeat(7, auto); justify-content: space-between; }
    }
    @media (max-width: 767.98px) {
        .form-head, .form-body { padding-left: 18px; padding-right: 18px; }
        .step-actions { margin: 12px -18px 0; padding: 14px 18px; }
        .step-item.active .step-label { display: none; }
        .stepper { padding: 10px 8px 0; }
        .step-item { padding: 4px 2px 12px; }
    }
    @media (max-width: 575.98px) {
        .draft-status { width: 100%; }
        .step-actions .btn-nav { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { .form-step.active { animation: none; } }
</style>
@endpush

@section('content')
<div class="create-page">
    <x-app.page-header kicker="Nouvel événement" title="Créer un événement" subtitle="Avancez étape par étape : chaque passage à l’étape suivante enregistre un brouillon.">
        <x-slot:actions>
            <div class="draft-status" id="draftStatus" role="status" aria-live="polite">
                <i class="bi bi-cloud"></i>
                <span>Aucun brouillon enregistré</span>
            </div>
        </x-slot:actions>
    </x-app.page-header>

    <nav class="stepper" aria-label="Étapes de création">
        <div class="progress-steps">
            @foreach($steps as $number => $step)
                <div @class(['step-item', 'active' => $loop->first]) data-step="{{ $number }}" title="{{ $step['title'] }}">
                    <div class="step-circle">{{ $number }}</div>
                    <div class="step-label">{{ $step['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="progress-line"><div class="progress-line-fill" id="progressFill"></div></div>
    </nav>

    <div class="create-shell">
        <div class="form-section">
            <div class="form-head">
                <small id="stepCounter">Étape 1 sur {{ count($steps) }}</small>
                <h2 class="form-section-title" id="stepTitle">{{ $steps[1]['title'] }}</h2>
                <p id="stepDescription">{{ $steps[1]['description'] }}</p>
            </div>

            <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" id="eventForm" class="form-body event-form" data-draft-url="{{ route('events.draft') }}" novalidate>
                @csrf
                <input type="hidden" id="draftEventId" name="draft_event_id" value="{{ old('draft_event_id') }}">

                <!-- Étape 1: Informations de base -->
                <div class="form-step active" data-step="1">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="titre" class="form-label">Titre de l'événement <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre') }}" placeholder="Ex. Gala annuel des partenaires" required>
                                @error('titre')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">Sélectionner</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->libelle }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Présentez l’événement en quelques lignes.">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="form-label">Image de couverture</span>
                                <label class="dropzone @error('image') border-danger @enderror" data-dropzone="image">
                                    <input type="file" class="@error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                    <span class="dz-icon"><i class="bi bi-image"></i></span>
                                    <span class="dz-text"><strong data-dz-name>Ajouter une image</strong><small>JPG, PNG, GIF · 2 Mo max</small></span>
                                    <span class="dz-action">Parcourir</span>
                                </label>
                                @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="form-label">Déroulé de l’événement <span class="text-muted">(facultatif)</span></span>
                                <label class="dropzone @error('programme_pdf') border-danger @enderror" data-dropzone="pdf">
                                    <input type="file" class="@error('programme_pdf') is-invalid @enderror" id="programme_pdf" name="programme_pdf" accept="application/pdf,.pdf">
                                    <span class="dz-icon"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <span class="dz-text"><strong data-dz-name>Ajouter le programme</strong><small>PDF · 10 Mo max</small></span>
                                    <span class="dz-action">Parcourir</span>
                                </label>
                                @error('programme_pdf')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="video_url" class="form-label">Lien vidéo <span class="text-muted">(facultatif)</span></label>
                        <input type="url" class="form-control @error('video_url') is-invalid @enderror" id="video_url" name="video_url" value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                        @error('video_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="form-text">YouTube, Vimeo ou lien vidéo externe.</small>
                    </div>

                    <div class="form-group">
                        <label for="site_web" class="form-label"><i class="bi bi-globe2"></i>Site web de la structure <span class="text-muted">(facultatif)</span></label>
                        <input type="url" class="form-control @error('site_web') is-invalid @enderror" id="site_web" name="site_web" value="{{ old('site_web') }}" placeholder="https://www.votre-structure.com" inputmode="url">
                        @error('site_web')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="form-text">Affiché aux invités pour en savoir plus sur votre structure.</small>
                    </div>

                    @include('events.partials.social-links-fields')

                    <div class="step-actions">
                        <a href="{{ route('events.index') }}" class="btn btn-nav-secondary">Annuler</a>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Étape 2: Dates et horaires -->
                <div class="form-step" data-step="2">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_debut" class="form-label"><i class="bi bi-calendar3"></i>Début <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('date_debut') is-invalid @enderror" id="date_debut" name="date_debut_at" value="{{ old('date_debut_at') }}" required>
                                @error('date_debut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_fin" class="form-label"><i class="bi bi-calendar-check"></i>Fin <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('date_fin') is-invalid @enderror" id="date_fin" name="date_fin_at" value="{{ old('date_fin_at') }}" required>
                                @error('date_fin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <small class="form-text" id="durationHint"></small>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()"><i class="bi bi-arrow-left"></i>Retour</button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Étape 3: Localisation -->
                <div class="form-step" data-step="3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ville" class="form-label">Ville</label>
                                <select class="form-select @error('ville') is-invalid @enderror" id="ville" name="ville">
                                    <option value="">Sélectionner une ville</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->nom }}" data-country="{{ optional($city->country)->nom }}" @selected(old('ville') === $city->nom)>
                                            {{ $city->nom }}{{ $city->country ? ' - ' . $city->country->nom : '' }}
                                        </option>
                                    @endforeach
                                    @if(old('ville') && !$cities->contains('nom', old('ville')))
                                        <option value="{{ old('ville') }}" selected>{{ old('ville') }}</option>
                                    @endif
                                </select>
                                @error('ville')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pays" class="form-label">Pays</label>
                                <select class="form-select @error('pays') is-invalid @enderror" id="pays" name="pays">
                                    <option value="">Sélectionner un pays</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->nom }}" @selected(old('pays') === $country->nom)>
                                            {{ $country->flag ? $country->flag . ' ' : '' }}{{ $country->nom }}
                                            @if($country->indicatif || $country->currency)
                                                ({{ trim(($country->indicatif ?: '') . ' ' . ($country->currency ?: '')) }})
                                            @endif
                                        </option>
                                    @endforeach
                                    @if(old('pays') && !$countries->contains('nom', old('pays')))
                                        <option value="{{ old('pays') }}" selected>{{ old('pays') }}</option>
                                    @endif
                                </select>
                                @error('pays')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group position-relative">
                        <label for="lieu" class="form-label"><i class="bi bi-geo-alt"></i>Lieu</label>
                        <input type="text" class="form-control @error('lieu') is-invalid @enderror" id="lieu" name="lieu" value="{{ old('lieu') }}" placeholder="Nom de la salle, de l’hôtel ou adresse" autocomplete="off" aria-autocomplete="list" aria-controls="venueSuggestions">
                        <div id="venueSuggestions" class="list-group position-absolute start-0 end-0 d-none" style="z-index: 1050; max-height: 280px; overflow-y: auto;" role="listbox"></div>
                        @error('lieu')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="lien_google_map" class="form-label"><i class="bi bi-map"></i>Lien Google Maps</label>
                        <input type="url" class="form-control @error('lien_google_map') is-invalid @enderror" id="lien_google_map" name="lien_google_map" value="{{ old('lien_google_map') }}" placeholder="Généré automatiquement à partir du lieu" readonly>
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                        @error('lien_google_map')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="form-text" id="mapHelp">Sélectionnez la ville et le pays, puis saisissez un lieu précis.</small>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()"><i class="bi bi-arrow-left"></i>Retour</button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Étape 4: Contacts événement -->
                <div class="form-step" data-step="4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="organisateur" class="form-label"><i class="bi bi-person-badge"></i>Organisateur</label>
                                <input type="text" class="form-control @error('organisateur') is-invalid @enderror" id="organisateur" name="organisateur" value="{{ old('organisateur', auth()->user()->organization?->name) }}">
                                @error('organisateur')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email_contact" class="form-label"><i class="bi bi-envelope"></i>Email de contact</label>
                                <input type="email" class="form-control @error('email_contact') is-invalid @enderror" id="email_contact" name="email_contact" value="{{ old('email_contact') }}" placeholder="contact@exemple.com">
                                @error('email_contact')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telephone" class="form-label"><i class="bi bi-telephone"></i>Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone') }}" placeholder="+225 ...">
                                @error('telephone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()"><i class="bi bi-arrow-left"></i>Retour</button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Étape 5: Tarification -->
                <div class="form-step" data-step="5">
                    <div class="form-group">
                        <span class="form-label">Type de tarification <span class="text-danger">*</span></span>
                        <div class="choice-grid">
                            @foreach($typeTarifications as $type)
                                @php $isPaidType = \Illuminate\Support\Str::lower(trim($type->libelle)) === 'payant'; @endphp
                                <label @class(['choice', 'is-invalid' => $errors->has('type_tarification_id')])>
                                    <input type="radio" name="type_tarification_id" value="{{ $type->id }}" data-label="{{ \Illuminate\Support\Str::lower(trim($type->libelle)) }}" @checked(old('type_tarification_id') == $type->id) @class(['is-invalid' => $errors->has('type_tarification_id')]) required>
                                    <span class="choice-body">
                                        <i class="bi {{ $isPaidType ? 'bi-ticket-perforated' : 'bi-gift' }}"></i>
                                        <strong>{{ $type->libelle }}</strong>
                                        <small>{{ $isPaidType ? 'Les invités règlent un droit d’entrée.' : 'Entrée libre pour vos invités.' }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('type_tarification_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 paid-field">
                            <div class="form-group">
                                <label for="prix" class="form-label"><i class="bi bi-cash-coin"></i>Prix</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('prix') is-invalid @enderror" id="prix" name="prix" value="{{ old('prix') }}" placeholder="0">
                                @error('prix')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6 paid-field">
                            <div class="form-group">
                                <label for="devise_id" class="form-label"><i class="bi bi-currency-exchange"></i>Devise</label>
                                <select class="form-select @error('devise_id') is-invalid @enderror" id="devise_id" name="devise_id">
                                    <option value="">Sélectionner</option>
                                    @foreach($devises as $devise)
                                        <option value="{{ $devise->id }}" @selected(old('devise_id') == $devise->id)>{{ $devise->libelle }}</option>
                                    @endforeach
                                </select>
                                @error('devise_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()"><i class="bi bi-arrow-left"></i>Retour</button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Étape 6: Statut, visibilité et capacité -->
                <div class="form-step" data-step="6">
                    <div class="form-group">
                        <span class="form-label">Statut <span class="text-danger">*</span></span>
                        <div class="choice-grid">
                            <label class="choice">
                                <input type="radio" name="statut" value="brouillon" @checked(old('statut', 'brouillon') === 'brouillon') required>
                                <span class="choice-body"><i class="bi bi-pencil-square"></i><strong>Brouillon</strong><small>En préparation, visible uniquement par votre équipe.</small></span>
                            </label>
                            <label class="choice">
                                <input type="radio" name="statut" value="publie" @checked(old('statut') === 'publie') required>
                                <span class="choice-body"><i class="bi bi-broadcast"></i><strong>Publié</strong><small>L’événement est actif et prêt à accueillir des invités.</small></span>
                            </label>
                        </div>
                        @error('statut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <span class="form-label">Visibilité <span class="text-danger">*</span></span>
                        <div class="choice-grid">
                            @foreach($visibilites as $visibilite)
                                <label @class(['choice', 'is-invalid' => $errors->has('visibilite_id')])>
                                    <input type="radio" name="visibilite_id" value="{{ $visibilite->id }}" data-label="{{ $visibilite->libelle }}" @checked(old('visibilite_id') == $visibilite->id) @class(['is-invalid' => $errors->has('visibilite_id')]) required>
                                    <span class="choice-body"><i class="bi {{ $visibilityIcon($visibilite->libelle) }}"></i><strong>{{ $visibilite->libelle }}</strong>@if($visibilite->description ?? null)<small>{{ $visibilite->description }}</small>@endif</span>
                                </label>
                            @endforeach
                        </div>
                        @error('visibilite_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacite_maximale" class="form-label"><i class="bi bi-people"></i>Capacité maximale</label>
                                <input type="number" min="1" class="form-control @error('capacite_maximale') is-invalid @enderror" id="capacite_maximale" name="capacite_maximale" value="{{ old('capacite_maximale') }}" placeholder="Illimitée si vide">
                                @error('capacite_maximale')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_limite_inscription" class="form-label"><i class="bi bi-calendar-x"></i>Date limite d'inscription</label>
                                <input type="date" class="form-control @error('date_limite_inscription') is-invalid @enderror" id="date_limite_inscription" name="date_limite_inscription" value="{{ old('date_limite_inscription') }}">
                                @error('date_limite_inscription')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="switch-row">
                            <label for="inscription_requise" class="form-label mb-0"><i class="bi bi-clipboard-check"></i>Inscription requise</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="inscription_requise" name="inscription_requise" value="1" @checked(old('inscription_requise'))>
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()"><i class="bi bi-arrow-left"></i>Retour</button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- Étape 7: Métadonnées -->
                <div class="form-step" data-step="7">
                    @include('events.partials.invitation-experience-fields', ['event' => null])
                    <div class="form-group">
                        <label for="tags" class="form-label"><i class="bi bi-tags"></i>Mots-clés</label>
                        <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags') }}" placeholder="Ex. conférence, technologie, innovation">
                        @error('tags')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="form-text">Séparez les mots-clés par des virgules.</small>
                    </div>

                    <div class="form-group">
                        <label for="notes_internes" class="form-label"><i class="bi bi-sticky"></i>Notes internes</label>
                        <textarea class="form-control @error('notes_internes') is-invalid @enderror" id="notes_internes" name="notes_internes" rows="3" placeholder="Informations utiles à votre équipe uniquement.">{{ old('notes_internes') }}</textarea>
                        @error('notes_internes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="form-text">Visibles uniquement par les administrateurs.</small>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()"><i class="bi bi-arrow-left"></i>Retour</button>
                        <button type="submit" class="btn btn-nav is-final"><i class="bi bi-check2-circle"></i>Créer l'événement</button>
                    </div>
                </div>
            </form>
        </div>

        <aside class="preview" aria-label="Aperçu de l’événement">
            <div class="preview-label"><i class="bi bi-eye"></i>Aperçu en direct</div>
            <div class="preview-card">
                <div class="pv-cover">
                    <img id="pvImage" alt="" hidden>
                    <span class="pv-empty" id="pvEmpty"><i class="bi bi-stars"></i></span>
                    <div class="pv-badges"><span class="pv-badge" id="pvStatus">Brouillon</span><span class="pv-badge dark" id="pvVisibility" hidden></span></div>
                    <div class="pv-date"><strong id="pvDay">--</strong><span id="pvMonth">mois</span></div>
                </div>
                <div class="pv-body">
                    <div class="pv-cat" id="pvCategory">Catégorie</div>
                    <div class="pv-title is-placeholder" id="pvTitle">Titre de votre événement</div>
                    <div class="pv-rows">
                        <div class="pv-row is-empty" id="pvWhen"><i class="bi bi-clock"></i><span>Date et heure à définir</span></div>
                        <div class="pv-row is-empty" id="pvWhere"><i class="bi bi-geo-alt"></i><span>Lieu à définir</span></div>
                    </div>
                    <div class="pv-foot">
                        <span class="pv-price" id="pvPrice">—</span>
                        <span class="pv-cap" id="pvCapacity">Capacité libre</span>
                    </div>
                </div>
            </div>
            <p class="preview-tip"><i class="bi bi-lightbulb me-1"></i>Cet aperçu reflète la carte que verront vos équipes dans le portefeuille.</p>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const steps = @json($steps);
    const totalSteps = Object.keys(steps).length;
    const eventForm = document.getElementById('eventForm');
    const draftInput = document.getElementById('draftEventId');
    const draftStatus = document.getElementById('draftStatus');

    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        document.getElementById('progressFill').style.width = progress + '%';

        document.querySelectorAll('.step-item').forEach((item, index) => {
            const stepNum = index + 1;
            item.classList.toggle('completed', stepNum < currentStep);
            item.classList.toggle('active', stepNum === currentStep);
            item.setAttribute('aria-current', stepNum === currentStep ? 'step' : 'false');
        });

        document.getElementById('stepTitle').textContent = steps[currentStep].title;
        document.getElementById('stepDescription').textContent = steps[currentStep].description;
        document.getElementById('stepCounter').textContent = `Étape ${currentStep} sur ${totalSteps}`;
    }

    function showStep(step) {
        document.querySelectorAll('.form-step').forEach(stepEl => stepEl.classList.remove('active'));
        document.querySelector(`.form-step[data-step="${step}"]`)?.classList.add('active');
        currentStep = step;
        updateProgress();
        refreshStepGate();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Required fields gate: "Continuer" stays disabled until the step's mandatory fields are filled.
    const fieldLabel = (field) => {
        const label = field.closest('.form-group')?.querySelector('.form-label');
        return (label?.textContent || field.name).replace('*', '').replace(/\(facultatif\)/i, '').replace(/\s+/g, ' ').trim();
    };

    function missingFields(step) {
        const stepEl = document.querySelector(`.form-step[data-step="${step}"]`);
        const missing = new Map();
        stepEl.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            if (field.disabled || field.closest('[hidden]')) return;
            const empty = field.type === 'radio'
                ? !stepEl.querySelector(`input[type="radio"][name="${CSS.escape(field.name)}"]:checked`)
                : field.type === 'checkbox' ? !field.checked : !field.value.trim();
            if (empty) missing.set(field.name, fieldLabel(field));
        });
        if (step === 2) {
            const start = document.getElementById('date_debut').value, end = document.getElementById('date_fin').value;
            if (start && end && new Date(end) <= new Date(start)) missing.set('chronology', 'une fin postérieure au début');
        }
        return [...missing.values()];
    }

    function refreshStepGate() {
        const stepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        const button = stepEl?.querySelector('.step-actions .btn-nav');
        if (!button) return;
        let hint = stepEl.querySelector('.step-missing');
        if (!hint) {
            hint = document.createElement('span');
            hint.className = 'step-missing';
            hint.setAttribute('aria-live', 'polite');
            button.before(hint);
        }
        const missing = missingFields(currentStep);
        button.disabled = missing.length > 0;
        button.title = missing.length ? 'Renseignez les champs obligatoires pour continuer' : '';
        hint.innerHTML = missing.length ? `<i class="bi bi-info-circle"></i><span>À renseigner : ${missing.map((m) => m.replace(/[<>&]/g, '')).join(', ')}</span>` : '';
    }

    function setDraftStatus(message, state = 'idle') {
        const icon = draftStatus.querySelector('i');
        const text = draftStatus.querySelector('span');

        draftStatus.classList.remove('saving', 'saved', 'error');
        if (state) draftStatus.classList.add(state);

        icon.className = state === 'saving'
            ? 'bi bi-cloud-arrow-up'
            : state === 'saved'
                ? 'bi bi-cloud-check'
                : state === 'error'
                    ? 'bi bi-exclamation-triangle'
                    : 'bi bi-cloud';

        text.textContent = message;
    }

    async function saveDraft() {
        const formData = new FormData(eventForm);
        if (draftInput.value) formData.set('draft_event_id', draftInput.value);

        setDraftStatus('Sauvegarde du brouillon…', 'saving');

        const response = await fetch(eventForm.dataset.draftUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {})[0]?.[0] || 'Le brouillon n’a pas pu être sauvegardé.';
            throw new Error(message);
        }

        draftInput.value = data.id;
        setDraftStatus(`Brouillon enregistré à ${data.saved_at}`, 'saved');
    }

    async function nextStep() {
        if (missingFields(currentStep).length) { refreshStepGate(); return; }
        if (!validateStep(currentStep, false)) return;

        try {
            await saveDraft();
            if (currentStep < totalSteps) showStep(currentStep + 1);
        } catch (error) {
            setDraftStatus(error.message, 'error');
            alert(error.message);
        }
    }

    eventForm.addEventListener('submit', function (event) {
        const incomplete = Array.from({ length: totalSteps }, (_, i) => i + 1).find((step) => missingFields(step).length);
        if (incomplete) {
            event.preventDefault();
            showStep(incomplete);
            return;
        }
        setDraftStatus('Finalisation de l’événement…', 'saving');
    });

    function prevStep() {
        if (currentStep > 1) showStep(currentStep - 1);
    }

    function validateStep(step, strict = false) {
        const stepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        const fields = stepElement.querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalidField = null;

        fields.forEach(field => {
            if (field.type === 'radio' || field.type === 'file') return;
            const value = field.type === 'checkbox' ? (field.checked ? field.value : '') : field.value.trim();
            let fieldValid = true;

            if (strict && field.required && !value) {
                fieldValid = false;
            } else if (!value) {
                field.classList.remove('is-invalid');
                return;
            } else if (field.type === 'email') {
                fieldValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            } else if (field.type === 'url') {
                try { new URL(value); } catch (e) { fieldValid = false; }
            }

            field.classList.toggle('is-invalid', !fieldValid);
            if (!fieldValid) {
                isValid = false;
                firstInvalidField ??= field;
            }
        });

        if (!isValid) {
            alert('Certains champs renseignés ne sont pas au bon format.');
            firstInvalidField?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalidField?.focus();
        }

        return isValid;
    }

    const citySelect = document.getElementById('ville');
    const countrySelect = document.getElementById('pays');
    const venueInput = document.getElementById('lieu');
    const mapLinkInput = document.getElementById('lien_google_map');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const mapHelp = document.getElementById('mapHelp');
    const venueSuggestions = document.getElementById('venueSuggestions');
    const geocodeUrl = @json(route('events.geocode'));
    const pricingInputs = document.querySelectorAll('input[name="type_tarification_id"]');
    const paidFields = document.querySelectorAll('.paid-field');
    let geocodeTimer;
    let geocodeController;

    function hideVenueSuggestions() {
        venueSuggestions.classList.add('d-none');
        venueSuggestions.replaceChildren();
    }

    function selectVenue(place) {
        venueInput.value = place.name;
        latitudeInput.value = place.latitude;
        longitudeInput.value = place.longitude;
        mapLinkInput.value = `https://www.google.com/maps?q=${place.latitude},${place.longitude}`;
        mapHelp.textContent = `Lieu sélectionné : ${place.display_name}`;
        hideVenueSuggestions();
        updatePreview();
    }

    function renderVenueSuggestions(results) {
        venueSuggestions.replaceChildren();

        results.forEach(place => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'list-group-item list-group-item-action text-start';
            button.setAttribute('role', 'option');

            const title = document.createElement('strong');
            title.className = 'd-block';
            title.textContent = place.name;

            const address = document.createElement('small');
            address.className = 'text-muted';
            address.textContent = place.display_name;

            button.append(title, address);
            button.addEventListener('click', () => selectVenue(place));
            venueSuggestions.appendChild(button);
        });

        venueSuggestions.classList.toggle('d-none', results.length === 0);
    }

    async function searchVenues() {
        const location = venueInput?.value.trim();
        if (!location || location.length < 3) {
            mapLinkInput.value = '';
            latitudeInput.value = '';
            longitudeInput.value = '';
            hideVenueSuggestions();
            mapHelp.textContent = 'Sélectionnez la ville et le pays, puis saisissez un lieu précis.';
            return;
        }

        geocodeController?.abort();
        geocodeController = new AbortController();
        mapHelp.textContent = 'Recherche des coordonnées avec OpenStreetMap…';
        const query = [location, citySelect?.value, countrySelect?.value].filter(Boolean).join(', ');

        try {
            const response = await fetch(`${geocodeUrl}?q=${encodeURIComponent(query)}`, {
                headers: { 'Accept': 'application/json' },
                signal: geocodeController.signal,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Lieu introuvable.');

            renderVenueSuggestions(data.results || []);
            mapHelp.textContent = 'Sélectionnez le lieu correspondant dans la liste.';
        } catch (error) {
            if (error.name === 'AbortError') return;
            mapLinkInput.value = '';
            latitudeInput.value = '';
            longitudeInput.value = '';
            hideVenueSuggestions();
            mapHelp.textContent = error.message;
        }
    }

    function scheduleVenueGeocoding() {
        clearTimeout(geocodeTimer);
        mapLinkInput.value = '';
        latitudeInput.value = '';
        longitudeInput.value = '';
        geocodeTimer = setTimeout(searchVenues, 500);
    }

    function selectedPricingLabel() {
        return document.querySelector('input[name="type_tarification_id"]:checked')?.dataset.label || '';
    }

    function updatePricingFields() {
        const isPaid = selectedPricingLabel() === 'payant';

        paidFields.forEach(field => {
            field.hidden = !isPaid;
            field.querySelectorAll('input, select').forEach(input => {
                input.disabled = !isPaid;
                input.required = isPaid;
            });
        });
    }

    citySelect?.addEventListener('change', function () {
        const selectedCountry = this.selectedOptions[0]?.dataset.country;
        if (selectedCountry && countrySelect && !countrySelect.value) countrySelect.value = selectedCountry;
        if (venueInput.value.trim()) scheduleVenueGeocoding();
    });

    countrySelect?.addEventListener('change', () => {
        if (venueInput.value.trim()) scheduleVenueGeocoding();
    });

    venueInput?.addEventListener('input', scheduleVenueGeocoding);
    document.addEventListener('click', event => {
        if (!venueInput.contains(event.target) && !venueSuggestions.contains(event.target)) hideVenueSuggestions();
    });
    pricingInputs.forEach(input => input.addEventListener('change', updatePricingFields));

    // Dropzones: file name, image thumbnail and drag state.
    document.querySelectorAll('[data-dropzone]').forEach(zone => {
        const input = zone.querySelector('input[type=file]');
        const name = zone.querySelector('[data-dz-name]');
        const icon = zone.querySelector('.dz-icon');
        const initialName = name.textContent;
        const initialIcon = icon.innerHTML;

        ['dragenter', 'dragover'].forEach(type => input.addEventListener(type, () => zone.classList.add('is-over')));
        ['dragleave', 'drop'].forEach(type => input.addEventListener(type, () => zone.classList.remove('is-over')));

        input.addEventListener('change', () => {
            const file = input.files[0];
            zone.classList.toggle('has-file', Boolean(file));
            name.textContent = file ? file.name : initialName;
            zone.querySelector('.dz-action').textContent = file ? 'Changer' : 'Parcourir';
            if (file && zone.dataset.dropzone === 'image') {
                const url = URL.createObjectURL(file);
                icon.innerHTML = `<img src="${url}" alt="">`;
            } else {
                icon.innerHTML = initialIcon;
            }
            updatePreview();
        });
    });

    // Live preview
    const pv = id => document.getElementById(id);
    const dateFormatter = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const timeFormatter = new Intl.DateTimeFormat('fr-FR', { hour: '2-digit', minute: '2-digit' });

    function setRow(id, text, fallback) {
        const row = pv(id);
        row.classList.toggle('is-empty', !text);
        row.querySelector('span').textContent = text || fallback;
    }

    function updatePreview() {
        const title = pv('titre').value.trim();
        pv('pvTitle').textContent = title || 'Titre de votre événement';
        pv('pvTitle').classList.toggle('is-placeholder', !title);

        const category = pv('category_id');
        pv('pvCategory').textContent = category.value ? category.selectedOptions[0].textContent.trim() : 'Catégorie';

        const imageFile = pv('image').files[0];
        if (imageFile) {
            pv('pvImage').src = URL.createObjectURL(imageFile);
        }
        pv('pvImage').hidden = !imageFile;
        pv('pvEmpty').hidden = Boolean(imageFile);

        const start = pv('date_debut').value ? new Date(pv('date_debut').value) : null;
        const end = pv('date_fin').value ? new Date(pv('date_fin').value) : null;
        pv('pvDay').textContent = start ? String(start.getDate()).padStart(2, '0') : '--';
        pv('pvMonth').textContent = start ? start.toLocaleDateString('fr-FR', { month: 'short' }).replace('.', '') : 'mois';
        setRow('pvWhen', start ? `${dateFormatter.format(start)} · ${timeFormatter.format(start)}${end ? ' → ' + timeFormatter.format(end) : ''}` : '', 'Date et heure à définir');

        const hint = pv('durationHint');
        if (start && end) {
            const minutes = Math.round((end - start) / 60000);
            hint.textContent = minutes > 0
                ? `Durée : ${minutes >= 1440 ? Math.floor(minutes / 1440) + ' j ' : ''}${Math.floor((minutes % 1440) / 60)} h ${String(minutes % 60).padStart(2, '0')}`
                : 'La fin doit être postérieure au début.';
            hint.classList.toggle('text-danger', minutes <= 0);
        } else {
            hint.textContent = '';
        }

        const place = [pv('lieu').value.trim(), citySelect.value].filter(Boolean).join(', ');
        setRow('pvWhere', place, 'Lieu à définir');

        const pricing = selectedPricingLabel();
        const price = pv('prix').value;
        const currency = pv('devise_id').value ? pv('devise_id').selectedOptions[0].textContent.trim() : '';
        pv('pvPrice').textContent = pricing === 'payant'
            ? (price ? `${Number(price).toLocaleString('fr-FR')} ${currency}`.trim() : 'Payant')
            : (pricing ? 'Gratuit' : '—');

        const capacity = pv('capacite_maximale').value;
        pv('pvCapacity').textContent = capacity ? `${Number(capacity).toLocaleString('fr-FR')} places` : 'Capacité libre';

        const status = document.querySelector('input[name="statut"]:checked')?.value;
        pv('pvStatus').textContent = status === 'publie' ? 'Publié' : 'Brouillon';

        const visibility = document.querySelector('input[name="visibilite_id"]:checked')?.dataset.label;
        pv('pvVisibility').hidden = !visibility;
        pv('pvVisibility').textContent = visibility || '';
    }

    eventForm.addEventListener('input', updatePreview);
    eventForm.addEventListener('change', updatePreview);
    eventForm.addEventListener('input', refreshStepGate);
    eventForm.addEventListener('change', refreshStepGate);

    document.querySelectorAll('.step-item').forEach((item, index) => {
        item.addEventListener('click', () => {
            const stepNum = index + 1;
            if (stepNum <= currentStep) showStep(stepNum);
        });
    });

    updatePricingFields();
    updatePreview();

    // After a server-side validation error, open the step that contains the first error.
    const firstError = document.querySelector('.form-step .is-invalid, .form-step .invalid-feedback');
    const errorStep = Number(firstError?.closest('.form-step')?.dataset.step || 1);
    if (errorStep > 1) {
        currentStep = errorStep;
        document.querySelectorAll('.form-step').forEach(stepEl => stepEl.classList.toggle('active', Number(stepEl.dataset.step) === errorStep));
    }
    updateProgress();
    refreshStepGate();
</script>
@endpush
