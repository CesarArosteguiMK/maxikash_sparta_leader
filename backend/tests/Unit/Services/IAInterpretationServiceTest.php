<?php

/**
 * Tests: fallback cuando LLM falla; evidencia referencia ids del motor.
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/IAInterpretationService.php';

class IAInterpretationServiceTest extends TestCase
{
    public function testFallbackCuandoLLMFalla(): void
    {
        $resultadoMotor = [
            'domicilio' => 60.0,
            'trabajo' => 25.0,
            'otro' => 15.0,
            'trazabilidad' => [
                'candidatos' => [
                    ['id' => 'u0', 'place_type' => 'domicilio', 'label' => 'Casa'],
                    ['id' => 'u1', 'place_type' => 'trabajo', 'label' => 'Oficina'],
                ],
            ],
            'motor_confidence' => 70.0,
        ];
        $llamarLLMFalla = function ($sys, $parts, $max) {
            return ['success' => false, 'texto' => '', 'mensaje' => 'Error'];
        };
        $svc = new \Services\IAInterpretationService();
        $out = $svc->interpretar($resultadoMotor, $llamarLLMFalla, null);
        $this->assertNotEmpty($out['resumen']);
        $this->assertIsArray($out['acciones_recomendadas']);
        $this->assertIsArray($out['riesgos_detectados']);
        $this->assertIsArray($out['patrones_conductuales']);
        $this->assertArrayHasKey('prediccion_intencion', $out);
        $this->assertArrayHasKey('accion', $out['prediccion_intencion']);
        $this->assertArrayHasKey('evidencia', $out['prediccion_intencion']);
        $this->assertArrayHasKey('nota', $out['prediccion_intencion']);
        $evidencia = $out['prediccion_intencion']['evidencia'];
        $idsCandidatos = ['u0', 'u1'];
        foreach ($evidencia as $id) {
            $this->assertContains($id, $idsCandidatos, 'evidencia debe referenciar ids de candidatos del motor');
        }
    }

    public function testEvidenciaReferenciaIdsDelMotor(): void
    {
        $resultadoMotor = [
            'domicilio' => 70.0,
            'trabajo' => 20.0,
            'otro' => 10.0,
            'trazabilidad' => [
                'candidatos' => [
                    ['id' => 'loc_1', 'key' => 0, 'place_type' => 'domicilio'],
                    ['id' => 'loc_2', 'key' => 1, 'place_type' => 'trabajo'],
                ],
            ],
            'motor_confidence' => 65.0,
        ];
        $llamarLLMFalla = function ($sys, $parts, $max) {
            return ['success' => false, 'texto' => ''];
        };
        $svc = new \Services\IAInterpretationService();
        $out = $svc->interpretar($resultadoMotor, $llamarLLMFalla, null);
        $evidencia = $out['prediccion_intencion']['evidencia'];
        $this->assertNotEmpty($evidencia, 'Fallback debe incluir al menos un id de evidencia');
        $idsEsperados = ['loc_1', 'loc_2'];
        foreach ($evidencia as $id) {
            $this->assertContains($id, $idsEsperados, "evidencia debe ser id de datosParaMotor: $id");
        }
    }
}
