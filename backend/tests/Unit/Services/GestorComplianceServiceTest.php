<?php

/**
 * Tests: visitas cercanas (<100m), cumplimiento.
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/GestorComplianceService.php';

class GestorComplianceServiceTest extends TestCase
{
    private function getService(): \Services\GestorComplianceService
    {
        return new \Services\GestorComplianceService();
    }

    public function testVisitasCercanasMenos100m(): void
    {
        $svc = $this->getService();
        $ubicUsuario = [['lat' => 19.43, 'lng' => -99.13]];
        $eventosGestor = [
            ['lat' => 19.43, 'lng' => -99.13, 'timestamp' => time()],
            ['lat' => 19.43001, 'lng' => -99.13, 'timestamp' => time() - 3600],
        ];
        $out = $svc->verificarCercaniaGestor($eventosGestor, $ubicUsuario);
        $this->assertArrayHasKey('visitas_cercanas', $out);
        $this->assertArrayHasKey('visitas_lejanas', $out);
        $this->assertArrayHasKey('porcentaje_cumplimiento', $out);
        $this->assertArrayHasKey('detalles', $out);
        $this->assertArrayHasKey('alertas', $out);
        $this->assertIsInt($out['visitas_cercanas']);
        $this->assertGreaterThanOrEqual(1, $out['visitas_cercanas']);
        $this->assertSame(100.0, $out['porcentaje_cumplimiento']);
    }

    public function testVisitasLejanas(): void
    {
        $svc = $this->getService();
        $ubicUsuario = [['lat' => 19.43, 'lng' => -99.13]];
        $eventosGestor = [
            ['lat' => 20.0, 'lng' => -100.0, 'timestamp' => time()],
        ];
        $out = $svc->verificarCercaniaGestor($eventosGestor, $ubicUsuario);
        $this->assertSame(1, $out['visitas_lejanas']);
        $this->assertSame(0.0, $out['porcentaje_cumplimiento']);
    }

    public function testCumplimientoCorrecto(): void
    {
        $svc = $this->getService();
        $ubicUsuario = [['lat' => 19.43, 'lng' => -99.13]];
        $eventosGestor = [
            ['lat' => 19.43, 'lng' => -99.13, 'timestamp' => time()],
            ['lat' => 20.0, 'lng' => -100.0, 'timestamp' => time() - 3600],
        ];
        $out = $svc->verificarCercaniaGestor($eventosGestor, $ubicUsuario);
        $this->assertSame(50.0, $out['porcentaje_cumplimiento']);
        $this->assertSame(1, $out['visitas_cercanas']);
        $this->assertSame(1, $out['visitas_lejanas']);
    }

    public function testSinEventosGestor(): void
    {
        $svc = $this->getService();
        $out = $svc->verificarCercaniaGestor([], [['lat' => 19.43, 'lng' => -99.13]]);
        $this->assertNull($out['porcentaje_cumplimiento']);
        $this->assertNotEmpty($out['alertas']);
    }

    public function testSinUbicacionesUsuario(): void
    {
        $svc = $this->getService();
        $out = $svc->verificarCercaniaGestor(
            [['lat' => 19.43, 'lng' => -99.13, 'timestamp' => time()]],
            []
        );
        $this->assertNull($out['porcentaje_cumplimiento']);
        $this->assertSame(1, $out['visitas_lejanas']);
    }
}
