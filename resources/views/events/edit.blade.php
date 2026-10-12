@extends('layouts.app')

@section('title', 'Modifier · ' . $event->titre)

@php
    $sections = [
        'infos' => ['icon' => 'bi-card-text', 'label' => 'Informations'],
        'media' => ['icon' => 'bi-image', 'label' => 'Médias et liens'],
        'when' => ['icon' => 'bi-geo-alt', 'label' => 'Date et lieu'],
        'contacts' => ['icon' => 'bi-person-lines-fill', 'label' => 'Contacts'],
        'pricing' => ['icon' => 'bi-ticket-perforated', 'label' => 'Tarification'],
        'access' => ['icon' => 'bi-shield-lock', 'label' => 'Statut et accès'],
        'experience' => ['icon' => 'bi-envelope-open-heart', 'label' => 'Expérience invité'],
        'meta' => ['icon' => 'bi-tags', 'label' => 'Notes internes'],
    ];
    $dateValue = fn ($date, $time) => $date ? $date->format('Y-m-d') . 'T' . substr($time ?: '00:00', 0, 5) : '';
    $currentImage = \App\Support\EventMedia::storageUrl($event->image);
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
    .edit-page { margin: 0 auto; max-width: 1240px; padding-bottom: 24px; }
    .edit-page *, .edit-page *::before, .edit-page *::after { min-width: 0; }
    .edit-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 260px; }
    .edit-section { scroll-margin-top: 84px; }
    .edit-section + .edit-section { margin-top: 16px; }
    .edit-section .ui-card-head h2 { align-items: center; display: flex; gap: 10px; }
    .edit-section .ui-card-head h2 i { align-items: center; background: var(--app-gold-soft); border-radius: 9px; color: var(--app-gold-deep); display: inline-flex; font-size: .9rem; height: 30px; justify-content: center; width: 30px; }
    .current-file { align-items: center; display: flex; gap: 10px; margin-top: 8px; }
    .current-file img { border-radius: 8px; height: 40px; object-fit: cover; width: 64px; }
    .current-file span { color: var(--app-muted); font-size: .76rem; }

    .edit-aside { display: grid; gap: 14px; position: sticky; top: 84px; }
    .toc { padding: 8px; }
    .toc a { align-items: center; border-radius: 10px; color: var(--app-muted); display: flex; font-size: .84rem; font-weight: 500; gap: 10px; padding: 8px 10px; text-decoration: none; transition: background .2s, color .2s; }
    .toc a:hover { background: rgba(23, 23, 19, .04); color: var(--app-ink); }
    .toc a.is-active { background: rgba(23, 23, 19, .06); color: var(--app-ink); font-weight: 600; }
    .toc a.is-active i { color: var(--app-gold); }
    .toc a .err { background: var(--app-red); border-radius: 50%; height: 7px; margin-left: auto; width: 7px; }
    .save-card { padding: 16px; }
    .save-state { align-items: center; color: var(--app-muted); display: flex; font-size: .78rem; gap: 7px; margin-bottom: 12px; }
    .save-state::before { background: var(--app-green); border-radius: 50%; content: ""; height: 7px; width: 7px; }
    .save-state.is-dirty { color: var(--app-gold-deep); }
    .save-state.is-dirty::before { background: var(--app-gold); }
    .save-card .ui-btn { width: 100%; }
    .mobile-save { display: none; }

    @media (max-width: 1099.98px) {
        .edit-grid { grid-template-columns: 1fr; }
        .edit-aside { display: none; }
        .mobile-save { -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); background: color-mix(in srgb, var(--app-bg) 88%, transparent); border-top: 1px solid var(--app-line); bottom: 0; display: flex; gap: 10px; left: 0; padding: 10px 16px calc(10px + env(safe-area-inset-bottom)); position: fixed; right: 0; z-index: 1020; }
        .mobile-save .ui-btn-primary { flex: 1; }
        .edit-page { padding-bottom: 80px; }
    }
</style>
@endpush

