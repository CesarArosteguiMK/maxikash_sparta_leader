<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasAgentService.php';

use Services\LeonidasAgentService;
use Services\LeonidasCapitalHumanoService;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

function context(array $overrides = []): array
{
    return $overrides + [
        'actor_id' => 878,
        'nombre_corto' => 'Lazaro',
        'permisos_agente' => ['convenio' => true, 'motos' => true, 'id_celula' => 1],
    ];
}

function ofertaResult(int $credito = 1600): array
{
    return [
        'success' => true,
        'mensaje' => 'Ofertas calculadas.',
        'datos' => [
            'credito' => [
                'Id_credito' => $credito,
                'Nombre_cliente' => 'CLIENTE PRUEBA',
                'Bucket_Morosidad_Real' => '8-30',
                'Dias_mora' => 18,
                'Avance_Pago_Plazo' => '20/52',
                'Adeudo_total' => 10000,
            ],
            'ofertas' => [[
                'id_producto' => 7,
                'id_detalle' => 70,
                'nombre' => 'Convenio semanal prueba',
                'tipo_calendario' => 'semanal',
                'porcentaje_descuento' => 20,
                'base_calculo' => 'adeudo_total',
                'descuento_monto' => 2000,
                'total_a_pagar' => 8000,
                'pago_inicial_monto' => null,
                'periodo_inicio' => 4,
                'periodo_fin_producto' => 12,
                'semanas_max' => 10,
            ]],
        ],
    ];
}

function newService(array $overrides = []): LeonidasAgentService
{
    $base = [
        'convenio_ofertas' => static fn(int $id): array => ofertaResult($id),
        'convenio_guardar' => static fn(array $datos): array => ['success' => true, 'mensaje' => 'OK', 'datos' => ['id_convenio' => 901]],
        'moto_buscar' => static fn(int $id): array => [
            'success' => true,
            'credito' => ['id_credito' => $id, 'nombre_cliente' => 'CLIENTE MOTO', 'status_credito' => 'Vencido'],
            'status_credito' => 'Vencido',
            'asignacion' => null,
        ],
        'moto_responsables' => static fn(): array => [[
            'id_persona' => 55,
            'nombre_completo' => 'RESPONSABLE UNO',
            'puesto' => 'Analista',
            'numero_empleado' => '126',
            'codigo_contpac' => '9001',
        ]],
        'moto_responsable_activo' => static fn(int $id): bool => $id === 55,
        'moto_asignar' => static fn(int $persona, int $credito, int $actor): array => [
            'success' => true,
            'partial' => false,
            'local' => ['success' => true, 'id_credito' => $credito, 'id_persona' => $persona],
            'legacy' => [
                'success' => true,
                'task_id' => 910661,
                'duplicate' => false,
                'verificacion' => [
                    'success' => true,
                    'task_id' => 910661,
                    'external_id' => '126',
                    'responsable' => 'RESPONSABLE UNO',
                    'client_name' => 'CLIENTE MOTO',
                    'responsable_correcto' => true,
                    'asignacion_activa' => true,
                    'cliente_correcto' => true,
                ],
            ],
        ],
    ];
    return new LeonidasAgentService($overrides + $base);
}

$_SESSION = [];
$service = newService();
$start = $service->resolver('quiero levantar un convenio', 'quiero levantar un convenio', context());
assertSameValue('agente_pregunta', $start['tipo'], 'El convenio debe pedir el credito.');
$offers = $service->resolver('1600', '1600', context());
assertSameValue('agente_opciones', $offers['tipo'], 'Debe presentar ofertas reales.');
$choose = $service->resolver('1', '1', context());
assertTrue(str_contains($choose['mensaje'], '4 a 10 semanas'), 'Debe informar el rango permitido.');
$proposal = $service->resolver('8 semanas', '8 semanas', context());
assertSameValue('convenio_crear', $proposal['propuesta_especificacion']['accion'], 'Debe preparar la accion de convenio.');
assertSameValue(8, $proposal['propuesta_especificacion']['payload']['semanas'], 'Debe conservar las semanas elegidas.');

