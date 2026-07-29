<?php
$tablasDisponibles = !empty($solicitudes_tablas_disponibles);
?>
<style>
.sa-atc { --sa-blue:#2457d6; --sa-ink:#172033; --sa-muted:#667085; }
.sa-atc .sa-hero { background:linear-gradient(135deg,#172554 0%,#2457d6 62%,#38bdf8 100%); color:#fff; border:0; overflow:hidden; }
.sa-atc .sa-kicker { font-size:.74rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; opacity:.78; }
.sa-atc .sa-panel { border:1px solid #e5e7eb; box-shadow:0 12px 30px rgba(15,23,42,.06); }
.sa-atc .sa-step { width:30px; height:30px; display:inline-grid; place-items:center; border-radius:50%; background:#e8efff; color:var(--sa-blue); font-weight:800; }
.sa-atc .sa-credit { border-left:4px solid var(--sa-blue); background:#f8faff; }
.sa-atc .sa-status { display:inline-flex; align-items:center; border-radius:999px; padding:.28rem .65rem; background:#e8efff; color:#2446a8; font-size:.75rem; font-weight:800; text-transform:uppercase; }
.sa-atc .sa-empty { padding:2.5rem 1rem; text-align:center; color:var(--sa-muted); }
.sa-atc .sa-required::after { content:' *'; color:#dc2626; }
.sa-atc .table > :not(caption) > * > * { vertical-align:middle; }
</style>

<div class="sa-atc container-fluid py-3">
    <div class="card sa-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="sa-kicker mb-2">ATC · Etapa 1</div>
            <h3 class="text-white mb-2">Solicitud de Adjudicación</h3>
            <p class="mb-0 opacity-75">Registra la intención de entrega antes de iniciar el pipeline operativo de Motos Adjudicadas.</p>
        </div>
    </div>

    <?php if (!$tablasDisponibles): ?>
    <div class="alert alert-warning d-flex gap-3 align-items-start" role="alert">
        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
        <div><strong>Modulo preparado, migracion pendiente.</strong><br>Debe aplicarse <code>scripts/migration_solicitudes_adjudicacion_etapa1.sql</code> antes de registrar solicitudes.</div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card sa-panel h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0"><span class="sa-step me-2">1</span>Buscar credito</h5>
                </div>
                <div class="card-body">
                    <form id="sa-form" novalidate>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="number" min="1" class="form-control" id="sa-id-credito" placeholder="ID de credito" required>
                            <button class="btn btn-primary" type="button" id="sa-buscar"><i class="fa-solid fa-magnifying-glass me-1"></i>Buscar</button>
                        </div>

                        <div id="sa-credito" class="sa-credit rounded p-3 mb-4 d-none"></div>

                        <div id="sa-captura" class="d-none">
                            <h5 class="mb-3"><span class="sa-step me-2">2</span>Datos de la entrega</h5>
                            <div class="mb-3">
                                <label class="form-label sa-required">¿Entregara el titular?</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check"><input class="form-check-input" type="radio" name="entregara_titular" id="sa-titular-si" value="1"><label class="form-check-label" for="sa-titular-si">Si</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" name="entregara_titular" id="sa-titular-no" value="0"><label class="form-check-label" for="sa-titular-no">No</label></div>
                                </div>
                            </div>

                            <div id="sa-tercero" class="d-none">
                                <div class="mb-3"><label class="form-label sa-required" for="sa-entregante">Nombre de quien entrega</label><input class="form-control" id="sa-entregante" maxlength="180"></div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label sa-required" for="sa-km">Kilometraje</label><input type="number" min="0" max="999999" class="form-control" id="sa-km"></div>
                                    <div class="col-md-6"><label class="form-label sa-required" for="sa-telefono">Telefono actual</label><input type="tel" class="form-control" id="sa-telefono" maxlength="20" placeholder="10 a 15 digitos"></div>
                                </div>
                                <div class="mt-3"><label class="form-label sa-required" for="sa-direccion">Direccion actual de resguardo</label><textarea class="form-control" id="sa-direccion" maxlength="500" rows="2"></textarea></div>
                                <div class="mt-3"><label class="form-label sa-required" for="sa-motivo">Motivo de la solicitud</label><textarea class="form-control" id="sa-motivo" maxlength="1000" rows="3"></textarea></div>
                            </div>

                            <div id="sa-errors" class="alert alert-danger mt-3 d-none"></div>
                            <button type="submit" class="btn btn-primary w-100 mt-4" id="sa-guardar" <?= $tablasDisponibles ? '' : 'disabled'; ?>>
                                <i class="fa-solid fa-paper-plane me-2"></i>Registrar solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card sa-panel">
                <div class="card-header border-bottom d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div><h5 class="mb-1"><span class="sa-step me-2">3</span>Mis solicitudes</h5><small class="text-muted">Estatus e historial de solicitudes creadas desde ATC.</small></div>
                    <div class="input-group" style="max-width:280px"><input id="sa-filtro" class="form-control" placeholder="Folio, credito o cliente"><button id="sa-refrescar" class="btn btn-outline-primary" type="button"><i class="fa-solid fa-rotate"></i></button></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Folio</th><th>Credito / Cliente</th><th>Entrega</th><th>Estatus</th><th>Fecha</th><th></th></tr></thead>
                        <tbody id="sa-lista"><tr><td colspan="6" class="sa-empty">Cargando solicitudes...</td></tr></tbody>
                    </table>
                </div>
            </div>

            <div class="card sa-panel mt-4 d-none" id="sa-detalle-card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center"><h5 class="mb-0">Detalle e historial</h5><button type="button" class="btn-close" id="sa-cerrar-detalle" aria-label="Cerrar"></button></div>
                <div class="card-body" id="sa-detalle"></div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';
    const tablasDisponibles = <?= $tablasDisponibles ? 'true' : 'false'; ?>;
    const $ = (id) => document.getElementById(id);
    let creditoActual = null;
    let idempotencyKey = null;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const notify = (icon, title, text = '') => window.Swal
        ? Swal.fire({icon, title, text, confirmButtonText:'Aceptar'})
        : alert(title + (text ? '\n' + text : ''));

    async function json(url, options = {}) {
        const response = await fetch(url, {headers:{Accept:'application/json','Content-Type':'application/json', ...(options.headers || {})}, ...options});
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message || 'Error de comunicacion.');
        return body;
    }

    function toggleTercero() {
        const no = $('sa-titular-no').checked;
        const definido = $('sa-titular-no').checked || $('sa-titular-si').checked;
        $('sa-tercero').classList.toggle('d-none', !no);
        if (!definido) $('sa-tercero').classList.add('d-none');
    }

    function resumenMotoFactura(moto) {
        if (!moto) {
            return '<div class="small text-muted mt-3"><i class="fa-solid fa-motorcycle me-1"></i>Sin factura de motocicleta registrada para este crédito.</div>';
        }
        const nombreMoto = [moto.marca, moto.modelo, moto.anio].filter(Boolean).map(esc).join(' ');
        return `<div class="border-top mt-3 pt-3">
            <div class="small fw-bold text-primary"><i class="fa-solid fa-motorcycle me-1"></i>Motocicleta facturada</div>
            <div class="small mt-1">${nombreMoto || 'Datos de unidad sin marca o modelo'}</div>
            <div class="small text-muted">NIV/VIN: <strong class="text-body">${esc(moto.numero_serie || 'No registrado')}</strong>${moto.numero_motor ? ` &middot; Motor: ${esc(moto.numero_motor)}` : ''}</div>
            <div class="small text-success mt-1"><i class="fa-solid fa-link me-1"></i>Coincidencia exacta por crédito</div>
        </div>`;
    }

    function renderCredito(credito, status) {
        creditoActual = credito;
        idempotencyKey = window.crypto && crypto.randomUUID
            ? crypto.randomUUID()
            : `atc-${Date.now()}-${credito.id_credito}`;
        $('sa-credito').innerHTML = `
            <div class="d-flex justify-content-between gap-2"><strong>Credito #${esc(credito.id_credito)}</strong><span class="badge bg-label-primary">${esc(status || 'Sin estatus')}</span></div>
            <div class="fw-semibold mt-2">${esc(credito.nombre_cliente)}</div>
            <div class="small text-muted mt-1">${esc(credito.telefono)} · ${esc(credito.sucursal)}</div>
            <div class="small text-muted">${esc(credito.direccion)}</div>
            ${resumenMotoFactura(credito.moto_factura)}`;
        $('sa-credito').classList.remove('d-none');
        $('sa-captura').classList.remove('d-none');
    }

    $('sa-buscar').addEventListener('click', async () => {
        const id = Number($('sa-id-credito').value || 0);
        if (!id) return notify('warning', 'Captura un ID de credito.');
        $('sa-buscar').disabled = true;
        try {
            const r = await json('/SolicitudAdjudicacion/buscarCredito', {method:'POST', body:JSON.stringify({id_credito:id})});
            if (!r.success || !r.credito) throw new Error(r.message || 'Credito no encontrado.');
            renderCredito(r.credito, r.status_credito);
        } catch (e) {
            creditoActual = null;
            $('sa-credito').classList.add('d-none');
            $('sa-captura').classList.add('d-none');
            notify('error', 'No se pudo consultar', e.message);
        } finally { $('sa-buscar').disabled = false; }
    });

    document.querySelectorAll('[name="entregara_titular"]').forEach((el) => el.addEventListener('change', toggleTercero));

    $('sa-id-credito').addEventListener('input', () => {
        if (!creditoActual || Number($('sa-id-credito').value) === Number(creditoActual.id_credito)) return;
        creditoActual = null;
        idempotencyKey = null;
        $('sa-credito').classList.add('d-none');
        $('sa-captura').classList.add('d-none');
    });

    $('sa-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!tablasDisponibles || !creditoActual) return;
        const titular = document.querySelector('[name="entregara_titular"]:checked');
        const payload = {
            id_credito: Number(creditoActual.id_credito),
            nombre_cliente: creditoActual.nombre_cliente || '',
            entregara_titular: titular ? titular.value : null,
            nombre_entregante: $('sa-entregante').value,
            kilometraje: $('sa-km').value,
            telefono_actual: $('sa-telefono').value,
            direccion_resguardo: $('sa-direccion').value,
            motivo: $('sa-motivo').value,
            datos_credito: creditoActual,
            idempotency_key: idempotencyKey
        };
        $('sa-guardar').disabled = true;
        $('sa-errors').classList.add('d-none');
        try {
            const r = await json('/SolicitudAdjudicacion/crear', {method:'POST', body:JSON.stringify(payload)});
            if (!r.success) {
                const errors = r.errors ? Object.values(r.errors) : [r.message || 'No se pudo registrar.'];
                $('sa-errors').innerHTML = errors.map((x) => `<div>${esc(x)}</div>`).join('');
                $('sa-errors').classList.remove('d-none');
                return;
            }
            notify('success', 'Solicitud registrada', r.solicitud?.folio || 'La solicitud quedo en estatus Recibida.');
            verificarRepuveSolicitudAutomatica(r.solicitud?.id);
            event.target.reset(); creditoActual = null; idempotencyKey = null; toggleTercero();
            $('sa-credito').classList.add('d-none'); $('sa-captura').classList.add('d-none');
            await cargarLista();
        } catch (e) { notify('error', 'No se pudo registrar', e.message); }
        finally { $('sa-guardar').disabled = !tablasDisponibles; }
    });

    async function verificarRepuveSolicitudAutomatica(idSolicitud, intento = 0) {
        if (!idSolicitud) return;
        try {
            const r = await json('/SolicitudAdjudicacion/verificarRepuveSolicitud', {
                method: 'POST',
                body: JSON.stringify({id_solicitud: Number(idSolicitud)})
            });
            if (r.blacklist) {
                notify('error', 'Adjudicacion bloqueada', r.message || 'No se puede proceder con la adjudicacion. Cualquier duda contacta a tu lider.');
                await cargarLista();
            } else if (r.requiere_validacion_manual) {
                notify('warning', 'Validacion manual REPUVE requerida', r.message || 'Debe realizarse la validacion manual de REPUVE posteriormente.');
            } else if (r.estado === 'SIN_REPORTE_ROBO') {
                notify('success', 'Sin Reporte de Robo', r.message || 'Puede continuar con la siguiente validacion.');
            } else if (r.estado === 'PENDIENTE_REPUVE') {
                if (intento === 0) {
                    notify('info', 'Validacion REPUVE en proceso', r.message || 'La adjudicacion no debe continuar hasta contar con el resultado.');
                }
                if (intento < 5) {
                    window.setTimeout(() => verificarRepuveSolicitudAutomatica(idSolicitud, intento + 1), 10000);
                }
            }
        } catch (e) {
            console.warn('No se pudo completar la verificación automática REPUVE.', e);
        }
    }

    async function cargarLista() {
        if (!tablasDisponibles) {
            $('sa-lista').innerHTML = '<tr><td colspan="6" class="sa-empty">Aplica la migracion para habilitar el historial.</td></tr>';
            return;
        }
        const q = encodeURIComponent($('sa-filtro').value.trim());
        try {
            const r = await json('/SolicitudAdjudicacion/listar?q=' + q);
            const rows = Array.isArray(r.rows) ? r.rows : [];
            $('sa-lista').innerHTML = rows.length ? rows.map((row) => `<tr>
                <td><strong>${esc(row.folio)}</strong></td>
                <td><strong>#${esc(row.id_credito)}</strong><br><small class="text-muted">${esc(row.nombre_cliente || 'Sin nombre')}</small></td>
                <td>${Number(row.entregara_titular) === 1 ? 'Titular' : esc(row.nombre_entregante || 'Tercero')}</td>
                <td><span class="sa-status">${esc(row.estatus)}</span></td>
                <td>${esc(row.fecha_alta_fmt)}</td>
                <td><button class="btn btn-sm btn-outline-primary sa-ver" data-id="${Number(row.id)}"><i class="fa-regular fa-eye"></i></button></td>
            </tr>`).join('') : '<tr><td colspan="6" class="sa-empty">Aun no tienes solicitudes registradas.</td></tr>';
        } catch (e) { $('sa-lista').innerHTML = `<tr><td colspan="6" class="sa-empty text-danger">${esc(e.message)}</td></tr>`; }
    }

    $('sa-lista').addEventListener('click', async (event) => {
        const button = event.target.closest('.sa-ver'); if (!button) return;
        try {
            const r = await json('/SolicitudAdjudicacion/detalle/' + Number(button.dataset.id));
            if (!r.success) throw new Error(r.message || 'No encontrada.');
            const s = r.solicitud, hist = Array.isArray(s.historial) ? s.historial : [];
            $('sa-detalle').innerHTML = `
                <div class="row g-3 mb-4"><div class="col-md-4"><small class="text-muted">Folio</small><div class="fw-bold">${esc(s.folio)}</div></div><div class="col-md-4"><small class="text-muted">Credito</small><div class="fw-bold">#${esc(s.id_credito)}</div></div><div class="col-md-4"><small class="text-muted">Estatus</small><div><span class="sa-status">${esc(s.estatus)}</span></div></div></div>
                ${Number(s.entregara_titular) === 1 ? '<p>Entregara el titular.</p>' : `<div class="row g-3 mb-4"><div class="col-md-6"><small class="text-muted">Entregante</small><div>${esc(s.nombre_entregante)}</div></div><div class="col-md-3"><small class="text-muted">Kilometraje</small><div>${esc(s.kilometraje)}</div></div><div class="col-md-3"><small class="text-muted">Telefono</small><div>${esc(s.telefono_actual)}</div></div><div class="col-12"><small class="text-muted">Resguardo</small><div>${esc(s.direccion_resguardo)}</div></div><div class="col-12"><small class="text-muted">Motivo</small><div>${esc(s.motivo)}</div></div></div>`}
                <h6>Historial</h6>${hist.map((h) => `<div class="border-start border-primary ps-3 py-2"><strong>${esc(h.estatus_nuevo)}</strong><div class="small">${esc(h.comentario)}</div><small class="text-muted">${esc(h.fecha_fmt)} · ${esc(h.actor_nombre)}</small></div>`).join('')}`;
            $('sa-detalle-card').classList.remove('d-none');
        } catch (e) { notify('error', 'No se pudo cargar el detalle', e.message); }
    });

    $('sa-cerrar-detalle').addEventListener('click', () => $('sa-detalle-card').classList.add('d-none'));
    $('sa-refrescar').addEventListener('click', cargarLista);
    $('sa-filtro').addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); cargarLista(); } });
    const creditoInicial = Number(new URLSearchParams(window.location.search).get('id_credito') || 0);
    if (creditoInicial > 0) {
        $('sa-id-credito').value = creditoInicial;
        $('sa-buscar').click();
    }
    cargarLista();
})();
</script>
