<?php $tablasDisponibles = !empty($solicitudes_tablas_disponibles); ?>
<style>
.sacc .sacc-hero { background:linear-gradient(135deg,#162b55,#3154d5 60%,#52a5ff); color:#fff; border:0; }
.sacc .sacc-panel { border:1px solid #e5e7eb; box-shadow:0 12px 30px rgba(15,23,42,.06); }
.sacc .sacc-step { width:34px; height:34px; display:inline-grid; place-items:center; border-radius:50%; background:#e8efff; color:#3154d5; font-weight:800; }
.sacc .sacc-status { border-radius:999px; padding:.25rem .6rem; background:#e8efff; color:#2446a8; font-size:.75rem; font-weight:800; text-transform:uppercase; }
.sacc .sacc-empty { padding:2rem; text-align:center; color:#667085; }
</style>

<div class="sacc container-fluid py-3">
    <div class="card sacc-hero mb-4">
        <div class="card-body p-4">
            <small class="fw-bold text-uppercase opacity-75">Motos Adjudicadas · Call Center</small>
            <h3 class="text-white mt-1 mb-2">Levantar solicitud desde dictaminación</h3>
            <p class="mb-0 opacity-75">La solicitud se captura dentro del dictamen de llamada, como establece el requerimiento.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card sacc-panel h-100">
                <div class="card-header"><h5 class="mb-0">Abrir crédito en Call Center</h5></div>
                <div class="card-body">
                    <form method="post" action="/EstadoCuenta/Consulta">
                        <input type="hidden" name="modoBusqueda" value="id">
                        <label class="form-label fw-semibold" for="sacc-id-credito">ID del crédito</label>
                        <div class="input-group">
                            <input type="number" min="1" required class="form-control" id="sacc-id-credito" name="idCredito" placeholder="ID de crédito">
                            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-arrow-right me-1"></i>Abrir dictaminación</button>
                        </div>
                    </form>

                    <hr class="my-4">
                    <div class="d-flex gap-3 mb-3"><span class="sacc-step">1</span><div><strong>Busca el crédito</strong><div class="text-muted small">Se abrirá su estado de cuenta.</div></div></div>
                    <div class="d-flex gap-3 mb-3"><span class="sacc-step">2</span><div><strong>Selecciona “Dictaminar llamada”</strong><div class="text-muted small">Es el botón con el icono de audífonos.</div></div></div>
                    <div class="d-flex gap-3"><span class="sacc-step">3</span><div><strong>Activa “Levantar solicitud de adjudicación”</strong><div class="text-muted small">Captura titular o tercero y guarda el dictamen.</div></div></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card sacc-panel">
                <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div><h5 class="mb-1">Mis solicitudes de Call Center</h5><small class="text-muted">Solicitudes generadas junto con los dictámenes.</small></div>
                    <div class="input-group" style="max-width:280px">
                        <input id="sacc-filtro" class="form-control" placeholder="Folio, crédito o cliente">
                        <button id="sacc-refrescar" class="btn btn-outline-primary" type="button"><i class="fa-solid fa-rotate"></i></button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Folio</th><th>Crédito / Cliente</th><th>Entrega</th><th>Estatus</th><th>Fecha</th><th></th></tr></thead>
                        <tbody id="sacc-lista"><tr><td colspan="6" class="sacc-empty">Cargando solicitudes...</td></tr></tbody>
                    </table>
                </div>
            </div>
            <div id="sacc-detalle-card" class="card sacc-panel mt-4 d-none">
                <div class="card-header d-flex justify-content-between"><h5 class="mb-0">Detalle e historial</h5><button id="sacc-cerrar" type="button" class="btn-close"></button></div>
                <div id="sacc-detalle" class="card-body"></div>
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
    async function api(url) {
        const response = await fetch(url, {headers:{Accept:'application/json'}});
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message || 'Error de comunicación.');
        return body;
    }
    async function cargar() {
        if (!tablas) {
            $('sacc-lista').innerHTML = '<tr><td colspan="6" class="sacc-empty">Falta aplicar la migración de solicitudes.</td></tr>';
            return;
        }
        try {
            const r = await api('/SolicitudAdjudicacion/listarCallCenter?q=' + encodeURIComponent($('sacc-filtro').value.trim()));
            const rows = Array.isArray(r.rows) ? r.rows : [];
            $('sacc-lista').innerHTML = rows.length ? rows.map(x => `<tr>
                <td><strong>${esc(x.folio)}</strong></td>
                <td><strong>#${esc(x.id_credito)}</strong><br><small class="text-muted">${esc(x.nombre_cliente || '')}</small></td>
                <td>${Number(x.entregara_titular) === 1 ? 'Titular' : esc(x.nombre_entregante || 'Tercero')}</td>
                <td><span class="sacc-status">${esc(x.estatus)}</span></td>
                <td>${esc(x.fecha_alta_fmt)}</td>
                <td><button class="btn btn-sm btn-outline-primary sacc-ver" data-id="${Number(x.id)}"><i class="fa-regular fa-eye"></i></button></td>
            </tr>`).join('') : '<tr><td colspan="6" class="sacc-empty">Aún no tienes solicitudes registradas.</td></tr>';
        } catch (error) {
            $('sacc-lista').innerHTML = `<tr><td colspan="6" class="sacc-empty text-danger">${esc(error.message)}</td></tr>`;
        }
    }
    $('sacc-lista').addEventListener('click', async event => {
        const button = event.target.closest('.sacc-ver');
        if (!button) return;
        try {
            const r = await api('/SolicitudAdjudicacion/detalleCallCenter/' + Number(button.dataset.id));
            if (!r.success) throw new Error(r.message || 'Solicitud no encontrada.');
            const s = r.solicitud, historial = Array.isArray(s.historial) ? s.historial : [];
            $('sacc-detalle').innerHTML = `<div class="row g-3 mb-4">
                <div class="col-md-4"><small class="text-muted">Folio</small><div class="fw-bold">${esc(s.folio)}</div></div>
                <div class="col-md-4"><small class="text-muted">Crédito</small><div class="fw-bold">#${esc(s.id_credito)}</div></div>
                <div class="col-md-4"><small class="text-muted">Entrega</small><div>${Number(s.entregara_titular) === 1 ? 'Titular' : esc(s.nombre_entregante)}</div></div>
                ${Number(s.entregara_titular) === 0 ? `<div class="col-md-4"><small class="text-muted">Teléfono</small><div>${esc(s.telefono_actual)}</div></div><div class="col-md-8"><small class="text-muted">Motivo</small><div>${esc(s.motivo)}</div></div>` : ''}
            </div><h6>Historial</h6>${historial.map(h => `<div class="border-start border-primary ps-3 py-2"><strong>${esc(h.estatus_nuevo)}</strong><div>${esc(h.comentario)}</div><small class="text-muted">${esc(h.fecha_fmt)} · ${esc(h.actor_nombre)}</small></div>`).join('')}`;
            $('sacc-detalle-card').classList.remove('d-none');
        } catch (error) {
            if (window.Swal) Swal.fire('Error', error.message, 'error');
        }
    });
    $('sacc-cerrar').addEventListener('click', () => $('sacc-detalle-card').classList.add('d-none'));
    $('sacc-refrescar').addEventListener('click', cargar);
    $('sacc-filtro').addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); cargar(); } });
    cargar();
})();
</script>