$saved = [];
$executor = newService([
    'convenio_guardar' => static function (array $datos) use (&$saved): array {
        $saved = $datos;
        return ['success' => true, 'mensaje' => 'OK', 'datos' => ['id_convenio' => 902]];
    },
]);
$executed = $executor->ejecutar('convenio_crear', $proposal['propuesta_especificacion']['payload'], context());
assertSameValue('agente_ejecutado', $executed['tipo'], 'Debe ejecutar el convenio confirmado.');
assertSameValue(1000.0, $saved['pago_semanal'], 'El pago semanal debe recalcularse en servidor.');
assertSameValue(878, $saved['usuario_alta'], 'Debe auditar con el actor autenticado.');
assertSameValue(1, $saved['id_celula'], 'Debe conservar la celula autorizada.');

$stale = newService(['convenio_ofertas' => static fn(int $id): array => [
    'success' => true,
    'datos' => ['credito' => ['Id_credito' => $id], 'ofertas' => []],
]]);
try {
    $stale->ejecutar('convenio_crear', $proposal['propuesta_especificacion']['payload'], context());
    throw new RuntimeException('Una oferta retirada no debe ejecutarse.');
} catch (RuntimeException $error) {
    assertTrue(str_contains($error->getMessage(), 'ya no está disponible'), 'Debe explicar que la oferta cambio.');
}

$_SESSION = [];
$moto = newService();
$moto->resolver('quiero adjudicar una moto', 'quiero adjudicar una moto', context());
$responsables = $moto->resolver('1881', '1881', context());
assertSameValue('agente_opciones', $responsables['tipo'], 'Debe listar responsables activos.');
$motoProposal = $moto->resolver('1', '1', context());
assertSameValue('moto_asignar', $motoProposal['propuesta_especificacion']['accion'], 'Debe preparar la asignacion de moto.');
$motoExecuted = $moto->ejecutar('moto_asignar', $motoProposal['propuesta_especificacion']['payload'], context());
assertSameValue('agente_ejecutado', $motoExecuted['tipo'], 'Debe asignar el credito confirmado.');
assertTrue(str_contains($motoExecuted['mensaje'], 'Tarea 910661 creada'), 'Debe informar la tarea Legacy verificada.');
assertTrue(str_contains($motoExecuted['mensaje'], 'Asignación activa en Sparta'), 'Debe confirmar la asignacion local verificada.');

$_SESSION = [];
$leticia = newService([
    'moto_buscar' => static fn(int $id): array => [
        'success' => true,
        'credito' => [
            'id_credito' => $id,
            'nombre_cliente' => 'CARLOS NOE CRUZ AGUILAR',
            'status_credito' => 'Vencido',
        ],
        'status_credito' => 'Vencido',
        'asignacion' => null,
    ],
    'moto_responsables' => static fn(): array => [[
        'id_persona' => 126,
        'nombre_completo' => 'LETICIA PEREZ CRUZ',
        'puesto' => 'Analista',
        'numero_empleado' => '126',
        'codigo_contpac' => '7821',
    ]],
    'moto_responsable_activo' => static fn(int $id): bool => $id === 126,
    'moto_asignar' => static fn(int $persona, int $credito, int $actor): array => [
        'success' => true,
        'partial' => false,
        'local' => ['success' => true, 'id_credito' => $credito, 'id_persona' => $persona],
        'legacy' => [
            'success' => true,
            'task_id' => 910661,
            'duplicate' => false,
            'verificacion' => [
                'success' => true,
                'task_id' => 910661,
                'external_id' => '126',
                'responsable' => 'LETICIA PEREZ CRUZ',
                'client_name' => 'CARLOS NOE CRUZ AGUILAR',
                'responsable_correcto' => true,
                'asignacion_activa' => true,
                'cliente_correcto' => true,
            ],
        ],
    ],
]);
$solicitudDirecta = "en motos adjudicadas puedes asignar el credito 1254060 que es carlos Noe cruz al usuario\n"
    . "**No. empleado:**\n**126**\n**LETICIA PEREZ CRUZ**\n**External id:**\n**126**";
