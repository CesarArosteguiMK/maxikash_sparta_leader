<div class="container-fluid py-4 ch-carta-gestor-page">
    <style>
        .ch-carta-gestor-page { color:#22303e; }
        .ch-carta-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .ch-carta-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .ch-carta-title i { color:#26344e; }
        .ch-carta-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; max-width:760px; }
        .ch-carta-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .ch-carta-actions .btn { min-height:2.25rem; display:inline-flex; align-items:center; justify-content:center; gap:.4rem; font-weight:700; }
        .ch-carta-summary { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .ch-carta-kpi { border:1px solid #e2e8f0; border-radius:.65rem; background:#fff; padding:.85rem .95rem; min-height:5rem; }
        .ch-carta-kpi-label { color:#64748b; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.025em; }
        .ch-carta-kpi-value { color:#22303e; font-size:1.55rem; font-weight:900; line-height:1.1; margin-top:.25rem; }
        .ch-carta-toolbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem; }
        .ch-carta-search { max-width:360px; min-width:240px; }
        .ch-carta-name { min-width:260px; }
        .ch-carta-name strong { display:block; color:#1f2937; font-size:.92rem; line-height:1.25; text-transform:uppercase; }
        .ch-carta-name small { color:#64748b; font-weight:700; }
        .ch-carta-meta { color:#475569; font-size:.82rem; font-weight:700; line-height:1.45; min-width:240px; }
        .ch-carta-meta span { display:block; }
        .ch-carta-badge { border-radius:999px; display:inline-flex; align-items:center; gap:.35rem; font-size:.75rem; font-weight:800; padding:.35rem .65rem; }
        .ch-carta-badge-pending { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .ch-carta-row-actions { display:flex; align-items:center; justify-content:flex-end; gap:.35rem; min-width:220px; }
        .ch-carta-row-actions .btn { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; font-weight:800; }
        .ch-carta-modal-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.65rem; }
        .ch-carta-modal-kpi { border:1px solid #e2e8f0; border-radius:.55rem; padding:.7rem .8rem; background:#fff; }
        .ch-carta-modal-kpi span { display:block; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; }
        .ch-carta-modal-kpi strong { color:#22303e; font-size:1.25rem; line-height:1.1; }
        .ch-carta-badge-sent { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .ch-carta-badge-done { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
        .ch-carta-badge-none { background:#f8fafc; color:#475569; border:1px solid #cbd5e1; }
        .ch-carta-status-cell { min-width:150px; }
        .ch-carta-status-badge { min-width:132px; justify-content:center; gap:.5rem; padding:.45rem .7rem; line-height:1.1; white-space:nowrap; }
        .ch-carta-status-badge i { font-size:.72rem; flex:0 0 auto; }
        .ch-carta-file-cell { min-width:220px; max-width:280px; }
        .ch-carta-file-name { display:block; max-width:210px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#64748b; font-weight:700; }
        .ch-carta-file-actions { display:flex; align-items:center; gap:.4rem; margin-top:.35rem; }
        .ch-carta-modal-toolbar { display:flex; align-items:end; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }
        .ch-carta-state-filter { position:relative; min-width:235px; }
        .ch-carta-state-filter .form-select { min-height:38px; font-weight:600; color:#374151; background-color:#fff; }
        .ch-carta-state-filter .dropdown-menu { width:100%; max-height:14rem; overflow:auto; padding:.25rem; z-index:1080; box-shadow:0 12px 28px rgba(15,23,42,.16); border:1px solid #d9e2ec; }
        .ch-carta-state-filter .dropdown-item { border-radius:.35rem; font-weight:700; color:#334155; }
        .ch-carta-state-filter .dropdown-item.active,
        .ch-carta-state-filter .dropdown-item:active { background:#26344e; color:#fff; }
        .ch-carta-modal-toolbar .form-control { min-width:220px; }
        #btnExcelSeguimientoCarta { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; }
        .ch-carta-table-controls { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin:.5rem 0 1rem; }
        .ch-carta-length { display:inline-flex; align-items:center; gap:.65rem; color:#2f3540; font-size:.95rem; }
        .ch-carta-length select { min-width:92px; border:1px solid #cbd5e1; border-radius:.85rem; padding:.45rem 2rem .45rem .85rem; color:#334155; background-color:#fff; font-weight:600; }
        .ch-carta-pagination { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; border-top:1px solid #e5e7eb; padding-top:1rem; margin-top:.5rem; }
        .ch-carta-pagination-info { color:#a0a7b1; font-size:.92rem; font-weight:800; }
        .ch-carta-page-buttons { display:flex; align-items:center; justify-content:flex-end; gap:.55rem; flex-wrap:wrap; }
        .ch-carta-page-btn { min-width:2.35rem; height:2.35rem; border:1px solid #eef1f6; border-radius:.5rem; background:#fff; color:#64748b; display:inline-flex; align-items:center; justify-content:center; font-size:.9rem; font-weight:800; box-shadow:0 1px 3px rgba(15,23,42,.04); }
        .ch-carta-page-btn:hover:not(:disabled) { border-color:#cbd5e1; color:#26344e; }
        .ch-carta-page-btn.active { background:#2f4f9f; color:#fff; border-color:#2f4f9f; box-shadow:0 4px 10px rgba(47,79,159,.28); }
        .ch-carta-page-btn:disabled { opacity:.48; cursor:not-allowed; }
        .ch-carta-page-ellipsis { color:#94a3b8; font-weight:900; min-width:2rem; text-align:center; }
        .ch-carta-doc-modal { max-width:min(96vw, 1220px); }
        .ch-carta-doc-frame { width:100%; height:78vh; border:0; background:#f8fafc; display:block; }
        @media (max-width: 767.98px) {
            .ch-carta-head,
            .ch-carta-actions,
            .ch-carta-toolbar { align-items:stretch; flex-direction:column; }
            .ch-carta-summary { grid-template-columns:1fr; }
            .ch-carta-actions .btn,
            .ch-carta-search { width:100%; max-width:none; }
            .ch-carta-row-actions { justify-content:flex-start; min-width:0; flex-wrap:wrap; }
            .ch-carta-modal-summary { grid-template-columns:1fr 1fr; }
            .ch-carta-pagination { align-items:stretch; flex-direction:column; }
            .ch-carta-page-buttons { justify-content:flex-start; }
            .ch-carta-doc-frame { height:72vh; }
        }
    </style>

    <div class="ch-carta-head">
        <div>
            <h1 class="ch-carta-title"><i class="fa-solid fa-file-signature"></i><span>Carta compromiso Gestor</span></h1>
            <p class="ch-carta-subtitle">Gestores activos que aun no tienen integrada la Carta de compromiso del Gestor en su expediente. Al cargar el documento, salen automaticamente de esta lista.</p>
        </div>
        <div class="ch-carta-actions">
            <button class="btn btn-label-primary" type="button" id="btnSeguimientoCartaGestor" title="Seguimiento de cartas">
                <i class="fa-solid fa-clipboard-list"></i><span>Seguimiento de cartas</span>
            </button>
            <button class="btn btn-label-secondary" type="button" id="btnActualizarCartaGestor" title="Actualizar">
                <i class="fa-solid fa-rotate"></i><span>Actualizar</span>
            </button>
        </div>
    </div>

    <div class="ch-carta-summary">
        <div class="ch-carta-kpi">
            <div class="ch-carta-kpi-label">Pendientes</div>
            <div class="ch-carta-kpi-value" id="cartaGestorTotal">0</div>
        </div>
        <div class="ch-carta-kpi">
            <div class="ch-carta-kpi-label">Con correo</div>
            <div class="ch-carta-kpi-value" id="cartaGestorConCorreo">0</div>
        </div>
        <div class="ch-carta-kpi">
            <div class="ch-carta-kpi-label">Sin correo</div>
            <div class="ch-carta-kpi-value" id="cartaGestorSinCorreo">0</div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="ch-carta-toolbar">
                <div>
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-list-check me-2"></i>Gestores pendientes</h5>
                    <div class="text-muted small fw-semibold" id="cartaGestorInfo">Cargando informacion...</div>
                </div>
                <input type="search" class="form-control ch-carta-search" id="cartaGestorBuscar" placeholder="Buscar gestor, puesto o departamento">
            </div>

            <div class="ch-carta-table-controls">
                <label class="ch-carta-length" for="cartaGestorPageSize">
                    <span>Mostrar</span>
                    <select id="cartaGestorPageSize" class="form-select">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>registros</span>
                </label>
            </div>

            <div class="table-responsive">
                <table class="table align-middle border-top">
                    <thead>
                        <tr>
                            <th>Gestor</th>
                            <th>Puesto / estructura</th>
                            <th>Jefe</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cartaGestorRows">
                        <tr><td colspan="5" class="text-center text-muted fw-semibold py-4">Cargando gestores...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="ch-carta-pagination" id="cartaGestorPagination">
                <div class="ch-carta-pagination-info" id="cartaGestorPageInfo">Mostrando de 0 a 0 de 0 registros</div>
                <div class="ch-carta-page-buttons" id="cartaGestorPageButtons" aria-label="Paginacion de gestores pendientes"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSeguimientoCartaGestor" tabindex="-1" aria-labelledby="modalSeguimientoCartaGestorLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalSeguimientoCartaGestorLabel">
                        <i class="fa-solid fa-clipboard-list me-2"></i>Seguimiento de cartas
                    </h5>
                    <div class="text-muted small fw-semibold">Consulta quien ya envio la carta y quien sigue pendiente despues del correo.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="ch-carta-modal-summary mb-3">
                    <div class="ch-carta-modal-kpi"><span>Total gestores</span><strong id="segCartaTotal">0</strong></div>
                    <div class="ch-carta-modal-kpi"><span>Pendientes de subir</span><strong id="segCartaPendienteSubir">0</strong></div>
                    <div class="ch-carta-modal-kpi"><span>Cartas recibidas</span><strong id="segCartaRecibida">0</strong></div>
                    <div class="ch-carta-modal-kpi"><span>Sin correo enviado</span><strong id="segCartaSinCorreo">0</strong></div>
                </div>

                <div class="ch-carta-modal-toolbar mb-3">
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                        <div>
                            <label class="form-label small fw-bold text-muted mb-1" for="segCartaEstadoBtn">Estado</label>
                            <div class="dropdown ch-carta-state-filter">
                                <button class="form-select text-start" type="button" id="segCartaEstadoBtn" aria-expanded="false">
                                    Todos los pendientes
                                </button>
                                <div class="dropdown-menu" id="segCartaEstadoMenu" aria-labelledby="segCartaEstadoBtn">
                                    <button class="dropdown-item active" type="button" data-estado="pendientes">Todos los pendientes</button>
                                    <button class="dropdown-item" type="button" data-estado="pendiente_subir">Pendientes de subir carta</button>
                                    <button class="dropdown-item" type="button" data-estado="recibida">Cartas recibidas</button>
                                    <button class="dropdown-item" type="button" data-estado="sin_correo_enviado">Sin correo enviado</button>
                                    <button class="dropdown-item" type="button" data-estado="todos">Todos</button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label small fw-bold text-muted mb-1" for="segCartaBuscar">Buscar</label>
                            <input type="search" class="form-control" id="segCartaBuscar" placeholder="Gestor, correo, jefe o puesto">
                        </div>
                        <label class="ch-carta-length mb-0" for="segCartaPageSize">
                            <span>Mostrar</span>
                            <select id="segCartaPageSize" class="form-select">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span>registros</span>
                        </label>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-label-secondary" id="btnRefrescarSeguimientoCarta">
                            <i class="fa-solid fa-rotate"></i><span>Actualizar</span>
                        </button>
                        <button type="button" class="btn btn-success" id="btnExcelSeguimientoCarta">
                            <i class="fa-solid fa-file-excel"></i><span>Descargar Excel</span>
                        </button>
                    </div>
                </div>

                <div class="text-muted small fw-semibold mb-2" id="segCartaInfo">Cargando seguimiento...</div>
                <div class="table-responsive">
                    <table class="table align-middle border-top">
                        <thead>
                            <tr>
                                <th>Gestor</th>
                                <th>Puesto / estructura</th>
                                <th>Jefe</th>
                                <th class="text-center ch-carta-status-cell">Estado</th>
                                <th>Correo enviado</th>
                                <th>Carta subida</th>
                            </tr>
                        </thead>
                        <tbody id="segCartaRows">
                            <tr><td colspan="6" class="text-center text-muted fw-semibold py-4">Cargando seguimiento...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="ch-carta-pagination" id="segCartaPagination">
                    <div class="ch-carta-pagination-info" id="segCartaPageInfo">Mostrando de 0 a 0 de 0 registros</div>
                    <div class="ch-carta-page-buttons" id="segCartaPageButtons" aria-label="Paginacion de seguimiento"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDocumentoCartaGestor" tabindex="-1" aria-labelledby="modalDocumentoCartaGestorLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered ch-carta-doc-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalDocumentoCartaGestorLabel">
                        <i class="fa-solid fa-file-pdf me-2"></i>Documento de carta
                    </h5>
                    <div class="text-muted small fw-semibold" id="segCartaDocName">Vista previa del documento cargado.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="ch-carta-doc-frame" id="segCartaDocFrame" title="Documento de carta" src="about:blank"></iframe>
            </div>
            <div class="modal-footer">
                <a class="btn btn-label-secondary" id="segCartaDocOpen" href="#" target="_blank" rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir aparte
                </a>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const state = { rows: [], filtro: '', pagina: 1, pageSize: 10, seguimientoRows: [], seguimientoFiltro: '', seguimientoEstado: 'pendientes', seguimientoResumen: {}, seguimientoPagina: 1, seguimientoPageSize: 10 };
    const els = {
        rows: document.getElementById('cartaGestorRows'),
        info: document.getElementById('cartaGestorInfo'),
        total: document.getElementById('cartaGestorTotal'),
        conCorreo: document.getElementById('cartaGestorConCorreo'),
        sinCorreo: document.getElementById('cartaGestorSinCorreo'),
        buscar: document.getElementById('cartaGestorBuscar'),
        actualizar: document.getElementById('btnActualizarCartaGestor'),
        mainPagination: document.getElementById('cartaGestorPagination'),
        mainPageInfo: document.getElementById('cartaGestorPageInfo'),
        mainPageSize: document.getElementById('cartaGestorPageSize'),
        mainPageButtons: document.getElementById('cartaGestorPageButtons'),
        seguimientoBtn: document.getElementById('btnSeguimientoCartaGestor'),
        seguimientoModal: document.getElementById('modalSeguimientoCartaGestor'),
        segRows: document.getElementById('segCartaRows'),
        segInfo: document.getElementById('segCartaInfo'),
        segEstadoBtn: document.getElementById('segCartaEstadoBtn'),
        segEstadoMenu: document.getElementById('segCartaEstadoMenu'),
        segBuscar: document.getElementById('segCartaBuscar'),
        segTotal: document.getElementById('segCartaTotal'),
        segPendienteSubir: document.getElementById('segCartaPendienteSubir'),
        segRecibida: document.getElementById('segCartaRecibida'),
        segSinCorreo: document.getElementById('segCartaSinCorreo'),
        segActualizar: document.getElementById('btnRefrescarSeguimientoCarta'),
        segExcel: document.getElementById('btnExcelSeguimientoCarta'),
        segPagination: document.getElementById('segCartaPagination'),
        segPageInfo: document.getElementById('segCartaPageInfo'),
        segPageSize: document.getElementById('segCartaPageSize'),
        segPageButtons: document.getElementById('segCartaPageButtons'),
        docModal: document.getElementById('modalDocumentoCartaGestor'),
        docFrame: document.getElementById('segCartaDocFrame'),
        docName: document.getElementById('segCartaDocName'),
        docOpen: document.getElementById('segCartaDocOpen')
    };
    const estadoSeguimientoLabels = {
        pendientes: 'Todos los pendientes',
        pendiente_subir: 'Pendientes de subir carta',
        recibida: 'Cartas recibidas',
        sin_correo_enviado: 'Sin correo enviado',
        todos: 'Todos'
    };

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[ch];
        });
    }

    function notify(icon, title, text) {
        if (window.Swal) {
            Swal.fire({ icon, title, text, timer: icon === 'success' ? 2200 : undefined, showConfirmButton: icon !== 'success' });
        } else {
            alert((title ? title + '\n' : '') + (text || ''));
        }
    }

    function cerrarMenuEstadoSeguimiento() {
        if (!els.segEstadoMenu || !els.segEstadoBtn) return;
        els.segEstadoMenu.classList.remove('show');
        els.segEstadoBtn.setAttribute('aria-expanded', 'false');
    }

    function setEstadoSeguimiento(valor, recargar) {
        state.seguimientoEstado = valor || 'pendientes';
        state.seguimientoPagina = 1;
        if (els.segEstadoBtn) {
            els.segEstadoBtn.textContent = estadoSeguimientoLabels[state.seguimientoEstado] || estadoSeguimientoLabels.pendientes;
        }
        if (els.segEstadoMenu) {
            els.segEstadoMenu.querySelectorAll('[data-estado]').forEach(function (item) {
                item.classList.toggle('active', item.dataset.estado === state.seguimientoEstado);
            });
        }
        cerrarMenuEstadoSeguimiento();
        if (recargar) cargarSeguimiento();
    }

    function abrirDocumentoCarta(url, titulo) {
        if (!url) {
            notify('warning', 'Documento no disponible', 'No se encontro la ruta del documento.');
            return;
        }
        if (!els.docModal || !els.docFrame || !els.docOpen) {
            window.open(url, '_blank', 'noopener');
            return;
        }
        if (els.docName) {
            els.docName.textContent = titulo || 'Vista previa del documento cargado.';
        }
        els.docFrame.src = url;
        els.docOpen.href = url;
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(els.docModal).show();
        } else if (window.$ && $('#modalDocumentoCartaGestor').modal) {
            $('#modalDocumentoCartaGestor').modal('show');
        } else {
            window.open(url, '_blank', 'noopener');
        }
    }

    function filteredRows() {
        const filtro = state.filtro.trim().toLowerCase();
        if (!filtro) return state.rows;
        return state.rows.filter(function (row) {
            return [
                row.nombre_completo,
                row.numero_empleado,
                row.correo,
                row.puestos,
                row.departamentos,
                row.areas,
                row.direcciones,
                row.jefe
            ].join(' ').toLowerCase().includes(filtro);
        });
    }

    function paginasVisibles(pagina, totalPages) {
        const total = Math.max(1, Number(totalPages || 1));
        const actual = Math.min(Math.max(1, Number(pagina || 1)), total);
        if (total <= 7) {
            return Array.from({ length: total }, (_, idx) => idx + 1);
        }
        if (actual <= 4) {
            return [1, 2, 3, 4, 5, '...', total];
        }
        if (actual >= total - 3) {
            return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        }
        return [1, '...', actual - 1, actual, actual + 1, '...', total];
    }

    function renderBotonesPaginacion(container, tipo, pagina, totalPages, totalRows) {
        if (!container) return;
        const total = Math.max(1, Number(totalPages || 1));
        const actual = Math.min(Math.max(1, Number(pagina || 1)), total);
        const sinDatos = Number(totalRows || 0) <= 0;
        const disabledPrev = sinDatos || actual <= 1;
        const disabledNext = sinDatos || actual >= total;
        const attr = tipo === 'seg' ? 'data-seg-page' : 'data-main-page';
        const btn = function (value, label, disabled, active, title) {
            return `<button type="button" class="ch-carta-page-btn${active ? ' active' : ''}" ${attr}="${value}" ${disabled ? 'disabled' : ''} title="${escapeHtml(title || label)}">${label}</button>`;
        };
        const pages = paginasVisibles(actual, total).map(function (item) {
            if (item === '...') {
                return '<span class="ch-carta-page-ellipsis">...</span>';
            }
            return btn(item, String(item), sinDatos, item === actual, 'Pagina ' + item);
        }).join('');
        container.innerHTML = [
            btn('first', '&laquo;', disabledPrev, false, 'Primera pagina'),
            btn('prev', '&lsaquo;', disabledPrev, false, 'Pagina anterior'),
            pages,
            btn('next', '&rsaquo;', disabledNext, false, 'Pagina siguiente'),
            btn('last', '&raquo;', disabledNext, false, 'Ultima pagina')
        ].join('');
    }

    function actualizarPaginacionPrincipal(totalRows, totalPages, start, end) {
        const pagina = state.pagina;
        const sinDatos = totalRows <= 0;
        if (els.mainPageInfo) {
            els.mainPageInfo.textContent = sinDatos
                ? 'Sin registros para mostrar'
                : `Mostrando de ${start + 1} a ${end} de ${totalRows} registros`;
        }
        if (!els.mainPagination) return;
        els.mainPagination.querySelectorAll('[data-main-page]').forEach(function (btn) {
            const accion = btn.dataset.mainPage || '';
            const deshabilitar = sinDatos
                || (['first', 'prev'].includes(accion) && pagina <= 1)
                || (['next', 'last'].includes(accion) && pagina >= totalPages);
            btn.disabled = deshabilitar;
        });
        if (els.mainPageInfo) {
            els.mainPageInfo.textContent = sinDatos
                ? 'Sin registros para mostrar'
                : `Mostrando de ${start + 1} a ${end} de ${totalRows} registros`;
        }
        renderBotonesPaginacion(els.mainPageButtons, 'main', state.pagina, totalPages, totalRows);
    }

    function render() {
        const rows = filteredRows();
        const conCorreo = state.rows.filter(row => String(row.correo || '').includes('@')).length;
        els.total.textContent = state.rows.length;
        els.conCorreo.textContent = conCorreo;
        els.sinCorreo.textContent = Math.max(0, state.rows.length - conCorreo);
        const pageSize = Math.max(1, Number(state.pageSize || 10));
        const totalPages = Math.max(1, Math.ceil(rows.length / pageSize));
        state.pagina = Math.min(Math.max(1, Number(state.pagina || 1)), totalPages);
        const start = (state.pagina - 1) * pageSize;
        const end = Math.min(rows.length, start + pageSize);
        const rowsPagina = rows.slice(start, end);
        els.info.textContent = rows.length === state.rows.length
            ? `${state.rows.length} gestor(es) pendientes.`
            : `${rows.length} de ${state.rows.length} gestor(es) visibles.`;

        if (!rows.length) {
            els.rows.innerHTML = '<tr><td colspan="5" class="text-center text-muted fw-semibold py-4">No hay gestores pendientes por Carta de compromiso.</td></tr>';
            actualizarPaginacionPrincipal(0, 1, 0, 0);
            return;
        }

        actualizarPaginacionPrincipal(rows.length, totalPages, start, end);
        els.rows.innerHTML = rowsPagina.map(function (row) {
            const correo = row.correo || 'Sin correo registrado';
            const telefono = row.telefono || '';
            const disabledCorreo = String(row.correo || '').includes('@') ? '' : 'disabled';
            return `
                <tr>
                    <td class="ch-carta-name">
                        <strong>${escapeHtml(row.nombre_completo || 'Sin nombre')}</strong>
                        <small>${escapeHtml(row.numero_empleado || 'Sin numero de empleado')}</small><br>
                        <small>${escapeHtml(correo)}</small>${telefono ? `<br><small>${escapeHtml(telefono)}</small>` : ''}
                    </td>
                    <td class="ch-carta-meta">
                        <span><i class="fa-solid fa-briefcase me-1"></i>${escapeHtml(row.puestos || 'Gestor')}</span>
                        <span><i class="fa-solid fa-building me-1"></i>${escapeHtml(row.departamentos || 'Sin departamento')}</span>
                        <span><i class="fa-solid fa-sitemap me-1"></i>${escapeHtml(row.areas || 'Sin area')}</span>
                        <span><i class="fa-solid fa-location-dot me-1"></i>${escapeHtml(row.direcciones || 'Sin direccion')}</span>
                    </td>
                    <td class="text-muted fw-semibold">${escapeHtml(row.jefe || 'Sin jefe asignado')}</td>
                    <td class="text-center">
                        <span class="ch-carta-badge ch-carta-badge-pending"><i class="fa-solid fa-clock"></i>Pendiente</span>
                    </td>
                    <td class="text-end">
                        <div class="ch-carta-row-actions">
                            <button type="button" class="btn btn-sm btn-primary" data-action="email" data-id="${Number(row.id_persona || 0)}" ${disabledCorreo}>
                                <i class="fa-solid fa-envelope"></i><span>Enviar</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-label-secondary" data-action="copy" data-link="${escapeHtml(row.url_subida || '')}" title="Copiar enlace">
                                <i class="fa-solid fa-link"></i>
                            </button>
                            <a class="btn btn-sm btn-label-secondary" href="${escapeHtml(row.url_subida || '#')}" target="_blank" rel="noopener" title="Abrir enlace">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function seguimientoBadge(row) {
        const estado = row.estado_carta || '';
        if (estado === 'recibida') {
            return '<span class="ch-carta-badge ch-carta-status-badge ch-carta-badge-done"><i class="fa-solid fa-check"></i><span>Carta recibida</span></span>';
        }
        if (estado === 'pendiente_subir') {
            return '<span class="ch-carta-badge ch-carta-status-badge ch-carta-badge-sent"><i class="fa-solid fa-envelope-circle-check"></i><span>Pendiente de subir</span></span>';
        }
        return '<span class="ch-carta-badge ch-carta-status-badge ch-carta-badge-none"><i class="fa-solid fa-envelope"></i><span>Sin correo enviado</span></span>';
    }

    function seguimientoFiltrado() {
        const filtro = state.seguimientoFiltro.trim().toLowerCase();
        if (!filtro) return state.seguimientoRows;
        return state.seguimientoRows.filter(function (row) {
            return [
                row.nombre_completo,
                row.numero_empleado,
                row.correo,
                row.puestos,
                row.departamentos,
                row.areas,
                row.direcciones,
                row.jefe,
                row.estado_carta_label,
                row.fecha_correo_enviado,
                row.fecha_carta_subida
            ].join(' ').toLowerCase().includes(filtro);
        });
    }

    function actualizarPaginacionSeguimiento(totalRows, totalPages, start, end) {
        const pagina = state.seguimientoPagina;
        const sinDatos = totalRows <= 0;
        if (els.segPageInfo) {
            els.segPageInfo.textContent = sinDatos
                ? 'Sin registros para mostrar'
                : `Mostrando de ${start + 1} a ${end} de ${totalRows} registros`;
        }
        if (!els.segPagination) return;
        els.segPagination.querySelectorAll('[data-seg-page]').forEach(function (btn) {
            const accion = btn.dataset.segPage || '';
            const deshabilitar = sinDatos
                || (['first', 'prev'].includes(accion) && pagina <= 1)
                || (['next', 'last'].includes(accion) && pagina >= totalPages);
            btn.disabled = deshabilitar;
        });
        if (els.segPageInfo) {
            els.segPageInfo.textContent = sinDatos
                ? 'Sin registros para mostrar'
                : `Mostrando de ${start + 1} a ${end} de ${totalRows} registros`;
        }
        renderBotonesPaginacion(els.segPageButtons, 'seg', state.seguimientoPagina, totalPages, totalRows);
    }

    function renderSeguimiento(resumen) {
        const rows = seguimientoFiltrado();
        resumen = resumen || {};
        state.seguimientoResumen = resumen;
        els.segTotal.textContent = resumen.total || 0;
        els.segPendienteSubir.textContent = resumen.pendiente_subir || 0;
        els.segRecibida.textContent = resumen.recibida || 0;
        els.segSinCorreo.textContent = resumen.sin_correo_enviado || 0;
        const pageSize = Math.max(1, Number(state.seguimientoPageSize || 10));
        const totalPages = Math.max(1, Math.ceil(rows.length / pageSize));
        state.seguimientoPagina = Math.min(Math.max(1, Number(state.seguimientoPagina || 1)), totalPages);
        const start = (state.seguimientoPagina - 1) * pageSize;
        const end = Math.min(rows.length, start + pageSize);
        const rowsPagina = rows.slice(start, end);
        els.segInfo.textContent = rows.length
            ? `${rows.length} gestor(es) visibles con el filtro seleccionado.`
            : 'No hay gestores visibles con el filtro seleccionado.';

        if (!rows.length) {
            els.segRows.innerHTML = '<tr><td colspan="6" class="text-center text-muted fw-semibold py-4">No hay gestores con este filtro.</td></tr>';
            actualizarPaginacionSeguimiento(0, 1, 0, 0);
            return;
        }

        actualizarPaginacionSeguimiento(rows.length, totalPages, start, end);
        els.segRows.innerHTML = rowsPagina.map(function (row) {
            const telefono = row.telefono || '';
            const correoEnvio = row.fecha_correo_enviado
                ? `${escapeHtml(row.fecha_correo_enviado)}${row.correo_envio ? `<br><small>${escapeHtml(row.correo_envio)}</small>` : ''}${row.correo_enviado_por ? `<br><small>Por: ${escapeHtml(row.correo_enviado_por)}</small>` : ''}`
                : '<span class="text-muted fw-semibold">Sin envio registrado</span>';
            const archivoCarta = row.carta_archivo || '';
            const urlCarta = archivoCarta ? '/caphum/verDocumentoPersona?archivo=' + encodeURIComponent(archivoCarta) : '';
            const cartaSubida = row.fecha_carta_subida
                ? `<div>${escapeHtml(row.fecha_carta_subida)}</div>${archivoCarta ? `<span class="ch-carta-file-name" title="${escapeHtml(archivoCarta)}">${escapeHtml(archivoCarta)}</span><div class="ch-carta-file-actions"><button type="button" class="btn btn-sm btn-label-primary" data-action="view-doc" data-url="${escapeHtml(urlCarta)}" data-title="${escapeHtml(archivoCarta)}" title="Ver documento"><i class="fa-solid fa-eye me-1"></i>Ver documento</button></div>` : ''}`
                : '<span class="text-muted fw-semibold">Pendiente</span>';
            return `
                <tr>
                    <td class="ch-carta-name">
                        <strong>${escapeHtml(row.nombre_completo || 'Sin nombre')}</strong>
                        <small>${escapeHtml(row.numero_empleado || 'Sin numero de empleado')}</small><br>
                        <small>${escapeHtml(row.correo || 'Sin correo registrado')}</small>${telefono ? `<br><small>${escapeHtml(telefono)}</small>` : ''}
                    </td>
                    <td class="ch-carta-meta">
                        <span><i class="fa-solid fa-briefcase me-1"></i>${escapeHtml(row.puestos || 'Gestor')}</span>
                        <span><i class="fa-solid fa-building me-1"></i>${escapeHtml(row.departamentos || 'Sin departamento')}</span>
                        <span><i class="fa-solid fa-sitemap me-1"></i>${escapeHtml(row.areas || 'Sin area')}</span>
                        <span><i class="fa-solid fa-location-dot me-1"></i>${escapeHtml(row.direcciones || 'Sin direccion')}</span>
                    </td>
                    <td class="text-muted fw-semibold">${escapeHtml(row.jefe || 'Sin jefe asignado')}</td>
                    <td class="text-center ch-carta-status-cell">${seguimientoBadge(row)}</td>
                    <td class="small fw-semibold">${correoEnvio}</td>
                    <td class="small fw-semibold ch-carta-file-cell">${cartaSubida}</td>
                </tr>
            `;
        }).join('');
    }

    async function cargar() {
        els.rows.innerHTML = '<tr><td colspan="5" class="text-center text-muted fw-semibold py-4">Cargando gestores...</td></tr>';
        try {
            const res = await fetch('/caphum/getGestoresPendientesCartaCompromiso', { credentials: 'same-origin' });
            const data = await res.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo cargar la lista.');
            state.rows = Array.isArray(data.datos) ? data.datos : [];
            state.pagina = 1;
            render();
        } catch (err) {
            els.rows.innerHTML = '<tr><td colspan="5" class="text-center text-danger fw-semibold py-4">No se pudo cargar la informacion.</td></tr>';
            els.info.textContent = err.message || 'Error al cargar.';
        }
    }

    async function cargarSeguimiento() {
        els.segRows.innerHTML = '<tr><td colspan="6" class="text-center text-muted fw-semibold py-4">Cargando seguimiento...</td></tr>';
        els.segInfo.textContent = 'Cargando seguimiento...';
        state.seguimientoPagina = 1;
        try {
            const qs = new URLSearchParams({ estado: state.seguimientoEstado || 'pendientes' });
            const res = await fetch('/caphum/getSeguimientoCartaCompromisoGestor?' + qs.toString(), { credentials: 'same-origin' });
            const data = await res.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo cargar el seguimiento.');
            const datos = data.datos || {};
            state.seguimientoRows = Array.isArray(datos.rows) ? datos.rows : [];
            renderSeguimiento(datos.resumen || {});
        } catch (err) {
            els.segRows.innerHTML = '<tr><td colspan="6" class="text-center text-danger fw-semibold py-4">No se pudo cargar el seguimiento.</td></tr>';
            els.segInfo.textContent = err.message || 'Error al cargar.';
        }
    }

    async function enviarRecordatorio(idPersona, button) {
        if (!idPersona) return;
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Enviando</span>';
        try {
            const res = await fetch('/caphum/enviarRecordatorioCartaCompromisoGestor', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_persona: idPersona })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo enviar el correo.');
            notify('success', 'Recordatorio enviado', data.mensaje || 'Correo enviado correctamente.');
            await cargar();
            if (els.seguimientoModal && els.seguimientoModal.classList.contains('show')) {
                await cargarSeguimiento();
            }
        } catch (err) {
            notify('error', 'No se pudo enviar', err.message || 'Intenta nuevamente.');
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    }

    async function copiar(text) {
        if (!text) return;
        try {
            await navigator.clipboard.writeText(text);
            notify('success', 'Enlace copiado', 'Ya puedes compartirlo para pruebas.');
        } catch (err) {
            notify('error', 'No se pudo copiar', text);
        }
    }

    els.buscar.addEventListener('input', function () {
        state.filtro = this.value || '';
        state.pagina = 1;
        render();
    });
    els.mainPageSize.addEventListener('change', function () {
        state.pageSize = Math.max(1, Number(this.value || 10));
        state.pagina = 1;
        render();
    });
    els.mainPagination.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-main-page]');
        if (!btn || btn.disabled) return;
        const rows = filteredRows();
        const totalPages = Math.max(1, Math.ceil(rows.length / Math.max(1, Number(state.pageSize || 10))));
        const accion = btn.dataset.mainPage || '';
        if (/^\d+$/.test(accion)) state.pagina = Math.min(totalPages, Math.max(1, Number(accion)));
        if (accion === 'first') state.pagina = 1;
        if (accion === 'prev') state.pagina = Math.max(1, state.pagina - 1);
        if (accion === 'next') state.pagina = Math.min(totalPages, state.pagina + 1);
        if (accion === 'last') state.pagina = totalPages;
        render();
    });
    els.actualizar.addEventListener('click', cargar);
    els.seguimientoBtn.addEventListener('click', function () {
        if (els.seguimientoModal && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(els.seguimientoModal).show();
        } else if (window.$ && $('#modalSeguimientoCartaGestor').modal) {
            $('#modalSeguimientoCartaGestor').modal('show');
        }
        cargarSeguimiento();
    });
    els.segEstadoBtn.addEventListener('click', function (event) {
        event.stopPropagation();
        const abierto = els.segEstadoMenu.classList.toggle('show');
        els.segEstadoBtn.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });
    els.segEstadoMenu.addEventListener('click', function (event) {
        const item = event.target.closest('[data-estado]');
        if (!item) return;
        setEstadoSeguimiento(item.dataset.estado || 'pendientes', true);
    });
    document.addEventListener('click', function (event) {
        if (!els.segEstadoMenu || !els.segEstadoBtn) return;
        if (els.segEstadoMenu.contains(event.target) || els.segEstadoBtn.contains(event.target)) return;
        cerrarMenuEstadoSeguimiento();
    });
    if (els.seguimientoModal) {
        els.seguimientoModal.addEventListener('hidden.bs.modal', cerrarMenuEstadoSeguimiento);
    }
    els.segBuscar.addEventListener('input', function () {
        state.seguimientoFiltro = this.value || '';
        state.seguimientoPagina = 1;
        renderSeguimiento(state.seguimientoResumen || {});
    });
    els.segPageSize.addEventListener('change', function () {
        state.seguimientoPageSize = Math.max(1, Number(this.value || 10));
        state.seguimientoPagina = 1;
        renderSeguimiento(state.seguimientoResumen || {});
    });
    els.segPagination.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-seg-page]');
        if (!btn || btn.disabled) return;
        const rows = seguimientoFiltrado();
        const totalPages = Math.max(1, Math.ceil(rows.length / Math.max(1, Number(state.seguimientoPageSize || 10))));
        const accion = btn.dataset.segPage || '';
        if (/^\d+$/.test(accion)) state.seguimientoPagina = Math.min(totalPages, Math.max(1, Number(accion)));
        if (accion === 'first') state.seguimientoPagina = 1;
        if (accion === 'prev') state.seguimientoPagina = Math.max(1, state.seguimientoPagina - 1);
        if (accion === 'next') state.seguimientoPagina = Math.min(totalPages, state.seguimientoPagina + 1);
        if (accion === 'last') state.seguimientoPagina = totalPages;
        renderSeguimiento(state.seguimientoResumen || {});
    });
    els.segActualizar.addEventListener('click', cargarSeguimiento);
    els.segExcel.addEventListener('click', function () {
        const qs = new URLSearchParams({ estado: state.seguimientoEstado || 'pendientes' });
        window.location.href = '/caphum/descargarSeguimientoCartaCompromisoGestorExcel?' + qs.toString();
    });
    els.segRows.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-action="view-doc"]');
        if (!btn) return;
        abrirDocumentoCarta(btn.dataset.url || '', btn.dataset.title || '');
    });
    if (els.docModal) {
        els.docModal.addEventListener('hidden.bs.modal', function () {
            if (els.docFrame) els.docFrame.src = 'about:blank';
            if (els.seguimientoModal && els.seguimientoModal.classList.contains('show')) {
                document.body.classList.add('modal-open');
            }
        });
    }
    els.rows.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-action]');
        if (!btn) return;
        if (btn.dataset.action === 'email') {
            enviarRecordatorio(Number(btn.dataset.id || 0), btn);
        } else if (btn.dataset.action === 'copy') {
            copiar(btn.dataset.link || '');
        }
    });

    cargar();
})();
</script>
