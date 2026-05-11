<?php
/** @var array $limite_repuve */
if (!isset($limite_repuve) || !is_array($limite_repuve)) {
    $limite_repuve = ['max' => 5, 'usado_hoy' => 0, 'restantes' => 5];
}
$lrMax = (int) ($limite_repuve['max'] ?? 5);
$lrUso = (int) ($limite_repuve['usado_hoy'] ?? 0);
$lrRes = (int) ($limite_repuve['restantes'] ?? max(0, $lrMax - $lrUso));
?>

<style>
    :root {
        --rep-color: #f59e0b;
        --rep-dark: #d97706;
        --rep-light: #fffbeb;
        --rep-border: #fcd34d;
        --rep-text: #92400e;
    }
    .rep-wrap { max-width: 920px; margin: 0 auto; }
    .rep-hero {
        background: linear-gradient(135deg, var(--rep-light) 0%, #fff 50%);
        border: 1px solid var(--rep-border);
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
    }
    .rep-hero h4 { color: var(--rep-text); margin: 0 0 0.35rem; font-weight: 700; }
    .rep-limite-pill {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.8rem; font-weight: 600;
        background: #fff; border: 1px solid var(--rep-border);
        border-radius: 999px; padding: 0.25rem 0.75rem; color: var(--rep-text);
    }
    .rep-card {
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .rep-card-hdr {
        background: var(--rep-light);
        border-bottom: 1px solid var(--rep-border);
        padding: 0.65rem 1rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--rep-text);
    }
    .rep-card-body { padding: 1rem 1.25rem; background: #fff; }
    .rep-dl { display: grid; grid-template-columns: 140px 1fr; gap: 0.35rem 1rem; font-size: 0.9rem; margin: 0; }
    .rep-dl dt { color: #64748b; font-weight: 600; margin: 0; }
    .rep-dl dd { margin: 0; word-break: break-word; }
    .rep-result-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
    }
    .rep-kv {
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 0.65rem 0.85rem;
        border: 1px solid #e2e8f0;
    }
    .rep-kv label {
        display: block;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.15rem;
        font-weight: 700;
    }
    .rep-kv span { font-weight: 600; color: #0f172a; font-size: 0.95rem; }
    .rep-note {
        border: 1px dashed var(--rep-border);
        background: var(--rep-light);
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        color: var(--rep-text);
    }
    @keyframes repFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .rep-animate { animation: repFadeIn 0.35s ease-out both; }
    /* Modal de carga: mismo estilo que referencia (caja blanca, título gris pizarra, subtítulo gris, anillo morado claro) */
    .rep-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .rep-loading-overlay.rep-loading-on { display: flex; }
    .rep-loading-box {
        background: #ffffff;
        border-radius: 10px;
        padding: 2rem 2.5rem 2rem;
        min-width: min(22rem, 100%);
        max-width: 26rem;
        width: 100%;
        text-align: center;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow:
            0 4px 6px -1px rgba(15, 23, 42, 0.06),
            0 10px 28px -4px rgba(15, 23, 42, 0.12),
            0 24px 48px -12px rgba(15, 23, 42, 0.14);
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .rep-loading-title {
        margin: 0 0 0.5rem;
        font-size: 1.625rem;
        font-weight: 600;
        color: #4a5568;
        letter-spacing: -0.025em;
        line-height: 1.3;
    }
    .rep-loading-sub {
        margin: 0 0 1.75rem;
        font-size: 0.9375rem;
        font-weight: 400;
        color: #718096;
        line-height: 1.45;
    }
    .rep-loading-spinner-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 0.125rem;
    }
    /* Anillo fino lavanda / periwinkle con tramo activo (referencia) */
    .rep-loading-spinner {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        border: 3px solid #ede9fe;
        border-top-color: #a5b4fc;
        border-right-color: #c4b5fd;
        animation: rep-loading-spin 0.85s linear infinite;
    }
    @keyframes rep-loading-spin {
        to { transform: rotate(360deg); }
    }
    @keyframes rep-consultar-pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.42);
            border-color: #dc2626 !important;
            color: #b91c1c !important;
        }
        50% {
            box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
            border-color: #ef4444 !important;
            color: #dc2626 !important;
        }
    }
    #rep-btn-consultar.rep-btn-consultar-fallo {
        animation: rep-consultar-pulse 1.15s ease-in-out infinite;
        background: #fff !important;
        border: 2px solid #dc2626 !important;
        color: #b91c1c !important;
    }
    .tooltip.rep-repuve-tooltip-wide .tooltip-inner {
        max-width: min(380px, 92vw);
        text-align: left;
        font-size: 0.8125rem;
        white-space: pre-line;
    }
