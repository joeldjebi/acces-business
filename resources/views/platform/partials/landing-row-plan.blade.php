<div class="row-card">
    <div class="row-head">
        <strong>Plan</strong>
        <div class="checks"><label><input type="checkbox" name="settings[plans][{{ $i }}][enabled]" value="1" @checked($item['enabled'] ?? true)> Visible</label><label><input type="checkbox" name="settings[plans][{{ $i }}][featured]" value="1" @checked($item['featured'] ?? false)> Mis en avant</label><button type="button" class="mini-btn danger" data-remove-row><i class="bi bi-trash"></i>Retirer</button></div>
    </div>
    <div class="grid">
        <div><label>Nom</label><input type="text" name="settings[plans][{{ $i }}][name]" value="{{ $item['name'] ?? '' }}"></div>
        <div><label>Prix / label</label><input type="text" name="settings[plans][{{ $i }}][price]" value="{{ $item['price'] ?? '' }}"></div>
        <div><label>Sous-titre</label><input type="text" name="settings[plans][{{ $i }}][subtitle]" value="{{ $item['subtitle'] ?? '' }}"></div>
        <div><label>Points inclus, un par ligne</label><textarea name="settings[plans][{{ $i }}][items]">{{ $lines($item['items'] ?? []) }}</textarea></div>
    </div>
</div>
