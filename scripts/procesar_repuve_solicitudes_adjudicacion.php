<?php

declare(strict_types=1);

use Models\Adjudicacion;
use Models\MotosAdjudicadas;
use Models\SolicitudAdjudicacion;

require dirname(__DIR__) . '/backend/core/Database.php';
require dirname(__DIR__) . '/backend/core/Model.php';
require dirname(__DIR__) . '/backend/models/Adjudicacion.php';
require dirname(__DIR__) . '/backend/models/MotosAdjudicadas.php';
require dirname(__DIR__) . '/backend/models/SolicitudAdjudicacion.php';

$limite = 50;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--limite=(\d+)$/', $arg, $m)) {
        $limite = max(1, min(200, (int) $m[1]));
    }
}

$solicitudes = new SolicitudAdjudicacion();
$creditos = new Adjudicacion();
$motos = new MotosAdjudicadas();
$resumen = ['revisadas' => 0, 'pendientes' => 0, 'sin_reporte' => 0, 'manual' => 0, 'blacklist' => 0, 'errores' => []];

foreach ($solicitudes->obtenerSolicitudesConRepuveProcesando($limite) as $solicitud) {
    $idSolicitud = (int) ($solicitud['id'] ?? 0);
    $idCredito = (int) ($solicitud['id_credito'] ?? 0);
    if ($idSolicitud <= 0 || $idCredito <= 0) {
        continue;
    }
    $resumen['revisadas']++;
    try {
        $factura = $creditos->consultarMotoFacturadaMaxiProd($idCredito);
        $moto = is_array($factura['datos'] ?? null) ? $factura['datos'] : [];
        $vin = strtoupper((string) preg_replace('/\s+/u', '', (string) ($moto['numero_serie'] ?? '')));
        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
            $solicitudes->registrarResultadoRepuveKnockout($idSolicitud, 'VALIDACION_MANUAL_REPUVE', 'Validacion manual REPUVE pendiente', 'No se encontro un NIV de 17 caracteres en la factura. Debe realizarse la validacion manual de REPUVE posteriormente.', 0, 'Proceso automatico REPUVE');
            $resumen['manual']++;
            continue;
        }

        $consulta = $motos->consultarRepuveConCriterio($idCredito, 'vin', $vin, 0);
        $robo = is_array($consulta['reporte_robo'] ?? null) ? $consulta['reporte_robo'] : [];
        if (!empty($robo['confirmado'])) {
            $resultado = $solicitudes->marcarBlacklistPorRepuve($idSolicitud, 0, 'Proceso automatico REPUVE', $robo);
            if (!empty($resultado['success'])) {
                $solicitudes->registrarResultadoRepuveKnockout($idSolicitud, 'REPORTE_ROBO', 'Reporte de Robo', 'No se puede Proceder con la Adjudicacion. Cualquier duda contacta a tu lider.', 0, 'Proceso automatico REPUVE', $robo);
                $resumen['blacklist']++;
            } else {
                $resumen['errores'][] = ['id_solicitud' => $idSolicitud, 'mensaje' => (string) ($resultado['message'] ?? 'No se pudo enviar a blacklist.')];
            }
            continue;
        }

        $estado = strtoupper(trim((string) ($consulta['repuve']['estado'] ?? '')));
        if ($estado === 'PROCESANDO') {
            $resumen['pendientes']++;
            continue;
        }
        $texto = trim((string) ($consulta['message'] ?? $consulta['repuve']['mensaje'] ?? ''));
        $normalizado = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
        if (str_contains($normalizado, 'sin reporte de robo') || str_contains($normalizado, 'no cuenta con reporte')) {
            $solicitudes->registrarResultadoRepuveKnockout($idSolicitud, 'SIN_REPORTE_ROBO', 'Sin Reporte de Robo', 'REPUVE indica Sin Reporte de Robo. Puede continuar con la siguiente validacion.', 0, 'Proceso automatico REPUVE');
            $resumen['sin_reporte']++;
            continue;
        }

        $solicitudes->registrarResultadoRepuveKnockout($idSolicitud, 'VALIDACION_MANUAL_REPUVE', 'Validacion manual REPUVE pendiente', 'La consulta REPUVE fue no exitosa o no permitio confirmar el resultado. Debe realizarse la validacion manual de REPUVE posteriormente.', 0, 'Proceso automatico REPUVE');
        $resumen['manual']++;
    } catch (Throwable $e) {
        $resumen['errores'][] = ['id_solicitud' => $idSolicitud, 'mensaje' => $e->getMessage()];
    }
}

echo json_encode($resumen, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
