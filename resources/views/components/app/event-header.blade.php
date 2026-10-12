@props(['event', 'active' => 'show', 'subtitle' => null])

@php
    $user = auth()->user();
    $canManageAccess = $user && ($user->isSuperAdmin() || $user->isAdmin());
    $statusLabels = ['publie' => 'Publié', 'brouillon' => 'Brouillon', 'annule' => 'Annulé', 'termine' => 'Terminé', 'reporte' => 'Reporté', 'archive' => 'Archivé'];
    $statusTones = ['publie' => 'success', 'brouillon' => 'warning', 'annule' => 'danger', 'termine' => 'info', 'reporte' => 'warning', 'archive' => 'info'];
    $start = $event->date_debut?->locale('fr');
    $image = \App\Support\EventMedia::storageUrl($event->image);
    $time = fn ($value) => $value ? \Illuminate\Support\Str::substr($value, 0, 5) : null;
    $tabs = collect([
        ['key' => 'show', 'route' => 'events.show', 'icon' => 'bi-grid-1x2', 'label' => 'Aperçu', 'manage' => false],
        ['key' => 'send-link', 'route' => 'events.send-link', 'icon' => 'bi-send', 'label' => 'Invitations', 'manage' => true],
        ['key' => 'registrations', 'route' => 'events.registrations', 'icon' => 'bi-people', 'label' => 'Inscriptions', 'manage' => true, 'count' => fn () => $event->registrations()->count()],
        ['key' => 'confirmed-attendees', 'route' => 'events.confirmed-attendees', 'icon' => 'bi-person-check', 'label' => 'Présences', 'manage' => true],
        ['key' => 'check-in', 'route' => 'events.check-in', 'icon' => 'bi-qr-code-scan', 'label' => 'Check-in', 'manage' => true],
    ])->filter(fn ($tab) => !$tab['manage'] || $canManageAccess);
@endphp

