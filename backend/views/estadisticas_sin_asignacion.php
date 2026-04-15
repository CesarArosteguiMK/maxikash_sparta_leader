<?php
/**
 * Se muestra cuando el usuario tiene el módulo Analítica sabueso pero no tiene
 * ningún tipo asignado en Asignación por puestos → Estadísticas por puesto.
 */
$titulo = isset($titulo) ? $titulo : 'Analítica sabueso';
?>
<style>
#estadisticasSinAsignacionWrap .card { border-left: 4px solid var(--bs-primary); }
#estadisticasSinAsignacionWrap .texto-aviso { font-size: 1.05rem; line-height: 1.6; color: #566a7f; }
</style>
<div class="card mb-4" id="estadisticasSinAsignacionWrap">
    <div class="card-body py-5 px-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-8">
                <h5 class="text-primary mb-3"><?= htmlspecialchars((string) $titulo, ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="texto-aviso mb-4">
                    Aún no tiene asignado el tablero de Analítica sabueso para su puesto. Si considera que debería tener acceso, contacte al administrador del sistema.
                </p>
            </div>
            <div class="col-12 col-md-4 text-center mt-4 mt-md-0">
                <div class="rounded-3 d-inline-flex align-items-center justify-content-center bg-label-primary p-4">
                    <i class="fa-solid fa-chart-column fa-3x text-primary" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>
