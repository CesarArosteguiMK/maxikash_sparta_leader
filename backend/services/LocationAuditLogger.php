<?php

/**
 * Logs/trazas inmutables: hash_input, resultado_motor (short), prompt_ia_short, response_hash, verif_result, timestamp.
 * Escribe trazas solo si SPARTA_ENABLE_FILE_LOGS=1; usa una carpeta temporal fuera del proyecto.
 */

namespace Services;

class LocationAuditLogger
{
    private string $logPath;

    public function __construct(?string $logPath = null)
    {
        $this->logPath = $logPath ?? (
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___php_logs'
            . DIRECTORY_SEPARATOR . 'location_audit.log'
        );
    }

    public function log(
        string $hashInput,
        array $resultadoMotorShort,
        string $promptIaShort,
        string $responseHash,
        array $verifResult,
        ?string $timestamp = null
    ): void {
        if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') !== '1') {
            return;
        }
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $ts = $timestamp ?? date('c');
        $line = json_encode([
            'hash_input'    => $hashInput,
            'resultado_motor' => $resultadoMotorShort,
            'prompt_ia_short' => substr($promptIaShort, 0, 500),
            'response_hash' => $responseHash,
            'verif_result'  => $verifResult,
            'timestamp'     => $ts,
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($this->logPath, $line, FILE_APPEND | LOCK_EX);
    }
}
