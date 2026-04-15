<?php
/** @var string $titulo */
/** @var int $anioDefault */
/** @var int $mesDefault */
/** @var string $datosInicialesJson */
$anioDefault = isset($anioDefault) ? (int) $anioDefault : (int) date('Y');
$mesDefault = isset($mesDefault) ? (int) $mesDefault : (int) date('n');
$datosInicialesJson = $datosInicialesJson ?? '{}';
$semanaDefault = isset($semanaDefault) ? (int) $semanaDefault : 0;
?>
<style>
    .ch-est-tip-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        z-index: 2;
        border: none;
        background: transparent;
        padding: 0 2px;
        margin: 0;
        line-height: 1;
        font-size: 11px;
        color: #8a96a8;
        cursor: help;
        opacity: 0.92;
    }
    .ch-est-tip-btn:hover,
    .ch-est-tip-btn:focus {
        color: #1a3a5c;
        opacity: 1;
    }
    .ch-est-tip-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 58, 92, 0.2);
        border-radius: 4px;
    }
    .tooltip.ch-est-tip-kpi .tooltip-inner {
        max-width: min(320px, 92vw);
        text-align: left;
        font-size: 12px;
        line-height: 1.45;
    }
    .ch-est-tip-btn.ch-est-tip-inline {
        position: static;
        top: auto;
        right: auto;
        z-index: auto;
        flex-shrink: 0;
        align-self: flex-start;
        margin-top: 0;
    }
    .ch-mov-card {
        cursor: pointer;
        border: 2px solid transparent !important;
        border-radius: 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ch-mov-card:hover {
        border-color: rgba(26, 58, 92, 0.25) !important;
        box-shadow: 0 2px 8px rgba(26, 58, 92, 0.08);
    }
    .ch-mov-card.ch-mov-card-active {
        border-color: #2ecc8b !important;
        box-shadow: 0 0 0 1px rgba(46, 204, 139, 0.35);
    }
    .ch-mov-card:focus-visible {
        outline: 2px solid #2ecc8b;
        outline-offset: 2px;
    }
    .ch-rot-legend-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 4px;
        vertical-align: middle;
    }
    /* KPIs empleados (3) arriba de Movimientos; Dept/Puestos arriba de Rotación */
    .ch-kpi-strip-empleados {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .ch-kpi-strip-empleados > .ch-kpi-mini {
        position: relative;
        background: #ffffff;
        border: 1px solid #dde3ec;
        border-radius: 10px;
        padding: 16px 20px;
        text-align: center;
        flex: 1 1 0;
        min-width: 140px;
    }
    .ch-kpi-card-depto-puesto {
        position: relative;
        background: #ffffff;
        border: 1px solid #dde3ec;
        border-radius: 10px;
        padding: 16px 12px;
        width: 100%;
    }
    @media (max-width: 991.98px) {
        .ch-kpi-strip-empleados > .ch-kpi-mini {
            flex: 1 1 calc(50% - 6px);
            min-width: 120px;
        }
    }
    /* Misma anchura que Movimientos: strip dentro de col-lg-8 */
    .ch-plantilla-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 12px;
    }
    .ch-plantilla-strip > .ch-plantilla-card-wrap {
        flex: 1 1 0;
        min-width: 0;
    }
    .ch-plantilla-strip > .ch-plantilla-card-wrap .ch-plantilla-card-inner {
        height: 100%;
    }
    @media (max-width: 991.98px) {
        .ch-plantilla-strip > .ch-plantilla-card-wrap {
            flex: 1 1 calc(50% - 6px);
            min-width: 140px;
        }
    }
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div style="background: #f0f4f8; padding: 20px; border-radius: 12px;">
        <div style="background: linear-gradient(90deg, #1a3a5c 0%, #1a3a5c 65%, #0d5c3a 100%); border-radius: 12px; padding: 20px 24px 18px; margin-bottom: 16px;">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <h4 style="color: #ffffff; font-size: 18px; font-weight: 700; margin: 0 0 4px;">Capital Humano — Panel de Estadísticas</h4>
                    <p id="chEstSubtitulo" style="color: rgba(255,255,255,0.6); font-size: 13px; margin: 0;">—</p>
                    <p id="chEstRangoFechas" style="color: rgba(255,255,255,0.45); font-size: 11px; margin: 6px 0 0;">—</p>
                </div>
                <div class="col-lg-4">
                    <div class="row g-2 justify-content-lg-end">
                        <div class="col-4 col-lg-4">
                            <label for="chEstAnio" style="color: rgba(255,255,255,0.6); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-bottom: 4px;">Año</label>
                            <select id="chEstAnio" aria-label="Año" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; color: #ffffff; padding: 6px 10px; font-size: 13px; font-weight: 600; cursor: pointer; width: 100%;"></select>
                        </div>
                        <div class="col-4 col-lg-4">
                            <label for="chEstMes" style="color: rgba(255,255,255,0.6); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-bottom: 4px;">Mes</label>
                            <select id="chEstMes" aria-label="Mes" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; color: #ffffff; padding: 6px 10px; font-size: 13px; font-weight: 600; cursor: pointer; width: 100%;"></select>
                        </div>
                        <div class="col-4 col-lg-4">
                            <label for="chEstSemana" style="color: rgba(255,255,255,0.6); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-bottom: 4px;">Semana</label>
                            <select id="chEstSemana" aria-label="Semana" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; color: #ffffff; padding: 6px 10px; font-size: 13px; font-weight: 600; cursor: pointer; width: 100%;">
                                <option value="0" style="color:#1a3a5c;background:#ffffff;">Todas</option>
                                <option value="1" style="color:#1a3a5c;background:#ffffff;">Sem 1</option>
                                <option value="2" style="color:#1a3a5c;background:#ffffff;">Sem 2</option>
                                <option value="3" style="color:#1a3a5c;background:#ffffff;">Sem 3</option>
                                <option value="4" style="color:#1a3a5c;background:#ffffff;">Sem 4</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 align-items-start">
            <div class="col-lg-8">
                <div class="ch-kpi-strip-empleados">
                    <div class="ch-kpi-mini">
                        <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                            title="Total de filas en la tabla persona (todos los registros, cualquier estatus)."
                            aria-label="Ayuda: total empleados en plantilla">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                        <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Total empleados en plantilla</div>
                        <div id="chKpiTotalEmp" style="font-size: 32px; font-weight: 800; color: #1a3a5c; line-height: 1;">0</div>
                    </div>
                    <div class="ch-kpi-mini">
                        <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                            title="Personas cuyo estatus es «Activo» (comparación sin distinguir mayúsculas), contadas en toda la tabla persona."
                            aria-label="Ayuda: empleados activos">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                        <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Empleados activos</div>
                        <div id="chKpiActivos" style="font-size: 32px; font-weight: 800; color: #2ecc8b; line-height: 1;">0</div>
                    </div>
                    <div class="ch-kpi-mini">
                        <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                            title="Personas con estatus «Baja» (comparación sin distinguir mayúsculas), contadas en toda la tabla persona."
                            aria-label="Ayuda: empleados baja">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                        <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Empleados baja</div>
                        <div id="chKpiInactivos" style="font-size: 32px; font-weight: 800; color: #95a5a6; line-height: 1;">0</div>
                    </div>
                </div>
                <div style="position: relative; background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px;">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2" style="margin-bottom: 10px;">
                        <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex: 1; min-width: 0;">
                            <span style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Movimientos del período</span>
                            <span id="chMovRangoInline" style="font-size: 11px; font-weight: 600; color: #6b7a90; letter-spacing: 0.02em;">—</span>
                        </div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Cada número cuenta eventos entre la primera y la última fecha del filtro (año, mes y semana): ingresos, bajas y reingresos. Al hacer clic en Ingresos, Bajas o Reingresos se abre abajo el panel con la gráfica (torta) por departamento según el puesto vigente (última asignación en asigna_puesto)."
                            aria-label="Ayuda: movimientos del período">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2" style="gap:8px;">
                        <div class="ch-mov-card" data-ch-mov-tipo="ingresos" role="button" tabindex="0" aria-expanded="false" title="Clic para ver en qué departamento están hoy (puesto vigente)"
                            style="position: relative; background: #eef1f5; border: 1px solid #dde3ec; border-radius: 8px; padding: 10px 14px; text-align: center; flex: 1; min-width: 100px;">
                            <button type="button" class="ch-est-tip-btn ch-mov-no-abrir" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                onclick="event.stopPropagation();"
                                title="Personas cuya fecha de ingreso cae dentro del periodo seleccionado (no es la plantilla completa, solo altas en esas fechas)."
                                aria-label="Ayuda: ingresos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 11px; color: #6b7a90; margin-bottom: 4px; padding-right: 10px;">Ingresos</div>
                            <div id="chMovIngresos" style="font-size: 22px; font-weight: 700; color: #2ecc8b;">0</div>
                        </div>
                        <div class="ch-mov-card" data-ch-mov-tipo="bajas" role="button" tabindex="0" aria-expanded="false" title="Clic para ver departamento según puesto vigente en sistema"
                            style="position: relative; background: #eef1f5; border: 1px solid #dde3ec; border-radius: 8px; padding: 10px 14px; text-align: center; flex: 1; min-width: 100px;">
                            <button type="button" class="ch-est-tip-btn ch-mov-no-abrir" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                onclick="event.stopPropagation();"
                                title="Registros en baja_persona cuya fecha de baja está dentro del periodo seleccionado."
                                aria-label="Ayuda: bajas">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 11px; color: #6b7a90; margin-bottom: 4px; padding-right: 10px;">Bajas</div>
                            <div id="chMovBajas" style="font-size: 22px; font-weight: 700; color: #e74c3c;">0</div>
                        </div>
                        <div class="ch-mov-card" data-ch-mov-tipo="reingresos" role="button" tabindex="0" aria-expanded="false" title="Clic para ver desglose por departamento"
                            style="position: relative; background: #eef1f5; border: 1px solid #dde3ec; border-radius: 8px; padding: 10px 14px; text-align: center; flex: 1; min-width: 100px;">
                            <button type="button" class="ch-est-tip-btn ch-mov-no-abrir" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                onclick="event.stopPropagation();"
                                title="Registros en la tabla reingresos cuya fecha de reingreso cae dentro del periodo seleccionado."
                                aria-label="Ayuda: reingresos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 11px; color: #6b7a90; margin-bottom: 4px; padding-right: 10px;">Reingresos</div>
                            <div id="chMovReingresos" style="font-size: 22px; font-weight: 700; color: #3498db;">0</div>
                        </div>
                    </div>
                    <p id="chMovClicAviso" style="font-size: 11px; color: #5a6a7d; margin: 10px 0 0; line-height: 1.45;">Tip: Haz clic en Ingresos, Bajas o Reingresos para abrir la gráfica por departamento.</p>
                    <div id="chMovResumenBarWrap" style="min-height: 220px; margin-top: 10px;">
                        <div id="chChartMovimientos"></div>
                    </div>
                    <div id="chMovDetalleWrap" class="d-none" style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #dde3ec;">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div id="chMovDetalleTitulo" style="font-size: 13px; font-weight: 700; color: #1a3a5c;">—</div>
                                <div id="chMovDetalleSub" style="font-size: 11px; color: #6b7a90; margin-top: 2px; min-height: 0;"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="chMovDetalleCerrar">Cerrar</button>
                        </div>
                        <div id="chChartMovDetalle" style="min-height: 260px;"></div>
                    </div>
                </div>

                <div style="font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; margin: 20px 0 10px;">Plantilla y Estructura</div>
                <div class="ch-plantilla-strip">
                    <div class="ch-plantilla-card-wrap">
                        <div class="ch-plantilla-card-inner" style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Distribución por Sede</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Top de países/sedes con más personal activo al último día del periodo. El número inferior cuenta cuántas sedes distintas tienen al menos un activo en ese cierre."
                            aria-label="Ayuda: distribución por sede">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chPlantillaSedeLista" style="font-size: 12px; color: #6b7a90; margin-bottom: 10px; white-space: pre-line;">—</div>
                    <div style="font-size: 12px; color: #6b7a90;">Total sedes con personal activo</div>
                    <div id="chPlantillaSedeTotal" style="font-size: 24px; font-weight: 700; color: #1a3a5c; margin-top: 4px;">0</div>
                        </div>
                    </div>
                    <div class="ch-plantilla-card-wrap d-none" id="chWrapCardGenero">
                        <div class="ch-plantilla-card-inner" style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-1">
                            <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Distribución por Género</div>
                            <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Conteo de empleados activos al cierre del periodo según el campo género o sexo en persona (si existe en la base de datos)."
                                aria-label="Ayuda: distribución por género">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span id="chBadgeGenPred" style="font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 8px;">
                        <span style="font-size: 12px; color: #6b7a90;">Hombres</span>
                        <span id="chPlantillaGenH" style="font-size: 20px; font-weight: 700; color: #1a3a5c;">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 6px;">
                        <span style="font-size: 12px; color: #6b7a90;">Mujeres</span>
                        <span id="chPlantillaGenM" style="font-size: 20px; font-weight: 700; color: #2ecc8b;">0</span>
                    </div>
                        </div>
                    </div>
                    <div class="ch-plantilla-card-wrap">
                        <div class="ch-plantilla-card-inner" style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Antigüedad Promedio</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="AVG(DATEDIFF(último día del período, fecha_ingreso)) solo entre empleados Activos con fecha de ingreso no nula y al cierre del período (mismo criterio que plantilla al cierre: ingreso hasta ese día). No incluye el KPI «Total empleados en plantilla» (todas las filas de persona). Debajo del número verás cuántas personas entran en ese promedio."
                            aria-label="Ayuda: antigüedad promedio">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chPlantillaAntig" style="font-size: 22px; font-weight: 800; color: #1a3a5c;">—</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 8px;">años promedio en la empresa</div>
                        </div>
                    </div>
                    <div class="ch-plantilla-card-wrap">
                        <div class="ch-plantilla-card-inner" style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-1 flex-wrap">
                            <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Empleados Nuevos (&lt; 90 días)</div>
                            <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Activos al cierre cuya fecha de ingreso está entre (último día del período − 89 días) y ese último día inclusive: 90 días calendario que terminan en la fecha fin del filtro. El rango exacto se muestra debajo del número."
                                aria-label="Ayuda: empleados nuevos 90 días">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span id="chBadgeNuevos90" style="background: #fef3cd; color: #7a5000; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;">Recién ingresados</span>
                    </div>
                    <div id="chPlantillaNuevos90" style="font-size: 28px; font-weight: 800; color: #f0a500; margin-top: 8px;">0</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 6px;">Al cierre del período filtrado</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div style="background: #ffffff; border: 1px solid #dde3ec; border-radius: 12px; padding: 14px 16px;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 6px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; padding-right: 8px;">Composición de plantilla</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Distribución de empleados activos al cierre del periodo por departamento del puesto vigente (última asignación en asigna_puesto). Clic en un sector lo separa (expand). Si hay muchos departamentos, el resto se agrupa en «Otros»."
                            aria-label="Ayuda: composición de plantilla por departamento">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chChartPlantilla" style="min-height: 320px;"></div>
                    </div>
                </div>
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; margin: 20px 0 10px;"></div>
                <div id="chColTiempoInduccion" class="d-none mb-2" style="max-width: 420px;">
                    <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Tiempo Promedio de Inducción</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Solo si existe el campo fecha_fin_induccion en persona: promedio de días entre ingreso y fin de inducción, para activos que cerraron inducción con fecha de fin dentro del periodo seleccionado."
                            aria-label="Ayuda: tiempo promedio de inducción">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chOnbDiasProm" style="font-size: 24px; font-weight: 700; color: #1a3a5c;">0</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 8px;">días promedio para completar inducción</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-flex flex-column" style="gap: 16px;">
                <div class="ch-kpi-card-depto-puesto">
                    <div class="d-flex" style="align-items: stretch;">
                        <div style="flex: 1 1 50%; text-align: center; padding: 0 8px; border-right: 1px solid #dde3ec;">
                            <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                title="Cantidad de departamentos distintos entre los empleados activos con puesto asignado (última asignación) al cierre del periodo."
                                aria-label="Ayuda: total departamentos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Total departamentos</div>
                            <div id="chKpiDeptos" style="font-size: 32px; font-weight: 800; color: #1a3a5c; line-height: 1;">0</div>
                        </div>
                        <div style="flex: 1 1 50%; text-align: center; padding: 0 8px;">
                            <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                title="Cantidad de puestos distintos (catálogo de puestos) entre los mismos empleados activos con asignación al cierre del periodo."
                                aria-label="Ayuda: total puestos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Total puestos</div>
                            <div id="chKpiPuestos" style="font-size: 32px; font-weight: 800; color: #1a3a5c; line-height: 1;">0</div>
                        </div>
                    </div>
                </div>
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; flex: 1 1 auto;">
                    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                        <div class="d-flex flex-wrap align-items-baseline gap-2" style="flex: 1; min-width: 0;">
                            <span style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Rotación</span>
                            <span id="chRotRangoInline" style="font-size: 11px; font-weight: 600; color: #6b7a90;">—</span>
                            <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Indica qué porcentaje de empleados dejó la empresa en el período seleccionado, en relación con la plantilla al cierre (personas sin estatus Baja e ingreso hasta el último día del filtro)."
                                aria-label="Ayuda: rotación">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span id="chBadgeRotacion">—</span>
                    </div>
                    <div class="d-flex flex-column justify-content-center text-center pt-2">
                        <div id="chRotacionPct" style="font-size: 28px; font-weight: 800; color: #2ecc8b;">0%</div>
                        <div style="font-size: 11px; color: #6b7a90; margin-top: 4px;">(Bajas período / plantilla al cierre) × 100</div>
                    </div>
                    <div class="mt-3 pt-3" style="border-top: 1px solid #dde3ec;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6b7a90; margin-bottom: 2px; text-align: center;">Indicador de rotación</div>
                        <div id="chChartRotacion" style="min-height: 200px; max-width: 280px; margin: 0 auto;"></div>
                        <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-1" style="font-size: 11px; color: #6b7a90;">
                            <span><span class="ch-rot-legend-dot" style="background:#2ecc8b;"></span> Bajas período: <strong id="chRotLegendBajas">0</strong></span>
                            <span><span class="ch-rot-legend-dot" style="background:#bdc3c7;"></span> Plantilla cierre: <strong id="chRotLegendPlantilla">0</strong></span>
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
            o.setAttribute('style', 'color:#1a3a5c;background:#ffffff;');
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
            o.setAttribute('style', 'color:#1a3a5c;background:#ffffff;');
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
        base.setAttribute('style', 'color:#1a3a5c;background:#ffffff;');
        sel.appendChild(base);
        for (var i = 1; i <= maxSemana; i++) {
            var o = document.createElement('option');
            o.value = String(i);
            o.textContent = 'Sem ' + i;
            o.setAttribute('style', 'color:#1a3a5c;background:#ffffff;');
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
        var rotPct = Math.min(100, Math.max(0, n(d.rotacion_pct)));
        var rotColor = rotPct > 5 ? '#e74c3c' : '#2ecc8b';

        if (!chCharts.mov) {
            chCharts.mov = new ApexCharts(document.querySelector('#chChartMovimientos'), {
                chart: { type: 'bar', height: 250, toolbar: { show: false } },
                series: [{ name: 'Personas', data: movSeries }],
                xaxis: { categories: ['Ingresos', 'Bajas', 'Reingresos'] },
                colors: ['#2ecc8b', '#e74c3c', '#3498db'],
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } },
                grid: { borderColor: '#eef1f5' }
            });
            chCharts.mov.render();
        } else {
            chCharts.mov.updateOptions({
                xaxis: { categories: ['Ingresos', 'Bajas', 'Reingresos'] },
                colors: ['#2ecc8b', '#e74c3c', '#3498db'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: true } }
            }, false, true);
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
    }

    function destruirMovDetalleChart() {
        if (chCharts.movDetalle) {
            try { chCharts.movDetalle.destroy(); } catch (eM) {}
            chCharts.movDetalle = null;
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
                });
            }
        } else {
            w.classList.add('d-none');
        }
    }

    function cerrarMovDetallePanel() {
        chMovTipoAbierto = null;
        var wrap = document.getElementById('chMovDetalleWrap');
        if (wrap) wrap.classList.add('d-none');
        document.querySelectorAll('.ch-mov-card').forEach(function (el) {
            el.classList.remove('ch-mov-card-active');
            el.setAttribute('aria-expanded', 'false');
        });
        destruirMovDetalleChart();
        setMovResumenBarVisible(true);
    }

    function actualizarSeleccionMovCards(tipoActivo) {
        document.querySelectorAll('.ch-mov-card').forEach(function (el) {
            var t = el.getAttribute('data-ch-mov-tipo');
            if (t === tipoActivo) {
                el.classList.add('ch-mov-card-active');
                el.setAttribute('aria-expanded', 'true');
            } else {
                el.classList.remove('ch-mov-card-active');
                el.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function renderMovDetallePie(rows) {
        if (!ensureApexReady()) return;
        destruirMovDetalleChart();
        var el = document.querySelector('#chChartMovDetalle');
        if (!el) return;
        var labels = [];
        var series = [];
        for (var i = 0; i < rows.length; i++) {
            labels.push(rows[i].nombre || '—');
            series.push(n(rows[i].cnt));
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
        chCharts.movDetalle = new ApexCharts(el, {
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
        });
        chCharts.movDetalle.render();
    }

    function solicitarMovDetalle(tipo) {
        if (tipo === chMovTipoAbierto) {
            cerrarMovDetallePanel();
            return;
        }
        chMovTipoAbierto = tipo;
        actualizarSeleccionMovCards(tipo);
        setMovResumenBarVisible(false);
        var wrap = document.getElementById('chMovDetalleWrap');
        if (wrap) wrap.classList.remove('d-none');
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
                    renderMovDetallePie([]);
                    return;
                }
                var dat = resp.datos;
                var rng = (dat.fecha_ini && dat.fecha_fin) ? (String(dat.fecha_ini) + ' → ' + String(dat.fecha_fin)) : '';
                var suf = (dat.total != null ? ' · Total: ' + String(dat.total) : '') + (rng ? ' · ' + rng : '');
                setText('chMovDetalleTitulo', (titulos[tipo] || tipo) + suf);
                setText('chMovDetalleSub', '');
                renderMovDetallePie(dat.por_departamento || []);
            })
            .catch(function () {
                if (reqId !== chMovDetalleReqSeq || tipo !== chMovTipoAbierto) return;
                setText('chMovDetalleTitulo', titulos[tipo] || tipo);
                setText('chMovDetalleSub', 'Error de red al cargar el desglose.');
                renderMovDetallePie([]);
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

    function pintar(d) {
        if (!d) return;
        cerrarMovDetallePanel();
        setText('chEstSubtitulo', d.periodo_label || '—');
        if (d.fecha_ini && d.fecha_fin) {
            setText('chEstRangoFechas', 'Rango consultado: ' + d.fecha_ini + ' → ' + d.fecha_fin);
        } else {
            setText('chEstRangoFechas', '—');
        }
        setText('chKpiTotalEmp', String(d.total_empleados ?? 0));
        setText('chKpiActivos', String(d.empleados_activos ?? 0));
        setText('chKpiInactivos', String(d.empleados_baja ?? 0));
        setText('chKpiDeptos', String(d.total_departamentos ?? 0));
        setText('chKpiPuestos', String(d.puestos_unicos ?? 0));
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
        setText('chRotLegendBajas', String(d.bajas ?? 0));
        setText('chRotLegendPlantilla', String(d.plantilla_cierre_total ?? d.total_empleados ?? 0));

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

    document.querySelectorAll('.ch-mov-card').forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (e.target.closest('.ch-mov-no-abrir')) return;
            var tipo = card.getAttribute('data-ch-mov-tipo');
            if (tipo) solicitarMovDetalle(tipo);
        });
        card.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            if (e.target.closest('.ch-mov-no-abrir')) return;
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
                new bootstrap.Tooltip(el, { customClass: 'ch-est-tip-kpi', container: 'body', trigger: 'hover focus' });
            } catch (e1) { /* ignorar */ }
        });
    }
    initChEstTooltips();
})();
</script>
