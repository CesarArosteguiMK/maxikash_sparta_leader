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
            <div class="input-group">
                <input type="number" id="inputBusqueda" class="form-control"
                       placeholder="Ej: 123456"
                       autocomplete="off"
                       min="1">
                <button class="btn btn-primary" id="btnBuscar" onclick="buscarCredito()">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
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
    <button class="btn btn-outline-secondary btn-sm" onclick="abrirHistorial()">
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
                           oninput="actualizarSlider(this.value)">
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
                        onclick="verTablaAmortizacion()" style="display:none;">
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
                                   <th>Semanal / Capital</th>
                                   <th>Saldo Restante</th>
                                   <th>Pago Realizado</th>
                                   <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAmortBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button class="btn btn-success" id="btnGuardar" onclick="guardarConvenio()">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Confirmar y Guardar Convenio
                        </button>
                        <button class="btn btn-outline-secondary" id="btnPdf" onclick="descargarPdf()" style="display:none;">
                            <i class="fa-solid fa-file-pdf me-1"></i> Descargar PDF
                        </button>

                        <button class="btn btn-danger btn-sm" id="btnCancelar"
                            onclick="cancelarConvenio()" style="display:none;">
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

<script>

// ══════════════════════════════════════════════════════
//  Estado global
// ══════════════════════════════════════════════════════
var _credito       = null;
var _ofertas       = [];
var _ofertaActiva  = null;
var _semanasActual = 1;
// var _pollingInterval = null;

// ══════════════════════════════════════════════════════
//  BUSCAR CRÉDITO
// ══════════════════════════════════════════════════════
function buscarCredito() {
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
}

document.getElementById('inputBusqueda').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') buscarCredito();
});

// ══════════════════════════════════════════════════════
//  SELECCIONAR CRÉDITO → ofertas + convenio activo
// ══════════════════════════════════════════════════════
function seleccionarCredito(idCredito) {
    document.getElementById('convContenido').style.display = 'none';
    document.getElementById('btnHistorialWrap').style.display = 'none';

    // Limpiar banner convenio activo si existía de búsqueda anterior
    var bannerPrevio = document.getElementById('bannerConvenioActivo');
    if (bannerPrevio) bannerPrevio.remove();

    // detenerPolling();

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

            if (!elegible) {
                Swal.close();
                Swal.fire('Sin ofertas', razon === 'bucket_fuera_de_rango'
                    ? 'Este crédito no tiene mora suficiente (mínimo 22 días).'
                    : 'Este crédito no cumple los criterios para ninguna oferta.', 'info');
                return;
            }

            // Llamada 2: convenio activo
            http.request({
                endpoint: '/convenios/getConvenioActivo',
                method: 'POST',
                data: { id_credito: idCredito },
                onSuccess: function(respConvenio) {
                    Swal.close();

                    // Resetear botones al estado normal antes de pintar
                    document.getElementById('btnGuardar').style.display = 'inline-block';
                    document.getElementById('btnPdf').className = 'btn btn-outline-secondary';

                    renderCreditoBanner(credito);
                    renderOfertas(ofertas);

                    document.getElementById('sliderSection').style.display = 'none';
                    document.getElementById('amortSection').style.display  = 'none';
                    document.getElementById('alertaIncumplimiento').style.display = 'none';
                    _ofertaActiva = null;

                    document.getElementById('convContenido').style.display = 'block';
                    document.getElementById('btnHistorialWrap').style.display = 'block';


                    if (respConvenio.success && respConvenio.datos && respConvenio.datos.estatus === 'activo') {
                        congelarModulo(respConvenio.datos);
                    } else {
                       // iniciarPolling(idCredito);
                    }
                },
                onError: function() {
                    Swal.close();
                    document.getElementById('btnGuardar').style.display = 'inline-block';
                    document.getElementById('btnPdf').className = 'btn btn-outline-secondary';
                    renderCreditoBanner(credito);
                    renderOfertas(ofertas);
                    document.getElementById('sliderSection').style.display = 'none';
                    document.getElementById('amortSection').style.display  = 'none';
                    document.getElementById('convContenido').style.display = 'block';
                   // iniciarPolling(idCredito);
                }
            });
        },
        onError: function() { Swal.close(); Swal.fire('Error', 'Error al cargar ofertas.', 'error'); }
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
            '<div class="col-6 col-md-2"><div class="label">Adeudo total</div><div class="valor" style="color:#4ade80;">' + fmt(adeudo) + '</div></div>' +
        '</div>';
}

