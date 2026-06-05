<style>
    .vac-admin-page { color: #24324a; }
    .vac-admin-card {
        background: #fff;
        border: 1px solid #dce6f2;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
    }
    .vac-admin-table th {
        color: #607089;
        font-size: 12px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .vac-admin-table td { vertical-align: middle; }
    .vac-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
    }
    .vac-pill-pendiente { color: #9a6500; background: #fff2d2; }
    .vac-pill-aprobada,
    .vac-pill-tomada { color: #08785f; background: #dff8f1; }
    .vac-pill-rechazada,
    .vac-pill-cancelada { color: #a13b32; background: #ffe5e1; }
    .vac-action-btn {
        width: 38px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d9a33b;
        border-radius: 999px;
        color: #d99a28;
        background: #fff;
    }
    .vac-action-btn:hover { background: #fff7e6; color: #b87508; }
    .vac-modal-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .vac-info-box {
        border: 1px solid #dce6f2;
        border-radius: 10px;
        padding: 12px;
        background: #f8fbff;
    }
    .vac-info-label {
        color: #607089;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .vac-days {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .vac-day {
        border-radius: 999px;
        background: #f3f6fb;
        border: 1px solid #dce6f2;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
        color: #44546a;
    }
    .vac-approval-box {
        border: 1px solid #dce6f2;
        border-radius: 12px;
        padding: 14px;
        background: #fff;
    }
    .vac-official-admin {
        border: 1px solid #dce6f2;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }
    .vac-official-head {
        display: grid;
        grid-template-columns: 130px 1fr auto;
        gap: 14px;
        align-items: center;
        background: #06449a;
        color: #fff;
        padding: 14px 20px;
    }
    .vac-official-logo {
        border: 1px dashed rgba(255,255,255,.55);
        border-radius: 6px;
        padding: 5px 10px;
        background: rgba(255,255,255,.08);
        text-align: center;
    }
    .vac-official-logo img { max-width: 54px; max-height: 54px; }
    .vac-official-title { font-size: 17px; font-weight: 900; line-height: 1.2; }
    .vac-official-subtitle { font-size: 12px; opacity: .9; }
    .vac-official-badge {
        border-radius: 999px;
        background: #a8e30f;
        color: #09285a;
        font-size: 11px;
        font-weight: 900;
        padding: 7px 12px;
    }
    .vac-official-body { padding: 18px 20px; }
    .vac-official-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .vac-official-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .vac-form-field label {
        display: block;
        color: #0b285f;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .vac-form-value {
        min-height: 38px;
        border: 1px solid #d8e2ef;
        border-radius: 6px;
        padding: 9px 10px;
        font-weight: 700;
        background: #fff;
    }
    .vac-signatures {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .vac-signature-card {
        border: 1px solid #d8e2ef;
        border-radius: 8px;
        padding: 10px;
        background: #f8fbff;
        min-height: 132px;
    }
    .vac-signature-card img {
        width: 100%;
        height: 64px;
        object-fit: contain;
        background: #fff;
        border: 1px solid #e1e8f2;
        border-radius: 6px;
    }
    .vac-signature-empty {
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed #c9d6e8;
        border-radius: 6px;
        color: #7a889a;
        background: #fff;
        font-size: 12px;
        font-weight: 800;
    }
    #vacAdminFirmaCanvas {
        width: 100%;
        height: 150px;
        display: block;
        cursor: crosshair;
        touch-action: none;
        background: #fff;
        border: 1px solid #d8e2ef;
        border-radius: 8px;
    }
    @media print {
        body * { visibility: hidden !important; }
        #vacAdminFormatoPrint, #vacAdminFormatoPrint * { visibility: visible !important; }
        #vacAdminFormatoPrint {
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
        }
        .vac-no-print { display: none !important; }
    }
    @media (max-width: 992px) {
        .vac-modal-grid { grid-template-columns: 1fr; }
        .vac-official-head,
        .vac-official-grid,
        .vac-official-grid.three,
        .vac-signatures { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid py-4 vac-admin-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-check me-2"></i>Panel admin vacaciones</h2>
            <p class="text-muted mb-0">Revisión de solicitudes por RR.HH. y jefe RR.HH.</p>
        </div>
        <button type="button" class="btn btn-outline-primary" id="vacAdminRefresh">
            <i class="fa-solid fa-rotate me-1"></i> Actualizar
        </button>
    </div>

    <div class="vac-admin-card p-3 p-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <strong>Solicitudes recibidas</strong>
            <span class="badge bg-light text-dark" id="vacAdminCount">0 registros</span>
        </div>
        <div class="table-responsive">
            <table class="table vac-admin-table align-middle">
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Área / Depto</th>
                        <th>Fechas</th>
                        <th>Días</th>
                        <th>RR.HH.</th>
                        <th>Jefe RR.HH.</th>
                        <th>Estatus</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="vacAdminBody">
                    <tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="vacSolicitudModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-umbrella-beach me-2"></i><span id="vacModalTitulo">Solicitud</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="vac-modal-grid mb-3" id="vacModalInfo"></div>
                <div class="mb-3">
                    <div class="fw-bold mb-2">Días solicitados</div>
                    <div class="vac-days" id="vacModalDias"></div>
                </div>
                <div class="mb-3">
                    <div class="fw-bold mb-2">Comentario del colaborador</div>
                    <div class="vac-info-box" id="vacModalComentario">-</div>
                </div>
                <div class="mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div class="fw-bold">Formato oficial firmado</div>
                        <button type="button" class="btn btn-outline-primary btn-sm vac-no-print" id="vacAdminBtnPdf">
                            <i class="fa-solid fa-file-pdf me-1"></i> Generar / imprimir PDF
                        </button>
                    </div>
                    <div class="vac-official-admin" id="vacAdminFormatoPrint">
                        <div class="vac-official-head">
                            <div class="vac-official-logo">
                                <img src="/assets/img/logo_correo.png" alt="Maxikash">
                            </div>
                            <div>
                                <div class="vac-official-title"><i class="fa-solid fa-file-lines me-1"></i> Solicitud de Vacaciones - Maxikash</div>
                                <div class="vac-official-subtitle">Amigo Efectivo S.A.P.I. de C.V. - Art. 76 LFT</div>
                            </div>
                            <div class="vac-official-badge">FORMATO OFICIAL</div>
                        </div>
                        <div class="vac-official-body">
                            <div class="vac-official-grid mb-3">
                                <div class="vac-form-field">
                                    <label>Nombre del colaborador</label>
                                    <div class="vac-form-value" id="vacFmtNombre">-</div>
                                </div>
                                <div class="vac-form-field">
                                    <label>Area / departamento</label>
                                    <div class="vac-form-value" id="vacFmtAreaDepto">-</div>
                                </div>
                            </div>
                            <div class="vac-official-grid three mb-3">
                                <div class="vac-form-field">
                                    <label>Dias que corresponden</label>
                                    <div class="vac-form-value" id="vacFmtOtorgados">-</div>
                                </div>
                                <div class="vac-form-field">
                                    <label>Dias a disfrutar</label>
                                    <div class="vac-form-value" id="vacFmtDisfrutar">-</div>
                                </div>
                                <div class="vac-form-field">
                                    <label>Estatus</label>
                                    <div class="vac-form-value" id="vacFmtEstatus">-</div>
                                </div>
                            </div>
                            <div class="vac-official-grid mb-3">
                                <div class="vac-form-field">
                                    <label>Periodo</label>
                                    <div class="vac-form-value" id="vacFmtPeriodo">-</div>
                                </div>
                                <div class="vac-form-field">
                                    <label>Fecha de reincorporacion</label>
                                    <div class="vac-form-value" id="vacFmtReincorporacion">-</div>
                                </div>
                            </div>
                            <div class="vac-form-field mb-3">
                                <label>Observaciones</label>
                                <div class="vac-form-value" id="vacFmtObservaciones">-</div>
                            </div>
                            <div class="vac-signatures">
                                <div class="vac-signature-card" id="vacFirmaColaboradorBox"></div>
                                <div class="vac-signature-card" id="vacFirmaRrhhBox"></div>
                                <div class="vac-signature-card" id="vacFirmaJefeBox"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="vac-approval-box">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Validación RR.HH.</strong>
                                <span id="vacModalRrhh"></span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success flex-fill" data-vac-resolver="rrhh:aprobada">
                                    <i class="fa-solid fa-check me-1"></i> Aceptar
                                </button>
                                <button class="btn btn-outline-danger flex-fill" data-vac-resolver="rrhh:rechazada">
                                    <i class="fa-solid fa-xmark me-1"></i> Rechazar
                                </button>
                            </div>
                            <div class="small text-muted mt-2" id="vacModalRrhhMeta"></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="vac-approval-box">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Validación jefe RR.HH.</strong>
                                <span id="vacModalJefe"></span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success flex-fill" data-vac-resolver="jefe:aprobada">
                                    <i class="fa-solid fa-check me-1"></i> Aceptar
                                </button>
                                <button class="btn btn-outline-danger flex-fill" data-vac-resolver="jefe:rechazada">
                                    <i class="fa-solid fa-xmark me-1"></i> Rechazar
                                </button>
                            </div>
                            <div class="small text-muted mt-2" id="vacModalJefeMeta"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vacFirmaAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-signature me-2"></i><span id="vacFirmaTitulo">Firmar aprobacion</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-2">Dibuja la firma del responsable para aprobar esta etapa.</p>
                <canvas id="vacAdminFirmaCanvas"></canvas>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary" id="vacAdminLimpiarFirma">
                        <i class="fa-solid fa-trash-can me-1"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-primary ms-auto" id="vacAdminConfirmarFirma">
                        <i class="fa-solid fa-check me-1"></i> Firmar y aprobar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const $ = (id) => document.getElementById(id);
    let modal;
    let modalFirma;
    let solicitudActual = 0;
    let detalleActual = null;
    let firmaPendiente = null;
    let firmaAdminDibujada = false;
    let firmaAdminInicializada = false;

    function fmtFecha(v) {
        if (!v) return '-';
        const s = String(v);
        const datePart = s.slice(0, 10);
        const parts = datePart.split('-');
        const fecha = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : s;
        return s.length > 10 ? `${fecha} ${s.slice(11, 16)}` : fecha;
    }

    function fmtNum(v) {
        const n = Number(v || 0);
        return Number.isInteger(n) ? String(n) : n.toFixed(1);
    }

    function normalizarFecha(v) {
        const s = String(v || '').slice(0, 10);
        return /^\d{4}-\d{2}-\d{2}$/.test(s) ? s : '';
    }

    function fechaYmd(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function siguienteDiaLaboral(fecha) {
        const f = normalizarFecha(fecha);
        if (!f) return '';
        const dt = new Date(f + 'T12:00:00');
        do {
            dt.setDate(dt.getDate() + 1);
        } while (dt.getDay() === 0 || dt.getDay() === 6);
        return fechaYmd(dt);
    }

    function pill(v) {
        const key = String(v || 'pendiente').toLowerCase();
        const label = key.charAt(0).toUpperCase() + key.slice(1);
        return `<span class="vac-pill vac-pill-${key}">${label}</span>`;
    }

    function swal(icon, title, text, opts = {}) {
        if (window.Swal) return Swal.fire({ icon, title, text, ...opts });
        alert((title || '') + (text ? '\n' + text : ''));
        return Promise.resolve({ isConfirmed: true, value: '' });
    }

    function signatureBox(titulo, firma, nombre, fecha) {
        const contenido = firma
            ? `<img src="${firma}" alt="Firma ${titulo}">`
            : '<div class="vac-signature-empty">Pendiente</div>';
        return `
            <div class="fw-bold small mb-2">${titulo}</div>
            ${contenido}
            <div class="small mt-2">${nombre || '-'}</div>
            <div class="small text-muted">${fecha ? fmtFecha(fecha) : '-'}</div>
        `;
    }

    function pintarFormato(s) {
        $('vacFmtNombre').textContent = s.nombre_completo || '-';
        $('vacFmtAreaDepto').textContent = `${s.area || '-'} / ${s.departamento || '-'}`;
        $('vacFmtOtorgados').textContent = '-';
        $('vacFmtDisfrutar').textContent = fmtNum(s.dias_solicitados);
        $('vacFmtEstatus').innerHTML = pill(s.estatus);
        $('vacFmtPeriodo').textContent = `${fmtFecha(s.periodo_inicio)} - ${fmtFecha(s.periodo_fin)}`;
        $('vacFmtReincorporacion').textContent = fmtFecha(siguienteDiaLaboral(s.fecha_fin));
        $('vacFmtObservaciones').textContent = s.comentario || 'Solicitud de vacaciones';
        $('vacFirmaColaboradorBox').innerHTML = signatureBox('Colaborador', s.firma_colaborador, s.nombre_completo, s.firma_colaborador_fecha);
        $('vacFirmaRrhhBox').innerHTML = signatureBox('RR.HH.', s.rrhh_firma, s.rrhh_nombre, s.rrhh_firma_fecha);
        $('vacFirmaJefeBox').innerHTML = signatureBox('Jefe / responsable', s.jefe_firma, s.jefe_nombre, s.jefe_firma_fecha);
    }

    function ajustarCanvasFirmaAdmin() {
        const canvas = $('vacAdminFirmaCanvas');
        if (!canvas) return;
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = Math.max(1, Math.floor(rect.width * ratio));
        canvas.height = Math.max(1, Math.floor(rect.height * ratio));
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.lineWidth = 2.5 * ratio;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#24324a';
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        firmaAdminDibujada = false;
    }

    function initCanvasFirmaAdmin() {
        const canvas = $('vacAdminFirmaCanvas');
        if (!canvas || firmaAdminInicializada) return;
        firmaAdminInicializada = true;
        let dibujando = false;
        let ultimo = null;
        function punto(ev) {
            const rect = canvas.getBoundingClientRect();
            const touch = ev.touches && ev.touches.length ? ev.touches[0] : ev;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return { x: (touch.clientX - rect.left) * scaleX, y: (touch.clientY - rect.top) * scaleY };
        }
        function iniciar(ev) {
            ev.preventDefault();
            dibujando = true;
            ultimo = punto(ev);
        }
        function mover(ev) {
            if (!dibujando || !ultimo) return;
            ev.preventDefault();
            const actual = punto(ev);
            const ctx = canvas.getContext('2d');
            ctx.beginPath();
            ctx.moveTo(ultimo.x, ultimo.y);
            ctx.lineTo(actual.x, actual.y);
            ctx.stroke();
            ultimo = actual;
            firmaAdminDibujada = true;
        }
        function terminar() {
            dibujando = false;
            ultimo = null;
        }
        canvas.addEventListener('mousedown', iniciar);
        canvas.addEventListener('mousemove', mover);
        window.addEventListener('mouseup', terminar);
        canvas.addEventListener('touchstart', iniciar, { passive: false });
        canvas.addEventListener('touchmove', mover, { passive: false });
        canvas.addEventListener('touchend', terminar);
        window.addEventListener('resize', ajustarCanvasFirmaAdmin);
    }

    function abrirFirma(etapa, accion) {
        firmaPendiente = { etapa, accion };
        $('vacFirmaTitulo').textContent = etapa === 'rrhh' ? 'Firma de RR.HH.' : 'Firma de jefe / responsable';
        modalFirma.show();
        setTimeout(() => {
            initCanvasFirmaAdmin();
            ajustarCanvasFirmaAdmin();
        }, 180);
    }

    function imprimirFormatoAdmin() {
        const source = $('vacAdminFormatoPrint');
        const clone = source.cloneNode(true);
        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Solicitud de Vacaciones</title>
<style>
@page { size: letter; margin: 0; }
* { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
body { margin: 0; padding: 9mm 0 0; color: #24324a; font-family: Arial, Helvetica, sans-serif; background: #fff; }
.fa-solid { display: none !important; }
.vac-official-admin { width: 180mm; margin: 0 auto; border: 0; border-radius: 0; overflow: hidden; background: #fff; }
.vac-official-head { display: grid; grid-template-columns: 31mm 1fr 35mm; gap: 4mm; align-items: center; min-height: 21mm; background: #064aa2; color: #fff; padding: 4mm 6mm; }
.vac-official-logo { height: 15mm; display: flex; align-items: center; justify-content: center; border: 1px dashed rgba(255,255,255,.6); border-radius: 1.5mm; background: rgba(255,255,255,.08); padding: 1.5mm; }
.vac-official-logo img { width: 11mm; height: 11mm; object-fit: contain; }
.vac-official-title { font-size: 16px; font-weight: 900; line-height: 1.15; color: #fff; }
.vac-official-subtitle { font-size: 11px; opacity: .95; margin-top: 2px; color: #fff; }
.vac-official-badge { justify-self: end; border-radius: 999px; background: #a8e30f; color: #09285a; font-size: 10px; font-weight: 900; padding: 2mm 4mm; white-space: nowrap; }
.vac-official-body { padding: 7mm 7mm 0; }
.vac-official-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4mm; margin-bottom: 5mm; }
.vac-official-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.vac-form-field label { display: block; color: #00275f; font-size: 10px; font-weight: 900; text-transform: uppercase; margin-bottom: 2mm; }
.vac-form-value { min-height: 10mm; border: 1px solid #d8e2ef; border-radius: 1.5mm; background: #fff; color: #24324a; padding: 2.6mm 3mm; font-size: 13px; font-weight: 700; line-height: 1.2; }
.vac-signatures { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 4mm; break-inside: avoid; }
.vac-signature-card { border: 1px solid #d8e2ef; border-radius: 2mm; padding: 3mm; background: #f8fbff; min-height: 36mm; }
.vac-signature-card img { width: 100%; height: 17mm; object-fit: contain; background: #fff; border: 1px solid #e1e8f2; border-radius: 1.5mm; }
.vac-signature-empty { height: 17mm; display: flex; align-items: center; justify-content: center; border: 1px dashed #c9d6e8; border-radius: 1.5mm; color: #7a889a; background: #fff; font-size: 11px; font-weight: 800; }
.fw-bold { font-weight: 700; }
.small { font-size: 11px; }
.text-muted { color: #65758b; }
.mb-3 { margin-bottom: 5mm; }
</style>
</head>
<body>${clone.outerHTML}</body>
</html>`);
        doc.close();
        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(() => iframe.remove(), 500);
        }, 250);
    }

    async function cargarLista() {
        $('vacAdminBody').innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>';
        const res = await fetch('/caphum/getVacacionesAdmin', { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!data.success) {
            $('vacAdminBody').innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">No se pudo cargar.</td></tr>';
            swal('error', 'Error', data.mensaje || 'No se pudo consultar solicitudes.');
            return;
        }
        const rows = Array.isArray(data.datos) ? data.datos : [];
        $('vacAdminCount').textContent = `${rows.length} registros`;
        if (!rows.length) {
            $('vacAdminBody').innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay solicitudes registradas.</td></tr>';
            return;
        }
        $('vacAdminBody').innerHTML = rows.map((r) => `
            <tr>
                <td>
                    <strong>${r.nombre_completo || 'Sin nombre'}</strong>
                    <div class="small text-muted">${r.numero_empleado ? '#' + r.numero_empleado : ''}</div>
                </td>
                <td>
                    <strong>${r.area || '-'}</strong>
                    <div class="small text-muted">${r.departamento || '-'}</div>
                </td>
                <td>
                    <strong>${fmtFecha(r.fecha_inicio)} - ${fmtFecha(r.fecha_fin)}</strong>
                    <div class="small text-muted">${r.modo_fechas === 'separados' ? 'Días separados' : 'Rango continuo'}</div>
                </td>
                <td><strong>${fmtNum(r.dias_solicitados)}</strong></td>
                <td>${pill(r.rrhh_estatus)}</td>
                <td>${pill(r.jefe_estatus)}</td>
                <td>${pill(r.estatus)}</td>
                <td>
                    <button type="button" class="vac-action-btn" title="Ver solicitud" data-vac-id="${r.id}">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function infoBox(label, value) {
        return `<div class="vac-info-box"><div class="vac-info-label">${label}</div><div class="fw-bold">${value || '-'}</div></div>`;
    }

    async function abrirDetalle(idSolicitud) {
        solicitudActual = idSolicitud;
        $('vacModalInfo').innerHTML = '';
        $('vacModalDias').innerHTML = '<span class="text-muted">Cargando...</span>';
        const res = await fetch('/caphum/getVacacionesSolicitud?id_solicitud=' + encodeURIComponent(idSolicitud), { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!data.success) {
            swal('error', 'Error', data.mensaje || 'No se pudo cargar la solicitud.');
            return;
        }

        const s = data.datos.solicitud || {};
        const dias = Array.isArray(data.datos.dias) ? data.datos.dias : [];
        detalleActual = data.datos;
        $('vacModalTitulo').textContent = s.nombre_completo || 'Solicitud';
        $('vacModalInfo').innerHTML = [
            infoBox('Colaborador', `${s.nombre_completo || '-'} ${s.numero_empleado ? '#' + s.numero_empleado : ''}`),
            infoBox('Ingreso', fmtFecha(s.fecha_ingreso)),
            infoBox('Área / Departamento', `${s.area || '-'} / ${s.departamento || '-'}`),
            infoBox('Periodo', `${fmtFecha(s.periodo_inicio)} - ${fmtFecha(s.periodo_fin)}`),
            infoBox('Fechas solicitadas', `${fmtFecha(s.fecha_inicio)} - ${fmtFecha(s.fecha_fin)}`),
            infoBox('Días', fmtNum(s.dias_solicitados)),
        ].join('');
        $('vacModalDias').innerHTML = dias.length
            ? dias.map((d) => `<span class="vac-day">${fmtFecha(d.fecha)}</span>`).join('')
            : '<span class="text-muted">Sin detalle de días.</span>';
        $('vacModalComentario').textContent = s.comentario || '-';
        $('vacModalRrhh').innerHTML = pill(s.rrhh_estatus);
        $('vacModalJefe').innerHTML = pill(s.jefe_estatus);
        $('vacModalRrhhMeta').textContent = [s.rrhh_nombre, s.rrhh_fecha ? fmtFecha(s.rrhh_fecha) : '', s.rrhh_comentario || ''].filter(Boolean).join(' · ');
        $('vacModalJefeMeta').textContent = [s.jefe_nombre, s.jefe_fecha ? fmtFecha(s.jefe_fecha) : '', s.jefe_comentario || ''].filter(Boolean).join(' · ');
        pintarFormato(s);
        document.querySelectorAll('[data-vac-resolver^="rrhh:"]').forEach((btn) => {
            btn.disabled = s.rrhh_estatus !== 'pendiente' || s.estatus === 'rechazada';
        });
        document.querySelectorAll('[data-vac-resolver^="jefe:"]').forEach((btn) => {
            btn.disabled = s.jefe_estatus !== 'pendiente' || s.estatus === 'rechazada';
        });
        modal.show();
    }

    async function resolver(etapa, accion) {
        if (accion === 'aprobada') {
            abrirFirma(etapa, accion);
            return;
        }

        const r = await swal('warning', 'Motivo del rechazo', 'Escribe por que se rechaza la solicitud.', {
            input: 'textarea',
            inputPlaceholder: 'Motivo...',
            showCancelButton: true,
            confirmButtonText: 'Rechazar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => !String(value || '').trim() ? 'El comentario es obligatorio.' : undefined
        });
        if (!r.isConfirmed) return;
        await resolverEnviar(etapa, accion, String(r.value || '').trim(), '');
    }

    async function resolverEnviar(etapa, accion, comentario, firmaResponsable) {
        const res = await fetch('/caphum/resolverVacacionesSolicitud', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({
                id_solicitud: solicitudActual,
                etapa,
                accion,
                comentario,
                firma_responsable: firmaResponsable || ''
            })
        });
        const data = await res.json();
        if (!data.success) {
            swal('warning', 'No se pudo actualizar', data.mensaje || 'Revisa la solicitud.');
            return;
        }
        await cargarLista();
        await abrirDetalle(solicitudActual);
        swal('success', 'Listo', data.mensaje || 'Solicitud actualizada.');
    }

    document.addEventListener('click', function (ev) {
        const ver = ev.target.closest('[data-vac-id]');
        if (ver) {
            abrirDetalle(Number(ver.getAttribute('data-vac-id')) || 0);
            return;
        }
        const action = ev.target.closest('[data-vac-resolver]');
        if (action) {
            const [etapa, accion] = String(action.getAttribute('data-vac-resolver')).split(':');
            resolver(etapa, accion);
        }
    });

    $('vacAdminBtnPdf').addEventListener('click', function () {
        imprimirFormatoAdmin();
    });

    $('vacAdminLimpiarFirma').addEventListener('click', ajustarCanvasFirmaAdmin);

    $('vacAdminConfirmarFirma').addEventListener('click', async function () {
        if (!firmaPendiente) return;
        if (!firmaAdminDibujada) {
            swal('warning', 'Firma requerida', 'Dibuja la firma antes de aprobar.');
            return;
        }
        const firma = $('vacAdminFirmaCanvas').toDataURL('image/png');
        $('vacAdminConfirmarFirma').disabled = true;
        try {
            await resolverEnviar(firmaPendiente.etapa, firmaPendiente.accion, '', firma);
            modalFirma.hide();
            firmaPendiente = null;
        } finally {
            $('vacAdminConfirmarFirma').disabled = false;
        }
    });

    $('vacAdminRefresh').addEventListener('click', cargarLista);
    modal = new bootstrap.Modal($('vacSolicitudModal'));
    modalFirma = new bootstrap.Modal($('vacFirmaAdminModal'));
    cargarLista().catch((err) => {
        console.error(err);
        swal('error', 'Error', 'No se pudo cargar el panel.');
    });
})();
</script>
