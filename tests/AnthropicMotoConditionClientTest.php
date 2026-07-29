<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/services/AnthropicMotoConditionClient.php';

use Services\AnthropicMotoConditionClient;

$client = new AnthropicMotoConditionClient();
$reflection = new ReflectionClass($client);
$imageReader = $reflection->getMethod('imagenLocal');
$imageReader->setAccessible(true);

$archivo = tempnam(sys_get_temp_dir(), 'sparta-anthropic-image-');
if ($archivo === false) {
    fwrite(STDERR, "No se pudo crear archivo temporal.\n");
    exit(1);
}
$jpg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAH/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/AV//xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/AV//2gAMAwEAAhEDEQA/AL//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Ap//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IX//2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z', true);
file_put_contents($archivo, $jpg === false ? '' : $jpg);

try {
    $imagen = $imageReader->invoke($client, $archivo);
    if (!is_array($imagen) || ($imagen['mime'] ?? '') !== 'image/jpeg' || empty($imagen['body'])) {
        fwrite(STDERR, "No se pudo leer una imagen local valida.\n");
        exit(1);
    }
    $config = $client->config();
    foreach (['enabled', 'api_key', 'base_url', 'model', 'timeout'] as $key) {
        if (!array_key_exists($key, $config)) {
            fwrite(STDERR, "Falta configuracion {$key}.\n");
            exit(1);
        }
    }
} finally {
    @unlink($archivo);
}

echo "AnthropicMotoConditionClientTest OK\n";
