<?php

// Se reportan todos los errores y advertencias
// error_reporting(E_ALL);

// Se remueve información sensible de los encabezados HTTP
header_remove('X-Powered-By');
header_remove('Server');

// Headers de seguridad
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://maps.googleapis.com https://*.googleapis.com https://www.gstatic.com https://*.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://www.youtube.com https://s.ytimg.com; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://fonts.gstatic.com https://www.gstatic.com https://cdnjs.cloudflare.com; font-src \'self\' data: https://fonts.gstatic.com https://www.gstatic.com https://*.gstatic.com; img-src \'self\' data: https: blob: http://98.90.194.116; connect-src \'self\' webpack: https://*.googleapis.com https://*.gstatic.com http://98.90.194.116 https://nominatim.openstreetmap.org https://*.youtube.com http://127.0.0.1:8000 http://localhost:8000; frame-src \'self\' https://www.google.com https://maps.google.com https://*.google.com https://www.youtube.com https://*.youtube.com; frame-ancestors \'self\';');
if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

/*
|--------------------------------------------------------------------------
| DEFINICIÓN DE CONSTANTES (ANTES DE AUTOLOAD Y CONFIG)
|--------------------------------------------------------------------------
*/
define('RAIZ', dirname(__DIR__) . '/backend');

if (!defined('SPARTA_PROJECT_ROOT')) {
    define('SPARTA_PROJECT_ROOT', dirname(RAIZ));
}
if (!defined('SPARTA_UPLOADS_ROOT')) {
    define('SPARTA_UPLOADS_ROOT', __DIR__ . DIRECTORY_SEPARATOR . 'uploads');
}

// Hora de negocio en CDMX: date() y strtotime sin TZ explícita usan esta zona (evita desfase vs CURDATE() del servidor)
if (function_exists('date_default_timezone_set')) {
    @date_default_timezone_set('America/Mexico_City');
}

// Cargar .env si existe — solo variables MAIL_* para no afectar DB ni otras configs
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            $eq = strpos($line, '=');
            if ($eq === false) continue;
            $key = trim(substr($line, 0, $eq));
            if ($key === '' || strpos($key, 'MAIL_') !== 0) continue;
            $value = trim(str_replace(["\r", "\n"], '', substr($line, $eq + 1)));
            if (preg_match('/^["\'](.+)["\']\s*$/', $value, $m)) $value = trim($m[1]);
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/controllers');
/* Carpeta real en disco: backend/libs (minúsculas; Linux distingue y /Libs fallaría). */
define('LIBRERIAS', RAIZ . '/libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');
define('LOGIN', 'Login');
define('VISTA_DEFECTO', 'Inicio');
define('METODO_DEFECTO', 'index');

// Solo se reportan los errores y se ignoran las advertencias
error_reporting(E_ERROR | E_PARSE);

require_once dirname(__DIR__) . '/backend/bootstrap_composer.php';
sparta_require_composer_autoload();
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
    // Linux distingue mayúsculas: carpetas reales son models/, controllers/, core/, etc.
    // Sin esto, Models\Foo buscaba backend/Models/Foo.php y fallaba fuera de Windows.
    $parts = explode('/', $archivo, 2);
    $top = $parts[0];
    $tail = $parts[1] ?? '';
    static $dirMap = [
        'Models' => 'models',
        'Controllers' => 'controllers',
        'Core' => 'core',
        'Libs' => 'libs',
        'Services' => 'services',
    ];
    $dir = $dirMap[$top] ?? strtolower($top);
    $rel = $tail !== '' ? $dir . '/' . $tail : $dir;
    $ruta = RAIZ . '/' . $rel . '.php';

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

