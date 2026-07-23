<?php
/**
 * Pipeline compartido: bootstrap backend, validación MX/GT, consulta S2 y datos para cruce BD.
 * Usado por ec-webhook-worker/worker.php y ec-gc-excel-enrich/enrich_gc_excel.php
 * (ambos viven bajo gastos-cobranza-agent/tools/).
 */

declare(strict_types=1);

/**
 * Raíz del repo sparta___SPARTA_SECRET_REDACTED__: sube directorios hasta encontrar backend/ y vendor/autoload.php.
 */
function spartaLedgerRoot(string $baseDir): string
{
    $dir = $baseDir;
    for ($i = 0; $i < 16; $i++) {
        $rp = realpath($dir);
        if ($rp !== false) {
            $dir = $rp;
        }
        $backend = $dir . DIRECTORY_SEPARATOR . 'backend';
        $vendor = $dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (is_dir($backend) && is_file($vendor)) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }

    fwrite(STDERR, "[FATAL] No se pudo resolver la raíz del repo (backend/ + vendor/autoload.php) desde {$baseDir}\n");
    exit(1);
}

/**
 * Autoload del backend (Core, Models, …) para ejecutar el cruce de gastos de cobranza como en la app.
 */
function ecWorkerBootstrapBackend(string $baseDir): void
{
    static $hecho = false;
    if ($hecho) {
        return;
    }
    $hecho = true;

    date_default_timezone_set('America/Mexico_City');

    $raizBackend = spartaLedgerRoot($baseDir) . DIRECTORY_SEPARATOR . 'backend';
    if (!is_dir($raizBackend)) {
        fwrite(STDERR, "[FATAL] No se encontró la carpeta backend: {$raizBackend}\n");
        exit(1);
    }

    if (!defined('EC_WORKER_BACKEND_ROOT')) {
        define('EC_WORKER_BACKEND_ROOT', $raizBackend);
    }

    spl_autoload_register(function (string $clase): void {
        if (strpos($clase, 'PhpOffice\\') === 0 ||
            strpos($clase, 'ZipStream\\') === 0 ||
            strpos($clase, 'Psr\\') === 0) {
            return;
        }

        $relativa = str_replace('\\', '/', $clase);
        // En Windows `Core/` y `core/` resuelven igual, pero el agente productivo
        // corre sobre un sistema de archivos sensible a mayúsculas/minúsculas.
        // Los directorios reales del backend se conservan en minúsculas.
        foreach (['Core', 'Models', 'Controllers', 'Services'] as $namespace) {
            $prefijo = $namespace . '/';
            if (strpos($relativa, $prefijo) === 0) {
                $relativa = strtolower($namespace) . '/' . substr($relativa, strlen($prefijo));
                break;
            }
        }

        $ruta = EC_WORKER_BACKEND_ROOT . '/' . $relativa . '.php';
        if (file_exists($ruta)) {
            require_once $ruta;
        }
    });
}

/**
 * Misma regla que EstadoCuenta::validarCredito (sin segunda llamada S2): si no hay referencias MX
 * pero sí filas en GT, el flujo de consulta MX no aplica.
 *
 * @return array{ok:bool, error?:string, tipo?:string}
 */
function ecWorkerValidarTerritorioCredito(int $idCredito, bool $saltarChequeo): array
{
    if ($saltarChequeo) {
        return ['ok' => true];
    }
    $refs = \Models\Empresa::getConsultaReferenciasEstadoCuenta($idCredito);
    if (empty($refs['datos'])) {
        $guat = \Models\Empresa::getGuatemalaEstadoCuenta($idCredito);
        if (!empty($guat['datos'])) {
            return [
                'ok' => false,
                'tipo' => 'credito_guatemala',
                'error' => 'Crédito de Guatemala (flujo Estado de cuenta MX no aplica)',
            ];
        }
    }

    return ['ok' => true];
}

/**
 * @return array{
 *   success:bool,
 *   error?:string,
 *   saldo_resumen?:string,
 *   datosNotasCargos?:list<array>,
 *   estadoCuenta?:array
 * }
 */
function ecS2ExtraerEstadoCuenta(array $json): ?array
{
    $candidatos = [
        $json['estadoCuenta'] ?? null,
        $json['data']['estadoCuenta'] ?? null,
        $json['data']['data']['estadoCuenta'] ?? null,
    ];

    // Algunas versiones de S2 envuelven el objeto directamente en `data`.
    if (isset($json['data']) && is_array($json['data']) && array_key_exists('idCredito', $json['data'])) {
        $candidatos[] = $json['data'];
    }

    foreach ($candidatos as $candidato) {
        if (is_array($candidato)) {
            return $candidato;
        }
    }

    return null;
}

function ecS2DescripcionRespuestaInvalida(array $json): string
{
    $partes = [];
    foreach (['code', 'status', 'tipo'] as $campo) {
        if (isset($json[$campo]) && is_scalar($json[$campo]) && trim((string) $json[$campo]) !== '') {
            $partes[] = $campo . '=' . trim((string) $json[$campo]);
        }
    }

    $mensaje = $json['mensaje'] ?? $json['message'] ?? $json['error'] ?? null;
    if (is_array($mensaje)) {
        $mensaje = implode(' ', array_map('strval', array_slice($mensaje, 0, 3)));
    }
    if (is_scalar($mensaje)) {
        $mensaje = trim((string) preg_replace('/[\r\n\t]+/', ' ', (string) $mensaje));
        if ($mensaje !== '') {
            $partes[] = 'mensaje=' . substr($mensaje, 0, 240);
        }
    }

    if ($partes === []) {
        $claves = array_slice(array_map('strval', array_keys($json)), 0, 8);
        if ($claves !== []) {
            $partes[] = 'claves=' . implode(',', $claves);
        }
    }

    return $partes !== [] ? ' (' . implode('; ', $partes) . ')' : '';
}