// ══════════════════════════════════════════════════════
//  RENDERIZAR CARDS DE OFERTAS
// ══════════════════════════════════════════════════════
var OFERTA_ICONOS = { 1: '🎯', 2: '💰', 3: '❄️', 4: '⚡' };

function renderOfertas(ofertas) {
    var cont = document.getElementById('ofertasContainer');
    var fmt  = function(v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };

    cont.innerHTML = ofertas.map(function(o, i) {
        var icono      = OFERTA_ICONOS[o.id_producto] || '📋';
        var primerPago = (o.pago_inicial === 'Si' && o.pago_inicial_monto)
            ? fmt(o.pago_inicial_monto)
            : 'No requiere primer pago';
        var plazoLabel = parseInt(o.semanas_max) === 1
            ? '1 semana (pago único)'
            : o.periodo_inicio + ' a ' + o.semanas_max + ' semanas';

        return '<div class="col-12 col-md-3">' +
            '<div class="oferta-card" id="oferta-card-' + i + '" onclick="seleccionarOferta(' + i + ')">' +
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
}

// ══════════════════════════════════════════════════════
//  CONGELAR MÓDULO (convenio activo existente)
// ══════════════════════════════════════════════════════
function congelarModulo(convenio) {
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

    // Tabla con datos reales del backend
    var filasHtml = convenio.amortizacion.map(function(fila) {

        var estatusBadge = fila.estatus_pago === 'pagado'
            ? '<span class="badge bg-success">Pagado</span>'
            : '<span class="badge bg-warning text-dark">Pendiente</span>';
        return '<tr>' +
    '<td>Semana ' + fila.numero_semana + '</td>' +
    '<td>' + fmtFechaRango(fila.fecha_pago) + '</td>' +
    '<td>' +
        '<span style="display:block;font-weight:600;">' + fmt(fila.pago_semanal) + '</span>' +
        '<span style="display:block;font-size:0.82em;color:#888;">' + fmt(fila.capital) + '</span>' +
    '</td>' +
    '<td>' + fmt(fila.saldo_restante) + '</td>' +
    '<td>' + fmtPagoRealizado(null) + '</td>' +
    '<td>' + estatusBadge + '</td>' +
'</tr>';
    }).join('');
    document.getElementById('tablaAmortBody').innerHTML = filasHtml;
    document.getElementById('amortSection').style.display = 'block';

    // Solo PDF visible
    document.getElementById('btnGuardar').style.display = 'none';
    document.getElementById('btnPdf').className = 'btn btn-primary';
    document.getElementById('btnPdf').style.display = 'inline-block';


    document.getElementById('btnCancelar').style.display = 'inline-block';
    window._idConvenioActivo = convenio.id;

    // Banner informativo
    var fechaAcuerdo   = new Date(convenio.fecha_acuerdo + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    var fechaUltimoPago = new Date(convenio.fecha_ultimo_pago + 'T12:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    var banner = document.getElementById('creditoBanner');
    banner.insertAdjacentHTML('afterend',
        '<div id="bannerConvenioActivo" class="alert d-flex align-items-center gap-2 mb-3 banner-convenio-activo-light">' +
            '<i class="fa-solid fa-lock fa-lg"></i>' +
            '<div><strong>Convenio activo registrado.</strong> ' +
            'Acuerdo del ' + fechaAcuerdo + '. ' +
            'Último pago: ' + fechaUltimoPago + '. ' +
            'Solo puedes descargar el PDF.</div>' +
        '</div>'
    );

    document.getElementById('amortSection').scrollIntoView({ behavior: 'smooth' });
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
function seleccionarOferta(idx) {
    document.querySelectorAll('.oferta-card').forEach(function(c) { c.classList.remove('seleccionada'); });
    document.getElementById('oferta-card-' + idx).classList.add('seleccionada');

    _ofertaActiva = _ofertas[idx];

    var min = _ofertaActiva.periodo_inicio;
    var max = _ofertaActiva.semanas_max;

    var slider = document.getElementById('sliderSemanas');
    slider.min   = min;
    slider.max   = max;
    slider.value = min;

    document.getElementById('labelMin').textContent = min + ' sem';
    document.getElementById('labelMax').textContent = max + ' sem';
    document.getElementById('sliderTitulo').textContent = 'Plazo para: ' + _ofertaActiva.nombre;

    actualizarSlider(min);

    document.getElementById('sliderSection').style.display = 'block';
    document.getElementById('amortSection').style.display  = 'none';
    document.getElementById('btnVerAmort').style.display   = 'inline-block';

    document.getElementById('sliderSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ══════════════════════════════════════════════════════
//  ACTUALIZAR SLIDER
// ══════════════════════════════════════════════════════
function actualizarSlider(semanas) {
    _semanasActual = parseInt(semanas);
    document.getElementById('semanasValor').textContent = _semanasActual;

    if (_ofertaActiva) {
        var total   = parseFloat(_ofertaActiva.total_a_pagar);
        var semanal = total / _semanasActual;
        document.getElementById('pagoSemanalCalc').textContent =
            '$' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' / semana';
    }
}

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
function verTablaAmortizacion() {
    if (!_ofertaActiva || !_credito) return;

    var o      = _ofertaActiva;
    var total  = parseFloat(o.total_a_pagar);
    var semanal = parseFloat((total / _semanasActual).toFixed(2));
    var fmt    = function(v) { return '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 }); };
    var hoy    = new Date();

    var añadirDias = function(fecha, dias) {
        var d = new Date(fecha);
        d.setDate(d.getDate() + dias);
        return d;
    };
    var fmtFecha = function(d) {
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    document.getElementById('amortTitulo').textContent = '📋 ' + o.nombre;
    document.getElementById('amortResumenCards').innerHTML =
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Deuda Original</div><div class="fw-bold text-primary fs-5">' + fmt(o.monto_base) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Descuento (' + parseInt(o.porcentaje_descuento) + '%)</div><div class="fw-bold text-danger fs-5">-' + fmt(o.descuento_monto) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Total a Pagar</div><div class="fw-bold text-success fs-5">' + fmt(total) + '</div></div></div>' +
        '<div class="col-6 col-md-3"><div class="border rounded p-2"><div class="small text-muted">Pago Semanal</div><div class="fw-bold text-warning fs-5">' + fmt(semanal) + '</div></div></div>';

    var saldo     = total;
    var filasHtml = '';
    for (var s = 1; s <= _semanasActual; s++) {
        var fechaPago = añadirDias(hoy, (s - 1) * 7 + 8);
        var capital   = (s < _semanasActual) ? semanal : parseFloat(saldo.toFixed(2));
        saldo         = parseFloat((saldo - capital).toFixed(2));
        if (saldo < 0) saldo = 0;

        filasHtml += '<tr>' +
    '<td>Semana ' + s + '</td>' +
    '<td>' + fmtFechaRango(fechaPago.toISOString().split('T')[0]) + '</td>' +
    '<td>' +
        '<span style="display:block;font-weight:600;">' + fmt(semanal) + '</span>' +
        '<span style="display:block;font-size:0.82em;color:#888;">' + fmt(capital) + '</span>' +
    '</td>' +
    '<td>' + fmt(saldo) + '</td>' +
    '<td>' + fmtPagoRealizado(null) + '</td>' +
    '<td><span class="badge bg-warning text-dark">Pendiente</span></td>' +
'</tr>';
    }
    document.getElementById('tablaAmortBody').innerHTML = filasHtml;

    document.getElementById('amortSection').style.display = 'block';
    document.getElementById('amortSection').scrollIntoView({ behavior: 'smooth' });
}

// ══════════════════════════════════════════════════════
//  GUARDAR CONVENIO
// ══════════════════════════════════════════════════════
function guardarConvenio() {
    if (!_ofertaActiva || !_credito) return;

    Swal.fire({
        title: '¿Confirmar convenio?',
        html: '<strong>' + _ofertaActiva.nombre + '</strong><br>' +
              _semanasActual + ' semanas — $' + parseFloat(_ofertaActiva.total_a_pagar / _semanasActual).toFixed(2) + ' / semana',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#764ba2',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        var hoy     = new Date().toISOString().split('T')[0];
        var total   = parseFloat(_ofertaActiva.total_a_pagar);
        var semanal = parseFloat((total / _semanasActual).toFixed(2));

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
                    Swal.fire('¡Guardado!', 'El convenio fue registrado exitosamente.', 'success')
                    .then(function() {
                        document.getElementById('btnGuardar').style.display = 'none';
                        document.getElementById('btnPdf').style.display     = 'inline-block';
                        document.getElementById('btnCancelar').style.display = 'inline-block';
                        window._idConvenioActivo = resp.datos.id_convenio;
                    });
                } else {
                    Swal.fire('Error', resp.mensaje || 'No se pudo guardar.', 'error');
                }
            },
            onError: function() { Swal.fire('Error', 'Error de conexión.', 'error'); }
        });
    });
}

// ══════════════════════════════════════════════════════
//  CANCELAR CONVENIO
// ══════════════════════════════════════════════════════
function cancelarConvenio() {
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
                }).then(function() {
                    _repintarTablaCancelada(resp.datos.numero_semana_cancelacion);
                    document.getElementById('btnCancelar').style.display = 'none';

                    var banner = document.getElementById('bannerConvenioActivo');
                    if (banner) {
                        banner.className = 'alerta-incumplimiento';
                        banner.innerHTML =
                            '<i class="fa-solid fa-ban me-2"></i>' +
                            '<strong>Convenio cancelado.</strong> ' +
                            'Las semanas pendientes quedaron anuladas. ' +
                            'Es posible generar un nuevo convenio.';
                    }
                });
            },
            onError: function() {
                Swal.close();
                Swal.fire('Error', 'Error de conexión.', 'error');
            }
        });
    });
}

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
function descargarPdf() {
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
}

