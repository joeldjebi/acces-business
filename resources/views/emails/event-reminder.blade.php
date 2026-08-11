<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rappel de réponse</title>
</head>
<body style="margin:0; background:#f8f4ec; font-family:Arial, sans-serif; color:#171713;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f8f4ec; padding:28px 12px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:620px; background:#fffefa; border:1px solid #dfd7cb; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 10px; color:#b98943; font-size:12px; font-weight:bold; letter-spacing:1.6px; text-transform:uppercase;">Rappel d'invitation</p>
                            <h1 style="margin:0 0 14px; font-size:26px; line-height:1.2;">{{ $event->titre }}</h1>
                            <p style="margin:0 0 20px; color:#625b51; font-size:16px; line-height:1.7;">Bonjour {{ $name }}, nous attendons encore votre réponse pour cet événement.</p>
                            <p style="margin:0 0 22px; color:#625b51; font-size:15px; line-height:1.7;">
                                Date: {{ optional($event->date_debut)->format('d/m/Y') ?: '-' }} à {{ $event->heure_debut ?: '-' }}<br>
                                Lieu: {{ $event->lieu ?: $event->ville ?: '-' }}
                            </p>
                            <a href="{{ $responseUrl }}" style="display:inline-block; background:#171713; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Répondre à l'invitation</a>
                            <p style="margin:22px 0 0; color:#8a6128; font-size:13px; line-height:1.6; word-break:break-all;">Lien direct: {{ $responseUrl }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
