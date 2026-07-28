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
                            <label class="form-label fw-semibold" for="notifUrl">Portal oficial</label>
                            <input class="form-control" id="notifUrl" name="url_descarga" type="url"
                                   value="<?= htmlspecialchars((string)$tipoInicial['url_descarga']) ?>" required>
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
    let campanias = [];
    let campaniaAvance = null;
    let timerBusqueda = null;

    const json = async (url, options = {}) => {
        const response = await fetch(url, {credentials: 'same-origin', cache: 'no-store', ...options});
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.mensaje || 'No se pudo completar la operación.');
        return data;
    };

    const actualizarTexto = () => {
        const anio = Number($('#notifAnio').value);
        const semestre = Number($('#notifSemestre').value);
        const nombre = `Semanas cotizadas ${anio} - ${semestre} semestre`;
        $('#notifTitulo').value = nombre;
        $('#notifMensaje').value = `Capital Humano solicita tu constancia detallada de semanas cotizadas del IMSS correspondiente a ${anio}, ${semestre} semestre. Este documento es obligatorio para mantener actualizado tu expediente laboral. Descárgalo desde el portal oficial del IMSS y súbelo en formato PDF.`;
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
            return `<article class="border rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-bold">${escapeHtml(c.titulo)}</div>
                        <small class="text-muted">${escapeHtml(c.anio)} · ${escapeHtml(c.semestre)} semestre</small>
                    </div>
                    <span class="badge ${activa ? 'text-bg-success' : 'text-bg-secondary'} align-self-start">${activa ? 'Activa' : 'Pausada'}</span>
                </div>
                <div class="d-flex justify-content-between small mt-3 mb-1">
                    <span>${entregados} de ${total} entregados</span><strong>${porcentaje}%</strong>
                </div>
                <div class="progress" style="height:8px"><div class="progress-bar" style="width:${porcentaje}%"></div></div>
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

    const cargarAvance = async () => {
        if (!campaniaAvance) return;
        const cuerpo = $('#avanceCuerpo');
        cuerpo.innerHTML = '<tr><td colspan="4" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Cargando...</td></tr>';
        try {
            const params = new URLSearchParams({
                id: campaniaAvance.id,
                estado: $('#avanceEstado').value,
                buscar: $('#avanceBuscar').value.trim()
            });
            const rows = (await json('/caphum/getPersonasCampaniaNotificacionDocumental?' + params)).datos || [];
            $('#avanceResumen').textContent = `${rows.length} resultado(s)`;
            cuerpo.innerHTML = rows.length ? rows.map(row => {
                const entregado = row.estado_entrega === 'Entregado';
                return `<tr>
                    <td><strong>${escapeHtml(row.nombre)}</strong><br><small class="text-muted">${escapeHtml(row.numero_empleado || 'Sin número')}</small></td>
                    <td>${escapeHtml(row.user_name || '')}</td>
                    <td><span class="badge ${entregado ? 'text-bg-success' : 'text-bg-warning'}">${escapeHtml(row.estado_entrega)}</span></td>
                    <td>${entregado ? `${escapeHtml(row.nombre_logico || '')}<br><small class="text-muted">${escapeHtml(row.cargado_en || '')}</small>` : '—'}</td>
                </tr>`;
            }).join('') : '<tr><td colspan="4" class="text-center text-muted py-4">No hay resultados.</td></tr>';
        } catch (error) {
            cuerpo.innerHTML = `<tr><td colspan="4"><div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div></td></tr>`;
        }
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = $('#btnGuardarCampania');
        button.disabled = true;
        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            payload.activa = $('#notifActiva').checked ? 1 : 0;
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
    $('#btnRecargarCampanias').addEventListener('click', cargarCampanias);
    $('#avanceEstado').addEventListener('change', cargarAvance);
    $('#avanceBuscar').addEventListener('input', () => {
        clearTimeout(timerBusqueda);
        timerBusqueda = setTimeout(cargarAvance, 350);
    });
    actualizarTexto();
    cargarCampanias();
})();
</script>
