<?php

namespace Controllers;

use Core\Controller;
use Models\AlmacenVirtual as AlmacenVirtualModel;

class AlmacenVirtual extends Controller
{
    private AlmacenVirtualModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new AlmacenVirtualModel();
    }

    public function index()
    {
        return $this->inventario();
    }

    public function inventario()
    {
        self::set('titulo', 'Almacen Virtual');
        self::set('av_modulo_id', AlmacenVirtualModel::moduloAlmacenVirtual());
        return self::render('almacen_virtual');
    }

    public function resumen()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->model->obtenerResumen(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el resumen de Almacen Virtual.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function celulas()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $rows = [];
            foreach ($this->model->obtenerCelulas() as $id => $nombre) {
                $rows[] = ['id_celula' => (int) $id, 'nombre' => $nombre];
            }
            echo json_encode(['success' => true, 'datos' => $rows], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'No se pudieron cargar las celulas.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function ubicaciones()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->model->listarUbicacionesActivas(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'No se pudieron cargar las ubicaciones.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function unidades()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filtros = [
                'q' => trim((string) ($_GET['q'] ?? '')),
                'id_celula' => (int) ($_GET['id_celula'] ?? 0),
                'estatus' => trim((string) ($_GET['estatus'] ?? '')),
                'id_ubicacion' => (int) ($_GET['id_ubicacion'] ?? 0),
                'page' => (int) ($_GET['page'] ?? 1),
                'limit' => (int) ($_GET['limit'] ?? 8),
            ];

            echo json_encode($this->model->listarUnidades($filtros), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron cargar las unidades.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function pendientesMotosAdjudicadas()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filtros = [
                'q' => trim((string) ($_GET['q'] ?? '')),
                'limit' => (int) ($_GET['limit'] ?? 8),
                'page' => (int) ($_GET['page'] ?? 1),
            ];
            echo json_encode($this->model->listarPendientesMotosAdjudicadas($filtros), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron cargar pendientes de Motos Adjudicadas.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function crearDesdeMotosAdjudicadas()
    {
        header('Content-Type: application/json; charset=utf-8');
        $data = $this->payloadJson();
        $idOperacion = (int) ($data['id_operacion'] ?? 0);
        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'Indica un id_operacion valido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));

        try {
            echo json_encode(
                $this->model->crearDesdeMotosAdjudicadas($idOperacion, $idUsuario, $nombreUsuario),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo crear la unidad desde Motos Adjudicadas.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    private function payloadJson(): array
    {
        $raw = file_get_contents('php://input');
        $data = [];
        if ($raw !== '' && $raw !== false) {
            $data = json_decode($raw, true) ?: [];
        }
        if (empty($data)) {
            $data = $_POST;
        }

        return is_array($data) ? $data : [];
    }
}
