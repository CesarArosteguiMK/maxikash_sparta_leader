<?php

namespace Models;

use Core\Model;
use Core\DatabaseSegundometro;

class SegundometroDAO extends Model
{
    /**
     * Configuración SSH para conectarse al servidor remoto
     * IMPORTANTE: Ajustar según tu configuración
     */
    private static $SSH_HOST = '34.173.106.81';
    private static $SSH_USER = 'jesus';
    //private static $SSH_KEY = 'C:\\Users\\lrgon\\Downloads\\jesusssh4.unknown';

    private static $SSH_KEY = __DIR__ . '/../config/ssh/jesusssh4.unknown';


    // __DIR__ = sparta___SPARTA_SECRET_REDACTED__/backend/Models/
    // /../ = Sube un nivel → sparta___SPARTA_SECRET_REDACTED__/backend/
    // /config/ssh/jesusssh4.unknown = Entra a config/ssh 

    private static $DIRECTORIO_REMOTO = '/home/usuariossftp/s2/mega_reporte';

    /** Último error al listar archivos (SSH o vacío), para mostrar en la UI */
    private static $lastListError = '';

    /**
     * Devuelve el último error de obtenerArchivos() (SSH fallido, etc.) para mostrarlo en la interfaz.
     * @return string
     */
    public static function getLastListError()
    {
        return self::$lastListError;
    }

    /**
     * Ruta de la clave SSH (cacheada). Con Plink usa .ppk; con OpenSSH usa PEM/.unknown.
     * @param bool|null $forPlink true = clave .ppk (Plink), false = clave PEM (OpenSSH), null = decidir por config (retrocompat)
     */
    private static function getSSHKey($forPlink = null)
    {
        static $cachedPlink = null, $cachedOpenSSH = null;
        $configFile = __DIR__ . '/../config/config.ini';
        $config = (is_file($configFile) && is_array($cfg = @parse_ini_file($configFile, true))) ? $cfg : [];
        $usePlink = ($forPlink !== null) ? $forPlink : !empty($config['ssh']['ssh_use_plink']);

        if ($usePlink) {
            if ($cachedPlink !== null) {
                return $cachedPlink;
            }
            // Plink exige clave .ppk. Orden: config, luego backend/config/ssh/jesusssh4.ppk. Usar la primera que EXISTA (intentar aunque is_readable falle).
            $ppkProyecto = __DIR__ . '/../config/ssh/jesusssh4.ppk';
            $candidatosPpk = array_filter([
                trim($config['ssh']['ssh_key_plink'] ?? ''),
                $ppkProyecto,
            ]);
            foreach ($candidatosPpk as $p) {
                if ($p !== '' && @is_file($p)) {
                    $cachedPlink = $p;
                    return $p;
                }
            }
            $path = trim($config['ssh']['ssh_key_plink'] ?? '');
            $cachedPlink = $path !== '' ? $path : $ppkProyecto;
            return $cachedPlink;
        }

        // No usar nunca clave en Downloads; invalidar caché si estaba guardada ahí.
        if ($cachedOpenSSH !== null && stripos($cachedOpenSSH, 'Downloads') === false) {
            return $cachedOpenSSH;
        }
        if ($cachedOpenSSH !== null) {
            $cachedOpenSSH = null;
        }
        $path = trim($config['ssh']['ssh_key'] ?? '');
        // Solo clave en backend/config/ssh (nunca usar rutas en Downloads).
        if ($path !== '' && stripos($path, 'Downloads') !== false) {
            $path = '';
        }
        $candidatos = array_filter([$path !== '' ? $path : null, self::$SSH_KEY]);
        foreach ($candidatos as $p) {
            if ($p === null || $p === '') {
                continue;
            }
            if (@is_file($p) && @is_readable($p)) {
                $cachedOpenSSH = $p;
                return $p;
            }
        }
        $cachedOpenSSH = self::$SSH_KEY;
        return $cachedOpenSSH;
    }
    
