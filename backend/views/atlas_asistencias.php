<?php
$atlasNow = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
$atlasStart = $atlasNow->modify('first day of this month')->format('Y-m-d');
$atlasEnd = $atlasNow->modify('last day of this month')->format('Y-m-d');
$atlasApiReady = !empty($atlas_admin_configurada);
?>

<div class="container-fluid py-3 atlas-attendance-page">
    <style>
        .atlas-attendance-page { color: #22303e; }
        .atlas-attendance-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .atlas-attendance-title { display:flex; align-items:center; gap:.65rem; margin:0; color:#173756; font-size:1.35rem; font-weight:900; }
        .atlas-attendance-title i { color:#0f766e; }
        .atlas-attendance-subtitle { margin:.2rem 0 0; color:#64748b; font-size:.86rem; font-weight:700; }
        .atlas-attendance-actions { display:flex; align-items:center; gap:.55rem; flex-wrap:wrap; }
        .atlas-attendance-filter-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#f8fafc; padding:.9rem; margin-bottom:1rem; }
        .atlas-attendance-filters { display:grid; grid-template-columns:repeat(7, minmax(9rem, 1fr)); gap:.7rem; align-items:end; }
        .atlas-attendance-filter-actions { display:flex; align-items:center; gap:.45rem; }
        .atlas-attendance-filter-actions .btn { min-height:2.35rem; }
        .atlas-attendance-metrics { display:grid; grid-template-columns:repeat(6, minmax(8rem, 1fr)); gap:.7rem; margin-bottom:1rem; }
        .atlas-attendance-metric { border:1px solid #dbe4ef; border-left:4px solid #64748b; border-radius:.45rem; background:#fff; padding:.72rem .8rem; min-width:0; }
        .atlas-attendance-metric.is-green { border-left-color:#16a34a; }
        .atlas-attendance-metric.is-red { border-left-color:#dc2626; }
        .atlas-attendance-metric.is-amber { border-left-color:#d97706; }
        .atlas-attendance-metric.is-blue { border-left-color:#2563eb; }
        .atlas-attendance-metric-label { color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; }
        .atlas-attendance-metric-value { color:#172033; font-size:1.25rem; font-weight:900; line-height:1.1; margin-top:.2rem; }
        .atlas-attendance-table-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#fff; overflow:hidden; }
        .atlas-attendance-table-head { display:flex; align-items:center; justify-content:space-between; gap:.8rem; padding:.75rem .9rem; border-bottom:1px solid #e5e7eb; }
        .atlas-attendance-table-title { margin:0; color:#173756; font-size:.9rem; font-weight:900; }
        .atlas-attendance-table-meta { color:#64748b; font-size:.75rem; font-weight:800; }
        .atlas-attendance-scroll { max-height:62vh; overflow:auto; }
        .atlas-attendance-table { min-width:1280px; margin:0; }
        .atlas-attendance-table th { position:sticky; top:0; z-index:2; background:#f8fafc; color:#566a7f; font-size:.68rem; font-weight:900; text-transform:uppercase; white-space:nowrap; }
        .atlas-attendance-table td { color:#566a7f; font-size:.76rem; font-weight:700; vertical-align:middle; }
        .atlas-attendance-main { color:#22303e; font-weight:900; line-height:1.18; }
        .atlas-attendance-sub { color:#94a3b8; font-size:.68rem; font-weight:800; line-height:1.18; margin-top:.12rem; }
        .atlas-attendance-badge { display:inline-flex; align-items:center; border-radius:999px; padding:.2rem .55rem; font-size:.68rem; font-weight:900; white-space:nowrap; }
        .atlas-attendance-status-cumplida { background:#dcfce7; color:#15803d; }
        .atlas-attendance-status-no-realizada { background:#fee2e2; color:#b91c1c; }
        .atlas-attendance-status-fuera { background:#ffedd5; color:#c2410c; }
        .atlas-attendance-status-en-visita { background:#dbeafe; color:#1d4ed8; }
        .atlas-attendance-status-programada { background:#f1f5f9; color:#475569; }
        .atlas-attendance-empty { padding:2.5rem 1rem !important; text-align:center; color:#64748b !important; }
        .atlas-attendance-note { color:#64748b; font-size:.73rem; font-weight:700; margin-top:.55rem; }
        @media (max-width: 1399.98px) {
            .atlas-attendance-filters { grid-template-columns:repeat(3, minmax(10rem, 1fr)); }
            .atlas-attendance-metrics { grid-template-columns:repeat(3, minmax(9rem, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .atlas-attendance-filters,
            .atlas-attendance-metrics { grid-template-columns:1fr; }
            .atlas-attendance-actions,
            .atlas-attendance-actions .btn,
            .atlas-attendance-filter-actions,
            .atlas-attendance-filter-actions .btn { width:100%; }
            .atlas-attendance-filter-actions .btn { flex:1; }
        }
    </style>

    <div class="atlas-attendance-head">
        <div>
            <h1 class="atlas-attendance-title">
                <i class="fa-solid fa-clipboard-user"></i>
                <span>Asistencias Atlas</span>
            </h1>
            <p class="atlas-attendance-subtitle">Visitas, ubicación, permanencia y actividad registrada desde la app.</p>
        </div>
        <div class="atlas-attendance-actions">
            <button type="button" class="btn btn-label-secondary" id="atlasAttendanceRefresh" title="Actualizar reporte">
                <i class="fa-solid fa-rotate me-2"></i>Actualizar
            </button>
            <button type="button" class="btn btn-success" id="atlasAttendanceDownload" title="Descargar archivo Excel">
                <i class="fa-solid fa-file-excel me-2"></i>Descargar Excel
            </button>
        </div>
    </div>

    <?php if (!$atlasApiReady): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Configura <strong>ATLAS_ADMIN_API_KEYS</strong> para consultar el reporte.</span>
        </div>
    <?php endif; ?>

    <section class="atlas-attendance-filter-panel" aria-label="Filtros de asistencias">
        <div class="atlas-attendance-filters">
            <div>
                <label class="form-label mb-1" for="atlasAttendanceStart">Fecha inicio</label>
                <input class="form-control form-control-sm" type="date" id="atlasAttendanceStart" value="<?= htmlspecialchars($atlasStart, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceEnd">Fecha fin</label>
                <input class="form-control form-control-sm" type="date" id="atlasAttendanceEnd" value="<?= htmlspecialchars($atlasEnd, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceCollaborator">Colaborador</label>
                <select class="form-select form-select-sm" id="atlasAttendanceCollaborator">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceDistributor">Distribuidor</label>
                <select class="form-select form-select-sm" id="atlasAttendanceDistributor">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceStatus">Estatus</label>
                <select class="form-select form-select-sm" id="atlasAttendanceStatus">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceDivisional">Divisional</label>
                <select class="form-select form-select-sm" id="atlasAttendanceDivisional">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="atlas-attendance-filter-actions">
                <button type="button" class="btn btn-primary btn-sm" id="atlasAttendanceApply" title="Aplicar filtros">
                    <i class="fa-solid fa-filter me-2"></i>Consultar
                </button>
                <button type="button" class="btn btn-label-secondary btn-sm" id="atlasAttendanceClear" title="Limpiar filtros">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                </button>
            </div>
        </div>
        <div class="atlas-attendance-note" id="atlasAttendancePerimeter">Perímetro operativo pendiente de consultar.</div>
    </section>

    <section class="atlas-attendance-metrics" aria-label="Resumen de asistencias">
        <article class="atlas-attendance-metric is-blue">
            <div class="atlas-attendance-metric-label">Visitas</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceTotal">0</div>
        </article>
        <article class="atlas-attendance-metric is-green">
            <div class="atlas-attendance-metric-label">Cumplidas</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceCompleted">0</div>
        </article>
        <article class="atlas-attendance-metric is-red">
            <div class="atlas-attendance-metric-label">No realizadas</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceMissed">0</div>
        </article>
        <article class="atlas-attendance-metric is-amber">
            <div class="atlas-attendance-metric-label">Fuera de ubicación</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceOutside">0</div>
        </article>
        <article class="atlas-attendance-metric is-blue">
            <div class="atlas-attendance-metric-label">Gestiones</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceManaged">0</div>
        </article>
        <article class="atlas-attendance-metric">
            <div class="atlas-attendance-metric-label">Pendientes</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendancePending">0</div>
        </article>
    </section>

    <section class="atlas-attendance-table-panel">
        <div class="atlas-attendance-table-head">
            <h2 class="atlas-attendance-table-title">Detalle de visitas</h2>
            <span class="atlas-attendance-table-meta" id="atlasAttendanceGenerated">Sin consultar</span>
        </div>
        <div class="atlas-attendance-scroll">
            <table class="table table-hover atlas-attendance-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Colaborador</th>
                        <th>Agencia / Distribuidor</th>
                        <th>Estatus</th>
                        <th>Perímetro</th>
                        <th>Llegada</th>
                        <th>Salida</th>
                        <th>Permanencia</th>
                        <th class="text-end">Gestiones</th>
                        <th class="text-end">Pendientes</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody id="atlasAttendanceBody">
                    <tr><td class="atlas-attendance-empty" colspan="11">Consultando asistencias...</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
(() => {
    const apiReady = <?= $atlasApiReady ? 'true' : 'false' ?>;
    const startInput = document.getElementById('atlasAttendanceStart');
    const endInput = document.getElementById('atlasAttendanceEnd');
    const collaboratorSelect = document.getElementById('atlasAttendanceCollaborator');
    const distributorSelect = document.getElementById('atlasAttendanceDistributor');
    const statusSelect = document.getElementById('atlasAttendanceStatus');
    const divisionalSelect = document.getElementById('atlasAttendanceDivisional');
    const body = document.getElementById('atlasAttendanceBody');
    const downloadButton = document.getElementById('atlasAttendanceDownload');
    let loading = false;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const number = (value) => Number(value || 0).toLocaleString('es-MX');

    const query = () => {
        const params = new URLSearchParams();
        if (startInput.value) params.set('fecha_inicio', startInput.value);
        if (endInput.value) params.set('fecha_fin', endInput.value);
        if (collaboratorSelect.value) params.set('gestor_persona_id', collaboratorSelect.value);
        if (distributorSelect.value) params.set('distribuidor_id', distributorSelect.value);
        if (statusSelect.value) params.set('estatus', statusSelect.value);
        if (divisionalSelect.value) params.set('divisional', divisionalSelect.value);
        return params;
    };

    const updateSelect = (select, items, valueKey, labelKey) => {
        const selected = select.value;
        select.innerHTML = '<option value="">Todos</option>' + items.map((item) => {
            const value = typeof item === 'object' ? item[valueKey] : item;
            const label = typeof item === 'object' ? item[labelKey] : item;
            return `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`;
        }).join('');
        if ([...select.options].some((option) => option.value === selected)) {
            select.value = selected;
        }
    };

    const statusClass = (status) => {
        const normalized = String(status || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        if (normalized === 'cumplida') return 'atlas-attendance-status-cumplida';
        if (normalized === 'no realizada') return 'atlas-attendance-status-no-realizada';
        if (normalized === 'fuera de ubicacion') return 'atlas-attendance-status-fuera';
        if (normalized === 'en visita') return 'atlas-attendance-status-en-visita';
        return 'atlas-attendance-status-programada';
    };

    const renderRows = (rows) => {
        if (!rows.length) {
            body.innerHTML = '<tr><td class="atlas-attendance-empty" colspan="11">No hay visitas para los filtros seleccionados.</td></tr>';
            return;
        }
        body.innerHTML = rows.map((row) => {
            const distance = row.distancia_metros === null || row.distancia_metros === undefined
                ? 'Sin distancia'
                : `${number(row.distancia_metros)} m`;
            const observations = row.observaciones_incumplimiento || row.observaciones || row.evidencia || '';
            return `<tr>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.fecha || '')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml(row.dia_visita || '')}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.colaborador || 'Sin asignar')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml([row.puesto || row.rol, row.divisional].filter(Boolean).join(' · '))}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.agencia || 'Sin agencia')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml(row.distribuidor || 'Sin distribuidor')}</div>
                </td>
                <td><span class="atlas-attendance-badge ${statusClass(row.estatus_visita)}">${escapeHtml(row.estatus_visita || '')}</span></td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.dentro_perimetro || 'Sin dato')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml(distance)}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.hora_llegada || '--:--')}</div>
                    <div class="atlas-attendance-sub">Conf. ${escapeHtml(row.hora_confirmacion_llegada || '--:--')}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.hora_salida || '--:--')}</div>
                    <div class="atlas-attendance-sub">Fin ${escapeHtml(row.hora_termino_visita || '--:--')}</div>
                </td>
                <td>${escapeHtml(row.tiempo_permanencia || 'Sin dato')}</td>
                <td class="text-end"><strong>${number(row.gestiones_realizadas)}</strong></td>
                <td class="text-end"><strong>${row.pendientes_por_gestionar === null ? '-' : number(row.pendientes_por_gestionar)}</strong></td>
                <td style="max-width:22rem; white-space:normal;">${escapeHtml(observations || 'Sin observaciones')}</td>
            </tr>`;
        }).join('');
    };

    const setSummary = (summary) => {
        document.getElementById('atlasAttendanceTotal').textContent = number(summary.total_visitas);
        document.getElementById('atlasAttendanceCompleted').textContent = number(summary.cumplidas);
        document.getElementById('atlasAttendanceMissed').textContent = number(summary.no_realizadas);
        document.getElementById('atlasAttendanceOutside').textContent = number(summary.fuera_ubicacion);
        document.getElementById('atlasAttendanceManaged').textContent = number(summary.gestiones_realizadas);
        document.getElementById('atlasAttendancePending').textContent = number(summary.pendientes_por_gestionar);
    };

    const reportError = (message) => {
        body.innerHTML = `<tr><td class="atlas-attendance-empty text-danger" colspan="11">${escapeHtml(message)}</td></tr>`;
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'No se pudo cargar el reporte', text: message });
        }
    };

    const loadReport = async () => {
        if (loading || !apiReady) return;
        if (!startInput.value || !endInput.value || endInput.value < startInput.value) {
            reportError('Selecciona un rango de fechas válido.');
            return;
        }
        loading = true;
        downloadButton.disabled = true;
        body.innerHTML = '<tr><td class="atlas-attendance-empty" colspan="11">Consultando asistencias...</td></tr>';
        try {
            const response = await fetch(`/Atlas/getReporteAsistencias?${query().toString()}`, {
                headers: { Accept: 'application/json' }
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.mensaje || 'API-COMERCIAL devolvió un error.');
            }
            const data = payload.datos || {};
            const rows = Array.isArray(data.filas) ? data.filas : [];
            const catalogs = data.catalogos || {};
            updateSelect(collaboratorSelect, catalogs.colaboradores || [], 'persona_id', 'nombre');
            updateSelect(distributorSelect, catalogs.distribuidores || [], 'id', 'nombre');
            updateSelect(statusSelect, catalogs.estatuses || [], '', '');
            updateSelect(divisionalSelect, catalogs.divisionales || [], '', '');
            renderRows(rows);
            setSummary(data.resumen || {});
            document.getElementById('atlasAttendancePerimeter').textContent =
                `Perímetro permitido: ${number(data.perimetro_metros)} m. ` +
                (data.pendientes_disponibles === false ? 'La fuente de pendientes no estuvo disponible en esta consulta.' : '');
            document.getElementById('atlasAttendanceGenerated').textContent =
                `${number(rows.length)} registros · Generado ${String(data.generado_at || '').replace('T', ' ')}`;
            downloadButton.disabled = false;
        } catch (error) {
            reportError(error.message || 'No se pudo consultar el reporte.');
        } finally {
            loading = false;
        }
    };

    document.getElementById('atlasAttendanceApply').addEventListener('click', loadReport);
    document.getElementById('atlasAttendanceRefresh').addEventListener('click', loadReport);
    document.getElementById('atlasAttendanceDownload').addEventListener('click', () => {
        window.location.href = `/Atlas/descargarReporteAsistencias?${query().toString()}`;
    });
    document.getElementById('atlasAttendanceClear').addEventListener('click', () => {
        collaboratorSelect.value = '';
        distributorSelect.value = '';
        statusSelect.value = '';
        divisionalSelect.value = '';
        loadReport();
    });

    if (apiReady) {
        loadReport();
    } else {
        body.innerHTML = '<tr><td class="atlas-attendance-empty" colspan="11">API administrativa no configurada.</td></tr>';
        downloadButton.disabled = true;
    }
})();
</script>
