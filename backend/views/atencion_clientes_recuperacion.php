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

body.dark-mode .ac-card              { background: #1e293b; border-color: #334155; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #0f172a; border-color: #334155; }

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
        <div class="card-body p-3">
            <div id="ar-loader-main" class="text-center py-5 text-muted" style="display:block;">
                <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
            </div>
            <div id="ar-lista-main"></div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const AR_EV_TOTAL = 10;
    const AR_URL = '/AtencionClientes/obtenerRecuperacionEnTransito';
    const AR_VACIO = 'No hay operaciones en En tránsito.';

    function arEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function arSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${arEsc(msg)}</span>
        </div>`;
    }

    function arRenderCard(item) {
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
                    <span class="ac-val">${ev} / ${AR_EV_TOTAL}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Días en pipeline</span>
                    <span class="ac-val">${dias}</span>
                </div>
            </div>
            <div class="ac-card-footer">
                <a class="ar-btn-pipeline" href="/MotosAdjudicadas/pipeline" target="_blank" rel="noopener">
                    <i class="fa-solid fa-diagram-project me-1"></i>Ver en operaciones
                </a>
            </div>
        </div>`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const loader = document.getElementById('ar-loader-main');
        const lista  = document.getElementById('ar-lista-main');
        if (!loader || !lista) return;

        fetch(AR_URL, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const n = data.datos.length;
                if (n === 0) {
                    lista.innerHTML = arSinDatos(AR_VACIO);
                } else {
                    lista.innerHTML = data.datos.map(function (d) { return arRenderCard(d); }).join('');
                }
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${arEsc(err.message || 'Error')}</div>`;
            })
            .finally(function () {
                loader.style.display = 'none';
                lista.classList.remove('ar-lista-updating');
            });
    });
})();
</script>
