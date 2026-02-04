<?php

namespace Controllers;

use Core\Controller;
use Models\Despachos as DespachosDAO;

class Despachos extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DespachosDAO();
    }

    /**
     * Vista principal de asignación de créditos a despachos
     */
    public function AsignacionCreditosDespacho()
    {
        $script = <<<'HTML'
        <script>
            console.log('✅ Controlador Despachos ejecutado correctamente');
            console.log('📄 Vista: asignacion_creditosDespacho.php');
        </script>
        HTML;
        
        self::set("titulo", "Asignación de Créditos - Despachos de Cobranza");
        self::set("script", $script);
        return self::render("asignacion_creditosDespacho");
    }

    /**
     * Obtener lista de despachos (Gestores y Supervisores)
     * Devuelve: JSON con array de despachos
     */
    public function ObtenerListaDespachos()
    {
        try {
            $despachos = $this->model->obtenerDespachos();
            
            if ($despachos) {
                echo json_encode([
                    'success' => true,
                    'despachos' => $despachos
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron despachos'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener despachos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener datos completos de un despacho específico
     * Parámetro: $idDespacho
     */
    public function ObtenerDatosDespacho($idDespacho)
    {
        try {
            $datos = $this->model->obtenerDatosDespacho($idDespacho);
            $metricas = $this->model->obtenerMetricasDespacho($idDespacho);
            
            echo json_encode([
                'success' => true,
                'informacion' => $datos['informacion'] ?? '',
                'comentarios' => $datos['comentarios'] ?? [],
                'metricas' => $metricas
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener datos del despacho: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar crédito por diferentes criterios
     * POST: tipo (id_credito, nombre_cliente, curp), valor
     */
    public function BuscarCredito()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $tipo = $input['tipo'] ?? '';
            $valor = $input['valor'] ?? '';

            if (empty($tipo) || empty($valor)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan parámetros de búsqueda'
                ]);
                return;
            }

            $credito = $this->model->buscarCredito($tipo, $valor);
            
            if ($credito) {
                echo json_encode([
                    'success' => true,
                    'credito' => $credito
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Crédito no encontrado'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al buscar crédito: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Asignar crédito a un despacho
     * POST: id_despacho, id_credito
     */
    public function AsignarCredito()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idDespacho = $input['id_despacho'] ?? null;
            $idCredito = $input['id_credito'] ?? null;

            if (!$idDespacho || !$idCredito) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan parámetros'
                ]);
                return;
            }

            // Verificar si ya está asignado
            $yaAsignado = $this->model->verificarAsignacion($idCredito);
            if ($yaAsignado) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Este crédito ya está asignado a un despacho'
                ]);
                return;
            }

            $resultado = $this->model->asignarCredito($idDespacho, $idCredito);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Crédito asignado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo asignar el crédito'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al asignar crédito: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Desasignar crédito de un despacho
     * POST: id_credito
     */
    public function DesasignarCredito()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idCredito = $input['id_credito'] ?? null;

            if (!$idCredito) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Falta el ID del crédito'
                ]);
                return;
            }

            $resultado = $this->model->desasignarCredito($idCredito);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Crédito desasignado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo desasignar el crédito'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al desasignar crédito: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener lista de créditos asignados a un despacho
     * Parámetro: $idDespacho
     */
    public function ObtenerCreditosAsignados($idDespacho)
    {
        try {
            $creditos = $this->model->obtenerCreditosAsignados($idDespacho);
            
            echo json_encode([
                'success' => true,
                'creditos' => $creditos
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener créditos asignados: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar comentario sobre un despacho
     * POST: id_despacho, comentario
     */
    public function GuardarComentarios()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idDespacho = $input['id_despacho'] ?? null;
            $comentario = $input['comentarios'] ?? '';

            if (!$idDespacho) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Falta el ID del despacho'
                ]);
                return;
            }

            $resultado = $this->model->guardarComentario($idDespacho, $comentario);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Comentario guardado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo guardar el comentario'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar comentario: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Exportar créditos asignados a Excel
     * Parámetro: $idDespacho
     */
    public function ExportarExcel($idDespacho)
    {
        try {
            $creditos = $this->model->obtenerCreditosAsignados($idDespacho);
            $datosDespacho = $this->model->obtenerDatosDespacho($idDespacho);
            
            // Generar Excel usando PhpSpreadsheet
            require_once __DIR__ . '/../libs/PhpSpreadsheet/vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Título
            $sheet->setCellValue('A1', 'Créditos Asignados al Despacho');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Información del despacho
            $sheet->setCellValue('A2', 'Despacho: ' . ($datosDespacho['nombre_despacho'] ?? 'N/A'));
            $sheet->mergeCells('A2:G2');
            
            // Encabezados
            $headers = ['ID Crédito', 'Cliente', 'Saldo', 'Días Mora', 'Estado', 'Fecha Asignación', 'Asignado Por'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '4', $header);
                $sheet->getStyle($col . '4')->getFont()->setBold(true);
                $sheet->getStyle($col . '4')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1e293b');
                $sheet->getStyle($col . '4')->getFont()->getColor()->setARGB('FFFFFFFF');
                $col++;
            }
            
            // Datos
            $row = 5;
            foreach ($creditos as $credito) {
                $sheet->setCellValue('A' . $row, $credito['id_credito']);
                $sheet->setCellValue('B' . $row, $credito['nombre_cliente']);
                $sheet->setCellValue('C' . $row, '$' . number_format($credito['saldo'], 2));
                $sheet->setCellValue('D' . $row, $credito['dias_mora']);
                $sheet->setCellValue('E' . $row, $credito['estado']);
                $sheet->setCellValue('F' . $row, $credito['fecha_asignacion']);
                $sheet->setCellValue('G' . $row, $credito['asignado_por'] ?? 'N/A');
                $row++;
            }
            
            // Ajustar anchos de columna
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Descargar archivo
            $filename = 'Creditos_Despacho_' . date('Y-m-d_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            echo "Error al exportar: " . $e->getMessage();
        }
    }
}
