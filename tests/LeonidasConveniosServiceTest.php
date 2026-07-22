<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasConveniosService.php';

use Services\LeonidasConveniosService;

function assertConvenio(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function servicioConvenioConDatos(array $ofertas, ?array $convenio = null, array $historial = []): LeonidasConveniosService
{
    return new LeonidasConveniosService(
        static fn(int $credito): array => $ofertas,
        static fn(int $credito): array => ['success' => true, 'datos' => $convenio],
        static fn(int $credito): array => ['success' => true, 'datos' => $historial]
    );
}

$service = new LeonidasConveniosService();

$casos = [
    'plazo_maximo' => 'A cuanto plazo en semanas maximo se puede cargar un convenio?',
    'reactivacion' => 'Se puede reactivar un convenio incumplido?',
    'causas_cancelacion' => 'Que considera el sistema para cancelar el convenio?',
    'tiempo_cancelacion' => 'Que tiempo considera el sistema para cancelar el convenio?',
    'pago_pendiente_conciliar' => 'Que es en la parte del pago Pendiente de conciliar?',
    'modificacion' => 'Como modifico un convenio?',
    'elegibilidad' => 'Que requiere un credito para obtener un convenio?',
];

foreach ($casos as $tema => $pregunta) {
    $respuesta = $service->resolver($pregunta);
    assertConvenio(is_array($respuesta), 'No respondio la pregunta: ' . $pregunta);
    assertConvenio(($respuesta['tema'] ?? '') === $tema, 'Clasifico mal el tema ' . $tema . '.');
    assertConvenio(($respuesta['tipo'] ?? '') === 'consulta_convenios', 'El tipo debe ser consulta_convenios.');
    assertConvenio(strlen((string) ($respuesta['mensaje'] ?? '')) > 120, 'La respuesta de ' . $tema . ' es demasiado pobre.');
    assertConvenio(($respuesta['fuente'] ?? '') === 'reglas_operativas_convenios', 'Debe declarar la fuente operativa.');
}

$plazo = $service->resolver($casos['plazo_maximo']);
assertConvenio(str_contains($plazo['mensaje'], 'No existe un maximo unico'), 'No debe inventar un plazo global.');
assertConvenio(str_contains($plazo['mensaje'], 'semanas_max'), 'Debe explicar de donde sale el maximo.');

$reactivacion = $service->resolver($casos['reactivacion']);
assertConvenio(str_contains($reactivacion['mensaje'], 'oferta'), 'Debe distinguir oferta y convenio.');
assertConvenio(str_contains($reactivacion['mensaje'], 'convenio nuevo'), 'Debe explicar que se crea un convenio nuevo.');

$cancelacion = $service->resolver($casos['tiempo_cancelacion']);
assertConvenio(str_contains($cancelacion['mensaje'], 'mas de 3 dias naturales'), 'Debe explicar el plazo exacto de cancelacion automatica.');
assertConvenio(str_contains($cancelacion['mensaje'], 'S2'), 'Debe explicar la verificacion en S2.');

$conciliacion = $service->resolver($casos['pago_pendiente_conciliar']);
assertConvenio(str_contains($conciliacion['mensaje'], 'no esta confirmado como pagado'), 'Debe explicar el estado real del comprobante.');
assertConvenio(str_contains($conciliacion['mensaje'], '3 dias antes'), 'Debe explicar la ventana de conciliacion con S2.');

$creditoBase = [
    'Nombre_cliente' => 'CLIENTE PRUEBA',
    'Bucket_Morosidad_Real' => 'd) 15 a 21 dias',
    'Dias_mora' => 16,
    'Avance_Pago_Plazo' => '12/52',
];

$elegible = servicioConvenioConDatos([
    'success' => true,
    'datos' => [
        'credito' => $creditoBase,
        'ofertas' => [[
            'nombre' => 'Convenio Base',
            'semanas_max' => 12,
            'total_a_pagar' => 8000,
            'porcentaje_descuento' => 20,
        ]],
        'ofertas_reactivables' => [],
        'elegible' => true,
    ],
]);
$respuestaElegible = $elegible->resolver('Puede el credito 1600 obtener convenio y a cuantas semanas?');
assertConvenio(($respuestaElegible['tema'] ?? '') === 'credito_elegible', 'Debe detectar un credito elegible.');
assertConvenio(($respuestaElegible['fuente'] ?? '') === 'modelo_convenios_tiempo_real', 'El diagnostico debe usar el modelo real.');
assertConvenio(str_contains($respuestaElegible['mensaje'], 'hasta 12 semanas'), 'Debe indicar el plazo real de la oferta.');
assertConvenio(str_contains($respuestaElegible['mensaje'], 'Bucket: d) 15 a 21 dias'), 'Debe explicar el bucket usado.');

$activo = servicioConvenioConDatos([
    'success' => true,
    'datos' => ['credito' => $creditoBase, 'ofertas' => []],
], [
    'estatus' => 'activo',
    'nombre_producto' => 'Convenio Especial',
    'numero_semanas' => 10,
    'nombre_cliente' => 'CLIENTE PRUEBA',
]);
$respuestaActiva = $activo->resolver('El credito 1600 puede tener otro convenio?');
assertConvenio(($respuestaActiva['tema'] ?? '') === 'credito_con_convenio', 'Debe detectar el convenio activo.');
assertConvenio(str_contains($respuestaActiva['mensaje'], 'no puede generarse otro convenio'), 'Debe explicar el bloqueo por convenio activo.');

$reactivable = servicioConvenioConDatos([
    'success' => true,
    'datos' => [
        'credito' => $creditoBase,
        'ofertas' => [],
        'ofertas_reactivables' => [['nombre' => 'Convenio Base']],
        'elegible' => false,
    ],
], [
    'estatus' => 'cancelado',
    'nombre_producto' => 'Convenio Base',
    'nombre_cliente' => 'CLIENTE PRUEBA',
]);
$respuestaReactivable = $reactivable->resolver('Por que el credito 1600 no tiene oferta de convenio?');
assertConvenio(($respuestaReactivable['tema'] ?? '') === 'credito_no_elegible', 'Debe diagnosticar el credito bloqueado.');
assertConvenio(str_contains($respuestaReactivable['mensaje'], 'reactivacion de la oferta'), 'Debe recomendar el flujo correcto de reactivacion.');

$fueraBucket = servicioConvenioConDatos([
    'success' => true,
    'datos' => [
        'credito' => array_merge($creditoBase, ['Bucket_Morosidad_Real' => 'b) 1 a 7 dias', 'Dias_mora' => 4]),
        'ofertas' => [],
        'ofertas_reactivables' => [],
        'elegible' => false,
    ],
]);
$respuestaFuera = $fueraBucket->resolver('Por que el credito 1600 no puede tener convenio?');
assertConvenio(str_contains($respuestaFuera['mensaje'], 'fuera del rango general'), 'Debe explicar el rechazo por bucket.');

$inexistente = servicioConvenioConDatos(['success' => false, 'mensaje' => 'Credito no encontrado.']);
$respuestaInexistente = $inexistente->resolver('El credito 999999 tiene convenio?');
assertConvenio(($respuestaInexistente['tema'] ?? '') === 'credito_no_encontrado', 'Debe distinguir un credito inexistente.');

$accion = $service->resolver('Cancela el convenio 1600');
assertConvenio($accion === null, 'Una orden de accion debe continuar al ejecutor del agente.');

$crear = $service->resolver('Quiero crear un convenio para el credito 1600');
assertConvenio($crear === null, 'Una solicitud de creacion debe continuar al ejecutor del agente.');

$fuera = $service->resolver('Cuantos candidatos hay en revision?');
assertConvenio($fuera === null, 'No debe capturar consultas de otros modulos.');

echo "LeonidasConveniosService: OK\n";