@once
    @push('styles')
    <style>
        .ev-head { margin-bottom: 22px; }
        .ev-back { align-items: center; color: var(--app-muted); display: inline-flex; font-size: .82rem; font-weight: 500; gap: 6px; margin-bottom: 14px; text-decoration: none; transition: color .2s; }
        .ev-back:hover { color: var(--app-ink); }
        .ev-back i { transition: transform .3s var(--app-ease); }
        .ev-back:hover i { transform: translateX(-3px); }
        .ev-head-main { align-items: center; display: flex; flex-wrap: wrap; gap: 18px 22px; }
        .ev-thumb { align-items: center; background: radial-gradient(100% 100% at 0% 0%, rgba(185, 137, 67, .5), transparent 60%), #171713; border-radius: 18px; box-shadow: 0 14px 30px -16px rgba(23, 23, 19, .6); color: #fff; display: flex; flex: 0 0 76px; flex-direction: column; height: 76px; justify-content: center; overflow: hidden; position: relative; }
        .ev-thumb img { height: 100%; inset: 0; object-fit: cover; position: absolute; width: 100%; }
        .ev-thumb.has-img::after { background: linear-gradient(0deg, rgba(10, 10, 9, .7), transparent 70%); content: ""; inset: 0; position: absolute; }
        .ev-thumb strong, .ev-thumb span { position: relative; z-index: 1; }
        .ev-thumb strong { font-size: 1.5rem; font-weight: 700; letter-spacing: -.04em; line-height: 1; }
        .ev-thumb span { color: #d8b978; font-size: .66rem; font-weight: 700; letter-spacing: .08em; margin-top: 3px; text-transform: uppercase; }
        .ev-thumb.has-img { justify-content: flex-end; padding-bottom: 8px; }
        .ev-head-text { flex: 1 1 320px; min-width: 0; }
        .ev-head-meta { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
        .ev-head h1 { color: var(--app-ink); font-size: clamp(1.5rem, 2.6vw, 2.15rem); font-weight: 700; letter-spacing: -.035em; line-height: 1.1; margin: 8px 0 6px; overflow-wrap: anywhere; }
        .ev-facts { color: var(--app-muted); display: flex; flex-wrap: wrap; font-size: .86rem; gap: 4px 18px; }
        .ev-facts i { color: var(--app-gold); margin-right: 6px; }
        .ev-head-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
        .ev-tabs { border-bottom: 1px solid var(--app-line); display: flex; gap: 4px; margin-top: 22px; overflow-x: auto; scrollbar-width: none; }
        .ev-tabs::-webkit-scrollbar { display: none; }
        .ev-tabs a { align-items: center; border-bottom: 2px solid transparent; color: var(--app-muted); display: inline-flex; font-size: .875rem; font-weight: 600; gap: 8px; margin-bottom: -1px; padding: 10px 12px 12px; text-decoration: none; transition: color .2s, border-color .2s; white-space: nowrap; }
        .ev-tabs a:hover { color: var(--app-ink); }
        .ev-tabs a.is-active { border-color: var(--app-ink); color: var(--app-ink); }
        .ev-tabs a i { font-size: .95rem; }
        .ev-tabs a.is-active i { color: var(--app-gold); }
        .ev-tabs .count { background: rgba(23, 23, 19, .07); border-radius: 99px; font-size: .7rem; font-variant-numeric: tabular-nums; padding: 1px 7px; }
        .ev-tabs a.is-active .count { background: var(--app-ink); color: #fff; }
        @media (max-width: 575.98px) {
            .ev-thumb { flex-basis: 60px; height: 60px; }
            .ev-head-actions { width: 100%; }
            .ev-head-actions > .ui-btn { flex: 1; }
        }
    </style>
    @endpush
    @push('scripts')
    <script>
        document.querySelectorAll('.ev-tabs').forEach((nav) => {
            const active = nav.querySelector('.is-active');
            if (active && nav.scrollWidth > nav.clientWidth) {
                nav.scrollLeft = active.offsetLeft - (nav.clientWidth - active.offsetWidth) / 2;
            }
        });
    </script>
    @endpush
@endonce

<header class="ev-head">
    <a href="{{ route('events.index') }}" class="ev-back"><i class="bi bi-arrow-left"></i>Événements</a>
    <div class="ev-head-main">
        <div @class(['ev-thumb', 'has-img' => $image])>
            @if($image)<img src="{{ $image }}" alt="">@endif
            <strong>{{ $start?->format('d') ?? '--' }}</strong>
            <span>{{ $start?->translatedFormat('M') ?? '—' }}</span>
        </div>
        <div class="ev-head-text">
            <div class="ev-head-meta">
                <span class="ui-pill dot {{ $statusTones[$event->statut] ?? '' }}">{{ $statusLabels[$event->statut] ?? ucfirst($event->statut) }}</span>
                @if($event->category)<span class="ui-pill">{{ $event->category->libelle }}</span>@endif
                @if($event->visibilite)<span class="ui-pill"><i class="bi bi-eye"></i>{{ $event->visibilite->libelle }}</span>@endif
            </div>
            <h1>{{ $event->titre }}</h1>
            <div class="ev-facts">
                <span><i class="bi bi-calendar3"></i>{{ $start ? $start->translatedFormat('l d F Y') : 'Date à confirmer' }}{{ $time($event->heure_debut) ? ' · ' . $time($event->heure_debut) : '' }}{{ $time($event->heure_fin) ? ' – ' . $time($event->heure_fin) : '' }}</span>
                <span><i class="bi bi-geo-alt"></i>{{ collect([$event->lieu, $event->ville])->filter()->implode(', ') ?: 'Lieu à confirmer' }}</span>
                @if($subtitle)<span>{{ $subtitle }}</span>@endif
            </div>
        </div>
        @isset($actions)
            <div class="ev-head-actions">{{ $actions }}</div>
        @endisset
    </div>
    @if($tabs->count() > 1)
        <nav class="ev-tabs" aria-label="Sections de l’événement">
            @foreach($tabs as $tab)
                <a href="{{ route($tab['route'], $event) }}" @class(['is-active' => $active === $tab['key']]) @if($active === $tab['key']) aria-current="page" @endif>
                    <i class="bi {{ $tab['icon'] }}"></i>{{ $tab['label'] }}
                    @isset($tab['count'])<span class="count">{{ ($tab['count'])() }}</span>@endisset
                </a>
            @endforeach
        </nav>
    @endif
</header>
