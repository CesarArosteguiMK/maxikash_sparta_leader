<?php
$departamento = $departamento ?? ['datos' => []];
$paisesActivos = $paisesActivos ?? [];
$listaJefes = $listaJefes ?? [];
$departamentosCandidatoCatalogo = [];
$deptosCandidatoIdsCatalogo = [];
foreach (($departamento['datos'] ?? []) as $d) {
    $idDeptoCandidato = (int)($d['id'] ?? 0);
    if ($idDeptoCandidato <= 0 || isset($deptosCandidatoIdsCatalogo[$idDeptoCandidato])) {
        continue;
    }
    if (isset($d['activo']) && (int)$d['activo'] !== 1) {
        continue;
    }
    $deptosCandidatoIdsCatalogo[$idDeptoCandidato] = true;
    $departamentosCandidatoCatalogo[] = $d;
}
?>
<script>
window.departamentosCandidatoBackend = <?= json_encode($departamentosCandidatoCatalogo, JSON_UNESCAPED_UNICODE) ?>;
</script>
<div class="content-wrapper">

    <div class="card">

        <div class="card-header border-bottom filtros-candidatos-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="card-title mb-0">Selección de personal</h4>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnLimpiarFiltrosCandidatos">
                    <i class="bx bx-rotate-left me-1"></i> Limpiar
                </button>
            </div>
            <div class="filtros-candidatos-label">Filtros</div>
            <h5 class="card-title mb-0">Filtros de búsqueda</h5>
            <div class="row pt-4 g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="UserRole">Departamento</label>
                    <select id="UserRole" class="form-select text-capitalize">
                        <option value="">Departamento</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="UserPlan">Puesto</label>
                    <select id="UserPlan" class="form-select text-capitalize">
                        <option value="">Puesto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="FilterTransaction">Etapa</label>
                    <select id="FilterTransaction" class="form-select text-capitalize">
                        <option value="">Etapa</option>
                        <option value="Por evaluar">Por evaluar</option>
                        <option value="Validado">Validado</option>
                        <option value="Pendiente de validacion final">Pendiente de validación final</option>
                        <option value="Ingreso programado">Ingreso programado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted" for="FilterJefeCandidato">Jefe</label>
                    <select id="FilterJefeCandidato" class="form-select text-capitalize">
                        <option value="">Jefe</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="panelIndicadoresCandidatos" class="px-4 pt-3 pb-2">
            <div class="kpi-toolbar">
                <button class="kpi-toggle-btn open" type="button" id="kpiToggleBtnCandidatos" onclick="kpiTogglePanelCandidatos()">
                    <span class="kpi-dot"></span>
                    Indicadores
                    <i class="bx bx-chevron-down kpi-chevron"></i>
                </button>
                <div class="kpi-toolbar-sep" id="kpiViewControlsSepCand"></div>
                <div id="kpiViewControlsCand">
                    <button class="kpi-view-btn active" id="vbtn-cand-default" onclick="kpiSetModeCand('default')" data-tip="Vista Estándar">
                        <i class="bx bx-layout"></i>
                        <span class="kpi-btn-text">Estándar</span>
                    </button>
                    <button class="kpi-view-btn" id="vbtn-cand-vision" onclick="kpiSetModeCand('vision')" data-tip="Vista Donut">
                        <i class="bx bx-doughnut-chart"></i>
                        <span class="kpi-btn-text">Donut</span>
                    </button>
                    <button class="kpi-view-btn" id="vbtn-cand-ministat" onclick="kpiSetModeCand('ministat')" data-tip="Vista Mini-Stat">
                        <i class="bx bx-columns"></i>
                        <span class="kpi-btn-text">Mini-Stat</span>
                    </button>
                    <div class="kpi-toolbar-sep"></div>
                    <button class="kpi-reset-btn" onclick="kpiResetPrefsCand()">
                        <i class="bx bx-rotate-left"></i>
                        Restablecer
                    </button>
                </div>
            </div>
            <div class="kpi-collapsible open" id="kpiCollapsibleCandidatos">
                <div class="kpi-collapsible-inner">
                    <div class="kpi-row-new mode-default<?= !empty($candidatosSoloValidacionFinal ?? false) ? ' kpi-solo-final' : '' ?>" id="kpiRowNewCand">
                        <div class="kpi-cell tipo-total revealed" id="kpi-cell-cand-total">
                            <span class="kpi-corner-icon"><i class="bx bx-group"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-group"></i></div>
                                <span class="kpi-cell-status">Bandeja</span>
                            </div>
                            <div class="kpi-num" id="kpi-total-candidatos">0</div>
                            <div class="kpi-lbl">Candidatos en bandeja</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-cand-total"></div></div>
                            <span class="kpi-cell-title">Candidatos en bandeja</span>
                            <div class="kpi-stats-grid-new">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-cand-total">0</div><div class="kpi-stat-lbl">Total</div></div>
                            </div>
                            <div class="donut-block">
                                <div class="donut-header">
                                    <span class="donut-title">Candidatos en bandeja</span>
                                    <span class="kpi-cell-status">Filtrado</span>
                                </div>
                                <div class="donut-svg-wrap">
                                    <svg class="donut-svg" viewBox="0 0 88 88">
                                        <circle class="donut-track" cx="44" cy="44" r="36"/>
                                        <circle class="donut-arc" id="kpi-arc-cand-total" cx="44" cy="44" r="36"/>
                                    </svg>
                                    <div class="donut-center-icon"><i class="bx bx-group"></i></div>
                                </div>
                                <div class="donut-stats">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-cand-total">0</div><div class="kpi-stat-lbl">Bandeja</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="kpi-cell tipo-puesto revealed<?= !empty($candidatosSoloValidacionFinal ?? false) ? ' d-none' : '' ?>" id="kpi-cell-cand-evaluar">
                            <span class="kpi-corner-icon"><i class="bx bx-user-plus"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-user-plus"></i></div>
                                <span class="kpi-cell-status">Evaluación</span>
                            </div>
                            <div class="kpi-num" id="kpi-por-evaluar">0</div>
                            <div class="kpi-lbl">Por evaluar</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-cand-evaluar"></div></div>
                            <span class="kpi-cell-title">Por evaluar</span>
                            <div class="kpi-stats-grid-new">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-cand-evaluar">0</div><div class="kpi-stat-lbl">Por evaluar</div></div>
                            </div>
                            <div class="donut-block">
                                <div class="donut-header">
                                    <span class="donut-title">Por evaluar</span>
                                    <span class="kpi-cell-status">Evaluación</span>
                                </div>
                                <div class="donut-svg-wrap">
                                    <svg class="donut-svg" viewBox="0 0 88 88">
                                        <circle class="donut-track" cx="44" cy="44" r="36"/>
                                        <circle class="donut-arc" id="kpi-arc-cand-evaluar" cx="44" cy="44" r="36"/>
                                    </svg>
                                    <div class="donut-center-icon"><i class="bx bx-user-plus"></i></div>
                                </div>
                                <div class="donut-stats">
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-cand-evaluar">0</div><div class="kpi-stat-lbl">Por evaluar</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="kpi-cell tipo-final revealed" id="kpi-cell-cand-final">
                            <span class="kpi-corner-icon"><i class="bx bx-check-shield"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-check-shield"></i></div>
                                <span class="kpi-cell-status">Final</span>
                            </div>
                            <div class="kpi-num" id="kpi-validacion-final-candidatos">0</div>
                            <div class="kpi-lbl">Validación final</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-cand-final"></div></div>
                            <span class="kpi-cell-title">Validación final</span>
                            <div class="kpi-stats-grid-new">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-cand-final">0</div><div class="kpi-stat-lbl">Final</div></div>
                            </div>
                            <div class="donut-block">
                                <div class="donut-header">
                                    <span class="donut-title">Validación final</span>
                                    <span class="kpi-cell-status">Final</span>
                                </div>
                                <div class="donut-svg-wrap">
                                    <svg class="donut-svg" viewBox="0 0 88 88">
                                        <circle class="donut-track" cx="44" cy="44" r="36"/>
                                        <circle class="donut-arc" id="kpi-arc-cand-final" cx="44" cy="44" r="36"/>
                                    </svg>
                                    <div class="donut-center-icon"><i class="bx bx-check-shield"></i></div>
                                </div>
                                <div class="donut-stats">
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-cand-final">0</div><div class="kpi-stat-lbl">Final</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="kpi-cell tipo-dep revealed" id="kpi-cell-cand-enviadas">
                            <span class="kpi-corner-icon"><i class="bx bx-send"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-send"></i></div>
                                <span class="kpi-cell-status">Docs</span>
                            </div>
                            <div class="kpi-num" id="kpi-postulaciones-enviadas">0</div>
                            <div class="kpi-lbl">Expedientes completos</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-cand-enviadas"></div></div>
                            <span class="kpi-cell-title">Expedientes completos</span>
                            <div class="kpi-stats-grid-new">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-cand-enviadas">0</div><div class="kpi-stat-lbl">Completos</div></div>
                            </div>
                            <div class="donut-block">
                                <div class="donut-header">
                                    <span class="donut-title">Expedientes completos</span>
                                    <span class="kpi-cell-status">Docs</span>
                                </div>
                                <div class="donut-svg-wrap">
                                    <svg class="donut-svg" viewBox="0 0 88 88">
                                        <circle class="donut-track" cx="44" cy="44" r="36"/>
                                        <circle class="donut-arc" id="kpi-arc-cand-enviadas" cx="44" cy="44" r="36"/>
                                    </svg>
                                    <div class="donut-center-icon"><i class="bx bx-send"></i></div>
                                </div>
                                <div class="donut-stats">
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-cand-enviadas">0</div><div class="kpi-stat-lbl">Completos</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-between m-4">
            <div class="col-8"></div>
            <div class="col-4 d-flex align-items-end justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary btn-action-size" id="btnHistoricoCandidatos">
                    <i class="fa fa-clock-rotate-left icon-sm me-sm-2"></i>
                    <span class="d-inline-block">Histórico</span>
                </button>
                <button type="button" class="btn btn-primary add-new btn-action-size" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddCandidato">
                    <i class="fa fa-user-plus icon-sm me-sm-2"></i>
                    <span class="d-inline-block">Agregar Candidato</span>
                </button>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="tablaCandidatos" class="dt-responsive table border-top" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre / Contacto</th>
                        <th>Puesto / Departamento</th>
                        <th>Ubicación</th>
                        <th>Estatus</th>
                        <th class="col-acciones-candidatos">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Documentación del candidato -->
