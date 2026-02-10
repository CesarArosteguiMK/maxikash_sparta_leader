<?php
/**
 * Vista "Analítica - Resumen". Solo usa variables pasadas por el controlador (sin hardcodear valores).
 * Datos desde SpatialAnalyticsService, GestorComplianceService, TemporalPaymentsService.
 * No se usa IA: confianza, estados y acciones son por reglas determinísticas.
 */
$enModal = !empty($en_modal);
$idCredito = isset($id_credito) ? (int) $id_credito : 0;
$confianzaGeneral = isset($confianzaGeneral) ? (int) $confianzaGeneral : 0;
$domicilioConfirmado = !empty($domicilioConfirmado);
$distanciaDomicilio = isset($distanciaDomicilio) && $distanciaDomicilio !== null ? (int) $distanciaDomicilio : null;
$puntoInteres = isset($puntoInteres) && is_array($puntoInteres) ? $puntoInteres : [];
$cumplimientoPorc = isset($cumplimientoPorc) && is_numeric($cumplimientoPorc) ? (float) $cumplimientoPorc : null;
$peorGestor = isset($peorGestor) && is_array($peorGestor) ? $peorGestor : null;
$visitasCercanas = isset($visitasCercanas) ? (int) $visitasCercanas : 0;
$visitasLejanas = isset($visitasLejanas) ? (int) $visitasLejanas : 0;
$totalPagos = isset($totalPagos) ? (int) $totalPagos : 0;
$intervaloPromedio = isset($intervaloPromedio) && is_numeric($intervaloPromedio) ? $intervaloPromedio : null;
$desviacion = isset($desviacion) && is_numeric($desviacion) ? $desviacion : null;
$diaFrecuente = isset($diaFrecuente) && (string) $diaFrecuente !== '' ? (string) $diaFrecuente : 'N/D';
$consistenciaDia = isset($consistenciaDia) && is_numeric($consistenciaDia) ? $consistenciaDia : null;
$patronPago = isset($patronPago) && (string) $patronPago !== '' ? (string) $patronPago : 'desconocido';
$etiquetaPatronPago = isset($etiquetaPatronPago) && (string) $etiquetaPatronPago !== '' ? (string) $etiquetaPatronPago : $patronPago;
$scoreCliente = isset($scoreCliente) ? (int) $scoreCliente : 0;
$scoreGestion = isset($scoreGestion) ? (int) $scoreGestion : 0;
$scorePagos = isset($scorePagos) ? (int) $scorePagos : 0;
$datosFaltantes = isset($datosFaltantes) && is_array($datosFaltantes) ? $datosFaltantes : [];
$accionesRecomendadas = isset($accionesRecomendadas) && is_array($accionesRecomendadas) ? $accionesRecomendadas : [];
$mensajesSugeridos = isset($mensajesSugeridos) && is_array($mensajesSugeridos) ? $mensajesSugeridos : [];
$ultimoPago = isset($ultimoPago) && is_array($ultimoPago) ? $ultimoPago : ['fecha' => null, 'monto' => null];

