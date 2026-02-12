<?php
/**
 * Script de diagnóstico para Shell Segundómetro
 * 
 * Verifica toda la configuración y conectividad SSH necesaria.
 * Genera un reporte detallado con todos los problemas encontrados.
 * 
 * Uso:
 *   - Desde navegador: http://tu-servidor/ruta/backend/scripts/diagnostico_segundometro.php
 *   - Desde CLI: php diagnostico_segundometro.php
 * 
 * El reporte se guarda en: backend/storage/logs/diagnostico_segundometro_[timestamp].txt
 */

// Configuración
date_default_timezone_set('America/Mexico_City');
$RAIZ = dirname(__DIR__);
$LOG_DIR = $RAIZ . '/storage/logs';
$timestamp = date('Y-m-d_H-i-s');
$archivoReporte = $LOG_DIR . '/diagnostico_segundometro_' . $timestamp . '.txt';

// Configuración SSH (debe coincidir con SegundometroDAO.php)
$SSH_HOST = '34.173.106.81';
$SSH_USER = 'jesus';
$DIRECTORIO_REMOTO = '/home/usuariossftp/s2/mega_reporte';

// Obtener ruta de la clave SSH igual que SegundometroDAO::getSSHKey(): primero proyecto, luego config.ini
function getDiagnosticoSSHKey($raiz) {
    $defaultKey = $raiz . '/config/ssh/jesusssh4.unknown';
    // 1) Primero la ruta por defecto del proyecto
    if (@is_file($defaultKey)) {
        return $defaultKey;
    }
    // 2) Si no existe, revisar config.ini
    $configFile = $raiz . '/config/config.ini';
    if (is_file($configFile)) {
        $config = @parse_ini_file($configFile, true);
        if (is_array($config)) {
            $path = trim($config['ssh']['ssh_key'] ?? '');
            if ($path !== '' && @is_file($path)) {
                return $path;
            }
        }
    }
    return $defaultKey;
}

// Obtener comando SSH igual que SegundometroDAO::getSSHCommand()
function getDiagnosticoSSHCommand() {
    $raiz = dirname(__DIR__);
    $configFile = $raiz . '/config/config.ini';
    if (is_file($configFile)) {
        $config = @parse_ini_file($configFile, true);
        if (is_array($config)) {
            $path = trim($config['ssh']['ssh_command'] ?? '');
            if ($path !== '' && @is_file($path)) {
                return $path;
            }
        }
    }
    $output = [];
    $ret = 0;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        @exec('where.exe ssh 2>&1', $output, $ret);
    } else {
        @exec('which ssh 2>&1', $output, $ret);
    }
    if ($ret === 0 && !empty($output)) {
        return trim($output[0]);
    }
    return null;
}

$SSH_KEY = getDiagnosticoSSHKey($RAIZ);
$SSH_CMD = getDiagnosticoSSHCommand();
if ($SSH_CMD === null) {
    $SSH_CMD = 'ssh';
}

// Array para almacenar resultados
$resultados = [];
$problemas = [];
$advertencias = [];

// Función para añadir resultado
function agregarResultado($categoria, $test, $estado, $mensaje, $detalle = '') {
    global $resultados, $problemas, $advertencias;
    
    $resultado = [
        'categoria' => $categoria,
        'test' => $test,
        'estado' => $estado, // OK, ERROR, WARNING
        'mensaje' => $mensaje,
        'detalle' => $detalle,
        'timestamp' => date('H:i:s')
    ];
    
    $resultados[] = $resultado;
    
    if ($estado === 'ERROR') {
        $problemas[] = $test . ': ' . $mensaje;
    } elseif ($estado === 'WARNING') {
        $advertencias[] = $test . ': ' . $mensaje;
    }
}

// Función para ejecutar comando y capturar salida
function ejecutarComando($comando) {
    $output = [];
    $returnVar = 0;
    @exec($comando . ' 2>&1', $output, $returnVar);
    return [
        'output' => implode("\n", $output),
        'return_code' => $returnVar,
        'success' => $returnVar === 0
    ];
}

// ============================================
// INICIO DEL DIAGNÓSTICO
// ============================================

