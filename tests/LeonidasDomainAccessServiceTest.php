<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasCapabilityRegistry.php';
require_once dirname(__DIR__) . '/backend/services/LeonidasDomainAccessService.php';

use Services\LeonidasCapabilityRegistry;
use Services\LeonidasDomainAccessService;

function assertDomainAccess(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$registry = new LeonidasCapabilityRegistry();
$access = new LeonidasDomainAccessService();

$cases = [
    'creditos' => ['Consulta el credito 1600', 'estado_cuenta'],
    'capital_humano' => ['Lista colaboradores de Capital Humano', 'rrhh_lectura'],
    'convenios' => ['Dame un reporte de Convenios', 'convenio'],
    'motos_adjudicadas' => ['Lista las Motos Adjudicadas', 'motos'],
    'direcciones' => ['Consulta el modulo Direcciones', 'direcciones'],
    'legacy' => ['Consulta las tareas de Legacy', 'legacy'],
    'atlas' => ['Dame un reporte de Atlas', 'atlas'],
    'tickets' => ['Lista los Tickets abiertos', 'tickets'],
    'analitica' => ['Dame un reporte de Analitica', 'analitica'],
    'gastos_cobranza' => ['Resume Gastos de Cobranza', 'gastos_cobranza'],
    'organizacion' => ['Consulta la Organizacion de la empresa', 'organizacion'],
    'servicios' => ['Explica el modulo Servicios', 'servicios'],
];

foreach ($cases as $expectedDomain => [$question, $permission]) {
    $domain = $registry->detectar($question);
    assertDomainAccess(
        ($domain['id'] ?? null) === $expectedDomain,
        "No se detecto {$expectedDomain} para validar su permiso."
    );

    $denied = $access->verificar($domain, []);
    assertDomainAccess(
        $denied['autorizado'] === false,
        "{$expectedDomain} sin permiso debe denegarse."
    );
    assertDomainAccess(
        ($denied['respuesta']['tipo'] ?? '') === 'dominio_permiso_denegado',
        "La denegacion de {$expectedDomain} debe ser explicita."
    );
    assertDomainAccess(
        ($denied['respuesta']['fuente'] ?? '') === 'control_permisos_sparta',
        "La denegacion de {$expectedDomain} debe identificar el control aplicado."
    );
    assertDomainAccess(
        str_contains((string) ($denied['respuesta']['mensaje'] ?? ''), 'No se consultaron datos'),
        "La denegacion de {$expectedDomain} debe confirmar que no consulto datos."
    );

    $allowed = $access->verificar($domain, [$permission => true]);
    assertDomainAccess(
        $allowed['autorizado'] === true,
        "{$permission} debe autorizar {$expectedDomain}."
    );
}

assertDomainAccess(
    $access->verificar(null, [])['autorizado'] === true,
    'Una pregunta sin dominio no debe denegarse por esta politica.'
);

echo "OK LeonidasDomainAccessService\n";