<div class="modal fade" id="modalDocumentacionCandidato" tabindex="-1" aria-labelledby="modalDocumentacionCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 85%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDocumentacionCandidatoLabel"><i class="fa fa-folder-open me-2"></i>Documentación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="modal-doc-toolbar">
                    <p class="text-muted small mb-0" id="modalDocumentacionCandidatoNombre"></p>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnDescargarExpedienteCandidatoZip">
                        <i class="fa fa-file-zipper me-1"></i> Descargar ZIP
                    </button>
                </div>
                <div id="modalDocumentacionCandidatoApiTrace" class="alert alert-secondary small py-2 mb-2 d-none" role="status" aria-live="polite"></div>
                <div id="modalDocumentacionCandidatoCargando" class="text-center py-4 text-muted">Cargando…</div>
                <div class="row g-3 align-items-stretch">
                    <div class="col-lg-8 col-12 d-flex flex-column">
                        <div id="modalDocumentacionCandidatoVacio" class="text-center py-4 text-muted d-none">No hay documentos subidos.</div>
                        <div id="modalDocumentacionCandidatoLista" class="list-group flex-grow-1 h-100 overflow-auto border rounded bg-body-tertiary"></div>
                    </div>
                    <div class="col-lg-4 col-12 d-flex flex-column">
                        <div class="modal-doc-col-stack d-flex flex-column gap-3 flex-grow-1 h-100">
                            <div id="modalDocumentacionCandidatoMetricas" class="d-none flex-fill"></div>
                            <div id="modalDocumentacionCandidatoSueldo" class="d-none flex-shrink-0"></div>
                            <div id="modalDocumentacionCandidatoVerificacion" class="d-none flex-fill"></div>
                            <div id="modalDocumentacionCandidatoAccionVerificar" class="d-none flex-shrink-0"></div>
                        </div>
                    </div>
                </div>
                <div id="modalDocumentacionCandidatoComparaciones" class="mt-3 d-none"></div>
            </div>
            <div class="modal-footer flex-column align-items-stretch border-top py-3 d-none text-lg-end" id="modalDocumentacionCandidatoAccionesProceso"></div>
        </div>
    </div>
</div>

<!-- Modal Análisis cruzado del candidato -->
<div class="modal fade" id="modalAnalisisCruzadoCandidato" tabindex="-1" aria-labelledby="modalAnalisisCruzadoCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 92%;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="modalAnalisisCruzadoCandidatoLabel"><i class="fa fa-shield-alt me-2"></i>Análisis cruzado documental</h5>
                    <p class="text-muted small mb-0" id="modalAnalisisCruzadoCandidatoNombre"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="modalAnalisisCruzadoCandidatoBody" class="small text-muted">No hay análisis disponible.</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visor de documento del candidato -->
<div class="modal fade" id="modalVisorDocumentoCandidato" tabindex="-1" aria-labelledby="modalVisorDocumentoCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 92vw;">
        <div class="modal-content visor-doc-candidato-content">
            <div class="modal-header py-2">
                <h5 class="modal-title text-truncate" id="modalVisorDocumentoCandidatoLabel">
                    <i class="fa fa-file-pdf me-2 text-danger"></i>Documento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0 position-relative">
                <div id="modalVisorDocumentoCandidatoLoading" class="visor-doc-candidato-loading">
                    <i class="fa fa-spinner fa-spin me-2"></i>Cargando documento...
                </div>
                <iframe id="modalVisorDocumentoCandidatoFrame" class="visor-doc-candidato-frame" title="Vista previa del documento"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Modal Histórico de candidatos -->
<div class="modal fade" id="modalHistoricoCandidatos" tabindex="-1" aria-labelledby="modalHistoricoCandidatosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHistoricoCandidatosLabel"><i class="fa fa-clock-rotate-left me-2"></i>Histórico de candidatos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs candidate-detail-tabs mb-3" id="modalHistoricoCandidatosTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="historico-tab-lista" data-bs-toggle="tab" data-bs-target="#historicoPaneLista" type="button" role="tab">
                            <i class="fa fa-list me-1"></i>Listado
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historico-tab-metrica" data-bs-toggle="tab" data-bs-target="#historicoPaneMetrica" type="button" role="tab">
                            <i class="fa fa-chart-line me-1"></i>Metrica global
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="historicoPaneLista" role="tabpanel" aria-labelledby="historico-tab-lista">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <p class="text-muted small mb-0">Consulta candidatos que ya salieron de la bandeja y abre su bitácora completa.</p>
                    <div class="historico-search">
                        <input type="text" class="form-control" id="modalHistoricoCandidatosBuscar" placeholder="Buscar candidato">
                    </div>
                </div>
                <div id="modalHistoricoCandidatosCargando" class="text-center py-4 text-muted">Cargando histórico…</div>
                <div id="modalHistoricoCandidatosLista" class="historico-list d-none"></div>
                <div id="modalHistoricoCandidatosVacio" class="text-center py-4 text-muted d-none">No hay candidatos fuera de la bandeja todavía.</div>
                    </div>
                    <div class="tab-pane fade" id="historicoPaneMetrica" role="tabpanel" aria-labelledby="historico-tab-metrica">
                        <div id="modalHistoricoCandidatosMetrica" class="historico-global-metrics"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bitácora del candidato -->
<div class="modal fade" id="modalBitacoraCandidato" tabindex="-1" aria-labelledby="modalBitacoraCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBitacoraCandidatoLabel"><i class="fa fa-clock-rotate-left me-2"></i>Bitácora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="modalBitacoraCandidatoNombre"></p>
                <div id="modalBitacoraCandidatoCargando" class="text-center py-4 text-muted">Cargando bitácora…</div>
                <div id="modalBitacoraCandidatoContenido" class="d-none">
                    <ul class="nav nav-tabs candidate-detail-tabs mb-3" id="modalBitacoraCandidatoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bitacora-tab-linea" data-bs-toggle="tab" data-bs-target="#bitacoraPaneLinea" type="button" role="tab">
                                <i class="fa fa-clock-rotate-left me-1"></i>Bitacora
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bitacora-tab-metrica" data-bs-toggle="tab" data-bs-target="#bitacoraPaneMetrica" type="button" role="tab">
                                <i class="fa fa-chart-line me-1"></i>Metrica
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="bitacoraPaneLinea" role="tabpanel" aria-labelledby="bitacora-tab-linea">
                            <div id="modalBitacoraCandidatoLista" class="candidate-timeline"></div>
                        </div>
                        <div class="tab-pane fade" id="bitacoraPaneMetrica" role="tabpanel" aria-labelledby="bitacora-tab-metrica">
                            <div id="modalBitacoraCandidatoMetrica"></div>
                        </div>
                    </div>
                </div>
                <div id="modalBitacoraCandidatoVacio" class="text-center py-4 text-muted d-none">Sin movimientos registrados.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fecha de ingreso (Continuar proceso) -->
<div class="modal fade" id="modalFechaIngresoCandidato" tabindex="-1" aria-labelledby="modalFechaIngresoCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFechaIngresoCandidatoLabel"><i class="fa fa-calendar-check me-2"></i>Fecha de ingreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fechaIngresoIdCandidato" value="">
                <p class="text-muted small mb-3">Selecciona la fecha en la que el candidato se presentará para firma de contrato e inicio de labores.</p>
                <label for="fechaIngresoCandidato" class="form-label">Fecha de ingreso <span class="text-danger">*</span></label>
                <input type="text" id="fechaIngresoCandidato" class="form-control" placeholder="YYYY-MM-DD" autocomplete="off">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarFechaIngresoCandidato"><i class="fa fa-envelope me-1"></i>Enviar notificaciones</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cerrar proceso del candidato -->
