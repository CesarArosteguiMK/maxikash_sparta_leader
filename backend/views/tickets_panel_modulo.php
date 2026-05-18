<?php
$c = isset($tickets_panel_categoria) ? preg_replace('/[^a-z0-9_]/', '', (string) $tickets_panel_categoria) : '';
$t = \Core\PanelAdminTicketTable::getTitulosColumnasPanelAdminPorCategoria($c);
$titulo = isset($tickets_panel_titulo) ? htmlspecialchars($tickets_panel_titulo, ENT_QUOTES, 'UTF-8') : 'Tickets';
$tituloPrefijo = isset($tickets_panel_titulo_prefijo) ? htmlspecialchars($tickets_panel_titulo_prefijo, ENT_QUOTES, 'UTF-8') : 'Panel Admin – ';
$icono = isset($tickets_panel_icono) ? htmlspecialchars($tickets_panel_icono, ENT_QUOTES, 'UTF-8') : 'fa-list';
$iconoColor = isset($tickets_panel_icono_color) ? htmlspecialchars($tickets_panel_icono_color, ENT_QUOTES, 'UTF-8') : 'text-primary';
$mostrarFormularios = !empty($tickets_panel_formularios);
$mostrarModalFormBuilderLectura = !empty($tickets_panel_modal_form_builder_lectura);
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
    @keyframes tm-blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    .tm-countdown-blink { animation: tm-blink 1s ease-in-out infinite; }
    /* Scrim entre modal Formularios (padre) y modales hijo (Tipo de pregunta / Editar): encima del padre */
    #modalFormulariosValidacion.modal-formularios-below-scrim { z-index: 1045 !important; }
    /* Mismo estilo “label” que el resto del tema (verde suave, no chillón) */
    #modalFormulariosValidacion #btnFormularioValidacionCrear {
        font-weight: 600;
    }
    #modalFormulariosValidacion #btnFormularioValidacionCrear.btn-label-success:hover {
        box-shadow: 0 2px 8px rgba(var(--bs-success-rgb), 0.25);
    }
    #scrimFormulariosModulo { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1060; pointer-events: auto; display: none; }
    #modalTipoPreguntaValidacion.modal.show, #modalEditarPreguntaValidacion.modal.show { z-index: 1100 !important; }
    /* Form Builder en modal grande (no fullscreen) */
    #modalFormBuilderValidacion .modal-dialog {
        width: min(96vw, 1650px);
        max-width: min(96vw, 1650px);
        height: 90vh;
        margin: 2.5vh auto;
    }
    #modalFormBuilderValidacion .modal-content {
        height: 100%;
        border-radius: 0.75rem;
        overflow: visible;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        background: rgba(255, 255, 255, 0.94) !important;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(0, 0, 0, 0.06);
    }
    #modalFormBuilderValidacion .modal-header {
        border-radius: 0.75rem 0.75rem 0 0;
        overflow: visible;
    }
    #modalFormBuilderValidacion .modal-body {
        height: calc(90vh - 56px);
        min-height: 320px;
        overflow: hidden;
        border-radius: 0 0 0.75rem 0.75rem;
        display: flex;
        flex-direction: column;
    }
#modalFormBuilderValidacion .modal-body #iframeFormBuilderValidacion {
        flex: 1 1 0;
        min-height: 0;
        width: 100%;
        border: none;
    }
    #modalResumenTicket.modal-resumen-ausencia .modal-dialog { max-width: min(92vw, 760px); }
    #modalResumenTicket.modal-resumen-reclamo .modal-dialog { max-width: min(96vw, 1120px); }
    #modalResumenTicket.modal-resumen-ausencia .modal-header { background: #fff; }
    #modalResumenTicket.modal-resumen-ausencia .modal-body { padding-top: 1rem; }
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketMetaBar,
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketAsignarBlock,
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketCountdownCard,
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketPrioridadCard,
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketReferenciaCard,
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketAsuntoWrap,
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketDescripcionWrap { display: none !important; }
    #modalResumenTicket.modal-resumen-ausencia .tm-resumen-main-col { width: 100%; flex: 0 0 100%; max-width: 100%; }
    #modalResumenTicket.modal-resumen-ausencia .tm-resumen-side-col { display: none !important; }
    #modalResumenTicket.modal-resumen-ausencia #resumenTicketModuloDetalleWrap .border {
        background: #f8fafc !important;
        border-color: #e7edf5 !important;
    }
    @media (min-width: 992px) {
        #modalResumenTicket .col-lg-5ths {
            flex: 0 0 auto;
            width: 20%;
        }
    }
    @media (max-width: 992px) {
        #modalFormBuilderValidacion .modal-dialog {
            width: 98vw;
            max-width: 98vw;
            height: 94vh;
            margin: 1.5vh auto;
        }
        #modalFormBuilderValidacion .modal-body {
            height: calc(94vh - 56px);
        }
    }
