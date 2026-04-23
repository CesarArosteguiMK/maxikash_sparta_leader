<?php
/**
 * Genera mapeo_datos_app_s2.xlsx con la tabla "Datos" (Figma App S2 / condonaciones).
 * Uso: php backend/scripts/generar_excel_mapeo_datos_app_s2.php
 */
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$out = dirname(__DIR__, 2) . '/mapeo_datos_app_s2.xlsx';

$rows = [
    ['Pantalla', 'Campo', 'Fuente', 'Etiqueta'],
    ['Principal', 'Nombre del cliente', 'S2Credit', 'nombreCliente'],
    ['Principal', 'Vencido/Al corriente', 'S2Credit', 'bandera'],
    ['Principal', 'Total a pagar', 'resumenCondonaciones', 'totalAPagar'],
    ['Principal', 'Número de cuotas', 'resumenCondonaciones', 'numeroCuotasCredito'],
    ['Principal', 'Saldo vencido', 'resumenCondonaciones', 'saldoVencidoCredito'],
    ['Principal', 'Cargo por pago tardío', 'resumenCondonaciones', 'totalCargosPagosTardio'],
    ['Tu progreso', 'Tu progreso', 'S2Credit', 'cuotasPagadas / cuotasContratadas'],
    ['Tu progreso', 'Pagos semanales de', 'S2Credit', 'cuota'],
    ['Tu progreso', 'Por pagar', 'S2Credit', 'adeudoTotal'],
    ['Últimos movimientos', 'Últimos movimientos', 'S2Credit', 'fechaDeposito, montoPago, moratorios'],
    ['Perfil', 'Total a pagar', 'S2Credit', '(cuotasContratadas) * (cuota)'],
    ['Perfil', 'Celular', 'S2Credit', 'celular'],
    ['Perfil', 'Inicio de financiamiento', 'S2Credit', 'primerVencimiento'],
    ['Perfil', 'Fin de financiamiento', 'S2Credit', 'ultimoVencimiento'],
    ['Datos para la transferencia', 'Cuenta clabe personalizada', 'S2Credit', 'referenciaSTP'],
    ['Datos para la transferencia', 'Banco destino', 'MaxiApp', 'banco'],
    ['Datos para la transferencia', 'Nombre del destinatario', 'S2Credit', 'nombreCliente'],
    ['Detalles del financiamiento', 'Fecha de abono inicial', 'S2Credit', 'primerVencimiento'],
    ['Detalles del financiamiento', 'Forma de Pago', 'MaxiApp', 'metodoPago'],
    ['Detalles del financiamiento', 'Agencia', 'MaxiApp', 'nombreSucursal'],
];

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$sheet->setTitle('Datos');

$rowNum = 1;
foreach ($rows as $r) {
    $col = 'A';
    foreach ($r as $cell) {
        $sheet->setCellValue($col . $rowNum, $cell);
        $col++;
    }
    $rowNum++;
}

// Encabezado: estilo
$headerRange = 'A1:D1';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('4472C4');
$sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

foreach (['A', 'B', 'C', 'D'] as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

$writer = new Xlsx($ss);
$writer->save($out);

echo "Archivo generado: $out\n";
