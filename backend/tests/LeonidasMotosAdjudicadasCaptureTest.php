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

final class LeonidasMotosSinOperacionServiceStub extends LeonidasMotosAdjudicadasService
{
    public function __construct()
    {
    }

    public function diagnosticar(int $idCredito): array
    {
        $task = [
            'task_id' => 976252,
            'credit_number' => (string) $idCredito,
            'client_name' => 'AYLIN YESENIA OLVERA GONZALEZ',
            'task_status' => '1',
            'campaign_id' => 431,
            'campaign_name' => 'SUPERVISORES V31',
            'task_deleted_at' => null,
            'campaign_deleted_at' => null,
        ];
        return [
            'id_credito' => $idCredito,
            'operacion' => null,
            'asignacion_local' => null,
            'legacy' => [
                'disponible' => true,
                'tasks' => [$task],
                'assignments' => [],
                'dictums' => [],
                'motos_task' => null,
                'otra_task_activa' => $task,
            ],
        ];
    }
}

final class LeonidasMotosConEvidenciasServiceStub extends LeonidasMotosAdjudicadasService
{
    public string $estatus = 'Cierre Documentado';

    public function __construct()
    {
    }

    public function diagnosticar(int $idCredito): array
    {
        return [
            'id_credito' => $idCredito,
            'operacion' => [
                'id' => 4256,
                'id_credito' => $idCredito,
                'nombre_cliente' => 'AYLIN YESENIA OLVERA GONZALEZ',
                'estatus' => $this->estatus,
                'datos_moto_at' => '2026-08-01 10:00:00',
                'evidencias_total' => 14,
            ],
            'asignacion_local' => null,
            'legacy' => [
                'disponible' => true,
                'tasks' => [],
                'assignments' => [],
                'dictums' => [],
                'motos_task' => null,
                'otra_task_activa' => null,
            ],
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

    public function testProponeCrearOperacionLocalAntesDeForzarEvidencias(): void
    {
        $service = new LeonidasMotosSinOperacionServiceStub();
        $context = $this->context;
        $context['permisos_agente']['motos_override_estatus'] = true;

        $result = $service->resolver(
            'MUEVE A EVIDENCIAS EL ID 2336022',
            'mueve a evidencias el id 2336022',
            $context
        );

        self::assertSame('agente_propuesta', $result['tipo']);
        self::assertSame(
            LeonidasMotosAdjudicadasService::ACTION_FORZAR_EVIDENCIAS,
            $result['propuesta_especificacion']['accion']
        );
        self::assertTrue($result['propuesta_especificacion']['payload']['crear_operacion_local']);
        self::assertSame(976252, $result['propuesta_especificacion']['payload']['legacy_task_id_fuente']);
        self::assertStringContainsString('crear la operacion local', $result['mensaje']);
    }

    public function testNoPermiteMoverOperacionLocalCuandoLegacyNoEsteDisponible(): void
    {
        $context = $this->context;
        $context['permisos_agente']['motos_override_estatus'] = true;

        $result = $this->service->resolver(
            'MUEVE A EVIDENCIAS EL ID 2257556',
            'mueve a evidencias el id 2257556',
            $context
        );

        self::assertSame('agente_diagnostico', $result['tipo']);
        self::assertArrayNotHasKey('propuesta_especificacion', $result);
        self::assertStringContainsString('la conexion a Legacy fallo', $result['mensaje']);
        self::assertStringContainsString('Legacy es obligatorio', $result['mensaje']);
        self::assertStringContainsString('No realice cambios', $result['mensaje']);
    }

    public function testNoCreaOperacionLocalSinPermisoOverride(): void
    {
        $service = new LeonidasMotosSinOperacionServiceStub();

        $result = $service->resolver(
            'MUEVE A EVIDENCIAS EL ID 2336022',
            'mueve a evidencias el id 2336022',
            $this->context
        );

        self::assertSame('agente_denegado', $result['tipo']);
        self::assertArrayNotHasKey('propuesta_especificacion', $result);
    }

    public function testReposicionaOperacionQueEstaFueraDeEvidencias(): void
    {
        $service = new LeonidasMotosConEvidenciasServiceStub();
        $context = $this->context;
        $context['permisos_agente']['motos_override_estatus'] = true;

        $result = $service->resolver(
            'MUEVE A EVIDENCIAS EL ID 2336022',
            'mueve a evidencias el id 2336022',
            $context
        );

        self::assertSame('agente_propuesta', $result['tipo']);
        self::assertSame(
            LeonidasMotosAdjudicadasService::ACTION_FORZAR_EVIDENCIAS,
            $result['propuesta_especificacion']['accion']
        );
        self::assertStringContainsString('Cierre Documentado', $result['mensaje']);
        self::assertStringContainsString('reposicionarla', $result['mensaje']);
    }

    public function testUsaEnvioNormalCuandoOperacionYaEstaEnEvidencias(): void
    {
        $service = new LeonidasMotosConEvidenciasServiceStub();
        $service->estatus = 'Recibido';

        $result = $service->resolver(
            'MUEVE A EVIDENCIAS EL ID 2336022',
            'mueve a evidencias el id 2336022',
            $this->context
        );

        self::assertSame('agente_propuesta', $result['tipo']);
        self::assertSame(
            LeonidasMotosAdjudicadasService::ACTION_ENVIAR_EVIDENCIAS,
            $result['propuesta_especificacion']['accion']
        );
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
