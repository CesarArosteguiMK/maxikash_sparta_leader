<style>
    .atlas-com-shell { color: #22303e; }
    .atlas-com-head { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; }
    .atlas-com-title { display:flex; align-items:center; gap:.65rem; font-weight:800; font-size:1.35rem; color:#22303e; }
    .atlas-com-title i { color:#26344e; }
    .atlas-com-sub { color:#6b7280; font-size:.9rem; margin-top:.15rem; }
    .btn-action-size { min-height:2.375rem; padding:.5rem .95rem; display:inline-flex; align-items:center; justify-content:center; gap:.35rem; font-size:.875rem; font-weight:600; }
    .atlas-com-card .tab-content:not(.doc-example-content) { padding:0 !important; }
    .atlas-com-card { border:1px solid #e2e8f0; border-radius:.875rem; background:#fff; box-shadow:none; }
    .atlas-com-tabs { border-bottom:1px solid #e2e8f0; gap:.35rem; flex-wrap:wrap; }
    .atlas-com-tabs .nav-link { border:0; border-bottom:3px solid transparent; color:#64748b; font-weight:800; padding:.75rem 1rem; }
    .atlas-com-tabs .nav-link.active { color:#173756; border-bottom-color:#d09f48; background:transparent; }
    .atlas-com-toolbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem; }
    .atlas-com-tools { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
    .atlas-com-search { max-width:18rem; }
    .atlas-com-dt-top { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .atlas-com-dt-top .dt-length label,
    .atlas-com-dt-top .dt-search label { display:flex; align-items:center; gap:.5rem; margin:0; }
    .atlas-com-dt-top .dt-length .form-select { width:auto; min-width:5.25rem; }
    .atlas-com-dt-top .dt-search .form-control { width:auto; }
    .atlas-com-actions-row { margin:1.5rem 1.5rem 2.75rem !important; }
    .atlas-com-table-wrap { overflow:auto; }
    #atlas-com-table-admin td { vertical-align:top; }
    .atlas-com-dt-footer { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; padding-top:1rem; }
    .atlas-com-dt-info { color:#a1a8b0; }
    .atlas-com-dt-pages { margin-left:auto; }
    .atlas-com-dt-pages .pagination { justify-content:flex-end; margin:0; }
    .atlas-com-main { font-weight:800; color:#22303e; }
    .atlas-com-muted { color:#7a838b; font-size:.78rem; font-weight:700; }
    .atlas-com-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.22rem .55rem; border-radius:999px; font-size:.72rem; font-weight:800; white-space:nowrap; }
    .atlas-com-badge-ok { background:#dcfce7; color:#15803d; }
    .atlas-com-badge-off { background:#fee2e2; color:#b91c1c; }
    .atlas-com-badge-draft { background:#fef3c7; color:#92400e; }
    .atlas-com-actions { display:flex; align-items:center; justify-content:center; gap:.35rem; }
    .atlas-com-row-drag { width:2.5rem; text-align:center; }
    .atlas-com-drag-handle { cursor:grab; color:#64748b; border:0; background:transparent; padding:.25rem .35rem; line-height:1; }
    .atlas-com-drag-handle:active { cursor:grabbing; }
    .atlas-com-error { background:#fef2f2 !important; color:#991b1b; }
    .atlas-com-tree { display:grid; gap:.6rem; }
    .atlas-com-tree-item { border:1px solid #e2e8f0; border-radius:.7rem; overflow:hidden; background:#fff; }
    .atlas-com-tree-h { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.72rem .85rem; background:#f8fafc; }
    .atlas-com-tree-body { padding:.65rem .9rem .85rem; display:grid; gap:.45rem; }
    .atlas-com-tree-sub { padding:.48rem .65rem; border-left:3px solid #d09f48; background:#fff7ed; border-radius:.45rem; }
    .atlas-com-tree-gest { margin:.32rem 0 0 1rem; color:#566a7f; font-weight:700; }
    .atlas-com-subpanel { background:#f8fafc; padding:.8rem .9rem; border-top:1px solid #e2e8f0; }
    .atlas-com-subpanel-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:.55rem; }
    .atlas-com-subpanel-list { display:grid; gap:.38rem; }
    .atlas-com-subitem { display:flex; align-items:center; justify-content:space-between; gap:.65rem; padding:.45rem .58rem; border:1px solid #e2e8f0; border-radius:.5rem; background:#fff; }
    .atlas-com-subitem-title { font-weight:800; color:#22303e; }
    .atlas-com-subitem-meta { color:#7a838b; font-size:.75rem; font-weight:700; }
    #modalAtlasComercial .modal-content { border:0; border-radius:1rem; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,.12); }
    #modalAtlasComercial .modal-header { background:#0f2747; border-bottom:1px solid rgba(255,255,255,.2); padding:1rem 1.25rem; }
    #modalAtlasComercial .modal-title { font-weight:700; color:#fff; line-height:1.25; }
    #modalAtlasComercial .modal-title i { color:#fff; }
    #modalAtlasComercial .atlas-com-modal-subtitle { color:rgba(255,255,255,.65); font-size:.82rem; font-weight:600; }
    #modalAtlasComercial .modal-body { padding:1.25rem; }
    #modalAtlasComercial .modal-footer { border-top:1px solid #e5e7eb; padding:1rem 1.25rem; gap:.75rem; }
    .atlas-com-required::after { content:" *"; color:#dc2626; }
    @media (max-width: 767.98px) {
        .atlas-com-head, .atlas-com-toolbar, .atlas-com-dt-top { align-items:stretch; flex-direction:column; }
        .atlas-com-actions-row .btn, .atlas-com-toolbar .form-control, .atlas-com-dt-top .dt-search .form-control { width:100%; max-width:none; }
        .atlas-com-dt-pages { margin-left:0; width:100%; }
        .atlas-com-dt-pages .pagination { justify-content:center; }
        .atlas-com-tabs { flex-wrap:nowrap; overflow-x:auto; }
        .atlas-com-tabs .nav-item { flex:0 0 auto; }
    }
</style>

<div class="container-fluid py-4 atlas-com-shell">
    <div class="atlas-com-head">
        <div>
            <div class="atlas-com-title"><i class="fa-solid fa-table-list"></i><span>Catálogos comerciales</span></div>
        </div>
    </div>
    <div class="card atlas-com-card">
        <div class="card-body">
            <ul class="nav atlas-com-tabs mb-0" id="atlas-com-tabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" type="button" data-atlas-com-tipo-tab="dictamen"><i class="fa-solid fa-list-check me-1"></i>Estatus</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" data-atlas-com-tipo-tab="tipo_gestion"><i class="fa-solid fa-briefcase me-1"></i>Tipos de gestión</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" data-atlas-com-tipo-tab="gestion"><i class="fa-solid fa-clipboard-list me-1"></i>Gestiones</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" data-atlas-com-flujo-tab="1"><i class="fa-solid fa-diagram-project me-1"></i>Flujo visual</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="atlas-com-tab-admin">
                    <div class="row justify-content-end atlas-com-actions-row">
                        <div class="col-12 d-flex align-items-end justify-content-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-com-nuevo-tipo="dictamen">
                                <i class="fa-solid fa-plus icon-sm me-sm-2"></i>
                                <span class="d-inline-block">Nuevo estatus</span>
                            </button>
                            <button type="button" class="btn btn-primary add-new btn-action-size d-none" data-atlas-com-nuevo-tipo="tipo_gestion">
                                <i class="fa-solid fa-plus icon-sm me-sm-2"></i>
                                <span class="d-inline-block">Nuevo tipo de gestión</span>
                            </button>
                            <button type="button" class="btn btn-primary add-new btn-action-size d-none" data-atlas-com-nuevo-tipo="gestion">
                                <i class="fa-solid fa-plus icon-sm me-sm-2"></i>
                                <span class="d-inline-block">Nueva gestión</span>
                            </button>
                            <button type="button" class="btn btn-label-secondary btn-action-size" id="atlas-com-btn-recargar">
                                <i class="fa-solid fa-rotate icon-sm me-sm-2"></i>
                                <span class="d-inline-block">Actualizar</span>
                            </button>
                        </div>
                    </div>
                    <div class="atlas-com-dt-top">
                        <div class="dt-length">
                            <label>
                                <span>Mostrar</span>
                            <select class="form-select form-select-sm" id="atlas-com-length">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                                <span>registros</span>
                            </label>
                        </div>
                        <div class="dt-search">
                            <label>
                                <span>Buscar:</span>
                                <input type="search" class="form-control form-control-sm" id="atlas-com-search" aria-label="Buscar en catálogo">
                            </label>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive atlas-com-table-wrap">
                        <table class="dt-responsive table border-top atlas-com-table" id="atlas-com-table-admin">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="atlas-com-dt-footer">
                        <div class="atlas-com-dt-info" id="atlas-com-table-info">Mostrando 0 a 0 de 0 registros</div>
                        <nav class="atlas-com-dt-pages" aria-label="Paginación de catálogos comerciales">
                            <ul class="pagination" id="atlas-com-pagination"></ul>
                        </nav>
                    </div>
                </div>

                <div class="tab-pane fade" id="atlas-com-tab-flujo">
                    <div class="atlas-com-toolbar">
                        <input type="search" class="form-control atlas-com-search" id="atlas-com-tree-search" placeholder="Filtrar flujo">
                        <span class="atlas-com-muted">Estructura publicada para app móvil y operación.</span>
                    </div>
                    <div class="atlas-com-tree" id="atlas-com-tree"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade atlas-com-modal" id="modalAtlasComercial" tabindex="-1" aria-labelledby="atlas-com-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="atlas-com-form">
                <div class="modal-header">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <h5 class="modal-title fw-bold mb-1 text-white" id="atlas-com-modal-title"><i class="fa-solid fa-pen-to-square me-2 text-white"></i>Catálogo</h5>
                            <p class="mb-0 small atlas-com-modal-subtitle">Catálogos comerciales de Atlas</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tipo" id="atlas-com-form-tipo">
                    <input type="hidden" name="id" id="atlas-com-form-id">
                    <div class="row g-3" id="atlas-com-form-fields"></div>
                </div>
                <div class="modal-footer d-flex justify-content-end align-items-center">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    let datos = { dictamenes: [], subestatus: [], tipos_gestion: [], gestiones: [] };
    let tipoActual = 'dictamen';
    let seleccionado = null;
    const estatusAbiertos = new Set();
    let adminPage = 1;
    let adminLength = 10;
    let filasAdminActuales = [];
    const modalEl = document.getElementById('modalAtlasComercial');
    let modal = null;
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    const norm = s => String(s || '').trim().toLowerCase();
    const byId = id => document.getElementById(id);
    const arr = tipo => tipo === 'dictamen' ? datos.dictamenes : (tipo === 'subestatus' ? datos.subestatus : (tipo === 'tipo_gestion' ? datos.tipos_gestion : datos.gestiones));

    function showBusy() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'Procesando su petición', html: 'Espere un momento...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
        }
    }
    function hideBusy() { if (typeof Swal !== 'undefined') Swal.close(); }
    function getModal() {
        if (!modalEl || !window.bootstrap) return null;
        if (!modal) {
            modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        return modal;
    }
    function showModal() {
        const modalInst = getModal();
        if (modalInst) {
            modalInst.show();
            return;
        }
        if (window.jQuery && jQuery.fn && jQuery.fn.modal && modalEl) {
            jQuery(modalEl).modal('show');
            return;
        }
        if (modalEl) {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }
    }
    function hideModal() {
        const modalInst = getModal();
        if (modalInst) {
            modalInst.hide();
            return;
        }
        if (window.jQuery && jQuery.fn && jQuery.fn.modal && modalEl) {
            jQuery(modalEl).modal('hide');
            return;
        }
        if (modalEl) {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }
    }
    async function json(url, body) {
        const res = await fetch(url, { method: 'POST', headers: {'Content-Type':'application/json', Accept:'application/json'}, credentials:'same-origin', body: JSON.stringify(body || {}) });
        const data = await res.json();
        if (!data || !data.success) throw new Error((data && (data.mensaje || data.error || (data.errores || []).join('\n'))) || 'Error');
        return data;
    }
    function aplicarCatalogosComerciales(data) {
        datos = Object.assign({ dictamenes: [], subestatus: [], tipos_gestion: [], gestiones: [] }, data.datos || {});
        renderAdmin();
        renderTree();
        syncNuevoButtons();
    }
    async function cargar(opts) {
        opts = opts || {};
        const httpClient = (typeof http !== 'undefined' && http && typeof http.request === 'function')
            ? http
            : (window.http && typeof window.http.request === 'function' ? window.http : null);
        if (httpClient) {
            return new Promise(resolve => {
                httpClient.request({
                    endpoint: '/Atlas/getCatalogosComerciales',
                    metodo: 'GET',
                    showLoader: opts.showLoader !== false,
                    onSuccess: data => {
                        try {
                            if (!data || data.success === false) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
                            aplicarCatalogosComerciales(data || {});
                        }
                        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo pintar', text:err.message || 'Error' }); }
                        resolve();
                    },
                    onError: err => {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo cargar', text:(err && err.message) || 'Error' });
                        resolve();
                    }
                });
            });
        }
        showBusy();
        try {
            const res = await fetch('/Atlas/getCatalogosComerciales', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
            aplicarCatalogosComerciales(data);
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo cargar', text:err.message || 'Error' });
        } finally { hideBusy(); }
    }
    function syncNuevoButtons() {
        document.querySelectorAll('[data-atlas-com-nuevo-tipo]').forEach(btn => {
            btn.classList.toggle('d-none', btn.getAttribute('data-atlas-com-nuevo-tipo') !== tipoActual);
        });
    }
    function badgeActivo(v) {
        return Number(v) === 1 ? '<span class="atlas-com-badge atlas-com-badge-ok"><i class="fa-solid fa-circle-check"></i>Activo</span>' : '<span class="atlas-com-badge atlas-com-badge-off"><i class="fa-solid fa-ban"></i>Inactivo</span>';
    }
    function badgeEstado(v) {
        const s = norm(v || 'publicado');
        if (s === 'borrador') return '<span class="atlas-com-badge atlas-com-badge-draft"><i class="fa-solid fa-pencil"></i>Borrador</span>';
        if (s === 'pendiente') return '<span class="atlas-com-badge atlas-com-badge-draft"><i class="fa-solid fa-clock"></i>Pendiente</span>';
        return '<span class="atlas-com-badge atlas-com-badge-ok"><i class="fa-solid fa-cloud-arrow-up"></i>Publicado</span>';
    }
    function columnas() {
        if (tipoActual === 'dictamen') return ['','Posición','Nombre','Objetivo','Fecha actualización','Acciones'];
        if (tipoActual === 'subestatus') return ['','Subestatus','Fecha actualización','Acciones'];
        if (tipoActual === 'tipo_gestion') return ['','Tipo de gestión','Fecha actualización','Acciones'];
        return ['','Estatus','Subestatus','Tipo gestión','Gestión','Ventana','Reglas','Estatus','Acciones'];
    }
    function rowAdmin(row) {
        const drag = '<td class="atlas-com-row-drag"><button type="button" class="atlas-com-drag-handle" draggable="true" data-drag-row="1" aria-label="Ordenar"><i class="fa-solid fa-grip-vertical"></i></button></td>';
        const actions = '<div class="atlas-com-actions"><button type="button" class="btn btn-sm btn-primary" data-edit="' + esc(row.id) + '" title="Editar"><i class="fa-solid fa-pen"></i></button></div>';
        const estado = '<div class="d-flex flex-column gap-1 align-items-start">' + badgeActivo(row.activo) + badgeEstado(row.estado_registro) + '</div>';
        if (tipoActual === 'dictamen') {
            const abierto = estatusAbiertos.has(String(row.id || ''));
            const actionsDictamen = '<div class="atlas-com-actions"><button type="button" class="btn btn-sm btn-label-secondary" data-toggle-subestatus="' + esc(row.id) + '" title="Ver subestatus" aria-label="Ver subestatus"><i class="fa-solid ' + (abierto ? 'fa-chevron-up' : 'fa-chevron-down') + '"></i></button><button type="button" class="btn btn-sm btn-primary" data-edit="' + esc(row.id) + '" title="Editar"><i class="fa-solid fa-pen"></i></button></div>';
            const main = '<tr data-id="' + esc(row.id) + '">' + drag + '<td><span class="badge bg-label-warning">' + esc(row.orden || '') + '</span></td><td><div class="fw-semibold">' + esc(row.nombre) + '</div></td><td>' + esc(row.objetivo || '') + '</td><td>' + esc(row.fecha_actualizacion_fmt || '') + '</td><td>' + actionsDictamen + '</td></tr>';
            return main + (abierto ? renderSubestatusEstatus(row) : '');
        }
        if (tipoActual === 'subestatus') {
            return '<tr data-id="' + esc(row.id) + '">' + drag + '<td><div class="fw-semibold">' + esc(row.nombre) + '</div></td><td>' + esc(row.fecha_actualizacion_fmt || '') + '</td><td>' + actions + '</td></tr>';
        }
        if (tipoActual === 'tipo_gestion') {
            return '<tr data-id="' + esc(row.id) + '">' + drag + '<td><div class="fw-semibold">' + esc(row.nombre || '') + '</div></td><td>' + esc(row.fecha_actualizacion_fmt || '') + '</td><td>' + actions + '</td></tr>';
        }
        const reglas = (Number(row.requiere_fecha) === 1 ? 'Fecha requerida' : 'Sin fecha') + ' · ' + (Number(row.permite_comentario) === 1 ? 'Comentario permitido' : 'Sin comentario');
        return '<tr data-id="' + esc(row.id) + '">' + drag + '<td>' + esc(row.dictamen_nombre || '') + '</td><td>' + esc(row.subestatus_nombre || '') + '</td><td>' + esc(row.tipo_gestion || '') + '</td><td><div class="fw-semibold">' + esc(row.nombre) + '</div></td><td>' + esc(row.ventana_complementaria || '') + '</td><td>' + esc(reglas) + '</td><td>' + estado + '</td><td>' + actions + '</td></tr>';
    }
    function renderSubestatusEstatus(row) {
        const subs = (datos.subestatus || []).filter(s => String(s.dictamen_id || '') === String(row.id || ''));
        const contenido = subs.length
            ? '<div class="atlas-com-subpanel-list">' + subs.map(s => '<div class="atlas-com-subitem"><div><div class="atlas-com-subitem-title">' + esc(s.nombre || '') + '</div><div class="atlas-com-subitem-meta">Posición ' + esc(s.orden || '') + (s.fecha_actualizacion_fmt ? ' · ' + esc(s.fecha_actualizacion_fmt) : '') + '</div></div><button type="button" class="btn btn-sm btn-primary" data-edit-subestatus="' + esc(s.id) + '" title="Editar subestatus" aria-label="Editar subestatus"><i class="fa-solid fa-pen"></i></button></div>').join('') + '</div>'
            : '<div class="text-muted fw-semibold">Este estatus todavía no tiene subestatus.</div>';
        return '<tr class="atlas-com-subpanel-row"><td colspan="6"><div class="atlas-com-subpanel"><div class="atlas-com-subpanel-head"><div class="atlas-com-muted">' + subs.length + ' subestatus registrados</div><button type="button" class="btn btn-sm btn-primary" data-add-subestatus="' + esc(row.id) + '"><i class="fa-solid fa-plus me-1"></i>Agregar subestatus</button></div>' + contenido + '</div></td></tr>';
    }
    function renderAdmin() {
        const table = byId('atlas-com-table-admin');
        if (!table) return;
        const q = norm(byId('atlas-com-search') ? byId('atlas-com-search').value : '');
        let rows = arr(tipoActual).filter(row => !q || JSON.stringify(row).toLowerCase().includes(q));
        filasAdminActuales = rows.slice();
        const total = filasAdminActuales.length;
        const pages = Math.max(1, Math.ceil(total / adminLength));
        adminPage = Math.max(1, Math.min(adminPage, pages));
        const start = total ? (adminPage - 1) * adminLength : 0;
        const end = Math.min(start + adminLength, total);
        const pageRows = filasAdminActuales.slice(start, end);
        const colspan = columnas().length;
        table.querySelector('thead').innerHTML = '<tr>' + columnas().map(c => '<th>' + esc(c) + '</th>').join('') + '</tr>';
        table.querySelector('tbody').innerHTML = pageRows.length ? pageRows.map(rowAdmin).join('') : '<tr><td colspan="' + colspan + '" class="text-center text-muted py-4">Sin registros.</td></tr>';
        initDrag(table.querySelector('tbody'));
        renderAdminFooter(total, start, end, pages);
    }
    function renderAdminFooter(total, start, end, pages) {
        const info = byId('atlas-com-table-info');
        const pag = byId('atlas-com-pagination');
        if (info) {
            info.textContent = total
                ? 'Mostrando ' + (start + 1) + ' a ' + end + ' de ' + total + ' registros'
                : 'Mostrando 0 a 0 de 0 registros';
        }
        if (!pag) return;
        const maxBtns = 5;
        let first = Math.max(1, adminPage - Math.floor(maxBtns / 2));
        let last = Math.min(pages, first + maxBtns - 1);
        first = Math.max(1, last - maxBtns + 1);
        const btn = (label, page, disabled, active, aria) => '<li class="page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '') + '"><button type="button" class="page-link" data-page="' + esc(page) + '"' + (disabled ? ' disabled' : '') + (aria ? ' aria-label="' + esc(aria) + '"' : '') + '>' + label + '</button></li>';
        let html = btn('&laquo;', 1, adminPage === 1, false, 'Primera pagina');
        html += btn('&lsaquo;', Math.max(1, adminPage - 1), adminPage === 1, false, 'Pagina anterior');
        for (let i = first; i <= last; i++) html += btn(String(i), i, false, i === adminPage, 'Pagina ' + i);
        html += btn('&rsaquo;', Math.min(pages, adminPage + 1), adminPage === pages, false, 'Pagina siguiente');
        html += btn('&raquo;', pages, adminPage === pages, false, 'Ultima pagina');
        pag.innerHTML = html;
    }
    function optionRows(rows, selected, placeholder) {
        return '<option value="">' + esc(placeholder) + '</option>' + rows.map(row => '<option value="' + esc(row.id) + '"' + (String(selected || '') === String(row.id) ? ' selected' : '') + '>' + esc(row.nombre) + '</option>').join('');
    }
    function abrirForm(tipo, row) {
        const r = row || {};
        byId('atlas-com-form-tipo').value = tipo;
        byId('atlas-com-form-id').value = r.id || '';
        byId('atlas-com-modal-title').innerHTML = '<i class="fa-solid fa-pen-to-square me-2 text-white"></i>' + (r.id ? 'Editar ' : 'Agregar ') + (tipo === 'dictamen' ? 'estatus' : (tipo === 'tipo_gestion' ? 'tipo de gestión' : tipo));
        let html = '';
        if (tipo === 'dictamen') {
            html = '<div class="col-md-4"><label class="form-label">Código</label><input class="form-control" name="codigo_estatus" value="' + esc(r.codigo_estatus || '') + '" placeholder="Código de estatus"></div><div class="col-md-4"><label class="form-label">Clave</label><input class="form-control" name="clave" value="' + esc(r.clave || '') + '" placeholder="Auto si se deja vacío"></div><div class="col-md-4"><label class="form-label atlas-com-required">Orden</label><input type="number" class="form-control" name="orden" value="' + esc(r.orden || '') + '" required></div><div class="col-12"><label class="form-label atlas-com-required">Nombre</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="Nombre del estatus"></div><div class="col-12"><label class="form-label">Objetivo</label><textarea class="form-control" name="objetivo" rows="3" placeholder="Objetivo comercial">' + esc(r.objetivo || '') + '</textarea></div>';
        } else if (tipo === 'subestatus') {
            html = '<div class="col-md-6"><label class="form-label atlas-com-required">Estatus</label><select class="form-select" name="dictamen_id" required>' + optionRows(datos.dictamenes, r.dictamen_id, 'Selecciona estatus') + '</select></div><div class="col-md-3"><label class="form-label">Clave</label><input class="form-control" name="clave" value="' + esc(r.clave || '') + '"></div><div class="col-md-3"><label class="form-label atlas-com-required">Orden</label><input type="number" class="form-control" name="orden" value="' + esc(r.orden || '') + '" required></div><div class="col-12"><label class="form-label atlas-com-required">Subestatus</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="Nombre del subestatus"></div>';
        } else if (tipo === 'tipo_gestion') {
            html = '<div class="col-md-4"><label class="form-label">Clave</label><input class="form-control" name="clave" value="' + esc(r.clave || '') + '" placeholder="Auto si se deja vacío"></div><div class="col-md-4"><label class="form-label atlas-com-required">Orden</label><input type="number" class="form-control" name="orden" value="' + esc(r.orden || '') + '" required></div><div class="col-md-4"><label class="form-label">Activo</label><select class="form-select" name="activo"><option value="1"' + (Number(r.activo ?? 1) === 1 ? ' selected' : '') + '>Activo</option><option value="0"' + (Number(r.activo ?? 1) === 0 ? ' selected' : '') + '>Inactivo</option></select></div><div class="col-12"><label class="form-label atlas-com-required">Tipo de gestión</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="Impulso comercial"></div>';
        } else {
            const subs = datos.subestatus.map(s => Object.assign({}, s, { nombre: (s.dictamen_nombre || '') + ' / ' + s.nombre }));
            html = '<div class="col-md-6"><label class="form-label atlas-com-required">Subestatus</label><select class="form-select" name="subestatus_id" required>' + optionRows(subs, r.subestatus_id, 'Selecciona subestatus') + '</select></div><div class="col-md-3"><label class="form-label">Clave</label><input class="form-control" name="clave" value="' + esc(r.clave || '') + '"></div><div class="col-md-3"><label class="form-label atlas-com-required">Orden</label><input type="number" class="form-control" name="orden" value="' + esc(r.orden || '') + '" required></div><div class="col-md-6"><label class="form-label">Tipo gestión</label><select class="form-select" name="tipo_gestion_id">' + optionRows(datos.tipos_gestion, r.tipo_gestion_id, 'Selecciona tipo de gestión') + '</select></div><div class="col-md-6"><label class="form-label atlas-com-required">Gestión desplegable</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="WhatsApp enviado"></div><div class="col-md-6"><label class="form-label">Ventana complementaria</label><input class="form-control" name="ventana_complementaria" value="' + esc(r.ventana_complementaria || '') + '"></div><div class="col-md-6"><label class="form-label">Campos adicionales</label><input class="form-control" name="campos_adicionales" value="' + esc(r.campos_adicionales || '') + '"></div><div class="col-md-6"><label class="form-label">Requiere fecha</label><select class="form-select" name="requiere_fecha"><option value="0">No</option><option value="1"' + (Number(r.requiere_fecha) === 1 ? ' selected' : '') + '>Sí</option></select></div><div class="col-md-6"><label class="form-label">Permite comentario</label><select class="form-select" name="permite_comentario"><option value="1">Sí</option><option value="0"' + (Number(r.permite_comentario) === 0 ? ' selected' : '') + '>No</option></select></div>';
        }
        if (tipo !== 'tipo_gestion') html += '<div class="col-md-6"><label class="form-label">Activo</label><select class="form-select" name="activo"><option value="1"' + (Number(r.activo ?? 1) === 1 ? ' selected' : '') + '>Activo</option><option value="0"' + (Number(r.activo ?? 1) === 0 ? ' selected' : '') + '>Inactivo</option></select></div>';
        html += '<div class="col-md-6"><label class="form-label">Estado</label><select class="form-select" name="estado_registro"><option value="publicado">Publicado</option><option value="pendiente"' + (norm(r.estado_registro) === 'pendiente' ? ' selected' : '') + '>Pendiente de publicar</option><option value="borrador"' + (norm(r.estado_registro) === 'borrador' ? ' selected' : '') + '>Borrador</option></select></div>';
        byId('atlas-com-form-fields').innerHTML = html;
        showModal();
    }
    function formJson(form) {
        const out = {};
        new FormData(form).forEach((v,k) => out[k] = v);
        return out;
    }
    function renderTree() {
        const q = norm(byId('atlas-com-tree-search') ? byId('atlas-com-tree-search').value : '');
        const cont = byId('atlas-com-tree');
        cont.innerHTML = datos.dictamenes.filter(d => !q || JSON.stringify(d).toLowerCase().includes(q) || datos.subestatus.some(s => String(s.dictamen_id) === String(d.id) && JSON.stringify(s).toLowerCase().includes(q)) || datos.gestiones.some(g => String(g.dictamen_id) === String(d.id) && JSON.stringify(g).toLowerCase().includes(q))).map(d => {
            const subs = datos.subestatus.filter(s => String(s.dictamen_id) === String(d.id));
            return '<div class="atlas-com-tree-item"><div class="atlas-com-tree-h"><div><div class="atlas-com-main">' + esc(d.orden || '') + '. ' + esc(d.nombre) + '</div><div class="atlas-com-muted">' + esc(d.objetivo || '') + '</div></div>' + badgeActivo(d.activo) + '</div><div class="atlas-com-tree-body">' + subs.map(s => '<div class="atlas-com-tree-sub"><div class="atlas-com-main">' + esc(s.nombre) + '</div>' + datos.gestiones.filter(g => String(g.subestatus_id) === String(s.id)).map(g => '<div class="atlas-com-tree-gest"><i class="fa-solid fa-minus me-1"></i>' + esc(g.nombre) + (g.tipo_gestion ? ' <span class="atlas-com-muted">(' + esc(g.tipo_gestion) + ')</span>' : '') + '</div>').join('') + '</div>').join('') + '</div></div>';
        }).join('') || '<div class="text-center text-muted py-4">Sin flujo configurado.</div>';
    }
    function initDrag(tbody) {
        let dragged = null;
        tbody.querySelectorAll('[data-drag-row]').forEach(handle => {
            const tr = handle.closest('tr');
            handle.addEventListener('dragstart', ev => {
                dragged = tr;
                if (ev.dataTransfer) {
                    ev.dataTransfer.effectAllowed = 'move';
                    ev.dataTransfer.setData('text/plain', tr ? (tr.getAttribute('data-id') || '') : '');
                }
            });
            handle.addEventListener('dragend', () => { guardarOrdenActual(); });
        });
        tbody.querySelectorAll('tr').forEach(tr => {
            tr.addEventListener('dragover', ev => { ev.preventDefault(); const target = ev.currentTarget; if (dragged && dragged !== target) target.parentNode.insertBefore(dragged, target.nextSibling); });
        });
    }
    function sincronizarOrdenPaginaDesdeDom(tbody) {
        const idsPagina = Array.from(tbody.querySelectorAll('tr[data-id]')).map(tr => String(tr.getAttribute('data-id') || ''));
        if (!idsPagina.length || !filasAdminActuales.length) return;
        const start = Math.max(0, (adminPage - 1) * adminLength);
        const pageMap = new Map(filasAdminActuales.map(row => [String(row.id || ''), row]));
        const ordenPagina = idsPagina.map(id => pageMap.get(id)).filter(Boolean);
        filasAdminActuales = filasAdminActuales.slice(0, start).concat(ordenPagina, filasAdminActuales.slice(start + ordenPagina.length));
    }
    async function guardarOrdenActual() {
        sincronizarOrdenPaginaDesdeDom(byId('atlas-com-table-admin').querySelector('tbody'));
        const ids = filasAdminActuales.map(row => row.id).filter(Boolean);
        if (!ids.length) return;
        try { await json('/Atlas/guardarOrdenCatalogosComerciales', { tipo: tipoActual, ids: ids }); await cargar(); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo guardar el orden', text:err.message || 'Error' }); }
    }
    function parseTextoImportado(text) {
        const lines = String(text || '').split(/\r?\n/).filter(l => l.trim() !== '');
        if (!lines.length) return [];
        const sep = lines[0].includes('\t') ? '\t' : ',';
        const headers = lines.shift().split(sep).map(h => norm(h).replace(/\s+/g, '_'));
        return lines.map(line => {
            const cols = line.split(sep);
            const row = {};
            headers.forEach((h,i) => row[h] = (cols[i] || '').trim());
            return { estatus: row.estatus || '', objetivo: row.objetivo || '', subestatus: row.sub_estatus || row.subestatus || '', tipo_gestion: row.tipo_de_gestion || row.tipo_gestion || '', gestion: row.lista_desplegable_de_gestion || row.lista_desplegable_gestion || row.gestion || '', ventana_complementaria: row.ventana_complementaria || '', campos_adicionales: row.campos_adicionales || '' };
        });
    }

    document.addEventListener('click', async function (ev) {
        const toggleSub = ev.target.closest('[data-toggle-subestatus]');
        if (toggleSub) {
            ev.preventDefault();
            const id = String(toggleSub.getAttribute('data-toggle-subestatus') || '');
            if (estatusAbiertos.has(id)) estatusAbiertos.delete(id);
            else estatusAbiertos.add(id);
            renderAdmin();
            return;
        }
        const addSub = ev.target.closest('[data-add-subestatus]');
        if (addSub) {
            ev.preventDefault();
            const dictamenId = String(addSub.getAttribute('data-add-subestatus') || '');
            const hermanos = (datos.subestatus || []).filter(s => String(s.dictamen_id || '') === dictamenId);
            const maxOrden = hermanos.reduce((max, row) => Math.max(max, parseInt(row.orden, 10) || 0), 0);
            abrirForm('subestatus', { dictamen_id: dictamenId, orden: maxOrden + 1, activo: 1, estado_registro: 'publicado' });
            return;
        }
        const editSub = ev.target.closest('[data-edit-subestatus]');
        if (editSub) {
            ev.preventDefault();
            abrirForm('subestatus', (datos.subestatus || []).find(r => String(r.id || '') === String(editSub.getAttribute('data-edit-subestatus') || '')) || null);
            return;
        }
        const tab = ev.target.closest('[data-atlas-com-tipo-tab]');
        if (tab) {
            tipoActual = tab.getAttribute('data-atlas-com-tipo-tab');
            adminPage = 1;
            document.querySelectorAll('#atlas-com-tabs .nav-link').forEach(b => b.classList.toggle('active', b === tab));
            byId('atlas-com-tab-admin')?.classList.add('show', 'active');
            byId('atlas-com-tab-flujo')?.classList.remove('show', 'active');
            syncNuevoButtons();
            renderAdmin();
            return;
        }
        const flujoTab = ev.target.closest('[data-atlas-com-flujo-tab]');
        if (flujoTab) {
            document.querySelectorAll('#atlas-com-tabs .nav-link').forEach(b => b.classList.toggle('active', b === flujoTab));
            byId('atlas-com-tab-admin')?.classList.remove('show', 'active');
            byId('atlas-com-tab-flujo')?.classList.add('show', 'active');
            document.querySelectorAll('[data-atlas-com-nuevo-tipo]').forEach(btn => btn.classList.add('d-none'));
            renderTree();
            return;
        }
        const edit = ev.target.closest('[data-edit]');
        if (edit) { abrirForm(tipoActual, arr(tipoActual).find(r => String(r.id) === String(edit.getAttribute('data-edit'))) || null); return; }
        const pageBtn = ev.target.closest('[data-page]');
        if (pageBtn) {
            ev.preventDefault();
            adminPage = Math.max(1, parseInt(pageBtn.getAttribute('data-page'), 10) || 1);
            renderAdmin();
            return;
        }
    });
    document.querySelectorAll('[data-atlas-com-nuevo-tipo]').forEach(btn => {
        btn.addEventListener('click', function (ev) {
            ev.preventDefault();
            abrirForm(btn.getAttribute('data-atlas-com-nuevo-tipo') || tipoActual, null);
        });
    });
    byId('atlas-com-btn-recargar').addEventListener('click', cargar);
    byId('atlas-com-search').addEventListener('input', () => { adminPage = 1; renderAdmin(); });
    byId('atlas-com-length').addEventListener('change', ev => { adminLength = Math.max(1, parseInt(ev.target.value, 10) || 10); adminPage = 1; renderAdmin(); });
    byId('atlas-com-tree-search').addEventListener('input', renderTree);
    byId('atlas-com-form').addEventListener('submit', async function (ev) {
        ev.preventDefault();
        showBusy();
        try { await json('/Atlas/guardarCatalogoComercial', formJson(ev.currentTarget)); hideModal(); await cargar({ showLoader: false }); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo guardar', text:err.message || 'Error' }); }
        finally { hideBusy(); }
    });
    cargar();
})();
</script>

