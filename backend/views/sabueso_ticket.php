<?php
$categoriasDisp = isset($categoriasDisponiblesPorPuesto) && is_array($categoriasDisponiblesPorPuesto) ? $categoriasDisponiblesPorPuesto : ['sabueso', 'solicitud_baja'];
$en = function($cat) use ($categoriasDisp) { return in_array($cat, $categoriasDisp, true); };
$funcionesTicket = isset($funcionesTicket) && is_array($funcionesTicket) ? $funcionesTicket : [];
?>
<style>
    /* Modal elegir categoría: diseño tipo cards (selección + continuar) */
    .modal-elegir-categoria .modal-content {
        border-radius: 1rem;
    }
    .modal-elegir-categoria .modal-header {
        position: relative;
        padding: 1.25rem 1.5rem 0.75rem;
        padding-right: 3rem;
        flex-wrap: nowrap;
        border-radius: 1rem 1rem 0 0;
    }
    .modal-elegir-categoria .modal-header .btn-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        margin: 0;
        flex-shrink: 0;
        z-index: 1;
    }
    .modal-elegir-categoria .modal-header > div:first-child {
        padding-right: 2rem;
    }
    .modal-elegir-categoria .modal-title {
        font-size: 1.9rem;
        line-height: 1.1;
        margin-bottom: 0.25rem;
    }
    .modal-elegir-categoria .ticket-subtitle {
        color: #6c757d;
        font-size: 1.05rem;
        margin: 0;
    }
    .modal-elegir-categoria .modal-body {
        padding: 0.75rem 1.5rem 1.25rem;
    }
    .modal-elegir-categoria .ticket-categoria-card {
        position: relative;
        width: 100%;
        min-height: 162px;
        border: 1px solid #e4e7ec;
        border-radius: 0.9rem;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        transition: all 0.18s ease;
        padding: 1rem 0.85rem;
        text-align: center;
    }
    .modal-elegir-categoria .ticket-categoria-card[data-disponible="1"] {
        cursor: pointer;
    }
    .modal-elegir-categoria .ticket-categoria-card[data-disponible="1"]:hover {
        transform: translateY(-1px);
        border-color: #0d6efd;
        box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12);
    }
    .modal-elegir-categoria .ticket-categoria-card.is-selected {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.18) inset;
        background: #eef5ff;
    }
    .modal-elegir-categoria .ticket-categoria-card[data-disponible="0"],
    .modal-elegir-categoria .ticket-categoria-card:disabled {
        opacity: 0.62;
        cursor: not-allowed;
        background: #fafbfc;
        pointer-events: none;
    }
    .modal-elegir-categoria .ticket-categoria-card--proximamente {
        overflow: hidden;
    }
    /* Lazo esquina superior derecha (no seleccionable vía disabled + data-disponible) */
    .modal-elegir-categoria .ticket-categoria-lazo {
        position: absolute;
        /* Más abajo + translateY: la P de "Próximamente" no queda bajo el recorte overflow:hidden de la card */
        top: 1.35rem;
        right: -2.35rem;
        z-index: 2;
        transform: translateY(0.22rem) rotate(40deg);
        transform-origin: center center;
        background: var(--bs-warning);
        color: var(--bs-warning-text-emphasis);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        padding: 0.38rem 2.55rem 0.32rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        line-height: 1.35;
        white-space: nowrap;
        pointer-events: none;
        user-select: none;
    }
    .modal-elegir-categoria .ticket-categoria-icon {
        width: 56px;
        height: 56px;
        border-radius: 0.75rem;
        border: 1px solid #e7e9ee;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #7a8597;
        background: #fff;
    }
    .modal-elegir-categoria .ticket-categoria-card.is-selected .ticket-categoria-icon {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .modal-elegir-categoria .ticket-categoria-icon i {
        font-size: 1.25rem;
    }
    .modal-elegir-categoria .ticket-categoria-name {
        font-size: 1.12rem;
        font-weight: 700;
        color: #344054;
        line-height: 1.25;
    }
    .modal-elegir-categoria .ticket-categoria-meta {
        font-size: 0.98rem;
        color: #98a2b3;
    }
    .modal-elegir-categoria .ticket-categoria-check {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: #0d6efd;
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
    }
    .modal-elegir-categoria .ticket-categoria-card.is-selected .ticket-categoria-check {
        display: inline-flex;
    }
    .modal-elegir-categoria .modal-footer {
        border-top: 1px solid #e9ecef;
        background: #fafafa;
        padding: 0.95rem 1.5rem;
        border-radius: 0 0 1rem 1rem;
    }
    .modal-elegir-categoria #btnContinuarCategoriaTicket {
        min-width: 190px;
    }
    #modal_fecha_vencimiento { width: 100%; cursor: pointer; }
    .flatpickr-calendar .flatpickr-monthDropdown-months { appearance: none !important; background-image: none !important; -webkit-appearance: none; -moz-appearance: none; }
    .flatpickr-calendar { transform: scale(1.12); transform-origin: top left; }
    /* Modal datos crédito: compacto vertical */
    .credito-modal-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .credito-modal-item { display: flex; flex-direction: column; gap: 0.15rem; }
    .credito-modal-item .fw-medium { word-break: break-word; }
    #modalDatosCredito .modal-dialog { max-width: 400px; }
    /* Alerta dictamen enviado en menú Ticket */
    #tablaTickets tr.fila-dictamen-enviado { cursor: pointer; border-left: 4px solid #0d6efd; }
    #tablaTickets tr.fila-dictamen-no-visto {
        animation: filaDictamenRedPulseTicket 1s ease-in-out infinite;
        border-left: 5px solid #dc3545 !important;
        border-right: 2px solid rgba(220, 53, 69, 0.6) !important;
        box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0.35), 0 0 12px rgba(220, 53, 69, 0.25);
        background: linear-gradient(90deg, rgba(220, 53, 69, 0.12) 0%, rgba(220, 53, 69, 0.02%) 100%) !important;
    }
    @keyframes filaDictamenRedPulseTicket {
        0%, 100% { box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0.35), 0 0 12px rgba(220, 53, 69, 0.2); }
        50%  { box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0.55), 0 0 20px rgba(220, 53, 69, 0.45); }
    }
    #tablaTickets tbody tr.fila-dictamen-enviado:hover { background-color: rgba(13, 110, 253, 0.08) !important; }
    #tablaTickets tbody tr.fila-dictamen-no-visto:hover { background: linear-gradient(90deg, rgba(220, 53, 69, 0.18) 0%, rgba(220, 53, 69, 0.04%) 100%) !important; }