<div class="modal fade modal-cerrar-proceso" id="modalCerrarProcesoCandidato" tabindex="-1" aria-labelledby="modalCerrarProcesoCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCerrarProcesoCandidatoLabel"><i class="fa fa-times-circle me-2"></i>Cerrar proceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Indica el motivo por el que no se continúa con el proceso de este candidato.</p>
                <input type="hidden" id="cerrarProcesoIdCandidato" value="">
                <div class="mb-3">
                    <label for="cerrarProcesoMotivo" class="form-label">Motivo <span class="text-danger">*</span></label>
                    <select id="cerrarProcesoMotivo" class="form-select" required>
                        <option value="">Selecciona un motivo</option>
                        <option value="no_cubre_perfil">No cubre el perfil</option>
                        <option value="desistio">Desistió</option>
                        <option value="sin_info_a_tiempo">No dio información a tiempo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="cerrarProcesoDescripcion" class="form-label">Descripción (opcional)</label>
                    <textarea id="cerrarProcesoDescripcion" class="form-control" rows="3" placeholder="Detalles adicionales..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-danger" id="btnConfirmarCerrarProceso"><i class="fa fa-check me-1"></i>Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Agregar Candidato -->
<div class="offcanvas offcanvas-end" id="offcanvasAddCandidato" tabindex="-1">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvasCandidatoTitulo">Nuevo Candidato</h5>
    </div>
    <div class="offcanvas-body p-4">
        <form id="formAgregarCandidato">
            <div class="mb-2">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombres" id="candidato_nombres" class="form-control" required maxlength="100" placeholder="Ej. Juan" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Segundo nombre (opcional)</label>
                <input type="text" name="segundo_nombre" id="candidato_segundo_nombre" class="form-control" maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                <input type="text" name="apellidop" id="candidato_apellidop" class="form-control" required maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Apellido materno <span class="text-danger">*</span></label>
                <input type="text" name="apellidom" id="candidato_apellidom" class="form-control" required maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                <input type="text" name="telefono" id="candidato_telefono" class="form-control" required maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <div class="mb-2">
                <label class="form-label">Correo <span class="text-danger">*</span></label>
                <input type="email" name="email" id="candidato_email" class="form-control" required maxlength="150">
            </div>
            <div class="mb-2">
                <label class="form-label">País <span class="text-danger">*</span></label>
                <select name="id_pais" id="candidato_id_pais" class="form-select js-select-buscador" required>
                    <option value="">Seleccione un país</option>
                    <?php foreach ($paisesActivos as $p): ?>
                        <option value="<?= (int)($p['id'] ?? 0) ?>"><?= htmlspecialchars($p['nombre'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2" id="div_candidato_estado" style="display:none;">
                <label class="form-label" id="label_candidato_estado">Estado</label>
                <select id="candidato_id_div_nivel1" name="id_div_nivel1" class="form-select js-select-buscador" disabled>
                    <option value="">Seleccione un estado</option>
                </select>
            </div>
            <div class="mb-2" id="div_candidato_municipio" style="display:none;">
                <label class="form-label" id="label_candidato_municipio">Alcaldía / Municipio</label>
                <select id="candidato_id_div_nivel2" name="id_div_nivel2" class="form-select js-select-buscador" disabled>
                    <option value="">Seleccione una alcaldía / municipio</option>
                </select>
            </div>
            <div class="mb-2" id="div_candidato_colonia" style="display:none;">
                <label class="form-label">Colonia</label>
                <select id="candidato_id_div_nivel3" name="id_div_nivel3" class="form-select js-select-buscador" disabled>
                    <option value="">Seleccione una colonia</option>
                </select>
            </div>
            <div class="mb-2" id="div_candidato_codigo_postal" style="display:none;">
                <label class="form-label">Codigo postal</label>
                <input type="text" name="codigo_postal" id="candidato_codigo_postal" class="form-control" maxlength="12" readonly>
            </div>
            <div class="mb-2" id="div_candidato_calle_texto" style="display:none;">
                <label class="form-label">Calle</label>
                <input type="text" name="domicilio_calle_texto" id="candidato_domicilio_calle_texto" class="form-control" maxlength="180">
            </div>
            <div class="row mb-2" id="div_candidato_num_extint" style="display:none;">
                <div class="col-md-6">
                    <label class="form-label">No. exterior</label>
                    <input type="text" name="domicilio_num_exterior" id="candidato_domicilio_num_exterior" class="form-control" maxlength="32">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. interior (opcional)</label>
                    <input type="text" name="domicilio_num_interior" id="candidato_domicilio_num_interior" class="form-control" maxlength="32">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Empresa <span class="text-danger">*</span></label>
                <select name="id_empresa" id="candidato_id_empresa" class="form-select js-select-buscador" required>
                    <option value="">Seleccione empresa</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Dirección <span class="text-danger">*</span></label>
                <select name="id_direccion" id="candidato_id_direccion" class="form-select js-select-buscador" required disabled>
                    <option value="">Seleccione dirección</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Área <span class="text-danger">*</span></label>
                <select name="id_area" id="candidato_id_area" class="form-select js-select-buscador" required disabled>
                    <option value="">Seleccione dirección primero</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Departamento al que aplica <span class="text-danger">*</span></label>
                <select name="id_departamento" id="candidato_id_departamento" class="form-select js-select-buscador" required disabled>
                    <option value="">Seleccione área primero</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Puesto solicitado <span class="text-danger">*</span></label>
                <select name="id_puesto" id="candidato_id_puesto" class="form-select js-select-buscador" required>
                    <option value="">Seleccione puesto</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Posible jefe <span class="text-danger">*</span></label>
                <select name="id_posible_jefe" id="candidato_id_posible_jefe" class="form-select js-select-buscador" required>
                    <option value="">Seleccione departamento y puesto primero</option>
                </select>
            </div>
            <div class="mb-2 d-none" id="wrap_candidato_jefe_divisional">
                <label class="form-label">Jefe divisional <span class="text-danger">*</span></label>
                <select name="id_jefe_divisional" id="candidato_id_jefe_divisional" class="form-select js-select-buscador">
                    <option value="">Seleccione jefe divisional</option>
                </select>
                <small class="text-muted">Aplica solo para candidatos de Cobranza con puesto de gestor.</small>
            </div>
            <div class="mb-2">
                <label class="form-label">Fecha de postulación <span class="text-danger">*</span></label>
                <div class="fecha-acta-wrapper fecha-postulacion-wrapper">
                    <input type="text" name="fecha_postulacion" id="candidato_fecha_postulacion" class="form-control" placeholder="YYYY-MM-DD" required autocomplete="off" readonly>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="candidato_asignar_legion" onchange="toggleLegionCandidato()">
                    <label class="form-check-label" for="candidato_asignar_legion">Asignar legión</label>
                </div>
            </div>
            <div class="mb-2" id="div_candidato_legion" style="display:none;">
                <label class="form-label">Legión <span class="text-danger">*</span></label>
                <select name="id_legion" id="candidato_id_legion" class="form-select js-select-buscador">
                    <option value="">Seleccione legión</option>
                    <option value="1">Sabueso</option>
                    <option value="2">Heraldo</option>
                    <option value="3">Centinela</option>
                    <option value="4">Senturiones</option>
                    <option value="5">Espartano</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Usuario <span class="text-danger">*</span></label>
                <input type="text" name="usuario" id="candidato_usuario" class="form-control" required maxlength="50" placeholder="Ej. lazaro.mendez" oninput="this.value = this.value.replace(/[^A-Za-z0-9_.\-]/g, '');">
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                <input type="text" name="contrasena" id="candidato_contrasena" class="form-control" required maxlength="255">
            </div>
            <button type="submit" class="btn btn-primary me-2" id="btnSubmitCandidato"><i class="bx bx-save me-1"></i> Guardar</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas"><i class="bx bx-x me-1"></i> Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal Resumen y Enviar postulación -->
<div class="modal fade" id="modalResumenPostulacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-resumen-candidato">
        <div class="modal-content resumen-candidato-modal-content">
            <div class="modal-header resumen-candidato-modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bx bx-user-detail text-primary"></i>
                    Resumen del candidato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body resumen-candidato-modal-body">
                <div id="resumenPostulacionTexto" class="resumen-candidato-card mb-4"></div>
                <div id="bloqueLinkDocumentos" class="mb-3 link-documentos-block" style="display: none;">
                    <div class="link-documentos-card">
                        <div class="link-documentos-label">Enlace para que el candidato suba sus documentos</div>
                        <div class="link-documentos-url-box">
                            <span class="link-documentos-url-icon" aria-hidden="true">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                            </span>
                            <input type="text" class="link-documentos-url-input" id="inputUrlDocumentos" readonly placeholder="Se generará al enviar o al reenviar" title="">
                        </div>
                        <div class="link-documentos-status" id="estadoUrlDocumentos" aria-live="polite"></div>
                        <div class="link-documentos-actions">
                            <button type="button" class="link-documentos-btn link-documentos-btn-copy" id="btnCopiarUrlDocumentos" title="Copiar URL">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                Copiar URL
                            </button>
                            <button type="button" class="link-documentos-btn link-documentos-btn-open" id="btnAbrirUrlDocumentos" title="Abrir en nueva pestaña">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Abrir URL
                            </button>
                            <button type="button" class="link-documentos-btn link-documentos-btn-reactivar" id="btnReactivarUrlDocumentos" title="Reactivar link vencido" style="display: none;">
                                <i class="bx bx-refresh" aria-hidden="true"></i>
                                Reactivar link
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end justify-content-md-center pt-2">
                    <button type="button" class="btn btn-primary btn-enviar-postulacion px-4 py-2" id="btnEnviarPostulacion" onclick="enviarPostulacionAlCandidato()">
                        <i class="bx bx-send me-2"></i> Enviar postulación al candidato
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="link-documentos-toast" id="toastUrlDocumentos" role="status" aria-live="polite">URL copiada al portapapeles</div>

<style>
.btn-action-size { height: 36px; padding: 0.375rem 0.75rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.375rem; }
#tablaCandidatos thead th { background-color: rgba(105, 108, 255, 0.1); font-weight: 600; }
/* Offcanvas candidato siempre visible por encima del layout */
#offcanvasAddCandidato.offcanvas {
    z-index: 1095 !important;
    top: 0 !important;
    height: 100vh !important;
}
.offcanvas-backdrop { z-index: 1090 !important; }
/* Scrim borroso detrás del modal (Documentación, Resumen postulación, etc.) */
.modal-backdrop { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
.modal-backdrop.show { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
/* Botón Guardar/Actualizar con color visible en modo claro y oscuro */
#offcanvasAddCandidato #btnSubmitCandidato.btn-primary { background-color: #696cff !important; border-color: #696cff !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-primary:hover { background-color: #5f61e6 !important; border-color: #5f61e6 !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-primary { background-color: #6366f1 !important; border-color: #6366f1 !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-primary:hover { background-color: #818cf8 !important; border-color: #818cf8 !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-success { background-color: #71dd37 !important; border-color: #71dd37 !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-success:hover { background-color: #85e34b !important; border-color: #85e34b !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-success { background-color: #22c55e !important; border-color: #22c55e !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-success:hover { background-color: #4ade80 !important; border-color: #4ade80 !important; color: #fff !important; }

/* Bloque enlace documentos: estilo tipo card oscuro (referencia URL component) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
.link-documentos-block { font-family: 'Inter', 'Public Sans', system-ui, sans-serif; }
.link-documentos-card {
    background: #1a1b26;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 28px 24px;
    width: 100%;
    box-shadow: 0 0 0 1px rgba(255,255,255,0.04), 0 12px 40px rgba(0, 0, 0, 0.35);
}
body:not(.dark-mode) .link-documentos-card {
    background: #f0f2f5;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.link-documentos-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.45);
    margin-bottom: 12px;
}
body:not(.dark-mode) .link-documentos-label { color: rgba(0, 0, 0, 0.5); }
.link-documentos-url-box {
    display: flex;
    align-items: center;
    background: #252633;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 14px 18px;
    gap: 10px;
    margin-bottom: 20px;
    transition: border-color 0.2s, background-color 0.2s;
}
.link-documentos-url-box:focus-within {
    border-color: rgba(139, 92, 246, 0.45);
    background: #2a2b38;
}
body:not(.dark-mode) .link-documentos-url-box {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
body:not(.dark-mode) .link-documentos-url-box:focus-within {
    border-color: rgba(139, 92, 246, 0.35);
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
}
.link-documentos-url-icon { color: rgba(255,255,255,0.4); flex-shrink: 0; }
body:not(.dark-mode) .link-documentos-url-icon { color: rgba(0, 0, 0, 0.4); }
.link-documentos-url-input {
    background: none;
    border: none;
    outline: none;
    color: rgba(255, 255, 255, 0.9);
    font-family: ui-monospace, 'SF Mono', 'Cascadia Code', monospace;
    font-size: 13.5px;
    font-weight: 400;
    letter-spacing: 0.01em;
    width: 100%;
    text-overflow: ellipsis;
    overflow: hidden;
}
.link-documentos-url-input::placeholder { color: rgba(255, 255, 255, 0.3); }
body:not(.dark-mode) .link-documentos-url-input { color: rgba(0, 0, 0, 0.85); }
body:not(.dark-mode) .link-documentos-url-input::placeholder { color: rgba(0, 0, 0, 0.4); }
.link-documentos-status {
    margin: -8px 0 14px;
    font-size: 12.5px;
    font-weight: 600;
    color: #34d399;
}
.link-documentos-status.is-expired { color: #f87171; }
body:not(.dark-mode) .link-documentos-status { color: #047857; }
body:not(.dark-mode) .link-documentos-status.is-expired { color: #b91c1c; }
.link-documentos-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
.link-documentos-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 18px;
    border-radius: 12px;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 500;
    letter-spacing: 0.01em;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.link-documentos-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.2s;
    background: white;
}
.link-documentos-btn:hover::before { opacity: 0.05; }
.link-documentos-btn:active::before { opacity: 0.1; }
.link-documentos-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
.link-documentos-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}
.link-documentos-btn-copy {
    background: rgba(139, 92, 246, 0.12);
    color: #a78bfa;
    border: 1px solid rgba(139, 92, 246, 0.2);
}
.link-documentos-btn-copy:hover {
    background: rgba(139, 92, 246, 0.18);
    border-color: rgba(139, 92, 246, 0.4);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.15);
}
.link-documentos-btn-open {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: #fff;
    border: 1px solid rgba(139, 92, 246, 0.3);
}
.link-documentos-btn-open:hover {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    transform: translateY(-1px);
    box-shadow: 0 6px 24px rgba(124, 58, 237, 0.35);
}
.link-documentos-btn-reactivar {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    border: 1px solid rgba(245, 158, 11, 0.35);
}
.link-documentos-btn-reactivar:hover {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    transform: translateY(-1px);
    box-shadow: 0 6px 24px rgba(217, 119, 6, 0.28);
}
.link-documentos-toast {
    position: fixed;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%) translateY(12px);
    background: #1e1e2e;
    border: 1px solid rgba(139, 92, 246, 0.25);
    color: #a78bfa;
    font-size: 13px;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 30px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none;
    white-space: nowrap;
    z-index: 9999;
}
.link-documentos-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* Cancelar: mantener estilo secundario visible en modo oscuro */
body.dark-mode #offcanvasAddCandidato .btn-outline-secondary { border-color: rgba(148, 163, 184, 0.5) !important; color: #94a3b8 !important; }
body.dark-mode #offcanvasAddCandidato .btn-outline-secondary:hover { background-color: rgba(71, 85, 105, 0.5) !important; border-color: #64748b !important; color: #e2e8f0 !important; }
/* Botones del formulario candidato */
#offcanvasAddCandidato #btnSubmitCandidato { min-width: 120px; font-weight: 600; }
#offcanvasAddCandidato .btn-outline-secondary:hover { background-color: #6c757d; color: #fff; }
.btn-action-size { height: 36px; padding: 0.375rem 0.75rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.375rem; }
/* Panel de indicadores Candidatos — mismos estilos que Gestión (kpi-toolbar, kpi-cell, kpi-num, kpi-lbl) */
#panelIndicadoresCandidatos .kpi-toolbar { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.65rem; flex-wrap:wrap; }
#panelIndicadoresCandidatos .kpi-toolbar-sep { width:1px; height:20px; background:rgba(99,102,241,0.12); flex-shrink:0; transition:opacity 0.28s ease, transform 0.33s cubic-bezier(0.4,0,0.2,1); }
#panelIndicadoresCandidatos .kpi-toolbar-sep.kpi-sep-hidden { opacity:0; transform:scaleY(0); pointer-events:none; }
#panelIndicadoresCandidatos #kpiViewControlsCand { display:flex; align-items:center; gap:0.5rem; flex-wrap:nowrap; overflow:hidden; max-width:700px; opacity:1; transform:translateX(0); transition:max-width 0.38s cubic-bezier(0.4,0,0.2,1), opacity 0.28s ease, transform 0.33s cubic-bezier(0.4,0,0.2,1); }
#panelIndicadoresCandidatos #kpiViewControlsCand.kpi-vc-hidden { max-width:0; opacity:0; transform:translateX(-22px); pointer-events:none; }
#panelIndicadoresCandidatos .kpi-view-btn { display:inline-flex; align-items:center; justify-content:center; gap:0.3rem; min-width:32px; height:32px; background:#fff; padding:0 0.5rem; border:1px solid rgba(99,102,241,0.12); border-radius:7px; cursor:pointer; color:#6b7280; font-size:0.75rem; font-weight:600; transition:all 0.2s; user-select:none; position:relative; }
#panelIndicadoresCandidatos .kpi-view-btn:hover { background:rgba(99,102,241,0.06); color:#6366f1; border-color:rgba(99,102,241,0.3); }
#panelIndicadoresCandidatos .kpi-view-btn.active { background:#6366f1; border-color:#6366f1; color:white; box-shadow:0 2px 8px rgba(99,102,241,0.3); }
#panelIndicadoresCandidatos .kpi-view-btn .kpi-btn-text { font-size:0.7rem; font-weight:600; white-space:nowrap; }
#panelIndicadoresCandidatos .kpi-reset-btn { display:inline-flex; align-items:center; gap:0.3rem; background:transparent; border:1px solid transparent; border-radius:7px; padding:0.38rem 0.6rem; cursor:pointer; font-size:0.72rem; font-weight:600; color:#6b7280; transition:all 0.2s; user-select:none; }
#panelIndicadoresCandidatos .kpi-reset-btn:hover { color:#ef4444; border-color:rgba(239,68,68,0.25); background:rgba(239,68,68,0.05); }
#panelIndicadoresCandidatos .kpi-cell-title { display:none; }
#panelIndicadoresCandidatos .kpi-stats-grid-new { display:none; grid-template-columns:1fr; align-items:center; margin-top:0.65rem; }
#panelIndicadoresCandidatos .kpi-stat-val { font-size:1.85rem; font-weight:700; color:var(--cell-num); line-height:1; }
#panelIndicadoresCandidatos .kpi-stat-lbl { font-size:0.62rem; font-weight:500; color:#6b7280; margin-top:0.2rem; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-cell-top { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-cell-top .kpi-cell-status { display:none; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-icon-wrap { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-corner-icon { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-cell { display:flex !important; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1.5rem 1.25rem !important; min-height:160px; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-cell-title { font-size:0.68rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.08em; display:block; margin-bottom:0.75rem; line-height:1.2; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-num { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-lbl { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-bar-track { display:block !important; margin-top:0.75rem; padding-top:0.5rem; width:100%; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .donut-block { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-stats-grid-new { display:flex !important; flex-direction:column; align-items:center; gap:0.25rem; margin-top:0; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-stat-val { font-size:2.25rem; font-weight:800; color:var(--cell-num); line-height:1; letter-spacing:-0.02em; }
#panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-stat-lbl { font-size:0.7rem; font-weight:500; color:#6b7280; margin-top:0.15rem; }
#panelIndicadoresCandidatos .kpi-row-new.mode-vision .kpi-cell { padding:1.1rem 1.25rem 1rem; min-height:unset; }
#panelIndicadoresCandidatos .kpi-row-new.mode-vision .kpi-cell-top { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-vision .kpi-num { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-vision .kpi-lbl { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-vision .kpi-bar-track { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-vision .kpi-stats-grid-new { display:none !important; }
#panelIndicadoresCandidatos .kpi-row-new.mode-vision .donut-block { display:flex !important; flex-direction:column; align-items:center; gap:0.65rem; }
#panelIndicadoresCandidatos .donut-block { display:none; }
#panelIndicadoresCandidatos .donut-header { width:100%; display:flex; align-items:center; justify-content:space-between; }
#panelIndicadoresCandidatos .donut-title { font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.07em; color:#6b7280; }
#panelIndicadoresCandidatos .donut-svg-wrap { position:relative; display:inline-flex; align-items:center; justify-content:center; width:96px; height:96px; }
#panelIndicadoresCandidatos .donut-svg { width:96px; height:96px; transform:rotate(-90deg); overflow:visible; }
#panelIndicadoresCandidatos .donut-track { fill:none; stroke:color-mix(in srgb,var(--cell-icon) 12%,transparent); stroke-width:8; stroke-linecap:round; }
#panelIndicadoresCandidatos .donut-arc { fill:none; stroke:var(--cell-icon); stroke-width:8; stroke-linecap:round; stroke-dasharray:0 226.2; transition:stroke-dasharray 1.1s cubic-bezier(0.4,0,0.2,1); filter:drop-shadow(0 0 4px color-mix(in srgb,var(--cell-icon) 40%,transparent)); }
#panelIndicadoresCandidatos .donut-center-icon { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:1.35rem; color:var(--cell-icon); }
#panelIndicadoresCandidatos .donut-stats { display:grid; grid-template-columns:1fr; align-items:center; width:100%; margin-top:0.35rem; padding-top:0.35rem; border-top:1px solid color-mix(in srgb,var(--cell-icon) 12%,transparent); }
#panelIndicadoresCandidatos .kpi-toggle-btn {
    display:inline-flex; align-items:center; gap:0.35rem;
    background:#fff; border:1px solid rgba(99,102,241,0.18); border-radius:8px;
    padding:0.38rem 0.85rem; cursor:pointer;
    font-size:0.78rem; font-weight:600; color:#6366f1;
    box-shadow:0 1px 4px rgba(99,102,241,0.07);
    transition:background 0.2s, box-shadow 0.2s;
    user-select:none; white-space:nowrap;
}
#panelIndicadoresCandidatos .kpi-toggle-btn:hover { background:rgba(99,102,241,0.06); box-shadow:0 2px 10px rgba(99,102,241,0.13); }
#panelIndicadoresCandidatos .kpi-toggle-btn .kpi-chevron { font-size:1rem; transition:transform 0.35s cubic-bezier(0.4,0,0.2,1); display:flex; }
#panelIndicadoresCandidatos .kpi-toggle-btn.open .kpi-chevron { transform:rotate(180deg); }
#panelIndicadoresCandidatos .kpi-toggle-btn .kpi-dot { width:6px; height:6px; border-radius:50%; background:#6366f1; flex-shrink:0; }
#panelIndicadoresCandidatos .kpi-collapsible { display:grid; grid-template-rows:0fr; transition:grid-template-rows 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.35s ease; opacity:0; }
#panelIndicadoresCandidatos .kpi-collapsible.open { grid-template-rows:1fr; opacity:1; }
#panelIndicadoresCandidatos .kpi-collapsible-inner { overflow:hidden; }
#panelIndicadoresCandidatos .kpi-row-new { display:grid; grid-template-columns:repeat(3,1fr); gap:0.85rem; padding-bottom:0.25rem; }
#panelIndicadoresCandidatos .kpi-cell {
    background:#fff; border-radius:14px;
    border:1px solid rgba(99,102,241,0.12);
    box-shadow:0 2px 16px rgba(99,102,241,0.08),0 1px 4px rgba(0,0,0,0.04), inset 4px 0 0 var(--cell-accent);
    position:relative; overflow:hidden; cursor:pointer;
    min-height:190px;
    transition:transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s, border-color 0.25s;
}
#panelIndicadoresCandidatos .kpi-cell.revealed { animation:kpiCellRevealCand 0.45s cubic-bezier(0.34,1.3,0.64,1) forwards; }
@keyframes kpiCellRevealCand {
    from { opacity:0; transform:translateY(14px) scale(0.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
#panelIndicadoresCandidatos .kpi-cell:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(99,102,241,0.13),0 2px 8px rgba(0,0,0,0.06),inset 4px 0 0 var(--cell-accent); border-color:rgba(99,102,241,0.28); }
#panelIndicadoresCandidatos .kpi-cell.tipo-dep    { --cell-accent:#6366f1; --cell-glow:rgba(99,102,241,0.06);  --cell-icon:#6366f1; --cell-num:#4f46e5; }
#panelIndicadoresCandidatos .kpi-cell.tipo-puesto { --cell-accent:#10b981; --cell-glow:rgba(16,185,129,0.06); --cell-icon:#10b981; --cell-num:#059669; }
#panelIndicadoresCandidatos .kpi-cell.tipo-total  { --cell-accent:#f59e0b; --cell-glow:rgba(245,158,11,0.07);  --cell-icon:#f59e0b; --cell-num:#d97706; }
#panelIndicadoresCandidatos .kpi-cell-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:0.85rem; }
#panelIndicadoresCandidatos .kpi-icon-wrap {
    border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;
    transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
}
#panelIndicadoresCandidatos .kpi-icon-wrap i { font-family:'boxicons' !important; font-style:normal; font-weight:400 !important; font-size:inherit !important; color:inherit !important; line-height:1; display:inline-block !important; }
#panelIndicadoresCandidatos .kpi-cell.tipo-dep    .kpi-icon-wrap { background:#ede9fe; border:1px solid #c4b5fd; color:#6366f1; box-shadow:0 3px 10px rgba(99,102,241,0.28); }
#panelIndicadoresCandidatos .kpi-cell.tipo-puesto .kpi-icon-wrap { background:#d1fae5; border:1px solid #6ee7b7; color:#059669; box-shadow:0 3px 10px rgba(16,185,129,0.28); }
#panelIndicadoresCandidatos .kpi-cell.tipo-total  .kpi-icon-wrap { background:#fef3c7; border:1px solid #fcd34d; color:#d97706; box-shadow:0 3px 10px rgba(245,158,11,0.28); }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-icon-wrap { display:none !important; }
#panelIndicadoresCandidatos .kpi-corner-icon {
    position:absolute; top:6px; left:8px;
    font-size:4.2rem; line-height:1;
    color:var(--cell-icon); opacity:0.07;
    pointer-events:none; user-select:none; z-index:0;
    transition:opacity 0.4s ease, transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
}
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-corner-icon { display:none !important; }
#panelIndicadoresCandidatos .kpi-cell-status {
    font-size:0.62rem; font-weight:600; letter-spacing:0.06em; text-transform:uppercase;
    color:var(--cell-icon); background:color-mix(in srgb,var(--cell-icon) 10%,transparent);
    border-radius:20px; padding:0.18rem 0.55rem; opacity:0.85;
}
#panelIndicadoresCandidatos .kpi-num { font-size:1.85rem; font-weight:700; line-height:1; color:var(--cell-num); }
#panelIndicadoresCandidatos .kpi-lbl { font-size:0.73rem; font-weight:500; color:#6b7280; margin-top:0.3rem; }
#panelIndicadoresCandidatos .kpi-bar-track { margin-top:0.9rem; height:3px; background:color-mix(in srgb,var(--cell-icon) 12%,transparent); border-radius:99px; overflow:hidden; }
#panelIndicadoresCandidatos .kpi-bar-fill  { height:100%; width:0%; background:var(--cell-icon); border-radius:99px; transition:width 1s cubic-bezier(0.4,0,0.2,1) 0.3s; }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-cell { padding:1.15rem 1.25rem 1.05rem; }
@media (max-width: 767px) {
    #panelIndicadoresCandidatos .kpi-row-new { grid-template-columns:1fr 1fr; }
    #panelIndicadoresCandidatos .kpi-num { font-size:1.5rem; }
}
@media (max-width: 480px) {
    #panelIndicadoresCandidatos .kpi-row-new { grid-template-columns:1fr; }
}
body.dark-mode #panelIndicadoresCandidatos .kpi-cell { background:#1a1d2e; border-color:rgba(99,102,241,0.18); box-shadow:0 2px 16px rgba(0,0,0,0.35),0 1px 4px rgba(0,0,0,0.2),inset 4px 0 0 var(--cell-accent); }
body.dark-mode #panelIndicadoresCandidatos .kpi-toggle-btn { background:#1a1d2e; border-color:rgba(99,102,241,0.35); color:#a5b4fc; }
body.dark-mode #panelIndicadoresCandidatos .kpi-toggle-btn:hover { background:rgba(99,102,241,0.12); border-color:rgba(99,102,241,0.5); color:#c7d2fe; }
body.dark-mode #panelIndicadoresCandidatos .kpi-toolbar-sep { background:rgba(148,163,184,0.25); }
body.dark-mode #panelIndicadoresCandidatos .kpi-view-btn { background:#1a1d2e; border-color:rgba(99,102,241,0.25); color:#94a3b8; }
body.dark-mode #panelIndicadoresCandidatos .kpi-view-btn:hover { background:rgba(99,102,241,0.15); color:#c7d2fe; border-color:rgba(99,102,241,0.4); }
body.dark-mode #panelIndicadoresCandidatos .kpi-view-btn.active { background:#6366f1; border-color:#6366f1; color:#fff; box-shadow:0 2px 12px rgba(99,102,241,0.4); }
body.dark-mode #panelIndicadoresCandidatos .kpi-view-btn.active .kpi-btn-text { color:#fff; }
body.dark-mode #panelIndicadoresCandidatos .kpi-reset-btn { color:#94a3b8; }
body.dark-mode #panelIndicadoresCandidatos .kpi-reset-btn:hover { color:#f87171; background:rgba(239,68,68,0.12); border-color:rgba(239,68,68,0.35); }
body.dark-mode #panelIndicadoresCandidatos .kpi-lbl { color:#8b90b0; }
body.dark-mode #panelIndicadoresCandidatos .kpi-cell-title { color:#8b90b0; }
body.dark-mode #panelIndicadoresCandidatos .kpi-stat-lbl { color:#8b90b0; }
body.dark-mode #panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-cell-title { color:#94a3b8; }
body.dark-mode #panelIndicadoresCandidatos .kpi-row-new.mode-ministat .kpi-stat-lbl { color:#94a3b8; }
.filtros-candidatos-header > h5.card-title { display:none; }
.filtros-candidatos-header > .d-flex {
    display:flex !important;
    margin-bottom:.85rem;
}
.filtros-candidatos-header .card-title {
    font-size:1.25rem;
    font-weight:800;
    color:#26364b;
}
.filtros-candidatos-label {
    margin:.9rem 0 .45rem;
    font-size:.72rem;
    font-weight:800;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:.06em;
}
.filtros-candidatos-header .form-label { margin-bottom:.35rem; letter-spacing:0; }
.filtros-candidatos-header .form-select { min-height:38px; }
.filtros-candidatos-header { padding:1.05rem 1.25rem 1rem !important; }
.filtros-candidatos-header .row { padding-top:0 !important; }
.filtros-candidatos-header .form-label { display:none; }
.filtros-candidatos-header .form-select {
    height:38px;
    font-size:.88rem;
    border-radius:8px;
}
.filtros-candidatos-header #btnLimpiarFiltrosCandidatos {
    height:32px;
    min-width:86px;
    border-radius:8px;
    font-size:.78rem;
    font-weight:700;
    padding:0 .8rem;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.2rem;
}
.filtros-clear-btn {
    width:38px;
    height:38px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    padding:0;
}
#panelIndicadoresCandidatos .kpi-toolbar { display:none !important; }
#panelIndicadoresCandidatos { padding-top:.75rem !important; padding-bottom:.35rem !important; }
#panelIndicadoresCandidatos .kpi-row-new { grid-template-columns:repeat(4,minmax(0,1fr)); gap:.7rem; }
#panelIndicadoresCandidatos .kpi-row-new.kpi-solo-final { grid-template-columns:repeat(3,minmax(0,1fr)); }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-cell {
    min-height:94px;
    padding:.8rem .95rem;
    border-radius:8px;
    box-shadow:0 1px 6px rgba(15,23,42,.06), inset 3px 0 0 var(--cell-accent);
    cursor:default;
}
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-icon-wrap {
    display:flex !important;
    width:auto;
    height:auto;
    font-size:1.2rem;
    background:transparent !important;
    border:0 !important;
    box-shadow:none !important;
}
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-cell-top { align-items:center; margin-bottom:.55rem; }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-bar-track { display:none; }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-num { font-size:1.65rem; line-height:1; }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-lbl {
    font-size:.78rem;
    font-weight:700;
    color:#4b5563;
    margin-top:.28rem;
}
#panelIndicadoresCandidatos .kpi-cell-status {
    font-size:.58rem;
    padding:.14rem .48rem;
    letter-spacing:.06em;
}
#panelIndicadoresCandidatos .kpi-cell:hover {
    transform:none;
    box-shadow:0 1px 6px rgba(15,23,42,.06), inset 3px 0 0 var(--cell-accent);
}
#panelIndicadoresCandidatos .kpi-cell.tipo-final { --cell-accent:#8b5cf6; --cell-glow:rgba(139,92,246,0.06); --cell-icon:#8b5cf6; --cell-num:#7c3aed; }
@media (max-width: 991px) {
    #panelIndicadoresCandidatos .kpi-row-new { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
/* Modo oscuro - Offcanvas Nuevo Candidato */
body.dark-mode #offcanvasAddCandidato { background: #1e293b; border-left: 1px solid rgba(148, 163, 184, 0.2); }
body.dark-mode #offcanvasAddCandidato .offcanvas-header { border-bottom-color: rgba(148, 163, 184, 0.2); }
body.dark-mode #offcanvasAddCandidato .offcanvas-title { color: #f1f5f9; }
body.dark-mode #offcanvasAddCandidato .form-label { color: #cbd5e1; }
body.dark-mode #offcanvasAddCandidato .form-control,
body.dark-mode #offcanvasAddCandidato .form-select { background: #334155; border-color: rgba(148, 163, 184, 0.3); color: #f1f5f9; }
body.dark-mode #offcanvasAddCandidato .form-control::placeholder { color: #94a3b8; }
body.dark-mode #offcanvasAddCandidato .form-control:focus,
body.dark-mode #offcanvasAddCandidato .form-select:focus { background: #475569; border-color: #6366f1; color: #f1f5f9; }
body.dark-mode #offcanvasAddCandidato .form-check-label { color: #cbd5e1; }
body.dark-mode #offcanvasAddCandidato .text-danger { color: #f87171 !important; }
/* Modo oscuro - Modal Resumen Postulación */
body.dark-mode #modalResumenPostulacion .modal-content { background: #1e293b; border: 1px solid rgba(148, 163, 184, 0.2); }
body.dark-mode #modalResumenPostulacion .modal-header { border-bottom-color: rgba(148, 163, 184, 0.2); }
body.dark-mode #modalResumenPostulacion .modal-title { color: #f1f5f9; }
body.dark-mode #modalResumenPostulacion .modal-body { color: #e2e8f0; }
body.dark-mode #modalResumenPostulacion .btn-close { filter: none; }

/* Modal Cerrar proceso: por encima de Documentación y de su scrim.
   Orden: Documentación (1090) → scrim (1094/10049) → modal Cerrar (99999 por JS/CSS). */
#modalCerrarProcesoCandidato.modal,
#modalCerrarProcesoCandidato.modal.show { z-index: 99999 !important; }
body.dark-mode #modalCerrarProcesoCandidato.modal.show { z-index: 99999 !important; }
#modalCerrarProcesoCandidato .modal-dialog { position: relative; z-index: 1 !important; }
/* Mantener Documentación por debajo del scrim de Cerrar cuando hay dos modales */
#modalDocumentacionCandidato.modal.show { z-index: 1090 !important; }
#modalAnalisisCruzadoCandidato.modal.show { z-index: 1120 !important; }
#modalAnalisisCruzadoCandidato .modal-body { background: #f8fafc; }
#modalAnalisisCruzadoCandidato .doc-v2-full { background: transparent; }
#modalVisorDocumentoCandidato.modal.show { z-index: 1110 !important; }
#modalVisorDocumentoCandidato .visor-doc-candidato-content {
    height: min(88vh, 900px);
    overflow: visible;
}
#modalVisorDocumentoCandidato .modal-header .btn-close {
    position: absolute;
    top: 10px;
    right: 12px;
    z-index: 4;
    width: 30px;
    height: 30px;
    margin: 0;
    padding: 0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef1f5 none !important;
    border: 1px solid #d8dee8;
    box-shadow: none;
    opacity: 0.9;
    filter: none;
    color: #64748b;
}
#modalVisorDocumentoCandidato .modal-header .btn-close::before {
    content: "\00d7";
    display: block;
    font-size: 21px;
    font-weight: 500;
    line-height: 1;
    transform: translateY(-1px);
}
#modalVisorDocumentoCandidato .modal-header .btn-close:hover {
    background-color: #e2e8f0;
    opacity: 1;
}
#modalVisorDocumentoCandidato .modal-body {
    height: calc(100% - 48px);
    overflow: hidden;
    background: #f8fafc;
}
#modalVisorDocumentoCandidato .visor-doc-candidato-frame {
    display: block;
    width: 100%;
    height: 100%;
    min-height: 68vh;
    border: 0;
    background: #fff;
}
#modalVisorDocumentoCandidato .visor-doc-candidato-loading {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(248, 250, 252, 0.92);
    color: #24324a;
    font-weight: 600;
}
body.dark-mode #modalVisorDocumentoCandidato .modal-content { background: #111827; color: #e5e7eb; }
body.dark-mode #modalVisorDocumentoCandidato .modal-body { background: #0f172a; }
body.dark-mode #modalVisorDocumentoCandidato .visor-doc-candidato-loading { background: rgba(15, 23, 42, 0.92); color: #e5e7eb; }
#modalDocumentacionCandidato .doc-sueldo-card .input-group-text { min-width: 38px; justify-content: center; }
#modalDocumentacionCandidato .doc-sueldo-card .btn-eye-sueldo { width: 38px; }
#modalDocumentacionCandidato .doc-sueldo-card input[disabled] { background-color: #f8fafc; }
#modalDocumentacionCandidato .modal-doc-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}
#modalDocumentacionCandidato .modal-doc-toolbar #modalDocumentacionCandidatoNombre {
    min-width: 0;
}
#modalDocumentacionCandidato .modal-doc-toolbar #btnDescargarExpedienteCandidatoZip {
    flex-shrink: 0;
}
@media (max-width: 576px) {
    #modalDocumentacionCandidato .modal-doc-toolbar {
        align-items: flex-start;
        flex-direction: column;
    }
}
#modalHistoricoCandidatos .historico-search {
    width: min(320px, 100%);
    border: 0 !important;
    box-shadow: none !important;
    background: transparent;
}
#modalHistoricoCandidatos .historico-search .form-control {
    width: 100%;
    height:34px;
    border-radius:8px !important;
    border:1px solid #cbd5e1;
    padding-left:.85rem;
    box-shadow:none;
}
#modalHistoricoCandidatos .historico-search .form-control:focus {
    border-color:#26364b;
}
#modalHistoricoCandidatos .historico-list { display: flex; flex-direction: column; gap: .65rem; }
#modalHistoricoCandidatos .historico-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .85rem 1rem;
    background: #fff;
}
#modalHistoricoCandidatos .historico-title {
    color: #334155;
    font-size: .9rem;
    font-weight: 800;
    text-transform: uppercase;
}
#modalHistoricoCandidatos .historico-meta {
    color: #64748b;
    font-size: .8rem;
    font-weight: 600;
}
#modalHistoricoCandidatos .historico-motivo {
    color: #475569;
    font-size: .8rem;
    background: #f8fafc;
    border-radius: 6px;
    padding: .35rem .5rem;
}
#modalBitacoraCandidato .candidate-timeline { position: relative; padding-left: 1.35rem; }
#modalBitacoraCandidato .candidate-timeline::before {
    content: "";
    position: absolute;
    left: .45rem;
    top: .35rem;
    bottom: .35rem;
    border-left: 2px dashed #cbd5e1;
}
#modalBitacoraCandidato .timeline-item {
    position: relative;
    padding: 0 0 1.25rem 1rem;
}
#modalBitacoraCandidato .timeline-dot {
    position: absolute;
    left: -.98rem;
    top: .28rem;
    width: .82rem;
    height: .82rem;
    border-radius: 50%;
    background: #fff;
    border: 3px solid var(--timeline-color, #0ea5e9);
}
#modalBitacoraCandidato .timeline-title {
    color: #253244;
    font-size: .88rem;
    font-weight: 800;
    letter-spacing: 0;
    text-transform: uppercase;
}
#modalBitacoraCandidato .timeline-date {
    color: #64748b;
    font-size: .78rem;
    font-weight: 700;
    white-space: nowrap;
}
#modalBitacoraCandidato .timeline-desc {
    color: #64748b;
    font-size: .82rem;
    font-weight: 600;
    line-height: 1.35;
}
#modalBitacoraCandidato .timeline-chip {
    display: inline-flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 999px;
    color: #334155;
    font-size: .76rem;
    font-weight: 700;
    padding: .25rem .55rem;
    margin-top: .45rem;
}

