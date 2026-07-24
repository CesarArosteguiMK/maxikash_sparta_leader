<?php
$tablasDisponibles = !empty($solicitudes_tablas_disponibles);
?>
<style>
.sa-inbox {
    --inbox-navy: #172554;
    --inbox-blue: #2457d6;
    --inbox-sky: #38bdf8;
    --inbox-ink: #172033;
    --inbox-muted: #667085;
    --inbox-border: #e4e7ec;
    --inbox-surface: #f8faff;
}
.sa-inbox .inbox-hero {
    position: relative;
    overflow: hidden;
    border: 0;
    color: #fff;
    background: linear-gradient(135deg, var(--inbox-navy) 0%, var(--inbox-blue) 62%, var(--inbox-sky) 100%) !important;
}
.sa-inbox .inbox-hero::after {
    position: absolute;
    right: -70px;
    bottom: -105px;
    width: 280px;
    height: 280px;
    content: "";
    border: 42px solid rgba(255, 255, 255, .10);
    border-radius: 50%;
}
.sa-inbox .inbox-kicker {
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .8;
}
.sa-inbox .inbox-hero .inbox-kicker,
.sa-inbox .inbox-hero p {
    color: rgba(255, 255, 255, .82) !important;
}
.sa-inbox .inbox-panel,
.sa-inbox .inbox-stat {
    border: 1px solid var(--inbox-border);
    box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
}
.sa-inbox .inbox-stat {
    height: 100%;
    background: #fff;
}
.sa-inbox .inbox-stat-icon {
    width: 42px;
    height: 42px;
    display: inline-grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 12px;
    color: var(--inbox-blue);
    background: #eaf0ff;
}
.sa-inbox .inbox-stat-value {
    color: var(--inbox-ink);
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1;
}
.sa-inbox .inbox-muted {
    color: var(--inbox-muted);
}
.sa-inbox .inbox-channel,
.sa-inbox .inbox-status {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .28rem .62rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 800;
    white-space: nowrap;
    text-transform: uppercase;
}
.sa-inbox .channel-atc { color: #1d4ed8; background: #dbeafe; }
.sa-inbox .channel-callcenter { color: #7e22ce; background: #f3e8ff; }
.sa-inbox .channel-despachos { color: #b45309; background: #fef3c7; }
.sa-inbox .channel-campo { color: #047857; background: #d1fae5; }
.sa-inbox .status-recibida { color: #b45309; background: #fff3cd; }
.sa-inbox .status-asignada { color: #047857; background: #d1fae5; }
.sa-inbox .status-default { color: #475467; background: #f2f4f7; }
.sa-inbox .inbox-table > :not(caption) > * > * {
    padding: .85rem .9rem;
    vertical-align: middle;
}
.sa-inbox .inbox-table tbody tr {
    cursor: pointer;
}
.sa-inbox .inbox-table tbody tr:hover {
    background: var(--inbox-surface);
}
.sa-inbox .inbox-empty {
    padding: 3rem 1rem !important;
    color: var(--inbox-muted);
    text-align: center;
}
.sa-inbox .inbox-detail {
    position: sticky;
    top: 1rem;
}
.sa-inbox .inbox-detail-block {
    padding: 1rem;
    border: 1px solid var(--inbox-border);
    border-radius: 12px;
    background: #fff;
}
.sa-inbox .inbox-label {
    display: block;
    margin-bottom: .2rem;
    color: var(--inbox-muted);
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.sa-inbox .inbox-value {
    color: var(--inbox-ink);
    font-weight: 600;
    overflow-wrap: anywhere;
}
.sa-inbox .inbox-timeline {
    position: relative;
    padding: .15rem 0 .85rem 1.25rem;
    border-left: 2px solid #dbe5ff;
}
.sa-inbox .inbox-timeline::before {
    position: absolute;
    top: .35rem;
    left: -6px;
    width: 10px;
    height: 10px;
    content: "";
    border: 2px solid #fff;
    border-radius: 50%;
    background: var(--inbox-blue);
    box-shadow: 0 0 0 2px #b9caff;
}
.sa-inbox .inbox-assignment {
    border: 1px solid #bfd0ff;
    background: #f5f8ff;
}
.sa-inbox .inbox-dot {
    width: 7px;
    height: 7px;
    display: inline-block;
    border-radius: 50%;
    background: currentColor;
}
.sa-inbox .form-label {
    font-weight: 600;
}
@media (max-width: 1199.98px) {
    .sa-inbox .inbox-detail {
        position: static;
    }
}
</style>

<div class="sa-inbox container-fluid py-3">
    <div class="card inbox-hero mb-4">
        <div class="card-body position-relative p-4 p-lg-5" style="z-index:1">
            <div class="inbox-kicker mb-2">Motos Adjudicadas · Etapa 2</div>
            <h3 class="text-white mb-2">Bandeja de solicitudes</h3>
            <p class="mb-0 opacity-75">Recibe en un solo lugar las solicitudes generadas por ATC, Call Center, Despachos y Campo para revisarlas y asignarlas al flujo operativo.</p>
        </div>
    </div>

    <?php if (!$tablasDisponibles): ?>
    <div class="alert alert-warning d-flex gap-3 align-items-start" role="alert">
        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
        <div>
            <strong>Bandeja preparada, migración pendiente.</strong><br>
            Debe aplicarse <code>scripts/migration_solicitudes_adjudicacion_etapa1.sql</code> para consultar y asignar solicitudes.
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card inbox-stat">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="inbox-stat-icon"><i class="fa-solid fa-inbox"></i></span>
                    <div><div class="inbox-stat-value" id="inbox-stat-recibidas">0</div><small class="inbox-muted">Por asignar</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card inbox-stat">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="inbox-stat-icon"><i class="fa-solid fa-user-check"></i></span>
                    <div><div class="inbox-stat-value" id="inbox-stat-asignadas">0</div><small class="inbox-muted">Asignadas</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card inbox-stat">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="inbox-stat-icon"><i class="fa-solid fa-layer-group"></i></span>
                    <div><div class="inbox-stat-value" id="inbox-stat-total">0</div><small class="inbox-muted">Solicitudes totales</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card inbox-stat">
                <div class="card-body">
                    <small class="inbox-muted d-block mb-2">Entradas por canal</small>
                    <div class="d-flex flex-wrap gap-2 small fw-semibold" id="inbox-stat-canales">
                        <span>ATC 0</span><span>·</span><span>Call Center 0</span><span>·</span><span>Despachos 0</span><span>·</span><span>Campo 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card inbox-panel mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label class="form-label" for="inbox-q">Buscar solicitud</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input class="form-control" id="inbox-q" placeholder="Folio, crédito, cliente, entregante o responsable">
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="inbox-canal">Origen</label>
                    <select class="form-select" id="inbox-canal">
                        <option value="">Todos</option>
                        <option value="ATC">ATC</option>
                        <option value="CALLCENTER">Call Center</option>
                        <option value="DESPACHOS">Despachos</option>
                        <option value="CAMPO">Campo</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="inbox-estatus">Estatus</label>
                    <select class="form-select" id="inbox-estatus">
                        <option value="">Todos</option>
                        <option value="recibida">Por asignar</option>
                        <option value="asignada">Asignada</option>
                    </select>
                </div>
                <div class="col-12 col-lg-3 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" type="button" id="inbox-filtrar"><i class="fa-solid fa-filter me-2"></i>Aplicar</button>
                    <button class="btn btn-outline-primary" type="button" id="inbox-refrescar" aria-label="Actualizar"><i class="fa-solid fa-rotate"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12" id="inbox-list-column">
            <div class="card inbox-panel">
                <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">Solicitudes recibidas</h5>
                        <small class="inbox-muted">Las pendientes se muestran primero, de la más antigua a la más reciente.</small>
                    </div>
                    <span class="badge bg-label-primary" id="inbox-result-count">0 resultados</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover inbox-table mb-0">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Crédito / cliente</th>
                                <th>Entrega</th>
                                <th>Solicitó</th>
                                <th>Asignación</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="inbox-rows">
                            <tr><td colspan="6" class="inbox-empty">Cargando bandeja...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5 d-none" id="inbox-detail-column">
            <div class="card inbox-panel inbox-detail">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <small class="inbox-muted">Revisión y asignación</small>
                        <h5 class="mb-0" id="inbox-detail-title">Detalle</h5>
                    </div>
                    <button class="btn-close" type="button" id="inbox-close-detail" aria-label="Cerrar detalle"></button>
                </div>
                <div class="card-body" id="inbox-detail-body">
                    <div class="inbox-empty">Selecciona una solicitud.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';

    const tablasDisponibles = <?= $tablasDisponibles ? 'true' : 'false'; ?>;
    const $ = (id) => document.getElementById(id);
    const state = { solicitud: null, responsables: [], cargandoResponsables: false };
    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));
    const texto = (value, fallback = 'No informado') => {
        const clean = String(value ?? '').trim();
        return clean === '' ? fallback : clean;
    };
    const canalLabel = (canal) => ({
        ATC: 'ATC',
        CALLCENTER: 'Call Center',
        DESPACHOS: 'Despachos',
        CAMPO: 'Campo'
    }[String(canal || '').toUpperCase()] || texto(canal));
    const channelClass = (canal) => {
        const value = String(canal || '').toLowerCase();
        return ['atc', 'callcenter', 'despachos', 'campo'].includes(value) ? `channel-${value}` : 'status-default';
    };
    const statusClass = (estatus) => {
        const value = String(estatus || '').toLowerCase();
        return ['recibida', 'asignada'].includes(value) ? `status-${value}` : 'status-default';
    };
    const statusLabel = (estatus) => ({
        recibida: 'Por asignar',
        asignada: 'Asignada',
        completada: 'Completada',
        rechazada: 'Rechazada',
        cancelada: 'Cancelada'
    }[String(estatus || '').toLowerCase()] || texto(estatus));
    const badgeCanal = (canal) => `<span class="inbox-channel ${channelClass(canal)}"><span class="inbox-dot"></span>${esc(canalLabel(canal))}</span>`;
    const badgeEstatus = (estatus) => `<span class="inbox-status ${statusClass(estatus)}">${esc(statusLabel(estatus))}</span>`;
    const notify = (icon, title, text = '') => window.Swal
        ? Swal.fire({ icon, title, text, confirmButtonText: 'Aceptar' })
        : alert(title + (text ? '\n' + text : ''));

    async function json(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                ...(options.headers || {})
            }
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok || body.success === false) {
            throw new Error(body.message || 'No se pudo completar la operación.');
        }
        return body;
    }

    function actualizarStats(stats = {}) {
        $('inbox-stat-recibidas').textContent = Number(stats.recibidas || 0);
        $('inbox-stat-asignadas').textContent = Number(stats.asignadas || 0);
        $('inbox-stat-total').textContent = Number(stats.total || 0);
        $('inbox-stat-canales').innerHTML = `
            <span>ATC ${Number(stats.atc || 0)}</span><span>·</span>
            <span>Call Center ${Number(stats.callcenter || 0)}</span><span>·</span>
            <span>Despachos ${Number(stats.despachos || 0)}</span><span>·</span>
            <span>Campo ${Number(stats.campo || 0)}</span>`;
    }

    function renderRows(rows) {
        $('inbox-result-count').textContent = `${rows.length} ${rows.length === 1 ? 'resultado' : 'resultados'}`;
        if (!rows.length) {
            $('inbox-rows').innerHTML = '<tr><td colspan="6" class="inbox-empty"><i class="fa-regular fa-folder-open fa-2x mb-3 d-block"></i>No hay solicitudes con los filtros seleccionados.</td></tr>';
            return;
        }
        $('inbox-rows').innerHTML = rows.map((row) => {
            const entrega = Number(row.entregara_titular) === 1
                ? '<strong>Titular</strong><br><small class="inbox-muted">Entrega directa</small>'
                : `<strong>${esc(texto(row.nombre_entregante, 'Tercero'))}</strong><br><small class="inbox-muted">Entrega por tercero</small>`;
            const asignacion = String(row.estatus).toLowerCase() === 'asignada'
                ? `<strong>${esc(texto(row.nombre_persona_asignada))}</strong><br><small class="inbox-muted">${esc(texto(row.fecha_asignacion_fmt, 'Sin fecha'))}</small>`
                : `${badgeEstatus(row.estatus)}<br><small class="inbox-muted mt-1 d-inline-block">Pendiente de responsable</small>`;
            return `<tr class="inbox-row" data-id="${Number(row.id)}">
                <td>${badgeCanal(row.canal)}<div class="fw-bold mt-2">${esc(row.folio)}</div><small class="inbox-muted">${esc(row.fecha_alta_fmt)}</small></td>
                <td><strong>#${esc(row.id_credito)}</strong><br><span>${esc(texto(row.nombre_cliente, 'Cliente sin nombre'))}</span></td>
                <td>${entrega}</td>
                <td><strong>${esc(texto(row.nombre_usuario_solicitante))}</strong><br><small class="inbox-muted">${esc(canalLabel(row.canal))}</small></td>
                <td>${asignacion}</td>
                <td><button class="btn btn-sm btn-outline-primary inbox-open" type="button" data-id="${Number(row.id)}">Revisar <i class="fa-solid fa-chevron-right ms-1"></i></button></td>
            </tr>`;
        }).join('');
    }

    async function cargarBandeja() {
        if (!tablasDisponibles) {
            $('inbox-rows').innerHTML = '<tr><td colspan="6" class="inbox-empty">Aplica la migración para habilitar la bandeja.</td></tr>';
            return;
        }
        $('inbox-refrescar').disabled = true;
        const params = new URLSearchParams({
            q: $('inbox-q').value.trim(),
            canal: $('inbox-canal').value,
            estatus: $('inbox-estatus').value
        });
        try {
            const result = await json(`/SolicitudAdjudicacion/listarBandeja?${params.toString()}`);
            renderRows(Array.isArray(result.rows) ? result.rows : []);
            actualizarStats(result.stats || {});
        } catch (error) {
            $('inbox-rows').innerHTML = `<tr><td colspan="6" class="inbox-empty text-danger">${esc(error.message)}</td></tr>`;
        } finally {
            $('inbox-refrescar').disabled = false;
        }
    }

    async function cargarResponsables() {
        if (state.responsables.length || state.cargandoResponsables) return;
        state.cargandoResponsables = true;
        try {
            const result = await json('/SolicitudAdjudicacion/responsablesBandeja');
            state.responsables = Array.isArray(result.rows) ? result.rows : [];
        } finally {
            state.cargandoResponsables = false;
        }
    }

    function field(label, value, col = 'col-12 col-md-6') {
        return `<div class="${col}"><span class="inbox-label">${esc(label)}</span><div class="inbox-value">${esc(texto(value))}</div></div>`;
    }

    function datosPorCanal(s) {
        const fields = [];
        if (s.telefono_actual) fields.push(field('Teléfono actual', s.telefono_actual));
        if (s.kilometraje !== null && s.kilometraje !== '') fields.push(field('Kilometraje', `${s.kilometraje} km`));
        if (s.direccion_resguardo) fields.push(field('Dirección de resguardo', s.direccion_resguardo, 'col-12'));
        if (s.motivo) fields.push(field('Motivo', s.motivo, 'col-12'));
        if (s.vin) fields.push(field('VIN', s.vin, 'col-12'));
        if (s.tipo_asignacion) fields.push(field('Asignación solicitada', s.tipo_asignacion));
        if (s.nombre_gestor || s.id_persona_gestor) {
            fields.push(field('Gestor solicitado', s.nombre_gestor || `Persona #${s.id_persona_gestor}`));
        }
        return fields.length
            ? `<div class="inbox-detail-block"><h6 class="mb-3">Información enviada por ${esc(canalLabel(s.canal))}</h6><div class="row g-3">${fields.join('')}</div></div>`
            : '';
    }

    function renderHistorial(historial) {
        if (!historial.length) return '<p class="inbox-muted mb-0">Aún no hay movimientos registrados.</p>';
        return historial.map((item) => `<div class="inbox-timeline">
            <div class="d-flex flex-wrap justify-content-between gap-2">
                <strong>${esc(statusLabel(item.estatus_nuevo))}</strong>
                <small class="inbox-muted">${esc(item.fecha_fmt)}</small>
            </div>
            <div class="small mt-1">${esc(texto(item.comentario, 'Sin comentario'))}</div>
            <small class="inbox-muted">${esc(texto(item.actor_nombre))} · ${esc(canalLabel(item.actor_canal))}</small>
        </div>`).join('');
    }

    function opcionesResponsables(s) {
        const actual = Number(s.id_persona_asignada || 0);
        const sugerido = actual || Number(s.id_persona_gestor || 0);
        const options = state.responsables.map((responsable) => {
            const id = Number(responsable.id_persona);
            const puesto = texto(responsable.puesto, '');
            const label = puesto ? `${responsable.nombre_completo} — ${puesto}` : responsable.nombre_completo;
            return `<option value="${id}" ${id === sugerido ? 'selected' : ''}>${esc(label)}</option>`;
        }).join('');
        return `<option value="">Selecciona un responsable</option>${options}`;
    }

    function renderDetalle(s) {
        const esTitular = Number(s.entregara_titular) === 1;
        const yaAsignada = String(s.estatus).toLowerCase() === 'asignada';
        const asignado = yaAsignada
            ? `<div class="alert alert-success mb-3">
                <div class="fw-bold"><i class="fa-solid fa-circle-check me-2"></i>Asignada a ${esc(texto(s.nombre_persona_asignada))}</div>
                <small>${esc(texto(s.fecha_asignacion_fmt, 'Fecha no registrada'))}${s.asignada_por_nombre ? ` · por ${esc(s.asignada_por_nombre)}` : ''}</small>
               </div>`
            : '';
        const sugerencia = !s.id_persona_asignada && s.id_persona_gestor
            ? `<div class="small text-primary mb-2"><i class="fa-solid fa-lightbulb me-1"></i>Se preseleccionó al gestor solicitado por Despachos; puedes cambiarlo.</div>`
            : '';

        $('inbox-detail-title').textContent = s.folio || 'Detalle';
        $('inbox-detail-body').innerHTML = `
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                ${badgeCanal(s.canal)}
                ${badgeEstatus(s.estatus)}
            </div>
            <div class="inbox-detail-block mb-3">
                <div class="row g-3">
                    ${field('Crédito', `#${s.id_credito}`)}
                    ${field('Cliente', s.nombre_cliente)}
                    ${field('Fecha de solicitud', s.fecha_alta_fmt)}
                    ${field('Solicitó', s.nombre_usuario_solicitante)}
                </div>
            </div>
            <div class="inbox-detail-block mb-3">
                <h6 class="mb-3">Persona que entregará la moto</h6>
                <div class="row g-3">
                    ${field('Tipo de entrega', esTitular ? 'Titular' : 'Tercero')}
                    ${!esTitular ? field('Nombre del entregante', s.nombre_entregante) : ''}
                </div>
            </div>
            <div class="mb-3">${datosPorCanal(s)}</div>
            <div class="inbox-detail-block inbox-assignment mb-3">
                <h6 class="mb-3"><i class="fa-solid fa-user-plus me-2 text-primary"></i>${yaAsignada ? 'Reasignar responsable' : 'Asignar responsable'}</h6>
                ${asignado}
                ${sugerencia}
                <label class="form-label" for="inbox-responsable">Responsable de Motos Adjudicadas</label>
                <select class="form-select mb-3" id="inbox-responsable">${opcionesResponsables(s)}</select>
                <label class="form-label" for="inbox-comentario">Indicaciones para la asignación <span class="fw-normal inbox-muted">(opcional)</span></label>
                <textarea class="form-control mb-3" id="inbox-comentario" rows="3" maxlength="1000" placeholder="Agrega contexto para quien continuará el flujo.">${esc(s.comentario_asignacion || '')}</textarea>
                <button class="btn btn-primary w-100" type="button" id="inbox-asignar">
                    <i class="fa-solid fa-paper-plane me-2"></i>${yaAsignada ? 'Guardar reasignación' : 'Asignar y enviar al flujo'}
                </button>
            </div>
            <div class="inbox-detail-block">
                <h6 class="mb-3">Historial de la solicitud</h6>
                ${renderHistorial(Array.isArray(s.historial) ? s.historial : [])}
            </div>`;

        $('inbox-asignar').addEventListener('click', asignarSolicitud);
    }

    async function abrirDetalle(id) {
        $('inbox-detail-column').classList.remove('d-none');
        $('inbox-list-column').classList.remove('col-12');
        $('inbox-list-column').classList.add('col-xl-7');
        $('inbox-detail-body').innerHTML = '<div class="inbox-empty"><span class="spinner-border spinner-border-sm me-2"></span>Cargando solicitud...</div>';
        try {
            const [result] = await Promise.all([
                json(`/SolicitudAdjudicacion/detalleBandeja/${Number(id)}`),
                cargarResponsables()
            ]);
            state.solicitud = result.solicitud;
            renderDetalle(state.solicitud);
            if (window.innerWidth < 1200) {
                $('inbox-detail-column').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (error) {
            $('inbox-detail-body').innerHTML = `<div class="alert alert-danger mb-0">${esc(error.message)}</div>`;
        }
    }

    async function asignarSolicitud() {
        const button = $('inbox-asignar');
        const idPersona = Number($('inbox-responsable').value || 0);
        if (!state.solicitud || !idPersona) {
            notify('warning', 'Selecciona un responsable.');
            return;
        }
        button.disabled = true;
        try {
            const result = await json('/SolicitudAdjudicacion/asignarBandeja', {
                method: 'POST',
                body: JSON.stringify({
                    id_solicitud: Number(state.solicitud.id),
                    id_persona: idPersona,
                    comentario: $('inbox-comentario').value.trim()
                })
            });
            state.solicitud = result.solicitud;
            renderDetalle(state.solicitud);
            await cargarBandeja();
            notify('success', 'Solicitud asignada', 'La solicitud ya fue enviada al flujo operativo de Motos Adjudicadas.');
        } catch (error) {
            button.disabled = false;
            notify('error', 'No se pudo asignar', error.message);
        }
    }

    function cerrarDetalle() {
        state.solicitud = null;
        $('inbox-detail-column').classList.add('d-none');
        $('inbox-list-column').classList.remove('col-xl-7');
        $('inbox-list-column').classList.add('col-12');
    }

    $('inbox-rows').addEventListener('click', (event) => {
        const target = event.target.closest('[data-id]');
        if (target) abrirDetalle(Number(target.dataset.id));
    });
    $('inbox-filtrar').addEventListener('click', cargarBandeja);
    $('inbox-refrescar').addEventListener('click', cargarBandeja);
    $('inbox-canal').addEventListener('change', cargarBandeja);
    $('inbox-estatus').addEventListener('change', cargarBandeja);
    $('inbox-q').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            cargarBandeja();
        }
    });
    $('inbox-close-detail').addEventListener('click', cerrarDetalle);

    cargarBandeja();
})();
</script>