</style>
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0 d-flex flex-wrap align-items-center gap-2 w-100">
            <span class="me-auto flex-grow-1 min-w-0"><i class="fa-solid <?= $icono; ?> me-2 <?= $iconoColor; ?>"></i><?= $tituloPrefijo . $titulo; ?></span>
            <?php if ($mostrarFormularios): ?>
            <span id="panelFormularioPrecargadoWrap" class="d-none align-items-center gap-1 small text-muted flex-shrink-0">
                <i class="fa-solid fa-clipboard-check text-success"></i> Formulario precargado: <strong id="panelFormularioPrecargadoNombre" class="text-body">—</strong>
            </span>
            <?php endif; ?>
            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-sm-auto">
                <?php if ($mostrarFormularios): ?>
                <button type="button" class="btn btn-sm btn-label-success" id="btnPanelAdminFormulariosValidacion" title="Formularios">
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

<!-- Modal: Resumen del ticket -->
<style>
/* Thumbnail de imágenes adjuntas: cover crop */
#resumenTicketEvidenciasGrid .tm-ev-img { width: 100%; height: 7rem; object-fit: cover; display: block; }
/* Searchable select: dropdown posicionado debajo del trigger */
#modalResumenTicket .select-search-wrapper { position: relative; width: 100%; }
#modalResumenTicket .select-search-wrapper .form-select { display: none !important; }
#modalResumenTicket .select-search-display { cursor: pointer; }
#modalResumenTicket .select-search-dropdown { position: absolute; top: 100%; left: 0; right: 0; z-index: 1070; display: none; margin-top: 0.125rem; max-height: 300px; overflow: hidden; }
#modalResumenTicket .select-search-dropdown.show { display: block; }
#modalResumenTicket .select-search-options { max-height: 220px; overflow-y: auto; }
/* Lightbox adjuntos (encima del modal resumen ticket) */
#tmAdjuntoLightbox {
    position: fixed; inset: 0; z-index: 1090; display: none; align-items: center; justify-content: center;
    padding: 3rem 3.5rem 4rem; box-sizing: border-box;
}
#tmAdjuntoLightbox.tm-adjunto-lightbox-open { display: flex; }
#tmAdjuntoLightbox .tm-adjunto-lb-backdrop {
    position: absolute; inset: 0; background: rgba(15, 23, 42, 0.94); cursor: pointer;
}
#tmAdjuntoLightbox .tm-adjunto-lb-stage {
    position: relative; z-index: 2; max-width: 96vw; max-height: 88vh; display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
}
#tmAdjuntoLightbox .tm-adjunto-lb-img-wrap {
    max-width: 92vw; max-height: calc(88vh - 3rem); display: flex; align-items: center; justify-content: center;
}
#tmAdjuntoLightbox .tm-adjunto-lb-img-wrap img {
    max-width: 92vw; max-height: calc(88vh - 3.5rem); width: auto; height: auto; object-fit: contain;
    border-radius: 0.35rem; box-shadow: 0 12px 40px rgba(0,0,0,0.45);
}
/* PDF: visor nativo del navegador dentro del mismo overlay (no nueva pestaña) */
#tmAdjuntoLightbox .tm-adjunto-lb-pdf-wrap {
    width: min(92vw, 1200px); height: calc(88vh - 3.5rem); max-height: calc(88vh - 3.5rem);
    background: #fff; border-radius: 0.35rem; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.45);
}
#tmAdjuntoLightbox .tm-adjunto-lb-pdf-wrap iframe {
    width: 100%; height: 100%; border: 0; display: block;
}
#tmAdjuntoLightbox .tm-adjunto-lb-caption {
    color: #e2e8f0; font-size: 0.85rem; text-align: center; max-width: 90vw; word-break: break-word;
}
#tmAdjuntoLightbox .tm-adjunto-lb-close {
    position: absolute; top: 0.75rem; right: 0.75rem; z-index: 3; width: 44px; height: 44px; border: none; border-radius: 50%;
    background: rgba(255,255,255,0.12); color: #fff; font-size: 1.5rem; line-height: 1; cursor: pointer;
}
#tmAdjuntoLightbox .tm-adjunto-lb-close:hover { background: rgba(255,255,255,0.22); }
#tmAdjuntoLightbox .tm-adjunto-lb-nav {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 3; width: 48px; height: 48px; border: none; border-radius: 50%;
    background: rgba(255,255,255,0.15); color: #fff; font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
}
#tmAdjuntoLightbox .tm-adjunto-lb-nav:hover { background: rgba(255,255,255,0.28); }
#tmAdjuntoLightbox .tm-adjunto-lb-nav:disabled { opacity: 0.25; cursor: not-allowed; }
#tmAdjuntoLightbox .tm-adjunto-lb-prev { left: 0.5rem; }
#tmAdjuntoLightbox .tm-adjunto-lb-next { right: 0.5rem; }
@media (max-width: 576px) {
    #tmAdjuntoLightbox { padding: 2.5rem 0.25rem 3.5rem; }
    #tmAdjuntoLightbox .tm-adjunto-lb-prev { left: 0.15rem; }
    #tmAdjuntoLightbox .tm-adjunto-lb-next { right: 0.15rem; }
}
</style>
<div class="modal fade" id="modalResumenTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header: avatar Sneat requiere .avatar-initial para centrar icono; X en esquina (position-absolute) -->
            <div class="modal-header align-items-center border-bottom position-relative py-3 ps-3 ps-md-4 pe-5">
                <div class="d-flex flex-grow-1 flex-column flex-sm-row align-items-sm-center gap-3 min-w-0">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary"><i class="fa-solid fa-ticket"></i></span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-uppercase text-muted small fw-semibold mb-0" style="letter-spacing: 0.05em;">Folio</div>
                            <div class="h5 mb-0 fw-bold text-heading" id="resumenTicketFolio">—</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0">
                        <span id="resumenTicketEstadoPill" class="badge bg-label-success">—</span>
                        <span id="resumenTicketPrioridadPill" class="badge bg-label-warning">—</span>
                    </div>
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2 mt-md-2 me-md-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Metadatos -->
            <div class="border-bottom bg-light px-3 py-2 px-md-4 small" id="resumenTicketMetaBar">
                <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-6">
                    <div class="col">
                        <div class="text-uppercase text-muted small fw-semibold mb-0" style="letter-spacing: 0.04em;">De</div>
                        <div class="fw-semibold text-truncate" id="resumenTicketDe">—</div>
                    </div>
                    <div class="col">
                        <div class="text-uppercase text-muted small fw-semibold mb-0" style="letter-spacing: 0.04em;">Asignado por</div>
                        <div class="fw-semibold text-truncate" id="resumenTicketAsignadoPor">—</div>
                    </div>
                    <div class="col">
                        <div class="text-uppercase text-muted small fw-semibold mb-0" style="letter-spacing: 0.04em;">Fecha</div>
                        <div class="fw-semibold" id="resumenTicketFecha">—</div>
                    </div>
                    <div class="col">
                        <div class="text-uppercase text-muted small fw-semibold mb-0" style="letter-spacing: 0.04em;">Vence</div>
                        <div class="fw-semibold text-danger" id="resumenTicketVence">—</div>
                    </div>
                    <div class="col">
                        <div class="text-uppercase text-muted small fw-semibold mb-0" style="letter-spacing: 0.04em;">Estado</div>
                        <div class="fw-semibold" id="resumenTicketEstado">—</div>
                    </div>
                    <div class="col">
                        <div class="text-uppercase text-muted small fw-semibold mb-0" style="letter-spacing: 0.04em;">Referencia</div>
                        <div class="fw-semibold text-truncate" id="resumenTicketRef">—</div>
                    </div>
                </div>
            </div>

            <!-- Cuerpo -->
            <div class="modal-body">
                <div class="row g-3">

                    <!-- Columna principal -->
                    <div class="col-12 col-md-8 tm-resumen-main-col">
                        <div class="mb-3" id="resumenTicketAsuntoWrap">
                            <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">
                                <i class="fa-solid fa-clipboard me-1"></i>Asunto
                            </p>
                            <div class="border rounded-2 bg-body-tertiary p-2 small" id="resumenTicketAsunto">—</div>
                        </div>
                        <div class="mb-3" id="resumenTicketDescripcionWrap">
                            <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">
                                <i class="fa-solid fa-file-lines me-1"></i>Descripción inicial
                            </p>
                            <div class="border rounded-2 bg-body-tertiary p-2 small" style="white-space:pre-wrap;" id="resumenTicketDescripcion">—</div>
                        </div>
                        <div id="resumenTicketModuloDetalleWrap" class="mb-3 d-none"></div>
                        <div id="resumenTicketExtraWrap" class="mb-3 d-none">
                            <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">
                                <i class="fa-solid fa-link me-1"></i>Nota / Enlace
                            </p>
                            <div class="border rounded-2 bg-body-tertiary p-2 small" id="resumenTicketNota"></div>
                            <div id="resumenTicketLinkWrap" class="mt-2"></div>
                        </div>
                        <div id="resumenTicketEvidenciasWrap" class="mb-3 d-none">
                            <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">
                                <i class="fa-solid fa-camera me-1"></i>Fotos y adjuntos al ticket
                            </p>
                            <p class="small text-muted mb-2">Archivos cargados al levantar la solicitud.</p>
                            <div id="resumenTicketEvidenciasGrid" class="row g-2"></div>
                        </div>
                        <div id="resumenTicketDsWrap" class="mb-3 d-none">
                            <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">
                                <i class="fa-solid fa-circle-check me-1"></i>Resultado DS
                            </p>
                            <div class="border rounded-2 bg-body-tertiary p-2 small" id="resumenTicketDs"></div>
                        </div>
                        <div id="resumenTicketRespuestaWrap" class="mb-3 d-none">
                            <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">
                                <i class="fa-solid fa-reply me-1"></i>Respuesta del ticket
                            </p>
                            <div class="border rounded-2 bg-body-tertiary p-3 small">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                    <span id="resumenTicketRespuestaBadge" class="badge bg-label-secondary">—</span>
                                    <span id="resumenTicketRespuestaFecha" class="text-muted"></span>
                                </div>
                                <div id="resumenTicketRespuestaComentario" style="white-space:pre-wrap;">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-12 col-md-4 tm-resumen-side-col">
                        <div class="card mb-2" id="resumenTicketAsignarBlock">
                            <div class="card-body py-2 px-3">
                                <p class="text-muted fw-semibold mb-2" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;" id="resumenTicketAsignarTitulo">Asignar a</p>
                                <p class="small text-muted mb-1 d-none" id="resumenTicketAsignadoACapoLabel">Asignado a: —</p>
                                <p class="small text-muted mb-1" id="resumenTicketCampoLabel">Segmento (máximo rango)</p>
                                <div class="btn-group btn-group-sm w-100 mb-2" role="group" aria-label="Segmento morosidad">
                                    <input type="radio" class="btn-check" name="tmAsignarCampo" id="tmAsignarCampo17" value="1_7" autocomplete="off" checked>
                                    <label class="btn btn-outline-secondary" for="tmAsignarCampo17">Campo 1–7</label>
                                    <input type="radio" class="btn-check" name="tmAsignarCampo" id="tmAsignarCampo821" value="8_21" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="tmAsignarCampo821">Campo 8–21</label>
                                </div>
                                <select id="resumenTicketAsignarSelect" class="form-select form-select-sm" aria-label="Asignar ticket">
                                    <option value="">Selecciona una persona</option>
                                </select>
                                <p id="resumenTicketAsignarHint" class="small text-warning mb-0 mt-1 d-none"></p>
                                <div id="resumenTicketMotivoWrap" class="mt-2 d-none">
                                    <p id="resumenTicketMotivoLabel" class="small text-muted fw-semibold mb-1">Motivo del cambio</p>
                                    <textarea id="resumenTicketAsignarMotivo" class="form-control form-control-sm" rows="3" placeholder="Obligatorio al cambiar de gestor"></textarea>
                                    <button type="button" id="resumenTicketTerritorialBtnReasignar" class="btn btn-sm btn-primary w-100 mt-2 d-none">Asignar gestor</button>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-2" id="resumenTicketCountdownCard">
                            <div class="card-body py-2 px-3">
                                <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Tiempo restante (24h)</p>
                                <span id="resumenTicketCountdown" class="fw-semibold">—</span>
                            </div>
                        </div>
                        <div class="card mb-2" id="resumenTicketPrioridadCard">
                            <div class="card-body py-2 px-3">
                                <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Prioridad</p>
                                <span class="badge bg-label-warning" id="resumenTicketPrioridadSide">—</span>
                            </div>
                        </div>
                        <div class="card mb-2" id="resumenTicketReferenciaCard">
                            <div class="card-body py-2 px-3">
                                <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Referencia</p>
                                <div class="small fw-semibold" id="resumenTicketRefSide">—</div>
                            </div>
                        </div>
                        <div id="resumenTicketFormularioWrap" class="card mb-2 d-none">
                            <div class="card-body py-2 px-3">
                                <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Formulario precargado</p>
                                <select id="resumenTicketFormularioSelect" class="form-select form-select-sm mt-1">
                                    <option value="">— Ninguno —</option>
                                </select>
                                <p id="resumenTicketFormularioPrecargado" class="small text-muted mb-0 mt-2"><span class="fw-semibold">Precargado:</span> <span id="resumenTicketFormularioPrecargadoNombre">—</span></p>
                            </div>
                        </div>
                        <div id="resumenTicketVerFormularioTerritorialWrap" class="card mb-2 d-none">
                            <div class="card-body py-2 px-3">
                                <p class="text-muted fw-semibold mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Formulario de validación</p>
                                <button type="button" id="resumenTicketBtnVerFormularioTerritorial" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fa-solid fa-clipboard-list me-1"></i>Ver formulario y preguntas
                                </button>
                                <p class="small text-muted mb-0 mt-2">Solo consulta.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer py-2">
                <span class="text-muted small me-auto"><i class="fa-regular fa-clock me-1"></i>Creado: <span id="resumenTicketCreado">—</span></span>
                <button type="button" class="btn btn-sm btn-success d-none" id="resumenTicketBtnAceptar">
                    <i class="fa-solid fa-check me-1"></i>Aceptar
                </button>
                <button type="button" class="btn btn-sm btn-danger d-none" id="resumenTicketBtnDenegar">
                    <i class="fa-solid fa-ban me-1"></i>Denegar
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<div id="tmAdjuntoLightbox" class="tm-adjunto-lightbox-root" aria-hidden="true" aria-modal="true" role="dialog">
    <div class="tm-adjunto-lb-backdrop" data-tm-lb-close="1" aria-label="Cerrar"></div>
    <button type="button" class="tm-adjunto-lb-close" data-tm-lb-close="1" aria-label="Cerrar"><i class="fa-solid fa-times"></i></button>
    <button type="button" class="tm-adjunto-lb-nav tm-adjunto-lb-prev" id="tmAdjuntoLightboxPrev" aria-label="Foto anterior"><i class="fa-solid fa-chevron-left"></i></button>
    <button type="button" class="tm-adjunto-lb-nav tm-adjunto-lb-next" id="tmAdjuntoLightboxNext" aria-label="Foto siguiente"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="tm-adjunto-lb-stage">
        <div class="tm-adjunto-lb-img-wrap">
            <img id="tmAdjuntoLightboxImg" src="" alt="">
        </div>
        <div class="tm-adjunto-lb-pdf-wrap d-none">
            <iframe id="tmAdjuntoLightboxPdf" title="Vista previa PDF" src="about:blank"></iframe>
        </div>
        <div class="tm-adjunto-lb-caption">
            <span id="tmAdjuntoLightboxNombre"></span>
            <span id="tmAdjuntoLightboxCounter" class="ms-1 opacity-75"></span>
        </div>
    </div>
