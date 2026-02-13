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
            // Plink exige clave .ppk (PuTTY), no OpenSSH PEM
            $path = trim($config['ssh']['ssh_key_plink'] ?? '');
            if ($path !== '' && @is_file($path)) {
                $cachedPlink = $path;
                return $cachedPlink;
            }
            $ppkProyecto = __DIR__ . '/../config/ssh/jesusssh4.ppk';
            if (@is_file($ppkProyecto)) {
                $cachedPlink = $ppkProyecto;
                return $cachedPlink;
            }
            $cachedPlink = $path !== '' ? $path : $ppkProyecto;
            return $cachedPlink;
        }

        if ($cachedOpenSSH !== null) {
            return $cachedOpenSSH;
        }
        if (@is_file(self::$SSH_KEY)) {
            $cachedOpenSSH = self::$SSH_KEY;
            return $cachedOpenSSH;
        }
        $path = trim($config['ssh']['ssh_key'] ?? '');
        if ($path !== '' && @is_file($path)) {
            $cachedOpenSSH = $path;
            return $cachedOpenSSH;
        }
        $cachedOpenSSH = self::$SSH_KEY;
        return $cachedOpenSSH;
    }
    
    /**
     * Detecta la ruta del ejecutable SSH (cacheada).
     * 1) Si en config.ini existe [ssh] ssh_command (ruta absoluta), se usa esa.
     * 2) Si no, se intenta detectar: en Windows "where ssh", en Linux "which ssh".
     * En el servidor, si "ssh" no está en PATH, añada en config.ini la ruta.
     */
    private static function getSSHCommand()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached === '' ? null : $cached;
        }
        $configFile = __DIR__ . '/../config/config.ini';
        if (is_file($configFile)) {
            $config = @parse_ini_file($configFile, true);
            if (is_array($config)) {
                $usePlink = !empty($config['ssh']['ssh_use_plink']);
                if ($usePlink) {
                    $path = trim($config['ssh']['ssh_command_plink'] ?? $config['ssh']['ssh_command'] ?? '');
                } else {
                    $path = trim($config['ssh']['ssh_command'] ?? '');
                }
                if ($path !== '' && @is_file($path)) {
                    $cached = $path;
                    return $path;
                }
            }
        }
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $candidates = $isWindows 
            ? ['where.exe ssh', 'C:\Windows\System32\OpenSSH\ssh.exe']
            : ['which ssh', '/usr/bin/ssh', '/bin/ssh'];
        
        foreach ($candidates as $cmd) {
            if (strpos($cmd, ' ') !== false) {
                $out = @shell_exec($cmd . ' 2>&1');
                $path = $out ? trim(explode("\n", $out)[0]) : '';
            } else {
                $path = $cmd;
            }
            if ($path !== '' && @is_file($path)) {
                $cached = $path;
                return $path;
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
     * Ejecutar comando SSH remoto
     * 
     * @param string $comando Comando a ejecutar en el servidor remoto
     * @return array ['success' => bool, 'output' => string, 'error' => string]
     */
    private static function ejecutarSSH($comando)
    {
        $sshCommand = self::getSSHCommand();
        if ($sshCommand === null) {
            return [
                'success' => false,
                'output' => '',
                'error' => 'SSH no encontrado. Configure [ssh] ssh_command en backend/config/config.ini con la ruta al ejecutable.',
                'return_code' => 127
            ];
        }

        // Modo por ejecutable real: si la ruta contiene "plink" → Plink (opciones + clave .ppk); si no → OpenSSH
        $isPlink = (stripos($sshCommand, 'plink') !== false);
        $sshKeyEscaped = escapeshellarg(self::getSSHKey($isPlink));
        $comandoEscapado = escapeshellarg($comando);
        
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.ini';
        
        if ($isPlink) {
            // Plink en -batch exige host key cacheada; -hostkey <fingerprint> evita "Cannot confirm a host key in batch mode"
            $hostkey = '';
            if (is_file($configFile)) {
                $cfg = @parse_ini_file($configFile, true);
                $hk = trim($cfg['ssh']['ssh_hostkey'] ?? '');
                if ($hk !== '') {
                    $hostkey = ' -hostkey ' . escapeshellarg($hk);
                }
            }
            $sshComando = sprintf(
                '%s -i %s%s -batch %s@%s %s 2>&1',
                escapeshellarg($sshCommand),
                $sshKeyEscaped,
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
                $sshKeyEscaped,
                $knownHosts,
                self::$SSH_USER,
                self::$SSH_HOST,
                $comandoEscapado
            );
        }
        
        // DEBUG temporal: logging del comando real y resultado
        $logFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'ssh_debug.log';
        @file_put_contents($logFile, "\n=== " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
        @file_put_contents($logFile, "Comando: $sshComando\n", FILE_APPEND);
        
        // exec() devuelve código de salida real: 0 = éxito (sudo cp, sudo rm no imprimen nada y antes se consideraban error)
        $outputLines = [];
        $returnVar = -1;
        exec($sshComando, $outputLines, $returnVar);
        $outputStr = trim(implode("\n", $outputLines));
        
        @file_put_contents($logFile, "Output: " . ($outputStr !== '' ? $outputStr : 'NULL') . "\n", FILE_APPEND);
        @file_put_contents($logFile, "Return code: $returnVar\n", FILE_APPEND);
        
        // Éxito = código 0. Si falla, error = salida del comando (o mensaje genérico si vacío)
        $success = ($returnVar === 0);
        $errorStr = $success ? '' : ($outputStr !== '' ? $outputStr : 'Comando remoto falló (código ' . $returnVar . ')');
        
        return [
            'success' => $success,
            'output' => $outputStr,
            'error' => $errorStr,
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
        
        try {
            // Solo hoy y ayer (2 días: el que vivimos + el anterior)
            $fechaLimite = date('Ymd', strtotime('-1 day'));
            
            // ls -l para obtener owner (col 3), size (col 5), filename (col 9)
            $comandoListar = sprintf(
                "cd %s && ls -l mega_rpt_*.csv.zip 2>/dev/null",
                escapeshellarg(self::$DIRECTORIO_REMOTO)
            );
            
            $resultado = self::ejecutarSSH($comandoListar);
            
            if (!$resultado['success']) {
                error_log("Error SSH al listar archivos: " . $resultado['error']);
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
        // Validar formato del nombre de archivo
        if (!preg_match('/mega_rpt_(\d{8})_(\d{2})_(\d{2})_(\d{2})\.csv\.zip/', $nombreArchivo, $matches)) {
            throw new \Exception('Formato de archivo inválido');
        }
        
        $fecha = $matches[1]; // YYYYMMDD
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
            // Plink: usar pscp (PuTTY SCP) con .ppk y -hostkey
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
     * Devuelve el comando shell completo para ejecutar monitorear.sh por streaming (timeout 45 s en remoto).
     * Usado por el controlador para proc_open y enviar SSE. Retorna null si SSH no está disponible.
     * @return string|null
     */
    public static function getComandoMonitorearParaStream()
    {
        $sshCommand = self::getSSHCommand();
        if ($sshCommand === null) {
            return null;
        }
        $isPlink = (stripos($sshCommand, 'plink') !== false);
        $comandoRemoto = 'timeout 45 sudo bash /home/jesus/scripts/monitorear.sh 2>&1';
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
            // -t fuerza PTY en el servidor para que la salida sea line-buffered y llegue en vivo al stream
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
            if ($diffSegundos < 240) {
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
}
