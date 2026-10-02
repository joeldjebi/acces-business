<!DOCTYPE html>
<html lang="fr">
<body style="margin:0;background:#f5f3ee;font-family:Arial,sans-serif;color:#171713;">
<div style="max-width:640px;margin:0 auto;padding:32px 18px;">
    <div style="background:#fff;border:1px solid #ded8cc;border-radius:8px;padding:34px;">
        <p style="margin:0 0 18px;">Bonjour {{ $name }},</p>
        <div style="font-size:16px;line-height:1.7;white-space:pre-line;">{{ $broadcast->message }}</div>
        @if($fileUrl)
            <p style="margin:26px 0 0;">
                <a href="{{ $fileUrl }}" style="display:inline-block;background:#171713;color:#fff;text-decoration:none;padding:13px 20px;border-radius:6px;">Ouvrir le fichier</a>
            </p>
        @endif
        <p style="margin:28px 0 0;color:#756f65;font-size:13px;">Communication concernant {{ $broadcast->event->titre }}.</p>
    </div>
</div>
</body>
</html>
