<style>
/* ══════════════════════════════════════════
   CONVENIOS — ESTILOS GLOBALES
══════════════════════════════════════════ */

/* Ocultar flechas del input numérico de pagos libres */
#migLibreNumPagos::-webkit-outer-spin-button,
#migLibreNumPagos::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
#migLibreNumPagos { -moz-appearance: textfield; appearance: textfield; }

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
    padding: 2rem 1.5rem 2.5rem;
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
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

body.dark-mode #concilPagosWrap [style*="background:#f8fafc"] {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode #concilPagosWrap [style*="background:#e2e8f0"] {
    background: #334155 !important;
}

/* ── Dark mode — Modal Migración / Preamortización ── */
body.dark-mode #modalMigracion .modal-content {
    background: #1e293b;
    color: #e2e8f0;
}
body.dark-mode #modalMigracion .modal-body {
    background: #1e293b;
}
body.dark-mode #modalMigracion .modal-footer {
    background: #1e293b;
    border-color: #334155;
}
body.dark-mode #modalMigracion .form-control,
body.dark-mode #modalMigracion .form-select {
    background: #0f172a;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode #modalMigracion .form-control::placeholder { color: #64748b; }
body.dark-mode #modalMigracion .input-group-text {
    background: #334155;
    border-color: #475569;
    color: #94a3b8;
}
body.dark-mode #modalMigracion label { color: #cbd5e1; }
body.dark-mode #modalMigracion hr { border-color: #334155; }
body.dark-mode #modalMigracion .alert-info {
    background: #0f2744;
    border-color: #1d4ed8;
    color: #93c5fd;
}
body.dark-mode #modalMigracion .alert-success {
    background: #052e16;
    border-color: #166534;
    color: #bbf7d0;
}
body.dark-mode #modalMigracion .alert-danger {
    background: #1f0a0a;
    border-color: #991b1b;
    color: #fca5a5;
}
body.dark-mode #modalMigracion .alert-warning {
    background: #1c1004;
    border-color: #92400e;
    color: #fcd34d;
}

/* Columna preamortización */
body.dark-mode #colPreamortizacion > div {
    background: #0f172a !important;
    border-color: #334155 !important;
}
body.dark-mode #colPreamortizacion h6 {
    color: #c084fc !important;
}
body.dark-mode #preamortTablaWrap {
    scrollbar-color: #334155 #0f172a;
}
body.dark-mode #preamortThead {
    background: #0f172a !important;
}
body.dark-mode #preamortTablaWrap table thead {
    background: #1e293b !important;
}
body.dark-mode #preamortTablaWrap table thead tr {
    background: #1e293b !important;
}
body.dark-mode #preamortTablaWrap table {
    color: #e2e8f0;
}
body.dark-mode #preamortTablaWrap tbody tr:hover td {
    background: rgba(167,139,250,0.07);
}

/* Filas globo e inicial dentro de preamort */
body.dark-mode #preamortBody tr[style*="background:rgba(118,75,162"] {
    background: rgba(167,139,250,0.12) !important;
}
body.dark-mode #preamortBody tr[style*="background:rgba(8,145,178"] {
    background: rgba(8,145,178,0.12) !important;
}

/* Resumen cards dentro del modal */
body.dark-mode #migResumenCards .border {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #e2e8f0;
}
body.dark-mode #migResumenCards .text-muted { color: #64748b !important; }

/* Sección monto adicional */
body.dark-mode #migPreview .border {
    border-color: #334155 !important;
}
body.dark-mode #migPreview [style*="background:#f0fdf4"] {
    background: #052e16 !important;
    border-color: #166534 !important;
}
body.dark-mode #migPreview [style*="background:#f8f5ff"] {
    background: #1e1533 !important;
    border-color: #4c1d95 !important;
}
body.dark-mode #migPreview [style*="background:#fafafa"],
body.dark-mode #colPreamortizacion [style*="background:#fafafa"] {
    background: #0f172a !important;
}
body.dark-mode #migTotalBase,
body.dark-mode #migTotalFinal {
    background: #0f172a !important;
    color: #c084fc !important;
}

/* Loader de SweetAlert2 — siempre amarillo ► eliminar azul */
.swal2-loader {
    border-color: #f59e0b transparent #f59e0b transparent !important;
}

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
            <?php if (!empty($permisoRegistrarConvenioExistente)): ?>
            <button type="button" class="btn btn-outline-warning"
                    id="btnRegistrarConvenioExistente"
                    style="display:none;"
                    onclick="window.abrirModalMigracion()">
                <i class="fas fa-file-import"></i> Registrar Convenio Existente
            </button>
            <?php endif; ?>
            <?php if (!empty($permisoReactivarOfertas)): ?>
            <button type="button" class="btn btn-outline-success"
                    id="btnReactivarOfertas"
                    style="display:none;"
                    onclick="window.reactivarOfertasCredito()">
                <i class="fa-solid fa-rotate-right me-1"></i> Reactivar Ofertas
            </button>
            <?php endif; ?>
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
                           oninput="window.actualizarSliderDisplay(this.value)"
                           onchange="window.actualizarSlider(this.value)">
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

        <!-- ADJUNTAR ARCHIVO -->
        <div id="adjuntarArchivoSection" class="mt-3" style="display:none;">
            <div class="p-3 border rounded" style="background:#f8f5ff;border-color:#c4b5fd !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <label class="form-label fw-bold mb-1" for="convPdfAdjunto">
                            <i class="fas fa-paperclip me-1" style="color:#764ba2;"></i>Adjuntar comprobante (PDF)
                        </label>
                        <input type="file" id="convPdfAdjunto"
                               class="form-control form-control-sm"
                               accept=".pdf,application/pdf"
                               onchange="window.validarPdfAdjuntoConv(this)">
                        <small class="text-muted">Opcional: adjunta el PDF del convenio firmado (máx. 5MB)</small>
                    </div>
                    <div id="convPdfPreview" class="text-center" style="min-width:60px;"></div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN FECHAS ESPECÍFICAS (tipo_calendario = libre) -->
        <!-- Este panel vive dentro del modal "Registrar Convenio Existente" -->

        <!-- TABLA DE AMORTIZACIÓN -->
        <div id="amortSection" class="conv-amort-section" style="display:none;">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 id="amortTitulo" class="fw-bold mb-3" style="color:#5b2d8e;"></h5>
                    <div class="row text-center g-2 mb-3" id="amortResumenCards">
                        <!-- llenado por JS -->
                    </div>
                    <div id="amortResumenAplicacion" class="mb-3" style="display:none;"></div>
                    <div id="amortPersonalizar" class="mb-3" style="display:none;">
                        <div class="p-3 border rounded" style="background:#f0fdf4;border-color:#86efac !important;">
                            <div class="fw-bold mb-2" style="color:#15803d;font-size:0.9rem;">
                                <i class="fas fa-sliders me-1"></i>Ajuste personalizado (opcional)
                            </div>
                            <div class="row g-3 align-items-end">
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-1">Total convenio</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="text" id="amortTotalBase" class="form-control fw-bold" readonly
                                               style="background:#f8f9fa;color:#15803d;font-size:1rem;">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-1">Monto adicional <span class="fw-normal text-muted">(Opcional)</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="amortMontoAdicional" class="form-control" min="0" max="10000" step="0.01"
                                               placeholder="0.00" oninput="window.amortRecalcularTotal()"
                                               title="Máximo $10,000">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-1">Total final <span class="fw-normal text-muted">(editable)</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="amortTotalFinal" class="form-control fw-bold" step="0.01" min="0"
                                               onblur="window.amortTotalFinalChanged()"
                                               style="color:#764ba2;font-size:1rem;"
                                               title="Edita para ajustar el total; la tabla se recalcula automáticamente">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="tabla-amort table table-borderless">
                            <thead>
                                <tr>
                                   <th>Semana</th>
                                   <th>Fecha de Pago Preferencial</th>
                                   <th id="thSemanalCapital">Semanal / Capital</th>
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

<!-- MODAL CONCILIACIÓN -->

<div class="modal fade" id="modalConciliacion" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-file-invoice me-2"></i>
          Conciliación — <span id="concilTitulo"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Loading -->
        <div id="concilLoading" class="text-center py-4">
          <div class="spinner-border text-warning"></div>
          <div class="mt-2 text-muted small">Consultando S2Movil...</div>
        </div>

        <!-- Contenido -->
        <div id="concilContenido" style="display:none;">

          <!-- Visualización de pagos -->
          <div id="concilPagosWrap" class="mb-3"></div>

          <hr>

          <!-- Formulario -->
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label fw-bold">
                <i class="fas fa-paperclip me-1"></i>Comprobante de pago
              </label>
              <input type="file" id="concilArchivo" class="form-control"
                     accept=".pdf,.jpg,.jpeg,.png">
              <small class="text-muted">PDF o imagen (máx. 5MB)</small>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold">Comentario</label>
              <textarea id="concilComentario" class="form-control" rows="2"
                        maxlength="250" placeholder="Opcional (máx. 250 caracteres)"></textarea>
              <small class="text-muted"><span id="concilCharCount">0</span>/250</small>
            </div>
          </div>

        </div>

        <!-- Ya conciliado -->
        <div id="concilYaConciliado" style="display:none;" class="text-center py-3">
          <i class="fas fa-circle-check fa-3x text-success mb-2"></i>
          <div class="fw-bold text-success">Esta semana ya fue conciliada</div>
          <div id="concilFechaPrevia" class="text-muted small mt-1"></div>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-warning text-white" id="concilBtnGuardar"
                onclick="window.guardarConciliacion()" style="display:none;">
          <i class="fas fa-check me-1"></i>Confirmar Conciliación
        </button>
      </div>


    </div>
  </div>
</div>

<!-- MODAL SUBIR COMPROBANTE -->
<div class="modal fade" id="modalSubirComprobante" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-upload me-2"></i>
          Subir Comprobante — <span id="subirCompTitulo"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" id="subirCompBtnClose"></button>
      </div>
      <div class="modal-body">

        <!-- Info de la semana -->
        <div id="subirCompInfo" class="row g-3 mb-3"></div>

        <hr>

        <!-- Fecha del pago -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Fecha del pago realizado</label>
            <input type="date" id="subirCompFecha" class="form-control">
          </div>
        </div>

        <!-- Comprobante -->
        <div class="mb-3">
          <label class="form-label fw-bold">
            <i class="fas fa-paperclip me-1"></i>Comprobante de pago
            <span class="text-danger">*</span>
          </label>
          <input type="file" id="subirCompArchivo" class="form-control"
                 accept=".pdf,.jpg,.jpeg,.png">
          <small class="text-muted">PDF o imagen (máx. 5MB). Obligatorio.</small>
        </div>

        <!-- Comentario -->
        <div class="mb-3">
          <label class="form-label fw-bold">Comentario</label>
          <textarea id="subirCompComentario" class="form-control" rows="2"
                    maxlength="250" placeholder="Opcional"></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" id="subirCompBtnCancelar">Cancelar</button>
        <button class="btn btn-warning text-white" id="subirCompBtnGuardar"
                onclick="window.guardarSubirComprobante()">
          <i class="fas fa-upload me-1"></i>Subir Comprobante
        </button>
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

<!-- MODAL VER COMPROBANTE -->
<div class="modal fade" id="modalVerComprobante" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-eye me-2"></i>
          Ver Comprobante — <span id="verCompTitulo"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" id="verCompBtnClose"></button>
      </div>
      <div class="modal-body">

        <!-- Info de la semana -->
        <div id="verCompInfo" class="row g-3 mb-3"></div>

        <hr>

        <!-- Visualizador del comprobante -->
        <div id="verCompVisualizador" class="mb-3 text-center"></div>

        <!-- Comentario (si existe) -->
        <div id="verCompComentarioWrap" class="mb-3" style="display:none;">
          <label class="form-label fw-bold">Comentario del gestor</label>
          <textarea id="verCompComentario" class="form-control" rows="2" readonly></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <input type="file" id="verCompArchivoReemplazar" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
        <button class="btn btn-warning" id="verCompBtnReemplazar" onclick="window.reemplazarComprobante()">
          <i class="fas fa-rotate me-1"></i>Reemplazar comprobante
        </button>
        <button class="btn btn-secondary" id="verCompBtnCerrar">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- ════════════════════════════════════════════════ -->
<!-- MODAL MIGRACIÓN DE CONVENIO                      -->
<!-- ════════════════════════════════════════════════ -->
<div class="modal fade" id="modalMigracion" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-file-import me-2"></i>Registrar Convenio
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="convenioNormal">

          <!-- STEP 1: Buscar crédito -->
          <div id="migStep1">
            <div id="migBuscadorWrap">
              <label class="form-label fw-bold">ID Crédito</label>
              <div class="input-group mb-3">
                <input type="number" id="migIdCredito" class="form-control"
                       placeholder="Ej. 193141">
                <button class="btn btn-primary" onclick="window.migBuscarCredito()">
                  <i class="fas fa-search"></i> Buscar
                </button>
              </div>
            </div>
            <div id="migInfoCliente" class="alert alert-info d-none"></div>
          </div>

          <!-- STEP 2: Formulario + Preamortización -->
          <div id="migStep2" class="d-none">
            <hr>
            <div class="row g-4">

              <!-- ── COLUMNA IZQUIERDA: Formulario ── -->
              <div class="col-lg-6">

                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Producto</label>
                    <select id="migProducto" class="form-select" onchange="window.migProductoChange()">
                      <option value="">Selecciona...</option>
                    </select>
                  </div>

                  <!-- RADIOS MODO: solo visible cuando se elige Convenio Pago Mixto -->
                  <div class="col-12" id="migModoGloboRadios" style="display:none;">
                    <div class="p-2 rounded" style="background:#f5f0ff;border:1px solid #c4b5fd;">
                      <span class="fw-semibold text-muted small me-3">Modo de pago:</span>
                      <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="migModoGlobo"
                               id="migModoRadioSemanal" value="semanal" checked
                               onchange="window.migModoGloboChange('semanal')">
                        <label class="form-check-label" for="migModoRadioSemanal">
                          <i class="fas fa-calendar-week me-1"></i>Estructura fija (mixto)
                        </label>
                      </div>
                      <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="migModoGlobo"
                               id="migModoRadioLibre" value="libre"
                               onchange="window.migModoGloboChange('libre')">
                        <label class="form-check-label" for="migModoRadioLibre">
                          <i class="fas fa-calendar-days me-1"></i>Fechas específicas
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="col-6" id="filaDescuentoPorcentaje">
                    <label class="form-label">% Descuento</label>
                    <div class="input-group">
                      <input type="number" id="migPorcentaje" class="form-control"
                             min="0" max="100" step="0.01" placeholder="20"
                             oninput="window.migCalcular()">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>

                  <div class="col-6" id="colFechaInicio">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" id="migFechaInicio" class="form-control"
                           oninput="window.migCalcular()">
                  </div>
                  <div id="noticeFechaLibre" class="col-12 d-none">
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>La fecha de inicio del convenio se toma del primer pago de la tabla.</small>
                  </div>

                  <div class="col-6">
                    <label class="form-label">Adeudo Base</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" id="migAdeudo" class="form-control"
                             step="0.01" placeholder="16284.33" readonly
                             style="background:#f8f9fa;" title="Calculado automáticamente según el producto seleccionado">
                    </div>
                    <div id="migBaseSelector" class="d-none" style="margin-top:5px;">
                      <div class="btn-group btn-group-sm w-100" role="group">
                        <input type="radio" class="btn-check" name="migBaseTipo" id="migBase_capital" value="saldo_total_capital" autocomplete="off">
                        <label class="btn btn-outline-primary" for="migBase_capital" style="font-size:.72rem;">Capital</label>
                        <input type="radio" class="btn-check" name="migBaseTipo" id="migBase_interes" value="interes" autocomplete="off">
                        <label class="btn btn-outline-warning" for="migBase_interes" style="font-size:.72rem;">Interés</label>
                        <input type="radio" class="btn-check" name="migBaseTipo" id="migBase_total" value="adeudo_total" autocomplete="off">
                        <label class="btn btn-outline-success" for="migBase_total" style="font-size:.72rem;">Total</label>
                      </div>
                      <small id="migBaseMontos" class="text-muted d-block" style="font-size:.7rem;margin-top:3px;"></small>
                    </div>
                  </div>

                  <!-- Semanas / Bucket — sube antes de Pago Semanal -->
                  <div class="col-6" id="colBucketMorosidad">
                    <label class="form-label" id="labelBucketMorosidad">Bucket Morosidad</label>
                    <input type="text" id="migBucket" class="form-control"
                           placeholder="g) 60 a 89 dias">
                  </div>

                  <div class="col-6" id="colPagoSemanal">
                    <label class="form-label">Pago Semanal</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" id="migPagoSemanal" class="form-control"
                             step="0.01" placeholder="3250" oninput="window.migCalcular()">
                    </div>
                  </div>

                  <!-- Pago Inicial baja después de Pago Semanal -->
                  <div class="col-6" id="colPagoInicial">
                    <label class="form-label">Pago inicial</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" id="globoPagoInicial" class="form-control"
                             step="0.01" min="0" placeholder="0.00"
                             oninput="window.migCalcular()">
                    </div>
                  </div>

                  <div class="col-6" id="colPagoFinal" style="display:none;">
                    <label class="form-label">Pago de Cierre</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" id="migPagoFinal" class="form-control"
                             step="0.01" placeholder="0.00" oninput="window.migCalcular()">
                    </div>
                  </div>
                </div>

                <!-- PANEL FECHAS ESPECÍFICAS – solo visible en modo libre de Pago Mixto -->
                <div id="migLibreWrap" style="display:none;" class="mt-3">
                  <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                      <label class="fw-semibold mb-0 text-nowrap small">Número de pagos:</label>
                      <input type="number" id="migLibreNumPagos" class="form-control form-control-sm"
                             style="width:75px;-moz-appearance:textfield;appearance:textfield;" min="1" max="15" value=""
                             onkeydown="(function(e){var k=e.key;if(k==='e'||k==='E'||k==='+'||k==='-'||k==='.'||k==='ArrowUp'||k==='ArrowDown'){e.preventDefault();}})(event)"
                             oninput="(function(el){var s=el.value;if(s==='')return;var v=parseInt(s);if(isNaN(v)||v<1){el.value=1;window.migLibreGenerarFilas();}else if(v>15){el.value=15;window.migLibreGenerarFilas();}else{window.migLibreGenerarFilas();}})(this)">
                      <small class="text-muted">(máx. 15)</small>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="window.migLibreDistribuirIgual()">
                      <i class="fas fa-equals me-1"></i> Distribuir igual
                    </button>
                    <div id="migLibreTotalIndicador" class="ms-auto px-2 py-1 rounded fw-bold text-center small"
                         style="border:2px solid #e2e8f0;min-width:200px;">
                      Asignado: $0.00 / $0.00
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                      <thead style="background:#f1f5f9;">
                        <tr>
                          <th class="text-center" style="width:45px;">#</th>
                          <th>Fecha de pago</th>
                          <th>Monto ($)</th>
                        </tr>
                      </thead>
                      <tbody id="migLibreFilasBody"></tbody>
                    </table>
                  </div>
                </div>

                <!-- Resumen cards -->
                <div id="migPreview" class="d-none mt-3">
                  <hr>
                  <div class="row text-center g-2" id="migResumenCards"></div>

                  <div class="mt-3 p-3 border rounded" style="background:#f0fdf4;border-color:#86efac !important;">
                    <div class="fw-bold mb-2" style="color:#15803d;font-size:0.9rem;">
                      <i class="fas fa-plus-circle me-1"></i>Monto adicional
                    </div>
                    <div class="row g-3 align-items-end">
                      <div class="col-4">
                        <label class="form-label small text-muted mb-1">Total a pagar</label>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">$</span>
                          <input type="text" id="migTotalBase" class="form-control fw-bold"
                                 readonly style="background:#f8f9fa;color:#15803d;font-size:1rem;">
                        </div>
                      </div>
                      <div class="col-4" id="colMigMontoAdicional">
                        <label class="form-label small text-muted mb-1">Monto adicional <span class="text-muted fw-normal">(Opcional)</span></label>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">$</span>
                          <input type="number" id="migMontoAdicional" class="form-control"
                                 min="0" max="10000" step="0.01" placeholder="0.00"
                                 data-maxintdigits="5"
                                 onkeydown="if(event.key==='e'||event.key==='E')event.preventDefault()"
                                 oninput="window.migRecalcularTotal()"
                                 title="Máximo $10,000">
                        </div>
                      </div>
                      <div class="col-4">
                        <label class="form-label small text-muted mb-1">Total final <span class="text-muted fw-normal">(editable)</span></label>
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">$</span>
                          <input type="number" id="migTotalFinal" class="form-control fw-bold"
                                 step="0.01" min="0"
                                 data-maxintdigits="7"
                                 onkeydown="if(event.key==='e'||event.key==='E')event.preventDefault()"
                                 onblur="window.migTotalFinalChanged()"
                                 style="color:#764ba2;font-size:1rem;"
                                 title="Edita este valor para ajustar el total a pagar; el % de descuento y las semanas se recalcularán automáticamente">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-3 p-3 border rounded" style="background:#f8f5ff;">
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
                      <div id="migPdfPreview" class="text-center" style="min-width:60px;"></div>
                    </div>
                  </div>
                </div>

              </div>
              <!-- ── FIN COLUMNA IZQUIERDA ── -->

              <!-- ── COLUMNA DERECHA: Preamortización ── -->
              <div class="col-lg-6" id="colPreamortizacion">
                <div class="h-100 p-3 border rounded" style="background:#fafafa;min-height:300px;">
                  <h6 class="fw-bold mb-3" style="color:#5b2d8e;">
                    <i class="fas fa-calendar-alt me-2"></i>Tabla de Preamortización
                  </h6>
                  <div id="preamortVacio" class="text-center text-muted py-5">
                    <i class="fas fa-table fa-2x mb-2 d-block"></i>
                    Ingresa los datos del convenio para ver la tabla
                  </div>
                  <div id="preamortTablaWrap" style="display:none;max-height:420px;overflow-y:auto;">
                    <table class="table table-sm table-borderless mb-0">
                      <thead id="preamortThead" style="position:sticky;top:0;background:#fafafa;">
                        <tr class="text-muted small">
                          <th>#</th>
                          <th>Fecha</th>
                          <th>Monto</th>
                          <th>Tipo</th>
                          <th>Saldo</th>
                        </tr>
                      </thead>
                      <tbody id="preamortBody"></tbody>
                    </table>
                  </div>
                </div>
              </div>
              <!-- ── FIN COLUMNA DERECHA ── -->

            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Cancelar
        </button>
        <button class="btn btn-success" id="migBtnGuardar"
                onclick="window.migGuardar()" style="display:none;">
          <i class="fas fa-save me-1"></i> Registrar Convenio
        </button>
      </div>

    </div>
  </div>
</div>




<script>



// ══════════════════════════════════════════════════════
//  Estado global
// ══════════════════════════════════════════════════════
var _credito = null;
var _ofertas = [];
var _ofertaActiva = null;
var _semanasActual = 1;
var _rayoModo = null; // 'unico' | 'quincenas' | 'semanal'
// var _pollingInterval = null;
var _estatusS2 = null;
var _estatusConvenio = null;

// ── Helpers de badge para el banner ──
function _renderS2Badge(s2) {
    if (!s2 || !s2.statusCredito) return '<span class="badge bg-secondary" style="font-size:.7rem;">—</span>';
    var st = s2.statusCredito;
    var color = st === 'Saldado' ? '#22c55e' : st === 'Vigente' ? '#3b82f6' : st === 'Vencido' ? '#ef4444' : '#64748b';
    var icon  = st === 'Saldado' ? '<i class="fa-solid fa-circle-check me-1"></i>' :
                st === 'Vigente' ? '<i class="fa-solid fa-circle-dot me-1"></i>'   :
                st === 'Vencido' ? '<i class="fa-solid fa-circle-xmark me-1"></i>' : '';
    var html = '<span class="badge" style="background:' + color + ';font-size:.7rem;">' + icon + st + '</span>';
    if (st === 'Saldado' && s2.fechaLiquidacion) {
        var fLiq = new Date(s2.fechaLiquidacion + 'T12:00:00').toLocaleDateString('es-MX', {day:'2-digit', month:'2-digit', year:'numeric'});
        html += '<span style="display:block;font-size:.62rem;color:#94a3b8;margin-top:2px;">';
        if (s2.motivo) html += s2.motivo + ' · ';
        html += fLiq + '</span>';
    }
    return html;
}

function _renderConvenioBadge(est) {
    if (est === 'activo')       return '<span class="badge" style="background:#22c55e;font-size:.7rem;"><i class="fa-solid fa-lock me-1"></i>Activo</span>';
    if (est === 'completado')   return '<span class="badge" style="background:#3b82f6;font-size:.7rem;"><i class="fa-solid fa-circle-check me-1"></i>Completado</span>';
    if (est === 'cancelado')    return '<span class="badge" style="background:#ef4444;font-size:.7rem;"><i class="fa-solid fa-ban me-1"></i>Cancelado</span>';
    if (est === 'sin_convenio') return '<span class="badge bg-secondary" style="font-size:.7rem;">Sin convenio</span>';
    return '<span class="badge bg-secondary" style="font-size:.7rem;">—</span>';
}

function actualizarColumnaS2() {
    var el = document.getElementById('s2StatusVal');
    if (el) el.innerHTML = _renderS2Badge(_estatusS2);

    var elAdeudo = document.getElementById('adeudoTotalVal');
    if (elAdeudo && _estatusS2) {
        var adeudoS2 = parseFloat(_estatusS2.adeudoTotal || 0);
        elAdeudo.textContent = '$' + adeudoS2.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        elAdeudo.style.color = adeudoS2 === 0 ? '#22c55e' : '#4ade80';
    }

    if (_estatusS2 && _estatusS2.statusCredito === 'Saldado') {
        var elAvance = document.getElementById('avancePagoVal');
        if (elAvance) elAvance.textContent = '100.0%';
    }

    verificarSaldado();
}

function verificarSaldado() {
    if (!_estatusS2 || _estatusS2.statusCredito !== 'Saldado') return;
    var cont = document.getElementById('ofertasContainer');
    if (!cont) return;

    var fmt = function (v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    // Buscar convenio no cancelado en el historial
    var historial = (_credito && _credito.historial_convenios) || [];
    var convComp = null;
    for (var i = 0; i < historial.length; i++) {
        if (historial[i].estatus !== 'cancelado') { convComp = historial[i]; break; }
    }

    var html =
        '<div class="alert d-flex align-items-start gap-3 mb-0" ' +
        'style="background:linear-gradient(135deg,#d1fae5,#bbf7d0);border:1px solid #22c55e;border-radius:.75rem;color:#166534;">' +
        '<i class="fa-solid fa-circle-check fa-2x mt-1" style="color:#22c55e;flex-shrink:0;"></i>' +
        '<div>' +
        '<div class="fw-bold" style="font-size:1rem;">Crédito Saldado!</div>';

    if (convComp) {
        var fAcuerdo = convComp.fecha_acuerdo
            ? new Date(convComp.fecha_acuerdo + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
            : '—';
        html +=
            '<div style="margin-top:.35rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">' +
            '<span class="badge" style="background:#22c55e;font-size:.78rem;">' + convComp.nombre_producto + '</span>' +
            '<span class="text-muted" style="font-size:.85rem;">' +
            fmt(convComp.total_a_pagar) + ' · ' + convComp.numero_semanas + ' sem' +
            '</span>' +
            '<span style="font-size:.8rem;">Acuerdo: ' + fAcuerdo + '</span>' +
            '</div>';
    } else {
        html += '<div style="font-size:.85rem;margin-top:.25rem;">Sin convenio registrado en el sistema.</div>';
    }

    if (_estatusS2.fechaLiquidacion) {
        var fLiq = new Date(_estatusS2.fechaLiquidacion + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
        html += '<div style="font-size:.78rem;margin-top:.25rem;opacity:.85;">Liquidado el ' + fLiq;
        if (_estatusS2.motivo) html += ' · ' + _estatusS2.motivo;
        html += '</div>';
    }

    html += '</div></div>';

    cont.innerHTML = html;

    // Actualizar estatus convenio en el banner
    _estatusConvenio = convComp ? 'completado' : 'sin_convenio';
    var elConv = document.getElementById('convenioStatusVal');
    if (elConv) elConv.innerHTML = _renderConvenioBadge(_estatusConvenio);

    document.getElementById('sliderSection').style.display = 'none';
    document.getElementById('adjuntarArchivoSection').style.display = 'none';
    document.getElementById('btnGuardar').style.display = 'none';
    var _btnRegConv = document.getElementById('btnRegistrarConvenioExistente');
    if (_btnRegConv) _btnRegConv.style.display = 'none';
}

// ══════════════════════════════════════════════════════
//  BUSCAR CRÉDITO
// ══════════════════════════════════════════════════════
window.buscarCredito = function () {
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

document.getElementById('inputBusqueda').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') window.buscarCredito();
});

// ══════════════════════════════════════════════════════
//  SELECCIONAR CRÉDITO → ofertas + convenio activo
// ══════════════════════════════════════════════════════
function seleccionarCredito(idCredito) {
    document.getElementById('convContenido').style.display = 'none';
    document.getElementById('btnHistorialWrap').style.display = 'none';
    var _btnRegConvSel = document.getElementById('btnRegistrarConvenioExistente');
    if (_btnRegConvSel) _btnRegConvSel.style.display = 'none';
    var _btnReactSel = document.getElementById('btnReactivarOfertas');
    if (_btnReactSel) _btnReactSel.style.display = 'none';

    var bannerPrevio = document.getElementById('bannerConvenioActivo');
    if (bannerPrevio) bannerPrevio.remove();
    var docAdjPrevio = document.getElementById('docAdjuntoConvenio');
    if (docAdjPrevio) docAdjPrevio.remove();

    // Resetear estado S2 y disparar consulta ligera (fire-and-forget)
    _estatusS2 = null;
    _estatusConvenio = null;
    http.request({
        endpoint: '/convenios/getEstatusS2',
        method: 'POST',
        data: { id_credito: idCredito },
        showLoader: false,
        onSuccess: function (respS2) {
            _estatusS2 = (respS2.success && respS2.datos) ? respS2.datos : null;
            actualizarColumnaS2();
        },
        onError: function () { _estatusS2 = null; actualizarColumnaS2(); }
    });

    // Llamada 1: ofertas
    var _errManejadoSel = false;
    http.request({
        endpoint: '/convenios/getOfertasCredito',
        method: 'POST',
        data: { id_credito: idCredito },
        onSuccess: function (respOfertas) {
            if (!respOfertas.success) {
                _errManejadoSel = true;
                var _msgSel = respOfertas.mensaje || 'Este crédito no cumple los criterios.';
                setTimeout(function () {
                    Swal.fire('Sin elegibilidad', _msgSel, 'info');
                }, 0);
                return;
            }

            var datos = respOfertas.datos;
            var credito = datos.credito;
            var ofertas = datos.ofertas;
            var elegible = datos.elegible;
            var razon = datos.razon;
            var estaReactivado = !!datos.reactivado;

            _credito = credito;
            _ofertas = ofertas;

            // Función interna: lanza el flujo historial → convenio activo
            var _lanzarHistorial = function () {
            // Llamada 3: historial para saber qué oferta pintar de gris
            http.request({
                endpoint: '/convenios/getHistorialConvenios',
                method: 'POST',
                data: { id_credito: idCredito },
                onSuccess: function (respHistorial) {
                    if (respHistorial.success && respHistorial.datos) {
                        _credito.historial_convenios = respHistorial.datos;
                    }

                    // Llamada 3: convenio activo
                    http.request({
                        endpoint: '/convenios/getConvenioActivo',
                        method: 'POST',
                        data: { id_credito: idCredito },
                        onSuccess: function (respConvenio) {
                            Swal.close();

                            document.getElementById('btnGuardar').style.display = 'inline-block';
                            document.getElementById('btnPdf').style.display = 'none';
                            document.getElementById('btnPdf').className = 'btn btn-outline-secondary';
                            document.getElementById('btnCancelar').style.display = 'none';

                            _estatusConvenio = (respConvenio.success && respConvenio.datos)
                                ? (respConvenio.datos.estatus === 'activo' ? 'activo' : (respConvenio.datos.estatus === 'completado' ? 'completado' : 'sin_convenio'))
                                : 'sin_convenio';
                            // Si reactivado + sólo convenios completados, usar badge neutro
                            if (estaReactivado && _estatusConvenio === 'completado') {
                                _estatusConvenio = 'sin_convenio';
                            }
                            renderCreditoBanner(credito);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);

                            var _sliderEl = document.getElementById('sliderSemanas');
                            _sliderEl.disabled = false;
                            _sliderEl.value = 1;
                            document.getElementById('sliderSection').style.display = 'none';
                            document.getElementById('adjuntarArchivoSection').style.display = 'none';
                            document.getElementById('amortSection').style.display = 'none';
                            document.getElementById('alertaIncumplimiento').style.display = 'none';
                            var _docAdjLimp = document.getElementById('docAdjuntoConvenio');
                            if (_docAdjLimp) _docAdjLimp.remove();
                            _ofertaActiva = null;

                            document.getElementById('convContenido').style.display = 'block';
                            document.getElementById('btnHistorialWrap').style.display = 'block';

                            verificarSaldado();

                            // Solo congelar si hay convenio activo (no congelar en créditos reactivados con convenio completado)
                            if (respConvenio.success && respConvenio.datos &&
                                respConvenio.datos.estatus === 'activo') {
                                congelarModulo(respConvenio.datos);
                            } else if (!estaReactivado && respConvenio.success && respConvenio.datos &&
                                respConvenio.datos.estatus === 'completado') {
                                congelarModulo(respConvenio.datos);
                            }
                        },
                        onError: function () {
                            Swal.close();
                            _estatusConvenio = 'desconocido';
                            renderCreditoBanner(credito);
                            renderOfertas(ofertas);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);
                            document.getElementById('convContenido').style.display = 'block';
                            verificarSaldado();
                        }
                    });
                },
                onError: function () {
                    // Si falla el historial, continuamos sin él
                    http.request({
                        endpoint: '/convenios/getConvenioActivo',
                        method: 'POST',
                        data: { id_credito: idCredito },
                        onSuccess: function (respConvenio) {
                            Swal.close();
                            document.getElementById('btnGuardar').style.display = 'inline-block';
                            document.getElementById('btnPdf').style.display = 'none';
                            document.getElementById('btnPdf').className = 'btn btn-outline-secondary';
                            document.getElementById('btnCancelar').style.display = 'none';
                            _estatusConvenio = (respConvenio.success && respConvenio.datos)
                                ? (respConvenio.datos.estatus === 'activo' ? 'activo' : (respConvenio.datos.estatus === 'completado' ? 'completado' : 'sin_convenio'))
                                : 'sin_convenio';
                            if (estaReactivado && _estatusConvenio === 'completado') {
                                _estatusConvenio = 'sin_convenio';
                            }
                            renderCreditoBanner(credito);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);
                            document.getElementById('convContenido').style.display = 'block';
                            verificarSaldado();

                            if (respConvenio.success && respConvenio.datos &&
                                respConvenio.datos.estatus === 'activo') {
                                congelarModulo(respConvenio.datos);
                            } else if (!estaReactivado && respConvenio.success && respConvenio.datos &&
                                respConvenio.datos.estatus === 'completado') {
                                congelarModulo(respConvenio.datos);
                            }
                        },
                        onError: function () {
                            Swal.close();
                            document.getElementById('btnGuardar').style.display = 'inline-block';
                            document.getElementById('btnPdf').style.display = 'none';
                            document.getElementById('btnCancelar').style.display = 'none';
                            _estatusConvenio = 'desconocido';
                            renderCreditoBanner(credito);
                            var bloqueados = datos.productos_bloqueados || [];
                            renderOfertas(ofertas, bloqueados);
                            document.getElementById('convContenido').style.display = 'block';
                            verificarSaldado();
                        }
                    });
                }
            });
            }; // fin _lanzarHistorial

            // Si el convenio ya está completado, saltar la validación de despacho
            // (el crédito pudo regularizarse y ya no está en asigna_creditos_despacho activo)
            // También si el crédito fue reactivado, saltar la validación de despacho
            if (datos.razon === 'convenio_completado' || estaReactivado) {
                _lanzarHistorial();
                return;
            }

            // Llamada 2: validar que el crédito esté en despacho
            var _errManejadoDesp2 = false;
            http.request({
                endpoint: '/convenios/checkDespacho',
                method: 'POST',
                data: { id_credito: idCredito },
                showLoader: false,
                onSuccess: function (respDesp) {
                    if (!respDesp.success) {
                        _errManejadoDesp2 = true;
                        setTimeout(function () {
                            Swal.fire({
                                icon: 'warning',

                                title: 'Crédito no asignado',
                                html:
                                    '<strong>' + credito.Nombre_cliente + '</strong> — Crédito #' + credito.Id_credito + '<br><br>' +
                                    'El crédito no se encuentra disponible para convenio.<br>' +
                                    '<span class="text-muted" style="font-size:.9rem;">Verifica que esté asignado a convenios. Si tienes más dudas, consulta con el administrador.</span>',
                                confirmButtonColor: '#764ba2',
                            });
                        }, 0);
                        return;
                    }
                    _lanzarHistorial();
                },
                onError: function () {
                    if (_errManejadoDesp2) return;
                    // Si falla la validación de despacho, continuar igualmente (soft fail)
                    _lanzarHistorial();
                }
            });
        },
        onError: function () {
            if (_errManejadoSel) return;
            setTimeout(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo cargar la información del crédito. Intenta de nuevo.',
                    confirmButtonColor: '#764ba2'
                });
            }, 0);
        }
    });
}

