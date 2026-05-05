<?php
declare(strict_types=1);

function atc_json(array $body, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    );
    exit;
}

function atc_openapi_spec(): array
{
    return [
        'openapi' => '3.0.3',
        'info' => [
            'title' => 'ATC Temporal API',
            'description' => 'Servicio temporal ATC (fase 1): solo persona_demograficos con datos.',
            'version' => '1.0.0',
        ],
        'servers' => [
            ['url' => 'http://127.0.0.1:8097'],
        ],
        'paths' => [
            '/health' => [
                'get' => [
                    'tags' => ['system'],
                    'summary' => 'Estado del servicio',
                    'responses' => [
                        '200' => [
                            'description' => 'Servicio activo',
                        ],
                    ],
                ],
            ],
            '/atc/variables' => [
                'get' => [
                    'tags' => ['atc'],
                    'summary' => 'Obtiene los 4 bloques JSON ATC',
                    'description' => 'Solo persona_demograficos tiene datos en fase 1. Los demás bloques se devuelven vacíos.',
                    'parameters' => [
                        [
                            'name' => 'id_credito',
                            'in' => 'query',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                            'description' => 'ID de crédito (resuelve fk_persona en oferta de __SPARTA_SECRET_REDACTED__)',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Respuesta OK',
                        ],
                        '400' => [
                            'description' => 'Parámetros incompletos',
                        ],
                        '404' => [
                            'description' => 'Persona no encontrada',
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function atc_persona_score(array $p): int
{
    $keys = [
        'nombres',
        'apellidop',
        'apellidom',
        'curp',
        '_rfc',
        '_fecha_nacimiento',
        '_sexo',
        'telefono_uno',
        'correo',
        '_estado_txt',
        '_municipio_txt',
        '_colonia_txt',
        'domicilio_calle_texto',
        'domicilio_num_exterior',
        'codigo_postal',
    ];
    $score = 0;
    foreach ($keys as $k) {
        if (trim((string) ($p[$k] ?? '')) !== '') {
            $score++;
        }
    }

    return $score;
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($path) ? rtrim($path, '/') : '/';
if ($path === '') {
    $path = '/';
}

if ($path === '/') {
    header('Location: /docs', true, 302);
    exit;
}

if ($path === '/openapi.json') {
    atc_json(atc_openapi_spec());
}

if ($path === '/docs') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>ATC Temporal API - Swagger</title>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">';
    echo '<style>body{margin:0;background:#fafafa}#swagger-ui{max-width:1200px;margin:0 auto}</style>';
    echo '</head><body><div id="swagger-ui"></div>';
    echo '<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>';
    echo '<script>window.ui=SwaggerUIBundle({url:"/openapi.json",dom_id:"#swagger-ui",deepLinking:true,displayRequestDuration:true,defaultModelsExpandDepth:1,docExpansion:"list"});</script>';
    echo '</body></html>';
    exit;
}

if ($path === '/health') {
    atc_json([
        'success' => true,
        'service' => 'atc-temporal-api',
        'status' => 'ok',
        'timestamp' => date('c'),
    ]);
}

if ($method !== 'GET') {
    atc_json(['success' => false, 'mensaje' => 'Solo GET'], 405);
}

if ($path !== '/atc/variables') {
    atc_json([
        'success' => false,
        'mensaje' => 'Ruta no encontrada',
        'rutas_disponibles' => ['/health', '/docs', '/openapi.json', '/atc/variables?id_persona=123'],
    ], 404);
}

$idCredito = isset($_GET['id_credito']) ? (int) $_GET['id_credito'] : 0;
if ($idCredito <= 0) {
    atc_json(['success' => false, 'mensaje' => 'Debe enviar id_credito.'], 400);
}

$root = dirname(__DIR__, 3);
define('RAIZ', $root . DIRECTORY_SEPARATOR . 'backend');
if (!defined('SPARTA_PROJECT_ROOT')) {
    define('SPARTA_PROJECT_ROOT', $root);
}
if (!defined('SPARTA_UPLOADS_ROOT')) {
    define('SPARTA_UPLOADS_ROOT', $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
}

$cfgFile = RAIZ . '/config/config.ini';
if (!is_readable($cfgFile)) {
    atc_json(['success' => false, 'mensaje' => 'No se encontró config.ini'], 500);
}
define('CONFIGURACION', parse_ini_file($cfgFile));

require_once $root . '/backend/bootstrap_composer.php';
sparta_require_composer_autoload();

spl_autoload_register(function ($archivo) {
    if (strpos($archivo, 'PhpOffice\\') === 0 ||
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }
    $archivo = str_replace('\\', '/', $archivo);
    $parts = explode('/', $archivo, 2);
    $top = $parts[0];
    $tail = $parts[1] ?? '';
    static $dirMap = [
        'Models' => 'models',
        'Controllers' => 'controllers',
        'Core' => 'core',
        'Libs' => 'libs',
        'Services' => 'services',
    ];
    $dir = $dirMap[$top] ?? strtolower($top);
    $rel = $tail !== '' ? $dir . '/' . $tail : $dir;
    $ruta = RAIZ . '/' . $rel . '.php';
    if (file_exists($ruta)) {
        require_once $ruta;
    }
});
require_once RAIZ . '/config/config.php';

try {
    $db = new \Core\Database();
    $pid = 0;
    $fuentePersona = 'local';
    if ($idCredito > 0) {
        try {
            $dbAws = new \Core\DatabaseAWS('__SPARTA_SECRET_REDACTED__');
            $of = $dbAws->queryOne(
                "SELECT fk_persona FROM oferta WHERE id_oferta = :id LIMIT 1",
                ['id' => $idCredito]
            );
            $pid = (int) ($of['fk_persona'] ?? 0);
            if ($pid > 0) {
                $fuentePersona = '__SPARTA_SECRET_REDACTED__';
            }
        } catch (\Throwable $e) {
            $pid = 0;
        }
    }
    if ($pid <= 0) {
        $probe = $db->queryOne('SELECT id FROM persona WHERE id = :id LIMIT 1', ['id' => $idCredito]);
        $pid = (int) ($probe['id'] ?? 0);
        if ($pid > 0) {
            $fuentePersona = 'local';
        }
    }
    if ($pid <= 0) {
        atc_json(['success' => false, 'mensaje' => 'No se encontró persona.'], 404);
    }

    $pLocal = null;
    $pMaxi = null;

    $pl = $db->queryOne(
        "SELECT
            id, nombres, segundo_nombre, apellidop, apellidom, curp,
            telefono_uno, correo, id_div_nivel1, id_div_nivel2, id_div_nivel3,
            domicilio_calle_texto, domicilio_num_exterior, domicilio_num_interior, codigo_postal
         FROM persona
         WHERE id = :id
         LIMIT 1",
        ['id' => $pid]
    );
    if (is_array($pl) && !empty($pl)) {
        $pl['_rfc'] = '';
        $pl['_fecha_nacimiento'] = '';
        $pl['_sexo'] = '';
        $pl['_estado_txt'] = '';
        $pl['_municipio_txt'] = '';
        $pl['_colonia_txt'] = '';
        $pLocal = $pl;
    }

    try {
        $dbAws = new \Core\DatabaseAWS('__SPARTA_SECRET_REDACTED__');
        $pm = $dbAws->queryOne(
            "SELECT
                id_persona, primer_nombre, segundo_nombre, apellido_paterno, apellido_materno,
                rfc, curp, fecha_nacimiento, sexo, telefono_celular, email,
                estado, ciudad, colonia, direccion, calle_numero, codigo_postal
             FROM persona
             WHERE id_persona = :id
             LIMIT 1",
            ['id' => $pid]
        );
        if (is_array($pm) && !empty($pm)) {
            $pMaxi = [
                'id' => (int) ($pm['id_persona'] ?? 0),
                'nombres' => (string) ($pm['primer_nombre'] ?? ''),
                'segundo_nombre' => (string) ($pm['segundo_nombre'] ?? ''),
                'apellidop' => (string) ($pm['apellido_paterno'] ?? ''),
                'apellidom' => (string) ($pm['apellido_materno'] ?? ''),
                'curp' => (string) ($pm['curp'] ?? ''),
                'telefono_uno' => (string) ($pm['telefono_celular'] ?? ''),
                'correo' => (string) ($pm['email'] ?? ''),
                'id_div_nivel1' => null,
                'id_div_nivel2' => null,
                'id_div_nivel3' => null,
                'domicilio_calle_texto' => (string) ($pm['direccion'] ?? ''),
                'domicilio_num_exterior' => (string) ($pm['calle_numero'] ?? ''),
                'domicilio_num_interior' => '',
                'codigo_postal' => (string) ($pm['codigo_postal'] ?? ''),
                '_rfc' => (string) ($pm['rfc'] ?? ''),
                '_fecha_nacimiento' => (string) ($pm['fecha_nacimiento'] ?? ''),
                '_sexo' => (string) ($pm['sexo'] ?? ''),
                '_estado_txt' => (string) ($pm['estado'] ?? ''),
                '_municipio_txt' => (string) ($pm['ciudad'] ?? ''),
                '_colonia_txt' => (string) ($pm['colonia'] ?? ''),
            ];
        }
    } catch (\Throwable $e) {
        $pMaxi = null;
    }

    $scoreLocal = is_array($pLocal) ? atc_persona_score($pLocal) : -1;
    $scoreMaxi = is_array($pMaxi) ? atc_persona_score($pMaxi) : -1;
    if ($scoreMaxi >= $scoreLocal && $scoreMaxi > -1) {
        $p = $pMaxi;
        $fuentePersona = '__SPARTA_SECRET_REDACTED__';
    } else {
        $p = $pLocal;
        $fuentePersona = 'local';
    }

    if (!is_array($p) || empty($p)) {
        atc_json(['success' => false, 'mensaje' => 'La persona no existe.'], 404);
    }

    $d1 = '';
    $d2 = '';
    $d3 = '';
    try {
        if (!empty($p['id_div_nivel1'])) {
            $r1 = $db->queryOne("SELECT nombre FROM division_nivel_1 WHERE id = :id LIMIT 1", ['id' => (int) $p['id_div_nivel1']]);
            $d1 = (string) ($r1['nombre'] ?? '');
        }
        if (!empty($p['id_div_nivel2'])) {
            $r2 = $db->queryOne("SELECT nombre FROM division_nivel_2 WHERE id = :id LIMIT 1", ['id' => (int) $p['id_div_nivel2']]);
            $d2 = (string) ($r2['nombre'] ?? '');
        }
        if (!empty($p['id_div_nivel3'])) {
            $r3 = $db->queryOne("SELECT nombre FROM division_nivel_3 WHERE id = :id LIMIT 1", ['id' => (int) $p['id_div_nivel3']]);
            $d3 = (string) ($r3['nombre'] ?? '');
        }
    } catch (\Throwable $e) {
        $d1 = '';
        $d2 = '';
        $d3 = '';
    }

    $payload = [
        'success' => true,
        'datos' => [
            'persona_demograficos' => [
                'persona' => [
                    'id_persona' => (int) ($p['id'] ?? 0),
                    'primer_nombre' => (string) ($p['nombres'] ?? ''),
                    'segundo_nombre' => (string) ($p['segundo_nombre'] ?? ''),
                    'rfc' => (string) ($p['_rfc'] ?? ''),
                    'apellido_paterno' => (string) ($p['apellidop'] ?? ''),
                    'apellido_materno' => (string) ($p['apellidom'] ?? ''),
                    'curp' => (string) ($p['curp'] ?? ''),
                    'fecha_nacimiento' => (string) ($p['_fecha_nacimiento'] ?? ''),
                    'sexo' => (string) ($p['_sexo'] ?? ''),
                ],
                'demograficos_contacto' => [
                    'telefono_wa' => (string) ($p['telefono_uno'] ?? ''),
                    'email' => (string) ($p['correo'] ?? ''),
                ],
                'demograficos_dom_ine' => [
                    'estado' => (string) ($p['_estado_txt'] ?? $d1),
                    'municipio' => (string) ($p['_municipio_txt'] ?? $d2),
                    'colonia' => (string) ($p['_colonia_txt'] ?? $d3),
                    'calle' => (string) ($p['domicilio_calle_texto'] ?? ''),
                    'numero' => trim((string) (($p['domicilio_num_exterior'] ?? '') . ' ' . ($p['domicilio_num_interior'] ?? ''))),
                    'codigo_postal' => (string) ($p['codigo_postal'] ?? ''),
                ],
                'demograficos_dom_adicional' => [
                    'estado' => '',
                    'municipio' => '',
                    'colonia' => '',
                    'calle' => '',
                    'numero' => '',
                    'codigo_postal' => '',
                ],
            ],
            'ofertas_maxi' => [[
                'oferta_activa_en_tuberia' => null,
                'id_credito' => '',
                'precio_moto' => '',
                'enganche' => '',
                'monto_a_financiar' => '',
                'cuota' => '',
                'plazo' => '',
                'tasa' => '',
                'marca' => '',
                'modelo' => '',
                'fecha_de_la_oferta' => '',
                'etapa' => '',
                'vigencia' => '',
                'motivo_externo_bitacora' => '',
                'bandera_firma_pendiente' => null,
            ]],
            'creditos_en_cartera' => [[
                'id_credito' => '',
                'estatus_cartera' => '',
                'bandera_atraso' => null,
                'saldo_total_vencido_mas_gc' => '',
                'cuotas_vencidas_gc' => '',
                'cuotas_vencidas_cartera' => '',
                'cuotas_contratadas' => '',
                'cuotas_pagadas' => '',
                'total_pagare' => '',
                'saldo_total_por_pagar' => '',
                'cuenta_bancaria_para_pago' => '',
                'cuota' => '',
            ]],
            'pagos_por_credito' => [[
                'monto_pago' => '',
                'fecha_hora_pago' => '',
                'referencia_de_pago' => '',
                'bandera_pagos_realizados_al_credito' => null,
            ]],
            'meta' => [
                'fase' => 1,
                'solo_persona_con_datos' => true,
                'id_persona' => $pid,
                'id_credito_consultado' => $idCredito > 0 ? $idCredito : null,
                'fuente_persona' => $fuentePersona,
            ],
        ],
    ];

    atc_json($payload);
} catch (\Throwable $e) {
    atc_json([
        'success' => false,
        'mensaje' => 'Error interno',
        'error' => $e->getMessage(),
    ], 500);
}

