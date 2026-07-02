<style>
.cma-hero {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .65rem;
    padding: 1rem 1.15rem;
}
.cma-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff7ed;
    color: #c2410c;
    flex-shrink: 0;
}
.cma-rule-card {
    border: 1px solid #e2e8f0;
    border-radius: .65rem;
    box-shadow: 0 .1rem .55rem rgba(15,23,42,.05);
}
.cma-rule-label {
    display: block;
    color: #64748b;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
}
.cma-fad-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .28rem .65rem;
    font-size: .74rem;
    font-weight: 800;
}
.cma-fad-pill--on { background: #dcfce7; color: #166534; }
.cma-fad-pill--off { background: #fee2e2; color: #991b1b; }
.cma-fad-kpi {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: .7rem .85rem;
    background: #f8fafc;
}
.cma-fad-table { margin: 0; font-size: .82rem; }
.cma-fad-table th {
    color: #64748b;
    font-size: .7rem;
    font-weight: 800;
    text-transform: uppercase;
    white-space: nowrap;
}
.cma-fad-table td { vertical-align: middle; }
.cma-fad-empty {
    border: 1px dashed #cbd5e1;
    border-radius: .65rem;
    padding: 1rem;
    text-align: center;
    color: #64748b;
}
.cma-fad-toggle {
    border: 1px solid #dbe4ef;
    border-radius: .75rem;
    background: #fff;
    padding: .6rem .7rem;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    color: #334155;
    text-align: left;
}
.cma-fad-toggle-track {
    width: 3.25rem;
    height: 1.65rem;
    border-radius: 999px;
    background: #cbd5e1;
    padding: .18rem;
    flex-shrink: 0;
    transition: background .18s ease;
}
.cma-fad-toggle-dot {
    width: 1.28rem;
    height: 1.28rem;
    border-radius: 999px;
    background: #fff;
    box-shadow: 0 2px 5px rgba(15, 23, 42, .25);
    transition: transform .18s ease;
}
.cma-fad-toggle.is-on .cma-fad-toggle-track { background: #22c55e; }
.cma-fad-toggle.is-on .cma-fad-toggle-dot { transform: translateX(1.58rem); }
.cma-fad-toggle-title {
    display: block;
    font-size: .9rem;
    font-weight: 800;
}
.cma-fad-toggle-sub {
    display: block;
    color: #64748b;
    font-size: .74rem;
    line-height: 1.2;
}
.cma-fad-section-title {
    color: #1e293b;
    font-size: .8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.cma-fad-inner-card {
    border: 1px solid #dbe4ef;
    border-radius: .65rem;
    background: #f8fafc;
    padding: .9rem;
}
.cma-fad-inner-title {
    color: #1e293b;
    font-size: .9rem;
    font-weight: 800;
}
.cma-fad-inner-sub {
    color: #64748b;
    font-size: .76rem;
}
body.dark-mode .cma-hero,
body.dark-mode .cma-rule-card,
body.dark-mode .cma-fad-kpi,
body.dark-mode .cma-fad-inner-card,
body.dark-mode .cma-fad-toggle {
    background: #17212b;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .cma-fad-section-title,
body.dark-mode .cma-fad-inner-title { color: #e2e8f0; }
body.dark-mode .cma-rule-label,
body.dark-mode .cma-fad-toggle-sub,
body.dark-mode .cma-fad-inner-sub { color: #94a3b8; }
</style>

<div class="container-fluid py-3" id="configMotosAdjFadApp">
    <div class="cma-hero mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="cma-icon"><i class="fa-solid fa-file-signature"></i></span>
                <div>
                    <h5 class="mb-1">FAD para motos adjudicadas</h5>
                    <p class="text-muted mb-0 small">Controla si el gestor debe atender FAD en la app y a que casos les aplica.</p>
                </div>
            </div>
            <a class="btn btn-label-secondary" href="/ConfigMotosAdj/consulta">
                <i class="fa-solid fa-arrow-left me-1"></i>Parametros Motos
            </a>
        </div>
    </div>

    <div class="card cma-rule-card" id="cmaFadCard">
        <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <div>
                <h6 class="mb-1">
                    <i class="fa-solid fa-file-signature me-2 text-warning"></i>
                    Administracion FAD
                </h6>
                <div class="small text-muted">Encendido global, excepciones, historial, decisiones y pendientes.</div>
            </div>
            <span class="cma-fad-pill cma-fad-pill--off" id="cmaFadEstado">
                <i class="fa-solid fa-circle-xmark"></i>Sin datos
            </span>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3 gap-2" id="cmaFadTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="cma-fad-tab-operacion-btn" type="button"
                            data-bs-toggle="tab" data-bs-target="#cma-fad-tab-operacion" role="tab">
                        <i class="fa-solid fa-sliders me-1"></i>Operacion
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cma-fad-tab-pendientes-btn" type="button"
                            data-bs-toggle="tab" data-bs-target="#cma-fad-tab-pendientes" role="tab">
                        <i class="fa-solid fa-bell me-1"></i>Pendientes FAD
                    </button>
                </li>
            </ul>

            <div class="tab-content p-0" id="cmaFadTabContent">
                <div class="tab-pane fade show active" id="cma-fad-tab-operacion" role="tabpanel">
                    <div class="row g-3 align-items-stretch mb-3">
                        <div class="col-12 col-lg-4">
                            <div class="cma-fad-kpi h-100">
                                <span class="cma-rule-label mb-1">Estado general</span>
                                <div class="fw-bold" id="cmaFadGlobalModo">Consultando...</div>
                                <div class="small text-muted mt-1" id="cmaFadGlobalMsg">Indica si FAD esta activo para todos los casos.</div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cma-fad-kpi h-100">
                                <span class="cma-rule-label mb-1">Excepciones activas</span>
                                <div class="fw-bold" id="cmaFadTotalReglas">0 excepciones</div>
                                <div class="small text-muted mt-1">Casos con una indicacion diferente al encendido general.</div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="cma-fad-kpi h-100">
                                <span class="cma-rule-label mb-1">Cambiar para todos</span>
                                <button type="button" class="cma-fad-toggle" id="cmaFadToggleGlobal" aria-pressed="false">
                                    <span>
                                        <span class="cma-fad-toggle-title" id="cmaFadToggleTitulo">FAD inactivo</span>
                                        <span class="cma-fad-toggle-sub" id="cmaFadToggleSub">Da clic para activarlo para todos.</span>
                                    </span>
                                    <span class="cma-fad-toggle-track" aria-hidden="true"><span class="cma-fad-toggle-dot"></span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <form class="mb-3 d-none" id="cmaFadForm">
                        <div class="cma-fad-inner-card" id="cmaFadExceptionControls">
                            <div class="mb-3">
                                <div class="cma-fad-inner-title">
                                    <i class="fa-solid fa-circle-minus me-1 text-warning"></i>Agregar excepcion por credito
                                </div>
                                <div class="cma-fad-inner-sub">Este credito no recibira solicitud FAD mientras la excepcion este activa.</div>
                            </div>
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-4">
                                    <label class="cma-rule-label mb-1" for="cmaFadCredito">Credito</label>
                                    <input type="text" class="form-control" id="cmaFadCredito" placeholder="Ej. 12345">
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="cma-rule-label mb-1" for="cmaFadMotivoExcepcion">Motivo</label>
                                    <input type="text" class="form-control" id="cmaFadMotivoExcepcion" placeholder="Ej. Excepcion manual autorizada">
                                </div>
                                <div class="col-12 col-lg-2 d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i>Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="cma-fad-section-title mb-2">
                        <i class="fa-solid fa-ban me-1"></i>Excepciones activas
                    </div>
                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-sm table-hover cma-fad-table">
                            <thead>
                                <tr>
                                    <th>Credito</th>
                                    <th>Motivo</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody id="cmaFadTbody">
                                <tr><td colspan="5" class="text-center text-muted py-3">Cargando excepciones...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-xl-6">
                            <div class="cma-fad-section-title mb-2">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i>Historial de apagados
                            </div>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover cma-fad-table">
                                    <thead>
                                        <tr>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Duracion</th>
                                            <th>Motivo</th>
                                            <th>Usuario</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cmaFadHistorialTbody">
                                        <tr><td colspan="5" class="text-center text-muted py-3">Cargando historial...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="cma-fad-section-title mb-2">
                                <i class="fa-solid fa-list-check me-1"></i>Decisiones recientes
                            </div>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover cma-fad-table">
                                    <thead>
                                        <tr>
                                            <th>Operacion</th>
                                            <th>Credito</th>
                                            <th>Decision</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cmaFadDecisionesTbody">
                                        <tr><td colspan="4" class="text-center text-muted py-3">Cargando decisiones...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="cma-fad-tab-pendientes" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="cma-fad-section-title mb-0">
                            <i class="fa-solid fa-bell me-1"></i>Pendientes FAD
                        </div>
                        <button type="button" class="btn btn-sm btn-label-secondary" id="cmaFadBtnPendientes">
                            <i class="fa-solid fa-rotate me-1"></i>Actualizar pendientes
                        </button>
                    </div>
                    <div class="table-responsive border rounded">
                        <table class="table table-sm table-hover cma-fad-table">
                            <thead>
                                <tr>
                                    <th>Operacion</th>
                                    <th>Credito</th>
                                    <th>Gestor</th>
                                    <th>Ultimo dato</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody id="cmaFadPendientesTbody">
                                <tr><td colspan="5" class="text-center text-muted py-3">Carga pendientes para consultar.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const $fadEstado = document.getElementById('cmaFadEstado');
    const $fadTbody = document.getElementById('cmaFadTbody');
    let fadGlobalEncendido = false;

    function esc(s) {
        if (s == null) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function actualizarBloqueoExcepciones(encendido, disponible) {
        const form = document.getElementById('cmaFadForm');
        const cont = document.getElementById('cmaFadExceptionControls');
        const bloqueado = !encendido || !disponible;
        if (form) form.classList.toggle('d-none', bloqueado);
        if (cont) cont.querySelectorAll('input, select, textarea, button').forEach(el => { el.disabled = bloqueado; });
    }

    function fechaCorta(v) {
        if (!v) return '-';
        return esc(String(v).replace('T', ' ').slice(0, 16));
    }

    function duracionFad(inicio, fin) {
        const a = inicio ? new Date(String(inicio).replace(' ', 'T')) : null;
        const b = fin ? new Date(String(fin).replace(' ', 'T')) : new Date();
        if (!a || Number.isNaN(a.getTime()) || Number.isNaN(b.getTime())) return '-';
        const mins = Math.max(0, Math.floor((b.getTime() - a.getTime()) / 60000));
        if (mins < 60) return mins + ' min';
        const horas = Math.floor(mins / 60);
        if (horas < 24) return horas + ' h';
        const dias = Math.floor(horas / 24);
        return dias + (dias === 1 ? ' dia' : ' dias');
    }

    function decisionTexto(v) {
        const mapa = {
            required: 'FAD requerido',
            credit_exception: 'Excepcion por credito',
            global_disabled_window: 'FAD apagado global',
        };
        return mapa[v] || v || '-';
    }

    function renderFad(fad) {
        const disponible = !!(fad && fad.disponible);
        const apagado = !!(fad && fad.global_apagado);
        const encendido = disponible && !apagado;
        const apagadoActual = fad && fad.apagado_actual ? fad.apagado_actual : null;
        const excepciones = Array.isArray(fad && fad.excepciones) ? fad.excepciones : [];
        fadGlobalEncendido = encendido;

        if ($fadEstado) {
            $fadEstado.className = 'cma-fad-pill ' + (encendido ? 'cma-fad-pill--on' : 'cma-fad-pill--off');
            $fadEstado.innerHTML = encendido
                ? '<i class="fa-solid fa-circle-check"></i>FAD activo'
                : '<i class="fa-solid fa-circle-xmark"></i>FAD apagado';
            if (!disponible) $fadEstado.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>No disponible';
        }

        const modo = document.getElementById('cmaFadGlobalModo');
        const msg = document.getElementById('cmaFadGlobalMsg');
        const total = document.getElementById('cmaFadTotalReglas');
        const toggle = document.getElementById('cmaFadToggleGlobal');
        const toggleTitle = document.getElementById('cmaFadToggleTitulo');
        const toggleSub = document.getElementById('cmaFadToggleSub');
        if (modo) modo.textContent = encendido ? 'FAD activo' : 'FAD apagado';
        if (msg) msg.textContent = apagadoActual ? (apagadoActual.motivo || 'FAD desactivado temporalmente') : 'FAD se pedira cuando aplique.';
        if (total) total.textContent = `${excepciones.length} excepcion${excepciones.length === 1 ? '' : 'es'} activa${excepciones.length === 1 ? '' : 's'}`;
        if (toggle) {
            toggle.classList.toggle('is-on', encendido);
            toggle.setAttribute('aria-pressed', encendido ? 'true' : 'false');
            toggle.disabled = !disponible;
        }
        if (toggleTitle) toggleTitle.textContent = encendido ? 'FAD activo' : 'FAD inactivo';
        if (toggleSub) toggleSub.textContent = encendido ? 'Da clic para apagarlo para todos.' : 'Da clic para activarlo para todos.';
        actualizarBloqueoExcepciones(encendido, disponible);

        if (!$fadTbody) return;
        if (!disponible) {
            $fadTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">${esc(fad && fad.mensaje ? fad.mensaje : 'No se pudo consultar FAD.')}</td></tr>`;
            return;
        }
        renderExcepciones(excepciones);
        renderHistorial(fad.historial_apagados || []);
        renderDecisiones(fad.decisiones || []);
    }

    function renderExcepciones(excepciones) {
        if (!$fadTbody) return;
        if (!excepciones.length) {
            $fadTbody.innerHTML = '<tr><td colspan="5"><div class="cma-fad-empty"><i class="fa-regular fa-folder-open d-block mb-1"></i>No hay excepciones puntuales. Solo aplica el interruptor general.</div></td></tr>';
            return;
        }
        $fadTbody.innerHTML = excepciones.map(r => `<tr>
            <td class="fw-bold"># ${esc(r.id_credito)}</td>
            <td>${esc(r.reason || '-')}</td>
            <td>${esc(r.created_by || '-')}</td>
            <td>${fechaCorta(r.created_at)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-label-danger" data-fad-desactivar="${esc(r.id)}">
                    <i class="fa-solid fa-ban me-1"></i>Desactivar
                </button>
            </td>
        </tr>`).join('');
    }

    function renderHistorial(rows) {
        const tbody = document.getElementById('cmaFadHistorialTbody');
        if (!tbody) return;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin apagados registrados.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(r => `<tr>
            <td>${fechaCorta(r.started_at)}</td>
            <td>${r.ended_at ? fechaCorta(r.ended_at) : '<span class="badge bg-label-warning">Abierto</span>'}</td>
            <td>${esc(duracionFad(r.started_at, r.ended_at))}</td>
            <td>${esc(r.motivo || '-')}</td>
            <td>${esc(r.created_by || '-')}</td>
        </tr>`).join('');
    }

    function renderDecisiones(rows) {
        const tbody = document.getElementById('cmaFadDecisionesTbody');
        if (!tbody) return;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin decisiones registradas.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.slice(0, 20).map(r => `<tr>
            <td>${esc(r.id_operacion || '-')}</td>
            <td>${esc(r.id_credito || '-')}</td>
            <td>${esc(decisionTexto(r.fad_decision_reason))}</td>
            <td>${fechaCorta(r.fad_decision_at || r.created_at)}</td>
        </tr>`).join('');
    }

    async function cargarFad() {
        try {
            const r = await fetch('/ConfigMotosAdj/fadObtener', { headers: { 'Accept': 'application/json' } });
            const data = await r.json();
            renderFad(data?.datos);
        } catch (e) {
            renderFad({ disponible: false, mensaje: 'No se pudo cargar FAD.' });
        }
    }

    async function fadAccionGlobal(accion) {
        let motivo = '';
        if (accion === 'off') {
            const ok = await Swal.fire({
                icon: 'warning',
                title: 'Apagar FAD',
                input: 'textarea',
                inputLabel: 'Motivo obligatorio',
                inputPlaceholder: 'Ej. FAD desactivado temporalmente por mantenimiento operativo',
                inputValidator: value => value && value.trim() ? undefined : 'Captura el motivo.',
                showCancelButton: true,
                confirmButtonText: 'Apagar FAD',
                cancelButtonText: 'Cancelar',
            });
            if (!ok.isConfirmed) return;
            motivo = String(ok.value || '').trim();
        } else {
            const ok = await Swal.fire({
                icon: 'question',
                title: 'Encender FAD',
                text: 'Se cerrara la ventana de apagado abierta y FAD volvera a aplicar.',
                showCancelButton: true,
                confirmButtonText: 'Encender',
                cancelButtonText: 'Cancelar',
            });
            if (!ok.isConfirmed) return;
        }

        try {
            const r = await fetch('/ConfigMotosAdj/fadGlobal', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ accion, motivo }),
            });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo actualizar FAD.');
            renderFad(data.datos);
            Swal.fire({ icon: 'success', title: 'Listo', text: data.mensaje || 'FAD actualizado.', timer: 1500, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo actualizar FAD.' });
        }
    }

    async function guardarReglaFad(ev) {
        ev.preventDefault();
        if (!fadGlobalEncendido) {
            Swal.fire({ icon: 'info', title: 'FAD esta apagado', text: 'Activa primero el interruptor general para poder agregar excepciones.' });
            return;
        }
        const creditoEl = document.getElementById('cmaFadCredito');
        const motivoEl = document.getElementById('cmaFadMotivoExcepcion');
        const payload = {
            id_credito: (creditoEl?.value || '').trim(),
            motivo: (motivoEl?.value || '').trim(),
        };
        if (!payload.id_credito || !payload.motivo) {
            Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Captura credito y motivo.' });
            return;
        }

        try {
            const r = await fetch('/ConfigMotosAdj/fadGuardarRegla', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo guardar la excepcion.');
            renderFad(data.datos);
            if (creditoEl) creditoEl.value = '';
            if (motivoEl) motivoEl.value = '';
            Swal.fire({ icon: 'success', title: 'Listo', text: data.mensaje || 'Excepcion agregada.', timer: 1500, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo guardar la excepcion FAD.' });
        }
    }

    async function desactivarExcepcion(id) {
        const ok = await Swal.fire({
            icon: 'question',
            title: 'Desactivar excepcion',
            text: 'No se borrara el registro; quedara en historial.',
            showCancelButton: true,
            confirmButtonText: 'Desactivar',
            cancelButtonText: 'Cancelar',
        });
        if (!ok.isConfirmed) return;
        try {
            const r = await fetch('/ConfigMotosAdj/fadDesactivarExcepcion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id }),
            });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo desactivar.');
            renderFad(data.datos);
            Swal.fire({ icon: 'success', title: 'Listo', text: data.mensaje || 'Excepcion desactivada.', timer: 1400, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo desactivar la excepcion.' });
        }
    }

    function renderPendientes(payload) {
        const tbody = document.getElementById('cmaFadPendientesTbody');
        if (!tbody) return;
        const raw = payload && payload.datos ? payload.datos : payload;
        const rows = Array.isArray(raw) ? raw : (Array.isArray(raw?.datos) ? raw.datos : (Array.isArray(raw?.items) ? raw.items : []));
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No hay pendientes FAD.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(r => {
            const idOperacion = r.id_operacion || r.operacion_id || r.id || '';
            return `<tr>
                <td>${esc(idOperacion || '-')}</td>
                <td>${esc(r.id_credito || r.credito || '-')}</td>
                <td>${esc(r.gestor_nombre || r.gestor || r.user_id_legacy || '-')}</td>
                <td>${fechaCorta(r.updated_at || r.created_at || r.dictamen_at || r.fecha)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" data-fad-recordatorio="${esc(idOperacion)}" ${idOperacion ? '' : 'disabled'}>
                        <i class="fa-solid fa-bell me-1"></i>Enviar recordatorio
                    </button>
                </td>
            </tr>`;
        }).join('');
    }

    async function cargarPendientesFad() {
        const tbody = document.getElementById('cmaFadPendientesTbody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Cargando pendientes...</td></tr>';
        try {
            const r = await fetch('/ConfigMotosAdj/fadPendientes', { headers: { 'Accept': 'application/json' } });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudieron cargar pendientes.');
            renderPendientes(data.datos);
        } catch (e) {
            if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">${esc(e.message || 'No se pudieron cargar pendientes.')}</td></tr>`;
        }
    }

    async function enviarRecordatorioFad(idOperacion) {
        if (!idOperacion) return;
        try {
            const r = await fetch('/ConfigMotosAdj/fadRecordatorio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_operacion: idOperacion }),
            });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo enviar recordatorio.');
            Swal.fire({ icon: 'success', title: 'Recordatorio enviado', timer: 1400, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo enviar recordatorio.' });
        }
    }

    document.getElementById('cmaFadToggleGlobal')?.addEventListener('click', () => {
        fadAccionGlobal(fadGlobalEncendido ? 'off' : 'soft_on');
    });
    document.getElementById('cmaFadForm')?.addEventListener('submit', guardarReglaFad);
    document.getElementById('cmaFadBtnPendientes')?.addEventListener('click', cargarPendientesFad);
    document.getElementById('cmaFadCard')?.addEventListener('click', function (ev) {
        const btnDesactivar = ev.target.closest('[data-fad-desactivar]');
        if (btnDesactivar) {
            desactivarExcepcion(parseInt(btnDesactivar.getAttribute('data-fad-desactivar'), 10) || 0);
            return;
        }
        const btnRecordatorio = ev.target.closest('[data-fad-recordatorio]');
        if (btnRecordatorio) enviarRecordatorioFad(btnRecordatorio.getAttribute('data-fad-recordatorio'));
    });
    cargarFad();
    cargarPendientesFad();
})();
</script>