$esc = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
?>
<link rel="stylesheet" href="/assets/css/analitica-ia.css">
<div class="analitica-ia-container <?= $enModal ? 'analitica-ia-container--modal' : '' ?>">
    <div class="analitica-ia-card">
        <div class="analitica-ia-header">
            <?php if ($enModal): ?>
            <h2 class="analitica-ia-title analitica-ia-title--modal">Resumen</h2>
            <p class="analitica-ia-nota-sin-ia">Análisis por <strong>reglas determinísticas</strong> (sin IA). La confianza, los estados y las acciones se calculan con reglas fijas a partir de ubicación, pagos y gestión de campo. No se usa inteligencia artificial en este resumen.</p>
            <?php else: ?>
            <h1 class="analitica-ia-title">Predicción IA – Cómo localizar al acreditado</h1>
            <p class="subtitle">Análisis integral basado en ubicación, pagos y gestión de campo</p>
            <?php endif; ?>
            <?php if ($confianzaGeneral > 0): ?>
            <div class="analitica-ia-confidence-wrap">
                <div class="analitica-ia-confidence-badge">
                    <?= $confianzaGeneral ?>%
                    <span class="label">confianza general</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="analitica-ia-summary-box">
            <p><strong>Resumen Ejecutivo:</strong></p>
            <p>
            <?php
            $partes = [];
            if ($domicilioConfirmado && $distanciaDomicilio !== null) {
                $partes[] = 'Domicilio probable confirmado a <span class="highlight">' . $esc($distanciaDomicilio) . ' metros</span>';
            }
            if (!empty($puntoInteres) && isset($puntoInteres['distancia']) && isset($puntoInteres['visitas'])) {
                $partes[] = 'Se detectó actividad recurrente en punto de interés a <span class="highlight">' . $esc($puntoInteres['distancia']) . ' km</span> (' . (int) $puntoInteres['visitas'] . ' visitas)';
            }
            if ($totalPagos > 0) {
                $partes[] = 'El cliente mantiene <span class="highlight">' . $totalPagos . ' pagos activos</span> con patrón ' . $esc(ucfirst($etiquetaPatronPago));
            }
            if ($cumplimientoPorc !== null) {
                $estadoGestion = $cumplimientoPorc < 30 ? 'crítica' : ($cumplimientoPorc < 70 ? 'regular' : 'buena');
                $partes[] = 'La eficacia de gestores es <span class="highlight">' . $esc($estadoGestion) . ': ' . number_format($cumplimientoPorc, 1) . '%</span> de cumplimiento';
            }
            if (empty($partes)) {
                echo 'No hay suficiente información para generar un resumen ejecutivo.';
            } else {
                echo implode('. ', $partes) . '.';
            }
            ?>
            </p>
        </div>
    </div>

    <div class="analitica-ia-card">
        <div class="analitica-ia-metrics-grid">
            <?php if ($scoreCliente > 0): ?>
            <?php
            $claseScoreCliente = $scoreCliente >= 70 ? 'analitica-ia-score-positive' : ($scoreCliente >= 40 ? 'analitica-ia-score-warning' : 'analitica-ia-score-critical');
            $tienePuntoInteresLejano = !empty($puntoInteres) && isset($puntoInteres['visitas']) && (int) $puntoInteres['visitas'] >= 5;
            $estadoCliente = $scoreCliente >= 70 ? ($tienePuntoInteresLejano ? 'Positivo con observación' : 'Positivo') : ($scoreCliente >= 40 ? 'Regular' : 'Crítico');
            $claseProgressCliente = $scoreCliente >= 70 ? 'analitica-ia-progress-positive' : ($scoreCliente >= 40 ? 'analitica-ia-progress-warning' : 'analitica-ia-progress-critical');
            ?>
            <div class="analitica-ia-metric-card">
                <div class="analitica-ia-metric-header">
                    <div class="analitica-ia-metric-title d-flex align-items-center gap-1 flex-wrap">
                        Cliente
                        <i class="fa-solid fa-circle-info text-muted small" style="cursor: help; font-size: 0.85em;" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $esc('La confianza de ubicación se calcula según qué tan cerca y con qué frecuencia se registran puntos alrededor del domicilio. Cuanto más cercanos y consistentes sean esos puntos, mayor será el porcentaje.') ?>" aria-label="Información sobre el score de ubicación"></i>
                    </div>
                    <div class="analitica-ia-score-circle <?= $claseScoreCliente ?>"><?= $scoreCliente ?>%</div>
                </div>
                <div class="analitica-ia-metric-content">
                    <p><strong>Estado:</strong> <?= $esc($estadoCliente) ?></p>
                    <?php if ($domicilioConfirmado && $distanciaDomicilio !== null): ?>
                    <p>Probable domicilio confirmado (<?= $esc($distanciaDomicilio) ?> m).</p>
                    <?php endif; ?>
                    <?php if (!empty($puntoInteres) && isset($puntoInteres['visitas']) && isset($puntoInteres['distancia'])): ?>
                    <p>Punto de interés con <strong><?= (int) $puntoInteres['visitas'] ?> visitas</strong> a <?= $esc($puntoInteres['distancia']) ?> km.</p>
                    <?php endif; ?>
                    <?php if (!empty($puntoInteres) && isset($puntoInteres['tipo']) && (string) $puntoInteres['tipo'] !== ''): ?>
                    <p><strong>Tipo (estrategia contacto):</strong> <?= $esc($puntoInteres['tipo']) ?>.</p>
                    <?php endif; ?>
                </div>
                <div class="analitica-ia-progress-bar-container">
                    <div class="analitica-ia-progress-bar <?= $claseProgressCliente ?>" style="width: <?= min(100, $scoreCliente) ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($cumplimientoPorc !== null): ?>
            <?php
            $claseScoreGestion = $cumplimientoPorc >= 70 ? 'analitica-ia-score-positive' : ($cumplimientoPorc >= 40 ? 'analitica-ia-score-warning' : 'analitica-ia-score-critical');
            $estadoGestion = $cumplimientoPorc >= 70 ? 'Positivo' : ($cumplimientoPorc >= 40 ? 'Regular' : 'Crítico');
            $claseProgressGestion = $cumplimientoPorc >= 70 ? 'analitica-ia-progress-positive' : ($cumplimientoPorc >= 40 ? 'analitica-ia-progress-warning' : 'analitica-ia-progress-critical');
            ?>
            <div class="analitica-ia-metric-card">
                <div class="analitica-ia-metric-header">
                    <div class="analitica-ia-metric-title d-flex align-items-center gap-1 flex-wrap">
                        Gestión
                        <i class="fa-solid fa-circle-info text-muted small" style="cursor: help; font-size: 0.85em;" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $esc('El cumplimiento de gestión refleja qué tan cerca estuvieron las visitas del gestor de las ubicaciones del cliente. Visitas más cercanas aumentan el porcentaje; visitas más lejanas lo reducen.') ?>" aria-label="Información sobre el score de gestión"></i>
                    </div>
                    <div class="analitica-ia-score-circle <?= $claseScoreGestion ?>"><?= $scoreGestion ?>%</div>
                </div>
                <div class="analitica-ia-metric-content">
                    <p><strong>Estado:</strong> <?= $esc($estadoGestion) ?></p>
                    <p>Cumplimiento de gestiones: <strong><?= number_format($cumplimientoPorc, 2) ?>%</strong></p>
                    <?php if ($visitasCercanas > 0 || $visitasLejanas > 0): ?>
                    <p><?= $visitasCercanas ?> visitas dentro de 100 m vs <?= $visitasLejanas ?> fuera de rango.</p>
                    <?php endif; ?>
                </div>
                <?php if ($peorGestor !== null && !empty($peorGestor['nombre'])): ?>
                <div class="analitica-ia-anomaly-box">
                    <p><span class="icon">Anomalía detectada:</span></p>
                    <?php if (isset($peorGestor['visitas_fuera_rango'])): ?>
                    <p>Se detectaron <strong><?= (int) $peorGestor['visitas_fuera_rango'] ?> visitas fuera del rango permitido</strong>
                    <?php if (isset($peorGestor['distancia_promedio'])): ?>
                    a una distancia promedio de <strong><?= number_format((float) $peorGestor['distancia_promedio'], 2) ?> km</strong>
                    <?php endif; ?>.</p>
                    <?php endif; ?>
                    <div class="analitica-ia-gestor-detail">
                        <p><strong>Gestor con mayor incumplimiento:</strong></p>
                        <p><?= $esc($peorGestor['nombre']) ?></p>
                        <?php if (isset($peorGestor['distancia_promedio'])): ?>
                        <p>Distancia promedio: <strong><?= number_format((float) $peorGestor['distancia_promedio'], 2) ?> km</strong></p>
                        <?php endif; ?>
                        <?php if (isset($peorGestor['visitas_fuera_rango'])): ?>
                        <p>Visitas fuera de rango: <strong><?= (int) $peorGestor['visitas_fuera_rango'] ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="analitica-ia-progress-bar-container">
                    <div class="analitica-ia-progress-bar <?= $claseProgressGestion ?>" style="width: <?= min(100, $scoreGestion) ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($totalPagos > 0): ?>
            <?php
            $claseScorePagos = $scorePagos >= 70 ? 'analitica-ia-score-positive' : ($scorePagos >= 40 ? 'analitica-ia-score-warning' : 'analitica-ia-score-critical');
            $esPagosIrregularConScoreAlto = $scorePagos >= 70 && $patronPago === 'irregular';
            $estadoPagos = $scorePagos >= 70 ? ($esPagosIrregularConScoreAlto ? 'Positivo moderado' : 'Positivo - Hábito activo') : ($scorePagos >= 40 ? 'Regular' : 'Crítico');
            $claseProgressPagos = $scorePagos >= 70 ? 'analitica-ia-progress-positive' : ($scorePagos >= 40 ? 'analitica-ia-progress-warning' : 'analitica-ia-progress-critical');
            ?>
            <div class="analitica-ia-metric-card">
                <div class="analitica-ia-metric-header">
                    <div class="analitica-ia-metric-title d-flex align-items-center gap-1 flex-wrap">
                        Pagos
                        <i class="fa-solid fa-circle-info text-muted small" style="cursor: help; font-size: 0.85em;" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $esc('La confianza se calcula según la constancia real de los pagos. Pequeñas variaciones en las fechas reducen ligeramente el porcentaje, aunque el historial sea bueno.') ?>" aria-label="Información sobre el score de pagos"></i>
                    </div>
                    <div class="analitica-ia-score-circle <?= $claseScorePagos ?>"><?= $scorePagos ?>%</div>
                </div>
                <div class="analitica-ia-metric-content">
                    <p><strong>Estado:</strong> <?= $esc($estadoPagos) ?></p>
                    <?php if ($esPagosIrregularConScoreAlto): ?>
                    <p>Historial activo pero irregular (<strong><?= $totalPagos ?> pagos</strong>).</p>
                    <?php else: ?>
                    <p>Historial de pagos activo (<strong><?= $totalPagos ?> pagos</strong>).</p>
                    <?php endif; ?>
                    <p><strong>Patrón:</strong> <?= $esc($etiquetaPatronPago === 'critico' ? 'Crítico' : ucfirst($etiquetaPatronPago)) ?></p>
                    <?php if ($diaFrecuente !== 'N/D'): ?>
                    <p><strong>Día frecuente:</strong> <?= $esc(ucfirst($diaFrecuente)) ?><?= $consistenciaDia !== null ? ' (' . $esc($consistenciaDia) . '%)' : '' ?></p>
                    <?php endif; ?>
                    <?php if (!empty($ultimoPago['fecha']) || !empty($ultimoPago['monto'])): ?>
                    <?php
                    $diasDesdeUltimo = null;
                    if (!empty($ultimoPago['fecha'])) {
                        $tsUltimo = strtotime($ultimoPago['fecha']);
                        $diasDesdeUltimo = $tsUltimo !== false ? (int) floor((time() - $tsUltimo) / 86400) : null;
                    }
                    ?>
                    <p><strong>Último pago:</strong> <?= !empty($ultimoPago['fecha']) ? $esc($ultimoPago['fecha']) : '—' ?><?= $diasDesdeUltimo !== null ? ' (hace ' . $diasDesdeUltimo . ' día' . ($diasDesdeUltimo !== 1 ? 's' : '') . ')' : '' ?><?= !empty($ultimoPago['monto']) ? ', monto ' . $esc($ultimoPago['monto']) : '' ?>.</p>
                    <?php endif; ?>
                </div>
                <div class="analitica-ia-progress-bar-container">
                    <div class="analitica-ia-progress-bar <?= $claseProgressPagos ?>" style="width: <?= min(100, $scorePagos) ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($accionesRecomendadas)): ?>
    <?php
    $prioridades = ['alta' => 1, 'media' => 2, 'baja' => 3];
    usort($accionesRecomendadas, function ($a, $b) use ($prioridades) {
        return ($prioridades[$a['prioridad']] ?? 999) - ($prioridades[$b['prioridad']] ?? 999);
    });
    ?>
    <div class="analitica-ia-card analitica-ia-actions-section">
        <h2 class="analitica-ia-actions-title">Acciones Recomendadas</h2>
        <?php foreach ($accionesRecomendadas as $accion): ?>
        <div class="analitica-ia-action-item">
            <div class="analitica-ia-priority-badge analitica-ia-priority-<?= $esc($accion['prioridad'] ?? 'baja') ?>"><?= $esc(ucfirst($accion['prioridad'] ?? 'baja')) ?></div>
            <div class="analitica-ia-action-content">
                <p><strong><?= $esc($accion['titulo'] ?? '') ?></strong></p>
                <p><?= $esc($accion['descripcion'] ?? '') ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($mensajesSugeridos)): ?>
    <div class="analitica-ia-card analitica-ia-messages-section">
        <h2 class="analitica-ia-actions-title">Mensajes Sugeridos</h2>
        <?php foreach ($mensajesSugeridos as $mensaje): ?>
        <div class="analitica-ia-message-card">
            <div class="analitica-ia-message-header">
                <span class="analitica-ia-message-type"><?= $esc($mensaje['tipo'] ?? '') ?></span>
                <span class="analitica-ia-message-priority">Prioridad: <?= $esc(ucfirst($mensaje['prioridad'] ?? 'baja')) ?></span>
            </div>
            <div class="analitica-ia-message-content">"<?= $esc($mensaje['mensaje'] ?? '') ?>"</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($datosFaltantes)): ?>
    <div class="analitica-ia-card">
        <div class="analitica-ia-missing-data">
            <h3>Datos Faltantes / Evidencia Requerida</h3>
            <ul>
                <?php foreach ($datosFaltantes as $dato): ?>
                <li><strong><?= $esc($dato['campo'] ?? '') ?></strong> – <?= $esc($dato['descripcion'] ?? '') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="analitica-ia-footer-note">
        <p>Referencias técnicas: analitica_espacial, analitica_pagos, analitica_gestiones</p>
        <p>Última actualización: <?= date('d/m/Y, h:i:s a') ?> | <strong>Análisis por reglas determinísticas (sin IA)</strong></p>
    </div>
</div>
