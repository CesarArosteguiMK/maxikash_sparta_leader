<?php

use PHPUnit\Framework\TestCase;

final class AtlasPresupuestoReajusteMasivoTest extends TestCase
{
    private string $view;
    private string $controller;
    private string $model;

    protected function setUp(): void
    {
        $backend = dirname(__DIR__);
        $this->view = (string)file_get_contents($backend . '/views/atlas_presupuestos.php');
        $this->controller = (string)file_get_contents($backend . '/controllers/Atlas.php');
        $this->model = (string)file_get_contents($backend . '/models/Atlas.php');
    }

    public function testUploadOnlyAnalyzesAndKeepsTheParsedRowsOnTheServer(): void
    {
        $this->assertStringContainsString(
            'AtlasDAO::analizarReajustePresupuestoMensual',
            $this->controller
        );
        $this->assertStringContainsString(
            "\$_SESSION['atlas_presupuesto_reajustes']",
            $this->controller
        );
        $this->assertStringContainsString("'filas' => \$filas", $this->controller);
        $this->assertStringContainsString('Analizar archivo', $this->view);
    }

    public function testLargeAnalysisHasEstimatedProgressAndPagedRendering(): void
    {
        $this->assertStringContainsString('Tiempo estimado:', $this->view);
        $this->assertStringContainsString('atlasPresAnalisisProgreso', $this->view);
        $this->assertStringContainsString('Ya falta poco, estamos preparando el comparativo', $this->view);
        $this->assertStringContainsString('comparativoPorPagina: 50', $this->view);
        $this->assertStringContainsString('registros.slice(inicio, inicio + this.comparativoPorPagina)', $this->view);
        $this->assertStringContainsString('renderPaginacionComparativoReajuste', $this->view);
        $this->assertStringContainsString('window.requestAnimationFrame', $this->view);
        $this->assertStringContainsString('retry: 0,', $this->view);
    }

    public function testBulkAnalysisReusesTheDistributorStatusAlreadyLoadedWithTheCatalog(): void
    {
        $this->assertStringContainsString("COALESCE(d.estatus, 'activo') AS distribuidor_estatus", $this->model);
        $this->assertStringContainsString('?array $distribuidor = null', $this->model);
        $this->assertStringContainsString(
            "\$distribuidor['estatus'] ?? \$distribuidor['distribuidor_estatus'] ?? null",
            $this->model
        );
        $this->assertGreaterThanOrEqual(2, substr_count($this->model, '$sucursalReferencia'));
    }

    public function testConfirmationUsesAnOpaqueTokenAndAnExplicitReason(): void
    {
        $this->assertStringContainsString(
            'public function confirmarReajustePresupuesto()',
            $this->controller
        );
        $this->assertStringContainsString(
            'AtlasDAO::confirmarReajustePresupuestoMensual',
            $this->controller
        );
        $this->assertStringContainsString(
            "endpoint: '/Atlas/confirmarReajustePresupuesto'",
            $this->view
        );
        $this->assertStringContainsString('Confirmar reajuste', $this->view);
        $this->assertStringContainsString('motivo', $this->view);
    }

    public function testConfirmationIsNeverRetriedAndOnlyChangedRowsArePersisted(): void
    {
        $confirmationStart = strpos($this->view, "endpoint: '/Atlas/confirmarReajustePresupuesto'");
        $confirmationEnd = strpos($this->view, 'cerrarModalComparativo()', $confirmationStart);
        $this->assertNotFalse($confirmationStart);
        $this->assertNotFalse($confirmationEnd);
        $confirmationBlock = substr($this->view, $confirmationStart, $confirmationEnd - $confirmationStart);
        $this->assertStringContainsString('retry: 0,', $confirmationBlock);

        $importStart = strpos($this->model, 'public static function importarPresupuestoMensual');
        $skipUnchanged = strpos(
            $this->model,
            'if ($esReajusteMasivo && !$cambioMeta && !$cambioDatos)',
            $importStart
        );
        $catalogUpdate = strpos($this->model, 'UPDATE atlas_catalogo_sucursales', $skipUnchanged);
        $detailUpsert = strpos($this->model, 'INSERT INTO atlas_presupuesto_sucursal_detalle', $skipUnchanged);
        $this->assertNotFalse($importStart);
        $this->assertNotFalse($skipUnchanged);
        $this->assertNotFalse($catalogUpdate);
        $this->assertNotFalse($detailUpsert);
        $this->assertLessThan($catalogUpdate, $skipUnchanged);
        $this->assertLessThan($detailUpsert, $skipUnchanged);
        $this->assertStringContainsString("'registros_persistidos' => \$registrosPersistidos", $this->model);
        $this->assertStringContainsString("'registros_sin_cambios' => max(0, \$importadas - \$registrosPersistidos)", $this->model);
        $this->assertStringContainsString('>Aplicadas</span>', $this->view);
        $this->assertStringContainsString('resumen.registros_persistidos', $this->view);
    }

    public function testAdjustmentReasonRemainsEditableWithoutBypassingConfirmationGuards(): void
    {
        $this->assertStringContainsString('motivo.disabled = false;', $this->view);
        $this->assertStringNotContainsString('motivo.disabled = !puedeConfirmar;', $this->view);
        $this->assertStringContainsString(
            'boton.disabled = !(puedeConfirmar && tieneToken && motivo.length >= 5);',
            $this->view
        );
    }

