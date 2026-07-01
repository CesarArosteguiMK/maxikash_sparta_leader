<?php
/**
 * CRONJOB: Migrar gastos de cobranza de despachos
 * Ubicación: /cronjobs/eliminar_gastos_despachos.php
 *
 * Migra los registros pendientes de gastos_cobranza (no pagados, no condonados)
 * de créditos con asignación activa a despacho externo hacia la tabla
 * gastos_cobranza_despachos. Los registros NO se eliminan de la tabla origen.
 *
 * Uso:
 *   - Ejecución manual:                php eliminar_gastos_despachos.php
 *   - Modo prueba (sin modificar DB):  php eliminar_gastos_despachos.php --dry-run
 *   - Forzar ejecución cualquier día:  php eliminar_gastos_despachos.php --force
 *   - Sin enviar a webhook:            php eliminar_gastos_despachos.php --no-webhook
 *   - Verbose (muestra detalles):      php eliminar_gastos_despachos.php --verbose
 *
 * Programación recomendada crontab (martes, 7:40 AM — antes del insertar_moras_martes):
 *   40 7 * * 2 cd /ruta/cronjobs && php eliminar_gastos_despachos.php >> /var/log/gastos_despacho.log 2>&1
 *
 * EJECUCIÓN EN MODO DE PRUEBAS (DRY-RUN):
 *   & "C:\xampp\php\php.exe" ".\backend\cronjobs\eliminar_gastos_despachos.php" --dry-run
 *
 * EJECUCIÓN FORZADA (FORCE):
 *   & "C:\xampp\php\php.exe" ".\backend\cronjobs\eliminar_gastos_despachos.php" --force
 */

// ============================================
// 1. BOOTSTRAP DEL PROYECTO
// ============================================

date_default_timezone_set('America/Mexico_City');

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/../vendor/autoload.php';
require_once $projectRoot . '/core/DatabaseSegundometro.php';

if (!defined('RAIZ')) {
    define('RAIZ', $projectRoot);
}

// ============================================
// 2. CONFIGURACIÓN DEL CRONJOB
// ============================================

$configFile = $projectRoot . '/config/config.ini';
if (!file_exists($configFile)) {
    die("ERROR: No se encontró el archivo de configuración: $configFile\n");
}

$config = parse_ini_file($configFile, true);
$GOOGLE_CHAT_WEBHOOK = $config['webhook']['GOOGLE_CHAT'] ?? '';

$args      = getopt('', ['dry-run', 'force', 'verbose', 'no-webhook']);
$dryRun    = isset($args['dry-run']);
$force     = isset($args['force']);
$verbose   = isset($args['verbose']);
$noWebhook = isset($args['no-webhook']);

// ============================================
// 3. CLASE LOGGER CON GOOGLE CHAT WEBHOOK
// ============================================

class CronLogger
{
    private $webhookUrl;
    private $verbose;
    private $startTime;
    private $mensajesPendientes = [];
    private $webhookEnabled;
    private $logFile;

