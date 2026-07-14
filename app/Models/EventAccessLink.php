<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventAccessLink extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'event_id',
        'email_destinataire',
        'token_unique',
        'envoye_par',
        'envoye_le',
        'utilise_le',
        'est_utilise',
    ];

    protected $casts = [
        'est_utilise' => 'boolean',
        'envoye_le' => 'datetime',
        'utilise_le' => 'datetime',
    ];

    /**
     * Boot du modèle pour générer automatiquement le token
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($link) {
            if (empty($link->token_unique)) {
                $link->token_unique = Str::random(32);
            }
            if (empty($link->envoye_le)) {
                $link->envoye_le = now();
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
     * Relation avec l'utilisateur qui a envoyé le lien
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'envoye_par');
    }

    /**
     * Marque le lien comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update([
            'est_utilise' => true,
            'utilise_le' => now(),
        ]);
    }

    /**
     * Vérifie si le lien est valide (non utilisé)
     */
    public function isValid(): bool
    {
        return !$this->est_utilise;
    }

    /**
     * Génère l'URL complète du lien d'accès
     */
    public function getAccessUrlAttribute(): string
    {
        return route('events.access', [
            'event' => $this->event_id,
            'token' => $this->token_unique,
        ]);
    }
}
