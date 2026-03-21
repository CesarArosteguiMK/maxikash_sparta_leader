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

<!-- Modal: Resumen del ticket (estilo detalle) -->
<style>
#modalResumenTicket .rt-header { background: #26344e; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
#modalResumenTicket .rt-folio-pill { display: inline-flex; flex-direction: column; padding: 6px 14px; border-radius: 999px; border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.08); }
#modalResumenTicket .rt-folio-pill .rt-folio-lbl { font-size: 0.65rem; font-weight: 700; letter-spacing: .08em; color: #94b4d4; }
#modalResumenTicket .rt-folio-pill .rt-folio-num { font-size: 0.95rem; font-weight: 800; color: #fff; }
#modalResumenTicket .rt-badges { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
#modalResumenTicket .rt-badge { font-size: 0.75rem; font-weight: 700; padding: 5px 10px; border-radius: 8px; color: #fff; }
#modalResumenTicket .rt-badge-estado { background: #166534; }
#modalResumenTicket .rt-badge-prioridad { background: #fd7e14; }
#modalResumenTicket .rt-meta-bar { display: grid; grid-template-columns: repeat(6, 1fr); gap: 0; background: #f1f5f9; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.8rem; }
#modalResumenTicket .rt-meta-cell { padding: 0 12px; border-right: 1px solid #e2e8f0; }
#modalResumenTicket .rt-meta-cell:last-child { border-right: none; }
@media (max-width: 768px) {
#modalResumenTicket .rt-meta-bar { grid-template-columns: 1fr 1fr; }
#modalResumenTicket .rt-meta-cell { border-right: none; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 6px; }
#modalResumenTicket .rt-body { flex-direction: column; }
#modalResumenTicket .rt-sidebar { max-width: none; }
}
#modalResumenTicket .rt-meta-lbl { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 2px; }
#modalResumenTicket .rt-meta-val { font-weight: 600; color: #1e293b; }
#modalResumenTicket .rt-meta-val.rt-vence { color: #dc2626; }
#modalResumenTicket .rt-body { display: flex; gap: 20px; padding: 20px; }
#modalResumenTicket .rt-main { flex: 1; min-width: 0; }
#modalResumenTicket .rt-block { margin-bottom: 16px; }
#modalResumenTicket .rt-block-lbl { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
#modalResumenTicket .rt-block-lbl i { color: #26344e; font-size: 0.85rem; }
#modalResumenTicket .rt-block-box { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #26344e; border-radius: 8px; padding: 10px 14px; font-size: 0.875rem; color: #334155; }
#modalResumenTicket .rt-block-box.rt-pre { white-space: pre-wrap; }
#modalResumenTicket .rt-btn-mapa { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; background: #e0f2fe; border: 1px solid #7dd3fc; color: #26344e; font-size: 0.8rem; font-weight: 700; text-decoration: none; margin-top: 8px; }
#modalResumenTicket .rt-btn-mapa:hover { background: #bae6fd; color: #26344e; }
#modalResumenTicket .rt-sidebar { width: 100%; max-width: 260px; flex-shrink: 0; }
#modalResumenTicket .rt-sidebar .rt-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; }
#modalResumenTicket .rt-sidebar .rt-card-lbl { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 6px; }
#modalResumenTicket .rt-sidebar .form-select { font-size: 0.8rem; border-color: #cbd5e1; }
/* Select con búsqueda (mismo patrón que organigrama) — Asignar a */
#modalResumenTicket .select-search-wrapper { position: relative; width: 100%; max-width: 100%; }
#modalResumenTicket .select-search-wrapper .form-select { display: none !important; }
#modalResumenTicket .select-search-display {
    position: relative; width: 100%;
    padding: 0.25rem 2rem 0.25rem 0.5rem;
    font-size: 0.875rem; font-weight: 400; line-height: 1.5;
    color: #697a8d; background-color: #fff;
    border: 1px solid #d9dee3; border-radius: 0.375rem;
    cursor: pointer; transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
