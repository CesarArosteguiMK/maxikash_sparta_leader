<?php
/**
 * Script: Copia Semana → Historial, notifica por webhook y trunca tabla Semana.
 *
 * Hace lo mismo que el botón "Truncar" del Shell Segundómetro:
 *   1. INSERT INTO tbl_segundometro_histo_prueba SELECT * FROM tbl_segundometro_semana_prueba
 *   2. Envía mensajes al webhook de Google Chat
 *   3. TRUNCATE TABLE tbl_segundometro_semana_prueba
 *
 * Uso:
 *   - Doble clic en truncar_semana_histo.bat (Windows)
 *   - O desde consola: php truncar_semana_histo.php
 *
 * Requiere: PHP con extensiones PDO, pdo_mysql, curl.
 */

date_default_timezone_set('America/Mexico_City');

$raiz = dirname(__DIR__);
require_once $raiz . '/core/DatabaseSegundometro.php';
require_once $raiz . '/core/Model.php';
require_once $raiz . '/models/SegundometroDAO.php';

// Para que el mensaje del webhook indique que fue ejecutado por este script
$_SESSION['usuario'] = 'script_truncar_semana_histo';

$fecha = date('Y-m-d H:i:s');
echo "[{$fecha}] Iniciando proceso: Semana → Historial, luego truncar Semana.\n";

try {
    $resultado = \Models\SegundometroDAO::truncarSemanaAHistorico();
    echo "[{$fecha}] OK - " . $resultado['mensaje'] . "\n";
    echo "Registros copiados: " . ($resultado['registros_copiados'] ?? 0) . "\n";
    exit(0);
} catch (\Throwable $e) {
    echo "[{$fecha}] ERROR - " . $e->getMessage() . "\n";
    exit(1);
}
