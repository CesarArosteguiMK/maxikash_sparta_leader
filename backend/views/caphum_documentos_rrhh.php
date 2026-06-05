<div class="container-fluid py-4">
    <style>
        .docs-rrhh-pagination .page-link {
            align-items: center;
            border-color: #e7ebf1;
            border-radius: 10px !important;
            color: #344054;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            min-width: 38px;
            padding: 0 0.75rem;
        }
        .docs-rrhh-pagination .page-item.active .page-link {
            background-color: #304d91;
            border-color: #304d91;
            box-shadow: 0 0.125rem 0.375rem rgba(48, 77, 145, 0.35);
            color: #fff;
        }
        .docs-rrhh-pagination .page-item.disabled .page-link {
            background-color: #fff;
            border-color: #edf0f4;
            color: #b8c0cc;
        }
        .docs-rrhh-pagination .page-item:not(.active):not(.disabled) .page-link:hover {
            background-color: #f6f8fb;
            border-color: #d9dee7;
            color: #243b66;
        }
        .docs-rrhh-import-person-separator > td {
            border-top: 2px solid #263653 !important;
        }
        .docs-rrhh-pdf-preview {
            align-items: center;
            background: #eef1f6;
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-height: 78vh;
            overflow: auto;
            padding: 18px;
        }
        .docs-rrhh-pdf-preview canvas {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 8px 22px rgba(16, 24, 40, 0.18);
            max-width: none;
        }
        .docs-rrhh-pdf-preview-state {
            align-items: center;
            color: #667085;
            display: flex;
            min-height: 72vh;
            justify-content: center;
            text-align: center;
            width: 100%;
        }
    </style>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="btn btn-primary rounded-3 disabled">
                <i class="fa-solid fa-folder-tree"></i>
            </span>
            <div>
                <h1 class="h3 mb-1">Expedientes RR.HH.</h1>
                <p class="text-muted mb-0">Consulta el avance documental de todos los colaboradores.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success" id="btnImportarDocsRrhh">
                <i class="fa-solid fa-file-import me-1"></i>Importar documentos
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnActualizarDocsRrhh">
                <i class="fa-solid fa-rotate me-1"></i>Actualizar
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <div class="text-muted small">Resumen global</div>
                    <h2 class="h5 mb-0">Documentacion de colaboradores</h2>
                </div>
                <span class="badge bg-label-primary px-3 py-2" id="docsRrhhBadgeGlobal">Cargando...</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Colaboradores</div>
                        <div class="h3 mb-0" id="docsRrhhTotalColaboradores">0</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Documentos cargados</div>
                        <div class="h3 text-success mb-0" id="docsRrhhCargadosGlobal">0</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Documentos faltantes</div>
                        <div class="h3 text-warning mb-0" id="docsRrhhFaltantesGlobal">0</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">% global</div>
                        <div class="h3 text-primary mb-0" id="docsRrhhPorcentajeGlobal">0%</div>
                    </div>
                </div>
            </div>

            <div class="progress" style="height: 12px;">
                <div class="progress-bar bg-success" id="docsRrhhProgressGlobal" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span>Mostrar</span>
                    <select class="form-select form-select-sm" id="docsRrhhPageSize" style="width: 90px;">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>registros</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm" id="docsRrhhFiltro" style="width: 170px;">
                        <option value="">Todos</option>
                        <option value="faltantes">Con faltantes</option>
                        <option value="completos">Completos</option>
                    </select>
                    <label class="mb-0" for="docsRrhhBuscar">Buscar:</label>
                    <input type="search" class="form-control form-control-sm" id="docsRrhhBuscar" style="width: 220px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Departamento / puesto</th>
                            <th class="text-center">Documentos</th>
                            <th>% local</th>
                            <th>Faltantes</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="docsRrhhTablaBody">
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <span class="spinner-border spinner-border-sm me-2"></span>Cargando expedientes...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                <div class="text-muted" id="docsRrhhInfoPaginacion">Mostrando 0 registros</div>
                <nav aria-label="Paginacion expedientes RR.HH.">
                    <ul class="pagination docs-rrhh-pagination gap-2 mb-0" id="docsRrhhPaginacion"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="docsRrhhImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Importar documentos RR.HH.</h5>
                    <div class="text-muted small" id="docsRrhhImportSeleccionResumen">No se han seleccionado archivos.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="file" class="d-none" id="docsRrhhImportInputArchivos" accept=".pdf,.zip,application/pdf,application/zip" multiple>
                <input type="file" class="d-none" id="docsRrhhImportInputCarpeta" webkitdirectory directory multiple>

                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <button type="button" class="btn btn-outline-primary" id="btnDocsRrhhImportArchivos">
                        <i class="fa-solid fa-file-zipper me-1"></i>Elegir ZIP/PDF
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnDocsRrhhImportCarpeta">
                        <i class="fa-solid fa-folder-open me-1"></i>Elegir carpeta
                    </button>
                    <button type="button" class="btn btn-success" id="btnDocsRrhhImportImportar" disabled>
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>Importar listos
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnDocsRrhhImportLimpiar">
                        <i class="fa-solid fa-eraser me-1"></i>Limpiar
                    </button>
                </div>

                <div id="docsRrhhImportResumen" class="d-flex flex-wrap gap-2 mb-3"></div>
                <div class="table-responsive" style="max-height: 52vh;">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Estado</th>
                                <th>Persona detectada</th>
                                <th>Tipo de documento</th>
                                <th>Archivo</th>
                                <th>Detalle</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="docsRrhhImportTabla">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Selecciona archivos o una carpeta para analizarlos automaticamente.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="docsRrhhImportPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="docsRrhhImportPreviewTitulo">Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="px-3 py-2 border-bottom d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="docsRrhhPdfZoomOut" title="Alejar">
                    <i class="fa-solid fa-magnifying-glass-minus"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="docsRrhhPdfZoomIn" title="Acercar">
                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="docsRrhhPdfFitWidth">
                    Ajustar ancho
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="docsRrhhPdfZoomReset">
                    100%
                </button>
                <span class="small text-muted ms-auto" id="docsRrhhPdfZoomLabel">Ajustado</span>
            </div>
            <div class="modal-body p-0">
                <div id="docsRrhhImportPreviewFrame" class="docs-rrhh-pdf-preview" aria-label="Vista previa documento">
                    <div class="docs-rrhh-pdf-preview-state">Selecciona un documento para previsualizar.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="docsRrhhDetalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Detalle documental</h5>
                    <div class="text-muted small" id="docsRrhhModalSubtitulo"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small">Requeridos</div>
                            <div class="h4 mb-0" id="docsRrhhModalReq">0</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small">Cargados</div>
                            <div class="h4 text-success mb-0" id="docsRrhhModalCar">0</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small">Faltantes</div>
                            <div class="h4 text-warning mb-0" id="docsRrhhModalFal">0</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small">% local</div>
                            <div class="h4 text-primary mb-0" id="docsRrhhModalPor">0%</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th class="text-center">Archivos</th>
                                <th>Ultima carga</th>
                                <th class="text-end">Estatus</th>
                            </tr>
                        </thead>
                        <tbody id="docsRrhhModalBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let colaboradores = [];
    let paginaActual = 1;
    let importFiles = [];
    let importAnalisis = null;
    let importPreviewBuffer = null;
    let importPreviewZoom = 1;
    let importPreviewFitWidth = true;
    let importPreviewRenderId = 0;

    const els = {
        importar: document.getElementById('btnImportarDocsRrhh'),
        actualizar: document.getElementById('btnActualizarDocsRrhh'),
        badge: document.getElementById('docsRrhhBadgeGlobal'),
        totalColaboradores: document.getElementById('docsRrhhTotalColaboradores'),
        cargadosGlobal: document.getElementById('docsRrhhCargadosGlobal'),
        faltantesGlobal: document.getElementById('docsRrhhFaltantesGlobal'),
        porcentajeGlobal: document.getElementById('docsRrhhPorcentajeGlobal'),
        progressGlobal: document.getElementById('docsRrhhProgressGlobal'),
        pageSize: document.getElementById('docsRrhhPageSize'),
        filtro: document.getElementById('docsRrhhFiltro'),
        buscar: document.getElementById('docsRrhhBuscar'),
        body: document.getElementById('docsRrhhTablaBody'),
        info: document.getElementById('docsRrhhInfoPaginacion'),
        paginacion: document.getElementById('docsRrhhPaginacion'),
        modal: document.getElementById('docsRrhhDetalleModal'),
        modalSubtitulo: document.getElementById('docsRrhhModalSubtitulo'),
        modalReq: document.getElementById('docsRrhhModalReq'),
        modalCar: document.getElementById('docsRrhhModalCar'),
        modalFal: document.getElementById('docsRrhhModalFal'),
        modalPor: document.getElementById('docsRrhhModalPor'),
        modalBody: document.getElementById('docsRrhhModalBody'),
        importModal: document.getElementById('docsRrhhImportModal'),
        importInputArchivos: document.getElementById('docsRrhhImportInputArchivos'),
        importInputCarpeta: document.getElementById('docsRrhhImportInputCarpeta'),
        importBtnArchivos: document.getElementById('btnDocsRrhhImportArchivos'),
        importBtnCarpeta: document.getElementById('btnDocsRrhhImportCarpeta'),
        importBtnImportar: document.getElementById('btnDocsRrhhImportImportar'),
        importBtnLimpiar: document.getElementById('btnDocsRrhhImportLimpiar'),
        importSeleccionResumen: document.getElementById('docsRrhhImportSeleccionResumen'),
        importResumen: document.getElementById('docsRrhhImportResumen'),
        importTabla: document.getElementById('docsRrhhImportTabla'),
        importPreviewModal: document.getElementById('docsRrhhImportPreviewModal'),
        importPreviewFrame: document.getElementById('docsRrhhImportPreviewFrame'),
        importPreviewTitulo: document.getElementById('docsRrhhImportPreviewTitulo'),
        pdfZoomOut: document.getElementById('docsRrhhPdfZoomOut'),
        pdfZoomIn: document.getElementById('docsRrhhPdfZoomIn'),
        pdfFitWidth: document.getElementById('docsRrhhPdfFitWidth'),
        pdfZoomReset: document.getElementById('docsRrhhPdfZoomReset'),
        pdfZoomLabel: document.getElementById('docsRrhhPdfZoomLabel')
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function iniciales(nombre) {
        const partes = String(nombre || 'CH').trim().split(/\s+/).filter(Boolean);
        return (partes.slice(0, 2).map(p => p.charAt(0)).join('') || 'CH').toUpperCase();
    }

    function importFormData() {
        const fd = new FormData();
        importFiles.forEach(file => {
            fd.append('archivos[]', file, file.name);
            fd.append('rutas_relativas[]', file.webkitRelativePath || file.name);
        });
        (importAnalisis?.items || []).forEach(item => {
            if (item.documento_manual && Number(item.id_documento || 0) > 0) {
                fd.append(`documentos_manual[${Number(item.source_index || 0)}]`, Number(item.id_documento || 0));
            }
        });
        return fd;
    }

    function importRenderResumen(resumen) {
        if (!els.importResumen) return;
        if (!resumen) {
            els.importResumen.innerHTML = '';
            return;
        }
        const chips = [
            ['Total', resumen.total || 0, 'bg-dark'],
            ['Listos', resumen.listo || 0, 'bg-success'],
            ['Importados', resumen.importado || 0, 'bg-primary'],
            ['Ambiguos', resumen.persona_ambigua || 0, 'bg-warning text-dark'],
            ['Sin persona', resumen.persona_no_encontrada || 0, 'bg-danger'],
            ['Sin tipo', resumen.documento_no_reconocido || 0, 'bg-secondary'],
            ['Ya existe', resumen.ya_existe || 0, 'bg-info text-dark'],
            ['Duplicados', resumen.duplicado_lote || 0, 'bg-warning text-dark'],
            ['Omitidos', resumen.omitido || 0, 'bg-light text-dark border'],
            ['Errores', resumen.error || 0, 'bg-danger']
        ];
        els.importResumen.innerHTML = chips
            .filter(([, valor], index) => index === 0 || Number(valor) > 0)
            .map(([label, valor, cls]) => `<span class="badge ${cls}">${escapeHtml(label)}: ${escapeHtml(valor)}</span>`)
            .join('');
    }

    function importBadge(estado, item = null) {
        if (item && item.documento_otros_automatico) {
            return '<span class="badge bg-secondary">Sin tipo</span>';
        }
        const mapa = {
            listo: ['bg-success', 'Listo'],
            importado: ['bg-primary', 'Importado'],
            persona_no_encontrada: ['bg-danger', 'Sin persona'],
            persona_ambigua: ['bg-warning text-dark', 'Ambiguo'],
            documento_no_reconocido: ['bg-secondary', 'Sin tipo'],
            ya_existe: ['bg-info text-dark', 'Ya existe'],
            duplicado_lote: ['bg-warning text-dark', 'Duplicado'],
            omitido: ['bg-light text-dark border', 'Omitido'],
            error: ['bg-danger', 'Error']
        };
        const cfg = mapa[estado] || ['bg-secondary', estado || 'Pendiente'];
        return `<span class="badge ${cfg[0]}">${escapeHtml(cfg[1])}</span>`;
    }

    function importRenderTabla(items) {
        if (!els.importTabla) return;
        if (!items || !items.length) {
            els.importTabla.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Selecciona archivos o una carpeta para analizarlos automaticamente.</td></tr>';
            return;
        }
        const catalogo = Array.isArray(importAnalisis?.catalogo) ? importAnalisis.catalogo : [];
        let personaAnterior = '';
        els.importTabla.innerHTML = items.map(item => {
            const nombrePersona = escapeHtml(item.persona || '');
            const numeroEmpleado = escapeHtml(item.numero_empleado || '');
            const carpetaPersona = escapeHtml(item.carpeta_persona || 'N/A');
            const personaKey = item.id_persona ? `id:${item.id_persona}` : `folder:${item.carpeta_persona || ''}`;
            const separarPersona = personaAnterior !== '' && personaKey !== personaAnterior;
            personaAnterior = personaKey;
            const persona = item.persona
                ? `${nombrePersona} ${numeroEmpleado ? `(No. ${numeroEmpleado})` : ''}<br><small class="text-muted">Score ${escapeHtml(item.score_persona || 0)}%</small>`
                : `<span class="text-muted">${carpetaPersona}</span>`;
            const idDocumento = Number(item.id_documento || 0);
            const opciones = [
                `<option value="">Seleccione tipo</option>`,
                ...catalogo.map(doc => {
                    const selected = Number(doc.id || 0) === idDocumento ? 'selected' : '';
                    return `<option value="${Number(doc.id || 0)}" ${selected}>${escapeHtml(doc.nombre || '')}</option>`;
                })
            ].join('');
            const tipo = `
                <select class="form-select form-select-sm" data-import-doc-type="${Number(item.source_index || 0)}">
                    ${opciones}
                </select>
                ${item.documento_manual ? '<div class="text-primary small mt-1">Seleccion manual</div>' : ''}
            `;
            return `
                <tr class="${separarPersona ? 'docs-rrhh-import-person-separator' : ''}">
                    <td>${importBadge(item.estado, item)}</td>
                    <td>${persona}</td>
                    <td>${tipo}</td>
                    <td><span class="text-break">${escapeHtml(item.ruta || item.archivo || '')}</span></td>
                    <td>${escapeHtml(item.razon || '')}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-import-preview="${Number(item.source_index || 0)}" title="Abrir documento">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function importSetFiles(fileList) {
        importFiles = Array.from(fileList || []);
        importAnalisis = null;
        importRenderResumen(null);
        importRenderTabla([]);
        const total = importFiles.length;
        const peso = importFiles.reduce((sum, file) => sum + (file.size || 0), 0);
        els.importSeleccionResumen.textContent = total
            ? `${total} archivo(s) seleccionado(s), ${(peso / 1024 / 1024).toFixed(1)} MB.`
            : 'No se han seleccionado archivos.';
        els.importBtnImportar.disabled = true;
        if (total > 0) {
            setTimeout(importAnalizar, 0);
        }
    }

    function importLimpiarSeleccion() {
        if (els.importInputArchivos) els.importInputArchivos.value = '';
        if (els.importInputCarpeta) els.importInputCarpeta.value = '';
        importSetFiles([]);
    }

    function importSetLoading(loading, texto) {
        [els.importBtnImportar, els.importBtnArchivos, els.importBtnCarpeta].forEach(btn => {
            if (btn) btn.disabled = !!loading;
        });
        if (!loading) {
            const listos = importAnalisis?.resumen?.listo || 0;
            els.importBtnImportar.disabled = listos <= 0;
        }
        if (texto) {
            els.importSeleccionResumen.textContent = texto;
        }
    }

    async function importEnviar(endpoint) {
        const res = await fetch(endpoint, {
            method: 'POST',
            body: importFormData(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const texto = await res.text();
            throw new Error(texto ? texto.slice(0, 250) : 'Respuesta no JSON del servidor.');
        }
        const json = await res.json();
        if (!json.success) {
            throw new Error(json.mensaje || 'La operacion no se completo.');
        }
        return json.datos || {};
    }

    function importUpdatePdfZoomLabel(label = null) {
        if (!els.pdfZoomLabel) return;
        els.pdfZoomLabel.textContent = label || `${Math.round(importPreviewZoom * 100)}%`;
    }

    async function importRenderPdfPreview(arrayBuffer = null) {
        if (arrayBuffer) {
            importPreviewBuffer = arrayBuffer;
        }
        const cont = els.importPreviewFrame;
        if (!cont || !importPreviewBuffer) return;
        const renderId = ++importPreviewRenderId;
        cont.innerHTML = '<div class="docs-rrhh-pdf-preview-state"><span class="spinner-border spinner-border-sm me-2"></span>Cargando vista previa...</div>';

        const pdfjs = window.pdfjsLib || (typeof pdfjsLib !== 'undefined' ? pdfjsLib : null);
        if (!pdfjs || typeof pdfjs.getDocument !== 'function') {
            cont.innerHTML = '<div class="docs-rrhh-pdf-preview-state">No se encontro el visor PDF del sistema.</div>';
            return;
        }

        if (pdfjs.GlobalWorkerOptions && !pdfjs.GlobalWorkerOptions.workerSrc) {
            pdfjs.GlobalWorkerOptions.workerSrc = '/assets/vendor/libs/pdf-viewer/pdf.worker.mjs';
        }

        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));

        const pdf = await pdfjs.getDocument({ data: importPreviewBuffer.slice(0) }).promise;
        if (renderId !== importPreviewRenderId) return;
        cont.innerHTML = '';
        const availableWidth = Math.max(360, cont.clientWidth - 64);
        let firstPageScale = importPreviewZoom;

        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            const page = await pdf.getPage(pageNum);
            if (renderId !== importPreviewRenderId) return;
            const baseViewport = page.getViewport({ scale: 1 });
            const scale = importPreviewFitWidth
                ? Math.max(0.65, Math.min(2.75, availableWidth / baseViewport.width))
                : importPreviewZoom;
            if (pageNum === 1) {
                firstPageScale = scale;
            }
            const viewport = page.getViewport({ scale });
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { alpha: false });
            canvas.width = Math.ceil(viewport.width);
            canvas.height = Math.ceil(viewport.height);
            canvas.style.width = Math.ceil(viewport.width) + 'px';
            canvas.style.height = Math.ceil(viewport.height) + 'px';
            cont.appendChild(canvas);
            await page.render({ canvasContext: ctx, viewport }).promise;
            if (pageNum === 1) {
                importUpdatePdfZoomLabel(importPreviewFitWidth ? `Ajustado (${Math.round(firstPageScale * 100)}%)` : null);
            }
            await new Promise(resolve => requestAnimationFrame(resolve));
        }
        importUpdatePdfZoomLabel(importPreviewFitWidth ? `Ajustado (${Math.round(firstPageScale * 100)}%)` : null);
    }

    async function importAbrirDocumento(sourceIndex) {
        try {
            const item = (importAnalisis?.items || []).find(row => Number(row.source_index || 0) === Number(sourceIndex || 0));
            if (els.importPreviewTitulo) {
                els.importPreviewTitulo.textContent = item?.archivo || item?.ruta || 'Documento';
            }
            importPreviewFitWidth = false;
            importPreviewZoom = 1.75;
            importPreviewBuffer = null;
            importUpdatePdfZoomLabel();
            if (els.importPreviewFrame) {
                els.importPreviewFrame.innerHTML = '<div class="docs-rrhh-pdf-preview-state"><span class="spinner-border spinner-border-sm me-2"></span>Cargando documento...</div>';
            }
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(els.importPreviewModal).show();
            } else if (window.jQuery) {
                window.jQuery(els.importPreviewModal).modal('show');
            }

            const fd = importFormData();
            fd.append('source_index', Number(sourceIndex || 0));
            const res = await fetch('/caphum/previsualizarImportacionDocumentosRrhh', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) {
                const texto = await res.text();
                throw new Error(texto || 'No se pudo abrir el documento.');
            }
            const arrayBuffer = await res.arrayBuffer();
            await importRenderPdfPreview(arrayBuffer);
        } catch (err) {
            if (els.importPreviewFrame) {
                els.importPreviewFrame.innerHTML = `<div class="docs-rrhh-pdf-preview-state text-danger">${escapeHtml(err.message || 'No se pudo abrir el documento.')}</div>`;
            }
            Swal.fire('Abrir documento', err.message || 'No se pudo abrir el documento.', 'error');
        }
    }

    async function importAnalizar() {
        if (!importFiles.length) return;
        try {
            importSetLoading(true, 'Analizando documentos...');
            importAnalisis = await importEnviar('/caphum/analizarImportacionDocumentosRrhh');
            importRenderResumen(importAnalisis.resumen);
            importRenderTabla(importAnalisis.items || []);
            const listos = importAnalisis?.resumen?.listo || 0;
            els.importSeleccionResumen.textContent = `Analisis listo. ${listos} documento(s) pueden importarse.`;
            els.importBtnImportar.disabled = listos <= 0;
        } catch (err) {
            Swal.fire('Importar documentos', err.message || 'No se pudo analizar la seleccion.', 'error');
        } finally {
            importSetLoading(false);
        }
    }

    async function importImportar() {
        const listos = importAnalisis?.resumen?.listo || 0;
        if (!listos) return;
        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Importar documentos',
            text: `Se importaran ${listos} documento(s) listos. Los ambiguos, duplicados o ya existentes se omitiran.`,
            showCancelButton: true,
            confirmButtonText: 'Importar',
            cancelButtonText: 'Cancelar'
        });
        if (!confirm.isConfirmed) return;

        try {
            importSetLoading(true, 'Importando documentos...');
            const resultado = await importEnviar('/caphum/importarDocumentosRrhh');
            importAnalisis = resultado;
            importRenderResumen(resultado.resumen);
            importRenderTabla(resultado.items || []);
            els.importSeleccionResumen.textContent = `Importacion finalizada. ${Number(resultado.importados || 0)} documento(s) importado(s).`;
            await cargarResumen();
            Swal.fire('Importar documentos', `Se importaron ${Number(resultado.importados || 0)} documento(s).`, 'success');
        } catch (err) {
            Swal.fire('Importar documentos', err.message || 'No se pudo importar la seleccion.', 'error');
        } finally {
            importSetLoading(false);
        }
    }

    function getFiltrados() {
        const q = (els.buscar.value || '').trim().toLowerCase();
        const filtro = els.filtro.value || '';
        return colaboradores.filter(col => {
            const texto = [
                col.numero_empleado,
                col.nombre_completo,
                col.correo,
                col.departamentos,
                col.puestos
            ].join(' ').toLowerCase();
            const coincideTexto = !q || texto.includes(q);
            const faltantes = Number(col.total_faltantes || 0);
            const coincideFiltro = !filtro
                || (filtro === 'faltantes' && faltantes > 0)
                || (filtro === 'completos' && faltantes === 0);
            return coincideTexto && coincideFiltro;
        });
    }

    function paginasVisibles(totalPaginas) {
        if (totalPaginas <= 7) {
            return Array.from({ length: totalPaginas }, (_, i) => i + 1);
        }

        const paginas = new Set([1, totalPaginas]);
        if (paginaActual <= 4) {
            [2, 3, 4, 5].forEach(p => paginas.add(p));
        } else if (paginaActual >= totalPaginas - 3) {
            [totalPaginas - 4, totalPaginas - 3, totalPaginas - 2, totalPaginas - 1].forEach(p => paginas.add(p));
        } else {
            [paginaActual - 2, paginaActual - 1, paginaActual, paginaActual + 1, paginaActual + 2].forEach(p => paginas.add(p));
        }

        const ordenadas = Array.from(paginas)
            .filter(p => p >= 1 && p <= totalPaginas)
            .sort((a, b) => a - b);
        const salida = [];
        ordenadas.forEach((p, index) => {
            const anterior = ordenadas[index - 1];
            if (index > 0 && p - anterior > 1) {
                salida.push('...');
            }
            salida.push(p);
        });
        return salida;
    }

    function renderPaginacion(totalPaginas) {
        if (!els.paginacion) return;
        const boton = (label, pagina, disabled, active = false) => `
            <li class="page-item ${active ? 'active' : ''} ${disabled ? 'disabled' : ''}">
                <a
                    class="page-link"
                    href="#"
                    ${disabled ? 'tabindex="-1" aria-disabled="true"' : `data-docs-page="${Number(pagina)}"`}
                >${label}</a>
            </li>
        `;

        const partes = [
            boton('&laquo;', 1, paginaActual <= 1),
            boton('&lsaquo;', paginaActual - 1, paginaActual <= 1)
        ];

        paginasVisibles(totalPaginas).forEach(p => {
            if (p === '...') {
                partes.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                return;
            }
            partes.push(boton(p, p, false, p === paginaActual));
        });

        partes.push(boton('&rsaquo;', paginaActual + 1, paginaActual >= totalPaginas));
        partes.push(boton('&raquo;', totalPaginas, paginaActual >= totalPaginas));
        els.paginacion.innerHTML = partes.join('');
    }

    function renderTabla() {
        const filtrados = getFiltrados();
        const pageSize = Number(els.pageSize.value || 10);
        const totalPaginas = Math.max(1, Math.ceil(filtrados.length / pageSize));
        paginaActual = Math.min(Math.max(1, paginaActual), totalPaginas);
        const inicio = (paginaActual - 1) * pageSize;
        const pagina = filtrados.slice(inicio, inicio + pageSize);

        if (!pagina.length) {
            els.body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay colaboradores para mostrar.</td></tr>';
        } else {
            els.body.innerHTML = pagina.map(col => {
                const pct = Number(col.porcentaje_local || 0);
                const faltantes = Number(col.total_faltantes || 0);
                const badge = faltantes > 0
                    ? `<span class="badge bg-warning text-dark">${faltantes} faltante(s)</span>`
                    : '<span class="badge bg-success">Completo</span>';
                const faltantesResumen = Array.isArray(col.faltantes_resumen) && col.faltantes_resumen.length
                    ? col.faltantes_resumen.map(escapeHtml).join(', ')
                    : 'Sin faltantes';
                return `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="btn btn-primary btn-sm rounded-3 disabled">${escapeHtml(iniciales(col.nombre_completo))}</span>
                                <div>
                                    <div class="fw-semibold"># ${escapeHtml(col.numero_empleado || '')}</div>
                                    <div class="fw-semibold text-uppercase">${escapeHtml(col.nombre_completo || 'Colaborador')}</div>
                                    ${col.correo ? `<div class="text-muted small">${escapeHtml(col.correo)}</div>` : ''}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">${escapeHtml(col.departamentos || 'Sin departamento')}</div>
                            <div class="text-muted small">${escapeHtml(col.puestos || 'Sin puesto')}</div>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold">${Number(col.total_cargados || 0)} / ${Number(col.total_requeridos || 0)}</div>
                            ${badge}
                        </td>
                        <td style="min-width: 170px;">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Avance</span>
                                <strong>${pct}%</strong>
                            </div>
                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-success" style="width: ${Math.min(100, Math.max(0, pct))}%;"></div>
                            </div>
                        </td>
                        <td>
                            <div class="small text-muted">${escapeHtml(faltantesResumen)}</div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" data-docs-detalle="${Number(col.id_persona || 0)}" title="Ver detalle">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        const hasta = filtrados.length ? Math.min(inicio + pageSize, filtrados.length) : 0;
        els.info.textContent = `Mostrando de ${filtrados.length ? inicio + 1 : 0} a ${hasta} de ${filtrados.length} registros`;
        renderPaginacion(totalPaginas);
    }

    function aplicarResumen(data) {
        const metricas = data.metricas || {};
        colaboradores = Array.isArray(data.colaboradores) ? data.colaboradores : [];

        const pct = Number(metricas.porcentaje_global || 0);
        els.totalColaboradores.textContent = Number(metricas.total_colaboradores || 0);
        els.cargadosGlobal.textContent = Number(metricas.total_cargados_global || 0);
        els.faltantesGlobal.textContent = Number(metricas.total_faltantes_global || 0);
        els.porcentajeGlobal.textContent = `${pct}%`;
        els.progressGlobal.style.width = `${Math.min(100, Math.max(0, pct))}%`;
        els.progressGlobal.setAttribute('aria-valuenow', String(pct));
        els.badge.textContent = `${Number(metricas.colaboradores_con_faltantes || 0)} con faltantes`;

        paginaActual = 1;
        renderTabla();
    }

    function abrirDetalle(idPersona) {
        const col = colaboradores.find(item => Number(item.id_persona || 0) === Number(idPersona));
        if (!col) return;

        els.modalSubtitulo.textContent = `# ${col.numero_empleado || ''} - ${col.nombre_completo || 'Colaborador'}`;
        els.modalReq.textContent = Number(col.total_requeridos || 0);
        els.modalCar.textContent = Number(col.total_cargados || 0);
        els.modalFal.textContent = Number(col.total_faltantes || 0);
        els.modalPor.textContent = `${Number(col.porcentaje_local || 0)}%`;
        const docs = Array.isArray(col.documentos) ? col.documentos : [];
        els.modalBody.innerHTML = docs.map(doc => {
            const cargado = !!doc.cargado;
            return `
                <tr>
                    <td>
                        <div class="fw-semibold">${escapeHtml(doc.nombre)}</div>
                    </td>
                    <td class="text-center">${Number(doc.total_archivos || 0)}</td>
                    <td>${doc.ultima_fecha ? escapeHtml(doc.ultima_fecha) : '<span class="text-muted">Sin carga</span>'}</td>
                    <td class="text-end">
                        ${cargado ? '<span class="badge bg-success">Cargado</span>' : '<span class="badge bg-warning text-dark">Faltante</span>'}
                    </td>
                </tr>
            `;
        }).join('') || '<tr><td colspan="4" class="text-center text-muted py-4">Sin documentos para mostrar.</td></tr>';

        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(els.modal).show();
        } else if (window.jQuery) {
            window.jQuery(els.modal).modal('show');
        }
    }

    async function cargarResumen() {
        els.actualizar.disabled = true;
        els.actualizar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando';
        try {
            const res = await fetch('/caphum/getResumenDocumentosRrhh', { cache: 'no-store' });
            const json = await res.json();
            if (!json.success) throw new Error(json.mensaje || 'No se pudo cargar el resumen documental.');
            aplicarResumen(json.datos || {});
        } catch (err) {
            els.body.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${escapeHtml(err.message || 'Error al obtener resumen documental.')}</td></tr>`;
            els.info.textContent = 'Mostrando 0 registros';
            if (els.paginacion) els.paginacion.innerHTML = '';
        } finally {
            els.actualizar.disabled = false;
            els.actualizar.innerHTML = '<i class="fa-solid fa-rotate me-1"></i>Actualizar';
        }
    }

    els.buscar.addEventListener('input', function () { paginaActual = 1; renderTabla(); });
    els.filtro.addEventListener('change', function () { paginaActual = 1; renderTabla(); });
    els.pageSize.addEventListener('change', function () { paginaActual = 1; renderTabla(); });
    els.paginacion.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-docs-page]');
        if (!btn) return;
        event.preventDefault();
        paginaActual = Number(btn.getAttribute('data-docs-page') || 1);
        renderTabla();
    });
    els.actualizar.addEventListener('click', cargarResumen);
    els.importar.addEventListener('click', function () {
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(els.importModal).show();
        } else if (window.jQuery) {
            window.jQuery(els.importModal).modal('show');
        }
    });
    els.importBtnArchivos.addEventListener('click', function () { els.importInputArchivos.click(); });
    els.importBtnCarpeta.addEventListener('click', function () { els.importInputCarpeta.click(); });
    els.importInputArchivos.addEventListener('change', function () { importSetFiles(this.files); });
    els.importInputCarpeta.addEventListener('change', function () { importSetFiles(this.files); });
    els.importBtnImportar.addEventListener('click', importImportar);
    els.importBtnLimpiar.addEventListener('click', function () {
        importLimpiarSeleccion();
    });
    els.importModal.addEventListener('hidden.bs.modal', function () {
        importLimpiarSeleccion();
    });
    els.importPreviewModal.addEventListener('hidden.bs.modal', function () {
        importPreviewRenderId++;
        if (els.importPreviewFrame) {
            els.importPreviewFrame.innerHTML = '<div class="docs-rrhh-pdf-preview-state">Selecciona un documento para previsualizar.</div>';
        }
        importPreviewBuffer = null;
        importPreviewFitWidth = true;
        importPreviewZoom = 1;
        importUpdatePdfZoomLabel('Ajustado');
    });
    els.pdfZoomOut.addEventListener('click', function () {
        importPreviewFitWidth = false;
        importPreviewZoom = Math.max(0.4, importPreviewZoom - 0.25);
        importRenderPdfPreview();
    });
    els.pdfZoomIn.addEventListener('click', function () {
        importPreviewFitWidth = false;
        importPreviewZoom = Math.min(4, importPreviewZoom + 0.25);
        importRenderPdfPreview();
    });
    els.pdfFitWidth.addEventListener('click', function () {
        importPreviewFitWidth = true;
        importRenderPdfPreview();
    });
    els.pdfZoomReset.addEventListener('click', function () {
        importPreviewFitWidth = false;
        importPreviewZoom = 1;
        importRenderPdfPreview();
    });
    els.importTabla.addEventListener('change', function (event) {
        const select = event.target.closest('[data-import-doc-type]');
        if (!select || !importAnalisis) return;
        const sourceIndex = Number(select.getAttribute('data-import-doc-type') || 0);
        const item = (importAnalisis.items || []).find(row => Number(row.source_index || 0) === sourceIndex);
        if (!item) return;
        item.id_documento = Number(select.value || 0) || null;
        item.documento_manual = Number(select.value || 0) > 0;
        importAnalizar();
    });
    els.importTabla.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-import-preview]');
        if (!btn) return;
        importAbrirDocumento(btn.getAttribute('data-import-preview'));
    });
    els.body.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-docs-detalle]');
        if (btn) abrirDetalle(btn.getAttribute('data-docs-detalle'));
    });
    document.addEventListener('DOMContentLoaded', cargarResumen);
})();
</script>
