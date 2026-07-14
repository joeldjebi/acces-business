<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Devise;
use App\Models\TypeTarification;
use App\Models\Visibilite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catégories
        $categories = [
            ['libelle' => 'Conférence', 'statut' => 1],
            ['libelle' => 'Concert', 'statut' => 1],
        ];
        
        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['libelle' => $category['libelle']],
                $category
            );
        }
        
        // Devises
        $devises = [
            ['libelle' => 'EUR', 'statut' => 1],
            ['libelle' => 'USD', 'statut' => 1],
        ];
        
        foreach ($devises as $devise) {
            Devise::firstOrCreate(
                ['libelle' => $devise['libelle']],
                $devise
            );
        }
        
        // Types de tarification
        $typeTarifications = [
            ['libelle' => 'Gratuit', 'statut' => 1],
            ['libelle' => 'Payant', 'statut' => 1],
        ];
        
        foreach ($typeTarifications as $type) {
            TypeTarification::firstOrCreate(
                ['libelle' => $type['libelle']],
                $type
            );
        }
        
        // Visibilités
        $visibilites = [
            ['libelle' => 'Public', 'statut' => 1],
            ['libelle' => 'Privé', 'statut' => 1],
            ['libelle' => 'Sur invitation', 'statut' => 1],
        ];
        
        foreach ($visibilites as $visibilite) {
            Visibilite::firstOrCreate(
                ['libelle' => $visibilite['libelle']],
                $visibilite
            );
        }
        
        $this->command->info('Données de référence créées avec succès !');
    }
}
