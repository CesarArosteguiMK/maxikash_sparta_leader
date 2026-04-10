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

/* Validación */
.cc-validacion-box {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: .45rem;
    padding: .4rem .75rem;
    margin-top: .6rem;
    font-size: .82rem;
}
.cc-validacion-box i { color: #d97706; font-size: .85rem; }
.cc-validacion-box .cc-val-user { font-weight: 700; color: #92400e; }
body.dark-mode .cc-validacion-box {
    background: rgba(180, 83, 9, 0.15);
    border-color: rgba(217, 119, 6, 0.4);
}
body.dark-mode .cc-validacion-box i { color: #fbbf24; }
body.dark-mode .cc-validacion-box .cc-val-user { color: #fcd34d; }

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

<!-- ══════════════════════════════════════
     PESTAÑAS
══════════════════════════════════════ -->
<div class="card shadow-sm">
    <div class="card-body pb-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-0">
            <!-- Pestañas -->
            <ul class="nav nav-tabs cc-nav-tabs border-0 mb-0" id="ccTabs" role="tablist">
                <!-- Pestaña 1: Enviados Finalizados (activa por defecto) -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-env-finalizado-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-env-finalizado"
                            type="button" role="tab"
                            aria-controls="tab-env-finalizado" aria-selected="true">
                        <i class="fa-solid fa-circle-check me-1 text-success"></i>Enviados Finalizados
                        <span class="badge bg-success ms-1" id="badge-env-finalizado">0</span>
                    </button>
                </li>
                <!-- Pestaña 2: En Proceso -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-en-proceso-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-en-proceso"
                            type="button" role="tab"
                            aria-controls="tab-en-proceso" aria-selected="false">
                        <i class="fa-solid fa-hourglass-half me-1 text-warning"></i>En Proceso
                        <span class="badge bg-warning text-dark ms-1" id="badge-en-proceso">0</span>
                    </button>
                </li>
            </ul>

            <!-- Barra de búsqueda derecha (se muestra al cargar datos) -->
            <div id="cc-search-bar" style="display:none;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fa-solid fa-magnifying-glass text-muted" style="font-size:.8rem;"></i>
                    </span>
                    <input type="text" id="cc-input-buscar"
                           class="form-control form-control-sm border-start-0"
                           style="min-width:260px;"
                           placeholder="Buscar crédito, cliente, producto..."
                           autocomplete="off">
                    <button type="button" class="btn btn-sm btn-outline-secondary border-start-0"
                            id="cc-btn-limpiar-busqueda" title="Limpiar" style="display:none;">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     CONTENIDO DE PESTAÑAS
══════════════════════════════════════ -->
<div class="tab-content mt-3" id="ccTabContent">

    <!-- ══ PESTAÑA 1: ENVIADOS FINALIZADOS ══ -->
    <div class="tab-pane fade show active" id="tab-env-finalizado" role="tabpanel">

        <div id="loader-env-finalizado" class="text-center py-5 text-muted">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando convenios...
        </div>
        <div id="wrap-env-finalizado" class="d-none">
            <!-- Las cards se inyectan aquí dinámicamente -->
        </div>
        <div id="empty-env-finalizado" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin convenios saldados por el momento.
        </div>
        <div id="empty-busqueda" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-search fa-2x mb-2 d-block opacity-50"></i>
            Sin resultados para la búsqueda.
        </div>
    </div>

    <!-- ══ PESTAÑA 2: EN PROCESO ══ -->
    <div class="tab-pane fade" id="tab-en-proceso" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="loader-en-proceso" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    Cargando registros...
                </div>
                <div id="wrap-tabla-en-proceso" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover cc-table align-middle mb-0" id="tbl-en-proceso">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID Crédito</th>
                                    <th>Cliente</th>
                                    <th>Fecha Alta</th>
                                    <th>Usuario Alta</th>
                                    <th>Última Act.</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody id="body-en-proceso"></tbody>
                        </table>
                    </div>
                </div>
                <div id="empty-en-proceso" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Sin registros en proceso.
                </div>
            </div>
        </div>
    </div>

</div>

<script>
/* ══════════════════════════════════════════
   CIERRE DE CRÉDITO — JS
══════════════════════════════════════════ */
(function () {
    'use strict';

    /* ── Helpers ── */
    function fmt(n) {
        const v = parseFloat(n) || 0;
        return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    }
    function fmtFecha(val) {
        if (!val) return '—';
        const d = new Date(val.replace(' ', 'T'));
        if (isNaN(d)) return val;
        return d.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    function badgeEstatus(estatus) {
        if (estatus === 'en_proceso')
            return '<span class="badge badge-en-proceso rounded-pill px-3">En Proceso</span>';
        return `<span class="badge bg-secondary">${estatus}</span>`;
    }
    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ══════════════════════════════════
       RENDER: CARDS ENVIADOS FINALIZADOS
    ══════════════════════════════════ */
    let _allRows    = [];
    let _validador  = '—';

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

        // Mostrar barra de búsqueda
        document.getElementById('cc-search-bar').style.display = '';

        _pintarCards(rows);
    }

    function _pintarCards(rows) {
        const wrap         = document.getElementById('wrap-env-finalizado');
        const emptyNormal  = document.getElementById('empty-env-finalizado');
        const emptySearch  = document.getElementById('empty-busqueda');

        emptyNormal.classList.add('d-none');
        emptySearch.classList.add('d-none');

        if (!rows.length) {
            wrap.classList.add('d-none');
            wrap.innerHTML = '';
            emptySearch.classList.remove('d-none');
            return;
        }

        wrap.classList.remove('d-none');
        wrap.innerHTML = rows.map(r => buildCard(r, _validador)).join('');
    }

    /* ── Filtro en tiempo real ── */
    function ccFiltrar(termino) {
        const t = termino.trim().toLowerCase();
        const btnLimpiar = document.getElementById('cc-btn-limpiar-busqueda');
        btnLimpiar.style.display = t ? '' : 'none';

        if (!t) {
            _pintarCards(_allRows);
            return;
        }

        const filtrados = _allRows.filter(r =>
            String(r.id_credito   || '').toLowerCase().includes(t) ||
            String(r.nombre_cliente || '').toLowerCase().includes(t) ||
            String(r.nombre_producto || '').toLowerCase().includes(t) ||
            String(r.nombre_despacho || '').toLowerCase().includes(t)
        );
        _pintarCards(filtrados);
    }

    document.getElementById('cc-input-buscar')
        .addEventListener('input', function () { ccFiltrar(this.value); });

    document.getElementById('cc-btn-limpiar-busqueda')
        .addEventListener('click', function () {
            const input = document.getElementById('cc-input-buscar');
            input.value = '';
            this.style.display = 'none';
            _pintarCards(_allRows);
            input.focus();
        });

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
                    <small>${esc(r.nombre_cliente)}</small>
                </span>
                <div class="cc-progress-wrap">
                    <span class="cc-prog-label">0</span>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar"
                             style="width:${pct}%"
                             aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="cc-prog-label">100</span>
                </div>
            </div>

            <!-- Cuerpo: detalles -->
            <div class="cc-conv-card-body">
                <div class="cc-conv-details">

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Producto elegido</span>
                        <span class="cc-val">${esc(r.nombre_producto)}</span>
                        <span style="background:#e0edff; color:#1d4ed8; font-size:.75rem; font-weight:700;
                                      padding:2px 8px; border-radius:20px; margin-left:.35rem; white-space:nowrap;">
                            ${parseFloat(r.porcentaje_descuento) || 0}%
                        </span>
                    </div>

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Nombre del cliente</span>
                        <span class="cc-val">${esc(r.nombre_cliente)}</span>
                    </div>

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Total final</span>
                        <span class="cc-val fw-bold text-success">${fmt(totalPagar)}</span>
                    </div>

                    ${resumenHtml}

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Despacho a cargo</span>
                        <span class="cc-val">${despacho}</span>
                    </div>

                    <!-- Validación: usuario de sesión actual -->
                    <div class="cc-validacion-box">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Validación:</span>
                        <span class="cc-val-user">${esc(validador)}</span>
                    </div>

                </div>
            </div>

            <!-- Footer: botón confirmar -->
            <div class="cc-conv-card-footer">
                <button class="cc-btn-confirmar" onclick="ccConfirmar(${r.id}, '${esc(r.id_credito)}')"
                        id="cc-btn-${r.id}">
                    <i class="fa-solid fa-check-circle"></i>
                    Confirmar cierre
                </button>
            </div>

        </div>`;
    }

    /* ══════════════════════════════════
       RENDER: TABLA EN PROCESO
    ══════════════════════════════════ */
    function renderTablaEnProceso(rows) {
        document.getElementById('loader-en-proceso').classList.add('d-none');

        if (!rows || rows.length === 0) {
            document.getElementById('empty-en-proceso').classList.remove('d-none');
            document.getElementById('badge-en-proceso').textContent = '0';
            return;
        }

        document.getElementById('badge-en-proceso').textContent = rows.length;
        document.getElementById('wrap-tabla-en-proceso').classList.remove('d-none');

        const tbody = document.getElementById('body-en-proceso');
        tbody.innerHTML = rows.map((r, i) => `
            <tr>
                <td class="text-muted fw-semibold">${i + 1}</td>
                <td><strong>${r.id_credito}</strong></td>
                <td>${r.nombre_cliente || '—'}</td>
                <td>${fmtFecha(r.fecha_alta)}</td>
                <td>${r.usuario_alta || '—'}</td>
                <td>${fmtFecha(r.fecha_actualizacion)}</td>
                <td>${badgeEstatus(r.estatus)}</td>
            </tr>
        `).join('');
    }

    /* ══════════════════════════════════
       CARGA DE DATOS
    ══════════════════════════════════ */

    // Enviados Finalizados — carga inicial (pestaña activa)
    function cargarEnviadoFinalizado() {
        fetch('/CierreCredito/getEnviadoFinalizado', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderCards(res.datos, res.validador || '—');
            })
            .catch(err => {
                document.getElementById('loader-env-finalizado').innerHTML =
                    `<div class="alert alert-danger m-3">Error al cargar: ${err.message}</div>`;
            });
    }

    // En Proceso — lazy: solo al activar la pestaña
    let enProcesoCargado = false;
    function cargarEnProceso() {
        if (enProcesoCargado) return;
        enProcesoCargado = true;
        fetch('/CierreCredito/getEnProceso', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderTablaEnProceso(res.datos);
            })
            .catch(err => {
                document.getElementById('loader-en-proceso').innerHTML =
                    `<div class="alert alert-danger">Error al cargar: ${err.message}</div>`;
            });
    }

    document.getElementById('tab-en-proceso-btn')
        .addEventListener('shown.bs.tab', cargarEnProceso);

    // Carga inicial
    cargarEnviadoFinalizado();

    /* ══════════════════════════════════
       CONFIRMAR CIERRE
    ══════════════════════════════════ */
    window.ccConfirmar = function(idRegistro, idCredito) {
        Swal.fire({
            title: '¿Confirmar cierre?',
            html: `Se confirmará el cierre del crédito <strong>${idCredito}</strong>.<br>
                   <small class="text-muted">Esta acción quedará registrada con tu usuario.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById(`cc-btn-${idRegistro}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Confirmando...'; }

            // TODO: llamar endpoint de confirmación cuando esté listo
            setTimeout(() => {
                Swal.fire({
                    title: '¡Confirmado!',
                    text: `Cierre del crédito ${idCredito} registrado.`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                const card = document.getElementById(`cc-card-${idRegistro}`);
                if (card) card.style.opacity = '.45';
            }, 600);
        });
    };

})();
</script>
