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

assertTrue(class_exists(Models\Adjudicacion::class, false), 'El agente debe cargar el modelo real de Motos Adjudicadas.');
assertTrue(class_exists(Models\Convenios::class, false), 'El agente debe cargar el modelo real de Convenios.');

function context(array $overrides = []): array
{
    return $overrides + [
        'actor_id' => 878,
        'nombre_corto' => 'Lazaro',
        'permisos_agente' => [
            'convenio' => true,
            'convenio_reactivar_cancelado' => true,
            'motos' => true,
            'id_celula' => 1,
        ],
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
        'convenio_excepcion_preparar' => static function (int $id, string $fecha, float $monto): array {
            $adeudo = $id === 172307 ? 19128.50 : 670.00;
            $descuento = max(0, round($adeudo - $monto, 2));
            return ['success' => true, 'datos' => [
                'id_credito' => $id,
                'cliente' => 'CLIENTE EXCEPCION',
                'fecha_pago' => $fecha,
                'monto' => $monto,
                'adeudo_s2' => $adeudo,
                'descuento_monto' => $descuento,
                'porcentaje_descuento' => $descuento > 0 ? round(($descuento / $adeudo) * 100, 2) : 0,
                'monto_adicional' => max(0, round($monto - $adeudo, 2)),
                'producto' => 'Convenio Pago Mixto',
            ]];
        },
        'convenio_excepcion_guardar' => static function (array $datos): array {
            $adeudo = (int) $datos['id_credito'] === 172307 ? 19128.50 : 670.00;
            $monto = (float) $datos['monto'];
            $descuento = max(0, round($adeudo - $monto, 2));
            return ['success' => true, 'datos' => [
                'id_convenio' => (int) $datos['id_credito'] === 172307 ? 999 : 1001,
                'producto' => 'Convenio Pago Mixto',
                'adeudo_s2' => $adeudo,
                'descuento_monto' => $descuento,
                'porcentaje_descuento' => $descuento > 0 ? round(($descuento / $adeudo) * 100, 2) : 0,
                'monto_adicional' => max(0, round($monto - $adeudo, 2)),
                'estatus' => 'activo',
                'estatus_cuota' => 'pendiente',
            ]];
        },
        'convenio_reactivacion_diagnosticar' => static fn(int $credito, int $convenio): array => [
            'success' => true,
            'datos' => [
                'id_convenio' => $convenio > 0 ? $convenio : 777,
                'id_credito' => $credito > 0 ? $credito : 172307,
                'cliente' => 'CLIENTE REACTIVACION',
                'producto' => 'Convenio Pago Mixto',
                'total_a_pagar' => 11500.00,
                'cuotas_total' => 1,
                'cuotas_canceladas' => 1,
                'cuotas_pagadas' => 0,
            ],
        ],
        'convenio_reactivar_cancelado' => static fn(int $convenio, string $usuario, string $motivo): array => [
            'success' => true,
            'datos' => [
                'id_convenio' => $convenio,
                'id_credito' => 172307,
                'cuotas_reabiertas' => 1,
                'cuotas_vencidas' => 1,
                'cuotas_pendientes' => 0,
                'cuotas_pagadas' => 0,
            ],
        ],
        'convenio_asignacion_actual' => static fn(int $credito): ?array => [
            'id' => 500,
            'id_credito' => $credito,
            'id_persona' => 55,
            'id_celula' => 1,
            'nombre_completo' => 'RESPONSABLE CONVENIO',
        ],
        'convenio_responsables_buscar' => static fn(int $celula, string $busqueda): array => [],
        'convenio_responsable_actual' => static fn(int $persona, int $celula): ?array => null,
        'convenio_asignar_credito' => static fn(int $persona, int $credito, int $celula, int $actor): array => [
            'success' => true,
            'asignacion' => [
                'id' => 501,
                'id_credito' => $credito,
                'id_persona' => $persona,
                'id_celula' => $celula,
                'nombre_completo' => 'RESPONSABLE CONVENIO',
            ],
        ],
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
                    'legacy_user_id' => 75,
                    'campaign_name' => 'ASIGNACION_W29_1A7',
                    'external_id' => '126',
                    'responsable' => 'RESPONSABLE UNO',
                    'client_name' => 'CLIENTE MOTO',
                    'responsable_correcto' => true,
                    'asignacion_activa' => true,
                    'asignacion_exclusiva' => true,
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
$excepcionDosTurnos = newService();
$solicitudExcepcion = 'crea convenio de pago mixto de un solo pago con fecha de 5 de julio del 2026, '
    . 'donde vas descartar las reglas de negocio q se establecieron el objetivo es q se genere como convenio de excepcion '
    . 'el pago es de 11500';
$preguntaCredito = $excepcionDosTurnos->resolver($solicitudExcepcion, $solicitudExcepcion, context());
assertSameValue('agente_pregunta', $preguntaCredito['tipo'], 'La excepcion sin credito debe conservar monto y fecha y pedir el credito.');
assertTrue(str_contains($preguntaCredito['mensaje'], '$11,500.00'), 'Debe repetir el monto interpretado antes de pedir el credito.');
assertTrue(str_contains($preguntaCredito['mensaje'], '05/07/2026'), 'Debe repetir la fecha interpretada antes de pedir el credito.');
$propuestaExcepcion = $excepcionDosTurnos->resolver('el credito es 172307', 'el credito es 172307', context());
assertSameValue('agente_propuesta', $propuestaExcepcion['tipo'], 'La segunda intervencion debe completar la propuesta excepcional.');
assertSameValue('convenio_crear_excepcion', $propuestaExcepcion['propuesta_especificacion']['accion'], 'Debe usar el ejecutor excepcional dedicado.');
assertSameValue(172307, $propuestaExcepcion['propuesta_especificacion']['payload']['id_credito'], 'No debe confundir el anio con el credito.');
assertSameValue('2026-07-05', $propuestaExcepcion['propuesta_especificacion']['payload']['fecha_pago'], 'Debe conservar la fecha historica.');
$excepcionCreada = $excepcionDosTurnos->ejecutar(
    'convenio_crear_excepcion',
    $propuestaExcepcion['propuesta_especificacion']['payload'],
    context()
);
assertSameValue('agente_ejecutado', $excepcionCreada['tipo'], 'Debe ejecutar la excepcion confirmada.');
assertTrue(str_contains($excepcionCreada['mensaje'], '#999'), 'Debe responder con el folio creado.');
assertTrue(str_contains($excepcionCreada['mensaje'], 'Base S2 hist'), 'Debe informar la base S2 verificada.');
assertTrue(str_contains($excepcionCreada['mensaje'], '$7,628.50'), 'Debe informar el descuento excepcional.');

$_SESSION = [];
$solicitudExcepcionCompleta = 'crea convenio de pago mixto de un solo pago con fecha de 4 de julio del 2026, '
    . 'donde vas descartar las reglas de negocio q se establecieron el objetivo es q se genere como convenio de excepcion '
    . 'el pago es de 5,478 pesos el credito es 1209225';
$propuestaCompleta = newService()->resolver($solicitudExcepcionCompleta, $solicitudExcepcionCompleta, context());
assertSameValue('agente_propuesta', $propuestaCompleta['tipo'], 'La frase completa debe resolverse en un turno.');
assertSameValue(5478.0, $propuestaCompleta['propuesta_especificacion']['payload']['monto'], 'Debe interpretar la coma como separador de miles.');
assertTrue(str_contains($propuestaCompleta['mensaje'], '$4,808.00'), 'Debe explicar el monto superior a la base sin descuento negativo.');

$_SESSION = [];
$solicitudCaptura = 'crea convenio de pago mixto de un solo pago con fecha de 03 de agosto del 2026, '
    . 'donde vas descartar las reglas de negocio q se establecieron el objetivo es q se genere como convenio de excepcion '
    . 'el pago es de 20000 pesos el credito es 1496964';
$propuestaCaptura = newService()->resolver($solicitudCaptura, $solicitudCaptura, context());
assertSameValue('agente_propuesta', $propuestaCaptura['tipo'], 'La instruccion exacta de la captura debe entrar al flujo operativo excepcional.');
assertSameValue('convenio_crear_excepcion', $propuestaCaptura['propuesta_especificacion']['accion'], 'La captura no debe convertirse en una explicacion general de Convenios.');
assertSameValue(1496964, $propuestaCaptura['propuesta_especificacion']['payload']['id_credito'], 'Debe conservar el credito de la captura.');
assertSameValue('2026-08-03', $propuestaCaptura['propuesta_especificacion']['payload']['fecha_pago'], 'Debe conservar la fecha de la captura.');
assertSameValue(20000.0, $propuestaCaptura['propuesta_especificacion']['payload']['monto'], 'Debe conservar el monto de la captura.');

$_SESSION = [];
$convenioRegularSinAsignacion = newService([
    'convenio_asignacion_actual' => static fn(int $credito): ?array => null,
]);
$convenioRegularSinAsignacion->resolver('quiero levantar un convenio', 'quiero levantar un convenio', context());
$preguntaCelulaRegular = $convenioRegularSinAsignacion->resolver('1600', '1600', context());
assertSameValue('agente_opciones', $preguntaCelulaRegular['tipo'], 'El convenio regular tambien debe detenerse si falta asignacion.');
assertTrue(str_contains($preguntaCelulaRegular['mensaje'], 'no tiene una asignacion activa'), 'Debe explicar por que pregunta la celula.');

$_SESSION = [];
$asignacionConvenio = null;
$datosExcepcionAsignada = [];
$responsablesConvenio = [
    [
        'id_persona' => 301,
        'id_celula' => 2,
        'nombre_completo' => 'CARLOS ANDRES SOLANO FERNANDEZ',
        'numero_empleado' => '1348',
        'puesto' => 'AGENTE CALL CENTER',
    ],
    [
        'id_persona' => 302,
        'id_celula' => 2,
        'nombre_completo' => 'MARIA SOLANO PEREZ',
        'numero_empleado' => '1440',
        'puesto' => 'AGENTE CALL CENTER',
    ],
];
$convenioSinAsignacion = newService([
    'convenio_asignacion_actual' => static function (int $credito) use (&$asignacionConvenio): ?array {
        return $asignacionConvenio;
    },
    'convenio_responsables_buscar' => static function (int $celula, string $busqueda) use ($responsablesConvenio): array {
        return $celula === 2 && stripos($busqueda, 'solano') !== false ? $responsablesConvenio : [];
    },
    'convenio_responsable_actual' => static function (int $persona, int $celula) use ($responsablesConvenio): ?array {
        foreach ($responsablesConvenio as $responsable) {
            if ((int) $responsable['id_persona'] === $persona && (int) $responsable['id_celula'] === $celula) {
                return $responsable;
            }
        }
        return null;
    },
    'convenio_asignar_credito' => static function (int $persona, int $credito, int $celula, int $actor) use (&$asignacionConvenio, $responsablesConvenio): array {
        foreach ($responsablesConvenio as $responsable) {
            if ((int) $responsable['id_persona'] === $persona && (int) $responsable['id_celula'] === $celula) {
                $asignacionConvenio = $responsable + ['id' => 777, 'id_credito' => $credito];
                return ['success' => true, 'asignacion' => $asignacionConvenio];
            }
        }
        return ['success' => false, 'message' => 'Responsable no encontrado.'];
    },
    'convenio_excepcion_guardar' => static function (array $datos) use (&$datosExcepcionAsignada): array {
        $datosExcepcionAsignada = $datos;
        return ['success' => true, 'datos' => [
            'id_convenio' => 1008,
            'producto' => 'Convenio Pago Mixto',
            'adeudo_s2' => 3229.0,
            'descuento_monto' => 0.0,
            'porcentaje_descuento' => 0.0,
            'monto_adicional' => 5771.0,
            'estatus' => 'activo',
            'estatus_cuota' => 'pendiente',
        ]];
    },
]);
$solicitudSinAsignacion = 'crea convenio de pago mixto de un solo pago con fecha de 2 de agosto del 2026, '
    . 'como convenio de excepcion el pago es de 9000 pesos el credito es 1408958';
$preguntaCelula = $convenioSinAsignacion->resolver($solicitudSinAsignacion, $solicitudSinAsignacion, context());
assertSameValue('agente_opciones', $preguntaCelula['tipo'], 'Un credito sin asignacion debe pedir la celula antes del convenio.');
assertTrue(str_contains($preguntaCelula['mensaje'], 'Gestion Call Center'), 'Debe ofrecer Despacho, Call Center y Campo.');
$preguntaResponsable = $convenioSinAsignacion->resolver('2', '2', context());
assertSameValue('agente_pregunta', $preguntaResponsable['tipo'], 'Despues de la celula debe pedir el responsable.');
assertTrue(str_contains($preguntaResponsable['mensaje'], 'apellido'), 'Debe permitir buscar con un apellido incompleto.');
$coincidencias = $convenioSinAsignacion->resolver('Solano', 'solano', context());
assertSameValue('agente_opciones', $coincidencias['tipo'], 'Un apellido ambiguo debe mostrar coincidencias.');
assertTrue(str_contains($coincidencias['mensaje'], 'CARLOS ANDRES SOLANO'), 'Debe mostrar la primera coincidencia parcial.');
assertTrue(str_contains($coincidencias['mensaje'], 'MARIA SOLANO'), 'Debe mostrar la segunda coincidencia parcial.');
$propuestaAsignacion = $convenioSinAsignacion->resolver('2', '2', context());
assertSameValue('convenio_asignar_credito', $propuestaAsignacion['propuesta_especificacion']['accion'], 'Debe preparar una asignacion confirmable.');
assertSameValue(302, $propuestaAsignacion['propuesta_especificacion']['payload']['id_persona'], 'Debe conservar la persona elegida.');
$asignadoYContinua = $convenioSinAsignacion->ejecutar(
    'convenio_asignar_credito',
    $propuestaAsignacion['propuesta_especificacion']['payload'],
    context()
);
assertSameValue('agente_propuesta', $asignadoYContinua['tipo'], 'Tras asignar debe continuar con la propuesta del convenio.');
assertSameValue('convenio_crear_excepcion', $asignadoYContinua['propuesta_especificacion']['accion'], 'La continuacion debe conservar el convenio excepcional original.');
assertTrue(str_contains($asignadoYContinua['mensaje'], 'Asignacion verificada'), 'Debe confirmar que la asignacion quedo verificada.');
$convenioAsignado = $convenioSinAsignacion->ejecutar(
    'convenio_crear_excepcion',
    $asignadoYContinua['propuesta_especificacion']['payload'],
    context()
);
assertSameValue('agente_ejecutado', $convenioAsignado['tipo'], 'Debe crear el convenio despues de verificar la asignacion.');
assertSameValue(2, $datosExcepcionAsignada['id_celula'], 'El convenio debe heredar la celula asignada, no la celula del usuario que confirma.');

$sinAsignacionAlConfirmar = newService([
    'convenio_asignacion_actual' => static fn(int $credito): ?array => null,
]);
try {
    $sinAsignacionAlConfirmar->ejecutar(
        'convenio_crear_excepcion',
        ['id_credito' => 1408958, 'fecha_pago' => '2026-08-02', 'monto' => 9000.0],
        context()
    );
    throw new RuntimeException('No debe crear el convenio si la asignacion desaparecio antes de confirmar.');
} catch (RuntimeException $error) {
    assertTrue(str_contains($error->getMessage(), 'no tiene una asignacion activa'), 'Debe bloquear la confirmacion sin asignacion.');
}

$_SESSION = [];
$reactivacion = newService();
$propuestaReactivacion = $reactivacion->resolver(
    'reactiva el convenio cancelado del credito 172307',
    'reactiva el convenio cancelado del credito 172307',
    context()
);
assertSameValue('agente_propuesta', $propuestaReactivacion['tipo'], 'Debe presentar la reactivacion antes de cambiar ambas tablas.');
assertSameValue('convenio_reactivar_cancelado', $propuestaReactivacion['propuesta_especificacion']['accion'], 'Debe usar el ejecutor transaccional.');
assertTrue(str_contains($propuestaReactivacion['mensaje'], 'convenio_cliente_amortizacion'), 'Debe explicar las dos tablas afectadas.');
$reactivado = $reactivacion->ejecutar(
    'convenio_reactivar_cancelado',
    $propuestaReactivacion['propuesta_especificacion']['payload'],
    context()
);
assertSameValue('agente_ejecutado', $reactivado['tipo'], 'Debe reactivar tras la confirmacion.');
assertTrue(str_contains($reactivado['mensaje'], 'Cuotas reabiertas'), 'Debe reportar la verificacion de amortizaciones.');

$_SESSION = [];
$moto = newService();
$moto->resolver('quiero adjudicar una moto', 'quiero adjudicar una moto', context());
$responsables = $moto->resolver('1881', '1881', context());
assertSameValue('agente_opciones', $responsables['tipo'], 'Debe listar responsables activos.');
$motoProposal = $moto->resolver('1', '1', context());
assertSameValue('moto_asignar', $motoProposal['propuesta_especificacion']['accion'], 'Debe preparar la asignacion de moto.');
$motoExecuted = $moto->ejecutar('moto_asignar', $motoProposal['propuesta_especificacion']['payload'], context());
assertSameValue('agente_ejecutado', $motoExecuted['tipo'], 'Debe asignar el credito confirmado.');
assertTrue(str_contains($motoExecuted['mensaje'], 'Tarea vigente: 910661'), 'Debe informar la tarea Legacy verificada.');
assertTrue(str_contains($motoExecuted['mensaje'], 'Usuario Legacy: 75'), 'Debe informar el usuario Legacy verificado.');
assertTrue(str_contains($motoExecuted['mensaje'], 'Campaña vigente: ASIGNACION_W29_1A7'), 'Debe informar la campaña Legacy verificada.');
assertTrue(str_contains($motoExecuted['mensaje'], 'task_user_assignments'), 'Debe confirmar la asignacion local verificada.');

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
                'legacy_user_id' => 75,
                'campaign_name' => 'ASIGNACION_W29_1A7',
                'external_id' => '126',
                'responsable' => 'LETICIA PEREZ CRUZ',
                'client_name' => 'CARLOS NOE CRUZ AGUILAR',
                'responsable_correcto' => true,
                'asignacion_activa' => true,
                'asignacion_exclusiva' => true,
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
$zyanya = newService([
    'moto_buscar' => static fn(int $id): array => [
        'success' => true,
        'credito' => [
            'id_credito' => $id,
            'nombre_cliente' => 'CLIENTE CREDITO 2253820',
            'status_credito' => 'Vencido',
        ],
        'status_credito' => 'Vencido',
        'asignacion' => null,
    ],
    'moto_responsables' => static fn(): array => [[
        'id_persona' => 776,
        'nombre_completo' => 'ZYANYA NAYELLY DIAZ CASTREJON',
        'puesto' => 'Gestor',
        'numero_empleado' => '776',
        'codigo_contpac' => '1842',
    ]],
    'moto_responsable_activo' => static fn(int $id): bool => $id === 776,
    'moto_asignar' => static fn(int $persona, int $credito, int $actor): array => [
        'success' => true,
        'partial' => false,
        'local' => ['success' => true, 'id_credito' => $credito, 'id_persona' => $persona],
        'legacy' => [
            'success' => true,
            'task_id' => 910613,
            'duplicate' => false,
            'verificacion' => [
                'success' => true,
                'task_id' => 910613,
                'legacy_user_id' => 75,
                'campaign_name' => 'ASIGNACION_W29_1A7',
                'external_id' => '776',
                'responsable' => 'ZYANYA NAYELLY DIAZ CASTREJON',
                'client_name' => 'CLIENTE CREDITO 2253820',
                'responsable_correcto' => true,
                'asignacion_activa' => true,
                'asignacion_exclusiva' => true,
                'cliente_correcto' => true,
            ],
        ],
    ],
]);
$solicitudZyanya = "En Motos Adjudicadas, asigna el cr\u{00E9}dito 2253820 a ZYANYA NAYELLY DIAZ CASTREJON.";
$propuestaZyanya = $zyanya->resolver($solicitudZyanya, $solicitudZyanya, context());
assertSameValue('agente_propuesta', $propuestaZyanya['tipo'], 'Debe resolver una asignacion directa usando solo el nombre completo.');
assertSameValue(2253820, $propuestaZyanya['propuesta_especificacion']['payload']['id_credito'], 'Debe conservar el credito solicitado para Zyanya.');
assertSameValue(776, $propuestaZyanya['propuesta_especificacion']['payload']['id_persona'], 'Debe identificar a Zyanya por nombre completo.');
assertTrue(str_contains(mb_strtolower($propuestaZyanya['mensaje'], 'UTF-8'), 'confirm'), 'Debe mostrar una vista previa y pedir confirmacion.');
$zyanyaEjecutada = $zyanya->ejecutar('moto_asignar', $propuestaZyanya['propuesta_especificacion']['payload'], context());
assertSameValue('agente_ejecutado', $zyanyaEjecutada['tipo'], 'Debe ejecutar la asignacion de Zyanya tras la confirmacion.');
assertTrue(str_contains($zyanyaEjecutada['mensaje'], 'Empleado: 776'), 'Debe informar el empleado verificado.');
assertTrue(str_contains($zyanyaEjecutada['mensaje'], 'Usuario Legacy: 75'), 'Debe informar el usuario Legacy verificado.');
assertTrue(str_contains($zyanyaEjecutada['mensaje'], 'Campaña vigente: ASIGNACION_W29_1A7'), 'Debe informar la campaña vigente.');
assertTrue(str_contains($zyanyaEjecutada['mensaje'], 'Tarea vigente: 910613'), 'Debe informar la tarea vigente.');
assertTrue(str_contains($zyanyaEjecutada['mensaje'], 'task_user_assignments'), 'Debe verificar la asignacion activa local.');

$_SESSION = [];
$carlosSolano = newService([
    'moto_buscar' => static fn(int $id): array => [
        'success' => true,
        'credito' => [
            'id_credito' => $id,
            'nombre_cliente' => 'CLIENTE CREDITO 2257556',
            'status_credito' => 'Vencido',
        ],
        'status_credito' => 'Vencido',
        'asignacion' => null,
    ],
    'moto_responsables' => static fn(): array => [[
        'id_persona' => 880,
        'nombre_completo' => 'CARLOS ANDRES SOLANO FERNANDEZ',
        'puesto' => 'Gestor',
        'numero_empleado' => '880',
        'codigo_contpac' => '880',
    ]],
]);
$solicitudCarlosSolano = 'ASIGNA EL ID 2257556 AL GESTOR CARLOS ANDRES SOLANO FERNANDEZ';
$propuestaCarlosSolano = $carlosSolano->resolver($solicitudCarlosSolano, $solicitudCarlosSolano, context());
assertSameValue('agente_propuesta', $propuestaCarlosSolano['tipo'], 'La instruccion natural reportada debe producir una vista previa valida.');
assertSameValue('moto_asignar', $propuestaCarlosSolano['propuesta_especificacion']['accion'], 'Debe enrutar la instruccion reportada a la asignacion de Motos.');
assertSameValue(2257556, $propuestaCarlosSolano['propuesta_especificacion']['payload']['id_credito'], 'Debe conservar el ID reportado.');
assertSameValue(880, $propuestaCarlosSolano['propuesta_especificacion']['payload']['id_persona'], 'Debe resolver al gestor por nombre completo.');

$_SESSION = [];
$solicitudNatural = 'me ayudas a asignar el id 1809373 al gestor JUAN ENRIQUE ROCIO SALAZAR';
$juanEnrique = newService([
    'moto_buscar' => static fn(int $id): array => [
        'success' => true,
        'credito' => [
            'id_credito' => $id,
            'nombre_cliente' => 'CLIENTE CREDITO 1809373',
            'status_credito' => 'Vencido',
        ],
        'status_credito' => 'Vencido',
        'asignacion' => null,
    ],
    'moto_responsables' => static fn(): array => [
        [
            'id_persona' => 1809,
            'nombre_completo' => 'JUAN ENRIQUE ROCIO SALAZAR',
            'puesto' => 'Gestor',
            'numero_empleado' => '1809',
            'codigo_contpac' => '9901',
        ],
        [
            'id_persona' => 1188,
            'nombre_completo' => 'IRAK BLANCO CRUZ',
            'puesto' => 'Gestor',
            'numero_empleado' => '999999748',
            'codigo_contpac' => '641',
        ],
    ],
]);
$propuestaNatural = $juanEnrique->resolver($solicitudNatural, $solicitudNatural, context());
assertSameValue('agente_propuesta', $propuestaNatural['tipo'], 'Debe entender ID y gestor sin exigir la frase Motos Adjudicadas.');
assertSameValue(1809373, $propuestaNatural['propuesta_especificacion']['payload']['id_credito'], 'Debe extraer el ID usado como credito.');
assertSameValue(1809, $propuestaNatural['propuesta_especificacion']['payload']['id_persona'], 'Debe identificar al gestor por nombre completo.');

$_SESSION = [];
$solicitudDelegadaSinEspacio = 'asigna el credito 1809373al gestor JUAN ENRIQUE ROCIO SALAZAR';
$contextoDelegado = context([
    'actor_id' => 1200,
    'nombre_corto' => 'Raymundo',
    'permisos_agente' => ['convenio' => false, 'motos' => true, 'id_celula' => null],
]);
$propuestaDelegada = $juanEnrique->resolver(
    $solicitudDelegadaSinEspacio,
    $solicitudDelegadaSinEspacio,
    $contextoDelegado
);
assertSameValue('agente_propuesta', $propuestaDelegada['tipo'], 'Un usuario delegado debe poder preparar la asignacion aunque falte el espacio antes de "al gestor".');
assertSameValue(1809373, $propuestaDelegada['propuesta_especificacion']['payload']['id_credito'], 'La captura sin espacio no debe alterar el credito.');
assertSameValue(1809, $propuestaDelegada['propuesta_especificacion']['payload']['id_persona'], 'La captura delegada debe conservar al gestor seleccionado.');
assertTrue(!array_key_exists('actor_id', $propuestaDelegada['propuesta_especificacion']['payload']), 'El actor no debe viajar en el payload manipulable; se toma de la sesion al confirmar.');

$_SESSION = [];
$solicitudExactaRaymundo = 'asigna el credito 1809373al gestor Irak Blanco Cruz';
$propuestaExactaRaymundo = $juanEnrique->resolver(
    $solicitudExactaRaymundo,
    $solicitudExactaRaymundo,
    $contextoDelegado
);
assertSameValue('agente_propuesta', $propuestaExactaRaymundo['tipo'], 'La frase exacta de Raymundo debe llegar a la vista previa de asignacion.');
assertSameValue(1809373, $propuestaExactaRaymundo['propuesta_especificacion']['payload']['id_credito'], 'Debe reconocer el credito aun cuando este pegado a "al".');
assertSameValue(1188, $propuestaExactaRaymundo['propuesta_especificacion']['payload']['id_persona'], 'Debe resolver a Irak Blanco Cruz sin confundirlo con otro gestor.');

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
    'credito' => ['id_credito' => $id, 'nombre_cliente' => 'CLIENTE', 'status_credito' => 'Vencido'],
    'status_credito' => 'Vencido',
    'asignacion' => [
        'id' => 77,
        'id_persona' => 99,
        'nombre_despacho' => 'RESPONSABLE EXISTENTE',
    ],
]]);
$assigned->resolver('quiero adjudicar una moto', 'quiero adjudicar una moto', context());
$assignedOptions = $assigned->resolver('1881', '1881', context());
assertSameValue('agente_opciones', $assignedOptions['tipo'], 'Una asignacion existente debe permitir elegir al nuevo responsable.');
$reassignmentProposal = $assigned->resolver('1', '1', context());
assertSameValue('agente_propuesta', $reassignmentProposal['tipo'], 'Debe pedir confirmacion antes de cambiar la asignacion.');
assertTrue(!empty($reassignmentProposal['propuesta_especificacion']['payload']['reasignar']), 'La vista previa debe marcar que es una reasignacion.');
assertSameValue(77, $reassignmentProposal['propuesta_especificacion']['payload']['asignacion_anterior_id'], 'Debe conservar el registro anterior para evitar confirmaciones obsoletas.');
assertSameValue(99, $reassignmentProposal['propuesta_especificacion']['payload']['asignacion_anterior_persona'], 'Debe conservar al responsable anterior.');
assertTrue(str_contains($reassignmentProposal['mensaje'], 'RESPONSABLE EXISTENTE'), 'Debe mostrar a quien se reemplazara.');

