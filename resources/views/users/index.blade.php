@extends('layouts.app')

@section('title', 'Utilisateurs')

@php
    $totalUsers = $users->count();
    $quota = \App\Support\SaasUsage::forOrganization(auth()->user()->organization);
    $userLimit = $quota['limits']['users'] ?? null;
    $usersUsed = $quota['usage']['users'] ?? $totalUsers;
    $canCreateUser = $userLimit === null || $usersUsed < $userLimit;

    $roles = [
        'super_admin' => ['label' => 'Super administrateur', 'tone' => 'gold'],
        'admin' => ['label' => 'Administrateur', 'tone' => 'success'],
        'manager' => ['label' => 'Manager', 'tone' => 'info'],
        'moderateur' => ['label' => 'Modérateur', 'tone' => ''],
    ];
@endphp

@push('styles')
<style>
    .users-page { margin: 0 auto; max-width: 1240px; }
    .users-page *, .users-page *::before, .users-page *::after { min-width: 0; }
    .member { align-items: center; display: grid; gap: 16px; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr) 140px 84px; padding: 14px 22px; transition: background .2s; }
    .member + .member { border-top: 1px solid var(--app-line); }
    .member[hidden] { display: none; }
    .member:hover { background: rgba(0, 0, 0, .02); }
    .you { color: var(--app-muted); font-size: .78rem; font-weight: 500; margin-left: 6px; }
    .member-date { color: var(--app-text); font-size: .84rem; }
    .list-head { border-bottom: 1px solid var(--app-line); color: var(--app-muted); display: grid; font-size: .7rem; font-weight: 600; gap: 16px; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr) 140px 84px; padding: 10px 22px; text-transform: none; }
    .list-search { max-width: 300px; }

    @media (max-width: 767.98px) {
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
    <x-app.page-header title="Utilisateurs" :subtitle="$usersUsed . ' / ' . ($userLimit ?? '∞') . ' siège(s) utilisé(s) sur votre forfait'">
        <x-slot:actions>
            @if($canCreateUser)
                <a href="{{ route('users.create') }}" class="ui-btn ui-btn-primary"><i class="bi bi-person-plus"></i>Nouvel utilisateur</a>
            @else
                <span class="ui-btn ui-btn-primary is-disabled" title="Limite du forfait atteinte"><i class="bi bi-lock"></i>Limite atteinte</span>
            @endif
        </x-slot:actions>
    </x-app.page-header>


    @if(!$canCreateUser)
        <div class="alert alert-warning">Votre forfait autorise {{ number_format((int) $userLimit, 0, ',', ' ') }} utilisateur(s). Passez à un forfait supérieur pour ajouter un compte.</div>
    @endif

    <section class="ui-card" style="overflow: hidden">
        <div class="ui-card-head">
            <div><h2>Équipe</h2><p>{{ $totalUsers }} compte(s)</p></div>
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
                @php $role = $roles[$account->role] ?? ['label' => ucfirst(str_replace('_', ' ', $account->role)), 'tone' => '']; @endphp
                <div class="member" data-member="{{ \Illuminate\Support\Str::lower($account->name . ' ' . $account->email . ' ' . $role['label']) }}">
                    <div class="ui-person">
                        <span @class(['ui-avatar', 'gold' => $account->role === 'super_admin'])>{{ strtoupper(mb_substr($account->name, 0, 1)) }}</span>
                        <div>
                            <strong>{{ $account->name }}@if($account->is(auth()->user()))<span class="you">(vous)</span>@endif</strong>
                            <small>{{ $account->email }}</small>
                        </div>
                    </div>
                    <div class="col-role"><span class="ui-pill {{ $role['tone'] }}">{{ $role['label'] }}</span></div>
                    <div class="member-date col-hide">{{ $account->created_at->format('d/m/Y') }}</div>
                    <div class="ui-actions col-actions">
                        <a href="{{ $account->is(auth()->user()) ? route('profile.edit') : route('users.edit', $account) }}" class="ui-icon-btn" title="Modifier"><i class="bi bi-pencil"></i></a>
                        @if(!$account->isSuperAdmin())
                            <form action="{{ route('users.destroy', $account) }}" method="POST" data-confirm-title="Supprimer {{ $account->name }} ?" data-confirm="Ce compte n’aura plus accès à la console. Cette action est définitive.">
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
