<style>
/* ══════════════════════════════════════════
   CONVENIOS — ESTILOS GLOBALES
══════════════════════════════════════════ */
.conv-header-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.conv-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; }
.conv-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.85; }

/* ── Cards de oferta ── */
.oferta-card {
    border: 2px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
    background: #fff;
    height: 100%;
}
.oferta-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(118, 75, 162, 0.2);
    border-color: #764ba2;
}
.oferta-card.seleccionada {
    border-color: #764ba2;
    background: linear-gradient(135deg, #f5f0ff 0%, #ede8ff 100%);
    box-shadow: 0 8px 24px rgba(118, 75, 162, 0.25);
}
.oferta-card .icono-oferta  { font-size: 1.8rem; margin-bottom: 0.5rem; }
.oferta-card .porcentaje    { font-size: 2.5rem; font-weight: 800; color: #764ba2; line-height: 1; }
.oferta-card .titulo-oferta { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0.3rem 0 0.6rem; }
.oferta-card .desc-oferta   { font-size: 0.82rem; color: #64748b; margin-bottom: 0.8rem; }
.oferta-card .detalle-item  { font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem; }
.oferta-card .detalle-item i { color: #22c55e; margin-right: 4px; }

/* ── Cards congeladas (convenio activo) ── */
.oferta-card.congelada {
    opacity: 0.35;
    filter: grayscale(100%);
    pointer-events: none;
    cursor: default;
}
.oferta-card.seleccionada-final {
    border-color: #764ba2;
    background: linear-gradient(135deg, #f5f0ff 0%, #ede8ff 100%);
    box-shadow: 0 8px 24px rgba(118, 75, 162, 0.25);
    pointer-events: none;
}
.oferta-card .check-confirmado {
    position: absolute;
    top: -12px;
    left: -12px;
    width: 28px;
    height: 28px;
    background: #22c55e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.85rem;
    box-shadow: 0 2px 8px rgba(34,197,94,0.4);
}

/* ── Oferta con convenio previo (gris) ── */
.oferta-card.oferta-historica {
    opacity: 0.5;
    filter: grayscale(100%);
    border-color: #9ca3af !important;
    cursor: not-allowed;
    pointer-events: none;
    position: relative;
    background: #f3f4f6;
}
.oferta-card.oferta-historica::after {
    content: "Convenio previo";
    position: absolute;
    top: 10px;
    right: 10px;
    background: #6b7280;
    color: white;
    font-size: 0.65rem;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
body.dark-mode .oferta-card.oferta-historica {
    opacity: 0.4;
    background: #1f2937;
    border-color: #4b5563 !important;
}
body.dark-mode .oferta-card.oferta-historica::after {
    background: #4b5563;
    color: #e5e7eb;
}


/* ── Badge sobre la card (completado / cancelado) ── */
.badge-historial-card {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 0.65rem;
    padding: 3px 9px;
    border-radius: 12px;
    font-weight: 700;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    z-index: 2;
}
.badge-completado-card {
    background: #1e40af;
    color: #fff;
}
.badge-cancelado-card {
    background: #991b1b;
    color: #fff;
}

/* ── Card completada (azul apagado) ── */
.oferta-card.oferta-completada {
    opacity: 0.45;
    filter: grayscale(60%);
    border-color: #93c5fd !important;
    background: #eff6ff;
    pointer-events: none;
    cursor: not-allowed;
}

/* ── Card cancelada (rojo apagado) ── */
.oferta-card.oferta-cancelada {
    opacity: 0.45;
    filter: grayscale(60%);
    border-color: #fca5a5 !important;
    background: #fff1f1;
    pointer-events: none;
    cursor: not-allowed;
}

/* ── Dark mode ── */
body.dark-mode .oferta-card.oferta-completada {
    background: #1e3a5f;
    border-color: #3b82f6 !important;
    opacity: 0.4;
}
body.dark-mode .oferta-card.oferta-cancelada {
    background: #3b1010;
    border-color: #ef4444 !important;
    opacity: 0.4;
}


/* ── Slider de semanas ── */
.conv-slider-section {
    background: linear-gradient(135deg, #f8f5ff 0%, #ede8ff 100%);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-top: 1.5rem;
}
.conv-slider-section h5 { font-weight: 700; color: #5b2d8e; margin-bottom: 0.3rem; }
.semanas-valor {
    font-size: 2.8rem;
    font-weight: 800;
    color: #764ba2;
    line-height: 1;
    text-align: center;
}
.pago-semanal-calculado {
    font-size: 1.1rem;
    font-weight: 600;
    color: #16a34a;
    text-align: center;
    margin-top: 0.25rem;
}
input[type=range].conv-range {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    background: #c4b5fd;
    border-radius: 5px;
    outline: none;
    margin: 0.5rem 0;
}
input[type=range].conv-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 22px;
    height: 22px;
    background: #764ba2;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(118, 75, 162, 0.4);
}
.semanas-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.72rem;
    color: #94a3b8;
    margin-top: 0.2rem;
}

/* ── Tabla de amortización ── */
.conv-amort-section {
    margin-top: 1.5rem;
    animation: fadeInUp 0.35s ease;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.amort-resumen td:first-child { font-weight: 600; color: #64748b; }
.amort-resumen td:last-child  { font-weight: 700; color: #1e293b; font-size: 1.1rem; }
.tabla-amort thead th {
    background: linear-gradient(135deg, #ede9fe 0%, #c7d2fe 100%);
    color: #5b2d8e;
    font-size: 0.82rem;
    font-weight: 600;
    text-align: center;
    padding: 0.65rem 0.5rem;
    border: none;
}
body.dark-mode .tabla-amort thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.tabla-amort tbody td {
    text-align: center;
    font-size: 0.83rem;
    padding: 0.55rem 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}
.tabla-amort tbody tr:nth-child(even) td { background: #f8f5ff; }

/* ── Alert incumplimiento ── */
.alerta-incumplimiento {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #f59e0b;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    color: #92400e;
    font-size: 0.88rem;
    margin-bottom: 1rem;
}

/* ── Info crédito banner ── */
.credito-banner {
    background: linear-gradient(90deg, #1e293b 0%, #334155 100%);
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    color: #f8fafc;
    margin-bottom: 1.25rem;
}
.credito-banner .label  { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
.credito-banner .valor  { font-size: 1rem; font-weight: 700; }
.credito-banner .bucket-badge {
    font-size: 0.75rem;
    padding: 0.3em 0.8em;
    border-radius: 20px;
    font-weight: 700;
}

/* ── Banner convenio activo ── */
.banner-convenio-activo-light {
    background: linear-gradient(135deg, #d1fae5 0%, #bbf7d0 100%) !important;
    border: 1px solid #22c55e !important;
    border-radius: .75rem !important;
    color: #166534 !important;
    font-weight: 500;
}

/* ══════════════════════════════════════════
   DARK MODE
══════════════════════════════════════════ */
body.dark-mode .oferta-card {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .oferta-card:hover {
    border-color: #a78bfa;
    box-shadow: 0 12px 28px rgba(167, 139, 250, 0.2);
}
body.dark-mode .oferta-card.seleccionada {
    background: linear-gradient(135deg, #2d1b69 0%, #3b1f6e 100%);
    border-color: #a78bfa;
    box-shadow: 0 8px 24px rgba(167, 139, 250, 0.25);
}
body.dark-mode .oferta-card .titulo-oferta { color: #f1f5f9; }
body.dark-mode .oferta-card .desc-oferta   { color: #94a3b8; }
body.dark-mode .oferta-card .detalle-item  { color: #94a3b8; }
body.dark-mode .oferta-card .porcentaje    { color: #c084fc; }

body.dark-mode .oferta-card.seleccionada-final {
    background: linear-gradient(135deg, #2d1b69 0%, #3b1f6e 100%);
    border-color: #a78bfa;
}
body.dark-mode .oferta-card.congelada {
    border-color: #334155;
}

body.dark-mode .conv-slider-section {
    background: linear-gradient(135deg, #1e1533 0%, #2a1a4e 100%);
}
body.dark-mode .conv-slider-section h5     { color: #c084fc !important; }
body.dark-mode .semanas-valor              { color: #a78bfa; }
body.dark-mode .pago-semanal-calculado     { color: #4ade80; }
body.dark-mode .semanas-labels             { color: #64748b; }
body.dark-mode input[type=range].conv-range { background: #4c1d95; }

body.dark-mode .tabla-amort tbody td {
    border-bottom: 1px solid #334155;
    color: #e2e8f0;
}
body.dark-mode .tabla-amort tbody tr:nth-child(even) td { background: #1e293b; }
body.dark-mode .amort-resumen td:first-child { color: #94a3b8; }
body.dark-mode .amort-resumen td:last-child  { color: #f1f5f9; }

body.dark-mode .alerta-incumplimiento {
    background: linear-gradient(135deg, #422006 0%, #78350f 100%);
    border-color: #d97706;
    color: #fed7aa;
}

body.dark-mode #convContenido h5[style*="color:#5b2d8e"],
body.dark-mode #convContenido h5[style*="color: #5b2d8e"] {
    color: #c084fc !important;
}

body.dark-mode #amortResumenCards .border {
    border-color: #334155 !important;
    background: #1e293b;
    color: #e2e8f0;
}
body.dark-mode #amortResumenCards .text-muted { color: #64748b !important; }

body.dark-mode #resultadosBusqueda .list-group-item {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode #resultadosBusqueda .list-group-item:hover {
    background: #2d3748;
    color: #f1f5f9;
}

body.dark-mode .banner-convenio-activo-light {
    background: linear-gradient(135deg, #166534 0%, #14532d 100%) !important;
    border: 1px solid #22c55e !important;
    color: #bbf7d0 !important;
}

/* ── Badges historial ── */
.badge-estatus-activo,
.badge-estatus-cancelado,
.badge-estatus-completado,
.badge-estatus-incumplimiento {
    display: inline-block;
    font-size: 0.68rem;
    padding: 0.25em 0.65em;
    border-radius: 20px;
    font-weight: 700;
    letter-spacing: 0.03em;
}
.badge-estatus-activo        { background:#dcfce7; border:1px solid #22c55e; color:#166534; }
.badge-estatus-cancelado     { background:#fee2e2; border:1px solid #ef4444; color:#991b1b; }
.badge-estatus-completado    { background:#dbeafe; border:1px solid #3b82f6; color:#1e40af; }
.badge-estatus-incumplimiento{ background:#fef3c7; border:1px solid #f59e0b; color:#92400e; }

body.dark-mode .badge-estatus-activo        { background:rgba(34,197,94,0.15);  border-color:#22c55e; color:#4ade80; }
body.dark-mode .badge-estatus-cancelado     { background:rgba(239,68,68,0.15);  border-color:#ef4444; color:#f87171; }
body.dark-mode .badge-estatus-completado    { background:rgba(59,130,246,0.15); border-color:#3b82f6; color:#60a5fa; }
body.dark-mode .badge-estatus-incumplimiento{ background:rgba(245,158,11,0.15); border-color:#f59e0b; color:#fbbf24; }


.fila-historial { cursor: pointer; }
.fila-historial:hover td { background: rgba(118,75,162,0.06); }
.fila-detalle-amort td { background: #f8f5ff; padding: 0 !important; }
body.dark-mode .fila-detalle-amort td { background: #1e1a2e; }
.amort-accordion { padding: 1rem 1.5rem; }
.amort-accordion .tabla-amort thead th { font-size: .75rem; }


/* ── Barra de progreso convenio ── */
.conv-progress-wrap {
    margin-top: 1rem;
    padding: 1rem 1.25rem;
    background: #f8f5ff;
    border-radius: .75rem;
    border: 1px solid #e9d5ff;
}
body.dark-mode .conv-progress-wrap {
    background: #1e1533;
    border-color: #4c1d95;
}
.conv-progress-label {
    display: flex;
    justify-content: space-between;
    font-size: .78rem;
    font-weight: 600;
    margin-bottom: .4rem;
    color: #5b2d8e;
}
body.dark-mode .conv-progress-label { color: #c084fc; }
.conv-progress-bar-bg {
    width: 100%;
    height: 14px;
    background: #e9d5ff;
    border-radius: 99px;
    overflow: hidden;
}
body.dark-mode .conv-progress-bar-bg { background: #3b1f6e; }
.conv-progress-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #764ba2, #667eea);
    transition: width .6s ease;
}
.conv-progress-info {
    display: flex;
    justify-content: space-between;
    font-size: .75rem;
    margin-top: .4rem;
    color: #64748b;
}
body.dark-mode .conv-progress-info { color: #94a3b8; }
.conv-progress-info .pagado  { color: #22c55e; font-weight: 700; }
.conv-progress-info .restante { color: #f59e0b; font-weight: 700; }

</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-xxl py-4">

    <!-- HEADER -->
    <div class="conv-header-gradient">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 style="color:#fff;"><i class="fa-solid fa-handshake me-2"></i>Convenios de Liquidación</h4>
                <p>Consulta y genera ofertas especiales para créditos en mora</p>
            </div>
            <i class="fa-solid fa-sack-dollar" style="font-size:3rem;opacity:0.3;"></i>
        </div>
    </div>

    <!-- BUSCADOR -->
   <div class="card mb-3">
    <div class="card-body">
        <label class="form-label fw-semibold">Buscar crédito por ID</label>
        <div class="d-flex gap-3">
            <div class="input-group">
                <input type="number" id="inputBusqueda" class="form-control"
                       placeholder="Ej: 123456"
                       autocomplete="off"
                       min="1">
                <button class="btn btn-primary" id="btnBuscar" onclick="window.buscarCredito()">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                </button>
            </div>
            <button type="button" class="btn btn-outline-warning"
                    onclick="window.abrirModalMigracion()">
                <i class="fas fa-file-import"></i> Registrar Convenio Existente
            </button>
        </div>
    </div>
</div>

    <!-- CONTENIDO DINÁMICO -->
    <div id="convContenido" style="display:none;">

        <!-- BANNER DEL CRÉDITO -->
        <div id="creditoBanner" class="credito-banner">
            <!-- se llena por JS -->
        </div>

        <!-- Botón historial — va después del creditoBanner -->
<div class="text-end mb-3" id="btnHistorialWrap" style="display:none;">
    <button class="btn btn-outline-secondary btn-sm" onclick="window.abrirHistorial()">
        <i class="fa-solid fa-clock-rotate-left me-1"></i> Ver historial de convenios
    </button>
</div>



        <!-- ALERTA INCUMPLIMIENTO -->
        <div id="alertaIncumplimiento" class="alerta-incumplimiento" style="display:none;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <strong>Incumplimiento detectado:</strong> Este crédito tuvo un convenio anterior con pago vencido hace más de 30 días. Puedes generar uno nuevo.
        </div>

        <!-- OFERTAS -->
        <h5 class="fw-bold mb-3" style="color:#5b2d8e;">
            <i class="fa-solid fa-tags me-2"></i>Ofertas disponibles para este crédito
        </h5>
        <div id="ofertasContainer" class="row g-3 mb-2">
            <!-- cards generadas por JS -->
        </div>

        <!-- SLIDER DE SEMANAS -->
        <div id="sliderSection" class="conv-slider-section" style="display:none;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 id="sliderTitulo">Selecciona el plazo de pago</h5>
                    <p class="text-muted small mb-2">El total a pagar se dividirá entre las semanas elegidas.</p>
                    <input type="range" class="conv-range" id="sliderSemanas"
                           min="1" max="12" value="1"
                           oninput="window.actualizarSlider(this.value)">
                    <div class="semanas-labels">
                        <span id="labelMin">1 sem</span>
                        <span id="labelMax">12 sem</span>
                    </div>
                </div>
                <div class="col-md-4 text-center mt-3 mt-md-0">
                    <div class="semanas-valor" id="semanasValor">1</div>
                    <div style="font-size:0.8rem;color:#94a3b8;">semanas</div>
                    <div class="pago-semanal-calculado" id="pagoSemanalCalc">$0.00 / semana</div>
                </div>
            </div>
            <div class="text-center mt-3">
                <button class="btn btn-outline-primary btn-sm" id="btnVerAmort"
                        onclick="window.verTablaAmortizacion()" style="display:none;">
                    <i class="fa-solid fa-table me-1"></i> Ver Tabla de Amortización
                </button>
            </div>
        </div>

        <!-- TABLA DE AMORTIZACIÓN -->
        <div id="amortSection" class="conv-amort-section" style="display:none;">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 id="amortTitulo" class="fw-bold mb-3" style="color:#5b2d8e;"></h5>
                    <div class="row text-center g-2 mb-3" id="amortResumenCards">
                        <!-- llenado por JS -->
                    </div>
                    <div class="table-responsive">
                        <table class="tabla-amort table table-borderless">
                            <thead>
                                <tr>
                                   <th>Semana</th>
                                   <th>Fecha de Pago Preferencial</th>
                                   <th id="thSemanalCapital">Semanal / Capital</th>
                                   <th>Saldo Restante</th>
                                   <th>Pago Realizado</th>
                                   <th>Estatus</th>
                                           <th>Accion</th>

                                </tr>
                            </thead>
                            <tbody id="tablaAmortBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button class="btn btn-success" id="btnGuardar" onclick="window.guardarConvenio()">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Confirmar y Guardar Convenio
                        </button>
                        <button class="btn btn-outline-secondary" id="btnPdf" onclick="window.descargarPdf()" style="display:none;">
                            <i class="fa-solid fa-file-pdf me-1"></i> Descargar PDF
                        </button>

                        <button class="btn btn-danger btn-sm" id="btnCancelar"
                            onclick="window.cancelarConvenio()" style="display:none;">
                            <i class="fa-solid fa-ban me-1"></i> Cancelar Convenio
                       </button>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL HISTORIAL -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                    Historial de Convenios — <span id="modalHistorialCredito"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">

                <!-- Estado cargando -->
                <div id="historialLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Cargando historial...</div>
                </div>

                <!-- Sin registros -->
                <div id="historialVacio" class="text-center py-5" style="display:none;">
                    <i class="fa-solid fa-folder-open fa-2x text-muted mb-2"></i>
                    <div class="text-muted">Este crédito no tiene convenios registrados.</div>
                </div>

                <!-- Tabla -->
                <div id="historialTablaWrap" style="display:none;">
                    <div class="table-responsive">
                        <table class="tabla-amort table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha Acuerdo</th>
                                    <th>Producto / Oferta</th>
                                    <th>Total a Pagar</th>
                                    <th>Semanas</th>
                                    <th>Estatus</th>
                                    <th>Autorizado por</th>
                                    <th>Cancelado por</th>
                                    <th>Fecha Cancelación</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tablaHistorialBody"></tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════ -->
<!-- MODAL MIGRACIÓN DE CONVENIO                  -->
<!-- ════════════════════════════════════════════ -->
<div class="modal fade" id="modalMigracion" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-file-import me-2"></i>Registrar Convenio Existente
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Paso 1: buscar crédito -->
        <div id="migStep1">
          <label class="form-label fw-bold">ID Crédito</label>
          <div class="input-group mb-3">
            <input type="number" id="migIdCredito" class="form-control"
                   placeholder="Ej. 193141">
            <button class="btn btn-primary" onclick="window.migBuscarCredito()">
                <i class="fas fa-search"></i> Buscar
            </button>
          </div>
          <div id="migInfoCliente" class="alert alert-info d-none"></div>
        </div>

        <!-- Paso 2: datos del convenio (oculto hasta buscar crédito) -->
        <div id="migStep2" class="d-none">
          <hr>
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label">Producto</label>
              <select id="migProducto" class="form-select" onchange="window.migProductoChange()">
                <option value="">Selecciona...</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">% Descuento</label>
              <div class="input-group">
                <input type="number" id="migPorcentaje" class="form-control"
                       min="0" max="100" step="0.01" placeholder="20"
                       oninput="window.migCalcular()">
                <span class="input-group-text">%</span>
              </div>
            </div>

            <div class="col-md-3">
              <label class="form-label">Fecha Inicio</label>
              <input type="date" id="migFechaInicio" class="form-control"
                     oninput="window.migCalcular()">
            </div>

            <div class="col-md-4">
              <label class="form-label">Adeudo Base</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" id="migAdeudo" class="form-control"
                       step="0.01" placeholder="16284.33" oninput="window.migCalcular()">
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Pago Semanal</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" id="migPagoSemanal" class="form-control"
                       step="0.01" placeholder="3250" oninput="window.migCalcular()">
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Bucket Morosidad</label>
              <input type="text" id="migBucket" class="form-control"
                     placeholder="g) 60 a 89 dias">
            </div>

          </div>

          <!-- Preview del cálculo y adjuntar PDF -->
          <div id="migPreview" class="d-none mt-3">
            <hr>
            <div class="row text-center g-2" id="migResumenCards"></div>

            <!-- NUEVO: Sección para adjuntar PDF -->
            <div class="mt-3 p-3 border rounded" style="background: #f8f5ff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <label class="form-label fw-bold mb-1">
                            <i class="fas fa-paperclip me-1"></i>Adjuntar comprobante (PDF)
                        </label>
                        <input type="file" id="migPdfAdjunto"
                               class="form-control form-control-sm"
                               accept=".pdf,application/pdf"
                               onchange="window.validarPdfAdjunto(this)">
                        <small class="text-muted">Opcional: adjunta el PDF del convenio firmado (máx. 5MB)</small>
                    </div>
                    <div id="migPdfPreview" class="text-center" style="min-width: 60px;">
                        <!-- Icono de PDF si hay archivo seleccionado -->
                    </div>
                </div>
            </div>
          </div>
        </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success d-none" id="migBtnGuardar"
                onclick="window.migGuardar()">
            <i class="fas fa-save"></i> Registrar Convenio
        </button>
      </div>

    </div>
  </div>
</div>

<script>

// ══════════════════════════════════════════════════════
//  Estado global
// ══════════════════════════════════════════════════════
var _credito       = null;
var _ofertas       = [];
var _ofertaActiva  = null;
var _semanasActual = 1;
var _rayoModo = null; // 'unico' | 'quincenas' | 'semanal'
// var _pollingInterval = null;

// ══════════════════════════════════════════════════════
//  BUSCAR CRÉDITO
// ══════════════════════════════════════════════════════
window.buscarCredito = function() {
    var id = document.getElementById('inputBusqueda').value.trim();

    if (!id || isNaN(id) || parseInt(id) < 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Debes ingresar un número de crédito para continuar.',
            confirmButtonColor: '#764ba2',
        });
        return;
    }

    seleccionarCredito(parseInt(id));
};

document.getElementById('inputBusqueda').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') window.buscarCredito();
});

// ══════════════════════════════════════════════════════
//  SELECCIONAR CRÉDITO → ofertas + convenio activo
// ══════════════════════════════════════════════════════
function seleccionarCredito(idCredito) {
    document.getElementById('convContenido').style.display = 'none';
    document.getElementById('btnHistorialWrap').style.display = 'none';

    var bannerPrevio = document.getElementById('bannerConvenioActivo');
    if (bannerPrevio) bannerPrevio.remove();

    Swal.fire({ title: 'Cargando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

    // Llamada 1: ofertas
    http.request({
        endpoint: '/convenios/getOfertasCredito',
        method: 'POST',
        data: { id_credito: idCredito },
        onSuccess: function(respOfertas) {
            if (!respOfertas.success) {
                Swal.close();
                Swal.fire('Sin elegibilidad', respOfertas.mensaje || 'Este crédito no cumple los criterios.', 'info');
                return;
            }

            var datos   = respOfertas.datos;
            var credito = datos.credito;
            var ofertas = datos.ofertas;
            var elegible = datos.elegible;
            var razon   = datos.razon;

            _credito = credito;
            _ofertas = ofertas;

            // Llamada 2: historial para saber qué oferta pintar de gris
            http.request({
                endpoint: '/convenios/getHistorialConvenios',
                method: 'POST',
                data: { id_credito: idCredito },
                onSuccess: function(respHistorial) {
                    if (respHistorial.success && respHistorial.datos) {
                        _credito.historial_convenios = respHistorial.datos;
                    }

                    // Llamada 3: convenio activo
                    http.request({
                        endpoint: '/convenios/getConvenioActivo',
                        method: 'POST',
                        data: { id_credito: idCredito },
                        onSuccess: function(respConvenio) {
                            Swal.close();

                            document.getElementById('btnGuardar').style.display = 'inline-block';
                            document.getElementById('btnPdf').className = 'btn btn-outline-secondary';

                            renderCreditoBanner(credito);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);

                            document.getElementById('sliderSection').style.display = 'none';
                            document.getElementById('amortSection').style.display  = 'none';
                            document.getElementById('alertaIncumplimiento').style.display = 'none';
                            _ofertaActiva = null;

                            document.getElementById('convContenido').style.display = 'block';
                            document.getElementById('btnHistorialWrap').style.display = 'block';

                            if (respConvenio.success && respConvenio.datos && respConvenio.datos.estatus === 'activo') {
                                congelarModulo(respConvenio.datos);
                            }
                        },
                        onError: function() {
                            Swal.close();
                            renderCreditoBanner(credito);
                            renderOfertas(ofertas);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);
                            document.getElementById('convContenido').style.display = 'block';
                        }
                    });
                },
                onError: function() {
                    // Si falla el historial, continuamos sin él
                    http.request({
                        endpoint: '/convenios/getConvenioActivo',
                        method: 'POST',
                        data: { id_credito: idCredito },
                        onSuccess: function(respConvenio) {
                            Swal.close();
                            renderCreditoBanner(credito);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);
                            document.getElementById('convContenido').style.display = 'block';

                            if (respConvenio.success && respConvenio.datos && respConvenio.datos.estatus === 'activo') {
                                congelarModulo(respConvenio.datos);
                            }
                        },
                        onError: function() {
                            Swal.close();
                            renderCreditoBanner(credito);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);
                            document.getElementById('convContenido').style.display = 'block';
                        }
                    });
                }
            });
        },
        onError: function() {
            Swal.close();
            Swal.fire('Error', 'Error al cargar ofertas.', 'error');
        }
    });
}

// ══════════════════════════════════════════════════════
//  BANNER DEL CRÉDITO
// ══════════════════════════════════════════════════════
function renderCreditoBanner(c) {
    var bucketColor = function(b) {
        if (!b) return 'bg-secondary';
        if (b.indexOf('e)') === 0) return 'bg-warning text-dark';
        if (b.indexOf('f)') === 0) return 'bg-orange text-white';
        return 'bg-danger text-white';
    };

    var avance = parseFloat(c.Avance_Pago_Plazo || 0).toFixed(1);
    var adeudo = parseFloat(c.Adeudo_total || 0);
    var fmt    = function(v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    document.getElementById('creditoBanner').innerHTML =
        '<div class="row g-3">' +
            '<div class="col-6 col-md-3"><div class="label">Crédito</div><div class="valor">#' + c.Id_credito + '</div></div>' +
            '<div class="col-6 col-md-3"><div class="label">Cliente</div><div class="valor" style="font-size:0.9rem;">' + c.Nombre_cliente + '</div></div>' +
            '<div class="col-6 col-md-2"><div class="label">Bucket</div><span class="bucket-badge ' + bucketColor(c.Bucket_Morosidad_Real) + '">' + c.Bucket_Morosidad_Real + '</span></div>' +
            '<div class="col-6 col-md-2"><div class="label">Avance pago</div><div class="valor">' + avance + '%</div></div>' +
            '<div class="col-6 col-md-2"><div class="label">Adeudo total Actual</div><div class="valor" style="color:#4ade80;">' + fmt(adeudo) + '</div></div>' +
        '</div>';
}

// ══════════════════════════════════════════════════════
//  RENDERIZAR CARDS DE OFERTAS
// ══════════════════════════════════════════════════════
var OFERTA_ICONOS = { 1: '🎯', 2: '💰', 3: '❄️', 4: '⚡' };

function renderOfertas(ofertas, productosBloqueados) {
    var cont = document.getElementById('ofertasContainer');
    var fmt  = function(v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    // Construir mapa id_producto → nombre desde historial y ofertas actuales
    var _historialProductos = {};
    ofertas.forEach(function(o) {
        _historialProductos[o.id_producto] = o.nombre;
    });
    if (_credito && _credito.historial_convenios) {
        _credito.historial_convenios.forEach(function(c) {
            if (c.id_producto_convenio && c.nombre_producto) {
                _historialProductos[c.id_producto_convenio] = c.nombre_producto;
            }
        });
    }

    // Construir mapa de productos usados en historial (cancelados/completados normales)
    var productosUsados = {};
    if (_credito && _credito.historial_convenios && _credito.historial_convenios.length > 0) {
        var historialOrdenado = _credito.historial_convenios
            .filter(function(c) { return c.estatus !== 'activo'; })
            .sort(function(a, b) { return new Date(b.fecha_alta) - new Date(a.fecha_alta); });

        historialOrdenado.forEach(function(c) {
            var idProd = c.id_producto_convenio;
            if (!productosUsados[idProd]) {
                productosUsados[idProd] = c.estatus;
            }
        });
    }

    // Cards de ofertas disponibles
    var htmlOfertas = ofertas.map(function(o, i) {
        var icono      = OFERTA_ICONOS[o.id_producto] || '📋';
        var primerPago = (o.pago_inicial === 'Si' && o.pago_inicial_monto)
            ? fmt(o.pago_inicial_monto)
            : 'No requiere primer pago';
        var plazoLabel = parseInt(o.semanas_max) === 1
            ? '1 semana (pago único)'
            : o.periodo_inicio + ' a ' + o.semanas_max + ' semanas';

        var estatusPrevio  = productosUsados[o.id_producto] || null;
        var claseAdicional = '';
        var badgeHistorial = '';

        if (estatusPrevio === 'completado') {
            claseAdicional = 'oferta-completada';
            badgeHistorial =
                '<div class="badge-historial-card badge-completado-card">' +
                    '<i class="fa-solid fa-circle-check me-1"></i>Convenio completado' +
                '</div>';
        } else if (estatusPrevio === 'cancelado') {
            claseAdicional = 'oferta-cancelada';
            badgeHistorial =
                '<div class="badge-historial-card badge-cancelado-card">' +
                    '<i class="fa-solid fa-ban me-1"></i>Convenio cancelado' +
                '</div>';
        }

        var clickeable = estatusPrevio ? '' : ' onclick="window.seleccionarOferta(' + i + ')"';
        var cursor     = estatusPrevio ? 'cursor:not-allowed;' : '';

        return '<div class="col-12 col-md-3">' +
            '<div class="oferta-card ' + claseAdicional + '" id="oferta-card-' + i + '"' + clickeable + ' style="' + cursor + '">' +
                badgeHistorial +
                '<div class="icono-oferta">' + icono + '</div>' +
                '<div class="titulo-oferta">' + o.nombre + '</div>' +
                '<div class="porcentaje">' + parseInt(o.porcentaje_descuento) + '%</div>' +
                '<div class="desc-oferta">Ahorro de ' + fmt(o.descuento_monto) + ' sobre ' + fmt(o.monto_base) + '</div>' +
                '<div class="detalle-item"><i class="fa-solid fa-check"></i> Total a pagar: <strong>' + fmt(o.total_a_pagar) + '</strong></div>' +
                '<div class="detalle-item"><i class="fa-solid fa-check"></i> Primer pago: <strong>' + primerPago + '</strong></div>' +
                '<div class="detalle-item"><i class="fa-solid fa-check"></i> Plazos: <strong>' + plazoLabel + '</strong></div>' +
            '</div>' +
        '</div>';
    }).join('');

    // Cards bloqueadas permanentemente por incumplimiento
    var htmlBloqueados = '';
    if (productosBloqueados && productosBloqueados.length > 0) {
        productosBloqueados.forEach(function(idProd) {
            var nombre = _historialProductos[idProd] || 'Producto';
            var icono  = OFERTA_ICONOS[idProd] || '🚫';
            htmlBloqueados +=
                '<div class="col-12 col-md-3">' +
                    '<div class="oferta-card" style="border-color:#ef4444;background:#fff1f1;opacity:0.65;cursor:not-allowed;pointer-events:none;position:relative;">' +
                        '<div style="position:absolute;top:10px;right:10px;background:#ef4444;color:#fff;' +
                             'font-size:0.65rem;padding:3px 9px;border-radius:12px;font-weight:700;">' +
                            '<i class="fas fa-ban me-1"></i>Bloqueado permanente' +
                        '</div>' +
                        '<div class="icono-oferta">' + icono + '</div>' +
                        '<div class="titulo-oferta" style="color:#991b1b;">' + nombre + '</div>' +
                        '<div class="desc-oferta" style="color:#dc2626;font-size:0.8rem;margin-top:.5rem;">' +
                            '<i class="fas fa-triangle-exclamation me-1"></i>' +
                            'Convenio cancelado por incumplimiento.<br>No disponible permanentemente.' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });
    }

    cont.innerHTML = htmlOfertas + htmlBloqueados;
}

// ══════════════════════════════════════════════════════
//  RENDERIZAR CELDA DE PAGO REALIZADO (datos S2Movil)
// ══════════════════════════════════════════════════════
function renderCeldaPagoS2(pagosS2, numeroSemana) {
    var pagos = pagosS2 && pagosS2[numeroSemana] ? pagosS2[numeroSemana] : null;

    if (!pagos || pagos.length === 0) {
        return '<span style="color:#aaa;">—</span>';
    }

    var fmt = function(v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };
    var fmtF = function(s) {
        if (!s) return '';
        var p = s.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };

    var html = '';

    pagos.forEach(function(p) {
        var esSobrante = p.sobrante > 0 && p.capital === 0;

        if (esSobrante) {
            // Línea de sobrante
            html +=
                '<div style="border-left:3px solid #f59e0b;padding-left:6px;margin-bottom:4px;">' +
                    '<span style="display:block;font-size:0.75rem;color:#92400e;font-weight:600;">' +
                        '<i class="fas fa-arrow-right-arrow-left" style="font-size:0.65rem;"></i> Sobrante: ' + fmt(p.montoPago) +
                    '</span>' +
                    '<span style="display:block;font-size:0.72rem;color:#b45309;">' +
                        'Aplicado: ' + fmt(p.montoPago) +
                    '</span>' +
                    '<span style="display:block;font-size:0.70rem;color:#aaa;">' + fmtF(p.fechaValor) + '</span>' +
                '</div>';
        } else {
            // Línea de pago normal
            html +=
                '<div style="border-left:3px solid #22c55e;padding-left:6px;margin-bottom:4px;">' +
                    '<span style="display:block;font-size:0.75rem;font-weight:700;color:#166534;">' +
                        '<i class="fas fa-circle-check" style="font-size:0.65rem;"></i> Pago: ' + fmt(p.montoPago) +
                    '</span>' +
                    '<span style="display:block;font-size:0.72rem;color:#15803d;">' +
                        'Aplicado: ' + fmt(p.capital) +
                    '</span>' +
                    (p.sobrante > 0
                        ? '<span style="display:block;font-size:0.70rem;color:#b45309;">Sobrante: ' + fmt(p.sobrante) + '</span>'
                        : '') +
                    '<span style="display:block;font-size:0.70rem;color:#aaa;">' + fmtF(p.fechaValor) + '</span>' +
                '</div>';
        }
    });

    return html;
}

// ══════════════════════════════════════════════════════
//  CONGELAR MÓDULO (convenio activo existente)
// ══════════════════════════════════════════════════════


function fmtFecha(fechaISO) {
    if (!fechaISO) return '—';
    var p = fechaISO.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}


function congelarModulo(convenio) {

   var progressPrevio = document.getElementById('convProgressWrap');
   if (progressPrevio) progressPrevio.remove();

    var fmt = function(v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    // Identificar card del convenio activo
    var idxOferta = -1;
    for (var i = 0; i < _ofertas.length; i++) {
        if (_ofertas[i].id_producto === convenio.id_producto_convenio) { idxOferta = i; break; }
    }

    // Aplicar clases a cards
    var cards = document.querySelectorAll('.oferta-card');
    for (var j = 0; j < cards.length; j++) {
        if (j === idxOferta) {
            cards[j].classList.add('seleccionada-final');
            if (!cards[j].querySelector('.check-confirmado')) {
                var check = document.createElement('div');
                check.className = 'check-confirmado';
                check.innerHTML = '<i class="fa-solid fa-check"></i>';
                cards[j].appendChild(check);
            }
        } else {
            cards[j].classList.add('congelada');
        }
    }

    // Slider congelado
    var slider = document.getElementById('sliderSemanas');
    slider.disabled = true;
    slider.value    = convenio.numero_semanas;
    _semanasActual  = convenio.numero_semanas;

    if (idxOferta >= 0) {
        _ofertaActiva  = _ofertas[idxOferta];
        slider.min = _ofertaActiva.periodo_inicio;
        slider.max = _ofertaActiva.semanas_max;
        document.getElementById('labelMin').textContent = _ofertaActiva.periodo_inicio + ' sem';
        document.getElementById('labelMax').textContent = _ofertaActiva.semanas_max + ' sem';
        document.getElementById('sliderTitulo').textContent = 'Plazo activo: ' + convenio.nombre_producto;
    }

    document.getElementById('semanasValor').textContent = convenio.numero_semanas;
    document.getElementById('pagoSemanalCalc').textContent =
        '$' + parseFloat(convenio.pago_semanal).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ' / semana';
    document.getElementById('sliderSection').style.display = 'block';

    // Resumen cards
    document.getElementById('amortTitulo').textContent = '📋 ' + convenio.nombre_producto;
document.getElementById('amortResumenCards').innerHTML =
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Deuda Original</div><div class="fw-bold text-primary fs-5">' + fmt(convenio.adeudo_total_original) + '</div></div></div>' +
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Descuento (' + parseInt(convenio.porcentaje_descuento) + '%)</div><div class="fw-bold text-danger fs-5">-' + fmt(convenio.descuento_monto) + '</div></div></div>' +
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Total a Pagar</div><div class="fw-bold text-success fs-5">' + fmt(convenio.total_a_pagar) + '</div></div></div>' +
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Pago Semanal</div><div class="fw-bold text-warning fs-5">' + fmt(convenio.pago_semanal) + '</div></div></div>';

// ── Barra de progreso ──────────────────────────
var totalPagar  = parseFloat(convenio.total_a_pagar);
var amort       = convenio.amortizacion || [];
var montoPagado = 0;

amort.forEach(function(fila) {
    if (fila.estatus_pago === 'pagado') {
        montoPagado += parseFloat(fila.capital || 0);
    }
});

montoPagado      = Math.min(montoPagado, totalPagar);
var montoRestante = Math.max(totalPagar - montoPagado, 0);
var pct           = totalPagar > 0 ? Math.round((montoPagado / totalPagar) * 100) : 0;

document.getElementById('amortResumenCards').insertAdjacentHTML('afterend',
    '<div class="conv-progress-wrap" id="convProgressWrap">' +
        '<div class="conv-progress-label">' +
            '<span><i class="fas fa-chart-line me-1"></i>Progreso del convenio</span>' +
            '<span>' + pct + '% completado</span>' +
        '</div>' +
        '<div class="conv-progress-bar-bg">' +
            '<div class="conv-progress-bar-fill" style="width:' + pct + '%"></div>' +
        '</div>' +
        '<div class="conv-progress-info">' +
            '<span>Inicio: <strong>' + fmt(totalPagar) + '</strong></span>' +
            '<span class="pagado">✓ Pagado: ' + fmt(montoPagado) + '</span>' +
            '<span class="restante">⏳ Restante: ' + fmt(montoRestante) + '</span>' +
            '<span>Meta: <strong>$0.00</strong></span>' +
        '</div>' +
    '</div>'
);


    // Tabla con datos reales del backend
   var filasHtml = convenio.amortizacion.map(function(fila) {

    var esPagado   = fila.estatus_pago === 'pagado';
    var esVencido  = fila.estatus_pago === 'vencido';
    var esPendiente = fila.estatus_pago === 'pendiente';

    var estatusBadge = esPagado
        ? '<span class="badge bg-success">Pagado</span>'
        : esVencido
            ? '<span class="badge bg-danger">Vencido</span>'
            : '<span class="badge bg-warning text-dark">Pendiente</span>';

    // Celda de fecha: si está pagado y tiene fecha_pago_real, mostrarla
    var celdaFecha = esPagado && fila.fecha_pago_real
        ? '<span style="display:block;font-weight:600;color:#22c55e;">'
          + fmtFecha(fila.fecha_pago_real) + '</span>'
          + '<span style="display:block;color:#888;font-size:0.82em;">Pagado</span>'
        : fmtFechaRango(fila.fecha_pago);


  // Celda de pago realizado — solo datos de BD, no S2Movil
var celdaMontoPagado = '';
if (esPagado) {
    celdaMontoPagado = '<span style="color:#22c55e;font-weight:600;">' + fmt(fila.pago_semanal) + '</span>'
        + (fila.fecha_pago_real
            ? '<br><small style="color:#888;">' + fmtFecha(fila.fecha_pago_real) + '</small>'
            : '');
} else {
    celdaMontoPagado = '<span style="color:#ccc;">—</span>';
}


    // Botón de acción
    var btnAccion = '';
    if (esPagado) {
        btnAccion = fila.fecha_pago_real
            ? '<small class="text-success"><i class="fas fa-check-double"></i> '
              + fmtFecha(fila.fecha_pago_real) + '</small>'
            : '<span class="badge bg-success">✓</span>';
         } else if (esPendiente || esVencido) {
    btnAccion = '<span style="color:#ccc;">—</span>';
           }

    return '<tr>' +
        '<td>Semana ' + fila.numero_semana + '</td>' +
        '<td>' + celdaFecha + '</td>' +
        '<td>' +
            '<span style="display:block;font-weight:600;">' + fmt(fila.pago_semanal) + '</span>' +
            '<span style="display:block;font-size:0.82em;color:#888;">' + fmt(fila.capital) + '</span>' +
        '</td>' +
        '<td>' + fmt(fila.saldo_restante) + '</td>' +
        '<td class="celda-monto-pagado">' + celdaMontoPagado + '</td>' +
        '<td class="celda-estatus">' + estatusBadge + '</td>' +
        '<td>' + btnAccion + '</td>' +
    '</tr>';
}).join('');
    document.getElementById('tablaAmortBody').innerHTML = filasHtml;
    document.getElementById('amortSection').style.display = 'block';

    bindBotonesPago(convenio.id_credito);


    // Solo PDF visible
    document.getElementById('btnGuardar').style.display = 'none';
    document.getElementById('btnPdf').className = 'btn btn-primary';
    document.getElementById('btnPdf').style.display = 'inline-block';


    document.getElementById('btnCancelar').style.display = 'inline-block';
    window._idConvenioActivo = convenio.id;

    // Banner informativo
    var fechaAcuerdo   = new Date(convenio.fecha_acuerdo + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    var fechaUltimoPago = new Date(convenio.fecha_ultimo_pago + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    var pdfBadge = '';
if (convenio.pdf_adjunto) {
    pdfBadge =
        '&nbsp;<a href="' + convenio.pdf_adjunto + '" target="_blank" ' +
        'class="text-decoration-none ms-2" ' +
        'style="display:inline-flex;align-items:center;gap:4px;background:#fff;color:#166534;' +
        'border:1px solid #22c55e;padding:2px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;" ' +
        'title="Ver documento adjunto del convenio">' +
        '<i class="fa-solid fa-file-pdf" style="color:#dc2626;"></i> Ver documento adjunto' +
        '</a>';
}

var banner = document.getElementById('creditoBanner');
banner.insertAdjacentHTML('afterend',
    '<div id="bannerConvenioActivo" class="alert d-flex align-items-center gap-2 mb-3 banner-convenio-activo-light">' +
        '<i class="fa-solid fa-lock fa-lg"></i>' +
        '<div>' +
            '<strong>Convenio activo registrado.</strong> ' +
            'Acuerdo del ' + fechaAcuerdo + '. ' +
            'Último pago: ' + fechaUltimoPago + '. ' +
            'Solo puedes descargar el PDF.' +
            pdfBadge +
        '</div>' +
    '</div>'
);

    document.getElementById('amortSection').scrollIntoView({ behavior: 'smooth' });
}

// ════════════════════════════════════════════════
// REGISTRAR PAGO DE SEMANA
// ════════════════════════════════════════════════
function bindBotonesPago(idCredito) {
    document.querySelectorAll('.btn-registrar-pago').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var semana   = btn.getAttribute('data-semana');
            var convenio = btn.getAttribute('data-convenio');
            var credito  = idCredito || btn.getAttribute('data-credito');

            Swal.fire({
                title: '¿Registrar pago?',
                html: 'Se marcará la <b>Semana ' + semana + '</b> como pagada.<br>'
                    + '<small class="text-muted">La fecha y monto se verificarán con S2Movil.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#22c55e',
            }).then(function(result) {
                if (!result.isConfirmed) return;

                btn.disabled  = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                http.request({
                    endpoint: '/convenios/registrarPago',
                    method:   'POST',
                    data: {
                        id_convenio:   convenio,
                        numero_semana: semana,
                        id_credito:    credito,
                    },
                    onSuccess: function(resp) {
                        if (!resp.success) {
                            Swal.fire('Error', resp.mensaje || 'No se pudo registrar.', 'error');
                            btn.disabled  = false;
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            return;
                        }

                        Swal.fire({
                            title: '¡Registrado!',
                            html: 'Semana ' + semana + ' marcada como pagada.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload(); // AUTO-REFRESH después de pago
                        });
                    },
                    onError: function() {
                        Swal.fire('Error', 'Error de conexión.', 'error');
                        btn.disabled  = false;
                        btn.innerHTML = '<i class="fas fa-check"></i>';
                    }
                });
            });
        });
    });
}

// ══════════════════════════════════════════════════════
//  POLLING — cada 30s verifica si se generó un convenio
// ══════════════════════════════════════════════════════
//function iniciarPolling(idCredito) {
//    detenerPolling();
//    _pollingInterval = setInterval(function() {
//        http.request({
//            endpoint: '/convenios/getConvenioActivo',
//            method: 'POST',
//            data: { id_credito: idCredito },
//            onSuccess: function(resp) {
//                if (resp.success && resp.datos && resp.datos.estatus === 'activo') {
//                    detenerPolling();
//                    congelarModulo(resp.datos);
//                }
//            },
//            onError: function() {}
//        });
//    }, 30000);
//}

//function detenerPolling() {
//    if (_pollingInterval) {
//        clearInterval(_pollingInterval);
//        _pollingInterval = null;
//    }
//}

// ══════════════════════════════════════════════════════
//  SELECCIONAR OFERTA → mostrar slider
// ══════════════════════════════════════════════════════
window.seleccionarOferta = function(idx) {
    document.querySelectorAll('.oferta-card').forEach(function(c) { c.classList.remove('seleccionada'); });
    document.getElementById('oferta-card-' + idx).classList.add('seleccionada');

    _ofertaActiva = _ofertas[idx];

    var esLiquidacionRayo = _ofertaActiva.id_producto === 4;

    var slider = document.getElementById('sliderSemanas');

    if (esLiquidacionRayo) {
    _rayoModo = 'unico'; // default
    slider.style.display = 'none';
    document.querySelector('.semanas-labels').style.display = 'none';

    var btnsExistentes = document.getElementById('rayoBtns');
    if (btnsExistentes) btnsExistentes.remove();

    slider.insertAdjacentHTML('afterend',
        '<div id="rayoBtns" class="d-flex gap-2 justify-content-center mt-2">' +
            '<button class="btn btn-primary rayo-btn" onclick="window.seleccionarRayo(1, event)">⚡ 1 Pago único</button>' +
            '<button class="btn btn-outline-primary rayo-btn" onclick="window.seleccionarRayo(2, event)">📅 2 Quincenas</button>' +
            '<button class="btn btn-outline-primary rayo-btn" onclick="window.seleccionarRayo(4, event)">📆 4 Semanas</button>' +
        '</div>'
    );

    _semanasActual = 1;
    document.getElementById('semanasValor').textContent = '1 pago único';
    var total = parseFloat(_ofertaActiva.total_a_pagar);
    document.getElementById('pagoSemanalCalc').textContent =
        '$' + total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' / pago';
} else {
    slider.style.display = 'block';
    document.querySelector('.semanas-labels').style.display = 'flex';
    var btnsRayo = document.getElementById('rayoBtns');
    if (btnsRayo) btnsRayo.remove();
    _rayoModo = null;

    slider.min   = _ofertaActiva.periodo_inicio;
    slider.max   = _ofertaActiva.semanas_max;
    slider.step  = 1;
    slider.value = _ofertaActiva.periodo_inicio;
    document.getElementById('labelMin').textContent = _ofertaActiva.periodo_inicio + ' sem';
    document.getElementById('labelMax').textContent = _ofertaActiva.semanas_max + ' sem';

}
    document.getElementById('sliderTitulo').textContent = 'Plazo para: ' + _ofertaActiva.nombre;
    actualizarSlider(slider.value);

    document.getElementById('sliderSection').style.display = 'block';
    document.getElementById('amortSection').style.display  = 'none';
    document.getElementById('btnVerAmort').style.display   = 'none'; // ya no se usa

    document.getElementById('sliderSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// ══════════════════════════════════════════════════════
//  ACTUALIZAR SLIDER
// ══════════════════════════════════════════════════════
window.actualizarSlider = function(semanas) {
    _semanasActual = parseInt(semanas);

    var esLiquidacionRayo = _ofertaActiva && _ofertaActiva.id_producto === 4;

    if (esLiquidacionRayo) {
        document.getElementById('semanasValor').textContent = _semanasActual === 1
            ? '1 pago único'
            : '2 quincenas';
    } else {
        document.getElementById('semanasValor').textContent = _semanasActual;
    }

    if (_ofertaActiva) {
        var total   = parseFloat(_ofertaActiva.total_a_pagar);
        var divisor = esLiquidacionRayo && _semanasActual === 4 ? 2 : _semanasActual;
        var semanal = total / divisor;
        document.getElementById('pagoSemanalCalc').textContent =
            '$' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) +
            (esLiquidacionRayo && _semanasActual === 4 ? ' / quincena' : ' / semana');
    }

    verTablaAmortizacion();
};


window.seleccionarRayo = function(opcion, ev) {
    document.querySelectorAll('.rayo-btn').forEach(function(b) {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    ev.target.classList.remove('btn-outline-primary');
    ev.target.classList.add('btn-primary');

    _semanasActual = opcion;
    var modos = { 1: 'unico', 2: 'quincenas', 4: 'semanal' };
    _rayoModo = modos[opcion];

    var labels = { 1: '1 pago único', 2: '2 quincenas', 4: '4 semanas' };
    document.getElementById('semanasValor').textContent = labels[opcion];

    var total   = parseFloat(_ofertaActiva.total_a_pagar);
    var divisor = opcion === 2 ? 2 : opcion;
    var monto   = total / divisor;
    document.getElementById('pagoSemanalCalc').textContent =
        '$' + monto.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) +
        (opcion === 2 ? ' / quincena' : opcion === 1 ? ' / pago' : ' / semana');

    verTablaAmortizacion();
};

// ══════════════════════════════════════════════════════
//  HELPER — RANGO DE FECHAS
// ══════════════════════════════════════════════════════
function fmtFechaRango(fechaISO) {
    var inicio = new Date(fechaISO + 'T00:00:00');
    var fin    = new Date(fechaISO + 'T00:00:00');
    fin.setDate(fin.getDate() + 7);

    var fmt = function(d) {
        return String(d.getDate()).padStart(2, '0') + '/' +
               String(d.getMonth() + 1).padStart(2, '0') + '/' +
               d.getFullYear();
    };

    return '<span style="display:block;font-weight:600;">' + fmt(inicio) + '</span>' +
           '<span style="display:block;color:#888;font-size:0.82em;">' + fmt(fin) + '</span>';
}

function calcularFechasQuincenales(fechaAcuerdoISO) {
    var hoy    = new Date(fechaAcuerdoISO + 'T00:00:00');
    var dia    = hoy.getDate();
    var mes    = hoy.getMonth();
    var anio   = hoy.getFullYear();

    var ultimoDiaMes = function(m, a) {
        return new Date(a, m + 1, 0).getDate();
    };

    var f1, f2;

    if (dia < 15) {
        // Primer pago el 15 del mismo mes
        f1 = new Date(anio, mes, 15);
        f2 = new Date(anio, mes, ultimoDiaMes(mes, anio));
    } else {
        // Primer pago el último día del mes actual
        f1 = new Date(anio, mes, ultimoDiaMes(mes, anio));
        // Segundo pago el 15 del mes siguiente
        var mesSig  = mes + 1 > 11 ? 0 : mes + 1;
        var anioSig = mes + 1 > 11 ? anio + 1 : anio;
        f2 = new Date(anioSig, mesSig, 15);
    }

    var fmt = function(d) {
        return d.getFullYear() + '-' +
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0');
    };

    return [fmt(f1), fmt(f2)];
}

function fmtPagoRealizado(pago) {
    if (!pago) return '<span style="color:#aaa;">—</span>';

    var fmt = function(v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };
    var fmtFecha = function(s) {
        if (!s) return '';
        var p = s.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };

    var lineas = '';

    if (pago.tipo === 'sobrante') {
        lineas =
            '<span style="display:block;font-size:0.78em;color:#f59e0b;">Sobrante: ' + fmt(pago.monto) + '</span>' +
            '<span style="display:block;font-size:0.78em;">Aplicado: ' + fmt(pago.aplicado) + '</span>' +
            '<span style="display:block;font-size:0.75em;color:#888;">' + fmtFecha(pago.fecha) + '</span>';
    } else {
        lineas =
            '<span style="display:block;font-weight:600;">' + fmt(pago.monto) + '</span>' +
            '<span style="display:block;font-size:0.78em;color:#888;">Aplicado: ' + fmt(pago.aplicado) + '</span>' +
            '<span style="display:block;font-size:0.75em;color:#888;">' + fmtFecha(pago.fecha) + '</span>';
    }

    return lineas;
}

// ══════════════════════════════════════════════════════
//  VER TABLA DE AMORTIZACIÓN (calculada en frontend)
// ══════════════════════════════════════════════════════
window.verTablaAmortizacion = function() {
    if (!_ofertaActiva || !_credito) return;

    var o   = _ofertaActiva;
    var total = parseFloat(o.total_a_pagar);
    var fmt = function(v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };
    var hoy = new Date().toISOString().split('T')[0];

    // ← PRIMERO declarar esto
    var esLiquidacionRayo = o.id_producto === 4;

    // ← DESPUÉS usarlo
    var labelPago = (esLiquidacionRayo && _rayoModo === 'quincenas') ? 'Por Quincena' : 'Pago Semanal';
    var montoPago = (esLiquidacionRayo && _rayoModo === 'quincenas')
        ? parseFloat((total / 2).toFixed(2))
        : parseFloat((total / _semanasActual).toFixed(2));

    document.getElementById('thSemanalCapital').textContent =
        (esLiquidacionRayo && _rayoModo === 'quincenas') ? 'Monto Quincena' : 'Semanal / Capital';

    document.getElementById('amortTitulo').textContent = '📋 ' + o.nombre;
    document.getElementById('amortResumenCards').innerHTML =
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Deuda Original</div><div class="fw-bold text-primary fs-5">' + fmt(o.monto_base) + '</div></div></div>' +
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Descuento (' + parseInt(o.porcentaje_descuento) + '%)</div><div class="fw-bold text-danger fs-5">-' + fmt(o.descuento_monto) + '</div></div></div>' +
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Total a Pagar</div><div class="fw-bold text-success fs-5">' + fmt(total) + '</div></div></div>' +
    '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">' + labelPago + '</div><div class="fw-bold text-warning fs-5">' + fmt(montoPago) + '</div></div></div>';

    var filasHtml = '';

    if (esLiquidacionRayo && _rayoModo === 'unico') {
        // ── Pago único ──────────────────────────────
        filasHtml =
            '<tr>' +
                '<td><strong>Pago único</strong></td>' +
                '<td>' + fmtFechaRango(hoy) + '</td>' +
                '<td><span style="font-weight:600;">' + fmt(total) + '</span></td>' +
                '<td>' + fmt(total) + '</td>' +
                '<td>' + fmt(0) + '</td>' +
                '<td>' + fmtPagoRealizado(null) + '</td>' +
                '<td><span class="badge bg-warning text-dark">Pendiente</span></td>' +
            '</tr>';

    } else if (esLiquidacionRayo && _rayoModo === 'quincenas') {
        // ── 2 quincenas ─────────────────────────────
        var fechas  = calcularFechasQuincenales(hoy);
        var mitad1  = parseFloat((total / 2).toFixed(2));
        var mitad2  = parseFloat((total - mitad1).toFixed(2)); // absorbe el centavo si hay

        filasHtml +=
            '<tr>' +
                '<td><strong>Quincena 1</strong></td>' +
                '<td><span style="font-weight:600;">' + fmtFecha(fechas[0]) + '</span></td>' +
                '<td><span style="font-weight:600;">' + fmt(mitad1) + '</span></td>' +
                '<td>' + fmt(mitad1) + '</td>' +
                '<td>' + fmt(mitad2) + '</td>' +
                '<td>' + fmtPagoRealizado(null) + '</td>' +
                '<td><span class="badge bg-warning text-dark">Pendiente</span></td>' +
            '</tr>' +
            '<tr>' +
                '<td><strong>Quincena 2</strong></td>' +
                '<td><span style="font-weight:600;">' + fmtFecha(fechas[1]) + '</span></td>' +
                '<td><span style="font-weight:600;">' + fmt(mitad2) + '</span></td>' +
                '<td>' + fmt(mitad2) + '</td>' +
                '<td>' + fmt(0) + '</td>' +
                '<td>' + fmtPagoRealizado(null) + '</td>' +
                '<td><span class="badge bg-warning text-dark">Pendiente</span></td>' +
            '</tr>';

    } else if (esLiquidacionRayo && _rayoModo === 'semanal') {
    // ── 4 semanas semanales ──────────────────────
    var añadirDias = function(fecha, dias) {
        var d = new Date(fecha + 'T00:00:00');
        d.setDate(d.getDate() + dias);
        return d.toISOString().split('T')[0];
    };

    var saldo   = total;
    var semanal = parseFloat((total / 4).toFixed(2));

    for (var s = 1; s <= 4; s++) {
        var fechaPago = añadirDias(hoy, (s - 1) * 7 + 8);
        var capital   = (s < 4) ? semanal : parseFloat(saldo.toFixed(2));
        saldo         = parseFloat((saldo - capital).toFixed(2));
        if (saldo < 0) saldo = 0;

        filasHtml +=
            '<tr>' +
                '<td><strong>Semana ' + s + '</strong></td>' +
                '<td>' + fmtFechaRango(fechaPago) + '</td>' +
                '<td><span style="font-weight:600;">' + fmt(semanal) + '</span></td>' +
                '<td>' + fmt(capital) + '</td>' +
                '<td>' + fmt(saldo) + '</td>' +
                '<td>' + fmtPagoRealizado(null) + '</td>' +
                '<td><span class="badge bg-warning text-dark">Pendiente</span></td>' +
            '</tr>';
    }

    } else {
        // ── Resto de productos — semanas normales ────
        var añadirDias = function(fecha, dias) {
            var d = new Date(fecha + 'T00:00:00');
            d.setDate(d.getDate() + dias);
            return d.toISOString().split('T')[0];
        };

        var saldo = total;
        var semanal = parseFloat((total / _semanasActual).toFixed(2));

        for (var s = 1; s <= _semanasActual; s++) {
            var fechaPago = añadirDias(hoy, (s - 1) * 7 + 8);
            var capital   = (s < _semanasActual) ? semanal : parseFloat(saldo.toFixed(2));
            saldo         = parseFloat((saldo - capital).toFixed(2));
            if (saldo < 0) saldo = 0;

            filasHtml +=
                '<tr>' +
                    '<td>Semana ' + s + '</td>' +
                    '<td>' + fmtFechaRango(fechaPago) + '</td>' +
                    '<td>' +
                        '<span style="display:block;font-weight:600;">' + fmt(semanal) + '</span>' +
                        '<span style="display:block;font-size:0.82em;color:#888;">' + fmt(capital) + '</span>' +
                    '</td>' +
                    '<td>' + fmt(saldo) + '</td>' +
                    '<td>' + fmtPagoRealizado(null) + '</td>' +
                    '<td><span class="badge bg-warning text-dark">Pendiente</span></td>' +
                '</tr>';
        }
    }

    document.getElementById('tablaAmortBody').innerHTML = filasHtml;
    document.getElementById('amortSection').style.display = 'block';
    document.getElementById('amortSection').scrollIntoView({ behavior: 'smooth' });
};

// ══════════════════════════════════════════════════════
//  GUARDAR CONVENIO
// ══════════════════════════════════════════════════════
window.guardarConvenio = function() {
    if (!_ofertaActiva || !_credito) return;

    // Calcular ANTES del Swal
    var hoy     = new Date().toISOString().split('T')[0];
    var total   = parseFloat(_ofertaActiva.total_a_pagar);
    var semanal = parseFloat((total / _semanasActual).toFixed(2));

    Swal.fire({
        title: '¿Confirmar convenio?',
        html: '<strong>' + _ofertaActiva.nombre + '</strong> — ' +
              _semanasActual + ' semanas — $' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ' / semana<br><br>' +
              '<span class="text-muted" style="font-size:.9rem;">Cliente: <strong>' + _credito.Nombre_cliente + '</strong></span><br>' +
              '<span class="text-muted" style="font-size:.9rem;">Total a pagar: <strong>$' + total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</strong></span>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#764ba2',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        http.request({
            endpoint: '/convenios/guardarConvenio',
            method: 'POST',
            data: {
                id_credito:                   _credito.Id_credito,
                id_producto_convenio:         _ofertaActiva.id_producto,
                id_producto_convenio_detalle: _ofertaActiva.id_detalle,
                nombre_cliente:               _credito.Nombre_cliente,
                bucket_morosidad_real:        _credito.Bucket_Morosidad_Real,
                dias_mora:                    _credito.Dias_mora,
                avance_pago_plazo:            _credito.Avance_Pago_Plazo,
                adeudo_total_original:        _credito.Adeudo_total,
                porcentaje_descuento:         _ofertaActiva.porcentaje_descuento,
                descuento_monto:              _ofertaActiva.descuento_monto,
                total_a_pagar:                total,
                pago_inicial_monto:           _ofertaActiva.pago_inicial_monto || '',
                numero_semanas:               _semanasActual,
                pago_semanal:                 semanal,
                fecha_acuerdo:                hoy,
            },
            onSuccess: function(resp) {
                if (resp.success) {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: 'El convenio fue registrado exitosamente.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload(); // AUTO-REFRESH
                    });
                } else {
                    Swal.fire('Error', resp.mensaje || 'No se pudo guardar.', 'error');
                }
            },
            onError: function() {
                Swal.fire('Error', 'Error de conexión.', 'error');
            }
        });
    });
};

// ══════════════════════════════════════════════════════
//  CANCELAR CONVENIO
// ══════════════════════════════════════════════════════
window.cancelarConvenio = function() {
    if (!window._idConvenioActivo) return;

    Swal.fire({
        title: '¿Cancelar convenio?',
        html: 'Esta acción <strong>no se puede deshacer</strong>.<br>' +
              'Las semanas pendientes quedarán marcadas como canceladas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, volver',
        confirmButtonColor: '#ef4444',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Cancelando...',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        http.request({
            endpoint: '/convenios/cancelarConvenio',
            method: 'POST',
            data: { id_convenio: window._idConvenioActivo },
            onSuccess: function(resp) {
                Swal.close();
                if (!resp.success) {
                    Swal.fire('Error', resp.mensaje || 'No se pudo cancelar.', 'error');
                    return;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Convenio cancelado',
                    text: 'El convenio fue cancelado correctamente.',
                    confirmButtonColor: '#764ba2',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    location.reload(); // AUTO-REFRESH
                });
            },
            onError: function() {
                Swal.close();
                Swal.fire('Error', 'Error de conexión.', 'error');
            }
        });
    });
};

// ══════════════════════════════════════════════════════
//  REPINTAR TABLA TRAS CANCELACIÓN
// ══════════════════════════════════════════════════════
function _repintarTablaCancelada(semanaCancelacion) {
    var filas = document.querySelectorAll('#tablaAmortBody tr');
    filas.forEach(function(fila, idx) {
        var numSemana    = idx + 1;
        var celdaEstatus = fila.querySelector('td:last-child');

        if (numSemana === semanaCancelacion) {
            fila.style.background = 'rgba(245,158,11,0.08)';
            celdaEstatus.innerHTML =
                '<span class="badge bg-warning text-dark">' +
                '<i class="fa-solid fa-ban me-1"></i>Cancelado aquí</span>';
        } else if (numSemana > semanaCancelacion) {
            fila.style.opacity         = '0.4';
            fila.style.textDecoration  = 'line-through';
            fila.style.color           = '#ef4444';
            celdaEstatus.style.textDecoration = 'none';
            celdaEstatus.style.color          = '';
            celdaEstatus.innerHTML =
                '<span class="badge bg-danger">No pagado</span>';
        }
    });
}

// ══════════════════════════════════════════════════════
//  DESCARGAR PDF
// ══════════════════════════════════════════════════════
window.descargarPdf = function() {
    if (!_credito) return;

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/convenios/descargarPdf';
    form.style.display = 'none';

    var inp = document.createElement('input');
    inp.name  = 'id_credito';
    inp.value = _credito.Id_credito;
    form.appendChild(inp);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

// ══════════════════════════════════════════════════════
//  HISTORIAL DE CONVENIOS
// ══════════════════════════════════════════════════════
window.abrirHistorial = function() {
    if (!_credito) return;

    document.getElementById('historialLoading').style.display    = 'block';
    document.getElementById('historialVacio').style.display      = 'none';
    document.getElementById('historialTablaWrap').style.display  = 'none';
    document.getElementById('modalHistorialCredito').textContent = 'Crédito #' + _credito.Id_credito;

    var modal = new bootstrap.Modal(document.getElementById('modalHistorial'));
    modal.show();

    http.request({
        endpoint: '/convenios/getHistorialConvenios',
        method: 'POST',
        data: { id_credito: _credito.Id_credito },
        onSuccess: function(resp) {
            document.getElementById('historialLoading').style.display = 'none';

            if (!resp.success || !resp.datos || resp.datos.length === 0) {
                document.getElementById('historialVacio').style.display = 'block';
                return;
            }

            var fmt = function(v) {
                return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            };
            var fmtFecha = function(s) {
                if (!s) return '—';
                var p = s.split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            };
            var badgeEstatus = function(e) {
                var labels = {
                    'activo':         'Activo',
                    'cancelado':      'Cancelado',
                    'completado':     'Completado',
                    'incumplimiento': 'Incumplimiento'
                };
                return '<span class="badge-estatus-' + e + '">' + (labels[e] || e) + '</span>';
            };
            var badgeEstatusPago = function(e) {
                var map = {
                    'pendiente':  ['#6c757d', 'Pendiente'],
                    'pagado':     ['#22c55e', 'Pagado'],
                    'vencido':    ['#ef4444', 'Vencido'],
                    'cancelado':  ['#9ca3af', 'Cancelado'],
                };
                var v = map[e] || ['#6c757d', e];
                return '<span style="background:' + v[0] + ';color:#fff;padding:2px 8px;border-radius:12px;font-size:.72rem;">' + v[1] + '</span>';
            };

            var filas = '';
            resp.datos.forEach(function(c, i) {
                var idConvenio = c.id;
                filas +=
                    '<tr class="fila-historial" onclick="toggleAmortizacion(' + idConvenio + ', this)">' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + fmtFecha(c.fecha_acuerdo) + '</td>' +
                        '<td>' + c.nombre_producto + '</td>' +
                        '<td>' + fmt(c.total_a_pagar) + '</td>' +
                        '<td>' + c.numero_semanas + ' sem</td>' +
                        '<td>' + badgeEstatus(c.estatus) + '</td>' +
                        '<td>' + (c.usuario_alta || '—') + '</td>' +
                        '<td>' + (c.usuario_cancela || '—') + '</td>' +
                        '<td>' + fmtFecha(c.fecha_cancelacion) + '</td>' +
                        '<td class="d-flex align-items-center gap-2">' +
    (c.pdf_adjunto
        ? '<a href="' + c.pdf_adjunto + '" target="_blank" ' +
          'class="btn btn-outline-danger btn-sm py-0 px-2" ' +
          'title="Ver PDF adjunto" onclick="event.stopPropagation()">' +
          '<i class="fa-solid fa-file-pdf"></i>' +
          '</a>'
        : '') +
    '<i class="fas fa-chevron-down" id="chevron-' + idConvenio + '"></i>' +
'</td>' +
                    '</tr>' +
                    '<tr class="fila-detalle-amort d-none" id="detalle-' + idConvenio + '">' +
                        '<td colspan="10">' +
                            '<div class="amort-accordion" id="amort-content-' + idConvenio + '">' +
                                '<div class="text-center text-muted py-3">' +
                                    '<i class="fas fa-spinner fa-spin me-2"></i>Cargando amortización...' +
                                '</div>' +
                            '</div>' +
                        '</td>' +
                    '</tr>';
            });

            document.getElementById('tablaHistorialBody').innerHTML    = filas;
            document.getElementById('historialTablaWrap').style.display = 'block';
        },
        onError: function() {
            document.getElementById('historialLoading').style.display = 'none';
            document.getElementById('historialVacio').style.display   = 'block';
        }
    });
};

// ────────────────────────────────────────────────
// ACCORDION — toggle amortización por convenio
// ────────────────────────────────────────────────

var _amortCache = {};

function toggleAmortizacion(idConvenio, fila) {
    var detalle  = document.getElementById('detalle-' + idConvenio);
    var chevron  = document.getElementById('chevron-' + idConvenio);
    var content  = document.getElementById('amort-content-' + idConvenio);
    var abierto  = !detalle.classList.contains('d-none');

    if (abierto) {
        detalle.classList.add('d-none');
        chevron.style.transform = 'rotate(0deg)';
        return;
    }

    detalle.classList.remove('d-none');
    chevron.style.transform = 'rotate(180deg)';

    // Si ya está en caché, no volver a pedir
    if (_amortCache[idConvenio]) {
        content.innerHTML = _amortCache[idConvenio];
        return;
    }

    // Pedir amortización al backend
    http.request({
        endpoint: '/convenios/getAmortizacionConvenio',
        method:   'POST',
        data:     { id_convenio: idConvenio },
        onSuccess: function(resp) {
            if (!resp.success || !resp.datos) {
                content.innerHTML = '<p class="text-danger p-3">Error al cargar amortización.</p>';
                return;
            }

            var fmt = function(v) {
                return '$' + parseFloat(v || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            };
            var fmtFecha = function(s) {
                if (!s) return '—';
                var p = s.split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            };
            var badgePago = function(e) {
                var map = {
                    'pendiente': ['#6c757d', 'Pendiente'],
                    'pagado':    ['#22c55e', 'Pagado'],
                    'vencido':   ['#ef4444', 'Vencido'],
                    'cancelado': ['#9ca3af', 'Cancelado'],
                };
                var v = map[e] || ['#6c757d', e];
                return '<span style="background:' + v[0] + ';color:#fff;padding:2px 8px;border-radius:12px;font-size:.72rem;">' + v[1] + '</span>';
            };

            var filas = resp.datos.map(function(row) {
                return '<tr>' +
                    '<td>Semana ' + row.numero_semana + '</td>' +
                    '<td>' + fmtFecha(row.fecha_pago) + '</td>' +
                    '<td>' + fmt(row.pago_semanal) + '</td>' +
                    '<td>' + fmt(row.capital) + '</td>' +
                    '<td>' + fmt(row.saldo_restante) + '</td>' +
                    '<td>' + (row.fecha_pago_real ? fmtFecha(row.fecha_pago_real) : '—') + '</td>' +
                    '<td>' + (row.monto_pagado ? fmt(row.monto_pagado) : '—') + '</td>' +
                    '<td>' + badgePago(row.estatus_pago) + '</td>' +
                '</tr>';
            }).join('');

            var html =
                '<div class="d-flex justify-content-between align-items-center mb-2">' +
                    '<span class="fw-bold text-muted" style="font-size:.85rem;">Tabla de Amortización</span>' +
                    '<button class="btn btn-outline-primary btn-sm" onclick="descargarPdfConvenio(' + idConvenio + ')">' +
                        '<i class="fas fa-file-pdf me-1"></i>Descargar tabla de amortización' +
                    '</button>' +
                '</div>' +
                '<div class="table-responsive">' +
                    '<table class="tabla-amort table table-borderless mb-0" style="font-size:.82rem;">' +
                        '<thead><tr>' +
                            '<th>Semana</th><th>Fecha Pago</th><th>Semanal</th>' +
                            '<th>Capital</th><th>Saldo</th>' +
                            '<th>Pago Real</th><th>Monto Pagado</th><th>Estatus</th>' +
                        '</tr></thead>' +
                        '<tbody>' + filas + '</tbody>' +
                    '</table>' +
                '</div>';

            _amortCache[idConvenio] = html;
            content.innerHTML = html;
        },
        onError: function() {
            content.innerHTML = '<p class="text-danger p-3">Error de conexión.</p>';
        }
    });
}

function descargarPdfConvenio(idConvenio) {
    // Reutiliza el endpoint existente de PDF
    // Necesita id_credito — lo tomamos del crédito activo
    if (!_credito) return;

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/convenios/descargarPdf';
    form.style.display = 'none';

    var inp = document.createElement('input');
    inp.name = 'id_credito';
    inp.value = _credito.Id_credito;
    form.appendChild(inp);

    var inpConv = document.createElement('input');
    inpConv.name = 'id_convenio';
    inpConv.value = idConvenio;
    form.appendChild(inpConv);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// ════════════════════════════════════════════════
// MODAL MIGRACIÓN
// ════════════════════════════════════════════════

var _migCredito = null;
var _migDetalle = null;

window.abrirModalMigracion = function() {
    // Reset
    _migCredito = null;
    _migDetalle = null;
    document.getElementById('migIdCredito').value   = '';
    document.getElementById('migInfoCliente').classList.add('d-none');
    document.getElementById('migStep2').classList.add('d-none');
    document.getElementById('migPreview').classList.add('d-none');
    document.getElementById('migBtnGuardar').classList.add('d-none');

    var modal = new bootstrap.Modal(document.getElementById('modalMigracion'));
    modal.show();

    // Cargar productos en el select
    migCargarProductos();
};

function migCargarProductos() {
    http.request({
        endpoint: '/convenios/getProductosConvenio',
        method:   'POST',
        data:     {},
        onSuccess: function(resp) {
            console.log('productos resp:', resp);
            if (!resp.success) return;
            var sel = document.getElementById('migProducto');
            sel.innerHTML = '<option value="">Selecciona...</option>';
            resp.datos.forEach(function(p) {
                p.detalles.forEach(function(d) {
                    sel.innerHTML += '<option value="' + d.id + '"'
                        + ' data-id-producto="' + p.id + '"'
                        + ' data-porcentaje="'  + d.porcentaje_descuento + '"'
                        + ' data-variable="'    + d.porcentaje_variable  + '">'
                        + p.nombre + ' — ' + d.porcentaje_descuento + '%'
                        + (d.porcentaje_variable ? ' (variable)' : '')
                        + '</option>';
                });
            });
        },
        onError: function() {}
    });
}

window.migBuscarCredito = function() {
    var id = document.getElementById('migIdCredito').value.trim();
    if (!id) return;

    // Reset estado visual
    var info = document.getElementById('migInfoCliente');
    info.classList.remove('d-none', 'alert-info', 'alert-success', 'alert-danger');
    info.classList.add('d-none');
    document.getElementById('migStep2').classList.add('d-none');
    document.getElementById('migPreview').classList.add('d-none');
    document.getElementById('migBtnGuardar').classList.add('d-none');

    // Mostrar cargando
    info.classList.remove('d-none');
    info.className = 'alert alert-secondary';
    info.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Consultando crédito...';

    http.request({
        endpoint: '/convenios/getOfertasCredito',
        method:   'POST',
        data:     { id_credito: id },
        onSuccess: function(resp) {
            if (!resp.success || !resp.datos || !resp.datos.credito) {
                info.className = 'alert alert-danger';
                info.innerHTML =
                    '<i class="fas fa-times-circle me-2"></i>' +
                    '<strong>Crédito no encontrado.</strong> ' +
                    (resp.mensaje || 'Verifica el ID e intenta de nuevo.');
                return;
            }

            var credito = resp.datos.credito;
            _migCredito = credito;

            // Ahora verificar si ya tiene convenio activo
            http.request({
                endpoint: '/convenios/getConvenioActivo',
                method:   'POST',
                data:     { id_credito: id },
                onSuccess: function(respConv) {
                    var tieneActivo = respConv.success && respConv.datos && respConv.datos.estatus === 'activo';

                    if (tieneActivo) {
                        var conv = respConv.datos;
                        var fmt  = function(v) {
                            return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
                        };

                        info.className = 'alert alert-danger';
                        info.innerHTML =
                            '<div class="d-flex align-items-start gap-2">' +
                                '<i class="fas fa-ban fa-lg mt-1 text-danger"></i>' +
                                '<div>' +
                                    '<strong>Este crédito ya tiene un convenio activo.</strong><br>' +
                                    '<small>' +
                                        '<span class="me-3"><i class="fas fa-user me-1"></i>' + credito.Nombre_cliente + '</span>' +
                                        '<span class="me-3"><i class="fas fa-calendar me-1"></i>Acuerdo: ' + conv.fecha_acuerdo + '</span>' +
                                        '<span><i class="fas fa-dollar-sign me-1"></i>Total: ' + fmt(conv.total_a_pagar) + '</span>' +
                                    '</small><br>' +
                                    '<small class="text-muted">Dale su seguimiento correspondiente ingresando el id credito en el buscador principal.</small>' +
                                '</div>' +
                            '</div>';

                        // No mostrar paso 2
                        document.getElementById('migStep2').classList.add('d-none');
                        document.getElementById('migBtnGuardar').classList.add('d-none');
                        return;
                    }

                    // ✅ Sin convenio activo — mostrar info verde y habilitar paso 2
                    var adeudo = parseFloat(credito.Adeudo_total || 0)
                        .toLocaleString('es-MX', { minimumFractionDigits: 2 });

                    info.className = 'alert alert-success';
                    info.innerHTML =
                        '<div class="d-flex align-items-start gap-2">' +
                            '<i class="fas fa-check-circle fa-lg mt-1 text-success"></i>' +
                            '<div>' +
                                '<strong>' + credito.Nombre_cliente + '</strong> — Crédito #' + credito.Id_credito + '<br>' +
                                '<small>' +
                                    '<span class="me-3"><i class="fas fa-exclamation-circle me-1"></i>Bucket: ' + (credito.Bucket_Morosidad_Real || '—') + '</span>' +
                                    '<span><i class="fas fa-dollar-sign me-1"></i>Adeudo S2Movil: $' + adeudo + '</span>' +
                                '</small><br>' +
                                '<small class="text-success fw-semibold">✓ Cumple condiciones para registrar un convenio.</small>' +
                            '</div>' +
                        '</div>';

                    document.getElementById('migAdeudo').value =
                        parseFloat(credito.Adeudo_total || 0).toFixed(2);
                    document.getElementById('migBucket').value =
                        credito.Bucket_Morosidad_Real || '';
                    document.getElementById('migStep2').classList.remove('d-none');
                },
                onError: function() {
                    // Si falla getConvenioActivo, continuamos igual (no bloqueamos)
                    info.className = 'alert alert-success';
                    info.innerHTML =
                        '<i class="fas fa-check-circle me-2"></i>' +
                        '<strong>' + credito.Nombre_cliente + '</strong> — Crédito #' + credito.Id_credito;

                    document.getElementById('migAdeudo').value =
                        parseFloat(credito.Adeudo_total || 0).toFixed(2);
                    document.getElementById('migBucket').value =
                        credito.Bucket_Morosidad_Real || '';
                    document.getElementById('migStep2').classList.remove('d-none');
                }
            });
        },
        onError: function() {
            info.className = 'alert alert-danger';
            info.innerHTML =
                '<i class="fas fa-times-circle me-2"></i>' +
                'Error de conexión. Intenta de nuevo.';
        }
    });
};

window.migProductoChange = function() {
    var sel     = document.getElementById('migProducto');
    var opt     = sel.options[sel.selectedIndex];
    var variable = opt.getAttribute('data-variable');
    var pct      = opt.getAttribute('data-porcentaje');

    var inputPct = document.getElementById('migPorcentaje');
    inputPct.value    = pct || '';
    inputPct.readOnly = variable !== '1';

    _migDetalle = {
        id_detalle:  sel.value,
        id_producto: opt.getAttribute('data-id-producto'),
    };

    migCalcular();
};

window.migCalcular = function() {
    var adeudo  = parseFloat(document.getElementById('migAdeudo').value)      || 0;
    var pct     = parseFloat(document.getElementById('migPorcentaje').value)   || 0;
    var semanal = parseFloat(document.getElementById('migPagoSemanal').value)  || 0;
    var fecha   = document.getElementById('migFechaInicio').value;

    // Limpiar error previo
    var errorExistente = document.getElementById('migErrorSemanal');
    if (errorExistente) errorExistente.remove();

    var mostrarError = function(campo, mensaje) {
        document.getElementById('migPreview').classList.add('d-none');
        document.getElementById('migBtnGuardar').classList.add('d-none');

        var inputCampo = document.getElementById(campo);
        if (!inputCampo) return;

        // Resaltar campo en rojo
        inputCampo.classList.add('is-invalid');

        // Insertar mensaje debajo del campo
        var div = document.createElement('div');
        div.id = 'migErrorSemanal';
        div.style.cssText = 'color:#dc2626;font-size:0.78rem;margin-top:4px;display:flex;align-items:center;gap:5px;';
        div.innerHTML = '<i class="fas fa-triangle-exclamation"></i> ' + mensaje;
        inputCampo.closest('.col-md-4')
            ? inputCampo.closest('.col-md-4').appendChild(div)
            : inputCampo.parentNode.appendChild(div);
    };

    var limpiarError = function() {
        document.getElementById('migPagoSemanal').classList.remove('is-invalid');
        document.getElementById('migAdeudo').classList.remove('is-invalid');
        document.getElementById('migPorcentaje').classList.remove('is-invalid');
        var e = document.getElementById('migErrorSemanal');
        if (e) e.remove();
    };

    if (!adeudo || !semanal || !fecha) {
        document.getElementById('migPreview').classList.add('d-none');
        document.getElementById('migBtnGuardar').classList.add('d-none');
        return;
    }

    // ── Validación 1: pago semanal debe ser positivo
    if (semanal <= 0) {
        mostrarError('migPagoSemanal', 'El pago semanal debe ser un valor positivo mayor a $0.');
        return;
    }

    // ── Validación 2: pago semanal no puede ser mayor al total a pagar
    var descuento = Math.round(adeudo * (pct / 100) * 100) / 100;
    var total     = Math.round((adeudo - descuento) * 100) / 100;

    if (semanal > total) {
        mostrarError('migPagoSemanal',
            'El pago semanal ($' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) +
            ') no puede ser mayor al total a pagar ($' +
            total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ').');
        return;
    }

    // ── Validación 3: semanas resultantes deben ser razonables (1-52)
    var semanasEnt = Math.floor(total / semanal);
    var residuo    = Math.round((total - semanasEnt * semanal) * 100) / 100;
    var semanas    = residuo > 0 ? semanasEnt + 1 : semanasEnt;

    if (semanas < 1 || semanas > 52) {
        mostrarError('migPagoSemanal',
            'El pago semanal genera ' + semanas + ' semanas, lo cual es inválido. ' +
            'Ajusta el monto para obtener entre 1 y 52 semanas.');
        return;
    }

    // ── Validación 4: adeudo base debe ser positivo
    if (adeudo <= 0) {
        mostrarError('migAdeudo', 'El adeudo base debe ser mayor a $0.');
        return;
    }

    // ── Validación 5: porcentaje entre 0 y 100
    if (pct < 0 || pct > 100) {
        mostrarError('migPorcentaje', 'El porcentaje de descuento debe estar entre 0% y 100%.');
        return;
    }

    // Todo válido — limpiar errores y mostrar preview
    limpiarError();

    var fmt = function(v) {
        return '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    document.getElementById('migResumenCards').innerHTML =
        '<div class="col-6 col-md-3"><div class="border rounded p-2">'
        + '<div class="small text-muted">Adeudo Base</div>'
        + '<div class="fw-bold text-primary">' + fmt(adeudo) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2">'
        + '<div class="small text-muted">Descuento (' + pct + '%)</div>'
        + '<div class="fw-bold text-danger">-' + fmt(descuento) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2">'
        + '<div class="small text-muted">Total a Pagar</div>'
        + '<div class="fw-bold text-success">' + fmt(total) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2">'
        + '<div class="small text-muted">Semanas</div>'
        + '<div class="fw-bold text-warning">' + semanas + ' sem</div></div></div>';

    document.getElementById('migPreview').classList.remove('d-none');
    document.getElementById('migBtnGuardar').classList.remove('d-none');
};

window.validarPdfAdjunto = function(input) {
    var preview = document.getElementById('migPdfPreview');
    if (!input.files || !input.files[0]) {
        preview.innerHTML = '';
        return;
    }

    var file = input.files[0];
    if (file.type !== 'application/pdf') {
        Swal.fire('Formato incorrecto', 'Solo se permiten archivos PDF', 'warning');
        input.value = '';
        preview.innerHTML = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) { // 5MB
        Swal.fire('Archivo muy grande', 'El PDF no debe exceder 5MB', 'warning');
        input.value = '';
        preview.innerHTML = '';
        return;
    }

    preview.innerHTML = '<i class="fas fa-file-pdf" style="font-size:2rem;color:#dc2626;"></i>';
};



// Modificar migGuardar para incluir el PDF
window.migGuardar = function() {
    if (!_migCredito || !_migDetalle) {
        Swal.fire('Error', 'Completa todos los campos.', 'warning');
        return;
    }

    // Guardia numérica antes de enviar al backend
var _semanal = parseFloat(document.getElementById('migPagoSemanal').value) || 0;
var _adeudo  = parseFloat(document.getElementById('migAdeudo').value) || 0;
var _pct     = parseFloat(document.getElementById('migPorcentaje').value) || 0;
var _total   = Math.round((_adeudo - Math.round(_adeudo * (_pct / 100) * 100) / 100) * 100) / 100;
var _semanas = _semanal > 0 ? Math.ceil(_total / _semanal) : -1;

if (_semanal <= 0 || _semanas < 1 || _semanas > 52) {
    Swal.fire({
        icon: 'warning',
        title: 'Pago semanal inválido',
        html: 'El valor <strong>$' + _semanal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</strong> ' +
              'generaría <strong>' + _semanas + ' semanas</strong>, lo cual no es válido.<br><br>' +
              '<small class="text-muted">Ingresa un monto semanal positivo que resulte en 1 a 52 semanas.</small>',
        confirmButtonColor: '#764ba2',
    });
    return;
}

    var adeudo   = document.getElementById('migAdeudo').value;
    var pct      = document.getElementById('migPorcentaje').value;
    var semanal  = document.getElementById('migPagoSemanal').value;
    var fecha    = document.getElementById('migFechaInicio').value;
    var bucket   = document.getElementById('migBucket').value;
    var pdfFile  = document.getElementById('migPdfAdjunto').files[0];

    if (!adeudo || !pct || !semanal || !fecha) {
        Swal.fire('Campos incompletos', 'Llena todos los campos requeridos.', 'warning');
        return;
    }

    Swal.fire({
        title: '¿Registrar convenio?',
        html: 'Se creará el convenio y se marcarán automáticamente<br>'
            + 'las semanas ya pagadas según S2Movil.'
            + (pdfFile ? '<br><br><small class="text-success">📎 Se adjuntará: ' + pdfFile.name + '</small>' : ''),
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText:  'Cancelar',
        confirmButtonColor: '#22c55e',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        document.getElementById('migBtnGuardar').disabled = true;
        document.getElementById('migBtnGuardar').innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Registrando...';

        // Si hay PDF, necesitamos usar FormData
        if (pdfFile) {
            var formData = new FormData();
            formData.append('id_credito', _migCredito.Id_credito);
            formData.append('nombre_cliente', _migCredito.Nombre_cliente);
            formData.append('id_producto_convenio', _migDetalle.id_producto);
            formData.append('id_producto_convenio_detalle', _migDetalle.id_detalle);
            formData.append('adeudo_base', adeudo);
            formData.append('porcentaje_descuento', pct);
            formData.append('pago_semanal', semanal);
            formData.append('fecha_inicio', fecha);
            formData.append('bucket_morosidad_real', bucket);
            formData.append('dias_mora', _migCredito.Dias_mora || 0);
            formData.append('avance_pago_plazo', _migCredito.Avance_Pago_Plazo || '');
            formData.append('pdf_adjunto', pdfFile);

            // Aquí necesitarías modificar el endpoint para aceptar multipart/form-data
            // Por ahora, simulamos o dejamos pendiente
            console.log('PDF a subir:', pdfFile.name);

            // Llamada con FormData (requiere modificar el endpoint)
            http.request({
                endpoint: '/convenios/migrarConvenio',
                method: 'POST',
                data: formData,
                contentType: false, // Importante para FormData
                processData: false, // Importante para FormData
                onSuccess: function(resp) {
                    // Mismo manejo que abajo
                    document.getElementById('migBtnGuardar').disabled = false;
                    document.getElementById('migBtnGuardar').innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';

                    if (!resp.success) {
                        Swal.fire('Error', resp.mensaje, 'error');
                        return;
                    }

                    var d = resp.datos;
                    bootstrap.Modal.getInstance(document.getElementById('modalMigracion')).hide();

                    Swal.fire({
                        title: '¡Convenio registrado!',
                        html: d.semanas_pagadas + ' de ' + d.semanas_total + ' semanas marcadas como pagadas.<br><small class="text-muted">Recargando...</small>',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                },
                onError: function() {
                    document.getElementById('migBtnGuardar').disabled = false;
                    document.getElementById('migBtnGuardar').innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';
                    Swal.fire('Error', 'Error de conexión.', 'error');
                }
            });
        } else {
            // Continuar con la llamada normal sin PDF
            http.request({
                endpoint: '/convenios/migrarConvenio',
                method:   'POST',
                data: {
                    id_credito:                   _migCredito.Id_credito,
                    nombre_cliente:               _migCredito.Nombre_cliente,
                    id_producto_convenio:         _migDetalle.id_producto,
                    id_producto_convenio_detalle: _migDetalle.id_detalle,
                    adeudo_base:                  adeudo,
                    porcentaje_descuento:         pct,
                    pago_semanal:                  semanal,
                    fecha_inicio:                 fecha,
                    bucket_morosidad_real:        bucket,
                    dias_mora:                     _migCredito.Dias_mora        || 0,
                    avance_pago_plazo:             _migCredito.Avance_Pago_Plazo || '',
                },
                onSuccess: function(resp) {
                    document.getElementById('migBtnGuardar').disabled = false;
                    document.getElementById('migBtnGuardar').innerHTML =
                        '<i class="fas fa-save"></i> Registrar Convenio';

                    if (!resp.success) {
                        Swal.fire('Error', resp.mensaje, 'error');
                        return;
                    }

                    var d = resp.datos;
                    bootstrap.Modal.getInstance(
                        document.getElementById('modalMigracion')
                    ).hide();

                    Swal.fire({
                        title: '¡Convenio registrado!',
                        html: d.semanas_pagadas + ' de ' + d.semanas_total
                            + ' semanas marcadas como pagadas.<br>'
                            + '<small class="text-muted">Recargando...</small>',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload(); // AUTO-REFRESH
                    });
                },
                onError: function() {
                    document.getElementById('migBtnGuardar').disabled = false;
                    document.getElementById('migBtnGuardar').innerHTML =
                        '<i class="fas fa-save"></i> Registrar Convenio';
                    Swal.fire('Error', 'Error de conexión.', 'error');
                }
            });
        }
    });
};

// Asegurar funciones globales
window.seleccionarCredito = seleccionarCredito;
window.renderCreditoBanner = renderCreditoBanner;
window.renderOfertas = renderOfertas;
window.congelarModulo = congelarModulo;
window.fmtFecha = fmtFecha;
window.fmtFechaRango = fmtFechaRango;
window.fmtPagoRealizado = fmtPagoRealizado;
window.toggleAmortizacion = toggleAmortizacion;
window.descargarPdfConvenio = descargarPdfConvenio;
window.migCargarProductos = migCargarProductos;

console.log('✅ Módulo Convenios cargado correctamente');

</script>