// ══════════════════════════════════════════════════════
//  BANNER DEL CRÉDITO
// ══════════════════════════════════════════════════════
function renderCreditoBanner(c) {
    var bucketColor = function (b) {
        if (!b) return 'bg-secondary';
        if (b.indexOf('e)') === 0) return 'bg-warning text-dark';
        if (b.indexOf('f)') === 0) return 'bg-orange text-white';
        return 'bg-danger text-white';
    };

    var avanceRaw = parseFloat(c.Avance_Pago_Plazo || 0).toFixed(1);
    var avance = (_estatusS2 && _estatusS2.statusCredito === 'Saldado') ? '100.0' : avanceRaw;
    var adeudo = parseFloat(c.Adeudo_total || 0);
    var fmt = function (v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    document.getElementById('creditoBanner').innerHTML =
        '<div class="row g-3">' +
        '<div class="col-6 col-md-2"><div class="label">Crédito</div><div class="valor">#' + c.Id_credito + '</div></div>' +
        '<div class="col-6 col-md-2"><div class="label">Cliente</div><div class="valor" style="font-size:0.9rem;">' + c.Nombre_cliente + '</div></div>' +
        '<div class="col-6 col-md-2"><div class="label">Bucket</div><span class="bucket-badge ' + bucketColor(c.Bucket_Morosidad_Real) + '">' + c.Bucket_Morosidad_Real + '</span></div>' +
        '<div class="col-6 col-md-1"><div class="label">Avance pago</div><div class="valor" id="avancePagoVal">' + avance + '%</div></div>' +
        '<div class="col-6 col-md-2"><div class="label">Adeudo total Actual</div><div class="valor" id="adeudoTotalVal" style="color:' + (_estatusS2 !== null ? (parseFloat(_estatusS2.adeudoTotal||0) === 0 ? '#22c55e' : '#4ade80') : '#4ade80') + ';">' + (_estatusS2 !== null ? ('$' + parseFloat(_estatusS2.adeudoTotal||0).toLocaleString('es-MX',{minimumFractionDigits:2})) : fmt(adeudo)) + '</div></div>' +
        '<div class="col-6 col-md-2"><div class="label">Estatus convenio</div><div class="valor" id="convenioStatusVal">' + _renderConvenioBadge(_estatusConvenio) + '</div></div>' +
        '<div class="col-6 col-md-1"><div class="label">Estatus S2</div><div class="valor" id="s2StatusVal">' + (_estatusS2 ? _renderS2Badge(_estatusS2) : '<span class="badge bg-secondary" style="font-size:.7rem;"><i class="fa fa-spinner fa-spin"></i></span>') + '</div></div>' +
        '</div>';

    var _btnRegConvBanner = document.getElementById('btnRegistrarConvenioExistente');
    if (_btnRegConvBanner) _btnRegConvBanner.style.display = 'inline-block';

    var _btnReact = document.getElementById('btnReactivarOfertas');
    if (_btnReact) _btnReact.style.display = 'inline-block';
}

// ══════════════════════════════════════════════════════
//  RENDERIZAR CARDS DE OFERTAS
// ══════════════════════════════════════════════════════
var OFERTA_ICONOS = { 1: '🎯', 2: '💰', 3: '❄️', 4: '⚡' };

function renderOfertas(ofertas, productosBloqueados) {
    var cont = document.getElementById('ofertasContainer');
    var fmt = function (v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    // ── Excluir el producto interno de Convenio Pago Mixto (se registra por otra pestaña) ──
    ofertas = ofertas.filter(function (o) {
        return o.nombre !== 'Convenio Pago Mixto';
    });

    // Construir mapa id_producto → nombre desde historial y ofertas actuales
    var _historialProductos = {};
    ofertas.forEach(function (o) {
        _historialProductos[o.id_producto] = o.nombre;
    });
    if (_credito && _credito.historial_convenios) {
        _credito.historial_convenios.forEach(function (c) {
            if (c.id_producto_convenio && c.nombre_producto) {
                _historialProductos[c.id_producto_convenio] = c.nombre_producto;
            }
        });
    }

    // Construir mapa de productos usados en historial (cancelados/completados normales)
    var productosUsados = {};
    if (_credito && _credito.historial_convenios && _credito.historial_convenios.length > 0) {
        var historialOrdenado = _credito.historial_convenios
            .filter(function (c) { return c.estatus !== 'activo'; })
            .sort(function (a, b) { return new Date(b.fecha_alta) - new Date(a.fecha_alta); });

        historialOrdenado.forEach(function (c) {
            var idProd = c.id_producto_convenio;
            if (!productosUsados[idProd]) {
                productosUsados[idProd] = c.estatus;
            }
        });
    }

    // Cards de ofertas disponibles
    var htmlOfertas = ofertas.map(function (o, i) {
        var icono = OFERTA_ICONOS[o.id_producto] || '📋';
        var primerPago = (o.pago_inicial === 'Si' && o.pago_inicial_monto)
            ? fmt(o.pago_inicial_monto)
            : 'No requiere primer pago';
        var plazoLabel = parseInt(o.semanas_max) === 1
            ? '1 semana (pago único)'
            : o.periodo_inicio + ' a ' + o.semanas_max + ' semanas';

        var estatusPrevio = productosUsados[o.id_producto] || null;
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
        var cursor = estatusPrevio ? 'cursor:not-allowed;' : '';

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
        productosBloqueados.forEach(function (idProd) {
            var nombre = _historialProductos[idProd] || 'Producto';
            var icono = OFERTA_ICONOS[idProd] || '🚫';
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

    // ── Prohibición total: sin ofertas disponibles por bloqueo de incumplimiento ──
    if (ofertas.length === 0 && productosBloqueados && productosBloqueados.length > 0) {
        cont.innerHTML =
            '<div class="col-12">' +
            '<div class="alert d-flex align-items-start gap-3 mb-0 p-4" ' +
            'style="background:#fff1f2;border:2px solid #ef4444;border-radius:12px;">' +
            '<div style="font-size:2.2rem;line-height:1;">🚫</div>' +
            '<div>' +
            '<div class="fw-bold mb-1" style="color:#991b1b;font-size:1.05rem;">' +
            'Convenios Prohibidos — Crédito bloqueado por incumplimiento' +
            '</div>' +
            '<div style="color:#7f1d1d;font-size:0.9rem;line-height:1.5;">' +
            'Este crédito tiene convenios cancelados por incumplimiento en todos los productos disponibles. ' +
            'No es posible generar un nuevo convenio.<br>' +
            '<span style="margin-top:.4rem;display:inline-block;">' +
            '<i class="fas fa-circle-info me-1"></i>' +
            'Para cualquier duda o excepción, consulta con el <strong>Administrador del sistema</strong>.' +
            '</span>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            htmlBloqueados;

        document.getElementById('sliderSection').style.display = 'none';
        document.getElementById('adjuntarArchivoSection').style.display = 'none';
        document.getElementById('amortSection').style.display = 'none';
        var _docAdjLimp2 = document.getElementById('docAdjuntoConvenio');
        if (_docAdjLimp2) _docAdjLimp2.remove();
        verificarSaldado();
        return;
    }

    cont.innerHTML = htmlOfertas + htmlBloqueados;

    // Si el crédito ya está saldado en S2, ocultar productos y mostrar info de cierre
    verificarSaldado();
}

// ══════════════════════════════════════════════════════
//  RENDERIZAR CELDA DE PAGO REALIZADO (datos S2Movil)
// ══════════════════════════════════════════════════════
function renderCeldaPagoS2(pagosS2, numeroSemana) {
    var pagos = pagosS2 && pagosS2[numeroSemana] ? pagosS2[numeroSemana] : null;

    if (!pagos || pagos.length === 0) {
        return '<span style="color:#aaa;">—</span>';
    }

    var fmt = function (v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };
    var fmtF = function (s) {
        if (!s) return '';
        var p = s.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };

    var html = '';

    pagos.forEach(function (p) {
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


// ══════════════════════════════════════════════════════
//  HELPER — buscar pagos S2 para una semana por fecha
// ══════════════════════════════════════════════════════
function buscarPagosS2PorFecha(pagosS2movil, fechaPagoISO) {
    if (!pagosS2movil || !fechaPagoISO) return [];

    var inicio = new Date(fechaPagoISO + 'T00:00:00');
    var fin = new Date(fechaPagoISO + 'T00:00:00');
    fin.setDate(fin.getDate() + 7);

    var resultado = [];
    Object.keys(pagosS2movil).forEach(function (cuota) {
        var pagos = pagosS2movil[cuota];
        pagos.forEach(function (p) {
            if (!p.fechaValor) return;
            var fv = new Date(p.fechaValor + 'T00:00:00');
            if (fv >= inicio && fv <= fin) {
                resultado.push(Object.assign({}, p, { cuota: cuota }));
            }
        });
    });

    return resultado;
}

// ══════════════════════════════════════════════════════
//  HELPER — agrupar semanas por idPago compartido
// ══════════════════════════════════════════════════════
function calcularGruposConciliacion(amortizacion, pagosS2movil) {
    // Devuelve mapa: numero_semana → { esPrimeroDelGrupo, semanasDelGrupo, pagosS2 }
    var mapa = {};

    amortizacion.forEach(function (fila) {
        if (fila.estatus_pago !== 'pagado') return;
        var pagos = buscarPagosS2PorFecha(pagosS2movil, fila.fecha_pago_real || fila.fecha_pago);
        if (pagos.length === 0) {
            mapa[fila.numero_semana] = { esPrimeroDelGrupo: true, semanasDelGrupo: [fila.numero_semana], pagosS2: [] };
        } else {
            mapa[fila.numero_semana] = { esPrimeroDelGrupo: false, semanasDelGrupo: [], pagosS2: pagos };
        }
    });

    // Agrupar por idPago — si varias semanas comparten el mismo idPago, solo la primera muestra botón
    var idPagoVisto = {};
    amortizacion.forEach(function (fila) {
        if (fila.estatus_pago !== 'pagado') return;
        var entry = mapa[fila.numero_semana];
        if (!entry || entry.pagosS2.length === 0) {
            if (entry) entry.esPrimeroDelGrupo = true;
            return;
        }
        // Tomar el primer idPago representativo
        var idPago = entry.pagosS2[0].idPago;
        if (!idPagoVisto[idPago]) {
            idPagoVisto[idPago] = fila.numero_semana;
            entry.esPrimeroDelGrupo = true;
        } else {
            entry.esPrimeroDelGrupo = false;
            // Agregar esta semana al grupo del primero
            var primeraSemana = idPagoVisto[idPago];
            if (mapa[primeraSemana]) {
                mapa[primeraSemana].semanasDelGrupo.push(fila.numero_semana);
            }
        }
    });

    // Asegurar que el primero de cada grupo se incluya a sí mismo
    amortizacion.forEach(function (fila) {
        var entry = mapa[fila.numero_semana];
        if (entry && entry.esPrimeroDelGrupo && entry.semanasDelGrupo.indexOf(fila.numero_semana) === -1) {
            entry.semanasDelGrupo.unshift(fila.numero_semana);
        }
    });

    return mapa;
}

function congelarModulo(convenio) {

    var progressPrevio = document.getElementById('convProgressWrap');
    if (progressPrevio) progressPrevio.remove();

    var fmt = function (v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    // Identificar card del convenio activo
    var idxOferta = -1;
    if (convenio.tipo !== 'globo') {  // ← NUEVO condicional
        for (var i = 0; i < _ofertas.length; i++) {
            if (_ofertas[i].id_producto === convenio.id_producto_convenio) { idxOferta = i; break; }
        }
    }

    // Aplicar clases a cards y actualizar la seleccionada con datos reales del convenio
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
            // Sobreescribir datos de la card con los valores reales del convenio
            // para evitar la contradicción entre lo que muestra la card y la tabla
            var elPct = cards[j].querySelector('.porcentaje');
            if (elPct) elPct.textContent = parseInt(convenio.porcentaje_descuento) + '%';
            var elDesc = cards[j].querySelector('.desc-oferta');
            if (elDesc) elDesc.textContent = 'Ahorro de ' + fmt(convenio.descuento_monto) + ' sobre ' + fmt(convenio.adeudo_total_original);
            var detalles = cards[j].querySelectorAll('.detalle-item');
            if (detalles[0]) detalles[0].innerHTML = '<i class="fa-solid fa-check"></i> Total a pagar: <strong>' + fmt(convenio.total_a_pagar) + '</strong>';
            if (detalles[2]) detalles[2].innerHTML = '<i class="fa-solid fa-check"></i> Plazos: <strong>' + convenio.numero_semanas + ' sem</strong>';
        } else {
            cards[j].classList.add('congelada');
        }
    }

    // Slider congelado
    var slider = document.getElementById('sliderSemanas');
    slider.disabled = true;
    slider.value = convenio.numero_semanas;
    _semanasActual = convenio.numero_semanas;

    var esRayoCongelado = false;
    if (idxOferta >= 0) {
        _ofertaActiva = _ofertas[idxOferta];
        esRayoCongelado = _ofertaActiva.id_producto === 4;
        if (!esRayoCongelado) {
            slider.min = _ofertaActiva.periodo_inicio;
            slider.max = _ofertaActiva.semanas_max;
            document.getElementById('labelMin').textContent = _ofertaActiva.periodo_inicio + ' sem';
            document.getElementById('labelMax').textContent = _ofertaActiva.semanas_max + ' sem';
        }
        document.getElementById('sliderTitulo').textContent = 'Plazo activo: ' + convenio.nombre_producto;
    } else if (convenio.tipo === 'globo') {
        document.getElementById('sliderTitulo').textContent = 'Plazo activo: ' + convenio.nombre_producto;
        document.getElementById('labelMin').textContent = convenio.numero_semanas + ' sem';
        document.getElementById('labelMax').textContent = convenio.numero_semanas + ' sem';
    } else if (convenio.id_producto_convenio === 4) {
        // Producto Pago Mixto (Rayo) pero no está en _ofertas (ej: convenio ya completado).
        // Forzar modo rayo bloqueado para que el panel no quede interactivo.
        esRayoCongelado = true;
        document.getElementById('sliderTitulo').textContent = 'Plazo activo: ' + convenio.nombre_producto;
    }

    if (esRayoCongelado) {
        // Ocultar slider y mostrar botones rayo bloqueados con la opción activa resaltada
        slider.style.display = 'none';
        document.querySelector('.semanas-labels').style.display = 'none';

        var btnsExistentes = document.getElementById('rayoBtns');
        if (btnsExistentes) btnsExistentes.remove();

        var numSemRayo = convenio.numero_semanas;
        var rayoBtnLabels = { 1: '⚡ 1 Pago único', 2: '📅 2 Quincenas', 4: '📆 4 Semanas' };
        var botonesHtml = [1, 2, 4].map(function (op) {
            var isSelected = op === numSemRayo;
            var cls = isSelected
                ? 'btn btn-primary rayo-btn'
                : 'btn btn-outline-secondary rayo-btn';
            var style = isSelected
                ? 'pointer-events:none;cursor:default;'
                : 'pointer-events:none;cursor:default;opacity:0.35;';
            return '<button class="' + cls + '" style="' + style + '" disabled>' + rayoBtnLabels[op] + '</button>';
        }).join('');

        slider.insertAdjacentHTML('afterend',
            '<div id="rayoBtns" class="d-flex gap-2 justify-content-center mt-2">' + botonesHtml + '</div>'
        );

        var rayoSemLabels = { 1: '1 pago único', 2: '2 quincenas', 4: '4 semanas' };
        var rayoPagoSufijo = numSemRayo === 1 ? ' / pago' : numSemRayo === 2 ? ' / quincena' : ' / semana';
        document.getElementById('semanasValor').textContent = rayoSemLabels[numSemRayo] || numSemRayo;
        document.getElementById('pagoSemanalCalc').textContent =
            '$' + parseFloat(convenio.pago_semanal).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + rayoPagoSufijo;
    } else {
        var _sufijoPago = (convenio.tipo_calendario || '') === 'libre' ? ' / pago' : ' / semana';
        document.getElementById('semanasValor').textContent = convenio.numero_semanas;
        document.getElementById('pagoSemanalCalc').textContent =
            '$' + parseFloat(convenio.pago_semanal).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + _sufijoPago;
    }
    document.getElementById('sliderSection').style.display = 'block';

    // Resumen cards
    document.getElementById('amortTitulo').textContent = '📋 ' + convenio.nombre_producto;
    // Saldo capital = adeudo original almacenado
    var _saldoCapConv = parseFloat(convenio.adeudo_total_original || 0);
    var _descuentoConv = parseFloat(convenio.descuento_monto);
    var _pctDescConv   = parseFloat(convenio.porcentaje_descuento);
    var _esRecargo     = _descuentoConv < 0;  // total > adeudo (monto adicional supera descuento)
    var _descLabelConv = _esRecargo
        ? 'Adicional (' + Math.abs(_pctDescConv).toFixed(2) + '%)'
        : 'Descuento (' + Math.abs(_pctDescConv).toFixed(2) + '%)';
    var _descDisplayConv = _esRecargo
        ? '+' + fmt(Math.abs(_descuentoConv))
        : '-' + fmt(Math.abs(_descuentoConv));
    var _esLibreConv    = (convenio.tipo_calendario || '') === 'libre';
    var _cuartaCard = _esLibreConv
        ? '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Número de pagos</div><div class="fw-bold text-warning fs-5">' + parseInt(convenio.numero_semanas) + ' pagos</div></div></div>'
        : '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Pago Semanal</div><div class="fw-bold text-warning fs-5">' + fmt(convenio.pago_semanal) + '</div></div></div>';
    document.getElementById('amortResumenCards').innerHTML =
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Saldo Capital</div><div class="fw-bold text-primary fs-5">' + fmt(_saldoCapConv) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">' + _descLabelConv + '</div><div class="fw-bold ' + (_esRecargo ? 'text-warning' : 'text-danger') + ' fs-5">' + _descDisplayConv + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Total a Pagar</div><div class="fw-bold text-success fs-5">' + fmt(convenio.total_a_pagar) + '</div></div></div>' +
        _cuartaCard;

    // ── Barra de progreso ──────────────────────────
    var totalPagar = parseFloat(convenio.total_a_pagar);
    var amort = convenio.amortizacion || [];
    var montoPagado = 0;

    amort.forEach(function (fila) {
        if (fila.estatus_pago === 'pagado') {
            montoPagado += parseFloat(fila.pago_semanal || 0);
        } else if (fila.estatus_pago === 'parcial') {
            montoPagado += parseFloat(fila.monto_pagado || 0) + parseFloat(fila.monto_secundario || 0);
        }
    });

    montoPagado = Math.min(montoPagado, totalPagar);
    var montoRestante = Math.max(totalPagar - montoPagado, 0);
    var pct = totalPagar > 0 ? Math.round((montoPagado / totalPagar) * 100) : 0;

    document.getElementById('amortResumenCards').insertAdjacentHTML('afterend',
        '<div class="conv-progress-wrap" id="convProgressWrap">' +
        '<div class="conv-progress-label">' +
        '<span><i class="fas fa-chart-line me-1"></i>Progreso del convenio</span>' +
        '<span>' + pct + '% completado</span>' +
        '</div>' +
        '<div class="conv-progress-bar-bg">' +
        '<div class="conv-progress-bar-fill" style"width:' + pct + '%"></div>' +
        '</div>' +
        '<div class="conv-progress-info">' +
        '<span>Inicio: <strong>' + fmt(totalPagar) + '</strong></span>' +
        '<span class="pagado">✓ Pagado: ' + fmt(montoPagado) + '</span>' +
        '<span class="restante">⏳ Restante: ' + fmt(montoRestante) + '</span>' +
        '<span>Meta: <strong>$0.00</strong></span>' +
        '</div>' +
        '</div>'
    );

    // ── Resumen de Aplicación (monto_adicional) ─────────
    (function () {
        var el = document.getElementById('amortResumenAplicacion');
        if (!el) return;
        var adic = parseFloat(convenio.monto_adicional || 0);
        if (adic > 0) {
            // Mostrar saldo capital = adeudo original almacenado
            var adeudoConv = parseFloat(convenio.adeudo_total_original || 0);
            var totalConv  = parseFloat(convenio.total_a_pagar);
            el.innerHTML =
                '<div class="p-3 border rounded" style="background:#fff8e1;border-color:#fbbf24 !important;">' +
                '<div class="fw-semibold mb-2" style="color:#b45309;font-size:0.85rem;">' +
                '<i class="fas fa-info-circle me-1"></i>Resumen de Aplicación — el total incluye un monto adicional' +
                '</div>' +
                '<div class="row text-center g-2">' +
                '<div class="col-4"><div class="border rounded p-2 bg-white">' +
                '<div class="small text-muted">Adeudo Convenio</div>' +
                '<div class="fw-bold text-primary">' + fmt(adeudoConv) + '</div>' +
                '</div></div>' +
                '<div class="col-4"><div class="border rounded p-2 bg-white">' +
                '<div class="small text-muted">Adicional Calculado</div>' +
                '<div class="fw-bold text-warning">+' + fmt(adic) + '</div>' +
                '</div></div>' +
                '<div class="col-4"><div class="border rounded p-2 bg-white">' +
                '<div class="small text-muted">Total a Cobrar</div>' +
                '<div class="fw-bold text-success">' + fmt(totalConv) + '</div>' +
                '</div></div>' +
                '</div></div>';
            el.style.display = 'block';
        } else {
            el.innerHTML = ''; el.style.display = 'none';
        }
    })();

    // ── Calcular grupos de conciliación ───────────
    var gruposConcil = calcularGruposConciliacion(
        convenio.amortizacion,
        convenio.pagos_s2movil || {}
    );

    console.log('Datos completos del convenio:', convenio);
    console.log('Amortización que llegó:', convenio.amortizacion);

    // ── Pre-computar distribución de sobrantes por grupo ─────────
    var surplusMap = {};
    Object.keys(gruposConcil).forEach(function (sem) {
        var entry = gruposConcil[sem];
        if (!entry.esPrimeroDelGrupo || !entry.semanasDelGrupo || entry.semanasDelGrupo.length < 2) return;
        var pagos = entry.pagosS2 || [];
        if (pagos.length === 0) return;
        var totalPago = parseFloat(pagos[0].montoPago || 0);
        var sobrante  = totalPago;
        entry.semanasDelGrupo.forEach(function (numSem) {
            var filaG = null;
            for (var i = 0; i < convenio.amortizacion.length; i++) {
                if (convenio.amortizacion[i].numero_semana == numSem) { filaG = convenio.amortizacion[i]; break; }
            }
            if (!filaG) return;
            var semPago  = parseFloat(filaG.pago_semanal || 0);
            var aplicado = Math.min(sobrante, semPago);
            surplusMap[numSem] = {
                aplicado:         round2(aplicado),
                totalPago:        totalPago,
                sobranteSobrante: round2(Math.max(0, sobrante - semPago)),
                esSobranteParcial: aplicado < semPago - 0.01
            };
            sobrante = round2(Math.max(0, sobrante - semPago));
        });
    });

    var _totalFilasAmort = convenio.amortizacion.length;
    var filasHtml = convenio.amortizacion.map(function (fila, _idxFila) {
        console.log('➡️ ENTRANDO a procesar semana:', fila.numero_semana, 'estatus:', fila.estatus_pago);
        console.log('Fila procesada:', fila.numero_semana, 'estatus:', fila.estatus_pago);

        // ── Estatus ───────────────────────────────────────────
        var esPagado = fila.estatus_pago === 'pagado';
        var esParcial = fila.estatus_pago === 'parcial';
        var esVencido = fila.estatus_pago === 'vencido';
        var esPendiente = fila.estatus_pago === 'pendiente';
        var esPendienteConciliar = fila.estatus_pago === 'pendiente_conciliar';
        var surplusEntry      = surplusMap[fila.numero_semana] || null;
        var esSobranteParcial = !!(surplusEntry && surplusEntry.esSobranteParcial);

        var estatusBadge = (esPagado && esSobranteParcial)
            ? '<span class="badge bg-warning text-dark"><i class="fas fa-coins me-1"></i>Sobrante parcial</span>'
            : esPagado
            ? '<span class="badge bg-success">Pagado</span>'
            : esParcial
                ? '<span class="badge bg-warning text-dark"><i class="fas fa-circle-half-stroke me-1"></i>Parcial</span>'
                : esVencido
                    ? '<span class="badge bg-danger">Vencido</span>'
                    : esPendienteConciliar
                        ? '<span class="badge" style="background:#7c3aed;color:#fff;"><i class="fas fa-clock me-1"></i>Pend. Conciliar</span>'
                        : '<span class="badge bg-secondary">Pendiente</span>';

        // ── Celda fecha ───────────────────────────────────────
        var celdaFecha = (esPagado || esParcial) && fila.fecha_pago_real
            ? '<span style="display:block;font-weight:600;color:' + (esParcial || esSobranteParcial ? '#f59e0b' : '#22c55e') + ';">'
            + fmtFecha(fila.fecha_pago) + '</span>'
            + '<span style="display:block;color:#888;font-size:0.82em;">' + (esParcial ? 'Parcial' : esSobranteParcial ? 'Sobrante' : 'Pagado') + '</span>'
            : fmtFechaRango(fila.fecha_pago);

        // ── Celda "Pago Realizado" ────────────────────────────
        var celdaMontoPagado = '';

        if (esPagado || esParcial) {
            var pagosS2 = fila.pagos_s2 || [];
            var grupoEntry = gruposConcil[fila.numero_semana];
            var esGrupo = grupoEntry && grupoEntry.semanasDelGrupo && grupoEntry.semanasDelGrupo.length > 1;
            var esPrimeroGrupo = grupoEntry && grupoEntry.esPrimeroDelGrupo;
            var esUltimoGrupo = esGrupo && grupoEntry.semanasDelGrupo[grupoEntry.semanasDelGrupo.length - 1] === fila.numero_semana;
            var fmt2 = function (v) {
                return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            };
            var fmtF2 = function (s) {
                if (!s) return '';
                var p = s.split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            };

            var montoAplicado = parseFloat(fila.monto_pagado || 0);
            var pagoSemanal = parseFloat(fila.pago_semanal || 0);
            var faltante = round2(pagoSemanal - montoAplicado);
            var colorBorde = esParcial ? '#f59e0b' : '#22c55e';
            var colorTexto = esParcial ? '#92400e' : '#166534';
            var colorAplicado = esParcial ? '#b45309' : '#15803d';

            if (!esGrupo || pagosS2.length === 0) {
                // Pago individual o sin datos S2 — monto simple
                var pagosSecundarios = fila.pagos_secundarios || [];
                var montoSec = parseFloat(fila.monto_secundario || 0);
                var tieneSecundario = montoSec > 0.01;
                var montoTotal = tieneSecundario ? round2(montoAplicado + montoSec) : montoAplicado;

                celdaMontoPagado =
                    '<div style="border-left:3px solid ' + colorBorde + ';padding-left:5px;">' +
                    // Si hay pago secundario: mostrar monto original tachado + total actualizado
                    (tieneSecundario
                        ? '<span style="display:block;font-size:0.72rem;color:#9ca3af;text-decoration:line-through;">' +
                          fmt2(montoAplicado) +
                          '</span>' +
                          '<span style="display:block;font-size:0.78rem;font-weight:700;color:' + colorTexto + ';">' +
                          fmt2(montoTotal) +
                          '</span>'
                        : '<span style="display:block;font-size:0.78rem;font-weight:700;color:' + colorTexto + ';">' +
                          fmt2(montoAplicado) +
                          (esParcial ? ' <small style="color:#dc2626;">(parcial)</small>' : '') +
                          '</span>'
                    ) +
                    (esParcial && !tieneSecundario && faltante > 0
                        ? '<span style="display:block;font-size:0.70rem;color:#dc2626;">Falta: ' + fmt2(faltante) + '</span>'
                        : '') +
                    (pagosS2.length > 0
                        ? '<span style="display:block;font-size:0.68rem;color:#aaa;">' + fmtF2(pagosS2[0].fechaValor) + '</span>'
                        : '') +
                    // Pagos secundarios que completaron el déficit (ej: $60 del 23/04)
                    pagosSecundarios.map(function(ps) {
                        return '<span style="display:block;font-size:0.68rem;color:#7c3aed;margin-top:2px;">' +
                               '<i class="fas fa-plus-circle" style="font-size:0.60rem;margin-right:2px;"></i>' +
                               fmt2(ps.montoPago) + ' · ' + fmtF2(ps.fechaValor) +
                               '</span>';
                    }).join('') +
                    '</div>';
            } else {
                // Pago compartido — corchete
                var lineaArriba = !esPrimeroGrupo;
                var lineaAbajo = !esUltimoGrupo;

                celdaMontoPagado =
                    '<div style="display:flex;align-items:stretch;gap:6px;min-height:44px;">' +
                    '<div style="display:flex;flex-direction:column;align-items:center;width:14px;flex-shrink:0;">' +
                    '<div style="width:2px;flex:1;background:' + (lineaArriba ? '#9ca3af' : 'transparent') + ';"></div>' +
                    '<div style="width:8px;height:8px;border-radius:50%;background:' + colorBorde + ';flex-shrink:0;border:1.5px solid #fff;outline:1.5px solid ' + colorBorde + ';"></div>' +
                    '<div style="width:2px;flex:1;background:' + (lineaAbajo ? '#9ca3af' : 'transparent') + ';"></div>' +
                    '</div>' +
                    '<div style="display:flex;align-items:center;width:8px;flex-shrink:0;">' +
                    '<div style="width:100%;height:2px;background:#9ca3af;"></div>' +
                    '</div>' +
                    '<div style="display:flex;flex-direction:column;justify-content:center;">' +
                    (esPrimeroGrupo && pagosS2.length > 0
                        ? '<span style="font-size:0.68rem;color:#64748b;font-weight:600;">' + fmt2(pagosS2[0].montoPago) + ' total</span>'
                        : '') +
                    '<span style="font-size:0.78rem;font-weight:700;color:' + colorTexto + ';">' +
                    fmt2(montoAplicado) +
                    (esParcial ? ' <small style="color:#dc2626;">(parcial)</small>' : '') +
                    '</span>' +
                    (esParcial && faltante > 0
                        ? '<span style="display:block;font-size:0.68rem;color:#dc2626;">Falta: ' + fmt2(faltante) + '</span>'
                        : '') +
                    (esPrimeroGrupo && surplusEntry && surplusEntry.sobranteSobrante > 0.005
                        ? '<span style="display:block;font-size:0.68rem;color:#b45309;font-weight:600;"><i class="fas fa-arrow-right-arrow-left" style="font-size:0.6rem;"></i> Sobrante: ' + fmt2(surplusEntry.sobranteSobrante) + '</span>'
                        : '') +
                    '</div>' +
                    '</div>';
            }

            // Sobrante parcial — override para semanas cubiertas insuficientemente con sobrante
            if (esSobranteParcial) {
                var fmt2ov = function (v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };
                var _sobAmt  = round2(surplusEntry.aplicado);
                var _faltAmt = round2(pagoSemanal - _sobAmt);
                celdaMontoPagado =
                    '<div style="border-left:3px solid #f59e0b;padding-left:5px;">' +
                    '<span style="display:block;font-size:0.72rem;color:#92400e;font-weight:600;">' +
                    '<i class="fas fa-coins" style="font-size:0.65rem;margin-right:3px;"></i>Sobrante aplicado</span>' +
                    '<span style="display:block;font-size:0.78rem;font-weight:700;color:#b45309;">' + fmt2ov(_sobAmt) + '</span>' +
                    (_faltAmt > 0.005
                        ? '<span style="display:block;font-size:0.70rem;color:#dc2626;">Faltan: ' + fmt2ov(_faltAmt) + '</span>'
                        : '') +
                    '</div>';
            }
        }

        // ── Botón acción ──────────────────────────────────────
        var btnAccion = '';

        if (esPagado) {  // SOLO para pagados
            var grupoEntry = gruposConcil[fila.numero_semana];
            var esPrimero = grupoEntry && grupoEntry.esPrimeroDelGrupo;

            if (esPrimero) {
                btnAccion =
                    '<div class="d-flex flex-column gap-1 align-items-center">' +
                    (fila.fecha_pago_real
                        ? '<small class="text-success"><i class="fas fa-check-double"></i> ' + fmtFecha(fila.fecha_pago_real) + '</small>'
                        : '<span class="badge bg-success">✓</span>') +
                    (fila.comprobante_path
                        ? '<button class="btn btn-xs btn-outline-info btn-ver-comprobante mt-1" ' +
                        'style="font-size:.75rem;padding:3px 8px;" ' +
                        'data-path="' + fila.comprobante_path + '" ' +
                        'data-semana="' + fila.numero_semana + '" ' +
                        'data-convenio="' + convenio.id + '" ' +
                        'data-credito="' + convenio.id_credito + '" ' +
                        'title="Ver comprobante">' +
                        '<i class="fas fa-eye"></i>' +
                        '</button>'
                        : '<button class="btn btn-xs btn-outline-warning btn-subir-comprobante mt-1" ' +
                        'style="font-size:.75rem;padding:3px 8px;" ' +
                        'data-semana="' + fila.numero_semana + '" ' +
                        'data-convenio="' + convenio.id + '" ' +
                        'data-credito="' + convenio.id_credito + '" ' +
                        'data-pago-semanal="' + fila.pago_semanal + '" ' +
                        'data-fecha-pago="' + fila.fecha_pago + '" ' +
                        'data-estatus-pagado="1" ' +
                        'title="Subir comprobante">' +
                        '<i class="fas fa-upload"></i>' +
                        '</button>') +
                    '</div>';
            } else {
                btnAccion =
                    '<div class="text-center">' +
                    (fila.fecha_pago_real
                        ? '<small class="text-success" style="font-size:.7rem;"><i class="fas fa-check-double"></i> ' + fmtFecha(fila.fecha_pago_real) + '</small>'
                        : '<span class="badge bg-success">✓</span>') +
                    '</div>';
            }

        } else if (esPendienteConciliar) {
            btnAccion = fila.comprobante_path
                ? '<button class="btn btn-xs btn-outline-info btn-ver-comprobante" ' +
                'style="font-size:.75rem;padding:3px 8px;" ' +
                'data-path="' + fila.comprobante_path + '" ' +
                'data-semana="' + fila.numero_semana + '" ' +
                'data-fecha-pago="' + fila.fecha_pago + '" ' +
                'data-pago-semanal="' + fila.pago_semanal + '" ' +
                'data-fecha-pago-real="' + (fila.fecha_pago_real || '') + '" ' +
                'data-comentario="' + (fila.comentario_gestor || '') + '" ' +
                'data-convenio="' + convenio.id + '" ' +
                'data-credito="' + convenio.id_credito + '" ' +
                'title="Ver comprobante">' +
                '<i class="fas fa-eye"></i>' +
                '</button>'
                : '<span style="color:#7c3aed;font-size:.7rem;">⏳</span>';

            console.log('Evaluando botón para semana', fila.numero_semana,
                'esVencido:', esVencido,
                'esParcial:', esParcial);

        } else if (esVencido || esParcial) {  // Vencidos y Parciales pueden subir comprobante
            btnAccion =
                '<button class="btn btn-xs btn-outline-warning btn-subir-comprobante" ' +
                'style="font-size:.75rem;padding:3px 8px;" ' +
                'data-semana="' + fila.numero_semana + '" ' +
                'data-convenio="' + convenio.id + '" ' +
                'data-credito="' + convenio.id_credito + '" ' +
                'data-pago-semanal="' + fila.pago_semanal + '" ' +
                'data-fecha-pago="' + fila.fecha_pago + '" ' +
                'title="Subir comprobante">' +
                '<i class="fas fa-upload"></i>' +
                '</button>';

        } else if (esPendiente) {
            btnAccion =
                '<button class="btn btn-xs btn-outline-warning btn-subir-comprobante" ' +
                'style="font-size:.75rem;padding:3px 8px;" ' +
                'data-semana="' + fila.numero_semana + '" ' +
                'data-convenio="' + convenio.id + '" ' +
                'data-credito="' + convenio.id_credito + '" ' +
                'data-pago-semanal="' + fila.pago_semanal + '" ' +
                'data-fecha-pago="' + fila.fecha_pago + '" ' +
                'title="Subir comprobante">' +
                '<i class="fas fa-upload"></i>' +
                '</button>';
        }

        var _etiquetaGlobo = '';
        if (convenio.tipo === 'globo') {
            if (_idxFila === 0 && parseFloat(convenio.pago_inicial_monto || 0) > 0) {
                _etiquetaGlobo = '<span style="display:block;font-size:0.68rem;font-weight:600;color:#0891b2;margin-top:2px;">' +
                    '<i class="fas fa-hand-holding-dollar me-1"></i>Pago inicial</span>';
            } else if (_idxFila === _totalFilasAmort - 1) {
                _etiquetaGlobo = '<span style="display:block;font-size:0.68rem;font-weight:600;color:#764ba2;margin-top:2px;">' +
                    '<i class="fas fa-flag-checkered me-1"></i>Pago de Cierre</span>';
            }
        }

        return '<tr>' +
            '<td>Semana ' + fila.numero_semana + _etiquetaGlobo + '</td>' +
            '<td>' + celdaFecha + '</td>' +
            '<td>' +
            '<span style="display:block;font-weight:600;">' + fmt(fila.pago_semanal) + '</span>' +
            '<span style="display:block;font-size:0.82em;color:#888;">' + fmt(fila.capital) + '</span>' +
            '</td>' +
            '<td class="celda-monto-pagado">' + celdaMontoPagado + '</td>' +
            '<td class="celda-estatus">' + estatusBadge + '</td>' +
            '<td>' + btnAccion + '</td>' +
            '</tr>';
    }).join('');

    document.getElementById('tablaAmortBody').innerHTML = filasHtml;
    bindBotonesConciliacionManual();
    document.getElementById('amortSection').style.display = 'block';
    bindBotonesPago(convenio.id_credito);
    bindBotonesConciliacionManual();
    bindBotonesSubirComprobante();
    bindBotonesVerComprobante();

    // Solo PDF visible — ocultar ajuste personalizado (puede quedar visible si verTablaAmortizacion corrió antes)
    var _apFreeze = document.getElementById('amortPersonalizar');
    if (_apFreeze) _apFreeze.style.display = 'none';
    document.getElementById('btnGuardar').style.display = 'none';
    document.getElementById('btnPdf').className = 'btn btn-primary';
    document.getElementById('btnPdf').style.display = 'inline-block';
    document.getElementById('btnCancelar').style.display = convenio.estatus === 'completado' ? 'none' : 'inline-block';
    window._idConvenioActivo = convenio.id;

    // Banner informativo
    var fmtFechaOpc = function (f) {
        if (!f) return '—';
        return new Date(f + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };
    var fechaAcuerdo    = fmtFechaOpc(convenio.fecha_acuerdo);
    var fechaUltimoPago = fmtFechaOpc(convenio.fecha_ultimo_pago);
    var esCompletado    = convenio.estatus === 'completado';
    var bannerTexto     = esCompletado
        ? '<strong>Convenio completado.</strong> Acuerdo del ' + fechaAcuerdo + '. Solo puedes descargar el PDF.'
        : '<strong>Convenio activo registrado.</strong> Acuerdo del ' + fechaAcuerdo + '. Último pago: ' + fechaUltimoPago + '. Solo puedes descargar el PDF.';

    var pdfBadge = '';
    if (convenio.pdf_adjunto) {
        pdfBadge =
            '&nbsp;<a href="' + convenio.pdf_adjunto + '" target="_blank" ' +
            'class="text-decoration-none ms-2" ' +
            'style="display:inline-flex;align-items:center;gap:4px;background:#fff;color:#166634;' +
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
        bannerTexto +
        pdfBadge +
        '</div>' +
        '</div>'
    );

    // ── Sección gestión de documento adjunto (subir / reemplazar post-creación) ──
    var docSectionOld = document.getElementById('docAdjuntoConvenio');
    if (docSectionOld) docSectionOld.remove();

    var _convId   = convenio.id;
    var _credId   = convenio.id_credito;
    var _docHtml  = '<div id="docAdjuntoConvenio" class="mb-3">' +
        '<div class="p-3 border rounded d-flex align-items-center justify-content-between flex-wrap gap-2" ' +
        'style="background:#f8f5ff;border-color:#c4b5fd !important;">' +
        '<div class="fw-bold" style="color:#5b2d8e;font-size:0.9rem;">' +
        '<i class="fas fa-paperclip me-1"></i>Documento adjunto' +
        '</div>';

    if (convenio.pdf_adjunto) {
        _docHtml +=
            '<div class="d-flex align-items-center gap-2">' +
            '<a href="' + convenio.pdf_adjunto + '" target="_blank" class="btn btn-sm btn-outline-success">' +
            '<i class="fas fa-file-pdf me-1" style="color:#dc2626;"></i>Ver documento' +
            '</a>' +
            '<button class="btn btn-sm btn-outline-secondary" onclick="window.toggleDocForm()">' +
            '<i class="fas fa-sync-alt me-1"></i>Reemplazar' +
            '</button>' +
            '</div>';
    } else {
        _docHtml +=
            '<button class="btn btn-sm btn-outline-primary" onclick="window.toggleDocForm()">' +
            '<i class="fas fa-upload me-1"></i>Adjuntar documento' +
            '</button>';
    }

    _docHtml +=
        '</div>' +
        '<div id="docAdjuntoForm" class="p-3 border-start border-end border-bottom rounded-bottom" ' +
        'style="display:none;border-color:#c4b5fd !important;background:#fdfaff;">' +
        '<div class="d-flex align-items-center gap-2 flex-wrap">' +
        '<input type="file" id="docAdjuntoInput" class="form-control form-control-sm" ' +
        'accept=".pdf,application/pdf" style="max-width:320px;">' +
        '<button class="btn btn-sm btn-success" ' +
        'onclick="window.subirDocConvenio(' + _convId + ',' + _credId + ')">' +
        '<i class="fas fa-save me-1"></i>Guardar' +
        '</button>' +
        '<button class="btn btn-sm btn-secondary" onclick="window.toggleDocForm()">' +
        'Cancelar' +
        '</button>' +
        '</div>' +
        '<small class="text-muted d-block mt-1">Solo PDF, máximo 5 MB</small>' +
        '</div>' +
        '</div>';

    document.getElementById('bannerConvenioActivo').insertAdjacentHTML('afterend', _docHtml);

    document.getElementById('amortSection').scrollIntoView({ behavior: 'smooth' });
}

// ── Helper ────────────────────────────────────────────
function round2(v) {
    return Math.round(parseFloat(v) * 100) / 100;
}

// ══════════════════════════════════════════════════════
//  GESTIÓN DOCUMENTO ADJUNTO (post-creación)
// ══════════════════════════════════════════════════════
window.toggleDocForm = function () {
    var form = document.getElementById('docAdjuntoForm');
    if (!form) return;
    var visible = form.style.display !== 'none';
    form.style.display = visible ? 'none' : 'block';
    if (!visible) {
        var inp = document.getElementById('docAdjuntoInput');
        if (inp) inp.value = '';
    }
};

window.subirDocConvenio = function (idConvenio, idCredito) {
    var input = document.getElementById('docAdjuntoInput');
    if (!input || !input.files || !input.files[0]) {
        Swal.fire('Selecciona un archivo', 'Debes elegir un PDF para adjuntar.', 'warning');
        return;
    }
    var file = input.files[0];
    if (file.type !== 'application/pdf') {
        Swal.fire('Formato incorrecto', 'Solo se permiten archivos PDF.', 'warning');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire('Archivo muy grande', 'El PDF no debe exceder 5 MB.', 'warning');
        return;
    }
    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
    var fd = new FormData();
    fd.append('id_convenio', idConvenio);
    fd.append('id_credito',  idCredito);
    fd.append('pdf_adjunto', file);
    http.request({
        endpoint: '/convenios/actualizarPdfConvenio',
        contentType: false,
        processData: false,
        data: fd,
        onSuccess: function (resp) {
            if (resp.success) {
                Swal.fire({ icon: 'success', title: '¡Listo!', text: 'Documento guardado correctamente.', timer: 1500, showConfirmButton: false })
                    .then(function () {
                        document.getElementById('inputBusqueda').value = idCredito;
                        window.buscarCredito();
                    });
            } else {
                Swal.fire('Error', resp.mensaje || 'No se pudo guardar el documento.', 'error');
            }
        },
        onError: function () { Swal.fire('Error', 'Error de conexión.', 'error'); }
    });
};
// ════════════════════════════════════════════════
// REGISTRAR PAGO DE SEMANA
// ════════════════════════════════════════════════
function bindBotonesPago(idCredito) {
    document.querySelectorAll('.btn-registrar-pago').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var semana = btn.getAttribute('data-semana');
            var convenio = btn.getAttribute('data-convenio');
            var credito = idCredito || btn.getAttribute('data-credito');

            Swal.fire({
                title: '¿Registrar pago?',
                html: 'Se marcará la <b>Semana ' + semana + '</b> como pagada.<br>'
                    + '<small class="text-muted">La fecha y monto se verificarán con S2Movil.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#22c55e',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                http.request({
                    endpoint: '/convenios/registrarPago',
                    method: 'POST',
                    data: {
                        id_convenio: convenio,
                        numero_semana: semana,
                        id_credito: credito,
                    },
                    onSuccess: function (resp) {
                        if (!resp.success) {
                            Swal.fire('Error', resp.mensaje || 'No se pudo registrar.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            return;
                        }

                        Swal.fire({
                            title: '¡Registrado!',
                            html: 'Semana ' + semana + ' marcada como pagada.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload(); // AUTO-REFRESH después de pago
                        });
                    },
                    onError: function () {
                        Swal.fire('Error', 'Error de conexión.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check"></i>';
                    }
                });
            });
        });
    });
}


// ══════════════════════════════════════════════════════
//  SELECCIONAR OFERTA → mostrar slider
// ══════════════════════════════════════════════════════
window.seleccionarOferta = function (idx) {
    document.querySelectorAll('.oferta-card').forEach(function (c) { c.classList.remove('seleccionada'); });
    document.getElementById('oferta-card-' + idx).classList.add('seleccionada');

    _ofertaActiva = _ofertas[idx];

    var esLiquidacionRayo = _ofertaActiva.id_producto === 4;
    var esLibre           = _ofertaActiva.tipo_calendario === 'libre';

    // ── Productos de calendario libre: mostrar sección adjunto y aviso de modal ──
    if (esLibre) {
        var sliderLibre = document.getElementById('sliderSemanas');
        sliderLibre.style.display = 'none';
        document.querySelector('.semanas-labels').style.display = 'none';
        var btnsRayoLibre = document.getElementById('rayoBtns');
        if (btnsRayoLibre) btnsRayoLibre.remove();
        var libreNoticeOld = document.getElementById('libreNotice');
        if (libreNoticeOld) libreNoticeOld.remove();
        sliderLibre.insertAdjacentHTML('afterend',
            '<div id="libreNotice" class="alert alert-info mt-2 mb-0" style="font-size:0.87rem;">' +
            '<i class="fas fa-calendar-alt me-2"></i>Este plan usa <strong>fechas específicas de pago</strong>. ' +
            'Configura las fechas con el botón <strong>"Registrar convenio existente"</strong> de arriba.' +
            '</div>'
        );
        document.getElementById('sliderTitulo').textContent = 'Plan: ' + _ofertaActiva.nombre;
        document.getElementById('sliderSection').style.display = 'block';
        document.getElementById('adjuntarArchivoSection').style.display = 'block';
        var _convFileLibre = document.getElementById('convPdfAdjunto');
        if (_convFileLibre) { _convFileLibre.value = ''; document.getElementById('convPdfPreview').innerHTML = ''; }
        document.getElementById('amortSection').style.display = 'none';
        document.getElementById('btnVerAmort').style.display = 'none';
        document.getElementById('sliderSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    var slider = document.getElementById('sliderSemanas');
    // Limpiar aviso libre si venía de selección anterior
    var libreNoticeClean = document.getElementById('libreNotice');
    if (libreNoticeClean) libreNoticeClean.remove();
    if (esLiquidacionRayo) {
        _rayoModo = null;
        slider.style.display = 'none';
        document.querySelector('.semanas-labels').style.display = 'none';

        var btnsExistentes = document.getElementById('rayoBtns');
        if (btnsExistentes) btnsExistentes.remove();

        slider.insertAdjacentHTML('afterend',
            '<div id="rayoBtns" class="d-flex gap-2 justify-content-center mt-2">' +
            '<button class="btn btn-outline-primary rayo-btn" data-rayo="1" onclick="window.seleccionarRayo(1, event)">⚡ 1 Pago único</button>' +
            '<button class="btn btn-outline-primary rayo-btn" data-rayo="2" onclick="window.seleccionarRayo(2, event)">📅 2 Quincenas</button>' +
            '<button class="btn btn-outline-primary rayo-btn" data-rayo="4" onclick="window.seleccionarRayo(4, event)">📆 4 Semanas</button>' +
            '</div>'
        );

        _semanasActual = null;
        document.getElementById('semanasValor').textContent = 'Selecciona';
        document.getElementById('pagoSemanalCalc').textContent = 'plazo de pagos';
    } else {
        slider.style.display = 'block';
        document.querySelector('.semanas-labels').style.display = 'flex';
        var btnsRayo = document.getElementById('rayoBtns');
        if (btnsRayo) btnsRayo.remove();
        _rayoModo = null;

        slider.min = _ofertaActiva.periodo_inicio;
        slider.max = _ofertaActiva.semanas_max;
        slider.step = 1;
        slider.value = _ofertaActiva.periodo_inicio;
        document.getElementById('labelMin').textContent = _ofertaActiva.periodo_inicio + ' sem';
        document.getElementById('labelMax').textContent = _ofertaActiva.semanas_max + ' sem';
    }

    document.getElementById('sliderTitulo').textContent = 'Plazo para: ' + _ofertaActiva.nombre;

    if (!esLiquidacionRayo) {
        actualizarSlider(slider.value);
    }

    document.getElementById('sliderSection').style.display = 'block';
    document.getElementById('adjuntarArchivoSection').style.display = 'block';
    var _convFileInput = document.getElementById('convPdfAdjunto');
    if (_convFileInput) { _convFileInput.value = ''; document.getElementById('convPdfPreview').innerHTML = ''; }
    document.getElementById('amortSection').style.display = 'none';
    document.getElementById('btnVerAmort').style.display = 'none';

    document.getElementById('sliderSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// ══════════════════════════════════════════════════════
//  ACTUALIZAR SLIDER
// ══════════════════════════════════════════════════════
window.actualizarSliderDisplay = function (semanas) {
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
        var total = parseFloat(_ofertaActiva.total_a_pagar);
        var divisor = esLiquidacionRayo && _semanasActual === 4 ? 2 : _semanasActual;
        var semanal = total / divisor;
        document.getElementById('pagoSemanalCalc').textContent =
            '$' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) +
            (esLiquidacionRayo && _semanasActual === 4 ? ' / quincena' : ' / semana');
    }
};

window.actualizarSlider = function (semanas) {
    window.actualizarSliderDisplay(semanas);
    verTablaAmortizacion();
};

// ══════════════════════════════════════════════════════
//  PANEL LIBRE — funciones del formulario de fechas
// ══════════════════════════════════════════════════════

window.libreGenerarFilas = function () {
    var n = parseInt(document.getElementById('libreNumPagos').value) || 1;
    if (n < 1)  n = 1;
    if (n > 52) n = 52;

    var tbody = document.getElementById('libreFilasBody');
    var filas = tbody ? tbody.querySelectorAll('tr') : [];
    var valoresAnteriores = [];
    filas.forEach(function (tr) {
        valoresAnteriores.push({
            fecha: tr.querySelector('.libre-fecha') ? tr.querySelector('.libre-fecha').value : '',
            monto: tr.querySelector('.libre-monto') ? tr.querySelector('.libre-monto').value : '',
        });
    });

    var html = '';
    for (var i = 0; i < n; i++) {
        var fechaVal = (valoresAnteriores[i] && valoresAnteriores[i].fecha) ? valoresAnteriores[i].fecha : '';
        var montoVal = (valoresAnteriores[i] && valoresAnteriores[i].monto) ? valoresAnteriores[i].monto : '';
        html +=
            '<tr>' +
            '<td class="text-center fw-bold text-muted">' + (i + 1) + '</td>' +
            '<td><input type="date" class="form-control form-control-sm libre-fecha" value="' + fechaVal + '" oninput="window.libreActualizarTotal()"></td>' +
            '<td><input type="number" class="form-control form-control-sm libre-monto" min="0" step="0.01" placeholder="0.00" value="' + montoVal + '" oninput="window.libreActualizarTotal()"></td>' +
            '</tr>';
    }
    if (tbody) tbody.innerHTML = html;
    window.libreActualizarTotal();
};

window.libreActualizarTotal = function () {
    if (!_ofertaActiva) return;
    var totalEsperado = parseFloat(_ofertaActiva.total_a_pagar);
    var filas = document.querySelectorAll('#libreFilasBody .libre-monto');
    var asignado = 0;
    filas.forEach(function (inp) {
        var v = parseFloat(inp.value);
        if (!isNaN(v) && v > 0) asignado += v;
    });
    asignado = Math.round(asignado * 100) / 100;

    var indicador  = document.getElementById('libreTotalIndicador');
    var btnGuardar = document.getElementById('btnGuardarLibre');
    var diferencia = Math.abs(asignado - totalEsperado);
    var cuadra     = diferencia < 0.02; // tolerancia 2 centavos por redondeos

    indicador.textContent = 'Asignado: $' + asignado.toLocaleString('es-MX', { minimumFractionDigits: 2 }) +
        ' / $' + totalEsperado.toLocaleString('es-MX', { minimumFractionDigits: 2 });

    if (cuadra) {
        indicador.style.borderColor  = '#22c55e';
        indicador.style.color        = '#15803d';
        indicador.style.background   = '#f0fdf4';
        if (btnGuardar) btnGuardar.disabled = false;
    } else {
        indicador.style.borderColor  = '#ef4444';
        indicador.style.color        = '#dc2626';
        indicador.style.background   = '#fff1f2';
        if (btnGuardar) btnGuardar.disabled = true;
    }
};

window.libreDistribuirIgual = function () {
    if (!_ofertaActiva) return;
    var n      = parseInt(document.getElementById('libreNumPagos').value) || 1;
    var total  = parseFloat(_ofertaActiva.total_a_pagar);
    var base   = Math.floor((total / n) * 100) / 100;
    var resto  = Math.round((total - base * n) * 100) / 100;

    var filas = document.querySelectorAll('#libreFilasBody .libre-monto');
    filas.forEach(function (inp, idx) {
        inp.value = (idx === filas.length - 1)
            ? (base + resto).toFixed(2)  // el último absorbe el centavo residual
            : base.toFixed(2);
    });
    window.libreActualizarTotal();
};


window.seleccionarRayo = function (opcion, ev) {
    var rayoLabels = {
        1: '⚡ 1 Pago único',
        2: '📅 2 Quincenas',
        4: '📆 4 Semanas'
    };

    document.querySelectorAll('.rayo-btn').forEach(function (b) {
        var op = parseInt(b.getAttribute('data-rayo'));
        var isSelected = op === opcion;
        b.classList.toggle('btn-primary', isSelected);
        b.classList.toggle('btn-outline-primary', !isSelected);
        b.innerHTML = isSelected
            ? '<i class="fa-solid fa-check me-1"></i>' + rayoLabels[op]
            : rayoLabels[op];
    });

    _semanasActual = opcion;
    var modos = { 1: 'unico', 2: 'quincenas', 4: 'semanal' };
    _rayoModo = modos[opcion];

    var labels = { 1: '1 pago único', 2: '2 quincenas', 4: '4 semanas' };
    document.getElementById('semanasValor').textContent = labels[opcion];

    var total = parseFloat(_ofertaActiva.total_a_pagar);
    var divisor = opcion === 2 ? 2 : opcion;
    var monto = total / divisor;
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
    var fin = new Date(fechaISO + 'T00:00:00');
    fin.setDate(fin.getDate() + 7);

    var fmt = function (d) {
        return String(d.getDate()).padStart(2, '0') + '/' +
            String(d.getMonth() + 1).padStart(2, '0') + '/' +
            d.getFullYear();
    };

    return '<span style="display:block;font-weight:600;">' + fmt(inicio) + '</span>' +
        '<span style="display:block;color:#888;font-size:0.82em;">' + fmt(fin) + '</span>';
}

function calcularFechasQuincenales(fechaAcuerdoISO) {
    var hoy = new Date(fechaAcuerdoISO + 'T00:00:00');
    var dia = hoy.getDate();
    var mes = hoy.getMonth();
    var anio = hoy.getFullYear();

    var ultimoDiaMes = function (m, a) {
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
        var mesSig = mes + 1 > 11 ? 0 : mes + 1;
        var anioSig = mes + 1 > 11 ? anio + 1 : anio;
        f2 = new Date(anioSig, mesSig, 15);
    }

    var fmt = function (d) {
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0');
    };

    return [fmt(f1), fmt(f2)];
}

function fmtPagoRealizado(pago) {
    if (!pago) return '<span style="color:#aaa;">—</span>';

    var fmt = function (v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };
    var fmtFecha = function (s) {
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
window.verTablaAmortizacion = function () {
    if (!_ofertaActiva || !_credito) return;

    // Limpiar error de descuento/adicional de una edición previa
    var _errPrevio = document.getElementById('_errDescuentoAmort');
    if (_errPrevio) _errPrevio.style.display = 'none';
    var _btnG = document.getElementById('btnGuardar');
    if (_btnG) _btnG.style.display = 'inline-block';

    var o = _ofertaActiva;
    var total = parseFloat(o.total_a_pagar);
    var fmt = function (v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };
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

    // Limpiar bloque de resumen adicional (solo aplica en convenio activo)
    (function () {
        var elRA = document.getElementById('amortResumenAplicacion');
        if (elRA) { elRA.innerHTML = ''; elRA.style.display = 'none'; }
    })();

    // Inicializar bloque personalizado
    (function () {
        var ap  = document.getElementById('amortPersonalizar');
        var atb = document.getElementById('amortTotalBase');
        var atf = document.getElementById('amortTotalFinal');
        var ama = document.getElementById('amortMontoAdicional');
        if (!ap || !atb || !atf || !ama) return;
        atb.value = total.toFixed(2);
        atf.value = total.toFixed(2);
        ama.value = '';
        ap.style.display = 'block';
    })();

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
        var fechas = calcularFechasQuincenales(hoy);
        var mitad1 = parseFloat((total / 2).toFixed(2));
        var mitad2 = parseFloat((total - mitad1).toFixed(2)); // absorbe el centavo si hay

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
        var añadirDias = function (fecha, dias) {
            var d = new Date(fecha + 'T00:00:00');
            d.setDate(d.getDate() + dias);
            return d.toISOString().split('T')[0];
        };

        var saldo = total;
        var semanal = parseFloat((total / 4).toFixed(2));

        for (var s = 1; s <= 4; s++) {
            var fechaPago = añadirDias(hoy, (s - 1) * 7 + 8);
            var capital = (s < 4) ? semanal : parseFloat(saldo.toFixed(2));
            saldo = parseFloat((saldo - capital).toFixed(2));
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
        var añadirDias = function (fecha, dias) {
            var d = new Date(fecha + 'T00:00:00');
            d.setDate(d.getDate() + dias);
            return d.toISOString().split('T')[0];
        };

        var saldo = total;
        var semanal = parseFloat((total / _semanasActual).toFixed(2));

        for (var s = 1; s <= _semanasActual; s++) {
            var fechaPago = añadirDias(hoy, (s - 1) * 7 + 8);
            var capital = (s < _semanasActual) ? semanal : parseFloat(saldo.toFixed(2));
            saldo = parseFloat((saldo - capital).toFixed(2));
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
    bindBotonesConciliacionManual();
    document.getElementById('amortSection').style.display = 'block';
    document.getElementById('amortSection').scrollIntoView({ behavior: 'smooth' });
};

// ══════════════════════════════════════════════════════
//  HELPERS DE VALIDACIÓN DE MONTO
// ══════════════════════════════════════════════════════

/**
 * Marca un input visualmente en rojo durante ~1.4 s.
 */
function _flashInvalid(el) {
    el.style.transition = 'background 0.15s, border-color 0.15s';
    el.style.borderColor = '#ef4444';
    el.style.background  = '#fff1f2';
    el.classList.add('is-invalid');
    setTimeout(function () {
        el.style.borderColor = '';
        el.style.background  = '';
        el.classList.remove('is-invalid');
    }, 1400);
}

/**
 * Muestra un toast no bloqueante cuando el monto capturado supera la deuda.
 * Usa una bandera por "contexto" para no repetir el toast en cada tecla.
 */
var _alertaExcedeBandera = {};
function _alertarExcedeDeuda(contexto, totalCapturado, deuda) {
    var fmt = function (v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    var excede = totalCapturado > deuda + 0.009; // tolerancia de centavos
    if (excede && !_alertaExcedeBandera[contexto]) {
        _alertaExcedeBandera[contexto] = true;
        var excedente = Math.round((totalCapturado - deuda) * 100) / 100;
        Swal.fire({
            icon: 'info',
            title: 'Monto mayor a la deuda',
            html: 'El excedente de <strong>' + fmt(excedente) + '</strong> se registrará como monto adicional.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3b82f6',
        });
    }
    if (!excede) {
        _alertaExcedeBandera[contexto] = false; // reset si vuelve a estar por debajo
    }
}

/**
 * Valida un input numérico de monto.
 * Borra el campo y lo flashea en rojo si:
 *   - contiene notación científica (e / E)
 *   - no es un número finito
 *   - es negativo
 *   - supera maxValido (>0)
 * Retorna true si el valor es aceptable, false si fue rechazado.
 */
function _sanitizarMonto(inputEl, maxValido) {
    if (!inputEl) return false;
    var raw = inputEl.value.trim();
    if (!raw) return false;

    // 1. Notación científica (e/E)
    if (/e/i.test(raw)) {
        inputEl.value = '';
        _flashInvalid(inputEl);
        return false;
    }

    // 2. Demasiados dígitos — se respeta data-maxintdigits en el elemento (solo parte entera)
    var _maxIntDigits = parseInt(inputEl.dataset.maxintdigits) || 12;
    var _intPart = raw.split('.')[0].replace(/[^0-9]/g, '');
    if (_intPart.length > _maxIntDigits) {
        inputEl.value = '';
        _flashInvalid(inputEl);
        return false;
    }

    // 3. Ceros de relleno: "00", "000", "0001", etc. — pero sí permitir "0" y "0.XX"
    if (/^0\d/.test(raw)) {
        inputEl.value = '';
        _flashInvalid(inputEl);
        return false;
    }

    var v = parseFloat(raw);

    // 4. No finito, NaN, negativo, o supera el máximo permitido
    if (!isFinite(v) || isNaN(v) || v < 0 || (maxValido > 0 && v > maxValido)) {
        inputEl.value = '';
        _flashInvalid(inputEl);
        return false;
    }

    return true;
}

// ══════════════════════════════════════════════════════
//  AJUSTE PERSONALIZADO DE TABLA (interfaz principal)
// ══════════════════════════════════════════════════════
window.amortRecalcularTotal = function () {
    if (!_ofertaActiva) return;
    var base = parseFloat(document.getElementById('amortTotalBase').value) || 0;
    var elAdic = document.getElementById('amortMontoAdicional');
    var _maxAdic = parseFloat(_ofertaActiva.monto_base) || base;
    if (!_sanitizarMonto(elAdic, _maxAdic)) return;
    var adic = parseFloat(elAdic.value) || 0;
    if (adic > 10000) {
        elAdic.value = '10000';
        adic = 10000;
        _flashInvalid(elAdic);
        var _errAdicAmort = document.getElementById('_errDescuentoAmort');
        if (!_errAdicAmort) {
            _errAdicAmort = document.createElement('div');
            _errAdicAmort.id = '_errDescuentoAmort';
            var _apParentA = document.getElementById('amortPersonalizar');
            if (_apParentA) _apParentA.insertAdjacentElement('afterend', _errAdicAmort);
        }
        _errAdicAmort.className = 'alert alert-danger mt-2 mb-0';
        _errAdicAmort.innerHTML = '<i class="fas fa-ban me-2"></i>El monto adicional no puede exceder <strong>$10,000</strong>. Se ha ajustado al máximo permitido.';
        _errAdicAmort.style.display = 'block';
        setTimeout(function () { if (_errAdicAmort) _errAdicAmort.style.display = 'none'; }, 3000);
    }
    var totalFinal = Math.round((base + adic) * 100) / 100;
    document.getElementById('amortTotalFinal').value = totalFinal.toFixed(2);
    _amortRegenerarTabla(totalFinal, adic);
};

window.amortTotalFinalChanged = function () {
    if (!_ofertaActiva) return;
    var elTF = document.getElementById('amortTotalFinal');
    var _maxTotal = parseFloat(_ofertaActiva.monto_base) || 0;
    if (!_sanitizarMonto(elTF, _maxTotal)) return;
    var totalFinal = parseFloat(elTF.value) || 0;
    if (totalFinal <= 0) return;
    // Comparar contra el total del convenio (con descuento), no contra la deuda original
    var totalConvenio = parseFloat(document.getElementById('amortTotalBase').value) || parseFloat(_ofertaActiva.total_a_pagar);
    var adic = totalFinal > totalConvenio
        ? Math.round((totalFinal - totalConvenio) * 100) / 100
        : 0;
    if (adic > 10000) {
        adic = 10000;
        totalFinal = Math.round((totalConvenio + adic) * 100) / 100;
        elTF.value = totalFinal.toFixed(2);
        _flashInvalid(elTF);
        var _errAdicTF = document.getElementById('_errDescuentoAmort');
        if (!_errAdicTF) {
            _errAdicTF = document.createElement('div');
            _errAdicTF.id = '_errDescuentoAmort';
            var _apParentTF = document.getElementById('amortPersonalizar');
            if (_apParentTF) _apParentTF.insertAdjacentElement('afterend', _errAdicTF);
        }
        _errAdicTF.className = 'alert alert-danger mt-2 mb-0';
        _errAdicTF.innerHTML = '<i class="fas fa-ban me-2"></i>El monto adicional no puede exceder <strong>$10,000</strong>. Se ha ajustado al máximo permitido.';
        _errAdicTF.style.display = 'block';
        setTimeout(function () { if (_errAdicTF) _errAdicTF.style.display = 'none'; }, 3000);
    }
    _alertarExcedeDeuda('amort', totalFinal, totalConvenio);
    document.getElementById('amortMontoAdicional').value = adic > 0 ? adic.toFixed(2) : '';
    _amortRegenerarTabla(totalFinal, adic);
};

function _amortRegenerarTabla(totalFinal, adicional) {
    if (!_ofertaActiva) return;
    var o = _ofertaActiva;
    var hoy = new Date().toISOString().split('T')[0];
    var esLiquidacionRayo = o.id_producto === 4;
    var fmt = function (v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    var montoBase       = parseFloat(o.monto_base);          // deuda original (para mostrar en tarjeta)
    var totalConvenio   = parseFloat(document.getElementById('amortTotalBase').value) || parseFloat(o.total_a_pagar);
    var baseEfectiva    = totalFinal > totalConvenio ? totalConvenio : totalFinal;
    var descuentoNuevo  = Math.round((montoBase - baseEfectiva) * 100) / 100;
    var pctNuevo        = montoBase > 0 ? Math.round((descuentoNuevo / montoBase) * 10000) / 100 : 0;

    // ── Descuento máximo 45% ──────────────────────────────────────────────
    var _errDescAmort = document.getElementById('_errDescuentoAmort');
    if (pctNuevo > 45) {
        if (!_errDescAmort) {
            _errDescAmort = document.createElement('div');
            _errDescAmort.id = '_errDescuentoAmort';
            var _apParent = document.getElementById('amortPersonalizar');
            if (_apParent) _apParent.insertAdjacentElement('afterend', _errDescAmort);
        }
        _errDescAmort.className = 'alert alert-danger mt-2 mb-0';
        _errDescAmort.innerHTML = '<i class="fas fa-ban me-2"></i>El descuento calculado <strong>' + pctNuevo.toFixed(2) + '%</strong> supera el máximo permitido del <strong>45%</strong>. El total mínimo es <strong>$' +
            (montoBase * 0.55).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</strong>.';
        _errDescAmort.style.display = 'block';
        document.getElementById('btnGuardar').style.display = 'none';
        document.getElementById('tablaAmortBody').innerHTML = '';
        return;
    }
    if (_errDescAmort) _errDescAmort.style.display = 'none';
    document.getElementById('btnGuardar').style.display = 'inline-block';
    // ─────────────────────────────────────────────────────────────────────

    var labelPago, montoPago;
    if (esLiquidacionRayo) {
        if (_rayoModo === 'unico')          { labelPago = 'Pago Único';    montoPago = totalFinal; }
        else if (_rayoModo === 'quincenas') { labelPago = 'Por Quincena'; montoPago = parseFloat((totalFinal / 2).toFixed(2)); }
        else                               { labelPago = 'Por Semana';   montoPago = parseFloat((totalFinal / 4).toFixed(2)); }
    } else {
        labelPago = 'Pago Semanal';
        montoPago = _semanasActual > 0 ? parseFloat((totalFinal / _semanasActual).toFixed(2)) : 0;
    }

    document.getElementById('amortResumenCards').innerHTML =
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Deuda Original</div><div class="fw-bold text-primary fs-5">' + fmt(montoBase) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Descuento (' + pctNuevo.toFixed(0) + '%)</div><div class="fw-bold text-danger fs-5">-' + fmt(descuentoNuevo) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Total a Pagar</div><div class="fw-bold text-success fs-5">' + fmt(totalFinal) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">' + labelPago + '</div><div class="fw-bold text-warning fs-5">' + fmt(montoPago) + '</div></div></div>';

    var elRA = document.getElementById('amortResumenAplicacion');
    if (elRA) {
        if (adicional > 0) {
            elRA.innerHTML =
                '<div class="p-3 border rounded" style="background:#fff8e1;border-color:#fbbf24 !important;">' +
                '<div class="fw-semibold mb-2" style="color:#b45309;font-size:0.85rem;">' +
                '<i class="fas fa-info-circle me-1"></i>Resumen de Aplicación — el total incluye un monto adicional' +
                '</div><div class="row text-center g-2">' +
                '<div class="col-4"><div class="border rounded p-2 bg-white">' +
                '<div class="small text-muted">Adeudo Convenio</div>' +
                '<div class="fw-bold text-primary">' + fmt(baseEfectiva) + '</div></div></div>' +
                '<div class="col-4"><div class="border rounded p-2 bg-white">' +
                '<div class="small text-muted">Adicional Calculado</div>' +
                '<div class="fw-bold text-warning">+' + fmt(adicional) + '</div></div></div>' +
                '<div class="col-4"><div class="border rounded p-2 bg-white">' +
                '<div class="small text-muted">Total a Cobrar</div>' +
                '<div class="fw-bold text-success">' + fmt(totalFinal) + '</div></div></div>' +
                '</div></div>';
            elRA.style.display = 'block';
        } else {
            elRA.innerHTML = ''; elRA.style.display = 'none';
        }
    }

    var añadirDias = function (fecha, dias) {
        var d = new Date(fecha + 'T00:00:00'); d.setDate(d.getDate() + dias); return d.toISOString().split('T')[0];
    };
    var filasHtml = '';

    if (esLiquidacionRayo && _rayoModo === 'unico') {
        filasHtml =
            '<tr><td><strong>Pago único</strong></td>' +
            '<td>' + fmtFechaRango(hoy) + '</td>' +
            '<td><span style="font-weight:600;">' + fmt(totalFinal) + '</span></td>' +
            '<td>' + fmt(totalFinal) + '</td><td>' + fmt(0) + '</td>' +
            '<td>' + fmtPagoRealizado(null) + '</td>' +
            '<td><span class="badge bg-warning text-dark">Pendiente</span></td></tr>';

    } else if (esLiquidacionRayo && _rayoModo === 'quincenas') {
        var fechas = calcularFechasQuincenales(hoy);
        var mitad1 = parseFloat((totalFinal / 2).toFixed(2));
        var mitad2 = parseFloat((totalFinal - mitad1).toFixed(2));
        filasHtml =
            '<tr><td><strong>Quincena 1</strong></td>' +
            '<td><span style="font-weight:600;">' + fmtFecha(fechas[0]) + '</span></td>' +
            '<td><span style="font-weight:600;">' + fmt(mitad1) + '</span></td>' +
            '<td>' + fmt(mitad1) + '</td><td>' + fmt(mitad2) + '</td>' +
            '<td>' + fmtPagoRealizado(null) + '</td>' +
            '<td><span class="badge bg-warning text-dark">Pendiente</span></td></tr>' +
            '<tr><td><strong>Quincena 2</strong></td>' +
            '<td><span style="font-weight:600;">' + fmtFecha(fechas[1]) + '</span></td>' +
            '<td><span style="font-weight:600;">' + fmt(mitad2) + '</span></td>' +
            '<td>' + fmt(mitad2) + '</td><td>' + fmt(0) + '</td>' +
            '<td>' + fmtPagoRealizado(null) + '</td>' +
            '<td><span class="badge bg-warning text-dark">Pendiente</span></td></tr>';

    } else if (esLiquidacionRayo && _rayoModo === 'semanal') {
        var _saldoR4 = totalFinal;
        var _sem4 = parseFloat((totalFinal / 4).toFixed(2));
        for (var s = 1; s <= 4; s++) {
            var fpR4 = añadirDias(hoy, (s - 1) * 7 + 8);
            var capR4 = (s < 4) ? _sem4 : parseFloat(_saldoR4.toFixed(2));
            _saldoR4 = parseFloat((_saldoR4 - capR4).toFixed(2));
            if (_saldoR4 < 0) _saldoR4 = 0;
            filasHtml +=
                '<tr><td><strong>Semana ' + s + '</strong></td>' +
                '<td>' + fmtFechaRango(fpR4) + '</td>' +
                '<td><span style="font-weight:600;">' + fmt(_sem4) + '</span></td>' +
                '<td>' + fmt(capR4) + '</td><td>' + fmt(_saldoR4) + '</td>' +
                '<td>' + fmtPagoRealizado(null) + '</td>' +
                '<td><span class="badge bg-warning text-dark">Pendiente</span></td></tr>';
        }

    } else {
        var _semN = montoPago;
        var _saldoN = totalFinal;
        var _residuoN = _semN > 0 ? Math.round((totalFinal - Math.floor(totalFinal / _semN) * _semN) * 100) / 100 : 0;
        for (var s = 1; s <= _semanasActual; s++) {
            var fpN = añadirDias(hoy, (s - 1) * 7 + 8);
            var esUltN = s === _semanasActual;
            var capN = (esUltN && _residuoN >= 1.00) ? parseFloat(_saldoN.toFixed(2)) : _semN;
            _saldoN = Math.round((_saldoN - capN) * 100) / 100;
            if (_saldoN < 0) _saldoN = 0;
            filasHtml +=
                '<tr><td>Semana ' + s + '</td>' +
                '<td>' + fmtFechaRango(fpN) + '</td>' +
                '<td><span style="display:block;font-weight:600;">' + fmt(_semN) + '</span>' +
                '<span style="display:block;font-size:0.82em;color:#888;">' + fmt(capN) + '</span></td>' +
                '<td>' + fmt(_saldoN) + '</td>' +
                '<td>' + fmtPagoRealizado(null) + '</td>' +
                '<td><span class="badge bg-warning text-dark">Pendiente</span></td></tr>';
        }
    }

    document.getElementById('tablaAmortBody').innerHTML = filasHtml;
}

// ══════════════════════════════════════════════════════
//  GUARDAR CONVENIO
// ══════════════════════════════════════════════════════
window.guardarConvenio = function () {
    if (!_ofertaActiva || !_credito) return;

    var hoy     = new Date().toISOString().split('T')[0];
    var esLibre = _ofertaActiva.tipo_calendario === 'libre';

    // ── MODO LIBRE ────────────────────────────────────────────
    if (esLibre) {
        var filasFecha = document.querySelectorAll('#libreFilasBody .libre-fecha');
        var filasMonto = document.querySelectorAll('#libreFilasBody .libre-monto');
        var pagos = [];
        var fechasVacias = false;

        filasFecha.forEach(function (inp, idx) {
            var fecha = inp.value;
            var monto = parseFloat(filasMonto[idx].value) || 0;
            if (!fecha) fechasVacias = true;
            pagos.push({ fecha: fecha, monto: monto });
        });

        if (fechasVacias) {
            Swal.fire('Faltan fechas', 'Completa todas las fechas de pago antes de guardar.', 'warning');
            return;
        }

        var totalLibre      = parseFloat(_ofertaActiva.total_a_pagar);
        var montoBase       = parseFloat(_ofertaActiva.monto_base);
        var descuentoMonto  = Math.round((montoBase - totalLibre) * 100) / 100;
        var pctDescuento    = montoBase > 0 ? Math.round((descuentoMonto / montoBase) * 10000) / 100 : 0;
        var numPagos        = pagos.length;

        if (pctDescuento > 45) {
            Swal.fire('Descuento no permitido', 'El descuento calculado (' + pctDescuento.toFixed(2) + '%) supera el máximo permitido del 45%.', 'error');
            return;
        }

        // Resumen para confirmación
        var resumenHtml = pagos.map(function (p, i) {
            return '<tr><td style="padding:2px 8px;">' + (i + 1) + '</td>' +
                '<td style="padding:2px 8px;">' + p.fecha + '</td>' +
                '<td style="padding:2px 8px;text-align:right;">$' +
                parseFloat(p.monto).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</td></tr>';
        }).join('');

        Swal.fire({
            title: '¿Confirmar convenio?',
            html: '<strong>' + _ofertaActiva.nombre + '</strong> — ' + numPagos + ' pagos<br>' +
                '<span class="text-muted" style="font-size:.9rem;">Total: <strong>$' +
                totalLibre.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</strong></span><br><br>' +
                '<table style="width:100%;font-size:.85rem;border-collapse:collapse;">' +
                '<thead><tr style="background:#f1f5f9;"><th style="padding:2px 8px;">#</th>' +
                '<th style="padding:2px 8px;">Fecha</th><th style="padding:2px 8px;">Monto</th></tr></thead>' +
                '<tbody>' + resumenHtml + '</tbody></table>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#764ba2',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var _convFileL = document.getElementById('convPdfAdjunto');
            var _convHasFileL = _convFileL && _convFileL.files && _convFileL.files[0];

            var _payloadLibre = {
                id_credito:                     _credito.Id_credito,
                id_producto_convenio:           _ofertaActiva.id_producto,
                id_producto_convenio_detalle:   _ofertaActiva.id_detalle,
                nombre_cliente:                 _credito.Nombre_cliente,
                bucket_morosidad_real:          _credito.Bucket_Morosidad_Real,
                dias_mora:                      _credito.Dias_mora,
                avance_pago_plazo:              _credito.Avance_Pago_Plazo,
                adeudo_total_original:          _credito.Adeudo_total,
                porcentaje_descuento:           pctDescuento,
                descuento_monto:                descuentoMonto,
                total_a_pagar:                  totalLibre,
                monto_adicional:                '',
                pago_inicial_monto:             '',
                numero_semanas:                 numPagos,
                pago_semanal:                   parseFloat((totalLibre / numPagos).toFixed(2)),
                fecha_acuerdo:                  hoy,
                tipo_calendario:                'libre',
                fechas_pagos:                   JSON.stringify(pagos),
            };

            var _reqLibre = {
                endpoint: '/convenios/guardarConvenio',
                method: 'POST',
                onSuccess: function (resp) {
                    if (resp.success) {
                        Swal.fire({ title: '¡Guardado!', text: 'El convenio fue registrado exitosamente.', icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function () { seleccionarCredito(_credito.Id_credito); });
                    } else {
                        Swal.fire('Error', resp.mensaje || 'No se pudo guardar.', 'error');
                    }
                },
                onError: function () { Swal.fire('Error', 'Error de conexión.', 'error'); }
            };

            if (_convHasFileL) {
                var formData = new FormData();
                Object.keys(_payloadLibre).forEach(function (k) {
                    if (_payloadLibre[k] !== null && _payloadLibre[k] !== undefined) {
                        formData.append(k, _payloadLibre[k]);
                    }
                });
                formData.append('pdf_adjunto', _convFileL.files[0]);
                _reqLibre.data = formData;
                _reqLibre.contentType = false;
                _reqLibre.processData = false;
            } else {
                _reqLibre.data = _payloadLibre;
            }

            http.request(_reqLibre);
        });
        return;
    }

    // ── MODO SEMANAL (lógica original) ────────────────────────
    var _apEl = document.getElementById('amortPersonalizar');
    var _usaPersonalizado = _apEl && _apEl.style.display !== 'none';
    var total = _usaPersonalizado
        ? (parseFloat(document.getElementById('amortTotalFinal').value) || parseFloat(_ofertaActiva.total_a_pagar))
        : parseFloat(_ofertaActiva.total_a_pagar);
    var _adicPersonalizado = _usaPersonalizado
        ? (parseFloat(document.getElementById('amortMontoAdicional').value) || 0)
        : 0;

    var _esRayoG = _ofertaActiva.id_producto === 4;
    var _numSemanasG = _esRayoG
        ? (_rayoModo === 'unico' ? 1 : _rayoModo === 'quincenas' ? 2 : 4)
        : _semanasActual;
    var semanal = _numSemanasG > 0 ? parseFloat((total / _numSemanasG).toFixed(2)) : 0;

    var _montoBaseG      = parseFloat(_ofertaActiva.monto_base);
    var _totalConvenioG  = parseFloat(document.getElementById('amortTotalBase')?.value) || parseFloat(_ofertaActiva.total_a_pagar);
    var _baseEfectivaG   = total > _totalConvenioG ? _totalConvenioG : total;
    var _descuentoMontoG = Math.round((_montoBaseG - _baseEfectivaG) * 100) / 100;
    var _pctDescuentoG   = _montoBaseG > 0 ? Math.round((_descuentoMontoG / _montoBaseG) * 10000) / 100 : 0;

    if (_pctDescuentoG > 45) {
        Swal.fire('Descuento no permitido', 'El descuento calculado (' + _pctDescuentoG.toFixed(2) + '%) supera el máximo permitido del 45%.', 'error');
        return;
    }

    Swal.fire({
        title: '¿Confirmar convenio?',
        html: '<strong>' + _ofertaActiva.nombre + '</strong> — ' +
            _numSemanasG + ' semanas — $' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ' / semana<br><br>' +
            '<span class="text-muted" style="font-size:.9rem;">Cliente: <strong>' + _credito.Nombre_cliente + '</strong></span><br>' +
            '<span class="text-muted" style="font-size:.9rem;">Total a pagar: <strong>$' + total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</strong></span>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#764ba2',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var _convFile = document.getElementById('convPdfAdjunto');
        var _convHasFile = _convFile && _convFile.files && _convFile.files[0];

        var _payloadData = {
            id_credito: _credito.Id_credito,
            id_producto_convenio: _ofertaActiva.id_producto,
            id_producto_convenio_detalle: _ofertaActiva.id_detalle,
            nombre_cliente: _credito.Nombre_cliente,
            bucket_morosidad_real: _credito.Bucket_Morosidad_Real,
            dias_mora: _credito.Dias_mora,
            avance_pago_plazo: _credito.Avance_Pago_Plazo,
            adeudo_total_original: _credito.Adeudo_total,
            porcentaje_descuento: _pctDescuentoG,
            descuento_monto: _descuentoMontoG,
            total_a_pagar: total,
            monto_adicional: _adicPersonalizado > 0 ? _adicPersonalizado.toFixed(2) : '',
            pago_inicial_monto: _ofertaActiva.pago_inicial_monto || '',
            numero_semanas: _numSemanasG,
            pago_semanal: semanal,
            fecha_acuerdo: hoy,
        };

        var _reqOpts = {
            endpoint: '/convenios/guardarConvenio',
            method: 'POST',
            onSuccess: function (resp) {
                if (resp.success) {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: 'El convenio fue registrado exitosamente.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        seleccionarCredito(_credito.Id_credito);
                    });
                } else {
                    Swal.fire('Error', resp.mensaje || 'No se pudo guardar.', 'error');
                }
            },
            onError: function () {
                Swal.fire('Error', 'Error de conexión.', 'error');
            }
        };

        if (_convHasFile) {
            var formData = new FormData();
            Object.keys(_payloadData).forEach(function (k) {
                if (_payloadData[k] !== null && _payloadData[k] !== undefined) {
                    formData.append(k, _payloadData[k]);
                }
            });
            formData.append('pdf_adjunto', _convFile.files[0]);
            _reqOpts.data = formData;
            _reqOpts.contentType = false;
            _reqOpts.processData = false;
        } else {
            _reqOpts.data = _payloadData;
        }

        http.request(_reqOpts);
    });
};

// ══════════════════════════════════════════════════════
//  CANCELAR CONVENIO
// ══════════════════════════════════════════════════════
window.cancelarConvenio = function () {
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
    }).then(function (result) {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Cancelando...',
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });

        http.request({
            endpoint: '/convenios/cancelarConvenio',
            method: 'POST',
            data: { id_convenio: window._idConvenioActivo },
            onSuccess: function (resp) {
                if (!resp.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo cancelar.', confirmButtonColor: '#764ba2' });
                    return;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Convenio cancelado',
                    text: 'El convenio fue cancelado correctamente.',
                    confirmButtonColor: '#764ba2',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function () {
                    location.reload(); // AUTO-REFRESH
                });
            },
            onError: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cancelar el convenio. Intenta de nuevo.', confirmButtonColor: '#764ba2' });
            }
        });
    });
};

// ══════════════════════════════════════════════════════
//  REPINTAR TABLA TRAS CANCELACIÓN
// ══════════════════════════════════════════════════════
function _repintarTablaCancelada(semanaCancelacion) {
    var filas = document.querySelectorAll('#tablaAmortBody tr');
    filas.forEach(function (fila, idx) {
        var numSemana = idx + 1;
        var celdaEstatus = fila.querySelector('td:last-child');

        if (numSemana === semanaCancelacion) {
            fila.style.background = 'rgba(245,158,11,0.08)';
            celdaEstatus.innerHTML =
                '<span class="badge bg-warning text-dark">' +
                '<i class="fa-solid fa-ban me-1"></i>Cancelado aquí</span>';
        } else if (numSemana > semanaCancelacion) {
            fila.style.opacity = '0.4';
            fila.style.textDecoration = 'line-through';
            fila.style.color = '#ef4444';
            celdaEstatus.style.textDecoration = 'none';
            celdaEstatus.style.color = '';
            celdaEstatus.innerHTML =
                '<span class="badge bg-danger">No pagado</span>';
        }
    });
}

// ══════════════════════════════════════════════════════
//  DESCARGAR PDF
// ══════════════════════════════════════════════════════
window.descargarPdf = function () {
    if (!_credito) return;

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/convenios/descargarPdf';
    form.style.display = 'none';

    var inp = document.createElement('input');
    inp.name = 'id_credito';
    inp.value = _credito.Id_credito;
    form.appendChild(inp);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

// ══════════════════════════════════════════════════════
//  HISTORIAL DE CONVENIOS
// ══════════════════════════════════════════════════════
window.abrirHistorial = function () {
    if (!_credito) return;

    document.getElementById('historialLoading').style.display = 'block';
    document.getElementById('historialVacio').style.display = 'none';
    document.getElementById('historialTablaWrap').style.display = 'none';
    document.getElementById('modalHistorialCredito').textContent = 'Crédito #' + _credito.Id_credito;

    var modal = new bootstrap.Modal(document.getElementById('modalHistorial'));
    modal.show();

    http.request({
        endpoint: '/convenios/getHistorialConvenios',
        method: 'POST',
        data: { id_credito: _credito.Id_credito },
        onSuccess: function (resp) {
            document.getElementById('historialLoading').style.display = 'none';

            if (!resp.success || !resp.datos || resp.datos.length === 0) {
                document.getElementById('historialVacio').style.display = 'block';
                return;
            }

            var fmt = function (v) {
                return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            };
            var fmtFecha = function (s) {
                if (!s) return '—';
                var p = s.split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            };
            var badgeEstatus = function (e) {
                var labels = {
                    'activo': 'Activo',
                    'cancelado': 'Cancelado',
                    'completado': 'Completado',
                    'incumplimiento': 'Incumplimiento'
                };
                return '<span class="badge-estatus-' + e + '">' + (labels[e] || e) + '</span>';
            };
            var badgeEstatusPago = function (e) {
                var map = {
                    'pendiente': ['#6c757d', 'Pendiente'],
                    'pagado': ['#22c55e', 'Pagado'],
                    'vencido': ['#ef4444', 'Vencido'],
                    'cancelado': ['#9ca3af', 'Cancelado'],
                };
                var v = map[e] || ['#6c757d', e];
                return '<span style="background:' + v[0] + ';color:#fff;padding:2px 8px;border-radius:12px;font-size:.72rem;">' + v[1] + '</span>';
            };

            var filas = '';
            resp.datos.forEach(function (c, i) {
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

            document.getElementById('tablaHistorialBody').innerHTML = filas;
            document.getElementById('historialTablaWrap').style.display = 'block';
        },
        onError: function () {
            document.getElementById('historialLoading').style.display = 'none';
            document.getElementById('historialVacio').style.display = 'block';
        }
    });
};

// ────────────────────────────────────────────────
// ACCORDION — toggle amortización por convenio
// ────────────────────────────────────────────────

var _amortCache = {};

function toggleAmortizacion(idConvenio, fila) {
    var detalle = document.getElementById('detalle-' + idConvenio);
    var chevron = document.getElementById('chevron-' + idConvenio);
    var content = document.getElementById('amort-content-' + idConvenio);
    var abierto = !detalle.classList.contains('d-none');

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
        method: 'POST',
        data: { id_convenio: idConvenio },
        onSuccess: function (resp) {
            if (!resp.success || !resp.datos) {
                content.innerHTML = '<p class="text-danger p-3">Error al cargar amortización.</p>';
                return;
            }

            var fmt = function (v) {
                return '$' + parseFloat(v || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            };
            var fmtFecha = function (s) {
                if (!s) return '—';
                var p = s.split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            };
            var badgePago = function (e) {
                var map = {
                    'pendiente': ['#6c757d', 'Pendiente'],
                    'pagado': ['#22c55e', 'Pagado'],
                    'vencido': ['#ef4444', 'Vencido'],
                    'cancelado': ['#9ca3af', 'Cancelado'],
                };
                var v = map[e] || ['#6c757d', e];
                return '<span style="background:' + v[0] + ';color:#fff;padding:2px 8px;border-radius:12px;font-size:.72rem;">' + v[1] + '</span>';
            };

            var filas = resp.datos.map(function (row) {
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
        onError: function () {
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
var _migSemanas = 0;
var _migOfertas = [];  // ofertas del crédito activo en el modal

// ── Selector de base de cálculo (Capital / Interés / Total) ──
function _migMostrarBaseSelector() {
    var panel = document.getElementById('migBaseSelector');
    if (!panel || !_migCredito) return;

    var capital = parseFloat(_migCredito.Saldo_total_capital || 0);
    var total   = parseFloat(_migCredito.Adeudo_total || 0);
    var interes = Math.max(total - capital, 0);
    var fmt = function(v) { return '$' + v.toLocaleString('es-MX', {minimumFractionDigits:2}); };

    var montosEl = document.getElementById('migBaseMontos');
    if (montosEl) {
        montosEl.innerHTML = 'Capital: ' + fmt(capital) + ' &nbsp;|&nbsp; Interés: ' + fmt(interes) + ' &nbsp;|&nbsp; Total: ' + fmt(total);
    }

    panel.classList.remove('d-none');

    // Seleccionar "Total" por defecto
    var radTotal = document.getElementById('migBase_total');
    if (radTotal) radTotal.checked = true;

    // Vincular eventos
    var radios = document.querySelectorAll('input[name="migBaseTipo"]');
    for (var i = 0; i < radios.length; i++) {
        radios[i].removeEventListener('change', _migOnBaseChange);
        radios[i].addEventListener('change', _migOnBaseChange);
    }
}

function _migOcultarBaseSelector() {
    var panel = document.getElementById('migBaseSelector');
    if (panel) panel.classList.add('d-none');
}

function _migGetMontoBase(tipo) {
    if (!_migCredito) return 0;
    var capital = parseFloat(_migCredito.Saldo_total_capital || 0);
    var total   = parseFloat(_migCredito.Adeudo_total || 0);
    var interes = Math.max(total - capital, 0);

    if (tipo === 'saldo_total_capital') return capital;
    if (tipo === 'interes') return interes;
    return total; // adeudo_total
}

function _migGetBaseSeleccionada() {
    var checked = document.querySelector('input[name="migBaseTipo"]:checked');
    return checked ? checked.value : 'adeudo_total';
}

function _migOnBaseChange() {
    var tipo = _migGetBaseSeleccionada();
    var monto = _migGetMontoBase(tipo);
    var migAdeudoEl = document.getElementById('migAdeudo');
    if (migAdeudoEl) {
        migAdeudoEl.value = monto.toFixed(2);
    }
    // Quitar aviso previo del producto (ya no aplica si el usuario cambió la base)
    var avisoPrev = document.getElementById('migAdeudoAviso');
    if (avisoPrev) avisoPrev.remove();

    // Recalcular si existe migCalcular
    if (typeof migCalcular === 'function') {
        migCalcular();
    }
}



// ══════════════════════════════════════════════════════
//  REACTIVAR OFERTAS
// ══════════════════════════════════════════════════════
window.reactivarOfertasCredito = function () {
    if (!_credito) return;
    var idCredito = _credito.Id_credito;
    var nombre = _credito.Nombre_cliente || '';

    Swal.fire({
        title: 'Reactivar Ofertas',
        html:
            '<p>Crédito <strong>#' + idCredito + '</strong> — ' + nombre + '</p>' +
            '<p class="text-muted small mb-0">Se habilitarán todas las ofertas disponibles para este crédito, ' +
            'ignorando convenios finalizados o cancelados anteriores. ' +
            'El historial de convenios se conserva intacto.</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-rotate-right me-1"></i>Sí, reactivar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            actions: 'd-flex gap-2 justify-content-center',
            confirmButton: 'btn btn-success px-4',
            cancelButton: 'btn btn-outline-secondary px-4'
        }
    }).then(function (res) {
        if (!res.isConfirmed) return;

        var fd = new FormData();
        fd.append('id_credito', idCredito);

        Swal.fire({
            title: 'Reactivando...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); }
        });

        fetch('/convenios/reactivarOfertas', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Ofertas reactivadas!',
                        text: data.mensaje || 'Ahora puedes generar un nuevo convenio.',
                        confirmButtonText: 'Continuar'
                    }).then(function () {
                        // Volver a cargar el crédito para mostrar las ofertas frescas
                        seleccionarCredito(idCredito);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje || 'Ocurrió un error.' });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo contactar al servidor.' });
            });
    });
};

window.abrirModalMigracion = function () {
    // Resetear variables
    _migCredito = null;
    _migDetalle = null;
    _globoCredito = null;

    // ── LIMPIAR PESTAÑA "CONVENIO NORMAL" ──────────────────────────
    var migIdCredito = document.getElementById('migIdCredito');
    if (migIdCredito) {
        var idPrincipal = (_credito && _credito.Id_credito)
            ? String(_credito.Id_credito)
            : ((document.getElementById('inputBusqueda') || {}).value || '');
        migIdCredito.value = idPrincipal;
    }

    // Ocultar buscador manual cuando ya hay un crédito cargado en la vista principal
    var migBuscadorWrap = document.getElementById('migBuscadorWrap');
    if (migBuscadorWrap) {
        migBuscadorWrap.style.display = (_credito && _credito.Id_credito) ? 'none' : '';
    }

    var migInfoCliente = document.getElementById('migInfoCliente');
    if (migInfoCliente) {
        migInfoCliente.classList.add('d-none');
        migInfoCliente.innerHTML = '';
        migInfoCliente.className = 'alert alert-info d-none';
    }

    window.migResetearFormulario();

    // ── LIMPIAR PESTAÑA "CONVENIO PAGO GLOBO" ──────────────────────
    var globoStep2 = document.getElementById('globoStep2');
    if (globoStep2) globoStep2.classList.add('d-none');

    var globoIdCredito = document.getElementById('globoIdCredito');
    if (globoIdCredito) globoIdCredito.value = '';

    var globoInfoCliente = document.getElementById('globoInfoCliente');
    if (globoInfoCliente) {
        globoInfoCliente.classList.add('d-none');
        globoInfoCliente.innerHTML = '';
        globoInfoCliente.className = 'alert alert-info d-none';
    }

    var globoBtnGuardar = document.getElementById('globoBtnGuardar');
    if (globoBtnGuardar) globoBtnGuardar.style.display = 'none';

    // Resetear valores de los inputs del globo
    var globoPorcentajeDescuento = document.getElementById('globoPorcentajeDescuento');
    if (globoPorcentajeDescuento) globoPorcentajeDescuento.value = '0';

    var globoPagosIgualesCant = document.getElementById('globoPagosIgualesCant');
    if (globoPagosIgualesCant) globoPagosIgualesCant.value = '4';

    var globoPagosIgualesMonto = document.getElementById('globoPagosIgualesMonto');
    if (globoPagosIgualesMonto) globoPagosIgualesMonto.value = '';

    var globoPagoGloboMonto = document.getElementById('globoPagoGloboMonto');
    if (globoPagoGloboMonto) globoPagoGloboMonto.value = '';

    var globoFrecuencia = document.getElementById('globoFrecuencia');
    if (globoFrecuencia) globoFrecuencia.value = 'semanal';

    var globoFechaPrimerPago = document.getElementById('globoFechaPrimerPago');
    if (globoFechaPrimerPago) globoFechaPrimerPago.value = '';

    var globoPdfAdjunto = document.getElementById('globoPdfAdjunto');
    if (globoPdfAdjunto) globoPdfAdjunto.value = '';

    var convPdfAdjunto = document.getElementById('convPdfAdjunto');
    if (convPdfAdjunto) { convPdfAdjunto.value = ''; }
    var convPdfPreview = document.getElementById('convPdfPreview');
    if (convPdfPreview) { convPdfPreview.innerHTML = ''; }

    // Resetear preview de pagos
    var globoPagosPreviewBody = document.getElementById('globoPagosPreviewBody');
    if (globoPagosPreviewBody) {
        globoPagosPreviewBody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">Ingresa los montos para ver el preview</td></tr>';
    }

    // Resetear resumen de cálculo
    var elementosReset = [
        'globoSumaIguales', 'globoSumaGlobo', 'globoTotalPagar',
        'globoDescuento', 'globoPorcentajeDescuentoMostrar', 'globoDetalleIguales'
    ];
    elementosReset.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            if (id === 'globoDetalleIguales') {
                el.innerHTML = '0 × $0.00';
            } else if (id === 'globoPorcentajeDescuentoMostrar') {
                el.innerHTML = '0%';
            } else {
                el.innerHTML = '$0.00';
            }
        }
    });

    // Resetear total con descuento
    var globoTotalConDescuento = document.getElementById('globoTotalConDescuento');
    if (globoTotalConDescuento) globoTotalConDescuento.value = '$0.00';

    // Ocultar mensaje de error
    var globoErrorMontos = document.getElementById('globoErrorMontos');
    if (globoErrorMontos) globoErrorMontos.style.display = 'none';

    // Limpiar preview de PDF
    var globoPdfPreview = document.getElementById('globoPdfPreview');
    if (globoPdfPreview) globoPdfPreview.innerHTML = '';

    // Resetear datos del cliente
    var globoNombreCliente = document.getElementById('globoNombreCliente');
    if (globoNombreCliente) globoNombreCliente.innerHTML = '-';

    var globoAdeudoTotal = document.getElementById('globoAdeudoTotal');
    if (globoAdeudoTotal) globoAdeudoTotal.innerHTML = '$0.00';

    var globoBucket = document.getElementById('globoBucket');
    if (globoBucket) globoBucket.innerHTML = '-';

    // ── CARGAR PRODUCTOS (solo si existe la función) ─────────────────
    if (typeof migCargarProductos === 'function') {
        migCargarProductos();
    }

    // ── ABRIR MODAL ─────────────────────────────────────────────────
    var modalElement = document.getElementById('modalMigracion');
    if (modalElement) {
        var modal = new bootstrap.Modal(modalElement);
        modal.show();

        // Auto-buscar si ya hay un crédito cargado en la vista principal
        if (_credito && _credito.Id_credito) {
            setTimeout(function () {
                window.migBuscarCredito();
            }, 350);
        }
    } else {
        console.error('Modal element not found');
    }
};

function migCargarProductos() {
    http.request({
        endpoint: '/convenios/getProductosConvenio',
        method: 'POST',
        data: {},
        onSuccess: function (resp) {
            console.log('productos resp:', resp);
            if (!resp.success) return;
            var sel = document.getElementById('migProducto');
            sel.innerHTML = '<option value="">Selecciona...</option>';
            resp.datos.forEach(function (p) {
                p.detalles.forEach(function (d) {
                    sel.innerHTML += '<option value="' + d.id + '"'
                        + ' data-id-producto="' + p.id + '"'
                        + ' data-porcentaje="' + d.porcentaje_descuento + '"'
                        + ' data-variable="' + d.porcentaje_variable + '">'
                        + p.nombre + ' — ' + d.porcentaje_descuento + '%'
                        + (d.porcentaje_variable ? ' (variable)' : '')
                        + '</option>';
                });
            });
        },
        onError: function () { }
    });
}

// ════════════════════════════════════════════════
// MIGRACIÓN - BUSCAR CRÉDITO (VERSIÓN ACTUALIZADA)
// ════════════════════════════════════════════════
window.migBuscarCredito = function () {
    var idCredito = parseInt(document.getElementById('migIdCredito').value);
    var info = document.getElementById('migInfoCliente');

    if (!idCredito || idCredito < 1) {
        Swal.fire('Campo requerido', 'Ingresa un ID de crédito válido.', 'warning');
        return;
    }

    // ── Reset completo de estado previo ───────────────────────────
    _migCredito = null;
    _migDetalle = null;
    _migSemanas = 0;
    _migOfertas = [];

    // Limpiar todos los campos del formulario (paso 2)
    window.migResetearFormulario();

    // Ocultar paso 2 mientras buscamos
    var migStep2 = document.getElementById('migStep2');
    if (migStep2) migStep2.classList.add('d-none');

    var migPreview = document.getElementById('migPreview');
    if (migPreview) migPreview.classList.add('d-none');

    // Limpiar y mostrar info
    info.classList.remove('d-none');
    info.className = 'alert alert-info';
    info.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Buscando crédito...';

    // ── Paso 1: Obtener datos del crédito ──────────────────────────
    http.request({
        endpoint: '/convenios/getOfertasCredito',
        method: 'POST',
        data: { id_credito: idCredito },
        onSuccess: function (respOfertas) {

            if (!respOfertas.success || !respOfertas.datos || !respOfertas.datos.credito) {
                info.className = 'alert alert-danger d-none';
                if (respOfertas.success && respOfertas.datos && respOfertas.datos.statusCredito === 'Saldado') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Crédito Saldado',
                        html: 'Este crédito ya se encuentra <strong>saldado</strong>.' +
                            (respOfertas.datos.fechaLiquidacion ? '<br>Fecha de liquidación: <strong>' + respOfertas.datos.fechaLiquidacion + '</strong>' : '') +
                            (respOfertas.datos.motivo ? '<br>Motivo: ' + respOfertas.datos.motivo : ''),
                        confirmButtonColor: '#22c55e'
                    });
                } else {
                    Swal.fire('Crédito no encontrado', respOfertas.mensaje || 'El crédito ingresado no existe o no es elegible.', 'error');
                }
                return;
            }

            var credito = respOfertas.datos.credito;

            // ── Guardar ofertas para uso en migProductoChange ─────────────
            _migOfertas = (respOfertas.datos.ofertas || []).filter(function (o) {
                return o.nombre !== 'Convenio Pago Mixto';
            });

            // ── Paso 2: Validar que el crédito esté en despacho ──────────
            var _errManejadoDesp = false;
            http.request({
                endpoint: '/convenios/validarDespacho',
                method: 'POST',
                data: { id_credito: idCredito },
                onSuccess: function (respDespacho) {

                    if (!respDespacho.success) {
                        _errManejadoDesp = true;
                        info.className = 'alert alert-danger d-none';
                        setTimeout(function () {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Crédito no disponible',
                                html:
                                    '<strong>' + credito.Nombre_cliente + '</strong> — Crédito #' + credito.Id_credito + '<br><br>' +
                                    'El crédito no se encuentra disponible para convenio.<br>' +
                                    '<span class="text-muted" style="font-size:.9rem;">Verifica que esté asignado a convenios. Si tienes más dudas, consulta con el administrador.</span>',
                                confirmButtonColor: '#764ba2',
                            });
                        }, 0);
                        return;
                    }

                    // ── Paso 3: Verificar que no tenga convenio activo ──────
                    http.request({
                        endpoint: '/convenios/getConvenioActivo',
                        method: 'POST',
                        data: { id_credito: idCredito },
                        onSuccess: function (respConvenio) {

                            if (respConvenio.success && respConvenio.datos &&
                                respConvenio.datos.estatus === 'activo') {
                                info.className = 'alert alert-warning d-none';
                                var migStep2Blq = document.getElementById('migStep2');
                                if (migStep2Blq) migStep2Blq.classList.add('d-none');
                                Swal.fire('Convenio activo', credito.Nombre_cliente + ' — Crédito #' + credito.Id_credito + ' ya tiene un convenio activo. No es posible registrar uno adicional.', 'warning');
                                return;
                            }

                            // ✅ Todo válido
                            _migCredito = credito;
                            _migMostrarBaseSelector();

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
                                '<span><i class="fas fa-dollar-sign me-1"></i>Adeudo S2: $' + adeudo + '</span>' +
                                '</small><br>' +
                                '<small class="text-success fw-semibold">✓ Crédito en despacho activo. Cumple condiciones para registrar un convenio.</small>' +
                                '</div>' +
                                '</div>';

                            // Asignar valores a los campos
                            var migAdeudo = document.getElementById('migAdeudo');
                            if (migAdeudo) migAdeudo.value = parseFloat(credito.Adeudo_total || 0).toFixed(2);

                            var migBucket = document.getElementById('migBucket');
                            if (migBucket) migBucket.value = credito.Bucket_Morosidad_Real || '';

                            // Mostrar el paso 2
                            var migStep2El = document.getElementById('migStep2');
                            if (migStep2El) migStep2El.classList.remove('d-none');
                        },
                        onError: function () {
                            // Si falla getConvenioActivo, habilitamos igualmente
                            _migCredito = credito;
                            _migMostrarBaseSelector();

                            info.className = 'alert alert-success';
                            info.innerHTML =
                                '<i class="fas fa-check-circle me-2"></i>' +
                                '<strong>' + credito.Nombre_cliente + '</strong> — Crédito #' + credito.Id_credito;

                            var migAdeudo = document.getElementById('migAdeudo');
                            if (migAdeudo) migAdeudo.value = parseFloat(credito.Adeudo_total || 0).toFixed(2);

                            var migBucket = document.getElementById('migBucket');
                            if (migBucket) migBucket.value = credito.Bucket_Morosidad_Real || '';

                            var migStep2El = document.getElementById('migStep2');
                            if (migStep2El) migStep2El.classList.remove('d-none');
                        }
                    });
                },
                onError: function () {
                    if (_errManejadoDesp) return;
                    info.className = 'alert alert-danger d-none';
                    setTimeout(function () {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Crédito no disponible',
                            html:
                                '<strong>' + credito.Nombre_cliente + '</strong> — Crédito #' + credito.Id_credito + '<br><br>' +
                                'El crédito no se encuentra disponible para convenio.<br>' +
                                '<span class="text-muted" style="font-size:.9rem;">Verifica que esté asignado a convenios. Si tienes más dudas, consulta con el administrador.</span>',
                            confirmButtonColor: '#764ba2',
                        });
                    }, 0);
                }
            });
        },
        onError: function () {
            if (_errManejadoMig) return;
            info.className = 'alert alert-danger d-none';
            Swal.fire('Error de conexión', 'No se pudo verificar el crédito. Intenta de nuevo.', 'error');
        }
    });
};

window.migProductoChange = function () {
    var sel = document.getElementById('migProducto');
    if (!sel) return;

    var opt = sel.options[sel.selectedIndex];
    if (!opt) return;

    var variable = opt.getAttribute('data-variable');
    var pct = opt.getAttribute('data-porcentaje');
    var nombre = opt.text || '';
    var esGlobo = nombre.indexOf('Convenio Pago Mixto') !== -1;

    var inputPct = document.getElementById('migPorcentaje');
    if (inputPct) {
        inputPct.value = pct || '';
        inputPct.readOnly = variable !== '1';
    }

    _migDetalle = {
        id_detalle: sel.value,
        id_producto: opt.getAttribute('data-id-producto'),
    };

    // ── Auto-ajustar adeudo base según el producto seleccionado ──────
    // Seleccionar la base del producto en el selector y actualizar el monto.
    if (!esGlobo && _migOfertas.length > 0) {
        var idProdNum = parseInt(_migDetalle.id_producto);
        var ofertaMatch = null;
        for (var _oi = 0; _oi < _migOfertas.length; _oi++) {
            if (_migOfertas[_oi].id_producto === idProdNum) { ofertaMatch = _migOfertas[_oi]; break; }
        }
        if (ofertaMatch) {
            // Pre-seleccionar el radio según base_calculo del producto
            var baseProducto = ofertaMatch.base_calculo || 'adeudo_total';
            var radioId = baseProducto === 'saldo_total_capital' ? 'migBase_capital'
                        : baseProducto === 'interes' ? 'migBase_interes'
                        : 'migBase_total';
            var radioEl = document.getElementById(radioId);
            if (radioEl) radioEl.checked = true;

            // Usar el monto según la base seleccionada
            var monto = _migGetMontoBase(_migGetBaseSeleccionada());
            var migAdeudoEl = document.getElementById('migAdeudo');
            if (migAdeudoEl) {
                migAdeudoEl.value = monto.toFixed(2);
            }
            // Quitar aviso previo
            var avisoPrevio = document.getElementById('migAdeudoAviso');
            if (avisoPrevio) avisoPrevio.remove();
        }
    } else if (esGlobo) {
        // Para globo, restaurar adeudo total del crédito
        var migAdeudoGlobo = document.getElementById('migAdeudo');
        if (migAdeudoGlobo && _migCredito) {
            migAdeudoGlobo.value = parseFloat(_migCredito.Adeudo_total || 0).toFixed(2);
        }
        var avisoPrevioG = document.getElementById('migAdeudoAviso');
        if (avisoPrevioG) avisoPrevioG.remove();
    }
    // ─────────────────────────────────────────────────────────────

    var colBucket = document.getElementById('colBucketMorosidad');
    var colSemanal = document.getElementById('colPagoSemanal');
    var colPagoFinal = document.getElementById('colPagoFinal');

    if (esGlobo) {
        // Ocultar % Descuento y mostrar Pago Inicial
        var filaDescuento = document.getElementById('filaDescuentoPorcentaje');
        if (filaDescuento) filaDescuento.style.display = 'none';
        var colPagoInicial = document.getElementById('colPagoInicial');
        if (colPagoInicial) colPagoInicial.style.display = 'block';

        // Pago semanal visible y editable
        if (colSemanal) {
            colSemanal.style.display = 'block';
            var inputSemanal = document.getElementById('migPagoSemanal');
            if (inputSemanal) {
                inputSemanal.readOnly = false;
                inputSemanal.style.background = '';
                inputSemanal.placeholder = 'Ej. 1900';
                inputSemanal.value = '';
            }
        }

        // Mostrar campo Pago Final
        if (colPagoFinal) colPagoFinal.style.display = 'block';

        // Reemplazar bucket por semanas dropdown
        if (colBucket && !document.getElementById('migSemanasGlobo')) {
            var labelBucket = document.getElementById('labelBucketMorosidad');
            if (labelBucket) labelBucket.textContent = 'Semanas a elegir';

            var inputBucket = document.getElementById('migBucket');
            if (inputBucket) inputBucket.style.display = 'none';

            var select = document.createElement('select');
            select.id = 'migSemanasGlobo';
            select.className = 'form-select';
            select.onchange = window.migCalcular;

            for (var s = 2; s <= 52; s++) {
                var op = document.createElement('option');
                op.value = s;
                op.textContent = s + ' semanas';
                select.appendChild(op);
            }
            
            
            colBucket.appendChild(select);
        } else if (colBucket) {
            var labelBucket = document.getElementById('labelBucketMorosidad');
            if (labelBucket) labelBucket.textContent = 'Semanas a elegir';
            var inputBucket = document.getElementById('migBucket');
            if (inputBucket) inputBucket.style.display = 'none';
            var existeSel = document.getElementById('migSemanasGlobo');
            if (existeSel) existeSel.style.display = 'block';
        }

        // Mostrar radios de modo y resetear a semanal
        var radiosDivP = document.getElementById('migModoGloboRadios');
        if (radiosDivP) radiosDivP.style.display = 'block';
        var radioSemanal = document.getElementById('migModoRadioSemanal');
        if (radioSemanal) radioSemanal.checked = true;
        var libreWrapP = document.getElementById('migLibreWrap');
        if (libreWrapP) libreWrapP.style.display = 'none';

    } else {
        // Restaurar % Descuento y ocultar Pago Inicial
        var filaDescuento = document.getElementById('filaDescuentoPorcentaje');
        if (filaDescuento) filaDescuento.style.display = '';
        var colPagoInicial = document.getElementById('colPagoInicial');
        if (colPagoInicial) colPagoInicial.style.display = 'none';
        // Restaurar form normal
        if (colSemanal) {
            colSemanal.style.display = 'block';
            var inputSemanal = document.getElementById('migPagoSemanal');
            if (inputSemanal) {
                inputSemanal.readOnly = false;
                inputSemanal.style.background = '';
                inputSemanal.placeholder = '3250';
            }
        }

        // Ocultar Pago Final
        if (colPagoFinal) colPagoFinal.style.display = 'none';

        // Restaurar campo Monto Adicional (solo visible en flujo normal)
        var colMigMontoAdNormal = document.getElementById('colMigMontoAdicional');
        var migMontoAdNormal = document.getElementById('migMontoAdicional');
        if (colMigMontoAdNormal) colMigMontoAdNormal.style.display = '';
        if (migMontoAdNormal) { migMontoAdNormal.disabled = false; migMontoAdNormal.value = ''; }

        if (colBucket) {
            var labelBucket = document.getElementById('labelBucketMorosidad');
            if (labelBucket) labelBucket.textContent = 'Bucket Morosidad';
            var inputBucket = document.getElementById('migBucket');
            if (inputBucket) inputBucket.style.display = 'block';
            var existeSel = document.getElementById('migSemanasGlobo');
            if (existeSel) existeSel.style.display = 'none';
        }

        // Ocultar radios y libre wrap para productos normales
        var radiosDivPE = document.getElementById('migModoGloboRadios');
        if (radiosDivPE) radiosDivPE.style.display = 'none';
        var libreWrapPE = document.getElementById('migLibreWrap');
        if (libreWrapPE) libreWrapPE.style.display = 'none';
    }

    if (typeof window.migCalcular === 'function') {
        window.migCalcular();
    }
};


// ════════════════════════════════════════════════
// MODO LIBRE – Pago Mixto con fechas específicas
// ════════════════════════════════════════════════

window.migModoGloboChange = function (modo) {
    var esLibre = modo === 'libre';

    // Mostrar/ocultar campos propios del modo semanal-globo
    var idsCamposGlobo = ['colPagoSemanal', 'colPagoFinal', 'colPagoInicial', 'colBucketMorosidad'];
    idsCamposGlobo.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.style.display = esLibre ? 'none' : 'block';
    });

    // Tabla de pagos libres
    var libreWrap = document.getElementById('migLibreWrap');
    if (libreWrap) libreWrap.style.display = esLibre ? 'block' : 'none';

    // Monto adicional: habilitado en libre, oculto en globo semanal
    var colAd = document.getElementById('colMigMontoAdicional');
    var inputAd = document.getElementById('migMontoAdicional');
    // Fecha inicio: en libre la primera fecha de pago es el inicio
    var colFechaInicioEl = document.getElementById('colFechaInicio');
    var noticeFechaEl = document.getElementById('noticeFechaLibre');
    if (esLibre) {
        if (colFechaInicioEl) colFechaInicioEl.style.display = 'none';
        if (noticeFechaEl) noticeFechaEl.classList.remove('d-none');
        if (colAd) colAd.style.display = '';
        if (inputAd) { inputAd.disabled = false; inputAd.value = ''; }
        window.migLibreGenerarFilas();
    } else {
        if (colFechaInicioEl) colFechaInicioEl.style.display = '';
        if (noticeFechaEl) noticeFechaEl.classList.add('d-none');
        if (colAd) colAd.style.display = 'none';
        if (inputAd) { inputAd.disabled = true; inputAd.value = ''; }
    }

    // Ocultar preview y botón guardar al cambiar de modo
    var preview = document.getElementById('migPreview');
    if (preview) preview.classList.add('d-none');
    var guardaBtn = document.querySelector('#modalMigracion .modal-footer .btn-success');
    if (guardaBtn) guardaBtn.style.display = 'none';

    if (!esLibre && typeof window.migCalcular === 'function') {
        window.migCalcular();
    }
};

window.migLibreGenerarFilas = function () {
    var tbody = document.getElementById('migLibreFilasBody');
    if (!tbody) return;
    var n = parseInt((document.getElementById('migLibreNumPagos') || {}).value) || 1;
    if (n < 1) n = 1;
    if (n > 15) {
        n = 15;
        var elNumPagos = document.getElementById('migLibreNumPagos');
        if (elNumPagos) elNumPagos.value = 15;
    }

    // Preservar valores existentes
    var existentes = [];
    tbody.querySelectorAll('tr').forEach(function (tr) {
        var f = tr.querySelector('.mig-libre-fecha');
        var m = tr.querySelector('.mig-libre-monto');
        existentes.push({ fecha: f ? f.value : '', monto: m ? m.value : '' });
    });

    tbody.innerHTML = '';
    for (var i = 1; i <= n; i++) {
        var prev = existentes[i - 1] || { fecha: '', monto: '' };
        // Calcular min date: el día siguiente a la fecha del pago anterior
        var minDate = '';
        if (i > 1) {
            var prevFechaRef = existentes[i - 2] ? existentes[i - 2].fecha : '';
            if (prevFechaRef) {
                var dt = new Date(prevFechaRef + 'T00:00:00');
                dt.setDate(dt.getDate() + 1);
                minDate = dt.toISOString().split('T')[0];
            }
        }
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td class="text-center fw-bold text-muted">' + i + '</td>' +
            '<td><input type="date" class="form-control form-control-sm mig-libre-fecha"' +
                ' value="' + (prev.fecha || '') + '"' +
                (minDate ? ' min="' + minDate + '"' : '') +
                ' oninput="window.migLibreDateChanged(this)"></td>' +
            '<td><input type="number" class="form-control form-control-sm mig-libre-monto" min="0" step="0.01" placeholder="0.00" value="' + (prev.monto || '') + '" oninput="window.migLibreActualizarTotal()"></td>';
        tbody.appendChild(tr);
    }
    window.migLibreActualizarTotal();
};

window.migLibreValidarFechas = function () {
    var fechaInputs = document.querySelectorAll('#migLibreFilasBody .mig-libre-fecha');
    var usadas = [];
    var prevFecha = '';
    var valido = true;
    fechaInputs.forEach(function (inp) {
        inp.classList.remove('is-invalid');
        var f = inp.value;
        if (f) {
            // Orden cronológico estrictamente ascendente (no igual)
            if (prevFecha && f <= prevFecha) {
                inp.classList.add('is-invalid');
                valido = false;
            }
            // Fecha duplicada con cualquier otra
            if (usadas.indexOf(f) !== -1) {
                inp.classList.add('is-invalid');
                valido = false;
            } else {
                usadas.push(f);
            }
            prevFecha = f;
        }
    });
    return valido;
};

// Sync primera fecha a migFechaInicio, actualiza min-dates subsecuentes y valida
window.migLibreDateChanged = function (input) {
    var tbody = document.getElementById('migLibreFilasBody');
    if (!tbody) return;

    var rows = tbody.querySelectorAll('tr');
    // Detectar el índice de la fila que cambió
    var idxCambiado = -1;
    rows.forEach(function (tr, i) {
        if (tr.querySelector('.mig-libre-fecha') === input) idxCambiado = i;
    });

    // Si cambió la primera fecha → limpiar todas las fechas posteriores
    if (idxCambiado === 0) {
        for (var j = 1; j < rows.length; j++) {
            var fInp = rows[j].querySelector('.mig-libre-fecha');
            if (fInp) fInp.value = '';
        }
    }

    // Actualizar min-date de todos los inputs a partir del índice cambiado
    var lastFecha = '';
    rows.forEach(function (tr, i) {
        var fInp = tr.querySelector('.mig-libre-fecha');
        if (!fInp) return;
        if (i === 0) {
            fInp.removeAttribute('min');
            lastFecha = fInp.value;
        } else {
            if (lastFecha) {
                var dt = new Date(lastFecha + 'T00:00:00');
                dt.setDate(dt.getDate() + 1);
                fInp.min = dt.toISOString().split('T')[0];
            } else {
                fInp.removeAttribute('min');
            }
            lastFecha = fInp.value || lastFecha;
        }
    });

    // Sync primera fecha a migFechaInicio
    if (rows.length > 0) {
        var firstDate = rows[0].querySelector('.mig-libre-fecha');
        var migFechaInicioEl = document.getElementById('migFechaInicio');
        if (firstDate && migFechaInicioEl) {
            migFechaInicioEl.value = firstDate.value;
        }
    }

    window.migLibreActualizarTotal();
};

window.migLibreActualizarTotal = function () {
    var adeudo = parseFloat((document.getElementById('migAdeudo') || {}).value) || 0;
    var adicional = parseFloat((document.getElementById('migMontoAdicional') || {}).value) || 0;
    var totalEsperado = Math.round((adeudo + adicional) * 100) / 100;
    var asignado = 0;
    document.querySelectorAll('#migLibreFilasBody .mig-libre-monto').forEach(function (inp) {
        asignado += parseFloat(inp.value) || 0;
    });
    asignado = Math.round(asignado * 100) / 100;

    var fmt = function (v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    var indicador = document.getElementById('migLibreTotalIndicador');
    if (indicador) {
        var diff = Math.round((asignado - totalEsperado) * 100) / 100;
        var color = Math.abs(diff) < 0.01 ? '#15803d' : (diff > 0 ? '#dc2626' : '#9333ea');
        indicador.style.borderColor = color;
        indicador.style.color = color;
        indicador.textContent = 'Asignado: ' + fmt(asignado) + ' / ' + fmt(totalEsperado);
    }
    _alertarExcedeDeuda('migLibre', asignado, adeudo);

    if (asignado > 0 && typeof window.migCalcular === 'function') {
        if (!window.migLibreValidarFechas()) {
            migGenerarPreamort([]);
            var guardaBtnFV = document.querySelector('#modalMigracion .modal-footer .btn-success');
            if (guardaBtnFV) guardaBtnFV.style.display = 'none';
            if (indicador) {
                indicador.style.borderColor = '#dc2626';
                indicador.style.color = '#dc2626';
                indicador.textContent = '⚠ Las fechas deben ser distintas y en orden ascendente (sin repetir)';
            }
            return;
        }
        window.migCalcular();
    }
};

window.migLibreDistribuirIgual = function () {
    var adeudo = parseFloat((document.getElementById('migAdeudo') || {}).value) || 0;
    if (adeudo <= 0) return;
    var inputs = document.querySelectorAll('#migLibreFilasBody .mig-libre-monto');
    var n = inputs.length;
    if (n === 0) return;
    var porPago = Math.floor((adeudo / n) * 100) / 100;
    var residuo = Math.round((adeudo - porPago * n) * 100) / 100;
    inputs.forEach(function (inp, idx) {
        inp.value = (idx === n - 1 ? Math.round((porPago + residuo) * 100) / 100 : porPago).toFixed(2);
    });
    window.migLibreActualizarTotal();
};

// ════════════════════════════════════════════════
// PREAMORTIZACIÓN EN TIEMPO REAL
// ════════════════════════════════════════════════
function migGenerarPreamort(filas) {
    var wrap = document.getElementById('preamortTablaWrap');
    var vacio = document.getElementById('preamortVacio');
    var tbody = document.getElementById('preamortBody');

    if (!filas || filas.length === 0) {
        wrap.style.display = 'none';
        vacio.style.display = 'block';
        return;
    }

    var fmt = function (v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    var fmtF = function (fechaISO) {
        if (!fechaISO) return '—';
        var p = fechaISO.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };

    var badgeTipo = function (tipo) {
        if (tipo === 'globo') {
            return '<span style="background:#764ba2;color:#fff;font-size:0.68rem;' +
                'padding:2px 8px;border-radius:12px;font-weight:600;">Cierre</span>';
        }
        if (tipo === 'unico') {
            return '<span style="background:#2563eb;color:#fff;font-size:0.68rem;' +
                'padding:2px 8px;border-radius:12px;font-weight:600;">Único</span>';
        }
        if (tipo === 'inicial') {
            return '<span style="background:#0891b2;color:#fff;font-size:0.68rem;' +
                'padding:2px 8px;border-radius:12px;font-weight:600;">Inicial</span>';
        }
        return '<span style="background:#e2e8f0;color:#475569;font-size:0.68rem;' +
            'padding:2px 8px;border-radius:12px;font-weight:600;">Normal</span>';
    };

    var html = filas.map(function (f) {
        var trStyle = f.tipo === 'globo'
            ? 'background:rgba(118,75,162,0.06);font-weight:600;'
            : f.tipo === 'inicial'
            ? 'background:rgba(8,145,178,0.06);font-weight:600;'
            : '';
        return '<tr style="' + trStyle + '">' +
            '<td style="color:#64748b;font-size:0.8rem;">' + f.num + '</td>' +
            '<td style="font-size:0.8rem;">' + fmtF(f.fecha) + '</td>' +
            '<td style="font-size:0.8rem;font-weight:600;">' + fmt(f.monto) + '</td>' +
            '<td>' + badgeTipo(f.tipo) + '</td>' +
            '<td style="font-size:0.8rem;color:#16a34a;font-weight:600;">' + fmt(f.saldo) + '</td>' +
            '</tr>';
    }).join('');

    tbody.innerHTML = html;
    wrap.style.display = 'block';
    vacio.style.display = 'none';
}

window.migCalcular = function () {

    var addDias = function (iso, d) {
        var dt = new Date(iso + 'T00:00:00');
        dt.setDate(dt.getDate() + d);
        return dt.toISOString().split('T')[0];
    };

    // ── Lógica especial para Convenio Pago Mixto ──────────
    var sel = document.getElementById('migProducto');
    var opt = sel ? sel.options[sel.selectedIndex] : null;
    var esGlobo = opt && (opt.text || '').indexOf('Convenio Pago Mixto') !== -1;

    if (esGlobo) {
        // ── Modo libre (fechas específicas) ──────────────────────────────
        var esModoLibre = !!(document.getElementById('migModoRadioLibre') && document.getElementById('migModoRadioLibre').checked);
        if (esModoLibre) {
            var adeudoL = parseFloat((document.getElementById('migAdeudo') || {}).value) || 0;
            var adicionalL = parseFloat((document.getElementById('migMontoAdicional') || {}).value) || 0;
            var previewL = document.getElementById('migPreview');
            var getGuardarBtnL = function () {
                return document.querySelector('#modalMigracion .modal-footer .btn-success');
            };

            var filasL = [];
            document.querySelectorAll('#migLibreFilasBody tr').forEach(function (tr, idx) {
                var f = tr.querySelector('.mig-libre-fecha');
                var m = tr.querySelector('.mig-libre-monto');
                filasL.push({
                    num: idx + 1,
                    fecha: f ? f.value : '',
                    monto: parseFloat(m ? m.value : 0) || 0
                });
            });

            var sumaL = Math.round(filasL.reduce(function (acc, r) { return acc + r.monto; }, 0) * 100) / 100;
            var totalFinalL = Math.round((sumaL + adicionalL) * 100) / 100;
            var descuentoL = Math.round((adeudoL - totalFinalL) * 100) / 100;
            var pctL = adeudoL > 0 ? Math.round((descuentoL / adeudoL) * 10000) / 100 : 0;

            var tieneFilas = filasL.some(function (r) { return r.monto > 0 && r.fecha; });
            if (!adeudoL || !tieneFilas) {
                if (previewL) previewL.classList.add('d-none');
                var btnLHide = getGuardarBtnL();
                if (btnLHide) btnLHide.style.display = 'none';
                migGenerarPreamort([]);
                return;
            }

            // ── Descuento máximo 45% (modo libre) ────────────────────────────────
            if (pctL > 45) {
                var errLibre = document.getElementById('migErrorGlobo');
                if (!errLibre) {
                    errLibre = document.createElement('div');
                    errLibre.id = 'migErrorGlobo';
                    errLibre.className = 'alert mt-2';
                    errLibre.style.display = 'none';
                    var _preEl = document.getElementById('migPreview');
                    if (_preEl && _preEl.parentNode) _preEl.parentNode.insertBefore(errLibre, _preEl);
                }
                errLibre.className = 'alert alert-warning mt-2';
                errLibre.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' +
                    'El descuento calculado (' + pctL.toFixed(2) + '%) supera el máximo permitido del <strong>45%</strong>. ' +
                    'El total a cobrar mínimo es <strong>$' +
                    (adeudoL * 0.55).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</strong>.';
                errLibre.style.display = 'block';
                var _btnLCap = getGuardarBtnL();
                if (_btnLCap) _btnLCap.style.display = 'none';
                migGenerarPreamort([]);
                return;
            }
            // ────────────────────────────────────────────────────────────────
            // Limpiar error si todo está OK
            var _errLibreOK = document.getElementById('migErrorGlobo');
            if (_errLibreOK) _errLibreOK.style.display = 'none';

            var fmtL = function (v) {
                return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            };

            var resumenCardsL = document.getElementById('migResumenCards');
            if (resumenCardsL) {
                resumenCardsL.innerHTML =
                    '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
                    '<div class="small text-muted">Adeudo Base</div>' +
                    '<div class="fw-bold text-primary">' + fmtL(adeudoL) + '</div></div></div>' +
                    '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
                    '<div class="small text-muted">Descuento (' + pctL.toFixed(2) + '%)</div>' +
                    '<div class="fw-bold ' + (descuentoL >= 0 ? 'text-danger' : 'text-warning') + '">' +
                    (descuentoL >= 0 ? '-' : '+') + fmtL(Math.abs(descuentoL)) + '</div></div></div>' +
                    '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
                    '<div class="small text-muted">Pagos</div>' +
                    '<div class="fw-bold text-success">' + filasL.length + ' pagos</div></div></div>' +
                    '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
                    '<div class="small text-muted">Total a Pagar</div>' +
                    '<div class="fw-bold text-warning">' + fmtL(totalFinalL) + '</div></div></div>';
            }

            var migTotalBaseEl = document.getElementById('migTotalBase');
            var migTotalFinalEl = document.getElementById('migTotalFinal');
            var colMigMontoAdEl = document.getElementById('colMigMontoAdicional');
            var migMontoAdEl = document.getElementById('migMontoAdicional');
            if (migTotalBaseEl) migTotalBaseEl.value = sumaL.toFixed(2);
            if (colMigMontoAdEl) colMigMontoAdEl.style.display = '';
            if (migMontoAdEl) migMontoAdEl.disabled = false;
            if (migTotalFinalEl) migTotalFinalEl.value = totalFinalL.toFixed(2);

            if (previewL) previewL.classList.remove('d-none');
            var btnLShow = getGuardarBtnL();
            if (btnLShow) btnLShow.style.display = 'inline-block';

            // Preamort desde las filas libres
            var filasPreL = [];
            var saldoPreL = totalFinalL;
            filasL.forEach(function (r, idx) {
                var montoP = (idx === filasL.length - 1 && adicionalL > 0)
                    ? Math.round((r.monto + adicionalL) * 100) / 100
                    : r.monto;
                saldoPreL = Math.round((saldoPreL - montoP) * 100) / 100;
                if (saldoPreL < 0) saldoPreL = 0;
                filasPreL.push({
                    num: r.num, fecha: r.fecha, monto: montoP,
                    tipo: idx === filasL.length - 1 ? 'globo' : 'normal',
                    saldo: saldoPreL
                });
            });
            migGenerarPreamort(filasPreL);
            return;
        }
        // ── Fin modo libre ────────────────────────────────────────────

        var adeudo = parseFloat(document.getElementById('migAdeudo').value) || 0;
        var semanal = parseFloat(document.getElementById('migPagoSemanal').value) || 0;
        var pagoFinal = parseFloat(document.getElementById('migPagoFinal') ? document.getElementById('migPagoFinal').value : 0) || 0;
        var semanas = parseInt(document.getElementById('migSemanasGlobo') ? document.getElementById('migSemanasGlobo').value : 1) || 1;
        var fecha = document.getElementById('migFechaInicio').value;
        var preview = document.getElementById('migPreview');
        var getGuardarBtn = function () {
            return document.querySelector('#modalMigracion .modal-footer .btn-success');
        };


       if (!adeudo || !fecha || !semanal) {
            if (preview) preview.classList.add('d-none');
            var btn = getGuardarBtn();
            if (btn) btn.style.display = 'none';
            migGenerarPreamort([]);
            return;
        }

        // ── CORRECCIÓN: total deriva de los pagos, no del % ──────────
        var pagoInicial = parseFloat(document.getElementById('globoPagoInicial') ? document.getElementById('globoPagoInicial').value : 0) || 0;

        // Limpiar/crear div de errores Globo
        var errGlobo = document.getElementById('migErrorGlobo');
        if (!errGlobo) {
            errGlobo = document.createElement('div');
            errGlobo.id = 'migErrorGlobo';
            errGlobo.className = 'alert mt-2';
            errGlobo.style.display = 'none';
            var previewEl = document.getElementById('migPreview');
            if (previewEl && previewEl.parentNode) previewEl.parentNode.insertBefore(errGlobo, previewEl);
        }


        errGlobo.style.display = 'none';
        var errBk = document.getElementById('migErrorBackend');
        if (errBk) errBk.style.display = 'none';

        var mostrarErrorGlobo = function (msg) {
            errGlobo.className = 'alert alert-warning mt-2';
            errGlobo.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + msg;
            errGlobo.style.display = 'block';
            var btn = getGuardarBtn();
            if (btn) btn.style.display = 'none';
            migGenerarPreamort([]);
        };

        // ── Validaciones Globo ───────────────────────────────────────
        // 1. Sanitizar campos de pago (rechaza científico, ceros extra, fuera de rango)
        var _elPagoIni = document.getElementById('globoPagoInicial');
        var _elPagoFin = document.getElementById('migPagoFinal');
        if (_elPagoIni && _elPagoIni.value.trim() && !_sanitizarMonto(_elPagoIni, adeudo - 0.01)) return;
        if (_elPagoFin && _elPagoFin.value.trim() && !_sanitizarMonto(_elPagoFin, adeudo)) return;
        // Recalcular tras posible sanitización
        pagoInicial = parseFloat((_elPagoIni || {}).value) || 0;
        pagoFinal   = parseFloat((_elPagoFin || {}).value) || 0;

        // 2. Pago inicial no puede ser >= al adeudo
        if (pagoInicial > 0 && pagoInicial >= adeudo) {
            mostrarErrorGlobo('El pago inicial ($' + pagoInicial.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ') no puede ser mayor o igual al adeudo base.');
            return;
        }
        // 3. Pago semanal requerido
        if (semanal <= 0) {
            mostrarErrorGlobo('El pago semanal debe ser mayor a $0.00.');
            return;
        }
        // 4. Al menos uno de pago inicial o pago de cierre debe ser > 0
        if (pagoInicial <= 0 && pagoFinal <= 0) {
            mostrarErrorGlobo('Debes indicar al menos un <strong>Pago Inicial</strong> o un <strong>Pago de Cierre</strong> mayor a $0.00.');
            return;
        }
        // 5. La suma total no puede exceder el adeudo
        var totalCalculado = Math.round((pagoInicial + semanal * (semanas - 1) + pagoFinal) * 100) / 100;
        if (totalCalculado > adeudo) {
            mostrarErrorGlobo('La suma de pagos ($' + totalCalculado.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ') excede el adeudo base ($' + adeudo.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '). Ajusta los montos.');
            return;
        }
        // 6. Descuento máximo 45%
        var _pctDescuentoCalc = adeudo > 0 ? Math.round(((adeudo - totalCalculado) / adeudo) * 10000) / 100 : 0;
        if (_pctDescuentoCalc > 45) {
            mostrarErrorGlobo('El descuento calculado (' + _pctDescuentoCalc.toFixed(2) + '%) supera el máximo permitido del <strong>45%</strong>. ' +
                'El total a cobrar mínimo es <strong>$' + (adeudo * 0.55).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</strong>.');
            return;
        }
        // ─────────────────────────────────────────────────────────────


        // ── CORRECCIÓN: total deriva de los pagos, no del % ──────────
        var sumaIguales = Math.round(semanal * (semanas - 1) * 100) / 100;
        var totalConGlobo = Math.round((sumaIguales + pagoFinal + pagoInicial) * 100) / 100;
        var descuento = Math.round((adeudo - totalConGlobo) * 100) / 100;
        var pctCalculado = adeudo > 0 ? Math.round((descuento / adeudo) * 10000) / 100 : 0;
        // ─────────────────────────────────────────────────────────────


        _migSemanas = semanas;

        var fmt = function (v) {
            return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
        };

        var resumenCards = document.getElementById('migResumenCards');
        if (resumenCards) {
            resumenCards.innerHTML =
    '<div class=\"col-6 col-md-3\"><div class=\"border rounded p-2\">' +
    '<div class=\"small text-muted\">Adeudo Base</div>' +
    '<div class=\"fw-bold text-primary\">' + fmt(adeudo) + '</div>' +
    '</div></div>' +
    '<div class=\"col-6 col-md-3\"><div class=\"border rounded p-2\">' +
    '<div class=\"small text-muted\">Descuento (' + pctCalculado.toFixed(2) + '%)</div>' +
    '<div class=\"fw-bold text-danger\">-' + fmt(descuento) + '</div>' +
    '</div></div>' +
    (pagoInicial > 0
        ? '<div class=\"col-6 col-md-3\"><div class=\"border rounded p-2\">' +
          '<div class=\"small text-muted\">Pago Inicial</div>' +
          '<div class=\"fw-bold text-info\">' + fmt(pagoInicial) + '</div>' +
          '</div></div>'
        : '') +
    '<div class=\"col-6 col-md-3\"><div class=\"border rounded p-2\">' +
    '<div class=\"small text-muted\">Pago Semanal × ' + (semanas - 1) + '</div>' +
    '<div class=\"fw-bold text-success\">' + fmt(semanal) + '</div>' +
    '</div></div>' +
    '<div class=\"col-6 col-md-3\"><div class=\"border rounded p-2\">' +
    '<div class=\"small text-muted\">Pago Final</div>' +
    '<div class=\"fw-bold text-warning\">' + fmt(pagoFinal) + '</div>' +
    '</div></div>';
        }

        var migTotalBase = document.getElementById('migTotalBase');
        var migTotalFinal = document.getElementById('migTotalFinal');
        var migMontoAd = document.getElementById('migMontoAdicional');
        var colMigMontoAd = document.getElementById('colMigMontoAdicional');
        if (migTotalBase) migTotalBase.value = totalConGlobo.toFixed(2);
        if (migMontoAd) { migMontoAd.value = ''; migMontoAd.disabled = true; }
        if (colMigMontoAd) colMigMontoAd.style.display = 'none';
        if (migTotalFinal) migTotalFinal.value = totalConGlobo.toFixed(2);

        if (preview) preview.classList.remove('d-none');
        var btn = getGuardarBtn();
        if (btn) btn.style.display = 'inline-block';

        // ── Preamortización (globo) ────────────────────────
var filasGlobo = [];
var saldoG = totalConGlobo;

// Fila 0: pago inicial (si existe)
if (pagoInicial > 0) {
    saldoG = Math.round((saldoG - pagoInicial) * 100) / 100;
    if (saldoG < 0) saldoG = 0;
    filasGlobo.push({
        num: 1,
        fecha: fecha,
        monto: pagoInicial,
        tipo: 'inicial',
        saldo: saldoG,
    });
}

var offsetNum = pagoInicial > 0 ? 1 : 0;
var offsetDias = pagoInicial > 0 ? 7 : 0;

for (var g = 1; g <= semanas; g++) {
    var esUltG = g === semanas;
    var montoG = esUltG ? pagoFinal : semanal;
    // Si el pago de cierre es 0, no agregar la fila fantasma
    if (esUltG && montoG === 0) break;
    var tipoG = semanas === 1 ? 'unico' : (esUltG ? 'globo' : 'normal');
    saldoG = Math.round((saldoG - montoG) * 100) / 100;
    if (saldoG < 0) saldoG = 0;
    filasGlobo.push({
        num: g + offsetNum,
        fecha: addDias(fecha, offsetDias + (g - 1) * 7),
        monto: montoG,
        tipo: tipoG,
        saldo: saldoG,
    });
}
migGenerarPreamort(filasGlobo);

        return;
    }
    // ────────────────────────────────────────────────────────

    var adeudo = parseFloat(document.getElementById('migAdeudo').value) || 0;
    var pct = parseFloat(document.getElementById('migPorcentaje').value) || 0;
    var semanal = parseFloat(document.getElementById('migPagoSemanal').value) || 0;
    var fecha = document.getElementById('migFechaInicio').value;

    var errorExistente = document.getElementById('migErrorSemanal');
    if (errorExistente) errorExistente.remove();

    var getGuardarBtn = function () {
        return document.querySelector('#modalMigracion .modal-footer .btn-success');
    };

    var mostrarError = function (campo, mensaje) {
        var guardarBtn = getGuardarBtn();
        if (guardarBtn) guardarBtn.style.display = 'none';

        migGenerarPreamort([]);

        var inputCampo = document.getElementById(campo);
        if (!inputCampo) return;

        inputCampo.classList.add('is-invalid');

        var div = document.createElement('div');
        div.id = 'migErrorSemanal';
        div.style.cssText = 'color:#dc2626;font-size:0.78rem;margin-top:4px;display:flex;align-items:center;gap:5px;';
        div.innerHTML = '<i class="fas fa-triangle-exclamation"></i> ' + mensaje;
        inputCampo.closest('.col-md-4')
            ? inputCampo.closest('.col-md-4').appendChild(div)
            : inputCampo.parentNode.appendChild(div);
    };

    var limpiarError = function () {
        var pagoSemanal = document.getElementById('migPagoSemanal');
        if (pagoSemanal) pagoSemanal.classList.remove('is-invalid');

        var migAdeudo = document.getElementById('migAdeudo');
        if (migAdeudo) migAdeudo.classList.remove('is-invalid');

        var migPorcentaje = document.getElementById('migPorcentaje');
        if (migPorcentaje) migPorcentaje.classList.remove('is-invalid');

        var e = document.getElementById('migErrorSemanal');
        if (e) e.remove();
    };

    if (!adeudo || !semanal || !fecha) {
        var preview = document.getElementById('migPreview');
        if (preview) preview.classList.add('d-none');
        var guardarBtn = getGuardarBtn();
        if (guardarBtn) guardarBtn.style.display = 'none';
        migGenerarPreamort([]);
        return;
    }

    if (semanal <= 0) {
        mostrarError('migPagoSemanal', 'El pago semanal debe ser un valor positivo mayor a $0.');
        return;
    }

    var descuento = Math.round(adeudo * (pct / 100) * 100) / 100;
    var total = Math.round((adeudo - descuento) * 100) / 100;

    if (semanal > total) {
        mostrarError('migPagoSemanal',
            'El pago semanal ($' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2 }) +
            ') no puede ser mayor al total a pagar ($' +
            total.toLocaleString('es-MX', { minimumFractionDigits: 2 }) + ').');
        return;
    }

    var semanasEnt = Math.floor(total / semanal);
    var residuo = Math.round((total - semanasEnt * semanal) * 100) / 100;
    // Solo se genera semana extra si el residuo es >= $1.00 (evita cuota fantasma por centavos)
    var semanas = residuo >= 1.00 ? semanasEnt + 1 : semanasEnt;
    _migSemanas = semanas;

    if (semanas < 1 || semanas > 52) {
        mostrarError('migPagoSemanal',
            'El pago semanal genera ' + semanas + ' semanas, lo cual es inválido. ' +
            'Ajusta el monto para obtener entre 1 y 52 semanas.');
        return;
    }

    if (adeudo <= 0) {
        mostrarError('migAdeudo', 'El adeudo base debe ser mayor a $0.');
        return;
    }

    if (pct < 0 || pct > 45) {
        mostrarError('migPorcentaje', 'El porcentaje de descuento debe estar entre 0% y 45%.');
        return;
    }

    limpiarError();

    var fmt = function (v) {
        return '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    var resumenCards = document.getElementById('migResumenCards');
    if (resumenCards) {
        resumenCards.innerHTML =
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
            + '<div class="fw-bold text-warning" id="migResumenSemanal">' + semanas + ' sem</div></div></div>';
    }

    var preview = document.getElementById('migPreview');
    if (preview) preview.classList.remove('d-none');

    var guardarBtn = getGuardarBtn();
    if (guardarBtn) guardarBtn.style.display = 'inline-block';

    var migTotalBase = document.getElementById('migTotalBase');
    if (migTotalBase) migTotalBase.value = total.toFixed(2);

    var migMontoAdicional = document.getElementById('migMontoAdicional');
    if (migMontoAdicional) migMontoAdicional.value = '';

    var migTotalFinal = document.getElementById('migTotalFinal');
    if (migTotalFinal) migTotalFinal.value = total.toFixed(2);

    // ── Preamortización (normal) ───────────────────────────
    var filasNorm = [];
    var saldoN = total;

    for (var n = 1; n <= semanas; n++) {
        var esUltN = n === semanas;
        var montoN = (esUltN && residuo >= 1.00) ? residuo : semanal;
        saldoN = Math.round((saldoN - montoN) * 100) / 100;
        if (saldoN < 0) saldoN = 0;
        filasNorm.push({
            num: n,
            fecha: addDias(fecha, (n - 1) * 7),
            monto: montoN,
            tipo: 'normal',
            saldo: saldoN,
        });
    }
    migGenerarPreamort(filasNorm);
};


window.migRecalcularTotal = function () {
    // En modo libre, reenviar al actualizador de totales libres
    var esModoLibreRec = !!(document.getElementById('migModoRadioLibre') && document.getElementById('migModoRadioLibre').checked);
    if (esModoLibreRec) {
        var adLibreRec = document.getElementById('migMontoAdicional');
        if (!_sanitizarMonto(adLibreRec, 10000)) return;
        window.migLibreActualizarTotal();
        return;
    }

    var base = parseFloat(document.getElementById('migTotalBase').value) || 0;
    var elAdicMig = document.getElementById('migMontoAdicional');
    var _maxAdicMig = parseFloat((document.getElementById('migAdeudo') || {}).value) || base;
    if (!_sanitizarMonto(elAdicMig, _maxAdicMig)) return;
    var adicional = parseFloat(elAdicMig.value) || 0;
    if (adicional > 10000) {
        elAdicMig.value = '10000';
        adicional = 10000;
        _flashInvalid(elAdicMig);
        var _errAdicMig = document.getElementById('migErrorSemanal');
        if (!_errAdicMig) {
            _errAdicMig = document.createElement('div');
            _errAdicMig.id = 'migErrorSemanal';
            _errAdicMig.style.cssText = 'color:#dc2626;font-size:0.78rem;margin-top:4px;display:flex;align-items:center;gap:5px;';
            elAdicMig.parentNode.appendChild(_errAdicMig);
        }
        _errAdicMig.innerHTML = '<i class="fas fa-triangle-exclamation"></i> El monto adicional no puede exceder <strong>$10,000</strong>. Se ha ajustado al máximo permitido.';
        _errAdicMig.style.display = 'flex';
        setTimeout(function () { if (_errAdicMig) _errAdicMig.style.display = 'none'; }, 3000);
    }

    var totalFinal = Math.round((base + adicional) * 100) / 100;
    var totalFinalInput = document.getElementById('migTotalFinal');
    if (totalFinalInput) totalFinalInput.value = totalFinal.toFixed(2);

    // Recalcular pago semanal manteniendo las mismas semanas
    var semanas = _migSemanas || 0;
    if (semanas > 0 && totalFinal > 0) {
        var nuevoSemanal = Math.round((totalFinal / semanas) * 100) / 100;
        var pagoSemanalInput = document.getElementById('migPagoSemanal');
        if (pagoSemanalInput) pagoSemanalInput.value = nuevoSemanal.toFixed(2);

        var fmt = function (v) {
            return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
        };
        var elSemanal = document.getElementById('migResumenSemanal');
        if (elSemanal) elSemanal.textContent = semanas + ' sem';
    }

    // ── CORRECCIÓN: recalcular % de descuento real ────────────────
    var adeudoBase = parseFloat(document.getElementById('migAdeudo').value) || 0;
    if (adeudoBase > 0 && totalFinal > 0) {
        var descuentoReal = Math.round((adeudoBase - totalFinal) * 100) / 100;
        var pctReal = Math.round((descuentoReal / adeudoBase) * 10000) / 100;

        // Actualizar card de descuento en resumen
        var cards = document.querySelectorAll('#migResumenCards .border.rounded');
        cards.forEach(function (card) {
            var label = card.querySelector('.small.text-muted');
            var valor = card.querySelector('.fw-bold.text-danger');
            if (label && valor && label.textContent.indexOf('Descuento') !== -1) {
                label.textContent = 'Descuento (' + pctReal.toFixed(2) + '%)';
                valor.textContent = '-' + fmt(descuentoReal);
            }
        });
    }

    // ── Regenerar preamortización tras cambio de total ────────────────
    var _semR = _migSemanas || 0;
    var _semanal = parseFloat((document.getElementById('migPagoSemanal') || {}).value) || 0;
    var _fechaR = (document.getElementById('migFechaInicio') || {}).value || '';
    if (_semR > 0 && _semanal > 0 && _fechaR && totalFinal > 0) {
        var _addDiasR = function (iso, d) {
            var dt = new Date(iso + 'T00:00:00');
            dt.setDate(dt.getDate() + d);
            return dt.toISOString().split('T')[0];
        };
        var _residuoR = Math.round((totalFinal - Math.floor(totalFinal / _semanal) * _semanal) * 100) / 100;
        var _filasR = [];
        var _saldoR = totalFinal;
        for (var _r = 1; _r <= _semR; _r++) {
            var _esUltR = _r === _semR;
            var _montoR = (_esUltR && _residuoR >= 1.00) ? _residuoR : _semanal;
            _saldoR = Math.round((_saldoR - _montoR) * 100) / 100;
            if (_saldoR < 0) _saldoR = 0;
            _filasR.push({ num: _r, fecha: _addDiasR(_fechaR, (_r - 1) * 7), monto: _montoR, tipo: 'normal', saldo: _saldoR });
        }
        migGenerarPreamort(_filasR);
    }
    // ─────────────────────────────────────────────────────────────
};

// ══════════════════════════════════════════════════════
//  EDICIÓN DIRECTA DEL TOTAL FINAL
//  Al editar el campo, recalcula: % descuento, semanas y preamort
// ══════════════════════════════════════════════════════
window.migTotalFinalChanged = function () {
    var sel = document.getElementById('migProducto');
    var opt = sel ? sel.options[sel.selectedIndex] : null;
    var esGloboTF = opt && (opt.text || '').indexOf('Convenio Pago Mixto') !== -1;

    if (esGloboTF) {
        var elTFGlobo = document.getElementById('migTotalFinal');
        var _maxTF = ((_credito && parseFloat(_credito.Adeudo_total)) || 0) + 10000;
        if (!_sanitizarMonto(elTFGlobo, _maxTF)) return;
        var totalFinalTF = parseFloat(elTFGlobo.value) || 0;
        if (totalFinalTF <= 0) return;

        var esModoLibreTF = !!(document.getElementById('migModoRadioLibre') && document.getElementById('migModoRadioLibre').checked);
        if (esModoLibreTF) {
            // Modo libre: ajustar monto adicional para que totalFinal coincida con la suma de filas
            var sumaFilasTF = 0;
            document.querySelectorAll('#migLibreFilasBody .mig-libre-monto').forEach(function (inp) {
                sumaFilasTF += parseFloat(inp.value) || 0;
            });
            sumaFilasTF = Math.round(sumaFilasTF * 100) / 100;
            var nuevoAdicionalTF = Math.round((totalFinalTF - sumaFilasTF) * 100) / 100;
            var inputAdTF = document.getElementById('migMontoAdicional');
            if (inputAdTF) inputAdTF.value = nuevoAdicionalTF !== 0 ? nuevoAdicionalTF.toFixed(2) : '';
            window.migLibreActualizarTotal();
        } else {
            // Modo globo semanal: recalcular pago semanal manteniendo semanas, pago inicial y pago final
            var semanasValTF = parseInt((document.getElementById('migSemanasGlobo') || {}).value) || 1;
            var pagoFinalValTF = parseFloat((document.getElementById('migPagoFinal') || {}).value) || 0;
            var pagoInicialValTF = parseFloat((document.getElementById('migPagoInicial') || {}).value) || 0;
            // total = pagoInicial + semanal * (semanas - 1) + pagoFinal
            var semanasInterTF = semanasValTF > 1 ? semanasValTF - 1 : 1;
            var nuevoSemanalTF = Math.max(0, Math.round(((totalFinalTF - pagoInicialValTF - pagoFinalValTF) / semanasInterTF) * 100) / 100);
            var inputSemTF = document.getElementById('migPagoSemanal');
            if (inputSemTF && nuevoSemanalTF > 0) inputSemTF.value = nuevoSemanalTF.toFixed(2);
            if (typeof window.migCalcular === 'function') window.migCalcular();
        }
        return;
    }

    // ── Productos normales (no Pago Mixto) ────────────────────────────────
    var adeudo  = parseFloat((document.getElementById('migAdeudo') || {}).value) || 0;
    var elTFNorm = document.getElementById('migTotalFinal');
    if (!_sanitizarMonto(elTFNorm, adeudo)) return;
    var totalFinal = parseFloat(elTFNorm.value) || 0;
    var semanal   = parseFloat((document.getElementById('migPagoSemanal') || {}).value) || 0;
    var fecha     = (document.getElementById('migFechaInicio') || {}).value || '';

    if (totalFinal <= 0 || adeudo <= 0 || semanal <= 0 || !fecha) return;

    // ── Separar base (con descuento) y adicional ─────────────────────
    // Si el usuario captura un total mayor al adeudo base, el excedente
    // se redirige automáticamente al campo de Monto Adicional.
    var adicionalAuto = 0;
    var baseEfectiva  = totalFinal;
    if (totalFinal > adeudo) {
        adicionalAuto = Math.round((totalFinal - adeudo) * 100) / 100;
        baseEfectiva  = adeudo;
    }
    _alertarExcedeDeuda('mig', totalFinal, adeudo);

    // ── Semanas (basadas en pago semanal fijo sobre el total completo) ──
    var semanasEnt = Math.floor(totalFinal / semanal);
    var residuo    = Math.round((totalFinal - semanasEnt * semanal) * 100) / 100;
    // Solo se genera semana extra si el residuo es >= $1.00 (evita cuota fantasma por centavos)
    var semanas    = residuo >= 1.00 ? semanasEnt + 1 : semanasEnt;
    if (semanas < 1 || semanas > 52) return;
    _migSemanas = semanas;

    // ── % Descuento (solo sobre la base, sin incluir adicional) ─────
    var pctReal   = adeudo > 0 ? Math.round(((adeudo - baseEfectiva) / adeudo) * 10000) / 100 : 0;
    var descuento = Math.round((adeudo - baseEfectiva) * 100) / 100;

    // ── Descuento máximo 45% ──────────────────────────────────────
    if (pctReal > 45) {
        var _errTFNorm = document.getElementById('migErrorSemanal');
        if (!_errTFNorm) {
            _errTFNorm = document.createElement('div');
            _errTFNorm.id = 'migErrorSemanal';
            _errTFNorm.style.cssText = 'color:#dc2626;font-size:0.78rem;margin-top:4px;display:flex;align-items:center;gap:5px;';
            elTFNorm.parentNode.appendChild(_errTFNorm);
        }
        _errTFNorm.innerHTML = '<i class="fas fa-triangle-exclamation"></i> El descuento (' + pctReal.toFixed(2) + '%) supera el máximo de 45%. Total mínimo: $' +
            (adeudo * 0.55).toLocaleString('es-MX', { minimumFractionDigits: 2 });
        _flashInvalid(elTFNorm);
        var _migGBtn = document.querySelector('#modalMigracion .modal-footer .btn-success');
        if (_migGBtn) _migGBtn.style.display = 'none';
        return;
    }
    var _errTFNormOK = document.getElementById('migErrorSemanal');
    if (_errTFNormOK) _errTFNormOK.remove();
    // ─────────────────────────────────────────────────────────────

    // Actualizar campo % (sin bloquear si es readOnly; es escritura programática)
    var migPorcentajeEl = document.getElementById('migPorcentaje');
    if (migPorcentajeEl) migPorcentajeEl.value = pctReal.toFixed(2);

    // Sincronizar migTotalBase y enrutar excedente a migMontoAdicional
    var migTotalBase = document.getElementById('migTotalBase');
    if (migTotalBase) migTotalBase.value = baseEfectiva.toFixed(2);
    var migMontoAdicional = document.getElementById('migMontoAdicional');
    if (migMontoAdicional) migMontoAdicional.value = adicionalAuto > 0 ? adicionalAuto.toFixed(2) : '';

    // ── Resumen cards ──────────────────────────────────────────
    var fmt = function (v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };
    var resumenCards = document.getElementById('migResumenCards');
    if (resumenCards) {
        resumenCards.innerHTML =
            '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
            '<div class="small text-muted">Adeudo Base</div>' +
            '<div class="fw-bold text-primary">' + fmt(adeudo) + '</div></div></div>' +
            '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
            '<div class="small text-muted">Descuento (' + pctReal.toFixed(2) + '%)</div>' +
            '<div class="fw-bold text-danger">-' + fmt(descuento) + '</div></div></div>' +
            (adicionalAuto > 0
                ? '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
                  '<div class="small text-muted">Adicional</div>' +
                  '<div class="fw-bold text-info">+' + fmt(adicionalAuto) + '</div></div></div>'
                : '') +
            '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
            '<div class="small text-muted">Total a Pagar</div>' +
            '<div class="fw-bold text-success">' + fmt(totalFinal) + '</div></div></div>' +
            '<div class="col-6 col-md-3"><div class="border rounded p-2">' +
            '<div class="small text-muted">Semanas</div>' +
            '<div class="fw-bold text-warning" id="migResumenSemanal">' + semanas + ' sem</div></div></div>';
    }

    // ── Regenerar preamortización ─────────────────────────────────
    var addDias = function (iso, d) {
        var dt = new Date(iso + 'T00:00:00');
        dt.setDate(dt.getDate() + d);
        return dt.toISOString().split('T')[0];
    };
    var filas = [];
    var saldo = totalFinal;
    for (var n = 1; n <= semanas; n++) {
        var esUlt = n === semanas;
        var monto = (esUlt && residuo >= 1.00) ? residuo : semanal;
        saldo = Math.round((saldo - monto) * 100) / 100;
        if (saldo < 0) saldo = 0;
        filas.push({ num: n, fecha: addDias(fecha, (n - 1) * 7), monto: monto, tipo: 'normal', saldo: saldo });
    }
    migGenerarPreamort(filas);
};

window.validarPdfAdjunto = function (input) {
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

window.validarPdfAdjuntoConv = function (input) {
    var preview = document.getElementById('convPdfPreview');
    if (!input.files || !input.files[0]) { preview.innerHTML = ''; return; }

    var file = input.files[0];
    if (file.type !== 'application/pdf') {
        Swal.fire('Formato incorrecto', 'Solo se permiten archivos PDF', 'warning');
        input.value = ''; preview.innerHTML = ''; return;
    }
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire('Archivo muy grande', 'El PDF no debe exceder 5MB', 'warning');
        input.value = ''; preview.innerHTML = ''; return;
    }

    preview.innerHTML = '<i class="fas fa-file-pdf" style="font-size:2rem;color:#dc2626;"></i>';
};


// Modificar migGuardar para incluir el PDF
window.migGuardar = function () {
    if (!_migCredito || !_migDetalle) {
        Swal.fire('Error', 'Completa todos los campos.', 'warning');
        return;
    }

    var getGuardarBtn = function () {
        return document.querySelector('#modalMigracion .modal-footer .btn-success');
    };

    // Detectar flujo globo
    var esGloboMig = (function () {
        var sel = document.getElementById('migProducto');
        if (!sel) return false;
        var opt = sel.options[sel.selectedIndex];
        return opt && (opt.text || '').indexOf('Convenio Pago Mixto') !== -1;
    })();

    // --- VALIDACIÓN DE ELEMENTOS EXISTENTES ---
    var migSemanasGlobo = document.getElementById('migSemanasGlobo');
    var migPagoFinal = document.getElementById('migPagoFinal');
    var esModoLibreMig = esGloboMig && !!(document.getElementById('migModoRadioLibre') && document.getElementById('migModoRadioLibre').checked);

    // Si es Pago Mixto semanal pero faltan elementos, mostrar error
    if (esGloboMig && !esModoLibreMig && (!migSemanasGlobo || !migPagoFinal)) {
        Swal.fire('Error', 'La interfaz de Convenio Pago Mixto está incompleta. Contacta al administrador.', 'error');
        return;
    }

    // ── Flujo Libre (fechas específicas) ─────────────────────────────────
    if (esModoLibreMig) {
        // Validar orden cronológico antes de recolectar datos
        if (!window.migLibreValidarFechas()) {
            Swal.fire('Fechas inválidas', 'Las fechas de los pagos deben estar en orden cronológico ascendente.', 'warning');
            return;
        }

        var _adeudoLibre = parseFloat(document.getElementById('migAdeudo')?.value) || 0;
        var _adicionalLibre = parseFloat(document.getElementById('migMontoAdicional')?.value) || 0;
        // La fecha de inicio del convenio es la fecha del primer pago
        var _primerFechaEl = document.querySelector('#migLibreFilasBody .mig-libre-fecha');
        var _fechaLibre = _primerFechaEl ? (_primerFechaEl.value || '') : '';
        var _bucketLibre = document.getElementById('migBucket')?.value || '';
        var _totalLibre = parseFloat(document.getElementById('migTotalFinal')?.value) || 0;
        var _pdfLibre = document.getElementById('convPdfAdjunto')?.files[0]
                     || document.getElementById('migPdfAdjunto')?.files[0];

        var filasLibre = [];
        document.querySelectorAll('#migLibreFilasBody tr').forEach(function (tr, idx, arr) {
            var f = tr.querySelector('.mig-libre-fecha')?.value || '';
            var m = parseFloat(tr.querySelector('.mig-libre-monto')?.value) || 0;
            // Añadir monto adicional al último pago
            if (idx === arr.length - 1 && _adicionalLibre > 0) {
                m = Math.round((m + _adicionalLibre) * 100) / 100;
            }
            filasLibre.push({ fecha: f, monto: m });
        });

        if (!_adeudoLibre || !_fechaLibre || !_totalLibre || filasLibre.length === 0) {
            Swal.fire('Campos incompletos', 'Ingresa el adeudo, fecha de acuerdo y los pagos en la tabla.', 'warning');
            return;
        }
        if (!filasLibre.every(function (r) { return r.fecha && r.monto > 0; })) {
            Swal.fire('Tabla incompleta', 'Todas las filas deben tener fecha, verifica de nuevo.', 'warning');
            return;
        }

        var _fechasPagosJson = JSON.stringify(filasLibre);
        var _descuentoLibre = Math.round((_adeudoLibre - _totalLibre) * 100) / 100;
        var _pctLibre = _adeudoLibre > 0 ? Math.round((_descuentoLibre / _adeudoLibre) * 10000) / 100 : 0;

        Swal.fire({
            title: '¿Registrar convenio?',
            html: 'Se creará el convenio con <strong>' + filasLibre.length + ' pagos</strong> en fechas específicas.'
                + (_pdfLibre ? '<br><br><small class="text-success">📎 Se adjuntará: ' + _pdfLibre.name + '</small>' : ''),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#22c55e',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var guardarBtn = getGuardarBtn();
            if (guardarBtn) {
                guardarBtn.disabled = true;
                guardarBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
            }

            var _dataLibre = {
                id_credito: _migCredito.Id_credito,
                id_producto_convenio: _migDetalle.id_producto,
                id_producto_convenio_detalle: _migDetalle.id_detalle,
                nombre_cliente: _migCredito.Nombre_cliente,
                bucket_morosidad_real: _bucketLibre,
                dias_mora: _migCredito.Dias_mora || 0,
                avance_pago_plazo: _migCredito.Avance_Pago_Plazo || '',
                adeudo_total_original: _adeudoLibre.toFixed(2),
                porcentaje_descuento: _pctLibre.toFixed(4),
                descuento_monto: _descuentoLibre.toFixed(2),
                total_a_pagar: _totalLibre.toFixed(2),
                numero_semanas: filasLibre.length,
                pago_semanal: (_totalLibre / filasLibre.length).toFixed(2),
                fecha_acuerdo: _fechaLibre,
                tipo_calendario: 'libre',
                fechas_pagos: _fechasPagosJson,
                monto_adicional: _adicionalLibre > 0 ? _adicionalLibre.toFixed(2) : '',
                usuario_alta: window.usuarioActual || 'sistema',
            };

            var onLibreSuccess = function (resp) {
                var guardarBtnRest = getGuardarBtn();
                if (guardarBtnRest) {
                    guardarBtnRest.disabled = false;
                    guardarBtnRest.innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';
                }
                if (!resp.success) {
                    Swal.fire('Error', resp.mensaje || 'Error desconocido', 'error');
                    return;
                }
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalMigracion'));
                if (modal) modal.hide();
                var _idRL = _migCredito ? _migCredito.Id_credito : null;
                Swal.fire({
                    title: '¡Convenio registrado!',
                    html: 'Convenio pago mixto con ' + filasLibre.length + ' pagos creado exitosamente.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                }).then(function () {
                    if (_idRL) {
                        document.getElementById('inputBusqueda').value = _idRL;
                        window.buscarCredito();
                    } else {
                        location.reload();
                    }
                });
            };
            var onLibreError = function () {
                var guardarBtnRest = getGuardarBtn();
                if (guardarBtnRest) {
                    guardarBtnRest.disabled = false;
                    guardarBtnRest.innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';
                }
                Swal.fire('Error', 'Error de conexión.', 'error');
            };

            if (_pdfLibre) {
                var fdLibre = new FormData();
                Object.keys(_dataLibre).forEach(function (k) { fdLibre.append(k, _dataLibre[k]); });
                fdLibre.append('pdf_adjunto', _pdfLibre);
                http.request({
                    endpoint: '/convenios/guardarConvenio',
                    method: 'POST',
                    data: fdLibre,
                    contentType: false,
                    processData: false,
                    onSuccess: onLibreSuccess,
                    onError: onLibreError
                });
            } else {
                http.request({
                    endpoint: '/convenios/guardarConvenio',
                    method: 'POST',
                    data: _dataLibre,
                    onSuccess: onLibreSuccess,
                    onError: onLibreError
                });
            }
        });
        return;
    }
    // ── Fin flujo libre ────────────────────────────────────────────────────

    var _semanal = parseFloat(document.getElementById('migPagoSemanal')?.value) || 0;
    var _adeudo = parseFloat(document.getElementById('migAdeudo')?.value) || 0;
    var _pct = parseFloat(document.getElementById('migPorcentaje')?.value) || 0;
    var _total = Math.round((_adeudo - Math.round(_adeudo * (_pct / 100) * 100) / 100) * 100) / 100;
    var _semanas = _semanal > 0 ? Math.ceil(_total / _semanal) : -1;

    if (!esGloboMig && (_semanal <= 0 || _semanas < 1 || _semanas > 52)) {
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

    var adeudo = document.getElementById('migAdeudo')?.value;
    var pct = document.getElementById('migPorcentaje')?.value;
    var semanal = document.getElementById('migPagoSemanal')?.value;
    var fecha = document.getElementById('migFechaInicio')?.value;
    var bucket = document.getElementById('migBucket')?.value;
    var pdfFile = document.getElementById('migPdfAdjunto')?.files[0];
    var adicional = parseFloat(document.getElementById('migMontoAdicional')?.value) || 0;
    var totalFinal = parseFloat(document.getElementById('migTotalFinal')?.value) || 0;

    var _adeudoEnvio = parseFloat(document.getElementById('migAdeudo')?.value) || 0;
    var _totalEnvio = parseFloat(document.getElementById('migTotalFinal')?.value) || 0;
    // El porcentaje de descuento debe calcularse sobre la parte base (sin el monto adicional),
    // de lo contrario cuando totalFinal > adeudo se genera un porcentaje negativo que el backend
    // aplica como doble carga junto con monto_adicional.
    var _pctEnvio = _adeudoEnvio > 0
        ? Math.round(((_adeudoEnvio - (_totalEnvio - adicional)) / _adeudoEnvio) * 10000) / 100
        : 0;

    if (!adeudo || parseFloat(adeudo) <= 0 || (!esGloboMig && (pct === '' || pct === null || pct === undefined || parseFloat(pct) < 0)) || !semanal || !fecha || totalFinal <= 0) {
        Swal.fire('Campos incompletos', 'Llena todos los campos requeridos o el adeudo/total es inválido.', 'warning');
        return;
    }

    if (_pctEnvio > 45) {
        Swal.fire('Descuento no permitido', 'El descuento calculado (' + _pctEnvio.toFixed(2) + '%) supera el máximo permitido del 45%.', 'error');
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
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#22c55e',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var guardarBtn = getGuardarBtn();
        if (guardarBtn) {
            guardarBtn.disabled = true;
            guardarBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
        }

        if (pdfFile) {
            var formData = new FormData();
            var endpointFinalPdf = esGloboMig ? '/convenios/registrarConvenioGlobo' : '/convenios/migrarConvenio';

            if (esGloboMig) {
                // VALIDACIÓN SEGURA de elementos globo
                var semanasGlobo = migSemanasGlobo ? (parseInt(migSemanasGlobo.value) || 1) : 1;
                var pagoFinalMonto = migPagoFinal ? (parseFloat(migPagoFinal.value) || 0) : 0;
                var _pagoInicialGlobo = parseFloat(document.getElementById('globoPagoInicial')?.value) || 0;
                var _totalGloboCalculado = Math.round((_pagoInicialGlobo + (semanasGlobo - 1) * (parseFloat(semanal) || 0) + pagoFinalMonto) * 100) / 100;

                formData.append('id_credito', _migCredito.Id_credito);
                formData.append('nombre_cliente', _migCredito.Nombre_cliente);
                formData.append('bucket_morosidad_real', bucket || '');
                formData.append('dias_mora', _migCredito.Dias_mora || 0);
                formData.append('avance_pago_plazo', _migCredito.Avance_Pago_Plazo || '');
                formData.append('adeudo_total_original', adeudo);
                formData.append('total_a_pagar', _totalGloboCalculado.toFixed(2));
                formData.append('pago_inicial_monto', _pagoInicialGlobo.toFixed(2));
                formData.append('pagos_iguales_cantidad', semanasGlobo - 1);
                formData.append('pagos_iguales_monto', semanal);
                formData.append('pago_globo_monto', pagoFinalMonto);
                formData.append('frecuencia', 'semanal');
                formData.append('fecha_primer_pago', fecha);
                formData.append('usuario_alta', window.usuarioActual || 'sistema');
            } else {
                formData.append('id_credito', _migCredito.Id_credito);
                formData.append('nombre_cliente', _migCredito.Nombre_cliente);
                formData.append('id_producto_convenio', _migDetalle.id_producto);
                formData.append('id_producto_convenio_detalle', _migDetalle.id_detalle);
                formData.append('adeudo_base', adeudo);
                formData.append('base_calculo', _migGetBaseSeleccionada());
                formData.append('monto_adicional', adicional > 0 ? adicional.toFixed(2) : '');
                formData.append('total_final_con_adicional', totalFinal.toFixed(2));
                formData.append('porcentaje_descuento', _pctEnvio);
                formData.append('pago_semanal', semanal);
                formData.append('fecha_inicio', fecha);
                formData.append('bucket_morosidad_real', bucket || '');
                formData.append('dias_mora', _migCredito.Dias_mora || 0);
                formData.append('avance_pago_plazo', _migCredito.Avance_Pago_Plazo || '');
            }
            formData.append('pdf_adjunto', pdfFile);

            http.request({
                endpoint: endpointFinalPdf,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                onSuccess: function (resp) {
                    var guardarBtnRestore = getGuardarBtn();
                    if (guardarBtnRestore) {
                        guardarBtnRestore.disabled = false;
                        guardarBtnRestore.innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';
                    }

                    if (!resp.success) {
                        var errBackend = document.getElementById('migErrorBackend');
                        if (!errBackend) {
                            errBackend = document.createElement('div');
                            errBackend.id = 'migErrorBackend';
                            var preamortWrap = document.getElementById('preamortTablaWrap');
                            if (preamortWrap && preamortWrap.parentNode) {
                                preamortWrap.parentNode.appendChild(errBackend);
                            }
                        }
                        errBackend.className = 'alert alert-danger mt-3';
                        errBackend.innerHTML = '<i class="fas fa-times-circle me-2"></i><strong>Error al generar:</strong> ' + (resp.mensaje || 'Error desconocido');
                        errBackend.style.display = 'block';
                        return;
                    }

                    var errBackend = document.getElementById('migErrorBackend');
                    if (errBackend) errBackend.style.display = 'none';

                    var d = resp.datos || {};
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalMigracion'));
                    if (modal) modal.hide();

                    var _idParaRecargar = _migCredito ? _migCredito.Id_credito : null;
                    Swal.fire({
                        title: '¡Convenio registrado!',
                        html: (d.semanas_pagadas || 0) + ' de ' + (d.semanas_total || 0) + ' semanas marcadas como pagadas.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(function () {
                        if (_idParaRecargar) {
                            document.getElementById('inputBusqueda').value = _idParaRecargar;
                            window.buscarCredito();
                        } else {
                            location.reload();
                        }
                    });
                },
                onError: function () {
                    var guardarBtnRestore = getGuardarBtn();
                    if (guardarBtnRestore) {
                        guardarBtnRestore.disabled = false;
                        guardarBtnRestore.innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';
                    }
                    Swal.fire('Error', 'Error de conexión.', 'error');
                }
            });
        } else {
            var endpointFinal = esGloboMig ? '/convenios/registrarConvenioGlobo' : '/convenios/migrarConvenio';

            var dataFinal;
            if (esGloboMig) {
                var semanasGlobo = migSemanasGlobo ? (parseInt(migSemanasGlobo.value) || 1) : 1;
                var pagoFinalMonto = migPagoFinal ? (parseFloat(migPagoFinal.value) || 0) : 0;
                var _pagoInicialGlobo = parseFloat(document.getElementById('globoPagoInicial')?.value) || 0;
                var _totalGloboCalculado = Math.round((_pagoInicialGlobo + (semanasGlobo - 1) * (parseFloat(semanal) || 0) + pagoFinalMonto) * 100) / 100;

                dataFinal = {
                    id_credito: _migCredito.Id_credito,
                    nombre_cliente: _migCredito.Nombre_cliente,
                    bucket_morosidad_real: bucket || '',
                    dias_mora: _migCredito.Dias_mora || 0,
                    avance_pago_plazo: _migCredito.Avance_Pago_Plazo || '',
                    adeudo_total_original: adeudo,
                    total_a_pagar: _totalGloboCalculado.toFixed(2),
                    pago_inicial_monto: _pagoInicialGlobo.toFixed(2),
                    pagos_iguales_cantidad: semanasGlobo - 1,
                    pagos_iguales_monto: semanal,
                    pago_globo_monto: pagoFinalMonto,
                    frecuencia: 'semanal',
                    fecha_primer_pago: fecha,
                    usuario_alta: window.usuarioActual || 'sistema',
                };
            } else {
                dataFinal = {
                    id_credito: _migCredito.Id_credito,
                    nombre_cliente: _migCredito.Nombre_cliente,
                    id_producto_convenio: _migDetalle.id_producto,
                    id_producto_convenio_detalle: _migDetalle.id_detalle,
                    adeudo_base: adeudo,
                    base_calculo: _migGetBaseSeleccionada(),
                    porcentaje_descuento: _pctEnvio,
                    pago_semanal: semanal,
                    fecha_inicio: fecha,
                    bucket_morosidad_real: bucket || '',
                    dias_mora: _migCredito.Dias_mora || 0,
                    avance_pago_plazo: _migCredito.Avance_Pago_Plazo || '',
                    monto_adicional: adicional > 0 ? adicional.toFixed(2) : null,
                    total_final_con_adicional: totalFinal.toFixed(2),
                };
            }

            http.request({
                endpoint: endpointFinal,
                method: 'POST',
                data: dataFinal,
                onSuccess: function (resp) {
                    var guardarBtnRestore = getGuardarBtn();
                    if (guardarBtnRestore) {
                        guardarBtnRestore.disabled = false;
                        guardarBtnRestore.innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';
                    }

                    if (!resp.success) {
                        var errBackend = document.getElementById('migErrorBackend');
                        if (!errBackend) {
                            errBackend = document.createElement('div');
                            errBackend.id = 'migErrorBackend';
                            var preamortWrap = document.getElementById('preamortTablaWrap');
                            if (preamortWrap && preamortWrap.parentNode) {
                                preamortWrap.parentNode.appendChild(errBackend);
                            }
                        }
                        errBackend.className = 'alert alert-danger mt-3';
                        errBackend.innerHTML = '<i class="fas fa-times-circle me-2"></i><strong>Error del servidor:</strong> ' + (resp.mensaje || 'Error desconocido');
                        errBackend.style.display = 'block';
                        return;
                    }

                    var errBackend = document.getElementById('migErrorBackend');
                    if (errBackend) errBackend.style.display = 'none';

                    var d = resp.datos || {};
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalMigracion'));
                    if (modal) modal.hide();

                    var _idParaRecargar2 = _migCredito ? _migCredito.Id_credito : null;
                    Swal.fire({
                        title: '¡Convenio registrado!',
                        html: (d.semanas_pagadas || 0) + ' de ' + (d.semanas_total || 0) + ' semanas marcadas como pagadas.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(function () {
                        if (_idParaRecargar2) {
                            document.getElementById('inputBusqueda').value = _idParaRecargar2;
                            window.buscarCredito();
                        } else {
                            location.reload();
                        }
                    });
                },
                onError: function () {
                    var guardarBtnRestore = getGuardarBtn();
                    if (guardarBtnRestore) {
                        guardarBtnRestore.disabled = false;
                        guardarBtnRestore.innerHTML = '<i class="fas fa-save"></i> Registrar Convenio';
                    }
                    Swal.fire('Error', 'Error de conexión.', 'error');
                }
            });
        }
    });
};

// ════════════════════════════════════════════════
// CONCILIACIÓN
// ════════════════════════════════════════════════

var _concilDatos = null;

function bindBotonesConciliacionManual() {
    document.querySelectorAll('.btn-conciliar').forEach(function (btn) {
        var nuevo = btn.cloneNode(true);
        btn.parentNode.replaceChild(nuevo, btn);
        nuevo.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var semana = nuevo.getAttribute('data-semana');
            var semanasGrupo = nuevo.getAttribute('data-semanas-grupo') || semana;
            var convenio = nuevo.getAttribute('data-convenio');
            var credito = nuevo.getAttribute('data-credito');
            window.abrirConciliacion(semana, convenio, credito, semanasGrupo);
        });
    });
}

window.abrirConciliacion = function (semana, idConvenio, idCredito, semanasGrupo) {
    var semanasArr = semanasGrupo ? semanasGrupo.split(',') : [semana];
    _concilDatos = {
        semana: semana,
        semanasGrupo: semanasArr,
        idConvenio: idConvenio,
        idCredito: idCredito
    };

    var tituloSemanas = semanasArr.length > 1
        ? 'Semanas ' + semanasArr.join(', ')
        : 'Semana ' + semana;

    document.getElementById('concilTitulo').textContent = tituloSemanas;
    document.getElementById('concilLoading').style.display = 'block';
    document.getElementById('concilContenido').style.display = 'none';
    document.getElementById('concilYaConciliado').style.display = 'none';
    document.getElementById('concilBtnGuardar').style.display = 'none';
    document.getElementById('concilArchivo').value = '';
    document.getElementById('concilComentario').value = '';
    document.getElementById('concilCharCount').textContent = '0';

    // Abrir modal manualmente sin Bootstrap
    var modalEl = document.getElementById('modalConciliacion');
    modalEl.style.display = 'block';
    modalEl.classList.add('show');
    modalEl.removeAttribute('aria-hidden');
    modalEl.setAttribute('aria-modal', 'true');

    // Agregar backdrop
    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'concilBackdrop';
    document.body.appendChild(backdrop);
    document.body.classList.add('modal-open');

    // Cerrar con la X
    modalEl.querySelector('.btn-close').onclick = function () {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        var bd = document.getElementById('concilBackdrop');
        if (bd) bd.remove();
        document.body.classList.remove('modal-open');
    };






    fetch('/convenios/getConciliacionSemana', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_convenio=' + encodeURIComponent(idConvenio) +
            '&numero_semana=' + encodeURIComponent(semana) +
            '&id_credito=' + encodeURIComponent(idCredito) +
            '&semanas_grupo=' + encodeURIComponent(semanasGrupo || semana)
    })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            document.getElementById('concilLoading').style.display = 'none';

            if (!resp.success) {
                Swal.fire('Error', resp.mensaje, 'error');
                return;
            }

            var d = resp.datos;

            if (d.conciliacion && d.conciliacion.estatus === 'conciliado') {
                document.getElementById('concilYaConciliado').style.display = 'block';
                document.getElementById('concilFechaPrevia').textContent =
                    'Conciliado el ' + (d.conciliacion.fecha_conciliacion || '—') +
                    ' por ' + (d.conciliacion.usuario_concilia || '—');
                return;
            }

            var fmt = function (v) {
                return '$' + parseFloat(v || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            };
            var fmtF = function (s) {
                if (!s) return '—';
                var p = s.split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            };

            var html = '';

            // ── Sección 1: Distribución S2Movil (crédito original) ──
            html += '<h6 class="fw-bold text-muted mb-2">' +
                '<i class="fas fa-code-branch me-2 text-warning"></i>' +
                'Distribución S2Movil — crédito original</h6>';

            if (!d.pagos_s2 || d.pagos_s2.length === 0) {
                html += '<div class="alert alert-warning">No se encontraron pagos en S2Movil para esta semana.</div>';
            } else {
                html += '<div style="position:relative;padding-left:24px;">';
                html += '<div style="position:absolute;left:8px;top:0;bottom:0;width:2px;background:#e2e8f0;"></div>';

                d.pagos_s2.forEach(function (pago) {
                    var tieneSobrante = pago.sobrante > 0;
                    var cuotasStr = pago.cuotas ? 'Cuota(s): ' + pago.cuotas : '';

                    // Nodo raíz — pago total
                    html +=
                        '<div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:4px;">' +
                        '<div style="padding-top:3px;">' +
                        '<div style="width:14px;height:14px;border-radius:50%;background:#1e293b;border:2px solid #fff;box-shadow:0 0 0 2px #1e293b;"></div>' +
                        '</div>' +
                        '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;padding:.6rem .9rem;flex:1;">' +
                        '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<span style="font-weight:700;font-size:.95rem;">' + fmt(pago.montoPago) + '</span>' +
                        '<span style="font-size:.75rem;color:#94a3b8;">' + fmtF(pago.fechaValor) + '</span>' +
                        '</div>' +
                        (cuotasStr ? '<span style="font-size:.72rem;color:#94a3b8;">' + cuotasStr + '</span>' : '') +
                        '</div>' +
                        '</div>';

                    // Rama capital
                    html +=
                        '<div style="display:flex;align-items:stretch;gap:10px;margin-bottom:4px;padding-left:6px;">' +
                        '<div style="display:flex;flex-direction:column;align-items:center;">' +
                        '<div style="width:1.5px;flex:1;background:#cbd5e1;min-height:8px;"></div>' +
                        '<div style="width:10px;height:10px;border-radius:50%;background:#22c55e;flex-shrink:0;border:2px solid #fff;box-shadow:0 0 0 1.5px #22c55e;"></div>' +
                        '<div style="width:1.5px;flex:' + (tieneSobrante ? '1' : '0') + ';min-height:' + (tieneSobrante ? '8px' : '0') + ';background:' + (tieneSobrante ? '#cbd5e1' : 'transparent') + ';"></div>' +
                        '</div>' +
                        '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.4rem;padding:.4rem .75rem;flex:1;margin:2px 0;">' +
                        '<span style="font-size:.82rem;font-weight:700;color:#166534;">✓ Capital: ' + fmt(pago.aplicado) + '</span>' +
                        '<span style="display:block;font-size:.70rem;color:#15803d;">Aplicado al crédito original</span>' +
                        '</div>' +
                        '</div>';

                    // Rama sobrante/interés (si existe)
                    if (tieneSobrante) {
                        html +=
                            '<div style="display:flex;align-items:stretch;gap:10px;padding-left:6px;margin-bottom:8px;">' +
                            '<div style="display:flex;flex-direction:column;align-items:center;">' +
                            '<div style="width:1.5px;flex:0;min-height:0;"></div>' +
                            '<div style="width:10px;height:10px;border-radius:50%;background:#f59e0b;flex-shrink:0;border:2px solid #fff;box-shadow:0 0 0 1.5px #f59e0b;"></div>' +
                            '<div style="width:1.5px;flex:1;background:transparent;"></div>' +
                            '</div>' +
                            '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.4rem;padding:.4rem .75rem;flex:1;margin:2px 0;">' +
                            '<span style="font-size:.82rem;font-weight:700;color:#d97706;">↷ Interés: ' + fmt(pago.sobrante) + '</span>' +
                            '<span style="display:block;font-size:.70rem;color:#b45309;">Aplicado a intereses y cargos</span>' +
                            '</div>' +
                            '</div>';
                    }
                });

                _concilDatos.monto_pago = d.pagos_s2[0].montoPago;
                _concilDatos.monto_aplicado = d.pagos_s2[0].aplicado;
                _concilDatos.monto_sobrante = d.pagos_s2[0].sobrante;
                _concilDatos.fecha_pago = d.pagos_s2[0].fechaValor;
            }

            // ── Sección 2: Aplicación al convenio (cascada) ──────────
            if (d.resumen_convenio && d.resumen_convenio.length > 0) {
                html += '<hr class="my-3">';
                html += '<h6 class="fw-bold text-muted mb-2">' +
                    '<i class="fas fa-handshake me-2" style="color:#764ba2;"></i>' +
                    'Aplicación al convenio</h6>';

                html += '<div style="background:#f8f5ff;border-radius:.75rem;padding:1rem;border:1px solid #e9d5ff;">';

                d.resumen_convenio.forEach(function (sem) {
                    var esParcial = sem.estatus_pago === 'parcial';
                    var esPagado = sem.estatus_pago === 'pagado';
                    var colorBorde = esParcial ? '#f59e0b' : '#22c55e';
                    var colorTexto = esParcial ? '#92400e' : '#166534';
                    var icono = esPagado ? '✓' : '◑';

                    html +=
                        '<div style="display:flex;align-items:center;justify-content:space-between;' +
                        'padding:.5rem .75rem;margin-bottom:.4rem;' +
                        'background:#fff;border-radius:.5rem;' +
                        'border-left:3px solid ' + colorBorde + ';">' +
                        '<div>' +
                        '<span style="font-size:.82rem;font-weight:700;color:' + colorTexto + ';">' +
                        icono + ' Semana ' + sem.numero_semana +
                        '</span>' +
                        '<span style="display:block;font-size:.72rem;color:#94a3b8;">' +
                        fmtF(sem.fecha_pago) +
                        '</span>' +
                        '</div>' +
                        '<div style="text-align:right;">' +
                        '<span style="font-size:.85rem;font-weight:700;color:' + colorTexto + ';">' +
                        fmt(sem.monto_pagado) +
                        '</span>' +
                        (esParcial
                            ? '<span style="display:block;font-size:.70rem;color:#dc2626;">' +
                            'Falta: ' + fmt(sem.faltante) + '</span>'
                            : '<span style="display:block;font-size:.70rem;color:#22c55e;">Pagado completo</span>') +
                        '</div>' +
                        '</div>';
                });

                // Total aplicado al convenio
                html +=
                    '<div style="display:flex;justify-content:space-between;padding:.5rem .75rem;' +
                    'border-top:1px solid #e9d5ff;margin-top:.4rem;">' +
                    '<span style="font-size:.82rem;font-weight:700;color:#5b2d8e;">Total aplicado al convenio</span>' +
                    '<span style="font-size:.85rem;font-weight:700;color:#5b2d8e;">' + fmt(d.total_aplicado_convenio) + '</span>' +
                    '</div>';

                html += '</div>';
            }

            document.getElementById('concilPagosWrap').innerHTML = html;
            document.getElementById('concilContenido').style.display = 'block';
            document.getElementById('concilBtnGuardar').style.display = 'inline-block';
        })
        .catch(function () {
            document.getElementById('concilLoading').style.display = 'none';
            Swal.fire('Error', 'Error de conexión.', 'error');
        });
};

