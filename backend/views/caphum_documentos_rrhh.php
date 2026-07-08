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
        .docs-rrhh-actions {
            align-items: center;
            display: inline-flex;
            flex-wrap: wrap;
            gap: 7px;
            justify-content: center;
            min-width: 90px;
        }
        .docs-rrhh-action-btn {
            align-items: center;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            padding: 0 !important;
            width: 42px;
        }
        .docs-rrhh-avatar-fallback {
            align-items: center;
            background: linear-gradient(135deg, #24324d 0%, #0d6efd 100%);
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 5px 16px rgba(30, 41, 59, 0.16);
            color: #fff;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 0.82rem;
            font-weight: 700;
            height: 46px;
            justify-content: center;
            letter-spacing: 0;
            line-height: 1;
            min-width: 46px;
            width: 46px;
        }
        .docs-rrhh-employee-code {
            align-items: center;
            color: #475569;
            display: flex;
            flex-wrap: wrap;
            font-size: 0.78rem;
            font-weight: 800;
            gap: 6px;
            line-height: 1.2;
            margin-bottom: 3px;
        }
        .docs-rrhh-code-value {
            background: #eaf1ff;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            color: #1d4ed8;
            display: inline-flex;
            font-weight: 900;
            line-height: 1;
            padding: 3px 8px;
        }
        .docs-rrhh-external-id {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 800;
            line-height: 1.25;
            margin-top: 2px;
        }
        .docs-rrhh-external-id strong {
            color: #334155;
        }
        .docs-rrhh-trayectoria-list {
            position: relative;
            padding: 0.25rem 0 0.25rem 1.35rem;
        }
        .docs-rrhh-trayectoria-list::before {
            content: "";
            position: absolute;
            left: 0.5rem;
            top: 0.75rem;
            bottom: 0.75rem;
            border-left: 2px dashed #cbd5e1;
        }
        .docs-rrhh-trayectoria-item {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.85rem;
            padding: 0.45rem 0 1rem 1rem;
        }
        .docs-rrhh-trayectoria-item::before {
            content: "";
            position: absolute;
            left: -0.04rem;
            top: 0.65rem;
            width: 0.8rem;
            height: 0.8rem;
            border-radius: 50%;
            background: #fff;
            border: 3px solid var(--docs-trayectoria-color, #2563eb);
            box-shadow: 0 0 0 3px #fff;
        }
        .docs-rrhh-trayectoria-title {
            color: #1e293b;
            font-size: 0.86rem;
            font-weight: 900;
            line-height: 1.25;
            text-transform: uppercase;
        }
        .docs-rrhh-trayectoria-actor,
        .docs-rrhh-trayectoria-detail,
        .docs-rrhh-trayectoria-date {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .docs-rrhh-trayectoria-date {
            text-align: right;
            white-space: nowrap;
        }
        .docs-rrhh-trayectoria-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 7px;
        }
        .docs-rrhh-trayectoria-chip {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            color: #475569;
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1.25;
            padding: 4px 8px;
        }
        .docs-rrhh-trayectoria-chip strong {
            color: #1e293b;
            font-weight: 900;
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
            <button type="button" class="btn btn-outline-success" id="btnImportarSueldosRrhh">
                <i class="fa-solid fa-file-excel me-1"></i>Importar sueldos
            </button>
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
                        <option value="sin_documentos">Sin documentos</option>
                        <option value="parcial">Parcial</option>
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

<div class="modal fade" id="docsRrhhSueldosModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">
                        <i class="fa-solid fa-file-excel me-2"></i>Importar sueldos
                    </h5>
                    <div class="text-muted small">El archivo debe incluir columnas CURP y SUELDO o SALARIO.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="file" class="d-none" id="docsRrhhSueldosInput" accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv">
                <div class="border rounded-3 p-3 bg-light mb-3">
                    <div class="fw-semibold mb-1">Formato esperado</div>
                    <div class="small text-muted">Encabezados aceptados: <strong>CURP</strong> y <strong>SUELDO</strong>, <strong>SUELDO BRUTO</strong>, <strong>SALARIO</strong>, <strong>SALARIO MENSUAL</strong> o <strong>SUELDO MENSUAL</strong>.</div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <button type="button" class="btn btn-outline-primary" id="btnDocsRrhhSueldosElegir">
                        <i class="fa-solid fa-folder-open me-1"></i>Elegir Excel
                    </button>
                    <button type="button" class="btn btn-success" id="btnDocsRrhhSueldosSubir" disabled>
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>Cargar sueldos
                    </button>
                </div>
                <div class="small text-muted mb-2" id="docsRrhhSueldosArchivo">No se ha seleccionado archivo.</div>
                <div id="docsRrhhSueldosResultado"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="docsRrhhImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="docsRrhhImportTitulo">Importar documentos RR.HH.</h5>
                    <div class="text-muted small" id="docsRrhhImportSeleccionResumen">No se han seleccionado archivos.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="file" class="d-none" id="docsRrhhImportInputArchivos" accept=".pdf,.zip,.fad,application/pdf,application/zip,application/octet-stream" multiple>
                <input type="file" class="d-none" id="docsRrhhImportInputCarpeta" webkitdirectory directory multiple>

                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <button type="button" class="btn btn-outline-primary" id="btnDocsRrhhImportArchivos">
                        <i class="fa-solid fa-file-zipper me-1"></i>Elegir ZIP/PDF/FAD
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

<div class="modal fade" id="docsRrhhTrayectoriaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1"><i class="fa-solid fa-route me-1"></i>Trayectoria laboral</h5>
                    <div class="text-muted small" id="docsRrhhTrayectoriaSubtitulo"></div>
                </div>
                <span class="badge bg-light text-dark ms-auto me-3" id="docsRrhhTrayectoriaTotal">0 movimientos</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="docsRrhhTrayectoriaBody" class="docs-rrhh-trayectoria-list">
                    <div class="text-muted small py-3">Selecciona un colaborador para consultar su trayectoria.</div>
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
    let importPreparandoArchivosDesde = 0;
    let importPersonaObjetivo = null;
    let sueldosArchivo = null;
    const IMPORT_MAX_FILES_PER_REQUEST = 10;
    const IMPORT_MAX_BYTES_PER_REQUEST = 30 * 1024 * 1024;
    const IMPORT_MAX_ZIP_BYTES_PER_REQUEST = 30 * 1024 * 1024;

    const els = {
        importarSueldos: document.getElementById('btnImportarSueldosRrhh'),
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
        importTitulo: document.getElementById('docsRrhhImportTitulo'),
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
        pdfZoomLabel: document.getElementById('docsRrhhPdfZoomLabel'),
        trayectoriaModal: document.getElementById('docsRrhhTrayectoriaModal'),
        trayectoriaSubtitulo: document.getElementById('docsRrhhTrayectoriaSubtitulo'),
        trayectoriaTotal: document.getElementById('docsRrhhTrayectoriaTotal'),
        trayectoriaBody: document.getElementById('docsRrhhTrayectoriaBody'),
        sueldosModal: document.getElementById('docsRrhhSueldosModal'),
        sueldosInput: document.getElementById('docsRrhhSueldosInput'),
        sueldosBtnElegir: document.getElementById('btnDocsRrhhSueldosElegir'),
        sueldosBtnSubir: document.getElementById('btnDocsRrhhSueldosSubir'),
        sueldosArchivo: document.getElementById('docsRrhhSueldosArchivo'),
        sueldosResultado: document.getElementById('docsRrhhSueldosResultado')
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

    function abrirImportarSueldos() {
        sueldosArchivo = null;
        if (els.sueldosInput) els.sueldosInput.value = '';
        if (els.sueldosArchivo) els.sueldosArchivo.textContent = 'No se ha seleccionado archivo.';
        if (els.sueldosResultado) els.sueldosResultado.innerHTML = '';
        if (els.sueldosBtnSubir) els.sueldosBtnSubir.disabled = true;
        bootstrap.Modal.getOrCreateInstance(els.sueldosModal).show();
    }

    function renderResultadoSueldos(datos) {
        if (!els.sueldosResultado) return;
        const errores = Array.isArray(datos?.errores) ? datos.errores : [];
        const erroresHtml = errores.length
            ? `<div class="mt-3">
                <div class="fw-semibold mb-2">Filas omitidas</div>
                <div class="table-responsive" style="max-height: 240px;">
                  <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Fila</th><th>CURP</th><th>Motivo</th></tr></thead>
                    <tbody>${errores.slice(0, 50).map(e => `
                      <tr>
                        <td>${escapeHtml(e.fila || '')}</td>
                        <td>${escapeHtml(e.curp || '')}</td>
                        <td>${escapeHtml(e.motivo || '')}</td>
                      </tr>`).join('')}</tbody>
                  </table>
                </div>
                ${errores.length > 50 ? `<div class="small text-muted mt-2">Se muestran 50 de ${escapeHtml(errores.length)} filas omitidas.</div>` : ''}
              </div>`
            : '';

        els.sueldosResultado.innerHTML = `
            <div class="row g-2">
              <div class="col-4"><div class="border rounded-3 p-2"><div class="small text-muted">Filas</div><div class="h5 mb-0">${escapeHtml(datos?.total || 0)}</div></div></div>
              <div class="col-4"><div class="border rounded-3 p-2"><div class="small text-muted">Actualizados</div><div class="h5 text-success mb-0">${escapeHtml(datos?.actualizados || 0)}</div></div></div>
              <div class="col-4"><div class="border rounded-3 p-2"><div class="small text-muted">Omitidos</div><div class="h5 text-warning mb-0">${escapeHtml(datos?.omitidos || 0)}</div></div></div>
            </div>
            ${erroresHtml}
        `;
    }

    async function subirSueldosExcel() {
        if (!sueldosArchivo) {
            Swal.fire('Importar sueldos', 'Selecciona un archivo Excel o CSV.', 'warning');
            return;
        }
        const fd = new FormData();
        fd.append('archivo', sueldosArchivo, sueldosArchivo.name);
        const htmlOriginal = els.sueldosBtnSubir?.innerHTML || '';
        if (els.sueldosBtnSubir) {
            els.sueldosBtnSubir.disabled = true;
            els.sueldosBtnSubir.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cargando...';
        }
        try {
            const res = await fetch('/CapHum/importarSueldosRrhhExcel', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.mensaje || data.error || 'No se pudo importar el archivo.');
            }
            renderResultadoSueldos(data.datos || {});
            Swal.fire('Sueldos importados', data.mensaje || 'Carga finalizada.', 'success');
        } catch (error) {
            Swal.fire('Importar sueldos', error.message || 'No se pudo importar el archivo.', 'error');
        } finally {
            if (els.sueldosBtnSubir) {
                els.sueldosBtnSubir.disabled = !sueldosArchivo;
                els.sueldosBtnSubir.innerHTML = htmlOriginal;
            }
        }
    }

    function importFormData(options = {}) {
        const fd = new FormData();
        const batchId = String(importAnalisis?.batch_id || '').trim();
        const usarBatch = options.usarBatch !== false;
        const cacheLote = options.cacheLote !== false;
        const files = Array.isArray(options.files) ? options.files : importFiles;
        const sourceOffset = Number(options.sourceOffset || 0);
        const manualesGlobales = options.manualesGlobales || null;
        fd.append('cache_lote', cacheLote ? '1' : '0');
        if (importPersonaObjetivo && Number(importPersonaObjetivo.id_persona || 0) > 0) {
            fd.append('id_persona', Number(importPersonaObjetivo.id_persona || 0));
        }
        if (batchId && usarBatch) {
            fd.append('batch_id', batchId);
        } else {
            files.forEach(file => {
                fd.append('archivos[]', file, file.name);
                fd.append('rutas_relativas[]', file.webkitRelativePath || file.name);
            });
        }
        if (manualesGlobales instanceof Map) {
            files.forEach((file, localIndex) => {
                const globalIndex = Number(file.__rrhhGlobalIndex ?? (sourceOffset + localIndex));
                const idDocumento = Number(manualesGlobales.get(globalIndex) || 0);
                if (idDocumento > 0) {
                    fd.append(`documentos_manual[${localIndex}]`, idDocumento);
                }
            });
        } else {
            (importAnalisis?.items || []).forEach(item => {
                if (item.documento_manual && Number(item.id_documento || 0) > 0) {
                    const localIndex = Math.max(0, Number(item.source_index || 0) - sourceOffset);
                    fd.append(`documentos_manual[${localIndex}]`, Number(item.id_documento || 0));
                }
            });
        }
        return fd;
    }

    function importFormatoBytes(bytes) {
        const valor = Number(bytes || 0);
        if (valor <= 0) return '0 MB';
        if (valor >= 1024 * 1024) return `${(valor / 1024 / 1024).toFixed(1)} MB`;
        return `${Math.max(1, Math.round(valor / 1024))} KB`;
    }

    function importDescripcionLote(batch, index, total) {
        const inicio = Number(batch?.sourceOffset || 0) + 1;
        const fin = inicio + Number(batch?.files?.length || 0) - 1;
        const nombres = Array.from(batch?.files || []).slice(0, 3).map(file => file.name).filter(Boolean);
        const extra = Number(batch?.files?.length || 0) > 3 ? ` y ${Number(batch.files.length) - 3} mas` : '';
        return `lote ${index + 1} de ${total}, archivos ${inicio}-${fin}, ${importFormatoBytes(batch?.bytes || 0)}${nombres.length ? ` (${nombres.join(', ')}${extra})` : ''}`;
    }

    function importMensajeErrorOperacion(err, contexto = {}) {
        const partes = [];
        const fase = contexto.fase || 'operación';
        const codigo = String(err?.codigo || '').trim();
        const sugerencias = {
            post_max_size_superado: 'La carga enviada supera el límite permitido por el servidor. Selecciona una carpeta para procesar en lotes más pequeños o divide los archivos.',
            lote_temporal_no_disponible: 'El lote temporal expiró o el navegador liberó los archivos. Vuelve a seleccionar la carpeta o los archivos y reintenta.',
            sin_archivos_validos: 'El servidor no recibió PDF, FAD o ZIP válidos. Revisa que la carpeta no esté vacía y que los archivos tengan extensión permitida.',
            respuesta_no_json: 'El servidor devolvió una respuesta inesperada. Normalmente pasa por un error PHP, timeout o rechazo del servidor antes de llegar al controlador.',
            conexion_fallida: 'No se pudo comunicar con el servidor. Revisa conexión, sesión activa y que el servicio esté disponible.'
        };
        partes.push(`No se pudo completar ${fase}.`);
        if (contexto.batch) {
            partes.push(`Dónde falló: ${importDescripcionLote(contexto.batch, contexto.index || 0, contexto.total || 1)}.`);
        }
        if (contexto.endpoint) {
            partes.push(`Proceso técnico: ${contexto.endpoint}.`);
        }
        if (codigo) {
            partes.push(`Código: ${codigo}.`);
        }
        partes.push(`Motivo detectado: ${err?.message || 'El servidor no devolvió detalle del error.'}`);
        partes.push(`Qué hacer: ${sugerencias[codigo] || 'Revisa el archivo o lote indicado; si se repite, comparte este detalle técnico para ubicar el punto exacto.'}`);
        return partes.join('\n');
    }

    function importResumenVacio() {
        return {
            total: 0,
            listo: 0,
            importado: 0,
            persona_no_encontrada: 0,
            persona_ambigua: 0,
            persona_no_coincide: 0,
            documento_no_reconocido: 0,
            ya_existe: 0,
            duplicado_lote: 0,
            omitido: 0,
            error: 0,
            documento_sin_permiso: 0
        };
    }

    function importSumarResumen(destino, fuente) {
        const out = destino || importResumenVacio();
        Object.keys(importResumenVacio()).forEach(key => {
            out[key] = Number(out[key] || 0) + Number(fuente?.[key] || 0);
        });
        return out;
    }

    function importNormalizarItemsBatch(items, sourceOffset) {
        return Array.from(items || []).map(item => ({
            ...item,
            source_index: Number(item.source_index || 0) + Number(sourceOffset || 0)
        }));
    }

    function importManualesGlobales() {
        const manuales = new Map();
        (importAnalisis?.items || []).forEach(item => {
            const sourceIndex = Number(item.source_index || 0);
            const idDocumento = Number(item.id_documento || 0);
            if (item.documento_manual && idDocumento > 0) {
                manuales.set(sourceIndex, idDocumento);
            }
        });
        return manuales;
    }

    function importCrearBatches(files) {
        const batches = [];
        let actual = [];
        let bytes = 0;
        Array.from(files || []).forEach((file, index) => {
            file.__rrhhGlobalIndex = index;
            const size = Number(file.size || 0);
            const debeCerrar = actual.length > 0
                && (actual.length >= IMPORT_MAX_FILES_PER_REQUEST || (bytes + size) > IMPORT_MAX_BYTES_PER_REQUEST);
            if (debeCerrar) {
                batches.push({ files: actual, sourceOffset: Number(actual[0].__rrhhGlobalIndex || 0), bytes });
                actual = [];
                bytes = 0;
            }
            actual.push(file);
            bytes += size;
        });
        if (actual.length) {
            batches.push({ files: actual, sourceOffset: Number(actual[0].__rrhhGlobalIndex || 0), bytes });
        }
        return batches;
    }

    function importTieneZipGrande(files) {
        return Array.from(files || []).some(file => importEsZip(file) && Number(file.size || 0) > IMPORT_MAX_ZIP_BYTES_PER_REQUEST);
    }

    function importPrimerArchivoGrande(files) {
        return Array.from(files || []).find(file => !importEsZip(file) && Number(file.size || 0) > IMPORT_MAX_BYTES_PER_REQUEST) || null;
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
            ['Revisar persona', resumen.persona_ambigua || 0, 'bg-warning text-dark'],
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
            persona_ambigua: ['bg-warning text-dark', 'Revisar persona'],
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
            const estatusPersonaRaw = String(item.estatus_persona || '').trim();
            const esPersonaBaja = estatusPersonaRaw.toLowerCase() === 'baja' || item.persona_activa === false;
            const fechaBaja = escapeHtml(item.fecha_baja || '');
            const estatusPersona = item.persona
                ? `<br><span class="badge ${esPersonaBaja ? 'bg-danger' : 'bg-success'}">${escapeHtml(esPersonaBaja ? 'Baja' : (estatusPersonaRaw || 'Activo'))}</span>${esPersonaBaja && fechaBaja ? ` <small class="text-muted">${fechaBaja}</small>` : ''}`
                : '';
            const carpetaPersona = escapeHtml(item.carpeta_persona || 'N/A');
            const personaKey = item.id_persona ? `id:${item.id_persona}` : `folder:${item.carpeta_persona || ''}`;
            const separarPersona = personaAnterior !== '' && personaKey !== personaAnterior;
            personaAnterior = personaKey;
            const persona = item.persona
                ? `${nombrePersona} ${numeroEmpleado ? `(No. ${numeroEmpleado})` : ''}${estatusPersona}<br><small class="text-muted">Score ${escapeHtml(item.score_persona || 0)}%</small>`
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
            const extensionArchivo = String(item.extension || item.archivo || item.ruta || '').toLowerCase();
            const esFad = extensionArchivo.endsWith('.fad') || extensionArchivo === 'fad';
            const botonPreview = esFad
                ? `<button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Archivo .FAD sin vista previa PDF">
                        <i class="fa-solid fa-file-shield"></i>
                   </button>`
                : `<button type="button" class="btn btn-sm btn-outline-primary" data-import-preview="${Number(item.source_index || 0)}" title="Abrir documento">
                        <i class="fa-solid fa-eye"></i>
                   </button>`;
            return `
                <tr class="${separarPersona ? 'docs-rrhh-import-person-separator' : ''}">
                    <td>${importBadge(item.estado, item)}</td>
                    <td>${persona}</td>
                    <td>${tipo}</td>
                    <td><span class="text-break">${escapeHtml(item.ruta || item.archivo || '')}</span></td>
                    <td>${escapeHtml(item.razon || '')}</td>
                    <td class="text-center">
                        ${botonPreview}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function importEsZip(file) {
        const texto = [
            file?.name || '',
            file?.webkitRelativePath || '',
            file?.type || ''
        ].join(' ');
        return /\.zip\b/i.test(texto) || /zip/i.test(String(file?.type || ''));
    }

    function importSeleccionTieneZip() {
        return importFiles.some(importEsZip);
    }

    function importMostrarPreparandoArchivos() {
        importPreparandoArchivosDesde = Date.now();
        Swal.fire({
            title: 'Preparando archivos',
            text: 'Estamos descomprimiendo y preparando los documentos. Por favor espere.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    async function importCerrarPreparandoArchivos() {
        if (!importPreparandoArchivosDesde) return;
        const restante = Math.max(0, 800 - (Date.now() - importPreparandoArchivosDesde));
        if (restante > 0) {
            await new Promise(resolve => setTimeout(resolve, restante));
        }
        const titulo = Swal.getTitle ? String(Swal.getTitle()?.textContent || '') : '';
        if (Swal.isVisible() && titulo === 'Preparando archivos') {
            Swal.close();
        }
        importPreparandoArchivosDesde = 0;
    }

    function importSetFiles(fileList) {
        importFiles = Array.from(fileList || []);
        importAnalisis = null;
        importRenderResumen(null);
        importRenderTabla([]);
        const total = importFiles.length;
        const peso = importFiles.reduce((sum, file) => sum + (file.size || 0), 0);
        const pesoMb = peso / 1024 / 1024;
        els.importSeleccionResumen.textContent = total
            ? `${total} archivo(s) seleccionado(s), ${pesoMb.toFixed(1)} MB.`
            : 'No se han seleccionado archivos.';
        els.importBtnImportar.disabled = true;
        if (total > 0) {
            const limiteMb = Math.floor(IMPORT_MAX_BYTES_PER_REQUEST / 1024 / 1024);
            if (importTieneZipGrande(importFiles)) {
                els.importSeleccionResumen.textContent = `ZIP demasiado grande para produccion. Descomprimelo y usa Elegir carpeta; se enviara en lotes de ${limiteMb} MB.`;
                els.importTabla.innerHTML = `<tr><td colspan="6" class="text-center text-warning py-4">El servidor rechaza ZIP grandes antes de que el sistema pueda analizarlos. Descomprime el ZIP y selecciona la carpeta.</td></tr>`;
                Swal.fire(
                    'ZIP demasiado grande',
                    `Este ZIP supera el tamano permitido para una sola carga. Descomprime el archivo y usa "Elegir carpeta"; el sistema lo analizara por lotes automaticamente.`,
                    'warning'
                );
                return;
            }
            const archivoGrande = importPrimerArchivoGrande(importFiles);
            if (archivoGrande) {
                els.importSeleccionResumen.textContent = `El archivo ${archivoGrande.name} pesa mas de ${limiteMb} MB.`;
                els.importTabla.innerHTML = `<tr><td colspan="6" class="text-center text-warning py-4">Ese archivo supera el limite por solicitud de produccion. Comprimelo o dividelo antes de subirlo.</td></tr>`;
                Swal.fire('Archivo demasiado grande', `El archivo "${archivoGrande.name}" supera ${limiteMb} MB.`, 'warning');
                return;
            }
            const batches = importCrearBatches(importFiles);
            if (batches.length > 1) {
                els.importSeleccionResumen.textContent = `${total} archivo(s), ${pesoMb.toFixed(1)} MB. Se analizaran en ${batches.length} lotes seguros para produccion.`;
            }
            if (importSeleccionTieneZip()) {
                importMostrarPreparandoArchivos();
            }
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

    async function importEnviar(endpoint, options = {}) {
        let res;
        try {
            res = await fetch(endpoint, {
                method: 'POST',
                body: importFormData(options),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        } catch (err) {
            const error = new Error(`No se pudo conectar con el servidor al llamar ${endpoint}. Revisa la red, el tamaño del lote o si el navegador liberó los archivos temporales.`);
            error.codigo = 'conexion_fallida';
            throw error;
        }
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const texto = await res.text();
            const error = new Error(texto ? texto.slice(0, 500) : `El servidor respondio HTTP ${res.status} sin JSON.`);
            error.codigo = 'respuesta_no_json';
            error.status = res.status;
            throw error;
        }
        const json = await res.json();
        if (!json.success) {
            const error = new Error(json.mensaje || 'La operacion no se completo.');
            error.codigo = json.codigo || json?.datos?.codigo || '';
            error.status = res.status;
            throw error;
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

            const batchId = String(importAnalisis?.batch_id || '').trim();
            const globalIndex = Number(sourceIndex || 0);
            const file = importFiles[globalIndex] || null;
            if (!batchId && !file) {
                throw new Error('No se encontro el archivo seleccionado en la carga actual.');
            }
            const fd = batchId
                ? importFormData()
                : importFormData({ files: file ? [file] : [], sourceOffset: globalIndex, usarBatch: false });
            fd.append('source_index', batchId ? globalIndex : 0);
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
        const mostrarPreparando = importSeleccionTieneZip();
        try {
            const batches = importCrearBatches(importFiles);
            importSetLoading(true, batches.length > 1 ? `Analizando lote 1 de ${batches.length}...` : 'Analizando documentos...');
            if (mostrarPreparando && !importPreparandoArchivosDesde) {
                importMostrarPreparandoArchivos();
            }
            const endpoint = importPersonaObjetivo
                ? '/caphum/analizarImportacionDocumentosPersonaRrhh'
                : '/caphum/analizarImportacionDocumentosRrhh';
            const combinado = {
                items: [],
                resumen: importResumenVacio(),
                catalogo: [],
                batch_id: ''
            };
            for (let i = 0; i < batches.length; i++) {
                const batch = batches[i];
                if (batches.length > 1) {
                    importSetLoading(true, `Analizando lote ${i + 1} de ${batches.length} (${batch.files.length} archivo(s))...`);
                }
                let parcial;
                try {
                    parcial = await importEnviar(endpoint, {
                        files: batch.files,
                        sourceOffset: batch.sourceOffset,
                        usarBatch: false,
                        cacheLote: false
                    });
                } catch (err) {
                    throw new Error(importMensajeErrorOperacion(err, {
                        fase: 'analisis de documentos',
                        endpoint,
                        batch,
                        index: i,
                        total: batches.length
                    }));
                }
                if (!combinado.catalogo.length && Array.isArray(parcial.catalogo)) {
                    combinado.catalogo = parcial.catalogo;
                }
                if (batches.length === 1 && parcial.batch_id) {
                    combinado.batch_id = parcial.batch_id;
                }
                combinado.items.push(...importNormalizarItemsBatch(parcial.items || [], batch.sourceOffset));
                importSumarResumen(combinado.resumen, parcial.resumen || {});
                importRenderResumen(combinado.resumen);
                if (batches.length > 1) {
                    const procesados = combinado.items.length;
                    importSetLoading(true, `Analizando lote ${i + 1} de ${batches.length}. Acumulado: ${procesados}/${importFiles.length} archivo(s), ${Number(combinado.resumen.listo || 0)} listo(s).`);
                }
            }
            importAnalisis = combinado;
            if (mostrarPreparando) {
                await importCerrarPreparandoArchivos();
            }
            importRenderResumen(importAnalisis.resumen);
            importRenderTabla(importAnalisis.items || []);
            const listos = importAnalisis?.resumen?.listo || 0;
            els.importSeleccionResumen.textContent = `Analisis listo. ${listos} documento(s) pueden importarse${batches.length > 1 ? ` en ${batches.length} lotes` : ''}.`;
            els.importBtnImportar.disabled = listos <= 0;
        } catch (err) {
            importPreparandoArchivosDesde = 0;
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
            const batches = importCrearBatches(importFiles);
            const manualesGlobales = importManualesGlobales();
            importSetLoading(true, batches.length > 1 ? `Importando lote 1 de ${batches.length}...` : 'Importando documentos...');
            Swal.fire({
                title: 'Subiendo documentos',
                text: batches.length > 1 ? `Se estan subiendo ${batches.length} lotes seguros. Por favor espere.` : 'Se estan subiendo los documentos. Por favor espere.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            const endpoint = importPersonaObjetivo
                ? '/caphum/importarDocumentosPersonaRrhh'
                : '/caphum/importarDocumentosRrhh';
            let resultado;
            if (batches.length === 1 && String(importAnalisis?.batch_id || '').trim()) {
                try {
                    resultado = await importEnviar(endpoint);
                } catch (err) {
                    if (err.codigo !== 'lote_temporal_no_disponible' || !importFiles.length) {
                        throw err;
                    }
                    importSetLoading(true, 'La preparacion temporal expiro. Reintentando con los archivos seleccionados...');
                    resultado = await importEnviar(endpoint, { usarBatch: false });
                }
            } else {
                resultado = {
                    items: [],
                    resumen: importResumenVacio(),
                    importados: 0,
                    batch_id: ''
                };
                for (let i = 0; i < batches.length; i++) {
                    const batch = batches[i];
                    if (batches.length > 1) {
                        importSetLoading(true, `Importando lote ${i + 1} de ${batches.length} (${batch.files.length} archivo(s))...`);
                        if (Swal.isVisible() && Swal.getHtmlContainer()) {
                            Swal.getHtmlContainer().textContent = `Lote ${i + 1} de ${batches.length}.`;
                        }
                    }
                    let parcial;
                    try {
                        parcial = await importEnviar(endpoint, {
                            files: batch.files,
                            sourceOffset: batch.sourceOffset,
                            usarBatch: false,
                            cacheLote: false,
                            manualesGlobales
                        });
                    } catch (err) {
                        throw new Error(importMensajeErrorOperacion(err, {
                            fase: 'importacion de documentos',
                            endpoint,
                            batch,
                            index: i,
                            total: batches.length
                        }));
                    }
                    resultado.items.push(...importNormalizarItemsBatch(parcial.items || [], batch.sourceOffset));
                    importSumarResumen(resultado.resumen, parcial.resumen || {});
                    resultado.importados += Number(parcial.importados || 0);
                    importRenderResumen(resultado.resumen);
                    if (batches.length > 1) {
                        const procesados = resultado.items.length;
                        importSetLoading(true, `Importando lote ${i + 1} de ${batches.length}. Acumulado: ${procesados}/${importFiles.length} archivo(s), ${Number(resultado.importados || 0)} importado(s).`);
                    }
                }
            }
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
                col.codigo_contpac,
                col.numero_empleado,
                col.nombre_completo,
                col.correo,
                col.departamentos,
                col.puestos
            ].join(' ').toLowerCase();
            const coincideTexto = !q || texto.includes(q);
            const faltantes = Number(col.total_faltantes || 0);
            const cargados = Number(col.total_cargados || 0);
            const requeridos = Number(col.total_requeridos || 0);
            const coincideFiltro = !filtro
                || (filtro === 'sin_documentos' && cargados === 0)
                || (filtro === 'parcial' && cargados > 0 && cargados < requeridos)
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
                const codigoContpac = escapeHtml(String(col.codigo_contpac || '').trim() || 'Sin id');
                const externalId = escapeHtml(String(col.numero_empleado || '').trim() || 'Sin id');
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
                                <span class="docs-rrhh-avatar-fallback">${escapeHtml(iniciales(col.nombre_completo))}</span>
                                <div>
                                    <div class="docs-rrhh-employee-code">
                                        <span>No. empleado:</span>
                                        <span class="docs-rrhh-code-value">${codigoContpac}</span>
                                    </div>
                                    <div class="fw-semibold text-uppercase">${escapeHtml(col.nombre_completo || 'Colaborador')}</div>
                                    <div class="docs-rrhh-external-id">External id: <strong>${externalId}</strong></div>
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
                            <div class="docs-rrhh-actions">
                                <button type="button" class="btn btn-sm btn-primary docs-rrhh-action-btn" data-docs-detalle="${Number(col.id_persona || 0)}" title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning docs-rrhh-action-btn" data-docs-trayectoria="${Number(col.id_persona || 0)}" title="Ver trayectoria laboral">
                                    <i class="fa-solid fa-route"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-success docs-rrhh-action-btn" data-docs-cargar-expediente="${Number(col.id_persona || 0)}" title="Cargar expediente de este colaborador">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </button>
                            </div>
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

        els.modalSubtitulo.textContent = `No. empleado: ${String(col.codigo_contpac || '').trim() || 'Sin id'} - ${col.nombre_completo || 'Colaborador'} | External id: ${String(col.numero_empleado || '').trim() || 'Sin id'}`;
        els.modalReq.textContent = Number(col.total_requeridos || 0);
        els.modalCar.textContent = Number(col.total_cargados || 0);
        els.modalFal.textContent = Number(col.total_faltantes || 0);
        els.modalPor.textContent = `${Number(col.porcentaje_local || 0)}%`;
        const docs = Array.isArray(col.documentos) ? col.documentos : [];
        els.modalBody.innerHTML = docs.map(doc => {
            const cargado = !!doc.cargado;
            const cubierto = String(doc.estatus || '').toLowerCase() === 'cubierto';
            const badge = cubierto
                ? '<span class="badge bg-info text-dark">Cubierto</span>'
                : (cargado ? '<span class="badge bg-success">Cargado</span>' : '<span class="badge bg-warning text-dark">Faltante</span>');
            const nota = cubierto && doc.cubierto_por
                ? `<div class="small text-muted">Cubierto por ${escapeHtml(doc.cubierto_por)}</div>`
                : '';
            return `
                <tr>
                    <td>
                        <div class="fw-semibold">${escapeHtml(doc.nombre)}</div>
                        ${nota}
                    </td>
                    <td class="text-center">${Number(doc.total_archivos || 0)}</td>
                    <td>${doc.ultima_fecha ? escapeHtml(doc.ultima_fecha) : '<span class="text-muted">Sin carga</span>'}</td>
                    <td class="text-end">
                        ${badge}
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

    function abrirImportacionPersona(idPersona) {
        const col = colaboradores.find(item => Number(item.id_persona || 0) === Number(idPersona));
        if (!col) return;
        importPersonaObjetivo = {
            id_persona: Number(col.id_persona || 0),
            nombre: col.nombre_completo || 'Colaborador',
            numero: col.codigo_contpac || col.numero_empleado || ''
        };
        importLimpiarSeleccion();
        if (els.importTitulo) {
            els.importTitulo.textContent = 'Cargar expediente de colaborador';
        }
        els.importSeleccionResumen.textContent = `Colaborador: ${importPersonaObjetivo.nombre}${importPersonaObjetivo.numero ? ' - No. ' + importPersonaObjetivo.numero : ''}. Selecciona ZIP/PDF/FAD o carpeta.`;
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(els.importModal).show();
        } else if (window.jQuery) {
            window.jQuery(els.importModal).modal('show');
        }
    }

    function formatoFechaTrayectoria(value) {
        if (!value) return '';
        const raw = String(value).replace('T', ' ');
        const fecha = raw.slice(0, 10).split('-');
        const hora = raw.length > 10 ? raw.slice(11, 19) : '';
        return fecha.length === 3 ? `${fecha[2]}/${fecha[1]}/${fecha[0]}${hora ? ' ' + hora : ''}` : raw;
    }

    function configTrayectoria(accion) {
        const key = String(accion || '').toLowerCase();
        const mapa = {
            alta_puesto: ['Asignaci\u00f3n inicial de puesto', '#2563eb'],
            agrego_puesto: ['Agreg\u00f3 puesto al colaborador', '#16a34a'],
            removio_puesto: ['Removi\u00f3 puesto del colaborador', '#dc2626'],
            ascenso_puesto: ['Aumento de puesto', '#7c3aed'],
            cambio_puesto_principal: ['Cambi\u00f3 puesto principal', '#0891b2'],
            puesto_actual: ['Puesto actual', '#64748b']
        };
        return mapa[key] || ['Movimiento de puesto', '#f97316'];
    }

    function origenTrayectoria(origen) {
        const key = String(origen || '').toLowerCase();
        const mapa = {
            alta_gestion_personal: 'Alta en Gesti\u00f3n de personal',
            edicion_gestion_personal: 'Edici\u00f3n en Gesti\u00f3n de personal',
            alta_rrhh: 'Alta desde RR.HH.',
            edicion_rrhh: 'Edici\u00f3n desde RR.HH.',
            semilla_estado_actual: 'L\u00ednea base desde puesto actual',
            estado_actual: 'Estado actual'
        };
        return mapa[key] || origen || '';
    }

    function detalleTrayectoria(item) {
        const anterior = item?.nombre_puesto_anterior || '';
        const nuevo = item?.nombre_puesto_nuevo || '';
        const deptoAnterior = item?.nombre_departamento_anterior || '';
        const deptoNuevo = item?.nombre_departamento_nuevo || '';
        const accion = String(item?.accion || '').toLowerCase();
        if (accion === 'removio_puesto') {
            return [anterior, deptoAnterior].filter(Boolean).join(' / ');
        }
        if (anterior && nuevo && anterior !== nuevo) {
            return `${anterior} -> ${nuevo}${deptoNuevo ? ' / ' + deptoNuevo : ''}`;
        }
        return [nuevo || anterior, deptoNuevo || deptoAnterior, item?.motivo || ''].filter(Boolean).join(' / ');
    }

    function metaTrayectoria(item) {
        const chips = [];
        const add = (label, value) => {
            const text = String(value || '').trim();
            if (text) chips.push([label, text]);
        };
        const fechaMovimiento = item?.fecha_movimiento || item?.creado_at || '';

        add('Fecha movimiento', formatoFechaTrayectoria(fechaMovimiento));

        return chips;
    }

    function renderTrayectoria(items) {
        const lista = Array.isArray(items) ? items : [];
        if (els.trayectoriaTotal) {
            els.trayectoriaTotal.textContent = `${lista.length} movimiento${lista.length === 1 ? '' : 's'}`;
        }
        if (!els.trayectoriaBody) return;
        if (!lista.length) {
            els.trayectoriaBody.innerHTML = '<div class="text-muted small py-3">Sin movimientos de puesto registrados todavia.</div>';
            return;
        }
        els.trayectoriaBody.innerHTML = lista.map(item => {
            const [titulo, color] = configTrayectoria(item.accion);
            const actor = item.responsable_nombre || 'Sistema';
            const fecha = formatoFechaTrayectoria(item.fecha_movimiento || item.creado_at || item.fecha_asignacion_nueva);
            const detalle = detalleTrayectoria(item);
            const chips = metaTrayectoria(item)
                .map(([label, value]) => `<span class="docs-rrhh-trayectoria-chip"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</span>`)
                .join('');
            return `
                <div class="docs-rrhh-trayectoria-item" style="--docs-trayectoria-color:${escapeHtml(color)}">
                    <div class="min-w-0">
                        <div class="docs-rrhh-trayectoria-title">${escapeHtml(titulo)}</div>
                        <div class="docs-rrhh-trayectoria-actor">${escapeHtml(actor)}</div>
                        ${detalle ? `<div class="docs-rrhh-trayectoria-detail">${escapeHtml(detalle)}</div>` : ''}
                        ${chips ? `<div class="docs-rrhh-trayectoria-meta">${chips}</div>` : ''}
                    </div>
                    <div class="docs-rrhh-trayectoria-date">${escapeHtml(fecha)}</div>
                </div>
            `;
        }).join('');
    }

    async function abrirTrayectoria(idPersona) {
        const col = colaboradores.find(item => Number(item.id_persona || 0) === Number(idPersona));
        if (els.trayectoriaSubtitulo) {
            els.trayectoriaSubtitulo.textContent = col
                ? `No. empleado: ${String(col.codigo_contpac || '').trim() || 'Sin id'} - ${col.nombre_completo || 'Colaborador'} | External id: ${String(col.numero_empleado || '').trim() || 'Sin id'}`
                : 'Colaborador';
        }
        if (els.trayectoriaBody) {
            els.trayectoriaBody.innerHTML = '<div class="text-muted small py-3"><span class="spinner-border spinner-border-sm me-2"></span>Cargando trayectoria...</div>';
        }
        if (els.trayectoriaTotal) els.trayectoriaTotal.textContent = '0 movimientos';
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(els.trayectoriaModal).show();
        } else if (window.jQuery) {
            window.jQuery(els.trayectoriaModal).modal('show');
        }
        try {
            const response = await fetch('/CapHum/getTrayectoriaPuestoPersona', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_persona: Number(idPersona || 0) })
            });
            const json = await response.json();
            if (!json.success) throw new Error(json.mensaje || 'No se pudo cargar la trayectoria.');
            renderTrayectoria(json.datos || []);
        } catch (err) {
            if (els.trayectoriaBody) {
                els.trayectoriaBody.innerHTML = `<div class="text-danger small py-3">${escapeHtml(err.message || 'No se pudo cargar la trayectoria.')}</div>`;
            }
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
        importPersonaObjetivo = null;
        if (els.importTitulo) {
            els.importTitulo.textContent = 'Importar documentos RR.HH.';
        }
        importLimpiarSeleccion();
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
    els.importarSueldos?.addEventListener('click', abrirImportarSueldos);
    els.sueldosBtnElegir?.addEventListener('click', function () {
        els.sueldosInput?.click();
    });
    els.sueldosInput?.addEventListener('change', function () {
        sueldosArchivo = this.files && this.files.length ? this.files[0] : null;
        if (els.sueldosArchivo) {
            els.sueldosArchivo.textContent = sueldosArchivo
                ? `${sueldosArchivo.name} (${Math.ceil((sueldosArchivo.size || 0) / 1024)} KB)`
                : 'No se ha seleccionado archivo.';
        }
        if (els.sueldosResultado) els.sueldosResultado.innerHTML = '';
        if (els.sueldosBtnSubir) els.sueldosBtnSubir.disabled = !sueldosArchivo;
    });
    els.sueldosBtnSubir?.addEventListener('click', subirSueldosExcel);
    els.body.addEventListener('click', function (event) {
        const detalleBtn = event.target.closest('[data-docs-detalle]');
        if (detalleBtn) {
            abrirDetalle(detalleBtn.getAttribute('data-docs-detalle'));
            return;
        }
        const trayectoriaBtn = event.target.closest('[data-docs-trayectoria]');
        if (trayectoriaBtn) {
            abrirTrayectoria(trayectoriaBtn.getAttribute('data-docs-trayectoria'));
            return;
        }
        const cargarBtn = event.target.closest('[data-docs-cargar-expediente]');
        if (cargarBtn) {
            abrirImportacionPersona(cargarBtn.getAttribute('data-docs-cargar-expediente'));
        }
    });
    document.addEventListener('DOMContentLoaded', cargarResumen);
})();
</script>
