<?php

namespace Controllers;

use Core\Controller;
use Core\DatabaseGeo;
use Core\UsuarioFantasmaReporteria;
use Models\Usuarios as UsuariosDao;

class Inicio extends Controller
{
    public function index()
    {
        if (UsuarioFantasmaReporteria::es()) {
            header('Location: ' . UsuarioFantasmaReporteria::URL_INICIO_SESION, true, 302);
            exit;
        }

        $this->validarActualizacionPassword();
        require_once dirname(__DIR__) . '/config/menu_accesos_inicio.php';
        $accesosRapidos = getAccesosRapidosDesdeModulos();
        $personaIdInicio = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $resValOpInicio = $personaIdInicio > 0
            ? \Core\TicketsPanelModuloHelper::resolverEntradaValidacionesOperativa($personaIdInicio)
            : null;
        if ($resValOpInicio !== null) {
            $urlValOp = $resValOpInicio['url'] ?? '/validaciones/gestor';
            $norm = function ($u) {
                return rtrim(strtolower((string) $u), '/');
            };
            $duplicado = false;
            foreach ($accesosRapidos as $a) {
                if (isset($a['url']) && $norm($a['url']) === $norm($urlValOp)) {
                    $duplicado = true;
                    break;
                }
            }
            if (!$duplicado) {
                $accesosRapidos[] = [
                    'url' => $urlValOp,
                    'label' => 'Validaciones',
                    'icon' => 'fa-solid fa-clipboard-check',
                    'bg' => 'bg-success',
                ];
            }
        }
        $this->set('accesosRapidos', $accesosRapidos);
        // Botones de diagnóstico (Segundómetro y BD alternas): solo usuario id 1 y si config lo permite
        $configInicio = (is_file($cfg = dirname(__DIR__) . '/config/config.ini') && is_array($c = @parse_ini_file($cfg, true))) ? ($c['inicio'] ?? []) : [];
        $mostrarDiagnosticoAdmin = (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === 1)
            && !empty($configInicio['mostrar_botones_diagnostico']);
        $this->set('mostrarDiagnosticoAdmin', $mostrarDiagnosticoAdmin);
        $mostrarBotonApiDocOneClick = (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === 878);
        $this->set('mostrarBotonApiDocOneClick', $mostrarBotonApiDocOneClick);
        // Botón "Servicios" para ver estado de los puertos locales (3001, 3100, 3110, 3120, 8000).
        $mostrarBotonEstadoServicios = $mostrarBotonApiDocOneClick;
        $this->set('mostrarBotonEstadoServicios', $mostrarBotonEstadoServicios);

        self::render("inicio___SPARTA_SECRET_REDACTED___contenido", false);
    }

    /**
     * Frase del día: misma frase para todos los usuarios (por fecha CDMX).
     * Lee backend/config/frases_motivacionales.json y devuelve una frase por día.
     * Respuesta JSON: { success: true, texto: "...", autor: "..." }
     */
    public function fraseDelDia()
    {
        header('Content-Type: application/json; charset=utf-8');
        $path = dirname(__DIR__) . '/config/frases_motivacionales.json';
        if (!is_file($path) || !is_readable($path)) {
            echo json_encode([
                'success' => false,
                'texto'   => 'El éxito es la suma de pequeños esfuerzos repetidos día tras día.',
                'autor'   => 'Robert Collier'
            ]);
            return;
        }
        $json = @file_get_contents($path);
        $data = $json ? @json_decode($json, true) : null;
        $frases = isset($data['frases']) && is_array($data['frases']) ? $data['frases'] : [];
        if (empty($frases)) {
            echo json_encode([
                'success' => true,
                'texto'   => 'El éxito es la suma de pequeños esfuerzos repetidos día tras día.',
                'autor'   => 'Robert Collier'
            ]);
            return;
        }
        $tz = new \DateTimeZone('America/Mexico_City');
        $now = new \DateTime('now', $tz);
        $key = $now->format('Y-m-d');
        $idx = abs(crc32($key)) % count($frases);
        $f = $frases[$idx];
        $texto = isset($f['texto']) ? (string) $f['texto'] : '';
        $autor = isset($f['autor']) ? (string) $f['autor'] : '';
        echo json_encode([
            'success' => true,
            'texto'   => $texto,
            'autor'   => $autor
        ]);
    }

    /**
     * Diagnóstico de conexión a BD direcciones alternas (__SPARTA_SECRET_REDACTED__): SSL, permisos, análisis por pasos.
     * No asume rutas fijas; usa la misma lógica que DatabaseGeo (__DIR__ del core) y comprueba también desde backend/.
     * Solo accesible para usuario con id 1.
     */
    public function diagnosticoConexiones()
    {
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 1) {
            header('Location: /inicio');
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');

        // Ruta que usa DatabaseGeo (desde backend/core → backend/BD)
        $paths = DatabaseGeo::getSslPaths();
        $dirGeo = $paths['dir'];
        $dirResolved = @realpath($dirGeo);

        // Ruta alternativa: desde este controlador (backend/controllers → backend/BD)
        $backendDir = dirname(__DIR__);
        $dirDesdeBackend = $backendDir . DIRECTORY_SEPARATOR . 'BD';
        $dirAltResolved = @realpath($dirDesdeBackend);

        // Usar la carpeta que exista (prioridad: la que usa la app, luego la alternativa)
        $dirEfectivo = $dirResolved ?: $dirAltResolved;
        $sslDir = $dirEfectivo ?: $dirGeo;

        $sep = DIRECTORY_SEPARATOR;
        $ca   = $sslDir . $sep . 'server-ca.pem';
        $cert = $sslDir . $sep . 'client-cert.pem';
        if (!is_file($cert)) {
            $cert = $sslDir . $sep . 'client-cert (3).pem';
        }
        $key = $sslDir . $sep . 'client-key.pem';
        if (!is_file($key)) {
            $key = $sslDir . $sep . 'client-key (1).pem';
        }

        $caExists = is_file($ca);
        $caReadable = $caExists && is_readable($ca);
        $certExists = is_file($cert);
        $certReadable = $certExists && is_readable($cert);
        $keyExists = is_file($key);
        $keyReadable = $keyExists && is_readable($key);

        $lines = [];
        $lines[] = '=== Diagnóstico de conexión (BD direcciones alternas / __SPARTA_SECRET_REDACTED__) ===';
        $lines[] = 'Servidor: ' . ($_SERVER['SERVER_NAME'] ?? '?') . ' | ' . date('Y-m-d H:i:s');
        $lines[] = '';
        $lines[] = '--- Ubicación de la carpeta de certificados (backend/BD) ---';
        $lines[] = 'Ruta que usa la app (desde Core\DatabaseGeo): ' . $dirGeo;
        $lines[] = '  Resuelta (realpath): ' . ($dirResolved ?: '(no existe o no accesible)');
        $lines[] = 'Ruta desde backend/ (desde controlador): ' . $dirDesdeBackend;
        $lines[] = '  Resuelta (realpath): ' . ($dirAltResolved ?: '(no existe o no accesible)');
        $lines[] = 'Carpeta usada en este diagnóstico: ' . ($dirEfectivo ?: $dirGeo);
        $lines[] = 'Existe carpeta: ' . (is_dir($sslDir) ? 'Sí' : 'No');
        $lines[] = '';
        $lines[] = '--- Archivos de certificados ---';
        $lines[] = 'server-ca.pem:  ' . ($caExists ? 'Existe' : 'NO EXISTE') . ' | Lectura: ' . ($caReadable ? 'Sí' : 'No');
        $lines[] = '  Ruta: ' . $ca;
        $lines[] = 'client-cert:   ' . ($certExists ? 'Existe' : 'NO EXISTE') . ' | Lectura: ' . ($certReadable ? 'Sí' : 'No');
        $lines[] = '  Ruta: ' . $cert;
        $lines[] = 'client-key:    ' . ($keyExists ? 'Existe' : 'NO EXISTE') . ' | Lectura: ' . ($keyReadable ? 'Sí' : 'No');
        $lines[] = '  Ruta: ' . $key;
        if ($caExists) {
            $sizeCa = @filesize($ca);
            $lines[] = 'Tamaño server-ca.pem: ' . ($sizeCa !== false ? $sizeCa . ' bytes' : '?') . ($sizeCa === 0 ? ' (vacío)' : '');
        }
        if ($certExists) {
            $sizeCert = @filesize($cert);
            $lines[] = 'Tamaño client-cert: ' . ($sizeCert !== false ? $sizeCert . ' bytes' : '?') . ($sizeCert === 0 ? ' (vacío)' : '');
        }
        if ($keyExists) {
            $sizeKey = @filesize($key);
            $lines[] = 'Tamaño client-key: ' . ($sizeKey !== false ? $sizeKey . ' bytes' : '?') . ($sizeKey === 0 ? ' (vacío)' : '');
        }
        $lines[] = '';

        // --- Entorno PHP y extensiones ---
        $lines[] = '--- Entorno (PHP y extensiones) ---';
        $lines[] = 'PHP: ' . PHP_VERSION . ' | SAPI: ' . php_sapi_name();
        $lines[] = 'Sistema: ' . PHP_OS . ' | Servidor: ' . ($_SERVER['SERVER_SOFTWARE'] ?? '?');
        $pdoLoaded = extension_loaded('pdo');
        $pdoMysqlLoaded = extension_loaded('pdo_mysql');
        $opensslLoaded = extension_loaded('openssl');
        $lines[] = 'PDO: ' . ($pdoLoaded ? 'Sí' : 'No') . ' | pdo_mysql: ' . ($pdoMysqlLoaded ? 'Sí' : 'No') . ' | openssl: ' . ($opensslLoaded ? 'Sí' : 'No');
        if (!$pdoLoaded || !$pdoMysqlLoaded) {
            $lines[] = '  (Falta extensión necesaria para MySQL.)';
        }
        $lines[] = '';

        // --- IP de salida (para autorizar en Cloud SQL) ---
        $lines[] = '--- IP de este servidor (añadir en redes autorizadas de la BD) ---';
        $ipSalida = null;
        if (function_exists('gethostname')) {
            $hostname = @gethostname();
            if ($hostname && function_exists('gethostbyname')) {
                $ipSalida = @gethostbyname($hostname);
                if ($ipSalida === $hostname) {
                    $ipSalida = null;
                }
            }
        }
        if (empty($ipSalida) && !empty($_SERVER['SERVER_ADDR'])) {
            $ipSalida = $_SERVER['SERVER_ADDR'];
        }
        $lines[] = 'IP local/host: ' . ($ipSalida ?: '(no detectada)');
        $lines[] = '  → En Cloud SQL / MySQL: autorizar esta IP para poder conectar.';
        $lines[] = '';

        // --- Conectividad de red (socket TCP) ---
        $host = '__SPARTA_HOST_REDACTED__';
        $port = 3306;
        $lines[] = '--- Conectividad de red (TCP ' . $host . ':' . $port . ') ---';
        $timeoutSocket = 5;
        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeoutSocket);
        $socketOk = (bool)$fp;
        $socketErrnoSaved = $errno;
        $socketErrstrSaved = $errstr;
        if ($fp) {
            $lines[] = 'Socket TCP: OK (puerto ' . $port . ' accesible; red/firewall permiten conexión).';
            fclose($fp);
        } else {
            $lines[] = 'Socket TCP: FALLO (no se puede conectar en ' . $timeoutSocket . ' s).';
            $lines[] = '  errno: ' . $errno . ' | errstr: ' . ($errstr ?: '(vacío)');
            if ($errno === 110 || $errstr === 'Connection timed out' || stripos($errstr, 'timed out') !== false) {
                $lines[] = '  → Timeout: la IP de este servidor probablemente no está autorizada en la BD, o hay firewall.';
            } elseif ($errno === 111 || stripos($errstr, 'refused') !== false) {
                $lines[] = '  → Connection refused: MySQL no escucha en ese puerto o en esa IP.';
            } else {
                $lines[] = '  → Revisar: firewall salida (este servidor), redes autorizadas (BD), que MySQL escuche en ' . $host . '.';
            }
        }
        $socketTimeout = !$socketOk && ($socketErrnoSaved === 110 || stripos((string)$socketErrstrSaved, 'timed out') !== false);
        $socketRefused = !$socketOk && ($socketErrnoSaved === 111 || stripos((string)$socketErrstrSaved, 'refused') !== false);
        $lines[] = '';

