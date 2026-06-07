<div class="container-fluid py-3 atlas-notif-page">
    <style>
        .atlas-notif-page { color: #22303e; }
        .atlas-notif-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
        .atlas-notif-title { display: flex; align-items: center; gap: .7rem; margin: 0; color: #1e3a5f; font-size: 1.28rem; font-weight: 800; }
        .atlas-notif-title i { color: #2563eb; }
        .atlas-notif-subtitle { margin: .2rem 0 0; color: #64748b; font-size: .86rem; font-weight: 700; }
        .atlas-notif-layout { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(19rem, .85fr); gap: 1rem; align-items: start; }
        .atlas-notif-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
        .atlas-notif-kpi { border: 1px solid #e2e8f0; border-radius: .65rem; background: #fff; padding: .78rem .9rem; }
        .atlas-notif-kpi span { display: flex; align-items: center; gap: .45rem; color: #64748b; font-size: .72rem; font-weight: 900; text-transform: uppercase; letter-spacing: .03em; }
        .atlas-notif-kpi strong { display: block; margin-top: .2rem; color: #173756; font-size: 1.2rem; font-weight: 900; }
        .atlas-notif-card { border: 1px solid #e2e8f0; border-radius: .75rem; background: #fff; padding: 1rem; box-shadow: none; }
        .atlas-notif-card h5 { display: flex; align-items: center; gap: .45rem; margin: 0 0 .75rem; color: #173756; font-size: .92rem; font-weight: 900; text-transform: uppercase; letter-spacing: .025em; }
        .atlas-notif-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        .atlas-notif-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
        .atlas-notif-field-wide { grid-column: 1 / -1; }
        .atlas-notif-actions { display: flex; justify-content: flex-end; gap: .65rem; flex-wrap: wrap; margin-top: .85rem; }
        .atlas-notif-preview { border: 1px solid #dbeafe; border-radius: 1rem; background: linear-gradient(180deg, #f8fbff, #fff); padding: .85rem; }
        .atlas-notif-phone { border: 1px solid #e2e8f0; border-radius: .9rem; background: #f8fafc; padding: .75rem; min-height: 7rem; }
        .atlas-notif-phone-title { color: #172033; font-size: .9rem; font-weight: 900; line-height: 1.15; }
        .atlas-notif-phone-msg { color: #64748b; font-size: .8rem; font-weight: 700; line-height: 1.25; margin-top: .25rem; }
        .atlas-notif-phone-img { width: 100%; max-height: 8rem; object-fit: cover; border-radius: .6rem; margin-top: .55rem; display: none; }
        .atlas-notif-payload { margin: .75rem 0 0; border: 1px solid #e2e8f0; border-radius: .6rem; background: #0f172a; color: #e2e8f0; padding: .75rem; max-height: 13rem; overflow: auto; font-size: .76rem; line-height: 1.35; }
        .atlas-notif-table { width: 100%; border-collapse: collapse; }
        .atlas-notif-table th { color: #566a7f; font-size: .7rem; font-weight: 900; text-transform: uppercase; border-bottom: 1px solid #dbe4ef; padding: .5rem; white-space: nowrap; }
        .atlas-notif-table td { color: #566a7f; font-size: .78rem; font-weight: 700; border-bottom: 1px solid #eef2f7; padding: .5rem; vertical-align: middle; }
        .atlas-notif-scroll { max-height: 18rem; overflow: auto; border: 1px solid #e2e8f0; border-radius: .65rem; }
        .atlas-notif-response { min-height: 8rem; max-height: 18rem; overflow: auto; border: 1px solid #e2e8f0; border-radius: .65rem; background: #f8fafc; padding: .75rem; color: #334155; font-size: .78rem; font-weight: 700; white-space: pre-wrap; }
        .atlas-notif-status-dot { width: .55rem; height: .55rem; border-radius: 999px; display: inline-block; background: #dc2626; }
        .atlas-notif-status-dot.is-ok { background: #16a34a; }
        .atlas-notif-muted { color: #64748b; font-size: .78rem; font-weight: 700; }
        .atlas-notif-badge { display: inline-flex; align-items: center; gap: .28rem; border-radius: 999px; padding: .18rem .55rem; font-size: .72rem; font-weight: 900; white-space: nowrap; }
        .atlas-notif-badge-ok { background: #dcfce7; color: #15803d; }
        .atlas-notif-badge-warn { background: #fee2e2; color: #b91c1c; }
        .atlas-notif-empty { text-align: center; color: #94a3b8; font-weight: 700; padding: 2rem !important; }
        .atlas-notif-row-actions { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; }
        .atlas-notif-row-actions .btn { width: 2.15rem; height: 2.15rem; border-radius: 999px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: #26344e; border-color: #26344e; box-shadow: 0 5px 12px rgba(15, 23, 42, .18); }
        @media (max-width: 991.98px) {
            .atlas-notif-layout { grid-template-columns: 1fr; }
            .atlas-notif-grid-3, .atlas-notif-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .atlas-notif-head { align-items: stretch; flex-direction: column; }
            .atlas-notif-grid, .atlas-notif-grid-3, .atlas-notif-kpis { grid-template-columns: 1fr; }
            .atlas-notif-actions .btn, .atlas-notif-head .btn { width: 100%; justify-content: center; }
        }
    </style>

    <div class="atlas-notif-head">
        <div>
            <h4 class="atlas-notif-title"><i class="fa-solid fa-bell"></i><span>Notificaciones App</span></h4>
            <p class="atlas-notif-subtitle">Gestion de avisos, campanas, inbox, tokens y auditoria Expo de Atlas.</p>
        </div>
        <a href="/Atlas/catalogos" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-tags me-1"></i>Catalogos</a>
    </div>

    <div class="atlas-notif-layout">
        <div class="d-flex flex-column gap-3">
            <div class="atlas-notif-kpis">
                <div class="atlas-notif-kpi"><span><i class="fa-solid fa-bullhorn"></i>Campanas</span><strong id="atlas-notif-kpi-campanias">0</strong></div>
                <div class="atlas-notif-kpi"><span><i class="fa-solid fa-paper-plane"></i>Enviadas</span><strong id="atlas-notif-kpi-enviadas">0</strong></div>
                <div class="atlas-notif-kpi"><span><i class="fa-solid fa-triangle-exclamation"></i>Fallidas</span><strong id="atlas-notif-kpi-fallidas">0</strong></div>
                <div class="atlas-notif-kpi"><span><i class="fa-solid fa-mobile-screen"></i>Tokens activos</span><strong id="atlas-notif-kpi-tokens">0</strong></div>
            </div>

            <div class="atlas-notif-card">
                <h5><i class="fa-solid fa-plug-circle-bolt"></i>Conexion Atlas App</h5>
                <div class="atlas-notif-grid">
                    <div><label class="form-label">Token API</label><input type="password" class="form-control" id="atlas-notif-token" placeholder="Bearer token de Atlas App"></div>
                    <div><label class="form-label">Usuario / correo API</label><input type="text" class="form-control" id="atlas-notif-login-user" placeholder="Usuario de login API"></div>
                    <div><label class="form-label">Contrasena API</label><input type="password" class="form-control" id="atlas-notif-login-pass" placeholder="Contrasena"></div>
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" id="atlas-notif-btn-login"><i class="fa-solid fa-key me-1"></i>Iniciar sesion</button>
                        <button type="button" class="btn btn-label-danger" id="atlas-notif-btn-clear-token">Limpiar token</button>
                        <span class="atlas-notif-muted"><span class="atlas-notif-status-dot" id="atlas-notif-token-dot"></span> <span id="atlas-notif-token-status">Sin token</span></span>
                    </div>
                </div>
            </div>

            <div class="atlas-notif-card">
                <h5><i class="fa-solid fa-pen-to-square"></i>Crear y enviar notificacion</h5>
                <form id="atlas-notif-form-aviso" autocomplete="off">
                    <div class="atlas-notif-grid-3">
                        <div class="atlas-notif-field-wide"><label class="form-label">Titulo *</label><input type="text" class="form-control" name="titulo" required placeholder="Titulo de la notificacion"></div>
                        <div class="atlas-notif-field-wide"><label class="form-label">Mensaje *</label><textarea class="form-control" name="mensaje" rows="2" required placeholder="Mensaje corto para el usuario"></textarea></div>
                        <div><label class="form-label">Type</label><input type="text" class="form-control" name="type" value="aviso_especial" placeholder="aviso_especial"></div>
                        <div><label class="form-label">Notification type</label><input type="text" class="form-control" name="notification_type" value="push" placeholder="push"></div>
                        <div><label class="form-label">ID credito</label><input type="text" class="form-control" name="id_credito" placeholder="Opcional"></div>
                        <div><label class="form-label">Monto</label><input type="number" step="0.01" class="form-control" name="monto" placeholder="Opcional"></div>
                        <div><label class="form-label">Semana</label><input type="text" class="form-control" name="semana" placeholder="Opcional"></div>
                        <div><label class="form-label">Imagen URL</label><input type="url" class="form-control" name="imagen_url" placeholder="https://..."></div>
                        <div class="atlas-notif-field-wide"><label class="form-label">HTML / contenido extendido</label><textarea class="form-control" name="html" rows="3" placeholder="Contenido que vive en atlas_notifications"></textarea></div>
                    </div>
                    <div class="atlas-notif-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Crear aviso</button></div>
                </form>
            </div>

            <div class="atlas-notif-card">
                <h5><i class="fa-solid fa-user-check"></i>Envio individual</h5>
                <form id="atlas-notif-form-individual" autocomplete="off">
                    <div class="atlas-notif-grid">
                        <div><label class="form-label">User ID</label><input type="text" class="form-control" name="user_id" placeholder="persona.id"></div>
                        <div><label class="form-label">External ID</label><input type="text" class="form-control" name="external_id" placeholder="numero empleado"></div>
                    </div>
                    <div class="atlas-notif-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>Enviar individual</button></div>
                </form>
            </div>

            <div class="atlas-notif-card">
                <h5><i class="fa-solid fa-users-rays"></i>Campana masiva</h5>
                <form id="atlas-notif-form-campania" autocomplete="off">
                    <div class="atlas-notif-grid">
                        <div><label class="form-label">User IDs</label><textarea class="form-control" name="user_ids" rows="2" placeholder="1133, 7"></textarea></div>
                        <div><label class="form-label">External IDs</label><textarea class="form-control" name="external_ids" rows="2" placeholder="999999704, 525"></textarea></div>
                    </div>
                    <div class="atlas-notif-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-bullhorn me-1"></i>Enviar campana</button></div>
                </form>
            </div>

            <div class="atlas-notif-card">
                <h5><i class="fa-solid fa-inbox"></i>Inbox por usuario</h5>
                <form id="atlas-notif-form-inbox" autocomplete="off">
                    <div class="atlas-notif-grid">
                        <div><label class="form-label">User ID</label><input type="text" class="form-control" name="user_id" placeholder="1133"></div>
                        <div><label class="form-label">External ID</label><input type="text" class="form-control" name="external_id" placeholder="999999704"></div>
                    </div>
                    <div class="atlas-notif-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Consultar inbox</button></div>
                </form>
                <div class="atlas-notif-scroll mt-3">
                    <table class="atlas-notif-table">
                        <thead><tr><th>Estado</th><th>Titulo</th><th>Fecha</th><th>Acciones</th></tr></thead>
                        <tbody id="atlas-notif-inbox-body"><tr><td colspan="4" class="atlas-notif-empty">Consulta un usuario para ver su inbox.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column gap-3">
            <div class="atlas-notif-card atlas-notif-preview">
                <h5><i class="fa-solid fa-eye"></i>Preview visual</h5>
                <div class="atlas-notif-phone">
                    <div class="atlas-notif-phone-title" id="atlas-notif-preview-title">Titulo de la notificacion</div>
                    <div class="atlas-notif-phone-msg" id="atlas-notif-preview-msg">Mensaje corto para el usuario.</div>
                    <img src="" alt="" class="atlas-notif-phone-img" id="atlas-notif-preview-img">
                </div>
                <pre class="atlas-notif-payload" id="atlas-notif-payload-preview">{}</pre>
            </div>

            <div class="atlas-notif-card">
                <h5><i class="fa-solid fa-clock-rotate-left"></i>Campanas, tokens y logs</h5>
                <div class="atlas-notif-grid">
                    <button type="button" class="btn btn-outline-primary" data-atlas-notif-load="/api/atlas/push-campaigns" data-atlas-notif-method="GET"><i class="fa-solid fa-bullhorn me-1"></i>Cargar campanas</button>
                    <button type="button" class="btn btn-outline-primary" data-atlas-notif-load="/api/atlas/push-tokens" data-atlas-notif-method="GET"><i class="fa-solid fa-mobile-screen me-1"></i>Cargar tokens</button>
                    <button type="button" class="btn btn-outline-primary" data-atlas-notif-load="/api/atlas/push-notifications/log" data-atlas-notif-method="GET"><i class="fa-solid fa-file-waveform me-1"></i>Cargar logs Expo</button>
                    <button type="button" class="btn btn-outline-danger" id="atlas-notif-btn-disable-token"><i class="fa-solid fa-mobile-screen-button me-1"></i>Desactivar token</button>
                </div>
                <div class="atlas-notif-grid mt-3">
                    <div><label class="form-label">Expo token a desactivar</label><input type="text" class="form-control" id="atlas-notif-token-delete" placeholder="ExponentPushToken[...]"></div>
                    <div><label class="form-label">Notification ID detalle</label><input type="text" class="form-control" id="atlas-notif-detalle-id" placeholder="123"></div>
                </div>
                <div class="atlas-notif-actions"><button type="button" class="btn btn-outline-primary" id="atlas-notif-btn-detalle"><i class="fa-solid fa-circle-info me-1"></i>Consultar detalle</button></div>
            </div>

            <div class="atlas-notif-card">
                <h5><i class="fa-solid fa-terminal"></i>Respuesta Atlas App</h5>
                <div class="atlas-notif-response" id="atlas-notif-response">Sin acciones ejecutadas.</div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let atlasNotifInboxContext = { user_id: '', external_id: '' };

    function esc(v) { return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
    function setKpi(id, value) { const el = document.getElementById(id); if (el) el.textContent = Number(value || 0).toLocaleString('es-MX'); }
    function setTexto(id, value) { const el = document.getElementById(id); if (el) el.textContent = value == null ? '' : String(value); }
    function formToJson(form) { const data = {}; Array.from(new FormData(form).entries()).forEach(pair => { data[pair[0]] = pair[1]; }); return data; }
    function showBusy() { if (typeof Swal !== 'undefined') Swal.fire({ title: 'Procesando su peticion', text: 'Espere un momento...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: function () { Swal.showLoading(); } }); }
    function hideBusy() {
        if (typeof Swal === 'undefined') return;
        const title = document.getElementById('swal2-title');
        if (title && title.textContent === 'Procesando su peticion') Swal.close();
    }
    function tokenActual() {
        const el = document.getElementById('atlas-notif-token');
        return (el && el.value ? String(el.value).trim() : '') || (typeof localStorage !== 'undefined' ? String(localStorage.getItem('atlas_app_token') || '').trim() : '');
    }
    function guardarToken(token) {
        const clean = String(token || '').trim();
        const el = document.getElementById('atlas-notif-token');
        if (el) el.value = clean;
        try { if (clean) localStorage.setItem('atlas_app_token', clean); else localStorage.removeItem('atlas_app_token'); } catch (e) {}
        actualizarTokenStatus();
    }
    function actualizarTokenStatus() {
        const ok = tokenActual() !== '';
        const dot = document.getElementById('atlas-notif-token-dot');
        if (dot) dot.classList.toggle('is-ok', ok);
        setTexto('atlas-notif-token-status', ok ? 'Token listo' : 'Sin token');
    }
    function setRespuesta(data) {
        const el = document.getElementById('atlas-notif-response');
        if (el) el.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
    }
    function lista(v) { return String(v || '').split(/[,\n;]/).map(x => x.trim()).filter(Boolean); }
    function avisoPayload() {
        const form = document.getElementById('atlas-notif-form-aviso');
        const d = form ? formToJson(form) : {};
        return {
            titulo: d.titulo || '',
            mensaje: d.mensaje || '',
            type: d.type || 'aviso_especial',
            notification_type: d.notification_type || 'push',
            imagen_url: d.imagen_url || '',
            html: d.html || '',
            id_credito: d.id_credito || '',
            monto: d.monto || '',
            semana: d.semana || '',
            data_json: { type: d.type || 'aviso_especial', screen: 'NotificacionEspecial', notification_id: null }
        };
    }
    function actualizarPreview() {
        const p = avisoPayload();
        setTexto('atlas-notif-preview-title', p.titulo || 'Titulo de la notificacion');
        setTexto('atlas-notif-preview-msg', p.mensaje || 'Mensaje corto para el usuario.');
        const img = document.getElementById('atlas-notif-preview-img');
        if (img) { img.style.display = p.imagen_url ? 'block' : 'none'; img.src = p.imagen_url || ''; }
        const payloadEl = document.getElementById('atlas-notif-payload-preview');
        if (payloadEl) payloadEl.textContent = JSON.stringify(p, null, 2);
    }
    async function proxy(method, path, body, query) {
        const res = await fetch('/Atlas/notificacionesAppProxy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ method: method, path: path, body: body || {}, query: query || {}, token: tokenActual() })
        });
        const data = await res.json();
        setRespuesta(data);
        if (!data || data.success === false) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo ejecutar la accion.');
        return data;
    }
    function extraerArray(data) {
        const d = data && data.datos ? data.datos : data;
        if (Array.isArray(d)) return d;
        if (d && Array.isArray(d.datos)) return d.datos;
        if (d && Array.isArray(d.data)) return d.data;
        if (d && Array.isArray(d.notifications)) return d.notifications;
        if (d && Array.isArray(d.inbox)) return d.inbox;
        if (d && Array.isArray(d.items)) return d.items;
        return [];
    }
    function renderInbox(items) {
        const body = document.getElementById('atlas-notif-inbox-body');
        if (!body) return;
        if (!items.length) {
            body.innerHTML = '<tr><td colspan="4" class="atlas-notif-empty">Sin notificaciones para este usuario.</td></tr>';
            return;
        }
        body.innerHTML = items.map(row => {
            const id = row.id || row.inbox_id || row.user_notification_id || '';
            const read = Number(row.is_read || row.leida || 0) === 1;
            const titulo = row.titulo || row.title || row.mensaje || row.message || 'Notificacion';
            const fecha = row.created_at || row.fecha_alta || row.sent_at || '';
            const estado = read ? '<span class="atlas-notif-badge atlas-notif-badge-ok"><i class="fa-solid fa-check-double"></i>Leida</span>' : '<span class="atlas-notif-badge atlas-notif-badge-warn"><i class="fa-solid fa-circle"></i>No leida</span>';
            return '<tr><td>' + estado + '</td><td>' + esc(titulo) + '</td><td>' + esc(fecha || '-') + '</td><td><div class="atlas-notif-row-actions"><button type="button" class="btn btn-sm btn-primary" title="Marcar leida" data-atlas-notif-read="' + esc(id) + '"><i class="fa-solid fa-check-double"></i></button><button type="button" class="btn btn-sm btn-primary" title="Ocultar" data-atlas-notif-hide="' + esc(id) + '"><i class="fa-solid fa-eye-slash"></i></button></div></td></tr>';
        }).join('');
    }
    function actualizarKpis(data) {
        const d = data && data.datos ? data.datos : data;
        if (!d || typeof d !== 'object') return;
        const src = d.metricas || d.metrics || d.totales || d;
        if (src.total_campanias != null || src.campaigns != null) setKpi('atlas-notif-kpi-campanias', src.total_campanias || src.campaigns);
        if (src.total_enviados != null || src.sent != null) setKpi('atlas-notif-kpi-enviadas', src.total_enviados || src.sent);
        if (src.total_fallidos != null || src.failed != null) setKpi('atlas-notif-kpi-fallidas', src.total_fallidos || src.failed);
        if (src.total_tokens != null || src.active_tokens != null) setKpi('atlas-notif-kpi-tokens', src.total_tokens || src.active_tokens);
    }

    guardarToken(typeof localStorage !== 'undefined' ? localStorage.getItem('atlas_app_token') || '' : '');
    actualizarPreview();

    const formAviso = document.getElementById('atlas-notif-form-aviso');
    const formIndividual = document.getElementById('atlas-notif-form-individual');
    const formCampania = document.getElementById('atlas-notif-form-campania');
    const formInbox = document.getElementById('atlas-notif-form-inbox');

    if (formAviso) {
        formAviso.addEventListener('input', actualizarPreview);
        formAviso.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            try { showBusy(); const data = await proxy('POST', '/api/atlas/notifications', avisoPayload(), {}); actualizarKpis(data); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Aviso creado', text: 'La notificacion fue enviada a Atlas App para registrarse.' }); }
            catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo crear', text: err.message || 'Error' }); }
            finally { hideBusy(); }
        });
    }
    const btnLogin = document.getElementById('atlas-notif-btn-login');
    if (btnLogin) btnLogin.addEventListener('click', async function () {
        const usuario = String((document.getElementById('atlas-notif-login-user') || {}).value || '').trim();
        const pass = String((document.getElementById('atlas-notif-login-pass') || {}).value || '').trim();
        if (!usuario || !pass) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Captura usuario y contrasena de Atlas App.' }); return; }
        try {
            showBusy();
            const data = await proxy('POST', '/auth/login', { email: usuario, username: usuario, password: pass }, {});
            const d = data.datos || {};
            const token = d.access_token || d.token || (d.data && (d.data.access_token || d.data.token)) || '';
            if (!token) throw new Error('Atlas App no devolvio token.');
            guardarToken(token);
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Conectado', text: 'Token de Atlas App guardado para esta sesion.' });
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo iniciar sesion', text: err.message || 'Error' });
        } finally { hideBusy(); }
    });
    const btnClear = document.getElementById('atlas-notif-btn-clear-token');
    if (btnClear) btnClear.addEventListener('click', function () { guardarToken(''); setRespuesta('Token local eliminado.'); });
    if (formIndividual) formIndividual.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        try { showBusy(); const data = await proxy('POST', '/api/atlas/push-notifications/send', Object.assign({}, avisoPayload(), formToJson(formIndividual)), {}); actualizarKpis(data); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Enviado', text: 'Solicitud de envio individual enviada a Atlas App.' }); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: err.message || 'Error' }); }
        finally { hideBusy(); }
    });
    if (formCampania) formCampania.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        const datos = formToJson(formCampania);
        const payload = Object.assign({}, avisoPayload(), { filtros_json: { user_ids: lista(datos.user_ids), external_ids: lista(datos.external_ids) } });
        try { showBusy(); const data = await proxy('POST', '/api/atlas/push-campaigns/send', payload, {}); actualizarKpis(data); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Campana enviada', text: 'Solicitud de campana enviada a Atlas App.' }); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: err.message || 'Error' }); }
        finally { hideBusy(); }
    });
    if (formInbox) formInbox.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        atlasNotifInboxContext = formToJson(formInbox);
        try { showBusy(); const data = await proxy('GET', '/api/atlas/notifications/inbox', {}, atlasNotifInboxContext); renderInbox(extraerArray(data)); }
        catch (err) { renderInbox([]); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo consultar', text: err.message || 'Error' }); }
        finally { hideBusy(); }
    });
    document.addEventListener('click', async function (ev) {
        const cargar = ev.target.closest('[data-atlas-notif-load]');
        if (cargar) {
            ev.preventDefault();
            try { showBusy(); const data = await proxy(cargar.getAttribute('data-atlas-notif-method') || 'GET', cargar.getAttribute('data-atlas-notif-load'), {}, {}); actualizarKpis(data); }
            catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo cargar', text: err.message || 'Error' }); }
            finally { hideBusy(); }
            return;
        }
        const readBtn = ev.target.closest('[data-atlas-notif-read]');
        const hideBtn = ev.target.closest('[data-atlas-notif-hide]');
        if (readBtn || hideBtn) {
            ev.preventDefault();
            const id = (readBtn || hideBtn).getAttribute(readBtn ? 'data-atlas-notif-read' : 'data-atlas-notif-hide');
            const action = readBtn ? 'read' : 'hide';
            try { showBusy(); await proxy('PATCH', '/api/atlas/notifications/inbox/' + encodeURIComponent(id) + '/' + action, atlasNotifInboxContext, {}); if (formInbox) formInbox.dispatchEvent(new Event('submit', { cancelable: true })); }
            catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: err.message || 'Error' }); }
            finally { hideBusy(); }
        }
    });
    const btnDetalle = document.getElementById('atlas-notif-btn-detalle');
    if (btnDetalle) btnDetalle.addEventListener('click', async function () {
        const id = String((document.getElementById('atlas-notif-detalle-id') || {}).value || '').trim();
        if (!id) return;
        try { showBusy(); await proxy('GET', '/api/atlas/notifications/' + encodeURIComponent(id), {}, {}); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo consultar', text: err.message || 'Error' }); }
        finally { hideBusy(); }
    });
    const btnDisable = document.getElementById('atlas-notif-btn-disable-token');
    if (btnDisable) btnDisable.addEventListener('click', async function () {
        const token = String((document.getElementById('atlas-notif-token-delete') || {}).value || '').trim();
        if (!token) return;
        try { showBusy(); await proxy('DELETE', '/api/atlas/push-tokens', { expo_push_token: token }, {}); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Token desactivado', text: 'Atlas App recibio la solicitud.' }); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo desactivar', text: err.message || 'Error' }); }
        finally { hideBusy(); }
    });
})();
</script>
