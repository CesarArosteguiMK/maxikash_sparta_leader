<?php

/**
 * Tests: BehaviorPredictionService — contrato de salida, pago próximo regular, incertidumbre, determinismo.
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/BehaviorPredictionService.php';

class BehaviorPredictionServiceTest extends TestCase
{
    private function getService(): \Services\BehaviorPredictionService
    {
        return new \Services\BehaviorPredictionService();
    }

    private function resultadoMotorBase(): array
    {
        $now = time();
        return [
            'domicilio' => 70.0,
            'trabajo' => 20.0,
            'otro' => 10.0,
            'motor_confidence' => 65.0,
            'trazabilidad' => [
                'candidatos' => [
                    ['id' => 'u0', 'key' => 0, 'place_type' => 'domicilio', 'label' => 'Casa', 'last_gps_days' => 2],
                    ['id' => 'u1', 'key' => 1, 'place_type' => 'trabajo', 'label' => 'Trabajo', 'last_gps_days' => 5],
                ],
            ],
        ];
    }

    public function testPredictPagoProximoRegular(): void
    {
        $svc = $this->getService();
        $now = time();
        $fechasPago = [
            date('Y-m-d', $now - 2 * 86400),
            date('Y-m-d', $now - 9 * 86400),
            date('Y-m-d', $now - 16 * 86400),
            date('Y-m-d', $now - 23 * 86400),
        ];
        $historial = [
            'fechas_pago' => $fechasPago,
            'gestiones' => [],
            'gps' => [],
        ];
        $datosReales = [
            'pagos_count' => 10,
            'gps' => [['id' => 'u0', 'ultima_fecha' => date('c', $now - 2 * 86400)]],
            'gestiones' => [
                ['id' => 'g0', 'fecha' => date('c', $now - 2 * 86400), 'tipo' => 'Pago Recibido'],
                ['id' => 'g1', 'fecha' => date('c', $now - 9 * 86400), 'tipo' => 'Pago Recibido'],
            ],
        ];
        $resultadoMotor = $this->resultadoMotorBase();
        $out = $svc->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial);

        $this->assertArrayHasKey('evento_probable', $out);
        $this->assertArrayHasKey('confianza_evento', $out);
        $this->assertArrayHasKey('indicadores', $out);
        $this->assertArrayHasKey('ventana_tiempo_estimada', $out);
        $this->assertArrayHasKey('explicacion_deterministica', $out);
        $this->assertArrayHasKey('evidencias', $out);

        $this->assertSame('pago_proximo', $out['evento_probable'], 'Pagos cada ~7 días y última hace 2 días => pago_proximo');
        $this->assertGreaterThanOrEqual(60.0, $out['confianza_evento'], 'Cliente regular debe tener confianza_evento >= 60');
        $this->assertLessThanOrEqual(100.0, $out['confianza_evento']);
        $this->assertArrayHasKey('desde_horas', $out['ventana_tiempo_estimada']);
        $this->assertArrayHasKey('hasta_horas', $out['ventana_tiempo_estimada']);
        $this->assertLessThanOrEqual(72, $out['ventana_tiempo_estimada']['hasta_horas'], 'Ventana hasta_horas debe ser <= 72 para pago próximo');
        $this->assertArrayHasKey('intervalo_promedio_pago', $out['indicadores']);
        $this->assertArrayHasKey('desviacion_intervalos', $out['indicadores']);
        $this->assertArrayHasKey('frecuencia_gestiones', $out['indicadores']);
        $this->assertArrayHasKey('recencia_gps', $out['indicadores']);
        $this->assertArrayHasKey('variabilidad_ubicacion', $out['indicadores']);
    }

    public function testPredictIncertidumbreClienteIrregular(): void
    {
        $svc = $this->getService();
        $historial = ['fechas_pago' => [], 'gestiones' => [], 'gps' => []];
        $datosReales = [
            'pagos_count' => 0,
            'gps' => [],
            'gestiones' => [],
        ];
        $resultadoMotor = [
            'domicilio' => 40.0,
            'trabajo' => 35.0,
            'otro' => 25.0,
            'motor_confidence' => 30.0,
            'trazabilidad' => [
                'candidatos' => [
                    ['id' => 'u0', 'key' => 0, 'last_gps_days' => 80],
                    ['id' => 'u1', 'key' => 1, 'last_gps_days' => 85],
                ],
            ],
        ];
        $out = $svc->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial);

        $this->assertSame('insuficiente_datos', $out['evento_probable'], 'Sin pagos ni gestiones => insuficiente_datos');
        $this->assertLessThan(30.0, $out['confianza_evento'], 'Datos insuficientes debe tener confianza_evento < 30');
        $this->assertGreaterThanOrEqual(0.0, $out['confianza_evento']);
    }

    public function testDeterminismo(): void
    {
        $svc = $this->getService();
        $resultadoMotor = $this->resultadoMotorBase();
        $datosReales = [
            'pagos_count' => 5,
            'gps' => [['id' => 'u0', 'ultima_fecha' => date('c', time() - 3 * 86400)]],
            'gestiones' => [
                ['id' => 'g0', 'fecha' => date('c', time() - 3 * 86400), 'tipo' => 'Pago Recibido'],
            ],
        ];
        $historial = ['fechas_pago' => [], 'gestiones' => $datosReales['gestiones'], 'gps' => $datosReales['gps']];

        $out1 = $svc->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial);
        $out2 = $svc->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial);
        $out3 = $svc->predecirIntencionAcreditado($resultadoMotor, $datosReales, $historial);

        $this->assertEquals($out1, $out2, 'Mismo input debe producir mismo output (1 vs 2)');
        $this->assertEquals($out2, $out3, 'Mismo input debe producir mismo output (2 vs 3)');
        $this->assertSame($out1['evento_probable'], $out2['evento_probable']);
        $this->assertSame($out1['confianza_evento'], $out2['confianza_evento']);
    }

    public function testSalidaContieneClavesRequeridas(): void
    {
        $svc = $this->getService();
        $out = $svc->predecirIntencionAcreditado(
            $this->resultadoMotorBase(),
            ['pagos_count' => 0, 'gps' => [], 'gestiones' => []],
            []
        );
        $this->assertArrayHasKey('evento_probable', $out);
        $this->assertArrayHasKey('confianza_evento', $out);
        $this->assertArrayHasKey('indicadores', $out);
        $this->assertArrayHasKey('ventana_tiempo_estimada', $out);
        $this->assertArrayHasKey('explicacion_deterministica', $out);
        $this->assertArrayHasKey('evidencias', $out);
        $this->assertIsFloat($out['confianza_evento']);
        $this->assertGreaterThanOrEqual(0.0, $out['confianza_evento']);
        $this->assertLessThanOrEqual(100.0, $out['confianza_evento']);
        $this->assertArrayHasKey('desde_horas', $out['ventana_tiempo_estimada']);
        $this->assertArrayHasKey('hasta_horas', $out['ventana_tiempo_estimada']);
        $this->assertIsArray($out['evidencias']);
    }
}
