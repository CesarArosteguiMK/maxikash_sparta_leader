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
        .atlas-notif-grid-main { grid-template-columns: minmax(11rem, .8fr) minmax(12rem, .9fr) minmax(16rem, 1.3fr); }
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
        .atlas-dest-panel { border: 1px solid #dbe4ef; border-radius: .7rem; padding: .85rem; background: #f8fafc; }
        .atlas-dest-list { max-height: 16rem; overflow: auto; border: 1px solid #dbe4ef; border-radius: .65rem; background: #fff; }
        .atlas-dest-row { display: flex; align-items: center; gap: .55rem; padding: .55rem .7rem; border-bottom: 1px solid #eef2f7; cursor: pointer; }
        .atlas-dest-row:last-child { border-bottom: 0; }
        .atlas-dest-row:hover { background: #f8fbff; }
        .atlas-dest-row input { flex: 0 0 auto; }
        .atlas-dest-name { color: #22303e; font-size: .82rem; font-weight: 900; line-height: 1.12; }
        .atlas-dest-meta { color: #94a3b8; font-size: .7rem; font-weight: 800; line-height: 1.15; margin-top: .1rem; }
        .atlas-dest-mode[hidden] { display: none !important; }
        .atlas-notif-page .select2-container { width: 100% !important; }
        @media (max-width: 1199.98px) { .atlas-notif-tab-grid { grid-template-columns: 1fr; } }
        @media (max-width: 991.98px) { .atlas-notif-send-grid, .atlas-notif-grid-3, .atlas-template-list { grid-template-columns: 1fr; } }
        @media (max-width: 575.98px) {
            .atlas-notif-head, .atlas-notif-card-head { align-items: stretch; flex-direction: column; }
            .atlas-notif-tabs { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; padding-bottom: .25rem; -webkit-overflow-scrolling: touch; }
            .atlas-notif-tabs .nav-item { flex: 0 0 auto; }
            .atlas-notif-tabs .nav-link { white-space: nowrap; padding: .6rem .75rem; }
            .atlas-notif-grid, .atlas-notif-grid-main { grid-template-columns: 1fr; }
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
        <li class="nav-item" role="presentation"><button class="nav-link active" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-notif-tab-enviar"><i class="fa-solid fa-paper-plane me-1"></i>Notificaciones</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-notif-tab-historial"><i class="fa-solid fa-clock-rotate-left me-1"></i>Historial</button></li>
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
                        <h5><i class="fa-solid fa-pen-to-square"></i>Contenido del envío</h5>
                        <span class="atlas-notif-muted">Este contenido se usa para push e inbox.</span>
                    </div>
                    <form id="atlas-notif-form-aviso" autocomplete="off">
                        <input type="hidden" name="type" value="aviso_especial">
                        <div class="atlas-notif-grid atlas-notif-grid-main">
                            <div><label class="form-label">Tipo de envío *</label>
                                <select class="form-select" id="atlas-notif-modo-envio" name="modo_envio">
                                    <option value="unico">Mensaje único</option>
                                    <option value="campania">Por campaña</option>
                                </select>
                            </div>
                            <div><label class="form-label">Plantilla</label>
                                <select class="form-select" id="atlas-notif-template-rapida">
                                    <option value="">Sin plantilla</option>
                                </select>
                            </div>
                            <div><label class="form-label">Título *</label><input type="text" class="form-control" name="titulo" required placeholder="Ej. Nuevo aviso disponible"></div>
                            <div class="atlas-notif-field-wide"><label class="form-label">Mensaje *</label><textarea class="form-control" name="mensaje" rows="3" required placeholder="Mensaje corto para mostrar en la app"></textarea></div>
                            <div class="atlas-notif-field-wide"><label class="form-label">Imagen URL</label><input type="url" class="form-control" name="imagen_url" placeholder="https://..."></div>
                        </div>
                        <input type="hidden" name="html" value="">
                        <div class="mt-3">
                            <div class="atlas-notif-card-head mb-2">
                                <h5><i class="fa-solid fa-paper-plane"></i>Elegir destinatarios</h5>
                                <span class="atlas-notif-muted">Elige a quién se envía.</span>
                            </div>
                            <div class="atlas-dest-panel atlas-dest-mode" id="atlas-dest-unico">
                                <label class="form-label">Destinatario *</label>
                                <select class="form-select" id="atlas-notif-destinatario-unico">
                                    <option value="">Selecciona destinatario</option>
                                </select>
                            </div>
                            <div class="atlas-dest-panel atlas-dest-mode" id="atlas-dest-campania" hidden>
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                                    <label class="form-label mb-0">Usuarios activos *</label>
                                    <label class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="atlas-dest-todos">
                                        <span class="form-check-label">Seleccionar todos</span>
                                    </label>
                                </div>
                                <input type="search" class="form-control mb-2" id="atlas-dest-buscar" placeholder="Buscar por nombre, usuario o external ID">
                                <div class="atlas-dest-list" id="atlas-dest-lista">
                                    <div class="atlas-notif-empty">Cargando usuarios...</div>
                                </div>
                            </div>
                        </div>
                        <div class="atlas-notif-actions">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>Enviar notificación</button>
                        </div>
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
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="atlas-notif-tab-historial" role="tabpanel">
            <div class="atlas-notif-card">
                <div class="atlas-notif-card-head">
                    <h5><i class="fa-solid fa-clock-rotate-left"></i>Historial de envíos</h5>
                    <span class="atlas-notif-muted">Registro general de notificaciones enviadas.</span>
                </div>
                <div class="atlas-notif-tools">
                    <button type="button" class="btn btn-primary" data-atlas-historial-notificaciones="1"><i class="fa-solid fa-rotate me-1"></i>Actualizar historial</button>
                </div>
                <div class="atlas-notif-scroll mt-3">
                    <table class="atlas-notif-table">
                        <thead>
                            <tr>
                                <th>Alcance</th>
                                <th>Notificación</th>
                                <th>Destinatarios</th>
                                <th>Lectura</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="atlas-notif-historial-body"><tr><td colspan="5" class="atlas-notif-empty">Cargando historial...</td></tr></tbody>
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
    let atlasPlantillas = [];
    let atlasPlantillaActual = null;
    let atlasTemplateQuill = null;
    let atlasUsuariosDisponibles = [];
    let atlasDestinatariosSeleccionados = new Set();

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
    function renderTemplateSelectRapido() {
        const sel = document.getElementById('atlas-notif-template-rapida');
        if (!sel) return;
        const actual = sel.value || '';
        const activas = atlasPlantillas.filter(row => Number(row.activo == null ? 1 : row.activo) === 1);
        sel.innerHTML = '<option value="">Sin plantilla</option>' + activas.map(row => {
            return '<option value="' + esc(row.id || '') + '">' + esc(row.nombre || row.asunto || 'Plantilla') + '</option>';
        }).join('');
        if (actual && activas.some(row => String(row.id || '') === String(actual))) sel.value = actual;
    }
    async function cargarPlantillas() {
        const data = await atlasJson('/Atlas/getPlantillasNotificaciones', {});
        atlasPlantillas = Array.isArray(data.datos) ? data.datos : [];
        if (!atlasPlantillaActual && atlasPlantillas.length) atlasPlantillaActual = atlasPlantillas[0];
        renderPlantillas();
        renderTemplateSelectRapido();
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
        const payload = {
            titulo: d.titulo || '',
            mensaje: d.mensaje || '',
            type: d.type || 'aviso_especial',
            data_json: { screen: 'NotificacionEspecial' }
        };
        const imagenUrl = String(d.imagen_url || '').trim();
        const html = String(d.html || '').trim();
        if (imagenUrl) payload.imagen_url = imagenUrl;
        if (html) payload.html = html;
        return payload;
    }
    function atlasRequestId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return 'sparta-' + window.crypto.randomUUID();
        }
        return 'sparta-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10);
    }
    function payloadEnvioUnificado(destinatarios) {
        const base = avisoPayload();
        return Object.assign({}, base, {
            request_id: atlasRequestId(),
            destinatarios: {
                user_ids: Array.isArray(destinatarios.user_ids) ? destinatarios.user_ids : [],
                external_ids: Array.isArray(destinatarios.external_ids) ? destinatarios.external_ids : []
            }
        });
    }
    function validarContenidoAviso() {
        const p = avisoPayload();
        if (!String(p.titulo || '').trim() || !String(p.mensaje || '').trim()) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Contenido requerido', text: 'Captura titulo y mensaje antes de enviar.' });
            return false;
        }
        return true;
    }
    function destinatariosDesdeUsuarios(items) {
        const userIds = [];
        const externalIds = [];
        (items || []).forEach(row => {
            const userId = usuarioId(row);
            const externalId = usuarioExternal(row);
            if (userId) userIds.push(String(userId));
            else if (externalId) externalIds.push(String(externalId));
        });
        return { user_ids: Array.from(new Set(userIds)), external_ids: Array.from(new Set(externalIds)) };
    }
    function aplicarModoEnvio() {
        const modo = document.getElementById('atlas-notif-modo-envio') ? document.getElementById('atlas-notif-modo-envio').value : 'unico';
        const unico = document.getElementById('atlas-dest-unico');
        const campania = document.getElementById('atlas-dest-campania');
        if (unico) unico.hidden = modo !== 'unico';
        if (campania) campania.hidden = modo !== 'campania';
        renderDestinatariosRapidos(document.getElementById('atlas-dest-buscar') ? document.getElementById('atlas-dest-buscar').value : '');
    }
    function actualizarPreview() {
        const p = avisoPayload();
        setTexto('atlas-notif-preview-title', p.titulo || 'Título de la notificación');
        setTexto('atlas-notif-preview-msg', p.mensaje || 'Mensaje corto para el usuario.');
        const img = document.getElementById('atlas-notif-preview-img');
        if (img) { img.style.display = p.imagen_url ? 'block' : 'none'; img.src = p.imagen_url || ''; }
    }
    async function proxy(method, path, body, query) {
        const res = await fetch('/Atlas/notificacionesAppProxy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ method: method, path: path, body: body || {}, query: query || {} })
        });
        const data = await res.json();
        if (!data || data.success === false) {
            const detalle = data && data.datos
                ? (data.datos.detail || data.datos.message || data.datos.error || data.datos.mensaje)
                : '';
            const texto = Array.isArray(detalle) ? detalle.map(item => item.msg || JSON.stringify(item)).join(' · ') : String(detalle || '');
            throw new Error(texto || (data && (data.mensaje || data.error)) || 'No se pudo ejecutar la acción.');
        }
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
    function textoToken(row) {
        return row.token_corto || row.expo_push_token || row.push_token || row.token || row.expo_token || row.device_token || '';
    }
    function usuarioNombre(row) {
        return row.nombre || row.name || row.user_name || row.usuario || row.email || row.correo || 'Usuario sin nombre';
    }
    function usuarioId(row) {
        return row.user_id || row.persona_id || row.id_usuario || row.id || '';
    }
    function usuarioExternal(row) {
        return row.external_id || row.numero_empleado || row.employee_number || '';
    }
    function usuarioActivo(row) {
        const activo = row.activo ?? row.active ?? row.is_active ?? row.enabled ?? 1;
        return String(activo) !== '0' && String(activo).toLowerCase() !== 'false';
    }
    function usuarioMeta(row) {
        const partes = [];
        const externalId = usuarioExternal(row);
        const userId = usuarioId(row);
        if (externalId) partes.push('External ID ' + externalId);
        if (userId) partes.push('User ID ' + userId);
        if (row.platform || row.plataforma) partes.push(row.platform || row.plataforma);
        return partes.join(' · ');
    }
    function usuariosActivosDisponibles() {
        return atlasUsuariosDisponibles.filter(usuarioActivo);
    }
    function destruirSelect2(el) {
        if (!el || !window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
        if (jQuery(el).data('select2')) jQuery(el).select2('destroy');
    }
    function iniciarSelect2(el) {
        if (!el || !window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
        jQuery(el).select2({ placeholder: 'Selecciona destinatario', width: '100%' });
    }
    function renderDestinatariosRapidos(filtro) {
        const activos = usuariosActivosDisponibles();
        const sel = document.getElementById('atlas-notif-destinatario-unico');
        if (sel) {
            const valorActual = sel.value || '';
            destruirSelect2(sel);
            sel.innerHTML = '<option value="">Selecciona destinatario</option>' + activos.map((row, idx) => {
                return '<option value="' + esc(String(idx)) + '">' + esc(usuarioNombre(row) + (usuarioMeta(row) ? ' · ' + usuarioMeta(row) : '')) + '</option>';
            }).join('');
            if (valorActual && activos[Number(valorActual)]) sel.value = valorActual;
            iniciarSelect2(sel);
        }

        const list = document.getElementById('atlas-dest-lista');
        if (!list) return;
        const q = String(filtro || '').trim().toLowerCase();
        const visibles = activos.map((row, idx) => ({ row, idx })).filter(item => {
            if (!q) return true;
            return [usuarioNombre(item.row), usuarioMeta(item.row), item.row.correo || item.row.email || '', textoToken(item.row)]
                .join(' ').toLowerCase().includes(q);
        });
        if (!visibles.length) {
            list.innerHTML = '<div class="atlas-notif-empty">No hay usuarios disponibles con ese filtro.</div>';
            return;
        }
        list.innerHTML = visibles.map(item => {
            const checked = atlasDestinatariosSeleccionados.has(String(item.idx)) ? ' checked' : '';
            return '<label class="atlas-dest-row">'
                + '<input class="form-check-input" type="checkbox" data-atlas-dest-idx="' + esc(String(item.idx)) + '"' + checked + '>'
                + '<span><span class="atlas-dest-name">' + esc(usuarioNombre(item.row)) + '</span>'
                + '<span class="atlas-dest-meta">' + esc(usuarioMeta(item.row) || 'Usuario activo') + '</span></span>'
                + '</label>';
        }).join('');
    }
    function renderUsuariosDisponibles(items) {
        const body = document.getElementById('atlas-notif-usuarios-body');
        if (!body) return;
        const activos = (items || []).filter(usuarioActivo);
        if (!activos.length) {
            body.innerHTML = '<tr><td colspan="4" class="atlas-notif-empty">No hay usuarios disponibles para notificar.</td></tr>';
            return;
        }
        body.innerHTML = activos.map(row => {
            const nombre = usuarioNombre(row);
            const userId = usuarioId(row);
            const externalId = usuarioExternal(row);
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
    function renderHistorialNotificaciones(items) {
        const body = document.getElementById('atlas-notif-historial-body');
        if (!body) return;
        if (!items || !items.length) {
            body.innerHTML = '<tr><td colspan="5" class="atlas-notif-empty">No hay notificaciones enviadas.</td></tr>';
            return;
        }
        body.innerHTML = items.map(row => {
            const total = Number(row.total_destinatarios || 0);
            const leidas = Number(row.total_leidas || 0);
            const alcance = String(row.alcance || '').toLowerCase();
            const badge = alcance === 'campania'
                ? '<span class="atlas-notif-badge atlas-notif-badge-warn"><i class="fa-solid fa-users"></i>Campaña</span>'
                : '<span class="atlas-notif-badge atlas-notif-badge-ok"><i class="fa-solid fa-user"></i>Usuario</span>';
            return '<tr>'
                + '<td>' + badge + '<div class="atlas-notif-table-sub">' + esc(row.alcance_nombre || '-') + '</div></td>'
                + '<td><div class="atlas-notif-table-main">' + esc(row.titulo || 'Notificación') + '</div><div class="atlas-notif-table-sub">' + esc(row.mensaje || '') + '</div></td>'
                + '<td><div class="atlas-notif-table-main">' + esc(String(total)) + '</div><div class="atlas-notif-table-sub">' + esc(row.type || '') + '</div></td>'
                + '<td><div class="atlas-notif-table-main">' + esc(leidas + ' leídas') + '</div><div class="atlas-notif-table-sub">' + esc((total - leidas) + ' pendientes') + '</div></td>'
                + '<td><div class="atlas-notif-table-main">' + esc(row.created_at_fmt || '-') + '</div><div class="atlas-notif-table-sub">' + esc(row.ultima_actividad_fmt ? 'Últ. mov. ' + row.ultima_actividad_fmt : '') + '</div></td>'
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
            atlasUsuariosDisponibles = Array.isArray(data.datos) ? data.datos : [];
            atlasDestinatariosSeleccionados = new Set();
            renderUsuariosDisponibles(atlasUsuariosDisponibles);
            renderDestinatariosRapidos(document.getElementById('atlas-dest-buscar') ? document.getElementById('atlas-dest-buscar').value : '');
            actualizarKpis({ datos: { active_tokens: data.totales && data.totales.total ? data.totales.total : 0 } });
            return data;
        } catch (err) {
            atlasUsuariosDisponibles = [];
            renderUsuariosDisponibles([]);
            renderDestinatariosRapidos('');
            if (!silencioso && typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'No se pudo cargar', text: err.message || 'Error' });
            }
            return null;
        } finally {
            if (!silencioso) hideBusy();
        }
    }
    async function cargarHistorialNotificaciones(silencioso) {
        if (!silencioso) showBusy();
        try {
            const data = await atlasJson('/Atlas/getHistorialNotificacionesApp', {});
            renderHistorialNotificaciones(Array.isArray(data.datos) ? data.datos : []);
            return data;
        } catch (err) {
            renderHistorialNotificaciones([]);
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
    const formTemplate = document.getElementById('atlas-template-form');

    initTemplateEditor();
    cargarPlantillas().catch(err => {
        const list = document.getElementById('atlas-template-list');
        if (list) list.innerHTML = '<div class="atlas-notif-empty">' + esc(err.message || 'No se pudieron cargar las plantillas.') + '</div>';
    });
    cargarUsuariosDisponibles(true);
    cargarHistorialNotificaciones(true);

    if (formAviso) {
        formAviso.addEventListener('input', actualizarPreview);
    }
    aplicarModoEnvio();
    if (formAviso) formAviso.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        if (!validarContenidoAviso()) return;
        const modo = document.getElementById('atlas-notif-modo-envio') ? document.getElementById('atlas-notif-modo-envio').value : 'unico';
        const activos = usuariosActivosDisponibles();
        let seleccionados = [];
        if (modo === 'campania') {
            const todos = document.getElementById('atlas-dest-todos');
            if (todos && todos.checked) {
                seleccionados = activos;
            } else {
                seleccionados = Array.from(atlasDestinatariosSeleccionados)
                    .map(idx => activos[Number(idx)])
                    .filter(Boolean);
            }
        } else {
            const sel = document.getElementById('atlas-notif-destinatario-unico');
            const row = sel ? activos[Number(sel.value)] : null;
            if (row) seleccionados = [row];
        }
        const payload = payloadEnvioUnificado(destinatariosDesdeUsuarios(seleccionados));
        if (!payload.destinatarios.user_ids.length && !payload.destinatarios.external_ids.length) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Destinatarios requeridos', text: modo === 'campania' ? 'Selecciona al menos un usuario activo.' : 'Selecciona el destinatario.' });
            return;
        }
        try {
            showBusy();
            const data = await proxy('POST', '/api/atlas/notifications/send', payload, {});
            actualizarKpis(data);
            await cargarHistorialNotificaciones(true);
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: modo === 'campania' ? 'Campaña enviada' : 'Notificación enviada', text: 'Se registró en inbox y se envió push desde Atlas App.' });
        }
        catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: err.message || 'Error' }); }
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
    const modoEnvio = document.getElementById('atlas-notif-modo-envio');
    if (modoEnvio) modoEnvio.addEventListener('change', aplicarModoEnvio);
    const templateRapida = document.getElementById('atlas-notif-template-rapida');
    if (templateRapida) templateRapida.addEventListener('change', function () {
        const row = atlasPlantillas.find(item => String(item.id || '') === String(templateRapida.value || ''));
        if (row) usarPlantillaEnAviso(row);
    });
    const buscarDest = document.getElementById('atlas-dest-buscar');
    if (buscarDest) buscarDest.addEventListener('input', function () {
        const todos = document.getElementById('atlas-dest-todos');
        if (todos) todos.checked = false;
        renderDestinatariosRapidos(buscarDest.value);
    });
    const destTodos = document.getElementById('atlas-dest-todos');
    if (destTodos) destTodos.addEventListener('change', function () {
        atlasDestinatariosSeleccionados = destTodos.checked
            ? new Set(usuariosActivosDisponibles().map((row, idx) => String(idx)))
            : new Set();
        document.querySelectorAll('[data-atlas-dest-idx]').forEach(el => { el.checked = destTodos.checked; });
    });
    const destLista = document.getElementById('atlas-dest-lista');
    if (destLista) destLista.addEventListener('change', function (ev) {
        const chk = ev.target.closest('[data-atlas-dest-idx]');
        if (!chk) return;
        const idx = String(chk.getAttribute('data-atlas-dest-idx'));
        if (chk.checked) atlasDestinatariosSeleccionados.add(idx);
        else atlasDestinatariosSeleccionados.delete(idx);
        const todos = document.getElementById('atlas-dest-todos');
        if (todos) todos.checked = atlasDestinatariosSeleccionados.size > 0 && atlasDestinatariosSeleccionados.size === usuariosActivosDisponibles().length;
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
        const cargarHistorial = ev.target.closest('[data-atlas-historial-notificaciones]');
        if (cargarHistorial) {
            ev.preventDefault();
            await cargarHistorialNotificaciones(false);
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
                if (path === '/api/atlas/push-tokens') {
                    atlasUsuariosDisponibles = extraerArray(data);
                    atlasDestinatariosSeleccionados = new Set();
                    renderUsuariosDisponibles(atlasUsuariosDisponibles);
                    renderDestinatariosRapidos(document.getElementById('atlas-dest-buscar') ? document.getElementById('atlas-dest-buscar').value : '');
                }
            }
            catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo cargar', text: err.message || 'Error' }); }
            finally { hideBusy(); }
            return;
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
