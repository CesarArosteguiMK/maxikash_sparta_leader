<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1">
                <i class="fa-solid fa-clipboard-list me-2"></i>Reporte de Campo
            </h3>
            <p class="text-muted mb-0">Personal de Campo 1-7 y Campo 8-21 con jerarquía operativa.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h5 class="mb-2">Descarga del reporte</h5>
                    <p class="text-muted mb-0">
                        Genera un Excel actualizado con gestores, puestos, departamentos y jefaturas:
                        supervisor, subgerente, gerente y subdirector.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="/analitica/descargarReporteCampoExcel" class="btn btn-primary" id="btnDescargarReporteCampo">
                        <i class="fa-solid fa-download me-2"></i>Descargar Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnDescargarReporteCampo');
    if (!btn) return;

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
</script>
