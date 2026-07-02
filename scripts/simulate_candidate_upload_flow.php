<?php

declare(strict_types=1);

error_reporting(E_ERROR | E_PARSE);

$root = dirname(__DIR__);
$backend = $root . DIRECTORY_SEPARATOR . 'backend';

if (!defined('RAIZ')) define('RAIZ', $backend);
if (!defined('CONTROLADORES')) define('CONTROLADORES', RAIZ . '/controllers');
if (!defined('MODELOS')) define('MODELOS', RAIZ . '/models');
if (!defined('LIBRERIAS')) define('LIBRERIAS', RAIZ . '/libs');
if (!defined('VISTAS')) define('VISTAS', RAIZ . '/views');
if (!defined('VISTA_DEFECTO')) define('VISTA_DEFECTO', 'Inicio');
if (!defined('METODO_DEFECTO')) define('METODO_DEFECTO', 'index');

spl_autoload_register(static function (string $archivo): void {
    $archivo = str_replace('\\', '/', $archivo);
    $ruta = RAIZ . '/' . $archivo . '.php';
    if (is_readable($ruta)) {
        require_once $ruta;
    }
});

require_once CONTROLADORES . '/CapHum.php';

function parse_args_sim(array $argv): array
{
    $out = ['case' => 'prueba', 'mode' => 'parallel', 'json' => true];
    foreach (array_slice($argv, 1) as $arg) {
        if (strpos($arg, '--case=') === 0) {
            $out['case'] = strtolower(trim(substr($arg, 7)));
        } elseif (strpos($arg, '--mode=') === 0) {
            $out['mode'] = strtolower(trim(substr($arg, 7)));
        } elseif ($arg === '--pretty') {
            $out['json'] = false;
        }
    }
    return $out;
}

function existing_items(array $map): array
{
    $items = [];
    foreach ($map as $tipo => $path) {
        if (!is_file($path)) {
            continue;
        }
        $items[(int) $tipo] = [
            'file_key' => 'archivo_' . (int) $tipo,
            'nombre_original' => basename($path),
            'ext' => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
            'tmp_name' => $path,
        ];
    }
    return $items;
}

function candidate_cases(): array
{
    $base = 'C:/Users/amigo_j9s4pcx/Downloads/Pruebas';
    return [
        'prueba' => [
            1 => $base . '/prueba candidato/Solicitud_Maxikash_JIMENEZ.pdf',
            3 => $base . '/prueba candidato/acta de nacimiento.pdf',
            4 => $base . '/prueba candidato/curp (37).pdf',
            5 => $base . '/prueba candidato/indentificacion.pdf',
            6 => $base . '/prueba candidato/Comprobante de domicilio.pdf',
            7 => $base . '/prueba candidato/Constancia de SF.pdf',
            8 => $base . '/prueba candidato/tarjetaNSS71139007331.pdf',
            10 => $base . '/prueba candidato/Estado de Cuenta - RC8VEXDN (1).pdf',
        ],
        'rojas' => [
            1 => $base . '/34. ROJAS ROJAS LUIS ANGEL/SOLICITUD.pdf',
            3 => $base . '/34. ROJAS ROJAS LUIS ANGEL/ACTA NACIMIENTO.pdf',
            4 => $base . '/34. ROJAS ROJAS LUIS ANGEL/CURP.pdf',
            5 => $base . '/34. ROJAS ROJAS LUIS ANGEL/INE.pdf',
            6 => $base . '/34. ROJAS ROJAS LUIS ANGEL/DOMICILIO.pdf',
            7 => $base . '/34. ROJAS ROJAS LUIS ANGEL/CSF.pdf',
            8 => $base . '/34. ROJAS ROJAS LUIS ANGEL/NSS.pdf',
            10 => $base . '/34. ROJAS ROJAS LUIS ANGEL/BBVA.pdf',
        ],
        'corona' => [
            1 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/SOLICITUD.pdf',
            3 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/ACTA NACIMIENTO.pdf',
            4 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/CURP.pdf',
            5 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/INE.pdf',
            6 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/DOMICILIO.pdf',
            7 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/CSF.pdf',
            8 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/NSS.pdf',
            10 => $base . '/37. CORONA CRUZ MIGUEL ANGEL/BBVA.pdf',
        ],
        'gomez' => [
            1 => $base . '/35. GOMEZ VEJAR SILVIA/SOLICITUD.pdf',
            3 => $base . '/35. GOMEZ VEJAR SILVIA/ACTA NACIMIENTO.pdf',
            4 => $base . '/35. GOMEZ VEJAR SILVIA/CURP.pdf',
            5 => $base . '/35. GOMEZ VEJAR SILVIA/INE.pdf',
            6 => $base . '/35. GOMEZ VEJAR SILVIA/DOMICILIO.pdf',
            7 => $base . '/35. GOMEZ VEJAR SILVIA/CSF.pdf',
            8 => $base . '/35. GOMEZ VEJAR SILVIA/NSS.pdf',
            10 => $base . '/35. GOMEZ VEJAR SILVIA/BBVA.pdf',
        ],
        'teran' => [
            1 => $base . '/38. TERAN GARCIA MARGARITA/SOLICITUD.pdf',
            3 => $base . '/38. TERAN GARCIA MARGARITA/ACTA NACIMIENTO.pdf',
            4 => $base . '/38. TERAN GARCIA MARGARITA/CURP.pdf',
            5 => $base . '/38. TERAN GARCIA MARGARITA/INE.pdf',
            6 => $base . '/38. TERAN GARCIA MARGARITA/DOMICILIO.pdf',
            7 => $base . '/38. TERAN GARCIA MARGARITA/CSF.pdf',
            8 => $base . '/38. TERAN GARCIA MARGARITA/NSS.pdf',
            10 => $base . '/38. TERAN GARCIA MARGARITA/BBVA.pdf',
        ],
    ];
}

