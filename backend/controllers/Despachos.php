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
            // Controlador Despachos inicializado
        </script>
        HTML;

        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Asignación de créditos ' . $emp);
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
        // Usamos filter_input o $_GET directamente para evitar el error de "member function get() on null"
        $id_celula = isset($_GET['id_celula']) ? intval($_GET['id_celula']) : 1;

        $despachos = $this->model->obtenerDespachos($id_celula);

        if ($despachos) {
            echo json_encode([
                'success' => true,
                'despachos' => $despachos
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontraron registros para esta célula'
            ]);
        }
    } catch (\Exception $e) {
        // IMPORTANTE: Asegúrate de que el header sea JSON para que el navegador no se confunda
        header('Content-Type: application/json');
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
     * JSON para importación Excel: nombre (persona) + id_despacho (tabla despachos) por id_persona del select.
     */
    public function ObtenerDespachoParaImportacionExcel($idPersona)
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $row = $this->model->obtenerDespachoImportacionPorIdPersona($idPersona);
            if (!$row) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró la persona o no es un gestor/supervisor de despacho.'
                ]);
                return;
            }

            $idDesp = $row['id_despacho'] ?? null;
            echo json_encode([
                'success' => true,
                'id_persona' => (int) $row['id_persona'],
                'nombre_completo' => trim((string) ($row['nombre_completo'] ?? '')),
                'id_despacho' => $idDesp !== null && $idDesp !== '' ? (int) $idDesp : null
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al consultar despacho: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Catálogo: filas de la tabla despachos (activos) + nombre de persona; mismo cardinal que despachos.
     */
    public function ObtenerCatalogoDespachosImportacionExcel()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filas = $this->model->obtenerCatalogoDespachosParaImportacionExcel();
            $out = [];
            foreach ($filas as $f) {
                $idDesp = $f['id_despacho'] ?? null;
                $out[] = [
                    'id_persona' => (int) ($f['id_persona'] ?? 0),
                    'nombre_completo' => trim((string) ($f['nombre_completo'] ?? '')) ?: '—',
                    'id_despacho' => $idDesp !== null && $idDesp !== '' ? (int) $idDesp : null
                ];
            }
            echo json_encode([
                'success' => true,
                'total' => count($out),
                'filas' => $out
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener el catálogo: ' . $e->getMessage(),
                'filas' => []
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
    /**
 * Asignar crédito a un despacho
 * POST: id_persona, id_credito, id_celula
 */
public function AsignarCredito()
{
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $idPersona = $input['id_persona'] ?? null;
        $idCredito = $input['id_credito'] ?? null;
        $idCelula = $input['id_celula'] ?? null;

        // Validar campos requeridos
        if (!$idPersona || !$idCredito) {
            echo json_encode([
                'success' => false,
                'message' => 'Faltan parámetros: id_persona y id_credito son requeridos'
            ]);
            return;
        }

        // Validar que id_celula sea válido (1=Despacho, 2=Gestión Call Center)
        if ($idCelula === null || !in_array($idCelula, [1, 2])) {
            echo json_encode([
                'success' => false,
                'message' => 'Tipo de célula no válido. Debe ser 1 (Despacho) o 2 (Gestión Call Center)'
            ]);
            return;
        }

        // Verificar si ya está asignado activamente
        $yaAsignado = $this->model->verificarAsignacion($idCredito);
        if ($yaAsignado) {
            echo json_encode([
                'success' => false,
                'message' => 'Este crédito ya está asignado a un despacho'
            ]);
            return;
        }

        // Llamar al modelo con los tres parámetros
        $resultado = $this->model->asignarCredito($idPersona, $idCredito, $idCelula);

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
 * Desasignar crédito de su despacho actual (soft-delete)
 * POST: { "id_credito": int }
 */
public function DesasignarCredito()
{
    try {
        $input     = json_decode(file_get_contents('php://input'), true);
        $idCredito = $input['id_credito'] ?? null;

        if (!$idCredito) {
            echo json_encode([
                'success' => false,
                'message' => 'Falta el parámetro id_credito'
            ]);
            return;
        }

        // Verificar que hay una asignación activa antes de intentar
        if (!$this->model->verificarAsignacion($idCredito)) {
            echo json_encode([
                'success' => false,
                'message' => 'Este crédito no tiene una asignación activa'
            ]);
            return;
        }

        $resultado = $this->model->desasignarCredito($idCredito);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Crédito desasignado correctamente'
                : 'No se pudo desasignar el crédito'
        ]);

    } catch (\Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al desasignar crédito: ' . $e->getMessage()
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
            // Sin enriquecimiento: la tabla solo necesita datos locales (id, estado, fecha, asignado_por)
            $creditos = $this->model->obtenerCreditosAsignados($idDespacho, false);

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
            // Aumentar tiempo de ejecución para reportes grandes
            set_time_limit(300); // 5 minutos
            ini_set('memory_limit', '512M');

            $creditos = $this->model->obtenerCreditosAsignados($idDespacho);
            $datosDespacho = $this->model->obtenerDatosDespacho($idDespacho);

            // Los datos ya vienen desde la base de datos, no necesitamos llamar a la API

            // Generar Excel usando PhpSpreadsheet
            if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
                require_once dirname(__DIR__) . '/bootstrap_composer.php';
                sparta_require_composer_autoload();
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Título
            $sheet->setCellValue('A1', 'Créditos Asignados al Despacho');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Información del despacho
            $nombreDespacho = $datosDespacho['datos']['nombre_completo'] ?? 'N/A';
            $sheet->setCellValue('A2', 'Despacho: ' . $nombreDespacho);
            $sheet->mergeCells('A2:H2');

            // Encabezados — id_credito e id_despacho primero para que la re-importación funcione sin editar el archivo
            $headers = ['id_credito', 'id_despacho', 'Cliente', 'Saldo', 'Días Mora', 'Estado', 'Fecha Asignación', 'Asignado Por'];
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
                // id_despacho: usar el campo si viene de BD, o el parámetro de la URL
                $idDespachoExport = $credito['id_despacho'] ?? $idDespacho;
                $sheet->setCellValue('B' . $row, $idDespachoExport);
                $sheet->setCellValue('C' . $row, $credito['nombre_cliente']);
                $sheet->setCellValue('D' . $row, '$' . number_format($credito['saldo'], 2));
                $sheet->setCellValue('E' . $row, $credito['dias_mora']);
                $estadoTexto = ($credito['estado'] === '1' || $credito['estado'] === 1) ? 'Activo' : 'Inactivo';
                $sheet->setCellValue('F' . $row, $estadoTexto);
                $sheet->setCellValue('G' . $row, $credito['fecha_asignacion']);
                $sheet->setCellValue('H' . $row, $credito['asignado_por'] ?? 'N/A');
                $row++;
            }

            // Ajustar anchos de columna
            foreach (range('A', 'H') as $col) {
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

            // Validar MIME real (no confiar en extensión del cliente)
            if (!\Core\SecureUpload::validateMime($archivo['tmp_name'], \Core\SecureUpload::MIME_DESPACHO)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tipo de archivo no permitido. Solo PDF, JPG, PNG, GIF, WebP, DOC o DOCX.'
                ]);
                return;
            }

            $mime = \Core\SecureUpload::getMimeType($archivo['tmp_name']);
            $extension = $mime ? \Core\SecureUpload::extensionFromMime($mime) : 'bin';

            // Validar tamaño (5MB máximo)
            if ($archivo['size'] > 5 * 1024 * 1024) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El archivo excede el tamaño máximo permitido de 5MB'
                ]);
                return;
            }

            $directorioBase = sparta_uploads_join('documentos_despacho');
            \Core\SecureUpload::ensureDir($directorioBase);

            $nombreArchivo = \Core\SecureUpload::generateSafeFilename($extension);
            $rutaCompleta = sparta_uploads_join('documentos_despacho', $nombreArchivo);
            $rutaRelativa = 'documentos_despacho/' . $nombreArchivo;

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

            $relPath = $documento['ruta_archivo'];
            // Compatibilidad con registros anteriores que incluyen 'uploads/' como prefijo
            if (str_starts_with($relPath, 'uploads/')) {
                $relPath = substr($relPath, 8);
            }
            $rutaCompleta = sparta_uploads_resolve_relative($relPath);

            // [DEBUG TEMPORAL] — eliminar antes de producción final
            $debugPath = sparta_uploads_root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
            error_log('[DEBUG DescargarDocumento] id=' . $idDocumento . ' | ruta_bd=' . $documento['ruta_archivo'] . ' | relPath=' . $relPath . ' | debugPath=' . $debugPath . ' | rutaCompleta=' . ($rutaCompleta ?? 'NULL') . ' | exists=' . (file_exists($debugPath) ? 'SI' : 'NO'));

            if ($rutaCompleta === null || !file_exists($rutaCompleta)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Archivo no encontrado en el servidor',
                    '_debug' => ['relPath' => $relPath, 'intentado' => $debugPath, 'exists' => file_exists($debugPath)]
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

    /**
     * Vista: Mi Gestión (créditos asignados al gestor autenticado)
     */
    public function MiGestion()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Mi Cartera ' . $emp);
        return self::render('mi_gestion_despacho');
    }

    /**
     * API: Obtener créditos asignados al usuario autenticado
     */
    public function ObtenerMisCreditos()
    {
        try {
            $idPersona = $_SESSION['usuario_id'] ?? null;

            if (!$idPersona) {
                echo json_encode(['success' => false, 'message' => 'Sesión no identificada']);
                return;
            }

            $creditos = $this->model->obtenerCreditosAsignados($idPersona);

            echo json_encode([
                'success' => true,
                'creditos' => $creditos
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener créditos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Exportar Excel de Mi Gestión
     */
    public function ExportarMiGestion()
    {
        try {
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            $idPersona = $_SESSION['usuario_id'] ?? null;
            if (!$idPersona) {
                echo 'Sesión no identificada';
                return;
            }

            $creditos      = $this->model->obtenerCreditosAsignados($idPersona);
            $datosDespacho = $this->model->obtenerDatosDespacho($idPersona);

            if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
                require_once dirname(__DIR__) . '/bootstrap_composer.php';
                sparta_require_composer_autoload();
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Mi Gestión - Créditos Asignados');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $nombre = $datosDespacho['datos']['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'N/A');
            $sheet->setCellValue('A2', 'Gestor: ' . $nombre);
            $sheet->mergeCells('A2:G2');

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

            $row = 5;
            foreach ($creditos as $credito) {
                $sheet->setCellValue('A' . $row, $credito['id_credito']);
                $sheet->setCellValue('B' . $row, $credito['nombre_cliente']);
                $sheet->setCellValue('C' . $row, '$' . number_format($credito['saldo'], 2));
                $sheet->setCellValue('D' . $row, $credito['dias_mora']);
                $estadoTexto = ($credito['estado'] === '1' || $credito['estado'] === 1) ? 'Activo' : 'Inactivo';
                $sheet->setCellValue('E' . $row, $estadoTexto);
                $sheet->setCellValue('F' . $row, $credito['fecha_asignacion']);
                $sheet->setCellValue('G' . $row, $credito['asignado_por'] ?? 'N/A');
                $row++;
            }

            foreach (range('A', 'G') as $c) {
                $sheet->getColumnDimension($c)->setAutoSize(true);
            }

            $filename = 'MiGestion_' . date('Y-m-d_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            echo 'Error al exportar: ' . $e->getMessage();
        }
    }

    /**
     * Descargar plantilla para importar créditos asignados
     */
    public function DescargarPlantillaExcelAsignacionCreditosDespacho()
    {
        $raizProyecto = dirname(__DIR__, 2);
        $path = $raizProyecto . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'plantilla.xlsx';

        if (!is_readable($path)) {
            http_response_code(404);
            echo 'No se encontró la plantilla en el servidor (public/templates/plantilla.xlsx).';
            return;
        }

        $filename = 'plantilla.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($path));

        readfile($path);
        exit;
    }

    /**
     * Emite JSON siempre con cuerpo no vacío (evita json_encode false por UTF-8 inválido → respuesta vacía en el cliente).
     *
     * @param array<string,mixed> $data
     */
    private function emitJsonImportacion(array $data): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $flags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        $json = json_encode($data, $flags);
        if ($json === false) {
            $json = json_encode([
                'success' => false,
                'message' => 'No se pudo generar la respuesta JSON: ' . json_last_error_msg(),
            ], $flags);
            if ($json === false) {
                $json = '{"success":false,"message":"Error interno al codificar respuesta"}';
            }
        }
        echo $json;
    }

    /**
     * Importar Excel (asignación masiva)
     * - $_FILES['excel'] (obligatorio)
     * - $_POST['id_persona'] (opcional): si el Excel no trae columna id_despacho, se usa el despacho del gestor seleccionado en pantalla.
     */
    public function ImportarExcelAsignacionCreditosDespacho()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();

        header('Content-Type: application/json; charset=utf-8');

        try {
            @ini_set('memory_limit', '1024M');
            @set_time_limit(0);

            $idPersonaRaw = $_POST['id_persona'] ?? null;
            $idPersona = ($idPersonaRaw !== null && $idPersonaRaw !== '' && (int) $idPersonaRaw > 0)
                ? (int) $idPersonaRaw
                : null;

            if (!isset($_FILES['excel']) || !is_array($_FILES['excel'])) {
                http_response_code(400);
                $this->emitJsonImportacion([
                    'success' => false,
                    'message' => 'No se recibió el archivo Excel.',
                ]);
                return;
            }

            if ($_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
                $uploadErr = (int) ($_FILES['excel']['error'] ?? 0);
                $msgExtra = '';
                if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
                    $msgExtra = ' El archivo supera el límite de subida del servidor (php.ini post_max_size / upload_max_filesize).';
                }
                http_response_code(400);
                $this->emitJsonImportacion([
                    'success' => false,
                    'message' => 'Error al subir el archivo Excel (código ' . $uploadErr . ').' . $msgExtra,
                ]);
                return;
            }

            $fileSize = (int) ($_FILES['excel']['size'] ?? 0);
            if ($fileSize <= 0) {
                http_response_code(400);
                $this->emitJsonImportacion([
                    'success' => false,
                    'message' => 'El archivo no tiene tamaño válido.',
                ]);
                return;
            }

            // Límite razonable para evitar tiempos/memoria excesivos
            $maxSize = 20 * 1024 * 1024; // 20MB
            if ($fileSize > $maxSize) {
                http_response_code(400);
                $this->emitJsonImportacion([
                    'success' => false,
                    'message' => 'El archivo excede el tamaño máximo permitido (20MB).',
                ]);
                return;
            }

            $ext = strtolower(pathinfo($_FILES['excel']['name'] ?? '', PATHINFO_EXTENSION));
            $allowed = ['xlsx', 'xls'];
            if (!in_array($ext, $allowed, true)) {
                http_response_code(400);
                $this->emitJsonImportacion([
                    'success' => false,
                    'message' => 'Formato no permitido. Usa .xlsx o .xls (descarga la plantilla para evitar errores).',
                ]);
                return;
            }

            $tmpPath = $_FILES['excel']['tmp_name'] ?? null;
            if (!$tmpPath || !file_exists($tmpPath)) {
                http_response_code(400);
                $this->emitJsonImportacion([
                    'success' => false,
                    'message' => 'Archivo temporal no disponible.',
                ]);
                return;
            }

            $originalName = $_FILES['excel']['name'] ?? 'excel';

            $resultado = $this->model->importarAsignaCreditosDesdeExcel($idPersona ?? 0, $tmpPath);

            if (!is_array($resultado)) {
                http_response_code(500);
                $this->emitJsonImportacion([
                    'success' => false,
                    'message' => 'Respuesta inválida del servidor al procesar el Excel.',
                    'archivo' => $originalName,
                ]);
                return;
            }

            $resultado['archivo'] = $originalName;
            $this->emitJsonImportacion($resultado);
            return;
        } catch (\Throwable $e) {
            http_response_code(500);
            $this->emitJsonImportacion([
                'success' => false,
                'message' => 'Error en la importación: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Historial completo de gestores que tuvieron asignado un crédito.
     * POST body: { id_credito }
     */
    public function ObtenerHistorialGestores()
    {
        try {
            $input     = json_decode(file_get_contents('php://input'), true);
            $idCredito = $input['id_credito'] ?? null;

            if (empty($idCredito)) {
                echo json_encode(['success' => false, 'message' => 'Falta id_credito']);
                return;
            }

            $historial = $this->model->obtenerHistorialGestores($idCredito);
            echo json_encode(['success' => true, 'historial' => $historial]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Convenios registrados para un crédito.
     * POST body: { id_credito }
     */
    public function ObtenerConveniosCredito()
    {
        try {
            $input     = json_decode(file_get_contents('php://input'), true);
            $idCredito = $input['id_credito'] ?? null;

            if (empty($idCredito)) {
                echo json_encode(['success' => false, 'message' => 'Falta id_credito']);
                return;
            }

            $convenios = $this->model->obtenerConveniosCredito($idCredito);
            echo json_encode(['success' => true, 'convenios' => $convenios]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
