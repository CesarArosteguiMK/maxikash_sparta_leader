<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/backend/core/Controller.php';
require_once dirname(__DIR__) . '/backend/controllers/CapHum.php';

use Controllers\CapHum;

$controller = (new ReflectionClass(CapHum::class))->newInstanceWithoutConstructor();
$counter = new ReflectionMethod($controller, 'contarPaginasPdfCandidato');
$counter->setAccessible(true);

$fixtures = ['solicitud_interna_maxikash.pdf' => 2];

foreach ($fixtures as $filename => $expected) {
    $path = dirname(__DIR__) . '/backend/storage/plantillas_candidatos/' . $filename;
    if (!is_file($path)) {
        fwrite(STDERR, "No existe fixture PDF: {$filename}\n");
        exit(1);
    }
    $actual = $counter->invoke($controller, $path);
    if ($actual !== $expected) {
        fwrite(STDERR, "Conteo incorrecto para {$filename}: {$actual}, esperado {$expected}\n");
        exit(1);
    }
}

$compressedPath = dirname(__DIR__)
    . '/backend/storage/plantillas_candidatos/solicitud_interna_maxikash_AcroForm.pdf';
$compressedCount = $counter->invoke($controller, $compressedPath);
if ($compressedCount !== 0) {
    fwrite(STDERR, "Un PDF no soportado debe quedar como conteo desconocido, no aproximado.\n");
    exit(1);
}

$resolver = new ReflectionMethod($controller, 'resolverPaginasPdfDocumentoCandidato');
$resolver->setAccessible(true);
$resolvedCount = $resolver->invoke($controller, [
    'verificacion_calidad_json' => json_encode([
        'motor_ia' => 'gemini',
        'paginas_pdf' => 2,
    ]),
], $compressedPath);
if ($resolvedCount !== 2) {
    fwrite(STDERR, "No se recupero el conteo de 2 paginas confirmado por el precheck.\n");
    exit(1);
}

echo "CapHumPdfPageCountTest OK\n";
