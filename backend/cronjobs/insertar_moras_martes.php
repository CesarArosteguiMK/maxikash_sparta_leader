<?php
/**
 * CRONJOB: Insertar créditos mora los martes a las 7:50 AM
 * Ubicación: /cronjobs/insertar_moras_martes.php
 * 
 * Uso:
 *   - Ejecución manual: php insertar_moras_martes.php
 *   - Modo prueba (sin modificar DB): php insertar_moras_martes.php --dry-run
 *   - Forzar ejecución cualquier día: php insertar_moras_martes.php --force
 * 
 * Programación recomendada crontab:
 *   50 7 * * 2 cd /ruta/cronjobs && php insertar_moras_martes.php >> /var/log/morosidad.log 2>&1
 */

// ============================================
// 1. BOOTSTRAP DEL PROYECTO
// ============================================

$projectRoot = dirname(__DIR__);

// Cargar autoloader del proyecto
require_once $projectRoot . '/../vendor/autoload.php';

// Definir constantes necesarias si no están definidas
if (!defined('RAIZ')) {
    define('RAIZ', $projectRoot);
}

// ============================================
// 2. CONFIGURACIÓN DEL CRONJOB
// ============================================

// Directorio de logs
$logDir = $projectRoot . '/cronjobs/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/morosidad_' . date('Y-m') . '.log';

// Parsear argumentos de línea de comandos
$args = getopt('', ['dry-run', 'force', 'verbose']);
$dryRun = isset($args['dry-run']);
$force = isset($args['force']);
$verbose = isset($args['verbose']);

// ============================================
// 3. CLASE LOGGER
// ============================================

class CronLogger
{
    private $logFile;
    private $verbose;
    private $startTime;

    public function __construct($logFile, $verbose = false)
    {
        $this->logFile = $logFile;
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
    }

    public function log($mensaje, $nivel = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $linea = "[$timestamp] [$nivel] $mensaje\n";
        
        // Escribir a archivo
        file_put_contents($this->logFile, $linea, FILE_APPEND);
        
        // Escribir a consola
        echo $linea;
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
        $this->log(str_repeat('=', 80), 'INFO');
    }

    public function getElapsedTime()
    {
        return round(microtime(true) - $this->startTime, 2);
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

    public function __construct(CronLogger $logger, $dryRun = false)
    {
        $this->logger = $logger;
        $this->dryRun = $dryRun;
        
        // Usar la clase Database del proyecto
        try {
            $this->db = new \Core\Database();
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

            // Validar día de la semana
            if (!$this->validarDiaSemana()) {
                return false;
            }

            // Paso 1: Truncar tabla
            $this->truncarTabla();

            // Paso 2: Consultar créditos morosos
            $creditos = $this->consultarCreditosMorosos();

            // Paso 3: Insertar en tabla de gastos
            $resultado = $this->insertarGastosCobranza($creditos);

            // Resumen final
            $this->mostrarResumen($resultado);

            $this->logger->separator();
            $this->logger->success("PROCESO COMPLETADO EXITOSAMENTE");
            $this->logger->info("Tiempo de ejecución: " . $this->logger->getElapsedTime() . " segundos");
            $this->logger->separator();

            return true;

        } catch (\Exception $e) {
            $this->logger->error("ERROR CRÍTICO: " . $e->getMessage());
            $this->logger->error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    private function validarDiaSemana()
    {
        $diaSemana = date('N'); // 1=Lunes, 2=Martes, etc.
        $nombreDia = date('l');
        
        $this->logger->info("Día actual: $nombreDia (código: $diaSemana)");

        if ($diaSemana != 2) {
            $this->logger->warning("Hoy NO es martes. El proceso debe ejecutarse los martes.");
            $this->logger->info("El script se detendrá automáticamente.");
            return false;
        }

        $this->logger->success("Validación de día correcta: Es martes");
        return true;
    }

    private function truncarTabla()
    {
        $this->logger->info("PASO 1: Truncando tabla gastos_cobranza");

        if ($this->dryRun) {
            $this->logger->warning("DRY-RUN: No se ejecutará el TRUNCATE");
            return;
        }

        $sql = "TRUNCATE TABLE gastos_cobranza";
        
        try {
            $this->db->query($sql);
            $this->logger->success("Tabla gastos_cobranza truncada exitosamente");
        } catch (\Exception $e) {
            $this->logger->error("Error al truncar tabla: " . $e->getMessage());
            throw $e;
        }
    }

    private function consultarCreditosMorosos()
    {
        $this->logger->info("PASO 2: Consultando créditos morosos (Dias_mora > 1)");

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
        $this->logger->info("PASO 3: Insertando registros en gastos_cobranza");
        
        $total = count($creditos);
        if ($total == 0) {
            $this->logger->warning("No hay créditos morosos para insertar");
            return ['total' => 0, 'insertados' => 0, 'errores' => 0];
        }

        if ($this->dryRun) {
            $this->logger->warning("DRY-RUN: No se insertarán registros");
            $this->logger->info("Se procesarían $total registros");
            return ['total' => $total, 'insertados' => 0, 'errores' => 0];
        }

        $insertados = 0;
        $errores = 0;
        $erroresDetalle = [];

        // SQL de inserción - AJUSTA LAS COLUMNAS SEGÚN TU TABLA
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
                // Preparar valores - AJUSTA SEGÚN TUS COLUMNAS REALES
                $valores = [
                    ':Id_credito' => $credito['Id_credito'] ?? null,
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

                // Mostrar progreso cada 50 registros
                if (($insertados + $errores) % 50 == 0) {
                    $progreso = round((($insertados + $errores) / $total) * 100, 1);
                    $this->logger->info("Progreso: $progreso% ({$insertados}/$total insertados, $errores errores)");
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
        $this->logger->info("Errores de inserción:      " . $resultado['errores']);
        
        if ($resultado['errores'] > 0 && isset($resultado['errores_detalle'])) {
            $this->logger->warning("Primeros 5 errores:");
            foreach (array_slice($resultado['errores_detalle'], 0, 5) as $error) {
                $this->logger->warning("  - $error");
            }
        }

        $porcentajeExito = $resultado['total'] > 0 
            ? round(($resultado['insertados'] / $resultado['total']) * 100, 2) 
            : 0;
        $this->logger->info("Tasa de éxito:             $porcentajeExito%");
    }
}

// ============================================
// 5. EJECUCIÓN PRINCIPAL
// ============================================

try {
    // Inicializar logger
    $logger = new CronLogger($logFile, $verbose);
    
    $logger->separator();
    $logger->info("CRONJOB: INSERCIÓN DE MOROSIDAD");
    $logger->info("Fecha/Hora: " . date('Y-m-d H:i:s'));
    $logger->info("Archivo de log: $logFile");
    
    if ($dryRun) {
        $logger->warning("MODO DRY-RUN ACTIVADO - No se modificará la base de datos");
    }
    
    if ($force) {
        $logger->warning("MODO FORCE ACTIVADO - Se ejecutará sin importar el día");
    }
    
    $logger->separator();

    // Ejecutar cronjob
    $cron = new CronMorosidad($logger, $dryRun);
    $exitoso = $cron->ejecutar();

    // Código de salida
    exit($exitoso ? 0 : 1);

} catch (\Exception $e) {
    if (isset($logger)) {
        $logger->error("ERROR FATAL NO CAPTURADO: " . $e->getMessage());
        $logger->error("Stack trace: " . $e->getTraceAsString());
    } else {
        echo "ERROR FATAL: " . $e->getMessage() . "\n";
    }
    exit(1);
}