// Ruta de diagnóstico deshabilitada por seguridad (no exponer phpinfo en producción)
if ($urlSolicitada[0] === 'plat_desc') {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Archivos bajo /uploads/*: servir solo desde public/uploads (Apache suele servirlos directo; esto cubre el fallback vía index.php)
$extension = pathinfo(end($urlSolicitada), PATHINFO_EXTENSION);
if ($extension !== '' && strtolower($extension) !== 'php' && isset($_GET['url'])) {
    $urlSegura = str_replace(['../', '..\\'], '', trim($_GET['url'], "/\\"));
    $normalized = str_replace('\\', '/', $urlSegura);
    if (preg_match('#^uploads/(.+)$#i', $normalized, $mUp)) {
        $rel = $mUp[1];
        if ($rel === '' || strpos($rel, '..') !== false) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        $target = sparta_uploads_root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        $resuelto = realpath($target);
        $uploadsReal = realpath(sparta_uploads_root());
        $uploadsNorm = $uploadsReal ? strtolower(str_replace('\\', '/', $uploadsReal)) : '';
        $resueltoNorm = $resuelto ? strtolower(str_replace('\\', '/', $resuelto)) : '';
        if (
            $uploadsReal === false || $resuelto === false || $resueltoNorm === ''
            || ($resueltoNorm !== $uploadsNorm && strpos($resueltoNorm, $uploadsNorm . '/') !== 0)
            || !is_file($resuelto)
        ) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        $mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mt = @mime_content_type($resuelto);
            if (is_string($mt) && $mt !== '') {
                $mime = $mt;
            }
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($resuelto));
        header('X-Content-Type-Options: nosniff');
        readfile($resuelto);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| LOGIN FORZADO (excepto sabueso/crearTicketWhatsApp, que usa API key)
|--------------------------------------------------------------------------
*/
$esCrearTicketWhatsApp = isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'sabueso'
    && strtolower($urlSolicitada[1]) === 'crearticketwhatsapp';

$esSubirDocCandidato = isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'caphum'
    && strtolower($urlSolicitada[1]) === 'subirdocumentoscandidato';

$esDescargarDocCandidato = isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'caphum'
    && strtolower($urlSolicitada[1]) === 'descargardocumentocandidato';

$esLlenarSolicitudEnLinea = isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'caphum'
    && strtolower($urlSolicitada[1]) === 'llenarsolicitudenlinea';

$esObtenerPlantillaSolicitudPdf = isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'caphum'
    && strtolower($urlSolicitada[1]) === 'obtenerplantillasolicitudpdf';

$esEstadoReportesAgente = isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'segundometro'
    && strtolower($urlSolicitada[1]) === 'estadoreportesagente';

// Importación Excel (XHR espera JSON; si la sesión cayó, el flujo de Login devolvía HTML y el modal mostraba "no es JSON").
$solicitaImportExcelDespachosSinSesion = !isset($_SESSION['login'])
    && !$esCrearTicketWhatsApp && !$esSubirDocCandidato && !$esDescargarDocCandidato
    && !$esLlenarSolicitudEnLinea && !$esObtenerPlantillaSolicitudPdf && !$esEstadoReportesAgente
    && isset($urlSolicitada[0], $urlSolicitada[1])
    && strtolower($urlSolicitada[0]) === 'despachos'
    && strtolower($urlSolicitada[1]) === 'importarexcelasignacioncreditosdespacho';

if ($solicitaImportExcelDespachosSinSesion) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no válida o expirada. Vuelva a iniciar sesión y repita la importación.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ((!isset($_SESSION['login']) && !$esCrearTicketWhatsApp && !$esSubirDocCandidato && !$esDescargarDocCandidato && !$esLlenarSolicitudEnLinea && !$esObtenerPlantillaSolicitudPdf && !$esEstadoReportesAgente) || strtolower($urlSolicitada[0]) === strtolower(LOGIN)) {
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

/*
|--------------------------------------------------------------------------
| VERIFICACIÓN DE ACCESO (dentro del index, sin rastro en red ni consola)
|--------------------------------------------------------------------------
*/
$rutasModulos = [
    'estadocuenta/consulta' => [1], 'estadocuenta/guatemala' => [1], 'estadocuenta/documentacion' => [2], 'estadocuenta/reporteDictamen' => [14],
    'estadocuenta/getcomplementosestadocuenta' => [1],
    'gestiones/seguimiento' => [3],
    'caphum/gestion' => [4], 'caphum/candidatos' => [42], 'caphum/getcandidatos' => [42], 'caphum/getcandidato' => [42], 'caphum/guardarcandidato' => [42], 'caphum/actualizarcandidato' => [42], 'caphum/eliminarcandidato' => [42], 'caphum/enviarpostulacioncandidato' => [42], 'caphum/gettokendocumentoscandidato' => [42], 'caphum/getdocumentoscandidatolist' => [42], 'caphum/verificarexpedientecandidato' => [42], 'caphum/verdocumentocandidato' => [42], 'caphum/eliminardocumentocandidato' => [42], 'caphum/validardocumentocandidato' => [42], 'caphum/cerrarprocesocandidato' => [42], 'caphum/continuarprocesocandidato' => [42], 'caphum/pasarcandidatoagestion' => [42],     'caphum/bajas' => [13], 'caphum/organigrama' => [5], 'caphum/niveljerarquicocolaborador' => [5], 'caphum/getpuestospersona' => [5],
    'caphum/estadisticas' => [38], 'caphum/getestadisticaspanel' => [38], 'caphum/getestadisticasmovimientodetalle' => [38],
    'reporteria/callcenter' => [6, 14, 15], 'reporteria/resumencallcenter' => [6, 14, 15], 'reporteria/primerospagos' => [49], 'reporteria/vencimientoslunes' => [49], 'reporteria/vencimientolunessiguientesemana' => [49],
    'reporteria/getvencimientoslunes' => [49], 'reporteria/getvencimientoslunessiguientesemana' => [49],
    'reporteria/descargarprimerospagossemanactualexcel' => [49],
    'reporteria/sabuesos' => [18, 19, 48], 'reporteria/consultaidcredito' => [18, 19, 29], 'reporteria/consultacreditorastreo' => [18, 19, 29], 'reporteria/descargarReporteSabuesos1' => [18], 'reporteria/descargarReporteSabuesos2' => [19], 'reporteria/descargarReporteSabuesos3' => [19, 48], 'reporteria/descargarReporteSabuesosEstadisticasDetalle' => [47],     'reporteria/layoutlegacy' => [7], 'reporteria/reporteCapitalHumano' => [34],
    'reporteria/getusuarioscapitalhumano' => [34], 'reporteria/getbajascapitalhumano' => [34],
    'reporteria/descargarbajasexcelcapitalhumano' => [34], 'reporteria/getfiltroscapitalhumano' => [34],
    'reporteria/descargarusuariosexcelcapitalhumano' => [34],
    'reporteriabi/flujocobranza' => [50],
    'condonaciones/historial' => [15, 39],
    'gastoscobranza/estadisticagc' => [40],
    'gastoscobranza/getdashboardestadistica' => [40],
    'sabueso/ticket' => [18], 'sabueso/verificarcreditoduplicadocreador' => [18], 'sabueso/guardarsolicitudbaja' => [18], 'sabueso/guardarticketplantilla' => [18], 'sabueso/guardarticketatencioncliente' => [18], 'sabueso/guardarticketvalidacion' => [18], 'sabueso/guardarticketviaticos' => [18], 'sabueso/guardarticketaplicacionespago' => [18], 'sabueso/guardarticketcreditoproblematico' => [18], 'sabueso/guardarticketaclaracioncredito' => [18], 'sabueso/paneladmininicio' => [19, 25, 27], 'sabueso/paneladmin' => [18, 19], 'validaciones/paneladmin' => [18, 19], 'validaciones/gestor' => [18, 19], 'validaciones/territorial' => [18, 19], 'validaciones/getticketsgestor' => [18, 19], 'validaciones/getticketsterritorial' => [18, 19], 'validaciones/getgestoresporcampo' => [18, 19], 'validaciones/reasignargestorticketterritorial' => [18, 19], 'validaciones/formulario' => [18, 19], 'validaciones/getformularios' => [18, 19], 'validaciones/guardarformulario' => [19], 'validaciones/toggleformulario' => [19], 'validaciones/eliminarformulario' => [19], 'validaciones/actualizarformulario' => [19], 'validaciones/getpreguntasformulario' => [18, 19], 'validaciones/guardarpreguntaformulario' => [19], 'validaciones/togglepreguntaformulario' => [19], 'validaciones/eliminarpreguntaformulario' => [19], 'viaticos/paneladmin' => [19], 'aplicacionespago/paneladmin' => [19], 'plantilla/paneladmin' => [19], 'atencioncliente/paneladmin' => [19], 'creditoproblematico/paneladmin' => [19], 'aclaracioncredito/paneladmin' => [19], 'sabueso/panelsolicitudbaja' => [25], 'sabueso/getsolicitudesbaja' => [25], 'sabueso/getsolicitudbajaporid' => [25], 'sabueso/veradjuntosolicitudbaja' => [25],
    // Cerrado/Eliminado Sabueso: módulo propio (modulos_web id 48). Quien solo tenga 19 no entra aquí.
    'sabueso/cerradoeliminado' => [48], 'sabueso/getticketscerradoseliminados' => [48], 'sabueso/getdatosticketcerradoeliminado' => [48],
    'sabueso/estadisticas' => [47], 'sabueso/getestadisticastickets' => [47], 'sabueso/getestadisticasporsabuesosolo' => [47],
    'sabueso/getestadisticasgestordetalle' => [47], 'sabueso/getestadisticassabuesodetalle' => [47], 'sabueso/getticketsdetallepordia' => [47],
    'sabueso/getreportesemanalgestorglobal' => [47], 'sabueso/reconsultarpagosemanareportesemanal' => [47],
    'sabueso/guardardictamenborrador' => [19], 'sabueso/enviardictamengestor' => [19], 'sabueso/getdictamendetalle' => [18, 19], 'sabueso/marcardictamenvisto' => [18, 19], 'sabueso/getdictamenactualticket' => [19],
    'sabueso/subirevidenciaticket' => [19],
    // Listar evidencias: gestores / menú Ticket (18) y panel admin Sabueso (19). Ver imágenes ya es verevidencia [18,19].
    'sabueso/getevidenciasticket' => [18, 19],
    'sabueso/asignarticket' => [18, 19],
    'sabueso/quitarasignacionticket' => [18, 19],
    'sabueso/getpersonassabuesojefesporcampo' => [18, 19],
    'sabueso/eliminarevidenciaticket' => [19], 'sabueso/verevidencia' => [18, 19], 'sabueso/otorgarprorrogadictamensistema' => [19], 'sabueso/otorgarintensidaddictamensistema' => [19],
    'convenios/consulta' => [45, 46], 'convenios/buscarcredito' => [45, 46], 'convenios/getofertascredito' => [45, 46], 'convenios/guardarconvenio' => [45, 46], 'convenios/getconvenioactivo' => [45, 46], 'convenios/descargarpdf' => [45, 46], 'convenios/getestatuss2' => [45, 46],
    'convenios/cancelarconvenio' => [45, 46], 'convenios/checkdespacho' => [45, 46], 'convenios/validardespacho' => [45, 46], 'convenios/migrarconvenio' => [45, 46], 'convenios/gethistorialconvenios' => [45, 46], 'convenios/registrarpago' => [45, 46], 'convenios/getproductosconvenio' => [45, 46], 'convenios/getamortizacionconvenio' => [45, 46], 'convenios/getconciliacionsemana' => [45, 46], 'convenios/guardarconciliacion' => [45, 46], 'convenios/subircomprobante' => [45, 46], 'convenios/registrarconvenioglobo' => [45, 46],
    'despachos/asignacioncreditosdespacho' => [20], 'departamentos/consulta' => [10], 'equivalencias/consulta' => [17],
    'configticketpuesto/consulta' => [26], 'configticketpuesto/getpuestossparta' => [26], 'configticketpuesto/getconfig' => [26], 'configticketpuesto/guardar' => [26], 'configticketpuesto/getconfigestadisticas' => [26], 'configticketpuesto/guardarestadisticas' => [26],
    'configticketpuesto/getusuariospanel' => [26], 'configticketpuesto/getconfigpanelusuario' => [26], 'configticketpuesto/guardarpanelusuario' => [26],
    'segundometro/shell' => [16],
    'gastoscobranza/shell' => [31],
    'gastoscobranza/estadoagente' => [31],
    'gastoscobranza/configurarautorunreporte' => [31],
    'gastoscobranza/ejecutarreporte' => [31],
    'gastoscobranza/logagente' => [31],
    'gastoscobranza/vaciarlogagente' => [31],
    'gastoscobranza/listarreportes' => [31],
    'gastoscobranza/descargarreporte' => [31],
    'gastoscobranza/descargarerroresreintento' => [31],
    'gastoscobranza/subirexcelec' => [31],
    'gastoscobranza/ejecutareclauncher' => [31],
    'gastoscobranza/ejecutarcargaverificacionsemana' => [31],
    'gastoscobranza/ejecutardescargoestatus3' => [31],
    'gastoscobranza/descargoestatus3ejecutarydescargar' => [31],
    'gastoscobranza/descargardescargoestatus3' => [31],
    'onboarding/index' => [44],
    'onboarding/video' => [44],
    'cierecredito/consulta' => [50],
    'cierecredito/getenproceso' => [50],
    'cierecredito/getenviadofinalizado' => [50],
    'cierecredito/crear' => [50],
    'cierecredito/cambiarestatus' => [50],
];
$controladoresModulos = [
    'segundometro' => [16],
    'gastoscobranza' => [31],
];
$path = strtolower(trim($controladorArchivo)) . '/' . strtolower(trim($metodo));
$modulosRequeridos = $rutasModulos[$path] ?? $controladoresModulos[strtolower(trim($controladorArchivo))] ?? null;
if (!$esEstadoReportesAgente && $modulosRequeridos !== null) {
    $modulosUsuario = $_SESSION['modulos'] ?? [];
    if (!is_array($modulosUsuario) || !array_intersect($modulosRequeridos, $modulosUsuario)) {
        $permitirValidacionesOperativa = false;
        $personaIdRuta = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaIdRuta > 0) {
            $entradaValOp = \Core\TicketsPanelModuloHelper::resolverEntradaValidacionesOperativa($personaIdRuta);
            if ($entradaValOp !== null) {
                $tipoValOp = $entradaValOp['tipo'] ?? '';
                $rutasValOpComunes = [
                    'validaciones/formulario',
                    'validaciones/getformularios',
                    'validaciones/getpreguntasformulario',
                    'sabueso/getevidenciasticket',
                    'sabueso/verevidencia',
                ];
                $rutasValOpGestor = array_merge($rutasValOpComunes, [
                    'validaciones/gestor',
                    'validaciones/getticketsgestor',
                    'sabueso/asignarticket',
                    'sabueso/quitarasignacionticket',
                    'sabueso/getpersonassabuesojefesporcampo',
                ]);
                $rutasValOpTerritorial = array_merge($rutasValOpComunes, [
                    'validaciones/territorial',
                    'validaciones/getticketsterritorial',
                    'validaciones/getgestoresporcampo',
                    'validaciones/reasignargestorticketterritorial',
                    'sabueso/asignarticket',
                    'sabueso/quitarasignacionticket',
                    'sabueso/getpersonassabuesojefesporcampo',
                ]);
                $setValOp = $tipoValOp === 'territorial' ? $rutasValOpTerritorial : ($tipoValOp === 'gestor' ? $rutasValOpGestor : []);
                $permitirValidacionesOperativa = in_array($path, $setValOp, true);
            }
        }
        if (!$permitirValidacionesOperativa) {
            header('Location: /' . VISTA_DEFECTO);
            exit;
        }
    }
}

$parametros = count($urlSolicitada) ? array_values($urlSolicitada) : [];
call_user_func_array([$controlador, $metodo], $parametros);

/*
|--------------------------------------------------------------------------
| FUNCIONES AUXILIARES
|--------------------------------------------------------------------------
*/
function solicitudEsFrontRequest()
{
    if (!empty($_SERVER['HTTP_FRONT_REQUEST']) && strtolower(trim((string)$_SERVER['HTTP_FRONT_REQUEST'])) === 'true') {
        return true;
    }
    if (function_exists('apache_request_headers')) {
        foreach (apache_request_headers() as $k => $v) {
            if (strtolower((string)$k) === 'front-request' && strtolower(trim((string)$v)) === 'true') {
                return true;
            }
        }
    }
    return false;
}

function recursoNoDisponible()
{
    if (solicitudEsFrontRequest()) {
        header('HTTP/1.0 404 Not Found');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Recurso no disponible', 'success' => false]);
        exit;
    }

    if (isset($_SESSION['login'])) header('Location: /' . VISTA_DEFECTO);
    else header('Location: /' . LOGIN);
    exit;
}