    public function __construct($webhookUrl, $verbose = false, $noWebhook = false)
    {
        $this->webhookUrl     = $webhookUrl;
        $this->verbose        = $verbose;
        $this->startTime      = microtime(true);
        $this->webhookEnabled = !$noWebhook && !empty($webhookUrl);

        $this->logFile = null;
        if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') === '1') {
            $logDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___cron_logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $this->logFile = $logDir . DIRECTORY_SEPARATOR . 'gastos_despacho_' . date('Y-m-d') . '.log';
        }
    }

    public function log($mensaje, $nivel = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $linea     = "[$timestamp] [$nivel] $mensaje";

        echo $linea . "\n";
        if ($this->logFile) {
            file_put_contents($this->logFile, $linea . "\n", FILE_APPEND);
        }

        if ($this->webhookEnabled && in_array($nivel, ['SUCCESS', 'WARNING', 'ERROR'])) {
            $this->mensajesPendientes[] = [
                'nivel'     => $nivel,
                'mensaje'   => $mensaje,
                'timestamp' => $timestamp
            ];
        }
    }

    public function info($mensaje)    { $this->log($mensaje, 'INFO'); }
    public function success($mensaje) { $this->log($mensaje, 'SUCCESS'); }
    public function warning($mensaje) { $this->log($mensaje, 'WARNING'); }
    public function error($mensaje)   { $this->log($mensaje, 'ERROR'); }

    public function debug($mensaje)
    {
        if ($this->verbose) {
            $this->log($mensaje, 'DEBUG');
        }
    }

    public function separator()
    {
        echo str_repeat('=', 80) . "\n";
    }

    public function getElapsedTime()
    {
        return round(microtime(true) - $this->startTime, 2);
    }

    public function enviarWebhookInmediato($titulo, $mensaje, $tipo = 'INFO')
    {
        if (!$this->webhookEnabled) {
            return;
        }

        $emoji = $this->getEmoji($tipo);
        $color = $this->getColor($tipo);

        $payload = [
            'cards' => [[
                'header' => [
                    'title'    => "$emoji $titulo",
                    'subtitle' => date('Y-m-d H:i:s')
                ],
                'sections' => [[
                    'widgets' => [[
                        'textParagraph' => [
                            'text' => "<font color=\"$color\"><b>$tipo:</b></font> $mensaje"
                        ]
                    ]]
                ]]
            ]]
        ];

        $this->enviarWebhook($payload);
    }

    public function enviarProgreso($eliminados, $omitidos, $errores, $total)
    {
        if (!$this->webhookEnabled) {
            return;
        }

        $procesados = $eliminados + $omitidos + $errores;
        $progreso   = $total > 0 ? round(($procesados / $total) * 100, 1) : 0;

        $payload = [
            'text' => "� Progreso: *{$progreso}%* | Migrados: {$eliminados} | Omitidos: {$omitidos} | Errores: {$errores}"
        ];

        $this->enviarWebhook($payload);
    }

    public function enviarResumen($titulo, $datos, $exitoso = true)
    {
        if (!$this->webhookEnabled) {
            return;
        }

        $emoji   = $exitoso ? '✅' : '❌';
        $widgets = [];

        foreach ($datos as $key => $value) {
            $widgets[] = [
                'keyValue' => [
                    'topLabel' => $key,
                    'content'  => (string)$value
                ]
            ];
        }

        if (!empty($this->mensajesPendientes)) {
            $texto = "*Eventos importantes:*\n";
            foreach (array_slice($this->mensajesPendientes, -10) as $msg) {
                $e = $this->getEmoji($msg['nivel']);
                $texto .= "• $e {$msg['mensaje']}\n";
            }
            $widgets[] = ['textParagraph' => ['text' => $texto]];
        }

        $payload = [
            'cards' => [[
                'header' => [
                    'title'    => "$emoji $titulo",
                    'subtitle' => 'Cronjob Gastos Despachos - ' . date('Y-m-d H:i:s')
                ],
                'sections' => [['widgets' => $widgets]]
            ]]
        ];

        $this->enviarWebhook($payload);
        $this->mensajesPendientes = [];
    }

    private function enviarWebhook($payload)
    {
        try {
            $ch = curl_init($this->webhookUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                echo "[WARNING] Error al enviar webhook: HTTP $httpCode\n";
            }
        } catch (\Exception $e) {
            echo "[WARNING] Excepción al enviar webhook: " . $e->getMessage() . "\n";
        }
    }

    private function getEmoji($tipo)
    {
        $emojis = [
            'SUCCESS' => '✅',
            'ERROR'   => '❌',
            'WARNING' => '⚠️',
            'INFO'    => 'ℹ️',
            'DEBUG'   => '🔍'
        ];
        return $emojis[$tipo] ?? 'ℹ️';
    }

    private function getColor($tipo)
    {
        $colores = [
            'SUCCESS' => '#00FF00',
            'ERROR'   => '#FF0000',
            'WARNING' => '#FFA500',
            'INFO'    => '#0000FF',
            'DEBUG'   => '#888888'
        ];
        return $colores[$tipo] ?? '#000000';
    }
}

// ============================================
// 4. CLASE PRINCIPAL DEL CRONJOB
// ============================================

class CronMigrarGastosDespachos
{
    private $logger;
    private $db;
    private $dryRun;
    private $force;

    public function __construct(CronLogger $logger, $dryRun = false, $force = false)
    {
        $this->logger = $logger;
        $this->dryRun = $dryRun;
        $this->force  = $force;

        try {
            $this->db = new \Core\DatabaseSegundometro();
            $this->logger->success("Conexión a base de datos establecida exitosamente");
        } catch (\Exception $e) {
            $this->logger->error("Error al conectar a la base de datos: " . $e->getMessage());
            throw $e;
        }
    }