$changedAssignment = newService(['moto_buscar' => static fn(int $id): array => [
    'success' => true,
    'credito' => ['id_credito' => $id, 'nombre_cliente' => 'CLIENTE', 'status_credito' => 'Vencido'],
    'status_credito' => 'Vencido',
    'asignacion' => [
        'id' => 88,
        'id_persona' => 100,
        'nombre_despacho' => 'TERCER RESPONSABLE',
    ],
]]);
try {
    $changedAssignment->ejecutar('moto_asignar', $reassignmentProposal['propuesta_especificacion']['payload'], context());
    throw new RuntimeException('Una confirmacion vieja no debe sobrescribir una asignacion posterior.');
} catch (RuntimeException $error) {
    assertTrue(str_contains($error->getMessage(), 'cambió después de la vista previa'), 'Debe detectar que la asignacion cambio despues de la confirmacion mostrada.');
}

$_SESSION = [];
$current = newService(['moto_buscar' => static fn(int $id): array => [
    'success' => true,
    'credito' => ['id_credito' => $id, 'nombre_cliente' => 'CLIENTE', 'status_credito' => 'Activo'],
    'status_credito' => 'Activo',
    'asignacion' => null,
]]);
$current->resolver('quiero adjudicar una moto', 'quiero adjudicar una moto', context());
$activeOptions = $current->resolver('1881', '1881', context());
assertSameValue('agente_opciones', $activeOptions['tipo'], 'Un credito activo debe poder avanzar como en la pantalla oficial.');
$activeProposal = $current->resolver('1', '1', context());
assertSameValue('agente_propuesta', $activeProposal['tipo'], 'Un credito activo debe llegar a vista previa antes de asignarse.');

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
$asignacionSoloConvenio = null;
$consultasOfertaAsignacionSolo = 0;
$responsableAsignacionSolo = [
    'id_persona' => 415,
    'id_celula' => 3,
    'nombre_completo' => 'RESPONSABLE CAMPO PRUEBA',
    'numero_empleado' => '2415',
    'puesto' => 'GESTOR DE CAMPO',
];
$servicioAsignacionSolo = newService([
    'convenio_ofertas' => static function (int $credito) use (&$consultasOfertaAsignacionSolo): array {
        $consultasOfertaAsignacionSolo++;
        return ofertaResult($credito);
    },
    'convenio_asignacion_actual' => static function (int $credito) use (&$asignacionSoloConvenio): ?array {
        return $asignacionSoloConvenio;
    },
    'convenio_responsables_buscar' => static function (int $celula, string $busqueda) use ($responsableAsignacionSolo): array {
        return $celula === 3 && stripos($busqueda, 'campo') !== false ? [$responsableAsignacionSolo] : [];
    },
    'convenio_responsable_actual' => static function (int $persona, int $celula) use ($responsableAsignacionSolo): ?array {
        return $persona === 415 && $celula === 3 ? $responsableAsignacionSolo : null;
    },
    'convenio_asignar_credito' => static function (int $persona, int $credito, int $celula, int $actor) use (&$asignacionSoloConvenio, $responsableAsignacionSolo): array {
        $asignacionSoloConvenio = $responsableAsignacionSolo + ['id' => 991, 'id_credito' => $credito];
        return ['success' => true, 'asignacion' => $asignacionSoloConvenio];
    },
]);
$peticionAsignacionSolo = 'ayudame a asignar a este credito 1551963 en el modulo de convenio, '
    . 'porque se abrio un convenio pero no tenia asignacion el credito';
