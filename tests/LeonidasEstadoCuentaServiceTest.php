<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasEstadoCuentaService.php';

use Services\LeonidasEstadoCuentaService;

function assertEstadoCuenta(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$s2 = static fn(int $credito): array => [
    'fuente' => 's2_estado_cuenta',
    'metricas' => [
        'id_credito' => $credito,
        'adeudo_total' => 12500.75,
        'domiciliado' => 'SI',
        'cuenta_concentradora' => '1234567890',
        'clabe_concentradora' => '012345678901234567',
        'banco_concentrador' => 'BANCO PRUEBA',
        'referencia_stp' => 'REF-' . $credito,
    ],
];

$gastos = static fn(int $credito): array => [
    'success' => true,
    'data' => [
        ['monto_pendiente_real' => 125.50],
        ['monto_original' => 100, 'condonacion_parcial_monto' => 25, 'monto_parcial_pagado' => 10],
    ],
];

$gestion = static fn(int $credito): array => [
    'activa' => true,
    'celula' => 3,
    'etiqueta_celula' => 'Cobranza Campo',
];

$ultimoPago = static fn(int $credito): array => ['Fecha_ultimo_pago_efectivo' => '2026-07-22'];

$motivos = static fn(): array => [
    ['id' => 1, 'motivo' => 'Campana Call Center'],
    ['id' => 2, 'motivo' => 'Credito liquidado'],
    ['id' => 3, 'motivo' => 'Convenios'],
];

$bucket = static fn(int $credito, array $contexto): array => [
    'mensaje' => "El credito {$credito} esta en 8-30 al corte operativo consultado.",
    'tipo' => 'consulta_analitica',
    'tema' => 'diagnostico_bucket',
    'fuente' => 'bucket_prueba',
];

$service = new LeonidasEstadoCuentaService($s2, $gastos, $gestion, $ultimoPago, $motivos, $bucket);
$contexto = [
    'permisos_agente' => [
        'estado_cuenta' => true,
        'aclaraciones_credito' => true,
        'gastos_cobranza' => true,
        'analitica' => true,
        'bucket' => true,
    ],
];

$casos = [
    'cargos_moratorios' => '¿Por qué no puedo aplicar cargos moratorios en el crédito 1600?',
    'tiempo_aclaracion' => '¿Cuánto tiempo tarda en verse impactado un pago después de una aclaración del crédito 1600?',
    'motivos_condonacion' => '¿Cuáles son los motivos por los cuales podría condonar cargos de cobranza?',
    'domiciliado' => '¿El crédito 1600 es domiciliado?',
    'adeudo' => '¿Cuál es el saldo total adeudado por el cliente en el crédito 1600?',
    'cuenta_concentradora' => 'Si el cliente no tiene banca móvil, ¿cuál es la cuenta concentrada del crédito 1600?',
];

foreach ($casos as $tema => $pregunta) {
    $respuesta = $service->resolver($pregunta, $contexto);
    assertEstadoCuenta(is_array($respuesta), 'No respondio: ' . $pregunta);
    assertEstadoCuenta(($respuesta['tema'] ?? '') === $tema, "Clasifico mal {$tema}.");
    assertEstadoCuenta(($respuesta['tipo'] ?? '') === 'consulta_estado_cuenta', "Tipo incorrecto para {$tema}.");
    assertEstadoCuenta(strlen((string) ($respuesta['mensaje'] ?? '')) > 70, "Respuesta pobre para {$tema}.");
}

$cargos = $service->resolver($casos['cargos_moratorios'], $contexto);
assertEstadoCuenta(str_contains($cargos['mensaje'], 'Cobranza Campo'), 'Debe explicar el bloqueo por gestion externa.');
assertEstadoCuenta(str_contains($cargos['mensaje'], 'cobros duplicados'), 'Debe explicar por que existe el bloqueo.');

$aclaracion = $service->resolver($casos['tiempo_aclaracion'], $contexto);
assertEstadoCuenta(str_contains($aclaracion['mensaje'], 'martes a domingo'), 'Debe explicar la regla calendarizada.');
assertEstadoCuenta(str_contains($aclaracion['mensaje'], '2026-07-22'), 'Debe mostrar el ultimo pago del credito.');

$condonacion = $service->resolver($casos['motivos_condonacion'], $contexto);
assertEstadoCuenta(str_contains($condonacion['mensaje'], 'Campana Call Center'), 'Debe usar el catalogo vigente.');
assertEstadoCuenta(str_contains($condonacion['mensaje'], '25 caracteres'), 'Debe explicar la validacion parcial.');

$domiciliado = $service->resolver($casos['domiciliado'], $contexto);
assertEstadoCuenta(str_contains($domiciliado['mensaje'], 'SI'), 'Debe reportar domiciliacion desde S2.');

$adeudo = $service->resolver($casos['adeudo'], $contexto);
assertEstadoCuenta(str_contains($adeudo['mensaje'], '$12,500.75'), 'Debe reportar el adeudo de S2.');

$cuenta = $service->resolver($casos['cuenta_concentradora'], $contexto);
assertEstadoCuenta(str_contains($cuenta['mensaje'], '012345678901234567'), 'Debe reportar la CLABE de S2.');
assertEstadoCuenta(str_contains($cuenta['mensaje'], 'REF-1600'), 'Debe distinguir la referencia individual.');

$bucketRespuesta = $service->resolver(
    '¿El crédito 1600 está asignado a alguna subárea de cobranza (bucket)?',
    $contexto
);
assertEstadoCuenta(($bucketRespuesta['tema'] ?? '') === 'diagnostico_bucket', 'Debe delegar al diagnostico real de bucket.');
assertEstadoCuenta(str_contains($bucketRespuesta['mensaje'], '8-30'), 'Debe devolver el bucket operativo.');

$sinDato = new LeonidasEstadoCuentaService(
    static fn(int $credito): array => ['metricas' => ['id_credito' => $credito]],
    $gastos,
    $gestion,
    $ultimoPago,
    $motivos,
    $bucket
);
$sinDomiciliacion = $sinDato->resolver('El credito 1600 es domiciliado?', $contexto);
assertEstadoCuenta(
    str_contains($sinDomiciliacion['mensaje'], 'no devolvio el campo'),
    'Sin dato S2 debe reconocer la ausencia y no inventar.'
);

$sinCuenta = $sinDato->resolver(
    'Si no tiene banca movil cual es la cuenta concentrada del credito 1600?',
    $contexto
);
assertEstadoCuenta(str_contains($sinCuenta['mensaje'], 'No voy a inventar'), 'Nunca debe inventar una cuenta bancaria.');

$sinPermiso = $contexto;
$sinPermiso['permisos_agente']['estado_cuenta'] = false;
$denegada = $service->resolver('Cual es el saldo total adeudado del credito 1600?', $sinPermiso);
assertEstadoCuenta(($denegada['tipo'] ?? '') === 'permiso_denegado', 'Debe respetar permisos de lectura.');

$orden = $service->resolver('Condona los cargos de cobranza del credito 1600', $contexto);
assertEstadoCuenta($orden === null, 'Una orden de escritura debe pasar al agente, no ejecutarse como consulta.');

$ajena = $service->resolver('Cuantos candidatos estan en revision?', $contexto);
assertEstadoCuenta($ajena === null, 'No debe interceptar preguntas de otros modulos.');

echo "OK LeonidasEstadoCuentaService\n";
