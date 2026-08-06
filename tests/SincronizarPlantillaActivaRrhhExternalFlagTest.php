<?php

$source = file_get_contents(dirname(__DIR__) . '/scripts/sincronizar_plantilla_activa_rrhh.php');
if ($source === false) {
    fwrite(STDERR, "No se pudo leer el sincronizador de plantilla RR.HH.\n");
    exit(1);
}

if (preg_match('/SET\s+p\.es_externo\s*=\s*1/i', $source)) {
    fwrite(STDERR, "La sincronizacion no debe inferir la marca Externo por ausencia en el padron.\n");
    exit(1);
}

if (strpos($source, 'SET p.es_externo = 0') === false) {
    fwrite(STDERR, "La plantilla vigente debe poder corregir una marca Externo incorrecta.\n");
    exit(1);
}

echo "SincronizarPlantillaActivaRrhhExternalFlagTest OK\n";
