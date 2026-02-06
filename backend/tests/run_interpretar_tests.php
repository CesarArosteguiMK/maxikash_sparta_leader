<?php

/**
 * Ejecuta los casos de AnaliticaInterpretarService sin PHPUnit.
 * Uso: php backend/tests/run_interpretar_tests.php (desde raíz del proyecto)
 */

$base = dirname(__DIR__);
require_once $base . '/config/config.php';
require_once $base . '/config/ConfigApi.php';
require_once $base . '/services/AnaliticaInterpretarService.php';

use Services\AnaliticaInterpretarService;

function assertTrue($cond, string $msg = ''): void
{
    if (!$cond) {
        throw new RuntimeException($msg ?: 'Assertion failed');
    }
}

function assertContains($needle, $haystack, string $msg = ''): void
{
    if (is_array($haystack)) {
        if (!in_array($needle, $haystack, true)) {
            throw new RuntimeException($msg ?: "Array does not contain: " . json_encode($needle));
        }
        return;
    }
    if (strpos((string) $haystack, (string) $needle) === false) {
        throw new RuntimeException($msg ?: "String does not contain: $needle");
    }
}

function assertArrayHasKey($key, array $arr, string $msg = ''): void
{
    if (!array_key_exists($key, $arr)) {
        throw new RuntimeException($msg ?: "Missing key: $key");
    }
}

function assertGreaterThanOrEqual($a, $b, string $msg = ''): void
{
    if ($b < $a) {
        throw new RuntimeException($msg ?: "Expected >= $a, got $b");
    }
}

function assertLessThanOrEqual($a, $b, string $msg = ''): void
{
    if ($b > $a) {
        throw new RuntimeException($msg ?: "Expected <= $a, got $b");
    }
}

function assertIsArray($v, string $msg = ''): void
{
    if (!is_array($v)) {
        throw new RuntimeException($msg ?: 'Expected array');
    }
}

$svc = new AnaliticaInterpretarService();
$tz = new DateTimeZone('America/Mexico_City');

$inputBase = [
    'analitica_espacial' => [],
    'analitica_pagos' => [],
    'analitica_gestiones' => [],
    'metadata' => [
        'idCredito' => 999,
        'idTicket' => 0,
        'fecha_actual' => (new DateTime('now', $tz))->format('c'),
        'timezone' => 'America/Mexico_City',
    ],
];

$ok = 0;
$fail = 0;

// 1. Cliente sin GPS
try {
    $input = $inputBase;
    $input['analitica_espacial'] = [];
    $input['analitica_pagos'] = ['total_pagos' => 0];
    $input['analitica_gestiones'] = [];
    $result = $svc->interpretar($input, null);
    assertTrue($result['success']);
    assertIsArray($result['data']);
    $data = $result['data'];
    assertArrayHasKey('one_line_summary', $data);
    assertArrayHasKey('missing_data', $data);
    assertContains('analitica_espacial', $data['missing_data']);
    assertArrayHasKey('overall_confidence', $data);
    assertGreaterThanOrEqual(0, $data['overall_confidence']);
    assertLessThanOrEqual(1, $data['overall_confidence']);
    assertArrayHasKey('predictions', $data);
    assertArrayHasKey('sections', $data);
    echo "OK testClienteSinGps\n";
    $ok++;
} catch (Throwable $e) {
    echo "FAIL testClienteSinGps: " . $e->getMessage() . "\n";
    $fail++;
}

// 2. Promesa vencida
try {
    $input = $inputBase;
    $input['analitica_espacial'] = [];
    $fechaActual = (new DateTime('now', $tz))->format('c');
    $hace70Dias = (new DateTime('-70 days', $tz))->format('c');
    $promesaPasada = (new DateTime('-5 days', $tz))->format('Y-m-d');
    $input['analitica_pagos'] = [
        'last_payment_date' => $hace70Dias,
        'estado_actual' => null,
        'dias_mora' => 70,
        'promesa_pago' => $promesaPasada,
        'monto_prometido' => null,
        'total_deuda' => 1000,
        'total_pagos' => 1,
    ];
    $input['analitica_gestiones'] = [];
    $input['metadata']['fecha_actual'] = $fechaActual;
    $result = $svc->interpretar($input, null);
    assertTrue($result['success']);
    $data = $result['data'];
    assertArrayHasKey('predictions', $data);
    $evidenceRefs = $data['evidence_references'] ?? [];
    $tieneRiesgoPago = in_array('rule:riesgo_alto_pago', $evidenceRefs, true);
    foreach ($data['predictions'] as $p) {
        if (stripos($p['label'], 'riesgo') !== false || stripos($p['label'], 'pago') !== false) {
            $tieneRiesgoPago = true;
            break;
        }
    }
    assertTrue($tieneRiesgoPago, 'Se esperaba riesgo alto de pago');
    echo "OK testPromesaVencida\n";
    $ok++;
} catch (Throwable $e) {
    echo "FAIL testPromesaVencida: " . $e->getMessage() . "\n";
    $fail++;
}

// 3. Baja eficacia gestores
try {
    $input = $inputBase;
    $input['analitica_espacial'] = [];
    $input['analitica_pagos'] = ['total_pagos' => 0];
    $fecha = (new DateTime('now', $tz))->format('c');
    $input['analitica_gestiones'] = [
        'porcentaje_cumplimiento' => 15,
        'detalles' => [
            ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
            ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
            ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
            ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => true],
            ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
        ],
    ];
    $result = $svc->interpretar($input, null);
    assertTrue($result['success']);
    $data = $result['data'];
    $evidenceRefs = $data['evidence_references'] ?? [];
    $tieneBajaEficacia = in_array('rule:baja_eficacia_gestores', $evidenceRefs, true);
    foreach ($data['predictions'] as $p) {
        if (stripos($p['label'], 'eficacia') !== false || stripos($p['label'], 'gestor') !== false) {
            $tieneBajaEficacia = true;
            break;
        }
    }
    assertTrue($tieneBajaEficacia, 'Se esperaba baja eficacia de gestores');
    echo "OK testBajaEficaciaGestores\n";
    $ok++;
} catch (Throwable $e) {
    echo "FAIL testBajaEficaciaGestores: " . $e->getMessage() . "\n";
    $fail++;
}

// 4. Schema salida
try {
    $input = $inputBase;
    $result = $svc->interpretar($input, null);
    assertTrue($result['success']);
    $data = $result['data'];
    assertArrayHasKey('one_line_summary', $data);
    assertArrayHasKey('sections', $data);
    assertArrayHasKey('predictions', $data);
    assertArrayHasKey('next_steps', $data);
    assertArrayHasKey('recommended_messages', $data);
    assertArrayHasKey('missing_data', $data);
    assertArrayHasKey('overall_confidence', $data);
    assertArrayHasKey('evidence_references', $data);
    assertIsArray($data['sections']);
    assertArrayHasKey('cliente', $data['sections']);
    assertArrayHasKey('gestores', $data['sections']);
    assertArrayHasKey('pagos', $data['sections']);
    echo "OK testSchemaSalida\n";
    $ok++;
} catch (Throwable $e) {
    echo "FAIL testSchemaSalida: " . $e->getMessage() . "\n";
    $fail++;
}

echo "\nTotal: $ok OK, $fail FAIL\n";
exit($fail > 0 ? 1 : 0);
