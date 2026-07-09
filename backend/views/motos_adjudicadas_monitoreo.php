<style>
    .madj-monitor-card {
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        box-shadow: 0 .5rem 1.5rem rgba(15, 23, 42, .06);
    }
    .madj-monitor-table td {
        vertical-align: middle;
    }
    .madj-monitor-name {
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .madj-op-main { min-width: 220px; }
    .madj-op-folio {
        font-size: .86rem;
        font-weight: 700;
        color: var(--bs-heading-color);
        line-height: 1.15;
    }
    .madj-op-credito {
        font-size: .82rem;
        font-weight: 700;
        color: var(--bs-primary);
        line-height: 1.15;
        margin-top: .1rem;
    }
    .madj-op-cliente {
        font-size: .78rem;
        font-weight: 700;
        color: var(--bs-secondary-color);
        line-height: 1.2;
        margin-top: .15rem;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .madj-op-fecha {
        font-size: .74rem;
        color: var(--bs-secondary-color);
        line-height: 1.15;
        margin-top: .1rem;
    }
    .madj-search-select { position: relative; }
    .madj-search-toggle {
        min-height: 38px;
        cursor: pointer;
        user-select: none;
    }
    .madj-search-menu {
        display: none;
        position: absolute;
        z-index: 3000;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: .5rem;
        box-shadow: 0 .75rem 1.75rem rgba(15, 23, 42, .16);
        padding: .5rem;
        width: 100%;
        min-width: 100%;
    }
    .madj-search-select.open .madj-search-menu { display: block; }
    .madj-search-options {
        max-height: 210px;
        overflow-y: auto;
        margin-top: .5rem;
        overscroll-behavior: contain;
    }
    .madj-inline-loading {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
    }
    .madj-inline-loading .spinner-border {
        width: .9rem;
        height: .9rem;
        border-width: .14rem;
    }
    .madj-search-option {
        border-radius: .35rem;
        padding: .45rem .55rem;
        cursor: pointer;
    }
    .madj-search-option:hover,
    .madj-search-option.active { background: var(--bs-gray-200); }
    .madj-search-option-name {
        font-size: .9rem;
        color: var(--bs-body-color);
        line-height: 1.2;
    }
    .madj-search-option-sub {
        font-size: .76rem;
        color: var(--bs-secondary-color);
        line-height: 1.2;
        margin-top: .15rem;
    }
    .madj-pagination {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .madj-pagination .madj-page-btn,
    .madj-pagination .madj-page-ellipsis {
        min-width: 38px;
        height: 38px;
        border-radius: .5rem !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 .65rem;
        border: 1px solid #edf0f4;
        background: #fff;
        color: #566476;
        box-shadow: none;
        font-weight: 500;
    }
    .madj-pagination .madj-page-btn.active {
        background: #4056a5;
        border-color: #4056a5;
        color: #fff;
        box-shadow: 0 .35rem .75rem rgba(64, 86, 165, .28);
    }
    .madj-pagination .madj-page-btn:disabled,
    .madj-pagination .madj-page-ellipsis {
        color: #a8b0bd;
        opacity: 1;
        background: #fff;
    }
    .madj-page-info {
        color: #a8b0bd !important;
        font-size: .92rem;
        font-weight: 500;
    }
</style>

<div class="container-fluid py-3" id="madjMonitoreoApp">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h4 class="mb-1">
                <i class="fa-solid fa-magnifying-glass-chart me-2 text-primary"></i>Monitoreo
            </h4>
            <p class="text-muted mb-0">Consulta operaciones, responsables y reasigna créditos sin entrar directo a la base de datos.</p>
        </div>
        <button type="button" class="btn btn-primary" id="btnMadjMonitoreoActualizar">
            <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
        </button>
    </div>

    <div class="card madj-monitor-card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="madjFiltroEtapa">Estatus</label>
                    <select class="form-select" id="madjFiltroEtapa">
                        <option value="">Todos</option>
                        <option value="evidencias">Evidencias</option>
                        <option value="recuperacion">Recuperación</option>
                        <option value="cartera">Cartera</option>
                        <option value="recepcion">Recepción</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="btnMadjMonitoreoLimpiar" title="Limpiar filtros">
                        <i class="fa-solid fa-eraser"></i>
                    </button>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-md-end gap-2">
                    <label class="mb-0" for="madjBusquedaGlobal">Buscar:</label>
                    <input type="search" class="form-control" id="madjBusquedaGlobal" style="max-width: 260px;">
                </div>
            </div>
        </div>
    </div>

    <div class="card madj-monitor-card">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span>Mostrar</span>
                <select class="form-select form-select-sm" id="madjPageSize" style="width: 92px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>registros</span>
            </div>
            <span class="badge bg-label-primary" id="madjMonitoreoTotal">0 registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 madj-monitor-table">
                <thead class="table-light">
                    <tr>
                        <th>Operación</th>
                        <th>Levantado por</th>
                        <th>Responsable actual</th>
                        <th>Seguimiento</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="madjMonitoreoTbody">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Cargando información...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="madj-page-info" id="madjPageInfo">Mostrando de 0 a 0 de 0 registros</span>
            <div class="madj-pagination" id="madjPager" role="navigation" aria-label="Paginación"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMadjMonitoreo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-motorcycle me-2 text-primary"></i>Detalle de adjudicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="border rounded p-3 h-100">
                            <div id="madjDetalleResumen" class="row g-3"></div>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="madjDetalleEstatus">Estatus</label>
                                    <select class="form-select" id="madjDetalleEstatus">
                                        <option value="Recibido">Evidencias</option>
                                        <option value="Revisión Recuperaciones">Recuperación</option>
                                        <option value="Cierre Documentado">Cartera</option>
                                        <option value="Recepción">Recepción</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-primary w-100" id="btnMadjGuardarEstatus">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>Actualizar estatus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-3">Responsable</h6>
                            <label class="form-label fw-semibold" for="madjBuscarPersona">Persona destino</label>
                            <div class="madj-search-select" id="madjPersonaSelect">
                                <button type="button" class="form-control d-flex align-items-center justify-content-between text-start madj-search-toggle" id="madjPersonaToggle">
                                    <span id="madjPersonaTexto" class="text-muted">Seleccione una persona</span>
                                    <i class="fa-solid fa-chevron-down ms-2"></i>
                                </button>
                                <div class="madj-search-menu">
                                    <input type="search" class="form-control" id="madjBuscarPersona" placeholder="Nombre, número o ID">
                                    <div class="madj-search-options" id="madjPersonaOpciones"></div>
                                </div>
                            </div>
                            <input type="hidden" id="madjPersonaDestino">
                            <button type="button" class="btn btn-primary w-100 mt-3" id="btnMadjReasignar">
                                <i class="fa-solid fa-user-check me-1"></i>Actualizar responsable
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var operaciones = [];
    var actual = null;
    var operacionesFiltradas = [];
    var paginaActual = 1;
    var personasCache = [];
    var personasCargadas = false;
    var personasRequestSeq = 0;

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function postJson(endpoint, data, onSuccess, onError, showLoader) {
        http.request({
            endpoint: endpoint,
            metodo: 'POST',
            data: JSON.stringify(data || {}),
            contentType: 'application/json',
            processData: false,
            showLoader: showLoader !== false,
            onSuccess: onSuccess,
            onError: onError || function (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: (err && (err.message || err.mensaje)) || 'No se pudo completar la operación.' });
            }
        });
    }

    function filtros() {
        return {
            etapa: $('#madjFiltroEtapa').val()
        };
    }

    function textoEtapa(estatus) {
        if (estatus === 'Recibido' || estatus === 'en_transito') return 'Evidencias';
        if (estatus === 'Procesando IA' || estatus === 'Revisión Recuperaciones') return 'Recuperación';
        if (estatus === 'Cierre Documentado') return 'Cartera';
        if (estatus === 'Recepción') return 'Recepción';
        return estatus || '-';
    }

    function renderTabla() {
        var $tb = $('#madjMonitoreoTbody');
        var pageSize = parseInt($('#madjPageSize').val(), 10) || 10;
        var total = operacionesFiltradas.length;
        var totalPaginas = Math.max(1, Math.ceil(total / pageSize));
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;
        var inicio = total ? ((paginaActual - 1) * pageSize) : 0;
        var fin = Math.min(inicio + pageSize, total);
        var paginaRows = operacionesFiltradas.slice(inicio, fin);

        $('#madjMonitoreoTotal').text(total + ' registros');
        $('#madjPageInfo').text(total ? ('Mostrando de ' + (inicio + 1) + ' a ' + fin + ' de ' + total + ' registros') : 'Mostrando de 0 a 0 de 0 registros');
        renderPager(totalPaginas);

        if (!operacionesFiltradas.length) {
            $tb.html('<tr><td colspan="5" class="text-center text-muted py-4">No se encontraron operaciones con esos filtros.</td></tr>');
            return;
        }
        $tb.html(paginaRows.map(function (r) {
            var responsable = r.responsable_actual || 'Sin responsable';
            var puesto = r.responsable_puesto ? '<div class="small text-muted">' + esc(r.responsable_puesto) + '</div>' : '';
            return '<tr>' +
                '<td><div class="madj-op-main">' +
                    '<div class="madj-op-folio">' + esc(r.folio || ('Operación ' + r.id)) + '</div>' +
                    '<div class="madj-op-credito">#' + esc(r.id_credito || '-') + '</div>' +
                    '<div class="madj-op-cliente" title="' + esc(r.nombre_cliente || '') + '">' + esc(r.nombre_cliente || '-') + '</div>' +
                    '<div class="madj-op-fecha">' + esc(r.fecha_actualizacion || r.fecha_alta || '-') + '</div>' +
                '</div></td>' +
                '<td><div class="madj-monitor-name" title="' + esc(r.levantado_por || '') + '">' + esc(r.levantado_por || 'Sistema') + '</div></td>' +
                '<td><div class="fw-semibold madj-monitor-name" title="' + esc(responsable) + '">' + esc(responsable) + '</div>' + puesto + '</td>' +
                '<td><div><span class="badge bg-label-primary">' + esc(textoEtapa(r.estatus)) + '</span></div><div class="mt-2"><span class="badge bg-label-info me-1">' + esc(r.evidencias_count || 0) + ' evid.</span><span class="badge bg-label-warning">' + esc(r.observaciones_count || 0) + ' obs.</span></div></td>' +
                '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary" data-madj-detalle="' + esc(r.id) + '"><i class="fa-solid fa-pen-to-square me-1"></i>Gestionar</button></td>' +
            '</tr>';
        }).join(''));
    }

    function renderPager(totalPaginas) {
        var $pager = $('#madjPager');
        var disabledFirst = paginaActual <= 1 ? ' disabled' : '';
        var disabledLast = paginaActual >= totalPaginas ? ' disabled' : '';
        var html = '<button type="button" class="madj-page-btn" ' + disabledFirst + ' data-page="1">«</button>';
        html += '<button type="button" class="madj-page-btn" ' + disabledFirst + ' data-page="' + (paginaActual - 1) + '">‹</button>';

        var pages = [];
        if (totalPaginas <= 7) {
            for (var i = 1; i <= totalPaginas; i++) pages.push(i);
        } else {
            pages.push(1);
            var start = Math.max(2, paginaActual - 1);
            var end = Math.min(totalPaginas - 1, paginaActual + 1);
            if (start > 2) pages.push('...');
            for (var j = start; j <= end; j++) pages.push(j);
            if (end < totalPaginas - 1) pages.push('...');
            pages.push(totalPaginas);
        }

        pages.forEach(function (p) {
            if (p === '...') {
                html += '<span class="madj-page-ellipsis">...</span>';
            } else {
                html += '<button type="button" class="madj-page-btn ' + (p === paginaActual ? 'active' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
        });

        html += '<button type="button" class="madj-page-btn" ' + disabledLast + ' data-page="' + (paginaActual + 1) + '">›</button>';
        html += '<button type="button" class="madj-page-btn" ' + disabledLast + ' data-page="' + totalPaginas + '">»</button>';
        $pager.html(html);
    }

    function aplicarBusquedaLocal() {
        var q = ($('#madjBusquedaGlobal').val() || '').toString().trim().toLowerCase();
        if (!q) {
            operacionesFiltradas = operaciones.slice();
        } else {
            operacionesFiltradas = operaciones.filter(function (r) {
                return [
                    r.id_credito,
                    r.folio,
                    r.nombre_cliente,
                    r.levantado_por,
                    r.responsable_actual,
                    r.responsable_puesto,
                    r.estatus,
                    textoEtapa(r.estatus)
                ].join(' ').toLowerCase().indexOf(q) !== -1;
            });
        }
        paginaActual = 1;
        renderTabla();
    }

    function cargar() {
        $('#madjMonitoreoTbody').html('<tr><td colspan="5" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Cargando información...</td></tr>');
        postJson('/MotosAdjudicadas/obtenerMonitoreoAdjudicaciones', { filtros: filtros() }, function (resp) {
            if (!resp || resp.success === false) {
                Swal.fire({ icon: 'error', title: 'Monitoreo', text: (resp && resp.message) || 'No se pudo cargar la información.' });
                operaciones = [];
            } else {
                operaciones = resp.datos || [];
            }
            aplicarBusquedaLocal();
        });
    }

    function resumenItem(label, value) {
        return '<div class="col-md-6"><div class="small text-muted">' + esc(label) + '</div><div class="fw-semibold">' + esc(value || '-') + '</div></div>';
    }

    function abrirDetalle(id) {
        actual = operaciones.filter(function (r) { return String(r.id) === String(id); })[0] || null;
        if (!actual) return;
        $('#madjDetalleEstatus').val(actual.estatus || 'Recibido');
        resetPersonaDestino();
        $('#madjDetalleResumen').html(
            resumenItem('ID crédito', actual.id_credito) +
            resumenItem('Cliente', actual.nombre_cliente) +
            resumenItem('Quién lo levantó', actual.levantado_por || 'Sistema') +
            resumenItem('Responsable actual', actual.responsable_actual || 'Sin responsable') +
            resumenItem('Asignado por', actual.asignado_por || '-') +
            resumenItem('Fecha de asignación', actual.fecha_asignacion_actual || '-') +
            resumenItem('Moto', [actual.marca, actual.modelo, actual.serie].filter(Boolean).join(' / ') || '-') +
            resumenItem('Contacto', actual.telefono_contacto || '-')
        );
        buscarPersonas();
        $('#modalMadjMonitoreo').modal('show');
    }

    function buscarPersonas() {
        var q = $('#madjBuscarPersona').val();
        var reqId = ++personasRequestSeq;
        renderPersonasOpciones(q, true);
        postJson('/MotosAdjudicadas/buscarPersonasMonitoreo', { buscar: q }, function (resp) {
            if (reqId !== personasRequestSeq) return;
            personasCache = (resp && resp.datos) || [];
            personasCargadas = true;
            renderPersonasOpciones(q);
        }, null, false);
    }

    function resetPersonaDestino() {
        $('#madjPersonaDestino').val('');
        $('#madjPersonaTexto').text('Seleccione una persona').addClass('text-muted');
        cerrarMenuPersonas(true);
        renderPersonasOpciones('');
    }

    function cerrarMenuPersonas(limpiarBusqueda) {
        if (limpiarBusqueda !== false) {
            $('#madjBuscarPersona').val('');
            renderPersonasOpciones('');
        }
        $('#madjPersonaSelect').removeClass('open');
    }

    function renderPersonasOpciones(q, cargando) {
        q = (q || '').toString().trim().toLowerCase();
        var rows = personasCache.filter(function (p) {
            if (!q) return true;
            return [
                p.id,
                p.numero_empleado,
                p.nombre_completo,
                p.puesto
            ].join(' ').toLowerCase().indexOf(q) !== -1;
        });

        if (cargando && !rows.length) {
            $('#madjPersonaOpciones').html('<div class="text-muted small px-2 py-2 madj-inline-loading"><span class="spinner-border text-primary" aria-hidden="true"></span><span>Cargando nombres...</span></div>');
            return;
        }

        if (!rows.length) {
            $('#madjPersonaOpciones').html('<div class="text-muted small px-2 py-2">' + (personasCargadas ? 'Sin resultados' : 'Escribe para buscar una persona') + '</div>');
            return;
        }

        $('#madjPersonaOpciones').html(rows.map(function (p) {
                var extra = p.puesto ? (' - ' + p.puesto) : '';
                var label = (p.nombre_completo || ('Persona #' + p.id)) + extra;
                return '<div class="madj-search-option" data-id-persona="' + esc(p.id) + '" data-label="' + esc(label) + '">' +
                    '<div class="madj-search-option-name">' + esc(p.nombre_completo || ('Persona #' + p.id)) + '</div>' +
                    '<div class="madj-search-option-sub">' + esc((p.numero_empleado ? ('#' + p.numero_empleado + ' · ') : '') + (p.puesto || 'Sin puesto')) + '</div>' +
                '</div>';
        }).join(''));
    }

    function reasignar() {
        if (!actual) return;
        var idPersona = $('#madjPersonaDestino').val();
        if (!idPersona) {
            Swal.fire({ icon: 'warning', title: 'Responsable', text: 'Selecciona una persona para continuar.' });
            return;
        }
        Swal.fire({
            icon: 'question',
            title: 'Actualizar responsable',
            text: 'El crédito quedará a cargo de la persona seleccionada.',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then(function (res) {
            if (!res.isConfirmed) return;
            postJson('/MotosAdjudicadas/reasignarMonitoreoAdjudicacion', {
                id_operacion: actual.id,
                id_persona: idPersona
            }, function (resp) {
                if (!resp || resp.success === false) {
                    Swal.fire({ icon: 'error', title: 'Responsable', text: (resp && resp.message) || 'No se pudo actualizar.' });
                    return;
                }
                Swal.fire({ icon: 'success', title: 'Listo', text: resp.message || 'Responsable actualizado.' });
                $('#modalMadjMonitoreo').modal('hide');
                cargar();
            });
        });
    }

    function guardarEstatus() {
        if (!actual) return;
        postJson('/MotosAdjudicadas/cambiarEstatus', {
            id: actual.id,
            estatus: $('#madjDetalleEstatus').val(),
            origen: 'monitoreo'
        }, function (resp) {
            if (!resp || resp.success === false) {
                Swal.fire({ icon: 'error', title: 'Estatus', text: (resp && resp.message) || 'No se pudo actualizar.' });
                return;
            }
            var inv = resp.inventario_motos_adjudicadas || null;
            var icon = inv && inv.success === false ? 'warning' : 'success';
            var msg = 'Estatus actualizado correctamente.';
            if (inv) {
                if (inv.success === false) {
                    msg += ' ' + (inv.message || 'No se pudo sincronizar con Almacen Virtual.');
                } else if (inv.ya_existe) {
                    msg += ' La unidad ya existia en Almacen Virtual.';
                } else {
                    msg += ' Unidad creada en Almacen Virtual para Evidencias.';
                }
            }
            Swal.fire({ icon: icon, title: inv && inv.success === false ? 'Estatus actualizado' : 'Listo', text: msg });
            cargar();
        });
    }

    function iniciarMonitoreo() {
        if (!window.jQuery || !window.http) {
            setTimeout(iniciarMonitoreo, 50);
            return;
        }

        $(document).on('click', '[data-madj-detalle]', function () { abrirDetalle($(this).data('madj-detalle')); });
        $('#btnMadjMonitoreoActualizar').on('click', cargar);
        $('#madjFiltroEtapa').on('change', cargar);
        $('#madjPageSize').on('change', function () {
            paginaActual = 1;
            renderTabla();
        });
        $(document).on('click', '.madj-page-btn', function () {
            var page = parseInt($(this).data('page'), 10);
            if (!page || $(this).is(':disabled')) return;
            paginaActual = page;
            renderTabla();
        });
        $('#btnMadjMonitoreoLimpiar').on('click', function () {
            $('#madjFiltroEtapa').val('');
            $('#madjBusquedaGlobal').val('');
            cargar();
        });
        $('#madjBusquedaGlobal').on('input', aplicarBusquedaLocal);
        $('#madjPersonaToggle').on('click', function () {
            var $select = $('#madjPersonaSelect');
            $select.toggleClass('open');
            if ($select.hasClass('open')) {
                buscarPersonas();
                setTimeout(function () { $('#madjBuscarPersona').trigger('focus'); }, 30);
            } else {
                cerrarMenuPersonas(true);
            }
        });
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#madjPersonaSelect').length) {
                cerrarMenuPersonas(true);
            }
        });
        $('#madjBuscarPersona').on('input', function () {
            clearTimeout(window._madjPersonaSearchTimer);
            window._madjPersonaSearchTimer = setTimeout(function () {
                renderPersonasOpciones($('#madjBuscarPersona').val());
                buscarPersonas();
            }, 180);
        });
        $('#madjBuscarPersona').on('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });
        $(document).on('click', '.madj-search-option', function () {
            $('#madjPersonaDestino').val($(this).data('id-persona') || '');
            $('#madjPersonaTexto').text($(this).data('label') || 'Persona seleccionada').removeClass('text-muted');
            cerrarMenuPersonas(true);
        });
        $('#btnMadjReasignar').on('click', reasignar);
        $('#btnMadjGuardarEstatus').on('click', guardarEstatus);
        $('#modalMadjMonitoreo').on('hidden.bs.modal', function () {
            actual = null;
            resetPersonaDestino();
            personasRequestSeq++;
        });

        cargar();
        buscarPersonas();
    }

    iniciarMonitoreo();
})();
</script>
