<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasAgentService.php';

use Services\LeonidasAgentService;

function dictamenAssert(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

function dictamenContexto(array $cambios = []): array
{
    return $cambios + [
        'actor_id' => 878,
        'nombre_corto' => 'Lazaro',
        'permisos_agente' => ['convenio' => true, 'motos' => true, 'id_celula' => 1],
    ];
}

function dictamenDiagnostico(int $credito = 1721947, array $cambios = []): array
{
    return array_replace_recursive([
        'success' => true,
        'id_credito' => $credito,
        's2' => ['success' => false],
        'segundometro' => ['Id_credito' => $credito],
        'operacion' => null,
        'legacy' => ['task' => null, 'dictamen' => null, 'error' => null],
        'puede_simular' => false,
        'desbloqueo_s2_disponible' => true,
        'bloqueos' => ['No se pudo validar el credito en S2; no se permite avanzar sin estado de cuenta.'],
    ], $cambios);
}

function dictamenServicio(array $cambios = []): LeonidasAgentService
{
    $base = [
        'dictamen_diagnosticar' => static fn(int $credito): array => dictamenDiagnostico($credito),
        'dictamen_autorizacion' => static fn(int $usuario): array => [
            'success' => true,
            'authorized' => true,
            'permiso_modulo' => true,
            'nip_configurado' => true,
        ],
        'dictamen_desbloquear_s2' => static fn(int $credito, string $nip, int $usuario): array =>
            $nip === '123456'
                ? ['success' => true, 'message' => 'OK']
                : ['success' => false, 'message' => 'NIP incorrecto o usuario sin permiso de desbloqueo.'],
        'dictamen_desbloquear_componentes' => static fn(int $credito, string $nip, int $usuario, string $ip): array =>
            $nip === '123456'
                ? ['success' => true, 'deleted' => [
                    'legacy_tasks' => 1,
                    'legacy_task_user_assignments' => 2,
                    'legacy_dictums' => 1,
                    'adj_operacion' => 1,
                ]]
                : ['success' => false, 'message' => 'NIP incorrecto o usuario sin permiso de desbloqueo.'],
    ];

    return new LeonidasAgentService($cambios + $base);
}

$_SESSION = [];
$servicio = dictamenServicio();
$diagnostico = $servicio->resolver(
    'Diagnostica el dictamen del credito 1721947',
    'diagnostica el dictamen del credito 1721947',
    dictamenContexto()
);
dictamenAssert(($diagnostico['tipo'] ?? '') === 'agente_diagnostico', 'Debe resolver el diagnostico sin pedir NIP.');
dictamenAssert(str_contains((string) $diagnostico['mensaje'], 'Segundometro: credito localizado'), 'Debe explicar Segundometro.');
dictamenAssert(str_contains((string) $diagnostico['mensaje'], 'Dictamen 13: libre'), 'Debe explicar el dictamen Legacy.');

$_SESSION = [];
$solicitudS2 = $servicio->resolver(
    'Desbloquea la validacion S2 del credito 1721947',
    'desbloquea la validacion s2 del credito 1721947',
    dictamenContexto()
);
dictamenAssert(($solicitudS2['entrada_segura'] ?? '') === 'nip', 'El desbloqueo S2 debe solicitar entrada segura.');
dictamenAssert(!str_contains(json_encode($_SESSION), '123456'), 'La tarea no debe guardar ningun NIP.');
dictamenAssert($servicio->entradaSeguraPendiente(878) === 'nip', 'Una recarga debe restaurar el campo NIP protegido.');
dictamenAssert($servicio->entradaSeguraPendiente(999) === null, 'Otra sesion no debe heredar el campo NIP protegido.');

$formatoInvalido = $servicio->resolver('12ab', '12ab', dictamenContexto());
dictamenAssert(($formatoInvalido['entrada_segura'] ?? '') === 'nip', 'Un formato invalido debe mantener el campo seguro.');
dictamenAssert(str_contains((string) $formatoInvalido['mensaje'], 'exactamente 6 digitos'), 'Debe explicar el formato del NIP.');

$nipIncorrecto = $servicio->resolver('111111', '111111', dictamenContexto());
dictamenAssert(($nipIncorrecto['entrada_segura'] ?? '') === 'nip', 'Un NIP incorrecto debe permitir reintento seguro.');
dictamenAssert(!str_contains((string) $nipIncorrecto['mensaje'], '111111'), 'Nunca debe repetir el NIP en la respuesta.');

$s2Ejecutado = $servicio->resolver('123456', '123456', dictamenContexto());
dictamenAssert(($s2Ejecutado['tipo'] ?? '') === 'agente_ejecutado', 'El NIP correcto debe ejecutar el desbloqueo S2.');
dictamenAssert(($s2Ejecutado['ejecucion']['accion'] ?? '') === 'desbloquear_s2', 'Debe registrar la accion S2 ejecutada.');
dictamenAssert(!isset($_SESSION['leonidas_agent_task']), 'La tarea debe limpiarse despues de ejecutar.');
dictamenAssert($servicio->entradaSeguraPendiente(878) === null, 'El campo NIP debe desactivarse despues de ejecutar.');

$_SESSION = [];
$sinPermiso = dictamenServicio([
    'dictamen_autorizacion' => static fn(int $usuario): array => [
        'success' => true,
        'authorized' => false,
        'permiso_modulo' => false,
        'nip_configurado' => false,
    ],
]);
$denegado = $sinPermiso->resolver(
    'Desbloquea la validacion S2 del credito 1721947',
    'desbloquea la validacion s2 del credito 1721947',
    dictamenContexto()
);
dictamenAssert(($denegado['tipo'] ?? '') === 'agente_denegado', 'Un usuario sin permiso debe ser rechazado antes del NIP.');
dictamenAssert(!isset($denegado['entrada_segura']), 'No debe pedir NIP a un usuario no autorizado.');

$_SESSION = [];
$bloqueoMixto = dictamenServicio([
    'dictamen_diagnosticar' => static fn(int $credito): array => dictamenDiagnostico($credito, [
        'operacion' => ['id' => 91],
        'desbloqueo_s2_disponible' => false,
        'bloqueos' => [
            'No se pudo validar el credito en S2; no se permite avanzar sin estado de cuenta.',
            'Ya existe una operacion local.',
        ],
    ]),
]);
$s2NoPermitido = $bloqueoMixto->resolver(
    'Desbloquea la validacion S2 del credito 1721947',
    'desbloquea la validacion s2 del credito 1721947',
    dictamenContexto()
);
dictamenAssert(($s2NoPermitido['tipo'] ?? '') === 'agente_denegado', 'S2 no debe desbloquearse cuando hay otros bloqueos.');
dictamenAssert(!isset($s2NoPermitido['entrada_segura']), 'No debe pedir NIP para una ruta S2 no permitida.');

$_SESSION = [];
$destructivo = dictamenServicio();
$advertencia = $destructivo->resolver(
    'Limpia y desbloquea los componentes del credito 1721947',
    'limpia y desbloquea los componentes del credito 1721947',
    dictamenContexto()
);
dictamenAssert(($advertencia['tipo'] ?? '') === 'agente_confirmacion_destructiva', 'La limpieza debe pedir confirmacion explicita.');
dictamenAssert(str_contains((string) $advertencia['mensaje'], 'CONFIRMAR LIMPIEZA'), 'Debe indicar la frase de confirmacion.');

$fraseAmbigua = $destructivo->resolver('si', 'si', dictamenContexto());
dictamenAssert(($fraseAmbigua['tipo'] ?? '') === 'agente_confirmacion_destructiva', 'Un si ambiguo no debe autorizar una limpieza.');
dictamenAssert(!isset($fraseAmbigua['entrada_segura']), 'No debe pedir NIP sin confirmacion destructiva valida.');

$confirmado = $destructivo->resolver('CONFIRMAR LIMPIEZA', 'confirmar limpieza', dictamenContexto());
dictamenAssert(($confirmado['entrada_segura'] ?? '') === 'nip', 'Tras confirmar debe solicitar NIP seguro.');
$limpiezaEjecutada = $destructivo->resolver('123456', '123456', dictamenContexto());
dictamenAssert(($limpiezaEjecutada['tipo'] ?? '') === 'agente_ejecutado', 'La limpieza autorizada debe ejecutarse.');
dictamenAssert(str_contains((string) $limpiezaEjecutada['mensaje'], '1 tarea(s) Legacy'), 'Debe informar lo eliminado.');

$_SESSION = [];
$cancelable = dictamenServicio();
$cancelable->resolver(
    'Limpia y desbloquea los componentes del credito 1721947',
    'limpia y desbloquea los componentes del credito 1721947',
    dictamenContexto()
);
$cancelado = $cancelable->resolver('cancelar', 'cancelar', dictamenContexto());
dictamenAssert(($cancelado['tipo'] ?? '') === 'agente_cancelado', 'Cancelar debe terminar el flujo sin ejecutar.');
dictamenAssert(!isset($_SESSION['leonidas_agent_task']), 'Cancelar debe limpiar la tarea.');
dictamenAssert($cancelable->entradaSeguraPendiente(878) === null, 'Cancelar no debe dejar un campo seguro pendiente.');

echo "LeonidasDictamenAgentServiceTest OK\n";
