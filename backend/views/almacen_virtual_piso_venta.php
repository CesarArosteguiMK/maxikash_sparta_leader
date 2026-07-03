<?php
$titulo = 'Piso de Venta';
?>

<div id="avp" class="container-fluid py-3 px-3 px-md-4">
    <div class="avp-head mb-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div class="d-flex gap-3 align-items-start">
                <span class="avp-head-icon"><i class="fa-solid fa-store"></i></span>
                <div>
                    <div class="avp-eyebrow">Almacen Virtual</div>
                    <h4 class="mb-1"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h4>
                    <div class="text-muted small">Unidades reparadas listas para venta por cliente y agencia.</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="/MotosAdjudicadas/kanbanOperativo" class="btn btn-label-secondary">
                    <i class="fa-solid fa-table-columns me-1"></i>Kanban
                </a>
                <a href="/MotosAdjudicadas/traspasos" class="btn btn-primary">
                    <i class="fa-solid fa-right-left me-1"></i>Traspasos
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avp-kpi">
                <div class="card-body">
                    <div class="text-muted small">Listas para venta</div>
                    <div class="avp-kpi-value" id="avp-kpi-total">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avp-kpi">
                <div class="card-body">
                    <div class="text-muted small">Pension a Max</div>
                    <div class="avp-kpi-value" id="avp-kpi-pension">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avp-kpi">
                <div class="card-body">
                    <div class="text-muted small">Amigo Efectivo</div>
                    <div class="avp-kpi-value" id="avp-kpi-amigo">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-none avp-kpi">
                <div class="card-body">
                    <div class="text-muted small">Sin cliente formal</div>
                    <div class="avp-kpi-value" id="avp-kpi-sin-cliente">0</div>
                </div>
            </div>
        </div>
    </div>

    <section class="avp-filters mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label" for="avp-q">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                    <input id="avp-q" type="search" class="form-control" placeholder="Folio, VIN, serie, marca, credito">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="avp-celula">Celula</label>
                <select id="avp-celula" class="form-select">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="avp-ubicacion">Agencia</label>
                <select id="avp-ubicacion" class="form-select">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="avp-cliente">Cliente</label>
                <select id="avp-cliente" class="form-select">
                    <option value="">Todos</option>
                    <option value="Pension a Max">Pension a Max</option>
                    <option value="Amigo Efectivo">Amigo Efectivo</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 d-grid">
                <button type="button" id="avp-refresh" class="btn btn-outline-primary">
                    <i class="fa-solid fa-rotate me-1"></i>Actualizar
                </button>
            </div>
        </div>
    </section>

    <section class="avp-table-wrap">
        <div class="avp-datatable-controls row mx-0 align-items-center">
            <div class="col-sm-12 col-md-6">
                <div class="dataTables_length">
                    <label>
                        Mostrar
                        <select id="avp-limit" class="form-select form-select-sm">
                            <option value="8" selected>8</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        registros
                    </label>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-responsive table border-top avp-table">
                <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Datos</th>
                        <th>Cliente</th>
                        <th>Ubicacion</th>
                        <th>Dias</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="avp-body">
                    <tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="avp-datatable-footer row mx-0 align-items-center">
            <div class="col-sm-12 col-md-6">
                <div class="dataTables_info" id="avp-pager-info">Mostrando 0 a 0 de 0 unidades</div>
            </div>
            <div class="col-sm-12 col-md-6">
                <nav aria-label="Paginacion piso venta">
                    <ul class="pagination mb-0 justify-content-md-end justify-content-center" id="avp-pagination"></ul>
                </nav>
            </div>
        </div>
    </section>
</div>

<style>
#avp .avp-head,
#avp .avp-filters,
#avp .avp-table-wrap {
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: .5rem;
}
#avp .avp-head,
#avp .avp-filters {
    padding: 1rem;
}
#avp .avp-head-icon {
    width: 3rem;
    height: 3rem;
    border-radius: .5rem;
    background: #dcfce7;
    color: #15803d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
