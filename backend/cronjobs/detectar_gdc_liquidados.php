<?php
/**
 * ============================================================
 * SCRIPT: detectar_gdc_liquidados.php
 * ============================================================
 * Ubicación: /sparta___SPARTA_SECRET_REDACTED__/backend/cronjobs/detectar_gdc_liquidados.php
 *
 * PROPÓSITO:
 *   Detecta qué créditos de la tabla gastos_cobranza (__SPARTA_SECRET_REDACTED__)
 *   ya están LIQUIDADOS, comparando contra tbl_segundometro_semana.
 *
 *   CRITERIO:
 *     Un crédito está liquidado si su Id_credito aparece en gastos_cobranza
 *     pero NO aparece en tbl_segundometro_semana (ya salió de la cartera activa).
 *
 *   FILTRO DE ALCANCE:
 *     Solo se consideran registros con estatus_pago IN (0, 1):
 *       0 = PENDIENTE, 1 = PAGO PARCIAL.
 *     Se excluyen: estatus_pago = 2 (PAGADO) y condonado = 1 (condonación total).
 *
 * FASE ACTUAL: Detección + INSERT INTO gastos_cobranza_liquidados.
 *   Usa INSERT IGNORE para ser idempotente (re-ejecuciones seguras).
 *   El --dry-run permite simular sin escribir en BD.
 *
 * ── Ejecución normal (detecta e inserta) ────────────────────
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\detectar_gdc_liquidados.php
 *
 * ── Solo conteo rápido ──────────────────────────────────────
 *   ... -- --solo-conteo
 *
 * ── Verbose (muestra cada Id_credito detectado) ─────────────
 *   ... -- --verbose
 *
 * ── Sin enviar a webhook ────────────────────────────────────
 *   ... -- --no-webhook
 *
 * ── Dry run (solo lectura, sin INSERT) ──────────────────────
 *   c:\xampp\php\php.exe -f C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\detectar_gdc_liquidados.php -- --dry-run --no-webhook
 * ============================================================
 */

declare(strict_types=1);

date_default_timezone_set('America/Mexico_City');

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/../vendor/autoload.php';
require_once $projectRoot . '/core/DatabaseSegundometro.php';

if (!defined('RAIZ')) {
    define('RAIZ', $projectRoot);
}

// ============================================
// ARGUMENTOS
// ============================================

$args       = getopt('', ['solo-conteo', 'verbose', 'no-webhook', 'dry-run']);
$soloConteo = isset($args['solo-conteo']);
$verbose    = isset($args['verbose']);
$noWebhook  = isset($args['no-webhook']);
$dryRun     = isset($args['dry-run']);

// ============================================
// WEBHOOK (opcional)
// ============================================

$configFile = $projectRoot . '/config/config.ini';
$WEBHOOK_URL = '';
if (file_exists($configFile)) {
    $config      = parse_ini_file($configFile, true);
    $WEBHOOK_URL = $config['webhook']['GOOGLE_CHAT'] ?? '';
}

