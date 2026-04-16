<?php

namespace Controllers;

use Core\Controller;
use Models\Convenios as ConveniosDAO;

class Convenios extends Controller
{
    /** Módulo web 32 — Permisos especiales: Registrar convenio existente */
    private const MODULO_REGISTRAR_CONVENIO_EXISTENTE = 32;

    private function usuarioTienePermisoRegistrarConvenioExistente(): bool
    {
        $mods = $_SESSION['modulos'] ?? [];
        if (!is_array($mods)) {
            return false;
        }
        foreach ($mods as $m) {
            if ((int) $m === self::MODULO_REGISTRAR_CONVENIO_EXISTENTE) {
                return true;
            }
        }
        return false;
    }

    // ─────────────────────────────────────────────
    // VISTA PRINCIPAL
    // ─────────────────────────────────────────────

    public function consulta()
    {
        $this->set('permisoRegistrarConvenioExistente', $this->usuarioTienePermisoRegistrarConvenioExistente());
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', 'Crear convenio | ' . $emp);
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

        // Monto adicional es opcional (cuando el usuario ajusta el total manualmente)
        $datos['monto_adicional'] = isset($_POST['monto_adicional']) && $_POST['monto_adicional'] !== '' && $_POST['monto_adicional'] !== 'null'
            ? (float) $_POST['monto_adicional']
            : 0.0;

        // Tipo calendario y fechas libres (solo para productos tipo_calendario='libre')
        $datos['tipo_calendario'] = isset($_POST['tipo_calendario']) && $_POST['tipo_calendario'] === 'libre'
            ? 'libre'
            : 'semanal';

        $datos['fechas_pagos'] = isset($_POST['fechas_pagos']) ? trim($_POST['fechas_pagos']) : '';

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
    // API: ESTATUS CRÉDITO EN S2
    // ─────────────────────────────────────────────

    public function getEstatusS2()
    {
        $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
        }

        $r = ConveniosDAO::getEstatusS2($idCredito);
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
    // API: VALIDAR CRÉDITO EN DESPACHO
    // Verifica que el crédito exista en asigna_creditos_despacho
    // y que su estatus sea 1 (activo en despacho, mora 8+).
    // Usada por el modal de Registrar Convenio Existente.
    // ════════════════════════════════════════════════

    // Valida si el crédito está en despacho (sin restricción de permiso especial).
    // Usada por seleccionarCredito del buscador principal.
    public function checkDespacho()
    {
        $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
            return;
        }

        $r = ConveniosDAO::validarCreditoEnDespacho($idCredito);
        self::respuestaJSON($r);
    }

    // ════════════════════════════════════════════════

    public function validarDespacho()
    {
        if (!$this->usuarioTienePermisoRegistrarConvenioExistente()) {
            self::respuestaJSON(self::respuesta(false, 'No tienes permiso para registrar convenios existentes.'));
            return;
        }

        $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
            return;
        }