#avp .avp-eyebrow {
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0;
}
#avp .avp-kpi {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
}
#avp .avp-kpi-value {
    color: #0f172a;
    font-size: 1.55rem;
    font-weight: 800;
}
#avp .avp-datatable-controls {
    padding: .85rem 1rem;
}
#avp .avp-datatable-controls .dataTables_length label {
    color: #64748b;
    display: inline-flex;
    gap: .5rem;
    align-items: center;
    margin: 0;
}
#avp .avp-datatable-controls .dataTables_length select {
    width: auto;
}
#avp .avp-table th {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: 0;
    color: #64748b;
}
#avp .avp-table td {
    vertical-align: middle;
}
#avp .avp-thumb {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: .45rem;
    object-fit: cover;
    background: #f1f5f9;
}
#avp .avp-thumb-empty {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: .45rem;
    background: #f1f5f9;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
#avp .avp-datatable-footer {
    border-top: 1px solid #e2e8f0;
    padding: .85rem 1rem;
}
#avp .avp-datatable-footer .dataTables_info {
    color: #64748b;
    font-size: .875rem;
}
#avp .avp-datatable-footer .page-link {
    min-width: 2.2rem;
    text-align: center;
}
@media (max-width: 767.98px) {
    #avp .avp-datatable-footer {
        gap: .75rem;
    }
}
</style>