#modalBitacoraCandidato .candidate-detail-tabs .nav-link {
    color: #64748b;
    font-size: .84rem;
    font-weight: 800;
}
#modalBitacoraCandidato .candidate-detail-tabs .nav-link.active { color: #25324a; }
#modalBitacoraCandidato .metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .7rem;
    margin-bottom: .9rem;
}
#modalBitacoraCandidato .metric-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    padding: .75rem;
    min-height: 88px;
}
#modalBitacoraCandidato .metric-label {
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
}
#modalBitacoraCandidato .metric-value {
    color: #253244;
    font-size: 1.05rem;
    font-weight: 900;
    margin-top: .25rem;
    line-height: 1.15;
}
#modalBitacoraCandidato .metric-sub {
    color: #64748b;
    font-size: .74rem;
    font-weight: 700;
    margin-top: .2rem;
}
#modalBitacoraCandidato .metric-section-title {
    color: #334155;
    font-size: .82rem;
    font-weight: 900;
    text-transform: uppercase;
    margin: .95rem 0 .45rem;
}
#modalBitacoraCandidato .metric-step {
    border: 1px solid #e2e8f0;
    border-left: 4px solid var(--metric-color, #2563eb);
    border-radius: 8px;
    padding: .65rem .75rem;
    margin-bottom: .55rem;
    background: #fff;
}
#modalBitacoraCandidato .metric-step.slowest {
    background: #fff7ed;
    border-color: #fed7aa;
    border-left-color: #f59e0b;
}
#modalBitacoraCandidato .metric-step-title {
    color: #253244;
    font-size: .82rem;
    font-weight: 900;
}
#modalBitacoraCandidato .metric-step-meta {
    color: #64748b;
    font-size: .76rem;
    font-weight: 700;
    margin-top: .2rem;
}
#modalBitacoraCandidato .metric-insight {
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    border-radius: 8px;
    color: #1e3a8a;
    font-size: .8rem;
    font-weight: 700;
    padding: .65rem .75rem;
}
@media (max-width: 768px) {
    #modalBitacoraCandidato .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 480px) {
    #modalBitacoraCandidato .metric-grid { grid-template-columns: 1fr; }
}
#modalHistoricoCandidatos .candidate-detail-tabs .nav-link {
    color: #64748b;
    font-size: .84rem;
    font-weight: 800;
}
#modalHistoricoCandidatos .candidate-detail-tabs .nav-link.active { color: #25324a; }
#modalHistoricoCandidatos .metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .7rem;
    margin-bottom: .9rem;
}
#modalHistoricoCandidatos .metric-card,
#modalHistoricoCandidatos .metric-step {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
}
#modalHistoricoCandidatos .metric-card {
    padding: .75rem;
    min-height: 88px;
}
#modalHistoricoCandidatos .metric-label {
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
}
#modalHistoricoCandidatos .metric-value {
    color: #253244;
    font-size: 1.05rem;
    font-weight: 900;
    margin-top: .25rem;
    line-height: 1.15;
}
#modalHistoricoCandidatos .metric-sub,
#modalHistoricoCandidatos .metric-step-meta {
    color: #64748b;
    font-size: .74rem;
    font-weight: 700;
    margin-top: .2rem;
}
#modalHistoricoCandidatos .metric-section-title {
    color: #334155;
    font-size: .82rem;
    font-weight: 900;
    text-transform: uppercase;
    margin: .95rem 0 .45rem;
}
#modalHistoricoCandidatos .metric-step {
    border-left: 4px solid var(--metric-color, #2563eb);
    padding: .65rem .75rem;
    margin-bottom: .55rem;
}
#modalHistoricoCandidatos .metric-step-title {
    color: #253244;
    font-size: .82rem;
    font-weight: 900;
}
#modalHistoricoCandidatos .metric-insight {
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    border-radius: 8px;
    color: #1e3a8a;
    font-size: .8rem;
    font-weight: 700;
    padding: .65rem .75rem;
}
#modalHistoricoCandidatos .metric-bar {
    height: 8px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
    margin-top: .45rem;
}
#modalHistoricoCandidatos .metric-bar-fill {
    height: 100%;
    width: var(--metric-width, 0%);
    background: var(--metric-color, #2563eb);
}
@media (max-width: 768px) {
    #modalHistoricoCandidatos .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 480px) {
    #modalHistoricoCandidatos .metric-grid { grid-template-columns: 1fr; }
}

