<?php
session_start();

// Conectar a la base de datos
define('RAIZ', dirname(__DIR__) . '/backend');
require_once RAIZ . '/config/config.php';
require_once RAIZ . '/core/Database.php';

use Core\Database;

echo "<style>
    body { font-family: 'Courier New', monospace; padding: 20px; background: #f5f5f5; }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; }
    pre { background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
</style>";

echo "<h1>🔍 DEBUG - Módulos y Sesión</h1>";

// 1. Verificar sesión
echo "<h2>1️⃣ SESIÓN ACTUAL</h2>";
echo "<pre>";
if (isset($_SESSION['usuario_id'])) {
    echo "<span class='success'>✅ Usuario ID: " . $_SESSION['usuario_id'] . "</span>\n";
    echo "Nombre: " . ($_SESSION['usuario_nombre'] ?? 'N/A') . "\n";
    echo "Usuario: " . ($_SESSION['usuario'] ?? 'N/A') . "\n\n";
    
    if (isset($_SESSION['modulos'])) {
        echo "<span class='success'>✅ Módulos en sesión: " . count($_SESSION['modulos']) . "</span>\n";
        echo "Lista: " . implode(', ', $_SESSION['modulos']) . "\n\n";
        
        if (in_array(20, $_SESSION['modulos'])) {
            echo "<span class='success'>✅✅✅ MÓDULO 20 ESTÁ EN LA SESIÓN</span>\n";
        } else {
            echo "<span class='error'>❌ MÓDULO 20 NO ESTÁ EN LA SESIÓN</span>\n";
        }
    } else {
        echo "<span class='error'>❌ No hay módulos en sesión</span>\n";
    }
} else {
    echo "<span class='error'>❌ No hay usuario en sesión</span>\n";
}
echo "</pre>";

// 2. Verificar módulo en BD
echo "<h2>2️⃣ MÓDULO EN BASE DE DATOS</h2>";
echo "<pre>";
try {
    $db = new Database();
    $modulo = $db->queryOne("SELECT * FROM modulos_web WHERE id = 20");
    
    if ($modulo) {
        echo "<span class='success'>✅ Módulo 20 existe en BD</span>\n";
        echo "Nombre: {$modulo['nombre']}\n";
        echo "Pestaña: {$modulo['pestana']}\n";
        echo "Activo: " . ($modulo['activo'] ? 'Sí' : 'No') . "\n";
    } else {
        echo "<span class='error'>❌ Módulo 20 NO existe en BD</span>\n";
        echo "<span class='warning'>⚠️ Necesitas ejecutar:</span>\n";
        echo "INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo) \n";
        echo "VALUES (20, 'Asignación de Créditos', 'Despachos', 'Gestión de asignación de créditos a despachos de cobranza', 1);\n";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
}
echo "</pre>";

// 3. Verificar asignación al usuario
if (isset($_SESSION['usuario_id'])) {
    echo "<h2>3️⃣ ASIGNACIÓN DEL MÓDULO AL USUARIO</h2>";
    echo "<pre>";
    try {
        $db = new Database();
        $asignacion = $db->queryOne(
            "SELECT * FROM asigna_modulo_web WHERE usuario_id = :uid AND modulo_web_id = 20",
            ['uid' => $_SESSION['usuario_id']]
        );
        
        if ($asignacion) {
            echo "<span class='success'>✅ Módulo 20 está asignado al usuario {$_SESSION['usuario_id']}</span>\n";
        } else {
            echo "<span class='error'>❌ Módulo 20 NO está asignado al usuario {$_SESSION['usuario_id']}</span>\n";
            echo "<span class='warning'>⚠️ Necesitas ejecutar:</span>\n";
            echo "INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id) \n";
            echo "VALUES ({$_SESSION['usuario_id']}, 20);\n";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
    }
    echo "</pre>";
}

// 4. Verificar todos los módulos del usuario en BD
if (isset($_SESSION['usuario_id'])) {
    echo "<h2>4️⃣ TODOS LOS MÓDULOS DEL USUARIO EN BD</h2>";
    echo "<pre>";
    try {
        $db = new Database();
        $modulos = $db->queryAll(
            "SELECT m.id, m.nombre, m.pestana 
             FROM asigna_modulo_web a 
             JOIN modulos_web m ON a.modulo_web_id = m.id 
             WHERE a.usuario_id = :uid 
             ORDER BY m.id",
            ['uid' => $_SESSION['usuario_id']]
        );
        
        if ($modulos) {
            echo "<span class='info'>Módulos asignados en BD:</span>\n";
            foreach ($modulos as $m) {
                $icon = $m['id'] == 20 ? '👉' : '  ';
                echo "$icon ID: {$m['id']} - {$m['nombre']} ({$m['pestana']})\n";
            }
        } else {
            echo "<span class='warning'>No hay módulos asignados en BD</span>\n";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
    }
    echo "</pre>";
}

// 5. SOLUCIÓN
echo "<h2>🔧 SOLUCIÓN</h2>";
echo "<pre>";
if (!isset($_SESSION['modulos']) || !in_array(20, $_SESSION['modulos'])) {
    echo "<span class='warning'>⚠️ Para que aparezca la sección 'Despachos':</span>\n\n";
    echo "1. Asegúrate de que las queries SQL de arriba estén ejecutadas\n";
    echo "2. <strong>CIERRA COMPLETAMENTE EL NAVEGADOR</strong> (no solo la pestaña)\n";
    echo "3. Abre el navegador de nuevo\n";
    echo "4. Inicia sesión nuevamente\n";
    echo "5. El módulo 20 se cargará en \$_SESSION['modulos']\n";
} else {
    echo "<span class='success'>✅✅✅ TODO ESTÁ CORRECTO</span>\n";
    echo "El módulo está en sesión. Si no ves la sección en el menú,\n";
    echo "refresca la página con Ctrl+Shift+R (recarga forzada sin caché)\n";
}
echo "</pre>";

echo "<hr>";
echo "<p><a href='/'>← Volver al inicio</a></p>";
