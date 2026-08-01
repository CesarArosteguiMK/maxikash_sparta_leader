<?php

use PHPUnit\Framework\TestCase;

final class MotosAdjudicadasReemplazoEvidenciaViewTest extends TestCase
{
    public function testPermisoEspecialPuedeReemplazarDesdeModalSoloLectura(): void
    {
        $view = file_get_contents(dirname(__DIR__) . '/views/atencion_clientes_evidencias.php');

        self::assertIsString($view);
        self::assertStringContainsString(
            "const AEV_PUEDE_REEMPLAZAR_EVIDENCIA = <?php echo \$aevPuedeReemplazarEvidencia ? 'true' : 'false'; ?>;",
            $view
        );
        self::assertStringContainsString(
            'aevValidarAbrir(${+item.id_credito}, { soloLectura: true',
            $view
        );
        self::assertStringNotContainsString(
            '#modalAevValidarEvidencias.aev-modo-lectura .aev-btn-reemplazo-gestor',
            $view
        );
        self::assertStringContainsString("const replGestor = ev.target.closest('[data-aev-reemplazar-gestor]');", $view);

        $handlerStart = strpos($view, "const replGestor = ev.target.closest('[data-aev-reemplazar-gestor]');");
        self::assertNotFalse($handlerStart);
        $handler = substr($view, $handlerStart, 700);

        self::assertStringContainsString('aevAbrirReemplazoGestor(', $handler);
        self::assertStringNotContainsString('if (_aevStore.soloLectura) return;', $handler);
    }
}
