<div class="row-card">
    <div class="row-head">
        <strong>Élément</strong>
        <div class="checks"><label><input type="checkbox" name="settings[{{ $group }}][{{ $i }}][enabled]" value="1" @checked($item['enabled'] ?? true)> Visible</label><button type="button" class="mini-btn danger" data-remove-row><i class="bi bi-trash"></i>Retirer</button></div>
    </div>
    <div class="grid">
        <div><label>Icône Bootstrap</label><input type="text" name="settings[{{ $group }}][{{ $i }}][icon]" value="{{ $item['icon'] ?? 'bi-check2' }}"></div>
        <div><label>Libellé</label><input type="text" name="settings[{{ $group }}][{{ $i }}][label]" value="{{ $item['label'] ?? '' }}"></div>
    </div>
</div>