function summarize_precheck(int $tipo, array $item, ?array $res): array
{
    $ver = is_array($res) ? ($res['verificacion'] ?? null) : null;
    $meta = is_array($ver) ? ($ver['_precheck_carga'] ?? null) : null;
    return [
        'tipo' => $tipo,
        'archivo' => $item['nombre_original'] ?? '',
        'aceptar' => is_array($res) ? (bool) ($res['aceptar'] ?? false) : false,
        'mensaje' => is_array($res) ? ($res['mensaje'] ?? null) : 'Sin respuesta de precheck.',
        'valido' => is_array($ver) && array_key_exists('valido', $ver) ? $ver['valido'] : null,
        'rechazado' => is_array($ver) && array_key_exists('rechazado', $ver) ? $ver['rechazado'] : null,
        'revision_manual' => is_array($ver) && array_key_exists('revision_manual', $ver) ? $ver['revision_manual'] : null,
        'resultado' => is_array($ver) ? ($ver['resultado'] ?? null) : null,
        'motor_ia' => is_array($ver) ? ($ver['motor_ia'] ?? null) : null,
        'cache_ia' => is_array($ver) ? ($ver['cache_ia'] ?? null) : null,
        'tiempo_ms' => is_array($meta) ? ($meta['tiempo_ms'] ?? null) : null,
        'fallback' => is_array($meta) ? ($meta['fallback'] ?? false) : false,
    ];
}

$args = parse_args_sim($argv);
$cases = candidate_cases();
$caseName = array_key_exists($args['case'], $cases) ? $args['case'] : 'prueba';
$items = existing_items($cases[$caseName]);

$rc = new ReflectionClass(\Controllers\CapHum::class);
$ctrl = $rc->newInstanceWithoutConstructor();
$parallel = $rc->getMethod('prevalidarContenidoDocumentosCandidatoEnParalelo');
$parallel->setAccessible(true);
$single = $rc->getMethod('verificarContenidoDocumentoCandidatoAntesDeGuardar');
$single->setAccessible(true);

$inicio = microtime(true);
$resultados = [];
if ($args['mode'] === 'sequential') {
    foreach ($items as $tipo => $item) {
        $t0 = microtime(true);
        $res = $single->invoke($ctrl, (int) $tipo, (string) $item['tmp_name']);
        $row = summarize_precheck((int) $tipo, $item, is_array($res) ? $res : null);
        $row['tiempo_ms'] = (int) round((microtime(true) - $t0) * 1000);
        $resultados[] = $row;
    }
} else {
    $prechecks = $parallel->invoke($ctrl, $items);
    foreach ($items as $tipo => $item) {
        $resultados[] = summarize_precheck((int) $tipo, $item, is_array($prechecks[$tipo] ?? null) ? $prechecks[$tipo] : null);
    }
}
$totalMs = (int) round((microtime(true) - $inicio) * 1000);

$summary = [
    'generated_at' => date('Y-m-d H:i:s'),
    'case' => $caseName,
    'mode' => $args['mode'] === 'sequential' ? 'sequential' : 'parallel',
    'docs_input' => count($items),
    'docs_accepted' => count(array_filter($resultados, static fn($r) => !empty($r['aceptar']))),
    'docs_rejected' => count(array_filter($resultados, static fn($r) => empty($r['aceptar']))),
    'elapsed_ms' => $totalMs,
    'results' => $resultados,
];

$outDir = $root . '/output/pdf/upload_flow_simulation';
if (!is_dir($outDir)) {
    @mkdir($outDir, 0775, true);
}
$outFile = $outDir . '/candidate_upload_' . $caseName . '_' . $summary['mode'] . '_' . date('Ymd_His') . '.json';
@file_put_contents($outFile, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$summary['output_file'] = $outFile;

if ($args['json']) {
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    echo 'Caso: ' . $summary['case'] . ' | modo: ' . $summary['mode'] . ' | docs: ' . $summary['docs_input'] . PHP_EOL;
    echo 'Aceptados: ' . $summary['docs_accepted'] . ' | rechazados: ' . $summary['docs_rejected'] . ' | total_ms: ' . $summary['elapsed_ms'] . PHP_EOL;
    foreach ($summary['results'] as $r) {
        echo '#' . $r['tipo'] . ' ' . $r['archivo'] . ' => ' . ($r['aceptar'] ? 'OK' : 'RECHAZA') . ' (' . ($r['tiempo_ms'] ?? '-') . ' ms)' . PHP_EOL;
        if ($r['mensaje']) {
            echo '  ' . $r['mensaje'] . PHP_EOL;
        }
    }
    echo 'Archivo: ' . $outFile . PHP_EOL;
}
