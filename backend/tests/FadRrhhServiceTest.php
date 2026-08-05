<?php

use PHPUnit\Framework\TestCase;
use Services\FadRrhhService;

require_once __DIR__ . '/../models/FadRrhh.php';
require_once __DIR__ . '/../services/FadRrhhPortalClient.php';
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
            'FAD_RRHH_SIGNATURE_BOX', 'FAD_RRHH_CERTIFICATE_BOX',
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
            putenv('FAD_RRHH_SIGNATURE_BOX=si');
            putenv('FAD_RRHH_CERTIFICATE_BOX=si');

            $config = (new FadRrhhService())->configuracion();

            self::assertTrue($config['api_ready']);
            self::assertFalse($config['flow_ready']);
            self::assertSame(
                ['country_id', 'requisition_type_id', 'sign_time_id', 'signature_box', 'certificate_box'],
                $config['flow_missing']
            );
        } finally {
            foreach ($previous as $name => $value) {
                $value === false ? putenv($name) : putenv($name . '=' . $value);
            }
        }
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