#modalResumenTicket .select-search-display:hover { border-color: #b0b7c3; }
#modalResumenTicket .select-search-display::after {
    content: '▼'; position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%);
    font-size: 0.65rem; color: #697a8d; pointer-events: none;
}
#modalResumenTicket .select-search-dropdown {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 2000; display: none;
    margin-top: 0.25rem; background: #fff; border: 1px solid #d9dee3; border-radius: 0.375rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1); max-height: 300px; overflow: hidden;
}
#modalResumenTicket .select-search-dropdown.show { display: block; }
#modalResumenTicket .select-search-input {
    width: 100%; padding: 0.5rem 0.75rem; border: none; border-bottom: 1px solid #d9dee3;
    font-size: 0.875rem; outline: none;
}
#modalResumenTicket .select-search-input:focus { border-bottom-color: #696cff; }
#modalResumenTicket .select-search-options { max-height: 220px; overflow-y: auto; }
#modalResumenTicket .select-search-option {
    padding: 0.45rem 0.65rem; cursor: pointer; transition: background-color 0.15s ease;
    font-size: 0.8125rem;
}
#modalResumenTicket .select-search-option:hover { background-color: #f5f5f9; }
#modalResumenTicket .select-search-option.selected { background-color: #696cff; color: #fff; }
#modalResumenTicket .select-search-option.no-results { padding: 1rem; text-align: center; color: #999; cursor: default; font-size: 0.8rem; }
#modalResumenTicket .select-search-option.no-results:hover { background-color: transparent; }
#modalResumenTicket .rt-footer { display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.8rem; }
#modalResumenTicket .rt-footer .rt-created { color: #64748b; display: flex; align-items: center; gap: 6px; }
#modalResumenTicket .rt-footer .btn { background: #26344e; border-color: #26344e; color: #fff; font-weight: 600; }
#modalResumenTicket #resumenTicketEvidenciasGrid .rt-ev-thumb {
    display: block; border-radius: 0.5rem; overflow: hidden; border: 1px solid #e2e8f0;
    background: #f8fafc; transition: box-shadow 0.15s ease;
}
#modalResumenTicket #resumenTicketEvidenciasGrid .rt-ev-thumb:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
#modalResumenTicket #resumenTicketEvidenciasGrid .rt-ev-thumb img { width: 100%; height: 128px; object-fit: cover; display: block; }
#modalResumenTicket #resumenTicketEvidenciasGrid .rt-ev-thumb-btn { cursor: pointer; text-align: left; }
#modalResumenTicket #resumenTicketEvidenciasGrid .rt-ev-thumb-btn:focus { outline: 2px solid #696cff; outline-offset: 2px; }
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
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="rt-header">
                <div class="rt-folio-pill">
                    <span class="rt-folio-lbl">Folio</span>
                    <span class="rt-folio-num" id="resumenTicketFolio">—</span>
                </div>
                <div class="rt-badges">
                    <span class="rt-badge rt-badge-estado" id="resumenTicketEstadoPill">—</span>
                    <span class="rt-badge rt-badge-prioridad" id="resumenTicketPrioridadPill">—</span>
                    <button type="button" class="btn btn-sm btn-light text-dark ms-1" style="width: 32px; height: 32px; padding: 0; border-radius: 8px;" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fa-solid fa-times"></i></button>
                </div>
            </div>
            <div class="rt-meta-bar">
                <div class="rt-meta-cell"><div class="rt-meta-lbl">De</div><div class="rt-meta-val" id="resumenTicketDe">—</div></div>
                <div class="rt-meta-cell"><div class="rt-meta-lbl">Asignado por</div><div class="rt-meta-val" id="resumenTicketAsignadoPor">—</div></div>
                <div class="rt-meta-cell"><div class="rt-meta-lbl">Fecha</div><div class="rt-meta-val" id="resumenTicketFecha">—</div></div>
                <div class="rt-meta-cell"><div class="rt-meta-lbl">Vence</div><div class="rt-meta-val rt-vence" id="resumenTicketVence">—</div></div>
                <div class="rt-meta-cell"><div class="rt-meta-lbl">Estado</div><div class="rt-meta-val" id="resumenTicketEstado">—</div></div>
                <div class="rt-meta-cell"><div class="rt-meta-lbl">Referencia</div><div class="rt-meta-val" id="resumenTicketRef">—</div></div>
            </div>
            <div class="rt-body">
                <div class="rt-main">
                    <div class="rt-block">
                        <div class="rt-block-lbl"><i class="fa-solid fa-clipboard"></i> Asunto</div>
                        <div class="rt-block-box" id="resumenTicketAsunto">—</div>
                    </div>
                    <div class="rt-block">
                        <div class="rt-block-lbl"><i class="fa-solid fa-file-lines"></i> Descripción inicial</div>
                        <div class="rt-block-box rt-pre" id="resumenTicketDescripcion">—</div>
                    </div>
                    <div id="resumenTicketExtraWrap" class="rt-block d-none">
                        <div class="rt-block-lbl"><i class="fa-solid fa-link"></i> Nota / Enlace</div>
                        <div class="rt-block-box" id="resumenTicketNota"></div>
                        <div id="resumenTicketLinkWrap"></div>
                    </div>
                    <div id="resumenTicketEvidenciasWrap" class="rt-block d-none">
                        <div class="rt-block-lbl"><i class="fa-solid fa-camera"></i> Fotos y adjuntos al ticket</div>
                        <p class="small text-muted mb-2 mb-md-0">Archivos cargados al levantar la solicitud.</p>
                        <div id="resumenTicketEvidenciasGrid" class="row g-2"></div>
                    </div>
                    <div id="resumenTicketDsWrap" class="rt-block d-none">
                        <div class="rt-block-lbl"><i class="fa-solid fa-circle-check"></i> Resultado DS</div>
                        <div class="rt-block-box" id="resumenTicketDs"></div>
                    </div>
                </div>
                <div class="rt-sidebar">
                    <div class="rt-block mb-3" id="resumenTicketAsignarBlock">
                        <div class="rt-card-lbl mb-2" id="resumenTicketAsignarTitulo">Asignar a</div>
                        <div class="small text-muted mb-1 d-none" id="resumenTicketAsignadoACapoLabel">Asignado a: —</div>
                        <div class="small text-muted mb-1" id="resumenTicketCampoLabel">Segmento (máximo rango)</div>
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
                            <div id="resumenTicketMotivoLabel" class="small text-muted mb-1" style="font-weight:600;">Motivo del cambio</div>
                            <textarea id="resumenTicketAsignarMotivo" class="form-control form-control-sm" rows="3" placeholder="Obligatorio al cambiar de gestor"></textarea>
                            <button type="button" id="resumenTicketTerritorialBtnReasignar" class="btn btn-sm btn-primary w-100 mt-2 d-none">Asignar gestor</button>
                        </div>
                    </div>
                    <div class="rt-card">
                        <div class="rt-card-lbl">Tiempo restante (24h)</div>
                        <span id="resumenTicketCountdown" class="fw-semibold">—</span>
                    </div>
                    <div class="rt-card">
                        <div class="rt-card-lbl">Prioridad</div>
                        <span class="rt-badge rt-badge-prioridad" id="resumenTicketPrioridadSide">—</span>
                    </div>
                    <div class="rt-card">
                        <div class="rt-card-lbl">Referencia</div>
                        <div class="rt-meta-val" id="resumenTicketRefSide">—</div>
                    </div>
                    <div id="resumenTicketFormularioWrap" class="rt-card d-none">
                        <div class="rt-card-lbl">Formulario precargado</div>
                        <select id="resumenTicketFormularioSelect" class="form-select form-select-sm mt-1">
                            <option value="">— Ninguno —</option>
                        </select>
                        <p id="resumenTicketFormularioPrecargado" class="small text-muted mb-0 mt-2"><span class="fw-semibold">Precargado:</span> <span id="resumenTicketFormularioPrecargadoNombre">—</span></p>
                    </div>
                    <div id="resumenTicketVerFormularioTerritorialWrap" class="rt-card d-none">
                        <div class="rt-card-lbl">Formulario de validación</div>
                        <button type="button" id="resumenTicketBtnVerFormularioTerritorial" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fa-solid fa-clipboard-list me-1"></i>Ver formulario y preguntas
                        </button>
                        <p class="small text-muted mb-0 mt-2">Solo consulta.</p>
                    </div>
                </div>
            </div>
            <div class="rt-footer">
                <span class="rt-created"><i class="fa-regular fa-clock"></i> Creado: <span id="resumenTicketCreado">—</span></span>
                <button type="button" class="btn btn-sm" data-bs-dismiss="modal"><i class="fa-solid fa-times me-1"></i>Cerrar</button>
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
