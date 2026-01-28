<?php

namespace Models;

use Core\Model;

class SegundometroDAO extends Model
{
    /**
     * Directorio donde se almacenan los archivos de reportes
     * IMPORTANTE: Ajustar según la configuración del servidor
     */
    private static $DIRECTORIO_REPORTES = '/var/segundometro/reportes';
    
    /**
     * Obtener archivos de reportes de los últimos N días
     * 
     * @param int $dias Número de días hacia atrás (default: 2)
     * @return array Lista de archivos con información
     */
    public static function obtenerArchivos($dias = 2)
    {
        $archivos = [];
        
        // Validar que el directorio exista
        if (!is_dir(self::$DIRECTORIO_REPORTES)) {
            // Para desarrollo/testing, retornar datos de ejemplo
            return self::generarDatosEjemplo();
        }
        
        try {
            // Obtener todos los archivos .zip del directorio
            $patron = self::$DIRECTORIO_REPORTES . '/mega_rpt_*.csv.zip';
            $archivosEncontrados = glob($patron);
            
            // Calcular fecha límite (hace N días)
            $fechaLimite = date('Ymd', strtotime("-{$dias} days"));
            
            foreach ($archivosEncontrados as $rutaCompleta) {
                $nombreArchivo = basename($rutaCompleta);
                
                // Extraer fecha del nombre del archivo: mega_rpt_YYYYMMDD_HH_MM_SS.csv.zip
                if (preg_match('/mega_rpt_(\d{8})_(\d{2})_(\d{2})_(\d{2})\.csv\.zip/', $nombreArchivo, $matches)) {
                    $fechaArchivo = $matches[1]; // YYYYMMDD
                    $hora = $matches[2];
                    $minuto = $matches[3];
                    $segundo = $matches[4];
                    
                    // Filtrar solo archivos de los últimos N días
                    if ($fechaArchivo >= $fechaLimite) {
                        $archivos[] = [
                            'nombre' => $nombreArchivo,
                            'ruta_completa' => $rutaCompleta,
                            'fecha' => date('Y-m-d', strtotime($fechaArchivo)),
                            'fecha_formato' => date('d/m/Y', strtotime($fechaArchivo)),
                            'hora' => "{$hora}:{$minuto}:{$segundo}",
                            'tamano' => self::formatearTamano(filesize($rutaCompleta)),
                            'tamano_bytes' => filesize($rutaCompleta),
                            'timestamp' => strtotime("{$fechaArchivo} {$hora}:{$minuto}:{$segundo}")
                        ];
                    }
                }
            }
            
            // Ordenar por timestamp descendente (más reciente primero)
            usort($archivos, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
            
        } catch (\Exception $e) {
            error_log("Error al obtener archivos: " . $e->getMessage());
            // En caso de error, retornar datos de ejemplo
            return self::generarDatosEjemplo();
        }
        
        return $archivos;
    }
    
    /**
     * Copiar archivo con +1 segundo en el nombre
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
        $hora = $matches[2];
        $minuto = $matches[3];
        $segundo = (int)$matches[4];
        
        // Incrementar segundo
        $segundoNuevo = $segundo + 1;
        
        // Manejar overflow de segundos (59 -> 00)
        if ($segundoNuevo >= 60) {
            $segundoNuevo = 0;
            $minuto = (int)$minuto + 1;
            
            // Manejar overflow de minutos (59 -> 00)
            if ($minuto >= 60) {
                $minuto = 0;
                $hora = (int)$hora + 1;
                
                // Manejar overflow de horas (23 -> 00)
                if ($hora >= 24) {
                    $hora = 0;
                    // Incrementar fecha si es necesario
                    $fecha = date('Ymd', strtotime($fecha . ' +1 day'));
                }
            }
        }
        
        // Formatear nuevo nombre
        $horaStr = str_pad($hora, 2, '0', STR_PAD_LEFT);
        $minutoStr = str_pad($minuto, 2, '0', STR_PAD_LEFT);
        $segundoStr = str_pad($segundoNuevo, 2, '0', STR_PAD_LEFT);
        $nombreNuevo = "mega_rpt_{$fecha}_{$horaStr}_{$minutoStr}_{$segundoStr}.csv.zip";
        
        // Rutas completas
        $rutaOrigen = self::$DIRECTORIO_REPORTES . '/' . $nombreArchivo;
        $rutaDestino = self::$DIRECTORIO_REPORTES . '/' . $nombreNuevo;
        
        // Verificar que el archivo origen exista
        if (!file_exists($rutaOrigen)) {
            // Para desarrollo/testing, simular éxito
            if (!is_dir(self::$DIRECTORIO_REPORTES)) {
                return [
                    'origen' => $nombreArchivo,
                    'destino' => $nombreNuevo,
                    'ruta_origen' => $rutaOrigen,
                    'ruta_destino' => $rutaDestino,
                    'comando' => "sudo cp {$rutaOrigen} {$rutaDestino}",
                    'simulado' => true
                ];
            }
            throw new \Exception('El archivo origen no existe');
        }
        
        // Verificar que el archivo destino no exista
        if (file_exists($rutaDestino)) {
            throw new \Exception('El archivo destino ya existe');
        }
        
        try {
            // Ejecutar comando sudo cp
            $comando = "sudo cp " . escapeshellarg($rutaOrigen) . " " . escapeshellarg($rutaDestino);
            $output = [];
            $returnVar = 0;
            
            exec($comando . ' 2>&1', $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Error al ejecutar comando: ' . implode("\n", $output));
            }
            
            // Verificar que el archivo se haya creado
            if (!file_exists($rutaDestino)) {
                throw new \Exception('El archivo no se creó correctamente');
            }
            
            return [
                'origen' => $nombreArchivo,
                'destino' => $nombreNuevo,
                'ruta_origen' => $rutaOrigen,
                'ruta_destino' => $rutaDestino,
                'comando' => $comando,
                'tamano' => self::formatearTamano(filesize($rutaDestino))
            ];
            
        } catch (\Exception $e) {
            error_log("Error al copiar archivo: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Formatear tamaño de archivo en formato legible
     * 
     * @param int $bytes Tamaño en bytes
     * @return string Tamaño formateado
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
     * Generar datos de ejemplo para desarrollo/testing
     * 
     * @return array Datos de ejemplo
     */
    private static function generarDatosEjemplo()
    {
        $archivos = [];
        $fechaHoy = date('Ymd');
        $fechaAyer = date('Ymd', strtotime('-1 day'));
        
        $fechas = [$fechaHoy, $fechaAyer];
        $horas = ['07', '09', '11', '13', '15', '17', '19', '21'];
        
        foreach ($fechas as $fecha) {
            foreach ($horas as $hora) {
                $minuto = '31';
                $segundo = rand(50, 59);
                $nombre = "mega_rpt_{$fecha}_{$hora}_{$minuto}_{$segundo}.csv.zip";
                
                $archivos[] = [
                    'nombre' => $nombre,
                    'ruta_completa' => self::$DIRECTORIO_REPORTES . '/' . $nombre,
                    'fecha' => date('Y-m-d', strtotime($fecha)),
                    'fecha_formato' => date('d/m/Y', strtotime($fecha)),
                    'hora' => "{$hora}:{$minuto}:{$segundo}",
                    'tamano' => rand(1, 50) . ' MB',
                    'tamano_bytes' => rand(1048576, 52428800),
                    'timestamp' => strtotime("{$fecha} {$hora}:{$minuto}:{$segundo}")
                ];
            }
        }
        
        // Ordenar por timestamp descendente
        usort($archivos, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        return $archivos;
    }
    
    /**
     * Configurar directorio de reportes personalizado
     * 
     * @param string $directorio Ruta del directorio
     */
    public static function setDirectorioReportes($directorio)
    {
        self::$DIRECTORIO_REPORTES = rtrim($directorio, '/');
    }
}
