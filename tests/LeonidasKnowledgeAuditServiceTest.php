<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasCapabilityRegistry.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasCodeKnowledgeService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasKnowledgeService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasKnowledgeAuditService.php';

use Services\LeonidasKnowledgeAuditService;

function assertKnowledgeAudit(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$summary = (new LeonidasKnowledgeAuditService())->resumen();
assertKnowledgeAudit(($summary['dominios_registrados'] ?? 0) === 12, 'El registro debe tener 12 dominios.');
assertKnowledgeAudit(($summary['dominios_documentados'] ?? 0) === 12, 'Todos los dominios deben estar documentados.');
assertKnowledgeAudit(($summary['dominios_sin_documento'] ?? []) === [], 'Hay dominios sin documento.');
assertKnowledgeAudit(($summary['dominios_sin_gobernanza'] ?? []) === [], 'Hay dominios sin gobierno documental.');
assertKnowledgeAudit(
    ($summary['controladores_reconocibles_por_codigo'] ?? 0) >= 48,
    'El inventario no reconoce los 48 controladores.'
);
assertKnowledgeAudit(
    ($summary['controladores_mencionados_explicitamente_en_documentos'] ?? 0) >= 48,
    'La documentacion no menciona explicitamente los 48 controladores.'
);
assertKnowledgeAudit(
    ($summary['documentos_complementarios'] ?? 0) >= 6,
    'Falta documentacion complementaria de la plataforma.'
);
assertKnowledgeAudit(
    ($summary['modulos_en_catalogo_curado'] ?? 0) >= 48,
    'El catalogo curado no cubre la amplitud de la plataforma.'
);
assertKnowledgeAudit(
    count((array) ($summary['revisiones_negocio_pendientes'] ?? [])) === 12,
    'La auditoria debe declarar honestamente las validaciones humanas pendientes.'
);
assertKnowledgeAudit(
    ($summary['documentos_fuera_de_vigencia'] ?? []) === [],
    'Hay documentos especializados fuera de vigencia.'
);

echo "OK LeonidasKnowledgeAuditService\n";
