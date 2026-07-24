<?php

use PHPUnit\Framework\TestCase;
use Services\SolicitudAdjudicacionValidator;

final class SolicitudAdjudicacionValidatorTest extends TestCase
{
    public function testRechazaSolicitudSinCreditoNiDecisionDeTitular(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([]);

        self::assertFalse($result['valid']);
        self::assertArrayHasKey('id_credito', $result['errors']);
        self::assertArrayHasKey('entregara_titular', $result['errors']);
    }

    public function testTitularNoExigeTodosLosCamposCondicionales(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => 123,
            'entregara_titular' => 0,
        ]);

        self::assertFalse($result['valid']);
        self::assertSame(
            ['nombre_entregante', 'kilometraje', 'telefono_actual', 'direccion_resguardo', 'motivo'],
            array_keys($result['errors'])
        );
    }

    public function testNormalizaSolicitudCompletaEntregadaPorTercero(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => '456',
            'entregara_titular' => 'no',
            'nombre_entregante' => '  Maria   Lopez  ',
            'kilometraje' => '12,345 km',
            'telefono_actual' => '(55) 1234-5678',
            'direccion_resguardo' => '  Calle 1, Colonia Centro ',
            'motivo' => ' Devolucion voluntaria ',
        ]);

        self::assertTrue($result['valid']);
        self::assertFalse($result['data']['entregara_titular']);
        self::assertSame('Maria Lopez', $result['data']['nombre_entregante']);
        self::assertSame(12345, $result['data']['kilometraje']);
        self::assertSame('5512345678', $result['data']['telefono_actual']);
    }

    public function testTitularSiDescartaCamposCondicionales(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => 789,
            'entregara_titular' => 'si',
            'nombre_entregante' => 'No debe persistir',
            'kilometraje' => 100,
            'telefono_actual' => '5512345678',
            'direccion_resguardo' => 'No debe persistir',
            'motivo' => 'No debe persistir',
        ]);

        self::assertTrue($result['valid']);
        self::assertTrue($result['data']['entregara_titular']);
        self::assertNull($result['data']['nombre_entregante']);
        self::assertNull($result['data']['kilometraje']);
        self::assertNull($result['data']['telefono_actual']);
        self::assertNull($result['data']['direccion_resguardo']);
        self::assertNull($result['data']['motivo']);
    }

    public function testRechazaKilometrajeNegativo(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => 900,
            'entregara_titular' => false,
            'nombre_entregante' => 'Persona autorizada',
            'kilometraje' => '-1',
            'telefono_actual' => '5512345678',
            'direccion_resguardo' => 'Calle 1',
            'motivo' => 'Devolución voluntaria',
        ]);

        self::assertFalse($result['valid']);
        self::assertArrayHasKey('kilometraje', $result['errors']);
    }

    public function testCallCenterSoloExigeNombreTelefonoYMotivoParaTercero(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => 901,
            'entregara_titular' => false,
            'nombre_entregante' => 'Familiar autorizado',
            'telefono_actual' => '5512345678',
            'motivo' => 'Entrega voluntaria',
        ], SolicitudAdjudicacionValidator::CANAL_CALLCENTER);

        self::assertTrue($result['valid']);
        self::assertNull($result['data']['kilometraje']);
        self::assertNull($result['data']['direccion_resguardo']);
    }

    public function testDespachosExigeVinYTipoDeAsignacion(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => 902,
            'entregara_titular' => true,
        ], SolicitudAdjudicacionValidator::CANAL_DESPACHOS);

        self::assertFalse($result['valid']);
        self::assertArrayHasKey('vin', $result['errors']);
        self::assertArrayHasKey('tipo_asignacion', $result['errors']);
    }

    public function testDespachosPermiteEquipoMaxikashSinGestor(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => 903,
            'entregara_titular' => true,
            'vin' => '3H1KA0940MD123456',
            'tipo_asignacion' => 'EQUIPO_MAXIKASH',
        ], SolicitudAdjudicacionValidator::CANAL_DESPACHOS);

        self::assertTrue($result['valid']);
        self::assertNull($result['data']['id_persona_gestor']);
    }

    public function testDespachosExigeGestorAlElegirDespacho(): void
    {
        $result = SolicitudAdjudicacionValidator::validarCreacion([
            'id_credito' => 904,
            'entregara_titular' => true,
            'vin' => '3H1KA0940MD123456',
            'tipo_asignacion' => 'DESPACHO',
        ], SolicitudAdjudicacionValidator::CANAL_DESPACHOS);

        self::assertFalse($result['valid']);
        self::assertArrayHasKey('id_persona_gestor', $result['errors']);
    }
}
