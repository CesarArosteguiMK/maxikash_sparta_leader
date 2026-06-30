<?php
$puedeResetearTotpDocumentosSensiblesRrhh = in_array(152, array_map('intval', (array) ($_SESSION['modulos'] ?? [])), true);
?>
<div class="container-fluid py-4 ch-access-page">
    <style>
        .ch-access-page { color:#22303e; }
        .ch-access-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .ch-access-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .ch-access-title i { color:#26344e; }
        .ch-access-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; }
        .ch-access-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .ch-access-actions .btn { min-height:2.25rem; display:inline-flex; align-items:center; justify-content:center; gap:.4rem; font-weight:700; }
        .ch-access-summary { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .ch-access-kpi { border:1px solid #e2e8f0; border-radius:.65rem; background:#fff; padding:.85rem .95rem; min-height:5rem; }
        .ch-access-kpi-label { color:#64748b; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.025em; }
        .ch-access-kpi-value { color:#22303e; font-size:1.55rem; font-weight:900; line-height:1.1; margin-top:.25rem; }
        .ch-access-kpi-split { display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:.65rem; }
        .ch-access-kpi-split-item + .ch-access-kpi-split-item { position:relative; padding-left:.8rem; }
        .ch-access-kpi-split-item + .ch-access-kpi-split-item::before { content:''; position:absolute; left:0; top:.15rem; bottom:.15rem; width:1px; background:linear-gradient(180deg, transparent, #dbe4ef 18%, #dbe4ef 82%, transparent); }
        .ch-access-name-cell { display:flex; align-items:flex-start; gap:.85rem; min-width:280px; }
        .ch-access-avatar { width:46px; height:46px; min-width:46px; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow:0 5px 16px rgba(30,41,59,.16); background:#f1f5f9; }
        .ch-access-avatar-fallback { width:46px; height:46px; min-width:46px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.82rem; font-weight:700; border:2px solid #fff; box-shadow:0 5px 16px rgba(30,41,59,.16); background:linear-gradient(135deg,#24324d 0%,#7257d8 100%); }
        .ch-access-action-btn { width:2.1rem; height:2.1rem; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; }
        .sede-glass-badge { background:rgba(255,255,255,.7); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); border:1px solid rgba(0,0,0,.06); border-radius:6px; }
        .ch-access-module-list { display:flex; flex-wrap:wrap; gap:1rem; align-items:stretch; }
        .ch-access-module-group { flex:1 1 calc(50% - .5rem); max-width:calc(50% - .5rem); min-width:0; border:2px solid #000; border-radius:.5rem; overflow:hidden; background:#fff; }
        .ch-access-module-group.is-wide { flex-basis:100%; max-width:100%; }
        .ch-access-module-head { background:rgba(26,82,168,.08); border-bottom:2px solid #000; color:#1e293b; }
        .ch-access-module-columns { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0; }
        .ch-access-module-columns .table + .table { border-left:1px solid #e9ecef; }
        .ch-access-module-row { border-left:3px solid transparent; border-bottom:1px solid #e9ecef; transition:background-color .15s ease, border-left-color .15s ease; }
        .ch-access-module-row:hover { background:#f8fafc; border-left-color:#1a52a8; }
        .ch-access-module-icon { width:40px; height:40px; border-radius:10px; background:rgba(26,82,168,.12); border:1px solid rgba(26,82,168,.25); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .ch-access-module-icon i { color:#1a52a8; font-size:1rem; }
        .ch-salary-audit-grid { display:grid; grid-template-columns:repeat(5, minmax(0, 1fr)); gap:.65rem; margin-bottom:1rem; }
        .ch-salary-audit-kpi { border:1px solid #e2e8f0; border-radius:.6rem; background:#f8fafc; padding:.75rem; }
        .ch-salary-audit-kpi span { display:block; color:#64748b; font-size:.7rem; font-weight:900; text-transform:uppercase; }
        .ch-salary-audit-kpi strong { display:block; color:#22303e; font-size:1.35rem; font-weight:900; margin-top:.15rem; }
        .ch-salary-audit-badge { display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:.2rem .55rem; font-size:.72rem; font-weight:800; }
        .ch-salary-audit-badge.ok { background:#dcfce7; color:#166534; }
        .ch-salary-audit-badge.warn { background:#fff3cd; color:#9a6700; }
        .ch-salary-audit-badge.danger { background:#fee2e2; color:#991b1b; }
        .ch-access-modal .modal-content { border:0; box-shadow:0 10px 40px rgba(0,0,0,.12); overflow:hidden; }
        .ch-access-modal .modal-header { background:#fff; border-bottom:1px solid #e2e8f0; }
        .ch-access-modal .modal-content { height:82vh; max-height:82vh; display:flex; flex-direction:column; }
        .ch-access-modal .modal-header,
        .ch-access-modal .modal-footer { flex:0 0 auto; }
        .ch-access-modal .modal-body { flex:1 1 auto; min-height:0; display:flex; flex-direction:column; overflow:hidden; padding:0; }
        .ch-access-permission-tabs { border-bottom:1px solid #e2e8f0; padding:.85rem 1.5rem 0; gap:.25rem; flex:0 0 auto; }
        .ch-access-permission-tabs .nav-link { border:0; border-bottom:3px solid transparent; border-radius:0; color:#64748b; font-weight:800; padding:.7rem 1rem; }
        .ch-access-permission-tabs .nav-link.active { color:#26344e; background:transparent; border-bottom-color:#26344e; }
        .ch-access-permission-content { flex:1 1 auto; min-height:0; overflow:auto; padding:1.25rem 1.5rem; }
        .ch-access-permission-title { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .ch-access-permission-title h6 { font-size:.95rem; font-weight:800; color:#22303e; margin:0 0 .25rem; }
        .ch-access-permission-title small { color:#64748b; font-weight:600; }
        .ch-access-credential-card { border:1px solid #e2e8f0; border-radius:.65rem; background:#f8fafc; padding:.9rem; }
        @media (max-width: 767.98px) {
            .ch-access-head,
            .ch-access-actions { align-items:stretch; flex-direction:column; }
            .ch-access-summary { grid-template-columns:1fr; }
            .ch-access-actions .btn { width:100%; max-width:none; }
            .ch-access-module-group { flex:1 1 100%; max-width:100%; }
            .ch-access-module-columns { grid-template-columns:1fr; }
            .ch-access-module-columns .table + .table { border-left:0; border-top:1px solid #e9ecef; }
            .ch-salary-audit-grid { grid-template-columns:1fr 1fr; }
        }
    </style>

    <div class="ch-access-head">
        <div>
            <h1 class="ch-access-title"><i class="fa-solid fa-user-shield"></i><span>Accesos</span></h1>
            <p class="ch-access-subtitle">Usuarios y permisos exclusivos del modulo Capital Humano.</p>
        </div>
        <div class="ch-access-actions">
            <button class="btn btn-label-primary" type="button" data-ch-access-salary-audit title="Auditoria salarios">
                <i class="fa-solid fa-user-lock"></i><span>Auditoría salarios</span>
            </button>
            <button class="btn btn-label-secondary" type="button" data-ch-access-refresh title="Actualizar">
                <i class="fa-solid fa-rotate"></i><span>Actualizar</span>
            </button>
        </div>
    </div>

    <div class="ch-access-summary">
        <div class="ch-access-kpi">
            <div class="ch-access-kpi-split">
                <div class="ch-access-kpi-split-item">
                    <div class="ch-access-kpi-label">Activos</div>
                    <div class="ch-access-kpi-value" id="chAccessActivos">0</div>
                </div>
                <div class="ch-access-kpi-split-item">
                    <div class="ch-access-kpi-label">Inactivos</div>
                    <div class="ch-access-kpi-value" id="chAccessInactivos">0</div>
                </div>
            </div>
        </div>
        <div class="ch-access-kpi">
            <div class="ch-access-kpi-label">Con permisos CH</div>
            <div class="ch-access-kpi-value" id="chAccessConPermisos">0</div>
        </div>
        <div class="ch-access-kpi">
            <div class="ch-access-kpi-label">Sin permisos CH</div>
            <div class="ch-access-kpi-value" id="chAccessSinPermisos">0</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-address-book me-2"></i>Usuarios</h5>
                    <div class="text-muted small fw-semibold" id="chAccessInfo">Pendiente de cargar.</div>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table id="chAccessTabla" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Sede</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="chAccessRows">
                        <tr><td colspan="3" class="text-center text-muted fw-semibold py-4">Cargando usuarios...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade ch-access-modal" id="modalChAccessPermisos" tabindex="-1" aria-labelledby="chAccessPermisosTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="flex-grow-1">
                        <h5 class="modal-title fw-bold mb-1" id="chAccessPermisosTitle"><i class="fa-solid fa-user-shield me-2"></i>Accesos</h5>
                        <div class="text-muted small fw-semibold" id="chAccessPermisosSubtitle">Usuario y permisos</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="chAccessPersonaId">
                    <ul class="nav nav-tabs ch-access-permission-tabs" id="chAccessPermisosTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#chAccessTabCuenta" role="tab">
                                <i class="fa-solid fa-user-shield me-1"></i>Cuenta
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#chAccessTabModulos" role="tab">
                                <i class="fa-solid fa-users me-1"></i>Modulos Capital Humano
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#chAccessTabGestiones" role="tab">
                                <i class="fa-solid fa-users-gear me-1"></i>Gestiones De Personal
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#chAccessTabEspeciales" role="tab">
                                <i class="fa-solid fa-shield-halved me-1"></i>Permisos Especiales
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content ch-access-permission-content">
                        <div class="tab-pane fade show active" id="chAccessTabCuenta" role="tabpanel">
                            <div class="ch-access-credential-card">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold">Usuario</label>
                                        <input type="text" class="form-control" id="chAccessUsuario" readonly>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold">Contrasena</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="chAccessPassword" readonly>
                                            <button type="button" class="btn btn-outline-secondary" data-ch-access-toggle-password title="Mostrar/ocultar contrasena">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($puedeResetearTotpDocumentosSensiblesRrhh): ?>
                                <div class="mt-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top pt-3">
                                    <div>
                                        <div class="fw-bold text-heading">Google Authenticator</div>
                                        <small class="text-muted">Reinicia el segundo paso para documentos sensibles de este usuario.</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger" data-ch-access-reset-totp>
                                        <i class="fa-solid fa-rotate-left me-1"></i>Reiniciar autenticador
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="chAccessTabModulos" role="tabpanel">
                            <div class="ch-access-permission-title">
                                <div>
                                    <h6 class="mb-1 fw-bold">Modulos Capital Humano</h6>
                                    <small class="text-muted">Gestiona los accesos principales de Capital Humano.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-ch-access-select-panel="#chAccessModulosSistema">
                                    <i class="fa-solid fa-check-double me-1"></i>Seleccionar todos
                                </button>
                            </div>
                            <div class="ch-access-module-list" id="chAccessModulosSistema">
                                <div class="text-center text-muted fw-semibold py-4 w-100">Cargando permisos...</div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="chAccessTabGestiones" role="tabpanel">
                            <div class="ch-access-permission-title">
                                <div>
                                    <h6 class="mb-1 fw-bold">Gestiones De Personal</h6>
                                    <small class="text-muted">Gestiona permisos de procesos y validaciones de personal.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-ch-access-select-panel="#chAccessModulosGestiones">
                                    <i class="fa-solid fa-check-double me-1"></i>Seleccionar todos
                                </button>
                            </div>
                            <div class="ch-access-module-list" id="chAccessModulosGestiones">
                                <div class="text-center text-muted fw-semibold py-4 w-100">Cargando permisos...</div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="chAccessTabEspeciales" role="tabpanel">
                            <div class="ch-access-permission-title">
                                <div>
                                    <h6 class="mb-1 fw-bold">Permisos Especiales</h6>
                                    <small class="text-muted">Controla permisos finos y administracion de accesos.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-ch-access-select-panel="#chAccessModulosEspeciales">
                                    <i class="fa-solid fa-check-double me-1"></i>Seleccionar todos
                                </button>
                            </div>
                            <div class="ch-access-module-list" id="chAccessModulosEspeciales">
                                <div class="text-center text-muted fw-semibold py-4 w-100">Cargando permisos...</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" data-ch-access-save>
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                    </button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalChSalaryAudit" tabindex="-1" aria-labelledby="chSalaryAuditTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-1" id="chSalaryAuditTitle"><i class="fa-solid fa-user-lock me-2"></i>Auditoría de salarios</h5>
                        <div class="text-muted small fw-semibold">Permisos, consultas, guardados e intentos rechazados.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="ch-salary-audit-grid" id="chSalaryAuditKpis">
                        <div class="ch-salary-audit-kpi"><span>Con permiso</span><strong>0</strong></div>
                        <div class="ch-salary-audit-kpi"><span>Lecturas</span><strong>0</strong></div>
                        <div class="ch-salary-audit-kpi"><span>Guardados</span><strong>0</strong></div>
                        <div class="ch-salary-audit-kpi"><span>Denegados</span><strong>0</strong></div>
                        <div class="ch-salary-audit-kpi"><span>Eventos</span><strong>0</strong></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="card h-100">
                                <div class="card-header fw-bold"><i class="fa-solid fa-key me-1"></i>Usuarios con permiso de salario</div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>Usuario</th><th>Puesto</th><th>Estatus</th></tr></thead>
                                        <tbody id="chSalaryAuditUsuarios"><tr><td colspan="3" class="text-center text-muted py-3">Sin cargar</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card h-100">
                                <div class="card-header fw-bold"><i class="fa-solid fa-clock-rotate-left me-1"></i>Bitácora reciente</div>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Resultado</th><th>Empleado</th></tr></thead>
                                        <tbody id="chSalaryAuditEventos"><tr><td colspan="5" class="text-center text-muted py-3">Sin cargar</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-ch-access-salary-audit-refresh><i class="fa-solid fa-rotate me-1"></i>Actualizar</button>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const app = {
            usuarios: [],
            tabla: null,
            async getJson(url) {
                const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Front-Request': 'true' } });
                return res.json();
            },
            async postJson(url, body) {
                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Front-Request': 'true' },
                    body: JSON.stringify(body || {})
                });
                return res.json();
            },
            async cargar() {
                this.setLoading('Cargando usuarios...');
                try {
                    const res = await this.getJson('/caphum/getAccesosCapitalHumano');
                    if (!res.success) {
                        this.toast(res.mensaje || 'No se pudo cargar.', 'error');
                        this.setLoading('No se pudieron cargar usuarios.');
                        return;
                    }
                    const datos = res.datos || {};
                    this.usuarios = Array.isArray(datos.usuarios) ? datos.usuarios : [];
                    this.render(datos.totales || {});
                } catch (e) {
                    this.toast('No se pudo cargar Accesos.', 'error');
                    this.setLoading('Error al cargar usuarios.');
                }
            },
            setLoading(text) {
                this.destroyTable();
                const tbody = document.getElementById('chAccessRows');
                if (tbody) tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted fw-semibold py-4">${this.escape(text)}</td></tr>`;
            },
            render(totales) {
                this.destroyTable();
                document.getElementById('chAccessActivos').textContent = Number(totales.activos || 0);
                document.getElementById('chAccessInactivos').textContent = Number(totales.inactivos || 0);
                document.getElementById('chAccessConPermisos').textContent = Number(totales.con_permisos_ch || 0);
                document.getElementById('chAccessSinPermisos').textContent = Number(totales.sin_permisos_ch || 0);
                const info = document.getElementById('chAccessInfo');
                if (info) info.textContent = `${this.usuarios.length} usuarios visibles.`;
                const tbody = document.getElementById('chAccessRows');
                if (!tbody) return;
                if (!this.usuarios.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted fw-semibold py-4">Sin usuarios para mostrar.</td></tr>';
                    return;
                }
                tbody.innerHTML = this.usuarios.map((u) => {
                    return `
                        <tr>
                            <td>
                                <div class="ch-access-name-cell">
                                    ${this.avatarHtml(u)}
                                    <div>
                                        <div class="fw-bold text-heading">${this.escape(u.nombre || 'Sin nombre')}</div>
                                        <div class="text-muted small">#${this.escape(u.numero_empleado || u.persona_id || '')} ${this.escape(u.correo || '')}</div>
                                        <div class="text-muted small"><i class="fa-solid fa-user me-1"></i>${this.escape(u.user_name || 'Sin usuario')}</div>
                                        <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i>${this.escape(this.formatoTelefono(u.telefono || 'Sin telefono'))}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 sede-glass-badge" title="${this.escape(u.pais || 'Mexico')}">
                                    <span class="text-muted fw-semibold" style="font-size:.75rem;">Sede:</span>
                                    <span class="${this.escape(this.flagPais(u.codigo_iso_pais || u.pais))}" style="font-size:1.1rem; border-radius:2px; box-shadow:0 1px 3px rgba(0,0,0,.15);"></span>
                                </small>
                                <small class="text-muted d-flex flex-column gap-1">
                                    <span class="d-inline-flex align-items-center gap-1 mt-2">
                                        <i class="fa fa-building"></i>
                                        Departamento: <strong class="ms-1">${this.escape(u.departamento || u.area || 'Sin departamento')}</strong>
                                    </span>
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="fa fa-briefcase"></i>
                                        Puesto: <strong class="ms-1">${this.escape(u.puesto || 'Sin puesto')}</strong>
                                    </span>
                                </small>
                                <hr class="my-2">
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${this.escape(u.jefe_nombre || 'Sin jefe')}</strong>
                                </small>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary ch-access-action-btn" data-ch-access-config="${Number(u.persona_id || 0)}" title="Configurar permisos Capital Humano" aria-label="Configurar permisos Capital Humano">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
                this.initTable();
            },
            avatarHtml(usuario) {
                const foto = String(usuario.foto_perfil || '').trim();
                const iniciales = this.iniciales(usuario.nombre || 'US');
                if (foto) {
                    return `<img class="ch-access-avatar" src="${this.escape(foto)}" alt="" loading="lazy" decoding="async" onerror="this.nextElementSibling.classList.remove('d-none');this.replaceWith(this.nextElementSibling);"><span class="ch-access-avatar-fallback d-none">${this.escape(iniciales)}</span>`;
                }
                return `<span class="ch-access-avatar-fallback">${this.escape(iniciales)}</span>`;
            },
            async abrirPermisos(idPersona) {
                const id = Number(idPersona || 0);
                if (!id) return;
                const previo = this.usuarios.find(u => Number(u.persona_id || 0) === id) || {};
                document.getElementById('chAccessPersonaId').value = id;
                document.getElementById('chAccessPermisosTitle').innerHTML = `<i class="fa-solid fa-user-shield me-2"></i>${this.escape(previo.nombre || 'Usuario')}`;
                document.getElementById('chAccessPermisosSubtitle').textContent = `Empleado #${previo.numero_empleado || id} | ${previo.puesto || 'Sin puesto'}`;
                document.getElementById('chAccessUsuario').value = previo.user_name || 'Cargando...';
                document.getElementById('chAccessPassword').value = '';
                document.getElementById('chAccessPassword').type = 'password';
                this.activarTabCuenta();
                this.setModulosLoading('Cargando permisos...');
                let loader = false;
                if (typeof showWait === 'function') {
                    showWait();
                    loader = true;
                }
                try {
                    const res = await this.getJson(`/caphum/getAccesoCapitalHumanoDetalle?id_persona=${encodeURIComponent(id)}`);
                    if (!res.success) {
                        this.toast(res.mensaje || 'No se pudo cargar el detalle.', 'error');
                        return;
                    }
                    const datos = res.datos || {};
                    const usuario = datos.usuario || previo;
                    document.getElementById('chAccessPermisosTitle').innerHTML = `<i class="fa-solid fa-user-shield me-2"></i>${this.escape(usuario.nombre || 'Usuario')}`;
                    document.getElementById('chAccessPermisosSubtitle').textContent = `Empleado #${usuario.numero_empleado || id} | ${usuario.puesto || 'Sin puesto'}`;
                    document.getElementById('chAccessUsuario').value = usuario.user_name || 'Sin usuario';
                    document.getElementById('chAccessPassword').value = usuario.password || 'Sin contrasena registrada';
                    document.getElementById('chAccessPassword').type = 'password';
                    this.renderModulos(datos.modulos || []);
                    this.mostrarModal(document.getElementById('modalChAccessPermisos'));
                } catch (e) {
                    this.toast('No se pudo cargar el detalle.', 'error');
                } finally {
                    if (loader && typeof Swal !== 'undefined') Swal.close();
                }
            },
            setModulosLoading(text) {
                const html = `<div class="text-center text-muted fw-semibold py-4 w-100">${this.escape(text)}</div>`;
                ['chAccessModulosSistema', 'chAccessModulosGestiones', 'chAccessModulosEspeciales'].forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) el.innerHTML = html;
                });
            },
            renderModulos(modulos) {
                const sistema = document.getElementById('chAccessModulosSistema');
                const gestiones = document.getElementById('chAccessModulosGestiones');
                const especiales = document.getElementById('chAccessModulosEspeciales');
                if (!sistema || !gestiones || !especiales) return;
                if (!Array.isArray(modulos) || !modulos.length) {
                    this.setModulosLoading('No hay modulos Capital Humano configurados.');
                    return;
                }

                const porGrupo = (lista) => {
                    const grupos = new Map();
                    lista.forEach((m) => {
                        const g = m.grupo_ch || 'Capital Humano';
                        if (!grupos.has(g)) {
                            grupos.set(g, {
                                icono: m.grupo_icono || 'fa-solid fa-folder',
                                orden: Number(m.grupo_orden || 999),
                                items: []
                            });
                        }
                        grupos.get(g).items.push(m);
                    });
                    return Array.from(grupos.entries()).sort((a, b) => a[1].orden - b[1].orden);
                };

                const grupoDe = (m) => this.norm(m.grupo_ch || '');
                const modulosSistema = modulos.filter((m) => ['modulos capital humano', 'seleccion de personal'].includes(grupoDe(m)));
                const modulosGestiones = modulos.filter((m) => grupoDe(m) === 'gestiones de personal');
                const modulosEspeciales = modulos.filter((m) => !modulosSistema.includes(m) && !modulosGestiones.includes(m));

                sistema.innerHTML = this.renderListaGrupos(porGrupo(modulosSistema), 'No hay modulos Capital Humano.', 'sistema');
                gestiones.innerHTML = this.renderListaGrupos(porGrupo(modulosGestiones), 'No hay gestiones de personal.', 'gestiones');
                especiales.innerHTML = this.renderListaGrupos(porGrupo(modulosEspeciales), 'No hay permisos especiales.', 'especiales');
                this.actualizarMasters();
            },
            renderListaGrupos(grupos, emptyText, prefijo) {
                if (!grupos.length) {
                    return `<div class="text-center text-muted fw-semibold py-4 w-100">${this.escape(emptyText)}</div>`;
                }
                return grupos.map(([grupo, data], idx) => this.renderGrupo(grupo, data, `${prefijo}-${idx}`)).join('');
            },
            renderGrupo(grupo, data, groupKey) {
                const groupId = `ch-access-grupo-${groupKey}`;
                const items = data.items || [];
                const allChecked = items.length > 0 && items.every(m => Number(m.asignado || 0) === 1);
                const columnas = this.columnas(items);
                return `
                    <section class="ch-access-module-group${columnas.length > 1 ? ' is-wide' : ''}">
                        <div class="ch-access-module-head px-3 py-2 d-flex align-items-center flex-wrap gap-2 fw-semibold">
                            <i class="${this.escape(data.icono || 'fa-solid fa-folder')} me-2 text-primary"></i>
                            <span class="flex-grow-1 min-w-0">${this.escape(grupo)} (${items.length})</span>
                            <label class="form-check-label small text-secondary mb-0" for="${groupId}-master">Marcar todo</label>
                            <input type="checkbox" class="form-check-input" id="${groupId}-master" data-ch-access-master="${this.escape(groupId)}"${allChecked ? ' checked' : ''}>
                        </div>
                        <div class="${columnas.length > 1 ? 'ch-access-module-columns' : ''}">
                            ${columnas.map(col => `
                                <table class="table table-hover mb-0" style="font-size:.9rem;">
                                    <tbody>${col.map(m => this.renderModuloFila(m, groupId, grupo)).join('')}</tbody>
                                </table>
                            `).join('')}
                        </div>
                    </section>
                `;
            },
            renderModuloFila(modulo, groupId, grupo) {
                const checked = Number(modulo.asignado || 0) === 1;
                const id = Number(modulo.id || 0);
                const nombre = modulo.nombre || 'Modulo';
                const descripcion = modulo.descripcion || nombre;
                return `
                    <tr class="ch-access-module-row">
                        <td style="padding:.875rem .875rem .875rem 1.25rem; vertical-align:middle;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="ch-access-module-icon"><i class="${this.escape(this.iconoModulo(modulo))}"></i></div>
                                <div>
                                    <div class="fw-semibold text-heading">${this.escape(nombre)}</div>
                                    <small class="text-muted">${this.escape(grupo)} &gt; ${this.escape(descripcion)}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end" style="padding:.875rem; vertical-align:middle; width:130px;">
                            <label class="form-check mb-0 d-inline-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input" value="${id}" data-ch-access-module data-ch-access-group="${this.escape(groupId)}"${checked ? ' checked' : ''}>
                                <span class="badge ${checked ? 'bg-primary' : 'bg-secondary'} rounded-pill px-3 py-1" data-ch-access-badge>${checked ? 'Asignado' : 'Asignar'}</span>
                            </label>
                        </td>
                    </tr>
                `;
            },
            columnas(items) {
                if (!Array.isArray(items) || items.length <= 5) return [items || []];
                const corte = Math.ceil(items.length / 2);
                return [items.slice(0, corte), items.slice(corte)];
            },
            toggleMaster(master) {
                const groupId = master.getAttribute('data-ch-access-master') || '';
                document.querySelectorAll(`[data-ch-access-group="${this.selectorEscape(groupId)}"]`).forEach((input) => {
                    input.checked = master.checked;
                    this.actualizarBadge(input);
                });
                this.actualizarMasters();
            },
            actualizarBadge(input) {
                const row = input.closest('.ch-access-module-row');
                const badge = row ? row.querySelector('[data-ch-access-badge]') : null;
                if (!badge) return;
                badge.textContent = input.checked ? 'Asignado' : 'Asignar';
                badge.classList.toggle('bg-primary', input.checked);
                badge.classList.toggle('bg-secondary', !input.checked);
            },
            actualizarMasters() {
                document.querySelectorAll('[data-ch-access-master]').forEach((master) => {
                    const groupId = master.getAttribute('data-ch-access-master') || '';
                    const items = Array.from(document.querySelectorAll(`[data-ch-access-group="${this.selectorEscape(groupId)}"]`));
                    master.checked = items.length > 0 && items.every(i => i.checked);
                    master.indeterminate = items.some(i => i.checked) && !master.checked;
                });
            },
            toggleTodos() {
                const checks = Array.from(document.querySelectorAll('[data-ch-access-module]'));
                const all = checks.length > 0 && checks.every(cb => cb.checked);
                checks.forEach(cb => {
                    cb.checked = !all;
                    this.actualizarBadge(cb);
                });
                this.actualizarMasters();
            },
            togglePanel(selector) {
                const panel = selector ? document.querySelector(selector) : null;
                if (!panel) return;
                const checks = Array.from(panel.querySelectorAll('[data-ch-access-module]'));
                const all = checks.length > 0 && checks.every(cb => cb.checked);
                checks.forEach(cb => {
                    cb.checked = !all;
                    this.actualizarBadge(cb);
                });
                this.actualizarMasters();
            },
            togglePassword() {
                const input = document.getElementById('chAccessPassword');
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            },
            activarTabCuenta() {
                const btn = document.querySelector('[data-bs-target="#chAccessTabCuenta"]');
                if (!btn) return;
                if (window.bootstrap && bootstrap.Tab) bootstrap.Tab.getOrCreateInstance(btn).show();
                else btn.click();
            },
            async guardar() {
                const id = Number(document.getElementById('chAccessPersonaId')?.value || 0);
                if (!id) return;
                const modulos = Array.from(document.querySelectorAll('[data-ch-access-module]:checked')).map(cb => Number(cb.value || 0)).filter(Boolean);
                try {
                    const res = await this.postJson('/caphum/guardarPermisosAccesoCapitalHumano', { id_persona: id, modulos });
                    this.toast(res.mensaje || 'Permisos guardados.', res.success ? 'success' : 'warning');
                    if (res.success) {
                        this.ocultarModal(document.getElementById('modalChAccessPermisos'));
                        await this.cargar();
                    }
                } catch (e) {
                    this.toast('No se pudieron guardar permisos.', 'error');
                }
            },
            async resetTotp() {
                const id = Number(document.getElementById('chAccessPersonaId')?.value || 0);
                if (!id) return;
                if (typeof Swal !== 'undefined') {
                    const ok = await Swal.fire({
                        icon: 'warning',
                        title: 'Reiniciar Google Authenticator',
                        text: 'El usuario tendra que escanear un nuevo QR la proxima vez que abra un documento sensible.',
                        showCancelButton: true,
                        confirmButtonText: 'Reiniciar',
                        cancelButtonText: 'Cancelar'
                    });
                    if (!ok.isConfirmed) return;
                } else if (!confirm('Reiniciar Google Authenticator de este usuario?')) {
                    return;
                }

                const body = new URLSearchParams();
                body.append('id_persona', String(id));
                try {
                    const res = await fetch('/caphum/resetTotpDocumentoSensiblePersona', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body
                    });
                    const json = await res.json();
                    this.toast(json.mensaje || (json.success ? 'Autenticador reiniciado.' : 'No se pudo reiniciar.'), json.success ? 'success' : 'warning');
                } catch (e) {
                    this.toast('No se pudo reiniciar Google Authenticator.', 'error');
                }
            },
            async abrirAuditoriaSalarios() {
                this.mostrarModal(document.getElementById('modalChSalaryAudit'));
                await this.cargarAuditoriaSalarios();
            },
            async cargarAuditoriaSalarios() {
                this.renderAuditoriaSalariosLoading();
                try {
                    const res = await this.getJson('/caphum/getAuditoriaSalariosRrhh');
                    if (!res.success) {
                        this.toast(res.mensaje || 'No se pudo cargar auditoria de salarios.', 'error');
                        this.renderAuditoriaSalarios({ usuarios_con_permiso: [], eventos: [], totales: {} });
                        return;
                    }
                    this.renderAuditoriaSalarios(res.datos || {});
                } catch (e) {
                    this.toast('No se pudo cargar auditoria de salarios.', 'error');
                    this.renderAuditoriaSalarios({ usuarios_con_permiso: [], eventos: [], totales: {} });
                }
            },
            renderAuditoriaSalariosLoading() {
                const usuarios = document.getElementById('chSalaryAuditUsuarios');
                const eventos = document.getElementById('chSalaryAuditEventos');
                if (usuarios) usuarios.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Cargando usuarios...</td></tr>';
                if (eventos) eventos.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Cargando bitacora...</td></tr>';
            },
            renderAuditoriaSalarios(datos) {
                const totales = datos.totales || {};
                const kpis = Array.from(document.querySelectorAll('#chSalaryAuditKpis strong'));
                [
                    totales.usuarios_con_permiso,
                    totales.lecturas,
                    totales.guardados,
                    totales.denegados,
                    totales.eventos
                ].forEach((value, index) => {
                    if (kpis[index]) kpis[index].textContent = Number(value || 0);
                });

                const usuarios = Array.isArray(datos.usuarios_con_permiso) ? datos.usuarios_con_permiso : [];
                const tbodyUsuarios = document.getElementById('chSalaryAuditUsuarios');
                if (tbodyUsuarios) {
                    tbodyUsuarios.innerHTML = usuarios.length ? usuarios.map(u => `
                        <tr>
                            <td>
                                <div class="fw-semibold">${this.escape(u.nombre || 'Sin nombre')}</div>
                                <div class="small text-muted">${this.escape(u.user_name || u.correo || '')}</div>
                            </td>
                            <td>${this.escape(u.puesto || u.departamento || 'Sin puesto')}</td>
                            <td><span class="ch-salary-audit-badge ${this.norm(u.estatus) === 'activo' ? 'ok' : 'warn'}">${this.escape(u.estatus || 'Sin estatus')}</span></td>
                        </tr>
                    `).join('') : '<tr><td colspan="3" class="text-center text-muted py-3">Nadie tiene permiso de salario.</td></tr>';
                }

                const eventos = Array.isArray(datos.eventos) ? datos.eventos : [];
                const tbodyEventos = document.getElementById('chSalaryAuditEventos');
                if (tbodyEventos) {
                    tbodyEventos.innerHTML = eventos.length ? eventos.map(e => {
                        const resultado = this.norm(e.resultado || '');
                        const clase = resultado === 'autorizado' ? 'ok' : (resultado === 'denegado' || resultado === 'fallido' ? 'danger' : 'warn');
                        return `
                            <tr>
                                <td class="text-nowrap">${this.escape(e.fecha_hora || '')}</td>
                                <td>${this.escape(e.usuario_nombre || ('Usuario #' + (e.id_usuario || '')))}</td>
                                <td>${this.escape(e.accion || '')}</td>
                                <td><span class="ch-salary-audit-badge ${clase}">${this.escape(e.resultado || '')}</span></td>
                                <td>${this.escape(e.persona_nombre || ('Persona #' + (e.id_persona || '')))}</td>
                            </tr>
                        `;
                    }).join('') : '<tr><td colspan="5" class="text-center text-muted py-3">Aun no hay eventos de salario.</td></tr>';
                }
            },
            initTable() {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
                if (typeof window.configuraTabla === 'function') {
                    window.configuraTabla('#chAccessTabla', {
                        registrosPorPagina: 10,
                        order: [],
                        columns: Array.from({ length: 3 }, () => ({ data: null, defaultContent: '' }))
                    });
                    return;
                }
                jQuery('#chAccessTabla').DataTable({
                    pageLength: 10,
                    order: [],
                    autoWidth: false,
                    language: {
                        emptyTable: 'Sin usuarios para mostrar',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        search: 'Buscar:',
                        zeroRecords: 'No se encontraron resultados',
                        paginate: { first: '<<', last: '>>', next: '>', previous: '<' }
                    }
                });
            },
            destroyTable() {
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#chAccessTabla')) {
                    jQuery('#chAccessTabla').DataTable().destroy();
                }
            },
            mostrarModal(modalEl) {
                if (!modalEl) return;
                if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(modalEl).show();
                else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') jQuery(modalEl).modal('show');
            },
            ocultarModal(modalEl) {
                if (!modalEl) return;
                if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') jQuery(modalEl).modal('hide');
            },
            iconoModulo(modulo) {
                const texto = this.norm(`${modulo.nombre || ''} ${modulo.grupo_ch || ''}`);
                if (texto.includes('vacacion')) return 'fa-solid fa-umbrella-beach';
                if (texto.includes('document')) return 'fa-solid fa-folder-open';
                if (texto.includes('baja')) return 'fa-solid fa-user-minus';
                if (texto.includes('candidato') || texto.includes('seleccion')) return 'fa-solid fa-user-check';
                if (texto.includes('estad')) return 'fa-solid fa-chart-pie';
                if (texto.includes('acceso') || texto.includes('permiso')) return 'fa-solid fa-user-shield';
                if (texto.includes('edicion')) return 'fa-solid fa-pen-to-square';
                return 'fa fa-cube';
            },
            iniciales(nombre) {
                const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
                return (partes.length ? partes.slice(0, 2).map(p => p.charAt(0)).join('') : 'US').toUpperCase();
            },
            formatoTelefono(value) {
                const raw = String(value || '').trim();
                const digits = raw.replace(/\D+/g, '');
                if (digits.length === 10) return `${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6)}`;
                return raw || 'Sin telefono';
            },
            selectorEscape(value) {
                if (window.CSS && typeof CSS.escape === 'function') return CSS.escape(value);
                return String(value || '').replace(/["\\]/g, '\\$&');
            },
            norm(value) {
                return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
            },
            flagPais(value) {
                const pais = this.norm(value || 'mx');
                if (pais === 'co' || pais.includes('colombia')) return 'fi fi-co fis';
                if (pais === 'us' || pais === 'usa' || pais.includes('estados unidos') || pais.includes('united states')) return 'fi fi-us fis';
                return 'fi fi-mx fis';
            },
            escape(value) {
                return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
            },
            toast(msg, icon) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: icon || 'info', text: msg, timer: 1800, showConfirmButton: false });
                } else {
                    alert(msg);
                }
            },
            bind() {
                document.addEventListener('click', (ev) => {
                    const cfg = ev.target.closest('[data-ch-access-config]');
                    if (cfg) {
                        this.abrirPermisos(cfg.getAttribute('data-ch-access-config'));
                        return;
                    }
                    const refresh = ev.target.closest('[data-ch-access-refresh]');
                    if (refresh) {
                        this.cargar();
                        return;
                    }
                    const salaryAudit = ev.target.closest('[data-ch-access-salary-audit]');
                    if (salaryAudit) {
                        this.abrirAuditoriaSalarios();
                        return;
                    }
                    const salaryAuditRefresh = ev.target.closest('[data-ch-access-salary-audit-refresh]');
                    if (salaryAuditRefresh) {
                        this.cargarAuditoriaSalarios();
                        return;
                    }
                    const save = ev.target.closest('[data-ch-access-save]');
                    if (save) {
                        this.guardar();
                        return;
                    }
                    const togglePassword = ev.target.closest('[data-ch-access-toggle-password]');
                    if (togglePassword) {
                        this.togglePassword();
                        return;
                    }
                    const resetTotp = ev.target.closest('[data-ch-access-reset-totp]');
                    if (resetTotp) {
                        this.resetTotp();
                        return;
                    }
                    const toggleAll = ev.target.closest('[data-ch-access-toggle-all]');
                    if (toggleAll) {
                        this.toggleTodos();
                        return;
                    }
                    const togglePanel = ev.target.closest('[data-ch-access-select-panel]');
                    if (togglePanel) {
                        this.togglePanel(togglePanel.getAttribute('data-ch-access-select-panel') || '');
                    }
                });
                document.addEventListener('change', (ev) => {
                    const master = ev.target.closest('[data-ch-access-master]');
                    if (master) {
                        this.toggleMaster(master);
                        return;
                    }
                    const item = ev.target.closest('[data-ch-access-module]');
                    if (item) {
                        this.actualizarBadge(item);
                        this.actualizarMasters();
                    }
                });
            }
        };
        app.bind();
        app.cargar();
    })();
    </script>
</div>
