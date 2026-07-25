<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/EnvLoader.php';
\Core\EnvLoader::load();
require_once __DIR__ . '/../core/DatabaseCliSupport.php';
require_once __DIR__ . '/../core/DatabaseSegundometro.php';
require_once __DIR__ . '/../services/PrimerosPagosS2VerificationService.php';

date_default_timezone_set('America/Mexico_City');
$opts = getopt('', ['limit:']);
$limite = isset($opts['limit']) ? (int) $opts['limit'] : 250;

try {
    $resultado = (new \Services\PrimerosPagosS2VerificationService())->ejecutar($limite);
    fwrite(STDOUT, json_encode($resultado, JSON_UNESCAPED_UNICODE) . PHP_EOL);
    exit(!empty($resultado['ok']) ? 0 : 1);
} catch (\Throwable $e) {
    fwrite(STDERR, '[PrimerosPagosS2] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
