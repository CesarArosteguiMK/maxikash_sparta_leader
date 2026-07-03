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

require_once RAIZ . '/core/DatabaseCliSupport.php';
require_once RAIZ . '/core/Database.php';

function sim_arg(array $argv, string $name, ?string $default = null): ?string
{
    $prefix = '--' . $name . '=';
    foreach (array_slice($argv, 1) as $arg) {
        if (strpos($arg, $prefix) === 0) {
            return substr($arg, strlen($prefix));
        }
    }
    return $default;
}

function sim_has_flag(array $argv, string $flag): bool
{
    return in_array('--' . $flag, array_slice($argv, 1), true);
}

function sim_candidate_files(string $case): array
{
    $base = 'C:/Users/amigo_j9s4pcx/Downloads/Pruebas';
    $cases = [
        'gomez' => [
            1 => $base . '/35. GOMEZ VEJAR SILVIA/SOLICITUD.pdf',
            2 => $base . '/35. GOMEZ VEJAR SILVIA/CV.pdf',
            3 => $base . '/35. GOMEZ VEJAR SILVIA/ACTA NACIMIENTO.pdf',
            4 => $base . '/35. GOMEZ VEJAR SILVIA/CURP.pdf',
            5 => $base . '/35. GOMEZ VEJAR SILVIA/INE.pdf',
            6 => $base . '/35. GOMEZ VEJAR SILVIA/DOMICILIO.pdf',
            7 => $base . '/35. GOMEZ VEJAR SILVIA/CSF.pdf',
            8 => $base . '/35. GOMEZ VEJAR SILVIA/NSS.pdf',
            9 => $base . '/35. GOMEZ VEJAR SILVIA/CARTA NO CREDITOS.pdf',
            10 => $base . '/35. GOMEZ VEJAR SILVIA/BBVA.pdf',
        ],
        'prueba' => [
            1 => $base . '/prueba candidato/Solicitud_Maxikash_JIMENEZ.pdf',
            2 => $base . '/prueba candidato/solicitud de empleo.pdf',
            3 => $base . '/prueba candidato/acta de nacimiento.pdf',
            4 => $base . '/prueba candidato/curp (37).pdf',
            5 => $base . '/prueba candidato/indentificacion.pdf',
            6 => $base . '/prueba candidato/Comprobante de domicilio.pdf',
            7 => $base . '/prueba candidato/Constancia de SF.pdf',
            8 => $base . '/prueba candidato/tarjetaNSS71139007331.pdf',
            9 => $base . '/prueba candidato/Infonavit.pdf',
            10 => $base . '/prueba candidato/Estado de Cuenta - RC8VEXDN (1).pdf',
        ],
    ];
    return $cases[$case] ?? $cases['gomez'];
}

function sim_candidate_profile(string $case, bool $mismatch): array
{
    if ($mismatch) {
        $stamp = date('YmdHis');
        return [
            'nombres' => 'PRUEBA CODEX',
            'apellidop' => 'UPLOADFLOW',
            'apellidom' => $stamp,
            'email_prefix' => 'codex.upload.mismatch.' . $stamp,
        ];
    }

    $profiles = [
        'gomez' => [
            'nombres' => 'SILVIA',
            'apellidop' => 'GOMEZ',
            'apellidom' => 'VEJAR',
            'email_prefix' => 'codex.upload.gomez.' . date('YmdHis'),
        ],
        'prueba' => [
            'nombres' => 'JESUS ANGEL',
            'apellidop' => 'MAYA',
            'apellidom' => 'HERNANDEZ',
            'email_prefix' => 'codex.upload.prueba.' . date('YmdHis'),
        ],
    ];

    return $profiles[$case] ?? $profiles['gomez'];
}

