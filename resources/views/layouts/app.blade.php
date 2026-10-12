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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #b98943;
            --sidebar-width: 264px;
            --sidebar-bg: #141412;
            --app-ink: #171713;
            --app-text: #2c2a25;
            --app-muted: #7a746a;
            --app-line: rgba(23, 23, 19, .08);
            --app-panel: #ffffff;
            --app-bg: #f6f4ef;
            --app-gold: #b98943;
            --app-gold-soft: rgba(185, 137, 67, .12);
            --app-gold-deep: #8a6128;
            --app-green: #2e7b65;
            --app-red: #a4514a;
            --app-blue: #315f83;
            --app-radius: 18px;
            --app-shadow: 0 1px 2px rgba(23, 23, 19, .04), 0 12px 32px -12px rgba(23, 23, 19, .10);
            --app-ease: cubic-bezier(.16, 1, .3, 1);
            --bs-body-font-family: Inter, -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", sans-serif;
            --bs-border-radius: 12px;
            --bs-link-color-rgb: 138, 97, 40;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--app-bg);
            color: var(--app-text);
            font-family: var(--bs-body-font-family);
            -webkit-font-smoothing: antialiased;
            letter-spacing: -.005em;
        }

        ::selection { background: var(--app-gold); color: #fff; }

        /* Sidebar */
        .sidebar {
            background: radial-gradient(120% 60% at 0% 0%, rgba(185, 137, 67, .16), transparent 60%), var(--sidebar-bg);
            color: #fff;
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
            scrollbar-color: rgba(255, 255, 255, .15) transparent;
        }

        .sidebar-header { padding: 22px 20px 14px; }
        .sidebar-logo { align-items: center; color: #fff; display: flex; gap: 12px; min-width: 0; text-decoration: none; }
        .sidebar-logo:hover { color: #fff; }
        .sidebar-logo > span:last-child { min-width: 0; }
        .sidebar-logo-icon {
            align-items: center;
            background: linear-gradient(140deg, #d8b978, #b98943 55%, #8a6128);
            border-radius: 11px;
            box-shadow: 0 8px 20px -8px rgba(185, 137, 67, .7), inset 0 1px 0 rgba(255, 255, 255, .35);
            color: #171713;
            display: flex;
            flex: 0 0 38px;
            font-size: 18px;
            height: 38px;
            justify-content: center;
            width: 38px;
        }
        .sidebar-logo-text { font-size: 1rem; font-weight: 650; letter-spacing: -.02em; line-height: 1.15; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sidebar-subtitle { color: rgba(255, 255, 255, .45); font-size: .74rem; font-weight: 500; margin-top: 2px; }

        .sidebar-menu { flex: 1; list-style: none; margin: 0; padding: 4px 12px 16px; }
        .sidebar-menu .sidebar-section { color: rgba(255, 255, 255, .34); font-size: .66rem; font-weight: 600; letter-spacing: .12em; margin: 0; padding: 18px 12px 8px; text-transform: uppercase; }
        .sidebar-menu li { margin: 1px 0; }
        .sidebar-menu a {
            align-items: center;
            border-radius: 10px;
            color: rgba(255, 255, 255, .66);
            display: flex;
            font-size: .875rem;
            font-weight: 500;
            gap: 11px;
            padding: 9px 12px;
            position: relative;
            text-decoration: none;
            transition: background .25s, color .25s;
        }
        .sidebar-menu a i { font-size: 1.02rem; opacity: .85; text-align: center; width: 18px; }
        .sidebar-menu a:hover { background: rgba(255, 255, 255, .06); color: #fff; }
        .sidebar-menu a.active { background: rgba(255, 255, 255, .09); box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .06); color: #fff; }
        .sidebar-menu a.active i { color: #d8b978; opacity: 1; }
        .sidebar-menu a.active::after { background: #d8b978; border-radius: 99px; box-shadow: 0 0 10px #d8b978; content: ""; height: 6px; position: absolute; right: 12px; width: 6px; }

        .sidebar-footer { border-top: 1px solid rgba(255, 255, 255, .07); padding: 12px; }
        .user-info { align-items: center; border-radius: 12px; display: flex; gap: 10px; padding: 6px; }
        .user-avatar {
            align-items: center;
            background: rgba(255, 255, 255, .1);
            border-radius: 10px;
            color: #d8b978;
            display: flex;
            flex-shrink: 0;
            font-size: .9rem;
            font-weight: 600;
            height: 36px;
            justify-content: center;
            width: 36px;
        }
        .user-details { flex: 1; min-width: 0; }
        .user-name { color: #fff; font-size: .84rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-role { color: rgba(255, 255, 255, .45); font-size: .72rem; margin-top: 1px; }
        .logout-btn {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 9px;
            color: rgba(255, 255, 255, .5);
            display: flex;
            flex-shrink: 0;
            height: 34px;
            justify-content: center;
            transition: background .2s, color .2s;
            width: 34px;
        }
        .logout-btn:hover { background: rgba(255, 255, 255, .08); color: #fff; }

        /* Main */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; padding: 0 32px 40px; }
        .app-topbar {
            align-items: center;
            backdrop-filter: saturate(180%) blur(18px);
            -webkit-backdrop-filter: saturate(180%) blur(18px);
            background: color-mix(in srgb, var(--app-bg) 82%, transparent);
            border-bottom: 1px solid transparent;
            display: flex;
            gap: 14px;
            height: 64px;
            margin: 0 -32px 22px;
            padding: 0 32px;
            position: sticky;
            top: 0;
            transition: border-color .3s;
            z-index: 1030;
        }
        .app-topbar.is-scrolled { border-bottom-color: var(--app-line); }
        .crumbs { align-items: center; color: var(--app-muted); display: flex; font-size: .86rem; gap: 8px; min-width: 0; }
        .crumbs strong { color: var(--app-ink); font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .crumbs i { font-size: .7rem; opacity: .6; }
        .topbar-actions { align-items: center; display: flex; gap: 10px; margin-left: auto; }
        .topbar-btn {
            align-items: center;
            background: var(--app-ink);
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: .82rem;
            font-weight: 600;
            gap: 6px;
            height: 36px;
            padding: 0 14px;
            text-decoration: none;
            transition: transform .3s var(--app-ease), box-shadow .3s;
        }
        .topbar-btn:hover { box-shadow: 0 10px 22px -10px rgba(23, 23, 19, .6); color: #fff; transform: translateY(-1px); }
        .topbar-avatar {
            align-items: center;
            background: linear-gradient(140deg, #d8b978, #b98943 60%, #8a6128);
            border-radius: 50%;
            color: #171713;
            display: flex;
            flex: 0 0 36px;
            font-size: .84rem;
            font-weight: 700;
            height: 36px;
            justify-content: center;
            width: 36px;
        }
        .menu-toggle {
            align-items: center;
            background: var(--app-panel);
            border: 1px solid var(--app-line);
            border-radius: 10px;
            color: var(--app-ink);
            display: none;
            flex-shrink: 0;
            font-size: 1.2rem;
            height: 38px;
            justify-content: center;
            width: 38px;
        }
        .sidebar-backdrop { backdrop-filter: blur(2px); background: rgba(10, 10, 9, .45); inset: 0; opacity: 0; pointer-events: none; position: fixed; transition: opacity .3s; z-index: 1035; }
        .app-content { animation: appIn .5s var(--app-ease) backwards; }
        @keyframes appIn { from { opacity: 0; transform: translateY(8px); } }

        /* Bootstrap polish (global, safe) */
        .btn { border-radius: 12px; font-weight: 500; }
        .btn-primary, .btn-dark { background: var(--app-ink); border-color: var(--app-ink); }
        .btn-primary:hover, .btn-dark:hover { background: #2c2a25; border-color: #2c2a25; }
        /* Form fields: one refined size for every console form */
        .form-label { color: var(--app-ink); font-size: .8rem; font-weight: 600; letter-spacing: -.005em; margin-bottom: 6px; }
        .form-label .text-muted, .form-label .opt { font-weight: 400; }
        .form-control, .form-select { background-color: #fff; border: 1px solid rgba(23, 23, 19, .13); border-radius: 10px; box-shadow: 0 1px 1px rgba(23, 23, 19, .03); color: var(--app-ink); font-size: .875rem; line-height: 1.4; min-height: 40px; padding: 8px 12px; transition: border-color .15s, box-shadow .15s, background-color .15s; }
        .form-select { padding-right: 34px; background-position: right 12px center; background-size: 14px 10px; }
        .form-control:hover, .form-select:hover { border-color: rgba(23, 23, 19, .24); }
        .form-control:focus, .form-select:focus { border-color: var(--app-gold); box-shadow: 0 0 0 3px var(--app-gold-soft); outline: 0; }
        .form-control::placeholder { color: rgba(23, 23, 19, .38); }
        .form-control[readonly], .form-control:disabled, .form-select:disabled { background-color: #f7f6f2; color: var(--app-muted); }
        textarea.form-control { min-height: 96px; resize: vertical; }
        .form-control[type=file] { padding: 6px 8px; }
        .form-control[type=file]::file-selector-button { background: rgba(23, 23, 19, .06); border: 0; border-radius: 7px; color: var(--app-ink); font-weight: 600; margin: 0 10px 0 0; padding: 5px 10px; }
        .form-control-sm, .form-select-sm { font-size: .8rem; min-height: 32px; padding-bottom: 4px; padding-top: 4px; }
        .form-text { color: var(--app-muted); font-size: .76rem; margin-top: 5px; }
        .invalid-feedback { font-size: .76rem; }
        .form-control.is-invalid, .form-select.is-invalid { border-color: rgba(164, 81, 74, .6); }
        .form-control.is-invalid:focus, .form-select.is-invalid:focus { box-shadow: 0 0 0 3px rgba(164, 81, 74, .14); }
        .form-check-input { border-color: rgba(23, 23, 19, .25); }
        .form-check-input:focus { border-color: var(--app-gold); box-shadow: 0 0 0 3px var(--app-gold-soft); }
        .form-check-input:checked { background-color: var(--app-ink); border-color: var(--app-ink); }
        .form-switch .form-check-input { height: 1.25em; width: 2.2em; }
        .input-group-text { background: #f7f6f2; border-color: rgba(23, 23, 19, .13); border-radius: 10px; color: var(--app-muted); font-size: .875rem; }
        .alert { border: 1px solid transparent; border-radius: 14px; font-size: .92rem; }
        .alert-success { background: #eef6f2; border-color: rgba(46, 123, 101, .2); color: #22604f; }
        .alert-danger { background: #f9eeed; border-color: rgba(164, 81, 74, .22); color: #8a3d36; }
        .alert-warning { background: #fbf4e8; border-color: rgba(185, 137, 67, .28); color: #7a5727; }
        .pagination { --bs-pagination-active-bg: var(--app-ink); --bs-pagination-active-border-color: var(--app-ink); --bs-pagination-color: var(--app-ink); --bs-pagination-hover-color: var(--app-ink); --bs-pagination-focus-box-shadow: 0 0 0 4px var(--app-gold-soft); margin: 0; }
        .text-primary { color: var(--app-gold) !important; }
        .card { border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); margin-bottom: 2rem; }
        .card-header { background: var(--app-ink); border-radius: var(--app-radius) var(--app-radius) 0 0 !important; color: #fff; padding: 1.1rem 1.25rem; }
        .badge-role { border-radius: 20px; font-weight: 500; padding: .45rem .9rem; }
        .badge-super-admin { background-color: #171713; color: #d8b978; }
        .badge-admin { background-color: var(--app-gold); color: #fff; }
        .badge-manager { background-color: var(--app-blue); color: #fff; }
        .badge-moderateur { background-color: #6c757d; color: #fff; }

        /* UI kit (shared by console pages) */
        .ui-btn { align-items: center; border: 1px solid transparent; border-radius: 999px; cursor: pointer; display: inline-flex; font-size: .875rem; font-weight: 600; gap: 8px; height: 42px; justify-content: center; letter-spacing: -.01em; padding: 0 18px; text-decoration: none; transition: transform .35s var(--app-ease), box-shadow .35s var(--app-ease), background .2s, border-color .2s, color .2s; white-space: nowrap; }
        .ui-btn:active { transform: scale(.97); }
        .ui-btn-sm { font-size: .8rem; height: 34px; padding: 0 13px; }
        .ui-btn-primary { background: var(--app-ink); box-shadow: 0 8px 20px -10px rgba(23, 23, 19, .55); color: #fff; }
        .ui-btn-primary:hover { box-shadow: 0 14px 28px -12px rgba(23, 23, 19, .6); color: #fff; transform: translateY(-1px); }
        .ui-btn-gold { background: linear-gradient(135deg, #d8b978, #b98943 50%, #8a6128); box-shadow: 0 10px 24px -12px rgba(185, 137, 67, .9); color: #fff; }
        .ui-btn-gold:hover { color: #fff; transform: translateY(-1px); }
        .ui-btn-success { background: var(--app-green); color: #fff; }
        .ui-btn-success:hover { background: #256a56; color: #fff; }
        .ui-btn-ghost { background: var(--app-panel); border-color: var(--app-line); color: var(--app-ink); }
        .ui-btn-ghost:hover { border-color: rgba(23, 23, 19, .2); color: var(--app-ink); }
        .ui-btn-soft { background: rgba(23, 23, 19, .05); color: var(--app-ink); }
        .ui-btn-soft:hover { background: rgba(23, 23, 19, .09); color: var(--app-ink); }
        .ui-btn-danger { background: transparent; border-color: rgba(164, 81, 74, .25); color: var(--app-red); }
        .ui-btn-danger:hover:not(:disabled) { background: var(--app-red); border-color: var(--app-red); color: #fff; }
        .ui-btn:disabled, .ui-btn.is-disabled { cursor: not-allowed; opacity: .5; transform: none; }

        .ui-card { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); }
        .ui-card-head { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; padding: 18px 22px; }
        .ui-card-head.has-border { border-bottom: 1px solid var(--app-line); }
        .ui-card-head h2 { color: var(--app-ink); font-size: 1rem; font-weight: 650; letter-spacing: -.015em; margin: 0; }
        .ui-card-head p { color: var(--app-muted); font-size: .8rem; margin: 3px 0 0; }
        .ui-card-body { padding: 18px 22px 22px; }

        .ui-stats { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); margin-bottom: 20px; }
        .ui-stat { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); padding: 16px 18px; position: relative; }
        .ui-stat-top { align-items: center; color: var(--app-muted); display: flex; font-size: .78rem; font-weight: 500; justify-content: space-between; }
        .ui-stat-top i { align-items: center; background: color-mix(in srgb, var(--accent, var(--app-gold)) 12%, transparent); border-radius: 9px; color: var(--accent, var(--app-gold)); display: flex; font-size: .9rem; height: 30px; justify-content: center; width: 30px; }
        .ui-stat strong { color: var(--app-ink); display: block; font-size: 1.75rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.04em; line-height: 1; margin-top: 12px; }
        .ui-stat small { color: var(--app-muted); display: block; font-size: .76rem; margin-top: 6px; }
        .ui-stat .ui-meter { margin-top: 10px; }
        .ui-meter { background: rgba(23, 23, 19, .06); border-radius: 99px; height: 5px; overflow: hidden; }
        .ui-meter span { background: var(--accent, var(--app-gold)); border-radius: inherit; display: block; height: 100%; }

        .ui-pill { align-items: center; background: rgba(23, 23, 19, .06); border-radius: 999px; color: var(--app-muted); display: inline-flex; font-size: .72rem; font-weight: 600; gap: 6px; line-height: 1.2; padding: 5px 10px; white-space: nowrap; }
        .ui-pill.dot::before { background: currentColor; border-radius: 50%; content: ""; flex-shrink: 0; height: 6px; width: 6px; }
        .ui-pill.success { background: rgba(46, 123, 101, .1); color: var(--app-green); }
        .ui-pill.warning { background: rgba(185, 137, 67, .13); color: var(--app-gold-deep); }
        .ui-pill.danger { background: rgba(164, 81, 74, .1); color: var(--app-red); }
        .ui-pill.info { background: rgba(49, 95, 131, .1); color: var(--app-blue); }
        .ui-pill.gold { background: rgba(185, 137, 67, .16); color: #725322; }

        .ev-tabs a, .resp-tabs a, .status-tabs a, .type-tabs a, .tabs a { flex-shrink: 0; }
        .resp-tabs, .status-tabs, .type-tabs { max-width: 100%; overflow-x: auto; scrollbar-width: none; }
        .ui-toolbar { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; }
        .ui-search { flex: 1 1 220px; position: relative; }
        .ui-search i { color: var(--app-muted); left: 14px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); }
        .ui-search input { padding-left: 38px !important; }
        .ui-toolbar .form-control, .ui-toolbar .form-select { background-color: #fbfaf7; border-radius: 999px; font-size: .86rem; height: 40px; min-height: 40px; }
        .ui-toolbar .form-select { padding-right: 34px; width: auto; }
        .ui-toolbar input[type=date] { width: auto; }

        .ui-table { margin: 0; width: 100%; }
        .ui-table thead th { background: transparent; border-bottom: 1px solid var(--app-line); color: var(--app-muted); font-size: .7rem; font-weight: 600; letter-spacing: .06em; padding: 10px 14px; text-transform: uppercase; white-space: nowrap; }
        .ui-table tbody td { border-bottom: 1px solid var(--app-line); color: var(--app-text); font-size: .86rem; padding: 14px; vertical-align: middle; }
        .ui-table tbody tr { transition: background .2s; }
        .ui-table tbody tr:hover { background: rgba(23, 23, 19, .02); }
        .ui-table tbody tr:last-child td { border-bottom: 0; }
        .ui-table th:first-child, .ui-table td:first-child { padding-left: 22px; }
        .ui-table th:last-child, .ui-table td:last-child { padding-right: 22px; }
        .ui-person { align-items: center; display: flex; gap: 12px; min-width: 0; }
        .ui-avatar { align-items: center; background: linear-gradient(140deg, #2c2a25, #171713); border-radius: 50%; color: #d8b978; display: flex; flex: 0 0 38px; font-size: .8rem; font-weight: 600; height: 38px; justify-content: center; width: 38px; }
        .ui-avatar.gold { background: linear-gradient(140deg, #d8b978, #b98943 60%, #8a6128); color: #171713; }
        .ui-person strong { color: var(--app-ink); display: block; font-size: .9rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ui-person small, .ui-sub { color: var(--app-muted); display: block; font-size: .78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ui-icon-btn { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 10px; color: var(--app-muted); display: inline-flex; height: 34px; justify-content: center; text-decoration: none; transition: background .2s, color .2s; width: 34px; }
        .ui-icon-btn:hover { background: rgba(23, 23, 19, .06); color: var(--app-ink); }
        .ui-icon-btn.danger:hover { background: rgba(164, 81, 74, .1); color: var(--app-red); }
        .ui-actions { align-items: center; display: flex; gap: 4px; justify-content: flex-end; }
        .ui-actions form { margin: 0; }

        .ui-empty { color: var(--app-muted); font-size: .9rem; padding: 48px 20px; text-align: center; }
        .ui-empty > i { align-items: center; background: var(--app-gold-soft); border-radius: 18px; color: var(--app-gold-deep); display: inline-flex; font-size: 1.5rem; height: 56px; justify-content: center; margin-bottom: 12px; width: 56px; }
        .ui-empty strong { color: var(--app-ink); display: block; font-size: 1.02rem; margin-bottom: 4px; }
        .ui-pager { display: flex; justify-content: center; padding: 16px; }
        .ui-pager nav { width: 100%; }
        .ui-pager p.small { display: none; }
        .ui-pager .pagination { gap: 4px; justify-content: center; }
        .ui-pager .page-link { border-radius: 10px !important; }

        .dropdown-menu { border: 1px solid var(--app-line); border-radius: 14px; box-shadow: 0 20px 40px -16px rgba(23, 23, 19, .3); font-size: .86rem; padding: 6px; }
        .dropdown-item { align-items: center; border-radius: 9px; display: flex; gap: 10px; padding: 8px 10px; }
        .dropdown-item i { color: var(--app-muted); width: 16px; }
        .dropdown-item.text-danger i { color: inherit; }
        .dropdown-item:active { background: var(--app-ink); }
        .modal-content { border: 0; border-radius: 22px; box-shadow: 0 40px 80px -30px rgba(0, 0, 0, .45); }
        .modal-backdrop.show { opacity: .4; }

        @media (max-width: 991.98px) {
            .sidebar { box-shadow: 20px 0 60px rgba(0, 0, 0, .3); transform: translateX(-100%); transition: transform .45s var(--app-ease); }
            body.nav-open .sidebar { transform: none; }
            body.nav-open .sidebar-backdrop { opacity: 1; pointer-events: auto; }
            body.nav-open { overflow: hidden; }
            .main-content { margin-left: 0; padding: 0 16px 28px; }
            .app-topbar { margin: 0 -16px 18px; padding: 0 16px; }
            .menu-toggle { display: inline-flex; }
        }
        @media (max-width: 575.98px) {
            .topbar-btn span { display: none; }
            .topbar-btn { justify-content: center; padding: 0; width: 36px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .app-content { animation: none; }
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
            ]
            : array_filter([
                'Pilotage' => [
                    $navItem('dashboard', 'bi-grid-1x2', 'Tableau de bord', 'dashboard'),
                    $navItem('events.index', 'bi-calendar-event', 'Événements', 'events.*'),
                ],
                'Organisation' => auth()->user()->isSuperAdmin() ? [
                    $navItem('users.index', 'bi-people', 'Utilisateurs', 'users.*'),
                    $navItem('saas.plans', 'bi-gem', 'Plans SaaS', 'saas.plans'),
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
                <div class="user-avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                </div>
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
                <span class="topbar-avatar" title="{{ auth()->user()->name }} · {{ auth()->user()->email }}">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
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
