<style>
    .madj-rep-page {
        --madj-navy: #24334f;
        --madj-teal: #0f9d92;
    }
    .madj-rep-hero {
        border-radius: .85rem;
        background: linear-gradient(120deg, #24334f 0%, #2f63d9 100%);
        color: #fff;
        box-shadow: 0 .65rem 1.6rem rgba(36, 51, 79, .18);
    }
    .madj-rep-card {
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .05);
    }
    .madj-rep-table td,
    .madj-rep-table th {
        vertical-align: middle;
    }
    .madj-rep-main {
        min-width: 230px;
    }
    .madj-rep-folio {
        display: inline-flex;
        background: rgba(245, 158, 11, .12);
        color: #b45309;
        border-radius: 999px;
        padding: .16rem .55rem;
        font-weight: 800;
        font-size: .75rem;
    }
    .madj-rep-strong {
        color: var(--madj-navy);
        font-weight: 800;
    }
    .madj-rep-muted {
        color: var(--bs-secondary-color);
        font-size: .78rem;
        line-height: 1.25;
    }
    .madj-rep-alert {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        padding: .18rem .5rem;
        font-size: .72rem;
        font-weight: 700;
        background: rgba(245, 158, 11, .14);
        color: #b45309;
    }
    html.dark-mode .madj-rep-card,
    body.dark-mode .madj-rep-card {
        background: #111827;
        border-color: #243246;
    }
    html.dark-mode .madj-rep-strong,
    body.dark-mode .madj-rep-strong {
        color: #e5edf7;
    }
</style>

