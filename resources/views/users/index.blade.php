@extends('layouts.app')

@section('title', 'Utilisateurs')

@php
    $totalUsers = $users->count();
    $quota = \App\Support\SaasUsage::forOrganization(auth()->user()->organization);
    $userLimit = $quota['limits']['users'] ?? null;
    $usersUsed = $quota['usage']['users'] ?? $totalUsers;
    $canCreateUser = $userLimit === null || $usersUsed < $userLimit;
    $usagePct = $userLimit ? min(100, round($usersUsed / $userLimit * 100)) : 0;

    $roles = [
        'super_admin' => ['label' => 'Super administrateur', 'short' => 'Super admin', 'icon' => 'bi-star-fill', 'tone' => 'gold', 'description' => 'Accès complet, abonnement et utilisateurs.'],
        'admin' => ['label' => 'Administrateur', 'short' => 'Admins', 'icon' => 'bi-shield-check', 'tone' => 'success', 'description' => 'Événements, invitations, inscriptions et check-in.'],
        'manager' => ['label' => 'Manager', 'short' => 'Managers', 'icon' => 'bi-briefcase', 'tone' => 'info', 'description' => 'Création et suivi des événements.'],
        'moderateur' => ['label' => 'Modérateur', 'short' => 'Modérateurs', 'icon' => 'bi-person-check', 'tone' => '', 'description' => 'Consultation et suivi opérationnel.'],
    ];
    $roleCounts = $users->countBy('role');
@endphp

