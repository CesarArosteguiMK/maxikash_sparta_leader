<?php

namespace controllers;

use Core\Controller;
use Models\Atlas as AtlasDAO;

class Atlas extends Controller
{
    public function atlas()
    {
        header('Location: /Atlas/catalogos', true, 302);
        exit;
    }

    public function catalogos()
    {
        $this->set('titulo', 'Catálogos');
        $this->set('google_maps_api_key_js', json_encode(defined('GOOGLE_MAPS_API_KEY') ? (string)GOOGLE_MAPS_API_KEY : '', JSON_UNESCAPED_SLASHES));
        $this->render('atlas');
    }

    public function notificacionesApp()
    {
        $this->set('titulo', 'Notificaciones App');
        $this->render('atlas_notificaciones_app');
    }

    public function sucursales()
    {
        header('Location: /Atlas/catalogos', true, 302);
        exit;
    }

    public function getSucursales()
    {
        $this->json(AtlasDAO::getSucursales());
    }

    public function getCatalogos()
    {
        $this->json(AtlasDAO::getCatalogos());
    }

    public function guardarSucursal()
    {
        $this->json(AtlasDAO::guardarSucursal($this->payload()));
    }

    public function guardarDivision()
    {
        $this->json(AtlasDAO::guardarDivision($this->payload()));
    }

    public function guardarDistribuidor()
    {
        $this->json(AtlasDAO::guardarDistribuidor($this->payload()));
    }

    public function guardarDiversificacion()
    {
        $this->json(AtlasDAO::guardarDiversificacion($this->payload()));
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
        $token = trim((string)($payload['token'] ?? ''));

        if (!in_array($method, ['GET', 'POST', 'PATCH', 'DELETE'], true)) {
            $this->json(['success' => false, 'mensaje' => 'Método no permitido.']);
        }

        if (!$this->atlasNotificacionesPathPermitido($path)) {
            $this->json(['success' => false, 'mensaje' => 'Endpoint Atlas App no permitido.']);
        }

        if ($token === '' && $path !== '/auth/login') {
            $this->json(['success' => false, 'mensaje' => 'Captura el token de Atlas App o inicia sesión.']);
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
        if ($token !== '' && $path !== '/auth/login') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

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
        $this->json([
            'success' => $httpCode >= 200 && $httpCode < 300,
            'status' => $httpCode,
            'datos' => is_array($decoded) ? $decoded : null,
            'raw' => is_array($decoded) ? null : $raw,
            'mensaje' => ($httpCode >= 200 && $httpCode < 300) ? 'Respuesta recibida.' : 'Atlas App devolvió un error.',
        ]);
    }

    private function atlasNotificacionesPathPermitido(string $path): bool
    {
        if ($path === '/auth/login') return true;
        if (in_array($path, [
            '/api/atlas/push-notifications/send',
            '/api/atlas/push-campaigns/send',
            '/api/atlas/push-campaigns',
            '/api/atlas/push-notifications/log',
            '/api/atlas/notifications',
            '/api/atlas/notifications/inbox',
            '/api/atlas/push-tokens',
        ], true)) return true;

        return (bool)preg_match('#^/api/atlas/notifications/\d+$#', $path)
            || (bool)preg_match('#^/api/atlas/notifications/inbox/\d+/(read|hide)$#', $path);
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

    private function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
