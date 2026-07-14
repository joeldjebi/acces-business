<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventOtpVerification extends Model
{
    protected $fillable = [
        'event_id',
        'email',
        'otp_code',
        'is_verified',
        'expires_at',
        'verified_at',
        'ip_address',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Génère un nouveau code OTP pour un événement et un email
     */
    public static function generateOtp(int $eventId, string $email, ?string $ipAddress = null): self
    {
        // Invalider les anciens OTP non vérifiés pour cet email et cet événement
        self::where('event_id', $eventId)
            ->where('email', $email)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->delete(); // Supprimer les anciens OTP non vérifiés

        // Générer un nouveau code à 6 chiffres
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'event_id' => $eventId,
            'email' => $email,
            'otp_code' => $otpCode,
            'is_verified' => false,
            'expires_at' => now()->addMinutes(15),
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }

    /**
     * Vérifie le code OTP
     */
    public function verify(string $code): bool
    {
        if ($this->is_verified) {
            return false; // Déjà vérifié
        }

        if ($this->expires_at < now()) {
            return false; // Expiré
        }

        if ($this->otp_code !== $code) {
            return false; // Code incorrect
        }

        // Marquer comme vérifié
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Vérifie si le code est valide (non vérifié et non expiré)
     */
    public function isValid(): bool
    {
        return !$this->is_verified && $this->expires_at > now();
    }

    /**
     * Relation avec l'événement
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