/* Modal Cerrar proceso: identidad visual distinta al de Documentación (diálogo de acción, no panel) */
#modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-dialog { max-width: 420px; }
/* Liquid Glass + acento rojo (modo claro) */
#modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-content {
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.92) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 24px 48px rgba(0,0,0,0.12), 0 0 0 1px rgba(255,255,255,0.4) inset, 0 0 0 1px rgba(220, 53, 69, 0.12);
    overflow: visible;
}
#modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-header {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.04) 100%);
    border-bottom: 2px solid rgba(220, 53, 69, 0.3);
    padding: 1rem 1.25rem;
    overflow: visible;
}
#modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-title {
    color: #b91c1c;
    font-weight: 600;
    font-size: 1.1rem;
}
#modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-title .fa-times-circle { opacity: 0.9; }
#modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-body {
    padding: 1.25rem 1.25rem 1rem;
    background: transparent;
}
#modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer {
    background: rgba(248, 249, 250, 0.8);
    border-top: 1px solid rgba(220, 53, 69, 0.12);
    padding: 1rem 1.25rem;
    gap: 0.5rem;
}
/* Modo oscuro: Liquid Glass + mismos colores “bonitos” que en claro (rojo/ámbar) */
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-content {
    background: rgba(30, 41, 59, 0.92) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-color: rgba(71, 85, 105, 0.6);
    box-shadow: 0 24px 48px rgba(0,0,0,0.4), 0 0 0 1px rgba(51, 65, 85, 0.4) inset, 0 0 0 1px rgba(248, 113, 113, 0.2);
}
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-header {
    background: linear-gradient(135deg, rgba(248, 113, 113, 0.18) 0%, rgba(220, 38, 38, 0.08) 100%);
    border-bottom-color: rgba(248, 113, 113, 0.4);
}
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-title { color: #fca5a5; }
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-body {
    background: transparent;
    color: #e2e8f0;
}
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-body .text-muted { color: #94a3b8 !important; }
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer {
    background: rgba(15, 23, 42, 0.7);
    border-top-color: rgba(248, 113, 113, 0.2);
}
/* Botón cerrar (X): que no sea negro puro en oscuro, estilo glass */
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-header .btn-close {
    background: rgba(248, 113, 113, 0.2) !important;
    border: 1px solid rgba(248, 113, 113, 0.35) !important;
    border-radius: 8px;
    opacity: 1;
    filter: none;
    color: #fca5a5;
    --bs-btn-close-color: #fca5a5;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fca5a5'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") !important;
}
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-header .btn-close:hover {
    background: rgba(248, 113, 113, 0.35) !important;
    border-color: rgba(248, 113, 113, 0.5) !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fecaca'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") !important;
}
/* Cancelar: estilo “orange-gold” en oscuro como en claro */
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer .btn-outline-secondary {
    color: #fbbf24 !important;
    border-color: #f59e0b !important;
    background: transparent !important;
}
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer .btn-outline-secondary:hover {
    color: #fcd34d !important;
    border-color: #fbbf24 !important;
    background: rgba(251, 191, 36, 0.15) !important;
}
/* Confirmar: rojo sólido en oscuro, igual que en claro */
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer .btn-outline-danger,
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer #btnConfirmarCerrarProceso {
    background: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #fff !important;
}
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer .btn-outline-danger:hover,
body.dark-mode #modalCerrarProcesoCandidato.modal-cerrar-proceso .modal-footer #btnConfirmarCerrarProceso:hover {
    background: #c82333 !important;
    border-color: #bd2130 !important;
    color: #fff !important;
}

