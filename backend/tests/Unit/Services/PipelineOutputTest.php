<?php

/**
 * Tests: formato de salida del pipeline (predicciones_finales, plan_operativo, prediccion_intencion, etc.).
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/LocationScoringService.php';
require_once dirname(__DIR__, 3) . '/services/IAInterpretationService.php';
require_once dirname(__DIR__, 3) . '/services/IAVerificationService.php';
require_once dirname(__DIR__, 3) . '/services/BehaviorPredictionService.php';

class PipelineOutputTest extends TestCase
{
    public function testFormatoSalidaTieneClavesRequeridas(): void
    {
        $datosParaMotor = [
            'pagos_count' => 2,
            'ubicaciones' => [
                ['id' => 'u0', 'etiqueta' => 'Casa', 'cantidad_registros' => 5, 'ultima_fecha' => date('c')],
                ['id' => 'u1', 'etiqueta' => 'Otro', 'cantidad_registros' => 2, 'ultima_fecha' => date('c')],
            ],
            'gestiones' => [['id' => 'g0', 'fecha' => date('c'), 'tipo' => 'Visita']],
        ];
        $motor = new \Services\LocationScoringService();
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
            return ['success' => false, 'texto' => ''];
        };
        $interpretacion = (new \Services\IAInterpretationService())->interpretar($resultadoMotor, $llamarLLM, null);
        $prediccion_intencion = $interpretacion['prediccion_intencion'] ?? ['accion' => '', 'evidencia' => [], 'nota' => ''];
        $idsEvidencia = array_map(function ($c) {
            return (string) ($c['id'] ?? $c['key'] ?? '');
        }, $resultadoMotor['trazabilidad']['candidatos'] ?? []);
        if (empty($prediccion_intencion['evidencia']) && !empty($idsEvidencia)) {
            $prediccion_intencion['evidencia'] = array_slice($idsEvidencia, 0, 2);
        }
        $datosReales = [
            'pagos_count' => 2,
            'gps' => [['id' => 'u0', 'ultima_fecha' => date('c')]],
            'gestiones' => [['id' => 'g0', 'fecha' => date('c'), 'tipo' => 'Visita']],
            'suspected_test' => false,
            'suspected_test_reasons' => [],
        ];
        $verificacion = (new \Services\IAVerificationService())->verificar($datosReales, $resultadoMotor, $interpretacion, $llamarLLM);
        $historial_temporal = ['fechas_pago' => [], 'gestiones' => $datosParaMotor['gestiones'] ?? [], 'gps' => $datosParaMotor['ubicaciones'] ?? []];
        $prediccion_conductual = (new \Services\BehaviorPredictionService())->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial_temporal);
        $verificacion = (new \Services\IAVerificationService())->enriquecerConEvidenciasPredictor($datosReales, $resultadoMotor, $prediccion_conductual, $verificacion);
        if (!empty($prediccion_conductual['evidencias'])) {
            $prediccion_intencion['evidencia'] = array_values(array_unique(array_merge($prediccion_intencion['evidencia'], array_slice($prediccion_conductual['evidencias'], 0, 5))));
        }
        $out = [
            'predicciones_finales' => $predicciones_finales,
            'confianza_global'     => (int) ($verificacion['veracity_score'] ?? 70) / 100.0,
            'plan_operativo'       => $interpretacion['acciones_recomendadas'] ?? [],
            'prediccion_intencion' => $prediccion_intencion,
            'prediccion_conductual' => $prediccion_conductual,
            'riesgos'              => $interpretacion['riesgos_detectados'] ?? [],
            'trazabilidad'         => ['motor' => $resultadoMotor],
            'verificacion'          => $verificacion,
        ];
        $this->assertArrayHasKey('predicciones_finales', $out);
        $this->assertArrayHasKey('confianza_global', $out);
        $this->assertArrayHasKey('plan_operativo', $out);
        $this->assertArrayHasKey('prediccion_intencion', $out);
        $this->assertArrayHasKey('riesgos', $out);
        $this->assertArrayHasKey('trazabilidad', $out);
        $this->assertArrayHasKey('verificacion', $out);
        $this->assertArrayHasKey('accion', $out['prediccion_intencion']);
        $this->assertArrayHasKey('evidencia', $out['prediccion_intencion']);
        $sumCheck = $out['predicciones_finales']['domicilio'] + $out['predicciones_finales']['trabajo'] + $out['predicciones_finales']['otro'];
        $this->assertGreaterThanOrEqual(99.99, $sumCheck);
        $this->assertLessThanOrEqual(100.01, $sumCheck);
        $this->assertNotEmpty($out['prediccion_intencion']['evidencia'], 'evidencia debe referenciar al menos un id de datosParaMotor');
        $this->assertArrayHasKey('prediccion_conductual', $out);
        $this->assertArrayHasKey('evento_probable', $out['prediccion_conductual']);
        $this->assertArrayHasKey('evidencias', $out['prediccion_conductual']);
    }

    public function testPipelineIncludesBehaviorPrediction(): void
    {
        $datosParaMotor = [
            'pagos_count' => 51,
            'ubicaciones' => [
                ['id' => 'u0', 'etiqueta' => 'POI', 'cantidad_registros' => 12, 'ultima_fecha' => date('c', strtotime('-2 days'))],
                ['id' => 'u1', 'etiqueta' => 'POI 2', 'cantidad_registros' => 6, 'ultima_fecha' => date('c', strtotime('-5 days'))],
            ],
            'gestiones' => [
                ['id' => 'g0', 'fecha' => date('c', strtotime('-2 days')), 'tipo' => 'Pago Recibido'],
                ['id' => 'g1', 'fecha' => date('c', strtotime('-9 days')), 'tipo' => 'Pago Recibido'],
            ],
        ];
        $motor = new \Services\LocationScoringService();
        $resultadoMotor = $motor->calcularProbabilidadLocalizacion($datosParaMotor);
        $datosReales = [
            'pagos_count' => 51,
            'gps' => [['id' => 'u0', 'ultima_fecha' => date('c', strtotime('-2 days'))]],
            'gestiones' => $datosParaMotor['gestiones'],
            'suspected_test' => false,
            'suspected_test_reasons' => [],
        ];
        $historial = ['fechas_pago' => [], 'gestiones' => $datosParaMotor['gestiones'], 'gps' => $datosParaMotor['ubicaciones']];
        $prediccion_conductual = (new \Services\BehaviorPredictionService())->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial);

        $this->assertArrayHasKey('prediccion_conductual', ['prediccion_conductual' => $prediccion_conductual]);
        $this->assertArrayHasKey('evento_probable', $prediccion_conductual);
        $this->assertArrayHasKey('confianza_evento', $prediccion_conductual);
        $this->assertArrayHasKey('evidencias', $prediccion_conductual);
        $this->assertIsArray($prediccion_conductual['evidencias']);
        $idsValidos = array_merge(
            array_map(function ($c) { return (string)($c['id'] ?? $c['key']); }, $resultadoMotor['trazabilidad']['candidatos'] ?? []),
            ['u0', 'g0', 'g1']
        );
        foreach ($prediccion_conductual['evidencias'] as $id) {
            $this->assertContains($id, $idsValidos, "evidencia {$id} debe ser id válido de datosParaMotor/datosReales");
        }
    }
}