$inicio = microtime(true);
$reporte = [];
$reporte[] = "===============================================";
$reporte[] = "DIAGNÓSTICO SHELL SEGUNDÓMETRO";
$reporte[] = "===============================================";
$reporte[] = "Fecha: " . date('Y-m-d H:i:s T');
$reporte[] = "Servidor: " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'CLI');
$reporte[] = "PHP Version: " . phpversion();
$reporte[] = "SO: " . PHP_OS;
$reporte[] = "Usuario ejecutando: " . (function_exists('posix_getpwuid') && function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user());
$reporte[] = "";

// ============================================
// 1. VERIFICAR FUNCIONES PHP CRÍTICAS
// ============================================
$reporte[] = "1. FUNCIONES PHP";
$reporte[] = str_repeat("-", 47);

$funcionesCriticas = ['exec', 'shell_exec', 'system', 'passthru'];
$disabledFunctions = array_map('trim', explode(',', ini_get('disable_functions')));

foreach ($funcionesCriticas as $funcion) {
    if (in_array($funcion, $disabledFunctions)) {
        agregarResultado('PHP', "Función $funcion", 'ERROR', 'Deshabilitada en disable_functions');
        $reporte[] = "  ❌ $funcion: DESHABILITADA";
    } elseif (function_exists($funcion)) {
        agregarResultado('PHP', "Función $funcion", 'OK', 'Disponible');
        $reporte[] = "  ✅ $funcion: Disponible";
    } else {
        agregarResultado('PHP', "Función $funcion", 'ERROR', 'No existe');
        $reporte[] = "  ❌ $funcion: NO EXISTE";
    }
}

$openBasedir = ini_get('open_basedir');
if ($openBasedir) {
    agregarResultado('PHP', 'open_basedir', 'WARNING', 'Está configurado', $openBasedir);
    $reporte[] = "  ⚠️  open_basedir: " . $openBasedir;
} else {
    agregarResultado('PHP', 'open_basedir', 'OK', 'Sin restricciones');
    $reporte[] = "  ✅ open_basedir: Sin restricciones";
}

$safeMode = ini_get('safe_mode');
if ($safeMode) {
    agregarResultado('PHP', 'safe_mode', 'WARNING', 'Activado');
    $reporte[] = "  ⚠️  safe_mode: Activado";
} else {
    agregarResultado('PHP', 'safe_mode', 'OK', 'Desactivado');
    $reporte[] = "  ✅ safe_mode: Desactivado";
}

$reporte[] = "";

// ============================================
// 2. VERIFICAR COMANDO SSH
// ============================================
$reporte[] = "2. COMANDO SSH";
$reporte[] = str_repeat("-", 47);

if (function_exists('exec') && !in_array('exec', $disabledFunctions)) {
    // Buscar ssh en el sistema
    $whichSsh = ejecutarComando('which ssh');
    if ($whichSsh['success'] && !empty(trim($whichSsh['output']))) {
        agregarResultado('SSH', 'Comando ssh', 'OK', 'Encontrado en PATH', trim($whichSsh['output']));
        $reporte[] = "  ✅ ssh encontrado: " . trim($whichSsh['output']);
        
        // Obtener versión de SSH
        $versionSsh = ejecutarComando('ssh -V');
        $reporte[] = "     Versión: " . trim($versionSsh['output']);
    } else {
        agregarResultado('SSH', 'Comando ssh', 'ERROR', 'No encontrado en PATH');
        $reporte[] = "  ❌ ssh NO encontrado en PATH";
        $reporte[] = "     Verificar: comando 'which ssh' o 'where ssh'";
    }
} else {
    agregarResultado('SSH', 'Comando ssh', 'ERROR', 'No se puede verificar (exec deshabilitado)');
    $reporte[] = "  ❌ No se puede verificar (exec deshabilitado)";
}

$reporte[] = "";

// ============================================
// 3. VERIFICAR CLAVE SSH
// ============================================
$reporte[] = "3. CLAVE SSH";
$reporte[] = str_repeat("-", 47);
$reporte[] = "  Ruta: $SSH_KEY";
$reporte[] = "  (misma lógica que SegundometroDAO::getSSHKey() - primero backend/config/ssh/, luego config.ini [ssh] ssh_key)";

