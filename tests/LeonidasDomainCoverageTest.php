<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasCapabilityRegistry.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasDomainService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasAssistantService.php';

use Services\LeonidasAssistantService;
use Services\LeonidasCapabilityRegistry;
use Services\LeonidasDomainService;

function assertDomainCoverage(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$registry = new LeonidasCapabilityRegistry();
$service = new LeonidasDomainService($registry);
$expected = [
    'creditos' => 'Explica el modulo de creditos',
    'capital_humano' => 'Que hace Capital Humano',
    'convenios' => 'Como funciona Convenios',
    'motos_adjudicadas' => 'Explica Motos Adjudicadas',
    'direcciones' => 'Que puedo hacer en Direcciones',
    'legacy' => 'Como se usa Legacy',
    'atlas' => 'Para que sirve Atlas',
    'tickets' => 'Explica el modulo de Tickets',
    'analitica' => 'Que hace Analitica',
    'gastos_cobranza' => 'Como funciona Gastos de Cobranza',
    'organizacion' => 'Explica Organizacion',
    'servicios' => 'Que hace Servicios',
];

$catalog = $registry->catalogoPublico();
assertDomainCoverage(count($catalog) === count($expected), 'El catalogo debe incluir los 12 dominios.');
$catalogJson = json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
assertDomainCoverage(
    !str_contains($catalogJson, 'Ã') && !str_contains($catalogJson, 'Â'),
    'El catalogo publico contiene texto UTF-8 corrompido.'
);

foreach ($catalog as $domain) {
    $id = (string) ($domain['id'] ?? '');
    assertDomainCoverage(isset($expected[$id]), 'Dominio inesperado o sin prueba: ' . $id);
    foreach (['nombre', 'proposito', 'control', 'modo_operativo'] as $field) {
        assertDomainCoverage(trim((string) ($domain[$field] ?? '')) !== '', "{$id} no declara {$field}.");
    }
    foreach (['submodulos', 'fuentes', 'consultas', 'acciones'] as $field) {
        assertDomainCoverage(
            is_array($domain[$field] ?? null) && count($domain[$field]) > 0,
            "{$id} no declara {$field}."
        );
    }
    foreach (['ejecutores', 'acciones_ejecutables'] as $field) {
        assertDomainCoverage(is_array($domain[$field] ?? null), "{$id} no declara {$field} como lista.");
    }
}

foreach ($expected as $id => $question) {
    $detected = $registry->detectar($question);
    assertDomainCoverage(
        ($detected['id'] ?? null) === $id,
        "No detecto {$id} con: {$question}. Detectado: " . (string) ($detected['id'] ?? 'ninguno')
    );

    $answer = $service->explicar($question);
    assertDomainCoverage(($answer['tipo'] ?? '') === 'dominio_sparta', "{$id} no genero explicacion.");
    assertDomainCoverage(($answer['dominio'] ?? '') === $id, "{$id} devolvio otro dominio.");
    assertDomainCoverage(
        str_contains((string) ($answer['mensaje'] ?? ''), 'Fuentes verificables:'),
        "{$id} no informa sus fuentes."
    );
}

$atlas = $registry->detectar('Explica Atlas');
assertDomainCoverage(
    ($atlas['acciones_ejecutables'] ?? null) === [],
    'Atlas no debe anunciar escrituras sin un ejecutor conectado.'
);
assertDomainCoverage(
    ($atlas['modo_operativo'] ?? '') === 'consulta_explicacion_y_navegacion',
    'Atlas debe declarar su modo de solo lectura, explicacion y navegacion.'
);

$humanResources = $registry->detectar('Explica Capital Humano');
assertDomainCoverage(
    in_array('rrhh_actualizar', $humanResources['acciones_ejecutables'] ?? [], true),
    'Capital Humano debe declarar el ejecutor real de actualizacion.'
);
assertDomainCoverage(
    ($humanResources['modo_operativo'] ?? '') === 'consulta_y_ejecucion_auditada',
    'Capital Humano debe declarar ejecucion auditada.'
);

assertDomainCoverage($registry->detectar('Hola Leonidas') === null, 'Un saludo no debe inventar dominio.');
assertDomainCoverage($service->explicar('Hola Leonidas') === null, 'Un saludo no debe explicar un dominio.');

$general = $service->capacidadesGenerales();
foreach ($catalog as $domain) {
    assertDomainCoverage(
        str_contains($general, (string) $domain['nombre']),
        'La cobertura general omitio ' . $domain['nombre'] . '.'
    );
}

$assistantReflection = new ReflectionClass(LeonidasAssistantService::class);
$assistant = $assistantReflection->newInstanceWithoutConstructor();
$operationalMethod = $assistantReflection->getMethod('esRespuestaOperativa');
foreach ([
    'dominio_permiso_denegado',
    'dominio_fuente_error',
    'dominio_ayuda',
] as $responseType) {
    assertDomainCoverage(
        $operationalMethod->invoke($assistant, ['tipo' => $responseType]) === true,
        "La respuesta {$responseType} podria ser reescrita por Gemini."
    );
}

echo "OK LeonidasDomainCoverage\n";
