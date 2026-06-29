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
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success" id="btnSubirDocsColaborador">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i>Subir documentos
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnActualizarDocsColaborador">
                <i class="fa-solid fa-rotate me-1"></i>Actualizar
            </button>
        </div>
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

<div class="modal fade" id="misDocsImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Subir documentos</h5>
                    <div class="text-muted small" id="misDocsImportResumenSeleccion">No se han seleccionado archivos.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="file" class="d-none" id="misDocsInputArchivos" accept=".pdf,.zip,.fad,application/pdf,application/zip,application/octet-stream" multiple>
                <input type="file" class="d-none" id="misDocsInputCarpeta" webkitdirectory directory multiple>

                <div class="alert alert-info py-2 mb-3">
                    Si eliges una carpeta, el nombre de la carpeta debe coincidir con tu perfil. Si no coincide, el sistema no importara esos documentos.
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <button type="button" class="btn btn-outline-primary" id="btnMisDocsElegirArchivos">
                        <i class="fa-solid fa-file-zipper me-1"></i>Elegir ZIP/PDF/FAD
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnMisDocsElegirCarpeta">
                        <i class="fa-solid fa-folder-open me-1"></i>Elegir carpeta
                    </button>
                    <button type="button" class="btn btn-success" id="btnMisDocsImportarListos" disabled>
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>Importar listos
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnMisDocsLimpiar">
                        <i class="fa-solid fa-eraser me-1"></i>Limpiar
                    </button>
                </div>

                <div id="misDocsImportResumen" class="d-flex flex-wrap gap-2 mb-3"></div>
                <div class="table-responsive" style="max-height: 55vh;">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Estado</th>
                                <th>Documento</th>
                                <th>Archivo</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="misDocsImportTabla">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Selecciona archivos o una carpeta para analizarlos automaticamente.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let documentos = [];
    let importFiles = [];
    let importAnalisis = null;
    const IMPORT_MAX_FILES_PER_REQUEST = 1000;
    const IMPORT_MAX_BYTES_PER_REQUEST = 850 * 1024 * 1024;

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
        actualizar: document.getElementById('btnActualizarDocsColaborador'),
        subir: document.getElementById('btnSubirDocsColaborador'),
        importModal: document.getElementById('misDocsImportModal'),
        importInputArchivos: document.getElementById('misDocsInputArchivos'),
        importInputCarpeta: document.getElementById('misDocsInputCarpeta'),
        importBtnArchivos: document.getElementById('btnMisDocsElegirArchivos'),
        importBtnCarpeta: document.getElementById('btnMisDocsElegirCarpeta'),
        importBtnImportar: document.getElementById('btnMisDocsImportarListos'),
        importBtnLimpiar: document.getElementById('btnMisDocsLimpiar'),
        importResumenSeleccion: document.getElementById('misDocsImportResumenSeleccion'),
        importResumen: document.getElementById('misDocsImportResumen'),
        importTabla: document.getElementById('misDocsImportTabla')
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

    function importFormData() {
        const fd = new FormData();
        const batchId = String(importAnalisis?.batch_id || '').trim();
        if (batchId) {
            fd.append('batch_id', batchId);
        } else {
            importFiles.forEach(file => {
                fd.append('archivos[]', file, file.name);
                fd.append('rutas_relativas[]', file.webkitRelativePath || file.name);
            });
        }
        (importAnalisis?.items || []).forEach(item => {
            if (item.documento_manual && Number(item.id_documento || 0) > 0) {
                fd.append(`documentos_manual[${Number(item.source_index || 0)}]`, Number(item.id_documento || 0));
            }
        });
        return fd;
    }

    function importBadge(estado, item = null) {
        if (item && item.documento_otros_automatico) {
            return '<span class="badge bg-secondary">Sin tipo</span>';
        }
        const mapa = {
            listo: ['bg-success', 'Listo'],
            importado: ['bg-primary', 'Importado'],
            persona_no_coincide: ['bg-danger', 'No coincide'],
            documento_no_reconocido: ['bg-secondary', 'Sin tipo'],
            ya_existe: ['bg-info text-dark', 'Ya existe'],
            duplicado_lote: ['bg-warning text-dark', 'Duplicado'],
            error: ['bg-danger', 'Error']
        };
        const cfg = mapa[estado] || ['bg-secondary', estado || 'Pendiente'];
        return `<span class="badge ${cfg[0]}">${escapeHtml(cfg[1])}</span>`;
    }

    function renderImportResumen(resumen) {
        if (!els.importResumen) return;
        if (!resumen) {
            els.importResumen.innerHTML = '';
            return;
        }
        const chips = [
            ['Total', resumen.total || 0, 'bg-dark'],
            ['Listos', resumen.listo || 0, 'bg-success'],
            ['Importados', resumen.importado || 0, 'bg-primary'],
            ['No coinciden', resumen.persona_no_coincide || 0, 'bg-danger'],
            ['Sin tipo', resumen.documento_no_reconocido || 0, 'bg-secondary'],
            ['Ya existe', resumen.ya_existe || 0, 'bg-info text-dark'],
            ['Duplicados', resumen.duplicado_lote || 0, 'bg-warning text-dark'],
            ['Errores', resumen.error || 0, 'bg-danger']
        ];
        els.importResumen.innerHTML = chips
            .filter(([, valor], index) => index === 0 || Number(valor) > 0)
            .map(([label, valor, cls]) => `<span class="badge ${cls}">${escapeHtml(label)}: ${escapeHtml(valor)}</span>`)
            .join('');
    }

    function renderImportTabla(items) {
        if (!els.importTabla) return;
        if (!items || !items.length) {
            els.importTabla.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Selecciona archivos o una carpeta para analizarlos automaticamente.</td></tr>';
            return;
        }
        const catalogo = Array.isArray(importAnalisis?.catalogo) ? importAnalisis.catalogo : [];
        els.importTabla.innerHTML = items.map(item => {
            const idDocumento = Number(item.id_documento || 0);
            const opciones = [
                '<option value="">Seleccione tipo</option>',
                ...catalogo.map(doc => {
                    const selected = Number(doc.id || 0) === idDocumento ? 'selected' : '';
                    return `<option value="${Number(doc.id || 0)}" ${selected}>${escapeHtml(doc.nombre || '')}</option>`;
                })
            ].join('');
            return `
                <tr>
                    <td>${importBadge(item.estado, item)}</td>
                    <td>
                        <select class="form-select form-select-sm" data-mis-doc-type="${Number(item.source_index || 0)}">
                            ${opciones}
                        </select>
                        ${item.documento_manual ? '<div class="text-primary small mt-1">Seleccion manual</div>' : ''}
                    </td>
                    <td><span class="text-break">${escapeHtml(item.ruta || item.archivo || '')}</span></td>
                    <td>${escapeHtml(item.razon || '')}</td>
                </tr>
            `;
        }).join('');
    }

    function setImportLoading(loading, texto = 'Procesando...') {
        [els.importBtnArchivos, els.importBtnCarpeta, els.importBtnLimpiar, els.importBtnImportar].forEach(btn => {
            if (btn) btn.disabled = loading || (btn === els.importBtnImportar && !(importAnalisis?.resumen?.listo > 0));
        });
        if (loading && els.importResumenSeleccion) {
            els.importResumenSeleccion.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>${escapeHtml(texto)}`;
        }
    }

    async function importEnviar(url) {
        let res;
        try {
            res = await fetch(url, {
                method: 'POST',
                body: importFormData(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        } catch (err) {
            throw new Error('No se pudo conectar con el servidor. Revisa que la seleccion no supere 1000 archivos o 850 MB por intento.');
        }
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const texto = await res.text();
            throw new Error(texto ? texto.slice(0, 250) : 'Respuesta no JSON del servidor.');
        }
        const json = await res.json();
        if (!json.success) {
            throw new Error(json.mensaje || 'La operacion no se completo.');
        }
        return json.datos || {};
    }

    async function analizarImportacion() {
        if (!importFiles.length) return;
        try {
            setImportLoading(true, 'Analizando documentos...');
            importAnalisis = await importEnviar('/caphum/analizarMisDocumentos');
            renderImportResumen(importAnalisis.resumen);
            renderImportTabla(importAnalisis.items || []);
            const listos = Number(importAnalisis?.resumen?.listo || 0);
            els.importResumenSeleccion.textContent = `Analisis listo. ${listos} documento(s) pueden importarse.`;
            els.importBtnImportar.disabled = listos <= 0;
        } catch (err) {
            Swal.fire('Subir documentos', err.message || 'No se pudo analizar la seleccion.', 'error');
        } finally {
            setImportLoading(false);
        }
    }

    function limpiarImportacion() {
        importFiles = [];
        importAnalisis = null;
        if (els.importInputArchivos) els.importInputArchivos.value = '';
        if (els.importInputCarpeta) els.importInputCarpeta.value = '';
        els.importResumenSeleccion.textContent = 'No se han seleccionado archivos.';
        renderImportResumen(null);
        renderImportTabla([]);
        els.importBtnImportar.disabled = true;
    }

    function seleccionarImportFiles(fileList) {
        const archivos = Array.from(fileList || []).filter(file => /\.(pdf|zip|fad)$/i.test(file.name || ''));
        if (!archivos.length) {
            Swal.fire('Subir documentos', 'Selecciona archivos PDF, FAD, ZIP o una carpeta con documentos.', 'warning');
            return;
        }
        if (archivos.length > IMPORT_MAX_FILES_PER_REQUEST) {
            Swal.fire('Subir documentos', `Selecciona maximo ${IMPORT_MAX_FILES_PER_REQUEST} archivos por intento.`, 'warning');
            return;
        }
        const totalBytes = archivos.reduce((sum, file) => sum + Number(file.size || 0), 0);
        if (totalBytes > IMPORT_MAX_BYTES_PER_REQUEST) {
            Swal.fire('Subir documentos', 'La seleccion supera 850 MB. Divide la carga en partes mas pequenas.', 'warning');
            return;
        }

        importFiles = archivos;
        importAnalisis = null;
        els.importResumenSeleccion.textContent = `Seleccionados ${archivos.length} archivo(s). Analizando...`;
        renderImportResumen(null);
        renderImportTabla([]);
        analizarImportacion();
    }

    async function importarListos() {
        const listos = Number(importAnalisis?.resumen?.listo || 0);
        if (!listos) return;
        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Importar documentos',
            text: `Se importaran ${listos} documento(s) listos a tu expediente.`,
            showCancelButton: true,
            confirmButtonText: 'Importar',
            cancelButtonText: 'Cancelar'
        });
        if (!confirm.isConfirmed) return;

        try {
            setImportLoading(true, 'Importando documentos...');
            const resultado = await importEnviar('/caphum/importarMisDocumentos');
            importAnalisis = resultado;
            renderImportResumen(resultado.resumen);
            renderImportTabla(resultado.items || []);
            const importados = Number(resultado.importados || 0);
            els.importResumenSeleccion.textContent = `Carga finalizada. ${importados} documento(s) importado(s).`;
            await cargarResumen();
            Swal.fire('Subir documentos', `Se importaron ${importados} documento(s).`, 'success');
        } catch (err) {
            Swal.fire('Subir documentos', err.message || 'No se pudo importar la seleccion.', 'error');
        } finally {
            setImportLoading(false);
        }
    }

    els.buscar.addEventListener('input', renderTabla);
    els.filtro.addEventListener('change', renderTabla);
    els.actualizar.addEventListener('click', cargarResumen);
    els.subir.addEventListener('click', () => {
        limpiarImportacion();
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(els.importModal).show();
        } else if (window.jQuery) {
            window.jQuery(els.importModal).modal('show');
        }
    });
    els.importBtnArchivos.addEventListener('click', () => els.importInputArchivos.click());
    els.importBtnCarpeta.addEventListener('click', () => els.importInputCarpeta.click());
    els.importBtnLimpiar.addEventListener('click', limpiarImportacion);
    els.importBtnImportar.addEventListener('click', importarListos);
    els.importInputArchivos.addEventListener('change', event => seleccionarImportFiles(event.target.files));
    els.importInputCarpeta.addEventListener('change', event => seleccionarImportFiles(event.target.files));
    els.importTabla.addEventListener('change', event => {
        const select = event.target.closest('[data-mis-doc-type]');
        if (!select || !importAnalisis) return;
        const sourceIndex = Number(select.dataset.misDocType || 0);
        const item = (importAnalisis.items || []).find(row => Number(row.source_index || 0) === sourceIndex);
        if (!item) return;
        item.id_documento = Number(select.value || 0);
        const doc = (importAnalisis.catalogo || []).find(row => Number(row.id || 0) === item.id_documento);
        item.documento = doc ? (doc.nombre || '') : '';
        item.documento_clave = doc ? (doc.clave || '') : '';
        item.documento_manual = item.id_documento > 0;
        analizarImportacion();
    });
    document.addEventListener('DOMContentLoaded', cargarResumen);
})();
</script>
