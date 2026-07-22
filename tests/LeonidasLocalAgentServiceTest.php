<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasLocalAgentService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasAgentService.php';

use Services\LeonidasLocalAgentService;
use Services\LeonidasAgentService;

function localAssert(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

function localSame($esperado, $actual, string $mensaje): void
{
    if ($esperado !== $actual) {
        throw new RuntimeException($mensaje . ' Esperado: ' . var_export($esperado, true) . ' Actual: ' . var_export($actual, true));
    }
}

$estados = [
    'segundometro' => ['estado' => 'down', 'listening' => false, 'pid' => null, 'http_status' => null],
    'correos_pp' => ['estado' => 'up', 'listening' => true, 'pid' => 31101, 'http_status' => 200],
    'gastos_cobranza' => ['estado' => 'up', 'listening' => true, 'pid' => 31201, 'http_status' => 200],
];
$ordenes = [];
$servicio = new LeonidasLocalAgentService([
    'status' => static function (string $id) use (&$estados): array {
        return $estados[$id];
    },
    'control' => static function (string $id, string $accion) use (&$estados, &$ordenes): array {
        $ordenes[] = [$id, $accion];
        if ($accion === 'parar') {
            $estados[$id] = ['estado' => 'down', 'listening' => false, 'pid' => null, 'http_status' => null];
        } else {
            $puerto = ['segundometro' => 3100, 'correos_pp' => 3110, 'gastos_cobranza' => 3120][$id];
            $estados[$id] = ['estado' => 'up', 'listening' => true, 'pid' => $puerto * 10, 'http_status' => 200];
        }
        return ['success' => true, 'estado' => $estados[$id]];
    },
]);
$contexto = ['actor_id' => 878, 'permisos_agente' => ['servicios_locales' => true]];

$estado = $servicio->resolver('como estan los agentes', 'como estan los agentes', $contexto);
localSame('agente_estado', $estado['tipo'], 'La consulta debe responder sin propuesta.');
localSame(3, count($estado['servicios']), 'Debe informar exactamente los tres agentes permitidos.');

$revision = $servicio->resolver('revisa el agente de primeros pagos', 'revisa el agente de primeros pagos', $contexto);
localSame('agente_estado', $revision['tipo'], 'Revisar debe interpretarse como una consulta de estado.');
localSame('correos_pp', $revision['servicios'][0]['servicio'], 'Debe reconocer el agente de correos de primeros pagos.');

$propuesta = $servicio->resolver('levanta el shell segundometro', 'levanta el shell segundometro', $contexto);
localSame('agente_propuesta', $propuesta['tipo'], 'Iniciar debe exigir confirmacion.');
localSame(LeonidasLocalAgentService::ACTION, $propuesta['propuesta_especificacion']['accion'], 'Debe usar el ejecutor limitado.');
localSame('segundometro', $propuesta['propuesta_especificacion']['payload']['servicio'], 'Debe identificar Segundometro.');
localSame([], $ordenes, 'Resolver nunca debe tocar procesos.');

$ejecutado = $servicio->ejecutar(
    LeonidasLocalAgentService::ACTION,
    $propuesta['propuesta_especificacion']['payload'],
    $contexto
);
localSame('agente_ejecutado', $ejecutado['tipo'], 'La orden confirmada debe ejecutarse.');
localSame([['segundometro', 'iniciar']], $ordenes, 'Debe ejecutar una sola orden permitida.');
localAssert(!empty($ejecutado['ejecucion']['verificado']), 'Debe marcar la comprobacion final.');

$sinCambio = $servicio->resolver('inicia segundometro', 'inicia segundometro', $contexto);
localSame('agente_estado', $sinCambio['tipo'], 'No debe proponer iniciar un agente que ya esta arriba.');
localSame(1, count($ordenes), 'La idempotencia no debe ejecutar otra orden.');

$reinicio = $servicio->resolver('reinicia primeros pagos', 'reinicia primeros pagos', $contexto);
localSame('reiniciar', $reinicio['propuesta_especificacion']['payload']['operacion'], 'Debe detectar reinicio antes que inicio.');

$detener = $servicio->resolver('deten gastos de cobranza', 'deten gastos de cobranza', $contexto);
localSame('parar', $detener['propuesta_especificacion']['payload']['operacion'], 'Debe mapear detener a parar.');

$detenerNatural = $servicio->resolver('para el agente shell gastos de cobranza', 'para el agente shell gastos de cobranza', $contexto);
localSame('parar', $detenerNatural['propuesta_especificacion']['payload']['operacion'], 'Debe aceptar para el agente sin confundir para como preposicion.');

$iniciarConPreposicion = $servicio->resolver(
    'inicia el agente shell segundometro para generar los reportes',
    'inicia el agente shell segundometro para generar los reportes',
    $contexto
);
localSame('agente_estado', $iniciarConPreposicion['tipo'], 'La palabra para usada como preposicion no debe convertir iniciar en detener.');

$enrutador = new LeonidasAgentService(['local_agent_service' => $servicio]);
$porLeonidas = $enrutador->resolver(
    'reinicia el agente correos primeros pagos',
    'reinicia el agente correos primeros pagos',
    $contexto
);
localSame('agente_propuesta', $porLeonidas['tipo'], 'El enrutador principal debe entregar la propuesta del agente local.');
localSame('correos_pp', $porLeonidas['propuesta_especificacion']['payload']['servicio'], 'Leonidas debe conservar el servicio detectado.');
localSame('reiniciar', $porLeonidas['propuesta_especificacion']['payload']['operacion'], 'Leonidas debe conservar la operacion detectada.');

$denegado = $servicio->resolver('reinicia segundometro', 'reinicia segundometro', ['actor_id' => 900, 'permisos_agente' => ['servicios_locales' => true]]);
localSame('agente_denegado', $denegado['tipo'], 'Solo Lazaro puede controlar procesos locales.');

try {
    $servicio->ejecutar(LeonidasLocalAgentService::ACTION, ['servicio' => 'cmd_libre', 'operacion' => 'iniciar'], $contexto);
    throw new RuntimeException('Una ruta fuera de la lista segura no debe ejecutarse.');
} catch (RuntimeException $error) {
    localAssert(str_contains($error->getMessage(), 'lista segura'), 'Debe explicar el rechazo de comandos no permitidos.');
}

echo "LeonidasLocalAgentServiceTest OK\n";
