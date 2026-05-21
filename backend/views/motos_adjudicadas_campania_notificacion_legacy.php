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
                        <div class="border rounded p-3 bg-light">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="pushUserIds" class="form-label fw-semibold">IDs de usuario legacy</label>
                                    <textarea class="form-control" id="pushUserIds" rows="4"
                                              placeholder="1160, 1133"></textarea>
                                    <div class="form-text">Separar por coma, espacio o salto de línea.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="pushExternalIds" class="form-label fw-semibold">Números de empleado</label>
                                    <textarea class="form-control" id="pushExternalIds" rows="4"
                                              placeholder="999999704"></textarea>
                                    <div class="form-text">Opcional si ya capturaste IDs de usuario legacy.</div>
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
    const userIds = document.getElementById('pushUserIds');
    const externalIds = document.getElementById('pushExternalIds');
    const btnEnviar = document.getElementById('btnEnviarPushLegacy');
    const destinoRadios = document.querySelectorAll('input[name="pushDestino"]');
    const destinatariosBox = document.getElementById('destinatariosEspecificosBox');
    const campaniasEspecialesBoxes = document.querySelectorAll('.campanias-especiales-box');
    const defaultCampaignId = <?= json_encode((string)$campaignIdDefault, JSON_UNESCAPED_SLASHES) ?>;

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
    }

    function buildPayload() {
        const envioEspecifico = destinoActual() === 'especificos';
        const envioEspecial = destinoActual() === 'especiales';
        return {
            titulo: titulo.value.trim(),
            mensaje: mensaje.value.trim(),
            segmento: 'all',
            user_id_legacy: envioEspecifico ? splitIds(userIds.value) : [],
            external_id: envioEspecifico ? splitIds(externalIds.value) : [],
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

    document.getElementById('btnResetPushLegacy').addEventListener('click', function () {
        form.reset();
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

        if (destinoActual() === 'especificos' && payload.user_id_legacy.length + payload.external_id.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Destinatarios requeridos', 'Captura al menos un usuario legacy o número de empleado.', 'warning');
            }
            return;
        }

        if (destinoActual() === 'especiales' && false) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Campañas especiales', 'Esta opción ya está preparada. Falta definir la funcionalidad antes de enviar.', 'info');
            }
            return;
        }

        const totalDestinatarios = payload.user_id_legacy.length + payload.external_id.length;
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
