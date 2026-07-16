<?php

require_once dirname(__DIR__) . '/backend/core/EnvLoader.php';
require_once dirname(__DIR__) . '/backend/core/DatabaseSegundometro.php';

use Core\DatabaseSegundometro;

function assertSchemaSqlSame(string $expected, string $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true)
        );
    }
}

$normalizar = new ReflectionMethod(DatabaseSegundometro::class, 'usarEsquemaConfigurado');
$normalizar->setAccessible(true);

$sqlConEsquemaAnterior = 'SELECT gc.id_gastos_cobranza '
    . 'FROM `esquema_anterior`.gastos_cobranza gc '
    . 'JOIN `esquema_anterior`.`condonaciones_cobranza_detalle` d '
    . 'ON d.id_gastos_cobranza = gc.id_gastos_cobranza';

assertSchemaSqlSame(
    'SELECT gc.id_gastos_cobranza FROM gastos_cobranza gc '
        . 'JOIN `condonaciones_cobranza_detalle` d '
        . 'ON d.id_gastos_cobranza = gc.id_gastos_cobranza',
    $normalizar->invoke(null, $sqlConEsquemaAnterior),
    'Las tablas administradas deben usar el esquema configurado.'
);

$sqlOtraTabla = 'SELECT * FROM `auditoria`.eventos';
assertSchemaSqlSame(
    $sqlOtraTabla,
    $normalizar->invoke(null, $sqlOtraTabla),
    'No se deben modificar referencias a tablas ajenas al flujo.'
);

echo "DatabaseSegundometroSchema: OK\n";
