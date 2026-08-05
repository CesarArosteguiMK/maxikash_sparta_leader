<?php

namespace Services;

interface FadRrhhHttpTransport
{
    /** @return array{status:int,headers:array<string,string>,body:string} */
    public function request(string $method, string $url, array $headers = [], $body = null): array;
}

final class FadRrhhCurlTransport implements FadRrhhHttpTransport
{
    public function request(string $method, string $url, array $headers = [], $body = null): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('La extensión cURL de PHP no está disponible.');
        }
        $responseHeaders = [];
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 35,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return $length;
            },
        ]);
        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }
        $responseBody = curl_exec($handle);
        if ($responseBody === false) {
            $error = curl_error($handle);
            curl_close($handle);
            throw new \RuntimeException('No fue posible comunicarse con FAD: ' . $error);
        }
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);
        return ['status' => $status, 'headers' => $responseHeaders, 'body' => (string) $responseBody];
    }
}

final class FadRrhhPortalClient
{
    private FadRrhhHttpTransport $http;
    private string $apiBase;
    private string $portalBase;
    private ?array $portalBootstrap = null;

    public function __construct(
        ?FadRrhhHttpTransport $http = null,
        string $apiBase = 'https://api.firmaautografa.com',
        string $portalBase = 'https://clientes.firmaautografa.com'
    ) {
        $this->http = $http ?? new FadRrhhCurlTransport();
        $this->apiBase = $this->validatedBase($apiBase, 'api.firmaautografa.com');
        $this->portalBase = $this->validatedBase($portalBase, 'clientes.firmaautografa.com');
    }