document.getElementById('concilComentario').addEventListener('input', function () {
    document.getElementById('concilCharCount').textContent = this.value.length;
});

window.guardarConciliacion = function () {
    if (!_concilDatos) return;

    var archivo = document.getElementById('concilArchivo').files[0];
    var comentario = document.getElementById('concilComentario').value.trim();

    Swal.fire({
        title: '¿Confirmar conciliación?',
        html: 'Semana <strong>' + _concilDatos.semana + '</strong> quedará marcada como conciliada.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, conciliar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f59e0b',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        document.getElementById('concilBtnGuardar').disabled = true;
        document.getElementById('concilBtnGuardar').innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        var formData = new FormData();
        formData.append('id_convenio', _concilDatos.idConvenio);
        formData.append('numero_semana', _concilDatos.semana);
        formData.append('monto_pago', _concilDatos.monto_pago || 0);
        formData.append('monto_aplicado', _concilDatos.monto_aplicado || 0);
        formData.append('monto_sobrante', _concilDatos.monto_sobrante || 0);
        formData.append('fecha_pago', _concilDatos.fecha_pago || '');
        formData.append('comentario', comentario);
        if (archivo) formData.append('comprobante', archivo);

        fetch('/convenios/guardarConciliacion', {
            method: 'POST',
            body: formData
        })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                document.getElementById('concilBtnGuardar').disabled = false;
                document.getElementById('concilBtnGuardar').innerHTML =
                    '<i class="fas fa-check me-1"></i>Confirmar Conciliación';

                if (!resp.success) {
                    var errBackend = document.getElementById('migErrorBackend');
                    if (!errBackend) {
                        errBackend = document.createElement('div');
                        errBackend.id = 'migErrorBackend';
                        var preamortWrap = document.getElementById('preamortTablaWrap');
                        if (preamortWrap && preamortWrap.parentNode) {
                            preamortWrap.parentNode.appendChild(errBackend);
                        }
                    }
                    errBackend.className = 'alert alert-danger mt-3';
                    errBackend.innerHTML = '<i class="fas fa-times-circle me-2"></i><strong>Error del servidor:</strong> ' + resp.mensaje;
                    errBackend.style.display = 'block';
                    return;
                }
                var errBackend = document.getElementById('migErrorBackend');
                if (errBackend) errBackend.style.display = 'none';

                var modalEl = document.getElementById('modalConciliacion');
                modalEl.style.display = 'none';
                modalEl.classList.remove('show');
                var bd = document.getElementById('concilBackdrop');
                if (bd) bd.remove();
                document.body.classList.remove('modal-open');

                Swal.fire({
                    title: '¡Conciliado!',
                    text: 'La semana ' + _concilDatos.semana + ' fue conciliada correctamente.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function () {
                    location.reload();
                });
            })
            .catch(function () {
                document.getElementById('concilBtnGuardar').disabled = false;
                document.getElementById('concilBtnGuardar').innerHTML =
                    '<i class="fas fa-check me-1"></i>Confirmar Conciliación';
                Swal.fire('Error', 'Error de conexión.', 'error');
            });
    });
};


