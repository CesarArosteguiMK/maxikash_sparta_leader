<?php
$campaignIdDefault = $campaign_id_default ?? ('feedback_' . date('Ymd_His'));
?>

<style>
    .legacy-feedback-page {
        max-width: 980px;
        margin: 0 auto;
    }
    .legacy-feedback-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }
    .feedback-hero {
        border: 1px solid rgba(var(--bs-primary-rgb), .18);
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .10), rgba(14, 159, 110, .08));
        padding: 1rem;
    }
    .feedback-hero-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), .12);
        flex: 0 0 auto;
    }
    .destino-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 1rem;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .destino-card:has(.form-check-input:checked) {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), .06);
    }
    .destinatarios-especificos {
        display: none;
    }
    .destinatarios-especificos.show {
        display: block;
    }
    .recipient-panel {
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        background: linear-gradient(180deg, rgba(var(--bs-primary-rgb), .04), var(--bs-body-bg) 42%);
        padding: 1rem;
    }
    .recipient-search {
        position: relative;
    }
    .recipient-results {
        display: none;
        position: absolute;
        z-index: 20;
        left: 0;
        right: 0;
        top: calc(100% + .4rem);
        max-height: 270px;
        overflow: auto;
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        background: var(--bs-body-bg);
        box-shadow: 0 18px 36px rgba(15, 23, 42, .14);
        padding: .35rem;
    }
    .recipient-results.show {
        display: block;
    }
    .recipient-result-item {
        width: 100%;
        border: 0;
        border-radius: 8px;
        background: transparent;
        text-align: left;
        padding: .65rem .75rem;
        color: var(--bs-body-color);
    }
    .recipient-result-item:hover {
        background: rgba(var(--bs-primary-rgb), .08);
    }
    .recipient-result-name {
        font-weight: 700;
        line-height: 1.2;
    }
    .recipient-result-meta {
        color: var(--bs-secondary-color);
        font-size: .78rem;
        margin-top: .15rem;
    }
    .recipient-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        min-height: 2rem;
        margin-top: .75rem;
    }
    .recipient-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        max-width: 100%;
        border: 1px solid rgba(var(--bs-primary-rgb), .18);
        border-radius: 999px;
        background: rgba(var(--bs-primary-rgb), .08);
        color: var(--bs-primary);
        padding: .35rem .5rem .35rem .75rem;
        font-size: .82rem;
        font-weight: 700;
    }
    .recipient-chip span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .recipient-chip button {
        border: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--bs-primary-rgb), .12);
        color: var(--bs-primary);
        padding: 0;
        line-height: 1;
    }
</style>

