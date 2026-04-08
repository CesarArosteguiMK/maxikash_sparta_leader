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
.cc-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; }
.cc-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.85; }

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

/* ── Tabla ── */
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

/* ── Dark mode ── */
body.dark-mode .cc-nav-tabs .nav-link { color: #94a3b8; }
body.dark-mode .cc-nav-tabs .nav-link.active { color: #60a5fa; border-bottom-color: #1e293b !important; }
body.dark-mode .cc-nav-tabs .nav-link:hover:not(.active) { background: #1e3a5f; color: #60a5fa; }
body.dark-mode .cc-table th { background: #1e293b; color: #94a3b8; }
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
        <ul class="nav nav-tabs cc-nav-tabs" id="ccTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-en-proceso-btn"
                        data-bs-toggle="tab" data-bs-target="#tab-en-proceso"
                        type="button" role="tab"
                        aria-controls="tab-en-proceso" aria-selected="true">
                    <i class="fa-solid fa-hourglass-half me-1 text-warning"></i>En Proceso
                    <span class="badge bg-warning text-dark ms-1" id="badge-en-proceso">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-env-finalizado-btn"
                        data-bs-toggle="tab" data-bs-target="#tab-env-finalizado"
                        type="button" role="tab"
                        aria-controls="tab-env-finalizado" aria-selected="false">
                    <i class="fa-solid fa-circle-check me-1 text-success"></i>Enviado Finalizado
                    <span class="badge bg-success ms-1" id="badge-env-finalizado">0</span>
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- ══════════════════════════════════════
     CONTENIDO DE PESTAÑAS
══════════════════════════════════════ -->
<div class="tab-content mt-3" id="ccTabContent">

    <!-- ── En Proceso ── -->
    <div class="tab-pane fade show active" id="tab-en-proceso" role="tabpanel">
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

    <!-- ── Enviado Finalizado ── -->
    <div class="tab-pane fade" id="tab-env-finalizado" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="loader-env-finalizado" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    Cargando registros...
                </div>
                <div id="wrap-tabla-env-finalizado" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover cc-table align-middle mb-0" id="tbl-env-finalizado">
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
                            <tbody id="body-env-finalizado"></tbody>
                        </table>
                    </div>
                </div>
                <div id="empty-env-finalizado" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Sin registros enviados / finalizados.
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
    function fmtFecha(val) {
        if (!val) return '—';
        const d = new Date(val.replace(' ', 'T'));
        if (isNaN(d)) return val;
        return d.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function badgeEstatus(estatus) {
        if (estatus === 'en_proceso')
            return '<span class="badge badge-en-proceso rounded-pill px-3">En Proceso</span>';
        if (estatus === 'enviado_finalizado')
            return '<span class="badge badge-env-finalizado rounded-pill px-3">Enviado Finalizado</span>';
        return `<span class="badge bg-secondary">${estatus}</span>`;
    }

    /* ── Renderizar tabla genérica ── */
    function renderTabla(rows, bodyId, loaderId, wrapId, emptyId, badgeId) {
        document.getElementById(loaderId).classList.add('d-none');

        if (!rows || rows.length === 0) {
            document.getElementById(emptyId).classList.remove('d-none');
            document.getElementById(badgeId).textContent = '0';
            return;
        }

        document.getElementById(badgeId).textContent = rows.length;
        document.getElementById(wrapId).classList.remove('d-none');

        const tbody = document.getElementById(bodyId);
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

    /* ── Cargar En Proceso ── */
    function cargarEnProceso() {
        fetch('/CierreCredito/getEnProceso', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderTabla(res.datos, 'body-en-proceso', 'loader-en-proceso',
                            'wrap-tabla-en-proceso', 'empty-en-proceso', 'badge-en-proceso');
            })
            .catch(err => {
                document.getElementById('loader-en-proceso').innerHTML =
                    `<div class="alert alert-danger">Error al cargar: ${err.message}</div>`;
            });
    }

    /* ── Cargar Enviado Finalizado (lazy: solo al activar la pestaña) ── */
    let envFinalCargado = false;
    function cargarEnviadoFinalizado() {
        if (envFinalCargado) return;
        envFinalCargado = true;
        fetch('/CierreCredito/getEnviadoFinalizado', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderTabla(res.datos, 'body-env-finalizado', 'loader-env-finalizado',
                            'wrap-tabla-env-finalizado', 'empty-env-finalizado', 'badge-env-finalizado');
            })
            .catch(err => {
                document.getElementById('loader-env-finalizado').innerHTML =
                    `<div class="alert alert-danger">Error al cargar: ${err.message}</div>`;
            });
    }

    /* ── Evento cambio de pestaña ── */
    document.getElementById('tab-env-finalizado-btn')
        .addEventListener('shown.bs.tab', cargarEnviadoFinalizado);

    /* ── Carga inicial ── */
    cargarEnProceso();

})();
</script>
