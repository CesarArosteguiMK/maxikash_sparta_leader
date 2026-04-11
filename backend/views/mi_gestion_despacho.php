<style>
    /* ===== KPI CARDS ===== */
    .kpi-wrapper {
        display: flex;
        justify-content: flex-start;
        align-items: stretch;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .kpi-item {
        flex: 1 1 160px;
        max-width: 220px;
        min-width: 140px;
    }

    .kpi-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        background: #ffffff;
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -1px rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
        cursor: default;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--kpi-color-start), var(--kpi-color-end));
        transition: height 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.12);
    }

    .kpi-card:hover::before { height: 6px; }

    .kpi-card.kpi-creditos      { --kpi-color-start: #696cff; --kpi-color-end: #9155fd; }
    .kpi-card.kpi-activos       { --kpi-color-start: #28a745; --kpi-color-end: #20c997; }
    .kpi-card.kpi-convenios     { --kpi-color-start: #0ea5e9; --kpi-color-end: #38bdf8; }
    .kpi-card.kpi-conv-atraso   { --kpi-color-start: #dc3545; --kpi-color-end: #fd7e14; }
    .kpi-card.kpi-conv-regla    { --kpi-color-start: #10b981; --kpi-color-end: #34d399; }

    .kpi-card .card-body {
        padding: 1rem 1rem 0.85rem;
        text-align: center;
        position: relative;
    }

    .kpi-number {
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.3rem;
        background: linear-gradient(135deg, var(--kpi-color-start), var(--kpi-color-end));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        transition: all 0.3s ease;
    }

    .kpi-card:hover .kpi-number { transform: scale(1.08); }

    .kpi-label {
        font-size: 0.65rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .kpi-icon {
        font-size: 2rem;
        opacity: 0.1;
        position: absolute;
        right: 0.75rem;
        top: 0.75rem;
        color: var(--kpi-color-start);
        transition: all 0.3s ease;
    }

    .kpi-card:hover .kpi-icon { opacity: 0.2; transform: scale(1.1) rotate(5deg); }

    /* ===== KPI CLICKABLES ===== */
    .kpi-card.kpi-clickable { cursor: pointer; }
    .kpi-card.kpi-clickable:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 24px -6px rgba(0,0,0,0.15);
    }
    .kpi-card.kpi-clickable .kpi-label::after {
        content: ' ▸';
        opacity: 0.5;
        font-size: 0.6rem;
    }

    /* ===== MODAL CONVENIOS ===== */
    .conv-kpi-card {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 1rem 1.1rem 0.9rem;
        transition: box-shadow 0.2s;
    }
    .conv-kpi-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .conv-progress-bar {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
        margin: 0.4rem 0 0.2rem;
    }
    .conv-progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
    }
    .conv-progress-fill.fill-regla  { background: linear-gradient(90deg,#10b981,#34d399); }
    .conv-progress-fill.fill-atraso { background: linear-gradient(90deg,#dc3545,#fd7e14); }

    /* ===== FILTROS ===== */
    .filter-bar {
        background: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    /* ===== BADGES ===== */
    .badge { font-size: 0.7rem; font-weight: 500; }

    /* ===== SWITCH ===== */
    .switch-credito { cursor: not-allowed; }

    /* ===== PROGRESS BAR MORA ===== */
    .mora-bar { height: 5px; border-radius: 3px; }

    /* ===== ANIMACIONES ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .animate-in { animation: fadeInUp 0.35s ease-out both; }
</style>

<!-- ===================================================== -->
<!-- ⚠️  PANEL DE PRUEBAS (comentado, descomentar si se necesita volver a testear)
<div id="panel-test-despacho" class="alert alert-warning border-warning mb-4" style="border-left: 4px solid #f59e0b;">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="fa-solid fa-flask text-warning"></i>
        <strong class="text-warning">MODO PRUEBAS</strong>
        <span class="badge bg-warning text-dark ms-1">TEMPORAL</span>
    </div>
    <p class="mb-2 small text-muted">Selecciona un despacho para simular su sesión y verificar que los datos se carguen correctamente.</p>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <label class="form-label mb-0 small fw-semibold">Simular despacho:</label>
        <select id="test-select-despacho" class="form-select form-select-sm" style="max-width: 350px;">
            <option value="">— Usar sesión actual —</option>
        </select>
        <span class="text-muted small" id="test-info-label"></span>
    </div>
</div>
-->

<!-- ===================================================== -->

<!-- ===== ENCABEZADO ===== -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">
            <i class="fa-solid fa-chart-gantt me-2"></i>Mi Cartera
        </h4>
        <p class="text-muted small mb-0">Créditos asignados a tu cartera</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" id="btn-refresh-gestion" onclick="cargarMiGestion()">
            <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
        </button>
        <button class="btn btn-success btn-sm" id="btn-exportar-mi-gestion">
            <i class="fa-solid fa-file-excel me-1"></i>Exportar Excel
        </button>
    </div>
</div>

<!-- ===== KPIs ===== -->
<div class="kpi-wrapper" id="kpi-container">
    <div class="kpi-item">
        <div class="card kpi-card kpi-creditos">
            <div class="card-body">
                <i class="fa-solid fa-list kpi-icon"></i>
                <div class="kpi-number" id="kpi-total">—</div>
                <p class="kpi-label">Total Créditos</p>
            </div>
        </div>
    </div>
    <div class="kpi-item">
        <div class="card kpi-card kpi-activos">
            <div class="card-body">
                <i class="fa-solid fa-circle-check kpi-icon"></i>
                <div class="kpi-number" id="kpi-activos">—</div>
                <p class="kpi-label">Activos</p>
            </div>
        </div>
    </div>
    <div class="kpi-item" onclick="abrirModalConveniosKpi('todos')" title="Ver todos los convenios">
        <div class="card kpi-card kpi-convenios kpi-clickable">
            <div class="card-body">
                <i class="fa-solid fa-handshake kpi-icon"></i>
                <div class="kpi-number" id="kpi-convenios">—</div>
                <p class="kpi-label">Núm. Convenios</p>
            </div>
        </div>
    </div>
    <div class="kpi-item" onclick="abrirModalConveniosKpi('atraso')" title="Ver convenios con atraso">
        <div class="card kpi-card kpi-conv-atraso kpi-clickable">
            <div class="card-body">
                <i class="fa-solid fa-triangle-exclamation kpi-icon"></i>
                <div class="kpi-number" id="kpi-conv-atraso">—</div>
                <p class="kpi-label">Convenios con Atraso</p>
            </div>
        </div>
    </div>
    <div class="kpi-item" onclick="abrirModalConveniosKpi('regla')" title="Ver convenios en regla">
        <div class="card kpi-card kpi-conv-regla kpi-clickable">
            <div class="card-body">
                <i class="fa-solid fa-circle-check kpi-icon"></i>
                <div class="kpi-number" id="kpi-conv-regla">—</div>
                <p class="kpi-label">Convenios en Regla</p>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONVENIOS KPI ===== -->
<div class="modal fade" id="modalConveniosKpi" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" id="modalConvHeader">
                <h5 class="modal-title fw-bold text-white" id="modalConvTitulo">
                    <i class="fa-solid fa-handshake me-2"></i>Mis Convenios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Resumen rápido -->
                <div id="modalConvResumen" class="d-flex gap-3 mb-3 flex-wrap"></div>
                <!-- Lista de convenios -->
                <div id="modalConvLista"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABLA ===== -->
<div class="card animate-in">
    <div class="card-header pb-0">
        <!-- Filtro de estatus -->
        <div class="filter-bar d-flex align-items-center gap-3 flex-wrap">
            <span class="text-muted small fw-semibold me-1">Filtrar por estado:</span>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="filtroEstado" id="filtroTodos" value="todos" checked>
                <label class="form-check-label small" for="filtroTodos">Todos</label>
            </div>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="filtroEstado" id="filtroActivos" value="activos">
                <label class="form-check-label small" for="filtroActivos">Solo activos</label>
            </div>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="filtroEstado" id="filtroInactivos" value="inactivos">
                <label class="form-check-label small" for="filtroInactivos">Solo inactivos</label>
            </div>
        </div>
    </div>

    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabla-mi-gestion">
            <thead>
                <tr>
                    <th>ID Crédito</th>
                    <th>Cliente</th>
                    <th>Saldo</th>
                    <th>Días Mora</th>
                    <th>Estado</th>
                    <th>Fecha Asignación</th>
                    <th>Asignado Por</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- ===== ESTADO VACÍO ===== -->
<div id="empty-state" style="display:none;" class="text-center py-5">
    <i class="fa-solid fa-inbox fa-3x text-secondary mb-3 d-block"></i>
    <h6 class="text-muted">No tienes créditos asignados</h6>
    <p class="text-muted small">Cuando te asignen créditos aparecerán aquí.</p>
</div>

<script>
let misCreditosCargados = [];

document.addEventListener('DOMContentLoaded', function () {
    cargarMiGestion();

    // Filtros de radio
    document.querySelectorAll('input[name="filtroEstado"]').forEach(radio => {
        radio.addEventListener('change', aplicarFiltroTabla);
    });

    document.getElementById('btn-exportar-mi-gestion').addEventListener('click', exportarMiGestion);
});

// ============================================================
// TEST: cargar lista de despachos en selector (comentado)
// Descomentar si se necesita volver a testear desde este módulo
// ============================================================
/*
(function cargarListaTest() {
    fetch('/despachos/obtenerListaDespachos')
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.despachos) return;
            const sel = document.getElementById('test-select-despacho');
            data.despachos.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id_persona;
                opt.textContent = `${d.nombre_completo} — ${d.nombre_puesto}`;
                sel.appendChild(opt);
            });
        })
        .catch(() => {});

    document.getElementById('test-select-despacho').addEventListener('change', function () {
        const idPersona = this.value;
        const label = document.getElementById('test-info-label');
        label.textContent = idPersona ? `(id_persona: ${idPersona})` : '';
        cargarMiGestion(idPersona || null);
    });
})();
*/

// ============================================================
// CARGA PRINCIPAL
// ============================================================
function cargarMiGestion(testIdPersona) {
    const btn = document.getElementById('btn-refresh-gestion');
    const icon = btn.querySelector('i');
    icon.classList.add('fa-spin');
    btn.disabled = true;

    // const url = testIdPersona  // TEST: descomentar junto con el panel de pruebas
    //     ? `/Despachos/ObtenerMisCreditos?test_id=${encodeURIComponent(testIdPersona)}`
    //     : '/Despachos/ObtenerMisCreditos';
    const url = '/Despachos/ObtenerMisCreditos';

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.success && Array.isArray(data.creditos)) {
                misCreditosCargados = data.creditos;
                actualizarKPIs(data.creditos);
                renderizarTabla(data.creditos);
            } else {
                mostrarEmptyState();
            }
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudieron cargar los créditos', 'error');
        })
        .finally(() => {
            icon.classList.remove('fa-spin');
            btn.disabled = false;
        });
}

// ============================================================
// KPIs
// ============================================================
function actualizarKPIs(creditos) {
    const activos      = creditos.filter(c => c.estado === '1' || c.estado === 1);
    const conConvenio  = creditos.filter(c => parseInt(c.tiene_convenio) === 1);
    const convAtraso   = conConvenio.filter(c => parseInt(c.tiene_atraso) === 1);
    const convRegla    = conConvenio.filter(c => parseInt(c.tiene_atraso) === 0);

    document.getElementById('kpi-total').textContent       = creditos.length;
    document.getElementById('kpi-activos').textContent     = activos.length;
    document.getElementById('kpi-convenios').textContent   = conConvenio.length;
    document.getElementById('kpi-conv-atraso').textContent = convAtraso.length;
    document.getElementById('kpi-conv-regla').textContent  = convRegla.length;
}

// ============================================================
// TABLA
// ============================================================
function renderizarTabla(creditos) {
    if (!creditos || creditos.length === 0) {
        mostrarEmptyState();
        return;
    }

    document.getElementById('empty-state').style.display = 'none';

    const filtro = document.querySelector('input[name="filtroEstado"]:checked').value;
    let filtrados = creditos;
    if (filtro === 'activos')   filtrados = creditos.filter(c => c.estado === '1' || c.estado === 1);
    if (filtro === 'inactivos') filtrados = creditos.filter(c => c.estado !== '1' && c.estado !== 1);

    const datosFormateados = filtrados.map(credito => {
        const esActivo = credito.estado === '1' || credito.estado === 1;
        const estadoBadge = esActivo
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-secondary">Inactivo</span>';

        const diasMora  = parseInt(credito.dias_mora || 0);
        const moraColor = diasMora > 60 ? 'bg-danger' : diasMora > 30 ? 'bg-warning' : 'bg-info';
        const moraHtml  = `
            <div class="d-flex align-items-center gap-2">
                <span class="badge ${moraColor}">${diasMora}</span>
            </div>`;

        const saldoHtml = `<span class="text-danger fw-semibold">${formatearMoneda(parseFloat(credito.saldo || 0))}</span>`;

        const nombreHtml = credito.nombre_cliente && credito.nombre_cliente !== 'No disponible'
            ? escapeHtml(credito.nombre_cliente)
            : '<span class="text-muted fst-italic">No disponible</span>';

        return [
            `<strong>${credito.id_credito}</strong>`,
            nombreHtml,
            saldoHtml,
            moraHtml,
            estadoBadge,
            credito.fecha_asignacion || '—',
            escapeHtml(credito.asignado_por || 'Sistema')
        ];
    });

    if ($.fn.DataTable.isDataTable('#tabla-mi-gestion')) {
        actualizaDatosTabla('#tabla-mi-gestion', datosFormateados, false);
    } else {
        configuraTabla('#tabla-mi-gestion', {
            registrosPorPagina: 15,
            columns: [
                { data: 0, title: 'ID Crédito' },
                { data: 1, title: 'Cliente' },
                { data: 2, title: 'Saldo' },
                { data: 3, title: 'Días Mora' },
                { data: 4, title: 'Estado' },
                { data: 5, title: 'Fecha Asignación' },
                { data: 6, title: 'Asignado Por' }
            ]
        });
        actualizaDatosTabla('#tabla-mi-gestion', datosFormateados, false);
    }
}

function aplicarFiltroTabla() {
    if (misCreditosCargados.length) {
        renderizarTabla(misCreditosCargados);
    }
}

function mostrarEmptyState() {
    document.getElementById('empty-state').style.display = 'block';
    if ($.fn.DataTable.isDataTable('#tabla-mi-gestion')) {
        actualizaDatosTabla('#tabla-mi-gestion', [], false);
    }
    // KPIs en cero
    ['kpi-total','kpi-activos','kpi-convenios','kpi-conv-atraso','kpi-conv-regla']
        .forEach(id => document.getElementById(id).textContent = '0');
}

// ============================================================
// MODAL CONVENIOS KPI
// ============================================================
function abrirModalConveniosKpi(tipo) {
    const conConvenio = misCreditosCargados.filter(c => parseInt(c.tiene_convenio) === 1);
    let lista, titulo, gradiente, icono;

    if (tipo === 'atraso') {
        lista     = conConvenio.filter(c => parseInt(c.tiene_atraso) === 1);
        titulo    = 'Convenios con Atraso';
        gradiente = 'linear-gradient(135deg,#dc3545,#fd7e14)';
        icono     = 'fa-triangle-exclamation';
    } else if (tipo === 'regla') {
        lista     = conConvenio.filter(c => parseInt(c.tiene_atraso) === 0);
        titulo    = 'Convenios en Regla';
        gradiente = 'linear-gradient(135deg,#10b981,#34d399)';
        icono     = 'fa-circle-check';
    } else {
        lista     = conConvenio;
        titulo    = 'Todos los Convenios';
        gradiente = 'linear-gradient(135deg,#0ea5e9,#38bdf8)';
        icono     = 'fa-handshake';
    }

    // Header color dinámico
    const header = document.getElementById('modalConvHeader');
    header.style.background = gradiente;

    // Título
    document.getElementById('modalConvTitulo').innerHTML =
        `<i class="fa-solid ${icono} me-2"></i>${titulo} <span class="badge bg-white text-dark ms-2" style="font-size:0.75rem;">${lista.length}</span>`;

    // Resumen rápido
    const enRegla  = lista.filter(c => parseInt(c.tiene_atraso) === 0).length;
    const enAtraso = lista.filter(c => parseInt(c.tiene_atraso) === 1).length;
    document.getElementById('modalConvResumen').innerHTML = lista.length ? `
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
            <i class="fa-solid fa-circle-check me-1"></i>En regla: ${enRegla}
        </span>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>Con atraso: ${enAtraso}
        </span>` : '';

    // Renderizar lista
    const contenedor = document.getElementById('modalConvLista');
    if (!lista.length) {
        contenedor.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-inbox fa-3x mb-3 d-block opacity-30"></i>
                <p class="mb-0">No hay convenios en esta categoría.</p>
            </div>`;
    } else {
        contenedor.innerHTML = lista.map(c => {
            const realizados   = parseInt(c.conv_pagos_realizados  || 0);
            const totalSemanas = parseInt(c.conv_num_semanas       || 0);
            const pct          = totalSemanas > 0 ? Math.min(100, Math.round((realizados / totalSemanas) * 100)) : 0;
            const esAtraso     = parseInt(c.tiene_atraso) === 1;
            const fillClass    = esAtraso ? 'fill-atraso' : 'fill-regla';
            const badge        = esAtraso
                ? '<span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Con atraso</span>'
                : '<span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i>En regla</span>';

            const nombre = c.nombre_cliente && c.nombre_cliente !== 'No disponible'
                ? escapeHtml(c.nombre_cliente)
                : '<span class="text-muted fst-italic">No disponible</span>';

            const proxPago = c.conv_fecha_proximo_pago
                ? `<span class="text-muted small"><i class="fa-regular fa-calendar me-1"></i>Próximo pago: <strong>${escapeHtml(c.conv_fecha_proximo_pago)}</strong></span>`
                : `<span class="text-muted small">Sin pagos pendientes</span>`;

            const totalStr = c.conv_total_pagar
                ? formatearMoneda(parseFloat(c.conv_total_pagar))
                : '—';
            const pagoStr = c.conv_pago_semanal
                ? formatearMoneda(parseFloat(c.conv_pago_semanal))
                : '—';

            return `
            <div class="conv-kpi-card mb-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
                    <div>
                        <span class="fw-bold text-primary me-2">#${escapeHtml(String(c.id_credito))}</span>
                        ${nombre}
                    </div>
                    ${badge}
                </div>
                <div class="d-flex gap-3 flex-wrap mb-2" style="font-size:0.8rem;">
                    <span class="text-muted"><i class="fa-solid fa-dollar-sign me-1"></i>Total convenio: <strong>${totalStr}</strong></span>
                    <span class="text-muted"><i class="fa-solid fa-coins me-1"></i>Pago periódico: <strong>${pagoStr}</strong></span>
                    ${proxPago}
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="conv-progress-bar flex-grow-1">
                        <div class="conv-progress-fill ${fillClass}" style="width:${pct}%"></div>
                    </div>
                    <span class="text-muted small text-nowrap" style="min-width:90px;">${realizados} / ${totalSemanas} pagos (${pct}%)</span>
                </div>
            </div>`;
        }).join('');
    }

    new bootstrap.Modal(document.getElementById('modalConveniosKpi')).show();
}

// ============================================================
// EXPORTAR
// ============================================================
function exportarMiGestion() {
    Swal.fire({
        title: 'Generando reporte...',
        html: 'Por favor espere.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });

    window.location.href = '/Despachos/ExportarMiGestion';

    setTimeout(() => {
        Swal.close();
        Swal.fire({ title: 'Descarga iniciada', icon: 'success', timer: 2500, showConfirmButton: false });
    }, 2500);
}

// ============================================================
// HELPERS
// ============================================================
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);
}

function formatearMonedaCorta(valor) {
    if (valor >= 1_000_000) return '$' + (valor / 1_000_000).toFixed(1) + 'M';
    if (valor >= 1_000)     return '$' + (valor / 1_000).toFixed(1) + 'K';
    return '$' + valor.toFixed(0);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