if (file_exists($SSH_KEY)) {
    agregarResultado('SSH', 'Archivo clave', 'OK', 'Existe', $SSH_KEY);
    $reporte[] = "  ✅ Archivo existe";
    
    // Verificar permisos
    $permisos = substr(sprintf('%o', fileperms($SSH_KEY)), -4);
    $reporte[] = "     Permisos: $permisos";
    
    if ($permisos === '0600' || $permisos === '0400') {
        agregarResultado('SSH', 'Permisos clave', 'OK', 'Correctos', $permisos);
        $reporte[] = "     ✅ Permisos correctos para SSH";
    } else {
        agregarResultado('SSH', 'Permisos clave', 'WARNING', 'Pueden causar problemas', $permisos);
        $reporte[] = "     ⚠️  Permisos pueden causar problemas";
        $reporte[] = "        Recomendado: chmod 600 $SSH_KEY";
    }
    
    // Verificar si es legible
    if (is_readable($SSH_KEY)) {
        agregarResultado('SSH', 'Lectura clave', 'OK', 'Usuario puede leer la clave');
        $reporte[] = "     ✅ Usuario actual puede leer la clave";
    } else {
        agregarResultado('SSH', 'Lectura clave', 'ERROR', 'Usuario NO puede leer la clave');
        $reporte[] = "     ❌ Usuario actual NO puede leer la clave";
    }
    
    // Verificar tamaño
    $tamano = filesize($SSH_KEY);
    $reporte[] = "     Tamaño: " . number_format($tamano) . " bytes";
    if ($tamano < 100) {
        agregarResultado('SSH', 'Tamaño clave', 'WARNING', 'Archivo muy pequeño, puede estar vacío o corrupto');
        $reporte[] = "     ⚠️  Archivo muy pequeño";
    } else {
        agregarResultado('SSH', 'Tamaño clave', 'OK', 'Tamaño normal');
    }
} else {
    agregarResultado('SSH', 'Archivo clave', 'ERROR', 'No existe', $SSH_KEY);
    $reporte[] = "  ❌ Archivo NO existe";
    $reporte[] = "     Verificar que la clave esté en: $SSH_KEY";
}

$reporte[] = "";

// ============================================
// 4. VERIFICAR CONECTIVIDAD SSH
// ============================================
$reporte[] = "4. CONECTIVIDAD SSH";
$reporte[] = str_repeat("-", 47);
$reporte[] = "  Host: $SSH_HOST";
$reporte[] = "  Usuario: $SSH_USER";

