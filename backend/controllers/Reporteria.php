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

            const dtLang = {
                decimal:',', thousands:'.',
                emptyTable:'Sin registros',
                info:'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty:'0 registros',
                infoFiltered:'(de _MAX_ totales)',
                lengthMenu:'Mostrar _MENU_',
                loadingRecords:'Cargando...', processing:'Procesando...',
                search:'', searchPlaceholder:'Buscar en tabla...',
                zeroRecords:'Sin coincidencias',
                paginate: { first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior' },
                aria: {
                    sortAscending:  ': ordenar ascendente',
                    sortDescending: ': ordenar descendente'
                }
            };

            let _data  = [];
            let dtVenc = null;

            const BUCKET_BADGE = {
                'Al corriente': 'bg-label-success',
                'M1':  'bg-label-info',
                'M2':  'bg-label-warning',
                'M3':  'bg-label-danger',
                'M4+': 'bg-label-danger',
            };

            function badgeBucket(val) {
                const v = val || '—';
                const cls = BUCKET_BADGE[v] ?? 'bg-label-secondary';
                return `<span class="badge ${cls}">${v}</span>`;
            }

            /* ── DataTable ── */
            function initDT() {
                if ($.fn.DataTable.isDataTable('#tablaVencimientos')) {
                    $('#tablaVencimientos').DataTable().destroy();
                }
                dtVenc = $('#tablaVencimientos').DataTable({
                    processing: true, responsive: true, pageLength: 25,
                    order: [[0, 'asc']],
                    language: dtLang,
                    columns: [
                        { data:'id_credito',   width:'110px' },
                        { data:'nombre',       width:'180px' },
                        { data:'bucket',       className:'text-center', width:'120px' },
                        { data:'bucket_real',  className:'text-center', width:'120px' },
                        { data:'bucket_final', className:'text-center', width:'120px' },
                        { data:'gestor',       width:'140px' },
                        { data:'jefe_plaza',   width:'140px' },
                        { data:'zonal',        width:'110px' },
                        { data:'territorial',  width:'120px' },
                        { data:'cuotas_venc',  className:'text-center', width:'90px' },
                        { data:'saldo',        className:'text-end',    width:'120px' },
                    ]
                });
            }

            /* ── Stats ── */
            function actualizarStats(data) {
                document.getElementById('statTotal').textContent = data.length;
                const totalSaldo = data.reduce((a, r) => a + parseFloat(r._saldo_raw || 0), 0);
                document.getElementById('statSaldo').textContent =
                    '$' + totalSaldo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            /* ── Poblar filtros dinámicos ── */
            function poblarFiltros(data) {
                const selBucket  = document.getElementById('fBucket');
                const selGestor  = document.getElementById('fGestor');

                const buckets  = [...new Set(data.map(r => r.Bucket_Morosidad_Final || '').filter(Boolean))].sort();
                const gestores = [...new Set(data.map(r => r.Gestor_Asignado || '').filter(Boolean))].sort();

                /* Limpiar y repoblar conservando la selección actual */
                const curBucket = selBucket.value;
                const curGestor = selGestor.value;

                selBucket.innerHTML = '<option value="">Todos los buckets</option>';
                buckets.forEach(b => {
                    const o = document.createElement('option');
                    o.value = b; o.textContent = b;
                    if (b === curBucket) o.selected = true;
                    selBucket.appendChild(o);
                });

                selGestor.innerHTML = '<option value="">Todos los gestores</option>';
                gestores.forEach(g => {
                    const o = document.createElement('option');
                    o.value = g; o.textContent = g;
                    if (g === curGestor) o.selected = true;
                    selGestor.appendChild(o);
                });
            }

            /* ── Cargar datos desde API ── */
            async function cargar() {
                ['statTotal', 'statSaldo']
                    .forEach(id => document.getElementById(id).textContent = '…');
                try {
                    const r = await fetch('/Reporteria/getVencimientosLunes', { method: 'POST' });
                    const d = await r.json();
                    _data = d.datos || [];

                    if (d.lunes_pasado) {
                        document.getElementById('lunesFecha').textContent = d.lunes_pasado;
                    }

                    poblarFiltros(_data);
                    renderTabla();
                } catch(e) { console.error(e); }
            }

            /* ── Render + filtros cliente ── */
            function renderTabla() {
                const fBucket = document.getElementById('fBucket').value;
                const fGestor = document.getElementById('fGestor').value;

                let datos = _data;
                if (fBucket) datos = datos.filter(r => (r.Bucket_Morosidad_Final || '') === fBucket);
                if (fGestor) datos = datos.filter(r => (r.Gestor_Asignado       || '') === fGestor);

                actualizarStats(datos);
                initDT();

                const rows = datos.map(r => {
                    const saldo = parseFloat(r.Saldo_vencido_actualizado || 0);
                    return {
                        _saldo_raw:   saldo,
                        id_credito:   `<span class="badge bg-label-secondary">${r.Id_credito || '—'}</span>`,
                        nombre:       `<span class="fw-semibold" style="font-size:.82rem;">${r.Nombre_cliente || '—'}</span>`,
                        bucket:       badgeBucket(r.Bucket_Morosidad),
                        bucket_real:  badgeBucket(r.Bucket_Morosidad_Real),
                        bucket_final: badgeBucket(r.Bucket_Morosidad_Final),
                        gestor:       `<span style="font-size:.75rem;" class="text-muted">${r.Gestor_Asignado || '—'}</span>`,
                        jefe_plaza:   `<span style="font-size:.75rem;" class="text-muted">${r.Jefe_de_Plaza   || '—'}</span>`,
                        zonal:        `<span style="font-size:.75rem;" class="text-muted">${r.Zonal           || '—'}</span>`,
                        territorial:  `<span style="font-size:.75rem;" class="text-muted">${r.Territorial     || '—'}</span>`,
                        cuotas_venc:  `<span class="fw-bold text-danger">${r.Cuotas_vencidas || '—'}</span>`,
                        saldo:        `<span class="fw-semibold text-warning" style="font-size:.82rem;">
                                          $${saldo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                                       </span>`,
                    };
                });

                dtVenc.clear().rows.add(rows).draw();
            }

            /* ── Exportar CSV ── */
            document.getElementById('btnExportarCSV').addEventListener('click', () => {
                const fBucket = document.getElementById('fBucket').value;
                const fGestor = document.getElementById('fGestor').value;
                let datos = _data;
                if (fBucket) datos = datos.filter(r => (r.Bucket_Morosidad_Final || '') === fBucket);
                if (fGestor) datos = datos.filter(r => (r.Gestor_Asignado        || '') === fGestor);

                const headers = [
                    'Id_credito','Nombre_cliente',
                    'Bucket_Morosidad','Bucket_Morosidad_Real','Bucket_Morosidad_Final',
                    'Gestor_Asignado','Jefe_de_Plaza','Zonal','Territorial',
                    'Cuotas_vencidas','Saldo_vencido_actualizado'
                ];
                const rows = datos.map(r => [
                    r.Id_credito, r.Nombre_cliente,
                    r.Bucket_Morosidad, r.Bucket_Morosidad_Real, r.Bucket_Morosidad_Final,
                    r.Gestor_Asignado, r.Jefe_de_Plaza, r.Zonal, r.Territorial,
                    r.Cuotas_vencidas, r.Saldo_vencido_actualizado
                ]);
                const csv = [headers, ...rows].map(r => r.map(v => `"${v ?? ''}"`).join(',')).join('\n');
                const a   = document.createElement('a');
                a.href    = URL.createObjectURL(new Blob([csv], { type:'text/csv' }));
                a.download = `vencimientos_lunes_${new Date().toISOString().substring(0,10)}.csv`;
                a.click();
            });

            /* ── Eventos filtros ── */
            document.getElementById('fBucket').addEventListener('change', renderTabla);
            document.getElementById('fGestor').addEventListener('change', renderTabla);

            document.getElementById('btnReset').addEventListener('click', () => {
                document.getElementById('fBucket').value = '';
                document.getElementById('fGestor').value = '';
                renderTabla();
            });

            /* ── Inicio ── */
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
            self::respuestaJSON(EmpresaDAO::getVencimientosLunes());
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
