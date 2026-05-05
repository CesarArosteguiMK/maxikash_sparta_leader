<style>
    /* ===================================================================
       MIS ADJUDICACIONES -" Paleta ámbar (hereda de Adjudicación)
       =================================================================== */
    :root {
        --madj-color:        #f59e0b;
        --madj-dark:         #d97706;
        --madj-light:        #fffbeb;
        --madj-border:       #fcd34d;
        --madj-text:         #92400e;
    }

    /* -- KPI Cards --------------------------------------------------- */
    .madj-kpi-wrapper {
        display: flex;
        justify-content: flex-start;
        align-items: stretch;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .madj-kpi-item { flex: 1 1 150px; max-width: 200px; min-width: 130px; }

    .madj-kpi-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        background: #ffffff;
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -1px rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
        cursor: default;
    }
    .madj-kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--kpi-c1), var(--kpi-c2));
        transition: height 0.3s ease;
    }
    .madj-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.12);
    }
    .madj-kpi-card:hover::before { height: 6px; }

    .madj-kpi-card.kpi-total   { --kpi-c1:#f59e0b; --kpi-c2:#fbbf24; }
    .madj-kpi-card.kpi-activos { --kpi-c1:#6366f1; --kpi-c2:#818cf8; }
    .madj-kpi-card.kpi-saldo   { --kpi-c1:#dc2626; --kpi-c2:#ef4444; }
    .madj-kpi-card.kpi-mora    { --kpi-c1:#64748b; --kpi-c2:#94a3b8; }

    .madj-kpi-card .card-body {
        padding: 1rem 1rem 0.85rem;
        text-align: center;
        position: relative;
    }
    .madj-kpi-num {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.3rem;
        background: linear-gradient(135deg, var(--kpi-c1), var(--kpi-c2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .madj-kpi-lbl {
        font-size: 0.65rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }
    .madj-kpi-icon {
        font-size: 2rem;
        opacity: 0.1;
        position: absolute;
        right: 0.75rem;
        top: 0.75rem;
        color: var(--kpi-c1);
    }

    /* -- Banner / alert ------------------------------------------- */
    .madj-alert {
        background: var(--madj-light);
        border: 1px solid var(--madj-border);
        border-left: 4px solid var(--madj-color);
        color: var(--madj-text);
        border-radius: 0.375rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    .madj-alert i { color: var(--madj-color); }

    /* -- Filtro bar ----------------------------------------------- */
    .madj-filter-bar {
        background: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
        padding: 0.875rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    /* -- Empty state ---------------------------------------------- */
    #madj-empty-state { display: none; }

    /* -- Animaciones ----------------------------------------------- */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .madj-animate { animation: fadeInUp 0.3s ease-out both; }

    /* -- Evidencias -" Secciones --------------------------------------- */
    .madj-ev-section  { margin-bottom: 1rem; }
    .madj-ev-hdr {
        display: flex; align-items: center; gap: .5rem;
        padding: .45rem .875rem;
        border-radius: .5rem .5rem 0 0;
        font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    }
    .madj-ev-hdr-orange { background:#fff7ed; border:1px solid #fed7aa; border-bottom:none; color:#9a3412; }
    .madj-ev-hdr-blue   { background:#eff6ff; border:1px solid #bfdbfe; border-bottom:none; color:#1e40af; }
    .madj-ev-hdr-green  { background:#f0fdf4; border:1px solid #bbf7d0; border-bottom:none; color:#166534; }
    .madj-ev-slots-wrap {
        padding: .75rem; background: #f8fafc;
        border: 1px solid #e2e8f0; border-top: none;
        border-radius: 0 0 .5rem .5rem;
        display: flex; gap: .625rem; overflow-x: auto; scrollbar-width: thin;
    }
    .madj-ev-slots-wrap::-webkit-scrollbar { height: 5px; }
    .madj-ev-slots-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .madj-ev-slot {
        flex-shrink: 0; width: 120px; height: 120px;
        background: #fff; border: 2px dashed #cbd5e1; border-radius: .625rem;
        cursor: pointer; position: relative;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .3rem; transition: border-color .15s, background .15s; overflow: hidden;
    }
    .madj-ev-slot:hover               { border-color: #f59e0b; background: #fffbeb; }
    .madj-ev-slot.has-file            { border-style: solid; border-color: #e2e8f0; cursor: default; }
    .madj-ev-slot.uploading           { opacity: .65; pointer-events: none; }
    .madj-ev-slot .slot-icon-ph       { font-size: 1.35rem; color: #94a3b8; pointer-events: none; }
    .madj-ev-slot .slot-lbl {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(15,23,42,.72); color: #fff;
        font-size: .58rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; padding: .18rem .25rem;
        text-align: center; pointer-events: none; line-height: 1.2;
    }
    .madj-slot-btn {
        position: absolute; top: 5px; right: 5px;
        width: 22px; height: 22px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; border: none; cursor: pointer; z-index: 2;
        box-shadow: 0 2px 5px rgba(0,0,0,.25);
    }
    .madj-slot-btn-add { background: #f59e0b; color: #fff; pointer-events: none; }
    .madj-slot-btn-rep { background: #f97316; color: #fff; }
    /* -- Status badges on evidence slots ------------------------- */
    .madj-ev-status-badge {
        position: absolute; bottom: 22px; left: 50%;
        transform: translateX(-50%);
        font-size: .58rem; font-weight: 800;
        border-radius: 999px; padding: 1px 7px;
        white-space: nowrap; pointer-events: none; z-index: 2;
    }
    .madj-ev-status-pendiente { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .madj-ev-status-recibido  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .madj-ev-status-aceptada  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .madj-ev-status-rechazada { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    @keyframes madj-ev-slot-rechazo-pulse {
        0%, 100% {
            border-color: #b91c1c !important;
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.55), 0 0 12px rgba(220, 38, 38, 0.25);
        }
        50% {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 5px rgba(220, 38, 38, 0.2), 0 0 18px rgba(248, 113, 113, 0.45);
        }
    }
    .madj-ev-slot.has-file.status-pendiente {
        border-width: 3px !important;
        border-color: #f59e0b !important;
        border-style: solid !important;
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.25);
    }
    .madj-ev-slot.has-file.status-recibido,
    .madj-ev-slot.has-file.status-aceptada {
        border-width: 3px !important;
        border-style: solid !important;
        /* Verde tipo icono “éxito” (referencia ~#28a745 / #22c55e) */
        border-color: #28a745 !important;
        box-shadow: 0 0 0 1px rgba(40, 167, 69, 0.45), 0 2px 12px rgba(40, 167, 69, 0.32);
    }
    .madj-ev-slot.has-file.status-rechazada {
        border-width: 3px !important;
        border-style: solid !important;
        border-color: #dc2626 !important;
        animation: madj-ev-slot-rechazo-pulse 1.15s ease-in-out infinite;
    }
    @media (prefers-reduced-motion: reduce) {
        .madj-ev-slot.has-file.status-rechazada,
        body.dark-mode .madj-ev-slot.has-file.status-rechazada { animation: none !important; }
    }
    .madj-ev-slot img.madj-thumb,
    .madj-ev-slot video.madj-thumb    { width: 100%; height: 100%; object-fit: cover; display: block; }
    .madj-slot-vid-ov {
        position: absolute; inset: 0; background: rgba(0,0,0,.38);
        display: flex; align-items: center; justify-content: center; pointer-events: none;
    }
    .madj-slot-vid-ov i { font-size: 1.8rem; color: #fff; }
    .madj-slot-upload-spin {
        position: absolute; inset: 0; background: rgba(255,255,255,.75);
        display: flex; align-items: center; justify-content: center; z-index: 3;
    }
    /* PDF / wide slot */
    .madj-ev-slot-pdf {
        width: 100%; min-height: 88px; background: #fff;
        border: 2px dashed #cbd5e1; border-radius: .625rem;
        cursor: pointer; position: relative;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .35rem; transition: border-color .15s, background .15s;
        overflow: hidden; padding: .75rem;
    }
    .madj-ev-slot-pdf:hover            { border-color: #f59e0b; background: #fffbeb; }
    .madj-ev-slot-pdf.has-file         { border-style: solid; border-color: #e2e8f0; }
    .madj-ev-slot-pdf .slot-icon-ph    { font-size: 1.6rem; color: #94a3b8; }
    .madj-ev-slot-pdf .slot-sublbl     { font-size: .65rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; text-align: center; }
    .madj-ev-slot-pdf .slot-fname      { font-size: .7rem; color: #1e293b; font-weight: 600; max-width: 100%; word-break: break-all; text-align: center; }
    /* -- Mobile: KPI 2--2 grid ----------------------------------- */
    @media (max-width: 575.98px) {
        .madj-kpi-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: .625rem; }
        .madj-kpi-item    { flex: none; max-width: none; min-width: 0; }
        .madj-kpi-num     { font-size: 1.15rem; }
        #madj-tabla_wrapper .dataTables_length,
        #madj-tabla_wrapper .dataTables_filter { text-align: left; }
        #madj-tabla_wrapper .dataTables_filter input {
            width: 100%;
            margin-left: 0;
        }
        #madj-tabla_wrapper .dt-layout-row:first-child,
        #madj-tabla_wrapper .dt-layout-row:last-child,
        #madj-tabla_wrapper .row:first-child,
        #madj-tabla_wrapper .row:last-child {
            padding-left: .55rem;
            padding-right: .55rem;
        }
    }
    /* -- Mobile cards (visible solo en < md) ------------------- */
    .madj-mcard {
        background: #fff;
        border-radius: .75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        padding: 1rem;
        border-left: 4px solid var(--madj-color);
    }
    .madj-mcard-nombre   { font-size: 1rem; font-weight: 700; color: #1e293b; line-height: 1.25; margin-bottom: .15rem; }
    .madj-mcard-id       { font-size: .7rem; color: #64748b; font-weight: 600; margin-bottom: .5rem; }
    .madj-mcard-meta     { display: flex; flex-wrap: wrap; gap: .35rem; align-items: center; margin-bottom: .625rem; }
    .madj-mcard-saldo    { font-weight: 700; color: #dc2626; font-size: .85rem; }
    .madj-mcard-btn {
        width: 100%; padding: .8rem 1rem;
        font-size: .95rem; font-weight: 700;
        border-radius: .5rem;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        border: none; cursor: pointer;
        background: var(--madj-color); color: #1a1a1a;
        transition: background .15s, transform .1s;
        -webkit-tap-highlight-color: transparent;
    }
    .madj-mcard-btn:active { transform: scale(.97); background: #d97706; }
    .madj-mcard-btn.is-sent { background: #2563eb; color: #fff; }
    .madj-mcard-btn.is-sent:active { background: #1d4ed8; }

    /* -- Desktop table visual refresh --------------------------- */
    .madj-table-shell {
        border-radius: .9rem;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
    }
    .madj-table thead th {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
        color: #475569;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .madj-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .72rem;
        padding-bottom: .72rem;
    }
    .madj-table tbody tr:hover {
        background: #fffdf7;
    }
    /* -- Controles DataTable (igual Gestión de Usuario) ---------- */
    #madj-tabla_wrapper .dataTables_length { margin-bottom: 1rem; }
    #madj-tabla_wrapper .dataTables_length select {
        margin: 0 .5rem;
        padding: .375rem 1.75rem .375rem .75rem;
        min-width: 74px;
        border: 1px solid #d9dee3;
        border-radius: .375rem;
        background: #fff;
        color: #374151 !important;
    }
    #madj-tabla_wrapper .dataTables_length select option { color: #111827; }
    #madj-tabla_wrapper .dataTables_filter {
        margin-bottom: 1rem;
        text-align: right;
    }
    #madj-tabla_wrapper .dataTables_filter input {
        margin-left: .5rem;
        padding: .375rem .75rem;
        border: 1px solid #d9dee3;
        border-radius: .375rem;
        background: #fff;
        color: #374151;
    }
    #madj-tabla_wrapper .dataTables_filter input:focus,
    #madj-tabla_wrapper .dataTables_length select:focus {
        border-color: #0d6efd;
        outline: none;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
    }
    #madj-tabla_wrapper .dt-layout-row:first-child,
    #madj-tabla_wrapper .row:first-child {
        padding-left: .9rem;
        padding-right: .9rem;
        padding-top: .55rem;
    }
    #madj-tabla_wrapper .dt-layout-row:last-child,
    #madj-tabla_wrapper .row:last-child {
        padding-left: .9rem;
        padding-right: .9rem;
        padding-bottom: .65rem;
        border-top: 1px solid #eef2f7;
        margin-top: .25rem;
    }
    #madj-tabla_wrapper .dataTables_info {
        font-size: .82rem;
        color: #6b7280;
        padding-top: .55rem;
    }
    #madj-tabla_wrapper .dataTables_paginate { padding-top: .35rem; }
    #madj-tabla_wrapper .paginate_button {
        border-radius: .5rem !important;
        border: 1px solid #e5e7eb !important;
        background: #fff !important;
        color: #64748b !important;
        min-width: 32px;
        height: 32px;
        line-height: 30px;
        padding: 0 .45rem !important;
        margin-left: .2rem !important;
    }
    #madj-tabla_wrapper .paginate_button.current,
    #madj-tabla_wrapper .paginate_button.current:hover {
        background: linear-gradient(180deg, #3b82f6, #2563eb) !important;
        color: #fff !important;
        border-color: #1d4ed8 !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, .28);
    }
    #madj-tabla_wrapper .paginate_button:hover {
        background: #f8fafc !important;
        color: #0f172a !important;
    }
    .madj-cell-id {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-weight: 800;
        color: #0f172a;
    }
    .madj-cell-id::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #f59e0b;
    }
    .madj-cliente-wrap {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
        min-width: 220px;
    }
    .madj-cliente-main {
        font-size: .82rem;
        font-weight: 800;
        color: #1e293b;
        text-transform: uppercase;
    }
    .madj-cliente-sub {
        font-size: .66rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: .2px;
    }
    .madj-saldo-pill {
        display: inline-flex;
        align-items: center;
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        border-radius: 999px;
        padding: .2rem .55rem;
        font-size: .74rem;
        font-weight: 800;
    }
    .madj-mora-pill {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        border-radius: 999px;
        padding: .2rem .48rem;
        font-size: .68rem;
        font-weight: 800;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .madj-mora-pill.is-high { background: #fee2e2; border-color: #fecaca; color: #b91c1c; }
    .madj-mora-pill.is-mid  { background: #fef3c7; border-color: #fde68a; color: #92400e; }
    .madj-mora-pill.is-low  { background: #dbeafe; border-color: #bfdbfe; color: #1e40af; }
    .madj-state-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .2rem .55rem;
        font-size: .68rem;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .madj-state-pill.is-active   { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
    .madj-state-pill.is-inactive { background: #e2e8f0; border-color: #cbd5e1; color: #334155; }
    .madj-asignado-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        padding: .2rem .55rem;
        font-size: .66rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .25px;
    }
    .madj-btn-ev {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border: none;
        border-radius: .55rem;
        background: linear-gradient(180deg, #f59e0b, #ea8a04);
        color: #1a1a1a;
        font-size: .78rem;
        font-weight: 800;
        padding: .45rem .72rem;
        box-shadow: 0 3px 8px rgba(245, 158, 11, .35);
        transition: transform .12s ease, box-shadow .15s ease, filter .15s ease;
    }
    .madj-btn-ev:hover {
        transform: translateY(-1px);
        filter: brightness(1.02);
        box-shadow: 0 6px 14px rgba(245, 158, 11, .36);
    }
    .madj-btn-ev:active {
        transform: translateY(0);
        box-shadow: 0 2px 5px rgba(245, 158, 11, .28);
    }
    .madj-btn-ev.is-sent {
        background: linear-gradient(180deg, #3b82f6, #2563eb);
        color: #fff;
        box-shadow: 0 3px 8px rgba(59, 130, 246, .35);
    }
    .madj-btn-ev.is-sent:hover {
        box-shadow: 0 6px 14px rgba(59, 130, 246, .36);
    }
    .madj-btn-ev.is-sent:active {
        box-shadow: 0 2px 5px rgba(59, 130, 246, .28);
    }
    .madj-accion-wrap {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: .42rem;
        min-width: 180px;
    }
    .madj-mini-progress { width: 100%; }
    .madj-mini-progress-bg {
        height: 6px;
        background: #dcfce7;
        border-radius: 999px;
        overflow: hidden;
    }
    .madj-mini-progress-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #22c55e, #16a34a);
        border-radius: 999px;
        transition: width .35s ease;
    }
    .madj-mini-progress-lbl {
        margin-top: .15rem;
        font-size: .62rem;
        font-weight: 800;
        color: #166534;
        text-align: right;
        letter-spacing: .15px;
    }
    .madj-asignacion-wrap {
        display: flex;
        flex-direction: column;
        gap: .2rem;
        min-width: 170px;
    }
    .madj-asignacion-fecha {
        font-size: .73rem;
        font-weight: 800;
        color: #1e293b;
    }
    .madj-asignacion-por {
        font-size: .65rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .25px;
    }
    /* -- Modal evidencias: barra de progreso ------------------- */
    .madj-ev-progress-wrap { margin-bottom: 1rem; }
    .madj-ev-progress-bg   { background: #e2e8f0; border-radius: .5rem; height: 8px; overflow: hidden; }
    .madj-ev-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #16a34a);
        border-radius: .5rem;
        transition: width .4s cubic-bezier(.4,0,.2,1);
        min-width: 0;
    }
    .madj-ev-progress-lbl  { font-size: .68rem; font-weight: 700; color: #64748b; text-align: right; margin-top: .2rem; }
    /* -- Mobile modal adjustments ------------------------------ */
    @media (max-width: 575.98px) {
        #modalEvidenciasMadj .modal-header { padding: .625rem 1rem; }
        #modalEvidenciasMadj .modal-body   { padding: .625rem .75rem; }
        .madj-ev-slots-wrap { display: grid !important; grid-template-columns: repeat(2, 1fr); overflow-x: visible; }
        .madj-ev-slot       { width: 100% !important; height: 140px !important; }
    }

    /* ===================================================================
       MIS ADJUDICACIONES — DARK MODE
       Selector: body.dark-mode  (alineado con el sistema del proyecto)
       =================================================================== */

    /* -- KPI Cards --------------------------------------------------- */
    body.dark-mode .madj-kpi-card {
        background: rgba(30, 41, 59, 0.88) !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.3), 0 2px 4px -1px rgba(0,0,0,.2) !important;
    }
    body.dark-mode .madj-kpi-lbl { color: #94a3b8 !important; }

    /* -- Alert contextual -------------------------------------------- */
    body.dark-mode .madj-alert {
        background: rgba(30, 41, 59, 0.7) !important;
        border-color: #475569 !important;
        border-left-color: var(--madj-color) !important;
        color: #fbbf24 !important;
    }

    /* -- Filtro bar -------------------------------------------------- */
    body.dark-mode .madj-filter-bar {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    body.dark-mode .madj-filter-bar .text-muted  { color: #94a3b8 !important; }
    body.dark-mode .madj-filter-bar .form-check-label { color: #e2e8f0 !important; }
    body.dark-mode .madj-filter-bar .form-check-input {
        background-color: #0f172a !important;
        border-color: #475569 !important;
    }

    /* -- Tabla Desktop ---------------------------------------------- */
    body.dark-mode .madj-table-shell {
        border-color: #334155 !important;
        box-shadow: 0 8px 22px rgba(0,0,0,.3) !important;
    }
    body.dark-mode .madj-table thead th {
        background: #1e293b !important;
        color: #94a3b8 !important;
        border-bottom-color: #334155 !important;
    }
    body.dark-mode .madj-table tbody td { border-color: #334155 !important; }
    body.dark-mode .madj-table tbody tr:hover { background: rgba(30,41,59,.6) !important; }

    body.dark-mode .madj-cell-id    { color: #f1f5f9 !important; }
    body.dark-mode .madj-cliente-main { color: #f1f5f9 !important; }
    body.dark-mode .madj-cliente-sub  { color: #94a3b8 !important; }
    body.dark-mode .madj-asignacion-fecha { color: #f1f5f9 !important; }
    body.dark-mode .madj-asignacion-por   { color: #94a3b8 !important; }

    /* -- Pills / Badges ---------------------------------------------- */
    body.dark-mode .madj-state-pill.is-active {
        background: #166534 !important;
        border-color: #15803d !important;
        color: #bbf7d0 !important;
    }
    body.dark-mode .madj-state-pill.is-inactive {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #94a3b8 !important;
    }
    body.dark-mode .madj-btn-ev {
        background: linear-gradient(180deg, #f59e0b, #d97706) !important;
        color: #1c1917 !important;
        box-shadow: 0 3px 8px rgba(245,158,11,.45) !important;
    }
    body.dark-mode .madj-btn-ev.is-sent {
        background: linear-gradient(180deg, #3b82f6, #2563eb) !important;
        color: #fff !important;
    }
    body.dark-mode .madj-asignado-chip {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #cbd5e1 !important;
    }
    /* Bootstrap bg-secondary-subtle badge (bucket) */
    body.dark-mode .badge.bg-secondary-subtle {
        background: #334155 !important;
        color: #94a3b8 !important;
        border-color: #475569 !important;
    }
    /* Mora badges en mobile cards */
    body.dark-mode .badge.bg-success  { background: #166534 !important; color: #dcfce7 !important; }
    body.dark-mode .badge.bg-secondary { background: #475569 !important; color: #e2e8f0 !important; }

    /* -- Mini-progreso en tabla -------------------------------------- */
    body.dark-mode .madj-mini-progress-bg  { background: #334155 !important; }
    body.dark-mode .madj-mini-progress-lbl { color: #4ade80 !important; }

    /* -- Tarjetas móviles ------------------------------------------- */
    body.dark-mode .madj-mcard {
        background: #1e293b !important;
        box-shadow: 0 2px 8px rgba(0,0,0,.3) !important;
    }
    body.dark-mode .madj-mcard-nombre { color: #f1f5f9 !important; }
    body.dark-mode .madj-mcard-id     { color: #94a3b8 !important; }

    /* -- Modal Evidencias — contenedor ------------------------------ */
    body.dark-mode #modalEvidenciasMadj .modal-content {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    body.dark-mode #modalEvidenciasMadj .modal-body {
        background: #1e293b !important;
        color: #f1f5f9 !important;
    }
    body.dark-mode #modalEvidenciasMadj .modal-footer.bg-light {
        background: #0f172a !important;
        border-top-color: #334155 !important;
    }

    /* -- Modal Evidencias — badges de estado de slot --------------- */
    body.dark-mode .madj-ev-status-pendiente {
        background: #78350f !important;
        border-color: #92400e !important;
        color: #fde68a !important;
    }
    body.dark-mode .madj-ev-status-recibido {
        background: #14532d !important;
        border-color: #166534 !important;
        color: #bbf7d0 !important;
    }
    body.dark-mode .madj-ev-status-aceptada {
        background: #14532d !important;
        border-color: #166534 !important;
        color: #bbf7d0 !important;
    }
    body.dark-mode .madj-ev-status-rechazada {
        background: #7f1d1d !important;
        border-color: #991b1b !important;
        color: #fecaca !important;
    }

    /* -- Modal Evidencias — cabeceras de sección -------------------- */
    body.dark-mode .madj-ev-hdr-orange {
        background: rgba(154,52,18,.18) !important;
        border-color: rgba(253,186,116,.25) !important;
        color: #fdba74 !important;
    }
    body.dark-mode .madj-ev-hdr-blue {
        background: rgba(30,64,175,.2) !important;
        border-color: rgba(147,197,253,.25) !important;
        color: #93c5fd !important;
    }
    body.dark-mode .madj-ev-hdr-green {
        background: rgba(22,101,52,.2) !important;
        border-color: rgba(134,239,172,.25) !important;
        color: #86efac !important;
    }

    /* -- Modal Evidencias — slots de subida ------------------------- */
    body.dark-mode .madj-ev-slots-wrap {
        background: #0f172a !important;
        border-color: #334155 !important;
    }
    body.dark-mode .madj-ev-slot {
        background: #1e293b !important;
        border-color: #475569 !important;
    }
    body.dark-mode .madj-ev-slot:hover {
        border-color: #f59e0b !important;
        background: rgba(245,158,11,.08) !important;
    }
    body.dark-mode .madj-ev-slot .slot-icon-ph { color: #64748b !important; }

    body.dark-mode .madj-ev-slot-pdf {
        background: #1e293b !important;
        border-color: #475569 !important;
    }
    body.dark-mode .madj-ev-slot-pdf:hover {
        border-color: #f59e0b !important;
        background: rgba(245,158,11,.08) !important;
    }
    body.dark-mode .madj-ev-slot-pdf .slot-icon-ph  { color: #64748b !important; }
    body.dark-mode .madj-ev-slot-pdf .slot-sublbl   { color: #94a3b8 !important; }
    body.dark-mode .madj-ev-slot-pdf .slot-fname    { color: #e2e8f0 !important; }

    /* Slot con archivo cargado: mantener borde de estado pero bg oscuro */
    body.dark-mode .madj-ev-slot.has-file         { background: #1e293b !important; }
    body.dark-mode .madj-ev-slot.has-file.status-pendiente {
        border-width: 3px !important;
        border-color: #fbbf24 !important;
        box-shadow: 0 0 0 1px rgba(251, 191, 36, 0.3);
    }
    body.dark-mode .madj-ev-slot.has-file.status-recibido,
    body.dark-mode .madj-ev-slot.has-file.status-aceptada {
        border-width: 3px !important;
        border-color: #34d399 !important;
        box-shadow: 0 0 0 1px rgba(52, 211, 153, 0.45), 0 2px 14px rgba(40, 167, 69, 0.35);
    }
    body.dark-mode .madj-ev-slot.has-file.status-rechazada {
        border-width: 3px !important;
        border-color: #f87171 !important;
        animation: madj-ev-slot-rechazo-pulse 1.15s ease-in-out infinite;
    }

    /* -- Modal Evidencias — barra de progreso ----------------------- */
    body.dark-mode .madj-ev-progress-bg  { background: #334155 !important; }
    body.dark-mode .madj-ev-progress-lbl { color: #94a3b8 !important; }

    /* -- Aviso "enviadas" en el footer del modal ------------------- */
    body.dark-mode #madj-ev-rechazo-notice {
        background: rgba(127,29,29,.25) !important;
        border-color: rgba(248,113,113,.35) !important;
    }
    body.dark-mode #madj-ev-rechazo-notice p { color: #fecaca !important; }
    body.dark-mode #madj-ev-sent-notice {
        background: rgba(30,64,175,.2) !important;
        border-color: rgba(59,130,246,.35) !important;
    }
    body.dark-mode #madj-ev-sent-notice p { color: #93c5fd !important; }

    /* -- Spinner / empty state textos ------------------------------- */
    body.dark-mode #madj-empty-state { color: #64748b !important; }
    body.dark-mode #madj-empty-state h5,
    body.dark-mode #madj-empty-state p  { color: #64748b !important; }

    /* -- Modal Evidencias: indicador de pasos ----------------------- */
    .madj-steps-indicator {
        display: flex; align-items: stretch; margin-bottom: 1rem;
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .625rem; overflow: hidden;
    }
    .madj-step-item {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: .4rem;
        padding: .6rem .75rem; font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .4px; color: #94a3b8; cursor: default; border-right: 1px solid #e2e8f0;
    }
    .madj-step-item:last-child { border-right: none; }
    .madj-step-item.active { background: #fff7ed; color: #c2410c; }
    .madj-step-item.done   { background: #f0fdf4; color: #166534; }
    .madj-step-num {
        width: 20px; height: 20px; border-radius: 50%; font-size: .6rem;
        display: flex; align-items: center; justify-content: center; font-weight: 800;
        background: #e2e8f0; color: #64748b; flex-shrink: 0;
    }
    .madj-step-item.active .madj-step-num { background: #f97316; color: #fff; }
    .madj-step-item.done   .madj-step-num { background: #22c55e; color: #fff; }

    /* -- Paso 1: formulario de datos de la moto --------------------- */
    .madj-datos-sec-hdr {
        display: flex; align-items: center; gap: .5rem;
        padding: .45rem .875rem; border-radius: .5rem .5rem 0 0;
        font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px;
    }
    .madj-datos-sec-hdr-moto { background: #fff7ed; border: 1px solid #fed7aa; border-bottom: none; color: #9a3412; }
    .madj-datos-sec-hdr-log  { background: #eff6ff; border: 1px solid #bfdbfe; border-bottom: none; color: #1e40af; }
    .madj-datos-sec-body {
        background: #f8fafc; border: 1px solid #e2e8f0; border-top: none;
        border-radius: 0 0 .5rem .5rem; padding: .75rem; margin-bottom: .875rem;
    }
    .madj-datos-field > label { font-size: .68rem; font-weight: 700; color: #475569; margin-bottom: .2rem; }
    .madj-datos-field .form-control,
    .madj-datos-field .form-select { font-size: .78rem; }
    .madj-datos-field .form-control.is-invalid,
    .madj-datos-field .form-select.is-invalid { border-color: #ef4444; }

    /* -- Paso 2: estado bloqueado ------------------------------------ */
    .madj-paso2-locked {
        background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: .625rem;
        padding: 2.5rem 1rem; text-align: center; color: #94a3b8;
    }
    .madj-paso2-locked i.madj-lock-icon { font-size: 2.25rem; display: block; margin-bottom: .625rem; }
    .madj-paso2-locked p { font-size: .78rem; font-weight: 600; margin: 0; }

    /* -- Paso 1: aviso de datos ya guardados ------------------------- */
    .madj-datos-saved-wrap {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: .5rem;
        padding: .625rem .875rem; margin-bottom: .875rem;
        display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;
    }
    .madj-datos-saved-wrap .saved-lbl {
        font-size: .75rem; font-weight: 700; color: #166534;
        display: flex; align-items: center; gap: .4rem;
    }

    /* dark-mode: steps + form + locked */
    body.dark-mode .madj-steps-indicator { background: #1e293b; border-color: #334155; }
    body.dark-mode .madj-step-item { border-color: #334155; color: #64748b; }
    body.dark-mode .madj-step-item.active { background: rgba(249,115,22,.1); color: #fb923c; }
    body.dark-mode .madj-step-item.done   { background: rgba(34,197,94,.08); color: #4ade80; }
    body.dark-mode .madj-step-num { background: #334155; color: #94a3b8; }
    body.dark-mode .madj-datos-sec-hdr-moto { background: rgba(249,115,22,.08); border-color: rgba(249,115,22,.25); color: #fb923c; }
    body.dark-mode .madj-datos-sec-hdr-log  { background: rgba(99,102,241,.08); border-color: rgba(99,102,241,.25); color: #818cf8; }
    body.dark-mode .madj-datos-sec-body { background: #1e293b; border-color: #334155; }
    body.dark-mode .madj-datos-field > label { color: #94a3b8; }
    body.dark-mode .madj-datos-field .form-control,
    body.dark-mode .madj-datos-field .form-select { background: #0f172a; border-color: #334155; color: #e2e8f0; }
    body.dark-mode .madj-paso2-locked { background: #1e293b; border-color: #334155; }
    body.dark-mode .madj-datos-saved-wrap { background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.2); }
    body.dark-mode .madj-datos-saved-wrap .saved-lbl { color: #4ade80; }
</style>

<!-- -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.-
     ENCABEZADO
     -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.- -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">
            <i class="fa-solid fa-motorcycle me-2" style="color:var(--madj-color);"></i>
            Mis Adjudicaciones
        </h4>
        <p class="text-muted small mb-0">Créditos adjudicados asignados a tu cuenta</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" id="madj-btn-refresh" onclick="madjCargar()">
            <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
        </button>
        <button class="btn btn-sm" id="madj-btn-exportar"
                style="background:var(--madj-color);color:#1a1a1a;font-weight:500;"
                onclick="madjExportar()">
            <i class="fa-solid fa-file-excel me-1"></i>Exportar Excel
        </button>
    </div>
</div>

<!-- -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.-
     ALERT CONTEXTUAL
     -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.- -->
<div class="madj-alert mb-4">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Módulo personal.</strong>
    Solo podrás ver los créditos adjudicados que te han sido asignados actualmente.
</div>

<!-- -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.-
     KPIs
     -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.- -->
<div class="madj-kpi-wrapper" id="madj-kpi-container">
    <div class="madj-kpi-item">
        <div class="card madj-kpi-card kpi-total">
            <div class="card-body">
                <i class="fa-solid fa-list madj-kpi-icon"></i>
                <div class="madj-kpi-num" id="madj-kpi-total">-"</div>
                <p class="madj-kpi-lbl">Total Asignados</p>
            </div>
        </div>
    </div>
    <div class="madj-kpi-item">
        <div class="card madj-kpi-card kpi-activos">
            <div class="card-body">
                <i class="fa-solid fa-circle-check madj-kpi-icon"></i>
                <div class="madj-kpi-num" id="madj-kpi-activos">-"</div>
                <p class="madj-kpi-lbl">Activos</p>
            </div>
        </div>
    </div>

</div>

<!-- -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.-
     FILTROS
     -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.- -->
<div class="madj-filter-bar d-flex align-items-center gap-3 flex-wrap">
    <span class="small fw-semibold text-muted">Filtrar por estado:</span>
    <div class="d-flex gap-2">
        <div class="form-check mb-0">
            <input class="form-check-input" type="radio" name="madjFiltroEstado" id="madj-f-todos" value="todos" checked onchange="madjAplicarFiltro()">
            <label class="form-check-label small" for="madj-f-todos">Todos</label>
        </div>
        <div class="form-check mb-0">
            <input class="form-check-input" type="radio" name="madjFiltroEstado" id="madj-f-activos" value="activos" onchange="madjAplicarFiltro()">
            <label class="form-check-label small" for="madj-f-activos">Activos</label>
        </div>
        <div class="form-check mb-0">
            <input class="form-check-input" type="radio" name="madjFiltroEstado" id="madj-f-inactivos" value="inactivos" onchange="madjAplicarFiltro()">
            <label class="form-check-label small" for="madj-f-inactivos">Inactivos</label>
        </div>
    </div>
</div>

<!-- -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.-
     TABLA
     -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.- -->
<div id="madj-loading" class="text-center py-3">
    <div class="spinner-border spinner-border-sm" style="color:var(--madj-color);"></div>
    <span class="ms-2 text-muted small">Cargando adjudicaciones--</span>
</div>
<div class="d-none d-md-block">
<div class="card shadow-sm border-0 madj-animate madj-table-shell">
    <div class="card-body p-0">
        <table id="madj-tabla" class="table table-hover mb-0 madj-table" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>ID Crédito</th>
                    <th>Cliente</th>
                    <th>Bucket</th>
                    <th>Estado</th>
                    <th>Asignación</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="madj-tbody"></tbody>
        </table>
        </div>
     </div>
</div>

<!-- Tarjetas móviles (visibles solo en < md) -->
<div id="madj-cards-mobile" class="d-md-none vstack gap-3 mb-3"></div>

<!-- Empty state -->
<div id="madj-empty-state" class="text-center py-5 text-muted">
    <i class="fa-solid fa-motorcycle fa-3x mb-3 d-block opacity-25"></i>
    <h5 class="fw-semibold">Sin adjudicaciones asignadas</h5>
    <p class="small mb-0">No tienes créditos adjudicados activos asignados a tu cuenta.</p>
</div>

<!-- -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.-
     MODAL -" REGISTRAR EVIDENCIAS
     -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.- -->
<div class="modal fade" id="modalEvidenciasMadj" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false"
     aria-labelledby="madj-ev-title-lbl" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"
                 style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;padding:.875rem 1.25rem;">
                <h5 class="modal-title mb-0" id="madj-ev-title-lbl">
                    <i class="fa-solid fa-camera me-2"></i>Registrar Evidencias &mdash;
                    <span id="madj-ev-titulo" style="font-weight:400;font-size:.9em;"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3" id="madj-ev-body">
                <div class="text-center py-5">
                    <div class="spinner-border" style="color:#f59e0b;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 flex-column align-items-stretch gap-2">
                <div id="madj-ev-rechazo-notice" class="d-none w-100"
                     style="background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;padding:.6rem .9rem;">
                    <p class="mb-0 small" style="color:#991b1b;font-weight:600;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Hay evidencias <strong>rechazadas</strong> (borde rojo). Reemplázalas y pulsa <strong>Enviar evidencias</strong> para que vuelvan a la bandeja de entrada de Evidencias.
                    </p>
                </div>
                <div id="madj-ev-sent-notice" class="d-none w-100"
                     style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:.5rem;padding:.6rem .9rem;">
                    <p class="mb-0 small" style="color:#1d4ed8;font-weight:600;">
                        <i class="fa-solid fa-circle-check me-1"></i>
                        Tus evidencias han sido enviadas y están siendo revisadas, mantente al tanto de cualquier cambio.
                    </p>
                </div>
                <div class="d-flex gap-2 justify-content-end w-100">
                    <button type="button" class="btn btn-sm d-none" id="madj-btn-enviar-evidencias"
                            onclick="madjEnviarEvidencias()"
                            style="background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;font-weight:700;box-shadow:0 2px 8px rgba(22,163,74,.25);">
                        <i class="fa-solid fa-paper-plane me-1"></i>Enviar evidencias
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.-
     JAVASCRIPT
     -.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.--.- -->
<?php
if (!function_exists('sparta_public_web_base')) {
    require_once dirname(__DIR__) . '/core/UploadsPaths.php';
}
$madjPublicPath = function_exists('sparta_public_web_base') ? sparta_public_web_base() : '';
?>
<script>
(function () {
    'use strict';

    var MADJ_SERVER_PUBLIC_BASE = <?php echo json_encode($madjPublicPath, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    let _todos = [];    // todos los registros cargados
    let _madjProgresoCreditos = {}; // { [id_credito]: { uploaded:number, total:number } }
    let _madjProgresoCargado  = false; // true tras resolver obtenerResumenEvidenciasCreditos
    let _madjSentCreditos = new Set(); // id_creditos donde todas las evidencias son 'recibido'

    // --------------------------------------------------------------
    // HELPERS
    // --------------------------------------------------------------
    function moneda(n) {
        const v = parseFloat(n || 0);
        return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 });
    }

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
    }

    function madjInferBaseDesdePathname() {
        const p = (window.location && window.location.pathname) || '';
        const segs = p.split('/').filter(function (x) { return x.length; });
        const k = segs.indexOf('public');
        if (k >= 0) {
            return '/' + segs.slice(0, k + 1).join('/');
        }
        return '';
    }

    function madjBasePublic() {
        if (typeof MADJ_SERVER_PUBLIC_BASE === 'string' && MADJ_SERVER_PUBLIC_BASE.length > 0) {
            return MADJ_SERVER_PUBLIC_BASE;
        }
        if (window._madjBaseCache !== undefined) {
            return window._madjBaseCache;
        }
        const path = (window.location && window.location.pathname) || '';
        let base = '';
        const i = path.indexOf('/public/');
        if (i !== -1) {
            base = path.substring(0, i + '/public'.length);
        } else {
            base = madjInferBaseDesdePathname();
        }
        window._madjBaseCache = base;
        return base;
    }

    /** Alineado a atencion_clientes_evidencias: evita http://uploads/... (host falso) y añade base pública */
    function madjUrlForDisplay(u) {
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
        const b = madjBasePublic();
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

    (function madjInstalarProteccionUploadsFalso() {
        if (window.__madjUploadsGuardInstalado) return;
        window.__madjUploadsGuardInstalado = true;

        function fix(v) {
            return madjUrlForDisplay(v);
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

    function madjSanearDomUrls(root) {
        if (!root || !root.querySelectorAll) return;
        root.querySelectorAll('[data-madj-src]').forEach(function (el) {
            const fixed = madjUrlForDisplay(el.getAttribute('data-madj-src') || '');
            if (fixed) el.setAttribute('src', fixed);
        });
        root.querySelectorAll('[src]').forEach(function (el) {
            const src = el.getAttribute('src');
            const fixed = madjUrlForDisplay(src || '');
            if (fixed && fixed !== src) el.setAttribute('src', fixed);
        });
    }

    function _madjTotalSlots() {
        return MADJ_EV_SECTIONS.reduce((acc, sec) => acc + sec.slots.length, 0);
    }

    function _madjSetProgressCredito(idCredito, uploaded) {
        const id = String(idCredito || '');
        if (!id) return;
        _madjProgresoCreditos[id] = {
            uploaded: Math.max(0, parseInt(uploaded || 0, 10)),
            total: _madjTotalSlots(),
        };
        _madjActualizarMiniProgresoTabla(id);
    }

    function _madjGetProgressCredito(idCredito) {
        const id = String(idCredito || '');
        return _madjProgresoCreditos[id] || { uploaded: 0, total: _madjTotalSlots() };
    }

    function _madjProgressHtml(idCredito) {
        const p = _madjGetProgressCredito(idCredito);
        const pct = p.total ? Math.round((p.uploaded / p.total) * 100) : 0;
        return `
            <div class="madj-mini-progress" data-credito="${esc(String(idCredito))}">
                <div class="madj-mini-progress-bg">
                    <div class="madj-mini-progress-fill" style="width:${pct}%;"></div>
                </div>
                <div class="madj-mini-progress-lbl">${p.uploaded}/${p.total}</div>
            </div>`;
    }

    function _madjActualizarMiniProgresoTabla(idCredito) {
        const p = _madjGetProgressCredito(idCredito);
        const pct = p.total ? Math.round((p.uploaded / p.total) * 100) : 0;
        const roots = document.querySelectorAll(`.madj-mini-progress[data-credito="${String(idCredito)}"]`);
        if (!roots.length) return;
        roots.forEach(root => {
            const fill = root.querySelector('.madj-mini-progress-fill');
            const lbl  = root.querySelector('.madj-mini-progress-lbl');
            if (fill) fill.style.width = pct + '%';
            if (lbl) lbl.textContent = p.uploaded + '/' + p.total;
        });
    }

    function _madjMarcarCreditoSent(idCredito, isSent) {
        const id = String(idCredito || '');
        if (!id) return;

        if (isSent) _madjSentCreditos.add(id);
        else _madjSentCreditos.delete(id);

        document.querySelectorAll(
            `button.madj-btn-ev[data-id="${id}"], button.madj-mcard-btn[data-id="${id}"]`
        ).forEach(b => {
            const icon = b.querySelector('i.fa-solid');
            const span = b.querySelector('span');

            b.classList.toggle('is-sent', !!isSent);
            b.title = isSent ? 'Ver evidencias enviadas' : 'Registrar Evidencias';

            if (icon) {
                icon.classList.toggle('fa-eye', !!isSent);
                icon.classList.toggle('fa-camera', !isSent);
            }

            if (span) {
                span.textContent = isSent ? 'Ver evidencias enviadas' : 'Registrar Evidencias';
            } else {
                b.childNodes.forEach(n => {
                    if (n.nodeType === Node.TEXT_NODE) {
                        n.nodeValue = isSent ? ' Ver evidencias enviadas' : ' Registrar Evidencias';
                    }
                });
            }
        });
    }

    /** Aplica mapa id_credito → { total, all_sent } | número (legacy) */
    function _madjAplicarMapaResumen(mapa) {
        if (!mapa || typeof mapa !== 'object') return;
        Object.keys(mapa).forEach(function (id) {
            const r = mapa[id];
            if (r && typeof r === 'object') {
                const total = Math.min(_madjTotalSlots(), Math.max(0, parseInt(r.total || 0, 10)));
                _madjSetProgressCredito(id, total);
                _madjMarcarCreditoSent(id, !!r.all_sent);
            } else {
                const total = Math.min(_madjTotalSlots(), Math.max(0, parseInt(r || 0, 10)));
                _madjSetProgressCredito(id, total);
                _madjMarcarCreditoSent(id, total >= _madjTotalSlots());
            }
        });
    }

    /**
     * @param {Array} creditos
     * @param {object|undefined} resumenInline — si viene en la misma respuesta que la lista (más rápido, sin parpadeo de botones).
     */
    function _madjPrecargarProgreso(creditos, resumenInline) {
        const ids = (creditos || []).map(c => +c.id_credito).filter(n => Number.isFinite(n) && n > 0);
        _madjProgresoCreditos = {};
        _madjProgresoCargado  = false;

        ids.forEach(id => {
            _madjProgresoCreditos[String(id)] = { uploaded: 0, total: _madjTotalSlots() };
        });

        if (resumenInline != null && typeof resumenInline === 'object') {
            _madjAplicarMapaResumen(resumenInline);
            _madjProgresoCargado = true;
            return Promise.resolve();
        }

        if (!ids.length) {
            _madjProgresoCargado = true;
            return Promise.resolve();
        }

        return fetch('/MotosAdjudicadas/obtenerResumenEvidenciasCreditos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids_credito: ids }),
        })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success || !data.resumen) return;
                _madjAplicarMapaResumen(data.resumen);
            })
            .catch(() => {})
            .finally(() => {
                _madjProgresoCargado = true;
            });
    }


    // --------------------------------------------------------------
    // CARGA PRINCIPAL
    // --------------------------------------------------------------
    /** Segunda fase: buckets desde Segundómetro (no bloquea la primera pintada). */
    function _madjEnriquecerBucketsMorosidad() {
        const ids = (_todos || []).map(c => +c.id_credito).filter(n => Number.isFinite(n) && n > 0);
        if (!ids.length) return;

        fetch('/MotosAdjudicadas/obtenerMorosidadMisCreditos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ ids_credito: ids }),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.success || !data.morosidad) return;
                const m = data.morosidad;
                let hubo = false;
                (_todos || []).forEach(function (c) {
                    const idKey = String(c.id_credito != null ? c.id_credito : '');
                    const row = m[idKey];
                    if (!row) return;
                    const bucket = String(row.bucket || '').trim();
                    if (bucket !== '' && c.bucket !== bucket) {
                        c.bucket = bucket;
                        hubo = true;
                    }
                });
                if (hubo) madjRenderizarTabla(_todos || []);
            })
            .catch(function () {});
    }

    function madjCargar() {
        const btn  = document.getElementById('madj-btn-refresh');
        const icon = btn.querySelector('i');
        icon.classList.add('fa-spin');
        btn.disabled = true;

        document.getElementById('madj-loading').style.display = 'block';

        fetch('/MotosAdjudicadas/obtenerMisAdjudicaciones?omitir_morosidad=1')
            .then(r => r.json())
            .then(data => {
                if (!data.success || !Array.isArray(data.creditos)) {
                    _todos = [];
                    _madjProgresoCreditos = {};
                    _madjProgresoCargado = false;
                    madjMostrarEmpty();
                    return Promise.resolve();
                }
                _todos = data.creditos;
                madjActualizarKPIs(_todos);
                return _madjPrecargarProgreso(_todos, data.resumen_evidencias);
            })
            .then(function () {
                madjRenderizarTabla(_todos || []);
                _madjEnriquecerBucketsMorosidad();
            })
            .catch(() => {
                Swal.fire('Error', 'No se pudieron cargar las adjudicaciones.', 'error');
            })
            .finally(() => {
                document.getElementById('madj-loading').style.display = 'none';
                icon.classList.remove('fa-spin');
                btn.disabled = false;
            });
    }

    // --------------------------------------------------------------
    // KPIs
    // --------------------------------------------------------------
    function madjActualizarKPIs(creditos) {
        const activos = creditos.filter(c => c.estado === 'Activo');

        document.getElementById('madj-kpi-total').textContent   = creditos.length;
        document.getElementById('madj-kpi-activos').textContent  = activos.length;
    }

    // --------------------------------------------------------------
    // TABLA
    // --------------------------------------------------------------
    function madjEstilizarControlesTabla() {
        const $w = $('#madj-tabla_wrapper');
        if (!$w.length) return;
        const $len = $w.find('.dataTables_length select');
        $len.addClass('form-select form-select-sm');
        $w.find('.dataTables_filter input')
            .addClass('form-control form-control-sm')
            .attr('placeholder', 'Buscar...');
        if (!$w.data('madjLenInit')) {
            if ($len.find('option[value="10"]').length) {
                $len.val('10').trigger('change');
            }
            $w.data('madjLenInit', 1);
        }
    }

    function madjRenderizarTabla(creditos) {
        if (!creditos || creditos.length === 0) {
            madjMostrarEmpty();
            return;
        }

        document.getElementById('madj-loading').style.display = 'none';
        document.getElementById('madj-empty-state').style.display = 'none';

        const filtro    = document.querySelector('input[name="madjFiltroEstado"]:checked').value;
        let filtrados   = creditos;
        if (filtro === 'activos')   filtrados = creditos.filter(c => c.estado === 'Activo');
        if (filtro === 'inactivos') filtrados = creditos.filter(c => c.estado !== 'Activo');

        const filas = filtrados.map(c => {
            const esActivo  = c.estado === 'Activo';
            const estadoBadge = `<span class="madj-state-pill ${esActivo ? 'is-active' : 'is-inactive'}">${esActivo ? 'Activo' : 'Inactivo'}</span>`;

            const nombre = c.nombre_cliente && c.nombre_cliente !== 'No disponible'
                ? `<div class="madj-cliente-wrap"><span class="madj-cliente-main">${esc(c.nombre_cliente)}</span><span class="madj-cliente-sub">ID #${esc(String(c.id_credito))}</span></div>`
                : '<span class="text-muted fst-italic">No disponible</span>';

            const bucket = c.bucket && c.bucket !== '-"'
                ? `<span class="badge bg-secondary-subtle text-secondary border" style="font-size:.68rem;">${esc(c.bucket)}</span>`
                : '<span class="text-muted">-"</span>';

            const asignacionHtml = `
                <div class="madj-asignacion-wrap">
                    <span class="madj-asignacion-fecha">${esc(c.fecha_asignacion || '-"')}</span>
                    <span class="madj-asignacion-por">${esc(c.asignado_por || 'Sistema')}</span>
                </div>`;

            const _isSent = _madjSentCreditos.has(String(c.id_credito));
            const btnAccion = `<button class="madj-btn-ev${_isSent ? ' is-sent' : ''}"
                data-id="${c.id_credito}"
                data-nombre="${esc(c.nombre_cliente || '')}"
                onclick="madjEvidenciasAbrir(+this.dataset.id, this.dataset.nombre)"
                title="${_isSent ? 'Ver evidencias enviadas' : 'Registrar Evidencias'}">
                <i class="fa-solid ${_isSent ? 'fa-eye' : 'fa-camera'}"></i>
                <span>${_isSent ? 'Ver evidencias enviadas' : 'Registrar Evidencias'}</span>
            </button>`;

            const accionHtml = `<div class="madj-accion-wrap">${btnAccion}${_madjProgressHtml(c.id_credito)}</div>`;

            return [
                `<span class="madj-cell-id">#${esc(String(c.id_credito))}</span>`,
                nombre,
                bucket,
                estadoBadge,
                asignacionHtml,
                accionHtml,
            ];
        });

        if ($.fn.DataTable.isDataTable('#madj-tabla')) {
            actualizaDatosTabla('#madj-tabla', filas, false);
            madjEstilizarControlesTabla();
        } else {
            configuraTabla('#madj-tabla', {
                registrosPorPagina: 10,
                columns: [
                    { data: 0, title: 'ID Crédito' },
                    { data: 1, title: 'Cliente' },
                    { data: 2, title: 'Bucket' },
                    { data: 3, title: 'Estado' },
                    { data: 4, title: 'Asignación' },
                    { data: 5, title: 'Acciones', orderable: false, searchable: false, className: 'text-center' },
                ]
            });
            actualizaDatosTabla('#madj-tabla', filas, false);
            madjEstilizarControlesTabla();
        }

        _madjRenderCards(filtrados);
    }

    function madjAplicarFiltro() {
        if (_todos.length) madjRenderizarTabla(_todos);
    }

    function madjMostrarEmpty() {
        document.getElementById('madj-empty-state').style.display = 'block';
        if ($.fn.DataTable.isDataTable('#madj-tabla')) {
            actualizaDatosTabla('#madj-tabla', [], false);
        } else {
            document.getElementById('madj-tbody').innerHTML = `
                <tr><td colspan="6" class="text-center py-4 text-muted fst-italic">
                    Sin adjudicaciones asignadas.
                </td></tr>`;
        }
        ['madj-kpi-total','madj-kpi-activos']
            .forEach(id => document.getElementById(id).textContent = '0');
        const _mc = document.getElementById('madj-cards-mobile');
        if (_mc) _mc.innerHTML = '';
    }

    // --------------------------------------------------------------
    // EVIDENCIAS -" ESTADO Y CONSTANTES
    // --------------------------------------------------------------
    let _madjEvState    = {};   // { slotKey: { src, type } }
    let _madjActiveOpId = null; // id_operacion activo en el modal
    let _madjActiveCreditId = null; // id_credito activo en el modal
    let _madjPendingFiles = {}; // { slotKey: File } -" ya no se usa para staging, se conserva por compatibilidad
    let _madjCommittedSlots = new Set();
    let _madjSlotEstatus = {}; // { slotKey: 'pendiente_envio' | 'recibido' }
    /** Veredicto Atención por slot: 0 sin veredicto, 1 aceptada, 2 rechazada (solo slots con val_atn en BD). */
    let _madjSlotValAtn = {};
    let _madjEnsureOpPromise = null;
    let _madjDatosMotoGuardados = false; // true cuando paso 1 está guardado
    let _madjDatosMotoData = null;       // datos logísticos guardados en paso 1
    let _madjMotoApiData   = null;       // datos de moto auto-cargados desde API externa
    let _madjMotoApiStatus = 'idle';     // 'idle' | 'loading' | 'loaded' | 'unavailable' | 'error'
    let _madjMotoApiMsg    = '';

    const MADJ_EV_SECTIONS = [
        { key: 'recoleccion', label: 'Evidencia de Recolección (Final)', headerClass: 'madj-ev-hdr-orange', icon: 'fa-camera-retro',
          slots: [
              { key: 'rec_tacometro', label: 'Tacómetro Rec.',  icon: 'fa-gauge-high',    accept: 'image/jpeg,image/png' },
              { key: 'rec_serie',     label: 'No. Serie Rec.',  icon: 'fa-hashtag',        accept: 'image/jpeg,image/png' },
              { key: 'rec_frontal',   label: 'Frontal Rec.',    icon: 'fa-camera',         accept: 'image/jpeg,image/png' },
              { key: 'rec_lateral',   label: 'Lateral Rec.',    icon: 'fa-camera-rotate',  accept: 'image/jpeg,image/png' },
          ]},
        { key: 'fisica', label: 'Evidencia Física (Momento 1)', headerClass: 'madj-ev-hdr-blue', icon: 'fa-camera',
          slots: [
              { key: 'fis_vin',       label: 'Serie VIN',       icon: 'fa-barcode',        accept: 'image/jpeg,image/png' },
              { key: 'fis_tacometro', label: 'Tacómetro',       icon: 'fa-gauge-high',     accept: 'image/jpeg,image/png' },
              { key: 'fis_frontal',   label: 'Vista Frontal',   icon: 'fa-camera',         accept: 'image/jpeg,image/png' },
              { key: 'fis_lateral',   label: 'Vista Lateral',   icon: 'fa-camera-rotate',  accept: 'image/jpeg,image/png' },
              { key: 'fis_360',       label: 'Inspección 360',  icon: 'fa-video',          accept: 'video/mp4', isVideo: true },
          ]},
    ];

    const MADJ_EV_DOCS = [];
    const MADJ_EV_ALLOWED_SLOTS = new Set(
        MADJ_EV_SECTIONS.flatMap(sec => sec.slots.map(sl => sl.key))
    );
    const MADJ_EV_VALID_ESTATUS = new Set(['pendiente_envio', 'recibido']);

    /** Motocicleta: ISO 3779 VIN máx. 17 (sin I,O,Q); placas MX moto cortas; motor sin estándar único. */
    const MADJ_VIN_MAX = 17;
    const MADJ_VIN_MIN = 8;
    const MADJ_MOTOR_MAX = 24;
    const MADJ_PLACAS_MOTO_MAX = 9;
    const MADJ_PLACAS_MOTO_MIN = 4;

    function _madjNormalizarEstatus(estatus) {
        const v = String(estatus || 'recibido').trim().toLowerCase();
        return MADJ_EV_VALID_ESTATUS.has(v) ? v : null;
    }

    // Busca la etiqueta de un slot a partir de su key
    function _madjSlotLabel(key) {
        for (const sec of MADJ_EV_SECTIONS) {
            for (const sl of sec.slots) {
                if (sl.key === key) return sl.label;
            }
        }
        return key;
    }

    // --------------------------------------------------------------
    // ABRIR MODAL
    // --------------------------------------------------------------
    function _madjDebeCargarDetalleRemoto(idCredito) {
        // Siempre cargar desde el servidor: necesitamos datos_moto además de evidencias.
        return true;
    }

    function _madjHidratarDetalleRemoto(det) {
        _madjActiveOpId = det && det.id ? det.id : _madjActiveOpId;

        (det && det.evidencias ? det.evidencias : []).forEach(ev => {
            const slotKey = String(ev.slot || '').trim();
            const estatus = _madjNormalizarEstatus(ev.estatus);
            if (!slotKey || !MADJ_EV_ALLOWED_SLOTS.has(slotKey) || !estatus || !ev.url) return;

            _madjEvState[slotKey] = { src: madjUrlForDisplay(ev.url), type: ev.tipo };
            _madjCommittedSlots.add(slotKey);
            _madjSlotEstatus[slotKey] = estatus;
            const va = parseInt(ev.val_atn, 10);
            _madjSlotValAtn[slotKey] = (va === 1 || va === 2) ? va : 0;
        });

        if (_madjActiveCreditId) {
            _madjSetProgressCredito(_madjActiveCreditId, _madjContarEvidencias());
        }

        if (det && det.datos_moto && typeof det.datos_moto === 'object' && Object.keys(det.datos_moto).length > 0) {
            _madjDatosMotoData = det.datos_moto;
            _madjDatosMotoGuardados = true;
        }
    }

    /** Hidratar respuesta de la API externa de motos */
    function _madjHidratarMotoApi(apiResp) {
        if (!apiResp) {
            _madjMotoApiStatus = 'error';
            _madjMotoApiMsg = 'Respuesta vacía al consultar REPUVE.';
            return;
        }
        _madjMotoApiMsg = String(apiResp.message || '').trim();
        if (apiResp.unavailable) {
            _madjMotoApiStatus = 'unavailable';
            return;
        }
        if (apiResp.repuve && String(apiResp.repuve.estado || '').toUpperCase() === 'PROCESANDO') {
            _madjMotoApiStatus = 'loading';
            if (!_madjMotoApiMsg) _madjMotoApiMsg = 'Consulta REPUVE en proceso. Intenta nuevamente en unos segundos.';
            return;
        }
        if (!apiResp.success || !apiResp.datos_moto) {
            _madjMotoApiStatus = 'error';
            if (!_madjMotoApiMsg) _madjMotoApiMsg = 'No se pudieron recuperar datos autocompletables desde REPUVE.';
            return;
        }
        _madjMotoApiData   = apiResp.datos_moto;
        _madjMotoApiStatus = 'loaded';
    }

    function _madjMotoApiBannerHtml() {
        if (_madjMotoApiStatus === 'loading') {
            return '<div class="alert alert-info py-2 px-3 mb-2 small">' +
                '<i class="fa-solid fa-spinner fa-spin me-1"></i>' +
                esc(_madjMotoApiMsg || 'Consultando REPUVE para autocompletar datos de la motocicleta...') +
                '</div>';
        }
        if (_madjMotoApiStatus === 'loaded') {
            return '<div class="alert alert-success py-2 px-3 mb-2 small">' +
                '<i class="fa-solid fa-circle-check me-1"></i>Datos base cargados desde REPUVE. Verifica y completa los campos faltantes.</div>';
        }
        if (_madjMotoApiStatus === 'unavailable') {
            return '<div class="alert alert-warning py-2 px-3 mb-2 small">' +
                '<i class="fa-solid fa-triangle-exclamation me-1"></i>' +
                esc(_madjMotoApiMsg || 'REPUVE no está configurado en este ambiente. Captura manual requerida.') +
                '</div>';
        }
        if (_madjMotoApiStatus === 'error') {
            return '<div class="alert alert-warning py-2 px-3 mb-2 small">' +
                '<i class="fa-solid fa-triangle-exclamation me-1"></i>' +
                esc(_madjMotoApiMsg || 'No se pudo autocompletar desde REPUVE. Puedes continuar con captura manual.') +
                '</div>';
        }
        return '';
    }

    function _madjAplicarMotoApiEnFormularioSiVacio() {
        if (!_madjMotoApiData || typeof _madjMotoApiData !== 'object') return;
        if (_madjDatosMotoGuardados) return;
        const keys = ['moto_marca', 'moto_modelo', 'moto_anio', 'moto_no_serie', 'moto_placas'];
        keys.forEach(function (k) {
            const el = document.getElementById('madj-datos-' + k);
            if (!el) return;
            if (String(el.value || '').trim() !== '') return;
            const v = String(_madjMotoApiData[k] || '').trim();
            if (!v) return;
            el.value = v;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    function _madjConsultarRepuveEnSegundoPlano(idCredito) {
        const id = parseInt(idCredito, 10);
        if (!id || id <= 0) return;
        if (_madjDatosMotoGuardados) return;
        if (_madjMotoApiStatus === 'loading' || _madjMotoApiStatus === 'loaded') return;

        _madjMotoApiStatus = 'loading';
        if (!_madjDatosMotoGuardados) {
            _madjRenderEvModalBody({});
        }

        fetch('/MotosAdjudicadas/consultarRepuveCredito', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id_credito: id }),
        })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                _madjHidratarMotoApi(resp || {});
                if (!_madjDatosMotoGuardados) {
                    _madjAplicarMotoApiEnFormularioSiVacio();
                    _madjRenderEvModalBody({});
                    _madjAplicarMotoApiEnFormularioSiVacio();
                }
            })
            .catch(function () {
                _madjMotoApiStatus = 'error';
                _madjMotoApiMsg = 'Error de red al consultar REPUVE.';
                if (!_madjDatosMotoGuardados) {
                    _madjRenderEvModalBody({});
                }
            });
    }

    function _madjAsegurarOperacionActiva() {
        if (_madjActiveOpId) {
            return Promise.resolve(_madjActiveOpId);
        }
        if (_madjEnsureOpPromise) {
            return _madjEnsureOpPromise;
        }
        if (!_madjActiveCreditId) {
            return Promise.reject(new Error('No hay un crédito activo.'));
        }

        const nombreCliente = (document.getElementById('madj-ev-titulo')?.textContent || '').trim();
        _madjEnsureOpPromise = fetch('/MotosAdjudicadas/obtenerEvidenciasCredito', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_credito: _madjActiveCreditId, nombre_cliente: nombreCliente }),
        })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success || !data.detalle) {
                    throw new Error((data && data.message) || 'No se pudo preparar la operación de evidencias.');
                }
                _madjHidratarDetalleRemoto(data.detalle);
                _madjActualizarProgreso();
                _madjActualizarBotonEnviar();
                return _madjActiveOpId;
            })
            .finally(() => {
                _madjEnsureOpPromise = null;
            });

        return _madjEnsureOpPromise;
    }

    function madjEvidenciasAbrir(idCredito, nombreCliente) {
        _madjEvState    = {};
        _madjActiveOpId = null;
        _madjActiveCreditId = idCredito;
        _madjPendingFiles = {};
        _madjCommittedSlots = new Set();
        _madjSlotEstatus = {};
        _madjSlotValAtn = {};
        _madjEnsureOpPromise = null;
        _madjDatosMotoGuardados = false;
        _madjDatosMotoData = null;
        _madjMotoApiData   = null;
        _madjMotoApiStatus = 'idle';
        _madjMotoApiMsg    = '';

        document.getElementById('madj-ev-titulo').textContent =
            nombreCliente || ('Crédito #' + idCredito);

        const modal = new bootstrap.Modal(document.getElementById('modalEvidenciasMadj'));
        modal.show();

        document.getElementById('madj-ev-body').innerHTML =
            '<div class="text-center py-5"><div class="spinner-border" style="color:#f59e0b;"></div>' +
            '<p class="mt-2 small text-muted">Cargando...</p></div>';

        fetch('/MotosAdjudicadas/obtenerEvidenciasCredito', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id_credito: idCredito, nombre_cliente: nombreCliente || '' }),
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    document.getElementById('madj-ev-body').innerHTML =
                        '<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>' +
                        esc(data.message || 'Error al cargar las evidencias.') + '</div>';
                    return;
                }
                _madjHidratarDetalleRemoto(data.detalle || {});
                _madjRenderEvModalBody(data.detalle || {});
                _madjConsultarRepuveEnSegundoPlano(idCredito);
            })
            .catch(() => {
                document.getElementById('madj-ev-body').innerHTML =
                    '<div class="alert alert-danger"><i class="fa-solid fa-circle-xmark me-2"></i>Error de red al cargar las evidencias.</div>';
            });
    }

    // --------------------------------------------------------------
    // RENDER BODY DEL MODAL — dos pasos
    // --------------------------------------------------------------
    function _madjRenderEvModalBody(det) {
        const paso1Done = _madjDatosMotoGuardados;

        // Actualizar barra mini de la tabla solo cuando paso 2 está activo
        if (_madjActiveCreditId && paso1Done) {
            _madjSetProgressCredito(_madjActiveCreditId, Object.keys(_madjEvState).length);
        }

        let html = '';

        // ── Indicador de pasos ────────────────────────────────────────
        html += `
        <div class="madj-steps-indicator">
            <div class="madj-step-item ${paso1Done ? 'done' : 'active'}">
                <div class="madj-step-num">${paso1Done ? '<i class="fa-solid fa-check" style="font-size:.55rem;"></i>' : '1'}</div>
                <span>Datos de la Moto</span>
            </div>
            <div class="madj-step-item ${paso1Done ? 'active' : ''}">
                <div class="madj-step-num">2</div>
                <span>Evidencias Fotográficas</span>
            </div>
        </div>`;

        // ── Paso 1 ────────────────────────────────────────────────────
        if (paso1Done) {
            const d = _madjDatosMotoData || {};
            const marca  = esc(d.moto_marca  || '');
            const modelo = esc(d.moto_modelo || '');
            const anio   = esc(d.moto_anio   || '');
            const serie  = esc(d.moto_no_serie || '');
            html += `
            <div class="madj-datos-saved-wrap">
                <span class="saved-lbl">
                    <i class="fa-solid fa-circle-check"></i>
                    Datos registrados &mdash; ${marca} ${modelo} ${anio}${serie ? ' &bull; Serie: ' + serie : ''}
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        style="font-size:.7rem;padding:.2rem .5rem;"
                        onclick="madjEditarDatosMoto()">
                    <i class="fa-solid fa-pen-to-square me-1"></i>Editar
                </button>
            </div>`;
        } else {
            html += _madjRenderDatosMotoForm();
        }

        // ── Paso 2 ────────────────────────────────────────────────────
        if (paso1Done) {
            const _totalSlots = MADJ_EV_SECTIONS.reduce((a, s) => a + s.slots.length, 0);
            const _uploaded   = Object.keys(_madjEvState).length;
            const _pct        = _totalSlots ? Math.round((_uploaded / _totalSlots) * 100) : 0;

            html += `
            <div class="madj-ev-progress-wrap">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size:.72rem;font-weight:700;color:#475569;">
                        <i class="fa-solid fa-images me-1" style="color:#f59e0b;"></i>Progreso de evidencias
                    </span>
                    <span class="madj-ev-progress-lbl" id="madj-ev-progreso-lbl">${_uploaded} / ${_totalSlots}</span>
                </div>
                <div class="madj-ev-progress-bg">
                    <div class="madj-ev-progress-fill" id="madj-ev-progreso-fill" style="width:${_pct}%;"></div>
                </div>
            </div>`;

            MADJ_EV_SECTIONS.forEach(sec => { html += _madjRenderEvSection(sec); });
        } else {
            html += `
            <div class="madj-paso2-locked">
                <i class="fa-solid fa-lock madj-lock-icon"></i>
                <p>Completa y guarda los datos de la motocicleta para desbloquear la subida de evidencias.</p>
            </div>`;
        }

        // Hidden file inputs (siempre en el DOM para que estén disponibles al subir)
        MADJ_EV_SECTIONS.forEach(sec => {
            sec.slots.forEach(sl => {
                const capAttr = sl.isVideo ? 'capture="camcorder"' : 'capture="environment"';
                html += `<input type="file" id="madj-finput-${sl.key}" class="d-none"
                             accept="${sl.accept}" ${capAttr}
                             onchange="madjSlotChange('${sl.key}', this)">`;
            });
        });

        const body = document.getElementById('madj-ev-body');
        body.innerHTML = html;
        madjSanearDomUrls(body);
        _madjBindDatosMotoConstraints();
        _madjActualizarBotonEnviar();
    }

    /** Filtros en vivo: VIN/placas/motor alfanuméricos; color y responsable sin dígitos. */
    function _madjBindDatosMotoConstraints() {
        const wrap = document.getElementById('madj-datos-form-wrap');
        if (!wrap) return;

        const vinStrip = /[^A-HJ-NPR-Z0-9]/gi;
        const motorStrip = /[^A-Za-z0-9\-]/g;
        const plateStrip = /[^A-Za-z0-9\-]/g;

        const serie = document.getElementById('madj-datos-moto_no_serie');
        if (serie) {
            serie.setAttribute('maxlength', String(MADJ_VIN_MAX));
            serie.addEventListener('input', function madjVinInput() {
                let v = serie.value.toUpperCase().replace(/\s/g, '').replace(vinStrip, '');
                if (v.length > MADJ_VIN_MAX) v = v.slice(0, MADJ_VIN_MAX);
                serie.value = v;
            });
        }
        const motor = document.getElementById('madj-datos-moto_no_motor');
        if (motor) {
            motor.setAttribute('maxlength', String(MADJ_MOTOR_MAX));
            motor.addEventListener('input', function madjMotorInput() {
                motor.value = motor.value.toUpperCase().replace(/\s/g, '').replace(motorStrip, '').slice(0, MADJ_MOTOR_MAX);
            });
        }
        const placas = document.getElementById('madj-datos-moto_placas');
        if (placas) {
            placas.setAttribute('maxlength', String(MADJ_PLACAS_MOTO_MAX));
            placas.addEventListener('input', function madjPlacasInput() {
                placas.value = placas.value.toUpperCase().replace(/\s/g, '').replace(plateStrip, '').slice(0, MADJ_PLACAS_MOTO_MAX);
            });
        }
        const color = document.getElementById('madj-datos-moto_color');
        if (color) {
            color.addEventListener('input', function madjColorInput() {
                color.value = color.value.replace(/[0-9]/g, '');
            });
        }
        const resp = document.getElementById('madj-datos-log_responsable');
        if (resp) {
            resp.addEventListener('input', function madjRespInput() {
                resp.value = resp.value.replace(/[0-9]/g, '');
            });
        }
    }

    // --------------------------------------------------------------
    // PASO 1 — Formulario de datos de la moto
    // --------------------------------------------------------------
    function _madjRenderDatosMotoForm() {
        const d = Object.assign({}, _madjMotoApiData || {}, _madjDatosMotoData || {});
        const v = (key) => esc(d[key] || '');

        const estadosMX = [
            'Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas',
            'Chihuahua','Ciudad de México','Coahuila','Colima','Durango','Guanajuato',
            'Guerrero','Hidalgo','Jalisco','México','Michoacán','Morelos','Nayarit',
            'Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí',
            'Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas',
        ];
        const estadoOptions = estadosMX.map(e =>
            `<option value="${esc(e)}"${d.log_estado === e ? ' selected' : ''}>${esc(e)}</option>`
        ).join('');

        return `
        <div id="madj-datos-form-wrap">
            ${_madjMotoApiBannerHtml()}
            <div class="madj-datos-sec-hdr madj-datos-sec-hdr-moto">
                <i class="fa-solid fa-motorcycle"></i> Datos de la Motocicleta
            </div>
            <div class="madj-datos-sec-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3 madj-datos-field">
                        <label for="madj-datos-moto_marca">Marca <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="madj-datos-moto_marca"
                               placeholder="Ej. Honda" maxlength="100" value="${v('moto_marca')}">
                    </div>
                    <div class="col-6 col-md-3 madj-datos-field">
                        <label for="madj-datos-moto_modelo">Modelo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="madj-datos-moto_modelo"
                               placeholder="Ej. CB125F" maxlength="100" value="${v('moto_modelo')}">
                    </div>
                    <div class="col-6 col-md-2 madj-datos-field">
                        <label for="madj-datos-moto_anio">Año <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="madj-datos-moto_anio"
                               placeholder="2022" min="1990" max="2030" value="${v('moto_anio')}">
                    </div>
                    <div class="col-6 col-md-2 madj-datos-field">
                        <label for="madj-datos-moto_color">Color <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="madj-datos-moto_color"
                               placeholder="Ej. Rojo" maxlength="50" autocomplete="off"
                               title="Solo letras (sin números)"
                               value="${v('moto_color')}">
                    </div>
                    <div class="col-6 col-md-4 madj-datos-field">
                        <label for="madj-datos-moto_no_serie">No. de Serie (VIN) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="madj-datos-moto_no_serie"
                               placeholder="Ej. MH4KC0110LK012345 (17)" maxlength="${MADJ_VIN_MAX}" autocomplete="off"
                               title="ISO 3779 (motocicleta): máximo 17 caracteres; sin I, O ni Q"
                               value="${v('moto_no_serie')}">
                    </div>
                    <div class="col-6 col-md-4 madj-datos-field">
                        <label for="madj-datos-moto_no_motor">No. de Motor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="madj-datos-moto_no_motor"
                               placeholder="Ej. JC65E-3900001" maxlength="${MADJ_MOTOR_MAX}" autocomplete="off"
                               title="Motocicleta: hasta ${MADJ_MOTOR_MAX} caracteres (letras, números, guion)"
                               value="${v('moto_no_motor')}">
                    </div>
                    <div class="col-6 col-md-4 madj-datos-field">
                        <label for="madj-datos-moto_placas">Placas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="madj-datos-moto_placas"
                               placeholder="Ej. Y001AA (moto)" maxlength="${MADJ_PLACAS_MOTO_MAX}" autocomplete="off"
                               title="Placa de motocicleta (México): típicamente 6 caracteres; máximo ${MADJ_PLACAS_MOTO_MAX}"
                               value="${v('moto_placas')}">
                    </div>
                </div>
            </div>

            <div class="madj-datos-sec-hdr madj-datos-sec-hdr-log">
                <i class="fa-solid fa-map-location-dot"></i> Datos Logísticos (Ubicación Actual)
            </div>
            <div class="madj-datos-sec-body">
                <div class="row g-2">
                    <div class="col-12 col-md-6 madj-datos-field">
                        <label for="madj-datos-log_ubicacion">Nombre del Resguardo / Almacén <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="madj-datos-log_ubicacion"
                               placeholder="Ej. Bodega Central Norte" maxlength="100" value="${v('log_ubicacion')}">
                    </div>
                    <div class="col-12 col-md-6 madj-datos-field">
                        <label for="madj-datos-log_direccion">Dirección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="madj-datos-log_direccion"
                               placeholder="Calle, número, colonia" maxlength="100" value="${v('log_direccion')}">
                    </div>
                    <div class="col-6 col-md-3 madj-datos-field">
                        <label for="madj-datos-log_ciudad">Ciudad / Municipio <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="madj-datos-log_ciudad"
                               placeholder="Monterrey" maxlength="50" value="${v('log_ciudad')}">
                    </div>
                    <div class="col-6 col-md-3 madj-datos-field">
                        <label for="madj-datos-log_estado">Estado <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="madj-datos-log_estado">
                            <option value="">— Seleccionar —</option>
                            ${estadoOptions}
                        </select>
                    </div>
                    <div class="col-6 col-md-4 madj-datos-field">
                        <label for="madj-datos-log_responsable">Responsable de Resguardo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="madj-datos-log_responsable"
                               placeholder="Nombre completo" maxlength="100" autocomplete="name"
                               title="Solo nombre (letras); sin números"
                               value="${v('log_responsable')}">
                    </div>
                    <div class="col-6 col-md-2 madj-datos-field">
                        <label for="madj-datos-log_telefono">Teléfono de Contacto <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control form-control-sm" id="madj-datos-log_telefono"
                               placeholder="10 dígitos" maxlength="10" pattern="[0-9]{10}"
                               inputmode="numeric" value="${v('log_telefono')}">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-1">
                <button type="button" class="btn btn-sm" id="madj-btn-guardar-datos"
                        onclick="madjGuardarDatosMoto()"
                        style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-weight:700;box-shadow:0 2px 8px rgba(217,119,6,.25);">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar y desbloquear evidencias
                </button>
            </div>
        </div>`;
    }

    function _madjRenderEvSection(sec) {
        let slotsHtml = '';
        sec.slots.forEach(sl => { slotsHtml += _madjRenderSlot(sl); });
        return `
        <div class="madj-ev-section">
            <div class="madj-ev-hdr ${sec.headerClass}">
                <i class="fa-solid ${sec.icon}"></i> ${esc(sec.label)}
            </div>
            <div class="madj-ev-slots-wrap">${slotsHtml}</div>
        </div>`;
    }

    function _madjSlotTieneRechazoAtn() {
        return Object.keys(_madjSlotValAtn).some(k => MADJ_EV_ALLOWED_SLOTS.has(k) && _madjSlotValAtn[k] === 2);
    }

    function _madjRenderSlot(sl) {
        const st = _madjEvState[sl.key];
        if (st && st.src) {
            const es = _madjSlotEstatus[sl.key];
            const va = parseInt(_madjSlotValAtn[sl.key], 10) || 0;
            const isPendiente = es === 'pendiente_envio';
            let statusCls;
            let statusBadge;
            if (va === 2) {
                statusCls = 'status-rechazada';
                statusBadge = '<span class="madj-ev-status-badge madj-ev-status-rechazada"><i class="fa-solid fa-xmark me-1"></i>Rechazada</span>';
            } else if (va === 1) {
                statusCls = 'status-aceptada';
                statusBadge = '<span class="madj-ev-status-badge madj-ev-status-aceptada"><i class="fa-solid fa-check me-1"></i>Aceptada</span>';
            } else {
                statusCls = isPendiente ? 'status-pendiente' : 'status-recibido';
                statusBadge = isPendiente
                    ? '<span class="madj-ev-status-badge madj-ev-status-pendiente">Por enviar</span>'
                    : '<span class="madj-ev-status-badge madj-ev-status-recibido"><i class="fa-solid fa-check me-1"></i>Enviado</span>';
            }
            /** Atención aceptó la evidencia (val_atn=1): no se permite reemplazar desde Mis adjudicaciones. */
            const mostrarBtnReemplazar = va !== 1;
            const btnRepHtml = mostrarBtnReemplazar
                ? `<button type="button" class="madj-slot-btn madj-slot-btn-rep" title="Reemplazar"
                        onclick="madjSlotTrigger('${sl.key}')">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>`
                : '';
            const media = sl.isVideo
                ? `<video class="madj-thumb" data-madj-src="${esc(st.src)}" muted playsinline></video>
                   <div class="madj-slot-vid-ov"><i class="fa-solid fa-play"></i></div>`
                : `<img class="madj-thumb" data-madj-src="${esc(st.src)}" alt="${esc(sl.label)}">`;
            return `
            <div class="madj-ev-slot has-file ${statusCls}" id="madj-slot-${sl.key}">
                ${media}
                <span class="slot-lbl">${esc(sl.label)}</span>
                ${statusBadge}
                ${btnRepHtml}
            </div>`;
        }
        return `
        <div class="madj-ev-slot" id="madj-slot-${sl.key}" onclick="madjSlotTrigger('${sl.key}')">
            <button class="madj-slot-btn madj-slot-btn-add" tabindex="-1">
                <i class="fa-solid fa-plus"></i>
            </button>
            <i class="slot-icon-ph fa-solid ${sl.icon}"></i>
            <span class="slot-lbl">${esc(sl.label)}</span>
        </div>`;
    }

    // --------------------------------------------------------------
    // PROGRESO DE EVIDENCIAS
    // --------------------------------------------------------------
    function _madjContarEvidencias() {
        return Object.keys(_madjEvState).filter(key => MADJ_EV_ALLOWED_SLOTS.has(key)).length;
    }

    function _madjActualizarBotonEnviar() {
        const btn    = document.getElementById('madj-btn-enviar-evidencias');
        const notice = document.getElementById('madj-ev-sent-notice');
        const noticeRech = document.getElementById('madj-ev-rechazo-notice');
        const totalSlots  = MADJ_EV_SECTIONS.reduce((a, s) => a + s.slots.length, 0);
        const totalFilled = _madjContarEvidencias();
        const hasPendiente = Object.values(_madjSlotEstatus).some(s => s === 'pendiente_envio');
        const allSent = totalFilled >= totalSlots && !hasPendiente && totalFilled > 0 && !_madjSlotTieneRechazoAtn();

        if (_madjActiveCreditId) {
            _madjMarcarCreditoSent(_madjActiveCreditId, allSent);
        }

        if (btn) {
            if (totalFilled >= totalSlots && hasPendiente) {
                btn.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar evidencias';
            } else {
                btn.classList.add('d-none');
            }
        }

        if (noticeRech) {
            if (_madjSlotTieneRechazoAtn()) noticeRech.classList.remove('d-none');
            else noticeRech.classList.add('d-none');
        }
        if (notice) {
            if (allSent) notice.classList.remove('d-none');
            else notice.classList.add('d-none');
        }
    }

    function _madjActualizarProgreso() {
        const total  = MADJ_EV_SECTIONS.reduce((a, s) => a + s.slots.length, 0);
        const count  = _madjContarEvidencias();
        const pct    = total ? Math.round((count / total) * 100) : 0;
        const fill   = document.getElementById('madj-ev-progreso-fill');
        const lbl    = document.getElementById('madj-ev-progreso-lbl');
        if (fill) fill.style.width = pct + '%';
        if (lbl)  lbl.textContent  = count + ' / ' + total;
    }

    // --------------------------------------------------------------
    // TARJETAS M-"VILES
    // --------------------------------------------------------------
    function _madjRenderCards(creditos) {
        const container = document.getElementById('madj-cards-mobile');
        if (!container) return;
        if (!creditos || creditos.length === 0) { container.innerHTML = ''; return; }

        container.innerHTML = creditos.map(c => {
            const esActivo  = c.estado === 'Activo';
            const nombre    = c.nombre_cliente && c.nombre_cliente !== 'No disponible'
                ? esc(c.nombre_cliente)
                : '<em class="text-muted">No disponible</em>';
            const bucketBadge = c.bucket && c.bucket !== '-"'
                ? `<span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem;">${esc(c.bucket)}</span>`
                : '';
            return `
            <div class="madj-mcard">
                <div class="madj-mcard-nombre">${nombre}</div>
                <div class="madj-mcard-id">#${esc(String(c.id_credito))} &bull; ${esc(c.asignado_por || 'Sistema')}</div>
                <div class="madj-mcard-meta">
                    ${bucketBadge}
                    <span class="badge ${esActivo ? 'bg-success' : 'bg-secondary'}">${esActivo ? 'Activo' : 'Inactivo'}</span>
                </div>
                <button class="madj-mcard-btn${_madjSentCreditos.has(String(c.id_credito)) ? ' is-sent' : ''}"
                        data-id="${+c.id_credito}"
                        data-nombre="${esc(c.nombre_cliente || '')}"
                        onclick="madjEvidenciasAbrir(+this.dataset.id, this.dataset.nombre)">
                    <i class="fa-solid ${_madjSentCreditos.has(String(c.id_credito)) ? 'fa-eye' : 'fa-camera'}"></i>
                    ${_madjSentCreditos.has(String(c.id_credito)) ? 'Ver evidencias enviadas' : 'Registrar Evidencias'}
                </button>
                ${_madjProgressHtml(c.id_credito)}
            </div>`;
        }).join('');
    }

    // --------------------------------------------------------------
    // TRIGGER / CHANGE -" FILE INPUTS
    // --------------------------------------------------------------
    function madjSlotTrigger(key) {
        document.getElementById('madj-finput-' + key)?.click();
    }

    function madjDocTrigger(key) {}
    function madjDocChange(key, input) {}

    function madjSlotChange(key, input) {
        const file = input.files[0];
        if (!file) return;
        input.value = '';

        // Mostrar estado "subiendo" inmediatamente
        const slotEl = document.getElementById('madj-slot-' + key);
        if (slotEl) {
            slotEl.classList.add('uploading', 'has-file');
            slotEl.onclick = null;
            slotEl.innerHTML =
                '<div class="madj-slot-upload-spin"><div class="spinner-border spinner-border-sm" style="color:#22c55e;"></div></div>' +
                '<span class="slot-lbl">' + esc(_madjSlotLabel(key)) + '</span>';
        }

        _madjAsegurarOperacionActiva()
            .then(() => _madjSubirPendiente(key, file))
            .catch(err => {
                // Restaurar slot vacío en caso de error
                if (slotEl) {
                    slotEl.classList.remove('uploading', 'has-file', 'status-pendiente', 'status-recibido', 'status-aceptada', 'status-rechazada');
                    slotEl.onclick = () => madjSlotTrigger(key);
                    const sl = (() => {
                        for (const sec of MADJ_EV_SECTIONS) {
                            const found = sec.slots.find(s => s.key === key);
                            if (found) return found;
                        }
                        return null;
                    })();
                    slotEl.innerHTML =
                        '<button class="madj-slot-btn madj-slot-btn-add" tabindex="-1"><i class="fa-solid fa-plus"></i></button>' +
                        '<i class="slot-icon-ph fa-solid ' + (sl ? sl.icon : 'fa-camera') + '"></i>' +
                        '<span class="slot-lbl">' + esc(_madjSlotLabel(key)) + '</span>';
                }
                Swal.fire('Error al subir', err.message || 'No se pudo guardar la evidencia.', 'error');
            });
    }

    function madjDocChange(key, input) {}

    // --------------------------------------------------------------
    // STAGE / GUARDAR EVIDENCIAS
    // --------------------------------------------------------------
    function _madjUpload(slotKey, file, isDoc) {
        const slotEl = document.getElementById('madj-slot-' + slotKey);
        const isPdf   = file.type === 'application/pdf';
        const isVideo = file.type.startsWith('video');

        _madjPendingFiles[slotKey] = file;
        _madjEvState[slotKey] = {
            src: URL.createObjectURL(file),
            type: isVideo ? 'video' : (isPdf ? 'pdf' : 'image'),
            pending: true,
        };

        if (slotEl) {
            slotEl.classList.add('has-file');
            slotEl.classList.remove('uploading');
            slotEl.onclick = null;
            if (isDoc) {
                slotEl.innerHTML =
                    `<i class="slot-icon-ph fa-solid fa-file-check" style="font-size:1.6rem;color:#22c55e;"></i>
                     <span class="slot-sublbl">Archivo seleccionado</span>
                     <button class="madj-slot-btn madj-slot-btn-rep" style="top:5px;right:5px;" title="Reemplazar"
                             onclick="madjDocTrigger('${slotKey}')">
                         <i class="fa-solid fa-arrows-rotate"></i>
                     </button>`;
            } else if (isVideo) {
                slotEl.innerHTML =
                    `<video class="madj-thumb" data-madj-src="${esc(_madjEvState[slotKey].src)}" muted playsinline></video>
                     <div class="madj-slot-vid-ov"><i class="fa-solid fa-play"></i></div>
                     <span class="slot-lbl">${esc(_madjSlotLabel(slotKey))} (pendiente)</span>
                     <button class="madj-slot-btn madj-slot-btn-rep" title="Reemplazar"
                             onclick="madjSlotTrigger('${slotKey}')">
                         <i class="fa-solid fa-arrows-rotate"></i>
                     </button>`;
            } else {
                slotEl.innerHTML =
                    `<img class="madj-thumb" data-madj-src="${esc(_madjEvState[slotKey].src)}" alt="">
                     <span class="slot-lbl">${esc(_madjSlotLabel(slotKey))} (pendiente)</span>
                     <button class="madj-slot-btn madj-slot-btn-rep" title="Reemplazar"
                             onclick="madjSlotTrigger('${slotKey}')">
                         <i class="fa-solid fa-arrows-rotate"></i>
                     </button>`;
            }
            madjSanearDomUrls(slotEl);
        }

        _madjActualizarProgreso();
        _madjActualizarBotonEnviar();
    }

    function _madjSubirPendiente(slotKey, file) {
        if (!_madjActiveOpId) {
            return Promise.reject(new Error('Operación inválida.'));
        }

        const slotEl = document.getElementById('madj-slot-' + slotKey);
        if (slotEl) {
            slotEl.classList.add('uploading');
            const spin = document.createElement('div');
            spin.className = 'madj-slot-upload-spin';
            spin.innerHTML = '<div class="spinner-border spinner-border-sm" style="color:#22c55e;"></div>';
            slotEl.appendChild(spin);
        }

        const fd = new FormData();
        fd.append('id_operacion', String(_madjActiveOpId));
        fd.append('slot', slotKey);
        fd.append('archivo', file);

        return fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo subir la evidencia.');
                }

                const isPdf   = file.type === 'application/pdf';
                const isVideo = file.type.startsWith('video');
                _madjEvState[slotKey] = {
                    src:  madjUrlForDisplay(data.url),
                    type: isVideo ? 'video' : (isPdf ? 'pdf' : 'image'),
                };
                _madjCommittedSlots.add(slotKey);
                delete _madjPendingFiles[slotKey];
                _madjSlotEstatus[slotKey] = 'pendiente_envio';
                _madjSlotValAtn[slotKey] = 0;

                const statusBadge = '<span class="madj-ev-status-badge madj-ev-status-pendiente">Por enviar</span>';

                if (slotEl) {
                    slotEl.classList.remove('uploading');
                    slotEl.querySelector('.madj-slot-upload-spin')?.remove();
                    slotEl.classList.add('has-file', 'status-pendiente');
                    slotEl.classList.remove('status-recibido', 'status-aceptada', 'status-rechazada');
                    slotEl.onclick = null;
                    if (isVideo) {
                        slotEl.innerHTML =
                            `<video class="madj-thumb" data-madj-src="${esc(madjUrlForDisplay(data.url))}" muted playsinline></video>
                             <div class="madj-slot-vid-ov"><i class="fa-solid fa-play"></i></div>
                             <span class="slot-lbl">${esc(_madjSlotLabel(slotKey))}</span>
                             ${statusBadge}
                             <button class="madj-slot-btn madj-slot-btn-rep" title="Reemplazar"
                                     onclick="madjSlotTrigger('${slotKey}')">
                                 <i class="fa-solid fa-arrows-rotate"></i>
                             </button>`;
                    } else {
                        slotEl.innerHTML =
                            `<img class="madj-thumb" data-madj-src="${esc(madjUrlForDisplay(data.url))}" alt="">
                             <span class="slot-lbl">${esc(_madjSlotLabel(slotKey))}</span>
                             ${statusBadge}
                             <button class="madj-slot-btn madj-slot-btn-rep" title="Reemplazar"
                                     onclick="madjSlotTrigger('${slotKey}')">
                                 <i class="fa-solid fa-arrows-rotate"></i>
                             </button>`;
                    }
                    madjSanearDomUrls(slotEl);
                }

                _madjActualizarProgreso();
                _madjActualizarBotonEnviar();
                if (_madjActiveCreditId) {
                    _madjSetProgressCredito(_madjActiveCreditId, _madjCommittedSlots.size);
                }
            })
            .catch(err => {
                if (slotEl) {
                    slotEl.classList.remove('uploading');
                    slotEl.querySelector('.madj-slot-upload-spin')?.remove();
                }
                throw err;
            });
    }

    async function madjEnviarEvidencias() {
        const confirm = await Swal.fire({
            title: '¿Enviar evidencias?',
            html: '<p>Al enviar, las evidencias estarán disponibles para revisión.</p>' +
                  '<p class="text-muted small mb-0">Las evidencias <strong>"Por enviar"</strong> pasarán a <strong>"Enviado"</strong>.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i>Sí, enviar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#16a34a',
        });
        if (!confirm.isConfirmed) return;

        const btn = document.getElementById('madj-btn-enviar-evidencias');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';
        }

        try {
            const resp = await fetch('/MotosAdjudicadas/enviarEvidencias', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_operacion: _madjActiveOpId }),
            }).then(r => r.json());

            if (!resp.success) throw new Error(resp.message || 'Error al enviar.');

            for (const key of Object.keys(_madjSlotEstatus)) {
                if (_madjSlotEstatus[key] === 'pendiente_envio') {
                    _madjSlotEstatus[key] = 'recibido';
                    _madjSlotValAtn[key] = 0;
                    const slotEl = document.getElementById('madj-slot-' + key);
                    if (slotEl) {
                        slotEl.classList.remove('status-pendiente', 'status-aceptada', 'status-rechazada');
                        slotEl.classList.add('status-recibido');
                        const badge = slotEl.querySelector('.madj-ev-status-badge');
                        if (badge) {
                            badge.className = 'madj-ev-status-badge madj-ev-status-recibido';
                            badge.innerHTML = '<i class="fa-solid fa-check me-1"></i>Enviado';
                        }
                    }
                }
            }

            if (btn) btn.classList.add('d-none');

            // Marcar crédito como "todo enviado" y actualizar botones en tabla/tarjetas
            if (_madjActiveCreditId) {
                _madjMarcarCreditoSent(_madjActiveCreditId, true);
            }

            // Mostrar aviso en el footer del modal
            _madjActualizarBotonEnviar();

            Swal.fire({
                icon: 'success',
                title: 'Enviadas',
                text: 'Las evidencias fueron enviadas para revisión.',
                timer: 2000,
                showConfirmButton: false,
            });
        } catch (err) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar evidencias';
            }
            Swal.fire('Error', err.message || 'No se pudieron enviar las evidencias.', 'error');
        }
    }

    // --------------------------------------------------------------
    // PASO 1 — GUARDAR / EDITAR DATOS DE LA MOTO
    // --------------------------------------------------------------
    function madjGuardarDatosMoto() {
        const campos = [
            'moto_marca', 'moto_modelo', 'moto_anio', 'moto_color',
            'moto_no_serie', 'moto_no_motor', 'moto_placas',
            'log_ubicacion', 'log_direccion', 'log_ciudad',
            'log_estado', 'log_responsable', 'log_telefono',
        ];

        let valido = true;
        const datos = {};
        const errores = [];

        campos.forEach(c => {
            const el = document.getElementById('madj-datos-' + c);
            if (!el) return;
            const val = el.value.trim();

            // Validación de vacío
            if (!val) {
                el.classList.add('is-invalid');
                valido = false;
                return;
            }

            // Validación específica: año numérico en rango
            if (c === 'moto_anio') {
                const anio = parseInt(val, 10);
                if (isNaN(anio) || anio < 1990 || anio > 2030) {
                    el.classList.add('is-invalid');
                    errores.push('El año debe ser un número entre 1990 y 2030.');
                    valido = false;
                    return;
                }
                el.classList.remove('is-invalid');
                datos[c] = val;
                return;
            }

            // VIN motocicleta — ISO 3779: hasta 17 caracteres; sin I, O, Q
            if (c === 'moto_no_serie') {
                const v = val.replace(/\s/g, '').toUpperCase();
                if (v.length < MADJ_VIN_MIN || v.length > MADJ_VIN_MAX || !/^[A-HJ-NPR-Z0-9]+$/.test(v)) {
                    el.classList.add('is-invalid');
                    errores.push(
                        'El VIN debe tener entre ' + MADJ_VIN_MIN + ' y ' + MADJ_VIN_MAX
                        + ' caracteres (solo letras permitidas sin I, O, Q y números). Estándar ISO 3779 para motos.'
                    );
                    valido = false;
                    return;
                }
                el.classList.remove('is-invalid');
                datos[c] = v;
                return;
            }

            // No. motor — típico fabricante moto (sin estándar único como el VIN)
            if (c === 'moto_no_motor') {
                const m = val.replace(/\s/g, '').toUpperCase();
                if (!m || m.length > MADJ_MOTOR_MAX || !/^[A-Z0-9\-]+$/.test(m)) {
                    el.classList.add('is-invalid');
                    errores.push('No. de motor: obligatorio, máximo ' + MADJ_MOTOR_MAX + ' caracteres (letras, números y guion).');
                    valido = false;
                    return;
                }
                el.classList.remove('is-invalid');
                datos[c] = m;
                return;
            }

            // Placas motocicleta México — serie corta (NOM / formatos estatales)
            if (c === 'moto_placas') {
                const p = val.replace(/\s/g, '').toUpperCase();
                if (p.length < MADJ_PLACAS_MOTO_MIN || p.length > MADJ_PLACAS_MOTO_MAX || !/^[A-Z0-9\-]+$/.test(p)) {
                    el.classList.add('is-invalid');
                    errores.push(
                        'Placas de motocicleta: entre ' + MADJ_PLACAS_MOTO_MIN + ' y ' + MADJ_PLACAS_MOTO_MAX
                        + ' caracteres (ej. Y001AA).'
                    );
                    valido = false;
                    return;
                }
                el.classList.remove('is-invalid');
                datos[c] = p;
                return;
            }

            // Color — solo letras (español)
            if (c === 'moto_color') {
                if (!/^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ\s]+$/u.test(val)) {
                    el.classList.add('is-invalid');
                    errores.push('El color solo debe contener letras (sin números).');
                    valido = false;
                    return;
                }
                el.classList.remove('is-invalid');
                datos[c] = val;
                return;
            }

            // Responsable — nombre sin dígitos
            if (c === 'log_responsable') {
                if (!/^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ\s'.-]+$/u.test(val)) {
                    el.classList.add('is-invalid');
                    errores.push('El responsable debe ser un nombre (letras); no se permiten números.');
                    valido = false;
                    return;
                }
                el.classList.remove('is-invalid');
                datos[c] = val;
                return;
            }

            // Validación específica: teléfono exactamente 10 dígitos
            if (c === 'log_telefono') {
                if (!/^\d{10}$/.test(val)) {
                    el.classList.add('is-invalid');
                    errores.push('El teléfono debe tener exactamente 10 dígitos numéricos.');
                    valido = false;
                    return;
                }
                el.classList.remove('is-invalid');
                datos[c] = val;
                return;
            }

            el.classList.remove('is-invalid');
            datos[c] = val;
        });

        if (!valido) {
            const msg = errores.length
                ? errores[0]
                : 'Debes completar todos los campos para continuar.';
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: msg,
                confirmButtonColor: '#f59e0b',
            });
            const primerInvalido = document.querySelector('#madj-datos-form-wrap .is-invalid');
            if (primerInvalido) primerInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const payload = {
            id_credito: _madjActiveCreditId,
            datos,
        };

        const btn = document.getElementById('madj-btn-guardar-datos');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
        }

        fetch('/MotosAdjudicadas/guardarDatosMoto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al guardar los datos.');

                _madjDatosMotoData = datos;
                _madjDatosMotoGuardados = true;
                _madjRenderEvModalBody({});

                Swal.fire({
                    icon: 'success',
                    title: '¡Datos guardados!',
                    text: 'Ahora puedes subir las evidencias fotográficas.',
                    timer: 2000,
                    showConfirmButton: false,
                });
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Guardar y desbloquear evidencias';
                }
                Swal.fire('Error', err.message || 'No se pudieron guardar los datos.', 'error');
            });
    }

    function madjEditarDatosMoto() {
        _madjDatosMotoGuardados = false;
        _madjRenderEvModalBody({});
        // Scroll al inicio del formulario
        const formWrap = document.getElementById('madj-datos-form-wrap');
        if (formWrap) formWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // --------------------------------------------------------------
    // EXPORTAR EXCEL
    // --------------------------------------------------------------
    function madjExportar() {
        if (!_todos.length) {
            Swal.fire('Sin datos', 'No hay registros para exportar.', 'warning');
            return;
        }

        const cabeceras = ['ID Crédito','Cliente','Bucket','Estado','Fecha Asignación','Asignado Por'];
        const filas = _todos.map(c => [
            c.id_credito,
            c.nombre_cliente || '',
            c.bucket || '',
            c.estado || '',
            c.fecha_asignacion || '',
            c.asignado_por || '',
        ]);

        // Usa la utilidad global si está disponible, o descarga CSV
        if (typeof exportarExcelDesdeArray === 'function') {
            exportarExcelDesdeArray(cabeceras, filas, 'Mis_Adjudicaciones');
        } else {
            const rows = [cabeceras, ...filas];
            const csv  = rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = Object.assign(document.createElement('a'), { href: url, download: 'Mis_Adjudicaciones.csv' });
            a.click();
            URL.revokeObjectURL(url);
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // GLOBALES
    // -----------------------------------------------------------------------------------------------------------------
    window.madjCargar            = madjCargar;
    window.madjAplicarFiltro     = madjAplicarFiltro;
    window.madjExportar          = madjExportar;
    window.madjEvidenciasAbrir   = madjEvidenciasAbrir;
    window.madjSlotTrigger       = madjSlotTrigger;
    window.madjDocTrigger        = madjDocTrigger;
    window.madjSlotChange        = madjSlotChange;
    window.madjDocChange         = madjDocChange;
    window.madjEnviarEvidencias  = madjEnviarEvidencias;
    window.madjGuardarDatosMoto  = madjGuardarDatosMoto;
    window.madjEditarDatosMoto   = madjEditarDatosMoto;

    // ------------------------------------------------------------------
    // SANITIZACIÓN EN TIEMPO REAL — campos del formulario de moto/logística
    // ------------------------------------------------------------------
    const _MADJ_LETTERS_ONLY = new Set(['moto_color', 'log_responsable']);
    const _MADJ_DIGITS_ONLY  = new Set(['log_telefono']);

    function _madjEsSpam(s) {
        if (!s || s.length < 6) return false;
        // 1. Mismo carácter repetido ≥6 veces seguidas → "eeeeeeee", "222222"
        if (/(.)\1{5,}/.test(s)) return true;
        // 2. Patrón corto (1-4 chars) repetido ≥4 veces → "21212121", "abababab"
        if (/^(.{1,4})\1{3,}/.test(s)) return true;
        // 3. Baja entropía: cadena ≥10 chars con pocos caracteres únicos
        //    Cubre "111122223333444455556666" (7 únicos / 33 = 0.21)
        if (s.length >= 10 && (new Set(s).size / s.length) < 0.25) return true;
        return false;
    }

    function _madjSanitizarCampo(el) {
        const campo = (el.id || '').replace('madj-datos-', '');
        let   val   = el.value;
        let   nuevo = val;

        if (_MADJ_LETTERS_ONLY.has(campo)) {
            // Solo letras (incluye acentos y ñ) y espacios simples — sin números ni especiales
            nuevo = val.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g, '');
        } else if (_MADJ_DIGITS_ONLY.has(campo)) {
            // Teléfono: solo dígitos
            nuevo = val.replace(/\D/g, '');
        } else {
            // Texto general: eliminar caracteres de inyección / shell / código
            nuevo = val.replace(/[/\\=(){}\[\]<>;:|@#%^*~`!$"']/g, '');
        }

        // Detectar entrada basura / spam — cualquier hit → limpiar campo completo
        if (_madjEsSpam(nuevo)) {
            nuevo = '';
        }

        if (nuevo !== val) {
            el.value = nuevo;
            el.classList.add('is-invalid');
            setTimeout(() => el.classList.remove('is-invalid'), 700);
        }
    }

    // Listener delegado: se activa en cualquier input dentro del formulario de moto
    document.addEventListener('input', function (e) {
        const el = e.target;
        if (el && el.id && el.id.startsWith('madj-datos-') && el.type !== 'number') {
            _madjSanitizarCampo(el);
        }
    });

    // INIT
    document.addEventListener('DOMContentLoaded', madjCargar);
    if (document.readyState !== 'loading') madjCargar();
})();
</script>

