<style>
.acr-header-gradient {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.acr-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.acr-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.9; color: #fff; }
.acr-header-gradient i  { color: #fff; }

.ac-nav-tabs .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.6rem 1.4rem;
}
.ac-nav-tabs .nav-link.active {
    color: #6d28d9;
    border-bottom-color: #fff !important;
}
.ac-nav-tabs .nav-link:hover:not(.active) {
    color: #6d28d9;
    background: #faf5ff;
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
.ac-card:hover { box-shadow: 0 4px 18px rgba(124,58,237,.14); }
.ac-card.acr-card-dict {
    border-color: #e9d5ff;
    background: #faf5ff;
}

.ac-card-header {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
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
    background: #faf5ff;
    gap: .5rem;
}

.acr-btn-pipeline {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
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
.acr-btn-pipeline:hover  { opacity: .92; transform: translateY(-1px); color: #fff; }
.acr-btn-pipeline:active { transform: translateY(0); }

body.dark-mode .ac-nav-tabs .nav-link        { color: #94a3b8; }
body.dark-mode .ac-nav-tabs .nav-link.active { color: #c4b5fd; border-bottom-color: #1e293b !important; }
body.dark-mode .ac-nav-tabs .nav-link:hover:not(.active) { background: #3b0764; color: #c4b5fd; }
body.dark-mode .ac-card              { background: #1e293b; border-color: #334155; }
body.dark-mode .ac-card.acr-card-dict { background: #1e1b4b; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #1e1b4b; border-color: #334155; }

.acr-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}
</style>

<div class="container-fluid py-4">

    <div class="acr-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-flag-checkered fa-2x"></i>
        <div>
            <h4>5.- Recepción</h4>
            <p>Gestión de recepción para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-tabs ac-nav-tabs border-0 mb-0" id="acrTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="acr-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acrTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="acr-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acr-tab-dictaminado-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acrTabDictaminado">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Dictaminado
                        <span class="badge bg-label-secondary ms-1" id="acr-badge-dictaminado" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="acrTabContent">
            <div class="tab-pane fade show active" id="acrTabBandeja" role="tabpanel">
                <div id="acr-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acr-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="acrTabDictaminado" role="tabpanel">
                <div id="acr-loader-dictaminado" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acr-lista-dictaminado"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const ACR_EV_TOTAL = 10;

    const ACR_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecuperacionRecepcion',
            vacio: 'No hay operaciones en bandeja (Recepción).',
        },
        dictaminado: {
            url:   '/AtencionClientes/obtenerDictaminadosRecepcion',
            vacio: 'No hay operaciones dictaminadas con expediente en esta etapa.',
        },
    };

    const ACR_BADGE = { bandeja: 'acr-badge-bandeja', dictaminado: 'acr-badge-dictaminado' };

    let _acrCargada = { bandeja: false, dictaminado: false };

    function acrEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function acrSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${acrEsc(msg)}</span>
        </div>`;
    }

    function acrRenderCardBandeja(item) {
        const ev = parseInt(item.evidencias_count, 10) || 0;
        const g  = item.gestor_nombre
            ? acrEsc(item.gestor_nombre)
            : '<span class="text-muted fst-italic">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? acrEsc(item.fecha_asignacion)
            : '<span class="text-muted fst-italic">—</span>';
        const est = item.estatus ? acrEsc(item.estatus) : '—';
        const dias = item.dias_en_pipeline != null && item.dias_en_pipeline !== ''
            ? acrEsc(String(item.dias_en_pipeline))
            : '—';

        return `
        <div class="ac-card">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${acrEsc(String(item.id_credito))}
                    <small>${acrEsc(item.nombre_cliente || '')}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">${acrEsc(item.folio || '—')}</span>
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
                    <span class="ac-val">${ev} / ${ACR_EV_TOTAL}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Días en pipeline</span>
                    <span class="ac-val">${dias}</span>
                </div>
            </div>
        </div>`;
    }

    function acrRenderCardDictaminado(item) {
        const estPipeline = item.estatus ? acrEsc(item.estatus) : '—';
        const dictTxt = item.dictamen
            ? acrEsc(item.dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const fechaD = item.fecha_dictamen
            ? acrEsc(item.fecha_dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const g = item.gestor_nombre
            ? acrEsc(item.gestor_nombre)
            : '<span class="text-muted fst-italic">Sin asignar</span>';

        return `
        <div class="ac-card acr-card-dict">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${acrEsc(String(item.id_credito))}
                    <small>${acrEsc(item.nombre_cliente || '')}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">${acrEsc(item.folio || '—')}</span>
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
                    <span class="ac-val" style="white-space:pre-line;">${acrEsc(item.comentarios)}</span>
                </div>` : ''}
            </div>
        </div>`;
    }

    function acrSetBadge(key, n) {
        const el = document.getElementById(ACR_BADGE[key]);
        if (!el) return;
        if (n > 0) {
            el.textContent   = n;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    function acrCargarSeccion(key, forzar) {
        const cfg = ACR_CONFIG[key];
        if (!cfg) return;

        const suf      = key === 'bandeja' ? 'bandeja' : 'dictaminado';
        const loaderId = 'acr-loader-' + suf;
        const listaId  = 'acr-lista-'  + suf;

        if (!forzar && _acrCargada[key]) {
            return;
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return;

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('acr-lista-updating');
        }

        fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const n = data.datos.length;
                acrSetBadge(key, n);
                _acrCargada[key] = true;

                const render = key === 'bandeja' ? acrRenderCardBandeja : acrRenderCardDictaminado;
                if (n === 0) {
                    lista.innerHTML = acrSinDatos(cfg.vacio);
                } else {
                    lista.innerHTML = data.datos.map(function (d) { return render(d); }).join('');
                }
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${acrEsc(err.message || 'Error')}</div>`;
                acrSetBadge(key, 0);
            })
            .finally(function () {
                loader.style.display = 'none';
                lista.classList.remove('acr-lista-updating');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        acrCargarSeccion('bandeja', true);

        const bb = document.getElementById('acr-tab-bandeja-btn');
        const bd = document.getElementById('acr-tab-dictaminado-btn');
        if (bb) {
            bb.addEventListener('shown.bs.tab', function () { acrCargarSeccion('bandeja', false); });
        }
        if (bd) {
            bd.addEventListener('shown.bs.tab', function () { acrCargarSeccion('dictaminado', false); });
        }
    });
})();
</script>