    public function ejecutar()
    {
        try {
            $this->logger->separator();
            $this->logger->info("INICIANDO PROCESO: MIGRAR GASTOS COBRANZA A TABLA DESPACHOS");
            $this->logger->info("Modo: " . ($this->dryRun ? "DRY-RUN (Simulación)" : "PRODUCCIÓN"));
            $this->logger->separator();

            $this->logger->enviarWebhookInmediato(
                "Cronjob Iniciado",
                "Migración de gastos de cobranza → gastos_cobranza_despachos en modo " . ($this->dryRun ? "simulación" : "producción"),
                "INFO"
            );

            // Validar día de la semana (martes)
            if (!$this->validarDiaSemana()) {
                return false;
            }

            // Paso 1: Consultar créditos asignados a despacho activo
            $idsCredito = $this->consultarCreditosDespachoActivo();

            if (empty($idsCredito)) {
                $this->logger->warning("No se encontraron créditos con despacho activo. Proceso finalizado.");
                return true;
            }

            // Paso 2: Asegurar que la tabla destino existe
            $this->asegurarTablaDespachos();

            // Paso 3: Migrar gastos pendientes a gastos_cobranza_despachos
            $resultado = $this->migrarGastosPendientes($idsCredito);

            // Resumen final
            $this->mostrarResumen($resultado);

            $this->logger->enviarResumen(
                "Proceso Completado Exitosamente",
                [
                    'Créditos con despacho activo'       => $resultado['total_creditos'],
                    'Gastos migrados'                    => $resultado['migrados'],
                    'Duplicados omitidos (ya existían)'  => $resultado['duplicados'],
                    'Gastos omitidos (ya pagados)'       => $resultado['omitidos_pagados'],
                    'Gastos omitidos (condonados)'       => $resultado['omitidos_condonados'],
                    'Errores'                            => $resultado['errores'],
                    'Tiempo de ejecución'                => $this->logger->getElapsedTime() . 's'
                ],
                true
            );

            $this->logger->separator();
            $this->logger->success("PROCESO COMPLETADO EXITOSAMENTE");
            $this->logger->info("Tiempo de ejecución: " . $this->logger->getElapsedTime() . " segundos");
            $this->logger->separator();

            return true;

        } catch (\Exception $e) {
            $this->logger->error("ERROR CRÍTICO: " . $e->getMessage());
            $this->logger->error("Stack trace: " . $e->getTraceAsString());

            try {
                $this->db->rollback();
                $this->logger->warning("ROLLBACK de seguridad ejecutado — Todos los cambios fueron revertidos");
            } catch (\Exception $rollbackError) {
                $this->logger->info("ROLLBACK de seguridad: " . $rollbackError->getMessage() . " (ya se ejecutó anteriormente)");
            }

            $this->logger->enviarWebhookInmediato(
                "ERROR CRÍTICO en Cronjob",
                $e->getMessage(),
                "ERROR"
            );

            return false;
        }
    }

    private function validarDiaSemana()
    {
        $diaSemana = date('N'); // 1=Lunes … 7=Domingo
        $nombreDia = date('l');

        $this->logger->info("Día actual: $nombreDia (código: $diaSemana)");

        if ($diaSemana != 2) {
            if ($this->force) {
                $this->logger->warning("Hoy NO es martes, pero el modo FORCE está activo.");
                $this->logger->info("Continuando ejecución forzada...");
                return true;
            }
            $this->logger->warning("Hoy NO es martes. El proceso debe ejecutarse los martes.");
            $this->logger->info("El script se detendrá automáticamente. Use --force para omitir esta validación.");
            return false;
        }

        $this->logger->success("Validación de día correcta: Es martes");
        return true;
    }

