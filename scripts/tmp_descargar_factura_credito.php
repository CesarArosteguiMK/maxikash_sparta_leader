<?php
/**
 * TEMPORAL — Descarga factura de un crédito (misma lógica que EstadoCuenta / FACTURA).
 * Borrar este archivo cuando ya no se necesite.
 *
 * Uso:
 *   c:\xampp\php\php.exe scripts/tmp_descargar_factura_credito.php 1874127
 *
 * Guarda en: scripts/tmp_factura_out/
 */

declare(strict_types=1);

$repoRoot = dirname(__DIR__);

define('RAIZ', $repoRoot . '/backend');
define('SPARTA_PROJECT_ROOT', $repoRoot);
define('SPARTA_UPLOADS_ROOT', $repoRoot . '/public/uploads');

require_once RAIZ . '/bootstrap_composer.php';
sparta_require_composer_autoload();
require_once RAIZ . '/core/UploadsPaths.php';

use Models\EstadoCuenta;

$id = isset($argv[1]) ? (int) $argv[1] : 1874127;
if ($id <= 0) {
    fwrite(STDERR, "Uso: php scripts/tmp_descargar_factura_credito.php ID_CREDITO\n");
    exit(1);
}

function s3Exists(string $fileName): bool
{
    $s3Url = 'http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=' . urlencode($fileName);
    $ch = curl_init($s3Url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $code === 200;
}

function s3Get(string $fileName): ?string
{
    $s3Url = 'http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=' . urlencode($fileName);
    $ch = curl_init($s3Url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 120,
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || $data === false || $data === '') {
        return null;
    }

    return $data;
}

$outDir = __DIR__ . '/tmp_factura_out';
if (!is_dir($outDir)) {
    if (!@mkdir($outDir, 0755, true)) {
        fwrite(STDERR, "No se pudo crear el directorio: {$outDir}\n");
        exit(1);
    }
}

// 1) Local doc_cliente
$directorioBase = sparta_uploads_join('documentos', 'doc_cliente');
if (is_dir($directorioBase)) {
    $idSeguro = preg_replace('/\D/', '', (string) $id);
    $patron = $directorioBase . DIRECTORY_SEPARATOR . $idSeguro . '_FACTURA_*.{pdf,jpg,jpeg,png}';
    $archivos = glob($patron, GLOB_BRACE);
    if ($archivos && count($archivos) > 0) {
        usort($archivos, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $src = $archivos[0];
        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
        $dest = $outDir . '/factura_' . $id . '_local.' . $ext;
        if (@copy($src, $dest)) {
            echo "OK copiado desde local:\n{$dest}\n";
            exit(0);
        }
        fwrite(STDERR, "Archivo local encontrado pero falló copy(): {$src}\n");
        exit(1);
    }
}

// 2) S3 estándar
$fileName = 'FACTURA/' . $id . '_factura.pdf';
if (s3Exists($fileName)) {
    $bin = s3Get($fileName);
    if ($bin !== null) {
        $dest = $outDir . '/factura_' . $id . '_s3.pdf';
        file_put_contents($dest, $bin);
        echo "OK descargado desde S3 (forma estándar):\n{$dest}\n";
        exit(0);
    }
}

// 3) BD oferta_documentos + S3
try {
    $res = EstadoCuenta::obtenerDocumentoPorTipo($id, 'FACTURA');
    if (!empty($res['success']) && !empty($res['datos']['nombre_archivo'])) {
        $nombreBD = basename(str_replace(['\\', '/'], '/', (string) $res['datos']['nombre_archivo']));
        if ($nombreBD !== '') {
            $fileNameBD = 'FACTURA/' . $nombreBD;
            if (s3Exists($fileNameBD)) {
                $bin = s3Get($fileNameBD);
                if ($bin !== null) {
                    $ext = strtolower(pathinfo($nombreBD, PATHINFO_EXTENSION)) ?: 'pdf';
                    $dest = $outDir . '/factura_' . $id . '_bd.' . $ext;
                    file_put_contents($dest, $bin);
                    echo "OK descargado desde S3 (nombre en BD):\n{$dest}\n";
                    exit(0);
                }
            }
            fwrite(STDERR, "BD tiene nombre_archivo={$nombreBD} pero S3 no respondió 200 para FACTURA/{$nombreBD}\n");
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error consultando BD: ' . $e->getMessage() . "\n");
}

fwrite(STDERR, "No se pudo resolver la factura para id_credito={$id} (local / S3 / BD).\n");
exit(2);
