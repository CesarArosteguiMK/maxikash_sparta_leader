<?php

use Models\AtlasVentas;
use PHPUnit\Framework\TestCase;
use Services\AtlasVentasReportService;

require_once dirname(__DIR__) . '/core/Model.php';
require_once dirname(__DIR__) . '/models/AtlasVentas.php';
require_once dirname(__DIR__) . '/services/AtlasVentasReportService.php';

final class AtlasVentasTest extends TestCase
{
    public function testGeneralRulePrioritizesPorDispersar(): void
    {
        $selection = AtlasVentas::seleccionarVenta([
            'fk_distribuidor' => 10,
            'fecha_paso_por_dispersar' => '2026-07-12 10:00:00',
            'fecha_paso_dispersado' => '2026-07-10 09:00:00',
            'fecha_paso_s2credit' => '2026-07-08 08:00:00',
        ], [], '2026-07-01', '2026-07-31');

        self::assertNotNull($selection);
        self::assertSame('POR_DISPERSAR', $selection['criterio_fecha_venta']);
        self::assertSame('2026-07-12 10:00:00', $selection['fecha_contabilizacion_venta']);
    }

    public function testGeneralRuleFallsBackToS2Credit(): void
    {
        $selection = AtlasVentas::seleccionarVenta([
            'fk_distribuidor' => 10,
            'fecha_paso_por_dispersar' => null,
            'fecha_paso_dispersado' => null,
            'fecha_paso_s2credit' => '2026-07-18 12:30:00',
        ], [], '2026-07-01', '2026-07-31');

        self::assertNotNull($selection);
        self::assertSame('S2CREDIT', $selection['criterio_fecha_venta']);
    }

    public function testSpecificActivationRuleUsesS2Credit(): void
    {
        $selection = AtlasVentas::seleccionarVenta([
            'fk_distribuidor' => 236,
            'fecha_paso_por_dispersar' => '2026-07-20 10:00:00',
            'fecha_paso_dispersado' => '2026-07-21 10:00:00',
            'fecha_paso_s2credit' => '2026-07-15 08:00:00',
        ], [[
            'id' => 9,
            'fk_distribuidor' => 236,
            'criterio_fecha' => 'ACTIVACION',
            'etapa_requerida' => 'S2CREDIT',
            'estatus' => 1,
            'vigencia_desde' => '1970-01-01',
            'vigencia_hasta' => null,
        ]], '2026-07-01', '2026-07-31');

        self::assertNotNull($selection);
        self::assertSame('ACTIVACION_S2', $selection['criterio_fecha_venta']);
        self::assertSame('2026-07-15 08:00:00', $selection['fecha_contabilizacion_venta']);
        self::assertSame(9, $selection['regla']['id']);
    }

    public function testInactiveSpecificRuleExcludesTheSale(): void
    {
        $selection = AtlasVentas::seleccionarVenta([
            'fk_distribuidor' => 824,
            'fecha_paso_por_dispersar' => '2026-07-20 10:00:00',
            'fecha_paso_dispersado' => '2026-07-21 10:00:00',
            'fecha_paso_s2credit' => '2026-07-15 08:00:00',
        ], [[
            'id' => 10,
            'fk_distribuidor' => 824,
            'criterio_fecha' => 'ACTIVACION',
            'etapa_requerida' => 'POR DISPERSAR',
            'estatus' => 0,
            'vigencia_desde' => '1970-01-01',
            'vigencia_hasta' => null,
        ]], '2026-07-01', '2026-07-31');

        self::assertNull($selection);
    }

    public function testFilterRejectsAnInvertedRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('fecha inicial');
        AtlasVentas::normalizarFiltros([
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-07-01',
        ]);
    }

    public function testReportKeepsTheReceivedLayoutHeadersAndOrder(): void
    {
        $service = new AtlasVentasReportService();
        $spreadsheet = $service->crear([[
            'id_persona' => 1522033,
            'id_oferta' => 2388759,
            'nombre_cliente' => 'ESTEBAN MARINERO MENDEZ',
            'fecha_contabilizacion_venta' => '2026-07-15 08:00:00',
            'sucursal' => 'ZMOTO PASO DEL MACHO',
            'distribuidor' => 'DISTRIBUIDOR DEMO',
            'fecha_oferta' => '2026-07-01 09:00:00',
            'fecha_etapa_actual' => '2026-07-20 11:00:00',
            'etapa' => 'S2CREDIT',
            'precio_moto' => 30000,
            'enganche' => 6650,
            'monto_financiar' => 23350,
            'semanas' => '52',
            'oferta' => '1',
            'modelo_moto' => 'MODELO',
            'marca_moto' => 'MARCA',
            'usuario' => 'vendedor.demo',
            'nombre_vendedor' => 'VENDEDOR DEMO',
            'pk_sucursal' => 1409,
            'fk_distribuidor' => 200,
        ]]);

        try {
            $sheet = $spreadsheet->getActiveSheet();
            self::assertSame('Hoja1', $sheet->getTitle());
            self::assertSame(
                [AtlasVentasReportService::HEADERS],
                $sheet->rangeToArray('A1:T1', null, true, false)
            );
            self::assertSame('usuario ', $sheet->getCell('Q1')->getValue());
            self::assertCount(1, $sheet->getTableCollection());
            self::assertSame(2388759, $sheet->getCell('B2')->getValue());
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    public function testSpartaExposesTheViewQueryAndExportRoutes(): void
    {
        $backend = dirname(__DIR__);
        $controller = (string)file_get_contents($backend . '/controllers/Atlas.php');
        $menu = (string)file_get_contents($backend . '/core/View.php');
        $view = (string)file_get_contents($backend . '/views/atlas_ventas.php');

        self::assertStringContainsString('public function ventas()', $controller);
        self::assertStringContainsString('public function getVentas()', $controller);
        self::assertStringContainsString('public function exportarVentas()', $controller);
        self::assertStringContainsString("'/Atlas/ventas'", $menu);
        self::assertStringContainsString('fetch(`/Atlas/getVentas?', $view);
        self::assertStringContainsString('/Atlas/exportarVentas?', $view);
    }
}
