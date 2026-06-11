<?php
// Solo se reportan los errores y se ignoran las advertencias
error_reporting(E_ERROR | E_PARSE);

// Se reportan todos los errores y advertencias
// error_reporting(E_ALL);

// Se remueve información sensible de los encabezados HTTP
header_remove('X-Powered-By');
header_remove('Server');
require_once __DIR__ . '/../backend/config/config.php';

// Configuración de la zona horaria para contemplar horario de verano
$validaHV = new DateTime('now', new DateTimeZone('America/Mexico_City'));
if ($validaHV->format('I')) date_default_timezone_set('America/Mazatlan');
else date_default_timezone_set('America/Mexico_City');

// Se definen las constantes de la aplicación
define('RAIZ', dirname(__DIR__) . '/backend');
define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/controllers');
define('LIBRERIAS', RAIZ . '/libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');
define('LOGIN', 'Login');
define('VISTA_DEFECTO', 'Inicio');
define('METODO_DEFECTO', 'index');

// Se define el tiempo de vida de la sesión mientras no se cierre el navegador
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

// La URL esperada es de la forma: /controlador/metodo/?parametro1=valor1&parametro2=valor2
$urlSolicitada = isset($_GET['url']) ? explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL)) : [''];

if ($urlSolicitada[0] === 'plat_desc') {
    phpinfo(); // Muestra información de PHP si se solicita
    exit;
}

// Si la URL solicitada no es un archivo PHP, se verifica su existencia
$extension = pathinfo(end($urlSolicitada), PATHINFO_EXTENSION);
if ($extension !== '' && strtolower($extension) !== 'php') {
    $rutaArchivo = dirname(__DIR__) . '/' . $_GET['url'];
    if (!file_exists($rutaArchivo)) header('HTTP/1.0 404 Not Found');
    exit;
}

// Se registra el autoload
spl_autoload_register(function ($archivo) {
    $archivo = str_replace('\\', '/', $archivo);
    require_once RAIZ . "/$archivo.php";
});

// Si no se ha iniciado sesión o se solicita el login, se llama al controlador de login y se finaliza la ejecución
if (!isset($_SESSION['login']) || strtolower($urlSolicitada[0]) === strtolower(LOGIN)) {
    $login = 'Controllers\\' . LOGIN;
    $login = new $login;
    $metodo = isset($urlSolicitada[1]) ? $urlSolicitada[1] : METODO_DEFECTO;
    $metodo = strtolower($urlSolicitada[0]) === strtolower(LOGIN) ? $metodo : METODO_DEFECTO;
    call_user_func_array([$login, $metodo], []);
    exit;
}

// Se valida que el archivo del controlador solicitado exista
if ($urlSolicitada[0] === '' || !file_exists(CONTROLADORES . "/$urlSolicitada[0].php")) recursoNoDisponible();

$controlador = 'Controllers\\' . ucfirst($urlSolicitada[0]);
unset($urlSolicitada[0]);

// Se valida que la clase del controlador exista
if (!class_exists($controlador)) recursoNoDisponible();

// Se crea una instancia del controlador y se obtiene el método a llamar
$controlador = new $controlador;
$metodo = isset($urlSolicitada[1]) ? $urlSolicitada[1] : METODO_DEFECTO;
unset($urlSolicitada[1]);

// Se valida que el método exista en el controlador
if (!method_exists($controlador, $metodo)) recursoNoDisponible();

// Se obtienen los parámetros de la URL solicitada
$parametros = count($urlSolicitada) ? array_values($urlSolicitada) : [];

// Se llama al método del controlador con los parámetros obtenidos
call_user_func_array([$controlador, $metodo], $parametros);

/**
 * Si es una solicitud AJAX, se devuelve un error 404, de lo contrario, se redirige a la página de inicio o al login si no está autenticado.
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

/**
 * Valida el navegador del usuario.
 * 
 * @return bool true si el navegador es compatible, false en caso contrario.
 */
function validaNavegador()
{
    $navegadores = [
        'Chrome' => 120,
        'Edge' => 120,
        'Firefox' => 130,
        // 'Safari' => 140,
        // 'Opera' => 105
    ];

    $b = new \foroco\BrowserDetection();
    $navegador = $b->getBrowser($_SERVER['HTTP_USER_AGENT']);

    if (!$navegadores[$navegador['browser_name']] || $navegador['browser_version'] < $navegadores[$navegador['browser_name']]) return false;
    return true;
}

/**
 * Devuelve un mensaje HTML de error para navegadores no compatibles.
 * 
 * @return string HTML del mensaje de error.
 */
function getErrorNavegador()
{
    $empresa = CONFIGURACION['EMPRESA'];

    return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Navegador no compatible</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f2f2f2;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .container {
                    background-color: #ffffff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
                .container h1 {
                    color: #ff0000;
                }
                .container p {
                    margin: 10px 0;
                }
                .container ul {
                    list-style: none;
                    padding: 0;
                }
                .container li {
                    margin: 10px 0;
                    display: flex;
                    align-items: center;
                }
                .container img {
                    width: 24px;
                    height: 24px;
                    margin-right: 10px;
                }
                .container a {
                    color: #007bff;
                    text-decoration: none;
                }
                .navegadores {
                    display: flex;
                    justify-content: center;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Navegador no compatible</h1>
                <p>El navegador que estás utilizando no es compatible con el sistema de $empresa</p>
                <p>Le recomendamos usar uno de los siguientes navegadores:</p>
                <div class="navegadores">
                    <ul>
                        <li>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/87/Google_Chrome_icon_%282011%29.png" alt="Google Chrome">
                            <a href="https://www.google.com/chrome/">Google Chrome</a>
                        </li>
                        <li>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/98/Microsoft_Edge_logo_%282019%29.svg" alt="Microsoft Edge">
                            <a href="https://www.microsoft.com/edge">Microsoft Edge</a>
                        </li>
                    </ul>
                </div>
            </div>
        </body>
        </html>
    HTML;
}
