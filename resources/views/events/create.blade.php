@extends('layouts.app')

@section('title', 'Créer un Événement')

@push('styles')
<style>
    .create-page {
        max-width: 1160px;
        margin: 0 auto;
        padding-bottom: 24px;
    }

    .create-hero {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        align-items: flex-end;
        margin-bottom: 22px;
        padding: 8px 2px 0;
    }

    .create-eyebrow {
        color: #9a6d2f;
        font-size: 0.76rem;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .create-title {
        color: #171713;
        font-size: clamp(1.75rem, 3vw, 2.6rem);
        font-weight: 500;
        margin: 0;
    }

    .create-subtitle {
        color: #6b665c;
        margin: 10px 0 0;
        max-width: 640px;
    }

    .draft-status {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        padding: 10px 14px;
        border: 1px solid rgba(185, 137, 67, 0.24);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
        color: #6d6254;
        font-size: 0.88rem;
        white-space: nowrap;
    }

    .draft-status i {
        color: #b98943;
    }

    .draft-status.saving i {
        animation: pulseDraft 1s ease-in-out infinite;
    }

    .draft-status.saved {
        color: #426447;
        border-color: rgba(66, 100, 71, 0.22);
    }

    .draft-status.saved i {
        color: #426447;
    }

    .draft-status.error {
        color: #9c3d35;
        border-color: rgba(156, 61, 53, 0.24);
    }

    .draft-status.error i {
        color: #9c3d35;
    }

    @keyframes pulseDraft {
        0%, 100% {
            opacity: 0.45;
        }
        50% {
            opacity: 1;
        }
    }

    .create-shell {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .form-section {
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(31, 29, 24, 0.08);
        border-radius: 24px;
        padding: 34px;
        margin-bottom: 25px;
        box-shadow: 0 24px 70px rgba(36, 32, 25, 0.08);
    }

    .form-section-title {
        font-size: 1.28rem;
        font-weight: 500;
        color: #171713;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid rgba(31, 29, 24, 0.08);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .progress-container {
        position: sticky;
        top: 92px;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(31, 29, 24, 0.08);
        border-radius: 24px;
        padding: 20px;
        box-shadow: 0 18px 50px rgba(36, 32, 25, 0.07);
    }

    .progress-steps {
        display: flex;
        flex-direction: column;
        gap: 10px;
        position: relative;
    }

    .progress-line {
        position: absolute;
        top: 20px;
        bottom: 20px;
        left: 20px;
        width: 1px;
        background: #e7e0d4;
        z-index: 1;
    }

    .progress-line-fill {
        width: 100%;
        height: 0;
        background: #b98943;
        transition: width 0.5s ease;
        border-radius: 999px;
    }

    .step-item {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 8px;
        border-radius: 16px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .step-item:hover {
        background: rgba(185, 137, 67, 0.06);
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 1px solid #ded7cc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        color: #93897b;
        transition: all 0.3s ease;
        flex: 0 0 40px;
    }

    .step-item.active .step-circle {
        background: #171713;
        border-color: #171713;
        color: white;
        box-shadow: 0 12px 28px rgba(23, 23, 19, 0.18);
    }

    .step-item.completed .step-circle {
        background: #426447;
        border-color: #426447;
        color: white;
    }

    .step-item.completed .step-circle::after {
        content: '✓';
        font-size: 18px;
    }

    .step-label {
        font-size: 0.92rem;
        font-weight: 400;
        color: #7c7266;
        text-align: left;
        transition: color 0.3s ease;
    }

    .step-item.active .step-label {
        color: #171713;
        font-weight: 500;
    }

    .step-item.completed .step-label {
        color: #426447;
    }

    .form-step {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .form-step.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-label {
        font-weight: 400;
        color: #38342e;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-control, .form-select {
        border: 1px solid #ddd6ca;
        border-radius: 16px;
        padding: 12px 15px;
        transition: all 0.3s ease;
        background-color: rgba(255, 255, 255, 0.84);
        color: #211f1a;
    }

    .form-control:focus, .form-select:focus {
        border-color: #b98943;
        box-shadow: 0 0 0 4px rgba(185, 137, 67, 0.12);
        outline: none;
    }

    .form-text {
        color: #8a8175 !important;
    }

    .btn-nav {
        background: #171713;
        border: 1px solid #171713;
        border-radius: 999px;
        padding: 12px 22px;
        font-weight: 500;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-nav:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(23, 23, 19, 0.18);
        color: white;
    }

    .btn-nav-secondary {
        background: #f5f0e8;
        color: #5f5549;
        border: 1px solid #e5dccc;
        border-radius: 999px;
        padding: 12px 22px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-nav-secondary:hover {
        background: #ede4d7;
        color: #332e28;
    }

    .step-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 34px;
        padding-top: 24px;
        border-top: 1px solid rgba(31, 29, 24, 0.08);
    }

    .form-check-input:checked {
        background-color: #171713;
        border-color: #171713;
    }

    @media (max-width: 992px) {
        .create-shell {
            grid-template-columns: 1fr;
        }

        .progress-container {
            position: static;
        }

        .progress-steps {
            flex-direction: row;
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .progress-line {
            display: none;
        }

        .step-item {
            width: auto;
            min-width: 122px;
            flex-direction: column;
            gap: 6px;
        }

        .step-label {
            font-size: 0.75rem;
            text-align: center;
        }
    }

    @media (max-width: 768px) {
        .create-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .draft-status {
            white-space: normal;
        }

        .form-section {
            padding: 22px;
            border-radius: 20px;
        }

        .step-actions {
            flex-direction: column-reverse;
        }

        .step-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="create-page">
    <section class="create-hero">
        <div>
            <div class="create-eyebrow">Création d'événement</div>
            <h1 class="create-title">Composer une expérience claire et mémorable</h1>
            <p class="create-subtitle">Avancez étape par étape. Les informations sont sauvegardées en brouillon à chaque passage à l'étape suivante.</p>
        </div>
        <div class="draft-status" id="draftStatus">
            <i class="bi bi-cloud"></i>
            <span>Aucun brouillon enregistré</span>
        </div>
    </section>

    <div class="create-shell">
        <div class="progress-container">
            <div class="progress-steps">
                <div class="progress-line">
                    <div class="progress-line-fill" id="progressFill"></div>
                </div>

                <div class="step-item active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Informations</div>
                </div>

                <div class="step-item" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Dates</div>
                </div>

                <div class="step-item" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Localisation</div>
                </div>

                <div class="step-item" data-step="4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Organisation</div>
                </div>

                <div class="step-item" data-step="5">
                    <div class="step-circle">5</div>
                    <div class="step-label">Tarification</div>
                </div>

                <div class="step-item" data-step="6">
                    <div class="step-circle">6</div>
                    <div class="step-label">Statut</div>
                </div>

                <div class="step-item" data-step="7">
                    <div class="step-circle">7</div>
                    <div class="step-label">Métadonnées</div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="form-section-title">
                <i class="bi bi-calendar-plus"></i>
                <span id="stepTitle">Informations de Base</span>
            </h3>

            <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" id="eventForm" data-draft-url="{{ route('events.draft') }}" novalidate>
                @csrf
                <input type="hidden" id="draftEventId" name="draft_event_id" value="{{ old('draft_event_id') }}">

                <!-- Étape 1: Informations de base -->
                <div class="form-step active" data-step="1">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="titre" class="form-label">
                                    <i class="bi bi-type"></i>
                                    Titre de l'événement <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('titre') is-invalid @enderror"
                                       id="titre"
                                       name="titre"
                                       value="{{ old('titre') }}"
                                       required>
                                @error('titre')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category_id" class="form-label">
                                    <i class="bi bi-tag"></i>
                                    Catégorie <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id"
                                        name="category_id"
                                        required>
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="bi bi-text-paragraph"></i>
                            Description
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">
                            <i class="bi bi-image"></i>
                            Image de l'événement
                        </label>
                        <input type="file"
                               class="form-control @error('image') is-invalid @enderror"
                               id="image"
                               name="image"
                               accept="image/*">
                        @error('image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Format: JPG, PNG, GIF (max 2MB)</small>
                    </div>

                    <div class="step-actions">
                        <a href="{{ route('events.index') }}" class="btn btn-nav-secondary">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </a>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 2: Dates et horaires -->
                <div class="form-step" data-step="2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_debut" class="form-label">
                                    <i class="bi bi-calendar3"></i>
                                    Date de début <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control @error('date_debut') is-invalid @enderror"
                                       id="date_debut"
                                       name="date_debut"
                                       value="{{ old('date_debut') }}"
                                       required>
                                @error('date_debut')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="heure_debut" class="form-label">
                                    <i class="bi bi-clock"></i>
                                    Heure de début <span class="text-danger">*</span>
                                </label>
                                <input type="time"
                                       class="form-control @error('heure_debut') is-invalid @enderror"
                                       id="heure_debut"
                                       name="heure_debut"
                                       value="{{ old('heure_debut') }}"
                                       required>
                                @error('heure_debut')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_fin" class="form-label">
                                    <i class="bi bi-calendar-check"></i>
                                    Date de fin <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control @error('date_fin') is-invalid @enderror"
                                       id="date_fin"
                                       name="date_fin"
                                       value="{{ old('date_fin') }}"
                                       required>
                                @error('date_fin')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="heure_fin" class="form-label">
                                    <i class="bi bi-clock-history"></i>
                                    Heure de fin <span class="text-danger">*</span>
                                </label>
                                <input type="time"
                                       class="form-control @error('heure_fin') is-invalid @enderror"
                                       id="heure_fin"
                                       name="heure_fin"
                                       value="{{ old('heure_fin') }}"
                                       required>
                                @error('heure_fin')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()">
                            <i class="bi bi-arrow-left me-1"></i>Précédent
                        </button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 3: Localisation -->
                <div class="form-step" data-step="3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lieu" class="form-label">
                                    <i class="bi bi-geo-alt"></i>
                                    Lieu
                                </label>
                                <input type="text"
                                       class="form-control @error('lieu') is-invalid @enderror"
                                       id="lieu"
                                       name="lieu"
                                       value="{{ old('lieu') }}">
                                @error('lieu')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ville" class="form-label">
                                    <i class="bi bi-building"></i>
                                    Ville
                                </label>
                                <select class="form-select @error('ville') is-invalid @enderror"
                                        id="ville"
                                        name="ville">
                                    <option value="">Sélectionner une ville</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->nom }}" data-country="{{ optional($city->country)->nom }}" {{ old('ville') === $city->nom ? 'selected' : '' }}>
                                            {{ $city->nom }}{{ $city->country ? ' - ' . $city->country->nom : '' }}
                                        </option>
                                    @endforeach
                                    @if(old('ville') && !$cities->contains('nom', old('ville')))
                                        <option value="{{ old('ville') }}" selected>{{ old('ville') }}</option>
                                    @endif
                                </select>
                                @error('ville')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code_postal" class="form-label">
                                    <i class="bi bi-mailbox"></i>
                                    Code postal
                                </label>
                                <input type="text"
                                       class="form-control @error('code_postal') is-invalid @enderror"
                                       id="code_postal"
                                       name="code_postal"
                                       value="{{ old('code_postal') }}">
                                @error('code_postal')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pays" class="form-label">
                                    <i class="bi bi-globe"></i>
                                    Pays
                                </label>
                                <select class="form-select @error('pays') is-invalid @enderror"
                                        id="pays"
                                        name="pays">
                                    <option value="">Sélectionner un pays</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->nom }}" {{ old('pays') === $country->nom ? 'selected' : '' }}>
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
                                @error('pays')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="adresse_complete" class="form-label">
                            <i class="bi bi-geo-alt-fill"></i>
                            Adresse complète
                        </label>
                        <textarea class="form-control @error('adresse_complete') is-invalid @enderror"
                                  id="adresse_complete"
                                  name="adresse_complete"
                                  rows="2">{{ old('adresse_complete') }}</textarea>
                        @error('adresse_complete')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="lien_google_map" class="form-label">
                            <i class="bi bi-geo-alt-fill"></i>
                            Lien Google Maps
                        </label>
                        <input type="url"
                               class="form-control @error('lien_google_map') is-invalid @enderror"
                               id="lien_google_map"
                               name="lien_google_map"
                               value="{{ old('lien_google_map') }}"
                               placeholder="https://maps.google.com/...">
                        @error('lien_google_map')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Collez ici le lien de partage Google Maps de l'adresse de l'événement</small>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()">
                            <i class="bi bi-arrow-left me-1"></i>Précédent
                        </button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 4: Organisation -->
                <div class="form-step" data-step="4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="organisateur" class="form-label">
                                    <i class="bi bi-person-badge"></i>
                                    Organisateur
                                </label>
                                <input type="text"
                                       class="form-control @error('organisateur') is-invalid @enderror"
                                       id="organisateur"
                                       name="organisateur"
                                       value="{{ old('organisateur') }}">
                                @error('organisateur')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email_contact" class="form-label">
                                    <i class="bi bi-envelope"></i>
                                    Email de contact
                                </label>
                                <input type="email"
                                       class="form-control @error('email_contact') is-invalid @enderror"
                                       id="email_contact"
                                       name="email_contact"
                                       value="{{ old('email_contact') }}">
                                @error('email_contact')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telephone" class="form-label">
                                    <i class="bi bi-telephone"></i>
                                    Téléphone
                                </label>
                                <input type="text"
                                       class="form-control @error('telephone') is-invalid @enderror"
                                       id="telephone"
                                       name="telephone"
                                       value="{{ old('telephone') }}">
                                @error('telephone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="site_web" class="form-label">
                                    <i class="bi bi-link-45deg"></i>
                                    Site web
                                </label>
                                <input type="url"
                                       class="form-control @error('site_web') is-invalid @enderror"
                                       id="site_web"
                                       name="site_web"
                                       value="{{ old('site_web') }}"
                                       placeholder="https://example.com">
                                @error('site_web')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()">
                            <i class="bi bi-arrow-left me-1"></i>Précédent
                        </button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 5: Tarification -->
                <div class="form-step" data-step="5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="type_tarification_id" class="form-label">
                                    <i class="bi bi-currency-exchange"></i>
                                    Type de tarification <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('type_tarification_id') is-invalid @enderror"
                                        id="type_tarification_id"
                                        name="type_tarification_id"
                                        required>
                                    <option value="">Sélectionner</option>
                                    @foreach($typeTarifications as $type)
                                        <option value="{{ $type->id }}" {{ old('type_tarification_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_tarification_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="prix" class="form-label">
                                    <i class="bi bi-cash-coin"></i>
                                    Prix
                                </label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       class="form-control @error('prix') is-invalid @enderror"
                                       id="prix"
                                       name="prix"
                                       value="{{ old('prix') }}">
                                @error('prix')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="devise_id" class="form-label">
                                    <i class="bi bi-currency-dollar"></i>
                                    Devise
                                </label>
                                <select class="form-select @error('devise_id') is-invalid @enderror"
                                        id="devise_id"
                                        name="devise_id">
                                    <option value="">Sélectionner</option>
                                    @foreach($devises as $devise)
                                        <option value="{{ $devise->id }}" {{ old('devise_id') == $devise->id ? 'selected' : '' }}>
                                            {{ $devise->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('devise_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()">
                            <i class="bi bi-arrow-left me-1"></i>Précédent
                        </button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 6: Statut, visibilité et capacité -->
                <div class="form-step" data-step="6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="statut" class="form-label">
                                    <i class="bi bi-toggle-on"></i>
                                    Statut <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('statut') is-invalid @enderror"
                                        id="statut"
                                        name="statut"
                                        required>
                                    <option value="brouillon" {{ old('statut', 'brouillon') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                    <option value="publie" {{ old('statut') == 'publie' ? 'selected' : '' }}>Publié</option>
                                    <option value="annule" {{ old('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                                    <option value="termine" {{ old('statut') == 'termine' ? 'selected' : '' }}>Terminé</option>
                                    <option value="reporte" {{ old('statut') == 'reporte' ? 'selected' : '' }}>Reporté</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="visibilite_id" class="form-label">
                                    <i class="bi bi-eye"></i>
                                    Visibilité <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('visibilite_id') is-invalid @enderror"
                                        id="visibilite_id"
                                        name="visibilite_id"
                                        required>
                                    <option value="">Sélectionner</option>
                                    @foreach($visibilites as $visibilite)
                                        <option value="{{ $visibilite->id }}" {{ old('visibilite_id') == $visibilite->id ? 'selected' : '' }}>
                                            {{ $visibilite->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('visibilite_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacite_maximale" class="form-label">
                                    <i class="bi bi-people"></i>
                                    Capacité maximale
                                </label>
                                <input type="number"
                                       min="1"
                                       class="form-control @error('capacite_maximale') is-invalid @enderror"
                                       id="capacite_maximale"
                                       name="capacite_maximale"
                                       value="{{ old('capacite_maximale') }}">
                                @error('capacite_maximale')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inscription_requise" class="form-label">
                                    <i class="bi bi-clipboard-check"></i>
                                    Inscription requise
                                </label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="inscription_requise"
                                           name="inscription_requise"
                                           value="1"
                                           {{ old('inscription_requise') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inscription_requise">
                                        Oui
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="date_limite_inscription" class="form-label">
                            <i class="bi bi-calendar-x"></i>
                            Date limite d'inscription
                        </label>
                        <input type="date"
                               class="form-control @error('date_limite_inscription') is-invalid @enderror"
                               id="date_limite_inscription"
                               name="date_limite_inscription"
                               value="{{ old('date_limite_inscription') }}">
                        @error('date_limite_inscription')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()">
                            <i class="bi bi-arrow-left me-1"></i>Précédent
                        </button>
                        <button type="button" class="btn btn-nav" onclick="nextStep()">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Étape 7: Métadonnées -->
                <div class="form-step" data-step="7">
                    <div class="form-group">
                        <label for="tags" class="form-label">
                            <i class="bi bi-tags"></i>
                            Tags/Mots-clés
                        </label>
                        <input type="text"
                               class="form-control @error('tags') is-invalid @enderror"
                               id="tags"
                               name="tags"
                               value="{{ old('tags') }}"
                               placeholder="Ex: conférence, technologie, innovation">
                        @error('tags')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Séparez les tags par des virgules</small>
                    </div>

                    <div class="form-group">
                        <label for="notes_internes" class="form-label">
                            <i class="bi bi-sticky"></i>
                            Notes internes
                        </label>
                        <textarea class="form-control @error('notes_internes') is-invalid @enderror"
                                  id="notes_internes"
                                  name="notes_internes"
                                  rows="3">{{ old('notes_internes') }}</textarea>
                        @error('notes_internes')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ces notes ne sont visibles que par les administrateurs</small>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-nav-secondary" onclick="prevStep()">
                            <i class="bi bi-arrow-left me-1"></i>Précédent
                        </button>
                        <button type="submit" class="btn btn-nav">
                            <i class="bi bi-check-circle me-1"></i>Créer l'événement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 7;
    const eventForm = document.getElementById('eventForm');
    const draftInput = document.getElementById('draftEventId');
    const draftStatus = document.getElementById('draftStatus');

    const stepTitles = {
        1: 'Informations de Base',
        2: 'Dates et Horaires',
        3: 'Localisation',
        4: 'Organisation',
        5: 'Tarification',
        6: 'Statut et Visibilité',
        7: 'Métadonnées'
    };

    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        const progressFill = document.getElementById('progressFill');
        progressFill.style.width = window.innerWidth <= 992 ? progress + '%' : '100%';
        progressFill.style.height = window.innerWidth <= 992 ? '100%' : progress + '%';

        document.querySelectorAll('.step-item').forEach((item, index) => {
            const stepNum = index + 1;
            item.classList.remove('active', 'completed');

            if (stepNum < currentStep) {
                item.classList.add('completed');
            } else if (stepNum === currentStep) {
                item.classList.add('active');
            }
        });

        document.getElementById('stepTitle').textContent = stepTitles[currentStep];
    }

    function showStep(step) {
        document.querySelectorAll('.form-step').forEach(stepEl => {
            stepEl.classList.remove('active');
        });

        const stepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        if (stepElement) {
            stepElement.classList.add('active');
        }

        currentStep = step;
        updateProgress();

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function setDraftStatus(message, state = 'idle') {
        const icon = draftStatus.querySelector('i');
        const text = draftStatus.querySelector('span');

        draftStatus.classList.remove('saving', 'saved', 'error');
        if (state) {
            draftStatus.classList.add(state);
        }

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

        if (draftInput.value) {
            formData.set('draft_event_id', draftInput.value);
        }

        setDraftStatus('Sauvegarde du brouillon...', 'saving');

        const response = await fetch(eventForm.dataset.draftUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {})[0]?.[0] || 'Le brouillon n’a pas pu être sauvegardé.';
            throw new Error(message);
        }

        draftInput.value = data.id;
        setDraftStatus(`Brouillon sauvegardé à ${data.saved_at}`, 'saved');
    }

    async function nextStep() {
        if (validateStep(currentStep, false)) {
            try {
                await saveDraft();

                if (currentStep < totalSteps) {
                    showStep(currentStep + 1);
                }
            } catch (error) {
                setDraftStatus(error.message, 'error');
                alert(error.message);
            }
        }
    }

    eventForm.addEventListener('submit', function() {
        setDraftStatus('Finalisation de l’événement...', 'saving');
    });

    function prevStep() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    function validateStep(step, strict = false) {
        const stepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        const fields = stepElement.querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalidField = null;

        fields.forEach(field => {
            const value = field.type === 'checkbox' ? (field.checked ? field.value : '') : field.value.trim();
            let fieldValid = true;

            if (strict && field.required && !value) {
                fieldValid = false;
            } else if (!value) {
                field.classList.remove('is-invalid');
                return;
            } else if (field.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                fieldValid = emailRegex.test(value);
            } else if (field.type === 'url') {
                try {
                    new URL(value);
                } catch (e) {
                    fieldValid = false;
                }
            }

            if (!fieldValid) {
                field.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            alert('Certains champs renseignés ne sont pas au bon format.');
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidField.focus();
            }
        }

        return isValid;
    }

    const citySelect = document.getElementById('ville');
    const countrySelect = document.getElementById('pays');

    citySelect?.addEventListener('change', function() {
        const selectedCountry = this.selectedOptions[0]?.dataset.country;

        if (selectedCountry && countrySelect && !countrySelect.value) {
            countrySelect.value = selectedCountry;
        }
    });

    updateProgress();
    window.addEventListener('resize', updateProgress);

    document.querySelectorAll('.step-item').forEach((item, index) => {
        item.addEventListener('click', function() {
            const stepNum = index + 1;
            if (stepNum <= currentStep) {
                showStep(stepNum);
            }
        });
    });
</script>
@endpush
