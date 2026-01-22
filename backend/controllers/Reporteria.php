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
            \PHPSpreadsheet::ColumnaExcel('ID ORIGINAL', 'id_original'),
            \PHPSpreadsheet::ColumnaExcel('TELEFONO', 'Telefono'),
            \PHPSpreadsheet::ColumnaExcel('FIDEICOMISO', 'fideicomiso'),
            \PHPSpreadsheet::ColumnaExcel('MKM', 'mkm'),
            \PHPSpreadsheet::ColumnaExcel('ID CREDITO', 'id_credit'),
            \PHPSpreadsheet::ColumnaExcel('NOMBRE', 'nombre'),
            \PHPSpreadsheet::ColumnaExcel('PAGOS VENCIDOS', 'pagos_vencidos'),
            \PHPSpreadsheet::ColumnaExcel('MONTO VENCIDO', 'monto_vencido', ['estilo' => \PHPSpreadsheet::GetEstilosExcel('moneda')]),
            \PHPSpreadsheet::ColumnaExcel('BUCKET', 'bucket'),
            \PHPSpreadsheet::ColumnaExcel('FECHA DE PAGO', 'fecha_de_pago'),
            \PHPSpreadsheet::ColumnaExcel('TELEFONO 1', 'telefono_1'),
            \PHPSpreadsheet::ColumnaExcel('TIPO DE PAGO', 'tipoo_de_pago'),
            \PHPSpreadsheet::ColumnaExcel('CLABE', 'clabe'),
            \PHPSpreadsheet::ColumnaExcel('BANCO', 'banco'),
            \PHPSpreadsheet::ColumnaExcel('ATRIBUTO SEGMENTO', 'atributo_segmento'),
            \PHPSpreadsheet::ColumnaExcel('NOMBRE COMPLETO', 'nombre_completo'),
            \PHPSpreadsheet::ColumnaExcel('NOMBRE REFERENCIA 1', 'nombre_completo_referencia1'),
            \PHPSpreadsheet::ColumnaExcel('TELEFONO REFERENCIA 1', 'telefono_referencia1'),
            \PHPSpreadsheet::ColumnaExcel('NOMBRE REFERENCIA 2', 'nombre_completo_referencia2'),
            \PHPSpreadsheet::ColumnaExcel('TELEFONO REFERENCIA 2', 'telefono_referencia2'),
            \PHPSpreadsheet::ColumnaExcel('NOMBRE REFERENCIA 3', 'nombre_referencia_3'),
            \PHPSpreadsheet::ColumnaExcel('TELEFONO REFERENCIA 3', 'telefono_referencia_3'),
            \PHPSpreadsheet::ColumnaExcel('MOTIVO DE NO PAGO', 'Motivo_de_no_Pago'),
            \PHPSpreadsheet::ColumnaExcel('CUANDO LE PAGAN', 'cuando_le_pagan'),
            \PHPSpreadsheet::ColumnaExcel('GIRO DE TRABAJO', 'Giro_de_Trabajo'),
            \PHPSpreadsheet::ColumnaExcel('HORA DE PAGO', 'hora_de_pago')
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
