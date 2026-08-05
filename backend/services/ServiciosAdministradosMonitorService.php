<?php

namespace Services;

/**
 * Monitor operativo para las APIs administradas desde Sparta.
 *
 * Comprueba salud y OpenAPI sin enviar credenciales, conserva telemetria local,
 * detecta procesos por su esquema y controla solo los iniciados por el modulo.
 */
class ServiciosAdministradosMonitorService
{
    /** @var array<int, bool> */
    private array $localPortReachability = [];

    /** @return array<string, mixed> */
    public function obtener(string $solo = ''): array
    {
        $catalogo = $this->catalogo();
        $solo = strtolower(trim($solo));
        if ($solo !== '') {
            $catalogo = array_values(array_filter($catalogo, static function (array $servicio) use ($solo): bool {
                return (string) ($servicio['id'] ?? '') === $solo;
            }));
            if ($catalogo === []) {
                throw new \InvalidArgumentException('Servicio no encontrado');
            }
        }

        $servicios = [];
        $resumen = ['stable' => 0, 'degraded' => 0, 'offline' => 0, 'total' => count($catalogo)];
        foreach ($catalogo as $servicio) {
            $remoto = !empty($servicio['remote_url']) ? $this->probarRemoto($servicio) : null;
            $local = $this->detectarLocal($servicio);
            $primario = $remoto ?? $local;
            $estado = (string) ($primario['status'] ?? 'offline');
            if (!isset($resumen[$estado])) {
                $estado = 'offline';
            }
            $resumen[$estado]++;

            $openapi = is_array($remoto['openapi']['json'] ?? null)
                ? $remoto['openapi']['json']
                : (is_array($local['openapi']['json'] ?? null) ? $local['openapi']['json'] : null);
            $endpoints = $openapi
                ? $this->extraerEndpoints($openapi)
                : (array) ($servicio['fallback_endpoints'] ?? []);
            $securitySchemes = $openapi ? $this->extraerEsquemasSeguridad($openapi) : [];
            $info = is_array($openapi['info'] ?? null) ? $openapi['info'] : [];
            $repositorio = $this->gitRepositorio((string) $servicio['workspace']);
            $proceso = $servicio['id'] === 'condonaciones' ? $this->procesoLocalInfo() : null;
            $diagnostico = $this->diagnosticarServicio($servicio, $estado, $remoto, $local, $repositorio, $proceso);

            $servicios[] = [
                'id' => $servicio['id'],
                'name' => $servicio['name'],
                'description' => $servicio['description'],
                'environment' => !empty($servicio['remote_url']) ? 'Cloud Run' : 'Workspace local',
                'status' => $estado,
                'status_label' => $estado === 'stable' ? 'Estable' : ($estado === 'degraded' ? 'Revisar' : 'Sin conexion'),
                'remote' => $remoto ? $this->limpiarPrueba($remoto) : null,
                'localhost' => $this->limpiarPrueba($local),
                'docs_url' => (string) (($remoto['docs_url'] ?? '') ?: ($local['docs_url'] ?? '')),
                'api_title' => (string) ($info['title'] ?? $servicio['expected_title']),
                'api_version' => (string) ($info['version'] ?? 'No disponible'),
                'endpoint_count' => count($endpoints),
                'endpoints' => $endpoints,
                'security_schemes' => $securitySchemes,
                'endpoint_signature' => hash('sha256', json_encode($endpoints, JSON_UNESCAPED_SLASHES) ?: ''),
                'modifications' => $repositorio['commits'],
                'repository' => $repositorio,
                'process' => $proceso,
                'diagnostic' => $diagnostico,
                'test_note' => 'Prueba GET de /health y /openapi.json ejecutada desde el servidor de Sparta.',
            ];
        }

        $telemetria = $this->registrarTelemetria($servicios);
        foreach ($servicios as &$servicio) {
            $detalle = $telemetria['by_service'][$servicio['id']] ?? [];
            $servicio['history'] = $detalle['history'] ?? [];
            $servicio['availability_24h'] = $detalle['availability_24h'] ?? null;
            $servicio['average_latency_24h'] = $detalle['average_latency_24h'] ?? null;
            $servicio['samples_24h'] = $detalle['samples_24h'] ?? 0;
            $servicio['last_available_at'] = $detalle['last_available_at'] ?? null;
            $servicio['last_outage_at'] = $detalle['last_outage_at'] ?? null;
            $incidentesServicio = array_values(array_filter(
                (array) ($telemetria['incidents'] ?? []),
                static fn($incidente): bool => is_array($incidente)
                    && (string) ($incidente['service'] ?? '') === (string) ($servicio['id'] ?? '')
            ));
            $servicio['incidents'] = array_slice($incidentesServicio, 0, 20);
            $servicio['active_incident'] = null;
            foreach ($incidentesServicio as $incidente) {
                if (($incidente['status'] ?? '') === 'active') {
                    $servicio['active_incident'] = $incidente;
                    break;
                }
            }
        }
        unset($servicio);

        return [
            'success' => true,
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => $resumen,
            'metrics' => $telemetria['metrics'],
            'events' => $telemetria['events'],
            'alerts' => $telemetria['alerts'],
            'incidents' => $telemetria['incidents'],
            'notification_channels' => $this->estadoCanalesNotificacion(),
            'incident_log_url' => '/monitoreo/incidentesLog',
            'history_window' => '24h',
            'services' => $servicios,
        ];
    }

    /** @return array<string, mixed> */
    public function iniciarLocal(string $servicioId): array
    {
        return $this->conBloqueoProceso(function () use ($servicioId): array {
            return $this->iniciarLocalBloqueado($servicioId);
        });
    }

    /** @return array<string, mixed> */
    private function iniciarLocalBloqueado(string $servicioId): array
    {
        $servicioId = strtolower(trim($servicioId));
        if ($servicioId !== 'condonaciones') {
            throw new \InvalidArgumentException('Solo la API local de Consumo Condonaciones puede iniciarse desde este panel.');
        }
        $servicio = null;
        foreach ($this->catalogo() as $item) {
            if (($item['id'] ?? '') === $servicioId) {
                $servicio = $item;
                break;
            }
        }
        if (!is_array($servicio)) {
            throw new \InvalidArgumentException('Servicio no encontrado.');
        }

        $localActual = $this->detectarLocal($servicio);
        if (($localActual['status'] ?? '') === 'stable') {
            $payload = $this->obtener($servicioId);
            return [
                'success' => true,
                'already_running' => true,
                'message' => 'La API ya se encuentra activa; no se creo un proceso duplicado.',
                'service' => $payload['services'][0] ?? null,
                'terminal' => $this->obtenerTerminal(),
            ];
        }
        $process = $this->procesoLocalInfo();
        if (!empty($process['owned'])) {
            return [
                'success' => true,
                'already_running' => true,
                'starting' => true,
                'message' => 'Python ya fue iniciado desde Sparta y se esta esperando la respuesta de /health.',
                'terminal' => $this->obtenerTerminal(),
            ];
        }

        if (stripos(PHP_OS, 'WIN') !== 0) {
            return ['success' => false, 'message' => 'El arranque desde web esta configurado para el servidor Windows de Sparta.'];
        }
        $workspace = (string) ($servicio['workspace'] ?? '');
        if ($workspace === '' || !is_dir($workspace) || !is_file($workspace . DIRECTORY_SEPARATOR . 'main.py')) {
            return ['success' => false, 'message' => 'No se encontro main.py en el workspace configurado.'];
        }
        $python = $this->resolverPython($workspace);
        if ($python === '') {
            return [
                'success' => false,
                'message' => 'No se encontro Python. Cree .venv en el workspace o configure SPARTA_API_CONDONACIONES_PYTHON.',
            ];
        }

        $logsDir = $this->logsDirectorio();
        if (!is_dir($logsDir) && !@mkdir($logsDir, 0775, true) && !is_dir($logsDir)) {
            return ['success' => false, 'message' => 'No se pudo crear la carpeta temporal de logs.'];
        }
        $stamp = date('Ymd-His');
        $stdoutPath = $logsDir . DIRECTORY_SEPARATOR . 'condonaciones-' . $stamp . '.log';
        $stderrPath = $logsDir . DIRECTORY_SEPARATOR . 'condonaciones-' . $stamp . '.error.log';
        @file_put_contents(
            $stdoutPath,
            '[' . date('Y-m-d H:i:s') . "] Sparta solicito el inicio de la API.\r\n"
            . 'PS ' . $workspace . '> ' . $this->comandoTerminal($python) . "\r\n\r\n"
        );
        $launch = $this->iniciarPythonOculto($python, $workspace, $stdoutPath, $stderrPath);
        if (empty($launch['ok'])) {
            return [
                'success' => false,
                'message' => (string) ($launch['message'] ?? 'No se pudo iniciar Python.'),
                'log_tail' => $this->leerLogsArranque($stdoutPath, $stderrPath),
                'terminal' => $this->obtenerTerminal(),
            ];
        }
        $this->guardarEstadoProceso([
            'service' => $servicioId,
            'pid' => (int) ($launch['pid'] ?? 0),
            'started_at' => (string) ($launch['started_at'] ?? date(DATE_ATOM)),
            'runner_path' => (string) ($launch['runner_path'] ?? ''),
            'workspace' => $workspace,
            'stdout' => $stdoutPath,
            'stderr' => $stderrPath,
        ]);
        $this->registrarEventoManual('process_started', $servicioId, 'Python iniciado desde Sparta.', 'info');

        $local = $localActual;
        $deadline = microtime(true) + 15;
        do {
            usleep(400000);
            $this->localPortReachability = [];
            $local = $this->detectarLocal($servicio);
            if (($local['status'] ?? '') === 'stable') {
                $payload = $this->obtener($servicioId);
                return [
                    'success' => true,
                    'message' => 'API de Consumo Condonaciones iniciada correctamente.',
                    'pid' => $launch['pid'] ?? null,
                    'service' => $payload['services'][0] ?? null,
                    'log_file' => basename($stdoutPath),
                    'terminal' => $this->obtenerTerminal(),
                ];
            }
        } while (microtime(true) < $deadline);

        $tail = $this->leerLogsArranque($stdoutPath, $stderrPath);
        return [
            'success' => false,
            'message' => $tail !== ''
                ? 'Python termino o no publico /health. Revise el detalle de arranque mostrado en la tarjeta.'
                : 'Python fue lanzado, pero /health no respondio dentro de 15 segundos.',
            'pid' => $launch['pid'] ?? null,
            'log_tail' => $tail,
            'localhost' => $this->limpiarPrueba($local),
            'terminal' => $this->obtenerTerminal(),
        ];
    }

    /** @return array<string, mixed> */
    public function accionLocal(string $servicioId, string $accion): array
    {
        $servicioId = strtolower(trim($servicioId));
        $accion = strtolower(trim($accion));
        if ($servicioId !== 'condonaciones') {
            throw new \InvalidArgumentException('Solo Condonaciones admite control de proceso desde este modulo.');
        }
        if ($accion === 'iniciar') {
            return $this->iniciarLocal($servicioId);
        }
        if ($accion === 'detener') {
            return $this->detenerLocal($servicioId);
        }
        if ($accion === 'reiniciar') {
            $stop = $this->detenerLocal($servicioId);
            if (empty($stop['success'])) {
                return $stop;
            }
            usleep(600000);
            $start = $this->iniciarLocal($servicioId);
            if (!empty($start['success'])) {
                $start['message'] = 'API de Consumo Condonaciones reiniciada correctamente.';
                $this->registrarEventoManual('process_restarted', $servicioId, 'Python reiniciado desde Sparta.', 'info');
            }
            return $start;
        }
        throw new \InvalidArgumentException('Accion no permitida.');
    }