$propuestaDirecta = $leticia->resolver($solicitudDirecta, $solicitudDirecta, context());
assertSameValue('agente_propuesta', $propuestaDirecta['tipo'], 'Debe interpretar la solicitud completa en un solo mensaje.');
assertSameValue(1254060, $propuestaDirecta['propuesta_especificacion']['payload']['id_credito'], 'Debe conservar el credito indicado.');
assertSameValue(126, $propuestaDirecta['propuesta_especificacion']['payload']['id_persona'], 'Debe resolver a Leticia por numero de empleado.');
assertSameValue('126', $propuestaDirecta['propuesta_especificacion']['payload']['external_id'], 'External id debe corresponder al numero de empleado, no al codigo CONTPAC.');
$directaEjecutada = $leticia->ejecutar('moto_asignar', $propuestaDirecta['propuesta_especificacion']['payload'], context());
assertSameValue('agente_ejecutado', $directaEjecutada['tipo'], 'Debe ejecutar la solicitud directa confirmada.');
assertTrue(str_contains($directaEjecutada['mensaje'], 'CARLOS NOE CRUZ AGUILAR'), 'Debe informar el cliente verificado en Legacy.');
assertTrue(str_contains($directaEjecutada['mensaje'], 'LETICIA PEREZ CRUZ'), 'Debe informar el responsable verificado.');
assertSameValue(910661, $directaEjecutada['ejecucion']['task_id'], 'Debe exponer el task Legacy verificado.');

$_SESSION = [];
$idsDistintos = str_replace("**126**\n**LETICIA PEREZ CRUZ**\n**External id:**\n**126**", "**126**\n**LETICIA PEREZ CRUZ**\n**External id:**\n**999**", $solicitudDirecta);
$rechazoIds = $leticia->resolver($idsDistintos, $idsDistintos, context());
assertSameValue('agente_error', $rechazoIds['tipo'], 'Debe rechazar numero de empleado y external id contradictorios.');

$_SESSION = [];
$nombreIncorrecto = str_replace('LETICIA PEREZ CRUZ', 'OTRA PERSONA', $solicitudDirecta);
$rechazoNombre = $leticia->resolver($nombreIncorrecto, $nombreIncorrecto, context());
assertSameValue('agente_error', $rechazoNombre['tipo'], 'Debe rechazar un nombre que no pertenece al identificador.');
assertTrue(str_contains($rechazoNombre['mensaje'], 'LETICIA PEREZ CRUZ'), 'Debe explicar a quien pertenece realmente el identificador.');

$_SESSION = [];
$legacyCaido = newService([
    'moto_asignar' => static fn(int $persona, int $credito, int $actor): array => [
        'success' => false,
        'partial' => true,
        'message' => 'Legacy no respondió.',
        'local' => ['success' => true],
        'legacy' => ['success' => false],
    ],
]);
$parcial = $legacyCaido->ejecutar('moto_asignar', $motoProposal['propuesta_especificacion']['payload'], context());
assertSameValue('agente_ejecucion_parcial', $parcial['tipo'], 'Un fallo Legacy debe reportarse como ejecucion parcial.');
assertTrue(str_contains($parcial['mensaje'], 'activa en Sparta'), 'Debe explicar que la parte local si quedo activa.');
assertTrue(str_contains($parcial['mensaje'], 'Legacy'), 'Debe explicar que fallo la parte Legacy.');

$_SESSION = [];
$assigned = newService(['moto_buscar' => static fn(int $id): array => [
    'success' => true,
    'credito' => ['id_credito' => $id, 'nombre_cliente' => 'CLIENTE'],
    'asignacion' => ['nombre_despacho' => 'RESPONSABLE EXISTENTE'],
]]);
$assigned->resolver('quiero adjudicar una moto', 'quiero adjudicar una moto', context());
$blocked = $assigned->resolver('1881', '1881', context());
assertSameValue('agente_error', $blocked['tipo'], 'No debe duplicar una asignacion activa.');

