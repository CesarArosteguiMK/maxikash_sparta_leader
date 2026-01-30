<?php
/**
 * CRONJOB: Insertar créditos mora los martes a las 7:50 AM
 * Ubicación: /cronjobs/insertar_moras_martes.php
 * 
 * Uso:
 *   - Ejecución manual: php insertar_moras_martes.php
 *   - Modo prueba (sin modificar DB): php insertar_moras_martes.php --dry-run
 *   - Forzar ejecución cualquier día: php insertar_moras_martes.php --force
 *   - Sin enviar a webhook: php insertar_moras_martes.php --no-webhook
 * 
 * Programación recomendada crontab:
 *   50 7 * * 2 cd /ruta/cronjobs && php insertar_moras_martes.php >> /var/log/morosidad_console.log 2>&1
 * 
 * CONFIGURACIÓN WEBHOOK GOOGLE CHAT:
 *   
 *   Para obtener la URL del webhook:
 *   1. En Google Chat, ve al espacio donde quieres las notificaciones
 *   2. Click en el nombre del espacio → Administrar webhooks
 *   3. Click en "+ Agregar webhook"
 *   4. Nombre: "Cronjob Morosidad"
 *   5. Click en "Guardar" y copia la URL generada
 *   6. Pégala en config.ini (NUNCA en el código fuente)
 */

// ============================================
// 1. BOOTSTRAP DEL PROYECTO
// ============================================

$projectRoot = dirname(__DIR__);

// Cargar autoloader del proyecto
require_once $projectRoot . '/../vendor/autoload.php';

// Cargar clases del core manualmente
require_once $projectRoot . '/core/DatabaseSegundometro.php';

// Definir constantes necesarias si no están definidas
if (!defined('RAIZ')) {
    define('RAIZ', $projectRoot);
}

// ============================================
// 2. CONFIGURACIÓN DEL CRONJOB
// ============================================

// � CARGAR WEBHOOK DE FORMA SEGURA DESDE CONFIG.INI
$configFile = $projectRoot . '/config/config.ini';
if (!file_exists($configFile)) {
    die("ERROR: No se encontró el archivo de configuración: $configFile\n");
}

$config = parse_ini_file($configFile, true);
$GOOGLE_CHAT_WEBHOOK = $config['webhook']['GOOGLE_CHAT'] ?? '';

// Parsear argumentos de línea de comandos
$args = getopt('', ['dry-run', 'force', 'verbose', 'no-webhook']);
$dryRun = isset($args['dry-run']);
$force = isset($args['force']);
$verbose = isset($args['verbose']);
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

    public function __construct($webhookUrl, $verbose = false, $noWebhook = false)
    {
        $this->webhookUrl = $webhookUrl;
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
        $this->webhookEnabled = !$noWebhook && !empty($webhookUrl);
    }

    public function log($mensaje, $nivel = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $linea = "[$timestamp] [$nivel] $mensaje";
        
        // Escribir a consola siempre
        echo $linea . "\n";
        
        // Acumular para webhook (solo mensajes importantes)
        if ($this->webhookEnabled && in_array($nivel, ['SUCCESS', 'WARNING', 'ERROR'])) {
            $this->mensajesPendientes[] = [
                'nivel' => $nivel,
                'mensaje' => $mensaje,
                'timestamp' => $timestamp
            ];
        }
    }

    public function info($mensaje)
    {
        $this->log($mensaje, 'INFO');
    }

    public function success($mensaje)
    {
        $this->log($mensaje, 'SUCCESS');
    }

    public function warning($mensaje)
    {
        $this->log($mensaje, 'WARNING');
    }

    public function error($mensaje)
    {
        $this->log($mensaje, 'ERROR');
    }

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

    /**
     * Enviar mensaje inmediato a Google Chat
     */
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
                    'title' => "$emoji $titulo",
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

    /**
     * Enviar resumen acumulado a Google Chat
     */
    public function enviarResumen($titulo, $datos, $exitoso = true)
    {
        if (!$this->webhookEnabled) {
            return;
        }

        $emoji = $exitoso ? '✅' : '❌';
        $widgets = [];

        // Agregar datos del resumen
        foreach ($datos as $key => $value) {
            $widgets[] = [
                'keyValue' => [
                    'topLabel' => $key,
                    'content' => (string)$value
                ]
            ];
        }

        // Agregar mensajes importantes acumulados
        if (!empty($this->mensajesPendientes)) {
            $texto = "*Eventos importantes:*\n";
            foreach (array_slice($this->mensajesPendientes, -10) as $msg) {
                $emoji = $this->getEmoji($msg['nivel']);
                $texto .= "• $emoji {$msg['mensaje']}\n";
            }
            
            $widgets[] = [
                'textParagraph' => [
                    'text' => $texto
                ]
            ];
        }

        $payload = [
            'cards' => [[
                'header' => [
                    'title' => "$emoji $titulo",
                    'subtitle' => 'Cronjob Morosidad - ' . date('Y-m-d H:i:s')
                ],
                'sections' => [[
                    'widgets' => $widgets
                ]]
            ]]
        ];

        $this->enviarWebhook($payload);
        $this->mensajesPendientes = []; // Limpiar después de enviar
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
            'ERROR' => '❌',
            'WARNING' => '⚠️',
            'INFO' => 'ℹ️',
            'DEBUG' => '🔍'
        ];
        return $emojis[$tipo] ?? 'ℹ️';
    }

    private function getColor($tipo)
    {
        $colores = [
            'SUCCESS' => '#00FF00',
            'ERROR' => '#FF0000',
            'WARNING' => '#FFA500',
            'INFO' => '#0000FF',
            'DEBUG' => '#888888'
        ];
        return $colores[$tipo] ?? '#000000';
    }
}