if (file_exists($SSH_KEY) && function_exists('exec') && !in_array('exec', $disabledFunctions)) {
    // Test 1: Ping al host
    $reporte[] = "";
    $reporte[] = "  [Test 1] Ping al servidor...";
    $pingCmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "ping -n 1 $SSH_HOST" : "ping -c 1 -W 5 $SSH_HOST";
    $pingResult = ejecutarComando($pingCmd);
    if ($pingResult['success']) {
        agregarResultado('Conectividad', 'Ping', 'OK', 'Servidor responde');
        $reporte[] = "  ✅ Servidor responde a ping";
    } else {
        agregarResultado('Conectividad', 'Ping', 'WARNING', 'Servidor no responde a ping (puede ser normal si firewall bloquea ICMP)');
        $reporte[] = "  ⚠️  Servidor no responde a ping (puede estar bloqueado por firewall)";
    }
    
    // Test 2: Conexión SSH básica (usa la misma clave y comando que el DAO)
    $reporte[] = "";
    $reporte[] = "  [Test 2] Conexión SSH básica (timeout 10s)...";
    $sshKeyEscaped = escapeshellarg($SSH_KEY);
    $sshExeEscaped = escapeshellarg($SSH_CMD);
    $knownHostsFile = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'NUL' : '/dev/null';
    $sshTest = sprintf(
        '%s -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=%s -o ConnectTimeout=10 -o BatchMode=yes %s@%s "echo OK" 2>&1',
        $sshExeEscaped,
        $sshKeyEscaped,
        $knownHostsFile,
        $SSH_USER,
        $SSH_HOST
    );
    
    $sshResult = ejecutarComando($sshTest);
    if ($sshResult['success'] && strpos($sshResult['output'], 'OK') !== false) {
        agregarResultado('Conectividad', 'Conexión SSH', 'OK', 'Conexión exitosa');
        $reporte[] = "  ✅ Conexión SSH exitosa";
    } else {
        agregarResultado('Conectividad', 'Conexión SSH', 'ERROR', 'Fallo la conexión', $sshResult['output']);
        $reporte[] = "  ❌ Fallo la conexión SSH";
        $reporte[] = "     Salida: " . substr($sshResult['output'], 0, 200);
        if (strpos($sshResult['output'], 'Permission denied') !== false) {
            $reporte[] = "     ⚠️  Error de permisos - verificar clave SSH";
        }
        if (strpos($sshResult['output'], 'Connection timed out') !== false) {
            $reporte[] = "     ⚠️  Timeout - verificar firewall y conectividad";
        }
    }
    
    // Test 3: Verificar directorio remoto
    if ($sshResult['success']) {
        $reporte[] = "";
        $reporte[] = "  [Test 3] Verificar directorio remoto...";
        $dirTest = sprintf(
            '%s -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=%s -o ConnectTimeout=10 %s@%s "test -d %s && echo OK || echo FAIL" 2>&1',
            $sshExeEscaped,
            $sshKeyEscaped,
            $knownHostsFile,
            $SSH_USER,
            $SSH_HOST,
            escapeshellarg($DIRECTORIO_REMOTO)
        );
        
        $dirResult = ejecutarComando($dirTest);
        if ($dirResult['success'] && strpos($dirResult['output'], 'OK') !== false) {
            agregarResultado('SSH', 'Directorio remoto', 'OK', 'Existe y es accesible', $DIRECTORIO_REMOTO);
            $reporte[] = "  ✅ Directorio existe: $DIRECTORIO_REMOTO";
        } else {
            agregarResultado('SSH', 'Directorio remoto', 'ERROR', 'No existe o no es accesible', $DIRECTORIO_REMOTO);
            $reporte[] = "  ❌ Directorio NO accesible: $DIRECTORIO_REMOTO";
        }
        
        // Test 4: Listar archivos
        $reporte[] = "";
        $reporte[] = "  [Test 4] Listar archivos mega_rpt_*.csv.zip...";
        $listTest = sprintf(
            '%s -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=%s -o ConnectTimeout=10 %s@%s "cd %s && ls -l mega_rpt_*.csv.zip 2>/dev/null | head -n 5" 2>&1',
            $sshExeEscaped,
            $sshKeyEscaped,
            $knownHostsFile,
            $SSH_USER,
            $SSH_HOST,
            escapeshellarg($DIRECTORIO_REMOTO)
        );
        
        $listResult = ejecutarComando($listTest);
        if ($listResult['success'] && !empty(trim($listResult['output']))) {
            $lineas = explode("\n", trim($listResult['output']));
            $numArchivos = count($lineas);
            agregarResultado('SSH', 'Listar archivos', 'OK', "Se encontraron archivos ($numArchivos)", implode("\n", array_slice($lineas, 0, 3)));
            $reporte[] = "  ✅ Se encontraron archivos ($numArchivos listados):";
            foreach (array_slice($lineas, 0, 3) as $linea) {
                $reporte[] = "     " . $linea;
            }
            if ($numArchivos > 3) {
                $reporte[] = "     ... (mostrando solo 3 de $numArchivos)";
            }
        } else {
            agregarResultado('SSH', 'Listar archivos', 'WARNING', 'No se encontraron archivos o error al listar', $listResult['output']);
            $reporte[] = "  ⚠️  No se encontraron archivos o error al listar";
            if (!empty($listResult['output'])) {
                $reporte[] = "     Salida: " . substr($listResult['output'], 0, 200);
            }
        }
    }
} else {
    agregarResultado('Conectividad', 'Tests SSH', 'ERROR', 'No se pueden ejecutar (clave no existe o exec deshabilitado)');
    $reporte[] = "  ❌ No se pueden ejecutar tests (verificar puntos anteriores)";
}