$_SESSION = [];
$current = newService(['moto_buscar' => static fn(int $id): array => [
    'success' => true,
    'credito' => ['id_credito' => $id, 'nombre_cliente' => 'CLIENTE', 'status_credito' => 'Activo'],
    'status_credito' => 'Activo',
    'asignacion' => null,
]]);
$current->resolver('quiero adjudicar una moto', 'quiero adjudicar una moto', context());
$wrongStatus = $current->resolver('1881', '1881', context());
assertSameValue('agente_error', $wrongStatus['tipo'], 'Solo debe admitir creditos vencidos.');
assertTrue(str_contains($wrongStatus['mensaje'], 'Activo'), 'Debe explicar el estatus que impide la asignacion.');

$changedStatus = newService(['moto_buscar' => static fn(int $id): array => [
    'success' => true,
    'credito' => ['id_credito' => $id, 'nombre_cliente' => 'CLIENTE', 'status_credito' => 'Liquidado'],
    'status_credito' => 'Liquidado',
    'asignacion' => null,
]]);
try {
    $changedStatus->ejecutar('moto_asignar', $motoProposal['propuesta_especificacion']['payload'], context());
    throw new RuntimeException('Un credito que cambio de estatus no debe asignarse.');
} catch (RuntimeException $error) {
    assertTrue(str_contains($error->getMessage(), 'cambió de estatus'), 'Debe explicar el cambio de estatus.');
}

$_SESSION = [];
$denied = newService()->resolver(
    'quiero levantar un convenio',
    'quiero levantar un convenio',
    context(['permisos_agente' => ['convenio' => false, 'motos' => false, 'id_celula' => null]])
);
assertSameValue('agente_denegado', $denied['tipo'], 'Debe bloquear el flujo sin permisos.');

$_SESSION = [];
$capitalHumano = new LeonidasCapitalHumanoService([
    'persona_buscar' => static fn(string $criterio): array => ['success' => true, 'datos' => [[
        'id' => 31,
        'nombre_completo' => 'JENIFER ITZEL PEINADO ALCALA',
    ]]],
    'persona_detalle' => static fn(int $id): array => ['success' => true, 'datos' => [
        'id' => $id,
        'nombres' => 'JENIFER',
        'segundo_nombre' => 'ITZEL',
        'apellidop' => 'PEINADO',
        'apellidom' => 'ALCALA',
        'estatus' => 'Activo',
        'numero_empleado' => '999999080',
        'codigo_contpac' => '31',
        'puesto' => 'Gerente',
        'departamento' => 'Atencion a Clientes',
    ]],
    'persona_documentos' => static fn(int $id): array => ['success' => true, 'datos' => ['total' => 10]],
    'vacaciones_resumen' => static fn(int $id): array => ['success' => true, 'datos' => ['periodo' => ['dias_disponibles' => 8]]],
    'permiso_actualizar' => static fn(int $persona, int $modulo, bool $asignado): array => [
        'success' => true,
        'mensaje' => 'Permiso actualizado.',
    ],
    'auditar' => static function (array $evento): void {
    },
]);
$capitalAgent = newService(['capital_humano_service' => $capitalHumano]);
$capitalContext = context(['permisos_agente' => [
    'rrhh_lectura' => true,
    'vacaciones' => true,
    'permisos' => true,
]]);
$ficha = $capitalAgent->resolver('ficha 360 de Jenifer Peinado', 'ficha 360 de jenifer peinado', $capitalContext);
assertSameValue('rrhh_ficha_360', $ficha['tipo'], 'El agente principal debe delegar la ficha 360 a Capital Humano.');
assertTrue(str_contains($ficha['mensaje'], 'JENIFER ITZEL PEINADO ALCALA'), 'La ficha 360 debe regresar por el chat principal.');

$permiso = $capitalAgent->resolver(
    'otorga el permiso 191 a la persona 31',
    'otorga el permiso 191 a la persona 31',
    $capitalContext
);
assertSameValue('agente_propuesta', $permiso['tipo'], 'El agente principal debe preparar acciones de Capital Humano.');
$permisoEjecutado = $capitalAgent->ejecutar(
    'permiso_actualizar',
    $permiso['propuesta_especificacion']['payload'],
    $capitalContext
);
assertSameValue('agente_ejecucion_exitosa', $permisoEjecutado['tipo'], 'El agente principal debe ejecutar la accion confirmada de Capital Humano.');

echo "LeonidasAgentService: OK\n";
