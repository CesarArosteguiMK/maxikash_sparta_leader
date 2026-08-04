<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../services/LeonidasAgentService.php';

final class AdjudicacionFallbackDbStub
{
    public function queryOne(string $sql, array $params = []): ?array
    {
        if (str_contains($sql, 'asigna_creditos_adjudicacion')) {
            return [
                'id' => 88,
                'id_persona' => 120,
                'estatus' => '1',
                'fecha_asignacion' => '2026-08-04 10:00',
                'nombre_despacho' => 'CARLOS ANDRES SOLANO FERNANDEZ',
                'puesto_despacho' => 'Gestor',
            ];
        }
        return null;
    }
}

final class AdjudicacionFallbackSegStub
{
    public bool $encontrado = true;

    public function queryOne(string $sql, array $params = []): ?array
    {
        if (!$this->encontrado || !str_contains($sql, 'tbl_segundometro_semana')) {
            return null;
        }
        return [
            'Id_credito' => 2257556,
            'Nombre_cliente' => 'VALDEMAR BALDOMERO VELEZ',
            'Status_credito' => 'Vigente',
            'Dias_mora' => 8,
            'Saldo_total_capital' => '39929.30',
            'Bucket_Morosidad_Real' => 'c) 8 a 14 dias',
            'Sucursal' => 'CORPORATIVO',
        ];
    }
}

final class AdjudicacionCreditoFallbackTest extends TestCase
{
    public function testUsaSoloCorteSemanalVigenteCuandoS2NoEstaDisponible(): void
    {
        $seg = new AdjudicacionFallbackSegStub();
        $modelo = new Models\Adjudicacion(new AdjudicacionFallbackDbStub(), $seg);
        $metodo = new ReflectionMethod($modelo, 'buscarCreditoPorIdLocal');
        $metodo->setAccessible(true);

        $resultado = $metodo->invoke($modelo, 2257556, 'S2 temporalmente no disponible.');

        self::assertTrue($resultado['success']);
        self::assertSame('Segundometro semanal', $resultado['fuente_credito']);
        self::assertSame('Vigente', $resultado['status_credito']);
        self::assertSame(120, $resultado['asignacion']['id_persona']);
        self::assertStringContainsString('corte semanal vigente', $resultado['advertencia']);
    }

    public function testNoUsaHistoricoCuandoElCreditoNoEstaEnElCorteVigente(): void
    {
        $seg = new AdjudicacionFallbackSegStub();
        $seg->encontrado = false;
        $modelo = new Models\Adjudicacion(new AdjudicacionFallbackDbStub(), $seg);
        $metodo = new ReflectionMethod($modelo, 'buscarCreditoPorIdLocal');
        $metodo->setAccessible(true);

        $resultado = $metodo->invoke($modelo, 1809373, 'S2 temporalmente no disponible.');

        self::assertFalse($resultado['success']);
        self::assertStringContainsString('corte semanal vigente', $resultado['message']);
    }
}
