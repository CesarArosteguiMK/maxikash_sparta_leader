<?php

namespace Controllers;

use Core\Controller;
use Models\ConfigMotosAdj as ConfigMotosAdjModel;

class ConfigMotosAdj extends Controller
{
    public function consulta()
    {
        self::set('titulo', 'Config Motos Adj');
        self::render('config_motos_adj');
    }

    public function obtener()
    {
        try {
            $model = new ConfigMotosAdjModel();
            self::respuestaJSON(self::respuesta(true, null, $model->obtener()));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener configuracion.', null, $e->getMessage()));
        }
    }

    public function guardar()
    {
        $raw = file_get_contents('php://input');
        $data = [];
        if ($raw !== '' && $raw !== false) {
            $data = json_decode($raw, true) ?: [];
        }
        if (empty($data)) {
            $data = $_POST;
        }

        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        try {
            $model = new ConfigMotosAdjModel();
            self::respuestaJSON($model->guardar($data, $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al guardar configuracion.', null, $e->getMessage()));
        }
    }

    public function fadObtener()
    {
        try {
            $model = new ConfigMotosAdjModel();
            self::respuestaJSON(self::respuesta(true, null, $model->obtenerFad()));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener configuracion FAD.', null, $e->getMessage()));
        }
    }

    public function fadGlobal()
    {
        $raw = file_get_contents('php://input');
        $data = [];
        if ($raw !== '' && $raw !== false) {
            $data = json_decode($raw, true) ?: [];
        }
        if (empty($data)) {
            $data = $_POST;
        }

        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        try {
            $model = new ConfigMotosAdjModel();
            self::respuestaJSON($model->actualizarFadGlobal((string) ($data['accion'] ?? ''), $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al actualizar FAD global.', null, $e->getMessage()));
        }
    }

    public function fadGuardarRegla()
    {
        $raw = file_get_contents('php://input');
        $data = [];
        if ($raw !== '' && $raw !== false) {
            $data = json_decode($raw, true) ?: [];
        }
        if (empty($data)) {
            $data = $_POST;
        }

        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        try {
            $model = new ConfigMotosAdjModel();
            self::respuestaJSON($model->guardarReglaFad($data, $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al guardar regla FAD.', null, $e->getMessage()));
        }
    }
}
