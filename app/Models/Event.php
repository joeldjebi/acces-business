<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'titre',
        'description',
        'slug',
        'image',
        'category_id',
        'user_id',
        'date_debut',
        'heure_debut',
        'date_fin',
        'heure_fin',
        'fuseau_horaire',
        'lieu',
        'adresse_complete',
        'ville',
        'code_postal',
        'pays',
        'latitude',
        'longitude',
        'lien_google_map',
        'organisateur',
        'email_contact',
        'telephone',
        'site_web',
        'statut',
        'visibilite_id',
        'date_publication',
        'capacite_maximale',
        'inscription_requise',
        'date_limite_inscription',
        'type_tarification_id',
        'prix',
        'devise_id',
        'tags',
        'vues',
        'notes_internes',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_limite_inscription' => 'date',
        'date_publication' => 'datetime',
        'inscription_requise' => 'boolean',
        'prix' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'capacite_maximale' => 'integer',
        'vues' => 'integer',
    ];

    /**
     * Boot du modèle pour générer automatiquement le slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->titre);
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('titre') && empty($event->slug)) {
                $event->slug = Str::slug($event->titre);
            }
        });
    }

    /**
     * Relation avec la catégorie
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation avec l'utilisateur créateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la visibilité
     */
    public function visibilite(): BelongsTo
    {
        return $this->belongsTo(Visibilite::class);
    }

    /**
     * Relation avec le type de tarification
     */
    public function typeTarification(): BelongsTo
    {
        return $this->belongsTo(TypeTarification::class, 'type_tarification_id');
    }

    /**
     * Relation avec la devise
     */
    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class);
    }

    /**
     * Scope pour les événements publiés
     */
    public function scopePublished($query)
    {
        return $query->where('statut', 'publie');
    }

    /**
     * Scope pour les événements à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date_debut', '>=', now());
    }

    /**
     * Scope pour les événements passés
     */
    public function scopePast($query)
    {
        return $query->where('date_fin', '<', now());
    }

    /**
     * Vérifie si l'événement est gratuit
     */
    public function isFree(): bool
    {
        return $this->typeTarification && $this->typeTarification->libelle === 'Gratuit';
    }

    /**
     * Vérifie si l'événement est publié
     */
    public function isPublished(): bool
    {
        return $this->statut === 'publie';
    }

    /**
     * Incrémente le compteur de vues
     */
    public function incrementViews(): void
    {
        $this->increment('vues');
    }

    /**
     * Relation avec les inscriptions
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Relation avec les vérifications OTP
     */
    public function otpVerifications(): HasMany
    {
        return $this->hasMany(EventOtpVerification::class);
    }

    /**
     * Relation avec les liens d'accès
     */
    public function accessLinks(): HasMany
    {
        return $this->hasMany(EventAccessLink::class);
    }

    /**
     * Vérifie si l'événement est public
     */
    public function isPublic(): bool
    {
        return $this->visibilite && $this->visibilite->libelle === 'Public';
    }

    /**
     * Vérifie si l'événement est privé
     */
    public function isPrivate(): bool
    {
        return $this->visibilite && $this->visibilite->libelle === 'Privé';
    }

    /**
     * Vérifie si l'événement est sur invitation
     */
    public function isInvitationOnly(): bool
    {
        return $this->visibilite && $this->visibilite->libelle === 'Sur invitation';
    }
}
