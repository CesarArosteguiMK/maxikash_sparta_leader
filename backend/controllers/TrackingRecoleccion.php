<?php

namespace Controllers;

use Core\Controller;
use Models\TrackingRecoleccion as TrackingModel;

class TrackingRecoleccion extends Controller
{
    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    public function index()
    {
        self::set('titulo', 'Tracking Recolección — Motos Adjudicadas');
        if (defined('GOOGLE_MAPS_API_KEY')) {
            self::set('google_maps_api_key_js', GOOGLE_MAPS_API_KEY);
        }
        return self::render('tracking_recoleccion');
    }

    // =========================================================================
    // API — CRÉDITOS PASO 2
    // =========================================================================

    /**
     * GET /TrackingRecoleccion/obtenerCreditosPaso2[?estado=X&municipio=Y]
     */
    public function obtenerCreditosPaso2()
    {
        $estado    = trim((string) ($_GET['estado']    ?? $_POST['estado']    ?? ''));
        $municipio = trim((string) ($_GET['municipio'] ?? $_POST['municipio'] ?? ''));

        try {
            $model   = new TrackingModel();
            $creditos = $model->obtenerCreditosPaso2(
                $estado    !== '' ? $estado    : null,
                $municipio !== '' ? $municipio : null
            );
            self::respuestaJSON(self::respuesta(true, null, $creditos));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener créditos.', null, $e->getMessage()));
        }
    }

    // =========================================================================
    // API — CATÁLOGOS
    // =========================================================================

    /**
     * GET /TrackingRecoleccion/obtenerEstados
     */
    public function obtenerEstados()
    {
        try {
            $model  = new TrackingModel();
            $estados = $model->obtenerEstados();
            self::respuestaJSON(self::respuesta(true, null, $estados));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener estados.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerMunicipios?estado=X
     */
    public function obtenerMunicipios()
    {
        $estado = trim((string) ($_GET['estado'] ?? $_POST['estado'] ?? ''));
        if ($estado === '') {
            self::respuestaJSON(self::respuesta(false, 'Parámetro estado requerido.'));
            return;
        }
        try {
            $model      = new TrackingModel();
            $municipios = $model->obtenerMunicipiosPorEstado($estado);
            self::respuestaJSON(self::respuesta(true, null, $municipios));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener municipios.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerUsuariosRecoleccion
     */
    public function obtenerUsuariosRecoleccion()
    {
        try {
            $model    = new TrackingModel();
            $usuarios = $model->obtenerUsuariosRecoleccion();
            self::respuestaJSON(self::respuesta(true, null, $usuarios));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener usuarios.', null, $e->getMessage()));
        }
    }

    // =========================================================================
    // API — RUTAS
    // =========================================================================

    /**
     * POST /TrackingRecoleccion/guardarRuta
     * Body JSON: { nombre_ruta, estado, municipio, fecha_programada, modo,
     *              usuarios:[], creditos:[], id_ruta? }
     */
    public function guardarRuta()
    {
        $idUsuario  = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $rawBody    = file_get_contents('php://input');
        $data       = [];

        if ($rawBody !== '' && $rawBody !== false) {
            $data = json_decode($rawBody, true) ?: [];
        }
        // Fallback a POST fields
        if (empty($data)) {
            $data = $_POST;
            if (isset($_POST['usuarios']) && is_string($_POST['usuarios'])) {
                $data['usuarios'] = json_decode($_POST['usuarios'], true) ?: [];
            }
            if (isset($_POST['creditos']) && is_string($_POST['creditos'])) {
                $data['creditos'] = json_decode($_POST['creditos'], true) ?: [];
            }
        }

        try {
            $model  = new TrackingModel();
            $result = $model->guardarRuta($data, $idUsuario);
            self::respuestaJSON($result);
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al guardar la ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/obtenerRutas
     * Body: { estado?, municipio? }
     */
    public function obtenerRutas()
    {
        $estado    = trim((string) ($_POST['estado']    ?? $_GET['estado']    ?? ''));
        $municipio = trim((string) ($_POST['municipio'] ?? $_GET['municipio'] ?? ''));

        try {
            $model = new TrackingModel();
            $rutas = $model->obtenerRutas(
                $estado    !== '' ? $estado    : null,
                $municipio !== '' ? $municipio : null
            );
            self::respuestaJSON(self::respuesta(true, null, $rutas));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener rutas.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerDetalleRuta?id_ruta=N
     */
    public function obtenerDetalleRuta()
    {
        $idRuta = (int) ($_GET['id_ruta'] ?? $_POST['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de ruta requerido.'));
            return;
        }
        try {
            $model  = new TrackingModel();
            $detalle = $model->obtenerDetalleRuta($idRuta);
            if ($detalle === null) {
                self::respuestaJSON(self::respuesta(false, 'Ruta no encontrada.'));
                return;
            }
            self::respuestaJSON(self::respuesta(true, null, $detalle));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener detalle de ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/actualizarConfirmacionGestor
     * Body: { id_ruta, id_credito, estatus_confirmacion_gestor }
     */
    public function actualizarConfirmacionGestor()
    {
        $rawBody = file_get_contents('php://input');
        $data    = [];
        if ($rawBody !== '' && $rawBody !== false) {
            $data = json_decode($rawBody, true) ?: [];
        }
        if (empty($data)) {
            $data = $_POST;
        }
        $idRuta    = (int) ($data['id_ruta']    ?? 0);
        $idCredito = (int) ($data['id_credito'] ?? 0);
        $estatus   = trim((string) ($data['estatus_confirmacion_gestor'] ?? ''));

        if ($idRuta <= 0 || $idCredito <= 0 || $estatus === '') {
            self::respuestaJSON(self::respuesta(false, 'Parámetros incompletos.'));
            return;
        }
        try {
            $model  = new TrackingModel();
            $result = $model->actualizarConfirmacionGestor($idRuta, $idCredito, $estatus);
            self::respuestaJSON($result);
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al actualizar confirmación.', null, $e->getMessage()));
        }
    }
}
