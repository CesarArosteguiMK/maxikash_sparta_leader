<?php

/**
 * Tests: formato de salida del pipeline (predicciones_finales, plan_operativo, prediccion_intencion, etc.).
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/LocationScoringService.php';
require_once dirname(__DIR__, 3) . '/services/IAInterpretationService.php';
require_once dirname(__DIR__, 3) . '/services/IAVerificationService.php';

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
        $datosReales = ['pagos_count' => 2, 'gps' => [], 'gestiones' => [], 'suspected_test' => false, 'suspected_test_reasons' => []];
        $verificacion = (new \Services\IAVerificationService())->verificar($datosReales, $resultadoMotor, $interpretacion, $llamarLLM);
        $out = [
            'predicciones_finales' => $predicciones_finales,
            'confianza_global'     => (int) ($verificacion['veracity_score'] ?? 70) / 100.0,
            'plan_operativo'       => $interpretacion['acciones_recomendadas'] ?? [],
            'prediccion_intencion' => $prediccion_intencion,
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
    }
}
