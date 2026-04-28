<style>
.ar-header-gradient {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.ar-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.ar-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.88; color: #fff; }
.ar-header-gradient i  { color: #fff; }

.ac-nav-tabs .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.6rem 1.4rem;
}
.ac-nav-tabs .nav-link.active {
    color: #0d9488;
    border-bottom-color: #fff !important;
}
.ac-nav-tabs .nav-link:hover:not(.active) {
    color: #0d9488;
    background: #f0fdfa;
}

.ac-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.ac-card:hover { box-shadow: 0 4px 18px rgba(15,118,110,.12); }
.ac-card.ar-card-dict {
    border-color: #cbd5e1;
    background: #fafafa;
}

.ac-card-header {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.ac-card-header .ac-credito-id {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: .3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
}
.ac-card-header .ac-credito-id small {
    font-weight: 400;
    font-size: .75rem;
    opacity: .9;
    margin-left: .35rem;
}

.ac-card-body { padding: 1.1rem 1.25rem; }
.ac-detail-row {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    margin-bottom: .45rem;
    font-size: .875rem;
}
.ac-detail-row:last-child { margin-bottom: 0; }
.ac-detail-row .ac-lbl {
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 140px;
}
.ac-detail-row .ac-val {
    color: #1e293b;
    font-weight: 500;
}

.ac-card-footer {
    border-top: 1px solid #e2e8f0;
    padding: .75rem 1.25rem;
    display: flex;
    justify-content: flex-end;
    background: #f8fafc;
    gap: .5rem;
}

.ar-btn-pipeline {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .85rem;
    padding: .45rem 1.4rem;
    border-radius: 2rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    text-decoration: none;
    transition: opacity .2s, transform .15s;
}
.ar-btn-pipeline:hover  { opacity: .92; transform: translateY(-1px); color: #fff; }
.ar-btn-pipeline:active { transform: translateY(0); }

.ar-btn-evidencias {
    background: linear-gradient(135deg, #115e59 0%, #0d9488 100%);
    border: none;
    color: #fff !important;
    font-weight: 700;
    font-size: .85rem;
    padding: .45rem 1.25rem;
    border-radius: 2rem;
}
.ar-btn-evidencias:hover { opacity: .92; color: #fff !important; }

/* ── Modal evidencias (Recuperación) ── */
/* dark-mode.css fuerza .modal-header a fondo blanco (!important); sin esto el título text-white no se ve */
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%) !important;
    border: none !important;
    border-bottom: none !important;
    color: #fff !important;
    padding: .85rem 1.15rem;
}
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .modal-title,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .modal-title span,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-subtitle,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-subtitle strong {
    color: #fff !important;
}
/* En cabecera el progreso inline no debe usar el verde oscuro del cuerpo del modal */
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-prog-lbl {
    color: rgba(255, 255, 255, 0.95) !important;
}
#modalArRecuperacionEvidencias .modal-header .ar-ev-subtitle {
    font-size: .78rem;
    opacity: .95;
    margin-top: .2rem;
}
#modalArRecuperacionEvidencias .modal-header .btn-close { filter: brightness(0) invert(1); }
#modalArRecuperacionEvidencias .ar-ev-prog-bg {
    height: 8px;
    background: #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: .75rem;
}
#modalArRecuperacionEvidencias .ar-ev-prog-fill {
    height: 100%;
    background: linear-gradient(90deg, #0d9488, #5eead4);
    border-radius: 6px;
    transition: width .25s ease;
}
#modalArRecuperacionEvidencias .ar-ev-prog-lbl { font-size: .8rem; font-weight: 800; color: #0f766e; }

.ar-ev-sec { margin-bottom: 1rem; }
.ar-ev-hdr {
    display: flex; align-items: center; gap: .45rem;
    padding: .38rem .65rem;
    border-radius: .45rem .45rem 0 0;
    font-size: .62rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
}
.ar-ev-hdr-orange { background: #fff7ed; border: 1px solid #fed7aa; border-bottom: 0; color: #9a3412; }
.ar-ev-hdr-blue   { background: #eff6ff; border: 1px solid #bfdbfe; border-bottom: 0; color: #1e40af; }
.ar-ev-hdr-green  { background: #f0fdf4; border: 1px solid #bbf7d0; border-bottom: 0; color: #14532d; }
.ar-ev-hdr-purple { background: #faf5ff; border: 1px solid #e9d5ff; border-bottom: 0; color: #6b21a8; }

.ar-ev-slots-wrap {
    padding: .55rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-top: 0;
    border-radius: 0 0 .45rem .45rem;
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
}
.ar-ev-slot {
    flex: 0 0 108px;
    width: 108px;
    height: 108px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: .5rem;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .15rem;
}
.ar-ev-slot--vac { border-style: dashed; color: #94a3b8; font-size: .62rem; text-align: center; padding: .25rem; }
.ar-ev-slot--pend { border-color: #f59e0b; }
.ar-ev-slot--acep { border-color: #22c55a; background: #f0fdf4; }
.ar-ev-slot--rec  { border-color: #ef4444; background: #fef2f2; }
.ar-ev-slot--click { cursor: pointer; }
.ar-ev-slot .ar-ev-thumb {
    position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
}
.ar-ev-slot .ar-ev-lbl {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(15,23,42,.78); color: #fff;
    font-size: .5rem; font-weight: 700; text-transform: uppercase;
    padding: .15rem; text-align: center;
}
.ar-ev-badge-mini {
    position: absolute; bottom: 1.15rem; left: 50%; transform: translateX(-50%);
    font-size: .5rem; font-weight: 800; padding: 1px 5px; border-radius: 3px; color: #fff;
}
.ar-ev-badge-mini.ok { background: #16a34a; }
.ar-ev-badge-mini.no { background: #dc2626; }

.ar-ev-doc-zone {
    min-height: 88px;
    border: 2px dashed #cbd5e1;
    border-radius: .5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: .75rem;
    text-align: center;
    gap: .35rem;
    background: #fff;
    cursor: pointer;
}
.ar-ev-doc-zone--green { border-color: #86efac; background: #f0fdf4; }
.ar-ev-doc-zone--purple { border-color: #d8b4fe; background: #faf5ff; }
.ar-ev-doc-zone a { font-size: .8rem; font-weight: 700; }

.ar-ev-cartera-panel {
    border-color: #e2e8f0 !important;
    min-height: 120px;
}
.ar-ev-cartera-panel textarea.form-control {
    min-height: 140px;
}
body.dark-mode .ar-ev-cartera-panel { border-color: #334155 !important; }
body.dark-mode .ar-ev-cartera-panel .form-label { color: #e2e8f0; }
body.dark-mode .ar-ev-cartera-panel textarea.form-control {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0;
}
body.dark-mode #modalArRecuperacionEvidencias #ar-ev-btn-enviar-cartera {
    color: #fff;
}

.ar-ev-notas-panel {
    border: 1px solid #e2e8f0;
    border-radius: .65rem;
    background: #f8fafc;
    padding: .85rem;
    min-height: 180px;
    max-height: 420px;
    overflow-y: auto;
}
.ar-ev-notas-panel .ar-ev-notas-title {
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
    color: #64748b; margin-bottom: .5rem;
}

body.dark-mode .ac-nav-tabs .nav-link        { color: #94a3b8; }
body.dark-mode .ac-nav-tabs .nav-link.active { color: #5eead4; border-bottom-color: #1e293b !important; }
body.dark-mode .ac-nav-tabs .nav-link:hover:not(.active) { background: #134e4a; color: #5eead4; }
body.dark-mode .ac-card              { background: #1e293b; border-color: #334155; }
body.dark-mode .ac-card.ar-card-dict { background: #0f172a; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #0f172a; border-color: #334155; }
body.dark-mode .ar-ev-slots-wrap { background: #0f172a; border-color: #334155; }
body.dark-mode .ar-ev-slot { background: #1e293b; border-color: #475569; }
body.dark-mode .ar-ev-notas-panel { background: #0f172a; border-color: #334155; }

.ar-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}
</style>

<div class="container-fluid py-4">

    <div class="ar-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-truck-moving fa-2x"></i>
        <div>
            <h4>3.- Recuperación</h4>
            <p>Gestión de recuperación para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-tabs ac-nav-tabs border-0 mb-0" id="arTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ar-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#arTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="ar-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ar-tab-dictaminado-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#arTabDictaminado">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Dictaminado
                        <span class="badge bg-label-secondary ms-1" id="ar-badge-dictaminado" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="arTabContent">
            <div class="tab-pane fade show active" id="arTabBandeja" role="tabpanel">
                <div id="ar-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ar-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="arTabDictaminado" role="tabpanel">
                <div id="ar-loader-dictaminado" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ar-lista-dictaminado"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal evidencias (vista Recuperación — bandeja) -->
<div class="modal fade" id="modalArRecuperacionEvidencias" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header ar-ev-modal-header">
                <div class="flex-grow-1 min-w-0">
                    <h5 class="modal-title mb-0 text-white fw-bold text-truncate">
                        <i class="fa-solid fa-images me-2"></i>Evidencias —
                        <span id="ar-ev-titulo-cliente">—</span>
                    </h5>
                    <div class="ar-ev-subtitle text-white">
                        Progreso de evidencias <strong>validadas</strong>
                        <span class="ar-ev-prog-lbl ms-1" id="ar-ev-prog-inline">0 / 9</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <input type="file" id="ar-ev-inp-repuve" class="d-none" accept="application/pdf,.pdf" tabindex="-1">
            <input type="file" id="ar-ev-inp-factura" class="d-none" accept="application/pdf,.pdf,image/jpeg,image/png" tabindex="-1">
            <div class="modal-body p-3" id="ar-ev-modal-inner">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm" style="color:#14b8a6;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 d-flex justify-content-end align-items-center gap-2 flex-wrap">
                <button type="button" id="ar-ev-btn-enviar-cartera" class="btn btn-primary btn-sm rounded-pill fw-semibold"
                        style="display:none;">
                    <i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!function_exists('sparta_public_web_base')) {
    require_once dirname(__DIR__) . '/core/UploadsPaths.php';
}
$arPublicPath = function_exists('sparta_public_web_base') ? sparta_public_web_base() : '';
?>
<script>
(function () {
    'use strict';

    var AR_SERVER_PUBLIC_BASE = <?php echo json_encode($arPublicPath, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    const AR_EV_TOTAL_LISTA = 10;
    const AR_IMG_KEYS = [
        'rec_tacometro', 'rec_serie', 'rec_frontal', 'rec_lateral',
        'fis_vin', 'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360'
    ];
    const AR_TOTAL_VALIDABLE_IMG = 9;

    const AR_SEC_REC = {
        key: 'rec',
        label: 'Evidencia de recolección (final)',
        hdr: 'ar-ev-hdr-orange',
        icon: 'fa-camera-retro',
        slots: [
            { key: 'rec_tacometro', label: 'Tacómetro Rec.', icon: 'fa-gauge-high' },
            { key: 'rec_serie',     label: 'No. Serie Rec.',  icon: 'fa-hashtag' },
            { key: 'rec_frontal',   label: 'Frontal Rec.',    icon: 'fa-camera' },
            { key: 'rec_lateral',   label: 'Lateral Rec.',    icon: 'fa-camera-rotate' },
        ],
    };
    const AR_SEC_FIS = {
        key: 'fis',
        label: 'Evidencia física (momento 1)',
        hdr: 'ar-ev-hdr-blue',
        icon: 'fa-camera',
        slots: [
            { key: 'fis_vin',       label: 'Serie VIN',       icon: 'fa-barcode' },
            { key: 'fis_tacometro', label: 'Tacómetro',       icon: 'fa-gauge-high' },
            { key: 'fis_frontal',   label: 'Vista frontal',   icon: 'fa-camera' },
            { key: 'fis_lateral',   label: 'Vista lateral',    icon: 'fa-camera-rotate' },
            { key: 'fis_360',       label: 'Inspección 360',  icon: 'fa-video' },
        ],
    };

    const AR_SLOT_LABEL = {};
    [AR_SEC_REC, AR_SEC_FIS].forEach(function (sec) {
        sec.slots.forEach(function (sl) { AR_SLOT_LABEL[sl.key] = sl.label; });
    });
    AR_SLOT_LABEL.doc_repuve = 'Repuve';
    AR_SLOT_LABEL.doc_factura = 'Factura';

    const AR_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecuperacionEnTransito',
            vacio: 'No hay operaciones en bandeja. Aparecen aquí las que ya están en Evidencias → Aprobados (Procesando IA con envío validado).',
        },
        dictaminado: {
            url:   '/AtencionClientes/obtenerDictaminadosRecuperacion',
            vacio: 'No hay operaciones dictaminadas con expediente en esta etapa.',
        },
    };

    const AR_BADGE = { bandeja: 'ar-badge-bandeja', dictaminado: 'ar-badge-dictaminado' };

    /** Texto provisional en panel lateral (Bitácora forense IA) hasta integrar contenido real */
    const AR_BITACORA_FORENSE_LOREM =
        '<p class="mb-3 text-body-secondary" style="font-size:.82rem;line-height:1.55;">Lorem ipsum dolor sit amet, ' +
        'consectetur adipiscing elit. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium ' +
        'doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto ' +
        'beatae vitae dicta sunt explicabo.</p>' +
        '<p class="mb-0 text-body-secondary" style="font-size:.82rem;line-height:1.55;">Nemo enim ipsam voluptatem ' +
        'quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione ' +
        'voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, ' +
        'adipisci velit.</p>';

    let _arCargada = { bandeja: false, dictaminado: false };
    let _arEvDetalle = null;

    function arEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function arInferBaseDesdePathname() {
        const p = (window.location && window.location.pathname) || '';
        const segs = p.split('/').filter(function (x) { return x.length; });
        const k = segs.indexOf('public');
        if (k >= 0) {
            return '/' + segs.slice(0, k + 1).join('/');
        }
        return '';
    }

    function arBasePublic() {
        if (typeof AR_SERVER_PUBLIC_BASE === 'string' && AR_SERVER_PUBLIC_BASE.length > 0) {
            return AR_SERVER_PUBLIC_BASE;
        }
        if (window._arBaseCache !== undefined) {
            return window._arBaseCache;
        }
        const path = (window.location && window.location.pathname) || '';
        let base = '';
        const i = path.indexOf('/public/');
        if (i !== -1) {
            base = path.substring(0, i + '/public'.length);
        } else {
            base = arInferBaseDesdePathname();
        }
        window._arBaseCache = base;
        return base;
    }

    function arUrlForDisplay(u) {
        if (u == null || u === '') {
            return '';
        }
        let s = String(u).trim().replace(/\\/g, '/');
        s = s.replace(/^https?:\/\/uploads(?=\/|$)/i, '/uploads');
        s = s.replace(/^\/{2,}uploads(?=\/|$)/i, '/uploads');
        s = s.replace(/^\/uploads\/uploads\//i, '/uploads/');
        if (/^https?:\/\//i.test(s)) {
            return s;
        }
        const b = arBasePublic();
        if (b !== '' && (s.indexOf(b + '/') === 0 || s === b)) {
            return s;
        }
        if (s.indexOf('/uploads/') === 0) {
            return b ? b + s : s;
        }
        if (/^uploads\//i.test(s)) {
            s = '/' + s;
            return b ? b + s : s;
        }
        return s;
    }

    function arMapaPorSlot(evidencias) {
        const m = {};
        if (!evidencias || !Array.isArray(evidencias)) {
            return m;
        }
        evidencias.forEach(function (e) {
            if (e && e.slot) {
                m[e.slot] = e;
            }
        });
        return m;
    }

    function arEstadoSlot(row) {
        if (!row || !row.url) {
            return 'vac';
        }
        const va = row.val_atn != null && row.val_atn !== '' ? parseInt(row.val_atn, 10) : 0;
        if (va === 1) {
            return 'acep';
        }
        if (va === 2) {
            return 'rec';
        }
        return 'pend';
    }

    function arCuentaValidadasImg(map) {
        let n = 0;
        AR_IMG_KEYS.forEach(function (k) {
            const row = map[k];
            if (row && row.url && arEstadoSlot(row) === 'acep') {
                n++;
            }
        });
        return n;
    }

    function arRenderSlotHtml(sl, map) {
        const row = map[sl.key];
        const st = arEstadoSlot(row);
        const has = row && row.url;
        const uRaw = has ? arUrlForDisplay(row.url) : '';
        const uEsc = arEsc(uRaw);

        let cls = 'ar-ev-slot';
        if (!has) {
            cls += ' ar-ev-slot--vac';
        } else if (st === 'acep') {
            cls += ' ar-ev-slot--acep ar-ev-slot--click';
        } else if (st === 'rec') {
            cls += ' ar-ev-slot--rec ar-ev-slot--click';
        } else {
            cls += ' ar-ev-slot--pend ar-ev-slot--click';
        }

        if (!has) {
            return `
            <div class="${cls}">
                <i class="fa-solid ${sl.icon} opacity-50" style="font-size:1.1rem;"></i>
                <span style="line-height:1.15;">${arEsc(sl.label)}</span>
            </div>`;
        }

        const esVideo = (sl.key === 'fis_360') || (row.tipo && String(row.tipo).toLowerCase().indexOf('video') !== -1);
        const media = esVideo
            ? '<video class="ar-ev-thumb" muted playsinline preload="metadata" src="' + uEsc + '"></video>'
            : '<img class="ar-ev-thumb" src="' + uEsc + '" alt="">';
        let badge = '';
        if (st === 'acep') {
            badge = '<span class="ar-ev-badge-mini ok">ACEPTADA</span>';
        } else if (st === 'rec') {
            badge = '<span class="ar-ev-badge-mini no">RECHAZADA</span>';
        }

        return `
        <div class="${cls}" data-ar-ev-url="${uEsc}" title="Clic para abrir">
            ${media}
            <span class="ar-ev-lbl">${arEsc(sl.label)}</span>
            ${badge}
        </div>`;
    }

    function arRenderSeccion(sec, map) {
        let inner = '';
        sec.slots.forEach(function (sl) {
            inner += arRenderSlotHtml(sl, map);
        });
        return `
        <div class="ar-ev-sec">
            <div class="ar-ev-hdr ${sec.hdr}"><i class="fa-solid ${sec.icon}"></i> ${arEsc(sec.label)}</div>
            <div class="ar-ev-slots-wrap">${inner}</div>
        </div>`;
    }

    function arRenderDocRepuve(map) {
        const row = map.doc_repuve;
        const has = row && row.url;
        const url = has ? arUrlForDisplay(row.url) : '';
        if (has) {
            return `
            <div class="ar-ev-doc-zone ar-ev-doc-zone--green" role="button" tabindex="0"
                 data-ar-ev-doc-open="${arEsc(url)}">
                <i class="fa-solid fa-file-pdf fa-2x text-success"></i>
                <span class="fw-bold text-success small">VER REPUVE</span>
                <span class="text-muted" style="font-size:.65rem;">PDF en expediente</span>
            </div>`;
        }
        return `
        <div class="ar-ev-doc-zone ar-ev-doc-zone--green" id="ar-ev-zone-repuve" role="button" tabindex="0">
            <i class="fa-solid fa-eye fa-lg text-success opacity-75"></i>
            <span class="fw-bold text-success small">SUBIR REPUVE</span>
            <span class="text-muted" style="font-size:.65rem;">Solo PDF</span>
        </div>`;
    }

    function arRenderDocFactura(map) {
        const row = map.doc_factura;
        const has = row && row.url;
        const url = has ? arUrlForDisplay(row.url) : '';
        if (has) {
            return `
            <div class="ar-ev-doc-zone ar-ev-doc-zone--purple" role="button" tabindex="0"
                 data-ar-ev-doc-open="${arEsc(url)}">
                <i class="fa-solid fa-file-invoice fa-2x" style="color:#7c3aed;"></i>
                <span class="fw-bold small" style="color:#5b21b6;">VER FACTURA</span>
                <span class="text-muted" style="font-size:.65rem;">Documento cargado</span>
            </div>`;
        }
        return `
        <div class="ar-ev-doc-zone ar-ev-doc-zone--purple" id="ar-ev-zone-factura" role="button" tabindex="0">
            <i class="fa-solid fa-file-circle-plus fa-lg" style="color:#7c3aed;"></i>
            <span class="fw-bold small" style="color:#5b21b6;">SUBIR FACTURA</span>
            <span class="text-muted" style="font-size:.65rem;">Momento 3 · PDF o imagen</span>
        </div>`;
    }

    /** Tras subir factura (momento 3): comentarios a ancho completo (debajo de Repuve y Factura) */
    function arRenderFacturaCarteraComentarios(map) {
        const row = map.doc_factura;
        const has = row && row.url && String(row.url).trim() !== '';
        if (!has) {
            return '';
        }
        return (
            '<div class="ar-ev-cartera-panel mt-2 pt-3 border-top">' +
            '<label class="form-label fw-bold mb-1 small" for="ar-ev-comentarios-cartera">Comentarios:</label>' +
            '<textarea id="ar-ev-comentarios-cartera" class="form-control form-control-sm" rows="5" ' +
            'placeholder="Observaciones para cartera (opcional)" autocomplete="off"></textarea>' +
            '</div>'
        );
    }

    function arEvToggleFooterEnviarCartera(map) {
        const btn = document.getElementById('ar-ev-btn-enviar-cartera');
        if (!btn) {
            return;
        }
        const has = map && map.doc_factura && map.doc_factura.url && String(map.doc_factura.url).trim() !== '';
        btn.style.display = has ? '' : 'none';
        btn.disabled = false;
    }

    function arBindSlotClicks(root) {
        if (!root || !root.querySelectorAll) {
            return;
        }
        root.querySelectorAll('.ar-ev-slot--click[data-ar-ev-url]').forEach(function (el) {
            el.addEventListener('click', function () {
                const u = el.getAttribute('data-ar-ev-url');
                if (u) {
                    window.open(u, '_blank', 'noopener');
                }
            });
        });
    }

    function arEvRenderDetalle(det) {
        const inner = document.getElementById('ar-ev-modal-inner');
        if (!inner || !det) {
            return;
        }

        const map = arMapaPorSlot(det.evidencias || []);
        const validadas = arCuentaValidadasImg(map);
        const pct = AR_TOTAL_VALIDABLE_IMG ? Math.round((validadas / AR_TOTAL_VALIDABLE_IMG) * 100) : 0;

        const titulo = document.getElementById('ar-ev-titulo-cliente');
        const progIn = document.getElementById('ar-ev-prog-inline');
        if (titulo) {
            titulo.textContent = (det.nombre_cliente && String(det.nombre_cliente).trim())
                ? String(det.nombre_cliente).trim()
                : ('Operación #' + (det.id || ''));
        }
        if (progIn) {
            progIn.textContent = validadas + ' / ' + AR_TOTAL_VALIDABLE_IMG;
        }

        inner.innerHTML =
            '<div class="row g-3">' +
            '<div class="col-lg-8">' +
            '<div class="d-flex justify-content-between align-items-end mb-1 flex-wrap gap-1">' +
            '<span style="font-size:.75rem;font-weight:700;color:#0f172a;">Progreso de evidencias <span class="text-success">validadas</span> (fotos / video etapas 1–2)</span>' +
            '<span class="ar-ev-prog-lbl">' + validadas + ' / ' + AR_TOTAL_VALIDABLE_IMG + '</span>' +
            '</div>' +
            '<div class="ar-ev-prog-bg mb-3"><div class="ar-ev-prog-fill" style="width:' + pct + '%;"></div></div>' +
            arRenderSeccion(AR_SEC_REC, map) +
            arRenderSeccion(AR_SEC_FIS, map) +
            '<div class="row g-2 mt-1">' +
            '<div class="col-md-4">' +
            '<div class="ar-ev-hdr ar-ev-hdr-green mb-0"><i class="fa-solid fa-file-pdf"></i> Repuve</div>' +
            '<div class="pt-2">' + arRenderDocRepuve(map) + '</div></div>' +
            '<div class="col-md-8">' +
            '<div class="ar-ev-hdr ar-ev-hdr-purple mb-0"><i class="fa-solid fa-file-invoice"></i> Momento 3: Factura</div>' +
            '<div class="pt-2">' + arRenderDocFactura(map) + '</div></div>' +
            '</div>' +
            '<div class="row mt-2 g-2"><div class="col-12">' + arRenderFacturaCarteraComentarios(map) + '</div></div>' +
            '</div>' +
            '<div class="col-lg-4">' +
            '<div class="ar-ev-notas-panel">' +
            '<div class="ar-ev-notas-title"><i class="fa-solid fa-fingerprint me-1"></i> Bitácora forense IA</div>' +
            '<div id="ar-ev-notas-body">' + AR_BITACORA_FORENSE_LOREM + '</div>' +
            '</div></div></div>';

        arBindSlotClicks(inner);

        const zr = inner.querySelector('#ar-ev-zone-repuve');
        if (zr) {
            zr.addEventListener('click', function () {
                const inp = document.getElementById('ar-ev-inp-repuve');
                if (inp) {
                    inp.value = '';
                    inp.click();
                }
            });
        }
        const zf = inner.querySelector('#ar-ev-zone-factura');
        if (zf) {
            zf.addEventListener('click', function () {
                const inp = document.getElementById('ar-ev-inp-factura');
                if (inp) {
                    inp.value = '';
                    inp.click();
                }
            });
        }
        inner.querySelectorAll('[data-ar-ev-doc-open]').forEach(function (z) {
            z.addEventListener('click', function () {
                const u = z.getAttribute('data-ar-ev-doc-open');
                if (u) {
                    window.open(u, '_blank', 'noopener');
                }
            });
        });

        arEvToggleFooterEnviarCartera(map);
    }

    function arEvEnviarACartera() {
        if (!_arEvDetalle || !_arEvDetalle.id) {
            return;
        }
        const ta = document.getElementById('ar-ev-comentarios-cartera');
        const com = ta ? String(ta.value || '').trim() : '';
        const btn = document.getElementById('ar-ev-btn-enviar-cartera');
        if (btn) {
            btn.disabled = true;
        }
        fetch('/MotosAdjudicadas/enviarRecuperacionACartera', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ id_operacion: _arEvDetalle.id, comentarios: com }),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo enviar.');
                }
                const mEl = document.getElementById('modalArRecuperacionEvidencias');
                if (mEl && window.bootstrap && bootstrap.Modal.getInstance(mEl)) {
                    bootstrap.Modal.getInstance(mEl).hide();
                }
                arCargarSeccion('bandeja', true);
                arCargarSeccion('dictaminado', true);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enviado a Cartera',
                        html:
                            'La operación quedó en estatus <strong>Cierre documentado</strong>. ' +
                            'La verás en la <strong>bandeja de entrada</strong> de ' +
                            '<strong>4.- Cierre documentación</strong>.',
                        confirmButtonText: 'Ir a Cierre documentación',
                        showCancelButton: true,
                        cancelButtonText: 'Cerrar',
                        reverseButtons: true,
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            window.location.href = '/AtencionClientes/cierreDocumentacion';
                        }
                    });
                }
            })
            .catch(function (err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: err.message || 'Error' });
                }
                if (btn) {
                    btn.disabled = false;
                }
            });
    }

    function arEvRecargarDetalle(idOp, cb) {
        fetch('/MotosAdjudicadas/obtenerDetalle/' + parseInt(idOp, 10) + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.detalle) {
                    throw new Error((data && data.message) || 'No se pudo recargar');
                }
                _arEvDetalle = data.detalle;
                arEvRenderDetalle(_arEvDetalle);
                if (typeof cb === 'function') {
                    cb();
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'No se pudo actualizar el detalle.' });
                }
            });
    }

    window.arEvModalAbrir = function (idOperacion) {
        const id = parseInt(idOperacion, 10);
        if (!id) {
            return;
        }
        _arEvDetalle = null;

        const inner = document.getElementById('ar-ev-modal-inner');
        const titulo = document.getElementById('ar-ev-titulo-cliente');
        const progIn = document.getElementById('ar-ev-prog-inline');
        if (titulo) {
            titulo.textContent = '…';
        }
        if (progIn) {
            progIn.textContent = '… / ' + AR_TOTAL_VALIDABLE_IMG;
        }
        if (inner) {
            inner.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<div class="spinner-border spinner-border-sm" style="color:#14b8a6;"></div>' +
                '<div class="small mt-2">Cargando evidencias…</div></div>';
        }
        const btnFoot = document.getElementById('ar-ev-btn-enviar-cartera');
        if (btnFoot) {
            btnFoot.style.display = 'none';
        }

        const mEl = document.getElementById('modalArRecuperacionEvidencias');
        if (mEl && window.bootstrap) {
            (new bootstrap.Modal(mEl)).show();
        }

        fetch('/MotosAdjudicadas/obtenerDetalle/' + id + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.detalle) {
                    throw new Error(data.message || 'No se pudo cargar el detalle');
                }
                _arEvDetalle = data.detalle;
                arEvRenderDetalle(_arEvDetalle);
            })
            .catch(function (err) {
                if (inner) {
                    inner.innerHTML = '<div class="alert alert-danger mb-0">' + arEsc(err.message || 'Error') + '</div>';
                }
            });
    };

    function arEvSubirArchivo(slot, input) {
        const f = input.files && input.files[0];
        if (input) {
            input.value = '';
        }
        if (!f || !_arEvDetalle || !_arEvDetalle.id) {
            return;
        }
        const fd = new FormData();
        fd.append('id_operacion', String(_arEvDetalle.id));
        fd.append('slot', slot);
        fd.append('archivo', f, f.name);
        fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', text: data.message || 'No se pudo subir el archivo.' });
                    }
                    return;
                }
                arEvRecargarDetalle(_arEvDetalle.id);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Listo', text: 'Archivo guardado.', timer: 1600, showConfirmButton: false });
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'Error de red al subir.' });
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const inpR = document.getElementById('ar-ev-inp-repuve');
        const inpF = document.getElementById('ar-ev-inp-factura');
        if (inpR) {
            inpR.addEventListener('change', function () {
                arEvSubirArchivo('doc_repuve', inpR);
            });
        }
        if (inpF) {
            inpF.addEventListener('change', function () {
                arEvSubirArchivo('doc_factura', inpF);
            });
        }
    });

    function arSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${arEsc(msg)}</span>
        </div>`;
    }

    function arRenderCardBandeja(item) {
        const ev = parseInt(item.evidencias_count, 10) || 0;
        const g  = item.gestor_nombre
            ? arEsc(item.gestor_nombre)
            : '<span class="text-muted fst-italic">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? arEsc(item.fecha_asignacion)
            : '<span class="text-muted fst-italic">—</span>';
        const est = item.estatus ? arEsc(item.estatus) : '—';
        const dias = item.dias_en_pipeline != null && item.dias_en_pipeline !== ''
            ? arEsc(String(item.dias_en_pipeline))
            : '—';
        const idOp = parseInt(item.id, 10) || 0;

        return `
        <div class="ac-card">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${arEsc(String(item.id_credito))}
                    <small>${arEsc(item.nombre_cliente || '')}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">${arEsc(item.folio || '—')}</span>
            </div>
            <div class="ac-card-body">
                <div class="ac-detail-row">
                    <span class="ac-lbl">Estatus pipeline</span>
                    <span class="ac-val">${est}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Gestor a cargo</span>
                    <span class="ac-val">${g}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Asignación realizada</span>
                    <span class="ac-val">${fa}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Evidencias en expediente</span>
                    <span class="ac-val">${ev} / ${AR_EV_TOTAL_LISTA}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Días en pipeline</span>
                    <span class="ac-val">${dias}</span>
                </div>
            </div>
            <div class="ac-card-footer d-flex flex-wrap justify-content-end gap-2 align-items-center">
                <button type="button" class="btn btn-sm ar-btn-evidencias"
                        onclick="arEvModalAbrir(${idOp})" ${idOp ? '' : 'disabled'}>
                    <i class="fa-solid fa-images me-1"></i>Evidencias
                </button>
            </div>
        </div>`;
    }

    function arRenderCardDictaminado(item) {
        const estPipeline = item.estatus ? arEsc(item.estatus) : '—';
        const dictTxt = item.dictamen
            ? arEsc(item.dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const fechaD = item.fecha_dictamen
            ? arEsc(item.fecha_dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const g = item.gestor_nombre
            ? arEsc(item.gestor_nombre)
            : '<span class="text-muted fst-italic">Sin asignar</span>';

        return `
        <div class="ac-card ar-card-dict">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${arEsc(String(item.id_credito))}
                    <small>${arEsc(item.nombre_cliente || '')}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">${arEsc(item.folio || '—')}</span>
            </div>
            <div class="ac-card-body">
                <div class="ac-detail-row">
                    <span class="ac-lbl">Estatus pipeline</span>
                    <span class="ac-val"><span class="badge bg-secondary">${estPipeline}</span></span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Dictamen</span>
                    <span class="ac-val">${dictTxt}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Gestor a cargo</span>
                    <span class="ac-val">${g}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Fecha dictamen</span>
                    <span class="ac-val">${fechaD}</span>
                </div>
                ${item.comentarios ? `
                <div class="ac-detail-row" style="align-items:flex-start;">
                    <span class="ac-lbl">Comentarios</span>
                    <span class="ac-val" style="white-space:pre-line;">${arEsc(item.comentarios)}</span>
                </div>` : ''}
            </div>
        </div>`;
    }

    function arSetBadge(key, n) {
        const el = document.getElementById(AR_BADGE[key]);
        if (!el) return;
        if (n > 0) {
            el.textContent   = n;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    function arCargarSeccion(key, forzar) {
        const cfg = AR_CONFIG[key];
        if (!cfg) return;

        const suf      = key === 'bandeja' ? 'bandeja' : 'dictaminado';
        const loaderId = 'ar-loader-' + suf;
        const listaId  = 'ar-lista-'  + suf;

        if (!forzar && _arCargada[key]) {
            return;
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return;

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('ar-lista-updating');
        }

        fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const n = data.datos.length;
                arSetBadge(key, n);
                _arCargada[key] = true;

                const render = key === 'bandeja' ? arRenderCardBandeja : arRenderCardDictaminado;
                if (n === 0) {
                    lista.innerHTML = arSinDatos(cfg.vacio);
                } else {
                    lista.innerHTML = data.datos.map(function (d) { return render(d); }).join('');
                }
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${arEsc(err.message || 'Error')}</div>`;
                arSetBadge(key, 0);
            })
            .finally(function () {
                loader.style.display = 'none';
                lista.classList.remove('ar-lista-updating');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        arCargarSeccion('bandeja', true);

        const bb = document.getElementById('ar-tab-bandeja-btn');
        const bd = document.getElementById('ar-tab-dictaminado-btn');
        if (bb) {
            bb.addEventListener('shown.bs.tab', function () { arCargarSeccion('bandeja', false); });
        }
        if (bd) {
            bd.addEventListener('shown.bs.tab', function () { arCargarSeccion('dictaminado', false); });
        }

        const btnEnviarCartera = document.getElementById('ar-ev-btn-enviar-cartera');
        if (btnEnviarCartera) {
            btnEnviarCartera.addEventListener('click', function () {
                arEvEnviarACartera();
            });
        }
    });
})();
</script>
