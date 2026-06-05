<style>
    .vac-page {
        color: #24324a;
    }

    .vac-page .page-title {
        font-weight: 800;
        letter-spacing: 0;
    }

    .vac-card {
        background: #fff;
        border: 1px solid #dce6f2;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
    }

    .vac-stat {
        min-height: 118px;
    }

    .vac-stat-label {
        color: #65758b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .vac-stat-value {
        color: #24324a;
        font-size: 32px;
        font-weight: 900;
        line-height: 1.1;
    }

    .vac-progress {
        height: 9px;
        border-radius: 999px;
        background: #e9eef5;
        overflow: hidden;
    }

    .vac-progress > span {
        display: block;
        height: 100%;
        width: 0%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0f9f8f, #55d6bd);
        transition: width .2s ease;
    }

    .vac-table th {
        color: #607089;
        font-size: 12px;
        text-transform: uppercase;
    }

    .vac-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .vac-status-pendiente { color: #9a6500; background: #fff2d2; }
    .vac-status-aprobada,
    .vac-status-tomada { color: #08785f; background: #dff8f1; }
    .vac-status-rechazada,
    .vac-status-cancelada { color: #a13b32; background: #ffe5e1; }

    .vac-mode-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .vac-mode-option {
        border: 1px solid #dce6f2;
        border-radius: 10px;
        background: #fff;
        color: #24324a;
        font-weight: 800;
        min-height: 44px;
    }

    .vac-mode-option.active {
        background: #24324a;
        border-color: #24324a;
        color: #fff;
        box-shadow: 0 8px 18px rgba(36, 50, 74, 0.18);
    }

    .vac-selected-days {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        min-height: 38px;
        padding: 8px;
        border: 1px dashed #c8d6e8;
        border-radius: 10px;
        background: #f8fbff;
    }

    .vac-day-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #d9a33b;
        border-radius: 999px;
        padding: 6px 10px;
        color: #744f0b;
        background: #fff7e6;
        font-size: 12px;
        font-weight: 800;
    }

    .vac-day-chip button {
        border: 0;
        background: transparent;
        color: inherit;
        padding: 0;
        line-height: 1;
    }

    .vac-page .flatpickr-input[readonly] {
        background: #fff;
        cursor: pointer;
    }

    .vac-days-warning {
        color: #b42318;
        font-size: 12px;
        font-weight: 800;
        margin-top: 4px;
        min-height: 18px;
    }

    .vac-days-over {
        color: #b42318;
    }

    .vac-official-modal .modal-dialog {
        max-width: 860px;
    }

    .vac-official-form {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #dce6f2;
    }

    .vac-official-head {
        display: grid;
        grid-template-columns: 140px 1fr auto;
        gap: 16px;
        align-items: center;
        background: #06449a;
        color: #fff;
        padding: 16px 28px;
    }

    .vac-official-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        border: 1px dashed rgba(255,255,255,.55);
        border-radius: 6px;
        background: rgba(255,255,255,.08);
        padding: 4px 10px;
    }

    .vac-official-logo img {
        max-width: 54px;
        max-height: 54px;
    }

    .vac-official-title {
        font-size: 18px;
        font-weight: 900;
        line-height: 1.2;
    }

    .vac-official-subtitle {
        font-size: 12px;
        opacity: .9;
        margin-top: 4px;
    }

    .vac-official-badge {
        border-radius: 999px;
        background: #a8e30f;
        color: #09285a;
        font-size: 12px;
        font-weight: 900;
        padding: 7px 14px;
        white-space: nowrap;
    }

    .vac-official-body {
        padding: 26px 28px;
    }

    .vac-official-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .vac-official-grid.three {
        grid-template-columns: 1fr 1fr 1fr;
    }

    .vac-official-field label {
        display: block;
        color: #0b285f;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .vac-official-value {
        min-height: 40px;
        border: 1px solid #d8e2ef;
        border-radius: 6px;
        background: #fff;
        color: #24324a;
        padding: 10px 12px;
        font-weight: 700;
    }

    .vac-official-value.muted {
        background: #f6f8fb;
        color: #607089;
    }

    .vac-sign-box {
        border: 1px solid #d8e2ef;
        border-radius: 8px;
        background: #f8fbff;
        padding: 16px 18px;
    }

    .vac-sign-canvas-wrap {
        background: #fff;
        border: 1px solid #d8e2ef;
        border-radius: 7px;
        overflow: hidden;
    }

    #vacFirmaCanvas {
        width: 100%;
        height: 120px;
        display: block;
        touch-action: none;
        cursor: crosshair;
    }

    .vac-official-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 18px;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }
        #vacFormatoPrint, #vacFormatoPrint * {
            visibility: visible !important;
        }
        #vacFormatoPrint {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .vac-no-print {
            display: none !important;
        }
        .vac-official-form {
            border: 0;
            box-shadow: none;
        }
    }

    @media (max-width: 576px) {
        .vac-mode-grid {
            grid-template-columns: 1fr;
        }
        .vac-official-head,
        .vac-official-grid,
        .vac-official-grid.three,
        .vac-official-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid py-4 vac-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-umbrella-beach me-2"></i>Vacaciones</h2>
            <p class="text-muted mb-0">Solicitud y control de saldo por aniversario laboral.</p>
        </div>
        <button type="button" class="btn btn-outline-primary" id="vacBtnRefrescar">
            <i class="fa-solid fa-rotate me-1"></i> Actualizar
        </button>
    </div>

    <div class="vac-card p-3 p-lg-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between gap-3">
            <div>
                <div class="vac-stat-label">Colaborador</div>
                <h4 class="mb-1" id="vacNombre">Cargando...</h4>
                <div class="text-muted" id="vacMetaPersona">-</div>
            </div>
            <div class="text-end">
                <div class="vac-stat-label">Periodo vigente</div>
                <div class="fw-bold" id="vacPeriodo">-</div>
                <div class="text-muted small" id="vacAniversario">-</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="vac-card vac-stat p-3">
                <div class="vac-stat-label">Días otorgados</div>
                <div class="vac-stat-value" id="vacDiasOtorgados">0</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="vac-card vac-stat p-3">
                <div class="vac-stat-label">Aprobados / tomados</div>
                <div class="vac-stat-value" id="vacDiasAprobados">0</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="vac-card vac-stat p-3">
                <div class="vac-stat-label">Pendientes</div>
                <div class="vac-stat-value" id="vacDiasPendientes">0</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="vac-card vac-stat p-3">
                <div class="vac-stat-label">Disponibles</div>
                <div class="vac-stat-value" id="vacDiasDisponibles">0</div>
            </div>
        </div>
    </div>

    <div class="vac-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Uso del periodo</strong>
            <span class="fw-bold" id="vacUsoTexto">0 / 0</span>
        </div>
        <div class="vac-progress"><span id="vacUsoBarra"></span></div>
        <div class="small text-muted mt-2">Las solicitudes pendientes se reservan para evitar sobrepasar el saldo disponible.</div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="vac-card p-3 p-lg-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-calendar-plus me-2"></i>Solicitar vacaciones</h5>
                <form id="vacFormSolicitud">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de solicitud</label>
                        <div class="vac-mode-grid">
                            <button type="button" class="vac-mode-option active" data-vac-mode="rango">
                                <i class="fa-solid fa-calendar-week me-1"></i> Rango continuo
                            </button>
                            <button type="button" class="vac-mode-option" data-vac-mode="separados">
                                <i class="fa-solid fa-calendar-days me-1"></i> Días separados
                            </button>
                        </div>
                    </div>
                    <div id="vacModoRango">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha inicial</label>
                        <input type="text" class="form-control vac-flatpickr-date" id="vacFechaInicio" placeholder="dd/mm/aaaa" autocomplete="off" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha final</label>
                        <input type="text" class="form-control vac-flatpickr-date" id="vacFechaFin" placeholder="dd/mm/aaaa" autocomplete="off" readonly required>
                    </div>
                    </div>
                    <div id="vacModoSeparados" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Agregar día</label>
                            <div class="input-group">
                                <input type="text" class="form-control vac-flatpickr-date" id="vacFechaSeparada" placeholder="dd/mm/aaaa" autocomplete="off" readonly>
                                <button type="button" class="btn btn-outline-primary" id="vacBtnAgregarDia">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Días seleccionados</label>
                            <div class="vac-selected-days" id="vacDiasSeleccionados">
                                <span class="text-muted small">Aún no has agregado días.</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="vac-stat-label">Días a disfrutar</div>
                        <div class="fw-bold" id="vacDiasPreview">0 días</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentario</label>
                        <textarea class="form-control" id="vacComentario" rows="3" placeholder="Opcional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="vacBtnSolicitar">
                        <i class="fa-solid fa-paper-plane me-1"></i> Enviar solicitud
                    </button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="vac-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial</h5>
                    <span class="badge bg-light text-dark" id="vacTotalSolicitudes">0 registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table vac-table align-middle">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Periodo</th>
                                <th>Días</th>
                                <th>Estatus</th>
                                <th>Creada</th>
                            </tr>
                        </thead>
                        <tbody id="vacHistorialBody">
                            <tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade vac-official-modal" id="vacModalFormato" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="vac-official-form" id="vacFormatoPrint">
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
                        <div class="vac-official-grid mb-4">
                            <div class="vac-official-field">
                                <label>Nombre del colaborador *</label>
                                <div class="vac-official-value" id="vacFmtNombre">-</div>
                            </div>
                            <div class="vac-official-field">
                                <label>Area / departamento *</label>
                                <div class="vac-official-value" id="vacFmtAreaDepto">-</div>
                            </div>
                        </div>

                        <div class="vac-official-grid three mb-4">
                            <div class="vac-official-field">
                                <label>Dias que corresponden *</label>
                                <div class="vac-official-value" id="vacFmtOtorgados">0</div>
                            </div>
                            <div class="vac-official-field">
                                <label>Dias a disfrutar *</label>
                                <div class="vac-official-value" id="vacFmtDisfrutar">0</div>
                            </div>
                            <div class="vac-official-field">
                                <label>Dias pendientes</label>
                                <div class="vac-official-value muted" id="vacFmtPendientes">0</div>
                            </div>
                        </div>

                        <div class="vac-official-grid mb-4">
                            <div class="vac-official-field">
                                <label>Periodo: del ano</label>
                                <div class="vac-official-value" id="vacFmtPeriodoInicio">-</div>
                            </div>
                            <div class="vac-official-field">
                                <label>Al ano</label>
                                <div class="vac-official-value" id="vacFmtPeriodoFin">-</div>
                            </div>
                        </div>

                        <div class="vac-official-grid mb-4">
                            <div class="vac-official-field">
                                <label>Fecha inicio de vacaciones *</label>
                                <div class="vac-official-value" id="vacFmtInicio">-</div>
                            </div>
                            <div class="vac-official-field">
                                <label>Fecha de reincorporacion *</label>
                                <div class="vac-official-value" id="vacFmtReincorporacion">-</div>
                            </div>
                        </div>

                        <div class="vac-official-field mb-4">
                            <label>Observaciones</label>
                            <div class="vac-official-value" style="min-height:64px;" id="vacFmtObservaciones">-</div>
                        </div>

                        <div class="vac-sign-box">
                            <h6 class="fw-bold mb-2"><i class="fa-solid fa-signature me-1"></i> Firma Digital del Colaborador</h6>
                            <div class="text-muted small mb-2">Dibuja tu firma en el recuadro con el mouse o dedo.</div>
                            <div class="vac-sign-canvas-wrap">
                                <canvas id="vacFirmaCanvas"></canvas>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3 mt-2 vac-no-print">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="vacBtnLimpiarFirma">
                                    <i class="fa-solid fa-trash-can me-1"></i> Limpiar firma
                                </button>
                                <span class="text-muted small">La firma quedara incluida en la solicitud generada.</span>
                            </div>
                        </div>

                        <div class="vac-official-actions vac-no-print">
                            <button type="button" class="btn btn-primary" id="vacBtnGenerarPdf">
                                <i class="fa-solid fa-file-pdf me-1"></i> Generar PDF oficial
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="vacBtnImprimirFormato">
                                <i class="fa-solid fa-print me-1"></i> Imprimir formato
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer vac-no-print">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="vacBtnConfirmarSolicitud">
                    <i class="fa-solid fa-paper-plane me-1"></i> Enviar solicitud firmada
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const params = new URLSearchParams(window.location.search);
    const idPersona = params.get('id_persona') || '';
    let modoFechas = 'rango';
    let diasSeparados = [];
    let diasDisponibles = 0;
    let diasPreviewActual = 0;
    let periodoInicioActual = '';
    let periodoFinActual = '';
    let avisoLimiteSeparadosMostrado = false;
    let personaActual = {};
    let periodoActual = {};
    let solicitudPendiente = null;
    let firmaDibujada = false;
    let firmaCanvasInicializado = false;

    function fmtNum(v) {
        const n = Number(v || 0);
        return Number.isInteger(n) ? String(n) : n.toFixed(1);
    }

    function fmtFecha(v) {
        if (!v) return '-';
        const parts = String(v).slice(0, 10).split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : v;
    }

    function escapeHtml(v) {
        return String(v ?? '').replace(/[&<>"']/g, (m) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[m]));
    }

    function anioDeFecha(v) {
        const fecha = normalizarFechaInput(v);
        return fecha ? fecha.slice(0, 4) : '-';
    }

    function siguienteDiaLaboral(fecha) {
        const dt = dateFromYmd(fecha);
        if (!dt) return '';
        do {
            dt.setDate(dt.getDate() + 1);
        } while (dt.getDay() === 0 || dt.getDay() === 6);
        return fechaYmd(dt);
    }

    function obtenerFechasSolicitudActual() {
        if (modoFechas === 'separados') {
            const fechas = [...diasSeparados].sort();
            return {
                fecha_inicio: fechas[0] || '',
                fecha_fin: fechas[fechas.length - 1] || '',
                fechas_separadas: fechas
            };
        }
        return {
            fecha_inicio: normalizarFechaInput($('vacFechaInicio').value),
            fecha_fin: normalizarFechaInput($('vacFechaFin').value),
            fechas_separadas: []
        };
    }

    function statusHtml(v) {
        const key = String(v || 'pendiente').toLowerCase();
        const label = key.charAt(0).toUpperCase() + key.slice(1);
        return `<span class="vac-status vac-status-${key}">${label}</span>`;
    }

    function swal(icon, title, text) {
        if (window.Swal) {
            return Swal.fire({ icon, title, text });
        }
        alert((title || '') + (text ? '\n' + text : ''));
    }

    function actualizarModoFechas(modo) {
        modoFechas = modo === 'separados' ? 'separados' : 'rango';
        document.querySelectorAll('[data-vac-mode]').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.vacMode === modoFechas);
        });
        $('vacModoRango').style.display = modoFechas === 'rango' ? '' : 'none';
        $('vacModoSeparados').style.display = modoFechas === 'separados' ? '' : 'none';
        $('vacFechaInicio').required = modoFechas === 'rango';
        $('vacFechaFin').required = modoFechas === 'rango';
        actualizarLimitesCalendarios();
        actualizarPreviewDias();
    }

    function normalizarFechaInput(v) {
        const s = String(v || '').trim();
        return /^\d{4}-\d{2}-\d{2}$/.test(s) ? s : '';
    }

    function fechaYmd(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function dateFromYmd(value) {
        const fecha = normalizarFechaInput(value);
        return fecha ? new Date(fecha + 'T12:00:00') : null;
    }

    function ultimoDiaLaboralPermitido(inicio, diasPermitidos) {
        const start = dateFromYmd(inicio);
        if (!start || diasPermitidos <= 0) return null;
        let cuenta = 0;
        let actual = new Date(start.getTime());
        let ultimo = null;
        const limitePeriodo = dateFromYmd(periodoFinActual);
        while (cuenta < diasPermitidos) {
            if (limitePeriodo && actual > limitePeriodo) break;
            const dow = actual.getDay();
            if (dow >= 1 && dow <= 5) {
                cuenta++;
                ultimo = new Date(actual.getTime());
            }
            actual.setDate(actual.getDate() + 1);
        }
        return ultimo ? fechaYmd(ultimo) : null;
    }

    function actualizarLimitesCalendarios() {
        const fpInicio = $('vacFechaInicio')?._flatpickr;
        const fpFin = $('vacFechaFin')?._flatpickr;
        const fpSeparada = $('vacFechaSeparada')?._flatpickr;
        const disponiblesEnteros = Math.max(0, Math.floor(diasDisponibles));
        const baseLimits = {
            minDate: periodoInicioActual || null,
            maxDate: periodoFinActual || null
        };

        if (fpInicio) {
            fpInicio.set('minDate', baseLimits.minDate);
            fpInicio.set('maxDate', baseLimits.maxDate);
        }

        if (fpFin) {
            const inicio = normalizarFechaInput($('vacFechaInicio').value);
            const maxPorSaldo = inicio ? ultimoDiaLaboralPermitido(inicio, disponiblesEnteros) : null;
            fpFin.set('minDate', inicio || baseLimits.minDate);
            fpFin.set('maxDate', maxPorSaldo || baseLimits.maxDate);
            const finActual = normalizarFechaInput($('vacFechaFin').value);
            if (finActual && maxPorSaldo && finActual > maxPorSaldo) {
                fpFin.clear();
                actualizarPreviewDias();
            }
        }

        if (fpSeparada) {
            fpSeparada.set('minDate', baseLimits.minDate);
            fpSeparada.set('maxDate', baseLimits.maxDate);
            fpSeparada.redraw();
        }
    }

    function initCalendarios() {
        if (typeof flatpickr === 'undefined') {
            return false;
        }
        const baseOpts = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: false,
            clickOpens: true,
            appendTo: document.body,
            static: false,
            locale: (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : undefined
        };

        const inicio = $('vacFechaInicio');
        if (inicio && !inicio._flatpickr) {
            flatpickr(inicio, {
                ...baseOpts,
                onChange: function () {
                    const fpFin = $('vacFechaFin')?._flatpickr;
                    if (fpFin) fpFin.clear();
                    actualizarLimitesCalendarios();
                    actualizarPreviewDias();
                }
            });
        }

        const fin = $('vacFechaFin');
        if (fin && !fin._flatpickr) {
            flatpickr(fin, {
                ...baseOpts,
                onChange: function () {
                    actualizarLimitesCalendarios();
                    actualizarPreviewDias();
                }
            });
        }

        const separada = $('vacFechaSeparada');
        if (separada && !separada._flatpickr) {
            flatpickr(separada, {
                ...baseOpts,
                mode: 'multiple',
                conjunction: ', ',
                disable: [
                    function (date) {
                        const ymd = fechaYmd(date);
                        const disponiblesEnteros = Math.max(0, Math.floor(diasDisponibles));
                        return diasSeparados.length >= disponiblesEnteros && !diasSeparados.includes(ymd);
                    }
                ],
                onChange: function (selectedDates, _dateStr, instance) {
                    const fechas = selectedDates.map(fechaYmd).sort();
                    const disponiblesEnteros = Math.max(0, Math.floor(diasDisponibles));
                    if (fechas.length > disponiblesEnteros) {
                        const permitidas = fechas.slice(0, disponiblesEnteros);
                        instance.setDate(permitidas, false);
                        diasSeparados = permitidas;
                        if (!avisoLimiteSeparadosMostrado) {
                            avisoLimiteSeparadosMostrado = true;
                            swal('warning', 'Saldo insuficiente', `Solo tiene ${fmtNum(diasDisponibles)} días disponibles. No puedes seleccionar más días.`);
                            setTimeout(() => { avisoLimiteSeparadosMostrado = false; }, 800);
                        }
                    } else {
                        diasSeparados = fechas;
                    }
                    renderDiasSeparados();
                    instance.redraw();
                }
            });
        }
        actualizarLimitesCalendarios();
        return true;
    }

    function scheduleInitCalendarios(intentos = 0) {
        if (initCalendarios()) return;
        if (intentos >= 25) return;
        setTimeout(() => scheduleInitCalendarios(intentos + 1), 120);
    }

    function calcularLaboralesRango(inicio, fin) {
        const dias = [];
        if (!inicio || !fin || fin < inicio) return dias;
        const actual = new Date(inicio + 'T12:00:00');
        const limite = new Date(fin + 'T12:00:00');
        while (actual <= limite) {
            const dow = actual.getDay();
            const y = actual.getFullYear();
            const m = String(actual.getMonth() + 1).padStart(2, '0');
            const d = String(actual.getDate()).padStart(2, '0');
            if (dow >= 1 && dow <= 5) dias.push(`${y}-${m}-${d}`);
            actual.setDate(actual.getDate() + 1);
        }
        return dias;
    }

    function renderDiasSeparados() {
        if (!diasSeparados.length) {
            $('vacDiasSeleccionados').innerHTML = '<span class="text-muted small">Aún no has agregado días.</span>';
            actualizarPreviewDias();
            return;
        }

        $('vacDiasSeleccionados').innerHTML = diasSeparados.map((fecha) => `
            <span class="vac-day-chip">
                ${fmtFecha(fecha)}
                <button type="button" aria-label="Quitar ${fmtFecha(fecha)}" data-remove-day="${fecha}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </span>
        `).join('');
        actualizarPreviewDias();
    }

    function actualizarPreviewDias() {
        let total = 0;
        if (modoFechas === 'separados') {
            total = diasSeparados.length;
        } else {
            total = calcularLaboralesRango($('vacFechaInicio').value, $('vacFechaFin').value).length;
        }
        diasPreviewActual = total;
        const excede = total > diasDisponibles;
        $('vacDiasPreview').textContent = `${fmtNum(total)} ${total === 1 ? 'día' : 'días'}`;
        $('vacDiasPreview').classList.toggle('vac-days-over', excede);
        let warning = $('vacDiasWarning');
        if (!warning) {
            warning = document.createElement('div');
            warning.id = 'vacDiasWarning';
            warning.className = 'vac-days-warning';
            $('vacDiasPreview').insertAdjacentElement('afterend', warning);
        }
        warning.textContent = excede
            ? `Solo tiene ${fmtNum(diasDisponibles)} días disponibles. Ajusta la solicitud para no exceder el saldo.`
            : '';
        $('vacBtnSolicitar').disabled = excede || total <= 0;
    }

    function ajustarCanvasFirma() {
        const canvas = $('vacFirmaCanvas');
        if (!canvas) return;
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = Math.max(1, Math.floor(rect.width * ratio));
        canvas.height = Math.max(1, Math.floor(rect.height * ratio));
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.lineWidth = 2.4 * ratio;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#24324a';
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        firmaDibujada = false;
    }

    function initFirmaCanvas() {
        const canvas = $('vacFirmaCanvas');
        if (!canvas || firmaCanvasInicializado) return;
        firmaCanvasInicializado = true;
        let dibujando = false;
        let ultimo = null;

        function punto(ev) {
            const rect = canvas.getBoundingClientRect();
            const touch = ev.touches && ev.touches.length ? ev.touches[0] : ev;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (touch.clientX - rect.left) * scaleX,
                y: (touch.clientY - rect.top) * scaleY
            };
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
            firmaDibujada = true;
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
        window.addEventListener('resize', ajustarCanvasFirma);
    }

    function limpiarFirma() {
        ajustarCanvasFirma();
    }

    function datosSolicitudPendiente() {
        const fechas = obtenerFechasSolicitudActual();
        return {
            modo_fechas: modoFechas,
            fecha_inicio: modoFechas === 'rango' ? fechas.fecha_inicio : '',
            fecha_fin: modoFechas === 'rango' ? fechas.fecha_fin : '',
            fechas_separadas: modoFechas === 'separados' ? fechas.fechas_separadas : [],
            comentario: $('vacComentario').value.trim(),
            fecha_inicio_real: fechas.fecha_inicio,
            fecha_fin_real: fechas.fecha_fin,
            reincorporacion: siguienteDiaLaboral(fechas.fecha_fin)
        };
    }

    function abrirFormatoSolicitud() {
        const datos = datosSolicitudPendiente();
        solicitudPendiente = datos;
        const areaDepto = [
            personaActual.area || '',
            personaActual.departamento || ''
        ].filter(Boolean).join(' / ') || 'No asignado';
        const pendientesDespues = Math.max(0, Number(diasDisponibles || 0) - Number(diasPreviewActual || 0));
        const observaciones = datos.comentario || (modoFechas === 'separados'
            ? `Dias solicitados: ${datos.fechas_separadas.map(fmtFecha).join(', ')}`
            : 'Solicitud de vacaciones');

        $('vacFmtNombre').textContent = personaActual.nombre_completo || '-';
        $('vacFmtAreaDepto').textContent = areaDepto;
        $('vacFmtOtorgados').textContent = fmtNum(periodoActual.dias_otorgados || 0);
        $('vacFmtDisfrutar').textContent = fmtNum(diasPreviewActual);
        $('vacFmtPendientes').textContent = fmtNum(pendientesDespues);
        $('vacFmtPeriodoInicio').textContent = anioDeFecha(periodoActual.periodo_inicio);
        $('vacFmtPeriodoFin').textContent = anioDeFecha(periodoActual.periodo_fin);
        $('vacFmtInicio').textContent = fmtFecha(datos.fecha_inicio_real);
        $('vacFmtReincorporacion').textContent = fmtFecha(datos.reincorporacion);
        $('vacFmtObservaciones').textContent = observaciones;

        const modalEl = $('vacModalFormato');
        const modal = window.bootstrap && bootstrap.Modal
            ? bootstrap.Modal.getOrCreateInstance(modalEl)
            : null;
        if (modal) {
            modal.show();
            setTimeout(() => {
                initFirmaCanvas();
                ajustarCanvasFirma();
            }, 220);
        } else {
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            initFirmaCanvas();
            ajustarCanvasFirma();
        }
    }

    function cerrarFormatoSolicitud() {
        const modalEl = $('vacModalFormato');
        const modal = window.bootstrap && bootstrap.Modal
            ? bootstrap.Modal.getInstance(modalEl)
            : null;
        if (modal) modal.hide();
    }

    function htmlFormatoImpresion(sourceId, canvasId) {
        const source = $(sourceId);
        const clone = source.cloneNode(true);
        clone.querySelectorAll('.vac-no-print').forEach((el) => el.remove());
        const originalCanvas = $(canvasId);
        const cloneCanvas = clone.querySelector('#' + canvasId);
        if (originalCanvas && cloneCanvas) {
            const img = document.createElement('img');
            img.src = originalCanvas.toDataURL('image/png');
            img.alt = 'Firma digital';
            img.className = 'vac-print-signature-img';
            cloneCanvas.replaceWith(img);
        }
        return clone.outerHTML;
    }

    function imprimirFormato() {
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
.vac-official-form { width: 180mm; margin: 0 auto; border: 0; border-radius: 0; overflow: hidden; background: #fff; }
.vac-official-head { display: grid; grid-template-columns: 31mm 1fr 35mm; gap: 4mm; align-items: center; min-height: 21mm; background: #064aa2; color: #fff; padding: 4mm 6mm; }
.vac-official-logo { height: 15mm; display: flex; align-items: center; justify-content: center; border: 1px dashed rgba(255,255,255,.6); border-radius: 1.5mm; background: rgba(255,255,255,.08); padding: 1.5mm; }
.vac-official-logo img { width: 11mm; height: 11mm; object-fit: contain; }
.vac-official-title { font-size: 16px; font-weight: 900; line-height: 1.15; color: #fff; }
.vac-official-subtitle { font-size: 11px; opacity: .95; margin-top: 2px; color: #fff; }
.vac-official-badge { justify-self: end; border-radius: 999px; background: #a8e30f; color: #09285a; font-size: 10px; font-weight: 900; padding: 2mm 4mm; white-space: nowrap; }
.vac-official-body { padding: 7mm 7mm 0; }
.vac-official-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4mm; margin-bottom: 5mm; }
.vac-official-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.vac-official-field label { display: block; color: #00275f; font-size: 10px; font-weight: 900; letter-spacing: .02em; text-transform: uppercase; margin-bottom: 2mm; }
.vac-official-value { min-height: 10mm; border: 1px solid #d8e2ef; border-radius: 1.5mm; background: #fff; color: #24324a; padding: 2.6mm 3mm; font-size: 13px; font-weight: 700; line-height: 1.2; }
.vac-official-value.muted { background: #f6f8fb; color: #607089; }
.vac-sign-box { border: 1px solid #d8e2ef; border-radius: 2mm; background: #f8fbff; padding: 4mm; break-inside: avoid; }
.vac-sign-canvas-wrap { background: #fff; border: 1px solid #d8e2ef; border-radius: 1.5mm; overflow: hidden; min-height: 31mm; }
.vac-print-signature-img { width: 100%; height: 31mm; object-fit: contain; display: block; background: #fff; }
.mb-4 { margin-bottom: 5mm; }
.mb-2 { margin-bottom: 8px; }
.fw-bold { font-weight: 700; }
.text-muted { color: #65758b; }
.small { font-size: 11px; }
h6 { margin: 0 0 2mm; font-size: 14px; }
</style>
</head>
<body>${htmlFormatoImpresion('vacFormatoPrint', 'vacFirmaCanvas')}</body>
</html>`);
        doc.close();
        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(() => iframe.remove(), 500);
        }, 250);
    }

    async function enviarSolicitudFirmada() {
        if (!solicitudPendiente) return;
        if (!firmaDibujada) {
            swal('warning', 'Firma requerida', 'Dibuja tu firma antes de enviar la solicitud.');
            return;
        }

        const body = {
            modo_fechas: solicitudPendiente.modo_fechas,
            fecha_inicio: solicitudPendiente.fecha_inicio,
            fecha_fin: solicitudPendiente.fecha_fin,
            fechas_separadas: solicitudPendiente.fechas_separadas,
            comentario: solicitudPendiente.comentario,
            firma_colaborador: $('vacFirmaCanvas').toDataURL('image/png')
        };
        if (idPersona) body.id_persona = idPersona;

        $('vacBtnConfirmarSolicitud').disabled = true;
        try {
            const res = await fetch('/caphum/solicitarVacaciones', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (!data.success) {
                swal('warning', 'No se pudo registrar', data.mensaje || 'Revisa la solicitud.');
                return;
            }
            cerrarFormatoSolicitud();
            $('vacFormSolicitud').reset();
            ['vacFechaInicio', 'vacFechaFin', 'vacFechaSeparada'].forEach((id) => {
                const el = $(id);
                if (el && el._flatpickr) el._flatpickr.clear();
            });
            diasSeparados = [];
            solicitudPendiente = null;
            renderDiasSeparados();
            actualizarModoFechas('rango');
            await cargarResumen();
            swal('success', 'Solicitud enviada', data.mensaje || 'Solicitud registrada.');
        } catch (err) {
            console.error(err);
            swal('error', 'Error', 'No se pudo enviar la solicitud.');
        } finally {
            $('vacBtnConfirmarSolicitud').disabled = false;
            actualizarPreviewDias();
        }
    }

    async function cargarResumen() {
        $('vacHistorialBody').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr>';
        const url = '/caphum/getResumenVacaciones' + (idPersona ? `?id_persona=${encodeURIComponent(idPersona)}` : '');
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.success) {
            swal('error', 'Error', data.mensaje || 'No se pudo cargar el resumen.');
            return;
        }

        const payload = data.datos || {};
        const persona = payload.persona || {};
        const periodo = payload.periodo || {};
        const solicitudes = Array.isArray(payload.solicitudes) ? payload.solicitudes : [];
        personaActual = persona;
        periodoActual = periodo;

        $('vacNombre').textContent = persona.nombre_completo || 'Sin nombre';
        $('vacMetaPersona').textContent = [
            persona.numero_empleado ? `Empleado ${persona.numero_empleado}` : '',
            persona.fecha_ingreso ? `Ingreso ${fmtFecha(persona.fecha_ingreso)}` : 'Sin fecha de ingreso'
        ].filter(Boolean).join(' · ');

        $('vacPeriodo').textContent = periodo.periodo_inicio && periodo.periodo_fin
            ? `${fmtFecha(periodo.periodo_inicio)} - ${fmtFecha(periodo.periodo_fin)}`
            : 'Sin periodo disponible';
        $('vacAniversario').textContent = periodo.proximo_aniversario
            ? `Próximo aniversario: ${fmtFecha(periodo.proximo_aniversario)}`
            : '-';

        const otorgados = Number(periodo.dias_otorgados || 0);
        const aprobados = Number(periodo.dias_aprobados || 0);
        const pendientes = Number(periodo.dias_pendientes || 0);
        const disponibles = Number(periodo.dias_disponibles || 0);
        periodoInicioActual = normalizarFechaInput(periodo.periodo_inicio);
        periodoFinActual = normalizarFechaInput(periodo.periodo_fin);
        diasDisponibles = disponibles;
        const usadosReservados = aprobados + pendientes;
        const pct = otorgados > 0 ? Math.min(100, Math.round((usadosReservados / otorgados) * 100)) : 0;

        $('vacDiasOtorgados').textContent = fmtNum(otorgados);
        $('vacDiasAprobados').textContent = fmtNum(aprobados);
        $('vacDiasPendientes').textContent = fmtNum(pendientes);
        $('vacDiasDisponibles').textContent = fmtNum(disponibles);
        $('vacUsoTexto').textContent = `${fmtNum(usadosReservados)} / ${fmtNum(otorgados)}`;
        $('vacUsoBarra').style.width = pct + '%';
        actualizarLimitesCalendarios();
        actualizarPreviewDias();

        $('vacTotalSolicitudes').textContent = `${solicitudes.length} registros`;
        if (!solicitudes.length) {
            $('vacHistorialBody').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay solicitudes registradas.</td></tr>';
            return;
        }

        $('vacHistorialBody').innerHTML = solicitudes.map((s) => `
            <tr>
                <td>
                    <strong>${fmtFecha(s.fecha_inicio)} - ${fmtFecha(s.fecha_fin)}</strong>
                    <div class="small text-muted">${s.modo_fechas === 'separados' ? 'Días separados' : 'Rango continuo'}</div>
                    <div class="small text-muted">${s.comentario || ''}</div>
                </td>
                <td>${fmtFecha(s.periodo_inicio)} - ${fmtFecha(s.periodo_fin)}</td>
                <td><strong>${fmtNum(s.dias_solicitados)}</strong></td>
                <td>${statusHtml(s.estatus)}</td>
                <td>${fmtFecha(s.creado_en)}</td>
            </tr>
        `).join('');
    }

    $('vacFormSolicitud').addEventListener('submit', async function (ev) {
        ev.preventDefault();
        $('vacBtnSolicitar').disabled = true;
        try {
            actualizarPreviewDias();
            if (diasPreviewActual <= 0) {
                swal('warning', 'Fechas requeridas', 'Selecciona los días de vacaciones que quieres solicitar.');
                return;
            }
            if (diasPreviewActual > diasDisponibles) {
                swal(
                    'warning',
                    'Saldo insuficiente',
                    `Solo tiene ${fmtNum(diasDisponibles)} días disponibles y está intentando solicitar ${fmtNum(diasPreviewActual)}.`
                );
                return;
            }
            abrirFormatoSolicitud();
        } catch (err) {
            console.error(err);
            swal('error', 'Error', 'No se pudo preparar la solicitud.');
        } finally {
            actualizarPreviewDias();
        }
    });

    document.querySelectorAll('[data-vac-mode]').forEach((btn) => {
        btn.addEventListener('click', () => actualizarModoFechas(btn.dataset.vacMode));
    });

    $('vacBtnAgregarDia').addEventListener('click', function () {
        const fecha = normalizarFechaInput($('vacFechaSeparada').value);
        if (!fecha) {
            swal('warning', 'Fecha requerida', 'Selecciona un día para agregar.');
            return;
        }
        if (diasSeparados.includes(fecha)) {
            $('vacFechaSeparada').value = '';
            return;
        }
        if (diasSeparados.length + 1 > diasDisponibles) {
            swal(
                'warning',
                'Saldo insuficiente',
                `Solo tiene ${fmtNum(diasDisponibles)} días disponibles. No puedes agregar más días a esta solicitud.`
            );
            return;
        }
        diasSeparados.push(fecha);
        diasSeparados.sort();
        const fp = $('vacFechaSeparada')?._flatpickr;
        if (fp) {
            fp.setDate(diasSeparados, false);
            fp.redraw();
        } else {
            $('vacFechaSeparada').value = '';
        }
        renderDiasSeparados();
    });

    $('vacDiasSeleccionados').addEventListener('click', function (ev) {
        const btn = ev.target.closest('[data-remove-day]');
        if (!btn) return;
        const fecha = btn.getAttribute('data-remove-day');
        diasSeparados = diasSeparados.filter((d) => d !== fecha);
        const fp = $('vacFechaSeparada')?._flatpickr;
        if (fp) {
            fp.setDate(diasSeparados, false);
            fp.redraw();
        }
        renderDiasSeparados();
    });

    $('vacFechaInicio').addEventListener('change', actualizarPreviewDias);
    $('vacFechaFin').addEventListener('change', actualizarPreviewDias);
    $('vacBtnRefrescar').addEventListener('click', cargarResumen);
    $('vacBtnLimpiarFirma').addEventListener('click', limpiarFirma);
    $('vacBtnGenerarPdf').addEventListener('click', imprimirFormato);
    $('vacBtnImprimirFormato').addEventListener('click', imprimirFormato);
    $('vacBtnConfirmarSolicitud').addEventListener('click', enviarSolicitudFirmada);
    scheduleInitCalendarios();
    actualizarModoFechas('rango');
    cargarResumen().catch((err) => {
        console.error(err);
        swal('error', 'Error', 'No se pudo cargar vacaciones.');
    });
})();
</script>
