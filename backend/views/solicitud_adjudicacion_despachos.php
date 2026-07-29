<?php $tablasDisponibles = !empty($solicitudes_tablas_disponibles); ?>
<style>
.sad { --sad-blue:#155eef; --sad-ink:#172033; --sad-muted:#667085; }
.sad .sad-hero { background:linear-gradient(135deg,#102a56,#155eef 62%,#53b1fd); color:#fff; border:0; }
.sad .sad-panel { border:1px solid #e5e7eb; box-shadow:0 12px 30px rgba(15,23,42,.06); }
.sad .sad-credit { border-left:4px solid var(--sad-blue); background:#f5f8ff; }
.sad .sad-status { border-radius:999px; padding:.25rem .6rem; background:#e8efff; color:#2446a8; font-size:.75rem; font-weight:800; text-transform:uppercase; }
.sad .sad-required::after { content:' *'; color:#dc2626; }
.sad .sad-empty { padding:2rem; text-align:center; color:var(--sad-muted); }
</style>

<div class="sad container-fluid py-3">
    <div class="card sad-hero mb-4">
        <div class="card-body p-4">
            <small class="fw-bold text-uppercase opacity-75">Despachos</small>
            <h3 class="text-white mt-1 mb-2">Solicitud de Adjudicación</h3>
            <p class="mb-0 opacity-75">Busca el crédito, registra los datos de entrega y define quién atenderá la recuperación.</p>
        </div>
    </div>

    <?php if (!$tablasDisponibles): ?>
    <div class="alert alert-warning">Falta aplicar la migración de solicitudes de adjudicación.</div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card sad-panel">
                <div class="card-header"><h5 class="mb-0">Nueva solicitud</h5></div>
                <div class="card-body">
                    <form id="sad-form" novalidate>
                        <label class="form-label sad-required" for="sad-id-credito">ID del crédito</label>
                        <div class="input-group mb-3">
                            <input type="number" min="1" id="sad-id-credito" class="form-control" placeholder="ID crédito">
                            <button type="button" id="sad-buscar" class="btn btn-primary"><i class="fa-solid fa-search me-1"></i>Buscar</button>
                        </div>
                        <div id="sad-credito" class="sad-credit rounded p-3 mb-3 d-none"></div>

                        <div id="sad-captura" class="d-none">
                            <div class="mb-3">
                                <label class="form-label sad-required">¿Entregará el titular?</label>
                                <div class="d-flex gap-4">
                                    <label class="form-check"><input class="form-check-input" type="radio" name="sad_titular" value="1"> Sí</label>
                                    <label class="form-check"><input class="form-check-input" type="radio" name="sad_titular" value="0"> No</label>
                                </div>
                            </div>
                            <div id="sad-tercero" class="d-none border rounded p-3 mb-3">
                                <div class="mb-3"><label class="form-label sad-required" for="sad-nombre">Nombre de quien entrega</label><input id="sad-nombre" class="form-control" maxlength="180"></div>
                                <div class="mb-3"><label class="form-label sad-required" for="sad-telefono">Teléfono</label><input id="sad-telefono" type="tel" class="form-control" maxlength="20"></div>
                                <div><label class="form-label sad-required" for="sad-motivo">Motivo</label><textarea id="sad-motivo" class="form-control" rows="2" maxlength="1000"></textarea></div>
                            </div>

                            <div class="mb-3"><label class="form-label sad-required" for="sad-vin">VIN de la motocicleta</label><input id="sad-vin" class="form-control text-uppercase" minlength="17" maxlength="17" placeholder="17 caracteres"></div>
                            <div class="mb-3">
                                <label class="form-label sad-required">Asignar solicitud a</label>
                                <div class="d-flex flex-wrap gap-4">
                                    <label class="form-check"><input class="form-check-input" type="radio" name="sad_asignacion" value="DESPACHO"> Nombre de Despacho</label>
                                    <label class="form-check"><input class="form-check-input" type="radio" name="sad_asignacion" value="EQUIPO_MAXIKASH"> Equipo Maxikash</label>
                                </div>
                                <small class="text-muted">Equipo Maxikash quedará pendiente de asignación por Motos Adjudicadas.</small>
                            </div>
                            <div id="sad-gestor-wrap" class="mb-3 d-none">
                                <label class="form-label sad-required" for="sad-gestor">Gestor del despacho</label>
                                <select id="sad-gestor" class="form-select"><option value="">Cargando gestores...</option></select>
                            </div>
                            <div id="sad-errors" class="alert alert-danger d-none"></div>
                            <button type="submit" id="sad-guardar" class="btn btn-primary w-100" <?= $tablasDisponibles ? '' : 'disabled'; ?>><i class="fa-solid fa-paper-plane me-1"></i>Registrar solicitud</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card sad-panel">
                <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div><h5 class="mb-1">Mis solicitudes de Despachos</h5><small class="text-muted">Seguimiento e historial del canal.</small></div>
                    <div class="input-group" style="max-width:280px"><input id="sad-filtro" class="form-control" placeholder="Folio, crédito o cliente"><button id="sad-refrescar" type="button" class="btn btn-outline-primary"><i class="fa-solid fa-rotate"></i></button></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Folio</th><th>Crédito</th><th>VIN</th><th>Asignación</th><th>Estatus</th><th></th></tr></thead>
                        <tbody id="sad-lista"><tr><td colspan="6" class="sad-empty">Cargando...</td></tr></tbody>
                    </table>
                </div>
            </div>
            <div id="sad-detalle-card" class="card sad-panel mt-4 d-none">
                <div class="card-header d-flex justify-content-between"><h5 class="mb-0">Detalle e historial</h5><button id="sad-cerrar" type="button" class="btn-close"></button></div>
                <div id="sad-detalle" class="card-body"></div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';
    const tablas = <?= $tablasDisponibles ? 'true' : 'false'; ?>;
    const $ = id => document.getElementById(id);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    let credito = null;
    let key = null;

    async function api(url, options = {}) {
        const response = await fetch(url, {headers:{Accept:'application/json','Content-Type':'application/json', ...(options.headers || {})}, ...options});
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message || 'Error de comunicación.');
        return body;
    }
    const avisar = (icon, title, text = '') => window.Swal ? Swal.fire({icon,title,text}) : alert(title + '\n' + text);

    function mostrarTercero() {
        const selected = document.querySelector('[name="sad_titular"]:checked');
        $('sad-tercero').classList.toggle('d-none', !selected || selected.value !== '0');
    }
    function mostrarGestor() {
        const selected = document.querySelector('[name="sad_asignacion"]:checked');
        $('sad-gestor-wrap').classList.toggle('d-none', !selected || selected.value !== 'DESPACHO');
    }
    document.querySelectorAll('[name="sad_titular"]').forEach(el => el.addEventListener('change', mostrarTercero));
    document.querySelectorAll('[name="sad_asignacion"]').forEach(el => el.addEventListener('change', mostrarGestor));

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

    async function cargarGestores() {
        try {
            const r = await api('/despachos/obtenerListaDespachos?id_celula=1');
            const rows = Array.isArray(r.despachos) ? r.despachos.filter(x => String(x.estatus).toLowerCase() === 'activo') : [];
            $('sad-gestor').innerHTML = '<option value="">Seleccione gestor</option>' + rows.map(x =>
                `<option value="${Number(x.id_persona)}" data-nombre="${esc(x.nombre_completo)}">${esc(x.nombre_completo)}${x.nombre_puesto ? ' · ' + esc(x.nombre_puesto) : ''}</option>`
            ).join('');
        } catch (e) {
            $('sad-gestor').innerHTML = '<option value="">No fue posible cargar gestores</option>';
        }
    }

    async function buscar() {
        const id = Number($('sad-id-credito').value || 0);
        if (!id) return avisar('warning', 'Captura un ID de crédito.');
        $('sad-buscar').disabled = true;
        try {
            const r = await api('/SolicitudAdjudicacion/buscarCreditoDespachos', {method:'POST', body:JSON.stringify({id_credito:id})});
            if (!r.success || !r.credito) throw new Error(r.message || 'Crédito no encontrado.');
            credito = r.credito;
            key = crypto.randomUUID ? crypto.randomUUID() : `despachos-${Date.now()}-${id}`;
            $('sad-credito').innerHTML = `<strong>Crédito #${esc(credito.id_credito)}</strong><div class="fw-semibold mt-2">${esc(credito.nombre_cliente)}</div><small class="text-muted">${esc(credito.telefono)} · ${esc(credito.direccion)}</small>`;
            $('sad-credito').insertAdjacentHTML('beforeend', resumenMotoFactura(credito.moto_factura));
            const vinFactura = String(credito.moto_factura?.numero_serie || '').toUpperCase();
            if (/^[A-HJ-NPR-Z0-9]{17}$/.test(vinFactura) && !$('sad-vin').value.trim()) {
                $('sad-vin').value = vinFactura;
            }
            $('sad-credito').classList.remove('d-none');
            $('sad-captura').classList.remove('d-none');
        } catch (e) {
            credito = null;
            $('sad-credito').classList.add('d-none');
            $('sad-captura').classList.add('d-none');
            avisar('error', 'No se pudo consultar', e.message);
        } finally { $('sad-buscar').disabled = false; }
    }
    $('sad-buscar').addEventListener('click', buscar);
    $('sad-id-credito').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscar(); } });

    $('sad-form').addEventListener('submit', async e => {
        e.preventDefault();
        if (!tablas || !credito) return;
        const titular = document.querySelector('[name="sad_titular"]:checked');
        const asignacion = document.querySelector('[name="sad_asignacion"]:checked');
        const gestor = $('sad-gestor').selectedOptions[0];
        const payload = {
            id_credito:Number(credito.id_credito), nombre_cliente:credito.nombre_cliente || '',
            entregara_titular:titular ? titular.value : null,
            nombre_entregante:$('sad-nombre').value, telefono_actual:$('sad-telefono').value,
            motivo:$('sad-motivo').value, vin:$('sad-vin').value,
            tipo_asignacion:asignacion ? asignacion.value : '',
            id_persona_gestor:gestor ? Number(gestor.value || 0) : 0,
            nombre_gestor:gestor ? gestor.dataset.nombre || '' : '',
            datos_credito:credito, idempotency_key:key
        };
        $('sad-guardar').disabled = true;
        $('sad-errors').classList.add('d-none');
        try {
            const r = await api('/SolicitudAdjudicacion/crearDespachos', {method:'POST', body:JSON.stringify(payload)});
            if (!r.success) {
                $('sad-errors').innerHTML = Object.values(r.errors || {general:r.message || 'No se pudo registrar.'}).map(x => `<div>${esc(x)}</div>`).join('');
                $('sad-errors').classList.remove('d-none');
                return;
            }
            avisar('success', 'Solicitud registrada', r.solicitud?.folio || '');
            verificarRepuveSolicitudAutomatica(r.solicitud?.id);
            e.target.reset(); credito = null; key = null; mostrarTercero(); mostrarGestor();
            $('sad-credito').classList.add('d-none'); $('sad-captura').classList.add('d-none');
            cargarLista();
        } catch (error) { avisar('error', 'No se pudo registrar', error.message); }
        finally { $('sad-guardar').disabled = !tablas; }
    });

    async function verificarRepuveSolicitudAutomatica(idSolicitud, intento = 0) {
        if (!idSolicitud) return;
        try {
            const r = await api('/SolicitudAdjudicacion/verificarRepuveSolicitud', {
                method: 'POST',
                body: JSON.stringify({id_solicitud: Number(idSolicitud)})
            });
            if (r.blacklist) {
                avisar('error', 'Adjudicacion bloqueada', r.message || 'No se puede proceder con la adjudicacion. Cualquier duda contacta a tu lider.');
                await cargarLista();
            } else if (r.requiere_validacion_manual) {
                avisar('warning', 'Validacion manual REPUVE requerida', r.message || 'Debe realizarse la validacion manual de REPUVE posteriormente.');
            } else if (r.estado === 'SIN_REPORTE_ROBO') {
                avisar('success', 'Sin Reporte de Robo', r.message || 'Puede continuar con la siguiente validacion.');
            } else if (r.estado === 'PENDIENTE_REPUVE') {
                if (intento === 0) {
                    avisar('info', 'Validacion REPUVE en proceso', r.message || 'La adjudicacion no debe continuar hasta contar con el resultado.');
                }
                if (intento < 5) {
                    window.setTimeout(() => verificarRepuveSolicitudAutomatica(idSolicitud, intento + 1), 10000);
                }
            }
        } catch (error) {
            console.warn('No se pudo completar la verificación automática REPUVE.', error);
        }
    }

    async function cargarLista() {
        if (!tablas) return;
        try {
            const r = await api('/SolicitudAdjudicacion/listarDespachos?q=' + encodeURIComponent($('sad-filtro').value.trim()));
            const rows = Array.isArray(r.rows) ? r.rows : [];
            $('sad-lista').innerHTML = rows.length ? rows.map(x => `<tr>
                <td><strong>${esc(x.folio)}</strong><br><small>${esc(x.fecha_alta_fmt)}</small></td>
                <td><strong>#${esc(x.id_credito)}</strong><br><small>${esc(x.nombre_cliente || '')}</small></td>
                <td>${esc(x.vin || '')}</td>
                <td>${x.tipo_asignacion === 'DESPACHO' ? esc(x.nombre_gestor || 'Despacho') : 'Equipo Maxikash'}</td>
                <td><span class="sad-status">${esc(x.estatus)}</span></td>
                <td><button type="button" class="btn btn-sm btn-outline-primary sad-ver" data-id="${Number(x.id)}"><i class="fa-regular fa-eye"></i></button></td>
            </tr>`).join('') : '<tr><td colspan="6" class="sad-empty">Aún no tienes solicitudes registradas.</td></tr>';
        } catch (e) { $('sad-lista').innerHTML = `<tr><td colspan="6" class="sad-empty text-danger">${esc(e.message)}</td></tr>`; }
    }
    $('sad-lista').addEventListener('click', async e => {
        const btn = e.target.closest('.sad-ver'); if (!btn) return;
        try {
            const r = await api('/SolicitudAdjudicacion/detalleDespachos/' + Number(btn.dataset.id));
            if (!r.success) throw new Error(r.message);
            const s = r.solicitud, h = Array.isArray(s.historial) ? s.historial : [];
            $('sad-detalle').innerHTML = `<div class="row g-3 mb-4">
                <div class="col-md-4"><small class="text-muted">Folio</small><div class="fw-bold">${esc(s.folio)}</div></div>
                <div class="col-md-4"><small class="text-muted">Crédito / VIN</small><div class="fw-bold">#${esc(s.id_credito)} · ${esc(s.vin)}</div></div>
                <div class="col-md-4"><small class="text-muted">Asignación</small><div>${s.tipo_asignacion === 'DESPACHO' ? esc(s.nombre_gestor) : 'Equipo Maxikash'}</div></div>
                <div class="col-12"><small class="text-muted">Entrega</small><div>${Number(s.entregara_titular) === 1 ? 'Titular' : esc(s.nombre_entregante) + ' · ' + esc(s.telefono_actual) + ' · ' + esc(s.motivo)}</div></div>
            </div><h6>Historial</h6>${h.map(x => `<div class="border-start border-primary ps-3 py-2"><strong>${esc(x.estatus_nuevo)}</strong><div>${esc(x.comentario)}</div><small class="text-muted">${esc(x.fecha_fmt)} · ${esc(x.actor_nombre)}</small></div>`).join('')}`;
            $('sad-detalle-card').classList.remove('d-none');
        } catch (error) { avisar('error', 'No se pudo cargar el detalle', error.message); }
    });
    $('sad-cerrar').addEventListener('click', () => $('sad-detalle-card').classList.add('d-none'));
    $('sad-refrescar').addEventListener('click', cargarLista);
    $('sad-filtro').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); cargarLista(); } });

    const inicial = Number(new URLSearchParams(location.search).get('id_credito') || 0);
    cargarGestores();
    cargarLista();
    if (inicial) { $('sad-id-credito').value = inicial; buscar(); }
})();
</script>