    /** @return array<string, mixed> */
    private function detenerLocal(string $servicioId): array
    {
        $estado = $this->leerEstadoProceso();
        $pid = (int) ($estado['pid'] ?? 0);
        if ($pid <= 0 || !$this->procesoEsControlable($estado)) {
            return [
                'success' => false,
                'message' => 'La API no fue iniciada por este modulo; por seguridad Sparta no puede detener ese proceso.',
            ];
        }

        $systemRoot = rtrim((string) getenv('SystemRoot'), '\\/');
        $taskkill = $systemRoot !== ''
            ? $systemRoot . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'taskkill.exe'
            : 'taskkill.exe';
        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open([$taskkill, '/PID', (string) $pid, '/T', '/F'], $descriptors, $pipes);
        if (!is_resource($process)) {
            return ['success' => false, 'message' => 'No se pudo solicitar el cierre del proceso de Python.'];
        }
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = @proc_close($process);
        if ($exit !== 0 && $this->procesoEsControlable($estado)) {
            return [
                'success' => false,
                'message' => trim($stderr) !== '' ? trim($stderr) : (trim($stdout) !== '' ? trim($stdout) : 'No se pudo detener el proceso.'),
            ];
        }

        $this->borrarEstadoProceso();
        $this->registrarEventoManual('process_stopped', $servicioId, 'Python detenido desde Sparta.', 'warning');
        return [
            'success' => true,
            'message' => 'API de Consumo Condonaciones detenida correctamente.',
            'service' => ($this->obtener($servicioId)['services'][0] ?? null),
            'terminal' => $this->obtenerTerminal(),
        ];
    }

    /** @return array<string, mixed> */
    public function obtenerLogs(string $archivo = ''): array
    {
        $dir = $this->logsDirectorio();
        $archivos = [];
        foreach (glob($dir . DIRECTORY_SEPARATOR . 'condonaciones-*.log') ?: [] as $path) {
            if (!is_file($path)) {
                continue;
            }
            $archivos[] = [
                'name' => basename($path),
                'size' => (int) (@filesize($path) ?: 0),
                'modified_at' => date(DATE_ATOM, (int) (@filemtime($path) ?: time())),
                'type' => str_ends_with(basename($path), '.error.log') ? 'error' : 'output',
            ];
        }
        usort($archivos, static fn(array $a, array $b): int => strcmp($b['modified_at'], $a['modified_at']));
        $archivos = array_slice($archivos, 0, 20);
        if ($archivo === '' && $archivos !== []) {
            $archivo = (string) $archivos[0]['name'];
        }

        $contenido = '';
        if ($archivo !== '') {
            $path = $this->resolverLogSeguro($archivo);
            if ($path === '') {
                throw new \InvalidArgumentException('Archivo de log no permitido.');
            }
            $raw = @file_get_contents($path);
            if (is_string($raw)) {
                $contenido = strlen($raw) > 40000 ? "… ultimos 40 KB …\n" . substr($raw, -40000) : $raw;
            }
        }

        return [
            'success' => true,
            'files' => $archivos,
            'selected' => $archivo,
            'content' => $contenido,
            'process' => $this->procesoLocalInfo(),
        ];
    }

    /** @return array<string, mixed> */
    public function obtenerTerminal(): array
    {
        $servicio = $this->resolverServicio('condonaciones');
        $workspace = (string) ($servicio['workspace'] ?? '');
        $python = $this->resolverPython($workspace);
        $estado = $this->leerEstadoProceso();
        $process = $this->procesoLocalInfo();
        $stdoutPath = (string) ($estado['stdout'] ?? '');
        $stderrPath = (string) ($estado['stderr'] ?? '');
        $trackedStdoutPath = $stdoutPath;

        if ($stdoutPath === '' || !is_file($stdoutPath)) {
            $candidates = array_values(array_filter(
                glob($this->logsDirectorio() . DIRECTORY_SEPARATOR . 'condonaciones-*.log') ?: [],
                static fn(string $path): bool => !str_ends_with($path, '.error.log') && is_file($path)
            ));
            usort($candidates, static fn(string $a, string $b): int => ((int) @filemtime($b)) <=> ((int) @filemtime($a)));
            $stdoutPath = (string) ($candidates[0] ?? '');
            if ($stdoutPath !== '') {
                $possibleError = preg_replace('/\.log$/', '.error.log', $stdoutPath);
                $stderrPath = is_string($possibleError) && is_file($possibleError) ? $possibleError : '';
            }
        }

        $output = $this->leerLogTail($stdoutPath, 80000);
        $stderr = $this->leerLogTail($stderrPath, 30000);
        if ($stderr !== '') {
            $output .= ($output !== '' ? "\n\n" : '') . "--- stderr ---\n" . $stderr;
        }
        $portOpen = $this->puertoLocalAbierto(8083);
        $owned = !empty($process['owned']);
        $liveOutput = $owned && $trackedStdoutPath !== '' && is_file($trackedStdoutPath);
        $status = $owned ? 'running' : ($portOpen ? 'external' : 'stopped');
        $stdoutName = $stdoutPath !== '' ? basename($stdoutPath) : '';

        return [
            'success' => true,
            'status' => $status,
            'status_label' => $status === 'running' ? 'Ejecutándose desde Sparta' : ($status === 'external' ? 'Activo externamente' : 'Detenido'),
            'running' => $owned || $portOpen,
            'owned' => $owned,
            'live_output' => $liveOutput,
            'pid' => $process['pid'] ?? null,
            'workspace' => $workspace,
            'python' => $python,
            'command' => $python !== '' ? $this->comandoTerminal($python) : 'python main.py',
            'prompt' => 'PS ' . $workspace . '> ',
            'docs_url' => 'http://localhost:8083/docs',
            'health_url' => 'http://127.0.0.1:8083/health',
            'log_file' => $stdoutName,
            'log_size' => $stdoutPath !== '' && is_file($stdoutPath) ? (int) (@filesize($stdoutPath) ?: 0) : 0,
            'log_modified_at' => $stdoutPath !== '' && is_file($stdoutPath) ? date(DATE_ATOM, (int) (@filemtime($stdoutPath) ?: time())) : null,
            'download_url' => $stdoutName !== '' ? '/monitoreo/log?archivo=' . rawurlencode($stdoutName) : '',
            'output' => $output,
            'updated_at' => date(DATE_ATOM),
        ];
    }

    /** @return array{path:string,name:string} */
    public function obtenerLogArchivo(string $archivo): array
    {
        $path = $this->resolverLogSeguro($archivo);
        if ($path === '') {
            throw new \InvalidArgumentException('Archivo de log no permitido.');
        }
        return ['path' => $path, 'name' => basename($path)];
    }

    /** @return array{path:string,name:string} */
    public function obtenerLogIncidentes(): array
    {
        $path = $this->storagePath('sparta-monitoreo-incidentes.log');
        if (!is_file($path)) {
            $header = "SPARTA LEDGER - REGISTRO DE INCIDENTES DE SERVICIOS\n"
                . 'Generado: ' . date(DATE_ATOM) . "\n"
                . "Este archivo no contiene tokens ni credenciales.\n"
                . str_repeat('=', 72) . "\n\n";
            if (@file_put_contents($path, $header, LOCK_EX) === false) {
                throw new \RuntimeException('No se pudo preparar el log de incidentes.');
            }
        }
        return ['path' => $path, 'name' => 'sparta-monitoreo-incidentes.log'];
    }

