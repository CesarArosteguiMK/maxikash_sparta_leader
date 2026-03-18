<?php

namespace Controllers;

use Core\Controller;
use Core\DatabaseGeo;
use Models\Usuarios as UsuariosDao;

class Inicio extends Controller
{
    public function index()
    {
        $this->validarActualizacionPassword();
        require_once dirname(__DIR__) . '/config/menu_accesos_inicio.php';
        $accesosRapidos = getAccesosRapidosDesdeModulos();
        $this->set('accesosRapidos', $accesosRapidos);
        // Botones de diagnóstico (Segundómetro y BD alternas): solo usuario id 1 y si config lo permite
        $configInicio = (is_file($cfg = dirname(__DIR__) . '/config/config.ini') && is_array($c = @parse_ini_file($cfg, true))) ? ($c['inicio'] ?? []) : [];
        $mostrarDiagnosticoAdmin = (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === 1)
            && !empty($configInicio['mostrar_botones_diagnostico']);
        $this->set('mostrarDiagnosticoAdmin', $mostrarDiagnosticoAdmin);

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

    private function validarActualizacionPassword()
    {
        // Suponiendo que en la sesión se guarda el nombre de usuario
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