$reporte[] = "";

// ============================================
// 5. PRUEBAS ESPECÍFICAS DE DETECCIÓN SSH
// ============================================
$reporte[] = "5. PRUEBAS ESPECÍFICAS (getSSHCommand)";
$reporte[] = str_repeat("-", 47);

// Cargar SegundometroDAO para probar getSSHCommand()
$segDAO = $RAIZ . '/models/SegundometroDAO.php';
if (file_exists($segDAO)) {
    require_once $RAIZ . '/core/Model.php';
    require_once $segDAO;
    
    $reporte[] = "";
    $reporte[] = "  [Test A] Lectura de config.ini desde PHP...";
    $configFile = $RAIZ . '/config/config.ini';
    if (is_file($configFile)) {
        $config = @parse_ini_file($configFile, true);
        if (is_array($config) && isset($config['ssh']['ssh_command'])) {
            $pathConfig = trim($config['ssh']['ssh_command']);
            agregarResultado('Config', 'Leer config.ini [ssh]', 'OK', 'Se lee correctamente', $pathConfig);
            $reporte[] = "  ✅ config.ini [ssh] ssh_command: $pathConfig";
            
            if (@is_file($pathConfig)) {
                agregarResultado('Config', 'Ruta SSH en config', 'OK', 'Archivo existe');
                $reporte[] = "     ✅ El archivo existe en esa ruta";
            } else {
                agregarResultado('Config', 'Ruta SSH en config', 'ERROR', 'Archivo NO existe en esa ruta');
                $reporte[] = "     ❌ El archivo NO existe en esa ruta";
            }
        } else {
            agregarResultado('Config', 'Leer config.ini [ssh]', 'WARNING', 'No está configurado o no se pudo leer');
            $reporte[] = "  ⚠️  [ssh] ssh_command no está en config.ini";
        }
    } else {
        agregarResultado('Config', 'config.ini', 'WARNING', 'No existe');
        $reporte[] = "  ⚠️  config.ini no existe";
    }
    
    $reporte[] = "";
    $reporte[] = "  [Test B] Ejecutar getSSHCommand() del DAO...";
    try {
        // Usar reflection para acceder al método privado
        $reflection = new \ReflectionClass('Models\SegundometroDAO');
        $method = $reflection->getMethod('getSSHCommand');
        $method->setAccessible(true);
        $sshPath = $method->invoke(null);
        
        if ($sshPath !== null && $sshPath !== '') {
            agregarResultado('DAO', 'getSSHCommand()', 'OK', 'Detectó ruta de SSH', $sshPath);
            $reporte[] = "  ✅ getSSHCommand() devuelve: $sshPath";
            
            if (@is_file($sshPath)) {
                $reporte[] = "     ✅ El archivo existe";
            } else {
                agregarResultado('DAO', 'SSH executable', 'ERROR', 'La ruta devuelta no existe como archivo');
                $reporte[] = "     ❌ Pero el archivo NO existe";
            }
        } else {
            agregarResultado('DAO', 'getSSHCommand()', 'ERROR', 'NO detectó ruta de SSH (devolvió null)');
            $reporte[] = "  ❌ getSSHCommand() devolvió NULL";
            $reporte[] = "     Esto significa que ni config.ini ni detección automática funcionaron";
        }
    } catch (\Exception $e) {
        agregarResultado('DAO', 'getSSHCommand()', 'ERROR', 'Error al ejecutar', $e->getMessage());
        $reporte[] = "  ❌ Error al ejecutar getSSHCommand(): " . $e->getMessage();
    }
} else {
    agregarResultado('DAO', 'SegundometroDAO.php', 'ERROR', 'No se pudo cargar');
    $reporte[] = "  ❌ No se pudo cargar SegundometroDAO.php";
}

$reporte[] = "";

