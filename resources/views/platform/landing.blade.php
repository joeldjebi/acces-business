@extends('layouts.app')

@section('title', 'Landing page')

@push('styles')
<style>
    .landing-admin { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; max-width:1480px; margin:0 auto; color:#2c2a25; }
    .landing-admin h1 { color:var(--ink); font-size:clamp(1.8rem,2.8vw,2.7rem); font-weight:700; margin:0 0 8px; }
    .landing-copy { color:var(--muted); margin:0; }
    .topbar { align-items:flex-start; display:flex; flex-wrap:wrap; gap:14px; justify-content:space-between; margin-bottom:18px; }
    .actions { display:flex; flex-wrap:wrap; gap:10px; }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); margin-bottom:14px; overflow:hidden; }
    .panel summary { align-items:center; cursor:pointer; display:flex; gap:10px; justify-content:space-between; list-style:none; padding:18px 20px; }
    .panel summary::-webkit-details-marker { display:none; }
    .panel-title { align-items:center; display:flex; gap:10px; }
    .panel-title i { align-items:center; background:rgba(185,137,67,.12); border-radius:12px; color:#8a6128; display:flex; height:38px; justify-content:center; width:38px; }
    .panel-title strong { display:block; color:var(--ink); }
    .panel-title span { color:var(--muted); display:block; font-size:.88rem; margin-top:2px; }
    .panel-body { border-top:1px solid var(--line); padding:20px; }
    .grid { display:grid; gap:14px; grid-template-columns:repeat(2,minmax(0,1fr)); }
    .grid-3 { display:grid; gap:12px; grid-template-columns:repeat(3,minmax(0,1fr)); }
    label { color:var(--ink); display:block; font-size:.82rem; font-weight:700; margin-bottom:6px; }
    input[type="text"], input[type="url"], input[type="color"], textarea { border:1px solid var(--line); border-radius:12px; color:var(--ink); min-height:42px; padding:10px 12px; width:100%; }
    textarea { min-height:92px; resize:vertical; }
    input[type="file"] { border:1px dashed var(--line); border-radius:12px; padding:12px; width:100%; }
    .help { color:var(--muted); font-size:.82rem; margin-top:6px; }
    .preview-img { background:#f4eee4; border:1px solid var(--line); border-radius:14px; height:110px; object-fit:cover; width:180px; }
    .rows { display:grid; gap:10px; }
    .row-card { background:#fbf8f1; border:1px solid var(--line); border-radius:14px; padding:14px; }
    .row-head { align-items:center; display:flex; gap:10px; justify-content:space-between; margin-bottom:10px; }
    .checks { align-items:center; display:flex; flex-wrap:wrap; gap:12px; }
    .checks label { align-items:center; display:inline-flex; gap:7px; margin:0; }
    .btn-line { align-items:center; display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
    .mini-btn { align-items:center; border:1px solid var(--line); border-radius:999px; background:#fffefa; color:var(--ink); display:inline-flex; gap:7px; min-height:38px; padding:0 12px; text-decoration:none; }
    .mini-btn.danger { color:#a4514a; }
    .submit-bar { align-items:center; background:rgba(255,254,250,.92); border:1px solid var(--line); border-radius:18px; bottom:18px; box-shadow:0 18px 44px rgba(39,33,25,.12); display:flex; gap:10px; justify-content:space-between; padding:12px 14px; position:sticky; z-index:10; }
    @media(max-width:900px){ .grid,.grid-3{grid-template-columns:1fr;} .submit-bar{align-items:stretch; flex-direction:column;} }
</style>
@endpush

@section('content')
@php
    $image = fn (?string $filename) => $landing->imageUrl($filename);
    $lines = fn ($items) => implode("\n", $items ?? []);
@endphp
<div class="landing-admin">
    <div class="topbar">
        <div>
            <h1>Landing page</h1>
            <p class="landing-copy">Pilotez tous les textes, images, menus, sections, tarifs, FAQ et couleurs de la page publique.</p>
        </div>
        <div class="actions">
            <a href="{{ route('landing') }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-box-arrow-up-right"></i> Voir la landing</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('platform.landing.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <details class="panel" open>
            <summary><div class="panel-title"><i class="bi bi-stars"></i><div><strong>Identité & SEO</strong><span>Logo, nom de marque, titre navigateur et description Google.</span></div></div><i class="bi bi-chevron-down"></i></summary>
            <div class="panel-body grid">
                <div><label>Nom affiché</label><input type="text" name="settings[brand][name]" value="{{ old('settings.brand.name', $settings['brand']['name'] ?? '') }}"></div>
                <div><label>Icône fallback Bootstrap</label><input type="text" name="settings[brand][logo_icon]" value="{{ old('settings.brand.logo_icon', $settings['brand']['logo_icon'] ?? 'bi-shield-check') }}"><div class="help">Exemple: bi-shield-check, bi-gem, bi-stars.</div></div>
                <div><label>Logo actuel</label>@if($image($settings['brand']['logo'] ?? null))<img class="preview-img" src="{{ $image($settings['brand']['logo']) }}" alt="Logo landing">@else<div class="help">Aucun logo uploadé.</div>@endif<input type="hidden" name="settings[brand][logo]" value="{{ $settings['brand']['logo'] ?? '' }}"></div>
                <div><label>Uploader un nouveau logo</label><input type="file" name="logo_file" accept="image/*"></div>
                <div><label>Titre navigateur</label><input type="text" name="settings[brand][meta_title]" value="{{ old('settings.brand.meta_title', $settings['brand']['meta_title'] ?? '') }}"></div>
                <div><label>Description SEO</label><textarea name="settings[brand][meta_description]">{{ old('settings.brand.meta_description', $settings['brand']['meta_description'] ?? '') }}</textarea></div>
                <div><label>Couleur principale</label><input type="color" name="settings[theme][ink]" value="{{ old('settings.theme.ink', $settings['theme']['ink'] ?? '#171713') }}"></div>
                <div><label>Couleur accent</label><input type="color" name="settings[theme][gold]" value="{{ old('settings.theme.gold', $settings['theme']['gold'] ?? '#b98943') }}"></div>
                <div><label>Fond global</label><input type="color" name="settings[theme][background]" value="{{ old('settings.theme.background', $settings['theme']['background'] ?? '#fbf8f1') }}"></div>
            </div>
        </details>

        <details class="panel">
            <summary><div class="panel-title"><i class="bi bi-menu-button-wide"></i><div><strong>Menu de navigation</strong><span>Libellés, ancres et visibilité des liens du menu.</span></div></div><i class="bi bi-chevron-down"></i></summary>
            <div class="panel-body"><div class="rows" data-repeater="navigation">
                @foreach($settings['navigation'] ?? [] as $i => $item)
                    @include('platform.partials.landing-row-navigation', ['i' => $i, 'item' => $item])
                @endforeach
            </div><button type="button" class="mini-btn" data-add="navigation"><i class="bi bi-plus-lg"></i>Ajouter un lien</button></div>
        </details>

        <details class="panel" open>
            <summary><div class="panel-title"><i class="bi bi-layout-text-window"></i><div><strong>Hero</strong><span>Premier écran, CTA, image, badges et métriques.</span></div></div><i class="bi bi-chevron-down"></i></summary>
            <div class="panel-body">
                <div class="checks mb-3"><label><input type="checkbox" name="settings[hero][enabled]" value="1" @checked($settings['hero']['enabled'] ?? true)> Afficher le hero</label></div>
                <div class="grid">
                    <div><label>Eyebrow</label><input type="text" name="settings[hero][eyebrow]" value="{{ $settings['hero']['eyebrow'] ?? '' }}"></div>
                    <div><label>Titre</label><input type="text" name="settings[hero][title]" value="{{ $settings['hero']['title'] ?? '' }}"></div>
                    <div style="grid-column:1/-1"><label>Texte</label><textarea name="settings[hero][body]">{{ $settings['hero']['body'] ?? '' }}</textarea></div>
                    <div><label>Bouton principal</label><input type="text" name="settings[hero][primary_label]" value="{{ $settings['hero']['primary_label'] ?? '' }}"></div>
                    <div><label>Bouton secondaire</label><input type="text" name="settings[hero][secondary_label]" value="{{ $settings['hero']['secondary_label'] ?? '' }}"></div>
                    <div><label>Image actuelle</label>@if($image($settings['hero']['image'] ?? null))<img class="preview-img" src="{{ $image($settings['hero']['image']) }}" alt="Hero">@endif<input type="hidden" name="settings[hero][image]" value="{{ $settings['hero']['image'] ?? '' }}"></div>
                    <div><label>Nouvelle image hero</label><input type="file" name="hero_image_file" accept="image/*"></div>
                    <div><label>Alt image</label><input type="text" name="settings[hero][image_alt]" value="{{ $settings['hero']['image_alt'] ?? '' }}"></div>
                    <div><label>Carte flottante - titre</label><input type="text" name="settings[hero][floating_title]" value="{{ $settings['hero']['floating_title'] ?? '' }}"></div>
                    <div><label>Carte flottante - texte</label><input type="text" name="settings[hero][floating_text]" value="{{ $settings['hero']['floating_text'] ?? '' }}"></div>
                </div>
                <h2 class="h6 mt-4">Badges de confiance</h2><div class="rows" data-repeater="trust_items">@foreach($settings['trust_items'] ?? [] as $i => $item)@include('platform.partials.landing-row-icon-label', ['group' => 'trust_items', 'i' => $i, 'item' => $item])@endforeach</div><button type="button" class="mini-btn" data-add="trust_items"><i class="bi bi-plus-lg"></i>Ajouter un badge</button>
                <h2 class="h6 mt-4">Métriques hero</h2><div class="rows" data-repeater="hero_metrics">@foreach($settings['hero_metrics'] ?? [] as $i => $item)@include('platform.partials.landing-row-metric', ['i' => $i, 'item' => $item])@endforeach</div><button type="button" class="mini-btn" data-add="hero_metrics"><i class="bi bi-plus-lg"></i>Ajouter une métrique</button>
            </div>
        </details>

        <details class="panel">
            <summary><div class="panel-title"><i class="bi bi-grid-3x3-gap"></i><div><strong>Fonctionnalités</strong><span>Titre de section et cartes fonctionnelles.</span></div></div><i class="bi bi-chevron-down"></i></summary>
            <div class="panel-body">
                @include('platform.partials.landing-section-fields', ['key' => 'features_section', 'section' => $settings['features_section'], 'label' => 'Afficher les fonctionnalités'])
                <div class="rows" data-repeater="features">@foreach($settings['features'] ?? [] as $i => $item)@include('platform.partials.landing-row-feature', ['i' => $i, 'item' => $item])@endforeach</div><button type="button" class="mini-btn" data-add="features"><i class="bi bi-plus-lg"></i>Ajouter une fonctionnalité</button>
            </div>
        </details>

        <details class="panel">
            <summary><div class="panel-title"><i class="bi bi-person-vcard"></i><div><strong>Représentation, check-in & wallet</strong><span>Les trois sections produit clés.</span></div></div><i class="bi bi-chevron-down"></i></summary>
            <div class="panel-body">
                @foreach(['representation' => 'Représentation', 'checkin' => 'Check-in'] as $key => $title)
                    <h2 class="h6 mt-2">{{ $title }}</h2>
                    @include('platform.partials.landing-section-fields', ['key' => $key, 'section' => $settings[$key], 'label' => 'Afficher cette section'])
                    @if($key === 'representation')<div><label>Points clés, un par ligne</label><textarea name="settings[representation][items]">{{ $lines($settings['representation']['items'] ?? []) }}</textarea></div>@endif
                @endforeach
                <h2 class="h6 mt-3">Wallet</h2>
                @include('platform.partials.landing-section-fields', ['key' => 'wallet', 'section' => $settings['wallet'], 'label' => 'Afficher la section wallet'])
                <div class="grid">
                    <div><label>Image actuelle</label>@if($image($settings['wallet']['image'] ?? null))<img class="preview-img" src="{{ $image($settings['wallet']['image']) }}" alt="Wallet">@endif<input type="hidden" name="settings[wallet][image]" value="{{ $settings['wallet']['image'] ?? '' }}"></div>
                    <div><label>Nouvelle image wallet</label><input type="file" name="wallet_image_file" accept="image/*"></div>
                    <div><label>Alt image</label><input type="text" name="settings[wallet][image_alt]" value="{{ $settings['wallet']['image_alt'] ?? '' }}"></div>
                    <div><label>Titre caption</label><input type="text" name="settings[wallet][caption_title]" value="{{ $settings['wallet']['caption_title'] ?? '' }}"></div>
                    <div><label>Texte caption</label><input type="text" name="settings[wallet][caption_text]" value="{{ $settings['wallet']['caption_text'] ?? '' }}"></div>
                </div>
            </div>
        </details>

        <details class="panel">
            <summary><div class="panel-title"><i class="bi bi-gem"></i><div><strong>Tarifs & sécurité</strong><span>Plans affichés, bloc sécurité et preuves de confiance.</span></div></div><i class="bi bi-chevron-down"></i></summary>
            <div class="panel-body">
                @include('platform.partials.landing-section-fields', ['key' => 'pricing_section', 'section' => $settings['pricing_section'], 'label' => 'Afficher les plans'])
                <div class="rows" data-repeater="plans">@foreach($settings['plans'] ?? [] as $i => $item)@include('platform.partials.landing-row-plan', ['i' => $i, 'item' => $item, 'lines' => $lines])@endforeach</div><button type="button" class="mini-btn" data-add="plans"><i class="bi bi-plus-lg"></i>Ajouter un plan</button>
                <hr>
                @include('platform.partials.landing-section-fields', ['key' => 'security', 'section' => $settings['security'], 'label' => 'Afficher la sécurité'])
                <div class="rows" data-repeater="security_items">@foreach($settings['security']['items'] ?? [] as $i => $item)@include('platform.partials.landing-row-icon-label', ['group' => 'security][items', 'i' => $i, 'item' => $item])@endforeach</div><button type="button" class="mini-btn" data-add="security_items"><i class="bi bi-plus-lg"></i>Ajouter une preuve sécurité</button>
            </div>
        </details>

        <details class="panel">
            <summary><div class="panel-title"><i class="bi bi-question-circle"></i><div><strong>FAQ, CTA final & footer</strong><span>Questions, appel à l’action et bas de page.</span></div></div><i class="bi bi-chevron-down"></i></summary>
            <div class="panel-body">
                <div class="checks mb-3"><label><input type="checkbox" name="settings[faq_section][enabled]" value="1" @checked($settings['faq_section']['enabled'] ?? true)> Afficher la FAQ</label></div>
                <div class="grid"><div><label>Eyebrow FAQ</label><input type="text" name="settings[faq_section][eyebrow]" value="{{ $settings['faq_section']['eyebrow'] ?? '' }}"></div><div><label>Titre FAQ</label><input type="text" name="settings[faq_section][title]" value="{{ $settings['faq_section']['title'] ?? '' }}"></div></div>
                <div class="rows" data-repeater="faqs">@foreach($settings['faqs'] ?? [] as $i => $item)@include('platform.partials.landing-row-faq', ['i' => $i, 'item' => $item])@endforeach</div><button type="button" class="mini-btn" data-add="faqs"><i class="bi bi-plus-lg"></i>Ajouter une question</button>
                <hr>
                @include('platform.partials.landing-section-fields', ['key' => 'cta', 'section' => $settings['cta'], 'label' => 'Afficher le CTA final'])
                <div class="grid"><div><label>Bouton principal CTA</label><input type="text" name="settings[cta][primary_label]" value="{{ $settings['cta']['primary_label'] ?? '' }}"></div><div><label>Bouton secondaire CTA</label><input type="text" name="settings[cta][secondary_label]" value="{{ $settings['cta']['secondary_label'] ?? '' }}"></div><div><label>Marque footer</label><input type="text" name="settings[footer][brand]" value="{{ $settings['footer']['brand'] ?? '' }}"></div><div><label>Texte footer</label><input type="text" name="settings[footer][text]" value="{{ $settings['footer']['text'] ?? '' }}"></div></div>
            </div>
        </details>

        <div class="submit-bar">
            <span class="landing-copy">Toutes les modifications s’appliquent à la page publique après sauvegarde.</span>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Enregistrer la landing</button>
        </div>
    </form>
</div>

<template id="tpl-navigation">@include('platform.partials.landing-row-navigation', ['i' => '__INDEX__', 'item' => ['label' => '', 'anchor' => '', 'enabled' => true]])</template>
<template id="tpl-trust_items">@include('platform.partials.landing-row-icon-label', ['group' => 'trust_items', 'i' => '__INDEX__', 'item' => ['icon' => 'bi-check2', 'label' => '', 'enabled' => true]])</template>
<template id="tpl-hero_metrics">@include('platform.partials.landing-row-metric', ['i' => '__INDEX__', 'item' => ['label' => '', 'value' => '', 'enabled' => true]])</template>
<template id="tpl-features">@include('platform.partials.landing-row-feature', ['i' => '__INDEX__', 'item' => ['icon' => 'bi-check2', 'title' => '', 'body' => '', 'enabled' => true]])</template>
<template id="tpl-plans">@include('platform.partials.landing-row-plan', ['i' => '__INDEX__', 'item' => ['name' => '', 'subtitle' => '', 'price' => '', 'items' => [], 'featured' => false, 'enabled' => true], 'lines' => $lines])</template>
<template id="tpl-security_items">@include('platform.partials.landing-row-icon-label', ['group' => 'security][items', 'i' => '__INDEX__', 'item' => ['icon' => 'bi-shield-check', 'label' => '', 'enabled' => true]])</template>
<template id="tpl-faqs">@include('platform.partials.landing-row-faq', ['i' => '__INDEX__', 'item' => ['question' => '', 'answer' => '', 'enabled' => true]])</template>

<script>
    document.querySelectorAll('[data-add]').forEach((button) => {
        button.addEventListener('click', () => {
            const key = button.dataset.add;
            const container = document.querySelector(`[data-repeater="${key}"]`);
            const template = document.getElementById(`tpl-${key}`);
            if (!container || !template) return;
            const index = Date.now();
            container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index));
        });
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-row]');
        if (button) button.closest('.row-card')?.remove();
    });
</script>
@endsection
