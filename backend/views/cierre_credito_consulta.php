<style>
/* ══════════════════════════════════════════
   CIERRE DE CRÉDITO — ESTILOS GLOBALES
══════════════════════════════════════════ */
.cc-header-gradient {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.cc-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.cc-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.85; color: #fff; }
.cc-header-gradient i  { color: #fff; }

/* ── Pestañas ── */
.cc-nav-tabs .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.6rem 1.4rem;
}
.cc-nav-tabs .nav-link.active {
    color: #1d4ed8;
    border-bottom-color: #fff !important;
}
.cc-nav-tabs .nav-link:hover:not(.active) {
    color: #1d4ed8;
    background: #eff6ff;
}

/* ── Tabla (En Proceso) ── */
.cc-table th {
    background: #f1f5f9;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    font-weight: 700;
}
.cc-table td { font-size: 0.88rem; vertical-align: middle; }

/* ── Badge estatus ── */
.badge-en-proceso       { background: #fef08a; color: #713f12; font-weight: 700; }
.badge-env-finalizado   { background: #bbf7d0; color: #14532d; font-weight: 700; }

/* ─────────────────────────────────────────
   CARDS DE CONVENIOS SALDADOS
───────────────────────────────────────── */
.cc-conv-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.cc-conv-card:hover { box-shadow: 0 4px 18px rgba(30,58,95,.12); }

/* Cabecera de la card */
.cc-conv-card-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.cc-conv-card-header .cc-credito-id {
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
.cc-conv-card-header .cc-credito-id small {
    font-weight: 400;
    font-size: .75rem;
    opacity: .8;
    margin-left: .35rem;
}

/* Barra de progreso dentro del header */
.cc-progress-wrap {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex: 1;
    max-width: 340px;
}
.cc-progress-wrap .cc-prog-label {
    color: rgba(255,255,255,.7);
    font-size: .72rem;
    white-space: nowrap;
    min-width: 10px;
    text-align: center;
}
.cc-progress-wrap .progress {
    flex: 1;
    height: 8px;
    border-radius: 20px;
    background: rgba(255,255,255,.25);
}
.cc-progress-wrap .progress-bar {
    background: #4ade80;
    border-radius: 20px;
    transition: width .6s ease;
}

/* Cuerpo de la card */
.cc-conv-card-body {
    padding: 1.1rem 1.25rem;
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

/* Columna derecha: detalles */
.cc-conv-details { flex: 1; min-width: 0; }
.cc-conv-details .cc-detail-row {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    margin-bottom: .45rem;
    font-size: .875rem;
}
.cc-conv-details .cc-detail-row .cc-lbl {
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 130px;
}
.cc-conv-details .cc-detail-row .cc-val {
    color: #1e293b;
    font-weight: 500;
}

/* Resumen de aplicación incrustado */
.cc-resumen-aplicacion {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: .6rem .9rem;
    margin: .5rem 0 .6rem 0;
    font-size: .82rem;
}
.cc-resumen-aplicacion .cc-res-title {
    font-weight: 700;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #1d4ed8;
    margin-bottom: .35rem;
}
.cc-resumen-aplicacion .cc-res-row {
    display: flex;
    justify-content: space-between;
    color: #475569;
    padding: .1rem 0;
}
.cc-resumen-aplicacion .cc-res-row.total {
    border-top: 1px solid #e2e8f0;
    margin-top: .25rem;
    padding-top: .3rem;
    font-weight: 700;
    color: #1e293b;
}

/* Validación — oculta visualmente en frontend */
.cc-validacion-box {
    display: none !important;
}

/* Footer de la card con botón confirmar */
.cc-conv-card-footer {
    border-top: 1px solid #e2e8f0;
    padding: .75rem 1.25rem;
    display: flex;
    justify-content: flex-end;
    background: #f8fafc;
}
.cc-btn-confirmar {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .85rem;
    padding: .45rem 1.4rem;
    border-radius: 2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s, transform .15s;
}
.cc-btn-confirmar:hover { opacity: .9; transform: translateY(-1px); }
.cc-btn-convenio {
    background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .85rem;
    padding: .45rem 1.4rem;
    border-radius: 2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s, transform .15s;
    text-decoration: none;
}
.cc-btn-convenio:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
.cc-btn-confirmar:active { transform: translateY(0); }
.cc-btn-confirmar:disabled { opacity: .5; cursor: not-allowed; }

/* ── Dark mode ── */
body.dark-mode .cc-nav-tabs .nav-link { color: #94a3b8; }
body.dark-mode .cc-nav-tabs .nav-link.active { color: #60a5fa; border-bottom-color: #1e293b !important; }
body.dark-mode .cc-nav-tabs .nav-link:hover:not(.active) { background: #1e3a5f; color: #60a5fa; }
body.dark-mode .cc-table th { background: #1e293b; color: #94a3b8; }
body.dark-mode .cc-conv-card { background: #1e293b; border-color: #334155; }
body.dark-mode .cc-conv-details .cc-detail-row .cc-val { color: #e2e8f0; }
body.dark-mode .cc-resumen-aplicacion { background: #0f172a; border-color: #334155; }
body.dark-mode .cc-conv-card-footer { background: #0f172a; border-color: #334155; }
body.dark-mode .cc-btn-confirmar {
    background: linear-gradient(135deg, #065f46 0%, #059669 100%);
    color: #d1fae5;
    border: 1px solid #059669;
    box-shadow: 0 0 0 1px rgba(16,185,129,.35);
}
body.dark-mode .cc-btn-confirmar:hover { background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #fff; }

/* ── Checklist de documentos (tab En Proceso) ── */
.cc-doccheck-wrap {
    background: #f8fafc;
    border-radius: .5rem;
    padding: .5rem .75rem;
    border: 1px solid #e2e8f0;
}
.cc-doccheck-title {
    font-size: .72rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: .4rem;
}
.cc-doccheck-items { display: flex; flex-wrap: wrap; gap: .4rem; }
.cc-doc-ok, .cc-doc-missing, .cc-doc-partial {
    display: inline-flex;
    align-items: center;
    font-size: .75rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}
.cc-doc-ok      { background: #dcfce7; color: #15803d; }
.cc-doc-missing { background: #fee2e2; color: #b91c1c; }
.cc-doc-partial { background: #fef9c3; color: #854d0e; }
body.dark-mode .cc-doccheck-wrap  { background: #0f172a; border-color: #334155; }
body.dark-mode .cc-doccheck-title { color: #94a3b8; }
body.dark-mode .cc-doc-ok      { background: rgba(21,128,61,.2);  color: #4ade80; }
body.dark-mode .cc-doc-missing { background: rgba(185,28,28,.2); color: #f87171; }
body.dark-mode .cc-doc-partial { background: rgba(133,77,14,.2); color: #fbbf24; }

/* ── Filtros de Validación de Cierre ── */
.cc-filtros-bar {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-bottom: .75rem;
    flex-wrap: wrap;
}
.cc-filtros-btn-toggle {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .35rem .8rem;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: .4rem;
    font-size: .82rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
    white-space: nowrap;
}
.cc-filtros-btn-toggle:hover { background: #f1f5f9; border-color: #94a3b8; }
.cc-filtros-btn-toggle.open  { border-color: #3b82f6; color: #1d4ed8; background: #eff6ff; }
.cc-filtros-chevron { font-size: .62rem; transition: transform .2s ease; }
.cc-filtros-btn-toggle.open .cc-filtros-chevron { transform: rotate(180deg); }
.cc-filtros-opciones {
    display: flex;
    gap: .4rem;
    flex-wrap: wrap;
    animation: ccFiltrosSlide .15s ease;
}
@keyframes ccFiltrosSlide {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}
/* ── Animaciones En Proceso detail panel ── */
@keyframes epDetailIn {
    from { opacity: 0; transform: translateX(18px) scale(.98); }
    to   { opacity: 1; transform: translateX(0)  scale(1); }
}
@keyframes epDetailOut {
    from { opacity: 1; transform: translateX(0)  scale(1); }
    to   { opacity: 0; transform: translateX(18px) scale(.98); }
}
@keyframes epCardDrop {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ep-detail-enter  { animation: epDetailIn  .25s cubic-bezier(.4,0,.2,1) both; }
.ep-detail-leave  { animation: epDetailOut .2s  cubic-bezier(.4,0,.2,1) both; }
.ep-card-drop     { animation: epCardDrop  .22s cubic-bezier(.4,0,.2,1) both; }
/* ── Selector de vista En Proceso ── */
.ep-view-toolbar  { display:flex; align-items:center; gap:.35rem; margin-bottom:.85rem; }
.ep-view-btn {
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.28rem .65rem; border:1px solid #e2e8f0; border-radius:.4rem;
    background:#f8fafc; color:#64748b; font-size:.78rem; font-weight:600;
    cursor:pointer; transition: background .12s, border-color .12s, color .12s;
    white-space:nowrap;
}
.ep-view-btn:hover  { background:#f1f5f9; border-color:#94a3b8; }
.ep-view-btn.active { background:#dbeafe; border-color:#3b82f6; color:#1d4ed8; }
/* ── Lista compacta (vista lista) ── */
.ep-list-row {
    display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
    background:#fff; border:1px solid #e2e8f0; border-radius:.6rem;
    padding:.6rem 1rem; transition: box-shadow .15s;
}
.ep-list-row:hover { box-shadow: 0 2px 10px rgba(30,58,95,.09); }
.ep-lr-info  { display:flex; flex-direction:column; gap:.05rem; min-width:0; flex:1 1 200px; }
.ep-lr-id    { font-weight:700; font-size:.9rem; color:#1e3a5f; letter-spacing:.2px; }
.ep-lr-name  { font-size:.8rem; color:#374151; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ep-lr-prod  { font-size:.73rem; color:#94a3b8; }
.ep-lr-meta  { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; flex-shrink:0; }
.ep-lr-total { font-weight:700; font-size:.83rem; color:#059669; white-space:nowrap; }
.ep-lr-actions { display:flex; align-items:center; gap:.35rem; flex-shrink:0; flex-wrap:wrap; }
.cc-filtro-opcion, .cc-filtro-hist-opcion {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .3rem .75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: .4rem;
    font-size: .8rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: background .12s, border-color .12s, color .12s;
}
.cc-filtro-opcion:hover, .cc-filtro-hist-opcion:hover { background: #f1f5f9; border-color: #94a3b8; }
.cc-filtro-opcion.active { background: #dbeafe; border-color: #3b82f6; color: #1d4ed8; }
.cc-filtro-opcion[data-filtro="con_docs"].active   { background: #dcfce7; border-color: #16a34a; color: #15803d; }
.cc-filtro-opcion[data-filtro="devueltos"].active  { background: #fef9c3; border-color: #ca8a04; color: #854d0e; }
.cc-filtro-opcion[data-filtro="despachos"].active  { background: rgba(30,58,138,.1); border-color: #1e3a8a; color: #1e3a8a; }
.cc-filtro-opcion[data-filtro="call_center"].active { background: rgba(22,163,74,.1); border-color: #16a34a; color: #15803d; }
/* Filtros Convenios tab */
.cc-filtro-conv[data-filtro-conv="con_docs"].active    { background: #dcfce7; border-color: #16a34a; color: #15803d; }
.cc-filtro-conv[data-filtro-conv="activos"].active     { background: #dbeafe; border-color: #3b82f6; color: #1d4ed8; }
.cc-filtro-conv[data-filtro-conv="cancelados"].active  { background: #fee2e2; border-color: #dc2626; color: #991b1b; }
body.dark-mode .cc-filtro-conv { background: #1e293b; border-color: #334155; color: #94a3b8; }
body.dark-mode .cc-filtro-conv:hover { background: #334155; border-color: #475569; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="con_docs"].active  { background: rgba(21,128,61,.2); border-color: #16a34a; color: #4ade80; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="activos"].active   { background: rgba(59,130,246,.18); border-color: #3b82f6; color: #93c5fd; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="cancelados"].active{ background: rgba(220,38,38,.2); border-color: #dc2626; color: #f87171; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="todos"].active     { background: rgba(59,130,246,.18); border-color: #3b82f6; color: #93c5fd; }
/* Scroll-to-top button */
.cc-scroll-top-btn {
    position: sticky; bottom: 1rem; float: right; margin-right: .5rem; z-index: 10;
    width: 38px; height: 38px; border-radius: 50%;
    background: #3b82f6; color: #fff; border: none;
    box-shadow: 0 2px 8px rgba(59,130,246,.35);
    cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center;
    transition: background .15s, box-shadow .15s;
}
.cc-scroll-top-btn:hover { background: #2563eb; box-shadow: 0 4px 12px rgba(37,99,235,.4); }
body.dark-mode .cc-scroll-top-btn { background: #1d4ed8; box-shadow: 0 2px 8px rgba(29,78,216,.4); }
body.dark-mode .cc-scroll-top-btn:hover { background: #2563eb; }
/* Filtros Convenios tab */
.cc-filtro-conv[data-filtro-conv="con_docs"].active    { background: #dcfce7; border-color: #16a34a; color: #15803d; }
.cc-filtro-conv[data-filtro-conv="activos"].active     { background: #dbeafe; border-color: #3b82f6; color: #1d4ed8; }
.cc-filtro-conv[data-filtro-conv="cancelados"].active  { background: #fee2e2; border-color: #dc2626; color: #991b1b; }
body.dark-mode .cc-filtro-conv { background: #1e293b; border-color: #334155; color: #94a3b8; }
body.dark-mode .cc-filtro-conv:hover { background: #334155; border-color: #475569; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="con_docs"].active  { background: rgba(21,128,61,.2); border-color: #16a34a; color: #4ade80; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="activos"].active   { background: rgba(59,130,246,.18); border-color: #3b82f6; color: #93c5fd; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="cancelados"].active{ background: rgba(220,38,38,.2); border-color: #dc2626; color: #f87171; }
body.dark-mode .cc-filtro-conv[data-filtro-conv="todos"].active     { background: rgba(59,130,246,.18); border-color: #3b82f6; color: #93c5fd; }
/* ── Descripción de sub-pestañas Cartera ── */
.cc-subtab-desc {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    background: #eff6ff;
    border-left: 3px solid #3b82f6;
    border-radius: 0 .4rem .4rem 0;
    padding: .55rem .9rem;
    font-size: .83rem;
    color: #1e40af;
    line-height: 1.45;
}
.cc-subtab-desc i { margin-top: .15rem; flex-shrink: 0; }
.cc-subtab-desc span { flex: 1; min-width: 0; }
.cc-subtab-desc--notif {
    background: #f0fdf4;
    border-left-color: #16a34a;
    color: #14532d;
}
body.dark-mode .cc-subtab-desc {
    background: rgba(59,130,246,.1);
    border-left-color: #3b82f6;
    color: #93c5fd;
}
body.dark-mode .cc-subtab-desc--notif {
    background: rgba(22,163,74,.1);
    border-left-color: #16a34a;
    color: #86efac;
}
/* Scroll-to-top button */
.cc-scroll-top-btn {
    position: sticky; bottom: 1rem; float: right; margin-right: .5rem; z-index: 10;
    width: 38px; height: 38px; border-radius: 50%;
    background: #3b82f6; color: #fff; border: none;
    box-shadow: 0 2px 8px rgba(59,130,246,.35);
    cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center;
    transition: background .15s, box-shadow .15s;
}
.cc-scroll-top-btn:hover { background: #2563eb; box-shadow: 0 4px 12px rgba(37,99,235,.4); }
body.dark-mode .cc-scroll-top-btn { background: #1d4ed8; box-shadow: 0 2px 8px rgba(29,78,216,.4); }
body.dark-mode .cc-scroll-top-btn:hover { background: #2563eb; }
/* ── Célula badges (Despachos / Call Center) ── */
.ep-celula-badge {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .15rem .55rem; border-radius: 100px;
    font-size: .69rem; font-weight: 700; letter-spacing: .2px; white-space: nowrap;
}
.ep-cel-desp { background: rgba(30,58,138,.1); color: #1e3a8a; border: 1px solid rgba(30,58,138,.3); }
.ep-cel-cc   { background: rgba(22,163,74,.1); color: #15803d; border: 1px solid rgba(22,163,74,.3); }
.cc-conv-card-header .ep-cel-desp { background: rgba(30,58,138,.35); color: #bfdbfe; border-color: rgba(30,58,138,.6); }
.cc-conv-card-header .ep-cel-cc   { background: rgba(22,163,74,.3); color: #bbf7d0; border-color: rgba(22,163,74,.55); }
body.dark-mode .ep-cel-desp { background: rgba(30,58,138,.25); color: #93c5fd; border-color: rgba(30,58,138,.5); }
body.dark-mode .ep-cel-cc   { background: rgba(22,163,74,.2); color: #4ade80; border-color: rgba(22,163,74,.45); }
/* ── Paginador de cards (Validación / En Proceso) ── */
.cc-pager-wrap { margin-top: .5rem; border-top: 1px solid #e2e8f0; padding: .5rem .25rem 0; }
.cc-pager-info { font-size: .83rem; color: #64748b; padding: .35rem .5rem; }
.cc-pager-len  { font-size: .83rem; color: #64748b; padding: .35rem .5rem;
                 display: flex; align-items: center; gap: .35rem; white-space: nowrap; }
.cc-pager-len select { padding: .2rem .4rem; border: 1px solid #cbd5e1; border-radius: .35rem;
                       background: #f8fafc; color: #334155; font-size: .8rem; cursor: pointer; }
body.dark-mode .cc-pager-wrap { border-color: #334155; }
body.dark-mode .cc-pager-info { color: #94a3b8; }
body.dark-mode .cc-pager-len  { color: #94a3b8; }
body.dark-mode .cc-pager-len select { background: #1e293b; border-color: #475569; color: #cbd5e1; }
.cc-filtro-count { font-size: .78rem; color: #64748b; }
body.dark-mode .cc-filtros-btn-toggle { background: #1e293b; border-color: #475569; color: #94a3b8; }
body.dark-mode .cc-filtros-btn-toggle:hover { background: #334155; border-color: #64748b; }
body.dark-mode .cc-filtros-btn-toggle.open  { border-color: #3b82f6; color: #60a5fa; background: rgba(59,130,246,.12); }
body.dark-mode .cc-filtro-opcion, body.dark-mode .cc-filtro-hist-opcion { background: #1e293b; border-color: #334155; color: #94a3b8; }
body.dark-mode .cc-filtro-opcion:hover, body.dark-mode .cc-filtro-hist-opcion:hover { background: #334155; border-color: #475569; }
body.dark-mode .cc-filtro-opcion.active { background: rgba(59,130,246,.18); border-color: #3b82f6; color: #93c5fd; }
body.dark-mode .cc-filtro-opcion[data-filtro="con_docs"].active  { background: rgba(21,128,61,.2); border-color: #16a34a; color: #4ade80; }
body.dark-mode .cc-filtro-opcion[data-filtro="devueltos"].active { background: rgba(133,77,14,.2); border-color: #ca8a04; color: #fbbf24; }
body.dark-mode .cc-filtro-opcion[data-filtro="despachos"].active  { background: rgba(30,58,138,.25); border-color: rgba(30,58,138,.5); color: #93c5fd; }
body.dark-mode .cc-filtro-opcion[data-filtro="call_center"].active { background: rgba(22,163,74,.2); border-color: rgba(22,163,74,.45); color: #4ade80; }
body.dark-mode .cc-filtro-count { color: #94a3b8; }

/* Historial filter option active colors */
.cc-filtro-hist-opcion.active                                          { background: #dbeafe; border-color: #3b82f6; color: #1d4ed8; }
.cc-filtro-hist-opcion[data-filtro-hist="enviado_cartera"].active      { background: #dcfce7; border-color: #16a34a; color: #15803d; }
.cc-filtro-hist-opcion[data-filtro-hist="en_cola,listo_envio"].active  { background: #fef9c3; border-color: #ca8a04; color: #854d0e; }
.cc-filtro-hist-opcion[data-filtro-hist="en_proceso"].active           { background: #dbeafe; border-color: #1d4ed8; color: #1e40af; }
.cc-filtro-hist-opcion[data-filtro-hist="descartado"].active           { background: #ffedd5; border-color: #ea580c; color: #9a3412; }
body.dark-mode .cc-filtro-hist-opcion.active                                          { background: rgba(59,130,246,.18);  border-color: #3b82f6; color: #93c5fd; }
body.dark-mode .cc-filtro-hist-opcion[data-filtro-hist="enviado_cartera"].active      { background: rgba(21,128,61,.2);    border-color: #16a34a; color: #4ade80; }
body.dark-mode .cc-filtro-hist-opcion[data-filtro-hist="en_cola,listo_envio"].active  { background: rgba(133,77,14,.2);    border-color: #ca8a04; color: #fbbf24; }
body.dark-mode .cc-filtro-hist-opcion[data-filtro-hist="en_proceso"].active           { background: rgba(30,64,175,.2);    border-color: #1d4ed8; color: #93c5fd; }
body.dark-mode .cc-filtro-hist-opcion[data-filtro-hist="descartado"].active           { background: rgba(154,52,18,.2);    border-color: #ea580c; color: #fb923c; }

/* ── Badge de porcentaje de descuento ── */
.cc-pct-badge {
    display: inline-block;
    background: #e0edff;
    color: #1d4ed8;
    font-size: .75rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    white-space: nowrap;
}
body.dark-mode .cc-pct-badge { background: rgba(59,130,246,.18); color: #93c5fd; }

/* ── Estatus badges (tabla convenios) dark mode ── */
body.dark-mode .cc-estatus-completado { background: rgba(20,83,45,.35)  !important; color: #4ade80 !important; }
body.dark-mode .cc-estatus-activo     { background: rgba(30,64,175,.35) !important; color: #93c5fd !important; }
body.dark-mode .cc-estatus-cancelado  { background: rgba(185,28,28,.35) !important; color: #fca5a5 !important; }
body.dark-mode .cc-estatus-default    { background: rgba(71,85,105,.3)  !important; color: #94a3b8 !important; }

/* ── Estatus badges Cartera ── */
.cc-estatus-enviado_cartera    { background: #e0e7ff; color: #3730a3; }
.cc-estatus-notificado_cartera { background: #e0f2fe; color: #0369a1; }
.cc-estatus-cerrado            { background: #1d4ed8; color: #fff; }
.cc-estatus-devuelto_cartera   { background: #b91c1c; color: #fff; }
body.dark-mode .cc-estatus-enviado_cartera    { background: rgba(55,48,163,.3)  !important; color: #a5b4fc !important; }
body.dark-mode .cc-estatus-notificado_cartera { background: rgba(3,105,161,.3) !important; color: #7dd3fc !important; }
body.dark-mode .cc-estatus-cerrado            { background: #1e3a8a !important; color: #bfdbfe !important; }
body.dark-mode .cc-estatus-devuelto_cartera   { background: rgba(185,28,28,.4) !important; color: #fca5a5 !important; }

/* ── Estatus badges (tab Historial/Movimientos) dark mode ── */
.cc-hist-enviado-ok      { background: #dcfce7; color: #15803d; font-size: .78rem; }
.cc-hist-enviado-warn    { background: #fef9c3; color: #854d0e; font-size: .78rem; }
.cc-hist-descartado      { background: #ffedd5; color: #9a3412; font-size: .78rem; }
.cc-hist-en-proceso      { background: #dbeafe; color: #1e40af; font-size: .78rem; }
.cc-hist-notificado      { background: #e0f2fe; color: #0369a1; font-size: .78rem; }
.cc-hist-cerrado         { background: #1d4ed8; color: #fff;    font-size: .78rem; }
.cc-hist-devuelto-cart   { background: #b91c1c; color: #fff;    font-size: .78rem; }
body.dark-mode .cc-hist-enviado-ok   { background: rgba(20,83,45,.35)   !important; color: #4ade80 !important; }
body.dark-mode .cc-hist-enviado-warn { background: rgba(133,77,14,.3)   !important; color: #fbbf24 !important; }
body.dark-mode .cc-hist-descartado   { background: rgba(154,52,18,.3)   !important; color: #fb923c !important; }
body.dark-mode .cc-hist-en-proceso   { background: rgba(30,64,175,.3)   !important; color: #93c5fd !important; }
body.dark-mode .cc-hist-notificado   { background: rgba(3,105,161,.3)   !important; color: #7dd3fc !important; }
body.dark-mode .cc-hist-cerrado      { background: #1e3a8a              !important; color: #bfdbfe !important; }
body.dark-mode .cc-hist-devuelto-cart{ background: rgba(185,28,28,.4)   !important; color: #fca5a5 !important; }

/* ── Trail de cartera (breadcrumb bajo badge en tab Convenios) ── */
.cc-trail { display:inline-flex; align-items:center; gap:3px; font-size:.65rem; font-weight:600; line-height:1; flex-wrap:wrap; }
.cc-trail-step { padding:1px 5px; border-radius:3px; background:#e2e8f0; color:#64748b; white-space:nowrap; }
.cc-trail-sep  { color:#94a3b8; font-size:.6rem; }
.cc-trail-step--active { font-weight:800; }
.cc-trail--devuelta .cc-trail-step--active { background:#fecaca; color:#991b1b; }
.cc-trail--notif    .cc-trail-step--active { background:#bae6fd; color:#0c4a6e; }
.cc-trail--cerrado  .cc-trail-step--active { background:#bfdbfe; color:#1e3a8a; }
body.dark-mode .cc-trail-step { background:rgba(100,116,139,.25); color:#94a3b8; }
body.dark-mode .cc-trail--devuelta .cc-trail-step--active { background:rgba(185,28,28,.35); color:#fca5a5; }
body.dark-mode .cc-trail--notif    .cc-trail-step--active { background:rgba(3,105,161,.3);  color:#7dd3fc; }
body.dark-mode .cc-trail--cerrado  .cc-trail-step--active { background:rgba(30,64,175,.3);  color:#93c5fd; }

/* ── Badge de antigüedad en Validación de Cierre ── */
.cc-age-badge {
    display:inline-flex; align-items:center; gap:.28rem;
    padding:.18rem .52rem; border-radius:9999px;
    font-size:.68rem; font-weight:700; white-space:nowrap;
    border: 1px solid transparent;
}
.cc-age-badge.age-fresh   { background:#dcfce7; color:#166534; border-color:#86efac; }
.cc-age-badge.age-mins    { background:#dbeafe; color:#1e40af; border-color:#93c5fd; }
.cc-age-badge.age-warn    { background:#fef9c3; color:#713f12; border-color:#fde047; }
.cc-age-badge.age-orange  { background:#ffedd5; color:#7c2d12; border-color:#fdba74; }
.cc-age-badge.age-danger  { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
.cc-age-badge.age-critical{ background:#7f1d1d; color:#fecaca; border-color:#991b1b;
    animation: cc-pulse-red 1.4s ease-in-out infinite; }
@keyframes cc-pulse-red {
    0%,100% { box-shadow:0 0 0 0 rgba(185,28,28,.5); }
    50%      { box-shadow:0 0 0 5px rgba(185,28,28,0); }
}
body.dark-mode .cc-age-badge.age-fresh  { background:rgba(22,163,74,.2);  color:#86efac; border-color:rgba(134,239,172,.35); }
body.dark-mode .cc-age-badge.age-mins   { background:rgba(37,99,235,.2);  color:#93c5fd; border-color:rgba(147,197,253,.35); }
body.dark-mode .cc-age-badge.age-warn   { background:rgba(202,138,4,.2);  color:#fde047; border-color:rgba(253,224,71,.35); }
body.dark-mode .cc-age-badge.age-orange { background:rgba(194,65,12,.25); color:#fdba74; border-color:rgba(253,186,116,.35); }
body.dark-mode .cc-age-badge.age-danger { background:rgba(185,28,28,.25); color:#fca5a5; border-color:rgba(252,165,165,.35); }

/* ── Línea de tiempo 4 momentos ── */
.cc-timeline {
    display:flex; flex-direction:column; gap:0;
    margin-top:.6rem;
    padding:.5rem .65rem;
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    border:1px solid #e2e8f0; border-radius:.45rem;
    font-size:.74rem;
}
.cc-timeline-title {
    font-weight:700; color:#475569; font-size:.68rem;
    text-transform:uppercase; letter-spacing:.05em; margin-bottom:.35rem;
}
.cc-tl-row {
    display:flex; align-items:flex-start; gap:.5rem;
    padding:.22rem 0; position:relative;
}
.cc-tl-row:not(:last-child)::after {
    content:''; position:absolute; left:.45rem; top:1.4rem;
    width:1px; height:calc(100% - .1rem); background:#cbd5e1;
}
.cc-tl-dot {
    width:.85rem; height:.85rem; border-radius:50%; flex-shrink:0;
    margin-top:.12rem; z-index:1;
}
.cc-tl-dot.done    { background:#22c55e; }
.cc-tl-dot.pending { background:#e2e8f0; border:2px solid #94a3b8; }
.cc-tl-info { display:flex; flex-direction:column; gap:.05rem; }
.cc-tl-label { font-weight:600; color:#334155; }
.cc-tl-date  { color:#64748b; font-size:.7rem; }
body.dark-mode .cc-timeline { background:rgba(30,41,59,.5); border-color:rgba(71,85,105,.4); }
body.dark-mode .cc-timeline-title { color:#94a3b8; }
body.dark-mode .cc-tl-label { color:#cbd5e1; }
body.dark-mode .cc-tl-date  { color:#94a3b8; }
body.dark-mode .cc-tl-row:not(:last-child)::after { background:#334155; }
body.dark-mode .cc-tl-dot.pending { background:#1e293b; border-color:#475569; }

/* ── Banner "Enviado a validación" en tab En Proceso ── */
.ep-espera-banner {
    display:flex; align-items:center; gap:.6rem;
    margin:.45rem 0 .35rem;
    padding:.5rem .75rem; border-radius:.5rem;
    border-left:4px solid transparent;
    font-size:.78rem;
}
.ep-espera-icon { font-size:1.1rem; flex-shrink:0; }
.ep-espera-body { display:flex; flex-direction:column; gap:.05rem; flex:1; min-width:0; }
.ep-espera-label { font-weight:700; font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; opacity:.75; }
.ep-espera-date  { font-weight:600; font-size:.82rem; white-space:nowrap; }
.ep-espera-chip  {
    flex-shrink:0; padding:.18rem .55rem; border-radius:9999px;
    font-weight:700; font-size:.68rem; white-space:nowrap;
    border:1px solid transparent;
}
/* Azul — < 1 día */
.ep-wait-blue  { background:#eff6ff; border-color:#bfdbfe; color:#1e40af; }
.ep-wait-blue  .ep-espera-icon  { color:#3b82f6; }
.ep-wait-blue  .ep-espera-chip  { background:#dbeafe; border-color:#93c5fd; color:#1e40af; }
/* Amarillo — 1-2 días */
.ep-wait-yellow { background:#fefce8; border-color:#fde68a; color:#713f12; }
.ep-wait-yellow .ep-espera-icon { color:#ca8a04; }
.ep-wait-yellow .ep-espera-chip { background:#fef9c3; border-color:#fde047; color:#713f12; }
/* Naranja — 3-6 días */
.ep-wait-orange { background:#fff7ed; border-color:#fed7aa; color:#7c2d12; }
.ep-wait-orange .ep-espera-icon { color:#ea580c; }
.ep-wait-orange .ep-espera-chip { background:#ffedd5; border-color:#fdba74; color:#7c2d12; }
/* Rojo — 7+ días */
.ep-wait-red    { background:#fff1f2; border-color:#fecdd3; color:#881337;
    animation: ep-pulse-border 1.6s ease-in-out infinite; }
.ep-wait-red    .ep-espera-icon { color:#dc2626; }
.ep-wait-red    .ep-espera-chip { background:#fee2e2; border-color:#fca5a5; color:#991b1b; }
@keyframes ep-pulse-border {
    0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.3); }
    50%      { box-shadow: 0 0 0 4px rgba(220,38,38,0); }
}
/* Dark mode */
body.dark-mode .ep-wait-blue   { background:rgba(37,99,235,.12);  border-color:rgba(147,197,253,.25); color:#93c5fd; }
body.dark-mode .ep-wait-yellow { background:rgba(202,138,4,.12);  border-color:rgba(253,224,71,.25);  color:#fde047; }
body.dark-mode .ep-wait-orange { background:rgba(194,65,12,.15);  border-color:rgba(253,186,116,.25); color:#fdba74; }
body.dark-mode .ep-wait-red    { background:rgba(185,28,28,.18);  border-color:rgba(252,165,165,.25); color:#fca5a5; }
body.dark-mode .ep-espera-chip { background:rgba(0,0,0,.25) !important; }

/* ── Banner de descarte previo en cards ── */
.cc-descarte-banner { background: #fef3c7; border: 1px solid #fde68a; border-radius: .45rem; padding: .55rem .8rem; font-size: .78rem; color: #78350f; }
.cc-descarte-banner .cc-descarte-meta { color: #92400e; }
body.dark-mode .cc-descarte-banner { background: rgba(120,53,15,.25) !important; border-color: rgba(253,230,138,.25) !important; color: #fcd34d !important; }
body.dark-mode .cc-descarte-banner .cc-descarte-meta { color: #fbbf24 !important; }

/* ── Panel lateral derecho (Tab En Proceso) ── */
.cc-ep-wrapper {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    margin-bottom: 1.25rem;
}
.cc-side-panel {
    width: 0;
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    flex-shrink: 0;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: none;
    border-radius: 0 .75rem .75rem 0;
    box-shadow: 3px 2px 10px rgba(0,0,0,.06);
    transition: width .32s cubic-bezier(.4,0,.2,1), max-height .32s cubic-bezier(.4,0,.2,1), opacity .25s ease, padding .32s ease;
    padding: 0;
    display: flex;
    flex-direction: column;
}
.cc-side-panel.open {
    width: 540px;
    max-height: 80vh;
    overflow-y: auto;
    opacity: 1;
    padding: 1.1rem 1.25rem;
}
.cc-conv-card.cc-has-panel {
    border-radius: .75rem 0 0 .75rem;
    border-right-color: #bfdbfe;
}
.cc-side-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .85rem;
    padding-bottom: .6rem;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.cc-side-panel-header .cc-sp-title {
    font-weight: 700;
    font-size: .82rem;
    color: #1d4ed8;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.cc-side-panel-close {
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 0 .25rem;
    font-size: .9rem;
    line-height: 1;
    transition: color .15s;
}
.cc-side-panel-close:hover { color: #dc2626; }
body.dark-mode .cc-side-panel {
    background: #0f172a;
    border-color: #334155;
    box-shadow: 3px 2px 10px rgba(0,0,0,.25);
}
body.dark-mode .cc-side-panel-header { border-color: #334155; }
body.dark-mode .cc-side-panel-header .cc-sp-title { color: #60a5fa; }

/* Tabla de amortización dentro del acordeón */
.cc-amort-table { width:100%; border-collapse:collapse; font-size:.8rem; }
.cc-amort-table th {
    background:#e8f0fe; color:#1e40af; font-weight:700;
    font-size:.72rem; text-transform:uppercase; letter-spacing:.03em;
    padding:.4rem .6rem; text-align:left; border-bottom:2px solid #bfdbfe;
}
.cc-amort-table td { padding:.4rem .6rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.cc-amort-table tr:last-child td { border-bottom:none; }
.cc-amort-table tr.pagada td { background: rgba(220,252,231,.35); }
.cc-amort-table tr.pendiente td { background: rgba(254,242,242,.4); }
body.dark-mode .cc-amort-table th { background:#1e3a5f; color:#93c5fd; border-color:#1e40af; }
body.dark-mode .cc-amort-table td { border-color:#1e293b; }
body.dark-mode .cc-amort-table tr.pagada td  { background: rgba(21,128,61,.12); }
body.dark-mode .cc-amort-table tr.pendiente td { background: rgba(185,28,28,.1); }

/* Botón descartar rojo visible */
.cc-btn-descartar {
    background: #dc2626;
    border: none;
    color: #fff;
    border-radius: .4rem;
    padding: .3rem .75rem;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .35rem;
    transition: background .15s, transform .1s;
    white-space: nowrap;
    flex-shrink: 0;
}
.cc-btn-descartar:hover  { background: #b91c1c; transform: translateY(-1px); }
.cc-btn-descartar:active { transform: translateY(0); }

/* ── Panel de detalle (child row DataTables) ── */
/* Panel de detalle (child row DataTables) */
.cc-conv-detail-inner { padding: 1rem 1.25rem; max-height: 520px; overflow-y: auto; }

/* ── Controles DataTables Convenios ── */
#tablaConveniosTodos_wrapper .dataTables_length { padding-left: .75rem; }
#tablaConveniosTodos_wrapper .dataTables_filter { padding-right: .75rem; }
#tablaConveniosTodos_wrapper .dataTables_info   { padding-left: .75rem; }
#tablaConveniosTodos_wrapper .dataTables_paginate { padding-right: .75rem; }

/* ── Botones de vista En Proceso (dark mode) ── */
body.dark-mode .ep-view-btn          { background: #1e293b; border-color: #334155; color: #94a3b8; }
body.dark-mode .ep-view-btn:hover    { background: #334155; border-color: #475569; color: #cbd5e1; }
body.dark-mode .ep-view-btn.active   { background: rgba(59,130,246,.18); border-color: #3b82f6; color: #93c5fd; }
/* ── Lista compacta (dark mode) ── */
body.dark-mode .ep-list-row  { background: #1e293b; border-color: #334155; }
body.dark-mode .ep-lr-id     { color: #93c5fd; }
body.dark-mode .ep-lr-name   { color: #cbd5e1; }
body.dark-mode .ep-lr-prod   { color: #64748b; }
body.dark-mode .ep-lr-total  { color: #34d399; }
/* ── Bloque Datos crédito S2 ── */
.cc-s2-block {
    margin-top:.6rem; padding:.45rem .65rem;
    background: linear-gradient(135deg,#f0f9ff,#e0f2fe);
    border: 1px solid #bae6fd; border-radius:.45rem; font-size:.78rem;
}
.cc-s2-block-title {
    font-weight:700; color:#0369a1; margin-bottom:.3rem;
    font-size:.7rem; text-transform:uppercase; letter-spacing:.05em;
}
.cc-s2-label  { color: #64748b; }
.cc-s2-value  { color: #0f172a; }
.cc-s2-total  { color: #059669; }
.cc-s2-empty  { display:flex; align-items:center; gap:.4rem; color:#64748b; font-size:.76rem; }
.cc-s2-footer { margin-top:.35rem; font-size:.68rem; color:#94a3b8; text-align:right; }
body.dark-mode .cc-s2-block       { background: linear-gradient(135deg,rgba(3,105,161,.15),rgba(14,165,233,.08)); border-color: #1e4e6e; }
body.dark-mode .cc-s2-block-title { color: #38bdf8; }
body.dark-mode .cc-s2-label       { color: #94a3b8; }
body.dark-mode .cc-s2-value       { color: #e2e8f0; }
body.dark-mode .cc-s2-total       { color: #34d399; }
body.dark-mode .cc-s2-empty       { color: #64748b; }
/* ── Dark mode: labels y filas resumen ── */
body.dark-mode .cc-conv-details .cc-detail-row .cc-lbl { color: #64748b; }
body.dark-mode .cc-resumen-aplicacion .cc-res-title   { color: #60a5fa; }
body.dark-mode .cc-resumen-aplicacion .cc-res-row     { color: #94a3b8; }
body.dark-mode .cc-resumen-aplicacion .cc-res-row.total { color: #e2e8f0; border-color: #334155; }
/* ── Badges estado docs (vista lista En Proceso) ── */
.cc-badge-listo     { background:#dcfce7; color:#15803d; font-size:.7rem; }
.cc-badge-docs-pend { background:#fef9c3; color:#854d0e; font-size:.7rem; }
body.dark-mode .cc-badge-listo     { background:rgba(21,128,61,.2);  color:#4ade80; }
body.dark-mode .cc-badge-docs-pend { background:rgba(133,77,14,.2); color:#fbbf24; }
/* ── Iconos docs inline (vista lista) ── */
.cc-doc-icon-ok      { font-size:.75rem; color:#16a34a; }
.cc-doc-icon-missing { font-size:.75rem; color:#dc2626; }
.cc-doc-icon-partial { font-size:.75rem; color:#d97706; }
.cc-doc-icon-none    { font-size:.75rem; color:#94a3b8; }
body.dark-mode .cc-doc-icon-ok      { color:#4ade80; }
body.dark-mode .cc-doc-icon-missing { color:#f87171; }
body.dark-mode .cc-doc-icon-partial { color:#fbbf24; }
/* ── Detalle cierre: valores coloreados y título sección ── */
.cc-det-danger       { font-size:1rem; font-weight:700; color:#dc2626; }
.cc-det-success      { font-size:1rem; font-weight:700; color:#16a34a; }
.cc-det-primary      { font-size:1rem; font-weight:700; color:#1d4ed8; }
.cc-det-secondary    { font-size:1rem; font-weight:700; color:#475569; }
.cc-det-section-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#1d4ed8; margin-bottom:.5rem; }
body.dark-mode .cc-det-danger        { color:#f87171; }
body.dark-mode .cc-det-success       { color:#34d399; }
body.dark-mode .cc-det-primary       { color:#60a5fa; }
body.dark-mode .cc-det-secondary     { color:#94a3b8; }
body.dark-mode .cc-det-section-title { color:#60a5fa; }
/* ── Panel de detalle En Proceso ── */
.cc-ep-detail-panel { background:#f8fafc; border:1px solid #e2e8f0; border-radius:.75rem; padding:1.1rem 1.25rem; height:100%; }
body.dark-mode .cc-ep-detail-panel { background:#0f172a; border-color:#334155; }
/* ── Vo.Bo validated banner ── */
.cc-vobo-ok-banner { display:flex; align-items:center; gap:.4rem; margin-top:.5rem; background:#dcfce7; border:1px solid #86efac; border-radius:.4rem; padding:.3rem .65rem; font-size:.78rem; color:#166534; }
body.dark-mode .cc-vobo-ok-banner { background:rgba(21,128,61,.15); border-color:#4ade80; color:#4ade80; }
/* ── Envío previo fallido banner ── */
.cc-envio-fallido-banner { display:flex; align-items:center; gap:.4rem; margin-top:.5rem; background:#fef9c3; border:1px solid #fde68a; border-radius:.4rem; padding:.3rem .65rem; font-size:.78rem; color:#854d0e; }
body.dark-mode .cc-envio-fallido-banner { background:rgba(133,77,14,.15); border-color:#fbbf24; color:#fbbf24; }
/* ── Texto registrado por (detail panel) ── */
.cc-det-registered-by { margin-bottom:.75rem; font-size:.8rem; color:#64748b; }
body.dark-mode .cc-det-registered-by { color:#94a3b8; }
/* ── Badge "NUEVO!" para convenios recién creados ── */
@keyframes cc-nuevo-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(16,185,129,.55); }
    70%  { box-shadow: 0 0 0 7px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
}
.cc-badge-nuevo {
    display:inline-flex; align-items:center; gap:.28rem;
    background:linear-gradient(135deg,#10b981,#059669);
    color:#fff; font-size:.65rem; font-weight:800; letter-spacing:.05em;
    padding:.18rem .52rem; border-radius:9999px; vertical-align:middle;
    animation: cc-nuevo-pulse 1.5s ease-in-out infinite;
    text-transform:uppercase; flex-shrink:0;
}
body.dark-mode .cc-badge-nuevo { background:linear-gradient(135deg,#059669,#047857); }
</style>

<!-- ══════════════════════════════════════
     ENCABEZADO
══════════════════════════════════════ -->
<div class="cc-header-gradient">
    <div class="d-flex align-items-center gap-3">
        <i class="fa-solid fa-file-circle-check fa-2x opacity-90"></i>
        <div>
            <h4>Cierre de Crédito</h4>
            <p>Seguimiento al proceso de cierre final de créditos.</p>
        </div>
    </div>
</div>

<?php
/* Permisos por pestaña (modulos_web 52–55, 59). Si la vista se carga sin el controlador, no muestra pestañas por seguridad. */
if (!isset($cc_perm_alguno)) {
    $cc_perm_convenios = false;
    $cc_perm_descargar_excel = false;
    $cc_perm_validacion = false;
    $cc_perm_en_proceso = false;
    $cc_perm_vobo = false;
    $cc_perm_historial = false;
    $cc_perm_cartera   = false;
    $cc_perm_peticiones = false;                                                                            
    $cc_perm_alguno = false;
    $cc_default_tab = null;
}
$ccActConv  = ($cc_default_tab === 'convenios');
$ccActVal   = ($cc_default_tab === 'validacion');
$ccActEp    = ($cc_default_tab === 'en_proceso');
$ccActVoBo  = ($cc_default_tab === 'vobo');
$ccActHist  = ($cc_default_tab === 'historial');
$ccActCart  = ($cc_default_tab === 'cartera');
$ccActPet   = ($cc_default_tab === 'peticiones');
$cc_perm_peticiones = $cc_perm_peticiones ?? false;
?>
<!-- ══════════════════════════════════════
     PESTAÑAS (visibles según permisos especiales modulos_web 52–55)
══════════════════════════════════════ -->
<?php if (empty($cc_perm_alguno)): ?>
<div class="alert alert-warning shadow-sm mb-3" role="alert">
    <i class="fa-solid fa-lock me-2"></i>
    <strong>Acceso restringido.</strong> No tiene permisos asignados para las pestañas de Cierre de crédito. Solicite a Capital Humano la habilitación correspondiente.
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body pb-0">
        <ul class="nav nav-tabs cc-nav-tabs border-0 mb-0" id="ccTabs" role="tablist">
                <?php if (!empty($cc_perm_convenios)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $ccActConv ? ' active' : '' ?>" id="tab-convenios-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-convenios"
                            type="button" role="tab"
                            aria-controls="tab-convenios" aria-selected="<?= $ccActConv ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-handshake me-1 text-primary"></i>Convenios Activos
                        <span class="badge bg-secondary ms-1" id="badge-convenios">0</span>
                    </button>
                </li>
                <?php endif; ?>
                <?php if (!empty($cc_perm_validacion)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $ccActVal ? ' active' : '' ?>" id="tab-env-finalizado-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-env-finalizado"
                            type="button" role="tab"
                            aria-controls="tab-env-finalizado" aria-selected="<?= $ccActVal ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-circle-check me-1 text-success"></i>Validacion de cierre
                        <span class="badge bg-success ms-1" id="badge-env-finalizado">0</span>
                    </button>
                </li>
                <?php endif; ?>
                <?php if (!empty($cc_perm_en_proceso)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $ccActEp ? ' active' : '' ?>" id="tab-en-proceso-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-en-proceso"
                            type="button" role="tab"
                            aria-controls="tab-en-proceso" aria-selected="<?= $ccActEp ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-hourglass-half me-1 text-warning"></i>En Proceso
                        <span class="badge bg-warning text-dark ms-1" id="badge-en-proceso">0</span>
                    </button>
                </li>
                <?php endif; ?>
                <?php if (!empty($cc_perm_vobo)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $ccActVoBo ? ' active' : '' ?>" id="tab-vobo-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-vobo"
                            type="button" role="tab"
                            aria-controls="tab-vobo" aria-selected="<?= $ccActVoBo ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-stamp me-1" style="color:#2563eb;"></i>Vo.Bo
                        <span class="badge ms-1" id="badge-vobo" style="background:#2563eb;color:#fff;">0</span>
                    </button>
                </li>
                <?php endif; ?>
                <?php if (!empty($cc_perm_historial)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $ccActHist ? ' active' : '' ?>" id="tab-historial-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-historial"
                            type="button" role="tab"
                            aria-controls="tab-historial" aria-selected="<?= $ccActHist ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-clock-rotate-left me-1 text-info"></i>Movimientos
                    </button>
                </li>
                <?php endif; ?>
                <?php if (!empty($cc_perm_cartera)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $ccActCart ? ' active' : '' ?>" id="tab-cartera-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-cartera"
                            type="button" role="tab"
                            aria-controls="tab-cartera" aria-selected="<?= $ccActCart ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-building-columns me-1" style="color:#0f5c8a;"></i>Cartera
                        <span class="badge ms-1" id="badge-cartera" style="background:#0f5c8a;color:#fff;">0</span>
                    </button>
                </li>
                <?php endif; ?>
                <?php if (!empty($cc_perm_peticiones)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?= $ccActPet ? ' active' : '' ?>" id="tab-peticiones-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-peticiones"
                            type="button" role="tab"
                            aria-controls="tab-peticiones" aria-selected="<?= $ccActPet ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-inbox me-1" style="color:#7c3aed;"></i>Peticiones
                        <span class="badge ms-1" id="badge-peticiones" style="background:#7c3aed;color:#fff;">0</span>
                    </button>
                </li>
                <?php endif; ?>
            </ul>
    </div>

<!-- ══════════════════════════════════════
     CONTENIDO DE PESTAÑAS
══════════════════════════════════════ -->
<div class="tab-content" id="ccTabContent">

    <!-- Barra de búsqueda general — fija dentro del contenido -->
    <div class="d-flex justify-content-end align-items-center pb-2 border-bottom mb-2">
        <div id="barraGeneral" class="dataTables_filter">
            <label style="display:flex;align-items:center;gap:.4rem;margin:0;font-size:.875rem;font-weight:400;color:#566a7f;">
                Buscar:
                <span style="position:relative;display:inline-flex;align-items:center;">
                    <input type="text" id="barraGeneral-input"
                           class="form-control form-control-sm"
                           placeholder=""
                           autocomplete="off"
                           style="padding-right:1.6rem;">
                    <button type="button" id="barraGeneral-limpiar"
                            title="Limpiar"
                            style="display:none;position:absolute;right:.35rem;background:none;border:none;padding:0;line-height:1;color:#a1acb8;cursor:pointer;font-size:.8rem;">
                        &#x2715;
                    </button>
                </span>
            </label>
        </div>
    </div>

    <?php if (!empty($cc_perm_convenios)): ?>
    <!-- ══ PESTAÑA 0: CONVENIOS (TODOS) ══ -->
    <div class="tab-pane fade<?= $ccActConv ? ' show active' : '' ?>" id="tab-convenios" role="tabpanel">
        <?php if (!empty($cc_perm_descargar_excel)): ?>
        <div class="d-flex justify-content-end align-items-center mb-2">
            <a href="/CierreCredito/descargarReporteConveniosActivos"
               class="btn btn-sm btn-outline-success"
               title="Descargar reporte de convenios activos en Excel">
                <i class="fa-solid fa-file-excel me-1"></i>Descargar Reporte
            </a>
        </div>
        <?php endif; ?>
        <!-- Filtros Convenios -->
        <div id="cc-filtros-conv" class="cc-filtros-bar d-none">
            <button id="cc-filtros-conv-toggle" class="cc-filtros-btn-toggle" type="button">
                <i class="fa-solid fa-filter"></i>Filtros
                <i class="fa-solid fa-chevron-down cc-filtros-chevron"></i>
            </button>
            <div id="cc-filtros-conv-opciones" class="cc-filtros-opciones" style="display:none;">
                <button class="cc-filtro-opcion cc-filtro-conv active" data-filtro-conv="todos" type="button">
                    <i class="fa-solid fa-list"></i>Todos
                </button>
                <button class="cc-filtro-opcion cc-filtro-conv" data-filtro-conv="con_docs" type="button">
                    <i class="fa-solid fa-paperclip"></i>Con documentos
                </button>
                <button class="cc-filtro-opcion cc-filtro-conv" data-filtro-conv="activos" type="button">
                    <i class="fa-solid fa-circle-check"></i>Activos
                </button>
                <button class="cc-filtro-opcion cc-filtro-conv" data-filtro-conv="cancelados" type="button">
                    <i class="fa-solid fa-ban"></i>Cancelados
                </button>
            </div>
            <span id="cc-filtro-conv-count" class="cc-filtro-count"></span>
        </div>
        <div id="loader-convenios" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando convenios...
        </div>
        <div id="wrap-convenios" class="d-none">
            <div class="card-datatable table-responsive">
                <table id="tablaConveniosTodos" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th></th><!-- control responsive -->
                            <th>Crédito / Cliente</th>
                            <th>Producto</th>
                            <th>Total</th>
                            <th>Fecha acuerdo</th>
                            <th>Avance</th>
                            <th>Estatus</th>
                            <th>Docs</th>
                            <th>Acciones</th>
                            <th></th><!-- id oculto para orden -->
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div id="empty-convenios" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin convenios registrados.
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($cc_perm_vobo)): ?>
    <!-- ══ PESTAÑA VO.BO ══ -->
    <div class="tab-pane fade<?= $ccActVoBo ? ' show active' : '' ?>" id="tab-vobo" role="tabpanel">
        <div id="loader-vobo" class="text-center py-5 text-muted<?= $ccActVoBo ? '' : ' d-none' ?>">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando envíos a dirección de cobranza...
        </div>
        <div id="wrap-vobo" class="d-none">
            <div class="card-datatable table-responsive">
                <table id="tablaVoBo" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>Crédito</th>
                            <th>Cliente</th>
                            <th>Comentario</th>
                            <th>Archivo</th>
                            <th>Enviado por</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div id="empty-vobo" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin registros pendientes en Vo.Bo.
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($cc_perm_validacion)): ?>
    <!-- ══ PESTAÑA 1: ENVIADOS FINALIZADOS ══ -->
    <div class="tab-pane fade<?= $ccActVal ? ' show active' : '' ?>" id="tab-env-finalizado" role="tabpanel">

        <!-- Botón Filtros con opciones desplegables -->
        <div id="cc-filtros-ef" class="cc-filtros-bar d-none">
            <button id="cc-filtros-toggle" class="cc-filtros-btn-toggle" type="button">
                <i class="fa-solid fa-filter"></i>Filtros
                <i class="fa-solid fa-chevron-down cc-filtros-chevron"></i>
            </button>
            <div id="cc-filtros-opciones" class="cc-filtros-opciones" style="display:none;">
                <button class="cc-filtro-opcion active" data-filtro="todos" type="button">
                    <i class="fa-solid fa-list"></i>Todos
                </button>
                <button class="cc-filtro-opcion" data-filtro="con_docs" type="button">
                    <i class="fa-solid fa-paperclip"></i>Con documentos
                </button>
                <button class="cc-filtro-opcion" data-filtro="devueltos" type="button">
                    <i class="fa-solid fa-rotate-left"></i>Devueltos
                </button>
                <?php if (!(isset($cc_celulas_permitidas) && $cc_celulas_permitidas !== null)): ?>
                <button class="cc-filtro-opcion" data-filtro="despachos" type="button">
                    <i class="fa-solid fa-file-lines"></i>Despachos
                </button>
                <button class="cc-filtro-opcion" data-filtro="call_center" type="button">
                    <i class="fa-solid fa-phone"></i>Call Center
                </button>
                <?php endif; ?>
            </div>
            <span id="cc-filtro-count" class="cc-filtro-count"></span>
        </div>

        <div id="loader-env-finalizado" class="text-center py-5 text-muted<?= $ccActVal ? '' : ' d-none' ?>">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando convenios...
        </div>
        <div id="wrap-env-finalizado" class="d-none">
            <!-- Las cards se inyectan aquí dinámicamente -->
        </div>
        <div id="pager-ef" class="cc-pager-wrap d-none"></div>
        <div id="empty-env-finalizado" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin convenios saldados por el momento.
        </div>
        <div id="empty-busqueda" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-search fa-2x mb-2 d-block opacity-50"></i>
            Sin resultados para la búsqueda.
        </div>
        <!-- Botón scroll to top -->
        <button type="button" class="cc-scroll-top-btn" id="cc-scroll-top-ef" title="Ir arriba">
            <i class="fa-solid fa-arrow-up"></i>
        </button>
    </div>
    <?php endif; ?>

    <?php if (!empty($cc_perm_en_proceso)): ?>
    <!-- ══ PESTAÑA 2: EN PROCESO ══ -->
    <div class="tab-pane fade<?= $ccActEp ? ' show active' : '' ?>" id="tab-en-proceso" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="loader-en-proceso" class="text-center py-5 text-muted<?= $ccActEp ? '' : ' d-none' ?>">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    Cargando registros...
                </div>
                <!-- Selector de vista -->
                <div id="ep-view-toolbar" class="ep-view-toolbar d-none">
                    <span style="font-size:.76rem;color:#94a3b8;font-weight:600;margin-right:.15rem;">Vista:</span>
                    <button class="ep-view-btn active" data-view="3" title="3 columnas">
                        <i class="fa-solid fa-table-cells-large"></i>3 columnas
                    </button>
                    <button class="ep-view-btn" data-view="2" title="2 columnas">
                        <i class="fa-solid fa-table-columns"></i>2 columnas
                    </button>
                    <button class="ep-view-btn" data-view="list" title="Lista compacta">
                        <i class="fa-solid fa-list"></i>Lista
                    </button>
                </div>
                <div id="wrap-ep-cards" class="d-none">
                    <!-- Cards renderizadas por JS -->
                </div>
                <div id="pager-ep" class="cc-pager-wrap d-none"></div>
                <div id="empty-en-proceso" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Sin registros en proceso de validación.
                </div>
                <div id="empty-busqueda-ep" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-search fa-2x mb-2 d-block opacity-50"></i>
                    Sin resultados para la búsqueda.
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($cc_perm_historial)): ?>
    <!-- ══ PESTAÑA 3: HISTORIAL ══ -->
    <div class="tab-pane fade<?= $ccActHist ? ' show active' : '' ?>" id="tab-historial" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">

                <!-- Filtros Movimientos -->
                <div id="cc-filtros-hist" class="cc-filtros-bar d-none" style="padding:.65rem .75rem;">
                    <button id="cc-filtros-hist-toggle" class="cc-filtros-btn-toggle" type="button">
                        <i class="fa-solid fa-filter"></i>Filtros
                        <i class="fa-solid fa-chevron-down cc-filtros-chevron"></i>
                    </button>
                    <div id="cc-filtros-hist-opciones" class="cc-filtros-opciones" style="display:none;">
                        <button class="cc-filtro-hist-opcion active" data-filtro-hist="todos" type="button">
                            <i class="fa-solid fa-list"></i>Todos
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="enviado_cartera" type="button">
                            <i class="fa-solid fa-circle-check"></i>Enviados
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="en_cola,listo_envio" type="button">
                            <i class="fa-solid fa-triangle-exclamation"></i>L&iacute;mite de env&iacute;o
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="en_proceso" type="button">
                            <i class="fa-solid fa-hourglass-half"></i>En proceso
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="envio_cobranza" type="button">
                            <i class="fa-solid fa-stamp"></i>En Vo.Bo
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="vo_bo_rechazado" type="button">
                            <i class="fa-solid fa-xmark"></i>Vo.Bo rechazado
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="descartado" type="button">
                            <i class="fa-solid fa-rotate-left"></i>Devueltos
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="notificado_cartera" type="button">
                            <i class="fa-solid fa-bell"></i>Notificado cartera
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="cerrado" type="button">
                            <i class="fa-solid fa-circle-check"></i>Cerrados
                        </button>
                        <button class="cc-filtro-hist-opcion" data-filtro-hist="devuelto_cartera" type="button">
                            <i class="fa-solid fa-rotate-left"></i>Devuelto cartera
                        </button>
                    </div>
                    <span id="cc-filtro-hist-count" class="cc-filtro-count"></span>
                </div>

                <div id="loader-historial" class="text-center py-5 text-muted<?= $ccActHist ? '' : ' d-none' ?>">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    Cargando movimientos...
                </div>
                <div id="wrap-historial" class="d-none">
                    <table id="tablaHistorialMovs" class="table table-hover mb-0 cc-table w-100">
                        <thead>
                            <tr>
                                <th class="ps-3">Cr&eacute;dito</th>
                                <th>Cliente</th>
                                <th>Estatus</th>
                                <th>Realizado por</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="empty-historial" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Sin movimientos registrados todavía.
                </div>
                <div id="empty-busqueda-hist" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-search fa-2x mb-2 d-block opacity-50"></i>
                    Sin resultados para la búsqueda.
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($cc_perm_cartera)): ?>
    <!-- ══ PESTAÑA 4: CARTERA ══ -->    <div class="tab-pane fade<?= $ccActCart ? ' show active' : '' ?>" id="tab-cartera" role="tabpanel">
        <div id="loader-cartera" class="text-center py-5 text-muted<?= $ccActCart ? '' : ' d-none' ?>">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando notificaciones de cartera...
        </div>
        <div id="wrap-cartera" class="d-none">
            <!-- Sub-pestañas -->
            <ul class="nav nav-tabs cc-nav-tabs mb-3" id="cartera-subtabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="cartera-recibidos-btn"
                            data-bs-toggle="tab" data-bs-target="#cartera-recibidos"
                            type="button" role="tab" aria-controls="cartera-recibidos" aria-selected="true">
                        <i class="fa-solid fa-inbox me-1"></i>Recibidos
                        <span class="badge ms-1" id="badge-cart-recibidos" style="background:#0f5c8a;color:#fff;">0</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cartera-notificados-btn"
                            data-bs-toggle="tab" data-bs-target="#cartera-notificados"
                            type="button" role="tab" aria-controls="cartera-notificados" aria-selected="false">
                        <i class="fa-solid fa-bell me-1"></i>Notificados
                        <span class="badge ms-1" id="badge-cart-notificados" style="background:#0f5c8a;color:#fff;">0</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <!-- Sub-tab: Recibidos (enviado_cartera) -->
                <div class="tab-pane fade show active" id="cartera-recibidos" role="tabpanel">
                    <div class="cc-subtab-desc mb-3">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Convenios de cierre recibidos en tu bandeja y <strong>pendientes de revisión y notificación interna</strong>. Confirma que la captura y la liquidación por <strong>QUITA en S2</strong> estén completadas y, una vez validado, marca el registro como <strong>Cerrado</strong> para que desaparezca de esta vista.</span>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table id="tablaCartRecibidos" class="dt-responsive table border-top">
                            <thead>
                                <tr>
                                    <th></th><!-- control responsive -->
                                    <th>Crédito / Cliente</th>
                                    <th>Producto</th>
                                    <th>Total convenio</th>
                                    <th>Fecha acuerdo</th>
                                    <th>Avance</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                    <th></th><!-- id oculto -->
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div id="empty-cart-recibidos" class="text-center py-4 text-muted d-none">
                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                        Sin registros recibidos en cartera.
                    </div>
                </div>
                <!-- Sub-tab: Notificados (notificado_cartera) -->
                <div class="tab-pane fade" id="cartera-notificados" role="tabpanel">
                    <div class="cc-subtab-desc cc-subtab-desc--notif mb-3">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Convenios <strong>ya notificados al área de cartera</strong> y en espera de resolución. Han sido enviados para su carga en S2; una vez liquidados el sistema los cerrará automáticamente.</span>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table id="tablaCartNotificados" class="dt-responsive table border-top">
                            <thead>
                                <tr>
                                    <th></th><!-- control responsive -->
                                    <th>Crédito / Cliente</th>
                                    <th>Producto</th>
                                    <th>Total convenio</th>
                                    <th>Fecha acuerdo</th>
                                    <th>Avance</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                    <th></th><!-- id oculto -->
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div id="empty-cart-notificados" class="text-center py-4 text-muted d-none">
                        <i class="fa-solid fa-bell fa-2x mb-2 d-block opacity-50"></i>
                        Sin convenios notificados pendientes.
                    </div>
                </div>
            </div>
        </div>
        <div id="empty-cartera" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin notificaciones de convenios pendientes.
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($cc_perm_peticiones)): ?>
    <!-- ══ PESTAÑA PETICIONES DE CANCELAMIENTO ══ -->
    <div class="tab-pane fade<?= $ccActPet ? ' show active' : '' ?>" id="tab-peticiones" role="tabpanel">
        <div class="cc-subtab-desc mb-3" style="background:#faf5ff;border-color:#c4b5fd;">
            <i class="fa-solid fa-circle-info" style="color:#7c3aed;"></i>
            <span>Solicitudes de cancelamiento enviadas por los gestores de Despachos, pendientes de <strong>autorización</strong>.
            Revisa el motivo y, si procede, autoriza la cancelación del convenio.</span>
        </div>
        <div id="loader-peticiones" class="text-center py-5 text-muted<?= $ccActPet ? '' : ' d-none' ?>">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando peticiones de cancelamiento...
        </div>
        <div id="wrap-peticiones" class="d-none">
            <div class="card-datatable table-responsive">
                <table id="tablaPeticiones" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th></th><!-- control expand -->
                            <th>Crédito / Cliente</th>
                            <th>Producto</th>
                            <th>Total convenio</th>
                            <th>Fecha acuerdo</th>
                            <th>Solicita</th>
                            <th>Fecha solicitud</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div id="empty-peticiones" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin peticiones de cancelamiento pendientes.
        </div>
    </div>
    <?php endif; ?>

</div>
</div>
<?php endif; ?>

<div class="modal fade" id="ccModalVoBo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-stamp me-2 text-primary"></i>Enviar a Vo.Bo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cc-vobo-id" value="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Archivo (PDF o imagen) <span class="text-muted fw-normal">(opcional)</span></label>
                    <input type="file" id="cc-vobo-archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    <small class="text-muted">Máximo 8 MB.</small>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Comentario <span class="text-danger">*</span></label>
                    <textarea id="cc-vobo-comentario" class="form-control" rows="3" maxlength="150" placeholder="Describe el motivo del envío a Dirección de Cobranza..."></textarea>
                    <div class="d-flex justify-content-end">
                        <small class="text-muted"><span id="cc-vobo-contador">0</span>/150</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="cc-vobo-enviar-btn">
                    <i class="fa-solid fa-paper-plane me-1"></i>Enviar a dirección de cobranza
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.__CC_PESTANAS_PERM__ = <?= json_encode([
    'alguno'      => !empty($cc_perm_alguno),
    'convenios'   => !empty($cc_perm_convenios),
    'validacion'  => !empty($cc_perm_validacion),
    'en_proceso'  => !empty($cc_perm_en_proceso),
    'vobo'        => !empty($cc_perm_vobo),
    'historial'   => !empty($cc_perm_historial),
    'cartera'     => !empty($cc_perm_cartera),
    'peticiones'  => !empty($cc_perm_peticiones),
    'defaultTab'  => isset($cc_default_tab) ? $cc_default_tab : null,
    'ambas_celulas' => (isset($cc_celulas_permitidas) ? $cc_celulas_permitidas : null) === null,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script>
/* ══════════════════════════════════════════
   CIERRE DE CRÉDITO — JS
══════════════════════════════════════════ */
(function () {
    'use strict';

    const CC_P = window.__CC_PESTANAS_PERM__ || {
        alguno: true,
        convenios: true,
        validacion: true,
        en_proceso: true,
        vobo: true,
        historial: true,
        cartera: true,
        peticiones: true,
        defaultTab: 'validacion',
        ambas_celulas: false,
    };
    if (!CC_P.alguno) {
        return;
    }

    /** true cuando el usuario tiene acceso a ambas células (Despachos + Call Center) */
    const CC_AMBAS = CC_P.ambas_celulas === true;

    /* ── Helpers ── */
    function fmt(n) {
        const v = parseFloat(n) || 0;
        return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    }
    function fmtFecha(val) {
        if (!val) return '—';
        // Tratar el valor como UTC (servidor almacena en UTC) y mostrar en hora México
        const iso = val.replace(' ', 'T') + (val.includes('T') || val.endsWith('Z') ? '' : 'Z');
        const d = new Date(iso);
        if (isNaN(d)) return val;
        return d.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: 'America/Mexico_City' });
    }
    function badgeEstatus(estatus) {
        if (estatus === 'en_proceso')
            return '<span class="badge badge-en-proceso rounded-pill px-3">En Proceso</span>';
        return `<span class="badge bg-secondary">${estatus}</span>`;
    }
    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    /** Devuelve un badge de célula (Despachos / Call Center) solo si el usuario ve ambas células. */
    function _celulaBadge(r) {
        if (!CC_AMBAS) return '';
        const cel = parseInt(r.id_celula) || 0;
        if (cel === 1) return `<span class="ep-celula-badge ep-cel-desp"><i class="fa-solid fa-file-lines me-1"></i>Despachos</span>`;
        if (cel === 2) return `<span class="ep-celula-badge ep-cel-cc"><i class="fa-solid fa-phone me-1"></i>Call Center</span>`;
        return '';
    }

    /**
     * Badge de antigüedad para el tab Validación de Cierre.
     * Usa fecha_modifica (cuando el convenio pasó a completado).
     * Si no existe, cae a fecha_alta del convenio.
     */
    function _ccAgeBadge(r) {
        const raw = r.fecha_modifica || r.fecha_alta;
        if (!raw) return '';
        const iso  = raw.replace(' ', 'T') + (raw.includes('T') || raw.endsWith('Z') ? '' : 'Z');
        const base = new Date(iso);
        if (isNaN(base)) return '';
        const mins = Math.floor((Date.now() - base.getTime()) / 60000);
        let cls, icon, label;
        if (mins < 30) {
            cls = 'age-fresh';   icon = 'fa-circle-check';       label = 'Recién completado';
        } else if (mins < 60) {
            cls = 'age-mins';    icon = 'fa-clock';               label = `${mins} min en espera`;
        } else if (mins < 180) {
            const h = Math.floor(mins / 60);
            cls = 'age-warn';    icon = 'fa-hourglass-half';      label = `${h}h en espera`;
        } else if (mins < 1440) {
            const h = Math.floor(mins / 60);
            cls = 'age-orange';  icon = 'fa-triangle-exclamation'; label = `${h}h de retraso`;
        } else if (mins < 10080) {
            const d = Math.floor(mins / 1440);
            cls = 'age-danger';  icon = 'fa-fire';                label = `${d} día${d!==1?'s':''} de retraso`;
        } else {
            cls = 'age-critical'; icon = 'fa-skull';              label = '+7 días de retraso';
        }
        return `<span class="cc-age-badge ${cls}"><i class="fa-solid ${icon}"></i>${label}</span>`;
    }

    /** Retorna true si el convenio fue creado hace menos de 30 minutos */
    function _isNew(fechaAlta) {
        if (!fechaAlta) return false;
        const iso  = fechaAlta.replace(' ', 'T') + (fechaAlta.includes('T') || fechaAlta.endsWith('Z') ? '' : 'Z');
        const base = new Date(iso);
        if (isNaN(base)) return false;
        return (Date.now() - base.getTime()) < 30 * 60 * 1000;
    }

    /**
     * Línea de tiempo de los 4 momentos del flujo de cierre.
     * M1: fecha_alta convenio_cliente (acuerdo firmado)
     * M2: fecha_modifica convenio_cliente (marcado completado)
     * M3: fecha_alta de cierre_credito_seguimiento — no disponible aquí, se omite
     * M4: confirmación por validador — pendiente
     */
    function _ccTimeline(r) {
        const fmt = (val) => {
            if (!val) return null;
            const iso = val.replace(' ', 'T') + (val.includes('T') || val.endsWith('Z') ? '' : 'Z');
            const d = new Date(iso);
            if (isNaN(d)) return null;
            return d.toLocaleString('es-MX', { day:'2-digit', month:'2-digit', year:'numeric',
                hour:'2-digit', minute:'2-digit', timeZone:'America/Mexico_City' });
        };
        const m1 = fmt(r.fecha_alta);
        const m2 = fmt(r.fecha_modifica);
        const steps = [
            { label: '1. Alta del convenio',   date: m1, done: !!m1 },
            { label: '2. Convenio completado', date: m2, done: !!m2 },
        ];
        const rows = steps.map(s => `
            <div class="cc-tl-row">
                <div class="cc-tl-dot ${s.done ? 'done' : 'pending'}"></div>
                <div class="cc-tl-info">
                    <span class="cc-tl-label">${s.label}</span>
                    ${s.date ? `<span class="cc-tl-date">${s.date}</span>`
                             : `<span class="cc-tl-date" style="font-style:italic;">Pendiente</span>`}
                </div>
            </div>`).join('');
        return `<div class="cc-timeline">
            <div class="cc-timeline-title"><i class="fa-solid fa-timeline me-1"></i>Flujo de cierre</div>
            ${rows}
        </div>`;
    }

    /**
     * Banner prominente de "Enviado a validación" para el tab En Proceso.
     * Muestra la fecha + cuánto tiempo lleva esperando con color según urgencia.
     */
    function _epEsperaBanner(fechaAlta) {
        if (!fechaAlta) return '';
        const iso  = fechaAlta.replace(' ', 'T') + (fechaAlta.includes('T') || fechaAlta.endsWith('Z') ? '' : 'Z');
        const base = new Date(iso);
        if (isNaN(base)) return '';
        const mins = Math.floor((Date.now() - base.getTime()) / 60000);
        const hrs  = Math.floor(mins / 60);
        const days = Math.floor(mins / 1440);

        let elapsed, chipCls;
        if (mins < 60) {
            elapsed = `${mins} min esperando`;                    chipCls = 'ep-wait-blue';
        } else if (mins < 1440) {
            elapsed = `${hrs}h esperando`;                        chipCls = 'ep-wait-blue';
        } else if (days < 3) {
            elapsed = `${days} día${days!==1?'s':''} esperando`;  chipCls = 'ep-wait-yellow';
        } else if (days < 7) {
            elapsed = `${days} días esperando`;                   chipCls = 'ep-wait-orange';
        } else {
            elapsed = `${days} días esperando`;                   chipCls = 'ep-wait-red';
        }

        const fmtD = base.toLocaleString('es-MX', {
            day:'2-digit', month:'2-digit', year:'numeric',
            hour:'2-digit', minute:'2-digit', timeZone:'America/Mexico_City'
        });

        return `<div class="ep-espera-banner ${chipCls}">
            <div class="ep-espera-icon"><i class="fa-solid fa-paper-plane"></i></div>
            <div class="ep-espera-body">
                <span class="ep-espera-label">Enviado a validación</span>
                <span class="ep-espera-date">${fmtD}</span>
            </div>
            <span class="ep-espera-chip">${elapsed}</span>
        </div>`;
    }

    /**
     * Renderiza un paginador Bootstrap-DT dentro del elemento `containerId`.
     * @param {string}   containerId   ID del div destino
     * @param {number}   page          Página actual (1-based)
     * @param {number}   total         Total de registros en el conjunto filtrado
     * @param {number}   pageSize      Registros por página
     * @param {number[]} pageSizeOpts  Opciones de "Mostrar X"
     * @param {function} onPage        Callback(newPage)
     * @param {function} onSize        Callback(newSize)
     */
    function _renderPager(containerId, page, total, pageSize, pageSizeOpts, onPage, onSize) {
        const el = document.getElementById(containerId);
        if (!el) return;
        const totalPages = Math.max(1, Math.ceil(total / pageSize));
        const from  = total === 0 ? 0 : (page - 1) * pageSize + 1;
        const to    = Math.min(page * pageSize, total);

        // Build page buttons (show up to 5 page numbers around current)
        let pageButtons = '';
        const delta = 2;
        const start = Math.max(1, page - delta);
        const end   = Math.min(totalPages, page + delta);
        if (start > 1) pageButtons += `<li class="page-item"><a class="page-link" href="#" data-p="1">1</a></li>`;
        if (start > 2) pageButtons += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
        for (let p = start; p <= end; p++) {
            pageButtons += `<li class="page-item${p === page ? ' active' : ''}"><a class="page-link" href="#" data-p="${p}">${p}</a></li>`;
        }
        if (end < totalPages - 1) pageButtons += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
        if (end < totalPages) pageButtons += `<li class="page-item"><a class="page-link" href="#" data-p="${totalPages}">${totalPages}</a></li>`;

        const sizeOptions = pageSizeOpts.map(v =>
            `<option value="${v}"${v === pageSize ? ' selected' : ''}>${v}</option>`
        ).join('');

        el.innerHTML = `
        <div class="row align-items-center g-0" style="padding:.35rem 0;">
            <div class="col-sm-12 col-md-5">
                <div class="cc-pager-len">
                    Mostrar
                    <select onchange="(${containerId === 'pager-ef' ? '(sz)=>{_efPage=1;_efPageSize=sz;_pintarCards(_currentEfRows);}' : '(sz)=>{_epPage=1;_pintarEnProceso(_currentEpRows);}'})(+this.value)">
                        ${sizeOptions}
                    </select>
                    registros
                </div>
            </div>
            <div class="col-sm-12 col-md-7 text-md-end">
                <div class="dataTables_paginate" style="padding:.35rem .5rem;">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item${page <= 1 ? ' disabled' : ''}">
                            <a class="page-link" href="#" data-p="1" title="Primera">&laquo;</a>
                        </li>
                        <li class="page-item${page <= 1 ? ' disabled' : ''}">
                            <a class="page-link" href="#" data-p="${page - 1}" title="Anterior">&lsaquo;</a>
                        </li>
                        ${pageButtons}
                        <li class="page-item${page >= totalPages ? ' disabled' : ''}">
                            <a class="page-link" href="#" data-p="${page + 1}" title="Siguiente">&rsaquo;</a>
                        </li>
                        <li class="page-item${page >= totalPages ? ' disabled' : ''}">
                            <a class="page-link" href="#" data-p="${totalPages}" title="&Uacute;ltima">&raquo;</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row g-0">
            <div class="col-12">
                <div class="cc-pager-info">
                    ${total === 0 ? 'Sin registros' : `Mostrando <strong>${from}</strong> a <strong>${to}</strong> de <strong>${total}</strong> registro${total !== 1 ? 's' : ''}`}
                </div>
            </div>
        </div>`;

        // Bind page-link clicks
        el.querySelectorAll('.page-link[data-p]').forEach(function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                const newPage = parseInt(this.dataset.p);
                if (isNaN(newPage) || newPage < 1 || newPage > totalPages) return;
                onPage(newPage);
            });
        });
        el.classList.remove('d-none');
    }

    function convenioEstatusBadge(estatus) {
        const map = {
            'completado':          { bg: '#bbf7d0', color: '#14532d', icon: 'fa-circle-check',      label: 'Completo'              },
            'activo':              { bg: '#dbeafe', color: '#1e40af', icon: 'fa-hourglass-half',    label: 'Activo'                },
            'cancelado':           { bg: '#fee2e2', color: '#b91c1c', icon: 'fa-ban',               label: 'Cancelado'             },
            'enviado_cartera':     { bg: '#e0e7ff', color: '#3730a3', icon: 'fa-inbox',             label: 'En bandeja de entrada' },
            'notificado_cartera':  { bg: '#e0f2fe', color: '#0369a1', icon: 'fa-bell',             label: 'Notificado'            },
            'cerrado':             { bg: '#1d4ed8', color: '#fff',    icon: 'fa-circle-check',      label: 'Cerrado'               },
            'devuelto_cartera':    { bg: '#b91c1c', color: '#fff',    icon: 'fa-rotate-left',       label: 'Devuelto cartera'      },
        };
        const cfg = map[estatus] || { bg: '#f1f5f9', color: '#475569', icon: 'fa-circle', label: esc(estatus || '—') };
        const cls = map[estatus] ? `cc-estatus-${estatus}` : 'cc-estatus-default';
        return `<span class="badge rounded-pill ${cls}" style="background:${cfg.bg};color:${cfg.color};font-size:.75rem;font-weight:700;">
                    <i class="fa-solid ${cfg.icon} me-1"></i>${cfg.label}
                </span>`;
    }

    /* ── Trail de cartera (debajo del badge de convenio) ── */
    function _trailCartera(seg) {
        const steps = {
            notificado_cartera: [
                { label: 'Registrado', active: false },
                { label: 'Notificado', active: true }
            ],
            cerrado: [
                { label: 'Registrado', active: false },
                { label: 'Notificado', active: false },
                { label: 'Cerrado',    active: true }
            ],
            devuelto_cartera: [
                { label: 'Registrado', active: false },
                { label: 'Notificado', active: false },
                { label: 'DEVUELTA',   active: true }
            ]
        };
        const trail = steps[seg];
        if (!trail) return '';
        const cls = seg === 'devuelto_cartera' ? 'cc-trail--devuelta'
                  : seg === 'cerrado'          ? 'cc-trail--cerrado'
                  :                              'cc-trail--notif';
        const items = trail.map(s =>
            `<span class="cc-trail-step${s.active ? ' cc-trail-step--active' : ''}">${s.label}</span>`
        ).join('<span class="cc-trail-sep">→</span>');
        return `<div class="cc-trail ${cls}" style="margin-top:4px;">${items}</div>`;
    }

    /* ══════════════════════════════════
       RENDER: CARDS ENVIADOS FINALIZADOS
    ══════════════════════════════════ */
    let _allRows          = [];
    let _allRowsEp        = [];
    let _allRowsVoBo      = [];
    let _epView            = '3'; // '3' | '2' | 'list'
    let _filtroEf         = 'todos';
    let _filtroHist       = 'todos';
    let _allRowsHist      = [];
    let _allRowsConv      = [];
    let _filtroConv       = 'todos';
    let _validador        = '—';
    let enProcesoCargado  = false;
    // Paginación Validación de Cierre
    let _efPage           = 1;
    let _efPageSize       = 12;
    let _currentEfRows    = [];
    // Paginación En Proceso
    let _epPage           = 1;
    let _currentEpRows    = [];

    function _rowPasaFiltroEf(r) {
        if (_filtroEf === 'con_docs')
            return !!(r.pdf_adjunto && r.pdf_adjunto !== '') || (parseInt(r.comprobantes_subidos) || 0) > 0;
        if (_filtroEf === 'devueltos')
            return !!r.ultimo_motivo_descarte;
        if (_filtroEf === 'despachos')
            return parseInt(r.id_celula) === 1;
        if (_filtroEf === 'call_center')
            return parseInt(r.id_celula) === 2;
        return true;
    }

    function _getRowsEf(t) {
        let rows = _filtroEf === 'todos' ? _allRows : _allRows.filter(_rowPasaFiltroEf);
        if (t) rows = rows.filter(r =>
            String(r.id_credito      || '').toLowerCase().includes(t) ||
            String(r.nombre_cliente  || '').toLowerCase().includes(t) ||
            String(r.nombre_producto || '').toLowerCase().includes(t) ||
            String(r.nombre_despacho || '').toLowerCase().includes(t)
        );
        return rows;
    }

    function renderCards(rows, validador) {
        _allRows   = rows;
        _validador = validador;

        document.getElementById('loader-env-finalizado').classList.add('d-none');
        const badge = document.getElementById('badge-env-finalizado');
        badge.textContent = rows.length;

        if (!rows.length) {
            document.getElementById('empty-env-finalizado').classList.remove('d-none');
            return;
        }

        document.getElementById('cc-filtros-ef').classList.remove('d-none');
        _pintarCards(_getRowsEf(''));
    }

    function _pintarCards(rows) {
        _currentEfRows = rows;
        const wrap        = document.getElementById('wrap-env-finalizado');
        const pagerEl     = document.getElementById('pager-ef');
        const emptyNormal = document.getElementById('empty-env-finalizado');
        const emptySearch = document.getElementById('empty-busqueda');
        const countEl     = document.getElementById('cc-filtro-count');

        emptyNormal.classList.add('d-none');
        emptySearch.classList.add('d-none');

        if (countEl) {
            const visible = rows.length;
            const total   = _allRows.length;
            countEl.textContent = visible < total
                ? `${visible} de ${total}`
                : (total ? `${total} total${total !== 1 ? 'es' : ''}` : '');
        }

        if (!rows.length) {
            wrap.classList.add('d-none');
            wrap.innerHTML = '';
            if (pagerEl) pagerEl.classList.add('d-none');
            emptySearch.classList.remove('d-none');
            return;
        }

        // Clamp page to valid range
        const totalPages = Math.max(1, Math.ceil(rows.length / _efPageSize));
        if (_efPage > totalPages) _efPage = totalPages;

        const pageRows = rows.slice((_efPage - 1) * _efPageSize, _efPage * _efPageSize);
        wrap.classList.remove('d-none');
        wrap.innerHTML = pageRows.map(r => buildCard(r, _validador)).join('');

        _renderPager('pager-ef', _efPage, rows.length, _efPageSize, [12, 24, 48],
            function(p) { _efPage = p; _pintarCards(_currentEfRows); },
            null
        );
    }

    /* ── Detecta qué pestaña está activa ── */
    function _tabActiva() {
        const elConv = document.getElementById('tab-convenios');
        if (elConv && elConv.classList.contains('show')) return 'conv';
        const elEp = document.getElementById('tab-en-proceso');
        if (elEp && elEp.classList.contains('show')) return 'ep';
        const elVoBo = document.getElementById('tab-vobo');
        if (elVoBo && elVoBo.classList.contains('show')) return 'vobo';
        const elHist = document.getElementById('tab-historial');
        if (elHist && elHist.classList.contains('show')) return 'hist';
        const elCart = document.getElementById('tab-cartera');
        if (elCart && elCart.classList.contains('show')) return 'cartera';
        const elPet = document.getElementById('tab-peticiones');
        if (elPet && elPet.classList.contains('show')) return 'peticiones';
        return 'ef';
    }

    /* ── Filtro en tiempo real (aplica en la pestaña activa) ── */
    function ccFiltrar(termino) {
        const t = termino.trim().toLowerCase();
        const btnLimpiar = document.getElementById('barraGeneral-limpiar');
        btnLimpiar.style.display = t ? '' : 'none';

        const tab = _tabActiva();

        if (tab === 'ep') {
            _epPage = 1; // reset pagination on new search
            _pintarEnProceso(!t ? _allRowsEp : _allRowsEp.filter(r =>
                String(r.id_credito      || '').toLowerCase().includes(t) ||
                String(r.nombre_cliente  || '').toLowerCase().includes(t) ||
                String(r.nombre_producto || '').toLowerCase().includes(t)
            ));
            return;
        }

        if (tab === 'hist') {
            if (_tablaHist) {
                // Apply both the estatus filter and the text search
                if (_filtroHist === 'todos') {
                    $.fn.dataTable.ext.search.pop(); // remove any previous custom filter
                } 
                _tablaHist.search(t).draw();
            }
            return;
        }

        if (tab === 'conv') {
            if (_tablaConv) { _tablaConv.search(t).draw(); }
            return;
        }

        if (tab === 'vobo') {
            if (_tablaVoBo) { _tablaVoBo.search(t).draw(); }
            return;
        }

        if (tab === 'cartera') {
            const subRec = document.getElementById('cartera-recibidos');
            const subNot = document.getElementById('cartera-notificados');
            if (subNot && subNot.classList.contains('show')) {
                if (_tablaCartNot) _tablaCartNot.search(t).draw();
            } else {
                if (_tablaCartRec) _tablaCartRec.search(t).draw();
            }
            return;
        }

        if (tab === 'peticiones') {
            if (_tablaPeticiones) _tablaPeticiones.search(t).draw();
            return;
        }

        // Tab 1: Enviados Finalizados
        _efPage = 1; // reset pagination on new search
        _pintarCards(_getRowsEf(t));
    }

    document.getElementById('barraGeneral-input')
        .addEventListener('input', function () { ccFiltrar(this.value); });

    // ── Selector de vista En Proceso ──
    document.querySelectorAll('.ep-view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (this.dataset.view === _epView) return;
            document.querySelectorAll('.ep-view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            _epPage = 1; // reset pagination on view change
            _epView = this.dataset.view;
            // Cerrar cualquier detalle abierto antes de re-renderizar
            document.querySelectorAll('[id^="ep-chunk-detail-col-"]').forEach(el => {
                const ci = parseInt(el.id.replace('ep-chunk-detail-col-', ''));
                _epCloseChunk(ci);
            });
            const t = document.getElementById('barraGeneral-input').value.trim().toLowerCase();
            _pintarEnProceso(!_allRowsEp.length ? [] : (t
                ? _allRowsEp.filter(r =>
                    String(r.id_credito || '').toLowerCase().includes(t) ||
                    String(r.nombre_cliente || '').toLowerCase().includes(t) ||
                    String(r.nombre_producto || '').toLowerCase().includes(t))
                : _allRowsEp));
        });
    });

    document.getElementById('barraGeneral-limpiar')
        .addEventListener('click', function () {
            const input = document.getElementById('barraGeneral-input');
            input.value = '';
            ccFiltrar('');
            input.focus();
        });

    const _filtrosHistToggle = document.getElementById('cc-filtros-hist-toggle');
    if (_filtrosHistToggle) {
        _filtrosHistToggle.addEventListener('click', function () {
            const opciones = document.getElementById('cc-filtros-hist-opciones');
            const isOpen   = opciones.style.display !== 'none';
            opciones.style.display = isOpen ? 'none' : 'flex';
            this.classList.toggle('open', !isOpen);
        });
    }

    document.querySelectorAll('.cc-filtro-hist-opcion').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cc-filtro-hist-opcion').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            _filtroHist = this.dataset.filtroHist;
            _applyHistFilter();
            const t = document.getElementById('barraGeneral-input').value.trim();
            if (t && _tablaHist) _tablaHist.search(t).draw();
        });
    });

    const _filtrosToggle = document.getElementById('cc-filtros-toggle');
    if (_filtrosToggle) {
        _filtrosToggle.addEventListener('click', function () {
            const opciones = document.getElementById('cc-filtros-opciones');
            const isOpen   = opciones.style.display !== 'none';
            opciones.style.display = isOpen ? 'none' : 'flex';
            this.classList.toggle('open', !isOpen);
        });
    }

    document.querySelectorAll('#cc-filtros-ef .cc-filtro-opcion').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#cc-filtros-ef .cc-filtro-opcion').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            _filtroEf = this.dataset.filtro;
            _efPage = 1; // reset pagination on filter change
            const t = document.getElementById('barraGeneral-input').value.trim().toLowerCase();
            _pintarCards(_getRowsEf(t));
        });
    });

    /* ── Filtros Convenios ── */
    function _filtroConvAplicar() {
        if (!_tablaConv) return;
        let filtered = _allRowsConv;
        if (_filtroConv === 'con_docs') {
            filtered = _allRowsConv.filter(function(r) {
                return !!(r.pdf_adjunto && r.pdf_adjunto !== '') || (parseInt(r.comprobantes_subidos) || 0) > 0;
            });
        } else if (_filtroConv === 'activos') {
            filtered = _allRowsConv.filter(function(r) {
                return String(r.estatus || '').toLowerCase() === 'activo';
            });
        } else if (_filtroConv === 'cancelados') {
            filtered = _allRowsConv.filter(function(r) {
                return String(r.estatus || '').toLowerCase() === 'cancelado';
            });
        }
        _tablaConv.clear().rows.add(filtered).draw();
        var countEl = document.getElementById('cc-filtro-conv-count');
        if (countEl) countEl.textContent = _filtroConv !== 'todos' ? filtered.length + ' de ' + _allRowsConv.length : '';
    }

    const _filtrosConvToggle = document.getElementById('cc-filtros-conv-toggle');
    if (_filtrosConvToggle) {
        _filtrosConvToggle.addEventListener('click', function () {
            var opciones = document.getElementById('cc-filtros-conv-opciones');
            var isOpen   = opciones.style.display !== 'none';
            opciones.style.display = isOpen ? 'none' : 'flex';
            this.classList.toggle('open', !isOpen);
        });
    }

    document.querySelectorAll('.cc-filtro-conv').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cc-filtro-conv').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            _filtroConv = this.dataset.filtroConv;
            _filtroConvAplicar();
        });
    });

    /* ── Scroll to top (Validación) ── */
    const _scrollTopEf = document.getElementById('cc-scroll-top-ef');
    if (_scrollTopEf) {
        _scrollTopEf.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ══════════════════════════════════
       HELPER: bloque de info S2
    ══════════════════════════════════ */
    function _s2InfoHtml(r) {
        const semana    = (r.semana_acuerdo != null && r.anio_semana_acuerdo != null)
            ? `Sem. ${r.semana_acuerdo} / ${r.anio_semana_acuerdo}` : '—';
        const s2Cont    = (r.s2_cuotas_contratadas != null) ? parseInt(r.s2_cuotas_contratadas) : null;
        const s2Pag     = (r.s2_cuotas_pagadas     != null) ? parseInt(r.s2_cuotas_pagadas)     : null;
        const s2Tot     = (r.s2_total_pagado        != null) ? parseFloat(r.s2_total_pagado)     : null;
        const s2Monto   = (r.s2_monto_otorgado      != null) ? parseFloat(r.s2_monto_otorgado)   : null;

        const s2Disponible = (s2Cont !== null || s2Pag !== null || s2Tot !== null || s2Monto !== null);

        const cuotasStr = (s2Pag !== null && s2Cont !== null) ? `${s2Pag} de ${s2Cont}` : '—';
        const totalStr  = s2Tot !== null
            ? Math.abs(s2Tot).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) : '—';
        const montoStr  = s2Monto !== null
            ? Math.abs(s2Monto).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) : '—';

        const cuerpo = s2Disponible
            ? `<div style="display:grid;grid-template-columns:1fr 1fr;gap:.25rem .5rem;">
                <div><span class="cc-s2-label">Semana acuerdo:</span> <strong class="cc-s2-value">${semana}</strong></div>
                <div><span class="cc-s2-label">Monto otorgado:</span> <strong class="cc-s2-value">${montoStr}</strong></div>
                <div><span class="cc-s2-label">Cuotas:</span> <strong class="cc-s2-value">${cuotasStr}</strong></div>
                <div><span class="cc-s2-label">Total pagado:</span> <strong class="cc-s2-total">${totalStr}</strong></div>
               </div>`
            : `<div class="cc-s2-empty">
                <i class="fa-solid fa-clock" style="color:#94a3b8;"></i>
                <span>Datos aún no disponibles en Segundometro</span>
               </div>`;

        return `<div class="cc-s2-block">
            <div class="cc-s2-block-title">
                <i class="fa-solid fa-chart-line me-1"></i>Datos crédito
            </div>
            ${cuerpo}
            <div class="cc-s2-footer">Datos validados en S2</div>
        </div>`;
    }

    function buildCard(r, validador) {
        const semanas      = parseInt(r.numero_semanas) || 1;
        const pagadas      = parseInt(r.cuotas_pagadas) || 0;
        const pct          = Math.min(100, Math.round((pagadas / semanas) * 100));

        const totalPagar   = parseFloat(r.total_a_pagar)   || 0;
        const adicional    = parseFloat(r.monto_adicional) || 0;
        const descuento    = parseFloat(r.descuento_monto) || 0;
        const totalInicial = totalPagar - adicional;

        // ── Resumen de aplicación (solo si tiene adicionales) ──
        let resumenHtml = '';
        if (adicional > 0) {
            resumenHtml = `
            <div class="cc-resumen-aplicacion">
                <div class="cc-res-title"><i class="fa-solid fa-list-check me-1"></i>Resumen de adicionales</div>
                <div class="cc-res-row"><span>Descuento (${esc(r.porcentaje_descuento)}%)</span><span class="text-success">- ${fmt(descuento)}</span></div>
                <div class="cc-res-row"><span>Total inicial</span><span>${fmt(totalInicial)}</span></div>
                <div class="cc-res-row"><span>Adicionales</span><span>${fmt(adicional)}</span></div>
                <div class="cc-res-row total"><span>Total final</span><span>${fmt(totalPagar)}</span></div>
            </div>`;
        }

        const despacho = r.nombre_despacho ? esc(r.nombre_despacho) : '<span class="text-muted fst-italic">Sin asignación</span>';

        return `
        <div class="cc-conv-card" id="cc-card-${r.id}">

            <!-- Cabecera: ID Crédito + barra de progreso -->
            <div class="cc-conv-card-header">
                <span class="cc-credito-id">
                    Crédito: ${esc(r.id_credito)}
                    ${_isNew(r.fecha_alta) ? '<span class="cc-badge-nuevo"><i class="fa-solid fa-star"></i>Nuevo</span>' : ''}
                    <small>${esc(r.nombre_cliente)}</small>
                </span>
                <span style="color:#fff; font-size:.82rem; font-weight:600; white-space:nowrap; display:flex; align-items:center; gap:.4rem; flex-wrap:wrap;">
                    ${_celulaBadge(r)}
                    ${pagadas} pagos de ${semanas}${pagadas >= semanas ? ' <i class=\'bi bi-check-circle-fill\' style=\'color:#4ade80\'></i>' : ''}
                </span>
            </div>
            <!-- Badge antigüedad (debajo del header) -->
            <div style="padding:.35rem .8rem .1rem;">${_ccAgeBadge(r)}</div>

            <!-- Cuerpo: detalles -->
            <div class="cc-conv-card-body">
                <div class="cc-conv-details">

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Producto elegido</span>
                        <span class="cc-val">${esc(r.nombre_producto)}</span>
                        <span class="cc-pct-badge" style="margin-left:.35rem;">
                            ${parseFloat(r.porcentaje_descuento) || 0}%
                        </span>
                    </div>

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Nombre del cliente</span>
                        <span class="cc-val">${esc(r.nombre_cliente)}</span>
                    </div>

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Adeudo original</span>
                        <span class="cc-val fw-bold" style="color:#dc2626;">${fmt(parseFloat(r.adeudo_total_original) || 0)}${r.base_calculo ? ' <span style="font-size:.72rem;color:#0003d1;font-weight:400;">(Calculado sobre ' + (r.base_calculo === 'saldo_total_capital' ? 'Capital' : r.base_calculo === 'adeudo_total' ? 'Total' : r.base_calculo === 'interes' ? 'Interés' : r.base_calculo) + ')</span>' : ''}</span>
                    </div>

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Total final</span>
                        <span class="cc-val fw-bold text-success">${fmt(totalPagar)}</span>
                    </div>

                    ${resumenHtml}

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Gestor a cargo</span>
                        <span class="cc-val" style="display:inline-flex;align-items:center;gap:.45rem;flex-wrap:wrap;">${despacho}${_celulaBadge(r)}</span>
                    </div>

                    <!-- Fecha de finalización -->
                    ${r.fecha_modifica ? `
                    <div class="cc-detail-row" style="margin-top:.35rem;">
                        <span class="cc-lbl">Finalizado el</span>
                        <span class="cc-val">${new Date(r.fecha_modifica.replace(' ','T') + (r.fecha_modifica.includes('T') || r.fecha_modifica.endsWith('Z') ? '' : 'Z')).toLocaleString('es-MX', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false,timeZone:'America/Mexico_City'})}</span>
                    </div>` : ''}

                    <!-- Validación: usuario de sesión actual -->
                    <div class="cc-validacion-box">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Validación:</span>
                        <span class="cc-val-user">${esc(validador)}</span>
                    </div>

                    <!-- Descarte previo -->
                    ${r.ultimo_motivo_descarte ? `
                    <div class="cc-descarte-banner" style="margin-top:.65rem;">
                        <div style="font-weight:700;margin-bottom:.3rem;"><i class="fa-solid fa-triangle-exclamation me-1"></i>Descartado</div>
                        <div><span style="font-weight:600;">Motivo:</span> ${esc(r.ultimo_motivo_descarte)}</div>
                        ${r.ultimo_comentario_descarte ? `<div style="margin-top:.25rem;"><span style="font-weight:600;">Comentario:</span> ${esc(r.ultimo_comentario_descarte)}</div>` : ''}
                        <div class="cc-descarte-meta" style="margin-top:.25rem;">
                            <i class="fa-solid fa-user-pen me-1"></i>${esc(r.usuario_descarte || '—')}
                            ${r.fecha_descarte ? `<span class="ms-2"><i class="fa-regular fa-clock me-1"></i>${new Date(r.fecha_descarte.replace(' ','T')+'Z').toLocaleString('es-MX',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',timeZone:'America/Mexico_City'})}</span>` : ''}
                        </div>
                    </div>` : ''}

                    <!-- Notificado previamente a cartera -->
                    ${r.estatus_seguimiento === 'notificado_cartera' ? `
                    <div style="display:flex;align-items:center;gap:.5rem;margin-top:.65rem;padding:.5rem .75rem;background:#fef3c7;border:1px solid #fde68a;border-radius:.5rem;">
                        <i class="fa-solid fa-bell" style="color:#d97706;font-size:.85rem;flex-shrink:0;"></i>
                        <span style="font-size:.8rem;color:#92400e;font-weight:600;">Notificado previamente a cartera</span>
                    </div>` : ''}

                    <!-- Documentos adjuntos -->
                    ${(() => {
                        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
                        const compPaths = (r.comprobantes_paths && r.comprobantes_paths !== '')
                            ? r.comprobantes_paths.split('|').filter(p => p)
                            : [];
                        if (!pdfOk && compPaths.length === 0) return '';
                        let items = '';
                        if (pdfOk)
                            items += `<a href="${esc(r.pdf_adjunto)}" target="_blank"
                                         class="btn btn-sm btn-outline-secondary"
                                         style="font-size:.78rem;">
                                         <i class="fa-solid fa-file-pdf me-1"></i>PDF convenio
                                      </a>`;
                        if (compPaths.length > 0) {
                            const links = compPaths.map((p, i) =>
                                `<a href="${esc(p)}" target="_blank" class="btn btn-sm btn-outline-success" style="font-size:.78rem;"><i class="fa-solid fa-receipt me-1"></i>Comprobante ${i + 1}</a>`
                            ).join('');
                            items += links;
                        }
                        return `<div class="cc-doccheck-wrap mt-2">
                                    <div class="cc-doccheck-title"><i class="fa-solid fa-paperclip me-1"></i>Documentos adjuntos</div>
                                    <div class="cc-doccheck-items" style="display:flex;gap:.4rem;flex-wrap:wrap;">${items}</div>
                                </div>`;
                    })()}

                    ${_s2InfoHtml(r)}
                    ${_ccTimeline(r)}

                </div>
            </div>

<!-- Footer: botones -->
            <div class="cc-conv-card-footer" style="gap:.5rem;">
                <a class="cc-btn-convenio" href="/Convenios/consulta?credito=${esc(r.id_credito)}" target="_blank">
                    <i class="fa-solid fa-handshake"></i>
                    Ir al convenio
                </a>
                <button class="cc-btn-confirmar" onclick="ccConfirmar(${r.id}, '${esc(r.id_credito)}', '${esc(r.nombre_cliente)}')"
                        id="cc-btn-${r.id}">
                    <i class="fa-solid fa-check-circle"></i>
                    Confirmar cierre
                </button>
            </div>

            <!-- Panel lateral derecho (oculto por defecto) -->
            <div class="cc-side-panel" id="cc-acc-body-${r.id}">
                <div class="cc-side-panel-header">
                    <span class="cc-sp-title"><i class="fa-solid fa-table-list me-1"></i>Detalle del cierre</span>
                    <button class="cc-side-panel-close" onclick="ccToggleDetalle(${r.id})" title="Cerrar panel">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="cc-acc-loader-${r.id}" class="text-center py-3 text-muted" style="display:none;">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...
                </div>
                <div id="cc-acc-content-${r.id}" style="overflow-y:auto;flex:1;"></div>
            </div>

        </div>`;
    }

    /* ══════════════════════════════════
       RENDER: CARDS EN PROCESO
    ══════════════════════════════════ */
    function renderEnProceso(rows) {
        _allRowsEp = rows;
        document.getElementById('loader-en-proceso').classList.add('d-none');
        document.getElementById('badge-en-proceso').textContent = rows.length;

        if (!rows || rows.length === 0) {
            document.getElementById('empty-en-proceso').classList.remove('d-none');
            return;
        }

        const t = document.getElementById('barraGeneral-input').value.trim().toLowerCase();
        _pintarEnProceso(t ? rows.filter(r =>
            String(r.id_credito      || '').toLowerCase().includes(t) ||
            String(r.nombre_cliente  || '').toLowerCase().includes(t) ||
            String(r.nombre_producto || '').toLowerCase().includes(t)
        ) : rows);
    }

    function _pintarEnProceso(rows) {
        _currentEpRows = rows;
        const wrap        = document.getElementById('wrap-ep-cards');
        const pagerEl     = document.getElementById('pager-ep');
        const emptyNormal = document.getElementById('empty-en-proceso');
        const emptySearch = document.getElementById('empty-busqueda-ep');
        const toolbar     = document.getElementById('ep-view-toolbar');
        emptyNormal.classList.add('d-none');
        if (emptySearch) emptySearch.classList.add('d-none');

        if (!rows.length) {
            wrap.classList.add('d-none');
            wrap.innerHTML = '';
            if (pagerEl) pagerEl.classList.add('d-none');
            if (toolbar) toolbar.classList.add('d-none');
            if (emptySearch) emptySearch.classList.remove('d-none');
            return;
        }

        // Mostrar toolbar de vista
        if (toolbar) toolbar.classList.remove('d-none');

        // Tamaño de página según vista
        const epPageSize = _epView === 'list' ? 15 : (_epView === '2' ? 10 : 12);

        // Clamp page to valid range
        const totalPages = Math.max(1, Math.ceil(rows.length / epPageSize));
        if (_epPage > totalPages) _epPage = totalPages;

        const pageRows = rows.slice((_epPage - 1) * epPageSize, _epPage * epPageSize);

        const chunkSize = _epView === 'list' ? 1 : (_epView === '2' ? 2 : 3);
        const colClass  = _epView === 'list' ? 'col-12' :
                          _epView === '2'    ? 'col-12 col-md-6' : 'col-12 col-md-4';

        let html = '';
        for (let i = 0; i < pageRows.length; i += chunkSize) {
            const chunk    = pageRows.slice(i, i + chunkSize);
            const chunkIdx = Math.floor(i / chunkSize);
            html += `<div class="row g-3 mb-2 ep-chunk-row" id="ep-chunk-row-${chunkIdx}">`;
            chunk.forEach((r, idx) => {
                const cardHtml = _epView === 'list'
                    ? buildEnProcesoListRow(r)
                    : buildEnProcesoCard(r);
                const rowAttr = _epView === 'list' ? ` data-ep-row="${JSON.stringify(r).replace(/"/g, '&quot;')}"` : '';
                html += `<div class="${colClass}" id="cc-ep-col-${r.id}" data-ep-chunk="${chunkIdx}" data-ep-order="${i + idx}"${rowAttr}>${cardHtml}</div>`;
            });
            html += `</div>`;
        }
        wrap.innerHTML = html;
        wrap.classList.remove('d-none');

        const pageSizeOpts = _epView === 'list' ? [15, 30, 50] : (_epView === '2' ? [10, 20, 40] : [12, 24, 48]);
        _renderPager('pager-ep', _epPage, rows.length, epPageSize, pageSizeOpts,
            function(p) {
                // Close any open detail panel before changing page
                document.querySelectorAll('[id^="ep-chunk-detail-col-"]').forEach(function(el) {
                    const ci = parseInt(el.id.replace('ep-chunk-detail-col-', ''));
                    _epCloseChunk(ci);
                });
                _epPage = p;
                _pintarEnProceso(_currentEpRows);
            },
            null
        );
    }

    // ── Vista Lista: resumen de detalle (se muestra al abrir "Ver detalle") ──
    function _buildEpListSummaryHtml(r) {
        const fmtN = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
        const numCon = parseInt(r.comprobantes_subidos) || 0;
        const numTot = parseInt(r.comprobantes_total)   || 0;

        const base      = r.base_calculo;
        const baseLabel = base === 'saldo_total_capital' ? 'Capital'
                        : base === 'adeudo_total'         ? 'Total'
                        : base === 'interes'              ? 'Interés'
                        : base ? base : null;

        const pdfBadge  = pdfOk
            ? `<span class="cc-doc-ok"><i class="fa-solid fa-file-pdf me-1"></i>PDF Convenio</span>`
            : `<span class="cc-doc-missing"><i class="fa-solid fa-file-pdf me-1"></i>Sin PDF convenio</span>`;
        const compBadge = numTot === 0
            ? `<span class="cc-doc-missing"><i class="fa-solid fa-receipt me-1"></i>Sin comprobantes</span>`
            : `<span class="${numCon === numTot ? 'cc-doc-ok' : 'cc-doc-partial'}"><i class="fa-solid fa-receipt me-1"></i>Comprobantes ${numCon}/${numTot}</span>`;

        return `
        <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:.6rem;">
            <div class="cc-detail-row">
                <span class="cc-lbl">Producto</span>
                <span class="cc-val">${esc(r.nombre_producto)}</span>
            </div>
            <div class="cc-detail-row">
                <span class="cc-lbl">Descuento</span>
                <span class="cc-val fw-bold text-success">${esc(r.porcentaje_descuento)}%</span>
            </div>
            <div class="cc-detail-row">
                <span class="cc-lbl">Adeudo original</span>
                <span class="cc-val">${fmtN(r.adeudo_total_original)}${baseLabel ? ` <span style="font-size:.7rem;color:#6b7a90;">(Calculado sobre ${baseLabel})</span>` : ''}</span>
            </div>
            <div class="cc-detail-row">
                <span class="cc-lbl">Total pagado</span>
                <span class="cc-val fw-bold text-success">${fmtN(r.total_a_pagar)}</span>
            </div>
            <div class="cc-detail-row">
                <span class="cc-lbl">Registrado por</span>
                <span class="cc-val">${esc(r.usuario_alta)}</span>
            </div>
            <div class="cc-detail-row">
                <span class="cc-lbl">En proceso desde</span>
                <span class="cc-val">${fmtFecha(r.fecha_actualizacion || r.fecha_alta)}</span>
            </div>
            ${_epEsperaBanner(r.fecha_alta)}
            <div class="cc-doccheck-wrap mt-1">
                <div class="cc-doccheck-title"><i class="fa-solid fa-paperclip me-1"></i>Documentos adjuntos</div>
                <div class="cc-doccheck-items">${pdfBadge}${compBadge}</div>
            </div>
            ${_s2InfoHtml(r)}
            ${parseInt(r.vobo_validado_direccion || 0) === 1 ? `
            <div class="cc-vobo-ok-banner">
                <i class="fa-solid fa-circle-check"></i>
                <span>Validado por dirección de cobranza${r.vobo_fecha_validacion ? ' · ' + fmtFecha(r.vobo_fecha_validacion) : ''}</span>
            </div>` : ''}
        </div>
        <hr style="border-color:#e2e8f0;margin:.5rem 0 .75rem;">`;
    }

    // ── Vista Lista: fila compacta horizontal ──
    function buildEnProcesoListRow(r) {
        const fmtN   = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
        const numCon = parseInt(r.comprobantes_subidos) || 0;
        const numTot = parseInt(r.comprobantes_total)   || 0;
        const compOk = numTot > 0 && numCon === numTot;
        const todoListo = pdfOk && compOk;

        const estadoBadge = todoListo
            ? `<span class="badge cc-badge-listo"><i class="fa-solid fa-circle-check me-1"></i>Listo</span>`
            : `<span class="badge cc-badge-docs-pend"><i class="fa-solid fa-triangle-exclamation me-1"></i>Docs pendientes</span>`;

        const pdfIcon = pdfOk
            ? `<span class="cc-doc-icon-ok" title="PDF adjunto"><i class="fa-solid fa-file-pdf"></i></span>`
            : `<span class="cc-doc-icon-missing" title="Sin PDF"><i class="fa-solid fa-file-pdf"></i></span>`;

        const compIcon = numTot === 0
            ? `<span class="cc-doc-icon-none" title="Sin comprobantes"><i class="fa-solid fa-receipt"></i></span>`
            : compOk
                ? `<span class="cc-doc-icon-ok" title="Comprobantes ${numCon}/${numTot}"><i class="fa-solid fa-receipt"></i> ${numCon}/${numTot}</span>`
                : `<span class="cc-doc-icon-partial" title="Comprobantes ${numCon}/${numTot}"><i class="fa-solid fa-receipt"></i> ${numCon}/${numTot}</span>`;

        const pdfLink = pdfOk
            ? `<a href="${esc(r.pdf_adjunto)}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;padding:.2rem .5rem;"><i class="fa-solid fa-eye"></i></a>`
            : '';

        return `
        <div class="ep-list-row" id="cc-ep-card-${r.id}">
            <div class="ep-lr-info">
                <span class="ep-lr-id">#${esc(r.id_credito)}</span>
                <span class="ep-lr-name">${esc(r.nombre_cliente)}</span>
                <span class="ep-lr-prod">${esc(r.nombre_producto)}</span>
            </div>
            <div class="ep-lr-meta">
                ${_isNew(r.fecha_alta) ? '<span class="cc-badge-nuevo"><i class="fa-solid fa-star"></i>Nuevo</span>' : ''}
                ${_celulaBadge(r)}
                ${estadoBadge}
                <span class="ep-lr-total">${fmtN(r.total_a_pagar)}</span>
                ${pdfIcon}${compIcon}
            </div>
            <div class="ep-lr-actions">
                ${pdfLink}
                <button class="btn btn-sm btn-outline-primary" id="cc-acc-btn-${r.id}"
                        style="font-size:.75rem;padding:.2rem .55rem;" onclick="ccToggleDetalle(${r.id})">
                    <i class="fa-solid fa-table-list me-1"></i>Ver detalle
                </button>
                <a href="/CierreCredito/descargarExcelCierre?id=${r.id}"
                   class="btn btn-sm btn-outline-success" style="font-size:.75rem;padding:.2rem .5rem;">
                    <i class="fa-solid fa-file-excel"></i>
                </a>
                ${parseInt(r.vobo_validado_direccion || 0) !== 1 ? `
                <button class="cc-btn-confirmar" style="font-size:.62rem;padding:.18rem .42rem;background:linear-gradient(135deg,#2563eb,#3b82f6);"
                        onclick="ccAbrirModalVoBo(${r.id})">
                    <i class="fa-solid fa-stamp me-1"></i>Vo.Bo
                </button>` : ''}
                <button class="cc-btn-confirmar" style="font-size:.75rem;padding:.28rem .65rem;background:linear-gradient(135deg,#059669,#10b981);"
                        onclick="ccEnviarACartera(${r.id})" id="cc-ep-btn-${r.id}">
                    <i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera
                </button>
                <button class="cc-btn-descartar" style="font-size:.75rem;padding:.28rem .55rem;"
                        onclick="ccDescartar(${r.id})" title="Descartar">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
            </div>
        </div>`;
    }

    function buildEnProcesoCard(r) {
        const fmtN   = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
        const numCon = parseInt(r.comprobantes_subidos) || 0;
        const numTot = parseInt(r.comprobantes_total)   || 0;
        const compOk = numTot > 0 && numCon === numTot;
        const todoListo = pdfOk && compOk;

        const pdfBadge = pdfOk
            ? `<a href="${esc(r.pdf_adjunto)}" target="_blank" class="cc-doc-ok" style="text-decoration:none;cursor:pointer;"><i class="fa-solid fa-file-pdf me-1"></i>PDF Convenio</a>`
            : `<span class="cc-doc-missing"><i class="fa-solid fa-file-pdf me-1"></i>Sin PDF convenio</span>`;

        let compBadge;
        if (numTot === 0) {
            compBadge = `<span class="cc-doc-missing"><i class="fa-solid fa-receipt me-1"></i>Sin comprobantes</span>`;
        } else if (compOk) {
            compBadge = `<span class="cc-doc-ok"><i class="fa-solid fa-receipt me-1"></i>Comprobantes ${numCon}/${numTot}</span>`;
        } else {
            compBadge = `<span class="cc-doc-partial"><i class="fa-solid fa-receipt me-1"></i>Comprobantes ${numCon}/${numTot}</span>`;
        }

        const pdfLink = pdfOk
            ? `<a href="${esc(r.pdf_adjunto)}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;"><i class="fa-solid fa-eye me-1"></i>Ver PDF</a>`
            : '';

        return `
        <div class="cc-conv-card h-100" id="cc-ep-card-${r.id}" style="width:100%;">

                <!-- Cabecera -->
                <div class="cc-conv-card-header" style="flex-direction:column;align-items:stretch;gap:.3rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                        <span class="cc-credito-id" style="flex:0 0 auto;">#${esc(r.id_credito)}${_isNew(r.fecha_alta) ? ' <span class="cc-badge-nuevo"><i class="fa-solid fa-star"></i>Nuevo</span>' : ''}</span>
                        <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                            ${_celulaBadge(r)}
                            <button class="cc-btn-descartar" onclick="ccDescartar(${r.id})"
                                    title="Descartar — regresar a Validacion de cierre">
                                <i class="fa-solid fa-rotate-left"></i>Descartar
                            </button>
                        </div>
                    </div>
                    <span style="color:#fff;font-weight:600;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(r.nombre_cliente)}</span>
                </div>

                <!-- Resumen rápido -->
                <div class="cc-conv-card-body">
                    <div class="cc-conv-details">
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Producto</span>
                            <span class="cc-val">${esc(r.nombre_producto)}</span>
                            <span class="cc-pct-badge" style="margin-left:.35rem;">
                                ${parseFloat(r.porcentaje_descuento) || 0}%
                            </span>
                        </div>
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Adeudo original</span>
                            <span class="cc-val fw-bold" style="color:#dc2626;">${fmtN(parseFloat(r.adeudo_total_original) || 0)}${r.base_calculo ? ' <span style="font-size:.72rem;color:#6b7a90;font-weight:400;">(Calculado sobre ' + (r.base_calculo === 'saldo_total_capital' ? 'Capital' : r.base_calculo === 'adeudo_total' ? 'Total' : r.base_calculo === 'interes' ? 'Interés' : r.base_calculo) + ')</span>' : ''}</span>
                        </div>
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Total pagado</span>
                            <span class="cc-val fw-bold text-success">${fmtN(r.total_a_pagar)}</span>
                        </div>
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Registrado por</span>
                            <span class="cc-val">${esc(r.usuario_alta)}</span>
                        </div>
                        <div class="cc-detail-row">
                            <span class="cc-lbl">En proceso desde</span>
                            <span class="cc-val">${fmtFecha(r.fecha_actualizacion || r.fecha_alta)}</span>
                        </div>
                        ${_epEsperaBanner(r.fecha_alta)}
                        <div class="cc-doccheck-wrap mt-2">
                            <div class="cc-doccheck-title"><i class="fa-solid fa-paperclip me-1"></i>Documentos adjuntos</div>
                            <div class="cc-doccheck-items">${pdfBadge}${compBadge}</div>
                        </div>
                        ${_s2InfoHtml(r)}
                        ${parseInt(r.vobo_validado_direccion || 0) === 1 ? `
                        <div class="cc-vobo-ok-banner">
                            <i class="fa-solid fa-badge-check"></i>
                            <span>Validado por dirección de cobranza${r.vobo_fecha_validacion ? ' · ' + fmtFecha(r.vobo_fecha_validacion) : ''}</span>
                        </div>` : ''}
                        ${r.fecha_envio_cartera ? `
                        <div class="cc-envio-fallido-banner">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Envío previo fallido — correo no notificado</span>
                        </div>` : ''}
                    </div>
                </div>

                <!-- Footer -->
                <div class="cc-conv-card-footer" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                    ${pdfLink}
                    <button class="btn btn-sm btn-outline-primary" id="cc-acc-btn-${r.id}"
                            style="font-size:.78rem;" onclick="ccToggleDetalle(${r.id})">
                        <i class="fa-solid fa-table-list me-1"></i>Ver detalle
                    </button>
                    <a href="/CierreCredito/descargarExcelCierre?id=${r.id}"
                       class="btn btn-sm btn-outline-success" style="font-size:.78rem;">
                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                    </a>
                    ${parseInt(r.vobo_validado_direccion || 0) !== 1 ? `
                    <button class="cc-btn-confirmar"
                            style="background:linear-gradient(135deg,#2563eb,#3b82f6);font-size:.62rem;padding:.18rem .42rem;flex:0 0 auto;min-width:unset;"
                            onclick="ccAbrirModalVoBo(${r.id})">
                        <i class="fa-solid fa-stamp me-1"></i>Vo.Bo
                    </button>` : ''}
                    <button class="cc-btn-confirmar"
                            style="background:linear-gradient(135deg,#059669,#10b981);flex:1 1 45%;min-width:100px;"
                            onclick="ccEnviarACartera(${r.id})" id="cc-ep-btn-${r.id}">
                        <i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera
                    </button>
                </div>

        </div>`;
    }

    
    /* ══════════════════════════════════
       RENDER: TABLA CONVENIOS (Tab 0)
    ══════════════════════════════════ */
    let _tablaConv = null;
    let _tablaHist = null;
    let _tablaVoBo = null;

    /* ── Helpers para la DataTable de Historial ── */
    const _histEtiqueta = (r) => {
        if (r.estatus === 'enviado_cartera' && r.email_destino_cartera)
            return `<span class="badge rounded-pill cc-hist-enviado-ok"><i class="fa-solid fa-circle-check me-1"></i>Enviado &mdash; correo notificado</span>`;
        if (r.estatus === 'enviado_cartera')
            return `<span class="badge rounded-pill cc-hist-enviado-warn"><i class="fa-solid fa-triangle-exclamation me-1"></i>Limite de envios rebasado &mdash; sin correo notificado</span>`;
        if (r.estatus === 'en_cola')
            return `<div style="display:inline-flex;flex-direction:column;gap:5px;align-items:flex-start;">
                        <span class="badge rounded-pill cc-hist-enviado-warn"><i class="fa-solid fa-triangle-exclamation me-1"></i>Limite de envios rebasado &mdash; sin correo notificado</span>
                        <button onclick="ccMarcarListoEnvio(${r.id})" style="font-size:.73rem;padding:2px 9px;background:#fffbeb;border:1px solid #fcd34d;color:#92400e;border-radius:6px;white-space:nowrap;cursor:pointer;"><i class="fa-solid fa-check me-1"></i>Marcar listo para reenv&iacute;o</button>
                    </div>`;
        if (r.estatus === 'listo_envio')
            return `<div style="display:inline-flex;flex-direction:column;gap:5px;align-items:flex-start;">
                        <span class="badge rounded-pill cc-hist-enviado-warn"><i class="fa-solid fa-triangle-exclamation me-1"></i>Limite de envios rebasado &mdash; sin correo notificado</span>
                        <button onclick="ccReenviarACartera(${r.id})" id="cc-hist-btn-${r.id}" style="font-size:.73rem;padding:2px 9px;background:linear-gradient(135deg,#059669,#10b981);border:none;color:#fff;border-radius:6px;white-space:nowrap;cursor:pointer;"><i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera</button>
                    </div>`;
        if (r.estatus === 'descartado')
            return `<span class="badge rounded-pill cc-hist-descartado"><i class="fa-solid fa-rotate-left me-1"></i>Devuelto a revisi&oacute;n</span>`;
        if (r.estatus === 'notificado_cartera')
            return `<span class="badge rounded-pill cc-hist-notificado"><i class="fa-solid fa-bell me-1"></i>Notificado a cartera</span>`;
        if (r.estatus === 'cerrado')
            return `<span class="badge rounded-pill cc-hist-cerrado"><i class="fa-solid fa-circle-check me-1"></i>Cerrado</span>`;
        if (r.estatus === 'devuelto_cartera')
            return `<span class="badge rounded-pill cc-hist-devuelto-cart"><i class="fa-solid fa-rotate-left me-1"></i>Devuelto por cartera</span>`;
        if (r.estatus === 'envio_cobranza')
            return `<span class="badge rounded-pill cc-hist-notificado"><i class="fa-solid fa-stamp me-1"></i>En Vo.Bo</span>`;
        if (r.estatus === 'vo_bo_rechazado')
            return `<span class="badge rounded-pill cc-hist-descartado"><i class="fa-solid fa-xmark me-1"></i>Vo.Bo rechazado</span>`;
        return `<span class="badge rounded-pill cc-hist-en-proceso"><i class="fa-solid fa-hourglass-half me-1"></i>En Proceso</span>`;
    };

    const _histQuienMovio = (r) => {
        if (r.estatus === 'en_proceso') return esc(r.usuario_alta);
        return esc(r.usuario_actualizacion || r.usuario_alta);
    };

    const _histFechaSort = (r) => {
        if (r.estatus === 'enviado_cartera') return r.fecha_envio_cartera || r.fecha_alta || '';
        if (r.estatus === 'descartado')      return r.fecha_actualizacion || '';
        if (r.estatus === 'en_cola')         return r.fecha_actualizacion || '';
        if (r.estatus === 'listo_envio')     return r.fecha_actualizacion || '';
        if (r.estatus === 'envio_cobranza')  return r.fecha_actualizacion || '';
        if (r.estatus === 'vo_bo_rechazado') return r.fecha_actualizacion || '';
        return r.fecha_alta || '';
    };

    const _histFechaDisplay = (r) => {
        if (r.estatus === 'enviado_cartera') return fmtFecha(r.fecha_envio_cartera);
        if (r.estatus === 'descartado')      return fmtFecha(r.fecha_actualizacion);
        if (r.estatus === 'en_cola')         return fmtFecha(r.fecha_actualizacion);
        if (r.estatus === 'listo_envio')     return fmtFecha(r.fecha_actualizacion);
        if (r.estatus === 'envio_cobranza')  return fmtFecha(r.fecha_actualizacion);
        if (r.estatus === 'vo_bo_rechazado') return fmtFecha(r.fecha_actualizacion);
        return fmtFecha(r.fecha_alta);
    };

    function _initTablaHist() {
        if (_tablaHist) return;
        _tablaHist = $('#tablaHistorialMovs').DataTable({
            data: [],
            columns: [
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort') return String(r.id_credito || '') + ' ' + String(r.nombre_cliente || '');
                        return `<span class="fw-bold ps-1" style="color:#1e293b;white-space:nowrap;">#${esc(r.id_credito)}</span>` +
                               (CC_AMBAS ? `<span style="display:inline-block;margin-left:.4rem;">${_celulaBadge(r)}</span>` : '');
                    }
                },
                {
                    data: 'nombre_cliente',
                    render: function(d, t) {
                        if (t !== 'display') return d || '';
                        return `<span style="font-size:.88rem;">${esc(d || '')}</span>`;
                    }
                },
                {
                    data: 'estatus',
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort') return d || '';
                        return _histEtiqueta(r);
                    }
                },
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort') return r.usuario_actualizacion || r.usuario_alta || '';
                        return `<span style="font-size:.83rem;color:#475569;">${_histQuienMovio(r)}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'sort')   return _histFechaSort(r);
                        if (t === 'filter') return _histFechaDisplay(r);
                        return `<span style="font-size:.83rem;color:#475569;white-space:nowrap;">${_histFechaDisplay(r)}</span>`;
                    }
                }
            ],
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            order: [[4, 'desc']],
            language: {
                emptyTable:   'Sin movimientos registrados',
                infoEmpty:    'Sin registros',
                info:         'Mostrando _START_ a _END_ de _TOTAL_ movimientos',
                infoFiltered: '(filtrado de _MAX_ totales)',
                lengthMenu:   'Mostrar _MENU_ registros',
                search:       'Buscar:',
                zeroRecords:  'Sin resultados para la b&uacute;squeda',
                paginate: { first: '&laquo;', last: '&raquo;', next: '&rsaquo;', previous: '&lsaquo;' }
            },
            dom: '<"d-flex align-items-center flex-wrap gap-2 px-3 py-2"l>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            autoWidth: false,
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
            }
        });
    }

    function _initTablaConv() {
        if (_tablaConv) return;
        _tablaConv = $('#tablaConveniosTodos').DataTable({
            data: [],
            columns: [
                { data: null, orderable: false, searchable: false, className: 'control', defaultContent: '' },
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort')
                            return String(r.id_credito || '') + ' ' + String(r.nombre_cliente || '');
                        return `<span class="fw-bold" style="color:#3b82f6;display:block;">#${esc(r.id_credito)}</span>` +
                               `<span class="text-muted" style="font-size:.78rem;">${esc(r.nombre_cliente)}</span>` +
                               `<span style="display:block;margin-top:.2rem;">${_celulaBadge(r)}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort') return String(r.nombre_producto || '');
                        const pct = parseFloat(r.porcentaje_descuento) || 0;
                        return `<span>${esc(r.nombre_producto)}</span><br>` +
                               `<span class="cc-pct-badge">${pct}%</span>`;
                    }
                },
                {
                    data: 'total_a_pagar',
                    className: 'text-end',
                    render: function(d) { return `<strong class="text-success">${fmt(d)}</strong>`; }
                },
                { data: 'fecha_acuerdo', render: function(d) { return esc(d || '—'); } },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(d, t, r) {
                        const pagadas = parseInt(r.cuotas_pagadas) || 0;
                        const semanas = parseInt(r.numero_semanas) || parseInt(r.num_semanas_amort) || 0;
                        if (!semanas) return '<span class="text-muted">—</span>';
                        const done = pagadas >= semanas
                            ? ' <i class="fa-solid fa-circle-check text-success" style="font-size:.72rem;"></i>' : '';
                        return `<span style="font-size:.83rem;white-space:nowrap;">${pagadas}/${semanas}${done}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort') return (r.estatus || '') + ' ' + (r.estatus_seguimiento || '');
                        let html = convenioEstatusBadge(r.estatus);
                        const seg = r.estatus_seguimiento;
                        if (seg && ['notificado_cartera','cerrado','devuelto_cartera'].indexOf(seg) !== -1) {
                            html += _trailCartera(seg);
                        }
                        return html;
                    }
                },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(d, t, r) {
                        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
                        const compSub = parseInt(r.comprobantes_subidos) || 0;
                        if (!pdfOk && compSub === 0) return '<span class="text-muted">—</span>';
                        let html = '<div style="display:flex;gap:.3rem;flex-wrap:wrap;">';
                        if (pdfOk)
                            html += `<a href="${esc(r.pdf_adjunto)}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary"
                                        style="font-size:.72rem;padding:.2rem .55rem;"
                                        title="Ver PDF del convenio">
                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                     </a>`;
                        if (compSub > 0)
                            html += `<span class="cc-doc-ok" style="padding:2px 9px;font-size:.75rem;font-weight:700;"
                                          title="${compSub} comprobante${compSub !== 1 ? 's' : ''} subido${compSub !== 1 ? 's' : ''}">
                                          <i class="fa-solid fa-receipt me-1"></i>${compSub}
                                     </span>`;
                        html += '</div>';
                        return html;
                    }
                },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(d, t, r) {
                        const nc = esc(r.nombre_cliente || '').replace(/'/g, "\\'");
                        const yaNotif = r.estatus_seguimiento && ['notificado_cartera','cerrado','devuelto_cartera'].indexOf(r.estatus_seguimiento) !== -1;
                        const btnNotif = yaNotif
                            ? `<button class="btn btn-sm btn-outline-secondary d-block w-100 mb-1" style="font-size:.72rem;white-space:nowrap;" disabled title="Ya notificado a cartera"><i class="fa-solid fa-bell-slash" style="font-size:.65rem;"></i> Notificado</button>`
                            : `<button class="btn btn-sm btn-outline-warning d-block w-100 mb-1" style="font-size:.72rem;white-space:nowrap;"` +
                               ` onclick="ccNotificarConvenio(${r.id},${r.id_credito},'${nc}')"` +
                               ` title="Notificar a cartera sobre este convenio">` +
                               `<i class="fa-solid fa-bell" style="font-size:.65rem;"></i> Notificar` +
                               `</button>`;
                        const btnDetalle = `<button class="btn btn-sm btn-outline-primary d-block w-100" style="font-size:.72rem;white-space:nowrap;"` +
                               ` onclick="ccToggleDetalleConv(this,${r.id})"` +
                               ` title="Ver amortización y documentos">` +
                               `<i class="fa-solid fa-chevron-down" style="font-size:.65rem;"></i> Ver detalle` +
                               `</button>`;
                        return `<div style="min-width:90px;">${btnNotif}${btnDetalle}</div>`;
                    }
                },
                { data: 'id', visible: false, searchable: false }
            ],
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            order: [[9, 'desc']],
            responsive: { details: { type: 'column', target: 0 } },
            language: {
                emptyTable:   'Sin convenios registrados',
                infoEmpty:    'Sin registros',
                info:         'Mostrando _START_ a _END_ de _TOTAL_ convenios',
                infoFiltered: '(filtrado de _MAX_ totales)',
                lengthMenu:   'Mostrar _MENU_ registros',
                search:       'Buscar:',
                zeroRecords:  'Sin resultados para la búsqueda',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            autoWidth: false,
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
            }
        });
    }

    function renderConvenios(rows) {
        _allRowsConv = rows;
        document.getElementById('loader-convenios').classList.add('d-none');
        document.getElementById('badge-convenios').textContent = rows.length;

        if (!rows || rows.length === 0) {
            document.getElementById('empty-convenios').classList.remove('d-none');
            return;
        }

        document.getElementById('cc-filtros-conv').classList.remove('d-none');
        document.getElementById('wrap-convenios').classList.remove('d-none');
        _initTablaConv();
        _filtroConvAplicar();
    }

    /* kept so ccFiltrar can call it; delegates to DataTables search */
    function _pintarConvenios(rows) {
        if (_tablaConv) {
            _tablaConv.clear().rows.add(rows).draw();
        }
    }

    /* legacy alias */
    function buildConvenioCard(r) { return r; }
    function buildConvenioRow(r)  { return r; }

    /* ══════════════════════════════════
       ACORDÉON DE DETALLE (Tab 2)
    ══════════════════════════════════ */
    const _detalleCache    = {};
    const _detalleConvCache = {};

    // ── Helper: cierra el panel de detalle de un chunk y restaura layout ──
    function _epCloseChunk(chunkIdx, onDone) {
        const detailCol   = document.getElementById(`ep-chunk-detail-col-${chunkIdx}`);
        if (!detailCol || !detailCol.dataset.openId) { if (onDone) onDone(); return; }
        const openedId  = detailCol.dataset.openId;
        const prevBtn   = document.getElementById(`cc-acc-btn-${openedId}`);
        if (prevBtn) prevBtn.innerHTML = '<i class="fa-solid fa-table-list me-1"></i>Ver detalle';

        // Animar salida del panel
        const innerPanel = detailCol.firstElementChild;
        if (innerPanel) {
            innerPanel.classList.remove('ep-detail-enter');
            innerPanel.classList.add('ep-detail-leave');
        }

        // Clase base de columna según vista actual
        const colClass = _epView === 'list' ? 'col-12' :
                         _epView === '2'    ? 'col-12 col-md-6' : 'col-12 col-md-4';

        const doRestore = () => {
            const chunkRow    = document.getElementById(`ep-chunk-row-${chunkIdx}`);
            const overflowRow = document.getElementById(`ep-chunk-overflow-${chunkIdx}`);
            const allCols = [
                ...(chunkRow    ? [...chunkRow.querySelectorAll('[data-ep-chunk]')]    : []),
                ...(overflowRow ? [...overflowRow.querySelectorAll('[data-ep-chunk]')] : [])
            ].sort((a, b) => parseInt(a.dataset.epOrder) - parseInt(b.dataset.epOrder));
            allCols.forEach(col => {
                col.className = `${colClass} ep-card-drop`;
                if (chunkRow) chunkRow.appendChild(col);
                col.addEventListener('animationend', () => col.classList.remove('ep-card-drop'), { once: true });
            });
            detailCol.remove();
            if (overflowRow) overflowRow.remove();
            if (onDone) onDone();
        };

        if (innerPanel) {
            innerPanel.addEventListener('animationend', doRestore, { once: true });
        } else {
            doRestore();
        }
    }

    window.ccToggleDetalle = function(id) {
        const btn    = document.getElementById(`cc-acc-btn-${id}`);
        const colEl  = document.getElementById(`cc-ep-col-${id}`);

        // ── En Proceso tab: panel lateral derecho, 4+8 columnas ──
        if (colEl) {
            const chunkIdx  = parseInt(colEl.dataset.epChunk);
            const chunkRow  = document.getElementById(`ep-chunk-row-${chunkIdx}`);
            const detailCol = document.getElementById(`ep-chunk-detail-col-${chunkIdx}`);
            const isOpen    = detailCol && detailCol.dataset.openId === String(id);

            // Cierra cualquier detalle abierto en cualquier chunk
            // Si hay uno abierto esperamos a que termine su animación antes de abrir el nuevo
            const openChunks = [...document.querySelectorAll('[id^="ep-chunk-detail-col-"]')];
            let pendingClose = openChunks.length;

            const doOpen = () => {
                if (isOpen) return; // estaba abierto → se cerró, listo

                // Abrir: reorganizar chunk row
                if (btn) btn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Cerrar';

                // Columnas según vista activa
                const colCard   = _epView === 'list' ? 'col-12'
                                : _epView === '2'    ? 'col-12 col-md-6'
                                :                     'col-12 col-md-4';
                const colDetail = _epView === 'list' ? 'col-12'
                                : _epView === '2'    ? 'col-12 col-md-6'
                                :                     'col-12 col-md-8';
                const colOther  = _epView === '2'    ? 'col-12 col-md-6'
                                : _epView === 'list' ? 'col-12'
                                :                     'col-12 col-md-4';

                // Recolectar y ordenar todas las cards del chunk (están todas en chunkRow todavía)
                const allCols = [...chunkRow.querySelectorAll('[data-ep-chunk]')]
                    .sort((a, b) => parseInt(a.dataset.epOrder) - parseInt(b.dataset.epOrder));
                const otherCols = allCols.filter(el => el.id !== `cc-ep-col-${id}`);

                // Limpiar chunk row
                while (chunkRow.firstChild) chunkRow.removeChild(chunkRow.firstChild);

                // Card expandida
                colEl.className = colCard;
                chunkRow.appendChild(colEl);

                // Panel de detalle, con animación de entrada
                const newDetailCol = document.createElement('div');
                newDetailCol.id        = `ep-chunk-detail-col-${chunkIdx}`;
                newDetailCol.className = colDetail;
                newDetailCol.dataset.openId = String(id);
                newDetailCol.innerHTML = `
                    <div class="ep-detail-enter cc-ep-detail-panel">
                        <div class="cc-side-panel-header">
                            <span class="cc-sp-title"><i class="fa-solid fa-table-list me-1"></i>Detalle del cierre</span>
                            <button class="cc-side-panel-close" onclick="ccToggleDetalle(${id})" title="Cerrar panel">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div id="cc-acc-loader-${id}" class="text-center py-3 text-muted">
                            <i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...
                        </div>
                        <div id="cc-acc-content-${id}" style="overflow-y:auto;"></div>
                    </div>`;
                chunkRow.appendChild(newDetailCol);

                // Cards restantes van en fila de desbordamiento debajo con animación
                if (otherCols.length > 0) {
                    const overflowRow = document.createElement('div');
                    overflowRow.id        = `ep-chunk-overflow-${chunkIdx}`;
                    overflowRow.className = 'row g-3 mt-0 mb-2';
                    otherCols.forEach((col, i) => {
                        col.className = `${colOther} ep-card-drop`;
                        col.style.animationDelay = `${i * 50}ms`;
                        col.addEventListener('animationend', () => {
                            col.classList.remove('ep-card-drop');
                            col.style.animationDelay = '';
                        }, { once: true });
                        overflowRow.appendChild(col);
                    });
                    chunkRow.insertAdjacentElement('afterend', overflowRow);
                }

                // Fetch detalle (ahora los nodos ya existen en el DOM)
                const loader  = document.getElementById(`cc-acc-loader-${id}`);
                const content = document.getElementById(`cc-acc-content-${id}`);

                // Vista lista: pre-poblar con resumen del registro inmediatamente
                if (_epView === 'list' && colEl && colEl.dataset.epRow) {
                    try {
                        const rowData = JSON.parse(colEl.dataset.epRow);
                        content.innerHTML = _buildEpListSummaryHtml(rowData)
                            + `<div id="cc-acc-amort-loading-${id}" class="text-center py-2 text-muted" style="font-size:.8rem;"><i class="fa-solid fa-spinner fa-spin me-1"></i>Cargando amortización...</div>`
                            + `<div id="cc-acc-amort-wrap-${id}"></div>`;
                        if (loader) loader.style.display = 'none';
                    } catch(e) {}
                }

                if (_detalleCache[id]) {
                    if (loader) loader.style.display = 'none';
                    const amortWrap = document.getElementById(`cc-acc-amort-wrap-${id}`);
                    if (amortWrap) {
                        const amortLoading = document.getElementById(`cc-acc-amort-loading-${id}`);
                        if (amortLoading) amortLoading.remove();
                        amortWrap.innerHTML = buildDetalleHtml(_detalleCache[id]);
                    } else {
                        content.innerHTML = buildDetalleHtml(_detalleCache[id]);
                    }
                    return;
                }

                fetch('/CierreCredito/getDetalleCierre', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                })
                .then(r => r.json())
                .then(res => {
                    if (loader) loader.style.display = 'none';
                    if (!res.success) throw new Error(res.mensaje);
                    _detalleCache[id] = res.datos;
                    const amortWrap = document.getElementById(`cc-acc-amort-wrap-${id}`);
                    if (amortWrap) {
                        const amortLoading = document.getElementById(`cc-acc-amort-loading-${id}`);
                        if (amortLoading) amortLoading.remove();
                        amortWrap.innerHTML = buildDetalleHtml(res.datos);
                    } else {
                        content.innerHTML = buildDetalleHtml(res.datos);
                    }
                })
                .catch(err => {
                    if (loader) loader.style.display = 'none';
                    content.innerHTML = `<div class="alert alert-danger py-2">Error: ${esc(err.message)}</div>`;
                });
            };

            if (openChunks.length === 0) {
                doOpen();
            } else {
                openChunks.forEach(el => {
                    const ci = parseInt(el.id.replace('ep-chunk-detail-col-', ''));
                    _epCloseChunk(ci, () => {
                        pendingClose--;
                        if (pendingClose === 0) doOpen();
                    });
                });
            }
            return;
        }

        // ── Validación tab: original side-panel approach ──
        const panel  = document.getElementById(`cc-acc-body-${id}`);
        const card   = document.getElementById(`cc-ep-card-${id}`) || document.getElementById(`cc-card-${id}`);
        const loader = document.getElementById(`cc-acc-loader-${id}`);
        const content = document.getElementById(`cc-acc-content-${id}`);
        const isOpen = panel && panel.classList.contains('open');

        if (isOpen) {
            panel.classList.remove('open');
            if (card) card.classList.remove('cc-has-panel');
            if (btn) btn.innerHTML = '<i class="fa-solid fa-table-list me-1"></i>Ver detalle';
            return;
        }

        if (panel) panel.classList.add('open');
        if (card) card.classList.add('cc-has-panel');
        if (btn) btn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Cerrar';

        if (_detalleCache[id]) {
            content.innerHTML = buildDetalleHtml(_detalleCache[id]);
            return;
        }

        loader.style.display = '';
        content.innerHTML = '';

        fetch('/CierreCredito/getDetalleCierre', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(res => {
            loader.style.display = 'none';
            if (!res.success) throw new Error(res.mensaje);
            _detalleCache[id] = res.datos;
            content.innerHTML = buildDetalleHtml(res.datos);
        })
        .catch(err => {
            loader.style.display = 'none';
            content.innerHTML = `<div class="alert alert-danger py-2">Error: ${esc(err.message)}</div>`;
        });
    };

    function buildDetalleHtml(d) {
        const fmtN = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const conv = d.convenio || {};
        const amort = d.amortizacion || [];

        // ── Resumen financiero ──
        const resumenHtml = `
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem;margin-bottom:1rem;">
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Adeudo original</div>
                <div class="cc-det-danger">${fmtN(conv.adeudo_total_original)}</div>
                ${conv.base_calculo ? '<div style="font-size:.7rem;color:#6b7a90;margin-top:2px;">Calculado sobre: ' + (conv.base_calculo === 'saldo_total_capital' ? 'Capital' : conv.base_calculo === 'adeudo_total' ? 'Total' : conv.base_calculo === 'interes' ? 'Interés' : conv.base_calculo) + '</div>' : ''}
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Descuento (${esc(conv.porcentaje_descuento)}%)</div>
                <div class="cc-det-success">- ${fmtN(conv.descuento_monto)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Total a pagar</div>
                <div class="cc-det-primary">${fmtN(conv.total_a_pagar)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Pago semanal</div>
                <div class="cc-det-secondary">${fmtN(conv.pago_semanal)}</div>
            </div>
        </div>`;

        // ── Tabla de amortización ──
        let filasAmort = '';
        if (amort.length === 0) {
            filasAmort = `<tr><td colspan="7" class="text-center text-muted py-3">Sin filas de amortización registradas.</td></tr>`;
        } else {
            filasAmort = amort.map(a => {
                const pagada = (a.estatus_pago === 'pagado');
                const cls    = pagada ? 'pagada' : 'pendiente';
                const icon   = pagada
                    ? `<i class="fa-solid fa-circle-check text-success"></i>`
                    : `<i class="fa-regular fa-clock text-warning"></i>`;
                const compIcon = a.comprobante_path
                    ? `<a href="${esc(a.comprobante_path)}" target="_blank" style="color:#16a34a;text-decoration:none;font-size:.78rem;white-space:nowrap;"><i class="fa-solid fa-paperclip me-1"></i>Ver Comprobante</a>`
                    : `<i class="fa-solid fa-minus text-muted"></i>`;
                return `
                <tr class="${cls}">
                    <td class="text-center fw-bold">${esc(a.numero_semana)}</td>
                    <td>${esc(a.fecha_pago || '—')}</td>
                    <td class="text-end">${fmtN(a.pago_semanal)}</td>
                    <td class="text-end">${fmtN(a.capital)}</td>
                    <td class="text-end">${fmtN(a.saldo_restante)}</td>
                    <td class="text-center">${icon} ${pagada ? 'Pagado' : 'Pendiente'}</td>
                    <td class="text-center">${compIcon}</td>
                </tr>`;
            }).join('');
        }

        const tablaAmort = `
        <div class="cc-det-section-title">
            <i class="fa-solid fa-table-list me-1"></i>Tabla de amortización
        </div>
        <div style="overflow-x:auto;">
            <table class="cc-amort-table">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Fecha pago</th>
                        <th class="text-end">Pago</th>
                        <th class="text-end">Capital</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-center">Comprobante</th>
                    </tr>
                </thead>
                <tbody>${filasAmort}</tbody>
            </table>
        </div>`;

        return resumenHtml + tablaAmort;
    }

    /* ══════════════════════════════════
       TOGGLE Y DETALLE — TAB CONVENIOS (Tab 0)
    ══════════════════════════════════ */
    window.ccToggleDetalleConv = function(btn, id) {
        if (!_tablaConv) return;
        const tr  = $(btn).closest('tr');
        const row = _tablaConv.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            btn.innerHTML = '<i class="fa-solid fa-chevron-down" style="font-size:.65rem;"></i> Ver detalle';
            btn.classList.replace('btn-primary', 'btn-outline-primary');
            return;
        }

        btn.innerHTML = '<i class="fa-solid fa-chevron-up" style="font-size:.65rem;"></i> Cerrar';
        btn.classList.replace('btn-outline-primary', 'btn-primary');
        tr.addClass('shown');

        const rowData = row.data();
        if (_detalleConvCache[id]) {
            row.child(`<div class="cc-conv-detail-inner">${buildDetalleConvHtml(_detalleConvCache[id], rowData)}</div>`).show();
            return;
        }

        row.child('<div class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...</div>').show();

        fetch('/CierreCredito/getDetalleConvenio', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.mensaje);
            _detalleConvCache[id] = res.datos;
            row.child(`<div class="cc-conv-detail-inner">${buildDetalleConvHtml(res.datos, rowData)}</div>`).show();
        })
        .catch(err => {
            row.child(`<div class="alert alert-danger m-2 py-2">Error: ${esc(err.message)}</div>`).show();
        });
    };

    /* ══════════════════════════════════
       NOTIFICAR CONVENIO A CARTERA
    ══════════════════════════════════ */
    window.ccNotificarConvenio = function(idConvenio, idCredito, nombreCliente) {
        Swal.fire({
            title: '¿Notificar a Cartera?',
            html: `Se enviará un correo a Cartera con los detalles del convenio de <strong>${nombreCliente}</strong> (crédito <strong>${idCredito}</strong>) junto con la tabla de amortización.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-bell me-1"></i>Enviar notificación',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#f59e0b',
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Enviando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch('/CierreCredito/notificarConvenio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_convenio=${idConvenio}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({
                    icon: 'success',
                    title: 'Notificación enviada',
                    text: res.mensaje || 'Cartera fue notificada exitosamente.',
                    timer: 2500, showConfirmButton: false
                });
                if (CC_P.convenios)  cargarConvenios().catch(() => {});
                if (CC_P.cartera)    cargarCartera().catch(() => {});
                if (CC_P.historial)  cargarHistorial().catch(() => {});
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    /* ══════════════════════════════════
       CERRAR CONVENIO (cartera)
    ══════════════════════════════════ */
    window.ccCerrarConvenio = function(idCierre, idCredito, nombreCliente) {
        Swal.fire({
            title: '¿Cerrar convenio?',
            html: `Confirma que el convenio de <strong>${nombreCliente}</strong> (crédito <strong>${idCredito}</strong>) ha sido revisado y aprobado por Cartera.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-circle-check me-1"></i>Cerrar convenio',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#1d4ed8',
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch('/CierreCredito/cerrarConvenioCartera', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idCierre}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({
                    icon: 'success',
                    title: 'Convenio cerrado',
                    text: res.mensaje || 'El convenio ha sido marcado como cerrado.',
                    timer: 2000, showConfirmButton: false
                });
                cargarCartera().catch(() => {});
                if (CC_P.historial) cargarHistorial().catch(() => {});
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    /* ══════════════════════════════════
       DEVOLVER POR CARTERA
    ══════════════════════════════════ */
    window.ccDevolverPorCartera = function(idCierre, idCredito, nombreCliente) {
        Swal.fire({
            title: 'Devolver al despacho',
            html: `Indica el motivo por el que se devuelve el convenio de <strong>${nombreCliente}</strong> (crédito <strong>${idCredito}</strong>).`,
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Motivo de devolución',
            inputPlaceholder: 'Escribe el motivo...',
            inputAttributes: { maxlength: 500, rows: 3 },
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-rotate-left me-1"></i>Devolver',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#b91c1c',
            preConfirm: (comentario) => {
                if (!comentario || !comentario.trim()) {
                    Swal.showValidationMessage('El motivo es requerido.');
                    return false;
                }
                return comentario.trim();
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const params = new URLSearchParams({ id: idCierre, comentario: result.value });
            fetch('/CierreCredito/devolverPorCartera', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({
                    icon: 'success',
                    title: 'Devuelto',
                    text: res.mensaje || 'El convenio fue marcado como devuelto por cartera.',
                    timer: 2000, showConfirmButton: false
                });
                // Limpiar caché de detalle para que se re-cargue con el comentario
                Object.keys(_detalleConvCache).forEach(k => delete _detalleConvCache[k]);
                cargarCartera().catch(() => {});
                if (CC_P.convenios)  cargarConvenios().catch(() => {});
                if (CC_P.historial)  cargarHistorial().catch(() => {});
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    function buildDetalleConvHtml(d, rowData) {
        const fmtN = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const conv  = d.convenio     || {};
        const amort = d.amortizacion || [];

        // ── Registrado por ──
        const altaHtml = conv.usuario_alta
            ? `<div class="cc-det-registered-by">
                   <i class="fa-solid fa-user-pen me-1"></i>
                   <span style="font-weight:600;">Registrado por:</span> ${esc(conv.usuario_alta)}
               </div>`
            : '';

        // ── Resumen financiero ──
        const resumenHtml = `
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:.75rem;margin-bottom:1rem;">
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Adeudo original</div>
                <div class="cc-det-danger">${fmtN(conv.adeudo_total_original)}${conv.base_calculo ? ' <span style="font-size:.72rem;color:#6b7a90;font-weight:400;">(Calculado sobre ' + (conv.base_calculo === 'saldo_total_capital' ? 'Capital' : conv.base_calculo === 'adeudo_total' ? 'Total' : conv.base_calculo === 'interes' ? 'Interés' : conv.base_calculo) + ')</span>' : ''}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Descuento (${esc(conv.porcentaje_descuento)}%)</div>
                <div class="cc-det-success">- ${fmtN(conv.descuento_monto)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Total a pagar</div>
                <div class="cc-det-primary">${fmtN(conv.total_a_pagar)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Pago semanal</div>
                <div class="cc-det-secondary">${fmtN(conv.pago_semanal)}</div>
            </div>
        </div>
        ${rowData ? _s2InfoHtml(rowData) : ''}
        ${rowData && parseInt(rowData.vobo_validado_direccion || 0) === 1 ? `
        <div class="cc-vobo-ok-banner">
            <i class="fa-solid fa-circle-check"></i>
            <span>Validado por dirección de cobranza${rowData.vobo_fecha_validacion ? ' · ' + fmtFecha(rowData.vobo_fecha_validacion) : ''}</span>
        </div>` : ''}`;

        // ── Tabla de amortización con documentos ──
        let filasAmort = '';
        if (amort.length === 0) {
            filasAmort = `<tr><td colspan="5" class="text-center text-muted py-3">Sin amortización registrada.</td></tr>`;
        } else {
            filasAmort = amort.map(a => {
                const pagada = (a.estatus_pago === 'pagado');
                const cls    = pagada ? 'pagada' : 'pendiente';
                const icon   = pagada
                    ? `<i class="fa-solid fa-circle-check text-success"></i>`
                    : `<i class="fa-regular fa-clock text-warning"></i>`;
                const compHtml = a.comprobante_path
                    ? `<a href="${esc(a.comprobante_path)}" target="_blank"
                              style="font-size:.72rem;padding:2px 8px;border-radius:4px;
                                     background:#dcfce7;color:#15803d;text-decoration:none;white-space:nowrap;">
                           <i class="fa-solid fa-paperclip me-1"></i>Ver ticket
                       </a>`
                    : `<span style="color:#94a3b8;font-size:.75rem;">—</span>`;

                return `
                <tr class="${cls}">
                    <td class="text-center fw-bold">${esc(a.numero_semana)}</td>
                    <td>${esc(a.fecha_pago || '—')}</td>
                    <td class="text-end">${fmtN(a.pago_semanal)}</td>
                    <td class="text-center">${icon} ${pagada ? 'Pagado' : 'Pendiente'}</td>
                    <td class="text-center">${compHtml}</td>
                </tr>`;
            }).join('');
        }

        const tablaAmort = `
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#1d4ed8;margin-bottom:.5rem;">
            <i class="fa-solid fa-table-list me-1"></i>Amortización y tickets de pago
        </div>
        <div style="overflow-x:auto;">
            <table class="cc-amort-table">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Fecha pago</th>
                        <th class="text-end">Pago</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-center">Comprobante</th>
                    </tr>
                </thead>
                <tbody>${filasAmort}</tbody>
            </table>
        </div>`;

        // ── Comentario de devolución por cartera ──
        let devolucionHtml = '';
        if (conv.comentario_devolucion_cartera) {
            const usuarioDev = conv.usuario_devolucion_cartera ? esc(conv.usuario_devolucion_cartera) : 'Cartera';
            const fechaDev   = conv.fecha_devolucion_cartera  ? esc(conv.fecha_devolucion_cartera)  : '';
            devolucionHtml = `
            <div style="margin-top:.75rem;padding:.75rem 1rem;border-radius:.5rem;background:#fef2f2;border:1px solid #fecaca;">
                <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#b91c1c;margin-bottom:.35rem;">
                    <i class="fa-solid fa-rotate-left me-1"></i>Devuelto por cartera
                </div>
                <div style="font-size:.85rem;color:#991b1b;white-space:pre-line;">${esc(conv.comentario_devolucion_cartera)}</div>
                <div style="font-size:.72rem;color:#9ca3af;margin-top:.35rem;">
                    <i class="fa-solid fa-user me-1"></i>${usuarioDev}${fechaDev ? ' &middot; ' + fechaDev : ''}
                </div>
            </div>`;
        }

        return altaHtml + resumenHtml + tablaAmort + devolucionHtml;
    }

    /* ══════════════════════════════════
       PESTAÑA VO.BO
    ══════════════════════════════════ */
    function _initTablaVoBo() {
        if (_tablaVoBo) return;
        _tablaVoBo = $('#tablaVoBo').DataTable({
            data: [],
            columns: [
                {
                    data: 'id_credito',
                    render: function(d, t, r) {
                        if (t !== 'display') return d || '';
                        return `<strong>#${esc(d)}</strong>${CC_AMBAS ? `<div style="margin-top:.2rem;">${_celulaBadge(r)}</div>` : ''}`;
                    }
                },
                { data: 'nombre_cliente', render: function(d) { return esc(d || '—'); } },
                { data: 'vobo_comentario', render: function(d) { return `<span style="font-size:.83rem;">${esc(d || '—')}</span>`; } },
                {
                    data: 'vobo_archivo',
                    orderable: false,
                    render: function(d) {
                        if (!d) return '<span class="text-muted">—</span>';
                        return `<a href="${esc(d)}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;"><i class="fa-solid fa-paperclip me-1"></i>Ver</a>`;
                    }
                },
                { data: 'usuario_actualizacion', render: function(d) { return esc(d || '—'); } },
                {
                    data: 'fecha_actualizacion',
                    render: function(d, t) {
                        if (t === 'sort') return d || '';
                        return fmtFecha(d);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(d, t, r) {
                        return `<div style="display:flex;gap:.35rem;min-width:170px;">
                            <button class="btn btn-sm btn-success" style="font-size:.72rem;" onclick="ccAprobarVoBo(${r.id})">
                                <i class="fa-solid fa-check me-1"></i>Aprobar
                            </button>
                            <button class="btn btn-sm btn-danger" style="font-size:.72rem;" onclick="ccRechazarVoBo(${r.id})">
                                <i class="fa-solid fa-xmark me-1"></i>Rechazar
                            </button>
                        </div>`;
                    }
                }
            ],
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50],
            order: [[5, 'desc']],
            language: {
                emptyTable:   'Sin registros en Vo.Bo',
                infoEmpty:    'Sin registros',
                info:         'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoFiltered: '(filtrado de _MAX_ totales)',
                lengthMenu:   'Mostrar _MENU_ registros',
                search:       'Buscar:',
                zeroRecords:  'Sin resultados para la búsqueda',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            autoWidth: false,
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
        });
    }

    function renderVoBo(rows) {
        _allRowsVoBo = rows || [];
        const loader = document.getElementById('loader-vobo');
        const wrap   = document.getElementById('wrap-vobo');
        const empty  = document.getElementById('empty-vobo');
        const badge  = document.getElementById('badge-vobo');
        if (loader) loader.classList.add('d-none');
        if (badge) badge.textContent = String(_allRowsVoBo.length);

        if (!_allRowsVoBo.length) {
            if (wrap) wrap.classList.add('d-none');
            if (empty) empty.classList.remove('d-none');
            return;
        }

        if (empty) empty.classList.add('d-none');
        if (wrap) wrap.classList.remove('d-none');
        _initTablaVoBo();
        _tablaVoBo.clear().rows.add(_allRowsVoBo).draw();
        const t = document.getElementById('barraGeneral-input').value.trim();
        if (t) _tablaVoBo.search(t).draw();
    }

    /* ══════════════════════════════════
       CARGA DE DATOS
    ══════════════════════════════════ */

    // ── Funciones de carga (también usadas para refrescar individualmente) ──
    function cargarConvenios() {
        return fetch('/CierreCredito/getAllConvenios', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderConvenios(res.datos);
            });
    }

    function cargarEnviadoFinalizado() {
        return fetch('/CierreCredito/getEnviadoFinalizado', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderCards(res.datos, res.validador || '—');
            });
    }

    function cargarEnProceso() {
        return fetch('/CierreCredito/getEnProceso', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderEnProceso(res.datos);
                enProcesoCargado = true;
            });
    }

    function cargarVoBo() {
        const loader = document.getElementById('loader-vobo');
        const wrap   = document.getElementById('wrap-vobo');
        const empty  = document.getElementById('empty-vobo');
        if (loader) loader.classList.remove('d-none');
        if (wrap) wrap.classList.add('d-none');
        if (empty) empty.classList.add('d-none');
        return fetch('/CierreCredito/getVoBo', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderVoBo(res.datos);
            });
    }

    function cargarHistorial() {
        document.getElementById('loader-historial').classList.remove('d-none');
        document.getElementById('wrap-historial').classList.add('d-none');
        document.getElementById('empty-historial').classList.add('d-none');
        return fetch('/CierreCredito/getHistorial', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderHistorial(res.datos);
            });
    }

    // Refresca historial silenciosamente (llamado tras descartar)
    function refrescarHistorial() {
        cargarHistorial().catch(() => {});
    }

    function cargarCartera() {
        const loaderEl = document.getElementById('loader-cartera');
        const wrapEl   = document.getElementById('wrap-cartera');
        const emptyEl  = document.getElementById('empty-cartera');
        if (loaderEl) loaderEl.classList.remove('d-none');
        if (wrapEl)   wrapEl.classList.add('d-none');
        if (emptyEl)  emptyEl.classList.add('d-none');
        return fetch('/CierreCredito/getCartera', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderCartera(res.datos);
            });
    }

    /* ══════════════════════════════════
       CARGA INICIAL — todas las pestañas en paralelo
    ══════════════════════════════════ */
    function cargarTodo() {
        const hasSwal = typeof Swal !== 'undefined';
        if (hasSwal) {
            Swal.fire({
                title: 'Obteniendo datos...',
                html: '<span style="font-size:.875rem;color:#64748b;">Cargando todas las pestañas</span>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
        }

        const promesas = [];
        if (CC_P.convenios)  promesas.push(cargarConvenios().catch(() => {}));
        if (CC_P.validacion) promesas.push(cargarEnviadoFinalizado().catch(() => {}));
        if (CC_P.en_proceso) promesas.push(cargarEnProceso().catch(() => {}));
        if (CC_P.vobo)       promesas.push(cargarVoBo().catch(() => {}));
        if (CC_P.historial)  promesas.push(cargarHistorial().catch(() => {}));
        if (CC_P.cartera)    promesas.push(cargarCartera().catch(() => {}));
        if (CC_P.peticiones) promesas.push(cargarPeticiones().catch(() => {}));

        Promise.all(promesas).then(() => { if (hasSwal) Swal.close(); });
    }

    // Listeners de pestañas — solo re-aplican el filtro activo (datos ya están renderizados)
    const tabHistorialBtn = document.getElementById('tab-historial-btn');
    if (tabHistorialBtn) {
        tabHistorialBtn.addEventListener('shown.bs.tab', function () {
            const t = document.getElementById('barraGeneral-input').value;
            if (t.trim()) ccFiltrar(t);
        });
    }

    const tabConveniosBtn = document.getElementById('tab-convenios-btn');
    if (tabConveniosBtn) {
        tabConveniosBtn.addEventListener('shown.bs.tab', function () {
            const t = document.getElementById('barraGeneral-input').value;
            if (t.trim()) ccFiltrar(t);
        });
    }

    const tabEnProcesoBtn = document.getElementById('tab-en-proceso-btn');
    if (tabEnProcesoBtn) {
        tabEnProcesoBtn.addEventListener('shown.bs.tab', function () {
            if (!enProcesoCargado) {
                cargarEnProceso().catch(() => {});
                return;
            }
            const t = document.getElementById('barraGeneral-input').value;
            if (t.trim()) ccFiltrar(t);
        });
    }

    const tabEnvFinalizadoBtn = document.getElementById('tab-env-finalizado-btn');
    if (tabEnvFinalizadoBtn) {
        tabEnvFinalizadoBtn.addEventListener('shown.bs.tab', function () {
            const t = document.getElementById('barraGeneral-input').value;
            if (t.trim()) ccFiltrar(t);
        });
    }

    const tabCarteraBtn = document.getElementById('tab-cartera-btn');
    if (tabCarteraBtn) {
        tabCarteraBtn.addEventListener('shown.bs.tab', function () {
            const t = document.getElementById('barraGeneral-input').value.trim();
            if (t) {
                if (_tablaCartRec) _tablaCartRec.search(t).draw();
                if (_tablaCartNot) _tablaCartNot.search(t).draw();
            }
        });
    }

    const tabVoBoBtn = document.getElementById('tab-vobo-btn');
    if (tabVoBoBtn) {
        tabVoBoBtn.addEventListener('shown.bs.tab', function () {
            const t = document.getElementById('barraGeneral-input').value;
            if (t.trim()) { if (_tablaVoBo) _tablaVoBo.search(t).draw(); }
        });
    }

    const tabPeticionesBtn = document.getElementById('tab-peticiones-btn');
    if (tabPeticionesBtn) {
        tabPeticionesBtn.addEventListener('shown.bs.tab', function () {
            const t = document.getElementById('barraGeneral-input').value.trim();
            if (t && _tablaPeticiones) _tablaPeticiones.search(t).draw();
        });
    }

    // Carga inicial — se difiere a window load para que SweetAlert2 (cargado al final del layout) ya esté disponible
    if (document.readyState === 'complete') {
        cargarTodo();
    } else {
        window.addEventListener('load', cargarTodo);
    }

    function renderHistorial(rows) {
        _allRowsHist = rows;
        document.getElementById('loader-historial').classList.add('d-none');

        if (!rows || rows.length === 0) {
            document.getElementById('empty-historial').classList.remove('d-none');
            return;
        }

        document.getElementById('wrap-historial').classList.remove('d-none');
        _initTablaHist();
        const $histBar = $('#cc-filtros-hist').css({'margin-bottom': 0, 'padding': 0}).removeClass('d-none');
        $('#tablaHistorialMovs_wrapper .dataTables_length').parent().append($histBar);
        _applyHistFilter();
        const t = document.getElementById('barraGeneral-input').value.trim();
        if (t) _tablaHist.search(t).draw();
    }

    function _applyHistFilter() {
        if (!_tablaHist) return;
        // Remove any previously registered custom filter for historial
        $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
            return fn._ccHistFilter !== true;
        });
        if (_filtroHist !== 'todos') {
            const valores = _filtroHist.split(',');
            const fn = function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'tablaHistorialMovs') return true;
                const row = _tablaHist.row(dataIndex).data();
                return row ? valores.indexOf(row.estatus) !== -1 : false;
            };
            fn._ccHistFilter = true;
            $.fn.dataTable.ext.search.push(fn);
        }
        const countEl = document.getElementById('cc-filtro-hist-count');
        _tablaHist.clear().rows.add(_allRowsHist).draw();
        if (countEl) {
            const visible = _tablaHist.rows({ search: 'applied' }).count();
            const total   = _allRowsHist.length;
            countEl.textContent = visible < total
                ? `${visible} de ${total}`
                : (total ? `${total} total${total !== 1 ? 'es' : ''}` : '');
        }
    }

    function _pintarHistorial(rows) {
        const wrap      = document.getElementById('wrap-historial');
        const emptyHist = document.getElementById('empty-historial');
        emptyHist.classList.add('d-none');
        if (!rows.length) {
            wrap.classList.add('d-none');
            return;
        }
        wrap.classList.remove('d-none');
        _initTablaHist();
        _tablaHist.clear().rows.add(rows).draw();
    }

    /* ══════════════════════════════════
       PESTAÑA CARTERA — sub-tabs Recibidos / Notificados
    ══════════════════════════════════ */
    let _tablaCartRec = null;  // enviado_cartera
    let _tablaCartNot = null;  // notificado_cartera

    /** Configuración de columnas compartida entre ambas sub-tablas.
     *  @param {boolean} conAcciones – incluir columna Acciones */
    function _colsCartera(conAcciones, conCerrarSimple) {
        const cols = [
            {
                data: null, orderable: false, searchable: false, className: 'cc-cart-toggle',
                defaultContent: '<i class="fa-solid fa-plus-circle" style="font-size:1rem;color:#3b82f6;cursor:pointer;"></i>'
            },
            {
                data: null, render: function(d, t, r) {
                    return `<strong>${esc(r.id_credito)}</strong><br><small class="text-muted">${esc(r.nombre_cliente)}</small>` +
                           (CC_AMBAS ? `<br>${_celulaBadge(r)}` : '');
                }
            },
            {
                data: null, render: function(d, t, r) {
                    const pct = parseFloat(r.porcentaje_descuento) || 0;
                    return `<span>${esc(r.nombre_producto || '—')}</span><br>` +
                           (pct ? `<span class="cc-pct-badge">${pct}%</span>` : '');
                }
            },
            {
                data: 'total_a_pagar', className: 'text-end',
                render: function(d) { return `<strong class="text-success">${fmt(d)}</strong>`; }
            },
            { data: 'fecha_acuerdo', render: function(d) { return esc(d || '—'); } },
            {
                data: null, orderable: false, searchable: false,
                render: function(d, t, r) {
                    const pagadas = parseInt(r.cuotas_pagadas) || 0;
                    const semanas = parseInt(r.numero_semanas) || parseInt(r.num_semanas_amort) || 0;
                    if (!semanas) return '<span class="text-muted">—</span>';
                    return `<span style="font-size:.83rem;">${pagadas}/${semanas}</span>`;
                }
            },
            {
                data: 'estatus', orderable: false,
                render: function(d) { return convenioEstatusBadge(d); }
            }
        ];
        if (conAcciones) {
            cols.push({
                data: null, orderable: false, searchable: false,
                render: function(d, t, r) {
                    const nc2 = esc(r.nombre_cliente || '').replace(/'/g, "\\'");
                    const convCompletado = (r.estatus_convenio === 'completado');
                    let btns = '';
                    if (convCompletado) {
                        btns = `<button class="btn btn-sm btn-primary d-block w-100" style="font-size:.72rem;"` +
                            ` onclick="ccCerrarConvenio(${r.id_cierre},${r.id_credito},'${nc2}')"` +
                            ` title="Cerrar convenio"><i class="fa-solid fa-circle-check me-1"></i>Cerrar</button>`;
                    } else {
                        btns = `<button class="btn btn-sm btn-danger d-block w-100" style="font-size:.72rem;"` +
                            ` onclick="ccDevolverPorCartera(${r.id_cierre},${r.id_credito},'${nc2}')"` +
                            ` title="Devolver al despacho"><i class="fa-solid fa-rotate-left me-1"></i>Devolver</button>`;
                    }
                    return `<div style="min-width:120px;">${btns}</div>`;
                }
            });
        } else if (conCerrarSimple) {
            cols.push({
                data: null, orderable: false, searchable: false,
                render: function(d, t, r) {
                    const nc2 = esc(r.nombre_cliente || '').replace(/'/g, "\\'");
                    return `<button class="btn btn-sm btn-primary" style="font-size:.72rem;"` +
                        ` onclick="ccCerrarConvenio(${r.id_cierre},${r.id_credito},'${nc2}')"` +
                        ` title="Marcar como cerrado"><i class="fa-solid fa-circle-check me-1"></i>Cerrar</button>`;
                }
            });
        }
        cols.push({ data: 'id_cierre', visible: false, searchable: false });
        return cols;
    }

    function _dtOptsCartera(emptyMsg) {
        return {
            data: [],
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            order: [[0, 'desc']],  // se sobreescribe en _initTablaCart*
            responsive: { details: false },
            language: {
                emptyTable:   emptyMsg,
                infoEmpty:    'Sin registros',
                info:         'Mostrando _START_ a _END_ de _TOTAL_ convenios',
                infoFiltered: '(filtrado de _MAX_ totales)',
                lengthMenu:   'Mostrar _MENU_ registros',
                search:       'Buscar:',
                zeroRecords:  'Sin resultados para la búsqueda',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            autoWidth: false,
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
        };
    }

    function _bindCartToggle(tableId, dtInstance) {
        $(`#${tableId} tbody`).on('click', 'td.cc-cart-toggle', function() {
            const tr   = $(this).closest('tr');
            const row  = dtInstance.row(tr);
            const icon = $(this).find('i');
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color','#3b82f6');
                return;
            }
            tr.addClass('shown');
            icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color','#ef4444');
            const rd = row.data();
            const idConv = rd.id_convenio;
            if (!idConv) {
                row.child('<div class="text-center py-3 text-muted">Sin convenio asociado.</div>').show();
                return;
            }
            if (_detalleConvCache[idConv]) {
                row.child(`<div class="cc-conv-detail-inner">${buildDetalleConvHtml(_detalleConvCache[idConv], rd)}</div>`).show();
                return;
            }
            row.child('<div class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...</div>').show();
            fetch('/CierreCredito/getDetalleConvenio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idConv}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                _detalleConvCache[idConv] = res.datos;
                row.child(`<div class="cc-conv-detail-inner">${buildDetalleConvHtml(res.datos, rd)}</div>`).show();
            })
            .catch(err => {
                row.child(`<div class="alert alert-danger m-2 py-2">Error: ${esc(err.message)}</div>`).show();
            });
        });
    }

    function _initTablaCartRecibidos() {
        if (_tablaCartRec) return;
        const opts = _dtOptsCartera('Sin registros recibidos en cartera');
        opts.columns = _colsCartera(false, true);
        opts.order   = [[8, 'desc']];   // id_cierre (col 8 con cerrar simple)
        _tablaCartRec = $('#tablaCartRecibidos').DataTable(opts);
        _bindCartToggle('tablaCartRecibidos', _tablaCartRec);
    }

    function _initTablaCartNotificados() {
        if (_tablaCartNot) return;
        const opts = _dtOptsCartera('Sin convenios notificados pendientes');
        opts.columns = _colsCartera(true);
        opts.order   = [[8, 'desc']];   // id_cierre (col 8 con acciones)
        _tablaCartNot = $('#tablaCartNotificados').DataTable(opts);
        _bindCartToggle('tablaCartNotificados', _tablaCartNot);
    }

    function renderCartera(rows) {
        const loaderEl = document.getElementById('loader-cartera');
        const wrapEl   = document.getElementById('wrap-cartera');
        const emptyEl  = document.getElementById('empty-cartera');
        if (loaderEl) loaderEl.classList.add('d-none');

        const badgeEl = document.getElementById('badge-cartera');

        if (!rows || rows.length === 0) {
            if (emptyEl) emptyEl.classList.remove('d-none');
            if (badgeEl) { badgeEl.textContent = '0'; badgeEl.style.display = ''; }
            return;
        }

        if (wrapEl) wrapEl.classList.remove('d-none');

        const recibidos    = rows.filter(r => r.estatus === 'enviado_cartera');
        const notificados  = rows.filter(r => r.estatus === 'notificado_cartera');

        // Badge principal de la pestaña: total de ambos
        if (badgeEl) {
            badgeEl.textContent = rows.length;
            badgeEl.style.display = '';
        }

        // Badges de sub-pestañas
        const badgeRec = document.getElementById('badge-cart-recibidos');
        const badgeNot = document.getElementById('badge-cart-notificados');
        if (badgeRec) badgeRec.textContent = recibidos.length;
        if (badgeNot) badgeNot.textContent = notificados.length;

        // ── Sub-tab Recibidos ──
        const emptyRec = document.getElementById('empty-cart-recibidos');
        _initTablaCartRecibidos();
        _tablaCartRec.clear().rows.add(recibidos).draw();
        if (emptyRec) emptyRec.classList.toggle('d-none', recibidos.length > 0);

        // ── Sub-tab Notificados ──
        const emptyNot = document.getElementById('empty-cart-notificados');
        _initTablaCartNotificados();
        _tablaCartNot.clear().rows.add(notificados).draw();
        if (emptyNot) emptyNot.classList.toggle('d-none', notificados.length > 0);

        const t = document.getElementById('barraGeneral-input').value.trim();
        if (t) {
            _tablaCartRec.search(t).draw();
            _tablaCartNot.search(t).draw();
        }
    }

    /* ══════════════════════════════════
       MARCAR LISTO (en_cola → listo_envio)
    ══════════════════════════════════ */
    window.ccMarcarListoEnvio = function(idRegistro) {
        Swal.fire({
            title: '¿Marcar como listo para reenvío?',
            html: 'Confirma que el límite de envíos ya se restableció y este correo puede ser enviado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Sí, marcar listo',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch('/CierreCredito/marcarListoEnvio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idRegistro}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({ icon: 'success', title: 'Listo', text: res.mensaje, timer: 1800, showConfirmButton: false });
                refrescarHistorial();
            })
            .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
        });
    };

    /* ══════════════════════════════════
       REENVIAR A CARTERA (listo_envio → enviado_cartera / en_cola)
    ══════════════════════════════════ */
    window.ccReenviarACartera = function(idRegistro) {
        Swal.fire({
            title: '¿Reenviar a cartera?',
            html: 'Se intentará enviar el correo de notificación nuevamente.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i>Sí, reenviar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            const btn = document.getElementById(`cc-hist-btn-${idRegistro}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando...'; }
            fetch('/CierreCredito/reenviarACartera', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idRegistro}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                const emailOk  = !res.email_error;
                const emailMsg = res.email_error
                    ? `<div class="alert alert-warning mt-2 mb-0 py-1 px-2" style="font-size:.8rem;text-align:left;">
                           <i class="fa-solid fa-triangle-exclamation me-1"></i><strong>Error SMTP:</strong><br>${esc(res.email_error)}
                       </div>`
                    : '';
                Swal.fire({
                    title: emailOk ? '¡Enviado!' : 'En cola',
                    html: `<span>${res.mensaje}</span>${emailMsg}`,
                    icon: emailOk ? 'success' : 'warning',
                    timer: emailOk ? 2000 : undefined,
                    showConfirmButton: !emailOk
                });
                refrescarHistorial();
            })
            .catch(err => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera'; }
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    /* ══════════════════════════════════
       CONFIRMAR CIERRE (Tab 1 → crea registro en proceso)
    ══════════════════════════════════ */
    window.ccConfirmar = function(idRegistro, idCredito, nombreCliente) {
        Swal.fire({
            title: '¿Confirmar cierre?',
            html: `Se enviará el crédito <strong>${idCredito}</strong> al proceso de validación.<br>
                   <small class="text-muted">La gestora validadora revisará los documentos antes de enviarlo a cartera.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById(`cc-btn-${idRegistro}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando...'; }

            const params = new URLSearchParams({
                id_credito:     idCredito,
                nombre_cliente: nombreCliente || '',
                estatus:        'en_proceso'
            });
            fetch('/CierreCredito/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({
                    title: '¡Confirmado!',
                    text: `Crédito ${idCredito} enviado al proceso de validación.`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                // Eliminar de _allRows y repintar para que no reaparezca en búsquedas
                _allRows = _allRows.filter(row => String(row.id) !== String(idRegistro));
                _pintarCards(_allRows);
                // Actualizar badge Tab 1
                const badge1 = document.getElementById('badge-env-finalizado');
                if (badge1) badge1.textContent = Math.max(0, (parseInt(badge1.textContent) || 1) - 1);
                // Incrementar badge tab 2 y resetear flag de carga lazy
                const badge2 = document.getElementById('badge-en-proceso');
                if (badge2) badge2.textContent = (parseInt(badge2.textContent) || 0) + 1;
                enProcesoCargado = false;
                refrescarHistorial();
            })
            .catch(err => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Confirmar cierre'; }
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    /* ══════════════════════════════════
       VO.BO: abrir modal y enviar
    ══════════════════════════════════ */
    window.ccAbrirModalVoBo = function(idRegistro) {
        const idEl = document.getElementById('cc-vobo-id');
        const fileEl = document.getElementById('cc-vobo-archivo');
        const comentEl = document.getElementById('cc-vobo-comentario');
        if (!idEl || !fileEl || !comentEl) return;
        idEl.value = String(idRegistro || '');
        fileEl.value = '';
        comentEl.value = '';
        const contadorEl = document.getElementById('cc-vobo-contador');
        if (contadorEl) contadorEl.textContent = '0';
        comentEl.oninput = function() { if (contadorEl) contadorEl.textContent = comentEl.value.length; };

        const modalEl = document.getElementById('ccModalVoBo');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    const _btnEnviarVoBo = document.getElementById('cc-vobo-enviar-btn');
    if (_btnEnviarVoBo) {
        _btnEnviarVoBo.addEventListener('click', function() {
            const id = parseInt(document.getElementById('cc-vobo-id').value || '0', 10);
            const archivo = document.getElementById('cc-vobo-archivo').files[0] || null;
            const comentario = (document.getElementById('cc-vobo-comentario').value || '').trim();

            if (!id) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'ID inválido para envío a Vo.Bo.' });
                return;
            }
            if (!comentario) {
                Swal.fire({ icon: 'warning', title: 'Comentario requerido', text: 'Debe capturar un comentario para Vo.Bo.' });
                return;
            }
            if (comentario.length > 150) {
                Swal.fire({ icon: 'warning', title: 'Comentario muy largo', text: 'El comentario no puede exceder 150 caracteres.' });
                return;
            }

            const fd = new FormData();
            fd.append('id', String(id));
            fd.append('comentario', comentario);
            fd.append('archivo', archivo);

            _btnEnviarVoBo.disabled = true;
            _btnEnviarVoBo.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando...';

            fetch('/CierreCredito/enviarAVoBo', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje || 'No se pudo enviar a Vo.Bo.');

                const modalEl = document.getElementById('ccModalVoBo');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();

                Swal.fire({ icon: 'success', title: 'Enviado', text: res.mensaje || 'Registro enviado a Vo.Bo.', timer: 2000, showConfirmButton: false });
                if (CC_P.en_proceso) cargarEnProceso().catch(() => {});
                if (CC_P.vobo)       cargarVoBo().catch(() => {});
                if (CC_P.historial)  cargarHistorial().catch(() => {});
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            })
            .finally(() => {
                _btnEnviarVoBo.disabled = false;
                _btnEnviarVoBo.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar a dirección de cobranza';
            });
        });
    }

    window.ccAprobarVoBo = function(idRegistro) {
        Swal.fire({
            title: '¿Confirmas visto bueno?',
            html: 'Este registro será marcado como <strong>validado por dirección de cobranza</strong>.',
            icon: 'question',
            input: 'textarea',
            inputPlaceholder: 'Comentario opcional de validación...',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Sí, confirmo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#16a34a'
        }).then(result => {
            if (!result.isConfirmed) return;
            const comentario = (result.value || '').trim();
            const params = new URLSearchParams({ id: String(idRegistro), comentario });
            fetch('/CierreCredito/aprobarVoBo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({ icon: 'success', title: 'Aprobado', text: res.mensaje, timer: 2000, showConfirmButton: false });
                if (CC_P.en_proceso) cargarEnProceso().catch(() => {});
                if (CC_P.vobo)       cargarVoBo().catch(() => {});
                if (CC_P.historial)  cargarHistorial().catch(() => {});
            })
            .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
        });
    };

    window.ccRechazarVoBo = function(idRegistro) {
        Swal.fire({
            title: 'Rechazar Vo.Bo',
            input: 'textarea',
            inputLabel: 'Motivo del rechazo',
            inputPlaceholder: 'Escribe el motivo...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-xmark me-1"></i>Rechazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            preConfirm: (val) => {
                if (!val || !val.trim()) {
                    Swal.showValidationMessage('Debe capturar el motivo del rechazo.');
                    return false;
                }
                return val.trim();
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            const params = new URLSearchParams({ id: String(idRegistro), comentario: result.value });
            fetch('/CierreCredito/rechazarVoBo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({ icon: 'success', title: 'Rechazado', text: res.mensaje, timer: 2200, showConfirmButton: false });
                if (CC_P.validacion) cargarEnviadoFinalizado().catch(() => {});
                if (CC_P.vobo)       cargarVoBo().catch(() => {});
                if (CC_P.historial)  cargarHistorial().catch(() => {});
            })
            .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
        });
    };

    /* ══════════════════════════════════
       ENVIAR A CARTERA (Tab 2)
    ══════════════════════════════════ */
    window.ccEnviarACartera = function(idRegistro) {
        Swal.fire({
            title: '¿Enviar a cartera?',
            html: 'Se marcará este cierre como <strong>enviado a cartera</strong>.<br><small class="text-muted">El área de cartera actualizará el sistema.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i>Sí, enviar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById(`cc-ep-btn-${idRegistro}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando...'; }

            fetch('/CierreCredito/enviarACartera', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idRegistro}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);

                const emailOk  = !res.email_error;
                const emailMsg = res.email_error
                    ? `<div class="alert alert-warning mt-2 mb-0 py-1 px-2" style="font-size:.8rem;text-align:left;">
                           <i class="fa-solid fa-triangle-exclamation me-1"></i><strong>Error de correo SMTP:</strong><br>${esc(res.email_error)}
                       </div>`
                    : '';

                Swal.fire({
                    title: emailOk ? '¡Enviado!' : 'Error de envío',
                    html: `<span>${res.mensaje}</span>${emailMsg}`,
                    icon: emailOk ? 'success' : 'warning',
                    timer: emailOk ? 2000 : undefined,
                    showConfirmButton: !emailOk
                });

                if (emailOk) {
                    const colEl = document.getElementById(`cc-ep-col-${idRegistro}`);
                    if (colEl) {
                        const chunkIdx    = parseInt(colEl.dataset.epChunk);
                        // Cerrar detalle si está abierto en este chunk
                        _epCloseChunk(chunkIdx);
                        colEl.remove();
                        const chunkRow    = document.getElementById(`ep-chunk-row-${chunkIdx}`);
                        const overflowRow = document.getElementById(`ep-chunk-overflow-${chunkIdx}`);
                        const totalLeft   = [
                            ...(chunkRow    ? chunkRow.querySelectorAll('[id^="cc-ep-col-"]')    : []),
                            ...(overflowRow ? overflowRow.querySelectorAll('[id^="cc-ep-col-"]') : [])
                        ].length;
                        if (totalLeft === 0) {
                            if (chunkRow)    chunkRow.remove();
                            if (overflowRow) overflowRow.remove();
                        }
                    }
                    const badge = document.getElementById('badge-en-proceso');
                    if (badge) badge.textContent = Math.max(0, (parseInt(badge.textContent) || 1) - 1);
                    // Si no quedan cards, mostrar estado vacío
                    const wrap2 = document.getElementById('wrap-ep-cards');
                    if (wrap2 && wrap2.querySelectorAll('[id^="cc-ep-card-"]').length === 0) {
                        wrap2.classList.add('d-none');
                        document.getElementById('empty-en-proceso').classList.remove('d-none');
                    }
                    refrescarHistorial();
                } else {
                    // El registro regresó a en_proceso — recargar tab para mostrar badge de envío fallido
                    cargarEnProceso();
                }
            })
            .catch(err => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar a cartera'; }
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    /* ══════════════════════════════════
       DESCARTAR (Tab 2 → regresa a Tab 1)
    ══════════════════════════════════ */
    window.ccDescartar = function(idRegistro) {
        // Cargar catálogo antes de abrir el Swal
        fetch('/CierreCredito/getCatalogoDescarte', { method: 'POST' })
        .then(r => r.json())
        .then(cat => {
            const opciones = (cat.datos || []).map(m =>
                `<option value="${m.id}">${esc(m.motivo)}</option>`
            ).join('');

            Swal.fire({
                title: '¿Descartar registro?',
                html: `<p class="text-muted mb-3" style="font-size:.85rem;">El convenio regresará a <strong>Validación de Cierre</strong>. Se podrá confirmar nuevamente desde allí.</p>
<div class="text-start mb-3">
  <label class="form-label fw-semibold" style="font-size:.83rem;">Motivo de descarte <span class="text-danger">*</span></label>
  <select id="swal-motivo-descarte" class="form-select form-select-sm">
    <option value="">— Selecciona un motivo —</option>
    ${opciones}
  </select>
</div>
<div class="text-start">
  <label class="form-label fw-semibold" style="font-size:.83rem;">Comentario adicional <span class="text-muted fw-normal">(opcional)</span></label>
  <textarea id="swal-comentario-descarte" class="form-control form-control-sm" rows="3"
            maxlength="150"
            placeholder="Describe el motivo con más detalle… (máx. 150 caracteres)"
            style="resize:vertical;"></textarea>
  <div class="text-end mt-1" style="font-size:.72rem;color:#94a3b8;"><span id="swal-coment-count">0</span> / 150</div>
</div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: '<i class="fa-solid fa-rotate-left me-1"></i>Sí, descartar',
                cancelButtonText: 'Cancelar',
                focusConfirm: false,
                didOpen: () => {
                    const ta = document.getElementById('swal-comentario-descarte');
                    const counter = document.getElementById('swal-coment-count');
                    ta.addEventListener('input', () => { counter.textContent = ta.value.length; });
                },
                preConfirm: () => {
                    const motivoId = document.getElementById('swal-motivo-descarte').value;
                    const comentario = document.getElementById('swal-comentario-descarte').value.trim();
                    if (!motivoId) {
                        Swal.showValidationMessage('Debes seleccionar un motivo de descarte.');
                        return false;
                    }
                    return { motivoId, comentario };
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                const { motivoId, comentario } = result.value;

                fetch('/CierreCredito/descartar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${idRegistro}&motivo_id=${encodeURIComponent(motivoId)}&comentario=${encodeURIComponent(comentario)}`
                })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                // Eliminar card de Tab 2
                const colEl = document.getElementById(`cc-ep-col-${idRegistro}`);
                if (colEl) {
                    const chunkIdx    = parseInt(colEl.dataset.epChunk);
                    // Cerrar detalle si está abierto en este chunk
                    _epCloseChunk(chunkIdx);
                    colEl.remove();
                    const chunkRow    = document.getElementById(`ep-chunk-row-${chunkIdx}`);
                    const overflowRow = document.getElementById(`ep-chunk-overflow-${chunkIdx}`);
                    const totalLeft   = [
                        ...(chunkRow    ? chunkRow.querySelectorAll('[id^="cc-ep-col-"]')    : []),
                        ...(overflowRow ? overflowRow.querySelectorAll('[id^="cc-ep-col-"]') : [])
                    ].length;
                    if (totalLeft === 0) {
                        if (chunkRow)    chunkRow.remove();
                        if (overflowRow) overflowRow.remove();
                    }
                }
                const badge = document.getElementById('badge-en-proceso');
                if (badge) badge.textContent = Math.max(0, (parseInt(badge.textContent) || 1) - 1);
                const wrap2 = document.getElementById('wrap-ep-cards');
                if (wrap2 && wrap2.querySelectorAll('[id^="cc-ep-card-"]').length === 0) {
                    wrap2.classList.add('d-none');
                    document.getElementById('empty-en-proceso').classList.remove('d-none');
                }
                // Refrescar Tab 1 para que reaparezca el convenio
                document.getElementById('loader-env-finalizado').classList.remove('d-none');
                document.getElementById('wrap-env-finalizado').classList.add('d-none');
                document.getElementById('empty-env-finalizado').classList.add('d-none');
                document.getElementById('empty-busqueda').classList.add('d-none');
                document.getElementById('barraGeneral-input').value = '';
                cargarEnviadoFinalizado();
                refrescarHistorial();
                Swal.fire({ title: '¡Descartado!', text: 'El convenio regresó a Enviados Finalizados.', icon: 'success', timer: 2000, showConfirmButton: false });
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });   // cierra Swal.then(result)
        });   // cierra fetch outer .then(cat)
    };

    /* ══════════════════════════════════
       PESTAÑA PETICIONES DE CANCELAMIENTO
    ══════════════════════════════════ */
    let _tablaPeticiones    = null;
    let _petDetalleCache    = {};   // cache amortizacion por id_convenio

    function cargarPeticiones() {
        if (!CC_P.peticiones) return Promise.resolve();
        return fetch('/CierreCredito/getPeticionesCancelamiento', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (res) {
                if (!res.success) throw new Error(res.mensaje);
                renderPeticiones((res.datos && res.datos.peticiones) ? res.datos.peticiones : []);
            });
    }

    function _petRefrescar() {
        _petDetalleCache = {};
        if (_tablaPeticiones) {
            _tablaPeticiones.destroy();
            _tablaPeticiones = null;
        }
        const wrap  = document.getElementById('wrap-peticiones');
        const empty = document.getElementById('empty-peticiones');
        const loader= document.getElementById('loader-peticiones');
        if (wrap)  wrap.classList.add('d-none');
        if (empty) empty.classList.add('d-none');
        if (loader) loader.classList.remove('d-none');
        cargarPeticiones().catch(() => {});
    }

    /* Construye el HTML del detalle expandido de una fila */
    function _buildPetDetalle(amort, rowData) {
        const fmtN = function(n) { return parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }); };

        const badgeEstPago = function(e) {
            if (e === 'pagado')    return '<span class="badge" style="background:#dcfce7;color:#15803d;">Pagado</span>';
            if (e === 'vencido')   return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Vencido</span>';
            if (e === 'cancelado') return '<span class="badge" style="background:#f1f5f9;color:#64748b;">Cancelado</span>';
            return '<span class="badge" style="background:#fef9c3;color:#854d0e;">Pendiente</span>';
        };

        let filas = '';
        if (!amort || !amort.length) {
            filas = '<tr><td colspan="5" class="text-center text-muted py-3">Sin amortización registrada.</td></tr>';
        } else {
            filas = amort.map(function(a) {
                const comp = a.comprobante_path
                    ? `<a href="${esc(a.comprobante_path)}" target="_blank" style="font-size:.72rem;padding:2px 8px;border-radius:4px;background:#dcfce7;color:#15803d;text-decoration:none;white-space:nowrap;"><i class="fa-solid fa-paperclip me-1"></i>Ver</a>`
                    : '<span class="text-muted" style="font-size:.75rem;">—</span>';
                return `<tr>
                    <td class="text-center fw-bold">${esc(a.numero_semana)}</td>
                    <td>${esc(a.fecha_pago || '—')}</td>
                    <td class="text-end">${fmtN(a.pago_semanal)}</td>
                    <td class="text-center">${badgeEstPago(a.estatus_pago)}</td>
                    <td class="text-center">${comp}</td>
                </tr>`;
            }).join('');
        }

        const motivoHtml = rowData.motivo_cancelamiento
            ? `<div style="margin-bottom:.9rem;padding:.65rem .9rem;background:#fefce8;border:1px solid #fde68a;border-radius:.45rem;">
                   <div style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#92400e;margin-bottom:.3rem;">
                       <i class="fa-solid fa-comment-dots me-1"></i>Motivo de cancelamiento
                   </div>
                   <div style="font-size:.87rem;color:#78350f;">${esc(rowData.motivo_cancelamiento)}</div>
               </div>`
            : '';

        return `<div style="padding:.9rem 1.1rem 1rem 1.4rem;background:#fafafa;">
            ${motivoHtml}
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#1d4ed8;margin-bottom:.5rem;">
                <i class="fa-solid fa-table-list me-1"></i>Amortización
            </div>
            <div style="overflow-x:auto;">
                <table class="cc-amort-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Fecha pago</th>
                            <th class="text-end">Pago</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-center">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>${filas}</tbody>
                </table>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button class="btn btn-sm btn-danger fw-bold cc-btn-autorizar-cancelamiento"
                        data-id="${esc(rowData.id)}" data-credito="${esc(rowData.id_credito)}" data-cliente="${esc(rowData.nombre_cliente)}"
                        style="font-size:.82rem;white-space:nowrap;">
                    <i class="fa-solid fa-circle-check me-1"></i>Sí, autorizar cancelamiento
                </button>
                <button class="btn btn-sm btn-outline-secondary cc-pet-btn-descartar"
                        data-id="${esc(rowData.id)}" data-credito="${esc(rowData.id_credito)}" data-cliente="${esc(rowData.nombre_cliente)}"
                        style="font-size:.82rem;white-space:nowrap;">
                    <i class="fa-solid fa-ban me-1"></i>No, descartar cancelamiento
                </button>
            </div>
        </div>`;
    }

    function renderPeticiones(rows) {
        const loader = document.getElementById('loader-peticiones');
        const wrap   = document.getElementById('wrap-peticiones');
        const empty  = document.getElementById('empty-peticiones');
        if (loader) loader.classList.add('d-none');

        const badge = document.getElementById('badge-peticiones');
        if (badge) badge.textContent = rows.length;

        if (!rows.length) {
            if (empty) empty.classList.remove('d-none');
            return;
        }

        if (wrap) wrap.classList.remove('d-none');

        if (_tablaPeticiones) {
            _tablaPeticiones.clear().rows.add(rows).draw();
            return;
        }

        _tablaPeticiones = $('#tablaPeticiones').DataTable({
            data: rows,
            order: [[6, 'asc']],   // col 6 = solicitud_cancelamiento_fecha
            language: {
                emptyTable:   'Sin peticiones de cancelamiento',
                infoEmpty:    'Sin registros',
                info:         'Mostrando _START_ a _END_ de _TOTAL_ peticiones',
                infoFiltered: '(filtrado de _MAX_ totales)',
                lengthMenu:   'Mostrar _MENU_ registros',
                search:       'Buscar:',
                zeroRecords:  'Sin resultados para la búsqueda',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' }
            },
            columns: [
                // col 0 — control expand
                {
                    data: null, orderable: false, searchable: false, className: 'dt-control text-center',
                    defaultContent: '<i class="fa-solid fa-plus-circle" style="font-size:1rem;color:#7c3aed;cursor:pointer;"></i>'
                },
                // col 1 — crédito / cliente
                {
                    data: null,
                    render: function (d, t, r) {
                        if (t === 'filter' || t === 'sort') return String(r.id_credito || '') + ' ' + String(r.nombre_cliente || '');
                        return `<strong style="color:#3b82f6;">#${esc(r.id_credito)}</strong><br>` +
                               `<small class="text-muted">${esc(r.nombre_cliente)}</small>`;
                    }
                },
                // col 2 — producto
                {
                    data: 'nombre_producto',
                    render: function (d) { return esc(d || '—'); }
                },
                // col 3 — total
                {
                    data: 'total_a_pagar', className: 'text-end',
                    render: function (d) { return `<strong class="text-success">${fmt(d)}</strong>`; }
                },
                // col 4 — fecha acuerdo
                {
                    data: 'fecha_acuerdo',
                    render: function (d) { return esc(d || '—'); }
                },
                // col 5 — solicita (usuario)
                {
                    data: 'usuario_cancela',
                    render: function (d) {
                        return `<span class="badge" style="background:#e0e7ff;color:#3730a3;font-weight:600;white-space:normal;">` +
                               `<i class="fa-solid fa-user me-1"></i>${esc(d || '—')}</span>`;
                    }
                },
                // col 6 — fecha solicitud
                {
                    data: 'solicitud_cancelamiento_fecha',
                    render: function (d, t) {
                        if (t === 'sort') return d || '';
                        return fmtFecha(d);
                    }
                },
                // cols 7+ eliminadas: Motivo y Acciones se ven en el detalle expandido
            ],
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50],
            responsive: { details: false },  // desactiva flecha responsive; el control + / - lo maneja dt-control
            dom: '<"row"<"col-sm-12 col-md-6"l>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            autoWidth: false,
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
            }
        });

        /* ── Expandir/Colapsar fila con tabla de amortización ── */
        $('#tablaPeticiones tbody').on('click', 'td.dt-control', function () {
            const tr  = $(this).closest('tr');
            const row = _tablaPeticiones.row(tr);
            const iconEl = this.querySelector('i');

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                if (iconEl) { iconEl.classList.replace('fa-minus-circle', 'fa-plus-circle'); iconEl.style.color = '#7c3aed'; }
                return;
            }

            // Cambiar ícono a spinner mientras carga
            if (iconEl) { iconEl.className = 'fa-solid fa-spinner fa-spin'; iconEl.style.color = '#7c3aed'; }

            const rowData = row.data();
            const idConv  = rowData.id;

            // Si ya está en caché, mostrar directo
            if (_petDetalleCache[idConv]) {
                row.child(`<div>${_buildPetDetalle(_petDetalleCache[idConv], rowData)}</div>`).show();
                tr.addClass('shown');
                if (iconEl) { iconEl.className = 'fa-solid fa-minus-circle'; iconEl.style.color = '#7c3aed'; }
                return;
            }

            // Cargar amortización desde el endpoint de detalle de convenio
            row.child('<div class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...</div>').show();

            fetch('/CierreCredito/getDetalleConvenio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + idConv
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success) throw new Error(res.mensaje);
                const amort = (res.datos && res.datos.amortizacion) ? res.datos.amortizacion : [];
                _petDetalleCache[idConv] = amort;
                row.child(`<div>${_buildPetDetalle(amort, rowData)}</div>`).show();
                tr.addClass('shown');
                if (iconEl) { iconEl.className = 'fa-solid fa-minus-circle'; iconEl.style.color = '#7c3aed'; }
            })
            .catch(function (err) {
                row.child(`<div class="alert alert-danger m-2 py-2">Error al cargar detalle: ${esc(err.message)}</div>`).show();
                if (iconEl) { iconEl.className = 'fa-solid fa-plus-circle'; iconEl.style.color = '#7c3aed'; }
            });
        });

        /* ── Botones de acción (autorizar / descartar) ── */
        document.getElementById('tablaPeticiones').addEventListener('click', function (e) {

            /* ── Botón Descartar cancelamiento ── */
            const btnDes = e.target.closest('.cc-pet-btn-descartar');
            if (btnDes) {
                const idConv  = btnDes.dataset.id;
                const credito = btnDes.dataset.credito;
                const cliente = btnDes.dataset.cliente;

                Swal.fire({
                    title: '¿Seguro que descartas la petición?',
                    html: `El convenio del crédito <strong>${esc(credito)}</strong> — ${esc(cliente)} volverá a estar <strong>activo</strong> sin solicitud pendiente.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-ban me-1"></i>Sí, descartar',
                    cancelButtonText: 'No, revisar',
                    confirmButtonColor: '#6b7280',
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    Swal.fire({ title: 'Descartando...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

                    const fd = new FormData();
                    fd.append('id_convenio', idConv);

                    fetch('/CierreCredito/descartarCancelamiento', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (resp) {
                        if (!resp.success) {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo descartar.' });
                            return;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Petición descartada',
                            text: 'El convenio permanece activo sin solicitud de cancelamiento.',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(function () { _petRefrescar(); });
                    })
                    .catch(function () {
                        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo completar la operación.' });
                    });
                });
                return;
            }

            /* ── Botón Autorizar cancelamiento ── */
            const btn = e.target.closest('.cc-btn-autorizar-cancelamiento');
            if (!btn) return;
            const idConvenio = btn.dataset.id;
            const credito    = btn.dataset.credito;
            const cliente    = btn.dataset.cliente;

            Swal.fire({
                title: '¿Autorizar cancelamiento?',
                html: `Estás a punto de <strong>cancelar definitivamente</strong> el convenio del crédito <strong>${esc(credito)}</strong> — ${esc(cliente)}.<br><br>Esta acción <strong>no se puede deshacer</strong>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-circle-check me-1"></i>Sí, autorizar',
                cancelButtonText: 'No, volver',
                confirmButtonColor: '#dc2626',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                Swal.fire({ title: 'Autorizando...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

                const fd = new FormData();
                fd.append('id_convenio', idConvenio);

                fetch('/CierreCredito/autorizarCancelamiento', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                })
                .then(function (r) { return r.json(); })
                .then(function (resp) {
                    if (!resp.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo autorizar.' });
                        return;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelamiento autorizado',
                        text: 'El convenio fue cancelado correctamente.',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(function () { _petRefrescar(); });
                })
                .catch(function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo completar la operación.' });
                });
            });
        });
    }

})();
</script>