// ════════════════════════════════════════════════
// SUBIR COMPROBANTE — semanas vencidas
// ════════════════════════════════════════════════

var _subirCompDatos = null;

function bindBotonesSubirComprobante() {
    document.querySelectorAll('.btn-subir-comprobante').forEach(function (btn) {
        var nuevo = btn.cloneNode(true);
        btn.parentNode.replaceChild(nuevo, btn);
        nuevo.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.abrirSubirComprobante(
                nuevo.getAttribute('data-semana'),
                nuevo.getAttribute('data-convenio'),
                nuevo.getAttribute('data-credito'),
                nuevo.getAttribute('data-pago-semanal'),
                nuevo.getAttribute('data-fecha-pago')
            );
        });
    });
}

function bindBotonesVerComprobante() {
    document.querySelectorAll('.btn-ver-comprobante').forEach(function (btn) {
        var nuevo = btn.cloneNode(true);
        btn.parentNode.replaceChild(nuevo, btn);
        nuevo.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.abrirVerComprobante(
                nuevo.getAttribute('data-semana'),
                nuevo.getAttribute('data-path'),
                nuevo.getAttribute('data-fecha-pago'),
                nuevo.getAttribute('data-pago-semanal'),
                nuevo.getAttribute('data-fecha-pago-real'),
                nuevo.getAttribute('data-comentario'),
                nuevo.getAttribute('data-convenio'),
                nuevo.getAttribute('data-credito')
            );
        });
    });
}

