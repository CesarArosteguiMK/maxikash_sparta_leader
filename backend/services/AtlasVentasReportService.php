<?php

namespace Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;

final class AtlasVentasReportService
{
    public const HEADERS = [
        'id_persona',
        'id_oferta',
        'Nombre_cliente',
        'Fecha de dispersión',
        'sucursal',
        'distribuidor',
        'fecha_oferta',
        'fecha_ETAPA ACTUAL',
        'etapa',
        'precio_moto',
        'enganche',
        'monto_financiar',
        'semanas',
        'oferta',
        'modelo_moto',
        'marca_moto',
        'usuario ',
        'Nombre del vendedor',
        'pk_sucursal',
        'fk_distribuidor',
    ];

    public function crear(array $ventas): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Sparta Atlas')
            ->setTitle('Ventas Atlas')
            ->setSubject('Ventas contabilizadas por rango de fechas');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hoja1');
        $sheet->setShowGridlines(false);
        $sheet->freezePane('A2');
        $sheet->fromArray(self::HEADERS, null, 'A1');

        $row = 2;
        foreach ($ventas as $venta) {
            $this->setInteger($sheet, 'A' . $row, $venta['id_persona'] ?? 0);
            $this->setInteger($sheet, 'B' . $row, $venta['id_oferta'] ?? 0);
            $sheet->setCellValueExplicit('C' . $row, (string)($venta['nombre_cliente'] ?? ''), DataType::TYPE_STRING);
            $this->setDate($sheet, 'D' . $row, $venta['fecha_dispersion'] ?? null);
            $sheet->setCellValueExplicit('E' . $row, (string)($venta['sucursal'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, (string)($venta['distribuidor'] ?? ''), DataType::TYPE_STRING);
            $this->setDate($sheet, 'G' . $row, $venta['fecha_oferta'] ?? null);
            $this->setDate($sheet, 'H' . $row, $venta['fecha_etapa_actual'] ?? null);
            $sheet->setCellValueExplicit('I' . $row, (string)($venta['etapa'] ?? ''), DataType::TYPE_STRING);
            $this->setNumber($sheet, 'J' . $row, $venta['precio_moto'] ?? 0);
            $this->setNumber($sheet, 'K' . $row, $venta['enganche'] ?? 0);
            $this->setNumber($sheet, 'L' . $row, $venta['monto_financiar'] ?? 0);
            $sheet->setCellValueExplicit('M' . $row, (string)($venta['semanas'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('N' . $row, (string)($venta['oferta'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('O' . $row, (string)($venta['modelo_moto'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('P' . $row, (string)($venta['marca_moto'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('Q' . $row, (string)($venta['usuario'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('R' . $row, (string)($venta['nombre_vendedor'] ?? ''), DataType::TYPE_STRING);
            $this->setInteger($sheet, 'S' . $row, $venta['pk_sucursal'] ?? 0);
            $this->setInteger($sheet, 'T' . $row, $venta['fk_distribuidor'] ?? 0);
            $row++;
        }

        $lastRow = max(1, $row - 1);
        $table = new Table('A1:T' . $lastRow, 'VentasAtlas');
        $table->setStyle((new TableStyle())->setTheme(TableStyle::TABLE_STYLE_MEDIUM4));
        $sheet->addTable($table);

        $sheet->getStyle('A1:T1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:T1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('70AD47');
        $sheet->getStyle('A1:T' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('D9EAD3');
        $sheet->getStyle('A1:T' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('C2:I' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('O2:R' . $lastRow)->getAlignment()->setWrapText(true);
        if ($lastRow >= 2) {
            $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');
            $sheet->getStyle('G2:H' . $lastRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');
            $sheet->getStyle('J2:L' . $lastRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        }

        $widths = [
            'A' => 13, 'B' => 13, 'C' => 31, 'D' => 22, 'E' => 29,
            'F' => 29, 'G' => 22, 'H' => 22, 'I' => 19, 'J' => 15,
            'K' => 14, 'L' => 18, 'M' => 11, 'N' => 16, 'O' => 23,
            'P' => 18, 'Q' => 19, 'R' => 29, 'S' => 14, 'T' => 17,
        ];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->getRowDimension(1)->setRowHeight(28);
        for ($current = 2; $current <= $lastRow; $current++) {
            $sheet->getRowDimension($current)->setRowHeight(24);
        }

        return $spreadsheet;
    }

    private function setDate($sheet, string $cell, $value): void
    {
        $text = trim((string)$value);
        if ($text === '') {
            $sheet->setCellValueExplicit($cell, '', DataType::TYPE_STRING);
            return;
        }
        try {
            $sheet->setCellValue($cell, Date::PHPToExcel(new \DateTimeImmutable($text)));
        } catch (\Throwable $e) {
            $sheet->setCellValueExplicit($cell, $text, DataType::TYPE_STRING);
        }
    }

    private function setNumber($sheet, string $cell, $value): void
    {
        $sheet->setCellValueExplicit($cell, (float)$value, DataType::TYPE_NUMERIC);
    }

    private function setInteger($sheet, string $cell, $value): void
    {
        $sheet->setCellValueExplicit($cell, (int)$value, DataType::TYPE_NUMERIC);
    }
}