        $r = ConveniosDAO::validarCreditoEnDespacho($idCredito);
        self::respuestaJSON($r);
    }

    // ════════════════════════════════════════════════
    // API: MIGRAR CONVENIO CON PDF (opcional)
    // ════════════════════════════════════════════════

    public function migrarConvenio()
    {
        if (!$this->usuarioTienePermisoRegistrarConvenioExistente()) {
            self::respuestaJSON(self::respuesta(false, 'No tienes permiso para registrar convenios existentes.'));
            return;
        }

        $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;

        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
            return;
        }

        // ── Validación de despacho antes de cualquier otra operación ──
        // El crédito debe existir en asigna_creditos_despacho con estatus = 1.
        // Si está current (estatus = 0) o no existe, se rechaza.
        $validacion = ConveniosDAO::validarCreditoEnDespacho($idCredito);
        if (!$validacion['success']) {
            self::respuestaJSON($validacion);
            return;
        }

        // Campos requeridos (comunes a ambos flujos)
        $campos = [
            'id_credito', 'nombre_cliente', 'id_producto_convenio',
            'id_producto_convenio_detalle', 'adeudo_base',
            'porcentaje_descuento', 'pago_semanal', 'fecha_inicio',
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                self::respuestaJSON(self::respuesta(false, "Campo requerido: $campo"));
                return;
            }
        }

        // ── Procesar PDF adjunto si viene en la petición ──
        $pdfPath = null;
        if (!empty($_FILES['pdf_adjunto']['name'])) {
            $pdfPath = $this->_guardarPdfAdjunto($_FILES['pdf_adjunto'], $idCredito);
            if (!$pdfPath) {
                self::respuestaJSON(self::respuesta(false, 'Error al guardar el PDF adjunto.'));
                return;
            }
        }

        $datos = array_merge($_POST, [
            'usuario_alta'               => $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema',
            'bucket_morosidad_real'      => $_POST['bucket_morosidad_real'] ?? '',
            'dias_mora'                  => $_POST['dias_mora']             ?? 0,
            'avance_pago_plazo'          => $_POST['avance_pago_plazo']     ?? '',
            'pdf_adjunto'                => $pdfPath,
            'monto_adicional'            => isset($_POST['monto_adicional']) ? (float) $_POST['monto_adicional'] : 0.0,
            'total_final_con_adicional'  => isset($_POST['total_final_con_adicional']) ? (float) $_POST['total_final_con_adicional'] : null,
        ]);

        $r = ConveniosDAO::migrarConvenio($datos);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // PRIVADO: Guarda PDF adjunto en el servidor
    // ─────────────────────────────────────────────

    private function _guardarPdfAdjunto($archivo, $idCredito)
    {
        try {
            $directorio = sparta_uploads_join('convenios');
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $extension     = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $nombreArchivo = 'convenio_' . $idCredito . '_' . date('Ymd_His') . '.' . $extension;
            $rutaCompleta  = sparta_uploads_join('convenios', $nombreArchivo);

            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                return '/uploads/convenios/' . $nombreArchivo;
            }

            return null;
        } catch (\Exception $e) {
            error_log('Error guardando PDF convenio: ' . $e->getMessage());
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

        $r = ConveniosDAO::getConvenioCualquierEstatus($idCredito);

        if (!$r['success'] || !$r['datos']) {
            self::respuestaJSON(self::respuesta(false, 'No se encontró convenio para este crédito.'));
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
        $pctDescuento   = (float) $convenio['porcentaje_descuento'];
        $montoAdicional = (float) ($convenio['monto_adicional'] ?? 0);
        $totalPagarNum  = (float) $convenio['total_a_pagar'];
        $totalInicial   = $totalPagarNum - $montoAdicional;
        $descuentoNum   = (float) $convenio['descuento_monto'];
        // Base real usada en el cálculo = saldo capital (no el adeudo total con recargos)
        $saldoCapital   = $totalInicial + $descuentoNum;
        $saldoCapitalFmt = number_format($saldoCapital, 2);
        $descuento      = number_format($descuentoNum, 2);
        $totalPagar     = number_format($totalPagarNum, 2);
        $pagoSemanal    = number_format((float) $convenio['pago_semanal'], 2);
        $numSemanas     = (int) $convenio['numero_semanas'];
        $pagoInicial    = $convenio['pago_inicial_monto'] ? '$' . number_format((float) $convenio['pago_inicial_monto'], 2) : 'No aplica';

        $totalInicialFmt    = number_format($totalInicial, 2);
        $montoAdicionalFmt  = number_format($montoAdicional, 2);

        // Filas extra solo cuando hay monto adicional
        $filasAdicionales = $montoAdicional > 0
            ? "<tr><td>Total inicial</td><td>\$$totalInicialFmt</td></tr>
                <tr><td>Adicionales</td><td>+\$$montoAdicionalFmt</td></tr>"
            : '';

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
                <tr><td>Deuda original</td><td>$$saldoCapitalFmt</td></tr>
                <tr><td>Descuento</td><td>$pctDescuento%</td></tr>
                $filasAdicionales
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

    // ════════════════════════════════════════════════
    // API: ELIMINAR CONVENIOS DE UN CRÉDITO (acción admin express)
    // POST: id_credito
    // ════════════════════════════════════════════════

    public function eliminarConveniosCredito()
    {
        $idCredito = isset($_POST['id_credito']) ? (int) $_POST['id_credito'] : 0;
        if ($idCredito <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de crédito inválido.'));
            return;
        }
        $r = ConveniosDAO::eliminarConveniosCredito($idCredito);
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

    // ════════════════════════════════════════════════
    // API: OBTENER DETALLES DE CONCILIACIÓN DE SEMANA
    // ════════════════════════════════════════════════

    public function getConciliacionSemana()
    {
        $idConvenio   = isset($_POST['id_convenio'])   ? (int) $_POST['id_convenio']   : 0;
        $numeroSemana = isset($_POST['numero_semana'])  ? (int) $_POST['numero_semana']  : 0;
        $idCredito    = isset($_POST['id_credito'])     ? (int) $_POST['id_credito']     : 0;

        if (!$idConvenio || !$numeroSemana || !$idCredito) {
            self::respuestaJSON(self::respuesta(false, 'Parámetros inválidos.'));
            return;
        }

        $semanasGrupo = isset($_POST['semanas_grupo']) ? trim($_POST['semanas_grupo']) : null;
        $r = ConveniosDAO::getConciliacionSemana($idConvenio, $numeroSemana, $idCredito, $semanasGrupo);
        self::respuestaJSON($r);
    }

    public function guardarConciliacion()
    {
        $campos = ['id_convenio', 'numero_semana', 'monto_pago', 'monto_aplicado', 'monto_sobrante', 'fecha_pago'];
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                self::respuestaJSON(self::respuesta(false, "Campo requerido: $campo"));
                return;
            }
        }

        $datos = array_merge($_POST, [
            'usuario' => $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema',
        ]);

        $archivo = isset($_FILES['comprobante']) && !empty($_FILES['comprobante']['tmp_name'])
            ? $_FILES['comprobante']
            : null;

        $r = ConveniosDAO::guardarConciliacion($datos, $archivo);
        self::respuestaJSON($r);
    }

    public function subirComprobante()
    {
        $campos = ['id_convenio', 'numero_semana', 'fecha_pago_real'];
        foreach ($campos as $campo) {
             if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                 self::respuestaJSON(self::respuesta(false, "Campo requerido: $campo"));
                 return;
             }
         }

        $archivo = isset($_FILES['comprobante']) && !empty($_FILES['comprobante']['tmp_name'])
            ? $_FILES['comprobante']
            : null;

        if (!$archivo) {
            self::respuestaJSON(self::respuesta(false, 'El comprobante es obligatorio.'));
            return;
        }

        $datos = array_merge($_POST, [
            'usuario' => $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema',
        ]);

        if (!empty($_POST['semanas_aplica'])) {
            $datos['semanas_aplica'] = $_POST['semanas_aplica'];
        }

        $r = ConveniosDAO::subirComprobante($datos, $archivo);
        self::respuestaJSON($r);
    }

    public function registrarConvenioGlobo()
{
    // ── 1. Solo POST ──────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido.']);
        return;
    }

    if (!$this->usuarioTienePermisoRegistrarConvenioExistente()) {
        self::respuestaJSON(self::respuesta(false, 'No tienes permiso para registrar convenios existentes.'));
        return;
    }

    // ── 2. Leer y sanear entrada ─────────────────────────────────
    $idCredito            = isset($_POST['id_credito'])             ? (int)    trim($_POST['id_credito'])            : 0;
    $nombreCliente        = isset($_POST['nombre_cliente'])         ?           trim($_POST['nombre_cliente'])        : '';
    $bucketMorosidad      = isset($_POST['bucket_morosidad_real'])  ?           trim($_POST['bucket_morosidad_real']) : '';
    $diasMora             = isset($_POST['dias_mora'])              ? (int)    trim($_POST['dias_mora'])             : 0;
    $avancePagoPlazo      = isset($_POST['avance_pago_plazo'])      ?           trim($_POST['avance_pago_plazo'])     : '';
    $adeudoOriginal       = isset($_POST['adeudo_total_original'])  ? (float)  trim($_POST['adeudo_total_original']) : 0.0;
    $totalAPagar          = isset($_POST['total_a_pagar'])          ? (float)  trim($_POST['total_a_pagar'])         : 0.0;
    $condonacion          = isset($_POST['condonacion_aplicada'])   ? (float)  trim($_POST['condonacion_aplicada'])  : null;
    $pagosIgualesCantidad = isset($_POST['pagos_iguales_cantidad']) ? (int)    trim($_POST['pagos_iguales_cantidad']): 0;
    $pagosIgualesMonto    = isset($_POST['pagos_iguales_monto'])    ? (float)  trim($_POST['pagos_iguales_monto'])    : 0.0;
    $pagoGloboMonto       = isset($_POST['pago_globo_monto'])       ? (float)  trim($_POST['pago_globo_monto'])       : 0.0;

    // Pago inicial es opcional, si no viene se asume 0.0 (no aplica)
    $pagoInicialMonto     = isset($_POST['pago_inicial_monto'])     ? (float)  trim($_POST['pago_inicial_monto'])     : 0.0;

    $frecuencia           = isset($_POST['frecuencia'])             ?           trim($_POST['frecuencia'])            : 'semanal';
    $fechaPrimerPago      = isset($_POST['fecha_primer_pago'])      ?           trim($_POST['fecha_primer_pago'])     : '';
    $usuarioAlta          = isset($_POST['usuario_alta'])           ?           trim($_POST['usuario_alta'])          : ($_SESSION['usuario'] ?? 'sistema');

    // ── 3. Validaciones básicas ──────────────────────────────────
    if ($idCredito < 1) {
        echo json_encode(['success' => false, 'mensaje' => 'ID de crédito inválido.']);
        return;
    }

    if (empty($fechaPrimerPago) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPrimerPago)) {
        echo json_encode(['success' => false, 'mensaje' => 'Fecha del primer pago inválida. Formato esperado: YYYY-MM-DD.']);
        return;
    }

    if ($pagosIgualesCantidad < 1) {
        echo json_encode(['success' => false, 'mensaje' => 'La cantidad de pagos iguales debe ser al menos 1.']);
        return;
    }

    if ($pagosIgualesMonto <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'El monto de los pagos iguales debe ser mayor a 0.']);
        return;
    }

    if ($pagoGloboMonto < 0) {
        echo json_encode(['success' => false, 'mensaje' => 'El monto del pago globo no puede ser negativo.']);
        return;
    }

    // Al menos uno entre pago inicial y pago globo debe ser > 0
    if ($pagoInicialMonto <= 0 && $pagoGloboMonto <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Debe indicar al menos un Pago Inicial o un Pago de Cierre mayor a $0.00.']);
        return;
    }

    if ($adeudoOriginal <= 0 || $totalAPagar <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'El adeudo original y el total a pagar deben ser mayores a 0.']);
        return;
    }

    if (!in_array($frecuencia, ['semanal', 'quincenal'])) {
        $frecuencia = 'semanal';
    }

    // ── 4. Llamar al model ────────────────────────────────────────
    $resultado = ConveniosDAO::registrarConvenioGlobo([

        'id_credito'            => $idCredito,
        'nombre_cliente'        => $nombreCliente,
        'bucket_morosidad_real' => $bucketMorosidad,
        'dias_mora'             => $diasMora,
        'avance_pago_plazo'     => $avancePagoPlazo,
        'adeudo_total_original' => $adeudoOriginal,
        'total_a_pagar'         => $totalAPagar,
        'condonacion_aplicada'  => $condonacion,
        'pagos_iguales_cantidad'=> $pagosIgualesCantidad,
        'pagos_iguales_monto'   => $pagosIgualesMonto,
        'pago_globo_monto'      => $pagoGloboMonto,
        'pago_inicial_monto'    => $pagoInicialMonto,
        'frecuencia'            => $frecuencia,
        'fecha_primer_pago'     => $fechaPrimerPago,
        'usuario_alta'          => $usuarioAlta,
    ]);

    // ── 5. Respuesta JSON ─────────────────────────────────────────
    header('Content-Type: application/json');
    echo json_encode($resultado);
}


    // ─────────────────────────────────────────────
    // VISTA: Estadística Convenios
    // ─────────────────────────────────────────────

    public function estadisticas()
    {
        $anio = (int) date('Y');
        $mes  = (int) date('n');

        $resConv  = ConveniosDAO::getEstadisticasConvenios($anio, $mes);
        $resCierr = ConveniosDAO::getEstadisticasCierresCredito($anio, $mes);
        $resAsig  = ConveniosDAO::getEstadisticasAsignacionCreditos($anio, $mes);

        $datos = [
            'convenios'  => ($resConv['success']  ?? false) ? ($resConv['datos']  ?? []) : [],
            'cierres'    => ($resCierr['success'] ?? false) ? ($resCierr['datos'] ?? []) : [],
            'asignacion' => ($resAsig['success']  ?? false) ? ($resAsig['datos']  ?? []) : [],
        ];

        self::set('titulo', 'Estadísticas Convenios');
        self::set('anioDefault', $anio);
        self::set('mesDefault',  $mes);
        self::set('datosInicialesJson', json_encode($datos, JSON_UNESCAPED_UNICODE));
        self::render('convenios_estadisticas');
    }