</style>
<div class="card">
    <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-ticket me-2"></i>Tickets
        </h5>
        <button type="button" class="btn btn-primary" id="btnAbrirModalLevantarTicket" title="Crear nuevo ticket">
            <i class="fa-solid fa-plus me-1"></i>Levantar ticket
        </button>
    </div>
    <div class="card-body border-bottom d-flex flex-wrap align-items-center gap-2 py-3">
        <label for="buscar_id_credito" class="form-label mb-0 text-nowrap">ID de crédito</label>
        <div class="input-group input-group-merge flex-grow-1" style="min-width: 280px; max-width: 420px;">
            <input type="number" id="buscar_id_credito" class="form-control" placeholder="Ej: 123456">
            <button type="button" class="btn btn-outline-primary" onclick="buscarCreditoModal()" title="Buscar datos del crédito">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table id="tablaTickets" class="dt-responsive table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th class="d-none">Fecha orden</th>
                    <th>Folio / Tipo</th>
                    <th>Gestión</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Crédito</th>
                    <th>Fechas</th>
                    <th>Tiempo para visitar / Prórroga</th>
                    <th>Resultado DS</th>
                    <th></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Elegir categoría antes de levantar ticket (activos: Sabueso y Validaciones; resto muestran Próximamente) -->
<div class="modal fade modal-elegir-categoria" id="modalElegirCategoriaTicket" tabindex="-1" aria-labelledby="modalElegirCategoriaTicketLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom-0">
                <div>
                    <h5 class="modal-title" id="modalElegirCategoriaTicketLabel">Levantar ticket</h5>
                    <p class="ticket-subtitle">¿A qué área pertenece tu solicitud?</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="modalCategoriasTicketLista">
                    <?php
                    $categoriasLevantamientoActivas = ['sabueso', 'validaciones'];
                    foreach ($funcionesTicket as $clave => $info):
                        if (!$en($clave)) continue;
                        $label = isset($info['label']) ? $info['label'] : $clave;
                        $icon = isset($info['icon']) ? $info['icon'] : 'fa-solid fa-circle';
                        $funcionaAhora = in_array($clave, $categoriasLevantamientoActivas, true);
                        $clsCard = 'ticket-categoria-card' . ($funcionaAhora ? '' : ' ticket-categoria-card--proximamente');
                        $disp = $funcionaAhora ? '1' : '0';
                    ?>
                    <div class="col-md-4 col-6">
                        <button type="button"
                                class="<?php echo htmlspecialchars($clsCard, ENT_QUOTES, 'UTF-8'); ?>"
                                data-categoria="<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"
                                data-disponible="<?php echo htmlspecialchars($disp, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php if (!$funcionaAhora): ?>disabled<?php endif; ?>>
                            <?php if (!$funcionaAhora): ?>
                            <span class="ticket-categoria-lazo">Próximamente</span>
                            <?php endif; ?>
                            <span class="ticket-categoria-check"><i class="fa-solid fa-check"></i></span>
                            <span class="ticket-categoria-icon"><i class="<?php echo htmlspecialchars($icon); ?>"></i></span>
                            <span class="ticket-categoria-name"><?php echo htmlspecialchars($label); ?></span>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnContinuarCategoriaTicket">Continuar <i class="fa-solid fa-arrow-right ms-1"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Levantar ticket (Sabueso) -->
