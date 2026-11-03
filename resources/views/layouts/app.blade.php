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

    <style>
        /* Système de design inspiré d'Apple (couleurs système, SF Pro, contrôles iOS/macOS) */
        :root {
            --primary-color: #0071e3;
            --sidebar-width: 260px;
            --sidebar-bg: #f5f5f7;
            --app-ink: #1d1d1f;
            --app-text: #1d1d1f;
            --app-muted: #6e6e73;
            --app-line: rgba(0, 0, 0, .08);
            --app-field-line: #d2d2d7;
            --app-panel: #ffffff;
            --app-bg: #ffffff;
            --app-soft: #f5f5f7;
            --app-fill: rgba(118, 118, 128, .12);
            --app-gold: #0071e3;
            --app-gold-soft: rgba(0, 113, 227, .12);
            --app-gold-deep: #0066cc;
            --app-green: #248a3d;
            --app-red: #d70015;
            --app-blue: #0066cc;
            --app-orange: #c93400;
            --app-radius: 18px;
            --app-shadow: none;
            --app-ease: cubic-bezier(.25, .1, .25, 1);
            --bs-body-font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", Helvetica, Arial, sans-serif;
            --bs-border-radius: 12px;
            --bs-link-color-rgb: 0, 102, 204;
            --bs-link-hover-color-rgb: 0, 102, 204;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--app-bg);
            color: var(--app-text);
            font-family: var(--bs-body-font-family);
            font-size: 15px;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -.01em;
        }
        h1, h2, h3, h4, h5, h6 { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Helvetica Neue", Helvetica, Arial, sans-serif; }
        a { color: var(--app-blue); }
        a:hover { color: var(--app-blue); }

        ::selection { background: rgba(0, 113, 227, .22); color: inherit; }

        /* Barre latérale façon macOS */
        .sidebar {
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            backdrop-filter: saturate(180%) blur(20px);
            background: rgba(245, 245, 247, .92);
            border-right: 1px solid var(--app-line);
            color: var(--app-ink);
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
            left: 0;
            overflow-x: hidden;
            overflow-y: auto;
            position: fixed;
            top: 0;
            width: var(--sidebar-width);
            z-index: 1040;
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, .15) transparent;
        }

        .sidebar-header { padding: 20px 18px 10px; }
        .sidebar-logo { align-items: center; color: var(--app-ink); display: flex; gap: 11px; min-width: 0; text-decoration: none; }
        .sidebar-logo:hover { color: var(--app-ink); }
        .sidebar-logo > span:last-child { min-width: 0; }
        .sidebar-logo-icon {
            align-items: center;
            background: var(--app-ink);
            border-radius: 9px;
            color: #fff;
            display: flex;
            flex: 0 0 34px;
            font-size: 16px;
            height: 34px;
            justify-content: center;
            width: 34px;
        }
        .sidebar-logo-text { font-size: .95rem; font-weight: 600; letter-spacing: -.02em; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sidebar-subtitle { color: var(--app-muted); font-size: .74rem; margin-top: 1px; }

        .sidebar-menu { flex: 1; list-style: none; margin: 0; padding: 4px 10px 16px; }
        .sidebar-menu .sidebar-section { color: var(--app-muted); font-size: .72rem; font-weight: 600; letter-spacing: 0; margin: 0; padding: 16px 10px 6px; }
        .sidebar-menu li { margin: 1px 0; }
        .sidebar-menu a {
            align-items: center;
            border-radius: 8px;
            color: var(--app-ink);
            display: flex;
            font-size: .875rem;
            font-weight: 400;
            gap: 10px;
            padding: 7px 10px;
            position: relative;
            text-decoration: none;
            transition: background .15s;
        }
        .sidebar-menu a i { color: var(--app-gold); font-size: 1rem; text-align: center; width: 18px; }
        .sidebar-menu a:hover { background: rgba(0, 0, 0, .05); color: var(--app-ink); }
        .sidebar-menu a.active { background: rgba(0, 0, 0, .08); color: var(--app-ink); font-weight: 500; }

        .sidebar-footer { border-top: 1px solid var(--app-line); padding: 10px; }
        .user-info { align-items: center; border-radius: 10px; display: flex; gap: 10px; padding: 6px; }
        .user-link { align-items: center; border-radius: 10px; color: inherit; display: flex; flex: 1; gap: 10px; min-width: 0; text-decoration: none; }
        .user-link:hover { color: inherit; }
        .user-link:hover .user-name { color: var(--app-blue); }
        .user-avatar {
            align-items: center;
            background: #8e8e93;
            border-radius: 50%;
            color: #fff;
            display: flex;
            flex-shrink: 0;
            font-size: .82rem;
            font-weight: 600;
            height: 32px;
            justify-content: center;
            width: 32px;
        }
        .user-details { flex: 1; min-width: 0; }
        .user-name { color: var(--app-ink); font-size: .84rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-role { color: var(--app-muted); font-size: .72rem; margin-top: 1px; }
        .logout-btn {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 8px;
            color: var(--app-muted);
            display: flex;
            flex-shrink: 0;
            height: 32px;
            justify-content: center;
            transition: background .15s, color .15s;
            width: 32px;
        }
        .logout-btn:hover { background: rgba(0, 0, 0, .06); color: var(--app-ink); }

        /* Contenu */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; padding: 0 36px 48px; }
        .app-topbar {
            align-items: center;
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            background: rgba(255, 255, 255, .8);
            border-bottom: 1px solid transparent;
            display: flex;
            gap: 14px;
            height: 56px;
            margin: 0 -36px 24px;
            padding: 0 36px;
            position: sticky;
            top: 0;
            transition: border-color .3s;
            z-index: 1030;
        }
        .app-topbar.is-scrolled { border-bottom-color: var(--app-line); }
        .crumbs { align-items: center; color: var(--app-muted); display: flex; font-size: .84rem; gap: 6px; min-width: 0; }
        .crumbs strong { color: var(--app-ink); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .crumbs i { font-size: .62rem; opacity: .6; }
        .topbar-actions { align-items: center; display: flex; gap: 10px; margin-left: auto; }
        .topbar-btn {
            align-items: center;
            background: var(--app-gold);
            border-radius: 980px;
            color: #fff;
            display: inline-flex;
            font-size: .82rem;
            font-weight: 400;
            gap: 6px;
            height: 32px;
            padding: 0 14px;
            text-decoration: none;
            transition: background .15s;
        }
        .topbar-btn:hover { background: #0077ed; color: #fff; }
        .topbar-avatar {
            align-items: center;
            background: #8e8e93;
            border: 0;
            border-radius: 50%;
            color: #fff;
            display: flex;
            flex: 0 0 32px;
            font-size: .8rem;
            font-weight: 600;
            height: 32px;
            justify-content: center;
            width: 32px;
        }
        .menu-toggle {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 8px;
            color: var(--app-gold);
            display: none;
            flex-shrink: 0;
            font-size: 1.35rem;
            height: 36px;
            justify-content: center;
            width: 36px;
        }
        .sidebar-backdrop { background: rgba(0, 0, 0, .3); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity .3s; z-index: 1035; }

        /* Bootstrap aux couleurs Apple */
        .btn { border-radius: 980px; font-weight: 400; }
        .btn-primary, .btn-dark { background: var(--app-gold); border-color: var(--app-gold); }
        .btn-primary:hover, .btn-dark:hover { background: #0077ed; border-color: #0077ed; }
        .btn-secondary, .btn-outline-secondary { background: var(--app-fill); border-color: transparent; color: var(--app-ink); }
        .form-label { color: var(--app-ink); font-size: .82rem; font-weight: 500; margin-bottom: 6px; }
        .form-label .text-muted, .form-label .opt { font-weight: 400; }
        .form-control, .form-select { background-color: #fff; border: 1px solid var(--app-field-line); border-radius: 12px; box-shadow: none; color: var(--app-ink); font-size: .9rem; line-height: 1.4; min-height: 42px; padding: 9px 13px; transition: border-color .15s, box-shadow .15s; }
        .form-select { padding-right: 36px; background-position: right 13px center; background-size: 13px 10px; }
        .form-control:hover, .form-select:hover { border-color: #b9b9be; }
        .form-control:focus, .form-select:focus { border-color: var(--app-gold); box-shadow: 0 0 0 4px rgba(0, 113, 227, .18); outline: 0; }
        .form-control::placeholder { color: #86868b; }
        .form-control[readonly], .form-control:disabled, .form-select:disabled { background-color: var(--app-soft); color: var(--app-muted); }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .form-control[type=file] { padding: 7px 9px; }
        .form-control[type=file]::file-selector-button { background: var(--app-fill); border: 0; border-radius: 8px; color: var(--app-ink); font-weight: 500; margin: 0 10px 0 0; padding: 5px 11px; }
        .form-control-sm, .form-select-sm { font-size: .82rem; min-height: 34px; padding-bottom: 5px; padding-top: 5px; }
        .form-text { color: var(--app-muted); font-size: .78rem; margin-top: 6px; }
        .invalid-feedback { color: var(--app-red); font-size: .78rem; }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #ff3b30; }
        .form-control.is-invalid:focus, .form-select.is-invalid:focus { box-shadow: 0 0 0 4px rgba(255, 59, 48, .16); }
        .form-check-input { border-color: #c7c7cc; }
        .form-check-input:focus { border-color: var(--app-gold); box-shadow: 0 0 0 4px rgba(0, 113, 227, .18); }
        .form-check-input:checked { background-color: var(--app-gold); border-color: var(--app-gold); }
        /* Interrupteur iOS */
        .form-switch .form-check-input { background-color: #e9e9eb; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3.4' fill='%23fff'/%3e%3c/svg%3e"); border: 0; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .04); height: 1.55em; width: 2.6em; }
        .form-switch .form-check-input:checked { background-color: #34c759; }
        .form-switch .form-check-input:focus { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3.4' fill='%23fff'/%3e%3c/svg%3e"); }
        .input-group-text { background: var(--app-soft); border-color: var(--app-field-line); border-radius: 12px; color: var(--app-muted); font-size: .9rem; }
        .alert { border: 0; border-radius: 14px; font-size: .9rem; padding: 13px 16px; }
        .alert-success { background: rgba(52, 199, 89, .12); color: #1b6d31; }
        .alert-danger { background: rgba(255, 59, 48, .1); color: #b00012; }
        .alert-warning { background: rgba(255, 149, 0, .12); color: #9a4a00; }
        .alert-light { background: var(--app-soft); color: var(--app-text); }
        .pagination { --bs-pagination-active-bg: var(--app-gold); --bs-pagination-active-border-color: var(--app-gold); --bs-pagination-color: var(--app-blue); --bs-pagination-hover-color: var(--app-blue); --bs-pagination-border-color: transparent; --bs-pagination-hover-bg: var(--app-soft); --bs-pagination-focus-box-shadow: 0 0 0 4px rgba(0, 113, 227, .18); margin: 0; }
        .text-primary { color: var(--app-gold) !important; }
        .text-success { color: var(--app-green) !important; }
        .text-danger { color: var(--app-red) !important; }
        .card { border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: none; margin-bottom: 2rem; }
        .card-header { background: var(--app-soft); border-bottom: 1px solid var(--app-line); border-radius: var(--app-radius) var(--app-radius) 0 0 !important; color: var(--app-ink); padding: 1rem 1.25rem; }
        .badge-role { border-radius: 980px; font-weight: 500; padding: .4rem .85rem; }
        .badge-super-admin { background-color: var(--app-ink); color: #fff; }
        .badge-admin { background-color: var(--app-gold); color: #fff; }
        .badge-manager { background-color: #5856d6; color: #fff; }
        .badge-moderateur { background-color: #8e8e93; color: #fff; }

        /* Composants partagés */
        .ui-btn { align-items: center; border: 1px solid transparent; border-radius: 980px; cursor: pointer; display: inline-flex; font-size: .875rem; font-weight: 400; gap: 7px; height: 38px; justify-content: center; letter-spacing: -.01em; padding: 0 18px; text-decoration: none; transition: background .15s, border-color .15s, color .15s, opacity .15s; white-space: nowrap; }
        .ui-btn-sm { font-size: .8rem; height: 30px; padding: 0 13px; }
        .ui-btn-primary, .ui-btn-gold { background: var(--app-gold); color: #fff; }
        .ui-btn-primary:hover, .ui-btn-gold:hover { background: #0077ed; color: #fff; }
        .ui-btn-success { background: #34c759; color: #fff; }
        .ui-btn-success:hover { background: #30b350; color: #fff; }
        .ui-btn-ghost { background: var(--app-fill); color: var(--app-ink); }
        .ui-btn-ghost:hover { background: rgba(118, 118, 128, .2); color: var(--app-ink); }
        .ui-btn-soft { background: transparent; color: var(--app-blue); }
        .ui-btn-soft:hover { background: var(--app-gold-soft); color: var(--app-blue); }
        .ui-btn-danger { background: rgba(255, 59, 48, .1); color: var(--app-red); }
        .ui-btn-danger:hover:not(:disabled) { background: #ff3b30; color: #fff; }
        .ui-btn:disabled, .ui-btn.is-disabled { cursor: not-allowed; opacity: .4; }

        .ui-card { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: none; }
        .ui-card-head { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; padding: 20px 24px; }
        .ui-card-head.has-border { border-bottom: 1px solid var(--app-line); }
        .ui-card-head h2 { color: var(--app-ink); font-size: 1.06rem; font-weight: 600; letter-spacing: -.02em; margin: 0; }
        .ui-card-head p { color: var(--app-muted); font-size: .82rem; margin: 2px 0 0; }
        .ui-card-body { padding: 20px 24px 24px; }

        .ui-stats { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 22px; }
        .ui-stat { background: var(--app-soft); border: 0; border-radius: var(--app-radius); padding: 18px 20px; position: relative; }
        .ui-stat-top { align-items: center; color: var(--app-muted); display: flex; font-size: .82rem; font-weight: 400; justify-content: space-between; }
        .ui-stat-top i { color: var(--app-gold); font-size: 1rem; }
        .ui-stat strong { color: var(--app-ink); display: block; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", sans-serif; font-size: 1.85rem; font-variant-numeric: tabular-nums; font-weight: 600; letter-spacing: -.03em; line-height: 1.05; margin-top: 10px; }
        .ui-stat small { color: var(--app-muted); display: block; font-size: .78rem; margin-top: 6px; }
        .ui-stat .ui-meter { background: rgba(0, 0, 0, .08); margin-top: 12px; }
        .ui-meter { background: var(--app-fill); border-radius: 99px; height: 6px; overflow: hidden; }
        .ui-meter span { background: var(--accent, var(--app-gold)); border-radius: inherit; display: block; height: 100%; }

        /* Pastilles aux couleurs système Apple */
        .ui-pill { align-items: center; background: var(--app-fill); border-radius: 980px; color: var(--app-muted); display: inline-flex; font-size: .74rem; font-weight: 500; gap: 5px; line-height: 1.2; padding: 4px 10px; white-space: nowrap; }
        .ui-pill.dot::before { background: currentColor; border-radius: 50%; content: ""; flex-shrink: 0; height: 6px; width: 6px; }
        .ui-pill.success { background: rgba(52, 199, 89, .14); color: var(--app-green); }
        .ui-pill.warning { background: rgba(255, 149, 0, .14); color: var(--app-orange); }
        .ui-pill.danger { background: rgba(255, 59, 48, .12); color: var(--app-red); }
        .ui-pill.info { background: rgba(0, 122, 255, .12); color: var(--app-blue); }
        .ui-pill.gold { background: rgba(88, 86, 214, .12); color: #4b49b6; }

        /* Contrôle segmenté iOS (onglets de filtre) */
        .ui-segmented { background: var(--app-fill); border-radius: 9px; display: inline-flex; gap: 2px; padding: 2px; }
        .ui-segmented > * { background: transparent; border: 0; border-radius: 7px; color: var(--app-ink); font-size: .82rem; font-weight: 500; padding: 5px 12px; text-decoration: none; }
        .ui-segmented > .is-active { background: #fff; box-shadow: 0 3px 8px rgba(0, 0, 0, .12), 0 3px 1px rgba(0, 0, 0, .04); }

        .ev-tabs a, .resp-tabs a, .status-tabs a, .type-tabs a, .tabs a { flex-shrink: 0; }
        .resp-tabs, .status-tabs, .type-tabs { max-width: 100%; overflow-x: auto; scrollbar-width: none; }
        .ui-toolbar { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; }
        .ui-search { flex: 1 1 220px; position: relative; }
        .ui-search i { color: #86868b; left: 13px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); }
        .ui-search input { padding-left: 36px !important; }
        .ui-toolbar .form-control, .ui-toolbar .form-select { background-color: var(--app-fill); border-color: transparent; border-radius: 10px; font-size: .86rem; height: 36px; min-height: 36px; }
        .ui-toolbar .form-control:focus, .ui-toolbar .form-select:focus { background-color: #fff; border-color: var(--app-gold); }
        .ui-toolbar .form-select { padding-right: 34px; width: auto; }
        .ui-toolbar input[type=date] { width: auto; }

        .ui-table { margin: 0; width: 100%; }
        .ui-table thead th { background: transparent; border-bottom: 1px solid var(--app-line); color: var(--app-muted); font-size: .78rem; font-weight: 500; letter-spacing: 0; padding: 10px 14px; text-transform: none; white-space: nowrap; }
        .ui-table tbody td { border-bottom: 1px solid var(--app-line); color: var(--app-text); font-size: .88rem; padding: 13px 14px; vertical-align: middle; }
        .ui-table tbody tr { transition: background .15s; }
        .ui-table tbody tr:hover { background: rgba(0, 0, 0, .02); }
        .ui-table tbody tr:last-child td { border-bottom: 0; }
        .ui-table th:first-child, .ui-table td:first-child { padding-left: 24px; }
        .ui-table th:last-child, .ui-table td:last-child { padding-right: 24px; }
        .ui-person { align-items: center; display: flex; gap: 12px; min-width: 0; }
        .ui-avatar { align-items: center; background: #8e8e93; border-radius: 50%; color: #fff; display: flex; flex: 0 0 36px; font-size: .8rem; font-weight: 600; height: 36px; justify-content: center; width: 36px; }
        .ui-avatar.gold { background: var(--app-gold); color: #fff; }
        .ui-person strong { color: var(--app-ink); display: block; font-size: .9rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ui-person small, .ui-sub { color: var(--app-muted); display: block; font-size: .78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ui-icon-btn { align-items: center; background: transparent; border: 0; border-radius: 50%; color: var(--app-blue); display: inline-flex; height: 32px; justify-content: center; text-decoration: none; transition: background .15s, color .15s; width: 32px; }
        .ui-icon-btn:hover { background: var(--app-gold-soft); color: var(--app-blue); }
        .ui-icon-btn.danger { color: var(--app-red); }
        .ui-icon-btn.danger:hover { background: rgba(255, 59, 48, .1); color: var(--app-red); }
        .ui-actions { align-items: center; display: flex; gap: 2px; justify-content: flex-end; }
        .ui-actions form { margin: 0; }

        .ui-empty { color: var(--app-muted); font-size: .9rem; padding: 48px 20px; text-align: center; }
        .ui-empty > i { color: #c7c7cc; display: block; font-size: 2.2rem; margin-bottom: 10px; }
        .ui-empty strong { color: var(--app-ink); display: block; font-size: 1.02rem; font-weight: 600; margin-bottom: 4px; }
        .ui-pager { display: flex; justify-content: center; padding: 16px; }
        .ui-pager nav { width: 100%; }
        .ui-pager p.small { display: none; }
        .ui-pager .pagination { gap: 4px; justify-content: center; }
        .ui-pager .page-link, .pagination .page-link { border-radius: 8px !important; }

        .dropdown-menu { -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); background: rgba(255, 255, 255, .92); border: 1px solid var(--app-line); border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, .12), 0 0 0 .5px rgba(0, 0, 0, .04); font-size: .86rem; min-width: 200px; padding: 5px; }
        .dropdown-item { align-items: center; border-radius: 6px; color: var(--app-ink); display: flex; gap: 10px; padding: 6px 10px; }
        .dropdown-item i { color: inherit; opacity: .75; width: 16px; }
        .dropdown-item:hover, .dropdown-item:focus { background: var(--app-gold); color: #fff; }
        .dropdown-item.text-danger:hover { background: #ff3b30; color: #fff !important; }
        .dropdown-item:active { background: var(--app-gold); }
        .dropdown-divider { border-color: var(--app-line); margin: 4px 6px; }
        .modal-content { border: 0; border-radius: 18px; box-shadow: 0 30px 80px rgba(0, 0, 0, .25); }
        .modal-backdrop.show { opacity: .3; }

        @media (max-width: 991.98px) {
            .sidebar { background: #f5f5f7; box-shadow: 20px 0 60px rgba(0, 0, 0, .15); transform: translateX(-100%); transition: transform .4s var(--app-ease); }
            body.nav-open .sidebar { transform: none; }
            body.nav-open .sidebar-backdrop { opacity: 1; pointer-events: auto; }
            body.nav-open { overflow: hidden; }
            .main-content { margin-left: 0; padding: 0 16px 28px; }
            .app-topbar { margin: 0 -16px 18px; padding: 0 16px; }
            .menu-toggle { display: inline-flex; }
        }
        @media (max-width: 575.98px) {
            .topbar-btn span { display: none; }
            .topbar-btn { justify-content: center; padding: 0; width: 32px; }
        }
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
        $brandLogo = \App\Support\EventMedia::storageUrl($currentOrganization?->logo);
        $isPlatformAdmin = auth()->user()->isPlatformAdmin();
    @endphp
    @php
        $navItem = fn (string $route, string $icon, string $label, string|array $active) => (object) compact('route', 'icon', 'label', 'active');
        $navGroups = $isPlatformAdmin
            ? [
                'Plateforme' => [
                    $navItem('platform.dashboard', 'bi-grid-1x2', 'Vue plateforme', 'platform.dashboard'),
                    $navItem('platform.organizations', 'bi-buildings', 'Organisations', 'platform.organizations'),
                    $navItem('platform.plans', 'bi-gem', 'Plans abonnement', 'platform.plans'),
                    $navItem('platform.communication-packages', 'bi-chat-dots', 'Packages messages', 'platform.communication-packages'),
                ],
                'Référentiels' => [
                    $navItem('categories.index', 'bi-tags', "Catégories d'événement", 'categories.*'),
                    $navItem('devises.index', 'bi-currency-exchange', 'Devises', 'devises.*'),
                    $navItem('localisations.index', 'bi-globe2', 'Pays & villes', ['localisations.*', 'countries.*', 'cities.*']),
                ],
                'Contenu' => [
                    $navItem('platform.landing.edit', 'bi-window-stack', 'Landing page', 'platform.landing.*'),
                    $navItem('platform.invitation-cards', 'bi-postcard', 'Cartes invitation', 'platform.invitation-cards'),
                    $navItem('platform.invitation-experience.edit', 'bi-envelope-open-heart', 'Expérience d’invitation', 'platform.invitation-experience.*'),
                ],
                'Paramètres' => [
                    $navItem('platform.mail-settings.edit', 'bi-envelope-at', 'Envoi des emails', 'platform.mail-settings.*'),
                ],
            ]
            : array_filter([
                'Pilotage' => [
                    $navItem('dashboard', 'bi-grid-1x2', 'Tableau de bord', 'dashboard'),
                    $navItem('events.index', 'bi-calendar-event', 'Événements', 'events.*'),
                ],
                'Organisation' => auth()->user()->isSuperAdmin() ? [
                    $navItem('users.index', 'bi-people', 'Utilisateurs', 'users.*'),
                    $navItem('saas.plans', 'bi-gem', 'Abonnement', 'saas.plans'),
                    $navItem('saas.billing', 'bi-receipt', 'Facturation', 'saas.billing'),
                    $navItem('saas.communications', 'bi-chat-dots', 'Crédits messages', 'saas.communications'),
                    $navItem('saas.branding', 'bi-palette', 'Branding', 'saas.branding'),
                ] : [],
            ]);
        $homeRoute = $isPlatformAdmin ? 'platform.dashboard' : 'dashboard';
    @endphp

    <aside class="sidebar" id="appSidebar" aria-label="Navigation principale">
        <div class="sidebar-header">
            <a href="{{ route($homeRoute) }}" class="sidebar-logo">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="sidebar-logo-icon" style="object-fit: cover;">
                @else
                    <span class="sidebar-logo-icon"><i class="bi bi-shield-check"></i></span>
                @endif
                <span>
                    <span class="sidebar-logo-text d-block">{{ $brandName }}</span>
                    <span class="sidebar-subtitle d-block">{{ $isPlatformAdmin ? 'Plateforme · Supervision' : ucfirst($currentOrganization?->plan ?? 'starter') . ' · Console' }}</span>
                </span>
            </a>
        </div>

        <ul class="sidebar-menu">
            @foreach($navGroups as $group => $items)
                <li class="sidebar-section">{{ $group }}</li>
                @foreach($items as $item)
                    <li>
                        <a href="{{ route($item->route) }}" @class(['active' => request()->routeIs(...(array) $item->active)])>
                            <i class="bi {{ $item->icon }}"></i>
                            <span>{{ $item->label }}</span>
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>

        <div class="sidebar-footer">
            <div class="user-info">
                @php $userBlock = '<div class="user-avatar">' . e(strtoupper(mb_substr(auth()->user()->name, 0, 1))) . '</div><div class="user-details"><div class="user-name">' . e(auth()->user()->name) . '</div><div class="user-role">' . e(ucfirst(str_replace('_', ' ', auth()->user()->role))) . '</div></div>'; @endphp
                @if($isPlatformAdmin)
                    <div class="user-link">{!! $userBlock !!}</div>
                @else
                    <a href="{{ route('profile.edit') }}" class="user-link" title="Mon profil">{!! $userBlock !!}</a>
                @endif
                <button type="submit" form="logout-form" class="logout-btn" title="Déconnexion" aria-label="Déconnexion">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-nav-close></div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <div class="main-content">
        <header class="app-topbar">
            <button type="button" class="menu-toggle" data-nav-toggle aria-controls="appSidebar" aria-label="Ouvrir le menu"><i class="bi bi-list"></i></button>
            <nav class="crumbs" aria-label="Fil d'Ariane">
                <span class="d-none d-sm-inline">{{ $brandName }}</span>
                <i class="bi bi-chevron-right d-none d-sm-inline"></i>
                <strong>@yield('title', 'Dashboard')</strong>
            </nav>
            <div class="topbar-actions">
                @if(!$isPlatformAdmin && !request()->routeIs('events.create', 'events.index'))
                    <a href="{{ route('events.create') }}" class="topbar-btn" title="Nouvel événement"><i class="bi bi-plus-lg"></i><span>Nouvel événement</span></a>
                @endif
                <div class="dropdown">
                    <button type="button" class="topbar-avatar" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu du compte">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-2 py-1"><strong class="d-block small text-dark">{{ auth()->user()->name }}</strong><span class="small text-muted">{{ auth()->user()->email }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        @unless($isPlatformAdmin)<li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i>Mon profil</a></li>@endunless
                        <li><button type="submit" form="logout-form" class="dropdown-item"><i class="bi bi-box-arrow-right"></i>Déconnexion</button></li>
                    </ul>
                </div>
            </div>
        </header>
    @endauth

    <div class="app-content @auth container-fluid px-0 @else container @endauth">
        @if(session('success') && !View::hasSection('hide_flash'))
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

        @if(session('warning') && !View::hasSection('hide_flash'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any() && !View::hasSection('hide_errors'))
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
    <x-app.confirm-dialog />
    @auth
    <script>
        (() => {
            const body = document.body;
            const topbar = document.querySelector('.app-topbar');
            const setOpen = (open) => body.classList.toggle('nav-open', open);
            document.querySelectorAll('[data-nav-toggle]').forEach((el) => el.addEventListener('click', () => setOpen(!body.classList.contains('nav-open'))));
            document.querySelectorAll('[data-nav-close], .sidebar-menu a').forEach((el) => el.addEventListener('click', () => setOpen(false)));
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
            const onScroll = () => topbar?.classList.toggle('is-scrolled', window.scrollY > 4);
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
    </script>
    @endauth
    @stack('scripts')
</body>
</html>
