<link rel="stylesheet" href="/assets/vendor/libs/quill/editor.css">
<div class="container-fluid py-3 atlas-notif-page">
    <style>
        .atlas-notif-page { color: #22303e; }
        .atlas-notif-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
        .atlas-notif-title { display: flex; align-items: center; gap: .7rem; margin: 0; color: #173756; font-size: 1.3rem; font-weight: 900; }
        .atlas-notif-title i { color: #2563eb; }
        .atlas-notif-subtitle { margin: .2rem 0 0; color: #64748b; font-size: .86rem; font-weight: 700; }
        .atlas-notif-tabs { border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; gap: .35rem; flex-wrap: wrap; }
        .atlas-notif-tabs .nav-link { border: 0; border-bottom: 3px solid transparent; color: #64748b; font-weight: 800; padding: .65rem .9rem; }
        .atlas-notif-tabs .nav-link.active { color: #173756; border-bottom-color: #2563eb; background: transparent; }
        .atlas-notif-tab-grid { display: grid; grid-template-columns: minmax(0, 1.28fr) minmax(20rem, .72fr); gap: 1rem; align-items: start; }
        .atlas-notif-card { border: 1px solid #e2e8f0; border-radius: .75rem; background: #fff; padding: 1rem; box-shadow: none; }
        .atlas-notif-card-head { display: flex; align-items: center; justify-content: space-between; gap: .8rem; margin-bottom: .85rem; }
        .atlas-notif-card h5 { display: flex; align-items: center; gap: .45rem; margin: 0; color: #173756; font-size: .9rem; font-weight: 900; text-transform: uppercase; letter-spacing: .025em; }
        .atlas-notif-step { display: inline-flex; align-items: center; justify-content: center; width: 1.65rem; height: 1.65rem; border-radius: 999px; background: #e0ecff; color: #1e40af; font-size: .78rem; font-weight: 900; }
        .atlas-notif-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        .atlas-notif-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
        .atlas-notif-field-wide { grid-column: 1 / -1; }
        .atlas-notif-actions { display: flex; justify-content: flex-end; gap: .65rem; flex-wrap: wrap; margin-top: .85rem; }
        .atlas-notif-send-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        .atlas-notif-send-box { border: 1px solid #dbe4ef; border-radius: .7rem; padding: .85rem; background: #f8fafc; }
        .atlas-notif-send-box h6 { display: flex; align-items: center; gap: .45rem; margin: 0 0 .65rem; color: #173756; font-size: .84rem; font-weight: 900; }
        .atlas-notif-preview { border-color: #dbeafe; background: linear-gradient(180deg, #f8fbff, #fff); }
        .atlas-notif-phone { border: 1px solid #dbe4ef; border-radius: 1rem; background: #f8fafc; padding: .85rem; min-height: 7rem; }
        .atlas-notif-phone-title { color: #172033; font-size: .95rem; font-weight: 900; line-height: 1.15; }
        .atlas-notif-phone-msg { color: #64748b; font-size: .82rem; font-weight: 700; line-height: 1.25; margin-top: .28rem; }
        .atlas-notif-phone-img { width: 100%; max-height: 9rem; object-fit: cover; border-radius: .6rem; margin-top: .55rem; display: none; }
        .atlas-notif-payload { margin: .75rem 0 0; border: 1px solid #e2e8f0; border-radius: .6rem; background: #0f172a; color: #e2e8f0; padding: .75rem; max-height: 12rem; overflow: auto; font-size: .74rem; line-height: 1.35; }
        .atlas-notif-tools { display: flex; align-items: center; justify-content: flex-end; gap: .65rem; flex-wrap: wrap; }
        .atlas-notif-tools .btn { min-height: 2.55rem; justify-content: center; }
        .atlas-notif-table { width: 100%; border-collapse: collapse; }
        .atlas-notif-table th { color: #566a7f; font-size: .7rem; font-weight: 900; text-transform: uppercase; border-bottom: 1px solid #dbe4ef; padding: .5rem; white-space: nowrap; }
        .atlas-notif-table td { color: #566a7f; font-size: .78rem; font-weight: 700; border-bottom: 1px solid #eef2f7; padding: .5rem; vertical-align: middle; }
        .atlas-notif-table-main { color: #22303e; font-weight: 900; line-height: 1.18; }
        .atlas-notif-table-sub { color: #94a3b8; font-size: .72rem; font-weight: 800; line-height: 1.18; margin-top: .12rem; }
        .atlas-notif-scroll { max-height: 18rem; overflow: auto; border: 1px solid #e2e8f0; border-radius: .65rem; }
        .atlas-notif-status-dot { width: .55rem; height: .55rem; border-radius: 999px; display: inline-block; background: #dc2626; }
        .atlas-notif-status-dot.is-ok { background: #16a34a; }
        .atlas-notif-muted { color: #64748b; font-size: .78rem; font-weight: 700; }
        .atlas-notif-badge { display: inline-flex; align-items: center; gap: .32rem; border-radius: 999px; padding: .22rem .65rem; font-size: .72rem; font-weight: 900; white-space: nowrap; }
        .atlas-notif-badge-ok { background: #dcfce7; color: #15803d; }
        .atlas-notif-badge-warn { background: #fee2e2; color: #b91c1c; }
        .atlas-notif-empty { text-align: center; color: #94a3b8; font-weight: 700; padding: 2rem !important; }
        .atlas-notif-row-actions { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; }
        .atlas-notif-row-actions .btn { width: 2.15rem; height: 2.15rem; border-radius: 999px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: #26344e; border-color: #26344e; box-shadow: 0 5px 12px rgba(15, 23, 42, .18); }
        .atlas-notif-advanced { margin-top: .8rem; border-top: 1px solid #eef2f7; padding-top: .75rem; }
        .atlas-notif-advanced summary { color: #566a7f; cursor: pointer; font-size: .78rem; font-weight: 900; text-transform: uppercase; letter-spacing: .02em; }
        .atlas-template-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem; }
        .atlas-template-card { width: 100%; border: 1px solid #dbe4ef; border-radius: .7rem; background: #fff; padding: .8rem; cursor: pointer; text-align: left; transition: border-color .15s ease, background-color .15s ease; }
        .atlas-template-card:hover, .atlas-template-card.is-active { border-color: #2563eb; background: #f8fbff; }
        .atlas-template-card-title { color: #173756; font-size: .84rem; font-weight: 900; line-height: 1.15; }
        .atlas-template-card-meta { color: #64748b; font-size: .72rem; font-weight: 800; margin-top: .18rem; text-transform: uppercase; letter-spacing: .02em; }
        .atlas-template-editor { min-height: 16rem; background: #fff; }
        .atlas-template-editor .ql-editor { min-height: 14rem; font-size: .9rem; }
        .atlas-template-preview { border: 1px solid #e2e8f0; border-radius: .65rem; padding: .9rem; background: #fff; min-height: 10rem; color: #22303e; }
        @media (max-width: 1199.98px) { .atlas-notif-tab-grid { grid-template-columns: 1fr; } }
        @media (max-width: 991.98px) { .atlas-notif-send-grid, .atlas-notif-grid-3, .atlas-template-list { grid-template-columns: 1fr; } }
        @media (max-width: 575.98px) {
            .atlas-notif-head, .atlas-notif-card-head { align-items: stretch; flex-direction: column; }
            .atlas-notif-tabs { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; padding-bottom: .25rem; -webkit-overflow-scrolling: touch; }
            .atlas-notif-tabs .nav-item { flex: 0 0 auto; }
            .atlas-notif-tabs .nav-link { white-space: nowrap; padding: .6rem .75rem; }
            .atlas-notif-grid { grid-template-columns: 1fr; }
            .atlas-notif-actions .btn, .atlas-notif-head .btn, .atlas-notif-tools .btn { width: 100%; justify-content: center; }
        }
    </style>

    <div class="atlas-notif-head">
        <div>
            <?php $atlasAdminOk = !empty($atlas_admin_configurada); ?>
            <h4 class="atlas-notif-title">
                <i class="fa-solid fa-bell"></i>
                <span>Notificaciones App</span>
                <span class="atlas-notif-badge <?= $atlasAdminOk ? 'atlas-notif-badge-ok' : 'atlas-notif-badge-warn' ?>">
                    <span class="atlas-notif-status-dot <?= $atlasAdminOk ? 'is-ok' : '' ?>"></span>
                    <?= $atlasAdminOk ? 'Activa' : 'No configurada' ?>
                </span>
            </h4>
            <p class="atlas-notif-subtitle">Operación de avisos push e inbox para Atlas App.</p>
        </div>
    </div>

    <ul class="nav atlas-notif-tabs" id="atlas-notif-tabs" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-notif-tab-enviar"><i class="fa-solid fa-paper-plane me-1"></i>Enviar</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-notif-tab-inbox"><i class="fa-solid fa-inbox me-1"></i>Inbox</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-notif-tab-monitoreo"><i class="fa-solid fa-chart-simple me-1"></i>Monitoreo</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-notif-tab-plantillas"><i class="fa-solid fa-layer-group me-1"></i>Plantillas</button></li>
    </ul>

    <div class="tab-content p-0">
        <div class="tab-pane fade" id="atlas-notif-tab-plantillas" role="tabpanel">
            <div class="atlas-notif-tab-grid">
                <div class="atlas-notif-card">
                    <div class="atlas-notif-card-head">
                        <h5><span class="atlas-notif-step">1</span><i class="fa-solid fa-layer-group"></i>Plantillas</h5>
                        <button type="button" class="btn btn-primary btn-sm" id="atlas-template-btn-nueva"><i class="fa-solid fa-plus me-1"></i>Nueva plantilla</button>
                    </div>
                    <div class="atlas-template-list" id="atlas-template-list">
                        <div class="atlas-notif-empty">Cargando plantillas...</div>
                    </div>
                </div>
                <div class="atlas-notif-card">
                    <div class="atlas-notif-card-head">
                        <h5><i class="fa-solid fa-pen-nib"></i>Editor visual</h5>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="atlas-template-btn-usar"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Usar en aviso</button>
                    </div>
                    <form id="atlas-template-form" autocomplete="off">
                        <input type="hidden" name="id">
                        <div class="atlas-notif-grid">
                            <div><label class="form-label">Nombre *</label><input type="text" class="form-control" name="nombre" required placeholder="Ej. Feliz cumpleaños"></div>
                            <div><label class="form-label">Tipo *</label>
                                <select class="form-select" name="categoria" required>
                                    <option value="cumpleanos">Feliz cumpleaños</option>
                                    <option value="avance_venta">Avance de venta</option>
                                    <option value="notificacion_especial">Notificación especial</option>
                                    <option value="atencion_colaborador">Atención al colaborador</option>
                                </select>
                            </div>
                            <div><label class="form-label">Asunto</label><input type="text" class="form-control" name="asunto" placeholder="Título sugerido para el aviso"></div>
                            <div><label class="form-label">Imagen URL</label><input type="url" class="form-control" name="imagen_url" placeholder="https://..."></div>
                            <div class="atlas-notif-field-wide"><label class="form-label">Mensaje corto</label><textarea class="form-control" name="mensaje_texto" rows="2" placeholder="Texto corto para push o resumen"></textarea></div>
                            <div><label class="form-label">Estatus</label><select class="form-select" name="activo"><option value="1">Activa</option><option value="0">Inactiva</option></select></div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Contenido HTML *</label>
                            <div id="atlas-template-editor" class="atlas-template-editor"></div>
                        </div>
                        <div class="atlas-notif-actions">
                            <button type="button" class="btn btn-outline-primary" id="atlas-template-btn-imagen"><i class="fa-solid fa-image me-1"></i>Insertar imagen URL</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar plantilla</button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <label class="form-label">Vista previa HTML</label>
                        <div class="atlas-template-preview" id="atlas-template-preview"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade show active" id="atlas-notif-tab-enviar" role="tabpanel">
            <div class="atlas-notif-tab-grid">
                <div class="atlas-notif-card">
                    <div class="atlas-notif-card-head">
                        <h5><span class="atlas-notif-step">1</span><i class="fa-solid fa-pen-to-square"></i>Contenido del envío</h5>
                        <span class="atlas-notif-muted">Este contenido se usa para push e inbox.</span>
                    </div>
                    <form id="atlas-notif-form-aviso" autocomplete="off">
                        <input type="hidden" name="type" value="aviso_especial">
                        <input type="hidden" name="notification_type" value="push">
                        <div class="atlas-notif-grid">
                            <div class="atlas-notif-field-wide"><label class="form-label">Título *</label><input type="text" class="form-control" name="titulo" required placeholder="Ej. Nuevo aviso disponible"></div>
                            <div class="atlas-notif-field-wide"><label class="form-label">Mensaje *</label><textarea class="form-control" name="mensaje" rows="3" required placeholder="Mensaje corto para mostrar en la app"></textarea></div>
                            <div><label class="form-label">Imagen URL</label><input type="url" class="form-control" name="imagen_url" placeholder="https://..."></div>
                            <div><label class="form-label">ID crédito</label><input type="text" class="form-control" name="id_credito" placeholder="Opcional"></div>
                        </div>
                        <details class="atlas-notif-advanced">
                            <summary>Datos adicionales</summary>
                            <div class="atlas-notif-grid-3 mt-3">
                                <div><label class="form-label">Monto</label><input type="number" step="0.01" class="form-control" name="monto" placeholder="Opcional"></div>
                                <div><label class="form-label">Semana</label><input type="text" class="form-control" name="semana" placeholder="Opcional"></div>
                                <div class="atlas-notif-field-wide"><label class="form-label">HTML / contenido extendido</label><textarea class="form-control" name="html" rows="3" placeholder="Contenido del aviso en inbox"></textarea></div>
                            </div>
                        </details>
                    </form>
                </div>
                <div class="atlas-notif-card atlas-notif-preview">
                    <div class="atlas-notif-card-head">
                        <h5><i class="fa-solid fa-eye"></i>Vista previa</h5>
                        <span class="atlas-notif-muted">Así se arma el aviso.</span>
                    </div>
                    <div class="atlas-notif-phone">
                        <div class="atlas-notif-phone-title" id="atlas-notif-preview-title">Título de la notificación</div>
                        <div class="atlas-notif-phone-msg" id="atlas-notif-preview-msg">Mensaje corto para el usuario.</div>
                        <img src="" alt="" class="atlas-notif-phone-img" id="atlas-notif-preview-img">
                    </div>
                    <pre class="atlas-notif-payload" id="atlas-notif-payload-preview">{}</pre>
                </div>
            </div>

            <div class="atlas-notif-card mt-3">
                <div class="atlas-notif-card-head">
                    <h5><span class="atlas-notif-step">2</span><i class="fa-solid fa-paper-plane"></i>Elegir destinatarios</h5>
                    <span class="atlas-notif-muted">Captura el contenido y elige a quién se envía.</span>
                </div>
                <div class="atlas-notif-send-grid">
                    <div class="atlas-notif-send-box">
                        <h6><i class="fa-solid fa-user-check"></i>Enviar a una persona</h6>
                        <form id="atlas-notif-form-individual" autocomplete="off">
                            <div class="atlas-notif-grid">
                                <div><label class="form-label">User ID</label><input type="text" class="form-control" name="user_id" placeholder="persona.id"></div>
                                <div><label class="form-label">External ID</label><input type="text" class="form-control" name="external_id" placeholder="Número empleado"></div>
                            </div>
                            <div class="atlas-notif-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>Enviar individual</button></div>
                        </form>
                    </div>
                    <div class="atlas-notif-send-box">
                        <h6><i class="fa-solid fa-users-rays"></i>Enviar campaña</h6>
                        <form id="atlas-notif-form-campania" autocomplete="off">
                            <div class="atlas-notif-grid">
                                <div><label class="form-label">User IDs</label><textarea class="form-control" name="user_ids" rows="2" placeholder="1133, 7"></textarea></div>
                                <div><label class="form-label">External IDs</label><textarea class="form-control" name="external_ids" rows="2" placeholder="999999704, 525"></textarea></div>
                            </div>
                            <div class="atlas-notif-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-bullhorn me-1"></i>Enviar campaña</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="atlas-notif-tab-inbox" role="tabpanel">
            <div class="atlas-notif-card">
                <div class="atlas-notif-card-head">
                    <h5><span class="atlas-notif-step">3</span><i class="fa-solid fa-inbox"></i>Inbox por usuario</h5>
                    <span class="atlas-notif-muted">Consulta y actualiza notificaciones del inbox.</span>
                </div>
                <form id="atlas-notif-form-inbox" autocomplete="off">
                    <div class="atlas-notif-grid">
                        <div><label class="form-label">User ID</label><input type="text" class="form-control" name="user_id" placeholder="1133"></div>
                        <div><label class="form-label">External ID</label><input type="text" class="form-control" name="external_id" placeholder="999999704"></div>
                    </div>
                    <div class="atlas-notif-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Consultar inbox</button></div>
                </form>
                <div class="atlas-notif-scroll mt-3">
                    <table class="atlas-notif-table">
                        <thead><tr><th>Estado</th><th>Título</th><th>Fecha</th><th>Acciones</th></tr></thead>
                        <tbody id="atlas-notif-inbox-body"><tr><td colspan="4" class="atlas-notif-empty">Consulta un usuario para ver su inbox.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="atlas-notif-tab-monitoreo" role="tabpanel">
            <div class="atlas-notif-card">
                <div class="atlas-notif-card-head">
                    <h5><i class="fa-solid fa-users-viewfinder"></i>Usuarios disponibles</h5>
                    <span class="atlas-notif-muted">Usuarios con dispositivo activo para recibir notificaciones.</span>
                </div>
                <div class="atlas-notif-tools">
                    <button type="button" class="btn btn-primary" data-atlas-usuarios-disponibles="1"><i class="fa-solid fa-rotate me-1"></i>Actualizar usuarios</button>
                </div>
                <div class="atlas-notif-scroll mt-3">
                    <table class="atlas-notif-table">
                        <thead><tr><th>Usuario</th><th>Identificador</th><th>Dispositivo</th><th>Última actividad</th></tr></thead>
                        <tbody id="atlas-notif-usuarios-body"><tr><td colspan="4" class="atlas-notif-empty">Actualiza para ver usuarios disponibles.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/assets/vendor/libs/quill/quill.js"></script>
<script>
(function () {
    let atlasNotifInboxContext = { user_id: '', external_id: '' };
    let atlasPlantillas = [];
    let atlasPlantillaActual = null;
    let atlasTemplateQuill = null;

    function esc(v) { return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
    function setKpi(id, value) { const el = document.getElementById(id); if (el) el.textContent = Number(value || 0).toLocaleString('es-MX'); }
    function setTexto(id, value) { const el = document.getElementById(id); if (el) el.textContent = value == null ? '' : String(value); }
    function formToJson(form) { const data = {}; Array.from(new FormData(form).entries()).forEach(pair => { data[pair[0]] = pair[1]; }); return data; }
    function showBusy() { if (typeof Swal !== 'undefined') Swal.fire({ title: 'Procesando su petición', text: 'Espere un momento...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: function () { Swal.showLoading(); } }); }
    function hideBusy() {
        if (typeof Swal === 'undefined') return;
        const title = document.getElementById('swal2-title');
        if (title && title.textContent === 'Procesando su petición') Swal.close();
    }
    function lista(v) { return String(v || '').split(/[,\n;]/).map(x => x.trim()).filter(Boolean); }
    async function atlasJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(body || {})
        });
        const data = await res.json();
        if (!data || data.success === false) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo ejecutar la acción.');
        return data;
    }
    function categoriaTexto(v) {
        const mapa = {
            cumpleanos: 'Feliz cumpleaños',
            avance_venta: 'Avance de venta',
            notificacion_especial: 'Notificación especial',
            atencion_colaborador: 'Atención al colaborador'
        };
        return mapa[v] || v || 'Plantilla';
    }
    function templateHtmlActual() {
        if (!atlasTemplateQuill) return '';
        return atlasTemplateQuill.root.innerHTML || '';
    }
    function renderTemplatePreview() {
        const preview = document.getElementById('atlas-template-preview');
        if (preview) preview.innerHTML = templateHtmlActual() || '<span class="atlas-notif-muted">Sin contenido.</span>';
    }
    function setTemplateForm(row) {
        const form = document.getElementById('atlas-template-form');
        if (!form) return;
        form.elements.id.value = row && row.id ? row.id : '';
        form.elements.nombre.value = row && row.nombre ? row.nombre : '';
        form.elements.categoria.value = row && row.categoria ? row.categoria : 'notificacion_especial';
        form.elements.asunto.value = row && row.asunto ? row.asunto : '';
        form.elements.imagen_url.value = row && row.imagen_url ? row.imagen_url : '';
        form.elements.mensaje_texto.value = row && row.mensaje_texto ? row.mensaje_texto : '';
        form.elements.activo.value = String(row && row.activo != null ? row.activo : 1);
        if (atlasTemplateQuill) {
            atlasTemplateQuill.root.innerHTML = row && row.html ? row.html : '<h2>Nueva plantilla</h2><p>Escribe aquí el mensaje.</p>';
        }
        atlasPlantillaActual = row || null;
        renderTemplatePreview();
    }
    function renderPlantillas() {
        const list = document.getElementById('atlas-template-list');
        if (!list) return;
        if (!atlasPlantillas.length) {
            list.innerHTML = '<div class="atlas-notif-empty">No hay plantillas registradas.</div>';
            return;
        }
        list.innerHTML = atlasPlantillas.map(row => {
            const active = atlasPlantillaActual && String(atlasPlantillaActual.id || '') === String(row.id || '') ? ' is-active' : '';
            const estado = Number(row.activo || 0) === 1 ? 'Activa' : 'Inactiva';
            return '<button type="button" class="atlas-template-card' + active + '" data-template-id="' + esc(row.id) + '">'
                + '<div class="atlas-template-card-title">' + esc(row.nombre || 'Plantilla') + '</div>'
                + '<div class="atlas-template-card-meta">' + esc(categoriaTexto(row.categoria)) + ' · ' + esc(estado) + '</div>'
                + '</button>';
        }).join('');
    }
    async function cargarPlantillas() {
        const data = await atlasJson('/Atlas/getPlantillasNotificaciones', {});
        atlasPlantillas = Array.isArray(data.datos) ? data.datos : [];
        if (!atlasPlantillaActual && atlasPlantillas.length) atlasPlantillaActual = atlasPlantillas[0];
        renderPlantillas();
        if (atlasPlantillaActual) setTemplateForm(atlasPlantillaActual);
        return data;
    }
    function initTemplateEditor() {
        if (atlasTemplateQuill || typeof Quill === 'undefined') return;
        atlasTemplateQuill = new Quill('#atlas-template-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
        atlasTemplateQuill.on('text-change', renderTemplatePreview);
    }
    function usarPlantillaEnAviso(row) {
        const form = document.getElementById('atlas-notif-form-aviso');
        if (!form || !row) return;
        form.elements.titulo.value = row.asunto || row.nombre || '';
        form.elements.mensaje.value = row.mensaje_texto || '';
        form.elements.imagen_url.value = row.imagen_url || '';
        form.elements.html.value = row.html || '';
        actualizarPreview();
        const enviarBtn = document.querySelector('[data-bs-target="#atlas-notif-tab-enviar"]');
        if (enviarBtn && window.bootstrap) bootstrap.Tab.getOrCreateInstance(enviarBtn).show();
    }
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
        setTexto('atlas-notif-preview-title', p.titulo || 'Título de la notificación');
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
            body: JSON.stringify({ method: method, path: path, body: body || {}, query: query || {} })
        });
        const data = await res.json();
        if (!data || data.success === false) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo ejecutar la acción.');
        return data;
    }
    function extraerArray(data) {
        const d = data && data.datos ? data.datos : data;
        if (Array.isArray(d)) return d;
        if (d && Array.isArray(d.datos)) return d.datos;
        if (d && Array.isArray(d.data)) return d.data;
        if (d && Array.isArray(d.notifications)) return d.notifications;
        if (d && Array.isArray(d.inbox)) return d.inbox;
        if (d && Array.isArray(d.tokens)) return d.tokens;
        if (d && Array.isArray(d.push_tokens)) return d.push_tokens;
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
            const titulo = row.titulo || row.title || row.mensaje || row.message || 'Notificación';
            const fecha = row.created_at || row.fecha_alta || row.sent_at || '';
            const estado = read ? '<span class="atlas-notif-badge atlas-notif-badge-ok"><i class="fa-solid fa-check-double"></i>Leída</span>' : '<span class="atlas-notif-badge atlas-notif-badge-warn"><i class="fa-solid fa-circle"></i>No leída</span>';
            return '<tr><td>' + estado + '</td><td>' + esc(titulo) + '</td><td>' + esc(fecha || '-') + '</td><td><div class="atlas-notif-row-actions"><button type="button" class="btn btn-sm btn-primary" title="Marcar leída" data-atlas-notif-read="' + esc(id) + '"><i class="fa-solid fa-check-double"></i></button><button type="button" class="btn btn-sm btn-primary" title="Ocultar" data-atlas-notif-hide="' + esc(id) + '"><i class="fa-solid fa-eye-slash"></i></button></div></td></tr>';
        }).join('');
    }
    function textoToken(row) {
        return row.token_corto || row.expo_push_token || row.push_token || row.token || row.expo_token || row.device_token || '';
    }
    function renderUsuariosDisponibles(items) {
        const body = document.getElementById('atlas-notif-usuarios-body');
        if (!body) return;
        const activos = (items || []).filter(row => {
            const activo = row.activo ?? row.active ?? row.is_active ?? row.enabled ?? 1;
            return String(activo) !== '0' && String(activo).toLowerCase() !== 'false';
        });
        if (!activos.length) {
            body.innerHTML = '<tr><td colspan="4" class="atlas-notif-empty">No hay usuarios disponibles para notificar.</td></tr>';
            return;
        }
        body.innerHTML = activos.map(row => {
            const nombre = row.nombre || row.name || row.user_name || row.usuario || row.email || 'Usuario sin nombre';
            const userId = row.user_id || row.persona_id || row.id_usuario || row.id || '';
            const externalId = row.external_id || row.numero_empleado || row.employee_number || '';
            const plataforma = row.platform || row.plataforma || row.device_platform || row.device_type || 'App';
            const token = textoToken(row);
            const tokenCorto = row.token_corto || (token ? (String(token).slice(0, 18) + '...') : 'Sin token visible');
            const fecha = row.last_seen_at_fmt || row.last_seen_at || row.updated_at_fmt || row.updated_at || row.fecha_actualizacion || row.created_at_fmt || row.created_at || row.fecha_alta || '-';
            return '<tr>'
                + '<td><div class="atlas-notif-table-main">' + esc(nombre) + '</div><div class="atlas-notif-table-sub">' + esc(row.email || row.correo || '') + '</div></td>'
                + '<td><div class="atlas-notif-table-main">' + esc(externalId ? 'External ID ' + externalId : 'User ID ' + (userId || '-')) + '</div><div class="atlas-notif-table-sub">' + esc(userId ? 'User ID ' + userId : '') + '</div></td>'
                + '<td><div class="atlas-notif-table-main"><i class="fa-solid fa-mobile-screen me-1"></i>' + esc(plataforma) + '</div><div class="atlas-notif-table-sub">' + esc(row.device_name || row.dispositivo || tokenCorto) + '</div><div class="atlas-notif-table-sub">' + esc(row.device_name ? tokenCorto : '') + '</div></td>'
                + '<td>' + esc(fecha) + '</td>'
                + '</tr>';
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
    async function cargarUsuariosDisponibles(silencioso) {
        if (!silencioso) showBusy();
        try {
            const data = await atlasJson('/Atlas/getUsuariosNotificacionesDisponibles', {});
            renderUsuariosDisponibles(Array.isArray(data.datos) ? data.datos : []);
            actualizarKpis({ datos: { active_tokens: data.totales && data.totales.total ? data.totales.total : 0 } });
            return data;
        } catch (err) {
            renderUsuariosDisponibles([]);
            if (!silencioso && typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'No se pudo cargar', text: err.message || 'Error' });
            }
            return null;
        } finally {
            if (!silencioso) hideBusy();
        }
    }

    actualizarPreview();

    const formAviso = document.getElementById('atlas-notif-form-aviso');
    const formIndividual = document.getElementById('atlas-notif-form-individual');
    const formCampania = document.getElementById('atlas-notif-form-campania');
    const formInbox = document.getElementById('atlas-notif-form-inbox');
    const formTemplate = document.getElementById('atlas-template-form');

    initTemplateEditor();
    cargarPlantillas().catch(err => {
        const list = document.getElementById('atlas-template-list');
        if (list) list.innerHTML = '<div class="atlas-notif-empty">' + esc(err.message || 'No se pudieron cargar las plantillas.') + '</div>';
    });
    cargarUsuariosDisponibles(true);

    if (formAviso) {
        formAviso.addEventListener('input', actualizarPreview);
        formAviso.addEventListener('submit', function (ev) { ev.preventDefault(); });
    }
    if (formIndividual) formIndividual.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        try { showBusy(); const data = await proxy('POST', '/api/atlas/push-notifications/send', Object.assign({}, avisoPayload(), formToJson(formIndividual)), {}); actualizarKpis(data); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Enviado', text: 'Solicitud de envío individual enviada a Atlas App.' }); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: err.message || 'Error' }); }
        finally { hideBusy(); }
    });
    if (formCampania) formCampania.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        const datos = formToJson(formCampania);
        const payload = Object.assign({}, avisoPayload(), { filtros_json: { user_ids: lista(datos.user_ids), external_ids: lista(datos.external_ids) } });
        try { showBusy(); const data = await proxy('POST', '/api/atlas/push-campaigns/send', payload, {}); actualizarKpis(data); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Campaña enviada', text: 'Solicitud de campaña enviada a Atlas App.' }); }
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
    if (formTemplate) formTemplate.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        const payload = formToJson(formTemplate);
        payload.html = templateHtmlActual();
        try {
            showBusy();
            const data = await atlasJson('/Atlas/guardarPlantillaNotificacion', payload);
            await cargarPlantillas();
            const found = atlasPlantillas.find(row => String(row.id || '') === String(data.id || payload.id || ''));
            if (found) setTemplateForm(found);
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Plantilla guardada', text: 'La plantilla quedó disponible para usarse en avisos.' });
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: err.message || 'Error' });
        } finally { hideBusy(); }
    });
    document.addEventListener('click', async function (ev) {
        const templateCard = ev.target.closest('[data-template-id]');
        if (templateCard) {
            ev.preventDefault();
            const id = templateCard.getAttribute('data-template-id');
            const row = atlasPlantillas.find(item => String(item.id || '') === String(id || ''));
            if (row) {
                setTemplateForm(row);
                renderPlantillas();
            }
            return;
        }
        const nuevaTemplate = ev.target.closest('#atlas-template-btn-nueva');
        if (nuevaTemplate) {
            ev.preventDefault();
            setTemplateForm({
                id: '',
                nombre: '',
                categoria: 'notificacion_especial',
                asunto: '',
                mensaje_texto: '',
                imagen_url: '',
                html: '<h2>Nueva plantilla</h2><p>Escribe aquí el mensaje.</p>',
                activo: 1
            });
            renderPlantillas();
            return;
        }
        const usarTemplate = ev.target.closest('#atlas-template-btn-usar');
        if (usarTemplate) {
            ev.preventDefault();
            const payload = formTemplate ? formToJson(formTemplate) : {};
            payload.html = templateHtmlActual();
            usarPlantillaEnAviso(payload);
            return;
        }
        const insertarImagen = ev.target.closest('#atlas-template-btn-imagen');
        if (insertarImagen) {
            ev.preventDefault();
            const url = window.prompt('Pega la URL de la imagen');
            if (url && atlasTemplateQuill) {
                const range = atlasTemplateQuill.getSelection(true);
                atlasTemplateQuill.insertEmbed(range ? range.index : atlasTemplateQuill.getLength(), 'image', url);
            }
            return;
        }
        const cargarUsuarios = ev.target.closest('[data-atlas-usuarios-disponibles]');
        if (cargarUsuarios) {
            ev.preventDefault();
            await cargarUsuariosDisponibles(false);
            return;
        }
        const cargar = ev.target.closest('[data-atlas-notif-load]');
        if (cargar) {
            ev.preventDefault();
            try {
                showBusy();
                const path = cargar.getAttribute('data-atlas-notif-load');
                const data = await proxy(cargar.getAttribute('data-atlas-notif-method') || 'GET', path, {}, {});
                actualizarKpis(data);
                if (path === '/api/atlas/push-tokens') renderUsuariosDisponibles(extraerArray(data));
            }
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
        try { showBusy(); await proxy('DELETE', '/api/atlas/push-tokens', { expo_push_token: token }, {}); if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Token desactivado', text: 'Atlas App recibió la solicitud.' }); }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo desactivar', text: err.message || 'Error' }); }
        finally { hideBusy(); }
    });
})();
</script>
