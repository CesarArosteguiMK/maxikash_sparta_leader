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
    .madj-ev-slot.has-file.status-pendiente { border-color: #fcd34d !important; border-style: solid !important; }
    .madj-ev-slot.has-file.status-recibido  { border-color: #86efac !important; border-style: solid !important; }
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
    <div class="madj-kpi-item">
        <div class="card madj-kpi-card kpi-saldo">
            <div class="card-body">
                <i class="fa-solid fa-peso-sign madj-kpi-icon"></i>
                <div class="madj-kpi-num" id="madj-kpi-saldo" style="font-size:1.1rem;">-"</div>
                <p class="madj-kpi-lbl">Saldo Total</p>
            </div>
        </div>
    </div>
    <div class="madj-kpi-item">
        <div class="card madj-kpi-card kpi-mora">
            <div class="card-body">
                <i class="fa-solid fa-clock madj-kpi-icon"></i>
                <div class="madj-kpi-num" id="madj-kpi-mora">-"</div>
                <p class="madj-kpi-lbl">Días Mora Prom.</p>
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
                    <th>Saldo</th>
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

    function _madjPrecargarProgreso(creditos) {
        const ids = (creditos || []).map(c => +c.id_credito).filter(n => Number.isFinite(n) && n > 0);
        _madjProgresoCreditos = {};

        ids.forEach(id => {
            _madjProgresoCreditos[String(id)] = { uploaded: 0, total: _madjTotalSlots() };
        });

        if (!ids.length) {
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
                Object.keys(data.resumen).forEach(id => {
                    const r = data.resumen[id];
                    if (r && typeof r === 'object') {
                        _madjSetProgressCredito(id, r.total || 0);
                        if (r.all_sent) _madjSentCreditos.add(String(id));
                        else _madjSentCreditos.delete(String(id));
                    } else {
                        _madjSetProgressCredito(id, parseInt(r || 0, 10));
                    }
                });
            })
            .catch(() => {});
    }

    // --------------------------------------------------------------
    // CARGA PRINCIPAL
    // --------------------------------------------------------------
    function madjCargar() {
        const btn  = document.getElementById('madj-btn-refresh');
        const icon = btn.querySelector('i');
        icon.classList.add('fa-spin');
        btn.disabled = true;

        document.getElementById('madj-loading').style.display = 'block';

        fetch('/MotosAdjudicadas/obtenerMisAdjudicaciones')
            .then(r => r.json())
            .then(data => {
                if (data.success && Array.isArray(data.creditos)) {
                    _todos = data.creditos;
                    madjActualizarKPIs(_todos);
                    _madjPrecargarProgreso(_todos)
                        .finally(() => {
                            madjRenderizarTabla(_todos);
                        });
                } else {
                    _todos = [];
                    _madjProgresoCreditos = {};
                    madjMostrarEmpty();
                }
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
        const saldoTotal = creditos.reduce((acc, c) => acc + parseFloat(c.saldo || 0), 0);
        const diasMoras  = creditos.map(c => parseInt(c.dias_mora || 0));
        const promMora   = diasMoras.length
            ? Math.round(diasMoras.reduce((a, b) => a + b, 0) / diasMoras.length)
            : 0;

        document.getElementById('madj-kpi-total').textContent   = creditos.length;
        document.getElementById('madj-kpi-activos').textContent  = activos.length;
        document.getElementById('madj-kpi-saldo').textContent    = moneda(saldoTotal);
        document.getElementById('madj-kpi-mora').textContent     = promMora + 'd';
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

            const saldoHtml = `<span class="madj-saldo-pill">${moneda(c.saldo)}</span>`;

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
                saldoHtml,
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
                    { data: 2, title: 'Saldo' },
                    { data: 3, title: 'Bucket' },
                    { data: 4, title: 'Estado' },
                    { data: 5, title: 'Asignación' },
                    { data: 6, title: 'Acciones', orderable: false, searchable: false, className: 'text-center' },
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
                <tr><td colspan="7" class="text-center py-4 text-muted fst-italic">
                    Sin adjudicaciones asignadas.
                </td></tr>`;
        }
        ['madj-kpi-total','madj-kpi-activos','madj-kpi-saldo','madj-kpi-mora']
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
    function madjEvidenciasAbrir(idCredito, nombreCliente) {
        _madjEvState    = {};
        _madjActiveOpId = null;
        _madjActiveCreditId = idCredito;
        _madjPendingFiles = {};
        _madjCommittedSlots = new Set();
        _madjSlotEstatus = {};

        document.getElementById('madj-ev-titulo').textContent =
            nombreCliente || ('Crédito #' + idCredito);
        document.getElementById('madj-ev-body').innerHTML =
            '<div class="text-center py-5"><div class="spinner-border" style="color:#f59e0b;"></div></div>';

        const modal = new bootstrap.Modal(document.getElementById('modalEvidenciasMadj'));
        modal.show();

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
                        esc(data.message || 'Error al cargar.') + '</div>';
                    return;
                }
                const det = data.detalle;
                _madjActiveOpId = det.id;

                // Pre-cargar estado desde evidencias ya subidas
                (det.evidencias || []).forEach(ev => {
                    _madjEvState[ev.slot] = { src: madjUrlForDisplay(ev.url), type: ev.tipo };
                    _madjCommittedSlots.add(ev.slot);
                    _madjSlotEstatus[ev.slot] = ev.estatus || 'recibido';
                });

                _madjSetProgressCredito(idCredito, _madjContarEvidencias());

                _madjRenderEvModalBody(det);
                _madjActualizarBotonEnviar();
            })
            .catch(() => {
                document.getElementById('madj-ev-body').innerHTML =
                    '<div class="alert alert-danger"><i class="fa-solid fa-circle-xmark me-2"></i>Error de red al cargar las evidencias.</div>';
            });
    }

    // --------------------------------------------------------------
    // RENDER BODY DEL MODAL
    // --------------------------------------------------------------
    function _madjRenderEvModalBody(det) {
        const _totalSlots = MADJ_EV_SECTIONS.reduce((a, s) => a + s.slots.length, 0);
        const _uploaded   = Object.keys(_madjEvState).length;
        const _pct        = _totalSlots ? Math.round((_uploaded / _totalSlots) * 100) : 0;

        if (_madjActiveCreditId) {
            _madjSetProgressCredito(_madjActiveCreditId, _uploaded);
        }

        let html = `
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

        // Hidden file inputs -" image/video slots (capture abre cámara directamente en móvil)
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
        _madjActualizarBotonEnviar();
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

    function _madjRenderSlot(sl) {
        const st = _madjEvState[sl.key];
        if (st && st.src) {
            const es = _madjSlotEstatus[sl.key];
            const isPendiente = es === 'pendiente_envio';
            const statusCls   = isPendiente ? 'status-pendiente' : 'status-recibido';
            const statusBadge = isPendiente
                ? '<span class="madj-ev-status-badge madj-ev-status-pendiente">Por enviar</span>'
                : '<span class="madj-ev-status-badge madj-ev-status-recibido"><i class="fa-solid fa-check me-1"></i>Enviado</span>';
            const media = sl.isVideo
                ? `<video class="madj-thumb" data-madj-src="${esc(st.src)}" muted playsinline></video>
                   <div class="madj-slot-vid-ov"><i class="fa-solid fa-play"></i></div>`
                : `<img class="madj-thumb" data-madj-src="${esc(st.src)}" alt="${esc(sl.label)}">`;
            return `
            <div class="madj-ev-slot has-file ${statusCls}" id="madj-slot-${sl.key}">
                ${media}
                <span class="slot-lbl">${esc(sl.label)}</span>
                ${statusBadge}
                <button class="madj-slot-btn madj-slot-btn-rep" title="Reemplazar"
                        onclick="madjSlotTrigger('${sl.key}')">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
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
        return Object.keys(_madjEvState).length;
    }

    function _madjActualizarBotonEnviar() {
        const btn    = document.getElementById('madj-btn-enviar-evidencias');
        const notice = document.getElementById('madj-ev-sent-notice');
        const totalSlots  = MADJ_EV_SECTIONS.reduce((a, s) => a + s.slots.length, 0);
        const totalFilled = Object.keys(_madjEvState).length;
        const hasPendiente = Object.values(_madjSlotEstatus).some(s => s === 'pendiente_envio');
        const allSent = totalFilled >= totalSlots && !hasPendiente && totalFilled > 0;

        if (btn) {
            if (totalFilled >= totalSlots && hasPendiente) {
                btn.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar evidencias';
            } else {
                btn.classList.add('d-none');
            }
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
            const dias      = parseInt(c.dias_mora || 0);
            const moraColor = dias > 90 ? 'bg-danger' : dias > 30 ? 'bg-warning text-dark' : 'bg-info text-dark';
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
                    <span class="madj-mcard-saldo">${moneda(c.saldo)}</span>
                    <span class="badge ${moraColor}">${dias}d mora</span>
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

        if (!_madjActiveOpId) {
            Swal.fire('Error', 'No hay una operaci-n activa.', 'error');
            return;
        }

        // Mostrar estado "subiendo" inmediatamente
        const slotEl = document.getElementById('madj-slot-' + key);
        if (slotEl) {
            slotEl.classList.add('uploading', 'has-file');
            slotEl.onclick = null;
            slotEl.innerHTML =
                '<div class="madj-slot-upload-spin"><div class="spinner-border spinner-border-sm" style="color:#22c55e;"></div></div>' +
                '<span class="slot-lbl">' + esc(_madjSlotLabel(key)) + '</span>';
        }

        _madjSubirPendiente(key, file).catch(err => {
            // Restaurar slot vac-o en caso de error
            if (slotEl) {
                slotEl.classList.remove('uploading', 'has-file', 'status-pendiente', 'status-recibido');
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

                const statusBadge = '<span class="madj-ev-status-badge madj-ev-status-pendiente">Por enviar</span>';

                if (slotEl) {
                    slotEl.classList.remove('uploading');
                    slotEl.querySelector('.madj-slot-upload-spin')?.remove();
                    slotEl.classList.add('has-file', 'status-pendiente');
                    slotEl.classList.remove('status-recibido');
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
                    const slotEl = document.getElementById('madj-slot-' + key);
                    if (slotEl) {
                        slotEl.classList.remove('status-pendiente');
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
                _madjSentCreditos.add(String(_madjActiveCreditId));
                document.querySelectorAll(
                    `button.madj-btn-ev[data-id="${_madjActiveCreditId}"],
                     button.madj-mcard-btn[data-id="${_madjActiveCreditId}"]`
                ).forEach(b => {
                    b.classList.add('is-sent');
                    b.title = 'Ver evidencias enviadas';
                    const icon = b.querySelector('i.fa-solid');
                    if (icon) { icon.classList.remove('fa-camera'); icon.classList.add('fa-eye'); }
                    const span = b.querySelector('span');
                    if (span) span.textContent = 'Ver evidencias enviadas';
                    else if (!b.querySelector('span')) {
                        b.childNodes.forEach(n => {
                            if (n.nodeType === Node.TEXT_NODE) n.nodeValue = ' Ver evidencias enviadas';
                        });
                    }
                });
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
    // EXPORTAR EXCEL
    // --------------------------------------------------------------
    function madjExportar() {
        if (!_todos.length) {
            Swal.fire('Sin datos', 'No hay registros para exportar.', 'warning');
            return;
        }

        const cabeceras = ['ID Crédito','Cliente','Saldo','Días Mora','Bucket','Estado','Fecha Asignación','Asignado Por'];
        const filas = _todos.map(c => [
            c.id_credito,
            c.nombre_cliente || '',
            parseFloat(c.saldo || 0),
            parseInt(c.dias_mora || 0),
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

    // INIT
    document.addEventListener('DOMContentLoaded', madjCargar);
    if (document.readyState !== 'loading') madjCargar();
})();
</script>

