<?php
$c = isset($tickets_panel_categoria) ? preg_replace('/[^a-z0-9_]/', '', (string) $tickets_panel_categoria) : '';
$t = \Core\PanelAdminTicketTable::getTitulosColumnasPanelAdminPorCategoria($c);
$titulo = isset($tickets_panel_titulo) ? htmlspecialchars($tickets_panel_titulo, ENT_QUOTES, 'UTF-8') : 'Tickets';
$icono = isset($tickets_panel_icono) ? htmlspecialchars($tickets_panel_icono, ENT_QUOTES, 'UTF-8') : 'fa-list';
$iconoColor = isset($tickets_panel_icono_color) ? htmlspecialchars($tickets_panel_icono_color, ENT_QUOTES, 'UTF-8') : 'text-primary';
$mostrarFormularios = !empty($tickets_panel_formularios);
?>
<style>
    #tablaTicketsModulo.table thead th { font-size: 0.78rem; letter-spacing: 0.02em; }
    #tablaTicketsModulo.table tbody td { font-size: 0.8125rem; }
    #tablaTicketsModulo.table tbody td .small, #tablaTicketsModulo.table tbody td small { font-size: 0.72rem; }
    #tablaTicketsModulo.table tbody td:last-child .btn { padding: 0.25rem 0.5rem; font-size: 0.8125rem; }
    #tablaTicketsModulo th:nth-child(6) { min-width: 10rem; white-space: nowrap; }
    body.panel-tickets-modulo-cargando #wrapTablaTicketsModulo { position: relative; min-height: 240px; }
    body.panel-tickets-modulo-cargando #tablaTicketsModulo { visibility: hidden !important; }
    body.panel-tickets-modulo-cargando #wrapTablaTicketsModulo::before {
        content: "Cargando tickets…";
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem; color: #6b7280;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.96));
        border-top: 1px solid #eef0f2; z-index: 2;
    }
    body.panel-tickets-modulo-cargando #tablaTicketsModulo_info,
    body.panel-tickets-modulo-cargando #tablaTicketsModulo_paginate { visibility: hidden !important; }
    /* Scrim entre modal Formularios (padre) y modales hijo (Tipo de pregunta / Editar): encima del padre */
    #modalFormulariosValidacion.modal-formularios-below-scrim { z-index: 1045 !important; }
    #scrimFormulariosModulo { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1060; pointer-events: auto; display: none; }
    #modalTipoPreguntaValidacion.modal.show, #modalEditarPreguntaValidacion.modal.show { z-index: 1100 !important; }
