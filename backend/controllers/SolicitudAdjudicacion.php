<?php

namespace Controllers;

use Core\Controller;
use Models\Adjudicacion as AdjudicacionDAO;
use Models\MotosAdjudicadas as MotosAdjudicadasDAO;
use Models\SolicitudAdjudicacion as SolicitudDAO;

class SolicitudAdjudicacion extends Controller
{
    private const MODULO_BANDEJA_SOLICITUDES = 204;
    private const MODULO_ATC_SOLICITUDES = 205;
    private const MODULO_DESPACHOS_SOLICITUDES = 206;

    private SolicitudDAO $model;
    private AdjudicacionDAO $creditos;
    private MotosAdjudicadasDAO $motos;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SolicitudDAO();
        $this->creditos = new AdjudicacionDAO();
        $this->motos = new MotosAdjudicadasDAO();
    }

    public function atc(): void
    {
        if (!$this->autorizarCanal('ATC', false)) {
            return;
        }
        $idCredito = (int) ($_GET['id_credito'] ?? 0);
        $url = '/AtencionClientes/atc?tab=solicitud';
        if ($idCredito > 0) {
            $url .= '&id_credito=' . $idCredito;
        }
        header('Location: ' . $url, true, 302);
        exit;
    }

    public function despachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', false)) {
            return;
        }
        self::set('titulo', 'Despachos - Solicitud de Adjudicacion');
        self::set('solicitudes_tablas_disponibles', $this->model->tablasDisponibles());
        self::render('solicitud_adjudicacion_despachos');
    }

    public function bandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', false)) {
            return;
        }
        self::set('titulo', 'Motos Adjudicadas - Bandeja de Solicitudes');
        self::set('solicitudes_tablas_disponibles', $this->model->tablasDisponibles());
        self::render('solicitud_adjudicacion_bandeja');
    }

    public function buscarCredito(): void
    {
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        $this->buscarCreditoRespuesta();
    }

    public function buscarCreditoDespachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        $this->buscarCreditoRespuesta();
    }

    /** Consulta informativa de la moto facturada para ATC, Call Center y Despachos. */
    public function consultarMotoFactura(): void
    {
        if (!$this->autorizarConsultaMotoFactura()) {
            return;
        }

        $body = $this->payload();
        $idCredito = (int) ($body['id_credito'] ?? $body['valor'] ?? 0);
        if ($idCredito <= 0) {
            $this->json(['success' => false, 'message' => 'Captura un ID de crédito válido.', 'datos' => null]);
            return;
        }

        $this->json($this->creditos->consultarMotoFacturadaMaxiProd($idCredito));
    }

    private function buscarCreditoRespuesta(): void
    {
        $body = $this->payload();
        $idCredito = (int) ($body['id_credito'] ?? $body['valor'] ?? 0);
        if ($idCredito <= 0) {
            $this->json(['success' => false, 'message' => 'Captura un ID de credito valido.']);
            return;
        }
        try {
            $this->json($this->creditos->buscarCreditoPorId($idCredito));
        } catch (\Throwable $e) {
            error_log('[SolicitudAdjudicacion] Error al consultar crédito: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No se pudo consultar el crédito.']);
        }
    }

    public function crear(): void
    {
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        $this->crearPorCanal('ATC');
    }

    public function crearCallCenter(): void
    {
        if (!$this->autorizarCanal('CALLCENTER', true)) {
            return;
        }
        $this->crearPorCanal('CALLCENTER');
    }

    public function crearDespachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        $this->crearPorCanal('DESPACHOS');
    }

    private function crearPorCanal(string $canal): void
    {
        $body = $this->payload();
        $body['canal'] = $canal;
        [$actorId, $actorNombre] = $this->actor();
        $this->json($this->model->crear($body, $actorId, $actorNombre));
    }

    /**
     * POST /SolicitudAdjudicacion/verificarRepuveSolicitud
     * Ejecuta el cruce automático una vez que la solicitud ya quedó guardada.
     */
    public function verificarRepuveSolicitud(): void
    {
        if (!$this->autorizarConsultaMotoFactura()) {
            return;
        }
        $body = $this->payload();
        $idSolicitud = (int) ($body['id_solicitud'] ?? 0);
        [$actorId, $actorNombre] = $this->actor();
        $solicitud = $this->model->obtenerPorId($idSolicitud, $actorId);
        if (!$solicitud) {
            $this->json(['success' => false, 'message' => 'La solicitud no esta disponible para la sesion actual.']);
            return;
        }
        if ((string) ($solicitud['estatus'] ?? '') === 'blacklist') {
            $this->json([
                'success' => true,
                'blacklist' => true,
                'estado' => 'ya_en_blacklist',
                'message' => 'La solicitud ya se encuentra en blacklist.',
            ]);
            return;
        }
        $resultado = $this->verificarRepuveSolicitudInterna(
            $idSolicitud,
            (int) ($solicitud['id_credito'] ?? 0),
            $actorId,
            $actorNombre
        );
        $resultado['success'] = true;
        $this->json($resultado);
    }

    /** Ejecuta REPUVE con el NIV de factura; nunca bloquea la solicitud por una falla externa. */
    private function verificarRepuveSolicitudInterna(int $idSolicitud, int $idCredito, int $actorId, string $actorNombre): array
    {
        if ($idSolicitud <= 0 || $idCredito <= 0) {
            return ['intentada' => false, 'blacklist' => false, 'estado' => 'sin_credito'];
        }

        try {
            $factura = $this->creditos->consultarMotoFacturadaMaxiProd($idCredito);
            $moto = is_array($factura['datos'] ?? null) ? $factura['datos'] : [];
            $vin = strtoupper(preg_replace('/\s+/u', '', (string) ($moto['numero_serie'] ?? '')));
            if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
                $salida = [
                    'intentada' => false,
                    'blacklist' => false,
                    'estado' => 'VALIDACION_MANUAL_REPUVE',
                    'etiqueta' => 'Validacion manual REPUVE pendiente',
                    'requiere_validacion_manual' => true,
                    'message' => 'No se encontro un NIV de 17 caracteres en la factura. Debe realizarse la validacion manual de REPUVE posteriormente.',
                ];
                $this->model->registrarResultadoRepuveKnockout($idSolicitud, $salida['estado'], $salida['etiqueta'], $salida['message'], $actorId, $actorNombre);
                return $salida;
            }

            $consulta = $this->motos->consultarRepuveConCriterio($idCredito, 'vin', $vin, $actorId);
            $reporteRobo = is_array($consulta['reporte_robo'] ?? null)
                ? $consulta['reporte_robo']
                : ['confirmado' => false];
            if (!empty($reporteRobo['confirmado'])) {
                $blacklist = $this->model->marcarBlacklistPorRepuve($idSolicitud, $actorId, $actorNombre, $reporteRobo);
                $salida = [
                    'intentada' => true,
                    'blacklist' => !empty($blacklist['success']),
                    'estado' => !empty($blacklist['success']) ? 'reporte_robo_confirmado' : 'error_blacklist',
                    'etiqueta' => 'Reporte de Robo',
                    'message' => 'No se puede Proceder con la Adjudicacion. Cualquier duda contacta a tu lider.',
                ];
                $this->model->registrarResultadoRepuveKnockout($idSolicitud, 'REPORTE_ROBO', 'Reporte de Robo', $salida['message'], $actorId, $actorNombre, $reporteRobo);
                return $salida;
            }

            $salida = $this->clasificarResultadoRepuve($consulta);
            $this->model->registrarResultadoRepuveKnockout(
                $idSolicitud,
                $salida['estado'],
                $salida['etiqueta'],
                $salida['message'],
                $actorId,
                $actorNombre,
                ['repuve_estado_tecnico' => $consulta['repuve']['estado'] ?? null]
            );
            return $salida;
        } catch (\Throwable $e) {
            error_log('[SolicitudAdjudicacion::verificarRepuveSolicitud] credito=' . $idCredito . ' :: ' . $e->getMessage());
            $salida = [
                'intentada' => false,
                'blacklist' => false,
                'estado' => 'VALIDACION_MANUAL_REPUVE',
                'etiqueta' => 'Validacion manual REPUVE pendiente',
                'requiere_validacion_manual' => true,
                'message' => 'La consulta REPUVE no fue exitosa. Debe realizarse la validacion manual de REPUVE posteriormente.',
            ];
            $this->model->registrarResultadoRepuveKnockout($idSolicitud, $salida['estado'], $salida['etiqueta'], $salida['message'], $actorId, $actorNombre);
            return $salida;
        }
    }

    /** @param array<string,mixed> $consulta @return array<string,mixed> */
    private function clasificarResultadoRepuve(array $consulta): array
    {
        $estadoTecnico = strtoupper(trim((string) ($consulta['repuve']['estado'] ?? '')));
        $texto = trim((string) ($consulta['message'] ?? $consulta['repuve']['mensaje'] ?? ''));
        $normalizado = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
        if ($estadoTecnico === 'PROCESANDO') {
            return [
                'intentada' => true, 'blacklist' => false, 'estado' => 'PENDIENTE_REPUVE',
                'etiqueta' => 'Validacion REPUVE en proceso',
                'message' => 'La consulta REPUVE esta en proceso. La adjudicacion no debe continuar hasta contar con el resultado.',
            ];
        }
        if (str_contains($normalizado, 'sin reporte de robo') || str_contains($normalizado, 'no cuenta con reporte')) {
            return [
                'intentada' => true, 'blacklist' => false, 'estado' => 'SIN_REPORTE_ROBO',
                'etiqueta' => 'Sin Reporte de Robo',
                'message' => 'REPUVE indica Sin Reporte de Robo. Puede continuar con la siguiente validacion.',
            ];
        }
        return [
            'intentada' => true, 'blacklist' => false, 'estado' => 'VALIDACION_MANUAL_REPUVE',
            'etiqueta' => 'Validacion manual REPUVE pendiente',
            'requiere_validacion_manual' => true,
            'message' => 'La consulta REPUVE fue no exitosa o no permitio confirmar el resultado. Debe realizarse la validacion manual de REPUVE posteriormente.',
        ];
    }

    public function listar(): void
    {
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        [$actorId] = $this->actor();
        $this->json($this->model->listarPorSolicitante($actorId, ['q' => $_GET['q'] ?? ''], 'ATC'));
    }

    public function listarDespachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        [$actorId] = $this->actor();
        $this->json($this->model->listarPorSolicitante($actorId, ['q' => $_GET['q'] ?? ''], 'DESPACHOS'));
    }

    public function listarBandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        $this->json($this->model->listarBandeja([
            'q' => $_GET['q'] ?? '',
            'canal' => $_GET['canal'] ?? '',
            'estatus' => $_GET['estatus'] ?? '',
        ]));
    }

    public function detalle($id = 0): void
    {
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        $this->detalleRespuesta((int) $id);
    }

    public function detalleDespachos($id = 0): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        $this->detalleRespuesta((int) $id);
    }

    public function detalleBandeja($id = 0): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        $row = $this->model->obtenerPorIdBandeja((int) $id);
        $this->json($row
            ? ['success' => true, 'solicitud' => $row]
            : ['success' => false, 'message' => 'Solicitud no encontrada.']);
    }

    public function responsablesBandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        try {
            $this->json([
                'success' => true,
                'rows' => $this->creditos->obtenerResponsables(),
            ]);
        } catch (\Throwable $e) {
            error_log('[SolicitudAdjudicacion::responsablesBandeja] ' . $e->getMessage());
            $this->json(['success' => false, 'rows' => [], 'message' => 'No se pudo cargar el catalogo de responsables.']);
        }
    }

    public function asignarBandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        $body = $this->payload();
        $idSolicitud = (int) ($body['id_solicitud'] ?? 0);
        $idPersona = (int) ($body['id_persona'] ?? 0);
        $comentario = trim((string) ($body['comentario'] ?? ''));
        if ($idSolicitud <= 0 || $idPersona <= 0) {
            $this->json(['success' => false, 'message' => 'Selecciona una solicitud y un responsable validos.']);
            return;
        }

        $solicitud = $this->model->obtenerPorIdBandeja($idSolicitud);
        if (!$solicitud) {
            $this->json(['success' => false, 'message' => 'La solicitud ya no esta disponible.']);
            return;
        }
        if (!$this->creditos->idPersonaEsResponsableActivo($idPersona)) {
            $this->json(['success' => false, 'message' => 'El responsable seleccionado no esta activo en Motos Adjudicadas.']);
            return;
        }
        $responsable = $this->creditos->obtenerDatosResponsable($idPersona);
        $nombreResponsable = trim((string) ($responsable['nombre_completo'] ?? ''));
        if ($nombreResponsable === '') {
            $this->json(['success' => false, 'message' => 'No se pudo identificar al responsable seleccionado.']);
            return;
        }

        [$actorId, $actorNombre] = $this->actor();
        $asignacion = $this->creditos->reasignarCredito(
            $idPersona,
            (int) $solicitud['id_credito'],
            $actorId
        );
        if (empty($asignacion['success'])) {
            $this->json([
                'success' => false,
                'message' => $asignacion['message'] ?? 'No se pudo completar la asignacion operativa.',
                'asignacion' => $asignacion,
            ]);
            return;
        }

        $resultado = $this->model->registrarAsignacionBandeja(
            $idSolicitud,
            $idPersona,
            $nombreResponsable,
            $actorId,
            $actorNombre,
            $comentario
        );
        $resultado['asignacion_operativa'] = $asignacion;
        $this->json($resultado);
    }

    private function detalleRespuesta(int $id): void
    {
        [$actorId] = $this->actor();
        $row = $this->model->obtenerPorId($id, $actorId);
        $this->json($row
            ? ['success' => true, 'solicitud' => $row]
            : ['success' => false, 'message' => 'Solicitud no encontrada.']);
    }

    private function payload(): array
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        return is_array($body) && $body !== [] ? $body : $_POST;
    }

    /** @return array{0:int,1:string} */
    private function actor(): array
    {
        $id = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombre = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario Sparta'));
        return [$id, $nombre];
    }

    private function autorizarCanal(string $canal, bool $json): bool
    {
        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        $permisos = [
            'ATC' => [self::MODULO_ATC_SOLICITUDES],
            'CALLCENTER' => [35],
            'DESPACHOS' => [self::MODULO_DESPACHOS_SOLICITUDES],
            'BANDEJA' => [self::MODULO_BANDEJA_SOLICITUDES],
        ];
        if (array_intersect($permisos[$canal] ?? [], $modulos)) {
            return true;
        }

        http_response_code(403);
        $etiqueta = $canal === 'CALLCENTER'
            ? 'Call Center'
            : ($canal === 'BANDEJA' ? 'la bandeja de solicitudes' : ucfirst(strtolower($canal)));
        if ($json) {
            $this->json(['success' => false, 'message' => 'No tienes permiso para operar solicitudes de ' . $etiqueta . '.']);
        } else {
            echo '<div class="alert alert-danger m-4">No tienes permiso para operar solicitudes de ' . htmlspecialchars($etiqueta) . '.</div>';
        }
        return false;
    }

    private function autorizarConsultaMotoFactura(): bool
    {
        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        if (array_intersect([
            self::MODULO_ATC_SOLICITUDES,
            35,
            self::MODULO_DESPACHOS_SOLICITUDES,
        ], $modulos)) {
            return true;
        }

        http_response_code(403);
        $this->json(['success' => false, 'message' => 'No tienes permiso para consultar datos de motocicletas facturadas.']);
        return false;
    }

    private function json(array $response): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
