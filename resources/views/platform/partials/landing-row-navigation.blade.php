<div class="row-card">
    <div class="row-head">
        <strong>Lien menu</strong>
        <div class="checks"><label><input type="checkbox" name="settings[navigation][{{ $i }}][enabled]" value="1" @checked($item['enabled'] ?? true)> Visible</label><button type="button" class="mini-btn danger" data-remove-row><i class="bi bi-trash"></i>Retirer</button></div>
    </div>
    <div class="grid">
        <div><label>Libellé</label><input type="text" name="settings[navigation][{{ $i }}][label]" value="{{ $item['label'] ?? '' }}"></div>
        <div><label>Ancre de section</label><input type="text" name="settings[navigation][{{ $i }}][anchor]" value="{{ $item['anchor'] ?? '' }}"><div class="help">Exemple: features, checkin, wallet, pricing, faq.</div></div>
    </div>
</div>
