<?php

use PHPUnit\Framework\TestCase;
use Services\FadRrhhContractDataService;

require_once __DIR__ . '/../services/FadRrhhContractDataService.php';

final class FadRrhhContractDataServiceTest extends TestCase
{
    private function baseCandidate(): array
    {
        return [
            'nombres' => 'ANA', 'segundo_nombre' => 'MARIA', 'apellidop' => 'LOPEZ', 'apellidom' => 'DIAZ',
            'email' => 'ana@example.com', 'telefono' => '5512345678',
            'domicilio_calle_texto' => 'REFORMA', 'domicilio_num_exterior' => '100',
            'nombre_div_nivel2' => 'CUAUHTEMOC', 'nombre_div_nivel1' => 'CIUDAD DE MEXICO', 'codigo_postal' => '06000',
            'nombre_puesto' => 'ANALISTA', 'sueldo_bruto' => '14000', 'sueldo_neto' => '12000', 'fecha_ingreso_programada' => '2026-08-10',
        ];
    }

    public function testConsolidaSeleccionDocumentosYDerivaCurp(): void
    {
        $bundle = [
            'verificacion' => ['resultado' => ['curp' => 'LODA000101MDFPZN09', 'rfc' => 'LODA000101AB1']],
            'documentos' => [
                ['tipo_documento' => 'Solicitud interna', 'verificacion_calidad' => ['datos' => [
                    'nacionalidad' => 'MEXICANA', 'estado_civil' => 'SOLTERA',
                    'contactos_emergencia' => ['MARIA 5511111111'],
                    'beneficiarios' => [
                        ['nombre_completo' => 'MARIA LOPEZ', 'parentesco' => 'MADRE', 'porcentaje' => 50],
                        ['nombre_completo' => 'JUAN DIAZ', 'parentesco' => 'PADRE', 'porcentaje' => 50],
                    ],
                    'actividades' => ['Validar expedientes', 'Elaborar reportes', 'Dar seguimiento'],
                ]]],
                ['tipo_documento' => 'NSS', 'verificacion_calidad' => ['nss' => ['nss' => '12345678901']]],
                ['tipo_documento' => 'Estado de cuenta', 'verificacion_fiscal' => ['banco' => 'BBVA', 'clabe' => '012345678901234567', 'numero_cuenta' => '1234567890']],
            ],
        ];

        $result = FadRrhhContractDataService::consolidate($this->baseCandidate(), $bundle);

        self::assertSame('ANA MARIA LOPEZ DIAZ', $result['data']['full_name']);
        self::assertSame('2000-01-01', $result['data']['birth_date']);
        self::assertSame('FEMENINO', $result['data']['sex']);
        self::assertSame('documento_nss', $result['sources']['nss']);
        self::assertSame('seleccion_personal', $result['sources']['salary']);
        self::assertSame('14000', $result['data']['salary']);
        self::assertTrue($result['ready'], json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    public function testNoInventaDatosQueNoAparecen(): void
    {
        $result = FadRrhhContractDataService::consolidate($this->baseCandidate(), []);

        self::assertSame('', $result['data']['nationality']);
        self::assertContains('nationality', $result['missing']);
        self::assertFalse($result['ready']);
    }

    public function testBeneficiariosDebenSumarCienYActividadesMinimoTres(): void
    {
        $validation = FadRrhhContractDataService::validate([
            'full_name' => 'A', 'nationality' => 'MEXICANA', 'sex' => 'FEMENINO', 'age' => 25,
            'marital_status' => 'SOLTERA', 'rfc' => 'LODA000101AB1', 'curp' => 'LODA000101MDFPZN09',
            'nss' => '12345678901', 'address' => 'DOMICILIO', 'email' => 'ana@example.com', 'phone' => '5512345678',
            'emergency_contacts' => 'MARIA', 'clabe' => '012345678901234567', 'account_number' => '123',
            'bank' => 'BBVA', 'position' => 'ANALISTA', 'activities' => ['UNA', 'DOS'], 'salary' => 14000,
            'start_date' => '2026-08-10', 'beneficiaries' => [['name' => 'MARIA', 'relationship' => 'MADRE', 'percentage' => 90]],
        ]);

        self::assertArrayHasKey('activities', $validation['errors']);
        self::assertArrayHasKey('beneficiaries', $validation['errors']);
    }

    public function testInterfazIncluyeRevisionCancelacionYConfirmacionEscrita(): void
    {
        $view = (string) file_get_contents(__DIR__ . '/../views/candidatos.php');
        $controller = (string) file_get_contents(__DIR__ . '/../controllers/CapHum.php');

        self::assertStringContainsString('id="modalDatosContratoFad"', $view);
        self::assertStringContainsString('Generar y enviar a FAD', $view);
        self::assertStringContainsString('public function datosContratoFad()', $controller);
        self::assertStringContainsString('public function enviarContratoFadGenerado()', $controller);
        self::assertStringContainsString('ENVIAR A FAD', $controller);
        self::assertStringContainsString('btnEnviarContratoFadConfirmado', $controller);
        self::assertStringContainsString('sueldo bruto mensual', $controller);
        self::assertStringContainsString('fadMostrarModalSinSuperposicion', $controller);
    }
}