window.abrirSubirComprobante = function (semana, idConvenio, idCredito, pagoSemanal, fechaPago) {
    _subirCompDatos = {
        semana: semana,
        idConvenio: idConvenio,
        idCredito: idCredito,
        pagoSemanal: pagoSemanal,
    };

    // Título
    document.getElementById('subirCompTitulo').textContent = 'Semana ' + semana;

    // Info de la semana
    var fmtFechaLocal = function (s) {
        if (!s) return '—';
        var p = s.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };
    var fmtMoneda = function (v) {
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    document.getElementById('subirCompInfo').innerHTML =
        '<div class="col-6 col-md-4">' +
        '<div class="border rounded p-2 text-center">' +
        '<div class="small text-muted">Semana</div>' +
        '<div class="fw-bold fs-5">' + semana + '</div>' +
        '</div>' +
        '</div>' +
        '<div class="col-6 col-md-4">' +
        '<div class="border rounded p-2 text-center">' +
        '<div class="small text-muted">Fecha preferencial</div>' +
        '<div class="fw-bold">' + fmtFechaLocal(fechaPago) + '</div>' +
        '</div>' +
        '</div>' +
        '<div class="col-6 col-md-4">' +
        '<div class="border rounded p-2 text-center">' +
        '<div class="small text-muted">Pago semanal</div>' +
        '<div class="fw-bold text-success">' + fmtMoneda(pagoSemanal) + '</div>' +
        '</div>' +
        '</div>';

    // Reset campos - SOLO los que existen en el HTML
    document.getElementById('subirCompFecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('subirCompArchivo').value = '';
    document.getElementById('subirCompComentario').value = '';

    // Abrir modal
    var modalEl = document.getElementById('modalSubirComprobante');
    modalEl.style.display = 'block';
    modalEl.classList.add('show');
    document.body.classList.add('modal-open');

    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'subirCompBackdrop';
    document.body.appendChild(backdrop);

    var cerrar = function () {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
        var bd = document.getElementById('subirCompBackdrop');
        if (bd) bd.remove();
    };

    document.getElementById('subirCompBtnClose').onclick = cerrar;
    document.getElementById('subirCompBtnCancelar').onclick = cerrar;
};

function _cargarSemanasVencidas(idConvenio, semanaActual) {
    // Buscar en la tabla amortización actual las semanas vencidas
    var filas = document.querySelectorAll('#tablaAmortBody tr');
    var opciones = '';

    filas.forEach(function (fila) {
        var celdaEstatus = fila.querySelector('.celda-estatus');
        var celdaNombre = fila.querySelector('td:first-child');
        if (!celdaEstatus || !celdaNombre) return;

        var esVencido = celdaEstatus.innerHTML.indexOf('Vencido') !== -1;
        if (!esVencido) return;

        var textoSemana = celdaNombre.textContent.trim(); // "Semana 4"
        var numSemana = textoSemana.replace('Semana ', '').trim();

        // La semana actual ya está incluida siempre
        var checked = numSemana === String(semanaActual) ? 'checked disabled' : '';
        var labelExtra = numSemana === String(semanaActual) ? ' (actual)' : '';

        opciones +=
            '<div class="form-check form-check-inline">' +
            '<input class="form-check-input semana-aplica-check" type="checkbox" ' +
            'id="semAplica_' + numSemana + '" ' +
            'value="' + numSemana + '" ' + checked + '>' +
            '<label class="form-check-label" for="semAplica_' + numSemana + '">' +
            textoSemana + labelExtra +
            '</label>' +
            '</div>';
    });

    document.getElementById('semanasAplicaOpciones').innerHTML = opciones;
}

window.toggleSemanasAplica = function (mostrar) {
    document.getElementById('semanasAplicaWrap').style.display = mostrar ? 'block' : 'none';
};

window.guardarSubirComprobante = function () {
    var semana = _subirCompDatos.semana;
    var idConvenio = _subirCompDatos.idConvenio;
    var fecha = document.getElementById('subirCompFecha').value;
    var archivo = document.getElementById('subirCompArchivo').files[0];
    var comentario = document.getElementById('subirCompComentario').value.trim();

    // Validaciones
    if (!fecha) {
        Swal.fire('Campo requerido', 'Ingresa la fecha del pago.', 'warning');
        return;
    }
    if (!archivo) {
        Swal.fire('Comprobante requerido',
            'Debes adjuntar el comprobante de pago para continuar.', 'warning');
        return;
    }

    // Deshabilitar botón mientras se procesa
    document.getElementById('subirCompBtnGuardar').disabled = true;
    document.getElementById('subirCompBtnGuardar').innerHTML =
        '<i class="fas fa-spinner fa-spin me-1"></i>Subiendo...';

    // Crear FormData con SOLO los campos que necesita el backend
    var formData = new FormData();
    formData.append('id_convenio', idConvenio);
    formData.append('numero_semana', semana);
    formData.append('fecha_pago_real', fecha);
    formData.append('comprobante', archivo);

    // Comentario es opcional
    if (comentario) {
        formData.append('comentario', comentario);
    }

    // ⚠️ IMPORTANTE: NO enviamos 'monto_pagado' porque el backend
    // debe calcularlo automáticamente del pago_semanal del convenio
    // según el modelo Convenios::subirComprobante()

    fetch('/convenios/subirComprobante', {
        method: 'POST',
        body: formData
    })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            // Restaurar botón
            document.getElementById('subirCompBtnGuardar').disabled = false;
            document.getElementById('subirCompBtnGuardar').innerHTML =
                '<i class="fas fa-upload me-1"></i>Subir Comprobante';

            if (!resp.success) {
                Swal.fire('Error', resp.mensaje, 'error');
                return;
            }

            // Cerrar modal manualmente
            var modalEl = document.getElementById('modalSubirComprobante');
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');

            var bd = document.getElementById('subirCompBackdrop');
            if (bd) bd.remove();

            // Mostrar éxito y recargar
            Swal.fire({
                title: '¡Comprobante subido!',
                html: 'Semana <strong>' + semana + '</strong> marcada como pendiente de conciliar.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(function () {
                location.reload();
            });
        })
        .catch(function (error) {
            console.error('Error:', error);
            document.getElementById('subirCompBtnGuardar').disabled = false;
            document.getElementById('subirCompBtnGuardar').innerHTML =
                '<i class="fas fa-upload me-1"></i>Subir Comprobante';
            Swal.fire('Error', 'Error de conexión.', 'error');
        });
};