$destinoAsignacionSolo = $servicioAsignacionSolo->resolver($peticionAsignacionSolo, $peticionAsignacionSolo, context());
assertSameValue('agente_opciones', $destinoAsignacionSolo['tipo'], 'La peticion reportada debe iniciar una asignacion, no diagnosticar ni abrir otro convenio.');
assertTrue(str_contains($destinoAsignacionSolo['mensaje'], '1551963'), 'Debe conservar el credito de la peticion de asignacion independiente.');
assertSameValue(4, count($destinoAsignacionSolo['acciones_rapidas'] ?? []), 'Debe ofrecer Despacho, Call Center, Campo y Cancelar como botones.');
assertSameValue('Cancelar', $destinoAsignacionSolo['acciones_rapidas'][3]['etiqueta'] ?? '', 'La ultima opcion rapida debe cancelar la gestion.');
assertSameValue(0, $consultasOfertaAsignacionSolo, 'Asignar un credito existente no debe calcular ofertas ni intentar otro convenio.');
// Simula que otro componente elimino la tarea generica entre mensajes. El
// respaldo dedicado debe conservar el flujo y evitar una consulta de plantilla.
unset($_SESSION['leonidas_agent_task']);
$responsableAsignacionSoloPregunta = $servicioAsignacionSolo->resolver('Campo', 'campo', context());
assertSameValue('agente_pregunta', $responsableAsignacionSoloPregunta['tipo'], 'Debe pedir el responsable despues de elegir Campo.');
unset($_SESSION['leonidas_agent_task']);
$coincidenciaAsignacionSolo = $servicioAsignacionSolo->resolver('responsable campo', 'responsable campo', context());
assertSameValue('agente_opciones', $coincidenciaAsignacionSolo['tipo'], 'Una coincidencia debe mostrarse como opcion seleccionable antes de preparar la asignacion.');
assertSameValue('RESPONSABLE CAMPO PRUEBA', $coincidenciaAsignacionSolo['acciones_rapidas'][0]['etiqueta'] ?? '', 'El nombre encontrado debe aparecer como boton.');
assertSameValue('Cancelar', $coincidenciaAsignacionSolo['acciones_rapidas'][1]['etiqueta'] ?? '', 'La seleccion de persona tambien debe permitir cancelar.');
unset($_SESSION['leonidas_agent_task']);
$propuestaAsignacionSolo = $servicioAsignacionSolo->resolver('si', 'si', context());
assertSameValue('agente_propuesta', $propuestaAsignacionSolo['tipo'], 'La asignacion independiente debe mostrar vista previa confirmable.');
assertSameValue('convenio_asignacion', $propuestaAsignacionSolo['propuesta_especificacion']['payload']['tipo_flujo'], 'Debe marcar que solo se asignara, sin continuar a un convenio.');
$resultadoAsignacionSolo = $servicioAsignacionSolo->ejecutar(
    'convenio_asignar_credito',
    $propuestaAsignacionSolo['propuesta_especificacion']['payload'],
    context()
);
assertSameValue('agente_ejecutado', $resultadoAsignacionSolo['tipo'], 'La confirmacion debe terminar al verificar la asignacion independiente.');
assertTrue(str_contains($resultadoAsignacionSolo['mensaje'], 'No se creo ni se modifico ningun convenio'), 'Debe aclarar que no abrio otro convenio.');
assertTrue(!isset($resultadoAsignacionSolo['propuesta_especificacion']), 'No debe encadenar una propuesta para crear convenio.');
assertSameValue(0, $consultasOfertaAsignacionSolo, 'La ejecucion de asignacion tampoco debe consultar ofertas.');