// ══════════════════════════════════════════════════════
//  HISTORIAL DE CONVENIOS
// ══════════════════════════════════════════════════════
function abrirHistorial() {
    if (!_credito) return;

    // Mostrar modal con loading
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

            var filas = resp.datos.map(function(c, i) {
                return '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + fmtFecha(c.fecha_acuerdo) + '</td>' +
                    '<td>' + c.nombre_producto + '</td>' +
                    '<td>' + fmt(c.total_a_pagar) + '</td>' +
                    '<td>' + c.numero_semanas + ' sem</td>' +
                    '<td>' + badgeEstatus(c.estatus) + '</td>' +
                    '<td>' + (c.usuario_alta || '—') + '</td>' +
                    '<td>' + (c.usuario_cancela || '—') + '</td>' +
                    '<td>' + fmtFecha(c.fecha_cancelacion) + '</td>' +
                '</tr>';
            }).join('');

            document.getElementById('tablaHistorialBody').innerHTML   = filas;
            document.getElementById('historialTablaWrap').style.display = 'block';
        },
        onError: function() {
            document.getElementById('historialLoading').style.display = 'none';
            document.getElementById('historialVacio').style.display   = 'block';
        }
    });
}

window.abrirHistorial = abrirHistorial;

window.buscarCredito    = buscarCredito;
window.seleccionarOferta = seleccionarOferta;
window.descargarPdf     = descargarPdf;
window.guardarConvenio  = guardarConvenio;
window.verTablaAmortizacion = verTablaAmortizacion;
window.actualizarSlider = actualizarSlider;
window.cancelarConvenio = cancelarConvenio;
</script>
