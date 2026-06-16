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
    .atlas-com-flow-search-row { margin-top:1rem; margin-bottom:1rem; }
    .atlas-com-flow-search-row .dt-search { display:flex; justify-content:flex-end; }
    .atlas-com-flow-search-row .dt-search label { display:flex; align-items:center; gap:.5rem; margin:0; }
    .atlas-com-flow-search-row .dt-search .form-control { width:auto; }
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
    .atlas-com-icon-text { display:inline-flex; align-items:center; gap:.45rem; font-weight:800; color:#22303e; }
    .atlas-com-icon-text i { width:1.1rem; color:#26344e; text-align:center; }
    .atlas-com-flow-group { display:flex; flex-direction:column; gap:.08rem; }
    .atlas-com-flow-group-label { color:#7a838b; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.03em; }
    .atlas-com-flow-group-name { color:#22303e; font-size:.88rem; font-weight:800; line-height:1.2; }
    .atlas-com-icon-gallery { display:grid; grid-template-columns:repeat(8, minmax(0, 1fr)); gap:.45rem; }
    .atlas-com-icon-option { width:2.35rem; height:2.35rem; border:1px solid #dbe4ef; border-radius:.6rem; background:#fff; color:#26344e; display:inline-flex; align-items:center; justify-content:center; }
    .atlas-com-icon-option:hover,
    .atlas-com-icon-option.is-active { border-color:#26344e; background:#edf2f8; box-shadow:0 0 0 .12rem rgba(38, 52, 78, .12); }
    .atlas-com-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.22rem .55rem; border-radius:999px; font-size:.72rem; font-weight:800; white-space:nowrap; }
    .atlas-com-badge-ok { background:#dcfce7; color:#15803d; }
    .atlas-com-badge-off { background:#fee2e2; color:#b91c1c; }
    .atlas-com-badge-draft { background:#fef3c7; color:#92400e; }
    .atlas-com-actions { display:flex; align-items:center; justify-content:center; gap:.35rem; }
    .atlas-com-row-drag { width:2.5rem; text-align:center; }
    .atlas-com-drag-handle { cursor:grab; color:#64748b; border:0; background:transparent; padding:.25rem .35rem; line-height:1; }
    .atlas-com-drag-handle:active { cursor:grabbing; }
    .atlas-com-error { background:#fef2f2 !important; color:#991b1b; }
    .atlas-com-tree { display:grid; gap:.75rem; }
    .atlas-com-tree-item { border:1px solid #dbe4ef; border-radius:.7rem; overflow:hidden; background:#fff; box-shadow:0 .08rem .25rem rgba(34,48,62,.04); }
    .atlas-com-tree-item summary { list-style:none; cursor:pointer; }
    .atlas-com-tree-item summary::-webkit-details-marker { display:none; }
    .atlas-com-tree-h { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.72rem .85rem; background:#f8fafc; border-left:4px solid #26344e; }
    .atlas-com-tree-title { display:flex; align-items:center; gap:.55rem; min-width:0; }
    .atlas-com-tree-toggle { color:#64748b; transition:transform .15s ease; }
    .atlas-com-tree-item[open] .atlas-com-tree-toggle { transform:rotate(90deg); }
    .atlas-com-tree-meta { display:flex; flex-direction:column; gap:.12rem; margin-top:.12rem; }
    .atlas-com-tree-objective { color:#566a7f; font-size:.78rem; font-weight:800; line-height:1.25; }
    .atlas-com-tree-body { padding:.75rem .9rem .9rem; display:grid; gap:.72rem; background:#fbfcfe; }
    .atlas-com-tree-sub { position:relative; margin-left:1.1rem; padding:.62rem .7rem .72rem 1rem; border:1px solid #fed7aa; border-left:4px solid #d09f48; background:#fffaf2; border-radius:.5rem; }
    .atlas-com-tree-sub::before { content:""; position:absolute; left:-1.05rem; top:1.05rem; width:1.05rem; border-top:2px solid #d09f48; }
    .atlas-com-tree-sub::after { content:""; position:absolute; left:-1.05rem; top:-.72rem; bottom:50%; border-left:2px solid #d09f48; }
    .atlas-com-tree-sub-head,
    .atlas-com-tree-type,
    .atlas-com-tree-gest { display:flex; align-items:center; justify-content:space-between; gap:.55rem; }
    .atlas-com-tree-sub-head { margin-bottom:.48rem; padding-bottom:.42rem; border-bottom:1px solid #ffedd5; }
    .atlas-com-tree-node-label { display:inline-flex; align-items:center; gap:.28rem; margin-right:.45rem; padding:.12rem .45rem; border-radius:999px; font-size:.62rem; font-weight:900; text-transform:uppercase; letter-spacing:.035em; white-space:nowrap; }
    .atlas-com-tree-node-label--sub { background:#ffedd5; color:#9a3412; }
    .atlas-com-tree-node-label--type { background:#e0f2fe; color:#075985; }
    .atlas-com-tree-node-label--gest { background:#dcfce7; color:#166534; }
    .atlas-com-tree-branch { position:relative; margin-left:1rem; padding-left:1.25rem; border-left:2px solid #93c5fd; display:grid; gap:.34rem; }
    .atlas-com-tree-type-wrap { position:relative; display:grid; gap:.3rem; }
    .atlas-com-tree-type-wrap::before { content:""; position:absolute; left:-1.25rem; top:1rem; width:1.1rem; border-top:2px solid #93c5fd; }
    .atlas-com-tree-type { margin:.16rem 0 .12rem; padding:.42rem .55rem; border:1px solid #bae6fd; border-left:4px solid #0ea5e9; border-radius:.45rem; background:#f0f9ff; color:#22303e; font-weight:800; }
    .atlas-com-tree-type-main,
    .atlas-com-tree-gest-main { min-width:0; display:flex; align-items:center; gap:.38rem; }
    .atlas-com-tree-type-main span,
    .atlas-com-tree-gest-main span { overflow:hidden; text-overflow:ellipsis; }
    .atlas-com-tree-gest { position:relative; margin:.1rem 0 0 1.25rem; padding:.32rem .45rem; border:1px solid #dcfce7; border-left:4px solid #22c55e; border-radius:.42rem; background:#f0fdf4; color:#22303e; font-weight:700; }
    .atlas-com-tree-gest::before { content:""; position:absolute; left:-1.25rem; top:50%; width:1.1rem; border-top:2px solid #86efac; }
    .atlas-com-tree-add { width:100%; border:1px dashed #cbd5e1; background:#fff; border-radius:.45rem; padding:.38rem .55rem; color:#26344e; font-weight:800; text-align:left; display:flex; align-items:center; gap:.45rem; }
    .atlas-com-tree-add:hover { border-color:#26344e; background:#f8fafc; }
    .atlas-com-tree-add-gest { position:relative; margin:.1rem 0 0 1.25rem; width:calc(100% - 1.25rem); border-color:#86efac; border-left:4px solid #22c55e; background:#f0fdf4; color:#166534; }
    .atlas-com-tree-add-gest::before { content:""; position:absolute; left:-1.25rem; top:50%; width:1.1rem; border-top:2px dashed #86efac; }
    .atlas-com-tree-add-gest:hover { border-color:#22c55e; background:#dcfce7; color:#14532d; }
    .atlas-com-tree-add-sub { margin-left:1.1rem; border-color:#fed7aa; background:#fffaf2; color:#9a3412; }
    .atlas-com-tree-add-sub:hover { border-color:#d09f48; background:#fff7ed; }
    .atlas-com-flow-context { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.55rem; padding:.62rem .75rem; border:1px solid #dbe4ef; border-left:4px solid #22c55e; border-radius:.55rem; background:#f8fafc; }
    .atlas-com-flow-context-item { min-width:0; }
    .atlas-com-flow-context-label { display:block; color:#64748b; font-size:.62rem; font-weight:900; text-transform:uppercase; letter-spacing:.035em; line-height:1.1; }
    .atlas-com-flow-context-value { display:block; color:#22303e; font-size:.8rem; font-weight:800; line-height:1.22; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .atlas-com-flow-actions { display:inline-flex; align-items:center; gap:.18rem; flex:0 0 auto; }
    .atlas-com-flow-action { border:0; background:transparent; width:1.55rem; height:1.55rem; display:inline-flex; align-items:center; justify-content:center; color:#64748b; padding:0; border-radius:999px; }
    .atlas-com-flow-action:hover { color:#26344e; background:#eef2f7; }
    .atlas-com-flow-action--danger:hover { color:#b91c1c; background:#fee2e2; }
    .atlas-com-subpanel { background:#f8fafc; padding:.8rem .9rem; border-top:1px solid #e2e8f0; }
    .atlas-com-subpanel-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:.55rem; }
    .atlas-com-subpanel-list { display:grid; gap:.38rem; }
    .atlas-com-subitem { display:flex; align-items:center; justify-content:space-between; gap:.65rem; padding:.45rem .58rem; border:1px solid #e2e8f0; border-radius:.5rem; background:#fff; }
    .atlas-com-subitem-title { font-weight:800; color:#22303e; }
    .atlas-com-subitem-meta { color:#7a838b; font-size:.75rem; font-weight:700; }
    #modalAtlasComercial .modal-title { display:flex; align-items:center; gap:.55rem; font-weight:800; line-height:1.25; }
    #modalAtlasComercial .modal-title i { font-size:1rem; }
    #modalAtlasComCambiarTipo .modal-title { display:flex; align-items:center; gap:.55rem; font-weight:800; line-height:1.25; }
    #modalAtlasComCambiarTipo .modal-title i { font-size:1rem; }
    #modalAtlasComercial .modal-footer,
    #modalAtlasComCambiarTipo .modal-footer { justify-content:flex-end; gap:.75rem; }
    #modalAtlasComercial .modal-footer .btn,
    #modalAtlasComCambiarTipo .modal-footer .btn { min-width:8.5rem; display:inline-flex; align-items:center; justify-content:center; gap:.35rem; font-weight:700; }
    .atlas-com-required::after { content:" *"; color:#dc2626; }
    @media (max-width: 767.98px) {
        .atlas-com-head, .atlas-com-toolbar, .atlas-com-dt-top { align-items:stretch; flex-direction:column; }
        .atlas-com-actions-row .btn, .atlas-com-toolbar .form-control, .atlas-com-dt-top .dt-search .form-control { width:100%; max-width:none; }
        .atlas-com-dt-pages { margin-left:0; width:100%; }
        .atlas-com-dt-pages .pagination { justify-content:center; }
        .atlas-com-tabs { flex-wrap:nowrap; overflow-x:auto; }
        .atlas-com-tabs .nav-item { flex:0 0 auto; }
        #modalAtlasComercial .modal-footer,
        #modalAtlasComCambiarTipo .modal-footer { flex-direction:column; align-items:stretch; }
        #modalAtlasComercial .modal-footer .btn,
        #modalAtlasComCambiarTipo .modal-footer .btn { width:100%; }
    }
</style>

<div class="container-fluid py-4 atlas-com-shell">
    <div class="atlas-com-head">
        <div>
            <div class="atlas-com-title"><i class="fa-solid fa-table-list"></i><span>Catálogos Comerciales</span></div>
        </div>
    </div>
    <div class="card atlas-com-card">
        <div class="card-body">
            <ul class="nav atlas-com-tabs mb-0" id="atlas-com-tabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" type="button" data-atlas-com-tipo-tab="dictamen"><i class="fa-solid fa-list-check me-1"></i>Estatus</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" data-atlas-com-tipo-tab="tipo_gestion"><i class="fa-solid fa-briefcase me-1"></i>Tipos de gestión</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" data-atlas-com-flujo-tab="1"><i class="fa-solid fa-diagram-project me-1"></i>Flujo visual</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="atlas-com-tab-admin">
                    <div class="row justify-content-end atlas-com-actions-row">
                        <div class="col-12 d-flex align-items-end justify-content-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-primary add-new btn-action-size d-none" data-atlas-com-nuevo-tipo="tipo_gestion">
                                <i class="fa-solid fa-plus icon-sm me-sm-2"></i>
                                <span class="d-inline-block">Nuevo tipo de gestión</span>
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
                        <nav class="atlas-com-dt-pages" aria-label="Paginación de Catálogos Comerciales">
                            <ul class="pagination" id="atlas-com-pagination"></ul>
                        </nav>
                    </div>
                </div>

                <div class="tab-pane fade" id="atlas-com-tab-flujo">
                    <div class="row align-items-center atlas-com-flow-search-row">
                        <div class="col-sm-12 col-md-6"></div>
                        <div class="col-sm-12 col-md-6">
                            <div class="dt-search">
                                <label>
                                    <span>Buscar:</span>
                                    <input type="search" class="form-control form-control-sm" id="atlas-com-tree-search" aria-label="Buscar en flujo visual">
                                </label>
                            </div>
                        </div>
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
                    <h5 class="modal-title fw-bold" id="atlas-com-modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Catálogo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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

<div class="modal fade atlas-com-modal" id="modalAtlasComCambiarTipo" tabindex="-1" aria-labelledby="atlas-com-cambiar-tipo-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="atlas-com-cambiar-tipo-form">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="atlas-com-cambiar-tipo-title"><i class="fa-solid fa-right-left me-2"></i>Cambiar tipo de gestión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="subestatus_id" id="atlas-com-cambiar-subestatus-id">
                    <input type="hidden" name="tipo_actual_id" id="atlas-com-cambiar-tipo-actual-id">
                    <div class="mb-3">
                        <label class="form-label atlas-com-required">Nuevo tipo de gestión</label>
                        <select class="form-select" id="atlas-com-cambiar-tipo-nuevo" required></select>
                    </div>
                    <div class="atlas-com-muted" id="atlas-com-cambiar-tipo-ayuda"></div>
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
    const modalCambiarTipoEl = document.getElementById('modalAtlasComCambiarTipo');
    const formCambiarTipo = document.getElementById('atlas-com-cambiar-tipo-form');
    const atlasComTabStorageKey = 'atlas_catalogos_comerciales_tab_activa';
    let modal = null;
    let modalCambiarTipo = null;
    let contextoCambiarTipo = null;
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    const norm = s => String(s || '').trim().toLowerCase();
    const byId = id => document.getElementById(id);
    const arr = tipo => tipo === 'dictamen' ? datos.dictamenes : (tipo === 'subestatus' ? datos.subestatus : (tipo === 'tipo_gestion' ? datos.tipos_gestion : datos.gestiones));
    const iconosTipoGestion = [
        'fa-solid fa-briefcase',
        'fa-solid fa-phone',
        'fa-solid fa-message',
        'fa-solid fa-route',
        'fa-solid fa-handshake',
        'fa-solid fa-sack-dollar',
        'fa-solid fa-calendar-check',
        'fa-solid fa-file-lines',
        'fa-solid fa-clipboard-check',
        'fa-solid fa-user-check',
        'fa-solid fa-bullhorn',
        'fa-solid fa-chart-line',
        'fa-solid fa-location-dot',
        'fa-solid fa-clock',
        'fa-solid fa-circle-question',
        'fa-solid fa-triangle-exclamation'
    ];

    function showBusy() {
        if (typeof showWait === 'function') {
            showWait('Espere un momento...');
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Procesando su petición',
                text: 'Espere un momento...',
                imageUrl: '/assets/img/wait.svg',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
        }
    }
    function hideBusy() { if (typeof Swal !== 'undefined') Swal.close(); }
    function hideBusyAfterRender() {
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                hideBusy();
            });
        });
    }
    function getModal() {
        if (!modalEl || !window.bootstrap) return null;
        if (!modal) {
            modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        return modal;
    }
    function getModalCambiarTipo() {
        if (!modalCambiarTipoEl || !window.bootstrap) return null;
        if (!modalCambiarTipo) {
            modalCambiarTipo = bootstrap.Modal.getOrCreateInstance(modalCambiarTipoEl);
        }
        return modalCambiarTipo;
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
    function showModalCambiarTipo() {
        const modalInst = getModalCambiarTipo();
        if (modalInst) {
            modalInst.show();
            return;
        }
        if (modalCambiarTipoEl) {
            modalCambiarTipoEl.classList.add('show');
            modalCambiarTipoEl.style.display = 'block';
            modalCambiarTipoEl.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }
    }
    function hideModalCambiarTipo() {
        const modalInst = getModalCambiarTipo();
        if (modalInst) {
            modalInst.hide();
            return;
        }
        if (modalCambiarTipoEl) {
            modalCambiarTipoEl.classList.remove('show');
            modalCambiarTipoEl.style.display = 'none';
            modalCambiarTipoEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }
    }
    async function json(url, body) {
        const res = await fetch(url, { method: 'POST', headers: {'Content-Type':'application/json', Accept:'application/json'}, credentials:'same-origin', body: JSON.stringify(body || {}) });
        const data = await res.json();
        if (!data || !data.success) throw new Error((data && (data.mensaje || data.error || (data.errores || []).join('\n'))) || 'Error');
        return data;
    }
    function guardarAtlasComTab(valor) {
        try {
            if (valor) window.localStorage.setItem(atlasComTabStorageKey, valor);
            else window.localStorage.removeItem(atlasComTabStorageKey);
        } catch (err) {}
    }
    function obtenerAtlasComTab() {
        try { return window.localStorage.getItem(atlasComTabStorageKey) || ''; } catch (err) { return ''; }
    }
    function mostrarAtlasComAdmin(tipo, opciones) {
        const tipoValido = tipo === 'tipo_gestion' ? 'tipo_gestion' : 'dictamen';
        tipoActual = tipoValido;
        if (!(opciones && opciones.conservarPagina)) adminPage = 1;
        const tab = document.querySelector('[data-atlas-com-tipo-tab="' + tipoValido + '"]');
        document.querySelectorAll('#atlas-com-tabs .nav-link').forEach(b => b.classList.toggle('active', b === tab));
        byId('atlas-com-tab-admin')?.classList.add('show', 'active');
        byId('atlas-com-tab-flujo')?.classList.remove('show', 'active');
        syncNuevoButtons();
        renderAdmin();
    }
    function mostrarAtlasComFlujo() {
        const flujoTab = document.querySelector('[data-atlas-com-flujo-tab]');
        document.querySelectorAll('#atlas-com-tabs .nav-link').forEach(b => b.classList.toggle('active', b === flujoTab));
        byId('atlas-com-tab-admin')?.classList.remove('show', 'active');
        byId('atlas-com-tab-flujo')?.classList.add('show', 'active');
        document.querySelectorAll('[data-atlas-com-nuevo-tipo]').forEach(btn => btn.classList.add('d-none'));
        renderTree();
    }
    function restaurarAtlasComTab() {
        const guardada = obtenerAtlasComTab();
        if (guardada === 'flujo') {
            mostrarAtlasComFlujo();
            return;
        }
        mostrarAtlasComAdmin(guardada === 'tipo_gestion' ? 'tipo_gestion' : 'dictamen', { conservarPagina: true });
    }
    function aplicarCatalogosComerciales(data) {
        datos = Object.assign({ dictamenes: [], subestatus: [], tipos_gestion: [], gestiones: [] }, data.datos || {});
        restaurarAtlasComTab();
    }
    async function cargar(opts) {
        opts = (opts && !opts.type) ? opts : {};
        const mostrarLoader = opts.showLoader !== false;
        const httpClient = (typeof http !== 'undefined' && http && typeof http.request === 'function')
            ? http
            : (window.http && typeof window.http.request === 'function' ? window.http : null);
        if (httpClient) {
            if (mostrarLoader) showBusy();
            return new Promise(resolve => {
                httpClient.request({
                    endpoint: '/Atlas/getCatalogosComerciales',
                    metodo: 'GET',
                    showLoader: false,
                    onSuccess: data => {
                        try {
                            if (!data || data.success === false) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
                            aplicarCatalogosComerciales(data || {});
                            if (mostrarLoader) hideBusyAfterRender();
                        }
                        catch (err) {
                            if (mostrarLoader) hideBusy();
                            if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo pintar', text:err.message || 'Error' });
                        }
                        resolve();
                    },
                    onError: err => {
                        if (mostrarLoader) hideBusy();
                        if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo cargar', text:(err && err.message) || 'Error' });
                        resolve();
                    }
                });
            });
        }
        if (mostrarLoader) showBusy();
        try {
            const res = await fetch('/Atlas/getCatalogosComerciales', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
            aplicarCatalogosComerciales(data);
            if (mostrarLoader) hideBusyAfterRender();
        } catch (err) {
            if (mostrarLoader) hideBusy();
            if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo cargar', text:err.message || 'Error' });
        }
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
    function renderIconosTipoGestion(iconoActual) {
        const actual = String(iconoActual || 'fa-solid fa-briefcase').trim();
        return '<div class="atlas-com-icon-gallery" role="group" aria-label="Iconos de tipo de gestión">' + iconosTipoGestion.map(icon => (
            '<button type="button" class="atlas-com-icon-option' + (icon === actual ? ' is-active' : '') + '" data-atlas-com-icon="' + esc(icon) + '" title="' + esc(icon) + '" aria-label="' + esc(icon) + '"><i class="' + esc(icon) + '"></i></button>'
        )).join('') + '</div>';
    }
    function columnas() {
        if (tipoActual === 'dictamen') return ['Posición','Nombre','Objetivo','Fecha actualización','Subestatus'];
        if (tipoActual === 'subestatus') return ['','Subestatus','Fecha actualización','Acciones'];
        if (tipoActual === 'tipo_gestion') return ['','Tipo de gestión','Fecha actualización','Acciones'];
        return ['','Grupo por estatus','Subestatus','Tipo gestión','Gestión','Ventana','Reglas','Acciones'];
    }
    function rowAdmin(row) {
        const drag = '<td class="atlas-com-row-drag"><button type="button" class="atlas-com-drag-handle" draggable="true" data-drag-row="1" aria-label="Ordenar"><i class="fa-solid fa-grip-vertical"></i></button></td>';
        const actions = '<div class="atlas-com-actions"><button type="button" class="btn btn-sm btn-primary" data-edit="' + esc(row.id) + '" title="Editar"><i class="fa-solid fa-pen"></i></button></div>';
        if (tipoActual === 'dictamen') {
            const abierto = estatusAbiertos.has(String(row.id || ''));
            const actionsDictamen = '<div class="atlas-com-actions"><button type="button" class="btn btn-sm btn-label-secondary" data-toggle-subestatus="' + esc(row.id) + '" title="Ver subestatus" aria-label="Ver subestatus"><i class="fa-solid ' + (abierto ? 'fa-chevron-up' : 'fa-chevron-down') + '"></i></button></div>';
            const main = '<tr data-id="' + esc(row.id) + '"><td><span class="badge bg-label-warning">' + esc(row.orden || '') + '</span></td><td><div class="fw-semibold">' + esc(row.nombre) + '</div></td><td>' + esc(row.objetivo || '') + '</td><td>' + esc(row.fecha_actualizacion_fmt || '') + '</td><td>' + actionsDictamen + '</td></tr>';
            return main + (abierto ? renderSubestatusEstatus(row) : '');
        }
        if (tipoActual === 'subestatus') {
            return '<tr data-id="' + esc(row.id) + '">' + drag + '<td><div class="fw-semibold">' + esc(row.nombre) + '</div></td><td>' + esc(row.fecha_actualizacion_fmt || '') + '</td><td>' + actions + '</td></tr>';
        }
        if (tipoActual === 'tipo_gestion') {
            return '<tr data-id="' + esc(row.id) + '">' + drag + '<td><div class="atlas-com-icon-text"><i class="' + esc(row.icon_font || 'fa-solid fa-briefcase') + '"></i><span>' + esc(row.nombre || '') + '</span></div></td><td>' + esc(row.fecha_actualizacion_fmt || '') + '</td><td>' + actions + '</td></tr>';
        }
        const reglas = (Number(row.requiere_fecha) === 1 ? 'Fecha requerida' : 'Sin fecha') + ' · ' + (Number(row.permite_comentario) === 1 ? 'Comentario permitido' : 'Sin comentario');
        const grupoEstatus = '<div class="atlas-com-flow-group"><span class="atlas-com-flow-group-label">Grupo</span><span class="atlas-com-flow-group-name">' + esc(row.dictamen_nombre || 'Sin estatus') + '</span></div>';
        return '<tr data-id="' + esc(row.id) + '">' + drag + '<td>' + grupoEstatus + '</td><td>' + esc(row.subestatus_nombre || '') + '</td><td>' + esc(row.tipo_gestion || '') + '</td><td><div class="fw-semibold">' + esc(row.nombre) + '</div></td><td>' + esc(row.ventana_complementaria || '') + '</td><td>' + esc(reglas) + '</td><td>' + actions + '</td></tr>';
    }
    function renderSubestatusEstatus(row) {
        const subs = (datos.subestatus || []).filter(s => String(s.dictamen_id || '') === String(row.id || ''));
        const contenido = subs.length
            ? '<div class="atlas-com-subpanel-list">' + subs.map(s => '<div class="atlas-com-subitem"><div><div class="atlas-com-subitem-title">' + esc(s.nombre || '') + '</div><div class="atlas-com-subitem-meta">Posición ' + esc(s.orden || '') + (s.fecha_actualizacion_fmt ? ' · ' + esc(s.fecha_actualizacion_fmt) : '') + '</div></div></div>').join('') + '</div>'
            : '<div class="text-muted fw-semibold">Este estatus todavía no tiene subestatus.</div>';
        return '<tr class="atlas-com-subpanel-row"><td colspan="5"><div class="atlas-com-subpanel"><div class="atlas-com-subpanel-head"><div class="atlas-com-muted">' + subs.length + ' subestatus registrados</div></div>' + contenido + '</div></td></tr>';
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
        if (tipoActual !== 'dictamen') initDrag(table.querySelector('tbody'));
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
    function contextoModalCatalogo(tipo, row) {
        const r = row || {};
        const editando = !!r.id;
        const sub = r.subestatus_id ? findCatalogo('subestatus', r.subestatus_id) : null;
        const estatus = sub && sub.dictamen_id ? findCatalogo('dictamen', sub.dictamen_id) : (r.dictamen_id ? findCatalogo('dictamen', r.dictamen_id) : null);
        const tipoGestion = r.tipo_gestion_id ? findCatalogo('tipo_gestion', r.tipo_gestion_id) : null;
        if (tipo === 'dictamen') {
            return {
                icono: 'fa-solid fa-list-check',
                titulo: (editando ? 'Editar ' : 'Agregar ') + 'Estatus',
                subtitulo: 'Estatus principal del flujo comercial.'
            };
        }
        if (tipo === 'subestatus') {
            return {
                icono: 'fa-solid fa-layer-group',
                titulo: (editando ? 'Editar ' : 'Agregar ') + 'Subestatus',
                subtitulo: estatus ? ('Pertenece a: ' + estatus.nombre) : 'Subestatus asociado a un estatus comercial.'
            };
        }
        if (tipo === 'tipo_gestion') {
            return {
                icono: 'fa-solid fa-briefcase',
                titulo: (editando ? 'Editar ' : 'Agregar ') + 'Tipo de Gestión',
                subtitulo: 'Agrupa las gestiones que aparecen dentro del flujo visual.'
            };
        }
        return {
            icono: 'fa-solid fa-clipboard-list',
            titulo: (editando ? 'Editar ' : 'Agregar ') + 'Gestión',
            subtitulo: [estatus ? estatus.nombre : '', sub ? sub.nombre : '', tipoGestion ? tipoGestion.nombre : '']
                .filter(Boolean)
                .join(' / ') || 'Opción de seguimiento dentro del flujo visual.'
        };
    }
    function abrirForm(tipo, row) {
        const r = row || {};
        byId('atlas-com-form-tipo').value = tipo;
        byId('atlas-com-form-id').value = r.id || '';
        const contexto = contextoModalCatalogo(tipo, r);
        byId('atlas-com-modal-title').innerHTML = '<i class="' + esc(contexto.icono) + ' me-2"></i><span>' + esc(contexto.titulo) + '</span>';
        const subtituloModal = byId('atlas-com-modal-subtitle');
        if (subtituloModal) subtituloModal.textContent = contexto.subtitulo;
        let html = '';
        if (tipo === 'dictamen') {
            html = '<div class="col-md-4"><label class="form-label">Código</label><input class="form-control" name="codigo_estatus" value="' + esc(r.codigo_estatus || '') + '" placeholder="Código de estatus"></div><div class="col-md-8"><label class="form-label atlas-com-required">Nombre</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="Nombre del estatus"></div><div class="col-12"><label class="form-label">Objetivo</label><textarea class="form-control" name="objetivo" rows="3" placeholder="Objetivo comercial">' + esc(r.objetivo || '') + '</textarea></div>';
        } else if (tipo === 'subestatus') {
            if (r._bloquear_contexto) {
                const estatusActual = findCatalogo('dictamen', r.dictamen_id);
                html = '<input type="hidden" name="dictamen_id" value="' + esc(r.dictamen_id || '') + '">' +
                    '<div class="col-12"><div class="atlas-com-flow-context">' +
                    '<div class="atlas-com-flow-context-item"><span class="atlas-com-flow-context-label">Estatus</span><span class="atlas-com-flow-context-value">' + esc(estatusActual ? estatusActual.nombre : 'No capturado') + '</span></div>' +
                    '</div></div>';
            } else {
                html = '<div class="col-md-6"><label class="form-label atlas-com-required">Estatus</label><select class="form-select" name="dictamen_id" required>' + optionRows(datos.dictamenes, r.dictamen_id, 'Selecciona estatus') + '</select></div>';
            }
            html += '<div class="col-md-6"><label class="form-label atlas-com-required">Subestatus</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="Nombre del subestatus"></div>';
        } else if (tipo === 'tipo_gestion') {
            const icono = String(r.icon_font || 'fa-solid fa-briefcase').trim();
            html = '<div class="col-12"><label class="form-label atlas-com-required">Tipo de gestión</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="Impulso comercial"></div><div class="col-12"><label class="form-label atlas-com-required">Icono</label><input type="hidden" name="icon_font" id="atlas-com-icon-font" value="' + esc(icono) + '">' + renderIconosTipoGestion(icono) + '</div>';
        } else {
            if (r._bloquear_contexto) {
                const subActual = findCatalogo('subestatus', r.subestatus_id);
                const estatusActual = subActual && subActual.dictamen_id ? findCatalogo('dictamen', subActual.dictamen_id) : null;
                const tipoActualGestion = findCatalogo('tipo_gestion', r.tipo_gestion_id);
                html = '<input type="hidden" name="subestatus_id" value="' + esc(r.subestatus_id || '') + '">' +
                    '<input type="hidden" name="tipo_gestion_id" value="' + esc(r.tipo_gestion_id || '') + '">' +
                    '<div class="col-12"><div class="atlas-com-flow-context">' +
                    '<div class="atlas-com-flow-context-item"><span class="atlas-com-flow-context-label">Estatus</span><span class="atlas-com-flow-context-value">' + esc(estatusActual ? estatusActual.nombre : 'No capturado') + '</span></div>' +
                    '<div class="atlas-com-flow-context-item"><span class="atlas-com-flow-context-label">Subestatus</span><span class="atlas-com-flow-context-value">' + esc(subActual ? subActual.nombre : 'No capturado') + '</span></div>' +
                    '<div class="atlas-com-flow-context-item"><span class="atlas-com-flow-context-label">Tipo</span><span class="atlas-com-flow-context-value">' + esc(tipoActualGestion ? tipoActualGestion.nombre : 'No capturado') + '</span></div>' +
                    '</div></div>';
            } else {
                const subs = datos.subestatus.map(s => Object.assign({}, s, { nombre: (s.dictamen_nombre || '') + ' / ' + s.nombre }));
                html = '<div class="col-md-6"><label class="form-label atlas-com-required">Subestatus</label><select class="form-select" name="subestatus_id" required>' + optionRows(subs, r.subestatus_id, 'Selecciona subestatus') + '</select></div><div class="col-md-6"><label class="form-label">Tipo gestión</label><select class="form-select" name="tipo_gestion_id">' + optionRows(datos.tipos_gestion, r.tipo_gestion_id, 'Selecciona tipo de gestión') + '</select></div>';
            }
            html += '<div class="col-md-6"><label class="form-label atlas-com-required">Gestión desplegable</label><input class="form-control" name="nombre" value="' + esc(r.nombre || '') + '" required placeholder="WhatsApp enviado"><small class="text-muted fw-semibold">Texto que verá el usuario como opción de gestión.</small></div><div class="col-md-6"><label class="form-label">Ventana complementaria</label><input class="form-control" name="ventana_complementaria" value="' + esc(r.ventana_complementaria || '') + '"><small class="text-muted fw-semibold">Pantalla o bloque extra que debe abrirse al elegir esta gestión.</small></div><div class="col-md-6"><label class="form-label">Campos adicionales</label><input class="form-control" name="campos_adicionales" value="' + esc(r.campos_adicionales || '') + '"><small class="text-muted fw-semibold">Datos extra que se pedirán para completar la gestión.</small></div><div class="col-md-6"><label class="form-label">Requiere fecha</label><select class="form-select" name="requiere_fecha"><option value="0">No</option><option value="1"' + (Number(r.requiere_fecha) === 1 ? ' selected' : '') + '>Sí</option></select><small class="text-muted fw-semibold">Indica si el usuario debe capturar una fecha de seguimiento.</small></div><div class="col-md-6"><label class="form-label">Permite comentario</label><select class="form-select" name="permite_comentario"><option value="1">Sí</option><option value="0"' + (Number(r.permite_comentario) === 0 ? ' selected' : '') + '>No</option></select></div>';
        }
        html += '<div class="col-md-6"><label class="form-label">Activo</label><select class="form-select" name="activo"><option value="1"' + (Number(r.activo ?? 1) === 1 ? ' selected' : '') + '>Activo</option><option value="0"' + (Number(r.activo ?? 1) === 0 ? ' selected' : '') + '>Inactivo</option></select></div>';
        html += '<div class="col-md-6"><label class="form-label">Estado</label><select class="form-select" name="estado_registro"><option value="publicado">Publicado</option><option value="pendiente"' + (norm(r.estado_registro) === 'pendiente' ? ' selected' : '') + '>Pendiente de publicar</option><option value="borrador"' + (norm(r.estado_registro) === 'borrador' ? ' selected' : '') + '>Borrador</option></select></div>';
        byId('atlas-com-form-fields').innerHTML = html;
        showModal();
    }
    function formJson(form) {
        const out = {};
        new FormData(form).forEach((v,k) => out[k] = v);
        return out;
    }
    function activo(row) {
        return Number(row && row.activo !== undefined ? row.activo : 1) === 1;
    }
    function findCatalogo(tipo, id) {
        return arr(tipo).find(r => String(r.id || '') === String(id || '')) || null;
    }
    function flowActions(tipo, id, label) {
        return '<span class="atlas-com-flow-actions">' +
            '<button type="button" class="atlas-com-flow-action" data-atlas-com-flow-edit="' + esc(tipo) + '" data-id="' + esc(id) + '" title="Editar ' + esc(label) + '" aria-label="Editar ' + esc(label) + '"><i class="fa-solid fa-pen"></i></button>' +
            '<button type="button" class="atlas-com-flow-action atlas-com-flow-action--danger" data-atlas-com-flow-off="' + esc(tipo) + '" data-id="' + esc(id) + '" title="Inactivar ' + esc(label) + '" aria-label="Inactivar ' + esc(label) + '"><i class="fa-solid fa-trash-can"></i></button>' +
        '</span>';
    }
    function flowTipoGestionAccion(tipo) {
        if (!tipo || !tipo.id) return '';
        return '<span class="atlas-com-flow-actions">' +
            '<button type="button" class="atlas-com-flow-action" data-atlas-com-flow-change-tipo="' + esc(tipo.id) + '" title="Cambiar tipo de gestión" aria-label="Cambiar tipo de gestión"><i class="fa-solid fa-right-left"></i></button>' +
        '</span>';
    }
    function renderAddGestion(sub, tipoId) {
        return '<button type="button" class="atlas-com-tree-add atlas-com-tree-add-gest" data-atlas-com-flow-add-gestion="' + esc(sub.id) + '"' + (tipoId ? ' data-tipo-gestion-id="' + esc(tipoId) + '"' : '') + '><i class="fa-solid fa-plus"></i><span>Agregar gestión</span></button>';
    }
    function renderAddSubestatus(dictamenId) {
        return '<button type="button" class="atlas-com-tree-add atlas-com-tree-add-sub" data-atlas-com-flow-add-subestatus="' + esc(dictamenId) + '"><i class="fa-solid fa-plus"></i><span>Agregar subestatus</span></button>';
    }
    function abrirCambiarTipoGestion(btn) {
        const wrap = btn.closest('.atlas-com-tree-type-wrap');
        const subestatusId = wrap ? (wrap.getAttribute('data-subestatus-id') || '') : '';
        const tipoActualId = wrap ? (wrap.getAttribute('data-tipo-gestion-id') || '') : '';
        const rows = datos.gestiones.filter(g => activo(g) && String(g.subestatus_id || '') === String(subestatusId) && String(g.tipo_gestion_id || '') === String(tipoActualId));
        const select = byId('atlas-com-cambiar-tipo-nuevo');
        const ayuda = byId('atlas-com-cambiar-tipo-ayuda');
        if (!subestatusId || !tipoActualId || !rows.length || !select) return;
        contextoCambiarTipo = { subestatusId: subestatusId, tipoActualId: tipoActualId, gestiones: rows };
        byId('atlas-com-cambiar-subestatus-id').value = subestatusId;
        byId('atlas-com-cambiar-tipo-actual-id').value = tipoActualId;
        select.innerHTML = optionRows(datos.tipos_gestion.filter(activo), '', 'Selecciona tipo de gestión');
        Array.from(select.options).forEach(opt => {
            if (String(opt.value) === String(tipoActualId)) opt.disabled = true;
        });
        if (ayuda) ayuda.textContent = 'Se actualizarán ' + rows.length + ' gestiones de esta rama.';
        showModalCambiarTipo();
    }
    async function guardarCambioTipoGestion() {
        const select = byId('atlas-com-cambiar-tipo-nuevo');
        const nuevoTipoId = select ? String(select.value || '') : '';
        if (!contextoCambiarTipo || !nuevoTipoId) throw new Error('Selecciona el nuevo tipo de gestión.');
        for (const row of contextoCambiarTipo.gestiones) {
            await json('/Atlas/guardarCatalogoComercial', Object.assign({}, row, {
                tipo: 'gestion',
                tipo_gestion_id: nuevoTipoId,
                activo: 1,
                estado_registro: row.estado_registro || 'publicado'
            }));
        }
        contextoCambiarTipo = null;
        hideModalCambiarTipo();
        await cargar({ showLoader: false });
    }
    function renderGestionFlujo(g) {
        return '<div class="atlas-com-tree-gest">' +
            '<div class="atlas-com-tree-gest-main"><span class="atlas-com-tree-node-label atlas-com-tree-node-label--gest"><i class="fa-solid fa-circle-dot"></i>Gestión</span><span>' + esc(g.nombre) + '</span></div>' +
            flowActions('gestion', g.id, 'gestión') +
        '</div>';
    }
    function renderTipoGestionFlujo(tipo, gestiones, sub) {
        const nombre = tipo ? (tipo.nombre || 'Sin tipo de gestión') : 'Sin tipo de gestión';
        const icono = tipo ? (tipo.icon_font || 'fa-solid fa-briefcase') : 'fa-solid fa-briefcase';
        const acciones = flowTipoGestionAccion(tipo);
        return '<div class="atlas-com-tree-type-wrap" data-subestatus-id="' + esc(sub.id) + '" data-tipo-gestion-id="' + esc(tipo && tipo.id ? tipo.id : '') + '">' +
            '<div class="atlas-com-tree-type">' +
                '<div class="atlas-com-tree-type-main"><span class="atlas-com-tree-node-label atlas-com-tree-node-label--type"><i class="' + esc(icono) + '"></i>Tipo</span><span>' + esc(nombre) + '</span></div>' +
                acciones +
            '</div>' +
            (gestiones.length ? gestiones.map(renderGestionFlujo).join('') : '<div class="atlas-com-muted ms-4 mt-1">Sin gestiones activas en este tipo.</div>') +
            renderAddGestion(sub, tipo && tipo.id ? tipo.id : '') +
        '</div>';
    }
    function renderSubestatusFlujo(sub) {
        const tiposActivos = datos.tipos_gestion.filter(activo);
        const tipoPorId = new Map(tiposActivos.map(t => [String(t.id || ''), t]));
        const gestiones = datos.gestiones
            .filter(g => activo(g) && String(g.subestatus_id || '') === String(sub.id || ''))
            .filter(g => !g.tipo_gestion_id || tipoPorId.has(String(g.tipo_gestion_id || '')))
            .sort((a, b) => (Number(a.orden || 0) - Number(b.orden || 0)) || String(a.nombre || '').localeCompare(String(b.nombre || '')));
        const grupos = new Map();
        gestiones.forEach(g => {
            const key = g.tipo_gestion_id ? String(g.tipo_gestion_id) : 'sin_tipo';
            if (!grupos.has(key)) grupos.set(key, []);
            grupos.get(key).push(g);
        });
        const cuerpo = Array.from(grupos.entries()).map(([key, rows]) => {
            const tipo = key === 'sin_tipo' ? null : tipoPorId.get(key);
            return renderTipoGestionFlujo(tipo, rows, sub);
        }).join('');
        return '<div class="atlas-com-tree-sub">' +
            '<div class="atlas-com-tree-sub-head"><div class="atlas-com-main"><span class="atlas-com-tree-node-label atlas-com-tree-node-label--sub"><i class="fa-solid fa-layer-group"></i>Subestatus</span>' + esc(sub.nombre) + '</div></div>' +
            '<div class="atlas-com-tree-branch">' + (cuerpo || '<div class="atlas-com-muted mt-2">Este subestatus todavía no tiene gestiones activas.</div>') + '</div>' +
        '</div>';
    }
    function renderTree() {
        const q = norm(byId('atlas-com-tree-search') ? byId('atlas-com-tree-search').value : '');
        const cont = byId('atlas-com-tree');
        const dictamenesActivos = datos.dictamenes.filter(activo);
        cont.innerHTML = dictamenesActivos.filter(d => {
            if (!q) return true;
            const subsQ = datos.subestatus.filter(s => activo(s) && String(s.dictamen_id) === String(d.id));
            const gestQ = datos.gestiones.filter(g => activo(g) && String(g.dictamen_id) === String(d.id));
            return JSON.stringify(d).toLowerCase().includes(q) ||
                subsQ.some(s => JSON.stringify(s).toLowerCase().includes(q)) ||
                gestQ.some(g => JSON.stringify(g).toLowerCase().includes(q));
        }).map(d => {
            const subs = datos.subestatus.filter(s => activo(s) && String(s.dictamen_id) === String(d.id));
            const gestionesCount = subs.reduce((acc, s) => acc + datos.gestiones.filter(g => activo(g) && String(g.subestatus_id) === String(s.id)).length, 0);
            const meta = '<div class="atlas-com-tree-meta"><div class="atlas-com-muted">' + esc(subs.length) + ' subestatus · ' + esc(gestionesCount) + ' gestiones</div>' + (d.objetivo ? '<div class="atlas-com-tree-objective">Objetivo: ' + esc(d.objetivo) + '</div>' : '') + '</div>';
            return '<details class="atlas-com-tree-item"' + (q ? ' open' : '') + '><summary class="atlas-com-tree-h"><div class="atlas-com-tree-title"><i class="fa-solid fa-chevron-right atlas-com-tree-toggle"></i><div><div class="atlas-com-main">' + esc(d.orden || '') + '. ' + esc(d.nombre) + '</div>' + meta + '</div></div>' + badgeActivo(d.activo) + '</summary><div class="atlas-com-tree-body">' + (subs.length ? subs.map(renderSubestatusFlujo).join('') : '<div class="text-muted fw-semibold">Este estatus todavía no tiene subestatus activo.</div>') + renderAddSubestatus(d.id) + '</div></details>';
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
    async function inactivarCatalogoFlujo(tipo, id) {
        const row = findCatalogo(tipo, id);
        if (!row) return;
        const etiqueta = tipo === 'tipo_gestion' ? 'tipo de gestión' : 'gestión';
        const confirmar = async () => {
            showBusy();
            try {
                await json('/Atlas/guardarCatalogoComercial', Object.assign({}, row, { tipo: tipo, activo: 0 }));
                await cargar({ showLoader: false });
            } catch (err) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo inactivar', text:err.message || 'Error' });
            } finally {
                hideBusy();
            }
        };
        if (typeof Swal !== 'undefined') {
            const r = await Swal.fire({
                icon: 'warning',
                title: 'Inactivar ' + etiqueta,
                text: 'Ya no aparecerá en el flujo visual activo.',
                showCancelButton: true,
                confirmButtonText: 'Inactivar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626'
            });
            if (r.isConfirmed) await confirmar();
            return;
        }
        if (window.confirm('Inactivar ' + etiqueta + '?')) await confirmar();
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
        const addGestion = ev.target.closest('[data-atlas-com-flow-add-gestion]');
        if (addGestion) {
            ev.preventDefault();
            const subId = addGestion.getAttribute('data-atlas-com-flow-add-gestion') || '';
            const tipoId = addGestion.getAttribute('data-tipo-gestion-id') || '';
            const sub = findCatalogo('subestatus', subId);
            abrirForm('gestion', {
                subestatus_id: subId,
                tipo_gestion_id: tipoId,
                orden: '',
                activo: 1,
                estado_registro: 'publicado',
                dictamen_id: sub ? sub.dictamen_id : '',
                _bloquear_contexto: true
            });
            return;
        }
        const addSubestatus = ev.target.closest('[data-atlas-com-flow-add-subestatus]');
        if (addSubestatus) {
            ev.preventDefault();
            abrirForm('subestatus', {
                dictamen_id: addSubestatus.getAttribute('data-atlas-com-flow-add-subestatus') || '',
                activo: 1,
                estado_registro: 'publicado',
                _bloquear_contexto: true
            });
            return;
        }
        const flowEdit = ev.target.closest('[data-atlas-com-flow-edit]');
        if (flowEdit) {
            ev.preventDefault();
            const tipo = flowEdit.getAttribute('data-atlas-com-flow-edit') || '';
            abrirForm(tipo, findCatalogo(tipo, flowEdit.getAttribute('data-id')) || null);
            return;
        }
        const flowOff = ev.target.closest('[data-atlas-com-flow-off]');
        if (flowOff) {
            ev.preventDefault();
            await inactivarCatalogoFlujo(flowOff.getAttribute('data-atlas-com-flow-off') || '', flowOff.getAttribute('data-id') || '');
            return;
        }
        const changeTipo = ev.target.closest('[data-atlas-com-flow-change-tipo]');
        if (changeTipo) {
            ev.preventDefault();
            abrirCambiarTipoGestion(changeTipo);
            return;
        }
        const toggleSub = ev.target.closest('[data-toggle-subestatus]');
        if (toggleSub) {
            ev.preventDefault();
            const id = String(toggleSub.getAttribute('data-toggle-subestatus') || '');
            if (estatusAbiertos.has(id)) estatusAbiertos.delete(id);
            else estatusAbiertos.add(id);
            renderAdmin();
            return;
        }
        const tab = ev.target.closest('[data-atlas-com-tipo-tab]');
        if (tab) {
            const tipo = tab.getAttribute('data-atlas-com-tipo-tab');
            guardarAtlasComTab(tipo);
            mostrarAtlasComAdmin(tipo);
            return;
        }
        const flujoTab = ev.target.closest('[data-atlas-com-flujo-tab]');
        if (flujoTab) {
            guardarAtlasComTab('flujo');
            mostrarAtlasComFlujo();
            return;
        }
        const edit = ev.target.closest('[data-edit]');
        if (edit) { abrirForm(tipoActual, arr(tipoActual).find(r => String(r.id) === String(edit.getAttribute('data-edit'))) || null); return; }
        const iconBtn = ev.target.closest('[data-atlas-com-icon]');
        if (iconBtn) {
            ev.preventDefault();
            const icon = iconBtn.getAttribute('data-atlas-com-icon') || 'fa-solid fa-briefcase';
            const input = byId('atlas-com-icon-font');
            if (input) input.value = icon;
            document.querySelectorAll('.atlas-com-icon-option').forEach(btn => btn.classList.toggle('is-active', btn === iconBtn));
            return;
        }
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
    if (formCambiarTipo) {
        formCambiarTipo.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            try { await guardarCambioTipoGestion(); }
            catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'No se pudo cambiar', text:err.message || 'Error' }); }
        });
    }
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
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { cargar(); }, { once: true });
    } else {
        cargar();
    }
})();
</script>
