<?php
$titulo = 'Traspasos';
?>

<link rel="stylesheet" href="/assets/css/almacen-virtual-dark.css?v=20260716">

<div id="avt" class="container-fluid py-3 px-3 px-md-4">
    <div class="avt-head mb-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div class="d-flex gap-3 align-items-start">
                <span class="avt-head-icon"><i class="fa-solid fa-right-left"></i></span>
                <div>
                    <div class="avt-eyebrow">Almacen Virtual</div>
                    <h4 class="mb-1"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h4>
                    <div class="text-muted small">Ordenes entre agencias con evidencia de origen y VoBo de destino.</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="/MotosAdjudicadas/pisoVenta" class="btn btn-label-secondary">
                    <i class="fa-solid fa-store me-1"></i>Piso de venta
                </a>
                <a href="/MotosAdjudicadas/kanbanOperativo" class="btn btn-primary">
                    <i class="fa-solid fa-table-columns me-1"></i>Kanban
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avt-kpi"><div class="card-body">
                <div class="text-muted small">Disponibles</div>
                <div class="avt-kpi-value" id="avt-kpi-disponibles">0</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avt-kpi"><div class="card-body">
                <div class="text-muted small">Creadas</div>
                <div class="avt-kpi-value" id="avt-kpi-creadas">0</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avt-kpi"><div class="card-body">
                <div class="text-muted small">En transito</div>
                <div class="avt-kpi-value" id="avt-kpi-transito">0</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avt-kpi"><div class="card-body">
                <div class="text-muted small">Recibidas</div>
                <div class="avt-kpi-value" id="avt-kpi-recibidas">0</div>
            </div></div>
        </div>
    </div>

    <section class="avt-panel mb-3">
        <div class="avt-panel-head">
            <div>
                <h5 class="mb-1">Unidades disponibles para traspaso</h5>
                <div class="text-muted small">Solo unidades en estatus lista para venta.</div>
            </div>
            <button type="button" id="avt-refresh-disponibles" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-rotate me-1"></i>Actualizar
            </button>
        </div>
        <div class="avt-filters">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="avt-q">Buscar</label>
                    <input id="avt-q" type="search" class="form-control" placeholder="Folio, VIN, marca, credito">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-celula">Celula</label>
                    <select id="avt-celula" class="form-select"><option value="">Todas</option></select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-ubicacion">Agencia origen</label>
                    <select id="avt-ubicacion" class="form-select"><option value="">Todas</option></select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-cliente">Cliente</label>
                    <select id="avt-cliente" class="form-select">
                        <option value="">Todos</option>
                        <option value="Pension a Max">Pension a Max</option>
                        <option value="Amigo Efectivo">Amigo Efectivo</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-limit">Registros</label>
                    <select id="avt-limit" class="form-select">
                        <option value="8" selected>8</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table border-top avt-table mb-0">
                <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Datos</th>
                        <th>Cliente</th>
                        <th>Origen</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody id="avt-disponibles-body">
                    <tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="avt-footer">
            <div class="dataTables_info" id="avt-disponibles-info">Mostrando 0 a 0 de 0 unidades</div>
            <ul class="pagination mb-0" id="avt-disponibles-pagination"></ul>
        </div>
    </section>

    <section class="avt-panel">
        <div class="avt-panel-head">
            <div>
                <h5 class="mb-1">Ordenes de traspaso</h5>
                <div class="text-muted small">Seguimiento de salida, transito y recepcion en destino.</div>
            </div>
            <button type="button" id="avt-refresh-ordenes" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-rotate me-1"></i>Actualizar
            </button>
        </div>
        <div class="avt-filters">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg-3">
                    <label class="form-label" for="avt-orden-q">Buscar</label>
                    <input id="avt-orden-q" type="search" class="form-control" placeholder="Folio, unidad, transportista">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-orden-estatus">Estatus</label>
                    <select id="avt-orden-estatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="creada">Creada</option>
                        <option value="en_transito">En transito</option>
                        <option value="recibida">Recibida</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-orden-origen">Origen</label>
                    <select id="avt-orden-origen" class="form-select"><option value="">Todos</option></select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-orden-destino">Destino</label>
                    <select id="avt-orden-destino" class="form-select"><option value="">Todos</option></select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="avt-orden-limit">Registros</label>
                    <select id="avt-orden-limit" class="form-select">
                        <option value="8" selected>8</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table border-top avt-table mb-0">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Unidad</th>
                        <th>Ruta</th>
                        <th>Transportista</th>
                        <th>Estatus</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody id="avt-ordenes-body">
                    <tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="avt-footer">
            <div class="dataTables_info" id="avt-ordenes-info">Mostrando 0 a 0 de 0 ordenes</div>
            <ul class="pagination mb-0" id="avt-ordenes-pagination"></ul>
        </div>
    </section>
