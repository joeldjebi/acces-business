<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class LandingPage extends Model
{
    protected $fillable = ['name', 'is_active', 'settings'];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public static function current(): self
    {
        if (!Schema::hasTable('landing_pages')) {
            return new static(['name' => 'default', 'is_active' => true, 'settings' => static::defaults()]);
        }

        return static::query()->where('name', 'default')->first()
            ?? new static(['name' => 'default', 'is_active' => true, 'settings' => static::defaults()]);
    }

    public function mergedSettings(): array
    {
        return array_replace_recursive(static::defaults(), $this->settings ?? []);
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->mergedSettings(), $key, $default);
    }

    public function imageUrl(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
            return $filename;
        }

        return Route::has('media.landing.image') ? route('media.landing.image', $filename) : asset('images/landing/' . $filename);
    }

    public static function defaults(): array
    {
        return [
            'brand' => [
                'name' => 'Accès Business',
                'logo' => null,
                'logo_icon' => 'bi-shield-check',
                'meta_title' => 'Accès Business - Invitations événementielles premium',
                'meta_description' => 'Créez vos événements, envoyez des invitations QR, gérez les réponses, représentants, wallets et check-in live.',
            ],
            'navigation' => [
                ['label' => 'Fonctionnalités', 'anchor' => 'features', 'enabled' => true],
                ['label' => 'Check-in', 'anchor' => 'checkin', 'enabled' => true],
                ['label' => 'Wallet', 'anchor' => 'wallet', 'enabled' => true],
                ['label' => 'Tarifs', 'anchor' => 'pricing', 'enabled' => true],
                ['label' => 'FAQ', 'anchor' => 'faq', 'enabled' => true],
            ],
            'hero' => [
                'enabled' => true,
                'eyebrow' => 'Plateforme invitations & accès',
                'title' => 'Gérez vos invitations événementielles avec précision.',
                'body' => 'Créez vos événements, envoyez des invitations personnalisées, collectez les réponses, gérez les représentants et validez les entrées par QR code depuis une console premium.',
                'primary_label' => 'Créer mon espace',
                'secondary_label' => 'Accéder à mon espace',
                'image' => 'event-checkin-hero.png',
                'image_alt' => 'Check-in QR à l’entrée d’un événement premium',
                'floating_title' => 'QR validé',
                'floating_text' => 'Entrée autorisée',
            ],
            'trust_items' => [
                ['icon' => 'bi-qr-code', 'label' => 'QR unique', 'enabled' => true],
                ['icon' => 'bi-phone', 'label' => 'Apple & Google Wallet', 'enabled' => true],
                ['icon' => 'bi-person-check', 'label' => 'Représentation tracée', 'enabled' => true],
            ],
            'hero_metrics' => [
                ['label' => 'Confirmés', 'value' => '184', 'enabled' => true],
                ['label' => 'Check-in', 'value' => '92', 'enabled' => true],
                ['label' => 'Représ.', 'value' => '17', 'enabled' => true],
            ],
            'features_section' => [
                'enabled' => true,
                'eyebrow' => 'Cycle complet',
                'title' => 'Tout le parcours d’invitation dans un seul outil.',
                'body' => 'Accès Business couvre la création de l’événement, la réponse de l’invité, les cartes, le check-in et l’analyse après événement.',
            ],
            'features' => [
                ['icon' => 'bi-calendar2-event', 'title' => 'Création événement', 'body' => 'Public, privé ou sur invitation avec capacité, tarification et localisation.', 'enabled' => true],
                ['icon' => 'bi-envelope-paper', 'title' => 'Invitations email', 'body' => 'Liens personnalisés, OTP et carte PDF avec QR code.', 'enabled' => true],
                ['icon' => 'bi-person-arms-up', 'title' => 'Représentation', 'body' => 'Un invité indisponible peut désigner un représentant avec trace complète.', 'enabled' => true],
                ['icon' => 'bi-qr-code-scan', 'title' => 'Check-in live', 'body' => 'Scan QR, anti double entrée, heure d’arrivée et opérateur.', 'enabled' => true],
                ['icon' => 'bi-wallet2', 'title' => 'Wallet mobile', 'body' => 'Ajout dans Apple Wallet et Google Wallet depuis le mail.', 'enabled' => true],
                ['icon' => 'bi-bell', 'title' => 'Relances automatiques', 'body' => 'Chaque organisation choisit son heure quotidienne de relance.', 'enabled' => true],
                ['icon' => 'bi-chat-dots', 'title' => 'SMS remerciement', 'body' => 'Messages post-événement selon les crédits SMS disponibles.', 'enabled' => true],
                ['icon' => 'bi-graph-up-arrow', 'title' => 'Stats & exports', 'body' => 'Suivi des réponses, représentants, cartes, scans et présences.', 'enabled' => true],
            ],
            'representation' => [
                'enabled' => true,
                'eyebrow' => 'Représentation',
                'title' => 'Quand un invité ne peut pas venir, l’organisation garde le contrôle.',
                'body' => 'L’invité renseigne son représentant. Le représentant reçoit un lien, confirme sa présence, reçoit sa carte, et l’organisateur voit tout l’historique.',
                'items' => [
                    'Coordonnées du représentant: nom, fonction, contact, email.',
                    'Notification à l’invité représenté après confirmation.',
                    'Détail complet accessible dans le tableau des inscriptions.',
                ],
            ],
            'checkin' => [
                'enabled' => true,
                'eyebrow' => 'Check-in live',
                'title' => 'Une entrée fluide, contrôlée et mesurable.',
                'body' => 'Les agents scannent le QR code, voient immédiatement le profil invité et valident l’entrée. Les doubles scans sont détectés et l’organisateur suit les arrivées en temps réel.',
            ],
            'wallet' => [
                'enabled' => true,
                'eyebrow' => 'Wallet',
                'title' => 'Des cartes prêtes pour iPhone et Android.',
                'body' => 'Les invités peuvent ajouter leur carte à Apple Wallet ou Google Wallet depuis le mail. Le QR reste accessible sans chercher une pièce jointe.',
                'image' => 'wallet-invitation.png',
                'image_alt' => 'Carte d’invitation dans un wallet mobile',
                'caption_title' => 'Invitation toujours à portée de main',
                'caption_text' => 'QR, date, lieu et accès mobile.',
            ],
            'pricing_section' => [
                'enabled' => true,
                'eyebrow' => 'Plans',
                'title' => 'Une offre claire pour chaque volume.',
                'body' => 'Démarrez léger, puis augmentez vos quotas d’événements, invités, emails, SMS et utilisateurs selon votre activité.',
            ],
            'plans' => [
                ['name' => 'Starter', 'subtitle' => 'Pour les petites équipes.', 'price' => 'Essentiel', 'items' => ['Événements publics et privés', 'Cartes PDF QR', 'Branding léger'], 'featured' => false, 'enabled' => true],
                ['name' => 'Business', 'subtitle' => 'Pour agences et événements corporate.', 'price' => 'Premium', 'items' => ['Représentation complète', 'Check-in live', 'Relances et exports'], 'featured' => true, 'enabled' => true],
                ['name' => 'Enterprise', 'subtitle' => 'Pour institutions et grands comptes.', 'price' => 'Sur mesure', 'items' => ['Quotas avancés', 'Wallet & SMS', 'Support prioritaire'], 'featured' => false, 'enabled' => true],
            ],
            'security' => [
                'enabled' => true,
                'eyebrow' => 'Sécurité',
                'title' => 'Des accès maîtrisés, des traces lisibles.',
                'body' => 'OTP, QR unique, rôles utilisateurs, séparation par organisation et journal d’activité pour chaque invité.',
                'items' => [
                    ['icon' => 'bi-key', 'label' => 'OTP privé', 'enabled' => true],
                    ['icon' => 'bi-upc-scan', 'label' => 'QR unique', 'enabled' => true],
                    ['icon' => 'bi-people', 'label' => 'Rôles', 'enabled' => true],
                    ['icon' => 'bi-clock-history', 'label' => 'Historique', 'enabled' => true],
                ],
            ],
            'faq_section' => ['enabled' => true, 'eyebrow' => 'FAQ', 'title' => 'Questions fréquentes.'],
            'faqs' => [
                ['question' => 'Peut-on gérer des événements privés ?', 'answer' => 'Oui. Les invités passent par un lien sécurisé et une vérification OTP avant de répondre.', 'enabled' => true],
                ['question' => 'Un invité peut-il se faire représenter ?', 'answer' => 'Oui. Il désigne un représentant, qui confirme ensuite sa présence via son propre lien.', 'enabled' => true],
                ['question' => 'Le check-in évite-t-il les doubles entrées ?', 'answer' => 'Oui. Un QR déjà scanné affiche immédiatement une alerte avec l’heure du premier check-in.', 'enabled' => true],
                ['question' => 'Les cartes Wallet sont-elles possibles ?', 'answer' => 'Oui, Apple Wallet et Google Wallet sont prévus dans le parcours invitation.', 'enabled' => true],
            ],
            'cta' => [
                'enabled' => true,
                'title' => 'Prêt à professionnaliser vos invitations ?',
                'body' => 'Centralisez les confirmations, cartes, représentants, relances et check-in dans une expérience premium pour vos invités et vos équipes.',
                'primary_label' => 'Créer mon espace',
                'secondary_label' => 'Connexion',
            ],
            'footer' => [
                'brand' => 'Accès Business',
                'text' => 'Invitations, accès et présence événementielle.',
            ],
            'theme' => [
                'ink' => '#171713',
                'gold' => '#b98943',
                'background' => '#fbf8f1',
            ],
        ];
    }
}
