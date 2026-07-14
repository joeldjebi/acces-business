@extends('layouts.app')

@section('title', 'Modifier un Événement')

@push('styles')
<style>
    .form-section {
        background: white;
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .form-section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .form-control, .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="bi bi-pencil-square text-primary"></i>
                Modifier l'Événement
            </h3>
            
            <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Informations de base -->
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
                                   value="{{ old('titre', $event->titre) }}" 
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
                                    <option value="{{ $category->id }}" {{ old('category_id', $event->category_id) == $category->id ? 'selected' : '' }}>
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
                              rows="4">{{ old('description', $event->description) }}</textarea>
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
                    @if($event->image)
                        <div class="mt-2">
                            <img src="{{ Storage::url($event->image) }}" alt="Image actuelle" style="max-width: 200px; border-radius: 8px;">
                        </div>
                    @endif
                </div>
                
                <!-- Dates et horaires -->
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
                                   value="{{ old('date_debut', $event->date_debut->format('Y-m-d')) }}" 
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
                                   value="{{ old('heure_debut', $event->heure_debut) }}" 
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
                                   value="{{ old('date_fin', $event->date_fin->format('Y-m-d')) }}" 
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
                                   value="{{ old('heure_fin', $event->heure_fin) }}" 
                                   required>
                            @error('heure_fin')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Localisation -->
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
                                   value="{{ old('lieu', $event->lieu) }}">
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
                                    <option value="{{ $city->nom }}" data-country="{{ optional($city->country)->nom }}" {{ old('ville', $event->ville) === $city->nom ? 'selected' : '' }}>
                                        {{ $city->nom }}{{ $city->country ? ' - ' . $city->country->nom : '' }}
                                    </option>
                                @endforeach
                                @if(old('ville', $event->ville) && !$cities->contains('nom', old('ville', $event->ville)))
                                    <option value="{{ old('ville', $event->ville) }}" selected>{{ old('ville', $event->ville) }}</option>
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
                                   value="{{ old('code_postal', $event->code_postal) }}">
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
                                    <option value="{{ $country->nom }}" {{ old('pays', $event->pays) === $country->nom ? 'selected' : '' }}>
                                        {{ $country->flag ? $country->flag . ' ' : '' }}{{ $country->nom }}
                                        @if($country->indicatif || $country->currency)
                                            ({{ trim(($country->indicatif ?: '') . ' ' . ($country->currency ?: '')) }})
                                        @endif
                                    </option>
                                @endforeach
                                @if(old('pays', $event->pays) && !$countries->contains('nom', old('pays', $event->pays)))
                                    <option value="{{ old('pays', $event->pays) }}" selected>{{ old('pays', $event->pays) }}</option>
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
                              rows="2">{{ old('adresse_complete', $event->adresse_complete) }}</textarea>
                    @error('adresse_complete')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Organisation -->
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
                                   value="{{ old('organisateur', $event->organisateur) }}">
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
                                   value="{{ old('email_contact', $event->email_contact) }}">
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
                                   value="{{ old('telephone', $event->telephone) }}">
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
                                   value="{{ old('site_web', $event->site_web) }}">
                            @error('site_web')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Tarification -->
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
                                    <option value="{{ $type->id }}" {{ old('type_tarification_id', $event->type_tarification_id) == $type->id ? 'selected' : '' }}>
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
                                   value="{{ old('prix', $event->prix) }}">
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
                                    <option value="{{ $devise->id }}" {{ old('devise_id', $event->devise_id) == $devise->id ? 'selected' : '' }}>
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
                
                <!-- Statut et visibilité -->
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
                                <option value="brouillon" {{ old('statut', $event->statut) == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                <option value="publie" {{ old('statut', $event->statut) == 'publie' ? 'selected' : '' }}>Publié</option>
                                <option value="annule" {{ old('statut', $event->statut) == 'annule' ? 'selected' : '' }}>Annulé</option>
                                <option value="termine" {{ old('statut', $event->statut) == 'termine' ? 'selected' : '' }}>Terminé</option>
                                <option value="reporte" {{ old('statut', $event->statut) == 'reporte' ? 'selected' : '' }}>Reporté</option>
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
                                    <option value="{{ $visibilite->id }}" {{ old('visibilite_id', $event->visibilite_id) == $visibilite->id ? 'selected' : '' }}>
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
                
                <!-- Capacité -->
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
                                   value="{{ old('capacite_maximale', $event->capacite_maximale) }}">
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
                                       {{ old('inscription_requise', $event->inscription_requise) ? 'checked' : '' }}>
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
                           value="{{ old('date_limite_inscription', $event->date_limite_inscription?->format('Y-m-d')) }}">
                    @error('date_limite_inscription')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Métadonnées -->
                <div class="form-group">
                    <label for="tags" class="form-label">
                        <i class="bi bi-tags"></i>
                        Tags/Mots-clés
                    </label>
                    <input type="text" 
                           class="form-control @error('tags') is-invalid @enderror" 
                           id="tags" 
                           name="tags" 
                           value="{{ old('tags', $event->tags) }}"
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
                              rows="3">{{ old('notes_internes', $event->notes_internes) }}</textarea>
                    @error('notes_internes')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Ces notes ne sont visibles que par les administrateurs</small>
                </div>
                
                <!-- Actions -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('events.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const citySelect = document.getElementById('ville');
    const countrySelect = document.getElementById('pays');

    citySelect?.addEventListener('change', function() {
        const selectedCountry = this.selectedOptions[0]?.dataset.country;

        if (selectedCountry && countrySelect && !countrySelect.value) {
            countrySelect.value = selectedCountry;
        }
    });
</script>
@endpush
