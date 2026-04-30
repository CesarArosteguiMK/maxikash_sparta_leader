<style>
/* ══════════════════════════════════════════
   2.- EVIDENCIAS — estética alineada a 1.- Retenciones
══════════════════════════════════════════ */
.ac-header-gradient {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.ac-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.ac-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.85; color: #fff; }
.ac-header-gradient i  { color: #fff; }


/* ── Cards (misma base que 1.- Retenciones) ── */
.ac-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.6rem;
    background: #fff;
    margin-bottom: .85rem;
    overflow: hidden;
    box-shadow: none;
    transition: background-color .15s ease, border-color .15s ease;
}
.ac-card:hover { background: #fcfcfd; border-color: #dbe2ea; }

.ac-card-body {
    padding: .62rem .9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .8rem;
}
.ae-list-grid {
    flex: 1;
    display: grid;
    grid-template-columns: minmax(210px, 1fr) minmax(320px, 1.35fr) minmax(210px, 1fr);
    gap: .25rem 1rem;
}
.ae-list-cell {
    display: flex;
    flex-direction: column;
    gap: .1rem;
    min-width: 0;
}
.ae-list-cell .ac-lbl {
    color: #6b7280;
    font-size: .68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    line-height: 1.1;
}
.ae-list-cell .ac-val {
    color: #1f2937;
    font-weight: 600;
    font-size: .82rem;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ae-list-credito .ac-val { font-weight: 700; }
.ae-list-nombre .ac-val { text-transform: uppercase; }
.ae-list-ev .ac-val { font-weight: 700; }
.ae-main-meta {
    display: flex;
    flex-direction: column;
    gap: .28rem;
}
.ae-main-folio {
    font-size: .78rem;
    font-weight: 700;
    color: #b45309;
}
.ae-main-credito {
    font-size: .84rem;
    font-weight: 700;
    color: #1f2937;
}
.ae-list-muted {
    color: #9ca3af;
    font-style: italic;
    font-weight: 500;
}
.ac-detail-row {
    display: flex;
    align-items: center;
    gap: .45rem;
    margin-bottom: .08rem;
    font-size: .82rem;
}
.ac-detail-row:last-child { margin-bottom: 0; }
.ac-detail-row .ac-lbl {
    color: #6b7280;
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 128px;
}
.ac-detail-row .ac-val {
    color: #1f2937;
    font-weight: 600;
}

.ac-card-footer {
    border-top: 1px solid #eef2f7;
    padding: .55rem .85rem;
    display: flex;
    justify-content: flex-end;
    background: #fff;
    gap: .5rem;
}
.ae-list-action {
    min-width: 142px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Mismo botón en píldora que «Dictaminar» en Retenciones */
.ac-btn-dictaminar {
    background: #2563eb;
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .76rem;
    padding: .38rem .9rem;
    border-radius: 999px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s, transform .15s;
}
.ac-btn-dictaminar:hover  { opacity: .9; transform: translateY(-1px); }
.ac-btn-dictaminar:active { transform: translateY(0); }

body.dark-mode .ac-card              { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #111827; border-color: #1f2937; }
body.dark-mode .ae-list-cell .ac-lbl { color: #94a3b8; }
body.dark-mode .ae-list-cell .ac-val { color: #e2e8f0; }
body.dark-mode .ae-list-muted { color: #64748b; }
body.dark-mode .ae-main-folio { color: #fcd34d; }
body.dark-mode .ae-main-credito { color: #e2e8f0; }
body.dark-mode .aev-ev-hint { color: #94a3b8 !important; }

@media (max-width: 991.98px) {
    .ae-list-grid {
        grid-template-columns: repeat(2, minmax(220px, 1fr));
    }
}
@media (max-width: 767.98px) {
    .ac-card-body {
        flex-direction: column;
        align-items: stretch;
    }
    .ae-list-grid {
        grid-template-columns: 1fr;
    }
    .ae-list-action {
        justify-content: flex-end;
        margin-top: .2rem;
    }
}

/* ── Modal validar evidencias (patrón Mis adjudicaciones + mock validación) ── */
#modalAevValidarEvidencias .modal-header {
    background: linear-gradient(135deg, #15803d, #22c55e);
    color: #fff; padding: .75rem 1.15rem; border: none;
}
#modalAevValidarEvidencias .btn-close { filter: brightness(0) invert(1); }
/* Una línea de ayuda; sin cajas ni párrafos largos */
.aev-ev-hint {
    font-size: .72rem;
    color: #64748b;
    margin: 0 0 .45rem;
    line-height: 1.35;
}
/* Recarga de tarjetas: mantiene contenido anterior visível (evita parpadeo en blanco) */
.ae-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}
.aev-ev-progress-wrap { margin-bottom: 1rem; }
.aev-ev-progress-lbl  { font-size: .8rem; font-weight: 700; color: #14532d; }
.aev-ev-progress-bg   { height: 8px; background: #e2e8f0; border-radius: 6px; overflow: hidden; }
.aev-ev-progress-fill { height: 100%; background: linear-gradient(90deg, #16a34a, #4ade80); border-radius: 6px; transition: width .25s; }
.aev-ev-section { margin-bottom: 1rem; }
.aev-ev-hdr {
    display: flex; align-items: center; gap: .5rem;
    padding: .4rem .75rem; border-radius: .5rem .5rem 0 0;
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .45px;
}
.aev-ev-hdr-orange { background: #fff7ed; border: 1px solid #fed7aa; border-bottom: 0; color: #9a3412; }
.aev-ev-hdr-blue   { background: #eff6ff; border: 1px solid #bfdbfe; border-bottom: 0; color: #1e40af; }
.aev-ev-hdr-green  { background: #f0fdf4; border: 1px solid #bbf7d0; border-bottom: 0; color: #14532d; }
.aev-ev-hdr-purple { background: #faf5ff; border: 1px solid #e9d5ff; border-bottom: 0; color: #6b21a8; }
.aev-ev-slots-wrap { padding: .65rem; background: #f8fafc; border: 1px solid #e2e8f0; border-top: 0; border-radius: 0 0 .5rem .5rem; display: flex; gap: .5rem; flex-wrap: wrap; }
.aev-ev-slot {
    flex: 0 0 118px; width: 118px; height: 118px; background: #fff; border: 2px solid #e2e8f0; border-radius: .6rem; position: relative; overflow: hidden;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .2rem;
}
.aev-ev-slot--acept { border-color: #22c55a; background: #f0fdf4; }
.aev-ev-slot--pend  { border-color: #f59e0b; border-style: solid; }
.aev-ev-slot--rech  { border-color: #ef4444; background: #fef2f2; }
.aev-ev-slot--vacio { border: 2px dashed #cbd5e1; color: #94a3b8; font-size: .7rem; text-align: center; padding: .35rem; }
.aev-ev-slot--click { cursor: pointer; }
.aev-ev-slot--click:focus { outline: 2px solid #2563eb; outline-offset: 2px; }
.aev-ev-slot .aev-txt-lbl { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(15,23,42,.75); color: #fff; font-size: .55rem; font-weight: 700; text-transform: uppercase; padding: .2rem; text-align: center; }
.aev-badge-ok { position: absolute; bottom: 1.35rem; left: 50%; transform: translateX(-50%); background: #22c55e; color: #fff; font-size: .58rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; }
.aev-badge-na { position: absolute; bottom: 1.35rem; left: 50%; transform: translateX(-50%); background: #dc2626; color: #fff; font-size: .58rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; }
/* Visor: dentro de #modalAevValidarEvidencias (trap de foco) — sigue a pantalla con fixed; sin overflow se recorta en .modal */
#modalAevValidarEvidencias.aev-ev-vista-abierta { overflow: visible !important; }
#modalAevValidarEvidencias .aev-vista-overlay {
    position: fixed; inset: 0; z-index: 10050;
    background: rgba(15, 23, 42, 0.78);
    display: flex; align-items: center; justify-content: center; padding: 1rem;
    pointer-events: auto;
}
#modalAevValidarEvidencias .aev-vista-panel { width: 100%; max-width: 42rem; max-height: 92vh; overflow: auto; background: #fff; border-radius: 0.75rem; box-shadow: 0 20px 50px rgba(0,0,0,.35); padding: 1rem 1.1rem; position: relative; z-index: 1; }
.aev-vista-mediabox { min-height: 12rem; max-height: 55vh; background: #0f172a; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; }
.aev-vista-mediabox video, .aev-vista-mediabox img { max-width: 100%; max-height: 50vh; object-fit: contain; }
.aev-vista-mediabox iframe { width: 100%; min-height: 50vh; border: 0; background: #fff; border-radius: 0.35rem; }
#modalAevValidarEvidencias .aev-vista-panel.aev-vista-panel--pdf-only { max-width: min(56rem, 96vw); }
#modalAevValidarEvidencias .aev-vista-mediabox.aev-vista-mediabox--repuve { min-height: 58vh; max-height: 72vh; }
#modalAevValidarEvidencias .aev-vista-panel.aev-vista-panel--repuve {
    border: 1px solid #bbf7d0;
    box-shadow: 0 22px 55px rgba(22, 163, 74, 0.12);
}
#modalAevValidarEvidencias .aev-vista-panel.aev-vista-panel--repuve #aev-vista-titulo { color: #14532d; }
.aev-btn-ver-repuve {
    border: 2px solid #22c55a; color: #14532d; background: #fff; border-radius: 2rem;
    font-weight: 800; font-size: .78rem; padding: .4rem 1.1rem; line-height: 1.2;
}
.aev-btn-ver-repuve:hover, .aev-btn-ver-repuve:focus {
    background: #dcfce7; border-color: #16a34a; color: #14532d;
}
.aev-doc-zone {
    min-height: 88px; border: 2px dashed #86efac; border-radius: .5rem; background: #f0fdf4; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; text-align: center; gap: .4rem; margin-top: -1px;
}
.aev-doc-hidden { display: none !important; }
.aev-doc-zone--click { cursor: pointer; }
.aev-doc-zone--rech  { border-color: #f87171 !important; background: #fef2f2 !important; }
.aev-doc-zone--acept { border-color: #4ade80 !important; }
.aev-btn-enviar { border: 2px solid #16a34a; color: #166534; font-weight: 800; border-radius: 2rem; padding: .4rem 1.25rem; }
.aev-btn-enviar:disabled { opacity: .5; }
body.dark-mode .aev-ev-slots-wrap { background: #0f172a; border-color: #334155; }
body.dark-mode .aev-ev-slot, body.dark-mode .aev-doc-zone { background: #1e293b; border-color: #334155; }
</style>

<div class="container-fluid py-4">

    <div class="ac-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-camera-retro fa-2x"></i>
        <div>
            <h4>2.- Evidencias</h4>
            <p>Gestión de evidencias para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-pills flex-column flex-md-row mb-3 gap-md-0 gap-2 border-0" id="aeTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ae-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#aeTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="ae-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ae-tab-aprobados-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#aeTabAprobados">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Aprobados
                        <span class="badge bg-label-success ms-1" id="ae-badge-aprobados" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ae-tab-correcciones-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#aeTabCorrecciones">
                        <i class="fa-solid fa-rotate me-1"></i>Correcciones
                        <span class="badge bg-label-warning ms-1" id="ae-badge-correcciones" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="aeTabContent">
            <div class="tab-pane fade show active" id="aeTabBandeja" role="tabpanel">
                <div id="ae-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ae-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="aeTabAprobados" role="tabpanel">
                <div id="ae-loader-aprobados" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ae-lista-aprobados"></div>
            </div>

            <div class="tab-pane fade" id="aeTabCorrecciones" role="tabpanel">
                <div id="ae-loader-correcciones" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ae-lista-correcciones"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Validar evidencias (mismo criterio de slots que Mis adjudicaciones / operaciones) -->
<div class="modal fade" id="modalAevValidarEvidencias" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">
                    <i class="fa-solid fa-clipboard-check me-2"></i>Validar evidencias &mdash;
                    <span id="aev-titulo-cliente" class="fw-normal" style="font-size:.9em;"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <input type="file" id="aev-inp-repuve" class="d-none" accept="application/pdf,.pdf" aria-hidden="true" tabindex="-1">
            <div class="modal-body p-3" id="aev-body" style="position:relative;">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm" style="color:#22c55e;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="aev-btn-enviar me-auto d-none" id="aev-btn-enviar" disabled
                        title="">
                    <i class="fa-solid fa-paper-plane me-1"></i>Enviar evidencias validadas
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
    <!-- Dentro de .modal para que el foco del trap de Bootstrap incluya comentario + botones (si está en body, no deja escribir) -->
    <div id="aev-vista-overlay" class="aev-vista-overlay d-none" role="dialog" aria-modal="true" aria-labelledby="aev-vista-titulo">
        <div class="aev-vista-panel" tabindex="-1">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0" id="aev-vista-titulo" style="font-size:1rem;font-weight:700;">Evidencia</h6>
                <button type="button" class="btn btn-sm btn-light border" id="aev-vista-btn-cerrar" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="aev-vista-mediabox" id="aev-vista-mediabox"></div>
            <div id="aev-vista-solo-aceptada" class="alert alert-success py-2 px-3 mb-3 d-none" role="status">
                <p class="mb-1 fw-bold"><i class="fa-solid fa-circle-check me-1"></i>Evidencia aceptada</p>
                <p class="small mb-0 text-dark" id="aev-vista-comentario-leido"></p>
            </div>
            <div id="aev-vista-solo-rechazada" class="alert alert-danger py-2 px-3 mb-3 d-none" role="status">
                <p class="mb-1 fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i>Evidencia rechazada</p>
                <p class="small mb-0 text-dark" id="aev-vista-comentario-rechazo"></p>
            </div>
            <div id="aev-vista-panel-dictamen" class="mb-0">
                <div class="mb-3">
                    <label for="aev-vista-comentario" class="form-label small fw-bold mb-1">Comentario</label>
                    <textarea class="form-control" id="aev-vista-comentario" name="aev_vista_comentario" rows="3" placeholder="" autocomplete="off"></textarea>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" class="btn btn-success" id="aev-vista-aceptar">
                        <i class="fa-solid fa-check me-1"></i>Aceptar
                    </button>
                    <button type="button" class="btn btn-danger" id="aev-vista-rechazar">
                        <i class="fa-solid fa-xmark me-1"></i>Rechazar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (!function_exists('sparta_public_web_base')) {
    require_once dirname(__DIR__) . '/core/UploadsPaths.php';
}
$aevPublicPath = function_exists('sparta_public_web_base') ? sparta_public_web_base() : '';
?>
<script>
(function () {
    'use strict';

    /** Ruta al directorio público, ej. /sparta___SPARTA_SECRET_REDACTED__/public (definida por el servidor, no adivinada) */
    var AEV_SERVER_PUBLIC_BASE = <?php echo json_encode($aevPublicPath, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function aevInferBaseDesdePathname() {
        const p = (window.location && window.location.pathname) || '';
        const segs = p.split('/').filter(function (x) { return x.length; });
        const k = segs.indexOf('public');
        if (k >= 0) {
            return '/' + segs.slice(0, k + 1).join('/');
        }
        return '';
    }

    function aevBasePublic() {
        if (typeof AEV_SERVER_PUBLIC_BASE === 'string' && AEV_SERVER_PUBLIC_BASE.length > 0) {
            return AEV_SERVER_PUBLIC_BASE;
        }
        if (window._aevBaseCache !== undefined) {
            return window._aevBaseCache;
        }
        const path = (window.location && window.location.pathname) || '';
        let base = '';
        const i = path.indexOf('/public/');
        if (i !== -1) {
            base = path.substring(0, i + '/public'.length);
        } else {
            base = aevInferBaseDesdePathname();
        }
        window._aevBaseCache = base;
        return base;
    }
    function aevUrlForDisplay(u) {
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
        const b = aevBasePublic();
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

    (function aevInstalarProteccionUploadsFalso() {
        if (window.__aevUploadsGuardInstalado) return;
        window.__aevUploadsGuardInstalado = true;

        function fix(v) {
            return aevUrlForDisplay(v);
        }
        function fixHtml(html) {
            return String(html).replace(/\b(src|href)=([\"'])(https?:\/\/uploads\/[^\"']*|\/\/+uploads\/[^\"']*)\2/gi, function (_, attr, q, url) {
                return attr + '=' + q + fix(url) + q;
            });
        }

        const innerDesc = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML');
        if (innerDesc && innerDesc.set && innerDesc.get && innerDesc.configurable) {
            Object.defineProperty(Element.prototype, 'innerHTML', {
                configurable: true,
                enumerable: innerDesc.enumerable,
                get: function () { return innerDesc.get.call(this); },
                set: function (value) { return innerDesc.set.call(this, fixHtml(value)); }
            });
        }

        const origSetAttribute = Element.prototype.setAttribute;
        Element.prototype.setAttribute = function (name, value) {
            const n = String(name || '').toLowerCase();
            if ((n === 'src' || n === 'href') && value != null) {
                value = fix(value);
            }
            return origSetAttribute.call(this, name, value);
        };

        [HTMLImageElement.prototype, HTMLMediaElement.prototype, HTMLIFrameElement.prototype].forEach(function (proto) {
            const d = Object.getOwnPropertyDescriptor(proto, 'src');
            if (!d || !d.set || !d.get || !d.configurable) return;
            Object.defineProperty(proto, 'src', {
                configurable: true,
                enumerable: d.enumerable,
                get: function () { return d.get.call(this); },
                set: function (value) { return d.set.call(this, fix(value)); }
            });
        });
    })();

    function aevNormalizarUrlsDetalle(det) {
        if (!det || !Array.isArray(det.evidencias)) return;
        det.evidencias.forEach(function (e) {
            if (e && e.url) e.url = aevUrlForDisplay(e.url);
        });
    }
    function aevSanearDomUrls(root) {
        if (!root || !root.querySelectorAll) return;
        root.querySelectorAll('[data-aev-src]').forEach(function (el) {
            const raw = el.getAttribute('data-aev-src');
            const fixed = aevUrlForDisplay(raw || '');
            if (fixed) {
                el.setAttribute('src', fixed);
            }
        });
        root.querySelectorAll('[data-aev-href]').forEach(function (el) {
            const raw = el.getAttribute('data-aev-href');
            const fixed = aevUrlForDisplay(raw || '');
            if (fixed) {
                el.setAttribute('href', fixed);
            }
        });
        root.querySelectorAll('[src],[href]').forEach(function (el) {
            const src = el.getAttribute('src');
            if (src) {
                const nsrc = aevUrlForDisplay(src);
                if (nsrc && nsrc !== src) el.setAttribute('src', nsrc);
            }
            const href = el.getAttribute('href');
            if (href) {
                const nhref = aevUrlForDisplay(href);
                if (nhref && nhref !== href) el.setAttribute('href', nhref);
            }
        });
    }
    function aevAsegurarOverlayDentroModal() {
        const o = document.getElementById('aev-vista-overlay');
        const m = document.getElementById('modalAevValidarEvidencias');
        if (o && m && o.parentNode !== m) {
            m.appendChild(o);
        }
    }

    const AE_EV_TOTAL = 10;
    const AE_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecibidos',
            vacio: 'No hay operaciones en la bandeja en este momento.',
        },
        aprobados: {
            url:   '/AtencionClientes/obtenerAprobadosEvidencias',
            vacio: 'No hay operaciones en Aprobados (Procesando IA) en este momento.',
        },
        correcciones: {
            url:   '/AtencionClientes/obtenerCorreccionesEvidencias',
            vacio: 'No hay correcciones de evidencias en este momento.',
        },
    };

    const AE_BADGE_TAB = { bandeja: 'ae-badge-bandeja', aprobados: 'ae-badge-aprobados', correcciones: 'ae-badge-correcciones' };

    /** Tooltip en miniaturas (corto) */
    const AEV_TITLE_DICTAMEN_SLOT = 'Clic para abrir y dictaminar';

    let _aeCargada = { bandeja: false, aprobados: false, correcciones: false };

    /** Evita varios POST finalizar + recargas seguidas al guardar veredictos rápido */
    let _aeFinalizarDebounceTimer = null;

    // Definición de slots (alineada a operaciones / Mis adjudicaciones) — sin tocar esos archivos: copia local.
    const AEV_EV_SECTIONS = [
        { key: 'rec', label: 'Evidencia de recolección (final)', headerClass: 'aev-ev-hdr-orange', icon: 'fa-camera-retro', slots: [
            { key: 'rec_tacometro', label: 'Tacómetro Rec.', icon: 'fa-gauge-high' },
            { key: 'rec_serie',     label: 'No. Serie Rec.',  icon: 'fa-hashtag' },
            { key: 'rec_frontal',   label: 'Frontal Rec.',   icon: 'fa-camera' },
            { key: 'rec_lateral',   label: 'Lateral Rec.',   icon: 'fa-camera-rotate' },
        ]},
        { key: 'fis', label: 'Evidencia física (momento 1)', headerClass: 'aev-ev-hdr-blue', icon: 'fa-camera', slots: [
            { key: 'fis_vin',       label: 'Serie VIN',       icon: 'fa-barcode' },
            { key: 'fis_tacometro', label: 'Tacómetro',       icon: 'fa-gauge-high' },
            { key: 'fis_frontal',   label: 'Vista Frontal',   icon: 'fa-camera' },
            { key: 'fis_lateral',   label: 'Vista Lateral',  icon: 'fa-camera-rotate' },
            { key: 'fis_360',       label: 'Inspección 360',  icon: 'fa-video' },
        ]},
    ];
    const AEV_EV_DOCS = [
        { key: 'repuve', label: 'Momento 2: Repuve', headerClass: 'aev-ev-hdr-green', icon: 'fa-file-pdf', slotKey: 'doc_repuve', slotLabel: 'Subir Repuve' },
    ];
    const AEV_IMAGEN_KEYS = (function () {
        const a = [];
        AEV_EV_SECTIONS.forEach(s => s.slots.forEach(sl => a.push(sl.key)));
        return a;
    })();
    const AEV_PDF_KEYS     = ['doc_repuve'];
    const AEV_TOTAL_IMAGEN = 9;
    const AEV_MODAL_TOTAL  = 10;
    const AEV_KEYS_10 = AEV_IMAGEN_KEYS.concat(AEV_PDF_KEYS);

    /** detalle de sesión: veredictos v[slot]= acep|rec, comentarios c[slot] (persiste solo mientras dura el modal) */
    let _aevStore  = { det: null, idCredito: 0, v: {}, c: {} };
    let _aevVistaCtx = { slot: '', label: '', evidId: 0, soloAceptada: false, soloRechazada: false };

    function aevReiniciarStore(det, idC) {
        _aevStore.det     = det;
        _aevStore.idCredito = idC;
        _aevStore.v     = {};
        _aevStore.c     = {};
        if (det && Array.isArray(det.evidencias)) {
            det.evidencias.forEach(function (e) {
                if (!e || !e.slot) return;
                if (e.slot === 'doc_repuve') return;
                const va = parseInt(e.val_atn, 10) || 0;
                if (va === 1) _aevStore.v[e.slot] = 'acep';
                if (va === 2) _aevStore.v[e.slot] = 'rec';
                if (e.comentario_atn) _aevStore.c[e.slot] = String(e.comentario_atn);
            });
        }
    }

    function aevVeredictoEfectivo(slot) {
        return _aevStore.v[slot] || null;
    }

    /** vac | pend | acep | rec — Repuve (doc_repuve) no usa dictamen en Atención */
    function aevEstadoEvidencia(row, slot) {
        if (!row || !row.url) return 'vac';
        if (slot === 'doc_repuve') return row.url ? 'subido' : 'vac';
        const vr = aevVeredictoEfectivo(slot);
        if (vr === 'acep') return 'acep';
        if (vr === 'rec')  return 'rec';
        const va = row && row.val_atn != null && row.val_atn !== '' ? parseInt(row.val_atn, 10) : 0;
        if (va === 2) return 'rec';
        if (va === 1) return 'acep';
        return 'pend';
    }

    function aevCuentaValidadosImagen(evList) {
        const m = aevMapaPorSlot(evList);
        let n   = 0;
        AEV_IMAGEN_KEYS.forEach(function (k) {
            if (aevEstadoEvidencia(m[k], k) === 'acep') n++;
        });
        return n;
    }

    function aevCuentaValidadosPdf(evList) {
        const m = aevMapaPorSlot(evList);
        const rep = m.doc_repuve;
        return rep && rep.url ? 1 : 0;
    }

    function aevCuentaValidadosTot(evList) {
        return aevCuentaValidadosImagen(evList) + aevCuentaValidadosPdf(evList);
    }

    function aevMapaPorSlot(evList) {
        const m = {};
        (evList || []).forEach(function (e) { m[e.slot] = e; });
        return m;
    }

    function aevNueveTodasAceptadas(evList) {
        const m = aevMapaPorSlot(evList);
        return AEV_IMAGEN_KEYS.every(function (k) {
            return aevEstadoEvidencia(m[k], k) === 'acep';
        });
    }

    function aevCerrarVistaOverlay() {
        const ovl = document.getElementById('aev-vista-overlay');
        const m   = document.getElementById('modalAevValidarEvidencias');
        if (m) m.classList.remove('aev-ev-vista-abierta');
        const box = document.getElementById('aev-vista-mediabox');
        const panRoot = ovl ? ovl.querySelector('.aev-vista-panel') : null;
        if (panRoot) {
            panRoot.classList.remove('aev-vista-panel--pdf-only');
            panRoot.classList.remove('aev-vista-panel--repuve');
        }
        if (box) {
            box.classList.remove('aev-vista-mediabox--repuve');
            box.innerHTML = '';
        }
        if (ovl) ovl.classList.add('d-none');
        const ta = document.getElementById('aev-vista-comentario');
        if (ta)  ta.value = '';
        const solo = document.getElementById('aev-vista-solo-aceptada');
        const rech = document.getElementById('aev-vista-solo-rechazada');
        const panel = document.getElementById('aev-vista-panel-dictamen');
        const cleido = document.getElementById('aev-vista-comentario-leido');
        const cleRech = document.getElementById('aev-vista-comentario-rechazo');
        if (solo) solo.classList.add('d-none');
        if (rech) rech.classList.add('d-none');
        if (panel) panel.classList.remove('d-none');
        if (cleido) { cleido.textContent = ''; cleido.style.display = ''; }
        if (cleRech) { cleRech.textContent = ''; cleRech.style.display = ''; }
        _aevVistaCtx.slot   = '';
        _aevVistaCtx.label  = '';
        _aevVistaCtx.evidId = 0;
        _aevVistaCtx.soloAceptada = false;
        _aevVistaCtx.soloRechazada = false;
    }

    function aevAbrirVistaEvidencia(slot, label) {
        if (!_aevStore.det) return;
        if (slot === 'doc_repuve') return;
        const ovlPre = document.getElementById('aev-vista-overlay');
        const panPre = ovlPre ? ovlPre.querySelector('.aev-vista-panel') : null;
        const boxPre = document.getElementById('aev-vista-mediabox');
        if (panPre) {
            panPre.classList.remove('aev-vista-panel--pdf-only');
            panPre.classList.remove('aev-vista-panel--repuve');
        }
        if (boxPre) boxPre.classList.remove('aev-vista-mediabox--repuve');
        const evMap = aevMapaPorSlot(_aevStore.det.evidencias);
        const row   = evMap[slot];
        if (!row || !row.url) return;
        const st = aevEstadoEvidencia(row, slot);
        const soloAceptada = (st === 'acep');
        const soloRechazada = (st === 'rec');
        const modoSoloLectura = soloAceptada || soloRechazada;
        _aevVistaCtx.soloAceptada = soloAceptada;
        _aevVistaCtx.soloRechazada = soloRechazada;
        aevAsegurarOverlayDentroModal();
        const modalAev = document.getElementById('modalAevValidarEvidencias');
        if (modalAev) modalAev.classList.add('aev-ev-vista-abierta');
        const urlRaw = aevUrlForDisplay(String(row.url));
        const ovl  = document.getElementById('aev-vista-overlay');
        const tEl  = document.getElementById('aev-vista-titulo');
        const box  = document.getElementById('aev-vista-mediabox');
        const cmt  = document.getElementById('aev-vista-comentario');
        const soloEl = document.getElementById('aev-vista-solo-aceptada');
        const rechEl = document.getElementById('aev-vista-solo-rechazada');
        const panelDict = document.getElementById('aev-vista-panel-dictamen');
        const cleido = document.getElementById('aev-vista-comentario-leido');
        const cleRech = document.getElementById('aev-vista-comentario-rechazo');
        if (!ovl || !box) return;
        if (tEl) {
            if (soloAceptada) {
                tEl.textContent = (label || slot) + ' — validada';
            } else if (soloRechazada) {
                tEl.textContent = (label || slot) + ' — rechazada';
            } else {
                tEl.textContent = label || slot;
            }
        }
        if (soloEl) soloEl.classList.toggle('d-none', !soloAceptada);
        if (rechEl) rechEl.classList.toggle('d-none', !soloRechazada);
        if (panelDict) panelDict.classList.toggle('d-none', modoSoloLectura);
        if (soloAceptada) {
            const txtCom = (row.comentario_atn || _aevStore.c[slot] || '').trim();
            if (cleido) {
                cleido.textContent = txtCom ? ('Comentario: ' + txtCom) : '';
                cleido.style.display = txtCom ? '' : 'none';
            }
            if (cmt) cmt.value = '';
        } else if (soloRechazada) {
            const txtRech = (row.comentario_atn || _aevStore.c[slot] || '').trim();
            if (cleRech) {
                cleRech.textContent = txtRech ? ('Comentario: ' + txtRech) : 'Sin comentario registrado.';
                cleRech.classList.toggle('text-muted', !txtRech);
            }
            if (cmt) cmt.value = '';
        } else {
            if (cmt) cmt.value = _aevStore.c[slot] || '';
            if (cleido) { cleido.textContent = ''; cleido.style.display = ''; }
            if (cleRech) { cleRech.textContent = ''; cleRech.style.display = ''; }
        }
        const urlE = aeEsc(urlRaw);
        const es360 = (slot === 'fis_360');
        const esVideo = (row.tipo && String(row.tipo).toLowerCase().indexOf('video') !== -1) || es360;
        const esPdf = (AEV_PDF_KEYS.indexOf(slot) !== -1)
            || (row.tipo && String(row.tipo).toLowerCase().indexOf('pdf') !== -1)
            || /\.pdf(\?|#|$)/i.test(urlRaw);
        if (esPdf) {
            box.innerHTML = '<iframe data-aev-src="' + urlE + '" title="Documento" class="aev-iframe-pdf"></iframe>';
        } else if (esVideo) {
            box.innerHTML = '<video controls playsinline data-aev-src="' + urlE + '"></video>';
        } else {
            box.innerHTML = '<img data-aev-src="' + urlE + '" alt="Evidencia">';
        }
        aevSanearDomUrls(box);
        _aevVistaCtx.slot  = slot;
        _aevVistaCtx.label = label || '';
        ovl.classList.remove('d-none');
        _aevVistaCtx.evidId = row && row.id ? parseInt(row.id, 10) : 0;
        if (!modoSoloLectura) {
            setTimeout(function () {
                const tx = document.getElementById('aev-vista-comentario');
                if (tx) tx.focus();
            }, 10);
        }
    }

    /** Repuve: solo lectura en el mismo overlay (sin dictaminar desde aquí). */
    function aevAbrirVistaRepuvePdf() {
        if (!_aevStore.det) return;
        const evMap = aevMapaPorSlot(_aevStore.det.evidencias);
        const row   = evMap.doc_repuve;
        if (!row || !row.url) return;
        const urlRaw = aevUrlForDisplay(String(row.url));
        aevAsegurarOverlayDentroModal();
        const modalAev = document.getElementById('modalAevValidarEvidencias');
        if (modalAev) modalAev.classList.add('aev-ev-vista-abierta');
        const ovl  = document.getElementById('aev-vista-overlay');
        const tEl  = document.getElementById('aev-vista-titulo');
        const box  = document.getElementById('aev-vista-mediabox');
        const panelDict = document.getElementById('aev-vista-panel-dictamen');
        const soloEl = document.getElementById('aev-vista-solo-aceptada');
        const rechEl = document.getElementById('aev-vista-solo-rechazada');
        const panRoot = ovl ? ovl.querySelector('.aev-vista-panel') : null;
        if (!ovl || !box) return;
        if (tEl) tEl.textContent = 'Momento 2: REPUVE — documento PDF';
        if (soloEl) soloEl.classList.add('d-none');
        if (rechEl) rechEl.classList.add('d-none');
        if (panelDict) panelDict.classList.add('d-none');
        if (panRoot) {
            panRoot.classList.add('aev-vista-panel--pdf-only');
            panRoot.classList.add('aev-vista-panel--repuve');
        }
        box.classList.add('aev-vista-mediabox--repuve');
        const urlE = aeEsc(urlRaw);
        box.innerHTML = '<iframe data-aev-src="' + urlE + '" title="Repuve (PDF)" class="aev-iframe-pdf"></iframe>';
        aevSanearDomUrls(box);
        _aevVistaCtx.slot  = 'doc_repuve';
        _aevVistaCtx.label = 'Repuve';
        _aevVistaCtx.evidId = row && row.id ? parseInt(row.id, 10) : 0;
        _aevVistaCtx.soloAceptada = false;
        _aevVistaCtx.soloRechazada = false;
        ovl.classList.remove('d-none');
    }

    function aevRefrescarCuerpoModal() {
        if (!_aevStore.det) return;
        const body = document.getElementById('aev-body');
        if (body) {
            requestAnimationFrame(function () {
                body.innerHTML = aevRenderCuerpoModalValidar(_aevStore.det);
                aevSanearDomUrls(body);
                aevSincroBtnEnviar();
            });
            return;
        }
        aevSincroBtnEnviar();
    }

    function aevRenderSeccionValidar(sec, map) {
        let inner = '';
        sec.slots.forEach(function (sl) {
            inner += aevRenderCelda(sl, map[sl.key]);
        });
        return `
        <div class="aev-ev-section">
            <div class="aev-ev-hdr ${sec.headerClass}"><i class="fa-solid ${sec.icon}"></i> ${aeEsc(sec.label)}</div>
            <div class="aev-ev-slots-wrap">${inner}</div>
        </div>`;
    }

    function aevRenderCelda(sl, row) {
        const st  = aevEstadoEvidencia(row, sl.key);
        const has = row && row.url;
        if (!has) {
            return `
            <div class="aev-ev-slot aev-ev-slot--vacio">
                <i class="fa-solid ${sl.icon} mb-1 opacity-50" style="font-size:1.1rem;"></i>
                <span style="line-height:1.15;">${aeEsc(sl.label)}</span>
            </div>`;
        }
        const uEsc = aeEsc(aevUrlForDisplay(row.url));
        const es360 = (sl.key === 'fis_360');
        const esVideo = (row.tipo && String(row.tipo).toLowerCase().indexOf('video') !== -1) || es360;
        const media = esVideo
            ? '<video class="aev-thumb" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" data-aev-src="' + uEsc + '" muted playsinline></video><div class="aev-aev-mute-play" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.2);pointer-events:none;"><i class="fa-solid fa-play" style="color:#fff;font-size:1.4rem;"></i></div>'
            : '<img class="aev-thumb" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" data-aev-src="' + uEsc + '" alt="">';
        const dataAttr = ' data-aev-ver="' + aeEsc(sl.key) + '" data-aev-lbl="' + aeEsc(sl.label) + '" ';
        const titleAttr = ' title="' + aeEsc(AEV_TITLE_DICTAMEN_SLOT) + '" ';
        if (st === 'acep') {
            return `
            <div class="aev-ev-slot aev-ev-slot--acept aev-ev-slot--click" style="position:relative;"` + dataAttr + titleAttr + `>
                ${media}
                <span class="aev-txt-lbl">${aeEsc(sl.label)}</span>
                <span class="aev-badge-ok">ACEPTADA</span>
            </div>`;
        }
        if (st === 'rec') {
            return `
            <div class="aev-ev-slot aev-ev-slot--rech aev-ev-slot--click" style="position:relative;"` + dataAttr + titleAttr + `>
                ${media}
                <span class="aev-txt-lbl">${aeEsc(sl.label)}</span>
                <span class="aev-badge-na">RECHAZADA</span>
            </div>`;
        }
        return `
        <div class="aev-ev-slot aev-ev-slot--pend aev-ev-slot--click" style="position:relative;"` + dataAttr + titleAttr + `>
            ${media}
            <span class="aev-txt-lbl">${aeEsc(sl.label)}</span>
        </div>`;
    }

    function aevRenderBloqueDoc(doc, map) {
        const ev  = map[doc.slotKey];
        if (!ev || !ev.url) {
            return `
            <div class="aev-doc-zone" data-aev-subir="doc_repuve" role="button" tabindex="0" style="cursor:pointer;" title="Solo PDF">
                <i class="fa-solid ${doc.icon} fa-2x text-success opacity-50"></i>
                <div class="fw-bold small text-success">${aeEsc(doc.slotLabel)}</div>
                <span class="small text-muted">Toca para subir (PDF)</span>
            </div>`;
        }
        const tit  = 'Repuve';
        return `
        <div class="aev-doc-zone aev-doc-zone--acept" data-aev-subir="doc_repuve" role="button" tabindex="0" style="border-style:solid;border-color:#22c55a;background:#f0fdf4;cursor:pointer;">
            <i class="fa-solid fa-file-pdf fa-2x text-success"></i>
            <div class="fw-bold small mt-1" style="color:#14532d;">${aeEsc(tit)}</div>
            <div class="small text-muted">PDF en expediente. Toca la zona (no el botón) para reemplazar.</div>
            <button type="button" class="aev-btn-ver-repuve mt-2" data-aev-ver-repuve="1">
                <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Ver PDF aquí
            </button>
            <span class="aev-badge-ok mt-1" style="position:static;transform:none;display:inline-block;background:#15803d;">En expediente</span>
        </div>`;
    }

    function aevRenderCuerpoModalValidar(det) {
        const evl = det.evidencias || [];
        const m   = aevMapaPorSlot(evl);
        const vi   = aevCuentaValidadosImagen(evl);
        const vpdf = aevCuentaValidadosPdf(evl);
        const vall = aevCuentaValidadosTot(evl);
        const pct9 = AEV_TOTAL_IMAGEN ? Math.round((vi / AEV_TOTAL_IMAGEN) * 100) : 0;
        const mostrarDoc = aevNueveTodasAceptadas(evl);

        let html = '';
        html += '<p class="aev-ev-hint" role="note"><i class="fa-solid fa-hand-pointer me-1" style="opacity:.65;" aria-hidden="true"></i>Clic en cada evidencia para aceptar o rechazar.</p>';

        html += '<div class="aev-ev-progress-wrap">';
        html += '<div class="d-flex justify-content-between align-items-end mb-1 flex-wrap gap-1">';
        html += '<span style="font-size:.75rem;font-weight:700;color:#0f172a;">Progreso de evidencias <span class="text-success">validadas</span> (fotos / video etapas 1–2)</span>';
        html += '<span class="aev-ev-progress-lbl" id="aev-lbl-9">' + vi + ' / ' + AEV_TOTAL_IMAGEN + '</span>';
        html += '</div><div class="aev-ev-progress-bg"><div class="aev-ev-progress-fill" id="aev-fill-9" style="width:' + pct9 + '%;"></div></div>';
        html += '<p class="small text-muted mt-1 mb-0">PDF Repuve en expediente: <strong>' + vpdf + ' / 1</strong> · <strong>Avance en pantalla: ' + vall + ' / ' + AEV_MODAL_TOTAL + '</strong></p>';
        html += '</div>';

        AEV_EV_SECTIONS.forEach(function (sec) { html += aevRenderSeccionValidar(sec, m); });

        if (mostrarDoc) {
            AEV_EV_DOCS.forEach(function (doc) {
                html += '<div class="aev-ev-section">';
                html += '<div class="aev-ev-hdr ' + doc.headerClass + '"><i class="fa-solid ' + doc.icon + '"></i> ' + aeEsc(doc.label) + '</div>';
                html += aevRenderBloqueDoc(doc, m);
                html += '</div>';
            });
        }
        return html;
    }

    /**
     * Enviar: visible y habilitado con las 9 aceptadas + Repuve (PDF) cargado en el sistema.
     */
    function aevSincroBtnEnviar() {
        const btn = document.getElementById('aev-btn-enviar');
        if (!btn) return;
        btn.classList.add('d-none');
        btn.disabled = true;
        btn.removeAttribute('title');
        if (!_aevStore.det) return;
        if (!aevNueveTodasAceptadas(_aevStore.det.evidencias)) return;
        const m = aevMapaPorSlot(_aevStore.det.evidencias);
        const rep = m.doc_repuve;
        if (!rep || !rep.url) return;
        btn.classList.remove('d-none');
        btn.disabled = false;
        btn.removeAttribute('title');
    }

    function aevSetRowValAtnEnDetalle(slot, valAtn, comentario) {
        if (!_aevStore.det || !Array.isArray(_aevStore.det.evidencias)) return;
        _aevStore.det.evidencias.forEach(function (row) {
            if (row && row.slot === slot) {
                row.val_atn = valAtn;
                row.comentario_atn = comentario;
            }
        });
    }

    function aevAplicarVeredictoDesdeVista(ver) {
        if (_aevVistaCtx.soloAceptada || _aevVistaCtx.soloRechazada) return;
        const s = _aevVistaCtx.slot;
        if (!s || s === 'doc_repuve') return;
        if (ver === 'acep') { _aevStore.v[s] = 'acep'; }
        else if (ver === 'rec') { _aevStore.v[s] = 'rec'; }
        const cmt = document.getElementById('aev-vista-comentario');
        const coment = cmt ? (cmt.value || '').trim() : '';
        if (cmt) _aevStore.c[s] = coment;
        const opId  = _aevStore.det && _aevStore.det.id ? parseInt(_aevStore.det.id, 10) : 0;
        const evidId = _aevVistaCtx.evidId;
        aevCerrarVistaOverlay();
        aevRefrescarCuerpoModal();
        if (opId > 0 && evidId > 0) {
            const val = ver === 'acep' ? 1 : 2;
            fetch('/MotosAdjudicadas/guardarVeredictoEvidenciaAtn', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify({
                    id_operacion:  opId,
                    id_evidencia:  evidId,
                    val_atn:       val,
                    comentario:    coment
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        aevSetRowValAtnEnDetalle(s, val, coment);
                        // Recalcula estatus tras cada veredicto para evitar carreras al cerrar rápido el modal.
                        aevRecalcularDespuesDeVeredicto(opId);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: data.message || 'Intenta de nuevo o revisa el servidor (¿migración adj_evidencia?)' });
                    } else {
                        window.alert((data && data.message) || 'No se pudo guardar el veredicto.');
                    }
                })
                .catch(function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo guardar el veredicto.' });
                    }
                });
        }
    }

    function aevRecargarPestanaEvidenciasActiva() {
        const b0 = document.getElementById('ae-tab-bandeja-btn');
        const b1 = document.getElementById('ae-tab-aprobados-btn');
        const b2 = document.getElementById('ae-tab-correcciones-btn');
        if (b0 && b0.classList.contains('active')) { aeCargarSeccion('bandeja', true); return; }
        if (b1 && b1.classList.contains('active')) { aeCargarSeccion('aprobados', true); return; }
        if (b2 && b2.classList.contains('active')) { aeCargarSeccion('correcciones', true); }
    }

    /**
     * Tras «Enviar evidencias validadas»: refresca datos sin cambiar la pestaña activa.
     * Evita brincos automáticos de Bandeja a Aprobados.
     */
    function aePostEnviarEvidenciasValidadas() {
        _aeCargada = { bandeja: false, aprobados: false, correcciones: false };
        aeCargarConteosPestanas();
        aevRecargarPestanaEvidenciasActiva();
    }

    function aevAplicarRespuestaFinalizar(data) {
        if (!data || !data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No se actualizó la etapa',
                    text: (data && data.message) ? String(data.message) : 'Respuesta inválida del servidor.'
                });
            }
            return;
        }
        _aeCargada = { bandeja: false, aprobados: false, correcciones: false };
        aeCargarConteosPestanas();
        aevRecargarPestanaEvidenciasActiva();
    }

    function aevOnModalCerrarValidar() {
        if (_aeFinalizarDebounceTimer) {
            clearTimeout(_aeFinalizarDebounceTimer);
            _aeFinalizarDebounceTimer = null;
        }
        const d = _aevStore.det;
        if (!d || !d.id) { return; }
        const opId = parseInt(d.id, 10);
        if (opId <= 0) { return; }
        fetch('/MotosAdjudicadas/finalizarCierreValidacionEvidenciaAtn', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ id_operacion: opId }),
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(data => { aevAplicarRespuestaFinalizar(data); });
    }

    function aevRecalcularDespuesDeVeredicto(opId) {
        const id = parseInt(opId, 10);
        if (id <= 0) return;
        if (_aeFinalizarDebounceTimer) {
            clearTimeout(_aeFinalizarDebounceTimer);
        }
        _aeFinalizarDebounceTimer = setTimeout(function () {
            _aeFinalizarDebounceTimer = null;
            fetch('/MotosAdjudicadas/finalizarCierreValidacionEvidenciaAtn', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify({ id_operacion: id }),
                credentials: 'same-origin'
            })
                .then(r => r.json())
                .then(data => { aevAplicarRespuestaFinalizar(data); });
        }, 350);
    }

    function aeEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function aeSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${aeEsc(msg)}</span>
        </div>`;
    }

    /**
     * Misma estructura visual que la tarjeta "Entrantes" en 1.- Retenciones
     * (encabezado azul, # crédito + nombre, filas etiqueta/valor, botón al pie).
     */
    function aeRenderCard(item) {
        const ev = parseInt(item.evidencias_count, 10) || 0;
        const g  = item.gestor_nombre ? aeEsc(item.gestor_nombre) : '<span class="ae-list-muted">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? aeEsc(item.fecha_asignacion)
            : '<span class="ae-list-muted">—</span>';
        const nombreCliente = item.nombre_cliente
            ? aeEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? aeEsc(item.folio) : '—';

        return `
        <div class="ac-card">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${aeEsc(String(item.id_credito))}</span>
                    </div>
                    <div class="ae-list-cell ae-list-gestor">
                        <span class="ac-lbl">Gestor a cargo</span>
                        <span class="ac-val">${g}</span>
                    </div>
                    <div class="ae-list-cell ae-list-asig">
                        <span class="ac-lbl">Asignación realizada</span>
                        <span class="ac-val">${fa}</span>
                    </div>
                    <div class="ae-list-cell ae-list-nombre">
                        <span class="ac-lbl">Nombre</span>
                        <span class="ac-val">${nombreCliente}</span>
                    </div>
                    <div class="ae-list-cell ae-list-ev">
                        <span class="ac-lbl">Evidencias cargadas</span>
                        <span class="ac-val">${ev} / ${AE_EV_TOTAL}</span>
                    </div>
                </div>
                <div class="ae-list-action">
                <button type="button" class="ac-btn-dictaminar"
                        onclick="aevValidarAbrir(${+item.id_credito})">
                    <i class="fa-solid fa-clipboard-check me-1"></i>Validar evidencias
                </button>
                </div>
            </div>
        </div>`;
    }

    window.aevValidarAbrir = function (idCredito) {
        const id = parseInt(idCredito, 10);
        if (!id) return;
        aevCerrarVistaOverlay();
        aevReiniciarStore(null, id);
        aevSincroBtnEnviar();
        const tit = document.getElementById('aev-titulo-cliente');
        const body = document.getElementById('aev-body');
        if (tit) tit.textContent = 'Crédito #' + id;
        if (body) {
            body.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm" style="color:#22c55e;"></div></div>';
        }
        const mEl = document.getElementById('modalAevValidarEvidencias');
        if (mEl && window.bootstrap) {
            (new bootstrap.Modal(mEl)).show();
        }

        fetch('/MotosAdjudicadas/obtenerEvidenciasCredito', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ id_credito: id, nombre_cliente: '' }),
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    body.innerHTML = '<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>' + aeEsc(data.message || 'Error al cargar') + '</div>';
                    aevReiniciarStore(null, id);
                    aevSincroBtnEnviar();
                    return;
                }
                const det = data.detalle;
                aevNormalizarUrlsDetalle(det);
                aevReiniciarStore(det, id);
                const nom = (det && det.nombre_cliente) ? String(det.nombre_cliente).trim() : '';
                if (tit) {
                    tit.textContent = nom || (det && det.folio ? 'Folio ' + det.folio : 'Crédito');
                }
                if (body) {
                    body.innerHTML = aevRenderCuerpoModalValidar(det);
                    aevSanearDomUrls(body);
                }
                aevSincroBtnEnviar();
            })
            .catch(function () {
                aevReiniciarStore(null, id);
                aevSincroBtnEnviar();
                if (body) {
                    body.innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-wifi me-1"></i>Error de red al cargar el detalle de evidencias.</div>';
                }
            });
    };

    function aeSetBadgeTab(key, n) {
        const el = document.getElementById(AE_BADGE_TAB[key]);
        if (!el) return;
        const num = Math.max(0, parseInt(n, 10) || 0);
        if (num > 0) {
            el.textContent = String(num);
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    /** Misma regla que cada lista; muestra los tres totales sin abrir cada pestaña. */
    function aeCargarConteosPestanas() {
        return fetch('/AtencionClientes/obtenerConteosEvidencias', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.conteos) return;
                const c = data.conteos;
                aeSetBadgeTab('bandeja', c.bandeja);
                aeSetBadgeTab('aprobados', c.aprobados);
                aeSetBadgeTab('correcciones', c.correcciones);
            })
            .catch(function () { /* silencioso: los badges se actualizan al entrar a cada pestaña */ });
    }

    function aeCargarSeccion(key, forzar) {
        const cfg = AE_CONFIG[key];
        if (!cfg) return Promise.resolve();

        const idSuffix  = (key === 'bandeja' ? 'bandeja' : key);
        const loaderId  = 'ae-loader-' + idSuffix;
        const listaId   = 'ae-lista-'  + idSuffix;

        if (!forzar && _aeCargada[key]) {
            return Promise.resolve();
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return Promise.resolve();

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('ae-lista-updating');
        }

        return fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    if (key === 'correcciones') {
                        lista.innerHTML    = aeSinDatos(AE_CONFIG.correcciones.vacio);
                        aeSetBadgeTab(key, 0);
                        _aeCargada[key]   = true;
                        return;
                    }
                    throw new Error(data.message || 'Error al cargar');
                }

                const n = data.datos.length;
                aeSetBadgeTab(key, n);
                _aeCargada[key] = true;

                if (n === 0) {
                    lista.innerHTML = aeSinDatos(cfg.vacio);
                } else {
                    lista.innerHTML = data.datos.map(d => aeRenderCard(d)).join('');
                }
            })
            .catch(err => {
                if (key === 'correcciones') {
                    lista.innerHTML  = aeSinDatos(AE_CONFIG.correcciones.vacio);
                    _aeCargada[key]  = true;
                } else {
                    lista.innerHTML = `<div class="alert alert-danger">${aeEsc(err.message)}</div>`;
                }
                aeSetBadgeTab(key, 0);
            })
            .finally(() => {
                loader.style.display = 'none';
                lista.classList.remove('ae-lista-updating');
            });
    }

    /** Carga inicial paralela (badges + bandeja) con el mismo Swal que 1.- Retenciones. */
    function aeCargarVistaInicialConSpinner() {
        const hasSwal = typeof Swal !== 'undefined';
        if (hasSwal) {
            Swal.fire({
                title: 'Cargando Evidencias…',
                html: '<span style="font-size:.875rem;color:#64748b;">Obteniendo todas las pestañas</span>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); },
            });
        }
        Promise.all([
            aeCargarConteosPestanas(),
            aeCargarSeccion('bandeja', true),
        ]).finally(function () {
            if (hasSwal) Swal.close();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        aevAsegurarOverlayDentroModal();
        aeCargarVistaInicialConSpinner();

        document.getElementById('ae-tab-bandeja-btn').addEventListener('shown.bs.tab', function () {
            aeCargarSeccion('bandeja', false);
        });
        document.getElementById('ae-tab-aprobados-btn').addEventListener('shown.bs.tab', function () {
            aeCargarSeccion('aprobados', false);
        });
        document.getElementById('ae-tab-correcciones-btn').addEventListener('shown.bs.tab', function () {
            aeCargarSeccion('correcciones', false);
        });

        const aevBody = document.getElementById('aev-body');
        if (aevBody) {
            aevBody.addEventListener('click', function (ev) {
                const verRep = ev.target.closest('[data-aev-ver-repuve]');
                if (verRep) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    aevAbrirVistaRepuvePdf();
                    return;
                }
                const verEl = ev.target.closest('[data-aev-ver]');
                if (verEl) {
                    const slot = verEl.getAttribute('data-aev-ver');
                    const lbl  = verEl.getAttribute('data-aev-lbl') || slot;
                    aevAbrirVistaEvidencia(slot, lbl);
                    return;
                }
                const sub = ev.target.closest('[data-aev-subir]');
                if (sub) {
                    const sk = sub.getAttribute('data-aev-subir');
                    if (sk === 'doc_repuve' && _aevStore.det && aevNueveTodasAceptadas(_aevStore.det.evidencias)) {
                        const inp = document.getElementById('aev-inp-repuve');
                        if (inp) { inp.value = ''; inp.click(); }
                    }
                }
            });
        }
        const aevInpRepuve = document.getElementById('aev-inp-repuve');
        if (aevInpRepuve) {
            aevInpRepuve.addEventListener('change', function (ev) {
                const f = ev.target && ev.target.files && ev.target.files[0];
                if (ev.target) { ev.target.value = ''; }
                if (!f || !_aevStore.det || !_aevStore.det.id) return;
                if (f.type && f.type !== 'application/pdf' && !/\.pdf$/i.test(f.name)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', text: 'Repuve: solo se acepta PDF.' });
                    } else { window.alert('Solo PDF.'); }
                    return;
                }
                const fd = new FormData();
                fd.append('id_operacion', String(_aevStore.det.id));
                fd.append('slot', 'doc_repuve');
                fd.append('archivo', f, f.name);
                const body = document.getElementById('aev-body');
                if (body) { body.insertAdjacentHTML('afterbegin', '<div class="aev-load-overlay" id="aev-subida-overlay" style="position:absolute;inset:0;background:rgba(255,255,255,.6);z-index:5;display:flex;align-items:center;justify-content:center;font-size:.9rem;">Subiendo…</div>'); }
                fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        const o = document.getElementById('aev-subida-overlay');
                        if (o) o.remove();
                        if (!data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', text: data.message || 'Error al subir' });
                            } else { window.alert(data.message || 'Error al subir'); }
                            return;
                        }
                        return fetch('/MotosAdjudicadas/obtenerDetalle/' + parseInt(_aevStore.det.id, 10) + '?incluir_todas=1', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                            .then(r2 => r2.json());
                    })
                    .then(function (j) {
                        if (!j || !j.success || !j.detalle) return;
                        aevNormalizarUrlsDetalle(j.detalle);
                        aevReiniciarStore(j.detalle, _aevStore.idCredito);
                        const body2 = document.getElementById('aev-body');
                        if (body2) {
                            body2.innerHTML = aevRenderCuerpoModalValidar(_aevStore.det);
                            aevSanearDomUrls(body2);
                        }
                        aevSincroBtnEnviar();
                    })
                    .catch(function () {
                        const o2 = document.getElementById('aev-subida-overlay');
                        if (o2) o2.remove();
                    });
            });
        }
        const ovl = document.getElementById('aev-vista-overlay');
        if (ovl) {
            ovl.addEventListener('click', function (ev) {
                if (ev.target === ovl) aevCerrarVistaOverlay();
            });
        }
        const btnC = document.getElementById('aev-vista-btn-cerrar');
        if (btnC) btnC.addEventListener('click', function () { aevCerrarVistaOverlay(); });
        const btnA = document.getElementById('aev-vista-aceptar');
        if (btnA) btnA.addEventListener('click', function () { aevAplicarVeredictoDesdeVista('acep'); });
        const btnR = document.getElementById('aev-vista-rechazar');
        if (btnR) btnR.addEventListener('click', function () { aevAplicarVeredictoDesdeVista('rec'); });
        document.addEventListener('keydown', function (ev) {
            if (ev.key !== 'Escape') return;
            const o = document.getElementById('aev-vista-overlay');
            if (o && !o.classList.contains('d-none')) aevCerrarVistaOverlay();
        });

        const mAev = document.getElementById('modalAevValidarEvidencias');
        if (mAev) {
            mAev.addEventListener('hidden.bs.modal', function () {
                aevCerrarVistaOverlay();
                aevOnModalCerrarValidar();
            });
        }

        const btnEnviar = document.getElementById('aev-btn-enviar');
        if (btnEnviar) {
            btnEnviar.addEventListener('click', function () {
                if (!_aevStore.det || !_aevStore.det.id) return;
                const opId = parseInt(_aevStore.det.id, 10);
                if (opId <= 0) return;
                btnEnviar.disabled = true;
                fetch('/MotosAdjudicadas/enviarEvidenciasValidadasAtencion', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body:    JSON.stringify({ id_operacion: opId }),
                    credentials: 'same-origin'
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        btnEnviar.disabled = false;
                        if (!data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: 'No se pudo enviar', text: data.message || 'Revisa que todo esté validado.' });
                            } else {
                                window.alert(data.message || 'No se pudo enviar.');
                            }
                            return;
                        }
                        aePostEnviarEvidenciasValidadas();
                        const mEl = document.getElementById('modalAevValidarEvidencias');
                        if (mEl && window.bootstrap) {
                            const inst = bootstrap.Modal.getInstance(mEl);
                            if (inst) inst.hide();
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Enviado',
                                text: 'Evidencias enviadas correctamente.',
                                confirmButtonColor: '#16a34a'
                            });
                        }
                    })
                    .catch(function () {
                        btnEnviar.disabled = false;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo enviar.' });
                        }
                    });
            });
        }
    });
})();
</script>
