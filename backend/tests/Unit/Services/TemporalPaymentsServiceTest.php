<?php

/**
 * Tests: recurrencia regular vs irregular, día dominante.
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/TemporalPaymentsService.php';

class TemporalPaymentsServiceTest extends TestCase
{
    private function getService(): \Services\TemporalPaymentsService
    {
        return new \Services\TemporalPaymentsService();
    }

    public function testPatronRegular(): void
    {
        $svc = $this->getService();
        $pagos = [];
        $base = strtotime('2025-01-01');
        for ($i = 0; $i < 5; $i++) {
            $pagos[] = ['fecha' => date('Y-m-d', $base + $i * 7 * 86400)];
        }
        $out = $svc->analizarPagos($pagos);
        $this->assertSame(5, $out['total_pagos']);
        $this->assertEqualsWithDelta(7.0, $out['intervalo_promedio_dias'], 0.1);
        $this->assertSame('regular', $out['patron_pago']);
    }

    public function testPatronIrregular(): void
    {
        $svc = $this->getService();
        $pagos = [
            ['fecha' => '2025-01-01'],
            ['fecha' => '2025-01-02'],
            ['fecha' => '2025-01-20'],
            ['fecha' => '2025-02-15'],
        ];
        $out = $svc->analizarPagos($pagos);
        $this->assertSame(4, $out['total_pagos']);
        $this->assertSame('irregular', $out['patron_pago']);
    }

    public function testDiaMasFrecuente(): void
    {
        $svc = $this->getService();
        $pagos = [
            ['fecha' => '2025-01-06'],
            ['fecha' => '2025-01-13'],
            ['fecha' => '2025-01-20'],
            ['fecha' => '2025-01-27'],
        ];
        $out = $svc->analizarPagos($pagos);
        $this->assertArrayHasKey('dia_mas_frecuente', $out);
        $this->assertNotNull($out['dia_mas_frecuente']);
        $this->assertContains($out['dia_mas_frecuente'], ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo']);
    }

    public function testConsistenciaDia(): void
    {
        $svc = $this->getService();
        $pagos = [
            ['fecha' => '2025-01-06'],
            ['fecha' => '2025-01-13'],
            ['fecha' => '2025-01-20'],
        ];
        $out = $svc->analizarPagos($pagos);
        $this->assertArrayHasKey('consistencia_dia', $out);
        $this->assertGreaterThanOrEqual(0, $out['consistencia_dia']);
        $this->assertLessThanOrEqual(1, $out['consistencia_dia']);
    }

    public function testAnalizarPagosVacio(): void
    {
        $svc = $this->getService();
        $out = $svc->analizarPagos([]);
        $this->assertSame(0, $out['total_pagos']);
        $this->assertSame(0.0, $out['intervalo_promedio_dias']);
        $this->assertSame('insuficiente_datos', $out['patron_pago']);
    }

    public function testInsuficientesDatosMenosDeTresPagos(): void
    {
        $svc = $this->getService();
        $out = $svc->analizarPagos([
            ['fecha_pago' => '2025-01-06'],
            ['fecha_pago' => '2025-01-13'],
        ]);
        $this->assertSame(2, $out['total_pagos']);
        $this->assertSame('insuficiente_datos', $out['patron_pago']);
    }

    public function testAnalizarPagosUnSoloPago(): void
    {
        $svc = $this->getService();
        $out = $svc->analizarPagos([['fecha' => '2025-01-15']]);
        $this->assertSame(1, $out['total_pagos']);
        $this->assertNotNull($out['dia_mas_frecuente']);
    }
}