    /**
     * Detecta la ruta del ejecutable SSH/Plink (cacheada).
     * Con ssh_use_plink=1: usa Plink SI Y SOLO SI la llave .ppk existe.
     * Si la .ppk no existe, hace fallback automático a OpenSSH.
     */
    private static function getSSHCommand()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached === '' ? null : $cached;
        }
        $configFile = __DIR__ . '/../config/config.ini';
        $config = is_file($configFile) ? @parse_ini_file($configFile, true) : false;
        $config = is_array($config) ? $config : [];
        $logFile = __DIR__ . '/../storage/logs/ssh_debug.log';

        // --- Plink (PuTTY) --- solo si la llave .ppk existe
        if (!empty($config['ssh']['ssh_use_plink'])) {
            $ppkConfig = trim($config['ssh']['ssh_key_plink'] ?? '');
            $ppkProyecto = __DIR__ . '/../config/ssh/jesusssh4.ppk';
            $ppkFound = ($ppkConfig !== '' && @is_file($ppkConfig)) || @is_file($ppkProyecto);

            if ($ppkFound) {
                $path = trim($config['ssh']['ssh_command_plink'] ?? '');
                if ($path !== '' && @is_file($path)) {
                    $cached = $path;
                    return $path;
                }
                $default = 'C:\\xampp\\plink.exe';
                if (@is_file($default)) {
                    $cached = $default;
                    return $default;
                }
            }
            // .ppk no encontrada o Plink no disponible -> fallback a OpenSSH
            @file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] FALLBACK: ssh_use_plink=1 pero .ppk no encontrada (" . ($ppkConfig ?: $ppkProyecto) . "). Usando OpenSSH.\n", FILE_APPEND);
        }

        // --- OpenSSH (fallback automático cuando .ppk no existe, o modo normal) ---
        $path = trim($config['ssh']['ssh_command'] ?? '');
        if ($path !== '' && @is_file($path)) {
            $cached = $path;
            return $path;
        }
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $candidates = $isWindows
            ? ['where.exe ssh', 'C:\\Windows\\System32\\OpenSSH\\ssh.exe']
            : ['which ssh', '/usr/bin/ssh', '/bin/ssh'];
        foreach ($candidates as $cmd) {
            if (strpos($cmd, ' ') !== false) {
                $out = @shell_exec($cmd . ' 2>&1');
                $p = $out ? trim(explode("\n", $out)[0]) : '';
            } else {
                $p = $cmd;
            }
            if ($p !== '' && @is_file($p)) {
                $cached = $p;
                return $p;
            }
        }
        if (!$isWindows) {
            $cached = 'ssh';
            return 'ssh';
        }
        $cached = '';
        return null;
    }
    
    /**
     * Ruta para UserKnownHostsFile: en Windows NUL, en Linux /dev/null.
     */
    private static function getSSHKnownHostsFile()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'NUL' : '/dev/null';
    }

    /**
     * Ejecutar comando SSH remoto.
     * Con ssh_use_plink=1: usa Plink + .ppk si la .ppk existe; si no, fallback automático a OpenSSH.
     */
    private static function ejecutarSSH($comando)
    {
        $sshCommand = self::getSSHCommand();
        if ($sshCommand === null) {
            return [
                'success' => false,
                'output' => '',
                'error' => 'SSH/Plink no encontrado. Configure [ssh] en backend/config/config.ini.',
                'return_code' => 127
            ];
        }

        $isPlink = (stripos($sshCommand, 'plink') !== false);
        $configFile = __DIR__ . '/../config/config.ini';
        $keyFinal = self::getSSHKey($isPlink ? true : false);
        $comandoEscapado = escapeshellarg($comando);

        // Log
        $logFile = __DIR__ . '/../storage/logs/ssh_debug.log';
        $logLines = ["\n=== " . date('Y-m-d H:i:s') . " ==="];
        $logLines[] = "Modo: " . ($isPlink ? "Plink (PuTTY)" : "OpenSSH");
        $logLines[] = "Ejecutable: " . $sshCommand;
        $logLines[] = "Clave: " . $keyFinal;
        $logLines[] = "Existe clave: " . (@is_file($keyFinal) ? 'SI' : 'NO');

        if ($isPlink) {
            $hostkey = '';
            $cfg = (is_file($configFile) && is_array($c = @parse_ini_file($configFile, true))) ? $c : [];
            $hk = trim($cfg['ssh']['ssh_hostkey'] ?? '');
            if ($hk !== '') {
                $hostkey = ' -hostkey ' . escapeshellarg($hk);
            }
            $sshComando = sprintf(
                '%s -i %s%s -batch %s@%s %s 2>&1',
                escapeshellarg($sshCommand),
                escapeshellarg($keyFinal),
                $hostkey,
                self::$SSH_USER,
                self::$SSH_HOST,
                $comandoEscapado
            );
        } else {
            $knownHosts = self::getSSHKnownHostsFile();
            $sshComando = sprintf(
                '%s -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=%s -o ConnectTimeout=10 -o BatchMode=yes -o ServerAliveInterval=5 %s@%s %s 2>&1',
                escapeshellarg($sshCommand),
                escapeshellarg($keyFinal),
                $knownHosts,
                self::$SSH_USER,
                self::$SSH_HOST,
                $comandoEscapado
            );
        }

        $logLines[] = "Comando: " . $sshComando;
        @file_put_contents($logFile, implode("\n", $logLines) . "\n", FILE_APPEND);

        $outputLines = [];
        $returnVar = -1;
        exec($sshComando, $outputLines, $returnVar);
        $outputStr = trim(implode("\n", $outputLines));

        @file_put_contents($logFile, "Output: " . ($outputStr !== '' ? $outputStr : 'NULL') . "\nReturn code: $returnVar\n", FILE_APPEND);

        $success = ($returnVar === 0);
        return [
            'success' => $success,
            'output' => $outputStr,
            'error' => $success ? '' : ($outputStr !== '' ? $outputStr : 'Comando remoto falló (código ' . $returnVar . ')'),
            'return_code' => $returnVar
        ];
    }
    
    /**
     * Obtener archivos de reportes: solo hoy y ayer, con owner (ls -l)
     * Owner s2 = proveedor, root = nosotros
     *
     * @return array Lista de archivos con información (nombre, owner, fecha_display, etc.)
     */
    public static function obtenerArchivos()
    {
        $archivos = [];
        self::$lastListError = '';

        try {
            // Solo hoy y ayer (formato nombre: mega_rpt_YYYYMMDD_HH_MM_SS.csv.zip)
            $fechaLimite = date('Ymd', strtotime('-1 day'));

            // ls -l para obtener owner (col 3), size (col 5), filename (col 9)
            $comandoListar = sprintf(
                "cd %s && ls -l mega_rpt_*.csv.zip 2>/dev/null",
                escapeshellarg(self::$DIRECTORIO_REMOTO)
            );

            $resultado = self::ejecutarSSH($comandoListar);

            if (!$resultado['success']) {
                $err = $resultado['error'] ?? 'Error desconocido al conectar por SSH';
                self::$lastListError = $err;
                error_log("Error SSH al listar archivos: " . $err);
                return [];
            }
            
            $lineas = explode("\n", trim($resultado['output']));
            
            foreach ($lineas as $linea) {
                $linea = trim($linea);
                if (empty($linea)) continue;
                
                // Formato ls -l: permisos links owner group size month day time filename
                $partes = preg_split('/\s+/', $linea, 9);
                if (count($partes) < 9) continue;
                
                $owner = $partes[2];
                $sizeBytes = (int) $partes[4];
                $nombreArchivo = basename($partes[8]);
                // Formato real del megareporte: mega_rpt_YYYYMMDD_HH_MM_SS.csv.zip
                if (!preg_match('/mega_rpt_(\d{8})_(\d{2})_(\d{2})_(\d{2})\.csv\.zip/', $nombreArchivo, $matches)) {
                    continue;
                }
                $fechaArchivo = $matches[1];
                $hora = $matches[2];
                $minuto = $matches[3];
                $segundo = $matches[4];
                
                // Solo hoy y ayer
                if ($fechaArchivo < $fechaLimite) continue;
                
                $fechaObj = date('Y-m-d', strtotime($fechaArchivo));
                $fechaHoy = date('Y-m-d');
                $fechaAyer = date('Y-m-d', strtotime('-1 day'));
                
                $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                         'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                $mes = $meses[(int)date('n', strtotime($fechaArchivo)) - 1];
                
                if ($fechaObj === $fechaHoy) {
                    $fechaDisplay = 'Hoy - ' . date('d', strtotime($fechaArchivo)) . ' de ' . $mes . ' de ' . date('Y', strtotime($fechaArchivo));
                } elseif ($fechaObj === $fechaAyer) {
                    $fechaDisplay = 'Ayer - ' . date('d', strtotime($fechaArchivo)) . ' de ' . $mes . ' de ' . date('Y', strtotime($fechaArchivo));
                } else {
                    $fechaDisplay = date('d', strtotime($fechaArchivo)) . ' de ' . $mes . ' de ' . date('Y', strtotime($fechaArchivo));
                }
                
                $archivos[] = [
                    'nombre' => $nombreArchivo,
                    'owner' => $owner,
                    'ruta_completa' => self::$DIRECTORIO_REMOTO . '/' . $nombreArchivo,
                    'fecha' => $fechaObj,
                    'fecha_formato' => date('d/m/Y', strtotime($fechaArchivo)),
                    'fecha_display' => $fechaDisplay,
                    'hora' => "{$hora}:{$minuto}:{$segundo}",
                    'tamano' => self::formatearTamano($sizeBytes),
                    'tamano_bytes' => $sizeBytes,
                    'timestamp' => strtotime("{$fechaArchivo} {$hora}:{$minuto}:{$segundo}")
                ];
            }
            
            // Ordenar por timestamp descendente (más reciente primero)
            usort($archivos, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
            
        } catch (\Exception $e) {
            error_log("Error al obtener archivos: " . $e->getMessage());
            // Retornar array vacío si hay error
            return [];
        }
        
        return $archivos;
    }
    
    /**
     * Copiar archivo con +1 segundo en el nombre (en servidor remoto)
     * Ejecuta: sudo cp archivo_original.zip archivo_+1segundo.zip
     * 
     * @param string $nombreArchivo Nombre del archivo a copiar
     * @return array Información de la operación
     */
    public static function copiarConSegundoAdelantado($nombreArchivo)
    {
        // Formato real: mega_rpt_YYYYMMDD_HH_MM_SS.csv.zip
        if (!preg_match('/mega_rpt_(\d{8})_(\d{2})_(\d{2})_(\d{2})\.csv\.zip/', $nombreArchivo, $matches)) {
            throw new \Exception('Formato de archivo inválido');
        }
        $fecha = $matches[1];
        $hora = (int)$matches[2];
        $minuto = (int)$matches[3];
        $segundo = (int)$matches[4];
        
        // Incrementar segundo
        $segundoNuevo = $segundo + 1;
        
        // Manejar overflow de segundos (59 -> 00)
        if ($segundoNuevo >= 60) {
            $segundoNuevo = 0;
            $minuto++;
            
            // Manejar overflow de minutos (59 -> 00)
            if ($minuto >= 60) {
                $minuto = 0;
                $hora++;
                
                // Manejar overflow de horas (23 -> 00)
                if ($hora >= 24) {
                    $hora = 0;
                    // Incrementar fecha
                    $fecha = date('Ymd', strtotime($fecha . ' +1 day'));
                }
            }
        }
        
        // Formatear nuevo nombre
        $horaStr = str_pad($hora, 2, '0', STR_PAD_LEFT);
        $minutoStr = str_pad($minuto, 2, '0', STR_PAD_LEFT);
        $segundoStr = str_pad($segundoNuevo, 2, '0', STR_PAD_LEFT);
        $nombreNuevo = "mega_rpt_{$fecha}_{$horaStr}_{$minutoStr}_{$segundoStr}.csv.zip";
        
        // Rutas completas en servidor remoto
        $rutaOrigen = self::$DIRECTORIO_REMOTO . '/' . $nombreArchivo;
        $rutaDestino = self::$DIRECTORIO_REMOTO . '/' . $nombreNuevo;
        
        try {
            // Ejecutar comando sudo cp en servidor remoto
            $comando = sprintf(
                "cd %s && sudo cp %s %s",
                escapeshellarg(self::$DIRECTORIO_REMOTO),
                escapeshellarg($nombreArchivo),
                escapeshellarg($nombreNuevo)
            );
            
            $resultado = self::ejecutarSSH($comando);
            
            if (!$resultado['success']) {
                throw new \Exception('Error al ejecutar comando: ' . $resultado['error']);
            }
            
            // Verificar que el archivo se haya creado (listar archivos)
            $comandoVerificar = sprintf(
                "cd %s && ls -1 %s 2>/dev/null",
                escapeshellarg(self::$DIRECTORIO_REMOTO),
                escapeshellarg($nombreNuevo)
            );
            
            $verificacion = self::ejecutarSSH($comandoVerificar);
            
            if (!$verificacion['success'] || empty(trim($verificacion['output']))) {
                throw new \Exception('El archivo no se creó correctamente en el servidor');
            }
            
            return [
                'origen' => $nombreArchivo,
                'destino' => $nombreNuevo,
                'ruta_origen' => $rutaOrigen,
                'ruta_destino' => $rutaDestino,
                'comando' => "sudo cp {$rutaOrigen} {$rutaDestino}",
                'mensaje' => 'Archivo copiado exitosamente'
            ];
            
        } catch (\Exception $e) {
            error_log("Error al copiar archivo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Copiar archivo en el servidor remoto con nombre de destino indicado (origen y destino válidos mega_rpt_*.csv.zip).
     *
     * @param string $nombreOrigen Nombre del archivo origen
     * @param string $nombreDestino Nombre del archivo destino (p. ej. +2 seg, +1 min)
     * @return array ['origen' => ..., 'destino' => ..., ...]
     */
    public static function copiarAArchivo($nombreOrigen, $nombreDestino)
    {
        if (!preg_match('/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/', $nombreOrigen)) {
            throw new \Exception('Formato de archivo origen inválido');
        }
        if (!preg_match('/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/', $nombreDestino)) {
            throw new \Exception('Formato de archivo destino inválido');
        }
        if ($nombreOrigen === $nombreDestino) {
            throw new \Exception('Origen y destino no pueden ser iguales');
        }
        $rutaOrigen = self::$DIRECTORIO_REMOTO . '/' . $nombreOrigen;
        $rutaDestino = self::$DIRECTORIO_REMOTO . '/' . $nombreDestino;
        try {
            $comando = sprintf(
                "cd %s && sudo cp %s %s",
                escapeshellarg(self::$DIRECTORIO_REMOTO),
                escapeshellarg($nombreOrigen),
                escapeshellarg($nombreDestino)
            );
            $resultado = self::ejecutarSSH($comando);
            if (!$resultado['success']) {
                throw new \Exception('Error al ejecutar comando: ' . $resultado['error']);
            }
            $comandoVerificar = sprintf(
                "cd %s && ls -1 %s 2>/dev/null",
                escapeshellarg(self::$DIRECTORIO_REMOTO),
                escapeshellarg($nombreDestino)
            );
            $verificacion = self::ejecutarSSH($comandoVerificar);
            if (!$verificacion['success'] || empty(trim($verificacion['output']))) {
                throw new \Exception('El archivo no se creó correctamente en el servidor');
            }
            return [
                'origen' => $nombreOrigen,
                'destino' => $nombreDestino,
                'ruta_origen' => $rutaOrigen,
                'ruta_destino' => $rutaDestino,
                'comando' => "sudo cp {$rutaOrigen} {$rutaDestino}",
                'mensaje' => 'Archivo copiado exitosamente'
            ];
        } catch (\Exception $e) {
            error_log("Error al copiar archivo: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Copiar archivo remoto a un archivo temporal local para descarga
     * Usa SCP: servidor remoto -> archivo temporal en el servidor PHP
     *
     * @param string $nombreArchivo Nombre del archivo (ej. mega_rpt_20260128_16_31_21.csv.zip)
     * @return string Ruta local del archivo temporal (el llamador debe eliminarlo después de enviarlo)
     */
    public static function copiarRemotoATemporal($nombreArchivo)
    {
        if (!preg_match('/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/', $nombreArchivo)) {
            throw new \Exception('Formato de archivo inválido');
        }

        $rutaRemota = self::$DIRECTORIO_REMOTO . '/' . $nombreArchivo;
        $tempDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        $nombreLocal = 'segundometro_' . uniqid() . '_' . $nombreArchivo;
        $rutaLocal = $tempDir . DIRECTORY_SEPARATOR . $nombreLocal;
        
        $sshCommand = self::getSSHCommand();
        $isPlink = $sshCommand !== null && (stripos($sshCommand, 'plink') !== false);
        
        if ($isPlink) {
            // Plink: intentar pscp (PuTTY SCP); si no está disponible, usar plink + cat (fallback)
            $configFile = __DIR__ . '/../config/config.ini';
            $cfg = (is_file($configFile) && is_array($c = @parse_ini_file($configFile, true))) ? $c : [];
            $pscpPath = trim($cfg['ssh']['ssh_pscp'] ?? '');
            if ($pscpPath === '' || !@is_file($pscpPath)) {
                $plinkDir = dirname($sshCommand);
                $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
                $pscpPath = $plinkDir . DIRECTORY_SEPARATOR . 'pscp' . ($isWin ? '.exe' : '');
            }
            if (!@is_file($pscpPath)) {
                $pscpPath = 'pscp' . (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '.exe' : '');
            }
            $keyPath = self::getSSHKey(true);
            $hostkey = '';
            $hk = trim($cfg['ssh']['ssh_hostkey'] ?? '');
            if ($hk !== '') {
                $hostkey = ' -hostkey ' . escapeshellarg($hk);
            }

            $usoPscp = @is_file($pscpPath);
            if ($usoPscp) {
                $remoteSpec = self::$SSH_USER . '@' . self::$SSH_HOST . ':' . $rutaRemota;
                $comando = sprintf(
                    '%s -i %s%s -batch %s %s 2>&1',
                    escapeshellarg($pscpPath),
                    escapeshellarg($keyPath),
                    $hostkey,
                    escapeshellarg($remoteSpec),
                    escapeshellarg($rutaLocal)
                );
            } else {
                // Fallback: plink ejecuta "cat rutaRemota" en el servidor; capturamos stdout en binario (no requiere pscp)
                $userHost = self::$SSH_USER . '@' . self::$SSH_HOST;
                $remoteCmd = 'cat ' . str_replace("'", "'\\''", $rutaRemota);
                $comandoPlink = sprintf(
                    '%s -i %s%s -batch %s %s',
                    escapeshellarg($sshCommand),
                    escapeshellarg($keyPath),
                    $hostkey,
                    escapeshellarg($userHost),
                    escapeshellarg($remoteCmd)
                );
                $descriptors = [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ];
                $proc = @proc_open($comandoPlink, $descriptors, $pipes, null, null, ['binary_pipes' => true]);
                if (!is_resource($proc)) {
                    @unlink($rutaLocal);
                    throw new \Exception('No se pudo descargar el archivo del servidor remoto: no se pudo ejecutar plink (pscp no está disponible). Instale PuTTY completo o configure ssh_pscp en config.ini.');
                }
                fclose($pipes[0]);
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                $out = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $returnVar = proc_close($proc);
                if ($returnVar !== 0 || $out === false) {
                    @unlink($rutaLocal);
                    throw new \Exception('No se pudo descargar el archivo del servidor remoto: ' . trim($stderr ?: 'plink falló (código ' . $returnVar . ')'));
                }
                if (file_put_contents($rutaLocal, $out, LOCK_EX) === false) {
                    @unlink($rutaLocal);
                    throw new \Exception('No se pudo guardar el archivo descargado en el servidor.');
                }
                return $rutaLocal;
            }
        } else {
            // OpenSSH: scp con clave PEM
            $sshKeyEscaped = escapeshellarg(self::getSSHKey(false));
            $remoteEscaped = escapeshellarg(self::$SSH_USER . '@' . self::$SSH_HOST . ':' . $rutaRemota);
            $localEscaped = escapeshellarg($rutaLocal);
            $knownHosts = self::getSSHKnownHostsFile();
            $comando = sprintf(
                'scp -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=%s -o ConnectTimeout=10 %s %s 2>&1',
                $sshKeyEscaped,
                $knownHosts,
                $remoteEscaped,
                $localEscaped
            );
        }
        
        $output = [];
        $returnVar = 0;
        exec($comando, $output, $returnVar);
        
        if ($returnVar !== 0 || !is_file($rutaLocal)) {
            @unlink($rutaLocal);
            throw new \Exception('No se pudo descargar el archivo del servidor remoto: ' . implode(' ', $output));
        }
        
        return $rutaLocal;
    }

    /**
     * Enviar mensaje a un webhook de Google Chat.
     *
     * @param string $webhookUrl URL del webhook
     * @param string $texto Texto del mensaje
     * @return bool true si se envió correctamente (HTTP 200)
     */
    private static function enviarWebhook($webhookUrl, $texto)
    {
        $payload = json_encode(['text' => $texto]);
        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode === 200;
    }

    /**
     * Proceso de truncar Segundómetro:
     *   1. Copiar tbl_segundometro_semana → tbl_segundometro_histo
     *   2. Notificar a webhook de Google Chat
     *   3. Si OK → truncar tbl_segundometro_semana
     *   4. Notificar resultado final
     *
     * @return array ['success' => bool, 'mensaje' => string, 'registros_copiados' => int]
     */
    public static function truncarSemanaAHistorico()
    {
        $webhookUrl = '__SPARTA_WEBHOOK_REDACTED__';
        $db = new DatabaseSegundometro();
        $fecha = date('Y-m-d H:i:s');
        $usuario = $_SESSION['usuario'] ?? 'sistema';

        // Paso 1: Copiar datos de semana a histórico
        try {
            $rowsCopied = $db->CRUD(
                'INSERT INTO tbl_segundometro_histo
                (
                KT, Id_credito, Id_cliente, Nombre_cliente, Fecha_nacimiento, Genero, Estado_civil, Celular,
                Saldo_vencido_inicio, Sucursal, Status_credito, Numero_amortizaciones, Monto_otorgado, Cuota,
                Fecha_inicio, Fecha_primer_vencimiento, Fecha_ultimo_vencimiento, Referencia_stp, Dias_mora,
                Dias_mora_max, Num_cuotas_pagadas, Saldo_total_capital, Saldo_para_liquidar_hoy, Abonos_total,
                Abonos_numero, Codigo_postal_1, Estado_1, Bucket_Morosidad, Dias_mora_ajustado,
                Dias_mora_ajustado_2, Bucket_Morosidad_Real, Avance_Pago_Plazo, Monto_otorgado_2, Rango_Monto,
                Bucket_Morosidad_Final, Delincuencia_jueves, dias_moda_martes, bucket_inicio_jueves,
                bucket_corte_martes, bucket_actual, delincuencia_martes, fecha_ultimo_abono_efectivo_actual,
                fecha_ultimo_abono_efectivo_domingo_1, fecha_ultimo_abono_efectivo_domingo_2,
                fecha_ultimo_abono_efectivo_domingo_3, fecha_ultimo_abono_efectivo_domingo_4, pago_acreditado,
                Dia_pago_moda, Saldo_total_capital_cierre, Dias_mora_cierre, Domicilio_Completo,
                Gestor_Asignado, Jefe_de_Plaza, Zonal, Territorial, Dias_mora_Lunes_07_30,
                Dias_mora_Lunes_09_30, Dias_mora_Lunes_11_30, Dias_mora_Lunes_13_30,
                Dias_mora_Lunes_14_30, Dias_mora_Lunes_16_30, Dias_mora_Lunes_18_30,
                Dias_mora_Lunes_20_30, Dias_mora_Lunes_23_50, Dias_mora_Martes_07_30,
                Dias_mora_Martes_09_30, Dias_mora_Martes_11_30, Dias_mora_Martes_13_30,
                Dias_mora_Martes_14_30, Dias_mora_Martes_16_30, Dias_mora_Martes_18_30,
                Dias_mora_Martes_20_30, Dias_mora_Martes_23_50, Dias_mora_Miercoles_07_30,
                Dias_mora_Miercoles_09_30, Dias_mora_Miercoles_11_30, Dias_mora_Miercoles_13_30,
                Dias_mora_Miercoles_14_30, Dias_mora_Miercoles_16_30, Dias_mora_Miercoles_18_30,
                Dias_mora_Miercoles_20_30, Dias_mora_Miercoles_23_50, Dias_mora_Jueves_07_30,
                Dias_mora_Jueves_09_30, Dias_mora_Jueves_11_30, Dias_mora_Jueves_13_30,
                Dias_mora_Jueves_14_30, Dias_mora_Jueves_16_30, Dias_mora_Jueves_18_30,
                Dias_mora_Jueves_20_30, Dias_mora_Jueves_23_50, Dias_mora_Viernes_07_30,
                Dias_mora_Viernes_09_30, Dias_mora_Viernes_11_30, Dias_mora_Viernes_13_30,
                Dias_mora_Viernes_14_30, Dias_mora_Viernes_16_30, Dias_mora_Viernes_18_30,
                Dias_mora_Viernes_20_30, Dias_mora_Viernes_23_50, Dias_mora_Sabado_07_30,
                Dias_mora_Sabado_09_30, Dias_mora_Sabado_11_30, Dias_mora_Sabado_13_30,
                Dias_mora_Sabado_14_30, Dias_mora_Sabado_16_30, Dias_mora_Sabado_18_30,
                Dias_mora_Sabado_20_30, Dias_mora_Sabado_23_50, Dias_mora_Domingo_07_30,
                Dias_mora_Domingo_09_30, Dias_mora_Domingo_11_30, Dias_mora_Domingo_13_30,
                Dias_mora_Domingo_14_30, Dias_mora_Domingo_16_30, Dias_mora_Domingo_18_30,
                Dias_mora_Domingo_20_30, Dias_mora_Domingo_23_50, Dias_mora_cierre_semana,
                Observaciones, Cierre_Actual, Saldo_vencido_actualizado, Ajuste, Ghost,
                Fecha_ultimo_pago_efectivo, Cuotas_vencidas, Cuotas_devengadas, Calle_adicional_1,
                Num_exterior_adicional_1, Num_interior_adicional_1, Cp_adicional_2, Colonia_adicional_1,
                Estado_adicional_2, Ciudad_adicional_1, Municipio_adicional_1, Coordenada_fat,
                Direccion_maps, Donde_firma, D_asenta, D_mnpio, D_estado, Codigo_postal_adicional_3,
                Direccion, Calle_numero, Colonia_adicional_2, Ciudad_adicional_2, Estado_adicional_3,
                Calle_numero_adic, Codigo_postal_adic, Adicionales_colonia, Municipio_delegacion,
                Entidad_1, Calle_adicional_2, Num_exterior_adicional_2, Num_interior_adicional_2,
                Cp_adicional_3, Colonia_adicional_3, Estado_adicional_4, Ciudad_adicional_3,
                Municipio_adicional_2, Tipo_de_contacto, Medio_de_contacto, Gestiones, Ultimo_Dictamen,
                Promesas_Totales, Promesas_cumplidas, Promesa_Vigente, Promesa_Rota, Dia_de_la_prom,
                Promesa_de_pago, Monto_abono_efectivo, Bucket_ajustado_ghost, Variable_3, Variable_4,
                Variable_5, Variable_6, Variable_7, Variable_8, Variable_9, Variable_10,
                SEMANA, fecha_hora_insert, reporte_lock
                )
                SELECT
                KT, Id_credito, Id_cliente, Nombre_cliente, Fecha_nacimiento, Genero, Estado_civil, Celular,
                Saldo_vencido_inicio, Sucursal, Status_credito, Numero_amortizaciones, Monto_otorgado, Cuota,
                Fecha_inicio, Fecha_primer_vencimiento, Fecha_ultimo_vencimiento, Referencia_stp, Dias_mora,
                Dias_mora_max, Num_cuotas_pagadas, Saldo_total_capital, Saldo_para_liquidar_hoy, Abonos_total,
                Abonos_numero, Codigo_postal_1, Estado_1, Bucket_Morosidad, Dias_mora_ajustado,
                Dias_mora_ajustado_2, Bucket_Morosidad_Real, Avance_Pago_Plazo, Monto_otorgado_2, Rango_Monto,
                Bucket_Morosidad_Final, Delincuencia_jueves, dias_moda_martes, bucket_inicio_jueves,
                bucket_corte_martes, bucket_actual, delincuencia_martes, fecha_ultimo_abono_efectivo_actual,
                fecha_ultimo_abono_efectivo_domingo_1, fecha_ultimo_abono_efectivo_domingo_2,
                fecha_ultimo_abono_efectivo_domingo_3, fecha_ultimo_abono_efectivo_domingo_4, pago_acreditado,
                Dia_pago_moda, Saldo_total_capital_cierre, Dias_mora_cierre, Domicilio_Completo,
                Gestor_Asignado, Jefe_de_Plaza, Zonal, Territorial, Dias_mora_Lunes_07_30,
                Dias_mora_Lunes_09_30, Dias_mora_Lunes_11_30, Dias_mora_Lunes_13_30,
                Dias_mora_Lunes_14_30, Dias_mora_Lunes_16_30, Dias_mora_Lunes_18_30,
                Dias_mora_Lunes_20_30, Dias_mora_Lunes_23_50, Dias_mora_Martes_07_30,
                Dias_mora_Martes_09_30, Dias_mora_Martes_11_30, Dias_mora_Martes_13_30,
                Dias_mora_Martes_14_30, Dias_mora_Martes_16_30, Dias_mora_Martes_18_30,
                Dias_mora_Martes_20_30, Dias_mora_Martes_23_50, Dias_mora_Miercoles_07_30,
                Dias_mora_Miercoles_09_30, Dias_mora_Miercoles_11_30, Dias_mora_Miercoles_13_30,
                Dias_mora_Miercoles_14_30, Dias_mora_Miercoles_16_30, Dias_mora_Miercoles_18_30,
                Dias_mora_Miercoles_20_30, Dias_mora_Miercoles_23_50, Dias_mora_Jueves_07_30,
                Dias_mora_Jueves_09_30, Dias_mora_Jueves_11_30, Dias_mora_Jueves_13_30,
                Dias_mora_Jueves_14_30, Dias_mora_Jueves_16_30, Dias_mora_Jueves_18_30,
                Dias_mora_Jueves_20_30, Dias_mora_Jueves_23_50, Dias_mora_Viernes_07_30,
                Dias_mora_Viernes_09_30, Dias_mora_Viernes_11_30, Dias_mora_Viernes_13_30,
                Dias_mora_Viernes_14_30, Dias_mora_Viernes_16_30, Dias_mora_Viernes_18_30,
                Dias_mora_Viernes_20_30, Dias_mora_Viernes_23_50, Dias_mora_Sabado_07_30,
                Dias_mora_Sabado_09_30, Dias_mora_Sabado_11_30, Dias_mora_Sabado_13_30,
                Dias_mora_Sabado_14_30, Dias_mora_Sabado_16_30, Dias_mora_Sabado_18_30,
                Dias_mora_Sabado_20_30, Dias_mora_Sabado_23_50, Dias_mora_Domingo_07_30,
                Dias_mora_Domingo_09_30, Dias_mora_Domingo_11_30, Dias_mora_Domingo_13_30,
                Dias_mora_Domingo_14_30, Dias_mora_Domingo_16_30, Dias_mora_Domingo_18_30,
                Dias_mora_Domingo_20_30, Dias_mora_Domingo_23_50, Dias_mora_cierre_semana,
                Observaciones, Cierre_Actual, Saldo_vencido_actualizado, Ajuste, Ghost,
                Fecha_ultimo_pago_efectivo, Cuotas_vencidas, Cuotas_devengadas, Calle_adicional_1,
                Num_exterior_adicional_1, Num_interior_adicional_1, Cp_adicional_2, Colonia_adicional_1,
                Estado_adicional_2, Ciudad_adicional_1, Municipio_adicional_1, Coordenada_fat,
                Direccion_maps, Donde_firma, D_asenta, D_mnpio, D_estado, Codigo_postal_adicional_3,
                Direccion, Calle_numero, Colonia_adicional_2, Ciudad_adicional_2, Estado_adicional_3,
                Calle_numero_adic, Codigo_postal_adic, Adicionales_colonia, Municipio_delegacion,
                Entidad_1, Calle_adicional_2, Num_exterior_adicional_2, Num_interior_adicional_2,
                Cp_adicional_3, Colonia_adicional_3, Estado_adicional_4, Ciudad_adicional_3,
                Municipio_adicional_2, Tipo_de_contacto, Medio_de_contacto, Gestiones, Ultimo_Dictamen,
                Promesas_Totales, Promesas_cumplidas, Promesa_Vigente, Promesa_Rota, Dia_de_la_prom,
                Promesa_de_pago, Monto_abono_efectivo, Bucket_ajustado_ghost, Variable_3, Variable_4,
                Variable_5, Variable_6, Variable_7, Variable_8, Variable_9, Variable_10,
                SEMANA, fecha_hora_insert, reporte_lock
                FROM tbl_segundometro_semana;
                '
            );
        } catch (\Exception $e) {
            $msgError = "❌ *Error al copiar Semana → Historial*\n"
                      . "Fecha: {$fecha}\n"
                      . "Usuario: {$usuario}\n"
                      . "Detalle: " . $e->getMessage();
            self::enviarWebhook($webhookUrl, $msgError);
            throw new \Exception('Error al copiar datos de Semana a Historial: ' . $e->getMessage());
        }

        // Paso 2: Notificar copia exitosa
        $msgCopia = "📋 *Copia automática de Semana → Historial completada*\n"
                  . "✅ *{$rowsCopied}* registros copiados exitosamente\n"
                  . "Fecha: {$fecha}\n"
                  . "Usuario: {$usuario}";
        self::enviarWebhook($webhookUrl, $msgCopia);

        // Paso 3: Truncar tabla semana
        try {
            $db->CRUD('TRUNCATE TABLE tbl_segundometro_semana');
        } catch (\Exception $e) {
            $msgError = "⚠️ *Copia exitosa pero error al truncar Semana*\n"
                      . "Se copiaron {$rowsCopied} registros pero no se pudo limpiar la tabla.\n"
                      . "Fecha: {$fecha}\n"
                      . "Usuario: {$usuario}\n"
                      . "Detalle: " . $e->getMessage();
            self::enviarWebhook($webhookUrl, $msgError);
            throw new \Exception('Copia exitosa (' . $rowsCopied . ' registros) pero error al truncar: ' . $e->getMessage());
        }

        // Paso 4: Notificar truncado exitoso
        $msgTruncar = "🧹 *Tabla Semana truncada exitosamente*\n"
                    . "Se eliminaron todos los registros de `tbl_segundometro_semana` después de copiar {$rowsCopied} registros al historial.\n"
                    . "Fecha: {$fecha}\n"
                    . "Usuario: {$usuario}";
        self::enviarWebhook($webhookUrl, $msgTruncar);

        return [
            'success' => true,
            'mensaje' => "Proceso completado: {$rowsCopied} registros copiados y tabla truncada.",
            'registros_copiados' => $rowsCopied
        ];
    }

    /**
     * Conteos por Bucket_Morosidad para un crédito en tbl_segundometro_histo (__SPARTA_SECRET_REDACTED__, __SPARTA_HOST_REDACTED__).
     * Útil para el modal Gestiones/Pagos: cuántas veces aparece Current, 1 a 7 días, etc.
     *
     * @param int $idCredito ID del crédito
     * @return array Lista de ['bucket' => string, 'cnt' => int]; vacío si error o sin datos
     */
    public static function getBucketMorosidadCounts($idCredito)
    {
        $idCredito = (int) $idCredito;
        if ($idCredito < 1) {
            return [];
        }
        try {
            $db = new DatabaseSegundometro();
            // Columna según tbl_segundometro_histo; si en tu BD se llama Bucket_Morosidad_Real, cámbiala aquí
            $rows = $db->queryAll(
                'SELECT Bucket_Morosidad AS bucket, COUNT(*) AS cnt FROM tbl_segundometro_histo WHERE Id_credito = :id_credito GROUP BY Bucket_Morosidad ORDER BY bucket',
                ['id_credito' => $idCredito]
            );
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Eliminar archivo en el servidor remoto SOLO si el owner es root (nosotros).
     * Seguridad: solo se elimina el archivo cuyo nombre coincide exactamente y cuyo owner es root.
     * En el servidor, "ls -lt | head" ayuda a ver orden (más reciente primero) y owner (root vs s2).
     * Comando ejecutado: cd DIR && sudo rm 'nombre_archivo' (un solo archivo, nunca glob).
     *
     * @param string $nombreArchivo Nombre exacto del archivo a eliminar (ej. mega_rpt_20260128_16_31_21.csv.zip)
     * @return array ['success' => true, 'mensaje' => ...]
     */
    public static function eliminarArchivo($nombreArchivo)
    {
        if (!preg_match('/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/', $nombreArchivo)) {
            throw new \Exception('Formato de archivo inválido');
        }

        // Listar SOLO este archivo (nombre entre comillas para no expandir glob)
        $comandoLs = sprintf(
            "cd %s && ls -l %s 2>/dev/null",
            escapeshellarg(self::$DIRECTORIO_REMOTO),
            escapeshellarg($nombreArchivo)
        );
        $resultado = self::ejecutarSSH($comandoLs);
        
        if (!$resultado['success'] || empty(trim($resultado['output']))) {
            throw new \Exception('El archivo no existe en el servidor');
        }
        
        // Extraer solo las líneas que son de un archivo (formato ls: permisos links owner group size ... nombre)
        $lineas = array_filter(array_map('trim', explode("\n", $resultado['output'])));
        $lineasQueCoinciden = [];
        foreach ($lineas as $l) {
            if (!preg_match('/^[-d]/', $l)) {
                continue; // saltar "total N"
            }
            $partes = preg_split('/\s+/', $l, 9);
            if (count($partes) < 9) {
                continue;
            }
            $nombreEnLinea = basename($partes[8]);
            if ($nombreEnLinea === $nombreArchivo) {
                $lineasQueCoinciden[] = $l;
            }
        }
        
        // CRÍTICO: debe haber exactamente una línea que corresponda a este archivo
        if (count($lineasQueCoinciden) !== 1) {
            throw new \Exception('No se pudo identificar de forma única el archivo a eliminar (se encontraron ' . count($lineasQueCoinciden) . ' coincidencias). No se eliminó nada.');
        }
        
        $linea = $lineasQueCoinciden[0];
        $partes = preg_split('/\s+/', $linea, 9);
        $owner = trim($partes[2]);
        
        if (strtolower($owner) !== 'root') {
            throw new \Exception('Solo se pueden eliminar archivos propios (nosotros). Este archivo es del proveedor (owner: ' . $owner . ').');
        }
        
        // Eliminar ÚNICAMENTE este archivo: sudo rm 'nombre_archivo' (sin asteriscos ni glob)
        $nombreEscapadoRemoto = str_replace("'", "'\\''", $nombreArchivo);
        $comandoRm = "cd " . self::$DIRECTORIO_REMOTO . " && sudo rm '" . $nombreEscapadoRemoto . "' 2>&1";
        $resRm = self::ejecutarSSH($comandoRm);
        
        if (!$resRm['success']) {
            throw new \Exception('Error al eliminar el archivo: ' . $resRm['error']);
        }
        
        return ['success' => true, 'mensaje' => 'Archivo eliminado correctamente'];
    }
    
    /**
     * Normalizar tamaño de archivo (de formato ls a formato legible)
     */
    private static function normalizarTamano($tamanoStr)
    {
        // Formato de ls puede ser: "1.2M", "500K", "2.5G", etc.
        return trim($tamanoStr);
    }
    
    /**
     * Convertir tamaño de formato ls a bytes
     */
    private static function convertirTamanoABytes($tamanoStr)
    {
        $tamanoStr = trim($tamanoStr);
        if (empty($tamanoStr)) return 0;
        
        $unidad = strtoupper(substr($tamanoStr, -1));
        $numero = (float)substr($tamanoStr, 0, -1);
        
        switch ($unidad) {
            case 'K': return $numero * 1024;
            case 'M': return $numero * 1024 * 1024;
            case 'G': return $numero * 1024 * 1024 * 1024;
            case 'T': return $numero * 1024 * 1024 * 1024 * 1024;
            default: return (int)$numero;
        }
    }
    
    /**
     * Formatear tamaño de archivo en formato legible
     */
    private static function formatearTamano($bytes)
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $unidades[$i];
    }
    
    /**
     * Configurar parámetros SSH
     */
    public static function configurarSSH($host, $user, $key, $directorio)
    {
        self::$SSH_HOST = $host;
        self::$SSH_USER = $user;
        self::$SSH_KEY = $key;
        self::$DIRECTORIO_REMOTO = $directorio;
    }

    // ========== MÉTODOS NUEVOS (no modifican lógica existente) ==========

    /**
     * Ejecutar ls -lt | head en el directorio remoto (misma conexión SSH).
     * @return array ['success' => bool, 'output' => string]
     */
    public static function obtenerSalidaLsLtHead()
    {
        $dir = str_replace("'", "'\\''", self::$DIRECTORIO_REMOTO);
        $comando = sprintf("bash -c 'cd %s && ls -lt | head'", $dir);
        $resultado = self::ejecutarSSH($comando);
        return [
            'success' => $resultado['success'],
            'output'   => $resultado['output'] ?? '',
            'error'    => $resultado['error'] ?? ''
        ];
    }

    /**
     * Devuelve el comando shell completo para ejecutar monitorear.sh por streaming (timeout 1 h en remoto).
     * Usado por el controlador para proc_open y enviar SSE. Retorna null si SSH no está disponible.
     * Nota: getSSHCommand() ya se encarga del fallback Plink->OpenSSH cuando .ppk no existe.
     * @return string|null
     */
    public static function getComandoMonitorearParaStream()
    {
        $sshCommand = self::getSSHCommand();
        if ($sshCommand === null) {
            return null;
        }
        $isPlink = (stripos($sshCommand, 'plink') !== false);
        $comandoRemoto = 'timeout 3600 sudo bash /home/jesus/scripts/monitorear.sh 2>&1';
        if ($isPlink) {
            $configFile = __DIR__ . '/../config/config.ini';
            $hostkey = '';
            if (is_file($configFile)) {
                $cfg = @parse_ini_file($configFile, true);
                $hk = trim($cfg['ssh']['ssh_hostkey'] ?? '');
                if ($hk !== '') {
                    $hostkey = ' -hostkey ' . escapeshellarg($hk);
                }
            }
            return sprintf(
                '%s -i %s%s -t -batch %s@%s %s',
                escapeshellarg($sshCommand),
                escapeshellarg(self::getSSHKey(true)),
                $hostkey,
                self::$SSH_USER,
                self::$SSH_HOST,
                escapeshellarg($comandoRemoto)
            );
        }
        $knownHosts = self::getSSHKnownHostsFile();
        return sprintf(
            '%s -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=%s -o ConnectTimeout=20 -o ServerAliveInterval=5 %s@%s %s',
            escapeshellarg($sshCommand),
            escapeshellarg(self::getSSHKey(false)),
            $knownHosts,
            self::$SSH_USER,
            self::$SSH_HOST,
            escapeshellarg($comandoRemoto)
        );
    }

    /**
     * Ejecutar monitorear.sh en el servidor remoto (timeout corto, una sola respuesta).
     * Para el panel Monitorear: sin streaming, la petición termina en unos segundos y no bloquea al cambiar de menú.
     * @param int $segundos Timeout en el servidor remoto (default 10)
     * @return array ['success' => bool, 'output' => string, 'error' => string]
     */
    public static function obtenerSalidaMonitorearCorto($segundos = 10)
    {
        $comando = sprintf('timeout %d sudo bash /home/jesus/scripts/monitorear.sh 2>&1', max(5, min(30, (int) $segundos)));
        $resultado = self::ejecutarSSH($comando);
        return [
            'success' => $resultado['success'],
            'output'   => $resultado['output'] ?? '',
            'error'    => $resultado['error'] ?? ''
        ];
    }

    /**
     * Ejecutar monitorear.sh en el servidor remoto para ver el proceso (sin timeout, para uso manual/CLI).
     * @return array ['success' => bool, 'output' => string]
     */
    public static function obtenerSalidaMonitorear()
    {
        $comando = 'sudo bash /home/jesus/scripts/monitorear.sh 2>&1';
        $resultado = self::ejecutarSSH($comando);
        return [
            'success' => $resultado['success'],
            'output'   => $resultado['output'] ?? '',
            'error'    => $resultado['error'] ?? ''
        ];
    }

    /**
     * Leer archivo de estado del último proceso Kronos (insertar_kronos.py).
     * El script en el servidor debe escribir OK, ERROR o RUNNING en ese archivo.
     * Ruta esperada: /home/jesus/scripts/ultimo_estado_kronos.txt
     * @return array ['success' => bool, 'status' => 'ok'|'error'|'running'|'unknown', 'message' => string]
     */
    public static function leerEstadoProcesoKronos()
    {
        $rutaEstado = '/home/jesus/scripts/ultimo_estado_kronos.txt';
        $comando = sprintf('cat %s 2>/dev/null || echo ""', escapeshellarg($rutaEstado));
        $resultado = self::ejecutarSSH($comando);
        $contenido = trim($resultado['output'] ?? '');
        $status = 'unknown';
        $message = $contenido;
        if ($contenido !== '') {
            $linea = strtolower(trim(explode("\n", $contenido)[0]));
            if (strpos($linea, 'ok') === 0 || $linea === 'ok') {
                $status = 'ok';
            } elseif (strpos($linea, 'error') === 0 || strpos($linea, 'fail') !== false) {
                $status = 'error';
            } elseif (strpos($linea, 'running') === 0 || strpos($linea, 'procesando') !== false) {
                $status = 'running';
            } else {
                $status = 'unknown';
            }
        }
        return [
            'success' => true,
            'status'  => $status,
            'message' => $message
        ];
    }

    /**
     * Slots de hora en tbl_segundometro_semana (columnas Dias_mora_Dia_HH_MM).
     * La BD solo tiene estos horarios, no cada minuto.
     */
    private static $SLOTS_SEGUNDOMETRO = ['07_30', '09_30', '11_30', '13_30', '14_30', '16_30', '18_30', '20_30', '23_50'];

    /**
     * Dado hora y minuto del archivo, devuelve el slot de la BD (ej. 16_31 → 16_30).
     * Regla: último slot <= (hh, mm); si es antes de 07:30, se usa 07_30.
     *
     * @param string $hh Hora 00-23
     * @param string $mm Minuto 00-59
     * @return string Slot en formato HH_MM
     */
    private static function horaArchivoASlot($hh, $mm)
    {
        $minutosActual = (int) $hh * 60 + (int) $mm;
        $slotElegido = self::$SLOTS_SEGUNDOMETRO[0];
        foreach (self::$SLOTS_SEGUNDOMETRO as $slot) {
            list($sh, $sm) = explode('_', $slot);
            $minutosSlot = (int) $sh * 60 + (int) $sm;
            if ($minutosSlot <= $minutosActual) {
                $slotElegido = $slot;
            }
        }
        return $slotElegido;
    }

    /**
     * Segundos desde la hora del reporte para considerar "procesando" antes de consultar BD.
     * Tras este tiempo se consulta la BD y se muestra ok/error.
     */
    private static $ESTADO_PROCESANDO_SEGUNDOS = 240; // 4 minutos

    /**
     * Estado de reportes consultando solo BD (sin SSH).
     * BD: __SPARTA_SECRET_REDACTED__, tabla: tbl_segundometro_semana, columnas Dias_mora_Dia_HH_MM con slots fijos.
     * La hora del archivo se mapea al slot correspondiente (ej. 16_31 → 16_30).
     * Durante 4 min desde la hora del reporte → procesando; después, si hay datos → ok, si no → error.
     *
     * @param array $nombresArchivo Lista de nombres (ej. mega_rpt_20260128_16_31_21.csv.zip)
     * @return array ['nombre_archivo' => 'ok'|'error'|'procesando']
     */
    public static function obtenerEstadoReportesPorBD(array $nombresArchivo)
    {
        $estados = [];
        $queriesPorColumna = [];
        $diaNombre = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
        ];
        $tz = new \DateTimeZone('America/Mexico_City');
        $ahora = new \DateTime('now', $tz);

        foreach ($nombresArchivo as $nombre) {
            $nombre = trim((string) $nombre);
            if ($nombre === '') {
                continue;
            }
            if (!preg_match('/mega_rpt_(\d{8})_(\d{2})_(\d{2})_\d{2}\.csv\.zip/', $nombre, $m)) {
                $estados[$nombre] = 'error';
                continue;
            }
            $fecha = $m[1];
            $hh = $m[2];
            $mm = $m[3];
            $tiempoReporte = \DateTime::createFromFormat('Ymd H:i:s', $fecha . ' ' . $hh . ':' . $mm . ':00', $tz);
            if (!$tiempoReporte) {
                $estados[$nombre] = 'error';
                continue;
            }
            $diaNum = (int) $tiempoReporte->format('N');
            $diaStr = $diaNombre[$diaNum] ?? 'Lunes';
            $slot = self::horaArchivoASlot($hh, $mm);
            $columna = 'Dias_mora_' . $diaStr . '_' . $slot;
            $diffSegundos = $ahora->getTimestamp() - $tiempoReporte->getTimestamp();
            if ($diffSegundos < self::$ESTADO_PROCESANDO_SEGUNDOS) {
                $estados[$nombre] = 'procesando';
                continue;
            }
            $queriesPorColumna[$columna][] = $nombre;
        }

        if (empty($queriesPorColumna)) {
            return $estados;
        }

        try {
            $db = new DatabaseSegundometro();
            foreach ($queriesPorColumna as $columna => $nombres) {
                $colEscapada = '`' . str_replace('`', '``', $columna) . '`';
                $sql = "SELECT 1 FROM tbl_segundometro_semana WHERE $colEscapada IS NOT NULL LIMIT 1";
                $row = $db->queryOne($sql);
                $resultado = $row ? 'ok' : 'error';
                foreach ($nombres as $nombre) {
                    $estados[$nombre] = $resultado;
                }
            }
        } catch (\Exception $e) {
            foreach (array_keys($queriesPorColumna) as $columna) {
                foreach ($queriesPorColumna[$columna] as $nombre) {
                    $estados[$nombre] = 'error';
                }
            }
        }
        return $estados;
    }

    /**
     * Diagnóstico completo de conectividad SSH para el Shell Segundómetro.
     * Prueba: config, detección de método, clave, conectividad, permisos, listado, descarga, monitoreo.
     * @return array Lista de pruebas con ['nombre', 'ok', 'detalle']
     */
    public static function diagnosticoSSH()
    {
        $resultados = [];
        $configFile = __DIR__ . '/../config/config.ini';
        $config = (is_file($configFile) && is_array($c = @parse_ini_file($configFile, true))) ? $c : [];
        $sshSection = $config['ssh'] ?? [];

        // 1. Configuración leída
        $usePlink = !empty($sshSection['ssh_use_plink']);
        $resultados[] = [
            'nombre' => 'config.ini [ssh]',
            'ok' => true,
            'detalle' => 'ssh_use_plink = ' . ($usePlink ? '1 (Plink preferido)' : '0 (OpenSSH)'),
            'ayuda' => 'PHP puede leer la configuración SSH',
            'grupo' => 'local',
        ];

        // 2. Llave .ppk
        $ppkConfig = trim($sshSection['ssh_key_plink'] ?? '');
        $ppkProyecto = __DIR__ . '/../config/ssh/jesusssh4.ppk';
        $ppkExiste = ($ppkConfig !== '' && @is_file($ppkConfig)) || @is_file($ppkProyecto);
        $ppkUsada = ($ppkConfig !== '' && @is_file($ppkConfig)) ? $ppkConfig : ((@is_file($ppkProyecto)) ? $ppkProyecto : ($ppkConfig ?: $ppkProyecto));
        $resultados[] = [
            'nombre' => 'Llave .ppk',
            'ok' => $ppkExiste,
            'detalle' => $ppkExiste
                ? 'Encontrada: ' . $ppkUsada
                : 'NO encontrada (' . ($ppkConfig ?: $ppkProyecto) . '). Si ssh_use_plink=1, se hará fallback a OpenSSH.',
            'ayuda' => 'El archivo de clave Plink existe en disco',
            'grupo' => 'local',
        ];

        // 3. Llave OpenSSH (.unknown / PEM)
        $keyOpenSSH = trim($sshSection['ssh_key'] ?? '');
        $keyProyecto = __DIR__ . '/../config/ssh/jesusssh4.unknown';
        $opensshExiste = ($keyOpenSSH !== '' && @is_file($keyOpenSSH)) || @is_file($keyProyecto);
        $opensshUsada = ($keyOpenSSH !== '' && @is_file($keyOpenSSH)) ? $keyOpenSSH : ((@is_file($keyProyecto)) ? $keyProyecto : ($keyOpenSSH ?: $keyProyecto));
        $resultados[] = [
            'nombre' => 'Llave OpenSSH',
            'ok' => $opensshExiste,
            'detalle' => $opensshExiste
                ? 'Encontrada: ' . $opensshUsada
                : 'NO encontrada (' . ($keyOpenSSH ?: $keyProyecto) . ')',
            'ayuda' => 'El archivo de clave OpenSSH existe en disco',
            'grupo' => 'local',
        ];

        // 4. Método elegido
        $sshCmd = self::getSSHCommand();
        $isPlink = $sshCmd !== null && (stripos($sshCmd, 'plink') !== false);
        $metodo = $sshCmd === null ? 'NINGUNO' : ($isPlink ? 'Plink (PuTTY)' : 'OpenSSH');
        $fallback = ($usePlink && !$isPlink && $sshCmd !== null);
        $detalleMetodo = 'Ejecutable: ' . ($sshCmd ?? 'no encontrado');
        if ($fallback) {
            $detalleMetodo .= ' | FALLBACK activo: config dice Plink pero .ppk no existe, usando OpenSSH';
        }
        $resultados[] = [
            'nombre' => 'Método SSH elegido',
            'ok' => $sshCmd !== null,
            'detalle' => $metodo . ' | ' . $detalleMetodo,
            'ayuda' => 'La lógica Plink/OpenSSH/Fallback funciona correctamente',
            'grupo' => 'local',
        ];

        // 5. Clave usada
        $keyFinal = $sshCmd !== null ? self::getSSHKey($isPlink) : null;
        $keyFinalExiste = $keyFinal !== null && @is_file($keyFinal);
        $resultados[] = [
            'nombre' => 'Clave SSH activa',
            'ok' => $keyFinalExiste,
            'detalle' => ($keyFinal ?? 'N/A') . ' | Existe: ' . ($keyFinalExiste ? 'SÍ' : 'NO'),
            'ayuda' => 'La clave que se va a usar realmente existe',
            'grupo' => 'local',
        ];

        // 6. Plink.exe disponible
        $plinkPath = trim($sshSection['ssh_command_plink'] ?? '');
        $plinkDefault = 'C:\\xampp\\plink.exe';
        $plinkDisponible = ($plinkPath !== '' && @is_file($plinkPath)) || @is_file($plinkDefault);
        $resultados[] = [
            'nombre' => 'Plink.exe disponible',
            'ok' => $plinkDisponible,
            'detalle' => $plinkDisponible
                ? 'Encontrado: ' . ((@is_file($plinkPath) ? $plinkPath : $plinkDefault))
                : 'NO encontrado (' . ($plinkPath ?: $plinkDefault) . ')',
            'ayuda' => 'Ejecutable Plink disponible',
            'grupo' => 'local',
        ];

        // 7. OpenSSH ssh.exe disponible
        $sshPath = trim($sshSection['ssh_command'] ?? '');
        $sshDefault = 'C:\\Windows\\System32\\OpenSSH\\ssh.exe';
        $sshDisponible = ($sshPath !== '' && @is_file($sshPath)) || @is_file($sshDefault);
        $resultados[] = [
            'nombre' => 'OpenSSH ssh.exe disponible',
            'ok' => $sshDisponible,
            'detalle' => $sshDisponible
                ? 'Encontrado: ' . ((@is_file($sshPath) ? $sshPath : $sshDefault))
                : 'NO encontrado',
            'ayuda' => 'Ejecutable OpenSSH disponible (para fallback)',
            'grupo' => 'local',
        ];

        // 8. pscp.exe disponible
        $pscpPath = trim($sshSection['ssh_pscp'] ?? '');
        $pscpDefault = 'C:\\xampp\\pscp.exe';
        $pscpDisponible = ($pscpPath !== '' && @is_file($pscpPath)) || @is_file($pscpDefault);
        $resultados[] = [
            'nombre' => 'pscp.exe disponible (descarga Plink)',
            'ok' => $pscpDisponible,
            'detalle' => $pscpDisponible
                ? 'Encontrado: ' . ((@is_file($pscpPath) ? $pscpPath : $pscpDefault))
                : 'NO encontrado (si se usa Plink, descarga usará fallback plink+cat)',
            'ayuda' => 'Ejecutable para descargar archivos disponible',
            'grupo' => 'local',
        ];

        if ($sshCmd === null) {
            $resultados[] = ['nombre' => 'Conectividad SSH', 'ok' => false, 'detalle' => 'No se puede probar: SSH/Plink no disponible', 'ayuda' => '', 'grupo' => 'remoto'];
            return $resultados;
        }

        // 9-17. Todas las pruebas remotas en UN SOLO comando SSH (evita rate limiting del servidor).
        // Plink en Windows no maneja saltos de línea en el argumento; usar separadores ";" en una sola línea.
        $dir = self::$DIRECTORIO_REMOTO;
        $dirEsc = str_replace("'", "'\\''", $dir);
        $tmpFile = '.diag_test_' . time() . '.tmp';
        $scriptRemoto = 'echo ---ECHO_TEST---; echo DIAGNOSTICO_OK;'
            . ' echo ---SUDO_TEST---; sudo echo SUDO_OK 2>&1;'
            . ' echo ---DIR_TEST---; ls -d \'' . $dirEsc . '\' 2>&1;'
            . ' echo ---LS_TEST---; cd \'' . $dirEsc . '\' && ls -lt mega_rpt_*.csv.zip 2>/dev/null | head -5;'
            . ' echo ---LS_END---;'
            . ' echo ---MON_TEST---; test -f /home/jesus/scripts/monitorear.sh && echo MON_EXISTE || echo MON_NO_EXISTE;'
            . ' echo ---MON_EXEC---; sudo bash -n /home/jesus/scripts/monitorear.sh 2>&1 && echo MON_SYNTAX_OK || echo MON_SYNTAX_FAIL;'
            . ' echo ---CP_TEST---; sudo cp --help > /dev/null 2>&1 && echo CP_OK || echo CP_FAIL;'
            . ' echo ---RM_TEST---; sudo rm --help > /dev/null 2>&1 && echo RM_OK || echo RM_FAIL;'
            . ' echo ---WRITE_TEST---; cd \'' . $dirEsc . '\' && sudo touch ' . $tmpFile . ' 2>&1 && sudo rm -f ' . $tmpFile . ' 2>&1 && echo WRITE_OK || echo WRITE_FAIL;'
            . ' echo ---FIN_DIAG---';
        $testAll = self::ejecutarSSH($scriptRemoto);
        $rawOutput = $testAll['output'] ?? '';

        $extraerSeccion = function ($marca, $marcaFin = null) use ($rawOutput) {
            $inicio = strpos($rawOutput, $marca);
            if ($inicio === false) return '';
            $inicio += strlen($marca);
            if ($marcaFin !== null) {
                $fin = strpos($rawOutput, $marcaFin, $inicio);
                if ($fin === false) return trim(substr($rawOutput, $inicio));
                return trim(substr($rawOutput, $inicio, $fin - $inicio));
            }
            $fin = strpos($rawOutput, '---', $inicio);
            if ($fin === false) return trim(substr($rawOutput, $inicio));
            return trim(substr($rawOutput, $inicio, $fin - $inicio));
        };

        // 9. Conectividad
        $secEcho = $extraerSeccion('---ECHO_TEST---');
        $echoOk = strpos($secEcho, 'DIAGNOSTICO_OK') !== false;
        $resultados[] = [
            'nombre' => 'Conectividad SSH (echo)',
            'ok' => $echoOk,
            'detalle' => $echoOk ? 'Conexión exitosa al servidor ' . self::$SSH_HOST . ' como ' . self::$SSH_USER : 'FALLO: ' . ($testAll['error'] ?: $rawOutput ?: 'sin respuesta'),
            'ayuda' => 'La llave SSH funciona, el servidor acepta la conexión',
            'cubre' => 'Listar, Copiar, Eliminar, Descargar, Monitorear',
            'grupo' => 'remoto',
        ];

        if (!$echoOk) {
            $resultados[] = ['nombre' => 'Pruebas restantes', 'ok' => false, 'detalle' => 'Omitidas porque la conexión básica falló', 'ayuda' => '', 'grupo' => 'remoto'];
            return $resultados;
        }

        // 10. sudo
        $secSudo = $extraerSeccion('---SUDO_TEST---');
        $sudoOk = strpos($secSudo, 'SUDO_OK') !== false;
        $resultados[] = [
            'nombre' => 'Permisos sudo',
            'ok' => $sudoOk,
            'detalle' => $sudoOk ? 'sudo funciona sin contraseña (NOPASSWD)' : 'FALLO: ' . $secSudo,
            'ayuda' => 'El usuario puede ejecutar comandos como root',
            'cubre' => 'Copiar, Eliminar, Monitorear',
            'grupo' => 'remoto',
        ];

        // 11. Directorio
        $secDir = $extraerSeccion('---DIR_TEST---');
        $dirOk = strpos($secDir, $dir) !== false;
        $resultados[] = [
            'nombre' => 'Directorio remoto',
            'ok' => $dirOk,
            'detalle' => $dirOk ? 'Accesible: ' . $dir : 'FALLO: ' . $secDir,
            'ayuda' => 'El directorio /home/usuariossftp/s2/mega_reporte existe',
            'cubre' => 'Listar, Copiar, Eliminar',
            'grupo' => 'remoto',
        ];

        // 12. Listar archivos
        $secLs = $extraerSeccion('---LS_TEST---', '---LS_END---');
        $lsOk = $secLs !== '';
        $lineas = $lsOk ? count(array_filter(explode("\n", $secLs))) : 0;
        $resultados[] = [
            'nombre' => 'Listar archivos (ls -lt)',
            'ok' => $lsOk,
            'detalle' => $lsOk ? $lineas . ' archivo(s) encontrados' : 'Sin archivos mega_rpt_*.csv.zip (directorio vacío o sin reportes recientes)',
            'ayuda' => 'Hay archivos mega_rpt_*.csv.zip y se pueden leer',
            'cubre' => 'Listar archivos',
            'grupo' => 'remoto',
        ];

        // 13. monitorear.sh existe
        $secMon = $extraerSeccion('---MON_TEST---');
        $monExiste = strpos($secMon, 'MON_EXISTE') !== false && strpos($secMon, 'MON_NO_EXISTE') === false;
        $resultados[] = [
            'nombre' => 'Script monitorear.sh (existe)',
            'ok' => $monExiste,
            'detalle' => $monExiste ? 'Existe en /home/jesus/scripts/monitorear.sh' : 'NO encontrado en el servidor',
            'ayuda' => 'El script de monitoreo está en el servidor',
            'cubre' => 'Monitorear',
            'grupo' => 'remoto',
        ];

        // 14. monitorear.sh ejecutable
        $secMonExec = $extraerSeccion('---MON_EXEC---');
        $monSyntaxOk = strpos($secMonExec, 'MON_SYNTAX_OK') !== false;
        $resultados[] = [
            'nombre' => 'Script monitorear.sh (ejecutable)',
            'ok' => $monSyntaxOk,
            'detalle' => $monSyntaxOk ? 'Sintaxis bash correcta y ejecutable con sudo' : 'FALLO: ' . ($monExiste ? $secMonExec : 'no existe'),
            'ayuda' => 'El script no tiene errores de sintaxis bash',
            'cubre' => 'Monitorear',
            'grupo' => 'remoto',
        ];

        // 15. sudo cp
        $secCp = $extraerSeccion('---CP_TEST---');
        $cpOk = strpos($secCp, 'CP_OK') !== false;
        $resultados[] = [
            'nombre' => 'Permiso sudo cp (copiar +1s)',
            'ok' => $cpOk,
            'detalle' => $cpOk ? 'sudo cp disponible' : 'FALLO: ' . $secCp,
            'ayuda' => 'El comando copiar funciona con permisos root',
            'cubre' => 'Copiar +1s',
            'grupo' => 'remoto',
        ];

        // 16. sudo rm
        $secRm = $extraerSeccion('---RM_TEST---');
        $rmOk = strpos($secRm, 'RM_OK') !== false;
        $resultados[] = [
            'nombre' => 'Permiso sudo rm (eliminar)',
            'ok' => $rmOk,
            'detalle' => $rmOk ? 'sudo rm disponible' : 'FALLO: ' . $secRm,
            'ayuda' => 'El comando eliminar funciona con permisos root',
            'cubre' => 'Eliminar',
            'grupo' => 'remoto',
        ];

        // 17. Escritura real
        $secWrite = $extraerSeccion('---WRITE_TEST---');
        $writeOk = strpos($secWrite, 'WRITE_OK') !== false;
        $resultados[] = [
            'nombre' => 'Escritura en directorio remoto',
            'ok' => $writeOk,
            'detalle' => $writeOk
                ? 'sudo touch/rm funciona en ' . $dir . ' (copiar y eliminar archivos OK)'
                : 'FALLO: no se puede escribir/borrar en el directorio: ' . $secWrite,
            'ayuda' => 'Crea y borra un archivo temporal en el directorio real',
            'cubre' => 'Copiar +1s, Eliminar',
            'grupo' => 'remoto',
        ];

        // 18. Descarga
        $descargaOk = false;
        $descargaDetalle = '';
        if ($isPlink) {
            if ($pscpDisponible) {
                $descargaOk = true;
                $descargaDetalle = 'pscp disponible para descarga';
            } else {
                $descargaOk = true;
                $descargaDetalle = 'pscp NO disponible, se usará fallback plink+cat (funcional pero más lento)';
            }
        } else {
            $scpTest = @shell_exec('where.exe scp 2>&1');
            $scpPath = $scpTest ? trim(explode("\n", $scpTest)[0]) : '';
            $descargaOk = ($scpPath !== '' && @is_file($scpPath));
            $descargaDetalle = $descargaOk ? 'scp encontrado: ' . $scpPath : 'scp NO encontrado en el sistema';
        }
        $resultados[] = [
            'nombre' => 'Capacidad de descarga (local)',
            'ok' => $descargaOk,
            'detalle' => $descargaDetalle,
            'ayuda' => 'pscp/scp existe para transferir archivos',
            'cubre' => 'Descargar',
            'grupo' => 'bd',
        ];

        // 19. BD MySQL
        $dbOk = false;
        $dbDetalle = '';
        try {
            $db = new DatabaseSegundometro();
            $row = $db->queryOne('SELECT 1 AS test');
            $dbOk = ($row && isset($row['test']) && $row['test'] == 1);
            $dbDetalle = $dbOk ? 'Conexión MySQL exitosa (__SPARTA_HOST_REDACTED__, __SPARTA_SECRET_REDACTED__)' : 'Conectó pero SELECT 1 falló';
        } catch (\Throwable $e) {
            $dbDetalle = 'FALLO: ' . $e->getMessage();
        }
        $resultados[] = [
            'nombre' => 'Base de datos (Truncar/Estado)',
            'ok' => $dbOk,
            'detalle' => $dbDetalle,
            'ayuda' => 'Conexión a __SPARTA_HOST_REDACTED__ __SPARTA_SECRET_REDACTED__ funciona',
            'cubre' => 'Truncar, Estado reportes',
            'grupo' => 'bd',
        ];

        // 20. Tabla Semana
        $tablaOk = false;
        $tablaDetalle = '';
        if ($dbOk) {
            try {
                $db2 = new DatabaseSegundometro();
                $row2 = $db2->queryOne('SELECT COUNT(*) AS total FROM tbl_segundometro_semana');
                $tablaOk = ($row2 !== null);
                $tablaDetalle = $tablaOk ? 'tbl_segundometro_semana accesible (' . ($row2['total'] ?? 0) . ' registros)' : 'Tabla no accesible';
            } catch (\Throwable $e) {
                $tablaDetalle = 'FALLO: ' . $e->getMessage();
            }
        } else {
            $tablaDetalle = 'Omitida: BD no disponible';
        }
        $resultados[] = [
            'nombre' => 'Tabla Semana (Truncar)',
            'ok' => $tablaOk,
            'detalle' => $tablaDetalle,
            'ayuda' => 'tbl_segundometro_semana existe y es accesible',
            'cubre' => 'Truncar',
            'grupo' => 'bd',
        ];

        return $resultados;
    }
}