    /**
     * Replica el inicio de sesión del portal sin persistir sus constantes públicas.
     * Las constantes se leen en memoria desde el bundle vigente del propio portal.
     */
    public function authenticate(string $username, string $password): array
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            throw new \RuntimeException('Faltan las credenciales del portal FAD RRHH.');
        }
        $bootstrap = $this->loadPortalBootstrap();
        $payload = json_encode([
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
        ], JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new \RuntimeException('No se pudo preparar la autenticación FAD.');
        }
        $cipher = openssl_encrypt(
            $payload,
            'aes-128-cbc',
            $bootstrap['login_key'],
            OPENSSL_RAW_DATA,
            $bootstrap['login_iv']
        );
        if ($cipher === false) {
            throw new \RuntimeException('No se pudo cifrar la autenticación FAD.');
        }
        $response = $this->http->request(
            'POST',
            $this->api('/authorization-server/oauth/token'),
            ['Authorization: ' . $bootstrap['authorization'], 'Content-Type: text/plain'],
            base64_encode($cipher)
        );
        $json = $this->jsonResponse($response, 'autenticar con FAD');
        $token = trim((string) ($json['access_token'] ?? $json['data']['access_token'] ?? ''));
        if ($token === '') {
            throw new \RuntimeException('FAD no devolvió un token de acceso.');
        }
        return [
            'access_token' => $token,
            'expires_in' => (int) ($json['expires_in'] ?? 0),
            'token_type' => (string) ($json['token_type'] ?? 'bearer'),
        ];
    }

    public function getCurrentUser(string $token): array
    {
        $uuid = $this->uuidV4();
        $response = $this->http->request(
            'GET',
            $this->api('/clients/users/' . rawurlencode($uuid)),
            $this->bearerHeaders($token)
        );
        $json = $this->jsonResponse($response, 'consultar el usuario FAD');
        $data = is_array($json['data'] ?? null) ? $json['data'] : $json;
        $userId = trim((string) ($data['userId'] ?? $data['id'] ?? ''));
        if ($userId === '') {
            throw new \RuntimeException('FAD no devolvió el identificador del usuario.');
        }
        return ['userId' => $userId];
    }

    public function getRequisitionTypes(string $token): array
    {
        return $this->dataList($this->authorizedJson('GET', '/clients/requisitions/types', $token));
    }

    public function getLifeTimes(string $token): array
    {
        return $this->dataList($this->authorizedJson('GET', '/clients/requisitions/lifetimes', $token));
    }

    public function getSigners(string $token, string $userId): array
    {
        return $this->dataList($this->authorizedJson(
            'GET',
            '/clients/users/' . rawurlencode($userId) . '/signers',
            $token
        ));
    }

    public function uploadDocument(string $token, string $userId, string $pdfPath, string $fileName): array
    {
        if (!is_file($pdfPath) || !is_readable($pdfPath)) {
            throw new \RuntimeException('El contrato PDF no está disponible para cargar.');
        }
        $response = $this->http->request(
            'POST',
            $this->api('/clients/users/' . rawurlencode($userId) . '/documents'),
            $this->bearerHeaders($token),
            ['files' => new \CURLFile($pdfPath, 'application/pdf', $fileName)]
        );
        return $this->jsonResponse($response, 'cargar el contrato en FAD');
    }

    public function createSigner(string $token, string $userId, array $signer): array
    {
        return $this->authorizedJson(
            'POST',
            '/clients/users/' . rawurlencode($userId) . '/signers',
            $token,
            $signer
        );
    }

    public function createRequisition(string $token, string $userId, array $requisition): array
    {
        return $this->authorizedJson(
            'POST',
            '/clients/users/' . rawurlencode($userId) . '/requisitions',
            $token,
            $requisition
        );
    }

    public function getRequisitionInfo(string $token, string $requisitionId): array
    {
        return $this->authorizedJson(
            'GET',
            '/clients/requisitions/' . rawurlencode($requisitionId) . '/info',
            $token
        );
    }

    public function downloadSignedPdf(string $token, string $requisitionId): string
    {
        $response = $this->http->request(
            'GET',
            $this->api('/cloud/getBackUp/' . rawurlencode($requisitionId) . '.pdf'),
            $this->bearerHeaders($token)
        );
        if ($response['status'] < 200 || $response['status'] >= 300) {
            throw new \RuntimeException('FAD no permitió descargar el PDF firmado.');
        }
        $body = (string) $response['body'];
        if (!str_starts_with($body, '%PDF-')) {
            throw new \RuntimeException('La respuesta de FAD no contiene un PDF válido.');
        }
        return $body;
    }

    private function authorizedJson(string $method, string $path, string $token, ?array $payload = null): array
    {
        $headers = $this->bearerHeaders($token);
        $body = null;
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $response = $this->http->request($method, $this->api($path), $headers, $body);
        return $this->jsonResponse($response, 'consultar la API FAD');
    }

    private function loadPortalBootstrap(): array
    {
        if ($this->portalBootstrap !== null) {
            return $this->portalBootstrap;
        }
        $index = $this->http->request('GET', $this->portalBase . '/');
        if ($index['status'] < 200 || $index['status'] >= 400) {
            throw new \RuntimeException('No se pudo leer la configuración pública del portal FAD.');
        }
        if (!preg_match('/<script[^>]+src=["\']([^"\']*main\.[^"\']+\.js)["\']/i', $index['body'], $scriptMatch)) {
            throw new \RuntimeException('No se localizó el bundle principal del portal FAD.');
        }
        $scriptUrl = $this->absolutePortalUrl($scriptMatch[1]);
        $bundle = $this->http->request('GET', $scriptUrl);
        if ($bundle['status'] < 200 || $bundle['status'] >= 400) {
            throw new \RuntimeException('No se pudo leer el bundle público del portal FAD.');
        }
        $authorization = $this->extractBundleValue($bundle['body'], '/\bcisab\s*:\s*["\']([^"\']+)["\']/');
        $loginKey = $this->extractBundleValue($bundle['body'], '/this\.LOGIN_KEY\s*=\s*["\']([^"\']+)["\']/');
        $loginIv = $this->extractBundleValue($bundle['body'], '/this\.LOGIN_IV\s*=\s*["\']([^"\']+)["\']/');
        if (strlen($loginKey) !== 16 || strlen($loginIv) !== 16 || !str_starts_with($authorization, 'Basic ')) {
            throw new \RuntimeException('La configuración pública de autenticación FAD cambió.');
        }
        $this->portalBootstrap = [
            'authorization' => $authorization,
            'login_key' => $loginKey,
            'login_iv' => $loginIv,
        ];
        return $this->portalBootstrap;
    }

    private function extractBundleValue(string $bundle, string $pattern): string
    {
        if (!preg_match($pattern, $bundle, $match)) {
            throw new \RuntimeException('No se encontró una constante requerida en el portal FAD.');
        }
        return (string) $match[1];
    }

    private function jsonResponse(array $response, string $operation): array
    {
        $json = json_decode((string) $response['body'], true);
        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($json)) {
            throw new \RuntimeException('No fue posible ' . $operation . '.');
        }
        if (array_key_exists('success', $json) && !$json['success']) {
            throw new \RuntimeException('FAD rechazó la operación solicitada.');
        }
        return $json;
    }

    private function dataList(array $json): array
    {
        $data = $json['data'] ?? $json;
        return is_array($data) ? array_values($data) : [];
    }

    private function bearerHeaders(string $token): array
    {
        $token = trim($token);
        if ($token === '') {
            throw new \RuntimeException('Token FAD vacío.');
        }
        return ['Authorization: Bearer ' . $token, 'Accept: application/json'];
    }

    private function api(string $path): string
    {
        return $this->apiBase . '/' . ltrim($path, '/');
    }

    private function absolutePortalUrl(string $path): string
    {
        if (preg_match('#^https://#i', $path)) {
            $host = strtolower((string) parse_url($path, PHP_URL_HOST));
            if ($host !== 'clientes.firmaautografa.com') {
                throw new \RuntimeException('El portal intentó cargar un bundle desde un dominio no autorizado.');
            }
            return $path;
        }
        return $this->portalBase . '/' . ltrim($path, '/');
    }

    private function validatedBase(string $url, string $expectedHost): string
    {
        $url = rtrim(trim($url), '/');
        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https'
            || strtolower((string) parse_url($url, PHP_URL_HOST)) !== $expectedHost) {
            throw new \InvalidArgumentException('Dominio FAD no autorizado.');
        }
        return $url;
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
            . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}
