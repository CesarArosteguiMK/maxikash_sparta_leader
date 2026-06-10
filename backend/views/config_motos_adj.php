<style>
.cma-hero {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .5rem;
    padding: 1rem 1.15rem;
}
.cma-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e0f2fe;
    color: #0369a1;
    flex-shrink: 0;
}
.cma-rule-card {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    box-shadow: 0 .1rem .55rem rgba(15,23,42,.05);
}
.cma-rule-label {
    display: block;
    color: #64748b;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
}
.cma-preview {
    background: #ecfeff;
    color: #155e75;
    border: 1px solid #a5f3fc;
    border-radius: .5rem;
    padding: .7rem .85rem;
    font-size: .82rem;
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
.cma-fad-scope { font-weight: 800; color: #1e293b; }
.cma-fad-message {
    max-width: 22rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cma-fad-help {
    border: 1px solid #fde68a;
    border-radius: .5rem;
    background: #fffbeb;
    color: #92400e;
    padding: .65rem .8rem;
    font-size: .82rem;
}
.cma-fad-section-title {
    color: #1e293b;
    font-size: .8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.cma-fad-checks {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: .65rem .8rem;
    background: #f8fafc;
}
.cma-fad-checks .form-check-label {
    font-weight: 700;
    color: #334155;
}
.cma-fad-checks .form-text {
    margin-top: .1rem;
    font-size: .72rem;
}
.cma-fad-choice {
    border: 1px solid #dbe4ef;
    border-radius: .6rem;
    padding: .65rem .75rem;
    background: #fff;
    height: 100%;
}
.cma-fad-choice strong {
    display: block;
    color: #1e293b;
    font-size: .9rem;
}
.cma-fad-choice span {
    color: #64748b;
    font-size: .75rem;
}
.cma-fad-empty {
    border: 1px dashed #cbd5e1;
    border-radius: .65rem;
    padding: 1rem;
    text-align: center;
    color: #64748b;
}
body.dark-mode .cma-hero,
body.dark-mode .cma-rule-card {
    background: #17212b;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .cma-rule-label { color: #94a3b8; }
body.dark-mode .cma-preview {
    background: #164e63;
    color: #cffafe;
    border-color: #0e7490;
}
body.dark-mode .cma-fad-kpi { background: #111827; border-color: #334155; }
body.dark-mode .cma-fad-scope { color: #e2e8f0; }
body.dark-mode .cma-fad-help { background: #422006; border-color: #854d0e; color: #fde68a; }
body.dark-mode .cma-fad-checks { background: #111827; border-color: #334155; }
body.dark-mode .cma-fad-checks .form-check-label,
body.dark-mode .cma-fad-section-title { color: #e2e8f0; }
body.dark-mode .cma-fad-choice { background: #111827; border-color: #334155; }
body.dark-mode .cma-fad-choice strong { color: #e2e8f0; }
</style>

<div class="container-fluid py-3" id="configMotosAdjApp">
    <div class="cma-hero mb-3">
        <div class="d-flex align-items-center gap-3">
            <span class="cma-icon"><i class="fa-solid fa-motorcycle"></i></span>
            <div>
                <h5 class="mb-1">Config Motos Adj</h5>
                <p class="text-muted mb-0 small">
                    Parametros operativos para motos adjudicadas. Este modulo queda preparado para crecer con nuevas reglas sin tocar cada pantalla.
                </p>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card cma-rule-card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fa-solid fa-calendar-days me-2 text-primary"></i>
                        Fechas de rutas de recoleccion
                    </h6>
                </div>
                <div class="card-body">
                    <label class="cma-rule-label mb-1" for="cmaDiasMinRuta">Anticipacion minima</label>
                    <div class="input-group" style="max-width: 320px;">
                        <button class="btn btn-outline-secondary" type="button" id="cmaBtnMenos">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center fw-semibold" id="cmaDiasMinRuta"
                               min="0" max="365" step="1" value="2">
                        <span class="input-group-text">días</span>
                        <button class="btn btn-outline-secondary" type="button" id="cmaBtnMas">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        Define desde cuántos días en el futuro se puede programar una ruta. Por ejemplo: 3 días, 4 días o 7 días.
                    </div>

                    <div class="cma-preview mt-3" id="cmaPreview">
                        Calculando fecha minima...
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-label-secondary" id="cmaBtnRecargar">
                        <i class="fa-solid fa-rotate me-1"></i>Recargar
                    </button>
                    <button type="button" class="btn btn-primary" id="cmaBtnGuardar">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card cma-rule-card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fa-solid fa-layer-group me-2 text-info"></i>
                        Preparado para nuevas reglas
                    </h6>
                </div>
                <div class="card-body small text-muted">
                    <p class="mb-2">La configuracion se guarda como claves independientes, asi podemos agregar despues:</p>
                    <ul class="mb-0">
                        <li>ventanas maximas de recoleccion,</li>
                        <li>reglas por CEDIS,</li>
                        <li>bloqueo por días no laborales,</li>
                        <li>configuracion de ETA o transportistas.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card cma-rule-card mt-3" id="cmaFadCard">
        <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <div>
                <h6 class="mb-1">
                    <i class="fa-solid fa-file-signature me-2 text-warning"></i>
                    FAD para motos adjudicadas
                </h6>
                <div class="small text-muted">Controla si el gestor debe atender el FAD en la app y a que casos les aplica.</div>
            </div>
            <span class="cma-fad-pill cma-fad-pill--off" id="cmaFadEstado">
                <i class="fa-solid fa-circle-xmark"></i>Sin datos
            </span>
        </div>
        <div class="card-body">
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
                        <span class="cma-rule-label mb-1">Casos configurados</span>
                        <div class="fw-bold" id="cmaFadTotalReglas">0 configuraciones activas</div>
                        <div class="small text-muted mt-1">Las reglas mas especificas pueden ganar sobre la general.</div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="cma-fad-kpi h-100">
                        <span class="cma-rule-label mb-1">Cambiar para todos</span>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-success" id="cmaFadBtnSoftOn">
                                <i class="fa-solid fa-toggle-on me-1"></i>Activar FAD
                            </button>
                            <button type="button" class="btn btn-sm btn-label-danger" id="cmaFadBtnOff">
                                <i class="fa-solid fa-toggle-off me-1"></i>Desactivar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cma-fad-help mb-3">
                <i class="fa-solid fa-circle-info me-1"></i>
                Piensalo como un interruptor operativo: puedes pedir FAD para todos, apagarlo para todos o aplicarlo solamente a un caso puntual.
            </div>

            <form class="mb-3" id="cmaFadForm">
                <div class="cma-fad-section-title mb-2">
                    <i class="fa-solid fa-bullseye me-1"></i>Agregar una excepcion o caso especial
                </div>
                <div class="row g-3 align-items-stretch">
                    <div class="col-12 col-lg-4">
                        <div class="cma-fad-choice">
                            <label class="cma-rule-label mb-1" for="cmaFadScope">Para quien aplica</label>
                            <select class="form-select" id="cmaFadScope">
                                <option value="operation">Solo una operacion</option>
                                <option value="credit">Solo un credito</option>
                                <option value="dictamen">Solo un dictamen</option>
                                <option value="user">Solo un usuario Legacy</option>
                                <option value="external_id">Solo un numero de empleado</option>
                                <option value="global">Todos los casos</option>
                            </select>
                            <span>Elige el tipo de caso que quieres controlar.</span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="cma-fad-choice">
                            <label class="cma-rule-label mb-1" for="cmaFadScopeValue">Identificador</label>
                            <input type="text" class="form-control" id="cmaFadScopeValue" placeholder="Ej. 103">
                            <span>Escribe el numero de operacion, credito, usuario o empleado.</span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="cma-fad-choice">
                            <label class="cma-rule-label mb-1" for="cmaFadMode">Que debe pasar</label>
                            <select class="form-select" id="cmaFadMode">
                                <option value="required">Debe completar FAD</option>
                                <option value="optional">Solo mostrar aviso</option>
                                <option value="off">No pedir FAD</option>
                            </select>
                            <span>Esto define la instruccion que recibira la app.</span>
                        </div>
                    </div>
                    <div class="col-12 col-lg-10">
                        <label class="cma-rule-label mb-1" for="cmaFadMessage">Texto que vera el gestor</label>
                        <input type="text" class="form-control" id="cmaFadMessage" value="FAD pendiente para moto adjudicada">
                    </div>
                    <div class="col-12 col-lg-2 d-grid align-self-end">
                        <button type="submit" class="btn btn-primary" title="Agregar caso FAD">
                            <i class="fa-solid fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-link px-0" type="button" data-bs-toggle="collapse" data-bs-target="#cmaFadOpcionesAvanzadas">
                        <i class="fa-solid fa-sliders me-1"></i>Opciones avanzadas
                    </button>
                    <div class="cma-fad-checks collapse" id="cmaFadOpcionesAvanzadas">
                        <div class="row g-2">
                            <div class="col-12 col-md-3">
                                <label class="cma-rule-label mb-1" for="cmaFadPriority">Importancia interna</label>
                                <input type="number" class="form-control form-control-sm" id="cmaFadPriority" min="0" max="9999" value="500">
                                <div class="form-text">Si dos reglas coinciden, gana el numero mayor.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="cmaFadEnabled" checked>
                                    <span class="form-check-label">Mantener activo este caso</span>
                                </label>
                                <div class="form-text">Si se apaga, se conserva pero no se usa.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="cmaFadRequires" checked>
                                    <span class="form-check-label">Marcar como pendiente</span>
                                </label>
                                <div class="form-text">La app lo mostrara como tarea pendiente.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="cmaFadBadge" checked>
                                    <span class="form-check-label">Mostrar aviso visual</span>
                                </label>
                                <div class="form-text">Muestra badge o alerta en la app del gestor.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="cmaFadBlockClose">
                                    <span class="form-check-label">Bloquear cierre</span>
                                </label>
                                <div class="form-text">Evita cerrar el flujo si FAD sigue pendiente.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="cmaFadNotify">
                                    <span class="form-check-label">Enviar recordatorio</span>
                                </label>
                                <div class="form-text">Deja marcado que debe notificarse si esta pendiente.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive border rounded">
                <table class="table table-sm table-hover cma-fad-table">
                    <thead>
                        <tr>
                            <th>Caso</th>
                            <th>Instruccion para la app</th>
                            <th>Avisos</th>
                            <th>Vigencia</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody id="cmaFadTbody">
                        <tr><td colspan="5" class="text-center text-muted py-3">Cargando reglas FAD...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const $dias = document.getElementById('cmaDiasMinRuta');
    const $preview = document.getElementById('cmaPreview');
    const $guardar = document.getElementById('cmaBtnGuardar');
    const $fadEstado = document.getElementById('cmaFadEstado');
    const $fadTbody = document.getElementById('cmaFadTbody');

    function clampDias(v) {
        const n = parseInt(v, 10);
        if (Number.isNaN(n)) return 2;
        return Math.max(0, Math.min(365, n));
    }

    function fechaMinimaTexto(dias) {
        const d = new Date();
        d.setDate(d.getDate() + dias);
        return d.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }

    function actualizarPreview() {
        const dias = clampDias($dias.value);
        $dias.value = String(dias);
        $preview.innerHTML = `<b>Con ${dias} día${dias === 1 ? '' : 's'} de anticipación:</b> la ruta más cercana se podrá programar a partir de <b>${fechaMinimaTexto(dias)}</b>.`;
    }

    function esc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function boolIcon(v, label) {
        return v ? `<span class="badge bg-label-success me-1">${esc(label)}</span>` : '';
    }

    function scopeTexto(scope) {
        const mapa = {
            global: 'Todos los casos',
            user: 'Solo usuario Legacy',
            external_id: 'Solo numero de empleado',
            credit: 'Solo credito',
            operation: 'Solo operacion',
            dictamen: 'Solo dictamen',
        };
        return mapa[scope] || scope || '-';
    }

    function modoTexto(mode) {
        const mapa = {
            required: 'Obligatorio',
            optional: 'Solo avisar',
            off: 'No aplicar',
        };
        return mapa[mode] || mode || '-';
    }

    function renderFad(fad) {
        const disponible = !!(fad && fad.disponible);
        const global = fad && fad.global ? fad.global : null;
        const reglas = Array.isArray(fad && fad.reglas) ? fad.reglas : [];
        const encendido = !!(global && parseInt(global.enabled, 10) === 1);

        if ($fadEstado) {
            $fadEstado.className = 'cma-fad-pill ' + (encendido ? 'cma-fad-pill--on' : 'cma-fad-pill--off');
            $fadEstado.innerHTML = encendido
                ? '<i class="fa-solid fa-circle-check"></i>FAD activo'
                : '<i class="fa-solid fa-circle-xmark"></i>FAD apagado';
            if (!disponible) {
                $fadEstado.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>No disponible';
            }
        }

        const modo = document.getElementById('cmaFadGlobalModo');
        const msg = document.getElementById('cmaFadGlobalMsg');
        const total = document.getElementById('cmaFadTotalReglas');
        if (modo) modo.textContent = global ? modoTexto(global.mode) : 'Sin configuracion general';
        if (msg) msg.textContent = global && global.message ? global.message : (fad && fad.mensaje ? fad.mensaje : 'Aqui veras que indicacion recibira la app.');
        if (total) total.textContent = `${reglas.length} caso${reglas.length === 1 ? '' : 's'} con FAD configurado`;

        if (!$fadTbody) return;
        if (!disponible) {
            $fadTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">${esc(fad && fad.mensaje ? fad.mensaje : 'No se pudo consultar FAD.')}</td></tr>`;
            return;
        }
        if (!reglas.length) {
            $fadTbody.innerHTML = '<tr><td colspan="5"><div class="cma-fad-empty"><i class="fa-regular fa-folder-open d-block mb-1"></i>No hay casos especiales configurados. Solo aplica el estado general.</div></td></tr>';
            return;
        }

        $fadTbody.innerHTML = reglas.map(function (r) {
            const flags = [
                boolIcon(parseInt(r.enabled, 10) === 1, 'activa'),
                boolIcon(parseInt(r.requires_fad, 10) === 1, 'debe completar'),
                boolIcon(parseInt(r.show_badge, 10) === 1, 'muestra aviso'),
                boolIcon(parseInt(r.block_close, 10) === 1, 'bloquea cierre'),
                boolIcon(parseInt(r.notify_pending, 10) === 1, 'recordatorio'),
            ].join('');
            const vigencia = [
                r.effective_from ? 'Desde ' + esc(r.effective_from) : 'Desde hoy',
                r.effective_to ? 'Hasta ' + esc(r.effective_to) : 'Sin cierre',
            ].join('<br>');
            return `<tr>
                <td>
                    <div class="cma-fad-scope">${esc(scopeTexto(r.scope))}</div>
                    <div class="small text-muted">${esc(r.scope_value)}</div>
                </td>
                <td><span class="badge bg-label-secondary">${esc(modoTexto(r.mode))}</span></td>
                <td>${flags || '<span class="text-muted">Sin acciones</span>'}</td>
                <td class="small">${vigencia}</td>
                <td class="cma-fad-message" title="${esc(r.message || '')}">${esc(r.message || '-')}</td>
            </tr>`;
        }).join('');
    }

    async function cargar() {
        try {
            const r = await fetch('/ConfigMotosAdj/obtener', { headers: { 'Accept': 'application/json' } });
            const data = await r.json();
            const dias = data?.datos?.tracking?.ruta_dias_minimos;
            $dias.value = String(clampDias(dias));
            actualizarPreview();
            renderFad(data?.datos?.fad);
        } catch (e) {
            actualizarPreview();
            renderFad({ disponible: false, mensaje: 'No se pudo cargar FAD.' });
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la configuracion.' });
        }
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
        const label = accion === 'off' ? 'desactivar FAD para todos' : 'activar FAD para todos';
        const ok = await Swal.fire({
            icon: 'question',
            title: 'Confirmar cambio',
            text: 'Se va a ' + label + '.',
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar',
        });
        if (!ok.isConfirmed) return;

        try {
            const r = await fetch('/ConfigMotosAdj/fadGlobal', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ accion }),
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
        const scope = document.getElementById('cmaFadScope')?.value || 'operation';
        const scopeValueEl = document.getElementById('cmaFadScopeValue');
        const payload = {
            scope,
            scope_value: scope === 'global' ? '*' : (scopeValueEl?.value || '').trim(),
            mode: document.getElementById('cmaFadMode')?.value || 'required',
            rollout_mode: 'manual',
            priority: parseInt(document.getElementById('cmaFadPriority')?.value || '500', 10),
            message: document.getElementById('cmaFadMessage')?.value || 'FAD pendiente para moto adjudicada',
            enabled: document.getElementById('cmaFadEnabled')?.checked ? 1 : 0,
            requires_fad: document.getElementById('cmaFadRequires')?.checked ? 1 : 0,
            show_badge: document.getElementById('cmaFadBadge')?.checked ? 1 : 0,
            block_close: document.getElementById('cmaFadBlockClose')?.checked ? 1 : 0,
            notify_pending: document.getElementById('cmaFadNotify')?.checked ? 1 : 0,
        };
        if (payload.scope !== 'global' && !payload.scope_value) {
            Swal.fire({ icon: 'warning', title: 'Falta dato', text: 'Captura el folio o identificador al que aplica FAD.' });
            return;
        }

        try {
            const r = await fetch('/ConfigMotosAdj/fadGuardarRegla', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo guardar la regla.');
            renderFad(data.datos);
            if (scopeValueEl && payload.scope !== 'global') scopeValueEl.value = '';
            Swal.fire({ icon: 'success', title: 'Listo', text: data.mensaje || 'Regla agregada.', timer: 1500, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo guardar la regla FAD.' });
        }
    }

    async function guardar() {
        const dias = clampDias($dias.value);
        $guardar.disabled = true;
        try {
            const r = await fetch('/ConfigMotosAdj/guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ ruta_dias_minimos: dias }),
            });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo guardar.');
            Swal.fire({ icon: 'success', title: 'Listo', text: 'Configuracion guardada correctamente.', timer: 1600, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo guardar la configuracion.' });
        } finally {
            $guardar.disabled = false;
        }
    }

    document.getElementById('cmaBtnMenos')?.addEventListener('click', () => { $dias.value = String(clampDias($dias.value) - 1); actualizarPreview(); });
    document.getElementById('cmaBtnMas')?.addEventListener('click', () => { $dias.value = String(clampDias($dias.value) + 1); actualizarPreview(); });
    document.getElementById('cmaBtnRecargar')?.addEventListener('click', cargar);
    $dias?.addEventListener('input', actualizarPreview);
    $guardar?.addEventListener('click', guardar);
    document.getElementById('cmaFadBtnSoftOn')?.addEventListener('click', () => fadAccionGlobal('soft_on'));
    document.getElementById('cmaFadBtnOff')?.addEventListener('click', () => fadAccionGlobal('off'));
    document.getElementById('cmaFadForm')?.addEventListener('submit', guardarReglaFad);
    document.getElementById('cmaFadScope')?.addEventListener('change', function () {
        const inp = document.getElementById('cmaFadScopeValue');
        if (!inp) return;
        if (this.value === 'global') {
            inp.value = '*';
            inp.disabled = true;
            inp.placeholder = 'Todos';
        } else {
            inp.disabled = false;
            if (inp.value === '*') inp.value = '';
            const placeholders = {
                operation: 'Ej. 103',
                credit: 'Ej. 1660735',
                dictamen: 'Ej. 250',
                user: 'ID Legacy',
                external_id: 'No. empleado',
            };
            inp.placeholder = placeholders[this.value] || 'Identificador';
        }
    });
    cargar();
    cargarFad();
})();
</script>
