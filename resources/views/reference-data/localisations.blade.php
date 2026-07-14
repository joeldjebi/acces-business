@extends('layouts.app')

@section('title', 'Pays & villes')

@push('styles')
<style>
    .ref-page {
        --ink: #171713;
        --text: #2c2a25;
        --muted: #746f65;
        --line: #dfd7cb;
        --panel: #fffefa;
        --panel-soft: #f8f4ec;
        --gold: #b98943;
        --green: #2e7b65;
        --red: #a4514a;
        --shadow: 0 18px 45px rgba(39, 33, 25, 0.08);
        color: var(--text);
        max-width: 1320px;
        margin: 0 auto;
    }

    .ref-head {
        margin-bottom: 18px;
    }

    .ref-kicker {
        color: var(--gold);
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .ref-title {
        color: var(--ink);
        font-size: clamp(1.7rem, 2.5vw, 2.45rem);
        font-weight: 600;
        line-height: 1.08;
        margin: 6px 0 0;
    }

    .ref-copy {
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 10px 0 0;
        max-width: 760px;
    }

    .ref-grid {
        display: grid;
        gap: 18px;
        grid-template-columns: minmax(300px, 0.45fr) minmax(0, 1fr);
        align-items: start;
    }

    .ref-stack {
        display: grid;
        gap: 18px;
    }

    .ref-card {
        background: var(--panel);
        border: 1px solid rgba(223, 215, 203, 0.74);
        border-radius: 18px;
        box-shadow: 0 18px 42px rgba(39, 33, 25, 0.055);
        overflow: hidden;
    }

    .ref-card-head {
        border-bottom: 1px solid rgba(223, 215, 203, 0.62);
        padding: 18px 20px;
    }

    .ref-card-head h2 {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .ref-card-head p {
        color: var(--muted);
        font-size: 0.82rem;
        margin: 4px 0 0;
    }

    .ref-form {
        display: grid;
        gap: 12px;
        padding: 18px;
    }

    .ref-label {
        color: var(--muted);
        font-size: 0.76rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .ref-input,
    .ref-select {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--ink);
        min-height: 42px;
        padding: 10px 12px;
        width: 100%;
    }

    .ref-btn {
        align-items: center;
        border: 1px solid transparent;
        border-radius: 999px;
        display: inline-flex;
        gap: 8px;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        text-decoration: none;
    }

    .ref-btn.primary {
        background: var(--ink);
        color: #fff;
    }

    .ref-list-row {
        align-items: center;
        border-bottom: 1px solid rgba(223, 215, 203, 0.72);
        display: grid;
        gap: 12px;
        padding: 14px 18px;
    }

    .country-row {
        grid-template-columns: 58px minmax(150px, 1fr) 96px 96px 104px 94px;
    }

    .city-row {
        grid-template-columns: minmax(150px, 1fr) minmax(150px, 0.8fr) 104px 94px;
    }

    .ref-list-row:last-child {
        border-bottom: 0;
    }

    .ref-list-head {
        background: var(--panel-soft);
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .flag-preview {
        align-items: center;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        display: inline-flex;
        font-size: 1.45rem;
        height: 44px;
        justify-content: center;
        width: 44px;
    }

    .status-pill {
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.76rem;
        justify-content: center;
        min-height: 28px;
        padding: 6px 10px;
        width: fit-content;
    }

    .status-pill.active {
        background: rgba(46, 123, 101, 0.12);
        color: var(--green);
    }

    .status-pill.inactive {
        background: rgba(164, 81, 74, 0.13);
        color: var(--red);
    }

    .row-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .icon-btn {
        align-items: center;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--ink);
        display: inline-flex;
        height: 36px;
        justify-content: center;
        width: 36px;
    }

    .icon-btn.danger {
        color: var(--red);
    }

    .empty-state {
        color: var(--muted);
        padding: 34px 18px;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .ref-grid {
            grid-template-columns: 1fr;
        }

        .country-row,
        .city-row,
        .ref-list-head {
            grid-template-columns: 1fr;
        }

        .ref-list-head {
            display: none;
        }

        .row-actions {
            justify-content: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="ref-page">
    <div class="ref-head">
        <div class="ref-kicker">Référentiels</div>
        <h1 class="ref-title">Pays & villes</h1>
        <p class="ref-copy">Gérez les pays proposés dans les événements, avec indicatif téléphonique, devise, drapeau et villes associées.</p>
    </div>

    <div class="ref-grid">
        <div class="ref-stack">
            <section class="ref-card">
                <div class="ref-card-head">
                    <h2>Nouveau pays</h2>
                    <p>Ajoutez un pays utilisable dans les formulaires événement.</p>
                </div>
                <form action="{{ route('countries.store') }}" method="POST" class="ref-form">
                    @csrf
                    <div>
                        <label for="country-nom" class="ref-label">Pays</label>
                        <input id="country-nom" name="nom" class="ref-input @error('nom') is-invalid @enderror" value="{{ old('nom') }}" maxlength="120" placeholder="Ex: Côte d'Ivoire" required>
                        @error('nom')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="indicatif" class="ref-label">Indicatif</label>
                            <input id="indicatif" name="indicatif" class="ref-input @error('indicatif') is-invalid @enderror" value="{{ old('indicatif') }}" maxlength="12" placeholder="+225">
                            @error('indicatif')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="currency" class="ref-label">Currency</label>
                            <input id="currency" name="currency" class="ref-input @error('currency') is-invalid @enderror" value="{{ old('currency') }}" maxlength="12" placeholder="XOF">
                            @error('currency')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="flag" class="ref-label">Flag</label>
                            <input id="flag" name="flag" class="ref-input @error('flag') is-invalid @enderror" value="{{ old('flag') }}" maxlength="16" placeholder="CI">
                            @error('flag')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="ref-btn primary">
                        <i class="bi bi-plus-circle"></i>
                        Créer le pays
                    </button>
                </form>
            </section>

            <section class="ref-card">
                <div class="ref-card-head">
                    <h2>Nouvelle ville</h2>
                    <p>Associez une ville à un pays existant.</p>
                </div>
                <form action="{{ route('cities.store') }}" method="POST" class="ref-form">
                    @csrf
                    <div>
                        <label for="city-country" class="ref-label">Pays</label>
                        <select id="city-country" name="country_id" class="ref-select @error('country_id') is-invalid @enderror" required>
                            <option value="">Sélectionner</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->flag ? $country->flag . ' ' : '' }}{{ $country->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="city-nom" class="ref-label">Ville</label>
                        <input id="city-nom" name="nom" class="ref-input @error('nom') is-invalid @enderror" value="{{ old('nom') }}" maxlength="120" placeholder="Ex: Abidjan" required>
                    </div>
                    <button type="submit" class="ref-btn primary">
                        <i class="bi bi-plus-circle"></i>
                        Créer la ville
                    </button>
                </form>
            </section>
        </div>

        <div class="ref-stack">
            <section class="ref-card">
                <div class="ref-card-head">
                    <h2>Pays enregistrés</h2>
                    <p>{{ $countries->count() }} pays, {{ $cities->count() }} ville(s) associée(s).</p>
                </div>

                <div class="ref-list-row ref-list-head country-row">
                    <div>Flag</div>
                    <div>Pays</div>
                    <div>Indicatif</div>
                    <div>Currency</div>
                    <div>Statut</div>
                    <div class="text-end">Actions</div>
                </div>

                @forelse($countries as $country)
                    <form id="update-country-{{ $country->id }}" action="{{ route('countries.update', $country) }}" method="POST">
                        @csrf
                        @method('PUT')
                    </form>
                    <div class="ref-list-row country-row">
                        <div>
                            <input form="update-country-{{ $country->id }}" name="flag" class="ref-input" value="{{ old('flag', $country->flag) }}" maxlength="16" aria-label="Drapeau">
                        </div>
                        <div>
                            <input form="update-country-{{ $country->id }}" name="nom" class="ref-input" value="{{ old('nom', $country->nom) }}" maxlength="120" required>
                            <small class="text-muted">{{ $country->cities_count }} ville(s)</small>
                        </div>
                        <div>
                            <input form="update-country-{{ $country->id }}" name="indicatif" class="ref-input" value="{{ old('indicatif', $country->indicatif) }}" maxlength="12">
                        </div>
                        <div>
                            <input form="update-country-{{ $country->id }}" name="currency" class="ref-input" value="{{ old('currency', $country->currency) }}" maxlength="12">
                        </div>
                        <div>
                            <label class="status-pill {{ $country->statut ? 'active' : 'inactive' }}">
                                <input form="update-country-{{ $country->id }}" type="checkbox" name="statut" value="1" class="me-2" {{ $country->statut ? 'checked' : '' }}>
                                {{ $country->statut ? 'Actif' : 'Inactif' }}
                            </label>
                        </div>
                        <div class="row-actions">
                            <button form="update-country-{{ $country->id }}" type="submit" class="icon-btn" title="Enregistrer">
                                <i class="bi bi-check2"></i>
                            </button>
                            <form action="{{ route('countries.destroy', $country) }}" method="POST" onsubmit="return confirm('Supprimer ce pays ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger" title="Supprimer" {{ $country->cities_count > 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Aucun pays enregistré.</div>
                @endforelse
            </section>

            <section class="ref-card">
                <div class="ref-card-head">
                    <h2>Villes enregistrées</h2>
                    <p>Les villes actives sont proposées dans le formulaire de création d'événement.</p>
                </div>

                <div class="ref-list-row ref-list-head city-row">
                    <div>Ville</div>
                    <div>Pays</div>
                    <div>Statut</div>
                    <div class="text-end">Actions</div>
                </div>

                @forelse($cities as $city)
                    <form id="update-city-{{ $city->id }}" action="{{ route('cities.update', $city) }}" method="POST">
                        @csrf
                        @method('PUT')
                    </form>
                    <div class="ref-list-row city-row">
                        <div>
                            <input form="update-city-{{ $city->id }}" name="nom" class="ref-input" value="{{ old('nom', $city->nom) }}" maxlength="120" required>
                        </div>
                        <div>
                            <select form="update-city-{{ $city->id }}" name="country_id" class="ref-select" required>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ $city->country_id === $country->id ? 'selected' : '' }}>
                                        {{ $country->flag ? $country->flag . ' ' : '' }}{{ $country->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="status-pill {{ $city->statut ? 'active' : 'inactive' }}">
                                <input form="update-city-{{ $city->id }}" type="checkbox" name="statut" value="1" class="me-2" {{ $city->statut ? 'checked' : '' }}>
                                {{ $city->statut ? 'Actif' : 'Inactif' }}
                            </label>
                        </div>
                        <div class="row-actions">
                            <button form="update-city-{{ $city->id }}" type="submit" class="icon-btn" title="Enregistrer">
                                <i class="bi bi-check2"></i>
                            </button>
                            <form action="{{ route('cities.destroy', $city) }}" method="POST" onsubmit="return confirm('Supprimer cette ville ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Aucune ville enregistrée.</div>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
