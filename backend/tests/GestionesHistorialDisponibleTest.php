<?php

use PHPUnit\Framework\TestCase;

final class GestionesHistorialDisponibleTest extends TestCase
{
    private string $controller;
    private string $view;
    private string $databaseLegacy;

    protected function setUp(): void
    {
        $backend = dirname(__DIR__);
        $this->controller = (string) file_get_contents($backend . '/controllers/Gestiones.php');
        $this->view = (string) file_get_contents($backend . '/views/gestiones_request.php');
        $this->databaseLegacy = (string) file_get_contents($backend . '/core/DatabaseLegacy.php');
    }

    public function testResumenDelClienteNoSeConfundeConHistoricoParcial(): void
    {
        self::assertStringContainsString(
            "self::set('gestionesDbFallosModoParcial', !empty(\$GestionesAll));",
            $this->controller
        );
        self::assertStringNotContainsString(
            "!empty(\$GestionesAll) || !empty(\$detalle)",
            $this->controller
        );
    }

    public function testFalloDeFuenteNoSePresentaComoCreditoSinGestiones(): void
    {
        self::assertStringContainsString('gestionesHistorialNoDisponible', $this->controller);
        self::assertStringContainsString(
            'empty($GestionesAll) && !$huboFalloHistorico',
            $this->controller
        );
        self::assertStringContainsString(
            'Esto no significa que el cr&eacute;dito no tenga gestiones.',
            $this->view
        );
        self::assertStringContainsString('Reintentar consulta', $this->view);
    }

    public function testConexionLegacyReintentaUnaVezAntesDeReportarFallo(): void
    {
        self::assertStringContainsString(
            'for ($intento = 1; $intento <= 2; $intento++)',
            $this->databaseLegacy
        );
        self::assertStringContainsString('usleep(250000);', $this->databaseLegacy);
    }
}