</style>

<div class="rep-wrap">
    <div class="rep-hero rep-animate">
        <h4><i class="fa-solid fa-id-card-clip me-2" style="color:var(--rep-color)"></i>Consulta REPUVE</h4>
        <p class="text-muted small mb-2 mb-md-3">
            Valida el crédito adjudicado y consulta en REPUVE por <strong>placa</strong> o <strong>VIN</strong> (número de serie, motocicleta).
            <strong>Una sola consulta REPUVE por crédito</strong> (costo del servicio); si sigue en proceso, puedes pulsar de nuevo solo para consultar el estatus, sin nuevo cargo.
            Los datos del vehículo se guardan en la operación y en <strong>Mis adjudicaciones</strong> → <strong>Registrar evidencias</strong> se muestran solos cuando ya existan.
        </p>
        <span class="rep-limite-pill" id="rep-limite-pill" title="Consultas nuevas a REPUVE por día (zona CDMX)">
            <i class="fa-solid fa-gauge-high"></i>
            Consultas hoy: <span id="rep-limite-uso"><?php echo (int) $lrUso; ?></span> / <?php echo (int) $lrMax; ?>
            <span class="text-muted">·</span>
            Restantes: <span id="rep-limite-rest"><?php echo (int) $lrRes; ?></span>
        </span>
    </div>

    <div class="rep-card">
        <div class="rep-card-hdr"><i class="fa-solid fa-magnifying-glass me-1"></i> 1. Crédito</div>
        <div class="rep-card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">ID crédito</label>
                    <input type="number" class="form-control" id="rep-id-credito" placeholder="Ej. 1637" min="1" />
                </div>
                <div class="col-md-auto">
                    <button type="button" class="btn btn-outline-secondary" id="rep-btn-validar">
                        <i class="fa-solid fa-check me-1"></i>Validar
                    </button>
                </div>
            </div>
            <div id="rep-credito-panel" class="mt-3" style="display:none;">
                <dl class="rep-dl">
                    <dt>Cliente</dt><dd id="rep-nombre-cliente">—</dd>
                    <dt title="Monto según Segundómetro (S2) para este crédito; no es un cargo de REPUVE.">Saldo total vencido</dt><dd id="rep-saldo">—</dd>
                    <dt>Mora (días)</dt><dd id="rep-mora">—</dd>
                    <dt>Gestor</dt><dd id="rep-gestor">—</dd>
                </dl>
            </div>
            <div id="rep-credito-err" class="alert alert-warning py-2 px-3 small mt-2" style="display:none;"></div>
        </div>
    </div>

    <div class="rep-card" id="rep-step-criterio" style="opacity:0.5; pointer-events:none;">
        <div class="rep-card-hdr"><i class="fa-solid fa-motorcycle me-1"></i> 2. Placa o VIN (REPUVE)</div>
        <div class="rep-card-body">
            <div class="row g-2 mb-2">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Buscar por</label>
                    <select class="form-select" id="rep-tipo">
                        <option value="plate">Placa</option>
                        <option value="vin">VIN (número de serie)</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label small text-muted mb-1" id="rep-valor-label">Placa</label>
                    <input type="text" class="form-control text-uppercase" id="rep-valor" placeholder="Ej. Y001AA (motocicleta)" maxlength="17" autocomplete="off" />
                </div>
            </div>
            <button type="button" class="btn text-white" id="rep-btn-consultar" style="background:linear-gradient(90deg,var(--rep-dark),var(--rep-color));border:none;">
                <i class="fa-solid fa-cloud-arrow-down me-1"></i>Consultar REPUVE
            </button>
            <div id="rep-consulta-msg" class="mt-3"></div>
        </div>
    </div>

    <div class="rep-card" id="rep-result-wrap" style="display:none;">
        <div class="rep-card-hdr"><i class="fa-solid fa-road me-1"></i> Resultado — datos del vehículo</div>
        <div class="rep-card-body">
            <div class="rep-result-grid mb-3" id="rep-result-grid"></div>
            <div class="rep-note mb-0">
                <i class="fa-solid fa-database me-1"></i>
                Los datos del vehículo se <strong>guardan solos en la base de datos</strong> al obtener respuesta de REPUVE. En <strong>Mis adjudicaciones</strong> → <strong>Registrar evidencias</strong>, «Datos de la motocicleta» ya vendrá lleno.
            </div>
        </div>
    </div>
