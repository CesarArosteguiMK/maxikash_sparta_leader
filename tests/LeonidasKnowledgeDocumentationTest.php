<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasKnowledgeService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasCapabilityRegistry.php';

use Services\LeonidasCapabilityRegistry;
use Services\LeonidasKnowledgeService;

function assertLeonidasKnowledge(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$domainDocuments = [
    'creditos' => ['consulta' => 'creditos', 'archivo' => 'CREDITOS.md'],
    'capital_humano' => ['consulta' => 'capital humano', 'archivo' => 'CAPITAL_HUMANO.md'],
    'convenios' => ['consulta' => 'convenios', 'archivo' => 'CONVENIOS.md'],
    'motos_adjudicadas' => ['consulta' => 'motos adjudicadas', 'archivo' => 'MOTOS_ADJUDICADAS.md'],
    'direcciones' => ['consulta' => 'direcciones', 'archivo' => 'DIRECCIONES.md'],
    'legacy' => ['consulta' => 'legacy', 'archivo' => 'LEGACY.md'],
    'atlas' => ['consulta' => 'atlas', 'archivo' => 'ATLAS.md'],
    'tickets' => ['consulta' => 'tickets', 'archivo' => 'TICKETS.md'],
    'analitica' => ['consulta' => 'analitica', 'archivo' => 'ANALITICA.md'],
    'gastos_cobranza' => ['consulta' => 'gastos cobranza', 'archivo' => 'GASTOS_COBRANZA.md'],
    'organizacion' => ['consulta' => 'organizacion', 'archivo' => 'ORGANIZACION.md'],
    'servicios' => ['consulta' => 'servicios', 'archivo' => 'SERVICIOS.md'],
];

$directory = dirname(__DIR__) . '/public/assets/docs/leonidas';
$requiredSections = [
    '## Reglas de negocio',
    '## Fuentes autorizadas',
    '## Permisos',
    '## Preguntas reales que debe responder',
    '## Ejecutores disponibles',
];

$catalog = (new LeonidasCapabilityRegistry())->catalogoPublico();
$catalogById = [];
foreach ($catalog as $domain) {
    $catalogById[(string) ($domain['id'] ?? '')] = $domain;
}
assertLeonidasKnowledge(
    array_keys($catalogById) === array_keys($domainDocuments),
    'La biblioteca documental y el registro de capacidades no tienen los mismos dominios.'
);

foreach ($domainDocuments as $domainId => $definition) {
    $fileName = $definition['archivo'];
    $path = $directory . '/' . $fileName;
    assertLeonidasKnowledge(is_file($path), "Falta el documento de {$domainId}: {$fileName}.");
    $content = (string) file_get_contents($path);
    assertLeonidasKnowledge(
        str_contains($content, 'Dominio: `' . $domainId . '`.'),
        "{$fileName} no declara el dominio {$domainId}."
    );
    foreach ($requiredSections as $section) {
        assertLeonidasKnowledge(
            str_contains($content, $section),
            "{$fileName} no contiene la seccion {$section}."
        );
    }
    foreach ($catalogById[$domainId]['acciones_ejecutables'] ?? [] as $action) {
        assertLeonidasKnowledge(
            str_contains($content, (string) $action),
            "{$fileName} no documenta el ejecutor conectado {$action}."
        );
    }
}

$service = new LeonidasKnowledgeService();
$reflection = new ReflectionClass($service);
$search = $reflection->getMethod('buscarDocumentacion');

foreach ($domainDocuments as $domainId => $definition) {
    $fileName = $definition['archivo'];
    $query = $definition['consulta'];
    $documents = $search->invoke($service, 'Explica las reglas, permisos y fuentes de ' . $query);
    assertLeonidasKnowledge($documents !== [], "La busqueda no encontro documentacion para {$domainId}.");
    $paths = array_map(static fn(array $document): string => (string) ($document['ruta'] ?? ''), $documents);
    assertLeonidasKnowledge(
        in_array('leonidas/' . $fileName, $paths, true),
        "La busqueda de {$domainId} no recupero leonidas/{$fileName}. Obtuvo: " . implode(', ', $paths)
    );
    foreach ($documents as $document) {
        assertLeonidasKnowledge(
            mb_strlen((string) ($document['contenido'] ?? ''), 'UTF-8') <= 2900,
            'Un fragmento documental excede el limite seguro de contexto.'
        );
    }
}

$supplementaryDocuments = [
    'Explica condonaciones y cierre de credito' => 'leonidas/CREDITOS_SUBMODULOS.md',
    'Como funciona almacen virtual y revision mecanica' => 'leonidas/MOTOS_CADENA_OPERATIVA.md',
    'Que hace el modulo de despachos' => 'leonidas/COBRANZA_CAMPO_DESPACHOS.md',
    'Como funciona onboarding y perfil' => 'leonidas/PLATAFORMA_USUARIO.md',
    'Explica primeros pagos y segundometro' => 'leonidas/REPORTERIA_PROCESOS.md',
    'Como se configuran formularios y equivalencias' => 'leonidas/CONFIGURACION_CATALOGOS.md',
];
foreach ($supplementaryDocuments as $question => $expectedPath) {
    $documents = $search->invoke($service, $question);
    $paths = array_map(static fn(array $document): string => (string) ($document['ruta'] ?? ''), $documents);
    assertLeonidasKnowledge(
        in_array($expectedPath, $paths, true),
        "La busqueda complementaria no recupero {$expectedPath}. Obtuvo: " . implode(', ', $paths)
    );
}

echo "OK LeonidasKnowledgeDocumentation\n";
