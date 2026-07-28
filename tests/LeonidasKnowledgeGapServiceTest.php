<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasCapabilityRegistry.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasCodeKnowledgeService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasKnowledgeGapService.php';

use Services\LeonidasKnowledgeGapService;

function assertKnowledgeGap(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$path = sys_get_temp_dir() . '/leonidas_knowledge_gap_' . bin2hex(random_bytes(6)) . '.jsonl';
$service = new LeonidasKnowledgeGapService($path);
$question = 'Como cierro el ticket 123456 de Juan Perez? Escribeme a juan@example.com o 5512345678';
$response = [
    'tipo' => 'dominio_requiere_criterio',
    'dominio' => 'tickets',
    'fuente' => 'registro_capacidades_sparta',
];

assertKnowledgeGap($service->registrar($question, $response), 'No registro la brecha.');
assertKnowledgeGap(!$service->registrar($question, $response), 'No deduplico la misma pregunta.');
assertKnowledgeGap(
    !$service->registrar('Hola', ['tipo' => 'conversacion']),
    'Una conversacion normal no debe registrarse como brecha.'
);

$contents = (string) file_get_contents($path);
assertKnowledgeGap(!str_contains($contents, 'Juan'), 'Persistio un nombre propio.');
assertKnowledgeGap(!str_contains($contents, 'Perez'), 'Persistio un apellido.');
assertKnowledgeGap(!str_contains($contents, 'juan@example.com'), 'Persistio un correo.');
assertKnowledgeGap(!str_contains($contents, '123456'), 'Persistio un identificador.');
assertKnowledgeGap(!str_contains($contents, '5512345678'), 'Persistio un telefono.');
assertKnowledgeGap(str_contains($contents, 'ticket'), 'El patron perdio el concepto tecnico.');

$summary = $service->resumen();
assertKnowledgeGap(($summary['total'] ?? 0) === 1, 'El resumen no conto la brecha.');
assertKnowledgeGap(
    ($summary['por_dominio']['tickets'] ?? 0) === 1,
    'El resumen no agrupo el dominio.'
);

@unlink($path);
echo "OK LeonidasKnowledgeGapService\n";
