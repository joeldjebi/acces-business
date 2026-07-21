@extends('layouts.app')

@section('title', 'Branding')

@php
    $primary = old('primary_color', $branding['primary_color'] ?? '#171713');
    $accent = old('accent_color', $branding['accent_color'] ?? '#b98943');
    $brandName = old('brand_name', $branding['brand_name'] ?? $organization->name);
    $logoUrl = \App\Support\EventMedia::storageUrl($organization->logo);
@endphp

@push('styles')
<style>
    .branding-page { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; max-width:1180px; margin:0 auto; color:#2c2a25; }
    .branding-grid { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:18px; align-items:start; }
    .brand-card { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:22px; }
    .brand-title { color:var(--ink); font-size:clamp(1.7rem,2.5vw,2.45rem); font-weight:600; margin:0 0 8px; }
    .brand-copy, .muted { color:var(--muted); }
    .form-label { color:var(--ink); font-weight:600; font-size:.86rem; }
    .form-control { border:1px solid var(--line); border-radius:10px; min-height:44px; }
    .color-row { display:grid; grid-template-columns:56px minmax(0,1fr); gap:10px; align-items:center; }
    .color-row input[type="color"] { border:1px solid var(--line); border-radius:10px; height:44px; width:56px; padding:4px; }
    .btn-brand { background:#171713; border:0; border-radius:10px; color:white; font-weight:600; min-height:44px; padding:0 16px; }
    .preview { background:{{ $primary }}; border-radius:16px; color:white; overflow:hidden; }
    .preview-head { align-items:center; display:flex; gap:12px; padding:18px; }
    .preview-logo { align-items:center; background:{{ $accent }}; border-radius:12px; color:{{ $primary }}; display:flex; height:48px; justify-content:center; overflow:hidden; width:48px; }
    .preview-logo img { height:100%; object-fit:cover; width:100%; }
    .preview-body { background:#fffefa; color:#171713; padding:18px; }
    .preview-pill { background:{{ $accent }}22; border:1px solid {{ $accent }}66; border-radius:999px; color:#6f4c1f; display:inline-flex; font-size:.8rem; font-weight:600; padding:7px 11px; }
    @media (max-width: 1000px) { .branding-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="branding-page">
    <div class="mb-4">
        <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">{{ $organization->name }} · identité</div>
        <h1 class="brand-title">Branding par organisation</h1>
        <p class="brand-copy mb-0">Personnalisez le nom affiché, le logo et les couleurs visibles dans la console SaaS.</p>
    </div>

    <div class="branding-grid">
        <section class="brand-card">
            <form method="POST" action="{{ route('saas.branding.update') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-12">
                    <label class="form-label">Nom affiché</label>
                    <input class="form-control" name="brand_name" value="{{ $brandName }}" placeholder="{{ $organization->name }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Couleur principale</label>
                    <div class="color-row">
                        <input type="color" name="primary_color" value="{{ $primary }}">
                        <input class="form-control" value="{{ $primary }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Couleur accent</label>
                    <div class="color-row">
                        <input type="color" name="accent_color" value="{{ $accent }}">
                        <input class="form-control" value="{{ $accent }}" readonly>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Logo</label>
                    <input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp">
                    <div class="muted mt-2">Format conseillé: carré, PNG ou WebP, moins de 2 Mo. L’affichage sur les cartes d’invitation dépend de l’autorisation du super admin plateforme.</div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn-brand">
                        <i class="bi bi-save me-2"></i> Enregistrer le branding
                    </button>
                </div>
            </form>
        </section>

        <aside class="brand-card">
            <h2 class="h5 mb-3" style="font-weight:600;">Aperçu</h2>
            <div class="preview">
                <div class="preview-head">
                    <div class="preview-logo">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $brandName }}">
                        @else
                            <i class="bi bi-shield-check"></i>
                        @endif
                    </div>
                    <div>
                        <div style="font-weight:600;">{{ $brandName }}</div>
                        <div style="opacity:.7;font-size:.85rem;">Console SaaS</div>
                    </div>
                </div>
                <div class="preview-body">
                    <span class="preview-pill">Plan {{ ucfirst($organization->plan) }}</span>
                    <p class="muted mt-3 mb-0">Ce style sera utilisé comme base pour personnaliser progressivement les pages publiques et les invitations.</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
