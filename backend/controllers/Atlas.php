<?php

namespace controllers;

use Core\Controller;
use Core\Database;
use Models\Atlas as AtlasDAO;
use Models\AtlasVentas as AtlasVentasDAO;
use Services\AtlasVentasReportService;

class Atlas extends Controller
{
    private const MODULO_ATLAS_VENTAS = 202;
    private const MODULO_ATLAS_EXPEDIENTES = 203;

    public function atlas()
    {
        header('Location: /Atlas/sucursales', true, 302);
        exit;
    }

    public function catalogos()
    {
        header('Location: /Atlas/sucursales', true, 302);
        exit;
    }

    public function sucursales()
    {
        $this->set('titulo', 'Sucursales');
        $this->set('atlas_vista_catalogos', 'sucursales');
        $this->set('google_maps_api_key_js', json_encode($this->googleMapsApiKey(), JSON_UNESCAPED_SLASHES));
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->set('atlas_permisos_sucursal', AtlasDAO::permisosSucursalAtlas($usuarioId));
        $this->render('atlas');
    }

    public function catalogosOperativos()
    {
        header('Location: /Atlas/distribuidores', true, 302);
        exit;
    }

    public function distribuidores()
    {
        $this->set('titulo', 'Distribuidores');
        $this->set('atlas_vista_catalogos', 'distribuidores');
        $this->set('google_maps_api_key_js', json_encode($this->googleMapsApiKey(), JSON_UNESCAPED_SLASHES));
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->set('atlas_permisos_sucursal', AtlasDAO::permisosSucursalAtlas($usuarioId));
        $this->render('atlas');
    }

    public function clasificaciones()
    {
        $this->set('titulo', 'Clasificaciones');
        $this->set('atlas_vista_catalogos', 'clasificaciones');
        $this->set('google_maps_api_key_js', json_encode($this->googleMapsApiKey(), JSON_UNESCAPED_SLASHES));
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->set('atlas_permisos_sucursal', AtlasDAO::permisosSucursalAtlas($usuarioId));
        $this->render('atlas');
    }

    public function notificacionesApp()
    {
        $this->set('titulo', 'Notificaciones App');
        $this->set('atlas_admin_configurada', $this->atlasAdminApiKey() !== '');
        $this->render('atlas_notificaciones_app');
    }

    public function accesosAtlas()
    {
        $this->set('titulo', 'Accesos Atlas');
        $this->render('atlas_accesos');
    }

    public function catalogosComerciales()
    {
        $this->set('titulo', 'Catálogos Comerciales');
        $this->render('atlas_catalogos_comerciales');
    }

    public function presupuestos()
    {
        $this->set('titulo', 'Presupuestos');
        $this->set('atlas_suc_asig_embedded', true);
        $this->set('atlas_suc_asig_titulo', 'Avance de meta');
        $this->render('atlas_presupuestos');
    }

    public function creditosOperacion()
    {
        $this->set('titulo', 'Créditos en operación');
        $this->render('atlas_creditos_operacion');
    }

    public function ventas()
    {
        $this->validarAccesoVentas();
        $this->set('titulo', 'Ventas');
        $this->set('layoutVendorLite', true);
        $this->set('layoutPreloadSweetAlert', true);
        $this->set('layoutPreloadSweetAlertTitle', 'Cargando ventas');
        $this->set('layoutPreloadSweetAlertText', 'Preparando la informaci&oacute;n...');
        $this->set('layoutSelect2', true);
        $this->render('atlas_ventas');
    }

