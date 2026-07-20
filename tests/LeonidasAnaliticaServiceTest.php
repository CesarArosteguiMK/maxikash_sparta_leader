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
    return $overrides + ['permisos_agente' => ['analitica' => true, 'bucket' => true, 'comparativas' => true]];
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

$calls = [];
$service = new LeonidasAnaliticaService([
    'bucket_actual' => static function (?string $corte) use ($avance, &$calls): array { $calls[] = ['actual', $corte]; return $avance; },
    'bucket_historico' => static function (?string $semana, ?string $corte) use ($avance, &$calls): array { $calls[] = ['historico', $semana, $corte]; return $avance + ['modo' => 'historico']; },
    'bucket_estresado' => static function (?string $corte) use ($avance, &$calls): array { $calls[] = ['estresado', $corte]; return $avance + ['modo' => 'estresado']; },
    'bucket_comparativo' => static function (?string $corte, ?string $modo) use ($comparativo, &$calls): array { $calls[] = ['comparativo', $corte, $modo]; return $comparativo; },
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

$denied = $service->resolver('avance de bucket', 'avance de bucket', analyticContext([
    'permisos_agente' => ['analitica' => true, 'bucket' => false, 'comparativas' => false],
]));
analyticAssert(($denied['tipo'] ?? '') === 'analitica_denegada', 'Debe responder una denegacion clara, no lanzar HTTP 500.');

echo "LeonidasAnaliticaServiceTest OK\n";