</div>

<?php if ($mostrarFormularios): ?>
<div class="modal fade modal-formularios-parent" id="modalFormulariosValidacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #14532d 0%, #166534 100%);">
                <h5 class="modal-title text-white"><i class="fa-solid fa-clipboard-list me-2"></i>Formularios – Validaciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="small text-muted mb-3">Elija un formulario para agregar o editar preguntas. Puede crear uno nuevo, inhabilitar o eliminar.</p>
                <div class="alert alert-light border mb-3 py-2 px-3 small d-flex align-items-center gap-2">
                    <i class="fa-solid fa-star text-warning"></i>
                    <span>Formulario precargado actual: <strong id="formularioPrecargadoActualNombre">—</strong></span>
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <span class="text-muted small">Lista de formularios</span>
                    <button type="button" class="btn btn-sm btn-label-success" id="btnFormularioValidacionCrear"><i class="fa-solid fa-plus me-1"></i>Crear nuevo</button>
                </div>
                <div id="formValidacionCrearInline" class="mb-3 p-3 rounded-2 border bg-light" style="display: none;">
                    <label class="form-label small fw-medium mb-2">Nombre del nuevo formulario</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="text" class="form-control form-control-sm" id="formValidacionNombreNuevo" placeholder="Ej: Cuestionario de Validación" maxlength="200" style="max-width: 280px;">
                        <button type="button" class="btn btn-sm btn-primary" id="btnFormularioValidacionCrearConfirm"><i class="fa-solid fa-check me-1"></i>Crear</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFormularioValidacionCrearCancel">Cancelar</button>
                    </div>
                </div>
                <ul class="list-group list-group-flush rounded-2 border" id="listaFormulariosValidacion">
                    <li class="list-group-item small text-muted fst-italic py-3 text-center">(Cargando…)</li>
                </ul>
                <p class="small text-muted mt-2 mb-0" id="formValidacionSinFormularios" style="display:none;">Aún no hay formularios. Cree uno con «Crear nuevo».</p>
            </div>
            <div class="modal-footer border-top-0 pt-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($mostrarFormularios || $mostrarModalFormBuilderLectura): ?>