    public function getVentas()
    {
        $this->validarAccesoVentas(true);
        $cargaCompleta = filter_var($_GET['carga_completa'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($cargaCompleta) {
            $forzarActualizacion = filter_var($_GET['actualizar'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $response = AtlasVentasDAO::precargar($forzarActualizacion);
            if (empty($response['success'])) {
                http_response_code((int)($response['status'] ?? 500));
            }
            $this->jsonComprimido($response);
        }

        $query = [];
        foreach ([
            'fecha_inicio', 'fecha_fin', 'fk_sucursal', 'fk_distribuidor',
            'historico', 'etapa', 'search', 'page', 'page_size',
        ] as $key) {
            if (isset($_GET[$key]) && trim((string)$_GET[$key]) !== '') {
                $query[$key] = $_GET[$key];
            }
        }

        $response = AtlasVentasDAO::consultar($query);
        if (empty($response['success'])) {
            http_response_code((int)($response['status'] ?? 500));
        }
        $this->json($response);
    }

    public function exportarVentas()
    {
        $this->validarAccesoVentas();
        $query = [];
        foreach (['fecha_inicio', 'fecha_fin', 'historico', 'fk_sucursal', 'fk_distribuidor', 'etapa', 'search'] as $key) {
            if (isset($_GET[$key]) && trim((string)$_GET[$key]) !== '') {
                $query[$key] = $_GET[$key];
            }
        }

        $tmp = null;
        try {
            $response = AtlasVentasDAO::consultar($query, true);
            if (empty($response['success'])) {
                throw new \RuntimeException((string)($response['mensaje'] ?? 'No se pudo preparar el reporte.'));
            }
            $datos = is_array($response['datos'] ?? null) ? $response['datos'] : [];
            $ventas = is_array($datos['filas'] ?? null) ? $datos['filas'] : [];
            $periodo = is_array($datos['periodo'] ?? null) ? $datos['periodo'] : [];

            $spreadsheet = (new AtlasVentasReportService())->crear($ventas);
            $tmp = tempnam(sys_get_temp_dir(), 'atlas_ventas_');
            if ($tmp === false) {
                throw new \RuntimeException('No se pudo preparar el archivo temporal.');
            }
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
            $spreadsheet->disconnectWorksheets();

            $inicio = preg_replace('/[^0-9-]/', '', (string)($periodo['fecha_inicio'] ?? date('Y-m-d')));
            $fin = preg_replace('/[^0-9-]/', '', (string)($periodo['fecha_fin'] ?? date('Y-m-d')));
            $filename = 'ventas_atlas_' . $inicio . '_a_' . $fin . '.xlsx';
            if (function_exists('session_write_close')) {
                session_write_close();
            }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($tmp));
            header('Cache-Control: max-age=0');
            readfile($tmp);
            @unlink($tmp);
            exit;
        } catch (\Throwable $e) {
            if (is_string($tmp) && is_file($tmp)) {
                @unlink($tmp);
            }
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'No se pudo generar el reporte de ventas: ' . $e->getMessage();
            exit;
        }
    }

    public function expedientes()
    {
        $this->validarAccesoExpedientes();
        $this->set('titulo', 'Expedientes');
        $this->set('atlas_admin_configurada', $this->atlasAdminApiKey() !== '');
        $this->render('atlas_expedientes');
    }

    public function getExpedientes()
    {
        $this->validarAccesoExpedientes(true);
        $query = [];
        foreach (['fecha_inicio', 'fecha_fin', 'estatus', 'fk_sucursal', 'search', 'page', 'page_size'] as $key) {
            if (isset($_GET[$key]) && trim((string)$_GET[$key]) !== '') {
                $query[$key] = $_GET[$key];
            }
        }

        if ((int)($_GET['all'] ?? 0) === 1) {
            $bulkQuery = array_merge(
                array_intersect_key($query, array_flip(['fecha_inicio', 'fecha_fin'])),
                ['completo' => 1, 'compacto' => 1, 'actualizar' => 1]
            );
            $this->streamAtlasExpedientesSnapshot($bulkQuery);
        }

        $this->json($this->atlasExpedientesApiResponse($this->atlasAdminApiRequest(
            'GET',
            '/api/atlas/admin/expedientes',
            null,
            $query
        )));
    }

    public function getExpedienteDetalle()
    {
        $this->validarAccesoExpedientes(true);
        $creditoId = (int)($_GET['credito_id'] ?? 0);
        if ($creditoId <= 0) {
            $this->json(['success' => false, 'mensaje' => 'Credito invalido.']);
        }

        $this->json($this->atlasExpedientesApiResponse($this->atlasAdminApiRequest(
            'GET',
            '/api/atlas/admin/expedientes/' . $creditoId
        )));
    }

    public function verEvidenciaExpediente()
    {
        $this->validarAccesoExpedientes(true);
        while (ob_get_level()) {
            ob_end_clean();
        }

        $creditoId = (int)($_GET['credito_id'] ?? 0);
        $evidenciaId = (int)($_GET['id'] ?? 0);
        if ($creditoId <= 0 || $evidenciaId <= 0) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-store');
            echo 'Identificadores de expediente o evidencia invalidos.';
            exit;
        }

        $response = $this->atlasAdminApiBinaryRequest(
            '/api/atlas/admin/expedientes/' . $creditoId
            . '/evidencias/' . $evidenciaId
            . '/contenido'
        );
        if (empty($response['success'])) {
            $status = (int)($response['status'] ?? 502);
            if ($status < 400 || $status > 599) {
                $status = 502;
            }
            http_response_code($status);
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-store');
            echo $status === 404
                ? 'La evidencia solicitada no esta disponible.'
                : 'No se pudo cargar la evidencia del expediente.';
            exit;
        }

        $content = (string)($response['contenido'] ?? '');
        $contentType = str_replace(
            ["\r", "\n"],
            '',
            (string)($response['content_type'] ?? 'application/octet-stream')
        );
        if ($contentType === '') {
            $contentType = 'application/octet-stream';
        }
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: inline; filename="evidencia-expediente-' . $evidenciaId . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        echo $content;
        exit;
    }

    public function registrarMovimientoExpediente()
    {
        $this->validarAccesoExpedientes(true);
        $payload = $this->payload();
        $creditoId = (int)($payload['credito_id'] ?? 0);
        if ($creditoId <= 0) {
            $this->json(['success' => false, 'mensaje' => 'Credito invalido.']);
        }
        unset($payload['credito_id'], $payload['origen_cambio'], $payload['document_change_source']);

        $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $usuarioNombre = trim((string)(
            $_SESSION['usuario_nombre']
            ?? $_SESSION['nombre']
            ?? $_SESSION['usuario']
            ?? 'Usuario Sparta'
        ));
        $payload['usuario_id'] = $usuarioId > 0 ? $usuarioId : null;
        $payload['usuario_nombre'] = $usuarioNombre !== '' ? $usuarioNombre : 'Usuario Sparta';

        $this->json($this->atlasExpedientesApiResponse($this->atlasAdminApiRequest(
            'POST',
            '/api/atlas/admin/expedientes/' . $creditoId . '/movimientos',
            $payload
        )));
    }

    public function importarExpedientes()
    {
        $this->validarAccesoExpedientes(true);
        $archivo = $_FILES['archivo'] ?? null;
        if (!$archivo || (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'mensaje' => 'Selecciona un archivo Excel valido.']);
        }

        $nombre = (string)($archivo['name'] ?? '');
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            $this->json(['success' => false, 'mensaje' => 'El layout debe ser un archivo .xlsx o .xls.']);
        }
        if ((int)($archivo['size'] ?? 0) > 10 * 1024 * 1024) {
            $this->json(['success' => false, 'mensaje' => 'El layout no puede superar 10 MB.']);
        }

        try {
            $filas = $this->leerExpedientesExcel((string)$archivo['tmp_name']);
            $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
            $usuarioNombre = trim((string)(
                $_SESSION['usuario_nombre']
                ?? $_SESSION['nombre']
                ?? $_SESSION['usuario']
                ?? 'Usuario Sparta'
            ));
            $lote = date('YmdHis') . '-' . bin2hex(random_bytes(5));
            $movimientos = array_map(
                static function (array $fila) use ($usuarioId, $usuarioNombre, $lote): array {
                    $requestId = sprintf(
                        'exp-layout-%s-%d-%d',
                        $lote,
                        (int)$fila['_excel_row'],
                        (int)$fila['credito_id']
                    );
                    return [
                        'credito_id' => (int)$fila['credito_id'],
                        'accion' => (string)$fila['accion'],
                        'motivo' => $fila['motivo'] !== '' ? $fila['motivo'] : null,
                        'comentario' => $fila['comentario'] !== '' ? $fila['comentario'] : null,
                        'evidencia_url' => null,
                        'request_id' => substr($requestId, 0, 64),
                        'usuario_id' => $usuarioId > 0 ? $usuarioId : null,
                        'usuario_nombre' => $usuarioNombre !== '' ? $usuarioNombre : 'Usuario Sparta',
                    ];
                },
                $filas
            );

            $this->json($this->atlasExpedientesApiResponse($this->atlasAdminApiRequest(
                'POST',
                '/api/atlas/admin/expedientes/importaciones',
                $movimientos,
                [],
                120
            )));
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'mensaje' => $e->getMessage() ?: 'No se pudo procesar el layout de expedientes.',
            ]);
        }
    }

    public function riesgosOperativos()
    {
        $this->set('titulo', 'Riesgos Operativos');
        $this->render('atlas_riesgos_operativos');
    }

    public function abanderamiento30()
    {
        $this->set('titulo', 'Abanderamiento 30+');
        $this->render('atlas_abanderamiento_30');
    }

    public function getPresupuestos()
    {
        $anio = (int)($_GET['anio'] ?? date('Y'));
        $this->json(AtlasDAO::getPresupuestos($anio));
    }

    public function getPresupuestoDetalle()
    {
        $this->json(AtlasDAO::getPresupuestoDetalle((int)($_GET['id'] ?? 0)));
    }

    public function getPresupuestoReasignacionCatalogos()
    {
        $presupuestoId = (int)($_GET['id'] ?? 0);
        if ($presupuestoId <= 0) {
            $this->json(['success' => false, 'mensaje' => 'Presupuesto invalido.']);
        }
        $this->json($this->atlasAdminApiRequest(
            'GET',
            '/api/atlas/admin/presupuestos/' . $presupuestoId . '/reasignacion'
        ));
    }

    public function reasignarPresupuesto()
    {
        $payload = $this->payload();
        $presupuestoId = (int)($payload['presupuesto_id'] ?? 0);
        if ($presupuestoId <= 0) {
            $this->json(['success' => false, 'mensaje' => 'Presupuesto invalido.']);
        }
        unset($payload['presupuesto_id']);
        $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $payload['usuario_id'] = $usuarioId > 0 ? $usuarioId : null;
        $response = $this->atlasAdminApiRequest(
            'POST',
            '/api/atlas/admin/presupuestos/' . $presupuestoId . '/reasignar',
            $payload
        );
        if (
            !empty($response['success'])
            && !empty($payload['detalle_ids'])
            && is_array($payload['detalle_ids'])
        ) {
            $consolidacion = AtlasDAO::consolidarAsignacionUnicaPresupuesto(
                $presupuestoId,
                $payload['detalle_ids'],
                $usuarioId
            );
            if (empty($consolidacion['success'])) {
                error_log('[Atlas][Presupuestos] No se pudieron limpiar repartos anteriores: ' . ($consolidacion['mensaje'] ?? 'Error desconocido.'));
            }
        }
        $this->json($response);
    }

    public function eliminarPresupuestoSucursal()
    {
        $payload = $this->payload();
        $presupuestoId = (int)($payload['presupuesto_id'] ?? 0);
        $detalleId = (int)($payload['detalle_id'] ?? 0);
        if ($presupuestoId <= 0 || $detalleId <= 0) {
            $this->json(['success' => false, 'mensaje' => 'Presupuesto o sucursal invalida.']);
        }
        unset($payload['presupuesto_id'], $payload['detalle_id']);
        $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $payload['usuario_id'] = $usuarioId > 0 ? $usuarioId : null;
        $this->json($this->atlasAdminApiRequest(
            'POST',
            '/api/atlas/admin/presupuestos/' . $presupuestoId . '/sucursales/' . $detalleId . '/reasignar-eliminar',
            $payload
        ));
    }

    public function getPresupuestoRanking()
    {
        $this->json(AtlasDAO::getPresupuestoRanking(
            (int)($_GET['id'] ?? 0),
            (string)($_GET['periodo'] ?? 'mes'),
            (int)($_GET['semana'] ?? 1),
            (string)($_GET['orden'] ?? 'cash')
        ));
    }

    public function getPresupuestoBitacora()
    {
        $this->json(AtlasDAO::getPresupuestoBitacora(
            (int)($_GET['id'] ?? 0),
            (int)($_GET['anio'] ?? date('Y'))
        ));
    }

    public function guardarPresupuestoSucursal()
    {
        $payload = $this->payload();
        $payload['usuario_id'] = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::guardarPresupuestoSucursal($payload));
    }

    public function eliminarPresupuestoMes()
    {
        $payload = $this->payload();
        $payload['usuario_id'] = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::eliminarPresupuestoMes($payload));
    }

    public function importarPresupuesto()
    {
        $anio = (int)($_POST['anio'] ?? date('Y'));
        $mes = (int)($_POST['mes'] ?? 0);
        $archivo = $_FILES['archivo'] ?? null;

        if ($mes < 1 || $mes > 12) {
            $this->json(['success' => false, 'mensaje' => 'Selecciona un mes valido.']);
        }
        if (!$archivo || (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'mensaje' => 'Carga un archivo Excel valido.']);
        }

        try {
            $filas = $this->leerPresupuestoExcel((string)$archivo['tmp_name']);
            $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
            $resultado = AtlasDAO::analizarReajustePresupuestoMensual($anio, $mes, $filas);
            if (!empty($resultado['success']) && is_array($resultado['datos'] ?? null)) {
                $datosAnalisis = $resultado['datos'];
                $huellaAnalisis = (string)($datosAnalisis['huella_analisis'] ?? '');
                $huellaPresupuesto = (string)($datosAnalisis['huella_presupuesto'] ?? '');
                if ($huellaAnalisis === '' || $huellaPresupuesto === '') {
                    $this->json([
                        'success' => false,
                        'mensaje' => 'No se pudo asegurar la version del comparativo. Vuelve a cargar el Excel.',
                    ]);
                }

                $token = bin2hex(random_bytes(16));
                if (!isset($_SESSION['atlas_presupuesto_reajustes']) || !is_array($_SESSION['atlas_presupuesto_reajustes'])) {
                    $_SESSION['atlas_presupuesto_reajustes'] = [];
                }
                foreach ($_SESSION['atlas_presupuesto_reajustes'] as $key => $item) {
                    if ((int)($item['created_at'] ?? 0) < time() - 1800) {
                        unset($_SESSION['atlas_presupuesto_reajustes'][$key]);
                    }
                }
                if (count($_SESSION['atlas_presupuesto_reajustes']) >= 5) {
                    uasort(
                        $_SESSION['atlas_presupuesto_reajustes'],
                        static fn(array $a, array $b): int => (int)($a['created_at'] ?? 0) <=> (int)($b['created_at'] ?? 0)
                    );
                    array_shift($_SESSION['atlas_presupuesto_reajustes']);
                }
                $_SESSION['atlas_presupuesto_reajustes'][$token] = [
                    'created_at' => time(),
                    'usuario_id' => $usuarioId,
                    'anio' => $anio,
                    'mes' => $mes,
                    'archivo_original' => mb_substr((string)$archivo['name'], 0, 220),
                    'filas' => $filas,
                    'huella_analisis' => $huellaAnalisis,
                    'huella_presupuesto' => $huellaPresupuesto,
                ];
                unset($resultado['datos']['huella_analisis'], $resultado['datos']['huella_presupuesto']);
                $resultado['datos']['reajuste_token'] = $token;
                $resultado['datos']['archivo_original'] = mb_substr((string)$archivo['name'], 0, 220);
                $resultado['datos']['expira_en_segundos'] = 1800;
            }
            $this->json($resultado);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'mensaje' => 'No se pudo leer el Excel de presupuesto.', 'error' => $e->getMessage()]);
        }
    }

    public function confirmarReajustePresupuesto()
    {
        $payload = $this->payload();
        $token = trim((string)($payload['token'] ?? ''));
        $motivo = trim((string)($payload['motivo'] ?? ''));
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            $this->json(['success' => false, 'mensaje' => 'El comparativo no es valido. Vuelve a cargar el Excel.']);
        }
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) {
            $this->json(['success' => false, 'mensaje' => 'Captura un motivo de entre 5 y 500 caracteres.']);
        }

        $store = $_SESSION['atlas_presupuesto_reajustes'] ?? [];
        $item = is_array($store) ? ($store[$token] ?? null) : null;
        if (!$item || (int)($item['created_at'] ?? 0) < time() - 1800) {
            unset($_SESSION['atlas_presupuesto_reajustes'][$token]);
            $this->json([
                'success' => false,
                'status' => 410,
                'mensaje' => 'El comparativo vencio. Vuelve a cargar el Excel para revisar datos actuales.',
            ]);
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $usuarioAnalisis = (int)($item['usuario_id'] ?? 0);
        if ($usuarioId > 0 && $usuarioAnalisis > 0 && $usuarioId !== $usuarioAnalisis) {
            unset($_SESSION['atlas_presupuesto_reajustes'][$token]);
            $this->json(['success' => false, 'status' => 403, 'mensaje' => 'Este comparativo pertenece a otra sesion.']);
        }

        $resultado = AtlasDAO::confirmarReajustePresupuestoMensual(
            (int)($item['anio'] ?? 0),
            (int)($item['mes'] ?? 0),
            (string)($item['archivo_original'] ?? ''),
            is_array($item['filas'] ?? null) ? $item['filas'] : [],
            (string)($item['huella_analisis'] ?? ''),
            (string)($item['huella_presupuesto'] ?? ''),
            $motivo,
            $usuarioId
        );

        if (!empty($resultado['success'])) {
            unset($_SESSION['atlas_presupuesto_reajustes'][$token]);
            if (!empty($resultado['datos']['resumen_importacion'])) {
                $pdfToken = bin2hex(random_bytes(16));
                if (!isset($_SESSION['atlas_presupuesto_resumen_importacion']) || !is_array($_SESSION['atlas_presupuesto_resumen_importacion'])) {
                    $_SESSION['atlas_presupuesto_resumen_importacion'] = [];
                }
                foreach ($_SESSION['atlas_presupuesto_resumen_importacion'] as $key => $resumenItem) {
                    if ((int)($resumenItem['created_at'] ?? 0) < time() - 3600) {
                        unset($_SESSION['atlas_presupuesto_resumen_importacion'][$key]);
                    }
                }
                $_SESSION['atlas_presupuesto_resumen_importacion'][$pdfToken] = [
                    'created_at' => time(),
                    'archivo_original' => (string)($item['archivo_original'] ?? ''),
                    'datos' => $resultado['datos'],
                ];
                $resultado['datos']['pdf_token'] = $pdfToken;
                $resultado['datos']['pdf_url'] = '/Atlas/descargarResumenImportacionPresupuesto?token=' . rawurlencode($pdfToken);
            }
        } elseif ((int)($resultado['status'] ?? 0) === 409) {
            unset($_SESSION['atlas_presupuesto_reajustes'][$token]);
        }

        $this->json($resultado);
    }

    public function descargarResumenImportacionPresupuesto()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $token = trim((string)($_GET['token'] ?? ''));
        $store = $_SESSION['atlas_presupuesto_resumen_importacion'] ?? [];
        $item = is_array($store) ? ($store[$token] ?? null) : null;
        if (!$token || !is_array($item) || (int)($item['created_at'] ?? 0) < time() - 3600) {
            header('Content-Type: text/plain; charset=utf-8', true, 404);
            echo 'Resumen de importacion no disponible.';
            exit;
        }

        try {
            $datos = is_array($item['datos'] ?? null) ? $item['datos'] : [];
            $resumen = is_array($datos['resumen_importacion'] ?? null) ? $datos['resumen_importacion'] : [];
            $anio = (int)($datos['anio'] ?? date('Y'));
            $mes = (int)($datos['mes'] ?? date('n'));
            $archivoOriginal = (string)($item['archivo_original'] ?? '');
            $html = $this->htmlResumenImportacionPresupuesto($resumen, $anio, $mes, $archivoOriginal);
            $tmpDir = defined('RAIZ') ? (RAIZ . '/storage/tmp_mpdf') : sys_get_temp_dir();
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'Letter',
                'tempDir' => is_dir($tmpDir) ? $tmpDir : sys_get_temp_dir(),
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 12,
                'margin_bottom' => 12,
            ]);
            $mpdf->SetTitle('Resumen importacion presupuesto');
            $mpdf->WriteHTML($html);
            $filename = sprintf('resumen_importacion_presupuesto_%04d_%02d.pdf', $anio, $mes);
            $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
            exit;
        } catch (\Throwable $e) {
            header('Content-Type: text/plain; charset=utf-8', true, 500);
            echo 'No se pudo generar el resumen PDF.';
            exit;
        }
    }

    public function descargarTemplatePresupuesto()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $anio = (int)($_GET['anio'] ?? date('Y'));
            $mes = (int)($_GET['mes'] ?? date('n'));
            if ($mes < 1 || $mes > 12) {
                $mes = (int)date('n');
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Presupuesto');

            $headers = [
                'Pk_Sucursal',
                'Distribuidor',
                'Sucursal',
                'Divisional',
                'Regional',
                'Supervisor ',
                'Asesor',
                'Estado',
                'Promedio Feb -  May',
                'Clasificacion nuevo esquema',
                'Creditos',
                'Cash',
                'Comisiona a partir de',
            ];
            foreach ($headers as $idx => $label) {
                $sheet->setCellValue($this->excelCell($idx + 1, 1), $label);
            }

            $rowNum = 2;
            foreach (AtlasDAO::getSucursalesTemplatePresupuestoMensual($anio, $mes) as $row) {
                $sheet->setCellValueExplicit($this->excelCell(1, $rowNum), (string)($row['fk_sucursal'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue($this->excelCell(2, $rowNum), $row['distribuidor'] ?? '');
                $sheet->setCellValue($this->excelCell(3, $rowNum), $row['sucursal'] ?? '');
                $sheet->setCellValue($this->excelCell(4, $rowNum), $row['divisional'] ?? '');
                $sheet->setCellValue($this->excelCell(5, $rowNum), $row['regional'] ?? '');
                $sheet->setCellValue($this->excelCell(6, $rowNum), $row['supervisor'] ?? '');
                $sheet->setCellValue($this->excelCell(7, $rowNum), $row['asesor'] ?? '');
                $sheet->setCellValue($this->excelCell(8, $rowNum), $row['estado'] ?? '');
                $sheet->setCellValue($this->excelCell(9, $rowNum), '');
                $sheet->setCellValue($this->excelCell(10, $rowNum), $row['clasificacion'] ?? '');
                $sheet->setCellValue($this->excelCell(11, $rowNum), $row['meta_creditos'] ?? '');
                $sheet->setCellValue($this->excelCell(12, $rowNum), $row['meta_cash'] ?? '');
                $sheet->setCellValue($this->excelCell(13, $rowNum), $row['comisiona_a_partir_de'] ?? '');
                $rowNum++;
            }

            $lastRow = max(2, $rowNum - 1);
            $sheet->getStyle('A1:M1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A1:M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('26344E');
            $sheet->getStyle('A1:M' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('D9DEE3');
            $sheet->getStyle('K2:K' . $lastRow)->getNumberFormat()->setFormatCode('0');
            $sheet->getStyle('L2:L' . $lastRow)->getNumberFormat()->setFormatCode('$#,##0.00');
            $sheet->getStyle('M2:M' . $lastRow)->getNumberFormat()->setFormatCode('0');
            foreach (range('A', 'M') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            $filename = sprintf('template_presupuesto_%04d_%02d.xlsx', $anio, $mes);
            $tmp = tempnam(sys_get_temp_dir(), 'atlas_presupuesto_');
            if ($tmp === false) {
                throw new \RuntimeException('No se pudo preparar el archivo temporal.');
            }
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
            $spreadsheet->disconnectWorksheets();

            if (function_exists('session_write_close')) {
                session_write_close();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($tmp));
            header('Cache-Control: max-age=0');
            readfile($tmp);
            @unlink($tmp);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'No se pudo generar el template: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            exit;
        }
    }

    public function descargarTemplateDistribuidores()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Distribuidores');

            $headers = [
                'ID',
                'Nombre comercial',
                'Razon social',
                'RFC',
                'Tipo persona',
                'Tipo distribuidor',
                'Estatus',
                'Contacto principal',
                'Telefono principal',
                'Telefono alterno',
                'Correo principal',
                'Regimen fiscal',
                'Banco deposito',
                'Titular deposito',
                'Cuenta deposito',
                'CLABE deposito',
                'Tipo motos',
                'Canal venta',
                'Presencia fisica',
                'Horario atencion',
                'Dias operacion',
                'Requiere cita',
                'Tiempo estadia',
                'Observaciones',
            ];
            foreach ($headers as $idx => $label) {
                $sheet->setCellValue($this->excelCell($idx + 1, 1), $label);
            }

            $rowNum = 2;
            foreach (AtlasDAO::getDistribuidoresTemplate() as $row) {
                $values = [
                    $row['id'] ?? '',
                    $row['nombre_comercial'] ?? $row['nombre'] ?? '',
                    $row['razon_social'] ?? '',
                    $row['rfc'] ?? '',
                    $row['tipo_persona'] ?? '',
                    $row['tipo_distribuidor'] ?? '',
                    $row['estatus'] ?? '',
                    $row['nombre_contacto'] ?? '',
                    $row['telefono_contacto'] ?? '',
                    $row['telefono_secundario'] ?? '',
                    $row['email_contacto'] ?? '',
                    $row['regimen_fiscal'] ?? '',
                    $row['banco_deposito'] ?? '',
                    $row['titular_deposito'] ?? '',
                    $row['cuenta_deposito'] ?? '',
                    $row['clabe_deposito'] ?? '',
                    $row['tipo_motos'] ?? '',
                    $row['canal_venta'] ?? '',
                    (int)($row['presencia_fisica'] ?? 1) === 1 ? 'SI' : 'NO',
                    $row['horario_atencion'] ?? '',
                    $row['dias_operacion'] ?? '',
                    (int)($row['requiere_cita'] ?? 0) === 1 ? 'SI' : 'NO',
                    $row['tiempo_promedio_entrega'] ?? '',
                    $row['observaciones'] ?? '',
                ];
                foreach ($values as $idx => $value) {
                    $sheet->setCellValueExplicit($this->excelCell($idx + 1, $rowNum), (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                $rowNum++;
            }

            $lastRow = max(2, $rowNum - 1);
            $lastCol = $this->excelCell(count($headers), 1);
            $sheet->getStyle('A1:' . $lastCol)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A1:' . $lastCol)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('26344E');
            $sheet->getStyle('A1:' . $this->excelCell(count($headers), $lastRow))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('D9DEE3');
            foreach (range('A', 'X') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            $filename = 'template_distribuidores_atlas_' . date('Ymd_His') . '.xlsx';
            $tmp = tempnam(sys_get_temp_dir(), 'atlas_distribuidores_');
            if ($tmp === false) {
                throw new \RuntimeException('No se pudo preparar el archivo temporal.');
            }
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
            $spreadsheet->disconnectWorksheets();

            if (function_exists('session_write_close')) {
                session_write_close();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($tmp));
            header('Cache-Control: max-age=0');
            readfile($tmp);
            @unlink($tmp);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'No se pudo generar el template: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            exit;
        }
    }

    public function descargarPlantillaAccesosAtlas()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Personal Atlas');

            $headers = [
                'ID acceso',
                'Persona ID',
                'Numero empleado',
                'Usuario',
                'Contrasena',
                'Nombre',
                'Correo',
                'Telefono',
                'Puesto',
                'Departamento',
                'Area',
                'Direccion',
                'Pais',
                'Jefe',
                'Puede ver',
                'Puede editar',
                'Puede administrar',
                'Acceso movil',
                'Excluido operativo',
                'Motivo exclusion',
                'Fecha exclusion',
                'Activo',
                'Ultima sincronizacion',
            ];
            foreach ($headers as $idx => $label) {
                $sheet->setCellValue($this->excelCell($idx + 1, 1), $label);
            }

            $rowNum = 2;
            foreach (AtlasDAO::getAccesosAtlasTemplate() as $row) {
                $values = [
                    $row['id'] ?? '',
                    $row['persona_id'] ?? '',
                    $row['numero_empleado'] ?? '',
                    $row['user_name'] ?? '',
                    $row['password'] ?? '',
                    $row['nombre'] ?? '',
                    $row['correo'] ?? '',
                    $row['telefono'] ?? '',
                    $row['puesto'] ?? '',
                    $row['departamento'] ?? '',
                    $row['area'] ?? '',
                    $row['direccion'] ?? '',
                    $row['pais'] ?? '',
                    $row['jefe_nombre'] ?? '',
                    (int)($row['puede_ver'] ?? 0) === 1 ? 'SI' : 'NO',
                    (int)($row['puede_editar'] ?? 0) === 1 ? 'SI' : 'NO',
                    (int)($row['puede_administrar'] ?? 0) === 1 ? 'SI' : 'NO',
                    (int)($row['acceso_movil'] ?? 0) === 1 ? 'SI' : 'NO',
                    (int)($row['excluido_operativo'] ?? 0) === 1 ? 'SI' : 'NO',
                    $row['excluido_motivo'] ?? '',
                    $row['excluido_at_fmt'] ?? '',
                    (int)($row['activo'] ?? 0) === 1 ? 'ACTIVO' : 'INACTIVO',
                    $row['ultima_sincronizacion_fmt'] ?? '',
                ];
                foreach ($values as $idx => $value) {
                    $sheet->setCellValueExplicit(
                        $this->excelCell($idx + 1, $rowNum),
                        (string)$value,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
                }
                $rowNum++;
            }

            $lastRow = max(2, $rowNum - 1);
            $lastCol = $this->excelCell(count($headers), 1);
            $sheet->getStyle('A1:' . $lastCol)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A1:' . $lastCol)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('26344E');
            $sheet->getStyle('A1:' . $this->excelCell(count($headers), $lastRow))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('D9DEE3');
            for ($col = 1; $col <= count($headers); $col++) {
                $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            $filename = 'plantilla_personal_accesos_atlas_' . date('Ymd_His') . '.xlsx';
            $tmp = tempnam(sys_get_temp_dir(), 'atlas_accesos_');
            if ($tmp === false) {
                throw new \RuntimeException('No se pudo preparar el archivo temporal.');
            }
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
            $spreadsheet->disconnectWorksheets();

            if (function_exists('session_write_close')) {
                session_write_close();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($tmp));
            header('Cache-Control: max-age=0');
            readfile($tmp);
            @unlink($tmp);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'No se pudo generar la plantilla de accesos Atlas: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            exit;
        }
    }

    public function importarDistribuidores()
    {
        $archivo = $_FILES['archivo'] ?? null;
        if (!$archivo || (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'mensaje' => 'Carga un archivo Excel valido.']);
        }

        try {
            $filas = $this->leerDistribuidoresExcel((string)$archivo['tmp_name']);
            $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
            $this->json(AtlasDAO::importarDistribuidoresLayout($filas, $usuarioId, (int)($_POST['desbloquear_existentes'] ?? 0) === 1));
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'mensaje' => 'No se pudo leer el Excel de distribuidores.', 'error' => $e->getMessage()]);
        }
    }

    public function importarPlantillaAccesosAtlas()
    {
        $archivo = $_FILES['archivo'] ?? null;
        if (!$archivo || (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'mensaje' => 'Carga la plantilla de personal en Excel.']);
        }

        try {
            $filas = $this->leerAccesosAtlasExcel((string)$archivo['tmp_name']);
            $usuarioId = (int)($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
            $this->json(AtlasDAO::importarAccesosAtlasLayout($filas, $usuarioId));
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'mensaje' => 'No se pudo leer la plantilla de accesos Atlas.', 'error' => $e->getMessage()]);
        }
    }

    private function htmlResumenImportacionPresupuesto(array $resumen, int $anio, int $mes, string $archivoOriginal): string
    {
        $nombreMes = $this->nombreMesPresupuesto($mes);
        $metricas = [
            'Esperadas' => (int)($resumen['sucursales_esperadas'] ?? 0),
            'Leidas' => (int)($resumen['filas_leidas'] ?? 0),
            'Cargadas' => (int)($resumen['registros_importados'] ?? 0),
            'Duplicadas' => (int)($resumen['duplicados'] ?? 0),
            'Extras' => (int)($resumen['extras'] ?? 0),
            'Faltantes' => (int)($resumen['faltantes'] ?? 0),
            'Asesores con error' => (int)($resumen['errores_asignacion'] ?? 0),
        ];

        $cards = '';
        foreach ($metricas as $label => $valor) {
            $cards .= '<td class="card"><div class="label">' . $this->ePdf($label) . '</div><div class="value">' . number_format($valor) . '</div></td>';
        }

        $html = '
        <html><head><style>
            body { font-family: sans-serif; color: #22303e; font-size: 10px; }
            h1 { font-size: 18px; margin: 0 0 4px; text-align: center; }
            h2 { font-size: 13px; margin: 18px 0 7px; color: #26344e; }
            .sub { text-align: center; color: #64748b; font-size: 10px; margin-bottom: 12px; }
            .cards { width: 100%; border-collapse: separate; border-spacing: 5px; margin-bottom: 10px; }
            .card { border: 1px solid #d9dee8; border-radius: 6px; padding: 7px; background: #f8fafc; text-align: center; }
            .label { color: #64748b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
            .value { color: #22303e; font-size: 14px; font-weight: bold; margin-top: 2px; }
            .note { border: 1px solid #fde68a; background: #fffbeb; color: #92400e; padding: 8px; border-radius: 6px; margin: 10px 0; }
            table.detalle { width: 100%; border-collapse: collapse; margin-top: 4px; }
            table.detalle th { background: #26344e; color: #fff; font-size: 8px; text-align: left; padding: 5px; }
            table.detalle td { border: 1px solid #e5e7eb; padding: 5px; vertical-align: top; }
            .muted { color: #64748b; }
        </style></head><body>
            <h1>Resumen de importacion de presupuesto</h1>
            <div class="sub">' . $this->ePdf($nombreMes . ' ' . $anio) . ' &middot; Archivo: ' . $this->ePdf($archivoOriginal ?: 'Sin archivo') . '</div>
            <table class="cards"><tr>' . $cards . '</tr></table>
            <div class="note">
                Este documento muestra las observaciones detectadas durante la carga. Las sucursales extra no se cargaron porque no estaban en el template esperado. Las faltantes son sucursales del template que no llegaron en el Excel.
            </div>';

        $html .= $this->tablaResumenImportacionPdf('Sucursales extra del Excel', 'Estas filas venian en el Excel, pero no existen en el template esperado y no se cargaron.', $resumen['detalle_extras'] ?? [], true);
        $html .= $this->tablaResumenImportacionPdf('Sucursales faltantes del template', 'Estas sucursales existen en el template esperado, pero no llegaron en el Excel.', $resumen['detalle_faltantes'] ?? [], false);
        $html .= $this->tablaResumenImportacionPdf('Sucursales duplicadas', 'Estas sucursales venian repetidas. Para cargar se tomo el ultimo registro encontrado por FK.', $resumen['detalle_duplicados'] ?? [], true);
        $html .= $this->tablaErroresAsignacionPresupuestoPdf('Errores de asignacion de asesor', 'Estas filas se cargaron al presupuesto, pero no actualizaron la asignacion operativa porque el asesor del Excel no se pudo ligar contra persona.', $resumen['detalle_errores_asignacion'] ?? []);
        $html .= '</body></html>';
        return $html;
    }

    private function tablaResumenImportacionPdf(string $titulo, string $descripcion, $rows, bool $mostrarFila): string
    {
        if (!is_array($rows) || !$rows) {
            return '';
        }
        $headFila = $mostrarFila ? '<th>Fila Excel</th>' : '';
        $html = '<h2>' . $this->ePdf($titulo) . '</h2><div class="muted">' . $this->ePdf($descripcion) . '</div>';
        $html .= '<table class="detalle"><thead><tr>' . $headFila . '<th>FK Sucursal</th><th>Sucursal</th><th>Distribuidor</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            if (!is_array($row)) {
                $row = ['fk_sucursal' => $row];
            }
            $html .= '<tr>';
            if ($mostrarFila) {
                $html .= '<td>' . $this->ePdf((string)($row['fila'] ?? '')) . '</td>';
            }
            $html .= '<td>' . $this->ePdf((string)($row['fk_sucursal'] ?? '')) . '</td>';
            $html .= '<td>' . $this->ePdf((string)($row['sucursal'] ?? 'Sin nombre en archivo')) . '</td>';
            $html .= '<td>' . $this->ePdf((string)($row['distribuidor'] ?? '')) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function tablaErroresAsignacionPresupuestoPdf(string $titulo, string $descripcion, $rows): string
    {
        if (!is_array($rows) || !$rows) {
            return '';
        }
        $html = '<h2>' . $this->ePdf($titulo) . '</h2><div class="muted">' . $this->ePdf($descripcion) . '</div>';
        $html .= '<table class="detalle"><thead><tr><th>Fila Excel</th><th>FK Sucursal</th><th>Sucursal</th><th>Asesor Excel</th><th>Falla</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $html .= '<tr>';
            $html .= '<td>' . $this->ePdf((string)($row['fila'] ?? '')) . '</td>';
            $html .= '<td>' . $this->ePdf((string)($row['fk_sucursal'] ?? '')) . '</td>';
            $html .= '<td>' . $this->ePdf((string)($row['sucursal'] ?? 'Sin nombre en archivo')) . '</td>';
            $html .= '<td>' . $this->ePdf((string)($row['asesor_excel'] ?? '')) . '</td>';
            $html .= '<td>' . $this->ePdf((string)($row['falla'] ?? 'No se pudo ligar contra persona.')) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function nombreMesPresupuesto(int $mes): string
    {
        $nombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return $nombres[$mes] ?? 'Mes';
    }

    private function ePdf(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function rutasGestores()
    {
        $this->set('titulo', 'Rutas y seguimiento');
        $this->set('google_maps_api_key_js', json_encode($this->googleMapsApiKey(), JSON_UNESCAPED_SLASHES));
        $this->render('atlas_rutas_gestores');
    }

    public function asistencias()
    {
        $this->set('titulo', 'Asistencias Atlas');
        $this->set('atlas_admin_configurada', $this->atlasAdminApiKey() !== '');
        $this->render('atlas_asistencias');
    }

    public function getReporteAsistencias()
    {
        $this->json($this->reporteAsistenciasApiResponse(
            $this->reporteAsistenciasQuery()
        ));
    }

    public function getCreditosSucursalesAsistencia()
    {
        $fechaInicio = trim((string)($_GET['fecha_inicio'] ?? ''));
        $fechaFin = trim((string)($_GET['fecha_fin'] ?? ''));
        $gestorPersonaId = (int)($_GET['gestor_persona_id'] ?? 0);
        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)
            || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)
            || $fechaFin < $fechaInicio
        ) {
            $this->json([
                'success' => false,
                'status' => 400,
                'mensaje' => 'Selecciona un rango de fechas valido para consultar los creditos.',
            ]);
        }
        if ($gestorPersonaId <= 0) {
            $this->json([
                'success' => false,
                'status' => 400,
                'mensaje' => 'No pudimos identificar al colaborador para consultar sus creditos.',
            ]);
        }

        $response = $this->atlasAdminApiRequest(
            'GET',
            '/api/atlas/admin/reportes/asistencias/creditos-sucursales',
            null,
            [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'gestor_persona_id' => $gestorPersonaId,
                'limit' => 100,
            ]
        );
        if (!empty($response['success'])) {
            $response['datos'] = $this->normalizarCreditosSucursalesAsistencia(
                $response['datos'] ?? null
            );
        } else {
            $status = (int)($response['status'] ?? 0);
            $response = [
                'success' => false,
                'status' => $status > 0 ? $status : 502,
                'mensaje' => 'No pudimos consultar los estados de creditos de la app en este momento.',
                'datos' => [
                    'registros' => [],
                    'total' => 0,
                ],
            ];
        }

        $this->json($response);
    }

    public function getRutasUsuarioSpartan()
    {
        $this->json($this->rutasUsuarioSpartanApiResponse(
            $this->rutasUsuarioSpartanQuery()
        ));
    }

    public function getResumenCoberturaAsistencia()
    {
        $mes = trim((string)($_GET['mes'] ?? ''));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            $this->json([
                'success' => false,
                'status' => 400,
                'mensaje' => 'Selecciona un mes valido para consultar la cobertura.',
            ]);
        }

        $contexto = $this->rutasUsuarioSpartanQuery();
        $identidad = is_array($contexto['query'] ?? null) ? $contexto['query'] : [];
        $query = array_intersect_key($identidad, array_flip([
            'gestor_persona_id',
            'external_id',
            'user_id',
        ]));
        if (empty($query['gestor_persona_id']) && empty($query['external_id']) && empty($query['user_id'])) {
            $this->json([
                'success' => false,
                'status' => 400,
                'mensaje' => 'No pudimos identificar al colaborador para consultar su cobertura.',
            ]);
        }

        $query['mes'] = $mes;
        $response = $this->atlasAdminApiRequest(
            'GET',
            '/api/atlas/admin/reportes/asistencias/checkins',
            null,
            $query
        );
        if (empty($response['success'])) {
            $status = (int)($response['status'] ?? 0);
            $this->json([
                'success' => false,
                'status' => $status > 0 ? $status : 502,
                'mensaje' => 'No pudimos preparar la cobertura mensual en este momento.',
            ]);
        }

        $this->json([
            'success' => true,
            'mensaje' => 'Cobertura mensual consultada correctamente.',
            'datos' => $this->normalizarResumenCoberturaAsistencia(
                $response['datos'] ?? null,
                $mes
            ),
        ]);
    }

    private function reporteAsistenciasApiResponse(array $query): array
    {
        $response = $this->atlasAdminApiRequest(
            'GET',
            '/api/atlas/admin/reportes/asistencias',
            null,
            $query
        );
        if (!empty($response['success']) && is_array($response['datos'] ?? null)) {
            $response['datos'] = $this->normalizarReporteAsistencias((array)$response['datos']);
        } elseif (empty($response['success'])) {
            $response['mensaje'] = 'No pudimos cargar el reporte en este momento. Intenta de nuevo mas tarde o avisanos para revisarlo.';
            unset($response['error']);
        }

        return $response;
    }

    public function verEvidenciaAsistencia()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $evidenciaId = (int)($_GET['id'] ?? 0);
        if ($evidenciaId <= 0) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Identificador de evidencia invalido.';
            exit;
        }

        $response = $this->atlasAdminApiBinaryRequest(
            '/api/atlas/admin/reportes/asistencias/evidencias/' . $evidenciaId . '/contenido'
        );
        if (empty($response['success'])) {
            http_response_code((int)($response['status'] ?? 502));
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-store');
            echo (string)($response['mensaje'] ?? 'No se pudo cargar la evidencia.');
            exit;
        }

        $content = (string)($response['contenido'] ?? '');
        header('Content-Type: ' . (string)($response['content_type'] ?? 'application/octet-stream'));
        header('Content-Disposition: inline; filename="evidencia-' . $evidenciaId . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: private, no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');
        echo $content;
        exit;
    }

    public function verMapaRutaAsistencia()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $rutaId = (int)($_GET['ruta_id'] ?? $_GET['id'] ?? 0);
        if ($rutaId <= 0) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-store');
            echo 'Identificador de ruta invalido.';
            exit;
        }

        $response = $this->atlasAdminApiBinaryRequest(
            '/api/atlas/admin/reportes/asistencias/rutas/' . $rutaId . '/mapa-estatico'
        );
        if (empty($response['success'])) {
            $status = (int)($response['status'] ?? 502);
            if ($status < 400 || $status > 599) {
                $status = 502;
            }
            http_response_code($status);
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-store');
            echo 'No se pudo cargar el mapa de la ruta.';
            exit;
        }

        $content = (string)($response['contenido'] ?? '');
        header('Content-Type: ' . (string)($response['content_type'] ?? 'image/png'));
        header('Content-Disposition: inline; filename="ruta-asistencia-' . $rutaId . '-mapa"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: private, max-age=300');
        header('X-Content-Type-Options: nosniff');
        echo $content;
        exit;
    }

    public function descargarReporteAsistencias()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $tmp = null;
        try {
            $response = $this->reporteAsistenciasApiResponse($this->reporteAsistenciasQuery());
            if (empty($response['success']) || !is_array($response['datos'] ?? null)) {
                throw new \RuntimeException((string)($response['mensaje'] ?? 'No se pudo consultar el reporte.'));
            }

            $datos = $response['datos'];
            $filas = is_array($datos['filas'] ?? null) ? $datos['filas'] : [];
            $filtroEvidencias = trim((string)($_GET['evidencias'] ?? ''));
            $filtroRutas = trim((string)($_GET['rutas'] ?? ''));
            $filtrosLocalesAplicados = false;
            if (in_array($filtroEvidencias, ['con', 'sin', 'incompletas'], true)) {
                $filas = $this->filtrarAsistenciasPorEvidencias($filas, $filtroEvidencias);
                $filtrosLocalesAplicados = true;
            } else {
                $filtroEvidencias = '';
            }
            if (in_array($filtroRutas, ['con', 'sin'], true)) {
                $filas = $this->filtrarAsistenciasPorRutas($filas, $filtroRutas);
                $filtrosLocalesAplicados = true;
            } else {
                $filtroRutas = '';
            }
            if ($filtrosLocalesAplicados) {
                $datos['filas'] = $filas;
                $datos['resumen'] = $this->resumenAsistenciasFiltradas(
                    $filas,
                    is_array($datos['resumen'] ?? null) ? $datos['resumen'] : []
                );
            }
            $colaboradores = $this->agruparAsistenciasPorColaborador($filas);
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Colaboradores');

            $columns = [
                ['Colaborador', 'colaborador'],
                ['Número de empleado', 'numero_empleado'],
                ['Puesto', 'puesto'],
                ['Rol Atlas', 'rol'],
                ['Divisional', 'divisional'],
                ['Días que acudió a sucursales', 'dias_con_asistencia'],
                ['Días que no acudió a sucursales', 'dias_sin_asistencia'],
                ['Total de gestiones realizadas', 'gestiones_realizadas'],
                ['Total de gestiones pendientes', 'gestiones_pendientes'],
            ];
            foreach ($columns as $index => $column) {
                $sheet->setCellValue($this->excelCell($index + 1, 1), $column[0]);
            }

            $rowNumber = 2;
            foreach ($colaboradores as $fila) {
                foreach ($columns as $index => $column) {
                    $key = $column[1];
                    $value = $fila[$key] ?? '';
                    if (in_array($key, ['dias_con_asistencia', 'dias_sin_asistencia', 'gestiones_realizadas', 'gestiones_pendientes'], true)
                        && $value !== null
                        && $value !== '') {
                        $sheet->setCellValue($this->excelCell($index + 1, $rowNumber), (float)$value);
                    } else {
                        $sheet->setCellValueExplicit(
                            $this->excelCell($index + 1, $rowNumber),
                            $value === null ? '' : (string)$value,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    }
                }
                $rowNumber++;
            }

            $lastRow = max(1, $rowNumber - 1);
            $lastColumn = $this->excelCell(count($columns), 1);
            $lastCell = $this->excelCell(count($columns), $lastRow);
            $sheet->getStyle('A1:' . $lastColumn)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A1:' . $lastColumn)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('173756');
            $sheet->getStyle('A1:' . $lastColumn)->getAlignment()->setWrapText(true);
            $sheet->getStyle('A1:' . $lastCell)
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setRGB('D9E2EC');
            if ($lastRow >= 2) {
                $sheet->getStyle('F2:I' . $lastRow)->getNumberFormat()->setFormatCode('0');
            }
            $sheet->freezePane('A2');
            $sheet->setAutoFilter('A1:' . $lastCell);

            $widths = [
                'A' => 34,
                'B' => 20,
                'C' => 24,
                'D' => 20,
                'E' => 24,
                'F' => 22,
                'G' => 24,
                'H' => 26,
                'I' => 26,
            ];
            foreach ($widths as $column => $width) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }

            $summarySheet = $spreadsheet->createSheet();
            $summarySheet->setTitle('Resumen');
            $periodo = is_array($datos['periodo'] ?? null) ? $datos['periodo'] : [];
            $totalesAgrupados = array_reduce(
                $colaboradores,
                static function (array $totales, array $colaborador): array {
                    foreach ([
                        'dias_con_asistencia',
                        'dias_sin_asistencia',
                        'gestiones_realizadas',
                        'gestiones_pendientes',
                    ] as $campo) {
                        $totales[$campo] += (int)($colaborador[$campo] ?? 0);
                    }
                    return $totales;
                },
                [
                    'dias_con_asistencia' => 0,
                    'dias_sin_asistencia' => 0,
                    'gestiones_realizadas' => 0,
                    'gestiones_pendientes' => 0,
                ]
            );
            $summaryRows = [
                ['Reporte', 'Asistencias Atlas'],
                ['Fecha inicio', $periodo['fecha_inicio'] ?? ''],
                ['Fecha fin', $periodo['fecha_fin'] ?? ''],
                ['Generado', $datos['generado_at'] ?? ''],
                ['Filtro de evidencias', $this->etiquetaFiltroEvidencias($filtroEvidencias)],
                ['Filtro de rutas', $this->etiquetaFiltroRutas($filtroRutas)],
                ['Total de colaboradores', count($colaboradores)],
                ['Días con asistencia', $totalesAgrupados['dias_con_asistencia']],
                ['Días sin asistencia', $totalesAgrupados['dias_sin_asistencia']],
                ['Gestiones realizadas', $totalesAgrupados['gestiones_realizadas']],
                ['Gestiones pendientes', $totalesAgrupados['gestiones_pendientes']],
            ];
            foreach ($summaryRows as $index => $summaryRow) {
                $summarySheet->setCellValue('A' . ($index + 1), $summaryRow[0]);
                $summarySheet->setCellValue('B' . ($index + 1), $summaryRow[1]);
            }
            $summarySheet->getStyle('A1:A' . count($summaryRows))->getFont()->setBold(true);
            $summarySheet->getStyle('B1:B' . count($summaryRows))->getAlignment()->setWrapText(true);
            $summarySheet->getColumnDimension('A')->setWidth(30);
            $summarySheet->getColumnDimension('B')->setWidth(70);
            $spreadsheet->setActiveSheetIndex(0);

            $start = preg_replace('/[^0-9-]/', '', (string)($periodo['fecha_inicio'] ?? date('Y-m-d')));
            $end = preg_replace('/[^0-9-]/', '', (string)($periodo['fecha_fin'] ?? date('Y-m-d')));
            $filename = 'asistencias_atlas_' . $start . '_a_' . $end . '.xlsx';
            $tmp = tempnam(sys_get_temp_dir(), 'atlas_asistencias_');
            if ($tmp === false) {
                throw new \RuntimeException('No se pudo preparar el archivo temporal.');
            }
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
            $spreadsheet->disconnectWorksheets();

            if (function_exists('session_write_close')) {
                session_write_close();
            }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($tmp));
            header('Cache-Control: max-age=0');
            readfile($tmp);
            @unlink($tmp);
            exit;
        } catch (\Throwable $e) {
            if (is_string($tmp) && is_file($tmp)) {
                @unlink($tmp);
            }
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'No se pudo generar el reporte de asistencias: ' . $e->getMessage();
            exit;
        }
    }

    private function normalizarReporteAsistencias(array $datos): array
    {
        unset($datos['contratos_faltantes']);
        $filas = is_array($datos['filas'] ?? null) ? $datos['filas'] : [];
        if (!is_array($datos['filas'] ?? null)) {
            $datos['filas'] = [];
            return $datos;
        }

        $sinClaveSucursal = 0;
        $grupos = [];
        foreach ($filas as $index => $fila) {
            if (!is_array($fila) || ($fila['es_visita'] ?? true) === false) {
                continue;
            }
            $clave = $this->claveAgenciaAsistencia($fila, (int)$index, $sinClaveSucursal);
            $hora = $this->etiquetaHoraVisitaAgencia($fila);
            $grupos[$clave][] = $hora;
        }

        foreach ($grupos as &$visitas) {
            usort($visitas, static function (array $a, array $b): int {
                return strcmp((string)($a['orden'] ?? ''), (string)($b['orden'] ?? ''));
            });
        }
        unset($visitas);

        foreach ($filas as $index => &$fila) {
            if (!is_array($fila)) {
                continue;
            }
            $clave = $this->claveAgenciaAsistencia($fila, (int)$index);
            $visitas = $grupos[$clave] ?? [];
            $fila['visitas_agencia_total'] = count($visitas);
            $fila['visitas_agencia_horarios'] = array_map(
                static fn(array $visita): array => [
                    'fecha' => $visita['fecha'] ?? '',
                    'hora_inicio' => $visita['hora_inicio'] ?? '',
                    'hora_fin' => $visita['hora_fin'] ?? '',
                    'etiqueta' => $visita['etiqueta'] ?? '',
                    'hora_disponible' => !empty($visita['hora_disponible']),
                ],
                $visitas
            );
        }
        unset($fila);

        $datos['filas'] = $filas;
        return $datos;
    }

    private function normalizarCreditosSucursalesAsistencia($datos): array
    {
        $payload = is_array($datos) ? $datos : [];
        $registros = $this->extraerListaApiComercial(
            $payload,
            ['registros', 'items', 'filas', 'data']
        );
        $normalizados = [];
        foreach ($registros as $registro) {
            if (!is_array($registro)) {
                continue;
            }
            $fkSucursal = (int)($registro['fk_sucursal'] ?? 0);
            $mes = trim((string)($registro['mes'] ?? ''));
            if ($fkSucursal <= 0 || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
                continue;
            }

            $gestionados = max(0, (int)($registro['creditos_gestionados']
                ?? $registro['creditos_dictaminados']
                ?? 0));
            $dictaminados = max(0, (int)($registro['creditos_dictaminados'] ?? $gestionados));
            $normalizados[] = [
                'fk_sucursal' => $fkSucursal,
                'sucursal' => trim((string)($registro['sucursal'] ?? '')),
                'mes' => $mes,
                'creditos_pendientes' => max(0, (int)($registro['creditos_pendientes'] ?? 0)),
                'creditos_rezagados' => max(0, (int)($registro['creditos_rezagados'] ?? 0)),
                'creditos_gestionados' => $gestionados,
                'creditos_dictaminados' => $dictaminados,
                'creditos_vendidos' => max(0, (int)($registro['creditos_vendidos'] ?? 0)),
                'total_creditos' => max(0, (int)($registro['total_creditos']
                    ?? $registro['total_creditos_unicos']
                    ?? 0)),
            ];
        }

        return [
            'periodo' => is_array($payload['periodo'] ?? null) ? $payload['periodo'] : [],
            'registros' => $normalizados,
            'total' => count($normalizados),
            'total_formula' => trim((string)($payload['total_formula'] ?? '')),
        ];
    }

    private function claveAgenciaAsistencia(array $fila, int $index, ?int &$sinClaveSucursal = null): string
    {
        foreach (['fk_sucursal', 'ruta_sucursal_id'] as $campo) {
            $valor = trim((string)($fila[$campo] ?? ''));
            if ($valor !== '') {
                return $campo . ':' . $valor;
            }
        }

        if ($sinClaveSucursal !== null) {
            $sinClaveSucursal++;
        }
        $agencia = mb_strtolower(trim((string)($fila['agencia'] ?? '')), 'UTF-8');
        $distribuidor = mb_strtolower(trim((string)($fila['distribuidor'] ?? '')), 'UTF-8');
        $claveNombre = trim($agencia . '|' . $distribuidor, '|');
        return $claveNombre !== '' ? 'agencia:' . $claveNombre : 'fila:' . $index;
    }

    private function etiquetaHoraVisitaAgencia(array $fila): array
    {
        $fecha = trim((string)($fila['fecha'] ?? ''));
        $horaInicio = $this->primerCampoTexto($fila, ['hora_llegada', 'hora_confirmacion_llegada', 'hora_gestion']);
        $horaFin = $this->primerCampoTexto($fila, ['hora_salida', 'hora_termino_visita']);
        $partes = [];
        if ($fecha !== '') {
            $partes[] = $fecha;
        }
        if ($horaInicio !== '' && $horaFin !== '') {
            $partes[] = $horaInicio . '-' . $horaFin;
        } elseif ($horaInicio !== '') {
            $partes[] = $horaInicio;
        } elseif ($horaFin !== '') {
            $partes[] = 'Salida ' . $horaFin;
        } else {
            $partes[] = 'Hora no disponible';
        }

        return [
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'etiqueta' => implode(' ', $partes),
            'hora_disponible' => $horaInicio !== '' || $horaFin !== '',
            'orden' => trim($fecha . ' ' . $horaInicio . ' ' . $horaFin),
        ];
    }

    private function primerCampoTexto(array $fila, array $campos): string
    {
        foreach ($campos as $campo) {
            $valor = trim((string)($fila[$campo] ?? ''));
            if ($valor !== '') {
                return $valor;
            }
        }
        return '';
    }

    private function agruparAsistenciasPorColaborador(array $filas): array
    {
        $grupos = [];
        foreach ($filas as $index => $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $personaId = (int)($fila['colaborador_persona_id'] ?? $fila['gestor_persona_id'] ?? 0);
            $numeroEmpleado = trim((string)($fila['numero_empleado'] ?? ''));
            $nombre = trim((string)($fila['colaborador'] ?? ''));
            if ($personaId > 0) {
                $clave = 'persona:' . $personaId;
            } elseif ($numeroEmpleado !== '') {
                $clave = 'empleado:' . mb_strtolower($numeroEmpleado, 'UTF-8');
            } elseif ($nombre !== '') {
                $clave = 'nombre:' . mb_strtolower($nombre, 'UTF-8');
            } else {
                $clave = 'sin-identidad:' . $index;
            }

            if (!isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'colaborador' => $nombre !== '' ? $nombre : 'Sin asignar',
                    'numero_empleado' => $numeroEmpleado,
                    'puesto' => trim((string)($fila['puesto'] ?? '')),
                    'rol' => trim((string)($fila['rol'] ?? '')),
                    'divisional' => trim((string)($fila['divisional'] ?? '')),
                    'gestiones_realizadas' => 0,
                    'dias_programados' => [],
                    'dias_con_asistencia' => [],
                    'filas' => [],
                ];
            }

            $grupos[$clave]['filas'][] = $fila;
            $grupos[$clave]['gestiones_realizadas'] += max(0, (int)($fila['gestiones_realizadas'] ?? 0));
            if (($fila['es_visita'] ?? true) === false) {
                continue;
            }

            $fecha = trim((string)($fila['fecha'] ?? ''));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                continue;
            }
            $grupos[$clave]['dias_programados'][$fecha] = true;
            if ($this->filaAsistenciaTieneCheckin($fila)) {
                $grupos[$clave]['dias_con_asistencia'][$fecha] = true;
            }
        }

        $resultado = [];
        foreach ($grupos as $grupo) {
            $diasSinAsistencia = array_diff_key(
                $grupo['dias_programados'],
                $grupo['dias_con_asistencia']
            );
            $resultado[] = [
                'colaborador' => $grupo['colaborador'],
                'numero_empleado' => $grupo['numero_empleado'],
                'puesto' => $grupo['puesto'],
                'rol' => $grupo['rol'],
                'divisional' => $grupo['divisional'],
                'dias_con_asistencia' => count($grupo['dias_con_asistencia']),
                'dias_sin_asistencia' => count($diasSinAsistencia),
                'gestiones_realizadas' => $grupo['gestiones_realizadas'],
                'gestiones_pendientes' => $this->totalPendientesAsistencias($grupo['filas']),
            ];
        }
        usort(
            $resultado,
            static fn(array $a, array $b): int => strcasecmp(
                (string)($a['colaborador'] ?? ''),
                (string)($b['colaborador'] ?? '')
            )
        );

        return $resultado;
    }

    private function filaAsistenciaTieneCheckin(array $fila): bool
    {
        if (($fila['es_visita'] ?? true) === false) {
            return false;
        }
        foreach (['hora_llegada', 'hora_confirmacion_llegada', 'checkin_at'] as $campo) {
            if (trim((string)($fila[$campo] ?? '')) !== '') {
                return true;
            }
        }
        return false;
    }

    private function filtrarAsistenciasPorEvidencias(array $filas, string $filtro): array
    {
        return array_values(array_filter(
            $filas,
            function ($fila) use ($filtro): bool {
                return is_array($fila) && $this->estadoEvidenciasAsistencia($fila) === $filtro;
            }
        ));
    }

    private function filtrarAsistenciasPorRutas(array $filas, string $filtro): array
    {
        return array_values(array_filter(
            $filas,
            function ($fila) use ($filtro): bool {
                return is_array($fila) && $this->estadoRutasAsistencia($fila) === $filtro;
            }
        ));
    }

    private function estadoEvidenciasAsistencia(array $fila): string
    {
        $evidencias = is_array($fila['evidencias'] ?? null) ? $fila['evidencias'] : [];
        $totalDeclarado = array_key_exists('total_evidencias', $fila)
            && $fila['total_evidencias'] !== null
            ? max(0, (int)$fila['total_evidencias'])
            : count($evidencias);
        if ($totalDeclarado === 0 && $evidencias === []) {
            return 'sin';
        }

        if ($evidencias === [] || $totalDeclarado > count($evidencias)) {
            return 'incompletas';
        }
        foreach ($evidencias as $evidencia) {
            $disponible = is_array($evidencia)
                && filter_var($evidencia['disponible'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (!$disponible || empty($evidencia['id'])) {
                return 'incompletas';
            }
        }

        return 'con';
    }

    private function estadoRutasAsistencia(array $fila): string
    {
        if (is_array($fila['rutas'] ?? null)) {
            return $fila['rutas'] !== [] ? 'con' : 'sin';
        }

        foreach (['total_rutas', 'rutas_total', 'rutas_generadas'] as $campo) {
            if (array_key_exists($campo, $fila) && trim((string)$fila[$campo]) !== '') {
                return (int)$fila[$campo] > 0 ? 'con' : 'sin';
            }
        }

        $rutaSucursal = trim((string)($fila['ruta_sucursal_id'] ?? ''));
        return $rutaSucursal !== '' && $rutaSucursal !== '0' ? 'con' : 'sin';
    }

    private function resumenAsistenciasFiltradas(array $filas, array $resumenOriginal): array
    {
        $resumen = array_merge($resumenOriginal, [
            'total_visitas' => 0,
            'cumplidas' => 0,
            'no_realizadas' => 0,
            'fuera_ubicacion' => 0,
            'en_visita' => 0,
            'programadas' => 0,
            'gestiones_realizadas' => 0,
            'pendientes_por_gestionar' => 0,
        ]);

        foreach ($filas as $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $esVisita = ($fila['es_visita'] ?? true) !== false;
            $estatus = trim((string)($fila['estatus_visita'] ?? ''));
            if ($esVisita) {
                $resumen['total_visitas']++;
                if ($estatus === 'Cumplida') {
                    $resumen['cumplidas']++;
                } elseif ($estatus === 'No realizada') {
                    $resumen['no_realizadas']++;
                } elseif ($estatus === 'En visita') {
                    $resumen['en_visita']++;
                } elseif ($estatus === 'Programada') {
                    $resumen['programadas']++;
                }
            }
            if (strpos($estatus, 'Fuera de ubicaci') === 0) {
                $resumen['fuera_ubicacion']++;
            }
            $resumen['gestiones_realizadas'] += (int)($fila['gestiones_realizadas'] ?? 0);
        }
        $resumen['pendientes_por_gestionar'] = $this->totalPendientesAsistencias($filas);

        return $resumen;
    }

    private function totalPendientesAsistencias(array $filas): int
    {
        $pendientesPorSucursal = [];
        foreach ($filas as $index => $fila) {
            if (!is_array($fila) || ($fila['es_visita'] ?? true) === false) {
                continue;
            }
            if (!array_key_exists('pendientes_por_gestionar', $fila)
                || $fila['pendientes_por_gestionar'] === null) {
                continue;
            }

            $sucursal = trim((string)($fila['fk_sucursal'] ?? $fila['ruta_sucursal_id'] ?? ''));
            $clave = $sucursal !== '' ? $sucursal : 'fila:' . $index;
            $pendientesPorSucursal[$clave] = max(
                (int)($pendientesPorSucursal[$clave] ?? 0),
                max(0, (int)$fila['pendientes_por_gestionar'])
            );
        }

        return array_sum($pendientesPorSucursal);
    }

    private function etiquetaFiltroEvidencias(string $filtro): string
    {
        return [
            'con' => 'Con evidencias',
            'sin' => 'Sin evidencias',
            'incompletas' => 'Incompletas',
        ][$filtro] ?? 'Todas';
    }

    private function etiquetaFiltroRutas(string $filtro): string
    {
        return [
            'con' => 'Con rutas',
            'sin' => 'Sin rutas',
        ][$filtro] ?? 'Todas';
    }

    private function reporteAsistenciasQuery(): array
    {
        $query = [];
        foreach (['fecha_inicio', 'fecha_fin', 'estatus', 'divisional'] as $key) {
            $value = trim((string)($_GET[$key] ?? ''));
            if ($value !== '') {
                $query[$key] = $value;
            }
        }
        foreach (['gestor_persona_id', 'distribuidor_id'] as $key) {
            $value = (int)($_GET[$key] ?? 0);
            if ($value > 0) {
                $query[$key] = $value;
            }
        }
        return $query;
    }

    private function rutasUsuarioSpartanQuery(): array
    {
        $personaId = (int)($_GET['gestor_persona_id'] ?? $_GET['persona_id'] ?? 0);
        $externalId = trim((string)($_GET['external_id'] ?? ''));
        $userId = trim((string)($_GET['user_id'] ?? ''));

        if ($externalId === '' && $personaId > 0) {
            $externalId = $this->numeroEmpleadoPersona($personaId);
        }

        if ($personaId <= 0 && $externalId === '' && $userId === '') {
            $personaSesion = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? 0);
            if ($personaSesion > 0) {
                $personaId = $personaSesion;
                $externalId = $this->numeroEmpleadoPersona($personaSesion);
            }
        }

        $query = [];
        if ($personaId > 0) {
            $query['gestor_persona_id'] = $personaId;
        }
        if ($externalId !== '') {
            $query['external_id'] = $externalId;
        }
        if ($userId !== '') {
            $query['user_id'] = $userId;
        }

        foreach (['fecha_inicio', 'fecha_fin'] as $key) {
            $value = trim((string)($_GET[$key] ?? ''));
            if ($value !== '') {
                $query[$key] = $value;
            }
        }

        $limit = (int)($_GET['limit'] ?? 100);
        $query['limit'] = max(1, min(100, $limit > 0 ? $limit : 100));

        return [
            'query' => $query,
            'usuario' => [
                'persona_id' => $personaId > 0 ? $personaId : null,
                'external_id' => $externalId !== '' ? $externalId : null,
                'user_id' => $userId !== '' ? $userId : null,
            ],
        ];
    }

    private function rutasUsuarioSpartanApiResponse(array $contexto): array
    {
        $query = is_array($contexto['query'] ?? null) ? $contexto['query'] : [];
        if (empty($query['gestor_persona_id']) && empty($query['external_id']) && empty($query['user_id'])) {
            return [
                'success' => false,
                'status' => 400,
                'mensaje' => 'No pudimos identificar al usuario para consultar sus rutas.',
                'datos' => [
                    'rutas' => [],
                    'total' => 0,
                ],
            ];
        }

        $response = $this->atlasAdminApiRequest(
            'GET',
            '/api/atlas/admin/reportes/asistencias/rutas',
            null,
            $query
        );

        if (!empty($response['success'])) {
            $normalizado = $this->normalizarRutasSpartan($response['datos'] ?? null);
            $normalizado['usuario'] = is_array($contexto['usuario'] ?? null) ? $contexto['usuario'] : [];
            $response['datos'] = $normalizado;
            return $response;
        }

        $status = (int)($response['status'] ?? 0);
        if (in_array($status, [204, 404], true)) {
            return [
                'success' => true,
                'status' => $status,
                'mensaje' => 'Sin rutas generadas para este usuario en el periodo filtrado.',
                'datos' => [
                    'rutas' => [],
                    'total' => 0,
                    'usuario' => is_array($contexto['usuario'] ?? null) ? $contexto['usuario'] : [],
                ],
            ];
        }

        $response['mensaje'] = 'El servicio de consulta de rutas de usuarios se encuentra en mantenimiento, en breves se desplegara y podras ver de nuevo las rutas';
        $response['datos'] = [
            'rutas' => [],
            'total' => 0,
        ];
        unset($response['error']);
        return $response;
    }

    private function normalizarRutasSpartan($datos): array
    {
        $rutas = $this->extraerListaApiComercial($datos, ['rutas', 'items', 'filas', 'registros', 'data']);
        $rutas = array_values(array_filter($rutas, 'is_array'));
        foreach ($rutas as &$ruta) {
            $visitas = [];
            foreach (['sucursales', 'visitas', 'detalle_visitas'] as $key) {
                if (is_array($ruta[$key] ?? null)) {
                    $visitas = $ruta[$key];
                    break;
                }
            }
            if (!array_key_exists('total_visitas', $ruta)) {
                $ruta['total_visitas'] = count(array_filter($visitas, 'is_array'));
            }
        }
        unset($ruta);

        $total = is_array($datos) && isset($datos['total']) ? (int)$datos['total'] : count($rutas);
        return [
            'rutas' => $rutas,
            'total' => $total,
        ];
    }

    private function normalizarResumenCoberturaAsistencia($datos, string $mes): array
    {
        $payload = is_array($datos) ? $datos : [];
        $resumen = is_array($payload['resumen'] ?? null)
            ? $payload['resumen']
            : $payload;
        $valor = static function (array $source, array $keys, $default = null) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $source)) {
                    return $source[$key];
                }
            }
            return $default;
        };

        $agenciasEnRuta = max(0, (int)$valor(
            $resumen,
            ['total_agencias_en_ruta', 'totalAgenciasEnRuta', 'agencias_en_ruta'],
            0
        ));
        $agenciasAsignadas = $valor(
            $resumen,
            ['agencias_asignadas', 'agenciasAsignadas'],
            null
        );
        if ($agenciasAsignadas !== null) {
            $agenciasAsignadas = max($agenciasEnRuta, (int)$agenciasAsignadas);
        }
        $visitasVencidas = max(0, (int)$valor(
            $resumen,
            ['visitas_vencidas', 'visitasVencidas'],
            0
        ));
        $agenciasAgendadasSinCheckin = [];
        $tieneDetalleVisitas = false;
        foreach ((array)($payload['dias'] ?? []) as $dia) {
            if (!is_array($dia)) {
                continue;
            }
            foreach ((array)($dia['visitas'] ?? []) as $visita) {
                if (!is_array($visita)) {
                    continue;
                }
                $tieneDetalleVisitas = true;
                $estatus = mb_strtolower(trim((string)($visita['estatus_visita'] ?? '')), 'UTF-8');
                $checkin = trim((string)($visita['checkin_at'] ?? ''));
                if ($estatus !== 'vencida' || $checkin !== '') {
                    continue;
                }
                $clave = trim((string)($visita['fk_sucursal'] ?? ''));
                if ($clave === '') {
                    $clave = trim((string)($visita['ruta_sucursal_id'] ?? $visita['sucursal'] ?? ''));
                }
                if ($clave !== '') {
                    $agenciasAgendadasSinCheckin[$clave] = true;
                }
            }
        }

        return [
            'mes' => trim((string)$valor($resumen, ['mes'], $mes)) ?: $mes,
            'agencias_asignadas' => $agenciasAsignadas,
            'agencias_en_ruta' => $agenciasEnRuta,
            'agencias_visitadas' => max(0, (int)$valor(
                $resumen,
                ['agencias_visitadas', 'agenciasVisitadas'],
                0
            )),
            'agencias_pendientes' => max(0, (int)$valor(
                $resumen,
                ['agencias_pendientes', 'agenciasPendientes'],
                0
            )),
            'porcentaje_cobertura' => max(0, min(100, (int)round((float)$valor(
                $resumen,
                ['porcentaje_cobertura', 'porcentajeCobertura'],
                0
            )))),
            'total_visitas_programadas' => max(0, (int)$valor(
                $resumen,
                ['total_visitas_programadas', 'totalVisitasProgramadas'],
                0
            )),
            'visitas_realizadas' => max(0, (int)$valor(
                $resumen,
                ['visitas_realizadas', 'visitasRealizadas'],
                0
            )),
            'visitas_vencidas' => $visitasVencidas,
            'agencias_agendadas_sin_checkin' => $tieneDetalleVisitas
                ? count($agenciasAgendadasSinCheckin)
                : $visitasVencidas,
        ];
    }

    private function extraerListaApiComercial($datos, array $keys): array
    {
        if (!is_array($datos)) {
            return [];
        }
        if ($this->esLista($datos)) {
            return $datos;
        }
        foreach ($keys as $key) {
            if (isset($datos[$key]) && is_array($datos[$key])) {
                return $this->esLista($datos[$key])
                    ? $datos[$key]
                    : $this->extraerListaApiComercial($datos[$key], $keys);
            }
        }
        return [];
    }

    private function esLista(array $items): bool
    {
        $expected = 0;
        foreach ($items as $key => $_value) {
            if ($key !== $expected) {
                return false;
            }
            $expected++;
        }
        return true;
    }

    private function numeroEmpleadoPersona(int $personaId): string
    {
        if ($personaId <= 0) {
            return '';
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT TRIM(COALESCE(numero_empleado, '')) AS numero_empleado FROM persona WHERE id = :id LIMIT 1",
                ['id' => $personaId]
            );
            return trim((string)($row['numero_empleado'] ?? ''));
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function sucursalesAsignadas()
    {
        $this->set('titulo', 'Seguimiento');
        $this->render('atlas_sucursales_asignadas');
    }

    public function seguimiento()
    {
        header('Location: /Atlas/presupuestos', true, 302);
        exit;
    }

    public function getSucursales()
    {
        $this->json(AtlasDAO::getSucursales());
    }

    public function guardarConfiguracionCalidadSucursales()
    {
        $this->json(AtlasDAO::guardarConfiguracionCalidadSucursales($this->payload()));
    }

    public function guardarConfiguracionHorarioOperativoRutas()
    {
        $this->json(AtlasDAO::guardarConfiguracionHorarioOperativoRutas($this->payload()));
    }

    public function getCatalogos()
    {
        $this->json(AtlasDAO::getCatalogos());
    }

    public function getMunicipiosPresencia()
    {
        $this->json(AtlasDAO::getMunicipiosPresencia((int)($_GET['estado_id'] ?? 0)));
    }

    public function getCatalogosComerciales()
    {
        $this->json(AtlasDAO::getCatalogosComerciales());
    }

    public function getRiesgosOperativos()
    {
        $this->json(AtlasDAO::getRiesgosOperativos());
    }

    public function getAccesosAtlas()
    {
        $this->json(AtlasDAO::getAccesosAtlas());
    }

    public function sincronizarAccesosAtlas()
    {
        $this->json(AtlasDAO::sincronizarAccesosAtlasComercialMexico());
    }

    public function actualizarExclusionAccesosAtlas()
    {
        $payload = $this->payload();
        $payload['_usuario_id'] = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::actualizarExclusionAccesosAtlas($payload));
    }

    public function getAccesoAtlasDetalle()
    {
        $this->json(AtlasDAO::getAccesoAtlasDetalle((int)($_GET['id'] ?? 0)));
    }

    public function guardarPermisosAccesoAtlas()
    {
        $this->json(AtlasDAO::guardarPermisosAccesoAtlas($this->payload()));
    }

    public function restablecerPasswordAccesoAtlas()
    {
        $payload = $this->payload();
        $payload['_usuario_id'] = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::restablecerPasswordAccesoAtlas($payload));
    }

    public function getRutasGestores()
    {
        $this->json(AtlasDAO::getRutasGestores());
    }

    public function getRutaGestorDetalle()
    {
        $this->json(AtlasDAO::getRutaGestorDetalle((int)($_GET['id'] ?? 0)));
    }

    public function pdfRutaGestor()
    {
        $id = (int)($_GET['id'] ?? 0);
        $res = AtlasDAO::getRutaGestorResumenTecnico($id);
        if (!$res['success']) {
            http_response_code(404);
            echo htmlspecialchars($res['mensaje'] ?? 'No se pudo generar el PDF.', ENT_QUOTES, 'UTF-8');
            exit;
        }
        $datos = $res['datos'];
        $ruta = $datos['ruta'];
        $resumen = $datos['resumen'];
        $sucursales = $datos['sucursales'];
        $creditos = $datos['creditos'];
        $h = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
        $money = static fn($v) => '$' . number_format((float)$v, 2, '.', ',');
        $num = static fn($v) => number_format((float)$v, 0, '.', ',');
        $fechaGeneracion = (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('d/m/Y H:i');
        $clasificaciones = '';
        foreach (($resumen['clasificaciones'] ?? []) as $nombre => $total) {
            $clasificaciones .= '<span class="pill">' . $h($nombre) . ': ' . $num($total) . '</span>';
        }
        $estados = '';
        foreach (($resumen['estados'] ?? []) as $nombre => $total) {
            $estados .= '<span class="pill">' . $h($nombre) . ': ' . $num($total) . '</span>';
        }
        $alertas = [];
        if ((int)($resumen['sucursales_sin_coordenadas'] ?? 0) > 0) {
            $alertas[] = $num($resumen['sucursales_sin_coordenadas']) . ' sucursal(es) sin coordenadas.';
        }
        if ((int)($resumen['sucursales_sin_telefono'] ?? 0) > 0) {
            $alertas[] = $num($resumen['sucursales_sin_telefono']) . ' sucursal(es) sin telefono principal.';
        }
        if (!$alertas) {
            $alertas[] = 'Sin alertas criticas detectadas en sucursales de la ruta.';
        }

        $rowsSuc = '';
        foreach ($sucursales as $s) {
            $lat = trim((string)($s['latitud'] ?? ''));
            $lng = trim((string)($s['longitud'] ?? ''));
            $coords = $lat !== '' && $lng !== ''
                ? '<a class="mini-card map-link" href="https://www.google.com/maps?q=' . rawurlencode($lat . ',' . $lng) . '" target="_blank" rel="noopener">Maps</a>'
                : '<span class="mini-card">Sin coordenadas</span>';
            $clasificacion = '<span class="mini-card">' . $h($s['clasificacion_nombre']) . '</span>';
            $visita = '<span class="mini-card">Fecha ' . $h($s['fecha_inicio_visita'] ?? '') . '</span>';
            $horario = '<span class="mini-card">Estancia ' . $h($s['estancia_valor'] ?? 45) . ' ' . $h($s['estancia_unidad'] ?? 'minutos') . '</span>';
            $detalleVisita = '<div class="inline-cards">' . $clasificacion . $coords . $visita . $horario . '</div>';
            $rowsSuc .= '<tr>
                <td class="center">' . $num($s['orden_visita'] ?? 0) . '</td>
                <td><strong>' . $h($s['sucursal']) . '</strong><br><span class="muted">FK ' . $h($s['fk_sucursal']) . ' · ' . $h($s['distribuidor']) . '</span></td>
                <td>' . $h($s['direccion']) . '<br><span class="muted">' . $h($s['municipio']) . ', ' . $h($s['estado']) . ' · CP ' . $h($s['codigo_postal']) . '</span></td>
                <td>' . $detalleVisita . '</td>
                <td class="right">' . $num($s['meta_creditos']) . '<br><span class="muted">' . $money($s['meta_cash']) . '</span></td>
                <td class="right">' . $num($s['total_creditos']) . '<br><span class="muted">' . $money($s['cash_detenido_operativo']) . '</span></td>
            </tr>';
        }
        if ($rowsSuc === '') {
            $rowsSuc = '<tr><td colspan="6" class="center muted">La ruta no tiene visitas activas.</td></tr>';
        }

        $rowsCred = '';
        foreach ($creditos as $c) {
            $rowsCred .= '<tr>
                <td>' . $h($c['id_solicitud'] ?: $c['credito_id']) . '</td>
                <td>' . $h($c['sucursal']) . '<br><span class="muted">FK ' . $h($c['fk_sucursal']) . '</span></td>
                <td>' . $h($c['bucket_actual'] ?: $c['tipo_bucket_actual']) . '</td>
                <td class="right">' . $money($c['monto_financiar']) . '</td>
                <td>' . $h($c['fecha_ultima_sync']) . '</td>
            </tr>';
        }
        if ($rowsCred === '') {
            $rowsCred = '<tr><td colspan="5" class="center muted">No hay creditos detenidos/pendientes operativos en esta ruta.</td></tr>';
        }

        $html = '<!doctype html><html><head><meta charset="utf-8"><style>
            body{font-family:DejaVu Sans,Arial,sans-serif;color:#1f2937;font-size:10px}
            h1{font-size:20px;margin:0 0 4px;color:#172554}
            h2{font-size:13px;margin:16px 0 7px;color:#1e3a8a;border-bottom:1px solid #dbeafe;padding-bottom:4px}
            .muted{color:#64748b;font-size:9px}.right{text-align:right}.center{text-align:center}
            .top{display:flex;justify-content:space-between;gap:12px;border-bottom:3px solid #1d4ed8;padding-bottom:10px;margin-bottom:10px}
            .boxgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:10px 0}
            .box{border:1px solid #dbeafe;background:#f8fafc;border-radius:6px;padding:8px}
            .box .lbl{font-size:8px;text-transform:uppercase;color:#64748b;font-weight:bold}.box .val{font-size:15px;color:#111827;font-weight:bold}
            table{width:100%;border-collapse:collapse;margin-top:6px}th{background:#eff6ff;color:#1e3a8a;text-align:left;font-size:8px;text-transform:uppercase}
            th,td{border:1px solid #e5e7eb;padding:5px;vertical-align:top}.pill{display:inline-block;border:1px solid #bfdbfe;background:#eff6ff;color:#1e40af;border-radius:10px;padding:3px 7px;margin:2px;font-size:9px}
            .inline-cards{white-space:nowrap;display:block}.mini-card{display:inline-block;border:1px solid #dbeafe;background:#f8fbff;color:#1f2937;border-radius:8px;padding:2px 5px;margin:0 2px 0 0;font-size:7.5px;font-weight:bold;white-space:nowrap}.map-link{color:#1d4ed8;text-decoration:none}
            .alert{border:1px solid #fde68a;background:#fffbeb;color:#92400e;padding:6px;margin:4px 0;border-radius:6px}
        </style></head><body>
            <div class="top">
                <div><h1>Resumen tecnico de ruta Atlas</h1><div class="muted">Generado CDMX: ' . $h($fechaGeneracion) . '</div></div>
                <div class="right"><strong>Ruta #' . $h($ruta['id']) . '</strong><br>' . $h($ruta['estatus']) . '</div>
            </div>
            <h2>Identificacion de ruta</h2>
            <table><tr><th>Ruta</th><th>Gestor</th><th>Periodo</th><th>Presupuesto</th></tr><tr>
                <td><strong>' . $h($ruta['nombre_ruta']) . '</strong><br><span class="muted">Tipo ' . $h($ruta['tipo_ruta']) . ' · Prioridad ' . $h($ruta['prioridad']) . ' · Criterio ' . $h($ruta['criterio_prioridad']) . '</span></td>
                <td><strong>' . $h($ruta['gestor_persona_nombre'] ?: $ruta['gestor_nombre']) . '</strong><br><span class="muted">' . $h($ruta['gestor_numero_empleado_actual'] ?: $ruta['gestor_numero_empleado']) . ' · ' . $h($ruta['gestor_departamento']) . ' / ' . $h($ruta['gestor_puesto']) . '</span><br><span class="muted">' . $h($ruta['gestor_correo']) . ' ' . $h($ruta['gestor_telefono']) . '</span></td>
                <td>' . $h($ruta['fecha_inicio_fmt']) . ' a ' . $h($ruta['fecha_fin_fmt']) . '</td>
                <td><strong>' . $h($ruta['presupuesto_mes']) . ' ' . $h($ruta['presupuesto_anio']) . '</strong></td>
            </tr></table>
            <div class="boxgrid">
                <div class="box"><div class="lbl">Visitas</div><div class="val">' . $num($resumen['total_sucursales']) . '</div></div>
                <div class="box"><div class="lbl">Meta creditos</div><div class="val">' . $num($resumen['meta_creditos']) . '</div></div>
                <div class="box"><div class="lbl">Meta cash</div><div class="val">' . $money($resumen['meta_cash']) . '</div></div>
                <div class="box"><div class="lbl">Cash operativo</div><div class="val">' . $money($resumen['cash_operativo']) . '</div></div>
            </div>
            <h2>Lecturas explotables</h2>
            <div><strong>Clasificaciones:</strong> ' . $clasificaciones . '</div>
            <div><strong>Estados:</strong> ' . $estados . '</div>
            <div><strong>Calidad operativa:</strong> ' . $num($resumen['sucursales_con_coordenadas']) . ' con coordenadas · ' . $num($resumen['sucursales_sin_coordenadas']) . ' sin coordenadas · ' . $num($resumen['sucursales_sin_telefono']) . ' sin telefono</div>
            <h2>Alertas</h2>' . implode('', array_map(static fn($a) => '<div class="alert">' . $h($a) . '</div>', $alertas)) . '
            <h2>Detalle tecnico de visitas</h2>
            <table><colgroup><col style="width:4%"><col style="width:20%"><col style="width:28%"><col style="width:28%"><col style="width:10%"><col style="width:10%"></colgroup><thead><tr><th>#</th><th>Sucursal</th><th>Direccion</th><th>Detalle visita</th><th class="right">Meta</th><th class="right">Operacion</th></tr></thead><tbody>' . $rowsSuc . '</tbody></table>
            <h2>Creditos pendientes/detenidos vinculados</h2>
            <table><thead><tr><th>Credito</th><th>Sucursal</th><th>Bucket</th><th class="right">Monto</th><th>Ultima sync</th></tr></thead><tbody>' . $rowsCred . '</tbody></table>
            <h2>Observaciones</h2><div>' . nl2br($h($ruta['observaciones'] ?: 'Sin observaciones capturadas.')) . '</div>
        </body></html>';

        $autoload = dirname(RAIZ) . '/vendor/autoload.php';
        if (!class_exists(\Mpdf\Mpdf::class) && is_file($autoload)) {
            require_once $autoload;
        }
        if (!class_exists(\Mpdf\Mpdf::class)) {
            http_response_code(500);
            echo 'No esta disponible la libreria mPDF para generar el resumen tecnico.';
            exit;
        }
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'Letter',
            'orientation' => 'L',
            'tempDir' => sys_get_temp_dir(),
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ]);
        $mpdf->SetTitle('Resumen tecnico ruta ' . (int)$ruta['id']);
        $mpdf->WriteHTML($html);
        $filename = 'atlas_ruta_' . (int)$ruta['id'] . '_resumen_tecnico.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    public function getRutasGestoresCatalogos()
    {
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::getRutasGestoresCatalogos($usuarioId));
    }

    public function getSucursalesAsignadas()
    {
        $this->json(AtlasDAO::getSucursalesAsignadas());
    }

    public function getSucursalAsignadaDetalle()
    {
        $this->json(AtlasDAO::getSucursalAsignadaDetalle((int)($_GET['fk_sucursal'] ?? 0)));
    }

    public function getGestoresOperativos()
    {
        $this->json(AtlasDAO::getGestoresOperativos());
    }

    public function guardarGestorOperativo()
    {
        $this->json(AtlasDAO::guardarGestorOperativo($this->payload()));
    }

    public function eliminarGestorOperativo()
    {
        $this->json(AtlasDAO::eliminarGestorOperativo($this->payload()));
    }

    public function guardarGestorSucursal()
    {
        $this->json(AtlasDAO::guardarGestorSucursal($this->payload()));
    }

    public function eliminarGestorSucursal()
    {
        $this->json(AtlasDAO::eliminarGestorSucursal($this->payload()));
    }

    public function guardarRutaGestor()
    {
        $this->json(AtlasDAO::guardarRutaGestor($this->payload()));
    }

    public function guardarRutaSucursal()
    {
        $this->json(AtlasDAO::guardarRutaSucursal($this->payload()));
    }

    public function guardarRutaCredito()
    {
        $this->json(AtlasDAO::guardarRutaCredito($this->payload()));
    }

    public function actualizarEstatusRutaGestor()
    {
        $this->json(AtlasDAO::actualizarEstatusRutaGestor($this->payload()));
    }

    public function eliminarRutaSucursal()
    {
        $this->json(AtlasDAO::eliminarRutaSucursal($this->payload()));
    }

    public function guardarOrdenRutaSucursales()
    {
        $this->json(AtlasDAO::guardarOrdenRutaSucursales($this->payload()));
    }

    public function eliminarRutaCredito()
    {
        $this->json(AtlasDAO::eliminarRutaCredito($this->payload()));
    }

    public function guardarCatalogoComercial()
    {
        $this->json(AtlasDAO::guardarCatalogoComercial($this->payload()));
    }

    public function guardarCatalogosComercialesBloque()
    {
        $this->json(AtlasDAO::guardarCatalogosComercialesBloque($this->payload()));
    }

    public function guardarOrdenCatalogosComerciales()
    {
        $this->json(AtlasDAO::guardarOrdenCatalogosComerciales($this->payload()));
    }

    public function getPlantillasNotificaciones()
    {
        $this->json(AtlasDAO::getPlantillasNotificaciones());
    }

    public function guardarPlantillaNotificacion()
    {
        $this->json(AtlasDAO::guardarPlantillaNotificacion($this->payload()));
    }

    public function getUsuariosNotificacionesDisponibles()
    {
        $this->json(AtlasDAO::getUsuariosNotificacionesDisponibles());
    }

    public function getHistorialNotificacionesApp()
    {
        $this->json(AtlasDAO::getHistorialNotificacionesApp());
    }

    public function guardarSucursal()
    {
        $payload = $this->payload();
        $payload['_usuario_id'] = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::guardarSucursal($payload));
    }

    public function actualizarSucursalPendiente()
    {
        $this->json(AtlasDAO::actualizarSucursalPendiente($this->payload()));
    }

    public function guardarDivision()
    {
        $this->json(['success' => false, 'mensaje' => 'El catalogo de divisiones ya no esta vigente. Asigna responsables directamente en sucursales.']);
    }

    public function eliminarDivision()
    {
        $this->json(['success' => false, 'mensaje' => 'El catalogo de divisiones ya no esta vigente.']);
    }

    public function fusionarDivisiones()
    {
        $this->json(['success' => false, 'mensaje' => 'El catalogo de divisiones ya no esta vigente.']);
    }

    public function guardarAsignacionDivision()
    {
        $this->json(['success' => false, 'mensaje' => 'La asignacion por division ya no esta vigente. Asigna responsables directamente en sucursales.']);
    }

    public function getActualizacionesDivisionales()
    {
        $this->json(['success' => true, 'mensaje' => 'Sin actualizaciones pendientes.', 'datos' => [], 'total' => 0]);
    }

    public function crearDivisionalDesdePersona()
    {
        $payload = $this->payload();
        $payload['_usuario_id'] = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::crearDivisionalDesdePersona($payload));
    }

    public function desactivarDivisional()
    {
        $payload = $this->payload();
        $payload['_usuario_id'] = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::desactivarDivisional($payload));
    }

    public function guardarDistribuidor()
    {
        $payload = $this->payload();
        $payload['_usuario_id'] = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $this->json(AtlasDAO::guardarDistribuidor($payload));
    }

    public function subirConstanciaFiscalDistribuidor()
    {
        $this->json(AtlasDAO::subirConstanciaFiscalDistribuidor($_POST, $_FILES['archivo'] ?? []));
    }

    public function subirEstadoCuentaDistribuidor()
    {
        $this->json(AtlasDAO::subirEstadoCuentaDistribuidor($_POST, $_FILES['archivo'] ?? []));
    }

    public function guardarCatalogoDistribuidorOpcion()
    {
        $this->json(AtlasDAO::guardarCatalogoDistribuidorOpcion($this->payload()));
    }

    public function guardarClasificacion()
    {
        $this->json(AtlasDAO::guardarClasificacion($this->payload()));
    }

    public function guardarOrdenClasificaciones()
    {
        $this->json(AtlasDAO::guardarOrdenClasificaciones($this->payload()));
    }

    public function notificacionesAppProxy()
    {
        $payload = $this->payload();
        $method = strtoupper(trim((string)($payload['method'] ?? 'GET')));
        $path = trim((string)($payload['path'] ?? ''));
        $body = $payload['body'] ?? null;
        $query = is_array($payload['query'] ?? null) ? $payload['query'] : [];

        if (!in_array($method, ['GET', 'POST', 'PATCH', 'DELETE'], true)) {
            $this->json(['success' => false, 'mensaje' => 'Método no permitido.']);
        }

        if (!$this->atlasNotificacionesPathPermitido($path)) {
            $this->json(['success' => false, 'mensaje' => 'Endpoint Atlas App no permitido.']);
        }

        $adminApiKey = $this->atlasAdminApiKey();
        if ($adminApiKey === '') {
            $this->json(['success' => false, 'mensaje' => 'ATLAS_ADMIN_API_KEYS no está configurada en servidor.']);
        }

        $base = getenv('ATLAS_APP_API_BASE');
        if ($base === false || trim($base) === '') {
            $base = 'https://api-comercial-601258367060.us-central1.run.app';
        }
        $url = rtrim($base, '/') . $path;
        if ($method === 'GET' && $query) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        $headers[] = 'X-API-Key: ' . $adminApiKey;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 35,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(is_array($body) ? $body : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $err !== '') {
            $this->json(['success' => false, 'mensaje' => 'No se pudo conectar con Atlas App.', 'error' => $err]);
        }

        $decoded = json_decode((string)$raw, true);
        $mensaje = ($httpCode >= 200 && $httpCode < 300) ? 'Respuesta recibida.' : 'Atlas App devolvió un error.';
        if ($httpCode < 200 || $httpCode >= 300) {
            $detalle = is_array($decoded) ? ($decoded['detail'] ?? $decoded['message'] ?? $decoded['error'] ?? $decoded['mensaje'] ?? '') : '';
            if (is_array($detalle)) {
                $partes = [];
                foreach ($detalle as $item) {
                    if (is_array($item)) {
                        $partes[] = (string)($item['msg'] ?? json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    } else {
                        $partes[] = (string)$item;
                    }
                }
                $detalle = implode(' · ', array_filter($partes));
            }
            if (trim((string)$detalle) !== '') {
                $mensaje = (string)$detalle;
            } elseif (!is_array($decoded) && trim((string)$raw) !== '') {
                $mensaje = 'Atlas App HTTP ' . $httpCode . ': ' . trim((string)$raw);
            }
        }
        $this->json([
            'success' => $httpCode >= 200 && $httpCode < 300,
            'status' => $httpCode,
            'datos' => is_array($decoded) ? $decoded : null,
            'raw' => is_array($decoded) ? null : $raw,
            'mensaje' => $mensaje,
        ]);
    }

    private function atlasNotificacionesPathPermitido(string $path): bool
    {
        return $path === '/api/atlas/notifications/send';
    }

    private function atlasAdminApiKey(): string
    {
        $raw = getenv('ATLAS_ADMIN_API_KEYS');
        if ($raw === false || trim((string)$raw) === '') {
            $raw = getenv('ATLAS_ADMIN_API_KEY');
        }

        if ($raw === false || trim((string)$raw) === '') {
            $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ConfigApi.php';
            if (!function_exists('config_api_load_from_db') && is_file($configPath)) {
                require_once $configPath;
            }
            if (function_exists('config_api_load_from_db')) {
                $config = config_api_load_from_db();
                $raw = $config['ATLAS_ADMIN_API_KEYS'] ?? $config['ATLAS_ADMIN_API_KEY'] ?? '';
            }
        }

        $keys = array_filter(array_map('trim', explode(',', (string)$raw)));
        return (string)($keys[0] ?? '');
    }

    private function atlasAdminApiRequest(
        string $method,
        string $path,
        ?array $body = null,
        array $query = [],
        int $timeout = 35
    ): array
    {
        $adminApiKey = $this->atlasAdminApiKey();
        if ($adminApiKey === '') {
            return ['success' => false, 'mensaje' => 'ATLAS_ADMIN_API_KEYS no esta configurada en servidor.'];
        }

        $base = getenv('ATLAS_APP_API_BASE');
        if ($base === false || trim($base) === '') {
            $base = 'https://api-comercial-601258367060.us-central1.run.app';
        }
        $url = rtrim($base, '/') . $path;
        if ($query) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        $method = strtoupper($method);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: ' . $adminApiKey,
            ],
            CURLOPT_TIMEOUT => max(10, min(120, $timeout)),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_ENCODING => '',
        ]);
        if ($method !== 'GET') {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($body ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $error !== '') {
            return [
                'success' => false,
                'mensaje' => 'No se pudo conectar con API-COMERCIAL.',
                'error' => $error,
            ];
        }

        $decoded = json_decode((string)$raw, true);
        $success = $httpCode >= 200 && $httpCode < 300 && is_array($decoded) && ($decoded['success'] ?? true);
        $mensaje = is_array($decoded)
            ? (string)($decoded['message'] ?? $decoded['mensaje'] ?? '')
            : '';
        if (!$success && is_array($decoded)) {
            $detail = $decoded['detail'] ?? $decoded['error'] ?? '';
            if (is_array($detail)) {
                $detail = implode(' | ', array_filter(array_map(
                    static fn($item) => is_array($item)
                        ? (string)($item['msg'] ?? json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        : (string)$item,
                    $detail
                )));
            }
            if (trim((string)$detail) !== '') {
                $mensaje = (string)$detail;
            }
        }
        if ($mensaje === '') {
            $mensaje = $success ? 'Operacion completada.' : 'API-COMERCIAL devolvio un error.';
        }

        return [
            'success' => $success,
            'status' => $httpCode,
            'mensaje' => $mensaje,
            'datos' => is_array($decoded) ? ($decoded['data'] ?? $decoded['datos'] ?? null) : null,
        ];
    }

    private function streamAtlasExpedientesSnapshot(array $query): void
    {
        $adminApiKey = $this->atlasAdminApiKey();
        if ($adminApiKey === '') {
            $this->json([
                'success' => false,
                'status' => 500,
                'mensaje' => 'ATLAS_ADMIN_API_KEYS no esta configurada en servidor.',
            ]);
        }

        $base = getenv('ATLAS_APP_API_BASE');
        if ($base === false || trim((string)$base) === '') {
            $base = 'https://api-comercial-601258367060.us-central1.run.app';
        }
        $url = rtrim((string)$base, '/') . '/api/atlas/admin/expedientes?' . http_build_query($query);
        $responseHeaders = [];
        $requestHeaders = [
            'Accept: application/json',
            'X-API-Key: ' . $adminApiKey,
        ];
        $browserAcceptsGzip = stripos((string)($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''), 'gzip') !== false;
        if ($browserAcceptsGzip) {
            $requestHeaders[] = 'Accept-Encoding: gzip';
        }

        if (function_exists('session_write_close')) {
            session_write_close();
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTP_CONTENT_DECODING => false,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $line = trim($line);
                if ($line === '') {
                    return $length;
                }
                if (stripos($line, 'HTTP/') === 0) {
                    $responseHeaders = [];
                    return $length;
                }
                $separator = strpos($line, ':');
                if ($separator !== false) {
                    $name = strtolower(trim(substr($line, 0, $separator)));
                    $responseHeaders[$name] = trim(substr($line, $separator + 1));
                }
                return $length;
            },
        ]);
        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $error !== '') {
            $this->json([
                'success' => false,
                'status' => 502,
                'mensaje' => 'No se pudo conectar con API-COMERCIAL.',
                'error' => $error,
            ]);
        }
        if ($httpCode >= 200 && $httpCode < 300) {
            if (($responseHeaders['x-atlas-format'] ?? '') !== 'columnar') {
                $legacyRaw = (string)$raw;
                if (stripos((string)($responseHeaders['content-encoding'] ?? ''), 'gzip') !== false) {
                    $legacyRaw = (string)gzdecode($legacyRaw);
                }
                $legacy = json_decode($legacyRaw, true);
                $data = is_array($legacy['data'] ?? null) ? $legacy['data'] : [];
                $rows = is_array($data['filas'] ?? null) ? $data['filas'] : [];
                $expected = (int)($data['paginacion']['total_filtrados'] ?? count($rows));
                if (
                    !is_array($legacy)
                    || empty($legacy['success'])
                    || ($data['formato'] ?? '') !== 'columnar'
                    || count($rows) !== $expected
                ) {
                    $this->json([
                        'success' => false,
                        'status' => 502,
                        'mensaje' => 'API-COMERCIAL no entrego la carga completa de Expedientes.',
                    ]);
                }
                $this->json([
                    'success' => true,
                    'status' => $httpCode,
                    'mensaje' => (string)($legacy['message'] ?? 'Expedientes consultados correctamente.'),
                    'datos' => $data,
                ]);
            }
            header('X-Atlas-Format: columnar');
            header('X-Atlas-Total: ' . (string)($responseHeaders['x-atlas-total'] ?? '0'));
        }

        http_response_code($httpCode > 0 ? $httpCode : 502);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: private, no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');
        if (isset($responseHeaders['content-encoding'])) {
            header('Content-Encoding: ' . $responseHeaders['content-encoding']);
            header('Vary: Accept-Encoding');
        }
        foreach (['x-atlas-snapshot', 'x-atlas-snapshot-version', 'x-atlas-snapshot-time-ms'] as $name) {
            if (isset($responseHeaders[$name])) {
                header($name . ': ' . $responseHeaders[$name]);
            }
        }
        header('Content-Length: ' . strlen((string)$raw));
        echo $raw;
        exit;
    }

    private function atlasAppApiRequest(string $method, string $path, ?array $body = null, array $query = []): array
    {
        $authorization = $this->atlasAppAuthorizationHeader();
        $adminApiKey = $this->atlasAdminApiKey();
        if ($authorization === '' && $adminApiKey === '') {
            return [
                'success' => false,
                'status' => 500,
                'mensaje' => 'No hay credenciales configuradas para consultar API-COMERCIAL.',
            ];
        }

        $base = getenv('ATLAS_APP_API_BASE');
        if ($base === false || trim($base) === '') {
            $base = 'https://api-comercial-601258367060.us-central1.run.app';
        }
        $url = rtrim($base, '/') . $path;
        if ($query) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        if ($authorization !== '') {
            $headers[] = 'Authorization: ' . $authorization;
        }
        if ($adminApiKey !== '') {
            $headers[] = 'X-API-Key: ' . $adminApiKey;
        }

        $method = strtoupper($method);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 35,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        if ($method !== 'GET') {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($body ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $error !== '') {
            return [
                'success' => false,
                'status' => 502,
                'mensaje' => 'No se pudo conectar con API-COMERCIAL.',
                'error' => $error,
            ];
        }

        $decoded = json_decode((string)$raw, true);
        $success = $httpCode >= 200 && $httpCode < 300 && is_array($decoded) && ($decoded['success'] ?? true);
        $mensaje = is_array($decoded)
            ? (string)($decoded['message'] ?? $decoded['mensaje'] ?? '')
            : '';
        if (!$success && is_array($decoded)) {
            $detail = $decoded['detail'] ?? $decoded['error'] ?? '';
            if (is_array($detail)) {
                $detail = implode(' | ', array_filter(array_map(
                    static fn($item) => is_array($item)
                        ? (string)($item['msg'] ?? json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        : (string)$item,
                    $detail
                )));
            }
            if (trim((string)$detail) !== '') {
                $mensaje = (string)$detail;
            }
        }
        if ($mensaje === '') {
            $mensaje = $success ? 'Operacion completada.' : 'API-COMERCIAL devolvio un error.';
        }

        return [
            'success' => $success,
            'status' => $httpCode,
            'mensaje' => $mensaje,
            'datos' => is_array($decoded) ? ($decoded['data'] ?? $decoded['datos'] ?? null) : null,
        ];
    }

    private function atlasAppAuthorizationHeader(): string
    {
        $raw = getenv('ATLAS_APP_AUTHORIZATION');
        if ($raw !== false && trim((string)$raw) !== '') {
            return $this->sanitizarAuthorizationHeader((string)$raw);
        }
        $token = getenv('ATLAS_APP_BEARER_TOKEN');
        if ($token !== false && trim((string)$token) !== '') {
            return $this->sanitizarAuthorizationHeader('Bearer ' . trim((string)$token));
        }

        $header = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
        if ($header === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (is_array($headers)) {
                $header = (string)($headers['Authorization'] ?? $headers['authorization'] ?? '');
            }
        }
        return $this->sanitizarAuthorizationHeader($header);
    }

    private function sanitizarAuthorizationHeader(string $header): string
    {
        $header = trim(str_replace(["\r", "\n"], '', $header));
        return stripos($header, 'Bearer ') === 0 ? $header : '';
    }

    private function atlasAdminApiBinaryRequest(string $path): array
    {
        $adminApiKey = $this->atlasAdminApiKey();
        if ($adminApiKey === '') {
            return [
                'success' => false,
                'status' => 500,
                'mensaje' => 'ATLAS_ADMIN_API_KEYS no esta configurada en servidor.',
            ];
        }

        $base = getenv('ATLAS_APP_API_BASE');
        if ($base === false || trim($base) === '') {
            $base = 'https://api-comercial-601258367060.us-central1.run.app';
        }

        $ch = curl_init(rtrim($base, '/') . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: image/*,video/*,audio/*,application/octet-stream',
                'X-API-Key: ' . $adminApiKey,
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $content = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = trim((string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
        $error = curl_error($ch);
        curl_close($ch);

        if ($content === false || $error !== '') {
            return [
                'success' => false,
                'status' => 502,
                'mensaje' => 'No se pudo conectar con API-COMERCIAL.',
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $decoded = json_decode((string)$content, true);
            return [
                'success' => false,
                'status' => $httpCode > 0 ? $httpCode : 502,
                'mensaje' => is_array($decoded)
                    ? (string)($decoded['detail'] ?? $decoded['message'] ?? 'No se pudo cargar la evidencia.')
                    : 'No se pudo cargar la evidencia.',
            ];
        }

        return [
            'success' => true,
            'status' => $httpCode,
            'content_type' => $contentType !== '' ? $contentType : 'application/octet-stream',
            'contenido' => (string)$content,
        ];
    }

    private function googleMapsApiKey(): string
    {
        $key = defined('GOOGLE_MAPS_API_KEY') ? trim((string)GOOGLE_MAPS_API_KEY) : '';
        if ($key !== '') {
            return $key;
        }

        $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ConfigApi.php';
        if (!function_exists('config_api_load_from_db') && is_file($configPath)) {
            require_once $configPath;
        }
        if (function_exists('config_api_load_from_db')) {
            $config = config_api_load_from_db();
            $key = trim((string)($config['GOOGLE_MAPS_API_KEY'] ?? ''));
        }

        return $key;
    }

    private function payload(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode((string)$raw, true);
        if (is_array($json)) {
            return $json;
        }
        return $_POST ?: [];
    }

    private function validarAccesoExpedientes(bool $json = false): void
    {
        $modulos = array_map('intval', (array)($_SESSION['modulos'] ?? []));
        if (in_array(self::MODULO_ATLAS_EXPEDIENTES, $modulos, true)) {
            return;
        }
        if ($json) {
            http_response_code(403);
            $this->json([
                'success' => false,
                'status' => 403,
                'mensaje' => 'No tienes permiso para operar expedientes.',
            ]);
        }
        header('Location: /Inicio', true, 302);
        exit;
    }

    private function validarAccesoVentas(bool $json = false): void
    {
        $modulos = array_map('intval', (array)($_SESSION['modulos'] ?? []));
        if (in_array(self::MODULO_ATLAS_VENTAS, $modulos, true)) {
            return;
        }
        if ($json) {
            http_response_code(403);
            $this->json([
                'success' => false,
                'status' => 403,
                'mensaje' => 'No tienes permiso para consultar ventas.',
            ]);
        }
        header('Location: /Inicio', true, 302);
        exit;
    }

    private function atlasExpedientesApiResponse(array $response): array
    {
        if (!empty($response['success'])) {
            return $response;
        }
        $message = strtolower((string)($response['mensaje'] ?? ''));
        if (
            str_contains($message, 'atlas_admin_api_key')
            || str_contains($message, 'api-comercial')
            || (int)($response['status'] ?? 0) >= 500
        ) {
            $response['mensaje'] = 'El servicio de Expedientes no esta disponible por el momento. Contacta a soporte para revisarlo.';
        }
        return $response;
    }

    private function leerExpedientesExcel(string $ruta): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
        try {
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $headerRow = 0;
            $headers = [];
            for ($row = 1; $row <= min(15, $highestRow); $row++) {
                $candidate = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $candidate[$col] = $this->normalizarHeaderPresupuesto(
                        $sheet->getCell($this->excelCell($col, $row))->getValue()
                    );
                }
                $hasCredit = (bool)array_intersect(
                    ['idcredito', 'creditoid', 'idoferta', 'oferta', 'credito'],
                    $candidate
                );
                $hasStatus = (bool)array_intersect(
                    ['estatus', 'estado', 'estatusexpediente', 'estadoexpediente', 'resultado'],
                    $candidate
                );
                if ($hasCredit && $hasStatus) {
                    $headerRow = $row;
                    $headers = $candidate;
                    break;
                }
            }

            if ($headerRow === 0) {
                throw new \RuntimeException(
                    'El layout debe incluir las columnas ID credito y Estatus expediente.'
                );
            }

            $map = [];
            foreach ($headers as $col => $header) {
                $field = match ($header) {
                    'idcredito', 'creditoid', 'idoferta', 'oferta', 'credito' => 'credito_id',
                    'estatus', 'estado', 'estatusexpediente', 'estadoexpediente', 'resultado' => 'estatus',
                    'motivo', 'incidencia', 'motivoincidencia', 'detalleincidencia', 'observacion', 'observaciones' => 'motivo',
                    'comentario', 'comentarios', 'detalle', 'notas', 'nota' => 'comentario',
                    default => null,
                };
                if ($field !== null && !in_array($field, $map, true)) {
                    $map[$col] = $field;
                }
            }

            if (!in_array('credito_id', $map, true) || !in_array('estatus', $map, true)) {
                throw new \RuntimeException(
                    'El layout debe incluir las columnas ID credito y Estatus expediente.'
                );
            }

            $rows = [];
            $errors = [];
            $seenCredits = [];
            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                $item = ['credito_id' => '', 'estatus' => '', 'motivo' => '', 'comentario' => ''];
                foreach ($map as $col => $field) {
                    $cell = $sheet->getCell($this->excelCell($col, $row));
                    $value = $cell->isFormula() ? $cell->getCalculatedValue() : $cell->getValue();
                    $item[$field] = trim((string)($value ?? ''));
                }
                if ($item['credito_id'] === '' && $item['estatus'] === '' && $item['motivo'] === '' && $item['comentario'] === '') {
                    continue;
                }

                $creditText = preg_replace('/[,\s]+/', '', $item['credito_id']) ?: '';
                if (!ctype_digit($creditText) || (int)$creditText <= 0) {
                    $errors[] = "Fila {$row}: ID credito invalido.";
                    continue;
                }
                $creditId = (int)$creditText;
                if (isset($seenCredits[$creditId])) {
                    $errors[] = "Fila {$row}: el credito {$creditId} esta repetido.";
                    continue;
                }

                $action = $this->normalizarEstatusExpedienteLayout($item['estatus']);
                if ($action === null) {
                    $errors[] = "Fila {$row}: estatus no reconocido.";
                    continue;
                }
                if (in_array($action, ['no_entregado', 'incidencia'], true) && mb_strlen($item['motivo']) < 5) {
                    $errors[] = "Fila {$row}: captura el motivo o incidencia.";
                    continue;
                }

                $seenCredits[$creditId] = true;
                $rows[] = [
                    'credito_id' => $creditId,
                    'accion' => $action,
                    'motivo' => $item['motivo'],
                    'comentario' => $item['comentario'],
                    '_excel_row' => $row,
                ];
                if (count($rows) > 2000) {
                    throw new \RuntimeException('El layout no puede superar 2,000 registros.');
                }
            }

            if ($errors) {
                $preview = array_slice($errors, 0, 8);
                if (count($errors) > count($preview)) {
                    $preview[] = 'Hay ' . (count($errors) - count($preview)) . ' error(es) adicional(es).';
                }
                throw new \RuntimeException(implode(' ', $preview));
            }
            if (!$rows) {
                throw new \RuntimeException('El layout no contiene expedientes para actualizar.');
            }
            return $rows;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function normalizarEstatusExpedienteLayout($value): ?string
    {
        return match ($this->normalizarHeaderPresupuesto($value)) {
            'recolectado', 'expedienterecolectado', 'entregado', 'expedienteentregado' => 'entregado',
            'norecolectado', 'expedientenorecolectado', 'noentregado', 'expedientenoentregado' => 'no_entregado',
            'incidencia', 'conincidencia', 'expedienteconincidencia' => 'incidencia',
            default => null,
        };
    }

    private function leerPresupuestoExcel(string $ruta): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headerRow = 0;
        $headers = [];
        for ($row = 1; $row <= min(15, $highestRow); $row++) {
            $tmp = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $value = $sheet->getCell($this->excelCell($col, $row))->getValue();
                $tmp[$col] = $this->normalizarHeaderPresupuesto($value);
            }
            $headersCredito = ['creditos', 'credito', 'metacreditos', 'presupuestodecreditos', 'presupuestocreditos'];
            $headersCash = ['cash', 'metacash', 'presupuesto', 'presupuestodecash', 'presupuestocash'];
            if (in_array('pksucursal', $tmp, true) && (array_intersect($headersCredito, $tmp) || array_intersect($headersCash, $tmp))) {
                $headerRow = $row;
                $headers = $tmp;
                break;
            }
        }

        if ($headerRow === 0) {
            throw new \RuntimeException('No se encontro encabezado con Pk_Sucursal, Creditos y Cash.');
        }

        $map = [];
        foreach ($headers as $col => $header) {
            $campo = match ($header) {
                'pksucursal', 'fksucursal', 'fksucursalid' => 'fk_sucursal',
                'distribuidor' => 'distribuidor',
                'sucursal' => 'sucursal',
                'divisional' => 'divisional',
                'regional' => 'regional',
                'supervisor' => 'supervisor',
                'asesor', 'asignacionjulio', 'gestor', 'responsable' => 'asesor',
                'estado' => 'estado',
                'promediofebmay', 'promediofebreroamay' => 'promedio_creditos',
                'clasificacionnuevoesquema', 'clasificacion', 'clasificacionenero', 'clasificacionfebrero', 'clasificacionmarzo', 'clasificacionabril', 'clasificacionmayo', 'clasificacionjunio', 'clasificacionjulio', 'clasificacionagosto', 'clasificacionseptiembre', 'clasificacionoctubre', 'clasificacionnoviembre', 'clasificaciondiciembre' => 'clasificacion',
                'creditos', 'credito', 'meta', 'metacreditos', 'metacredito', 'presupuestodecreditos', 'presupuestocreditos' => 'meta_creditos',
                'cash', 'metacash', 'presupuesto', 'presupuestodecash', 'presupuestocash' => 'meta_cash',
                'comisionaapartirde', 'comisionapartirde', 'comisiondesde', 'comision' => 'comisiona_a_partir_de',
                default => null,
            };
            if ($campo !== null) {
                $map[$col] = $campo;
            }
        }

        if (!in_array('fk_sucursal', $map, true)) {
            throw new \RuntimeException('El Excel no trae Pk_Sucursal.');
        }

        $filas = [];
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $item = [];
            foreach ($map as $col => $campo) {
                $cell = $sheet->getCell($this->excelCell($col, $row));
                $value = $cell->isFormula() ? $cell->getCalculatedValue() : $cell->getValue();
                $item[$campo] = is_string($value) ? trim($value) : $value;
            }
            $fk = trim((string)($item['fk_sucursal'] ?? ''));
            $tieneDatos = (bool)array_filter(
                $item,
                static fn($value): bool => $value !== null && trim((string)$value) !== ''
            );
            if ($fk === '' && !$tieneDatos) {
                continue;
            }
            $item['_excel_row'] = $row;
            $filas[] = $item;
        }

        $spreadsheet->disconnectWorksheets();
        if (!$filas) {
            throw new \RuntimeException('El Excel no contiene filas de presupuesto.');
        }
        return $filas;
    }

    private function leerDistribuidoresExcel(string $ruta): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headerRow = 0;
        $headers = [];
        for ($row = 1; $row <= min(15, $highestRow); $row++) {
            $tmp = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $tmp[$col] = $this->normalizarHeaderPresupuesto($sheet->getCell($this->excelCell($col, $row))->getValue());
            }
            if (in_array('nombrecomercial', $tmp, true) || in_array('rfc', $tmp, true)) {
                $headerRow = $row;
                $headers = $tmp;
                break;
            }
        }

        if ($headerRow === 0) {
            throw new \RuntimeException('No se encontro encabezado de distribuidores.');
        }

        $map = [];
        foreach ($headers as $col => $header) {
            $campo = match ($header) {
                'id' => 'id',
                'nombrecomercial', 'distribuidor', 'nombre' => 'nombre_comercial',
                'razonsocial' => 'razon_social',
                'rfc' => 'rfc',
                'tipopersona' => 'tipo_persona',
                'tipodistribuidor' => 'tipo_distribuidor',
                'estatus', 'estado' => 'estatus',
                'contactoprincipal', 'nombrecontacto' => 'nombre_contacto',
                'telefonoprincipal', 'telefonocontacto' => 'telefono_contacto',
                'telefonoalterno', 'telefonosecundario' => 'telefono_secundario',
                'correoprincipal', 'emailcontacto', 'correo' => 'email_contacto',
                'regimenfiscal' => 'regimen_fiscal',
                'bancodeposito', 'banco' => 'banco_deposito',
                'titulardeposito', 'titularcuenta', 'titular' => 'titular_deposito',
                'cuentadeposito', 'cuenta' => 'cuenta_deposito',
                'clabedeposito', 'clabe' => 'clabe_deposito',
                'tipomotos', 'tipodemotos' => 'tipo_motos',
                'canalventa', 'canaldeventa' => 'canal_venta',
                'presenciafisica' => 'presencia_fisica',
                'horarioatencion' => 'horario_atencion',
                'diasoperacion' => 'dias_operacion',
                'requierecita' => 'requiere_cita',
                'tiempoestadia', 'tiempopromedioentrega' => 'tiempo_promedio_entrega',
                'observaciones' => 'observaciones',
                default => null,
            };
            if ($campo !== null) {
                $map[$col] = $campo;
            }
        }

        if (!in_array('nombre_comercial', $map, true) && !in_array('rfc', $map, true)) {
            throw new \RuntimeException('El Excel debe traer Nombre comercial o RFC.');
        }

        $filas = [];
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $item = [];
            foreach ($map as $col => $campo) {
                $cell = $sheet->getCell($this->excelCell($col, $row));
                $value = $cell->isFormula() ? $cell->getCalculatedValue() : $cell->getFormattedValue();
                $item[$campo] = is_string($value) ? trim($value) : $value;
            }
            $nombre = trim((string)($item['nombre_comercial'] ?? ''));
            $rfc = trim((string)($item['rfc'] ?? ''));
            if ($nombre === '' && $rfc === '') {
                continue;
            }
            $item['_excel_row'] = $row;
            $filas[] = $item;
        }

        $spreadsheet->disconnectWorksheets();
        if (!$filas) {
            throw new \RuntimeException('El Excel no contiene filas de distribuidores.');
        }
        return $filas;
    }

    private function leerAccesosAtlasExcel(string $ruta): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headerRow = 0;
        $headers = [];
        for ($row = 1; $row <= min(15, $highestRow); $row++) {
            $tmp = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $tmp[$col] = $this->normalizarHeaderPresupuesto($sheet->getCell($this->excelCell($col, $row))->getValue());
            }
            if (in_array('idacceso', $tmp, true) || in_array('numeroempleado', $tmp, true) || in_array('personaid', $tmp, true)) {
                $headerRow = $row;
                $headers = $tmp;
                break;
            }
        }

        if ($headerRow === 0) {
            throw new \RuntimeException('No se encontro encabezado de plantilla de personal.');
        }

        $map = [];
        foreach ($headers as $col => $header) {
            $campo = match ($header) {
                'idacceso', 'id' => 'id',
                'personaid', 'idpersona' => 'persona_id',
                'numeroempleado', 'numempleado', 'empleado' => 'numero_empleado',
                'nombre' => 'nombre',
                'correo', 'email' => 'correo',
                'telefono', 'telefonocelular' => 'telefono',
                'puesto' => 'puesto',
                'departamento' => 'departamento',
                'area' => 'area',
                'direccion' => 'direccion',
                'pais' => 'pais',
                'jefe' => 'jefe_nombre',
                'puedever', 'ver' => 'puede_ver',
                'puedeeditar', 'editar' => 'puede_editar',
                'puedeadministrar', 'administrar' => 'puede_administrar',
                'accesomovil', 'movil' => 'acceso_movil',
                'excluidooperativo', 'excluido' => 'excluido_operativo',
                'motivoexclusion', 'motivo' => 'excluido_motivo',
                'activo', 'estatus' => 'activo',
                default => null,
            };
            if ($campo !== null) {
                $map[$col] = $campo;
            }
        }

        if (!in_array('id', $map, true) && !in_array('persona_id', $map, true) && !in_array('numero_empleado', $map, true)) {
            throw new \RuntimeException('La plantilla debe traer ID acceso, Persona ID o Numero empleado.');
        }

        $filas = [];
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $item = [];
            foreach ($map as $col => $campo) {
                $cell = $sheet->getCell($this->excelCell($col, $row));
                $value = $cell->isFormula() ? $cell->getCalculatedValue() : $cell->getFormattedValue();
                $item[$campo] = is_string($value) ? trim($value) : $value;
            }
            $id = trim((string)($item['id'] ?? ''));
            $personaId = trim((string)($item['persona_id'] ?? ''));
            $numero = trim((string)($item['numero_empleado'] ?? ''));
            if ($id === '' && $personaId === '' && $numero === '') {
                continue;
            }
            $item['_excel_row'] = $row;
            $filas[] = $item;
        }

        $spreadsheet->disconnectWorksheets();
        if (!$filas) {
            throw new \RuntimeException('La plantilla no contiene filas para actualizar.');
        }
        return $filas;
    }

    private function normalizarHeaderPresupuesto($value): string
    {
        $text = mb_strtolower(trim((string)$value), 'UTF-8');
        $text = strtr($text, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
            'Ã¡' => 'a', 'Ã©' => 'e', 'Ã­' => 'i', 'Ã³' => 'o', 'Ãº' => 'u', 'Ã¼' => 'u', 'Ã±' => 'n',
        ]);
        return preg_replace('/[^a-z0-9]+/', '', $text) ?: '';
    }

    private function excelCell(int $col, int $row): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function jsonComprimido(array $data): void
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            $json = '{"success":false,"status":500,"mensaje":"No se pudo preparar la respuesta."}';
            http_response_code(500);
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $acceptEncoding = strtolower((string)($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''));
        if (
            str_contains($acceptEncoding, 'gzip')
            && function_exists('gzencode')
            && !filter_var(ini_get('zlib.output_compression'), FILTER_VALIDATE_BOOLEAN)
        ) {
            $gzip = gzencode($json, 6);
            if (is_string($gzip)) {
                header('Content-Encoding: gzip');
                header('Vary: Accept-Encoding');
                header('Content-Length: ' . strlen($gzip));
                echo $gzip;
                exit;
            }
        }
        echo $json;
        exit;
    }
}