// ============================================
// 6. VARIABLES DE ENTORNO Y CONTEXTO
// ============================================
$reporte[] = "6. VARIABLES DE ENTORNO Y CONTEXTO";
$reporte[] = str_repeat("-", 47);

$sapi = php_sapi_name();
$reporte[] = "  SAPI: $sapi";
if ($sapi === 'cli') {
    $reporte[] = "     ℹ️  Modo CLI (línea de comandos)";
} elseif (in_array($sapi, ['apache', 'apache2handler', 'fpm-fcgi', 'cgi-fcgi'])) {
    $reporte[] = "     ℹ️  Modo Web ($sapi)";
}

$path = getenv('PATH');
if ($path) {
    $reporte[] = "";
    $reporte[] = "  Variable PATH:";
    $pathParts = explode(PATH_SEPARATOR, $path);
    $numPaths = count($pathParts);
    $reporte[] = "     Total de rutas: $numPaths";
    $reporte[] = "     Primeras 5 rutas:";
    foreach (array_slice($pathParts, 0, 5) as $i => $p) {
        $reporte[] = "       " . ($i + 1) . ". " . $p;
    }
    
    // Buscar OpenSSH en PATH
    $foundOpenSSH = false;
    foreach ($pathParts as $p) {
        if (stripos($p, 'OpenSSH') !== false || stripos($p, 'ssh') !== false) {
            $reporte[] = "     ✅ Encontrado directorio SSH en PATH: $p";
            $foundOpenSSH = true;
        }
    }
    if (!$foundOpenSSH) {
        agregarResultado('Entorno', 'PATH con SSH', 'WARNING', 'No hay directorio OpenSSH/ssh en PATH');
        $reporte[] = "     ⚠️  No se encontró directorio OpenSSH/ssh en PATH";
    }
} else {
    agregarResultado('Entorno', 'Variable PATH', 'WARNING', 'No se pudo leer');
    $reporte[] = "  ⚠️  No se pudo leer variable PATH";
}

$reporte[] = "";
$reporte[] = "  Otras variables relevantes:";
$relevantVars = ['HOME', 'USERPROFILE', 'SYSTEMROOT', 'WINDIR', 'TEMP', 'TMP'];
foreach ($relevantVars as $var) {
    $val = getenv($var);
    if ($val) {
        $reporte[] = "     $var: $val";
    }
}

$reporte[] = "";

// ============================================
// 7. TEST DE EJECUCIÓN REAL SSH DESDE PHP
// ============================================
$reporte[] = "7. TEST DE EJECUCIÓN SSH DESDE CONTEXTO PHP";
$reporte[] = str_repeat("-", 47);

$reporte[] = "";
$reporte[] = "  [Test 1] Ejecutar 'where.exe ssh' (Windows)...";
$whereResult = ejecutarComando('where.exe ssh');
if ($whereResult['success'] && !empty(trim($whereResult['output']))) {
    $paths = explode("\n", trim($whereResult['output']));
    agregarResultado('Test SSH', 'where.exe ssh', 'OK', 'Encontró ' . count($paths) . ' ruta(s)', $whereResult['output']);
    $reporte[] = "  ✅ Encontró " . count($paths) . " ruta(s):";
    foreach ($paths as $p) {
        $p = trim($p);
        if ($p) $reporte[] = "     • $p";
    }
} else {
    agregarResultado('Test SSH', 'where.exe ssh', 'ERROR', 'No encontró ssh.exe');
    $reporte[] = "  ❌ No encontró ssh.exe";
    if (!empty($whereResult['output'])) {
        $reporte[] = "     Salida: " . $whereResult['output'];
    }
}

$reporte[] = "";
$reporte[] = "  [Test 2] Intentar ejecutar SSH directamente...";
$sshTestDirect = ejecutarComando('ssh -V');
if ($sshTestDirect['success'] || strpos($sshTestDirect['output'], 'OpenSSH') !== false) {
    agregarResultado('Test SSH', 'ssh -V', 'OK', 'SSH ejecutable desde PHP');
    $reporte[] = "  ✅ SSH es ejecutable desde este contexto PHP";
    $reporte[] = "     " . trim($sshTestDirect['output']);
} else {
    agregarResultado('Test SSH', 'ssh -V', 'ERROR', 'SSH NO ejecutable desde PHP');
    $reporte[] = "  ❌ SSH NO es ejecutable desde este contexto PHP";
    $reporte[] = "     Salida: " . trim($sshTestDirect['output']);
}

