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
</style>

<div class="rep-wrap">
    <div class="rep-hero rep-animate">
        <h4><i class="fa-solid fa-id-card-clip me-2" style="color:var(--rep-color)"></i>Consulta REPUVE</h4>
        <p class="text-muted small mb-2 mb-md-3">
            Valida el crédito adjudicado, consulta por <strong>placa</strong> o <strong>VIN</strong> y guardamos los datos del vehículo en la operación.
            En <strong>Mis adjudicaciones</strong> → Registrar evidencias, los campos de la moto se cargarán automáticamente.
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
                    <dt>Saldo</dt><dd id="rep-saldo">—</dd>
                    <dt>Mora</dt><dd id="rep-mora">—</dd>
                    <dt>Gestor</dt><dd id="rep-gestor">—</dd>
                </dl>
            </div>
            <div id="rep-credito-err" class="alert alert-warning py-2 px-3 small mt-2" style="display:none;"></div>
        </div>
    </div>

    <div class="rep-card" id="rep-step-criterio" style="opacity:0.5; pointer-events:none;">
        <div class="rep-card-hdr"><i class="fa-solid fa-motorcycle me-1"></i> 2. Criterio REPUVE</div>
        <div class="rep-card-body">
            <div class="row g-2 mb-2">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Buscar por</label>
                    <select class="form-select" id="rep-tipo">
                        <option value="plate">Placa</option>
                        <option value="vin">VIN (serie)</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label small text-muted mb-1">Valor</label>
                    <input type="text" class="form-control text-uppercase" id="rep-valor" placeholder="Placa o VIN según el tipo" autocomplete="off" />
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
                Estos datos se guardan en la operación del crédito. Al abrir <strong>Registrar evidencias</strong> en Mis adjudicaciones, el formulario ya vendrá lleno en «Datos de la motocicleta».
            </div>
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
    const $msg = document.getElementById('rep-consulta-msg');
    const $resWrap = document.getElementById('rep-result-wrap');
    const $resGrid = document.getElementById('rep-result-grid');
    const $limU = document.getElementById('rep-limite-uso');
    const $limR = document.getElementById('rep-limite-rest');

    let idCreditoOk = 0;

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
        try {
            const r = await fetch('/MotosAdjudicadas/buscarCredito', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ valor: v })
            });
            const j = await r.json();
            if (!j.success) {
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
        $msg.innerHTML = '';
        $resWrap.style.display = 'none';
        if (!idCreditoOk) {
            $msg.innerHTML = '<div class="alert alert-warning py-2 small mb-0">Primero valida el crédito.</div>';
            return;
        }
        const tipo = ($tipo.value || 'plate').trim();
        const valor = ($valor.value || '').trim();
        if (!valor) {
            $msg.innerHTML = '<div class="alert alert-warning py-2 small mb-0">Captura placa o VIN.</div>';
            return;
        }
        $consultar.disabled = true;
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
            const txt = j.message || (j.success ? 'Listo.' : 'Sin datos.');
            $msg.innerHTML = '<div class="alert alert-' + cls + ' py-2 small mb-0">' + String(txt).replace(/</g, '&lt;') + '</div>';

            if (j.datos_moto) {
                renderDatosMoto(j.datos_moto);
            } else {
                renderDatosMoto(null);
            }
        } catch (e) {
            $msg.innerHTML = '<div class="alert alert-danger py-2 small mb-0">Error de red.</div>';
        } finally {
            $consultar.disabled = false;
        }
    });
})();
</script>
