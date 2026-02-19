<?php

namespace Core;

/**
 * Rate limiting para login: intentos por IP, bloqueo temporal y registro de fallos.
 */
class LoginRateLimit
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;
    private const WINDOW_MINUTES = 15;

    /** @var string */
    private static $dir;

    private static function getDir(): string
    {
        if (self::$dir === null) {
            self::$dir = defined('RAIZ') ? RAIZ . '/storage/rate_limit' : dirname(__DIR__) . '/storage/rate_limit';
            if (!is_dir(self::$dir)) {
                @mkdir(self::$dir, 0755, true);
            }
        }
        return self::$dir;
    }

    private static function getClientIp(): string
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = is_string($_SERVER[$key]) ? trim(explode(',', $_SERVER[$key])[0]) : '';
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    private static function getFilePath(): string
    {
        $ip = self::getClientIp();
        $hash = hash('sha256', $ip);
        return self::getDir() . '/' . $hash . '.json';
    }

    private static function read(): array
    {
        $path = self::getFilePath();
        if (!is_file($path)) {
            return ['attempts' => 0, 'first_at' => 0, 'locked_until' => 0];
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return ['attempts' => 0, 'first_at' => 0, 'locked_until' => 0];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data + ['attempts' => 0, 'first_at' => 0, 'locked_until' => 0] : ['attempts' => 0, 'first_at' => 0, 'locked_until' => 0];
    }

    private static function write(array $data): void
    {
        $path = self::getFilePath();
        @file_put_contents($path, json_encode($data), LOCK_EX);
    }

    /**
     * Comprueba si la IP está bloqueada. Devuelve [true] si puede intentar; [false, mensaje] si está bloqueada.
     * @return array{0: bool, 1?: string}
     */
    public static function check(): array
    {
        $data = self::read();
        $now = time();
        if ($data['locked_until'] > $now) {
            $mins = (int) ceil(($data['locked_until'] - $now) / 60);
            return [false, "Demasiados intentos. Intente de nuevo en {$mins} minutos."];
        }
        if ($data['locked_until'] > 0 && $data['locked_until'] <= $now) {
            self::write(['attempts' => 0, 'first_at' => 0, 'locked_until' => 0]);
        }
        return [true];
    }

    /**
     * Registra un intento fallido (incrementa contador y opcionalmente bloquea).
     */
    public static function recordFailure(): void
    {
        $data = self::read();
        $now = time();
        if ($data['locked_until'] > $now) {
            return;
        }
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        if ($data['first_at'] === 0) {
            $data['first_at'] = $now;
        }
        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            $data['locked_until'] = $now + (self::LOCKOUT_MINUTES * 60);
        }
        self::write($data);
        self::logFailure();
    }

    /**
     * Limpia el contador tras un login correcto.
     */
    public static function clear(): void
    {
        self::write(['attempts' => 0, 'first_at' => 0, 'locked_until' => 0]);
    }

    private static function logFailure(): void
    {
        $logDir = defined('RAIZ') ? RAIZ . '/storage/logs' : dirname(__DIR__) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $ip = self::getClientIp();
        $user = isset($_POST['usuario']) ? (is_string($_POST['usuario']) ? $_POST['usuario'] : '') : '';
        $line = date('Y-m-d H:i:s') . ' IP=' . $ip . ' usuario=' . $user . "\n";
        @file_put_contents($logDir . '/login_failures.log', $line, FILE_APPEND | LOCK_EX);
    }
}
