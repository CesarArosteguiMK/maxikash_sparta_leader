<?php

namespace Controllers;

use Core\Controller;
use Models\Convenios as ConveniosDAO;

class Convenios extends Controller
{
    // ─────────────────────────────────────────────
    // VISTA PRINCIPAL
    // ─────────────────────────────────────────────

    public function consulta()
    {
        $this->render('convenios_consulta');
    }

    // ─────────────────────────────────────────────
    // API: BUSCAR CRÉDITO
    // ─────────────────────────────────────────────

    public function buscarCredito()
    {
        $termino = isset($_POST['termino']) ? trim($_POST['termino']) : '';

        if ($termino === '') {
            self::respuestaJSON(self::respuesta(false, 'Ingresa un nombre o ID de crédito.'));
        }

        $r = ConveniosDAO::buscarCredito($termino);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: OBTENER OFERTAS ELEGIBLES DE UN CRÉDITO
    // ─────────────────────────────────────────────

    public function getOfertasCredito()
    {
        $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
        }

        $r = ConveniosDAO::getOfertasElegibles($idCredito);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: GUARDAR CONVENIO
    // ─────────────────────────────────────────────

    public function guardarConvenio()
    {
        $campos = [
            'id_credito', 'id_producto_convenio', 'id_producto_convenio_detalle',
            'nombre_cliente', 'bucket_morosidad_real', 'dias_mora', 'avance_pago_plazo',
            'adeudo_total_original', 'porcentaje_descuento', 'descuento_monto',
            'total_a_pagar', 'numero_semanas', 'pago_semanal', 'fecha_acuerdo',
        ];

        $datos = [];
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                self::respuestaJSON(self::respuesta(false, "Campo requerido faltante: $campo"));
            }
            $datos[$campo] = $_POST[$campo];
        }

        // Pago inicial es opcional
        $datos['pago_inicial_monto'] = isset($_POST['pago_inicial_monto']) && $_POST['pago_inicial_monto'] !== ''
            ? (float) $_POST['pago_inicial_monto']
            : null;

       $datos['usuario_alta'] = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';

        $r = ConveniosDAO::guardarConvenio($datos);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: OBTENER CONVENIO ACTIVO
    // ─────────────────────────────────────────────

    public function getConvenioActivo()
    {
        $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
        }

        $r = ConveniosDAO::getConvenioActivo($idCredito);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
// API: CANCELAR CONVENIO
// ─────────────────────────────────────────────

public function cancelarConvenio()
{
    $idConvenio = isset($_POST['id_convenio']) ? (int) $_POST['id_convenio'] : 0;

    if ($idConvenio <= 0) {
        self::respuestaJSON(self::respuesta(false, 'ID de convenio inválido.'));
    }

    $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';

    $r = ConveniosDAO::cancelarConvenio($idConvenio, $usuario);
    self::respuestaJSON($r);
}
// ════════════════════════════════════════════════
// API: MIGRAR CONVENIO EXISTENTE
// ════════════════════════════════════════════════

// ════════════════════════════════════════════════
// API: MIGRAR CONVENIO CON PDF
// ════════════════════════════════════════════════

public function migrarConvenio()
{
    // Verificar si es multipart/form-data (con PDF)
    $esMultipart = !empty($_FILES['pdf_adjunto']['name']);

    if ($esMultipart) {
        // Procesar con archivo
        $campos = ['id_credito','nombre_cliente','id_producto_convenio',
                   'id_producto_convenio_detalle','adeudo_base',
                   'porcentaje_descuento','pago_semanal','fecha_inicio'];

        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                self::respuestaJSON(self::respuesta(false, "Campo requerido: $campo"));
                return;
            }
        }

        // Procesar PDF
        $pdfPath = null;
        if (!empty($_FILES['pdf_adjunto']['tmp_name'])) {
            $pdfPath = $this->_guardarPdfAdjunto($_FILES['pdf_adjunto'], $_POST['id_credito']);
            if (!$pdfPath) {
                self::respuestaJSON(self::respuesta(false, 'Error al guardar el PDF adjunto.'));
                return;
            }
        }

