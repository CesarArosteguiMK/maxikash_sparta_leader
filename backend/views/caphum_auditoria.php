<?php
$puedeResetearTotpDocumentosSensiblesRrhh = in_array(152, array_map('intval', (array) ($_SESSION['modulos'] ?? [])), true);
?>
<div class="container-fluid py-4 ch-audit-page">
    <style>
        .ch-audit-page { color:#22303e; }
        .ch-audit-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .ch-audit-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:900; margin:0; }
        .ch-audit-title i { color:#26344e; }
        .ch-audit-subtitle { color:#64748b; font-size:.88rem; font-weight:700; margin:.2rem 0 0; }
        .ch-audit-actions { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
        .ch-audit-actions .btn { min-height:2.25rem; display:inline-flex; align-items:center; justify-content:center; gap:.45rem; font-weight:800; }
        .ch-audit-mode-tabs { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; margin:0 0 1rem; }
        .ch-audit-mode-tab { border:1px solid #cbd5e1; background:#fff; color:#26344e; border-radius:.55rem; min-height:2.45rem; padding:.45rem .9rem; font-weight:900; display:inline-flex; align-items:center; gap:.45rem; }
        .ch-audit-mode-tab.active { background:#26344e; color:#fff; border-color:#26344e; box-shadow:0 6px 14px rgba(38,52,78,.18); }
        .ch-audit-section { display:none; }
        .ch-audit-section.active { display:block; }
        .ch-audit-kpis { display:grid; grid-template-columns:repeat(5, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .ch-audit-kpi { border:1px solid #e2e8f0; border-radius:.55rem; background:#fff; padding:.85rem .95rem; min-height:5.2rem; }
        .ch-audit-kpi span { color:#64748b; display:block; font-size:.7rem; font-weight:900; letter-spacing:.025em; text-transform:uppercase; }
        .ch-audit-kpi strong { color:#22303e; display:block; font-size:1.55rem; font-weight:900; line-height:1.1; margin-top:.25rem; }
        .ch-audit-layout { display:grid; grid-template-columns:minmax(0, 1.1fr) minmax(320px, .9fr); gap:1rem; margin-bottom:1rem; }
        .ch-audit-panel { background:#fff; border:1px solid #e2e8f0; border-radius:.65rem; box-shadow:0 5px 18px rgba(30,41,59,.06); overflow:hidden; }
        .ch-audit-panel-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; padding:.95rem 1rem; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
        .ch-audit-panel-title { display:flex; align-items:center; gap:.45rem; font-size:.98rem; font-weight:900; margin:0; color:#22303e; }
        .ch-audit-panel-body { padding:1rem; }
        .ch-audit-tabs { display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; }
        .ch-audit-tab { border:1px solid #cbd5e1; background:#fff; color:#26344e; border-radius:999px; padding:.35rem .7rem; font-size:.78rem; font-weight:900; cursor:pointer; }
        .ch-audit-tab.active { background:#26344e; color:#fff; border-color:#26344e; }
        .ch-audit-table { width:100%; border-collapse:separate; border-spacing:0; }
        .ch-audit-table th { color:#64748b; font-size:.72rem; font-weight:900; text-transform:uppercase; border-bottom:1px solid #e2e8f0; padding:.65rem .7rem; white-space:nowrap; }
        .ch-audit-table td { border-bottom:1px solid #eef2f7; padding:.7rem; vertical-align:top; font-size:.86rem; }
        .ch-audit-table tr:last-child td { border-bottom:0; }
        .ch-audit-user { min-width:210px; }
        .ch-audit-user strong { display:block; color:#22303e; font-size:.88rem; font-weight:900; }
        .ch-audit-user small { color:#64748b; display:block; font-weight:700; margin-top:.15rem; }
        .ch-audit-document { min-width:220px; }
        .ch-audit-document strong { display:block; color:#22303e; font-size:.86rem; font-weight:900; }
        .ch-audit-document small { color:#64748b; display:block; font-weight:700; margin-top:.16rem; word-break:break-word; }
        .ch-audit-doc-note { border-radius:999px; display:inline-flex; align-items:center; gap:.3rem; font-size:.68rem; font-weight:900; padding:.18rem .5rem; margin-top:.3rem; }
        .ch-audit-doc-note.protected { background:#eef2ff; color:#3730a3; }
        .ch-audit-doc-note.regular { background:#ecfeff; color:#155e75; }
        .ch-audit-doc-note.salary { background:#fff7ed; color:#9a3412; }
        .ch-audit-badge { border-radius:999px; display:inline-flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:900; padding:.22rem .55rem; white-space:nowrap; }
        .ch-audit-badge.ok { background:#dcfce7; color:#166534; }
        .ch-audit-badge.warn { background:#fff7ed; color:#9a3412; }
        .ch-audit-badge.danger { background:#fee2e2; color:#991b1b; }
        .ch-audit-badge.neutral { background:#e2e8f0; color:#334155; }
        .ch-audit-action { border-radius:999px; display:inline-flex; align-items:center; gap:.35rem; font-size:.72rem; font-weight:900; padding:.28rem .58rem; white-space:nowrap; }
        .ch-audit-action.view { background:#e0f2fe; color:#075985; }
        .ch-audit-action.download { background:#dcfce7; color:#166534; }
        .ch-audit-action.delete { background:#fee2e2; color:#991b1b; }
        .ch-audit-action.upload { background:#ede9fe; color:#5b21b6; }
        .ch-audit-action.auth { background:#fef3c7; color:#92400e; }
        .ch-audit-action.neutral { background:#e2e8f0; color:#334155; }
        .ch-audit-filters { display:grid; grid-template-columns:170px 170px minmax(180px, 1fr); gap:.65rem; align-items:center; }
        .ch-audit-empty { color:#64748b; font-weight:700; padding:1.2rem; text-align:center; }
        .ch-audit-scroll { max-height:420px; overflow:auto; }
        .ch-audit-activity-scroll { max-height:560px; overflow:auto; }
        .ch-ia-kpis { display:grid; grid-template-columns:repeat(5, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .ch-ia-kpi { border:1px solid #e2e8f0; border-radius:.55rem; background:#fff; padding:.85rem .95rem; min-height:5.2rem; }
        .ch-ia-kpi span { color:#64748b; display:block; font-size:.7rem; font-weight:900; letter-spacing:.025em; text-transform:uppercase; }
        .ch-ia-kpi strong { color:#22303e; display:block; font-size:1.55rem; font-weight:900; line-height:1.1; margin-top:.25rem; }
        .ch-ia-filters { display:grid; grid-template-columns:170px 190px minmax(220px, 1fr); gap:.65rem; align-items:center; min-width:min(100%, 680px); }
        .ch-ia-table th { font-size:.68rem; font-weight:700; }
        .ch-ia-table td { font-size:.8rem; line-height:1.32; padding:.62rem .7rem; }
        .ch-ia-table .ch-audit-user { min-width:180px; }
        .ch-ia-table .ch-audit-user strong { font-size:.78rem; font-weight:600; line-height:1.24; color:#334155; }
        .ch-ia-table .ch-audit-user small { font-size:.72rem; font-weight:500; color:#718096; }
        .ch-ia-table .ch-audit-action { font-size:.67rem; font-weight:600; padding:.18rem .48rem; }
        .ch-ia-date { color:#334155; font-weight:500; white-space:nowrap; }
        .ch-ia-module, .ch-ia-summary { color:#334155; font-weight:500; min-width:150px; }
        .ch-ia-changes { min-width:280px; max-width:440px; }
        .ch-ia-change-list { display:flex; flex-direction:column; gap:.22rem; }
        .ch-ia-change { border:0; border-left:2px solid #dbe4ef; border-radius:0; background:transparent; padding:.16rem 0 .18rem .5rem; }
        .ch-ia-change strong { color:#334155; display:inline; font-size:.76rem; font-weight:700; margin:0 .25rem 0 0; }
        .ch-ia-change span { color:#64748b; display:inline; font-size:.74rem; font-weight:500; word-break:break-word; }
        .ch-ia-change span + span::before { content:" | "; color:#cbd5e1; margin:0 .2rem; }
        .ch-ia-more { color:#718096; font-size:.74rem; font-weight:600; display:block; padding-left:.5rem; }
        @media (max-width: 1199.98px) {
            .ch-audit-kpis { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .ch-ia-kpis { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .ch-audit-layout { grid-template-columns:1fr; }
        }
        @media (max-width: 767.98px) {
            .ch-audit-head, .ch-audit-actions { flex-direction:column; align-items:stretch; }
            .ch-audit-kpis, .ch-audit-filters, .ch-ia-kpis, .ch-ia-filters { grid-template-columns:1fr; }
            .ch-audit-actions .btn { width:100%; }
        }
    </style>

    <div class="ch-audit-head">
        <div>
            <h1 class="ch-audit-title"><i class="fa-solid fa-shield-halved"></i><span>Auditoria RR.HH.</span></h1>
            <p class="ch-audit-subtitle">Control de accesos sensibles, Google Authenticator y descargas de reportes de personal.</p>
        </div>
        <div class="ch-audit-actions">
            <button class="btn btn-label-secondary" type="button" id="chAuditRefresh">
                <i class="fa-solid fa-rotate"></i><span>Actualizar</span>
            </button>
        </div>
    </div>

    <div class="ch-audit-mode-tabs" role="tablist" aria-label="Tipo de auditoria">
        <button type="button" class="ch-audit-mode-tab active" data-audit-mode="sensible">
            <i class="fa-solid fa-shield-halved"></i><span>Datos sensibles</span>
        </button>
        <button type="button" class="ch-audit-mode-tab" data-audit-mode="interna">
            <i class="fa-solid fa-clipboard-list"></i><span>Auditoria interna</span>
        </button>
    </div>

    <div class="ch-audit-section active" id="chAuditSectionSensible">
    <div class="ch-audit-kpis">
        <div class="ch-audit-kpi"><span>Authenticator confirmados</span><strong id="chAuditKpiTotp">0</strong></div>
        <div class="ch-audit-kpi"><span>Permiso documentos</span><strong id="chAuditKpiDocs">0</strong></div>
        <div class="ch-audit-kpi"><span>Permiso salarios</span><strong id="chAuditKpiSalary">0</strong></div>
        <div class="ch-audit-kpi"><span>Intentos denegados</span><strong id="chAuditKpiDenied">0</strong></div>
        <div class="ch-audit-kpi"><span>Eventos auditados</span><strong id="chAuditKpiEvents">0</strong></div>
    </div>

    <div class="ch-audit-layout">
        <section class="ch-audit-panel">
            <div class="ch-audit-panel-head">
                <h2 class="ch-audit-panel-title"><i class="fa-solid fa-users-gear"></i><span>Permisos criticos</span></h2>
                <div class="ch-audit-tabs" id="chAuditPermissionTabs">
                    <button class="ch-audit-tab active" type="button" data-perm="documentos_sensibles">Documentos</button>
                    <button class="ch-audit-tab" type="button" data-perm="salarios">Salarios</button>
                    <button class="ch-audit-tab" type="button" data-perm="reset_totp">Reset TOTP</button>
                    <button class="ch-audit-tab" type="button" data-perm="auditoria">Auditoria</button>
                </div>
            </div>
            <div class="ch-audit-scroll">
                <table class="ch-audit-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Puesto</th>
                            <th>Departamento</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody id="chAuditPermissionsBody">
                        <tr><td colspan="4" class="ch-audit-empty">Cargando permisos...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ch-audit-panel">
            <div class="ch-audit-panel-head">
                <h2 class="ch-audit-panel-title"><i class="fa-solid fa-mobile-screen-button"></i><span>Google Authenticator</span></h2>
            </div>
            <div class="ch-audit-scroll">
                <table class="ch-audit-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Ultimo uso</th>
                            <?php if ($puedeResetearTotpDocumentosSensiblesRrhh): ?>
                            <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="chAuditTotpBody">
                        <tr><td colspan="<?php echo $puedeResetearTotpDocumentosSensiblesRrhh ? 4 : 3; ?>" class="ch-audit-empty">Cargando autenticadores...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="ch-audit-panel">
        <div class="ch-audit-panel-head">
            <h2 class="ch-audit-panel-title"><i class="fa-solid fa-clock-rotate-left"></i><span>Bitacora sensible</span></h2>
            <div class="ch-audit-filters">
                <select class="form-select form-select-sm" id="chAuditFilterTipo">
                    <option value="todos">Todo</option>
                    <option value="documentos">Documentos</option>
                    <option value="salarios">Salarios</option>
                </select>
                <select class="form-select form-select-sm" id="chAuditFilterResultado">
                    <option value="todos">Todos los resultados</option>
                    <option value="autorizado">Autorizado</option>
                    <option value="denegado">Denegado</option>
                </select>
                <input class="form-control form-control-sm" id="chAuditSearch" type="search" placeholder="Buscar usuario, persona, accion o detalle">
            </div>
        </div>
        <div class="ch-audit-activity-scroll">
            <table class="ch-audit-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Usuario</th>
                        <th>Persona</th>
                        <th>Documento</th>
                        <th>Accion</th>
                        <th>Resultado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody id="chAuditEventsBody">
                    <tr><td colspan="8" class="ch-audit-empty">Cargando bitacora...</td></tr>
                </tbody>
            </table>
        </div>
    </section>
    </div>

    <div class="ch-audit-section" id="chAuditSectionInterna">
        <div class="ch-ia-kpis">
            <div class="ch-ia-kpi"><span>Eventos auditados</span><strong id="chIaTotal">0</strong></div>
            <div class="ch-ia-kpi"><span>Personas</span><strong id="chIaPersonas">0</strong></div>
            <div class="ch-ia-kpi"><span>Candidatos</span><strong id="chIaCandidatos">0</strong></div>
            <div class="ch-ia-kpi"><span>Permisos</span><strong id="chIaPermisos">0</strong></div>
            <div class="ch-ia-kpi"><span>Ultimas 24h</span><strong id="chIa24h">0</strong></div>
        </div>

        <section class="ch-audit-panel">
            <div class="ch-audit-panel-head">
                <h2 class="ch-audit-panel-title"><i class="fa-solid fa-clock-rotate-left"></i><span>Bitacora interna</span></h2>
                <div class="ch-ia-filters">
                    <select class="form-select form-select-sm" id="chIaTipo">
                        <option value="todos">Todas las entidades</option>
                        <option value="persona">Personas / usuarios</option>
                        <option value="candidato">Candidatos</option>
                        <option value="permisos">Permisos</option>
                        <option value="general">General</option>
                    </select>
                    <select class="form-select form-select-sm" id="chIaAccion">
                        <option value="todos">Todas las acciones</option>
                    </select>
                    <input class="form-control form-control-sm" id="chIaBuscar" type="search" placeholder="Buscar usuario, persona, candidato o accion">
                </div>
            </div>
            <div class="ch-audit-activity-scroll">
                <table class="ch-audit-table ch-ia-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Modulo</th>
                            <th>Entidad</th>
                            <th>Accion</th>
                            <th>Resumen</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="chIaBody">
                        <tr><td colspan="7" class="ch-audit-empty">Cargando auditoria interna...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
(() => {
    const state = {
        data: null,
        permisoActivo: 'documentos_sensibles',
        filtros: { tipo: 'todos', resultado: 'todos', q: '' },
    };
    const puedeResetearTotp = <?php echo $puedeResetearTotpDocumentosSensiblesRrhh ? 'true' : 'false'; ?>;

    const $ = (selector) => document.querySelector(selector);
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
    const textOrDash = (value) => {
        const text = String(value ?? '').trim();
        return text === '' ? '-' : escapeHtml(text);
    };
    const badge = (label, type) => `<span class="ch-audit-badge ${type}">${escapeHtml(label)}</span>`;
    const resultBadge = (resultado) => {
        const value = String(resultado ?? '').toLowerCase();
        if (value === 'autorizado' || value === 'ok') return badge(resultado || 'autorizado', 'ok');
        if (value === 'denegado') return badge(resultado, 'danger');
        return badge(resultado || 'desconocido', 'neutral');
    };
    const actionMeta = (accion, tipo) => {
        const value = String(accion || '').toLowerCase();
        const map = {
            ver: ['Visualizo', 'view', 'fa-eye'],
            descargar: ['Descargo', 'download', 'fa-download'],
            eliminar: ['Elimino', 'delete', 'fa-trash'],
            subir: ['Subio', 'upload', 'fa-upload'],
            importar: ['Importo', 'upload', 'fa-file-import'],
            guardar: ['Guardo', 'upload', 'fa-floppy-disk'],
            leer: ['Consulto', 'view', 'fa-eye'],
            totp: ['Valido TOTP', 'auth', 'fa-key'],
            totp_setup: ['Configuro TOTP', 'auth', 'fa-qrcode'],
            totp_confirmar: ['Confirmo TOTP', 'auth', 'fa-check-double'],
            totp_estado: ['Reviso TOTP', 'auth', 'fa-mobile-screen-button'],
            reset_totp: ['Reinicio TOTP', 'auth', 'fa-rotate-left'],
            generar_token: [tipo === 'documentos' ? 'Autorizo acceso' : 'Autorizo', 'auth', 'fa-shield-halved'],
        };
        return map[value] || [accion || 'Evento', 'neutral', 'fa-circle-info'];
    };
    const actionBadge = (accion, tipo) => {
        const [label, css, icon] = actionMeta(accion, tipo);
        return `<span class="ch-audit-action ${css}"><i class="fa-solid ${icon}"></i>${escapeHtml(label)}</span>`;
    };
    const documentCell = (row) => {
        const accion = String(row.accion || '').toLowerCase();
        if (['reset_totp', 'totp', 'totp_setup', 'totp_confirmar', 'totp_estado'].includes(accion)) {
            return `
                <td class="ch-audit-document">
                    <strong>Google Authenticator</strong>
                    <small><span class="ch-audit-doc-note protected"><i class="fa-solid fa-mobile-screen-button"></i>Segundo paso</span></small>
                </td>
            `;
        }
        if (row.tipo === 'salarios') {
            return `
                <td class="ch-audit-document">
                    <strong>Salario protegido</strong>
                    <small><span class="ch-audit-doc-note salary"><i class="fa-solid fa-lock"></i>Campo cifrado</span></small>
                </td>
            `;
        }
        const documento = String(row.documento_nombre || row.recurso || '').trim();
        const idDocumento = String(row.id_documento || '').trim();
        const archivo = String(row.archivo || '').trim();
        const protegido = ['28', '29', '31', '37', '38'].includes(idDocumento) || archivo.toLowerCase().endsWith('.fad');
        const nota = protegido
            ? '<span class="ch-audit-doc-note protected"><i class="fa-solid fa-lock"></i>Documento protegido</span>'
            : '<span class="ch-audit-doc-note regular"><i class="fa-solid fa-file-lines"></i>Registro documental</span>';
        return `
            <td class="ch-audit-document">
                <strong>${textOrDash(documento)}</strong>
                <small>${nota}</small>
            </td>
        `;
    };

    function setLoading(isLoading) {
        const btn = $('#chAuditRefresh');
        if (!btn) return;
        btn.disabled = isLoading;
        btn.innerHTML = isLoading
            ? '<i class="fa-solid fa-spinner fa-spin"></i><span>Cargando</span>'
            : '<i class="fa-solid fa-rotate"></i><span>Actualizar</span>';
    }

    function renderKpis() {
        const totales = state.data?.totales || {};
        const denegados = Number(totales.denegados_documentos || 0) + Number(totales.denegados_salarios || 0);
        const eventos = Number(totales.eventos_documentos || 0) + Number(totales.eventos_salarios || 0);
        $('#chAuditKpiTotp').textContent = `${totales.totp_confirmados || 0}/${totales.totp_configurados || 0}`;
        $('#chAuditKpiDocs').textContent = totales.usuarios_documentos || 0;
        $('#chAuditKpiSalary').textContent = totales.usuarios_salarios || 0;
        $('#chAuditKpiDenied').textContent = denegados;
        $('#chAuditKpiEvents').textContent = eventos;
    }

    function renderPermissions() {
        const tbody = $('#chAuditPermissionsBody');
        const rows = state.data?.usuarios_con_permiso?.[state.permisoActivo] || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="ch-audit-empty">Sin usuarios con este permiso.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map((row) => `
            <tr>
                <td class="ch-audit-user">
                    <strong>${textOrDash(row.nombre)}</strong>
                    <small>#${textOrDash(row.numero_empleado)} &middot; ${textOrDash(row.user_name || row.correo)}</small>
                </td>
                <td>${textOrDash(row.puesto)}</td>
                <td>${textOrDash(row.departamento)}</td>
                <td>${badge(row.estatus || 'Activo', String(row.estatus || '').toLowerCase() === 'baja' ? 'danger' : 'ok')}</td>
            </tr>
        `).join('');
    }

    function renderTotp() {
        const tbody = $('#chAuditTotpBody');
        const rows = state.data?.autenticadores || [];
        const colspan = puedeResetearTotp ? 4 : 3;
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="${colspan}" class="ch-audit-empty">Nadie ha configurado Google Authenticator.</td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map((row) => {
            const confirmado = Number(row.confirmado || 0) === 1;
            const idPersona = Number(row.persona_id || row.id_persona || 0);
            const acciones = puedeResetearTotp
                ? `<td>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold" data-ch-audit-reset-totp="${idPersona}" data-ch-audit-reset-name="${escapeHtml(row.nombre || '')}" title="Reiniciar Google Authenticator">
                        <i class="fa-solid fa-rotate-left me-1"></i>Reiniciar
                    </button>
                </td>`
                : '';
            return `
                <tr>
                    <td class="ch-audit-user">
                        <strong>${textOrDash(row.nombre)}</strong>
                        <small>#${textOrDash(row.numero_empleado)} &middot; ${textOrDash(row.user_name || row.correo)}</small>
                    </td>
                    <td>${badge(confirmado ? 'Confirmado' : 'Pendiente', confirmado ? 'ok' : 'warn')}</td>
                    <td>${textOrDash(row.ultimo_uso_en || row.actualizado_en)}</td>
                    ${acciones}
                </tr>
            `;
        }).join('');
    }

    function renderEvents() {
        const tbody = $('#chAuditEventsBody');
        const q = state.filtros.q.trim().toLowerCase();
        let rows = state.data?.eventos || [];
        rows = rows.filter((row) => {
            const tipoOk = state.filtros.tipo === 'todos' || row.tipo === state.filtros.tipo;
            const resultado = String(row.resultado || '').toLowerCase();
            const resultadoOk = state.filtros.resultado === 'todos' || resultado === state.filtros.resultado;
            const hayBusqueda = !q || [
                row.usuario_nombre,
                row.persona_nombre,
                row.documento_nombre,
                row.id_documento,
                row.id_documento_carga,
                row.recurso,
                row.archivo,
                row.accion,
                row.resultado,
                row.detalle,
                row.ip,
            ].some((value) => String(value || '').toLowerCase().includes(q));
            return tipoOk && resultadoOk && hayBusqueda;
        });
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="ch-audit-empty">No hay eventos con esos filtros.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map((row) => `
            <tr>
                <td>${textOrDash(row.fecha_hora)}</td>
                <td>${badge(row.tipo === 'salarios' ? 'Salarios' : 'Documentos', row.tipo === 'salarios' ? 'warn' : 'neutral')}</td>
                <td class="ch-audit-user">
                    <strong>${textOrDash(row.usuario_nombre)}</strong>
                    <small>ID ${textOrDash(row.id_usuario)}${row.ip ? ` &middot; ${textOrDash(row.ip)}` : ''}</small>
                </td>
                <td class="ch-audit-user">
                    <strong>${textOrDash(row.persona_nombre)}</strong>
                    <small>ID ${textOrDash(row.id_persona)}</small>
                </td>
                ${documentCell(row)}
                <td>${actionBadge(row.accion, row.tipo)}</td>
                <td>${resultBadge(row.resultado)}</td>
                <td>${textOrDash(row.detalle)}</td>
            </tr>
        `).join('');
    }

    function renderAll() {
        renderKpis();
        renderPermissions();
        renderTotp();
        renderEvents();
    }

    const internalState = { eventos: [], acciones: [], cargado: false };
    const shortValue = (value) => {
        if (value === null || typeof value === 'undefined') return '-';
        if (Array.isArray(value)) return value.join(', ');
        if (typeof value === 'object') return JSON.stringify(value);
        const text = String(value);
        return text.length > 120 ? text.slice(0, 117) + '...' : text;
    };
    const prettyName = (value) => {
        const text = String(value ?? '').trim();
        if (!text) return '-';
        if (/[a-záéíóúñü]/.test(text)) return text;
        return text.toLocaleLowerCase('es-MX').replace(/(^|[\s.'-])([a-záéíóúñü])/g, (match, sep, char) => (
            sep + char.toLocaleUpperCase('es-MX')
        ));
    };
    const internalTipoBadge = (tipo) => {
        const value = String(tipo || 'general').toLowerCase();
        const labels = { persona: 'Persona', candidato: 'Candidato', permisos: 'Permisos', general: 'General' };
        const icons = { persona: 'fa-user', candidato: 'fa-user-plus', permisos: 'fa-key', general: 'fa-circle-info' };
        const cssMap = { persona: 'view', candidato: 'upload', permisos: 'auth', general: 'neutral' };
        return `<span class="ch-audit-action ${cssMap[value] || 'neutral'}"><i class="fa-solid ${icons[value] || icons.general}"></i>${escapeHtml(labels[value] || value)}</span>`;
    };
    const internalAccionLabel = (accion) => String(accion || 'evento').replaceAll('_', ' ');
    const renderInternalCambios = (row) => {
        const cambios = row.cambios && typeof row.cambios === 'object' ? row.cambios : {};
        const detalle = row.detalle && typeof row.detalle === 'object' ? row.detalle : {};
        const keysCambios = Object.keys(cambios);
        const keysDetalle = Object.keys(detalle);
        if (!keysCambios.length && !keysDetalle.length) {
            return '<span class="ch-audit-empty d-block p-0 text-start">Sin detalle registrado.</span>';
        }
        const cambiosHtml = keysCambios.slice(0, 4).map((key) => {
            const c = cambios[key] || {};
            return `<div class="ch-ia-change">
                <strong>${escapeHtml(key)}</strong>
                <span>Antes: ${textOrDash(shortValue(c.antes))}</span>
                <span>Despues: ${textOrDash(shortValue(c.despues))}</span>
            </div>`;
        }).join('');
        const detalleHtml = keysDetalle.slice(0, 4).map((key) => `
            <div class="ch-ia-change">
                <strong>${escapeHtml(key.replaceAll('_', ' '))}</strong>
                <span>${textOrDash(shortValue(detalle[key]))}</span>
            </div>
        `).join('');
        const restantes = Math.max(0, keysCambios.length - 4) + Math.max(0, keysDetalle.length - 4);
        const more = restantes > 0 ? `<span class="ch-ia-more">+${restantes} detalle(s) mas.</span>` : '';
        return `<div class="ch-ia-change-list">${cambiosHtml}${detalleHtml}${more}</div>`;
    };
    const renderInternalRows = () => {
        const tbody = $('#chIaBody');
        const rows = internalState.eventos || [];
        if (!tbody) return;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="ch-audit-empty">Sin movimientos internos con esos filtros.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map((row) => `
            <tr>
                <td class="ch-ia-date">${textOrDash(row.fecha_hora)}</td>
                <td class="ch-audit-user">
                    <strong>${textOrDash(prettyName(row.usuario_nombre || ('Usuario #' + (row.id_usuario || ''))))}</strong>
                    <small>ID ${textOrDash(row.id_usuario)}${row.ip ? ` &middot; ${textOrDash(row.ip)}` : ''}</small>
                </td>
                <td class="ch-ia-module">${textOrDash(row.modulo)}</td>
                <td class="ch-audit-user">
                    ${internalTipoBadge(row.entidad_tipo)}
                    <strong>${textOrDash(prettyName(row.entidad_nombre || ('#' + (row.entidad_id || ''))))}</strong>
                    <small>ID ${textOrDash(row.entidad_id)}</small>
                </td>
                <td>${actionBadge(row.accion || 'evento', 'documentos')}</td>
                <td class="ch-ia-summary">${textOrDash(row.resumen)}</td>
                <td class="ch-ia-changes">${renderInternalCambios(row)}</td>
            </tr>
        `).join('');
    };
    const renderInternalActions = () => {
        const select = $('#chIaAccion');
        if (!select) return;
        const current = select.value || 'todos';
        const options = ['<option value="todos">Todas las acciones</option>'];
        (internalState.acciones || []).forEach((row) => {
            const accion = String(row.accion || '').trim();
            if (!accion) return;
            options.push(`<option value="${escapeHtml(accion)}">${escapeHtml(internalAccionLabel(accion))} (${Number(row.total || 0)})</option>`);
        });
        select.innerHTML = options.join('');
        select.value = Array.from(select.options).some((o) => o.value === current) ? current : 'todos';
    };
    const renderInternalKpis = (totales) => {
        $('#chIaTotal').textContent = Number(totales?.total || 0);
        $('#chIaPersonas').textContent = Number(totales?.personas || 0);
        $('#chIaCandidatos').textContent = Number(totales?.candidatos || 0);
        $('#chIaPermisos').textContent = Number(totales?.permisos || 0);
        $('#chIa24h').textContent = Number(totales?.ultimas_24h || 0);
    };

    async function loadInternalAudit() {
        setLoading(true);
        const qs = new URLSearchParams();
        qs.set('tipo', $('#chIaTipo')?.value || 'todos');
        qs.set('accion', $('#chIaAccion')?.value || 'todos');
        qs.set('q', $('#chIaBuscar')?.value || '');
        try {
            const response = await fetch('/caphum/getAuditoriaInternaRrhh?' + qs.toString(), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.mensaje || 'No se pudo cargar la auditoria interna.');
            }
            internalState.eventos = payload.datos?.eventos || [];
            internalState.acciones = payload.datos?.acciones || internalState.acciones || [];
            internalState.cargado = true;
            renderInternalKpis(payload.datos?.totales || {});
            renderInternalActions();
            renderInternalRows();
        } catch (error) {
            $('#chIaBody').innerHTML = `<tr><td colspan="7" class="ch-audit-empty">${escapeHtml(error.message || 'Error al cargar auditoria interna.')}</td></tr>`;
        } finally {
            setLoading(false);
        }
    }

    async function loadAudit() {
        setLoading(true);
        try {
            const response = await fetch('/caphum/getAuditoriaRrhh', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.mensaje || 'No se pudo cargar la auditoria.');
            }
            state.data = payload.datos || {};
            renderAll();
        } catch (error) {
            $('#chAuditEventsBody').innerHTML = `<tr><td colspan="8" class="ch-audit-empty">${escapeHtml(error.message || 'Error al cargar auditoria.')}</td></tr>`;
            $('#chAuditPermissionsBody').innerHTML = '<tr><td colspan="4" class="ch-audit-empty">No se pudo cargar permisos.</td></tr>';
            $('#chAuditTotpBody').innerHTML = '<tr><td colspan="3" class="ch-audit-empty">No se pudo cargar autenticadores.</td></tr>';
        } finally {
            setLoading(false);
        }
    }

    async function resetTotpDesdeAuditoria(idPersona, nombre) {
        idPersona = Number(idPersona || 0);
        if (!idPersona || !puedeResetearTotp) return;
        const label = String(nombre || '').trim() || `usuario #${idPersona}`;
        if (typeof Swal !== 'undefined') {
            const ok = await Swal.fire({
                icon: 'warning',
                title: 'Reiniciar Google Authenticator',
                text: `Se reiniciara el segundo paso de ${label}. El usuario tendra que escanear un nuevo QR en su proximo acceso.`,
                showCancelButton: true,
                confirmButtonText: 'Reiniciar',
                cancelButtonText: 'Cancelar'
            });
            if (!ok.isConfirmed) return;
        } else if (!confirm(`Reiniciar Google Authenticator de ${label}?`)) {
            return;
        }

        const body = new URLSearchParams();
        body.append('id_persona', String(idPersona));
        try {
            const response = await fetch('/caphum/resetTotpDocumentoSensiblePersona', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body
            });
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.mensaje || 'No se pudo reiniciar Google Authenticator.');
            }
            if (typeof Swal !== 'undefined') {
                await Swal.fire('Autenticador reiniciado', payload.mensaje || 'El segundo paso fue reiniciado correctamente.', 'success');
            }
            await loadAudit();
        } catch (error) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Google Authenticator', error.message || 'No se pudo reiniciar Google Authenticator.', 'error');
            } else {
                alert(error.message || 'No se pudo reiniciar Google Authenticator.');
            }
        }
    }

    $('#chAuditRefresh')?.addEventListener('click', () => {
        if (document.body.dataset.chAuditMode === 'interna') {
            loadInternalAudit();
        } else {
            loadAudit();
        }
    });
    $('#chAuditTotpBody')?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-ch-audit-reset-totp]');
        if (!btn) return;
        resetTotpDesdeAuditoria(btn.getAttribute('data-ch-audit-reset-totp'), btn.getAttribute('data-ch-audit-reset-name'));
    });
    document.querySelectorAll('[data-audit-mode]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const mode = btn.dataset.auditMode || 'sensible';
            document.body.dataset.chAuditMode = mode;
            document.querySelectorAll('[data-audit-mode]').forEach((el) => el.classList.toggle('active', el === btn));
            $('#chAuditSectionSensible')?.classList.toggle('active', mode === 'sensible');
            $('#chAuditSectionInterna')?.classList.toggle('active', mode === 'interna');
            if (mode === 'interna' && !internalState.cargado) {
                loadInternalAudit();
            }
        });
    });
    $('#chAuditPermissionTabs')?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-perm]');
        if (!btn) return;
        state.permisoActivo = btn.dataset.perm;
        document.querySelectorAll('#chAuditPermissionTabs .ch-audit-tab').forEach((el) => el.classList.toggle('active', el === btn));
        renderPermissions();
    });
    $('#chAuditFilterTipo')?.addEventListener('change', (event) => {
        state.filtros.tipo = event.target.value;
        renderEvents();
    });
    $('#chAuditFilterResultado')?.addEventListener('change', (event) => {
        state.filtros.resultado = event.target.value;
        renderEvents();
    });
    $('#chAuditSearch')?.addEventListener('input', (event) => {
        state.filtros.q = event.target.value;
        renderEvents();
    });
    $('#chIaTipo')?.addEventListener('change', loadInternalAudit);
    $('#chIaAccion')?.addEventListener('change', loadInternalAudit);
    let internalSearchTimer = null;
    $('#chIaBuscar')?.addEventListener('input', () => {
        clearTimeout(internalSearchTimer);
        internalSearchTimer = setTimeout(loadInternalAudit, 250);
    });

    document.body.dataset.chAuditMode = 'sensible';
    loadAudit();
})();
</script>