@push('styles')
<style>
    .users-page { margin: 0 auto; max-width: 1240px; }
    .users-page *, .users-page *::before, .users-page *::after { min-width: 0; }
    .quota-note { align-items: center; background: #fbf4e8; border: 1px solid rgba(185, 137, 67, .28); border-radius: 14px; color: #7a5727; display: flex; font-size: .9rem; gap: 10px; margin-bottom: 18px; padding: 12px 16px; }

    .users-top { display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr) 300px; margin-bottom: 20px; }
    .roles { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .role { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); padding: 16px; }
    .role-top { align-items: center; display: flex; justify-content: space-between; }
    .role-top span { color: var(--app-muted); font-size: .78rem; font-weight: 500; }
    .role-top i { font-size: .9rem; }
    .role strong { color: var(--app-ink); display: block; font-size: 1.6rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.04em; line-height: 1; margin-top: 10px; }
    .role small { color: var(--app-muted); display: block; font-size: .72rem; line-height: 1.4; margin-top: 6px; }
    .seats { background: #141412; border-radius: var(--app-radius); color: #fff; padding: 18px 20px; position: relative; overflow: hidden; }
    .seats::before { background: radial-gradient(circle at 100% 0%, rgba(185, 137, 67, .45), transparent 60%); content: ""; inset: 0; position: absolute; }
    .seats > * { position: relative; }
    .seats span { color: rgba(255, 255, 255, .6); font-size: .78rem; }
    .seats strong { display: block; font-size: 1.9rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.04em; line-height: 1; margin: 10px 0 12px; }
    .seats strong small { color: rgba(255, 255, 255, .5); font-size: 1rem; font-weight: 600; }
    .seats .ui-meter { background: rgba(255, 255, 255, .12); }
    .seats .ui-meter span { background: #d8b978; }
    .seats p { color: rgba(255, 255, 255, .55); font-size: .76rem; margin: 10px 0 0; }

    .member { align-items: center; display: grid; gap: 16px; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr) 140px 84px; padding: 14px 22px; transition: background .2s; }
    .member + .member { border-top: 1px solid var(--app-line); }
    .member[hidden] { display: none; }
    .member:hover { background: rgba(23, 23, 19, .02); }
    .member .ui-avatar { flex-basis: 42px; font-size: .9rem; height: 42px; width: 42px; }
    .you { background: var(--app-ink); border-radius: 99px; color: #fff; font-size: .62rem; font-weight: 700; letter-spacing: .04em; margin-left: 6px; padding: 2px 7px; text-transform: uppercase; vertical-align: 2px; }
    .member-date { color: var(--app-text); font-size: .84rem; }
    .member-date small { color: var(--app-muted); display: block; font-size: .76rem; }
    .list-head { border-bottom: 1px solid var(--app-line); color: var(--app-muted); display: grid; font-size: .7rem; font-weight: 600; gap: 16px; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr) 140px 84px; letter-spacing: .06em; padding: 10px 22px; text-transform: uppercase; }
    .list-search { max-width: 300px; }

    @media (max-width: 1099.98px) { .users-top { grid-template-columns: 1fr; } }
    @media (max-width: 767.98px) {
        .roles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .list-head { display: none; }
        .member { grid-template-columns: minmax(0, 1fr) auto; }
        .member .col-hide { display: none; }
        .member .col-role { grid-column: 1; }
        .member .col-actions { grid-column: 2; grid-row: 1 / span 2; }
        .list-search { max-width: none; }
    }
</style>
@endpush

@section('content')
<div class="users-page">
    <x-app.page-header kicker="Gouvernance & accès" title="Utilisateurs" subtitle="Invitez votre équipe et attribuez à chacun le bon niveau d’accès.">
        <x-slot:actions>
            @if($canCreateUser)
                <a href="{{ route('users.create') }}" class="ui-btn ui-btn-primary"><i class="bi bi-person-plus"></i>Nouvel utilisateur</a>
            @else
                <span class="ui-btn ui-btn-primary is-disabled" title="Limite du forfait atteinte"><i class="bi bi-lock"></i>Limite atteinte</span>
            @endif
        </x-slot:actions>
    </x-app.page-header>

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if(!$canCreateUser)
        <div class="quota-note"><i class="bi bi-info-circle"></i>Votre forfait autorise {{ number_format((int) $userLimit, 0, ',', ' ') }} utilisateur(s). Passez à un forfait supérieur pour ajouter un compte.</div>
    @endif

    <div class="users-top">
        <section class="roles">
            @foreach($roles as $key => $role)
                <article class="role">
                    <div class="role-top"><span>{{ $role['short'] }}</span><span class="ui-pill {{ $role['tone'] }}" style="padding: 5px 7px"><i class="bi {{ $role['icon'] }}"></i></span></div>
                    <strong>{{ $roleCounts->get($key, 0) }}</strong>
                    <small>{{ $role['description'] }}</small>
                </article>
            @endforeach
        </section>
        <aside class="seats">
            <span>Sièges utilisés</span>
            <strong>{{ $usersUsed }} <small>/ {{ $userLimit ?? '∞' }}</small></strong>
            <div class="ui-meter"><span style="width: {{ $userLimit ? $usagePct : 100 }}%"></span></div>
            <p>{{ $userLimit ? max(0, $userLimit - $usersUsed) . ' siège(s) disponible(s) sur votre forfait.' : 'Utilisateurs illimités sur votre forfait.' }}</p>
        </aside>
    </div>

    <section class="ui-card" style="overflow: hidden">
        <div class="ui-card-head">
            <div><h2>Équipe</h2><p>{{ $totalUsers }} compte(s) autorisé(s) à intervenir sur la console.</p></div>
            @if($totalUsers > 5)
                <label class="ui-search list-search mb-0 ui-toolbar">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control" placeholder="Filtrer l’équipe…" data-member-filter aria-label="Filtrer l’équipe">
                </label>
            @endif
        </div>

        @if($users->isNotEmpty())
            <div class="list-head"><div>Membre</div><div>Rôle</div><div>Ajouté le</div><div></div></div>
            @foreach($users as $account)
                @php $role = $roles[$account->role] ?? ['label' => ucfirst(str_replace('_', ' ', $account->role)), 'icon' => 'bi-person', 'tone' => '']; @endphp
                <div class="member" data-member="{{ \Illuminate\Support\Str::lower($account->name . ' ' . $account->email . ' ' . $role['label']) }}">
                    <div class="ui-person">
                        <span @class(['ui-avatar', 'gold' => $account->role === 'super_admin'])>{{ strtoupper(mb_substr($account->name, 0, 1)) }}</span>
                        <div>
                            <strong>{{ $account->name }}@if($account->is(auth()->user()))<span class="you">Vous</span>@endif</strong>
                            <small>{{ $account->email }}</small>
                        </div>
                    </div>
                    <div class="col-role"><span class="ui-pill {{ $role['tone'] }}"><i class="bi {{ $role['icon'] }}"></i>{{ $role['label'] }}</span></div>
                    <div class="member-date col-hide">{{ $account->created_at->format('d/m/Y') }}<small>{{ $account->created_at->locale('fr')->diffForHumans() }}</small></div>
                    <div class="ui-actions col-actions">
                        <a href="{{ route('users.edit', $account) }}" class="ui-icon-btn" title="Modifier"><i class="bi bi-pencil"></i></a>
                        @if(!$account->isSuperAdmin())
                            <form action="{{ route('users.destroy', $account) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ui-icon-btn danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
            <div class="ui-empty d-none" data-member-empty><i class="bi bi-search"></i><strong>Aucun membre trouvé</strong>Essayez un autre nom ou email.</div>
        @else
            <div class="ui-empty">
                <i class="bi bi-people"></i>
                <strong>Aucun utilisateur</strong>Invitez votre premier collaborateur.
                @if($canCreateUser)<div class="mt-3"><a href="{{ route('users.create') }}" class="ui-btn ui-btn-primary"><i class="bi bi-person-plus"></i>Créer un utilisateur</a></div>@endif
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const input = document.querySelector('[data-member-filter]');
        if (!input) return;
        const rows = [...document.querySelectorAll('[data-member]')];
        const empty = document.querySelector('[data-member-empty]');
        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            let visible = 0;
            rows.forEach(row => {
                const match = !q || row.dataset.member.includes(q);
                row.hidden = !match;
                visible += match;
            });
            empty.classList.toggle('d-none', visible > 0);
        });
    })();
</script>
@endpush
