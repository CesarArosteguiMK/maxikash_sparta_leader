<?php

namespace Services;

/**
 * Control limitado y verificable de los agentes locales autorizados.
 * Nunca acepta comandos, rutas o puertos proporcionados por el usuario o la IA.
 */
final class LeonidasLocalAgentService
{
    public const ACTION = 'servicio_local_control';

    /** @var array<string, callable> */
    private array $adapters;

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }

    public static function accionesEjecutables(): array
    {
        return [self::ACTION];
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $texto = $this->normalizar($normalizado !== '' ? $normalizado : $mensaje);
        $servicioId = $this->detectarServicio($texto);
        $accion = $this->detectarAccion($texto);
        $consultaEstado = preg_match('/\b(estado|estatus|status|como esta|como estan|esta arriba|esta abajo|esta activo|esta caido|funciona|corriendo|revisa|consultar?|verifica)\b/u', $texto) === 1;
        $mencionaAgentes = preg_match('/\b(agente|agentes|servicio|servicios|shell|segundometro|primeros pagos|gastos (de )?cobranza)\b/u', $texto) === 1;

        if ($servicioId === null && !$mencionaAgentes) {
            return null;
        }

        if ($consultaEstado && $accion === null) {
            if (!$this->autorizado($contexto)) {
                return $this->respuesta('Tu perfil no tiene permiso para consultar ni controlar los agentes locales.', 'agente_denegado');
            }
            if ($servicioId !== null) {
                return $this->respuestaEstado([$servicioId]);
            }
            return $this->respuestaEstado(array_keys($this->catalogo()));
        }

        if ($accion === null) {
            return null;
        }
        if (!$this->autorizado($contexto)) {
            return $this->respuesta('Tu perfil no tiene permiso para iniciar, detener o reiniciar agentes locales.', 'agente_denegado');
        }
        if ($servicioId === null) {
            return $this->respuesta(
                'Indica cual agente deseas controlar: Segundometro, correos de primeros pagos o gastos de cobranza.',
                'agente_pregunta'
            );
        }

        $servicio = $this->servicio($servicioId);
        $estado = $this->estado($servicioId);
        if ($accion === 'iniciar' && ($estado['estado'] ?? '') === 'up') {
            return $this->respuesta($servicio['nombre'] . ' ya esta arriba y responde correctamente. No hice cambios.', 'agente_estado');
        }
        if ($accion === 'parar' && empty($estado['listening'])) {
            return $this->respuesta($servicio['nombre'] . ' ya esta detenido. No hice cambios.', 'agente_estado');
        }

        $verbo = ['iniciar' => 'iniciar', 'parar' => 'detener', 'reiniciar' => 'reiniciar'][$accion];
        $estadoActual = $this->etiquetaEstado($estado);

        return [
            'mensaje' => 'Vista previa del control de servicio:'
                . "\nAgente: " . $servicio['nombre']
                . "\nPuerto: " . $servicio['port']
                . "\nEstado actual: " . $estadoActual
                . "\nAccion: " . ucfirst($verbo)
                . "\nAl confirmar ejecutare la orden y comprobare el puerto y la respuesta HTTP.",
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => self::ACTION,
                'resumen' => $verbo . ' ' . $servicio['nombre'],
                'payload' => [
                    'servicio' => $servicioId,
                    'operacion' => $accion,
                ],
            ],
        ];
    }

    public function ejecutar(string $accion, array $payload, array $contexto): array
    {
        if ($accion !== self::ACTION) {
            throw new \RuntimeException('El ejecutor de agentes locales no reconoce la accion solicitada.');
        }
        if (!$this->autorizado($contexto)) {
            throw new \RuntimeException('Tu perfil ya no tiene permiso para controlar agentes locales.');
        }

        $servicioId = trim((string) ($payload['servicio'] ?? ''));
        $operacion = trim((string) ($payload['operacion'] ?? ''));
        if (!isset($this->catalogo()[$servicioId]) || !in_array($operacion, ['iniciar', 'parar', 'reiniciar'], true)) {
            throw new \RuntimeException('La orden confirmada no pertenece a la lista segura de agentes y operaciones.');
        }

        $servicio = $this->servicio($servicioId);
        $antes = $this->estado($servicioId);
        if ($operacion === 'iniciar' && ($antes['estado'] ?? '') === 'up') {
            return $this->resultadoSinCambio($servicio, $operacion, $antes, 'El agente ya estaba arriba.');
        }
        if ($operacion === 'parar' && empty($antes['listening'])) {
            return $this->resultadoSinCambio($servicio, $operacion, $antes, 'El agente ya estaba detenido.');
        }

        $control = $this->controlar($servicioId, $operacion);
        $despues = is_array($control['estado'] ?? null) ? $control['estado'] : $this->estado($servicioId);
        $esperado = $operacion === 'parar' ? 'down' : 'up';
        $verificado = $esperado === 'down'
            ? empty($despues['listening'])
            : (($despues['estado'] ?? '') === 'up');

        if (!$verificado || empty($control['success'])) {
            $detalle = trim((string) ($control['message'] ?? 'La comprobacion posterior no alcanzo el estado esperado.'));
            throw new \RuntimeException(
                'No se pudo ' . $this->verbo($operacion) . ' ' . $servicio['nombre'] . '. '
                . $detalle . ' Estado verificado: ' . $this->etiquetaEstado($despues) . '.'
            );
        }

        $mensaje = 'Orden completada y verificada.'
            . "\nAgente: " . $servicio['nombre']
            . "\nAccion: " . ucfirst($this->verbo($operacion))
            . "\nEstado final: " . $this->etiquetaEstado($despues)
            . "\nPuerto: " . $servicio['port'];
        if (!empty($despues['pid'])) {
            $mensaje .= "\nPID: " . (int) $despues['pid'];
        }
        if (isset($despues['http_status'])) {
            $mensaje .= "\nHTTP: " . (int) $despues['http_status'];
        }

        return [
            'mensaje' => $mensaje,
            'tipo' => 'agente_ejecutado',
            'ejecucion' => [
                'servicio' => $servicioId,
                'operacion' => $operacion,
                'estado_antes' => (string) ($antes['estado'] ?? 'desconocido'),
                'estado_final' => (string) ($despues['estado'] ?? 'desconocido'),
                'puerto' => (int) $servicio['port'],
                'pid' => $despues['pid'] ?? null,
                'verificado' => true,
            ],
        ];
    }

    private function respuestaEstado(array $ids): array
    {
        $lineas = [];
        $detalle = [];
        foreach ($ids as $id) {
            $servicio = $this->servicio($id);
            $estado = $this->estado($id);
            $lineas[] = $servicio['nombre'] . ': ' . $this->etiquetaEstado($estado)
                . ' (puerto ' . $servicio['port'] . ').';
            $detalle[] = [
                'servicio' => $id,
                'nombre' => $servicio['nombre'],
                'puerto' => $servicio['port'],
                'estado' => $estado['estado'] ?? 'desconocido',
                'pid' => $estado['pid'] ?? null,
                'http_status' => $estado['http_status'] ?? null,
            ];
        }
        return [
            'mensaje' => "Estado verificado de los agentes:\n" . implode("\n", $lineas),
            'tipo' => 'agente_estado',
            'servicios' => $detalle,
        ];
    }

    private function resultadoSinCambio(array $servicio, string $operacion, array $estado, string $detalle): array
    {
        return [
            'mensaje' => $detalle . ' Estado verificado: ' . $this->etiquetaEstado($estado) . '.',
            'tipo' => 'agente_ejecutado',
            'ejecucion' => [
                'servicio' => $servicio['id'],
                'operacion' => $operacion,
                'estado_antes' => $estado['estado'] ?? 'desconocido',
                'estado_final' => $estado['estado'] ?? 'desconocido',
                'puerto' => $servicio['port'],
                'pid' => $estado['pid'] ?? null,
                'verificado' => true,
                'sin_cambio' => true,
            ],
        ];
    }

    private function autorizado(array $contexto): bool
    {
        return (int) ($contexto['actor_id'] ?? 0) === 878
            && !empty($contexto['permisos_agente']['servicios_locales']);
    }

    private function detectarServicio(string $texto): ?string
    {
        if (preg_match('/\b(segundometro|segundo metro)\b/u', $texto)) {
            return 'segundometro';
        }
        if (preg_match('/\b(correos? (de )?primeros pagos|primeros pagos)\b/u', $texto)) {
            return 'correos_pp';
        }
        if (preg_match('/\b(gastos (de )?cobranza)\b/u', $texto)) {
            return 'gastos_cobranza';
        }
        return null;
    }

    private function detectarAccion(string $texto): ?string
    {
        if (preg_match('/\b(reiniciar|reinicia|reinicien|reactivar|reactiva|reinicio)\b/u', $texto)) {
            return 'reiniciar';
        }
        if (preg_match('/\b(detener|deten|detenga|detengan|parar|paren|apagar|apaga|tumbar|tumba)\b/u', $texto)
            || preg_match('/\bpara (?:el |al )?(?:agente|servicio|shell)\b/u', $texto)) {
            return 'parar';
        }
        if (preg_match('/\b(iniciar|inicia|inicien|levantar|levanta|arrancar|arranca|encender|enciende)\b/u', $texto)) {
            return 'iniciar';
        }
        return null;
    }

    private function estado(string $servicioId): array
    {
        $servicio = $this->servicio($servicioId);
        if (isset($this->adapters['status'])) {
            $estado = ($this->adapters['status'])($servicioId, $servicio);
            return is_array($estado) ? $estado : ['estado' => 'desconocido', 'listening' => false];
        }
        return $this->estadoReal($servicio);
    }

    private function controlar(string $servicioId, string $operacion): array
    {
        $servicio = $this->servicio($servicioId);
        if (isset($this->adapters['control'])) {
            $resultado = ($this->adapters['control'])($servicioId, $operacion, $servicio);
            return is_array($resultado) ? $resultado : ['success' => false, 'message' => 'El adaptador no devolvio un resultado valido.'];
        }
        return $this->controlReal($servicio, $operacion);
    }

    private function controlReal(array $servicio, string $operacion): array
    {
        if (stripos(PHP_OS, 'WIN') !== 0) {
            return ['success' => false, 'message' => 'El control local solo esta habilitado en Windows.'];
        }

        if ($operacion === 'parar') {
            $lanzado = $this->detenerReal($servicio);
            $estado = $this->esperarEstado($servicio, 'down', 10000);
            return ['success' => $lanzado && empty($estado['listening']), 'estado' => $estado, 'message' => 'Se ejecuto el cierre y se comprobo el puerto.'];
        }

        if ($operacion === 'reiniciar') {
            $this->detenerReal($servicio);
            $detenido = $this->esperarEstado($servicio, 'down', 10000);
            if (!empty($detenido['listening'])) {
                return ['success' => false, 'estado' => $detenido, 'message' => 'El proceso anterior no libero el puerto.'];
            }
            usleep(500000);
        }

        $lanzado = $this->iniciarReal($servicio);
        $estado = $lanzado
            ? $this->esperarEstado($servicio, 'up', 35000)
            : $this->estadoReal($servicio);
        return [
            'success' => $lanzado && ($estado['estado'] ?? '') === 'up',
            'estado' => $estado,
            'message' => $lanzado ? 'Se ejecuto el arranque y se comprobo el health.' : 'No se pudo lanzar el archivo de inicio.',
        ];
    }

    private function iniciarReal(array $servicio): bool
    {
        $bat = (string) $servicio['start_bat'];
        if (!is_file($bat)) {
            return false;
        }

        $cmdTemporal = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . 'sparta-leonidas-' . $servicio['id'] . '-' . date('Ymd-His') . '-' . getmypid() . '.cmd';
        $contenido = "@echo off\r\ncd /d \"" . str_replace('"', '""', dirname($bat)) . "\"\r\n"
            . 'call "' . str_replace('"', '""', $bat) . '" >NUL 2>NUL' . "\r\n"
            . 'del "%~f0" >NUL 2>NUL' . "\r\n";
        if (@file_put_contents($cmdTemporal, $contenido) === false) {
            return false;
        }

        $psTemporal = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . 'sparta-leonidas-launch-' . $servicio['id'] . '-' . date('Ymd-His') . '-' . getmypid() . '.ps1';
        $ps = "\$ErrorActionPreference = 'Stop'\r\n";
        $ps .= '$argsList = @('
            . $this->psQuote('/d') . ', '
            . $this->psQuote('/c') . ', '
            . $this->psQuote($cmdTemporal) . ")\r\n";
        $ps .= 'Start-Process -FilePath ' . $this->psQuote('cmd.exe')
            . ' -ArgumentList $argsList -WorkingDirectory ' . $this->psQuote(dirname($bat))
            . " -WindowStyle Hidden\r\n";
        if (@file_put_contents($psTemporal, $ps) === false) {
            @unlink($cmdTemporal);
            return false;
        }

        $systemRoot = rtrim((string) getenv('SystemRoot'), '\\/');
        $psExe = $systemRoot !== ''
            ? $systemRoot . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'WindowsPowerShell'
                . DIRECTORY_SEPARATOR . 'v1.0' . DIRECTORY_SEPARATOR . 'powershell.exe'
            : 'powershell.exe';
        if ($psExe !== 'powershell.exe' && !is_file($psExe)) {
            $psExe = 'powershell.exe';
        }
        $comando = '"' . str_replace('"', '""', $psExe) . '" -NoProfile -ExecutionPolicy Bypass -File "'
            . str_replace('"', '""', $psTemporal) . '"';
        $proceso = @proc_open($comando, [0 => ['file', 'NUL', 'r'], 1 => ['file', 'NUL', 'a'], 2 => ['file', 'NUL', 'a']], $pipes, dirname($bat));
        if (!is_resource($proceso)) {
            @unlink($psTemporal);
            @unlink($cmdTemporal);
            return false;
        }
        $codigo = @proc_close($proceso);
        @unlink($psTemporal);
        return $codigo === 0;
    }

    private function detenerReal(array $servicio): bool
    {
        $ejecutado = false;
        $stopPs1 = (string) ($servicio['stop_ps1'] ?? '');
        $stopBat = (string) ($servicio['stop_bat'] ?? '');
        if ($stopPs1 !== '' && is_file($stopPs1)) {
            @shell_exec('powershell.exe -NoProfile -ExecutionPolicy Bypass -File "' . str_replace('"', '""', $stopPs1) . '" -Silent');
            $ejecutado = true;
        } elseif ($stopBat !== '' && is_file($stopBat)) {
            @shell_exec('cmd.exe /d /c "' . str_replace('"', '""', $stopBat) . '"');
            $ejecutado = true;
        }
        $puerto = (int) $servicio['port'];
        $teniaProceso = isset($this->puertosEnEscucha()[$puerto]);
        $this->detenerPuerto($puerto);
        return $ejecutado || $teniaProceso;
    }

    private function esperarEstado(array $servicio, string $objetivo, int $maxMs): array
    {
        $limite = microtime(true) + ($maxMs / 1000);
        do {
            $estado = $this->estadoReal($servicio);
            if (($objetivo === 'up' && ($estado['estado'] ?? '') === 'up')
                || ($objetivo === 'down' && empty($estado['listening']))) {
                return $estado;
            }
            usleep(500000);
        } while (microtime(true) < $limite);
        return $estado;
    }

    private function estadoReal(array $servicio): array
    {
        $puertos = $this->puertosEnEscucha();
        $puerto = (int) $servicio['port'];
        $escuchando = isset($puertos[$puerto]);
        $http = $escuchando ? $this->probarHttp((string) $servicio['health']) : ['ok' => false, 'status' => null, 'ms' => null];
        return [
            'estado' => $escuchando && $http['ok'] ? 'up' : ($escuchando ? 'degraded' : 'down'),
            'listening' => $escuchando,
            'pid' => $escuchando ? $puertos[$puerto] : null,
            'http_ok' => (bool) $http['ok'],
            'http_status' => $http['status'],
            'latency_ms' => $http['ms'],
        ];
    }

    private function puertosEnEscucha(): array
    {
        $salida = @shell_exec('netstat -ano');
        $puertos = [];
        if (!is_string($salida)) {
            return $puertos;
        }
        foreach (preg_split('/\r?\n/', $salida) ?: [] as $linea) {
            $linea = trim($linea);
            if (stripos($linea, 'LISTENING') === false) {
                continue;
            }
            $partes = preg_split('/\s+/', $linea);
            if (!is_array($partes) || count($partes) < 5) {
                continue;
            }
            $indiceEstado = null;
            foreach ($partes as $indice => $parte) {
                if (strcasecmp((string) $parte, 'LISTENING') === 0) {
                    $indiceEstado = $indice;
                    break;
                }
            }
            if ($indiceEstado === null || $indiceEstado < 2 || !isset($partes[$indiceEstado + 1])) {
                continue;
            }
            $local = (string) $partes[$indiceEstado - 2];
            $pid = (string) $partes[$indiceEstado + 1];
            if (preg_match('/:(\d+)$/', $local, $m) && preg_match('/^\d+$/', $pid)) {
                $puertos[(int) $m[1]] = (int) $pid;
            }
        }
        return $puertos;
    }

    private function detenerPuerto(int $puerto): void
    {
        $pid = $this->puertosEnEscucha()[$puerto] ?? 0;
        if ($pid > 0) {
            @shell_exec('taskkill /PID ' . (int) $pid . ' /F');
        }
    }

    private function probarHttp(string $url): array
    {
        $ch = @curl_init($url);
        if (!$ch) {
            return ['ok' => false, 'status' => null, 'ms' => null];
        }
        @curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_TIMEOUT_MS => 1500,
            CURLOPT_CONNECTTIMEOUT_MS => 800,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'Sparta-Leonidas/1.0',
        ]);
        $inicio = microtime(true);
        @curl_exec($ch);
        $status = (int) @curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = (int) @curl_errno($ch);
        @curl_close($ch);
        return [
            'ok' => $error === 0 && $status >= 200 && $status < 500,
            'status' => $status > 0 ? $status : null,
            'ms' => (int) round((microtime(true) - $inicio) * 1000),
        ];
    }

    private function servicio(string $id): array
    {
        $catalogo = $this->catalogo();
        if (!isset($catalogo[$id])) {
            throw new \RuntimeException('El agente solicitado no pertenece a la lista segura.');
        }
        return $catalogo[$id];
    }

    private function catalogo(): array
    {
        $backend = dirname(__DIR__);
        $ds = DIRECTORY_SEPARATOR;
        return [
            'segundometro' => [
                'id' => 'segundometro',
                'nombre' => 'Agente Segundometro',
                'port' => 3100,
                'health' => 'http://127.0.0.1:3100/health',
                'start_bat' => $backend . $ds . 'services' . $ds . 'segundometro-agent' . $ds . 'iniciar-agente.bat',
                'stop_ps1' => $backend . $ds . 'services' . $ds . 'segundometro-agent' . $ds . 'cerrar-agente.ps1',
            ],
            'correos_pp' => [
                'id' => 'correos_pp',
                'nombre' => 'Agente de correos de primeros pagos',
                'port' => 3110,
                'health' => 'http://127.0.0.1:3110/health',
                'start_bat' => $backend . $ds . 'services' . $ds . 'correos-primeros-pagos-agent' . $ds . 'iniciar-agente.bat',
                'stop_bat' => $backend . $ds . 'services' . $ds . 'correos-primeros-pagos-agent' . $ds . 'cerrar-agente.bat',
            ],
            'gastos_cobranza' => [
                'id' => 'gastos_cobranza',
                'nombre' => 'Agente de gastos de cobranza',
                'port' => 3120,
                'health' => 'http://127.0.0.1:3120/health',
                'start_bat' => $backend . $ds . 'services' . $ds . 'gastos-cobranza-agent' . $ds . 'iniciar-agente.bat',
                'stop_ps1' => $backend . $ds . 'services' . $ds . 'gastos-cobranza-agent' . $ds . 'cerrar-agente.ps1',
            ],
        ];
    }

    private function etiquetaEstado(array $estado): string
    {
        return match ((string) ($estado['estado'] ?? '')) {
            'up' => 'arriba y respondiendo',
            'degraded' => 'puerto abierto, pero health sin respuesta valida',
            'down' => 'detenido',
            default => 'desconocido',
        };
    }

    private function verbo(string $operacion): string
    {
        return ['iniciar' => 'iniciar', 'parar' => 'detener', 'reiniciar' => 'reiniciar'][$operacion] ?? $operacion;
    }

    private function respuesta(string $mensaje, string $tipo): array
    {
        return ['mensaje' => $mensaje, 'tipo' => $tipo];
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u']);
    }

    private function psQuote(string $valor): string
    {
        return "'" . str_replace("'", "''", $valor) . "'";
    }
}
