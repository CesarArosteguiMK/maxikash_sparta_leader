<?php
/**
 * OPCIONAL: Solo quita la última línea "//# sourceMappingURL=..." en .mjs del pdf-viewer
 * para evitar el 404 de pdf.mjs.map. NO tocar los .js del vendor (rompen el código).
 *
 * Los avisos "unsupported protocol webpack://" se pueden ocultar en DevTools:
 * Chrome/Edge: F12 → Settings → desmarcar "Enable JavaScript source maps"
 */
$files = [
    __DIR__ . '/vendor/libs/pdf-viewer/pdf.mjs',
    __DIR__ . '/vendor/libs/pdf-viewer/pdf.worker.mjs',
];
foreach ($files as $path) {
    if (!is_file($path)) continue;
    $content = file_get_contents($path);
    $last = preg_replace('/[\r\n]+$/', '', $content);
    if (preg_match('/\/\/# sourceMappingURL=[^\r\n]+$/', $last)) {
        $content = preg_replace('/[\r\n]*\/\/# sourceMappingURL=[^\r\n]+[\r\n]*$/', "\n", $content);
        file_put_contents($path, $content);
        echo "Quitada referencia en: " . basename($path) . "\n";
    }
}
echo "Listo (solo pdf-viewer).\n";