/* Modal Resumen Candidato - mejora visual (modo claro y oscuro) */
.modal-resumen-candidato .modal-content { border-radius: 14px; overflow: visible; box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
.modal-resumen-candidato .resumen-candidato-modal-header { position: relative; padding: 1rem 1.25rem; padding-right: 2.75rem; border-bottom-width: 1px; }
.modal-resumen-candidato .resumen-candidato-modal-header .btn-close { position: absolute; top: -0.6rem; right: -0.6rem; margin: 0; width: 1.25rem; height: 1.25rem; border-radius: 6px; background-color: #fff; border: 1px solid rgba(0,0,0,0.12); box-shadow: 0 2px 6px rgba(0,0,0,0.1); opacity: 0.95; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; }
.modal-resumen-candidato .resumen-candidato-modal-header .btn-close:hover { opacity: 1; background-color: #f1f3f5; }
/* Modo oscuro: botón X gris con icono blanco, sin recorte */
body.dark-mode .modal-resumen-candidato .resumen-candidato-modal-header .btn-close { background-color: #475569 !important; border-color: rgba(148, 163, 184, 0.45) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.35); color: #f1f5f9; opacity: 1; --bs-btn-close-color: #f1f5f9; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23f1f5f9'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") !important; }
body.dark-mode .modal-resumen-candidato .resumen-candidato-modal-header .btn-close:hover { background-color: #64748b !important; color: #fff; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") !important; }
.modal-resumen-candidato .resumen-candidato-modal-body { padding: 1.25rem 1.5rem; border-radius: 0 0 14px 14px; overflow: hidden; }
.resumen-candidato-card { border-radius: 12px; padding: 1rem 1.25rem; background: rgba(105, 108, 255, 0.06); border: 1px solid rgba(105, 108, 255, 0.15); }
.resumen-candidato-card .resumen-row { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.5rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.resumen-candidato-card .resumen-row:last-child { border-bottom: none; }
.resumen-candidato-card .resumen-label { font-size: 0.8rem; font-weight: 500; color: #6c757d; min-width: 100px; }
.resumen-candidato-card .resumen-value { font-size: 0.95rem; font-weight: 600; color: #212529; }
.btn-enviar-postulacion { font-weight: 600; border-radius: 10px; min-height: 44px; transition: transform 0.15s ease, box-shadow 0.15s ease; }
.btn-enviar-postulacion:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(105, 108, 255, 0.35); }
body.dark-mode .resumen-candidato-card { background: rgba(99, 102, 241, 0.12); border-color: rgba(148, 163, 184, 0.2); }
body.dark-mode .resumen-candidato-card .resumen-row { border-bottom-color: rgba(148, 163, 184, 0.15); }
body.dark-mode .resumen-candidato-card .resumen-label { color: #94a3b8; }
body.dark-mode .resumen-candidato-card .resumen-value { color: #f1f5f9; }
body.dark-mode .btn-enviar-postulacion:hover { box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4); }
@media (max-width: 576px) {
    .modal-resumen-candidato .resumen-candidato-modal-body .d-flex { justify-content: stretch !important; }
    .btn-enviar-postulacion { width: 100%; justify-content: center; }
}
/* Calendario Flatpickr - solo hasta hoy; modo claro legible */
.fecha-postulacion-wrapper.fecha-acta-wrapper { position: relative; z-index: 1; }
#candidato_fecha_postulacion { width: 100%; cursor: pointer; }
.flatpickr-calendar .flatpickr-monthDropdown-months { appearance: none !important; background-image: none !important; -webkit-appearance: none; -moz-appearance: none; }
.flatpickr-calendar { z-index: 99999 !important; position: fixed !important; transform: scale(1.12); transform-origin: top left; background: #fff !important; border: 1px solid rgba(0,0,0,0.12) !important; box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important; }
.flatpickr-calendar.open { display: block !important; visibility: visible !important; opacity: 1 !important; }
body:not(.dark-mode) .flatpickr-calendar { background: #fff !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day { color: #374151 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.flatpickr-disabled { color: #9ca3af !important; background: #f3f4f6 !important; cursor: not-allowed !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.selected { background: #696cff !important; border-color: #696cff !important; color: #fff !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.today { border-color: #696cff !important; border-width: 2px !important; font-weight: 600 !important; background: #eef2ff !important; color: #4338ca !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.today:hover { background: #c7d2fe !important; border-color: #696cff !important; color: #3730a3 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day:hover:not(.flatpickr-disabled) { background: #f3f4f6 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-current-month { color: #111827 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-weekdays { color: #6b7280 !important; }
.flatpickr-calendar .flatpickr-day.today { border-color: #696cff !important; border-width: 2px !important; font-weight: 600 !important; background-color: #f0f0ff !important; }
.flatpickr-calendar .flatpickr-day.today:hover { background-color: #e0e0ff !important; border-color: #696cff !important; }
#tablaCandidatos thead th { background-color: rgba(105, 108, 255, 0.1); font-weight: 600; }
#tablaCandidatos th.col-acciones-candidatos { min-width: 270px; }
#tablaCandidatos .btn-accion-candidato { white-space: nowrap; }
</style>

<script>
window.puedeGestionarCandidatos = <?= json_encode(!empty($puedeGestionarCandidatos ?? false)) ?>;
</script>
