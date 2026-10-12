@extends('layouts.app')

@section('title', 'Branding')

@php
    $primary = old('primary_color', $branding['primary_color'] ?? '#171713');
    $accent = old('accent_color', $branding['accent_color'] ?? '#b98943');
    $brandName = old('brand_name', $branding['brand_name'] ?? $organization->name);
    $logoUrl = \App\Support\EventMedia::storageUrl($organization->logo);
    $cardSettings = ($organization->settings ?? [])['invitation_card'] ?? [];
    $colorsAllowed = (bool) ($cardSettings['allow_organization_branding'] ?? false);
    $logoAllowed = (bool) ($cardSettings['allow_organization_logo'] ?? false);
    $presets = [['#171713', '#b98943'], ['#0f2a43', '#d4a24c'], ['#1d3b2a', '#c9a25b'], ['#3a1e2b', '#d6a57c'], ['#1f2937', '#60a5fa']];
@endphp

@push('styles')
<style>
    .brand-page { margin: 0 auto; max-width: 1180px; }
    .brand-page *, .brand-page *::before, .brand-page *::after { min-width: 0; }
    .brand-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 400px; }
    .color-field { align-items: center; background: #fff; border: 1px solid rgba(23, 23, 19, .13); border-radius: 10px; display: flex; gap: 10px; height: 40px; padding: 0 6px 0 4px; transition: border-color .15s, box-shadow .15s; }
    .color-field:focus-within { border-color: var(--app-gold); box-shadow: 0 0 0 3px var(--app-gold-soft); }
    .color-field input[type=color] { -webkit-appearance: none; appearance: none; background: none; border: 0; border-radius: 8px; cursor: pointer; flex: 0 0 32px; height: 32px; padding: 0; width: 32px; }
    .color-field input[type=color]::-webkit-color-swatch-wrapper { padding: 0; }
    .color-field input[type=color]::-webkit-color-swatch { border: 1px solid rgba(23, 23, 19, .12); border-radius: 8px; }
    .color-field input[type=text] { background: none; border: 0; color: var(--app-ink); flex: 1; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .86rem; outline: 0; text-transform: uppercase; width: 100%; }
    .presets { display: flex; flex-wrap: wrap; gap: 8px; }
    .preset { background: none; border: 1px solid rgba(23, 23, 19, .13); border-radius: 999px; display: inline-flex; padding: 3px; transition: border-color .2s, transform .2s; }
    .preset:hover { border-color: var(--app-ink); transform: translateY(-1px); }
    .preset span { border-radius: 50%; display: block; height: 20px; width: 20px; }
    .preset span + span { margin-left: -6px; box-shadow: 0 0 0 2px #fff; }
    .logo-drop { align-items: center; background: #fff; border: 1.5px dashed rgba(23, 23, 19, .18); border-radius: 14px; cursor: pointer; display: flex; gap: 14px; margin: 0; padding: 12px; position: relative; transition: border-color .2s; }
    .logo-drop:hover, .logo-drop.is-over { border-color: var(--app-gold); }
    .logo-drop input { cursor: pointer; inset: 0; opacity: 0; position: absolute; }
    .logo-thumb { align-items: center; background: var(--app-bg); border-radius: 12px; color: var(--app-muted); display: flex; flex: 0 0 56px; font-size: 1.3rem; height: 56px; justify-content: center; overflow: hidden; width: 56px; }
    .logo-thumb img { height: 100%; object-fit: cover; width: 100%; }
    .logo-drop strong { color: var(--app-ink); display: block; font-size: .86rem; }
    .logo-drop small { color: var(--app-muted); font-size: .76rem; }

    .preview-wrap { display: grid; gap: 16px; position: sticky; top: 84px; }
    .pv-label { color: var(--app-muted); font-size: .72rem; font-weight: 600; letter-spacing: .06em; margin: 0 4px 8px; text-transform: uppercase; }
    .pv-console { background: #141412; border-radius: 18px; box-shadow: 0 24px 48px -28px rgba(23, 23, 19, .6); display: grid; grid-template-columns: 150px 1fr; overflow: hidden; }
    .pv-side { background: radial-gradient(120% 60% at 0% 0%, rgba(185, 137, 67, .16), transparent 60%), #141412; color: #fff; padding: 14px 12px; }
    .pv-brand { align-items: center; display: flex; gap: 8px; margin-bottom: 14px; }
    .pv-logo { align-items: center; background: linear-gradient(140deg, #d8b978, #b98943 55%, #8a6128); border-radius: 8px; color: #171713; display: flex; flex: 0 0 26px; font-size: .8rem; height: 26px; justify-content: center; overflow: hidden; width: 26px; }
    .pv-logo img { height: 100%; object-fit: cover; width: 100%; }
    .pv-brand strong { font-size: .76rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pv-nav { display: grid; gap: 4px; }
    .pv-nav span { background: rgba(255, 255, 255, .06); border-radius: 6px; height: 16px; }
    .pv-nav span:first-child { background: rgba(255, 255, 255, .14); }
    .pv-main { background: #f6f4ef; display: grid; gap: 8px; align-content: start; padding: 14px; }
    .pv-main span { background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0, 0, 0, .05); height: 30px; }
    .pv-main span:first-child { background: none; box-shadow: none; height: 12px; width: 60%; background: rgba(23, 23, 19, .12); border-radius: 4px; }

    .pv-card { border-radius: 18px; box-shadow: 0 24px 48px -28px rgba(23, 23, 19, .5); overflow: hidden; }
    .pv-card-top { background: radial-gradient(100% 120% at 0% 0%, color-mix(in srgb, var(--pv-accent) 45%, transparent), transparent 60%), var(--pv-primary); color: #fff; padding: 18px; transition: background .3s; }
    .pv-card-top small { color: var(--pv-accent); display: block; font-size: .62rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .pv-card-top strong { display: block; font-size: 1.05rem; font-weight: 700; letter-spacing: -.02em; margin-top: 6px; }
    .pv-card-body { align-items: center; background: #fff; display: flex; gap: 12px; justify-content: space-between; padding: 14px 18px; }
    .pv-card-body span { color: var(--app-muted); font-size: .76rem; }
    .pv-chip { background: color-mix(in srgb, var(--pv-accent) 16%, transparent); border-radius: 99px; color: color-mix(in srgb, var(--pv-accent) 70%, #000); font-size: .72rem; font-weight: 700; padding: 4px 10px; }
    .approval { align-items: center; border-radius: 10px; display: flex; font-size: .78rem; gap: 8px; padding: 8px 10px; }
    .approval.ok { background: rgba(46, 123, 101, .08); color: var(--app-green); }
    .approval.ko { background: var(--app-gold-soft); color: var(--app-gold-deep); }


    /* Invitation experience */
    .exp { margin-top: 20px; scroll-margin-top: 84px; }
    .switch-line { align-items: center; background: var(--app-bg); border-radius: 14px; display: flex; gap: 16px; justify-content: space-between; padding: 12px 16px; }
    .switch-line strong { color: var(--app-ink); display: block; font-size: .9rem; }
    .switch-line small { color: var(--app-muted); font-size: .78rem; }
    .tpl-grid, .music-grid { display: grid; gap: 12px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .tpl { cursor: pointer; display: block; margin: 0; position: relative; }
    .tpl input { opacity: 0; pointer-events: none; position: absolute; }
    .tpl-body { background: #fff; border: 1px solid rgba(23, 23, 19, .13); border-radius: 14px; display: flex; flex-direction: column; gap: 4px; height: 100%; padding: 12px; transition: border-color .2s, box-shadow .2s; }
    .tpl input:checked + .tpl-body { border-color: var(--app-ink); box-shadow: 0 0 0 3px rgba(23, 23, 19, .06); }
    .tpl input:focus-visible + .tpl-body { box-shadow: 0 0 0 3px var(--app-gold-soft); }
    .tpl.is-locked { cursor: not-allowed; }
    .tpl.is-locked .tpl-body { background: #f7f6f2; opacity: .75; }
    .tpl-body strong { color: var(--app-ink); font-size: .88rem; font-weight: 650; }
    .tpl-body small { color: var(--app-muted); font-size: .76rem; line-height: 1.4; }
    .music-body i { color: var(--app-gold-deep); font-size: 1.05rem; margin-bottom: 4px; }
    .tpl-preview { align-items: center; align-self: flex-start; background: rgba(23, 23, 19, .05); border: 0; border-radius: 99px; color: var(--app-ink); display: inline-flex; font-size: .74rem; font-weight: 600; gap: 5px; margin-top: 8px; padding: 5px 11px; }
    .tpl-preview:hover { background: rgba(23, 23, 19, .1); }
    .tpl-thumb { align-items: center; border-radius: 10px; display: flex; height: 86px; justify-content: center; margin-bottom: 8px; overflow: hidden; position: relative; }
    .tpl-thumb--classic { background: #f4efe6; }
    .tpl-thumb--prestige { background: #0d0d0c; }
    .tpl-thumb--modern { background: #f6f4ef; }
    .tpl-thumb span { aspect-ratio: 1.45; border-radius: 4px; position: relative; width: 58%; }
    .tpl-thumb--classic span { background: linear-gradient(160deg, #efe4cf, #e4d4b6); box-shadow: 0 8px 16px -8px rgba(40, 30, 15, .45); }
    .tpl-thumb--classic span::before { background: #e9dcc2; clip-path: polygon(0 0, 100% 0, 50% 58%); content: ""; inset: 0; position: absolute; }
    .tpl-thumb--classic span::after, .tpl-thumb--prestige span::after { background: var(--ie-accent); border-radius: 50%; content: ""; height: 16px; left: 50%; position: absolute; top: 58%; transform: translate(-50%, -50%); width: 16px; }
    .tpl-thumb--prestige span { background: #1b1a17; box-shadow: inset 0 0 0 1px rgba(216, 185, 120, .5); }
    .tpl-thumb--prestige span::before { background: #23211d; clip-path: polygon(0 0, 100% 0, 50% 58%); content: ""; inset: 0; position: absolute; }
    .tpl-thumb--prestige span::after { background: #b98943; }
    .tpl-thumb--modern span { aspect-ratio: .74; background: radial-gradient(120% 90% at 0% 0%, color-mix(in srgb, var(--ie-accent) 55%, transparent), transparent 60%), var(--ie-primary); border-radius: 2px 6px 6px 2px; box-shadow: 0 8px 16px -8px rgba(0, 0, 0, .45); width: 26%; }
    .custom-music { background: var(--app-bg); border-radius: 14px; margin-top: 14px; padding: 14px 16px; }
    .current-track { align-items: center; display: flex; gap: 12px; }
    .current-track > i { color: var(--app-gold-deep); font-size: 1.3rem; }
    .current-track > div { flex: 1; }
    .current-track strong { color: var(--app-ink); display: block; font-size: .86rem; margin-bottom: 4px; }
    .current-track audio { height: 32px; max-width: 100%; width: 100%; }
    .upsell { align-items: center; background: linear-gradient(120deg, rgba(185, 137, 67, .12), rgba(185, 137, 67, .04)); border: 1px solid rgba(185, 137, 67, .25); border-radius: 14px; color: #6b5126; display: flex; flex-wrap: wrap; font-size: .82rem; gap: 10px 14px; margin-top: 14px; padding: 12px 14px; }
    .upsell > i { color: var(--app-gold-deep); font-size: 1.1rem; }
    .upsell > span { flex: 1 1 260px; }
    .preview-stage { background: #f4efe6; height: min(560px, 72vh); position: relative; }
    .preview-slot { inset: 0; position: absolute; }
    @media (max-width: 767.98px) { .tpl-grid, .music-grid { grid-template-columns: 1fr; } }
    @media (max-width: 1099.98px) { .brand-grid { grid-template-columns: 1fr; } .preview-wrap { position: static; } }
</style>
@endpush

@section('content')
<div class="brand-page">
    <x-app.page-header kicker="Identité" title="Branding" subtitle="Le nom et le logo de votre espace, et les couleurs de vos cartes d’invitation." />

    <div class="brand-grid">
        <section class="ui-card">
            <form method="POST" action="{{ route('saas.branding.update') }}" enctype="multipart/form-data" class="ui-card-body" id="brandingForm" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label" for="brand_name">Nom affiché</label>
                        <input class="form-control @error('brand_name') is-invalid @enderror" id="brand_name" name="brand_name" value="{{ $brandName }}" placeholder="{{ $organization->name }}" maxlength="255">
                        @error('brand_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Affiché dans la console et sur vos cartes. Laissé vide, le nom de l’organisation est utilisé.</div>
                    </div>

                    <div class="col-12">
                        <span class="form-label d-block">Logo</span>
                        <label class="logo-drop @error('logo') border-danger @enderror" data-logo-drop>
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp" data-logo-input>
                            <span class="logo-thumb" data-logo-thumb>@if($logoUrl)<img src="{{ $logoUrl }}" alt="">@else<i class="bi bi-image"></i>@endif</span>
                            <span><strong data-logo-name>{{ $logoUrl ? 'Remplacer le logo' : 'Ajouter un logo' }}</strong><small>Carré, PNG, JPG ou WebP · 2 Mo max</small></span>
                        </label>
                        @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="primary_text">Couleur principale</label>
                        <div class="color-field">
                            <input type="color" name="primary_color" value="{{ $primary }}" data-color="primary" aria-label="Choisir la couleur principale">
                            <input type="text" id="primary_text" value="{{ strtoupper($primary) }}" data-hex="primary" maxlength="7" spellcheck="false">
                        </div>
                        @error('primary_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="accent_text">Couleur d’accent</label>
                        <div class="color-field">
                            <input type="color" name="accent_color" value="{{ $accent }}" data-color="accent" aria-label="Choisir la couleur d’accent">
                            <input type="text" id="accent_text" value="{{ strtoupper($accent) }}" data-hex="accent" maxlength="7" spellcheck="false">
                        </div>
                        @error('accent_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <span class="form-label d-block">Harmonies suggérées</span>
                        <div class="presets">
                            @foreach($presets as [$p, $a])
                                <button type="button" class="preset" data-preset="{{ $p }},{{ $a }}" title="{{ $p }} · {{ $a }}"><span style="background: {{ $p }}"></span><span style="background: {{ $a }}"></span></button>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end border-top pt-4">
                        <button type="reset" class="ui-btn ui-btn-soft">Réinitialiser</button>
                        <button type="submit" class="ui-btn ui-btn-primary"><i class="bi bi-check2"></i>Enregistrer le branding</button>
                    </div>
                </div>
            </form>
        </section>

        <aside class="preview-wrap" style="--pv-primary: {{ $primary }}; --pv-accent: {{ $accent }}" data-preview>
            <div>
                <div class="pv-label">Console</div>
                <div class="pv-console">
                    <div class="pv-side">
                        <div class="pv-brand">
                            <span class="pv-logo" data-pv-logo>@if($logoUrl)<img src="{{ $logoUrl }}" alt="">@else<i class="bi bi-shield-check"></i>@endif</span>
                            <strong data-pv-name>{{ $brandName }}</strong>
                        </div>
                        <div class="pv-nav"><span></span><span></span><span></span><span></span></div>
                    </div>
                    <div class="pv-main"><span></span><span></span><span></span></div>
                </div>
            </div>
            <div>
                <div class="pv-label">Carte d’invitation</div>
                <div class="pv-card">
                    <div class="pv-card-top"><small data-pv-name-card>{{ $brandName }}</small><strong>Gala annuel des partenaires</strong></div>
                    <div class="pv-card-body"><span>Invitation nominative · QR code</span><span class="pv-chip">VIP</span></div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <div @class(['approval', 'ok' => $colorsAllowed, 'ko' => !$colorsAllowed])><i class="bi {{ $colorsAllowed ? 'bi-check-circle' : 'bi-shield-lock' }}"></i>{{ $colorsAllowed ? 'Vos couleurs sont appliquées aux cartes d’invitation.' : 'Couleurs des cartes : en attente d’autorisation par la plateforme.' }}</div>
                <div @class(['approval', 'ok' => $logoAllowed, 'ko' => !$logoAllowed])><i class="bi {{ $logoAllowed ? 'bi-check-circle' : 'bi-shield-lock' }}"></i>{{ $logoAllowed ? 'Votre logo est affiché sur les cartes d’invitation.' : 'Logo sur les cartes : en attente d’autorisation par la plateforme.' }}</div>
            </div>
        </aside>
    </div>

    @php
        $selectedTemplate = old('template', isset($templates[$experience['template']]) ? $experience['template'] : array_key_first($templates));
        $selectedSource = old('music_source', $experience['music_source']);
        if ($selectedSource === 'custom' && !$canUseCustomMusic) { $selectedSource = 'platform'; }
        $platformMusic = $platformExperience['music']
            ? ['type' => 'file', 'url' => \App\Support\InvitationExperience::musicUrl($platformExperience['music']), 'title' => $platformExperience['music_title']]
            : ['type' => 'synth', 'url' => null, 'title' => 'Mélodie de la plateforme'];
        $customMusic = $experience['custom_music']
            ? ['type' => 'file', 'url' => \App\Support\InvitationExperience::musicUrl($experience['custom_music']), 'title' => $experience['custom_music_title']]
            : null;
        $previewMusic = match ($selectedSource) { 'none' => null, 'custom' => $customMusic ?: $platformMusic, default => $platformMusic };
        $expErrors = $errors->hasAny(['template', 'music_source', 'music', 'music_title', 'rights_confirmed']);
    @endphp

    <section class="ui-card exp" id="experience">
        <div class="ui-card-head has-border">
            <div><h2>Expérience d’invitation</h2><p>L’animation et la musique qui accueillent vos invités sur leur page de réponse.</p></div>
            @unless($platformExperience['enabled'])<span class="ui-pill warning">Désactivée par la plateforme</span>@endunless
        </div>
        <form method="POST" action="{{ route('saas.branding.experience.update') }}" enctype="multipart/form-data" class="ui-card-body" id="experienceForm" novalidate>
            @csrf
            @method('PUT')
            @if($expErrors)<div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>@endif

            <div class="switch-line">
                <div><strong>Activer l’animation d’ouverture</strong><small>Chaque événement peut ensuite garder ce réglage ou choisir une autre animation.</small></div>
                <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="enabled" value="1" @checked(old('enabled', $experience['enabled']))></div>
            </div>

            <span class="form-label d-block mt-4">Animation</span>
            <div class="tpl-grid">
                @forelse($templates as $key => $tpl)
                    <label class="tpl">
                        <input type="radio" name="template" value="{{ $key }}" @checked($selectedTemplate === $key)>
                        <span class="tpl-body">
                            <span class="tpl-thumb tpl-thumb--{{ $key }}" style="--ie-primary: {{ $primary }}; --ie-accent: {{ $accent }}" aria-hidden="true"><span></span></span>
                            <strong>{{ $tpl['name'] }}</strong>
                            <small>{{ $tpl['description'] }}</small>
                            <button type="button" class="tpl-preview" data-preview-template="{{ $key }}"><i class="bi bi-play-circle"></i>Aperçu</button>
                        </span>
                    </label>
                @empty
                    <p class="text-muted small">Aucune animation n’est proposée par la plateforme pour le moment.</p>
                @endforelse
            </div>

            <span class="form-label d-block mt-4">Musique</span>
            <div class="music-grid">
                <label class="tpl">
                    <input type="radio" name="music_source" value="platform" @checked($selectedSource === 'platform')>
                    <span class="tpl-body music-body"><i class="bi bi-music-note-beamed"></i><strong>Musique de la plateforme</strong><small>{{ $platformMusic['title'] ?: 'Mélodie douce' }}</small></span>
                </label>
                <label @class(['tpl', 'is-locked' => !$canUseCustomMusic])>
                    <input type="radio" name="music_source" value="custom" @checked($selectedSource === 'custom') @disabled(!$canUseCustomMusic)>
                    <span class="tpl-body music-body">
                        <i class="bi {{ $canUseCustomMusic ? 'bi-vinyl' : 'bi-lock' }}"></i><strong>Ma musique</strong>
                        <small>{{ $canUseCustomMusic ? ($customMusic['title'] ?? 'Ajoutez votre propre morceau') : 'Forfait payant requis' }}</small>
                    </span>
                </label>
                <label class="tpl">
                    <input type="radio" name="music_source" value="none" @checked($selectedSource === 'none')>
                    <span class="tpl-body music-body"><i class="bi bi-volume-mute"></i><strong>Sans musique</strong><small>Animation silencieuse</small></span>
                </label>
            </div>

            @if($canUseCustomMusic)
                <div class="custom-music" data-custom-music @if($selectedSource !== 'custom') hidden @endif>
                    @if($customMusic)
                        <div class="current-track"><i class="bi bi-vinyl"></i><div><strong>{{ $customMusic['title'] }}</strong><audio controls preload="none" src="{{ $customMusic['url'] }}"></audio></div>
                            <label class="form-check small m-0"><input class="form-check-input" type="checkbox" name="remove_music" value="1"> Retirer</label>
                        </div>
                    @endif
                    <div class="row g-3 mt-1">
                        <div class="col-md-7">
                            <label class="form-label" for="music">{{ $customMusic ? 'Remplacer le morceau' : 'Fichier audio' }}</label>
                            <input type="file" class="form-control @error('music') is-invalid @enderror" id="music" name="music" accept="audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,.mp3,.m4a,.ogg">
                            <div class="form-text">MP3, M4A ou OGG · 8 Mo max · idéalement 30 à 90 secondes, il est joué en boucle.</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="music_title">Titre affiché</label>
                            <input class="form-control" id="music_title" name="music_title" value="{{ old('music_title', $customMusic['title'] ?? '') }}" maxlength="120" placeholder="Ex. Valse d’ouverture">
                        </div>
                        <div class="col-12">
                            <label class="form-check small"><input class="form-check-input @error('rights_confirmed') is-invalid @enderror" type="checkbox" name="rights_confirmed" value="1" @checked(old('rights_confirmed'))> Je certifie disposer des droits de diffusion de ce morceau (libre de droits ou licence acquise).</label>
                        </div>
                    </div>
                </div>
            @else
                <div class="upsell"><i class="bi bi-stars"></i><span>{{ $customMusicDeniedReason }} Avec un forfait payant actif, vous pouvez accueillir vos invités avec votre propre musique.</span><a href="{{ route('saas.plans') }}" class="ui-btn ui-btn-gold ui-btn-sm">Voir les forfaits</a></div>
            @endif

            <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-4">
                <button type="submit" class="ui-btn ui-btn-primary"><i class="bi bi-check2"></i>Enregistrer l’expérience</button>
            </div>
        </form>
    </section>

    <div class="modal fade" id="experiencePreview" tabindex="-1" aria-label="Aperçu de l’animation" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden">
                <div class="preview-stage">
                    @foreach($templates as $key => $tpl)
                        <div class="preview-slot" data-preview-slot="{{ $key }}" hidden>
                            <x-invitation.envelope :preview="true" :experience="\App\Support\InvitationExperience::preview($key, $organization, $previewMusic)" title="Gala annuel des partenaires" date="Samedi 24 octobre 2026 · 19:00" place="Hôtel Ivoire, Abidjan" guest="Marie Koffi" />
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">Aperçu avec vos couleurs et votre logo enregistrés.</small>
                    <div class="d-flex gap-2"><button type="button" class="ui-btn ui-btn-soft ui-btn-sm" data-preview-replay><i class="bi bi-arrow-counterclockwise"></i>Rejouer</button><button type="button" class="ui-btn ui-btn-primary ui-btn-sm" data-bs-dismiss="modal">Fermer</button></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('brandingForm');
        const preview = document.querySelector('[data-preview]');
        const isHex = (v) => /^#[0-9a-f]{6}$/i.test(v);
        const apply = (key, value) => {
            form.querySelector(`[data-color="${key}"]`).value = value;
            form.querySelector(`[data-hex="${key}"]`).value = value.toUpperCase();
            form.querySelector(`[data-hex="${key}"]`).classList.remove('text-danger');
            preview.style.setProperty(`--pv-${key}`, value);
        };
        form.querySelectorAll('[data-color]').forEach((input) => input.addEventListener('input', () => apply(input.dataset.color, input.value)));
        form.querySelectorAll('[data-hex]').forEach((input) => input.addEventListener('input', () => {
            let value = input.value.trim();
            if (value && value[0] !== '#') value = '#' + value;
            if (isHex(value)) apply(input.dataset.hex, value.toLowerCase());
            else input.classList.add('text-danger');
        }));
        form.querySelectorAll('[data-preset]').forEach((b) => b.addEventListener('click', () => {
            const [p, a] = b.dataset.preset.split(',');
            apply('primary', p); apply('accent', a);
        }));

        const name = form.querySelector('#brand_name');
        name.addEventListener('input', () => {
            const value = name.value.trim() || name.placeholder;
            document.querySelectorAll('[data-pv-name], [data-pv-name-card]').forEach((el) => el.textContent = value);
        });

        const logo = form.querySelector('[data-logo-input]');
        const drop = form.querySelector('[data-logo-drop]');
        ['dragenter', 'dragover'].forEach((t) => logo.addEventListener(t, () => drop.classList.add('is-over')));
        ['dragleave', 'drop'].forEach((t) => logo.addEventListener(t, () => drop.classList.remove('is-over')));
        logo.addEventListener('change', () => {
            const file = logo.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            form.querySelector('[data-logo-thumb]').innerHTML = `<img src="${url}" alt="">`;
            document.querySelector('[data-pv-logo]').innerHTML = `<img src="${url}" alt="">`;
            form.querySelector('[data-logo-name]').textContent = file.name;
        });

        form.addEventListener('reset', () => setTimeout(() => {
            form.querySelectorAll('[data-color]').forEach((input) => apply(input.dataset.color, input.value));
            name.dispatchEvent(new Event('input'));
        }));
    })();

    (() => {
        const form = document.getElementById('experienceForm');
        if (!form) return;
        const custom = form.querySelector('[data-custom-music]');
        form.querySelectorAll('input[name="music_source"]').forEach((input) => input.addEventListener('change', () => { if (custom) custom.hidden = input.value !== 'custom' || !input.checked; }));

        const modalEl = document.getElementById('experiencePreview');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        let current = null;
        const show = (template) => {
            modalEl.querySelectorAll('[data-preview-slot]').forEach((slot) => {
                const on = slot.dataset.previewSlot === template;
                slot.hidden = !on;
                const ie = slot.querySelector('[data-ie]');
                ie?.reset?.();
                if (on) current = ie;
            });
            modal.show();
        };
        document.querySelectorAll('[data-preview-template]').forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); show(b.dataset.previewTemplate); }));
        modalEl.querySelector('[data-preview-replay]').addEventListener('click', () => current?.reset?.());
        modalEl.addEventListener('hidden.bs.modal', () => modalEl.querySelectorAll('[data-ie]').forEach((ie) => ie.reset?.()));
    })();
</script>
@endpush
