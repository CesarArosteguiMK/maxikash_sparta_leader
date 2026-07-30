<?php

use PHPUnit\Framework\TestCase;

final class AtlasPresupuestoReestructuraTest extends TestCase
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

    public function testMonthlyTableUsesTheExistingTemplateFields(): void
    {
        $this->assertStringContainsString('PK sucursal', $this->view);
        $this->assertStringContainsString('Presupuesto de créditos', $this->view);
        $this->assertStringContainsString('Presupuesto de cash', $this->view);
        $this->assertStringContainsString('Presupuesto base', $this->view);
        $this->assertStringContainsString('Clasificación', $this->view);
        $this->assertStringContainsString('name="comisiona_a_partir_de"', $this->view);

        foreach ([
            "'Pk_Sucursal'",
            "'Asesor'",
            "'Clasificacion nuevo esquema'",
            "'Creditos'",
            "'Cash'",
            "'Comisiona a partir de'",
        ] as $header) {
            $this->assertStringContainsString($header, $this->controller);
        }
    }

    public function testReassignmentMovesCompleteBranchesToOnePerson(): void
    {
        $this->assertStringContainsString('detalle_ids: detalleIds', $this->view);
        $this->assertStringContainsString('asesor_destino_persona_id: destinoId', $this->view);
        $this->assertStringNotContainsString('asignaciones: estado.asignaciones', $this->view);
        $this->assertStringContainsString(
            'consolidarAsignacionUnicaPresupuesto',
            $this->controller
        );
        $this->assertStringContainsString(
            'UPDATE atlas_presupuesto_sucursal_gestores',
            $this->model
        );
    }

    public function testDeletionKeepsOneResultingResponsibleAndItsReason(): void
    {
        $this->assertStringContainsString('asignaciones_destino: [{', $this->view);
        $this->assertStringContainsString('persona_id: responsableId', $this->view);
        $this->assertStringContainsString('meta_creditos: totalCreditos.toFixed(2)', $this->view);
        $this->assertStringContainsString('meta_cash: totalCash.toFixed(2)', $this->view);
        $this->assertStringContainsString('id="atlasPresEliminarMotivo"', $this->view);
    }

    public function testBudgetBaseCanBeEditedWithoutChangingTheTemplateContract(): void
    {
        $this->assertStringContainsString(
            'comisiona_a_partir_de = :comisiona_a_partir_de',
            $this->model
        );
        $this->assertStringContainsString(
            "'comisiona_a_partir_de' => \$presupuestoBaseNuevo",
            $this->model
        );
    }
}
