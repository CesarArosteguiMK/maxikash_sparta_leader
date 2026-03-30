<?php
/**
 * Modal Aclaraciones GC — Estado de cuenta (México / Guatemala).
 * Espera $dataEstadoCuenta; nombre: $dataCliente['nombreCliente'] o $nombreCliente.
 */
$nombreParaAcl = '';
if (isset($dataCliente) && is_array($dataCliente) && array_key_exists('nombreCliente', $dataCliente)) {
    $nombreParaAcl = (string) $dataCliente['nombreCliente'];
} elseif (isset($nombreCliente)) {
    $nombreParaAcl = (string) $nombreCliente;
}
$idCredAcl = (string) ($dataEstadoCuenta['idCredito'] ?? '');
$idCredAclEsc = htmlspecialchars($idCredAcl, ENT_QUOTES, 'UTF-8');
$nombreAclEsc = htmlspecialchars($nombreParaAcl, ENT_QUOTES, 'UTF-8');

$ecAclarFechaCdmxServidor = '';
try {
    $dtA = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $ecAclarFechaCdmxServidor = $dtA->format('d/m/Y H:i:s');
} catch (Throwable $e) {
    $ecAclarFechaCdmxServidor = date('d/m/Y H:i:s');
}
$ecAclarFechaCdmxEsc = htmlspecialchars($ecAclarFechaCdmxServidor, ENT_QUOTES, 'UTF-8');
?>
<div class="modal fade" id="modalAclaracionesGc" tabindex="-1" aria-labelledby="modalAclaracionesGcLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-info bg-opacity-10">
                <h5 class="modal-title" id="modalAclaracionesGcLabel">
                    <i class="fa fa-balance-scale text-info me-2" aria-hidden="true"></i>
                    Aclaraciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="ecAclaracionesIdCredito">ID crédito</label>
                        <input type="text" id="ecAclaracionesIdCredito" class="form-control bg-light" readonly autocomplete="off" value="<?= $idCredAclEsc ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="ecAclaracionesNombre">Nombre</label>
                        <input type="text" id="ecAclaracionesNombre" class="form-control bg-light" readonly autocomplete="off" value="<?= $nombreAclEsc ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="ecAclaracionesFechaCdmx">Fecha del reporte</label>
                        <input type="text" id="ecAclaracionesFechaCdmx" class="form-control bg-light" readonly autocomplete="off" value="<?= $ecAclarFechaCdmxEsc ?>">
                        <small class="text-muted">Zona horaria: Ciudad de México</small>
                    </div>
                    <div class="col-12">
                        <span class="form-label fw-semibold d-block mb-2">Tipo de reporte</span>
                        <div class="d-flex flex-wrap gap-4 align-items-center" role="radiogroup" aria-label="Tipo de reporte">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ecAclaracionesTipoReporte" id="ecAclaracionesTipoError" value="error" autocomplete="off">
                                <label class="form-check-label" for="ecAclaracionesTipoError">Error</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ecAclaracionesTipoReporte" id="ecAclaracionesTipoFalta" value="falta_aplicar" autocomplete="off">
                                <label class="form-check-label" for="ecAclaracionesTipoFalta">Falta aplicar</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="ecAclaracionesErrorWrap">
                        <label class="form-label fw-semibold" for="ecAclaracionesGastoCorregir">Gasto de cobranza a corregir</label>
                        <select id="ecAclaracionesGastoCorregir" class="form-select">
                            <option value="">Cargando gastos…</option>
                        </select>
                        <small class="text-muted d-block mt-1">Seleccione el cargo aplicado al cliente que desea reportar como error.</small>
                        <label class="form-label fw-semibold mt-3" for="ecAclaracionesMontoCorregir">Monto a corregir</label>
                        <input type="number" id="ecAclaracionesMontoCorregir" class="form-control" min="0" step="0.01" placeholder="0.00" autocomplete="off">
                    </div>
                    <div class="col-12 d-none" id="ecAclaracionesMontoWrap">
                        <label class="form-label fw-semibold" for="ecAclaracionesMonto">Monto a aplicar</label>
                        <input type="number" id="ecAclaracionesMonto" class="form-control" min="0" step="0.01" placeholder="0.00" autocomplete="off">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="ecAclaracionesObservaciones">Observaciones</label>
                        <textarea id="ecAclaracionesObservaciones" class="form-control" rows="4" placeholder="Detalle de la aclaración…" autocomplete="off"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="ecAclaracionesBtnGuardar" onclick="guardarAclaracionesGc()">
                    <i class="fa fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    function ecFormatearFechaHoraCdmx(d) {
        try {
            return new Intl.DateTimeFormat('es-MX', {
                timeZone: 'America/Mexico_City',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            }).format(d);
        } catch (e) {
            return d.toLocaleString('es-MX');
        }
    }

    function ecFormatearMonedaAcl(n) {
        try {
            return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);
        } catch (e) {
            return '$' + Number(n).toFixed(2);
        }
    }

    /** Incluye gastos ya aplicados (preload dedicado); fallback al listado solo pendientes. */
    function ecObtenerGastosCobranzaAclaraciones() {
        var elAcl = document.getElementById('ec-gastos-cobranza-aclaraciones-preload');
        if (elAcl && elAcl.textContent) {
            try {
                var arrAcl = JSON.parse(elAcl.textContent);
                if (Array.isArray(arrAcl) && arrAcl.length > 0) return arrAcl;
            } catch (e) { /* continuar */ }
        }
        if (typeof GASTOS_COBRANZA_PRELOAD !== 'undefined' && Array.isArray(GASTOS_COBRANZA_PRELOAD)) {
            return GASTOS_COBRANZA_PRELOAD;
        }
        var el = document.getElementById('ec-gastos-cobranza-preload');
        if (el && el.textContent) {
            try {
                var arr = JSON.parse(el.textContent);
                return Array.isArray(arr) ? arr : [];
            } catch (e) {
                return [];
            }
        }
        return [];
    }

    function ecRellenarSelectGastoCorregir() {
        var sel = document.getElementById('ecAclaracionesGastoCorregir');
        if (!sel) return;
        sel.innerHTML = '';
        var gastos = ecObtenerGastosCobranzaAclaraciones().filter(function (g) {
            if (!g) return false;
            var orig = parseFloat(g.monto_original);
            var pend = parseFloat(g.monto);
            return (orig > 0) || (pend > 0);
        });
        if (gastos.length === 0) {
            var opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = 'No hay gastos de cobranza registrados para este crédito';
            sel.appendChild(opt0);
            return;
        }
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = 'Seleccione un gasto…';
        sel.appendChild(ph);
        gastos.forEach(function (g) {
            var id = g.id_gasto != null ? g.id_gasto : g.id_gastos_cobranza;
            if (id == null) return;
            var sem = (g.semana != null && g.semana !== '') ? ('Sem. ' + g.semana + ' · ') : '';
            var per = g.periodo || '—';
            var pend = parseFloat(g.monto) || 0;
            var orig = parseFloat(g.monto_original) || 0;
            var o = document.createElement('option');
            o.value = String(id);
            o.setAttribute('data-monto-pendiente', String(pend));
            o.setAttribute('data-monto-original', String(orig));
            var sufijo;
            if (pend > 0) {
                sufijo = 'Pendiente ' + ecFormatearMonedaAcl(pend);
            } else {
                sufijo = 'Aplicado · Original ' + ecFormatearMonedaAcl(orig);
            }
            o.textContent = sem + per + ' · ' + sufijo;
            sel.appendChild(o);
        });
    }

    function ecAclaracionesTipoSeleccionado() {
        var r = document.querySelector('input[name="ecAclaracionesTipoReporte"]:checked');
        return r ? r.value : '';
    }

    function ecActualizarVistaTipoAclaraciones() {
        var wrapFalta = document.getElementById('ecAclaracionesMontoWrap');
        var wrapErr = document.getElementById('ecAclaracionesErrorWrap');
        var montoAplicar = document.getElementById('ecAclaracionesMonto');
        var montoCorr = document.getElementById('ecAclaracionesMontoCorregir');
        var selGasto = document.getElementById('ecAclaracionesGastoCorregir');
        var tipo = ecAclaracionesTipoSeleccionado();
        var esFalta = tipo === 'falta_aplicar';
        var esError = tipo === 'error';
        if (wrapFalta) {
            wrapFalta.classList.toggle('d-none', !esFalta);
            if (!esFalta && montoAplicar) montoAplicar.value = '';
        }
        if (wrapErr) {
            wrapErr.classList.toggle('d-none', !esError);
            if (esError) ecRellenarSelectGastoCorregir();
            if (!esError) {
                if (montoCorr) montoCorr.value = '';
                if (selGasto) selGasto.selectedIndex = 0;
            }
        }
    }

    function ecResetAclaracionesModal() {
        var fe = document.getElementById('ecAclaracionesFechaCdmx');
        if (fe) fe.value = ecFormatearFechaHoraCdmx(new Date());
        document.querySelectorAll('input[name="ecAclaracionesTipoReporte"]').forEach(function (el) {
            el.checked = false;
        });
        var obs = document.getElementById('ecAclaracionesObservaciones');
        if (obs) obs.value = '';
        var m = document.getElementById('ecAclaracionesMonto');
        if (m) m.value = '';
        var mc = document.getElementById('ecAclaracionesMontoCorregir');
        if (mc) mc.value = '';
        ecRellenarSelectGastoCorregir();
        ecActualizarVistaTipoAclaraciones();
    }

    function ecInitAclaracionesModal() {
        var modalEl = document.getElementById('modalAclaracionesGc');
        if (!modalEl) return;

        modalEl.addEventListener('show.bs.modal', ecResetAclaracionesModal);

        document.querySelectorAll('input[name="ecAclaracionesTipoReporte"]').forEach(function (el) {
            el.addEventListener('change', ecActualizarVistaTipoAclaraciones);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ecInitAclaracionesModal);
    } else {
        ecInitAclaracionesModal();
    }
})();