<!-- Modal Form Builder (iframe; en territorial solo lectura desde resumen de ticket) -->
<div class="modal fade" id="modalFormBuilderValidacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header py-2 border-bottom" style="background: linear-gradient(135deg, #14532d 0%, #166534 100%);">
                <h5 class="modal-title text-white" id="modalFormBuilderValidacionTitulo"><i class="fa-solid fa-pen-to-square me-2"></i>Formulario de validación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0 overflow-hidden">
                <iframe id="iframeFormBuilderValidacion" src="about:blank" title="Formulario de validación" style="width:100%;min-height:70vh;border:0;display:block;"></iframe>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($mostrarFormularios): ?>
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
                    <button type="button" class="btn btn-sm btn-label-success mt-2" id="fpAgregarOpcionCerrada"><i class="fa-solid fa-plus me-1"></i>Opción</button>
                </div>
                <div id="fpWrapMultiple" class="fp-editor-section d-none">
                    <label class="form-label fw-medium" for="fpTextoMultiple">Pregunta</label>
                    <textarea id="fpTextoMultiple" class="form-control mb-3" rows="3"></textarea>
                    <div id="fpListaOpcionesMultiple" class="d-flex flex-column gap-2"></div>
                    <button type="button" class="btn btn-sm btn-label-success mt-2" id="fpAgregarOpcionMultiple"><i class="fa-solid fa-plus me-1"></i>Opción</button>
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
                <button type="button" class="btn btn-label-success" id="fpBtnGuardarPregunta"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
