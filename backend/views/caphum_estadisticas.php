<?php
/**
 * Estadísticas Capital Humano — vista con Bootstrap del tema (sin bloque `<style>` propio).
 * Datos: JSON inicial en vista + `fetch` al panel (lógica de negocio sin cambiar aquí).
 *
 * @var string $titulo
 * @var int $anioDefault
 * @var int $mesDefault
 * @var string $datosInicialesJson
 * @var int $semanaDefault
 * @var string|null $chEstRangoIni Y-m-d (lunes de la semana en curso)
 * @var string|null $chEstRangoFin Y-m-d (hoy; periodo por defecto = lunes → hoy)
 */
$anioDefault = isset($anioDefault) ? (int) $anioDefault : (int) date('Y');
$mesDefault = isset($mesDefault) ? (int) $mesDefault : (int) date('n');
$datosInicialesJson = $datosInicialesJson ?? '{}';
$semanaDefault = isset($semanaDefault) ? (int) $semanaDefault : 0;
if (isset($chEstRangoIni, $chEstRangoFin) && is_string($chEstRangoIni) && is_string($chEstRangoFin)
    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $chEstRangoIni) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $chEstRangoFin)) {
    // ya fijado en CapHum::estadisticas (lunes → hoy)
} else {
    try {
        $__h = new \DateTimeImmutable('today');
        $__dow = (int) $__h->format('N');
        $__lun = $__h->modify('-' . ($__dow - 1) . ' days');
        $chEstRangoIni = $__lun->format('Y-m-d');
        $chEstRangoFin = $__h->format('Y-m-d');
    } catch (\Throwable $e) {
        $chEstRangoIni = date('Y-m-d');
        $chEstRangoFin = date('Y-m-d');
    }
}
?>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fa-solid fa-users me-2 text-primary"></i>Estadísticas Capital Humano
                </h4>
                <p id="chEstRangoFechas" class="text-muted mb-0 small">—</p>
            </div>
            <div class="ch-est-fp-rango p-1 p-md-0" style="max-width: 34rem; width: 100%;">
                <label for="flatpickr-range-ch-est" class="form-label text-muted mb-1 fw-semibold" style="font-size:.82rem;">
                    <i class="fa fa-calendar-alt me-1" aria-hidden="true"></i>Periodo (rango de fechas)
                </label>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" id="flatpickr-range-ch-est" readonly
                        class="form-control flex-grow-1 ch-est-fp-input shadow-none"
                        style="min-width: 13rem; max-width: 22rem; cursor: pointer; user-select: none; min-height: 2.35rem;"
                        placeholder="Selecciona inicio y fin" autocomplete="off"
                        title="No se pueden elegir fechas posteriores a hoy." />
                    <button type="button" class="btn btn-outline-secondary flex-shrink-0 px-3" id="btnChEstRestablecerPeriodo"
                        style="min-height: 2.35rem;"
                        title="Volver al periodo por defecto: lunes de esta semana hasta hoy">
                        Restablecer
                    </button>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-12 col-md-3">
                        <label for="chEstFiltroDireccion" class="form-label text-muted mb-1 fw-semibold" style="font-size:.8rem;">Direcci&oacute;n</label>
                        <select id="chEstFiltroDireccion" class="form-select shadow-none" style="min-height: 2.35rem;">
                            <option value="">Todas las direcciones</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-none" id="chEstWrapFiltroArea">
                        <label for="chEstFiltroArea" class="form-label text-muted mb-1 fw-semibold" style="font-size:.8rem;">Área</label>
                        <select id="chEstFiltroArea" class="form-select shadow-none" style="min-height: 2.35rem;" disabled>
                            <option value="">Todas las áreas</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-none" id="chEstWrapFiltroDepto">
                        <label for="chEstFiltroDepartamento" class="form-label text-muted mb-1 fw-semibold" style="font-size:.8rem;">Departamento</label>
                        <select id="chEstFiltroDepartamento" class="form-select shadow-none" style="min-height: 2.35rem;" disabled>
                            <option value="">Todos los departamentos</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-none" id="chEstWrapFiltroPuesto">
                        <label for="chEstFiltroPuesto" class="form-label text-muted mb-1 fw-semibold" style="font-size:.8rem;">Puesto</label>
                        <select id="chEstFiltroPuesto" class="form-select shadow-none" style="min-height: 2.35rem;" disabled>
                            <option value="">Todos los puestos</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs: misma cuadrícula y marco que Gastos Cobranza (`gastos_cobranza_estadistica.php`) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Empleados activos</span>
                        <div class="ch-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div id="chKpiActivos" class="fs-4 fw-bold text-success">0</div>
                        <div class="small text-muted mt-1" id="chKpiActivosSub">—</div>
                        <div class="mt-auto pt-2"><span class="badge rounded-pill bg-label-success text-success" id="chKpiActivosPctBadge">—</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Ingresos del periodo</span>
                        <div class="ch-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div id="chKpiTotalEmp" class="fs-4 fw-bold text-success">0</div>
                        <div class="small text-muted mt-1 flex-grow-1" id="chKpiTotalSub">—</div>
                        <div class="mt-auto pt-2"><span class="badge rounded-pill bg-label-success text-success" id="chKpiTotalPctBadge">—</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Empleados baja</span>
                        <div class="ch-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div id="chKpiInactivos" class="fs-4 fw-bold text-secondary">0</div>
                        <div class="small text-muted mt-1" id="chKpiBajaSub">—</div>
                        <div class="mt-auto pt-2"><span class="badge rounded-pill bg-label-secondary text-secondary" id="chKpiBajasPctBadge">—</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <div class="d-flex align-items-start gap-1 mb-2">
                            <span class="badge rounded-pill bg-label-warning text-warning fw-bold py-2 px-2 flex-grow-1 text-center lh-sm min-w-0" style="font-size:.82rem;letter-spacing:.04em;line-height:1.2;white-space:normal">Estructura</span>
                            <button type="button" class="btn btn-link btn-sm text-muted p-0 lh-1 flex-shrink-0 align-self-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Conteos influenciados por el rango: se toman empleados activos al cierre del período seleccionado. Índice áreas: total áreas ÷ empleados activos (×100). Índice departamentos: total departamentos ÷ empleados activos (×100). Índice puestos: total puestos únicos ÷ empleados activos (×100)."
                                aria-label="Ayuda: índices áreas, departamentos y puestos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="ch-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div class="row g-1 flex-grow-1 align-items-center">
                            <div class="col-3 text-center border-end">
                                <div class="small text-muted mb-1 fw-semibold" style="font-size:.72rem;">Direcci&oacute;n</div>
                                <div id="chKpiDirecciones" class="fw-bold text-body" style="font-size:1.8rem;line-height:1;">0</div>
                            </div>
                            <div class="col-3 text-center border-end">
                                <div class="small text-muted mb-1 fw-semibold" style="font-size:.72rem;">Áreas</div>
                                <div id="chKpiAreas" class="fw-bold text-body" style="font-size:1.8rem;line-height:1;">0</div>
                            </div>
                            <div class="col-3 text-center border-end">
                                <div class="small text-muted mb-1 fw-semibold" style="font-size:.72rem;">Deptos</div>
                                <div id="chKpiDeptos" class="fw-bold text-body" style="font-size:1.8rem;line-height:1;">0</div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="small text-muted mb-1 fw-semibold" style="font-size:.72rem;">Puestos</div>
                                <div id="chKpiPuestos" class="fw-bold text-body" style="font-size:1.8rem;line-height:1;">0</div>
                            </div>
                        </div>
                        <div class="row gx-1 mt-auto pt-2">
                            <div class="col-3 text-center">
                                <span class="badge rounded-pill bg-label-secondary text-secondary" id="chKpiDireccionesPctBadge">â€”</span>
                            </div>
                            <div class="col-3 text-center">
                                <span class="badge rounded-pill bg-label-secondary text-secondary" id="chKpiAreasPctBadge">—</span>
                            </div>
                            <div class="col-3 text-center">
                                <span class="badge rounded-pill bg-label-secondary text-secondary" id="chKpiDeptosPctBadge">—</span>
                            </div>
                            <div class="col-3 text-center">
                                <span class="badge rounded-pill bg-label-secondary text-secondary" id="chKpiPuestosPctBadge">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 align-items-start">
            <div class="col-lg-8">
                <div id="chCardMovimientos" class="card shadow-sm mb-3">
                    <div class="card-header py-3 d-flex flex-wrap align-items-start gap-2">
                        <div class="d-flex flex-wrap align-items-baseline gap-2 flex-grow-1" style="min-width: 0;">
                            <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Movimientos del período</span>
                            <span id="chMovRangoInline" class="small text-muted">—</span>
                        </div>
                    </div>
                    <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <div class="ch-mov-card border rounded-3 bg-light user-select-none position-relative flex-fill text-center py-3 px-2" data-ch-mov-tipo="ingresos" role="button" tabindex="0" aria-expanded="false">
                            <div class="small text-muted mb-1">Ingresos</div>
                            <div id="chMovIngresos" class="fs-5 fw-bold lh-sm text-success">0</div>
                        </div>
                        <div class="ch-mov-card border rounded-3 bg-light user-select-none position-relative flex-fill text-center py-3 px-2" data-ch-mov-tipo="bajas" role="button" tabindex="0" aria-expanded="false">
                            <div class="small text-muted mb-1">Bajas</div>
                            <div id="chMovBajas" class="fs-5 fw-bold lh-sm text-danger">0</div>
                        </div>
                        <div class="ch-mov-card border rounded-3 bg-light user-select-none position-relative flex-fill text-center py-3 px-2" data-ch-mov-tipo="reingresos" role="button" tabindex="0" aria-expanded="false">
                            <div class="small text-muted mb-1">Reingresos</div>
                            <div id="chMovReingresos" class="fs-5 fw-bold lh-sm text-primary">0</div>
                        </div>
                    </div>
                    <p id="chMovClicAviso" class="small text-muted mt-3 mb-0">Tip: Haz clic en Ingresos, Bajas o Reingresos para abrir la gráfica por departamento.</p>
                    <div id="chMovResumenBarWrap" class="mt-3" style="min-height: 220px;">
                        <div id="chChartMovimientos"></div>
                    </div>
                    <div id="chMovDetalleWrap" class="d-none mt-3 pt-3 border-top">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div id="chMovDetalleTitulo" class="fw-bold text-body">—</div>
                                <div id="chMovDetalleSub" class="small text-muted mt-1"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="chMovDetalleCerrar">Cerrar</button>
                        </div>
                        <div id="chMovDetalleChartShell" class="position-relative rounded border bg-white" style="min-height: 260px;">
                            <div class="position-absolute top-0 end-0 p-2 pt-2 pe-2" style="z-index: 2;">
                                <div class="btn-group" role="group" aria-label="Tipo de gráfica por departamento">
                                    <input type="radio" class="btn-check" name="chMovDetChartTipo" id="chDetTipoPie" value="pie" checked autocomplete="off">
                                    <label class="btn btn-outline-secondary px-3 py-2" for="chDetTipoPie" title="Pastel"><span class="small lh-1"><i class="fa fa-pie-chart" aria-hidden="true"></i></span></label>
                                    <input type="radio" class="btn-check" name="chMovDetChartTipo" id="chDetTipoBar" value="bar" autocomplete="off">
                                    <label class="btn btn-outline-secondary px-3 py-2" for="chDetTipoBar" title="Barras"><span class="small lh-1"><i class="fa fa-bar-chart" aria-hidden="true"></i></span></label>
                                    <input type="radio" class="btn-check" name="chMovDetChartTipo" id="chDetTipoLine" value="line" autocomplete="off">
                                    <label class="btn btn-outline-secondary px-3 py-2" for="chDetTipoLine" title="Línea"><span class="small lh-1"><i class="fa fa-line-chart" aria-hidden="true"></i></span></label>
                                </div>
                            </div>
                            <div id="chChartMovDetalle" class="pt-1" style="min-height: 260px;"></div>
                        </div>
                    </div>
                    </div>
                </div>

                <h6 class="mb-2 mt-3" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Plantilla y estructura</h6>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-3 align-items-stretch">
                    <div class="col d-flex">
                        <div class="card h-100 shadow-sm position-relative w-100">
                            <div class="card-body py-3 d-flex flex-column">
                                <div class="mb-2">
                                    <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Distribución por sede</span>
                                </div>
                                <div id="chPlantillaSedeLista" class="small text-muted mb-2 flex-grow-1" style="white-space: pre-line;">—</div>
                                <div class="small text-muted">Total sedes con personal activo</div>
                                <div id="chPlantillaSedeTotal" class="fs-5 fw-bold lh-sm text-body pt-1 mt-auto">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col d-flex">
                        <div class="card h-100 shadow-sm position-relative w-100">
                            <div class="card-body py-3 d-flex flex-column">
                                <div class="mb-2">
                                    <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Antigüedad promedio</span>
                                </div>
                                <div class="flex-grow-1"></div>
                                <div id="chPlantillaAntig" class="fs-5 fw-bold lh-sm text-body mt-auto">—</div>
                                <div class="small text-muted mt-2">Años promedio en la empresa</div>
                            </div>
                        </div>
                    </div>
                    <div class="col d-flex">
                        <div class="card h-100 shadow-sm position-relative w-100">
                            <div class="card-body py-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-1">
                                    <div class="d-flex align-items-start gap-1 flex-wrap">
                                        <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Empleados nuevos (&lt; 90 días)</span>
                                        <button type="button" class="btn btn-link btn-sm text-muted p-0 lh-1 align-baseline text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                            title="Cuenta empleados con estatus Activo al cierre del período y cuya fecha de ingreso cae en los 90 días calendario que terminan el último día del período filtrado (fecha fin del filtro): desde (ese día − 89 días) hasta ese mismo día inclusive."
                                            aria-label="Ayuda: empleados nuevos 90 días">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <span id="chBadgeNuevos90" class="badge rounded-pill bg-label-warning text-warning">Recién ingresados</span>
                                </div>
                                <div class="flex-grow-1"></div>
                                <div id="chPlantillaNuevos90" class="fs-5 fw-bold lh-sm text-warning mt-auto">0</div>
                                <div class="small text-muted mt-2">Al cierre del período filtrado</div>
                            </div>
                        </div>
                    </div>
                    <div class="col d-flex">
                        <div class="card h-100 shadow-sm position-relative w-100">
                            <div class="card-body py-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-1">
                                    <div class="d-flex align-items-start gap-1 flex-wrap">
                                        <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Empleados bajas (&lt; 90 días)</span>
                                        <button type="button" class="btn btn-link btn-sm text-muted p-0 lh-1 align-baseline text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                            title="Cuenta registros en baja_persona cuya fecha de baja cae en los 90 días calendario que terminan el último día del período filtrado (fecha fin del filtro): desde (ese día − 89 días) hasta ese mismo día inclusive. Es la misma ventana que «Empleados Nuevos», pero sobre fecha de baja. El texto bajo el número solo indica que el indicador se refiere al cierre del período filtrado; aquí no se listan fechas."
                                            aria-label="Ayuda: empleados bajas 90 días">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <span id="chBadgeBajas90" class="badge rounded-pill bg-label-danger text-danger">Bajas recientes</span>
                                </div>
                                <div class="flex-grow-1"></div>
                                <div id="chPlantillaBajas90" class="fs-5 fw-bold lh-sm text-danger mt-auto">0</div>
                                <div class="small text-muted mt-2">Al cierre del período filtrado</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-3 d-none" id="chWrapCardGenero">
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 shadow-sm position-relative">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-start gap-1 flex-wrap">
                                        <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Distribución por género</span>
                                        <button type="button" class="btn btn-link btn-sm text-muted p-0 lh-1 align-baseline text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                            title="Conteo de empleados activos al cierre del periodo según el campo género o sexo en persona (si existe en la base de datos)."
                                            aria-label="Ayuda: distribución por género">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <span id="chBadgeGenPred" class="badge rounded-pill bg-label-secondary text-secondary">—</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="small text-muted">Hombres</span>
                                    <span id="chPlantillaGenH" class="fw-bold fs-5 text-body">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="small text-muted">Mujeres</span>
                                    <span id="chPlantillaGenM" class="fw-bold fs-5 text-success">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm mb-3">
                    <div class="card-header py-3 d-flex flex-wrap align-items-start gap-2">
                        <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Composición de plantilla</span>
                    </div>
                    <div class="card-body">
                        <div id="chChartPlantilla" style="min-height: 320px;"></div>
                    </div>
                </div>
                <div id="chColTiempoInduccion" class="d-none mb-3" style="max-width: 420px;">
                    <div class="card shadow-sm">
                        <div class="card-body py-3 position-relative">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Tiempo promedio de inducción</span>
                                <button type="button" class="btn btn-link btn-sm text-muted p-0 lh-1 align-baseline text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                    title="Solo si existe el campo fecha_fin_induccion en persona: promedio de días entre ingreso y fin de inducción, para activos que cerraron inducción con fecha de fin dentro del periodo seleccionado."
                                    aria-label="Ayuda: tiempo promedio de inducción">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div id="chOnbDiasProm" class="fs-4 fw-bold text-body">0</div>
                            <div class="small text-muted mt-2">Días promedio para completar inducción</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-flex flex-column gap-3">
                <div id="chCardRotacion" class="card shadow-sm d-flex flex-column">
                    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div class="d-flex flex-wrap align-items-baseline gap-2 flex-grow-1" style="min-width: 0;">
                            <span class="mb-0" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Rotación</span>
                            <span id="chRotRangoInline" class="small text-muted">—</span>
                        </div>
                        <span id="chBadgeRotacion" class="flex-shrink-0" role="button" tabindex="0" title="Clic para leer el detalle">—</span>
                    </div>
                    <div class="card-body d-flex flex-column flex-grow-1">
                    <div class="d-flex flex-column justify-content-center text-center pt-1">
                        <div id="chRotacionPct" class="fs-2 fw-bold text-success">0%</div>
                        <div class="small text-muted mt-2">(Bajas período / empleados activos) × 100</div>
                    </div>
                    <div class="mt-3 pt-3 border-top d-flex flex-column flex-grow-1 align-items-center">
                        <div class="text-center mb-2" style="font-size:.7rem;font-weight:600;letter-spacing:.02em;color:var(--bs-secondary-color)">Indicador de rotación</div>
                        <div id="chChartRotacion" style="min-height: 200px; max-width: 280px; margin: 0 auto;"></div>
                        <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-2 small text-muted">
                            <span><span class="rounded-circle bg-success d-inline-block align-middle me-1" style="width:8px;height:8px" aria-hidden="true"></span> Bajas período: <strong id="chRotLegendBajas">0</strong></span>
                            <span><span class="rounded-circle bg-secondary d-inline-block align-middle me-1" style="width:8px;height:8px" aria-hidden="true"></span> Empleados activos: <strong id="chRotLegendPlantilla">0</strong></span>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var chEstRangoIniDefault = <?php echo json_encode($chEstRangoIni, JSON_UNESCAPED_UNICODE); ?>;
    var chEstRangoFinDefault = <?php echo json_encode($chEstRangoFin, JSON_UNESCAPED_UNICODE); ?>;
    var chEstRango = { inicio: chEstRangoIniDefault, fin: chEstRangoFinDefault };
    var chEstFiltroEstructura = { id_direccion: '', id_area: '', id_departamento: '', id_puesto: '' };
    var datosIni = <?php echo $datosInicialesJson; ?>;

    var ST_BADGE_VERDE = 'background: #d4f5e7; color: #0d5c3a; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;';
    var ST_BADGE_AMARILLO = 'background: #fef3cd; color: #7a5000; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;';
    var ST_BADGE_ROJO = 'background: #fde8e8; color: #7a1111; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;';
    var ST_BADGE_NAVY = 'background: #1a3a5c; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;';

    var ST_ROT_OK = 'font-size: 28px; font-weight: 800; color: #2ecc8b;';
    var ST_ROT_BAD = 'font-size: 28px; font-weight: 800; color: #e74c3c;';

    var chCharts = { mov: null, plantilla: null, rotacion: null, movDetalle: null };
    /** Tipo de gráfica del panel «por departamento» (solo con Ingresos/Bajas/Reingresos abiertos): bar | pie | line */
    var chMovDetalleChartTipo = 'pie';
    /** Últimas filas por departamento mostradas; permite cambiar tipo sin nueva petición */
    var chMovDetalleLastRows = [];
    /** Evita redibujar al cambiar tipo antes de que llegue el desglose (o con datos del panel anterior). */
    var chMovDetalleListo = false;
    var chMovTipoAbierto = null;
    var chMovDetalleReqSeq = 0;
    /** Paleta cíclica para sectores del pie de departamentos */
    var CH_DEPTO_COLORS = [
        '#1a3a5c', '#2ecc8b', '#3498db', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22',
        '#34495e', '#16a085', '#27ae60', '#2980b9', '#8e44ad', '#c0392b', '#d35400', '#7f8c8d',
        '#00b894', '#0984e3', '#6c5ce7', '#fdcb6e', '#e17055', '#636e72', '#00cec9', '#a29bfe', '#fab1a0'
    ];
    var chReqSeq = 0;
    var chLoadingOpen = false;
    var chApexLoading = false;
    var chLastDataForCharts = null;
    var chRotacionSyncResizeT = null;

    function chEstFormatoYmd(fecha) {
        var y = fecha.getFullYear();
        var mo = String(fecha.getMonth() + 1).padStart(2, '0');
        var da = String(fecha.getDate()).padStart(2, '0');
        return y + '-' + mo + '-' + da;
    }

    function chEstPayloadFiltro() {
        var p = (chEstRango.inicio || '').split('-');
        var y = parseInt(p[0], 10);
        var m = parseInt(p[1], 10);
        if (isNaN(y)) y = new Date().getFullYear();
        if (isNaN(m)) m = 1;
        return {
            fecha_inicio: chEstRango.inicio,
            fecha_fin: chEstRango.fin,
            anio: y,
            mes: m,
            semana: 0,
            id_direccion: chEstFiltroEstructura.id_direccion ? parseInt(chEstFiltroEstructura.id_direccion, 10) : null,
            id_area: chEstFiltroEstructura.id_area ? parseInt(chEstFiltroEstructura.id_area, 10) : null,
            id_departamento: chEstFiltroEstructura.id_departamento ? parseInt(chEstFiltroEstructura.id_departamento, 10) : null,
            id_puesto: chEstFiltroEstructura.id_puesto ? parseInt(chEstFiltroEstructura.id_puesto, 10) : null
        };
    }

    function chEstCerrarFlatpickrCalendario(fpInstance) {
        var fp = fpInstance;
        if (!fp) {
            var elFp = document.getElementById('flatpickr-range-ch-est');
            fp = elFp && elFp._flatpickr ? elFp._flatpickr : null;
        }
        if (!fp) return;
        try {
            if (typeof fp.close === 'function') {
                fp.close();
            }
        } catch (e1) { /* ignorar */ }
        var inp = document.getElementById('flatpickr-range-ch-est');
        if (inp) {
            try {
                inp.blur();
            } catch (e2) { /* ignorar */ }
        }
    }

    function chEstAplicarRangoYRefrescar(iniYmd, finYmd, fpInstance) {
        chEstRango.inicio = iniYmd;
        chEstRango.fin = finYmd;
        if (fpInstance) {
            try {
                var a = new Date(iniYmd + 'T12:00:00');
                var b = new Date(finYmd + 'T12:00:00');
                fpInstance.setDate([a, b], false);
            } catch (eSd) { /* ignorar */ }
        }
        chEstCerrarFlatpickrCalendario(fpInstance || null);
        refrescar();
    }

    /** Lunes de la semana calendario local → hoy (misma regla que el servidor). */
    function chEstRangoLunesHoy() {
        var hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        var dow = hoy.getDay();
        var diffToMon = dow === 0 ? -6 : 1 - dow;
        var lun = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() + diffToMon);
        lun.setHours(0, 0, 0, 0);
        return { ini: chEstFormatoYmd(lun), fin: chEstFormatoYmd(hoy) };
    }

    function chEstRestaurarPeriodoPorDefecto() {
        var rh = chEstRangoLunesHoy();
        var el = document.getElementById('flatpickr-range-ch-est');
        var fp = el && el._flatpickr ? el._flatpickr : null;
        chEstAplicarRangoYRefrescar(rh.ini, rh.fin, fp);
    }

    /**
     * El HTML de esta vista va en el cuerpo ANTES de los vendor JS del layout; aquí flatpickr
     * aún no existe cuando corre el IIFE. Se programa la init tras DOMContentLoaded / polling.
     */
    function initFlatpickrChEst() {
        var el = document.getElementById('flatpickr-range-ch-est');
        if (!el || el._flatpickr || typeof flatpickr === 'undefined') {
            return;
        }
        var hoyMax = new Date();
        hoyMax.setHours(23, 59, 59, 999);
        flatpickr(el, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            clickOpens: true,
            allowInput: false,
            maxDate: hoyMax,
            disableMobile: true,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                },
                rangeSeparator: ' a '
            },
            defaultDate: [chEstRango.inicio, chEstRango.fin],
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    chEstRango.inicio = chEstFormatoYmd(selectedDates[0]);
                    chEstRango.fin = chEstFormatoYmd(selectedDates[1]);
                    chEstCerrarFlatpickrCalendario(instance);
                    /** Deferir refresco: deja que Flatpickr cierre el DOM antes de Swal/fetch (evita calendario “atascado” abierto). */
                    setTimeout(function () {
                        chEstCerrarFlatpickrCalendario(null);
                        refrescar();
                    }, 0);
                } else if (selectedDates.length === 0) {
                    chEstRestaurarPeriodoPorDefecto();
                }
            },
            onClose: function () {
                chEstCerrarFlatpickrCalendario(null);
            }
        });
    }

    function scheduleInitFlatpickrChEst() {
        var n = 0;
        function intentar() {
            if (typeof flatpickr !== 'undefined') {
                initFlatpickrChEst();
                return;
            }
            n += 1;
            if (n > 100) {
                return;
            }
            setTimeout(intentar, 40);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', intentar);
        } else {
            intentar();
        }
    }

    function setText(id, txt) {
        var el = document.getElementById(id);
        if (el) el.textContent = txt;
    }

    function chEstSetSelectOptions(selectId, rows, placeholder) {
        var el = document.getElementById(selectId);
        if (!el) return;
        var html = '<option value="">' + (placeholder || 'Todos') + '</option>';
        (rows || []).forEach(function (r) {
            var id = (r && r.id != null) ? String(r.id) : '';
            var nombre = (r && r.nombre != null) ? String(r.nombre) : '';
            if (!id) return;
            html += '<option value="' + chEscHtml(id) + '">' + chEscHtml(nombre || ('#' + id)) + '</option>';
        });
        el.innerHTML = html;
    }

    function chEstToggleFiltroWrap(id, show) {
        var el = document.getElementById(id);
        if (!el) return;
        if (show) el.classList.remove('d-none');
        else el.classList.add('d-none');
    }

    function chEstCargarFiltrosEstructura(opts) {
        opts = opts || {};
        var payload = {
            id_area: opts.id_area != null && opts.id_area !== '' ? parseInt(String(opts.id_area), 10) : null,
            id_departamento: opts.id_departamento != null && opts.id_departamento !== '' ? parseInt(String(opts.id_departamento), 10) : null
        };
        return fetch('/caphum/getEstadisticasFiltrosEstructura', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify(payload)
        })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                if (!resp || !resp.success || !resp.datos) return;
                var data = resp.datos;
                chEstSetSelectOptions('chEstFiltroArea', data.areas || [], 'Todas las áreas');
                if (payload.id_area) {
                    chEstSetSelectOptions('chEstFiltroDepartamento', data.departamentos || [], 'Todos los departamentos');
                    chEstToggleFiltroWrap('chEstWrapFiltroDepto', true);
                } else {
                    chEstSetSelectOptions('chEstFiltroDepartamento', [], 'Todos los departamentos');
                    chEstToggleFiltroWrap('chEstWrapFiltroDepto', false);
                }
                if (payload.id_departamento) {
                    chEstSetSelectOptions('chEstFiltroPuesto', data.puestos || [], 'Todos los puestos');
                    chEstToggleFiltroWrap('chEstWrapFiltroPuesto', true);
                } else {
                    chEstSetSelectOptions('chEstFiltroPuesto', [], 'Todos los puestos');
                    chEstToggleFiltroWrap('chEstWrapFiltroPuesto', false);
                }

                var selArea = document.getElementById('chEstFiltroArea');
                var selDep = document.getElementById('chEstFiltroDepartamento');
                var selPue = document.getElementById('chEstFiltroPuesto');
                if (selArea) selArea.value = chEstFiltroEstructura.id_area || '';
                if (selDep) selDep.value = chEstFiltroEstructura.id_departamento || '';
                if (selPue) selPue.value = chEstFiltroEstructura.id_puesto || '';
            })
            .catch(function () { /* ignorar errores de carga de filtros */ });
    }

    /** Evita mostrar fechas/cifras de un filtro anterior mientras llega la respuesta o si falla la petición. */
    function chEstCargarFiltrosEstructura(opts) {
        opts = opts || {};
        var payload = {
            id_direccion: opts.id_direccion != null && opts.id_direccion !== '' ? parseInt(String(opts.id_direccion), 10) : null,
            id_area: opts.id_area != null && opts.id_area !== '' ? parseInt(String(opts.id_area), 10) : null,
            id_departamento: opts.id_departamento != null && opts.id_departamento !== '' ? parseInt(String(opts.id_departamento), 10) : null
        };
        return fetch('/caphum/getEstadisticasFiltrosEstructura', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify(payload)
        })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                if (!resp || !resp.success || !resp.datos) return;
                var data = resp.datos;
                chEstSetSelectOptions('chEstFiltroDireccion', data.direcciones || [], 'Todas las direcciones');
                chEstSetSelectOptions('chEstFiltroArea', data.areas || [], 'Todas las áreas');
                var selDir = document.getElementById('chEstFiltroDireccion');
                var selArea = document.getElementById('chEstFiltroArea');
                var selDep = document.getElementById('chEstFiltroDepartamento');
                var selPue = document.getElementById('chEstFiltroPuesto');
                chEstToggleFiltroWrap('chEstWrapFiltroArea', !!payload.id_direccion);
                chEstToggleFiltroWrap('chEstWrapFiltroDepto', !!payload.id_area);
                chEstToggleFiltroWrap('chEstWrapFiltroPuesto', !!payload.id_departamento);
                if (selArea) selArea.disabled = !payload.id_direccion;
                if (payload.id_area) {
                    chEstSetSelectOptions('chEstFiltroDepartamento', data.departamentos || [], 'Todos los departamentos');
                    if (selDep) selDep.disabled = false;
                } else {
                    chEstSetSelectOptions('chEstFiltroDepartamento', [], 'Todos los departamentos');
                    if (selDep) selDep.disabled = true;
                }
                if (payload.id_departamento) {
                    chEstSetSelectOptions('chEstFiltroPuesto', data.puestos || [], 'Todos los puestos');
                    if (selPue) selPue.disabled = false;
                } else {
                    chEstSetSelectOptions('chEstFiltroPuesto', [], 'Todos los puestos');
                    if (selPue) selPue.disabled = true;
                }
                if (selDir) selDir.value = chEstFiltroEstructura.id_direccion || '';
                if (selArea) selArea.value = chEstFiltroEstructura.id_area || '';
                if (selDep) selDep.value = chEstFiltroEstructura.id_departamento || '';
                if (selPue) selPue.value = chEstFiltroEstructura.id_puesto || '';
            })
            .catch(function () { /* ignorar errores de carga de filtros */ });
    }

    function marcarTextosContextoFiltroPendiente(mensaje) {
        var m = mensaje || 'Actualizando según el filtro…';
        setText('chMovClicAviso', m);
    }

    function badgeStyleFromClass(cls) {
        var c = (cls || '').toLowerCase();
        if (c.indexOf('danger') !== -1) return ST_BADGE_ROJO;
        if (c.indexOf('warning') !== -1) return ST_BADGE_AMARILLO;
        if (c.indexOf('success') !== -1 || c.indexOf('secondary') !== -1) return ST_BADGE_VERDE;
        if (c.indexOf('info') !== -1) return ST_BADGE_AMARILLO;
        return ST_BADGE_VERDE;
    }

    function setBadge(id, text, cls) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = text;
        el.setAttribute('style', badgeStyleFromClass(cls));
    }

    function chEscHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function chRotacionSwalIcon(msg) {
        var head = String(msg).split(/\n\n+/)[0].trim().toLowerCase();
        if (head === 'controlada') {
            return 'success';
        }
        if (head === 'moderada') {
            return 'warning';
        }
        if (head === 'elevada') {
            return 'error';
        }
        return 'info';
    }

    function chRotacionAyudaMostrar() {
        var card = document.getElementById('chCardRotacion');
        var msg = card && card.getAttribute('data-rotacion-ayuda');
        if (!msg || !String(msg).trim()) {
            return;
        }
        if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
            var parts = String(msg).split(/\n\n+/);
            var htmlBody;
            if (parts.length >= 2) {
                htmlBody = '<div class="text-start"><div class="fw-bold text-body mb-2 fs-6">' + chEscHtml(parts[0].trim()) + '</div>'
                    + '<div class="small text-body" style="line-height:1.5">' + chEscHtml(parts.slice(1).join('\n\n').trim()) + '</div></div>';
            } else {
                htmlBody = '<div class="text-start small" style="white-space:pre-wrap;line-height:1.5">' + chEscHtml(msg) + '</div>';
            }
            Swal.fire({
                icon: chRotacionSwalIcon(msg),
                html: htmlBody,
                showConfirmButton: true,
                confirmButtonText: 'Entendido',
                width: '34rem'
            });
        } else {
            window.alert(String(msg));
        }
    }

    function setRotacionPctStyle(pct) {
        var el = document.getElementById('chRotacionPct');
        if (!el) return;
        var n = parseFloat(String(pct).replace(',', '.'));
        if (isNaN(n)) n = 0;
        el.setAttribute('style', n > 5 ? ST_ROT_BAD : ST_ROT_OK);
    }

    function setColVisible(id, show) {
        var el = document.getElementById(id);
        if (!el) return;
        if (show) el.classList.remove('d-none');
        else el.classList.add('d-none');
    }

    function n(v) {
        var x = parseFloat(String(v == null ? 0 : v).replace(',', '.'));
        return isNaN(x) ? 0 : x;
    }

    /** Iguala la altura del card Rotación a la del card Movimientos (solo ≥ lg). */
    function syncChRotacionCardMinHeight() {
        var mov = document.getElementById('chCardMovimientos');
        var rot = document.getElementById('chCardRotacion');
        if (!mov || !rot) return;
        var lg = typeof window.matchMedia === 'function' && window.matchMedia('(min-width: 992px)').matches;
        if (!lg) {
            rot.style.minHeight = '';
            if (chCharts.rotacion && typeof chCharts.rotacion.resize === 'function') {
                try { chCharts.rotacion.resize(); } catch (eRz) { /* ignorar */ }
            }
            return;
        }
        var h = mov.getBoundingClientRect().height;
        if (h > 0) {
            rot.style.minHeight = Math.round(h) + 'px';
        }
        if (chCharts.rotacion && typeof chCharts.rotacion.resize === 'function') {
            try { chCharts.rotacion.resize(); } catch (eRz2) { /* ignorar */ }
        }
    }

    function scheduleSyncChRotacionCardMinHeight() {
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                syncChRotacionCardMinHeight();
            });
        });
    }

    /** Apex pie: durante updateSeries/updateOptions `globals.series` o `config.series` pueden ser undefined un instante. */
    function chApexPieLegendValor(opts) {
        try {
            if (opts == null || opts.w == null) return '';
            var idx = typeof opts.seriesIndex === 'number' ? opts.seriesIndex : parseInt(String(opts.seriesIndex), 10);
            if (!isFinite(idx) || idx < 0) return '';
            var g = opts.w.globals;
            if (!g || g.series == null) return '';
            var ser = g.series;
            if (!Array.isArray(ser)) return '';
            var v = ser[idx];
            if (v !== undefined && v !== null) return v;
            if (Array.isArray(ser[0])) {
                var inner = ser[0];
                return (inner && inner[idx] != null) ? inner[idx] : '';
            }
            return '';
        } catch (eL) {
            return '';
        }
    }

    function chApexPieDataLabelValor(val, opts) {
        try {
            if (opts == null || opts.w == null) return val != null && val !== '' ? String(val) : '';
            var idx = typeof opts.seriesIndex === 'number' ? opts.seriesIndex : parseInt(String(opts.seriesIndex), 10);
            if (!isFinite(idx) || idx < 0) {
                return val != null && val !== '' ? String(val) : '';
            }
            var cfg = opts.w.config;
            if (!cfg || cfg.series == null) return val != null && val !== '' ? String(val) : '';
            var s = cfg.series;
            if (Array.isArray(s) && s[idx] !== undefined && s[idx] !== null) {
                return String(s[idx]);
            }
            return val != null && val !== '' ? String(val) : '';
        } catch (eD) {
            return val != null && val !== '' ? String(val) : '';
        }
    }

    function ensureApexReady() {
        if (typeof ApexCharts !== 'undefined') return true;
        if (chApexLoading) return false;
        chApexLoading = true;
        var s = document.createElement('script');
        s.src = '/assets/vendor/libs/apex-charts/apexcharts.js';
        s.onload = function () {
            chApexLoading = false;
            if (chLastDataForCharts) renderCharts(chLastDataForCharts);
        };
        s.onerror = function () { chApexLoading = false; };
        document.head.appendChild(s);
        return false;
    }

    function renderCharts(d) {
        chLastDataForCharts = d;
        if (!ensureApexReady()) return;

        var movSeries = [n(d.ingresos), n(d.bajas), n(d.reingresos)];
        var movCats = ['Ingresos', 'Bajas', 'Reingresos'];
        var movColors = ['#2ecc8b', '#e74c3c', '#3498db'];
        var rotPct = Math.min(100, Math.max(0, n(d.rotacion_pct)));
        var rotColor = rotPct > 5 ? '#e74c3c' : '#2ecc8b';

        var tipoResumenActual = null;
        try {
            if (chCharts.mov && chCharts.mov.w && chCharts.mov.w.config && chCharts.mov.w.config.chart) {
                tipoResumenActual = chCharts.mov.w.config.chart.type;
            }
        } catch (eTipo) {
            tipoResumenActual = null;
        }
        if (chCharts.mov && tipoResumenActual !== 'bar') {
            destruirChartMovimientosResumen();
        }

        var elMov = document.querySelector('#chChartMovimientos');
        var optMovBar = {
            chart: { type: 'bar', height: 250, toolbar: { show: false } },
            series: [{ name: 'Personas', data: movSeries }],
            xaxis: { categories: movCats },
            colors: movColors,
            dataLabels: { enabled: false },
            plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
            grid: { borderColor: '#eef1f5' },
            tooltip: { y: { formatter: function (val) { return val + ' personas'; } } }
        };
        if (elMov) {
            try {
                if (chCharts.mov) {
                    chCharts.mov.destroy();
                    chCharts.mov = null;
                }
            } catch (eMovD) {
                chCharts.mov = null;
            }
            chCharts.mov = new ApexCharts(elMov, optMovBar);
            chCharts.mov.render();
        }

        var deptoRows = Array.isArray(d.plantilla_por_departamento) ? d.plantilla_por_departamento : [];
        var pieLabels = [];
        var pieSeries = [];
        for (var di = 0; di < deptoRows.length; di++) {
            pieLabels.push(deptoRows[di].nombre || '—');
            pieSeries.push(n(deptoRows[di].cnt));
        }
        if (!pieLabels.length) {
            pieLabels = ['Sin datos'];
            pieSeries = [1];
        }
        var pieColors = [];
        for (var ci = 0; ci < pieLabels.length; ci++) {
            pieColors.push(CH_DEPTO_COLORS[ci % CH_DEPTO_COLORS.length]);
        }
        if (pieLabels.length === 1 && pieLabels[0] === 'Sin datos') {
            pieColors = ['#dde3ec'];
        }

        var plantChartType = null;
        try {
            if (chCharts.plantilla && chCharts.plantilla.w && chCharts.plantilla.w.config && chCharts.plantilla.w.config.chart) {
                plantChartType = chCharts.plantilla.w.config.chart.type;
            }
        } catch (ePt) { plantChartType = null; }
        if (chCharts.plantilla && plantChartType !== 'pie') {
            try { chCharts.plantilla.destroy(); } catch (ePd) {}
            chCharts.plantilla = null;
        }

        var pieCommon = {
            chart: { type: 'pie', height: 300, toolbar: { show: false }, animations: { speed: 380 } },
            labels: pieLabels,
            colors: pieColors,
            legend: {
                position: 'bottom',
                fontSize: '11px',
                horizontalAlign: 'center',
                itemMargin: { horizontal: 8, vertical: 3 },
                onItemClick: { toggleDataSeries: false },
                onItemHover: { highlightDataSeries: true },
                formatter: function (seriesName, opts) {
                    var val = chApexPieLegendValor(opts);
                    return seriesName + ': ' + val;
                }
            },
            plotOptions: {
                pie: {
                    expandOnClick: true
                }
            },
            dataLabels: {
                enabled: pieLabels.length <= 14 && !(pieLabels.length === 1 && pieLabels[0] === 'Sin datos'),
                formatter: function (val, opts) {
                    return chApexPieDataLabelValor(val, opts);
                },
                style: { fontSize: '11px', fontWeight: 600 }
            },
            tooltip: {
                y: { formatter: function (val) { return val + ' personas'; } }
            },
            stroke: { width: 1, colors: ['#ffffff'] }
        };

        var elPlantilla = document.querySelector('#chChartPlantilla');
        if (elPlantilla) {
            try {
                if (chCharts.plantilla) {
                    chCharts.plantilla.destroy();
                    chCharts.plantilla = null;
                }
            } catch (ePlD) {
                chCharts.plantilla = null;
            }
            chCharts.plantilla = new ApexCharts(elPlantilla, Object.assign({}, pieCommon, {
                series: pieSeries
            }));
            chCharts.plantilla.render();
        }

        var elRot = document.querySelector('#chChartRotacion');
        if (elRot) {
            try {
                if (chCharts.rotacion) {
                    chCharts.rotacion.destroy();
                    chCharts.rotacion = null;
                }
            } catch (eRoD) {
                chCharts.rotacion = null;
            }
            chCharts.rotacion = new ApexCharts(elRot, {
                chart: { type: 'radialBar', height: 200, toolbar: { show: false }, animations: { speed: 400 } },
                series: [rotPct],
                labels: ['Rotación'],
                colors: [rotColor],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '62%', background: 'transparent' },
                        track: { background: '#e9ecef', strokeWidth: '100%' },
                        dataLabels: {
                            name: {
                                show: true,
                                fontSize: '11px',
                                fontWeight: 600,
                                color: '#6b7a90',
                                offsetY: 14
                            },
                            value: {
                                show: true,
                                fontSize: '24px',
                                fontWeight: 800,
                                color: '#1a3a5c',
                                offsetY: -10,
                                formatter: function (v) {
                                    var x = parseFloat(String(v).replace(',', '.'));
                                    if (isNaN(x)) x = 0;
                                    return Math.round(x) + '%';
                                }
                            },
                            total: { show: false }
                        }
                    }
                },
                stroke: { lineCap: 'round' }
            });
            chCharts.rotacion.render();
        }
        scheduleSyncChRotacionCardMinHeight();
    }

    function destruirMovDetalleChart() {
        if (chCharts.movDetalle) {
            try { chCharts.movDetalle.destroy(); } catch (eM) {}
            chCharts.movDetalle = null;
        }
    }

    function destruirChartMovimientosResumen() {
        if (chCharts.mov) {
            try { chCharts.mov.destroy(); } catch (eMr) {}
            chCharts.mov = null;
        }
    }

    function setMovResumenBarVisible(show) {
        var w = document.getElementById('chMovResumenBarWrap');
        if (!w) return;
        if (show) {
            w.classList.remove('d-none');
            if (chCharts.mov && typeof chCharts.mov.resize === 'function') {
                requestAnimationFrame(function () {
                    try { chCharts.mov.resize(); } catch (eR) { /* ignorar */ }
                    scheduleSyncChRotacionCardMinHeight();
                });
            } else {
                scheduleSyncChRotacionCardMinHeight();
            }
        } else {
            w.classList.add('d-none');
            scheduleSyncChRotacionCardMinHeight();
        }
    }

    function cerrarMovDetallePanel() {
        chMovTipoAbierto = null;
        chMovDetalleListo = false;
        var wrap = document.getElementById('chMovDetalleWrap');
        if (wrap) wrap.classList.add('d-none');
        document.querySelectorAll('.ch-mov-card').forEach(function (el) {
            el.classList.remove('border-success', 'border-2', 'shadow-sm');
            el.setAttribute('aria-expanded', 'false');
        });
        destruirMovDetalleChart();
        setMovResumenBarVisible(true);
        scheduleSyncChRotacionCardMinHeight();
    }

    function actualizarSeleccionMovCards(tipoActivo) {
        document.querySelectorAll('.ch-mov-card').forEach(function (el) {
            var t = el.getAttribute('data-ch-mov-tipo');
            if (t === tipoActivo) {
                el.classList.add('border-success', 'border-2', 'shadow-sm');
                el.setAttribute('aria-expanded', 'true');
            } else {
                el.classList.remove('border-success', 'border-2', 'shadow-sm');
                el.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function syncMovDetalleTipoRadios() {
        var t = chMovDetalleChartTipo || 'pie';
        document.querySelectorAll('input[name="chMovDetChartTipo"]').forEach(function (r) {
            r.checked = (r.value === t);
        });
    }

    function renderMovDetalleChart(rows) {
        chMovDetalleLastRows = Array.isArray(rows) ? rows.slice() : [];
        if (!ensureApexReady()) return;
        destruirMovDetalleChart();
        var el = document.querySelector('#chChartMovDetalle');
        if (!el) return;
        var labels = [];
        var series = [];
        for (var i = 0; i < chMovDetalleLastRows.length; i++) {
            labels.push(chMovDetalleLastRows[i].nombre || '—');
            series.push(n(chMovDetalleLastRows[i].cnt));
        }
        if (!labels.length) {
            labels = ['Sin datos'];
            series = [1];
        }
        var pieColors = [];
        for (var c = 0; c < labels.length; c++) {
            pieColors.push(CH_DEPTO_COLORS[c % CH_DEPTO_COLORS.length]);
        }
        if (labels.length === 1 && labels[0] === 'Sin datos') {
            pieColors = ['#dde3ec'];
        }
        var tipo = chMovDetalleChartTipo || 'pie';
        var opt;

        if (tipo === 'pie') {
            opt = {
                chart: { type: 'pie', height: 280, toolbar: { show: false }, animations: { speed: 360 } },
                series: series,
                labels: labels,
                colors: pieColors,
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    horizontalAlign: 'center',
                    itemMargin: { horizontal: 6, vertical: 2 },
                    onItemClick: { toggleDataSeries: false },
                    onItemHover: { highlightDataSeries: true },
                    formatter: function (seriesName, opts) {
                        return seriesName + ': ' + chApexPieLegendValor(opts);
                    }
                },
                plotOptions: { pie: { expandOnClick: true } },
                dataLabels: {
                    enabled: labels.length <= 12 && !(labels.length === 1 && labels[0] === 'Sin datos'),
                    formatter: function (val, opts) {
                        return chApexPieDataLabelValor(val, opts);
                    },
                    style: { fontSize: '11px', fontWeight: 600 }
                },
                tooltip: { y: { formatter: function (val) { return val + ' personas'; } } },
                stroke: { width: 1, colors: ['#ffffff'] }
            };
        } else if (tipo === 'line') {
            /** Línea garantizada + puntos multicolor: serie line base + overlays scatter por punto (evita fallos de markers.discrete en esta versión de Apex). */
            var markerColors = pieColors.slice(0, series.length);
            while (markerColors.length < series.length) {
                markerColors.push(CH_DEPTO_COLORS[markerColors.length % CH_DEPTO_COLORS.length]);
            }
            var lineStroke = '#5a6a7d';
            var mixSeries = [{ name: 'Personas', type: 'line', data: series }];
            var mixColors = [lineStroke];
            var strokeWidths = [4];
            var markerSizes = [0];
            for (var si = 0; si < series.length; si++) {
                var sData = [];
                for (var sj = 0; sj < series.length; sj++) {
                    sData.push(sj === si ? series[sj] : null);
                }
                mixSeries.push({ name: 'p' + String(si), type: 'scatter', data: sData });
                mixColors.push(markerColors[si]);
                strokeWidths.push(0);
                markerSizes.push(7);
            }
            opt = {
                chart: { type: 'line', height: 290, toolbar: { show: false }, zoom: { enabled: false } },
                series: mixSeries,
                xaxis: {
                    categories: labels,
                    labels: {
                        style: { fontSize: '10px' },
                        rotate: labels.length > 6 ? -35 : 0,
                        rotateAlways: labels.length > 6
                    }
                },
                yaxis: { min: 0 },
                colors: mixColors,
                stroke: {
                    curve: 'straight',
                    width: strokeWidths,
                    lineCap: 'round',
                    lineJoin: 'round'
                },
                legend: { show: false },
                markers: {
                    size: markerSizes,
                    strokeWidth: 2,
                    strokeColors: '#ffffff',
                    hover: { size: 9 }
                },
                dataLabels: { enabled: false },
                grid: { borderColor: '#eef1f5', padding: { top: 8, right: 12 } },
                tooltip: {
                    shared: false,
                    intersect: true,
                    y: {
                        formatter: function (val, opts) {
                            if (val == null || val === '') return '';
                            var i = opts && opts.dataPointIndex != null ? opts.dataPointIndex : -1;
                            if (i < 0 || i >= labels.length) {
                                return String(val) + ' personas';
                            }
                            return val + ' personas' + (labels[i] != null ? ' · ' + labels[i] : '');
                        }
                    }
                }
            };
        } else {
            var hBar = Math.min(520, Math.max(260, labels.length * 28));
            opt = {
                chart: { type: 'bar', height: hBar, toolbar: { show: false } },
                series: [{ name: 'Personas', data: series }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        distributed: true,
                        barHeight: '72%',
                        dataLabels: { position: 'end' }
                    }
                },
                colors: pieColors,
                dataLabels: {
                    enabled: labels.length <= 14 && !(labels.length === 1 && labels[0] === 'Sin datos'),
                    formatter: function (val) { return String(Math.round(val)); },
                    textAnchor: 'start',
                    offsetX: 24,
                    style: { fontSize: '11px', fontWeight: 700, colors: ['#2f3b52'] },
                    dropShadow: { enabled: false }
                },
                xaxis: {
                    categories: labels,
                    labels: { style: { fontSize: '10px' } }
                },
                yaxis: {
                    labels: {
                        maxWidth: 200,
                        style: { fontSize: '10px' }
                    }
                },
                grid: { borderColor: '#eef1f5', padding: { top: 4, right: 62 } },
                tooltip: { y: { formatter: function (val) { return val + ' personas'; } } }
            };
        }

        chCharts.movDetalle = new ApexCharts(el, opt);
        chCharts.movDetalle.render();
        chMovDetalleListo = true;
        scheduleSyncChRotacionCardMinHeight();
    }

    function solicitarMovDetalle(tipo) {
        if (tipo === chMovTipoAbierto) {
            cerrarMovDetallePanel();
            return;
        }
        chMovTipoAbierto = tipo;
        chMovDetalleListo = false;
        chMovDetalleLastRows = [];
        destruirMovDetalleChart();
        actualizarSeleccionMovCards(tipo);
        setMovResumenBarVisible(false);
        var wrap = document.getElementById('chMovDetalleWrap');
        if (wrap) wrap.classList.remove('d-none');
        scheduleSyncChRotacionCardMinHeight();
        syncMovDetalleTipoRadios();
        var titulos = {
            ingresos: 'Ingresos del período — por departamento',
            bajas: 'Bajas del período — por departamento',
            reingresos: 'Reingresos del período — por departamento'
        };
        setText('chMovDetalleTitulo', (titulos[tipo] || tipo) + ' (cargando…)');
        setText('chMovDetalleSub', '');
        var reqId = ++chMovDetalleReqSeq;
        var filtro = chEstPayloadFiltro();
        filtro.tipo = tipo;
        fetch('/caphum/getEstadisticasMovimientoDetalle', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify(filtro)
        })
            .then(function (r) {
                return r.text().then(function (txt) {
                    var j = null;
                    try { j = JSON.parse(txt); } catch (e2) { j = null; }
                    return { ok: r.ok, json: j };
                });
            })
            .then(function (wrap) {
                if (reqId !== chMovDetalleReqSeq || tipo !== chMovTipoAbierto) return;
                var resp = wrap.json;
                if (!wrap.ok || !resp || !resp.success || !resp.datos) {
                    setText('chMovDetalleTitulo', titulos[tipo] || tipo);
                    if (resp && resp.mensaje) {
                        setText('chMovDetalleSub', String(resp.mensaje) + (resp.error ? ' — ' + String(resp.error) : ''));
                    } else {
                        setText('chMovDetalleSub', '');
                    }
                    renderMovDetalleChart([]);
                    return;
                }
                var dat = resp.datos;
                var rng = (dat.fecha_ini && dat.fecha_fin) ? (String(dat.fecha_ini) + ' → ' + String(dat.fecha_fin)) : '';
                var suf = (dat.total != null ? ' · Total: ' + String(dat.total) : '') + (rng ? ' · ' + rng : '');
                setText('chMovDetalleTitulo', (titulos[tipo] || tipo) + suf);
                setText('chMovDetalleSub', '');
                renderMovDetalleChart(dat.por_departamento || []);
            })
            .catch(function () {
                if (reqId !== chMovDetalleReqSeq || tipo !== chMovTipoAbierto) return;
                setText('chMovDetalleTitulo', titulos[tipo] || tipo);
                setText('chMovDetalleSub', 'Error de red al cargar el desglose.');
                renderMovDetalleChart([]);
            });
    }

    function showLoadingAviso() {
        if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
            chLoadingOpen = true;
            Swal.fire({
                title: 'Cargando datos',
                text: 'Actualizando estadísticas del período seleccionado...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { if (Swal.showLoading) Swal.showLoading(); }
            });
            return;
        }
        setText('chEstRangoFechas', 'Actualizando estadísticas…');
    }

    function hideLoadingAviso() {
        var swalEstabaAbierto = chLoadingOpen;
        if (chLoadingOpen && typeof Swal !== 'undefined' && Swal && typeof Swal.close === 'function') {
            Swal.close();
            chLoadingOpen = false;
        }
        if (swalEstabaAbierto) {
            setTimeout(function () {
                chEstCerrarFlatpickrCalendario(null);
            }, 0);
        }
    }

    /** Rango de fechas compacto para badges (misma idea que Gastos Cobranza). */
    function chPeriodoBadgeText(d) {
        if (!d || !d.fecha_ini || !d.fecha_fin) return '—';
        var a = String(d.fecha_ini).replace(/T.*/, '').slice(0, 10);
        var b = String(d.fecha_fin).replace(/T.*/, '').slice(0, 10);
        function dmy(s) {
            var p = s.split('-');
            if (p.length !== 3) return s;
            return parseInt(p[2], 10) + '/' + parseInt(p[1], 10) + '/' + p[0];
        }
        return dmy(a) + ' – ' + dmy(b);
    }

    function setChTopKpiPeriodBadges(d) {
        var t = chPeriodoBadgeText(d);
        document.querySelectorAll('.ch-kpi-period-badge').forEach(function (el) {
            el.textContent = t;
        });
    }

    /** Una sola línea bajo el título: período consultado (sin duplicar `periodo_label` del servidor). */
    function chEstLineaRangoConsultado(d) {
        var t = chPeriodoBadgeText(d);
        if (t === '—') return 'Rango consultado: —';
        return 'Rango consultado: ' + t.replace(/\s*–\s*/g, ' → ');
    }

    function pintar(d) {
        if (!d) return;
        cerrarMovDetallePanel();
        setText('chEstRangoFechas', chEstLineaRangoConsultado(d));
        setChTopKpiPeriodBadges(d);
        var totEmp = n(d.total_empleados);
        var actEmp = n(d.empleados_activos);
        var bjEmp = n(d.empleados_baja);
        setText('chKpiTotalEmp', String(d.ingresos ?? 0));
        setText('chKpiTotalSub', 'Ingresos registrados en el periodo');
        var elTotPct = document.getElementById('chKpiTotalPctBadge');
        if (elTotPct) elTotPct.textContent = totEmp > 0 ? (Math.round((n(d.ingresos) / totEmp) * 100) + '%') : '—';

        setText('chKpiActivos', String(d.empleados_activos ?? 0));
        setText('chKpiActivosSub', 'Activos al cierre del periodo');
        var elActPct = document.getElementById('chKpiActivosPctBadge');
        if (elActPct) elActPct.textContent = totEmp > 0 ? (Math.round((actEmp / totEmp) * 100) + '%') : '—';

        setText('chKpiInactivos', String(d.empleados_baja ?? 0));
        setText('chKpiBajaSub', 'Bajas registradas en el periodo');
        var elBjPct = document.getElementById('chKpiBajasPctBadge');
        if (elBjPct) elBjPct.textContent = totEmp > 0 ? (Math.round((bjEmp / totEmp) * 100) + '%') : '—';

        setText('chKpiDirecciones', String(d.total_direcciones ?? 0));
        setText('chKpiAreas', String(d.total_areas ?? 0));
        setText('chKpiDeptos', String(d.total_departamentos ?? 0));
        setText('chKpiPuestos', String(d.puestos_unicos ?? 0));
        var ndi = n(d.total_direcciones);
        var nd = n(d.total_departamentos);
        var na = n(d.total_areas);
        var np = n(d.puestos_unicos);
        var elDipb = document.getElementById('chKpiDireccionesPctBadge');
        var elDpb = document.getElementById('chKpiDeptosPctBadge');
        var elApb = document.getElementById('chKpiAreasPctBadge');
        var elPpb = document.getElementById('chKpiPuestosPctBadge');
        if (elDipb) elDipb.textContent = actEmp > 0 ? (Math.round((ndi / actEmp) * 1000) / 10).toFixed(1).replace(/\.0$/, '') + '%' : '-';
        if (elApb) elApb.textContent = actEmp > 0 ? (Math.round((na / actEmp) * 1000) / 10).toFixed(1).replace(/\.0$/, '') + '%' : '—';
        if (elDpb) elDpb.textContent = actEmp > 0 ? (Math.round((nd / actEmp) * 1000) / 10).toFixed(1).replace(/\.0$/, '') + '%' : '—';
        if (elPpb) elPpb.textContent = actEmp > 0 ? (Math.round((np / actEmp) * 1000) / 10).toFixed(1).replace(/\.0$/, '') + '%' : '—';
        var rangoCorto = (d.fecha_ini && d.fecha_fin) ? (String(d.fecha_ini) + ' → ' + String(d.fecha_fin)) : '—';
        setText('chMovRangoInline', rangoCorto);
        setText('chRotRangoInline', rangoCorto);
        setText('chMovClicAviso', 'Tip: Haz clic en Ingresos, Bajas o Reingresos para abrir la gráfica por departamento.');
        setText('chMovIngresos', String(d.ingresos ?? 0));
        setText('chMovBajas', String(d.bajas ?? 0));
        setText('chMovReingresos', String(d.reingresos ?? 0));
        setText('chRotacionPct', String(d.rotacion_pct ?? 0) + '%');
        setRotacionPctStyle(d.rotacion_pct ?? 0);
        setBadge('chBadgeRotacion', d.rotacion_badge_text || '—', d.rotacion_badge_class || '');
        var rotCard = document.getElementById('chCardRotacion');
        if (rotCard) {
            rotCard.setAttribute('data-rotacion-ayuda', d.rotacion_ayuda != null ? String(d.rotacion_ayuda) : '');
        }
        var elBr = document.getElementById('chBadgeRotacion');
        if (elBr) {
            var ay = (d.rotacion_ayuda != null && String(d.rotacion_ayuda).trim()) ? String(d.rotacion_ayuda).trim() : '';
            var baseSt = badgeStyleFromClass(d.rotacion_badge_class || '');
            elBr.setAttribute('style', baseSt + (ay ? ';cursor:pointer' : ';cursor:default'));
        }
        setText('chRotLegendBajas', String(d.bajas ?? 0));
        setText('chRotLegendPlantilla', String(d.empleados_activos ?? 0));

        var topSede = d.plantilla_sede_top || [];
        var sedeLines = [];
        for (var si = 0; si < topSede.length; si++) {
            var rowS = topSede[si];
            if (!rowS) continue;
            sedeLines.push(String(si + 1) + '. ' + (rowS.nombre || '—') + ': ' + String(rowS.cnt ?? 0));
        }
        setText('chPlantillaSedeLista', sedeLines.length ? sedeLines.join('\n') : 'Sin datos');
        setText('chPlantillaSedeTotal', String(d.plantilla_total_sedes_activas ?? 0));

        var omitGen = !!d.plantilla_omit_genero;
        var wrapGen = document.getElementById('chWrapCardGenero');
        if (wrapGen) {
            if (omitGen) wrapGen.classList.add('d-none');
            else wrapGen.classList.remove('d-none');
        }
        if (!omitGen) {
            setText('chPlantillaGenH', String(d.plantilla_genero_hombres ?? 0));
            setText('chPlantillaGenM', String(d.plantilla_genero_mujeres ?? 0));
            var elBg = document.getElementById('chBadgeGenPred');
            if (elBg) {
                elBg.textContent = d.plantilla_genero_badge || '—';
                elBg.setAttribute('style', 'background: #1a3a5c; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;');
            }
        }

        setText('chPlantillaAntig', d.plantilla_antiguedad_label || '—');
        setText('chPlantillaNuevos90', String(d.plantilla_empleados_nuevos_90 ?? 0));
        setText('chPlantillaBajas90', String(d.plantilla_empleados_bajas_90 ?? 0));
        var omitTi = !!d.onb_omit_tiempo_induccion;
        setColVisible('chColTiempoInduccion', !omitTi);
        setText('chOnbDiasProm', String(d.onb_dias_prom_induccion ?? 0));
        renderCharts(d);
    }

    var chEstRefrescarTimer = null;

    /** Agrupa cambios de fecha (Flatpickr dispara varios eventos seguidos) y evita respuestas cruzadas. */
    function refrescar() {
        if (chEstRefrescarTimer !== null) {
            clearTimeout(chEstRefrescarTimer);
        }
        chEstRefrescarTimer = setTimeout(function () {
            chEstRefrescarTimer = null;
            refrescarEjecutar();
        }, 400);
    }

    function refrescarEjecutar() {
        var reqId = ++chReqSeq;
        showLoadingAviso();
        marcarTextosContextoFiltroPendiente('Actualizando según el filtro…');
        var bodyJson;
        try {
            bodyJson = JSON.stringify(chEstPayloadFiltro());
        } catch (eBody) {
            hideLoadingAviso();
            setText('chEstRangoFechas', 'No se pudo armar la petición del filtro'
                + ((eBody && eBody.message) ? ' — ' + String(eBody.message) : '') + '.');
            return;
        }
        fetch('/caphum/getEstadisticasPanel', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: bodyJson
        })
            .then(function (r) {
                if (!r || typeof r.text !== 'function') {
                    return { ok: false, status: 0, json: null, raw: '' };
                }
                return r.text().then(function (txt) {
                    var j = null;
                    try {
                        j = JSON.parse(txt);
                    } catch (e1) {
                        j = null;
                    }
                    return { ok: r.ok, status: r.status, json: j, raw: txt };
                });
            })
            .then(function (wrap) {
                if (reqId !== chReqSeq) {
                    return;
                }
                var resp = wrap.json;
                if (!wrap.ok || resp === null) {
                    setText('chEstRangoFechas', 'El servidor no devolvió JSON (¿sesión o ruta?). HTTP '
                        + String(wrap.status) + (wrap.raw ? ': ' + wrap.raw.slice(0, 280) : ''));
                    marcarTextosContextoFiltroPendiente('Sin datos: revise la respuesta del servidor.');
                    return;
                }
                if (resp.success && resp.datos != null) {
                    try {
                        pintar(resp.datos);
                    } catch (ePintar) {
                        var det = (ePintar && ePintar.message) ? String(ePintar.message) : String(ePintar);
                        if (typeof console !== 'undefined' && console.error) {
                            console.error('[CH estadísticas] pintar:', ePintar);
                        }
                        setText('chEstRangoFechas', 'Los datos llegaron pero falló al pintar el panel — ' + det.slice(0, 220));
                        marcarTextosContextoFiltroPendiente('Error al actualizar gráficas o textos. Revise la consola del navegador (F12).');
                    }
                    return;
                }
                var msg = resp.mensaje ? resp.mensaje : 'No se pudieron cargar las estadísticas.';
                setText('chEstRangoFechas', msg + (resp.error ? ' — ' + String(resp.error) : ''));
                marcarTextosContextoFiltroPendiente(msg);
            })
            .catch(function (err) {
                if (reqId !== chReqSeq) {
                    return;
                }
                if (typeof console !== 'undefined' && console.error) {
                    console.error('[CH estadísticas] fetch getEstadisticasPanel:', err);
                }
                var det = (err && err.message) ? String(err.message) : String(err);
                setText('chEstRangoFechas', 'No se pudo contactar al servidor — ' + det.slice(0, 220)
                    + ' · Revise F12 → Red (timeout, red o http/https distinto a la página).');
                marcarTextosContextoFiltroPendiente('Sin datos: falló la petición al panel.');
            })
            .finally(function () {
                if (reqId === chReqSeq) {
                    hideLoadingAviso();
                }
            });
    }

    pintar(datosIni);
    scheduleInitFlatpickrChEst();

    var btnChEstReset = document.getElementById('btnChEstRestablecerPeriodo');
    if (btnChEstReset) {
        btnChEstReset.addEventListener('click', function () {
            chEstRestaurarPeriodoPorDefecto();
        });
    }

    var selDireccion = document.getElementById('chEstFiltroDireccion');
    var selArea = document.getElementById('chEstFiltroArea');
    var selDepartamento = document.getElementById('chEstFiltroDepartamento');
    var selPuesto = document.getElementById('chEstFiltroPuesto');
    if (selDireccion) {
        selDireccion.addEventListener('change', function () {
            chEstFiltroEstructura.id_direccion = this.value || '';
            chEstFiltroEstructura.id_area = '';
            chEstFiltroEstructura.id_departamento = '';
            chEstFiltroEstructura.id_puesto = '';
            chEstCargarFiltrosEstructura({
                id_direccion: chEstFiltroEstructura.id_direccion,
                id_area: null,
                id_departamento: null
            }).then(refrescar);
        });
    }
    if (selArea) {
        selArea.addEventListener('change', function () {
            chEstFiltroEstructura.id_area = this.value || '';
            chEstFiltroEstructura.id_departamento = '';
            chEstFiltroEstructura.id_puesto = '';
            chEstCargarFiltrosEstructura({
                id_direccion: chEstFiltroEstructura.id_direccion,
                id_area: chEstFiltroEstructura.id_area,
                id_departamento: null
            }).then(refrescar);
        });
    }
    if (selDepartamento) {
        selDepartamento.addEventListener('change', function () {
            chEstFiltroEstructura.id_departamento = this.value || '';
            chEstFiltroEstructura.id_puesto = '';
            chEstCargarFiltrosEstructura({
                id_direccion: chEstFiltroEstructura.id_direccion,
                id_area: chEstFiltroEstructura.id_area,
                id_departamento: chEstFiltroEstructura.id_departamento
            }).then(refrescar);
        });
    }
    if (selPuesto) {
        selPuesto.addEventListener('change', function () {
            chEstFiltroEstructura.id_puesto = this.value || '';
            refrescar();
        });
    }
    chEstCargarFiltrosEstructura({ id_direccion: null, id_area: null, id_departamento: null });

    document.querySelectorAll('input[name="chMovDetChartTipo"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (!this.checked) return;
            chMovDetalleChartTipo = this.value || 'pie';
            if (chMovTipoAbierto && chMovDetalleListo) {
                renderMovDetalleChart(chMovDetalleLastRows);
            }
        });
    });

    document.querySelectorAll('.ch-mov-card').forEach(function (card) {
        card.addEventListener('click', function () {
            var tipo = card.getAttribute('data-ch-mov-tipo');
            if (tipo) solicitarMovDetalle(tipo);
        });
        card.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            var tipo = card.getAttribute('data-ch-mov-tipo');
            if (tipo) solicitarMovDetalle(tipo);
        });
    });
    var btnCerrMov = document.getElementById('chMovDetalleCerrar');
    if (btnCerrMov) btnCerrMov.addEventListener('click', cerrarMovDetallePanel);

    function initChEstTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            return;
        }
        document.querySelectorAll('[data-ch-est-tip="1"]').forEach(function (el) {
            if (el.getAttribute('data-ch-tip-inited') === '1') {
                return;
            }
            el.setAttribute('data-ch-tip-inited', '1');
            try {
                new bootstrap.Tooltip(el, { container: 'body', trigger: 'hover focus' });
            } catch (e1) { /* ignorar */ }
        });
    }
    initChEstTooltips();

    (function bindChRotacionAyuda() {
        var br = document.getElementById('chBadgeRotacion');
        if (!br || br.getAttribute('data-ch-rot-ayuda-listener') === '1') {
            return;
        }
        br.setAttribute('data-ch-rot-ayuda-listener', '1');
        function onActivate(e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') {
                return;
            }
            e.preventDefault();
            chRotacionAyudaMostrar();
        }
        br.addEventListener('click', onActivate);
        br.addEventListener('keydown', onActivate);
    })();

    window.addEventListener('resize', function () {
        if (chRotacionSyncResizeT) {
            clearTimeout(chRotacionSyncResizeT);
        }
        chRotacionSyncResizeT = setTimeout(function () {
            chRotacionSyncResizeT = null;
            scheduleSyncChRotacionCardMinHeight();
        }, 120);
    });
})();
</script>
