<?php
/**
 * Script temporal: extrae el $script de Sabueso::ticket() o ::paneladmin()
 * Uso: php scripts/extraer_script_ticket_sabueso.php [ticket|paneladmin]
 * Guarda en: backend/storage/script_ticket_extraido.js
 */
chdir(dirname(__DIR__));
define('RAIZ', dirname(__DIR__) . '/backend');
define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/controllers');
define('LIBRERIAS', RAIZ . '/Libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');
define('LOGIN', 'Login');
define('VISTA_DEFECTO', 'Inicio');
define('METODO_DEFECTO', 'index');
error_reporting(E_ERROR | E_PARSE);
require_once LIBRERIAS . '/PhpSpreadsheet/vendor/autoload.php';
spl_autoload_register(function ($a) {
    if (strpos($a,'PhpOffice\\')===0||strpos($a,'ZipStream\\')===0||strpos($a,'Psr\\')===0) return;
    $r = RAIZ . '/' . str_replace('\\','/', $a) . '.php';
    if (!file_exists($r)) throw new Exception("Autoload: $r");
    require_once $r;
});
require_once RAIZ . '/config/config.php';
date_default_timezone_set('America/Mexico_City');
if (php_sapi_name()==='cli') {
    if (session_status()===PHP_SESSION_NONE) session_start();
    $_SESSION['login']=true;$_SESSION['usuario_id']=1;$_SESSION['modulos']=[18,19];$_SESSION['persona_id']=1;
}
$vista = isset($argv[1]) ? strtolower(trim($argv[1])) : 'ticket';
if ($vista !== 'paneladmin') $vista = 'ticket';
ob_start();
$sabueso = new Controllers\Sabueso();
$sabueso->$vista();
$html = ob_get_clean();
preg_match_all('/<script[^>]*>([\s\S]*?)<\/script>/s', $html, $matches);
$script = '';
if (!empty($matches[1])) {
    foreach ($matches[1] as $s) {
        $ok = ($vista === 'paneladmin') 
            ? (strpos($s, 'var esAdminTicket = true') !== false && strpos($s, 'tablaTicketsPanel') !== false)
            : (strpos($s, 'var esAdminTicket') !== false && strpos($s, 'tablaTickets') !== false);
        if ($ok) { $script = trim($s); break; }
    }
}
$outDir = RAIZ . '/storage';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);
$outFile = $outDir . '/script_ticket_extraido.js';
file_put_contents($outFile, $script);
$lineas = substr_count($script, "\n") + 1;
echo "Guardado: $outFile ($lineas lineas)\n";
$arr = explode("\n", $script);
if (count($arr) >= 1540) echo "Linea 1540: " . trim($arr[1539]) . "\n";
else echo "La linea 1540 no existe (hay $lineas lineas).\n";