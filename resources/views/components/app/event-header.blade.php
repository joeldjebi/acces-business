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
        .ev-head { margin-bottom: 24px; }
        .ev-back { align-items: center; color: var(--app-blue); display: inline-flex; font-size: .86rem; gap: 4px; margin-bottom: 14px; text-decoration: none; }
        .ev-back:hover { color: var(--app-blue); text-decoration: underline; }
        .ev-head-main { align-items: center; display: flex; flex-wrap: wrap; gap: 18px 22px; }
        /* Vignette façon icône Calendrier d'Apple : mois en rouge, jour en grand */
        .ev-thumb { align-items: center; background: #fff; border-radius: 16px; box-shadow: 0 0 0 1px var(--app-line), 0 4px 14px rgba(0, 0, 0, .06); color: var(--app-ink); display: flex; flex: 0 0 72px; flex-direction: column-reverse; height: 72px; justify-content: center; overflow: hidden; position: relative; }
        .ev-thumb img { height: 100%; inset: 0; object-fit: cover; position: absolute; width: 100%; }
        .ev-thumb strong, .ev-thumb span { position: relative; z-index: 1; }
        .ev-thumb strong { font-size: 1.85rem; font-weight: 300; letter-spacing: -.04em; line-height: 1; }
        .ev-thumb span { color: #ff3b30; font-size: .68rem; font-weight: 600; margin-bottom: 2px; text-transform: capitalize; }
        .ev-head-text { flex: 1 1 320px; min-width: 0; }
        .ev-head-meta { align-items: center; display: flex; flex-wrap: wrap; gap: 6px; }
        .ev-head h1 { color: var(--app-ink); font-size: clamp(1.6rem, 2.8vw, 2.1rem); font-weight: 700; letter-spacing: -.035em; line-height: 1.1; margin: 8px 0 6px; overflow-wrap: anywhere; }
        .ev-facts { color: var(--app-muted); display: flex; flex-wrap: wrap; font-size: .9rem; gap: 4px 18px; }
        .ev-facts i { margin-right: 6px; }
        .ev-head-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
        /* Onglets : contrôle segmenté iOS */
        .ev-tabs { background: var(--app-fill); border-radius: 10px; display: flex; gap: 2px; margin-top: 22px; max-width: 100%; overflow-x: auto; padding: 2px; scrollbar-width: none; width: fit-content; }
        .ev-tabs::-webkit-scrollbar { display: none; }
        .ev-tabs a { align-items: center; border-radius: 8px; color: var(--app-ink); display: inline-flex; font-size: .84rem; font-weight: 500; gap: 7px; padding: 6px 14px; text-decoration: none; transition: background .15s; white-space: nowrap; }
        .ev-tabs a:hover { color: var(--app-ink); }
        .ev-tabs a.is-active { background: #fff; box-shadow: 0 3px 8px rgba(0, 0, 0, .12), 0 3px 1px rgba(0, 0, 0, .04); }
        .ev-tabs a i { color: var(--app-muted); font-size: .9rem; }
        .ev-tabs a.is-active i { color: var(--app-gold); }
        .ev-tabs .count { color: var(--app-muted); font-size: .74rem; font-variant-numeric: tabular-nums; }
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
            @if($image)
                <img src="{{ $image }}" alt="">
            @else
                <strong>{{ $start?->format('d') ?? '--' }}</strong>
                <span>{{ $start?->translatedFormat('M') ?? '—' }}</span>
            @endif
        </div>
        <div class="ev-head-text">
            <div class="ev-head-meta">
                <span class="ui-pill dot {{ $statusTones[$event->statut] ?? '' }}">{{ $statusLabels[$event->statut] ?? ucfirst($event->statut) }}</span>
                @if($event->category)<span class="ui-pill">{{ $event->category->libelle }}</span>@endif
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
