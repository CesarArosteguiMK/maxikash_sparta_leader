<style>
    /* ===================================================================
       OPERACIONES — Paleta índigo #6366f1
       =================================================================== */
    :root {
        --ops-green:        #6366f1;
        --ops-green-dark:   #4f46e5;
        --ops-green-light:  #eef2ff;
        --ops-green-border: #a5b4fc;
        --ops-green-text:   #3730a3;
    }

    /* ── Kanban layout ─────────────────────────────────────────────── */
    .ops-pipeline-wrapper {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        min-height: calc(100vh - 260px);
    }

    .ops-pipeline-wrapper::-webkit-scrollbar { height: 7px; }
    .ops-pipeline-wrapper::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .ops-pipeline-wrapper::-webkit-scrollbar-thumb { background: var(--ops-green-border); border-radius: 10px; }

    .ops-column {
        min-width: 270px;
        max-width: 270px;
        background: #f8fafc;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }

    .ops-column-header {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem 0.75rem 0 0;
        border-bottom: 2px solid var(--ops-green);
        background: var(--ops-green-light);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .ops-column-header .ops-col-title {
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--ops-green-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ops-column-header .ops-col-count {
        background: var(--ops-green);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 0.1rem 0.5rem;
        flex-shrink: 0;
    }

    .ops-cards-body {
        padding: 0.625rem;
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        overflow-y: auto;
        flex: 1;
        max-height: calc(100vh - 320px);
    }

    .ops-cards-body::-webkit-scrollbar { width: 5px; }
    .ops-cards-body::-webkit-scrollbar-track { background: transparent; }
    .ops-cards-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* ── Kanban card ───────────────────────────────────────────────── */
    .ops-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.625rem;
        padding: 0.75rem;
        cursor: pointer;
        transition: box-shadow 0.18s, transform 0.18s;
        border-left: 4px solid var(--ops-green);
        animation: opsCardIn 0.25s ease-out;
    }

    @keyframes opsCardIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .ops-card:hover {
        box-shadow: 0 4px 14px rgba(99,102,241,0.18);
        transform: translateY(-2px);
    }

    /* Cancelado — borde rojo */
    .ops-card--cancelado {
        border-color: #fca5a5 !important;
        box-shadow: 0 0 0 1px rgba(220,38,38,.15) !important;
    }
    .ops-card--cancelado:hover {
        box-shadow: 0 4px 14px rgba(220,38,38,.22) !important;
    }

    /* En tránsito — borde azul */
    .ops-card--en-transito {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 1px rgba(37,99,235,.12) !important;
    }
    .ops-card--en-transito:hover {
        box-shadow: 0 4px 14px rgba(37,99,235,.2) !important;
    }

    .ops-card-folio {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--ops-green-text);
        background: var(--ops-green-light);
        border: 1px solid var(--ops-green-border);
        border-radius: 999px;
        padding: 0.1rem 0.55rem;
        display: inline-block;
        margin-bottom: 0.35rem;
    }

    .ops-card-nombre {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.3;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ops-card-credito {
        font-size: 0.72rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .ops-aging-badge {
        font-size: 0.65rem;
        font-weight: 600;
        border-radius: 999px;
        padding: 0.1rem 0.5rem;
        border: 1px solid transparent;
    }
    .ops-aging-green  { background: #e0e7ff; color: #3730a3; border-color: #a5b4fc; }
    .ops-aging-yellow { background: #fef9c3; color: #713f12; border-color: #fcd34d; }
    .ops-aging-red    { background: #fee2e2; color: #7f1d1d; border-color: #fca5a5; }

    .ops-area-badge {
        font-size: 0.65rem;
        font-weight: 600;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 0.1rem 0.5rem;
        color: #475569;
    }

    /* Checklist mini */
    .ops-checklist {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-top: 0.4rem;
        border-top: 1px dashed #e2e8f0;
        padding-top: 0.4rem;
    }

    .ops-chk-item {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.68rem;
        color: #64748b;
    }

    .ops-chk-item.done { color: var(--ops-green); }
    .ops-chk-item i { font-size: 0.65rem; flex-shrink: 0; width: 0.7rem; text-align: center; }

    /* ── Buttons ───────────────────────────────────────────────────── */
    .btn-ops-green {
        background: var(--ops-green);
        border: none;
        color: #fff;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .btn-ops-green:hover, .btn-ops-green:focus {
        background: var(--ops-green-dark);
        color: #fff;
        box-shadow: 0 4px 8px rgba(99,102,241,.35);
        transform: translateY(-1px);
    }

    .ops-card-accent { border-top: 3px solid var(--ops-green) !important; }

    /* ── Step badge ────────────────────────────────────────────────── */
    .ops-step-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.5rem; height: 1.5rem; background: var(--ops-green);
        color: #fff; border-radius: 50%; font-size: 0.72rem; font-weight: 700;
        flex-shrink: 0; line-height: 1;
    }

    /* ── Detail modal ──────────────────────────────────────────────── */
    .ops-detail-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .ops-detail-section h6 {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--ops-green-text);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    /* ── Detail: header bar ──────────────────────────────────────── */
    .ops-det-header {
        background: #ffffff;
        border-radius: .75rem;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-top: 4px solid #6366f1;
        box-shadow: 0 1px 4px rgba(99,102,241,.08);
    }
    .ops-det-name {
        font-size: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        line-height: 1.2;
        color: #1e293b;
    }
    .ops-det-area { font-size: .72rem; color: #64748b; margin-top: 2px; }
    .ops-det-id   { font-size: 1.625rem; font-weight: 900; color: var(--ops-green); line-height: 1; }
    .ops-det-lbl  { font-size: .62rem; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }

    /* ── Viewer row (fixed height — never moves) ───────────────── */
    .ops-visor-row { height: 520px; }
    .ops-visor-row > [class*="col"] { height: 100%; }
    @media (max-width: 767.98px) {
        .ops-visor-row { height: auto !important; }
        .ops-visor-row > [class*="col"] { height: auto !important; }
        .ops-tramo-body  { min-height: 240px !important; }
        .ops-bitacora-body { min-height: 120px !important; }
    }

    /* ── Asset Viewer ────────────────────────────────────────────── */
    .ops-asset-viewer {
        background: #f1f5f9;
        border-radius: .75rem;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: 2px solid #e2e8f0;
    }

    /* ── Acciones de Tramo ───────────────────────────────────────── */
    .ops-tramo-wrap {
        display: flex; flex-direction: column;
        flex: 2 1 0; min-height: 0;
        border: 1px solid #e2e8f0; border-radius: .75rem; overflow: hidden;
    }
    .ops-tramo-hdr {
        background: #1e293b; color: #e2e8f0;
        font-size: .7rem; font-weight: 700; letter-spacing: .6px; text-transform: uppercase;
        padding: .55rem .875rem; display: flex; align-items: center; gap: .5rem; flex-shrink: 0;
    }
    .ops-tramo-hdr i { color: #38bdf8; }
    .ops-tramo-body {
        flex: 1 1 0; min-height: 220px; overflow-y: auto; padding: .5rem .75rem;
        background: #f8fafc; scrollbar-width: thin;
    }
    .ops-tramo-body::-webkit-scrollbar { width: 4px; }
    .ops-tramo-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .ops-tramo-foot {
        flex-shrink: 0; padding: .3rem .5rem;
        border-top: 1px solid #e2e8f0; background: #f1f5f9;
    }

    /* ── Bitácora Forense ────────────────────────────────────────── */
    .ops-bitacora-wrap {
        display: flex; flex-direction: column;
        flex: 1; min-height: 0;
        border: 1px solid #1e293b; border-radius: .75rem; overflow: hidden;
    }
    .ops-bitacora-hdr {
        background: #0f172a; color: #38bdf8;
        font-size: .7rem; font-weight: 700; letter-spacing: .7px; text-transform: uppercase;
        padding: .55rem .875rem; display: flex; align-items: center; gap: .5rem;
    }
    .ops-bitacora-hdr i { font-size: .85rem; }
    .ops-bitacora-body {
        background: #0f172a; padding: .625rem .875rem;
        flex: 1 1 0; min-height: 0; overflow-y: auto; scrollbar-width: thin;
    }
    .ops-bitacora-body::-webkit-scrollbar { width: 4px; }
    .ops-bitacora-body::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .ops-bitacora-entry {
        font-family: 'Courier New', monospace; font-size: .68rem;
        color: #94a3b8; padding: .25rem 0; border-bottom: 1px solid #1e293b;
        line-height: 1.45; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ops-bitacora-entry:last-child { border-bottom: none; }
    .ops-bitacora-entry .bit-name { color: #38bdf8; font-weight: 700; }
    .ops-bitacora-entry .bit-accion { color: #e2e8f0; }
    .ops-bitacora-entry .bit-fecha { color: #475569; }
    .ops-bitacora-empty { font-size: .72rem; color: #334155; text-align: center; padding: .75rem; font-style: italic; }
    .ops-asset-viewer img,
    .ops-asset-viewer video  { max-width: 100%; max-height: 100%; object-fit: contain; }
    .ops-av-ph {
        display: flex; flex-direction: column; align-items: center;
        gap: .5rem; color: #334155; font-size: .78rem; text-align: center; padding: 1.5rem;
    }
    .ops-av-ph i { font-size: 2.5rem; }
    .ops-av-lbl {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(15,23,42,.8); color: #e2e8f0;
        font-size: .62rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; padding: .2rem .75rem; text-align: center; display: none;
    }

    /* ── Evidence sections ───────────────────────────────────────── */
    .ops-ev-section  { margin-bottom: 1rem; }
    .ops-ev-hdr {
        display: flex; align-items: center; gap: .5rem;
        padding: .45rem .875rem;
        border-radius: .5rem .5rem 0 0;
        font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    }
    .ops-ev-hdr-orange { background:#fff7ed; border:1px solid #fed7aa; border-bottom:none; color:#9a3412; }
    .ops-ev-hdr-blue   { background:#eff6ff; border:1px solid #bfdbfe; border-bottom:none; color:#1e40af; }
    .ops-ev-hdr-green  { background:#f0fdf4; border:1px solid #bbf7d0; border-bottom:none; color:#166534; }
    .ops-ev-hdr-purple { background:#faf5ff; border:1px solid #e9d5ff; border-bottom:none; color:#6b21a8; }

    .ops-ev-slots-wrap {
        padding: .75rem; background: #f8fafc;
        border: 1px solid #e2e8f0; border-top: none;
        border-radius: 0 0 .5rem .5rem;
        display: flex; gap: .625rem; overflow-x: auto; scrollbar-width: thin;
    }
    .ops-ev-slots-wrap::-webkit-scrollbar { height: 5px; }
    .ops-ev-slots-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Square image/video slot */
    .ops-ev-slot {
        flex-shrink: 0; width: 120px; height: 120px;
        background: #fff; border: 2px dashed #cbd5e1; border-radius: .625rem;
        cursor: pointer; position: relative;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .3rem; transition: border-color .15s, background .15s; overflow: hidden;
    }
    .ops-ev-slot:hover                { border-color: var(--ops-green); background: #eef2ff; }
    .ops-ev-slot.has-file             { border-style: solid; border-color: #e2e8f0; cursor: default; }
    .ops-ev-slot.uploading            { opacity: .65; pointer-events: none; }
    .ops-ev-slot .slot-icon-ph        { font-size: 1.35rem; color: #94a3b8; pointer-events: none; }
    .ops-ev-slot .slot-lbl {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(15,23,42,.72); color: #fff;
        font-size: .58rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; padding: .18rem .25rem;
        text-align: center; pointer-events: none; line-height: 1.2;
    }
    .ops-slot-btn {
        position: absolute; top: 5px; right: 5px;
        width: 22px; height: 22px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; border: none; cursor: pointer; z-index: 2;
        box-shadow: 0 2px 5px rgba(0,0,0,.25);
    }
    .ops-slot-btn-add { background: var(--ops-green); color: #fff; pointer-events: none; }
    .ops-slot-btn-rep { background: #f97316; color: #fff; }
    .ops-ev-slot img.ops-thumb,
    .ops-ev-slot video.ops-thumb      { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ops-slot-vid-ov {
        position: absolute; inset: 0; background: rgba(0,0,0,.38);
        display: flex; align-items: center; justify-content: center; pointer-events: none;
    }
    .ops-slot-vid-ov i                { font-size: 1.8rem; color: #fff; }
    .ops-slot-upload-spin {
        position: absolute; inset: 0; background: rgba(255,255,255,.75);
        display: flex; align-items: center; justify-content: center; z-index: 3;
    }

    /* PDF / wide slot */
    .ops-ev-slot-pdf {
        width: 100%; min-height: 88px; background: #fff;
        border: 2px dashed #cbd5e1; border-radius: .625rem;
        cursor: pointer; position: relative;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .35rem; transition: border-color .15s, background .15s;
        overflow: hidden; padding: .75rem;
    }
    .ops-ev-slot-pdf:hover            { border-color: var(--ops-green); background: #eef2ff; }
    .ops-ev-slot-pdf.has-file         { border-style: solid; border-color: #e2e8f0; }
    .ops-ev-slot-pdf .slot-icon-ph    { font-size: 1.6rem; color: #94a3b8; }
    .ops-ev-slot-pdf .slot-sublbl     { font-size: .65rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; text-align: center; }
    .ops-ev-slot-pdf .slot-fname      { font-size: .7rem; color: #1e293b; font-weight: 600; max-width: 100%; word-break: break-all; text-align: center; }

    /* Empty column placeholder */
    .ops-empty-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 2rem 1rem;
        color: #94a3b8;
        font-size: 0.8rem;
        text-align: center;
    }
    .ops-empty-col i { font-size: 2rem; opacity: 0.35; }

    /* ===================================================================
       DARK MODE — Operaciones Pipeline
       =================================================================== */

    /* Remap CSS vars para dark mode */
    body.dark-mode {
        --ops-green-light:  #1e1b4b;
        --ops-green-border: #4338ca;
        --ops-green-text:   #c7d2fe;
    }

    /* ── Kanban board ─────────────────────────────────────────────── */
    body.dark-mode .ops-pipeline-wrapper::-webkit-scrollbar-track { background: #1e293b; }

    body.dark-mode .ops-column {
        background: #1e293b;
        border-color: #334155;
    }
    body.dark-mode .ops-column-header {
        background: #0f172a;
        border-bottom-color: var(--ops-green);
    }
    body.dark-mode .ops-cards-body::-webkit-scrollbar-thumb { background: #334155; }

    /* ── Kanban cards ─────────────────────────────────────────────── */
    body.dark-mode .ops-card {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .ops-card:hover { box-shadow: 0 4px 14px rgba(99,102,241,.25); }
    body.dark-mode .ops-card-nombre { color: #e2e8f0; }
    body.dark-mode .ops-card-credito { color: #94a3b8; }

    body.dark-mode .ops-aging-green  { background: #1e1b4b; color: #c7d2fe; border-color: #4338ca; }
    body.dark-mode .ops-aging-yellow { background: #422006; color: #fcd34d; border-color: #92400e; }
    body.dark-mode .ops-aging-red    { background: #450a0a; color: #fca5a5; border-color: #7f1d1d; }

    body.dark-mode .ops-area-badge { background: #1e293b; border-color: #334155; color: #94a3b8; }

    body.dark-mode .ops-checklist { border-top-color: #334155; }
    body.dark-mode .ops-chk-item  { color: #64748b; }
    body.dark-mode .ops-chk-item.done { color: var(--ops-green); }

    body.dark-mode .ops-empty-col { color: #475569; }

    /* ── Modal detalle: sección genérica ─────────────────────────── */
    body.dark-mode .ops-detail-section {
        background: #1e293b;
        border-color: #334155;
    }
    body.dark-mode .ops-detail-section h6 { color: #a7f3d0; }

    /* ── Modal detalle: footer con inline style ───────────────────── */
    body.dark-mode #modalDetalleOperacion .modal-footer {
        background: #0f172a !important;
        border-top-color: #334155 !important;
    }

    /* ── Header info bar (dark mode) ─────────────────────────────── */
    body.dark-mode .ops-det-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-color: #334155;
        border-top-color: #6366f1;
        box-shadow: 0 1px 4px rgba(99,102,241,.18);
    }
    body.dark-mode .ops-det-name  { color: #f1f5f9; }
    body.dark-mode .ops-det-area  { color: #94a3b8; }
    body.dark-mode .ops-det-lbl   { color: #475569; }

    /* ── Asset Viewer (dark mode) ────────────────────────────────── */
    body.dark-mode .ops-asset-viewer {
        background: #0f172a;
        border-color: #1e293b;
    }
    body.dark-mode .ops-av-ph { color: #334155; }

    /* ── Acciones de Tramo ───────────────────────────────────────── */
    body.dark-mode .ops-tramo-wrap { border-color: #334155; }
    body.dark-mode .ops-tramo-body {
        background: #1e293b;
        scrollbar-color: #334155 transparent;
    }
    body.dark-mode .ops-tramo-body .text-muted { color: #475569 !important; }
    body.dark-mode .ops-tramo-foot {
        background: #0f172a;
        border-top-color: #334155;
    }

    /* ── Secciones de evidencia ──────────────────────────────────── */
    body.dark-mode .ops-ev-hdr-orange { background: #431407; border-color: #9a3412; color: #fdba74; }
    body.dark-mode .ops-ev-hdr-blue   { background: #1e3a5f; border-color: #1e40af; color: #93c5fd; }
    body.dark-mode .ops-ev-hdr-green  { background: #14532d; border-color: #166534; color: #86efac; }
    body.dark-mode .ops-ev-hdr-purple { background: #3b0764; border-color: #6b21a8; color: #d8b4fe; }

    body.dark-mode .ops-ev-slots-wrap {
        background: #1e293b;
        border-color: #334155;
    }
    body.dark-mode .ops-ev-slots-wrap::-webkit-scrollbar-thumb { background: #334155; }

    body.dark-mode .ops-ev-slot {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .ops-ev-slot:hover    { background: #1e1b4b; border-color: var(--ops-green); }
    body.dark-mode .ops-ev-slot.has-file { border-color: #334155; }
    body.dark-mode .ops-ev-slot .slot-icon-ph { color: #475569; }

    body.dark-mode .ops-ev-slot-pdf {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .ops-ev-slot-pdf:hover    { background: #1e1b4b; border-color: var(--ops-green); }
    body.dark-mode .ops-ev-slot-pdf.has-file { border-color: #334155; }
    body.dark-mode .ops-ev-slot-pdf .slot-icon-ph { color: #475569; }
    body.dark-mode .ops-ev-slot-pdf .slot-sublbl  { color: #475569; }
    body.dark-mode .ops-ev-slot-pdf .slot-fname   { color: #e2e8f0; }

    body.dark-mode .ops-slot-upload-spin { background: rgba(15,23,42,.85); }

    /* ── PDF doc wrapper ─────────────────────────────────────────── */
    .ops-ev-doc-body {
        padding: .5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 .5rem .5rem;
    }
    body.dark-mode .ops-ev-doc-body {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
</style>

<!-- Título -->
<h4 class="mb-2">
    <i class="fa-solid fa-diagram-project me-2" style="color: var(--ops-green);"></i>
    Pipeline de Operaciones
</h4>

<!-- Banner contextual -->
<div class="mb-3 d-flex align-items-center gap-2 px-3 py-2 rounded-2"
     style="background:var(--ops-green-light); border:1px solid var(--ops-green-border); border-left:4px solid var(--ops-green);">
    <i class="fa-solid fa-circle-info" style="color:var(--ops-green); flex-shrink:0;"></i>
    <span style="font-size:0.875rem; color:var(--ops-green-text);">
        <strong>Módulo Operaciones</strong> — Seguimiento del proceso de recuperación de motos adjudicadas en cada etapa del pipeline.
    </span>
</div>

<!-- Toolbar -->
<div class="d-flex align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="ops-btn-refresh" onclick="opsCargarPipeline()">
            <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
        </button>
    </div>
    <div id="ops-pipeline-stats" class="d-flex gap-2 flex-wrap align-items-center">
        <!-- stats se renderizan por JS -->
    </div>
</div>

<!-- Kanban Board -->
<div class="ops-pipeline-wrapper" id="ops-pipeline-board">
    <!-- columnas se renderizan por JS -->
</div>

<!-- Spinner de carga inicial -->
<div id="ops-loading" class="text-center py-5">
    <div class="spinner-border" style="color:var(--ops-green);"></div>
    <p class="mt-2 text-muted small">Cargando pipeline…</p>
</div>


<!-- ═══════════════════════════════════════════════════════════════════
     MODAL: DETALLE / MOVER ETAPA
     ═══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetalleOperacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"
         style="max-width:96vw;width:96vw;margin:.5rem auto;">
        <div class="modal-content" style="height:94vh;">
            <div class="modal-header" style="background:var(--ops-green-light); border-bottom:1px solid var(--ops-green-border);">
                <h5 class="modal-title" style="color:var(--ops-green-text);">
                    <i class="fa-solid fa-diagram-project me-2" style="color:var(--ops-green);"></i>
                    Detalle de Operación — <span id="det-folio"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="det-body" style="overflow-y:auto;flex:1 1 auto;">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ops-green);"></div>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-danger btn-sm" id="det-btn-eliminar">
                    <i class="fa-solid fa-trash me-1"></i>Eliminar
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-outline-warning btn-sm" id="det-btn-regresar" style="display:none;">
                        <i class="fa-solid fa-arrow-left me-1"></i>Regresar etapa
                    </button>
                    <button type="button" class="btn btn-ops-green btn-sm" id="det-btn-mover">
                        <i class="fa-solid fa-arrow-right me-1"></i>Avanzar etapa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL: RESUMEN DICTAMEN (sólo lectura — estatus cancelado)
     ═══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalOpsDictamenResumen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#7f1d1d 0%,#dc2626 100%);border-radius:.4rem .4rem 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fa-solid fa-file-circle-check me-2"></i>Resumen del dictamen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ops-dictamen-resumen-body">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;">
                <button type="button" class="btn fw-bold"
                        style="background:#e2e8f0;color:#475569;border:none;border-radius:2rem;padding:.45rem 1.4rem;"
                        data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════════════════════════════ -->
<script>
(function () {

    // ──────────────────────────────────────────────────────────────────
    // CONSTANTES
    // ──────────────────────────────────────────────────────────────────
    const STAGES = [
        'Retenciones',
        'Recibido',
        'Procesando IA',
        'Revisión Recuperaciones',
        'Cierre Documentado',
        'Recepción',
    ];

    const STAGE_ICONS = {
        'Recibido':                  'fa-inbox',
        'Procesando IA':             'fa-robot',
        'Revisión Recuperaciones':   'fa-magnifying-glass',
        'Retenciones':               'fa-hand',
        'Cierre Documentado':        'fa-file-circle-check',
        'Recepción':                 'fa-flag-checkered',
    };

    // Checklist items por etapa (los que se muestran en la mini-card)
    const STAGE_CHECKLIST = {
        'Recibido':                  ['Datos logísticos completos', 'Vehículo identificado'],
        'Procesando IA':             ['Validación IA pendiente', 'Score asignado'],
        'Revisión Recuperaciones':   ['Revisión equipo', 'Observaciones registradas'],
        'Retenciones':               ['Trámite retención', 'Docs firmados'],
        'Cierre Documentado':        ['Factura validada', 'Expediente completo'],
        'Recepción':                 ['Entrega física', 'Cierre en sistema'],
    };

    // Estado global
    let _operaciones  = [];
    let _detalleActual = null;

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE DEFINITIONS
    // ──────────────────────────────────────────────────────────────────
    const EV_SECTIONS = [
        {
            key: 'recoleccion',
            label: 'Evidencia de Recolección (Final)',
            headerClass: 'ops-ev-hdr-orange',
            icon: 'fa-camera-retro',
            slots: [
                { key: 'rec_tacometro', label: 'Tacómetro Rec.',  icon: 'fa-gauge-high',    accept: 'image/jpeg,image/png' },
                { key: 'rec_serie',     label: 'No. Serie Rec.',  icon: 'fa-hashtag',        accept: 'image/jpeg,image/png' },
                { key: 'rec_frontal',   label: 'Frontal Rec.',    icon: 'fa-camera',         accept: 'image/jpeg,image/png' },
                { key: 'rec_lateral',   label: 'Lateral Rec.',    icon: 'fa-camera-rotate',  accept: 'image/jpeg,image/png' },
            ],
        },
        {
            key: 'fisica',
            label: 'Evidencia Física (Momento 1)',
            headerClass: 'ops-ev-hdr-blue',
            icon: 'fa-camera',
            slots: [
                { key: 'fis_vin',       label: 'Serie VIN',       icon: 'fa-barcode',        accept: 'image/jpeg,image/png' },
                { key: 'fis_tacometro', label: 'Tacómetro',       icon: 'fa-gauge-high',     accept: 'image/jpeg,image/png' },
                { key: 'fis_frontal',   label: 'Vista Frontal',   icon: 'fa-camera',         accept: 'image/jpeg,image/png' },
                { key: 'fis_lateral',   label: 'Vista Lateral',   icon: 'fa-camera-rotate',  accept: 'image/jpeg,image/png' },
                { key: 'fis_360',       label: 'Inspección 360',  icon: 'fa-video',          accept: 'video/mp4', isVideo: true },
            ],
        },
    ];

    const EV_DOCS = [
        {
            key: 'repuve',  label: 'Momento 2: Repuve',
            headerClass: 'ops-ev-hdr-green',  icon: 'fa-file-pdf',
            slotKey: 'doc_repuve',  slotLabel: 'Subir Repuve',
            accept: 'application/pdf',
        },
        {
            key: 'factura', label: 'Momento 3: Factura',
            headerClass: 'ops-ev-hdr-purple', icon: 'fa-file-invoice',
            slotKey: 'doc_factura', slotLabel: 'Subir Factura',
            accept: 'application/pdf,image/jpeg,image/png',
        },
    ];

    // Evidence state: `${opId}_${slotKey}` → { src, type, label, uploaded }
    let _evState = {};
    let _activeOpId = null;

    // ──────────────────────────────────────────────────────────────────
    // INIT
    // ──────────────────────────────────────────────────────────────────
    function opsPipelineInit() {
        opsCargarPipeline();
    }

    // ──────────────────────────────────────────────────────────────────
    // CARGAR PIPELINE
    // ──────────────────────────────────────────────────────────────────
    function opsCargarPipeline() {
        document.getElementById('ops-loading').style.display = 'block';
        document.getElementById('ops-pipeline-board').style.display = 'none';

        fetch('/MotosAdjudicadas/obtenerOperaciones', { method: 'GET', headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al cargar el pipeline.');
                _operaciones = data.operaciones || [];
                opsRenderPipeline(_operaciones);
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'No se pudo cargar el pipeline.', confirmButtonColor: '#6366f1' });
            })
            .finally(() => {
                document.getElementById('ops-loading').style.display = 'none';
                document.getElementById('ops-pipeline-board').style.display = 'flex';
            });
    }

    // ──────────────────────────────────────────────────────────────────
    // RENDER PIPELINE
    // ──────────────────────────────────────────────────────────────────
    function opsRenderPipeline(ops) {
        const board = document.getElementById('ops-pipeline-board');
        board.innerHTML = '';

        // Agrupar por etapa
        // en_transito → Recibido  |  cancelado → Retenciones (tarjeta roja)
        const groups = {};
        STAGES.forEach(s => groups[s] = []);
        ops.forEach(op => {
            let stage = op.estatus;
            if (stage === 'en_transito') stage = 'Recibido';
            else if (stage === 'cancelado') stage = 'Retenciones';
            if (groups[stage]) groups[stage].push(op);
        });

        // Stats
        const statsEl = document.getElementById('ops-pipeline-stats');
        statsEl.innerHTML = `<span class="badge bg-label-secondary" style="font-size:.72rem;">Total: <strong>${ops.length}</strong></span>`;
        const atrasadas = ops.filter(o => (o.dias_en_pipeline || 0) > 5).length;
        if (atrasadas > 0) {
            statsEl.innerHTML += `<span class="badge" style="background:#fee2e2;color:#7f1d1d;font-size:.72rem;border-radius:999px;padding:.2rem .55rem;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>${atrasadas} atrasada${atrasadas > 1 ? 's' : ''}
            </span>`;
        }

        STAGES.forEach(stage => {
            const cards = groups[stage];
            const col = document.createElement('div');
            col.className = 'ops-column';
            col.innerHTML = `
                <div class="ops-column-header">
                    <span class="ops-col-title"><i class="fa-solid ${STAGE_ICONS[stage]} me-1"></i>${stage}</span>
                    <span class="ops-col-count">${cards.length}</span>
                </div>
                <div class="ops-cards-body" id="ops-col-${opsSlug(stage)}">
                    ${cards.length === 0 ? opsEmptyCol() : cards.map(opsRenderCard).join('')}
                </div>
            `;
            board.appendChild(col);
        });
    }

    function opsEmptyCol() {
        return `<div class="ops-empty-col"><i class="fa-regular fa-folder-open"></i><span>Sin operaciones</span></div>`;
    }

    // ──────────────────────────────────────────────────────────────────
    // RENDER CARD
    // ──────────────────────────────────────────────────────────────────
    function opsRenderCard(op) {
        const dias = parseInt(op.dias_en_pipeline || 0);
        let agingClass = 'ops-aging-green';
        let agingLabel = `${dias}d`;
        if (dias > 5)      { agingClass = 'ops-aging-red';    }
        else if (dias > 2) { agingClass = 'ops-aging-yellow'; }

        const checks = (STAGE_CHECKLIST[op.estatus] || []).map((item, i) => {
            const done = i === 0 && opsCardCheckDone(op, i);
            return `<div class="ops-chk-item ${done ? 'done' : ''}">
                <i class="fa-solid ${done ? 'fa-circle-check' : 'fa-circle'}" style="${done ? '' : 'opacity:.35;'}"></i>
                ${item}
            </div>`;
        }).join('');

        const areaHtml = op.area_actual
            ? `<span class="ops-area-badge">${opsEsc(op.area_actual)}</span>`
            : '';

        // Estilos y comportamiento especiales para cancelado
        const esCancelado  = op.estatus === 'cancelado';
        const esTransito   = op.estatus === 'en_transito';
        const cardExtra    = esCancelado ? ' ops-card--cancelado' : (esTransito ? ' ops-card--en-transito' : '');
        const clickHandler = esCancelado
            ? `opsDictamenResumen(${op.id})`
            : `opsAbrirDetalle(${op.id})`;
        const transitoBadge = esTransito
            ? `<div style="margin-top:.3rem;"><span style="background:#dbeafe;color:#1e40af;font-size:.7rem;font-weight:700;border-radius:20px;padding:1px 8px;"><i class="fa-solid fa-truck-fast me-1"></i>En tránsito</span></div>`
            : '';
        const canceladoBadge = esCancelado
            ? `<div style="margin-top:.3rem;"><span style="background:#fee2e2;color:#b91c1c;font-size:.7rem;font-weight:700;border-radius:20px;padding:1px 8px;"><i class="fa-solid fa-ban me-1"></i>Cancelado</span></div>`
            : '';

        return `
        <div class="ops-card${cardExtra}" onclick="${clickHandler}" title="${opsEsc(op.nombre_cliente)}">
            <div class="d-flex align-items-center justify-content-between mb-1 gap-1 flex-wrap">
                <span class="ops-card-folio">${opsEsc(op.folio)}</span>
                <span class="ops-aging-badge ${agingClass}">${agingLabel}</span>
            </div>
            <div class="ops-card-nombre">${opsEsc(op.nombre_cliente)}</div>
            <div class="ops-card-credito">
                <i class="fa-solid fa-hashtag me-1" style="opacity:.5;"></i>Crédito ${opsEsc(String(op.id_credito))}
                ${areaHtml ? '&nbsp;' + areaHtml : ''}
            </div>
            <div class="ops-checklist">${checks}</div>
            ${canceladoBadge}${transitoBadge}
        </div>`;
    }

    // Lógica mínima de "completado" para el primer ítem del checklist por etapa
    function opsCardCheckDone(op, idx) {
        if (op.estatus === 'Recibido'    && idx === 0) return !!(op.responsable_entrega);
        if (op.estatus === 'Procesando IA' && idx === 1) return op.score_ia > 0;
        return false;
    }

    // ──────────────────────────────────────────────────────────────────
    // MODAL: RESUMEN DEL DICTAMEN (cancelado — sólo lectura)
    // ──────────────────────────────────────────────────────────────────
    function opsDictamenResumen(idOperacion) {
        const body = document.getElementById('ops-dictamen-resumen-body');
        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></div>';
        new bootstrap.Modal(document.getElementById('modalOpsDictamenResumen')).show();

        fetch(`/AtencionClientes/obtenerDictamen?id=${idOperacion}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'No se encontró el dictamen.');
                const d = data.dictamen;
                const fila = (lbl, val) => `
                    <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.35rem 0;border-bottom:1px solid #f1f5f9;">
                        <span style="color:#64748b;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;min-width:160px;flex-shrink:0;">${opsEsc(lbl)}</span>
                        <span style="color:#1e293b;font-weight:500;">${val ? opsEsc(String(val)) : '<span style="color:#94a3b8;font-style:italic;">—</span>'}</span>
                    </div>`;

                body.innerHTML = `
                    <div style="margin-bottom:.75rem;">
                        <span style="background:#fee2e2;color:#b91c1c;font-size:.8rem;font-weight:700;border-radius:20px;padding:3px 12px;">
                            <i class="fa-solid fa-ban me-1"></i>Cancelado — promesa de pago
                        </span>
                    </div>
                    ${fila('Llamada a',          d.llamada_a)}
                    ${fila('Número',             d.numero)}
                    ${fila('Persona contactada', d.persona_contactada)}
                    ${fila('Tipo contacto',      d.tipo_contacto)}
                    ${fila('Resultado',          d.resultado)}
                    ${fila('Dictamen',           d.dictamen)}
                    ${fila('Plataforma',         d.plataforma)}
                    ${d.comentarios ? fila('Comentarios', d.comentarios) : ''}
                    ${fila('Registrado',         d.fecha_alta_fmt)}
                `;
            })
            .catch(err => {
                body.innerHTML = `<div class="alert alert-danger">${opsEsc(err.message)}</div>`;
            });
    }


    // ──────────────────────────────────────────────────────────────────
    function opsAbrirDetalle(id) {
        _detalleActual = null;
        document.getElementById('det-folio').textContent = '…';
        document.getElementById('det-body').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm" style="color:var(--ops-green);"></div>
            </div>`;
        new bootstrap.Modal(document.getElementById('modalDetalleOperacion')).show();

        fetch(`/MotosAdjudicadas/obtenerDetalle/${id}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message);
                _detalleActual = data.detalle;
                opsRenderDetalle(data.detalle);
            })
            .catch(err => {
                document.getElementById('det-body').innerHTML = `
                    <div class="alert alert-danger m-3">${opsEsc(err.message)}</div>`;
            });
    }

    function opsRenderDetalle(op) {
        _activeOpId = op.id;
        document.getElementById('det-folio').textContent = op.folio;

        // Botones avanzar / regresar etapa
        const stageIdx   = STAGES.indexOf(op.estatus);
        const siguiente  = stageIdx >= 0 && stageIdx < STAGES.length - 1 ? STAGES[stageIdx + 1] : null;
        const anterior   = stageIdx > 0 ? STAGES[stageIdx - 1] : null;
        const btnMover   = document.getElementById('det-btn-mover');
        const btnRegresar = document.getElementById('det-btn-regresar');
        if (siguiente) {
            btnMover.style.display = '';
            btnMover.onclick = () => opsMoverEstatus(op.id, siguiente);
            btnMover.innerHTML = `<i class="fa-solid fa-arrow-right me-1"></i>Mover a "${siguiente}"`;
        } else {
            btnMover.style.display = 'none';
        }
        if (anterior) {
            btnRegresar.style.display = '';
            btnRegresar.onclick = () => opsRegresarEstatus(op.id, anterior, op.estatus, op.area_actual || '');
            btnRegresar.innerHTML = `<i class="fa-solid fa-arrow-left me-1"></i>Regresar a "${anterior}"`;
        } else {
            btnRegresar.style.display = 'none';
        }
        document.getElementById('det-btn-eliminar').onclick = () => opsEliminar(op.id);

        // Aging
        const dias = parseInt(op.dias_en_pipeline || 0);
        let agingClass = 'ops-aging-green';
        if (dias > 5) agingClass = 'ops-aging-red';
        else if (dias > 2) agingClass = 'ops-aging-yellow';

        // Build evidence map from server data
        (op.evidencias || []).forEach(ev => {
            const k = `${op.id}_${ev.slot}`;
            if (!_evState[k]) {
                _evState[k] = { src: ev.url, type: ev.tipo, label: ev.slot, uploaded: true };
            }
        });

        // Build all slots for hidden inputs
        const allSlotDefs = [
            ...EV_SECTIONS.flatMap(s => s.slots),
            ...EV_DOCS.map(d => ({ key: d.slotKey, accept: d.accept, isPDF: true })),
        ];

        const fileInputs = allSlotDefs.map(s =>
            `<input type="file" id="inp-${s.key}" style="display:none" accept="${s.accept || 'image/jpeg,image/png'}"
                    onchange="opsSlotChange('${s.key}',${op.id},this)">`
        ).join('');

        const html = `
        <!-- ① INFO BAR -->
        <div class="ops-det-header d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-3">
            <div>
                <div class="ops-det-name">${opsEsc(op.nombre_cliente)}</div>
                <div class="ops-det-area">
                    <i class="fa-solid fa-location-dot me-1"></i>${opsEsc(op.area_actual || 'Sin área')}
                </div>
                <div class="mt-1 d-flex gap-2 flex-wrap">
                    <span class="ops-aging-badge ${agingClass}">${dias}d en pipeline</span>
                    <span class="badge" style="background:var(--ops-green);color:#fff;border-radius:999px;font-size:.68rem;padding:.2rem .55rem;">${opsEsc(op.estatus)}</span>
                </div>
            </div>
            <div class="text-end">
                <div class="ops-det-lbl">ID Crédito</div>
                <div class="ops-det-id">#${opsEsc(String(op.id_credito))}</div>
                <div class="ops-det-lbl mt-1">Folio</div>
                <div style="font-size:.78rem;color:#94a3b8;font-weight:600;">${opsEsc(op.folio)}</div>
            </div>
        </div>

        <!-- ② VISOR + ACCIONES DE TRAMO + BITÁCORA -->
        <div class="row g-3 mb-3 ops-visor-row" style="align-items:stretch;">
            <div class="col-12 col-md-6 d-flex flex-column">
                <div class="ops-asset-viewer">
                    <div class="ops-av-ph" id="ops-av-ph">
                        <i class="fa-regular fa-image"></i>
                        <span>Selecciona una evidencia para previsualizarla</span>
                    </div>
                    <img id="ops-av-img" style="display:none" src="" alt="">
                    <video id="ops-av-vid" style="display:none" controls playsinline src=""></video>
                    <iframe id="ops-av-pdf" style="display:none;width:100%;height:100%;border:none;border-radius:.5rem;" src="" allowfullscreen></iframe>
                    <div class="ops-av-lbl" id="ops-av-lbl"></div>
                </div>
            </div>
            <div class="col-12 col-md-6 d-flex flex-column gap-2" style="height:100%;">
                <div class="ops-tramo-wrap">
                    <div class="ops-tramo-hdr">
                        <i class="fa-solid fa-comment-lines"></i>Acciones de Tramo
                    </div>
                    <div class="ops-tramo-body" id="det-obs-list">
                        ${opsRenderObservaciones(op.observaciones || [])}
                    </div>
                    <div class="ops-tramo-foot">
                        <div class="input-group input-group-sm">
                            <input type="text" id="det-obs-input" class="form-control"
                                   placeholder="Especifique el motivo del rechazo o aprobación del dictamen…"
                                   maxlength="500">
                            <button class="btn btn-ops-green" type="button"
                                    onclick="opsAgregarObservacion(${op.id},'${opsEscJS(op.estatus)}','${opsEscJS(op.area_actual || '')}')">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="ops-bitacora-wrap">
                    <div class="ops-bitacora-hdr">
                        <i class="fa-solid fa-clock-rotate-left"></i>Bitácora Forense Maxikash
                    </div>
                    <div class="ops-bitacora-body" id="det-bitacora">
                        ${opsRenderBitacora(op.bitacora || [])}
                    </div>
                </div>
            </div>
        </div>

        <!-- ④ EVIDENCIA DE RECOLECCIÓN -->
        ${opsRenderEvSection(op.id, EV_SECTIONS[0])}

        <!-- ④ EVIDENCIA FÍSICA (MOMENTO 1) -->
        ${opsRenderEvSection(op.id, EV_SECTIONS[1])}

        <!-- ⑤ MOMENTO 2 & 3 -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6">${opsRenderEvDoc(op.id, EV_DOCS[0])}</div>
            <div class="col-12 col-sm-6">${opsRenderEvDoc(op.id, EV_DOCS[1])}</div>
        </div>

        <!-- ⑥ DATOS DEL EXPEDIENTE (acordeón) -->
        <div class="accordion accordion-flush mb-3">
            <div class="accordion-item" style="border:1px solid #e2e8f0;border-radius:.5rem;overflow:hidden;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#det-acc-datos"
                            style="font-size:.78rem;font-weight:700;color:var(--ops-green-text);background:var(--ops-green-light);">
                        <i class="fa-solid fa-circle-info me-2"></i>Datos del expediente
                    </button>
                </h2>
                <div id="det-acc-datos" class="accordion-collapse collapse">
                    <div class="accordion-body py-2 px-3">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <div class="ops-detail-section mb-2">
                                    <h6><i class="fa-solid fa-motorcycle me-1"></i>Vehículo</h6>
                                    ${opsDatoPar('Marca / Modelo', [op.marca, op.modelo].filter(Boolean).join(' ') || '—')}
                                    ${opsDatoPar('N° Serie',  op.serie     || '—')}
                                    ${opsDatoPar('N° Motor', op.num_motor || '—')}
                                    ${opsDatoPar('Placas',   op.placas    || '—')}
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="ops-detail-section mb-2">
                                    <h6><i class="fa-solid fa-truck me-1"></i>Logística</h6>
                                    ${opsDatoPar('Responsable', op.responsable_entrega   || '—')}
                                    ${opsDatoPar('Teléfono',    op.telefono_contacto     || '—')}
                                    ${opsDatoPar('Dirección',   op.direccion_recoleccion || '—')}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="ops-detail-section mb-0">
                                    <h6><i class="fa-solid fa-dollar-sign me-1"></i>Financiero</h6>
                                    <div class="row g-1">
                                        <div class="col-4">${opsDatoPar('Días mora',    (op.dias_mora || 0) + 'd')}</div>
                                        <div class="col-4">${opsDatoPar('Saldo capital', opsFormatMXN(op.saldo_capital || 0))}</div>
                                        <div class="col-4">${opsDatoPar('Adeudo total',  opsFormatMXN(op.adeudo_total  || 0))}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ⑦ HISTORIAL -->
        <div class="ops-detail-section mb-0">
            <h6><i class="fa-solid fa-clock-rotate-left me-1"></i>Historial de etapas</h6>
            ${opsRenderHistorial(op.historial || [])}
        </div>

        ${fileInputs}`;

        document.getElementById('det-body').innerHTML = html;
        requestAnimationFrame(() => {
            const _obsList = document.getElementById('det-obs-list');
            if (_obsList) _obsList.scrollTop = _obsList.scrollHeight;
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE: render section (image/video slots)
    // ──────────────────────────────────────────────────────────────────
    function opsRenderEvSection(opId, section) {
        const slots = section.slots.map(slot => {
            const ev = _evState[`${opId}_${slot.key}`];
            if (ev && ev.src) {
                const isVideo = slot.isVideo || ev.type === 'video';
                return `
                <div class="ops-ev-slot has-file" id="slot-${slot.key}"
                     onclick="opsSlotView('${slot.key}',${opId})">
                    ${isVideo
                        ? `<video class="ops-thumb" src="${opsEsc(ev.src)}" muted></video>
                           <div class="ops-slot-vid-ov"><i class="fa-solid fa-play"></i></div>`
                        : `<img class="ops-thumb" src="${opsEsc(ev.src)}" alt="">`}
                    <div class="slot-lbl">${opsEsc(slot.label)}</div>
                    <button class="ops-slot-btn ops-slot-btn-rep"
                            onclick="event.stopPropagation();opsSlotTrigger('${slot.key}')">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>`;
            }
            return `
            <div class="ops-ev-slot" id="slot-${slot.key}"
                 onclick="opsSlotTrigger('${slot.key}')">
                <i class="fa-solid ${slot.icon} slot-icon-ph"></i>
                <div class="slot-lbl">${opsEsc(slot.label)}</div>
                <button class="ops-slot-btn ops-slot-btn-add">+</button>
            </div>`;
        }).join('');

        return `
        <div class="ops-ev-section">
            <div class="ops-ev-hdr ${section.headerClass}">
                <i class="fa-solid ${section.icon}"></i>${opsEsc(section.label)}
            </div>
            <div class="ops-ev-slots-wrap">${slots}</div>
        </div>`;
    }

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE: render doc slot (PDF/wide)
    // ──────────────────────────────────────────────────────────────────
    function opsRenderEvDoc(opId, doc) {
        const ev     = _evState[`${opId}_${doc.slotKey}`];
        const hasFile = ev && ev.src;
        const isPDF  = ev && ev.type === 'pdf';
        const isImg  = ev && ev.type === 'image';

        let inner;
        if (hasFile) {
            inner = isPDF
                ? `<i class="fa-solid fa-file-pdf slot-icon-ph" style="color:#ef4444;font-size:1.6rem;"></i>
                   <div class="slot-fname">${opsEsc(ev.label || doc.slotLabel)}</div>
                   <button class="ops-slot-btn ops-slot-btn-rep" style="top:8px;right:8px;"
                           onclick="event.stopPropagation();opsSlotTrigger('${doc.slotKey}')">
                       <i class="fa-solid fa-rotate"></i>
                   </button>`
                : `<img style="max-height:60px;max-width:100%;object-fit:contain;border-radius:.375rem;"
                        src="${opsEsc(ev.src)}" alt="">
                   <button class="ops-slot-btn ops-slot-btn-rep" style="top:8px;right:8px;"
                           onclick="event.stopPropagation();opsSlotTrigger('${doc.slotKey}')">
                       <i class="fa-solid fa-rotate"></i>
                   </button>`;
        } else {
            inner = `<i class="fa-solid fa-cloud-arrow-up" style="font-size:1.6rem;color:#94a3b8;"></i>
                     <div class="slot-sublbl">${opsEsc(doc.slotLabel)}</div>
                     <button class="ops-slot-btn ops-slot-btn-add" style="top:8px;right:8px;pointer-events:none;">+</button>`;
        }

        const clickAttr = hasFile
            ? `onclick="opsSlotView('${doc.slotKey}',${opId})"`
            : `onclick="opsSlotTrigger('${doc.slotKey}')"`;


        return `
        <div class="ops-ev-section mb-0">
            <div class="ops-ev-hdr ${doc.headerClass}">
                <i class="fa-solid ${doc.icon}"></i>${opsEsc(doc.label)}
            </div>
            <div class="ops-ev-doc-body">
                <div class="ops-ev-slot-pdf ${hasFile ? 'has-file' : ''}" id="slot-${doc.slotKey}" ${clickAttr}>
                    ${inner}
                </div>
            </div>
        </div>`;
    }

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE: trigger file input
    // ──────────────────────────────────────────────────────────────────
    function opsSlotTrigger(key) {
        const inp = document.getElementById('inp-' + key);
        if (inp) inp.click();
    }

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE: show file in viewer
    // ──────────────────────────────────────────────────────────────────
    function opsSlotView(key, opId) {
        const ev = _evState[`${opId}_${key}`];
        if (!ev || !ev.src) return;

        const ph  = document.getElementById('ops-av-ph');
        const img = document.getElementById('ops-av-img');
        const vid = document.getElementById('ops-av-vid');
        const pdf = document.getElementById('ops-av-pdf');
        const lbl = document.getElementById('ops-av-lbl');
        if (!ph) return;

        ph.style.display  = 'none';
        img.style.display = 'none';
        vid.style.display = 'none';
        if (pdf) { pdf.style.display = 'none'; pdf.src = ''; }

        // Find label
        const allDefs = [...EV_SECTIONS.flatMap(s => s.slots), ...EV_DOCS.map(d => ({ key: d.slotKey, label: d.slotLabel }))];
        const def = allDefs.find(s => s.key === key);

        if (ev.type === 'video') {
            vid.src = ev.src;
            vid.style.display = 'block';
        } else if (ev.type === 'pdf' || ev.tipo === 'pdf') {
            if (pdf) {
                pdf.src = ev.src;
                pdf.style.display = 'block';
            } else {
                // fallback: open in new tab
                window.open(ev.src, '_blank');
                ph.style.display = '';
            }
        } else {
            img.src = ev.src;
            img.style.display = 'block';
        }

        if (lbl) {
            lbl.textContent = def ? def.label : key;
            lbl.style.display = 'block';
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE: file selected → preview + upload
    // ──────────────────────────────────────────────────────────────────
    function opsSlotChange(key, opId, input) {
        const file = input.files[0];
        if (!file) return;

        // Validate by slot type
        const allSlots  = EV_SECTIONS.flatMap(s => s.slots);
        const slotDef   = allSlots.find(s => s.key === key);
        const docDef    = EV_DOCS.find(d => d.slotKey === key);

        if (slotDef && slotDef.isVideo) {
            if (file.type !== 'video/mp4') {
                Swal.fire({ icon: 'warning', title: 'Solo MP4', text: 'Este campo solo acepta videos MP4.', confirmButtonColor: '#6366f1' });
                input.value = ''; return;
            }
        } else if (docDef) {
            const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!allowed.includes(file.type)) {
                Swal.fire({ icon: 'warning', title: 'Tipo no válido', text: 'Solo se aceptan PDF, JPG o PNG.', confirmButtonColor: '#6366f1' });
                input.value = ''; return;
            }
        } else {
            if (!['image/jpeg', 'image/png'].includes(file.type)) {
                Swal.fire({ icon: 'warning', title: 'Solo imágenes', text: 'Solo se aceptan imágenes JPG o PNG.', confirmButtonColor: '#6366f1' });
                input.value = ''; return;
            }
        }

        if (file.size > 20 * 1024 * 1024) {
            Swal.fire({ icon: 'warning', title: 'Archivo muy grande', text: 'El límite es 20 MB.', confirmButtonColor: '#6366f1' });
            input.value = ''; return;
        }

        const mediaType = file.type.startsWith('video/') ? 'video'
                        : file.type === 'application/pdf'  ? 'pdf'
                        : 'image';

        const slotEl = document.getElementById('slot-' + key);
        if (slotEl) slotEl.classList.add('uploading');

        const reader = new FileReader();
        reader.onload = (e) => {
            _evState[`${opId}_${key}`] = {
                src: e.target.result, type: mediaType,
                label: file.name, uploaded: false,
            };
            opsUpdateSlotUI(key, opId);
            opsSlotView(key, opId);
            input.value = '';
            opsUploadEvidencia(key, opId, file);
        };
        reader.readAsDataURL(file);
    }

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE: rebuild single slot HTML
    // ──────────────────────────────────────────────────────────────────
    function opsUpdateSlotUI(key, opId) {
        const ev     = _evState[`${opId}_${key}`];
        const slotEl = document.getElementById('slot-' + key);
        if (!ev || !slotEl) return;

        slotEl.classList.remove('uploading');

        const spinner = ev.uploaded ? '' :
            `<div class="ops-slot-upload-spin">
                <div class="spinner-border spinner-border-sm" style="color:var(--ops-green);"></div>
             </div>`;

        const allSlots = EV_SECTIONS.flatMap(s => s.slots);
        const slotDef  = allSlots.find(s => s.key === key);
        const docDef   = EV_DOCS.find(d => d.slotKey === key);

        if (slotDef) {
            const isVideo = ev.type === 'video';
            slotEl.classList.add('has-file');
            slotEl.setAttribute('onclick', `opsSlotView('${key}',${opId})`);
            slotEl.innerHTML = `
                ${isVideo
                    ? `<video class="ops-thumb" src="${opsEsc(ev.src)}" muted></video>
                       <div class="ops-slot-vid-ov"><i class="fa-solid fa-play"></i></div>`
                    : `<img class="ops-thumb" src="${opsEsc(ev.src)}" alt="">`}
                <div class="slot-lbl">${opsEsc(slotDef.label)}</div>
                <button class="ops-slot-btn ops-slot-btn-rep"
                        onclick="event.stopPropagation();opsSlotTrigger('${key}')">
                    <i class="fa-solid fa-rotate"></i>
                </button>
                ${spinner}`;
        } else if (docDef) {
            const isPDF = ev.type === 'pdf';
            slotEl.classList.add('has-file');
            slotEl.setAttribute('onclick', `opsSlotView('${key}',${opId})`);
            slotEl.innerHTML = isPDF
                ? `<i class="fa-solid fa-file-pdf slot-icon-ph" style="color:#ef4444;font-size:1.6rem;"></i>
                   <div class="slot-fname">${opsEsc(ev.label || docDef.slotLabel)}</div>
                   <button class="ops-slot-btn ops-slot-btn-rep" style="top:8px;right:8px;"
                           onclick="event.stopPropagation();opsSlotTrigger('${key}')">
                       <i class="fa-solid fa-rotate"></i>
                   </button>${spinner}`
                : `<img style="max-height:60px;max-width:100%;object-fit:contain;border-radius:.375rem;"
                        src="${opsEsc(ev.src)}" alt="">
                   <button class="ops-slot-btn ops-slot-btn-rep" style="top:8px;right:8px;"
                           onclick="event.stopPropagation();opsSlotTrigger('${key}')">
                       <i class="fa-solid fa-rotate"></i>
                   </button>${spinner}`;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // EVIDENCE: upload to server
    // ──────────────────────────────────────────────────────────────────
    async function opsUploadEvidencia(key, opId, file) {
        const formData = new FormData();
        formData.append('id_operacion', opId);
        formData.append('slot', key);
        formData.append('archivo', file);

        try {
            const r    = await fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: formData });
            const data = await r.json();
            if (data.success) {
                const st = _evState[`${opId}_${key}`];
                if (st) { st.uploaded = true; st.src = data.url; }
                opsUpdateSlotUI(key, opId);
                // Agregar a bitácora en tiempo real
                if (data.bitacora_entry) {
                    const bit = document.getElementById('det-bitacora');
                    if (bit) {
                        const { nombre_usuario, accion, fecha_alta } = data.bitacora_entry;
                        const html = `<div class="ops-bitacora-entry">
                            <span class="bit-name">${opsEsc((nombre_usuario||'').toUpperCase())}</span>
                            <span class="bit-sep" style="color:#334155;margin:0 .4rem;">----</span>
                            <span class="bit-accion">${opsEsc((accion||'').toUpperCase())}</span>
                            <span class="bit-sep" style="color:#334155;margin:0 .4rem;">----</span>
                            <span class="bit-fecha">${opsEsc(fecha_alta||'')}</span>
                        </div>`;
                        bit.insertAdjacentHTML('afterbegin', html);
                        bit.querySelectorAll('.ops-bitacora-empty').forEach(el => el.remove());
                    }
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Error al subir', text: data.message, confirmButtonColor: '#6366f1' });
                delete _evState[`${opId}_${key}`];
                opsResetSlotUI(key);
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: err.message, confirmButtonColor: '#6366f1' });
            delete _evState[`${opId}_${key}`];
            opsResetSlotUI(key);
        }
    }

    function opsResetSlotUI(key) {
        const slotEl = document.getElementById('slot-' + key);
        if (!slotEl) return;
        const allSlots = EV_SECTIONS.flatMap(s => s.slots);
        const slotDef  = allSlots.find(s => s.key === key);
        const docDef   = EV_DOCS.find(d => d.slotKey === key);
        if (slotDef) {
            slotEl.classList.remove('has-file', 'uploading');
            slotEl.setAttribute('onclick', `opsSlotTrigger('${key}')`);
            slotEl.innerHTML = `<i class="fa-solid ${slotDef.icon} slot-icon-ph"></i>
                                <div class="slot-lbl">${opsEsc(slotDef.label)}</div>
                                <button class="ops-slot-btn ops-slot-btn-add">+</button>`;
        } else if (docDef) {
            slotEl.classList.remove('has-file', 'uploading');
            slotEl.setAttribute('onclick', `opsSlotTrigger('${key}')`);
            slotEl.innerHTML = `<i class="fa-solid fa-cloud-arrow-up" style="font-size:1.6rem;color:#94a3b8;"></i>
                                <div class="slot-sublbl">${opsEsc(docDef.slotLabel)}</div>
                                <button class="ops-slot-btn ops-slot-btn-add" style="top:8px;right:8px;pointer-events:none;">+</button>`;
        }
    }

    function opsDatoPar(label, value) {
        return `<div class="mb-1" style="font-size:.8125rem;">
            <span class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.4px;">${opsEsc(label)}: </span>
            <span class="fw-semibold">${opsEsc(String(value))}</span>
        </div>`;
    }

    function opsRenderObservaciones(obs) {
        if (!obs.length) return '<p class="text-muted small mb-0">Sin observaciones.</p>';
        return obs.map(o => `
            <div class="d-flex gap-2 mb-2 align-items-start" style="font-size:.8125rem;">
                <i class="fa-regular fa-comment mt-1" style="color:var(--ops-green);flex-shrink:0;"></i>
                <div>
                    <div>${opsEsc(o.texto)}</div>
                    <div class="text-muted" style="font-size:.68rem;">${opsEsc(o.etapa)} · ${opsEsc(o.fecha)}</div>
                </div>
            </div>`).join('');
    }

    function opsRenderBitacora(entries) {
        if (!entries || !entries.length) {
            return '<div class="ops-bitacora-empty">Sin registros aún.</div>';
        }
        return entries.map(e => {
            const nombre = opsEsc((e.nombre_usuario || '').toUpperCase());
            const accion = opsEsc((e.accion || '').toUpperCase());
            const fecha  = opsEsc(e.fecha_alta || '');
            return `<div class="ops-bitacora-entry">
                <span class="bit-name">${nombre}</span>
                <span class="bit-sep" style="color:#334155;margin:0 .4rem;">----</span>
                <span class="bit-accion">${accion}</span>
                <span class="bit-sep" style="color:#334155;margin:0 .4rem;">----</span>
                <span class="bit-fecha">${fecha}</span>
            </div>`;
        }).join('');
    }

    function opsRenderHistorial(hist) {
        if (!hist.length) return '<p class="text-muted small mb-0">Sin historial.</p>';
        return hist.map(h => `
            <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.78rem;">
                <i class="fa-solid fa-circle-dot" style="color:var(--ops-green);font-size:.6rem;flex-shrink:0;"></i>
                <span class="text-muted">${opsEsc(h.fecha)}</span>
                <span>${opsEsc(h.estatus_anterior || 'Inicio')} → <strong>${opsEsc(h.estatus_nuevo)}</strong></span>
            </div>`).join('');
    }

    // ──────────────────────────────────────────────────────────────────
    // MOVER ETAPA
    // ──────────────────────────────────────────────────────────────────
    function opsMoverEstatus(id, nuevoEstatus) {
        Swal.fire({
            icon: 'question',
            title: '¿Mover operación?',
            html: `Se moverá a la etapa <strong>${nuevoEstatus}</strong>.`,
            showCancelButton: true,
            confirmButtonText: 'Mover',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#6366f1',
        }).then(res => {
            if (!res.isConfirmed) return;

            Swal.fire({ title: 'Moviendo…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch('/MotosAdjudicadas/cambiarEstatus', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, estatus: nuevoEstatus }),
            })
            .then(r => r.json())
            .then(data => {
                Swal.close();
                if (!data.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#6366f1' });
                    return;
                }
                bootstrap.Modal.getInstance(document.getElementById('modalDetalleOperacion'))?.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Etapa actualizada',
                    text: `Movido a "${nuevoEstatus}"`,
                    timer: 1800, showConfirmButton: false, timerProgressBar: true,
                });
                opsCargarPipeline();
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#6366f1' });
            });
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // REGRESAR ETAPA
    // ──────────────────────────────────────────────────────────────────
    function opsRegresarEstatus(id, etapaPrevia, estatusActual, areaActual) {
        // Fix: Bootstrap 5 traps focus with a 'focusin' listener that blocks SweetAlert2 inputs.
        // We intercept that event while the Swal is open and stop it from firing.
        const _swalFocusFix = (e) => { if (e.target.closest?.('.swal2-container')) e.stopImmediatePropagation(); };
        document.addEventListener('focusin', _swalFocusFix, true);

        Swal.fire({
            icon: 'warning',
            title: 'Regresar a etapa anterior',
            html: `<p class="mb-2" style="font-size:.88rem;">La operación regresará de <strong>${estatusActual}</strong> a <strong>${etapaPrevia}</strong>.</p>
                   <p class="mb-1" style="font-size:.8rem;color:#64748b;">Especifica el motivo del regreso:</p>`,
            input: 'textarea',
            inputPlaceholder: 'Ej. Documentación incompleta, requiere revisión adicional…',
            inputAttributes: { maxlength: 500, rows: 3, style: 'font-size:.85rem;' },
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-arrow-left me-1"></i>Regresar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f59e0b',
            preConfirm: (motivo) => {
                if (!motivo || !motivo.trim()) {
                    Swal.showValidationMessage('Debes especificar un motivo para el regreso.');
                    return false;
                }
                return motivo.trim();
            }
        }).then(res => {
            document.removeEventListener('focusin', _swalFocusFix, true);
            if (!res.isConfirmed) return;
            const motivo = res.value;

            Swal.fire({ title: 'Regresando etapa…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch('/MotosAdjudicadas/cambiarEstatus', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, estatus: etapaPrevia }),
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#6366f1' });
                    return Promise.reject('api-error');
                }
                return fetch('/MotosAdjudicadas/agregarObservacion', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_operacion: id,
                        etapa: estatusActual,
                        area: areaActual || '',
                        texto: `↩ REGRESO A "${etapaPrevia}": ${motivo}`,
                    }),
                });
            })
            .then(r => r ? r.json() : null)
            .then(() => {
                Swal.close();
                bootstrap.Modal.getInstance(document.getElementById('modalDetalleOperacion'))?.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Etapa regresada',
                    text: `Movido de regreso a "${etapaPrevia}"`,
                    timer: 1800, showConfirmButton: false, timerProgressBar: true,
                });
                opsCargarPipeline();
            })
            .catch(err => {
                if (err === 'api-error') return;
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: err.message, confirmButtonColor: '#6366f1' });
            });
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // AGREGAR OBSERVACIÓN
    // ──────────────────────────────────────────────────────────────────
    function opsAgregarObservacion(idOperacion, etapa, area) {
        const texto = (document.getElementById('det-obs-input')?.value || '').trim();
        if (!texto) return;

        fetch('/MotosAdjudicadas/agregarObservacion', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_operacion: idOperacion, etapa, area, texto }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#6366f1' });
                return;
            }
            // Insertar nueva observación al final
            const lista = document.getElementById('det-obs-list');
            if (lista) {
                lista.querySelectorAll('p.text-muted').forEach(el => el.remove());
                const nueva = `<div class="d-flex gap-2 mb-2 align-items-start" style="font-size:.8125rem;">
                    <i class="fa-regular fa-comment mt-1" style="color:var(--ops-green);flex-shrink:0;"></i>
                    <div>
                        <div>${opsEsc(texto)}</div>
                        <div class="text-muted" style="font-size:.68rem;">${opsEsc(etapa)} · ${opsEsc(data.fecha || '')}</div>
                    </div>
                </div>`;
                lista.insertAdjacentHTML('beforeend', nueva);
                lista.scrollTop = lista.scrollHeight;
            }
            // Insertar en bitácora en tiempo real
            const bit = document.getElementById('det-bitacora');
            if (bit && data.bitacora_entry) {
                const { nombre_usuario, accion, fecha_alta } = data.bitacora_entry;
                const html = `<div class="ops-bitacora-entry">
                    <span class="bit-name">${opsEsc((nombre_usuario||'').toUpperCase())}</span>
                    <span class="bit-sep" style="color:#334155;margin:0 .4rem;">----</span>
                    <span class="bit-accion">${opsEsc((accion||'').toUpperCase())}</span>
                    <span class="bit-sep" style="color:#334155;margin:0 .4rem;">----</span>
                    <span class="bit-fecha">${opsEsc(fecha_alta||'')}</span>
                </div>`;
                bit.insertAdjacentHTML('afterbegin', html);
                bit.querySelectorAll('.ops-bitacora-empty').forEach(el => el.remove());
            }
            document.getElementById('det-obs-input').value = '';
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#6366f1' });
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // ELIMINAR OPERACIÓN
    // ──────────────────────────────────────────────────────────────────
    function opsEliminar(id) {
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar operación?',
            text: 'Esta acción no se puede deshacer. Se eliminarán evidencias, observaciones e historial.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
        }).then(res => {
            if (!res.isConfirmed) return;

            Swal.fire({ title: 'Eliminando…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch('/MotosAdjudicadas/eliminarOperacion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            })
            .then(r => r.json())
            .then(data => {
                Swal.close();
                if (!data.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#6366f1' });
                    return;
                }
                bootstrap.Modal.getInstance(document.getElementById('modalDetalleOperacion'))?.hide();
                Swal.fire({
                    icon: 'success', title: 'Operación eliminada',
                    timer: 1800, showConfirmButton: false, timerProgressBar: true,
                });
                opsCargarPipeline();
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#6366f1' });
            });
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────
    function opsEsc(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function opsEscJS(str) {
        return String(str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function opsSlug(str) {
        return str.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    }

    function opsFormatMXN(num) {
        return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(num);
    }

    // ──────────────────────────────────────────────────────────────────
    // EXPONER GLOBALES
    // ──────────────────────────────────────────────────────────────────
    window.opsCargarPipeline     = opsCargarPipeline;
    window.opsAbrirDetalle       = opsAbrirDetalle;
    window.opsMoverEstatus       = opsMoverEstatus;
    window.opsRegresarEstatus    = opsRegresarEstatus;
    window.opsAgregarObservacion = opsAgregarObservacion;
    window.opsEliminar           = opsEliminar;
    // Evidence
    window.opsSlotTrigger        = opsSlotTrigger;
    window.opsSlotView           = opsSlotView;
    window.opsSlotChange         = opsSlotChange;

    // INIT al cargar DOM
    document.addEventListener('DOMContentLoaded', opsPipelineInit);
    // Por si ya cargó (AJAX-rendered views)
    if (document.readyState !== 'loading') opsPipelineInit();

})();
</script>