function ecS2EsFallaGlobal(array $json): bool
{
    $status = strtoupper(trim((string) ($json['status'] ?? $json['tipo'] ?? '')));
    if (in_array($status, ['ERROR_ACCESO', 'ERROR_AUTENTICACION', 'ERROR_CONFIGURACION'], true)) {
        return true;
    }

    $mensaje = $json['mensaje'] ?? $json['message'] ?? $json['error'] ?? '';
    if (is_array($mensaje)) {
        $mensaje = implode(' ', array_map('strval', $mensaje));
    }
    $mensaje = strtolower((string) $mensaje);

    return strpos($mensaje, 'handler class cannot be loaded') !== false
        || strpos($mensaje, 'missing or malformed token') !== false;
}

function consultarEstadoCuentaS2(string $endpoint, string $token, int $idCredito, string $fechaCorte): array
{
    $payload = json_encode([
        'idCredito' => $idCredito,
        'fechaCorte' => $fechaCorte,
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Token: ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_CONNECTTIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'error' => 'cURL: ' . ($curlErr ?: 'sin respuesta')];
    }
    if ($httpCode !== 200) {
        $jsonErr = json_decode((string) $response, true);
        $msg = null;
        if (is_array($jsonErr) && isset($jsonErr['mensaje']) && is_array($jsonErr['mensaje']) && isset($jsonErr['mensaje'][0])) {
            $msg = (string) $jsonErr['mensaje'][0];
        }
        $detalle = $msg !== null && $msg !== '' ? $msg : "HTTP {$httpCode}";

        return ['success' => false, 'error' => $detalle];
    }

    $json = json_decode($response, true);
    if (!is_array($json)) {
        return ['success' => false, 'error' => 'Respuesta S2 no es un objeto JSON'];
    }

    $ec = ecS2ExtraerEstadoCuenta($json);
    if ($ec === null) {
        return [
            'success' => false,
            'error' => 'Respuesta S2 sin estadoCuenta' . ecS2DescripcionRespuestaInvalida($json),
            'fallo_global' => ecS2EsFallaGlobal($json),
        ];
    }
    if (!is_array($ec)) {
        return ['success' => false, 'error' => 'estadoCuenta inválido'];
    }

    if (
        !isset($ec['idCredito']) ||
        $ec['idCredito'] === null ||
        $ec['idCredito'] === '' ||
        (is_string($ec['idCredito']) && trim($ec['idCredito']) === '')
    ) {
        return ['success' => false, 'error' => 'ID de crédito incorrecto en respuesta API'];
    }

    $saldo = null;
    if (isset($ec['datosSaldos']['saldoTotalVencido'])) {
        $saldo = 'Saldo vencido: ' . $ec['datosSaldos']['saldoTotalVencido'];
    }

    $notas = $ec['datosNotasCargos'] ?? [];
    if (!is_array($notas)) {
        $notas = [];
    }

    return [
        'success' => true,
        'saldo_resumen' => $saldo ?? '',
        'datosNotasCargos' => $notas,
        'estadoCuenta' => $ec,
    ];
}

/**
 * Recupera automáticamente el consumo cuando la ruta configurada apunta a un
 * controlador S2 retirado. Al funcionar la alternativa, actualiza $endpoint
 * para que los créditos siguientes ya no repitan la llamada fallida.
 */
function consultarEstadoCuentaS2ConFallback(
    string &$endpoint,
    string $token,
    int $idCredito,
    string $fechaCorte
): array {
    $result = consultarEstadoCuentaS2($endpoint, $token, $idCredito, $fechaCorte);
    if (!empty($result['success'])) {
        return $result;
    }

    $errorPrincipal = (string) ($result['error'] ?? '');
    if (stripos($errorPrincipal, 'handler class cannot be loaded') === false) {
        return $result;
    }

    $endpointAlternativo = trim((string) (
        getenv('S2_ESTADO_CUENTA_URL_FALLBACK')
        ?: 'https://servicios.s2movil.net/s2maxikash/estadocuenta'
    ));
    if ($endpointAlternativo === '' || rtrim($endpointAlternativo, '/') === rtrim($endpoint, '/')) {
        return $result;
    }

    $resultAlternativo = consultarEstadoCuentaS2(
        $endpointAlternativo,
        $token,
        $idCredito,
        $fechaCorte
    );
    $resultAlternativo['fallback_intentado'] = true;

    if (!empty($resultAlternativo['success'])) {
        $endpoint = $endpointAlternativo;
        $resultAlternativo['fallback_activado'] = true;

        return $resultAlternativo;
    }

    $errorAlternativo = (string) ($resultAlternativo['error'] ?? 'Error desconocido');
    $resultAlternativo['error'] = 'Ruta S2 principal: ' . $errorPrincipal
        . ' | Ruta S2 alternativa: ' . $errorAlternativo;

    return $resultAlternativo;
}
