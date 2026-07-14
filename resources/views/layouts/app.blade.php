<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #b98943;
            --sidebar-width: 280px;
            --sidebar-bg: #171713;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f3ee;
            color: #2c2a25;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            padding: 0;
            box-shadow: 10px 0 40px rgba(23, 23, 19, 0.14);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: transparent;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .sidebar-logo-icon {
            width: 45px;
            height: 45px;
            background: #c59a55;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: none;
            color: #171713;
        }

        .sidebar-logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        .sidebar-subtitle {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.56);
            margin-left: 57px;
            font-weight: 500;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0;
            margin: 0;
            flex: 1;
        }

        .sidebar-menu li {
            margin: 0.25rem 0.75rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            position: relative;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .sidebar-menu a i {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 0;
            background: #c59a55;
            border-radius: 0 4px 4px 0;
            transition: height 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(3px);
            padding-left: 1.4rem;
        }

        .sidebar-menu a:hover::before {
            height: 60%;
        }

        .sidebar-menu a.active {
            background: rgba(197, 154, 85, 0.16);
            color: white;
            font-weight: 600;
            box-shadow: none;
            border: 1px solid rgba(197, 154, 85, 0.28);
        }

        .sidebar-menu a.active::before {
            height: 70%;
        }

        .sidebar-menu a.active i {
            color: #d8b978;
            transform: none;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 1rem 1.5rem;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: transparent;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #c59a55;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .user-details {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 2px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 28px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.7), rgba(245, 243, 238, 0) 260px),
                #f5f3ee;
        }

        .navbar-custom {
            background: #fffefa;
            border: 1px solid #dfd7cb;
            box-shadow: 0 14px 36px rgba(39, 33, 25, 0.06);
            margin-bottom: 1.35rem;
            border-radius: 8px;
            padding: 0.9rem 1.25rem;
        }

        .navbar-custom .container-fluid {
            gap: 1rem;
            padding-left: 0;
            padding-right: 0;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            background: #171713;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.25rem;
        }

        .btn-primary {
            background: #171713;
            border: none;
        }

        .btn-primary:hover {
            background: #2c2a25;
        }

        .badge-role {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-super-admin {
            background-color: #dc3545;
            color: white;
        }

        .badge-admin {
            background-color: #fd7e14;
            color: white;
        }

        .badge-manager {
            background-color: #0dcaf0;
            color: white;
        }

        .badge-moderateur {
            background-color: #6c757d;
            color: white;
        }

        .text-primary {
            color: #b98943 !important;
        }

        .user-avatar-small {
            background: #c59a55 !important;
            border-radius: 8px !important;
            color: #171713 !important;
            box-shadow: none !important;
        }

        .navbar-custom h4 {
            color: #171713 !important;
            font-size: 1.05rem;
            font-weight: 600 !important;
            line-height: 1.25;
        }

        .navbar-custom small {
            color: #746f65 !important;
            display: block;
            margin-top: 2px;
            line-height: 1.3;
        }

        .navbar-user {
            min-width: 0;
        }

        .navbar-user-name,
        .navbar-user-email {
            display: block;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .navbar-custom {
                padding: 0.75rem 1rem;
            }

            .sidebar-header {
                padding: 1.5rem 1rem;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .sidebar-menu li {
            animation: slideIn 0.3s ease-out backwards;
        }

        .sidebar-menu li:nth-child(1) { animation-delay: 0.1s; }
        .sidebar-menu li:nth-child(2) { animation-delay: 0.2s; }
        .sidebar-menu li:nth-child(3) { animation-delay: 0.3s; }
        .sidebar-menu li:nth-child(4) { animation-delay: 0.4s; }
    </style>

    @stack('styles')
</head>
<body>
    @auth
    @php
        $currentOrganization = auth()->user()->organization;
        $organizationSettings = $currentOrganization?->settings ?? [];
        $organizationBranding = $organizationSettings['branding'] ?? [];
        $brandName = $organizationBranding['brand_name'] ?? $currentOrganization?->name ?? 'EventOps';
        $brandLogo = $currentOrganization?->logo ? \Illuminate\Support\Facades\Storage::url($currentOrganization->logo) : null;
        $isPlatformAdmin = auth()->user()->isPlatformAdmin();
    @endphp
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="sidebar-logo-icon" style="object-fit: cover;">
                @else
                    <div class="sidebar-logo-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                @endif
                <div class="sidebar-logo-text">{{ $brandName }}</div>
            </div>
            <div class="sidebar-subtitle">{{ $isPlatformAdmin ? 'Plateforme · Supervision' : ucfirst($currentOrganization?->plan ?? 'starter') . ' · Console SaaS' }}</div>
        </div>

        <ul class="sidebar-menu">
            @if($isPlatformAdmin)
            <li>
                <a href="{{ route('platform.dashboard') }}" class="{{ request()->routeIs('platform.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Vue plateforme</span>
                </a>
            </li>
            <li>
                <a href="{{ route('platform.organizations') }}" class="{{ request()->routeIs('platform.organizations') ? 'active' : '' }}">
                    <i class="bi bi-buildings"></i>
                    <span>Organisations</span>
                </a>
            </li>
            @else
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li>
                <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Événements</span>
                </a>
            </li>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isManager())
            <li>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Catégories d'événement</span>
                </a>
            </li>
            <li>
                <a href="{{ route('devises.index') }}" class="{{ request()->routeIs('devises.*') ? 'active' : '' }}">
                    <i class="bi bi-currency-exchange"></i>
                    <span>Devises</span>
                </a>
            </li>
            <li>
                <a href="{{ route('localisations.index') }}" class="{{ request()->routeIs('localisations.*') || request()->routeIs('countries.*') || request()->routeIs('cities.*') ? 'active' : '' }}">
                    <i class="bi bi-globe2"></i>
                    <span>Pays & villes</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->isSuperAdmin())
            <li>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li>
                <a href="{{ route('saas.plans') }}" class="{{ request()->routeIs('saas.plans') ? 'active' : '' }}">
                    <i class="bi bi-gem"></i>
                    <span>Plans SaaS</span>
                </a>
            </li>
            <li>
                <a href="{{ route('saas.billing') }}" class="{{ request()->routeIs('saas.billing') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Facturation</span>
                </a>
            </li>
            <li>
                <a href="{{ route('saas.branding') }}" class="{{ request()->routeIs('saas.branding') ? 'active' : '' }}">
                    <i class="bi bi-palette"></i>
                    <span>Branding</span>
                </a>
            </li>
            @endif
            @endif
        </ul>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                </div>
            </div>

            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="d-flex align-items-center justify-content-center text-white text-decoration-none p-2 rounded"
               style="background: rgba(255, 255, 255, 0.1); transition: all 0.3s ease;"
               onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'"
               onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
                <i class="bi bi-box-arrow-right me-2"></i>
                <span style="font-weight: 500;">Déconnexion</span>
            </a>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0" style="color: #1a1a1a;">
                        <i class="bi bi-{{ request()->routeIs('dashboard') ? 'speedometer2' : (request()->routeIs('users.*') ? 'people' : 'house') }} me-2 text-primary"></i>
                        @yield('title', 'Dashboard')
                    </h4>
                    <small class="text-muted">Console de gestion événementielle</small>
                </div>
                <div class="d-flex align-items-center gap-3 navbar-user">
                    <div class="text-end navbar-user">
                        <div class="navbar-user-name" style="color: #1a1a1a; font-weight: 500;">{{ auth()->user()->name }}</div>
                        <small class="text-muted navbar-user-email">{{ auth()->user()->email }}</small>
                    </div>
                    <div class="user-avatar-small" style="width: 42px; height: 42px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 17px; flex: 0 0 42px;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <div class="@auth container-fluid @else container @endauth">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                @if(session('invitation_token'))
                    <div class="mt-3">
                        <a href="{{ route('invitation.download', ['token' => session('invitation_token')]) }}" 
                           class="btn btn-sm btn-outline-light">
                            <i class="bi bi-download me-2"></i>Télécharger la carte d'invitation en PDF
                        </a>
                    </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    @auth
    </div>
    @endauth

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