<div class="modal fade" id="modalLevantarTicket" tabindex="-1" aria-labelledby="modalLevantarTicketLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalLevantarTicketLabel">
                    <i class="fa-solid fa-ticket me-2"></i>Levantar ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Completa los campos con datos correctos. La descripción debe ser clara para que el equipo pueda dar seguimiento.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="modal_id_tipo_ticket" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select id="modal_id_tipo_ticket" class="form-select" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-none" aria-hidden="true">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control bg-light" value="Abierto" readonly>
                    </div>
                    <!-- Prioridad siempre Alta en backend; select oculto por si el JS legacy lo rellena -->
                    <div class="col-md-6 d-none" aria-hidden="true">
                        <label for="modal_id_prioridad" class="form-label">Prioridad</label>
                        <select id="modal_id_prioridad" class="form-select" aria-hidden="true">
                            <option value="">—</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-none" aria-hidden="true">
                        <label for="modal_id_origen_ticket" class="form-label">Origen <span class="text-danger">*</span></label>
                        <select id="modal_id_origen_ticket" class="form-select" required aria-hidden="true">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_id_credito" class="form-label">ID de crédito <span class="text-danger">*</span></label>
                        <input type="number" id="modal_id_credito" class="form-control" placeholder="ID de crédito" min="1" step="1" required>
                    </div>
                    <!-- Vencimiento automático +24h en backend -->
                    <div class="col-md-6 d-none" aria-hidden="true">
                        <label for="modal_fecha_vencimiento" class="form-label">Fecha vencimiento (auto)</label>
                        <input type="hidden" id="modal_fecha_vencimiento" value="">
                    </div>
                    <div class="col-12">
                        <label for="modal_descripcion_inicial" class="form-label">Descripción inicial <span class="text-danger">*</span></label>
                        <textarea id="modal_descripcion_inicial" class="form-control" rows="4" placeholder="Describa el motivo del ticket..." required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnLevantarTicket" onclick="enviarLevantarTicket()">
                    <span id="btnLevantarTicketText"><i class="fa-solid fa-check me-1"></i>Levantar ticket</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Solicitud de baja (Levantar ticket > Solicitud de baja) -->