        $datos = array_merge($_POST, [
            'usuario_alta'        => $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema',
            'bucket_morosidad_real' => $_POST['bucket_morosidad_real'] ?? '',
            'dias_mora'           => $_POST['dias_mora'] ?? 0,
            'avance_pago_plazo'   => $_POST['avance_pago_plazo'] ?? '',
            'pdf_adjunto'         => $pdfPath,
        ]);
    } else {
        // Sin PDF (JSON normal)
        $campos = ['id_credito','nombre_cliente','id_producto_convenio',
                   'id_producto_convenio_detalle','adeudo_base',
                   'porcentaje_descuento','pago_semanal','fecha_inicio'];

        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                self::respuestaJSON(self::respuesta(false, "Campo requerido: $campo"));
                return;
            }
        }

        $datos = array_merge($_POST, [
            'usuario_alta'        => $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema',
            'bucket_morosidad_real' => $_POST['bucket_morosidad_real'] ?? '',
            'dias_mora'           => $_POST['dias_mora'] ?? 0,
            'avance_pago_plazo'   => $_POST['avance_pago_plazo'] ?? '',
        ]);
    }

    $r = ConveniosDAO::migrarConvenio($datos);
    self::respuestaJSON($r);
}

/**
 * Guarda PDF adjunto en el servidor
 */