<script>
(function () {
    const state = { page: 1, limit: 8, pages: 1, total: 0 };
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

    function toast(message, type = 'info') {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon: type, title: message, showConfirmButton: false, timer: 2600 });
            return;
        }
        console.log(message);
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

    function renderPagination() {
        const el = $('avp-pagination');
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

    function params() {
        const p = new URLSearchParams();
        p.set('page', String(state.page));
        p.set('limit', String(state.limit));
        const q = $('avp-q')?.value.trim() || '';
        const celula = $('avp-celula')?.value || '';
        const ubicacion = $('avp-ubicacion')?.value || '';
        const cliente = $('avp-cliente')?.value || '';
        if (q) p.set('q', q);
        if (celula) p.set('id_celula', celula);
        if (ubicacion) p.set('id_ubicacion', ubicacion);
        if (cliente) p.set('cliente_destino', cliente);
        return p;
    }

    function renderRows(rows) {
        const body = $('avp-body');
        if (!body) return;
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Sin unidades listas para venta.</td></tr>';
            return;
        }
        body.innerHTML = rows.map((row) => {
            const img = row.foto_url_publica
                ? '<img src="' + esc(row.foto_url_publica) + '" class="avp-thumb" alt="Foto unidad">'
                : '<span class="avp-thumb-empty"><i class="fa-solid fa-motorcycle"></i></span>';
            const cliente = row.cliente_destino || 'Sin cliente formal';
            const clienteBadge = row.cliente_destino ? 'bg-label-success text-success' : 'bg-label-secondary text-secondary';
            const sla = row.sla || {};
            const slaBadge = sla.color === 'danger' ? 'bg-label-danger text-danger' : (sla.color === 'warning' ? 'bg-label-warning text-warning' : 'bg-label-success text-success');
            return '<tr>' +
                '<td><div class="d-flex align-items-center gap-2">' + img + '<div><div class="fw-semibold">' + esc(row.folio_unidad || ('Unidad #' + row.id_unidad)) + '</div><div class="text-muted small">' + esc(row.nombre_celula || '') + '</div></div></div></td>' +
                '<td><div class="fw-semibold">' + esc([row.marca, row.modelo, row.anio].filter(Boolean).join(' ')) + '</div><div class="text-muted small">VIN: ' + esc(row.vin || 'N/D') + '</div><div class="text-muted small">Color: ' + esc(row.color || 'N/D') + '</div></td>' +
                '<td><span class="badge ' + clienteBadge + '">' + esc(cliente) + '</span><div class="text-muted small mt-1">' + esc(row.fecha_envio_fmt || '') + '</div></td>' +
                '<td><div class="fw-semibold">' + esc(row.nombre_ubicacion || 'Sin asignar') + '</div><div class="text-muted small">' + esc(row.tipo_ubicacion || '') + '</div></td>' +
                '<td><span class="badge ' + slaBadge + '">' + esc(row.dias_almacen || 0) + ' dias</span></td>' +
                '<td><a href="/MotosAdjudicadas/traspasos?unidad=' + encodeURIComponent(String(row.id_unidad || '')) + '" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-right-left me-1"></i>Traspasar</a></td>' +
            '</tr>';
        }).join('');
    }

    async function loadResumen() {
        const res = await fetch('/MotosAdjudicadas/pisoVentaResumen', { headers: { Accept: 'application/json' } });
        const json = await res.json();
        const d = json.datos || {};
        $('avp-kpi-total').textContent = Number(d.total || 0).toLocaleString('es-MX');
        $('avp-kpi-pension').textContent = Number(d.pension_max || 0).toLocaleString('es-MX');
        $('avp-kpi-amigo').textContent = Number(d.amigo_efectivo || 0).toLocaleString('es-MX');
        $('avp-kpi-sin-cliente').textContent = Number(d.sin_cliente || 0).toLocaleString('es-MX');
    }

    async function loadCatalogos() {
        const [celulasRes, ubicacionesRes] = await Promise.all([
            fetch('/MotosAdjudicadas/inventarioCelulas', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/MotosAdjudicadas/inventarioUbicaciones', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null)
        ]);
        const celula = $('avp-celula');
        if (celula && celulasRes?.success) {
            celula.innerHTML = '<option value="">Todas</option>' + (celulasRes.datos || []).map((row) => '<option value="' + esc(row.id_celula) + '">' + esc(row.nombre) + '</option>').join('');
        }
        const ubicacion = $('avp-ubicacion');
        if (ubicacion && ubicacionesRes?.success) {
            ubicacion.innerHTML = '<option value="">Todas</option>' + (ubicacionesRes.datos || []).map((row) => '<option value="' + esc(row.id_ubicacion) + '">' + esc(row.nombre_ubicacion) + '</option>').join('');
        }
    }

    async function loadRows() {
        const body = $('avp-body');
        if (body) body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>';
        const res = await fetch('/MotosAdjudicadas/pisoVentaUnidades?' + params().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) body.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">' + esc(json.message || 'No se pudo cargar.') + '</td></tr>';
            return;
        }
        if (json.tablas_disponibles === false) {
            toast('Falta ejecutar la tabla av_piso_venta_envios.', 'warning');
        }
        state.total = Number(json.total || 0);
        state.page = Number(json.page || 1);
        state.limit = Number(json.limit || state.limit);
        state.pages = Number(json.pages || 1);
        renderRows(json.rows || []);
        $('avp-pager-info').textContent = rangeInfo(state.total, state.page, state.limit, 'unidades');
        renderPagination();
    }

    async function refresh(resetPage = false) {
        if (resetPage) state.page = 1;
        await Promise.all([loadResumen(), loadRows()]);
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadCatalogos();
        await refresh(true);
        $('avp-refresh')?.addEventListener('click', () => refresh(true));
        $('avp-limit')?.addEventListener('change', () => {
            state.limit = Number($('avp-limit')?.value || 8) || 8;
            refresh(true);
        });
        ['avp-q', 'avp-celula', 'avp-ubicacion', 'avp-cliente'].forEach((id) => {
            $(id)?.addEventListener(id === 'avp-q' ? 'input' : 'change', () => {
                clearTimeout(window.__avpTimer);
                window.__avpTimer = setTimeout(() => refresh(true), 260);
            });
        });
        $('avp-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const next = Number(link.dataset.page || 1);
            if (next && next !== state.page) {
                state.page = next;
                refresh(false);
            }
        });
    });
})();
</script>
