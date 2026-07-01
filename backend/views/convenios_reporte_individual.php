<?php
$idConvenioInicial = isset($_GET['id_convenio']) ? (int) $_GET['id_convenio'] : 0;
$idCreditoInicial = isset($_GET['id_credito']) ? (int) $_GET['id_credito'] : 0;
?>

<style>
    .cvi-shell { color: #334155; }
    .cvi-panel,
    .cvi-search,
    .cvi-kpi {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .55rem;
        box-shadow: 0 .35rem 1rem rgba(15, 23, 42, .04);
    }
    .cvi-title { color: #5b3ea6; font-weight: 800; letter-spacing: 0; }
    .cvi-kpi small { color: #64748b; font-weight: 800; text-transform: uppercase; font-size: .68rem; }
    .cvi-kpi strong { color: #172033; font-size: 1.15rem; }
    .cvi-pill {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        padding: .24rem .55rem;
        font-size: .72rem;
        font-weight: 800;
    }
    .cvi-pill.activo { color: #0369a1; background: #e0f2fe; }
    .cvi-pill.completado,
    .cvi-pill.pagado { color: #15803d; background: #dcfce7; }
    .cvi-pill.cancelado,
    .cvi-pill.vencido { color: #b91c1c; background: #fee2e2; }
    .cvi-pill.parcial,
    .cvi-pill.pendiente { color: #9a5b00; background: #fef3c7; }
    .cvi-pill.pendiente_conciliar { color: #5b3ea6; background: #f4efff; }
    .cvi-pill.neutral { color: #475569; background: #e2e8f0; }
    .cvi-pill.reactivado { color: #8a4f00; background: #fff4cf; border: 1px solid #fedf89; }
    .cvi-info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .cvi-info {
        border: 1px solid #edf2f7;
        border-radius: .45rem;
        padding: .75rem;
        min-height: 76px;
        background: #fbfdff;
    }
    .cvi-info span { display: block; color: #64748b; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
    .cvi-info strong { display: block; color: #1f2937; margin-top: .15rem; overflow-wrap: anywhere; }
    .cvi-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 880px;
    }
    .cvi-table th {
        color: #3151c7;
        font-size: .72rem;
        text-transform: uppercase;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: .7rem .75rem;
        white-space: nowrap;
    }
    .cvi-table td {
        border-bottom: 1px solid #edf2f7;
        padding: .7rem .75rem;
        vertical-align: middle;
        font-size: .86rem;
    }
    .cvi-timeline {
        position: relative;
        padding-left: 1.25rem;
    }
    .cvi-timeline::before {
        content: "";
        position: absolute;
        left: .35rem;
        top: .25rem;
        bottom: .25rem;
        width: 2px;
        background: #e2e8f0;
    }
    .cvi-event {
        position: relative;
        padding: 0 0 1rem 1rem;
    }
    .cvi-event::before {
        content: "";
        position: absolute;
        left: -.02rem;
        top: .35rem;
        width: .7rem;
        height: .7rem;
        border-radius: 50%;
        background: #5b72e8;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dbe4ff;
    }
    .cvi-event h6 { margin: 0; font-weight: 800; color: #1f2937; }
    .cvi-event p { margin: .15rem 0 0; color: #64748b; }
    .cvi-empty {
        border: 1px dashed #cbd5e1;
        border-radius: .55rem;
        color: #64748b;
        background: #f8fafc;
    }
    @media (max-width: 992px) {
        .cvi-info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px) {
        .cvi-info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid py-3 cvi-shell">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="cvi-title mb-1"><i class="fa-solid fa-timeline me-2"></i>Reporte individual de convenio</h4>
            <div class="text-muted">Ficha completa, amortizacion y bitacora del convenio seleccionado.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="/convenios/reporteria">
                <i class="fa-solid fa-arrow-left me-1"></i>Reporteria
            </a>
            <a class="btn btn-outline-primary" href="/convenios/reporteHistorico">
                <i class="fa-solid fa-table-list me-1"></i>Historico
            </a>
        </div>
    </div>

    <form id="cviSearch" class="cvi-search p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold">ID convenio</label>
                <input type="number" min="1" class="form-control" name="id_convenio" value="<?= $idConvenioInicial > 0 ? htmlspecialchars((string) $idConvenioInicial, ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Ej. 694">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold">ID credito</label>
                <input type="number" min="1" class="form-control" name="id_credito" value="<?= $idCreditoInicial > 0 ? htmlspecialchars((string) $idCreditoInicial, ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Ej. 1940735">
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Consultar
                </button>
            </div>
        </div>
    </form>

    <div id="cviLoading" class="cvi-empty p-4 text-center d-none">
        <i class="fa-solid fa-circle-notch fa-spin me-1"></i>Cargando convenio...
    </div>

    <div id="cviMessage" class="cvi-empty p-4 text-center">
        <i class="fa-solid fa-circle-info me-1"></i>Ingresa un ID de convenio o ID de credito para consultar.
    </div>

    <div id="cviContent" class="d-none">
        <div class="cvi-panel p-3 mb-3">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                <div>
                    <h5 id="cviCliente" class="fw-bold mb-1">--</h5>
                    <div class="text-muted">
                        <span id="cviOferta">--</span>
                        <span id="cviReactivado"></span>
                    </div>
                </div>
                <div class="text-end">
                    <div id="cviStatus"></div>
                    <a id="cviPdf" class="btn btn-sm btn-outline-secondary mt-2 d-none" target="_blank" rel="noopener">
                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                    </a>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Monto original</small><br><strong id="kOriginal">$0.00</strong></div></div>
                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Descuento</small><br><strong id="kDescuento">$0.00</strong></div></div>
                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Convenio</small><br><strong id="kConvenio">$0.00</strong></div></div>
                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Pagado</small><br><strong id="kPagado">$0.00</strong></div></div>
                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Restante</small><br><strong id="kSaldo">$0.00</strong></div></div>
                <div class="col-6 col-xl-2"><div class="cvi-kpi p-3"><small>Semanas</small><br><strong id="kSemanas">0</strong></div></div>
            </div>

            <div id="cviInfoGrid" class="cvi-info-grid"></div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-7">
                <div class="cvi-panel p-0 overflow-hidden">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-table me-1 text-primary"></i>Amortizacion</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="cvi-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha pago</th>
                                    <th>Pago semanal</th>
                                    <th>Pago realizado</th>
                                    <th>Estatus</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody id="cviAmortizacion"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-5">
                <div class="cvi-panel p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>Bitacora</h6>
                    <div id="cviBitacora" class="cvi-timeline"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('cviSearch');
    const loading = document.getElementById('cviLoading');
    const message = document.getElementById('cviMessage');
    const content = document.getElementById('cviContent');

    const initialConvenio = <?= json_encode($idConvenioInicial); ?>;
    const initialCredito = <?= json_encode($idCreditoInicial); ?>;

    const money = (value) => new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2
    }).format(Number(value || 0));

    const fmtDate = (value) => {
        if (!value) return '--';
        const clean = String(value).slice(0, 10);
        const parts = clean.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : value;
    };

    const esc = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const numericMoney = (value) => {
        const raw = String(value ?? '').trim();
        if (raw === '' || Number.isNaN(Number(raw))) return 0;
        return Number(raw);
    };

    function statusBadge(value) {
        const v = String(value || '').toLowerCase();
        const labels = {
            activo: 'Activo',
            completado: 'Liquidado',
            cancelado: 'Cancelado',
            pagado: 'Pagado',
            parcial: 'Parcial',
            vencido: 'Vencido',
            pendiente: 'Pendiente',
            pendiente_conciliar: 'Pendiente conciliar'
        };
        const label = labels[v] || (v ? v.charAt(0).toUpperCase() + v.slice(1) : 'Sin estatus');
        const known = ['activo', 'completado', 'cancelado', 'pagado', 'parcial', 'vencido', 'pendiente', 'pendiente_conciliar'];
        const cls = known.includes(v) ? v : 'neutral';
        return `<span class="cvi-pill ${cls}">${esc(label)}</span>`;
    }

    function pagoTotal(row) {
        return numericMoney(row.monto_pagado) + Number(row.monto_secundario || 0);
    }

    function renderInfo(convenio) {
        const items = [
            ['ID convenio', convenio.id_convenio],
            ['ID credito', convenio.id_credito],
            ['Fecha convenio', fmtDate(convenio.fecha_acuerdo || convenio.fecha_alta)],
            ['Primer pago', fmtDate(convenio.fecha_primer_pago)],
            ['Ultimo pago', fmtDate(convenio.fecha_ultimo_pago)],
            ['Celula', convenio.celula],
            ['Usuario alta', convenio.usuario_alta],
            ['Calendario', convenio.tipo_calendario || convenio.frecuencia],
            ['Base calculo', convenio.base_calculo || '--'],
            ['Monto adicional', money(convenio.monto_adicional)],
            ['Pago semanal', money(convenio.pago_semanal)],
            ['Cancelamiento', convenio.fecha_cancelacion ? fmtDate(convenio.fecha_cancelacion) : 'Sin cancelacion'],
        ];
        document.getElementById('cviInfoGrid').innerHTML = items.map(([label, value]) => {
            return `<div class="cvi-info"><span>${esc(label)}</span><strong>${esc(value ?? '--')}</strong></div>`;
        }).join('');
    }

    function renderAmortizacion(rows) {
        const tbody = document.getElementById('cviAmortizacion');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Sin tabla de amortizacion registrada.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map((row) => {
            const comprobante = row.comprobante_path
                ? `<a class="btn btn-sm btn-outline-primary" href="${esc(row.comprobante_path)}" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip"></i></a>`
                : '<span class="text-muted">--</span>';
            return `<tr>
                <td class="fw-bold">${esc(row.numero_semana)}</td>
                <td>
                    <div>${fmtDate(row.fecha_pago)}</div>
                    <div class="text-muted small">${row.fecha_pago_real ? 'Real: ' + fmtDate(row.fecha_pago_real) : ''}</div>
                </td>
                <td>${money(row.pago_semanal)}</td>
                <td class="fw-bold">${money(pagoTotal(row))}</td>
                <td>${statusBadge(row.estatus_pago)}</td>
                <td>${comprobante}</td>
            </tr>`;
        }).join('');
    }

    function renderBitacora(rows) {
        const wrap = document.getElementById('cviBitacora');
        if (!rows.length) {
            wrap.innerHTML = '<div class="text-muted">Sin eventos registrados.</div>';
            return;
        }
        wrap.innerHTML = rows.map((event) => {
            const usuario = event.usuario ? `<div class="small text-muted">Usuario: ${esc(event.usuario)}</div>` : '';
            return `<div class="cvi-event">
                <div class="small text-muted mb-1">${fmtDate(event.fecha)} ${String(event.fecha || '').length > 10 ? esc(String(event.fecha).slice(11, 16)) : ''}</div>
                <h6>${esc(event.titulo)}</h6>
                <p>${esc(event.detalle || '')}</p>
                ${usuario}
            </div>`;
        }).join('');
    }

    function renderReporte(datos) {
        const convenio = datos.convenio || {};
        const amortizacion = datos.amortizacion || [];
        const pagado = amortizacion.reduce((sum, row) => sum + pagoTotal(row), 0);
        const totalConvenio = Number(convenio.total_a_pagar || 0);
        const saldo = Math.max(totalConvenio - pagado, 0);

        document.getElementById('cviCliente').textContent = convenio.nombre_cliente || 'Sin nombre';
        document.getElementById('cviOferta').textContent = convenio.oferta_seleccionada || 'Sin oferta';
        document.getElementById('cviStatus').innerHTML = statusBadge(convenio.estatus);
        document.getElementById('cviReactivado').innerHTML = Number(convenio.es_reactivado || 0) === 1
            ? '<span class="cvi-pill reactivado ms-2"><i class="fa-solid fa-rotate"></i>Reactivado</span>'
            : '';

        const pdf = document.getElementById('cviPdf');
        if (convenio.pdf_adjunto) {
            pdf.href = convenio.pdf_adjunto;
            pdf.classList.remove('d-none');
        } else {
            pdf.classList.add('d-none');
            pdf.removeAttribute('href');
        }

        document.getElementById('kOriginal').textContent = money(convenio.adeudo_total_original);
        document.getElementById('kDescuento').textContent = `${money(convenio.descuento_monto)} (${Number(convenio.porcentaje_descuento || 0).toFixed(2)}%)`;
        document.getElementById('kConvenio').textContent = money(totalConvenio);
        document.getElementById('kPagado').textContent = money(pagado);
        document.getElementById('kSaldo').textContent = money(saldo);
        document.getElementById('kSemanas').textContent = convenio.numero_semanas || amortizacion.length || 0;

        renderInfo(convenio);
        renderAmortizacion(amortizacion);
        renderBitacora(datos.bitacora || []);

        message.classList.add('d-none');
        content.classList.remove('d-none');
    }

    async function loadReporte() {
        const formData = new FormData(form);
        const idConvenio = Number(formData.get('id_convenio') || 0);
        const idCredito = Number(formData.get('id_credito') || 0);
        if (!idConvenio && !idCredito) {
            content.classList.add('d-none');
            message.classList.remove('d-none');
            message.innerHTML = '<i class="fa-solid fa-circle-info me-1"></i>Ingresa un ID de convenio o ID de credito para consultar.';
            return;
        }

        const params = new URLSearchParams();
        if (idConvenio) params.set('id_convenio', idConvenio);
        if (idCredito) params.set('id_credito', idCredito);

        loading.classList.remove('d-none');
        message.classList.add('d-none');
        try {
            const response = await fetch(`/convenios/reporteIndividualDatos?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.mensaje || 'No se pudo cargar el reporte.');
            }
            renderReporte(payload.datos || {});
        } catch (err) {
            content.classList.add('d-none');
            message.classList.remove('d-none');
            message.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(err.message)}`;
        } finally {
            loading.classList.add('d-none');
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadReporte();
    });

    if (initialConvenio > 0 || initialCredito > 0) {
        loadReporte();
    }
})();
</script>
