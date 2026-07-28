<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasCodeKnowledgeService.php';

use Services\LeonidasCodeKnowledgeService;

function assertCodeKnowledge(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$service = new LeonidasCodeKnowledgeService();
$inventory = $service->inventory();
$counts = ['controladores' => 0, 'modelos' => 0, 'servicios' => 0];
foreach ($inventory as $component) {
    $type = (string) ($component['tipo'] ?? '');
    if (isset($counts[$type])) {
        $counts[$type]++;
    }
    assertCodeKnowledge(
        !isset($component['codigo'], $component['sql'], $component['contenido']),
        'El inventario seguro no debe incluir cuerpos de codigo ni SQL.'
    );
    assertCodeKnowledge(
        count((array) ($component['metodos_publicos'] ?? [])) <= 24,
        'Un componente excedio el limite de metodos enviado al contexto.'
    );
}

assertCodeKnowledge($counts['controladores'] >= 48, 'No se inventariaron todos los controladores de Sparta.');
assertCodeKnowledge(
    $counts['modelos'] >= 61,
    'No se inventariaron los 61 modelos con contenido de Sparta; el archivo test_ssh.php esta vacio.'
);
assertCodeKnowledge($counts['servicios'] >= 20, 'No se inventariaron los servicios de Sparta.');

$cases = [
    'Explica el modulo Almacen Virtual' => ['AlmacenVirtual', 'inventario'],
    'Que hace el modulo Viaticos' => ['Viaticos', 'paneladmin'],
    'Como funciona el modulo Tracking Recoleccion' => ['TrackingRecoleccion', 'rutas'],
    'Explica el modulo de condonaciones' => ['Condonaciones', 'crear'],
    'Que permite el modulo Onboarding' => ['Onboarding', 'video'],
];

foreach ($cases as $question => [$expectedClass, $expectedMethod]) {
    $context = $service->contextoPara($question);
    $components = (array) ($context['componentes_relevantes'] ?? []);
    assertCodeKnowledge($components !== [], 'No encontro componente para: ' . $question);
    $first = $components[0];
    assertCodeKnowledge(
        ($first['clase'] ?? '') === $expectedClass,
        "Esperaba {$expectedClass} para '{$question}', obtuvo " . (string) ($first['clase'] ?? 'ninguno')
    );
    assertCodeKnowledge(
        in_array($expectedMethod, (array) ($first['metodos_publicos'] ?? []), true),
        "{$expectedClass} no expuso el metodo publico esperado {$expectedMethod}."
    );

    $response = $service->resolver($question);
    assertCodeKnowledge(($response['tipo'] ?? '') === 'modulo_codigo_sparta', 'No genero respuesta de modulo.');
    assertCodeKnowledge(
        str_contains((string) ($response['mensaje'] ?? ''), $expectedClass),
        'La respuesta no identifico el componente correcto.'
    );
}

assertCodeKnowledge(
    $service->resolver('Hola Leonidas') === null,
    'Un saludo no debe activar el inventario de codigo.'
);

echo "OK LeonidasCodeKnowledgeService\n";