        // --- Resolución DNS (opcional; si el host fuera nombre) ---
        $ipResuelta = @gethostbyname($host);
        $lines[] = 'Resolución de ' . $host . ': ' . ($ipResuelta !== $host ? $ipResuelta : '(mismo valor, no es nombre DNS)');
        $lines[] = '';

        // Diagnóstico por pasos (como test_geo_ssl.php)
        $dbname = '__SPARTA_SECRET_REDACTED__';
        $user = '__SPARTA_SECRET_REDACTED__';
        $pass = '__SPARTA_PASSWORD_REDACTED__';
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $opciones = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];

        $lines[] = '--- Diagnóstico por pasos (conexión a ' . $host . ':' . $port . ') ---';
        $lines[] = '';

        // Paso 1: Sin SSL
        $lines[] = '1. Conexión SIN SSL (comprobar red/firewall):';
        try {
            $pdo = new \PDO($dsn, $user, $pass, $opciones);
            $lines[] = '   OK (TCP establecido; servidor aceptó sin forzar SSL).';
            $pdo = null;
        } catch (\PDOException $e) {
            $lines[] = '   Error: ' . $e->getMessage();
            $lines[] = '   (Si falla: firewall, host incorrecto, o servidor exige SSL.)';
        }
        $lines[] = '';

        // Paso 2: Solo CA
        $lines[] = '2. Conexión SSL solo CA (sin client-cert/key):';
        if (is_file($ca)) {
            $opts = $opciones + [
                \PDO::MYSQL_ATTR_SSL_CA => $ca,
                \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            try {
                $pdo = new \PDO($dsn, $user, $pass, $opts);
                $lines[] = '   OK (SSL con CA; servidor no exige certificado cliente).';
                $pdo = null;
            } catch (\PDOException $e) {
                $lines[] = '   Error: ' . $e->getMessage();
            }
        } else {
            $lines[] = '   (server-ca.pem no encontrado)';
        }
        $lines[] = '';

        // Paso 3: CA + cert + key
        $lines[] = '3. Conexión SSL completa (CA + cert + key):';
        if (is_file($ca) && is_file($cert) && is_file($key)) {
            $opts = $opciones + [
                \PDO::MYSQL_ATTR_SSL_CA => $ca,
                \PDO::MYSQL_ATTR_SSL_CERT => $cert,
                \PDO::MYSQL_ATTR_SSL_KEY => $key,
                \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            try {
                $pdo = new \PDO($dsn, $user, $pass, $opts);
                $lines[] = '   OK (conexión SSL completa; esta es la config que usa la app).';
                $pdo = null;
            } catch (\PDOException $e) {
                $lines[] = '   Error: ' . $e->getMessage();
            }
        } else {
            $lines[] = '   (falta ca, cert o key)';
        }
        $lines[] = '';

        // Conexión con DatabaseGeo (clase real)
        $lines[] = '--- Conexión con DatabaseGeo (clase de la app) ---';
        $db = new DatabaseGeo();
        $connected = $db->isConnected();
        $lastError = DatabaseGeo::getLastError();
        $lines[] = 'Conectado: ' . ($connected ? 'Sí' : 'No');
        if (!$connected && $lastError) {
            $lines[] = 'Error PDO: ' . $lastError;
        }
        $lines[] = '';

        // --- Si está conectado: listar tablas y consulta de prueba ---
        if ($connected && $db !== null) {
            $lines[] = '--- Tablas en __SPARTA_SECRET_REDACTED__ (SHOW TABLES) ---';
            $rowsTables = $db->queryAll('SHOW TABLES');
            $tables = [];
            foreach ($rowsTables as $r) {
                $tables[] = reset($r);
            }
            $numTables = count($tables);
            $lines[] = 'Número de tablas: ' . $numTables;
            if ($numTables > 0) {
                $lines[] = 'Tablas: ' . implode(', ', array_slice($tables, 0, 25));
                if ($numTables > 25) {
                    $lines[] = '  (... y ' . ($numTables - 25) . ' más)';
                }
            }

            $lines[] = '';
            $lines[] = '--- Consulta de prueba (SELECT 1) ---';
            $rowTest = $db->queryOne('SELECT 1 AS test');
            $lines[] = ($rowTest && isset($rowTest['test'])) ? 'OK (SELECT 1 devuelve: ' . $rowTest['test'] . ')' : 'Error o sin resultado';

            $lines[] = '';
            $lines[] = '--- Consulta oferta_coordenada (tabla usada por la app) ---';
            $rowCount = $db->queryOne('SELECT COUNT(*) AS total FROM oferta_coordenada');
            if ($rowCount !== null && isset($rowCount['total'])) {
                $lines[] = 'Filas en oferta_coordenada: ' . $rowCount['total'];
            } else {
                $lines[] = 'Tabla oferta_coordenada no existe o error al contar';
            }
            $lines[] = '';
        }

        // --- Interpretación de errores PDO ---
        $lines[] = '--- Interpretación de errores PDO ---';
        $lines[] = 'Código 2002: no se pudo establecer conexión TCP (timeout, firewall, IP no autorizada).';
        $lines[] = 'Código 1045: acceso denegado (usuario/contraseña o host no permitido).';
        $lines[] = 'Código 2026: problema con certificado SSL.';
        $lines[] = '';

        // --- Checklist de red (revisar si hay timeout / 2002) ---
        $lines[] = '--- Checklist de red (revisar si hay timeout o error 2002) ---';
        $lines[] = '';
        $lines[] = '1. Autorización de red en la BD (' . $host . ')';
        $lines[] = '   Si la base está en Google Cloud SQL (o similar):';
        $lines[] = '   En la consola, en "Redes autorizadas" / "Authorized networks", hay que añadir';
        $lines[] = '   la IP del servidor de la app: 34.51.32.249.';
        $lines[] = '   Sin esa IP, Cloud SQL no acepta conexiones desde 34.51.32.249 y se produce el timeout.';
        if ($socketOk) {
            $lines[] = '   [Verificación] TCP OK → probablemente esta IP ya está autorizada.';
        } elseif ($socketTimeout) {
            $lines[] = '   [Verificación] Timeout → REVISAR: añadir IP de este servidor (' . ($ipSalida ?: 'ver arriba') . ') en redes autorizadas.';
        } else {
            $lines[] = '   [Verificación] Conexión fallida → revisar redes autorizadas en la consola de la BD.';
        }
        $lines[] = '';
        $lines[] = '2. Firewall en el servidor de la app (34.51.32.249)';
        $lines[] = '   Comprobar que no se esté bloqueando la salida al puerto ' . $port . ' hacia ' . $host . '.';
        if ($socketOk) {
            $lines[] = '   [Verificación] TCP OK → la salida al puerto ' . $port . ' está permitida desde este servidor.';
        } elseif ($socketTimeout) {
            $lines[] = '   [Verificación] Timeout → REVISAR: reglas de firewall de salida (iptables, ufw, etc.) hacia ' . $host . ':' . $port . '.';
        } else {
            $lines[] = '   [Verificación] Conexión fallida → revisar firewall de salida en este servidor.';
        }
        $lines[] = '';
        $lines[] = '3. Que MySQL escuche en la IP correcta';
        $lines[] = '   Si ' . $host . ' es la IP pública del servidor de BD, MySQL debe estar configurado';
        $lines[] = '   para escuchar en esa IP (o en 0.0.0.0), no solo en 127.0.0.1.';
        if ($socketOk) {
            $lines[] = '   [Verificación] TCP OK → MySQL acepta conexiones en ese puerto/IP.';
        } elseif ($socketRefused) {
            $lines[] = '   [Verificación] Connection refused → REVISAR: MySQL debe escuchar en 0.0.0.0 o en la IP pública, no solo en 127.0.0.1.';
        } else {
            $lines[] = '   [Verificación] Conexión fallida → revisar bind_address de MySQL en el servidor de BD.';
        }
        $lines[] = '';

        // Sugerencia y posible causa
        $posibleCausa = [];
        if (!$dirResolved && !$dirAltResolved) {
            $lines[] = 'Solución: La carpeta backend/BD no existe en ninguna ruta comprobada. Crear la carpeta y copiar los 3 .pem (server-ca, client-cert, client-key). La ruta depende de dónde esté el proyecto (D:, Linux, etc.).';
            $posibleCausa[] = 'Falta carpeta de certificados.';
        } elseif (!$caExists || !$certExists || !$keyExists) {
            $lines[] = 'Solución: Copiar los 3 archivos .pem en la carpeta que sí existe (ver rutas resueltas arriba).';
            $posibleCausa[] = 'Faltan uno o más .pem.';
        } elseif (!$caReadable || !$certReadable || !$keyReadable) {
            $lines[] = 'Solución: Revisar permisos (chmod 600 o 644) para que el usuario del servidor web pueda leer los .pem.';
            $posibleCausa[] = 'Permisos de lectura de los .pem.';
        } elseif (!$pdoLoaded || !$pdoMysqlLoaded) {
            $lines[] = 'Solución: Habilitar extensiones PDO y pdo_mysql en php.ini.';
            $posibleCausa[] = 'Extensiones PHP (pdo_mysql).';
        } elseif (!$connected) {
            $lines[] = 'Solución: Archivos OK pero la BD rechaza la conexión. Revisar: red/firewall (' . $host . ':' . $port . '), credenciales y que el usuario tenga acceso desde esta IP.';
            if ($lastError) {
                if (strpos($lastError, '2002') !== false || stripos($lastError, 'timed out') !== false) {
                    $posibleCausa[] = 'Red/firewall: IP de este servidor no autorizada o timeout (código 2002).';
                } elseif (strpos($lastError, '1045') !== false) {
                    $posibleCausa[] = 'Credenciales o acceso denegado (código 1045).';
                } elseif (strpos($lastError, '2026') !== false) {
                    $posibleCausa[] = 'Certificado SSL rechazado (código 2026).';
                } else {
                    $posibleCausa[] = 'Conexión rechazada; ver mensaje PDO arriba.';
                }
            } else {
                $posibleCausa[] = 'Conexión rechazada (revisar pasos 1–3 y socket TCP).';
            }
        }
        if (!empty($posibleCausa)) {
            $lines[] = '';
            $lines[] = 'Posible causa: ' . implode(' ', $posibleCausa);
        }

        $report = implode("\n", $lines);
        $reportHtml = htmlspecialchars($report, ENT_QUOTES, 'UTF-8');
        $reportHtml = str_replace(
            ['Sí', 'No', 'NO EXISTE', 'Existe', 'Error:', 'Error PDO:', 'Solución:', 'Posible causa:', 'FALLO', 'Socket TCP: OK', 'Socket TCP: FALLO'],
            ['<span class="ok">Sí</span>', '<span class="error">No</span>', '<span class="error">NO EXISTE</span>', '<span class="ok">Existe</span>', '<span class="error">Error:</span>', '<span class="error">Error PDO:</span>', '<span class="warning">Solución:</span>', '<span class="warning">Posible causa:</span>', '<span class="error">FALLO</span>', '<span class="ok">Socket TCP: OK</span>', '<span class="error">Socket TCP: FALLO</span>'],
            $reportHtml
        );
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Diagnóstico de conexiones</title>
            <meta charset="UTF-8">
            <style>
                body { font-family: 'Courier New', monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; margin: 0; }
                .container { max-width: 920px; margin: 0 auto; background: #252526; padding: 24px; border-radius: 8px; }
                pre { white-space: pre-wrap; word-wrap: break-word; line-height: 1.5; font-size: 13px; }
                .ok { color: #4ec9b0; }
                .error { color: #f48771; }
                .warning { color: #dcdcaa; }
                .btn { display: inline-block; padding: 10px 20px; background: #0e639c; color: #fff; text-decoration: none; border-radius: 4px; margin: 8px 4px; border: none; cursor: pointer; font-size: 14px; }
                .btn:hover { background: #1177bb; }
                .header { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #444; }
                .header h1 { color: #4ec9b0; margin: 0 0 8px 0; font-size: 1.25rem; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔌 Diagnóstico de conexiones (BD direcciones alternas)</h1>
                </div>
                <pre><?php echo $reportHtml; ?></pre>
                <div style="margin-top: 20px;">
                    <button onclick="window.location.reload()" class="btn">🔄 Actualizar</button>
                    <button onclick="window.close()" class="btn">✖️ Cerrar</button>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Ejecuta diagnóstico completo del sistema Segundómetro (SSH, path, listar archivos, etc.).
     * Solo accesible para usuario con id 1.
     */
    public function diagnosticoSegundometro()
    {
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 1) {
            header('Location: /inicio');
            exit;
        }
        $scriptPath = __DIR__ . '/../scripts/diagnostico_segundometro.php';

        if (!file_exists($scriptPath)) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            echo '<h1>Error</h1><p>Script de diagnóstico no encontrado</p>';
            exit;
        }

        // Ejecutar script y capturar salida
        ob_start();
        include $scriptPath;
        $output = ob_get_clean();

        // Si ya es HTML completo, mostrarlo directamente
        if (strpos($output, '<!DOCTYPE') !== false || strpos($output, '<html>') !== false) {
            echo $output;
            exit;
        }

        // Si no, es texto plano - no hacer htmlspecialchars en los emojis
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Diagnóstico Shell Segundómetro - Servidor</title>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    background: #1e1e1e;
                    color: #d4d4d4;
                    padding: 20px;
                    margin: 0;
                }
                .container {
                    max-width: 1400px;
                    margin: 0 auto;
                    background: #252526;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
                }
                pre {
                    white-space: pre-wrap;
                    word-wrap: break-word;
                    line-height: 1.6;
                    font-size: 13px;
                }
                .ok { color: #4ec9b0; }
                .error { color: #f48771; }
                .warning { color: #dcdcaa; }
                .info { color: #9cdcfe; }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background: #0e639c;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 10px 5px;
                    cursor: pointer;
                    border: none;
                    font-size: 14px;
                }
                .btn:hover {
                    background: #1177bb;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #444;
                }
                .header h1 {
                    color: #4ec9b0;
                    margin: 0 0 10px 0;
                }
                .header p {
                    color: #888;
                    margin: 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔍 Diagnóstico Shell Segundómetro</h1>
                    <p>Ejecutado en: <?php echo $_SERVER['SERVER_NAME'] ?? 'servidor'; ?> | <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
                <pre><?php
                // Colorear salida sin escapar los emojis
                $colored = $output;
                $colored = str_replace('✅', '<span class="ok">✅</span>', $colored);
                $colored = str_replace('❌', '<span class="error">❌</span>', $colored);
                $colored = str_replace('⚠️', '<span class="warning">⚠️</span>', $colored);
                $colored = str_replace('ℹ️', '<span class="info">ℹ️</span>', $colored);
                echo $colored;
                ?></pre>
                <div style="text-align: center; margin-top: 30px;">
                    <button onclick="window.location.reload()" class="btn">🔄 Ejecutar de nuevo</button>
                    <button onclick="window.close()" class="btn">✖️ Cerrar</button>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Inicia el flujo 1-click de API documentación en segundo plano.
     * Solo usuario 878.
     *
     * Punto único pactado desde la web: ejecuta launcher\web-api-1click-runner.bat
     * → Iniciar-API-Verificacion.bat (doctor, auto-fix/install si aplica, arranque).
     * Quien mantenga Python portable en API\tools y Tesseract debe hacerlo vía despliegue;
     * el usuario 878 no debe depender de consola manual en el servidor.
     */
    public function apiDocOneClickIniciar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        if (stripos(PHP_OS, 'WIN') !== 0) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Este flujo 1-click está diseñado para Windows Server']);
            return;
        }

        $apiDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'API';
        $launcherDir = $apiDir . DIRECTORY_SEPARATOR . 'launcher';
        $runner = $launcherDir . DIRECTORY_SEPARATOR . 'web-api-1click-runner.bat';
        $logsDir = $apiDir . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0777, true);
        }

        if (!is_file($runner)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró el runner 1-click',
                'runner' => $runner,
            ]);
            return;
        }

        $running = $this->apiDocOneClickHayProcesoActivo();
        if ($running['is_running']) {
            echo json_encode([
                'success' => true,
                'started' => false,
                'message' => 'Ya hay una ejecución en curso. Mostrando log actual...',
                'log_file' => basename((string)($running['log_file'] ?? '')),
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $logName = 'web-api-1click-' . date('Ymd-His') . '.log';
        $logPath = $logsDir . DIRECTORY_SEPARATOR . $logName;
        $_SESSION['api_doc_1click_log'] = $logPath;
        $_SESSION['api_doc_1click_started_at'] = time();

        $runnerQ = '"' . str_replace('"', '""', $runner) . '"';
        $logQ = '"' . str_replace('"', '""', $logPath) . '"';
        $cmd = 'start "" /b cmd /c ""' . str_replace('"', '""', $runner) . '" > "' . str_replace('"', '""', $logPath) . '" 2>&1"';
        @pclose(@popen($cmd, 'r'));

        // Pequeña pausa para que el log nazca y no se vea vacío al primer poll
        usleep(250000);

        echo json_encode([
            'success' => true,
            'started' => true,
            'message' => 'Ejecución 1-click iniciada.',
            'log_file' => $logName,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Devuelve estado y tail del log de la ejecución 1-click.
     * Solo usuario 878.
     */
    public function apiDocOneClickEstado()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $logPath = (string)($_SESSION['api_doc_1click_log'] ?? '');
        if ($logPath === '' || !is_file($logPath)) {
            echo json_encode([
                'success' => true,
                'has_log' => false,
                'is_running' => false,
                'completed' => false,
                'message' => 'Aún no hay ejecución iniciada.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $content = $this->leerTailArchivo($logPath, 64000);
        $completed = (strpos($content, '__FIN__:') !== false);
        $exitCode = null;
        if ($completed && preg_match('/__FIN__:(-?\d+)/', $content, $m)) {
            $exitCode = (int)$m[1];
        }
        // Si el .bat murió por sintaxis CMD, no llega __FIN__: — detectar para no dejar el UI colgado en "Ejecutando".
        $fatalBatch = $this->apiDocOneClickDetectFatalBatchError($content);
        if ($fatalBatch) {
            $completed = true;
            if ($exitCode === null) {
                $exitCode = 1;
            }
        }
        $startedAt = (int)($_SESSION['api_doc_1click_started_at'] ?? 0);
        if ($startedAt > 0 && (time() - $startedAt) > 7200 && !$completed && !$fatalBatch) {
            $completed = true;
            if ($exitCode === null) {
                $exitCode = 1;
            }
            $content .= "\r\n\r\n[AVISO PANEL] Lleva más de 2 horas sin marca de fin en el log.\r\n";
            $content .= "Puede usar «Desbloquear panel» y pulsar API de nuevo (el .bat del servidor puede seguir un rato).\r\n";
        }

        echo json_encode([
            'success' => true,
            'has_log' => true,
            'is_running' => !$completed,
            'completed' => $completed,
            'exit_code' => $exitCode,
            'log_file' => basename($logPath),
            'output_tail' => $content,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function apiDocOneClickHayProcesoActivo(): array
    {
        $logPath = (string)($_SESSION['api_doc_1click_log'] ?? '');
        if ($logPath === '' || !is_file($logPath)) {
            return ['is_running' => false];
        }
        $startedAt = (int)($_SESSION['api_doc_1click_started_at'] ?? 0);
        // Si lleva más de 2 h sin marca de fin en el log, liberar para poder pulsar API de nuevo
        // (pip/torch pueden tardar, pero así no queda bloqueado eternamente el panel por un colgajo).
        if ($startedAt > 0 && (time() - $startedAt) > 7200) {
            return ['is_running' => false, 'log_file' => $logPath, 'panel_stale' => true];
        }
        $tail = $this->leerTailArchivo($logPath, 16000);
        if (strpos($tail, '__FIN__:') !== false) {
            return ['is_running' => false, 'log_file' => $logPath];
        }
        if ($this->apiDocOneClickDetectFatalBatchError($tail)) {
            return ['is_running' => false, 'log_file' => $logPath];
        }
        return ['is_running' => true, 'log_file' => $logPath];
    }

    /**
     * Quita la sesión 1-click para poder lanzar una nueva ejecución desde la web (usuario 878).
     * No mata procesos ya arrancados en el servidor — solo desbloquea el botón respecto de PHP.
     */
    public function apiDocOneClickOlvidar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);

            return;
        }
        unset($_SESSION['api_doc_1click_log'], $_SESSION['api_doc_1click_started_at']);
        echo json_encode([
            'success' => true,
            'message' => 'Panel desbloqueado. Ya puedes pulsar API otra vez. Si había instalación en curso, puede seguir un rato en segundo plano en el servidor.',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Intenta detener el proceso 1-click/API atascado en el servidor y desbloquea el panel (usuario 878).
     * Ejecuta launcher\web-api-1click-parar.bat en segundo plano (log en backend/API/logs).
     */
    public function apiDocOneClickParar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado'], JSON_UNESCAPED_UNICODE);

            return;
        }
        if (stripos(PHP_OS, 'WIN') !== 0) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Solo Windows'], JSON_UNESCAPED_UNICODE);

            return;
        }
        $apiDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'API';
        $launcherDir = $apiDir . DIRECTORY_SEPARATOR . 'launcher';
        $stopper = $launcherDir . DIRECTORY_SEPARATOR . 'web-api-1click-parar.bat';
        $logsDir = $apiDir . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0777, true);
        }
        if (!is_file($stopper)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró web-api-1click-parar.bat en launcher',
            ], JSON_UNESCAPED_UNICODE);

            return;
        }
        $logName = 'web-api-1click-parar-' . date('Ymd-His') . '.log';
        $logPath = $logsDir . DIRECTORY_SEPARATOR . $logName;

        $cmd = 'start "" /b cmd /c ""'
            . str_replace('"', '""', $stopper) . '" > "'
            . str_replace('"', '""', $logPath) . '" 2>&1"';
        @pclose(@popen($cmd, 'r'));
        usleep(200000);

        unset($_SESSION['api_doc_1click_log'], $_SESSION['api_doc_1click_started_at']);

        echo json_encode([
            'success' => true,
            'message' => 'Parada solicitada: se liberó el panel; en el servidor se intenta cortar el batch/doctor/Python de esta API y el puerto 8000. Revise el log "' . $logName . '" (Lista → seleccionar ese archivo).',
            'log_file' => $logName,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function apiDocOneClickDetectFatalBatchError(string $content): bool
    {
        if ($content === '') {
            return false;
        }
        $patterns = [
            '. was unexpected at this time.',
            'was unexpected at this time.',
            "'-' is not recognized",
            'is not recognized as an internal or external command',
        ];
        foreach ($patterns as $p) {
            if (stripos($content, $p) !== false) {
                return true;
            }
        }
        return false;
    }

    private function leerTailArchivo(string $path, int $maxBytes = 64000): string
    {
        if (!is_file($path)) {
            return '';
        }
        $size = @filesize($path);
        if (!is_int($size) || $size <= 0) {
            return '';
        }

        $fp = @fopen($path, 'rb');
        if (!$fp) {
            return '';
        }

        $readBytes = min($maxBytes, $size);
        if ($size > $readBytes) {
            @fseek($fp, $size - $readBytes);
        }
        $data = @fread($fp, $readBytes);
        @fclose($fp);

        if ($data === false) {
            return '';
        }
        return (string)$data;
    }

    /**
     * Lista archivos .log permitidos en backend/API/logs (para panel web usuario 878).
     */
    public function apiDocLogListar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $dir = $this->apiDocLogsDirResolved();
        if ($dir === '') {
            echo json_encode(['success' => false, 'message' => 'No existe carpeta logs de la API']);
            return;
        }
        $items = [];
        $dh = @opendir($dir);
        if ($dh === false) {
            echo json_encode(['success' => false, 'message' => 'No se puede leer la carpeta logs']);
            return;
        }
        while (($f = readdir($dh)) !== false) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            if (!$this->apiDocLogBasenamePermitido($f)) {
                continue;
            }
            $full = $dir . DIRECTORY_SEPARATOR . $f;
            if (!is_file($full)) {
                continue;
            }
            $items[] = [
                'name' => $f,
                'size' => @filesize($full) ?: 0,
                'mtime' => @filemtime($full) ?: 0,
            ];
        }
        closedir($dh);
        usort($items, static function ($a, $b) {
            return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
        });
        echo json_encode([
            'success' => true,
            'logs_dir' => 'backend/API/logs',
            'files' => $items,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Devuelve contenido de un log permitido (JSON). GET ?archivo=nombre.log&completo=1
     */
    public function apiDocLogContenido()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $base = isset($_GET['archivo']) ? (string) $_GET['archivo'] : '';
        $completo = !empty($_GET['completo']) && $_GET['completo'] !== '0' && $_GET['completo'] !== 'false';
        $resolved = $this->apiDocLogResolveSafe($base);
        if ($resolved === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Archivo no permitido o no encontrado']);
            return;
        }
        $maxFull = 524288; // 512 KiB
        $maxTail = 131072; // 128 KiB
        $size = @filesize($resolved);
        if (!is_int($size) || $size <= 0) {
            echo json_encode([
                'success' => true,
                'archivo' => basename($resolved),
                'size' => 0,
                'truncado' => false,
                'contenido' => '',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($completo && $size > $maxFull) {
            $content = $this->leerTailArchivo($resolved, $maxFull);
            echo json_encode([
                'success' => true,
                'archivo' => basename($resolved),
                'size' => $size,
                'truncado' => true,
                'nota' => 'Archivo muy grande: se muestran solo los últimos 512 KiB. Use descargar para el archivo completo.',
                'contenido' => $content,
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!$completo) {
            $content = $this->leerTailArchivo($resolved, $maxTail);
            echo json_encode([
                'success' => true,
                'archivo' => basename($resolved),
                'size' => $size,
                'truncado' => $size > $maxTail,
                'contenido' => $content,
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        $content = @file_get_contents($resolved);
        if ($content === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo']);
            return;
        }
        $jsonFlags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $jsonFlags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        echo json_encode([
            'success' => true,
            'archivo' => basename($resolved),
            'size' => $size,
            'truncado' => false,
            'contenido' => $content,
        ], $jsonFlags);
    }

    /**
     * Descarga un log permitido como adjunto.
     */
    public function apiDocLogDescargar()
    {
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'No autorizado';
            return;
        }
        $base = isset($_GET['archivo']) ? (string) $_GET['archivo'] : '';
        $resolved = $this->apiDocLogResolveSafe($base);
        if ($resolved === '') {
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Archivo no permitido o no encontrado';
            return;
        }
        $name = basename($resolved);
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
        header('X-Content-Type-Options: nosniff');
        $size = @filesize($resolved);
        if (is_int($size) && $size > 0) {
            header('Content-Length: ' . $size);
        }
        readfile($resolved);
        exit;
    }

    private function apiDocLogsDirResolved(): string
    {
        $apiDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'API';
        $logs = $apiDir . DIRECTORY_SEPARATOR . 'logs';
        $r = @realpath($logs);
        return is_string($r) && is_dir($r) ? $r : '';
    }

    private function apiDocLogBasenamePermitido(string $base): bool
    {
        if ($base === '' || strpbrk($base, "\\/") !== false) {
            return false;
        }
        // Solo nombres planos *.log dentro de backend/API/logs (sin .. ni rutas).
        // Lista blanca muy estricta ocultaba archivos válidos nuevos → el usuario 878 veía el desplegable vacío.
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*\.log$/', $base);
    }

    /**
     * Resuelve nombre seguro bajo API/logs; cadena vacía si inválido.
     */
    private function apiDocLogResolveSafe(string $base): string
    {
        $base = trim($base);
        if (!$this->apiDocLogBasenamePermitido($base)) {
            return '';
        }
        $dir = $this->apiDocLogsDirResolved();
        if ($dir === '') {
            return '';
        }
        $full = $dir . DIRECTORY_SEPARATOR . $base;
        if (!is_file($full)) {
            return '';
        }
        $real = @realpath($full);
        if (!is_string($real) || !is_file($real)) {
            return '';
        }
        $dirNorm = strtolower(str_replace('\\', '/', $dir));
        $realNorm = strtolower(str_replace('\\', '/', $real));
        if ($realNorm !== $dirNorm && strpos($realNorm, $dirNorm . '/') !== 0) {
            return '';
        }
        return $real;
    }

    /**
     * Estado de los servicios locales (puertos del orquestador).
     * Solo usuario 878.
     *
     * Lee netstat para detectar qué puertos están en LISTENING y, si lo está,
     * intenta una llamada HTTP corta a su /health o /docs (la que cada servicio
     * usa) para confirmar que responde y, si la respuesta trae JSON con el
     * mismo formato, lo expone tal cual.
     *
     * Respuesta JSON:
     * {
     *   success: true,
     *   generated_at: "2026-05-07 14:30:00",
     *   summary: { up: 4, down: 1, total: 5 },
     *   services: [
     *     { id, name, port, role, url_check, listening:bool, http_ok:bool,
     *       http_status:int|null, http_ms:int|null, latency_ms:int|null,
     *       url_browser, hint }
     *   ]
     * }
     */
    public function serviciosLocalesEstado()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $servicios = $this->serviciosLocalesCatalogo();

        $listening = $this->serviciosLocalesPuertosEnListen();

        $out = [];
        $up = 0;
        foreach ($servicios as $s) {
            $port = (int)$s['port'];
            $isListen = isset($listening[$port]);
            $http = ['ok' => false, 'status' => null, 'ms' => null];
            if ($isListen) {
                $http = $this->serviciosLocalesProbarHttp($s['url_check'], 1500);
            }
            if ($isListen && $http['ok']) {
                $up++;
                $estado = 'up';
            } elseif ($isListen) {
                $estado = 'listen_no_http';
            } else {
                $estado = 'down';
            }
            $out[] = [
                'id'         => $s['id'],
                'name'       => $s['name'],
                'port'       => $port,
                'role'       => $s['role'],
                'url_check'  => $s['url_check'],
                'url_browser'=> $s['url_browser'],
                'listening'  => $isListen,
                'pid'        => $isListen ? ($listening[$port] ?? null) : null,
                'http_ok'    => $http['ok'],
                'http_status'=> $http['status'],
                'latency_ms' => $http['ms'],
                'estado'     => $estado, // up | listen_no_http | down
                'hint'       => $s['hint'],
                'can_control'=> true,
            ];
        }

        echo json_encode([
            'success'      => true,
            'generated_at' => date('Y-m-d H:i:s'),
            'summary'      => [
                'up'    => $up,
                'down'  => count($servicios) - $up,
                'total' => count($servicios),
            ],
            'services'     => $out,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Ejecuta acción sobre un servicio local desde el panel web (usuario 878).
     * Acciones: iniciar | parar | reiniciar
     */
    public function serviciosLocalesAccion()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        if (stripos(PHP_OS, 'WIN') !== 0) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Solo disponible en Windows']);
            return;
        }

        $serviceId = trim((string)($_POST['service'] ?? $_GET['service'] ?? ''));
        $action = trim((string)($_POST['action'] ?? $_GET['action'] ?? ''));
        if ($serviceId === '' || $action === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros service/action']);
            return;
        }
        if (!in_array($action, ['iniciar', 'parar', 'reiniciar'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción inválida']);
            return;
        }

        $byId = [];
        foreach ($this->serviciosLocalesCatalogo() as $srv) {
            $byId[$srv['id']] = $srv;
        }
        if (!isset($byId[$serviceId])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
            return;
        }
        $srv = $byId[$serviceId];
        $port = (int)$srv['port'];

        $ok = false;
        if ($action === 'iniciar') {
            $ok = $this->serviciosLocalesIniciar($srv);
            usleep(350000);
        } elseif ($action === 'parar') {
            $ok = $this->serviciosLocalesParar($srv);
            usleep(350000);
        } else { // reiniciar
            $this->serviciosLocalesParar($srv);
            usleep(800000);
            $ok = $this->serviciosLocalesIniciar($srv);
            usleep(350000);
        }

        $listening = $this->serviciosLocalesPuertosEnListen();
        $isListen = isset($listening[$port]);
        $http = ['ok' => false, 'status' => null, 'ms' => null];
        if ($isListen) {
            $http = $this->serviciosLocalesProbarHttp((string)$srv['url_check'], 1400);
        }
        $estado = ($isListen && $http['ok']) ? 'up' : ($isListen ? 'listen_no_http' : 'down');
        $st = $http['status'];
        $stStr = $st === null || $st === '' ? '—' : (string) $st;
        $hintPost = 'Tras la orden: puerto ' . $port . ' ' . ($isListen ? 'en escucha' : 'sin proceso en escucha')
            . '. HTTP ' . ($http['ok'] ? 'OK (' . $stStr . ')' : 'sin respuesta esperada (' . $stStr . ').');

        echo json_encode([
            'success' => (bool) $ok,
            'message' => ucfirst($action) . ' enviado para ' . $srv['name'] . '.',
            'hint_post' => $hintPost,
            'service' => $serviceId,
            'action'  => $action,
            'estado'  => $estado,
            'listening' => $isListen,
            'pid' => $isListen ? ($listening[$port] ?? null) : null,
            'http_status' => $http['status'],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Diagnóstico de red/config hacia la API de verificación de expediente (Python),
     * ejecutado en el mismo servidor que PHP — sin SSH. Solo usuario 878.
     * GET → JSON: health, TCP, GET/POST cortos a /validar-expediente, timeouts en ini.
     */
    public function docVerificacionDiagnostico878()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ((int) ($_SESSION['usuario_id'] ?? 0) !== 878) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $payload = $this->docVerificacion878ArmarDiagnostico();
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    private function docVerificacion878ArmarDiagnostico(): array
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (dirname(__DIR__) . '/config/config.ini');
        $out = [
            'success'       => true,
            'generated_at'  => date('Y-m-d H:i:s'),
            'config_path'   => $configFile,
            'config_exists' => is_file($configFile),
        ];
        if (!is_file($configFile)) {
            $out['success'] = false;
            $out['message'] = 'No existe config.ini en la ruta esperada.';
            return $out;
        }
        $config = @parse_ini_file($configFile, true);
        if (!is_array($config)) {
            $out['success'] = false;
            $out['message'] = 'No se pudo leer config.ini (parse_ini_file).';
            return $out;
        }
        $doc = $config['doc_verificacion'] ?? [];
        if (!is_array($doc)) {
            $out['success'] = false;
            $out['message'] = 'Falta la sección [doc_verificacion] en config.ini.';
            return $out;
        }
        $apiUrlVerificar = trim((string) ($doc['api_url'] ?? ''));
        $apiKey = trim((string) ($doc['api_key'] ?? ''));
        $timeoutExp = isset($doc['validar_expediente_timeout_seconds']) ? (int) $doc['validar_expediente_timeout_seconds'] : 300;
        $retries = isset($doc['validar_expediente_retries']) ? (int) $doc['validar_expediente_retries'] : 2;
        $out['config'] = [
            'api_url_length'     => strlen($apiUrlVerificar),
            'api_url_tail'       => strlen($apiUrlVerificar) > 120 ? ('…' . substr($apiUrlVerificar, -120)) : $apiUrlVerificar,
            'api_key_hint'       => $this->docVerificacion878EnmascararApiKey($apiKey),
            'timeout_seconds'    => $timeoutExp,
            'retries_configured' => $retries,
            'note'               => 'Misma base URL que CapHum::validarExpedienteApi (se quita /verificar al final si existe).',
        ];
        if ($apiUrlVerificar === '' || $apiKey === '') {
            $out['success'] = false;
            $out['message'] = 'En [doc_verificacion] faltan api_url o api_key.';
            return $out;
        }
        $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrlVerificar);
        $baseUrl = rtrim((string) $baseUrl, '/');
        $healthUrl = $baseUrl . '/health';
        $validarUrl = $baseUrl . '/validar-expediente';
        $out['urls'] = [
            'base_resolved' => $baseUrl,
            'health'        => $healthUrl,
            'validar'       => $validarUrl,
        ];

        $healthAttempts = [];
        $healthOk = false;
        for ($hi = 0; $hi < 2; $hi++) {
            if ($hi > 0) {
                usleep(500000);
            }
            $t0 = microtime(true);
            $hc = curl_init($healthUrl);
            curl_setopt_array($hc, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT_MS => 8000,
                CURLOPT_CONNECTTIMEOUT_MS => 4000,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
            ]);
            $hBody = curl_exec($hc);
            $hCode = (int) curl_getinfo($hc, CURLINFO_HTTP_CODE);
            $hErr = curl_error($hc);
            $hErrno = (int) curl_errno($hc);
            curl_close($hc);
            $ms = (int) round((microtime(true) - $t0) * 1000);
            $snippet = is_string($hBody) ? substr(preg_replace('/\s+/', ' ', $hBody), 0, 200) : '';
            $healthAttempts[] = [
                'attempt'    => $hi + 1,
                'http_code'  => $hCode,
                'ms'         => $ms,
                'curl_errno' => $hErrno,
                'curl_error' => $hErr !== '' ? $hErr : null,
                'body_snip'  => $snippet,
            ];
            if ($hCode === 200 && $hBody !== false) {
                $healthOk = true;
                break;
            }
        }
        $out['health'] = [
            'ok'       => $healthOk,
            'attempts' => $healthAttempts,
        ];

        $pu = parse_url($baseUrl);
        $host = isset($pu['host']) ? (string) $pu['host'] : '';
        $scheme = strtolower((string) ($pu['scheme'] ?? 'http'));
        $port = isset($pu['port']) ? (int) $pu['port'] : ($scheme === 'https' ? 443 : 80);
        $tcp = ['host' => $host, 'port' => $port, 'ok' => false, 'errno' => null, 'errstr' => null, 'ms' => null];
        if ($host !== '') {
            $t0 = microtime(true);
            $fp = @fsockopen($host, $port, $errno, $errstr, 5);
            $tcp['ms'] = (int) round((microtime(true) - $t0) * 1000);
            if ($fp !== false) {
                $tcp['ok'] = true;
                fclose($fp);
            } else {
                $tcp['errno'] = $errno;
                $tcp['errstr'] = $errstr;
            }
        } else {
            $tcp['errstr'] = 'No se pudo extraer host de base_resolved.';
        }
        $out['tcp_connect'] = $tcp;

        $t0 = microtime(true);
        $chGet = curl_init($validarUrl);
        curl_setopt_array($chGet, [
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
        ]);
        $gBody = curl_exec($chGet);
        $gCode = (int) curl_getinfo($chGet, CURLINFO_HTTP_CODE);
        $gErr = curl_error($chGet);
        $gErrno = (int) curl_errno($chGet);
        curl_close($chGet);
        $out['get_validar_expediente'] = [
            'note'       => 'Muchas APIs responden 405 en GET si el endpoint solo acepta POST; indica que el servidor HTTP contestó.',
            'http_code'  => $gCode,
            'ms'         => (int) round((microtime(true) - $t0) * 1000),
            'curl_errno' => $gErrno,
            'curl_error' => $gErr !== '' ? $gErr : null,
            'body_snip'  => is_string($gBody) ? substr(preg_replace('/\s+/', ' ', $gBody), 0, 220) : '',
        ];

        $t0 = microtime(true);
        $postFields = ['tipo_documento' => '__diag878_sin_archivos__'];
        $chPost = curl_init($validarUrl);
        curl_setopt_array($chPost, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 22,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);
        $pBody = curl_exec($chPost);
        $pCode = (int) curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        $pErr = curl_error($chPost);
        $pErrno = (int) curl_errno($chPost);
        curl_close($chPost);
        $out['post_validar_expediente_minimo'] = [
            'note'       => 'POST sin PDF (solo tipo_documento). Si responde 4xx rápido, la ruta vive; timeout aquí sugiere servicio colgado al leer cuerpo.',
            'http_code'  => $pCode,
            'ms'         => (int) round((microtime(true) - $t0) * 1000),
            'curl_errno' => $pErrno,
            'curl_error' => $pErr !== '' ? $pErr : null,
            'body_snip'  => is_string($pBody) ? substr(preg_replace('/\s+/', ' ', $pBody), 0, 320) : '',
        ];

        $interpretacion = [];
        if (!$healthOk) {
            $interpretacion[] = 'Health falló: la API no respondió 200 en /health (revisar URL base, firewall o que uvicorn esté arriba).';
        }
        if (!$tcp['ok'] && $host !== '') {
            $interpretacion[] = 'TCP a host:puerto falló: PHP en este servidor no abre socket al host configurado (firewall, IP incorrecta o servicio caído).';
        }
        if ($pErrno === 28 || ($pErr !== '' && stripos($pErr, 'timed out') !== false)) {
            $interpretacion[] = 'POST mínimo hizo timeout: mismo síntoma que expedientes reales; revisar logs del proceso Python.';
        } elseif ($pCode === 0 && $pErrno !== 0) {
            $interpretacion[] = 'POST mínimo sin HTTP: error de transporte cURL (' . $pErrno . ').';
        } elseif (in_array($pCode, [400, 401, 403, 404, 422], true)) {
            $interpretacion[] = 'POST mínimo obtuvo HTTP ' . $pCode . ': la ruta existe y rechaza el payload vacío (esperado); el canal PHP→API para POST funciona.';
        }
        if ($interpretacion === []) {
            $interpretacion[] = 'Revise códigos HTTP y tiempos arriba. Si health y POST mínimo son OK pero el expediente real hace timeout, suele ser OCR/PDFs grandes o un worker único bloqueado.';
        }
        $out['interpretacion'] = $interpretacion;

        return $out;
    }

    private function docVerificacion878EnmascararApiKey(string $k): string
    {
        $len = strlen($k);
        if ($len === 0) {
            return '(vacía)';
        }
        if ($len <= 6) {
            return '(longitud ' . $len . ', oculta)';
        }

        return 'longitud ' . $len . ' · sufijo …' . substr($k, -4);
    }

    /** Catálogo único de servicios locales para estado y acciones. */
    private function serviciosLocalesCatalogo(): array
    {
        $backendRoot = dirname(__DIR__);
        return [
            [
                'id'   => 'doc_candidato',
                'name' => 'API documentación candidato (Node)',
                'port' => 3001,
                'role' => 'API Node — validación de documentos en alta de candidato',
                'url_check'   => 'http://127.0.0.1:3001/',
                'url_browser' => 'http://127.0.0.1:3001/',
                'hint' => 'Si está caída: backend/API/documentacion-candidato/iniciar-agente.bat',
                'start_bat' => $backendRoot . DIRECTORY_SEPARATOR . 'API' . DIRECTORY_SEPARATOR . 'documentacion-candidato' . DIRECTORY_SEPARATOR . 'iniciar-agente.bat',
                'stop_ps1'  => $backendRoot . DIRECTORY_SEPARATOR . 'API' . DIRECTORY_SEPARATOR . 'documentacion-candidato' . DIRECTORY_SEPARATOR . 'cerrar-agente.ps1',
            ],
            [
                'id'   => 'segundometro',
                'name' => 'Agente Segundómetro (Node)',
                'port' => 3100,
                'role' => 'Agente cron de reportes Segundómetro vía SSH',
                'url_check'   => 'http://127.0.0.1:3100/health',
                'url_browser' => 'http://127.0.0.1:3100/health',
                'hint' => 'Si está caída: backend/services/segundometro-agent/iniciar-agente.bat',
                'start_bat' => $backendRoot . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'segundometro-agent' . DIRECTORY_SEPARATOR . 'iniciar-agente.bat',
                'stop_ps1'  => $backendRoot . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'segundometro-agent' . DIRECTORY_SEPARATOR . 'cerrar-agente.ps1',
            ],
            [
                'id'   => 'correos_pp',
                'name' => 'Agente correos primeros pagos (Node)',
                'port' => 3110,
                'role' => 'Genera y envía correos de primeros pagos de cobranza',
                'url_check'   => 'http://127.0.0.1:3110/health',
                'url_browser' => 'http://127.0.0.1:3110/health',
                'hint' => 'Si está caída: backend/services/correos-primeros-pagos-agent/iniciar-agente.bat',
                'start_bat' => $backendRoot . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'correos-primeros-pagos-agent' . DIRECTORY_SEPARATOR . 'iniciar-agente.bat',
                'stop_bat'  => $backendRoot . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'correos-primeros-pagos-agent' . DIRECTORY_SEPARATOR . 'cerrar-agente.bat',
            ],
            [
                'id'   => 'gastos_cobranza',
                'name' => 'Agente Gastos cobranza (Node)',
                'port' => 3120,
                'role' => 'Reportes de cobranza, worker EC, lista negra, descargo estatus 3',
                'url_check'   => 'http://127.0.0.1:3120/health',
                'url_browser' => 'http://127.0.0.1:3120/health',
                'hint' => 'Si está caída: backend/services/gastos-cobranza-agent/iniciar-agente.bat',
                'start_bat' => $backendRoot . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'gastos-cobranza-agent' . DIRECTORY_SEPARATOR . 'iniciar-agente.bat',
                'stop_ps1'  => $backendRoot . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'gastos-cobranza-agent' . DIRECTORY_SEPARATOR . 'cerrar-agente.ps1',
            ],
            [
                'id'   => 'api_doc_python',
                'name' => 'API verificación documentos (Python · uvicorn)',
                'port' => 8000,
                'role' => 'OCR + verificación documental (FastAPI 1-click)',
                'url_check'   => 'http://127.0.0.1:8000/docs',
                'url_browser' => 'http://127.0.0.1:8000/docs',
                'hint' => 'Si está caída: backend/API/launcher/iniciar-agente.bat',
                'start_bat' => $backendRoot . DIRECTORY_SEPARATOR . 'API' . DIRECTORY_SEPARATOR . 'launcher' . DIRECTORY_SEPARATOR . 'iniciar-agente.bat',
                'stop_ps1'  => $backendRoot . DIRECTORY_SEPARATOR . 'API' . DIRECTORY_SEPARATOR . 'launcher' . DIRECTORY_SEPARATOR . 'cerrar-agente.ps1',
            ],
        ];
    }

    private function serviciosLocalesIniciar(array $srv): bool
    {
        $bat = (string)($srv['start_bat'] ?? '');
        if ($bat === '' || !is_file($bat)) {
            return false;
        }
        $cmd = 'start "" /b cmd /c ""' . str_replace('"', '""', $bat) . '""';
        @pclose(@popen($cmd, 'r'));
        return true;
    }

    private function serviciosLocalesParar(array $srv): bool
    {
        $ok = false;
        $stopPs1 = (string)($srv['stop_ps1'] ?? '');
        $stopBat = (string)($srv['stop_bat'] ?? '');
        if ($stopPs1 !== '' && is_file($stopPs1)) {
            $ps = '"' . str_replace('"', '""', $stopPs1) . '"';
            $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . $ps . ' -Silent';
            @shell_exec($cmd);
            $ok = true;
        } elseif ($stopBat !== '' && is_file($stopBat)) {
            $cmd = 'cmd /c "' . str_replace('"', '""', $stopBat) . '"';
            @shell_exec($cmd);
            $ok = true;
        }
        // Fallback: matar proceso dueño del puerto.
        $port = (int)($srv['port'] ?? 0);
        if ($port > 0) {
            $this->serviciosLocalesStopPort($port);
            $ok = true;
        }
        return $ok;
    }

    private function serviciosLocalesStopPort(int $port): void
    {
        if ($port <= 0) {
            return;
        }
        $out = @shell_exec('netstat -ano');
        if (!is_string($out) || $out === '') {
            return;
        }
        $pids = [];
        foreach (preg_split('/\r?\n/', $out) as $line) {
            if (stripos($line, 'LISTENING') === false || strpos($line, ':' . $port) === false) {
                continue;
            }
            if (preg_match('/\sLISTENING\s+(\d+)\s*$/i', trim($line), $m)) {
                $pids[(int)$m[1]] = true;
            }
        }
        foreach (array_keys($pids) as $pid) {
            @shell_exec('taskkill /PID ' . (int)$pid . ' /F');
        }
    }

    /** Devuelve [puerto => pid] de los puertos TCP en LISTENING locales. */
    private function serviciosLocalesPuertosEnListen(): array
    {
        $ports = [];
        // netstat -ano funciona tanto en Windows Server como en CMD/IIS sin admin
        $cmd = stripos(PHP_OS, 'WIN') === 0 ? 'netstat -ano' : 'ss -tlnp';
        $out = @shell_exec($cmd);
        if (!is_string($out) || $out === '') {
            return $ports;
        }
        $lines = preg_split('/\r?\n/', $out);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            // Windows: "  TCP    127.0.0.1:8000   0.0.0.0:0   LISTENING   12345"
            if (stripos(PHP_OS, 'WIN') === 0) {
                if (stripos($line, 'LISTENING') === false) continue;
                if (preg_match('/(?:\[?[^\s\[\]]+\]?:|:):?(\d+)\s+\S+\s+LISTENING\s+(\d+)/i', $line, $m)) {
                    $port = (int)$m[1];
                    $pid  = (int)$m[2];
                    if ($port > 0) $ports[$port] = $pid;
                }
            } else {
                // ss -tlnp: ej. "LISTEN 0 128 *:8000 *:* users:((\"python\",pid=1234,fd=4))"
                if (preg_match('/:(\d+)\s/', $line, $m)) {
                    $ports[(int)$m[1]] = null;
                }
            }
        }
        return $ports;
    }

    /** Hace un GET corto y devuelve ['ok'=>bool, 'status'=>int|null, 'ms'=>int|null]. */
    private function serviciosLocalesProbarHttp(string $url, int $timeoutMs = 1500): array
    {
        $ch = @curl_init($url);
        if (!$ch) {
            return ['ok' => false, 'status' => null, 'ms' => null];
        }
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY         => true, // HEAD-like: nos basta el status
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 2,
            CURLOPT_TIMEOUT_MS     => $timeoutMs,
            CURLOPT_CONNECTTIMEOUT_MS => min(800, $timeoutMs),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'SpartaLedger/ServiciosLocalesEstado',
        ];
        @curl_setopt_array($ch, $opts);
        $t0 = microtime(true);
        $resp = @curl_exec($ch);
        $ms = (int)round((microtime(true) - $t0) * 1000);
        $status = (int)@curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errno  = @curl_errno($ch);
        @curl_close($ch);
        if ($errno !== 0 && $status === 0) {
            // Algunos /health solo aceptan GET completo; reintentar sin NOBODY.
            $ch2 = @curl_init($url);
            if ($ch2) {
                $opts[CURLOPT_NOBODY] = false;
                @curl_setopt_array($ch2, $opts);
                $t1 = microtime(true);
                @curl_exec($ch2);
                $ms = (int)round((microtime(true) - $t1) * 1000);
                $status = (int)@curl_getinfo($ch2, CURLINFO_RESPONSE_CODE);
                @curl_close($ch2);
            }
        }
        $ok = ($status >= 200 && $status < 500); // 4xx aún cuenta como "responde HTTP"
        return ['ok' => $ok, 'status' => $status > 0 ? $status : null, 'ms' => $ms];
    }

    private function validarActualizacionPassword()
    {
        $usuarioSesion = $_SESSION['usuario'] ?? null;

        if (!$usuarioSesion) {
            return; // si no hay sesión, no hacemos nada
        }

        // Obtener datos del usuario desde la base
        $usuarioData = UsuariosDao::getUsuarioPorNombre($usuarioSesion);

        if (!$usuarioData) {
            return;
        }

        $excluirUsuarios = ['ALSO', 'ALSO']; // usuarios que NO deben actualizar
        // Si la contraseña es igual al usuario (comparación directa), pedir cambio
        if ($usuarioData['PASS'] === $usuarioSesion && !in_array($usuarioSesion, $excluirUsuarios)) {
            // SweetAlert2 para actualizar contraseña
            $usuarioSesion = $_SESSION['usuario'] ?? '';
            echo <<<HTML
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
             const usuarioSesion = '{$usuarioSesion}';
            Swal.fire({
                title: 'Actualiza tu contraseña',
                html:
                    '<div style="position:relative;">' +
                        '<input id="newPass" type="password" class="swal2-input" placeholder="Nueva contraseña">' +
                        '<button type="button" id="toggleNew" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer;">👁️</button>' +
                    '</div>' +
                    '<div style="position:relative;">' +
                        '<input id="confirmPass" type="password" class="swal2-input" placeholder="Confirmar contraseña">' +
                        '<button type="button" id="toggleConfirm" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer;">👁️</button>' +
                    '</div>',
                confirmButtonText: 'Actualizar',
                focusConfirm: false,
                allowOutsideClick: false,
                didOpen: () => {
                    const toggleNew = document.getElementById('toggleNew');
                    const toggleConfirm = document.getElementById('toggleConfirm');
                    const newPass = document.getElementById('newPass');
                    const confirmPass = document.getElementById('confirmPass');

                    toggleNew.addEventListener('click', () => {
                        newPass.type = newPass.type === 'password' ? 'text' : 'password';
                    });
                    toggleConfirm.addEventListener('click', () => {
                        confirmPass.type = confirmPass.type === 'password' ? 'text' : 'password';
                    });
                },
                preConfirm: () => {
                    const newPass = document.getElementById('newPass').value;
                    const confirmPass = document.getElementById('confirmPass').value;
                    if (!newPass || !confirmPass) {
                        Swal.showValidationMessage('Debes llenar ambos campos');
                        return false;
                    }
                    // Validación de longitud mínima
                    if (newPass.length < 8) {
                        Swal.showValidationMessage('La contraseña debe tener al menos 8 caracteres');
                        return false;
                    }

                    if (newPass.length > 15) {
                        Swal.showValidationMessage('La contraseña no puede tener más de 20 caracteres');
                        return false;
                    }

                    if (newPass !== confirmPass) {
                        Swal.showValidationMessage('Las contraseñas no coinciden');
                        return false;
                    }

                    if (newPass.toUpperCase() === usuarioSesion.toUpperCase()) {
                        Swal.showValidationMessage('La contraseña no puede ser igual al usuario');
                        return false;
                    }
                      // No solo números
                    if (/^\\d+$/.test(newPass)) {
                        Swal.showValidationMessage('La contraseña no puede ser solo números');
                        return false;
                    }

                    return { newPass: newPass };
                }
            }).then((result) => {
                if(result.isConfirmed) {
                    fetch('/Inicio/actualizar_password', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ password: result.value.newPass })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success){
                            Swal.fire('¡Listo!', 'Tu contraseña se actualizó correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error')
                                .then(() => location.reload());
                        }
                    })
                    .catch(err => Swal.fire('Error', 'Ocurrió un error inesperado', 'error')
                        .then(() => location.reload()));
                }
            });
            </script>
HTML;
            exit;
        }
    }

    public function actualizar_password()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevaPassword = $input['password'] ?? null;
        $usuario = $_SESSION['usuario'] ?? null;

        if (!$nuevaPassword || !$usuario) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        // Llamamos al modelo para actualizar
        $resultado = UsuariosDao::actualizarPassword($usuario, $nuevaPassword);

        echo json_encode($resultado);
    }

    // Método de debug para probar la búsqueda de documentos vía router
    public function debug_document_search()
    {
        $this->set('titulo', 'Debug Búsqueda de Documento');
        self::render('debug_document_search');
    }


}
