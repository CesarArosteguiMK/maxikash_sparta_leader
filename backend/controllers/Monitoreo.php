<?php

namespace Controllers;

use Core\Controller;

/**
 * Panel privado para supervisar las APIs administradas por Pedro.
 *
 * Este acceso es deliberadamente independiente del catalogo de modulos y de
 * los permisos almacenados en base de datos.
 */
class Monitoreo extends Controller
{
    private const PERSONA_AUTORIZADA_ID = 877;

    public function index()
    {
        if (!$this->esPersonaAutorizada()) {
            header('Location: /inicio', true, 302);
            exit;
        }

        $modoPlaneador = strtolower(trim((string) ($_GET['modo'] ?? ''))) === 'planeador';
        $this->set('titulo', ($modoPlaneador ? 'Modo planeador' : 'Monitoreo de servicios') . ' | Sparta Ledger');
        $this->set('modoPlaneador', $modoPlaneador);
        self::render('monitoreo', false);
    }

    /** Devuelve salud, OpenAPI y modificaciones de los servicios. */
    public function estado()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->autorizarJson()) {
            return;
        }

        try {
            $monitor = new \Services\ServiciosAdministradosMonitorService();
            $payload = $monitor->obtener((string) ($_GET['servicio'] ?? ''));
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\InvalidArgumentException $e) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[Monitoreo estado] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo completar el monitoreo de servicios.',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /** Inicia, reinicia o detiene de forma controlada la API local. */
    public function accion()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->autorizarJson()) {
            return;
        }
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido'], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (strcasecmp((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''), 'XMLHttpRequest') !== 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Solicitud invalida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $payload = $this->leerPayloadJson();
        $servicio = strtolower(trim((string) ($payload['servicio'] ?? '')));
        $accion = strtolower(trim((string) ($payload['accion'] ?? '')));
        if ($servicio !== 'condonaciones' || !in_array($accion, ['iniciar', 'reiniciar', 'detener'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Accion no permitida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $monitor = new \Services\ServiciosAdministradosMonitorService();
            $result = $monitor->accionLocal($servicio, $accion);
            if (empty($result['success'])) {
                http_response_code(409);
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[Monitoreo accion] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo iniciar la API local.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /** Consola y listado de logs rotativos de Condonaciones. */
    public function logs()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->autorizarJson()) {
            return;
        }
        try {
            $monitor = new \Services\ServiciosAdministradosMonitorService();
            echo json_encode(
                $monitor->obtenerLogs((string) ($_GET['archivo'] ?? '')),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[Monitoreo logs] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudieron consultar los logs.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /** Estado y salida casi en tiempo real de la terminal de Condonaciones. */
    public function terminal()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->autorizarJson()) {
            return;
        }
        try {
            $monitor = new \Services\ServiciosAdministradosMonitorService();
            echo json_encode($monitor->obtenerTerminal(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            error_log('[Monitoreo terminal] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo leer la terminal local.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /** Descarga un log validado del directorio exclusivo del monitor. */
    public function log()
    {
        if (!$this->esPersonaAutorizada()) {
            http_response_code(403);
            echo 'No autorizado';
            return;
        }
        $this->liberarSesion();
        try {
            $monitor = new \Services\ServiciosAdministradosMonitorService();
            $file = $monitor->obtenerLogArchivo((string) ($_GET['archivo'] ?? ''));
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
            header('Content-Length: ' . (string) filesize($file['path']));
            readfile($file['path']);
        } catch (\InvalidArgumentException $e) {
            http_response_code(404);
            echo 'Log no encontrado';
        }
    }

    /** Ejecuta una peticion contra un endpoint perteneciente al OpenAPI autorizado. */
    public function probar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->autorizarJson()) {
            return;
        }
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'
            || strcasecmp((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''), 'XMLHttpRequest') !== 0) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Solicitud no permitida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $payload = $this->leerPayloadJson();
        try {
            $monitor = new \Services\ServiciosAdministradosMonitorService();
            $result = $monitor->probarEndpoint(
                (string) ($payload['servicio'] ?? ''),
                (string) ($payload['metodo'] ?? 'GET'),
                (string) ($payload['path'] ?? ''),
                is_array($payload['query'] ?? null) ? $payload['query'] : [],
                is_array($payload['body'] ?? null) ? $payload['body'] : null,
                !empty($payload['confirmar_mutacion']),
                is_array($payload['auth'] ?? null) ? $payload['auth'] : []
            );
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[Monitoreo probar] ' . $e->getMessage());
            http_response_code(502);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function autorizarJson(): bool
    {
        if ($this->esPersonaAutorizada()) {
            $this->liberarSesion();
            return true;
        }

        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
        return false;
    }

    private function esPersonaAutorizada(): bool
    {
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        return $personaId === self::PERSONA_AUTORIZADA_ID;
    }

    /** Permite que el polling de terminal corra mientras una acción sigue activa. */
    private function liberarSesion(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
    }

    /** @return array<string, mixed> */
    private function leerPayloadJson(): array
    {
        $raw = file_get_contents('php://input');
        $payload = is_string($raw) ? json_decode($raw, true) : null;
        return is_array($payload) ? $payload : $_POST;
    }
}
