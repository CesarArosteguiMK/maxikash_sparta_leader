<?php

/**
 * Logs/trazas inmutables: hash_input, resultado_motor (short), prompt_ia_short, response_hash, verif_result, timestamp.
 * Escribe en storage/logs/location_audit.log (o backend/storage/logs/).
 */

namespace Services;

class LocationAuditLogger
{
    private string $logPath;

    public function __construct(?string $logPath = null)
    {
        $this->logPath = $logPath ?? (__DIR__ . '/../storage/logs/location_audit.log');
    }

    public function log(
        string $hashInput,
        array $resultadoMotorShort,
        string $promptIaShort,
        string $responseHash,
        array $verifResult,
        ?string $timestamp = null
    ): void {
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
