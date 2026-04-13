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

        <div class="mb-4 d-flex flex-wrap" style="gap:12px;">
            <div style="position: relative; background: #ffffff; border: 1px solid #dde3ec; border-radius: 10px; padding: 16px 20px; text-align: center; flex: 1; min-width: 140px;">
                <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                    title="Foto al último día del periodo filtrado: personas que no tienen estatus «Baja» y cuya fecha de ingreso es anterior o igual a esa fecha. Puede incluir inactivos que no estén dados de baja en sistema."
                    aria-label="Ayuda: total empleados">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </button>
                <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Total Empleados</div>
                <div id="chKpiTotalEmp" style="font-size: 32px; font-weight: 800; color: #1a3a5c; line-height: 1;">0</div>
            </div>
            <div style="position: relative; background: #ffffff; border: 1px solid #dde3ec; border-radius: 10px; padding: 16px 20px; text-align: center; flex: 1; min-width: 140px;">
                <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                    title="Personas con estatus «activo» en la misma fecha de corte (último día del periodo seleccionado), con las mismas reglas de plantilla que usa el panel."
                    aria-label="Ayuda: empleados activos">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </button>
                <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Empleados Activos</div>
                <div id="chKpiActivos" style="font-size: 32px; font-weight: 800; color: #2ecc8b; line-height: 1;">0</div>
            </div>
            <div style="position: relative; background: #ffffff; border: 1px solid #dde3ec; border-radius: 10px; padding: 16px 20px; text-align: center; flex: 1; min-width: 140px;">
                <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                    title="Cantidad de departamentos distintos entre los empleados activos que tienen puesto asignado en el modelo del panel (al cierre del periodo)."
                    aria-label="Ayuda: total departamentos">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </button>
                <div style="font-size: 12px; color: #6b7a90; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 16px;">Total Departamentos</div>
                <div id="chKpiDeptos" style="font-size: 32px; font-weight: 800; color: #1a3a5c; line-height: 1;">0</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div style="position: relative; background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Movimientos del período</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Cada número cuenta eventos ocurridos entre la primera y la última fecha del filtro (año, mes y semana): ingresos, bajas, cierres de asignación de jefe (transferencias) y reingresos."
                            aria-label="Ayuda: movimientos del período">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2" style="gap:8px;">
                        <div style="position: relative; background: #eef1f5; border: 1px solid #dde3ec; border-radius: 8px; padding: 10px 14px; text-align: center; flex: 1; min-width: 100px;">
                            <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                title="Personas cuya fecha de ingreso cae dentro del periodo seleccionado (no es la plantilla completa, solo altas en esas fechas)."
                                aria-label="Ayuda: ingresos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 11px; color: #6b7a90; margin-bottom: 4px; padding-right: 10px;">Ingresos</div>
                            <div id="chMovIngresos" style="font-size: 22px; font-weight: 700; color: #2ecc8b;">0</div>
                        </div>
                        <div style="position: relative; background: #eef1f5; border: 1px solid #dde3ec; border-radius: 8px; padding: 10px 14px; text-align: center; flex: 1; min-width: 100px;">
                            <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                title="Registros en baja_persona cuya fecha de baja está dentro del periodo seleccionado."
                                aria-label="Ayuda: bajas">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 11px; color: #6b7a90; margin-bottom: 4px; padding-right: 10px;">Bajas</div>
                            <div id="chMovBajas" style="font-size: 22px; font-weight: 700; color: #e74c3c;">0</div>
                        </div>
                        <div style="position: relative; background: #eef1f5; border: 1px solid #dde3ec; border-radius: 8px; padding: 10px 14px; text-align: center; flex: 1; min-width: 100px;">
                            <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                title="En este periodo cuenta cuántas veces se registró el fin de una asignación de jefe (campo fecha_fin en asigna_jefe). Puede deberse a cambio de jefe, de área u otro movimiento; el sistema no indica el motivo. No es transferencia bancaria."
                                aria-label="Ayuda: qué cuenta como transferencia">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 11px; color: #6b7a90; margin-bottom: 4px; padding-right: 10px;">Transferencias</div>
                            <div id="chMovTransf" style="font-size: 22px; font-weight: 700; color: #f0a500;">0</div>
                        </div>
                        <div style="position: relative; background: #eef1f5; border: 1px solid #dde3ec; border-radius: 8px; padding: 10px 14px; text-align: center; flex: 1; min-width: 100px;">
                            <button type="button" class="ch-est-tip-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-ch-est-tip="1"
                                title="Registros en la tabla reingresos cuya fecha de reingreso cae dentro del periodo seleccionado."
                                aria-label="Ayuda: reingresos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                            <div style="font-size: 11px; color: #6b7a90; margin-bottom: 4px; padding-right: 10px;">Reingresos</div>
                            <div id="chMovReingresos" style="font-size: 22px; font-weight: 700; color: #3498db;">0</div>
                        </div>
                    </div>
                    <div id="chChartMovimientos" style="min-height: 220px; margin-top: 10px;"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-1">
                            <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Rotación</div>
                            <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Indica qué porcentaje de empleados dejó la empresa en el período seleccionado, en relación con el total de trabajadores."
                                aria-label="Ayuda: rotación">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span id="chBadgeRotacion">—</span>
                    </div>
                    <div class="d-flex flex-column justify-content-center text-center pt-2">
                        <div id="chRotacionPct" style="font-size: 28px; font-weight: 800; color: #2ecc8b;">0%</div>
                        <div style="font-size: 11px; color: #6b7a90; margin-top: 4px;">(Bajas / total empleados) × 100</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-1">
                            <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Selección</div>
                            <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Vacantes abiertas y candidatos en proceso se calculan con el estado actual del módulo de candidatos; no se filtran por el mes o semana del panel, por eso pueden no cambiar al mover el filtro."
                                aria-label="Ayuda: selección y candidatos">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span id="chBadgeSeleccion">—</span>
                    </div>
                    <div style="font-size: 12px; color: #6b7a90;">Vacantes abiertas (combinación depto / puesto)</div>
                    <div id="chVacantes" style="font-size: 24px; font-weight: 700; color: #f0a500; margin-top: 4px; margin-bottom: 16px;">0</div>
                    <div style="font-size: 12px; color: #6b7a90;">Candidatos activos en proceso</div>
                    <div id="chCandActivos" style="font-size: 20px; font-weight: 700; color: #1a3a5c; margin-top: 4px;">0</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Contrataciones</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Candidatos que pasaron a estatus Contratado con fecha de actualización o registro dentro del periodo. Los días promedio comparan registro inicial con la fecha en que quedaron contratados, solo para esos casos del periodo."
                            aria-label="Ayuda: contrataciones">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div style="font-size: 12px; color: #6b7a90;">Nuevos contratados (período)</div>
                    <div id="chContrata" style="font-size: 24px; font-weight: 700; color: #2ecc8b; margin-top: 4px; margin-bottom: 16px;">0</div>
                    <div style="font-size: 12px; color: #6b7a90;">Días promedio de proceso</div>
                    <div id="chDiasProm" style="font-size: 20px; font-weight: 700; color: #1a3a5c; margin-top: 4px;">0</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Inducción</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Cursos en progreso: empleados activos al cierre que aún tienen asignado el módulo web de onboarding (id 44). Cursos completados (periodo): activos que ingresaron en el periodo y ya no tienen ese módulo asignado (se considera cerrado en sistema)."
                            aria-label="Ayuda: inducción cursos">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div style="font-size: 12px; color: #6b7a90;">Cursos en progreso (módulo onboarding)</div>
                    <div id="chIndProg" style="font-size: 24px; font-weight: 700; color: #1a3a5c; margin-top: 4px; margin-bottom: 16px;">0</div>
                    <div style="font-size: 12px; color: #6b7a90;">Cursos completados (período)</div>
                    <div id="chIndComp" style="font-size: 24px; font-weight: 700; color: #2ecc8b; margin-top: 4px;">0</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-1">
                            <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Aprobación</div>
                            <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Porcentaje de candidatos cuya fecha de registro cae en el periodo y que quedaron en estatus Validado o Contratado, respecto del total de candidatos registrados en ese mismo periodo."
                                aria-label="Ayuda: tasa de aprobación">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span id="chBadgeAprob" style="background: #1a3a5c; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px;">Meta</span>
                    </div>
                    <div class="d-flex flex-column justify-content-center text-center pt-2">
                        <div id="chTasaAprob" style="font-size: 28px; font-weight: 800; color: #2ecc8b;">0%</div>
                        <div style="font-size: 11px; color: #6b7a90; margin-top: 6px;">Candidatos registrados en el período (Validado + Contratado)</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; margin: 20px 0 10px;">Plantilla y Estructura</div>
        <div class="row g-3 mb-2">
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
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
            <!-- Si no existe persona.genero ni persona.sexo, el modelo envía plantilla_omit_genero y se oculta esta card -->
            <div class="col-md-6 col-lg-3" id="chWrapCardGenero" style="display: none;">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
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
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Antigüedad Promedio</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Promedio de días desde la fecha de ingreso hasta el último día del periodo seleccionado, solo entre empleados activos con ingreso válido. Al cambiar el filtro cambia la fecha de corte y por tanto el resultado."
                            aria-label="Ayuda: antigüedad promedio">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chPlantillaAntig" style="font-size: 22px; font-weight: 800; color: #1a3a5c;">—</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 8px;">años promedio en la empresa</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-1 flex-wrap">
                            <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b;">Empleados Nuevos (&lt; 90 días)</div>
                            <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                                title="Activos cuya fecha de ingreso cae en los 90 días anteriores al último día del periodo (ventana móvil según el filtro)."
                                aria-label="Ayuda: empleados nuevos 90 días">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span id="chBadgeNuevos90" style="background: #fef3cd; color: #7a5000; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; display: inline-block;">Recién ingresados</span>
                    </div>
                    <div id="chPlantillaNuevos90" style="font-size: 28px; font-weight: 800; color: #f0a500; margin-top: 8px;">0</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 6px;">Respecto al fin del período seleccionado</div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div style="background: #ffffff; border: 1px solid #dde3ec; border-radius: 12px; padding: 14px 16px;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 6px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; padding-right: 8px;">Composición de plantilla</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Gráfico radial: porcentaje de empleados activos respecto del total de plantilla al cierre del periodo (según las reglas del panel)."
                            aria-label="Ayuda: composición de plantilla">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chChartPlantilla" style="min-height: 220px;"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div style="background: #ffffff; border: 1px solid #dde3ec; border-radius: 12px; padding: 14px 16px;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 6px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; padding-right: 8px;">Onboarding e incorporación</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Serie con onboarding completo y pendiente (módulo web 44) al cierre, y empleados con ingreso en los últimos 90 días respecto al fin del periodo."
                            aria-label="Ayuda: gráfico onboarding">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chChartOnboarding" style="min-height: 220px;"></div>
                </div>
            </div>
        </div>

        <div style="font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; margin: 20px 0 10px;">Onboarding e Inducción</div>
        <div class="row g-3 mb-2">
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">En Período de Prueba</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Empleados activos al cierre con menos de 90 días desde su fecha de ingreso (medidos al último día del periodo seleccionado)."
                            aria-label="Ayuda: período de prueba">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chOnbPrueba" style="font-size: 28px; font-weight: 800; color: #f0a500;">0</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 8px;">Menos de 90 días en empresa</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Inducción (sin módulo 44 vs con módulo)</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Sobre todos los activos al cierre del periodo: completa = sin módulo web 44 asignado; pendiente = con módulo 44 (onboarding aún asignado en sistema). No se limita solo a quien ingresó en el mes."
                            aria-label="Ayuda: inducción completa vs pendiente">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 4px;">
                        <span style="font-size: 12px; color: #6b7a90;">Completa</span>
                        <span id="chOnbComp" style="font-size: 20px; font-weight: 700; color: #2ecc8b;">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center" style="margin-top: 6px;">
                        <span style="font-size: 12px; color: #6b7a90;">Pendiente</span>
                        <span id="chOnbPen" style="font-size: 20px; font-weight: 700; color: #e74c3c;">0</span>
                    </div>
                    <div style="font-size: 11px; color: #6b7a90; margin-top: 8px;">Completa = activo sin asigna_modulo_web (44); pendiente = con módulo</div>
                </div>
            </div>
            <!-- campo persona.fecha_fin_induccion: si no existe en BD, onb_omit_tiempo_induccion y se oculta esta card -->
            <div class="col-md-6 col-lg-3" id="chColTiempoInduccion" style="display: none;">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
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
            <div class="col-md-6 col-lg-3">
                <div style="position: relative; background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">% Inducción Completa</div>
                        <button type="button" class="ch-est-tip-btn" style="position: static; flex-shrink: 0; margin-top: -2px;" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Solo sobre quienes ingresaron en el periodo seleccionado: porcentaje que sigue activo y ya no tiene el módulo web 44 (onboarding), respecto del total de ingresos del mismo periodo. No representa a toda la plantilla."
                            aria-label="Ayuda: porcentaje de inducción completa">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chOnbPct" style="font-size: 28px; font-weight: 800; color: #2ecc8b;">0%</div>
                    <div style="font-size: 11px; color: #6b7a90; margin-top: 6px;">(ingresos período sin módulo 44 / total ingresos período) × 100</div>
                </div>
            </div>
        </div>

        <div style="font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; margin: 20px 0 10px;">Estructura Operativa</div>
        <div class="row g-3 mb-2">
            <div class="col-md-6 col-lg-6">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Agentes Call Center</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Activos al cierre cuyo puesto actual (última asignación en asigna_puesto) coincide con los criterios de nombre para agente de Call Center definidos en el panel."
                            aria-label="Ayuda: agentes call center">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chOpAgentes" style="font-size: 28px; font-weight: 800; color: #1a3a5c;">0</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 6px;">Personas con puesto de agente en Call Center y asignación activa, al cierre del período</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6">
                <div style="background: #f5f7fa; border: 1px solid #dde3ec; border-radius: 12px; padding: 16px 18px; height: 100%;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 10px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #2ecc8b; padding-right: 8px;">Departamento con más Personal</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Departamento del puesto vigente de cada activo (última fila de asigna_puesto por persona). Se muestra el depto con mayor conteo y cuántas personas activas lo tienen al cierre del periodo."
                            aria-label="Ayuda: departamento con más personal">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chOpDeptoNombre" style="font-size: 16px; font-weight: 700; color: #1a3a5c;">—</div>
                    <div style="font-size: 12px; color: #6b7a90; margin-top: 6px;">Personas activas (estatus y plantilla al cierre), por departamento del puesto activo</div>
                    <div id="chOpDeptoCnt" style="font-size: 22px; font-weight: 700; color: #2ecc8b; margin-top: 4px;">0</div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div style="background: #ffffff; border: 1px solid #dde3ec; border-radius: 12px; padding: 14px 16px;">
                    <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 6px;">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7a90; padding-right: 8px;">Tendencia de estructura operativa</div>
                        <button type="button" class="ch-est-tip-btn ch-est-tip-inline" data-bs-toggle="tooltip" data-bs-placement="left" data-ch-est-tip="1"
                            title="Barras al cierre del periodo: cantidad de agentes de Call Center, supervisores (puesto marcado como jefe) y número de personas del departamento con más personal activo."
                            aria-label="Ayuda: gráfico estructura operativa">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="chChartEstructura" style="min-height: 210px;"></div>
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

    var ST_APROB_OK = 'font-size: 28px; font-weight: 800; color: #2ecc8b;';
    var ST_APROB_MID = 'font-size: 28px; font-weight: 800; color: #f0a500;';
    var ST_APROB_LOW = 'font-size: 28px; font-weight: 800; color: #e74c3c;';
    var chCharts = { mov: null, plantilla: null, estructura: null, onb: null };
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

    function setTasaAprobStyle(pct) {
        var el = document.getElementById('chTasaAprob');
        if (!el) return;
        var n = parseFloat(String(pct).replace(',', '.'));
        if (isNaN(n)) n = 0;
        var st = ST_APROB_OK;
        if (n < 50) st = ST_APROB_LOW;
        else if (n < 80) st = ST_APROB_MID;
        el.setAttribute('style', st);
    }

    function setColVisible(id, show) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = show ? 'col-md-6 col-lg-3' : 'col-md-6 col-lg-3 d-none';
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

        var total = Math.max(0, n(d.total_empleados));
        var activos = Math.max(0, n(d.empleados_activos));

        var movSeries = [n(d.ingresos), n(d.bajas), n(d.transferencias), n(d.reingresos)];
        var onbSeries = [n(d.onb_completa_activos), n(d.onb_pendiente_activos), n(d.plantilla_empleados_nuevos_90)];
        var estSeries = [n(d.op_agentes_call), n(d.op_supervisores), n(d.op_depto_top_cnt)];

        if (!chCharts.mov) {
            chCharts.mov = new ApexCharts(document.querySelector('#chChartMovimientos'), {
                chart: { type: 'bar', height: 250, toolbar: { show: false } },
                series: [{ name: 'Personas', data: movSeries }],
                xaxis: { categories: ['Ingresos', 'Bajas', 'Transferencias', 'Reingresos'] },
                colors: ['#2ecc8b'],
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 6, columnWidth: '48%' } },
                grid: { borderColor: '#eef1f5' }
            });
            chCharts.mov.render();
        } else {
            chCharts.mov.updateOptions({ xaxis: { categories: ['Ingresos', 'Bajas', 'Transferencias', 'Reingresos'] } }, false, true);
            chCharts.mov.updateSeries([{ name: 'Personas', data: movSeries }], true);
        }

        if (!chCharts.plantilla) {
            chCharts.plantilla = new ApexCharts(document.querySelector('#chChartPlantilla'), {
                chart: { type: 'radialBar', height: 250 },
                series: [total > 0 ? Math.round((activos / total) * 100) : 0],
                labels: ['Activos'],
                colors: ['#2ecc8b'],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '62%' },
                        dataLabels: {
                            name: { fontSize: '14px' },
                            value: { fontSize: '22px', formatter: function (val) { return Math.round(val) + '%'; } },
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function () { return String(total); }
                            }
                        }
                    }
                }
            });
            chCharts.plantilla.render();
        } else {
            chCharts.plantilla.updateSeries([total > 0 ? Math.round((activos / total) * 100) : 0], true);
            chCharts.plantilla.updateOptions({
                plotOptions: {
                    radialBar: {
                        dataLabels: {
                            total: { formatter: function () { return String(total); } }
                        }
                    }
                }
            }, false, true);
        }

        if (!chCharts.estructura) {
            chCharts.estructura = new ApexCharts(document.querySelector('#chChartEstructura'), {
                chart: { type: 'bar', height: 230, toolbar: { show: false } },
                series: [{ name: 'Personas', data: estSeries }],
                xaxis: { categories: ['Agentes CC', 'Supervisores', 'Top depto (cant.)'] },
                colors: ['#1a3a5c'],
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
                grid: { borderColor: '#eef1f5' }
            });
            chCharts.estructura.render();
        } else {
            chCharts.estructura.updateOptions({ xaxis: { categories: ['Agentes CC', 'Supervisores', 'Top depto (cant.)'] } }, false, true);
            chCharts.estructura.updateSeries([{ name: 'Personas', data: estSeries }], true);
        }

        if (!chCharts.onb) {
            chCharts.onb = new ApexCharts(document.querySelector('#chChartOnboarding'), {
                chart: { type: 'line', height: 230, toolbar: { show: false } },
                series: [{ name: 'Personas', data: onbSeries }],
                xaxis: { categories: ['Onboarding completo', 'Onboarding pendiente', 'Nuevos < 90 días'] },
                stroke: { curve: 'smooth', width: 3 },
                markers: { size: 4 },
                colors: ['#f0a500'],
                dataLabels: { enabled: false },
                grid: { borderColor: '#eef1f5' }
            });
            chCharts.onb.render();
        } else {
            chCharts.onb.updateSeries([{ name: 'Personas', data: onbSeries }], true);
        }
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
        setText('chEstSubtitulo', d.periodo_label || '—');
        if (d.fecha_ini && d.fecha_fin) {
            setText('chEstRangoFechas', 'Rango consultado: ' + d.fecha_ini + ' → ' + d.fecha_fin);
        } else {
            setText('chEstRangoFechas', '—');
        }
        setText('chKpiTotalEmp', String(d.total_empleados ?? 0));
        setText('chKpiActivos', String(d.empleados_activos ?? 0));
        setText('chKpiDeptos', String(d.total_departamentos ?? 0));
        setText('chMovIngresos', String(d.ingresos ?? 0));
        setText('chMovBajas', String(d.bajas ?? 0));
        setText('chMovTransf', String(d.transferencias ?? 0));
        setText('chMovReingresos', String(d.reingresos ?? 0));
        setText('chRotacionPct', String(d.rotacion_pct ?? 0) + '%');
        setRotacionPctStyle(d.rotacion_pct ?? 0);
        setBadge('chBadgeRotacion', d.rotacion_badge_text || '—', d.rotacion_badge_class || '');
        setText('chVacantes', String(d.vacantes_abiertas ?? 0));
        setText('chCandActivos', String(d.candidatos_activos ?? 0));
        setBadge('chBadgeSeleccion', d.seleccion_badge_text || '—', d.seleccion_badge_class || '');
        setText('chContrata', String(d.contrataciones ?? 0));
        setText('chDiasProm', String(d.dias_promedio_contratacion ?? 0));
        setText('chIndProg', String(d.induccion_progreso ?? 0));
        setText('chIndComp', String(d.induccion_completados ?? 0));
        setText('chTasaAprob', String(d.tasa_aprobacion_pct ?? 0) + '%');
        setTasaAprobStyle(d.tasa_aprobacion_pct ?? 0);

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
            if (omitGen) wrapGen.style.display = 'none';
            else wrapGen.style.removeProperty('display');
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

        setText('chOnbPrueba', String(d.onb_en_prueba ?? 0));
        setText('chOnbComp', String(d.onb_completa_activos ?? 0));
        setText('chOnbPen', String(d.onb_pendiente_activos ?? 0));
        var omitTi = !!d.onb_omit_tiempo_induccion;
        setColVisible('chColTiempoInduccion', !omitTi);
        setText('chOnbDiasProm', String(d.onb_dias_prom_induccion ?? 0));
        setText('chOnbPct', String(d.onb_pct_induccion_completa ?? 0) + '%');
        var elOnbPct = document.getElementById('chOnbPct');
        if (elOnbPct) {
            var np = parseFloat(String(d.onb_pct_induccion_completa ?? 0).replace(',', '.'));
            if (isNaN(np)) np = 0;
            var stp = ST_APROB_OK;
            if (np < 50) stp = ST_APROB_LOW;
            else if (np < 80) stp = ST_APROB_MID;
            elOnbPct.setAttribute('style', stp);
        }

        setText('chOpAgentes', String(d.op_agentes_call ?? 0));
        setText('chOpDeptoNombre', d.op_depto_top_nombre || '—');
        setText('chOpDeptoCnt', String(d.op_depto_top_cnt ?? 0));
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
                    return;
                }
                if (resp.success && resp.datos != null) {
                    pintar(resp.datos);
                    return;
                }
                var msg = resp.mensaje ? resp.mensaje : 'No se pudieron cargar las estadísticas.';
                setText('chEstSubtitulo', msg);
                setText('chEstRangoFechas', resp.error ? String(resp.error) : '');
            })
            .catch(function () {
                if (reqId !== chReqSeq) return;
                hideLoadingAviso();
                setText('chEstSubtitulo', 'Error de red al consultar el panel.');
                setText('chEstRangoFechas', 'Comprueba la sesión y la ruta /caphum/getEstadisticasPanel');
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