<div class="container-fluid legacy-feedback-page py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="mb-1">
                <i class="fa-solid fa-comments me-2 text-primary"></i>Comentarios Legacy
            </h3>
            <p class="text-muted mb-0">Campaña para pedir opinión de la app Legacy a los usuarios.</p>
        </div>
    </div>

    <div class="feedback-hero d-flex align-items-start gap-3 mb-4">
        <span class="feedback-hero-icon">
            <i class="fa-solid fa-star-half-stroke"></i>
        </span>
        <div>
            <div class="fw-bold mb-1">Esta campaña abre el flujo de opinión en la app.</div>
            <div class="text-muted small">
                La notificación se envía por versión de aplicación si capturas una versión. Los usuarios que ya dieron su opinión no volverán a ver el modal.
            </div>
        </div>
    </div>

    <div class="card legacy-feedback-card">
        <div class="card-header bg-white">
            <strong>Datos de la campaña de comentarios</strong>
        </div>
        <div class="card-body">
            <form id="legacyFeedbackCampaignForm" autocomplete="off">
                <input type="hidden" id="feedbackCampaignId" value="<?= htmlspecialchars((string)$campaignIdDefault, ENT_QUOTES, 'UTF-8') ?>">

                <div class="row g-3">
                    <input type="hidden" id="feedbackTitulo" value="Ayúdanos a mejorar">
                    <input type="hidden" id="feedbackMensaje" value="Tu opinión es muy importante para nosotros.">

                    <div class="col-12">
                        <label for="feedbackAppVersion" class="form-label fw-semibold">Versión de aplicación</label>
                        <input type="text" class="form-control" id="feedbackAppVersion" maxlength="50"
                               placeholder="Ej. 1.3.5">
                        <div class="form-text">Si lo dejas vacío, se enviará a todos los usuarios activos de todas las versiones instaladas.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Destinatarios</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="destino-card d-block h-100" for="feedbackDestinoTodos">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="feedbackDestino" id="feedbackDestinoTodos" value="todos" checked>
                                        <span class="form-check-label fw-semibold">Todos los usuarios</span>
                                    </div>
                                    <div class="text-muted small mt-2">Si no capturas versión, se enviará a todos los usuarios activos de todas las versiones.</div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="destino-card d-block h-100" for="feedbackDestinoEspecificos">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="feedbackDestino" id="feedbackDestinoEspecificos" value="especificos">
                                        <span class="form-check-label fw-semibold">Usuarios específicos</span>
                                    </div>
                                    <div class="text-muted small mt-2">Úsalo para enviar la campaña a una o varias personas puntuales.</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 destinatarios-especificos" id="feedbackDestinatariosBox">
                        <div class="recipient-panel">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <div class="fw-semibold">Personas específicas</div>
                                    <div class="text-muted small">Busca por nombre o número de empleado.</div>
                                </div>
                                <span class="badge rounded-pill bg-primary-subtle text-primary" id="feedbackRecipientTotalBadge">0 destinatarios</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label for="feedbackRecipientSearchInput" class="form-label fw-semibold">Buscar persona</label>
                                    <div class="recipient-search">
                                        <input type="text" class="form-control" id="feedbackRecipientSearchInput"
                                               placeholder="Ej. Raymundo, José Alberto o 999999704">
                                        <div class="recipient-results" id="feedbackRecipientSearchResults"></div>
                                    </div>
                                    <div class="recipient-chips" id="feedbackRecipientSelectedList"></div>
                                </div>
                                <div class="col-md-5">
                                    <label for="feedbackExternalIds" class="form-label fw-semibold">Números de empleado</label>
                                    <textarea class="form-control" id="feedbackExternalIds" rows="4" placeholder="Ej. 999999704"></textarea>
                                    <div class="form-text">Puedes pegar varios separados por coma, espacio o salto de línea.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="btnResetFeedbackCampaign">
                        <i class="fa-solid fa-rotate-left me-1"></i>Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnEnviarFeedbackCampaign">
                        <i class="fa-solid fa-paper-plane me-1"></i>Enviar campaña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('legacyFeedbackCampaignForm');
    const titulo = document.getElementById('feedbackTitulo');
    const mensaje = document.getElementById('feedbackMensaje');
    const appVersion = document.getElementById('feedbackAppVersion');
    const campaignId = document.getElementById('feedbackCampaignId');
    const externalIds = document.getElementById('feedbackExternalIds');
    const recipientTotalBadge = document.getElementById('feedbackRecipientTotalBadge');
    const recipientSearchInput = document.getElementById('feedbackRecipientSearchInput');
    const recipientSearchResults = document.getElementById('feedbackRecipientSearchResults');
    const recipientSelectedList = document.getElementById('feedbackRecipientSelectedList');
    const btnEnviar = document.getElementById('btnEnviarFeedbackCampaign');
    const destinatariosBox = document.getElementById('feedbackDestinatariosBox');

    if (!recipientSearchInput || !recipientSearchResults || !recipientSelectedList || !destinatariosBox) {
        const resetButton = document.getElementById('btnResetFeedbackCampaign');

        function buildDefaultPayload() {
            return {
                titulo: titulo.value.trim(),
                mensaje: mensaje.value.trim(),
                app_version: appVersion.value.trim(),
                campaign_id: campaignId.value.trim(),
                user_id_legacy: [],
                external_id: []
            };
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                form.reset();
                appVersion.value = '';
            });
        }

        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            const payload = buildDefaultPayload();

            const textoConfirmacion = payload.app_version
                ? 'Se enviará la campaña de comentarios a todos los usuarios activos de la versión ' + payload.app_version + '.'
                : 'Se enviará la campaña de comentarios a todos los usuarios activos de todas las versiones instaladas.';
            if (typeof Swal !== 'undefined') {
                const confirm = await Swal.fire({
                    icon: 'question',
                    title: 'Confirmar envío',
                    text: textoConfirmacion,
                    showCancelButton: true,
                    confirmButtonText: 'Enviar campaña',
                    cancelButtonText: 'Cancelar'
                });
                if (!confirm.isConfirmed) return;
            } else if (!window.confirm(textoConfirmacion)) {
                return;
            }

            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

            try {
                const resp = await fetch('/MotosAdjudicadas/enviarComentariosLegacy', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await resp.json().catch(() => ({}));
                if (!resp.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo enviar la campaña de comentarios.');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Campaña enviada', data.message || 'La campaña se envió correctamente.', 'success');
                }
            } catch (err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', err.message || 'No se pudo enviar la campaña de comentarios.', 'error');
                }
            } finally {
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar campaña';
            }
        });

        return;
    }

    const selectedRecipients = new Map();
    let searchTimer = null;
    let searchRequestId = 0;

    function splitIds(value) {
        return String(value || '')
            .split(/[\s,;]+/)
            .map((item) => item.trim())
            .filter(Boolean)
            .filter((item, index, arr) => arr.indexOf(item) === index);
    }

    function destinoActual() {
        const checked = document.querySelector('input[name="feedbackDestino"]:checked');
        return checked ? checked.value : 'todos';
    }

    function externalIdsSeleccionados() {
        return Array.from(selectedRecipients.keys());
    }

    function externalIdsTotales() {
        return Array.from(new Set([...externalIdsSeleccionados(), ...splitIds(externalIds.value)]));
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function renderSelectedRecipients() {
        if (!recipientSelectedList) return;
        if (selectedRecipients.size === 0) {
            recipientSelectedList.innerHTML = '<span class="text-muted small">Sin personas seleccionadas.</span>';
            return;
        }
        recipientSelectedList.innerHTML = Array.from(selectedRecipients.values()).map((item) => `
            <span class="recipient-chip" title="${escapeHtml(item.nombre)}">
                <span>${escapeHtml(item.nombre)} · ${escapeHtml(item.numero_empleado)}</span>
                <button type="button" data-remove-recipient="${escapeHtml(item.numero_empleado)}" aria-label="Quitar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </span>
        `).join('');
    }

    function actualizarResumenDestinatarios() {
        const total = externalIdsTotales().length;
        if (recipientTotalBadge) {
            recipientTotalBadge.textContent = total === 1 ? '1 destinatario' : total + ' destinatarios';
        }
        renderSelectedRecipients();
    }

    function toggleDestinatarios() {
        destinatariosBox.classList.toggle('show', destinoActual() === 'especificos');
        actualizarResumenDestinatarios();
    }

    function renderSearchResults(rows, message = '') {
        if (!recipientSearchResults) return;
        if (message) {
            recipientSearchResults.innerHTML = '<div class="text-muted small px-2 py-2">' + escapeHtml(message) + '</div>';
            recipientSearchResults.classList.add('show');
            return;
        }
        if (!rows.length) {
            recipientSearchResults.innerHTML = '<div class="text-muted small px-2 py-2">Sin resultados.</div>';
            recipientSearchResults.classList.add('show');
            return;
        }
        recipientSearchResults.innerHTML = rows.map((row) => {
            const external = String(row.numero_empleado || '').trim();
            const selected = selectedRecipients.has(external);
            const puesto = row.puesto ? ' · ' + row.puesto : '';
            const legacy = row.user_id_legacy ? ' · Legacy #' + row.user_id_legacy : '';
            return `
                <button type="button" class="recipient-result-item" data-recipient='${escapeHtml(JSON.stringify(row))}' ${selected ? 'disabled' : ''}>
                    <div class="recipient-result-name">${escapeHtml(row.nombre || 'Sin nombre')}</div>
                    <div class="recipient-result-meta">#${escapeHtml(external)}${escapeHtml(puesto)}${escapeHtml(legacy)}</div>
                </button>
            `;
        }).join('');
        recipientSearchResults.classList.add('show');
    }

    async function buscarDestinatarios() {
        const q = recipientSearchInput.value.trim();
        if (q.length < 2) {
            recipientSearchResults.classList.remove('show');
            recipientSearchResults.innerHTML = '';
            return;
        }
        const currentRequest = ++searchRequestId;
        renderSearchResults([], 'Buscando...');
        try {
            const resp = await fetch('/MotosAdjudicadas/buscarDestinatariosCampaniaLegacy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ buscar: q })
            });
            const data = await resp.json().catch(() => ({}));
            if (currentRequest !== searchRequestId) return;
            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'No se pudo buscar.');
            }
            renderSearchResults(Array.isArray(data.datos) ? data.datos : []);
        } catch (err) {
            if (currentRequest !== searchRequestId) return;
            renderSearchResults([], err.message || 'No se pudo buscar.');
        }
    }

    function buildPayload() {
        const envioEspecifico = destinoActual() === 'especificos';
        return {
            titulo: titulo.value.trim(),
            mensaje: mensaje.value.trim(),
            app_version: appVersion.value.trim(),
            campaign_id: campaignId.value.trim(),
            user_id_legacy: [],
            external_id: envioEspecifico ? externalIdsTotales() : []
        };
    }

    document.querySelectorAll('input[name="feedbackDestino"]').forEach((radio) => {
        radio.addEventListener('change', toggleDestinatarios);
    });
    externalIds.addEventListener('input', actualizarResumenDestinatarios);
    recipientSearchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(buscarDestinatarios, 250);
    });
    recipientSearchInput.addEventListener('focus', function () {
        if (recipientSearchInput.value.trim().length >= 2 && recipientSearchResults.innerHTML.trim() !== '') {
            recipientSearchResults.classList.add('show');
        }
    });
    recipientSearchResults.addEventListener('click', function (ev) {
        const btn = ev.target.closest('.recipient-result-item');
        if (!btn || btn.disabled) return;
        try {
            const item = JSON.parse(btn.getAttribute('data-recipient') || '{}');
            const external = String(item.numero_empleado || '').trim();
            if (!external) return;
            selectedRecipients.set(external, item);
            recipientSearchInput.value = '';
            recipientSearchResults.classList.remove('show');
            recipientSearchResults.innerHTML = '';
            actualizarResumenDestinatarios();
        } catch (err) {}
    });
    recipientSelectedList.addEventListener('click', function (ev) {
        const btn = ev.target.closest('[data-remove-recipient]');
        if (!btn) return;
        selectedRecipients.delete(btn.getAttribute('data-remove-recipient'));
        actualizarResumenDestinatarios();
    });
    document.addEventListener('click', function (ev) {
        if (!ev.target.closest('.recipient-search')) {
            recipientSearchResults.classList.remove('show');
        }
    });

    document.getElementById('btnResetFeedbackCampaign').addEventListener('click', function () {
        form.reset();
        selectedRecipients.clear();
        recipientSearchResults.classList.remove('show');
        recipientSearchResults.innerHTML = '';
        toggleDestinatarios();
    });

    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        const payload = buildPayload();

        if (!payload.titulo || !payload.mensaje) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Campos requeridos', 'Escribe título y mensaje antes de enviar.', 'warning');
            }
            return;
        }

        if (destinoActual() === 'especificos' && payload.external_id.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Destinatarios requeridos', 'Busca una persona o captura al menos un número de empleado.', 'warning');
            }
            return;
        }

        const textoConfirmacion = destinoActual() === 'especificos'
            ? 'Se enviará la campaña de comentarios a ' + payload.external_id.length + ' destinatario(s).'
            : (payload.app_version
                ? 'Se enviará la campaña de comentarios a todos los usuarios activos de la versión ' + payload.app_version + '.'
                : 'Se enviará la campaña de comentarios a todos los usuarios activos de todas las versiones instaladas.');

        if (typeof Swal !== 'undefined') {
            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Confirmar envío',
                text: textoConfirmacion,
                showCancelButton: true,
                confirmButtonText: 'Enviar campaña',
                cancelButtonText: 'Cancelar'
            });
            if (!confirm.isConfirmed) return;
        } else if (!window.confirm(textoConfirmacion)) {
            return;
        }

        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

        try {
            const resp = await fetch('/MotosAdjudicadas/enviarComentariosLegacy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'No se pudo enviar la campaña de comentarios.');
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire('Campaña enviada', data.message || 'La campaña se envió correctamente.', 'success');
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', err.message || 'No se pudo enviar la campaña de comentarios.', 'error');
            }
        } finally {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar campaña';
        }
    });

    toggleDestinatarios();
})();
</script>
