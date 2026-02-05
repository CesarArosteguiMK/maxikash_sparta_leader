<?php

/**
 * Tests: fallback cuando LLM falla; motor_confidence < 10 → suspected_test true.
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/IAVerificationService.php';

class IAVerificationServiceTest extends TestCase
{
    public function testFallbackCuandoLLMFalla(): void
    {
        $datosReales = ['pagos_count' => 0, 'gps' => [], 'gestiones' => [], 'suspected_test' => false, 'suspected_test_reasons' => []];
        $resultadoMotor = ['domicilio' => 33.33, 'trabajo' => 33.33, 'otro' => 33.34, 'motor_confidence' => 50.0];
        $interpretacion = ['resumen' => 'Test', 'prediccion_intencion' => ['accion' => 'Revisar', 'evidencia' => [], 'nota' => '']];
        $llamarLLMFalla = function ($sys, $parts, $max) {
            return ['success' => false, 'texto' => ''];
        };
        $svc = new \Services\IAVerificationService();
        $out = $svc->verificar($datosReales, $resultadoMotor, $interpretacion, $llamarLLMFalla);
        $this->assertArrayHasKey('veracity_score', $out);
        $this->assertArrayHasKey('suspected_test', $out);
        $this->assertArrayHasKey('evidencias_validadas', $out);
        $this->assertArrayHasKey('claims_no_soportados', $out);
        $this->assertIsInt($out['veracity_score']);
        $this->assertGreaterThanOrEqual(0, $out['veracity_score']);
        $this->assertLessThanOrEqual(100, $out['veracity_score']);
        $this->assertIsBool($out['suspected_test']);
    }

    public function testMotorConfidenceMenor10SuspectedTestTrue(): void
    {
        $datosReales = ['pagos_count' => 0, 'gps' => [], 'gestiones' => [], 'suspected_test' => false, 'suspected_test_reasons' => []];
        $resultadoMotor = ['domicilio' => 33.33, 'trabajo' => 33.33, 'otro' => 33.34, 'motor_confidence' => 5.0];
        $interpretacion = ['resumen' => 'Pocos datos', 'prediccion_intencion' => []];
        $llamarLLMFalla = function ($sys, $parts, $max) {
            return ['success' => false, 'texto' => ''];
        };
        $svc = new \Services\IAVerificationService();
        $out = $svc->verificar($datosReales, $resultadoMotor, $interpretacion, $llamarLLMFalla);
        $this->assertTrue($out['suspected_test'], 'motor_confidence < 10 debe implicar suspected_test true');
        $this->assertLessThanOrEqual(50, $out['veracity_score']);
    }
}
