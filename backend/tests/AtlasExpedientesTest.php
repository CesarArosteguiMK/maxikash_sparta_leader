<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/controllers/Atlas.php';

final class AtlasExpedientesTest extends TestCase
{
    private string $view;
    private string $controllerSource;

    protected function setUp(): void
    {
        $backend = dirname(__DIR__);
        $this->view = (string)file_get_contents($backend . '/views/atlas_expedientes.php');
        $this->controllerSource = (string)file_get_contents($backend . '/controllers/Atlas.php');
    }

    public function testListRequestsHistoricalS2CreditWithoutAStageSelector(): void
    {
        $this->assertStringContainsString('Hist&oacute;rico de activaciones desde el inicio de Maxikash.', $this->view);
        $this->assertStringContainsString('Cr&eacute;ditos activos en S2Credit', $this->view);
        $this->assertStringNotContainsString('id="atlasExpedientesStage"', $this->view);
        $this->assertStringNotContainsString("params.set('etapa'", $this->view);
        $this->assertStringContainsString("const activationDate = String(row.fecha_activacion_s2", $this->view);
    }

    public function testListingLoadsOnceAndSearchesAndFiltersInTheBrowser(): void
    {
        $this->assertStringContainsString("fetch('/Atlas/getExpedientes?all=1'", $this->view);
        $this->assertSame(1, substr_count($this->view, "fetch('/Atlas/getExpedientes?all=1'"));
        $this->assertStringContainsString('serverSide: false', $this->view);
        $this->assertStringContainsString('deferRender: true', $this->view);
        $this->assertStringContainsString('data: []', $this->view);
        $this->assertStringContainsString('expedienteMatchesFilters', $this->view);
        $this->assertStringContainsString("table.rows({ search: 'applied' })", $this->view);
        $this->assertStringContainsString("dataTableFilters.push(expedienteMatchesFilters)", $this->view);
        $this->assertStringNotContainsString('serverSide: true', $this->view);
        $this->assertStringNotContainsString('table.ajax.reload', $this->view);
        $this->assertStringNotContainsString("params.set('estatus'", $this->view);
        $this->assertStringNotContainsString("params.set('fk_sucursal'", $this->view);
        $this->assertStringNotContainsString("params.set('search'", $this->view);
    }

    public function testCompleteListingIsAggregatedBySpartaForOneBrowserResponse(): void
    {
        $this->assertStringContainsString("(int)(\$_GET['all'] ?? 0) === 1", $this->controllerSource);
        $this->assertStringContainsString("['completo' => 1, 'compacto' => 1, 'actualizar' => 1]", $this->controllerSource);
        $this->assertStringContainsString('normalizeListingRows', $this->view);
        $this->assertStringContainsString("data.formato === 'columnar'", $this->view);
        $this->assertStringContainsString('payload.datos || payload.data || {}', $this->view);
        $this->assertStringContainsString("CURLOPT_ENCODING => ''", $this->controllerSource);
        $this->assertStringNotContainsString('atlasAdminExpedientesCompleto', $this->controllerSource);
        $this->assertStringNotContainsString('La carga completa supera el limite operativo permitido.', $this->controllerSource);
        $this->assertStringContainsString('streamAtlasExpedientesSnapshot($bulkQuery)', $this->controllerSource);
        $this->assertStringContainsString("'Accept-Encoding: gzip'", $this->controllerSource);
        $this->assertStringContainsString('CURLOPT_HTTP_CONTENT_DECODING => false', $this->controllerSource);
        $this->assertStringContainsString("(\$responseHeaders['x-atlas-format'] ?? '') !== 'columnar'", $this->controllerSource);
        $this->assertStringContainsString("header('Content-Encoding: ' . \$responseHeaders['content-encoding'])", $this->controllerSource);
        $this->assertStringContainsString('Pulsa Actualizar para consultar los cambios aplicados.', $this->view);
    }

    public function testLayoutUploadStaysServerToServer(): void
    {
        $this->assertStringContainsString("fetch('/Atlas/importarExpedientes'", $this->view);
        $this->assertStringContainsString(
            "'/api/atlas/admin/expedientes/importaciones'",
            $this->controllerSource
        );
        $this->assertStringNotContainsString('localStorage.api_token', $this->view);
        $this->assertStringContainsString('atlasExpedientesApiResponse', $this->controllerSource);
    }

    public function testExcelLayoutNormalizesTheThreeBusinessStatuses(): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ID crédito', 'Estatus expediente', 'Motivo / incidencia', 'Comentarios'],
            [1001, 'Expediente recolectado', '', 'Completo'],
            [1002, 'Expediente no recolectado', 'No estaba disponible', 'Reintentar'],
            [1003, 'Incidencia', 'Factura con moto diferente', 'Validar con sucursal'],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'atlas_exp_test_');
        $this->assertNotFalse($path);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $reflection = new ReflectionClass(\controllers\Atlas::class);
            $controller = $reflection->newInstanceWithoutConstructor();
            $method = $reflection->getMethod('leerExpedientesExcel');
            $method->setAccessible(true);
            $rows = $method->invoke($controller, $path);

            $this->assertSame(['entregado', 'no_entregado', 'incidencia'], array_column($rows, 'accion'));
            $this->assertSame([1001, 1002, 1003], array_column($rows, 'credito_id'));
            $this->assertSame('Factura con moto diferente', $rows[2]['motivo']);
        } finally {
            @unlink($path);
        }
    }

    public function testLayoutRejectsAnIncidentWithoutAReason(): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ID credito', 'Estatus expediente'],
            [1001, 'Incidencia'],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'atlas_exp_test_');
        $this->assertNotFalse($path);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $reflection = new ReflectionClass(\controllers\Atlas::class);
            $controller = $reflection->newInstanceWithoutConstructor();
            $method = $reflection->getMethod('leerExpedientesExcel');
            $method->setAccessible(true);

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('captura el motivo o incidencia');
            $method->invoke($controller, $path);
        } finally {
            @unlink($path);
        }
    }

    public function testChangeOriginIsDisplayedAndCannotBeSpoofedFromSparta(): void
    {
        $this->assertStringContainsString('const changeOriginDefinition', $this->view);
        $this->assertStringContainsString('Cambio realizado mediante carga de layout', $this->view);
        $this->assertStringContainsString("data: 'origen_cambio'", $this->view);
        $this->assertStringContainsString("defaultContent: 'legacy'", $this->view);
        $this->assertStringContainsString('changeOriginBadge(event.origen_cambio)', $this->view);
        $this->assertStringContainsString('changeOriginBadge(expediente.origen_cambio)', $this->view);
        $this->assertStringContainsString(
            "unset(\$payload['credito_id'], \$payload['origen_cambio'], \$payload['document_change_source']);",
            $this->controllerSource
        );
    }

    public function testListingShowsTheManagerAndKeepsTheClientInsideTheDetailModal(): void
    {
        $this->assertStringContainsString('<th>Gestor a cargo</th>', $this->view);
        $this->assertStringNotContainsString('<th>Cliente</th>', $this->view);
        $this->assertStringContainsString("row.gestor_nombre || 'Sin gestor asignado'", $this->view);
        $this->assertStringContainsString('id="atlasExpedientesDetailGestor"', $this->view);
        $this->assertStringContainsString("detailItem('Cliente', expediente.cliente_nombre)", $this->view);
        $this->assertStringContainsString("expediente.gestor_nombre || 'Sin gestor asignado'", $this->view);
    }
}
