<?php
/**
 * Script para verificar y eliminar BOM de archivos PHP
 * Uso: php scripts/verificar_bom.php [--fix]
 * 
 * --fix: Elimina automáticamente el BOM de los archivos afectados
 */

$fix = in_array('--fix', $argv);

function hasBOM($file) {
    $handle = fopen($file, 'rb');
    if (!$handle) return false;
    $bytes = fread($handle, 3);
    fclose($handle);
    return $bytes === "\xEF\xBB\xBF";
}

function removeBOM($file) {
    $content = file_get_contents($file);
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
        file_put_contents($file, $content);
        return true;
    }
    return false;
}

function findPHPFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

$root = dirname(__DIR__);
$phpFiles = findPHPFiles($root . '/backend');

$filesWithBOM = [];
foreach ($phpFiles as $file) {
    if (hasBOM($file)) {
        $filesWithBOM[] = $file;
    }
}

if (empty($filesWithBOM)) {
    echo "✅ No se encontraron archivos PHP con BOM.\n";
    exit(0);
}

echo "⚠️  Se encontraron " . count($filesWithBOM) . " archivo(s) con BOM:\n\n";
foreach ($filesWithBOM as $file) {
    $relative = str_replace($root . '\\', '', $file);
    echo "  - $relative\n";
}

if ($fix) {
    echo "\n🔧 Eliminando BOM...\n";
    foreach ($filesWithBOM as $file) {
        if (removeBOM($file)) {
            $relative = str_replace($root . '\\', '', $file);
            echo "  ✅ BOM eliminado: $relative\n";
        }
    }
    echo "\n✅ Proceso completado.\n";
} else {
    echo "\n💡 Para eliminar automáticamente el BOM, ejecuta:\n";
    echo "   php scripts/verificar_bom.php --fix\n";
}

exit(count($filesWithBOM) > 0 ? 1 : 0);

