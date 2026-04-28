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

.ac-nav-tabs .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.6rem 1.4rem;
}
.ac-nav-tabs .nav-link.active {
    color: #c2410c;
    border-bottom-color: #fff !important;
}
.ac-nav-tabs .nav-link:hover:not(.active) {
    color: #c2410c;
    background: #fff7ed;
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
.ac-card:hover { box-shadow: 0 4px 18px rgba(234,88,12,.14); }
.ac-card.acd-card-dict {
    border-color: #fed7aa;
    background: #fffbeb;
}

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

body.dark-mode .ac-nav-tabs .nav-link        { color: #94a3b8; }
body.dark-mode .ac-nav-tabs .nav-link.active { color: #fdba74; border-bottom-color: #1e293b !important; }
body.dark-mode .ac-nav-tabs .nav-link:hover:not(.active) { background: #431407; color: #fdba74; }
body.dark-mode .ac-card              { background: #1e293b; border-color: #334155; }
body.dark-mode .ac-card.acd-card-dict { background: #292524; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #292524; border-color: #334155; }

.acd-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}

/* Modal Cierre documentación (vista 4) */
#modalAcdCierreDocumentacion .acd-cierre-banner {
    background: linear-gradient(135deg, #c7d2fe 0%, #e9d5ff 100%);
    border-radius: 0.75rem;
    padding: 0.85rem 1.25rem;
    margin: -1rem -1rem 1rem -1rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    color: #312e81;
    font-weight: 700;
    font-size: 0.95rem;
}
#modalAcdCierreDocumentacion .acd-cierre-banner i { font-size: 1.25rem; opacity: 0.9; }

.acd-cierre-etapas-box {
    border: 2px solid #22c55e;
    border-radius: 0.75rem;
    padding: 1rem 1rem 0.75rem;
    background: #fff;
    min-height: 280px;
}
.acd-cierre-etapas-lbl {
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: #16a34a;
    display: block;
    margin-bottom: 0.65rem;
}
.acd-cierre-etapa-fila {
    border: 2px solid #22c55e;
    border-radius: 0.5rem;
    padding: 0.55rem 0.75rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #14532d;
}
.acd-cierre-etapa-fila .badge { font-size: 0.68rem; }
.acd-cierre-btn-coment {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    white-space: nowrap;
}

.acd-cierre-s2-box {
    border: 3px solid #22c55e;
    border-radius: 0.75rem;
    padding: 1.25rem;
    background: #f0fdf4;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.acd-cierre-s2-text {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #14532d;
    text-align: center;
    margin-bottom: 1rem;
    line-height: 1.35;
}
body.dark-mode .acd-cierre-etapas-box,
body.dark-mode #modalAcdCierreDocumentacion .modal-content { background: #1e293b; color: #e2e8f0; }
body.dark-mode .acd-cierre-etapa-fila { background: #0f172a; color: #bbf7d0; border-color: #22c55e; }
body.dark-mode .acd-cierre-s2-box { background: #052e16; border-color: #4ade80; }
body.dark-mode .acd-cierre-s2-text { color: #bbf7d0; }
body.dark-mode #modalAcdCierreDocumentacion .acd-cierre-banner {
    background: linear-gradient(135deg, #4338ca 0%, #7c3aed 100%);
    color: #eef2ff;
}
body.dark-mode #modalAcdCierreVerBitacoraEtapa .modal-content { background: #1e293b; color: #e2e8f0; }

/* Bloque evidencia S2 (modal vista 4) */
#acdEvidenciaCard {
    border: 2px solid #fed7aa;
    border-radius: 0.75rem;
    background: #fffbeb;
    padding: 1rem 1.15rem 1.1rem;
    margin-bottom: 1rem;
}
#acdEvidenciaTitulo {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    color: #9a3412;
    margin: 0 0 0.75rem 0;
}
#acdEvidenciaTitulo.acd-ev-ok {
    color: #15803d;
}
body.dark-mode #acdEvidenciaCard {
    background: #292524;
    border-color: #78350f;
}
body.dark-mode #acdEvidenciaTitulo { color: #fdba74; }
body.dark-mode #acdEvidenciaTitulo.acd-ev-ok { color: #86efac; }
</style>

<div class="container-fluid py-4">

    <div class="acd-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-file-circle-check fa-2x"></i>
        <div>
            <h4>4.- Cierre Documentación</h4>
            <p>Gestión de cierre de documentación para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-tabs ac-nav-tabs border-0 mb-0" id="acdTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="acd-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acdTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="acd-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acd-tab-dictaminado-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acdTabDictaminado">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Dictaminado
                        <span class="badge bg-label-secondary ms-1" id="acd-badge-dictaminado" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="acdTabContent">
            <div class="tab-pane fade show active" id="acdTabBandeja" role="tabpanel">
                <div id="acd-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acd-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="acdTabDictaminado" role="tabpanel">
                <div id="acd-loader-dictaminado" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acd-lista-dictaminado"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cierre documentado — etapas previas + confirmación S2 -->
<div class="modal fade" id="modalAcdCierreDocumentacion" tabindex="-1" aria-labelledby="modalAcdCierreDocumentacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body pt-4 pb-3">
                <div class="acd-cierre-banner" role="status">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Cartera — Cierre documentado.</span>
                </div>
                <p class="text-muted small mb-3" id="acdCierreSubtitulo"></p>
                <div id="acdCierreLoader" class="text-center py-4 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando expediente…
                </div>
                <div id="acdCierreContenido" class="row g-3">
                    <div class="col-lg-5">
                        <div class="acd-cierre-etapas-box">
                            <span class="acd-cierre-etapas-lbl">ETAPAS</span>
                            <div id="acdCierreEtapasFilas"></div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="acd-cierre-s2-box">
                            <p class="acd-cierre-s2-text mb-0">
                                CONFIRMA AQUÍ QUE EL CIERRE FUE REGISTRADO EN S2 DE FORMA EXITOSA
                            </p>
                            <div class="form-check mx-auto mt-3" style="max-width:22rem;">
                                <input class="form-check-input" type="checkbox" id="acdChkConfirmoS2" autocomplete="off" disabled>
                                <label class="form-check-label small fw-semibold" for="acdChkConfirmoS2">
                                    Confirmo que el cierre quedó registrado correctamente en S2.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="acdEvidenciaCard" class="mt-4">
                    <h2 class="h6" id="acdEvidenciaTitulo">Evidencia de cierre en S2</h2>
                    <div id="acdEvidenciaFormulario">
                        <p class="small text-muted mb-2">Adjunta una imagen (o PDF) y comentarios opcionales antes de confirmar el registro en S2.</p>
                        <div class="mb-2">
                            <input type="file" class="form-control form-control-sm" id="acdEvidenciaArchivo"
                                   accept="image/jpeg,image/png,application/pdf">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1" for="acdEvidenciaComentarios">Comentarios</label>
                            <textarea class="form-control form-control-sm" id="acdEvidenciaComentarios" rows="3"
                                      placeholder="Detalle relevante para expediente…"></textarea>
                        </div>
                        <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold" id="acdBtnSubirEvidenciaCierre">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i>Subir evidencia
                        </button>
                    </div>
                    <div id="acdEvidenciaExito" style="display:none;" class="text-center py-2">
                        <p class="small text-muted mb-2 mb-md-3">Puedes verificar el archivo antes de marcar la confirmación en S2.</p>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill fw-bold" id="acdBtnVerEvidenciaCierre">
                            <i class="fa-regular fa-eye me-1"></i>Ver
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success rounded-pill btn-sm fw-bold" id="acdBtnRegistrarConfirmacionS2" disabled>
                    <i class="fa-solid fa-check me-1"></i>Registrar confirmación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal secundario: bitácora filtrada por etapa -->
<div class="modal fade" id="modalAcdCierreVerBitacoraEtapa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title mb-0" id="acdCierreBitModalTitulo">Comentarios / bitácora</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-2" id="acdCierreBitModalBody"></div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const ACD_EV_TOTAL = 10;

    const ACD_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecuperacionCierreDocumentado',
            vacio: 'No hay operaciones en bandeja (Cierre documentado).',
        },
        dictaminado: {
            url:   '/AtencionClientes/obtenerDictaminadosCierreDocumentacion',
            vacio: 'No hay operaciones dictaminadas con expediente en esta etapa.',
        },
    };

    const ACD_BADGE = { bandeja: 'acd-badge-bandeja', dictaminado: 'acd-badge-dictaminado' };

    let _acdCargada = { bandeja: false, dictaminado: false };

    let _acdCierreIdOp = 0;
    let _acdCierreBuckets = { atencion: [], validacion: [], recuperacion: [] };
    let _acdEvidenciaUrl = '';

    const ACD_CIERRE_ETAPA_META = [
        { key: 'atencion', tituloModal: 'Atención a clientes — bitácora', textoFila: 'ATENCION A CLIENTES OK' },
        { key: 'validacion', tituloModal: 'Validaciones — bitácora', textoFila: 'VALIDACIONES OK' },
        { key: 'recuperacion', tituloModal: 'Recuperación — bitácora', textoFila: 'RECUPERACION OK' },
    ];

    function acdPartirBitacoraPorEtapa(bitacora) {
        const buckets = { atencion: [], validacion: [], recuperacion: [] };
        (bitacora || []).forEach(function (b) {
            const a = String(b.accion || '');
            if (/RECUPER|TRÁNSITO|TRANSITO|RECOLECCI|REVISIÓN\s*RECUPER|REVISIÓN\s*RECUP|REVISION\s*RECUPER/i.test(a)) {
                buckets.recuperacion.push(b);
            } else if (/PROCESANDO\s*IA|VALID|CORRECCI|FACTURA|\bIA\b/i.test(a)) {
                buckets.validacion.push(b);
            } else {
                buckets.atencion.push(b);
            }
        });
        return buckets;
    }

    function acdRenderFilasEtapasCierre(buckets) {
        return ACD_CIERRE_ETAPA_META.map(function (meta) {
            const lines = buckets[meta.key] || [];
            const tiene = lines.length > 0;
            const badge = '<span class="badge bg-success">OK</span>';
            const btnDis = tiene ? '' : ' disabled';
            const btnClass = tiene ? 'btn-outline-success' : 'btn-outline-secondary';
            return `
            <div class="acd-cierre-etapa-fila">
                <span style="flex:1;min-width:0;">${meta.textoFila} ${badge}</span>
                <button type="button" class="btn ${btnClass} btn-sm acd-cierre-btn-coment"${btnDis}
                    data-acd-etapa="${meta.key}">
                    <i class="fa-regular fa-comments me-1"></i>Ver comentarios
                </button>
            </div>`;
        }).join('');
    }

    function acdModalBitacoraEtapaMostrar(key) {
        const meta = ACD_CIERRE_ETAPA_META.find(function (m) { return m.key === key; });
        const lines = _acdCierreBuckets[key] || [];
        const tituloEl = document.getElementById('acdCierreBitModalTitulo');
        const body = document.getElementById('acdCierreBitModalBody');
        if (tituloEl) tituloEl.textContent = meta ? meta.tituloModal : 'Bitácora';
        if (!body) return;
        if (!lines.length) {
            body.innerHTML = '<p class="text-muted small mb-0">No hay movimientos clasificados para esta etapa.</p>';
        } else {
            body.innerHTML = '<ul class="list-group list-group-flush">' + lines.map(function (b) {
                return `<li class="list-group-item px-2 py-2">
                    <div class="small text-muted">${acdEsc(b.fecha_alta || '')} · ${acdEsc(b.nombre_usuario || '')}</div>
                    <div style="font-size:.85rem;">${acdEsc(b.accion || '')}</div>
                </li>`;
            }).join('') + '</ul>';
        }
        const el = document.getElementById('modalAcdCierreVerBitacoraEtapa');
        if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function acdModalCierreDocLimpiarFormulario() {
        _acdCierreBuckets = { atencion: [], validacion: [], recuperacion: [] };
        _acdEvidenciaUrl = '';
        const chk = document.getElementById('acdChkConfirmoS2');
        const btn = document.getElementById('acdBtnRegistrarConfirmacionS2');
        const filas = document.getElementById('acdCierreEtapasFilas');
        const sub = document.getElementById('acdCierreSubtitulo');
        const titEv = document.getElementById('acdEvidenciaTitulo');
        const formEv = document.getElementById('acdEvidenciaFormulario');
        const okEv = document.getElementById('acdEvidenciaExito');
        const inpF = document.getElementById('acdEvidenciaArchivo');
        const txC = document.getElementById('acdEvidenciaComentarios');
        if (chk) {
            chk.checked = false;
            chk.disabled = true;
        }
        if (btn) btn.disabled = true;
        if (filas) filas.innerHTML = '';
        if (sub) sub.textContent = '';
        if (titEv) {
            titEv.textContent = 'Evidencia de cierre en S2';
            titEv.classList.remove('acd-ev-ok');
        }
        if (formEv) formEv.style.display = '';
        if (okEv) okEv.style.display = 'none';
        if (inpF) inpF.value = '';
        if (txC) txC.value = '';
    }

    window.acdModalCierreDocAbrir = function (idOperacion, idCredito, nombreCliente) {
        idOperacion = parseInt(idOperacion, 10) || 0;
        if (idOperacion <= 0) return;
        acdModalCierreDocLimpiarFormulario();
        _acdCierreIdOp = idOperacion;
        const sub = document.getElementById('acdCierreSubtitulo');
        if (sub) {
            sub.textContent = 'Crédito ' + String(idCredito || '') + (nombreCliente ? ' · ' + String(nombreCliente) : '');
        }
        const loader = document.getElementById('acdCierreLoader');
        const contenido = document.getElementById('acdCierreContenido');
        if (loader) loader.style.display = 'block';
        if (contenido) contenido.style.opacity = '0.45';

        const modalEl = document.getElementById('modalAcdCierreDocumentacion');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        fetch('/MotosAdjudicadas/obtenerDetalle/' + idOperacion + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.detalle) {
                    throw new Error(data.message || 'No se pudo cargar el detalle.');
                }
                const bit = data.detalle.bitacora || [];
                _acdCierreBuckets = acdPartirBitacoraPorEtapa(bit);
                const filas = document.getElementById('acdCierreEtapasFilas');
                if (filas) filas.innerHTML = acdRenderFilasEtapasCierre(_acdCierreBuckets);
            })
            .catch(function (err) {
                const filas = document.getElementById('acdCierreEtapasFilas');
                if (filas) {
                    filas.innerHTML = '<div class="alert alert-warning mb-0 small">' + acdEsc(err.message || 'Error') + '</div>';
                }
            })
            .finally(function () {
                if (loader) loader.style.display = 'none';
                if (contenido) contenido.style.opacity = '1';
            });
    };

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

    function acdRenderCardBandeja(item) {
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
                <button type="button" class="acd-btn-pipeline acd-abrir-modal-cierre-doc" title="Cierre documentado en S2"
                    data-acd-id-operacion="${Number(item.id)}"
                    data-acd-id-credito="${Number(item.id_credito)}"
                    data-acd-nombre="${encodeURIComponent(String(item.nombre_cliente || ''))}">
                    <i class="fa-solid fa-file-circle-check me-1"></i>Cierre documentado
                </button>
            </div>
        </div>`;
    }

    function acdRenderCardDictaminado(item) {
        const estPipeline = item.estatus ? acdEsc(item.estatus) : '—';
        const dictTxt = item.dictamen
            ? acdEsc(item.dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const fechaD = item.fecha_dictamen
            ? acdEsc(item.fecha_dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const g = item.gestor_nombre
            ? acdEsc(item.gestor_nombre)
            : '<span class="text-muted fst-italic">Sin asignar</span>';

        return `
        <div class="ac-card acd-card-dict">
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
                    <span class="ac-val" style="white-space:pre-line;">${acdEsc(item.comentarios)}</span>
                </div>` : ''}
            </div>
        </div>`;
    }

    function acdSetBadge(key, n) {
        const el = document.getElementById(ACD_BADGE[key]);
        if (!el) return;
        if (n > 0) {
            el.textContent   = n;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    function acdCargarSeccion(key, forzar) {
        const cfg = ACD_CONFIG[key];
        if (!cfg) return;

        const suf      = key === 'bandeja' ? 'bandeja' : 'dictaminado';
        const loaderId = 'acd-loader-' + suf;
        const listaId  = 'acd-lista-'  + suf;

        if (!forzar && _acdCargada[key]) {
            return;
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return;

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('acd-lista-updating');
        }

        fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const n = data.datos.length;
                acdSetBadge(key, n);
                _acdCargada[key] = true;

                const render = key === 'bandeja' ? acdRenderCardBandeja : acdRenderCardDictaminado;
                if (n === 0) {
                    lista.innerHTML = acdSinDatos(cfg.vacio);
                } else {
                    lista.innerHTML = data.datos.map(function (d) { return render(d); }).join('');
                }
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${acdEsc(err.message || 'Error')}</div>`;
                acdSetBadge(key, 0);
            })
            .finally(function () {
                loader.style.display = 'none';
                lista.classList.remove('acd-lista-updating');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        acdCargarSeccion('bandeja', true);

        const bb = document.getElementById('acd-tab-bandeja-btn');
        const bd = document.getElementById('acd-tab-dictaminado-btn');
        if (bb) {
            bb.addEventListener('shown.bs.tab', function () { acdCargarSeccion('bandeja', false); });
        }
        if (bd) {
            bd.addEventListener('shown.bs.tab', function () { acdCargarSeccion('dictaminado', false); });
        }

        const chkS2 = document.getElementById('acdChkConfirmoS2');
        const btnReg = document.getElementById('acdBtnRegistrarConfirmacionS2');
        if (chkS2 && btnReg) {
            chkS2.addEventListener('change', function () {
                btnReg.disabled = !chkS2.checked;
            });
        }

        const btnSubEv = document.getElementById('acdBtnSubirEvidenciaCierre');
        const btnVerEv = document.getElementById('acdBtnVerEvidenciaCierre');
        if (btnSubEv) {
            btnSubEv.addEventListener('click', function () {
                const idOp = _acdCierreIdOp;
                const input = document.getElementById('acdEvidenciaArchivo');
                if (!idOp || !input || !input.files || !input.files[0]) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', text: 'Selecciona una imagen o PDF.' });
                    }
                    return;
                }
                btnSubEv.disabled = true;
                const fd = new FormData();
                fd.append('id_operacion', String(idOp));
                fd.append('slot', 'doc_cierre_s2');
                fd.append('archivo', input.files[0]);
                fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success || !data.url) {
                            throw new Error(data.message || 'No se pudo subir la evidencia.');
                        }
                        _acdEvidenciaUrl = String(data.url);
                        const com = (document.getElementById('acdEvidenciaComentarios') || {}).value || '';
                        const titEv = document.getElementById('acdEvidenciaTitulo');
                        const formEv = document.getElementById('acdEvidenciaFormulario');
                        const okEv = document.getElementById('acdEvidenciaExito');
                        if (titEv) {
                            titEv.textContent = 'EVIDENCIA CARGADA CON EXITO';
                            titEv.classList.add('acd-ev-ok');
                        }
                        if (formEv) formEv.style.display = 'none';
                        if (okEv) okEv.style.display = '';
                        if (chkS2) chkS2.disabled = false;
                        if (String(com).trim() !== '') {
                            return fetch('/MotosAdjudicadas/agregarObservacion', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    id_operacion: idOp,
                                    etapa: 'Cierre Documentado',
                                    area: 'Cierre documentación',
                                    texto: 'Comentarios evidencia cierre S2: ' + String(com).trim(),
                                }),
                            }).then(function (r) { return r.json(); });
                        }
                    })
                    .catch(function (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', text: err.message || 'Error' });
                        }
                    })
                    .finally(function () {
                        btnSubEv.disabled = false;
                    });
            });
        }
        if (btnVerEv) {
            btnVerEv.addEventListener('click', function () {
                if (_acdEvidenciaUrl) {
                    window.open(_acdEvidenciaUrl, '_blank', 'noopener,noreferrer');
                }
            });
        }

        const filasEtapas = document.getElementById('acdCierreEtapasFilas');
        if (filasEtapas) {
            filasEtapas.addEventListener('click', function (e) {
                const t = e.target.closest('[data-acd-etapa]');
                if (!t || t.disabled) return;
                acdModalBitacoraEtapaMostrar(t.getAttribute('data-acd-etapa'));
            });
        }

        if (btnReg) {
            btnReg.addEventListener('click', function () {
                if (!_acdCierreIdOp || !chkS2 || !chkS2.checked) return;
                btnReg.disabled = true;
                fetch('/MotosAdjudicadas/confirmarCierreDocumentacionEnS2', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id_operacion: _acdCierreIdOp }),
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success) throw new Error(data.message || 'No se pudo registrar.');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Listo',
                                text: 'Confirmación registrada. La operación pasó a la bandeja de Recepción (vista 5).',
                                timer: 2800,
                                showConfirmButton: false,
                            });
                        }
                        const mEl = document.getElementById('modalAcdCierreDocumentacion');
                        if (mEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const inst = bootstrap.Modal.getInstance(mEl);
                            if (inst) inst.hide();
                        }
                        acdCargarSeccion('bandeja', true);
                    })
                    .catch(function (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', text: err.message || 'Error' });
                        }
                        btnReg.disabled = false;
                    });
            });
        }

        const modalCierre = document.getElementById('modalAcdCierreDocumentacion');
        if (modalCierre) {
            modalCierre.addEventListener('hidden.bs.modal', function () {
                _acdCierreIdOp = 0;
                acdModalCierreDocLimpiarFormulario();
            });
        }

        /* Delegación: onclick inline + JSON.stringify(nombre) rompía el HTML por comillas dobles en el atributo */
        const listaBandeja = document.getElementById('acd-lista-bandeja');
        if (listaBandeja) {
            listaBandeja.addEventListener('click', function (e) {
                const btn = e.target.closest('.acd-abrir-modal-cierre-doc');
                if (!btn) {
                    return;
                }
                const idOp = parseInt(btn.getAttribute('data-acd-id-operacion'), 10) || 0;
                const idCred = parseInt(btn.getAttribute('data-acd-id-credito'), 10) || 0;
                let nombre = '';
                try {
                    nombre = decodeURIComponent(btn.getAttribute('data-acd-nombre') || '');
                } catch (err2) {
                    nombre = '';
                }
                if (typeof window.acdModalCierreDocAbrir === 'function') {
                    window.acdModalCierreDocAbrir(idOp, idCred, nombre);
                }
            });
        }
    });
})();
</script>
