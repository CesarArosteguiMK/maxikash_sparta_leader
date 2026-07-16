<style>
    .legacy-sync-page {
        color: #22303e;
    }
    .legacy-sync-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: .85rem;
        background: linear-gradient(135deg, #26344e 0%, #36527a 100%);
        color: #fff;
    }
    .legacy-sync-header h4 {
        margin: 0;
        color: #fff;
        font-size: 1.35rem;
        font-weight: 800;
    }
    .legacy-sync-header p {
        margin: .15rem 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: .88rem;
    }
    .legacy-sync-card {
        border: 1px solid #dbe4ef;
        border-radius: .75rem;
        background: #fff;
        padding: 1rem;
    }
    .legacy-sync-title {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin: 0 0 .35rem;
        color: #26344e;
        font-size: .95rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .legacy-sync-note {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        line-height: 1.45;
    }
    .legacy-sync-actionbar {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .legacy-sync-btn {
        border: 0;
        border-radius: 999px;
        background: #26344e;
        color: #fff;
        padding: .68rem 1.25rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        box-shadow: 0 8px 18px rgba(38, 52, 78, .18);
    }
    .legacy-sync-btn:hover {
        background: #1d293f;
        color: #fff;
    }
    .legacy-sync-result {
        margin-top: 1rem;
        display: none;
    }
    .legacy-sync-summary {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: .65rem;
        margin-bottom: 1rem;
    }
    .legacy-sync-kpi {
        border: 1px solid #e2e8f0;
        border-radius: .65rem;
        padding: .75rem;
        background: #f8fafc;
    }
    .legacy-sync-kpi span {
        display: block;
        color: #64748b;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .035em;
    }
    .legacy-sync-kpi strong {
        display: block;
        margin-top: .2rem;
        color: #26344e;
        font-size: 1.35rem;
        line-height: 1;
    }
    .legacy-sync-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        overflow: hidden;
        background: #fff;
    }
    .legacy-sync-table {
        width: 100%;
        margin: 0;
        font-size: .84rem;
    }
    .legacy-sync-table th {
        background: #f8fafc;
        color: #566a7f;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .legacy-sync-table td {
        vertical-align: middle;
        color: #475569;
    }
    .legacy-sync-user strong,
    .legacy-sync-role {
        color: #26344e;
        font-weight: 800;
    }
    .legacy-sync-muted {
        color: #94a3b8;
        font-size: .78rem;
        font-weight: 700;
    }
    .legacy-sync-pill {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        padding: .18rem .55rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .legacy-sync-pill-ok { background: #dcfce7; color: #15803d; }
    .legacy-sync-pill-warn { background: #fef3c7; color: #b45309; }
    .legacy-sync-pill-error { background: #fee2e2; color: #b91c1c; }
    .legacy-sync-pill-info { background: #e0f2fe; color: #0369a1; }
    .legacy-sync-motivos {
        display: flex;
        flex-wrap: wrap;
        gap: .3rem;
    }
    .legacy-sync-tabs {
        border-bottom: 1px solid #dbe4ef;
        margin-bottom: 1rem;
        gap: .5rem;
    }
    .legacy-sync-tabs .nav-link {
        border: 0;
        border-radius: .65rem .65rem 0 0;
        color: #566a7f;
        font-weight: 800;
        padding: .75rem 1rem;
        transition: none;
    }
    .legacy-sync-tabs .nav-link:hover,
    .legacy-sync-tabs .nav-link:focus,
    .legacy-sync-tabs .nav-link:focus-visible {
        color: #566a7f;
        box-shadow: none;
        outline: 0;
    }
    .legacy-sync-tabs .nav-link.active {
        background: #26344e;
        color: #fff;
    }
    .legacy-sync-tabs .nav-link.active:hover,
    .legacy-sync-tabs .nav-link.active:focus,
    .legacy-sync-tabs .nav-link.active:focus-visible {
        color: #fff;
    }
    .legacy-sync-config-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1rem;
    }
    .legacy-sync-list {
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        max-height: 460px;
        overflow: auto;
        background: #fff;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-content: start;
        gap: .25rem;
        padding: .5rem;
    }
    .legacy-sync-check {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        padding: .75rem .9rem;
        border-bottom: 1px solid #eef2f7;
        border-radius: .65rem;
        cursor: pointer;
    }
    .legacy-sync-check:last-child { border-bottom: 0; }
    .legacy-sync-check strong {
        display: block;
        color: #26344e;
        font-size: .86rem;
        line-height: 1.2;
    }
    .legacy-sync-check small {
        display: block;
        color: #64748b;
        font-weight: 700;
        margin-top: .15rem;
    }
    .legacy-sync-search {
        max-width: 320px;
    }
    .legacy-sync-personal-grid {
        display: grid;
        grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
        gap: 1rem;
    }
    .legacy-sync-user-list {
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        max-height: 460px;
        overflow: auto;
        background: #fff;
    }
    .legacy-sync-user-option {
        width: 100%;
        border: 0;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        text-align: left;
        padding: .8rem .9rem;
        color: #26344e;
    }
    .legacy-sync-user-option:hover,
    .legacy-sync-user-option.is-active {
        background: #f1f5f9;
    }
    .legacy-sync-user-option strong {
        display: block;
        font-size: .86rem;
        font-weight: 800;
    }
    .legacy-sync-user-option small {
        display: block;
        color: #64748b;
        font-weight: 700;
        margin-top: .15rem;
    }
    .legacy-sync-selected-box {
        border: 1px solid #dbe4ef;
        border-radius: .75rem;
        background: #f8fafc;
        padding: 1rem;
        min-height: 180px;
    }
    .legacy-sync-dept-summary {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: 1rem;
    }
    .legacy-sync-dept-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border: 1px solid #dbe4ef;
        border-radius: 999px;
        padding: .45rem .75rem;
        background: #f8fafc;
        color: #26344e;
        font-weight: 800;
        font-size: .78rem;
    }
    .legacy-sync-dept-chip small {
        color: #64748b;
        font-weight: 800;
    }
    @media (max-width: 1199.98px) {
        .legacy-sync-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .legacy-sync-config-grid { grid-template-columns: 1fr; }
        .legacy-sync-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .legacy-sync-personal-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 767.98px) {
        .legacy-sync-header { align-items: flex-start; flex-direction: column; }
        .legacy-sync-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .legacy-sync-list { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid py-4 legacy-sync-page">
    <div class="legacy-sync-header">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-arrows-rotate fa-2x"></i>
            <div>
                <h4>Sincroniza Legacy</h4>
                <p>Sincronizacion completa de usuarios Spartan hacia Legacy: alta, usuario, contrasena, rol y jerarquia.</p>
            </div>
        </div>
        <span class="badge bg-label-light text-dark">
            <i class="fa-solid fa-shield-halved me-1"></i>Organizacion
        </span>
    </div>

    <ul class="nav legacy-sync-tabs" id="legacy-sync-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#legacy-sync-tab-proceso" role="tab">
                <i class="fa-solid fa-arrows-rotate me-1"></i>Sincronización
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#legacy-sync-tab-personal" role="tab">
                <i class="fa-solid fa-user-check me-1"></i>Actualizacion individual
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#legacy-sync-tab-config" role="tab">
                <i class="fa-solid fa-sliders me-1"></i>Configuración
            </button>
        </li>
    </ul>

    <div class="tab-content p-0">
        <div class="tab-pane fade show active" id="legacy-sync-tab-proceso" role="tabpanel">
    <div class="legacy-sync-card">
        <h5 class="legacy-sync-title">
            <i class="fa-solid fa-database"></i> Reproceso masivo
        </h5>
        <p class="legacy-sync-note">
            Ejecuta una sincronizacion forzada para todos los usuarios dentro del alcance configurado.
            El backend divide el proceso en lotes internos y entrega un resumen global; no necesitas escoger
            cuantos usuarios revisar. Actualiza o crea el usuario en Legacy, sincroniza usuario/contrasena,
            asigna el rol equivalente y recalcula supervisor, subgerente, gerente y subdirector.
        </p>

        <div class="legacy-sync-actionbar">
            <div class="legacy-sync-note">
                <i class="fa-solid fa-layer-group me-1"></i>
                Se procesan todos los candidatos detectados, por lotes de backend.
            </div>
            <button type="button" class="legacy-sync-btn" id="legacy-sync-btn">
                <i class="fa-solid fa-cloud-arrow-up"></i> Sincronizar todo con Legacy
            </button>
        </div>
    </div>

    <div class="legacy-sync-result" id="legacy-sync-result">
        <div class="legacy-sync-summary" id="legacy-sync-summary"></div>
        <div class="legacy-sync-table-wrap">
            <table class="table table-hover legacy-sync-table" id="legacy-sync-table">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Usuario</th>
                        <th>Spartan</th>
                        <th>Legacy</th>
                        <th>Motivo</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody id="legacy-sync-tbody"></tbody>
            </table>
        </div>
    </div>
        </div>

        <div class="tab-pane fade" id="legacy-sync-tab-personal" role="tabpanel">
            <div class="legacy-sync-card">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                    <div>
                        <h5 class="legacy-sync-title">
                            <i class="fa-solid fa-user-gear"></i> Actualizar usuario
                        </h5>
                        <p class="legacy-sync-note">
                            Selecciona una persona dentro del alcance configurado y actualiza solo ese usuario en Legacy.
                        </p>
                    </div>
                    <span class="badge bg-label-info text-dark" id="legacy-sync-personal-status">Cargando usuarios</span>
                </div>

                <div class="legacy-sync-personal-grid">
                    <div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <input type="search" class="form-control legacy-sync-search" id="legacy-sync-personal-search" placeholder="Buscar usuario, puesto o departamento...">
                            <button type="button" class="btn btn-label-secondary" id="legacy-sync-personal-refresh" title="Actualizar lista">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                        <div class="legacy-sync-user-list" id="legacy-sync-personal-list">
                            <div class="p-3 text-muted">Cargando usuarios...</div>
                        </div>
                    </div>
                    <div>
                        <div class="legacy-sync-selected-box" id="legacy-sync-personal-detail">
                            <div class="text-muted fw-bold">
                                <i class="fa-solid fa-hand-pointer me-1"></i>
                                Selecciona un usuario para actualizarlo.
                            </div>
                        </div>
                        <div class="legacy-sync-actionbar">
                            <div class="legacy-sync-note" id="legacy-sync-personal-resumen">
                                Solo aparecen usuarios cuyo puesto pertenece a la configuracion activa.
                            </div>
                            <button type="button" class="legacy-sync-btn" id="legacy-sync-personal-btn" disabled>
                                <i class="fa-solid fa-cloud-arrow-up"></i> Actualizar usuario
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="legacy-sync-tab-config" role="tabpanel">
            <div class="legacy-sync-card">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                    <div>
                        <h5 class="legacy-sync-title">
                            <i class="fa-solid fa-filter-circle-dollar"></i> Alcance de sincronización
                        </h5>
                        <p class="legacy-sync-note">
                            Define qué puestos se sincronizan con Legacy.
                            Al guardar esta configuración, deja de usarse la regla fija anterior.
                        </p>
                        <p class="legacy-sync-note mt-2">
                            Si un puesto no aparece en esta lista, primero revisa que tenga equivalencia configurada en
                            <strong>Equivalencia puestos</strong>. Sin esa equivalencia, el sistema no puede enviarlo a Legacy.
                        </p>
                    </div>
                    <span class="badge bg-label-info text-dark" id="legacy-sync-config-status">Cargando configuración</span>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <input type="search" class="form-control legacy-sync-search" id="legacy-sync-config-search" placeholder="Buscar puesto...">
                </div>

                <div class="legacy-sync-dept-summary" id="legacy-sync-dept-summary">
                    <span class="legacy-sync-dept-chip">
                        <i class="fa-solid fa-building-user"></i>
                        Departamentos cubiertos se calculan al guardar
                    </span>
                </div>

                <div class="legacy-sync-config-grid">
                    <div>
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-id-card-clip me-1"></i>Puestos</h6>
                        <div class="legacy-sync-list" id="legacy-sync-puestos-list">
                            <div class="p-3 text-muted">Cargando puestos...</div>
                        </div>
                    </div>
                </div>

                <div class="legacy-sync-actionbar">
                    <div class="legacy-sync-note" id="legacy-sync-config-resumen">
                        Selecciona al menos un puesto.
                    </div>
                    <button type="button" class="legacy-sync-btn" id="legacy-sync-config-save">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar configuración
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const btn = document.getElementById('legacy-sync-btn');
    const result = document.getElementById('legacy-sync-result');
    const summary = document.getElementById('legacy-sync-summary');
    const tbody = document.getElementById('legacy-sync-tbody');
    const configStatus = document.getElementById('legacy-sync-config-status');
    const configSearch = document.getElementById('legacy-sync-config-search');
    const configSave = document.getElementById('legacy-sync-config-save');
    const configResumen = document.getElementById('legacy-sync-config-resumen');
    const puestosList = document.getElementById('legacy-sync-puestos-list');
    const deptSummary = document.getElementById('legacy-sync-dept-summary');
    const personalStatus = document.getElementById('legacy-sync-personal-status');
    const personalSearch = document.getElementById('legacy-sync-personal-search');
    const personalRefresh = document.getElementById('legacy-sync-personal-refresh');
    const personalList = document.getElementById('legacy-sync-personal-list');
    const personalDetail = document.getElementById('legacy-sync-personal-detail');
    const personalResumen = document.getElementById('legacy-sync-personal-resumen');
    const personalBtn = document.getElementById('legacy-sync-personal-btn');
    const LEGACY_SYNC_UI_VERSION = 'legacy-sync-progress-v2';
    const LEGACY_SYNC_ACTIVE_TAB_KEY = 'caphum_sincroniza_legacy_active_tab';
    let legacySyncConfig = { puestos: [], departamentosSincronizacion: [], seleccion: { puestos: [] }, configurado: false };
    let legacySyncPersonal = { usuarios: [], seleccionado: null };

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function guardarLegacySyncTabActivo(target) {
        try {
            if (target) window.localStorage.setItem(LEGACY_SYNC_ACTIVE_TAB_KEY, target);
            else window.localStorage.removeItem(LEGACY_SYNC_ACTIVE_TAB_KEY);
        } catch (err) {}
    }

    function obtenerLegacySyncTabActivo() {
        try { return window.localStorage.getItem(LEGACY_SYNC_ACTIVE_TAB_KEY) || ''; } catch (err) { return ''; }
    }

    function restaurarLegacySyncTabActivo() {
        const target = obtenerLegacySyncTabActivo();
        if (!target) return;
        const btn = document.querySelector('#legacy-sync-tabs [data-bs-target="' + target + '"]');
        const pane = document.querySelector(target);
        if (!btn || !pane) {
            guardarLegacySyncTabActivo('');
            return;
        }
        if (window.bootstrap && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(btn).show();
        } else {
            document.querySelectorAll('#legacy-sync-tabs .nav-link').forEach(el => el.classList.toggle('active', el === btn));
            document.querySelectorAll('#legacy-sync-tabs + .tab-content > .tab-pane').forEach(el => el.classList.toggle('show', el === pane));
            document.querySelectorAll('#legacy-sync-tabs + .tab-content > .tab-pane').forEach(el => el.classList.toggle('active', el === pane));
        }
    }

    function motivoLabel(motivo) {
        const map = {
            no_existe_en_legacy: 'No existe en Legacy',
            baja_en_legacy_con_spartan_activo: 'Baja en Legacy',
            role_desalineado: 'Rol desalineado',
            usuario_desalineado: 'Usuario desalineado',
            nombre_desalineado: 'Nombre desalineado',
            sincronizacion_forzada: 'Recalculo forzado',
            baja_spartan_activa_en_legacy: 'Baja Spartan activa en Legacy'
        };
        return map[motivo] || motivo;
    }

    function resultadoMeta(resultado) {
        if (resultado === 'actualizado') return { cls: 'legacy-sync-pill-ok', icon: 'fa-circle-check', label: 'Actualizado' };
        if (resultado === 'sin_cambios') return { cls: 'legacy-sync-pill-info', icon: 'fa-circle-info', label: 'Sin cambios' };
        if (resultado === 'omitido') return { cls: 'legacy-sync-pill-warn', icon: 'fa-triangle-exclamation', label: 'Omitido' };
        if (resultado === 'error') return { cls: 'legacy-sync-pill-error', icon: 'fa-circle-xmark', label: 'Error' };
        return { cls: 'legacy-sync-pill-info', icon: 'fa-circle-info', label: resultado || 'Revisado' };
    }

    function kpi(label, value) {
        return '<div class="legacy-sync-kpi"><span>' + esc(label) + '</span><strong>' + esc(value) + '</strong></div>';
    }

    function renderResumen(resumen) {
        const r = resumen || {};
        summary.innerHTML = [
            kpi('Revisados', r.revisados || 0),
            kpi('Detectados', r.pendientes_detectados || 0),
            kpi('Actualizados', r.actualizados || 0),
            kpi('Sin cambios', r.sin_cambios || 0),
            kpi('Lotes', r.lotes || 0),
            kpi('Errores', r.errores || 0)
        ].join('');
    }

    function renderTabla(datos) {
        const rows = Array.isArray(datos) ? datos : [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hubo usuarios por sincronizar.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            const sync = row.sync || {};
            const meta = resultadoMeta(sync.resultado || '');
            const motivos = (row.motivos || []).map(function (m) {
                return '<span class="legacy-sync-pill legacy-sync-pill-info">' + esc(motivoLabel(m)) + '</span>';
            }).join('');
            return '<tr>' +
                '<td><span class="legacy-sync-pill legacy-sync-pill-info">' + esc(row.lote || '-') + '</span></td>' +
                '<td class="legacy-sync-user"><strong>' + esc(row.nombre || 'Sin nombre') + '</strong><div class="legacy-sync-muted"># ' + esc(row.external_id || '-') + '</div></td>' +
                '<td><div class="legacy-sync-role">' + esc(row.puesto || '-') + '</div><div class="legacy-sync-muted">' + esc(row.departamento || '-') + '</div></td>' +
                '<td><span class="legacy-sync-role">' + esc(row.role_legacy || '-') + '</span></td>' +
                '<td><div class="legacy-sync-motivos">' + motivos + '</div></td>' +
                '<td><span class="legacy-sync-pill ' + meta.cls + '"><i class="fa-solid ' + meta.icon + '"></i>' + esc(meta.label) + '</span>' +
                    (sync.mensaje ? '<div class="legacy-sync-muted mt-1">' + esc(sync.mensaje) + '</div>' : '') +
                '</td>' +
            '</tr>';
        }).join('');
    }

    function actualizarResumenConfig() {
        const puestos = (legacySyncConfig.seleccion.puestos || []).length;
        const departamentos = (legacySyncConfig.departamentosSincronizacion || []).length;
        if (configResumen) {
            configResumen.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>' +
                esc(puestos) + ' puesto(s) seleccionados; ' + esc(departamentos) + ' departamento(s) cubiertos.';
        }
    }

    function renderDepartamentosSincronizacion() {
        if (!deptSummary) return;
        const rows = legacySyncConfig.departamentosSincronizacion || [];
        if (!rows.length) {
            deptSummary.innerHTML = '<span class="legacy-sync-dept-chip"><i class="fa-solid fa-building-user"></i>Departamentos cubiertos se calculan al guardar</span>';
            return;
        }
        deptSummary.innerHTML = rows.map(function (row) {
            return '<span class="legacy-sync-dept-chip">' +
                '<i class="fa-solid fa-building-user"></i>' +
                esc(row.nombre || 'Departamento') +
                '<small>' + esc(row.puestos_seleccionados || 0) + ' puesto(s)</small>' +
            '</span>';
        }).join('');
    }

    function actualizarSeleccionConfig(id, checked) {
        const set = new Set((legacySyncConfig.seleccion.puestos || []).map(String));
        if (checked) set.add(String(id || ''));
        else set.delete(String(id || ''));
        legacySyncConfig.seleccion.puestos = Array.from(set).filter(Boolean);
        legacySyncConfig.departamentosSincronizacion = calcularDepartamentosDesdeSeleccion();
        renderDepartamentosSincronizacion();
        actualizarResumenConfig();
    }

    function calcularDepartamentosDesdeSeleccion() {
        const puestosSel = new Set((legacySyncConfig.seleccion.puestos || []).map(String));
        const map = new Map();
        (legacySyncConfig.puestos || []).forEach(function (row) {
            const id = String(row.id || '');
            if (!puestosSel.has(id)) return;
            const depId = String(row.departamento_id || row.departamento || '');
            if (!map.has(depId)) {
                map.set(depId, {
                    id: row.departamento_id || depId,
                    nombre: row.departamento || 'Sin departamento',
                    puestos_seleccionados: 0
                });
            }
            const dep = map.get(depId);
            dep.puestos_seleccionados += 1;
        });
        return Array.from(map.values()).sort(function (a, b) {
            return String(a.nombre || '').localeCompare(String(b.nombre || ''));
        });
    }

    function renderConfigListas() {
        const filtro = String(configSearch && configSearch.value || '').trim().toLowerCase();
        const puestosSel = new Set((legacySyncConfig.seleccion.puestos || []).map(String));
        const puestos = (legacySyncConfig.puestos || []).filter(function (row) {
            const texto = [row.nombre, row.departamento].join(' ').toLowerCase();
            return !filtro || texto.indexOf(filtro) !== -1;
        });

        puestosList.innerHTML = puestos.length ? puestos.map(function (row) {
            const id = String(row.id || '');
            const equivalencia = row.legacy_nombre || row.legacy_clave
                ? 'Legacy: ' + (row.legacy_nombre || row.legacy_clave) + (row.legacy_clave ? ' · ' + row.legacy_clave : '')
                : 'Sin equivalencia Legacy';
            return '<label class="legacy-sync-check">' +
                '<input class="form-check-input mt-1" type="checkbox" data-legacy-sync-puesto value="' + esc(id) + '"' + (puestosSel.has(id) ? ' checked' : '') + '>' +
                '<span><strong>' + esc(row.nombre || 'Puesto') + '</strong><small>' + esc(row.departamento || 'Sin departamento') + '</small><small>' + esc(equivalencia) + '</small></span>' +
            '</label>';
        }).join('') : '<div class="p-3 text-muted">No hay puestos con ese filtro.</div>';

        actualizarResumenConfig();
    }

    function renderPersonalDetalle() {
        if (!personalDetail || !personalBtn) return;
        const row = legacySyncPersonal.seleccionado;
        personalBtn.disabled = !row;
        if (!row) {
            personalDetail.innerHTML = '<div class="text-muted fw-bold"><i class="fa-solid fa-hand-pointer me-1"></i>Selecciona un usuario para actualizarlo.</div>';
            return;
        }
        personalDetail.innerHTML = '' +
            '<div class="legacy-sync-user mb-3">' +
                '<strong>' + esc(row.nombre || 'Sin nombre') + '</strong>' +
                '<div class="legacy-sync-muted"># ' + esc(row.external_id || '-') + (row.correo ? ' · ' + esc(row.correo) : '') + '</div>' +
            '</div>' +
            '<div class="row g-2">' +
                '<div class="col-md-6"><div class="legacy-sync-muted">Puesto</div><div class="legacy-sync-role">' + esc(row.puesto || '-') + '</div></div>' +
                '<div class="col-md-6"><div class="legacy-sync-muted">Departamento</div><div class="legacy-sync-role">' + esc(row.departamento || '-') + '</div></div>' +
                '<div class="col-md-6"><div class="legacy-sync-muted">Rol Legacy</div><span class="legacy-sync-pill legacy-sync-pill-info">' + esc(row.role_legacy || '-') + '</span></div>' +
            '</div>';
    }

    function renderPersonalLista() {
        if (!personalList) return;
        const filtro = String(personalSearch && personalSearch.value || '').trim().toLowerCase();
        const rows = (legacySyncPersonal.usuarios || []).filter(function (row) {
            const texto = [row.nombre, row.external_id, row.puesto, row.departamento, row.correo].join(' ').toLowerCase();
            return !filtro || texto.indexOf(filtro) !== -1;
        });
        if (personalResumen) {
            personalResumen.innerHTML = '<i class="fa-solid fa-users me-1"></i>' + esc(rows.length) + ' usuario(s) disponibles dentro del alcance configurado.';
        }
        if (!rows.length) {
            personalList.innerHTML = '<div class="p-3 text-muted">No hay usuarios con ese filtro.</div>';
            return;
        }
        const seleccionadoId = legacySyncPersonal.seleccionado ? String(legacySyncPersonal.seleccionado.id_persona || '') : '';
        personalList.innerHTML = rows.map(function (row) {
            const id = String(row.id_persona || '');
            return '<button type="button" class="legacy-sync-user-option' + (id === seleccionadoId ? ' is-active' : '') + '" data-legacy-sync-personal-id="' + esc(id) + '">' +
                '<strong>' + esc(row.nombre || 'Sin nombre') + '</strong>' +
                '<small># ' + esc(row.external_id || '-') + ' · ' + esc(row.puesto || '-') + '</small>' +
                '<small>' + esc(row.departamento || '-') + '</small>' +
            '</button>';
        }).join('');
    }

    function seleccionarPersonal(idPersona) {
        const id = String(idPersona || '');
        legacySyncPersonal.seleccionado = (legacySyncPersonal.usuarios || []).find(function (row) {
            return String(row.id_persona || '') === id;
        }) || null;
        renderPersonalLista();
        renderPersonalDetalle();
    }

    async function cargarUsuariosPersonal() {
        if (!personalList) return;
        try {
            if (personalStatus) {
                personalStatus.className = 'badge bg-label-info text-dark';
                personalStatus.textContent = 'Cargando usuarios';
            }
            personalList.innerHTML = '<div class="p-3 text-muted">Cargando usuarios...</div>';
            const resp = await fetch('/CapHum/getUsuariosSincronizaLegacy', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const data = await resp.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
            legacySyncPersonal.usuarios = Array.isArray(data.usuarios) ? data.usuarios : [];
            legacySyncPersonal.seleccionado = null;
            if (personalStatus) {
                personalStatus.className = 'badge bg-label-success text-dark';
                personalStatus.textContent = esc(legacySyncPersonal.usuarios.length) + ' usuarios';
            }
            renderPersonalLista();
            renderPersonalDetalle();
        } catch (err) {
            if (personalStatus) {
                personalStatus.className = 'badge bg-label-danger text-dark';
                personalStatus.textContent = 'No se pudo cargar';
            }
            const mensaje = err && err.message ? err.message : 'No se pudieron cargar usuarios.';
            personalList.innerHTML = '<div class="p-3 text-danger">' + esc(mensaje) + '</div>';
        }
    }

    async function sincronizarUsuarioPersonal() {
        const row = legacySyncPersonal.seleccionado;
        if (!row) return;
        try {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Actualizando usuario',
                    html: progressHtml(0, 0, row.nombre || 'Usuario seleccionado'),
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });
            }
            const resp = await fetch('/CapHum/sincronizarLegacyUsuario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id_persona: row.id_persona })
            });
            const data = await resp.json();
            if (!resp.ok || !data || data.success === false) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo actualizar.');
            const meta = resultadoMeta(data.resultado || '');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: data.resultado === 'error' ? 'error' : 'success',
                    title: meta.label,
                    text: data.mensaje || 'Usuario procesado.'
                });
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: err.message || 'Error' });
            }
        }
    }

    async function cargarConfiguracionLegacy() {
        if (!puestosList) return;
        try {
            const resp = await fetch('/CapHum/getConfiguracionSincronizaLegacy', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const data = await resp.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
            legacySyncConfig = {
                configurado: !!data.configurado,
                puestos: data.catalogos && Array.isArray(data.catalogos.puestos) ? data.catalogos.puestos : [],
                departamentosSincronizacion: Array.isArray(data.departamentos_sincronizacion) ? data.departamentos_sincronizacion : [],
                seleccion: { puestos: data.seleccion && Array.isArray(data.seleccion.puestos) ? data.seleccion.puestos : [] }
            };
            if (configStatus) {
                configStatus.className = legacySyncConfig.configurado ? 'badge bg-label-success text-dark' : 'badge bg-label-warning text-dark';
                configStatus.textContent = legacySyncConfig.configurado ? 'Configuración activa' : 'Usando regla anterior';
            }
            renderDepartamentosSincronizacion();
            renderConfigListas();
        } catch (err) {
            if (configStatus) {
                configStatus.className = 'badge bg-label-danger text-dark';
                configStatus.textContent = 'No se pudo cargar';
            }
            puestosList.innerHTML = '<div class="p-3 text-danger">No se pudieron cargar puestos.</div>';
        }
    }

    async function guardarConfiguracionLegacy() {
        const payload = {
            puestos: legacySyncConfig.seleccion.puestos || []
        };
        try {
            const resp = await fetch('/CapHum/guardarConfiguracionSincronizaLegacy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo guardar.');
            legacySyncConfig.seleccion = data.seleccion || payload;
            legacySyncConfig.departamentosSincronizacion = Array.isArray(data.departamentos_sincronizacion) ? data.departamentos_sincronizacion : calcularDepartamentosDesdeSeleccion();
            legacySyncConfig.configurado = true;
            if (configStatus) {
                configStatus.className = 'badge bg-label-success text-dark';
                configStatus.textContent = 'Configuración activa';
            }
            renderDepartamentosSincronizacion();
            actualizarResumenConfig();
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Configuración guardada', text: data.mensaje || 'El alcance de sincronización quedó actualizado.' });
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: err.message || 'Error' });
        }
    }

    function progressHtml(actual, total, detalle) {
        const pct = total > 0 ? Math.round((actual / total) * 100) : 0;
        const contador = total > 0
            ? '<div class="small fw-bold mt-2">' + esc(actual) + ' de ' + esc(total) + '</div>'
            : '<div class="small fw-bold mt-2">Preparando lista de usuarios...</div>';
        return '' +
            '<div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Sincronizando...</span></div>' +
            '<p class="fw-bold mb-1">' + esc(detalle || 'Preparando sincronizacion...') + '</p>' +
            '<p class="small text-muted mb-3">No cierre esta ventana.</p>' +
            '<div class="progress" style="height:10px;border-radius:999px;overflow:hidden;">' +
                '<div class="progress-bar" role="progressbar" style="width:' + pct + '%;"></div>' +
            '</div>' +
            contador +
            '<div class="small text-muted mt-1" style="font-size:.68rem;">' + esc(LEGACY_SYNC_UI_VERSION) + '</div>';
    }

    function swalProgress(actual, total, detalle) {
        if (typeof Swal === 'undefined') return;
        Swal.update({ html: progressHtml(actual, total, detalle) });
    }

    function describirRespuestaInesperada(data) {
        if (data && Object.prototype.hasOwnProperty.call(data, 'total_no_leidas')) {
            return 'Se recibio la respuesta del modulo de notificaciones, no la de Sincroniza Legacy. Recarga la pagina e intenta de nuevo.';
        }
        return 'La respuesta no corresponde al endpoint de Sincroniza Legacy.';
    }

    async function postJson(payload, tipoEsperado) {
        const resp = await fetch('/CapHum/sincronizarLegacyPendientesRrhh', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
        let data = null;
        try {
            data = await resp.json();
        } catch (err) {
            throw new Error('El servidor no devolvio JSON valido para la sincronizacion Legacy.');
        }
        if (!resp.ok || !data || data.success === false) {
            throw new Error((data && (data.mensaje || data.error)) || 'No se pudo procesar la solicitud.');
        }
        if (tipoEsperado && data.tipo_respuesta !== tipoEsperado) {
            throw new Error(describirRespuestaInesperada(data));
        }
        return data;
    }

    function resumenBase(revisados, total) {
        return {
            revisados: revisados || 0,
            pendientes_detectados: total || 0,
            actualizados: 0,
            sin_cambios: 0,
            omitidos: 0,
            errores: 0,
            lotes: total || 0
        };
    }

    function acumularResumen(destino, fuente) {
        const f = fuente || {};
        destino.actualizados += parseInt(f.actualizados, 10) || 0;
        destino.sin_cambios += parseInt(f.sin_cambios, 10) || 0;
        destino.omitidos += parseInt(f.omitidos, 10) || 0;
        destino.errores += parseInt(f.errores, 10) || 0;
    }

    async function sincronizar() {
        btn.disabled = true;
        if (typeof Swal !== 'undefined') {
            Swal.close();
            Swal.fire({
                title: 'Buscando usuarios para sincronizar',
                html: progressHtml(0, 0, 'Consultando usuarios dentro del alcance configurado...'),
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
        }
        try {
            const plan = await postJson({ plan: true, forzar: true }, 'legacy_sync_plan');
            const pendientes = Array.isArray(plan.pendientes) ? plan.pendientes : [];
            const total = pendientes.length;
            const acumulado = resumenBase(plan.resumen && plan.resumen.revisados, total);
            const resultados = [];

            if (total === 0) {
                renderResumen(plan.resumen || acumulado);
                renderTabla([]);
                result.style.display = 'block';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sincronizacion terminada',
                        text: plan.mensaje || 'No se detectaron usuarios pendientes.'
                    });
                }
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.update({ title: 'Encontrados ' + total + ' usuarios' });
                swalProgress(0, total, 'Iniciando sincronizacion masiva por usuario.');
            }

            for (let i = 0; i < pendientes.length; i++) {
                const item = Object.assign({}, pendientes[i], { lote: i + 1 });
                const nombre = item.nombre ? (' · ' + item.nombre) : '';
                if (typeof Swal !== 'undefined') {
                    Swal.update({ title: 'Sincronizando ' + (i + 1) + ' de ' + total });
                }
                swalProgress(i + 1, total, 'Sincronizando ' + (i + 1) + ' de ' + total + nombre);
                try {
                    const parcial = await postJson({ pendientes: [item] }, 'legacy_sync_lote');
                    acumularResumen(acumulado, parcial.resumen || {});
                    if (Array.isArray(parcial.datos)) {
                        resultados.push.apply(resultados, parcial.datos);
                    }
                } catch (errItem) {
                    acumulado.errores += 1;
                    resultados.push(Object.assign({}, item, {
                        sync: {
                            resultado: 'error',
                            mensaje: errItem.message || 'No se pudo sincronizar este usuario.'
                        }
                    }));
                }
            }

            renderResumen(acumulado);
            renderTabla(resultados);
            result.style.display = 'block';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: acumulado.errores > 0 ? 'warning' : 'success',
                    title: acumulado.errores > 0 ? 'Sincronizacion con observaciones' : 'Sincronizacion terminada',
                    text: acumulado.errores > 0
                        ? ('Se terminaron los lotes con ' + acumulado.errores + ' error(es).')
                        : 'Todos los usuarios detectados fueron procesados.'
                });
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'No se pudo iniciar', text: err.message || 'No se pudo ejecutar la sincronizacion masiva.' });
            } else {
                window.alert('No se pudo ejecutar la sincronizacion masiva.');
            }
        } finally {
            btn.disabled = false;
        }
    }

    if (btn) {
        btn.addEventListener('click', sincronizar);
    }
    if (configSearch) {
        configSearch.addEventListener('input', renderConfigListas);
    }
    if (configSave) {
        configSave.addEventListener('click', guardarConfiguracionLegacy);
    }
    if (personalSearch) {
        personalSearch.addEventListener('input', renderPersonalLista);
    }
    if (personalRefresh) {
        personalRefresh.addEventListener('click', cargarUsuariosPersonal);
    }
    if (personalBtn) {
        personalBtn.addEventListener('click', sincronizarUsuarioPersonal);
    }
    document.addEventListener('change', function (ev) {
        if (ev.target && ev.target.matches('[data-legacy-sync-puesto]')) {
            actualizarSeleccionConfig(ev.target.value, ev.target.checked);
        }
    });
    document.addEventListener('click', function (ev) {
        const btnPersonal = ev.target && ev.target.closest ? ev.target.closest('[data-legacy-sync-personal-id]') : null;
        if (btnPersonal) {
            seleccionarPersonal(btnPersonal.getAttribute('data-legacy-sync-personal-id'));
        }
    });
    document.querySelectorAll('#legacy-sync-tabs [data-bs-toggle="tab"]').forEach(function (btnTab) {
        btnTab.addEventListener('shown.bs.tab', function () {
            guardarLegacySyncTabActivo(btnTab.getAttribute('data-bs-target') || '');
        });
    });
    restaurarLegacySyncTabActivo();
    cargarConfiguracionLegacy();
    cargarUsuariosPersonal();
})();
</script>
