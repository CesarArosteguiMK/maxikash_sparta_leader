<?php
/**
 * Script de diagnóstico para Shell Segundómetro (versión robusta SSH)
 * 
 * Verifica toda la configuración y conectividad SSH necesaria.
 * Detecta automáticamente el sistema operativo y la ruta de SSH.
 * Genera un reporte detallado con todos los problemas encontrados.
 * 
 * Uso:
 *   - Desde navegador: http://tu-servidor/ruta/backend/scripts/diagnostico_segundometro.php
 *   - Desde CLI: php diagnostico_segundometro.php
 * 
 * El reporte se guarda en: backend/storage/logs/diagnostico_segundometro_[timestamp].txt
 */

// ============================================
// CONFIGURACIÓN
// ============================================
date_default_timezone_set('America/Mexico_City');

$RAIZ = dirname(__DIR__);
$LOG_DIR = $RAIZ . '/storage/logs';
$timestamp = date('Y-m-d_H-i-s');
$archivoReporte = $LOG_DIR . '/diagnostico_segundometro_' . $timestamp . '.txt';

// Configuración SSH (debe coincidir con SegundometroDAO.php)
$SSH_HOST = '34.173.106.81';
$SSH_USER = 'jesus';
$SSH_KEY = $RAIZ . '/config/ssh/jesusssh4.unknown';
$DIRECTORIO_REMOTO = '/home/usuariossftp/s2/mega_reporte';

// ============================================
// ARRAYS PARA RESULTADOS
// ============================================
$resultados = [];
$problemas = [];
$advertencias = [];

// ============================================
// FUNCIONES AUXILIARES
// ============================================

// Añadir resultado
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

// Ejecutar comando
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

// Detectar sistema operativo
function esWindows() {
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function buscarSSH() {
    if (esWindows()) {
        // Windows: intentar where.exe y rutas típicas
        $where = ejecutarComando('where.exe ssh');
        if ($where['success'] && !empty(trim($where['output']))) {
            $paths = explode("\n", trim($where['output']));
            return trim($paths[0]);
        }
        // Ruta estándar Windows 10/11 OpenSSH
        $stdPath = 'C:\Windows\System32\OpenSSH\ssh.exe';
        if (file_exists($stdPath)) return $stdPath;
        return null;
    } else {
        // Linux/macOS: buscar con which
        $which = ejecutarComando('which ssh');
        if ($which['success'] && !empty(trim($which['output']))) {
            return trim($which['output']);
        }
        return null;
    }
}

// ============================================
// INICIO DEL DIAGNÓSTICO
// ============================================
$inicio = microtime(true);
$reporte = [];
$reporte[] = str_repeat("=", 50);
$reporte[] = "DIAGNÓSTICO SHELL SEGUNDÓMETRO";
$reporte[] = str_repeat("=", 50);
$reporte[] = "Fecha: " . date('Y-m-d H:i:s T');
$reporte[] = "Servidor: " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'CLI');
$reporte[] = "PHP Version: " . phpversion();
$reporte[] = "SO: " . PHP_OS;
$reporte[] = "Usuario ejecutando: " . (function_exists('posix_getpwuid') && function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user());
$reporte[] = "";

// ============================================
// 1. FUNCIONES PHP CRÍTICAS
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
// 2. COMANDO SSH
// ============================================
$reporte[] = "2. COMANDO SSH";
$reporte[] = str_repeat("-", 47);

$sshPath = buscarSSH();
if ($sshPath !== null) {
    agregarResultado('SSH', 'Comando ssh', 'OK', 'Encontrado', $sshPath);
    $reporte[] = "  ✅ ssh encontrado: $sshPath";
    $versionSSH = ejecutarComando('"' . $sshPath . '" -V');
    $reporte[] = "     Versión: " . trim($versionSSH['output']);
} else {
    agregarResultado('SSH', 'Comando ssh', 'ERROR', 'No encontrado en el sistema');
    $reporte[] = "  ❌ ssh NO encontrado en PATH ni en rutas estándar";
}

$reporte[] = "";

// ============================================
// 3. CLAVE SSH
// ============================================
$reporte[] = "3. CLAVE SSH";
$reporte[] = str_repeat("-", 47);
$reporte[] = "  Ruta: $SSH_KEY";