    public function testBudgetModalsAreMountedAtBodyBeforeBootstrapInitialization(): void
    {
        $mountCall = strpos($this->view, 'this.mountModalsAtBody();');
        $bootstrapInit = strpos(
            $this->view,
            "this.modalImport = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoImportar'));"
        );

        $this->assertNotFalse($mountCall);
        $this->assertNotFalse($bootstrapInit);
        $this->assertLessThan($bootstrapInit, $mountCall);
        $this->assertStringContainsString('document.body.appendChild(modal);', $this->view);
    }

    public function testMissingRowsNeverActAsImplicitDeletionOrders(): void
    {
        $this->assertStringContainsString(
            'la ausencia no se interpreta como una orden de eliminacion.',
            $this->model
        );
        $this->assertStringContainsString("'puede_confirmar' => \$puedeConfirmar ? 1 : 0", $this->model);
        $this->assertStringContainsString("'faltantes' => [count(\$faltantes), \$mensajeFaltantes]", $this->model);
        $this->assertStringContainsString("'advertencias' => \$advertencias", $this->model);
        $this->assertStringContainsString('$preservadasPorFk', $this->model);
        $this->assertStringContainsString('if (!$esReajusteMasivo) {', $this->model);
        $this->assertStringContainsString(
            'if ($esRecarga && $detallesAnteriores && !$esReajusteMasivo) {',
            $this->model
        );
    }

    public function testOperationalWarningsDoNotBlockValidBudgetChanges(): void
    {
        $this->assertStringContainsString(
            'Las PK nuevas se agregaran al presupuesto mensual.',
            $this->model
        );
        $this->assertStringContainsString(
            "'asignaciones' => [count(\$erroresAsignacion), 'El presupuesto puede cargarse, pero esas filas no actualizaran la asignacion operativa; se conservara la actual cuando exista.']",
            $this->model
        );
        $this->assertStringContainsString(
            '&& ($duplicadas || $omitidasInvalidas > 0)',
            $this->model
        );
        $this->assertStringContainsString('const advertencias = Array.isArray(datos.advertencias)', $this->view);
        $this->assertStringContainsString('Puedes confirmar el reajuste', $this->view);
    }

    public function testNewBranchPksAreLoadedIntoTheMonthlyBudgetInsteadOfBeingDiscarded(): void
    {
        $this->assertStringContainsString("'alta_presupuesto'", $this->model);
        $this->assertStringContainsString("'altas_fuera_catalogo'", $this->model);
        $this->assertStringContainsString("'fuera_catalogo' => \$sucursalCatalogo ? 0 : 1", $this->model);
        $this->assertStringContainsString('sucursal(es) nueva(s) se agregaron al presupuesto mensual.', $this->view);
        $this->assertStringNotContainsString('extra no venían en el template y no se cargaron', $this->view);
    }

    public function testInactiveCatalogBranchesRemainBlockedAndExcelAmountsUseDatabasePrecision(): void
    {
        $this->assertStringContainsString(
            'La sucursal existe en el catalogo, pero esta inactiva.',
            $this->model
        );
        $this->assertStringContainsString('decimalPresupuestoPersistible', $this->model);
        $this->assertStringContainsString('nullableDecimalPresupuestoPersistible', $this->model);
        $this->assertStringContainsString('PHP_ROUND_HALF_UP', $this->model);
    }

    public function testExistingMonthRequiresOnlyItsCurrentBudgetBranches(): void
    {
        $this->assertStringContainsString(
            "\$sucursalesObligatorias = \$presupuestoId > 0\n                ? \$actualesPorFk\n                : \$esperadasPorFk;",
            $this->model
        );
        $this->assertStringContainsString(
            "\$sucursalesObligatorias = \$esRecarga\n                ? \$detallesAnteriores\n                : \$esperadasPorFk;",
            $this->model
        );
        $this->assertStringContainsString(
            "'sucursales_obligatorias' => count(\$sucursalesObligatorias)",
            $this->model
        );
        $this->assertStringContainsString(
            "'sucursales_esperadas' => count(\$sucursalesObligatorias)",
            $this->model
        );
    }

    public function testConcurrentChangesInvalidateTheReviewedComparison(): void
    {
        $this->assertStringContainsString('huellaPresupuestoMensual', $this->model);
        $this->assertStringContainsString('hash_equals($huellaAnalisisEsperada', $this->model);
        $this->assertStringContainsString("'status' => 409", $this->model);
    }

    public function testExistingMonthTemplateIsPrefilledWithoutChangingItsLayout(): void
    {
        $this->assertStringContainsString(
            'getSucursalesTemplatePresupuestoMensual($anio, $mes)',
            $this->controller
        );
        $this->assertStringContainsString(
            'public static function getSucursalesTemplatePresupuestoMensual',
            $this->model
        );
        $this->assertStringContainsString("\$row['meta_creditos'] ?? ''", $this->controller);
        $this->assertStringContainsString("\$row['meta_cash'] ?? ''", $this->controller);
        $this->assertStringContainsString("\$row['comisiona_a_partir_de'] ?? ''", $this->controller);
        $this->assertStringContainsString('$templateRows = [];', $this->model);
        $this->assertStringContainsString('return $templateRows;', $this->model);
    }
}
