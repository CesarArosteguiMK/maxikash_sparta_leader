<?php
/**
 * =============================================================================
 * CRON: ejecutar_gastos_cobranza_agente_diario.php
 * =============================================================================
 * Equivale a pulsar «Ejecutar agente» en Shell Gastos Cobranza: POST /run al
 * servicio Node (gastos-cobranza-agent), misma config que [gastoscobranza_agent]
 * en backend/config/config.ini.
 *
 * Programación principal (recomendada): el propio agente Node (gastos-cobranza-agent)
 * incluye un programador interno a las 10:00 hora civil CDMX (America/Mexico_City),
 * con opción GASTOS_GC_REMOTE_CDMX_TIME=1 para no depender del reloj del servidor.
 * No hace falta el Programador de tareas de Windows salvo que prefiera este PHP.
 *
 * Windows (opcional, Programador de tareas):
 *   Programa: …\ejecutar_gastos_cobranza_agente_diario.bat · Diario 10:00 (hora del SO)
 *
 * Manual:
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\ejecutar_gastos_cobranza_agente_diario.php
 *
 * Opciones:
 *   --dry-run   Solo comprueba GET /health; no llama POST /run.
 * =============================================================================
 */

declare(strict_types=1);

date_default_timezone_set('America/Mexico_City');

$argv = $argv ?? [];
$dryRun = in_array('--dry-run', $argv, true);

$root = dirname(__DIR__);
$configFile = $root . '/config/config.ini';

if (!is_file($configFile)) {
    fwrite(STDERR, "[gastos-cobranza-cron] ERROR: no existe config.ini en {$configFile}\n");
    exit(1);
}

$parsed = @parse_ini_file($configFile, true);
$cfg = (is_array($parsed) && isset($parsed['gastoscobranza_agent']) && is_array($parsed['gastoscobranza_agent']))
    ? $parsed['gastoscobranza_agent']
    : [];

$enabledRaw = isset($cfg['enabled']) ? (string) $cfg['enabled'] : '0';
$enabled = in_array(strtolower(trim($enabledRaw)), ['1', 'true', 'yes', 'on'], true);

if (!$enabled) {
    echo "[gastos-cobranza-cron] INFO: [gastoscobranza_agent] enabled=0 — no se ejecuta nada (salida 0).\n";
    exit(0);
}

$baseUrl = rtrim(trim((string) ($cfg['url'] ?? 'http://127.0.0.1:3120')), '/');
$apiKey = trim((string) ($cfg['key'] ?? ''));

$timeoutHealth = 20;
$timeoutRun = 7200;

if (!function_exists('curl_init')) {
    fwrite(STDERR, "[gastos-cobranza-cron] ERROR: PHP sin extensión curl.\n");
    exit(1);
}

/**
 * @return array{ok: bool, status: int, body: string, json: ?array}
 */
function gastos_cobranza_agent_curl(
    string $method,
    string $url,
    ?string $jsonBody,
    string $apiKey,
    int $timeoutSec
): array {
    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'status' => 0, 'body' => '', 'json' => null];
    }
    $headers = ['Accept: application/json'];
    if ($apiKey !== '') {
        $headers[] = 'X-Api-Key: ' . $apiKey;
    }
    if ($jsonBody !== null) {
        $headers[] = 'Content-Type: application/json';
    }
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(15, $timeoutSec));
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSec);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($jsonBody !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
    }
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($raw === false) {
        return ['ok' => false, 'status' => 0, 'body' => $err ?: 'curl_exec falló', 'json' => null];
    }
    $j = json_decode($raw, true);

    return [
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'body' => (string) $raw,
        'json' => is_array($j) ? $j : null,
    ];
}

$ts = date('Y-m-d H:i:s T');
echo "[gastos-cobranza-cron] {$ts} baseUrl={$baseUrl}\n";

$healthUrl = $baseUrl . '/health';
$h = gastos_cobranza_agent_curl('GET', $healthUrl, null, $apiKey, $timeoutHealth);

if (!$h['ok'] || !is_array($h['json']) || empty($h['json']['success'])) {
    fwrite(STDERR, "[gastos-cobranza-cron] ERROR: /health no OK (HTTP {$h['status']}). " . ($h['body'] !== '' ? substr($h['body'], 0, 500) : '') . "\n");
    exit(1);
}

echo "[gastos-cobranza-cron] /health OK.\n";

if ($dryRun) {
    echo "[gastos-cobranza-cron] --dry-run: no se invoca POST /run.\n";
    exit(0);
}

$runUrl = $baseUrl . '/run';
echo "[gastos-cobranza-cron] Iniciando POST /run (timeout {$timeoutRun}s)…\n";

$r = gastos_cobranza_agent_curl('POST', $runUrl, '{}', $apiKey, $timeoutRun);

$j = $r['json'];
$exitCode = 0;
if (!$r['ok']) {
    $exitCode = 1;
    fwrite(STDERR, "[gastos-cobranza-cron] ERROR: HTTP {$r['status']}\n");
} elseif (is_array($j) && array_key_exists('success', $j) && !$j['success']) {
    $exitCode = 1;
    fwrite(STDERR, "[gastos-cobranza-cron] ERROR: agente respondió success=false\n");
}

if (is_array($j)) {
    $codigo = $j['codigo_salida'] ?? $j['code'] ?? null;
    $msg = $j['mensaje'] ?? $j['message'] ?? '';
    echo '[gastos-cobranza-cron] Respuesta JSON: ' . json_encode($j, JSON_UNESCAPED_UNICODE) . "\n";
    if ($codigo !== null) {
        echo "[gastos-cobranza-cron] codigo_salida={$codigo}\n";
    }
    if ($msg !== '') {
        echo '[gastos-cobranza-cron] mensaje: ' . $msg . "\n";
    }
} else {
    echo '[gastos-cobranza-cron] Cuerpo (no JSON): ' . substr($r['body'], 0, 2000) . "\n";
    if (!$r['ok']) {
        $exitCode = 1;
    }
}

exit($exitCode);