function sim_has_expira(Core\Database $db): bool
{
    try {
        $row = $db->queryOne("
            SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'candidato_documento_token'
              AND COLUMN_NAME = 'expira'
        ");
        return (int)($row['c'] ?? 0) > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function sim_insert_candidate(Core\Database $db, array $profile): array
{
    $stamp = date('YmdHis');
    $fecha = date('Y-m-d H:i:s');
    $sql = "
        INSERT INTO candidatos (
            nombres, segundo_nombre, apellidop, apellidom,
            email, telefono, id_pais, id_div_nivel1, id_div_nivel2, id_div_nivel3,
            domicilio_calle_texto, domicilio_num_exterior, domicilio_num_interior, codigo_postal,
            id_puesto, id_departamento, id_posible_jefe, id_jefe_divisional,
            fecha_postulacion, id_legion, usuario, contrasena,
            postulacion_enviada, fecha_postulacion_enviada, estatus, notas, fecha_registro
        ) VALUES (
            :nombres, NULL, :apellidop, :apellidom,
            :email, :telefono, NULL, NULL, NULL, NULL,
            NULL, NULL, NULL, NULL,
            NULL, NULL, NULL, NULL,
            NULL, NULL, NULL, NULL,
            1, :fecha_enviada, :estatus, :notas, :fecha_registro
        )
    ";
    $db->CRUD($sql, [
        'nombres' => $profile['nombres'],
        'apellidop' => $profile['apellidop'],
        'apellidom' => $profile['apellidom'],
        'email' => $profile['email_prefix'] . '@example.invalid',
        'telefono' => '5550000000',
        'fecha_enviada' => $fecha,
        'estatus' => 'Prueba automatica',
        'notas' => 'Registro temporal creado por scripts/simulate_candidate_upload_http.php',
        'fecha_registro' => $fecha,
    ]);
    $row = $db->queryOne('SELECT LAST_INSERT_ID() AS id');
    $id = (int)($row['id'] ?? 0);
    if ($id <= 0) {
        throw new RuntimeException('No se obtuvo id de candidato de prueba.');
    }
    return ['id' => $id, 'stamp' => $stamp];
}

function sim_insert_token(Core\Database $db, int $id): string
{
    $token = bin2hex(random_bytes(32));
    if (sim_has_expira($db)) {
        $db->CRUD(
            'INSERT INTO candidato_documento_token (id_candidato, token, expira) VALUES (:id, :token, :expira)',
            ['id' => $id, 'token' => $token, 'expira' => date('Y-m-d H:i:s', time() + 86400)]
        );
    } else {
        $db->CRUD(
            'INSERT INTO candidato_documento_token (id_candidato, token) VALUES (:id, :token)',
            ['id' => $id, 'token' => $token]
        );
    }
    return $token;
}

function sim_post_upload(string $url, array $files): array
{
    $post = [];
    foreach ($files as $tipo => $path) {
        if (!is_file($path)) {
            throw new RuntimeException('No existe archivo para tipo ' . $tipo . ': ' . $path);
        }
        $post['archivo_' . (int)$tipo] = new CURLFile($path, 'application/pdf', basename($path));
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 240,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HEADER => false,
    ]);
    $inicio = microtime(true);
    $body = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    $elapsed = (int)round((microtime(true) - $inicio) * 1000);
    $json = is_string($body) ? json_decode($body, true) : null;
    return [
        'http_code' => $http,
        'curl_error' => $err ?: null,
        'elapsed_ms' => $elapsed,
        'json' => is_array($json) ? $json : null,
        'body_head' => is_string($body) ? substr($body, 0, 500) : null,
    ];
}

function sim_query_state(Core\Database $db, int $id): array
{
    $docs = $db->queryAll(
        'SELECT tipo_documento, nombre_archivo, ruta_archivo, verificacion_fiscal_json, verificacion_calidad_json FROM candidato_documento WHERE id_candidato = :id ORDER BY id',
        ['id' => $id]
    );
    $jobs = [];
    try {
        $jobs = $db->queryAll(
            'SELECT id, estado, intentos, expediente_completo, last_error FROM candidato_verificacion_documental_job WHERE id_candidato = :id ORDER BY id',
            ['id' => $id]
        );
    } catch (Throwable $e) {
        $jobs = [];
    }
    $cand = $db->queryOne('SELECT id, ultima_verificacion_expediente FROM candidatos WHERE id = :id', ['id' => $id]);
    return [
        'docs_count' => count($docs),
        'docs' => array_map(static function ($d) {
            return [
                'tipo_documento' => $d['tipo_documento'] ?? '',
                'nombre_archivo' => $d['nombre_archivo'] ?? '',
                'ruta_archivo' => $d['ruta_archivo'] ?? '',
                'tiene_verificacion' => trim((string)($d['verificacion_fiscal_json'] ?? $d['verificacion_calidad_json'] ?? '')) !== '',
            ];
        }, $docs),
        'jobs' => $jobs,
        'ultima_verificacion' => $cand['ultima_verificacion_expediente'] ?? null,
    ];
}

function sim_wait_jobs(Core\Database $db, int $id, int $seconds): array
{
    $state = sim_query_state($db, $id);
    $deadline = time() + max(0, $seconds);
    while (time() < $deadline) {
        $jobs = $state['jobs'] ?? [];
        $active = array_filter($jobs, static fn($j) => in_array((string)($j['estado'] ?? ''), ['pendiente', 'procesando'], true));
        if (!$active) {
            return $state;
        }
        sleep(5);
        $state = sim_query_state($db, $id);
    }
    return $state;
}

function sim_rrmdir_safe(int $id): void
{
    if ($id <= 0) {
        return;
    }
    $base = realpath(RAIZ . '/storage/candidatos');
    $dir = RAIZ . '/storage/candidatos/' . $id;
    if (!$base || !is_dir($dir)) {
        return;
    }
    $real = realpath($dir);
    if (!$real || strpos($real, $base) !== 0) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }
    @rmdir($real);
}

function sim_cleanup(Core\Database $db, int $id): void
{
    foreach ([
        'candidato_verificacion_documental_job',
        'candidato_documento_eliminacion',
        'candidato_documento_subida_manual',
        'candidato_bitacora',
        'candidato_documento',
        'candidato_documento_token',
    ] as $table) {
        try {
            $db->CRUD("DELETE FROM {$table} WHERE id_candidato = :id", ['id' => $id]);
        } catch (Throwable $e) {
        }
    }
    $db->CRUD('DELETE FROM candidatos WHERE id = :id', ['id' => $id]);
    sim_rrmdir_safe($id);
}

$case = strtolower((string)sim_arg($argv, 'case', 'gomez'));
$baseUrl = rtrim((string)sim_arg($argv, 'base-url', 'http://127.0.0.1/sparta___SPARTA_SECRET_REDACTED__/public'), '/');
$keep = sim_has_flag($argv, 'keep');
$mismatch = sim_has_flag($argv, 'mismatch');
$waitSeconds = (int)sim_arg($argv, 'wait', '90');

$db = new Core\Database();
$created = null;
$summary = [
    'generated_at' => date('Y-m-d H:i:s'),
    'case' => $case,
    'base_url' => $baseUrl,
    'cleanup' => !$keep,
    'mismatch' => $mismatch,
];

try {
    $profile = sim_candidate_profile($case, $mismatch);
    $created = sim_insert_candidate($db, $profile);
    $id = (int)$created['id'];
    $token = sim_insert_token($db, $id);
    $url = $baseUrl . '/CapHum/subirDocumentosCandidato/' . rawurlencode($token);
    $summary['id_candidato_prueba'] = $id;
    $summary['candidate_profile'] = $profile;
    $summary['token_tail'] = substr($token, -8);
    $summary['url'] = $url;

    $summary['post'] = sim_post_upload($url, sim_candidate_files($case));
    $summary['state_after_post'] = sim_query_state($db, $id);
    $summary['state_after_wait'] = sim_wait_jobs($db, $id, $waitSeconds);

    if (!$keep) {
        sim_cleanup($db, $id);
        $summary['state_after_cleanup'] = [
            'candidate_exists' => (bool)$db->queryOne('SELECT id FROM candidatos WHERE id = :id', ['id' => $id]),
            'docs_count' => (int)(($db->queryOne('SELECT COUNT(*) AS c FROM candidato_documento WHERE id_candidato = :id', ['id' => $id])['c'] ?? 0)),
        ];
    }
} catch (Throwable $e) {
    $summary['error'] = $e->getMessage();
    if (!$keep && is_array($created ?? null) && !empty($created['id'])) {
        try {
            sim_cleanup($db, (int)$created['id']);
            $summary['cleanup_after_error'] = true;
        } catch (Throwable $cleanupError) {
            $summary['cleanup_after_error'] = $cleanupError->getMessage();
        }
    }
}

$outDir = $root . '/output/pdf/upload_flow_simulation';
if (!is_dir($outDir)) {
    @mkdir($outDir, 0775, true);
}
$outFile = $outDir . '/candidate_upload_http_' . $case . '_' . date('Ymd_His') . '.json';
@file_put_contents($outFile, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$summary['output_file'] = $outFile;

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
