{{-- Shared visual kit for the event create/edit forms: dropzones, choice cards, switch row. --}}
@once
@push('styles')
<style>
    .event-form .form-group { margin-bottom: 18px; }
    .event-form .form-label { align-items: center; display: flex; gap: 6px; }
    .event-form .form-label > i { color: var(--app-gold); font-size: .85rem; }
    .event-form .form-text { display: block; }
    .event-form #socialLinksList { gap: 10px !important; }
    .event-form #addSocialLink { border-color: rgba(23, 23, 19, .15); border-radius: 999px; font-size: .76rem; font-weight: 600; }
    .event-form .remove-social-link { align-items: center; border-color: transparent; border-radius: 10px; display: inline-flex; height: 40px; justify-content: center; width: 40px; }
    .event-form #venueSuggestions { border-radius: 12px; box-shadow: 0 20px 40px -16px rgba(23, 23, 19, .3); margin-top: 6px; overflow: hidden; }

    /* Dropzones */
    .dropzone { align-items: center; background: #fbfaf7; border: 1.5px dashed rgba(23, 23, 19, .18); border-radius: 16px; cursor: pointer; display: flex; gap: 14px; margin: 0; padding: 10px 12px; position: relative; transition: border-color .2s, background .2s; }
    .dropzone:hover, .dropzone.is-over { background: #fff; border-color: var(--app-gold); }
    .dropzone.has-file { border-style: solid; border-color: rgba(46, 123, 101, .35); }
    .dropzone input[type=file] { cursor: pointer; inset: 0; opacity: 0; position: absolute; width: 100%; }
    .dz-icon { align-items: center; background: var(--app-gold-soft); border-radius: 10px; color: var(--app-gold-deep); display: flex; flex: 0 0 38px; font-size: 1.05rem; height: 38px; justify-content: center; overflow: hidden; width: 38px; }
    .dz-icon img { height: 100%; object-fit: cover; width: 100%; }
    .dz-text { flex: 1; }
    .dz-text strong { color: var(--app-ink); display: block; font-size: .88rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dz-text small { color: var(--app-muted); display: block; font-size: .76rem; margin-top: 2px; }
    .dz-action { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 999px; color: var(--app-ink); font-size: .76rem; font-weight: 600; padding: 6px 12px; white-space: nowrap; }

    /* Choice cards */
    .choice-grid { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
    .choice { cursor: pointer; display: block; margin: 0; position: relative; }
    .choice input { opacity: 0; pointer-events: none; position: absolute; }
    .choice-body { background: #fff; border: 1px solid rgba(23, 23, 19, .13); border-radius: 12px; display: flex; flex-direction: column; gap: 3px; height: 100%; padding: 12px 14px; transition: border-color .2s, background .2s, box-shadow .2s; }
    .choice-body i { color: var(--app-gold-deep); font-size: 1rem; margin-bottom: 4px; }
    .choice-body strong { color: var(--app-ink); font-size: .86rem; font-weight: 650; }
    .choice-body small { color: var(--app-muted); font-size: .78rem; line-height: 1.4; }
    .choice:hover .choice-body { border-color: rgba(23, 23, 19, .22); }
    .choice input:checked + .choice-body { background: #fff; border-color: var(--app-ink); box-shadow: 0 0 0 3px rgba(23, 23, 19, .06); }
    .choice input:checked + .choice-body::after { align-items: center; background: var(--app-ink); border-radius: 50%; color: #fff; content: "\F26E"; display: flex; font-family: "bootstrap-icons"; font-size: .8rem; height: 20px; justify-content: center; position: absolute; right: 12px; top: 12px; width: 20px; }
    .choice input:focus-visible + .choice-body { box-shadow: 0 0 0 4px var(--app-gold-soft); }
    .choice.is-invalid .choice-body { border-color: rgba(164, 81, 74, .45); }

    .switch-row { align-items: center; background: #fff; border: 1px solid rgba(23, 23, 19, .13); border-radius: 10px; display: flex; gap: 12px; justify-content: space-between; min-height: 40px; padding: 6px 12px; }
    .switch-row .form-check { margin: 0; }
    .switch-row .form-check-input { cursor: pointer; }

    @media (max-width: 575.98px) { .event-form .form-group > .d-flex.justify-content-between { align-items: flex-start !important; flex-direction: column; } }
</style>
@endpush
@endonce
