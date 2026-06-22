<div class="container-xxl flex-grow-1 container-p-y atlas-risk-page">
    <style>
        .atlas-risk-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .atlas-risk-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; }
        .atlas-risk-kpis { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .atlas-risk-kpi { border:1px solid #e5e7eb; border-radius:.65rem; background:#fff; padding:.9rem 1rem; min-height:5.2rem; }
        .atlas-risk-kpi-label { color:#64748b; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.02em; }
        .atlas-risk-kpi-value { color:#22303e; font-size:1.15rem; font-weight:900; margin-top:.15rem; }
        .atlas-risk-kpi-danger { border-color:#fecaca; background:#fff7f7; }
        .atlas-risk-kpi-danger .atlas-risk-kpi-value { color:#dc2626; }
        .atlas-risk-main { color:#22303e; font-weight:900; line-height:1.16; }
        .atlas-risk-sub { color:#64748b; font-size:.76rem; font-weight:700; line-height:1.24; margin-top:.16rem; }
        .atlas-risk-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.24rem .62rem; font-size:.72rem; font-weight:900; white-space:nowrap; }
        .atlas-risk-badge-danger { background:#fee2e2; color:#b91c1c; }
        .atlas-risk-badge-ok { background:#dcfce7; color:#15803d; }
        .atlas-risk-badge-muted { background:#f1f5f9; color:#64748b; }
        .atlas-risk-empty { text-align:center; color:#94a3b8; font-weight:800; padding:2rem !important; }
        @media (max-width: 991.98px) { .atlas-risk-kpis { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 575.98px) { .atlas-risk-kpis { grid-template-columns:1fr; } }
    </style>

    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h1 class="atlas-risk-title"><i class="fa-solid fa-triangle-exclamation"></i><span>Riesgos Operativos</span></h1>
            <p class="atlas-risk-subtitle">Agencias o distribuidores detenidos en Sparta que todavía registran venta reciente en Maxi.</p>
        </div>
        <button type="button" class="btn btn-primary btn-action-size" id="atlasRiskRefresh">
            <i class="fa-solid fa-rotate"></i><span>Actualizar</span>
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="atlas-risk-kpis">
                <div class="atlas-risk-kpi">
                    <div class="atlas-risk-kpi-label">Distribuidores detenidos</div>
                    <div class="atlas-risk-kpi-value" id="atlasRiskDistribuidores">0</div>
                </div>
                <div class="atlas-risk-kpi">
                    <div class="atlas-risk-kpi-label">Sucursales detenidas</div>
                    <div class="atlas-risk-kpi-value" id="atlasRiskSucursales">0</div>
                </div>
                <div class="atlas-risk-kpi atlas-risk-kpi-danger">
                    <div class="atlas-risk-kpi-label">Con venta en Maxi</div>
                    <div class="atlas-risk-kpi-value" id="atlasRiskConVenta">0</div>
                </div>
                <div class="atlas-risk-kpi">
                    <div class="atlas-risk-kpi-label">Monto detectado 90 dias</div>
                    <div class="atlas-risk-kpi-value" id="atlasRiskMonto">$0.00</div>
                </div>
            </div>

            <div class="alert alert-warning d-none" id="atlasRiskMaxiWarning"></div>

            <div class="card-datatable table-responsive">
                <table class="table table-hover border-top" id="atlasRiskTable">
                    <thead>
                        <tr>
                            <th>Distribuidor</th>
                            <th>Sucursal</th>
                            <th>Motivo</th>
                            <th>Venta Maxi</th>
                            <th>Riesgo</th>
                        </tr>
                    </thead>
                    <tbody id="atlasRiskTableBody">
                        <tr><td colspan="5" class="atlas-risk-empty">Cargando riesgos operativos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const tbody = document.getElementById('atlasRiskTableBody');
    const warning = document.getElementById('atlasRiskMaxiWarning');
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    };
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[ch]));
    const renderRows = (rows) => {
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="atlas-risk-empty">No hay distribuidores o agencias detenidas con riesgo operativo.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map((row) => {
            const riesgo = Number(row.tiene_venta_maxi || 0) === 1;
            return `
                <tr>
                    <td>
                        <div class="atlas-risk-main">${escapeHtml(row.distribuidor)}</div>
                        <div class="atlas-risk-sub">${escapeHtml(row.estatus_distribuidor)}</div>
                    </td>
                    <td>
                        <div class="atlas-risk-main">${row.fk_sucursal ? 'FK ' + escapeHtml(row.fk_sucursal) + ' · ' : ''}${escapeHtml(row.sucursal)}</div>
                        <div class="atlas-risk-sub">${escapeHtml(row.direccion)}</div>
                    </td>
                    <td>
                        <div class="atlas-risk-main">${escapeHtml(row.motivo_bloqueo)}</div>
                        <div class="atlas-risk-sub">${row.bloqueo_fin_at_fmt ? 'Fin: ' + escapeHtml(row.bloqueo_fin_at_fmt) : (row.fecha_baja_fmt ? 'Baja: ' + escapeHtml(row.fecha_baja_fmt) : '')}</div>
                    </td>
                    <td>
                        <div class="atlas-risk-main">${escapeHtml(row.ventas_30d)} venta(s) 30 dias</div>
                        <div class="atlas-risk-sub">${escapeHtml(row.ventas_90d)} en 90 dias · ${escapeHtml(row.monto_90d_fmt)}</div>
                        <div class="atlas-risk-sub">${row.ultima_venta_fmt ? 'Ultima: ' + escapeHtml(row.ultima_venta_fmt) : 'Sin venta reciente'}</div>
                    </td>
                    <td>
                        <span class="atlas-risk-badge ${riesgo ? 'atlas-risk-badge-danger' : 'atlas-risk-badge-ok'}">
                            <i class="fa-solid ${riesgo ? 'fa-triangle-exclamation' : 'fa-circle-check'}"></i>${riesgo ? 'Riesgo alto' : 'Controlado'}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    };
    const cargar = () => {
        tbody.innerHTML = '<tr><td colspan="5" class="atlas-risk-empty">Cargando riesgos operativos...</td></tr>';
        const onSuccess = (resp) => {
            const data = resp || {};
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="5" class="atlas-risk-empty">${escapeHtml(data.mensaje || 'No se pudieron cargar los riesgos.')}</td></tr>`;
                return;
            }
            const tot = data.totales || {};
            setText('atlasRiskDistribuidores', tot.distribuidores_detenidos || 0);
            setText('atlasRiskSucursales', tot.sucursales_detenidas || 0);
            setText('atlasRiskConVenta', tot.sucursales_con_venta_maxi || 0);
            setText('atlasRiskMonto', tot.monto_90d_fmt || '$0.00');
            if (Number(tot.maxi_disponible || 0) !== 1) {
                warning.classList.remove('d-none');
                warning.textContent = tot.maxi_mensaje || 'Maxi no disponible.';
            } else {
                warning.classList.add('d-none');
                warning.textContent = '';
            }
            renderRows(Array.isArray(data.datos) ? data.datos : []);
        };
        if (window.http && typeof window.http.request === 'function') {
            window.http.request({
                endpoint: '/Atlas/getRiesgosOperativos',
                method: 'GET',
                showLoader: true,
                onSuccess
            });
            return;
        }
        fetch('/Atlas/getRiesgosOperativos')
            .then((r) => r.json())
            .then(onSuccess)
            .catch((err) => {
                tbody.innerHTML = `<tr><td colspan="5" class="atlas-risk-empty">${escapeHtml(err.message || 'Error de red.')}</td></tr>`;
            });
    };
    document.getElementById('atlasRiskRefresh')?.addEventListener('click', cargar);
    document.addEventListener('DOMContentLoaded', cargar);
})();
</script>
