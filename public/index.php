<?php

// Se reportan todos los errores y advertencias
// error_reporting(E_ALL);

// Se remueve información sensible de los encabezados HTTP
header_remove('X-Powered-By');
header_remove('Server');

/*
|--------------------------------------------------------------------------
| DEFINICIÓN DE CONSTANTES (ANTES DE AUTOLOAD Y CONFIG)
|--------------------------------------------------------------------------
*/
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

// Solo se reportan los errores y se ignoran las advertencias
error_reporting(E_ERROR | E_PARSE);

require_once LIBRERIAS . '/PhpSpreadsheet/vendor/autoload.php';
/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN GENERAL
|--------------------------------------------------------------------------
*/
spl_autoload_register(function ($archivo) {
    // Ignorar clases que maneja el vendor de PhpSpreadsheet
    if (strpos($archivo, 'PhpOffice\\') === 0 || 
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }
    $archivo = str_replace('\\', '/', $archivo);
    $ruta = RAIZ . "/$archivo.php";

    if (!file_exists($ruta)) {
        throw new Exception("Autoload no encontró: $ruta");
    }

    require_once $ruta;
});
require_once RAIZ . '/config/config.php';

// Configuración de la zona horaria para contemplar horario de verano
$validaHV = new DateTime('now', new DateTimeZone('America/Mexico_City'));
if ($validaHV->format('I')) date_default_timezone_set('America/Mazatlan');
else date_default_timezone_set('America/Mexico_City');

/*
|--------------------------------------------------------------------------
| AUTOLOAD
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SESIÓN
|--------------------------------------------------------------------------
*/
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false, // solo HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

use Core\SessionGuard;

SessionGuard::validar();



/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/
// La URL esperada es de la forma: /controlador/metodo
$urlSolicitada = isset($_GET['url'])
    ? explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL))
    : [''];

if ($urlSolicitada[0] === 'plat_desc') {
    phpinfo();
    exit;
}

// Si la URL solicitada no es un archivo PHP, se verifica su existencia
$extension = pathinfo(end($urlSolicitada), PATHINFO_EXTENSION);
if ($extension !== '' && strtolower($extension) !== 'php') {
    $rutaArchivo = dirname(__DIR__) . '/' . $_GET['url'];
    if (!file_exists($rutaArchivo)) header('HTTP/1.0 404 Not Found');
    exit;
}

/*
|--------------------------------------------------------------------------
| LOGIN FORZADO (excepto sabueso/crearTicketWhatsApp, que usa API key)
|--------------------------------------------------------------------------
*/
$esCrearTicketWhatsApp = isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'sabueso'
    && strtolower($urlSolicitada[1]) === 'crearticketwhatsapp';

if ((!isset($_SESSION['login']) && !$esCrearTicketWhatsApp) || strtolower($urlSolicitada[0]) === strtolower(LOGIN)) {
    $login = 'Controllers\\' . LOGIN;
    $login = new $login;

    $metodo = isset($urlSolicitada[1]) ? $urlSolicitada[1] : METODO_DEFECTO;
    $metodo = strtolower($urlSolicitada[0]) === strtolower(LOGIN)
        ? $metodo
        : METODO_DEFECTO;

    call_user_func_array([$login, $metodo], []);
    exit;
}

/*
|--------------------------------------------------------------------------
| CONTROLADOR NORMAL
|--------------------------------------------------------------------------
*/
$controladorArchivo = $urlSolicitada[0];

if ($controladorArchivo === '' || !file_exists(CONTROLADORES . "/$controladorArchivo.php")) {
    recursoNoDisponible();
}

$controlador = 'Controllers\\' . ucfirst($controladorArchivo);
unset($urlSolicitada[0]);

if (!class_exists($controlador)) recursoNoDisponible();

$controlador = new $controlador;
$metodo = isset($urlSolicitada[1]) ? $urlSolicitada[1] : METODO_DEFECTO;
unset($urlSolicitada[1]);

if (!method_exists($controlador, $metodo)) recursoNoDisponible();

$parametros = count($urlSolicitada) ? array_values($urlSolicitada) : [];
call_user_func_array([$controlador, $metodo], $parametros);

/*
|--------------------------------------------------------------------------
| FUNCIONES AUXILIARES
|--------------------------------------------------------------------------
*/
function recursoNoDisponible()
{
    $headers = apache_request_headers();
    if (isset($headers['Front-Request']) && strtolower($headers['Front-Request']) === 'true') {
        header('HTTP/1.0 404 Not Found');
        echo json_encode(['error' => 'Recurso no disponible']);
        exit;
    }

    if (isset($_SESSION['login'])) header('Location: /' . VISTA_DEFECTO);
    else header('Location: /' . LOGIN);
    exit;
}