// ============================================
// 4. CLASE PRINCIPAL DEL CRONJOB
// ============================================

class CronMorosidad
{
    private $logger;
    private $db;
    private $dryRun;
    private $force;

    public function __construct(CronLogger $logger, $dryRun = false, $force = false)
    {
        $this->logger = $logger;
        $this->dryRun = $dryRun;
        $this->force = $force;
        
        // Usar la clase DatabaseSegundometro del proyecto
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
            $this->logger->info("INICIANDO PROCESO DE MOROSIDAD");
            $this->logger->info("Modo: " . ($this->dryRun ? "DRY-RUN (Simulación)" : "PRODUCCIÓN"));
            $this->logger->separator();

            // Enviar notificación de inicio
            $this->logger->enviarWebhookInmediato(
                "Cronjob Iniciado",
                "Proceso de morosidad iniciado en modo " . ($this->dryRun ? "simulación" : "producción"),
                "INFO"
            );

            // Validar día de la semana
            if (!$this->validarDiaSemana()) {
                return false;
            }

            // Paso 1: Consultar créditos morosos
            $creditos = $this->consultarCreditosMorosos();

            // Paso 2: Insertar en tabla de gastos
            $resultado = $this->insertarGastosCobranza($creditos);

            // Resumen final
            $this->mostrarResumen($resultado);

            // Enviar resumen a Google Chat
            $this->logger->enviarResumen(
                "Proceso Completado Exitosamente",
                [
                    'Total créditos morosos' => $resultado['total'],
                    'Insertados' => $resultado['insertados'],
                    'Duplicados omitidos' => $resultado['duplicados'],
                    'Errores' => $resultado['errores'],
                    'Tasa de procesamiento' => ($resultado['total'] > 0 
                        ? round((($resultado['insertados'] + $resultado['duplicados']) / $resultado['total']) * 100, 2) 
                        : 0) . '%',
                    'Tiempo de ejecución' => $this->logger->getElapsedTime() . 's'
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
            
            // Enviar error a Google Chat
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
        $diaSemana = date('N'); // 1=Lunes, 2=Martes, etc.
        $nombreDia = date('l');
        
        $this->logger->info("Día actual: $nombreDia (código: $diaSemana)");

        if ($diaSemana != 2) {
            if ($this->force) {
                $this->logger->warning("Hoy NO es martes, pero el modo FORCE está activo.");
                $this->logger->info("Continuando ejecución forzada...");
                return true;
            }
            $this->logger->warning("Hoy NO es martes. El proceso debe ejecutarse los martes.");
            $this->logger->info("El script se detendrá automáticamente.");
            return false;
        }

        $this->logger->success("Validación de día correcta: Es martes");
        return true;
    }

    private function consultarCreditosMorosos()
    {
        $this->logger->info("PASO 1: Consultando créditos morosos (Dias_mora > 1)");

        $sql = "SELECT * FROM tbl_segundometro_semana WHERE Dias_mora > 1";
        
        try {
            $creditos = $this->db->queryAll($sql);
            $total = count($creditos);
            
            $this->logger->success("Créditos morosos encontrados: $total");
            
            if ($total > 0) {
                $this->logger->debug("Primeros 3 registros (muestra):");
                foreach (array_slice($creditos, 0, 3) as $index => $credito) {
                    $this->logger->debug(sprintf(
                        "  [%d] ID: %s, Cliente: %s, Dias_mora: %s",
                        $index + 1,
                        $credito['Id_credito'] ?? 'N/A',
                        $credito['Nombre_cliente'] ?? 'N/A',
                        $credito['Dias_mora'] ?? 'N/A'
                    ));
                }
            }

            return $creditos;

        } catch (\Exception $e) {
            $this->logger->error("Error al consultar créditos morosos: " . $e->getMessage());
            throw $e;
        }
    }

    private function insertarGastosCobranza($creditos)
    {
        $this->logger->info("PASO 2: Insertando registros en gastos_cobranza");
        
        $total = count($creditos);
        if ($total == 0) {
            $this->logger->warning("No hay créditos morosos para insertar");
            return ['total' => 0, 'insertados' => 0, 'errores' => 0, 'duplicados' => 0];
        }

        if ($this->dryRun) {
            $this->logger->warning("DRY-RUN: No se insertarán registros");
            $this->logger->info("Se procesarían $total registros");
            return ['total' => $total, 'insertados' => 0, 'errores' => 0, 'duplicados' => 0];
        }

        $insertados = 0;
        $errores = 0;
        $duplicados = 0;
        $erroresDetalle = [];

        // SQL para verificar duplicados (mismo crédito en la misma semana)
        $sqlVerificar = <<<SQL
            SELECT COUNT(*) as existe 
            FROM gastos_cobranza 
            WHERE Id_credito = :Id_credito 
            AND WEEK(SEMANA, 1) = WEEK(NOW(), 1)
            AND YEAR(SEMANA) = YEAR(NOW())
        SQL;

        // SQL de inserción con control de duplicados
        $sql = <<<SQL
            INSERT INTO gastos_cobranza (
                Id_credito,
                Id_cliente,
                Nombre_cliente,
                Saldo_vencido_inicio,
                SEMANA,
                periodo_inicio,
                periodo_fin,
                monto_valor,
                Fecha_primer_vencimiento,
                cuota,
                condonado
            ) VALUES (
                :Id_credito,
                :Id_cliente,
                :Nombre_cliente,
                :Saldo_vencido_inicio,
                NOW(),
                :periodo_inicio,
                :periodo_fin,
                :monto_valor,
                :Fecha_primer_vencimiento,
                :cuota,
                :condonado
            )
        SQL;

        foreach ($creditos as $index => $credito) {
            try {
                $idCredito = $credito['Id_credito'] ?? null;
                
                if (!$idCredito) {
                    $errores++;
                    $this->logger->error("Registro sin Id_credito en índice $index");
                    continue;
                }

                // Verificar si ya existe para esta semana
                $verificacion = $this->db->queryAll($sqlVerificar, [':Id_credito' => $idCredito]);
                
                if ($verificacion && $verificacion[0]['existe'] > 0) {
                    $duplicados++;
                    $this->logger->debug("Crédito $idCredito ya existe para esta semana - omitido");
                    continue;
                }

                // Preparar valores para inserción
                $valores = [
                    ':Id_credito' => $idCredito,
                    ':Id_cliente' => $credito['Id_cliente'] ?? null,
                    ':Nombre_cliente' => $credito['Nombre_cliente'] ?? null,
                    ':Saldo_vencido_inicio' => $credito['Saldo_vencido_inicio'] ?? 0,
                    ':periodo_inicio' => $credito['periodo_inicio'] ?? null,
                    ':periodo_fin' => $credito['periodo_fin'] ?? null,
                    ':monto_valor' => $credito['monto_valor'] ?? 0,
                    ':Fecha_primer_vencimiento' => $credito['Fecha_primer_vencimiento'] ?? null,
                    ':cuota' => $credito['cuota'] ?? 0,
                    ':condonado' => $credito['condonado'] ?? 0
                ];

                $this->db->query($sql, $valores);
                $insertados++;

                // Mostrar progreso cada 50 registros procesados
                $procesados = $insertados + $duplicados + $errores;
                if ($procesados % 50 == 0) {
                    $progreso = round(($procesados / $total) * 100, 1);
                    $this->logger->info("Progreso: $progreso% ({$insertados} insertados, {$duplicados} duplicados, {$errores} errores)");
                }

            } catch (\Exception $e) {
                $errores++;
                $errorMsg = sprintf(
                    "Error en registro %d (ID: %s): %s",
                    $index + 1,
                    $credito['Id_credito'] ?? 'N/A',
                    $e->getMessage()
                );
                $this->logger->error($errorMsg);
                $erroresDetalle[] = $errorMsg;

                // Si hay muchos errores, detener
                if ($errores > 100) {
                    $this->logger->error("DEMASIADOS ERRORES ($errores). Deteniendo proceso.");
                    break;
                }
            }
        }

        return [
            'total' => $total,
            'insertados' => $insertados,
            'errores' => $errores,
            'duplicados' => $duplicados,
            'errores_detalle' => $erroresDetalle
        ];
    }

    private function mostrarResumen($resultado)
    {
        $this->logger->separator();
        $this->logger->info("RESUMEN DE EJECUCIÓN");
        $this->logger->separator();
        $this->logger->info("Total de créditos morosos: " . $resultado['total']);
        $this->logger->info("Insertados exitosamente:   " . $resultado['insertados']);
        $this->logger->info("Duplicados omitidos:       " . $resultado['duplicados']);
        $this->logger->info("Errores de inserción:      " . $resultado['errores']);
        
        if ($resultado['errores'] > 0 && isset($resultado['errores_detalle'])) {
            $this->logger->warning("Primeros 5 errores:");
            foreach (array_slice($resultado['errores_detalle'], 0, 5) as $error) {
                $this->logger->warning("  - $error");
            }
        }

        $procesados = $resultado['insertados'] + $resultado['duplicados'];
        $porcentajeExito = $resultado['total'] > 0 
            ? round(($procesados / $resultado['total']) * 100, 2) 
            : 0;
        $this->logger->info("Tasa de procesamiento:     $porcentajeExito%");
        
        if ($resultado['duplicados'] > 0) {
            $this->logger->success("Control de duplicados funcionó correctamente");
        }
    }
}

// ============================================
// 5. EJECUCIÓN PRINCIPAL
// ============================================

try {
    // Inicializar logger con webhook
    $logger = new CronLogger($GOOGLE_CHAT_WEBHOOK, $verbose, $noWebhook);
    
    $logger->separator();
    $logger->info("CRONJOB: INSERCIÓN DE MOROSIDAD");
    $logger->info("Fecha/Hora: " . date('Y-m-d H:i:s'));
    
    if ($dryRun) {
        $logger->warning("MODO DRY-RUN ACTIVADO - No se modificará la base de datos");
    }
    
    if ($force) {
        $logger->warning("MODO FORCE ACTIVADO - Se ejecutará sin importar el día");
    }

    if ($noWebhook || empty($GOOGLE_CHAT_WEBHOOK)) {
        $logger->warning("WEBHOOK DESACTIVADO - No se enviarán notificaciones a Google Chat");
    } else {
        $logger->success("WEBHOOK ACTIVADO - Se enviarán notificaciones a Google Chat");
    }
    
    $logger->separator();

    // Ejecutar cronjob
    $cron = new CronMorosidad($logger, $dryRun, $force);
    $exitoso = $cron->ejecutar();

    // Código de salida
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