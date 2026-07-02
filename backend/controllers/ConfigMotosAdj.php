<?php

namespace Controllers;

use Core\Controller;
use Models\ConfigMotosAdj as ConfigMotosAdjModel;

class ConfigMotosAdj extends Controller
{
    public function consulta()
    {
        self::set('titulo', 'Parametros Motos');
        self::render('config_motos_adj');
    }

    public function rutas()
    {
        self::set('titulo', 'Parametros Motos - Fechas de rutas');
        self::render('config_motos_adj_rutas');
    }

    public function fad()
    {
        self::set('titulo', 'Parametros Motos - FAD');
        self::render('config_motos_adj_fad');
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
        $data = $this->payloadJson();
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        try {
            $model = new ConfigMotosAdjModel();
            $accion = (string) ($data['accion'] ?? '');
            if (strtolower($accion) === 'off') {
                self::respuestaJSON($model->apagarFadGlobal((string) ($data['motivo'] ?? ''), $idUsuario));
                return;
            }
            self::respuestaJSON($model->encenderFadGlobal($idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al actualizar FAD global.', null, $e->getMessage()));
        }
    }

    public function fadGuardarRegla()
    {
        $data = $this->payloadJson();
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        try {
            $model = new ConfigMotosAdjModel();
            self::respuestaJSON($model->agregarExcepcionCredito($data, $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al guardar excepcion FAD.', null, $e->getMessage()));
        }
    }

    public function fadDesactivarExcepcion()
    {
        $data = $this->payloadJson();
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        try {
            $model = new ConfigMotosAdjModel();
            self::respuestaJSON($model->desactivarExcepcionCredito((int) ($data['id'] ?? 0), $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al desactivar excepcion FAD.', null, $e->getMessage()));
        }
    }

    public function fadPendientes()
    {
        self::respuestaJSON($this->fadApiRequest('GET', '/api/fad/motos-adjudicadas/pending'));
    }

    public function fadRecordatorio()
    {
        $data = $this->payloadJson();
        $idOperacion = (int) ($data['id_operacion'] ?? 0);
        if ($idOperacion <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Operacion no valida.']);
            return;
        }

        self::respuestaJSON($this->fadApiRequest('POST', '/api/fad/motos-adjudicadas/' . $idOperacion . '/reminder'));
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

    private function fadApiRequest(string $method, string $path): array
    {
        $base = rtrim((string) (getenv('FAD_MOTOS_API_BASE') ?: ''), '/');
        if ($base === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base = $scheme . '://' . $host;
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'mensaje' => 'PHP no tiene cURL habilitado.'];
        }

        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
        ]);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
        }
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $err) {
            return ['success' => false, 'mensaje' => 'No se pudo conectar con el servicio FAD.', 'error' => $err];
        }

        $json = json_decode((string) $raw, true);
        return [
            'success' => $status >= 200 && $status < 300,
            'status' => $status,
            'datos' => $json,
            'raw' => $json ? null : $raw,
            'mensaje' => $status >= 200 && $status < 300 ? 'Respuesta FAD recibida.' : 'El servicio FAD devolvio un error.',
        ];
    }
}
