<?php
/**
 * Actualiza el PDF embebido en solicitud_llenar.html con solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf
 * Ejecutar una vez: php actualizar_pdf_embebido.php
 */
$dir = __DIR__;
$pdfFile = $dir . '/solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf';
$htmlFile = $dir . '/solicitud_llenar.html';

if (!is_file($pdfFile)) {
    fwrite(STDERR, "No existe: $pdfFile\n");
    exit(1);
}
if (!is_file($htmlFile)) {
    fwrite(STDERR, "No existe: $htmlFile\n");
    exit(1);
}

$pdfBytes = file_get_contents($pdfFile);
$b64 = base64_encode($pdfBytes);

$html = file_get_contents($htmlFile);
$marker = 'const PDF_B64 = "';
$start = strpos($html, $marker);
if ($start === false) {
    fwrite(STDERR, "No se encontró const PDF_B64 en el HTML.\n");
    exit(1);
}
$startQuote = $start + strlen($marker);
$end = strpos($html, '";', $startQuote);
if ($end === false) {
    fwrite(STDERR, "No se encontró el cierre del PDF_B64 en el HTML.\n");
    exit(1);
}
$newHtml = substr($html, 0, $startQuote) . $b64 . '";' . substr($html, $end + 2);

if (!file_put_contents($htmlFile, $newHtml)) {
    fwrite(STDERR, "Error al escribir $htmlFile\n");
    exit(1);
}

echo "OK: solicitud_llenar.html actualizado con el PDF embebido de solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf\n";
