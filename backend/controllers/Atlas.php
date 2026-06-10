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
        $this->set('atlas_admin_configurada', $this->atlasAdminApiKey() !== '');
        $this->render('atlas_notificaciones_app');
    }

    public function catalogosComerciales()
    {
        $this->set('titulo', 'Catálogos comerciales');
        $this->render('atlas_catalogos_comerciales');
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

    public function getCatalogosComerciales()
    {
        $this->json(AtlasDAO::getCatalogosComerciales());
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
