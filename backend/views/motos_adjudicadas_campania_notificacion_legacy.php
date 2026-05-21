<?php
$campaignIdDefault = $campaign_id_default ?? ('camp_' . date('Ymd_His'));
?>

<style>
    .push-legacy-page {
        max-width: 980px;
        margin: 0 auto;
    }
    .push-legacy-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
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
    .recipient-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .recipient-panel__title {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .recipient-panel__icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), .1);
        flex: 0 0 auto;
    }
    .recipient-input-card {
        height: 100%;
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        background: var(--bs-body-bg);
        padding: .9rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .recipient-input-card:focus-within {
        border-color: rgba(var(--bs-primary-rgb), .65);
        box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .08);
    }
    .recipient-input-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .5rem;
    }
    .recipient-input-title label {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin: 0;
    }
    .recipient-textarea {
        min-height: 116px;
        resize: vertical;
        border-radius: 10px;
        background-color: var(--bs-body-bg);
    }
    .recipient-help {
        color: var(--bs-secondary-color);
        font-size: .78rem;
        margin-top: .5rem;
    }
    .recipient-total-badge {
        white-space: nowrap;
    }
    .recipient-search {
        position: relative;
    }
    .recipient-search-control {
        display: flex;
        align-items: center;
        gap: .6rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        background: var(--bs-body-bg);
        padding: .15rem .75rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .recipient-search-control:focus-within {
        border-color: rgba(var(--bs-primary-rgb), .65);
        box-shadow: 0 0 0 .2rem rgba(var(--bs-primary-rgb), .08);
    }
    .recipient-search-control input {
        border: 0;
        outline: 0;
        box-shadow: none;
        flex: 1 1 auto;
        min-width: 0;
        padding-left: 0;
        padding-right: 0;
        background: transparent;
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
    .campanias-especiales-box {
        display: none;
    }
    .campanias-especiales-box.show {
        display: block;
    }
    #campaniasEspecialesBox,
    #campaniasEspecialesBox.show {
        display: none;
    }
</style>

<div class="container-fluid push-legacy-page py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="mb-1">
                <i class="fa-solid fa-bell me-2 text-primary"></i>Campaña Notificación Legacy
            </h3>
            <p class="text-muted mb-0">Envío administrativo de campañas push para usuarios legacy.</p>
        </div>
    </div>

    <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
        <i class="fa-solid fa-circle-info mt-1"></i>
        <div>
            Envía avisos masivos a los usuarios de la app Legacy y dirige su atención al módulo correcto en el momento oportuno.
        </div>
    </div>

    <div class="card push-legacy-card">
        <div class="card-header bg-white">
            <strong>Datos de la campaña</strong>
        </div>
        <div class="card-body">
            <form id="pushLegacyForm" autocomplete="off">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="pushTitulo" class="form-label fw-semibold">Título</label>
                        <input type="text" class="form-control" id="pushTitulo" maxlength="120" required
                               placeholder="Aviso importante">
                    </div>

                    <div class="col-12">
                        <label for="pushMensaje" class="form-label fw-semibold">Mensaje</label>
                        <textarea class="form-control" id="pushMensaje" rows="4" maxlength="500" required
                                  placeholder="Escribe el mensaje que recibirá el usuario."></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Destinatarios</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="destino-card d-block h-100" for="destinoTodos">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pushDestino" id="destinoTodos" value="todos" checked>
                                        <span class="form-check-label fw-semibold">Todos los usuarios</span>
                                    </div>
                                    <div class="text-muted small mt-2">Se enviará a todos los usuarios de la app Legacy que puedan recibir notificaciones.</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="destino-card d-block h-100" for="destinoEspecificos">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pushDestino" id="destinoEspecificos" value="especificos">
                                        <span class="form-check-label fw-semibold">Usuarios específicos</span>
                                    </div>
                                    <div class="text-muted small mt-2">Úsalo solo si la campaña debe llegar a personas puntuales.</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="destino-card d-block h-100" for="destinoEspeciales">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pushDestino" id="destinoEspeciales" value="especiales">
                                        <span class="form-check-label fw-semibold">Campañas especiales</span>
                                    </div>
                                    <div class="text-muted small mt-2">Opción preparada para campañas con reglas especiales.</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 destinatarios-especificos" id="destinatariosEspecificosBox">
                        <div class="recipient-panel">
                            <div class="recipient-panel__header">
                                <div class="recipient-panel__title">
                                    <span class="recipient-panel__icon">
                                        <i class="fa-solid fa-user-check"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">Personas específicas</div>
                                        <div class="text-muted small">Agrega solo a quienes deben recibir esta campaña.</div>
                                    </div>
                                </div>
                                <span class="badge rounded-pill bg-primary-subtle text-primary recipient-total-badge" id="recipientTotalBadge">
                                    0 destinatarios
                                </span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <div class="recipient-input-card">
                                        <div class="recipient-input-title">
                                            <label for="recipientSearchInput" class="form-label fw-semibold">
                                                <i class="fa-solid fa-magnifying-glass text-primary"></i>
                                                Buscar por nombre o número
                                            </label>
                                        </div>
                                        <div class="recipient-search">
                                            <div class="recipient-search-control">
                                                <i class="fa-solid fa-user-plus text-muted"></i>
                                                <input type="text" class="form-control" id="recipientSearchInput"
                                                       placeholder="Ej. Raymundo, José Alberto o 999999704">
                                            </div>
                                            <div class="recipient-results" id="recipientSearchResults"></div>
                                        </div>
                                        <div class="recipient-chips" id="recipientSelectedList"></div>
                                        <div class="recipient-help">Busca, selecciona una o varias personas y se agregarán a la campaña.</div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="recipient-input-card">
                                        <div class="recipient-input-title">
                                            <label for="pushExternalIds" class="form-label fw-semibold">
                                                <i class="fa-solid fa-address-card text-primary"></i>
                                                Números de empleado
                                            </label>
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary" id="externalIdsCount">0</span>
                                        </div>
                                        <textarea class="form-control recipient-textarea" id="pushExternalIds" rows="4"
                                                  placeholder="Ej. 999999704"></textarea>
                                        <div class="recipient-help">Pega uno o varios números si ya los tienes a la mano.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 campanias-especiales-box" id="campaniasEspecialesBox">
                        <div class="alert alert-secondary mb-0">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                            Campañas especiales quedará lista para configurar reglas específicas.
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="btnResetPushLegacy">
                        <i class="fa-solid fa-rotate-left me-1"></i>Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnEnviarPushLegacy">
                        <i class="fa-solid fa-paper-plane me-1"></i>Enviar campaña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('pushLegacyForm');
    const titulo = document.getElementById('pushTitulo');
    const mensaje = document.getElementById('pushMensaje');
    const externalIds = document.getElementById('pushExternalIds');
    const externalIdsCount = document.getElementById('externalIdsCount');
    const recipientTotalBadge = document.getElementById('recipientTotalBadge');
    const recipientSearchInput = document.getElementById('recipientSearchInput');
    const recipientSearchResults = document.getElementById('recipientSearchResults');
    const recipientSelectedList = document.getElementById('recipientSelectedList');
    const btnEnviar = document.getElementById('btnEnviarPushLegacy');
    const destinoRadios = document.querySelectorAll('input[name="pushDestino"]');
    const destinatariosBox = document.getElementById('destinatariosEspecificosBox');
    const campaniasEspecialesBoxes = document.querySelectorAll('.campanias-especiales-box');
    const defaultCampaignId = <?= json_encode((string)$campaignIdDefault, JSON_UNESCAPED_SLASHES) ?>;
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
        const checked = document.querySelector('input[name="pushDestino"]:checked');
        return checked ? checked.value : 'todos';
    }

    function toggleDestinatarios() {
        destinatariosBox.classList.toggle('show', destinoActual() === 'especificos');
        campaniasEspecialesBoxes.forEach((box) => box.classList.toggle('show', destinoActual() === 'especiales'));
        actualizarResumenDestinatarios();
    }

    function externalIdsSeleccionados() {
        return Array.from(selectedRecipients.keys());
    }

    function externalIdsTotales() {
        return Array.from(new Set([...externalIdsSeleccionados(), ...splitIds(externalIds.value)]));
    }

    function actualizarResumenDestinatarios() {
        const totalEmpleados = splitIds(externalIds.value).length;
        const totalSeleccionados = externalIdsSeleccionados().length;
        const total = externalIdsTotales().length;
        if (externalIdsCount) externalIdsCount.textContent = String(totalEmpleados);
        if (recipientTotalBadge) {
            recipientTotalBadge.textContent = total === 1 ? '1 destinatario' : total + ' destinatarios';
        }
        renderSelectedRecipients();
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
        const envioEspecial = destinoActual() === 'especiales';
        return {
            titulo: titulo.value.trim(),
            mensaje: mensaje.value.trim(),
            segmento: 'all',
            user_id_legacy: [],
            external_id: envioEspecifico ? externalIdsTotales() : [],
            data: envioEspecial ? {
                type: 'aviso_especial',
                screen: 'NotificacionEspecial',
                campaign_id: defaultCampaignId
            } : {
                type: 'campaign',
                screen: 'Home',
                campaign_id: defaultCampaignId
            }
        };
    }

    destinoRadios.forEach((radio) => radio.addEventListener('change', toggleDestinatarios));
    [externalIds].forEach((field) => {
        field.addEventListener('input', actualizarResumenDestinatarios);
    });
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
        } catch (err) {
            return;
        }
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

    document.getElementById('btnResetPushLegacy').addEventListener('click', function () {
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

        if (destinoActual() === 'especiales' && false) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Campañas especiales', 'Esta opción ya está preparada. Falta definir la funcionalidad antes de enviar.', 'info');
            }
            return;
        }

        const totalDestinatarios = payload.external_id.length;
        let textoConfirmacion = destinoActual() === 'especificos'
            ? 'Se enviará la campaña a ' + totalDestinatarios + ' destinatario(s) indicado(s).'
            : 'Se enviará la campaña a todos los usuarios de la app Legacy que puedan recibir notificaciones.';

        if (destinoActual() === 'especiales') {
            textoConfirmacion = 'Se enviará una campaña especial a todos los usuarios de la app Legacy que puedan recibir notificaciones.';
        }

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
            const resp = await fetch('/MotosAdjudicadas/enviarCampaniaNotificacionLegacy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok || !data.success) {
                throw new Error(data.message || 'No se pudo enviar la campaña.');
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire('Campaña enviada', data.message || 'La campaña se envió correctamente.', 'success');
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', err.message || 'No se pudo enviar la campaña.', 'error');
            }
        } finally {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar campaña';
        }
    });

    toggleDestinatarios();
})();
</script>
