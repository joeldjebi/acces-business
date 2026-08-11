<div class="row-card">
    <div class="row-head">
        <strong>Fonctionnalité</strong>
        <div class="checks"><label><input type="checkbox" name="settings[features][{{ $i }}][enabled]" value="1" @checked($item['enabled'] ?? true)> Visible</label><button type="button" class="mini-btn danger" data-remove-row><i class="bi bi-trash"></i>Retirer</button></div>
    </div>
    <div class="grid-3">
        <div><label>Icône</label><input type="text" name="settings[features][{{ $i }}][icon]" value="{{ $item['icon'] ?? 'bi-check2' }}"></div>
        <div><label>Titre</label><input type="text" name="settings[features][{{ $i }}][title]" value="{{ $item['title'] ?? '' }}"></div>
        <div><label>Description</label><textarea name="settings[features][{{ $i }}][body]">{{ $item['body'] ?? '' }}</textarea></div>
    </div>
</div>