</style>
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0 d-flex flex-wrap align-items-center gap-2 w-100">
            <span class="me-auto flex-grow-1 min-w-0"><i class="fa-solid <?= $icono; ?> me-2 <?= $iconoColor; ?>"></i>Panel Admin – <?= $titulo; ?></span>
            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-sm-auto">
                <?php if ($mostrarFormularios): ?>
                <button type="button" class="btn btn-sm btn-outline-success" id="btnPanelAdminFormulariosValidacion" title="Formularios">
                    <i class="fa-solid fa-clipboard-list me-1"></i>Formularios
                </button>
                <?php endif; ?>
                <?php if (!empty($panel_admin_mostrar_volver) && !empty($panel_admin_url_inicio)): ?>
                <a href="<?= htmlspecialchars($panel_admin_url_inicio, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary" title="Volver a la selección de paneles">
                    <i class="fa-solid fa-arrow-left me-1"></i>Volver
                </a>
                <?php endif; ?>
            </div>
        </h5>
    </div>
    <div class="card-datatable table-responsive" id="wrapTablaTicketsModulo">
        <table id="tablaTicketsModulo" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th><?= htmlspecialchars($t['folio'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['estado'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['prioridad'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['ref'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['fechas'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['creador'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['asignado'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['tiempo'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?= htmlspecialchars($t['ds'], ENT_QUOTES, 'UTF-8'); ?></th>
                    <th></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?php if ($mostrarFormularios): ?>
<div id="scrimFormulariosModulo" aria-hidden="true" data-role="scrim-formularios-hijo"></div>
<div class="modal fade modal-formularios-parent" id="modalFormulariosValidacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #14532d 0%, #166534 100%);">
                <h5 class="modal-title text-white"><i class="fa-solid fa-clipboard-list me-2"></i>Formularios – Validaciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="small text-muted mb-3">Defina el cuestionario de validaciones. Marque o desmarque las preguntas que quiera incluir (predefinidas y personalizadas).</p>
                <div class="rounded-3 border bg-label-success bg-opacity-10 p-3 mb-3">
                    <h6 class="small text-uppercase text-muted mb-2"><i class="fa-solid fa-bookmark me-1"></i>Preguntas predefinidas</h6>
                    <p class="small text-muted mb-2">Incluir en cuestionario: marque la casilla.</p>
                    <ul class="list-group list-group-flush rounded-2 border" id="listaPreguntasPredefinidasValidacion">
                        <li class="list-group-item small text-muted fst-italic py-3 text-center">(Cargando…)</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <h6 class="small text-uppercase text-muted mb-0"><i class="fa-solid fa-plus-circle me-1"></i>Preguntas personalizadas</h6>
                    <button type="button" class="btn btn-sm btn-success" id="btnFormValidacionNuevasPreguntas"><i class="fa-solid fa-circle-plus me-1"></i>Nueva pregunta</button>
                </div>
                <p class="small text-muted mb-2">Marque para incluir en el cuestionario; desmarque si no aplica a todos los clientes.</p>
                <ul class="list-group" id="listaPreguntasNuevasValidacion"></ul>
                <p class="small text-muted mt-2 mb-0" id="formValidacionSinNuevas" style="display:none;">Aún no hay preguntas personalizadas.</p>
            </div>
            <div class="modal-footer border-top-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalTipoPreguntaValidacion" tabindex="-1" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title"><i class="fa-solid fa-list-check me-2 text-success"></i>Tipo de pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary text-start fp-btn-tipo" data-fp-tipo="abierta"><i class="fa-solid fa-pen-to-square me-2"></i>Pregunta abierta</button>
                    <button type="button" class="btn btn-outline-primary text-start fp-btn-tipo" data-fp-tipo="cerrada"><i class="fa-solid fa-circle-dot me-2"></i>Pregunta cerrada</button>
                    <button type="button" class="btn btn-outline-primary text-start fp-btn-tipo" data-fp-tipo="multiple"><i class="fa-solid fa-square-check me-2"></i>Selección múltiple</button>
                    <button type="button" class="btn btn-outline-secondary text-start fp-btn-tipo" data-fp-tipo="si_no"><i class="fa-solid fa-toggle-on me-2"></i>Sí / No</button>
                    <button type="button" class="btn btn-outline-secondary text-start fp-btn-tipo" data-fp-tipo="escala"><i class="fa-solid fa-sliders me-2"></i>Escala 1 a 5</button>
                    <button type="button" class="btn btn-outline-secondary text-start fp-btn-tipo" data-fp-tipo="fecha"><i class="fa-solid fa-calendar-day me-2"></i>Fecha</button>
                    <button type="button" class="btn btn-outline-secondary text-start fp-btn-tipo" data-fp-tipo="numero"><i class="fa-solid fa-hashtag me-2"></i>Número</button>
                </div>
            </div>
            <div class="modal-footer border-top-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Volver</button></div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEditarPreguntaValidacion" tabindex="-1" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title" id="modalEditarPreguntaValidacionLabel">Nueva pregunta</h5>
                <button type="button" class="btn-close" id="fpBtnCerrarEditorX"></button>
            </div>
            <div class="modal-body pt-0">
                <input type="hidden" id="fpTipoActual" value="">
                <div id="fpEditorError" class="alert alert-danger small py-2 d-none"></div>
                <div id="fpWrapAbierta" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoAbierta">Enunciado</label>
                    <textarea id="fpTextoAbierta" class="form-control" rows="5"></textarea>
                </div>
                <div id="fpWrapCerrada" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoCerrada">Pregunta</label>
                    <textarea id="fpTextoCerrada" class="form-control mb-3" rows="3"></textarea>
                    <label class="form-label small text-muted">Opciones (marque la correcta)</label>
                    <div id="fpListaOpcionesCerrada" class="d-flex flex-column gap-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-2" id="fpAgregarOpcionCerrada"><i class="fa-solid fa-plus me-1"></i>Opción</button>
                </div>
                <div id="fpWrapMultiple" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoMultiple">Pregunta</label>
                    <textarea id="fpTextoMultiple" class="form-control mb-3" rows="3"></textarea>
                    <div id="fpListaOpcionesMultiple" class="d-flex flex-column gap-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-2" id="fpAgregarOpcionMultiple"><i class="fa-solid fa-plus me-1"></i>Opción</button>
                </div>
                <div id="fpWrapSiNo" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoSiNo">Pregunta Sí/No</label>
                    <textarea id="fpTextoSiNo" class="form-control" rows="4"></textarea>
                </div>
                <div id="fpWrapEscala" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoEscala">Pregunta</label>
                    <textarea id="fpTextoEscala" class="form-control mb-3" rows="3"></textarea>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label small" for="fpEscalaMin">Etiqueta mín. (1)</label><input type="text" class="form-control form-control-sm" id="fpEscalaMin"></div>
                        <div class="col-md-6"><label class="form-label small" for="fpEscalaMax">Etiqueta máx. (5)</label><input type="text" class="form-control form-control-sm" id="fpEscalaMax"></div>
                    </div>
                </div>
                <div id="fpWrapFecha" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoFecha">Pregunta (fecha)</label>
                    <textarea id="fpTextoFecha" class="form-control" rows="4"></textarea>
                </div>
                <div id="fpWrapNumero" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoNumero">Pregunta</label>
                    <textarea id="fpTextoNumero" class="form-control mb-3" rows="3"></textarea>
                    <div class="row g-2">
                        <div class="col-6"><input type="number" class="form-control form-control-sm" id="fpNumMin" placeholder="Mín"></div>
                        <div class="col-6"><input type="number" class="form-control form-control-sm" id="fpNumMax" placeholder="Máx"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" id="fpBtnCancelarEditor">Cancelar</button>
                <button type="button" class="btn btn-success" id="fpBtnGuardarPregunta"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
