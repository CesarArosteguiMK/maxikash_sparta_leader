<?php

require_once dirname(__DIR__) . '/backend/services/GeminiClient.php';

use Services\GeminiClient;

function assertGeminiRouting(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$client = new GeminiClient();
$method = new ReflectionMethod($client, 'buildPayload');
$method->setAccessible(true);

$parts = [['text' => 'Prueba sin datos personales']];
$newPayload = json_decode($method->invoke(
    $client,
    'Responde JSON.',
    $parts,
    'gemini-3.6-flash',
    500,
    true,
    0.1,
    'LOW'
), true);
$litePayload = json_decode($method->invoke(
    $client,
    'Responde JSON.',
    $parts,
    'gemini-3.5-flash-lite',
    500,
    true,
    0.1,
    'LOW'
), true);
$legacyPayload = json_decode($method->invoke(
    $client,
    'Responde JSON.',
    $parts,
    'gemini-3.1-flash-lite',
    500,
    true,
    0.1,
    'LOW'
), true);

assertGeminiRouting(is_array($newPayload), 'No se genero el payload para Gemini 3.6.');
assertGeminiRouting(!isset($newPayload['generationConfig']['temperature']), 'Gemini 3.6 no debe recibir temperature.');
assertGeminiRouting(($newPayload['generationConfig']['thinkingConfig']['thinkingLevel'] ?? '') === 'LOW', 'Gemini 3.6 debe usar razonamiento de baja latencia.');
assertGeminiRouting(!isset($litePayload['generationConfig']['temperature']), 'Gemini 3.5 Flash-Lite no debe recibir temperature.');
assertGeminiRouting(isset($legacyPayload['generationConfig']['temperature']), 'El fallback 3.1 debe conservar compatibilidad de muestreo.');
assertGeminiRouting(($newPayload['generationConfig']['responseMimeType'] ?? '') === 'application/json', 'La respuesta JSON debe seguir activa.');

echo "GeminiRoutingTest OK\n";
