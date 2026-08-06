<?php

use PHPUnit\Framework\TestCase;

final class FadRrhhIntegrationPreparationTest extends TestCase
{
    public function testControllerExponePreparacionEstadoYCompuerta(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../controllers/CapHum.php');

        self::assertStringContainsString('public function prepararContratacionFad()', $controller);
        self::assertStringContainsString('public function estadoContratacionFad()', $controller);
        self::assertStringContainsString('public function vincularSolicitudFad()', $controller);
        self::assertStringContainsString('public function probarConexionFad()', $controller);
        self::assertStringContainsString('public function enviarContratoFad()', $controller);
        self::assertStringContainsString('public function sincronizarContratacionFad()', $controller);
        self::assertStringContainsString('evaluarPasoGestion($id_candidato)', $controller);
        self::assertStringContainsString('btn-preparar-fad-candidato', $controller);
        self::assertStringContainsString('btn-enviar-contrato-fad-candidato', $controller);
    }

    public function testConfiguracionMantieneIntegracionDesactivadaPorDefecto(): void
    {
        $example = (string) file_get_contents(dirname(__DIR__, 2) . '/.env.example');

        self::assertStringContainsString('# FAD_RRHH_ENABLED=0', $example);
        self::assertStringContainsString('# FAD_RRHH_ENFORCE_SIGNED=0', $example);
        self::assertStringContainsString('FAD_RRHH_USERNAME=cuenta_exclusiva_capital_humano', $example);
        self::assertStringContainsString('FAD_RRHH_PASSWORD=secreto_exclusivo_capital_humano', $example);
    }
}
