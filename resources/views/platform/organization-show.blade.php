@extends('layouts.app')

@section('title', 'Détails organisation')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; max-width:1480px; margin:0 auto; color:#2c2a25; }
    .panel,.metric { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .muted { color:var(--muted); }
    .grid { display:grid; gap:18px; }
    .metrics { grid-template-columns:repeat(4,minmax(0,1fr)); margin-bottom:18px; }
    .main-grid { grid-template-columns:minmax(0,1.2fr) minmax(360px,.8fr); align-items:start; }
    .metric span { color:var(--muted); display:block; font-size:.74rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
    .metric strong { color:var(--ink); display:block; font-size:1.9rem; font-weight:600; margin-top:8px; }
    .section-title { color:var(--ink); font-size:1rem; font-weight:600; margin:0 0 4px; }
    .form-control,.form-select { border:1px solid var(--line); border-radius:10px; min-height:42px; }
    .btn-dark { background:#171713; border:0; border-radius:10px; }
    .link-box { background:var(--soft); border:1px solid var(--line); border-radius:10px; color:#171713; font-size:.82rem; padding:10px 12px; word-break:break-all; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; }
    .status-active,.status-trialing,.status-publie,.status-present,.status-paid { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-suspended,.status-cancelled,.status-annule,.status-absent { background:rgba(164,81,74,.13); color:#a4514a; }
    .status-brouillon,.status-en_attente,.status-pending,.status-draft { background:rgba(185,137,67,.14); color:#8a6128; }
    .mini-table { border-collapse:separate; border-spacing:0; width:100%; }
    .mini-table th { color:var(--muted); font-size:.72rem; font-weight:600; letter-spacing:.07em; padding:0 10px 10px; text-transform:uppercase; white-space:nowrap; }
    .mini-table td { border-top:1px solid var(--line); padding:12px 10px; vertical-align:middle; }
    .module-grid { display:grid; gap:12px; grid-template-columns:repeat(3,minmax(0,1fr)); }
    .module-card { background:var(--soft); border:1px solid var(--line); border-radius:14px; padding:14px; }
    .module-card strong { color:var(--ink); display:block; font-size:1.35rem; font-weight:600; }
    .quick-nav { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px; }
    .quick-nav a { align-items:center; background:var(--panel); border:1px solid var(--line); border-radius:999px; color:var(--ink); display:inline-flex; font-size:.84rem; gap:6px; padding:8px 12px; text-decoration:none; }
    .quick-nav a:hover { border-color:#b98943; color:#8a6128; }
    .role-form { display:grid; gap:8px; grid-template-columns:minmax(0,1fr) 160px 42px; margin-bottom:10px; }
    .logo-preview { align-items:center; background:var(--soft); border:1px solid var(--line); border-radius:14px; display:flex; height:58px; justify-content:center; overflow:hidden; width:58px; }
    .logo-preview img { height:100%; object-fit:cover; width:100%; }
    .permission-box { background:var(--soft); border:1px solid var(--line); border-radius:14px; padding:14px; }
    .permission-box .form-check-input { border-color:#b98943; }
    @media (max-width:1100px){ .metrics,.main-grid,.module-grid{grid-template-columns:1fr;} .role-form{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="platform">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">Organisation</div>
            <h1 class="mb-1" style="font-weight:600;">{{ $organization->name }}</h1>
            <p class="muted mb-0">{{ $organization->slug }} · {{ $organization->domain ?: 'Aucun domaine' }}</p>
        </div>
        <a href="{{ route('platform.organizations') }}" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left me-2"></i> Organisations
        </a>
    </div>

    <section class="grid metrics">
        <article class="metric"><span>Événements</span><strong>{{ $eventsCount }}</strong></article>
        <article class="metric"><span>Utilisateurs</span><strong>{{ $organization->users_count }}</strong></article>
        <article class="metric"><span>Inscriptions</span><strong>{{ $registrationsCount }}</strong></article>
        <article class="metric"><span>Liens envoyés</span><strong>{{ $accessLinksCount }}</strong></article>
    </section>

    <nav class="quick-nav" aria-label="Sections organisation">
        <a href="#detail"><i class="bi bi-building"></i> Détail</a>
        <a href="#events"><i class="bi bi-calendar-event"></i> Événements</a>
        <a href="#categories"><i class="bi bi-tags"></i> Catégories</a>
        <a href="#devises"><i class="bi bi-cash-coin"></i> Devises</a>
        <a href="#localisations"><i class="bi bi-geo-alt"></i> Pays & villes</a>
        <a href="#users"><i class="bi bi-people"></i> Utilisateurs</a>
        <a href="#plan"><i class="bi bi-gem"></i> Plan SaaS</a>
        <a href="#billing"><i class="bi bi-receipt"></i> Facturation</a>
        <a href="#branding"><i class="bi bi-palette"></i> Branding</a>
        <a href="#invitation-card"><i class="bi bi-postcard"></i> Carte invitation</a>
    </nav>

    <div class="grid main-grid">
        <main class="grid">
            <section class="panel" id="detail">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h2 class="section-title">Détail organisation</h2>
                        <p class="muted mb-0">Plan, statut, domaine, dates et liens d'accès client.</p>
                    </div>
                    <span class="status-pill status-{{ $organization->status }}">{{ ucfirst($organization->status) }}</span>
                </div>

                <form method="POST" action="{{ route('platform.organizations.update', $organization) }}" class="row g-3 align-items-end">
                    @csrf
                    @method('PUT')
                    <div class="col-lg-4">
                        <label class="form-label">Nom</label>
                        <input class="form-control" name="name" value="{{ $organization->name }}" required>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Plan</label>
                        <select class="form-select" name="plan">
                            @foreach(\App\Support\SaasPlans::all(false) as $key => $availablePlan)
                                <option value="{{ $key }}" {{ $organization->plan === $key ? 'selected' : '' }}>{{ $availablePlan['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status">
                            @foreach(['active', 'trialing', 'suspended', 'cancelled'] as $status)
                                <option value="{{ $status }}" {{ $organization->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Domaine</label>
                        <input class="form-control" name="domain" value="{{ $organization->domain }}">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Fin abonnement</label>
                        <input type="date" class="form-control" name="subscription_ends_at" value="{{ optional($organization->subscription_ends_at)->format('Y-m-d') }}">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-dark" type="submit"><i class="bi bi-save me-2"></i> Enregistrer</button>
                    </div>
                </form>

                <div class="row g-3 mt-3">
                    <div class="col-lg-6">
                        <label class="form-label">Lien register client</label>
                        <div class="link-box">
                            @if($organization->onboarding_token)
                                {{ route('client.register', $organization->onboarding_token) }}
                            @else
                                Onboarding utilisé ou non généré.
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label">Lien login client</label>
                        <div class="link-box">{{ route('client.login.organization', $organization->slug) }}</div>
                    </div>
                    <div class="col-lg-1 d-flex align-items-end">
                        <form method="POST" action="{{ route('platform.organizations.onboarding-link', $organization) }}" class="w-100">
                            @csrf
                            <button class="btn btn-outline-dark w-100" type="submit" title="Regénérer">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="panel" id="plan">
                <h2 class="section-title">Modules client</h2>
                <p class="muted">Vue synthétique des espaces client: catégories, devises, pays & villes, utilisateurs, plans, facturation et branding.</p>
                <div class="module-grid">
                    <div class="module-card" id="categories"><span class="muted">Catégories d'événement</span><strong>{{ $categories->count() }}</strong></div>
                    <div class="module-card" id="devises"><span class="muted">Devises</span><strong>{{ $devises->count() }}</strong></div>
                    <div class="module-card" id="localisations"><span class="muted">Pays & villes</span><strong>{{ $countries->count() }} / {{ $citiesCount }}</strong></div>
                    <div class="module-card"><span class="muted">Utilisateurs</span><strong>{{ $users->count() }}</strong></div>
                    <div class="module-card"><span class="muted">Plan SaaS</span><strong>{{ $plan['name'] }}</strong></div>
                    <div class="module-card"><span class="muted">Factures</span><strong>{{ $invoices->count() }}</strong></div>
                </div>
            </section>

            <section class="panel" id="events">
                <h2 class="section-title">Événements récents</h2>
                <p class="muted">{{ $publishedEvents }} publié(s), {{ $draftEvents }} brouillon(s). Derniers événements créés dans cette organisation.</p>
                <div class="table-responsive">
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Événement</th>
                                <th>Catégorie</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Prix</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                <tr>
                                    <td>
                                        <strong>{{ $event->titre }}</strong>
                                        <div class="muted">{{ $event->ville ?: $event->lieu ?: 'Lieu non défini' }}</div>
                                    </td>
                                    <td>{{ optional($event->category)->libelle ?: '-' }}</td>
                                    <td>{{ optional($event->date_debut)->format('d/m/Y') ?: '-' }}</td>
                                    <td><span class="status-pill status-{{ $event->statut }}">{{ ucfirst($event->statut) }}</span></td>
                                    <td>{{ $event->prix ? number_format((float) $event->prix, 0, ',', ' ') . ' ' . optional($event->devise)->libelle : 'Gratuit' }}</td>
                                    <td>
                                        <a href="{{ route('platform.organizations.events.show', [$organization, $event]) }}" class="btn btn-outline-dark btn-sm">
                                            <i class="bi bi-eye me-1"></i> Détail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="muted text-center py-4">Aucun événement.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <aside class="grid">
            <section class="panel" id="users">
                <h2 class="section-title">Utilisateurs et rôles</h2>
                <p class="muted">Gestion rapide des permissions client.</p>
                @forelse($users as $clientUser)
                    <form method="POST" action="{{ route('platform.organizations.users.role', [$organization, $clientUser]) }}" class="role-form">
                        @csrf
                        @method('PUT')
                        <div>
                            <strong>{{ $clientUser->name }}</strong>
                            <div class="muted">{{ $clientUser->email }}</div>
                        </div>
                        <select class="form-select" name="role">
                            @foreach(['super_admin' => 'Admin principal', 'admin' => 'Admin', 'manager' => 'Manager', 'moderateur' => 'Modérateur'] as $role => $label)
                                <option value="{{ $role }}" {{ $clientUser->role === $role ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-dark" type="submit"><i class="bi bi-save"></i></button>
                    </form>
                @empty
                    <div class="muted">Aucun utilisateur client.</div>
                @endforelse
            </section>

            <section class="panel">
                <h2 class="section-title">Référentiels</h2>
                <p class="muted">Catégories, devises et localisations configurées.</p>
                <div class="mb-3">
                    <strong>Catégories</strong>
                    <div class="muted">{{ $categories->pluck('libelle')->take(8)->implode(', ') ?: 'Aucune catégorie' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Devises</strong>
                    <div class="muted">{{ $devises->pluck('libelle')->take(8)->implode(', ') ?: 'Aucune devise' }}</div>
                </div>
                <div>
                    <strong>Pays & villes</strong>
                    <div class="muted">
                        @forelse($countries->take(6) as $country)
                            {{ $country->flag }} {{ $country->nom }} ({{ $country->cities_count }}){{ !$loop->last ? ',' : '' }}
                        @empty
                            Aucun pays
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="panel" id="billing">
                <h2 class="section-title">Facturation</h2>
                <p class="muted">Plan {{ $plan['name'] }} · {{ $organization->subscription_ends_at ? 'échéance ' . $organization->subscription_ends_at->format('d/m/Y') : 'pas d’échéance' }}</p>
                @forelse($invoices as $invoice)
                    <div class="d-flex justify-content-between align-items-center gap-3 py-2 border-top">
                        <div>
                            <strong>{{ $invoice->reference }}</strong>
                            <div class="muted">{{ $invoice->description }}</div>
                        </div>
                        <span class="status-pill status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                @empty
                    <div class="muted">Aucune facture.</div>
                @endforelse
            </section>

            <section class="panel" id="branding">
                <h2 class="section-title">Branding</h2>
                @php($branding = ($organization->settings ?? [])['branding'] ?? [])
                <p class="muted mb-2">Nom affiché: <strong>{{ $branding['brand_name'] ?? $organization->name }}</strong></p>
                <p class="muted mb-2">Couleur principale: {{ $branding['primary_color'] ?? '#171713' }}</p>
                <p class="muted mb-0">Couleur accent: {{ $branding['accent_color'] ?? '#b98943' }}</p>
            </section>

            <section class="panel" id="invitation-card">
                @php
                    $invitationCard = ($organization->settings ?? [])['invitation_card'] ?? [];
                    $signatureLogo = !empty($invitationCard['signature_logo']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($invitationCard['signature_logo']) : null;
                    $organizationLogo = $organization->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($organization->logo) : null;
                @endphp
                <h2 class="section-title">Carte d’invitation</h2>
                <p class="muted">Signature plateforme et autorisation d’affichage du logo organisateur.</p>

                <form method="POST" action="{{ route('platform.organizations.invitation-card.update', $organization) }}" enctype="multipart/form-data" class="grid">
                    @csrf
                    @method('PUT')

                    <div class="permission-box">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="allow_organization_logo" name="allow_organization_logo" value="1" {{ !empty($invitationCard['allow_organization_logo']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_organization_logo">Autoriser le logo organisateur sur la carte</label>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="logo-preview">
                                @if($organizationLogo)
                                    <img src="{{ $organizationLogo }}" alt="{{ $organization->name }}">
                                @else
                                    <i class="bi bi-building"></i>
                                @endif
                            </div>
                            <div class="muted">
                                {{ $organizationLogo ? 'Logo disponible dans le branding client.' : 'Aucun logo organisation enregistré.' }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Texte de signature SA</label>
                        <textarea class="form-control" name="signature_text" rows="3" placeholder="Ex: Validé par Accès Business">{{ old('signature_text', $invitationCard['signature_text'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="form-label">Logo de signature SA</label>
                        <input type="file" class="form-control" name="signature_logo" accept="image/png,image/jpeg,image/jpg,image/webp">
                        @if($signatureLogo)
                            <div class="d-flex align-items-center gap-3 mt-3">
                                <div class="logo-preview"><img src="{{ $signatureLogo }}" alt="Signature plateforme"></div>
                                <span class="muted">Logo de signature actuellement utilisé.</span>
                            </div>
                        @endif
                    </div>

                    <button class="btn btn-dark" type="submit">
                        <i class="bi bi-save me-2"></i> Enregistrer la carte
                    </button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