public function getEstadisticasConvenios()
{
    $anio = isset($_POST['anio']) ? (int) $_POST['anio'] : 0;
    $mes  = isset($_POST['mes'])  && $_POST['mes'] !== '' ? (int) $_POST['mes'] : null;

    if ($anio <= 0) {
        self::respuestaJSON(self::respuesta(false, 'El parámetro anio es requerido.'));
        return;
    }

    $r = ConveniosDAO::getEstadisticasConvenios($anio, $mes);
    self::respuestaJSON($r);
}

public function getEstadisticasConveniosDetalle()
{
    $anio = isset($_POST['anio']) ? (int) $_POST['anio'] : 0;
    $mes  = isset($_POST['mes'])  && $_POST['mes'] !== '' ? (int) $_POST['mes'] : null;
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'activos';

    if ($anio <= 0) {
        self::respuestaJSON(self::respuesta(false, 'El parámetro anio es requerido.'));
        return;
    }

    $r = ConveniosDAO::getEstadisticasConveniosDetalle($anio, $mes, $tipo);
    self::respuestaJSON($r);
}

public function getEstadisticasCierresCredito()
{
    $anio = isset($_POST['anio']) ? (int) $_POST['anio'] : 0;
    $mes  = isset($_POST['mes'])  && $_POST['mes'] !== '' ? (int) $_POST['mes'] : null;

    if ($anio <= 0) {
        self::respuestaJSON(self::respuesta(false, 'El parámetro anio es requerido.'));
        return;
    }

    $r = ConveniosDAO::getEstadisticasCierresCredito($anio, $mes);
    self::respuestaJSON($r);
}

public function getEstadisticasAsignacionCreditos()
{
    $anio = isset($_POST['anio']) ? (int) $_POST['anio'] : 0;
    $mes  = isset($_POST['mes'])  && $_POST['mes'] !== '' ? (int) $_POST['mes'] : null;

    if ($anio <= 0) {
        self::respuestaJSON(self::respuesta(false, 'El parámetro anio es requerido.'));
        return;
    }

    $r = ConveniosDAO::getEstadisticasAsignacionCreditos($anio, $mes);
    self::respuestaJSON($r);
}

}
