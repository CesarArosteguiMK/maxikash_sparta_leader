<div class="card">
    <div class="card">
        <div class="card">
            <div class="row g-0 align-items-center">

                <!-- Texto -->
                <div class="col-12 col-md-8">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">HOLA, <?= $_SESSION['usuario_nombre']; ?> </h5>
                        <p class="mb-6">
                            Accede a los reportes esenciales del módulo Sabuesos. Visualiza datos sobre tickets, análisis administrativo y gestión de casos cerrados. ¡Información estratégica al alcance de un clic!
                        </p>

                    </div>
                </div>

                <!-- Imagen -->
                <div class="col-12 col-md-4">
                    <div class="card-body ps-md-2 pe-5 text-end">
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                             class="img-fluid scaleX-n1-rtl"
                             alt="View Badge User">
                    </div>
                </div>

                <div class="row gy-6 mb-6">

                    <!-- REPORTE 1: Tickets -->
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Reporte de Tickets</h5>
                                        <p class="text-body w-sm-80 app-academy-xl-100">Visualiza información sobre tickets activos, creación, vencimiento y estado de asignación.</p>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-descargar-tickets" title="Descargar reporte en Excel">
                                        <i class="fa fa-download me-2"></i>Descargar Reporte
                                    </button>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png?ga=GA1.1.191732613.1764875703" alt="tickets illustration">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- REPORTE 2: Panel Admin -->
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Reporte Panel Admin</h5>
                                        <p class="text-body app-academy-sm-60 app-academy-xl-100">Análisis administrativo con detalles de rastreo, asignación y gestión de tickets.</p>
                                    </div>
                                    <div class="mb-0">
                                        <button type="button" class="btn btn-primary btn-descargar-admin" title="Descargar reporte en Excel">
                                            <i class="fa fa-download me-2"></i>Descargar Reporte
                                        </button>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053333.png?ga=GA1.1.191732613.1764875703" alt="admin illustration">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- REPORTE 3: Cerrado/Eliminado -->
                    <div class="col-lg-4">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                                <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                    <div class="card-title">
                                        <h5 class="text-primary mb-2">Reporte Cerrado/Eliminado</h5>
                                        <p class="text-body app-academy-sm-60 app-academy-xl-100">Historial de tickets cerrados o eliminados con detalles de cierre y personal responsable.</p>
                                    </div>
                                    <div class="mb-0">
                                        <button type="button" class="btn btn-primary btn-descargar-cerrado" title="Descargar reporte en Excel">
                                            <i class="fa fa-download me-2"></i>Descargar Reporte
                                        </button>
                                    </div>
                                </div>
                                <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                    <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053244.png?ga=GA1.1.191732613.1764875703" alt="closed illustration">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos personalizados para los botones de reportes */
    .btn-reporte-tickets,
    .btn-reporte-admin,
    .btn-reporte-cerrado {
        transition: all 0.3s ease;
    }

    .btn-reporte-tickets:hover,
    .btn-reporte-admin:hover,
    .btn-reporte-cerrado:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Botón: Descargar Reporte de Tickets
        document.querySelector('.btn-descargar-tickets').addEventListener('click', function() {
            Swal.fire({
                title: 'Descargar Reporte de Tickets',
                html: `
                    <p>Se descargará un archivo Excel con:</p>
                    <ul style="text-align: left; display: inline-block; margin-bottom: 1rem;">
                        <li>✓ Tickets activos del usuario</li>
                        <li>✓ Estado y prioridad</li>
                        <li>✓ Fechas de creación y vencimiento</li>
                        <li>✓ Asignación y dictámenes</li>
                    </ul>
                    <p><strong>¿Deseas descargar?</strong></p>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-download me-2"></i>Descargar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#a1acb8'
            }).then(result => {
                if (result.isConfirmed) {
                    descargarReporte('/Reporteria/descargarReporteSabuesos1', 'Tickets');
                }
            });
        });

        // Botón: Descargar Reporte Panel Admin
        document.querySelector('.btn-descargar-admin').addEventListener('click', function() {
            Swal.fire({
                title: 'Descargar Reporte Panel Admin',
                html: `
                    <p>Se descargará un archivo Excel con:</p>
                    <ul style="text-align: left; display: inline-block; margin-bottom: 1rem;">
                        <li>✓ Todos los tickets del sistema</li>
                        <li>✓ Quién levantó cada ticket</li>
                        <li>✓ Asignación y estado</li>
                        <li>✓ Información de dictámenes</li>
                    </ul>
                    <p><strong>¿Deseas descargar?</strong></p>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-download me-2"></i>Descargar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#a1acb8'
            }).then(result => {
                if (result.isConfirmed) {
                    descargarReporte('/Reporteria/descargarReporteSabuesos2', 'Panel Admin');
                }
            });
        });

        // Botón: Descargar Reporte Cerrado/Eliminado
        document.querySelector('.btn-descargar-cerrado').addEventListener('click', function() {
            Swal.fire({
                title: 'Descargar Reporte Cerrado - Eliminado',
                html: `
                    <p>Se descargará un archivo Excel con:</p>
                    <ul style="text-align: left; display: inline-block; margin-bottom: 1rem;">
                        <li>✓ Tickets cerrados o eliminados</li>
                        <li>✓ Quién creó cada ticket</li>
                        <li>✓ Quién lo cerró/eliminó</li>
                        <li>✓ Fechas de acción</li>
                    </ul>
                    <p><strong>¿Deseas descargar?</strong></p>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-download me-2"></i>Descargar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#a1acb8'
            }).then(result => {
                if (result.isConfirmed) {
                    descargarReporte('/Reporteria/descargarReporteSabuesos3', 'Cerrado-Eliminado');
                }
            });
        });

        // Función auxiliar para descargar
        function descargarReporte(endpoint, nombreReporte) {
            Swal.fire({
                title: 'Generando reporte...',
                text: `Por favor espera mientras se prepara ${nombreReporte}`,
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en la descarga');
                return response.blob();
            })
            .then(blob => {
                Swal.close();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Reporte_Sabuesos_${nombreReporte}_${new Date().toISOString().slice(0, 10)}.xlsx`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Descarga completada',
                    text: `El reporte de ${nombreReporte} se ha descargado correctamente`,
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la descarga',
                    text: 'No se pudo generar el reporte. Intenta de nuevo.',
                });
            });
        }
    });
</script>
