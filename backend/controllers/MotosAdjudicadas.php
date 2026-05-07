<?php

namespace Controllers;

use Core\Controller;
use Models\Adjudicacion as AdjudicacionDAO;
use Models\MotosAdjudicadas as MotosAdjudicadasDAO;

class MotosAdjudicadas extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new MotosAdjudicadasDAO();
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    /**
     * GET /MotosAdjudicadas/pipeline
     */
    public function pipeline()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Operaciones - Pipeline ' . $emp);
        return self::render('operaciones_pipeline');
    }

    /**
     * GET /MotosAdjudicadas/listaDictamenes
     */
    public function listaDictamenes()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Motos Adjudicadas - Lista de Dictámenes ' . $emp);
        $gmk = defined('GOOGLE_MAPS_API_KEY') ? (string) GOOGLE_MAPS_API_KEY : '';
        self::set(
            'google_maps_api_key_js',
            json_encode($gmk, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        );
        return self::render('motos_adjudicadas_lista_dictamenes');
    }

    /**
     * GET /MotosAdjudicadas/obtenerListaDictamenes
     */
    public function obtenerListaDictamenes()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
            if ($limit !== null && $limit <= 0) {
                $limit = null;
            }
            $offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
            $modoRapido = isset($_GET['rapido']) && (string) $_GET['rapido'] === '1';

            $result = $this->model->obtenerListaDictumsMotos($limit, $offset, $modoRapido);
            echo json_encode(
                [
                    'success'  => true,
                    'rows'     => $result['rows'],
                    'has_more' => $result['has_more'],
                ],
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/obtenerListaDictamenesCompleta
     * Segunda fase opcional para refrescar lista completa en background (sin repetir el mismo endpoint en Network).
     */
    public function obtenerListaDictamenesCompleta()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $result = $this->model->obtenerListaDictumsMotos(null, 0, false);
            echo json_encode(
                [
                    'success'  => true,
                    'rows'     => $result['rows'],
                    'has_more' => $result['has_more'],
                ],
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/resolverNombresClienteDictamenes?ids=1,2,3
     * Resuelve y cachea nombres por lote para la lista de dictámenes.
     */
    public function resolverNombresClienteDictamenes()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = trim((string) ($_GET['ids'] ?? ''));
            if ($raw === '') {
                echo json_encode(['success' => true, 'nombres' => []], JSON_UNESCAPED_UNICODE);
                return;
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw)), static fn($v) => $v > 0)));
            if ($ids === []) {
                echo json_encode(['success' => true, 'nombres' => []], JSON_UNESCAPED_UNICODE);
                return;
            }
            $map = $this->model->resolverNombresClienteDictamenesPorCreditos($ids);
            echo json_encode(['success' => true, 'nombres' => $map], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — BUSCAR CRÉDITO EN ADJUDICACIÓN
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/buscarCredito
     * Body JSON: { "valor": 1637 }
     * Verifica asignación activa en asigna_creditos_adjudicacion y enriquece con S2.
     */
    public function buscarCredito()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $valor = (int) ($body['valor'] ?? 0);

        if ($valor <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $result = $this->model->buscarCreditoEnAdjudicacion($valor);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — SUBIR EVIDENCIA
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/subirEvidencia  (multipart/form-data)
     * Fields: id_operacion, slot
     * File:   archivo
     */
    public function subirEvidencia()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idOperacion = (int) ($_POST['id_operacion'] ?? 0);
        $slot        = trim($_POST['slot'] ?? '');

        if ($idOperacion <= 0 || $slot === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $code = $_FILES['archivo']['error'] ?? -1;
            echo json_encode(['success' => false, 'message' => "Error de subida (código {$code})."]);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->subirEvidencia($idOperacion, $slot, $_FILES['archivo'], $idUsuario, $nombreUsuario);
            if ($result['success']) {
                $bit = $this->model->obtenerBitacora($idOperacion);
                $result['bitacora_entry'] = $bit[0] ?? null;
            }
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — PIPELINE
    // =========================================================================

    /**
     * GET /MotosAdjudicadas/obtenerOperaciones
     * Devuelve todas las tarjetas del kanban como JSON.
     */
    public function obtenerOperaciones()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $ops = $this->model->obtenerPipeline();
            echo json_encode(['success' => true, 'operaciones' => $ops]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/obtenerDetalle/{id}?incluir_todas=1
     */
    public function obtenerDetalle($id = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int) $id;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }
        try {
            $detalle = $this->model->obtenerDetalle($id);
            if (!$detalle) {
                echo json_encode(['success' => false, 'message' => 'Operación no encontrada.']);
                return;
            }
            // Pipeline / Kanban: solo filas ya enviadas al pipeline (recibido).
            // incluir_todas=1 (Atención a clientes, refresco tras subir Repuve): incluye pendiente_envio.
            $incluirTodas = isset($_GET['incluir_todas'])
                && ($_GET['incluir_todas'] === '1' || $_GET['incluir_todas'] === 'true');
            if (!$incluirTodas) {
                $detalle['evidencias'] = array_values(
                    array_filter($detalle['evidencias'] ?? [], fn($e) => ($e['estatus'] ?? 'recibido') === 'recibido')
                );
            }
            echo json_encode(['success' => true, 'detalle' => $detalle]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/recepcionResumenFinanciero?id_credito=N
     * Saldo capital y adeudo total desde API S2 (estado de cuenta).
     */
    public function recepcionResumenFinanciero()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCred = (int) ($_GET['id_credito'] ?? 0);
        if ($idCred <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        try {
            $res = $this->model->obtenerResumenFinancieroEstadoCuentaS2($idCred);
            echo json_encode($res);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/obtenerResumenS2ModalDictamen?id_credito=N
     * Datos S2 (estado de cuenta) para la sección «S2» del modal Lista Dictámenes.
     */
    public function obtenerResumenS2ModalDictamen()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCred = (int) ($_GET['id_credito'] ?? 0);
        if ($idCred <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        try {
            $res = $this->model->obtenerResumenS2ModalDictamen($idCred);
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/guardarSeguimientoMaDictamen
     * Body JSON: id_credito, comentarios (obligatorio), aplica (0|1) para recolección,
     * gestor_manual (bool): si true, id_persona_responsable es el elegido en catálogo; si false, se usa id_persona_responsable_default (Gestor Legacy).
     * La asignación llama a Adjudicacion::asignarCredito, que crea fila en personal_adjudicacion si aún no existe.
     * Persiste seguimiento en adj_s2_cache_dictamen (ma_seg_comentarios, ma_seg_aplica, ma_seg_actualizado_at).
     * Asignación a Mis adjudicaciones solo si aplica === 1.
     */
    public function guardarSeguimientoMaDictamen()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body                   = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito              = (int) ($body['id_credito'] ?? $body['id_dictum'] ?? 0);
        $comentarios            = trim((string) ($body['comentarios'] ?? ''));
        $idPersonaManual        = (int) ($body['id_persona_responsable'] ?? 0);
        $idPersonaDefault       = (int) ($body['id_persona_responsable_default'] ?? 0);
        $gestorManualRaw        = $body['gestor_manual'] ?? false;
        $gestorManual           = ($gestorManualRaw === true || $gestorManualRaw === 1 || $gestorManualRaw === '1');
        $aplicaRaw              = $body['aplica'] ?? null;
        $aplica                 = null;
        if ($aplicaRaw === 0 || $aplicaRaw === '0' || $aplicaRaw === false) {
            $aplica = 0;
        } elseif ($aplicaRaw === 1 || $aplicaRaw === '1' || $aplicaRaw === true) {
            $aplica = 1;
        }

        $idPersonaResponsable = 0;
        $adj                  = new AdjudicacionDAO();
        if ($aplica === 1) {
            if ($gestorManual) {
                if ($idPersonaManual <= 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Seleccione un gestor en la lista (Elegir gestor) o guarde sin selección para usar el Gestor a cargo (Legacy).',
                    ], JSON_UNESCAPED_UNICODE);

                    return;
                }
                $idPersonaResponsable = $idPersonaManual;
            } else {
                if ($idPersonaDefault <= 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No hay Gestor a cargo (Legacy) vinculado a persona en Sparta. Revise dictum → users → número de empleado.',
                    ], JSON_UNESCAPED_UNICODE);

                    return;
                }
                $idPersonaResponsable = $idPersonaDefault;
            }
        }

        try {
            $result = $this->model->guardarSeguimientoMaDictamen($idCredito, $comentarios, $aplica);
            if (!empty($result['success']) && $idCredito > 0 && $aplica === 1) {
                $idUsuarioAlta = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
                if ($idUsuarioAlta > 0) {
                    $asig = $adj->asignarCredito($idPersonaResponsable, $idCredito, $idUsuarioAlta);
                    if (empty($asig['success']) && !empty($asig['message'])) {
                        $m = (string) $asig['message'];
                        if (stripos($m, 'ya está asignado a este responsable') !== false
                            || stripos($m, 'ya esta asignado a este responsable') !== false) {
                            $asig['success'] = true;
                            $asig['message'] = 'El crédito ya estaba asignado al responsable seleccionado.';
                        }
                    }
                    $result['asignacion'] = $asig;
                    if (!empty($asig['success'])) {
                        $result['message'] = 'Seguimiento guardado. '
                            . ($asig['message'] ?? 'Crédito asignado correctamente; aparecerá en Mis adjudicaciones del responsable.');
                    } else {
                        $result['message'] = 'Seguimiento guardado. '
                            . ($asig['message'] ?? 'No se pudo completar la asignación.');
                    }
                } else {
                    $result['asignacion'] = [
                        'success' => false,
                        'message' => 'No hay persona en sesión para registrar la asignación.',
                    ];
                    $result['message'] = 'Seguimiento guardado. '
                        . ($result['asignacion']['message'] ?? '');
                }
            } elseif (!empty($result['success']) && $idCredito > 0 && $aplica === 0) {
                $result['message'] = 'Seguimiento guardado. No aplica para recolección: el crédito no se asignó a Mis adjudicaciones.';
                $result['asignacion'] = [
                    'success'                  => true,
                    'omitida_por_recoleccion'  => true,
                    'message'                  => 'Sin asignación al indicar que no aplica para recolección.',
                ];
            } elseif (!empty($result['success'])) {
                $result['message'] = 'Seguimiento guardado.';
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/registrarLlegadaAlmacenRecepcion
     * Body JSON: { "id_operacion": 123 }
     */
    public function registrarLlegadaAlmacenRecepcion()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOp    = (int) ($body['id_operacion'] ?? 0);
        $idUsr   = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nomUsr  = trim((string) ($_SESSION['usuario_nombre'] ?? 'SISTEMA'));
        if ($idOp <= 0) {
            echo json_encode(['success' => false, 'message' => 'Operación inválida.']);
            return;
        }
        try {
            echo json_encode($this->model->registrarLlegadaAlmacenRecepcion($idOp, $idUsr, $nomUsr));
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/guardarRecepcionEstadoDocumento
     * Body JSON: { "id_operacion": 1, "documento": "dacion"|"tarjeta", "estado": "pending"|"missing" }
     */
    public function guardarRecepcionEstadoDocumento()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOp   = (int) ($body['id_operacion'] ?? 0);
        $doc    = trim((string) ($body['documento'] ?? ''));
        $estado = trim((string) ($body['estado'] ?? ''));
        $idUsr  = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nomUsr = trim((string) ($_SESSION['usuario_nombre'] ?? 'SISTEMA'));
        if ($idOp <= 0) {
            echo json_encode(['success' => false, 'message' => 'Operación inválida.']);
            return;
        }
        try {
            echo json_encode($this->model->guardarRecepcionEstadoDocumento($idOp, $doc, $estado, $idUsr, $nomUsr));
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/confirmarRecepcionAlmacen
     * Body JSON: { "id_operacion": 1, "ubicacion": "...", "observaciones": "..." }
     */
    public function confirmarRecepcionAlmacen()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOp   = (int) ($body['id_operacion'] ?? 0);
        $ubic   = trim((string) ($body['ubicacion'] ?? ''));
        $obs    = trim((string) ($body['observaciones'] ?? ''));
        $idUsr  = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nomUsr = trim((string) ($_SESSION['usuario_nombre'] ?? 'SISTEMA'));
        if ($idOp <= 0) {
            echo json_encode(['success' => false, 'message' => 'Operación inválida.']);
            return;
        }
        try {
            echo json_encode($this->model->confirmarRecepcionAlmacen($idOp, $ubic, $obs, $idUsr, $nomUsr));
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — CREAR OPERACIÓN
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/crearOperacion
     * Body JSON: {
     *   id_credito, nombre_cliente, responsable_entrega, telefono_contacto,
     *   direccion_recoleccion, marca, modelo, serie, num_motor, placas,
     *   dias_mora, saldo_capital, adeudo_total, area_actual
     * }
     */
    public function crearOperacion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $idCredito     = (int) ($body['id_credito']     ?? 0);
        $nombreCliente = trim($body['nombre_cliente']   ?? '');

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        if ($nombreCliente === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre del cliente es obligatorio.']);
            return;
        }

        $idUsuario = (int) ($_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        try {
            $result = $this->model->crearOperacion($body, $idUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — CAMBIAR ESTATUS
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/cambiarEstatus
     * Body JSON: { "id": 5, "estatus": "Procesando IA" }
     */
    public function cambiarEstatus()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $id      = (int) ($body['id']     ?? 0);
        $estatus = trim($body['estatus']  ?? '');

        if ($id <= 0 || $estatus === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->cambiarEstatus($id, $estatus, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — OBSERVACIONES
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/agregarObservacion
     * Body JSON: { "id_operacion": 5, "etapa": "Recibido", "area": "Adjudicación", "texto": "..." }
     */
    public function agregarObservacion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $etapa       = trim($body['etapa']          ?? '');
        $area        = trim($body['area']           ?? '');
        $texto       = trim($body['texto']          ?? '');

        if ($idOperacion <= 0 || $texto === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result        = $this->model->agregarObservacion($idOperacion, $etapa, $area, $idUsuario, $texto, $nombreUsuario);
            // Append last bitácora entry so the frontend can display it immediately
            if ($result['success']) {
                $accionBit = 'AGREGÓ ACCIÓN DE TRAMO: ' . mb_strtoupper(mb_substr($texto, 0, 60)) . (mb_strlen($texto) > 60 ? '…' : '');
                $bit = $this->model->obtenerBitacora($idOperacion);
                $result['bitacora_entry'] = $bit[0] ?? null;
            }
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — ELIMINAR OPERACIÓN
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/eliminarOperacion
     * Body JSON: { "id": 5 }
     */
    // =========================================================================
    // MIS ADJUDICACIONES
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/enviarEvidencias
     * Body JSON: { "id_operacion": 5 }
     * Cambia todas las evidencias 'pendiente_envio' de la operación a 'recibido'.
     */
    public function enviarEvidencias()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->enviarEvidencias($idOperacion, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/repuveConsulta
     * Consulta REPUVE por crédito (placa o VIN).
     */
    public function repuveConsulta()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Consulta REPUVE — Motos Adjudicadas ' . $emp);
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        self::set('limite_repuve', $this->model->obtenerInfoLimiteRepuve($idUsuario));
        return self::render('repuve_consulta');
    }

    /**
     * POST /MotosAdjudicadas/misAdjudicaciones
     */
    public function misAdjudicaciones()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Mis Adjudicaciones ' . $emp);
        return self::render('mis_adjudicaciones');
    }

    /**
     * POST /MotosAdjudicadas/obtenerEvidenciasCredito
     * Body JSON: { "id_credito": 12345, "nombre_cliente": "Juan Pérez" }
     * Obtiene (o crea si no existe) la operación del pipeline asociada al crédito
     * y devuelve un detalle compacto para el modal de Mis Adjudicaciones.
     */
    public function obtenerEvidenciasCredito()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body          = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito     = (int)  ($body['id_credito']     ?? 0);
        $nombreCliente = trim(  ($body['nombre_cliente'] ?? ''));

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        try {
            $result = $this->model->obtenerOCrearOperacion($idCredito, $nombreCliente, $idUsuario);
            if (empty($result['success']) || empty($result['detalle']) || !is_array($result['detalle'])) {
                echo json_encode($result);
                return;
            }

            $detalle = $result['detalle'];

            if (!empty($detalle['evidencias']) && is_array($detalle['evidencias'])) {
                foreach ($detalle['evidencias'] as &$ev) {
                    if (!is_array($ev) || empty($ev['url'])) {
                        continue;
                    }
                    $u = str_replace('\\', '/', trim((string) $ev['url']));
                    $u = preg_replace('#^https?://uploads(?=/|$)#i', '/uploads', $u);
                    $u = preg_replace('#^/{2,}uploads(?=/|$)#i', '/uploads', $u);
                    $u = preg_replace('#^/uploads/uploads/#i', '/uploads/', $u);
                    if (function_exists('sparta_url_publica_desde_repositorio')) {
                        $u = sparta_url_publica_desde_repositorio($u);
                    }
                    $ev['url'] = $u;
                }
                unset($ev);
            }

            $historial = [];
            if (!empty($detalle['historial']) && is_array($detalle['historial'])) {
                foreach ($detalle['historial'] as $h) {
                    if (!is_array($h)) {
                        continue;
                    }
                    $historial[] = [
                        'id'            => isset($h['id']) ? (int) $h['id'] : 0,
                        'estatus_actual'=> (string) ($h['estatus_nuevo'] ?? $h['estatus_actual'] ?? ''),
                        'id_usuario'    => isset($h['id_usuario']) ? (int) $h['id_usuario'] : 0,
                        'fecha'         => (string) ($h['fecha'] ?? ''),
                    ];
                }
            }

            // Extraer campos de datos_moto guardados en adj_operacion
            $datosMoto = null;
            $camposMoto = [
                'moto_marca','moto_modelo','moto_anio','moto_color',
                'moto_no_serie','moto_no_motor','moto_placas',
                'log_direccion','log_ciudad',
                'log_estado','log_lugar_resguardo','log_lugar_otro','log_telefono',
            ];
            $tieneDatosMoto = false;
            foreach ($camposMoto as $c) {
                if (!empty($detalle[$c])) {
                    $tieneDatosMoto = true;
                    break;
                }
            }
            if ($tieneDatosMoto) {
                $datosMoto = [];
                foreach ($camposMoto as $c) {
                    $datosMoto[$c] = $detalle[$c] ?? null;
                }
            }

            $detalleCompacto = [
                'id'                     => isset($detalle['id']) ? (int) $detalle['id'] : 0,
                'folio'                  => (string) ($detalle['folio'] ?? ''),
                'id_credito'             => isset($detalle['id_credito']) ? (int) $detalle['id_credito'] : $idCredito,
                'nombre_cliente'         => (string) ($detalle['nombre_cliente'] ?? $nombreCliente),
                'id_usuario_alta'        => isset($detalle['id_usuario_alta']) ? (int) $detalle['id_usuario_alta'] : null,
                'fecha_alta'             => $detalle['fecha_alta'] ?? null,
                'fecha_actualizacion'    => $detalle['fecha_actualizacion'] ?? null,
                'fecha_llegada_almacen'  => $detalle['fecha_llegada_almacen'] ?? null,
                'recepcion_ubicacion'    => $detalle['recepcion_ubicacion'] ?? null,
                'recepcion_observaciones'=> $detalle['recepcion_observaciones'] ?? null,
                'recepcion_confirmada_at'=> $detalle['recepcion_confirmada_at'] ?? null,
                'fecha_alta_fmt'         => (string) ($detalle['fecha_alta_fmt'] ?? ''),
                'fecha_actualizacion_fmt'=> (string) ($detalle['fecha_actualizacion_fmt'] ?? ''),
                'dias_en_pipeline'       => isset($detalle['dias_en_pipeline']) ? (int) $detalle['dias_en_pipeline'] : 0,
                'datos_moto'             => $datosMoto,
                'evidencias'             => is_array($detalle['evidencias'] ?? null) ? $detalle['evidencias'] : [],
                'observaciones'          => is_array($detalle['observaciones'] ?? null) ? $detalle['observaciones'] : [],
                'historial'              => $historial,
            ];

            echo json_encode(['success' => true, 'detalle' => $detalleCompacto]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/guardarDatosMoto
     * Body JSON: { "id_credito": 12345, "datos": { "moto_marca": "Honda", ... } }
     * Guarda los datos de la moto y logísticos en adj_operacion.
     */
    public function guardarDatosMoto()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $datos     = $body['datos'] ?? [];

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        if (!is_array($datos) || empty($datos)) {
            echo json_encode(['success' => false, 'message' => 'No se recibieron datos.']);
            return;
        }

        $idUsuario     = (int)   ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = (string) ($_SESSION['nombre']    ?? $_SESSION['usuario']     ?? '');

        try {
            // Obtener id_operacion a partir de id_credito
            $op = $this->model->obtenerOCrearOperacion($idCredito, '', $idUsuario);
            if (empty($op['success']) || empty($op['detalle']['id'])) {
                echo json_encode(['success' => false, 'message' => $op['message'] ?? 'No se encontró la operación.']);
                return;
            }
            $idOperacion = (int) $op['detalle']['id'];

            $result = $this->model->guardarDatosMoto($idOperacion, $datos, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/consultarRepuveCredito
     * Body JSON: { "id_credito": 12345 }
     * Consulta REPUVE una sola vez por crédito y reutiliza el registro en BD.
     */
    public function consultarRepuveCredito()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $result = $this->model->consultarRepuvePorCredito($idCredito, $idUsuario);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/obtenerDatosMotoFactura
     * Body JSON: { "id_credito": 12345 }
     * Extrae VIN, motor y color desde el documento FACTURA (si existe).
     */
    public function obtenerDatosMotoFactura()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $result = $this->model->obtenerDatosMotoDesdeFactura($idCredito);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/ejecutarConsultaRepuve
     * Body JSON: { "id_credito": 12345, "tipo": "plate"|"vin", "valor": "..." }
     * Exige crédito con asignación activa en adjudicación.
     */
    public function ejecutarConsultaRepuve()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $tipoRaw   = strtolower(trim((string) ($body['tipo'] ?? 'plate')));
        $tipo      = in_array($tipoRaw, ['plate', 'vin'], true) ? $tipoRaw : 'plate';
        $valor     = (string) ($body['valor'] ?? '');
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $valCred = $this->model->buscarCreditoEnAdjudicacion($idCredito);
            if (empty($valCred['success'])) {
                echo json_encode($valCred, JSON_UNESCAPED_UNICODE);
                return;
            }

            $result = $this->model->consultarRepuveConCriterio($idCredito, $tipo, $valor, $idUsuario);
            $result['credito'] = [
                'id_credito'     => $idCredito,
                'nombre_cliente' => (string) ($valCred['nombre_cliente'] ?? ''),
            ];
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/obtenerResumenEvidenciasCreditos
     * Body JSON: { "ids_credito": [123, 456] }
     */
    public function obtenerResumenEvidenciasCreditos()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids  = $body['ids_credito'] ?? $body['ids'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            echo json_encode(['success' => true, 'resumen' => []]);
            return;
        }

        try {
            $resumen = $this->model->obtenerResumenEvidenciasPorCreditos($ids);
            echo json_encode(['success' => true, 'resumen' => $resumen]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET/POST /MotosAdjudicadas/obtenerMisAdjudicaciones
     * Query opcional: omitir_morosidad=1 — omite consulta a Segundómetro (respuesta más rápida; usar luego obtenerMorosidadMisCreditos).
     */
    public function obtenerMisAdjudicaciones()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idPersona = $_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? null;
        if (!$idPersona) {
            echo json_encode(['success' => false, 'message' => 'Sesión no identificada.']);
            return;
        }

        $omitirMorosidad = isset($_GET['omitir_morosidad'])
            && (string) $_GET['omitir_morosidad'] === '1';

        try {
            $pack              = $this->model->obtenerMisAdjudicaciones((int) $idPersona, !$omitirMorosidad);
            $creditos          = $pack['creditos'];
            $resumenEvidencias = $pack['resumen_evidencias'];
            echo json_encode([
                'success'            => true,
                'creditos'           => $creditos,
                'resumen_evidencias' => $resumenEvidencias,
                'morosidad_diferida' => $omitirMorosidad,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/obtenerMorosidadMisCreditos
     * Body JSON: { "ids_credito": [1, 2, 3] } — buckets desde Segundómetro (segunda fase de Mis adjudicaciones).
     */
    public function obtenerMorosidadMisCreditos()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids  = $body['ids_credito'] ?? $body['ids'] ?? [];

        if (!is_array($ids) || $ids === []) {
            echo json_encode(['success' => true, 'morosidad' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $morosidad = $this->model->obtenerMorosidadSegundometroPorCreditos(
                array_values(array_unique(array_filter(array_map('intval', $ids), static fn ($v) => $v > 0)))
            );
            echo json_encode(['success' => true, 'morosidad' => $morosidad], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    /**
     * POST /MotosAdjudicadas/guardarVeredictoEvidenciaAtn
     * Body JSON: { "id_operacion", "id_evidencia", "val_atn": 1|2, "comentario" }
     */
    public function guardarVeredictoEvidenciaAtn()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body         = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion  = (int) ($body['id_operacion'] ?? 0);
        $idEvidencia  = (int) ($body['id_evidencia'] ?? 0);
        $valAtn       = (int) ($body['val_atn'] ?? 0);
        $comentario   = (string) ($body['comentario'] ?? '');
        $idUsuario    = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->guardarVeredictoEvidenciaAtn(
                $idOperacion,
                $idEvidencia,
                $valAtn,
                $comentario,
                $idUsuario,
                $nombreUsuario
            );
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/finalizarCierreValidacionEvidenciaAtn
     * Body JSON: { "id_operacion" } — al cerrar el modal: si hay rechazos en medios, pasa a Revisión Recuperaciones (no avanza a Procesando IA).
     */
    public function finalizarCierreValidacionEvidenciaAtn()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $idUsuario   = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->finalizarCierreValidacionEvidenciaAtn($idOperacion, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/enviarEvidenciasValidadasAtencion
     * Body JSON: { "id_operacion" } — único paso a Procesando IA (pestaña Aprobados).
     */
    public function enviarEvidenciasValidadasAtencion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body            = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion     = (int) ($body['id_operacion'] ?? 0);
        $idUsuario       = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario   = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->enviarEvidenciasValidadasAtencion($idOperacion, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/confirmarCierreDocumentacionEnS2
     * Body JSON: { "id_operacion": 12 } — vista 4: confirma cierre en S2 y envía la operación a Recepción (vista 5).
     */
    public function confirmarCierreDocumentacionEnS2()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $idUsuario   = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->confirmarCierreDocumentacionEnS2($idOperacion, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/enviarRecuperacionACartera
     * Body JSON: { "id_operacion": 12, "comentarios": "..." } — Recuperación → Cartera (Cierre documentado).
     */
    public function enviarRecuperacionACartera()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body          = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion   = (int) ($body['id_operacion'] ?? 0);
        $comentarios   = trim((string) ($body['comentarios'] ?? ''));
        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->enviarRecuperacionACartera($idOperacion, $comentarios, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function eliminarOperacion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = (int) ($body['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->eliminarOperacion($id);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
