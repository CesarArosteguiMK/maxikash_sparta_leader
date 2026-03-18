<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;

class Reporteria extends Controller
{
    public function reporteCapitalHumano()
    {
        $script = "";
        self::set("titulo", "Reporte CH");
        self::set("script", $script);
        self::render("reporte_capital_humano");
    }

    // ==== Reporte de Capital Humano ====
    public function getUsuariosCapitalHumano()
{
    // No activar display_errors en producción; los errores se registran en log del servidor
    // Limpiar cualquier salida previa
    while (ob_get_level()) ob_end_clean();

    try {
        $tieneDepartamento = in_array(10, $_SESSION['modulos'] ?? []);
        $resultado = \Models\CapHum::getConsultaGestoresAll($_SESSION['usuario_id'], $tieneDepartamento);
        $usuarios = $resultado['datos'] ?? [];

        $datos = array_map(function($p) {
            return [
                'id' => $p['id'] ?? '',
                'numero_empleado' => $p['numero_empleado'] ?? '',
                'nombre_jefe' => $p['nombre_jefe'] ?? '',
                'nombres' => $p['nombres'] ?? '',
                'segundo_nombre' => $p['segundo_nombre'] ?? '',
                'apellidop' => $p['apellidop'] ?? '',
                'apellidom' => $p['apellidom'] ?? '',
                'nombre_departamento' => $p['nombre_departamento'] ?? '',
                'nombre_puesto' => $p['nombre_puesto'] ?? '',
                'id_puesto' => $p['id_puesto'] ?? null,
                'id_departamento' => $p['id_departamento'] ?? null,
                'estatus' => $p['estatus'] ?? '',
                'usuario' => $p['usuario'] ?? '',
            ];
        }, $usuarios);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'datos' => $datos
        ]);
        exit;

    } catch (\Exception $e) {
        error_log('Error en getUsuariosCapitalHumano: ' . $e->getMessage());
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'datos' => [],
            'mensaje' => 'Error al cargar usuarios: ' . $e->getMessage()
        ]);
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
                $nombreCompleto = trim(($baja['nombres'] ?? '') . ' ' . ($baja['apellidop'] ?? '') . ' ' . ($baja['apellidom'] ?? ''));
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
     * Obtener filtros dinámicos para el reporte de Capital Humano
     * GET /Reporteria/getFiltrosCapitalHumano
     */
    /**
 * Obtener filtros dinámicos para el reporte de Capital Humano
 * GET /Reporteria/getFiltrosCapitalHumano
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
     * GET /Reporteria/descargarUsuariosExcelCapitalHumano
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

                $data[] = [
                    'numero_empleado' => $u['numero_empleado'] ?? '',
                    'nombre_completo' => $nombreCompleto,
                    'telefono' => $u['telefono'] ?? '',
                    'departamento' => $u['nombre_departamento'] ?? 'N/A',
                    'puesto' => $u['nombre_puesto'] ?? 'N/A',
                    'estatus' => $u['estatus'] ?? 'N/A',
                    'usuario' => $u['usuario'] ?? 'N/A',
                    'jefe' => $u['nombre_jefe'] ?? 'N/A'
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
                \PHPSpreadsheet::ColumnaExcel('jefe', 'JEFE INMEDIATO')
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

            // Descargar Excel
            \PHPSpreadsheet::DescargaExcel(
                $nombreArchivo,
                "Reporte_Capital_Humano",
                "Usuarios",
                $columnas,
                $data
            );

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

    public function resumencallcenter()
    {
        $script = <<<'HTML'
            <script>
            document.getElementById('btn-ultimo-corte').addEventListener('click', function(e) {
            e.preventDefault();

            // Mostrar SweetAlert de carga
            Swal.fire({
                title: 'Consultando Último Corte...',
                text: 'Por favor espera',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('/Reporteria/getUltimoCorte', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'ultimo_corte' })
            })
            .then(resp => resp.json())
            .then(data => {
                Swal.close();

                const nombreColumna = data?.datos?.columna || "";
                if (!nombreColumna) {
                    Swal.fire({
                        title: 'Sin cortes disponibles',
                        text: 'No se encontró ningún corte cargado.',
                        icon: 'warning'
                    });
                    return;
                }

                // Confirmación de descarga
                Swal.fire({
                    html: `<p>El último corte disponible es:</p><strong>${nombreColumna}</strong><br><br>¿Deseas descargarlo?`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, descargar',
                    cancelButtonText: 'No'
                }).then(result => {
                    if (result.isConfirmed) {
                        Swal.fire({
                        title: 'Generando archivo...',
                        text: `Por favor espera mientras se descarga el Excel ${nombreColumna}`,
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });

                        // Setear el valor en el input hidden y enviar el form
                        document.getElementById('input-columna').value = nombreColumna;
                        document.getElementById('form-descarga').submit();

                         // Cerrar SweetAlert automáticamente después de 5 segundos por seguridad
                        setTimeout(() => Swal.close(), 90000);
                    }
                });
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo comunicar con el servidor.',
                    icon: 'error'
                });
            });
        });

            </script>
HTML;

        self::set("titulo", "Resumen Call Center");
        self::set("script", $script);
        self::render("reporteria_call_center");
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

        // Columnas completas según tu var_dump
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
            header('Location: /Reporteria/layoutlegacy?error=' . urlencode('No se pudieron obtener los datos del reporte Legacy.'));
            exit;
        }

        $data = $r['datos'];

        // Columnas completas según tu var_dump
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
            $filtroDepartamento = $_GET['departamento'] ?? '';
            $filtroPuesto = $_GET['puesto'] ?? '';
            $filtroEstatus = $_GET['estatus'] ?? '';

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

            // Columnas usando SOLO los campos disponibles en el modelo actual
            $columnas = [
                \PHPSpreadsheet::ColumnaExcel('numero_empleado', 'NO. EMPLEADO'),
                \PHPSpreadsheet::ColumnaExcel('nombres', 'NOMBRES'),
                \PHPSpreadsheet::ColumnaExcel('segundo_nombre', 'SEGUNDO NOMBRE'),
                \PHPSpreadsheet::ColumnaExcel('apellidop', 'APELLIDO PATERNO'),
                \PHPSpreadsheet::ColumnaExcel('apellidom', 'APELLIDO MATERNO'),
                \PHPSpreadsheet::ColumnaExcel('usuario', 'USUARIO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_departamento', 'DEPARTAMENTO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_puesto', 'PUESTO'),
                \PHPSpreadsheet::ColumnaExcel('nombre_jefe', 'JEFE INMEDIATO'),
                \PHPSpreadsheet::ColumnaExcel('estatus', 'ESTATUS'),
            ];

            // Preparar datos formateados
            $datosFormateados = [];
            foreach ($datosFiltrados as $gestor) {
                $datosFormateados[] = [
                    'numero_empleado' => $gestor['numero_empleado'] ?? '',
                    'nombres' => $gestor['nombres'] ?? '',
                    'segundo_nombre' => $gestor['segundo_nombre'] ?? '',
                    'apellidop' => $gestor['apellidop'] ?? '',
                    'apellidom' => $gestor['apellidom'] ?? '',
                    'usuario' => $gestor['usuario'] ?? '',
                    'nombre_departamento' => $gestor['nombre_departamento'] ?? '',
                    'nombre_puesto' => $gestor['nombre_puesto'] ?? '',
                    'nombre_jefe' => $gestor['nombre_jefe'] ?? '',
                    'estatus' => $gestor['estatus'] ?? '',
                ];
            }

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

    public function VencimientosLunes()
    {
        $script = <<<'HTML'
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ── Paleta de buckets ──────────────────────────────────────
        const BUCKET_META = {
            'a) Current':      { cls: 'bg-label-success',   icon: 'fa-circle-check',         short: 'Current'  },
            'b) 1 a 7 dias':   { cls: 'bg-label-info',      icon: 'fa-clock',                short: '1-7d'     },
            'c) 8 a 30 dias':  { cls: 'bg-label-warning',   icon: 'fa-triangle-exclamation', short: '8-30d'    },
            'd) 31 a 60 dias': { cls: 'bg-label-danger',    icon: 'fa-fire',                 short: '31-60d'   },
            'e) 61+ dias':     { cls: 'bg-label-secondary', icon: 'fa-skull-crossbones',     short: '61+d'     },
        };
        const BUCKET_ORDER = Object.keys(BUCKET_META);

        function badgeBucket(val, small = false) {
            const v   = val || '—';
            const m   = BUCKET_META[v] ?? { cls: 'bg-label-secondary', icon: 'fa-question', short: v };
            const sz  = small ? 'font-size:.68rem;' : '';
            return `<span class="badge ${m.cls}" style="${sz}">
                        <i class="fa ${m.icon} me-1"></i>${small ? m.short : v}
                    </span>`;
        }

        function movimientoHtml(nacio, actual) {
            if (!nacio || !actual) return '<span class="text-muted">—</span>';
            const iN = BUCKET_ORDER.indexOf(nacio);
            const iA = BUCKET_ORDER.indexOf(actual);
            if (iN === iA) return `<span class="text-muted" title="Sin cambio"><i class="fa fa-equals"></i></span>`;
            if (iA < iN)   return `<span class="text-success" title="Mejoró"><i class="fa fa-arrow-up"></i></span>`;
            return             `<span class="text-danger"  title="Empeoró"><i class="fa fa-arrow-down"></i></span>`;
        }

        const dtLang = {
            decimal: ',', thousands: '.',
            emptyTable: 'Sin registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_',
            infoEmpty: '0 registros', infoFiltered: '(de _MAX_)',
            lengthMenu: 'Mostrar _MENU_', loadingRecords: 'Cargando...',
            processing: 'Procesando...', search: '',
            searchPlaceholder: 'Buscar...', zeroRecords: 'Sin coincidencias',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
        };

        let _data        = [];
        let dtVenc       = null;
        let _corteActual = '';

        // ══════════════════════════════════════════════════════════
        //  DATATABLE
        // ══════════════════════════════════════════════════════════
        function initDT() {
            if ($.fn.DataTable.isDataTable('#tablaVencimientos'))
                $('#tablaVencimientos').DataTable().destroy();

            dtVenc = $('#tablaVencimientos').DataTable({
                processing: true, responsive: true, pageLength: 25,
                order: [[0, 'asc']], language: dtLang,
                columns: [
                    { data: 'general',   width: '210px' },
                    { data: 'jerarquia', width: '230px', orderable: false },
                    { data: 'nacio',     className: 'text-center', width: '130px' },
                    { data: 'corte',     className: 'text-center', width: '170px' },
                ]
            });
        }

        // ══════════════════════════════════════════════════════════
        //  STATS — distribución y matriz
        // ══════════════════════════════════════════════════════════
        function calcStats(data) {
            const nacDist = {};
            BUCKET_ORDER.forEach(b => nacDist[b] = 0);
            data.forEach(r => { if (nacDist[r.bucket_nacio] !== undefined) nacDist[r.bucket_nacio]++; });

            const matriz = {};
            BUCKET_ORDER.forEach(b => {
                matriz[b] = {};
                BUCKET_ORDER.forEach(c => matriz[b][c] = 0);
            });
            data.forEach(r => {
                const n = r.bucket_nacio;
                const c = r.bucket_corte_actual;
                if (n && c && matriz[n] !== undefined) {
                    matriz[n][c] = (matriz[n][c] || 0) + 1;
                }
            });

            return { nacDist, matriz };
        }

        function renderStats(data) {
            const { nacDist, matriz } = calcStats(data);

            // Cards nacimiento
            let htmlNac = '';
            BUCKET_ORDER.forEach(b => {
                const m   = BUCKET_META[b] ?? {};
                const cnt = nacDist[b] || 0;
                htmlNac += `
                <div class="col">
                    <div class="card text-center h-100 border-0 shadow-sm">
                        <div class="card-body py-2 px-2">
                            <div class="badge ${m.cls} mb-1" style="font-size:.65rem;">
                                <i class="fa ${m.icon} me-1"></i>${m.short}
                            </div>
                            <div class="fw-bold" style="font-size:1.5rem;">${cnt}</div>
                            <div class="text-muted" style="font-size:.65rem;">nacieron</div>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('statsNacimiento').innerHTML = htmlNac;

            // Matriz movimiento
            let htmlMat = `
            <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0" style="font-size:.75rem;">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:120px;">Nació \\ Corte</th>
                        ${BUCKET_ORDER.map(b => `<th class="text-center">${BUCKET_META[b]?.short || b}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>`;

            BUCKET_ORDER.forEach(b => {
                const total = BUCKET_ORDER.reduce((a, c) => a + (matriz[b][c] || 0), 0);
                if (!total) return;
                htmlMat += `<tr><td>${badgeBucket(b, true)}</td>`;
                BUCKET_ORDER.forEach(c => {
                    const v   = matriz[b][c] || 0;
                    const iB  = BUCKET_ORDER.indexOf(b);
                    const iC  = BUCKET_ORDER.indexOf(c);
                    let cls   = '';
                    if (v > 0) {
                        if (iC < iB)      cls = 'table-success';
                        else if (iC > iB) cls = 'table-danger';
                        else              cls = 'table-secondary';
                    }
                    htmlMat += `<td class="text-center ${cls}">${v || '—'}</td>`;
                });
                htmlMat += `</tr>`;
            });

            htmlMat += `</tbody></table></div>`;
            document.getElementById('statsMatriz').innerHTML = htmlMat;

            renderStatsJerarquia(data);
        }

        // ══════════════════════════════════════════════════════════
        //  STATS JERARQUÍA — ranking de gestión
        // ══════════════════════════════════════════════════════════
        function renderStatsJerarquia(data) {
            const territoriales = {};

            data.forEach(r => {
                const ter  = r.Territorial     || '(Sin territorial)';
                const zon  = r.Zonal           || '(Sin zonal)';
                const jefe = r.Jefe_de_Plaza   || '(Sin jefe)';
                const gest = r.Gestor_Asignado || '(Sin gestor)';

                if (!territoriales[ter])
                    territoriales[ter] = { total: 0, mejoraron: 0, empeoraron: 0, igual: 0, zonales: {} };
                const T = territoriales[ter]; T.total++;

                if (!T.zonales[zon])
                    T.zonales[zon] = { total: 0, mejoraron: 0, empeoraron: 0, igual: 0, jefes: {} };
                const Z = T.zonales[zon]; Z.total++;

                if (!Z.jefes[jefe])
                    Z.jefes[jefe] = { total: 0, mejoraron: 0, empeoraron: 0, igual: 0, gestores: {} };
                const J = Z.jefes[jefe]; J.total++;

                if (!J.gestores[gest])
                    J.gestores[gest] = { total: 0, mejoraron: 0, empeoraron: 0, igual: 0 };
                const G = J.gestores[gest]; G.total++;

                const iN = BUCKET_ORDER.indexOf(r.bucket_nacio);
                const iA = BUCKET_ORDER.indexOf(r.bucket_corte_actual);
                if (iA >= 0 && iN >= 0) {
                    if (iA < iN)       { G.mejoraron++;  J.mejoraron++;  Z.mejoraron++;  T.mejoraron++;  }
                    else if (iA > iN)  { G.empeoraron++; J.empeoraron++; Z.empeoraron++; T.empeoraron++; }
                    else               { G.igual++;      J.igual++;      Z.igual++;      T.igual++;      }
                }
            });

            const pct = (a, b) => b ? Math.round(a / b * 100) : 0;

            const terOrdenados = Object.entries(territoriales)
                .map(([k, v]) => ({ nombre: k, ...v }))
                .sort((a, b) => pct(a.mejoraron, a.total) - pct(b.mejoraron, b.total));

            let html = '';

            terOrdenados.forEach((ter, idx) => {
                const pM      = pct(ter.mejoraron, ter.total);
                const pE      = pct(ter.empeoraron, ter.total);
                const alerta  = pM < 20 ? 'border-danger' : pM < 50 ? 'border-warning' : 'border-success';
                const icono   = pM < 20 ? 'fa-circle-exclamation text-danger'
                                        : pM < 50 ? 'fa-triangle-exclamation text-warning'
                                                  : 'fa-circle-check text-success';

                const zonOrdenados = Object.entries(ter.zonales)
                    .map(([k, v]) => ({ nombre: k, ...v }))
                    .sort((a, b) => pct(a.mejoraron, a.total) - pct(b.mejoraron, b.total));

                let htmlZon = '';

                zonOrdenados.forEach(zon => {
                    const pZ = pct(zon.mejoraron, zon.total);

                    const jefOrdenados = Object.entries(zon.jefes)
                        .map(([k, v]) => ({ nombre: k, ...v }))
                        .sort((a, b) => pct(a.mejoraron, a.total) - pct(b.mejoraron, b.total));

                    let htmlJef = '';

                    jefOrdenados.forEach(jef => {
                        const pJ = pct(jef.mejoraron, jef.total);

                        const gestOrdenados = Object.entries(jef.gestores)
                            .map(([k, v]) => ({ nombre: k, ...v }))
                            .sort((a, b) => pct(a.mejoraron, a.total) - pct(b.mejoraron, b.total));

                        let htmlGest = '';
                        gestOrdenados.forEach(gest => {
                            const pG = pct(gest.mejoraron, gest.total);
                            htmlGest += `
                            <tr>
                                <td style="padding-left:2.8rem;font-size:.74rem;">
                                    <i class="fa fa-user text-muted me-1"></i>${gest.nombre}
                                </td>
                                <td class="text-center">${gest.total}</td>
                                <td class="text-center text-success">${gest.mejoraron}</td>
                                <td class="text-center text-danger">${gest.empeoraron}</td>
                                <td class="text-center">
                                    <div class="progress d-inline-flex" style="height:5px;width:55px;vertical-align:middle;">
                                        <div class="progress-bar bg-success" style="width:${pG}%"></div>
                                    </div>
                                    <span class="ms-1" style="font-size:.68rem;">${pG}%</span>
                                </td>
                            </tr>`;
                        });

                        htmlJef += `
                        <tr class="table-light">
                            <td style="padding-left:1.8rem;font-size:.77rem;">
                                <i class="fa fa-user-tie text-primary me-1"></i>${jef.nombre}
                            </td>
                            <td class="text-center fw-semibold">${jef.total}</td>
                            <td class="text-center text-success fw-semibold">${jef.mejoraron}</td>
                            <td class="text-center text-danger fw-semibold">${jef.empeoraron}</td>
                            <td class="text-center">
                                <div class="progress d-inline-flex" style="height:5px;width:55px;vertical-align:middle;">
                                    <div class="progress-bar bg-success" style="width:${pJ}%"></div>
                                </div>
                                <span class="ms-1" style="font-size:.68rem;">${pJ}%</span>
                            </td>
                        </tr>${htmlGest}`;
                    });

                    htmlZon += `
                    <tr class="table-secondary">
                        <td style="padding-left:.9rem;font-size:.8rem;">
                            <i class="fa fa-map-location-dot text-info me-1"></i>${zon.nombre}
                        </td>
                        <td class="text-center fw-bold">${zon.total}</td>
                        <td class="text-center text-success fw-bold">${zon.mejoraron}</td>
                        <td class="text-center text-danger fw-bold">${zon.empeoraron}</td>
                        <td class="text-center">
                            <div class="progress d-inline-flex" style="height:6px;width:55px;vertical-align:middle;">
                                <div class="progress-bar bg-success" style="width:${pZ}%"></div>
                            </div>
                            <span class="ms-1" style="font-size:.68rem;">${pZ}%</span>
                        </td>
                    </tr>${htmlJef}`;
                });

                html += `
                <div class="card mb-3 border-start border-3 ${alerta}">
                    <div class="card-header d-flex align-items-center justify-content-between py-2"
                         style="cursor:pointer;"
                         data-bs-toggle="collapse"
                         data-bs-target="#ter_${idx}">
                        <div>
                            <i class="fa fa-globe me-2 text-muted"></i>
                            <strong>${ter.nombre}</strong>
                            <span class="badge bg-label-secondary ms-2">${ter.total} créditos</span>
                            <i class="fa ${icono} ms-2"></i>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <span class="text-success" style="font-size:.78rem;">
                                <i class="fa fa-arrow-up me-1"></i>${ter.mejoraron} mejoraron (${pM}%)
                            </span>
                            <span class="text-danger" style="font-size:.78rem;">
                                <i class="fa fa-arrow-down me-1"></i>${ter.empeoraron} empeoraron (${pE}%)
                            </span>
                            <i class="fa fa-chevron-down text-muted fa-xs"></i>
                        </div>
                    </div>
                    <div class="collapse" id="ter_${idx}">
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0 align-middle" style="font-size:.78rem;">
                                <thead class="table-dark" style="font-size:.7rem;">
                                    <tr>
                                        <th>Nivel</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Mejoraron</th>
                                        <th class="text-center">Empeoraron</th>
                                        <th class="text-center">% Gestión</th>
                                    </tr>
                                </thead>
                                <tbody>${htmlZon}</tbody>
                            </table>
                        </div>
                    </div>
                </div>`;
            });

            document.getElementById('statsJerarquia').innerHTML =
                html || '<p class="text-muted text-center py-3">Sin datos de jerarquía.</p>';
        }

        // ══════════════════════════════════════════════════════════
        //  ACORDEÓN JERARQUÍA en la tabla
        // ══════════════════════════════════════════════════════════
        function jerarquiaHtml(r, idx) {
            const ter  = r.Territorial     || null;
            const zon  = r.Zonal           || null;
            const jefe = r.Jefe_de_Plaza   || null;
            const gest = r.Gestor_Asignado || '—';

            const niveles = [];
            if (ter)  niveles.push({ icono: 'fa-globe',            cls: 'text-secondary', label: ter  });
            if (zon)  niveles.push({ icono: 'fa-map-location-dot', cls: 'text-info',      label: zon  });
            if (jefe) niveles.push({ icono: 'fa-user-tie',         cls: 'text-primary',   label: jefe });
            niveles.push(          { icono: 'fa-user',             cls: 'text-muted',     label: gest });

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
                    <i id="ico_${id}" class="fa fa-chevron-right fa-xs text-muted ms-1"></i>
                </div>
                <div id="${id}" style="display:none;padding-left:.8rem;margin-top:.3rem;
                                       border-left:2px solid #e0e0e0;">
                    ${niveles.slice(1).map(n => `
                        <div style="font-size:.72rem;margin-bottom:.15rem;">
                            <i class="fa ${n.icono} ${n.cls} me-1"></i>${n.label}
                        </div>`).join('')}
                </div>
            </div>`;
        }

        window.toggleJQ = function(id) {
            const el  = document.getElementById(id);
            const ico = document.getElementById(`ico_${id}`);
            if (!el) return;
            const open        = el.style.display !== 'none';
            el.style.display  = open ? 'none' : 'block';
            if (ico) ico.className = `fa fa-xs text-muted ms-1 ${open ? 'fa-chevron-right' : 'fa-chevron-down'}`;
        };

        // ══════════════════════════════════════════════════════════
        //  FILTROS
        // ══════════════════════════════════════════════════════════
        function poblarFiltros(data) {
            const campos = {
                fBucketNacio: r => r.bucket_nacio,
                fBucketCorte: r => r.bucket_corte_actual,
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
        }

        function getFiltros() {
            return {
                bucketNacio: document.getElementById('fBucketNacio')?.value || '',
                bucketCorte: document.getElementById('fBucketCorte')?.value || '',
                territorial: document.getElementById('fTerritorial')?.value || '',
                zonal:       document.getElementById('fZonal')?.value       || '',
                jefe:        document.getElementById('fJefe')?.value        || '',
                gestor:      document.getElementById('fGestor')?.value      || '',
                busq:       (document.getElementById('fBusq')?.value        || '').toLowerCase(),
                movimiento:  document.getElementById('fMovimiento')?.value  || '',
            };
        }

        function aplicarFiltros(data) {
            const f = getFiltros();
            return data.filter(r => {
                if (f.bucketNacio && r.bucket_nacio        !== f.bucketNacio) return false;
                if (f.bucketCorte && r.bucket_corte_actual !== f.bucketCorte) return false;
                if (f.territorial && r.Territorial         !== f.territorial) return false;
                if (f.zonal       && r.Zonal               !== f.zonal)       return false;
                if (f.jefe        && r.Jefe_de_Plaza        !== f.jefe)        return false;
                if (f.gestor      && r.Gestor_Asignado      !== f.gestor)      return false;
                if (f.busq) {
                    const h = `${r.Nombre_cliente} ${r.Id_credito}`.toLowerCase();
                    if (!h.includes(f.busq)) return false;
                }
                if (f.movimiento) {
                    const iN = BUCKET_ORDER.indexOf(r.bucket_nacio);
                    const iA = BUCKET_ORDER.indexOf(r.bucket_corte_actual);
                    if (f.movimiento === 'mejoro'   && !(iA < iN))  return false;
                    if (f.movimiento === 'empeoró'  && !(iA > iN))  return false;
                    if (f.movimiento === 'igual'    && !(iA === iN)) return false;
                }
                return true;
            });
        }

        // ══════════════════════════════════════════════════════════
        //  CARGAR DATOS
        // ══════════════════════════════════════════════════════════
        async function cargar() {
            document.getElementById('statTotal').textContent = '…';
            try {
                const r = await fetch('/Reporteria/getVencimientosLunes', { method: 'POST' });
                const d = await r.json();
                _data        = d.datos        || [];
                _corteActual = d.corte_actual || '';

                if (d.lunes_pasado)
                    document.getElementById('lunesFecha').textContent = d.lunes_pasado;
                if (_corteActual)
                    document.getElementById('corteLabel').textContent =
                        _corteActual.replace('Dias_mora_', '').replace(/_/g, ' ');

                poblarFiltros(_data);
                renderTabla();
                renderStats(_data);
            } catch(e) {
                console.error('Error cargando vencimientos:', e);
            }
        }

        // ══════════════════════════════════════════════════════════
        //  RENDER TABLA
        // ══════════════════════════════════════════════════════════
        function renderTabla() {
            const datos = aplicarFiltros(_data);
            document.getElementById('statTotal').textContent = datos.length;
            initDT();

            const fmt = v => '$' + parseFloat(v || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            const rows = datos.map((r, i) => {
                const saldo = parseFloat(r.Saldo_vencido_actualizado || 0);
                return {
                    general: `
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa fa-id-card text-primary mt-1" style="font-size:.9rem;"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:.82rem;">
                                    ${r.Nombre_cliente || '—'}
                                </div>
                                <div class="text-muted" style="font-size:.7rem;">
                                    <i class="fa fa-hashtag fa-xs me-1"></i>${r.Id_credito || ''}
                                </div>
                            </div>
                        </div>`,

                    jerarquia: jerarquiaHtml(r, i),

                    nacio: badgeBucket(r.bucket_nacio),

                    corte: `
                        <div>${badgeBucket(r.bucket_corte_actual)}</div>
                        <div class="mt-1 d-flex align-items-center justify-content-center gap-1"
                             style="font-size:.72rem;">
                            ${movimientoHtml(r.bucket_nacio, r.bucket_corte_actual)}
                            <span class="text-muted">mov.</span>
                        </div>
                        <div class="mt-1" style="font-size:.7rem;">
                            <i class="fa fa-receipt fa-xs text-muted me-1"></i>
                            <span class="text-danger fw-bold">${r.Cuotas_vencidas || '—'}</span>
                            <span class="text-muted ms-1">ctas</span>
                        </div>
                        <div style="font-size:.7rem;">
                            <i class="fa fa-dollar-sign fa-xs text-muted me-1"></i>
                            <span class="text-warning fw-semibold">${fmt(saldo)}</span>
                        </div>`,
                };
            });

            dtVenc.clear().rows.add(rows).draw();
        }

        // ══════════════════════════════════════════════════════════
        //  EXPORTAR CSV
        // ══════════════════════════════════════════════════════════
        document.getElementById('btnExportarCSV').addEventListener('click', () => {
            const datos   = aplicarFiltros(_data);
            const headers = [
                'Id_credito','Nombre_cliente',
                'Bucket_Nacio','Bucket_Corte_Actual',
                'Territorial','Zonal','Jefe_Plaza','Gestor_Asignado',
                'Cuotas_vencidas','Saldo_vencido_actualizado','Dias_mora_corte'
            ];
            const rows = datos.map(r => [
                r.Id_credito, r.Nombre_cliente,
                r.bucket_nacio, r.bucket_corte_actual,
                r.Territorial, r.Zonal, r.Jefe_de_Plaza, r.Gestor_Asignado,
                r.Cuotas_vencidas, r.Saldo_vencido_actualizado, r.dias_mora_corte
            ]);
            const csv = [headers, ...rows]
                .map(r => r.map(v => `"${v ?? ''}"`).join(','))
                .join('\n');
            const a       = document.createElement('a');
            a.href        = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
            a.download    = `vencimientos_lunes_${new Date().toISOString().substring(0, 10)}.csv`;
            a.click();
        });

        // ══════════════════════════════════════════════════════════
        //  EVENTOS
        // ══════════════════════════════════════════════════════════
        ['fBucketNacio','fBucketCorte','fTerritorial','fZonal','fJefe','fGestor','fMovimiento']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', () => {
                    renderTabla();
                    renderStats(aplicarFiltros(_data));
                });
            });

        document.getElementById('fBusq')?.addEventListener('input', renderTabla);

        document.getElementById('btnReset').addEventListener('click', () => {
            ['fBucketNacio','fBucketCorte','fTerritorial','fZonal','fJefe','fGestor','fMovimiento']
                .forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
            const busq = document.getElementById('fBusq');
            if (busq) busq.value = '';
            renderTabla();
            renderStats(_data);
        });

        // ── Inicio ────────────────────────────────────────────────
        cargar();
    });
    </script>
    HTML;

        self::set("titulo", "Vencimientos — Lunes de Cierre");
        self::set("script", $script);
        self::render("reporte_vencimientos_lunes");
    }
    public function getVencimientosLunes()
    {
        try {
            self::respuestaJSON(EmpresasDAO::getVencimientosLunes());
        } catch (\Exception $e) {
            self::respuestaJSON(["success" => false, "mensaje" => $e->getMessage()]);
        }
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
                'Estadísticas Sabueso',
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

} // ← ¡ESTA ES LA ÚNICA LLAVE DE CIERRE DE LA CLASE!
