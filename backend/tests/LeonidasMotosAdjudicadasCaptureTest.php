<?php

use PHPUnit\Framework\TestCase;
use Services\LeonidasMotosAdjudicadasService;

require_once __DIR__ . '/../services/LeonidasMotosAdjudicadasService.php';

final class LeonidasMotosAdjudicadasCaptureServiceStub extends LeonidasMotosAdjudicadasService
{
    public function __construct()
    {
    }

    public function diagnosticar(int $idCredito): array
    {
        return [
            'id_credito' => $idCredito,
            'operacion' => [
                'id' => 592,
                'id_credito' => $idCredito,
                'estatus' => 'En transito',
            ],
            'asignacion_local' => null,
            'legacy' => [],
        ];
    }
}

final class LeonidasMotosAdjudicadasCaptureTest extends TestCase
{
    private LeonidasMotosAdjudicadasCaptureServiceStub $service;
    private array $context;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->service = new LeonidasMotosAdjudicadasCaptureServiceStub();
        $this->context = [
            'actor_id' => 900001,
            'nombre' => 'USUARIO DE PRUEBA',
            'permisos_agente' => ['motos' => true],
        ];
    }

    public function testPreparaVistaPreviaConTodosLosDatosSinEscribirEnBase(): void
    {
        $message = $this->completeMessage();
        $result = $this->service->resolver($message, $this->normalize($message), $this->context);

        self::assertIsArray($result);
        self::assertSame('agente_propuesta', $result['tipo']);
        self::assertSame(
            LeonidasMotosAdjudicadasService::ACTION_GUARDAR_DATOS_MOTO,
            $result['propuesta_especificacion']['accion']
        );
        self::assertStringContainsString('No se ha modificado ningun dato todavia', $result['mensaje']);

        $payload = $result['propuesta_especificacion']['payload'];
        self::assertSame(1990648, $payload['id_credito']);
        self::assertSame(592, $payload['id_operacion']);
        self::assertSame('En transito', $payload['estatus_esperado']);
        self::assertSame([
            'moto_marca' => 'CF MOTO',
            'moto_no_serie' => 'LCEPDNL71T6004049',
            'moto_modelo' => '2026',
            'moto_anio' => '2026',
            'moto_color' => 'NEBULA WHITE',
            'moto_no_motor' => '172MM-2ATA065006',
            'moto_placas' => '',
            'kilometraje' => '178',
            'tiene_llave_fisica' => 'si',
            'la_moto_tiene_placa_fisica' => 'no',
            'tiene_tarjeta_de_circulacion_en_fisico' => 'no',
            'responsable_entrega' => 'BENJAMIN MARQUEZ GOMEZ',
            'log_telefono' => '5610498548',
            'log_direccion' => 'En Av. Gral. Martin carrera 174, col. Martin carrera, c.p. 07070, alcaldia Gustavo A madero CDMX',
            'log_lugar_resguardo' => 'otro',
            'log_lugar_otro' => 'CDMX',
        ], $payload['datos']);
    }

    public function testPreguntaDatoFaltanteYAceptaNoParaDejarloVacio(): void
    {
        $message = str_replace("Placas: NO\n", '', $this->completeMessage());
        $question = $this->service->resolver($message, $this->normalize($message), $this->context);

        self::assertSame('agente_pregunta', $question['tipo']);
        self::assertStringContainsString('Falta el dato de las placas, ¿cuál sería?', $question['mensaje']);

        $result = $this->service->resolver('NO', 'no', $this->context);

        self::assertSame('agente_propuesta', $result['tipo']);
        self::assertSame('', $result['propuesta_especificacion']['payload']['datos']['moto_placas']);
    }

    public function testPreguntaCamposFaltantesUnoPorUno(): void
    {
        $message = "Edita los datos del formulario de Adjudicacion de Motos del id 1990648:\n"
            . "Marca: CF MOTO\n"
            . "Serie: LCEPDNL71T6004049";

        $firstQuestion = $this->service->resolver($message, $this->normalize($message), $this->context);
        self::assertStringContainsString('Falta el dato del modelo', $firstQuestion['mensaje']);

        $secondQuestion = $this->service->resolver('2026', '2026', $this->context);
        self::assertStringContainsString('Falta el dato del año', $secondQuestion['mensaje']);
    }

    public function testDeniegaCapturaSinPermisoDeMotos(): void
    {
        $context = $this->context;
        $context['actor_id'] = 900002;
        $context['permisos_agente']['motos'] = false;

        $message = $this->completeMessage();
        $result = $this->service->resolver($message, $this->normalize($message), $context);

        self::assertSame('agente_denegado', $result['tipo']);
        self::assertStringContainsString('no tiene acceso', $result['mensaje']);
    }

    private function completeMessage(): string
    {
        return <<<TEXT
Edita los siguientes datos del formulario de Adjudicacion de Motos del id 1990648:

Marca: CF MOTO
Serie: LCEPDNL71T6004049
Modelo: 2026
Año: 2026
Color: NEBULA WHITE
No. motor: 172MM-2A TA065006
Placas: NO
Kilometraje: 178
Llave física: SI
Placa física: NO
Tarjeta circulación: NO
Lugar de resguardo: CDMX
Responsable: BENJAMIN MARQUEZ GOMEZ
Teléfono: 56 1049 8548
Dirección resguardo: En Av. Gral. Martin carrera 174, col. Martin carrera, c.p. 07070, alcaldia Gustavo A madero CDMX
TEXT;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $ascii !== false ? $ascii : $value;
    }
}
