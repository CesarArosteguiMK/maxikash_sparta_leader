<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="btn btn-primary rounded-3 disabled">
                <i class="fa-solid fa-folder-open"></i>
            </span>
            <div>
                <h1 class="h3 mb-1">Mis documentos</h1>
                <p class="text-muted mb-0">Consulta el avance de tu expediente documental.</p>
            </div>
        </div>
        <button type="button" class="btn btn-outline-primary" id="btnActualizarDocsColaborador">
            <i class="fa-solid fa-rotate me-1"></i>Actualizar
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <div class="text-muted small">Colaborador</div>
                    <h2 class="h5 mb-0" id="docColaboradorNombre">Cargando...</h2>
                    <div class="text-muted small" id="docColaboradorDetalle"></div>
                </div>
                <span class="badge bg-label-primary px-3 py-2" id="docColaboradorBadge">Expediente</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Documentos requeridos</div>
                        <div class="h3 mb-0" id="docTotalRequeridos">0</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Cargados</div>
                        <div class="h3 text-success mb-0" id="docTotalCargados">0</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Faltantes</div>
                        <div class="h3 text-warning mb-0" id="docTotalFaltantes">0</div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Avance</div>
                        <div class="h3 text-primary mb-0" id="docPorcentaje">0%</div>
                    </div>
                </div>
            </div>

            <div class="progress" style="height: 12px;">
                <div class="progress-bar bg-success" id="docProgressBar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <h2 class="h5 mb-0">
                    <i class="fa-solid fa-list-check me-2"></i>Detalle de documentos
                </h2>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select class="form-select form-select-sm" id="docFiltroEstatus" style="width: 150px;">
                        <option value="">Todos</option>
                        <option value="Faltante">Faltantes</option>
                        <option value="Cargado">Cargados</option>
                    </select>
                    <input type="search" class="form-control form-control-sm" id="docBuscar" placeholder="Buscar documento" style="width: 220px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th class="text-center">Archivos</th>
                            <th>Última carga</th>
                            <th class="text-end">Estatus</th>
                        </tr>
                    </thead>
                    <tbody id="docTablaBody">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <span class="spinner-border spinner-border-sm me-2"></span>Cargando documentos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let documentos = [];

    const els = {
        nombre: document.getElementById('docColaboradorNombre'),
        detalle: document.getElementById('docColaboradorDetalle'),
        badge: document.getElementById('docColaboradorBadge'),
        requeridos: document.getElementById('docTotalRequeridos'),
        cargados: document.getElementById('docTotalCargados'),
        faltantes: document.getElementById('docTotalFaltantes'),
        porcentaje: document.getElementById('docPorcentaje'),
        progress: document.getElementById('docProgressBar'),
        body: document.getElementById('docTablaBody'),
        buscar: document.getElementById('docBuscar'),
        filtro: document.getElementById('docFiltroEstatus'),
        actualizar: document.getElementById('btnActualizarDocsColaborador')
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderTabla() {
        const q = (els.buscar.value || '').trim().toLowerCase();
        const estatus = els.filtro.value || '';
        const filtrados = documentos.filter(doc => {
            const coincideTexto = !q || String(doc.nombre || '').toLowerCase().includes(q);
            const coincideEstatus = !estatus || doc.estatus === estatus;
            return coincideTexto && coincideEstatus;
        });

        if (!filtrados.length) {
            els.body.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No hay documentos para mostrar.</td></tr>';
            return;
        }

        els.body.innerHTML = filtrados.map(doc => {
            const cargado = !!doc.cargado;
            const badge = cargado
                ? '<span class="badge bg-success">Cargado</span>'
                : '<span class="badge bg-warning text-dark">Faltante</span>';
            return `
                <tr>
                    <td>
                        <div class="fw-semibold">${escapeHtml(doc.nombre)}</div>
                        ${doc.clave ? `<div class="text-muted small">${escapeHtml(doc.clave)}</div>` : ''}
                    </td>
                    <td class="text-center">${Number(doc.total_archivos || 0)}</td>
                    <td>${doc.ultima_fecha ? escapeHtml(doc.ultima_fecha) : '<span class="text-muted">Sin carga</span>'}</td>
                    <td class="text-end">${badge}</td>
                </tr>
            `;
        }).join('');
    }

    function aplicarResumen(data) {
        const persona = data.persona || {};
        const metricas = data.metricas || {};
        documentos = Array.isArray(data.documentos) ? data.documentos : [];

        els.nombre.textContent = persona.nombre_completo || 'Colaborador';
        els.detalle.textContent = [persona.numero_empleado ? `# ${persona.numero_empleado}` : '', persona.correo || '']
            .filter(Boolean)
            .join(' · ');

        const requeridos = Number(metricas.total_requeridos || 0);
        const cargados = Number(metricas.total_cargados || 0);
        const faltantes = Number(metricas.total_faltantes || 0);
        const porcentaje = Number(metricas.porcentaje || 0);

        els.requeridos.textContent = requeridos;
        els.cargados.textContent = cargados;
        els.faltantes.textContent = faltantes;
        els.porcentaje.textContent = `${porcentaje}%`;
        els.progress.style.width = `${Math.min(100, Math.max(0, porcentaje))}%`;
        els.progress.setAttribute('aria-valuenow', String(porcentaje));
        els.badge.textContent = faltantes > 0 ? 'Pendiente' : 'Completo';
        els.badge.className = faltantes > 0 ? 'badge bg-warning text-dark px-3 py-2' : 'badge bg-success px-3 py-2';

        renderTabla();
    }

    async function cargarResumen() {
        els.actualizar.disabled = true;
        els.actualizar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando';
        try {
            const res = await fetch('/caphum/getResumenDocumentosColaborador', { cache: 'no-store' });
            const json = await res.json();
            if (!json.success) throw new Error(json.mensaje || 'No se pudo cargar el resumen.');
            aplicarResumen(json.datos || {});
        } catch (err) {
            els.body.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${escapeHtml(err.message || 'Error al cargar documentos.')}</td></tr>`;
        } finally {
            els.actualizar.disabled = false;
            els.actualizar.innerHTML = '<i class="fa-solid fa-rotate me-1"></i>Actualizar';
        }
    }

    els.buscar.addEventListener('input', renderTabla);
    els.filtro.addEventListener('change', renderTabla);
    els.actualizar.addEventListener('click', cargarResumen);
    document.addEventListener('DOMContentLoaded', cargarResumen);
})();
</script>
