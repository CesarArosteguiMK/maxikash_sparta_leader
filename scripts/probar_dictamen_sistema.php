<?php
/**
 * Prueba por consola el dictamen del sistema para un id_ticket.
 * No usa sesión: llama directo al modelo (misma BD que la app).
 *
 * Uso:
 *   php scripts/probar_dictamen_sistema.php ID_TICKET
 *   php scripts/probar_dictamen_sistema.php ID_TICKET --generar
 *
 * --generar  Ejecuta generarDictamenSistema (como el botón robot).
 * Sin flag: solo muestra getDictamenSistema (estado actual en BD).
 */

if (php_sapi_name() !== 'cli') {
    echo "Solo CLI.\n";
    exit(1);
}

$idTicket = isset($argv[1]) ? (int) $argv[1] : 0;
$generar = in_array('--generar', $argv, true);

if ($idTicket < 1) {
    echo "Uso: php scripts/probar_dictamen_sistema.php ID_TICKET [--generar]\n";
    exit(1);
}

define('RAIZ', dirname(__DIR__) . '/backend');
define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('LIBRERIAS', RAIZ . '/Libs');
define('CONTROLADORES', RAIZ . '/controllers');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');
define('LOGIN', 'Login');
define('VISTA_DEFECTO', 'Inicio');
define('METODO_DEFECTO', 'index');

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once LIBRERIAS . '/PhpSpreadsheet/vendor/autoload.php';
spl_autoload_register(function ($archivo) {
    if (strpos($archivo, 'PhpOffice\\') === 0 ||
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }
    $archivo = str_replace('\\', '/', $archivo);
    $ruta = RAIZ . '/' . $archivo . '.php';
    if (!file_exists($ruta)) {
        throw new Exception("Autoload no encontró: $ruta");
    }
    require_once $ruta;
});
require_once RAIZ . '/config/config.php';
date_default_timezone_set('America/Mexico_City');

use Models\Ticket as TicketDAO;

echo "id_ticket = $idTicket\n";

if ($generar) {
    echo "Ejecutando generarDictamenSistema...\n";
    $r = TicketDAO::generarDictamenSistema($idTicket);
} else {
    echo "Consultando getDictamenSistema...\n";
    $r = TicketDAO::getDictamenSistema($idTicket);
}

echo "success: " . (($r['success'] ?? false) ? 'true' : 'false') . "\n";
echo "mensaje: " . ($r['mensaje'] ?? '') . "\n";
if (!empty($r['datos'])) {
    echo "datos:\n";
    echo json_encode($r['datos'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
if (!empty($r['debug'])) {
    echo "debug: " . $r['debug'] . "\n";
}

exit(($r['success'] ?? false) ? 0 : 2);
