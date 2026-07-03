<?php

namespace Controllers;

use Core\Controller;
use Models\AvanceBucket;
use Models\ComparativoCierreSemanal;
use Models\Direcciones;
use Models\EstadoCuenta as EstadoCuentaDAO;
use Models\Empresa as EmpresasDAO;
use Models\PrimerosPagosHistoricoSegundometro;
use Models\SegundometroComparativaSemanal;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Services\ReporteCampoService;

require_once dirname(__DIR__) . '/cronjobs/PrimerosPagosAutoSwitch.php';

class Reporteria extends Controller
{
    /** URL base pública «Analítica» (controlador PHP Reporteria; históricamente /reporteria/). */
    private const BASE_PRIMEROS_PAGOS_ANALITICA = '/analitica';

    /**
     * Permiso especial para gestión/envío de correos en Primeros pagos.
     * Compatibilidad:
     * - 33: configuración previa del proyecto.
     * - 25: alta reciente de permiso especial «Correos».
     */
    private const MODULOS_ENVIAR_CORREO_PRIMEROS_PAGOS = [33, 25];

    /**
     * Permisos especiales (pestaña «Permisos especiales» en modulos_web): cada tarjeta y su vista exigen su id (65–68).
     * El módulo 49 no sustituye a 65–68: solo sirve para el ítem de menú y entrar al landing vacío hasta asignar cards.
     */
    private const MODULO_PP_COBRANZA_ESPERADA = 65;
    private const MODULO_PP_CARTERA = 66;
    /** Entrada «Cartera actual» y JSON de cartera: solo `modulos_web` id 19. */
    private const MODULO_CARTERA_ACTUAL_MENU = 19;
    private const MODULO_PP_PROXIMA_SEMANA = 67;
    private const MODULO_PP_HISTORICO = 68;

    /** Usuario que puede ejecutar copia + purga histórico primeros pagos desde la UI (solo este id). */
    private const USUARIO_PIPELINE_HISTO_PRIMEROS_DESDE_SEGUNDOMETRO = 878;
    private const PP_DESTINATARIOS_FILE = RAIZ . '/storage/config/primeros_pagos_destinatarios.json';

    /** @return list<int> */
    private function modulosSesionInt(): array
    {
        return array_map('intval', (array) ($_SESSION['modulos'] ?? []));
    }

    /** Cobranza esperada — semana actual: solo módulo 65 en sesión (sin bypass por usuario 1). */
    private function puedeAccederCobranzaEsperadaSemanaActual(): bool
    {
        return in_array(self::MODULO_PP_COBRANZA_ESPERADA, $this->modulosSesionInt(), true);
    }

    private function puedeAccederCarteraSegundometro(): bool
    {
        return in_array(self::MODULO_CARTERA_ACTUAL_MENU, $this->modulosSesionInt(), true);
    }

    private function puedeAccederPrimerosPagosProximaSemana(): bool
    {
        return in_array(self::MODULO_PP_PROXIMA_SEMANA, $this->modulosSesionInt(), true);
    }

    private function puedeAccederPrimerosPagosHistorico(): bool
    {
        return in_array(self::MODULO_PP_HISTORICO, $this->modulosSesionInt(), true);
    }

    private function puedeMostrarBotonPipelineHistoPrimerosDesdeSegundometro(): bool
    {
        return (int) ($_SESSION['usuario_id'] ?? 0) === self::USUARIO_PIPELINE_HISTO_PRIMEROS_DESDE_SEGUNDOMETRO;
    }

    /** Solo permiso especial asignado en sesión (sin bypass por usuario). */
    private function puedeEnviarCorreoPrimerosPagos(): bool
    {
        $mods = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        foreach (self::MODULOS_ENVIAR_CORREO_PRIMEROS_PAGOS as $mid) {
            if (in_array((int) $mid, $mods, true)) {
                return true;
            }
        }
        return false;
    }

    /** @return list<string> */
    private function primerosPagosDestinatariosDefault(): array
    {
        return [
            'roman.jimenez@__SPARTA_SECRET_REDACTED__.mx',
            'marlon.flores@__SPARTA_SECRET_REDACTED__.mx',
            'hector.ruiz@__SPARTA_SECRET_REDACTED__.mx',
            'guillermo.garcia@__SPARTA_SECRET_REDACTED__.mx',
            '__SPARTA_SECRET_REDACTED__@__SPARTA_SECRET_REDACTED__.mx',
            'josealberto.hernandez@__SPARTA_SECRET_REDACTED__.mx',
            'josue.aldrete@__SPARTA_SECRET_REDACTED__.mx',
            'erika.ortiz@__SPARTA_SECRET_REDACTED__.mx',
            'lrgonzalez033@gmail.com',
        ];
    }

    /**
     * @param array<int, string|array{email?:mixed,activo?:mixed}> $raw
     * @return list<array{email:string,activo:bool}>
     */
    private function normalizarDestinatariosConEstado(array $raw): array
    {
        $map = [];
        foreach ($raw as $item) {
            $emailRaw = is_array($item) ? ($item['email'] ?? '') : $item;
            $email = strtolower(trim((string) $emailRaw));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $activo = true;
            if (is_array($item)) {
                $v = $item['activo'] ?? true;
                $activo = !in_array($v, [false, 0, '0', 'false', 'FALSE', 'off', 'OFF', 'no', 'NO'], true);
            }
            $map[$email] = ['email' => $email, 'activo' => $activo];
        }
        return array_values($map);
    }

