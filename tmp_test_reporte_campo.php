<?php
error_reporting(E_ALL);
define('RAIZ', __DIR__ . '/backend');
define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
require_once __DIR__ . '/vendor/autoload.php';
spl_autoload_register(function ($archivo) {
    $archivo = str_replace('\\', '/', $archivo);
    $ruta = RAIZ . "/$archivo.php";
    if (is_readable($ruta)) {
        require_once $ruta;
    }
});

$reporte = (new Services\ReporteCampoService())->generarExcel();
$spreadsheet = $reporte['spreadsheet'];
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();
$bajas = 0;
for ($row = 2; $row <= $highestRow; $row++) {
    if (strtolower(trim((string)$sheet->getCell('C' . $row)->getValue())) === 'baja') {
        $bajas++;
    }
}
echo json_encode([
    'total' => $reporte['total'],
    'highest_row' => $highestRow,
    'bajas' => $bajas,
    'headers' => [
        $sheet->getCell('A1')->getValue(),
        $sheet->getCell('B1')->getValue(),
        $sheet->getCell('C1')->getValue(),
        $sheet->getCell('D1')->getValue(),
    ],
    'first_data' => [
        $sheet->getCell('A2')->getValue(),
        $sheet->getCell('B2')->getValue(),
        $sheet->getCell('C2')->getValue(),
        $sheet->getCell('D2')->getValue(),
    ],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
$spreadsheet->disconnectWorksheets();