<div class="modal fade" id="modalSolicitudBaja" tabindex="-1" aria-labelledby="modalSolicitudBajaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalSolicitudBajaLabel">
                    <i class="fa-solid fa-user-xmark me-2"></i>Solicitud de baja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Completa los datos de la solicitud. Puedes adjuntar un documento o imagen como evidencia (PDF o foto).</p>
                <form id="formSolicitudBaja">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="solicitud_baja_motivo" class="form-label">Motivo de la solicitud <span class="text-danger">*</span></label>
                            <select id="solicitud_baja_motivo" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Renuncia voluntaria">Renuncia voluntaria</option>
                                <option value="Despido">Despido</option>
                                <option value="Término de contrato">Término de contrato</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="solicitud_baja_detalle_motivo" class="form-label">Detalle del motivo <span class="text-danger">*</span></label>
                            <textarea id="solicitud_baja_detalle_motivo" class="form-control" rows="3" placeholder="Explique los hechos o razones que motivan esta solicitud..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label for="solicitud_baja_descripcion" class="form-label">Descripción u observaciones</label>
                            <textarea id="solicitud_baja_descripcion" class="form-control" rows="2" placeholder="Observaciones adicionales (opcional)"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="solicitud_baja_nombre_colaborador" class="form-label">Colaborador a dar de baja <span class="text-danger">*</span></label>
                            <input type="text" id="solicitud_baja_nombre_colaborador" class="form-control" placeholder="Nombre completo de la persona" maxlength="255" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adjuntar evidencia (foto o PDF)</label>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <input type="file" id="solicitud_baja_adjunto" class="d-none" accept=".pdf,image/jpeg,image/png,image/gif,image/webp" multiple>
                                <button type="button" class="btn btn-outline-primary" id="btnSolicitudBajaElegirArchivos">
                                    <i class="fa fa-paperclip me-2"></i>Elegir archivos
                                </button>
                                <span id="solicitud_baja_count_archivos" class="text-muted small">No se ha seleccionado ningún archivo</span>
                            </div>
                            <small class="text-muted d-block">Puedes subir múltiples archivos PDF o imágenes.</small>
                            <div id="solicitud_baja_lista_archivos" class="mt-2" style="display: none;"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarSolicitudBaja">
                    <span id="btnEnviarSolicitudBajaText"><i class="fa-solid fa-paper-plane me-1"></i>Enviar solicitud</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Plantilla (Levantar ticket > Plantilla) -->
<div class="modal fade" id="modalTicketPlantilla" tabindex="-1" aria-labelledby="modalTicketPlantillaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTicketPlantillaLabel"><i class="fa-solid fa-file-lines me-2"></i>Ticket Plantilla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Solicite una plantilla o documento. Opcionalmente adjunte un archivo de referencia.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="ticket_plantilla_tipo" class="form-label">Tipo de plantilla <span class="text-danger">*</span></label>
                        <select id="ticket_plantilla_tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="Solicitud de formato">Solicitud de formato</option>
                            <option value="Documento plantilla">Documento plantilla</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="ticket_plantilla_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea id="ticket_plantilla_descripcion" class="form-control" rows="4" placeholder="Describa qué plantilla necesita..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label for="ticket_plantilla_adjunto" class="form-label">Adjuntar (opcional)</label>
                        <input type="file" id="ticket_plantilla_adjunto" class="form-control" accept=".pdf,image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">PDF o imagen. Máx. recomendado: 10 MB.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarTicketPlantilla"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atención al cliente (Levantar ticket > Atención al cliente) -->