// ════════════════════════════════════════════════
// VER COMPROBANTE — semanas pendiente_conciliar / pagadas
// ════════════════════════════════════════════════
window.abrirVerComprobante = function (semana, path, fechaPago, pagoSemanal, fechaPagoReal, comentario, idConvenio, idCredito) {
    document.getElementById('verCompTitulo').textContent = 'Semana ' + semana;

    // Guardar datos en el modal para el reemplazo
    var modal = document.getElementById('modalVerComprobante');
    modal.dataset.semana    = semana;
    modal.dataset.path      = path || '';
    modal.dataset.idConvenio = idConvenio || '';
    modal.dataset.idCredito  = idCredito  || '';

    var fmtFechaLocal = function (s) {
        if (!s) return '—';
        var p = s.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };
    var fmtMoneda = function (v) {
        if (!v) return '—';
        return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    // Info
    document.getElementById('verCompInfo').innerHTML =
        '<div class="col-6 col-md-4">' +
        '<div class="border rounded p-2 text-center">' +
        '<div class="small text-muted">Semana</div>' +
        '<div class="fw-bold fs-5">' + semana + '</div>' +
        '</div>' +
        '</div>' +
        '<div class="col-6 col-md-4">' +
        '<div class="border rounded p-2 text-center">' +
        '<div class="small text-muted">Fecha preferencial</div>' +
        '<div class="fw-bold">' + fmtFechaLocal(fechaPago) + '</div>' +
        '</div>' +
        '</div>' +
        '<div class="col-6 col-md-4">' +
        '<div class="border rounded p-2 text-center">' +
        '<div class="small text-muted">Fecha pago registrada</div>' +
        '<div class="fw-bold text-success">' + fmtFechaLocal(fechaPagoReal) + '</div>' +
        '</div>' +
        '</div>';

    // Visualizador
    var ext = path ? path.split('.').pop().toLowerCase() : '';
    var vis = '';
    if (ext === 'pdf') {
        vis = '<iframe src="' + path + '" width="100%" height="420px" ' +
            'style="border:1px solid #e2e8f0;border-radius:.5rem;"></iframe>';
    } else if (['jpg', 'jpeg', 'png'].indexOf(ext) !== -1) {
        vis = '<img src="' + path + '" style="max-width:100%;max-height:420px;' +
            'border-radius:.5rem;border:1px solid #e2e8f0;" alt="Comprobante">';
    } else {
        vis = '<div class="alert alert-warning">No se puede previsualizar este archivo. ' +
            '<a href="' + path + '" target="_blank">Abrir en nueva pestaña</a></div>';
    }
    document.getElementById('verCompVisualizador').innerHTML = vis;

    // Comentario
    if (comentario && comentario.trim() !== '') {
        document.getElementById('verCompComentario').value = comentario;
        document.getElementById('verCompComentarioWrap').style.display = 'block';
    } else {
        document.getElementById('verCompComentarioWrap').style.display = 'none';
    }

    // Abrir modal
    var modalEl = document.getElementById('modalVerComprobante');
    modalEl.style.display = 'block';
    modalEl.classList.add('show');
    document.body.classList.add('modal-open');
    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'verCompBackdrop';
    document.body.appendChild(backdrop);

    var cerrar = function () {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
        var bd = document.getElementById('verCompBackdrop');
        if (bd) bd.remove();
    };

    document.getElementById('verCompBtnClose').onclick = cerrar;
    document.getElementById('verCompBtnCerrar').onclick = cerrar;
};

// ════════════════════════════════════════════════
// REEMPLAZAR COMPROBANTE
// ════════════════════════════════════════════════
window.reemplazarComprobante = function () {
    var modal      = document.getElementById('modalVerComprobante');
    var semana     = modal.dataset.semana;
    var idConvenio = modal.dataset.idConvenio;
    var idCredito  = modal.dataset.idCredito;

    if (!idConvenio) {
        Swal.fire('Sin datos', 'No se pudo identificar el convenio.', 'warning');
        return;
    }

    var fileInput = document.getElementById('verCompArchivoReemplazar');
    fileInput.value = '';
    fileInput.onchange = function () {
        var archivo = fileInput.files[0];
        if (!archivo) return;

        var btn = document.getElementById('verCompBtnReemplazar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Subiendo...';

        var formData = new FormData();
        formData.append('id_convenio',    idConvenio);
        formData.append('numero_semana',  semana);
        formData.append('fecha_pago_real', new Date().toISOString().split('T')[0]);
        formData.append('comprobante',    archivo);

        fetch('/convenios/subirComprobante', {
            method: 'POST',
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-rotate me-1"></i>Reemplazar comprobante';

            if (!resp.success) {
                Swal.fire('Error', resp.mensaje, 'error');
                return;
            }

            Swal.fire({
                title: '¡Comprobante reemplazado!',
                html: 'Semana <strong>' + semana + '</strong> actualizada correctamente.',
                icon: 'success',
                timer: 1800,
                showConfirmButton: false
            }).then(function () {
                var base = window.location.pathname;
                window.location.href = base + (idCredito ? '?credito=' + idCredito : '');
            });
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-rotate me-1"></i>Reemplazar comprobante';
            Swal.fire('Error', 'Error de conexión al subir el archivo.', 'error');
        });
    };

    fileInput.click();
};

// ═══════════════════════════════════════════════════════════════════════
// CONVENIO PAGO GLOBO - FUNCIONES COMPLETAS
// ═══════════════════════════════════════════════════════════════════════

var _globoCredito = null;

window.globoBuscarCredito = function () {
    var idCredito = parseInt(document.getElementById('globoIdCredito').value);
    var info = document.getElementById('globoInfoCliente');

    if (!idCredito || idCredito < 1) {
        Swal.fire('Campo requerido', 'Ingresa un ID de crédito válido.', 'warning');
        return;
    }

    document.getElementById('globoStep2').classList.add('d-none');
    document.getElementById('globoBtnGuardar').style.display = 'none';
    info.classList.remove('d-none');
    info.className = 'alert alert-info';
    info.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Buscando crédito...';

    http.request({
        endpoint: '/convenios/getOfertasCredito',
        method: 'POST',
        data: { id_credito: idCredito },
        onSuccess: function (respOfertas) {
            if (!respOfertas.success || !respOfertas.datos || !respOfertas.datos.credito) {
                info.className = 'alert alert-danger d-none';
                if (respOfertas.success && respOfertas.datos && respOfertas.datos.statusCredito === 'Saldado') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Crédito Saldado',
                        html: 'Este crédito ya se encuentra <strong>saldado</strong>.' +
                            (respOfertas.datos.fechaLiquidacion ? '<br>Fecha de liquidación: <strong>' + respOfertas.datos.fechaLiquidacion + '</strong>' : '') +
                            (respOfertas.datos.motivo ? '<br>Motivo: ' + respOfertas.datos.motivo : ''),
                        confirmButtonColor: '#22c55e'
                    });
                } else {
                    Swal.fire('Crédito no encontrado', respOfertas.mensaje || 'El crédito ingresado no existe o no es elegible.', 'error');
                }
                return;
            }

            var credito = respOfertas.datos.credito;

            http.request({
                endpoint: '/convenios/getConvenioActivo',
                method: 'POST',
                data: { id_credito: idCredito },
                onSuccess: function (respConvenio) {
                    if (respConvenio.success && respConvenio.datos &&
                        respConvenio.datos.estatus === 'activo') {
                        info.className = 'alert alert-warning d-none';
                        Swal.fire('Convenio activo', credito.Nombre_cliente + ' — Crédito #' + credito.Id_credito + ' ya tiene un convenio activo registrado.', 'warning');
                        return;
                    }

                    _globoCredito = credito;

                    var adeudo = parseFloat(credito.Adeudo_total || 0);
                    var fmt = function (v) {
                        return '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 });
                    };

                    document.getElementById('globoNombreCliente').textContent = credito.Nombre_cliente;
                    document.getElementById('globoAdeudoTotal').textContent = fmt(adeudo);
                    document.getElementById('globoBucket').textContent = credito.Bucket_Morosidad_Real || '—';

                    info.className = 'alert alert-success';
                    info.innerHTML =
                        '<div class="d-flex align-items-start gap-2">' +
                        '<i class="fas fa-check-circle fa-lg mt-1 text-success"></i>' +
                        '<div>' +
                        '<strong>' + credito.Nombre_cliente + '</strong> — Crédito #' + credito.Id_credito + '<br>' +
                        '<small>Bucket: ' + (credito.Bucket_Morosidad_Real || '—') + ' | Adeudo: ' + fmt(adeudo) + '</small>' +
                        '</div>' +
                        '</div>';

                    var fechaDefault = new Date();
                    fechaDefault.setDate(fechaDefault.getDate() + 7);
                    document.getElementById('globoFechaPrimerPago').value =
                        fechaDefault.toISOString().split('T')[0];

                    document.getElementById('globoStep2').classList.remove('d-none');
                    window.globoCalcularPorcentaje();
                },
                onError: function () {
                    info.className = 'alert alert-danger';
                    info.innerHTML = '<i class="fas fa-times-circle me-2"></i>Error al validar convenio activo.';
                }
            });
        },
        onError: function () {
            info.className = 'alert alert-danger';
            info.innerHTML = '<i class="fas fa-times-circle me-2"></i>Error de conexión.';
        }
    });
};

