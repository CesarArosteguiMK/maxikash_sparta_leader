<?php
$tipos = is_array($tiposNotificacionDocumental ?? null) ? $tiposNotificacionDocumental : [];
$anioActual = (int) date('Y');
$semestreActual = (int) date('n') <= 6 ? 1 : 2;
$tipoInicial = $tipos[0] ?? [
    'clave' => 'semanas_cotizadas',
    'nombre' => 'Semanas cotizadas IMSS (segundos patrones)',
    'url_descarga' => 'https://serviciosdigitales.imss.gob.mx/semanascotizadas-web/usuarios/IngresoAsegurado',
];
?>
<div class="container-fluid py-3" id="rrhhNotificaciones">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="badge text-bg-primary mb-2">Capital Humano</span>
                <h2 class="h4 mb-1"><i class="fa-solid fa-bell me-2 text-primary"></i>Notificación documental</h2>
                <p class="text-muted mb-0">Solicita documentos obligatorios y consulta quién ya realizó la entrega.</p>
            </div>
            <div class="alert alert-info mb-0 py-2">
                <i class="fa-solid fa-circle-info me-1"></i>
                Aplica a colaboradores activos de México con acceso a Sparta.
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h3 class="h6 mb-1">Crear o actualizar solicitud</h3>
                    <small class="text-muted">Cada periodo admite una sola entrega por colaborador.</small>
                </div>
                <div class="card-body px-4">
                    <form id="formCampania">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="notifTipo">Notificación</label>
                            <select class="form-select" id="notifTipo" name="tipo" required>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?= htmlspecialchars((string)$tipo['clave']) ?>"
                                            data-url="<?= htmlspecialchars((string)$tipo['url_descarga']) ?>">
                                        <?= htmlspecialchars((string)$tipo['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="notifAlcance">Destinatarios</label>
                            <select class="form-select" id="notifAlcance" name="alcance">
                                <option value="todos">Todos los colaboradores aplicables</option>
                                <option value="seleccionados">Solo usuarios seleccionados</option>
                            </select>
                        </div>
                        <div class="border rounded-3 p-3 mb-3 d-none" id="selectorDestinatarios">
                            <label class="form-label fw-semibold" for="buscarDestinatario">Seleccionar colaboradores</label>
                            <input class="form-control" id="buscarDestinatario"
                                   placeholder="Buscar por nombre, usuario, correo o número de empleado">
                            <div class="form-text mb-2">Puedes seleccionar 1, 5, 20 o cualquier cantidad necesaria.</div>
                            <div class="list-group mb-2" id="resultadosDestinatarios" style="max-height:220px;overflow:auto"></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong><span id="totalDestinatarios">0</span> seleccionados</strong>
                                <button class="btn btn-link btn-sm text-danger p-0" id="limpiarDestinatarios" type="button">Limpiar selección</button>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-2" id="destinatariosSeleccionados"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold" for="notifAnio">Año</label>
                                <input class="form-control" id="notifAnio" name="anio" type="number"
                                       min="2020" max="2100" value="<?= $anioActual ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" for="notifSemestre">Semestre</label>
                                <select class="form-select" id="notifSemestre" name="semestre" required>
                                    <option value="1" <?= $semestreActual === 1 ? 'selected' : '' ?>>1.er semestre</option>
                                    <option value="2" <?= $semestreActual === 2 ? 'selected' : '' ?>>2.º semestre</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="notifTitulo">Título</label>
                            <input class="form-control" id="notifTitulo" name="titulo" maxlength="180">
                            <div class="form-text">También será el nombre visible del documento.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="notifMensaje">Explicación para el colaborador</label>
                            <textarea class="form-control" id="notifMensaje" name="mensaje" rows="4" maxlength="5000"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="notifUrl">Enlace de descarga o consulta</label>
                            <input class="form-control" id="notifUrl" name="url_descarga" type="text"
                                   value="<?= htmlspecialchars((string)$tipoInicial['url_descarga']) ?>">
                            <div class="form-text">Se deja vacío cuando el documento lo proporciona el colaborador.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="notifLimite">Fecha límite <span class="text-muted fw-normal">(opcional)</span></label>
                            <input class="form-control" id="notifLimite" name="fecha_limite" type="date">
                            <div class="form-text">Aunque venza, la solicitud seguirá bloqueando hasta que se entregue el PDF.</div>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" id="notifActiva" name="activa" type="checkbox" checked>
                            <label class="form-check-label fw-semibold" for="notifActiva">Activar al guardar</label>
                        </div>
                        <button class="btn btn-primary w-100" id="btnGuardarCampania" type="submit">
                            <i class="fa-solid fa-bell me-2"></i>Guardar y activar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 mb-1">Periodos solicitados</h3>
                        <small class="text-muted">Avance de entregas por semestre.</small>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" id="btnRecargarCampanias" type="button">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
                <div class="card-body px-4" id="listaCampanias">
                    <div class="text-center text-muted py-5">
                        <span class="spinner-border spinner-border-sm me-2"></span>Cargando periodos...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAvanceCampania" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="avanceTitulo">Avance de entregas</h5>
                    <small class="text-muted" id="avanceResumen"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-sm-4">
                        <select class="form-select" id="avanceEstado">
                            <option value="todos">Todos</option>
                            <option value="pendientes">Pendientes</option>
                            <option value="entregados">Entregados</option>
                        </select>
                    </div>
                    <div class="col-sm-8">
                        <input class="form-control" id="avanceBuscar" placeholder="Buscar por nombre, usuario o número de empleado">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Entrega</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="avanceCuerpo"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';
    const $ = (selector) => document.querySelector(selector);
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);
    const form = $('#formCampania');
    const lista = $('#listaCampanias');
    const catalogo = <?= json_encode($tipos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let campanias = [];
    let campaniaAvance = null;
    let timerBusqueda = null;
    let timerDestinatarios = null;
    let timerActualizacionAvance = null;
    let consultaAvanceEnCurso = false;
    const destinatarios = new Map();

    const json = async (url, options = {}) => {
        const response = await fetch(url, {credentials: 'same-origin', cache: 'no-store', ...options});
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.mensaje || 'No se pudo completar la operación.');
        return data;
    };

    const actualizarTexto = () => {
        const anio = Number($('#notifAnio').value);
        const semestre = Number($('#notifSemestre').value);
        const tipo = catalogo.find(item => item.clave === $('#notifTipo').value) || catalogo[0] || {};
        const nombre = `${tipo.nombre || 'Documento'} ${anio} - ${semestre} semestre`;
        $('#notifTitulo').value = nombre;
        $('#notifMensaje').value = `Capital Humano solicita el documento «${tipo.nombre || 'Documento'}» para ${anio}, ${semestre} semestre. ${tipo.mensaje_predeterminado || 'Carga el documento solicitado en formato PDF.'} Esta entrega es obligatoria para mantener actualizado tu expediente laboral.`;
        $('#notifUrl').value = tipo.url_descarga || '';
    };

    const renderSeleccionados = () => {
        $('#totalDestinatarios').textContent = destinatarios.size;
        const personas = Array.from(destinatarios.values());
        $('#destinatariosSeleccionados').innerHTML = personas.slice(0, 30).map(persona =>
            `<span class="badge text-bg-primary d-inline-flex align-items-center gap-1">
                ${escapeHtml(persona.nombre)}
                <button class="btn-close btn-close-white" type="button" style="font-size:.5rem"
                        data-quitar-destinatario="${Number(persona.id_persona)}" aria-label="Quitar"></button>
            </span>`
        ).join('') + (personas.length > 30 ? `<span class="badge text-bg-secondary">+${personas.length - 30} más</span>` : '');
    };

    const cargarDestinatarios = async () => {
        const contenedor = $('#resultadosDestinatarios');
        contenedor.innerHTML = '<div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span>Buscando...</div>';
        try {
            const params = new URLSearchParams({buscar: $('#buscarDestinatario').value.trim(), limite: 50});
            const rows = (await json('/caphum/buscarPersonasNotificacionDocumental?' + params)).datos || [];
            contenedor.innerHTML = rows.length ? rows.map(persona => {
                const id = Number(persona.id_persona);
                return `<label class="list-group-item list-group-item-action d-flex gap-2 align-items-start">
                    <input class="form-check-input mt-1" type="checkbox" data-destinatario="${id}" ${destinatarios.has(id) ? 'checked' : ''}>
                    <span><strong>${escapeHtml(persona.nombre)}</strong>
                    <small class="d-block text-muted">${escapeHtml(persona.user_name || '')}${persona.numero_empleado ? ' · ' + escapeHtml(persona.numero_empleado) : ''}</small></span>
                </label>`;
            }).join('') : '<div class="text-center text-muted py-3">No se encontraron colaboradores.</div>';
            rows.forEach(persona => {
                const check = contenedor.querySelector(`[data-destinatario="${Number(persona.id_persona)}"]`);
                if (check) check._persona = persona;
            });
        } catch (error) {
            contenedor.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div>`;
        }
    };

    const cambiarAlcance = () => {
        const seleccionados = $('#notifAlcance').value === 'seleccionados';
        $('#selectorDestinatarios').classList.toggle('d-none', !seleccionados);
        if (seleccionados && !$('#resultadosDestinatarios').children.length) cargarDestinatarios();
    };

    const renderCampanias = () => {
        if (!campanias.length) {
            lista.innerHTML = '<div class="text-center text-muted py-5"><i class="fa-regular fa-bell-slash fa-2x mb-3 d-block"></i>Aún no hay periodos configurados.</div>';
            return;
        }
        lista.innerHTML = campanias.map(c => {
            const total = Number(c.total_personas || 0);
            const entregados = Number(c.entregados || 0);
            const porcentaje = total ? Math.round(entregados * 100 / total) : 0;
            const activa = Number(c.activa) === 1;
            const avancePorEmpresa = (Array.isArray(c.por_empresa) ? c.por_empresa : []).map(empresa => {
                const totalEmpresa = Number(empresa.total_personas || 0);
                const entregadosEmpresa = Number(empresa.entregados || 0);
                const pendientesEmpresa = Number(empresa.pendientes || 0);
                const porcentajeEmpresa = totalEmpresa ? Math.round(entregadosEmpresa * 100 / totalEmpresa) : 0;
                return `<div class="border rounded-2 p-2 flex-grow-1" style="min-width:210px">
                    <div class="d-flex justify-content-between align-items-center gap-2 small">
                        <strong>${escapeHtml(empresa.nombre || 'Empresa')}</strong>
                        <span class="text-nowrap">${entregadosEmpresa} / ${totalEmpresa}</span>
                    </div>
                    <div class="progress my-2" style="height:6px"><div class="progress-bar" style="width:${porcentajeEmpresa}%"></div></div>
                    <small class="text-muted">${pendientesEmpresa} pendientes · ${porcentajeEmpresa}%</small>
                </div>`;
            }).join('');
            return `<article class="border rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-bold">${escapeHtml(c.titulo)}</div>
                        <small class="text-muted">${escapeHtml(c.anio)} · ${escapeHtml(c.semestre)} semestre · ${c.alcance === 'seleccionados' ? 'Selección específica' : 'Todos'}</small>
                    </div>
                    <span class="badge ${activa ? 'text-bg-success' : 'text-bg-secondary'} align-self-start">${activa ? 'Activa' : 'Pausada'}</span>
                </div>
                <div class="d-flex justify-content-between small mt-3 mb-1">
                    <span>${entregados} de ${total} entregados</span><strong>${porcentaje}%</strong>
                </div>
                <div class="progress" style="height:8px"><div class="progress-bar" style="width:${porcentaje}%"></div></div>
                ${avancePorEmpresa ? `<div class="mt-3"><small class="d-block text-muted fw-semibold mb-2">Avance por empresa</small><div class="d-flex flex-wrap gap-2">${avancePorEmpresa}</div></div>` : ''}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <small class="text-danger fw-semibold">${Number(c.pendientes || 0)} pendientes</small>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" data-avance="${Number(c.id)}"><i class="fa-solid fa-users-viewfinder me-1"></i>Ver avance</button>
                        <button class="btn btn-outline-${activa ? 'warning' : 'success'} btn-sm" data-estado="${Number(c.id)}" data-activa="${activa ? 0 : 1}">
                            <i class="fa-solid fa-${activa ? 'pause' : 'play'} me-1"></i>${activa ? 'Pausar' : 'Activar'}
                        </button>
                    </div>
                </div>
            </article>`;
        }).join('');
    };

    const cargarCampanias = async () => {
        lista.innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span>Cargando periodos...</div>';
        try {
            campanias = (await json('/caphum/getCampaniasNotificacionDocumental')).datos || [];
            renderCampanias();
        } catch (error) {
            lista.innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message)}</div>`;
        }
    };

    const insigniaPatrones = (row) => {
        if (!campaniaAvance || campaniaAvance.tipo !== 'semanas_cotizadas' || row.estado_entrega !== 'Entregado') {
            return '';
        }
        const estado = String(row.estado_analisis_patrones || 'pendiente');
        if (estado === 'pendiente') {
            if (row.archivo_disponible_nodo === false) {
                return '<span class="badge rounded-pill text-bg-light border text-secondary mt-1" title="Este entorno comparte la base de datos, pero no tiene el PDF físico."><i class="fa-solid fa-server me-1"></i>Pendiente: PDF solo en servidor</span>';
            }
            if (row.worker_lanzado_nodo === false) {
                return '<span class="badge rounded-pill text-bg-danger mt-1" title="El servidor no pudo iniciar el proceso interno."><i class="fa-solid fa-triangle-exclamation me-1"></i>Motor V1 no inició</span>';
            }
            return '<span class="badge rounded-pill text-bg-light border text-warning mt-1"><i class="fa-solid fa-spinner fa-spin me-1"></i>En cola del Motor V1</span>';
        }
        if (estado === 'sin_lectura') {
            const motivo = row.mensaje_analisis_patrones
                || 'El Motor V1 no pudo determinar el contenido del PDF.';
            return `<span class="badge rounded-pill text-bg-secondary mt-1" title="${escapeHtml(motivo)}">
                <i class="fa-solid fa-eye-low-vision me-1"></i>Revisión manual
            </span><small class="d-block text-muted mt-1">${escapeHtml(motivo)}</small>`;
        }
        if (estado === 'documento_incorrecto') {
            const motivo = row.mensaje_analisis_patrones
                || 'El archivo no corresponde al documento solicitado.';
            return `<span class="badge rounded-pill text-bg-danger mt-1" title="${escapeHtml(motivo)}">
                <i class="fa-solid fa-file-circle-xmark me-1"></i>Documento incorrecto
            </span><small class="d-block text-danger mt-1">${escapeHtml(motivo)}</small>`;
        }
        const cantidad = Number(row.patrones_vigentes || 0);
        const clase = cantidad >= 2 ? 'text-bg-danger' : (cantidad === 1 ? 'text-bg-primary' : 'text-bg-warning');
        const historial = Number(row.patrones_historial || 0);
        const titulo = historial
            ? `${historial} registro${historial === 1 ? '' : 's'} patronal${historial === 1 ? '' : 'es'} en el historial`
            : 'Lectura del Motor V1';
        return `<span class="badge rounded-pill ${clase} mt-1" title="${escapeHtml(titulo)}">
            <i class="fa-solid fa-building-user me-1"></i>${cantidad} ${cantidad === 1 ? 'patrón vigente' : 'patrones vigentes'}
        </span>`;
    };

    const detenerActualizacionAvance = () => {
        if (timerActualizacionAvance) {
            clearTimeout(timerActualizacionAvance);
            timerActualizacionAvance = null;
        }
    };

    const cargarAvance = async ({silencioso = false} = {}) => {
        if (!campaniaAvance) return;
        detenerActualizacionAvance();
        if (consultaAvanceEnCurso) return;
        consultaAvanceEnCurso = true;
        const cuerpo = $('#avanceCuerpo');
        const modal = $('#modalAvanceCampania');
        const cuerpoModal = modal.querySelector('.modal-body');
        const posicionScroll = silencioso && cuerpoModal ? cuerpoModal.scrollTop : null;
        if (!silencioso) {
            cuerpo.innerHTML = '<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Cargando...</td></tr>';
        }
        try {
            const params = new URLSearchParams({
                id: campaniaAvance.id,
                estado: $('#avanceEstado').value,
                buscar: $('#avanceBuscar').value.trim(),
                solo_estado: silencioso ? '1' : '0'
            });
            const rows = (await json('/caphum/getPersonasCampaniaNotificacionDocumental?' + params)).datos || [];
            $('#avanceResumen').textContent = `${rows.length} resultado(s)`;
            cuerpo.innerHTML = rows.length ? rows.map(row => {
                const entregado = row.estado_entrega === 'Entregado';
                const badgePatrones = insigniaPatrones(row);
                return `<tr>
                    <td><strong>${escapeHtml(row.nombre)}</strong><br><small class="text-muted">${escapeHtml(row.numero_empleado || 'Sin número')}</small></td>
                    <td>${escapeHtml(row.user_name || '')}</td>
                    <td><span class="badge ${entregado ? 'text-bg-success' : 'text-bg-warning'}">${escapeHtml(row.estado_entrega)}</span></td>
                    <td>${entregado ? `${escapeHtml(row.nombre_logico || '')}<br><small class="text-muted">${escapeHtml(row.cargado_en || '')}</small>${badgePatrones ? `<br>${badgePatrones}` : ''}` : '—'}</td>
                    <td class="text-end">${entregado ? `
                        <div class="btn-group btn-group-sm" role="group" aria-label="Acciones del documento">
                            <button class="btn btn-outline-primary" type="button" title="Ver PDF" aria-label="Ver PDF"
                                    data-ver-entrega="${Number(row.id_documento_carga || 0)}" data-nombre-entrega="${escapeHtml(row.nombre_logico || 'Documento')}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            ${row.estado_analisis_patrones === 'sin_lectura' ? `
                            <button class="btn btn-outline-warning" type="button" title="Reintentar lectura" aria-label="Reintentar lectura"
                                    data-reintentar-lectura="${Number(row.id_documento_carga || 0)}" data-nombre-entrega="${escapeHtml(row.nombre_logico || 'Documento')}">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>` : ''}
                            <button class="btn btn-outline-danger" type="button" title="Eliminar y solicitar de nuevo" aria-label="Eliminar y solicitar de nuevo"
                                    data-eliminar-entrega="${Number(row.id_documento_carga || 0)}" data-nombre-entrega="${escapeHtml(row.nombre_logico || 'Documento')}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>` : '-'}</td>
                </tr>`;
            }).join('') : '<tr><td colspan="5" class="text-center text-muted py-4">No hay resultados.</td></tr>';
            if (posicionScroll !== null && cuerpoModal) {
                cuerpoModal.scrollTop = posicionScroll;
            }

            const hayLecturasPendientes = rows.some(row =>
                row.estado_entrega === 'Entregado'
                && row.estado_analisis_patrones === 'pendiente'
                && row.archivo_disponible_nodo !== false
                && row.worker_lanzado_nodo !== false
            );
            if (hayLecturasPendientes && modal.classList.contains('show')) {
                timerActualizacionAvance = setTimeout(
                    () => cargarAvance({silencioso: true}),
                    4000
                );
            }
        } catch (error) {
            if (!silencioso) {
                cuerpo.innerHTML = `<tr><td colspan="5"><div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div></td></tr>`;
            }
        } finally {
            consultaAvanceEnCurso = false;
        }
    };

    const cargarLibreriaQr = () => {
        if (window.QRCode && typeof window.QRCode.toCanvas === 'function') return Promise.resolve();
        return new Promise((resolve, reject) => {
            const existente = document.querySelector('script[data-rrhh-qrcode="1"]');
            if (existente) {
                existente.addEventListener('load', resolve, {once: true});
                existente.addEventListener('error', () => reject(new Error('No se pudo cargar el QR.')), {once: true});
                return;
            }
            const script = document.createElement('script');
            script.src = '/assets/vendor/libs/qrcode/qrcode.js';
            script.dataset.rrhhQrcode = '1';
            script.onload = resolve;
            script.onerror = () => reject(new Error('No se pudo cargar el QR.'));
            document.head.appendChild(script);
        });
    };

    const renderQr = async (otpauthUrl) => {
        if (!otpauthUrl) return;
        try {
            await cargarLibreriaQr();
            const canvas = document.getElementById('rrhh-notif-totp-qr');
            if (canvas && window.QRCode && typeof window.QRCode.toCanvas === 'function') {
                await window.QRCode.toCanvas(canvas, otpauthUrl, {width: 190, margin: 2, errorCorrectionLevel: 'M'});
            }
        } catch (_error) {
            const aviso = document.getElementById('rrhh-notif-totp-qr-error');
            if (aviso) aviso.classList.remove('d-none');
        }
    };

    const pedirAutorizacionDocumento = async (idDocumentoCarga, accion) => {
        const solicitar = async (codigo = '') => {
            const formData = new FormData();
            formData.append('id', String(idDocumentoCarga));
            formData.append('accion', accion);
            if (codigo) formData.append('totp_code', codigo);
            return json('/caphum/crearTokenDocumentoPersonaSensible', {
                method: 'POST',
                body: formData,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
        };

        let respuesta = await solicitar();
        if (!respuesta.datos?.requiere_totp) return respuesta.datos || {};
        if (!window.Swal) throw new Error('Google Authenticator es requerido para este documento.');

        const datosTotp = respuesta.datos || {};
        const esSetup = !!datosTotp.setup;
        const htmlTotp = esSetup
            ? `<div class="text-start"><p class="mb-2">Escanea este QR desde Google Authenticator.</p>
                <div class="text-center border rounded p-2 mb-2"><canvas id="rrhh-notif-totp-qr" width="190" height="190"></canvas>
                <div id="rrhh-notif-totp-qr-error" class="small text-danger d-none">No se pudo mostrar el QR.</div></div>
                <p class="small text-muted mb-0">Despues captura el codigo de 6 digitos generado por la aplicacion.</p></div>`
            : '<p class="mb-0">Escribe el codigo de 6 digitos de Google Authenticator para continuar.</p>';
        const confirmacion = await Swal.fire({
            title: esSetup ? 'Configurar segundo paso' : 'Segundo paso requerido',
            html: htmlTotp,
            input: 'text',
            inputPlaceholder: 'Codigo de 6 digitos',
            inputAttributes: {maxlength: 6, inputmode: 'numeric', autocomplete: 'one-time-code'},
            showCancelButton: true,
            confirmButtonText: esSetup ? 'Activar y continuar' : 'Confirmar',
            cancelButtonText: 'Cancelar',
            didOpen: () => renderQr(String(datosTotp.otpauth_url || '')),
            preConfirm: value => {
                const codigo = String(value || '').replace(/\D+/g, '');
                if (codigo.length !== 6) {
                    Swal.showValidationMessage('Captura los 6 digitos.');
                    return false;
                }
                return codigo;
            }
        });
        if (!confirmacion.isConfirmed) return null;
        respuesta = await solicitar(confirmacion.value);
        return respuesta.datos || {};
    };

    const abrirDocumento = (url, nombre) => {
        let modal = document.getElementById('modalVisorEntregaDocumental');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'modalVisorEntregaDocumental';
            modal.tabIndex = -1;
            modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML = `
                <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:92vw">
                    <div class="modal-content border-0 shadow-lg" style="height:min(88vh,900px)">
                        <div class="modal-header py-2">
                            <h5 class="modal-title text-truncate"><i class="fa-solid fa-file-pdf text-danger me-2"></i><span></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body p-0"><iframe title="Vista previa del documento" style="display:block;width:100%;height:100%;border:0;background:#fff"></iframe></div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
            modal.addEventListener('hidden.bs.modal', () => {
                const frame = modal.querySelector('iframe');
                if (frame) frame.src = 'about:blank';
            });
        }
        if (!window.bootstrap?.Modal) {
            window.open(url, '_blank', 'noopener,noreferrer');
            return;
        }
        modal.querySelector('.modal-title span').textContent = nombre;
        modal.querySelector('iframe').src = url;
        bootstrap.Modal.getOrCreateInstance(modal, {backdrop: false}).show();
    };

    $('#avanceCuerpo').addEventListener('click', async event => {
        const botonVer = event.target.closest('[data-ver-entrega]');
        const botonReintentar = event.target.closest('[data-reintentar-lectura]');
        const botonEliminar = event.target.closest('[data-eliminar-entrega]');
        const boton = botonVer || botonReintentar || botonEliminar;
        if (!boton) return;

        const idDocumento = Number(
            boton.dataset.verEntrega
            || boton.dataset.reintentarLectura
            || boton.dataset.eliminarEntrega
            || 0
        );
        if (!idDocumento) return;
        const nombre = boton.dataset.nombreEntrega || 'Documento';

        try {
            if (botonVer) {
                boton.disabled = true;
                const autorizado = await pedirAutorizacionDocumento(idDocumento, 'ver');
                if (autorizado?.url) abrirDocumento(autorizado.url, nombre);
                return;
            }

            if (botonReintentar) {
                const confirmarReintento = window.Swal
                    ? await Swal.fire({
                        icon: 'question',
                        title: 'Reintentar lectura',
                        text: `El Motor V1 volverá a analizar "${nombre}" sin eliminar el PDF.`,
                        showCancelButton: true,
                        confirmButtonText: 'Sí, reintentar',
                        cancelButtonText: 'Cancelar'
                    })
                    : {isConfirmed: window.confirm(`¿Reintentar la lectura de ${nombre}?`)};
                if (!confirmarReintento.isConfirmed) return;

                boton.disabled = true;
                const formData = new FormData();
                formData.append('id_documento', String(idDocumento));
                const resultado = await json('/caphum/reintentarAnalisisPatronesNotificacionDocumental', {
                    method: 'POST',
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                if (window.Swal) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Reintento iniciado',
                        text: resultado.mensaje,
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
                await cargarAvance();
                return;
            }

            const confirmar = window.Swal
                ? await Swal.fire({
                    icon: 'warning',
                    title: 'Eliminar documento',
                    text: `Se eliminara "${nombre}" y el colaborador volvera a tener la solicitud pendiente.`,
                    showCancelButton: true,
                    confirmButtonText: 'Si, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545'
                })
                : {isConfirmed: window.confirm(`Eliminar ${nombre}?`)};
            if (!confirmar.isConfirmed) return;

            boton.disabled = true;
            const autorizado = await pedirAutorizacionDocumento(idDocumento, 'eliminar');
            if (!autorizado?.token) return;
            const formData = new FormData();
            formData.append('id_documento', String(idDocumento));
            formData.append('token', autorizado.token);
            const resultado = await json('/caphum/eliminarDocumentoPersona', {
                method: 'POST',
                body: formData,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            if (window.Swal) {
                await Swal.fire({icon: 'success', title: 'Documento eliminado', text: resultado.mensaje});
            }
            await Promise.all([cargarAvance(), cargarCampanias()]);
        } catch (error) {
            if (window.Swal) Swal.fire({icon: 'error', title: 'No se pudo completar', text: error.message});
            else alert(error.message);
        } finally {
            boton.disabled = false;
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = $('#btnGuardarCampania');
        button.disabled = true;
        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            payload.activa = $('#notifActiva').checked ? 1 : 0;
            payload.persona_ids = Array.from(destinatarios.keys());
            if (payload.alcance === 'seleccionados' && !payload.persona_ids.length) {
                throw new Error('Selecciona al menos un colaborador.');
            }
            const result = await json('/caphum/guardarCampaniaNotificacionDocumental', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            if (window.Swal) await Swal.fire({icon: 'success', title: 'Guardado', text: result.mensaje});
            else alert(result.mensaje);
            await cargarCampanias();
        } catch (error) {
            if (window.Swal) Swal.fire({icon: 'error', title: 'No se pudo guardar', text: error.message});
            else alert(error.message);
        } finally {
            button.disabled = false;
        }
    });

    lista.addEventListener('click', async event => {
        const avance = event.target.closest('[data-avance]');
        if (avance) {
            campaniaAvance = campanias.find(c => Number(c.id) === Number(avance.dataset.avance));
            if (!campaniaAvance) return;
            $('#avanceTitulo').textContent = campaniaAvance.titulo;
            $('#avanceEstado').value = 'todos';
            $('#avanceBuscar').value = '';
            bootstrap.Modal.getOrCreateInstance($('#modalAvanceCampania')).show();
            cargarAvance();
            return;
        }
        const estado = event.target.closest('[data-estado]');
        if (!estado) return;
        try {
            await json('/caphum/cambiarEstadoCampaniaNotificacionDocumental', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: Number(estado.dataset.estado), activa: Number(estado.dataset.activa)})
            });
            cargarCampanias();
        } catch (error) {
            if (window.Swal) Swal.fire({icon: 'error', title: 'No se pudo cambiar', text: error.message});
            else alert(error.message);
        }
    });

    $('#notifAnio').addEventListener('change', actualizarTexto);
    $('#notifSemestre').addEventListener('change', actualizarTexto);
    $('#notifTipo').addEventListener('change', actualizarTexto);
    $('#notifAlcance').addEventListener('change', cambiarAlcance);
    $('#buscarDestinatario').addEventListener('input', () => {
        clearTimeout(timerDestinatarios);
        timerDestinatarios = setTimeout(cargarDestinatarios, 300);
    });
    $('#resultadosDestinatarios').addEventListener('change', event => {
        const check = event.target.closest('[data-destinatario]');
        if (!check) return;
        const id = Number(check.dataset.destinatario);
        if (check.checked && check._persona) destinatarios.set(id, check._persona);
        else destinatarios.delete(id);
        renderSeleccionados();
    });
    $('#destinatariosSeleccionados').addEventListener('click', event => {
        const button = event.target.closest('[data-quitar-destinatario]');
        if (!button) return;
        const id = Number(button.dataset.quitarDestinatario);
        destinatarios.delete(id);
        const check = $('#resultadosDestinatarios').querySelector(`[data-destinatario="${id}"]`);
        if (check) check.checked = false;
        renderSeleccionados();
    });
    $('#limpiarDestinatarios').addEventListener('click', () => {
        destinatarios.clear();
        $('#resultadosDestinatarios').querySelectorAll('[data-destinatario]').forEach(check => { check.checked = false; });
        renderSeleccionados();
    });
    $('#btnRecargarCampanias').addEventListener('click', cargarCampanias);
    $('#avanceEstado').addEventListener('change', cargarAvance);
    $('#avanceBuscar').addEventListener('input', () => {
        clearTimeout(timerBusqueda);
        timerBusqueda = setTimeout(cargarAvance, 350);
    });
    $('#modalAvanceCampania').addEventListener('hidden.bs.modal', detenerActualizacionAvance);
    actualizarTexto();
    cambiarAlcance();
    cargarCampanias();
})();
</script>
