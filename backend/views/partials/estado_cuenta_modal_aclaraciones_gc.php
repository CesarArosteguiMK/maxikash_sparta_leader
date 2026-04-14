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

$ecMeta = isset($ecAclaracionesUltimoPagoMeta) && is_array($ecAclaracionesUltimoPagoMeta)
    ? $ecAclaracionesUltimoPagoMeta
    : [
        'ymd' => null,
        'min_guardar_ymd' => '',
        'max_guardar_ymd' => '',
        'tiene_ultimo_pago' => false,
        'ventana_ok' => false,
        'permite_guardar' => false,
        'label_display' => 'Sin registro en Segundómetro',
        'ventana_falta_mensaje' => '',
        'ventana_falta_lunes_fin_semana' => false,
    ];
$ymdMeta = isset($ecMeta['ymd']) && is_string($ecMeta['ymd']) ? $ecMeta['ymd'] : '';
$tieneYmdValido = $ymdMeta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymdMeta);
$ecAclarTieneUltimoPago = array_key_exists('tiene_ultimo_pago', $ecMeta)
    ? !empty($ecMeta['tiene_ultimo_pago'])
    : $tieneYmdValido;
$ecAclarVentanaOk = array_key_exists('ventana_ok', $ecMeta)
    ? !empty($ecMeta['ventana_ok'])
    : (!empty($ecMeta['permite_guardar']));
