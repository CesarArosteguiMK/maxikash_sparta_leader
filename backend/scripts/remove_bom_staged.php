<?php
/**
 * Quita BOM de los archivos PHP indicados (por línea en stdin).
 * Uso: git diff --cached --name-only | grep '\.php$' | php backend/scripts/remove_bom_staged.php
 * O: php backend/scripts/remove_bom_staged.php archivo1.php archivo2.php
 */
$bom = "\xEF\xBB\xBF";
$paths = [];

if (count($argv) > 1) {
    $paths = array_slice($argv, 1);
} else {
    $stdin = stream_get_contents(STDIN);
    $paths = array_filter(array_map('trim', preg_split('/\r?\n/', $stdin)));
}

$modified = [];
foreach ($paths as $path) {
    $path = trim($path);
    if ($path === '' || !is_file($path)) {
        continue;
    }
    $content = file_get_contents($path);
    if (substr($content, 0, 3) !== $bom) {
        continue;
    }
    if (file_put_contents($path, substr($content, 3)) !== false) {
        $modified[] = $path;
    }
}

foreach ($modified as $p) {
    echo $p . "\n";
}
