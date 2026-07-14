<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'email',
        'nom',
        'prenom',
        'telephone',
        'entreprise',
        'statut_reponse',
        'token_unique',
        'qr_code_path',
        'carte_envoyee',
        'date_inscription',
        'date_reponse',
        'date_validation_otp',
        'user_id',
    ];

    protected $casts = [
        'carte_envoyee' => 'boolean',
        'date_inscription' => 'datetime',
        'date_reponse' => 'datetime',
        'date_validation_otp' => 'datetime',
    ];

    /**
     * Boot du modèle pour générer automatiquement le token
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->token_unique)) {
                $registration->token_unique = Str::random(32);
            }
            if (empty($registration->date_inscription)) {
                $registration->date_inscription = now();
            }
        });
    }

    /**
     * Relation avec l'événement
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relation avec l'utilisateur (si connecté)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les présents
     */
    public function scopePresents($query)
    {
        return $query->where('statut_reponse', 'present');
    }

    /**
     * Scope pour les peut-être
     */
    public function scopePeutEtre($query)
    {
        return $query->where('statut_reponse', 'peut_etre');
    }

    /**
     * Scope pour les absents
     */
    public function scopeAbsents($query)
    {
        return $query->where('statut_reponse', 'absent');
    }

    /**
     * Scope pour les en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut_reponse', 'en_attente');
    }

    /**
     * Vérifie si la carte a été envoyée
     */
    public function hasCardSent(): bool
    {
        return $this->carte_envoyee;
    }

    /**
     * Vérifie si l'inscription est confirmée (présent ou peut-être)
     */
    public function isConfirmed(): bool
    {
        return in_array($this->statut_reponse, ['present', 'peut_etre']);
    }

    /**
     * Génère le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }
}
