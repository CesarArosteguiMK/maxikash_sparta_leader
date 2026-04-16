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
 */
$anioDefault = isset($anioDefault) ? (int) $anioDefault : (int) date('Y');
$mesDefault = isset($mesDefault) ? (int) $mesDefault : (int) date('n');
$datosInicialesJson = $datosInicialesJson ?? '{}';
$semanaDefault = isset($semanaDefault) ? (int) $semanaDefault : 0;
?>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fa-solid fa-users me-2 text-primary"></i>Estadísticas Capital Humano
                </h4>
                <p id="chEstSubtitulo" class="text-muted mb-0 small">—</p>
                <p id="chEstRangoFechas" class="text-muted mb-0 mt-1 small">—</p>
            </div>
            <div class="d-flex flex-wrap align-items-end gap-2">
                <div>
                    <label for="chEstAnio" class="form-label small text-muted mb-0">Año</label>
                    <select id="chEstAnio" class="form-select form-select-sm" style="min-width: 5.5rem;" aria-label="Año"></select>
                </div>
                <div>
                    <label for="chEstMes" class="form-label small text-muted mb-0">Mes</label>
                    <select id="chEstMes" class="form-select form-select-sm" style="min-width: 9rem;" aria-label="Mes"></select>
                </div>
                <div>
                    <label for="chEstSemana" class="form-label small text-muted mb-0">Semana</label>
                    <select id="chEstSemana" class="form-select form-select-sm" style="min-width: 6.5rem;" aria-label="Semana">
                        <option value="0">Todas</option>
                        <option value="1">Sem 1</option>
                        <option value="2">Sem 2</option>
                        <option value="3">Sem 3</option>
                        <option value="4">Sem 4</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- KPIs: misma cuadrícula y marco que Gastos Cobranza (`gastos_cobranza_estadistica.php`) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Total empleados en plantilla</span>
                        <div class="ch-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div id="chKpiTotalEmp" class="fs-4 fw-bold text-body">0</div>
                        <div class="small text-muted mt-1 flex-grow-1" id="chKpiTotalSub">—</div>
                        <div class="mt-auto pt-2"><span class="badge rounded-pill bg-label-secondary text-secondary" id="chKpiTotalPctBadge">—</span></div>
                    </div>
                </div>
            </div>
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
                            <span class="badge rounded-pill bg-label-warning text-warning fw-bold py-2 px-2 flex-grow-1 text-center lh-sm min-w-0" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Deptos · Puestos</span>
                            <button type="button" class="btn btn-link btn-sm text-muted p-0 lh-1 flex-shrink-0 align-self-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Índice deptos: total departamentos ÷ empleados activos (×100). Índice puestos: total puestos únicos ÷ empleados activos (×100)."
                                aria-label="Ayuda: índices departamentos y puestos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="ch-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div class="row g-1 flex-grow-1 align-items-center">
                            <div class="col-6 text-center border-end">
                                <div class="small text-muted mb-1">Total departamentos</div>
                                <div id="chKpiDeptos" class="fs-4 fw-bold text-body">0</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="small text-muted mb-1">Total puestos</div>
                                <div id="chKpiPuestos" class="fs-4 fw-bold text-body">0</div>
                            </div>
                        </div>
                        <div class="row gx-1 mt-auto pt-2">
                            <div class="col-6 text-center">
                                <span class="badge rounded-pill bg-label-secondary text-secondary" id="chKpiDeptosPctBadge">—</span>
                            </div>
                            <div class="col-6 text-center">
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
    var anioSel = <?php echo json_encode($anioDefault); ?>;
    var mesSel = <?php echo json_encode($mesDefault); ?>;
    var semanaSel = <?php echo json_encode($semanaDefault); ?>;
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

    function mesNombre(m) {
        var n = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return n[m] || String(m);
    }

    function llenarAnios() {
        var sel = document.getElementById('chEstAnio');
        if (!sel) return;
        var now = new Date();
        var y0 = now.getFullYear();
        var desde = y0 - 3;
        var hasta = y0; // no mostrar años futuros
        if (anioSel > hasta) anioSel = hasta;
        sel.innerHTML = '';
        for (var y = desde; y <= hasta; y++) {
            var o = document.createElement('option');
            o.value = String(y);
            o.textContent = String(y);
            if (y === anioSel) o.selected = true;
            sel.appendChild(o);
        }
    }

    function llenarMeses() {
        var sel = document.getElementById('chEstMes');
        if (!sel) return;
        var now = new Date();
        var y = anioSel;
        var mesMax = (y === now.getFullYear()) ? (now.getMonth() + 1) : 12;
        if (mesSel > mesMax) mesSel = mesMax;
        if (mesSel < 1) mesSel = 1;
        sel.innerHTML = '';
        for (var m = 1; m <= mesMax; m++) {
            var o = document.createElement('option');
            o.value = String(m);
            o.textContent = mesNombre(m) + ' ' + y;
            if (m === mesSel) o.selected = true;
            sel.appendChild(o);
        }
    }

    function semanaDelMesActual(day) {
        if (day <= 7) return 1;
        if (day <= 14) return 2;
        if (day <= 21) return 3;
        return 4;
    }

    function llenarSemanas() {
        var sel = document.getElementById('chEstSemana');
        if (!sel) return;
        var now = new Date();
        var anioNow = now.getFullYear();
        var mesNow = now.getMonth() + 1;
        var maxSemana = 4;
        if (anioSel === anioNow && mesSel === mesNow) {
            maxSemana = semanaDelMesActual(now.getDate());
        }
        if (semanaSel > maxSemana || semanaSel < 0) semanaSel = 0;

        sel.innerHTML = '';
        var base = document.createElement('option');
        base.value = '0';
        base.textContent = 'Todas';
        sel.appendChild(base);
        for (var i = 1; i <= maxSemana; i++) {
            var o = document.createElement('option');
            o.value = String(i);
            o.textContent = 'Sem ' + i;
            sel.appendChild(o);
        }
        sel.value = String(semanaSel);
    }

    function setText(id, txt) {
        var el = document.getElementById(id);
        if (el) el.textContent = txt;
    }

    /** Evita mostrar fechas/cifras de un filtro anterior mientras llega la respuesta o si falla la petición. */
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
        if (elMov && !chCharts.mov) {
            chCharts.mov = new ApexCharts(elMov, optMovBar);
            chCharts.mov.render();
        } else if (chCharts.mov) {
            chCharts.mov.updateSeries([{ name: 'Personas', data: movSeries }], true);
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
                    var idx = opts.seriesIndex;
                    var val = opts.w.globals.series[idx];
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
                    return String(opts.w.config.series[opts.seriesIndex]);
                },
                style: { fontSize: '11px', fontWeight: 600 }
            },
            tooltip: {
                y: { formatter: function (val) { return val + ' personas'; } }
            },
            stroke: { width: 1, colors: ['#ffffff'] }
        };

        if (!chCharts.plantilla) {
            chCharts.plantilla = new ApexCharts(document.querySelector('#chChartPlantilla'), Object.assign({}, pieCommon, {
                series: pieSeries
            }));
            chCharts.plantilla.render();
        } else {
            chCharts.plantilla.updateOptions(pieCommon, false, true);
            chCharts.plantilla.updateSeries(pieSeries, true);
        }

        var elRot = document.querySelector('#chChartRotacion');
        if (elRot) {
            if (!chCharts.rotacion) {
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
            } else {
                chCharts.rotacion.updateOptions({ colors: [rotColor] }, false, false);
                chCharts.rotacion.updateSeries([rotPct], true);
            }
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
                        var idx = opts.seriesIndex;
                        return seriesName + ': ' + opts.w.globals.series[idx];
                    }
                },
                plotOptions: { pie: { expandOnClick: true } },
                dataLabels: {
                    enabled: labels.length <= 12 && !(labels.length === 1 && labels[0] === 'Sin datos'),
                    formatter: function (val, opts) {
                        return String(opts.w.config.series[opts.seriesIndex]);
                    },
                    style: { fontSize: '11px', fontWeight: 600 }
                },
                tooltip: { y: { formatter: function (val) { return val + ' personas'; } } },
                stroke: { width: 1, colors: ['#ffffff'] }
            };
        } else if (tipo === 'line') {
            /** Versión estable: trazo simple + markers.discrete por punto. Evita que Apex deje la línea oculta y mantiene colores distintos en cada punto. */
            var markerColors = pieColors.slice(0, series.length);
            while (markerColors.length < series.length) {
                markerColors.push(CH_DEPTO_COLORS[markerColors.length % CH_DEPTO_COLORS.length]);
            }
            var markerDiscrete = [];
            for (var m = 0; m < series.length; m++) {
                markerDiscrete.push({
                    seriesIndex: 0,
                    dataPointIndex: m,
                    fillColor: markerColors[m],
                    strokeColor: '#ffffff',
                    size: 7
                });
            }
            var lineStroke = '#5a6a7d';
            opt = {
                chart: { type: 'line', height: 290, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Personas', data: series }],
                xaxis: {
                    categories: labels,
                    labels: {
                        style: { fontSize: '10px' },
                        rotate: labels.length > 6 ? -35 : 0,
                        rotateAlways: labels.length > 6
                    }
                },
                yaxis: { min: 0 },
                colors: [lineStroke],
                stroke: {
                    curve: 'straight',
                    width: 4,
                    lineCap: 'round',
                    lineJoin: 'round'
                },
                legend: { show: false },
                markers: {
                    size: 7,
                    strokeWidth: 2,
                    strokeColors: '#ffffff',
                    hover: { size: 9 },
                    discrete: markerDiscrete
                },
                dataLabels: { enabled: false },
                grid: { borderColor: '#eef1f5', padding: { top: 8, right: 12 } },
                tooltip: {
                    y: {
                        formatter: function (val, opts) {
                            var i = opts.dataPointIndex;
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
        var sem = parseInt(document.getElementById('chEstSemana').value, 10);
        if (isNaN(sem)) sem = 0;
        var m = parseInt(document.getElementById('chEstMes').value, 10);
        if (isNaN(m)) m = mesSel;
        var reqId = ++chMovDetalleReqSeq;
        fetch('/caphum/getEstadisticasMovimientoDetalle', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify({ tipo: tipo, anio: anioSel, mes: m, semana: sem })
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
        setText('chEstSubtitulo', 'Cargando datos...');
    }

    function hideLoadingAviso() {
        if (chLoadingOpen && typeof Swal !== 'undefined' && Swal && typeof Swal.close === 'function') {
            Swal.close();
            chLoadingOpen = false;
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

    function pintar(d) {
        if (!d) return;
        cerrarMovDetallePanel();
        setText('chEstSubtitulo', d.periodo_label || '—');
        if (d.fecha_ini && d.fecha_fin) {
            setText('chEstRangoFechas', 'Rango consultado: ' + d.fecha_ini + ' → ' + d.fecha_fin);
        } else {
            setText('chEstRangoFechas', '—');
        }
        setChTopKpiPeriodBadges(d);
        var totEmp = n(d.total_empleados);
        var actEmp = n(d.empleados_activos);
        var bjEmp = n(d.empleados_baja);
        setText('chKpiTotalEmp', String(d.total_empleados ?? 0));
        setText('chKpiTotalSub', 'Personas · todos los estatus');
        var elTotPct = document.getElementById('chKpiTotalPctBadge');
        if (elTotPct) elTotPct.textContent = totEmp > 0 ? '100%' : '—';

        setText('chKpiActivos', String(d.empleados_activos ?? 0));
        setText('chKpiActivosSub', 'Activos al cierre del periodo');
        var elActPct = document.getElementById('chKpiActivosPctBadge');
        if (elActPct) elActPct.textContent = totEmp > 0 ? (Math.round((actEmp / totEmp) * 100) + '%') : '—';

        setText('chKpiInactivos', String(d.empleados_baja ?? 0));
        setText('chKpiBajaSub', 'Estatus baja en persona');
        var elBjPct = document.getElementById('chKpiBajasPctBadge');
        if (elBjPct) elBjPct.textContent = totEmp > 0 ? (Math.round((bjEmp / totEmp) * 100) + '%') : '—';

        setText('chKpiDeptos', String(d.total_departamentos ?? 0));
        setText('chKpiPuestos', String(d.puestos_unicos ?? 0));
        var nd = n(d.total_departamentos);
        var np = n(d.puestos_unicos);
        var elDpb = document.getElementById('chKpiDeptosPctBadge');
        var elPpb = document.getElementById('chKpiPuestosPctBadge');
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
            sedeLines.push(String(si + 1) + '. ' + (topSede[si].nombre || '—') + ': ' + String(topSede[si].cnt ?? 0));
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

    function refrescar() {
        var reqId = ++chReqSeq;
        var sem = parseInt(document.getElementById('chEstSemana').value, 10);
        if (isNaN(sem)) sem = 0;
        semanaSel = sem;
        var m = parseInt(document.getElementById('chEstMes').value, 10);
        if (isNaN(m)) m = mesSel;
        var ay = document.getElementById('chEstAnio');
        if (ay) {
            var y = parseInt(ay.value, 10);
            if (!isNaN(y)) anioSel = y;
        }
        showLoadingAviso();
        marcarTextosContextoFiltroPendiente('Actualizando según el filtro…');
        fetch('/caphum/getEstadisticasPanel', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify({ anio: anioSel, mes: m, semana: sem })
        })
            .then(function (r) {
                return r.text().then(function (txt) {
                    var j = null;
                    try { j = JSON.parse(txt); } catch (e1) { j = null; }
                    return { ok: r.ok, status: r.status, json: j, raw: txt };
                });
            })
            .then(function (wrap) {
                if (reqId !== chReqSeq) return;
                hideLoadingAviso();
                var resp = wrap.json;
                if (!wrap.ok || resp === null) {
                    setText('chEstSubtitulo', 'El servidor no devolvió JSON (¿sesión o ruta?).');
                    setText('chEstRangoFechas', 'HTTP ' + String(wrap.status) + (wrap.raw ? ': ' + wrap.raw.slice(0, 280) : ''));
                    marcarTextosContextoFiltroPendiente('Sin datos: revise la respuesta del servidor.');
                    return;
                }
                if (resp.success && resp.datos != null) {
                    pintar(resp.datos);
                    return;
                }
                var msg = resp.mensaje ? resp.mensaje : 'No se pudieron cargar las estadísticas.';
                setText('chEstSubtitulo', msg);
                setText('chEstRangoFechas', resp.error ? String(resp.error) : '');
                marcarTextosContextoFiltroPendiente(msg);
            })
            .catch(function () {
                if (reqId !== chReqSeq) return;
                hideLoadingAviso();
                setText('chEstSubtitulo', 'Error de red al consultar el panel.');
                setText('chEstRangoFechas', 'Comprueba la sesión y la ruta /caphum/getEstadisticasPanel');
                marcarTextosContextoFiltroPendiente('Sin datos: error de red al cambiar el filtro.');
            });
    }

    llenarAnios();
    llenarMeses();
    llenarSemanas();
    pintar(datosIni);

    document.getElementById('chEstAnio').addEventListener('change', function () {
        anioSel = parseInt(this.value, 10);
        if (isNaN(anioSel)) anioSel = new Date().getFullYear();
        llenarMeses();
        llenarSemanas();
        refrescar();
    });
    document.getElementById('chEstMes').addEventListener('change', function () {
        mesSel = parseInt(this.value, 10);
        if (isNaN(mesSel)) mesSel = 1;
        llenarSemanas();
        refrescar();
    });
    document.getElementById('chEstSemana').addEventListener('change', refrescar);

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
