<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\EventOtpController;
use App\Http\Controllers\EventAccessController;
use App\Http\Controllers\ReferenceDataController;
use App\Http\Controllers\SaasSettingsController;
use App\Http\Controllers\PlatformAdminController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::get('/', function () {
    // Si aucun utilisateur n'existe, rediriger vers l'inscription
    if (\Illuminate\Support\Facades\Schema::hasTable('users') && \App\Models\User::count() === 0) {
        return redirect('/register');
    }
    return redirect()->route('client.login');
});

// Routes d'authentification
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'clientLogin'])->middleware('throttle:6,1');
Route::get('/platform/login', [AuthController::class, 'showPlatformLoginForm'])->name('platform.login');
Route::post('/platform/login', [AuthController::class, 'platformLogin'])->name('platform.login.store')->middleware('throttle:6,1');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:4,1');
Route::get('/client/login', [AuthController::class, 'showClientLoginForm'])->name('client.login');
Route::get('/client/login/{organization:slug}', [AuthController::class, 'showClientLoginForm'])->name('client.login.organization');
Route::post('/client/login', [AuthController::class, 'clientLogin'])->name('client.login.store')->middleware('throttle:6,1');
Route::get('/client/register', [AuthController::class, 'showRegisterForm'])->name('client.register.self');
Route::get('/client/register/{token}', [AuthController::class, 'showClientRegisterForm'])->name('client.register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:platform_admin'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('/dashboard', [PlatformAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/organizations', [PlatformAdminController::class, 'organizations'])->name('organizations');
    Route::post('/organizations', [PlatformAdminController::class, 'storeOrganization'])->name('organizations.store');
    Route::put('/organizations/{organization}', [PlatformAdminController::class, 'updateOrganization'])->name('organizations.update');
    Route::post('/organizations/{organization}/onboarding-link', [PlatformAdminController::class, 'regenerateOnboardingLink'])->name('organizations.onboarding-link');
    Route::put('/organizations/{organization}/users/{user}/role', [PlatformAdminController::class, 'updateUserRole'])->name('organizations.users.role');
    Route::get('/plans', [PlatformAdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [PlatformAdminController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [PlatformAdminController::class, 'updatePlan'])->name('plans.update');
});

// Routes protégées
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Gestion des utilisateurs (seulement pour super admin)
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class);

        Route::get('/saas/plans', [SaasSettingsController::class, 'plans'])->name('saas.plans');
        Route::post('/saas/plans', [SaasSettingsController::class, 'updatePlan'])->name('saas.plans.update');
        Route::get('/saas/facturation', [SaasSettingsController::class, 'billing'])->name('saas.billing');
        Route::put('/saas/facturation', [SaasSettingsController::class, 'updateBilling'])->name('saas.billing.update');
        Route::get('/saas/branding', [SaasSettingsController::class, 'branding'])->name('saas.branding');
        Route::put('/saas/branding', [SaasSettingsController::class, 'updateBranding'])->name('saas.branding.update');
    });

    // Routes d'accès (admin) - Doit être avant la route resource pour éviter les conflits
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/events/download-csv-template', [EventAccessController::class, 'downloadCsvTemplate'])->name('events.download-csv-template');
    });

    // Gestion des événements (tous les utilisateurs authentifiés)
    Route::post('/events/draft', [EventController::class, 'saveDraft'])->name('events.draft');
    Route::delete('/events/destroy-all', [EventController::class, 'destroyAll'])->name('events.destroy-all');
    Route::resource('events', EventController::class);

    Route::middleware('role:super_admin,admin,manager')->group(function () {
        Route::get('/categories-evenement', [ReferenceDataController::class, 'categories'])->name('categories.index');
        Route::post('/categories-evenement', [ReferenceDataController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories-evenement/{category}', [ReferenceDataController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories-evenement/{category}', [ReferenceDataController::class, 'destroyCategory'])->name('categories.destroy');

        Route::get('/devises', [ReferenceDataController::class, 'devises'])->name('devises.index');
        Route::post('/devises', [ReferenceDataController::class, 'storeDevise'])->name('devises.store');
        Route::put('/devises/{devise}', [ReferenceDataController::class, 'updateDevise'])->name('devises.update');
        Route::delete('/devises/{devise}', [ReferenceDataController::class, 'destroyDevise'])->name('devises.destroy');

        Route::get('/localisations', [ReferenceDataController::class, 'localisations'])->name('localisations.index');
        Route::post('/pays', [ReferenceDataController::class, 'storeCountry'])->name('countries.store');
        Route::put('/pays/{country}', [ReferenceDataController::class, 'updateCountry'])->name('countries.update');
        Route::delete('/pays/{country}', [ReferenceDataController::class, 'destroyCountry'])->name('countries.destroy');
        Route::post('/villes', [ReferenceDataController::class, 'storeCity'])->name('cities.store');
        Route::put('/villes/{city}', [ReferenceDataController::class, 'updateCity'])->name('cities.update');
        Route::delete('/villes/{city}', [ReferenceDataController::class, 'destroyCity'])->name('cities.destroy');
    });

    // Routes pour les inscriptions et accès aux événements
    Route::post('/events/{event}/register', [EventRegistrationController::class, 'store'])->name('events.register');

    // Routes d'accès (admin)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/events/{event}/send-link', [EventAccessController::class, 'showSendLinkForm'])->name('events.send-link');
        Route::post('/events/{event}/send-link', [EventAccessController::class, 'sendLink'])->name('events.send-link');
        Route::get('/events/{event}/registrations', [EventRegistrationController::class, 'index'])->name('events.registrations');
    });
});

// Routes publiques pour l'accès avec token (privé/invitation)
Route::get('/events/{event}/access/{token}', [EventAccessController::class, 'showAccessForm'])->name('events.access');

// Routes OTP publiques (pour l'accès sans authentification)
Route::post('/events/{event}/request-otp', [EventOtpController::class, 'requestOtp'])->name('events.request-otp');
Route::post('/events/{event}/verify-otp', [EventOtpController::class, 'verifyOtp'])->name('events.verify-otp');

// Routes publiques pour répondre après vérification OTP
Route::get('/events/{event}/respond', [EventRegistrationController::class, 'showResponseForm'])->name('events.respond');
Route::post('/events/{event}/respond', [EventRegistrationController::class, 'submitResponse'])->name('events.submit-response');
Route::get('/events/{event}/response-confirmation', [EventRegistrationController::class, 'showResponseConfirmation'])->name('events.response-confirmation');

// Route publique pour télécharger la carte d'invitation PDF (sécurisée par token unique)
Route::get('/invitation/download/{token}', [EventRegistrationController::class, 'downloadInvitationCard'])->name('invitation.download');

// Route pour vérifier le QR code (publique)
Route::get('/events/verify-qr/{token}', function ($token) {
    $registration = \App\Models\EventRegistration::where('token_unique', $token)->first();

    if (!$registration) {
        return view('events.qr-invalid');
    }

    return view('events.qr-verified', compact('registration'));
})->name('events.verify-qr');