function guardarAclaracionesGc() {
    var tipo = '';
    var chk = document.querySelector('input[name="ecAclaracionesTipoReporte"]:checked');
    if (chk) tipo = chk.value;
    if (!tipo) {
        if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Seleccione el tipo de reporte.', 'warning');
        else alert('Seleccione el tipo de reporte.');
        return;
    }
    if (tipo === 'falta_aplicar') {
        var m = parseFloat((document.getElementById('ecAclaracionesMonto') || {}).value);
        if (!(m > 0)) {
            if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Indique un monto a aplicar mayor a cero.', 'warning');
            else alert('Indique un monto a aplicar mayor a cero.');
            return;
        }
    }
    if (tipo === 'error') {
        var idG = (document.getElementById('ecAclaracionesGastoCorregir') || {}).value || '';
        if (!idG) {
            if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Seleccione el gasto de cobranza a corregir.', 'warning');
            else alert('Seleccione el gasto de cobranza a corregir.');
            return;
        }
        var mc = parseFloat((document.getElementById('ecAclaracionesMontoCorregir') || {}).value);
        if (!(mc > 0)) {
            if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Indique un monto a corregir mayor a cero.', 'warning');
            else alert('Indique un monto a corregir mayor a cero.');
            return;
        }
    }
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Siguiente paso',
            text: 'La persistencia en base de datos (__SPARTA_SECRET_REDACTED__) se conectará al avanzar el desarrollo.'
        });
    }
}
</script>
