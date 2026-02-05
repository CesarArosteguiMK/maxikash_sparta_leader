<?php

/**
 * Tests: normalización (suma 100 ±0.01), penalizaciones GPS, formato salida.
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/LocationScoringService.php';

class LocationScoringServiceTest extends TestCase
{
    private function getMotor(array $config = []): \Services\LocationScoringService
    {
        return new \Services\LocationScoringService($config);
    }

    public function testPrediccionesSuman100(): void
    {
        $datos = [
            'pagos_count' => 5,
            'ubicaciones' => [
                ['id' => 'u0', 'etiqueta' => 'A', 'cantidad_registros' => 10, 'ultima_fecha' => date('c', strtotime('-5 days'))],
                ['id' => 'u1', 'etiqueta' => 'B', 'cantidad_registros' => 4, 'ultima_fecha' => date('c', strtotime('-10 days'))],
                ['id' => 'u2', 'etiqueta' => 'C', 'cantidad_registros' => 1, 'ultima_fecha' => date('c', strtotime('-2 days'))],
            ],
            'gestiones' => [
                ['id' => 'g0', 'fecha' => date('c'), 'tipo' => 'Visita'],
            ],
        ];
        $motor = $this->getMotor();
        $out = $motor->calcularProbabilidadLocalizacion($datos);
        $sum = ($out['domicilio'] ?? 0) + ($out['trabajo'] ?? 0) + ($out['otro'] ?? 0);
        $this->assertGreaterThanOrEqual(99.99, $sum, 'Suma debe ser >= 99.99');
        $this->assertLessThanOrEqual(100.01, $sum, 'Suma debe ser <= 100.01');
    }

    public function testSalidaContieneClavesRequeridas(): void
    {
        $datos = [
            'pagos_count' => 0,
            'ubicaciones' => [],
            'gestiones' => [],
        ];
        $motor = $this->getMotor();
        $out = $motor->calcularProbabilidadLocalizacion($datos);
        $this->assertArrayHasKey('domicilio', $out);
        $this->assertArrayHasKey('trabajo', $out);
        $this->assertArrayHasKey('otro', $out);
        $this->assertArrayHasKey('trazabilidad', $out);
        $this->assertArrayHasKey('motor_confidence', $out);
        $this->assertIsFloat($out['domicilio']);
        $this->assertIsFloat($out['motor_confidence']);
        $this->assertGreaterThanOrEqual(0, $out['motor_confidence']);
        $this->assertLessThanOrEqual(100, $out['motor_confidence']);
    }

    public function testPenalizacionGpsDatosAntiguos(): void
    {
        $now = time();
        $datosRecientes = [
            'pagos_count' => 5,
            'ubicaciones' => [
                ['id' => 'u0', 'etiqueta' => 'A', 'cantidad_registros' => 8, 'ultima_fecha' => date('c', $now - 5 * 86400)],
                ['id' => 'u1', 'etiqueta' => 'B', 'cantidad_registros' => 8, 'ultima_fecha' => date('c', $now - 5 * 86400)],
            ],
            'gestiones' => [],
        ];
        $datosAntiguos = [
            'pagos_count' => 5,
            'ubicaciones' => [
                ['id' => 'u0', 'etiqueta' => 'A', 'cantidad_registros' => 8, 'ultima_fecha' => date('c', $now - 95 * 86400)],
                ['id' => 'u1', 'etiqueta' => 'B', 'cantidad_registros' => 8, 'ultima_fecha' => date('c', $now - 95 * 86400)],
            ],
            'gestiones' => [],
        ];
        $motor = $this->getMotor();
        $outReciente = $motor->calcularProbabilidadLocalizacion($datosRecientes);
        $outAntiguo = $motor->calcularProbabilidadLocalizacion($datosAntiguos);
        $this->assertGreaterThan(
            $outAntiguo['motor_confidence'],
            $outReciente['motor_confidence'],
            'Con datos más recientes motor_confidence debe ser mayor'
        );
    }

    public function testDeterminismoSinRand(): void
    {
        $datos = [
            'pagos_count' => 3,
            'ubicaciones' => [
                ['id' => 'u0', 'etiqueta' => 'X', 'cantidad_registros' => 5, 'ultima_fecha' => '2026-01-01T12:00:00'],
            ],
            'gestiones' => [['id' => 'g0', 'fecha' => '2026-01-15T10:00:00', 'tipo' => 'Pago']],
        ];
        $motor = $this->getMotor();
        $a = $motor->calcularProbabilidadLocalizacion($datos);
        $b = $motor->calcularProbabilidadLocalizacion($datos);
        $this->assertEquals($a['domicilio'], $b['domicilio']);
        $this->assertEquals($a['trabajo'], $b['trabajo']);
        $this->assertEquals($a['otro'], $b['otro']);
    }
}