<div class="modal fade" id="modalTicketAtencionCliente" tabindex="-1" aria-labelledby="modalTicketAtencionClienteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTicketAtencionClienteLabel"><i class="fa-regular fa-message me-2"></i>Ticket Atención al cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Registre una queja, consulta o solicitud de seguimiento. Indique prioridad y datos de contacto si aplica.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="ticket_atencion_asunto" class="form-label">Asunto <span class="text-danger">*</span></label>
                        <input type="text" id="ticket_atencion_asunto" class="form-control" placeholder="Resumen del tema" maxlength="255" required>
                    </div>
                    <div class="col-12">
                        <label for="ticket_atencion_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea id="ticket_atencion_descripcion" class="form-control" rows="4" placeholder="Describa la situación o consulta..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="ticket_atencion_prioridad" class="form-label">Prioridad</label>
                        <select id="ticket_atencion_prioridad" class="form-select">
                            <option value="alta">Alta</option>
                            <option value="media" selected>Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="ticket_atencion_telefono" class="form-label">Teléfono de contacto</label>
                        <input type="text" id="ticket_atencion_telefono" class="form-control" placeholder="Opcional" maxlength="50">
                    </div>
                    <div class="col-12">
                        <label for="ticket_atencion_email" class="form-label">Correo de contacto</label>
                        <input type="email" id="ticket_atencion_email" class="form-control" placeholder="Opcional" maxlength="100">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarTicketAtencionCliente"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Validaciones (Levantar ticket > Validación de domicilio) -->
<div class="modal fade" id="modalTicketValidacion" tabindex="-1" aria-labelledby="modalTicketValidacionLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTicketValidacionLabel"><i class="fa-solid fa-clipboard-check me-2"></i>Ticket Validación de domicilio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="ticket-validacion-form rounded-3 p-3 mb-0" style="background-color: #fff; border: 1px solid #d1d5db; border-top: 5px solid #4f46e5; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07);">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="ticket_validacion_descripcion" class="form-label fw-semibold"><i class="fa-solid fa-align-left text-primary me-1"></i>Descripción <span class="text-danger">*</span></label>
                            <textarea id="ticket_validacion_descripcion" class="form-control" rows="4" placeholder="Describa qué debe validarse (domicilio, dirección, etc.)..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-paperclip text-primary me-1"></i>Archivos <span class="text-muted fw-normal">(PDF o imágenes, varios)</span></label>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <input type="file" id="ticket_validacion_adjunto" class="d-none" accept=".pdf,image/jpeg,image/png,image/gif,image/webp" multiple>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="ticket_validacion_btn_archivos"><i class="fa-solid fa-paperclip me-1"></i>Elegir archivos</button>
                                <span class="text-muted small" id="ticket_validacion_count_archivos">Ningún archivo seleccionado</span>
                            </div>
                            <div class="form-text mb-2">Puedes subir múltiples archivos PDF o imagen. Máx. recomendado: 10 MB c/u.</div>
                            <div id="ticket_validacion_lista_archivos" class="d-none"></div>
                        </div>
                        <div class="col-12">
                            <label for="ticket_validacion_nota" class="form-label fw-semibold"><i class="fa-solid fa-sticky-note text-primary me-1"></i>Nota</label>
                            <textarea id="ticket_validacion_nota" class="form-control" rows="2" placeholder="Observaciones o comentarios adicionales..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="ticket_validacion_url" class="form-label fw-semibold"><i class="fa-solid fa-link text-primary me-1"></i>Link <span class="text-muted fw-normal">(URL de dirección)</span></label>
                            <input type="url" id="ticket_validacion_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarTicketValidacion"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Viáticos (Levantar ticket > Viáticos) -->