</div>

<div class="modal fade" id="avt-create-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="avt-create-form" enctype="multipart/form-data">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Crear orden de traspaso</h5>
                    <div class="text-muted small" id="avt-create-unit-label">Unidad</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_unidad" id="avt-create-id-unidad">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="avt-create-destino">Agencia destino</label>
                        <select class="form-select" id="avt-create-destino" name="id_ubicacion_destino" required>
                            <option value="">Selecciona destino</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="avt-create-fecha">Salida estimada</label>
                        <input type="datetime-local" class="form-control" id="avt-create-fecha" name="fecha_salida_estimada" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="avt-create-tipo">Tipo transportista</label>
                        <select class="form-select" id="avt-create-tipo" name="tipo_transportista" required>
                            <option value="">Selecciona</option>
                            <option value="interno">Interno</option>
                            <option value="externo">Externo</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="avt-create-nombre">Transportista</label>
                        <input type="text" class="form-control" id="avt-create-nombre" name="transportista_nombre" maxlength="150" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="avt-create-contacto">Contacto</label>
                        <input type="text" class="form-control" id="avt-create-contacto" name="transportista_contacto" maxlength="80">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="avt-create-foto">Evidencia origen</label>
                        <input type="file" class="form-control" id="avt-create-foto" name="tras_origen_foto" accept="image/*" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="avt-create-obs">Observaciones origen</label>
                        <textarea class="form-control" id="avt-create-obs" name="observaciones_origen" rows="3" maxlength="1200"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-right-left me-1"></i>Crear traspaso
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="avt-receive-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="avt-receive-form" enctype="multipart/form-data">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">VoBo destino</h5>
                    <div class="text-muted small" id="avt-receive-label">Orden</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_traspaso" id="avt-receive-id-traspaso">
                <input type="hidden" name="id_unidad" id="avt-receive-id-unidad">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="avt-receive-foto">Evidencia destino</label>
                        <input type="file" class="form-control" id="avt-receive-foto" name="tras_destino_foto" accept="image/*" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="avt-receive-obs">Observaciones destino</label>
                        <textarea class="form-control" id="avt-receive-obs" name="observaciones_destino" rows="3" maxlength="1200"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-clipboard-check me-1"></i>Confirmar recepcion
                </button>
            </div>
        </form>
    </div>
</div>

<style>
#avt .avt-head,
#avt .avt-panel {
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: .5rem;
}
#avt .avt-head {
    padding: 1rem;
}
#avt .avt-head-icon {
    width: 3rem;
    height: 3rem;
    border-radius: .5rem;
    background: #ffedd5;
    color: #c2410c;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
