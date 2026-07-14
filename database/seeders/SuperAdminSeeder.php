<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si un super admin existe déjà
        $existingSuperAdmin = User::where('role', 'super_admin')->first();
        
        if (!$existingSuperAdmin) {
            User::create([
                'name' => 'Super Administrateur',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]);
            
            $this->command->info('Super administrateur créé avec succès !');
            $this->command->info('Email: admin@example.com');
            $this->command->info('Mot de passe: password');
            $this->command->warn('⚠️  Veuillez changer le mot de passe après la première connexion !');
        } else {
            $this->command->info('Un super administrateur existe déjà.');
        }
    }
}