    /**
     * Obtiene todos los id_credito con asignación activa a despacho externo.
     *
     * @return int[]
     */
    private function consultarCreditosDespachoActivo(): array
    {
        $this->logger->info("PASO 1: Consultando créditos con despacho activo (estatus = '1')");

        $sql = "SELECT DISTINCT id_credito
                FROM __SPARTA_SECRET_REDACTED__.asigna_creditos_despacho
                WHERE estatus = '1'";

        try {
            $rows  = $this->db->queryAll($sql);
            $ids   = [];
            foreach ($rows as $row) {
                $id = (int)($row['id_credito'] ?? 0);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }

            $total = count($ids);
            $this->logger->success("Créditos con despacho activo encontrados: $total");

            if ($total > 0) {
                $this->logger->debug("Primeros 5 IDs (muestra): " . implode(', ', array_slice($ids, 0, 5)));
            }

            return $ids;

        } catch (\Exception $e) {
            $this->logger->error("Error al consultar créditos con despacho activo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crea la tabla gastos_cobranza_despachos si no existe, con la misma
     * estructura que gastos_cobranza. En dry-run no hace nada.
     */
    private function asegurarTablaDespachos(): void
    {
        if ($this->dryRun) {
            $this->logger->info("PASO 2: DRY-RUN — verificación de tabla destino omitida");
            return;
        }

        $this->logger->info("PASO 2: Verificando tabla destino gastos_cobranza_despachos");

        $sql = "CREATE TABLE IF NOT EXISTS gastos_cobranza_despachos LIKE gastos_cobranza";
        $this->db->CRUD($sql, []);

        $this->logger->success("Tabla gastos_cobranza_despachos lista");
    }

    /**
     * Migra los gastos_cobranza pendientes (no pagados, no condonados)
     * de los créditos recibidos a gastos_cobranza_despachos, en lotes.
     *
     * Criterio de migración (registros que se copian):
     *   - condonado = 0 / NULL  (no condonado)
     *   - estatus_pago = 0 / NULL (pendiente)
     *
     * Se omiten:
     *   - estatus_pago IN (1, 2) — abonados o pagados
     *   - condonado = 1 — condonados
     *
     * Usa INSERT IGNORE para no fallar si el registro ya existe en destino.
     * Los registros de gastos_cobranza NO se eliminan.
     *
     * @param int[] $idsCredito
     * @return array{total_creditos:int, migrados:int, duplicados:int, omitidos_pagados:int, omitidos_condonados:int, errores:int, errores_detalle:string[]}
     */
    private function migrarGastosPendientes(array $idsCredito): array
    {
        $this->logger->info("PASO 3: Migrando gastos_cobranza pendientes → gastos_cobranza_despachos (MODO LOTES)");

        $totalCreditos      = count($idsCredito);
        $migrados           = 0;
        $duplicados         = 0;
        $omitidosPagados    = 0;
        $omitidosCondonados = 0;
        $errores            = 0;
        $erroresDetalle     = [];

        $tamañoLote = 500;
        $lotes      = array_chunk($idsCredito, $tamañoLote);
        $totalLotes = count($lotes);

        $this->logger->info("Procesando {$totalCreditos} créditos en {$totalLotes} lotes de {$tamañoLote}");

        if ($this->dryRun) {
            $this->logger->warning("DRY-RUN: No se migrarán registros");
            return $this->simularMigracion($idsCredito, $lotes);
        }

        // Iniciar transacción global
        $this->logger->info("Iniciando transacción de base de datos...");
        $this->db->beginTransaction();

        $mitadEnviada = false;

        foreach ($lotes as $numeroLote => $lote) {
            try {
                $marcadores = [];
                $params     = [];
                foreach ($lote as $idx => $id) {
                    $key          = "id_$idx";
                    $marcadores[] = ":$key";
                    $params[$key] = $id;
                }
                $inList = implode(',', $marcadores);

                // ── Contar omitidos (pagados/condonados) para el log ─────────────────────
                $sqlContarOmitidos = "SELECT
                    SUM(CASE WHEN condonado = 1 THEN 1 ELSE 0 END)                            AS total_condonados,
                    SUM(CASE WHEN (estatus_pago = 1 OR estatus_pago = 2)
                              AND (condonado IS NULL OR condonado = 0) THEN 1 ELSE 0 END)     AS total_pagados
                FROM gastos_cobranza
                WHERE Id_credito IN ($inList)";

                $conteo = $this->db->queryAll($sqlContarOmitidos, $params);
                if (!empty($conteo[0])) {
                    $omitidosCondonados += (int)($conteo[0]['total_condonados'] ?? 0);
                    $omitidosPagados    += (int)($conteo[0]['total_pagados']    ?? 0);
                }

                // ── Contar cuántos se van a migrar ───────────────────────────────────────
                $sqlContar = "SELECT COUNT(*) AS total
                              FROM gastos_cobranza
                              WHERE Id_credito IN ($inList)
                                AND (condonado IS NULL OR condonado = 0)
                                AND (estatus_pago IS NULL OR estatus_pago = 0)";

                $filaContar   = $this->db->queryAll($sqlContar, $params);
                $gastosEnLote = (int)($filaContar[0]['total'] ?? 0);

                if ($gastosEnLote === 0) {
                    $this->logger->debug("Lote " . ($numeroLote + 1) . ": sin gastos pendientes que migrar");
                    continue;
                }

                // ── INSERT IGNORE → gastos_cobranza_despachos ────────────────────────────
                // INSERT IGNORE descarta silenciosamente duplicados por clave primaria
                $sqlInsert = "INSERT IGNORE INTO gastos_cobranza_despachos
                              SELECT * FROM gastos_cobranza
                              WHERE Id_credito IN ($inList)
                                AND (condonado IS NULL OR condonado = 0)
                                AND (estatus_pago IS NULL OR estatus_pago = 0)";

                $insertadosLote = $this->db->CRUD($sqlInsert, $params);

                // Filas afectadas < gastosEnLote → hubo duplicados ignorados
                $duplicadosLote = $gastosEnLote - $insertadosLote;
                $migrados       += $insertadosLote;
                $duplicados     += max(0, $duplicadosLote);

                // Progreso
                $progreso = round((($numeroLote + 1) / $totalLotes) * 100, 1);
                $this->logger->info(sprintf(
                    "Lote %d/%d completado — Progreso: %s%% (%d migrados, %d duplicados, %d omit. pagados, %d omit. condonados)",
                    $numeroLote + 1,
                    $totalLotes,
                    $progreso,
                    $migrados,
                    $duplicados,
                    $omitidosPagados,
                    $omitidosCondonados
                ));

                if (!$mitadEnviada && $progreso >= 50.0) {
                    $this->logger->enviarProgreso($migrados, $omitidosPagados + $omitidosCondonados + $duplicados, $errores, $totalCreditos);
                    $mitadEnviada = true;
                }

            } catch (\Exception $e) {
                $errores    += count($lote);
                $errorMsg    = "Error en lote " . ($numeroLote + 1) . ": " . $e->getMessage();
                $this->logger->error($errorMsg);
                $erroresDetalle[] = $errorMsg;

                if ($errores > 1000) {
                    $this->logger->error("DEMASIADOS ERRORES ($errores). Haciendo ROLLBACK...");
                    $this->db->rollback();
                    $this->logger->error("ROLLBACK completado. Ningún cambio fue guardado en la DB.");
                    throw new \Exception("Proceso abortado por exceso de errores. Rollback ejecutado.");
                }
            }
        }

        // COMMIT
        try {
            $this->db->commit();
            $this->logger->success("Transacción COMMIT exitoso — Cambios guardados permanentemente");
        } catch (\Exception $e) {
            $this->logger->error("Error al hacer COMMIT: " . $e->getMessage());
            $this->db->rollback();
            $this->logger->error("ROLLBACK ejecutado por error en COMMIT.");
            throw $e;
        }

        return [
            'total_creditos'      => $totalCreditos,
            'migrados'            => $migrados,
            'duplicados'          => $duplicados,
            'omitidos_pagados'    => $omitidosPagados,
            'omitidos_condonados' => $omitidosCondonados,
            'errores'             => $errores,
            'errores_detalle'     => $erroresDetalle,
        ];
    }

    /**
     * Simula la migración (dry-run): solo cuenta, no modifica la BD.
     *
     * @param int[]   $idsCredito
     * @param array[] $lotes
     * @return array
     */
    private function simularMigracion(array $idsCredito, array $lotes): array
    {
        $totalCreditos      = count($idsCredito);
        $migrarían          = 0;
        $omitidosPagados    = 0;
        $omitidosCondonados = 0;

        foreach ($lotes as $numeroLote => $lote) {
            $marcadores = [];
            $params     = [];
            foreach ($lote as $idx => $id) {
                $key          = "id_$idx";
                $marcadores[] = ":$key";
                $params[$key] = $id;
            }
            $inList = implode(',', $marcadores);

            try {
                $sqlContar = "SELECT
                    SUM(CASE WHEN (condonado IS NULL OR condonado = 0)
                              AND (estatus_pago IS NULL OR estatus_pago = 0) THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN condonado = 1 THEN 1 ELSE 0 END)                              AS condonados,
                    SUM(CASE WHEN (estatus_pago = 1 OR estatus_pago = 2)
                              AND (condonado IS NULL OR condonado = 0) THEN 1 ELSE 0 END)       AS pagados
                FROM gastos_cobranza
                WHERE Id_credito IN ($inList)";

                $fila = $this->db->queryAll($sqlContar, $params);
                if (!empty($fila[0])) {
                    $migrarían          += (int)($fila[0]['pendientes'] ?? 0);
                    $omitidosCondonados += (int)($fila[0]['condonados'] ?? 0);
                    $omitidosPagados    += (int)($fila[0]['pagados']    ?? 0);
                }
            } catch (\Exception $e) {
                $this->logger->warning("DRY-RUN: Error al consultar lote " . ($numeroLote + 1) . ": " . $e->getMessage());
            }
        }

        $this->logger->info("DRY-RUN: Se migrarían $migrarían gastos pendientes → gastos_cobranza_despachos");
        $this->logger->info("DRY-RUN: Se omitirían $omitidosPagados gastos ya pagados/abonados");
        $this->logger->info("DRY-RUN: Se omitirían $omitidosCondonados gastos condonados");

        return [
            'total_creditos'      => $totalCreditos,
            'migrados'            => 0,
            'duplicados'          => 0,
            'omitidos_pagados'    => $omitidosPagados,
            'omitidos_condonados' => $omitidosCondonados,
            'errores'             => 0,
            'errores_detalle'     => [],
            '_dry_migrarian'      => $migrarían,
        ];
    }

    private function mostrarResumen(array $resultado)
    {
        $this->logger->separator();
        $this->logger->info("RESUMEN DE MIGRACIÓN");
        $this->logger->separator();
        $this->logger->info("Créditos con despacho activo:       " . $resultado['total_creditos']);

        if ($this->dryRun) {
            $this->logger->info("Gastos que se migrarían:            " . ($resultado['_dry_migrarian'] ?? 0) . " (DRY-RUN)");
        } else {
            $this->logger->info("Gastos migrados → despachos:        " . $resultado['migrados']);
            $this->logger->info("Duplicados ignorados (ya existían): " . ($resultado['duplicados'] ?? 0));
        }

        $this->logger->info("Omitidos (ya pagados/abonados):     " . $resultado['omitidos_pagados']);
        $this->logger->info("Omitidos (condonados):              " . $resultado['omitidos_condonados']);
        $this->logger->info("Errores:                            " . $resultado['errores']);

        if ($resultado['errores'] > 0 && !empty($resultado['errores_detalle'])) {
            $this->logger->warning("Primeros 5 errores:");
            foreach (array_slice($resultado['errores_detalle'], 0, 5) as $error) {
                $this->logger->warning("  - $error");
            }
        }
    }
}

// ============================================
// 5. EJECUCIÓN PRINCIPAL
// ============================================

try {
    $logger = new CronLogger($GOOGLE_CHAT_WEBHOOK, $verbose, $noWebhook);

    $logger->separator();
    $logger->info("CRONJOB: MIGRAR GASTOS COBRANZA A TABLA DESPACHOS");
    $logger->info("Fecha/Hora: " . date('Y-m-d H:i:s'));

    if ($dryRun) {
        $logger->warning("MODO DRY-RUN ACTIVADO — No se modificará la base de datos");
    }

    if ($force) {
        $logger->warning("MODO FORCE ACTIVADO — Se ejecutará sin importar el día");
    }

    if ($noWebhook || empty($GOOGLE_CHAT_WEBHOOK)) {
        $logger->warning("WEBHOOK DESACTIVADO — No se enviarán notificaciones a Google Chat");
    } else {
        $logger->success("WEBHOOK ACTIVADO — Se enviarán notificaciones a Google Chat");
    }

    $logger->separator();

    $cron    = new CronMigrarGastosDespachos($logger, $dryRun, $force);
    $exitoso = $cron->ejecutar();

    exit($exitoso ? 0 : 1);

} catch (\Exception $e) {
    if (isset($logger)) {
        $logger->error("ERROR FATAL NO CAPTURADO: " . $e->getMessage());
        $logger->error("Stack trace: " . $e->getTraceAsString());

        $logger->enviarWebhookInmediato(
            "ERROR FATAL en Cronjob",
            "Error no capturado: " . $e->getMessage(),
            "ERROR"
        );
    } else {
        echo "ERROR FATAL: " . $e->getMessage() . "\n";
    }
    exit(1);
}
