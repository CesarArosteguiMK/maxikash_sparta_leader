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
$diagnosticoCredito = [
    'success' => true,
    'modo' => 'credito',
    'id_credito' => 1600,
    'encontrado' => true,
    'en_semana_actual' => false,
    'cliente' => 'RAFAEL GALLEGOS MONROY',
    'semana' => 'Semana 13-2026',
    'corte' => '14:30',
    'dia_corte' => 'Lunes',
    'dias_mora_corte' => 4,
    'bucket_real' => '1-7',
    'bucket_segundometro' => '1-7',
    'bucket_historico' => 'Current',
    'bucket_comparativo' => '1-7',
    'bucket_comparativo_conciliado' => '1-7',
    'bucket_nacimiento' => '1-7',
    'bucket_actual' => '1-7',
    'bucket_cierre_ajustado' => 'Current',
    'fecha_hora_fuente' => '2026-07-20 14:30:00',
    'consultado_at' => '2026-07-20 16:00:00',
    'antiguedad_minutos' => 90,
    'vistas' => [
        [
            'vista' => 'Nacimiento',
            'fuente' => 'tbl_segundometro_histo.Bucket_Morosidad_Real',
            'bucket' => '1-7',
            'dias_mora' => 4,
            'corte' => 'Lunes 14:30',
            'fecha_hora_fuente' => '2026-07-20 14:30:00',
            'antiguedad_minutos' => 90,
            'formula' => 'Normaliza el bucket real al catalogo operativo.',
            'filtros' => ['Id_credito', 'SEMANA'],
        ],
        [
            'vista' => 'Segundometro / Avance',
            'fuente' => 'tbl_segundometro_histo',
            'bucket' => '1-7',
            'dias_mora' => 4,
            'corte' => 'Lunes 14:30',
            'fecha_hora_fuente' => '2026-07-20 14:30:00',
            'antiguedad_minutos' => 90,
            'formula' => 'Compara nacimiento contra mora del corte y conserva el menos moroso.',
            'filtros' => ['Id_credito', 'SEMANA', 'Dias_mora_Lunes_14_30'],
        ],
        [
            'vista' => 'Historico / cierre ajustado',
            'fuente' => 'tbl_segundometro_histo.Bucket_ajustado_ghost/Cierre_Actual',
            'bucket' => 'Current',
            'dias_mora' => 4,
            'corte' => 'Lunes 14:30',
            'fecha_hora_fuente' => '2026-07-20 14:30:00',
            'antiguedad_minutos' => 90,
            'formula' => 'Usa cierre ajustado y aplica reglas Ghost.',
            'filtros' => ['Id_credito', 'SEMANA', 'Cierre_Actual', 'Ghost'],
        ],
    ],
    's2' => [
        'estado' => 'disponible',
        'fuente' => 's2_estado_cuenta',
        'consultado_at' => '2026-07-20 16:00:00',
        'metricas' => [
            'mora' => 2,
            'saldo' => 8500,
            'saldo_vencido' => 500,
            'estatus' => 'ACTIVO',
            'ultimo_pago_fecha' => '2026-07-20',
            'ultimo_pago_monto' => 750,
            'pagos_registrados' => 3,
        ],
        'pagos' => [['fecha' => '2026-07-20 15:10:00', 'monto' => 750]],
    ],
    'condonaciones' => [
        'estado' => 'disponible',
        'total' => 1,
        'movimientos' => [['created_at' => '2026-07-19 12:00:00', 'total_condonado' => 100]],
    ],
    'convenios' => ['estado' => 'sin_movimientos', 'total' => 0, 'movimientos' => []],
    'movimientos' => [
        ['fecha' => '2026-07-20 15:10:00', 'titulo' => 'Pago aplicado en S2', 'detalle' => '$750.00', 'fuente' => 'S2'],
        ['fecha' => '2026-07-19 12:00:00', 'titulo' => 'Condonacion registrada', 'detalle' => '$100.00', 'fuente' => 'Sparta / Segundometro'],
    ],
    'fuentes_estado' => [
        'bucket_sparta' => 'disponible',
        's2' => 'disponible',
        'condonaciones' => 'disponible',
        'convenios_reestructuras' => 'sin_movimientos',
    ],
    'conclusion' => [
        'nivel' => 'inferencia_fuerte',
        'texto' => 'S2 registra un pago posterior a la fotografia de Bucket; el desfase de corte explica la mejora posterior.',
    ],
    'razones' => ['Historico usa el cierre semanal consolidado; Comparativo recalcula el corte intradia seleccionado.'],
];
$diagnosticoSemana = [
    'success' => true,
    'modo' => 'semana',
    'semana' => 'Semana 28-2026',
    'corte' => '14:30',
    'dia_corte' => 'Lunes',
    'historico' => [
        'total' => 100,
        'total_comparable' => 90,
        'total_121' => 10,
        'buckets' => ['a) Current' => 40, 'b) 1 a 7 dias' => 25, 'i) 121+ dias' => 10],
    ],
    'comparativo' => ['total_visible' => 94],
    'diferencia_total_pantallas' => -6,
    'diferencia_total_comparable' => 4,
    'bucket_solicitado' => [
        'solicitado' => '1-7',
        'historico' => 25,
        'comparativo' => 29,
        'diferencia' => 4,
        'detalle_cuadra' => true,
        'diferencia_no_explicada' => 0,
    ],
    'transiciones' => [
        ['historico' => 'Current', 'comparativo' => '1-7', 'creditos' => 5],
        ['historico' => '1-7', 'comparativo' => 'Current', 'creditos' => 1],
    ],
    'creditos_diferencia' => [
        [
            'id_credito' => 2001,
            'cliente' => 'CLIENTE UNO',
            'movimiento' => 'entra',
            'bucket_nacimiento' => 'Current',
            'bucket_historico' => 'Current',
            'bucket_comparativo' => '1-7',
            'bucket_por_mora' => '1-7',
            'dias_mora_corte' => 2,
            'cierre_actual' => 'VENCIDO',
            'bucket_ajustado_ghost' => '1-7',
            'variable_8' => 'CONCILIACION_W28',
            'ghost' => 'Sin dato',
            'fecha_hora_fuente' => '2026-07-13 14:30:00',
            'motivo' => 'Comparativo usa 2 dias de mora al corte.',
            'formula_historico' => 'cierre consolidado semanal => Current',
            'formula_comparativo' => '2 dias de mora al corte => 1-7',
        ],
        ['id_credito' => 2002, 'cliente' => 'CLIENTE DOS', 'movimiento' => 'entra', 'bucket_historico' => 'Current', 'bucket_comparativo' => '1-7', 'dias_mora_corte' => 3, 'fecha_hora_fuente' => '2026-07-13 14:30:00', 'motivo' => 'Comparativo usa 3 dias de mora al corte.'],
        ['id_credito' => 2003, 'cliente' => 'CLIENTE TRES', 'movimiento' => 'entra', 'bucket_historico' => 'Current', 'bucket_comparativo' => '1-7', 'dias_mora_corte' => 4, 'fecha_hora_fuente' => '2026-07-13 14:30:00', 'motivo' => 'Comparativo usa 4 dias de mora al corte.'],
        ['id_credito' => 2004, 'cliente' => 'CLIENTE CUATRO', 'movimiento' => 'entra', 'bucket_historico' => 'Current', 'bucket_comparativo' => '1-7', 'dias_mora_corte' => 5, 'fecha_hora_fuente' => '2026-07-13 14:30:00', 'motivo' => 'Comparativo usa 5 dias de mora al corte.'],
        ['id_credito' => 2005, 'cliente' => 'CLIENTE CINCO', 'movimiento' => 'entra', 'bucket_historico' => 'Current', 'bucket_comparativo' => '1-7', 'dias_mora_corte' => 6, 'fecha_hora_fuente' => '2026-07-13 14:30:00', 'motivo' => 'Comparativo usa 6 dias de mora al corte.'],
        ['id_credito' => 2006, 'cliente' => 'CLIENTE SEIS', 'movimiento' => 'sale', 'bucket_historico' => '1-7', 'bucket_comparativo' => 'Current', 'dias_mora_corte' => 0, 'fecha_hora_fuente' => '2026-07-13 14:30:00', 'motivo' => 'Un pago dejo el credito en Current al corte.'],
    ],
    'resumen_creditos_diferencia' => ['afectados' => 6, 'entran' => 5, 'salen' => 1, 'reclasificados' => 0, 'neto' => 4],
    'detalle_creditos_truncado' => false,
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
    'bucket_diagnostico' => static function (array $criterios) use ($diagnosticoCredito, $diagnosticoSemana, &$calls): array {
        $calls[] = ['diagnostico', $criterios];
        return isset($criterios['id_credito']) ? $diagnosticoCredito : $diagnosticoSemana;
    },
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

$diagnosticoCreditoResultado = $service->resolver(
    'por que el credito 1600 aparece en 1-7 en segundometro pero en 8-30 en otra pantalla',
    'por que el credito 1600 aparece en 1-7 en segundometro pero en 8-30 en otra pantalla',
    analyticContext()
);
analyticAssert(($diagnosticoCreditoResultado['tipo'] ?? '') === 'analitica_bucket_diagnostico', 'Debe diagnosticar un credito entre pantallas.');
analyticAssert(($diagnosticoCreditoResultado['metricas']['id_credito'] ?? null) === 1600, 'Debe conservar el credito diagnosticado.');
analyticAssert(str_contains((string) ($diagnosticoCreditoResultado['mensaje'] ?? ''), '8-30 no es un bucket nativo'), 'Debe explicar que 8-30 es una agrupacion.');
analyticAssert(str_contains((string) ($diagnosticoCreditoResultado['mensaje'] ?? ''), 'No aparece en la semana actual'), 'Debe distinguir evidencia historica de la semana actual.');
analyticAssert(str_contains((string) ($diagnosticoCreditoResultado['mensaje'] ?? ''), 'Bucket de nacimiento'), 'Debe explicar la clasificacion de nacimiento.');
analyticAssert(str_contains((string) ($diagnosticoCreditoResultado['mensaje'] ?? ''), 'Formula exacta'), 'Debe exponer la formula usada por cada vista.');
analyticAssert(str_contains((string) ($diagnosticoCreditoResultado['mensaje'] ?? ''), 'Pago aplicado en S2'), 'Debe incluir pagos y movimientos cronologicos.');
analyticAssert(str_contains((string) ($diagnosticoCreditoResultado['mensaje'] ?? ''), 'Condonaciones: 1 movimiento'), 'Debe informar las condonaciones encontradas.');
analyticAssert(str_contains((string) ($diagnosticoCreditoResultado['mensaje'] ?? ''), 'Fuentes y vigencia'), 'Debe identificar fuentes y antiguedad de los datos.');
analyticAssert(($diagnosticoCreditoResultado['metricas']['nivel_conclusion'] ?? null) === 'inferencia_fuerte', 'Debe conservar el nivel de certeza de la conclusion.');
$ultimaLlamada = end($calls);
analyticAssert(($ultimaLlamada[1]['id_credito'] ?? null) === 1600, 'Debe enviar el id del credito al conciliador.');
analyticAssert(($ultimaLlamada[1]['bucket'] ?? null) === '8-30', 'Debe reconocer el rango compuesto 8-30.');

$diagnosticoOrdenDirecta = $service->resolver(
    'diagnostica el bucket del credito 1600',
    'diagnostica el bucket del credito 1600',
    analyticContext()
);
analyticAssert(($diagnosticoOrdenDirecta['tipo'] ?? '') === 'analitica_bucket_diagnostico', 'Debe reconocer una orden directa de diagnostico sin exigir la frase por que.');

$diagnosticoSemanaResultado = $service->resolver(
    'por que en la semana 28 el historico bucket 1-7 da otra cantidad que el comparativo semana actual vs semana pasada',
    'por que en la semana 28 el historico bucket 1-7 da otra cantidad que el comparativo semana actual vs semana pasada',
    analyticContext()
);
analyticAssert(($diagnosticoSemanaResultado['tipo'] ?? '') === 'analitica_bucket_diagnostico', 'Debe conciliar Historico y Comparativo por semana.');
analyticAssert(str_contains((string) ($diagnosticoSemanaResultado['mensaje'] ?? ''), '121+'), 'Debe explicar la diferencia de universo visible.');
analyticAssert(str_contains((string) ($diagnosticoSemanaResultado['mensaje'] ?? ''), 'En 1-7'), 'Debe responder el bucket solicitado con cifras.');
analyticAssert(($diagnosticoSemanaResultado['metricas']['diferencia_comparable'] ?? null) === 4, 'Debe conservar la diferencia del universo comparable.');
analyticAssert(str_contains((string) ($diagnosticoSemanaResultado['mensaje'] ?? ''), '5 credito(s) que entran y 1 que salen'), 'Debe explicar entradas y salidas que forman la diferencia neta.');
analyticAssert(str_contains((string) ($diagnosticoSemanaResultado['mensaje'] ?? ''), 'Credito 2,001 - CLIENTE UNO'), 'Debe identificar cada credito concreto de la diferencia.');
analyticAssert(($diagnosticoSemanaResultado['metricas']['creditos_afectados'] ?? null) === 6, 'Debe contar todos los creditos afectados, no solo el neto.');
analyticAssert(($diagnosticoSemanaResultado['metricas']['diferencia_neta_detalle'] ?? null) === 4, 'Debe reconciliar entradas menos salidas con el neto informado.');
analyticAssert(($diagnosticoSemanaResultado['metricas']['detalle_cuadra'] ?? null) === true, 'Debe certificar que el detalle individual cuadra con la diferencia.');
analyticAssert(($diagnosticoSemanaResultado['metricas']['diferencia_no_explicada'] ?? null) === 0, 'No debe dejar creditos sin reconciliar cuando el detalle cuadra.');
$filaCredito = null;
foreach ((array) ($diagnosticoSemanaResultado['reporte']['filas'] ?? []) as $fila) {
    if (str_starts_with((string) ($fila['nombre'] ?? ''), 'Credito 2,001')) {
        $filaCredito = $fila;
        break;
    }
}
analyticAssert(is_array($filaCredito), 'El reporte debe conservar una fila por cada credito que explica la diferencia.');
analyticAssert(($filaCredito['bucket_nacimiento'] ?? null) === 'Current', 'Debe conservar el bucket de nacimiento del credito.');
analyticAssert(($filaCredito['variable_8'] ?? null) === 'CONCILIACION_W28', 'Debe conservar la evidencia Variable_8.');
analyticAssert(($filaCredito['formula_historico'] ?? null) === 'cierre consolidado semanal => Current', 'Debe conservar la formula historica exacta.');
analyticAssert(($filaCredito['formula_comparativo'] ?? null) === '2 dias de mora al corte => 1-7', 'Debe conservar la formula comparativa exacta.');
$ultimaLlamada = end($calls);
analyticAssert(($ultimaLlamada[1]['semana'] ?? null) === '28', 'Debe resolver la semana sin inventar el anio.');
analyticAssert(($ultimaLlamada[1]['bucket'] ?? null) === '1-7', 'Debe reconocer el bucket 1-7.');

$denied = $service->resolver('avance de bucket', 'avance de bucket', analyticContext([
    'permisos_agente' => ['analitica' => true, 'bucket' => false, 'comparativas' => false],
]));
analyticAssert(($denied['tipo'] ?? '') === 'analitica_denegada', 'Debe responder una denegacion clara, no lanzar HTTP 500.');

echo "LeonidasAnaliticaServiceTest OK\n";
