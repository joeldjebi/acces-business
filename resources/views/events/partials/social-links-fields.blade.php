@php
    $currentEvent = $event ?? null;
    $socialLinks = old('social_links', $currentEvent?->social_links ?? []);
    $socialLinks = count($socialLinks) ? $socialLinks : [['label' => '', 'url' => '']];
@endphp

<div class="form-group">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
        <label class="form-label mb-0">
            <i class="bi bi-share"></i>
            Réseaux sociaux et liens utiles <span class="text-muted">(facultatif)</span>
        </label>
        <button type="button" class="btn btn-outline-dark btn-sm" id="addSocialLink">
            <i class="bi bi-plus-lg me-1"></i>Ajouter un lien
        </button>
    </div>

    <div id="socialLinksList" class="d-grid gap-2" data-next-index="{{ count($socialLinks) }}">
        @foreach($socialLinks as $index => $link)
            <div class="social-link-row row g-2 align-items-start">
                <div class="col-md-4">
                    <input type="text"
                           class="form-control @error("social_links.$index.label") is-invalid @enderror"
                           name="social_links[{{ $index }}][label]"
                           value="{{ $link['label'] ?? '' }}"
                           maxlength="100"
                           placeholder="Ex. LinkedIn">
                    @error("social_links.$index.label")
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-7">
                    <input type="url"
                           class="form-control @error("social_links.$index.url") is-invalid @enderror"
                           name="social_links[{{ $index }}][url]"
                           value="{{ $link['url'] ?? '' }}"
                           maxlength="1000"
                           placeholder="https://...">
                    @error("social_links.$index.url")
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-1 d-flex justify-content-md-end">
                    <button type="button" class="btn btn-outline-danger remove-social-link" title="Supprimer ce lien" aria-label="Supprimer ce lien">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
    <small class="form-text text-muted">Indiquez librement le libellé et l’adresse de chaque réseau ou lien.</small>
</div>

<template id="socialLinkTemplate">
    <div class="social-link-row row g-2 align-items-start">
        <div class="col-md-4">
            <input type="text" class="form-control social-label" maxlength="100" placeholder="Ex. LinkedIn">
        </div>
        <div class="col-md-7">
            <input type="url" class="form-control social-url" maxlength="1000" placeholder="https://...">
        </div>
        <div class="col-md-1 d-flex justify-content-md-end">
            <button type="button" class="btn btn-outline-danger remove-social-link" title="Supprimer ce lien" aria-label="Supprimer ce lien">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    (() => {
        const list = document.getElementById('socialLinksList');
        const addButton = document.getElementById('addSocialLink');
        const template = document.getElementById('socialLinkTemplate');

        function bindRemoveButton(button) {
            button.addEventListener('click', () => {
                const rows = list.querySelectorAll('.social-link-row');
                const row = button.closest('.social-link-row');

                if (rows.length === 1) {
                    row.querySelectorAll('input').forEach(input => input.value = '');
                    return;
                }

                row.remove();
            });
        }

        list.querySelectorAll('.remove-social-link').forEach(bindRemoveButton);

        addButton.addEventListener('click', () => {
            const index = Number(list.dataset.nextIndex || 0);
            const fragment = template.content.cloneNode(true);
            fragment.querySelector('.social-label').name = `social_links[${index}][label]`;
            fragment.querySelector('.social-url').name = `social_links[${index}][url]`;
            bindRemoveButton(fragment.querySelector('.remove-social-link'));
            list.appendChild(fragment);
            list.dataset.nextIndex = index + 1;
        });
    })();
</script>
@endpush
