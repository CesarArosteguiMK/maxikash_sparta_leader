<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasAnaliticaService.php';

use Services\LeonidasAnaliticaService;

function analyticAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function analyticContext(array $overrides = []): array
{
    return $overrides + ['permisos_agente' => [
        'analitica' => true,
        'bucket' => true,
        'comparativas' => true,
        'segundometro' => true,
        'primeros_pagos' => true,
        'gastos_cobranza' => true,
    ]];
}

$avance = [
    'success' => true,
    'modo' => 'actual',
    'corte' => '14:30',
    'total' => 15,
    'resumen_cierre' => [
        ['bucket' => 'a) Current', 'valor' => 10, 'porcentaje' => 66.67],
        ['bucket' => 'b) 1 a 7 dias', 'valor' => 5, 'porcentaje' => 33.33],
    ],
    'indicadores' => ['mejoran' => 4, 'igual' => 8, 'empeoran' => 3],
];
$comparativo = [
    'success' => true,
    'corte' => '14:30',
    'semana_actual' => 'Semana 29-2026',
    'semana_pasada' => 'Semana 28-2026',
    'advertencias' => [],
    'creditos' => [
        'semana_actual' => ['total' => 15, 'filas' => [['bucket' => 'a) Current', 'valor' => 10]]],
        'semana_pasada' => ['total' => 12, 'filas' => [['bucket' => 'a) Current', 'valor' => 8]]],
    ],
];
$segundometro = [
    'fecha_referencia' => '2026-07-20',
    'datos' => [
        ['hora' => '07_30', 'creditos_actual' => 8, 'cobrado_actual' => 1200.50],
        ['hora' => '14_30', 'creditos_actual' => 14, 'cobrado_actual' => 4200.75],
    ],
];
$primerosPagos = [
    'success' => true,
    'datos' => ['semanas' => [[
        'semana' => 'Semana 28-2026',
        'disponible' => true,
        'total' => 20,
        'corte' => ['current_al_corte' => 15, 'pendientes_primeros_pagos' => 5],
    ]]],
];
$gastos = [
    'success' => true,
    'datos' => [
        'periodo_label' => 'Mes actual',
        'kpis' => [
            'total_generado' => ['monto' => 10000, 'count' => 10, 'pct' => 100],
            'recuperado' => ['monto' => 6000, 'count' => 6, 'pct' => 60],
            'pendiente' => ['monto' => 3000, 'count' => 3, 'pct' => 30],
            'condonado' => ['monto' => 1000, 'count' => 1, 'pct' => 10],
        ],
    ],
];

$calls = [];
$service = new LeonidasAnaliticaService([
    'bucket_actual' => static function (?string $corte) use ($avance, &$calls): array { $calls[] = ['actual', $corte]; return $avance; },
    'bucket_historico' => static function (?string $semana, ?string $corte) use ($avance, &$calls): array { $calls[] = ['historico', $semana, $corte]; return $avance + ['modo' => 'historico']; },
    'bucket_estresado' => static function (?string $corte) use ($avance, &$calls): array { $calls[] = ['estresado', $corte]; return $avance + ['modo' => 'estresado']; },
    'bucket_comparativo' => static function (?string $corte, ?string $modo) use ($comparativo, &$calls): array { $calls[] = ['comparativo', $corte, $modo]; return $comparativo; },
    'segundometro' => static function (?string $fecha) use ($segundometro, &$calls): array { $calls[] = ['segundometro', $fecha]; return $segundometro; },
    'primeros_pagos' => static function (int $semanas) use ($primerosPagos, &$calls): array { $calls[] = ['primeros_pagos', $semanas]; return $primerosPagos; },
    'gastos_cobranza' => static function (string $periodo, string $grupo, ?string $inicio, ?string $fin) use ($gastos, &$calls): array { $calls[] = ['gastos', $periodo, $grupo, $inicio, $fin]; return $gastos; },
]);

