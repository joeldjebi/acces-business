<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation expirée</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f4f2ed; color: #171713; font-family: Arial, sans-serif; }
        main { width: min(620px, 100%); background: #fff; border: 1px solid #ddd7ca; border-radius: 8px; padding: clamp(28px, 6vw, 52px); box-shadow: 0 24px 60px rgba(23, 23, 19, .1); }
        .label { margin: 0 0 14px; color: #9a6b2f; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        h1 { margin: 0 0 16px; font-size: clamp(28px, 5vw, 42px); }
        p { margin: 0; color: #68645c; font-size: 17px; line-height: 1.65; }
        strong { color: #171713; }
    </style>
</head>
<body>
    <main>
        <p class="label">Accès à l’événement</p>
        <h1>Cette invitation a expiré</h1>
        <p>
            La date limite d’inscription à <strong>{{ $event->titre }}</strong>
            était fixée au {{ $event->date_limite_inscription->format('d/m/Y') }}.
            Contactez l’organisateur si vous avez besoin d’assistance.
        </p>
    </main>
</body>
</html>
