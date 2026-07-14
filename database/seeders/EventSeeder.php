<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Devise;
use App\Models\Event;
use App\Models\TypeTarification;
use App\Models\User;
use App\Models\Visibilite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les données de référence
        $category = Category::first();
        $visibilitePublic = Visibilite::where('libelle', 'Public')->first();
        $visibilitePrive = Visibilite::where('libelle', 'Privé')->first();
        $visibiliteInvitation = Visibilite::where('libelle', 'Sur invitation')->first();
        $typeGratuit = TypeTarification::where('libelle', 'Gratuit')->first();
        $typePayant = TypeTarification::where('libelle', 'Payant')->first();
        $deviseEUR = Devise::where('libelle', 'EUR')->first();
        $user = User::first();
        
        // Créer un utilisateur si aucun n'existe
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => \Hash::make('password'),
                'role' => 'super_admin',
            ]);
            $this->command->info('Utilisateur de test créé : admin@test.com / password');
        }

        if (!$category || !$visibilitePublic || !$visibilitePrive || !$visibiliteInvitation || !$typeGratuit || !$typePayant || !$deviseEUR) {
            $this->command->error('Veuillez d\'abord exécuter ReferenceDataSeeder !');
            return;
        }

        // Événement PUBLIC
        Event::firstOrCreate(
            ['slug' => 'conference-innovation-2024'],
            [
                'titre' => 'Conférence Innovation 2024',
                'description' => 'Une conférence exceptionnelle sur les dernières innovations technologiques. Venez découvrir les tendances du futur et rencontrer des experts du secteur.',
                'slug' => 'conference-innovation-2024',
                'category_id' => $category->id,
                'user_id' => $user->id,
                'date_debut' => now()->addDays(30),
                'heure_debut' => '09:00',
                'date_fin' => now()->addDays(30),
                'heure_fin' => '18:00',
                'fuseau_horaire' => 'Europe/Paris',
                'lieu' => 'Palais des Congrès',
                'adresse_complete' => '2 Place de la Porte Maillot',
                'ville' => 'Paris',
                'code_postal' => '75017',
                'pays' => 'France',
                'latitude' => 48.8842,
                'longitude' => 2.2833,
                'lien_google_map' => 'https://maps.google.com/?q=Palais+des+Congrès+Paris',
                'organisateur' => 'Tech Events Paris',
                'email_contact' => 'contact@techevents.fr',
                'telephone' => '+33 1 23 45 67 89',
                'site_web' => 'https://www.techevents.fr',
                'statut' => 'publie',
                'visibilite_id' => $visibilitePublic->id,
                'date_publication' => now(),
                'capacite_maximale' => 500,
                'inscription_requise' => true,
                'date_limite_inscription' => now()->addDays(25),
                'type_tarification_id' => $typeGratuit->id,
                'prix' => null,
                'devise_id' => null,
                'tags' => 'innovation, technologie, conférence, startup',
                'vues' => 0,
                'notes_internes' => 'Événement public - Inscription ouverte à tous',
            ]
        );

        // Événement PRIVÉ
        Event::firstOrCreate(
            ['slug' => 'seminaire-entreprise-prive'],
            [
                'titre' => 'Séminaire Entreprise - Leadership & Management',
                'description' => 'Séminaire exclusif réservé aux membres de l\'entreprise. Formation approfondie sur le leadership et le management d\'équipe.',
                'slug' => 'seminaire-entreprise-prive',
                'category_id' => $category->id,
                'user_id' => $user->id,
                'date_debut' => now()->addDays(45),
                'heure_debut' => '08:30',
                'date_fin' => now()->addDays(46),
                'heure_fin' => '17:00',
                'fuseau_horaire' => 'Europe/Paris',
                'lieu' => 'Centre de Formation',
                'adresse_complete' => '15 Avenue des Champs-Élysées',
                'ville' => 'Paris',
                'code_postal' => '75008',
                'pays' => 'France',
                'latitude' => 48.8698,
                'longitude' => 2.3081,
                'lien_google_map' => 'https://maps.google.com/?q=Centre+de+Formation+Paris',
                'organisateur' => 'RH Formation',
                'email_contact' => 'formation@rh-company.fr',
                'telephone' => '+33 1 98 76 54 32',
                'site_web' => 'https://www.rh-company.fr',
                'statut' => 'publie',
                'visibilite_id' => $visibilitePrive->id,
                'date_publication' => now(),
                'capacite_maximale' => 50,
                'inscription_requise' => true,
                'date_limite_inscription' => now()->addDays(40),
                'type_tarification_id' => $typePayant->id,
                'prix' => 299.00,
                'devise_id' => $deviseEUR->id,
                'tags' => 'formation, leadership, management, entreprise',
                'vues' => 0,
                'notes_internes' => 'Événement privé - Accès réservé aux employés authentifiés',
            ]
        );

        // Événement SUR INVITATION
        Event::firstOrCreate(
            ['slug' => 'soiree-vip-exclusive'],
            [
                'titre' => 'Soirée VIP Exclusive - Networking Premium',
                'description' => 'Soirée de networking exclusive sur invitation uniquement. Rencontrez des dirigeants et investisseurs dans un cadre privilégié.',
                'slug' => 'soiree-vip-exclusive',
                'category_id' => $category->id,
                'user_id' => $user->id,
                'date_debut' => now()->addDays(60),
                'heure_debut' => '19:00',
                'date_fin' => now()->addDays(60),
                'heure_fin' => '23:00',
                'fuseau_horaire' => 'Europe/Paris',
                'lieu' => 'Hôtel Ritz Paris',
                'adresse_complete' => '15 Place Vendôme',
                'ville' => 'Paris',
                'code_postal' => '75001',
                'pays' => 'France',
                'latitude' => 48.8676,
                'longitude' => 2.3284,
                'lien_google_map' => 'https://maps.google.com/?q=Ritz+Paris',
                'organisateur' => 'Elite Networking',
                'email_contact' => 'vip@elitenetworking.fr',
                'telephone' => '+33 1 42 60 38 30',
                'site_web' => 'https://www.elitenetworking.fr',
                'statut' => 'publie',
                'visibilite_id' => $visibiliteInvitation->id,
                'date_publication' => now(),
                'capacite_maximale' => 100,
                'inscription_requise' => true,
                'date_limite_inscription' => now()->addDays(55),
                'type_tarification_id' => $typePayant->id,
                'prix' => 500.00,
                'devise_id' => $deviseEUR->id,
                'tags' => 'vip, networking, exclusif, invitation',
                'vues' => 0,
                'notes_internes' => 'Événement sur invitation - Accès strictement contrôlé',
            ]
        );

        $this->command->info('3 événements créés avec succès :');
        $this->command->info('- Événement PUBLIC : Conférence Innovation 2024');
        $this->command->info('- Événement PRIVÉ : Séminaire Entreprise');
        $this->command->info('- Événement SUR INVITATION : Soirée VIP Exclusive');
    }
}