<div class="modal fade" id="modalTicketViaticos" tabindex="-1" aria-labelledby="modalTicketViaticosLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTicketViaticosLabel"><i class="fa-solid fa-money-bill-1 me-2"></i>Ticket Viáticos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Solicite viáticos o comprobación de gastos. Opcionalmente adjunte comprobante.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="ticket_viaticos_tipo" class="form-label">Tipo de viático <span class="text-danger">*</span></label>
                        <select id="ticket_viaticos_tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="Solicitud de viáticos">Solicitud de viáticos</option>
                            <option value="Comprobación de gastos">Comprobación de gastos</option>
                            <option value="Reembolso">Reembolso</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="ticket_viaticos_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea id="ticket_viaticos_descripcion" class="form-control" rows="4" placeholder="Describa el motivo y monto si aplica..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label for="ticket_viaticos_adjunto" class="form-label">Adjuntar (opcional)</label>
                        <input type="file" id="ticket_viaticos_adjunto" class="form-control" accept=".pdf,image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">PDF o imagen. Máx. recomendado: 10 MB.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarTicketViaticos"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aplicaciones de pago (Levantar ticket > Aplicaciones de pago) -->
<div class="modal fade" id="modalTicketAplicacionesPago" tabindex="-1" aria-labelledby="modalTicketAplicacionesPagoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTicketAplicacionesPagoLabel"><i class="fa-solid fa-credit-card me-2"></i>Ticket Aplicaciones de pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Solicite aplicación de pago, aclaración de aplicación o seguimiento. Opcionalmente adjunte comprobante.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="ticket_aplicaciones_tipo" class="form-label">Tipo de solicitud <span class="text-danger">*</span></label>
                        <select id="ticket_aplicaciones_tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="Aplicación de pago">Aplicación de pago</option>
                            <option value="Aclaración de aplicación">Aclaración de aplicación</option>
                            <option value="Seguimiento de pago">Seguimiento de pago</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="ticket_aplicaciones_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea id="ticket_aplicaciones_descripcion" class="form-control" rows="4" placeholder="Describa la solicitud (crédito, monto, fecha si aplica)..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label for="ticket_aplicaciones_adjunto" class="form-label">Adjuntar (opcional)</label>
                        <input type="file" id="ticket_aplicaciones_adjunto" class="form-control" accept=".pdf,image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">PDF o imagen. Máx. recomendado: 10 MB.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarTicketAplicacionesPago"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crédito problemático (Levantar ticket > Crédito problemático) -->
<div class="modal fade" id="modalTicketCreditoProblematico" tabindex="-1" aria-labelledby="modalTicketCreditoProblematicoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTicketCreditoProblematicoLabel"><i class="fa-solid fa-triangle-exclamation me-2"></i>Ticket Crédito problemático</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Reporte o solicitud de seguimiento para créditos en situación problemática. Opcionalmente adjunte evidencia.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="ticket_credito_problematico_tipo" class="form-label">Tipo de solicitud <span class="text-danger">*</span></label>
                        <select id="ticket_credito_problematico_tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="Recuperación">Recuperación</option>
                            <option value="Seguimiento especial">Seguimiento especial</option>
                            <option value="Reporte de situación">Reporte de situación</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="ticket_credito_problematico_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea id="ticket_credito_problematico_descripcion" class="form-control" rows="4" placeholder="Describa el crédito y la situación..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label for="ticket_credito_problematico_adjunto" class="form-label">Adjuntar (opcional)</label>
                        <input type="file" id="ticket_credito_problematico_adjunto" class="form-control" accept=".pdf,image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">PDF o imagen. Máx. recomendado: 10 MB.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarTicketCreditoProblematico"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aclaración de crédito (Levantar ticket > Aclaración de crédito) -->