<div class="container-fluid py-3 madj-rep-page" id="madjReporteApp">
    <div class="madj-rep-hero p-4 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 text-white"><i class="fa-solid fa-table-list me-2"></i>Reporte de seguimiento</h3>
                <p class="mb-0" style="color:rgba(255,255,255,.76)">Seguimiento consolidado de operaciones de motos adjudicadas.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/MotosAdjudicadas/reporteria" class="btn btn-light">
                    <i class="fa-solid fa-arrow-left me-1"></i>Reporteria
                </a>
                <button type="button" class="btn btn-outline-light" id="madjRepCsv">
                    <i class="fa-solid fa-file-csv me-1"></i>CSV
                </button>
            </div>
        </div>
    </div>

    <div class="card madj-rep-card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold" for="madjRepDesde">Desde</label>
                    <input type="date" class="form-control" id="madjRepDesde">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold" for="madjRepHasta">Hasta</label>
                    <input type="date" class="form-control" id="madjRepHasta">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold" for="madjRepEstatus">Estatus</label>
                    <select class="form-select" id="madjRepEstatus"><option value="">Todos</option></select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold" for="madjRepEstado">Estado</label>
                    <select class="form-select" id="madjRepEstado"><option value="">Todos</option></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold" for="madjRepQ">Buscar</label>
                    <input type="search" class="form-control" id="madjRepQ" placeholder="Credito, cliente, VIN, folio...">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="button" class="btn btn-primary w-100" id="madjRepBuscar" title="Buscar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="madjRepLimpiar" title="Limpiar">
                        <i class="fa-solid fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card madj-rep-card">
        <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span>Mostrar</span>
                <select class="form-select form-select-sm" id="madjRepLimit" style="width: 95px;">
                    <option value="100">100</option>
                    <option value="200" selected>200</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                </select>
                <span>registros</span>
            </div>
            <span class="badge bg-label-primary" id="madjRepTotal">0 registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 madj-rep-table">
                <thead>
                    <tr>
                        <th>Operacion</th>
                        <th>Ubicacion</th>
                        <th>Unidad</th>
                        <th>Seguimiento</th>
                        <th>Alertas</th>
                    </tr>
                </thead>
                <tbody id="madjRepBody">
                    <tr><td colspan="5" class="text-center text-muted py-4">Cargando informacion...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const app = document.getElementById('madjReporteApp');
    if (!app) return;
    let rows = [];
    let catalogsLoaded = false;

    const fmt = new Intl.NumberFormat('es-MX');

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    function fechaCorta(value) {
        if (!value) return 'Sin fecha';
        const str = String(value).replace(' ', 'T');
        const d = new Date(str);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    function cargarCatalogo(selectId, items) {
        const sel = document.getElementById(selectId);
        const actual = sel.value;
        sel.innerHTML = '<option value="">Todos</option>' + (items || []).map(it => {
            const label = String(it.label || '');
            return `<option value="${escapeHtml(label)}">${escapeHtml(label)} (${fmt.format(it.total || 0)})</option>`;
        }).join('');
        sel.value = actual;
    }

    function render() {
        const body = document.getElementById('madjRepBody');
        document.getElementById('madjRepTotal').textContent = `${fmt.format(rows.length)} registros`;
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Sin registros para mostrar</td></tr>';
            return;
        }

        body.innerHTML = rows.map(row => {
            const alertas = [];
            if (Number(row.ubicacion_incompleta || 0) === 1) alertas.push('<span class="madj-rep-alert"><i class="fa-solid fa-location-dot"></i>Ubicacion incompleta</span>');
            if (Number(row.unidad_incompleta || 0) === 1) alertas.push('<span class="madj-rep-alert"><i class="fa-solid fa-motorcycle"></i>Unidad incompleta</span>');
            if (!alertas.length) alertas.push('<span class="badge bg-label-success">Sin alerta</span>');

            return `<tr>
                <td class="madj-rep-main">
                    <span class="madj-rep-folio">${escapeHtml(row.folio || ('ADJ-' + row.id))}</span>
                    <div class="madj-rep-strong mt-2">#${escapeHtml(row.id_credito)} ${escapeHtml(row.nombre_cliente)}</div>
                    <div class="madj-rep-muted">Operacion interna: ${escapeHtml(row.id)}</div>
                </td>
                <td>
                    <div class="madj-rep-strong"><i class="fa-solid fa-location-dot me-1 text-primary"></i>${escapeHtml(row.estado_normalizado)}</div>
                    <div class="madj-rep-muted">${escapeHtml(row.municipio)}</div>
                    <div class="madj-rep-muted text-truncate" style="max-width: 320px;">${escapeHtml(row.direccion)}</div>
                </td>
                <td>
                    <div class="madj-rep-strong">${escapeHtml(row.unidad || 'Sin unidad')}</div>
                    <div class="madj-rep-muted">VIN: ${escapeHtml(row.vin || 'No disponible')}</div>
                    <div class="madj-rep-muted">Placas: ${escapeHtml(row.placas || 'No disponible')}</div>
                </td>
                <td>
                    <span class="badge bg-label-primary">${escapeHtml(row.estatus)}</span>
                    <div class="madj-rep-muted mt-2"><i class="fa-solid fa-calendar-plus me-1"></i>Alta: ${escapeHtml(fechaCorta(row.fecha_alta))}</div>
                    <div class="madj-rep-muted"><i class="fa-solid fa-rotate me-1"></i>Actualizacion: ${escapeHtml(fechaCorta(row.fecha_actualizacion))}</div>
                    <div class="madj-rep-muted"><i class="fa-solid fa-warehouse me-1"></i>Almacen: ${escapeHtml(fechaCorta(row.fecha_llegada_almacen))}</div>
                </td>
                <td><div class="d-flex flex-column gap-1">${alertas.join('')}</div></td>
            </tr>`;
        }).join('');
    }

    async function cargar() {
        const qs = new URLSearchParams();
        ['Desde', 'Hasta', 'Estatus', 'Estado', 'Q', 'Limit'].forEach(key => {
            const el = document.getElementById('madjRep' + key);
            if (el && el.value) qs.set(key.toLowerCase(), el.value);
        });
        const resp = await fetch('/MotosAdjudicadas/reporteSeguimientoDatos?' + qs.toString(), { headers: { 'Accept': 'application/json' } });
        const json = await resp.json();
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el reporte.');
        const datos = json.datos || {};
        rows = datos.rows || [];
        if (!catalogsLoaded) {
            cargarCatalogo('madjRepEstatus', (datos.catalogos || {}).estatus || []);
            cargarCatalogo('madjRepEstado', (datos.catalogos || {}).estados || []);
            catalogsLoaded = true;
        }
        render();
    }

    function descargarCsv() {
        if (!rows.length) return;
        const cols = ['id', 'folio', 'id_credito', 'nombre_cliente', 'estatus', 'estado_normalizado', 'municipio', 'direccion', 'unidad', 'vin', 'fecha_alta', 'fecha_actualizacion', 'fecha_llegada_almacen'];
        const csv = [cols.join(',')].concat(rows.map(row => cols.map(c => {
            const v = String(row[c] ?? '').replace(/"/g, '""');
            return `"${v}"`;
        }).join(','))).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'ReporteSeguimiento_MotosAdjudicadas.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(a.href);
    }

    document.getElementById('madjRepBuscar').addEventListener('click', cargar);
    document.getElementById('madjRepLimit').addEventListener('change', cargar);
    document.getElementById('madjRepCsv').addEventListener('click', descargarCsv);
    document.getElementById('madjRepLimpiar').addEventListener('click', () => {
        ['Desde', 'Hasta', 'Estatus', 'Estado', 'Q'].forEach(key => {
            const el = document.getElementById('madjRep' + key);
            if (el) el.value = '';
        });
        cargar();
    });
    document.getElementById('madjRepQ').addEventListener('keydown', ev => {
        if (ev.key === 'Enter') cargar();
    });

    cargar().catch(err => {
        document.getElementById('madjRepBody').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${escapeHtml(err.message)}</td></tr>`;
    });
})();
</script>
