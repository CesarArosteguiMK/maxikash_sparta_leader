<?php

require_once dirname(__DIR__) . '/backend/services/LeonidasConveniosService.php';

use Services\LeonidasConveniosService;

function assertConvenio(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$service = new LeonidasConveniosService();

$casos = [
    'plazo_maximo' => 'A cuanto plazo en semanas maximo se puede cargar un convenio?',
    'reactivacion' => 'Se puede reactivar un convenio incumplido?',
    'causas_cancelacion' => 'Que considera el sistema para cancelar el convenio?',
    'tiempo_cancelacion' => 'Que tiempo considera el sistema para cancelar el convenio?',
    'pago_pendiente_conciliar' => 'Que es en la parte del pago Pendiente de conciliar?',
    'modificacion' => 'Como modifico un convenio?',
    'elegibilidad' => 'Que requiere un credito para obtener un convenio?',
];

foreach ($casos as $tema => $pregunta) {
    $respuesta = $service->resolver($pregunta);
    assertConvenio(is_array($respuesta), 'No respondio la pregunta: ' . $pregunta);
    assertConvenio(($respuesta['tema'] ?? '') === $tema, 'Clasifico mal el tema ' . $tema . '.');
    assertConvenio(($respuesta['tipo'] ?? '') === 'consulta_convenios', 'El tipo debe ser consulta_convenios.');
    assertConvenio(strlen((string) ($respuesta['mensaje'] ?? '')) > 120, 'La respuesta de ' . $tema . ' es demasiado pobre.');
    assertConvenio(($respuesta['fuente'] ?? '') === 'reglas_operativas_convenios', 'Debe declarar la fuente operativa.');
}

$plazo = $service->resolver($casos['plazo_maximo']);
assertConvenio(str_contains($plazo['mensaje'], 'No existe un plazo máximo único'), 'No debe inventar un plazo global.');

$reactivacion = $service->resolver($casos['reactivacion']);
assertConvenio(str_contains($reactivacion['mensaje'], 'oferta'), 'Debe distinguir oferta y convenio.');
assertConvenio(str_contains($reactivacion['mensaje'], 'convenio nuevo'), 'Debe explicar que se crea un convenio nuevo.');

$conciliacion = $service->resolver($casos['pago_pendiente_conciliar']);
assertConvenio(str_contains($conciliacion['mensaje'], 'no está confirmado como pagado'), 'Debe explicar el estado real del comprobante.');

$accion = $service->resolver('Cancela el convenio 1600');
assertConvenio($accion === null, 'Una orden de accion debe continuar al ejecutor del agente.');

$fuera = $service->resolver('Cuantos candidatos hay en revision?');
assertConvenio($fuera === null, 'No debe capturar consultas de otros modulos.');

echo "LeonidasConveniosService: OK\n";
