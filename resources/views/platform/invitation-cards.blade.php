@extends('layouts.app')

@section('title', 'Cartes invitation')

@push('styles')
<style>
    .platform-cards { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; max-width:1480px; margin:0 auto; color:#2c2a25; }
    .page-head { align-items:flex-end; display:flex; gap:18px; justify-content:space-between; margin-bottom:20px; }
    .kicker { color:var(--gold); font-size:.76rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; }
    .title { color:var(--ink); font-size:clamp(1.8rem,3vw,2.7rem); font-weight:600; line-height:1.08; margin:6px 0 8px; }
    .copy,.muted { color:var(--muted); }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .toolbar { align-items:center; display:flex; gap:10px; margin-bottom:18px; }
    .form-control { border:1px solid var(--line); border-radius:12px; min-height:42px; }
    .btn-dark { background:#171713; border:0; border-radius:12px; min-height:42px; }
    .org-card { display:grid; gap:18px; grid-template-columns:minmax(220px,.8fr) minmax(0,1.4fr); padding:20px; }
    .org-card + .org-card { border-top:1px solid var(--line); }
    .org-logo { align-items:center; background:var(--soft); border:1px solid var(--line); border-radius:14px; display:flex; height:58px; justify-content:center; overflow:hidden; width:58px; }
    .org-logo img { height:100%; object-fit:cover; width:100%; }
    .org-name { color:var(--ink); display:block; font-size:1.05rem; font-weight:600; }
    .settings-grid { display:grid; gap:14px; grid-template-columns:repeat(2,minmax(0,1fr)); }
    .permission-box { background:var(--soft); border:1px solid var(--line); border-radius:14px; padding:14px; }
    .signature-row { display:grid; gap:12px; grid-template-columns:minmax(0,1fr) minmax(220px,.55fr); margin-top:14px; }
    .signature-logo { align-items:center; background:#fff; border:1px solid var(--line); border-radius:12px; display:flex; min-height:54px; justify-content:center; overflow:hidden; padding:8px; }
    .signature-logo img { max-height:44px; max-width:150px; object-fit:contain; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; }
    .status-on { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-off { background:rgba(185,137,67,.14); color:#8a6128; }
    @media (max-width: 1050px) { .page-head,.toolbar{align-items:flex-start;flex-direction:column;} .org-card,.settings-grid,.signature-row{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="platform-cards">
    <header class="page-head">
        <div>
            <div class="kicker">Super admin plateforme</div>
            <h1 class="title">Cartes invitation</h1>
            <p class="copy mb-0">Autorisez les logos et couleurs des organisations, puis ajoutez la signature plateforme affichée sur les cartes.</p>
        </div>
        <a href="{{ route('platform.organizations') }}" class="btn btn-outline-dark">
            <i class="bi bi-buildings me-2"></i> Organisations
        </a>
    </header>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }}">{{ session($key) }}</div>
        @endif
    @endforeach

    <section class="panel">
        <form method="GET" action="{{ route('platform.invitation-cards') }}" class="toolbar">
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Rechercher une organisation">
            <button class="btn btn-dark" type="submit"><i class="bi bi-search me-2"></i> Filtrer</button>
        </form>

        @forelse($organizations as $organization)
            @php
                $settings = $organization->settings ?? [];
                $branding = $settings['branding'] ?? [];
                $card = $settings['invitation_card'] ?? [];
                $organizationLogo = \App\Support\EventMedia::storageUrl($organization->logo);
                $signatureLogo = !empty($card['signature_logo']) ? \App\Support\EventMedia::storageUrl($card['signature_logo']) : null;
                $logoAllowed = !empty($card['allow_organization_logo']);
                $brandingAllowed = !empty($card['allow_organization_branding']);
            @endphp

            <article class="org-card">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="org-logo">
                            @if($organizationLogo)
                                <img src="{{ $organizationLogo }}" alt="{{ $organization->name }}">
                            @else
                                <i class="bi bi-building"></i>
                            @endif
                        </div>
                        <div>
                            <span class="org-name">{{ $organization->name }}</span>
                            <span class="muted">{{ $organization->slug }} · {{ $organization->plan }}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="status-pill {{ $logoAllowed ? 'status-on' : 'status-off' }}">{{ $logoAllowed ? 'Logo autorisé' : 'Logo bloqué' }}</span>
                        <span class="status-pill {{ $brandingAllowed ? 'status-on' : 'status-off' }}">{{ $brandingAllowed ? 'Couleurs autorisées' : 'Couleurs bloquées' }}</span>
                    </div>
                    <p class="muted mt-3 mb-0">
                        Couleurs client: {{ $branding['primary_color'] ?? '#171713' }} · {{ $branding['accent_color'] ?? '#b98943' }}
                    </p>
                </div>

                <form method="POST" action="{{ route('platform.organizations.invitation-card.update', $organization) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="settings-grid">
                        <div class="permission-box">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="allow_logo_{{ $organization->id }}" name="allow_organization_logo" value="1" {{ $logoAllowed ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_logo_{{ $organization->id }}">Autoriser le logo organisateur</label>
                            </div>
                            <div class="muted small mt-2">Le logo client apparaîtra sur la carte uniquement si cette option est activée.</div>
                        </div>

                        <div class="permission-box">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="allow_branding_{{ $organization->id }}" name="allow_organization_branding" value="1" {{ $brandingAllowed ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_branding_{{ $organization->id }}">Autoriser les couleurs organisation</label>
                            </div>
                            <div class="muted small mt-2">Si désactivé, la carte utilise les couleurs premium par défaut.</div>
                        </div>
                    </div>

                    <div class="signature-row">
                        <div>
                            <label class="form-label mt-3">Texte de signature SA</label>
                            <textarea class="form-control" name="signature_text" rows="3" placeholder="Ex: Validé par Accès Business">{{ old('signature_text', $card['signature_text'] ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label mt-3">Logo signature SA</label>
                            <input class="form-control" type="file" name="signature_logo" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <div class="signature-logo mt-2">
                                @if($signatureLogo)
                                    <img src="{{ $signatureLogo }}" alt="Signature plateforme">
                                @else
                                    <span class="muted small">Aucun logo de signature</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-dark" type="submit">
                            <i class="bi bi-save me-2"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </article>
        @empty
            <div class="text-center muted py-5">Aucune organisation trouvée.</div>
        @endforelse

        @if($organizations->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $organizations->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
