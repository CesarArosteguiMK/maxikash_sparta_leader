<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasCapabilityRegistry.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasDomainAccessService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasDataSourceRegistry.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasUniversalQueryService.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasSemanticQueryService.php';

use Services\LeonidasSemanticQueryService;
use Services\LeonidasUniversalQueryService;

function assertSemanticGuard(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$questions = [
    'Consulta el credito 1600',
    'Lista colaboradores de Capital Humano',
    'Dame un reporte de Convenios',
    'Lista las Motos Adjudicadas',
    'Consulta el modulo Direcciones',
    'Consulta las tareas de Legacy',
    'Dame un reporte de Atlas',
    'Lista los Tickets abiertos',
    'Dame un reporte de Analitica',
    'Resume Gastos de Cobranza',
    'Consulta la Organizacion de la empresa',
    'Explica el modulo Servicios',
];

$semantic = new LeonidasSemanticQueryService();
$universal = new LeonidasUniversalQueryService();

foreach ($questions as $question) {
    $semanticResponse = $semantic->resolver($question, 999, []);
    assertSemanticGuard(
        ($semanticResponse['tipo'] ?? '') === 'dominio_permiso_denegado',
        "El enrutador semantico no detuvo sin permiso: {$question}"
    );

    $universalResponse = $universal->resolver($question, 999, []);
    assertSemanticGuard(
        ($universalResponse['tipo'] ?? '') === 'dominio_permiso_denegado',
        "El descubridor universal no detuvo sin permiso: {$question}"
    );
}

echo "OK LeonidasSemanticPermissionGuard\n";
