<?php

define('RAIZ', dirname(__DIR__) . '/backend');
var_dump(RAIZ);

echo "
";
echo RAIZ . PHP_EOL;
var_dump(scandir(RAIZ));
var_dump(scandir(RAIZ . '/Controllers'));
echo "
"; exit;


// Solo se reportan los errores y se ignoran las advertencias
error_reporting(E_ERROR | E_PARSE);

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

define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/Controllers');
define('LIBRERIAS', RAIZ . '/libs');
define('MODELOS', RAIZ . '/Models');
define('VISTAS', RAIZ . '/Views');
define('COMPONENTES', RAIZ . '/components');
define('LOGIN', 'Login');
define('VISTA_DEFECTO', 'Inicio');
define('METODO_DEFECTO', 'index');

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN GENERAL
|--------------------------------------------------------------------------
*/
spl_autoload_register(function ($archivo) {
    $archivo = str_replace('\\', '/', $archivo);
    require_once RAIZ . "/$archivo.php";
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

// Verifica si la sesión de usuario está activa y si el navegador es compatible
if (!isset($_SESSION['login'])) {
    require_once LIBRERIAS . '/BrowserDetection/BrowserDetection.php';
    if (!validaNavegador()) {
        echo getErrorNavegador();
        exit;
    }
}

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
| LOGIN FORZADO
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['login']) || strtolower($urlSolicitada[0]) === strtolower(LOGIN)) {
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
if ($urlSolicitada[0] === '' || !file_exists(CONTROLADORES . "/$urlSolicitada[0].php")) {
    recursoNoDisponible();
}

$controlador = 'Controllers\\' . ucfirst($urlSolicitada[0]);
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

function validaNavegador()
{
    $navegadores = [
        'Chrome' => 120,
        'Edge' => 120,
        'Firefox' => 130,
    ];

    $b = new \foroco\BrowserDetection();
    $navegador = $b->getBrowser($_SERVER['HTTP_USER_AGENT']);

    if (
        !isset($navegadores[$navegador['browser_name']]) ||
        $navegador['browser_version'] < $navegadores[$navegador['browser_name']]
    ) {
        return false;
    }
    return true;
}

function getErrorNavegador()
{
    $empresa = CONFIGURACION['EMPRESA'];

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Navegador no compatible</title>
</head>
<body>
    <h1>Navegador no compatible</h1>
    <p>El navegador que estás utilizando no es compatible con el sistema de $empresa</p>
</body>
</html>
HTML;
}