    /**
     * @return array{destinatarios:list<array{email:string,activo:bool}>,updated_at:?string}
     */
    private function cargarConfigDestinatariosPrimerosPagos(): array
    {
        $path = self::PP_DESTINATARIOS_FILE;
        $updatedAt = null;
        if (!is_file($path)) {
            $items = $this->normalizarDestinatariosConEstado($this->primerosPagosDestinatariosDefault());
            return ['destinatarios' => $items, 'updated_at' => null];
        }
        $raw = @file_get_contents($path);
        $json = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($json)) {
            $items = $this->normalizarDestinatariosConEstado($this->primerosPagosDestinatariosDefault());
            return ['destinatarios' => $items, 'updated_at' => null];
        }
        $lista = $json['destinatarios'] ?? [];
        if (!is_array($lista)) {
            $lista = [];
        }
        $items = $this->normalizarDestinatariosConEstado($lista);
        if (empty($items)) {
            $items = $this->normalizarDestinatariosConEstado($this->primerosPagosDestinatariosDefault());
        }
        $updatedAt = isset($json['updated_at']) ? (string) $json['updated_at'] : null;
        return ['destinatarios' => $items, 'updated_at' => $updatedAt];
    }

    /** @return list<string> */
    private function cargarDestinatariosPrimerosPagosActivos(): array
    {
        $cfg = $this->cargarConfigDestinatariosPrimerosPagos();
        $activos = [];
        foreach ($cfg['destinatarios'] as $it) {
            if (!empty($it['activo'])) {
                $activos[] = (string) $it['email'];
            }
        }
        if (empty($activos)) {
            $defaults = $this->normalizarDestinatariosConEstado($this->primerosPagosDestinatariosDefault());
            foreach ($defaults as $it) {
                $activos[] = $it['email'];
            }
        }
        return array_values(array_unique($activos));
    }

    /**
     * @param array<int, string|array{email?:mixed,activo?:mixed}> $destinatarios
     * @return list<array{email:string,activo:bool}>
     */
    private function guardarDestinatariosPrimerosPagos(array $destinatarios): array
    {
        $items = $this->normalizarDestinatariosConEstado($destinatarios);
        if (empty($items)) {
            throw new \RuntimeException('Debes guardar al menos un correo válido.');
        }
        $dir = dirname(self::PP_DESTINATARIOS_FILE);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('No se pudo crear el directorio de configuración.');
        }
        $payload = [
            'destinatarios' => $items,
            'updated_at' => date('c'),
            'updated_by' => (int) ($_SESSION['usuario_id'] ?? 0),
        ];
        $ok = @file_put_contents(
            self::PP_DESTINATARIOS_FILE,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
        if ($ok === false) {
            throw new \RuntimeException('No se pudo guardar la configuración de correos.');
        }
        return $items;
    }

    /**
     * Lunes ISO (America/Mexico_City): la cartera «Cobranza esperada — semana actual» no está disponible hasta el martes.
     */
    private function esLunesCdmxCarteraSemanaActualCerrada(): bool
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $hoy = new \DateTimeImmutable('now', $tz);

        return (int) $hoy->format('N') === 1;
    }

    /**
     * Martes o miércoles ISO (America/Mexico_City): «Primeros pagos próxima semana» no está disponible; solo de jueves a lunes.
     */
    private function esMartesOMiercolesCdmxPrimerosPagosProximaSemanaCerrada(): bool
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $hoy = new \DateTimeImmutable('now', $tz);
        $n = (int) $hoy->format('N');

        return $n === 2 || $n === 3;
    }

    public function reporteCapitalHumano()
    {
        $script = "";
        self::set("titulo", "Reportes de Personal");
        self::set("script", $script);
        self::render("reporte_capital_humano");
    }

    public function reporteCampo()
    {
        self::set("titulo", "Reporte de Campo");
        self::render("reporte_campo");
    }

    public function descargarReporteCampoExcel()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $reporte = (new ReporteCampoService())->generarExcel();
            $spreadsheet = $reporte['spreadsheet'];
            $filename = 'reporte_campo_' . date('Ymd_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('X-Reporte-Campo-Total: ' . (string)($reporte['total'] ?? 0));

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
            exit;
        } catch (\Throwable $e) {
            error_log('Error en descargarReporteCampoExcel: ' . $e->getMessage());
            http_response_code(500);
            echo 'Error al generar el reporte de campo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            exit;
        }
    }

    // ==== Reportes de Personal ====
    public function getUsuariosCapitalHumano()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        try {
            $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? 0);
            if ($usuarioId <= 0) {
                echo json_encode([
                    'success' => false,
                    'datos' => [],
                    'mensaje' => 'Sesion no valida. Vuelve a iniciar sesion.'
                ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                exit;
            }

            $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
            $tieneDepartamento = in_array(10, $modulos, true);
            $resultado = \Models\CapHum::getConsultaGestoresAll($usuarioId, $tieneDepartamento);
            if (!($resultado['success'] ?? false)) {
                echo json_encode([
                    'success' => false,
                    'datos' => [],
                    'mensaje' => $resultado['mensaje'] ?? 'Error al cargar usuarios.',
                    'error' => $resultado['error'] ?? null
                ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                exit;
            }

            $usuarios = is_array($resultado['datos'] ?? null) ? $resultado['datos'] : [];
            $datos = array_map(function($p) {
                return [
                    'id' => $p['id'] ?? '',
                    'numero_empleado' => $p['numero_empleado'] ?? '',
                    'nombre_jefe' => $p['nombre_jefe'] ?? ($p['nombre_vacante_jefe'] ?? ''),
                    'nombres' => $p['nombres'] ?? '',
                    'segundo_nombre' => $p['segundo_nombre'] ?? '',
                    'apellidop' => $p['apellidop'] ?? '',
                    'apellidom' => $p['apellidom'] ?? '',
                    'nombre_departamento' => $p['nombre_departamento'] ?? '',
                    'nombre_puesto' => $p['nombre_puesto'] ?? '',
                    'id_puesto' => $p['id_puesto'] ?? null,
                    'id_departamento' => $p['id_departamento'] ?? null,
                    'estatus' => $p['estatus'] ?? '',
                    'usuario' => $p['usuario'] ?? ($p['user_name'] ?? ''),
                ];
            }, $usuarios);

            echo json_encode([
                'success' => true,
                'datos' => $datos
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        } catch (\Throwable $e) {
            error_log('Error en getUsuariosCapitalHumano: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'datos' => [],
                'mensaje' => 'Error al cargar usuarios.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        }
    }

    public function getBajasCapitalHumano()
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            $fecha_inicio = $input['fecha_inicio'] ?? null;
            $fecha_fin = $input['fecha_fin'] ?? null;

            if ($fecha_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Formato de fecha de inicio inválido'
                ]);
                return;
            }
            if ($fecha_fin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Formato de fecha de fin inválido'
                ]);
                return;
            }

            $resultado = \Models\CapHum::getConsultaBajas($fecha_inicio, $fecha_fin);

            if (!$resultado) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Error al consultar las bajas: respuesta vacía del modelo',
                    'datos' => []
                ]);
                return;
            }

            if (!isset($resultado['success']) || $resultado['success'] === false) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => $resultado['mensaje'] ?? 'Error al consultar las bajas',
                    'error' => $resultado['error'] ?? null,
                    'datos' => []
                ]);
                return;
            }

            $bajas = $resultado['datos'] ?? [];
            $datos = [];

            if (is_array($bajas) && count($bajas) > 0) {
                $datos = array_map(function($p) {
                    return [
                        'nombres' => $p['nombres'] ?? '',
                        'segundo_nombre' => $p['segundo_nombre'] ?? '',
                        'apellidop' => $p['apellidop'] ?? '',
                        'apellidom' => $p['apellidom'] ?? '',
                        'numero_empleado' => $p['numero_empleado'] ?? '',
                        'external_id' => $p['external_id'] ?? '',
                        'departamento' => $p['departamento'] ?? '',
                        'nombre_puesto' => $p['nombre_puesto'] ?? '',
                        'fecha_baja' => $p['fecha_baja'] ?? '',
                        'registro_baja' => $p['registro_baja'] ?? '',
                        'motivo' => $p['motivo'] ?? '',
                        'descripcion' => $p['descripcion'] ?? '',
                        'user_name' => $p['user_name'] ?? '',
                    ];
                }, $bajas);
            }

            self::respuestaJSON([
                'success' => true,
                'datos' => $datos,
                'mensaje' => count($datos) > 0 ? 'Bajas encontradas' : 'No se encontraron bajas en el rango seleccionado'
            ]);

        } catch (\Exception $e) {
            error_log('Error en getBajasCapitalHumano: ' . $e->getMessage());
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'datos' => []
            ]);
        }
    }

    public function descargarBajasExcelCapitalHumano()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        try {
            $departamento = $_GET['departamento'] ?? null;
            $puesto = $_GET['puesto'] ?? null;
            $estatus = $_GET['estatus'] ?? null;
            $multipuesto = $_GET['multipuesto'] ?? null;

            $resultado = \Models\CapHum::getConsultaBajasAvanzado([
                'departamento' => $departamento,
                'puesto' => $puesto,
                'estatus' => $estatus,
                'multipuesto' => $multipuesto
            ]);

            if (!$resultado || !isset($resultado['success']) || !$resultado['success']) {
                die('Error al obtener las bajas: ' . ($resultado['mensaje'] ?? 'Error desconocido'));
            }

            $bajas = $resultado['datos'] ?? [];

            if (empty($bajas)) {
                die('No hay bajas para descargar');
            }

            $data = [];
            foreach ($bajas as $baja) {
                $nombreCompleto = trim(implode(' ', array_filter([
                    $baja['nombres'] ?? '',
                    $baja['segundo_nombre'] ?? '',
                    $baja['apellidop'] ?? '',
                    $baja['apellidom'] ?? '',
                ], static fn($valor) => trim((string) $valor) !== '')));
                $fechaBaja = $baja['fecha_baja'] ?? '';
                if ($fechaBaja) {
                    try {
                        $fechaBaja = date('d/m/Y', strtotime($fechaBaja));
                    } catch (\Exception $e) {}
                }
                $data[] = [
                    'external_id' => $baja['external_id'] ?? '',
                    'numero_empleado' => $baja['numero_empleado'] ?? '',
                    'nombre_completo' => $nombreCompleto,
                    'departamento' => $baja['departamento'] ?? 'N/A',
                    'nombre_puesto' => $baja['nombre_puesto'] ?? 'N/A',
                    'fecha_baja' => $fechaBaja,
                    'registro_baja' => $baja['registro_baja'] ?? '',
                    'motivo' => $baja['motivo'] ?? 'N/A',
                    'descripcion' => $baja['descripcion'] ?? 'Sin descripción',
                    'user_name' => $baja['user_name'] ?? 'N/A'
                ];
            }

            $columnas = [
                \PHPSpreadsheet::ColumnaExcel('external_id', 'External ID'),
                \PHPSpreadsheet::ColumnaExcel('numero_empleado', 'NÚMERO DE EMPLEADO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_completo', 'NOMBRE COMPLETO'),
                \PHPSpreadsheet::ColumnaExcel('departamento', 'DEPARTAMENTO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_puesto', 'PUESTO'),
                \PHPSpreadsheet::ColumnaExcel('fecha_baja', 'FECHA DE BAJA'),
                \PHPSpreadsheet::ColumnaExcel('registro_baja', 'REGISTRO DE BAJA'),
                \PHPSpreadsheet::ColumnaExcel('motivo', 'MOTIVO'),
                \PHPSpreadsheet::ColumnaExcel('descripcion', 'DESCRIPCIÓN'),
                \PHPSpreadsheet::ColumnaExcel('user_name', 'USUARIO')
            ];

            $nombreArchivo = 'Bajas_' . date('Y-m-d');
            \PHPSpreadsheet::DescargaExcel(
                $nombreArchivo,
                "Bajas de Personal",
                "Bajas",
                $columnas,
                $data
            );
            exit;

        } catch (\Exception $e) {
            error_log('Error en descargarBajasExcelCapitalHumano: ' . $e->getMessage());
            die('Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Obtener filtros dinámicos para Reportes de Personal
     * GET /analitica/getFiltrosCapitalHumano
     */
    public function getFiltrosCapitalHumano()
    {
    // No activar display_errors en producción; los errores se registran en log del servidor
    // Limpiar cualquier salida previa
    while (ob_get_level()) ob_end_clean();

    try {
        // 1. Departamentos usando CapHum::getComboDepartamentos()
        $deptos = \Models\CapHum::getComboDepartamentos();

        // 2. Puestos (todos) usando CapHum::getConsultaPuestos(null)
        $puestos = \Models\CapHum::getConsultaPuestos(null);

        // 3. Motivos de baja únicos
        $motivos = [];
        try {
            $bajas = \Models\CapHum::getConsultaBajasAvanzado([]);
            if ($bajas && $bajas['success'] && !empty($bajas['datos'])) {
                foreach ($bajas['datos'] as $b) {
                    if (!empty($b['motivo']) && !in_array($b['motivo'], $motivos)) {
                        $motivos[] = $b['motivo'];
                    }
                }
                sort($motivos);
            }
        } catch (\Exception $e) {
            error_log('Error cargando motivos de baja: ' . $e->getMessage());
        }

        // 4. Estatus disponibles para usuarios activos
        $estatusUsuarios = ['Activo', 'Inactivo'];

        // ✅ RESPUESTA JSON LIMPIA
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'departamentos' => $deptos['datos'] ?? [],
            'puestos' => $puestos['datos'] ?? [],
            'estatus_bajas' => $motivos,
            'estatus_usuarios' => $estatusUsuarios
        ]);
        exit;

    } catch (\Exception $e) {
        error_log('Error en getFiltrosCapitalHumano: ' . $e->getMessage());
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al cargar filtros: ' . $e->getMessage()
        ]);
        exit;
    }
}

    /**
     * Descargar Excel de usuarios activos filtrados
     * GET /analitica/descargarUsuariosExcelCapitalHumano
     */
    public function descargarUsuariosExcelCapitalHumano()
    {
        // Limpiar buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            // Obtener filtros de GET
            $filtros = [
                'departamento' => $_GET['departamento'] ?? null,
                'puesto' => $_GET['puesto'] ?? null,
                'estatus' => $_GET['estatus'] ?? null,
                'multipuesto' => $_GET['multipuesto'] ?? null
            ];

            // Verificar sesión
            if (!isset($_SESSION['usuario_id'])) {
                header('Content-Type: text/html; charset=utf-8');
                die('Sesión no válida. Por favor inicia sesión nuevamente.');
            }

            // OPTIMIZACIÓN: Filtros aplicados directamente en SQL (una sola consulta)
            $resultado = \Models\CapHum::getGestoresParaReporte($filtros);

            if (!$resultado['success']) {
                header('Content-Type: text/html; charset=utf-8');
                die('Error al obtener datos: ' . ($resultado['mensaje'] ?? 'Error desconocido'));
            }

            $usuarios = $resultado['datos'] ?? [];

            if (empty($usuarios)) {
                die('No hay usuarios para descargar con los filtros seleccionados');
            }

            // Preparar datos para Excel
            $data = [];
            foreach ($usuarios as $u) {
                $nombreCompleto = trim(($u['nombres'] ?? '') . ' ' .
                                     ($u['segundo_nombre'] ?? '') . ' ' .
                                     ($u['apellidop'] ?? '') . ' ' .
                                     ($u['apellidom'] ?? ''));

                // Estado actual y motivo de ausencia vigente
                $razon        = $u['ausencia_razon'] ?? null;
                $fechaInicio  = $u['ausencia_fecha_inicio'] ?? null;
                $fechaFin     = $u['ausencia_fecha_fin'] ?? null;

                if ($razon !== null) {
                    $esVacaciones   = (stripos($razon, 'vacacion') !== false);
                    $estadoActual   = $esVacaciones ? 'Vacaciones' : 'Ausencia';
                    $fmtInicio      = $fechaInicio ? date('d-m-Y', strtotime($fechaInicio)) : '';
                    $fmtFin         = $fechaFin    ? date('d-m-Y', strtotime($fechaFin))    : '';
                    $motivo         = $razon . ' (del ' . $fmtInicio . ' al ' . $fmtFin . ')';
                } else {
                    $estadoActual = '';
                    $motivo       = '';
                }

                $data[] = [
                    'numero_empleado' => $u['numero_empleado'] ?? '',
                    'nombre_completo' => $nombreCompleto,
                    'telefono' => $u['telefono'] ?? '',
                    'departamento' => $u['nombre_departamento'] ?? 'N/A',
                    'puesto' => $u['nombre_puesto'] ?? 'N/A',
                    'estatus' => $u['estatus'] ?? 'N/A',
                    'usuario' => $u['usuario'] ?? 'N/A',
                    'jefe' => $u['nombre_jefe'] ?? 'N/A',
                    'estado_actual' => $estadoActual,
                    'motivo' => $motivo
                ];
            }

            // Columnas para Excel
            $columnas = [
                \PHPSpreadsheet::ColumnaExcel('numero_empleado', 'NÚMERO DE EMPLEADO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_completo', 'NOMBRE COMPLETO'),
                \PHPSpreadsheet::ColumnaExcel('telefono', 'TELÉFONO'),
                \PHPSpreadsheet::ColumnaExcel('departamento', 'DEPARTAMENTO'),
                \PHPSpreadsheet::ColumnaExcel('puesto', 'PUESTO'),
                \PHPSpreadsheet::ColumnaExcel('estatus', 'ESTATUS'),
                \PHPSpreadsheet::ColumnaExcel('usuario', 'USUARIO'),
                \PHPSpreadsheet::ColumnaExcel('jefe', 'JEFE INMEDIATO'),
                \PHPSpreadsheet::ColumnaExcel('estado_actual', 'ESTADO ACTUAL'),
                \PHPSpreadsheet::ColumnaExcel('motivo', 'MOTIVOS')
            ];

            // Nombre del archivo
            $nombreArchivo = "Plantilla_Gestores";

    // Agregar departamento si existe
    if (!empty($departamento)) {
        $nombreArchivo .= "_" . str_replace(' ', '_', $departamento);
    }

    // Agregar puesto si existe
    if (!empty($puesto)) {
        $nombreArchivo .= "_" . str_replace(' ', '_', $puesto);
    }

    // Agregar estatus si existe
    if (!empty($estatus)) {
        $nombreArchivo .= "_" . $estatus;
    }

    // Agregar fecha y hora
    $fecha = date('Y-m-d');
    $hora = date('His');
    $nombreArchivo .= "_{$fecha}_{$hora}";

            // Generar workbook y aplicar colores de fila según estado actual
            $libro = \PHPSpreadsheet::GeneraExcel(
                "Reporte_Capital_Humano",
                "Usuarios",
                $columnas,
                $data
            );

            // Colorear filas: Ausencia = amarillo claro, Vacaciones = azul claro
            // Se aplica celda por celda (igual que GeneraExcel internamente) para garantizar
            // que el color de fondo quede pintado y no sea sobreescrito por estilos previos.
            $hoja        = $libro->getActiveSheet();
            $filaInicial = 3; // fila 1 = título, fila 2 = encabezados
            $totalCols   = count($columnas);

            foreach ($data as $idx => $fila) {
                $estadoActual = $fila['estado_actual'] ?? '';
                if ($estadoActual === 'Ausencia') {
                    $rgb = 'FFFDE7'; // amarillo muy claro
                } elseif ($estadoActual === 'Vacaciones') {
                    $rgb = 'E3F2FD'; // azul muy claro
                } else {
                    continue;
                }
                $noFila    = $filaInicial + $idx;
                $fillStyle = [
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $rgb],
                    ],
                ];
                for ($colIdx = 1; $colIdx <= $totalCols; $colIdx++) {
                    $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $noFila;
                    $hoja->getStyle($cellRef)->applyFromArray($fillStyle);
                }
            }

            // Descargar Excel
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Pragma: public');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro);
            $writer->save('php://output');

            exit;

        } catch (\Exception $e) {
            error_log('Error en descargarUsuariosExcelCapitalHumano: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            header('Content-Type: text/html; charset=utf-8');
            echo '<div style="font-family: Arial; padding: 20px; text-align: center;">';
            echo '<h2 style="color: #dc3545;">Error al generar Excel</h2>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<a href="javascript:history.back()" style="display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;">Volver</a>';
            echo '</div>';
            exit;
        }
    }

    public function descargarDiasAcumuladosReingresosExcel()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['persona_id'])) {
                header('Content-Type: text/html; charset=utf-8');
                die('Sesion no valida. Por favor inicia sesion nuevamente.');
            }

            $anio = (int) ($_GET['anio'] ?? date('Y'));
            $anioActual = (int) date('Y');
            if ($anio < 2000 || $anio > $anioActual) {
                header('Content-Type: text/html; charset=utf-8');
                die('Ejercicio invalido.');
            }

            $resultado = \Models\CapHum::getDiasAcumuladosReingresos($anio);
            if (empty($resultado['success'])) {
                header('Content-Type: text/html; charset=utf-8');
                die('Error al obtener datos: ' . ($resultado['mensaje'] ?? 'Error desconocido'));
            }

            $payload = $resultado['datos'] ?? [];
            $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];
            if (empty($rows)) {
                header('Content-Type: text/html; charset=utf-8');
                die('No hay plantilla con dias acumulados para el ejercicio seleccionado.');
            }

            $fechaCorte = (string) ($payload['fecha_corte'] ?? '');
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'numero_empleado' => $row['numero_empleado'] ?? '',
                    'nombre_completo' => $row['nombre_completo'] ?? '',
                    'departamento' => $row['departamento'] ?? 'N/A',
                    'puesto' => $row['puesto'] ?? 'N/A',
                    'estatus_actual' => $row['estatus_actual'] ?? '',
                    'fecha_ingreso_inicial' => $row['fecha_ingreso_inicial'] ?? '',
                    'tuvo_reingreso' => $row['tuvo_reingreso'] ?? 'No',
                    'reingresos_historicos' => (int) ($row['reingresos_historicos'] ?? 0),
                    'reingresos_ejercicio' => (int) ($row['reingresos_ejercicio'] ?? 0),
                    'bajas_ejercicio' => (int) ($row['bajas_ejercicio'] ?? 0),
                    'periodos_contabilizados' => (int) ($row['periodos_contabilizados'] ?? 0),
                    'dias_acumulados' => (int) ($row['dias_acumulados'] ?? 0),
                    'fecha_corte' => $fechaCorte,
                    'detalle_periodos' => $row['detalle_periodos'] ?? '',
                ];
            }

            $columnas = [
                \PHPSpreadsheet::ColumnaExcel('numero_empleado', 'NUMERO DE EMPLEADO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_completo', 'NOMBRE COMPLETO'),
                \PHPSpreadsheet::ColumnaExcel('departamento', 'DEPARTAMENTO'),
                \PHPSpreadsheet::ColumnaExcel('puesto', 'PUESTO'),
                \PHPSpreadsheet::ColumnaExcel('estatus_actual', 'ESTATUS ACTUAL'),
                \PHPSpreadsheet::ColumnaExcel('fecha_ingreso_inicial', 'FECHA INGRESO INICIAL'),
                \PHPSpreadsheet::ColumnaExcel('tuvo_reingreso', 'TUVO REINGRESO'),
                \PHPSpreadsheet::ColumnaExcel('reingresos_historicos', 'REINGRESOS HISTORICOS'),
                \PHPSpreadsheet::ColumnaExcel('reingresos_ejercicio', 'REINGRESOS EN EJERCICIO'),
                \PHPSpreadsheet::ColumnaExcel('bajas_ejercicio', 'BAJAS EN EJERCICIO'),
                \PHPSpreadsheet::ColumnaExcel('periodos_contabilizados', 'PERIODOS CONTABILIZADOS'),
                \PHPSpreadsheet::ColumnaExcel('dias_acumulados', 'DIAS ACUMULADOS'),
                \PHPSpreadsheet::ColumnaExcel('fecha_corte', 'FECHA CORTE'),
                \PHPSpreadsheet::ColumnaExcel('detalle_periodos', 'DETALLE PERIODOS'),
            ];

            $nombreArchivo = 'Plantilla_Dias_Acumulados_' . $anio . '_' . date('Y-m-d_His');
            \PHPSpreadsheet::DescargaExcel(
                $nombreArchivo,
                'Plantilla',
                'Plantilla dias acumulados ' . $anio,
                $columnas,
                $data
            );
            exit;
        } catch (\Throwable $e) {
            error_log('Error en descargarDiasAcumuladosReingresosExcel: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            header('Content-Type: text/html; charset=utf-8');
            echo 'Error al generar el archivo Excel: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            exit;
        }
    }

    /**
     * Call Center: dictamen de llamadas.
     * URL canónica: /analitica/callcenter
     * El historial de condonaciones está en Gastos Cobranza → /condonaciones/historial
     */
    public function callcenter()
    {
        if (isset($_GET['seccion']) && (string) $_GET['seccion'] === 'condonaciones') {
            header('Location: /condonaciones/historial', true, 302);
            exit;
        }
        self::set('titulo', 'Call Center');
        self::set('script', '');
        self::render('call_center');
    }

    /**
     * Comparativas avance semanal (Analítica): landing al estilo Call Center.
     * URL canónica: /analitica/comparativas
     * Permiso: modulos_web id 60 (Analítica → Comparativas).
     */
    public function comparativas()
    {
        self::set('titulo', 'Comparativas avance semanal');
        self::set('script', '');
        self::render('comparativas');
    }

    /**
     * Landing Asignación (Analítica), mismo estilo que Comparativas.
     * URL canónica: /analitica/asignacion
     * Permiso: modulos_web id 84 (Direcciones).
     */
    public function asignacion()
    {
        self::set('titulo', 'Actualización de dirección');
        self::set('script', '');
        $cssPath = realpath(__DIR__ . '/../../public/assets/css/reporteria-asignacion-landing.css');
        $cssV = $cssPath ? (int) filemtime($cssPath) : time();
        self::set('css', '<link rel="stylesheet" href="/assets/css/reporteria-asignacion-landing.css?v=' . $cssV . '">');
        self::render('asignacion');
    }

    public function asignacionDirecciones()
    {
        self::set('titulo', 'Actualización de dirección');
        self::set('script', '');
        self::render('asignacion_direcciones');
    }

    private function responderJsonAsignacionDirecciones(array $payload): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function inputJsonAsignacionDirecciones(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw ?: '[]', true);
        return is_array($json) ? $json : ($_POST ?: []);
    }

    public function getAsignacionDireccionesCredito()
    {
        $input = $this->inputJsonAsignacionDirecciones();
        $idCredito = (int)($input['id_credito'] ?? $_GET['id_credito'] ?? 0);
        try {
            $dao = new Direcciones();
            $this->responderJsonAsignacionDirecciones($dao->buscarPorCredito($idCredito));
        } catch (\Exception $e) {
            $this->responderJsonAsignacionDirecciones(['success' => false, 'mensaje' => 'No se pudo consultar direcciones.', 'error' => $e->getMessage()]);
        }
    }

    public function postAsignacionDireccion()
    {
        try {
            $dao = new Direcciones();
            $this->responderJsonAsignacionDirecciones($dao->guardarDireccion($this->inputJsonAsignacionDirecciones()));
        } catch (\Exception $e) {
            $this->responderJsonAsignacionDirecciones(['success' => false, 'mensaje' => 'No se pudo guardar la direccion.', 'error' => $e->getMessage()]);
        }
    }

    public function postAsignacionDireccionesOrden()
    {
        $input = $this->inputJsonAsignacionDirecciones();
        try {
            $dao = new Direcciones();
            $this->responderJsonAsignacionDirecciones($dao->reordenarDirecciones((int)($input['id_credito'] ?? 0), $input['ids'] ?? []));
        } catch (\Exception $e) {
            $this->responderJsonAsignacionDirecciones(['success' => false, 'mensaje' => 'No se pudo actualizar el orden.', 'error' => $e->getMessage()]);
        }
    }

    public function postAsignacionDireccionesSync()
    {
        try {
            $dao = new Direcciones();
            $this->responderJsonAsignacionDirecciones($dao->sincronizarDesdeSegundometro());
        } catch (\Exception $e) {
            $this->responderJsonAsignacionDirecciones(['success' => false, 'mensaje' => 'No se pudo sincronizar con Segundometro.', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Tablero Asignación — Proyección: semana pasada, actual y próxima (martes–lunes).
     * URL canónica: /analitica/asignacionTablero
     * Permiso: modulos_web id 84.
     */
    public function asignacionTablero()
    {
        self::set('titulo', 'Asignación — Tablero Proyección');
        self::set('asg_titulo_tablero', 'Asignación — Tablero Proyección');
        self::set('asg_excel_path', self::BASE_PRIMEROS_PAGOS_ANALITICA . '/descargarAsignacionTableroExcel');
        $cssPath = realpath(__DIR__ . '/../../public/assets/css/reporteria-asignacion-tablero.css');
        $cssV = $cssPath ? (int) filemtime($cssPath) : time();
        self::set('css', '<link rel="stylesheet" href="/assets/css/reporteria-asignacion-tablero.css?v=' . $cssV . '">');
        $cfgJson = json_encode(
            [
                'basePath' => self::BASE_PRIMEROS_PAGOS_ANALITICA . '/asignacionTablero',
                'dosVentanas' => false,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        self::set('script', self::htmlScriptBundleAsignacionTablero($cfgJson));
        self::render('asignacion_tablero');
    }

    /**
     * Tablero Asignación — dos ventanas: mismas columnas 1 y 2 que el tablero de tres (semana pasada real + semana actual; sin columna «próxima»).
     * URL canónica: /analitica/asignacionTableroDos
     * Permiso: modulos_web id 84.
     */
    public function asignacionTableroDos()
    {
        self::set('titulo', 'Asignación — Tablero dos ventanas');
        self::set('asg_titulo_tablero', 'Asignación — Tablero dos ventanas');
        self::set('asg_excel_path', self::BASE_PRIMEROS_PAGOS_ANALITICA . '/descargarAsignacionTableroDosExcel');
        $cssPath = realpath(__DIR__ . '/../../public/assets/css/reporteria-asignacion-tablero.css');
        $cssV = $cssPath ? (int) filemtime($cssPath) : time();
        self::set('css', '<link rel="stylesheet" href="/assets/css/reporteria-asignacion-tablero.css?v=' . $cssV . '">');
        $cfgJson = json_encode(
            [
                'basePath' => self::BASE_PRIMEROS_PAGOS_ANALITICA . '/asignacionTableroDos',
                'dosVentanas' => true,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        self::set('script', self::htmlScriptBundleAsignacionTablero($cfgJson));
        self::render('asignacion_tablero');
    }

    /**
     * JSON del portafolio automático de asignación (continuidad, nuevos y huérfanos).
     * URL: /analitica/getAsignacionTableroJson
     * Columna «semana pasada» (Proyección y dos ventanas): **tbl_segundometro_histo** (Gestor_Asignado + Bucket_Morosidad_Real, SEMANA = label). External ID y puesto se enriquecen desde persona si el nombre coincide. «Actual» y «próxima» siguen por campañas Legacy + tbl_segundometro_semana (bucket).
     * Query opcional: dos_ventanas=1 → misma forma que «Tablero dos ventanas» (2 columnas de semana).
     * Para paginación solo en cliente: mostrar=todas (devuelve todas las filas; la vista tablero ya no pagina en servidor).
     */
    public function getAsignacionTableroJson()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $prevDisplayErrors = @ini_set('display_errors', '0');
        try {
            $mostrar = isset($_GET['mostrar']) ? (string) $_GET['mostrar'] : '';
            $limite = \Models\AsignacionTablero::parseLimiteMostrar($mostrar !== '' ? $mostrar : null, '10');
            $paginaRaw = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
            $payload = \Models\AsignacionTablero::obtenerPortafolioAutomatico();
            if (isset($_GET['dos_ventanas']) && (string) $_GET['dos_ventanas'] === '1') {
                $payload = \Models\AsignacionTablero::portafolioDosVentanasDesdeCompleto($payload);
            }
            $filas = is_array($payload['filas'] ?? null) ? $payload['filas'] : [];
            $total = count($filas);
            if ($limite === null) {
                $paginaTam = $total > 0 ? $total : 1;
                $totalPaginas = 1;
                $pagina = 1;
            } else {
                $paginaTam = (int) $limite;
                $totalPaginas = $paginaTam > 0 ? max(1, (int) ceil($total / $paginaTam)) : 1;
                $pagina = min(max(1, $paginaRaw), $totalPaginas);
            }
            $offset = ($pagina - 1) * $paginaTam;
            $payload['filas'] = $limite === null ? $filas : array_slice($filas, $offset, $paginaTam);
            $payload['paginacion'] = [
                'mostrar' => \Models\AsignacionTablero::limiteMostrarAQuery($limite),
                'total_filas' => $total,
                'mostradas' => count($payload['filas']),
                'pagina' => $pagina,
                'total_paginas' => $totalPaginas,
            ];
            $json = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            if ($json === false) {
                throw new \RuntimeException('json_encode: ' . json_last_error_msg());
            }
            header('Content-Type: application/json; charset=utf-8');
            echo $json;
            exit;
        } catch (\Throwable $e) {
            $trace = $e->getTraceAsString();
            if (strlen($trace) > 4000) {
                $trace = substr($trace, 0, 4000) . '…';
            }
            error_log('Reporteria::getAsignacionTableroJson ' . $e->getFile() . ':' . $e->getLine() . ' -> ' . $e->getMessage() . "\n" . $trace);
            $detail = 'Error al consultar el portafolio automático.';
            if (getenv('SPARTA_LEDGER_DEBUG_API') === '1' || (defined('SPARTA_LEDGER_DEBUG_API') && SPARTA_LEDGER_DEBUG_API === true)) {
                $detail = $e->getMessage();
            }
            $limite = \Models\AsignacionTablero::parseLimiteMostrar('todas', '10');
            $fallback = [
                'semanas' => [],
                'subcols' => \Models\AsignacionTablero::SUBCOLS,
                'filas' => [],
                'resumen' => ['total' => 0, 'continuidad' => 0, 'nuevo' => 0, 'huerfano' => 0, 'sin_jefe' => 0],
                'campanias' => ['actual' => [], 'anterior' => []],
                'paginacion' => [
                    'mostrar' => \Models\AsignacionTablero::limiteMostrarAQuery($limite),
                    'total_filas' => 0,
                    'mostradas' => 0,
                    'pagina' => 1,
                    'total_paginas' => 1,
                ],
                'warning' => $detail,
            ];
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($fallback, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        } finally {
            if ($prevDisplayErrors !== false) {
                @ini_set('display_errors', $prevDisplayErrors);
            }
        }
    }

    /**
     * Excel del tablero Asignación (2 o 3 ventanas según $portafolio['semanas']).
     *
     * @param array<string,mixed> $portafolio
     */
    private function exportarPortafolioAsignacionExcel(array $portafolio, string $tituloFila1, string $prefijoNombreArchivo): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $semanas = is_array($portafolio['semanas'] ?? null) ? $portafolio['semanas'] : [];
            $subcols = is_array($portafolio['subcols'] ?? null) ? $portafolio['subcols'] : \Models\AsignacionTablero::SUBCOLS;
            $filas = is_array($portafolio['filas'] ?? null) ? $portafolio['filas'] : [];
            $numSemanas = count($semanas);
            if ($numSemanas < 1) {
                throw new \RuntimeException('Portafolio sin semanas para exportar.');
            }
            $numSubcols = count($subcols);
            if ($numSemanas === 2 && $filas !== []) {
                $probe = $filas[0]['cells'][0] ?? null;
                if (is_array($probe) && array_key_exists('Bucket_Morosidad_Real', $probe)) {
                    $numSubcols = 4;
                    $subcols = array_merge($subcols, [
                        ['key' => 'Bucket_Morosidad_Real', 'text' => 'Bucket', 'align' => 'text-start'],
                    ]);
                }
            }
            $lastColIdx = 1 + $numSemanas * $numSubcols;
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIdx);
            $mergeBanner = 'A1:' . $lastColLetter . '1';
            $mergeSub = 'A2:' . $lastColLetter . '2';

            $fillPorSemana = static function (array $sem): string {
                $hl = (int) ($sem['hist_level'] ?? 0);
                if ($hl === 3) {
                    return 'D8DADF';
                }
                if ($hl === 2) {
                    return 'E4E6E9';
                }
                if ($hl === 1) {
                    return 'F0F2F4';
                }
                if (($sem['th_class'] ?? '') === 'comp-th-act') {
                    return 'E8F5E9';
                }

                return 'E3F2FD';
            };

            $colorTextoPorSemana = static function (array $sem): string {
                if (($sem['th_class'] ?? '') === 'comp-th-act') {
                    return '146C43';
                }
                if (($sem['th_class'] ?? '') === 'comp-th-fut') {
                    return '055160';
                }

                return '434A54';
            };

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Asignación');

            $bordeFino = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'DEE2E6'],
                    ],
                ],
            ];

            $sheet->setCellValue('A1', $tituloFila1);
            $sheet->mergeCells($mergeBanner);
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '696CFF']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ] + $bordeFino);
            $sheet->getRowDimension(1)->setRowHeight(28);

            $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i') . ' · America/Mexico_City');
            $sheet->mergeCells($mergeSub);
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '697A8D']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ] + $bordeFino);

            $rChips = 3;
            $rSem = 4;
            $rSub = 5;
            $rData = 6;

            $sheet->mergeCells("A{$rChips}:A{$rSub}");
            $sheet->setCellValue("A{$rChips}", 'ID Crédito');
            $sheet->getStyle("A{$rChips}:A{$rSub}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '696CFF']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FC'],
                ],
            ] + $bordeFino);

            foreach ($semanas as $si => $sem) {
                $c0 = $si * $numSubcols + 2;
                $L0 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c0);
                $L2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c0 + $numSubcols - 1);
                $fill = $fillPorSemana($sem);
                $txtColor = $colorTextoPorSemana($sem);

                $mergeChips = "{$L0}{$rChips}:{$L2}{$rChips}";
                $sheet->mergeCells($mergeChips);
                $sheet->setCellValue("{$L0}{$rChips}", (string) ($sem['chip_text'] ?? ''));
                $sheet->getStyle($mergeChips)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $txtColor]],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $fill],
                    ],
                ] + $bordeFino);

                $mergeSem = "{$L0}{$rSem}:{$L2}{$rSem}";
                $sheet->mergeCells($mergeSem);
                $sheet->setCellValue("{$L0}{$rSem}", (string) ($sem['label'] ?? ''));
                $sheet->getStyle($mergeSem)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $txtColor]],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $fill],
                    ],
                ] + $bordeFino);

                foreach ($subcols as $ci => $sub) {
                    $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c0 + $ci);
                    $cel = $colLet . $rSub;
                    $sheet->setCellValue($cel, (string) ($sub['text'] ?? ''));
                    $subFill = $fill;
                    if (($sem['th_class'] ?? '') === 'comp-th-act') {
                        $subFill = 'DCF5E4';
                    } elseif (($sem['th_class'] ?? '') === 'comp-th-fut') {
                        $subFill = 'D3E8F5';
                    }
                    $sheet->getStyle($cel)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $txtColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $subFill],
                        ],
                    ] + $bordeFino);
                }
            }

            $sheet->getRowDimension($rChips)->setRowHeight(42);
            $sheet->getRowDimension($rSem)->setRowHeight(22);
            $sheet->getRowDimension($rSub)->setRowHeight(36);

            $sheet->getColumnDimension('A')->setWidth(14);
            for ($c = 2; $c <= $lastColIdx; $c++) {
                $L = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $sheet->getColumnDimension($L)->setWidth(18);
            }

            $filaExcel = $rData;
            if ($filas === []) {
                $cellsVacias = [];
                for ($i = 0; $i < $numSemanas; $i++) {
                    $vac = ['ext' => '—', 'nom' => '—', 'pue' => '—'];
                    if ($numSubcols >= 4) {
                        $vac['Bucket_Morosidad_Real'] = '—';
                    }
                    $cellsVacias[] = $vac;
                }
                $filas = [[
                    'id_credito' => '—',
                    'cells' => $cellsVacias,
                ]];
            }

            foreach ($filas as $fila) {
                $idCredito = trim((string) ($fila['id_credito'] ?? ''));
                $sheet->setCellValue('A' . $filaExcel, $idCredito !== '' ? $idCredito : '—');
                $sheet->getStyle('A' . $filaExcel)->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '434A54']],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFFFF'],
                    ],
                ] + $bordeFino);

                $cells = is_array($fila['cells'] ?? null) ? $fila['cells'] : [];
                foreach ($semanas as $si => $sem) {
                    $c0 = $si * $numSubcols + 2;
                    $fill = $fillPorSemana($sem);
                    if (($sem['th_class'] ?? '') === 'comp-th-act') {
                        $fill = 'F1FBF4';
                    } elseif (($sem['th_class'] ?? '') === 'comp-th-fut') {
                        $fill = 'F0F8FC';
                    }

                    $cellSem = is_array($cells[$si] ?? null) ? $cells[$si] : [];
                    $vals = [
                        trim((string) ($cellSem['ext'] ?? '')) ?: '—',
                        trim((string) ($cellSem['nom'] ?? '')) ?: '—',
                        trim((string) ($cellSem['pue'] ?? '')) ?: '—',
                    ];
                    if ($numSubcols >= 4) {
                        $vals[] = trim((string) ($cellSem['Bucket_Morosidad_Real'] ?? '')) ?: '—';
                    }
                    foreach ($vals as $ci => $val) {
                        $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c0 + $ci);
                        $cel = $colLet . $filaExcel;
                        $sheet->setCellValue($cel, $val);
                        $sheet->getStyle($cel)->applyFromArray([
                            'font' => ['size' => 10, 'color' => ['rgb' => '434A54']],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $fill],
                            ],
                        ] + $bordeFino);
                    }
                }

                $sheet->getRowDimension($filaExcel)->setRowHeight(22);
                $filaExcel++;
            }

            $sheet->freezePane('B' . $rData);
            $sheet->setSelectedCells('A1');

            $nombre = $prefijoNombreArchivo . date('Ymd_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombre . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Throwable $e) {
            error_log('Reporteria::exportarPortafolioAsignacionExcel -> ' . $e->getMessage());
            header('HTTP/1.0 500 Internal Server Error');
            header('Content-Type: text/plain; charset=utf-8');
            echo 'No se pudo generar el Excel. Intente de nuevo o contacte a sistemas.';
            exit;
        }
    }

    /**
     * Excel del tablero Asignación: encabezados con colores (semana pasada / actual / próxima) como en pantalla.
     * URL: /analitica/descargarAsignacionTableroExcel · Módulo 84.
     * Siempre exporta el portafolio completo (independiente del «Mostrar» de la vista).
     */
    public function descargarAsignacionTableroExcel()
    {
        $portafolio = \Models\AsignacionTablero::obtenerPortafolioAutomatico();
        $this->exportarPortafolioAsignacionExcel(
            $portafolio,
            'Asignación — Tablero Proyección (semana pasada · actual · próxima, martes a lunes)',
            'Asignacion_Tablero_'
        );
    }

    /**
     * Excel del tablero en dos ventanas (semana pasada + semana actual, sin proyección próxima).
     * URL: /analitica/descargarAsignacionTableroDosExcel · Módulo 84.
     */
    public function descargarAsignacionTableroDosExcel()
    {
        $portafolio = \Models\AsignacionTablero::portafolioDosVentanasDesdeCompleto(
            \Models\AsignacionTablero::obtenerPortafolioAutomatico()
        );
        $this->exportarPortafolioAsignacionExcel(
            $portafolio,
            'Asignación — Tablero dos ventanas (semana pasada + actual, martes a lunes)',
            'Asignacion_Tablero_DosVentanas_'
        );
    }

    /**
     * Tablero comparativo segundómetro (cortes del día vs semanas históricas).
     * URL canónica: /analitica/comparativasAvanceSemanal
     */
    public function comparativasAvanceSemanal()
    {
        self::set('titulo', 'Comparativas — Avance por cortes');
        $hoyMxGet = isset($_GET['hoy_mx']) ? trim((string) $_GET['hoy_mx']) : '';
        self::set('comp_placeholder_cdmx', $hoyMxGet === '');

        $compPayload = null;
        $compError = null;
        $compFechaMin = '';
        $compFechaMax = '';

        try {
            $fechaGet = isset($_GET['fecha']) ? (string) $_GET['fecha'] : null;
            if ($hoyMxGet !== '') {
                $compPayload = SegundometroComparativaSemanal::calcular($fechaGet, $hoyMxGet);
                $compFechaMin = (string) ($compPayload['fecha_min'] ?? '');
                $compFechaMax = (string) ($compPayload['fecha_max'] ?? '');
            }
        } catch (\InvalidArgumentException $e) {
            $compError = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('Reporteria::comparativasAvanceSemanal -> ' . $e->getMessage());
            $compError = 'No se pudieron cargar los datos. Intente más tarde o use Actualizar.';
        }

        $cErrStr = ($compError !== null && $compError !== '') ? (string) $compError : '';
        $compOkInicio = ($cErrStr === '' && is_array($compPayload));
        $compEsperandoHoyMx = ($hoyMxGet === '') && ($cErrStr === '');
        $compUiDatos = $compOkInicio || $compEsperandoHoyMx;

        $initialJson = json_encode($compPayload, JSON_UNESCAPED_UNICODE);
        if ($initialJson === false || $compPayload === null) {
            $initialJson = 'null';
        }

        $minEsc = htmlspecialchars($compFechaMin, ENT_QUOTES, 'UTF-8');
        $maxEsc = htmlspecialchars($compFechaMax, ENT_QUOTES, 'UTF-8');
        $rangoJson = json_encode(['min' => $minEsc, 'max' => $maxEsc], JSON_UNESCAPED_UNICODE);
        if ($rangoJson === false) {
            $rangoJson = '{}';
        }

        self::set('comp_error', $compError);
        self::set('comp_payload', $compPayload);
        self::set('comp_fecha_min', $compFechaMin);
        self::set('comp_fecha_max', $compFechaMax);
        self::set('comp_ok_inicio', $compOkInicio);
        self::set('comp_esperando_hoy_mx', $compEsperandoHoyMx);
        self::set('comp_ui_datos', $compUiDatos);
        self::set('comp_initial_json', $initialJson);
        self::set('comp_rango_json', $rangoJson);
        self::set('script', '');
        self::render('comparativas_avance_semanal');
    }

    /**
     * JSON para refresco del tablero (misma forma que el antiguo endpoint FastAPI).
     */
    public function getComparativasAvanceSemanalJson()
    {
        try {
            $fecha = isset($_GET['fecha']) ? (string) $_GET['fecha'] : null;
            $hoyMx = isset($_GET['hoy_mx']) ? trim((string) $_GET['hoy_mx']) : null;
            self::respuestaJSON(SegundometroComparativaSemanal::calcular($fecha, $hoyMx));
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            self::respuestaJSON(['detail' => $e->getMessage()]);
        } catch (\Throwable $e) {
            error_log('Reporteria::getComparativasAvanceSemanalJson -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['detail' => 'Error al consultar la base de datos.']);
        }
    }

    /**
     * Comparativo dinamico: cierre actual vs semana pasada por bucket, creditos y saldo capital.
     * URL canonica: /analitica/comparativoCierreSemanal
     */
    public function comparativoCierreSemanal()
    {
        self::set('titulo', 'Comparativo');
        $payload = null;
        $error = null;

        try {
            $corte = isset($_GET['corte']) ? (string) $_GET['corte'] : null;
            $modoConciliacion = isset($_GET['modo_conciliacion']) ? (string) $_GET['modo_conciliacion'] : null;
            $payload = ComparativoCierreSemanal::calcular($corte, $modoConciliacion);
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('Reporteria::comparativoCierreSemanal -> ' . $e->getMessage());
            $error = 'No se pudieron cargar los datos del comparativo.';
        }

        $initialJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($initialJson === false || $payload === null) {
            $initialJson = 'null';
        }

        self::set('comparativo_payload', $payload);
        self::set('comparativo_error', $error);
        self::set('comparativo_initial_json', $initialJson);
        self::set('script', '');
        self::render('comparativo_cierre_semanal');
    }

    /**
     * JSON para refrescar el comparativo dinamico.
     */
    public function getComparativoCierreSemanalJson()
    {
        try {
            $corte = isset($_GET['corte']) ? (string) $_GET['corte'] : null;
            $modoConciliacion = isset($_GET['modo_conciliacion']) ? (string) $_GET['modo_conciliacion'] : null;
            self::respuestaJSON(ComparativoCierreSemanal::calcular($corte, $modoConciliacion));
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        } catch (\Throwable $e) {
            error_log('Reporteria::getComparativoCierreSemanalJson -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al consultar la base de datos.']);
        }
    }

    /**
     * Alias antiguo → redirige a callcenter (conserva query string).
     */
    /**
     * Avance Bucket: matriz Bucket_Morosidad_Real vs Cierre_Ajustado2.
     * URL canonica: /analitica/avanceBucket
     */
    public function avanceBucket($vistaRuta = null)
    {
        self::set('titulo', 'Avance Bucket');
        $payload = null;
        $error = null;
        $vista = strtolower(trim((string) ($vistaRuta ?? ($_GET['vista'] ?? ''))));
        $mostrarAvance = in_array($vista, ['avance', 'historico'], true);

        if ($vistaRuta === null && isset($_GET['vista']) && $mostrarAvance) {
            header('Location: /analitica/avanceBucket/' . $vista, true, 302);
            exit;
        }

        if ($mostrarAvance) {
            try {
                $corte = isset($_GET['corte']) ? (string) $_GET['corte'] : null;
                if ($vista === 'historico') {
                    $semana = isset($_GET['semana']) ? (string) $_GET['semana'] : null;
                    $payload = AvanceBucket::calcularHistorico($semana, $corte);
                } else {
                    $payload = AvanceBucket::calcular($corte);
                }
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (\Throwable $e) {
                error_log('Reporteria::avanceBucket -> ' . $e->getMessage());
                $error = 'No se pudieron cargar los datos de Avance Bucket.';
            }
        }

        $initialJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($initialJson === false || $payload === null) {
            $initialJson = 'null';
        }

        self::set('avance_bucket_payload', $payload);
        self::set('avance_bucket_error', $error);
        self::set('avance_bucket_initial_json', $initialJson);
        self::set('avance_bucket_vista', $vista);
        self::set('script', '');
        self::render('avance_bucket');
    }

    public function getAvanceBucketJson()
    {
        try {
            $corte = isset($_GET['corte']) ? (string) $_GET['corte'] : null;
            self::respuestaJSON(AvanceBucket::calcular($corte));
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        } catch (\Throwable $e) {
            error_log('Reporteria::getAvanceBucketJson -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al consultar la base de datos.']);
        }
    }

    public function getAvanceBucketHistoricoJson()
    {
        try {
            $semana = isset($_GET['semana']) ? (string) $_GET['semana'] : null;
            $corte = isset($_GET['corte']) ? (string) $_GET['corte'] : null;
            self::respuestaJSON(AvanceBucket::calcularHistorico($semana, $corte));
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        } catch (\Throwable $e) {
            error_log('Reporteria::getAvanceBucketHistoricoJson -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al consultar la base de datos historica.']);
        }
    }

    public function resumencallcenter()
    {
        $q = isset($_SERVER['QUERY_STRING']) && (string)$_SERVER['QUERY_STRING'] !== ''
            ? '?' . $_SERVER['QUERY_STRING']
            : '';
        header('Location: ' . self::BASE_PRIMEROS_PAGOS_ANALITICA . '/callcenter' . $q, true, 302);
        exit;
    }

    public function layoutlegacy()
    {
        $script = <<<'HTML'
            <script>
            document.getElementById('btn-ultimo-corte').addEventListener('click', function(e) {
                e.preventDefault();

                // Mostrar SweetAlert de carga
                Swal.fire({
                    title: 'Preparando Reporte Legacy...',
                    text: 'Por favor espera mientras se cargan los datos',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                // Simular una pequeña validación o carga de datos
                // En este caso, vamos directo a la confirmación después de un breve delay
                setTimeout(() => {
                    Swal.close();

                    // Modal de confirmación elegante
                    Swal.fire({
                        html: `
                            <p style="margin-bottom: 1rem; font-size: 1.1rem;">El reporte de usuarios Legacy está listo para descargar.</p>
                            <p style="margin-bottom: 1.5rem; color: #697a8d;">Contiene toda la información actualizada de usuarios, roles y jerarquías organizacionales.</p>
                            <p style="margin-top: 1.5rem;"><strong>¿Deseas descargarlo ahora?</strong></p>
                        `,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bx bx-download me-2"></i>Sí, descargar',
                        cancelButtonText: 'No, cancelar',
                        confirmButtonColor: '#696cff',
                        cancelButtonColor: '#a1acb8',
                        reverseButtons: true,
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-label-secondary'
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            // Mostrar modal de generación
                            Swal.fire({
                                title: 'Generando archivo Excel...',
                                text: 'Por favor espera mientras se genera el reporte Legacy',
                                icon: 'info',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => Swal.showLoading()
                            });

                            // Enviar el formulario para descargar
                            document.getElementById('form-descarga').submit();

                            // Cerrar SweetAlert automáticamente después de 2-3 segundos
                            // cuando la descarga ya debería haber comenzado
                            setTimeout(() => {
                                Swal.close();
                            }, 2500);
                        }
                    });
                }, 800); // Pequeño delay para mostrar el modal de carga
            });
            </script>
        HTML;

        self::set("titulo", "Layout Legacy");
        self::set("script", $script);
        self::render("layout_legacy");
    }

    public function getUltimoCorte()
    {
        self::respuestaJSON(EmpresasDAO::getObtenerUltimoCorte());
    }

    public function ProcesarDescargarCorte()
    {
        // Aumentar tiempo de ejecución a 5 minutos
        set_time_limit(300);

        // Aumentar memoria si es necesario
        ini_set('memory_limit', '512M');

        // Limpiar buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Obtener corte por GET
        $corte = $_GET['columna'] ?? null;
        if (!$corte) {
            echo "No se recibió el nombre del corte.";
            return;
        }

        $r = EmpresasDAO::descargarCorte($corte);

        if (!$r['success'] || empty($r['datos'])) {
            echo "No se pudieron obtener los datos del corte.";
            return;
        }

        $data = $r['datos'];

        // Columnas alineadas al resultado del reporte de corte (EmpresasDAO)
        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('id_original', 'ID ORIGINAL'),
            \PHPSpreadsheet::ColumnaExcel('Telefono', 'TELEFONO'),
            \PHPSpreadsheet::ColumnaExcel('fideicomiso', 'FIDEICOMISO'),
            \PHPSpreadsheet::ColumnaExcel('mkm', 'MKM'),
            \PHPSpreadsheet::ColumnaExcel('id_credit', 'ID CREDITO'),
            \PHPSpreadsheet::ColumnaExcel('nombre', 'NOMBRE'),
            \PHPSpreadsheet::ColumnaExcel('pagos_vencidos', 'PAGOS VENCIDOS'),
            \PHPSpreadsheet::ColumnaExcel('monto_vencido', 'MONTO VENCIDO', ['estilo' => \PHPSpreadsheet::GetEstilosExcel('moneda')]),
            \PHPSpreadsheet::ColumnaExcel('bucket', 'BUCKET'),
            \PHPSpreadsheet::ColumnaExcel('fecha_de_pago', 'FECHA DE PAGO'),
            \PHPSpreadsheet::ColumnaExcel('telefono_1', 'TELEFONO 1'),
            \PHPSpreadsheet::ColumnaExcel('tipoo_de_pago', 'TIPO DE PAGO'),
            \PHPSpreadsheet::ColumnaExcel('clabe', 'CLABE'),
            \PHPSpreadsheet::ColumnaExcel('banco', 'BANCO'),
            \PHPSpreadsheet::ColumnaExcel('atributo_segmento', 'ATRIBUTO SEGMENTO'),
            \PHPSpreadsheet::ColumnaExcel('nombre_completo', 'NOMBRE COMPLETO'),
            \PHPSpreadsheet::ColumnaExcel('nombre_completo_referencia1', 'NOMBRE REFERENCIA 1'),
            \PHPSpreadsheet::ColumnaExcel('telefono_referencia1', 'TELEFONO REFERENCIA 1'),
            \PHPSpreadsheet::ColumnaExcel('nombre_completo_referencia2', 'NOMBRE REFERENCIA 2'),
            \PHPSpreadsheet::ColumnaExcel('telefono_referencia2', 'TELEFONO REFERENCIA 2'),
            \PHPSpreadsheet::ColumnaExcel('nombre_referencia_3', 'NOMBRE REFERENCIA 3'),
            \PHPSpreadsheet::ColumnaExcel('telefono_referencia_3', 'TELEFONO REFERENCIA 3'),
            \PHPSpreadsheet::ColumnaExcel('Motivo_de_no_Pago', 'MOTIVO DE NO PAGO'),
            \PHPSpreadsheet::ColumnaExcel('cuando_le_pagan', 'CUANDO LE PAGAN'),
            \PHPSpreadsheet::ColumnaExcel('Giro_de_Trabajo', 'GIRO DE TRABAJO'),
            \PHPSpreadsheet::ColumnaExcel('hora_de_pago', 'HORA DE PAGO')
        ];

        // Descargar Excel directamente
        \PHPSpreadsheet::DescargaExcel(
            "Corte_{$corte}",
            "Datos Corte",
            "Corte",
            $columnas,
            $data
        );

        // Terminar ejecución para que no se agregue nada extra
        exit;
    }

    public function ProcesarDescargarLegacy()
    {
        // 1. Limpiar el buffer para evitar que cualquier eco previo rompa el Excel
        while (ob_get_level()) {
            ob_end_clean();
        }
        $r = EmpresasDAO::descargarReporteLegacy();

        // 2. Si hay error, redirigir en lugar de hacer echo (que corrompe el Excel)
        if (!$r['success'] || empty($r['datos'])) {
            header('Location: ' . self::BASE_PRIMEROS_PAGOS_ANALITICA . '/layoutlegacy?error=' . urlencode('No se pudieron obtener los datos del reporte Legacy.'));
            exit;
        }

        $data = $r['datos'];

        // Columnas alineadas al resultado del reporte Legacy (EmpresasDAO)
        // Columnas que coinciden con los datos de la consulta SQL
        // IMPORTANTE: El primer parámetro es el CAMPO (clave del array), el segundo es el TÍTULO
        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('external_id', 'ID EXTERNO'),
            \PHPSpreadsheet::ColumnaExcel('username', 'USUARIO'),
            \PHPSpreadsheet::ColumnaExcel('name', 'NOMBRE COMPLETO'),
            \PHPSpreadsheet::ColumnaExcel('password', 'CONTRASEÑA'),
            \PHPSpreadsheet::ColumnaExcel('legion', 'LEGION'),
            \PHPSpreadsheet::ColumnaExcel('role', 'ROL'),
            \PHPSpreadsheet::ColumnaExcel('color', 'COLOR'),
            \PHPSpreadsheet::ColumnaExcel('supervisor_id', 'ID SUPERVISOR'),
            \PHPSpreadsheet::ColumnaExcel('supervisor_nombre', 'NOMBRE SUPERVISOR'),
            \PHPSpreadsheet::ColumnaExcel('subgerente_id', 'ID SUBGERENTE'),
            \PHPSpreadsheet::ColumnaExcel('subgerente_nombre', 'NOMBRE SUBGERENTE'),
            \PHPSpreadsheet::ColumnaExcel('gerente_id', 'ID GERENTE'),
            \PHPSpreadsheet::ColumnaExcel('gerente_nombre', 'NOMBRE GERENTE'),
            \PHPSpreadsheet::ColumnaExcel('subdirector_id', 'ID SUBDIRECTOR'),
            \PHPSpreadsheet::ColumnaExcel('subdirector_nombre', 'NOMBRE SUBDIRECTOR'),
            \PHPSpreadsheet::ColumnaExcel('city', 'CIUDAD'),
            \PHPSpreadsheet::ColumnaExcel('state', 'ESTADO'),
            \PHPSpreadsheet::ColumnaExcel('municipality', 'MUNICIPIO'),
            \PHPSpreadsheet::ColumnaExcel('settlement_tupe', 'TIPO ASENTAMIENTO'),
            \PHPSpreadsheet::ColumnaExcel('postal_code', 'CODIGO POSTAL')
        ];

        // Descargar Excel directamente
        \PHPSpreadsheet::DescargaExcel(
            "Reporte_Legacy_" . date('Y-m-d'),
            "Datos Legacy",
            "Legacy",
            $columnas,
            $data
        );

        // Terminar ejecución para que no se agregue nada extra
        exit;
    }

    public function descargarPlantillaGestores()
    {
        // Aumentar tiempo de ejecución
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        // Limpiar buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            // Obtener filtros de GET
            $modulosSesion = array_map('intval', (array)($_SESSION['modulos'] ?? []));
            if (!in_array(94, $modulosSesion, true)) {
                http_response_code(403);
                die('No tienes permiso para descargar la plantilla de gestores.');
            }
            $tokenPlantilla = (string)($_REQUEST['plantilla_token'] ?? '');
            $tokensPlantilla = $_SESSION['rrhh_plantilla_gestores_tokens'] ?? [];
            if (!is_array($tokensPlantilla)) {
                $tokensPlantilla = [];
            }
            foreach ($tokensPlantilla as $token => $expira) {
                if ((int)$expira < time()) {
                    unset($tokensPlantilla[$token]);
                }
            }
            $tokenValido = $tokenPlantilla !== ''
                && isset($tokensPlantilla[$tokenPlantilla])
                && (int)$tokensPlantilla[$tokenPlantilla] >= time();
            if (!$tokenValido) {
                http_response_code(403);
                die('Confirma la descarga con Google Authenticator antes de generar la plantilla.');
            }
            unset($tokensPlantilla[$tokenPlantilla]);
            $_SESSION['rrhh_plantilla_gestores_tokens'] = $tokensPlantilla;

            $filtroDepartamento = $_REQUEST['departamento'] ?? '';
            $filtroPuesto = $_REQUEST['puesto'] ?? '';
            $filtroEstatus = $_REQUEST['estatus'] ?? '';
            $columnasSolicitadas = $_REQUEST['columnas'] ?? [];
            if (!is_array($columnasSolicitadas)) {
                $columnasSolicitadas = [];
            }
            $columnasSolicitadas = array_values(array_unique(array_filter(array_map(static function ($valor) {
                return preg_replace('/[^a-z0-9_]+/i', '', (string)$valor);
            }, $columnasSolicitadas))));

            $columnasDisponibles = [
                'numero_empleado' => 'NO. EMPLEADO',
                'codigo_contpac' => 'CODIGO CONTPAQ',
                'nombre_completo' => 'NOMBRE COMPLETO',
                'nombres' => 'NOMBRES',
                'segundo_nombre' => 'SEGUNDO NOMBRE',
                'apellidop' => 'APELLIDO PATERNO',
                'apellidom' => 'APELLIDO MATERNO',
                'usuario' => 'USUARIO',
                'correo' => 'CORREO',
                'telefonos' => 'TELEFONOS',
                'curp' => 'CURP',
                'rfc' => 'RFC',
                'nss' => 'NSS',
                'fecha_nacimiento' => 'FECHA NACIMIENTO',
                'sexo' => 'SEXO',
                'fecha_ingreso' => 'FECHA INGRESO',
                'fecha_registro' => 'FECHA REGISTRO',
                'registro_patronal' => 'REGISTRO PATRONAL',
                'codigo_contpaq_rrhh' => 'CODIGO CONTPAQ RRHH',
                'fecha_contpaq' => 'FECHA CONTPAQ',
                'fecha_imss_alta' => 'FECHA IMSS ALTA',
                'direccion_organizacional' => 'DIRECCION',
                'area_texto' => 'AREA',
                'nombre_departamento' => 'DEPARTAMENTO',
                'nombre_puesto' => 'PUESTO',
                'nombre_jefe' => 'JEFE INMEDIATO',
                'ubicacion_laboral' => 'UBICACION LABORAL',
                'municipio_laboral' => 'MUNICIPIO LABORAL',
                'domicilio' => 'DOMICILIO',
                'codigo_postal' => 'CODIGO POSTAL',
                'nombre_pais' => 'PAIS',
                'estatus' => 'ESTATUS',
                'salario_sensible' => 'SALARIO SENSIBLE',
            ];
            $columnasSeleccionadas = array_values(array_filter($columnasSolicitadas, static function ($columna) use ($columnasDisponibles) {
                return array_key_exists($columna, $columnasDisponibles);
            }));
            if (empty($columnasSeleccionadas)) {
                $columnasSeleccionadas = ['numero_empleado', 'nombre_completo', 'nombre_departamento', 'nombre_puesto', 'nombre_jefe', 'estatus'];
            }
            $incluyeSalario = in_array('salario_sensible', $columnasSeleccionadas, true);
            if ($incluyeSalario && !in_array(153, $modulosSesion, true)) {
                http_response_code(403);
                die('No tienes permiso especial para exportar salario sensible.');
            }

            // Obtener datos usando el mismo método que getUsuarios
            $tieneDepartamento = in_array(10, $_SESSION['modulos'] ?? []);
            $resultado = \Models\CapHum::getConsultaGestoresAll($_SESSION['usuario_id'], $tieneDepartamento);

            if (!$resultado['success'] || empty($resultado['datos'])) {
                die("No se pudieron obtener los datos de los gestores.");
            }

            $datos = $resultado['datos'];

            // Aplicar filtros si existen
            $datosFiltrados = array_filter($datos, function($gestor) use ($filtroDepartamento, $filtroPuesto, $filtroEstatus) {
                $cumpleDepartamento = empty($filtroDepartamento) || ($gestor['nombre_departamento'] ?? '') === $filtroDepartamento;
                $cumplePuesto = empty($filtroPuesto) || ($gestor['nombre_puesto'] ?? '') === $filtroPuesto;
                $cumpleEstatus = empty($filtroEstatus) || ($gestor['estatus'] ?? '') === $filtroEstatus;

                return $cumpleDepartamento && $cumplePuesto && $cumpleEstatus;
            });

            // Validar que haya datos después del filtro
            if (empty($datosFiltrados)) {
                die("No se encontraron datos con los filtros aplicados.");
            }

            $columnas = [];
            foreach ($columnasSeleccionadas as $columna) {
                $columnas[] = \PHPSpreadsheet::ColumnaExcel($columna, $columnasDisponibles[$columna]);
            }

            $idsPersonas = array_values(array_unique(array_filter(array_map(static function ($gestor) {
                return (int)($gestor['id'] ?? 0);
            }, $datosFiltrados))));
            $extrasPorPersona = [];
            if (!empty($idsPersonas)) {
                $db = new \Core\Database();
                \Models\CapHumRrhh::asegurarTablas($db);
                $paramsIds = [];
                $placeholders = [];
                foreach ($idsPersonas as $idx => $idPersona) {
                    $key = 'id' . $idx;
                    $placeholders[] = ':' . $key;
                    $paramsIds[$key] = $idPersona;
                }
                $extras = $db->queryAll("
                    SELECT
                        p.id,
                        COALESCE(p.correo, '') AS correo,
                        COALESCE(p.telefono_uno, '') AS telefono_uno,
                        COALESCE(p.telefono_dos, '') AS telefono_dos,
                        COALESCE(p.domicilio_calle_texto, '') AS domicilio_persona,
                        COALESCE(p.codigo_postal, '') AS codigo_postal_persona,
                        COALESCE(p.curp, '') AS curp_persona,
                        COALESCE(r.rfc, '') AS rfc,
                        COALESCE(r.nss, '') AS nss,
                        COALESCE(r.fecha_nacimiento, '') AS fecha_nacimiento,
                        COALESCE(r.sexo, '') AS sexo,
                        COALESCE(r.registro_patronal, '') AS registro_patronal,
                        COALESCE(r.codigo_contpaq, '') AS codigo_contpaq_rrhh,
                        COALESCE(r.fecha_contpaq, '') AS fecha_contpaq,
                        COALESCE(r.fecha_imss_alta, '') AS fecha_imss_alta,
                        COALESCE(r.direccion_organizacional, '') AS direccion_organizacional,
                        COALESCE(r.area_texto, '') AS area_texto,
                        COALESCE(r.ubicacion_laboral, '') AS ubicacion_laboral,
                        COALESCE(r.municipio_laboral, '') AS municipio_laboral,
                        COALESCE(r.jefe_directo_texto, '') AS jefe_directo_texto,
                        COALESCE(tel.telefonos, '') AS telefonos_extra,
                        COALESCE(dom.domicilio, '') AS domicilio_extra,
                        COALESCE(dom.codigo_postal, '') AS codigo_postal_extra
                    FROM __SPARTA_SECRET_REDACTED__.persona p
                    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh r ON r.id_persona = p.id
                    LEFT JOIN (
                        SELECT id_persona, GROUP_CONCAT(numero ORDER BY id ASC SEPARATOR ', ') AS telefonos
                        FROM __SPARTA_SECRET_REDACTED__.telefonos_persona
                        WHERE estatus = 'Activo'
                        GROUP BY id_persona
                    ) tel ON tel.id_persona = p.id
                    LEFT JOIN (
                        SELECT id_persona,
                               SUBSTRING_INDEX(GROUP_CONCAT(domicilio_texto ORDER BY id ASC SEPARATOR '||'), '||', 1) AS domicilio,
                               SUBSTRING_INDEX(GROUP_CONCAT(codigo_postal ORDER BY id ASC SEPARATOR '||'), '||', 1) AS codigo_postal
                        FROM __SPARTA_SECRET_REDACTED__.domicilio_persona
                        WHERE estatus = 'Activo'
                        GROUP BY id_persona
                    ) dom ON dom.id_persona = p.id
                    WHERE p.id IN (" . implode(',', $placeholders) . ")
                ", $paramsIds);
                foreach ($extras as $extra) {
                    $extrasPorPersona[(int)($extra['id'] ?? 0)] = $extra;
                }
            }

            // Preparar datos formateados
            $datosFormateados = [];
            foreach ($datosFiltrados as $gestor) {
                $idPersona = (int)($gestor['id'] ?? 0);
                $extra = $extrasPorPersona[$idPersona] ?? [];
                $nombreCompleto = trim(implode(' ', array_filter([
                    $gestor['nombres'] ?? '',
                    $gestor['segundo_nombre'] ?? '',
                    $gestor['apellidop'] ?? '',
                    $gestor['apellidom'] ?? '',
                ], static fn($valor) => trim((string)$valor) !== '')));
                $telefonos = trim((string)($extra['telefonos_extra'] ?? ''));
                if ($telefonos === '') {
                    $telefonos = trim(implode(', ', array_filter([
                        $extra['telefono_uno'] ?? '',
                        $extra['telefono_dos'] ?? '',
                    ], static fn($valor) => trim((string)$valor) !== '')));
                }
                $domicilio = trim((string)($extra['domicilio_extra'] ?? '')) ?: trim((string)($extra['domicilio_persona'] ?? ''));
                $codigoPostal = trim((string)($extra['codigo_postal_extra'] ?? '')) ?: trim((string)($extra['codigo_postal_persona'] ?? ''));
                $salario = '';
                if ($incluyeSalario && $idPersona > 0) {
                    $resSalario = \Models\CapHum::getSalarioSensiblePersona($idPersona);
                    if (!empty($resSalario['success']) && !empty($resSalario['datos']['tiene_salario'])) {
                        $salario = $resSalario['datos']['salario'] ?? '';
                    }
                }

                $filaCompleta = [
                    'numero_empleado' => $gestor['numero_empleado'] ?? '',
                    'codigo_contpac' => $gestor['codigo_contpac'] ?? '',
                    'nombre_completo' => $nombreCompleto,
                    'nombres' => $gestor['nombres'] ?? '',
                    'segundo_nombre' => $gestor['segundo_nombre'] ?? '',
                    'apellidop' => $gestor['apellidop'] ?? '',
                    'apellidom' => $gestor['apellidom'] ?? '',
                    'usuario' => $gestor['usuario'] ?? '',
                    'correo' => $extra['correo'] ?? '',
                    'telefonos' => $telefonos,
                    'curp' => $extra['curp_persona'] ?? '',
                    'rfc' => $extra['rfc'] ?? '',
                    'nss' => $extra['nss'] ?? '',
                    'fecha_nacimiento' => $extra['fecha_nacimiento'] ?? '',
                    'sexo' => $extra['sexo'] ?? '',
                    'fecha_ingreso' => $gestor['fecha_ingreso'] ?? '',
                    'fecha_registro' => $gestor['fecha_registro'] ?? '',
                    'registro_patronal' => $extra['registro_patronal'] ?? '',
                    'codigo_contpaq_rrhh' => $extra['codigo_contpaq_rrhh'] ?? '',
                    'fecha_contpaq' => $extra['fecha_contpaq'] ?? '',
                    'fecha_imss_alta' => $extra['fecha_imss_alta'] ?? '',
                    'direccion_organizacional' => $extra['direccion_organizacional'] ?? '',
                    'area_texto' => $extra['area_texto'] ?? '',
                    'nombre_departamento' => $gestor['nombre_departamento'] ?? '',
                    'nombre_puesto' => $gestor['nombre_puesto'] ?? '',
                    'nombre_jefe' => ($extra['jefe_directo_texto'] ?? '') ?: ($gestor['nombre_jefe'] ?? ''),
                    'ubicacion_laboral' => $extra['ubicacion_laboral'] ?? '',
                    'municipio_laboral' => $extra['municipio_laboral'] ?? '',
                    'domicilio' => $domicilio,
                    'codigo_postal' => $codigoPostal,
                    'nombre_pais' => $gestor['nombre_pais'] ?? '',
                    'estatus' => $gestor['estatus'] ?? '',
                    'salario_sensible' => $salario,
                ];
                $filaSeleccionada = [];
                foreach ($columnasSeleccionadas as $columna) {
                    $filaSeleccionada[$columna] = $filaCompleta[$columna] ?? '';
                }
                $datosFormateados[] = $filaSeleccionada;
            }

            \Models\CapHum::registrarAuditoriaSalarioSensibleRrhh([
                'id_usuario' => (int)($_SESSION['usuario_id'] ?? 0),
                'usuario_nombre' => (string)($_SESSION['usuario_nombre'] ?? $_SESSION['nombre_usuario'] ?? $_SESSION['usuario'] ?? ''),
                'id_persona' => 0,
                'persona_nombre' => 'Plantilla de gestores',
                'accion' => 'descargar_plantilla',
                'resultado' => 'autorizado',
                'ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
                'user_agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'detalle' => 'Columnas: ' . implode(', ', $columnasSeleccionadas),
                'fecha_hora' => date('Y-m-d H:i:s'),
            ]);

            // Nombre del archivo con timestamp y filtros aplicados
            $fechaActual = date('Y-m-d_His');
            $nombreArchivo = "Plantilla_Gestores";

            // Agregar filtros al nombre del archivo
            if ($filtroDepartamento) {
                $nombreArchivo .= "_" . str_replace(' ', '_', $filtroDepartamento);
            }
            if ($filtroPuesto) {
                $nombreArchivo .= "_" . str_replace(' ', '_', $filtroPuesto);
            }
            if ($filtroEstatus) {
                $nombreArchivo .= "_" . $filtroEstatus;
            }

            $nombreArchivo .= "_{$fechaActual}";

            // Descargar Excel
            \PHPSpreadsheet::DescargaExcel(
                $nombreArchivo,
                "Plantilla de Gestores",
                "Gestores",
                $columnas,
                $datosFormateados
            );

            exit;

        } catch (\Exception $e) {
            error_log('Error en descargarPlantillaGestores: ' . $e->getMessage());
            die('Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Vista: Reportes del módulo Sabuesos (Tickets, Panel Admin, Cerrado/Eliminado)
     */
    public function sabuesos()
    {
        $script = "";
        self::set("titulo", "Reportes - Sabuesos");
        self::set("script", $script);
        self::render("reporteria_sabuesos");
    }

    /**
     * Rastreo: vista panel Sabueso solo consulta crédito. Acceso principal desde Estado de cuenta (botón + iframe).
     * Autorización: módulos 18, 27 o 29 (ver getRutasModulos).
     */
    public function consultaIdCredito()
    {
        (new Sabueso())->paneladmin(true);
    }

    /**
     * Alias histórico: misma acción que consultaIdCredito.
     */
    public function consultaCreditoRastreo()
    {
        $this->consultaIdCredito();
    }

    /**
     * ===== REPORTES SABUESOS (Fase 2) =====
     * 1️⃣ REPORTE 1: Tickets (solo del usuario actual)
     */
  public function descargarReporteSabuesos1()
{
    try {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId < 1) {
            http_response_code(401);
            echo json_encode(['error' => 'Sesión no válida']);
            exit;
        }

        $resultado = \Models\Ticket::getListaTickets($usuarioId, true);

        if (!$resultado['success'] || empty($resultado['datos'])) {
            http_response_code(404);
            echo json_encode(['error' => 'No hay tickets disponibles']);
            exit;
        }

        $datos = $resultado['datos'];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('folio', 'FOLIO'),
            \PHPSpreadsheet::ColumnaExcel('id_credito', 'ID CRÉDITO'),
            \PHPSpreadsheet::ColumnaExcel('nombre_cliente', 'NOMBRE CLIENTE'),          //  NUEVO
            \PHPSpreadsheet::ColumnaExcel('tipo_ticket_nombre', 'TIPO DE TICKET'),
            \PHPSpreadsheet::ColumnaExcel('estado_ticket_nombre', 'ESTADO'),
            \PHPSpreadsheet::ColumnaExcel('prioridad_nombre', 'PRIORIDAD'),
            \PHPSpreadsheet::ColumnaExcel('descripcion_inicial', 'DESCRIPCIÓN'),
            \PHPSpreadsheet::ColumnaExcel('origen_nombre', 'ORIGEN'),
            \PHPSpreadsheet::ColumnaExcel('fecha_creacion', 'FECHA CREACIÓN'),
            \PHPSpreadsheet::ColumnaExcel('fecha_vencimiento', 'FECHA VENCIMIENTO'),
            \PHPSpreadsheet::ColumnaExcel('fecha_respuesta', 'FECHA RESPUESTA'),        //  NUEVO
            \PHPSpreadsheet::ColumnaExcel('minutos_respuesta', 'MINUTOS EN CONTESTAR'), //  NUEVO
            \PHPSpreadsheet::ColumnaExcel('creador_nombre', 'LEVANTADO POR'),
            \PHPSpreadsheet::ColumnaExcel('asignado_nombre', 'ASIGNADO A'),
            \PHPSpreadsheet::ColumnaExcel('dictamen_estado', 'ÚLTIMO DICTAMEN'),
            \PHPSpreadsheet::ColumnaExcel('dictamen_fecha_visto', 'DICTAMEN VISTO'),
        ];

        // Obtener nombres de clientes para el reporte
        $idsCredito = array_column($datos, 'id_credito');
        $mapaClientes = \Models\Ticket::getNombresClienteParaReporte($idsCredito);

        $datosFormateados = array_map(function($ticket) use ($mapaClientes) {
            // Calcular minutos entre creación y respuesta
            $minutos = '—';
            if (!empty($ticket['fecha_creacion']) && !empty($ticket['dictamen_fecha_envio'])) {
                $inicio = strtotime($ticket['fecha_creacion']);
                $fin    = strtotime($ticket['dictamen_fecha_envio']);
                if ($fin > $inicio) {
                    $minutos = round(($fin - $inicio) / 60);
                }
            }

            return [
                'folio'                  =>     $ticket['folio'] ?? '—',
                'id_credito'             =>     $ticket['id_credito'] ?? '—',
                'nombre_cliente' => $mapaClientes[(int)($ticket['id_credito'] ?? 0)] ?? '—',              //  NUEVO
                'tipo_ticket_nombre'     =>     $ticket['tipo_ticket_nombre'] ?? '—',
                'estado_ticket_nombre'   =>     $ticket['estado_ticket_nombre'] ?? '—',
                'prioridad_nombre'       =>     $ticket['prioridad_nombre'] ?? '—',
                'descripcion_inicial'    =>     $ticket['descripcion_inicial'] ?? '—',
                'origen_nombre'          =>     $ticket['origen_nombre'] ?? '—',
                'fecha_creacion'         =>     isset($ticket['fecha_creacion']) ? date('Y-m-d H:i', strtotime($ticket['fecha_creacion'])) : '—',
                'fecha_vencimiento'      =>     isset($ticket['fecha_vencimiento']) ? date('Y-m-d', strtotime($ticket['fecha_vencimiento'])) : '—',
                'fecha_respuesta'        =>     isset($ticket['dictamen_fecha_envio']) ? date('Y-m-d H:i', strtotime($ticket['dictamen_fecha_envio'])) : '—', //  NUEVO
                'minutos_respuesta'      =>     $minutos,                                      //  NUEVO
                'creador_nombre'         =>     $ticket['creador_nombre'] ?? '—',
                'asignado_nombre'        =>     $ticket['asignado_nombre'] ?? '—',
                'dictamen_estado'        =>     $ticket['dictamen_estado'] ?? '—',
                'dictamen_fecha_visto'   =>     isset($ticket['dictamen_fecha_visto']) ? date('Y-m-d H:i', strtotime($ticket['dictamen_fecha_visto'])) : '—',
            ];
        }, $datos);

        \PHPSpreadsheet::DescargaExcel(
            "Reporte_Tickets_" . date('Y-m-d_His'),
            "Tickets Activos",
            "Tickets",
            $columnas,
            $datosFormateados
        );

        exit;
    } catch (\Exception $e) {
        error_log('Error en descargarReporteSabuesos1: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al generar el reporte: ' . $e->getMessage()]);
        exit;
    }
}

    /**
     * 2️⃣ REPORTE 2: Panel Admin (todos los tickets del sistema)
     */
   public function descargarReporteSabuesos2()
{
    try {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId < 1) {
            http_response_code(401);
            echo json_encode(['error' => 'Sesión no válida']);
            exit;
        }

        $resultado = \Models\Ticket::getListaTickets($usuarioId, false);

        if (!$resultado['success'] || empty($resultado['datos'])) {
            http_response_code(404);
            echo json_encode(['error' => 'No hay tickets disponibles']);
            exit;
        }

        $datos = $resultado['datos'];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('folio', 'FOLIO'),
            \PHPSpreadsheet::ColumnaExcel('id_credito', 'ID CRÉDITO'),
            \PHPSpreadsheet::ColumnaExcel('nombre_cliente', 'NOMBRE CLIENTE'),          //  NUEVO
            \PHPSpreadsheet::ColumnaExcel('tipo_ticket_nombre', 'TIPO DE TICKET'),
            \PHPSpreadsheet::ColumnaExcel('estado_ticket_nombre', 'ESTADO'),
            \PHPSpreadsheet::ColumnaExcel('prioridad_nombre', 'PRIORIDAD'),
            \PHPSpreadsheet::ColumnaExcel('descripcion_inicial', 'DESCRIPCIÓN'),
            \PHPSpreadsheet::ColumnaExcel('origen_nombre', 'ORIGEN'),
            \PHPSpreadsheet::ColumnaExcel('fecha_creacion', 'FECHA CREACIÓN'),
            \PHPSpreadsheet::ColumnaExcel('fecha_vencimiento', 'FECHA VENCIMIENTO'),
            \PHPSpreadsheet::ColumnaExcel('fecha_respuesta', 'FECHA RESPUESTA'),        //  NUEVO
            \PHPSpreadsheet::ColumnaExcel('minutos_respuesta', 'MINUTOS EN CONTESTAR'), //  NUEVO
            \PHPSpreadsheet::ColumnaExcel('creador_nombre', 'LEVANTADO POR'),
            \PHPSpreadsheet::ColumnaExcel('asignado_nombre', 'ASIGNADO A'),
            \PHPSpreadsheet::ColumnaExcel('dictamen_estado', 'ÚLTIMO DICTAMEN'),
            \PHPSpreadsheet::ColumnaExcel('dictamen_fecha_visto', 'DICTAMEN VISTO'),
        ];

        // Obtener nombres de clientes para el reporte
        $idsCredito = array_column($datos, 'id_credito');
        $mapaClientes = \Models\Ticket::getNombresClienteParaReporte($idsCredito);

        $datosFormateados = array_map(function($ticket) use ($mapaClientes) {
            // Calcular minutos entre creación y respuesta
            $minutos = '—';
            if (!empty($ticket['fecha_creacion']) && !empty($ticket['dictamen_fecha_envio'])) {
                $inicio = strtotime($ticket['fecha_creacion']);
                $fin    = strtotime($ticket['dictamen_fecha_envio']);
                if ($fin > $inicio) {
                    $minutos = round(($fin - $inicio) / 60);
                }
            }

            return [
                'folio'               => $ticket['folio'] ?? '—',
                'id_credito'          => $ticket['id_credito'] ?? '—',
                'nombre_cliente'      => $mapaClientes[(int)($ticket['id_credito'] ?? 0)] ?? '—',              //  NUEVO
                'tipo_ticket_nombre'  => $ticket['tipo_ticket_nombre'] ?? '—',
                'estado_ticket_nombre'=> $ticket['estado_ticket_nombre'] ?? '—',
                'prioridad_nombre'    => $ticket['prioridad_nombre'] ?? '—',
                'descripcion_inicial' => $ticket['descripcion_inicial'] ?? '—',
                'origen_nombre'       => $ticket['origen_nombre'] ?? '—',
                'fecha_creacion'      => isset($ticket['fecha_creacion']) ? date('Y-m-d H:i', strtotime($ticket['fecha_creacion'])) : '—',
                'fecha_vencimiento'   => isset($ticket['fecha_vencimiento']) ? date('Y-m-d', strtotime($ticket['fecha_vencimiento'])) : '—',
                'fecha_respuesta'     => isset($ticket['dictamen_fecha_envio']) ? date('Y-m-d H:i', strtotime($ticket['dictamen_fecha_envio'])) : '—', //  NUEVO
                'minutos_respuesta'   => $minutos,                                      //  NUEVO
                'creador_nombre'      => $ticket['creador_nombre'] ?? '—',
                'asignado_nombre'     => $ticket['asignado_nombre'] ?? '—',
                'dictamen_estado'     => $ticket['dictamen_estado'] ?? '—',
                'dictamen_fecha_visto'=> isset($ticket['dictamen_fecha_visto']) ? date('Y-m-d H:i', strtotime($ticket['dictamen_fecha_visto'])) : '—',
            ];
        }, $datos);

        \PHPSpreadsheet::DescargaExcel(
            "Reporte_PanelAdmin_" . date('Y-m-d_His'),
            "Panel Administrativo",
            "Admin",
            $columnas,
            $datosFormateados
        );

        exit;
    } catch (\Exception $e) {
        error_log('Error en descargarReporteSabuesos2: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al generar el reporte: ' . $e->getMessage()]);
        exit;
    }
}

    /**
     * 3️⃣ REPORTE 3: Cerrado/Eliminado (tickets históricos)
     */
    public function descargarReporteSabuesos3()
    {
        try {
            // Limpiar buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
            if ($usuarioId < 1) {
                http_response_code(401);
                echo json_encode(['error' => 'Sesión no válida']);
                exit;
            }

            // Obtener datos usando el modelo
            $resultado = \Models\Ticket::getListaTicketsCerradosEliminados();

            if (!$resultado['success'] || empty($resultado['datos'])) {
                http_response_code(404);
                echo json_encode(['error' => 'No hay tickets cerrados/eliminados disponibles']);
                exit;
            }

            $datos = $resultado['datos'];

            // Definir columnas para el Excel
            $columnas = [
                \PHPSpreadsheet::ColumnaExcel('folio', 'FOLIO'),
                \PHPSpreadsheet::ColumnaExcel('id_credito', 'ID CRÉDITO'),
                \PHPSpreadsheet::ColumnaExcel('tipo_ticket_nombre', 'TIPO DE TICKET'),
                \PHPSpreadsheet::ColumnaExcel('estado_ticket_nombre', 'ESTADO'),
                \PHPSpreadsheet::ColumnaExcel('tipo_accion', 'ACCIÓN'),
                \PHPSpreadsheet::ColumnaExcel('prioridad_nombre', 'PRIORIDAD'),
                \PHPSpreadsheet::ColumnaExcel('descripcion_inicial', 'DESCRIPCIÓN'),
                \PHPSpreadsheet::ColumnaExcel('fecha_creacion', 'FECHA CREACIÓN'),
                \PHPSpreadsheet::ColumnaExcel('fecha_vencimiento', 'FECHA VENCIMIENTO'),
                \PHPSpreadsheet::ColumnaExcel('creador_nombre', 'LEVANTADO POR'),
                \PHPSpreadsheet::ColumnaExcel('asignado_nombre', 'ASIGNADO A'),
                \PHPSpreadsheet::ColumnaExcel('quien_elimino_nombre', 'CERRADO/ELIMINADO POR'),
                \PHPSpreadsheet::ColumnaExcel('fecha_eliminacion', 'FECHA CIERRE/ELIMINACIÓN'),
            ];

            // Formatear datos
            $datosFormateados = array_map(function($ticket) {
                return [
                    'folio' => $ticket['folio'] ?? '—',
                    'id_credito' => $ticket['id_credito'] ?? '—',
                    'tipo_ticket_nombre' => $ticket['tipo_ticket_nombre'] ?? '—',
                    'estado_ticket_nombre' => $ticket['estado_ticket_nombre'] ?? '—',
                    'tipo_accion' => $ticket['tipo_accion'] ?? '—',
                    'prioridad_nombre' => $ticket['prioridad_nombre'] ?? '—',
                    'descripcion_inicial' => $ticket['descripcion_inicial'] ?? '—',
                    'fecha_creacion' => isset($ticket['fecha_creacion']) ? date('Y-m-d H:i', strtotime($ticket['fecha_creacion'])) : '—',
                    'fecha_vencimiento' => isset($ticket['fecha_vencimiento']) ? date('Y-m-d', strtotime($ticket['fecha_vencimiento'])) : '—',
                    'creador_nombre' => $ticket['creador_nombre'] ?? '—',
                    'asignado_nombre' => $ticket['asignado_nombre'] ?? '—',
                    'quien_elimino_nombre' => $ticket['quien_elimino_nombre'] ?? '—',
                    'fecha_eliminacion' => isset($ticket['fecha_eliminacion']) ? date('Y-m-d H:i', strtotime($ticket['fecha_eliminacion'])) : '—',
                ];
            }, $datos);

            // Descargar Excel
            \PHPSpreadsheet::DescargaExcel(
                "Reporte_CerradoEliminado_" . date('Y-m-d_His'),
                "Tickets CerradosEliminados",
                "Histórico",
                $columnas,
                $datosFormateados
            );

            exit;
        } catch (\Exception $e) {
            error_log('Error en descargarReporteSabuesos3: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al generar el reporte: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Landing de Primeros pagos: cobranza esperada (VencimientosLunes) o semana actual / próximo lunes (VencimientosLunesSiguienteSemana).
     */
    public function PrimerosPagos()
    {
        $mods = $this->modulosSesionInt();

        self::set('pp_perm_cobranza', in_array(self::MODULO_PP_COBRANZA_ESPERADA, $mods, true));
        // Cartera actual ahora vive en menú propio: /analitica/carteraactual
        self::set('pp_perm_cartera', false);
        self::set('pp_perm_proxima', in_array(self::MODULO_PP_PROXIMA_SEMANA, $mods, true));
        self::set('pp_perm_historico', in_array(self::MODULO_PP_HISTORICO, $mods, true));

        self::set("titulo", "Primeros pagos");
        self::set("script", "");
        self::render("reporte_primeros_pagos_inicio");
    }

    /**
     * Landing dedicado para «Cartera actual»: muestra solo la card de cartera
     * y desde ahí permite abrir el tablero completo.
     */
    public function CarteraActual()
    {
        self::set('pp_perm_cobranza', false);
        self::set('pp_perm_cartera', true);
        self::set('pp_perm_proxima', false);
        self::set('pp_perm_historico', false);

        self::set('titulo', 'Cartera actual');
        self::set('script', '');
        self::render('reporte_primeros_pagos_inicio');
    }

    /**
     * Histórico semanal (gráficas) desde `tbl_histo_primeros_pagos` — requiere módulo 68.
     * URL: /analitica/PrimerosPagosHistorico
     */
    public function PrimerosPagosHistorico()
    {
        self::set('titulo', 'Primeros pagos — Histórico por semana');
        self::set('script', '');
        self::set('pph_btn_pipeline_histo', $this->puedeMostrarBotonPipelineHistoPrimerosDesdeSegundometro());
        self::render('reporte_primeros_pagos_historico');
    }

    /**
     * POST JSON: copia desde `tbl_segundometro_histo` → `tbl_histo_primeros_pagos` y purga fuera de cartera.
     * Solo usuario {@see USUARIO_PIPELINE_HISTO_PRIMEROS_DESDE_SEGUNDOMETRO}; requiere módulo histórico (68).
     *
     * Body: { "semanas": ["Semana 18-2026", ...] } o { "semana": "Semana 18-2026", "replace_dest": true }
     */
    public function postPrimerosPagosHistoricoPipeline()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['login'])) {
            http_response_code(401);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión no válida.']);

            return;
        }
        if (!$this->puedeMostrarBotonPipelineHistoPrimerosDesdeSegundometro()) {
            http_response_code(403);
            self::respuestaJSON(['success' => false, 'mensaje' => 'No autorizado.']);

            return;
        }
        if (!$this->puedeAccederPrimerosPagosHistorico()) {
            http_response_code(403);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sin acceso al histórico de primeros pagos.']);

            return;
        }

        $raw = file_get_contents('php://input');
        $body = json_decode((string) $raw, true);
        if (!is_array($body)) {
            $body = [];
        }
        $semanas = $body['semanas'] ?? null;
        if (!is_array($semanas)) {
            $una = trim((string) ($body['semana'] ?? ''));
            $semanas = $una !== '' ? [$una] : [];
        }
        $limpio = [];
        foreach ($semanas as $s) {
            $t = trim((string) $s);
            if ($t !== '' && !isset($limpio[$t])) {
                $limpio[$t] = true;
            }
        }
        $semanas = array_keys($limpio);
        if ($semanas === []) {
            http_response_code(400);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Indique al menos una etiqueta SEMANA (ej. Semana 18-2026).']);

            return;
        }

        $replaceDest = !empty($body['replace_dest']);

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(600);
            }
            @ini_set('max_execution_time', '600');

            $copia = PrimerosPagosHistoricoSegundometro::copiarDesdeSegundometroHistoHaciaPrimerosPagos($semanas, false, [
                'replace_dest' => $replaceDest,
            ]);
            if (!($copia['success'] ?? false)) {
                http_response_code(422);
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => (string) ($copia['mensaje'] ?? $copia['error'] ?? 'Error en la copia.'),
                    'copia' => $copia,
                ]);

                return;
            }

            $purga = PrimerosPagosHistoricoSegundometro::purgarFilasFueraCarteraHistorico(false, null, []);
            if (!($purga['success'] ?? false)) {
                http_response_code(500);
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => (string) ($purga['error'] ?? 'Error en la purga.'),
                    'copia' => $copia,
                    'purga' => $purga,
                ]);

                return;
            }

            self::respuestaJSON([
                'success' => true,
                'mensaje' => 'Copia y purga completadas.',
                'copia' => $copia,
                'purga' => $purga,
            ]);
        } catch (\Throwable $e) {
            error_log('Reporteria::postPrimerosPagosHistoricoPipeline -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al ejecutar el proceso.']);
        }
    }

    /**
     * JSON: semanas disponibles en histórico (SEMANA distinta + conteo).
     */
    public function getPrimerosPagosHistoricoSemanas()
    {
        $r = PrimerosPagosHistoricoSegundometro::listarSemanas(5);
        if (!($r['success'] ?? false)) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => (string) ($r['mensaje'] ?? 'Error'),
                'error' => isset($r['error']) ? (string) $r['error'] : null,
                'datos' => [],
            ]);
        }
        self::respuestaJSON([
            'success' => true,
            'datos' => $r['datos'] ?? [],
        ]);
    }

    /**
     * JSON: comparativo de nacimiento vs corte actual de las últimas 5 semanas cerradas
     * (excluye la semana ISO en curso).
     */
    public function getPrimerosPagosHistoricoComparativo()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $r = PrimerosPagosHistoricoSegundometro::resumenUltimasNSemanas(5);
            if (!($r['success'] ?? false)) {
                http_response_code(404);
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => (string) ($r['mensaje'] ?? 'Sin datos'),
                    'error' => isset($r['error']) ? (string) $r['error'] : null,
                ]);

                return;
            }
            self::respuestaJSON(['success' => true, 'datos' => $r['datos'] ?? []]);
        } catch (\Throwable $e) {
            error_log('Reporteria::getPrimerosPagosHistoricoComparativo -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al armar el comparativo histórico.']);
        }
    }

    /**
     * JSON: jerarquías agregadas (POST JSON).
     * - { "semanas": ["Semana 16-2026", ...] } — lista explícita.
     * - { "ultimas": true } — mismas últimas semanas cerradas que el comparativo (puede lanzarse en paralelo).
     */
    public function getPrimerosPagosHistoricoJerarquias()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $body = json_decode($raw ?: '[]', true);
            if (!is_array($body)) {
                $body = [];
            }
            $semanas = $body['semanas'] ?? [];
            if (!is_array($semanas)) {
                http_response_code(400);
                self::respuestaJSON(['success' => false, 'mensaje' => 'El campo semanas debe ser un arreglo.']);

                return;
            }
            if (!empty($body['ultimas'])) {
                $ls = PrimerosPagosHistoricoSegundometro::listarSemanas(5);
                if (!($ls['success'] ?? false)) {
                    http_response_code(404);
                    self::respuestaJSON([
                        'success' => false,
                        'mensaje' => (string) ($ls['mensaje'] ?? 'No se pudo listar semanas para jerarquías.'),
                        'error' => isset($ls['error']) ? (string) $ls['error'] : null,
                    ]);

                    return;
                }
                $semanas = [];
                foreach ((array) ($ls['datos'] ?? []) as $row) {
                    $s = trim((string) ($row['semana'] ?? ''));
                    if ($s !== '') {
                        $semanas[] = $s;
                    }
                }
            }
            if ($semanas === []) {
                http_response_code(400);
                self::respuestaJSON(['success' => false, 'mensaje' => 'Indique semanas[] o ultimas: true.']);

                return;
            }
            $r = PrimerosPagosHistoricoSegundometro::jerarquiasAgregadasPorSemanas($semanas);
            if (!($r['success'] ?? false)) {
                http_response_code(400);
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => (string) ($r['mensaje'] ?? 'Error'),
                    'error' => isset($r['error']) ? (string) $r['error'] : null,
                ]);

                return;
            }
            self::respuestaJSON(['success' => true, 'datos' => $r['datos'] ?? []]);
        } catch (\Throwable $e) {
            error_log('Reporteria::getPrimerosPagosHistoricoJerarquias -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al consultar jerarquías históricas.']);
        }
    }

    /**
     * JSON: resumen y series para gráficas de una semana (POST JSON { "semana": "..." }).
     */
    public function getPrimerosPagosHistoricoResumen()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $body = json_decode($raw ?: '[]', true);
            if (!is_array($body)) {
                $body = [];
            }
            $sem = trim((string) ($body['semana'] ?? ''));
            if ($sem === '') {
                http_response_code(400);
                self::respuestaJSON(['success' => false, 'mensaje' => 'Indique la semana (campo semana).']);

                return;
            }
            $r = PrimerosPagosHistoricoSegundometro::resumenPorSemana($sem);
            if (!($r['success'] ?? false)) {
                http_response_code(404);
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => (string) ($r['mensaje'] ?? 'Sin datos'),
                    'error' => isset($r['error']) ? (string) $r['error'] : null,
                ]);

                return;
            }
            self::respuestaJSON(['success' => true, 'datos' => $r['datos'] ?? []]);
        } catch (\Throwable $e) {
            error_log('Reporteria::getPrimerosPagosHistoricoResumen -> ' . $e->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al consultar el resumen.']);
        }
    }

    private function scriptVencimientosLunes(string $fetchUrl, bool $vistaSimple = false, array $primerosPagosCols = [], bool $modoCartera = false): string
    {
        $colsJson = json_encode($primerosPagosCols, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $script     = <<<'HTML'
        <script>
        document.addEventListener('DOMContentLoaded', function () {

            const VISTA_SIMPLE = %%VISTA_SIMPLE%%;
            const PRIMEROS_PAGOS_COLS = %%PRIMEROS_PAGOS_COLS%%;
            const MODO_CARTERA = %%MODO_CARTERA%%;

            const BUCKET_META = {
                'a) Current':      { cls: 'bg-label-success',   icon: 'fa-circle-check',         short: 'Current' },
                'b) 1 a 7 dias':   { cls: 'bg-label-danger',    icon: 'fa-clock',                short: '1-7d'    },
                'c) 8 a 30 dias':  { cls: 'bg-label-warning',   icon: 'fa-triangle-exclamation', short: '8-30d'   },
                'd) 31 a 60 dias': { cls: 'bg-label-danger',    icon: 'fa-fire',                 short: '31-60d'  },
                'e) 61+ dias':     { cls: 'bg-label-secondary', icon: 'fa-skull-crossbones',     short: '61+d'    },
                'f) 8 a 14 dias':  { cls: 'bg-label-warning',   icon: 'fa-calendar-week',        short: '8-14d'   },
                'g) 15 a 21 dias': { cls: 'bg-label-warning',   icon: 'fa-calendar-days',        short: '15-21d'  },
                'h) 22+ dias':     { cls: 'bg-label-danger',    icon: 'fa-skull-crossbones',     short: '22+d'    },
            };
            const BUCKET_ORDER_CORTE_LUNES = ['a) Current', 'b) 1 a 7 dias', 'c) 8 a 30 dias', 'd) 31 a 60 dias', 'e) 61+ dias'];
            const BUCKET_ORDER_CORTE_CARTERA = ['a) Current', 'b) 1 a 7 dias', 'f) 8 a 14 dias', 'g) 15 a 21 dias', 'h) 22+ dias'];
            const BUCKET_ORDER_CORTE = MODO_CARTERA ? BUCKET_ORDER_CORTE_CARTERA : BUCKET_ORDER_CORTE_LUNES;
            const BUCKET_ORDER_NAC = MODO_CARTERA
                ? ['a) Current', 'b) 1 a 7 dias', 'f) 8 a 14 dias', 'g) 15 a 21 dias', 'h) 22+ dias']
                : BUCKET_ORDER_CORTE_LUNES;
            const BUCKET_ORDER  = BUCKET_ORDER_CORTE;
            const BUCKET_NAC_TOP = MODO_CARTERA ? [...BUCKET_ORDER_NAC] : ['a) Current', 'b) 1 a 7 dias'];

            /**
             * Alinea textos de bucket desde `Bucket_Morosidad_Real` y `Cierre_Actual` (__SPARTA_SECRET_REDACTED__)
             * a las claves f/g/h usadas en cards Cartera. El segundómetro a veces usa a–d, e) 22a29/30, etc.
             */
            function canonBucket(v) {
                if (v === null || v === undefined) return '';
                let s = String(v).trim();
                if (!s) return '';
                s = s.replace(/\s+/g, ' ');
                const low = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                if (low.includes('current')) return 'a) Current';
                const mapa = {
                    'Current': 'a) Current',
                    'current': 'a) Current',
                    'b) 1 a 7 días': 'b) 1 a 7 dias',
                    'c) 8 a 30 días': 'c) 8 a 30 dias',
                    'd) 31 a 60 días': 'd) 31 a 60 dias',
                    'e) 61+ días': 'e) 61+ dias',
                    /* Indicadores: c) / d) → franjas Cartera f) / g) */
                    'c) 8 a 14 dias': 'f) 8 a 14 dias',
                    'c) 8 a 14 días': 'f) 8 a 14 dias',
                    'd) 15 a 21 dias': 'g) 15 a 21 dias',
                    'd) 15 a 21 días': 'g) 15 a 21 dias',
                    'f) 8 a 14 días': 'f) 8 a 14 dias',
                    'g) 15 a 21 días': 'g) 15 a 21 dias',
                    /* Cola 22+ en BD con distintas etiquetas → h) 22+ dias (matriz buckets / ETL) */
                    'e) 22+ dias': 'h) 22+ dias',
                    'e) 22+ días': 'h) 22+ dias',
                    'e) 22 a 29 dias': 'h) 22+ dias',
                    'e) 22 a 29 días': 'h) 22+ dias',
                    'e) 22 a 30 dias': 'h) 22+ dias',
                    'e) 22 a 30 días': 'h) 22+ dias',
                    'h) 22+ días': 'h) 22+ dias',
                };
                if (mapa[s]) return mapa[s];
                if (BUCKET_META[s]) return s;
                return s;
            }

            /**
             * Cartera: 5 tramos. Segundómetro trae a veces c)8-30, d)31-60, e)61+ (Lunes) →
             * van todos a h) 22+ (lunesAnchoCarteraH22). Otras claves f/g no reconocidas →
             * reclasificar por días (Bucket + dias_mora_* / Cierre_Actual).
             */
            function bucketCarteraDesdeDias(d) {
                const n = Math.max(0, Math.floor(Number(d)));
                if (n === 0) return 'a) Current';
                if (n <= 7) return 'b) 1 a 7 dias';
                if (n <= 14) return 'f) 8 a 14 dias';
                if (n <= 21) return 'g) 15 a 21 dias';
                return 'h) 22+ dias';
            }
            /**
             * Tramos «anchos» del esquema Lunes (c/d/e) en Cartera van todos a la card h) 22+,
             * no a f/g por días de mora.
             */
            const BUCKET_LUNES_ANCHO_A_CARTERA_H22 = {
                'c) 8 a 30 dias': 'h) 22+ dias',
                'd) 31 a 60 dias': 'h) 22+ dias',
                'e) 61+ dias': 'h) 22+ dias',
            };
            function lunesAnchoCarteraH22(k) {
                if (!k) return null;
                return BUCKET_LUNES_ANCHO_A_CARTERA_H22[k] || null;
            }
            /** Números enteros con separador de miles (p. ej. 44,452) */
            function fmtInt(n) {
                const x = Number(n);
                return Number.isFinite(x) ? x.toLocaleString('es-MX') : '—';
            }
            function getDiasMoraNac(r) {
                if (!r || typeof r !== 'object') return NaN;
                const v = r.dias_mora_nacimiento ?? r.Dias_mora_nacimiento
                    ?? r.dias_mora_ajustado ?? r.Dias_mora_ajustado
                    ?? r.dias_mora ?? r.Dias_mora;
                if (v === null || v === undefined || v === '') return NaN;
                const x = Number(v);
                return Number.isFinite(x) ? x : NaN;
            }
            function getDiasMoraCorte(r) {
                if (!r || typeof r !== 'object') return NaN;
                const v = r.dias_mora_corte ?? r.Dias_mora_corte;
                if (v === null || v === undefined || v === '') return NaN;
                const x = Number(v);
                return Number.isFinite(x) ? x : NaN;
            }
            function nacKeyCartera(r) {
                if (!MODO_CARTERA) return canonBucket(r.bucket_nacio);
                const k0 = canonBucket(r.bucket_nacio);
                const aH = lunesAnchoCarteraH22(k0);
                if (aH) return aH;
                if (k0 && BUCKET_ORDER_NAC.indexOf(k0) >= 0) return k0;
                const dm = getDiasMoraNac(r);
                if (Number.isFinite(dm) && dm >= 0) return bucketCarteraDesdeDias(dm);
                return k0 || '';
            }
            function corteKeyCartera(r) {
                if (!MODO_CARTERA) return canonBucket(r.bucket_corte_actual);
                const k0 = canonBucket(r.bucket_corte_actual);
                const aH = lunesAnchoCarteraH22(k0);
                if (aH) return aH;
                if (k0 && BUCKET_ORDER_CORTE.indexOf(k0) >= 0) return k0;
                const dm = getDiasMoraCorte(r);
                if (Number.isFinite(dm) && dm >= 0) return bucketCarteraDesdeDias(dm);
                return k0 || '';
            }

            function severidadNacKey(k) {
                if (!k) return -1;
                if (MODO_CARTERA) {
                    const m = { 'a) Current': 0, 'b) 1 a 7 dias': 1, 'f) 8 a 14 dias': 2, 'g) 15 a 21 dias': 3, 'h) 22+ dias': 4 };
                    return m[k] !== undefined ? m[k] : -1;
                }
                const m2 = { 'a) Current': 0, 'b) 1 a 7 dias': 1, 'c) 8 a 30 dias': 2, 'd) 31 a 60 dias': 3, 'e) 61+ dias': 4 };
                return m2[k] !== undefined ? m2[k] : -1;
            }
            function severidadCorteKey(k) {
                if (!k) return -1;
                if (MODO_CARTERA) {
                    const mC = { 'a) Current': 0, 'b) 1 a 7 dias': 1, 'f) 8 a 14 dias': 2, 'g) 15 a 21 dias': 3, 'h) 22+ dias': 4 };
                    return mC[k] !== undefined ? mC[k] : -1;
                }
                const m2 = { 'a) Current': 0, 'b) 1 a 7 dias': 1, 'c) 8 a 30 dias': 2, 'd) 31 a 60 dias': 3, 'e) 61+ dias': 4 };
                return m2[k] !== undefined ? m2[k] : -1;
            }
            function severidadNac(v) {
                return severidadNacKey(canonBucket(v));
            }
            function severidadCorte(v) {
                return severidadCorteKey(canonBucket(v));
            }

            function badgeBucket(val, small = false) {
                const v = canonBucket(val) || val || '—';
                const m = BUCKET_META[v] ?? { cls:'bg-label-secondary', icon:'fa-question', short: v };
                const sz = small ? 'font-size:.68rem;' : '';
                return `<span class="badge ${m.cls}" style="${sz}">
                            <i class="fa ${m.icon} me-1"></i>${small ? m.short : v}
                        </span>`;
            }

            function movimientoHtml(nacio, actual) {
                if (!nacio || !actual) return '<span class="text-muted">—</span>';
                const sN = severidadNac(nacio);
                const sA = severidadCorte(actual);
                if (sN < 0 || sA < 0) return '<span class="text-muted">—</span>';
                if (sN === sA) return `<span class="text-muted" title="Sin cambio"><i class="fa fa-equals"></i></span>`;
                if (sA < sN)   return `<span class="text-success" title="Mejoró"><i class="fa fa-arrow-up"></i></span>`;
                return `<span class="text-danger" title="Empeoró"><i class="fa fa-arrow-down"></i></span>`;
            }

            const dtLang = {
                decimal:',', thousands:'.', emptyTable:'Sin registros',
                info:'Mostrando _START_ a _END_ de _TOTAL_',
                infoEmpty:'0 registros', infoFiltered:'(de _MAX_)',
                lengthMenu:'Mostrar _MENU_', loadingRecords:'Cargando...',
                processing:'Procesando...', search:'',
                searchPlaceholder:'Buscar...', zeroRecords:'Sin coincidencias',
                paginate:{ first:'«', last:'»', next:'›', previous:'‹' },
            };

            let _data        = [];
            let dtVenc       = null;
            let _corteActual = '';
            let _totalEnTabla = 0;
            /** dictamen: telef / campo en la semana (CDMX), clave = Id_crédito segundómetro; solo MODO_CARTERA */
            let _gestionesPorIdCredito = {};

            /* ── DataTable ──
               Cartera: ~toda la cartera en memoria; no destruir/recrear al filtrar (muy lento)
               y deferRender: solo se pinta el DOM de la página visible. */
            function initDT() {
                if ($.fn.DataTable.isDataTable('#tablaVencimientos')) {
                    dtVenc = $('#tablaVencimientos').DataTable();
                    return;
                }

                const cols = VISTA_SIMPLE
                    ? (Array.isArray(PRIMEROS_PAGOS_COLS) ? PRIMEROS_PAGOS_COLS : []).map(c => ({
                        data: c.key,
                        defaultContent: '—',
                        className: (c.key === 'monto' || c.key === 'cuota')
                            ? 'text-end text-nowrap'
                            : (c.key === 'fecha_ultimo_pago_efectivo' ? 'text-center text-nowrap' : 'text-nowrap'),
                        render: function (data) {
                            if (data === null || data === undefined || data === '') return '—';
                            if (c.key === 'fecha_ultimo_pago_efectivo') {
                                const s = String(data).trim();
                                const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
                                if (m) {
                                    const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
                                    if (!Number.isNaN(d.getTime())) {
                                        return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
                                    }
                                }
                            }
                            return String(data);
                        },
                    }))
                    : [
                        { data:'general',   width:'200px' },
                        { data:'jerarquia', width:'220px', orderable: false },
                        { data:'nacio',     className:'text-center', width:'130px' },
                        { data:'corte',     className:'text-center', width:'160px' },
                    ];

                const dtBase = {
                    processing: true,
                    deferRender: true,
                    orderClasses: false,
                    /* En Cartera ~70k filas, Responsive añade coste sin aportar mucho. */
                    responsive: VISTA_SIMPLE,
                    scrollX: VISTA_SIMPLE,
                    pageLength: VISTA_SIMPLE ? 10 : 5,
                    searching: VISTA_SIMPLE,
                    lengthMenu: VISTA_SIMPLE
                        ? [[10, 25, 50, -1], [10, 25, 50, 'Todos']]
                        : [[5, 10, 25, -1], [5, 10, 25, 'Todos']],
                    /* Lunes de cierre: sin caja "Buscar..." de DataTables (búsqueda queda en Filtros → fBusq) */
                    language: dtLang,
                    columns: cols,
                };
                if (MODO_CARTERA) {
                    dtBase.ordering = false;
                    dtBase.order = [];
                } else {
                    dtBase.order = [[0, 'asc']];
                }

                dtVenc = $('#tablaVencimientos').DataTable(dtBase);
            }

            /* ── Stats ── */
            function calcStats(data) {
                const nacDist = {};
                const corteDist = {};
                BUCKET_ORDER_NAC.forEach(b => { nacDist[b] = 0; });
                BUCKET_ORDER_CORTE.forEach(b => { corteDist[b] = 0; });
                data.forEach(r => {
                    const bn = nacKeyCartera(r);
                    if (bn !== '' && nacDist[bn] !== undefined) nacDist[bn]++;
                    const bc = corteKeyCartera(r);
                    if (bc !== '' && corteDist[bc] !== undefined) corteDist[bc]++;
                });

                const matriz = {};
                BUCKET_ORDER_NAC.forEach(b => {
                    matriz[b] = {};
                    BUCKET_ORDER_CORTE.forEach(c => { matriz[b][c] = 0; });
                });
                data.forEach(r => {
                    const n = nacKeyCartera(r);
                    const c = corteKeyCartera(r);
                    if (n && c && matriz[n] !== undefined && matriz[n][c] !== undefined) {
                        matriz[n][c] = (matriz[n][c] || 0) + 1;
                    }
                });

                return { nacDist, matriz, corteDist };
            }

            /** Misma fecha / corte que el encabezado (#lunesFecha, #corteLabel); colores alineados con el título. */
            function actualizarTituloDistribCorte() {
                if (MODO_CARTERA) return;
                const elFe = document.getElementById('distribCorteFecha');
                const elCo = document.getElementById('distribCorteCorteLbl');
                if (!elFe && !elCo) return;
                const fe = (document.getElementById('lunesFecha')?.textContent || '').trim() || '—';
                const co = (document.getElementById('corteLabel')?.textContent || '').trim() || '—';
                if (elFe) elFe.textContent = fe;
                if (elCo) elCo.textContent = co;
            }

            function renderPagoPorDiaUltimoEfectivo(data) {
                const host = document.getElementById('statsPagoPorDia');
                if (!host || !VISTA_SIMPLE) return;
                const arr = Array.isArray(data) ? data : [];
                const total = arr.length || 0;
                const cnt = { jue: 0, vie: 0, sab: 0, dom: 0, lun: 0 };
                const parseFechaPago = (raw) => {
                    if (raw == null || raw === '') return null;
                    const s = String(raw).trim();
                    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
                    if (!m) {
                        const d0 = new Date(s);
                        return Number.isNaN(d0.getTime()) ? null : d0;
                    }
                    const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]), 12, 0, 0);
                    return Number.isNaN(d.getTime()) ? null : d;
                };
                arr.forEach((r) => {
                    const d = parseFechaPago(r.fecha_ultimo_pago_efectivo);
                    if (!d) return;
                    const dow = d.getDay();
                    if (dow === 4) cnt.jue++;
                    else if (dow === 5) cnt.vie++;
                    else if (dow === 6) cnt.sab++;
                    else if (dow === 0) cnt.dom++;
                    else if (dow === 1) cnt.lun++;
                });
                const pct = (n) => (total ? Math.round((Number(n) / total) * 100) : 0);
                const meta = [
                    { k: 'jue', label: 'Pago Jueves', cls: 'bg-label-info', icon: 'fa-calendar-day' },
                    { k: 'vie', label: 'Pago Viernes', cls: 'bg-label-primary', icon: 'fa-calendar-day' },
                    { k: 'sab', label: 'Pago Sábado', cls: 'bg-label-success', icon: 'fa-calendar-day' },
                    { k: 'dom', label: 'Pago Domingo', cls: 'bg-label-warning', icon: 'fa-calendar-day' },
                    { k: 'lun', label: 'Pago Lunes', cls: 'bg-label-secondary', icon: 'fa-calendar-day' },
                ];
                const fsCard = { bd: '.65rem', num: '1.35rem', pct: '1.02rem', ft: '.6rem', pad: 'py-2 px-2', bdgMb: 'mb-1' };
                host.innerHTML = meta.map((m) => {
                    const n = cnt[m.k] || 0;
                    return `
                    <div class="col">
                        <div class="card text-center h-100 border-0 shadow-sm">
                            <div class="card-body ${fsCard.pad}">
                                <div class="badge ${m.cls} ${fsCard.bdgMb}" style="font-size:${fsCard.bd};">
                                    <i class="fa ${m.icon} fa-fw me-1" aria-hidden="true"></i>${m.label}
                                </div>
                                <div class="fw-bold text-nowrap" style="font-size:${fsCard.num};line-height:1.2;">
                                    ${fmtInt(n)}<span class="text-muted fw-semibold" style="font-size:${fsCard.pct};margin-left:4px;">(${pct(n)}%)</span>
                                </div>
                                <div class="text-muted" style="font-size:${fsCard.ft};">registros</div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            }

            function renderStats(data) {
                if (VISTA_SIMPLE) {
                    const totalRegs = Number.isFinite(_totalEnTabla) ? _totalEnTabla : (_data.length || 0);
                    const elNac = document.getElementById('statsNacimiento');
                    if (elNac) {
                        const n = Number(totalRegs);
                        elNac.textContent = Number.isFinite(n) ? n.toLocaleString('es-MX') : '—';
                    }
                    renderPagoPorDiaUltimoEfectivo(data);
                    const elJer = document.getElementById('statsJerarquia');
                    if (elJer) elJer.innerHTML = '';
                    const elCorte = document.getElementById('statsCorte');
                    if (elCorte) elCorte.innerHTML = '';
                    return;
                }

                const { nacDist, matriz, corteDist } = calcStats(data);
                const totalRegs = data.length || 0;
                const pctOf = (n) => (totalRegs ? Math.round((Number(n) / totalRegs) * 100) : 0);

                /* Barra global en Distribución de nacimiento: Current vs 1-7d (solo Lunes de cierre) */
                (function () {
                    if (MODO_CARTERA) return;
                    const elWrap = document.getElementById('nacimientoGlobalResumen');
                    const elPc = document.getElementById('nacPctCurrent');
                    const elP17 = document.getElementById('nacPct17');
                    const elBc = document.getElementById('nacBarCurrent');
                    const elB17 = document.getElementById('nacBar17');
                    if (!elWrap || !elPc || !elP17 || !elBc || !elB17) return;
                    const totalCurrent = nacDist['a) Current'] || 0;
                    const total17 = nacDist['b) 1 a 7 dias'] || 0;
                    const totalG = totalCurrent + total17;
                    if (totalG > 0) {
                        const pC = Math.round((totalCurrent / totalG) * 100);
                        const p7 = 100 - pC;
                        elPc.textContent = pC + '%';
                        elP17.textContent = p7 + '%';
                        elBc.style.width = pC + '%';
                        elB17.style.width = p7 + '%';
                        elBc.style.visibility = pC > 0 ? '' : 'hidden';
                        elB17.style.visibility = p7 > 0 ? '' : 'hidden';
                        elBc.setAttribute('aria-valuenow', String(pC));
                        elB17.setAttribute('aria-valuenow', String(p7));
                        elWrap.style.display = '';
                    } else {
                        elWrap.style.display = 'none';
                    }
                })();

                /* Cards nacimiento: Current + 1-7d arriba; barra global debajo; resto de buckets abajo */
                const fsCard = MODO_CARTERA
                    ? { bd: '.58rem', num: '1.08rem', pct: '.78rem', ft: '.58rem', pad: 'py-2 px-2', bdgMb: 'mb-1' }
                    : { bd: '.65rem', num: '1.5rem', pct: '1.05rem', ft: '.65rem', pad: 'py-2 px-2', bdgMb: 'mb-1' };
                const colMinCartera = MODO_CARTERA ? ' style="min-width:8.5rem"' : '';
                const cardNacHtml = (b) => {
                    const m   = BUCKET_META[b] ?? {};
                    const cnt = nacDist[b] || 0;
                    if (!MODO_CARTERA && !cnt) return '';
                    return `
                    <div class="col"${colMinCartera}>
                        <div class="card text-center h-100 border-0 shadow-sm">
                            <div class="card-body ${fsCard.pad}">
                                <div class="badge ${m.cls} ${fsCard.bdgMb}" style="font-size:${fsCard.bd};">
                                    <i class="fa ${m.icon} fa-fw me-1" aria-hidden="true"></i>${m.short}
                                </div>
                                <div class="fw-bold text-nowrap" style="font-size:${fsCard.num};line-height:1.2;">${fmtInt(cnt)}<span class="text-muted fw-semibold" style="font-size:${fsCard.pct};margin-left:4px;">(${pctOf(cnt)}%)</span></div>
                                <div class="text-muted" style="font-size:${fsCard.ft};">nacieron</div>
                            </div>
                        </div>
                    </div>`;
                };
                let htmlNacTop = '';
                let htmlNacRest = '';
                if (MODO_CARTERA) {
                    BUCKET_ORDER_NAC.forEach(b => { htmlNacTop += cardNacHtml(b); });
                } else {
                    BUCKET_NAC_TOP.forEach(b => { htmlNacTop += cardNacHtml(b); });
                    BUCKET_ORDER_NAC.forEach(b => {
                        if (BUCKET_NAC_TOP.includes(b)) return;
                        htmlNacRest += cardNacHtml(b);
                    });
                }
                const elTop = document.getElementById('statsNacimientoTop');
                const elRest = document.getElementById('statsNacimientoRest');
                if (elTop) elTop.innerHTML = htmlNacTop;
                if (elRest) {
                    elRest.innerHTML = htmlNacRest;
                    elRest.classList.toggle('d-none', MODO_CARTERA || !htmlNacRest.trim());
                }

                if (MODO_CARTERA) {
                    const elAdj = document.getElementById('statAdjudicadas');
                    if (elAdj) {
                        let nAdj = 0;
                        data.forEach(r => {
                            if (Number(r.es_adjudicada) === 1) nAdj++;
                        });
                        elAdj.textContent = nAdj.toLocaleString('es-MX');
                    }
                }

                /* Distribución de corte: Cierre_Actual (expuesto como bucket_corte_actual), distinto a nacimiento. */
                const cardCorteBucketHtml = (b) => {
                    const m = BUCKET_META[b] ?? {};
                    const cnt = corteDist[b] || 0;
                    if (!MODO_CARTERA && !cnt) return '';
                    return `
                    <div class="col"${colMinCartera}>
                        <div class="card text-center h-100 border-0 shadow-sm">
                            <div class="card-body ${fsCard.pad}">
                                <div class="badge ${m.cls} ${fsCard.bdgMb}" style="font-size:${fsCard.bd};">
                                    <i class="fa ${m.icon} fa-fw me-1" aria-hidden="true"></i>${m.short}
                                </div>
                                <div class="fw-bold text-nowrap" style="font-size:${fsCard.num};line-height:1.2;">${fmtInt(cnt)}<span class="text-muted fw-semibold" style="font-size:${fsCard.pct};margin-left:4px;">(${pctOf(cnt)}%)</span></div>
                                <div class="text-muted" style="font-size:${fsCard.ft};">al corte</div>
                            </div>
                        </div>
                    </div>`;
                };
                let htmlCorteBuckets = '';
                BUCKET_ORDER_CORTE.forEach(b => { htmlCorteBuckets += cardCorteBucketHtml(b); });
                const htmlCorte = htmlCorteBuckets || '<div class="col-12"><p class="text-muted small mb-0">Sin buckets de corte reconocidos en los datos.</p></div>';
                document.getElementById('statsCorte').innerHTML = htmlCorte;
                actualizarTituloDistribCorte();

                /* Jerarquía: un pase O(n) pesado; diferir al siguiente tick para que pinten
                   distribuciones y el cierre del modal, y no bloquear con stats+tabla a la vez. */
                if (MODO_CARTERA) {
                    setTimeout(function () { renderStatsJerarquia(data); }, 0);
                } else {
                    renderStatsJerarquia(data);
                }
            }

            /* ── Ranking jerarquía — rediseñado ── */
            function renderStatsJerarquia(data) {
                const liderObsKey = (r) => {
                    const raw = r.Observaciones != null ? r.Observaciones : (r.observaciones != null ? r.observaciones : '');
                    const s = String(raw).trim();
                    return s !== '' ? s : '(Sin líder)';
                };

                const lideres = {};

                data.forEach(r => {
                    const lid  = liderObsKey(r);
                    const ter  = r.Territorial     || '(Sin territorial)';
                    const zon  = r.Zonal           || '(Sin zonal)';
                    const jefe = r.Jefe_de_Plaza   || '(Sin jefe)';
                    const gest = r.Gestor_Asignado || '(Sin gestor)';

                    if (!lideres[lid]) lideres[lid] = { total:0, cobrados:0, pendientes:0, territoriales:{} };
                    const L = lideres[lid]; L.total++;

                    if (!L.territoriales[ter]) L.territoriales[ter] = { total:0, cobrados:0, pendientes:0, zonales:{} };
                    const T = L.territoriales[ter]; T.total++;

                    /* clave combinada zonal+jefe para evitar filas dobles */
                    const zonKey = zon === jefe ? zon : `${zon}|||${jefe}`;
                    if (!T.zonales[zonKey]) T.zonales[zonKey] = {
                        zonNombre: zon, jefNombre: jefe, mismoNombre: zon === jefe,
                        total:0, cobrados:0, pendientes:0, gestores:{}
                    };
                    const Z = T.zonales[zonKey]; Z.total++;

                    if (!Z.gestores[gest]) Z.gestores[gest] = { total:0, cobrados:0, pendientes:0, idCreditos: [] };
                    const G = Z.gestores[gest];
                    G.total++;
                    const sN = severidadNacKey(nacKeyCartera(r));
                    const sA = severidadCorteKey(corteKeyCartera(r));
                    const cobro = (sA >= 0 && sN >= 0 && sA < sN);
                    const idC = (r.Id_credito != null && r.Id_credito !== '')
                        ? String(r.Id_credito)
                        : (r.id_credito != null && r.id_credito !== '' ? String(r.id_credito) : null);
                    if (idC) {
                        const nomC = (r.Nombre_cliente != null && String(r.Nombre_cliente).trim() !== '')
                            ? String(r.Nombre_cliente).trim()
                            : (r.nombre_cliente != null && String(r.nombre_cliente).trim() !== ''
                                ? String(r.nombre_cliente).trim()
                                : '—');
                        G.idCreditos.push({ id: idC, nombre: nomC, esCobrado: !!cobro });
                    }

                    if (cobro) {
                        G.cobrados++;  Z.cobrados++;  T.cobrados++;  L.cobrados++;
                    } else {
                        G.pendientes++; Z.pendientes++; T.pendientes++; L.pendientes++;
                    }
                });

                /* detectar si el territorial es "current" (sin gestión de cobranza) */
                const esCurrent = (nombre) =>
                    /^current$/i.test(nombre.trim()) ||
                    nombre.trim() === '(Sin territorial)';

                const tieneCarteraReal = Object.values(lideres).some(L =>
                    Object.keys(L.territoriales).some((nombreTer) => !esCurrent(nombreTer))
                );

                const liderOrdenados = Object.entries(lideres)
                    .map(([k,v]) => ({ nombre:k, ...v }))
                    .sort((a, b) =>
                        (a.cobrados / Math.max(a.total, 1)) - (b.cobrados / Math.max(b.total, 1))
                    );

                const barColor = (pct) => pct >= 70 ? '#28a745' : pct >= 40 ? '#fd7e14' : '#dc3545';
                const pphEsc = (s) => String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/"/g, '&quot;');
                const pphSortIdCred = (a, b) => {
                    const na = parseInt(a, 10);
                    const nb = parseInt(b, 10);
                    if (!Number.isNaN(na) && !Number.isNaN(nb) && String(a) === String(na) && String(b) === String(nb)) {
                        return na - nb;
                    }
                    return String(a).localeCompare(String(b), undefined, { numeric: true });
                };
                const pphFilasCredUniq = (arr) => {
                    const m = new Map();
                    (arr || []).forEach((it) => {
                        const id = typeof it === 'string' ? it : (it && it.id != null ? String(it.id) : '');
                        if (!id) return;
                        const nom = typeof it === 'string'
                            ? '—'
                            : (it && it.nombre != null && String(it.nombre).trim() !== '' ? String(it.nombre).trim() : '—');
                        if (!m.has(id)) m.set(id, nom);
                    });
                    return [...m.entries()].sort((a, b) => pphSortIdCred(a[0], b[0]));
                };
                /** Unifica por ID; si un ID aparece con distinto estado, cuenta como cobrado si alguna fila lo fue */
                const pphAgruparCredsCobPend = (arr) => {
                    const m = new Map();
                    (arr || []).forEach((it) => {
                        if (!it || it.id == null) return;
                        const id = String(it.id);
                        if (!id) return;
                        const nom = (it.nombre != null && String(it.nombre).trim() !== '') ? String(it.nombre).trim() : '—';
                        const esC = !!it.esCobrado;
                        if (!m.has(id)) m.set(id, { nombre: nom, esCobrado: esC });
                        else {
                            const p = m.get(id);
                            m.set(id, { nombre: nom, esCobrado: p.esCobrado || esC });
                        }
                    });
                    const orden = [...m.entries()].sort((a, b) => pphSortIdCred(a[0], b[0]));
                    const cobrados = [];
                    const pendientes = [];
                    orden.forEach(([id, v]) => {
                        if (v.esCobrado) cobrados.push([id, v.nombre]);
                        else pendientes.push([id, v.nombre]);
                    });
                    return { cobrados, pendientes, total: orden.length };
                };
                const pphZonBrdL = (p) => (p === 0 ? '#E24B4A' : p === 100 ? '#1D9E75' : '#BA7517');
                const pphDonC = 2 * Math.PI * 16;

                const pphDonOf = (pct) => (pphDonC * (1 - Math.min(100, pct) / 100)).toFixed(2);

                let html = '';
                let htmlAviso = '';
                const avisoSinCartera = `
                        <div class="card mb-3 border-start border-3 border-secondary">
                            <div class="card-body py-3">
                                <p class="mb-0 text-muted" style="font-size:.82rem;">
                                    <i class="fa fa-circle-info text-secondary me-2"></i>
                                    El seguimiento de los créditos se podrá visualizar una vez que se asigne la cartera. Consulte disponibilidad con el administrador de asignación de cartera.
                                </p>
                            </div>
                        </div>`;

                liderOrdenados.forEach((lid, lidx) => {
                    const pctLid = lid.total ? Math.round(lid.cobrados / lid.total * 100) : 0;

                    const terOrdenados = Object.entries(lid.territoriales)
                        .map(([k,v]) => ({ nombre:k, ...v }))
                        .sort((a, b) => {
                            if (esCurrent(a.nombre)) return -1;
                            if (esCurrent(b.nombre)) return  1;
                            return (a.cobrados / Math.max(a.total,1)) - (b.cobrados / Math.max(b.total,1));
                        });

                    let htmlTer = '';
                    terOrdenados.forEach((ter, tix) => {

                        /* ── Sin cartera operativa: aviso solo si no hay ningún territorial real con créditos ── */
                        if (esCurrent(ter.nombre)) {
                            if (!tieneCarteraReal && !htmlAviso) {
                                htmlAviso = avisoSinCartera;
                            }
                            return;
                        }

                        /* ── Territorial normal ── */
                        const pctTer = ter.total ? Math.round(ter.cobrados / ter.total * 100) : 0;

                        const zonOrdenados = Object.values(ter.zonales)
                            .sort((a,b) => (a.cobrados/Math.max(a.total,1)) - (b.cobrados/Math.max(b.total,1)));

                        let htmlZon = '';
                        zonOrdenados.forEach((zon, zix) => {
                        const pZ = zon.total ? Math.round(zon.cobrados / zon.total * 100) : 0;
                        const nombreMostrar = zon.mismoNombre ? zon.zonNombre : zon.jefNombre;
                        const pphZonCid = `pphZonB_${lidx}_${tix}_${zix}`;

                        const gestOrdenados = Object.entries(zon.gestores)
                            .map(([k,v]) => ({ nombre:k, ...v }))
                            .sort((a,b) => (a.cobrados/Math.max(a.total,1)) - (b.cobrados/Math.max(b.total,1)));

                        let htmlGest = '';
                        gestOrdenados.forEach((gest, gix) => {
                            const crdId = `pphCrd_${lidx}_${tix}_${zix}_${gix}`;
                            const gList = pphAgruparCredsCobPend(gest.idCreditos);
                            const nIds = gList.total;
                            const filaCred = (idStr, nomStr, esCobr) => {
                                const st = esCobr
                                    ? { bg: '#E8F5E9', color: '#2E7D32', txt: (MODO_CARTERA ? 'Pagado' : 'Cobrado') }
                                    : { bg: '#FFF3E0', color: '#E65100', txt: 'Pendiente' };
                                const etqCls = esCobr ? 'cartera-jer-etalq cartera-jer-etalq--cob' : 'cartera-jer-etalq cartera-jer-etalq--pend';
                                const etq = '<span class="badge rounded-pill ' + etqCls + '" style="background:' + st.bg + '; color:' + st.color + '; font-size:.64rem; font-weight:600; padding:0.25em 0.5em;">' + st.txt + '</span>';
                                const gFila = (MODO_CARTERA && _gestionesPorIdCredito)
                                    ? (_gestionesPorIdCredito[idStr] || _gestionesPorIdCredito[String(idStr)] || null)
                                    : null;
                                const tGtel = (gFila && Number.isFinite(Number(gFila.telefonicas))) ? Number(gFila.telefonicas) : 0;
                                const tGcamp = (gFila && Number.isFinite(Number(gFila.campo))) ? Number(gFila.campo) : 0;
                                const icoFila = MODO_CARTERA
                                    ? '<span class="cartera-jer-gest-wrap d-inline-flex align-items-center flex-wrap" title="Gestiones (Call Center + Legacy + Sky), semana (lun–dom) actual CDMX">'
                                    + '<span class="cartera-jer-gest-metric d-inline-flex align-items-center gap-1">'
                                    + '<span class="cartera-jer-gest-emoji" title="Telefónicas" aria-hidden="true">📞</span>'
                                    + '<span class="cartera-jer-gest-num text-body-secondary fw-semibold" data-gest-id="' + pphEsc(idStr) + '" data-gest-tipo="tel">' + fmtInt(tGtel) + '</span></span>'
                                    + '<span class="cartera-jer-gest-metric d-inline-flex align-items-center gap-1">'
                                    + '<span class="cartera-jer-gest-emoji" title="Campo" aria-hidden="true">🛵</span>'
                                    + '<span class="cartera-jer-gest-num text-body-secondary fw-semibold" data-gest-id="' + pphEsc(idStr) + '" data-gest-tipo="camp">' + fmtInt(tGcamp) + '</span></span>'
                                    + '</span>'
                                    : '';
                                return `<div class="d-flex align-items-center flex-wrap gap-2 border-top px-3 py-1 ps-5 small">
                                <span class="cartera-jer-id-pill badge rounded-pill" style="background:#E6F1FB; color:#185FA5;">${pphEsc(idStr)}</span>
                                <span class="text-body" style="font-size:.8rem;">${pphEsc(nomStr)}</span>
                                ${etq}
                                ${icoFila}
                            </div>`;
                            };
                            const idRows = nIds
                                ? [
                                    ...gList.cobrados.map(([a, b]) => filaCred(a, b, true)),
                                    ...gList.pendientes.map(([a, b]) => filaCred(a, b, false)),
                                ].join('')
                                : '<div class="d-flex border-top px-3 py-1 ps-5 small text-muted">Sin ID en dato de cartera</div>';
                            htmlGest += `<div>
                                <div class="d-flex align-items-center flex-wrap gap-2 ${gix > 0 ? 'border-top' : ''} px-3 py-1 ps-4">
                                    <div class="small text-muted flex-grow-1" style="min-width:6rem;">${pphEsc(gest.nombre)}</div>
                                    <span class="cartera-jer-cred-pill badge rounded-pill border text-body fw-medium px-2 py-0" style="background:#F4F3EF; font-size:.72rem;">${fmtInt(gest.total)} créditos</span>
                                    <div class="d-flex flex-wrap align-items-center gap-2 small text-body-secondary" style="font-size:.75rem;">
                                        <span class="d-inline-flex align-items-center gap-1">
                                            <span class="rounded-circle d-inline-block flex-shrink-0" style="width:6px;height:6px;background-color:#1D9E75;"></span>${fmtInt(gest.cobrados)} ${MODO_CARTERA ? 'pagados' : 'cobrados'}
                                        </span>
                                        <span class="d-inline-flex align-items-center gap-1">
                                            <span class="rounded-circle d-inline-block flex-shrink-0" style="width:6px;height:6px;background-color:#BA7517;"></span>${fmtInt(gest.pendientes)} pendientes
                                        </span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1 text-decoration-none fw-semibold align-baseline" style="color:#185FA5; font-size:.75rem; border:0; box-shadow:none;" data-bs-toggle="collapse" data-bs-target="#${crdId}" aria-expanded="false" title="Listado de clientes (IDs)">Ver clientes</button>
                                </div>
                                <div class="collapse bg-white" id="${crdId}">${idRows}</div>
                            </div>`;
                        });

                        htmlZon += `<div>
                            <div class="d-flex align-items-center gap-2 px-3 py-2 bg-light border-top border-start border-3 w-100"
                                 style="border-left-color: ${pphZonBrdL(pZ)} !important; cursor: pointer"
                                 data-bs-toggle="collapse" data-bs-target="#${pphZonCid}" aria-expanded="true" role="button" tabindex="0">
                                <span class="cartera-jer-zonal-ribbon badge" style="background:#E6F1FB; color:#185FA5; font-size:0.7rem;">Zonal</span>
                                <div class="fw-medium small flex-grow-1">${pphEsc(nombreMostrar)}</div>
                                <span class="text-body-secondary user-select-none d-inline-block" style="transform: rotate(180deg)">▾</span>
                            </div>
                            <div class="collapse show border-top bg-white" id="${pphZonCid}">
                                <div class="d-flex gap-2 px-3 py-2 ps-4 bg-white">
                                    <div class="cartera-kpi cartera-kpi--asig flex-fill text-center rounded-2 py-2 px-3" style="min-width:0; background:#F1EFE8;">
                                        <span class="fs-4 fw-medium d-block" style="color:#5F5E5A;">${fmtInt(zon.total)}</span>
                                        <span style="font-size:9px; letter-spacing:0.02em; color:#5F5E5A;">Asignados</span>
                                    </div>
                                    <div class="cartera-kpi cartera-kpi--cobr flex-fill text-center rounded-2 py-2 px-3" style="min-width:0; background:#EAF3DE;">
                                        <span class="fs-4 fw-medium d-block" style="color:#3B6D11;">${fmtInt(zon.cobrados)}</span>
                                        <span style="font-size:9px; letter-spacing:0.02em; color:#3B6D11;">${MODO_CARTERA ? 'Pagados' : 'Cobrados'}</span>
                                    </div>
                                    <div class="cartera-kpi cartera-kpi--pend flex-fill text-center rounded-2 py-2 px-3" style="min-width:0; background:#FAEEDA;">
                                        <span class="fs-4 fw-medium d-block" style="color:#854F0B;">${fmtInt(zon.pendientes)}</span>
                                        <span style="font-size:9px; letter-spacing:0.02em; color:#854F0B;">Pendientes</span>
                                    </div>
                                    <div class="cartera-kpi cartera-kpi--efic flex-fill text-center rounded-2 py-2 px-3" style="min-width:0; background:#E8EBEF;">
                                        <span class="fs-4 fw-medium d-block" style="color: ${pphZonBrdL(pZ)};">${pZ}%</span>
                                        <span style="font-size:9px; letter-spacing:0.02em; color: ${pphZonBrdL(pZ)};">Efectividad</span>
                                    </div>
                                </div>
                                <div class="bg-white">${htmlGest}</div>
                            </div>
                        </div>`;
                        });

                        htmlTer += `<div class="mb-2 rounded-2 border overflow-hidden">
                        <div class="d-flex align-items-center flex-wrap gap-2 px-3 py-2 bg-white text-body border-bottom" style="cursor: pointer" data-bs-toggle="collapse" data-bs-target="#ter_${lidx}_${tix}" aria-expanded="false" role="button" tabindex="0">
                            <span class="small text-muted text-uppercase fw-semibold">Territorial</span>
                            <span class="fw-medium fs-6 flex-grow-1 text-body">${pphEsc(ter.nombre)}</span>
                            <span class="cartera-jer-cred-pill badge rounded-pill border text-body fw-medium px-2" style="background: #F4F3EF;">${fmtInt(ter.total)} créditos</span>
                            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width:6px; height:6px; background-color: #1D9E75;"></span>${fmtInt(ter.cobrados)} ${MODO_CARTERA ? 'pagados' : 'cobrados'}
                                </span>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width:6px; height:6px; background-color: #BA7517;"></span>${fmtInt(ter.pendientes)} pendientes
                                </span>
                            </div>
                            <svg class="flex-shrink-0" width="44" height="44" viewBox="0 0 44 44" style="min-width: 44px" aria-hidden="true" focusable="false">
                                <circle r="16" cx="22" cy="22" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="3"></circle>
                                <circle r="16" cx="22" cy="22" fill="none" stroke="${barColor(pctTer)}" stroke-width="3" stroke-linecap="round" transform="rotate(-90 22 22)" style="stroke-dasharray: ${pphDonC.toFixed(2)}; stroke-dashoffset: ${pphDonOf(pctTer)};"></circle>
                            </svg>
                            <span class="text-muted user-select-none d-inline-block">▾</span>
                        </div>
                        <div class="collapse" id="ter_${lidx}_${tix}"><div class="p-0 bg-white">${htmlZon}</div></div>
                    </div>`;
                    });

                    if (!htmlTer.trim()) {
                        return;
                    }

                    html += `<div class="mb-3 rounded-3 border overflow-hidden">
                        <div class="d-flex align-items-center flex-wrap gap-2 px-3 py-2 bg-white text-body border-bottom" style="cursor: pointer" data-bs-toggle="collapse" data-bs-target="#lid_${lidx}" aria-expanded="false" role="button" tabindex="0">
                            <span class="small text-muted text-uppercase fw-semibold">Líder</span>
                            <span class="fw-medium fs-6 flex-grow-1 text-body">${pphEsc(lid.nombre)}</span>
                            <span class="cartera-jer-cred-pill badge rounded-pill border text-body fw-medium px-2" style="background: #F4F3EF;">${fmtInt(lid.total)} créditos</span>
                            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width:6px; height:6px; background-color: #1D9E75;"></span>${fmtInt(lid.cobrados)} ${MODO_CARTERA ? 'pagados' : 'cobrados'}
                                </span>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width:6px; height:6px; background-color: #BA7517;"></span>${fmtInt(lid.pendientes)} pendientes
                                </span>
                            </div>
                            <svg class="flex-shrink-0" width="44" height="44" viewBox="0 0 44 44" style="min-width: 44px" aria-hidden="true" focusable="false">
                                <circle r="16" cx="22" cy="22" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="3"></circle>
                                <circle r="16" cx="22" cy="22" fill="none" stroke="${barColor(pctLid)}" stroke-width="3" stroke-linecap="round" transform="rotate(-90 22 22)" style="stroke-dasharray: ${pphDonC.toFixed(2)}; stroke-dashoffset: ${pphDonOf(pctLid)};"></circle>
                            </svg>
                            <span class="text-muted user-select-none d-inline-block">▾</span>
                        </div>
                        <div class="collapse" id="lid_${lidx}"><div class="p-2 bg-white">${htmlTer}</div></div>
                    </div>`;
                });

                document.getElementById('statsJerarquia').innerHTML =
                    (htmlAviso + html).trim() || '<p class="text-muted">Sin datos.</p>';
            }

            async function cargarConteosGestionesCollapse(el) {
                if (!MODO_CARTERA || !el || el.dataset.gestionesLoaded === '1' || el.dataset.gestionesLoading === '1') return;
                const ids = [...el.querySelectorAll('[data-gest-id]')]
                    .map(n => parseInt(n.getAttribute('data-gest-id') || '', 10))
                    .filter(n => Number.isFinite(n) && n > 0);
                const uniq = [...new Set(ids)];
                if (!uniq.length) return;
                el.dataset.gestionesLoading = '1';
                try {
                    const res = await fetch('/analitica/getGestionesConteoCreditosSemana', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ ids: uniq })
                    });
                    const out = await res.json();
                    const mapa = (out && out.success && out.datos && typeof out.datos === 'object') ? out.datos : {};
                    el.querySelectorAll('[data-gest-id][data-gest-tipo]').forEach(span => {
                        const id = String(span.getAttribute('data-gest-id') || '');
                        const tipo = span.getAttribute('data-gest-tipo') || '';
                        const g = mapa[id] || mapa[String(parseInt(id, 10))] || null;
                        if (!g) return;
                        const v = tipo === 'tel' ? g.telefonicas : g.campo;
                        span.textContent = fmtInt(Number.isFinite(Number(v)) ? Number(v) : 0);
                    });
                    el.dataset.gestionesLoaded = '1';
                } catch (e) {
                    console.warn('No se pudieron cargar conteos de gestiones:', e);
                } finally {
                    delete el.dataset.gestionesLoading;
                }
            }

            (function bindConteosGestionesJerarquia() {
                const elJer = document.getElementById('statsJerarquia');
                if (!elJer || elJer.dataset.gestionesBound === '1') return;
                elJer.dataset.gestionesBound = '1';
                elJer.addEventListener('shown.bs.collapse', function (ev) {
                    const el = ev.target;
                    if (el && el.id && String(el.id).startsWith('pphCrd_')) {
                        cargarConteosGestionesCollapse(el);
                    }
                });
            })();

            /* ── Acordeón jerarquía en tabla ── */
            function jerarquiaHtml(r, idx) {
                const niveles = [];
                const obsRaw = r.Observaciones != null ? r.Observaciones : r.observaciones;
                const lidLbl = (obsRaw != null && String(obsRaw).trim() !== '')
                    ? String(obsRaw).trim()
                    : '(Sin líder)';
                niveles.push({ icono:'fa-star', cls:'text-warning', label: lidLbl });
                if (r.Territorial)   niveles.push({ icono:'fa-globe',            cls:'text-secondary', label: r.Territorial   });
                if (r.Zonal)         niveles.push({ icono:'fa-map-location-dot', cls:'text-info',      label: r.Zonal         });
                if (r.Jefe_de_Plaza) niveles.push({ icono:'fa-user-tie',         cls:'text-primary',   label: r.Jefe_de_Plaza });
                niveles.push(                     { icono:'fa-user',             cls:'text-muted',     label: r.Gestor_Asignado || '—' });

                const id  = `jq_${idx}`;
                const top = niveles[0];

                if (niveles.length === 1) {
                    return `<div style="font-size:.75rem;">
                                <i class="fa ${top.icono} ${top.cls} me-1"></i>${top.label}
                            </div>`;
                }

                return `
                <div>
                    <div class="d-flex align-items-center gap-1"
                         style="cursor:pointer;font-size:.75rem;"
                         onclick="toggleJQ('${id}')">
                        <i class="fa ${top.icono} ${top.cls}"></i>
                        <span class="fw-semibold">${top.label}</span>
                        <i class="fa fa-chevron-right fa-xs text-muted ms-1 jq-ico" id="ico_${id}"></i>
                    </div>
                    <div id="${id}" style="display:none;padding-left:.8rem;margin-top:.25rem;border-left:2px solid #e0e0e0;">
                        ${niveles.slice(1).map(n => `
                            <div style="font-size:.71rem;margin-bottom:.12rem;">
                                <i class="fa ${n.icono} ${n.cls} me-1"></i>${n.label}
                            </div>`).join('')}
                    </div>
                </div>`;
            }

            window.toggleJQ = function(id) {
                const el  = document.getElementById(id);
                const ico = document.getElementById(`ico_${id}`);
                if (!el) return;
                const open = el.style.display !== 'none';
                el.style.display = open ? 'none' : 'block';
                if (ico) ico.className = `fa fa-xs text-muted ms-1 jq-ico ${open ? 'fa-chevron-right' : 'fa-chevron-down'}`;
            };

            /* ── Filtros ── */
            function actualizarOpcionesBucketCorte(data, nacioValue) {
                const sel = document.getElementById('fBucketCorte');
                if (!sel) return;
                const cur = sel.value;
                if (MODO_CARTERA) {
                    const vals = [...new Set(data.map(r => r.bucket_corte_actual).filter(Boolean))].sort();
                    sel.innerHTML = '<option value="">Todos</option>';
                    vals.forEach(v => {
                        const o = document.createElement('option');
                        o.value = v; o.textContent = v;
                        if (v === cur) o.selected = true;
                        sel.appendChild(o);
                    });
                    return;
                }
                if (nacioValue === 'a) Current') {
                    sel.innerHTML = '<option value="a) Current">a) Current</option>';
                    sel.value = 'a) Current';
                } else if (nacioValue === 'b) 1 a 7 dias') {
                    sel.innerHTML = '<option value="">Todos</option><option value="a) Current">a) Current</option><option value="b) 1 a 7 dias">b) 1 a 7 dias</option>';
                    if (cur === 'a) Current' || cur === 'b) 1 a 7 dias') sel.value = cur;
                    else sel.value = '';
                } else {
                    const vals = [...new Set(data.map(r => r.bucket_corte_actual).filter(Boolean))].sort();
                    sel.innerHTML = '<option value="">Todos</option>';
                    vals.forEach(v => {
                        const o = document.createElement('option');
                        o.value = v; o.textContent = v;
                        if (v === cur) o.selected = true;
                        sel.appendChild(o);
                    });
                }
            }

            function poblarFiltros(data) {
                const campos = {
                    fBucketNacio: r => r.bucket_nacio,
                    fTerritorial: r => r.Territorial,
                    fZonal:       r => r.Zonal,
                    fJefe:        r => r.Jefe_de_Plaza,
                    fGestor:      r => r.Gestor_Asignado,
                };
                Object.entries(campos).forEach(([id, fn]) => {
                    const sel = document.getElementById(id);
                    if (!sel) return;
                    const cur  = sel.value;
                    const vals = [...new Set(data.map(fn).filter(Boolean))].sort();
                    sel.innerHTML = `<option value="">Todos</option>`;
                    vals.forEach(v => {
                        const o = document.createElement('option');
                        o.value = v; o.textContent = v;
                        if (v === cur) o.selected = true;
                        sel.appendChild(o);
                    });
                });
                actualizarOpcionesBucketCorte(data, document.getElementById('fBucketNacio')?.value || '');
            }

            function aplicarFiltros(data) {
                if (VISTA_SIMPLE) {
                    return data;
                }
                const f = {
                    bucketNacio: document.getElementById('fBucketNacio')?.value || '',
                    bucketCorte: document.getElementById('fBucketCorte')?.value || '',
                    territorial: document.getElementById('fTerritorial')?.value || '',
                    zonal:       document.getElementById('fZonal')?.value       || '',
                    jefe:        document.getElementById('fJefe')?.value        || '',
                    gestor:      document.getElementById('fGestor')?.value      || '',
                    busq:       (document.getElementById('fBusq')?.value        || '').toLowerCase(),
                    movimiento:  document.getElementById('fMovimiento')?.value  || '',
                };
                return data.filter(r => {
                    if (f.bucketNacio && nacKeyCartera(r) !== canonBucket(f.bucketNacio)) return false;
                    if (f.bucketCorte && corteKeyCartera(r) !== canonBucket(f.bucketCorte)) return false;
                    if (f.territorial && r.Territorial         !== f.territorial) return false;
                    if (f.zonal       && r.Zonal               !== f.zonal)       return false;
                    if (f.jefe        && r.Jefe_de_Plaza       !== f.jefe)        return false;
                    if (f.gestor      && r.Gestor_Asignado     !== f.gestor)      return false;
                    if (f.busq) {
                        const h = `${r.Nombre_cliente} ${r.Id_credito}`.toLowerCase();
                        if (!h.includes(f.busq)) return false;
                    }
                    if (f.movimiento) {
                        const sN = severidadNacKey(nacKeyCartera(r));
                        const sA = severidadCorteKey(corteKeyCartera(r));
                        if (f.movimiento === 'mejoro'   && !(sA < sN))   return false;
                        if (f.movimiento === 'empeoró'  && !(sA > sN))   return false;
                        if (f.movimiento === 'igual'    && !(sA === sN)) return false;
                    }
                    return true;
                });
            }

            /* ── Cargar ── */
            async function cargar() {
                const elStatLoad = document.getElementById('statTotal');
                if (elStatLoad) elStatLoad.textContent = '…';
                if (typeof showWait === 'function') {
                    showWait();
                } else {
                    Swal.fire({
                        title: 'Procesando su petición',
                        text: 'Espere un momento...',
                        imageUrl: '/assets/img/wait.svg',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false
                    });
                }
                try {
                    const r = await fetch('__FETCH_VENCIMIENTOS__', { method:'POST' });
                    const ct = (r.headers.get('content-type') || '').toLowerCase();
                    if (!ct.includes('application/json')) {
                        const raw = await r.text();
                        throw new Error('La respuesta no es JSON (¿sesión vencida o ruta incorrecta?). ' + raw.substring(0, 120));
                    }
                    const d = await r.json();
                    const elLunes = document.getElementById('lunesFecha');

                    if (d.success === false) {
                        _data = [];
                        _totalEnTabla = 0;
                        _gestionesPorIdCredito = {};
                        if (elLunes) elLunes.textContent = '—';
                        const errTxt = [d.mensaje, d.error].filter(Boolean).join(' — ');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'No se cargaron los datos', text: errTxt || 'Error desconocido.' });
                        } else {
                            alert(errTxt || 'No se cargaron los datos');
                        }
                        renderStats(_data);
                        setTimeout(function () { try { renderTabla(); } catch (e) { console.error(e); } }, 0);
                        if (!VISTA_SIMPLE && !MODO_CARTERA) await cargarEstadoEnvioAutomatico();
                        return;
                    }

                    _data        = Array.isArray(d.datos) ? d.datos : [];
                    _corteActual = d.corte_actual || '';
                    if (MODO_CARTERA && d.gestiones_por_id_credito && typeof d.gestiones_por_id_credito === 'object') {
                        _gestionesPorIdCredito = d.gestiones_por_id_credito;
                    } else {
                        _gestionesPorIdCredito = {};
                    }
                    const tTab = Number(d.total_en_tabla);
                    _totalEnTabla = Number.isFinite(tTab) ? tTab : (_data.length || 0);

                    const fv = (d.fecha_primer_vencimiento_display || d.lunes_pasado || '').toString().trim();
                    if (elLunes) {
                        elLunes.textContent = fv || '—';
                        if (!VISTA_SIMPLE && d.usado_fallback_lunes && d.lunes_calendario) {
                            elLunes.title = 'El lunes ' + d.lunes_calendario + ' aún no tiene filas en segundómetro; se muestra el último lunes con datos: ' + d.lunes_pasado + '.';
                        } else {
                            elLunes.title = '';
                        }
                    }
                    if (!VISTA_SIMPLE && _corteActual) {
                        const elCorte = document.getElementById('corteLabel');
                        if (elCorte) elCorte.textContent = _corteActual.replace(/_/g,' ');
                    }

                    if (!VISTA_SIMPLE) {
                        poblarFiltros(_data);
                    }
                    /* Resumen/estadísticas primero; la tabla (miles de filas) en el siguiente tick
                       para no bloquear el hilo y permitir cerrar el modal de espera antes del draw pesado. */
                    renderStats(_data);
                    setTimeout(function () {
                        try { renderTabla(); } catch (e) { console.error(e); }
                    }, 0);
                    if (!VISTA_SIMPLE && !MODO_CARTERA) await cargarEstadoEnvioAutomatico();
                } catch(e) {
                    console.error(e);
                    const elLunes = document.getElementById('lunesFecha');
                    if (elLunes) elLunes.textContent = '—';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error de red o formato', text: String(e && e.message ? e.message : e) });
                    }
                } finally {
                    Swal.close();
                }
            }

            async function cargarEstadoEnvioAutomatico() {
                if (MODO_CARTERA) return;
                const badge = document.getElementById('estadoEnvioAuto');
                const badgeAgente = document.getElementById('estadoAgenteCorreos');
                const swAuto = document.getElementById('switchAutoEnvioPrimerosPagos');
                if (!badge) return;
                try {
                    const r = await fetch('/analitica/getEstadoEnvioVencimientosLunesProgramado', { method:'POST' });
                    const d = await r.json();
                    if (!d?.success) {
                        badge.className = 'badge bg-label-danger';
                        badge.innerHTML = '<i class="fa fa-circle-xmark me-1"></i> Auto correo: sin estado';
                        badge.title = d?.mensaje || 'No se pudo consultar estado de cron.';
                        if (badgeAgente) {
                            badgeAgente.className = 'badge bg-label-secondary';
                            badgeAgente.innerHTML = '<i class="fa fa-robot me-1"></i> Agente: —';
                            badgeAgente.title = '';
                        }
                        return;
                    }
                    const en = d.datos?.auto_envio_enabled === true;
                    if (swAuto) swAuto.checked = en;
                    const st = d.datos?.estado || 'pendiente';
                    const detalle = d.datos?.detalle || '';
                    if (st === 'apagado') {
                        badge.className = 'badge bg-label-secondary';
                        badge.innerHTML = '<i class="fa fa-power-off me-1"></i> Auto correo: desactivado';
                    } else if (st === 'ok') {
                        badge.className = 'badge bg-label-success';
                        badge.innerHTML = '<i class="fa fa-circle-check me-1"></i> Auto correo: OK';
                    } else if (st === 'error') {
                        badge.className = 'badge bg-label-danger';
                        badge.innerHTML = '<i class="fa fa-circle-xmark me-1"></i> Auto correo: error de envío';
                    } else if (st === 'incompleto') {
                        badge.className = 'badge bg-label-warning';
                        badge.innerHTML = '<i class="fa fa-calendar-day me-1"></i> Auto correo: faltan ventanas';
                    } else {
                        badge.className = 'badge bg-label-warning';
                        badge.innerHTML = '<i class="fa fa-clock me-1"></i> Auto correo: pendiente';
                    }
                    let title = detalle || 'Estado de envío automático.';
                    if (d.datos?.auto_envio_updated_at) {
                        title += ' · Interruptor: ' + d.datos.auto_envio_updated_at;
                    }
                    badge.title = title;

                    if (badgeAgente) {
                        const agOn = d.datos?.agente_correos_online;
                        const agDet = d.datos?.agente_correos_detalle || '';
                        if (agOn === null || agOn === undefined) {
                            badgeAgente.className = 'badge bg-label-secondary';
                            badgeAgente.innerHTML = '<i class="fa fa-robot me-1"></i> Agente: no consultado';
                            badgeAgente.title = agDet || 'Sonda desactivada en config o sin datos.';
                        } else if (agOn === true) {
                            badgeAgente.className = 'badge bg-label-success';
                            badgeAgente.innerHTML = '<i class="fa fa-robot me-1"></i> Agente: en línea';
                            badgeAgente.title = agDet;
                        } else {
                            badgeAgente.className = 'badge bg-label-danger';
                            badgeAgente.innerHTML = '<i class="fa fa-robot me-1"></i> Agente: fuera de línea';
                            badgeAgente.title = agDet;
                        }
                    }
                } catch (e) {
                    badge.className = 'badge bg-label-danger';
                    badge.innerHTML = '<i class="fa fa-circle-xmark me-1"></i> Auto correo: sin estado';
                    badge.title = 'Error al consultar estado.';
                    const badgeAgente = document.getElementById('estadoAgenteCorreos');
                    if (badgeAgente) {
                        badgeAgente.className = 'badge bg-label-secondary';
                        badgeAgente.innerHTML = '<i class="fa fa-robot me-1"></i> Agente: —';
                    }
                }
            }

            /* ── Render tabla ── */
            function renderTabla() {
                const datos = aplicarFiltros(_data);
                const elStat = document.getElementById('statTotal');
                if (elStat) elStat.textContent = fmtInt(datos.length);
                initDT();

                const fmt = v => '$' + parseFloat(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');

                const rows = datos.map((r, i) => {
                    if (VISTA_SIMPLE) {
                        const o = {};
                        (Array.isArray(PRIMEROS_PAGOS_COLS) ? PRIMEROS_PAGOS_COLS : []).forEach(c => {
                            let v = r[c.key];
                            if (v === null || v === undefined) v = '';
                            if ((c.key === 'monto' || c.key === 'cuota') && v !== '' && !Number.isNaN(parseFloat(v))) {
                                o[c.key] = fmt(v);
                            } else {
                                o[c.key] = v === '' ? '' : String(v);
                            }
                        });
                        return o;
                    }
                    return {
                        general: `
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa fa-id-card text-primary mt-1" style="font-size:.9rem;"></i>
                                <div>
                                    <div class="fw-semibold" style="font-size:.82rem;">${r.Nombre_cliente || '—'}</div>
                                    <div class="text-muted" style="font-size:.7rem;">
                                        <i class="fa fa-hashtag fa-xs me-1"></i>${r.Id_credito || ''}
                                    </div>
                                    ${MODO_CARTERA && Number(r.es_adjudicada) === 1 ? (() => { const gh = String(r.Ghost ?? r.ghost ?? '').replace(/</g,'&lt;').replace(/"/g,'&quot;'); return `<div class="mt-1" style="font-size:.65rem;max-width:15rem;"><span class="badge bg-label-info me-1" style="font-size:.6rem;"><i class="fa fa-gavel me-1"></i>Adjudicada</span><span class="text-muted text-truncate d-inline-block align-bottom" style="max-width:10rem;" title="${gh}">${gh}</span></div>`; })() : ''}
                                </div>
                            </div>`,

                        jerarquia: jerarquiaHtml(r, i),

                        nacio: badgeBucket(r.bucket_nacio),

                        corte: `<div>${badgeBucket(r.bucket_corte_actual)}</div>`,
                    };
                });

                dtVenc.clear();
                if (MODO_CARTERA && rows.length > 5000) {
                    const TAM_LOTE = 5000;
                    var offLote = 0;
                    function inyectarLoteVenc() {
                        var lote = rows.slice(offLote, offLote + TAM_LOTE);
                        if (lote.length) {
                            dtVenc.rows.add(lote);
                            offLote += TAM_LOTE;
                            (typeof requestAnimationFrame === 'function' ? requestAnimationFrame : setTimeout)(inyectarLoteVenc, 0);
                        } else {
                            dtVenc.draw(false);
                        }
                    }
                    inyectarLoteVenc();
                } else {
                    dtVenc.rows.add(rows).draw(false);
                }
            }

            function buildCorreoPayload() {
                const datos = aplicarFiltros(_data);
                if (VISTA_SIMPLE) {
                    return {
                        vista_primeros_pagos_simple: true,
                        total_registros: datos.length,
                        primer_vencimiento: document.getElementById('lunesFecha')?.textContent?.trim() || '',
                        corte_actual: '',
                        generado_en: new Date().toISOString(),
                        total_en_tabla: _totalEnTabla,
                        nota: 'Origen: tbl_segundometro_primeros_pagos (__SPARTA_SECRET_REDACTED__).',
                    };
                }
                const { nacDist, matriz } = calcStats(datos);
                const totalCurrentNac = nacDist['a) Current'] || 0;
                const total1a7Nac = nacDist['b) 1 a 7 dias'] || 0;
                const recuperados1a7 = (matriz['b) 1 a 7 dias'] && matriz['b) 1 a 7 dias']['a) Current']) ? matriz['b) 1 a 7 dias']['a) Current'] : 0;
                const pendientes1a7 = Math.max(0, total1a7Nac - recuperados1a7);
                const corteEnCurrent = totalCurrentNac + recuperados1a7;
                const corteEn17 = pendientes1a7;
                const totalGlobal = totalCurrentNac + total1a7Nac;
                const pctCurrent = totalGlobal ? Math.round(totalCurrentNac / totalGlobal * 100) : 0;
                const pct17 = 100 - pctCurrent;
                const pctRecuperados = total1a7Nac ? Math.round(recuperados1a7 / total1a7Nac * 100) : 0;

                return {
                    total_registros: datos.length,
                    primer_vencimiento: document.getElementById('lunesFecha')?.textContent?.trim() || '',
                    corte_actual: document.getElementById('corteLabel')?.textContent?.trim() || '',
                    generado_en: new Date().toISOString(),
                    global: {
                        total: totalGlobal,
                        current: totalCurrentNac,
                        uno_a_siete: total1a7Nac,
                        pct_current: pctCurrent,
                        pct_uno_a_siete: pct17,
                    },
                    nacimiento: {
                        current: totalCurrentNac,
                        uno_a_siete: total1a7Nac,
                    },
                    corte: {
                        current_mas_recuperados: corteEnCurrent,
                        pendientes: corteEn17,
                    },
                    matriz: {
                        current: {
                            total: totalCurrentNac,
                            siguen_current: totalCurrentNac,
                            efectividad: totalCurrentNac > 0 ? 100 : 0,
                        },
                        uno_a_siete: {
                            total: total1a7Nac,
                            recuperados: recuperados1a7,
                            siguen_uno_a_siete: pendientes1a7,
                            efectividad: pctRecuperados,
                        },
                    },
                    nota: 'Datos calculados con los filtros actualmente aplicados en pantalla.',
                };
            }

            /* ── Exportar CSV ── */
            document.getElementById('btnExportarCSV')?.addEventListener('click', () => {
                const datos = aplicarFiltros(_data);
                const headers = MODO_CARTERA
                    ? ['Id_credito','Nombre_cliente','Bucket_Nacio','Bucket_Corte_Actual',
                        'Observaciones','Territorial','Zonal','Jefe_Plaza','Gestor_Asignado',
                        'Cuotas_vencidas','Saldo_vencido_actualizado','Dias_mora_corte','Ghost','Es_adjudicada']
                    : ['Id_credito','Nombre_cliente','Bucket_Nacio','Bucket_Corte_Actual',
                        'Observaciones','Territorial','Zonal','Jefe_Plaza','Gestor_Asignado',
                        'Cuotas_vencidas','Saldo_vencido_actualizado','Dias_mora_corte'];
                const rows = MODO_CARTERA
                    ? datos.map(r => [
                        r.Id_credito, r.Nombre_cliente,
                        r.bucket_nacio, r.bucket_corte_actual,
                        r.Observaciones ?? r.observaciones ?? '',
                        r.Territorial, r.Zonal, r.Jefe_de_Plaza, r.Gestor_Asignado,
                        r.Cuotas_vencidas, r.Saldo_vencido_actualizado, r.dias_mora_corte,
                        r.Ghost ?? r.ghost ?? '', r.es_adjudicada ?? ''
                    ])
                    : datos.map(r => [
                        r.Id_credito, r.Nombre_cliente,
                        r.bucket_nacio, r.bucket_corte_actual,
                        r.Observaciones ?? r.observaciones ?? '',
                        r.Territorial, r.Zonal, r.Jefe_de_Plaza, r.Gestor_Asignado,
                        r.Cuotas_vencidas, r.Saldo_vencido_actualizado, r.dias_mora_corte
                    ]);
                const csv = [headers,...rows].map(r=>r.map(v=>`"${v??''}"`).join(',')).join('\n');
                const a   = document.createElement('a');
                a.href    = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
                a.download = (MODO_CARTERA ? 'cartera_segundometro_' : 'vencimientos_lunes_') + new Date().toISOString().substring(0,10) + '.csv';
                a.click();
            });

            /* ── Interruptor envío automático (servidor; no depende de sesión al releer) ── */
            document.getElementById('switchAutoEnvioPrimerosPagos')?.addEventListener('change', async function () {
                const el = this;
                const previous = !el.checked;
                const fd = new FormData();
                fd.append('enabled', el.checked ? '1' : '0');
                try {
                    const r = await fetch('/analitica/setSwitchPrimerosPagosAutoEnvio', { method: 'POST', body: fd });
                    const d = await r.json();
                    if (!d?.success) {
                        el.checked = previous;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'No se guardó', text: d?.mensaje || '' });
                        }
                        return;
                    }
                    await cargarEstadoEnvioAutomatico();
                } catch (e) {
                    el.checked = previous;
                    console.error(e);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo guardar el interruptor.' });
                    }
                }
            });

            /* ── Enviar correo ── */
            document.getElementById('btnEnviarCorreo')?.addEventListener('click', async () => {
                const btn = document.getElementById('btnEnviarCorreo');
                const defaultAsunto = VISTA_SIMPLE ? 'Primeros pagos — Semana actual' : 'Primeros pagos — Lunes de Cierre';
                let destinatariosRaw = '';
                let asunto = defaultAsunto;
                const obtenerDestinatariosConfigurados = async () => {
                    const r = await fetch('/analitica/getPrimerosPagosDestinatariosCorreo', { method: 'POST' });
                    const d = await r.json();
                    if (!d?.success) {
                        throw new Error(d?.mensaje || 'No se pudo obtener la lista de correos.');
                    }
                    const arr = Array.isArray(d?.datos?.destinatarios) ? d.datos.destinatarios : [];
                    return arr
                        .map((v) => (typeof v === 'string' ? { email: v, activo: true } : v))
                        .filter((v) => v && v.email)
                        .map((v) => ({
                            email: String(v.email || '').trim().toLowerCase(),
                            activo: v.activo !== false,
                        }))
                        .filter((v) => v.email);
                };
                const parseEmails = (raw) => (raw || '')
                    .split(/[,\s;]+/)
                    .map(v => v.trim().toLowerCase())
                    .filter(Boolean);
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const escapeHtml = (raw) => String(raw ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
                let prefillItems = [];
                try {
                    const list = await obtenerDestinatariosConfigurados();
                    prefillItems = list;
                } catch (e) {
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({ icon: 'warning', title: 'Destinatarios', text: e?.message || 'No se pudo cargar la lista guardada.' });
                    }
                }

                if (typeof Swal !== 'undefined') {
                    const res = await Swal.fire({
                        title: 'Administrar correos',
                        html: `
                            <p class="text-muted small mb-3 mt-0 text-center">(Primeros pagos)</p>
                            <div class="text-start">
                                <label class="form-label mb-2 fw-semibold">Destinatarios</label>
                                <div id="swal-emails-lista" class="rounded-3 border bg-light p-2 overflow-auto" style="max-height:220px;"></div>
                                <div class="input-group input-group-sm mt-3">
                                    <input id="swal-emails-new" class="form-control" placeholder="nuevo@dominio.com">
                                    <button id="swal-emails-add" type="button" class="btn btn-primary">
                                        <i class="fa fa-plus me-1"></i>Agregar
                                    </button>
                                </div>
                                <div id="swal-emails-preview" class="small mt-2"></div>
                            </div>`,
                        confirmButtonText: 'Guardar',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar',
                        focusConfirm: false,
                        didOpen: () => {
                            const listaEl = document.getElementById('swal-emails-lista');
                            const input = document.getElementById('swal-emails-new');
                            const btnAdd = document.getElementById('swal-emails-add');
                            const preview = document.getElementById('swal-emails-preview');
                            let items = [...prefillItems];
                            const refreshPreview = () => {
                                const total = items.length;
                                const activos = items.filter((x) => x.activo).length;
                                if (!preview) return;
                                if (!total) {
                                    preview.innerHTML = '<span class="text-warning fw-semibold">Debes agregar al menos un correo.</span>';
                                    return;
                                }
                                if (!activos) {
                                    preview.innerHTML = '<span class="text-warning fw-semibold">Activa al menos un correo para envio.</span>';
                                    return;
                                }
                                preview.innerHTML = `<span class="badge bg-success-subtle text-success">Activos: ${activos}</span> <span class="badge bg-primary-subtle text-primary ms-1">Total: ${total}</span>`;
                            };
                            const render = () => {
                                if (!listaEl) return;
                                if (!items.length) {
                                    listaEl.innerHTML = '<div class="text-muted small py-2 px-1">Sin correos. Agrega uno para comenzar.</div>';
                                } else {
                                    listaEl.innerHTML = items.map((it, ix) => `
                                        <div class="d-flex align-items-center gap-2 py-2 px-2 mb-1 rounded-2 border bg-white">
                                            <div class="form-check form-switch m-0 flex-grow-1 d-flex align-items-center gap-2">
                                                <input class="form-check-input me-1" type="checkbox" role="switch" id="ppsendmail_${ix}" ${it.activo ? 'checked' : ''} data-ix="${ix}">
                                                <label class="form-check-label small fw-medium ${it.activo ? '' : 'text-muted'}" for="ppsendmail_${ix}">${escapeHtml(it.email)}</label>
                                                <span class="badge ${it.activo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} ms-2">${it.activo ? 'Activo' : 'Inactivo'}</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-del="${ix}" title="Quitar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    `).join('');
                                }
                                refreshPreview();
                            };
                            listaEl?.addEventListener('click', (ev) => {
                                const btnDel = ev.target?.closest ? ev.target.closest('[data-del]') : null;
                                const del = btnDel?.getAttribute ? btnDel.getAttribute('data-del') : null;
                                if (del == null) return;
                                const i = Number(del);
                                if (!Number.isFinite(i)) return;
                                items.splice(i, 1);
                                render();
                            });
                            listaEl?.addEventListener('change', (ev) => {
                                const t = ev.target;
                                if (!t?.getAttribute) return;
                                const ix = Number(t.getAttribute('data-ix'));
                                if (!Number.isFinite(ix) || !items[ix]) return;
                                items[ix].activo = !!t.checked;
                                render();
                            });
                            btnAdd?.addEventListener('click', () => {
                                const email = String(input?.value || '').trim().toLowerCase();
                                if (!email) return;
                                if (!emailRegex.test(email)) {
                                    if (preview) preview.innerHTML = `<span class="text-danger">Correo invalido: ${escapeHtml(email)}</span>`;
                                    return;
                                }
                                if (items.some((x) => x.email === email)) {
                                    if (preview) preview.innerHTML = '<span class="text-warning">Ese correo ya existe.</span>';
                                    return;
                                }
                                items.push({ email, activo: true });
                                if (input) input.value = '';
                                render();
                            });
                            input?.addEventListener('keydown', (ev) => {
                                if (ev.key === 'Enter') {
                                    ev.preventDefault();
                                    btnAdd?.click();
                                }
                            });
                            window.__ppSendMailItemsRef = () => items;
                            render();
                        },
                        preConfirm: () => {
                            const items = (typeof window.__ppSendMailItemsRef === 'function') ? window.__ppSendMailItemsRef() : [];
                            if (!Array.isArray(items) || !items.length) {
                                Swal.showValidationMessage('Escribe al menos un destinatario.');
                                return false;
                            }
                            const lista = [...new Set(items.filter((x) => x.activo).map((x) => String(x.email || '').trim().toLowerCase()).filter(Boolean))];
                            const invalidos = lista.filter(e => !emailRegex.test(e));
                            if (!lista.length) {
                                Swal.showValidationMessage('Escribe al menos un destinatario válido.');
                                return false;
                            }
                            if (invalidos.length) {
                                Swal.showValidationMessage(`Corrige correos inválidos: ${invalidos.join(', ')}`);
                                return false;
                            }
                            return { destinatarios: items };
                        }
                    });
                    try { delete window.__ppSendMailItemsRef; } catch (e) {}
                    if (!res.isConfirmed || !res.value) return;
                    try {
                        const r = await fetch('/analitica/setPrimerosPagosDestinatariosCorreo', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ destinatarios: res.value.destinatarios }),
                        });
                        const d = await r.json();
                        if (!d?.success) throw new Error(d?.mensaje || 'No se pudo guardar.');
                        Swal.fire({ icon: 'success', title: 'Guardado', text: 'Destinatarios actualizados correctamente.' });
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: e?.message || 'No se pudo guardar la lista.' });
                    }
                    return;
                } else {
                    destinatariosRaw = window.prompt('Destinatarios (separados por coma):', '') || '';
                    if (!destinatariosRaw.trim()) return;
                    asunto = window.prompt('Asunto:', defaultAsunto) || defaultAsunto;
                }

                const destinatarios = [...new Set(parseEmails(destinatariosRaw))];

                if (!destinatarios.length) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'Sin destinatarios', text: 'Agrega al menos un correo válido.' });
                    }
                    return;
                }

                const payload = {
                    destinatarios,
                    asunto,
                    reporte: buildCorreoPayload(),
                };

                try {
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Enviando...';
                    }

                    const resp = await fetch('/analitica/enviarCorreoVencimientosLunes', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const out = await resp.json();

                    if (out?.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Correo enviado', text: out.mensaje || 'Reporte enviado correctamente.' });
                        }
                    } else {
                        throw new Error(out?.mensaje || 'No se pudo enviar el correo.');
                    }
                } catch (err) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: err?.message || 'Error al enviar correo.' });
                    }
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-envelope me-1"></i> Enviar correo';
                    }
                }
            });

            /* ── Administrar destinatarios ── */
            document.getElementById('btnConfigCorreosPrimerosPagos')?.addEventListener('click', async () => {
                const parseEmails = (raw) => (raw || '')
                    .split(/[,\s;]+/)
                    .map(v => v.trim().toLowerCase())
                    .filter(Boolean);
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                let actuales = [];
                try {
                    const r = await fetch('/analitica/getPrimerosPagosDestinatariosCorreo', { method: 'POST' });
                    const d = await r.json();
                    if (!d?.success) throw new Error(d?.mensaje || 'No se pudo cargar la configuración.');
                    const arr = Array.isArray(d?.datos?.destinatarios) ? d.datos.destinatarios : [];
                    actuales = arr
                        .map((v) => (typeof v === 'string' ? { email: String(v || '').trim(), activo: true } : v))
                        .map((v) => ({
                            email: String(v?.email || '').trim().toLowerCase(),
                            activo: v?.activo !== false,
                        }))
                        .filter((v) => v.email);
                } catch (e) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Correos', text: e?.message || 'No se pudo cargar la lista.' });
                    }
                    return;
                }

                if (typeof Swal === 'undefined') {
                    const txt = window.prompt('Correos (separados por coma):', actuales.map(v => v.email).join(', ')) || '';
                    const lista = [...new Set(parseEmails(txt))];
                    if (!lista.length) return;
                    const invalidos = lista.filter(e => !emailRegex.test(e));
                    if (invalidos.length) return;
                    await fetch('/analitica/setPrimerosPagosDestinatariosCorreo', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ destinatarios: lista.map((email) => ({ email, activo: true })) }),
                    });
                    return;
                }

                const res = await Swal.fire({
                    title: 'Administrar correos',
                    html: `
                        <p class="text-muted small mb-3 mt-0 text-center">(Primeros pagos)</p>
                        <div class="text-start">
                            <label class="form-label mb-2 fw-semibold">Destinatarios</label>
                            <div id="swal-correos-admin-lista" class="rounded-3 border bg-light p-2 overflow-auto"></div>
                            <div class="input-group input-group-sm mt-3">
                                <input id="swal-correos-admin-new" class="form-control" placeholder="nuevo@dominio.com">
                                <button id="swal-correos-admin-add" type="button" class="btn btn-primary">
                                    <i class="fa fa-plus me-1"></i>Agregar
                                </button>
                            </div>
                            <div id="swal-correos-admin-preview" class="small mt-2"></div>
                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    didOpen: () => {
                        const listaEl = document.getElementById('swal-correos-admin-lista');
                        const inNew = document.getElementById('swal-correos-admin-new');
                        const btnAdd = document.getElementById('swal-correos-admin-add');
                        const pv = document.getElementById('swal-correos-admin-preview');
                        let items = [...actuales];
                        const render = () => {
                            if (!listaEl) return;
                            if (!items.length) {
                                listaEl.innerHTML = '<div class="text-muted small py-2 px-1">Sin correos. Agrega uno para comenzar.</div>';
                            } else {
                                listaEl.innerHTML = items.map((it, ix) => `
                                    <div class="d-flex align-items-center gap-2 py-2 px-2 mb-1 rounded-2 border bg-white">
                                        <div class="form-check form-switch m-0 flex-grow-1 d-flex align-items-center gap-2">
                                            <input class="form-check-input me-1" type="checkbox" role="switch" id="ppmail_${ix}" ${it.activo ? 'checked' : ''} data-ix="${ix}">
                                            <label class="form-check-label small fw-medium ${it.activo ? '' : 'text-muted'}" for="ppmail_${ix}">${it.email}</label>
                                            <span class="badge ${it.activo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} ms-2">${it.activo ? 'Activo' : 'Inactivo'}</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-del="${ix}" title="Quitar">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                `).join('');
                            }
                            refresh();
                        };
                        const refresh = () => {
                            if (!pv) return;
                            const total = items.length;
                            const activos = items.filter((x) => x.activo).length;
                            if (!total) {
                                pv.innerHTML = '<span class="text-warning fw-semibold">Debes agregar al menos un correo.</span>';
                                return;
                            }
                            if (!activos) {
                                pv.innerHTML = '<span class="text-warning fw-semibold">Activa al menos un correo para envío.</span>';
                                return;
                            }
                            pv.innerHTML = `<span class="badge bg-success-subtle text-success">Activos: ${activos}</span> <span class="badge bg-primary-subtle text-primary ms-1">Total: ${total}</span>`;
                        };
                        listaEl?.addEventListener('click', (ev) => {
                            const btnDel = ev.target?.closest ? ev.target.closest('[data-del]') : null;
                            const del = btnDel?.getAttribute ? btnDel.getAttribute('data-del') : null;
                            if (del == null) return;
                            const i = Number(del);
                            if (!Number.isFinite(i)) return;
                            items.splice(i, 1);
                            render();
                        });
                        listaEl?.addEventListener('change', (ev) => {
                            const t = ev.target;
                            if (!t?.getAttribute) return;
                            const ix = Number(t.getAttribute('data-ix'));
                            if (!Number.isFinite(ix) || !items[ix]) return;
                            items[ix].activo = !!t.checked;
                            render();
                        });
                        btnAdd?.addEventListener('click', () => {
                            const email = String(inNew?.value || '').trim().toLowerCase();
                            if (!email) return;
                            if (!emailRegex.test(email)) {
                                if (pv) pv.innerHTML = `<span class="text-danger">Correo inválido: ${email}</span>`;
                                return;
                            }
                            if (items.some((x) => x.email === email)) {
                                if (pv) pv.innerHTML = `<span class="text-warning">Ese correo ya existe.</span>`;
                                return;
                            }
                            items.push({ email, activo: true });
                            if (inNew) inNew.value = '';
                            render();
                        });
                        inNew?.addEventListener('keydown', (ev) => {
                            if (ev.key === 'Enter') {
                                ev.preventDefault();
                                btnAdd?.click();
                            }
                        });
                        window.__ppMailItemsRef = () => items;
                        render();
                    },
                    preConfirm: () => {
                        const items = (typeof window.__ppMailItemsRef === 'function') ? window.__ppMailItemsRef() : [];
                        if (!Array.isArray(items) || !items.length) {
                            Swal.showValidationMessage('Debes agregar al menos un correo.');
                            return false;
                        }
                        const invalidos = items.map((x) => x.email).filter(e => !emailRegex.test(e));
                        if (invalidos.length) {
                            Swal.showValidationMessage(`Corrige correos inválidos: ${invalidos.join(', ')}`);
                            return false;
                        }
                        const activos = items.filter((x) => x.activo);
                        if (!activos.length) {
                            Swal.showValidationMessage('Activa al menos un correo.');
                            return false;
                        }
                        return { destinatarios: items };
                    }
                });
                if (!res.isConfirmed || !res.value) return;

                try {
                    const r = await fetch('/analitica/setPrimerosPagosDestinatariosCorreo', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ destinatarios: res.value.destinatarios }),
                    });
                    const d = await r.json();
                    if (!d?.success) throw new Error(d?.mensaje || 'No se pudo guardar.');
                    Swal.fire({ icon: 'success', title: 'Guardado', text: 'Destinatarios actualizados correctamente.' });
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: e?.message || 'No se pudo guardar la lista.' });
                } finally {
                    try { delete window.__ppMailItemsRef; } catch (e) {}
                }
            });

            /* ── Eventos ── */
            const elBucketNacio = document.getElementById('fBucketNacio');
            if (elBucketNacio) {
                elBucketNacio.addEventListener('change', function() {
                    actualizarOpcionesBucketCorte(_data, this.value || '');
                    renderTabla();
                    renderStats(aplicarFiltros(_data));
                });
            }
            ['fBucketCorte','fTerritorial','fZonal','fJefe','fGestor','fMovimiento']
                .forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('change', () => {
                        renderTabla();
                        renderStats(aplicarFiltros(_data));
                    });
                });

            document.getElementById('fBusq')
                ?.addEventListener('input', renderTabla);

            if (VISTA_SIMPLE) {
                const btnXlsSemana = document.getElementById('btnDescargarExcelPrimerosPagosSemana');
                if (btnXlsSemana) {
                    btnXlsSemana.addEventListener('click', async function() {
                        if (typeof Swal === 'undefined') {
                            window.location.href = '/analitica/descargarPrimerosPagosSemanaActualExcel';
                            return;
                        }
                        Swal.fire({
                            title: 'Descargando Excel',
                            html: '<p class="mb-0 text-muted" style="font-size:0.9rem;">Generando el archivo…</p>',
                            allowOutsideClick: false,
                            allowEscapeKey: true,
                            showConfirmButton: false,
                            didOpen: () => { Swal.showLoading(); },
                            customClass: { popup: 'shadow-lg' }
                        });
                        try {
                            const r = await fetch('/analitica/descargarPrimerosPagosSemanaActualExcel', {
                                method: 'GET',
                                credentials: 'same-origin'
                            });
                            const ct = (r.headers.get('content-type') || '').toLowerCase();
                            if (!r.ok || ct.includes('text/plain') || ct.includes('text/html') || ct.includes('application/json')) {
                                const t = await r.text();
                                throw new Error((t || '').trim().substring(0, 280) || 'No se pudo generar el Excel.');
                            }
                            const blob = await r.blob();
                            const cd = r.headers.get('Content-Disposition') || '';
                            let fname = 'Primeros_pagos_semana_actual.xlsx';
                            const mStar = cd.match(/filename\\*=UTF-8''([^;]+)/i);
                            const mQuot = cd.match(/filename="([^"]+)"/i);
                            if (mStar) {
                                try { fname = decodeURIComponent(mStar[1].trim()); } catch (e) { /* usar default */ }
                            } else if (mQuot) {
                                fname = mQuot[1];
                            }
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = fname;
                            a.rel = 'noopener';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            URL.revokeObjectURL(url);
                            await Swal.fire({
                                icon: 'success',
                                title: 'Descarga completada',
                                text: 'El Excel se generó y la descarga debería haberse iniciado. Si no ves el archivo, revisa la carpeta de descargas o la barra del navegador.',
                                confirmButtonText: '<i class="fa fa-check me-2"></i>Aceptar',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'shadow-lg rounded-3',
                                    confirmButton: 'btn btn-success px-4 fw-semibold',
                                    actions: 'mt-3 mb-1'
                                }
                            });
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo descargar',
                                text: err && err.message ? err.message : 'Error al descargar el Excel.',
                                confirmButtonText: 'Entendido',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'shadow-lg rounded-3',
                                    confirmButton: 'btn btn-danger px-4 fw-semibold',
                                    actions: 'mt-3 mb-1'
                                }
                            });
                        }
                    });
                }
            }

            const btnResetFiltros = document.getElementById('btnReset');
            if (btnResetFiltros) {
                btnResetFiltros.addEventListener('click', () => {
                    ['fBucketNacio','fBucketCorte','fTerritorial','fZonal','fJefe','fGestor','fMovimiento']
                        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
                    const fb = document.getElementById('fBusq');
                    if (fb) fb.value = '';
                    actualizarOpcionesBucketCorte(_data, '');
                    renderTabla();
                    renderStats(_data);
                });
            }

            cargar();
        });
        </script>
    HTML;

        $out = str_replace(
            ['__FETCH_VENCIMIENTOS__', '%%VISTA_SIMPLE%%', '%%PRIMEROS_PAGOS_COLS%%', '%%MODO_CARTERA%%'],
            [
                $fetchUrl,
                $vistaSimple ? 'true' : 'false',
                $colsJson !== '' ? $colsJson : '[]',
                $modoCartera ? 'true' : 'false',
            ],
            $script
        );
        $base = self::BASE_PRIMEROS_PAGOS_ANALITICA;
        $out = str_replace(['/Reporteria/', '/reporteria/', '/analitica/'], $base . '/', $out);

        return $out;
    }

    public function VencimientosLunes()
    {
        $urlNorm = strtolower(str_replace('\\', '/', trim((string) ($_GET['url'] ?? ''), '/')));
        // Solo migrar ruta legada «reporteria/...» (minúsculas). No incluir «analitica/...»: urlNorm siempre
        // está en minúsculas y coincidiría con la URL canónica /analitica/VencimientosLunes → bucle infinito.
        if ($urlNorm === 'reporteria/vencimientoslunes') {
            header('Location: ' . self::BASE_PRIMEROS_PAGOS_ANALITICA . '/VencimientosLunes', true, 301);
            exit;
        }
        if ($this->esLunesCdmxCarteraSemanaActualCerrada()) {
            header('Location: ' . self::BASE_PRIMEROS_PAGOS_ANALITICA . '/PrimerosPagos?pp_cartera_lunes=1', true, 302);
            exit;
        }
        self::set('titulo', 'Primeros pagos — Lunes de Cierre');
        self::set('vencimientos_titulo_card', 'Primeros pagos — Lunes de Cierre');
        self::set('vencimientos_vista_simple', false);
        self::set('vencimientos_modo_cartera', false);
        self::set('columnas_primeros_pagos', []);
        self::set('vencimientos_puede_enviar_correo_primeros_pagos', $this->puedeEnviarCorreoPrimerosPagos());
        self::set('script', $this->scriptVencimientosLunes(self::BASE_PRIMEROS_PAGOS_ANALITICA . '/getVencimientosLunes', false, [], false));
        self::render('reporte_vencimientos_lunes');
    }

    /**
     * Cartera completa (tbl_segundometro_semana): sin filtro por primer vencimiento; nacimiento por días; adjudicadas vía Ghost.
     */
    public function Cartera()
    {
        self::set('titulo', 'Cartera - semana actual');
        self::set('vencimientos_titulo_card', 'Cartera - semana actual');
        self::set('vencimientos_vista_simple', false);
        self::set('vencimientos_modo_cartera', true);
        self::set('columnas_primeros_pagos', []);
        self::set('vencimientos_puede_enviar_correo_primeros_pagos', false);
        self::set('script', $this->scriptVencimientosLunes(
            self::BASE_PRIMEROS_PAGOS_ANALITICA . '/getCarteraSegundometroSemana',
            false,
            [],
            true
        ));
        self::render('reporte_vencimientos_lunes');
    }

    public function VencimientosLunesSiguienteSemana()
    {
        $urlNorm = strtolower(str_replace('\\', '/', trim((string) ($_GET['url'] ?? ''), '/')));
        if ($urlNorm === 'reporteria/vencimientolunessiguientesemana') {
            header('Location: ' . self::BASE_PRIMEROS_PAGOS_ANALITICA . '/VencimientosLunesSiguienteSemana', true, 301);
            exit;
        }
        if ($this->esMartesOMiercolesCdmxPrimerosPagosProximaSemanaCerrada()) {
            header('Location: ' . self::BASE_PRIMEROS_PAGOS_ANALITICA . '/PrimerosPagos?pp_proxima_semana_cerrada=1', true, 302);
            exit;
        }
        self::set('titulo', 'Primeros pagos — Semana actual');
        self::set('vencimientos_titulo_card', 'Primeros pagos — Semana actual');
        self::set('vencimientos_vista_simple', true);
        self::set('vencimientos_modo_cartera', false);
        $colsMeta = EmpresasDAO::columnasPrimerosPagosMegareporte();
        self::set('columnas_primeros_pagos', $colsMeta);
        self::set('vencimientos_puede_enviar_correo_primeros_pagos', $this->puedeEnviarCorreoPrimerosPagos());
        self::set('script', $this->scriptVencimientosLunes(self::BASE_PRIMEROS_PAGOS_ANALITICA . '/getVencimientosLunesSiguienteSemana', true, $colsMeta, false));
        self::render('reporte_vencimientos_lunes');
    }

    public function getCarteraSegundometroSemana()
    {
        try {
            self::respuestaJSON(EmpresasDAO::getCarteraSegundometroSemana());
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    public function getGestionesConteoCreditosSemana()
    {
        try {
            $raw = file_get_contents('php://input');
            $body = json_decode($raw ?: '[]', true);
            if (!is_array($body)) {
                http_response_code(400);
                self::respuestaJSON(['success' => false, 'mensaje' => 'JSON inválido.']);
            }

            $ids = $body['ids'] ?? [];
            if (!is_array($ids)) {
                http_response_code(400);
                self::respuestaJSON(['success' => false, 'mensaje' => 'El campo ids debe ser un arreglo.']);
            }

            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $x) => $x > 0)));
            if ($ids === []) {
                self::respuestaJSON(['success' => true, 'datos' => []]);
            }
            $ids = array_slice($ids, 0, 300);

            $rows = array_map(static fn (int $id): array => ['Id_credito' => $id, 'Id_cliente' => 0], $ids);
            $rangoSem = EstadoCuentaDAO::rangoSemanaCalendarioCDMX();
            $mapa = EstadoCuentaDAO::mapaGestionesDictamenPorIdCreditoSegundometro(
                $rows,
                $rangoSem['inicio'],
                $rangoSem['fin'],
                true
            );

            self::respuestaJSON([
                'success' => true,
                'datos' => $mapa,
                'inicio' => $rangoSem['inicio'],
                'fin' => $rangoSem['fin'],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'mensaje' => 'Error al consultar gestiones.']);
        }
    }

    public function getVencimientosLunes()
    {
        if ($this->esLunesCdmxCarteraSemanaActualCerrada()) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'La cartera no abre hasta el martes, revise los datos en la sección de Primeros pagos próxima semana.',
            ]);

            return;
        }
        try {
            self::respuestaJSON(EmpresasDAO::getVencimientosLunes(0, false));
        } catch (\Exception $e) {
            self::respuestaJSON(["success" => false, "mensaje" => $e->getMessage()]);
        }
    }

    public function getVencimientosLunesSiguienteSemana()
    {
        if ($this->esMartesOMiercolesCdmxPrimerosPagosProximaSemanaCerrada()) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'La cartera de primeros pagos (próxima semana) no está disponible. Podrá consultarla de jueves a lunes; mientras tanto revise la sección de Cobranza esperada — semana actual.',
            ]);

            return;
        }
        try {
            self::respuestaJSON(EmpresasDAO::getVencimientosLunes(0, true));
        } catch (\Exception $e) {
            self::respuestaJSON(["success" => false, "mensaje" => $e->getMessage()]);
        }
    }

    /**
     * Excel (.xlsx) con las mismas columnas que la tabla en «Primeros pagos — Semana actual».
     */
    public function descargarPrimerosPagosSemanaActualExcel()
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }

            $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
            if ($usuarioId < 1) {
                http_response_code(401);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'Sesión no válida.';
                exit;
            }

            if ($this->esMartesOMiercolesCdmxPrimerosPagosProximaSemanaCerrada()) {
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'La descarga no está disponible. Podrá descargar el Excel de jueves a lunes.';
                exit;
            }

            $raw = EmpresasDAO::getVencimientosLunes(0, true);
            if (empty($raw['success'])) {
                http_response_code(404);
                header('Content-Type: text/plain; charset=utf-8');
                echo $raw['mensaje'] ?? 'No se pudieron obtener los datos.';
                exit;
            }

            $datos = $raw['datos'] ?? [];
            if (!is_array($datos)) {
                $datos = [];
            }

            $colsMeta = EmpresasDAO::columnasPrimerosPagosMegareporte();
            $columnas = [];
            foreach ($colsMeta as $c) {
                $esMoneda = ($c['excel_tipo'] ?? '') === 'moneda';
                $columnas[] = \PHPSpreadsheet::ColumnaExcel(
                    $c['key'],
                    $c['titulo'],
                    ['estilo' => \PHPSpreadsheet::GetEstilosExcel($esMoneda ? 'moneda' : 'texto_izquierda')]
                );
            }

            $filas = [];
            foreach ($datos as $r) {
                if (!is_array($r)) {
                    continue;
                }
                $fila = [];
                foreach ($colsMeta as $c) {
                    $k = $c['key'];
                    $v = $r[$k] ?? null;
                    if (($c['excel_tipo'] ?? '') === 'moneda' && $v !== null && $v !== '' && is_numeric($v)) {
                        $fila[$k] = number_format((float) $v, 2, '.', '');
                    } else {
                        $fila[$k] = $v === null || $v === '' ? '' : (string) $v;
                    }
                }
                $filas[] = $fila;
            }

            $nombreArchivo = 'Primeros_pagos_semana_actual_' . date('Y-m-d');
            \PHPSpreadsheet::DescargaExcel(
                $nombreArchivo,
                'Semana actual',
                'Primeros pagos — Semana actual',
                $columnas,
                $filas
            );
            exit;
        } catch (\Throwable $e) {
            error_log('Reporteria::descargarPrimerosPagosSemanaActualExcel -> ' . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Error al generar el archivo Excel.';
            exit;
        }
    }

    /**
     * Config [correos_primeros_pagos_agent] para sondar el proceso Node (HTTP).
     */
    private function correosPrimerosPagosAgenteConfig(): array
    {
        static $cfg = null;
        if ($cfg !== null) {
            return $cfg;
        }
        $cfg = ['enabled' => '1', 'url' => 'http://127.0.0.1:3110'];
        $configFile = __DIR__ . '/../config/config.ini';
        if (is_file($configFile)) {
            $parsed = @parse_ini_file($configFile, true);
            if (is_array($parsed) && isset($parsed['correos_primeros_pagos_agent']) && is_array($parsed['correos_primeros_pagos_agent'])) {
                $cfg = array_merge($cfg, $parsed['correos_primeros_pagos_agent']);
            }
        }
        return $cfg;
    }

    /**
     * @return array{online: ?bool, detail: string, json: ?array}
     */
    private function probeCorreosPrimerosPagosAgent(): array
    {
        $c = $this->correosPrimerosPagosAgenteConfig();
        $enabled = in_array((string)($c['enabled'] ?? '1'), ['1', 'true', 'TRUE', 'yes', 'on'], true);
        if (!$enabled) {
            return ['online' => null, 'detail' => 'Sonda del agente desactivada en config.ini.', 'json' => null];
        }
        $url = rtrim(trim((string)($c['url'] ?? 'http://127.0.0.1:3110')), '/');
        $target = $url . '/';
        if (function_exists('curl_init')) {
            $ch = curl_init($target);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
            $raw = curl_exec($ch);
            $err = curl_error($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($raw === false) {
                return ['online' => false, 'detail' => 'Sin conexión al agente en ' . $url . ' (' . ($err ?: 'timeout') . ').', 'json' => null];
            }
            if ($code < 200 || $code >= 300) {
                return ['online' => false, 'detail' => 'Agente respondió HTTP ' . $code . '.', 'json' => null];
            }
            $j = json_decode($raw, true);
            if (!is_array($j)) {
                return ['online' => false, 'detail' => 'Respuesta no JSON del agente.', 'json' => null];
            }
            $ok = !empty($j['ok']);
            $interval = isset($j['intervalMs']) ? (int)$j['intervalMs'] : 0;
            $sec = $interval > 0 ? (int)round($interval / 1000) : 600;
            $partFreq = ($sec >= 60 && $sec % 60 === 0)
                ? ('cada ~' . (int)($sec / 60) . ' min')
                : ('cada ~' . $sec . ' s');
            $detail = $ok
                ? 'Agente Node activo (' . $partFreq . ' ejecuta el cron PHP). Horarios de envío: CDMX (no la hora del servidor).'
                : 'Agente respondió sin ok.';
            return ['online' => $ok, 'detail' => $detail, 'json' => $j];
        }
        $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
        $raw = @file_get_contents($target, false, $ctx);
        if ($raw === false) {
            return ['online' => false, 'detail' => 'No se pudo conectar al agente.', 'json' => null];
        }
        $j = json_decode($raw, true);
        if (is_array($j) && !empty($j['ok'])) {
            return ['online' => true, 'detail' => 'Agente Node activo.', 'json' => $j];
        }
        return ['online' => false, 'detail' => 'Respuesta inválida.', 'json' => null];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCorreosAgenteMetaForEstado(): array
    {
        $p = $this->probeCorreosPrimerosPagosAgent();
        $out = [
            'agente_correos_online' => $p['online'],
            'agente_correos_detalle' => $p['detail'],
        ];
        if (is_array($p['json'])) {
            $out['agente_correos_interval_ms'] = $p['json']['intervalMs'] ?? null;
            $out['agente_correos_pid'] = $p['json']['pid'] ?? null;
            if (!empty($p['json']['skippedBecauseMondayCDMX'])) {
                $out['agente_omitido_lunes_cdmx'] = true;
            }
        }
        return $out;
    }

    public function getPrimerosPagosDestinatariosCorreo()
    {
        try {
            if (!$this->puedeEnviarCorreoPrimerosPagos()) {
                self::respuestaJSON(self::respuesta(false, 'No autorizado.'));
                return;
            }
            $cfg = $this->cargarConfigDestinatariosPrimerosPagos();
            self::respuestaJSON(self::respuesta(true, 'Destinatarios obtenidos.', [
                'destinatarios' => $cfg['destinatarios'],
                'updated_at' => $cfg['updated_at'],
            ]));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener destinatarios: ' . $e->getMessage()));
        }
    }

    public function setPrimerosPagosDestinatariosCorreo()
    {
        try {
            if (!$this->puedeEnviarCorreoPrimerosPagos()) {
                self::respuestaJSON(self::respuesta(false, 'No autorizado.'));
                return;
            }
            $raw = file_get_contents('php://input');
            $body = json_decode((string) $raw, true);
            if (!is_array($body)) {
                self::respuestaJSON(self::respuesta(false, 'Payload inválido.'));
                return;
            }
            $dest = $body['destinatarios'] ?? [];
            if (!is_array($dest)) {
                self::respuestaJSON(self::respuesta(false, 'destinatarios debe ser un arreglo.'));
                return;
            }
            $guardados = $this->guardarDestinatariosPrimerosPagos($dest);
            self::respuestaJSON(self::respuesta(true, 'Destinatarios actualizados.', [
                'destinatarios' => $guardados,
            ]));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al guardar destinatarios: ' . $e->getMessage()));
        }
    }

    public function getEstadoEnvioVencimientosLunesProgramado()
    {
        try {
            if ((int)($_SESSION['usuario_id'] ?? 0) !== 1) {
                self::respuestaJSON(self::respuesta(false, 'No autorizado.'));
            }

            date_default_timezone_set('America/Mexico_City');
            $stSwitch = \PrimerosPagosAutoSwitch::getState();
            $baseMeta = array_merge([
                'auto_envio_enabled' => $stSwitch['enabled'],
                'auto_envio_updated_at' => $stSwitch['updated_at'],
            ], $this->buildCorreosAgenteMetaForEstado());

            if (!$stSwitch['enabled']) {
                self::respuestaJSON(self::respuesta(true, 'Envío automático desactivado', array_merge($baseMeta, [
                    'estado' => 'apagado',
                    'detalle' => 'El interruptor está apagado. El cron no enviará correos hasta activarlo (estado guardado en el servidor).',
                ])));
            }

            if ((int) date('N') === 1) {
                self::respuestaJSON(self::respuesta(true, 'Lunes sin envío automático', array_merge($baseMeta, [
                    'estado' => 'ok',
                    'detalle' => 'Los lunes no se envían correos de primeros pagos (CDMX). El envío programado opera de martes a domingo.',
                    'primeros_pagos_pausado_lunes' => true,
                ])));
            }

            // CDMX, 24 h (tarde/noche: 16:45, 18:45, 20:45, 23:50 — no formato 12 h en UI)
            $horarios = ['07:45','09:45','11:45','13:45','14:45','16:45','18:45','20:45','23:50'];
            $hoy = date('Y-m-d');
            $ahora = date('H:i');
            $transcurridos = array_values(array_filter($horarios, function ($h) use ($ahora) {
                return strcmp($h, $ahora) <= 0;
            }));

            $estadoFile = RAIZ . '/storage/runtime/primeros_pagos/primeros_pagos_estado.json';
            if (!is_file($estadoFile) && is_file(RAIZ . '/cronjobs/logs/primeros_pagos_estado.json')) {
                $estadoFile = RAIZ . '/cronjobs/logs/primeros_pagos_estado.json';
            }
            if (!is_file($estadoFile)) {
                self::respuestaJSON(self::respuesta(true, 'Sin estado aún', array_merge($baseMeta, [
                    'estado' => 'pendiente',
                    'detalle' => 'Aún no hay estado registrado del cron para hoy.'
                ])));
            }

            $raw = @file_get_contents($estadoFile);
            $json = is_string($raw) ? json_decode($raw, true) : null;
            if (!is_array($json) || ($json['date'] ?? '') !== $hoy) {
                self::respuestaJSON(self::respuesta(true, 'Estado del día no disponible', array_merge($baseMeta, [
                    'estado' => 'pendiente',
                    'detalle' => 'No hay estado registrado para hoy.'
                ])));
            }

            $slots = is_array($json['slots'] ?? null) ? $json['slots'] : [];
            if (empty($transcurridos)) {
                self::respuestaJSON(self::respuesta(true, 'Aún sin horarios vencidos', array_merge($baseMeta, [
                    'estado' => 'pendiente',
                    'detalle' => 'Aún no hay horarios programados transcurridos hoy.'
                ])));
            }

            $faltantes = [];
            $errores = [];
            foreach ($transcurridos as $slot) {
                if (!isset($slots[$slot])) {
                    $faltantes[] = $slot;
                    continue;
                }
                if (($slots[$slot]['status'] ?? '') !== 'success') {
                    $errores[] = $slot;
                }
            }

            if (empty($faltantes) && empty($errores)) {
                $ultimo = end($transcurridos);
                self::respuestaJSON(self::respuesta(true, 'OK', array_merge($baseMeta, [
                    'estado' => 'ok',
                    'detalle' => "Todos los envíos automáticos transcurridos van OK. Último horario validado: {$ultimo}."
                ])));
            }

            $detalle = '';
            if (!empty($faltantes)) {
                $detalle .= 'Sin registro automático en ventanas: ' . implode(', ', $faltantes) . '. ';
            }
            if (!empty($errores)) {
                $detalle .= 'Cron marcó fallo en: ' . implode(', ', $errores) . '.';
            }
            $detalle = trim($detalle);
            // Rojo solo si hubo intentos automáticos no exitosos; faltantes = cron no corrió o solo manual/--force.
            $estado = !empty($errores) ? 'error' : 'incompleto';
            if ($estado === 'incompleto') {
                $detalle .= ' No indica fallo de correo si solo usaste envío manual o --force: esas ejecuciones no llenan el slot fijo (07:45, 09:45…).';
            }
            self::respuestaJSON(self::respuesta(true, 'Con incidencias', array_merge($baseMeta, [
                'estado' => $estado,
                'detalle' => trim($detalle)
            ])));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al consultar estado: ' . $e->getMessage()));
        }
    }

    /**
     * Activa/desactiva el envío automático por cron (solo usuario_id 1).
     * El estado se guarda en disco en el servidor, no en sesión.
     */
    public function setSwitchPrimerosPagosAutoEnvio()
    {
        try {
            if ((int)($_SESSION['usuario_id'] ?? 0) !== 1) {
                self::respuestaJSON(self::respuesta(false, 'No autorizado.'));
            }

            $raw = $_POST['enabled'] ?? null;
            if ($raw === null || $raw === '') {
                self::respuestaJSON(self::respuesta(false, 'Falta el parámetro enabled (0 o 1).'));
            }
            $sv = strtolower(trim((string)$raw));
            if ($sv !== '0' && $sv !== '1') {
                self::respuestaJSON(self::respuesta(false, 'enabled debe ser 0 o 1.'));
            }
            $enabled = ($sv === '1');

            $saved = \PrimerosPagosAutoSwitch::setEnabled($enabled);
            self::respuestaJSON(self::respuesta(true, $enabled ? 'Envío automático activado.' : 'Envío automático desactivado.', [
                'auto_envio_enabled' => $saved['enabled'],
                'auto_envio_updated_at' => $saved['updated_at'],
            ]));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al guardar interruptor: ' . $e->getMessage()));
        }
    }

    /**
     * Uso por cron: genera datos frescos y envía correo a una lista.
     *
     * @param bool $forzarEjecucionLunes true = ignorar la regla «no lunes» (p. ej. cron con --force).
     */
    public function enviarCorreoVencimientosLunesProgramado(array $destinatarios, string $asunto = 'Primeros pagos — Lunes de Cierre', bool $forzarEjecucionLunes = false): array
    {
        try {
            if (function_exists('date_default_timezone_set')) {
                @date_default_timezone_set('America/Mexico_City');
            }
            // Alineado con cron enviar_primeros_pagos_lunes.php: los lunes no hay envío (martes–domingo).
            if ((int) date('N') === 1 && !$forzarEjecucionLunes) {
                return self::respuesta(true, 'Omitido: lunes (CDMX) sin envío automático de primeros pagos (martes–domingo).', [
                    'omitido_por_lunes' => true,
                    'total_registros' => 0,
                ]);
            }

            $destinatariosLimpios = [];
            foreach ($destinatarios as $email) {
                $email = strtolower(trim((string)$email));
                if ($email === '') {
                    continue;
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return self::respuesta(false, "Correo inválido en cron: {$email}");
                }
                $destinatariosLimpios[] = $email;
            }
            $destinatariosLimpios = array_values(array_unique($destinatariosLimpios));
            if (empty($destinatariosLimpios)) {
                return self::respuesta(false, 'Cron sin destinatarios válidos.');
            }

            $raw = EmpresasDAO::getVencimientosLunes();
            if (empty($raw['success'])) {
                return self::respuesta(false, $raw['mensaje'] ?? 'No se pudieron obtener datos del reporte.');
            }

            $rows = is_array($raw['datos'] ?? null) ? $raw['datos'] : [];
            $payload = $this->buildPayloadVencimientosLunes($rows, (string)($raw['lunes_pasado'] ?? ''), (string)($raw['corte_actual'] ?? ''));
            $html = $this->buildCorreoVencimientosLunesHtml($payload);
            $envio = $this->enviarCorreoHtmlVencimientos($destinatariosLimpios, $asunto, $html);
            if (empty($envio['success'])) {
                return self::respuesta(false, $envio['mensaje'] ?? 'No se pudo enviar el correo programado.');
            }

            return self::respuesta(true, 'Correo programado enviado.', [
                'destinatarios' => $destinatariosLimpios,
                'total_registros' => $payload['total_registros'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            error_log('Reporteria::enviarCorreoVencimientosLunesProgramado -> ' . $e->getMessage());
            return self::respuesta(false, 'Error en envío programado: ' . $e->getMessage());
        }
    }

    private function buildPayloadVencimientosLunes(array $rows, string $lunesPasado, string $corteActual): array
    {
        $bucketNacio = [
            'a) Current' => 0,
            'b) 1 a 7 dias' => 0,
        ];

        $matriz = [
            'b) 1 a 7 dias' => ['a) Current' => 0],
        ];

        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            // PDO puede devolver distinto casing en alias; normalizamos para matriz (jerarquía / efectividad).
            $lc = array_change_key_case($r, CASE_LOWER);
            $n = trim((string)($lc['bucket_nacio'] ?? ''));
            $c = trim((string)($lc['bucket_corte_actual'] ?? ''));
            if (array_key_exists($n, $bucketNacio)) {
                $bucketNacio[$n]++;
            }
            if ($n === 'b) 1 a 7 dias' && $c === 'a) Current') {
                $matriz['b) 1 a 7 dias']['a) Current']++;
            }
        }

        $totalCurrentNac = (int)$bucketNacio['a) Current'];
        $total1a7Nac = (int)$bucketNacio['b) 1 a 7 dias'];
        $recuperados1a7 = (int)$matriz['b) 1 a 7 dias']['a) Current'];
        $pendientes1a7 = max(0, $total1a7Nac - $recuperados1a7);
        $totalGlobal = $totalCurrentNac + $total1a7Nac;
        $pctCurrent = $totalGlobal > 0 ? (int)round($totalCurrentNac / $totalGlobal * 100) : 0;
        $pct17 = $totalGlobal > 0 ? 100 - $pctCurrent : 0;
        $pctRecuperados = $total1a7Nac > 0 ? (int)round($recuperados1a7 / $total1a7Nac * 100) : 0;

        return [
            'total_registros' => count($rows),
            'primer_vencimiento' => $lunesPasado !== '' ? $lunesPasado : date('Y-m-d'),
            'corte_actual' => str_replace('_', ' ', $corteActual),
            'generado_en' => date('c'),
            'global' => [
                'total' => $totalGlobal,
                'current' => $totalCurrentNac,
                'uno_a_siete' => $total1a7Nac,
                'pct_current' => $pctCurrent,
                'pct_uno_a_siete' => $pct17,
            ],
            'nacimiento' => [
                'current' => $totalCurrentNac,
                'uno_a_siete' => $total1a7Nac,
            ],
            'corte' => [
                'current_mas_recuperados' => $totalCurrentNac + $recuperados1a7,
                'pendientes' => $pendientes1a7,
            ],
            'matriz' => [
                'current' => [
                    'total' => $totalCurrentNac,
                    'siguen_current' => $totalCurrentNac,
                    'efectividad' => $totalCurrentNac > 0 ? 100 : 0,
                ],
                'uno_a_siete' => [
                    'total' => $total1a7Nac,
                    'recuperados' => $recuperados1a7,
                    'siguen_uno_a_siete' => $pendientes1a7,
                    'efectividad' => $pctRecuperados,
                ],
            ],
            'nota' => 'Datos generados automáticamente para envío programado.',
        ];
    }

    public function enviarCorreoVencimientosLunes()
    {
        try {
            if (!$this->puedeAccederCobranzaEsperadaSemanaActual()) {
                self::respuestaJSON(self::respuesta(false, 'No autorizado para enviar correo de esta vista. Se requiere acceso a Cobranza esperada — semana actual.'));
            }
            if (!$this->puedeEnviarCorreoPrimerosPagos()) {
                self::respuestaJSON(self::respuesta(false, 'No autorizado para enviar este correo. Se requiere el permiso «Enviar correo» (módulo 33) o usuario administrador.'));
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $destinatarios = $input['destinatarios'] ?? [];
            $asunto = trim((string)($input['asunto'] ?? 'Primeros pagos — Lunes de Cierre'));
            $reporte = is_array($input['reporte'] ?? null) ? $input['reporte'] : [];

            if (!is_array($destinatarios) || empty($destinatarios)) {
                self::respuestaJSON(self::respuesta(false, 'Debes indicar al menos un destinatario.'));
            }

            $destinatariosLimpios = [];
            foreach ($destinatarios as $email) {
                $email = strtolower(trim((string)$email));
                if ($email === '') {
                    continue;
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    self::respuestaJSON(self::respuesta(false, "Correo inválido: {$email}"));
                }
                $destinatariosLimpios[] = $email;
            }
            $destinatariosLimpios = array_values(array_unique($destinatariosLimpios));

            if (empty($destinatariosLimpios)) {
                self::respuestaJSON(self::respuesta(false, 'No hay destinatarios válidos para enviar.'));
            }

            $html = $this->buildCorreoVencimientosLunesHtml($reporte);
            $resultadoEnvio = $this->enviarCorreoHtmlVencimientos($destinatariosLimpios, $asunto, $html);

            if (!$resultadoEnvio['success']) {
                self::respuestaJSON(self::respuesta(false, $resultadoEnvio['mensaje'] ?? 'No se pudo enviar el correo.'));
            }

            self::respuestaJSON(self::respuesta(true, 'Correo enviado correctamente.', [
                'destinatarios' => $destinatariosLimpios
            ]));
        } catch (\Throwable $e) {
            error_log('Reporteria::enviarCorreoVencimientosLunes -> ' . $e->getMessage());
            self::respuestaJSON(self::respuesta(false, 'Error al enviar correo: ' . $e->getMessage()));
        }
    }

    private function enviarCorreoHtmlVencimientos(array $destinatarios, string $asunto, string $html): array
    {
        try {
            $autoload = dirname(RAIZ) . '/vendor/autoload.php';
            if (!is_file($autoload)) {
                return self::respuesta(false, 'No se encontró Composer (PHPMailer). Ejecute composer install en la raíz del proyecto.');
            }
            require_once $autoload;

            $configPath = RAIZ . '/config/config.ini';
            $ini = is_file($configPath) ? parse_ini_file($configPath, true) : [];
            $mail = $ini['mail'] ?? [];

            $smtpHost = trim((string)($mail['smtp_host'] ?? ''));
            $smtpPort = (int)($mail['smtp_port'] ?? 587);
            $smtpSecure = strtolower(trim((string)($mail['smtp_secure'] ?? 'tls')));
            $smtpUser = trim((string)($mail['smtp_user'] ?? ''));
            $smtpPass = trim((string)($mail['smtp_pass'] ?? ''));
            $from = trim((string)($mail['mail_from'] ?? $smtpUser));
            $fromName = trim((string)($mail['mail_from_name'] ?? 'Sparta Ledger'));

            if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '') {
                return self::respuesta(false, 'Falta configuración SMTP en backend/config/config.ini sección [mail].');
            }

            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $smtpHost;
            $mailer->Port = $smtpPort;
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtpUser;
            $mailer->Password = $smtpPass;
            if ($smtpSecure === 'ssl') {
                $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mailer->CharSet = 'UTF-8';
            $mailer->isHTML(true);
            $mailer->setFrom($from !== '' ? $from : $smtpUser, $fromName !== '' ? $fromName : 'Sparta Ledger');
            $mailer->Sender = ($from !== '' ? $from : $smtpUser);
            $mailer->addReplyTo(($from !== '' ? $from : $smtpUser), $fromName !== '' ? $fromName : 'Sparta Ledger');
            $mailer->Subject = $asunto;
            $mailer->Body = $html;
            $mailer->AltBody = strip_tags($html);

            foreach ($destinatarios as $email) {
                $mailer->addAddress($email);
            }
            $mailer->send();

            return self::respuesta(true, 'OK');
        } catch (\Throwable $e) {
            error_log('Reporteria::enviarCorreoHtmlVencimientos -> ' . $e->getMessage());
            return self::respuesta(false, 'No se pudo enviar el correo: ' . $e->getMessage());
        }
    }

    /**
     * Cron / agente Node: envía el Excel del reporte Gastos Cobranza con la misma SMTP que [mail] en config.ini
     * (mismo criterio que enviarCorreoHtmlVencimientos / correos programados).
     */
    public function enviarCorreoReporteGastosCobranza(array $destinatarios, string $rutaAdjuntoAbs, string $asunto = ''): array
    {
        try {
            $rutaAdjuntoAbs = realpath($rutaAdjuntoAbs) ?: trim($rutaAdjuntoAbs);
            if ($rutaAdjuntoAbs === '' || !is_file($rutaAdjuntoAbs) || !is_readable($rutaAdjuntoAbs)) {
                return self::respuesta(false, 'Archivo adjunto no encontrado o no legible.');
            }

            $destinatariosLimpios = [];
            foreach ($destinatarios as $email) {
                $email = strtolower(trim((string) $email));
                if ($email === '') {
                    continue;
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return self::respuesta(false, "Correo inválido: {$email}");
                }
                $destinatariosLimpios[] = $email;
            }
            $destinatariosLimpios = array_values(array_unique($destinatariosLimpios));
            if (empty($destinatariosLimpios)) {
                return self::respuesta(false, 'Sin destinatarios válidos.');
            }

            $autoload = dirname(RAIZ) . '/vendor/autoload.php';
            if (!is_file($autoload)) {
                return self::respuesta(false, 'No se encontró Composer (PHPMailer). Ejecute composer install en la raíz del proyecto.');
            }
            require_once $autoload;

            $configPath = RAIZ . '/config/config.ini';
            $ini = is_file($configPath) ? parse_ini_file($configPath, true) : [];
            $mail = $ini['mail'] ?? [];

            $smtpHost = trim((string) ($mail['smtp_host'] ?? ''));
            $smtpPort = (int) ($mail['smtp_port'] ?? 587);
            $smtpSecure = strtolower(trim((string) ($mail['smtp_secure'] ?? 'tls')));
            $smtpUser = trim((string) ($mail['smtp_user'] ?? ''));
            $smtpPass = trim((string) ($mail['smtp_pass'] ?? ''));
            $from = trim((string) ($mail['mail_from'] ?? $smtpUser));
            $fromName = trim((string) ($mail['mail_from_name'] ?? 'Sparta Ledger'));

            if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '') {
                return self::respuesta(false, 'Falta configuración SMTP en backend/config/config.ini sección [mail].');
            }

            $baseName = basename($rutaAdjuntoAbs);
            if ($asunto === '') {
                $asunto = 'Reporte Gastos Cobranza — ' . date('Y-m-d');
            }

            $esc = static function ($v): string {
                return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
            };
            $html = '<p>Se generó el reporte de cobranza adjunto.</p>'
                . '<p><strong>' . $esc($baseName) . '</strong></p>'
                . '<p style="color:#555;font-size:12px;">Mensaje automático (agente Gastos Cobranza).</p>';

            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $smtpHost;
            $mailer->Port = $smtpPort;
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtpUser;
            $mailer->Password = $smtpPass;
            if ($smtpSecure === 'ssl') {
                $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mailer->CharSet = 'UTF-8';
            $mailer->isHTML(true);
            $mailer->setFrom($from !== '' ? $from : $smtpUser, $fromName !== '' ? $fromName : 'Sparta Ledger');
            $mailer->Sender = ($from !== '' ? $from : $smtpUser);
            $mailer->addReplyTo(($from !== '' ? $from : $smtpUser), $fromName !== '' ? $fromName : 'Sparta Ledger');
            $mailer->Subject = $asunto;
            $mailer->Body = $html;
            $mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
            $mailer->addAttachment($rutaAdjuntoAbs, $baseName);

            foreach ($destinatariosLimpios as $email) {
                $mailer->addAddress($email);
            }
            $mailer->send();

            return self::respuesta(true, 'Correo con reporte enviado.', [
                'destinatarios' => $destinatariosLimpios,
                'adjunto' => $baseName,
            ]);
        } catch (\Throwable $e) {
            error_log('Reporteria::enviarCorreoReporteGastosCobranza -> ' . $e->getMessage());

            return self::respuesta(false, 'No se pudo enviar el correo: ' . $e->getMessage());
        }
    }

    private function buildCorreoVencimientosLunesHtml(array $r): string
    {
        if (!empty($r['vista_primeros_pagos_simple'])) {
            return $this->buildCorreoVencimientosPrimerosPagosSemanaHtml($r);
        }

        $num = function ($v): string {
            return number_format((float)$v, 0, '.', ',');
        };
        $esc = function ($v): string {
            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        };

        $primerVencimiento = $esc($r['primer_vencimiento'] ?? '—');
        $corteActual = $esc($r['corte_actual'] ?? '—');
        $generadoEn = isset($r['generado_en']) ? date('Y-m-d', strtotime((string)$r['generado_en'])) : date('Y-m-d');

        $total = (float)($r['total_registros'] ?? 0);
        $nacCurrent = (float)($r['nacimiento']['current'] ?? 0);
        $nac17 = (float)($r['nacimiento']['uno_a_siete'] ?? 0);

        $corteRecuperados = (float)($r['corte']['current_mas_recuperados'] ?? 0);
        $cortePendientes = (float)($r['corte']['pendientes'] ?? 0);

        $globTotal = (float)($r['global']['total'] ?? 0);
        $globCurrent = (float)($r['global']['current'] ?? 0);
        $glob17 = (float)($r['global']['uno_a_siete'] ?? 0);
        $pctCurrent = (float)($r['global']['pct_current'] ?? 0);
        $pct17 = (float)($r['global']['pct_uno_a_siete'] ?? 0);

        $mCurrentTotal = (float)($r['matriz']['current']['total'] ?? 0);
        $mCurrentSiguen = (float)($r['matriz']['current']['siguen_current'] ?? 0);

        $m17Total = (float)($r['matriz']['uno_a_siete']['total'] ?? 0);
        $m17Recuperados = (float)($r['matriz']['uno_a_siete']['recuperados'] ?? 0);
        $m17Siguen = (float)($r['matriz']['uno_a_siete']['siguen_uno_a_siete'] ?? 0);
        $m17Efectividad = (float)($r['matriz']['uno_a_siete']['efectividad'] ?? 0);

        $nota = $esc($r['nota'] ?? 'Generado automáticamente');

        $den = $total > 0 ? (float)$total : 1;
        $pctNacCurrent   = (int) round($nacCurrent       / $den * 100);
        $pctNac17        = (int) round($nac17            / $den * 100);
        $pctCorteCurrent = (int) round($corteRecuperados / $den * 100);
        $pctCortePend    = (int) round($cortePendientes  / $den * 100);

        return <<<HTML
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Primeros Pagos — Lunes de Cierre</title>
  <style type="text/css">
    body { margin: 0 !important; padding: 0 !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    .email-outer { width: 100%; background: #f0f4f8; }
    .email-shell { max-width: 640px; width: 100%; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
    .mono { font-family: Consolas, 'Courier New', monospace; }
    .header { background: #1d4ed8; padding: 28px 24px 20px; border-bottom: 1px solid #bfdbfe; }
    .header-eyebrow { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #bfdbfe; margin-bottom: 8px; }
    .header h1 { font-size: 20px; font-weight: 700; color: #fff; line-height: 1.35; margin: 0 0 12px 0; }
    .body { padding: 24px 20px 28px; background: #ffffff; }
    .stat-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; margin-bottom: 20px; }
    .big-num { font-size: 36px; font-weight: 600; color: #1d4ed8; line-height: 1; }
    .label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; }
    .block-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
    .block-card-title { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 12px; }
    .mini-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center; }
    .badge { display: inline-block; border-radius: 20px; padding: 3px 10px; font-size: 10px; font-weight: 600; margin-bottom: 8px; }
    .badge-green { background: #dcfce7; color: #16a34a; }
    .badge-info { background: #e0f2fe; color: #0284c7; }
    .badge-red { background: #fee2e2; color: #dc2626; }
    .badge-yellow { background: #fef9c3; color: #b45309; }
    .num { font-size: 24px; font-weight: 600; color: #0f172a; line-height: 1.2; margin-bottom: 4px; }
    .num-pct { font-size: 16px; font-weight: 600; color: #64748b; margin-left: 4px; }
    .sub { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
    .footer { background: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 14px 16px; text-align: center; font-size: 10px; color: #94a3b8; }
    @media only screen and (max-width: 600px) {
      .email-shell { width: 100% !important; max-width: 100% !important; border-radius: 0 !important; }
      .header { padding: 20px 14px 16px !important; }
      .header h1 { font-size: 17px !important; }
      .body { padding: 16px 12px 20px !important; }
      .big-num { font-size: 30px !important; }
      .num { font-size: 20px !important; }
      .num-pct { font-size: 14px !important; }
      .email-stack td.stack-col { display: block !important; width: 100% !important; max-width: 100% !important; padding-left: 0 !important; padding-right: 0 !important; padding-bottom: 12px !important; }
      .email-stack td.stack-col:last-child { padding-bottom: 0 !important; }
      .stat-inner td { display: block !important; width: 100% !important; text-align: center !important; padding: 10px 12px !important; }
      .footer { padding: 12px 10px !important; font-size: 9px !important; }
    }
  </style>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#1e293b;">
  <table role="presentation" class="email-outer" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;">
    <tr>
      <td align="center" style="padding:12px 8px;">
        <table role="presentation" class="email-shell" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:16px;border:1px solid #e2e8f0;">
          <tr>
            <td class="header">
              <div class="header-eyebrow">Reporte de cartera</div>
              <h1>📅 Primeros Pagos — Lunes de Cierre</h1>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:12px;color:#bfdbfe;line-height:1.5;">
                <tr>
                  <td style="padding:0 0 6px 0;">Primer vencimiento: <strong style="color:#ffffff;">{$primerVencimiento}</strong></td>
                </tr>
                <tr>
                  <td style="padding:0;">Corte actual: <code class="mono" style="background:rgba(255,255,255,.2);color:#e0f2fe;border-radius:4px;padding:3px 8px;font-size:11px;">{$corteActual}</code></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td class="body">
              <table role="presentation" class="stat-banner" width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:20px;">
                <tr class="stat-inner">
                  <td class="mono" style="padding:14px 16px;vertical-align:middle;text-align:center;width:38%;" width="38%">
                    <div class="big-num">{$num($total)}</div>
                  </td>
                  <td style="padding:14px 16px;vertical-align:middle;text-align:left;">
                    <div style="font-size:15px;font-weight:600;color:#0f172a;margin-bottom:2px;">Registros totales</div>
                    <div class="label">Cartera activa en el corte</div>
                  </td>
                </tr>
              </table>

              <div class="block-card">
                <div class="block-card-title">🥚 Distribución de nacimiento</div>
                <table role="presentation" class="email-stack" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:separate;border-spacing:0;">
                  <tr>
                    <td class="stack-col" width="50%" style="width:50%;vertical-align:top;padding:0 6px 0 0;">
                      <div class="mini-card">
                        <div class="badge badge-green">✔ Current</div>
                        <div class="num mono">{$num($nacCurrent)}<span class="num-pct">({$pctNacCurrent}%)</span></div>
                        <div class="sub">nacieron</div>
                      </div>
                    </td>
                    <td class="stack-col" width="50%" style="width:50%;vertical-align:top;padding:0 0 0 6px;">
                      <div class="mini-card">
                        <div class="badge badge-red">🕐 1-7d</div>
                        <div class="num mono">{$num($nac17)}<span class="num-pct">({$pctNac17}%)</span></div>
                        <div class="sub">nacieron</div>
                      </div>
                    </td>
                  </tr>
                </table>
              </div>

              <div class="block-card" style="background:#f0f3f7;border:1px solid #d8dfe7;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:14px;">
                  <tr>
                    <td style="font-size:12px;font-weight:600;color:#000000;padding:0 0 6px 0;">📊 Distribución de corte</td>
                  </tr>
                  <tr>
                    <td style="font-size:11px;line-height:1.55;color:#64748b;padding:0;">
                      <span style="color:#6b7785;font-weight:600;">{$primerVencimiento}</span><br />
                      <span style="color:#475569;">Corte actual:</span>
                      <code class="mono" style="display:inline-block;max-width:100%;word-break:break-word;background:rgba(3,195,236,.12);color:#0b7285;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;margin-top:4px;">{$corteActual}</code>
                    </td>
                  </tr>
                </table>
                <table role="presentation" class="email-stack" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:separate;border-spacing:0;">
                  <tr>
                    <td class="stack-col" width="50%" style="width:50%;vertical-align:top;padding:0 6px 0 0;">
                      <div class="mini-card" style="background:#f0f3f7;border:1px solid #d8dfe7;">
                        <div class="badge badge-green">✔ Current</div>
                        <div class="num mono">{$num($corteRecuperados)}<span class="num-pct">({$pctCorteCurrent}%)</span></div>
                        <div class="sub">al corte</div>
                      </div>
                    </td>
                    <td class="stack-col" width="50%" style="width:50%;vertical-align:top;padding:0 0 0 6px;">
                      <div class="mini-card" style="background:#f0f3f7;border:1px solid #d8dfe7;">
                        <div class="badge badge-yellow" style="line-height:1.3;font-size:9px;">⏳ Pendientes primeros pagos</div>
                        <div class="num mono">{$num($cortePendientes)}<span class="num-pct">({$pctCortePend}%)</span></div>
                        <div class="sub">por recuperar</div>
                      </div>
                    </td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
          <tr>
            <td class="footer mono">
              {$nota} · Sistema de Cobranza · {$esc($generadoEn)}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * Plantilla de correo simplificada para «Primeros pagos — Semana actual» (tbl_segundometro_primeros_pagos).
     *
     * @param array<string, mixed> $r Payload desde buildCorreoPayload (vista simple).
     */
    private function buildCorreoVencimientosPrimerosPagosSemanaHtml(array $r): string
    {
        $num = static function ($v): string {
            return number_format((float)$v, 0, '.', ',');
        };
        $esc = static function ($v): string {
            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        };

        $primerVencimiento = $esc($r['primer_vencimiento'] ?? '—');
        $generadoEn        = isset($r['generado_en']) ? date('Y-m-d', strtotime((string)$r['generado_en'])) : date('Y-m-d');
        $totalTabla        = (float)($r['total_en_tabla'] ?? $r['total_registros'] ?? 0);
        $nota              = $esc($r['nota'] ?? 'Generado automáticamente');

        return <<<HTML
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8" />
  <title>Primeros pagos — Semana actual</title>
</head>
<body style="margin:0;padding:16px;background:#f0f4f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#1e293b;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
    <tr>
      <td style="background:#1d4ed8;padding:20px 18px;color:#fff;">
        <div style="font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#bfdbfe;margin-bottom:6px;">Reporte</div>
        <h1 style="margin:0;font-size:18px;line-height:1.35;">Primeros pagos — Semana actual</h1>
        <p style="margin:10px 0 0;font-size:12px;color:#e0e2fe;line-height:1.5;">
          Primer vencimiento: <strong style="color:#fff;">{$primerVencimiento}</strong>
        </p>
      </td>
    </tr>
    <tr>
      <td style="padding:20px 18px;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-align:center;">
          <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Nacimiento · Total de registros</div>
          <div style="font-size:32px;font-weight:700;color:#1d4ed8;">{$num($totalTabla)}</div>
        </div>
        <p style="margin:16px 0 0;font-size:11px;color:#94a3b8;text-align:center;">{$nota} · {$esc($generadoEn)}</p>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * Excel: detalle reciente (dictamen enviado) — misma base que estadísticas Sabueso.
     * Hasta 2000 filas para export; la vista en pantalla sigue usando el límite habitual vía API.
     */
    public function descargarReporteSabuesosEstadisticasDetalle()
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }

            $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
            if ($usuarioId < 1) {
                http_response_code(401);
                echo json_encode(['error' => 'Sesión no válida']);
                exit;
            }

            $datos = \Models\Ticket::getEstadisticasTickets(['detalle_limit' => 2000]);
            if (empty($datos['success'])) {
                http_response_code(404);
                echo json_encode(['error' => $datos['mensaje'] ?? 'No hay datos']);
                exit;
            }

            $detalle = $datos['detalle_timings'] ?? [];
            if (!is_array($detalle)) {
                $detalle = [];
            }

            $columnas = [
                \PHPSpreadsheet::ColumnaExcel('folio', 'FOLIO'),
                \PHPSpreadsheet::ColumnaExcel('creador_nombre', 'QUIÉN LEVANTÓ'),
                \PHPSpreadsheet::ColumnaExcel('creador_id', 'ID GESTOR (CREADOR)'),
                \PHPSpreadsheet::ColumnaExcel('asignado_nombre', 'ASIGNADO A (SABUESO)'),
                \PHPSpreadsheet::ColumnaExcel('fecha_creacion', 'LEVANTADO'),
                \PHPSpreadsheet::ColumnaExcel('dictamen_fecha_envio', 'DICTAMEN ENVIADO'),
                \PHPSpreadsheet::ColumnaExcel('dictamen_fecha_visto', 'VISTO POR GESTOR'),
                \PHPSpreadsheet::ColumnaExcel('pct_efectividad', '% EFECTIVIDAD'),
                \PHPSpreadsheet::ColumnaExcel('medidas_preventivas', 'MEDIDAS PREVENTIVAS'),
                \PHPSpreadsheet::ColumnaExcel('cumplimiento_etiqueta', 'CUMPLIMIENTO ETIQUETA'),
                \PHPSpreadsheet::ColumnaExcel('dictamen_sistema_resultado', 'RESULTADO DS'),
            ];

            $fmt = function ($v) {
                if ($v === null || $v === '') {
                    return '—';
                }
                if (is_numeric($v)) {
                    return $v;
                }
                $t = strtotime((string)$v);
                if ($t !== false && strlen((string)$v) >= 10) {
                    return date('Y-m-d H:i', $t);
                }
                return (string)$v;
            };

            $datosFormateados = [];
            foreach ($detalle as $r) {
                $pct = $r['pct_efectividad'] ?? null;
                $datosFormateados[] = [
                    'folio' => $r['folio'] ?? '—',
                    'creador_nombre' => trim((string)($r['creador_nombre'] ?? '—')),
                    'creador_id' => isset($r['creador_id']) ? (int)$r['creador_id'] : '—',
                    'asignado_nombre' => trim((string)($r['asignado_nombre'] ?? '—')),
                    'fecha_creacion' => $fmt($r['fecha_creacion'] ?? null),
                    'dictamen_fecha_envio' => $fmt($r['dictamen_fecha_envio'] ?? null),
                    'dictamen_fecha_visto' => $fmt($r['dictamen_fecha_visto'] ?? null),
                    'pct_efectividad' => ($pct !== null && $pct !== '') ? $pct . '%' : '—',
                    'medidas_preventivas' => trim((string)($r['medidas_preventivas'] ?? '—')),
                    'cumplimiento_etiqueta' => trim((string)($r['cumplimiento_etiqueta'] ?? '—')),
                    'dictamen_sistema_resultado' => trim((string)($r['dictamen_sistema_resultado'] ?? '—')),
                ];
            }

            \PHPSpreadsheet::DescargaExcel(
                'Estadisticas_Detalle_Dictamen_' . date('Y-m-d_His'),
                'Detalle dictamen enviado',
                'Analítica sabueso',
                $columnas,
                $datosFormateados
            );
            exit;
        } catch (\Exception $e) {
            error_log('Error en descargarReporteSabuesosEstadisticasDetalle: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al generar el reporte: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Scripts del tablero Asignación (cfg inyectada + JS en public/assets/js/reporteria/).
     */
    private static function htmlScriptBundleAsignacionTablero(string $cfgJson): string
    {
        static $cache = null;
        if ($cache === null) {
            $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'reporteria';
            $cache = [
                'main' => (string) file_get_contents($dir . DIRECTORY_SEPARATOR . 'ReporteriaAsignacionTablero.frontend.js'),
                'excel' => (string) file_get_contents($dir . DIRECTORY_SEPARATOR . 'ReporteriaAsignacionTablero.excel.js'),
            ];
        }

        return '<script>' . "\n"
            . '(function () {' . "\n"
            . '    var cfg = ' . $cfgJson . ";\n"
            . $cache['main']
            . '})();' . "\n"
            . '</script>' . "\n"
            . '<script>' . "\n"
            . $cache['excel']
            . "\n</script>\n";
    }

} // ← ¡ESTA ES LA ÚNICA LLAVE DE CIERRE DE LA CLASE!