$_SESSION = [];
$negacionAbrirConvenio = newService([
    'convenio_asignacion_actual' => static fn(int $credito): ?array => null,
]);
$negacionAbrirConvenio->resolver('quiero abrir otro convenio', 'quiero abrir otro convenio', context());
$preguntaCreditoAsignar = $negacionAbrirConvenio->resolver(
    'no quiero abrir otro convenio solo quiero asginarlo',
    'no quiero abrir otro convenio solo quiero asginarlo',
    context()
);
assertSameValue('agente_pregunta', $preguntaCreditoAsignar['tipo'], 'La negacion de abrir debe reemplazar incluso una tarea anterior y pedir el credito para asignarlo.');
assertTrue(str_contains($preguntaCreditoAsignar['mensaje'], 'no abrire otro convenio'), 'Debe confirmar que entendio la negacion del usuario.');
$destinoTrasCredito = $negacionAbrirConvenio->resolver('1551963', '1551963', context());
assertSameValue('agente_opciones', $destinoTrasCredito['tipo'], 'Al recibir el credito debe preguntar Despacho, Call Center o Campo.');

$_SESSION = [];
$cancelarAsignacionConvenio = newService([
    'convenio_asignacion_actual' => static fn(int $credito): ?array => null,
]);
$cancelarAsignacionConvenio->resolver($peticionAsignacionSolo, $peticionAsignacionSolo, context());
$asignacionCancelada = $cancelarAsignacionConvenio->resolver('Cancelar', 'cancelar', context());
assertSameValue('agente_cancelado', $asignacionCancelada['tipo'], 'El boton Cancelar debe terminar la gestion sin cambios.');
assertTrue(str_contains($asignacionCancelada['mensaje'], 'No se modifico ningun dato'), 'La cancelacion debe confirmar que no hubo cambios.');

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
