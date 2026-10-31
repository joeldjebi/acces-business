{{--
    Popup de confirmation réutilisable (chargé une fois dans le layout).

    Sur un formulaire :
        <form ... data-confirm="Ce message s'affiche dans le popup."
                  data-confirm-title="Supprimer l'événement ?"   (optionnel)
                  data-confirm-button="Supprimer"                 (optionnel, défaut : Supprimer)
                  data-confirm-tone="danger|primary">             (optionnel, défaut : danger)

    En JavaScript :
        if (await window.appConfirm({ title, message, confirmLabel, tone })) { ... }
--}}
<style>
    .confirm-dialog .modal-dialog { max-width: 420px; }
    .confirm-dialog .modal-body { padding: 24px 24px 20px; }
    .confirm-icon { align-items: center; border-radius: 50%; display: inline-flex; font-size: 1.2rem; height: 44px; justify-content: center; margin-bottom: 14px; width: 44px; }
    .confirm-dialog[data-tone="danger"] .confirm-icon { background: rgba(164, 81, 74, .1); color: var(--app-red); }
    .confirm-dialog[data-tone="primary"] .confirm-icon { background: var(--app-soft); color: var(--app-ink); }
    .confirm-dialog h2 { color: var(--app-ink); font-size: 1.05rem; font-weight: 650; margin: 0 0 6px; }
    .confirm-dialog p { color: var(--app-muted); font-size: .9rem; line-height: 1.5; margin: 0; }
    .confirm-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
    .confirm-dialog[data-tone="danger"] [data-confirm-ok] { background: var(--app-red); color: #fff; }
    .confirm-dialog[data-tone="danger"] [data-confirm-ok]:hover { background: #8a3d36; }
</style>

<div class="modal fade confirm-dialog" id="appConfirm" tabindex="-1" aria-labelledby="appConfirmTitle" aria-describedby="appConfirmMessage" aria-hidden="true" data-tone="danger">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <span class="confirm-icon" aria-hidden="true"><i class="bi bi-trash3" data-confirm-icon></i></span>
                <h2 id="appConfirmTitle" data-confirm-title></h2>
                <p id="appConfirmMessage" data-confirm-message></p>
                <div class="confirm-actions">
                    <button type="button" class="ui-btn ui-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="ui-btn ui-btn-primary" data-confirm-ok></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const el = document.getElementById('appConfirm');
        if (!el || !window.bootstrap) return;
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        const ok = el.querySelector('[data-confirm-ok]');
        let resolver = null;

        const settle = (value) => { if (resolver) { resolver(value); resolver = null; } };
        ok.addEventListener('click', () => { settle(true); modal.hide(); });
        el.addEventListener('hidden.bs.modal', () => settle(false));
        el.addEventListener('shown.bs.modal', () => ok.focus());

        window.appConfirm = ({ title = 'Confirmer la suppression ?', message = '', confirmLabel = 'Supprimer', tone = 'danger' } = {}) => {
            settle(false);
            el.dataset.tone = tone;
            el.querySelector('[data-confirm-icon]').className = 'bi ' + (tone === 'danger' ? 'bi-trash3' : 'bi-question-lg');
            el.querySelector('[data-confirm-title]').textContent = title;
            el.querySelector('[data-confirm-message]').textContent = message;
            ok.textContent = confirmLabel;
            modal.show();
            return new Promise((resolve) => { resolver = resolve; });
        };

        // Formulaires marqués data-confirm : on bloque l'envoi, on demande, puis on renvoie le formulaire.
        document.addEventListener('submit', async (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
            if (form.dataset.confirmed === '1') { delete form.dataset.confirmed; return; }
            event.preventDefault();
            event.stopImmediatePropagation();
            const confirmed = await window.appConfirm({
                title: form.dataset.confirmTitle,
                message: form.dataset.confirm,
                confirmLabel: form.dataset.confirmButton,
                tone: form.dataset.confirmTone,
            });
            if (!confirmed) return;
            form.dataset.confirmed = '1';
            form.requestSubmit(event.submitter || undefined);
        }, true);
    })();
</script>
