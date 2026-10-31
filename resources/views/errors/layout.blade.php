<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <style>
        :root { --ink: #171713; --muted: #7a746a; --line: rgba(23, 23, 19, .1); }
        * { box-sizing: border-box; }
        body { align-items: center; background: #fff; color: var(--ink); display: flex; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", Helvetica, Arial, sans-serif; justify-content: center; margin: 0; min-height: 100vh; padding: 24px; -webkit-font-smoothing: antialiased; }
        main { max-width: 440px; text-align: center; }
        .code { color: var(--muted); font-size: .85rem; font-weight: 600; letter-spacing: .08em; }
        h1 { font-size: 1.6rem; font-weight: 700; letter-spacing: -.02em; line-height: 1.25; margin: 10px 0 8px; }
        p { color: var(--muted); font-size: .95rem; line-height: 1.55; margin: 0; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 26px; }
        a, button { align-items: center; border: 1px solid var(--line); border-radius: 999px; color: var(--ink); cursor: pointer; display: inline-flex; font: inherit; font-size: .9rem; font-weight: 600; height: 42px; padding: 0 18px; text-decoration: none; background: #fff; }
        .primary { background: var(--ink); border-color: var(--ink); color: #fff; }
    </style>
</head>
<body>
    <main>
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <button type="button" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">Retour</button>
            <a href="{{ url('/') }}" class="primary">Accueil</a>
        </div>
    </main>
</body>
</html>
