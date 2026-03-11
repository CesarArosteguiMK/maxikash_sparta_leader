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
.oferta-card .badge-tipo {
    position: absolute;
    top: -10px;
    right: 16px;
    font-size: 0.7rem;
    padding: 0.3em 0.8em;
    border-radius: 20px;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.badge-popular  { background: #ef4444; color: #fff; }
.badge-flexible { background: #3b82f6; color: #fff; }
.badge-mejor    { background: #8b5cf6; color: #fff; }

.oferta-card .icono-oferta { font-size: 1.8rem; margin-bottom: 0.5rem; }
.oferta-card .porcentaje   { font-size: 2.5rem; font-weight: 800; color: #764ba2; line-height: 1; }
.oferta-card .titulo-oferta { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0.3rem 0 0.6rem; }
.oferta-card .desc-oferta   { font-size: 0.82rem; color: #64748b; margin-bottom: 0.8rem; }
.oferta-card .detalle-item  { font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem; }
.oferta-card .detalle-item i { color: #22c55e; margin-right: 4px; }

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
/* Light mode: morado oscuro, Dark mode: blanco */
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

body.dark-mode .conv-slider-section {
    background: linear-gradient(135deg, #1e1533 0%, #2a1a4e 100%);
}
body.dark-mode .conv-slider-section h5     { color: #c084fc !important; }
body.dark-mode .semanas-valor              { color: #a78bfa; }
body.dark-mode .pago-semanal-calculado     { color: #4ade80; }
body.dark-mode .semanas-labels             { color: #64748b; }
body.dark-mode input[type=range].conv-range {
    background: #4c1d95;
}

body.dark-mode .tabla-amort tbody td {
    border-bottom: 1px solid #334155;
    color: #e2e8f0;
}
body.dark-mode .tabla-amort tbody tr:nth-child(even) td { background: #1e1533; }
body.dark-mode .amort-resumen td:first-child { color: #94a3b8; }
body.dark-mode .amort-resumen td:last-child  { color: #f1f5f9; }

body.dark-mode .alerta-incumplimiento {
    background: linear-gradient(135deg, #422006 0%, #78350f 100%);
    border-color: #d97706;
    color: #fed7aa;
}

/* Inline color:#5b2d8e en h5 */
body.dark-mode #convContenido h5[style*="color:#5b2d8e"],
body.dark-mode #convContenido h5[style*="color: #5b2d8e"] {
    color: #c084fc !important;
}

/* Cards del resumen amortización */
body.dark-mode #amortResumenCards .border {
    border-color: #334155 !important;
    background: #1e293b;
    color: #e2e8f0;
}
body.dark-mode #amortResumenCards .text-muted { color: #64748b !important; }

/* Lista resultados búsqueda */
body.dark-mode #resultadosBusqueda .list-group-item {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode #resultadosBusqueda .list-group-item:hover {
    background: #2d3748;
    color: #f1f5f9;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-xxl py-4">

    <!-- HEADER -->
    <div class="conv-header-gradient">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="fa-solid fa-handshake me-2"></i>Convenios de Liquidación</h4>
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
                       min="1"
                       oninput="validarInputBusqueda()">
                <button class="btn btn-primary" id="btnBuscar" onclick="buscarCredito()" disabled>
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

            <!-- Resumen numérico -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 id="amortTitulo" class="fw-bold mb-3" style="color:#5b2d8e;"></h5>
                    <div class="row text-center g-2 mb-3" id="amortResumenCards">
                        <!-- llenado por JS -->
                    </div>
                    <!-- Tabla semanal -->
                    <div class="table-responsive">
                        <table class="tabla-amort table table-borderless">
                            <thead>
                                <tr>
                                    <th>Semana</th>
                                    <th>Fecha de Pago</th>
                                    <th>Pago Semanal</th>
                                    <th>Capital</th>
                                    <th>Saldo Restante</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAmortBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button class="btn btn-success" onclick="guardarConvenio()">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Confirmar y Guardar Convenio
                        </button>
                        <button class="btn btn-outline-secondary" onclick="descargarPdf()">
                            <i class="fa-solid fa-file-pdf me-1"></i> Descargar PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// ══════════════════════════════════════════════════════
//  Estado global
// ══════════════════════════════════════════════════════
let _credito        = null;   // datos del crédito seleccionado
let _ofertas        = [];     // lista de ofertas elegibles
let _ofertaActiva   = null;   // oferta seleccionada actualmente
let _semanasActual  = 1;      // semanas elegidas en el slider

// ══════════════════════════════════════════════════════
//  BUSCAR CRÉDITO
// ══════════════════════════════════════════════════════
function buscarCredito() {
    const id = document.getElementById('inputBusqueda').value.trim();
    if (!id || isNaN(id) || parseInt(id) < 1) return;
    seleccionarCredito(parseInt(id));
}

function validarInputBusqueda() {
    const input = document.getElementById('inputBusqueda');
    const btn   = document.getElementById('btnBuscar');
    btn.disabled = !(input.value && !isNaN(input.value) && parseInt(input.value) > 0);
}

document.getElementById('inputBusqueda').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') buscarCredito();
});

// ══════════════════════════════════════════════════════
//  SELECCIONAR CRÉDITO → cargar ofertas
// ══════════════════════════════════════════════════════
function seleccionarCredito(idCredito) {
    document.getElementById('convContenido').style.display = 'none';

    Swal.fire({ title: 'Cargando ofertas...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    http.request({
        endpoint: '/convenios/getOfertasCredito',
        method: 'POST',
        data: { id_credito: idCredito },
        onSuccess: (resp) => {
            Swal.close();
            if (!resp.success) {
                Swal.fire('Sin elegibilidad', resp.mensaje || 'Este crédito no cumple los criterios.', 'info');
                return;
            }

            const { credito, ofertas, elegible, razon } = resp.datos;
            _credito = credito;
            _ofertas = ofertas;

            if (!elegible) {
                Swal.fire('Sin ofertas', razon === 'bucket_fuera_de_rango'
                    ? 'Este crédito no tiene mora suficiente (mínimo 22 días).'
                    : 'Este crédito no cumple los criterios para ninguna oferta.', 'info');
                return;
            }

            renderCreditoBanner(credito);
            renderOfertas(ofertas);

            // Resetear slider y amort
            document.getElementById('sliderSection').style.display = 'none';
            document.getElementById('amortSection').style.display = 'none';
            _ofertaActiva = null;

            document.getElementById('convContenido').style.display = 'block';
        },
        onError: () => { Swal.close(); Swal.fire('Error', 'Error al cargar ofertas.', 'error'); }
    });
}

// ══════════════════════════════════════════════════════
//  BANNER DEL CRÉDITO
// ══════════════════════════════════════════════════════
function renderCreditoBanner(c) {
    const bucketColor = (b) => {
        if (!b) return 'bg-secondary';
        if (b.startsWith('e)')) return 'bg-warning text-dark';
        if (b.startsWith('f)')) return 'bg-orange text-white';
        return 'bg-danger text-white';
    };

    const avance = parseFloat(c.Avance_Pago_Plazo || 0).toFixed(1);
    const adeudo = parseFloat(c.Adeudo_total || 0);
    const fmt    = v => '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });

    document.getElementById('creditoBanner').innerHTML = `
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="label">Crédito</div>
                <div class="valor">#${c.Id_credito}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="label">Cliente</div>
                <div class="valor" style="font-size:0.9rem;">${c.Nombre_cliente}</div>
            </div>
            <div class="col-6 col-md-2">
                <div class="label">Bucket</div>
                <span class="bucket-badge ${bucketColor(c.Bucket_Morosidad_Real)}">${c.Bucket_Morosidad_Real}</span>
            </div>
            <div class="col-6 col-md-2">
                <div class="label">Avance pago</div>
                <div class="valor">${avance}%</div>
            </div>
            <div class="col-6 col-md-2">
                <div class="label">Adeudo total</div>
                <div class="valor" style="color:#4ade80;">${fmt(adeudo)}</div>
            </div>
        </div>
    `;
}

// ══════════════════════════════════════════════════════
//  RENDERIZAR CARDS DE OFERTAS
// ══════════════════════════════════════════════════════

// Iconos fijos por producto
const OFERTA_ICONOS = { 1: '🎯', 2: '💰', 3: '❄️', 4: '⚡' };

/**
 * Calcula los badges para cada oferta según el perfil real del cliente.
 *
 * Criterios de puntuación:
 *  - Ahorro absoluto (descuento_monto) → beneficio económico directo
 *  - Penalización si es pago único (semanas_max=1) y el cliente tiene avance bajo (<61)
 *  - Penalización fuerte si no hay descuento (Congela tu Deuda)
 *  - Bonus de flexibilidad según semanas_max disponibles
 *
 * Solo la oferta de mayor puntaje recibe "Mejor Oferta".
 * La segunda recibe "Recomendada". Las demás no llevan badge.
 */
function calcularBadgesOfertas(ofertas, credito) {
    // Extraer límite inferior del rango de avance (ej. "61-80%" → 61)
    const avancePago = parseInt((credito.Avance_Pago_Plazo || '0').match(/\d+/) || [0]);
    const capacidadPagoUnico = avancePago >= 61;

    const puntuados = ofertas.map((o, idx) => {
        let score = parseFloat(o.descuento_monto); // base: ahorro en pesos

        // Pago único: viable solo con avance alto; si no, penalizar
        if (parseInt(o.semanas_max) === 1 && !capacidadPagoUnico) {
            score *= 0.4;
        }

        // Sin descuento (Congela tu Deuda): beneficio económico mínimo
        if (parseFloat(o.porcentaje_descuento) === 0) {
            score *= 0.25;
        }

        // Bonus por flexibilidad de plazos (más semanas = más accesible)
        score += parseInt(o.semanas_max) * 3;

        return { idx, score };
    });

    // Ordenar mayor a menor
    puntuados.sort((a, b) => b.score - a.score);

    // DEBUG — quitar en producción
    console.table(puntuados.map(p => ({
        oferta:  ofertas[p.idx].nombre,
        score:   p.score.toFixed(2),
        rank:    puntuados.indexOf(p) + 1,
    })));

    const badges = {};
    puntuados.forEach(({ idx }, rank) => {
        if (rank === 0) {
            badges[idx] = { badge: 'badge-mejor', badgeLabel: 'Mejor Oferta' };
        } else if (rank === 1) {
            badges[idx] = { badge: 'badge-popular', badgeLabel: 'Recomendada' };
        } else {
            badges[idx] = null; // sin badge
        }
    });

    return badges;
}

function renderOfertas(ofertas) {
    const cont   = document.getElementById('ofertasContainer');
    const fmt    = v => '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });
    const badges = calcularBadgesOfertas(ofertas, _credito);

    cont.innerHTML = ofertas.map((o, i) => {
        const icono    = OFERTA_ICONOS[o.id_producto] || '📋';
        const meta     = badges[i];
        const badgeHtml = meta
            ? `<span class="badge-tipo ${meta.badge}">${meta.badgeLabel}</span>`
            : '';
        const primerPago = o.pago_inicial === 'Si' && o.pago_inicial_monto
            ? fmt(o.pago_inicial_monto)
            : 'No requiere primer pago';
        const plazoLabel = parseInt(o.semanas_max) === 1
            ? '1 semana (pago único)'
            : `${o.periodo_inicio} a ${o.semanas_max} semanas`;

        return `
        <div class="col-12 col-md-6 col-lg-4">
            <div class="oferta-card" id="oferta-card-${i}" onclick="seleccionarOferta(${i})">
                ${badgeHtml}
                <div class="icono-oferta">${icono}</div>
                <div class="titulo-oferta">${o.nombre}</div>
                <div class="porcentaje">${parseInt(o.porcentaje_descuento)}%</div>
                <div class="desc-oferta">Ahorro de ${fmt(o.descuento_monto)} sobre ${fmt(o.monto_base)}</div>
                <div class="detalle-item"><i class="fa-solid fa-check"></i> Total a pagar: <strong>${fmt(o.total_a_pagar)}</strong></div>
                <div class="detalle-item"><i class="fa-solid fa-check"></i> Primer pago: <strong>${primerPago}</strong></div>
                <div class="detalle-item"><i class="fa-solid fa-check"></i> Plazos: <strong>${plazoLabel}</strong></div>
            </div>
        </div>`;
    }).join('');
}

// ══════════════════════════════════════════════════════
//  SELECCIONAR OFERTA → mostrar slider
// ══════════════════════════════════════════════════════
function seleccionarOferta(idx) {
    // Quitar selección previa
    document.querySelectorAll('.oferta-card').forEach(c => c.classList.remove('seleccionada'));
    document.getElementById('oferta-card-' + idx).classList.add('seleccionada');

    _ofertaActiva = _ofertas[idx];

    const min = _ofertaActiva.periodo_inicio;
    const max = _ofertaActiva.semanas_max;

    const slider = document.getElementById('sliderSemanas');
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
        const total     = parseFloat(_ofertaActiva.total_a_pagar);
        const semanal   = total / _semanasActual;
        document.getElementById('pagoSemanalCalc').textContent =
            '$' + semanal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' / semana';
    }
}

// ══════════════════════════════════════════════════════
//  VER TABLA DE AMORTIZACIÓN (simulada en frontend)
// ══════════════════════════════════════════════════════
function verTablaAmortizacion() {
    if (!_ofertaActiva || !_credito) return;

    const o        = _ofertaActiva;
    const total    = parseFloat(o.total_a_pagar);
    const semanal  = parseFloat((total / _semanasActual).toFixed(2));
    const fmt      = v => '$' + parseFloat(v).toLocaleString('es-MX', { minimumFractionDigits: 2 });

    // Fecha de acuerdo = hoy
    const hoy = new Date();
    const añadirDias = (fecha, dias) => {
        const d = new Date(fecha);
        d.setDate(d.getDate() + dias);
        return d;
    };
    const fmtFecha = d => d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });

    // Resumen
    document.getElementById('amortTitulo').textContent = '📋 ' + o.nombre;
    document.getElementById('amortResumenCards').innerHTML = `
        <div class="col-6 col-md-3">
            <div class="border rounded p-2">
                <div class="small text-muted">Deuda Original</div>
                <div class="fw-bold text-primary fs-5">${fmt(o.monto_base)}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-2">
                <div class="small text-muted">Descuento (${parseInt(o.porcentaje_descuento)}%)</div>
                <div class="fw-bold text-danger fs-5">-${fmt(o.descuento_monto)}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-2">
                <div class="small text-muted">Total a Pagar</div>
                <div class="fw-bold text-success fs-5">${fmt(total)}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-2">
                <div class="small text-muted">Pago Semanal</div>
                <div class="fw-bold text-warning fs-5">${fmt(semanal)}</div>
            </div>
        </div>
    `;

    // Tabla de amortización
    let saldo       = total;
    let filasHtml   = '';
    for (let s = 1; s <= _semanasActual; s++) {
        const fechaPago = añadirDias(hoy, (s - 1) * 7 + 8);
        const capital   = (s < _semanasActual) ? semanal : parseFloat(saldo.toFixed(2));
        saldo           = parseFloat((saldo - capital).toFixed(2));
        if (saldo < 0) saldo = 0;

        filasHtml += `<tr>
            <td>Semana ${s}</td>
            <td>${fmtFecha(fechaPago)}</td>
            <td>${fmt(semanal)}</td>
            <td>${fmt(capital)}</td>
            <td>${fmt(saldo)}</td>
        </tr>`;
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
        html: `<strong>${_ofertaActiva.nombre}</strong><br>
               ${_semanasActual} semanas — $${parseFloat(_ofertaActiva.total_a_pagar / _semanasActual).toFixed(2)} / semana`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#764ba2',
    }).then(result => {
        if (!result.isConfirmed) return;

        const hoy       = new Date().toISOString().split('T')[0];
        const total     = parseFloat(_ofertaActiva.total_a_pagar);
        const semanal   = parseFloat((total / _semanasActual).toFixed(2));

        http.request({
            endpoint: '/convenios/guardarConvenio',
            method: 'POST',
            data: {
                id_credito:                     _credito.Id_credito,
                id_producto_convenio:           _ofertaActiva.id_producto,
                id_producto_convenio_detalle:   _ofertaActiva.id_detalle,
                nombre_cliente:                 _credito.Nombre_cliente,
                bucket_morosidad_real:          _credito.Bucket_Morosidad_Real,
                dias_mora:                      _credito.Dias_mora,
                avance_pago_plazo:              _credito.Avance_Pago_Plazo,
                adeudo_total_original:          _credito.Adeudo_total,
                porcentaje_descuento:           _ofertaActiva.porcentaje_descuento,
                descuento_monto:                _ofertaActiva.descuento_monto,
                total_a_pagar:                  total,
                pago_inicial_monto:             _ofertaActiva.pago_inicial_monto || '',
                numero_semanas:                 _semanasActual,
                pago_semanal:                   semanal,
                fecha_acuerdo:                  hoy,
            },
            onSuccess: (resp) => {
                if (resp.success) {
                    Swal.fire('¡Guardado!', 'El convenio fue registrado exitosamente.', 'success');
                } else {
                    Swal.fire('Error', resp.mensaje || 'No se pudo guardar.', 'error');
                }
            },
            onError: () => Swal.fire('Error', 'Error de conexión.', 'error')
        });
    });
}

// ══════════════════════════════════════════════════════
//  DESCARGAR PDF
// ══════════════════════════════════════════════════════
function descargarPdf() {
    if (!_credito) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/convenios/descargarPdf';
    form.style.display = 'none';

    const inp = document.createElement('input');
    inp.name  = 'id_credito';
    inp.value = _credito.Id_credito;
    form.appendChild(inp);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
