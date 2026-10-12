<div class="row-card">
    <div class="row-head">
        <strong>Étape du parcours</strong>
        <div class="checks"><label><input type="checkbox" name="settings[journey_steps][{{ $i }}][enabled]" value="1" @checked($item['enabled'] ?? true)> Visible</label><button type="button" class="mini-btn danger" data-remove-row><i class="bi bi-trash"></i>Retirer</button></div>
    </div>
    <div class="grid-3">
        <div><label>Icône</label><input type="text" name="settings[journey_steps][{{ $i }}][icon]" value="{{ $item['icon'] ?? 'bi-check2' }}"></div>
        <div><label>Titre</label><input type="text" name="settings[journey_steps][{{ $i }}][title]" value="{{ $item['title'] ?? '' }}"></div>
        <div>
            <label>Écran affiché dans le téléphone</label>
            <select class="form-select" name="settings[journey_steps][{{ $i }}][screen]">
                @foreach(\App\Models\LandingPage::JOURNEY_SCREENS as $value => $screenLabel)
                    <option value="{{ $value }}" @selected(($item['screen'] ?? 'invitation') === $value)>{{ $screenLabel }}</option>
                @endforeach
            </select>
        </div>
        <div style="grid-column:1/-1"><label>Description</label><textarea name="settings[journey_steps][{{ $i }}][body]">{{ $item['body'] ?? '' }}</textarea></div>
    </div>
</div>
