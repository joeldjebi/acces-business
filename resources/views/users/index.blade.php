@extends('layouts.app')

@section('title', 'Utilisateurs')

@php
    $totalUsers = $users->count();
    $superAdmins = $users->where('role', 'super_admin')->count();
    $admins = $users->where('role', 'admin')->count();
    $managers = $users->where('role', 'manager')->count();
    $moderateurs = $users->where('role', 'moderateur')->count();

    $roleLabels = [
        'super_admin' => 'Super administrateur',
        'admin' => 'Administrateur',
        'manager' => 'Manager',
        'moderateur' => 'Modérateur',
    ];
@endphp

@push('styles')
<style>
    .ops-page {
        --ink: #171713;
        --text: #2c2a25;
        --muted: #746f65;
        --line: #dfd7cb;
        --panel: #fffefa;
        --panel-soft: #f8f4ec;
        --gold: #b98943;
        --green: #2e7b65;
        --blue: #315f83;
        --red: #a4514a;
        --shadow: 0 18px 45px rgba(39, 33, 25, 0.055);
        color: var(--text);
        max-width: 1480px;
        margin: 0 auto;
    }

    .ops-page *,
    .ops-page *::before,
    .ops-page *::after {
        min-width: 0;
    }

    .ops-head {
        align-items: flex-start;
        display: flex;
        gap: 20px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .ops-kicker {
        color: var(--gold);
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .ops-title {
        color: var(--ink);
        font-size: clamp(1.7rem, 2.5vw, 2.6rem);
        font-weight: 600;
        line-height: 1.08;
        margin: 6px 0 0;
    }

    .ops-copy {
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 10px 0 0;
        max-width: 720px;
    }

    .ops-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .ops-btn {
        align-items: center;
        border-radius: 14px;
        display: inline-flex;
        font-size: 0.88rem;
        font-weight: 600;
        gap: 8px;
        min-height: 44px;
        padding: 0 15px;
        text-decoration: none;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .ops-btn.primary {
        background: var(--ink);
        color: #fff;
    }

    .ops-btn.secondary {
        background: var(--panel);
        border-color: var(--line);
        color: var(--ink);
    }

    .role-strip {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        margin-bottom: 18px;
    }

    .role-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 17px;
    }

    .role-card span {
        color: var(--muted);
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .role-card strong {
        color: var(--ink);
        display: block;
        font-size: 1.8rem;
        font-weight: 600;
        line-height: 1;
        margin-top: 12px;
    }

    .users-panel {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .panel-head {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: flex;
        gap: 16px;
        justify-content: space-between;
        padding: 16px 18px;
    }

    .panel-head h2 {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .panel-head p {
        color: var(--muted);
        font-size: 0.8rem;
        margin: 3px 0 0;
    }

    .user-row {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: grid;
        gap: 16px;
        grid-template-columns: 52px minmax(230px, 1.2fr) minmax(220px, 1fr) minmax(150px, 0.65fr) minmax(150px, 0.65fr) 104px;
        padding: 16px 18px;
    }

    .user-row.user-head {
        background: rgba(248, 244, 236, 0.72);
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .user-row:last-child {
        border-bottom: 0;
    }

    .avatar {
        align-items: center;
        background: var(--ink);
        border-radius: 14px;
        color: #d8b978;
        display: flex;
        font-weight: 600;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .user-name {
        color: var(--ink);
        display: block;
        font-weight: 600;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .user-meta {
        color: var(--muted);
        display: block;
        font-size: 0.82rem;
        line-height: 1.45;
        margin-top: 5px;
        overflow-wrap: anywhere;
    }

    .role-pill {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.74rem;
        font-weight: 600;
        gap: 6px;
        justify-content: center;
        min-height: 30px;
        padding: 6px 11px;
        text-align: center;
    }

    .role-pill.super-admin {
        background: rgba(164, 81, 74, 0.13);
        color: var(--red);
    }

    .role-pill.admin {
        background: rgba(185, 137, 67, 0.15);
        color: #8a6128;
    }

    .role-pill.manager {
        background: rgba(49, 95, 131, 0.13);
        color: var(--blue);
    }

    .role-pill.moderateur {
        background: rgba(46, 123, 101, 0.12);
        color: var(--green);
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn-action {
        align-items: center;
        background: #fffaf6;
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--ink);
        display: inline-flex;
        height: 36px;
        justify-content: center;
        text-decoration: none;
        transition: border-color 0.2s ease, transform 0.2s ease;
        width: 36px;
    }

    .btn-action:hover {
        border-color: rgba(185, 137, 67, 0.55);
        color: var(--gold);
        transform: translateY(-1px);
    }

    .btn-action-delete {
        color: var(--red);
    }

    .empty-state {
        color: var(--muted);
        padding: 42px 20px;
        text-align: center;
    }

    @media (max-width: 1280px) {
        .role-strip {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .user-row {
            grid-template-columns: 52px minmax(220px, 1.2fr) minmax(180px, 1fr) minmax(132px, .65fr) 104px;
        }

        .user-row > :nth-child(5) {
            display: none;
        }
    }

    @media (max-width: 992px) {
        .ops-head {
            flex-direction: column;
        }

        .ops-actions {
            justify-content: flex-start;
        }

        .role-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .user-row,
        .user-row.user-head {
            grid-template-columns: 1fr;
        }

        .user-row.user-head {
            display: none;
        }

        .avatar {
            display: none;
        }

        .action-buttons {
            justify-content: flex-start;
        }
    }

    @media (max-width: 576px) {
        .role-strip {
            grid-template-columns: 1fr;
        }

        .ops-btn {
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="ops-page">
    <div class="ops-head">
        <div>
            <div class="ops-kicker">Gouvernance & accès</div>
            <h1 class="ops-title">Utilisateurs</h1>
            <p class="ops-copy">
                Administrez les rôles, les profils opérationnels et les permissions de votre console événementielle.
            </p>
        </div>
        <div class="ops-actions">
            <a href="{{ route('users.create') }}" class="ops-btn primary">
                <i class="bi bi-person-plus"></i>
                Nouvel utilisateur
            </a>
        </div>
    </div>

    <section class="role-strip">
        <div class="role-card">
            <span>Total</span>
            <strong>{{ $totalUsers }}</strong>
        </div>
        <div class="role-card">
            <span>Super admin</span>
            <strong>{{ $superAdmins }}</strong>
        </div>
        <div class="role-card">
            <span>Admins</span>
            <strong>{{ $admins }}</strong>
        </div>
        <div class="role-card">
            <span>Managers</span>
            <strong>{{ $managers }}</strong>
        </div>
        <div class="role-card">
            <span>Modérateurs</span>
            <strong>{{ $moderateurs }}</strong>
        </div>
    </section>

    <section class="users-panel">
        <div class="panel-head">
            <div>
                <h2>Annuaire opérationnel</h2>
                <p>Liste des comptes autorisés à intervenir sur la plateforme.</p>
            </div>
        </div>

        <div class="user-row user-head">
            <div>#</div>
            <div>Utilisateur</div>
            <div>Email</div>
            <div>Rôle</div>
            <div>Création</div>
            <div class="text-end">Actions</div>
        </div>

        @forelse($users as $account)
            <div class="user-row">
                <div class="avatar">{{ strtoupper(substr($account->name, 0, 1)) }}</div>
                <div>
                    <span class="user-name">{{ $account->name }}</span>
                    <span class="user-meta">ID {{ $account->id }}</span>
                </div>
                <div>
                    <span class="user-name" style="font-size: .92rem;">{{ $account->email }}</span>
                    <span class="user-meta">Compte {{ $account->created_at->diffForHumans() }}</span>
                </div>
                <div>
                    <span class="role-pill {{ str_replace('_', '-', $account->role) }}">
                        <i class="bi bi-{{ $account->role === 'super_admin' ? 'star-fill' : ($account->role === 'admin' ? 'shield-check' : ($account->role === 'manager' ? 'briefcase' : 'person-check')) }}"></i>
                        {{ $roleLabels[$account->role] ?? ucfirst(str_replace('_', ' ', $account->role)) }}
                    </span>
                </div>
                <div>
                    <span class="user-name" style="font-size: .92rem;">{{ $account->created_at->format('d/m/Y') }}</span>
                    <span class="user-meta">{{ $account->created_at->format('H:i') }}</span>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('users.edit', $account) }}" class="btn-action" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if(!$account->isSuperAdmin())
                        <form action="{{ route('users.destroy', $account) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action-delete" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="bi bi-inbox d-block mb-3" style="font-size: 2.6rem;"></i>
                Aucun utilisateur trouvé.
                <div class="mt-3">
                    <a href="{{ route('users.create') }}" class="ops-btn primary">
                        <i class="bi bi-person-plus"></i>
                        Créer un utilisateur
                    </a>
                </div>
            </div>
        @endforelse
    </section>
</div>
@endsection