analyticAssert($service->resolver('hola', 'hola', analyticContext()) === null, 'No debe interceptar conversaciones ajenas a Analitica.');
$concepto = $service->resolver('explicame los buckets', 'explicame los buckets', analyticContext());
analyticAssert(($concepto['tipo'] ?? '') === 'analitica_explicacion', 'Debe explicar Bucket sin consultar datos.');

$actual = $service->resolver('dame el avance de bucket a las 14:30', 'dame el avance de bucket a las 14:30', analyticContext());
analyticAssert(($actual['tipo'] ?? '') === 'analitica_bucket', 'Debe calcular el avance actual.');
analyticAssert(($actual['reporte']['total'] ?? null) === 15, 'Debe preservar el total verificado.');
analyticAssert(($actual['grafica']['series'][0]['valor'] ?? null) === 10, 'Debe generar una grafica basada en datos reales.');
analyticAssert($calls[0] === ['actual', '14:30'], 'Debe enviar el corte solicitado al modelo.');

$historico = $service->resolver('bucket historico semana 27-2026 a las 09:30', 'bucket historico semana 27-2026 a las 09:30', analyticContext());
analyticAssert(($historico['tipo'] ?? '') === 'analitica_bucket', 'Debe consultar historico.');
analyticAssert($calls[1] === ['historico', 'Semana 27-2026', '09:30'], 'Debe conservar semana y corte historicos.');

$estresado = $service->resolver('bucket estresado +1', 'bucket estresado +1', analyticContext());
analyticAssert(($estresado['tipo'] ?? '') === 'analitica_bucket', 'Debe consultar escenario estresado.');

$comp = $service->resolver('comparativa de bucket contra semana pasada', 'comparativa de bucket contra semana pasada', analyticContext());
analyticAssert(($comp['tipo'] ?? '') === 'analitica_bucket_comparativa', 'Debe generar la comparativa semanal.');
analyticAssert(($comp['metricas']['total_anterior'] ?? null) === 12, 'Debe conservar el total anterior.');

$seg = $service->resolver('segundometro de hoy a las 14:30', 'segundometro de hoy a las 14:30', analyticContext());
analyticAssert(($seg['tipo'] ?? '') === 'analitica_segundometro', 'Debe consultar Segundometro.');
analyticAssert(($seg['metricas']['total'] ?? null) === 14, 'Debe seleccionar el corte solicitado del Segundometro.');
analyticAssert(($seg['metricas']['corte'] ?? null) === '14:30', 'Debe presentar la hora legible.');

$pp = $service->resolver('primeros pagos de las ultimas 3 semanas', 'primeros pagos de las ultimas 3 semanas', analyticContext());
analyticAssert(($pp['tipo'] ?? '') === 'analitica_primeros_pagos', 'Debe consultar Primeros Pagos.');
analyticAssert(($pp['metricas']['pendientes'] ?? null) === 5, 'Debe conservar pendientes de Primeros Pagos.');

$gc = $service->resolver('gastos de cobranza de este mes', 'gastos de cobranza de este mes', analyticContext());
analyticAssert(($gc['tipo'] ?? '') === 'analitica_gastos_cobranza', 'Debe consultar Gastos de Cobranza.');
analyticAssert(($gc['reporte']['total'] ?? null) === 10, 'Debe conservar el total de cargos verificado.');
analyticAssert(($gc['grafica']['series'][1]['valor'] ?? null) === 6000.0, 'Debe graficar el monto recuperado real.');

$denied = $service->resolver('avance de bucket', 'avance de bucket', analyticContext([
    'permisos_agente' => ['analitica' => true, 'bucket' => false, 'comparativas' => false],
]));
analyticAssert(($denied['tipo'] ?? '') === 'analitica_denegada', 'Debe responder una denegacion clara, no lanzar HTTP 500.');

echo "LeonidasAnaliticaServiceTest OK\n";