$reporte[] = "";
$reporte[] = "  [Test 3] Ruta completa C:\\Windows\\System32\\OpenSSH\\ssh.exe...";
$sshFullPath = 'C:\Windows\System32\OpenSSH\ssh.exe';
if (file_exists($sshFullPath)) {
    $reporte[] = "  ✅ Archivo existe: $sshFullPath";
    
    $sshTestFull = ejecutarComando('"' . $sshFullPath . '" -V');
    if ($sshTestFull['success'] || strpos($sshTestFull['output'], 'OpenSSH') !== false) {
        agregarResultado('Test SSH', 'Ruta completa SSH', 'OK', 'Ejecutable con ruta completa');
        $reporte[] = "  ✅ Ejecutable con ruta completa";
        $reporte[] = "     " . trim($sshTestFull['output']);
    } else {
        agregarResultado('Test SSH', 'Ruta completa SSH', 'ERROR', 'Existe pero no ejecutable');
        $reporte[] = "  ❌ Existe pero no se puede ejecutar";
        $reporte[] = "     Salida: " . trim($sshTestFull['output']);
    }
} else {
    agregarResultado('Test SSH', 'SSH en ruta estándar', 'WARNING', 'No existe en ruta estándar de Windows');
    $reporte[] = "  ⚠️  No existe en: $sshFullPath";
}

$reporte[] = "";

// ============================================
// 8. VERIFICAR DIRECTORIOS DE LA APLICACIÓN
// ============================================
$reporte[] = "8. DIRECTORIOS DE LA APLICACIÓN";
$reporte[] = str_repeat("-", 47);

$directorios = [
    'Raíz backend' => $RAIZ,
    'Config' => $RAIZ . '/config',
    'Config SSH' => $RAIZ . '/config/ssh',
    'Storage' => $RAIZ . '/storage',
    'Storage logs' => $LOG_DIR,
    'Models' => $RAIZ . '/models',
    'Controllers' => $RAIZ . '/controllers'
];

foreach ($directorios as $nombre => $ruta) {
    if (is_dir($ruta)) {
        if (is_writable($ruta)) {
            agregarResultado('Directorios', $nombre, 'OK', 'Existe y es escribible', $ruta);
            $reporte[] = "  ✅ $nombre: Existe y es escribible";
        } else {
            agregarResultado('Directorios', $nombre, 'WARNING', 'Existe pero NO es escribible', $ruta);
            $reporte[] = "  ⚠️  $nombre: Existe pero NO es escribible";
        }
    } else {
        agregarResultado('Directorios', $nombre, 'ERROR', 'No existe', $ruta);
        $reporte[] = "  ❌ $nombre: NO existe ($ruta)";
    }
}

$reporte[] = "";

// ============================================
// 9. VERIFICAR CLASES PHP NECESARIAS
// ============================================
$reporte[] = "9. CLASES PHP";
$reporte[] = str_repeat("-", 47);

$archivoDAO = $RAIZ . '/models/SegundometroDAO.php';
if (file_exists($archivoDAO)) {
    agregarResultado('Clases', 'SegundometroDAO.php', 'OK', 'Existe', $archivoDAO);
    $reporte[] = "  ✅ SegundometroDAO.php existe";
    
    // Leer configuración del archivo
    $contenido = file_get_contents($archivoDAO);
    if (preg_match('/private static \$SSH_HOST = [\'"](.+?)[\'"]/', $contenido, $m)) {
        $hostEnArchivo = $m[1];
        if ($hostEnArchivo === $SSH_HOST) {
            $reporte[] = "     ✅ SSH_HOST coincide: $hostEnArchivo";
        } else {
            agregarResultado('Clases', 'Configuración SSH_HOST', 'WARNING', "No coincide con este script (archivo: $hostEnArchivo, script: $SSH_HOST)");
            $reporte[] = "     ⚠️  SSH_HOST en archivo: $hostEnArchivo";
            $reporte[] = "        (este script usa: $SSH_HOST)";
        }
    }
    
    if (preg_match('/private static \$SSH_KEY = (.+?);/', $contenido, $m)) {
        $reporte[] = "     Configuración SSH_KEY (por defecto en DAO): " . trim($m[1]);
    }
    $reporte[] = "     Clave que usa este diagnóstico (getSSHKey): $SSH_KEY";
    $reporte[] = "     Comando SSH que usa este diagnóstico: $SSH_CMD";
} else {
    agregarResultado('Clases', 'SegundometroDAO.php', 'ERROR', 'No existe', $archivoDAO);
    $reporte[] = "  ❌ SegundometroDAO.php NO existe";
}

