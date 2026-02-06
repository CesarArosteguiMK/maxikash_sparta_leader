<?php

/**
 * Tests: Haversine, última ubicación, aperturas últimos 5 días.
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/SpatialAnalyticsService.php';

class SpatialAnalyticsServiceTest extends TestCase
{
    private function getService(): \Services\SpatialAnalyticsService
    {
        return new \Services\SpatialAnalyticsService();
    }

    public function testHaversineCorrecto(): void
    {
        $svc = $this->getService();
        $lat1 = 19.43;
        $lon1 = -99.13;
        $lat2 = 19.44;
        $lon2 = -99.14;
        $d = $svc->haversineMetros($lat1, $lon1, $lat2, $lon2);
        $this->assertIsFloat($d);
        $this->assertGreaterThan(0, $d);
        $this->assertLessThan(20000, $d);
        $mismoPunto = $svc->haversineMetros($lat1, $lon1, $lat1, $lon1);
        $this->assertEqualsWithDelta(0.0, $mismoPunto, 0.01);
    }

    public function testCalcularDistanciasCasa(): void
    {
        $svc = $this->getService();
        $domicilio = ['id' => 'casa', 'lat' => 19.43, 'lng' => -99.13, 'label' => 'Casa'];
        $ubicaciones = [
            ['id' => 'u0', 'lat' => 19.43, 'lng' => -99.13, 'label' => 'A', 'visitas_count' => 5, 'ultima_fecha' => '2026-01-01'],
            ['id' => 'u1', 'lat' => 19.44, 'lng' => -99.13, 'label' => 'B', 'visitas_count' => 2, 'ultima_fecha' => null],
        ];
        $out = $svc->calcularDistanciasCasa($ubicaciones, $domicilio);
        $this->assertCount(2, $out);
        $this->assertEqualsWithDelta(0.0, $out[0]['distancia_m'], 1.0);
        $this->assertGreaterThan(0, $out[1]['distancia_m']);
        $this->assertArrayHasKey('id', $out[0]);
        $this->assertArrayHasKey('label', $out[0]);
        $this->assertArrayHasKey('distancia_m', $out[0]);
        $this->assertArrayHasKey('visitas_count', $out[0]);
    }

    public function testUltimaUbicacionApp(): void
    {
        $svc = $this->getService();
        $eventos = [
            ['lat' => 19.43, 'lng' => -99.13, 'timestamp' => time() - 86400],
            ['lat' => 19.44, 'lng' => -99.14, 'timestamp' => time() - 3600],
        ];
        $ult = $svc->ultimaUbicacionApp($eventos);
        $this->assertNotEmpty($ult);
        $this->assertEqualsWithDelta(19.44, $ult['lat'], 0.01);
        $this->assertEqualsWithDelta(-99.14, $ult['lng'], 0.01);
        $ultConCasa = $svc->ultimaUbicacionApp($eventos, ['lat' => 19.43, 'lng' => -99.13]);
        $this->assertArrayHasKey('distancia_a_casa_m', $ultConCasa);
    }

    public function testUltimaUbicacionAppVacio(): void
    {
        $svc = $this->getService();
        $ult = $svc->ultimaUbicacionApp([]);
        $this->assertSame([], $ult);
    }

    public function testAperturasUltimos5Dias(): void
    {
        $svc = $this->getService();
        $ahora = time();
        $eventos = [
            ['lat' => 19.43, 'lng' => -99.13, 'timestamp' => $ahora - 86400],
            ['lat' => 19.43, 'lng' => -99.13, 'timestamp' => $ahora - 7200],
            ['lat' => 19.44, 'lng' => -99.14, 'timestamp' => $ahora - 3600],
        ];
        $out = $svc->aperturasUltimosDias($eventos, 5);
        $this->assertArrayHasKey('total_aperturas', $out);
        $this->assertArrayHasKey('aperturas_por_ubicacion', $out);
        $this->assertArrayHasKey('ubicaciones_distintas', $out);
        $this->assertArrayHasKey('resumen_por_dia', $out);
        $this->assertSame(3, $out['total_aperturas']);
        $this->assertGreaterThanOrEqual(1, $out['ubicaciones_distintas']);
        $this->assertIsArray($out['aperturas_por_ubicacion']);
        $this->assertIsArray($out['resumen_por_dia']);
    }

    public function testAperturasUltimosDiasExcluyeAntiguos(): void
    {
        $svc = $this->getService();
        $ahora = time();
        $eventos = [
            ['lat' => 19.43, 'lng' => -99.13, 'timestamp' => $ahora - 10 * 86400],
        ];
        $out = $svc->aperturasUltimosDias($eventos, 5);
        $this->assertSame(0, $out['total_aperturas']);
        $this->assertSame(0, $out['ubicaciones_distintas']);
    }
}
