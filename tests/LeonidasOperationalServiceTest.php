<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasAttachmentService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasOperationStore.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasFinancialWorkflowService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasTrackingEvidenceService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasOperationalService.php';

use Services\LeonidasOperationalService;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function opAssert(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

final class LeonidasOperationStoreFake
{
    public array $ejecuciones = [];
    public array $autorizaciones = [];
    private int $secuencia = 1;

    public function buscarEjecucion(string $key): ?array
    {
        return $this->ejecuciones[$key] ?? null;
    }

    public function iniciar(string $key, string $accion, int $actor, string $hash): array
    {
        if (isset($this->ejecuciones[$key])) {
            return ['nueva' => false, 'ejecucion' => $this->ejecuciones[$key]];
        }
        $this->ejecuciones[$key] = ['accion' => $accion, 'actor_id' => $actor, 'estado' => 'ejecutando'];
        return ['nueva' => true, 'ejecucion' => $this->ejecuciones[$key]];
    }

    public function completar(string $key, array $comprobante): void
    {
        $this->ejecuciones[$key] = [
            'estado' => 'verificada',
            'comprobante' => $comprobante,
        ];
    }

    public function fallar(string $key, string $mensaje): void
    {
        $this->ejecuciones[$key] = ['estado' => 'fallida', 'error' => $mensaje];
    }

    public function crearAutorizacion(string $accion, array $payload, int $actor, string $resumen): array
    {
        $codigo = 'LEO-' . str_pad((string) $this->secuencia++, 10, 'A', STR_PAD_LEFT);
        $this->autorizaciones[$codigo] = [
            'codigo' => $codigo,
            'accion' => $accion,
            'payload' => $payload,
            'resumen' => $resumen,
            'primer_actor_id' => $actor,
            'estado' => 'pendiente',
            'expira_en' => date('Y-m-d H:i:s', time() + 3600),
        ];
        return $this->autorizaciones[$codigo];
    }

    public function obtenerAutorizacion(string $codigo): ?array
    {
        return $this->autorizaciones[$codigo] ?? null;
    }

    public function reclamarAutorizacion(string $codigo, int $actor): array
    {
        $row = $this->autorizaciones[$codigo] ?? null;
        if (!$row || $row['estado'] !== 'pendiente') throw new RuntimeException('No disponible');
        if ((int) $row['primer_actor_id'] === $actor) throw new DomainException('Actor repetido');
        $row['segundo_actor_id'] = $actor;
        $row['estado'] = 'ejecutando';
        $this->autorizaciones[$codigo] = $row;
        return $row;
    }

    public function marcarAutorizacion(string $codigo, string $estado, ?int $actor = null, array $comprobante = []): void
    {
        $this->autorizaciones[$codigo]['estado'] = $estado;
        $this->autorizaciones[$codigo]['segundo_actor_id'] = $actor;
        $this->autorizaciones[$codigo]['comprobante'] = $comprobante;
    }
}

$reflection = new ReflectionClass(LeonidasOperationalService::class);
$method = $reflection->getMethod('definiciones');
$method->setAccessible(true);
$definiciones = $method->invoke(null);

opAssert(count($definiciones) >= 40, 'Deben existir al menos 40 capacidades operativas/consultivas.');

$payloadPara = static function (array $def): array {
    $payload = (array) ($def['defaults'] ?? []);
    foreach ((array) ($def['campos'] ?? []) as $campo => $meta) {
        $tipo = (string) ($meta['tipo'] ?? 'text');
        $payload[$campo] = match ($tipo) {
            'int' => $campo === 'id_celula' ? 1 : 123,
            'decimal' => 1250.50,
            'bool' => true,
            'csv_int' => [11, 12],
            'json' => [['id_credito' => 123, 'estatus_confirmacion_gestor' => 'confirmado']],
            'code' => 'CON-ABC12345',
            'token' => str_repeat('a', 36),
            default => $campo === 'estatus' ? '1' : 'Dato válido',
        };
    }
    return $payload;
};

$ejecutables = LeonidasOperationalService::accionesEjecutables();
opAssert(in_array('ticket_crear', $ejecutables, true), 'Crear ticket debe ser ejecutable.');
opAssert(in_array('condonacion_aprobar', $ejecutables, true), 'Aprobar condonación debe ser ejecutable.');
opAssert(in_array('cierre_enviar_cartera', $ejecutables, true), 'Enviar cierre a Cartera debe ser ejecutable.');
opAssert(in_array(LeonidasOperationalService::APPROVAL_ACTION, $ejecutables, true), 'Debe existir el ejecutor de segunda autorización.');

foreach ($definiciones as $accion => $def) {
    if (!empty($def['consulta'])) continue;
    $store = new LeonidasOperationStoreFake();
    $adapters = [
        'operation_store' => $store,
        'attachment_validate' => static function (): void {},
        $accion . '_inspect' => static fn(): array => ['success' => true, 'message' => 'Estado actual'],
        $accion . '_execute' => static fn(array $payload): array => [
            'success' => true,
            'message' => 'Ejecutado por adaptador oficial',
            'id' => $payload['id_ticket'] ?? $payload['id_credito'] ?? 1,
        ],
        $accion . '_verify' => static fn(): array => ['success' => true, 'message' => 'Verificado en fuente'],
    ];
    $service = new LeonidasOperationalService($adapters);
    $permisos = [(string) $def['permiso'] => true];
    $contexto1 = ['actor_id' => 10, 'nombre' => 'Primera Persona', 'permisos_agente' => $permisos];
    $payload = $payloadPara($def);
    if ($accion === 'direccion_corregir') $payload['cambios'] = ['estado' => 'PUEBLA'];
    if ($accion === 'almacen_finalizar_revision') $payload['dictamen'] = 'reparada';
    if ($accion === 'tracking_crear_ruta') $payload['tipo_transportista'] = 'interno';
    $respuesta = $service->ejecutar($accion, $payload, $contexto1);

    if (!empty($def['sensible'])) {
        opAssert(($respuesta['tipo'] ?? '') === 'agente_autorizacion_pendiente', "{$accion} debe solicitar segunda autorización.");
        $codigo = (string) ($respuesta['autorizacion']['codigo'] ?? '');
        opAssert($codigo !== '', "{$accion} debe generar código de autorización.");
        $contexto2 = ['actor_id' => 20, 'nombre' => 'Segunda Persona', 'permisos_agente' => $permisos];
        $ejecutada = $service->ejecutar(LeonidasOperationalService::APPROVAL_ACTION, ['codigo' => $codigo], $contexto2);
        opAssert(($ejecutada['tipo'] ?? '') === 'agente_ejecutado', "{$accion} debe ejecutarse con el segundo actor.");
        opAssert((int) ($ejecutada['autorizacion']['primer_actor_id'] ?? 0) === 10, 'Debe conservar el primer actor.');
        opAssert((int) ($ejecutada['autorizacion']['segundo_actor_id'] ?? 0) === 20, 'Debe registrar el segundo actor.');
    } else {
        opAssert(($respuesta['tipo'] ?? '') === 'agente_ejecutado', "{$accion} debe ejecutar y verificar.");
        $duplicada = $service->ejecutar($accion, $payload, $contexto1);
        opAssert(($duplicada['tipo'] ?? '') === 'agente_idempotente', "{$accion} debe bloquear la ejecución duplicada.");
    }
}

unset($_SESSION['leonidas_operational_task']);
$ticketStore = new LeonidasOperationStoreFake();
$ticketService = new LeonidasOperationalService([
    'operation_store' => $ticketStore,
    'attachment_validate' => static function (): void {},
    'ticket_crear_inspect' => static fn(): array => ['success' => true, 'message' => 'Crédito localizado'],
    'ticket_crear_execute' => static fn(): array => ['success' => true, 'message' => 'Ticket creado', 'id_ticket' => 77],
    'ticket_crear_verify' => static fn(): array => ['success' => true, 'message' => 'Ticket localizado'],
]);
$ticketContext = ['actor_id' => 5, 'nombre' => 'Operador', 'permisos_agente' => ['tickets' => true]];
$inicio = $ticketService->resolver('crear ticket', 'crear ticket', $ticketContext);
opAssert(($inicio['campo_pendiente'] ?? '') === 'id_credito', 'Debe recopilar primero el crédito faltante.');
$siguiente = $ticketService->resolver('456', '456', $ticketContext);
opAssert(($siguiente['campo_pendiente'] ?? '') === 'id_tipo_ticket', 'Debe recopilar el tipo de ticket.');
$origen = $ticketService->resolver('2', '2', $ticketContext);
opAssert(($origen['campo_pendiente'] ?? '') === 'id_origen_ticket', 'Debe recopilar el origen del ticket.');
$descripcion = $ticketService->resolver('1', '1', $ticketContext);
opAssert(($descripcion['campo_pendiente'] ?? '') === 'descripcion', 'Debe recopilar la descripción faltante.');
$preview = $ticketService->resolver('Cliente solicita aclaración de saldo', '', $ticketContext);
opAssert(($preview['tipo'] ?? '') === 'agente_vista_previa', 'Debe mostrar vista previa antes de escribir.');
opAssert(($preview['propuesta_especificacion']['accion'] ?? '') === 'ticket_crear', 'La vista previa debe conservar la intención.');

unset($_SESSION['leonidas_operational_task']);
$denegado = $ticketService->resolver(
    'cerrar ticket 99',
    'cerrar ticket 99',
    ['actor_id' => 5, 'nombre' => 'Sin Permiso', 'permisos_agente' => ['tickets' => false]]
);
opAssert(($denegado['tipo'] ?? '') === 'agente_denegado', 'Debe validar permisos antes de preparar una escritura.');

$falloStore = new LeonidasOperationStoreFake();
$falloService = new LeonidasOperationalService([
    'operation_store' => $falloStore,
    'attachment_validate' => static function (): void {},
    'ticket_cerrar_inspect' => static fn(): array => ['success' => true, 'message' => 'Ticket abierto'],
    'ticket_cerrar_execute' => static fn(): array => ['success' => true, 'message' => 'Modelo respondió'],
    'ticket_cerrar_verify' => static fn(): array => ['success' => false, 'message' => 'No coincide'],
]);
$fallo = false;
try {
    $falloService->ejecutar('ticket_cerrar', ['id_ticket' => 99], [
        'actor_id' => 5,
        'nombre' => 'Operador',
        'permisos_agente' => ['tickets' => true],
    ]);
} catch (RuntimeException $e) {
    $fallo = str_contains($e->getMessage(), 'verificación posterior');
}
opAssert($fallo, 'Leonidas no debe afirmar que terminó si falla la verificación posterior.');

echo "LeonidasOperationalService: OK (" . count($definiciones) . " capacidades)\n";