    /** @return array<string, mixed> */
    public function probarEndpoint(
        string $servicioId,
        string $metodo,
        string $path,
        array $query = [],
        ?array $body = null,
        bool $confirmarMutacion = false,
        array $auth = []
    ): array {
        $servicio = $this->resolverServicio($servicioId);
        $metodo = strtoupper(trim($metodo));
        if (!in_array($metodo, ['GET', 'POST', 'PUT', 'PATCH'], true)) {
            throw new \InvalidArgumentException('Metodo no permitido en el probador seguro.');
        }
        if ($metodo !== 'GET' && !$confirmarMutacion) {
            throw new \InvalidArgumentException('Confirme expresamente la ejecucion de una solicitud que puede modificar datos.');
        }
        $path = trim($path);
        if ($path === '' || $path[0] !== '/' || str_contains($path, '..') || str_contains($path, '\\') || preg_match('#^//|[\r\n]#', $path)) {
            throw new \InvalidArgumentException('Ruta de endpoint invalida.');
        }
        if (str_contains($path, '?') || str_contains($path, '#') || preg_match('/\{[^}]+\}/', $path)) {
            throw new \InvalidArgumentException('Use una ruta concreta y capture el query en su campo correspondiente.');
        }

        $remoteBase = rtrim((string) ($servicio['remote_url'] ?? ''), '/');
        $local = $this->detectarLocal($servicio);
        $base = $remoteBase !== '' ? $remoteBase : rtrim((string) ($local['base_url'] ?? ''), '/');
        if ($base === '' || ($remoteBase === '' && ($local['status'] ?? 'offline') === 'offline')) {
            throw new \RuntimeException('El servicio no esta disponible para ejecutar la prueba.');
        }

        $openapiCheck = $this->httpJson($base . '/openapi.json', 4000);
        $openapi = is_array($openapiCheck['json'] ?? null) ? $openapiCheck['json'] : null;
        $endpoints = $openapi ? $this->extraerEndpoints($openapi) : (array) ($servicio['fallback_endpoints'] ?? []);
        $endpointPermitido = null;
        foreach ($endpoints as $endpoint) {
            if (strtoupper((string) ($endpoint['method'] ?? '')) !== $metodo) {
                continue;
            }
            if ($this->rutaCoincidePlantilla($path, (string) ($endpoint['path'] ?? ''))) {
                $endpointPermitido = $endpoint;
                break;
            }
        }
        if (!is_array($endpointPermitido)) {
            throw new \InvalidArgumentException('La ruta y el metodo no pertenecen al OpenAPI autorizado del servicio.');
        }

        $querySegura = [];
        foreach ($query as $key => $value) {
            if (!is_string($key) || !preg_match('/^[A-Za-z0-9_.-]{1,80}$/', $key) || (!is_scalar($value) && $value !== null)) {
                throw new \InvalidArgumentException('El query contiene un parametro no permitido.');
            }
            $querySegura[$key] = $value;
        }
        $url = $base . $path . ($querySegura !== [] ? '?' . http_build_query($querySegura) : '');
        if (strlen($url) > 2048) {
            throw new \InvalidArgumentException('La URL de prueba es demasiado larga.');
        }
        $bodyJson = $body !== null ? json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        if (!is_string($bodyJson) || strlen($bodyJson) > 100000) {
            throw new \InvalidArgumentException('El body JSON excede el limite de 100 KB.');
        }

        $ch = @curl_init($url);
        if (!$ch) {
            throw new \RuntimeException('No se pudo inicializar cURL.');
        }
        $authResult = $this->construirAutenticacionPrueba($auth, $endpointPermitido, $openapi ?? []);
        $headers = array_merge(['Accept: application/json'], $authResult['headers']);
        if ($bodyJson !== '') {
            $headers[] = 'Content-Type: application/json';
        }
        @curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT_MS => 12000,
            CURLOPT_CONNECTTIMEOUT_MS => 2500,
            CURLOPT_CUSTOMREQUEST => $metodo,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'SpartaLedger/MonitoreoEndpointTester',
        ]);
        if ($bodyJson !== '') {
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
        }
        $started = microtime(true);
        $raw = @curl_exec($ch);
        $latency = (int) round((microtime(true) - $started) * 1000);
        $status = (int) @curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $contentType = (string) @curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = trim((string) @curl_error($ch));
        $errno = (int) @curl_errno($ch);
        @curl_close($ch);
        $raw = is_string($raw) ? $raw : '';
        $truncated = strlen($raw) > 200000;
        if ($truncated) {
            $raw = substr($raw, 0, 200000);
        }
        $decoded = $raw !== '' ? json_decode($raw, true) : null;
        $response = is_array($decoded) ? $decoded : $raw;
        $okHttp = $errno === 0 && $status >= 200 && $status < 400;
        if ($errno !== 0 || $status === 0) {
            $connectionStatus = 'network_error';
            $connectionMessage = $error !== '' ? $error : 'El servicio no respondio.';
        } elseif (in_array($status, [401, 403], true)) {
            $connectionStatus = 'auth_failed';
            $connectionMessage = 'El servicio respondio, pero rechazo las credenciales.';
        } elseif ($okHttp) {
            $connectionStatus = 'connected';
            $connectionMessage = 'Conexion y credenciales aceptadas por el endpoint.';
        } else {
            $connectionStatus = 'http_error';
            $connectionMessage = 'El servicio respondio HTTP ' . $status . '; revise los parametros enviados.';
        }
        $this->registrarEventoManual(
            'endpoint_tested',
            (string) $servicio['id'],
            $metodo . ' ' . $path . ' respondio HTTP ' . ($status ?: 'sin respuesta') . ' en ' . $latency . ' ms.',
            $okHttp ? 'info' : 'warning'
        );

        return [
            'success' => true,
            'ok_http' => $okHttp,
            'request' => ['service' => $servicio['id'], 'method' => $metodo, 'url' => $url],
            'auth_applied' => $authResult['applied'],
            'auth_mode' => $authResult['mode'],
            'connection_status' => $connectionStatus,
            'connection_message' => $connectionMessage,
            'status' => $status ?: null,
            'latency_ms' => $latency,
            'content_type' => $contentType,
            'error' => $error,
            'truncated' => $truncated,
            'response' => $response,
        ];
    }

    /**
     * Construye exclusivamente headers declarados por OpenAPI. El secreto se usa
     * en memoria durante esta solicitud y nunca forma parte de la respuesta o log.
     *
     * @return array{headers:list<string>,applied:bool,mode:string}
     */
    private function construirAutenticacionPrueba(array $auth, array $endpoint, array $openapi): array
    {
        $mode = strtolower(trim((string) ($auth['mode'] ?? 'none')));
        if ($mode === '' || $mode === 'none') {
            return ['headers' => [], 'applied' => false, 'mode' => 'none'];
        }
        if (!in_array($mode, ['bearer', 'api_key', 'http'], true)) {
            throw new \InvalidArgumentException('Modo de autenticacion no permitido.');
        }

        $token = trim((string) ($auth['token'] ?? ''));
        if ($token === '' || strlen($token) > 4096 || preg_match('/[\r\n\x00]/', $token)) {
            throw new \InvalidArgumentException('Capture un token valido de hasta 4096 caracteres.');
        }

        $allowedHeaders = [];
        $endpointSecurity = array_fill_keys((array) ($endpoint['security'] ?? []), true);
        foreach ((array) ($endpoint['parameters'] ?? []) as $parameter) {
            if (!is_array($parameter) || strtolower((string) ($parameter['in'] ?? '')) !== 'header') {
                continue;
            }
            $name = trim((string) ($parameter['name'] ?? ''));
            if ($name !== '' && preg_match('/^[A-Za-z0-9-]{1,80}$/', $name)) {
                $allowedHeaders[strtolower($name)] = $name;
            }
        }
        foreach ((array) ($openapi['components']['securitySchemes'] ?? []) as $schemeKey => $scheme) {
            if (!is_array($scheme) || !isset($endpointSecurity[(string) $schemeKey])) {
                continue;
            }
            $schemeType = strtolower((string) ($scheme['type'] ?? ''));
            if ($schemeType === 'http' && strtolower((string) ($scheme['scheme'] ?? '')) === 'bearer') {
                $allowedHeaders['authorization'] = 'Authorization';
                continue;
            }
            if ($schemeType !== 'apikey' || strtolower((string) ($scheme['in'] ?? '')) !== 'header') {
                continue;
            }
            $name = trim((string) ($scheme['name'] ?? ''));
            if ($name !== '' && preg_match('/^[A-Za-z0-9-]{1,80}$/', $name)) {
                $allowedHeaders[strtolower($name)] = $name;
            }
        }

        if ($mode === 'bearer') {
            if (!isset($allowedHeaders['authorization'])) {
                throw new \InvalidArgumentException('El endpoint no declara autenticacion Bearer en OpenAPI.');
            }
            $token = preg_replace('/^Bearer\s+/i', '', $token) ?? $token;
            return [
                'headers' => ['Authorization: Bearer ' . $token],
                'applied' => true,
                'mode' => 'bearer',
            ];
        }

        $requestedHeader = trim((string) ($auth['header'] ?? ($mode === 'api_key' ? 'X-API-Key' : 'Authorization')));
        if (!preg_match('/^[A-Za-z0-9-]{1,80}$/', $requestedHeader)) {
            throw new \InvalidArgumentException('Nombre de header de autenticacion invalido.');
        }
        $headerKey = strtolower($requestedHeader);
        if (!isset($allowedHeaders[$headerKey])) {
            throw new \InvalidArgumentException('El header de autenticacion no esta declarado por el OpenAPI seleccionado.');
        }

        $prefix = $mode === 'http' ? trim((string) ($auth['prefix'] ?? '')) : '';
        if ($prefix !== '' && !preg_match('/^[A-Za-z0-9._-]{1,30}$/', $prefix)) {
            throw new \InvalidArgumentException('Prefijo HTTP invalido.');
        }
        $value = $prefix !== '' ? $prefix . ' ' . $token : $token;
        return [
            'headers' => [$allowedHeaders[$headerKey] . ': ' . $value],
            'applied' => true,
            'mode' => $mode,
        ];
    }

    /** @return array<string, mixed> */
    private function resolverServicio(string $servicioId): array
    {
        $servicioId = strtolower(trim($servicioId));
        foreach ($this->catalogo() as $servicio) {
            if (($servicio['id'] ?? '') === $servicioId) {
                return $servicio;
            }
        }
        throw new \InvalidArgumentException('Servicio no encontrado.');
    }

    private function rutaCoincidePlantilla(string $ruta, string $plantilla): bool
    {
        $quoted = preg_quote($plantilla, '#');
        $pattern = preg_replace('/\\\\\{[^}]+\\\\\}/', '[^/?]+', $quoted);
        return is_string($pattern) && preg_match('#^' . $pattern . '$#', $ruta) === 1;
    }

    private function resolverPython(string $workspace): string
    {
        $configured = trim((string) (getenv('SPARTA_API_CONDONACIONES_PYTHON') ?: ''));
        $candidates = [
            $configured,
            $workspace . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe',
            $workspace . DIRECTORY_SEPARATOR . 'venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe',
            'C:\\Python312\\python.exe',
            'C:\\Python311\\python.exe',
        ];
        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }
        return '';
    }

    private function comandoTerminal(string $python): string
    {
        return '& "' . str_replace('"', '`"', $python) . '" main.py';
    }

    private function leerLogTail(string $path, int $maxBytes): string
    {
        if ($path === '' || !is_file($path) || $maxBytes <= 0) {
            return '';
        }
        $size = (int) (@filesize($path) ?: 0);
        $handle = @fopen($path, 'rb');
        if (!is_resource($handle)) {
            return '';
        }
        $truncated = $size > $maxBytes;
        if ($truncated) {
            @fseek($handle, -$maxBytes, SEEK_END);
        }
        $content = stream_get_contents($handle);
        fclose($handle);
        if (!is_string($content)) {
            return '';
        }
        if (preg_match('//u', $content) !== 1) {
            $clean = function_exists('iconv') ? @iconv('UTF-8', 'UTF-8//IGNORE', $content) : false;
            $content = is_string($clean) ? $clean : preg_replace('/[\x80-\xFF]/', '?', $content);
            $content = is_string($content) ? $content : '';
        }
        return ($truncated ? "… salida anterior omitida …\n" : '') . $content;
    }

    /** @return array{ok:bool,pid?:int,message?:string} */
    private function iniciarPythonOculto(string $python, string $workspace, string $stdoutPath, string $stderrPath): array
    {
        $quote = static function (string $value): string {
            return "'" . str_replace("'", "''", $value) . "'";
        };
        $runnerPath = dirname($stdoutPath) . DIRECTORY_SEPARATOR . 'condonaciones-runner-' . date('Ymd-His') . '-' . getmypid() . '.cmd';
        $runner = "@echo off\r\n"
            . 'cd /d "' . str_replace('"', '""', $workspace) . '"' . "\r\n"
            . 'set PYTHONUNBUFFERED=1' . "\r\n"
            . 'set PYTHONUTF8=1' . "\r\n"
            . 'set PYTHONIOENCODING=utf-8' . "\r\n"
            . '"' . str_replace('"', '""', $python) . '" main.py 1>>"'
            . str_replace('"', '""', $stdoutPath) . '" 2>&1' . "\r\n"
            . 'del "%~f0" >NUL 2>NUL' . "\r\n";
        if (@file_put_contents($runnerPath, $runner) === false) {
            return ['ok' => false, 'message' => 'No se pudo crear el runner temporal de la API.'];
        }
        $script = '$ErrorActionPreference = "Stop"' . "\r\n"
            . '$p = Start-Process -FilePath ' . $quote('cmd.exe')
            . ' -ArgumentList @(' . $quote('/d') . ', ' . $quote('/c') . ', ' . $quote($runnerPath) . ')'
            . ' -WorkingDirectory ' . $quote($workspace)
            . ' -WindowStyle Hidden -PassThru' . "\r\n"
            . 'Write-Output ("PID:" + $p.Id)' . "\r\n"
            . 'Write-Output ("START:" + $p.StartTime.ToUniversalTime().ToString("o"))' . "\r\n";
        if (function_exists('mb_convert_encoding')) {
            $encoded = base64_encode(mb_convert_encoding($script, 'UTF-16LE', 'UTF-8'));
        } elseif (function_exists('iconv')) {
            $encoded = base64_encode((string) iconv('UTF-8', 'UTF-16LE', $script));
        } else {
            return ['ok' => false, 'message' => 'No se pudo codificar la orden de PowerShell.'];
        }
        $systemRoot = rtrim((string) getenv('SystemRoot'), '\\/');
        $powershell = $systemRoot !== ''
            ? $systemRoot . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'WindowsPowerShell' . DIRECTORY_SEPARATOR . 'v1.0' . DIRECTORY_SEPARATOR . 'powershell.exe'
            : 'powershell.exe';
        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open([
            $powershell,
            '-NoProfile',
            '-NonInteractive',
            '-ExecutionPolicy',
            'Bypass',
            '-EncodedCommand',
            $encoded,
        ], $descriptors, $pipes);
        if (!is_resource($process)) {
            return ['ok' => false, 'message' => 'No se pudo abrir PowerShell para iniciar la API.'];
        }
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = @proc_close($process);
        if ($exit !== 0 || !preg_match('/PID:(\d+)/', $stdout, $match)) {
            return [
                'ok' => false,
                'message' => trim($stderr) !== '' ? trim($stderr) : 'PowerShell no confirmo el proceso de Python.',
            ];
        }
        preg_match('/START:([^\r\n]+)/', $stdout, $startMatch);
        return [
            'ok' => true,
            'pid' => (int) $match[1],
            'started_at' => trim((string) ($startMatch[1] ?? date(DATE_ATOM))),
            'runner_path' => $runnerPath,
        ];
    }

    private function leerLogsArranque(string $stdoutPath, string $stderrPath): string
    {
        $parts = [];
        foreach ([$stderrPath, $stdoutPath] as $path) {
            if (!is_file($path)) {
                continue;
            }
            $content = @file_get_contents($path);
            if (!is_string($content) || trim($content) === '') {
                continue;
            }
            $parts[] = trim(substr($content, -6000));
        }
        return trim(implode("\n", $parts));
    }

    /** @return list<array<string, mixed>> */
    private function catalogo(): array
    {
        $htdocs = dirname(__DIR__, 4);
        $workspaceMotos = trim((string) (getenv('SPARTA_API_MOTOS_WORKSPACE') ?: ''));
        $workspaceComercial = trim((string) (getenv('SPARTA_API_COMERCIAL_WORKSPACE') ?: ''));
        $workspaceCondonaciones = trim((string) (getenv('SPARTA_API_CONDONACIONES_WORKSPACE') ?: ''));

        return [
            [
                'id' => 'adjudicaciones',
                'name' => 'API POST Adjudicaciones Servicio1',
                'description' => 'Adjudicaciones, tracking, almacen, evidencias y notificaciones de motocicletas.',
                'expected_title' => 'API POST Adjudicaciones Servicio1',
                'remote_url' => 'https://motosadjudicadas-601258367060.us-central1.run.app',
                'local_ports' => [8082, 8080],
                'workspace' => $workspaceMotos !== '' ? $workspaceMotos : $htdocs . DIRECTORY_SEPARATOR . 'API-MotosAdjudicadas-BaaS' . DIRECTORY_SEPARATOR . 'API-MotosAdjudicadas',
                'fallback_endpoints' => [
                    ['method' => 'GET', 'path' => '/health', 'summary' => 'Estado del servicio'],
                ],
            ],
            [
                'id' => 'comercial',
                'name' => 'API Comercial',
                'description' => 'Operacion comercial Atlas: creditos, rutas, visitas, expedientes y reporteria.',
                'expected_title' => 'API Comercial',
                'remote_url' => 'https://api-comercial-601258367060.us-central1.run.app',
                'local_ports' => [8000, 8080],
                'workspace' => $workspaceComercial !== '' ? $workspaceComercial : $htdocs . DIRECTORY_SEPARATOR . 'API-Atlas Comercial' . DIRECTORY_SEPARATOR . 'API-COMERCIAL',
                'fallback_endpoints' => [
                    ['method' => 'GET', 'path' => '/health', 'summary' => 'Estado del servicio'],
                ],
            ],
            [
                'id' => 'condonaciones',
                'name' => 'API GET Consumo Condonaciones',
                'description' => 'Consulta condonaciones, estado de cuenta, variables ATC y documentacion por credito.',
                'expected_title' => 'API Condonaciones Sparta Ledger',
                'remote_url' => '',
                'local_ports' => [8000, 8083, 8080],
                'workspace' => $workspaceCondonaciones !== '' ? $workspaceCondonaciones : $htdocs . DIRECTORY_SEPARATOR . 'API-ConsumoCondonaciones-python' . DIRECTORY_SEPARATOR . 'API-GET-ConsumoCondonaciones',
                'fallback_endpoints' => [
                    ['method' => 'GET', 'path' => '/', 'summary' => 'Informacion de la API'],
                    ['method' => 'GET', 'path' => '/health', 'summary' => 'Estado del servicio'],
                    ['method' => 'GET', 'path' => '/api/condonaciones/{id_credito}', 'summary' => 'Consulta completa de condonaciones'],
                    ['method' => 'GET', 'path' => '/api/condonaciones/{id_credito}/solo-condonados', 'summary' => 'Movimientos condonados'],
                    ['method' => 'GET', 'path' => '/api/condonaciones/{id_credito}/pendientes', 'summary' => 'Movimientos pendientes'],
                    ['method' => 'GET', 'path' => '/api/condonaciones/{id_credito}/general', 'summary' => 'Resumen general'],
                    ['method' => 'GET', 'path' => '/api/condonaciones/{id_credito}/resumen-simple', 'summary' => 'Resumen simplificado'],
                    ['method' => 'GET', 'path' => '/api/estado-cuenta/{id_credito}', 'summary' => 'Estado de cuenta S2'],
                    ['method' => 'GET', 'path' => '/api/estado-cuenta-sparta/{id_credito}', 'summary' => 'Estado de cuenta procesado por Sparta'],
                    ['method' => 'GET', 'path' => '/api/documentacion/credito/{id_credito}', 'summary' => 'Documentacion del credito'],
                    ['method' => 'GET', 'path' => '/atc/variables', 'summary' => 'Variables de atencion al cliente'],
                ],
            ],
        ];
    }

    /** @param array<string, mixed> $servicio */
    private function probarRemoto(array $servicio): array
    {
        $base = rtrim((string) ($servicio['remote_url'] ?? ''), '/');
        $health = $this->httpJson($base . '/health', 3500);
        $openapi = $this->httpJson($base . '/openapi.json', 5000);
        $status = !empty($health['ok']) && !empty($openapi['ok'])
            ? 'stable'
            : ((!empty($health['ok']) || !empty($openapi['ok'])) ? 'degraded' : 'offline');

        return [
            'status' => $status,
            'base_url' => $base,
            'docs_url' => $base . '/docs',
            'health_url' => $base . '/health',
            'http_status' => $health['status'] ?? null,
            'latency_ms' => $health['ms'] ?? null,
            'error' => (string) (($health['error'] ?? '') ?: ($openapi['error'] ?? '')),
            'health' => $health,
            'openapi' => $openapi,
        ];
    }

    /** @param array<string, mixed> $servicio */
    private function detectarLocal(array $servicio): array
    {
        $expectedTitle = (string) ($servicio['expected_title'] ?? '');
        $ports = array_values(array_unique(array_map('intval', (array) ($servicio['local_ports'] ?? []))));
        $otros = [];
        foreach ($ports as $port) {
            if ($port <= 0) {
                continue;
            }
            if (!$this->puertoLocalAbierto($port)) {
                continue;
            }
            $base = 'http://127.0.0.1:' . $port;
            $openapi = $this->httpJson($base . '/openapi.json', 900);
            if (empty($openapi['ok']) || !is_array($openapi['json'] ?? null)) {
                continue;
            }
            $title = trim((string) ($openapi['json']['info']['title'] ?? ''));
            if ($expectedTitle !== '' && strcasecmp($title, $expectedTitle) !== 0) {
                $otros[] = 'Puerto ' . $port . ': ' . ($title !== '' ? $title : 'otra aplicacion');
                continue;
            }
            $health = $this->httpJson($base . '/health', 1200);
            $status = !empty($health['ok']) ? 'stable' : 'degraded';
            return [
                'status' => $status,
                'base_url' => $base,
                'docs_url' => 'http://localhost:' . $port . '/docs',
                'health_url' => $base . '/health',
                'port' => $port,
                'http_status' => $health['status'] ?? null,
                'latency_ms' => $health['ms'] ?? null,
                'error' => (string) ($health['error'] ?? ''),
                'health' => $health,
                'openapi' => $openapi,
                'note' => 'Proceso local identificado por su esquema OpenAPI.',
            ];
        }

        $fallbackPort = (int) ($ports[0] ?? 8000);
        return [
            'status' => 'offline',
            'base_url' => 'http://127.0.0.1:' . $fallbackPort,
            'docs_url' => 'http://localhost:' . $fallbackPort . '/docs',
            'health_url' => 'http://127.0.0.1:' . $fallbackPort . '/health',
            'port' => null,
            'http_status' => null,
            'latency_ms' => null,
            'error' => 'No se encontro la API en los puertos configurados.',
            'openapi' => ['json' => null],
            'note' => $otros === []
                ? 'Puertos probados: ' . implode(', ', $ports) . '.'
                : 'Se detectaron otros servicios: ' . implode('; ', $otros) . '.',
        ];
    }

    private function puertoLocalAbierto(int $port): bool
    {
        if (array_key_exists($port, $this->localPortReachability)) {
            return $this->localPortReachability[$port];
        }
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.18);
        $abierto = is_resource($socket);
        if ($abierto) {
            fclose($socket);
        }
        $this->localPortReachability[$port] = $abierto;
        return $abierto;
    }

    /** @return array<string, mixed> */
    private function httpJson(string $url, int $timeoutMs): array
    {
        $ch = @curl_init($url);
        if (!$ch) {
            return ['ok' => false, 'status' => null, 'ms' => null, 'errno' => null, 'error' => 'No se pudo inicializar cURL.', 'json' => null];
        }
        @curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT_MS => $timeoutMs,
            CURLOPT_CONNECTTIMEOUT_MS => min(1500, $timeoutMs),
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'SpartaLedger/ServiciosAdministrados',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $started = microtime(true);
        $body = @curl_exec($ch);
        $ms = (int) round((microtime(true) - $started) * 1000);
        $status = (int) @curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errno = (int) @curl_errno($ch);
        $curlError = trim((string) @curl_error($ch));
        @curl_close($ch);
        $json = is_string($body) && $body !== '' ? json_decode($body, true) : null;
        $ok = $errno === 0 && $status >= 200 && $status < 300;
        $error = '';
        if (!$ok) {
            $error = $curlError !== '' ? $curlError : ($status > 0 ? 'HTTP ' . $status : 'Sin respuesta HTTP');
        }
        return [
            'ok' => $ok,
            'status' => $status > 0 ? $status : null,
            'ms' => $ms,
            'errno' => $errno > 0 ? $errno : null,
            'error' => $error,
            'json' => is_array($json) ? $json : null,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function extraerEndpoints(array $openapi): array
    {
        $metodos = ['get', 'post', 'put', 'patch', 'delete'];
        $globalSecurity = is_array($openapi['security'] ?? null) ? $openapi['security'] : [];
        $out = [];
        foreach ((array) ($openapi['paths'] ?? []) as $path => $definiciones) {
            if (!is_array($definiciones)) {
                continue;
            }
            $pathParameters = $this->normalizarParametrosOpenApi((array) ($definiciones['parameters'] ?? []), $openapi);
            foreach ($metodos as $metodo) {
                if (!isset($definiciones[$metodo]) || !is_array($definiciones[$metodo])) {
                    continue;
                }
                $operation = $definiciones[$metodo];
                $parameters = [];
                foreach (array_merge($pathParameters, $this->normalizarParametrosOpenApi((array) ($operation['parameters'] ?? []), $openapi)) as $parameter) {
                    $key = strtolower((string) ($parameter['in'] ?? '')) . ':' . strtolower((string) ($parameter['name'] ?? ''));
                    $parameters[$key] = $parameter;
                }
                $requirements = array_key_exists('security', $operation) && is_array($operation['security'])
                    ? $operation['security']
                    : $globalSecurity;
                $securityNames = [];
                $securityOptional = false;
                foreach ((array) $requirements as $requirement) {
                    if (!is_array($requirement)) {
                        continue;
                    }
                    if ($requirement === []) {
                        $securityOptional = true;
                    }
                    foreach (array_keys($requirement) as $schemeName) {
                        $securityNames[] = (string) $schemeName;
                    }
                }
                $securityNames = array_values(array_unique(array_filter($securityNames)));
                $out[] = [
                    'method' => strtoupper($metodo),
                    'path' => (string) $path,
                    'summary' => trim((string) ($operation['summary'] ?? '')),
                    'description' => trim((string) ($operation['description'] ?? '')),
                    'parameters' => array_values($parameters),
                    'request_body' => $this->extraerRequestBodyOpenApi($operation, $openapi),
                    'security' => $securityNames,
                    'security_required' => $securityNames !== [] && !$securityOptional,
                ];
            }
        }
        usort($out, static function (array $a, array $b): int {
            return [$a['path'], $a['method']] <=> [$b['path'], $b['method']];
        });
        return $out;
    }

    /** @return list<array<string, mixed>> */
    private function normalizarParametrosOpenApi(array $parameters, array $openapi): array
    {
        $out = [];
        foreach ($parameters as $parameter) {
            if (!is_array($parameter)) {
                continue;
            }
            $parameter = $this->resolverRefOpenApi($parameter, $openapi);
            $name = trim((string) ($parameter['name'] ?? ''));
            $location = strtolower(trim((string) ($parameter['in'] ?? '')));
            if ($name === '' || !in_array($location, ['path', 'query', 'header'], true)) {
                continue;
            }
            $schema = is_array($parameter['schema'] ?? null)
                ? $this->resolverSchemaPrincipal($parameter['schema'], $openapi)
                : [];
            $example = $parameter['example'] ?? $schema['example'] ?? $schema['default'] ?? null;
            $enum = is_array($schema['enum'] ?? null) ? array_values(array_slice($schema['enum'], 0, 50)) : [];
            $lowerName = strtolower($name);
            $out[] = [
                'name' => $name,
                'in' => $location,
                'required' => $location === 'path' || !empty($parameter['required']),
                'description' => trim((string) ($parameter['description'] ?? $schema['description'] ?? '')),
                'type' => $this->tipoSchemaOpenApi($schema),
                'format' => trim((string) ($schema['format'] ?? '')),
                'default' => $schema['default'] ?? null,
                'example' => $example,
                'enum' => $enum,
                'minimum' => $schema['minimum'] ?? null,
                'maximum' => $schema['maximum'] ?? null,
                'auth_parameter' => $location === 'header' && in_array($lowerName, ['authorization', 'x-api-key', 'api-key', 'apikey'], true),
            ];
        }
        return $out;
    }

    /** @return array<string, mixed>|null */
    private function extraerRequestBodyOpenApi(array $operation, array $openapi): ?array
    {
        if (!is_array($operation['requestBody'] ?? null)) {
            return null;
        }
        $requestBody = $this->resolverRefOpenApi($operation['requestBody'], $openapi);
        $content = is_array($requestBody['content'] ?? null) ? $requestBody['content'] : [];
        if ($content === []) {
            return null;
        }
        $contentType = isset($content['application/json']) ? 'application/json' : (string) array_key_first($content);
        $media = is_array($content[$contentType] ?? null) ? $content[$contentType] : [];
        $schema = is_array($media['schema'] ?? null) ? $media['schema'] : [];
        $example = $media['example'] ?? null;
        if ($example === null && is_array($media['examples'] ?? null)) {
            foreach ($media['examples'] as $candidate) {
                if (is_array($candidate) && array_key_exists('value', $candidate)) {
                    $example = $candidate['value'];
                    break;
                }
            }
        }
        if ($example === null) {
            $example = $this->generarEjemploSchemaOpenApi($schema, $openapi);
        }
        return [
            'required' => !empty($requestBody['required']),
            'content_type' => $contentType,
            'description' => trim((string) ($requestBody['description'] ?? '')),
            'example' => $example,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function extraerEsquemasSeguridad(array $openapi): array
    {
        $out = [];
        foreach ((array) ($openapi['components']['securitySchemes'] ?? []) as $key => $rawScheme) {
            if (!is_array($rawScheme)) {
                continue;
            }
            $scheme = $this->resolverRefOpenApi($rawScheme, $openapi);
            $type = strtolower(trim((string) ($scheme['type'] ?? '')));
            if (!in_array($type, ['http', 'apikey'], true)) {
                continue;
            }
            $httpScheme = strtolower(trim((string) ($scheme['scheme'] ?? '')));
            $header = $type === 'apikey' && strtolower((string) ($scheme['in'] ?? '')) === 'header'
                ? trim((string) ($scheme['name'] ?? 'X-API-Key'))
                : ($type === 'http' ? 'Authorization' : '');
            $out[] = [
                'key' => (string) $key,
                'type' => $type,
                'scheme' => $httpScheme,
                'header' => $header,
                'bearer_format' => trim((string) ($scheme['bearerFormat'] ?? '')),
                'description' => trim((string) ($scheme['description'] ?? '')),
            ];
        }
        return $out;
    }

    /** @return array<string, mixed> */
    private function resolverRefOpenApi(array $value, array $openapi): array
    {
        $ref = (string) ($value['$ref'] ?? '');
        if ($ref === '' || !str_starts_with($ref, '#/')) {
            return $value;
        }
        $resolved = $openapi;
        foreach (explode('/', substr($ref, 2)) as $part) {
            $part = str_replace(['~1', '~0'], ['/', '~'], $part);
            if (!is_array($resolved) || !array_key_exists($part, $resolved)) {
                return $value;
            }
            $resolved = $resolved[$part];
        }
        if (!is_array($resolved)) {
            return $value;
        }
        unset($value['$ref']);
        return array_replace($resolved, $value);
    }

    /** @return array<string, mixed> */
    private function resolverSchemaPrincipal(array $schema, array $openapi): array
    {
        $schema = $this->resolverRefOpenApi($schema, $openapi);
        foreach (['anyOf', 'oneOf', 'allOf'] as $combinator) {
            if (!is_array($schema[$combinator] ?? null)) {
                continue;
            }
            foreach ($schema[$combinator] as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }
                $candidate = $this->resolverRefOpenApi($candidate, $openapi);
                if (($candidate['type'] ?? '') !== 'null') {
                    return array_replace($schema, $candidate);
                }
            }
        }
        return $schema;
    }

    private function tipoSchemaOpenApi(array $schema): string
    {
        $type = strtolower(trim((string) ($schema['type'] ?? '')));
        if ($type !== '') {
            return $type;
        }
        if (isset($schema['properties'])) {
            return 'object';
        }
        if (isset($schema['items'])) {
            return 'array';
        }
        return 'string';
    }

    /** @return mixed */
    private function generarEjemploSchemaOpenApi(array $schema, array $openapi, int $depth = 0)
    {
        if ($depth > 6) {
            return null;
        }
        $schema = $this->resolverSchemaPrincipal($schema, $openapi);
        foreach (['example', 'default'] as $key) {
            if (array_key_exists($key, $schema)) {
                return $schema[$key];
            }
        }
        if (is_array($schema['enum'] ?? null) && $schema['enum'] !== []) {
            return $schema['enum'][0];
        }
        $type = $this->tipoSchemaOpenApi($schema);
        if ($type === 'object') {
            $example = [];
            foreach (array_slice((array) ($schema['properties'] ?? []), 0, 40, true) as $name => $property) {
                if (!is_array($property) || !empty($property['readOnly'])) {
                    continue;
                }
                $example[(string) $name] = $this->generarEjemploSchemaOpenApi($property, $openapi, $depth + 1);
            }
            return $example !== [] ? $example : new \stdClass();
        }
        if ($type === 'array') {
            $items = is_array($schema['items'] ?? null) ? $schema['items'] : [];
            return [$this->generarEjemploSchemaOpenApi($items, $openapi, $depth + 1)];
        }
        if ($type === 'boolean') {
            return false;
        }
        if (in_array($type, ['integer', 'number'], true)) {
            if (isset($schema['minimum']) && is_numeric($schema['minimum'])) {
                return $type === 'integer' ? (int) $schema['minimum'] : (float) $schema['minimum'];
            }
            if (isset($schema['exclusiveMinimum']) && is_numeric($schema['exclusiveMinimum'])) {
                return $type === 'integer' ? ((int) $schema['exclusiveMinimum'] + 1) : ((float) $schema['exclusiveMinimum'] + 1);
            }
            return 0;
        }
        return match (strtolower((string) ($schema['format'] ?? ''))) {
            'date' => '2026-01-01',
            'date-time' => '2026-01-01T00:00:00Z',
            'email' => 'usuario@ejemplo.com',
            'uuid' => '00000000-0000-4000-8000-000000000000',
            default => 'string',
        };
    }

    /**
     * Convierte las pruebas técnicas en una explicación administrativa. Las
     * causas internas de Cloud Run se mantienen como probables hasta disponer
     * de Google Cloud Logging o de otro proveedor de trazas.
     *
     * @param array<string, mixed> $catalogo
     * @param array<string, mixed>|null $remoto
     * @param array<string, mixed> $local
     * @param array<string, mixed> $repositorio
     * @param array<string, mixed>|null $proceso
     * @return array<string, mixed>
     */
    private function diagnosticarServicio(
        array $catalogo,
        string $estado,
        ?array $remoto,
        array $local,
        array $repositorio,
        ?array $proceso
    ): array {
        $source = $remoto ?? $local;
        $health = is_array($source['health'] ?? null) ? $source['health'] : [];
        $openapi = is_array($source['openapi'] ?? null) ? $source['openapi'] : [];
        $healthOk = !empty($health['ok']);
        $openapiOk = !empty($openapi['ok']);
        $http = (int) (($health['status'] ?? null) ?: ($openapi['status'] ?? null) ?: 0);
        $errno = (int) (($health['errno'] ?? null) ?: ($openapi['errno'] ?? null) ?: 0);
        $latency = is_numeric($source['latency_ms'] ?? null) ? (int) $source['latency_ms'] : null;
        $error = trim((string) (($health['error'] ?? '') ?: ($openapi['error'] ?? '') ?: ($source['error'] ?? '')));
        $isLocal = empty($catalogo['remote_url']);

        $diagnostico = [
            'level' => $estado === 'offline' ? 'critical' : ($estado === 'degraded' ? 'warning' : 'ok'),
            'cause_code' => 'unknown',
            'title' => 'Estado por confirmar',
            'summary' => 'La evidencia disponible no permite aislar una causa todavía.',
            'confidence' => 'low',
            'confidence_label' => 'Confianza baja',
            'confirmed' => false,
            'evidence' => [],
            'actions' => [],
            'logs_available' => $isLocal,
            'logs_note' => $isLocal
                ? 'La terminal local puede aportar el error exacto de Python.'
                : 'Para confirmar la causa interna se requiere conectar Google Cloud Logging.',
            'observed_at' => date(DATE_ATOM),
        ];

        if ($estado === 'stable' && $latency !== null && $latency > 1500) {
            $diagnostico = array_replace($diagnostico, [
                'level' => 'warning',
                'cause_code' => 'high_latency',
                'title' => 'Servicio disponible con respuesta lenta',
                'summary' => 'La API responde, pero superó 1,500 ms en la lectura actual.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'confirmed' => true,
                'actions' => [
                    'Repetir la comprobación para descartar una demora aislada.',
                    'Revisar consultas a base de datos y servicios externos.',
                    'Comparar la hora de la demora con los últimos cambios publicados.',
                ],
            ]);
        } elseif ($estado === 'stable') {
            $diagnostico = array_replace($diagnostico, [
                'level' => 'ok',
                'cause_code' => 'operational',
                'title' => 'Operación normal',
                'summary' => 'Health check y esquema OpenAPI responden correctamente.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'confirmed' => true,
                'actions' => ['No se requiere acción; mantener la supervisión automática.'],
            ]);
        } elseif ($isLocal && $estado === 'offline') {
            $diagnostico = array_replace($diagnostico, [
                'level' => 'critical',
                'cause_code' => 'local_process_unavailable',
                'title' => 'Proceso local no disponible',
                'summary' => 'No se identificó la API de Condonaciones en los puertos configurados.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'actions' => [
                    'Abrir Localhost / Terminal e iniciar python main.py.',
                    'Confirmar que el puerto 8083 quede escuchando.',
                    'Si Python termina de inmediato, revisar las últimas líneas del log local.',
                ],
            ]);
        } elseif (in_array($http, [401, 403], true)) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'authentication_rejected',
                'title' => 'Autenticación rechazada',
                'summary' => 'El servicio respondió, pero rechazó las credenciales de la comprobación.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'confirmed' => true,
                'actions' => [
                    'Validar que el health check pueda consultarse sin token o configurar una credencial de monitoreo.',
                    'Revisar vigencia, prefijo y permisos del token.',
                ],
            ]);
        } elseif ($http === 429) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'rate_limited',
                'title' => 'Límite de solicitudes alcanzado',
                'summary' => 'El servicio respondió HTTP 429 y está rechazando solicitudes temporalmente.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'confirmed' => true,
                'actions' => [
                    'Esperar la ventana de recuperación y volver a comprobar.',
                    'Revisar límites, concurrencia y tráfico reciente del servicio.',
                ],
            ]);
        } elseif ($http >= 500) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'server_error',
                'title' => 'Error interno del servicio',
                'summary' => 'La aplicación o su infraestructura respondió HTTP ' . $http . '.',
                'confidence' => 'medium',
                'confidence_label' => 'Confianza media',
                'actions' => [
                    'Revisar el stacktrace y la revisión activa del servicio.',
                    'Comprobar base de datos, secretos y dependencias externas.',
                    'Comparar el incidente con el último despliegue; revertir sólo si se confirma relación.',
                ],
            ]);
        } elseif ($errno === 6 || stripos($error, 'resolve host') !== false) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'dns_failure',
                'title' => 'No se pudo resolver el dominio',
                'summary' => 'El servidor de Sparta no logró convertir el dominio en una dirección de red.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'actions' => [
                    'Comprobar DNS y que la URL configurada siga vigente.',
                    'Validar conectividad desde el servidor de Sparta.',
                ],
            ]);
        } elseif (in_array($errno, [35, 51, 58, 60], true) || preg_match('/ssl|tls|certificate/i', $error)) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'tls_failure',
                'title' => 'Fallo de certificado o conexión segura',
                'summary' => 'La negociación HTTPS no pudo completarse.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'actions' => [
                    'Revisar vigencia y cadena del certificado TLS.',
                    'Confirmar fecha, hora y certificados raíz del servidor de Sparta.',
                ],
            ]);
        } elseif ($errno === 28 || stripos($error, 'timed out') !== false || stripos($error, 'timeout') !== false) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'timeout',
                'title' => 'Tiempo de espera agotado',
                'summary' => 'El servicio no respondió dentro del límite configurado.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'actions' => [
                    'Reintentar para determinar si fue una demora temporal.',
                    'Revisar carga, escalado y dependencias lentas.',
                    'Consultar logs de la aplicación en la misma hora del incidente.',
                ],
            ]);
        } elseif ($errno === 7 || stripos($error, 'failed to connect') !== false || stripos($error, 'connection refused') !== false) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'connection_refused',
                'title' => 'Conexión rechazada',
                'summary' => 'El destino existe, pero no aceptó la conexión del monitor.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'actions' => [
                    'Confirmar que el contenedor o proceso esté iniciado y escuchando.',
                    'Revisar puerto, reglas de ingreso y estado de la revisión desplegada.',
                ],
            ]);
        } elseif ($estado === 'degraded' && $healthOk && !$openapiOk) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'openapi_unavailable',
                'title' => 'API activa, documentación no disponible',
                'summary' => 'El health check responde, pero /openapi.json falló.',
                'confidence' => 'high',
                'confidence_label' => 'Confianza alta',
                'confirmed' => true,
                'actions' => [
                    'Revisar la generación o publicación del esquema OpenAPI.',
                    'Confirmar que /openapi.json no requiera autenticación.',
                ],
            ]);
        } elseif ($estado === 'degraded' && !$healthOk && $openapiOk) {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'health_failed',
                'title' => 'Aplicación accesible, health check con error',
                'summary' => 'El esquema OpenAPI responde, pero /health indica una falla.',
                'confidence' => 'medium',
                'confidence_label' => 'Confianza media',
                'actions' => [
                    'Revisar qué dependencia valida el health check.',
                    'Consultar logs de base de datos y servicios externos.',
                ],
            ]);
        } else {
            $diagnostico = array_replace($diagnostico, [
                'cause_code' => 'unreachable',
                'title' => 'Servicio sin respuesta',
                'summary' => 'Health check y OpenAPI no entregaron una respuesta utilizable.',
                'confidence' => 'medium',
                'confidence_label' => 'Confianza media',
                'actions' => [
                    'Repetir la comprobación desde Sparta.',
                    'Revisar disponibilidad de la revisión y logs de la aplicación.',
                    'Confirmar red, dominio y dependencias del servicio.',
                ],
            ]);
        }

        $evidence = [];
        if ($health !== []) {
            $evidence[] = $this->resumirPruebaDiagnostico('/health', $health);
        }
        if ($openapi !== []) {
            $evidence[] = $this->resumirPruebaDiagnostico('/openapi.json', $openapi);
        }
        if ($isLocal && trim((string) ($local['note'] ?? '')) !== '') {
            $evidence[] = trim((string) $local['note']);
        }
        if ($proceso !== null) {
            $evidence[] = !empty($proceso['owned'])
                ? 'Proceso controlado por Sparta: PID ' . (int) ($proceso['pid'] ?? 0) . '.'
                : 'Proceso Python: externo, detenido o no identificado.';
        }
        if (!empty($repositorio['dirty'])) {
            $evidence[] = 'Workspace con ' . (int) ($repositorio['changed_files'] ?? 0) . ' cambios locales sin confirmar.';
        }
        $diagnostico['evidence'] = array_values(array_filter($evidence));
        return $diagnostico;
    }

    /** @param array<string, mixed> $prueba */
    private function resumirPruebaDiagnostico(string $ruta, array $prueba): string
    {
        $status = (int) ($prueba['status'] ?? 0);
        $ms = is_numeric($prueba['ms'] ?? null) ? (int) $prueba['ms'] : null;
        if (!empty($prueba['ok'])) {
            return $ruta . ': HTTP ' . $status . ($ms !== null ? ' en ' . $ms . ' ms.' : '.');
        }
        $error = trim((string) ($prueba['error'] ?? 'Sin respuesta'));
        return $ruta . ': ' . ($status > 0 ? 'HTTP ' . $status : $error) . ($ms !== null ? ' después de ' . $ms . ' ms.' : '.');
    }

    /** @return array<string, mixed> */
    private function limpiarPrueba(array $prueba): array
    {
        unset($prueba['openapi'], $prueba['health']);
        return $prueba;
    }

    /** @param list<array<string, mixed>> $servicios
     *  @return array<string, mixed>
     */
    private function registrarTelemetria(array $servicios): array
    {
        $now = time();
        $nuevosEventos = [];
        $history = $this->actualizarJson('history.json', function ($actual) use ($servicios, $now, &$nuevosEventos): array {
            $actual = is_array($actual) ? array_values($actual) : [];
            $lastByService = [];
            for ($index = count($actual) - 1; $index >= 0; $index--) {
                foreach ((array) ($actual[$index]['services'] ?? []) as $id => $sample) {
                    if (!isset($lastByService[$id]) && is_array($sample)) {
                        $lastByService[$id] = $sample;
                    }
                }
                if (count($lastByService) >= 3) {
                    break;
                }
            }

            $sampleServices = [];
            foreach ($servicios as $servicio) {
                $id = (string) ($servicio['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $remote = is_array($servicio['remote'] ?? null) ? $servicio['remote'] : null;
                $local = is_array($servicio['localhost'] ?? null) ? $servicio['localhost'] : [];
                $latency = $remote !== null ? ($remote['latency_ms'] ?? null) : ($local['latency_ms'] ?? null);
                $current = [
                    'status' => (string) ($servicio['status'] ?? 'offline'),
                    'latency_ms' => is_numeric($latency) ? (int) $latency : null,
                    'http_status' => $remote !== null ? ($remote['http_status'] ?? null) : ($local['http_status'] ?? null),
                    'endpoint_count' => (int) ($servicio['endpoint_count'] ?? 0),
                    'endpoint_signature' => (string) ($servicio['endpoint_signature'] ?? ''),
                    'error' => trim((string) (($remote['error'] ?? '') ?: ($local['error'] ?? ''))),
                    'diagnostic' => is_array($servicio['diagnostic'] ?? null) ? $servicio['diagnostic'] : [],
                ];
                $previous = $lastByService[$id] ?? null;
                if (!is_array($previous) && $current['status'] === 'offline') {
                    $nuevosEventos[] = [
                        'type' => 'status_offline',
                        'service' => $id,
                        'message' => 'Se detectó el servicio sin respuesta en la primera lectura disponible.',
                        'severity' => 'critical',
                    ];
                } elseif (is_array($previous) && ($previous['status'] ?? '') !== $current['status']) {
                    $isDown = $current['status'] === 'offline';
                    $isRecovered = ($previous['status'] ?? '') === 'offline' && $current['status'] !== 'offline';
                    $nuevosEventos[] = [
                        'type' => $isDown ? 'status_offline' : ($isRecovered ? 'status_recovered' : 'status_changed'),
                        'service' => $id,
                        'message' => $isDown
                            ? 'El servicio dejo de responder.'
                            : ($isRecovered
                                ? 'El servicio volvió a responder; estado actual: ' . $current['status'] . '.'
                                : 'El servicio cambio de ' . ($previous['status'] ?? 'desconocido') . ' a ' . $current['status'] . '.'),
                        'severity' => $isDown ? 'critical' : ($current['status'] === 'degraded' ? 'warning' : 'info'),
                    ];
                }
                if (is_array($previous)
                    && ($previous['endpoint_signature'] ?? '') !== ''
                    && $current['endpoint_signature'] !== ''
                    && ($previous['endpoint_signature'] ?? '') !== $current['endpoint_signature']) {
                    $nuevosEventos[] = [
                        'type' => 'openapi_changed',
                        'service' => $id,
                        'message' => 'El inventario OpenAPI cambio de ' . (int) ($previous['endpoint_count'] ?? 0) . ' a ' . $current['endpoint_count'] . ' endpoints.',
                        'severity' => 'warning',
                    ];
                }
                $sampleServices[$id] = $current;
            }

            $ultimo = $actual !== [] ? $actual[count($actual) - 1] : null;
            $same = is_array($ultimo) && (int) ($ultimo['timestamp'] ?? 0) > $now - 15;
            if ($same) {
                foreach ($sampleServices as $id => $sample) {
                    $before = $ultimo['services'][$id] ?? null;
                    if (!is_array($before)
                        || ($before['status'] ?? '') !== $sample['status']
                        || ($before['endpoint_signature'] ?? '') !== $sample['endpoint_signature']) {
                        $same = false;
                        break;
                    }
                }
            }
            if (!$same) {
                $actual[] = [
                    'timestamp' => $now,
                    'at' => date(DATE_ATOM, $now),
                    'services' => $sampleServices,
                ];
            }
            $minimum = $now - 172800;
            $actual = array_values(array_filter($actual, static fn($row): bool => is_array($row) && (int) ($row['timestamp'] ?? 0) >= $minimum));
            return array_slice($actual, -2000);
        }, []);

        foreach ($nuevosEventos as $evento) {
            $this->registrarEventoManual(
                (string) $evento['type'],
                (string) $evento['service'],
                (string) $evento['message'],
                (string) $evento['severity']
            );
        }

        $incidentHistory = $this->registrarIncidentes($servicios, $now);
        $events = $this->leerJson('events.json', []);
        $events = is_array($events) ? array_reverse(array_slice($events, -60)) : [];
        $windowStart = $now - 86400;
        $byService = [];
        $allSamples = 0;
        $allAvailable = 0;
        $allLatencies = [];
        foreach ($servicios as $servicio) {
            $id = (string) ($servicio['id'] ?? '');
            $rows = [];
            $available = 0;
            $latencies = [];
            $lastAvailable = null;
            $lastOutage = null;
            foreach ((array) $history as $row) {
                $timestamp = (int) ($row['timestamp'] ?? 0);
                if ($timestamp < $windowStart || !is_array($row['services'][$id] ?? null)) {
                    continue;
                }
                $sample = $row['services'][$id];
                $status = (string) ($sample['status'] ?? 'offline');
                $isAvailable = $status !== 'offline';
                if ($isAvailable) {
                    $available++;
                    $lastAvailable = $row['at'] ?? null;
                } else {
                    $lastOutage = $row['at'] ?? null;
                }
                if (is_numeric($sample['latency_ms'] ?? null)) {
                    $latencies[] = (int) $sample['latency_ms'];
                    $allLatencies[] = (int) $sample['latency_ms'];
                }
                $rows[] = [
                    'at' => (string) ($row['at'] ?? date(DATE_ATOM, $timestamp)),
                    'timestamp' => $timestamp,
                    'status' => $status,
                    'latency_ms' => $sample['latency_ms'] ?? null,
                    'http_status' => $sample['http_status'] ?? null,
                ];
            }
            $count = count($rows);
            $allSamples += $count;
            $allAvailable += $available;
            $byService[$id] = [
                'history' => array_slice($rows, -288),
                'availability_24h' => $count > 0 ? round(($available / $count) * 100, 1) : null,
                'average_latency_24h' => $latencies !== [] ? (int) round(array_sum($latencies) / count($latencies)) : null,
                'samples_24h' => $count,
                'last_available_at' => $lastAvailable,
                'last_outage_at' => $lastOutage,
            ];
        }
        $incidentCount = count(array_filter($events, static function ($event) use ($windowStart): bool {
            return is_array($event)
                && (int) ($event['timestamp'] ?? 0) >= $windowStart
                && in_array((string) ($event['type'] ?? ''), ['status_offline', 'openapi_changed'], true);
        }));
        $alerts = array_values(array_filter($events, static function ($event) use ($windowStart): bool {
            return is_array($event)
                && (int) ($event['timestamp'] ?? 0) >= $windowStart
                && in_array((string) ($event['severity'] ?? ''), ['warning', 'critical'], true);
        }));

        return [
            'by_service' => $byService,
            'metrics' => [
                'availability_24h' => $allSamples > 0 ? round(($allAvailable / $allSamples) * 100, 1) : null,
                'average_latency_24h' => $allLatencies !== [] ? (int) round(array_sum($allLatencies) / count($allLatencies)) : null,
                'samples_24h' => $allSamples,
                'incidents_24h' => $incidentCount,
                'window_label' => 'Ultimas 24 horas',
            ],
            'events' => array_slice($events, 0, 40),
            'alerts' => array_slice($alerts, 0, 12),
            'incidents' => array_slice($incidentHistory, 0, 100),
        ];
    }

    /**
     * Mantiene un ciclo de vida por caída. La escritura se protege con el
     * mismo bloqueo de los archivos JSON para evitar alertas duplicadas si dos
     * comprobaciones coinciden en el tiempo.
     *
     * @param list<array<string, mixed>> $servicios
     * @return list<array<string, mixed>>
     */
    private function registrarIncidentes(array $servicios, int $now): array
    {
        $transiciones = [];
        $incidentes = $this->actualizarJson('incidents.json', function ($actual) use ($servicios, $now, &$transiciones): array {
            $actual = is_array($actual) ? array_values(array_filter($actual, 'is_array')) : [];
            foreach ($servicios as $servicio) {
                $serviceId = trim((string) ($servicio['id'] ?? ''));
                if ($serviceId === '') {
                    continue;
                }
                $status = (string) ($servicio['status'] ?? 'offline');
                $diagnostico = is_array($servicio['diagnostic'] ?? null) ? $servicio['diagnostic'] : [];
                $activeIndex = null;
                for ($index = count($actual) - 1; $index >= 0; $index--) {
                    if (($actual[$index]['service'] ?? '') === $serviceId && ($actual[$index]['status'] ?? '') === 'active') {
                        $activeIndex = $index;
                        break;
                    }
                }

                if ($status === 'offline') {
                    if ($activeIndex === null) {
                        $incidente = [
                            'id' => 'INC-' . date('Ymd-His', $now) . '-' . strtoupper(substr(hash('sha256', $serviceId . '|' . $now . '|' . microtime(true)), 0, 6)),
                            'service' => $serviceId,
                            'service_name' => (string) ($servicio['name'] ?? $serviceId),
                            'status' => 'active',
                            'opened_timestamp' => $now,
                            'opened_at' => date(DATE_ATOM, $now),
                            'last_seen_at' => date(DATE_ATOM, $now),
                            'recovered_timestamp' => null,
                            'recovered_at' => null,
                            'duration_seconds' => null,
                            'recovery_status' => null,
                            'diagnostic' => $diagnostico,
                            'notifications' => null,
                        ];
                        $actual[] = $incidente;
                        $transiciones[] = ['kind' => 'down', 'incident' => $incidente];
                    } else {
                        $actual[$activeIndex]['last_seen_at'] = date(DATE_ATOM, $now);
                        $actual[$activeIndex]['diagnostic'] = $diagnostico;
                    }
                    continue;
                }

                if ($activeIndex !== null) {
                    $opened = (int) ($actual[$activeIndex]['opened_timestamp'] ?? $now);
                    $actual[$activeIndex]['status'] = 'resolved';
                    $actual[$activeIndex]['recovered_timestamp'] = $now;
                    $actual[$activeIndex]['recovered_at'] = date(DATE_ATOM, $now);
                    $actual[$activeIndex]['duration_seconds'] = max(0, $now - $opened);
                    $actual[$activeIndex]['recovery_status'] = $status;
                    $actual[$activeIndex]['recovery_diagnostic'] = $diagnostico;
                    $transiciones[] = ['kind' => 'recovered', 'incident' => $actual[$activeIndex]];
                }
            }

            usort($actual, static fn(array $a, array $b): int => ((int) ($b['opened_timestamp'] ?? 0)) <=> ((int) ($a['opened_timestamp'] ?? 0)));
            return array_slice($actual, 0, 100);
        }, []);

        foreach ($transiciones as $transicion) {
            $kind = (string) ($transicion['kind'] ?? 'down');
            $incidente = is_array($transicion['incident'] ?? null) ? $transicion['incident'] : [];
            $this->escribirLogIncidente($kind, $incidente);
            $entregas = $this->notificarTransicionIncidente($kind, $incidente);
            $this->guardarEntregasIncidente((string) ($incidente['id'] ?? ''), $entregas);
            $this->escribirLogEntrega((string) ($incidente['id'] ?? ''), $entregas);
        }

        $incidentes = $this->leerJson('incidents.json', $incidentes);
        return is_array($incidentes) ? array_values(array_filter($incidentes, 'is_array')) : [];
    }

    /** @param array<string, mixed> $incidente */
    private function escribirLogIncidente(string $kind, array $incidente): void
    {
        try {
            $archivo = $this->obtenerLogIncidentes();
        } catch (\Throwable $e) {
            error_log('[Monitoreo incident log] ' . $e->getMessage());
            return;
        }
        $diagnostico = is_array($incidente['diagnostic'] ?? null) ? $incidente['diagnostic'] : [];
        $lineas = [
            '[' . date('Y-m-d H:i:s P') . '] ' . ($kind === 'recovered' ? 'SERVICIO REACTIVADO' : 'CAÍDA DE SERVICIO'),
            'Incidente: ' . (string) ($incidente['id'] ?? 'Sin identificador'),
            'Servicio: ' . (string) ($incidente['service_name'] ?? $incidente['service'] ?? 'Desconocido'),
            'Estado: ' . ($kind === 'recovered' ? 'RESUELTO' : 'ACTIVO'),
        ];
        if ($kind === 'recovered') {
            $lineas[] = 'Inicio: ' . (string) ($incidente['opened_at'] ?? 'No disponible');
            $lineas[] = 'Recuperación: ' . (string) ($incidente['recovered_at'] ?? date(DATE_ATOM));
            $lineas[] = 'Duración: ' . $this->formatearDuracionIncidente((int) ($incidente['duration_seconds'] ?? 0));
            $lineas[] = 'Estado al recuperarse: ' . (string) ($incidente['recovery_status'] ?? 'stable');
        }
        $lineas[] = 'Causa probable: ' . (string) ($diagnostico['title'] ?? 'No determinada');
        $lineas[] = 'Confianza: ' . (string) ($diagnostico['confidence_label'] ?? 'No disponible');
        $lineas[] = 'Resumen: ' . (string) ($diagnostico['summary'] ?? 'Sin resumen');
        $lineas[] = 'Evidencia:';
        foreach ((array) ($diagnostico['evidence'] ?? []) as $evidencia) {
            $lineas[] = '  - ' . preg_replace('/\s+/', ' ', trim((string) $evidencia));
        }
        $lineas[] = 'Acciones sugeridas:';
        foreach ((array) ($diagnostico['actions'] ?? []) as $index => $accion) {
            $lineas[] = '  ' . ((int) $index + 1) . '. ' . preg_replace('/\s+/', ' ', trim((string) $accion));
        }
        $lineas[] = str_repeat('-', 72);
        @file_put_contents((string) $archivo['path'], implode("\n", $lineas) . "\n\n", FILE_APPEND | LOCK_EX);
    }

    /** @param array<string, mixed> $entregas */
    private function escribirLogEntrega(string $incidentId, array $entregas): void
    {
        try {
            $archivo = $this->obtenerLogIncidentes();
        } catch (\Throwable $e) {
            return;
        }
        $chat = is_array($entregas['google_chat'] ?? null) ? $entregas['google_chat'] : [];
        $email = is_array($entregas['email_fallback'] ?? null) ? $entregas['email_fallback'] : [];
        $linea = '[' . date('Y-m-d H:i:s P') . '] NOTIFICACIONES | Incidente ' . $incidentId
            . ' | Google Chat: ' . (string) ($chat['status'] ?? 'disabled')
            . ' | Correo de respaldo: ' . (string) ($email['status'] ?? 'disabled') . "\n\n";
        @file_put_contents((string) $archivo['path'], $linea, FILE_APPEND | LOCK_EX);
    }

    private function formatearDuracionIncidente(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remaining = $seconds % 60;
        return ($hours > 0 ? $hours . ' h ' : '') . ($minutes > 0 ? $minutes . ' min ' : '') . $remaining . ' s';
    }

    /** @param array<string, mixed> $incidente
     *  @return array<string, mixed>
     */
    private function notificarTransicionIncidente(string $kind, array $incidente): array
    {
        $webhook = trim((string) (getenv('SPARTA_MONITOREO_GOOGLE_CHAT_WEBHOOK') ?: ''));
        $chat = $webhook === ''
            ? ['status' => 'disabled', 'message' => 'Webhook pendiente de configurar.']
            : $this->enviarGoogleChat($webhook, $this->mensajeIncidente($kind, $incidente));
        $email = ['status' => 'standby', 'message' => 'Sólo se utiliza si falla un webhook configurado.'];

        if (($chat['status'] ?? '') === 'failed') {
            $this->registrarEventoManual(
                'notification_failed',
                (string) ($incidente['service'] ?? ''),
                'Google Chat no recibió el aviso del incidente ' . (string) ($incidente['id'] ?? '') . '.',
                'warning'
            );
            $email = $this->enviarCorreoRespaldo($kind, $incidente);
        } elseif (($chat['status'] ?? '') === 'disabled') {
            $email = ['status' => 'disabled', 'message' => 'No se intenta correo mientras Google Chat no esté configurado.'];
        }
        return ['google_chat' => $chat, 'email_fallback' => $email, 'attempted_at' => date(DATE_ATOM)];
    }

    /** @param array<string, mixed> $incidente */
    private function mensajeIncidente(string $kind, array $incidente): string
    {
        $diagnostico = is_array($incidente['diagnostic'] ?? null) ? $incidente['diagnostic'] : [];
        $titulo = $kind === 'recovered' ? '✅ SERVICIO REACTIVADO' : '🔴 CAÍDA DE SERVICIO';
        $lineas = [
            $titulo,
            'Servicio: ' . (string) ($incidente['service_name'] ?? $incidente['service'] ?? 'Desconocido'),
            'Incidente: ' . (string) ($incidente['id'] ?? 'Sin identificador'),
            'Hora: ' . date('Y-m-d H:i:s'),
        ];
        if ($kind === 'recovered') {
            $lineas[] = 'Duración: ' . $this->formatearDuracionIncidente((int) ($incidente['duration_seconds'] ?? 0));
        }
        $lineas[] = 'Causa probable: ' . (string) ($diagnostico['title'] ?? 'No determinada');
        $lineas[] = 'Detalle: ' . (string) ($diagnostico['summary'] ?? 'Sin detalle');
        $acciones = (array) ($diagnostico['actions'] ?? []);
        if ($kind !== 'recovered' && $acciones !== []) {
            $lineas[] = 'Primera acción: ' . (string) $acciones[0];
        }
        $lineas[] = 'Consulta el diagnóstico completo en Sparta Ledger > Monitoreo.';
        return implode("\n", $lineas);
    }

    /** @return array{status:string,message:string,http_status?:int} */
    private function enviarGoogleChat(string $webhook, string $message): array
    {
        $parts = parse_url($webhook);
        if (!is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || strtolower((string) ($parts['host'] ?? '')) !== 'chat.googleapis.com'
            || !str_starts_with((string) ($parts['path'] ?? ''), '/v1/spaces/')) {
            return ['status' => 'failed', 'message' => 'La URL del webhook de Google Chat no es válida.'];
        }
        $payload = json_encode(['text' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $ch = @curl_init($webhook);
        if (!$ch || !is_string($payload)) {
            return ['status' => 'failed', 'message' => 'No se pudo preparar el envío a Google Chat.'];
        }
        @curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT_MS => 6000,
            CURLOPT_CONNECTTIMEOUT_MS => 2500,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'SpartaLedger/MonitoreoAlertas',
        ]);
        @curl_exec($ch);
        $status = (int) @curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = trim((string) @curl_error($ch));
        $errno = (int) @curl_errno($ch);
        @curl_close($ch);
        if ($errno === 0 && $status >= 200 && $status < 300) {
            return ['status' => 'sent', 'message' => 'Aviso entregado a Google Chat.', 'http_status' => $status];
        }
        return [
            'status' => 'failed',
            'message' => $error !== '' ? $error : 'Google Chat respondió HTTP ' . ($status ?: 'sin respuesta') . '.',
            'http_status' => $status,
        ];
    }

    /** @param array<string, mixed> $incidente
     *  @return array{status:string,message:string}
     */
    private function enviarCorreoRespaldo(string $kind, array $incidente): array
    {
        $recipientsRaw = trim((string) (getenv('SPARTA_MONITOREO_ALERT_EMAIL_TO') ?: ''));
        $host = trim((string) (getenv('MAIL_SMTP_HOST') ?: ''));
        $user = trim((string) (getenv('MAIL_SMTP_USER') ?: ''));
        $password = (string) (getenv('MAIL_SMTP_PASS') ?: '');
        $recipients = array_values(array_filter(
            preg_split('/[;,\s]+/', $recipientsRaw) ?: [],
            static fn(string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        ));
        if ($recipients === [] || $host === '' || $user === '' || $password === '') {
            return ['status' => 'disabled', 'message' => 'Correo de respaldo pendiente de destinatarios o SMTP.'];
        }
        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $bootstrap = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap_composer.php';
            if (is_file($bootstrap)) {
                require_once $bootstrap;
                if (function_exists('sparta_require_composer_autoload')) {
                    sparta_require_composer_autoload();
                }
            }
        }
        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            return ['status' => 'failed', 'message' => 'PHPMailer no está disponible.'];
        }
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = max(1, (int) (getenv('MAIL_SMTP_PORT') ?: 465));
            $mail->Timeout = 8;
            $mail->Timelimit = 10;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $password;
            $secure = strtolower(trim((string) (getenv('MAIL_SMTP_SECURE') ?: ($mail->Port === 465 ? 'ssl' : 'tls'))));
            $mail->SMTPSecure = $secure === 'ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';
            $from = trim((string) (getenv('MAIL_SMTP_FROM') ?: getenv('MAIL_FROM') ?: $user));
            $fromName = trim((string) (getenv('MAIL_SMTP_FROM_NAME') ?: getenv('MAIL_FROM_NAME') ?: 'Sparta Monitoreo'));
            $mail->setFrom($from, $fromName);
            foreach ($recipients as $recipient) {
                $mail->addAddress($recipient);
            }
            $mail->Subject = ($kind === 'recovered' ? '[RECUPERADO] ' : '[CAÍDA] ')
                . (string) ($incidente['service_name'] ?? $incidente['service'] ?? 'Servicio web');
            $plain = $this->mensajeIncidente($kind, $incidente);
            $mail->isHTML(true);
            $mail->Body = '<pre style="font:14px/1.5 Arial,sans-serif;white-space:pre-wrap">'
                . htmlspecialchars($plain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
            $mail->AltBody = $plain;
            $incidentLog = $this->obtenerLogIncidentes();
            $mail->addAttachment($incidentLog['path'], $incidentLog['name']);
            $mail->send();
            return ['status' => 'sent', 'message' => 'Correo de respaldo enviado.'];
        } catch (\Throwable $e) {
            error_log('[Monitoreo correo respaldo] ' . $e->getMessage());
            return ['status' => 'failed', 'message' => 'No se pudo enviar el correo de respaldo.'];
        }
    }

    /** @param array<string, mixed> $entregas */
    private function guardarEntregasIncidente(string $incidentId, array $entregas): void
    {
        if ($incidentId === '') {
            return;
        }
        $this->actualizarJson('incidents.json', static function ($incidentes) use ($incidentId, $entregas): array {
            $incidentes = is_array($incidentes) ? array_values($incidentes) : [];
            foreach ($incidentes as &$incidente) {
                if (is_array($incidente) && ($incidente['id'] ?? '') === $incidentId) {
                    $incidente['notifications'] = $entregas;
                    break;
                }
            }
            unset($incidente);
            return $incidentes;
        }, []);
    }

    /** @return array<string, mixed> */
    private function estadoCanalesNotificacion(): array
    {
        $chatEnabled = trim((string) (getenv('SPARTA_MONITOREO_GOOGLE_CHAT_WEBHOOK') ?: '')) !== '';
        $emailEnabled = trim((string) (getenv('SPARTA_MONITOREO_ALERT_EMAIL_TO') ?: '')) !== ''
            && trim((string) (getenv('MAIL_SMTP_HOST') ?: '')) !== ''
            && trim((string) (getenv('MAIL_SMTP_USER') ?: '')) !== ''
            && (string) (getenv('MAIL_SMTP_PASS') ?: '') !== '';
        return [
            'google_chat' => [
                'enabled' => $chatEnabled,
                'label' => $chatEnabled ? 'Configurado' : 'Pendiente de webhook',
                'events' => ['status_offline', 'status_recovered'],
            ],
            'email_fallback' => [
                'enabled' => $emailEnabled,
                'label' => $emailEnabled ? 'Listo como respaldo' : 'Pendiente de SMTP y destinatarios',
                'only_on_webhook_failure' => true,
            ],
        ];
    }

    private function registrarEventoManual(string $type, string $service, string $message, string $severity = 'info'): void
    {
        $this->actualizarJson('events.json', static function ($events) use ($type, $service, $message, $severity): array {
            $events = is_array($events) ? array_values($events) : [];
            $now = time();
            $last = $events !== [] ? $events[count($events) - 1] : null;
            if (is_array($last)
                && ($last['type'] ?? '') === $type
                && ($last['service'] ?? '') === $service
                && ($last['message'] ?? '') === $message
                && (int) ($last['timestamp'] ?? 0) > $now - 10) {
                return $events;
            }
            $events[] = [
                'id' => substr(hash('sha256', $type . '|' . $service . '|' . $now . '|' . microtime(true)), 0, 16),
                'type' => $type,
                'service' => $service,
                'message' => $message,
                'severity' => in_array($severity, ['info', 'warning', 'critical'], true) ? $severity : 'info',
                'timestamp' => $now,
                'at' => date(DATE_ATOM, $now),
            ];
            return array_slice($events, -300);
        }, []);
    }

    /** @return array<string, mixed> */
    private function gitRepositorio(string $workspace): array
    {
        $commits = $this->gitCambios($workspace);
        $base = [
            'available' => false,
            'branch' => null,
            'dirty' => false,
            'changed_files' => 0,
            'ahead' => 0,
            'behind' => 0,
            'commits' => $commits,
        ];
        if ($workspace === '' || !is_dir($workspace) || !is_dir($workspace . DIRECTORY_SEPARATOR . '.git')) {
            return $base;
        }
        $safe = str_replace('\\', '/', $workspace);
        $result = $this->ejecutarProceso([
            'git', '-c', 'safe.directory=' . $safe, '-C', $workspace, 'status', '--porcelain=v1', '--branch', '--untracked-files=normal',
        ]);
        if (($result['exit'] ?? 1) !== 0) {
            return $base;
        }
        $lines = preg_split('/\r?\n/', trim((string) ($result['stdout'] ?? ''))) ?: [];
        $header = str_starts_with((string) ($lines[0] ?? ''), '## ') ? array_shift($lines) : '';
        $branch = trim((string) preg_replace('/^##\s+/', '', $header));
        $ahead = preg_match('/ahead (\d+)/', $header, $matchAhead) ? (int) $matchAhead[1] : 0;
        $behind = preg_match('/behind (\d+)/', $header, $matchBehind) ? (int) $matchBehind[1] : 0;
        $branch = trim((string) preg_replace('/\.\.\..*$/', '', $branch));
        $changed = count(array_filter($lines, static fn($line): bool => trim((string) $line) !== ''));
        return [
            'available' => true,
            'branch' => $branch !== '' ? $branch : null,
            'dirty' => $changed > 0,
            'changed_files' => $changed,
            'ahead' => $ahead,
            'behind' => $behind,
            'commits' => $commits,
        ];
    }

    /** @return array<string, mixed> */
    private function procesoLocalInfo(): array
    {
        $estado = $this->leerEstadoProceso();
        $owned = $this->procesoEsControlable($estado);
        $startedAt = (string) ($estado['started_at'] ?? '');
        $startedTimestamp = $startedAt !== '' ? strtotime($startedAt) : false;
        return [
            'owned' => $owned,
            'pid' => $owned ? (int) ($estado['pid'] ?? 0) : null,
            'started_at' => $owned ? $startedAt : null,
            'uptime_seconds' => $owned && $startedTimestamp !== false ? max(0, time() - $startedTimestamp) : null,
            'can_stop' => $owned,
            'can_restart' => $owned,
            'source' => $owned ? 'Sparta' : 'Externo o no identificado',
        ];
    }

    /** @param array<string, mixed> $estado */
    private function procesoEsControlable(array $estado): bool
    {
        if (stripos(PHP_OS, 'WIN') !== 0 || ($estado['service'] ?? '') !== 'condonaciones') {
            return false;
        }
        $pid = (int) ($estado['pid'] ?? 0);
        $runner = (string) ($estado['runner_path'] ?? '');
        $logsDir = str_replace('\\', '/', $this->logsDirectorio());
        $runnerNorm = str_replace('\\', '/', $runner);
        if ($pid <= 0 || $runner === '' || !is_file($runner) || !str_starts_with(strtolower($runnerNorm), strtolower($logsDir . '/'))) {
            return false;
        }
        $systemRoot = rtrim((string) getenv('SystemRoot'), '\\/');
        $tasklist = $systemRoot !== ''
            ? $systemRoot . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'tasklist.exe'
            : 'tasklist.exe';
        $result = $this->ejecutarProceso([$tasklist, '/FI', 'PID eq ' . $pid, '/FO', 'CSV', '/NH']);
        $stdout = strtolower((string) ($result['stdout'] ?? ''));
        return ($result['exit'] ?? 1) === 0
            && str_contains($stdout, '"' . $pid . '"')
            && (str_contains($stdout, 'cmd.exe') || str_contains($stdout, 'python.exe'));
    }

    /** @param array<string, mixed> $estado */
    private function guardarEstadoProceso(array $estado): void
    {
        $this->actualizarJson('process-condonaciones.json', static fn($current): array => $estado, []);
    }

    /** @return array<string, mixed> */
    private function leerEstadoProceso(): array
    {
        $estado = $this->leerJson('process-condonaciones.json', []);
        return is_array($estado) ? $estado : [];
    }

    private function borrarEstadoProceso(): void
    {
        $estado = $this->leerEstadoProceso();
        $runner = (string) ($estado['runner_path'] ?? '');
        if ($runner !== '' && is_file($runner)) {
            @unlink($runner);
        }
        $path = $this->storagePath('process-condonaciones.json');
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function logsDirectorio(): string
    {
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta_servicios_administrados';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private function resolverLogSeguro(string $archivo): string
    {
        $archivo = basename(trim($archivo));
        if (!preg_match('/^condonaciones-\d{8}-\d{6}(?:\.error)?\.log$/', $archivo)) {
            return '';
        }
        $path = $this->logsDirectorio() . DIRECTORY_SEPARATOR . $archivo;
        $real = realpath($path);
        $dir = realpath($this->logsDirectorio());
        if ($real === false || $dir === false || !is_file($real)) {
            return '';
        }
        $realNorm = strtolower(str_replace('\\', '/', $real));
        $dirNorm = strtolower(rtrim(str_replace('\\', '/', $dir), '/'));
        return str_starts_with($realNorm, $dirNorm . '/') ? $real : '';
    }

    private function storagePath(string $file): string
    {
        $configured = trim((string) (getenv('SPARTA_MONITOREO_STORAGE') ?: ''));
        $dir = $configured !== ''
            ? $configured
            : rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta_monitoreo';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($file);
    }

    /** @template T
     *  @param callable():T $callback
     *  @return T
     */
    private function conBloqueoProceso(callable $callback)
    {
        $handle = @fopen($this->storagePath('process-condonaciones.lock'), 'c+');
        if (!is_resource($handle)) {
            return $callback();
        }
        if (!@flock($handle, LOCK_EX)) {
            fclose($handle);
            return $callback();
        }
        try {
            return $callback();
        } finally {
            @flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @return mixed */
    private function leerJson(string $file, $default)
    {
        $path = $this->storagePath($file);
        if (!is_file($path)) {
            return $default;
        }
        $raw = @file_get_contents($path);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        return $decoded !== null ? $decoded : $default;
    }

    /** @param callable(mixed):mixed $mutator
     *  @return mixed
     */
    private function actualizarJson(string $file, callable $mutator, $default)
    {
        $path = $this->storagePath($file);
        $handle = @fopen($path, 'c+');
        if (!is_resource($handle)) {
            return $mutator($default);
        }
        @flock($handle, LOCK_EX);
        rewind($handle);
        $raw = stream_get_contents($handle);
        $current = is_string($raw) && trim($raw) !== '' ? json_decode($raw, true) : $default;
        if ($current === null) {
            $current = $default;
        }
        $next = $mutator($current);
        $json = json_encode($next, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if (is_string($json)) {
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $json);
            fflush($handle);
        }
        @flock($handle, LOCK_UN);
        fclose($handle);
        return $next;
    }

    /** @param list<string> $command
     *  @return array{exit:int,stdout:string,stderr:string}
     */
    private function ejecutarProceso(array $command): array
    {
        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open($command, $descriptors, $pipes);
        if (!is_resource($process)) {
            return ['exit' => 1, 'stdout' => '', 'stderr' => 'No se pudo abrir el proceso.'];
        }
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return ['exit' => (int) @proc_close($process), 'stdout' => $stdout, 'stderr' => $stderr];
    }

    /** @return list<array{hash:string,date:string,author:string,subject:string}> */
    private function gitCambios(string $workspace): array
    {
        if ($workspace === '' || !is_dir($workspace) || !is_dir($workspace . DIRECTORY_SEPARATOR . '.git')) {
            return [];
        }
        $safe = str_replace('\\', '/', $workspace);
        $format = '%h%x1f%ad%x1f%an%x1f%s%x1e';
        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = @proc_open([
            'git',
            '-c',
            'safe.directory=' . $safe,
            '-C',
            $workspace,
            'log',
            '-3',
            '--date=iso-strict',
            '--pretty=format:' . $format,
        ], $descriptors, $pipes);
        if (!is_resource($process)) {
            return [];
        }
        $raw = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        @proc_close($process);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $out = [];
        foreach (explode("\x1e", $raw) as $registro) {
            $registro = trim($registro);
            if ($registro === '') {
                continue;
            }
            $partes = explode("\x1f", $registro, 4);
            if (count($partes) !== 4) {
                continue;
            }
            $out[] = [
                'hash' => trim($partes[0]),
                'date' => trim($partes[1]),
                'author' => trim($partes[2]),
                'subject' => trim($partes[3]),
            ];
        }
        return $out;
    }
}
