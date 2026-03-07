<?php
/**
 * Elimina BOM (Byte Order Mark) UTF-8 (EF BB BF) del inicio de archivos PHP.
 * Ejecutar desde la raíz del proyecto: php backend/scripts/remove_bom.php
 */
$root = dirname(__DIR__);
$dirs = [
    $root . '/controllers',
    $root . '/views',
    $root . '/core',
    $root . '/models',
];
$bom = "\xEF\xBB\xBF";
$fixed = 0;
$checked = 0;

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') continue;
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $checked++;
        if (substr($content, 0, 3) === $bom) {
            $newContent = substr($content, 3);
            if (file_put_contents($path, $newContent) !== false) {
                echo "BOM removido: " . str_replace($root . DIRECTORY_SEPARATOR, '', $path) . "\n";
                $fixed++;
            }
        }
    }
}

echo "\nRevisados: $checked archivos. BOM eliminado en: $fixed archivos.\n";
