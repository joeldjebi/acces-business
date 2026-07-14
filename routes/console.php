<?php

use Illuminate\Foundation\Inspiring;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform:create-admin {email} {--name=Platform Admin} {--password=}', function (string $email) {
    $password = $this->option('password') ?: Str::password(18);

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'organization_id' => null,
            'name' => $this->option('name') ?: 'Platform Admin',
            'password' => Hash::make($password),
            'role' => 'platform_admin',
        ]
    );

    $this->info('Compte platform_admin prêt: ' . $user->email);

    if (!$this->option('password')) {
        $this->warn('Mot de passe généré à conserver maintenant: ' . $password);
    }
})->purpose('Create or update a platform administrator account');
