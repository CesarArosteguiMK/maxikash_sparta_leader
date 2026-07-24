<?php

namespace Services;

use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeonidasArtifactBuilder
{
    private GeminiMediaClient $client;
    private LeonidasDiagramRenderer $diagramRenderer;

    public function __construct(
        ?GeminiMediaClient $client = null,
        ?LeonidasDiagramRenderer $diagramRenderer = null
    ) {
        $this->client = $client ?? new GeminiMediaClient();
        $this->diagramRenderer = $diagramRenderer ?? new LeonidasDiagramRenderer();
    }

    /** @return array<string, mixed> */
    public function buildDiagram(string $prompt): array
    {
        $result = $this->client->generateStructuredJson(
            "Convierte la solicitud en un diagrama claro. No inventes hechos ni cifras. " .
            "Devuelve titulo, subtitulo, nodes y edges. Solicitud:\n" . $prompt,
            [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string'],
                    'subtitle' => ['type' => 'string'],
                    'nodes' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'string'],
                                'label' => ['type' => 'string'],
                                'type' => ['type' => 'string', 'enum' => ['start', 'process', 'decision', 'end']],
                            ],
                            'required' => ['id', 'label', 'type'],
                        ],
                    ],
                    'edges' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'from' => ['type' => 'string'],
                                'to' => ['type' => 'string'],
                                'label' => ['type' => 'string'],
                            ],
                            'required' => ['from', 'to', 'label'],
                        ],
                    ],
                ],
                'required' => ['title', 'subtitle', 'nodes', 'edges'],
            ]
        );
        if (empty($result['success'])) {
            return $result;
        }

        return [
            'success' => true,
            'body' => $this->diagramRenderer->render((array) $result['data']),
            'mime' => 'image/svg+xml',
            'model' => (string) ($result['model'] ?? 'Gemini'),
            'name_hint' => 'diagrama',
        ];
    }

    /** @return array<string, mixed> */
    public function buildPdf(string $prompt, string $requester = ''): array
    {
        $result = $this->client->generateStructuredJson(
            "Prepara un documento profesional en espanol basado UNICAMENTE en la informacion suministrada. " .
            "No inventes nombres, cifras, registros ni resultados operativos. Si faltan datos, indicalo en notes. " .
            "Devuelve title, subtitle, sections (heading y paragraphs) y notes. Solicitud:\n" . $prompt,
            [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string'],
                    'subtitle' => ['type' => 'string'],
                    'sections' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'heading' => ['type' => 'string'],
                                'paragraphs' => ['type' => 'array', 'items' => ['type' => 'string']],
                            ],
                            'required' => ['heading', 'paragraphs'],
                        ],
                    ],
                    'notes' => ['type' => 'array', 'items' => ['type' => 'string']],
                ],
                'required' => ['title', 'subtitle', 'sections', 'notes'],
            ]
        );
        if (empty($result['success'])) {
            return $result;
        }

        $data = (array) $result['data'];
        $mpdf = new Mpdf([
            'format' => 'Letter',
            'margin_left' => 18,
            'margin_right' => 18,
            'margin_top' => 18,
            'margin_bottom' => 18,
        ]);
        $mpdf->SetTitle((string) ($data['title'] ?? 'Documento de Leonidas'));
        $html = '<style>body{font-family:dejavusans;color:#26384f;font-size:10.5pt}h1{color:#17365d;font-size:22pt;margin-bottom:4px}h2{color:#294f78;font-size:14pt;border-bottom:1px solid #dbe3ed;padding-bottom:4px;margin-top:20px}.subtitle{color:#718096;margin-bottom:18px}.meta{background:#f1f5f9;padding:8px;border-left:4px solid #476f9f}.notes{background:#fff7e6;border:1px solid #f1d49b;padding:10px}p{line-height:1.55}</style>';
        $html .= '<h1>' . $this->html((string) ($data['title'] ?? 'Documento generado')) . '</h1>';
        $html .= '<div class="subtitle">' . $this->html((string) ($data['subtitle'] ?? 'Sparta')) . '</div>';
        $html .= '<div class="meta">Generado por Leonidas el ' . date('d/m/Y H:i');
        if ($requester !== '') {
            $html .= ' para ' . $this->html($requester);
        }
        $html .= '.</div>';
        foreach (array_slice((array) ($data['sections'] ?? []), 0, 30) as $section) {
            if (!is_array($section)) {
                continue;
            }
            $html .= '<h2>' . $this->html((string) ($section['heading'] ?? 'Seccion')) . '</h2>';
            foreach (array_slice((array) ($section['paragraphs'] ?? []), 0, 50) as $paragraph) {
                $html .= '<p>' . nl2br($this->html((string) $paragraph)) . '</p>';
            }
        }
        $notes = array_values(array_filter(array_map('strval', (array) ($data['notes'] ?? []))));
        if ($notes !== []) {
            $html .= '<h2>Notas y limites de la informacion</h2><div class="notes"><ul>';
            foreach ($notes as $note) {
                $html .= '<li>' . $this->html($note) . '</li>';
            }
            $html .= '</ul></div>';
        }
        $mpdf->WriteHTML($html);

        return [
            'success' => true,
            'body' => $mpdf->Output('', 'S'),
            'mime' => 'application/pdf',
            'model' => (string) ($result['model'] ?? 'Gemini'),
            'name_hint' => $this->slug((string) ($data['title'] ?? 'reporte')),
        ];
    }

    /** @return array<string, mixed> */
    public function buildSpreadsheet(string $prompt, string $requester = ''): array
    {
        $result = $this->client->generateStructuredJson(
            "Prepara una hoja de calculo basada UNICAMENTE en los datos suministrados. " .
            "No inventes personas, cifras ni registros. Conserva identificadores y ceros a la izquierda como texto. " .
            "Si no hay datos tabulares, crea una plantilla util y explica la carencia en notes. " .
            "Devuelve title, sheet_name, columns, rows y notes. Cada row debe tener exactamente una celda por columna. Solicitud:\n" . $prompt,
            [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string'],
                    'sheet_name' => ['type' => 'string'],
                    'columns' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'rows' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                    'notes' => ['type' => 'array', 'items' => ['type' => 'string']],
                ],
                'required' => ['title', 'sheet_name', 'columns', 'rows', 'notes'],
            ]
        );
        if (empty($result['success'])) {
            return $result;
        }

        $data = (array) $result['data'];
        $columns = array_values(array_slice(array_map('strval', (array) ($data['columns'] ?? [])), 0, 80));
        if ($columns === []) {
            return ['success' => false, 'message' => 'Gemini no devolvio columnas validas para el Excel.'];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($this->sheetName((string) ($data['sheet_name'] ?? 'Reporte')));
        foreach ($columns as $columnIndex => $column) {
            $sheet->setCellValueExplicit([$columnIndex + 1, 1], $column, DataType::TYPE_STRING);
        }
        foreach (array_slice((array) ($data['rows'] ?? []), 0, 5000) as $rowIndex => $row) {
            if (!is_array($row)) {
                continue;
            }
            foreach ($columns as $columnIndex => $_column) {
                $value = $row[$columnIndex] ?? '';
                $sheet->setCellValueExplicit(
                    [$columnIndex + 1, $rowIndex + 2],
                    is_bool($value) ? ($value ? 'Si' : 'No') : (string) $value,
                    DataType::TYPE_STRING
                );
            }
        }
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF294F78');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:' . $lastColumn . max(1, $sheet->getHighestRow()));
        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
        for ($columnIndex = 1; $columnIndex <= $lastColumnIndex; $columnIndex++) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $notes = array_values(array_filter(array_map('strval', (array) ($data['notes'] ?? []))));
        if ($requester !== '') {
            $notes[] = 'Solicitado por: ' . $requester;
        }
        $notes[] = 'Generado por Leonidas el ' . date('d/m/Y H:i') . '.';
        if ($notes !== []) {
            $notesSheet = $spreadsheet->createSheet();
            $notesSheet->setTitle('Notas');
            $notesSheet->setCellValue('A1', 'Notas y limites de la informacion');
            $notesSheet->getStyle('A1')->getFont()->setBold(true);
            foreach ($notes as $index => $note) {
                $notesSheet->setCellValueExplicit([1, $index + 2], $note, DataType::TYPE_STRING);
            }
            $notesSheet->getColumnDimension('A')->setWidth(90);
        }
        $spreadsheet->setActiveSheetIndex(0);

        $temp = tempnam(sys_get_temp_dir(), 'leonidas-xlsx-');
        if (!is_string($temp)) {
            throw new \RuntimeException('No se pudo preparar el archivo Excel.');
        }
        try {
            (new Xlsx($spreadsheet))->save($temp);
            $body = file_get_contents($temp);
        } finally {
            @unlink($temp);
            $spreadsheet->disconnectWorksheets();
        }
        if (!is_string($body) || $body === '') {
            throw new \RuntimeException('No se pudo construir el archivo Excel.');
        }

        return [
            'success' => true,
            'body' => $body,
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'model' => (string) ($result['model'] ?? 'Gemini'),
            'name_hint' => $this->slug((string) ($data['title'] ?? 'reporte')),
        ];
    }

    private function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($value)) ?: 'reporte';
        return trim(substr($value, 0, 60), '-') ?: 'reporte';
    }

    private function sheetName(string $value): string
    {
        $value = preg_replace('/[\\\\\\/?*\\[\\]:]/', ' ', trim($value)) ?: 'Reporte';
        return mb_substr($value, 0, 31, 'UTF-8') ?: 'Reporte';
    }
}