function enviarWebhook(string $url, string $mensaje): void
{
    if (empty($url)) {
        return;
    }
    $payload = json_encode(['text' => $mensaje], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function log_msg(string $nivel, string $msg): void
{
    $ts = date('Y-m-d H:i:s');
    echo "[$ts] [$nivel] $msg\n";
}

// ============================================
// CONEXIÓN
// ============================================

try {
    $db = new \Core\DatabaseSegundometro();
} catch (\Throwable $e) {
    log_msg('ERROR', 'No se pudo conectar a __SPARTA_SECRET_REDACTED__: ' . $e->getMessage());
    exit(1);
}

echo str_repeat('=', 70) . "\n";
log_msg('INFO', 'Inicio detección GDC liquidados' . ($dryRun ? ' [DRY RUN — sin escrituras]' : ''));
echo str_repeat('=', 70) . "\n";

// ============================================
// QUERY 1: CONTEO DE CRÉDITOS LIQUIDADOS
// ============================================
//
// Lógica: Id_credito está en gastos_cobranza pero NOT IN tbl_segundometro_semana.
// LEFT JOIN + IS NULL es equivalente a NOT IN / NOT EXISTS pero más eficiente en MySQL.
//
$sqlConteo = "
    SELECT COUNT(DISTINCT gdc.Id_credito) AS total_liquidados
    FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza gdc
    LEFT JOIN `__SPARTA_SECRET_REDACTED__`.tbl_segundometro_semana s2
        ON s2.Id_credito = gdc.Id_credito
    WHERE s2.Id_credito IS NULL
      AND COALESCE(gdc.condonado, 0) = 0
      AND COALESCE(gdc.estatus_pago, 0) IN (0, 1)
";

try {
    $rowConteo       = $db->queryOne($sqlConteo);
    $totalLiquidados = (int) ($rowConteo['total_liquidados'] ?? 0);
    log_msg('INFO', "Créditos liquidados detectados: {$totalLiquidados}");
} catch (\Throwable $e) {
    log_msg('ERROR', 'Error al ejecutar QUERY 1 (conteo): ' . $e->getMessage());
    exit(1);
}

if ($soloConteo) {
    log_msg('INFO', 'Modo --solo-conteo: fin del script.');
    exit(0);
}

// ============================================
// QUERY 2: LISTA DE Id_credito LIQUIDADOS
// ============================================
//
// Devuelve los Id_credito distintos que están en gastos_cobranza
// pero ya no aparecen en tbl_segundometro_semana.
//
$sqlLista = "
    SELECT DISTINCT gdc.Id_credito
    FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza gdc
    LEFT JOIN `__SPARTA_SECRET_REDACTED__`.tbl_segundometro_semana s2
        ON s2.Id_credito = gdc.Id_credito
    WHERE s2.Id_credito IS NULL
      AND COALESCE(gdc.condonado, 0) = 0
      AND COALESCE(gdc.estatus_pago, 0) IN (0, 1)
    ORDER BY gdc.Id_credito
";

try {
    $creditosLiquidados = $db->queryAll($sqlLista);
    log_msg('INFO', 'Lista de Id_credito obtenida: ' . count($creditosLiquidados) . ' registros.');

    if ($verbose) {
        foreach ($creditosLiquidados as $row) {
            log_msg('DEBUG', '  Id_credito liquidado: ' . $row['Id_credito']);
        }
    }
} catch (\Throwable $e) {
    log_msg('ERROR', 'Error al ejecutar QUERY 2 (lista): ' . $e->getMessage());
    exit(1);
}

// ============================================
// QUERY 3: DETALLE COMPLETO DE REGISTROS GDC
// ============================================
//
// Trae los registros PENDIENTES (estatus_pago=0) y PAGO PARCIAL (estatus_pago=1)
// de gastos_cobranza para los créditos que ya no están en tbl_segundometro_semana.
// Excluye: condonado=1 (condonación total) y estatus_pago=2 (pagado completamente).
// Id_cliente, Nombre_cliente y Saldo_vencido_inicio existen en gastos_cobranza.
//
$sqlDetalle = "
    SELECT
        gdc.id_gastos_cobranza,
        gdc.Id_credito,
        gdc.Id_cliente,
        gdc.Nombre_cliente,
        gdc.Saldo_vencido_inicio,
        gdc.SEMANA,
        gdc.periodo_inicio,
        gdc.periodo_fin,
        gdc.monto_valor,
        gdc.cuota,
        gdc.parcialidad,
        gdc.Fecha_primer_vencimiento,
        COALESCE(gdc.condonado, 0)                 AS condonado,
        COALESCE(gdc.estatus_pago, 0)              AS estatus_pago,
        COALESCE(gdc.monto_parcial_pagado, 0)      AS monto_parcial_pagado,
        COALESCE(gdc.condonacion_parcial_monto, 0) AS condonacion_parcial_monto,
        gdc.condonacion_parcial_motivo,
        gdc.celula,
        gdc.fecha_condonacion,
        gdc.fecha_pago
    FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza gdc
    LEFT JOIN `__SPARTA_SECRET_REDACTED__`.tbl_segundometro_semana s2
        ON s2.Id_credito = gdc.Id_credito
    WHERE s2.Id_credito IS NULL
      AND COALESCE(gdc.condonado, 0) = 0
      AND COALESCE(gdc.estatus_pago, 0) IN (0, 1)
    ORDER BY gdc.Id_credito, gdc.periodo_inicio
";

try {
    $detalle = $db->queryAll($sqlDetalle);
    $totalFilas = count($detalle);
    log_msg('INFO', "Detalle GDC liquidados: {$totalFilas} filas (registros individuales de gastos_cobranza).");
} catch (\Throwable $e) {
    log_msg('ERROR', 'Error al ejecutar QUERY 3 (detalle): ' . $e->getMessage());
    exit(1);
}

// ============================================
// QUERY 4: MONTOS AGREGADOS POR ESTATUS
// ============================================
//
// monto_pendiente    : suma de monto_valor de filas con estatus_pago = 0 (PENDIENTE)
// monto_pago_parcial : suma de monto_valor de filas con estatus_pago = 1 (PAGO PARCIAL)
//
$sqlMontos = "
    SELECT
        SUM(CASE WHEN COALESCE(gdc.estatus_pago, 0) = 0 THEN gdc.monto_valor ELSE 0 END) AS monto_pendiente,
        SUM(CASE WHEN COALESCE(gdc.estatus_pago, 0) = 1 THEN gdc.monto_valor ELSE 0 END) AS monto_pago_parcial,
        SUM(CASE WHEN COALESCE(gdc.estatus_pago, 0) = 0 THEN 1 ELSE 0 END)               AS filas_pendiente,
        SUM(CASE WHEN COALESCE(gdc.estatus_pago, 0) = 1 THEN 1 ELSE 0 END)               AS filas_pago_parcial
    FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza gdc
    LEFT JOIN `__SPARTA_SECRET_REDACTED__`.tbl_segundometro_semana s2
        ON s2.Id_credito = gdc.Id_credito
    WHERE s2.Id_credito IS NULL
      AND COALESCE(gdc.condonado, 0) = 0
      AND COALESCE(gdc.estatus_pago, 0) IN (0, 1)
";

try {
    $rowMontos       = $db->queryOne($sqlMontos);
    $montoPendiente  = round((float) ($rowMontos['monto_pendiente']    ?? 0), 2);
    $montoParcial    = round((float) ($rowMontos['monto_pago_parcial'] ?? 0), 2);
    $filasPendiente  = (int) ($rowMontos['filas_pendiente']    ?? 0);
    $filasParcial    = (int) ($rowMontos['filas_pago_parcial'] ?? 0);
} catch (\Throwable $e) {
    log_msg('ERROR', 'Error al ejecutar QUERY 4 (montos): ' . $e->getMessage());
    exit(1);
}

// ============================================
// INSERT: MOVER REGISTROS A gastos_cobranza_liquidados
// ============================================
//
// INSERT IGNORE: si el id_gastos_cobranza ya existe en destino lo omite.
// Esto hace el script idempotente (re-ejecuciones seguras sin duplicados).
//
$filasInsertadas = 0;
$filasEliminadas = 0;

if ($dryRun) {
    log_msg('INFO', '[DRY RUN] Se omiten INSERT y DELETE.');
} else {
    $sqlInsert = "
        INSERT IGNORE INTO `__SPARTA_SECRET_REDACTED__`.gastos_cobranza_liquidados
            (id_gastos_cobranza, Id_credito, Id_cliente, Nombre_cliente,
             Saldo_vencido_inicio, SEMANA, periodo_inicio, periodo_fin,
             parcialidad, monto_valor, condonacion_parcial_monto,
             condonacion_parcial_motivo, monto_parcial_pagado,
             Fecha_primer_vencimiento, cuota, condonado, celula,
             estatus_pago, fecha_condonacion, fecha_pago, created_at)
        SELECT
            gdc.id_gastos_cobranza, gdc.Id_credito, gdc.Id_cliente, gdc.Nombre_cliente,
            gdc.Saldo_vencido_inicio, gdc.SEMANA, gdc.periodo_inicio, gdc.periodo_fin,
            gdc.parcialidad, gdc.monto_valor, gdc.condonacion_parcial_monto,
            gdc.condonacion_parcial_motivo, gdc.monto_parcial_pagado,
            gdc.Fecha_primer_vencimiento, gdc.cuota, gdc.condonado, gdc.celula,
            gdc.estatus_pago, gdc.fecha_condonacion, gdc.fecha_pago, gdc.created_at
        FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza gdc
        LEFT JOIN `__SPARTA_SECRET_REDACTED__`.tbl_segundometro_semana s2
            ON s2.Id_credito = gdc.Id_credito
        WHERE s2.Id_credito IS NULL
          AND COALESCE(gdc.condonado, 0) = 0
          AND COALESCE(gdc.estatus_pago, 0) IN (0, 1)
    ";

    $sqlDelete = "
        DELETE FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
        WHERE id_gastos_cobranza IN (
            SELECT id FROM (
                SELECT id_gastos_cobranza AS id
                FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza_liquidados
            ) AS tmp
        )
    ";

    try {
        $db->beginTransaction();
        log_msg('INFO', 'Transacción iniciada.');

        $filasInsertadas = $db->CRUD($sqlInsert);
        log_msg('INFO', "INSERT: {$filasInsertadas} filas copiadas a gastos_cobranza_liquidados.");

        if ($filasInsertadas === 0) {
            log_msg('INFO', 'Sin filas nuevas — DELETE omitido.');
        } else {
            $filasEliminadas = $db->CRUD($sqlDelete);
            log_msg('INFO', "DELETE: {$filasEliminadas} filas eliminadas de gastos_cobranza.");
        }

        $db->commit();
        log_msg('INFO', 'Transacción confirmada (COMMIT).');
    } catch (\Throwable $e) {
        $db->rollback();
        log_msg('ERROR', 'Error — se ejecutó ROLLBACK: ' . $e->getMessage());
        exit(1);
    }
}

// ============================================
// RESUMEN FINAL
// ============================================

echo str_repeat('-', 70) . "\n";
log_msg('INFO', "Resumen:");
log_msg('INFO', "  Créditos únicos liquidados : {$totalLiquidados}");
log_msg('INFO', "  Filas en gastos_cobranza   : {$totalFilas}");
log_msg('INFO', "  Filas insertadas en destino: " . ($dryRun ? '(dry run)' : $filasInsertadas));
log_msg('INFO', "  Filas eliminadas de origen  : " . ($dryRun ? '(dry run)' : $filasEliminadas));
echo str_repeat('-', 70) . "\n";
log_msg('INFO', "  Monto pendiente (estatus 0)      : $" . number_format($montoPendiente, 2) . "  [{$filasPendiente} filas]");
log_msg('INFO', "  Monto pago parcial (estatus 1)   : $" . number_format($montoParcial,   2) . "  [{$filasParcial} filas]");
echo str_repeat('=', 70) . "\n";
log_msg('INFO', $dryRun ? 'Dry run completado. Sin escrituras en BD.' : 'Proceso completado.');
echo str_repeat('=', 70) . "\n";

// ── Notificación webhook (solo si configurado) ────────────────
if (!$noWebhook && !empty($WEBHOOK_URL)) {
    $msg = "📋 *GDC Liquidados*" . ($dryRun ? " [DRY RUN]" : "") . "\n"
         . "Créditos únicos liquidados: *{$totalLiquidados}*\n"
         . "Filas en gastos_cobranza: *{$totalFilas}*\n"
         . "Filas insertadas en destino: *" . ($dryRun ? '(dry run)' : $filasInsertadas) . "*\n"
         . "Filas eliminadas de origen: *" . ($dryRun ? '(dry run)' : $filasEliminadas) . "*\n"
         . "Monto pendiente (estatus 0): *$" . number_format($montoPendiente, 2) . "* ({$filasPendiente} filas)\n"
         . "Monto pago parcial (estatus 1): *$" . number_format($montoParcial, 2) . "* ({$filasParcial} filas)\n"
         . "Fecha: " . date('Y-m-d H:i:s');
    enviarWebhook($WEBHOOK_URL, $msg);
}

exit(0);
