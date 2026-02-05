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
                'datos' => $datos['datos'] ?? [],
                'comentarios' => $datos['comentarios'] ?? '',
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
                // Obtener información de asignación si existe
                $asignacion = $this->model->obtenerAsignacionCredito($valor);
                
                echo json_encode([
                    'success' => true,
                    'credito' => $credito,
                    'asignacion' => $asignacion
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
     * POST: id_persona, id_credito
     */
    public function AsignarCredito()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idPersona = $input['id_persona'] ?? null;
            $idCredito = $input['id_credito'] ?? null;

            if (!$idPersona || !$idCredito) {
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

            $resultado = $this->model->asignarCredito($idPersona, $idCredito);
            
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
    public function CambiarEstatusCredito()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idCredito = $input['id_credito'] ?? null;
            $nuevoEstatus = $input['nuevo_estatus'] ?? null;

            if (!$idCredito || $nuevoEstatus === null) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ]);
                return;
            }

            $resultado = $this->model->cambiarEstatusCredito($idCredito, $nuevoEstatus);
            
            if ($resultado) {
                $mensaje = $nuevoEstatus === '1' ? 'Crédito activado correctamente' : 'Crédito desactivado correctamente';
                echo json_encode([
                    'success' => true,
                    'message' => $mensaje
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo cambiar el estatus del crédito'
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
            
            // Enriquecer datos de créditos con información de la API
            foreach ($creditos as &$credito) {
                $datosCredito = $this->model->buscarCredito('id_credito', $credito['id_credito']);
                if ($datosCredito) {
                    $credito['nombre_cliente'] = $datosCredito['nombre_cliente'];
                    $credito['saldo'] = $datosCredito['saldo_actual'];
                    $credito['dias_mora'] = $datosCredito['dias_mora'];
                } else {
                    $credito['nombre_cliente'] = 'No disponible';
                    $credito['saldo'] = 0;
                    $credito['dias_mora'] = 0;
                }
            }
            unset($credito); // Romper la referencia
            
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
            $nombreDespacho = $datosDespacho['datos']['nombre_completo'] ?? 'N/A';
            $sheet->setCellValue('A2', 'Despacho: ' . $nombreDespacho);
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

    /**
     * Obtener catálogo de documentos
     */
    public function ObtenerCatalogoDocumentos()
    {
        try {
            $catalogo = $this->model->obtenerCatalogoDocumentos();
            echo json_encode([
                'success' => true,
                'catalogo' => $catalogo
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener catálogo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener documentos de un despacho
     */
    public function ObtenerDocumentosDespacho()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idPersona = $input['id_persona'] ?? null;

            if (!$idPersona) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Falta el ID de la persona'
                ]);
                return;
            }

            $documentos = $this->model->obtenerDocumentosDespacho($idPersona);
            echo json_encode([
                'success' => true,
                'documentos' => $documentos
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener documentos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Subir documento para un despacho
     */
    public function SubirDocumento()
    {
        try {
            $idPersona = $_POST['id_persona'] ?? null;
            $idCatalogoDocumento = $_POST['id_catalogo_documento'] ?? null;

            if (!$idPersona || !$idCatalogoDocumento) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ]);
                return;
            }

            // Validar que se subió un archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se recibió ningún archivo o hubo un error en la carga'
                ]);
                return;
            }

            $archivo = $_FILES['archivo'];
            
            // Validar extensión
            $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, $extensionesPermitidas)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Extensión de archivo no permitida. Solo se permiten: ' . implode(', ', $extensionesPermitidas)
                ]);
                return;
            }

            // Validar tamaño (5MB máximo)
            if ($archivo['size'] > 5 * 1024 * 1024) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El archivo excede el tamaño máximo permitido de 5MB'
                ]);
                return;
            }

            // Crear directorio si no existe
            $directorioBase = __DIR__ . '/../../uploads/documentos_despacho';
            if (!file_exists($directorioBase)) {
                mkdir($directorioBase, 0777, true);
            }

            // Generar nombre único para el archivo
            $nombreOriginal = pathinfo($archivo['name'], PATHINFO_FILENAME);
            $nombreArchivo = $nombreOriginal . '_' . time() . '.' . $extension;
            $rutaCompleta = $directorioBase . '/' . $nombreArchivo;
            $rutaRelativa = 'uploads/documentos_despacho/' . $nombreArchivo;

            // Mover archivo
            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al guardar el archivo en el servidor'
                ]);
                return;
            }

            // Guardar en base de datos
            $resultado = $this->model->subirDocumento($idPersona, $idCatalogoDocumento, $nombreArchivo, $rutaRelativa);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Documento subido correctamente',
                    'nombre_archivo' => $nombreArchivo
                ]);
            } else {
                // Si falla la BD, eliminar el archivo
                unlink($rutaCompleta);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al guardar en la base de datos'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Descargar documento
     */
    public function DescargarDocumento($idDocumento)
    {
        try {
            // Obtener información del documento
            $documento = $this->model->obtenerInfoDocumento($idDocumento);

            if (!$documento) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ]);
                return;
            }

            $rutaCompleta = __DIR__ . '/../../' . $documento['ruta_archivo'];

            if (!file_exists($rutaCompleta)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Archivo no encontrado en el servidor'
                ]);
                return;
            }

            // Detectar tipo de archivo
            $extension = strtolower(pathinfo($documento['nombre_archivo'], PATHINFO_EXTENSION));
            
            // Configurar Content-Type según extensión
            $contentTypes = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            
            $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
            
            // Para PDFs e imágenes, usar inline para permitir visualización
            // Para otros archivos, forzar descarga
            $disposition = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']) ? 'inline' : 'attachment';
            
            // Enviar archivo
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: ' . $disposition . '; filename="' . $documento['nombre_archivo'] . '"');
            header('Content-Length: ' . filesize($rutaCompleta));
            readfile($rutaCompleta);
            exit;

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al descargar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar tipo de persona del despacho
     */
    public function ActualizarTipoPersona()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idPersona = $input['id_persona'] ?? null;
            $tipoPersona = $input['tipo_persona'] ?? null;

            if (!$idPersona || !$tipoPersona) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ]);
                return;
            }

            // Validar que el tipo sea FISICA o MORAL
            if (!in_array($tipoPersona, ['FISICA', 'MORAL'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tipo de persona no válido. Solo se permite FISICA o MORAL'
                ]);
                return;
            }

            $resultado = $this->model->actualizarTipoPersona($idPersona, $tipoPersona);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tipo de persona actualizado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo actualizar el tipo de persona'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar tipo de persona: ' . $e->getMessage()
            ]);
        }
    }
}
