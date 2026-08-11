<div class="row-card">
    <div class="row-head">
        <strong>Question</strong>
        <div class="checks"><label><input type="checkbox" name="settings[faqs][{{ $i }}][enabled]" value="1" @checked($item['enabled'] ?? true)> Visible</label><button type="button" class="mini-btn danger" data-remove-row><i class="bi bi-trash"></i>Retirer</button></div>
    </div>
    <div class="grid">
        <div><label>Question</label><input type="text" name="settings[faqs][{{ $i }}][question]" value="{{ $item['question'] ?? '' }}"></div>
        <div><label>Réponse</label><textarea name="settings[faqs][{{ $i }}][answer]">{{ $item['answer'] ?? '' }}</textarea></div>
    </div>
</div>