private function _guardarPdfAdjunto($archivo, $idCredito)
{
    try {
        $directorio = $_SERVER['DOCUMENT_ROOT'] . '/uploads/convenios/';

        // Crear directorio si no existe
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'convenio_' . $idCredito . '_' . date('Ymd_His') . '.' . $extension;
        $rutaCompleta = $directorio . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return '/uploads/convenios/' . $nombreArchivo; // Ruta relativa para guardar en BD
        }

        return null;
    } catch (\Exception $e) {
        error_log("Error guardando PDF: " . $e->getMessage());
        return null;
    }
}

    // ─────────────────────────────────────────────
    // PDF: DESCARGAR TABLA DE AMORTIZACIÓN
    // ─────────────────────────────────────────────

    public function descargarPdf()
    {
        $idCredito  = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
        }

        $r = ConveniosDAO::getConvenioActivo($idCredito);

        if (!$r['success'] || !$r['datos']) {
            self::respuestaJSON(self::respuesta(false, 'No se encontró convenio activo para este crédito.'));
        }

        $convenio = $r['datos'];
        $amort    = $convenio['amortizacion'];

        $this->_generarPdf($convenio, $amort);
    }

    // ─────────────────────────────────────────────
    // GENERAR PDF (privado)
    // ─────────────────────────────────────────────

    private function _generarPdf($convenio, $amortizacion)
    {
        $nombreProducto = htmlspecialchars($convenio['nombre_producto'] ?? 'Convenio');
        $nombreCliente  = htmlspecialchars($convenio['nombre_cliente'] ?? '');
        $idCredito      = (int) $convenio['id_credito'];
        $fechaAcuerdo   = $convenio['fecha_acuerdo'];
        $adeudoOrig     = number_format((float) $convenio['adeudo_total_original'], 2);
        $descuento      = number_format((float) $convenio['descuento_monto'], 2);
        $totalPagar     = number_format((float) $convenio['total_a_pagar'], 2);
        $pagoSemanal    = number_format((float) $convenio['pago_semanal'], 2);
        $numSemanas     = (int) $convenio['numero_semanas'];
        $pagoInicial    = $convenio['pago_inicial_monto'] ? '$' . number_format((float) $convenio['pago_inicial_monto'], 2) : 'No aplica';

        // Filas de amortización

        $filasHtml = '';
        foreach ($amortizacion as $row) {
            $filasHtml .= '
            <tr>
                <td>Semana ' . (int) $row['numero_semana'] . '</td>
                <td>' . date('d/m/Y', strtotime($row['fecha_pago'])) . '</td>
                <td>$' . number_format((float) $row['pago_semanal'], 2) . '</td>
                <td>$' . number_format((float) $row['capital'], 2) . '</td>
                <td>$' . number_format((float) $row['saldo_restante'], 2) . '</td>
            </tr>';
        }

        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 20px; }
                h1 { font-size: 18px; color: #5b2d8e; text-align: center; margin-bottom: 4px; }
                h2 { font-size: 13px; color: #444; text-align: center; margin-top: 0; }
                .resumen { width: 100%; border-collapse: collapse; margin: 16px 0; }
                .resumen td { padding: 6px 10px; border: 1px solid #ddd; }
                .resumen td:first-child { font-weight: bold; background: #f5f5f5; width: 40%; }
                table.amort { width: 100%; border-collapse: collapse; margin-top: 10px; }
                table.amort th { background: #5b2d8e; color: #fff; padding: 6px 8px; text-align: center; font-size: 11px; }
                table.amort td { padding: 5px 8px; border-bottom: 1px solid #eee; text-align: center; font-size: 11px; }
                table.amort tr:nth-child(even) td { background: #f9f5ff; }
                .footer { margin-top: 20px; font-size: 10px; color: #888; text-align: center; }
            </style>
        </head>
        <body>
            <h1> $nombreProducto </h1>
            <h2>Tabla de Amortización — Crédito #$idCredito</h2>

            <table class="resumen">
                <tr><td>Cliente</td><td>$nombreCliente</td></tr>
                <tr><td>Fecha de acuerdo</td><td>$fechaAcuerdo</td></tr>
                <tr><td>Deuda original</td><td>$$adeudoOrig</td></tr>
                <tr><td>Descuento aplicado</td><td>-$$descuento</td></tr>
                <tr><td>Total a pagar</td><td>$$totalPagar</td></tr>
                <tr><td>Primer pago (inicial)</td><td>$pagoInicial</td></tr>
                <tr><td>Pago semanal</td><td>$$pagoSemanal</td></tr>
                <tr><td>Número de semanas</td><td>$numSemanas</td></tr>
            </table>

            <table class="amort">
                <thead>
                    <tr>
                        <th>Semana</th>
                        <th>Fecha de Pago</th>
                        <th>Pago Semanal</th>
                        <th>Capital</th>
                        <th>Saldo Restante</th>
                    </tr>
                </thead>
                <tbody>
                    $filasHtml
                </tbody>
            </table>

            <div class="footer"> — Uso interno. Sujeto a términos del convenio.</div>
        </body>
        </html>
        HTML;

        try {
            $mpdf = new \mPDF([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 15,
                'margin_right'  => 15,
                'margin_top'    => 15,
                'margin_bottom' => 15,
            ]);
            $mpdf->WriteHTML($html);
            $nombreArchivo = 'convenio_credito_' . $idCredito . '_' . date('Ymd') . '.pdf';
            $mpdf->Output($nombreArchivo, 'D');
        } catch (\Exception $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al generar el PDF.', null, $e->getMessage()));
        }
        exit;
    }

    // ─────────────────────────────────────────────
// API: HISTORIAL DE CONVENIOS
// ─────────────────────────────────────────────

public function getHistorialConvenios()
{
    $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

    if ($idCredito <= 0) {
        self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
    }

    $r = ConveniosDAO::getHistorialConvenios($idCredito);
    self::respuestaJSON($r);
}

// ════════════════════════════════════════════════
// API: REGISTRAR PAGO DE SEMANA
// ════════════════════════════════════════════════

public function registrarPago()
{
    $idConvenio   = isset($_POST['id_convenio'])   ? (int) $_POST['id_convenio']   : 0;
    $numeroSemana = isset($_POST['numero_semana'])  ? (int) $_POST['numero_semana']  : 0;
    $idCredito    = isset($_POST['id_credito'])     ? (int) $_POST['id_credito']     : 0;

    if ($idConvenio <= 0 || $numeroSemana <= 0 || $idCredito <= 0) {
        self::respuestaJSON(self::respuesta(false, 'Parámetros inválidos.'));
    }

    $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';

    $r = ConveniosDAO::registrarPago($idConvenio, $numeroSemana, $idCredito);
    self::respuestaJSON($r);
}

// ════════════════════════════════════════════════
// API: OBTENER PRODUCTOS DEL CATÁLOGO
// ════════════════════════════════════════════════

public function getProductosConvenio()
{
    $r = ConveniosDAO::getProductosConvenio();
    self::respuestaJSON($r);
}

public function getAmortizacionConvenio()
{
    if (empty($_POST['id_convenio'])) {
        self::respuestaJSON(self::respuesta(false, 'id_convenio requerido.'));
        return;
    }
    $r = ConveniosDAO::getAmortizacionConvenio((int) $_POST['id_convenio']);
    self::respuestaJSON($r);
}

}
