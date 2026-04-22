<?php
/**
 * Lee solo las primeras filas de un xlsx pesado (sin cargar toda la hoja).
 */
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class FirstRowsFilter implements IReadFilter
{
    private int $startRow;
    private int $endRow;

    public function __construct(int $startRow, int $endRow)
    {
        $this->startRow = $startRow;
        $this->endRow = $endRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}

$f = $argv[1] ?? 'C:\\Users\\amigo_j9s4pcx\\Downloads\\SEMANA 16 PARA JON.xlsx';
if (!is_readable($f)) {
    fwrite(STDERR, "No se puede leer: $f\n");
    exit(1);
}

$filter = new FirstRowsFilter(1, 3);
$reader = new XlsxReader();
$reader->setReadDataOnly(true);
$reader->setReadEmptyCells(false);
$reader->setReadFilter($filter);

$ss = $reader->load($f);
$ws = $ss->getActiveSheet();
$h = $ws->getHighestRow();
$c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ws->getHighestColumn());
echo "Columnas en rango leído: $c  Filas: $h\n";
for ($r = 1; $r <= min(3, $h); $r++) {
    echo "--- Fila $r ---\n";
    for ($i = 1; $i <= min($c, 200); $i++) {
        $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        $v = $ws->getCell($letter . $r)->getValue();
        if ($v !== null && trim((string) $v) !== '') {
            echo $letter . "\t" . substr(str_replace(["\n", "\r"], ' ', (string) $v), 0, 100) . "\n";
        }
    }
}
