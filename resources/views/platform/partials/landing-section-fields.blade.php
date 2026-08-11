<div class="checks mb-3"><label><input type="checkbox" name="settings[{{ $key }}][enabled]" value="1" @checked($section['enabled'] ?? true)> {{ $label }}</label></div>
<div class="grid mb-3">
    <div><label>Eyebrow</label><input type="text" name="settings[{{ $key }}][eyebrow]" value="{{ $section['eyebrow'] ?? '' }}"></div>
    <div><label>Titre</label><input type="text" name="settings[{{ $key }}][title]" value="{{ $section['title'] ?? '' }}"></div>
    <div style="grid-column:1/-1"><label>Texte</label><textarea name="settings[{{ $key }}][body]">{{ $section['body'] ?? '' }}</textarea></div>
</div>