$reporte[] = "";

// ============================================
// RESUMEN
// ============================================
$duracion = round(microtime(true) - $inicio, 2);

$reporte[] = "===============================================";
$reporte[] = "RESUMEN";
$reporte[] = "===============================================";
$reporte[] = "Total de tests ejecutados: " . count($resultados);
$reporte[] = "Problemas críticos: " . count($problemas);
$reporte[] = "Advertencias: " . count($advertencias);
$reporte[] = "Duración: {$duracion}s";
$reporte[] = "";

if (count($problemas) > 0) {
    $reporte[] = "❌ PROBLEMAS CRÍTICOS:";
    foreach ($problemas as $i => $problema) {
        $reporte[] = "  " . ($i + 1) . ". $problema";
    }
    $reporte[] = "";
}

if (count($advertencias) > 0) {
    $reporte[] = "⚠️  ADVERTENCIAS:";
    foreach ($advertencias as $i => $advertencia) {
        $reporte[] = "  " . ($i + 1) . ". $advertencia";
    }
    $reporte[] = "";
}

if (count($problemas) === 0 && count($advertencias) === 0) {
    $reporte[] = "✅ TODO CORRECTO - No se encontraron problemas";
    $reporte[] = "";
}

$reporte[] = "===============================================";
$reporte[] = "Reporte generado: " . date('Y-m-d H:i:s T');
$reporte[] = "===============================================";

// ============================================
// GUARDAR REPORTE
// ============================================
$reporteTexto = implode("\n", $reporte);

if (!is_dir($LOG_DIR)) {
    @mkdir($LOG_DIR, 0755, true);
}

$guardado = @file_put_contents($archivoReporte, $reporteTexto);

// ============================================
// MOSTRAR REPORTE
// ============================================
if (php_sapi_name() === 'cli') {
    // Modo CLI
    echo $reporteTexto . "\n";
    if ($guardado !== false) {
        echo "\n✅ Reporte guardado en: $archivoReporte\n";
    } else {
        echo "\n❌ Error al guardar reporte en: $archivoReporte\n";
    }
} else {
    // Modo navegador
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Diagnóstico Shell Segundómetro</title>
        <style>
            body {
                font-family: 'Courier New', monospace;
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 20px;
                margin: 0;
            }
            .container {
                max-width: 1200px;
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
                margin-top: 20px;
            }
            .btn:hover {
                background: #1177bb;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <pre><?php
            // Colorear salida
            $output = $reporteTexto;
            $output = str_replace('✅', '<span class="ok">✅</span>', $output);
            $output = str_replace('❌', '<span class="error">❌</span>', $output);
            $output = str_replace('⚠️', '<span class="warning">⚠️</span>', $output);
            echo $output;
            ?></pre>
            
            <?php if ($guardado !== false): ?>
                <p class="ok">✅ Reporte guardado en: <?php echo htmlspecialchars($archivoReporte); ?></p>
                <a href="?descargar=1" class="btn">📥 Descargar reporte</a>
            <?php else: ?>
                <p class="error">❌ Error al guardar reporte</p>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

// Opción para descargar el reporte
if (isset($_GET['descargar']) && file_exists($archivoReporte)) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . basename($archivoReporte) . '"');
    readfile($archivoReporte);
    exit;
}
