<?php

/**
 * Ejemplo de uso del pipeline de predicción (Motor + Interpretación fallback + Verificación fallback).
 * No requiere GEMINI_API_KEY para ejecutar; produce JSON con todas las claves requeridas.
 * predicciones_finales suman 100 ±0.01; prediccion_intencion.evidencia referencia ids de datosParaMotor.
 *
 * Uso: php backend/services/ejemplo_uso_pipeline.php
 */

require_once __DIR__ . '/LocationScoringService.php';
require_once __DIR__ . '/IAInterpretationService.php';
require_once __DIR__ . '/IAVerificationService.php';

use Services\LocationScoringService;
use Services\IAInterpretationService;
use Services\IAVerificationService;

$datosParaMotor = [
    'pagos_count' => 51,
    'ubicaciones' => [
        ['id' => 'u0', 'etiqueta' => 'Punto de interés', 'cantidad_registros' => 12, 'ultima_fecha' => '2026-01-08T22:45:28'],
        ['id' => 'u1', 'etiqueta' => 'Punto de interés 2', 'cantidad_registros' => 6, 'ultima_fecha' => '2025-12-31T03:59:16'],
        ['id' => 'u2', 'etiqueta' => 'Menos frecuente', 'cantidad_registros' => 1, 'ultima_fecha' => '2025-12-30T23:22:16'],
    ],
    'gestiones' => [
        ['id' => 'g0', 'fecha' => '2026-01-27T22:03:34', 'tipo' => 'Pago Recibido'],
        ['id' => 'g1', 'fecha' => '2026-01-27T15:07:47', 'tipo' => 'Pago Recibido'],
        ['id' => 'g2', 'fecha' => '2026-01-27T10:11:24', 'tipo' => 'Pago Recibido'],
    ],
];

$motor = new LocationScoringService();
$resultadoMotor = $motor->calcularProbabilidadLocalizacion($datosParaMotor);

$dom = (float) ($resultadoMotor['domicilio'] ?? 0);
$tra = (float) ($resultadoMotor['trabajo'] ?? 0);
$otr = (float) ($resultadoMotor['otro'] ?? 0);
$sum = $dom + $tra + $otr;
if (abs($sum - 100.0) > 0.01) {
    $otr = round(100.0 - $dom - $tra, 2);
}
$predicciones_finales = ['domicilio' => round($dom, 2), 'trabajo' => round($tra, 2), 'otro' => round($otr, 2)];

$llamarLLM = function ($sys, $parts, $max) {
    return ['success' => false, 'texto' => '', 'mensaje' => 'Sin API'];
};
$interpretacionSvc = new IAInterpretationService();
$interpretacion = $interpretacionSvc->interpretar($resultadoMotor, $llamarLLM, 'Crédito ejemplo.');
$prediccion_intencion = $interpretacion['prediccion_intencion'] ?? ['accion' => '', 'evidencia' => [], 'nota' => ''];
$idsEvidencia = array_map(function ($c) { return (string)($c['id'] ?? $c['key'] ?? ''); }, $resultadoMotor['trazabilidad']['candidatos'] ?? []);
if (empty($prediccion_intencion['evidencia']) && !empty($idsEvidencia)) {
    $prediccion_intencion['evidencia'] = array_slice($idsEvidencia, 0, 2);
}

$datosReales = [
    'pagos_count' => 51,
    'gps' => [],
    'gestiones' => [],
    'suspected_test' => false,
    'suspected_test_reasons' => [],
];
$verificacionSvc = new IAVerificationService();
$verificacion = $verificacionSvc->verificar($datosReales, $resultadoMotor, $interpretacion, $llamarLLM);

$plan_operativo = $interpretacion['acciones_recomendadas'] ?? ['Revisar mapa de ubicaciones', 'Revisar historial de gestiones'];
$riesgos = array_values(array_map('strval', $interpretacion['riesgos_detectados'] ?? []));

$out = [
    'predicciones_finales' => $predicciones_finales,
    'confianza_global'     => (int) ($verificacion['veracity_score'] ?? 70) / 100.0,
    'plan_operativo'       => $plan_operativo,
    'prediccion_intencion' => $prediccion_intencion,
    'riesgos'              => $riesgos,
    'trazabilidad'         => [
        'motor' => ['domicilio' => $dom, 'trabajo' => $tra, 'otro' => $otr, 'motor_confidence' => $resultadoMotor['motor_confidence'] ?? 0],
        'interpretacion_ok' => $interpretacion['success'] ?? false,
        'verificacion_ok'   => $verificacion['success'] ?? false,
    ],
    'verificacion' => [
        'veracity_score'       => (int) ($verificacion['veracity_score'] ?? 70),
        'suspected_test'       => (bool) ($verificacion['suspected_test'] ?? false),
        'evidencias_validadas' => $verificacion['evidencias_validadas'] ?? [],
        'claims_no_soportados' => $verificacion['claims_no_soportados'] ?? [],
    ],
];

$sumCheck = $predicciones_finales['domicilio'] + $predicciones_finales['trabajo'] + $predicciones_finales['otro'];
if (php_sapi_name() === 'cli') {
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n";
    if (abs($sumCheck - 100.0) > 0.01) {
        fwrite(STDERR, "WARN: predicciones_finales sum = {$sumCheck}\n");
    }
    if (empty($prediccion_intencion['evidencia'])) {
        fwrite(STDERR, "WARN: prediccion_intencion.evidencia vacío\n");
    }
}
return $out;