#avt .avt-eyebrow {
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0;
}
#avt .avt-kpi {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
}
#avt .avt-kpi-value {
    color: #0f172a;
    font-size: 1.55rem;
    font-weight: 800;
}
#avt .avt-panel-head {
    padding: 1rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e2e8f0;
}
#avt .avt-filters {
    padding: 1rem;
}
#avt .avt-table th {
    color: #64748b;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: 0;
}
#avt .avt-table td {
    vertical-align: middle;
}
#avt .avt-thumb {
    width: 3rem;
    height: 3rem;
    border-radius: .45rem;
    object-fit: cover;
    background: #f1f5f9;
}
#avt .avt-thumb-empty {
    width: 3rem;
    height: 3rem;
    border-radius: .45rem;
    background: #f1f5f9;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
#avt .avt-footer {
    border-top: 1px solid #e2e8f0;
    padding: .85rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
#avt .avt-footer .dataTables_info {
    color: #64748b;
    font-size: .875rem;
}
#avt .avt-footer .page-link {
    min-width: 2.2rem;
    text-align: center;
}
@media (max-width: 767.98px) {
    #avt .avt-panel-head,
    #avt .avt-footer {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<script>
(function () {
    const disp = { page: 1, limit: 8, pages: 1, total: 0 };
    const ord = { page: 1, limit: 8, pages: 1, total: 0 };
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

    function toast(message, type = 'info') {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon: type, title: message, showConfirmButton: false, timer: 2800 });
            return;
        }
        console.log(message);
    }

    function modal(id) {
        return window.bootstrap ? bootstrap.Modal.getOrCreateInstance($(id)) : null;
    }

    function rangeInfo(total, page, limit, label) {
        const start = total > 0 ? ((page - 1) * limit) + 1 : 0;
        const end = total > 0 ? Math.min(page * limit, total) : 0;
        return 'Mostrando ' + start + ' a ' + end + ' de ' + total + ' ' + label;
    }

    function pageNumbers(current, pages) {
        const totalPages = Math.max(1, Number(pages || 1));
        const out = [];
        let start = Math.max(1, current - 2);
        let end = Math.min(totalPages, start + 4);
        start = Math.max(1, end - 4);
        for (let page = start; page <= end; page++) out.push(page);
        return out;
    }

    function renderPagination(elId, state) {
        const el = $(elId);
        if (!el) return;
        const current = Math.max(1, Number(state.page || 1));
        const totalPages = Math.max(1, Number(state.pages || 1));
        const item = (label, target, disabled, active) => {
            const cls = ['page-item'];
            if (disabled) cls.push('disabled');
            if (active) cls.push('active');
            const attrs = disabled ? 'tabindex="-1" aria-disabled="true"' : 'href="#" data-page="' + esc(target) + '"';
            return '<li class="' + cls.join(' ') + '"><a class="page-link" ' + attrs + '>' + label + '</a></li>';
        };
        el.innerHTML = [
            item('&laquo;', current - 1, current <= 1, false),
            ...pageNumbers(current, totalPages).map((p) => item(String(p), p, false, p === current)),
            item('&raquo;', current + 1, current >= totalPages, false)
        ].join('');
    }

    function paramsDisponibles() {
        const p = new URLSearchParams();
        p.set('page', String(disp.page));
        p.set('limit', String(disp.limit));
        const q = $('avt-q')?.value.trim() || '';
        const celula = $('avt-celula')?.value || '';
        const ubicacion = $('avt-ubicacion')?.value || '';
        const cliente = $('avt-cliente')?.value || '';
        if (q) p.set('q', q);
        if (celula) p.set('id_celula', celula);
        if (ubicacion) p.set('id_ubicacion', ubicacion);
        if (cliente) p.set('cliente_destino', cliente);
        return p;
    }

    function paramsOrdenes() {
        const p = new URLSearchParams();
        p.set('page', String(ord.page));
        p.set('limit', String(ord.limit));
        const q = $('avt-orden-q')?.value.trim() || '';
        const estatus = $('avt-orden-estatus')?.value || '';
        const origen = $('avt-orden-origen')?.value || '';
        const destino = $('avt-orden-destino')?.value || '';
        if (q) p.set('q', q);
        if (estatus) p.set('estatus_traspaso', estatus);
        if (origen) p.set('id_ubicacion_origen', origen);
        if (destino) p.set('id_ubicacion_destino', destino);
        return p;
    }

    function unidadHtml(row) {
        const img = row.foto_url_publica
            ? '<img src="' + esc(row.foto_url_publica) + '" class="avt-thumb" alt="Foto unidad">'
            : '<span class="avt-thumb-empty"><i class="fa-solid fa-motorcycle"></i></span>';
        return '<div class="d-flex align-items-center gap-2">' + img + '<div><div class="fw-semibold">' + esc(row.folio_unidad || ('Unidad #' + row.id_unidad)) + '</div><div class="text-muted small">' + esc(row.nombre_celula || '') + '</div></div></div>';
    }

    function renderDisponibles(rows) {
        const body = $('avt-disponibles-body');
        if (!body) return;
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Sin unidades disponibles para traspaso.</td></tr>';
            return;
        }
        body.innerHTML = rows.map((row) => {
            const cliente = row.cliente_destino || 'Sin cliente formal';
            return '<tr>' +
                '<td>' + unidadHtml(row) + '</td>' +
                '<td><div class="fw-semibold">' + esc([row.marca, row.modelo, row.anio].filter(Boolean).join(' ')) + '</div><div class="text-muted small">VIN: ' + esc(row.vin || 'N/D') + '</div><div class="text-muted small">Color: ' + esc(row.color || 'N/D') + '</div></td>' +
                '<td><span class="badge bg-label-success text-success">' + esc(cliente) + '</span></td>' +
                '<td><div class="fw-semibold">' + esc(row.nombre_ubicacion || 'Sin asignar') + '</div><div class="text-muted small">' + esc(row.tipo_ubicacion || '') + '</div></td>' +
                '<td><button type="button" class="btn btn-sm btn-primary avt-open-create" data-id="' + esc(row.id_unidad) + '" data-label="' + esc(row.folio_unidad || ('Unidad #' + row.id_unidad)) + '" data-origen="' + esc(row.id_ubicacion_actual || '') + '"><i class="fa-solid fa-right-left me-1"></i>Crear</button></td>' +
            '</tr>';
        }).join('');
    }

    function estatusBadge(estatus) {
        const map = {
            creada: 'bg-label-secondary text-secondary',
            en_transito: 'bg-label-warning text-warning',
            recibida: 'bg-label-success text-success',
            cancelada: 'bg-label-danger text-danger'
        };
        return '<span class="badge ' + (map[estatus] || 'bg-label-secondary text-secondary') + '">' + esc(String(estatus || '').replace(/_/g, ' ')) + '</span>';
    }

    function renderOrdenes(rows) {
        const body = $('avt-ordenes-body');
        if (!body) return;
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Sin ordenes de traspaso.</td></tr>';
            return;
        }
        body.innerHTML = rows.map((row) => {
            const puedeRecibir = ['creada', 'en_transito'].includes(String(row.estatus_traspaso || ''));
            const accion = puedeRecibir
                ? '<button type="button" class="btn btn-sm btn-success avt-open-receive" data-id="' + esc(row.id_traspaso) + '" data-unidad="' + esc(row.id_unidad) + '" data-label="' + esc(row.folio_traspaso || '') + '"><i class="fa-solid fa-clipboard-check me-1"></i>VoBo</button>'
                : '<span class="text-muted small">Cerrada</span>';
            return '<tr>' +
                '<td><div class="fw-semibold">' + esc(row.folio_traspaso || ('Orden #' + row.id_traspaso)) + '</div><div class="text-muted small">' + esc(row.fecha_creacion_fmt || '') + '</div></td>' +
                '<td>' + unidadHtml(row) + '</td>' +
                '<td><div class="fw-semibold">' + esc(row.ubicacion_origen_nombre || 'Origen') + '</div><div class="text-muted small">a ' + esc(row.ubicacion_destino_nombre || 'Destino') + '</div></td>' +
                '<td><div class="fw-semibold">' + esc(row.transportista_nombre || 'N/D') + '</div><div class="text-muted small">' + esc(row.tipo_transportista || '') + ' ' + esc(row.fecha_salida_estimada_fmt || '') + '</div></td>' +
                '<td>' + estatusBadge(row.estatus_traspaso) + '</td>' +
                '<td>' + accion + '</td>' +
            '</tr>';
        }).join('');
    }

    async function loadResumen() {
        const res = await fetch('/MotosAdjudicadas/traspasosResumen', { headers: { Accept: 'application/json' } });
        const json = await res.json();
        const d = json.datos || {};
        $('avt-kpi-disponibles').textContent = Number(d.disponibles || 0).toLocaleString('es-MX');
        $('avt-kpi-creadas').textContent = Number(d.creadas || 0).toLocaleString('es-MX');
        $('avt-kpi-transito').textContent = Number(d.en_transito || 0).toLocaleString('es-MX');
        $('avt-kpi-recibidas').textContent = Number(d.recibidas || 0).toLocaleString('es-MX');
    }

    async function loadCatalogos() {
        const [celulasRes, ubicacionesRes] = await Promise.all([
            fetch('/MotosAdjudicadas/inventarioCelulas', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/MotosAdjudicadas/inventarioUbicaciones', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null)
        ]);
        if (celulasRes?.success) {
            const opts = '<option value="">Todas</option>' + (celulasRes.datos || []).map((row) => '<option value="' + esc(row.id_celula) + '">' + esc(row.nombre) + '</option>').join('');
            $('avt-celula').innerHTML = opts;
        }
        if (ubicacionesRes?.success) {
            const opts = '<option value="">Todas</option>' + (ubicacionesRes.datos || []).map((row) => '<option value="' + esc(row.id_ubicacion) + '">' + esc(row.nombre_ubicacion) + '</option>').join('');
            $('avt-ubicacion').innerHTML = opts;
            $('avt-orden-origen').innerHTML = '<option value="">Todos</option>' + opts.replace('<option value="">Todas</option>', '');
            $('avt-orden-destino').innerHTML = '<option value="">Todos</option>' + opts.replace('<option value="">Todas</option>', '');
            $('avt-create-destino').innerHTML = '<option value="">Selecciona destino</option>' + opts.replace('<option value="">Todas</option>', '');
        }
    }

    async function loadDisponibles(resetPage = false) {
        if (resetPage) disp.page = 1;
        const body = $('avt-disponibles-body');
        if (body) body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr>';
        const res = await fetch('/MotosAdjudicadas/traspasosUnidadesDisponibles?' + paramsDisponibles().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) body.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">' + esc(json.message || 'No se pudo cargar.') + '</td></tr>';
            return;
        }
        disp.total = Number(json.total || 0);
        disp.page = Number(json.page || 1);
        disp.limit = Number(json.limit || disp.limit);
        disp.pages = Number(json.pages || 1);
        renderDisponibles(json.rows || []);
        $('avt-disponibles-info').textContent = rangeInfo(disp.total, disp.page, disp.limit, 'unidades');
        renderPagination('avt-disponibles-pagination', disp);
    }

    async function loadOrdenes(resetPage = false) {
        if (resetPage) ord.page = 1;
        const body = $('avt-ordenes-body');
        if (body) body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>';
        const res = await fetch('/MotosAdjudicadas/traspasosOrdenes?' + paramsOrdenes().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) body.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">' + esc(json.message || 'No se pudo cargar.') + '</td></tr>';
            return;
        }
        ord.total = Number(json.total || 0);
        ord.page = Number(json.page || 1);
        ord.limit = Number(json.limit || ord.limit);
        ord.pages = Number(json.pages || 1);
        renderOrdenes(json.rows || []);
        $('avt-ordenes-info').textContent = rangeInfo(ord.total, ord.page, ord.limit, 'ordenes');
        renderPagination('avt-ordenes-pagination', ord);
    }

    async function refreshAll() {
        await Promise.all([loadResumen(), loadDisponibles(false), loadOrdenes(false)]);
    }

    function bindEvents() {
        $('avt-refresh-disponibles')?.addEventListener('click', () => loadDisponibles(true));
        $('avt-refresh-ordenes')?.addEventListener('click', () => loadOrdenes(true));
        $('avt-limit')?.addEventListener('change', () => {
            disp.limit = Number($('avt-limit')?.value || 8) || 8;
            loadDisponibles(true);
        });
        $('avt-orden-limit')?.addEventListener('change', () => {
            ord.limit = Number($('avt-orden-limit')?.value || 8) || 8;
            loadOrdenes(true);
        });
        ['avt-q', 'avt-celula', 'avt-ubicacion', 'avt-cliente'].forEach((id) => {
            $(id)?.addEventListener(id === 'avt-q' ? 'input' : 'change', () => {
                clearTimeout(window.__avtDispTimer);
                window.__avtDispTimer = setTimeout(() => loadDisponibles(true), 260);
            });
        });
        ['avt-orden-q', 'avt-orden-estatus', 'avt-orden-origen', 'avt-orden-destino'].forEach((id) => {
            $(id)?.addEventListener(id === 'avt-orden-q' ? 'input' : 'change', () => {
                clearTimeout(window.__avtOrdTimer);
                window.__avtOrdTimer = setTimeout(() => loadOrdenes(true), 260);
            });
        });
        $('avt-disponibles-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const next = Number(link.dataset.page || 1);
            if (next && next !== disp.page) {
                disp.page = next;
                loadDisponibles(false);
            }
        });
        $('avt-ordenes-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const next = Number(link.dataset.page || 1);
            if (next && next !== ord.page) {
                ord.page = next;
                loadOrdenes(false);
            }
        });
        $('avt-disponibles-body')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.avt-open-create');
            if (!btn) return;
            $('avt-create-form')?.reset();
            $('avt-create-id-unidad').value = btn.dataset.id || '';
            $('avt-create-unit-label').textContent = btn.dataset.label || 'Unidad';
            const origen = btn.dataset.origen || '';
            Array.from($('avt-create-destino')?.options || []).forEach((opt) => {
                opt.disabled = origen !== '' && opt.value === origen;
            });
            modal('avt-create-modal')?.show();
        });
        $('avt-ordenes-body')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.avt-open-receive');
            if (!btn) return;
            $('avt-receive-form')?.reset();
            $('avt-receive-id-traspaso').value = btn.dataset.id || '';
            $('avt-receive-id-unidad').value = btn.dataset.unidad || '';
            $('avt-receive-label').textContent = btn.dataset.label || 'Orden';
            modal('avt-receive-modal')?.show();
        });
        $('avt-create-form')?.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const form = ev.currentTarget;
            const res = await fetch('/MotosAdjudicadas/traspasosCrearOrden', { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!json.success) {
                toast(json.message || 'No se pudo crear el traspaso.', 'error');
                return;
            }
            modal('avt-create-modal')?.hide();
            toast(json.message || 'Traspaso creado.', 'success');
            await refreshAll();
        });
        $('avt-receive-form')?.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const form = ev.currentTarget;
            const res = await fetch('/MotosAdjudicadas/traspasosConfirmarRecepcion', { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!json.success) {
                toast(json.message || 'No se pudo confirmar la recepcion.', 'error');
                return;
            }
            modal('avt-receive-modal')?.hide();
            toast(json.message || 'Traspaso recibido.', 'success');
            await refreshAll();
        });
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const qs = new URLSearchParams(window.location.search);
        const unidad = qs.get('unidad');
        if (unidad) $('avt-q').value = unidad;
        await loadCatalogos();
        bindEvents();
        await refreshAll();
    });
})();
</script>
