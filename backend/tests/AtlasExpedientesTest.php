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
        $this->assertStringContainsString("if (startInput.value) params.set('fecha_inicio'", $this->view);
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
}
