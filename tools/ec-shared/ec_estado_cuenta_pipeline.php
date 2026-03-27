<?php
/**
 * Pipeline compartido: bootstrap backend, validación MX/GT, consulta S2 y datos para cruce BD.
 * Usado por ec-webhook-worker/worker.php y ec-gc-excel-enrich/enrich_gc_excel.php.
 */

declare(strict_types=1);

/** Raíz del repo sparta___SPARTA_SECRET_REDACTED__ (cualquier carpeta bajo tools/…). */
function spartaLedgerRoot(string $baseDir): string
{
    return dirname(dirname($baseDir));
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
        $ruta = EC_WORKER_BACKEND_ROOT . '/' . str_replace('\\', '/', $clase) . '.php';
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
    if (!is_array($json) || !isset($json['estadoCuenta'])) {
        return ['success' => false, 'error' => 'Respuesta sin estadoCuenta'];
    }

    $ec = $json['estadoCuenta'];
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
