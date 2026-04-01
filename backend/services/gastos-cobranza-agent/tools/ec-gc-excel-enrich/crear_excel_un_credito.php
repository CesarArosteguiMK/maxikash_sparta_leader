<?php
/**
 * Genera un .xlsx mínimo (columna CREDITO) para probar enrich_gc_excel.php con un solo id.
 *
 * Uso:
 *   php crear_excel_un_credito.php --id=1422728 --output="C:\Users\...\Desktop\prueba_un_credito.xlsx"
 */

declare(strict_types=1);

$baseDir = dirname(__FILE__);

$autoloadVendor = '';
for ($dir = $baseDir, $i = 0; $i < 16; $i++, $dir = dirname($dir)) {
    $try = $dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    if (is_readable($try)) {
        $autoloadVendor = $try;
        break;
    }
    if ($dir === dirname($dir)) {
        break;
    }
}

$opts = getopt('', ['id:', 'output:', 'help']);
if ($opts === false || isset($opts['help']) || empty($opts['id'])) {
    fwrite(STDERR, "Uso: php crear_excel_un_credito.php --id=1422728 --output=ruta\\salida.xlsx\n");
    exit(isset($opts['help']) ? 0 : 1);
}

$id = (int) $opts['id'];
$out = isset($opts['output']) ? trim((string) $opts['output'], " \t\"'") : $baseDir . DIRECTORY_SEPARATOR . 'prueba_un_credito.xlsx';
if ($id <= 0) {
    fwrite(STDERR, "ID inválido.\n");
    exit(1);
}

if ($autoloadVendor === '') {
    fwrite(STDERR, "vendor/autoload.php no encontrado (composer install en la raíz del repo).\n");
    exit(1);
}
require_once $autoloadVendor;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$ss = new Spreadsheet();
$sh = $ss->getActiveSheet();
$sh->setCellValue('A1', 'CREDITO');
$sh->setCellValue('B1', 'NOMBRE CLIENTE');
$sh->setCellValue('A2', $id);
$sh->setCellValue('B2', 'Prueba');

$w = new Xlsx($ss);
$w->save($out);

echo "Creado: {$out}\n";