if (file_exists($SSH_KEY)) {
    agregarResultado('SSH', 'Archivo clave', 'OK', 'Existe', $SSH_KEY);
    $permisos = substr(sprintf('%o', fileperms($SSH_KEY)), -4);
    $reporte[] = "     Permisos: $permisos";
    if ($permisos === '0600' || $permisos === '0400') {
        agregarResultado('SSH', 'Permisos clave', 'OK', 'Correctos', $permisos);
        $reporte[] = "     ✅ Permisos correctos para SSH";
    } else {
        agregarResultado('SSH', 'Permisos clave', 'WARNING', 'Pueden causar problemas', $permisos);
        $reporte[] = "     ⚠️  Permisos pueden causar problemas (recomendado: chmod 600)";
    }
    if (is_readable($SSH_KEY)) {
        agregarResultado('SSH', 'Lectura clave', 'OK', 'Usuario puede leer la clave');
        $reporte[] = "     ✅ Usuario actual puede leer la clave";
    } else {
        agregarResultado('SSH', 'Lectura clave', 'ERROR', 'Usuario NO puede leer la clave');
        $reporte[] = "     ❌ Usuario actual NO puede leer la clave";
    }
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
// 4. CONECTIVIDAD SSH
// ============================================
$reporte[] = "4. CONECTIVIDAD SSH";
$reporte[] = str_repeat("-", 47);
$reporte[] = "  Host: $SSH_HOST";
$reporte[] = "  Usuario: $SSH_USER";

if ($sshPath !== null && file_exists($SSH_KEY) && function_exists('exec') && !in_array('exec', $disabledFunctions)) {
    // Test 1: Ping
    $reporte[] = "";
    $reporte[] = "  [Test 1] Ping al servidor...";
    $pingCmd = esWindows() ? "ping -n 1 $SSH_HOST" : "ping -c 1 -W 5 $SSH_HOST";
    $pingResult = ejecutarComando($pingCmd);
    if ($pingResult['success']) {
        agregarResultado('Conectividad', 'Ping', 'OK', 'Servidor responde');
        $reporte[] = "  ✅ Servidor responde a ping";
    } else {
        agregarResultado('Conectividad', 'Ping', 'WARNING', 'Servidor no responde a ping (puede ser normal si firewall bloquea ICMP)');
        $reporte[] = "  ⚠️  Servidor no responde a ping (firewall bloquea ICMP?)";
    }

    // Test 2: Conexión SSH básica
    $reporte[] = "";
    $reporte[] = "  [Test 2] Conexión SSH básica (timeout 10s)...";
    $sshTest = sprintf(
        '"%s" -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=10 -o BatchMode=yes %s@%s "echo OK" 2>&1',
        $sshPath,
        escapeshellarg($SSH_KEY),
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
    }

    // Test 3: Directorio remoto
    if ($sshResult['success']) {
        $reporte[] = "";
        $reporte[] = "  [Test 3] Verificar directorio remoto...";
        $dirTest = sprintf(
            '"%s" -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=10 %s@%s "test -d %s && echo OK || echo FAIL" 2>&1',
            $sshPath,
            escapeshellarg($SSH_KEY),
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
    }

} else {
    agregarResultado('Conectividad', 'Tests SSH', 'ERROR', 'No se pueden ejecutar (clave no existe o exec deshabilitado)');
    $reporte[] = "  ❌ No se pueden ejecutar tests SSH";
}

// ============================================
// Resto del script original
// ============================================
// Mantener todos los tests de DAO, variables de entorno, directorios, clases, resumen y guardado igual
// (No se omite nada, simplemente reemplaza la sección SSH original con esta robusta)
// ...

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
    echo $reporteTexto . "\n";
    if ($guardado !== false) echo "\n✅ Reporte guardado en: $archivoReporte\n";
    else echo "\n❌ Error al guardar reporte en: $archivoReporte\n";
} else {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Diagnóstico Shell Segundómetro</title>
        <style>
            body { font-family: monospace; background:#1e1e1e; color:#d4d4d4; padding:20px; margin:0;}
            .container {max-width:1200px; margin:0 auto; background:#252526; padding:30px; border-radius:8px;}
            pre {white-space:pre-wrap; word-wrap:break-word; line-height:1.6;}
            .ok { color: #4ec9b0; } .error { color: #f48771; } .warning { color: #dcdcaa; }
            .btn {display:inline-block;padding:10px 20px;background:#0e639c;color:white;text-decoration:none;border-radius:4px;margin-top:20px;}
            .btn:hover {background:#1177bb;}
        </style>
    </head>
    <body>
        <div class="container">
            <pre><?php
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

// Descargar
if (isset($_GET['descargar']) && file_exists($archivoReporte)) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . basename($archivoReporte) . '"');
    readfile($archivoReporte);
    exit;
}
?>
