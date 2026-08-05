<?php

use PHPUnit\Framework\TestCase;
use Services\FadRrhhService;

require_once __DIR__ . '/../models/FadRrhh.php';
require_once __DIR__ . '/../services/FadRrhhPortalClient.php';
require_once __DIR__ . '/../services/FadRrhhTemplateCatalog.php';
require_once __DIR__ . '/../services/FadRrhhService.php';

final class FadRrhhServiceTest extends TestCase
{
    public function testPreparacionNoRompeFlujoHeredado(): void
    {
        $result = FadRrhhService::evaluarEvidencia(
            ['enabled' => false, 'enforce_signed' => false, 'api_ready' => false],
            null
        );

        self::assertTrue($result['permitido']);
        self::assertSame('NOT_STARTED', $result['estatus']);
    }

    public function testConfiguracionObligatoriaIncompletaBloqueaAlta(): void
    {
        $result = FadRrhhService::evaluarEvidencia(
            ['enabled' => true, 'enforce_signed' => true, 'api_ready' => false],
            ['estatus' => 'PENDING']
        );

        self::assertFalse($result['permitido']);
        self::assertStringContainsString('configuración API', $result['motivo']);
    }

    public function testFirmaSinPdfVerificadoNoEsSuficiente(): void
    {
        $result = FadRrhhService::evaluarEvidencia(
            ['enabled' => true, 'enforce_signed' => true, 'api_ready' => true],
            ['estatus' => 'SIGNED', 'pdf_firmado_ruta' => null, 'pdf_firmado_sha256' => null]
        );

        self::assertFalse($result['permitido']);
        self::assertStringContainsString('PDF final', $result['motivo']);
    }

    public function testFirmaYPdfVerificadoPermitenAlta(): void
    {
        $result = FadRrhhService::evaluarEvidencia(
            ['enabled' => true, 'enforce_signed' => true, 'api_ready' => true],
            [
                'estatus' => 'SIGNED',
                'pdf_firmado_ruta' => 'expedientes/candidato/contrato-firmado.pdf',
                'pdf_firmado_sha256' => str_repeat('a', 64),
            ]
        );

        self::assertTrue($result['permitido']);
        self::assertSame('SIGNED', $result['estatus']);
    }

    public function testConfiguracionNoAceptaEtiquetasComoIdsNiJson(): void
    {
        $names = [
            'FAD_RRHH_ENABLED', 'FAD_RRHH_USERNAME', 'FAD_RRHH_PASSWORD',
            'FAD_RRHH_COUNTRY_ID', 'FAD_RRHH_REQUISITION_TYPE_ID', 'FAD_RRHH_SIGN_TIME_ID',
            'FAD_RRHH_TEMPLATE_AMIGO_GENERAL_APPROVED',
            'FAD_RRHH_TEMPLATE_AMIGO_ACTUALIZACION_APPROVED',
            'FAD_RRHH_TEMPLATE_PENSIONAMAX_APPROVED',
            'FAD_RRHH_TEMPLATE_GESTOR_COBRANZA_APPROVED',
        ];
        $previous = [];
        foreach ($names as $name) {
            $previous[$name] = getenv($name);
        }
        try {
            putenv('FAD_RRHH_ENABLED=1');
            putenv('FAD_RRHH_USERNAME=cuenta');
            putenv('FAD_RRHH_PASSWORD=secreto');
            putenv('FAD_RRHH_COUNTRY_ID=Mexico');
            putenv('FAD_RRHH_REQUISITION_TYPE_ID=laboral');
            putenv('FAD_RRHH_SIGN_TIME_ID=vigencia');

            $config = (new FadRrhhService())->configuracion();

            self::assertTrue($config['api_ready']);
            self::assertFalse($config['flow_ready']);
            self::assertSame(
                ['country_id', 'requisition_type_id', 'sign_time_id', 'approved_template'],
                $config['flow_missing']
            );
        } finally {
            foreach ($previous as $name => $value) {
                $value === false ? putenv($name) : putenv($name . '=' . $value);
            }
        }
    }

    public function testCatalogoSeparaEmpresaFirmanteLegalYPaginacion(): void
    {
        $catalog = new \Services\FadRrhhTemplateCatalog();
        $pensionamax = $catalog->get('PENSIONAMAX_NUEVO');
        $actualizacion = $catalog->get('AMIGO_ACTUALIZACION');

        self::assertSame('MARIA DEL CARMEN JARAMILLO CAMACHO', $pensionamax['legal_signer_name']);
        self::assertSame(10, $pensionamax['expected_pages']);
        self::assertCount(10, $pensionamax['worker_signatures']);
        self::assertCount(10, $pensionamax['legal_signatures']);
        self::assertSame('GABRIELA LUCERO SANCHEZ', $actualizacion['legal_signer_name']);
        self::assertSame(9, $actualizacion['expected_pages']);
    }

    public function testRepresentanteLegalPermiteUnaDiferenciaTipograficaUnica(): void
    {
        $service = new FadRrhhService();
        $method = new ReflectionMethod($service, 'findLegalSigner');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            ['signerId' => 'legal-1', 'fullName' => 'GABRIELA LUCERO SANCHEZ.'],
            ['signerId' => 'otro', 'fullName' => 'PERSONA COMPLETAMENTE DISTINTA'],
        ], 'GABRIELA LUCERO SANCHEZ');

        self::assertSame('legal-1', $result['signerId']);
    }

    public function testCatalogoRealDeFadConservaNombreLegible(): void
    {
        $service = new FadRrhhService();
        $method = new ReflectionMethod($service, 'safeCatalog');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            ['requisitionTypeId' => 2, 'requisitionType' => 'Contrato'],
            ['signTimeId' => 15, 'signTime' => '10 dias'],
        ]);

        self::assertSame([
            ['id' => '2', 'nombre' => 'Contrato'],
            ['id' => '15', 'nombre' => '10 dias'],
        ], $result);
    }
}