$ecAclarPuedeGuardar = $ecAclarTieneUltimoPago;
$ecAclarLabelUltimoEsc = htmlspecialchars((string) ($ecMeta['label_display'] ?? ''), ENT_QUOTES, 'UTF-8');
$ecMetaJson = htmlspecialchars(
    json_encode(
        [
            'permite' => $ecAclarTieneUltimoPago,
            'ventana_ok' => $ecAclarVentanaOk,
            'label_ultimo' => (string) ($ecMeta['label_display'] ?? ''),
            'ymd' => $ecMeta['ymd'] ?? null,
            'min' => (string) ($ecMeta['min_guardar_ymd'] ?? ''),
            'max' => (string) ($ecMeta['max_guardar_ymd'] ?? ''),
            'ventana_falta_mensaje' => (string) ($ecMeta['ventana_falta_mensaje'] ?? ''),
            'ventana_falta_lunes_fin_semana' => !empty($ecMeta['ventana_falta_lunes_fin_semana']),
        ],
        JSON_UNESCAPED_UNICODE
    ),
    ENT_QUOTES,
    'UTF-8'
);

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
                    <input type="hidden" id="ecAclaracionesPagoMetaJson" value="<?= $ecMetaJson ?>">
                    <?php if (!$ecAclarTieneUltimoPago): ?>
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 rounded-3 border border-danger bg-label-danger p-2">
                            <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                <span class="small text-muted text-nowrap">Último pago efectivo</span>
                                <span class="fw-semibold text-truncate" id="ecAclaracionesUltimoPago"><?= htmlspecialchars((string) ($ecMeta['label_display'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <span class="fw-semibold text-danger text-nowrap align-self-center">Sin dato en Segundómetro</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-12" id="ecAclaracionesRowUltimoDetalle">
                        <label class="form-label fw-semibold" for="ecAclaracionesUltimoPagoInput">Último pago efectivo (Segundómetro)</label>
                        <input type="text" id="ecAclaracionesUltimoPagoInput" class="form-control bg-light" readonly autocomplete="off" value="<?= $ecAclarLabelUltimoEsc ?>">
                    </div>
                    <div class="col-12 d-none" id="ecAclaracionesBannerVentana">
                        <div id="ecAclaracionesBannerVentanaInner" class="rounded-3 border border-danger bg-label-danger p-2">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                    <span class="small text-muted text-nowrap">Último pago efectivo</span>
                                    <span class="fw-semibold text-truncate" id="ecAclaracionesBannerVentanaFecha"><?= $ecAclarLabelUltimoEsc ?></span>
                                </div>
                                <span id="ecAclaracionesBannerVentanaEtiqueta" class="fw-semibold text-danger text-nowrap align-self-center">No se puede guardar</span>
                            </div>
                            <p class="small text-danger mb-0 mt-2" id="ecAclaracionesBannerVentanaMsg"></p>
                        </div>
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
                        <label class="form-label fw-semibold" for="ecAclaracionesMontoCorregir">Monto a corregir</label>
                        <input type="number" id="ecAclaracionesMontoCorregir" class="form-control" min="0" step="0.01" placeholder="0.00" autocomplete="off">
                    </div>
                    <div class="col-12 d-none" id="ecAclaracionesMontoWrap">
                        <label class="form-label fw-semibold" for="ecAclaracionesMonto">Monto a aplicar</label>
                        <input type="number" id="ecAclaracionesMonto" class="form-control" min="0" step="0.01" placeholder="0.00" autocomplete="off">
                    </div>
                    <div class="col-12" id="ecAclaracionesObsWrap">
                        <label class="form-label fw-semibold" for="ecAclaracionesObservaciones">Observaciones</label>
                        <textarea id="ecAclaracionesObservaciones" class="form-control" rows="4" placeholder="Detalle de la aclaración…" autocomplete="off"></textarea>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <?php if ($ecAclarPuedeGuardar): ?>
                <span id="ecAclaracionesFooterGuardarWrap">
                    <button type="button" class="btn btn-primary" id="ecAclaracionesBtnGuardar" onclick="guardarAclaracionesGc()">
                        <i class="fa fa-save me-1"></i>Guardar
                    </button>
                </span>
                <?php endif; ?>
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

    function ecAclaracionesTipoSeleccionado() {
        var r = document.querySelector('input[name="ecAclaracionesTipoReporte"]:checked');
        return r ? r.value : '';
    }

    function ecAclaracionesLeerMeta() {
        var metaIn = document.getElementById('ecAclaracionesPagoMetaJson');
        if (!metaIn || !metaIn.value) return null;
        try {
            return JSON.parse(metaIn.value);
        } catch (e) {
            return null;
        }
    }

    function ecActualizarVistaTipoAclaraciones() {
        var meta = ecAclaracionesLeerMeta();
        var ventanaOk = !!(meta && meta.ventana_ok);
        var tipo = ecAclaracionesTipoSeleccionado();
        var bloqueadoFaltaVentana = (tipo === 'falta_aplicar' && !ventanaOk);

        var rowUltimo = document.getElementById('ecAclaracionesRowUltimoDetalle');
        var banner = document.getElementById('ecAclaracionesBannerVentana');
        var fechaBanner = document.getElementById('ecAclaracionesBannerVentanaFecha');
        if (fechaBanner) {
            var lblUlt = (meta && meta.label_ultimo) ? String(meta.label_ultimo) : '';
            if (!lblUlt) {
                var inpUlt = document.getElementById('ecAclaracionesUltimoPagoInput');
                lblUlt = inpUlt ? String(inpUlt.value || '') : '';
            }
            fechaBanner.textContent = lblUlt;
        }

        var bannerMsg = document.getElementById('ecAclaracionesBannerVentanaMsg');
        var bannerInner = document.getElementById('ecAclaracionesBannerVentanaInner');
        var bannerEtq = document.getElementById('ecAclaracionesBannerVentanaEtiqueta');
        var clsDangerInner = 'rounded-3 border border-danger bg-label-danger p-2';
        var clsOkInner = 'rounded-3 border border-success bg-label-success p-2';
        if (bloqueadoFaltaVentana) {
            if (rowUltimo) rowUltimo.classList.add('d-none');
            if (banner) banner.classList.remove('d-none');
            var esFinSem = !!(meta && meta.ventana_falta_lunes_fin_semana);
            if (bannerInner) bannerInner.className = esFinSem ? clsOkInner : clsDangerInner;
            if (bannerEtq) {
                bannerEtq.className = esFinSem
                    ? 'fw-semibold text-success text-nowrap align-self-center'
                    : 'fw-semibold text-danger text-nowrap align-self-center';
                bannerEtq.textContent = esFinSem ? 'Información' : 'No se puede guardar';
            }
            if (bannerMsg) {
                bannerMsg.className = esFinSem ? 'small text-success mb-0 mt-2' : 'small text-danger mb-0 mt-2';
                bannerMsg.textContent = (meta && meta.ventana_falta_mensaje)
                    ? String(meta.ventana_falta_mensaje)
                    : 'No es posible registrar «falta aplicar» con la fecha de último pago mostrada (calendario Ciudad de México).';
            }
        } else {
            if (rowUltimo) rowUltimo.classList.remove('d-none');
            if (banner) banner.classList.add('d-none');
            if (bannerInner) bannerInner.className = clsDangerInner;
            if (bannerEtq) {
                bannerEtq.className = 'fw-semibold text-danger text-nowrap align-self-center';
                bannerEtq.textContent = 'No se puede guardar';
            }
            if (bannerMsg) {
                bannerMsg.className = 'small text-danger mb-0 mt-2';
                bannerMsg.textContent = '';
            }
        }

        var wrapFalta = document.getElementById('ecAclaracionesMontoWrap');
        var wrapErr = document.getElementById('ecAclaracionesErrorWrap');
        var montoAplicar = document.getElementById('ecAclaracionesMonto');
        var montoCorr = document.getElementById('ecAclaracionesMontoCorregir');
        var obsWrap = document.getElementById('ecAclaracionesObsWrap');
        var btnWrap = document.getElementById('ecAclaracionesFooterGuardarWrap');

        if (bloqueadoFaltaVentana) {
            if (wrapFalta) {
                wrapFalta.classList.add('d-none');
                if (montoAplicar) montoAplicar.value = '';
            }
            if (wrapErr) {
                wrapErr.classList.add('d-none');
                if (montoCorr) montoCorr.value = '';
            }
            if (obsWrap) {
                obsWrap.classList.add('d-none');
                var obs = document.getElementById('ecAclaracionesObservaciones');
                if (obs) obs.value = '';
            }
            if (btnWrap) btnWrap.classList.add('d-none');
            return;
        }

        if (obsWrap) obsWrap.classList.remove('d-none');
        if (btnWrap) btnWrap.classList.remove('d-none');

        var esFalta = tipo === 'falta_aplicar';
        var esError = tipo === 'error';
        if (wrapFalta) {
            wrapFalta.classList.toggle('d-none', !esFalta);
            if (!esFalta && montoAplicar) montoAplicar.value = '';
        }
        if (wrapErr) {
            wrapErr.classList.toggle('d-none', !esError);
            if (!esError && montoCorr) montoCorr.value = '';
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
    var metaIn = document.getElementById('ecAclaracionesPagoMetaJson');
    var meta = null;
    if (metaIn && metaIn.value) {
        try {
            meta = JSON.parse(metaIn.value);
        } catch (e1) {
            meta = null;
        }
    }
    if (!meta || !meta.permite) {
        if (typeof Swal !== 'undefined') Swal.fire('No permitido', 'No hay fecha de último pago efectivo en Segundómetro para este crédito.', 'warning');
        else alert('No se puede guardar.');
        return;
    }

    var tipo = '';
    var chk = document.querySelector('input[name="ecAclaracionesTipoReporte"]:checked');
    if (chk) tipo = chk.value;
    if (!tipo) {
        if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Seleccione el tipo de reporte.', 'warning');
        else alert('Seleccione el tipo de reporte.');
        return;
    }

    var ymd = meta.ymd || '';
    var minY = meta.min || '';
    var maxY = meta.max || '';
    var ventanaOk = !!meta.ventana_ok;
    if (tipo === 'falta_aplicar') {
        if (!ventanaOk || !ymd || !minY || !maxY || ymd < minY || ymd > maxY) {
            var txtVent = (meta && meta.ventana_falta_mensaje) ? String(meta.ventana_falta_mensaje)
                : 'Para «Falta aplicar» el último pago efectivo debe estar dentro de la ventana permitida (calendario Ciudad de México).';
            var esFinSemSw = !!(meta && meta.ventana_falta_lunes_fin_semana);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: esFinSemSw ? 'Aviso' : 'No permitido',
                    text: txtVent,
                    icon: esFinSemSw ? 'success' : 'warning'
                });
            } else {
                alert(txtVent);
            }
            return;
        }
    }
    var monto = 0;
    if (tipo === 'falta_aplicar') {
        monto = parseFloat((document.getElementById('ecAclaracionesMonto') || {}).value);
        if (!(monto > 0)) {
            if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Indique un monto a aplicar mayor a cero.', 'warning');
            else alert('Indique un monto a aplicar mayor a cero.');
            return;
        }
    }
    if (tipo === 'error') {
        monto = parseFloat((document.getElementById('ecAclaracionesMontoCorregir') || {}).value);
        if (!(monto > 0)) {
            if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Indique un monto a corregir mayor a cero.', 'warning');
            else alert('Indique un monto a corregir mayor a cero.');
            return;
        }
    }
    var idCredStr = (document.getElementById('ecAclaracionesIdCredito') || {}).value || '';
    var idCred = parseInt(idCredStr, 10);
    if (!(idCred > 0)) {
        if (typeof Swal !== 'undefined') Swal.fire('Atención', 'ID de crédito no válido.', 'warning');
        else alert('ID de crédito no válido.');
        return;
    }
    var nombre = (document.getElementById('ecAclaracionesNombre') || {}).value || '';
    var mensaje = (document.getElementById('ecAclaracionesObservaciones') || {}).value || '';
    var btn = document.getElementById('ecAclaracionesBtnGuardar');
    if (btn) btn.disabled = true;

    fetch('/EstadoCuenta/GuardarAclaracionGc', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            id_credito: idCred,
            nombre: nombre,
            tipo_reporte: tipo,
            monto: monto,
            mensaje: mensaje
        })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (btn) btn.disabled = false;
            if (data && data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Guardado', text: data.mensaje || 'Aclaración registrada.' }).then(function () {
                        var modalEl = document.getElementById('modalAclaracionesGc');
                        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getInstance(modalEl);
                            if (inst) inst.hide();
                        }
                    });
                } else {
                    alert(data.mensaje || 'Guardado.');
                }
            } else {
                var errTxt = (data && data.mensaje) ? data.mensaje : 'No se pudo guardar.';
                if (typeof Swal !== 'undefined') {
                    var esAviso = data && data.alerta === 'info';
                    Swal.fire({
                        icon: esAviso ? 'info' : 'error',
                        title: esAviso ? 'Aviso' : 'Error',
                        text: errTxt
                    });
                } else {
                    alert(errTxt);
                }
            }
        })
        .catch(function () {
            if (btn) btn.disabled = false;
            if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error de conexión.', 'error');
            else alert('Error de conexión.');
        });
}
</script>