<div class="modal fade" id="modalTicketAclaracionCredito" tabindex="-1" aria-labelledby="modalTicketAclaracionCreditoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-glass">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalTicketAclaracionCreditoLabel"><i class="fa-solid fa-dollar-sign me-2"></i>Ticket Aclaración de crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Solicite aclaración de saldo, pagos, cargos o información del crédito. Opcionalmente adjunte comprobante.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="ticket_aclaracion_tipo" class="form-label">Tipo de aclaración <span class="text-danger">*</span></label>
                        <select id="ticket_aclaracion_tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="Saldo">Saldo</option>
                            <option value="Pagos">Pagos</option>
                            <option value="Cargos">Cargos</option>
                            <option value="Información general">Información general</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="ticket_aclaracion_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea id="ticket_aclaracion_descripcion" class="form-control" rows="4" placeholder="Describa qué requiere aclarar (crédito, fechas, montos)..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label for="ticket_aclaracion_adjunto" class="form-label">Adjuntar (opcional)</label>
                        <input type="file" id="ticket_aclaracion_adjunto" class="form-control" accept=".pdf,image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text">PDF o imagen. Máx. recomendado: 10 MB.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarTicketAclaracionCredito"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal datos del crédito (búsqueda por ID) - compacto vertical -->
<div class="modal fade" id="modalDatosCredito" tabindex="-1" aria-labelledby="modalDatosCreditoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-glass shadow-sm">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title text-primary mb-0" id="modalDatosCreditoLabel">
                    <i class="fa-solid fa-user-tag me-2"></i>Datos del crédito
                </h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-3" id="modalDatosCreditoBody">
                <!-- Se llena por JS cuando existe el crédito -->
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="usarCreditoEnTicket()" title="Abrir formulario de ticket con este ID ya rellenado">
                    <i class="fa-solid fa-check me-1"></i>Usar este crédito
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle del dictamen (menú Ticket): ver dictamen enviado -->
<div class="modal fade" id="modalDetalleDictamen" tabindex="-1" aria-labelledby="modalDetalleDictamenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-glass">
            <div class="modal-header py-2 d-flex align-items-center">
                <h5 class="modal-title mb-0" id="modalDetalleDictamenLabel"><i class="fa-solid fa-file-lines me-2"></i>Detalle del dictamen</h5>
                <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-12 col-md-5 bg-light bg-opacity-50 p-3 border-end">
                        <div class="dictamen-detalle-imagen-principal mb-2 rounded overflow-hidden bg-dark bg-opacity-10" style="min-height: 200px;">
                            <img id="modalDetalleDictamenImgPrincipal" src="" alt="Evidencia" class="img-fluid w-100" style="object-fit: contain; max-height: 280px;">
                        </div>
                        <div class="d-flex flex-wrap gap-2 dictamen-detalle-miniaturas" id="modalDetalleDictamenMiniaturas"></div>
                    </div>
                    <div class="col-12 col-md-7 p-4">
                        <div class="alert alert-info py-2 mb-3 d-flex align-items-center gap-2" id="modalDetalleDictamenNota12h" role="note">
                            <i class="fa-solid fa-clock text-info"></i>
                            <span>Vas a tener 12 horas para visitar al cliente</span>
                        </div>
                        <div class="mb-3"><span class="text-muted small">Tipo</span><div id="modalDetalleDictamenTipo" class="fw-semibold"></div></div>
                        <div class="mb-3"><span class="text-muted small">Descripción</span><div id="modalDetalleDictamenDescripcion" class="text-break"></div></div>
                        <div class="mb-3" id="modalDetalleDictamenDomiciliosWrap" style="display: none;">
                            <span class="text-muted small">Domicilios de visita</span>
                            <div id="modalDetalleDictamenDomicilios" class="mt-1 d-flex flex-column gap-2"></div>
                        </div>
                        <div class="mb-2"><span class="text-muted small">Enviado</span><div id="modalDetalleDictamenEnviado" class="small"></div></div>
                        <div><span class="text-muted small">Visto por gestor</span><div id="modalDetalleDictamenVisto" class="small"></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal ver evidencia en grande (vista Ticket) -->
<div class="modal fade" id="modalVerEvidenciaDictamenTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fa-solid fa-expand me-2"></i>Evidencia</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalVerEvidenciaDictamenTicketImg" src="" alt="Evidencia" class="img-fluid rounded" style="max-height: 85vh; width: auto;">
            </div>
        </div>
    </div>
</div>
