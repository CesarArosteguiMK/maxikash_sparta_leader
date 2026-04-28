<style>
.acd-header-gradient {
    background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.acd-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.acd-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.9; color: #fff; }
.acd-header-gradient i  { color: #fff; }

.ac-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.ac-card:hover { box-shadow: 0 4px 18px rgba(234,88,12,.14); }

.ac-card-header {
    background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%);
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
    background: #fffbeb;
    gap: .5rem;
}

.acd-btn-pipeline {
    background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%);
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
.acd-btn-pipeline:hover  { opacity: .92; transform: translateY(-1px); color: #fff; }
.acd-btn-pipeline:active { transform: translateY(0); }

body.dark-mode .ac-card              { background: #1e293b; border-color: #334155; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #292524; border-color: #334155; }
</style>

<div class="container-fluid py-4">

    <div class="acd-header-gradient d-flex align-items-center gap-3 flex-wrap">
        <i class="fa-solid fa-file-circle-check fa-2x"></i>
        <div class="flex-grow-1">
            <h4 class="d-flex align-items-center gap-2 flex-wrap">
                4.- Cierre Documentación
                <span class="badge bg-white text-dark d-none" id="acd-badge-header" style="font-size:.72rem;"></span>
            </h4>
            <p class="mb-0">Operaciones en etapa <strong>Cierre Documentado</strong> del pipeline.</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-3">
            <div id="acd-loader-main" class="text-center py-5 text-muted" style="display:block;">
                <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
            </div>
            <div id="acd-lista-main"></div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const ACD_EV_TOTAL = 10;
    const ACD_URL = '/AtencionClientes/obtenerRecuperacionCierreDocumentado';
    const ACD_VACIO = 'No hay operaciones en Cierre documentado.';

    function acdEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function acdSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${acdEsc(msg)}</span>
        </div>`;
    }

    function acdRenderCard(item) {
        const ev = parseInt(item.evidencias_count, 10) || 0;
        const g  = item.gestor_nombre
            ? acdEsc(item.gestor_nombre)
            : '<span class="text-muted fst-italic">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? acdEsc(item.fecha_asignacion)
            : '<span class="text-muted fst-italic">—</span>';
        const est = item.estatus ? acdEsc(item.estatus) : '—';
        const dias = item.dias_en_pipeline != null && item.dias_en_pipeline !== ''
            ? acdEsc(String(item.dias_en_pipeline))
            : '—';

        return `
        <div class="ac-card">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${acdEsc(String(item.id_credito))}
                    <small>${acdEsc(item.nombre_cliente || '')}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">${acdEsc(item.folio || '—')}</span>
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
                    <span class="ac-val">${ev} / ${ACD_EV_TOTAL}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Días en pipeline</span>
                    <span class="ac-val">${dias}</span>
                </div>
            </div>
            <div class="ac-card-footer">
                <a class="acd-btn-pipeline" href="/MotosAdjudicadas/pipeline" target="_blank" rel="noopener">
                    <i class="fa-solid fa-diagram-project me-1"></i>Ver en operaciones
                </a>
            </div>
        </div>`;
    }

    function acdSetBadgeHeader(n) {
        const el = document.getElementById('acd-badge-header');
        if (!el) return;
        if (n > 0) {
            el.textContent   = n;
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const loader = document.getElementById('acd-loader-main');
        const lista  = document.getElementById('acd-lista-main');
        if (!loader || !lista) return;

        fetch(ACD_URL, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const n = data.datos.length;
                acdSetBadgeHeader(n);
                if (n === 0) {
                    lista.innerHTML = acdSinDatos(ACD_VACIO);
                } else {
                    lista.innerHTML = data.datos.map(function (d) { return acdRenderCard(d); }).join('');
                }
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${acdEsc(err.message || 'Error')}</div>`;
                acdSetBadgeHeader(0);
            })
            .finally(function () {
                loader.style.display = 'none';
            });
    });
})();
</script>
