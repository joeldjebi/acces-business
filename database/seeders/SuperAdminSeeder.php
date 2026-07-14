<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platformEmail = config('saas.platform_admin.email');

        if ($platformEmail && env('PLATFORM_ADMIN_PASSWORD') && !User::where('role', 'platform_admin')->exists()) {
            User::create([
                'organization_id' => null,
                'name' => config('saas.platform_admin.name', 'Platform Admin'),
                'email' => $platformEmail,
                'password' => Hash::make(env('PLATFORM_ADMIN_PASSWORD', Str::random(24))),
                'role' => 'platform_admin',
            ]);

            $this->command->info('Super admin plateforme créé.');
            $this->command->info('Email: ' . $platformEmail);
        } elseif ($platformEmail && !User::where('role', 'platform_admin')->exists()) {
            $this->command->warn('Aucun super admin plateforme créé: définissez PLATFORM_ADMIN_PASSWORD ou utilisez php artisan platform:create-admin.');
        }

        // Vérifier si un super admin existe déjà
        $existingSuperAdmin = User::where('role', 'super_admin')->first();
        
        if (!$existingSuperAdmin) {
            $organizationName = config('app.name') === 'Laravel'
                ? 'Accès Business'
                : config('app.name', 'Accès Business');

            $organization = Organization::firstOrCreate(
                ['slug' => Str::slug($organizationName) ?: 'acces-business'],
                [
                    'name' => $organizationName,
                    'plan' => 'starter',
                    'status' => 'active',
                ]
            );

            User::create([
                'organization_id' => $organization->id,
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
