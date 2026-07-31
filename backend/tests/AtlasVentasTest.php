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

    public function testSaleKeepsDispersionDateSeparateFromAccountingDate(): void
    {
        $method = new ReflectionMethod(AtlasVentas::class, 'normalizarVenta');
        $method->setAccessible(true);
        $sale = $method->invoke(null, [
            'id_persona' => 387902,
            'id_oferta' => 2219592,
            'cliente_nombre_completo' => 'CESAR JAHIR CORTES ROSALES',
            'fecha_dispersion' => '2026-07-13 20:21:35',
        ], [
            'fecha_contabilizacion_venta' => '2026-07-12 10:00:00',
            'criterio_fecha_venta' => 'POR_DISPERSAR',
            'regla' => ['id' => null],
        ]);

        self::assertSame(2219592, $sale['id_oferta']);
        self::assertSame('CESAR JAHIR CORTES ROSALES', $sale['nombre_cliente']);
        self::assertSame('2026-07-13 20:21:35', $sale['fecha_dispersion']);
        self::assertSame('2026-07-12 10:00:00', $sale['fecha_contabilizacion_venta']);
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

    public function testCurrentStageFilterIsNormalizedForExactMatching(): void
    {
        $filters = AtlasVentas::normalizarFiltros([
            'fecha_inicio' => '2026-07-01',
            'fecha_fin' => '2026-07-31',
            'etapa' => '  Por dispersar  ',
        ]);

        self::assertSame('POR DISPERSAR', $filters['etapa']);
    }

    public function testHistoricalFilterStartsAtTheBeginningWithoutTheTwentyFourMonthLimit(): void
    {
        $filters = AtlasVentas::normalizarFiltros([
            'historico' => 1,
            'fecha_fin' => '2026-07-31',
        ]);

        self::assertTrue($filters['historico']);
        self::assertSame('1970-01-01', $filters['fecha_inicio']);
        self::assertSame('2026-07-31', $filters['fecha_fin']);
    }

    public function testReportKeepsTheReceivedLayoutHeadersAndOrder(): void
    {
        $service = new AtlasVentasReportService();
        $spreadsheet = $service->crear([[
            'id_persona' => 387902,
            'id_oferta' => 2219592,
            'nombre_cliente' => 'CESAR JAHIR CORTES ROSALES',
            'fecha_dispersion' => '2026-07-13 20:21:35',
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
            self::assertSame(2219592, $sheet->getCell('B2')->getValue());
            self::assertSame('13/07/2026 20:21:35', $sheet->getCell('D2')->getFormattedValue());
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    public function testSalesViewUsesTheCondensedReadableColumnOrder(): void
    {
        ob_start();
        include dirname(__DIR__) . '/views/atlas_ventas.php';
        $view = (string)ob_get_clean();

        preg_match_all('/<th>(.*?)<\/th>/s', $view, $matches);
        $headers = array_map(
            static fn(string $header): string => html_entity_decode(strip_tags($header), ENT_QUOTES, 'UTF-8'),
            $matches[1] ?? []
        );

        self::assertCount(6, $headers);
        self::assertSame([
            'Cliente',
            'Fecha de dispersión',
            'Sucursal / Distribuidor',
            'Fecha de oferta',
            'Fecha de etapa actual',
            'Detalles',
        ], $headers);
        self::assertNotContains('ID de persona', $headers);
        self::assertNotContains('ID de oferta', $headers);
        self::assertNotContains('Etapa', $headers);
        self::assertStringContainsString(
            'ID de oferta: ${escapeHtml(row.id_oferta || \'Sin dato\')}',
            $view
        );
        self::assertMatchesRegularExpression(
            '/formatDate\(row\.fecha_etapa_actual\).*?stageHtml\(row\.etapa\).*?<\/td>/s',
            $view
        );
        self::assertMatchesRegularExpression(
            '/<span>Sucursal<\/span>.*?row\.sucursal.*?atlas-sales-location-divider.*?<span>Distribuidor<\/span>.*?row\.distribuidor.*?<\/td>/s',
            $view
        );
        self::assertStringContainsString('formatDate(row.fecha_dispersion)', $view);
        self::assertStringContainsString("S2CREDIT: { label: 'S2Credit', icon: 'fa-solid fa-credit-card'", $view);
        self::assertStringContainsString("'POR DISPERSAR': { label: 'Por dispersar', icon: 'fa-solid fa-hourglass-half'", $view);
        self::assertStringContainsString("DISPERSADO: { label: 'Dispersado', icon: 'fa-solid fa-money-bill-transfer'", $view);
        self::assertStringContainsString("CANCELADO: { label: 'Cancelado', icon: 'fa-solid fa-circle-xmark'", $view);
        self::assertStringContainsString('id="atlasSalesDetailsModal"', $view);
        self::assertStringContainsString('data-bs-target="#atlasSalesDetailsModal"', $view);
        self::assertStringContainsString('data-atlas-sale-detail="${row._detailIndex}"', $view);
        self::assertStringContainsString("elements.rows.addEventListener('click'", $view);
        foreach ([
            'row.precio_moto',
            'row.enganche',
            'row.monto_financiar',
            'row.semanas',
            'row.oferta',
            'row.modelo_moto',
            'row.marca_moto',
            'row.usuario',
            'row.nombre_vendedor',
            'row.pk_sucursal',
            'row.fk_distribuidor',
        ] as $detailField) {
            self::assertStringContainsString($detailField, $view);
        }
        self::assertStringNotContainsString('atlas-sales-rule-tag', $view);
        foreach (['fa-wallet', 'fa-motorcycle', 'fa-user-tie', 'fa-link'] as $modalIcon) {
            self::assertStringContainsString($modalIcon, $view);
        }
    }

    public function testSpartaExposesTheViewQueryAndExportRoutes(): void
    {
        $backend = dirname(__DIR__);
        $controller = (string)file_get_contents($backend . '/controllers/Atlas.php');
        $menu = (string)file_get_contents($backend . '/core/View.php');
        $model = (string)file_get_contents($backend . '/models/AtlasVentas.php');
        $view = (string)file_get_contents($backend . '/views/atlas_ventas.php');

        self::assertStringContainsString('public function ventas()', $controller);
        self::assertStringContainsString('public function getVentas()', $controller);
        self::assertStringContainsString('public function exportarVentas()', $controller);
        self::assertStringContainsString("\$this->set('layoutVendorLite', true)", $controller);
        self::assertStringContainsString("\$this->set('layoutPreloadSweetAlert', true)", $controller);
        self::assertStringContainsString("\$this->set('layoutSelect2', true)", $controller);
        self::assertStringNotContainsString("header('Location: /Atlas/ventas'", $controller);
        self::assertStringContainsString('AtlasVentasDAO::precargar($forzarActualizacion)', $controller);
        self::assertStringContainsString('jsonComprimido($response)', $controller);
        self::assertStringContainsString("'/Atlas/ventas'", $menu);
        self::assertStringContainsString('if ($layoutPreloadSweetAlert)', $menu);
        self::assertStringContainsString('if (!$layoutPreloadSweetAlert)', $menu);
        self::assertStringContainsString('window.__atlasVentasPreload', $menu);
        self::assertLessThan(
            strpos($menu, '<!-- Layout wrapper -->'),
            strpos($menu, 'window.__atlasVentasPreload')
        );
        self::assertStringContainsString("'/Atlas/getVentas?carga_completa=1'", $view);
        self::assertStringContainsString('fetch(preloadUrl', $view);
        self::assertStringContainsString("mode: 'range'", $view);
        self::assertStringContainsString("state.allRows.filter", $view);
        self::assertStringContainsString("elements.search.addEventListener('input', scheduleFilter)", $view);
        self::assertStringContainsString('id="atlasSalesStage"', $view);
        self::assertStringContainsString('atlas-sales-filters-secondary', $view);
        self::assertStringContainsString("minimumResultsForSearch: 0", $view);
        self::assertStringContainsString("&& (!stage || row._stage === stage)", $view);
        self::assertStringContainsString("state.start = '';", $view);
        self::assertStringContainsString("state.end = '';", $view);
        self::assertStringContainsString('>Limpiar', $view);
        self::assertStringContainsString('>Listo', $view);
        self::assertStringContainsString('atlasSalesRefresh', $view);
        self::assertStringContainsString('const preloadStartedAt = showPreload();', $view);
        self::assertStringContainsString('await closePreload(preloadStartedAt);', $view);
        self::assertStringContainsString("document.addEventListener('DOMContentLoaded', initializeAtlasSales", $view);
        self::assertStringContainsString('atlas-sales-inline-loader', $view);
        self::assertSame(1, substr_count($view, 'fetch('));
        self::assertStringNotContainsString('atlasSalesConsult', $view);
        self::assertStringNotContainsString('atlasSalesStart', $view);
        self::assertStringNotContainsString('atlasSalesEnd', $view);
        self::assertSame(1, substr_count($view, 'history.replaceState'));
        self::assertStringContainsString("window.history.replaceState(null, '', '/Atlas/ventas')", $view);
        self::assertStringNotContainsString('restoreFiltersFromUrl', $view);
        self::assertStringNotContainsString('new URLSearchParams(location.search)', $view);
        self::assertStringContainsString('atlas_ventas_precarga_cache', $model);
        self::assertStringContainsString("'ventas:v3:historico'", $model);
        self::assertStringContainsString("WHERE etapa IN ('S2CREDIT', 'POR DISPERSAR', 'DISPERSADO')", $model);
        self::assertStringContainsString("UPPER(TRIM(o.etapa)) = :etapa_actual", $model);
        self::assertStringContainsString('/Atlas/exportarVentas?', $view);
        self::assertStringContainsString("link.download = '';", $view);
    }
}