</div>

<div id="rep-loading-overlay" class="rep-loading-overlay" role="status" aria-live="polite" aria-hidden="true">
    <div class="rep-loading-box">
        <h3 class="rep-loading-title" id="rep-loading-msg">Consultando información…</h3>
        <p class="rep-loading-sub" id="rep-loading-sub">Por favor espere</p>
        <div class="rep-loading-spinner-wrap">
            <div class="rep-loading-spinner" role="presentation" aria-hidden="true"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const $id = document.getElementById('rep-id-credito');
    const $validar = document.getElementById('rep-btn-validar');
    const $panel = document.getElementById('rep-credito-panel');
    const $err = document.getElementById('rep-credito-err');
    const $step2 = document.getElementById('rep-step-criterio');
    const $consultar = document.getElementById('rep-btn-consultar');
    const $tipo = document.getElementById('rep-tipo');
    const $valor = document.getElementById('rep-valor');
    const $valorLabel = document.getElementById('rep-valor-label');
    const $msg = document.getElementById('rep-consulta-msg');
    const $resWrap = document.getElementById('rep-result-wrap');
    const $resGrid = document.getElementById('rep-result-grid');
    const $limU = document.getElementById('rep-limite-uso');
    const $limR = document.getElementById('rep-limite-rest');

    let idCreditoOk = 0;

    const $overlay = document.getElementById('rep-loading-overlay');
    const $loadMsg = document.getElementById('rep-loading-msg');
    const $loadSub = document.getElementById('rep-loading-sub');

    function repShowLoading(title, sub) {
        if ($loadMsg) $loadMsg.textContent = title || 'Consultando información…';
        if ($loadSub) $loadSub.textContent = sub !== undefined && sub !== null ? sub : 'Por favor espere';
        if ($overlay) {
            $overlay.classList.add('rep-loading-on');
            $overlay.setAttribute('aria-hidden', 'false');
        }
    }

    function repHideLoading() {
        if ($overlay) {
            $overlay.classList.remove('rep-loading-on');
            $overlay.setAttribute('aria-hidden', 'true');
        }
    }

    function repSyncTipoUi() {
        if (!$tipo || !$valor) return;
        const t = ($tipo.value || 'plate').trim();
        if (t === 'vin') {
            if ($valorLabel) $valorLabel.textContent = 'VIN (número de serie)';
            $valor.placeholder = 'Ej. 17 caracteres sin I, O, Q';
            $valor.setAttribute('maxlength', '17');
        } else {
            if ($valorLabel) $valorLabel.textContent = 'Placa';
            $valor.placeholder = 'Ej. Y001AA (motocicleta)';
            $valor.setAttribute('maxlength', '16');
        }
    }

    if ($tipo) {
        $tipo.addEventListener('change', repSyncTipoUi);
        repSyncTipoUi();
    }

    function setLimite(l) {
        if (!l || typeof l !== 'object') return;
        if ($limU) $limU.textContent = l.usado_hoy != null ? l.usado_hoy : '0';
        if ($limR) $limR.textContent = l.restantes != null ? l.restantes : '0';
    }

    function fmtMoney(n) {
        if (n == null || n === '') return '—';
        try {
            return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n));
        } catch (e) { return String(n); }
    }

    function activarPaso2(on) {
        if (!$step2) return;
        $step2.style.opacity = on ? '1' : '0.5';
        $step2.style.pointerEvents = on ? 'auto' : 'none';
    }

    $validar.addEventListener('click', async function () {
        const v = parseInt(String($id.value || '0'), 10);
        $err.style.display = 'none';
        $panel.style.display = 'none';
        idCreditoOk = 0;
        activarPaso2(false);
        $resWrap.style.display = 'none';
        if (!v || v <= 0) {
            $err.textContent = 'Indica un ID de crédito válido.';
            $err.style.display = 'block';
            return;
        }
        $validar.disabled = true;
        repShowLoading('Consultando información…', 'Validando crédito y datos de cartera');
        try {
            const r = await fetch('/MotosAdjudicadas/buscarCredito', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ valor: v })
            });
            const j = await r.json();
            if (!j.success) {
                repHideLoading();
                $err.textContent = j.message || 'No se pudo validar el crédito.';
                $err.style.display = 'block';
                return;
            }
            idCreditoOk = v;
            document.getElementById('rep-nombre-cliente').textContent = j.nombre_cliente || '—';
            document.getElementById('rep-saldo').textContent = fmtMoney(j.saldo_actual);
            document.getElementById('rep-mora').textContent = (j.dias_mora != null ? j.dias_mora + ' días' : '—');
            document.getElementById('rep-gestor').textContent = j.gestor_nombre || '—';
            $panel.style.display = 'block';
            activarPaso2(true);
        } catch (e) {
            $err.textContent = 'Error de red al validar.';
            $err.style.display = 'block';
        } finally {
            repHideLoading();
            $validar.disabled = false;
        }
    });

    const LABELS = {
        moto_marca: 'Marca',
        moto_modelo: 'Modelo',
        moto_anio: 'Año',
        moto_placas: 'Placas',
        moto_no_serie: 'No. de serie (VIN)',
        moto_no_motor: 'No. de motor',
        moto_color: 'Color'
    };

    function repEscapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function repDisposeConsultarTooltip() {
        if (!$consultar || typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        const inst = bootstrap.Tooltip.getInstance($consultar);
        if (inst) inst.dispose();
    }

    function repResetConsultarBtnEstilo() {
        if (!$consultar) return;
        repDisposeConsultarTooltip();
        $consultar.classList.remove('rep-btn-consultar-fallo');
        $consultar.removeAttribute('data-bs-toggle');
        $consultar.removeAttribute('data-bs-placement');
        $consultar.removeAttribute('data-bs-html');
        $consultar.removeAttribute('title');
        $consultar.style.background = 'linear-gradient(90deg,var(--rep-dark),var(--rep-color))';
        $consultar.style.border = 'none';
        $consultar.style.color = '';
    }

    function repMarcarConsultarFalloServicio(detalle) {
        if (!$consultar) return;
        repResetConsultarBtnEstilo();
        const txt = (detalle && String(detalle).trim()) ? String(detalle).trim() : 'Servicio REPUVE no disponible.';
        $consultar.classList.add('rep-btn-consultar-fallo');
        $consultar.style.background = '#fff';
        $consultar.style.border = '2px solid #dc2626';
        $consultar.style.color = '#b91c1c';
        $consultar.setAttribute('title', txt);
        $consultar.setAttribute('data-bs-toggle', 'tooltip');
        $consultar.setAttribute('data-bs-placement', 'top');
        $consultar.setAttribute('data-bs-html', 'false');
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip($consultar, { container: 'body', customClass: 'rep-repuve-tooltip-wide' });
        }
    }

    function repBloqueDetalleRepuve(j) {
        let out = '';
        if (j.adj_operacion_sync_error) {
            out += '<div class="alert alert-danger py-2 small mt-2 mb-0"><strong>No se actualizó adj_operacion:</strong> '
                + repEscapeHtml(j.adj_operacion_sync_error) + '</div>';
        }
        if (j.repuve_respuesta_tecnica || j.repuve || j.repuve_ultima_encuesta || j.repuve_respuesta_api || j.repuve_respuesta_raw) {
            const pack = j.repuve_respuesta_tecnica && typeof j.repuve_respuesta_tecnica === 'object'
                ? j.repuve_respuesta_tecnica
                : {
                    repuve_resumen_ui: j.repuve || null,
                    ultima_encuesta_estatus: j.repuve_ultima_encuesta || null,
                    paso2_respuesta_api_guardada_en_bd: j.repuve_respuesta_api || null,
                    paso1_respuesta_inicio_consulta: j.repuve_respuesta_raw || null
                };
            out += '<details class="small mt-2"><summary class="text-muted">Ver respuesta técnica REPUVE (JSON — formato API Nubarium)</summary>'
                + '<pre class="bg-light border rounded p-2 mt-1 mb-0 text-start" style="font-size:0.72rem;max-height:320px;overflow:auto;white-space:pre-wrap;">'
                + repEscapeHtml(JSON.stringify(pack, null, 2))
                + '</pre></details>';
        }
        return out;
    }

    function renderDatosMoto(dm) {
        $resGrid.innerHTML = '';
        if (!dm || typeof dm !== 'object') {
            $resWrap.style.display = 'none';
            return;
        }
        const keys = Object.keys(LABELS).filter(function (k) { return dm[k]; });
        if (!keys.length) {
            $resWrap.style.display = 'none';
            return;
        }
        keys.forEach(function (k) {
            const kv = document.createElement('div');
            kv.className = 'rep-kv';
            kv.innerHTML = '<label>' + LABELS[k] + '</label><span>' + String(dm[k]).replace(/</g, '&lt;') + '</span>';
            $resGrid.appendChild(kv);
        });
        $resWrap.style.display = 'block';
    }

    $consultar.addEventListener('click', async function () {
        repResetConsultarBtnEstilo();
        $msg.innerHTML = '';
        $resWrap.style.display = 'none';
        if (!idCreditoOk) {
            $msg.innerHTML = '<div class="alert alert-warning py-2 small mb-0">Primero valida el crédito.</div>';
            return;
        }
        const tipo = ($tipo && $tipo.value ? $tipo.value : 'plate').trim();
        const valor = ($valor.value || '').trim();
        if (!valor) {
            $msg.innerHTML = '<div class="alert alert-warning py-2 small mb-0">Captura la placa o el VIN según el tipo elegido.</div>';
            return;
        }
        $consultar.disabled = true;
        repShowLoading('Consultando información…', 'Consulta REPUVE en curso — por favor espere');
        try {
            const r = await fetch('/MotosAdjudicadas/ejecutarConsultaRepuve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_credito: idCreditoOk, tipo: tipo, valor: valor })
            });
            const j = await r.json();
            if (j.limite_consultas) setLimite(j.limite_consultas);

            if (j.unavailable) {
                $msg.innerHTML = '<div class="alert alert-danger py-2 small mb-0">' + (j.message || 'REPUVE no disponible.') + '</div>';
                return;
            }
            if (j.limite_alcanzado) {
                $msg.innerHTML = '<div class="alert alert-danger py-2 small mb-0">' + (j.message || 'Límite diario.') + '</div>';
                return;
            }

            let cls = j.success ? 'success' : 'warning';
            if (j.repuve && j.repuve.estado === 'PROCESANDO') cls = 'info';
            if (j.repuve_error_servicio) {
                cls = 'danger';
                repMarcarConsultarFalloServicio(j.message || '');
            } else {
                repResetConsultarBtnEstilo();
            }
            if (!j.repuve_error_servicio && j.repuve_sin_datos_padron && !j.success) cls = 'warning';
            let txt = j.message || (j.success ? 'Listo.' : 'Sin datos.');
            if (j.repuve_error_servicio) {
                txt = 'REPUVE no disponible. El proveedor respondió con un error. Esto no se debe a sus datos.';
            }
            $msg.innerHTML = '<div class="alert alert-' + cls + ' py-1 px-2 small mb-0">' + String(txt).replace(/</g, '&lt;') + '</div>'
                + repBloqueDetalleRepuve(j);

            if (j.datos_moto) {
                renderDatosMoto(j.datos_moto);
            } else {
                renderDatosMoto(null);
            }
        } catch (e) {
            $msg.innerHTML = '<div class="alert alert-danger py-2 small mb-0">Error de red.</div>';
        } finally {
            repHideLoading();
            $consultar.disabled = false;
        }
    });
})();
</script>