@section('content')
<div class="edit-page">
    <x-app.event-header :event="$event" active="edit">
        <x-slot:actions>
            <a href="{{ route('events.show', $event) }}" class="ui-btn ui-btn-ghost"><i class="bi bi-eye"></i>Voir l’événement</a>
        </x-slot:actions>
    </x-app.event-header>

    <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data" id="editForm" class="event-form" novalidate>
        @csrf
        @method('PUT')

        <div class="edit-grid">
            <div>
                <section class="ui-card edit-section" id="infos">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['infos']['icon'] }}"></i>Informations</h2></div>
                    <div class="ui-card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="titre" class="form-label">Titre de l'événement <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre', $event->titre) }}" required>
                                @error('titre')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">Sélectionner</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $event->category_id) == $category->id)>{{ $category->libelle }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $event->description) }}</textarea>
                                @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ui-card edit-section" id="media">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['media']['icon'] }}"></i>Médias et liens</h2></div>
                    <div class="ui-card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="form-label">Image de couverture</span>
                                <label class="dropzone @error('image') border-danger @enderror" data-dropzone="image">
                                    <input type="file" class="@error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                    <span class="dz-icon">@if($currentImage)<img src="{{ $currentImage }}" alt="">@else<i class="bi bi-image"></i>@endif</span>
                                    <span class="dz-text"><strong data-dz-name>{{ $currentImage ? 'Remplacer l’image' : 'Ajouter une image' }}</strong><small>JPG, PNG, GIF · 2 Mo max</small></span>
                                    <span class="dz-action">Parcourir</span>
                                </label>
                                @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <span class="form-label">Déroulé de l’événement <span class="text-muted">(facultatif)</span></span>
                                <label class="dropzone @error('programme_pdf') border-danger @enderror" data-dropzone="pdf">
                                    <input type="file" class="@error('programme_pdf') is-invalid @enderror" id="programme_pdf" name="programme_pdf" accept="application/pdf,.pdf">
                                    <span class="dz-icon"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <span class="dz-text"><strong data-dz-name>{{ $event->programme_pdf ? 'Remplacer le programme' : 'Ajouter le programme' }}</strong><small>PDF · 10 Mo max</small></span>
                                    <span class="dz-action">Parcourir</span>
                                </label>
                                @if($event->programme_pdf)
                                    <div class="current-file"><a href="{{ \App\Support\EventMedia::storageUrl($event->programme_pdf) }}" target="_blank" rel="noopener" class="small fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i>Ouvrir le PDF actuel</a></div>
                                @endif
                                @error('programme_pdf')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="video_url" class="form-label">Lien vidéo <span class="text-muted">(facultatif)</span></label>
                                <input type="url" class="form-control @error('video_url') is-invalid @enderror" id="video_url" name="video_url" value="{{ old('video_url', $event->video_url) }}" placeholder="https://youtube.com/watch?v=...">
                                @error('video_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="site_web" class="form-label">Site web de la structure <span class="text-muted">(facultatif)</span></label>
                                <input type="url" class="form-control @error('site_web') is-invalid @enderror" id="site_web" name="site_web" value="{{ old('site_web', $event->site_web) }}" placeholder="https://www.votre-structure.com" inputmode="url">
                                @error('site_web')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                @include('events.partials.social-links-fields', ['event' => $event])
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ui-card edit-section" id="when">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['when']['icon'] }}"></i>Date et lieu</h2></div>
                    <div class="ui-card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="date_debut" class="form-label">Début <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('date_debut') is-invalid @enderror" id="date_debut" name="date_debut_at" value="{{ old('date_debut_at', $dateValue($event->date_debut, $event->heure_debut)) }}" required>
                                @error('date_debut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_fin" class="form-label">Fin <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('date_fin') is-invalid @enderror" id="date_fin" name="date_fin_at" value="{{ old('date_fin_at', $dateValue($event->date_fin, $event->heure_fin)) }}" required>
                                @error('date_fin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="ville" class="form-label">Ville</label>
                                <select class="form-select @error('ville') is-invalid @enderror" id="ville" name="ville">
                                    <option value="">Sélectionner une ville</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->nom }}" data-country="{{ optional($city->country)->nom }}" @selected(old('ville', $event->ville) === $city->nom)>{{ $city->nom }}{{ $city->country ? ' - ' . $city->country->nom : '' }}</option>
                                    @endforeach
                                    @if(old('ville', $event->ville) && !$cities->contains('nom', old('ville', $event->ville)))
                                        <option value="{{ old('ville', $event->ville) }}" selected>{{ old('ville', $event->ville) }}</option>
                                    @endif
                                </select>
                                @error('ville')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="pays" class="form-label">Pays</label>
                                <select class="form-select @error('pays') is-invalid @enderror" id="pays" name="pays">
                                    <option value="">Sélectionner un pays</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->nom }}" @selected(old('pays', $event->pays) === $country->nom)>
                                            {{ $country->flag ? $country->flag . ' ' : '' }}{{ $country->nom }}@if($country->indicatif || $country->currency) ({{ trim(($country->indicatif ?: '') . ' ' . ($country->currency ?: '')) }})@endif
                                        </option>
                                    @endforeach
                                    @if(old('pays', $event->pays) && !$countries->contains('nom', old('pays', $event->pays)))
                                        <option value="{{ old('pays', $event->pays) }}" selected>{{ old('pays', $event->pays) }}</option>
                                    @endif
                                </select>
                                @error('pays')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 position-relative">
                                <label for="lieu" class="form-label">Lieu</label>
                                <input type="text" class="form-control @error('lieu') is-invalid @enderror" id="lieu" name="lieu" value="{{ old('lieu', $event->lieu) }}" placeholder="Nom de la salle, de l’hôtel ou adresse" autocomplete="off" aria-autocomplete="list" aria-controls="venueSuggestions">
                                <div id="venueSuggestions" class="list-group position-absolute start-0 end-0 d-none" style="z-index: 1050; max-height: 280px; overflow-y: auto;" role="listbox"></div>
                                @error('lieu')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="lien_google_map" class="form-label">Lien Google Maps</label>
                                <input type="url" class="form-control @error('lien_google_map') is-invalid @enderror" id="lien_google_map" name="lien_google_map" value="{{ old('lien_google_map', $event->lien_google_map) }}" placeholder="Généré automatiquement à partir du lieu" readonly>
                                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $event->latitude) }}">
                                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $event->longitude) }}">
                                @error('lien_google_map')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="form-text" id="mapHelp">Modifiez le lieu pour rechercher de nouvelles coordonnées.</small>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ui-card edit-section" id="contacts">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['contacts']['icon'] }}"></i>Contacts</h2></div>
                    <div class="ui-card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="organisateur" class="form-label">Organisateur</label>
                                <input type="text" class="form-control @error('organisateur') is-invalid @enderror" id="organisateur" name="organisateur" value="{{ old('organisateur', $event->organisateur ?: auth()->user()->organization?->name) }}">
                                @error('organisateur')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email_contact" class="form-label">Email de contact</label>
                                <input type="email" class="form-control @error('email_contact') is-invalid @enderror" id="email_contact" name="email_contact" value="{{ old('email_contact', $event->email_contact) }}">
                                @error('email_contact')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', $event->telephone) }}">
                                @error('telephone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ui-card edit-section" id="pricing">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['pricing']['icon'] }}"></i>Tarification</h2></div>
                    <div class="ui-card-body pt-0">
                        <div class="form-group">
                            <span class="form-label">Type de tarification <span class="text-danger">*</span></span>
                            <div class="choice-grid">
                                @foreach($typeTarifications as $type)
                                    @php $isPaidType = \Illuminate\Support\Str::lower(trim($type->libelle)) === 'payant'; @endphp
                                    <label @class(['choice', 'is-invalid' => $errors->has('type_tarification_id')])>
                                        <input type="radio" name="type_tarification_id" value="{{ $type->id }}" data-label="{{ \Illuminate\Support\Str::lower(trim($type->libelle)) }}" @checked(old('type_tarification_id', $event->type_tarification_id) == $type->id) required>
                                        <span class="choice-body"><i class="bi {{ $isPaidType ? 'bi-ticket-perforated' : 'bi-gift' }}"></i><strong>{{ $type->libelle }}</strong><small>{{ $isPaidType ? 'Les invités règlent un droit d’entrée.' : 'Entrée libre pour vos invités.' }}</small></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('type_tarification_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 paid-field">
                                <label for="prix" class="form-label">Prix</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('prix') is-invalid @enderror" id="prix" name="prix" value="{{ old('prix', $event->prix) }}">
                                @error('prix')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 paid-field">
                                <label for="devise_id" class="form-label">Devise</label>
                                <select class="form-select @error('devise_id') is-invalid @enderror" id="devise_id" name="devise_id">
                                    <option value="">Sélectionner</option>
                                    @foreach($devises as $devise)
                                        <option value="{{ $devise->id }}" @selected(old('devise_id', $event->devise_id) == $devise->id)>{{ $devise->libelle }}</option>
                                    @endforeach
                                </select>
                                @error('devise_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ui-card edit-section" id="access">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['access']['icon'] }}"></i>Statut et accès</h2></div>
                    <div class="ui-card-body pt-0">
                        <div class="form-group">
                            <span class="form-label">Statut <span class="text-danger">*</span></span>
                            <div class="choice-grid">
                                <label class="choice">
                                    <input type="radio" name="statut" value="brouillon" @checked(old('statut', $event->statut) === 'brouillon') required>
                                    <span class="choice-body"><i class="bi bi-pencil-square"></i><strong>Brouillon</strong><small>En préparation, visible uniquement par votre équipe.</small></span>
                                </label>
                                <label class="choice">
                                    <input type="radio" name="statut" value="publie" @checked(old('statut', $event->statut) === 'publie') required>
                                    <span class="choice-body"><i class="bi bi-broadcast"></i><strong>Publié</strong><small>L’événement est actif et prêt à accueillir des invités.</small></span>
                                </label>
                            </div>
                            @if(!in_array(old('statut', $event->statut), ['brouillon', 'publie'], true))
                                <div class="form-text text-warning-emphasis">Statut actuel : <strong>{{ ucfirst($event->statut) }}</strong>. Choisissez « Brouillon » ou « Publié » pour enregistrer.</div>
                            @endif
                            @error('statut')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <span class="form-label">Visibilité <span class="text-danger">*</span></span>
                            <div class="choice-grid">
                                @foreach($visibilites as $visibilite)
                                    <label @class(['choice', 'is-invalid' => $errors->has('visibilite_id')])>
                                        <input type="radio" name="visibilite_id" value="{{ $visibilite->id }}" @checked(old('visibilite_id', $event->visibilite_id) == $visibilite->id) required>
                                        <span class="choice-body"><i class="bi {{ $visibilityIcon($visibilite->libelle) }}"></i><strong>{{ $visibilite->libelle }}</strong></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('visibilite_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="capacite_maximale" class="form-label">Capacité maximale</label>
                                <input type="number" min="1" class="form-control @error('capacite_maximale') is-invalid @enderror" id="capacite_maximale" name="capacite_maximale" value="{{ old('capacite_maximale', $event->capacite_maximale) }}" placeholder="Illimitée si vide">
                                @error('capacite_maximale')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_limite_inscription" class="form-label">Date limite d'inscription</label>
                                <input type="date" class="form-control @error('date_limite_inscription') is-invalid @enderror" id="date_limite_inscription" name="date_limite_inscription" value="{{ old('date_limite_inscription', $event->date_limite_inscription?->format('Y-m-d')) }}">
                                @error('date_limite_inscription')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="switch-row">
                                    <label for="inscription_requise" class="form-label mb-0">Inscription requise</label>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="inscription_requise" name="inscription_requise" value="1" @checked(old('inscription_requise', $event->inscription_requise))>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ui-card edit-section" id="experience">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['experience']['icon'] }}"></i>Expérience invité</h2></div>
                    <div class="ui-card-body pt-0">
                        @include('events.partials.invitation-experience-fields', ['event' => $event])
                        @if(!\App\Support\InvitationExperience::platform()['enabled'])<p class="text-muted small mb-0">L’animation d’ouverture est désactivée par la plateforme.</p>@endif
                    </div>
                </section>

                <section class="ui-card edit-section" id="meta">
                    <div class="ui-card-head"><h2><i class="bi {{ $sections['meta']['icon'] }}"></i>Notes internes</h2></div>
                    <div class="ui-card-body pt-0">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="tags" class="form-label">Mots-clés</label>
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags', $event->tags) }}" placeholder="Ex. conférence, technologie, innovation">
                                @error('tags')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="form-text">Séparez les mots-clés par des virgules.</small>
                            </div>
                            <div class="col-12">
                                <label for="notes_internes" class="form-label">Notes internes</label>
                                <textarea class="form-control @error('notes_internes') is-invalid @enderror" id="notes_internes" name="notes_internes" rows="3">{{ old('notes_internes', $event->notes_internes) }}</textarea>
                                @error('notes_internes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="form-text">Visibles uniquement par les administrateurs.</small>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="edit-aside">
                <nav class="ui-card toc" aria-label="Sections du formulaire">
                    @foreach($sections as $id => $section)
                        <a href="#{{ $id }}" data-toc="{{ $id }}" @class(['is-active' => $loop->first])><i class="bi {{ $section['icon'] }}"></i>{{ $section['label'] }}<span class="err d-none"></span></a>
                    @endforeach
                </nav>
                <div class="ui-card save-card">
                    <div class="save-state" data-save-state>Aucune modification</div>
                    <button type="submit" class="ui-btn ui-btn-primary"><i class="bi bi-check2-circle"></i>Enregistrer</button>
                    <a href="{{ route('events.show', $event) }}" class="ui-btn ui-btn-soft mt-2">Annuler</a>
                </div>
            </aside>
        </div>

        <div class="mobile-save">
            <a href="{{ route('events.show', $event) }}" class="ui-btn ui-btn-soft">Annuler</a>
            <button type="submit" class="ui-btn ui-btn-primary"><i class="bi bi-check2-circle"></i>Enregistrer</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('editForm');
    const citySelect = document.getElementById('ville');
    const countrySelect = document.getElementById('pays');
    const venueInput = document.getElementById('lieu');
    const mapLinkInput = document.getElementById('lien_google_map');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const mapHelp = document.getElementById('mapHelp');
    const venueSuggestions = document.getElementById('venueSuggestions');
    const geocodeUrl = @json(route('events.geocode'));
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
            const response = await fetch(`${geocodeUrl}?q=${encodeURIComponent(query)}`, { headers: { 'Accept': 'application/json' }, signal: geocodeController.signal });
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

    function updatePricingFields() {
        const isPaid = document.querySelector('input[name="type_tarification_id"]:checked')?.dataset.label === 'payant';
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
    countrySelect?.addEventListener('change', () => { if (venueInput.value.trim()) scheduleVenueGeocoding(); });
    venueInput?.addEventListener('input', scheduleVenueGeocoding);
    document.addEventListener('click', event => {
        if (!venueInput.contains(event.target) && !venueSuggestions.contains(event.target)) hideVenueSuggestions();
    });
    document.querySelectorAll('input[name="type_tarification_id"]').forEach(input => input.addEventListener('change', updatePricingFields));

    // Dropzones: file name and image thumbnail.
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
            icon.innerHTML = file && zone.dataset.dropzone === 'image' ? `<img src="${URL.createObjectURL(file)}" alt="">` : initialIcon;
        });
    });

    // Unsaved-changes indicator and guard.
    const saveState = document.querySelector('[data-save-state]');
    let dirty = false;
    form.addEventListener('input', () => {
        dirty = true;
        if (saveState) { saveState.textContent = 'Modifications non enregistrées'; saveState.classList.add('is-dirty'); }
    });
    window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
    form.addEventListener('submit', () => { dirty = false; });

    // Table of contents: highlight the section in view, flag sections with errors.
    const tocLinks = [...document.querySelectorAll('[data-toc]')];
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            tocLinks.forEach(link => link.classList.toggle('is-active', link.dataset.toc === entry.target.id));
        });
    }, { rootMargin: '-40% 0px -55% 0px' });
    document.querySelectorAll('.edit-section').forEach(section => {
        observer.observe(section);
        if (section.querySelector('.is-invalid, .invalid-feedback')) {
            document.querySelector(`[data-toc="${section.id}"] .err`)?.classList.remove('d-none');
        }
    });
    const firstError = document.querySelector('.edit-section .is-invalid, .edit-section .invalid-feedback');
    if (firstError) setTimeout(() => firstError.closest('.edit-section').scrollIntoView({ behavior: 'smooth' }), 200);

    updatePricingFields();
</script>
@endpush
