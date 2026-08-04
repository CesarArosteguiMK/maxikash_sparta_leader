<?php

use PHPUnit\Framework\TestCase;

final class AtlasSucursalesAntiguedadTest extends TestCase
{
    private string $model;
    private string $view;

    protected function setUp(): void
    {
        $backend = dirname(__DIR__);
        $this->model = (string)file_get_contents($backend . '/models/Atlas.php');
        $this->view = (string)file_get_contents($backend . '/views/atlas.php');
    }

    public function testBranchesLoadedByBudgetAndMissingFromCatalogAreIncluded(): void
    {
        $this->assertStringContainsString('FROM atlas_presupuesto_sucursal_detalle pd', $this->model);
        $this->assertStringContainsString('WHERE NOT EXISTS (', $this->model);
        $this->assertStringContainsString('1 AS pendiente_catalogo', $this->model);
        $this->assertStringContainsString('COLLATE utf8mb4_unicode_ci', $this->model);
        $this->assertStringContainsString("'pendientes_catalogo' => count(\$pendientesCatalogo)", $this->model);
    }

    public function testAgeBucketsCoverEveryPendingBranchWithoutOverlap(): void
    {
        $this->assertStringContainsString("< 7 THEN 'nuevas'", $this->model);
        $this->assertStringContainsString("< 30 THEN 'semana'", $this->model);
        $this->assertStringContainsString("ELSE 'mes'", $this->model);
        $this->assertStringContainsString('DATEDIFF(CURDATE(), DATE(nuevas.fecha_carga_excel))', $this->model);
    }

    public function testNewBranchesAreTheDefaultViewAndTheFullCatalogRemainsAvailable(): void
    {
        $this->assertStringContainsString('<option value="nuevas" selected>Nuevas</option>', $this->view);
        $this->assertStringContainsString('<option value="semana">Hace 1 semana</option>', $this->view);
        $this->assertStringContainsString('<option value="mes">Hace 1 mes</option>', $this->view);
        $this->assertStringContainsString('<option value="todas">Todas las sucursales</option>', $this->view);
        $this->assertStringContainsString('function sucursalesPorAntiguedad()', $this->view);
    }

    public function testPendingBranchCanBeCompletedUsingItsBudgetPrimaryKey(): void
    {
        $this->assertStringContainsString('data-atlas-completar-sucursal', $this->view);
        $this->assertStringContainsString('Completar sucursal de Excel', $this->view);
        $this->assertStringContainsString('$fkSucursalSolicitada', $this->model);
        $this->assertStringContainsString('$fkSucursal = $fkSucursalSolicitada;', $this->model);
        $this->assertStringContainsString('La PK indicada no corresponde a una sucursal pendiente cargada desde Presupuestos.', $this->model);
    }

    public function testCompletingTheSamePendingRowDoesNotTriggerTheDuplicateNameGuard(): void
    {
        $this->assertStringContainsString("if (actualFk && String(row.fk_sucursal || '') === actualFk) return false;", $this->view);
        $this->assertStringContainsString("if (fkActual && String(row.fk_sucursal || '') === fkActual) return false;", $this->view);
    }
}
