<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'event_id',
        'email',
        'nom',
        'prenom',
        'telephone',
        'entreprise',
        'fonction',
        'statut_reponse',
        'token_unique',
        'qr_code_path',
        'carte_envoyee',
        'date_inscription',
        'date_reponse',
        'date_validation_otp',
        'user_id',
        'representant_nom',
        'representant_prenoms',
        'representant_fonction',
        'representant_contact',
        'representant_email',
        'representant_statut',
        'representant_confirme_le',
        'representant_token',
        'representant_mail_envoye',
        'representant_mail_envoye_le',
        'representant_mail_erreur',
        'representant_confirmation_ip',
        'representant_confirmation_user_agent',
        'representant_carte_envoyee',
        'representant_carte_envoyee_le',
        'representant_carte_erreur',
        'represente_notification_envoyee',
        'represente_notification_envoyee_le',
        'represente_notification_erreur',
        'checked_in_at',
        'checked_in_by',
        'checkin_ip',
        'checkin_user_agent',
        'checkin_count',
        'last_reminder_sent_at',
        'reminder_count',
        'thank_you_sms_sent_at',
        'thank_you_sms_status',
        'thank_you_sms_error',
    ];

    protected $casts = [
        'carte_envoyee' => 'boolean',
        'date_inscription' => 'datetime',
        'date_reponse' => 'datetime',
        'date_validation_otp' => 'datetime',
        'representant_confirme_le' => 'datetime',
        'representant_mail_envoye' => 'boolean',
        'representant_mail_envoye_le' => 'datetime',
        'representant_carte_envoyee' => 'boolean',
        'representant_carte_envoyee_le' => 'datetime',
        'represente_notification_envoyee' => 'boolean',
        'represente_notification_envoyee_le' => 'datetime',
        'checked_in_at' => 'datetime',
        'checkin_count' => 'integer',
        'last_reminder_sent_at' => 'datetime',
        'reminder_count' => 'integer',
        'thank_you_sms_sent_at' => 'datetime',
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


    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(EventRegistrationActivity::class);
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
     * Scope pour les personnes représentées
     */
    public function scopeRepresentes($query)
    {
        return $query->where('statut_reponse', 'represente');
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
     * Vérifie si l'invité se fait représenter.
     */
    public function isRepresented(): bool
    {
        return $this->statut_reponse === 'represente';
    }


    public function hasCheckedIn(): bool
    {
        return !is_null($this->checked_in_at);
    }

    public function checkInDisplayName(): string
    {
        if ($this->isRepresented()) {
            return trim(($this->representant_prenoms ?? '') . ' ' . ($this->representant_nom ?? '')) ?: ($this->representant_email ?: $this->nom_complet);
        }

        return $this->nom_complet ?: $this->email;
    }

    public function checkInContact(): ?string
    {
        return $this->isRepresented() ? $this->representant_contact : $this->telephone;
    }

    /**
     * Génère le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }
}