window.globoCalcularPorcentaje = function () {
    if (!_globoCredito) return;

    var adeudoOriginal = parseFloat(_globoCredito.Adeudo_total || 0);
    var porcentaje = parseFloat(document.getElementById('globoPorcentajeDescuento').value) || 0;

    if (porcentaje > 100) porcentaje = 100;
    if (porcentaje < 0) porcentaje = 0;
    document.getElementById('globoPorcentajeDescuento').value = porcentaje;

    var totalConDescuento = adeudoOriginal * (1 - porcentaje / 100);
    var fmt = function (v) {
        return '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    document.getElementById('globoTotalConDescuento').value = fmt(totalConDescuento);

    var pagosIgualesMonto = parseFloat(document.getElementById('globoPagosIgualesMonto').value) || 0;
    var pagoGloboMonto = parseFloat(document.getElementById('globoPagoGloboMonto').value) || 0;

    if (pagosIgualesMonto === 0 && pagoGloboMonto === 0 && porcentaje > 0) {
        window.globoSugerirMontos();
    } else {
        window.globoRecalcular();
    }
};

window.globoSugerirMontos = function () {
    if (!_globoCredito) return;

    var adeudoOriginal = parseFloat(_globoCredito.Adeudo_total || 0);
    var porcentaje = parseFloat(document.getElementById('globoPorcentajeDescuento').value) || 0;
    var pagosIgualesCant = parseInt(document.getElementById('globoPagosIgualesCant').value) || 4;

    var totalConDescuento = adeudoOriginal * (1 - porcentaje / 100);
    var totalAPagar = totalConDescuento;

    var pagoIgualBase = Math.floor(totalAPagar / (pagosIgualesCant + 1) * 100) / 100;
    var sumaIguales = pagoIgualBase * pagosIgualesCant;
    var pagoGlobo = Math.round((totalAPagar - sumaIguales) * 100) / 100;

    if (pagoGlobo < 0) pagoGlobo = 0.01;

    document.getElementById('globoPagosIgualesMonto').value = pagoIgualBase.toFixed(2);
    document.getElementById('globoPagoGloboMonto').value = pagoGlobo.toFixed(2);

    window.globoRecalcular();
};

window.globoRecalcular = function () {
    if (!_globoCredito) return;

    var adeudoOriginal = parseFloat(_globoCredito.Adeudo_total || 0);
    var porcentaje = parseFloat(document.getElementById('globoPorcentajeDescuento').value) || 0;
    var pagosIgualesCant = parseInt(document.getElementById('globoPagosIgualesCant').value) || 0;
    var pagosIgualesMonto = parseFloat(document.getElementById('globoPagosIgualesMonto').value) || 0;
    var pagoGloboMonto = parseFloat(document.getElementById('globoPagoGloboMonto').value) || 0;

    var totalConDescuento = adeudoOriginal * (1 - porcentaje / 100);
    var sumCalculada = (pagosIgualesCant * pagosIgualesMonto) + pagoGloboMonto;
    var descuentoAplicado = adeudoOriginal - sumCalculada;
    var porcentajeAplicado = adeudoOriginal > 0 ? (descuentoAplicado / adeudoOriginal) * 100 : 0;

    var fmt = function (v) {
        return '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    document.getElementById('globoSumaIguales').innerHTML = fmt(pagosIgualesCant * pagosIgualesMonto);
    document.getElementById('globoDetalleIguales').innerHTML = pagosIgualesCant + ' × ' + fmt(pagosIgualesMonto);
    document.getElementById('globoSumaGlobo').innerHTML = fmt(pagoGloboMonto);
    document.getElementById('globoTotalPagar').innerHTML = fmt(sumCalculada);
    document.getElementById('globoDescuento').innerHTML = fmt(descuentoAplicado);
    document.getElementById('globoPorcentajeDescuentoMostrar').innerHTML = porcentajeAplicado.toFixed(2) + '%';
    document.getElementById('globoTotalConDescuento').value = fmt(totalConDescuento);

    var errorDiv = document.getElementById('globoErrorMontos');
    var guardarBtn = document.getElementById('globoBtnGuardar');

    if (pagosIgualesMonto === 0 && pagoGloboMonto === 0) {
        errorDiv.style.display = 'block';
        errorDiv.className = 'alert alert-info mt-3';
        errorDiv.innerHTML =
            '<i class="fas fa-info-circle me-2"></i>' +
            '<strong>Configura el convenio:</strong> Ingresa los montos de los pagos iguales y el pago globo final.';
        guardarBtn.style.display = 'none';
        return;
    }

    var diferencia = Math.abs(sumCalculada - totalConDescuento);

    if (diferencia > 1.00) {
        errorDiv.style.display = 'block';
        if (sumCalculada > totalConDescuento) {
            errorDiv.className = 'alert alert-danger mt-3';
            errorDiv.innerHTML =
                '<i class="fas fa-times-circle me-2"></i>' +
                '<strong>El total excede el monto permitido:</strong> La suma de los pagos (' +
                fmt(sumCalculada) + ') es mayor al total con descuento (' +
                fmt(totalConDescuento) + ').';
        } else {
            errorDiv.className = 'alert alert-warning mt-3';
            errorDiv.innerHTML =
                '<i class="fas fa-calculator me-2"></i>' +
                '<strong>Falta ' + fmt(totalConDescuento - sumCalculada) + ' para completar el total.</strong><br>' +
                'Total con descuento: ' + fmt(totalConDescuento) + '<br>' +
                'Suma actual: ' + fmt(sumCalculada);
        }
        guardarBtn.style.display = 'none';
        return;
    }

    errorDiv.style.display = 'block';
    if (porcentaje > 0) {
        errorDiv.className = 'alert alert-success mt-3';
        errorDiv.innerHTML =
            '<i class="fas fa-gift me-2"></i>' +
            '<strong>¡Convenio válido con ' + porcentaje + '% de descuento!</strong> ' +
            'El cliente pagará <strong>' + fmt(sumCalculada) + '</strong> en ' +
            (pagosIgualesCant + 1) + ' pagos, ahorrando <strong>' + fmt(descuentoAplicado) + '</strong>.';
    } else {
        errorDiv.className = 'alert alert-success mt-3';
        errorDiv.innerHTML =
            '<i class="fas fa-check-circle me-2"></i>' +
            '<strong>¡Convenio válido!</strong> El cliente pagará <strong>' + fmt(sumCalculada) + '</strong> en ' +
            (pagosIgualesCant + 1) + ' pagos.';
    }

    guardarBtn.style.display = 'inline-block';
    window.globoActualizarPreview();
};

window.globoActualizarPreview = function () {
    var frecuencia = document.getElementById('globoFrecuencia').value;
    var fechaPrimerPago = document.getElementById('globoFechaPrimerPago').value;
    var pagosIgualesCant = parseInt(document.getElementById('globoPagosIgualesCant').value) || 0;
    var pagosIgualesMonto = parseFloat(document.getElementById('globoPagosIgualesMonto').value) || 0;
    var pagoGloboMonto = parseFloat(document.getElementById('globoPagoGloboMonto').value) || 0;

    var totalPagos = pagosIgualesCant + 1;
    var diasIntervalo = frecuencia === 'quincenal' ? 14 : 7;

    var fmt = function (v) {
        return '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    var fmtFecha = function (fechaISO) {
        if (!fechaISO) return '—';
        var p = fechaISO.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };

    var html = '';
    for (var i = 1; i <= totalPagos; i++) {
        var esGlobo = (i === totalPagos);
        var monto = esGlobo ? pagoGloboMonto : pagosIgualesMonto;
        var tipo = esGlobo ? '<span class="badge" style="background:#764ba2;">Pago Globo</span>' :
            '<span class="badge bg-secondary">Pago igual</span>';

        var fechaPago = '—';
        if (fechaPrimerPago) {
            var fecha = new Date(fechaPrimerPago + 'T00:00:00');
            fecha.setDate(fecha.getDate() + (i - 1) * diasIntervalo);
            fechaPago = fmtFecha(fecha.toISOString().split('T')[0]);
        }

        html += '<tr>' +
            '<td>' + i + '</td>' +
            '<td>' + tipo + '</td>' +
            '<td class="fw-bold ' + (esGlobo ? 'text-primary' : '') + '">' + fmt(monto) + '</td>' +
            '<td>' + fechaPago + '</td>' +
            '</tr>';
    }

    document.getElementById('globoPagosPreviewBody').innerHTML = html;
};

window.globoVerTablaAmortizacion = function () {
    if (!_globoCredito) return;

    var frecuencia = document.getElementById('globoFrecuencia').value;
    var fechaPrimerPago = document.getElementById('globoFechaPrimerPago').value;
    var pagosIgualesCant = parseInt(document.getElementById('globoPagosIgualesCant').value) || 0;
    var pagosIgualesMonto = parseFloat(document.getElementById('globoPagosIgualesMonto').value) || 0;
    var pagoGloboMonto = parseFloat(document.getElementById('globoPagoGloboMonto').value) || 0;
    var totalAPagar = (pagosIgualesCant * pagosIgualesMonto) + pagoGloboMonto;

    var totalPagos = pagosIgualesCant + 1;
    var diasIntervalo = frecuencia === 'quincenal' ? 14 : 7;
    var saldoActual = totalAPagar;

    var fmt = function (v) {
        return '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    };

    var fmtFecha = function (fechaISO) {
        if (!fechaISO) return '—';
        var p = fechaISO.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    };

    var filas = '';
    for (var p = 1; p <= totalPagos; p++) {
        var esUltimo = (p === totalPagos);
        var monto = esUltimo ? pagoGloboMonto : pagosIgualesMonto;
        var capital = esUltimo ? saldoActual : monto;
        saldoActual = Math.round((saldoActual - capital) * 100) / 100;
        if (saldoActual < 0) saldoActual = 0;

        var fechaPago = '—';
        if (fechaPrimerPago) {
            var fecha = new Date(fechaPrimerPago + 'T00:00:00');
            fecha.setDate(fecha.getDate() + (p - 1) * diasIntervalo);
            fechaPago = fmtFecha(fecha.toISOString().split('T')[0]);
        }

        filas += '<tr>' +
            '<td class="text-center">' + p + '</td>' +
            '<td>' + fmtFecha(fechaPago) + '</td>' +
            '<td class="text-end">' + fmt(monto) + '</td>' +
            '<td class="text-end">' + fmt(capital) + '</td>' +
            '<td class="text-end">' + fmt(saldoActual) + '</td>' +
            '</tr>';
    }

    Swal.fire({
        title: '<i class="fas fa-table me-2"></i>Tabla de Amortización - Pago Globo',
        html: '<div class="table-responsive" style="max-height: 400px;">' +
            '<table class="table table-sm table-bordered">' +
            '<thead class="table-dark">' +
            '<tr><th># Pago</th><th>Fecha Estimada</th><th>Pago Programado</th>' +
            '<th>Capital</th><th>Saldo Restante</th></tr>' +
            '</thead>' +
            '<tbody>' + filas + '</tbody>' +
            '</table>' +
            '</div>' +
            '<hr>' +
            '<div class="text-start">' +
            '<strong>Resumen:</strong><br>' +
            'Total a pagar: ' + fmt(totalAPagar) + '<br>' +
            'Frecuencia: ' + (frecuencia === 'quincenal' ? 'Quincenal' : 'Semanal') + '<br>' +
            'Total de pagos: ' + totalPagos + ' (incluye pago globo)' +
            '</div>',
        width: '800px',
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#764ba2'
    });
};

window.globoValidarPdf = function (input) {
    var preview = document.getElementById('globoPdfPreview');
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

    if (file.size > 5 * 1024 * 1024) {
        Swal.fire('Archivo muy grande', 'El PDF no debe exceder 5MB', 'warning');
        input.value = '';
        preview.innerHTML = '';
        return;
    }

    preview.innerHTML = '<i class="fas fa-file-pdf" style="font-size:2rem;color:#dc2626;"></i>';
};

window.globoGuardar = function () {
    if (!_globoCredito) {
        Swal.fire('Error', 'Primero busca un crédito válido.', 'warning');
        return;
    }

    // 1. Captura de valores de los inputs
    var pagosIgualesCant = parseInt(document.getElementById('globoPagosIgualesCant').value) || 0;
    var pagosIgualesMonto = parseFloat(document.getElementById('globoPagosIgualesMonto').value) || 0;
    var pagoGloboMonto = parseFloat(document.getElementById('globoPagoGloboMonto').value) || 0;
    var frecuencia = document.getElementById('globoFrecuencia').value;
    var fechaPrimerPago = document.getElementById('globoFechaPrimerPago').value;
    var porcentaje = parseFloat(document.getElementById('globoPorcentajeDescuento').value) || 0;

    var pagoInicialEl = document.getElementById('globoPagoInicial');
    var pagoInicial = pagoInicialEl ? (parseFloat(pagoInicialEl.value) || 0) : 0;

    var pdfFile = document.getElementById('globoPdfAdjunto').files[0];

    // 2. Cálculos de Validación (CORREGIDO: Incluye pago inicial)
    // El total a pagar debe ser la suma de todos los componentes del flujo
    var totalAPagar = pagoInicial + (pagosIgualesCant * pagosIgualesMonto) + pagoGloboMonto;

    var adeudoOriginal = parseFloat(_globoCredito.Adeudo_total || 0);
    var totalEsperadoConDescuento = adeudoOriginal * (1 - porcentaje / 100);

    // 3. Validaciones de Negocio
    if (adeudoOriginal <= 0) {
        Swal.fire('Validación', 'El adeudo del crédito es 0 o no está disponible. No se puede registrar el convenio.', 'error');
        return;
    }
    if (pagosIgualesCant < 1) {
        Swal.fire('Validación', 'La cantidad de pagos iguales debe ser al menos 1.', 'warning');
        return;
    }
    if (pagosIgualesMonto <= 0 || pagoGloboMonto <= 0) {
        Swal.fire('Validación', 'Los montos de los pagos deben ser mayores a 0.', 'warning');
        return;
    }
    if (!fechaPrimerPago) {
        Swal.fire('Validación', 'Selecciona la fecha del primer pago.', 'warning');
        return;
    }

    // Validar contra el descuento ofrecido (tolerancia de $1.00)
    if (Math.abs(totalAPagar - totalEsperadoConDescuento) > 1.00) {
        Swal.fire('Validación', 'Los montos (Inicial + Semanas + Globo) no coinciden con el total con descuento. Revisa los valores.', 'warning');
        return;
    }

    // 4. Confirmación con el usuario
    Swal.fire({
        title: '¿Registrar convenio con pago globo?',
        html: `<strong>${_globoCredito.Nombre_cliente}</strong><br>
               ${porcentaje > 0 ? 'Descuento aplicado: <strong>' + porcentaje + '%</strong><br>' : ''}
               ${pagoInicial > 0 ? 'Pago inicial (Enganche): <strong>$' + pagoInicial.toLocaleString('es-MX') + '</strong><br>' : ''}
               Total a pagar: <strong>$' + ${totalAPagar.toLocaleString('es-MX')} + '</strong><br>
               Pagos iguales: ${pagosIgualesCant} × $${pagosIgualesMonto.toLocaleString('es-MX')}<br>
               Pago globo final: $${pagoGloboMonto.toLocaleString('es-MX')}
               ${pdfFile ? '<br><br><small class="text-success">📎 Adjunto: ' + pdfFile.name + '</small>' : ''}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#22c55e'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var btn = document.getElementById('globoBtnGuardar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

        // 5. Preparación del FormData (Único objeto)
        var formData = new FormData();
        formData.append('id_credito', _globoCredito.Id_credito);
        formData.append('nombre_cliente', _globoCredito.Nombre_cliente);
        formData.append('bucket_morosidad_real', _globoCredito.Bucket_Morosidad_Real || '');
        formData.append('dias_mora', _globoCredito.Dias_mora || 0);
        formData.append('avance_pago_plazo', _globoCredito.Avance_Pago_Plazo || '');
        formData.append('adeudo_total_original', adeudoOriginal);
        formData.append('total_a_pagar', totalAPagar.toFixed(2));
        formData.append('pagos_iguales_cantidad', pagosIgualesCant);
        formData.append('pagos_iguales_monto', pagosIgualesMonto);
        formData.append('pago_globo_monto', pagoGloboMonto);
        formData.append('frecuencia', frecuencia);
        formData.append('fecha_primer_pago', fechaPrimerPago);
        formData.append('porcentaje_descuento', porcentaje);
        formData.append('pago_inicial_monto', pagoInicial); // Llave que espera el nuevo Model
        formData.append('usuario_alta', window.usuarioActual || 'sistema');

        if (pdfFile) {
            formData.append('pdf_adjunto', pdfFile);
        }

        // 6. Envío al Servidor
        http.request({
            endpoint: '/convenios/registrarConvenioGlobo',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            onSuccess: function (resp) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Convenio Globo';

                if (!resp.success) {
                    // Manejo de errores visual en el formulario
                    var errBackend = document.getElementById('migErrorBackend');
                    if (!errBackend) {
                        errBackend = document.createElement('div');
                        errBackend.id = 'migErrorBackend';
                        var preamortWrap = document.getElementById('preamortTablaWrap');
                        if (preamortWrap) preamortWrap.parentNode.appendChild(errBackend);
                    }
                    errBackend.className = 'alert alert-danger mt-3';
                    errBackend.innerHTML = '<i class="fas fa-times-circle me-2"></i><strong>Error del servidor:</strong> ' + resp.mensaje;
                    errBackend.style.display = 'block';
                    return;
                }

                // Éxito: Cerrar modal y recargar
                var modalEl = document.getElementById('modalMigracion');
                if (modalEl) {
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }

                var _idParaRecargar3 = _globoCredito ? _globoCredito.Id_credito : null;
                Swal.fire({
                    title: '¡Convenio registrado!',
                    text: 'El convenio con pago globo y enganche se guardó correctamente.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                }).then(function () {
                    if (_idParaRecargar3) {
                        document.getElementById('inputBusqueda').value = _idParaRecargar3;
                        window.buscarCredito();
                    } else {
                        location.reload();
                    }
                });
            },
            onError: function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Convenio Globo';
                Swal.fire('Error', 'Error de conexión al guardar.', 'error');
            }
        });
    });
};

// Asegurar que las funciones globo estén disponibles globalmente
window.globoBuscarCredito = globoBuscarCredito;
window.globoCalcularPorcentaje = globoCalcularPorcentaje;
window.globoSugerirMontos = globoSugerirMontos;
window.globoRecalcular = globoRecalcular;
window.globoActualizarPreview = globoActualizarPreview;
window.globoVerTablaAmortizacion = globoVerTablaAmortizacion;
window.globoValidarPdf = globoValidarPdf;
window.globoGuardar = globoGuardar;

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

// ════════════════════════════════════════════════
// RESET COMPLETO MODAL MIGRACION
// ════════════════════════════════════════════════
window.migResetearFormulario = function () {
    // Pasos
    var step2 = document.getElementById('migStep2');
    if (step2) step2.classList.add('d-none');
    var preview = document.getElementById('migPreview');
    if (preview) preview.classList.add('d-none');

    // Preamortización
    var preamortBody = document.getElementById('preamortBody');
    if (preamortBody) preamortBody.innerHTML = '';
    var preamortWrap = document.getElementById('preamortTablaWrap');
    if (preamortWrap) preamortWrap.style.display = 'none';
    var preamortVacio = document.getElementById('preamortVacio');
    if (preamortVacio) preamortVacio.style.display = 'block';

    // Resumen cards / totales
    var migResumenCards = document.getElementById('migResumenCards');
    if (migResumenCards) migResumenCards.innerHTML = '';
    var migTotalBase = document.getElementById('migTotalBase');
    if (migTotalBase) migTotalBase.value = '';
    var migTotalFinal = document.getElementById('migTotalFinal');
    if (migTotalFinal) migTotalFinal.value = '';
    var migMontoAdicional = document.getElementById('migMontoAdicional');
    if (migMontoAdicional) { migMontoAdicional.value = ''; migMontoAdicional.disabled = false; }
    var colMigMontoAd = document.getElementById('colMigMontoAdicional');
    if (colMigMontoAd) colMigMontoAd.style.display = '';

    // Botón guardar
    var migBtnGuardar = document.getElementById('migBtnGuardar');
    if (migBtnGuardar) migBtnGuardar.style.display = 'none';

    // Errores previos
    var errBk = document.getElementById('migErrorBackend');
    if (errBk) errBk.style.display = 'none';
    var errGlobo = document.getElementById('migErrorGlobo');
    if (errGlobo) errGlobo.style.display = 'none';
    var errSem = document.getElementById('migErrorSemanal');
    if (errSem) errSem.remove();
    var migAdeudoAviso = document.getElementById('migAdeudoAviso');
    if (migAdeudoAviso) migAdeudoAviso.remove();

    // Campos de formulario
    var migPorcentaje = document.getElementById('migPorcentaje');
    if (migPorcentaje) { migPorcentaje.value = ''; migPorcentaje.readOnly = false; migPorcentaje.classList.remove('is-invalid'); }
    var migPagoSemanal = document.getElementById('migPagoSemanal');
    if (migPagoSemanal) { migPagoSemanal.value = ''; migPagoSemanal.readOnly = false; migPagoSemanal.style.background = ''; migPagoSemanal.classList.remove('is-invalid'); }
    var migFechaInicio = document.getElementById('migFechaInicio');
    if (migFechaInicio) migFechaInicio.value = '';
    var migAdeudoEl = document.getElementById('migAdeudo');
    if (migAdeudoEl) migAdeudoEl.value = '';
    _migOcultarBaseSelector();
    var globoPagoInicial = document.getElementById('globoPagoInicial');
    if (globoPagoInicial) globoPagoInicial.value = '';
    var migPagoFinalEl = document.getElementById('migPagoFinal');
    if (migPagoFinalEl) migPagoFinalEl.value = '';
    var migPdfAdjunto = document.getElementById('migPdfAdjunto');
    if (migPdfAdjunto) migPdfAdjunto.value = '';
    var migPdfPreview = document.getElementById('migPdfPreview');
    if (migPdfPreview) migPdfPreview.innerHTML = '';

    // Producto select + columnas dependientes
    var migProductoSel = document.getElementById('migProducto');
    if (migProductoSel) migProductoSel.selectedIndex = 0;
    var filaDesc = document.getElementById('filaDescuentoPorcentaje');
    if (filaDesc) filaDesc.style.display = '';
    var colPagoIn = document.getElementById('colPagoInicial');
    if (colPagoIn) colPagoIn.style.display = 'none';
    var colPagoFinalEl = document.getElementById('colPagoFinal');
    if (colPagoFinalEl) colPagoFinalEl.style.display = 'none';

    // Bucket: restaurar label, visibilidad y quitar select dinámico
    var semanasGloboSel = document.getElementById('migSemanasGlobo');
    if (semanasGloboSel) semanasGloboSel.style.display = 'none';
    var labelBucket = document.getElementById('labelBucketMorosidad');
    if (labelBucket) labelBucket.textContent = 'Bucket Morosidad';
    var inputBucket = document.getElementById('migBucket');
    if (inputBucket) { inputBucket.value = ''; inputBucket.style.display = 'block'; }

    // Radios modo Pago Mixto → ocultar y resetear a semanal
    var migModoGloboRadios = document.getElementById('migModoGloboRadios');
    if (migModoGloboRadios) migModoGloboRadios.style.display = 'none';
    var migModoRadioSemanal = document.getElementById('migModoRadioSemanal');
    if (migModoRadioSemanal) migModoRadioSemanal.checked = true;

    // Restaurar visibilidad de Fecha Inicio (ocultada en modo libre)
    var colFechaInicioReset = document.getElementById('colFechaInicio');
    if (colFechaInicioReset) colFechaInicioReset.style.display = '';
    var noticeFechaReset = document.getElementById('noticeFechaLibre');
    if (noticeFechaReset) noticeFechaReset.classList.add('d-none');

    // Panel libre (fechas específicas)
    var migLibreWrap = document.getElementById('migLibreWrap');
    if (migLibreWrap) migLibreWrap.style.display = 'none';
    var migLibreFilasBody = document.getElementById('migLibreFilasBody');
    if (migLibreFilasBody) migLibreFilasBody.innerHTML = '';
    var migLibreNumPagos = document.getElementById('migLibreNumPagos');
    if (migLibreNumPagos) migLibreNumPagos.value = '';
    var migLibreTotalIndicador = document.getElementById('migLibreTotalIndicador');
    if (migLibreTotalIndicador) migLibreTotalIndicador.textContent = 'Asignado: $0.00 / $0.00';
};

// Limpiar modal al cerrar (X o botón Cancelar)
(function () {
    var _modalMigEl = document.getElementById('modalMigracion');
    if (_modalMigEl) {
        _modalMigEl.addEventListener('hidden.bs.modal', function () {
            window.migResetearFormulario();
        });
    }
}());

console.log('✅ Módulo Convenios cargado correctamente');

// ══════════════════════════════════════════════════════
//  AUTO-CARGA desde parámetro URL ?credito=ID
// ══════════════════════════════════════════════════════
(function () {
    var params = new URLSearchParams(window.location.search);
    var idCredito = params.get('credito');
    if (idCredito && !isNaN(idCredito) && parseInt(idCredito) > 0) {
        var input = document.getElementById('inputBusqueda');
        if (input) {
            input.value = parseInt(idCredito);
            window.buscarCredito();
        }
    }
}());

</script>
