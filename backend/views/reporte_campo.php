<?php
$puedeConBajas = !empty($puede_reporte_campo_con_bajas);
$puedeSinBajas = !empty($puede_reporte_campo_sin_bajas);
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1">
                <i class="fa-solid fa-clipboard-list me-2"></i>Reporte de Campo
            </h3>
            <p class="text-muted mb-0">Personal de Campo 1-7, Campo 8-30 y Campo 30+ con jerarquía operativa.</p>
        </div>
    </div>

    <?php if ($puedeConBajas): ?>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h5 class="mb-2">Organigrama cobranza de campo con bajas</h5>
                    <p class="text-muted mb-0">
                        Genera un Excel actualizado con gestores, puestos, departamentos y jefaturas:
                        supervisor, subgerente, gerente y subdirector, incluyendo bajas marcadas en rojo.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="/analitica/descargarReporteCampoExcel/con-bajas" class="btn btn-primary js-descargar-reporte-campo">
                        <i class="fa-solid fa-download me-2"></i>Descargar Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($puedeSinBajas): ?>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h5 class="mb-2">Organigrama cobranza de campo sin bajas</h5>
                    <p class="text-muted mb-0">
                        Genera el mismo Excel operativo de cobranza de campo, pero excluyendo las personas en estatus de baja.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="/analitica/descargarReporteCampoExcel/sin-bajas" class="btn btn-primary js-descargar-reporte-campo">
                        <i class="fa-solid fa-download me-2"></i>Descargar Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$puedeConBajas && !$puedeSinBajas): ?>
    <div class="alert alert-warning shadow-sm">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        No tienes permisos asignados para descargar reportes de campo.
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var botones = document.querySelectorAll('.js-descargar-reporte-campo');
    if (!botones.length) return;

    botones.forEach(function (btn) {
    btn.addEventListener('click', function () {
        if (window.Swal) {
            Swal.fire({
                title: 'Generando reporte',
                text: 'La descarga comenzará en unos segundos.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });
            setTimeout(function () {
                if (Swal.isVisible()) {
                    Swal.close();
                }
            }, 4500);
            return;
        }

        btn.classList.add('disabled');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Generando...';
        setTimeout(function () {
            btn.classList.remove('disabled');
            btn.innerHTML = '<i class="fa-solid fa-download me-2"></i>Descargar Excel';
        }, 4500);
    });
    });
});
</script>
