<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;

class Reporteria extends Controller
{
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
    public function bonoscobranza()
    {
        $script = <<<HTML
            <script>
                const tabla = "#historialSolicitudes"
                const getSolicitudes = () => {
                    
                    const parametros = {
                        usuario: $_SESSION[usuario_id]
                    }

                    consultaServidor("/Empresas/ConsultaDepartamentos", parametros, (respuesta) => {
                        if (!respuesta.success) return showError(respuesta.mensaje)  
                        
                        const datos = respuesta.datos.map(empresas => {
                            return [
                                 null,
                                empresas.NOMBRE,
                                empresas.RFC,
                                empresas.RAZON_SOCIAL,
                                empresas.ESTATUS
                            ]
                        });

                        actualizaDatosTabla(tabla, datos)
                      
                    })
                }
                
                
                $(document).ready(() => {
                    
                    configuraTabla(tabla)
                    getSolicitudes()
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

}
