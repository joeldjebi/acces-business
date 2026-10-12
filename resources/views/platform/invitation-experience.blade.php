@extends('layouts.app')

@section('title', 'Expérience d’invitation')

@php
    $music = $settings['music']
        ? ['type' => 'file', 'url' => \App\Support\InvitationExperience::musicUrl($settings['music']), 'title' => $settings['music_title']]
        : ['type' => 'synth', 'url' => null, 'title' => 'Mélodie générée'];
    $defaultTemplate = old('default_template', $settings['default_template']);
@endphp

@push('styles')
<style>
    .pa-exp { margin: 0 auto; max-width: 1180px; }
    .pa-exp *, .pa-exp *::before, .pa-exp *::after { min-width: 0; }
    .tpl-list { display: grid; gap: 14px; }
    .tpl-row { align-items: start; display: grid; gap: 18px; grid-template-columns: 150px minmax(0, 1fr) auto; padding: 16px 18px; }
    .tpl-row + .tpl-row { border-top: 1px solid var(--app-line); }
    .tpl-thumb { align-items: center; border-radius: 12px; display: flex; height: 100px; justify-content: center; overflow: hidden; }
    .tpl-thumb--classic { background: #f4efe6; }
    .tpl-thumb--prestige { background: #0d0d0c; }
    .tpl-thumb--modern { background: #f6f4ef; }
    .tpl-thumb span { aspect-ratio: 1.45; border-radius: 4px; position: relative; width: 62%; }
    .tpl-thumb--classic span { background: linear-gradient(160deg, #efe4cf, #e4d4b6); box-shadow: 0 8px 16px -8px rgba(40, 30, 15, .45); }
    .tpl-thumb--classic span::before, .tpl-thumb--prestige span::before { clip-path: polygon(0 0, 100% 0, 50% 58%); content: ""; inset: 0; position: absolute; }
    .tpl-thumb--classic span::before { background: #e9dcc2; }
    .tpl-thumb--prestige span { background: #1b1a17; box-shadow: inset 0 0 0 1px rgba(216, 185, 120, .5); }
    .tpl-thumb--prestige span::before { background: #23211d; }
    .tpl-thumb--classic span::after, .tpl-thumb--prestige span::after { background: #b98943; border-radius: 50%; content: ""; height: 16px; left: 50%; position: absolute; top: 58%; transform: translate(-50%, -50%); width: 16px; }
    .tpl-thumb--modern span { aspect-ratio: .74; background: radial-gradient(120% 90% at 0% 0%, rgba(185, 137, 67, .55), transparent 60%), #171713; border-radius: 2px 6px 6px 2px; box-shadow: 0 8px 16px -8px rgba(0, 0, 0, .45); width: 28%; }
    .tpl-side { align-items: flex-end; display: flex; flex-direction: column; gap: 10px; }
    .default-pick { align-items: center; display: inline-flex; font-size: .8rem; gap: 6px; white-space: nowrap; }
    .music-now { align-items: center; background: var(--app-bg); border-radius: 14px; display: flex; gap: 14px; padding: 14px 16px; }
    .music-now > i { align-items: center; background: #171713; border-radius: 12px; color: #d8b978; display: flex; flex: 0 0 44px; font-size: 1.2rem; height: 44px; justify-content: center; }
    .music-now strong { color: var(--app-ink); display: block; font-size: .9rem; }
    .music-now small { color: var(--app-muted); font-size: .78rem; }
    .music-now audio { height: 32px; margin-top: 6px; width: 100%; }
    .preview-stage { background: #f4efe6; height: min(560px, 72vh); position: relative; }
    .preview-slot { inset: 0; position: absolute; }
    .grid-2 { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); }
    @media (max-width: 991.98px) { .grid-2 { grid-template-columns: 1fr; } .tpl-row { grid-template-columns: 1fr; } .tpl-side { align-items: flex-start; flex-direction: row; flex-wrap: wrap; } }
</style>
@endpush

@section('content')
<div class="pa-exp">
    <x-app.page-header kicker="Contenu" title="Expérience d’invitation" subtitle="Les animations d’ouverture et la musique proposées aux structures pour accueillir leurs invités." />

    <form method="POST" action="{{ route('platform.invitation-experience.update') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <div class="grid-2">
            <section class="ui-card">
                <div class="ui-card-head has-border">
                    <div><h2>Animations proposées</h2><p>Les structures choisissent parmi les animations actives.</p></div>
                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="enabled" value="1" id="exp-enabled" @checked(old('enabled', $settings['enabled']))>
                        <label class="form-check-label small fw-semibold" for="exp-enabled">Fonctionnalité active</label>
                    </div>
                </div>
                <div class="tpl-list">
                    @foreach($settings['templates'] as $key => $tpl)
                        <div class="tpl-row">
                            <div class="tpl-thumb tpl-thumb--{{ $key }}" aria-hidden="true"><span></span></div>
                            <div class="row g-2">
                                <div class="col-sm-5">
                                    <label class="form-label" for="tpl-name-{{ $key }}">Nom</label>
                                    <input class="form-control" id="tpl-name-{{ $key }}" name="templates[{{ $key }}][name]" value="{{ old("templates.$key.name", $tpl['name']) }}" maxlength="60">
                                </div>
                                <div class="col-sm-7">
                                    <label class="form-label" for="tpl-desc-{{ $key }}">Description</label>
                                    <input class="form-control" id="tpl-desc-{{ $key }}" name="templates[{{ $key }}][description]" value="{{ old("templates.$key.description", $tpl['description']) }}" maxlength="200">
                                </div>
                            </div>
                            <div class="tpl-side">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="templates[{{ $key }}][enabled]" value="1" id="tpl-on-{{ $key }}" @checked(old("templates.$key.enabled", $tpl['enabled']))>
                                    <label class="form-check-label small" for="tpl-on-{{ $key }}">Active</label>
                                </div>
                                <label class="default-pick"><input class="form-check-input m-0" type="radio" name="default_template" value="{{ $key }}" @checked($defaultTemplate === $key)> Par défaut</label>
                                <button type="button" class="ui-btn ui-btn-soft ui-btn-sm" data-preview-template="{{ $key }}"><i class="bi bi-play-circle"></i>Aperçu</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('default_template')<div class="text-danger small px-4 pb-3">{{ $message }}</div>@enderror
            </section>

            <section class="ui-card">
                <div class="ui-card-head has-border"><div><h2>Musique par défaut</h2><p>Jouée pour les structures qui gardent la musique de la plateforme.</p></div></div>
                <div class="ui-card-body">
                    <div class="music-now">
                        <i class="bi {{ $music['type'] === 'file' ? 'bi-vinyl' : 'bi-soundwave' }}"></i>
                        <div class="flex-fill">
                            <strong>{{ $music['title'] ?: 'Musique de la plateforme' }}</strong>
                            @if($music['type'] === 'file')
                                <audio controls preload="none" src="{{ $music['url'] }}"></audio>
                            @else
                                <small>Mélodie douce générée dans le navigateur : libre de droits, sans fichier à télécharger.</small>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="music">{{ $settings['music'] ? 'Remplacer par un autre fichier' : 'Remplacer par un fichier audio' }}</label>
                        <input type="file" class="form-control @error('music') is-invalid @enderror" id="music" name="music" accept="audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,.mp3,.m4a,.ogg">
                        @error('music')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">MP3, M4A ou OGG · 8 Mo max. Utilisez uniquement un morceau libre de droits ou sous licence.</div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="music_title">Titre affiché</label>
                        <input class="form-control" id="music_title" name="music_title" value="{{ old('music_title', $settings['music_title']) }}" maxlength="120" placeholder="Ex. Prélude d’ouverture">
                    </div>
                    @if($settings['music'])
                        <label class="form-check small mt-3"><input class="form-check-input" type="checkbox" name="remove_music" value="1"> Revenir à la mélodie générée</label>
                    @endif
                    <div class="upsell-note mt-3 small text-muted"><i class="bi bi-gem me-1"></i>La musique personnalisée des structures dépend de l’option « Musique perso. » de chaque <a href="{{ route('platform.plans') }}">plan</a> et d’un abonnement payé en cours.</div>
                </div>
            </section>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="ui-btn ui-btn-primary"><i class="bi bi-check2"></i>Enregistrer</button>
        </div>
    </form>

    <div class="modal fade" id="experiencePreview" tabindex="-1" aria-label="Aperçu de l’animation" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden">
                <div class="preview-stage">
                    @foreach(array_keys(\App\Support\InvitationExperience::TEMPLATES) as $key)
                        <div class="preview-slot" data-preview-slot="{{ $key }}" hidden>
                            <x-invitation.envelope :preview="true" :experience="\App\Support\InvitationExperience::preview($key, null, $music)" title="Gala annuel des partenaires" date="Samedi 24 octobre 2026 · 19:00" place="Hôtel Ivoire, Abidjan" guest="Marie Koffi" />
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">Aperçu aux couleurs par défaut.</small>
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
        const modalEl = document.getElementById('experiencePreview');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        let current = null;
        document.querySelectorAll('[data-preview-template]').forEach((b) => b.addEventListener('click', () => {
            modalEl.querySelectorAll('[data-preview-slot]').forEach((slot) => {
                const on = slot.dataset.previewSlot === b.dataset.previewTemplate;
                slot.hidden = !on;
                const ie = slot.querySelector('[data-ie]');
                ie?.reset?.();
                if (on) current = ie;
            });
            modal.show();
        }));
        modalEl.querySelector('[data-preview-replay]').addEventListener('click', () => current?.reset?.());
        modalEl.addEventListener('hidden.bs.modal', () => modalEl.querySelectorAll('[data-ie]').forEach((ie) => ie.reset?.()));
    })();
</script>
@endpush
