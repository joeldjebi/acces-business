<div class="row-card">
    <div class="row-head">
        <strong>Métrique</strong>
        <div class="checks"><label><input type="checkbox" name="settings[hero_metrics][{{ $i }}][enabled]" value="1" @checked($item['enabled'] ?? true)> Visible</label><button type="button" class="mini-btn danger" data-remove-row><i class="bi bi-trash"></i>Retirer</button></div>
    </div>
    <div class="grid">
        <div><label>Libellé</label><input type="text" name="settings[hero_metrics][{{ $i }}][label]" value="{{ $item['label'] ?? '' }}"></div>
        <div><label>Valeur</label><input type="text" name="settings[hero_metrics][{{ $i }}][value]" value="{{ $item['value'] ?? '' }}"></div>
    </div>
</div>
