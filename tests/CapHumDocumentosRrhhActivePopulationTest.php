<?php

$source = file_get_contents(dirname(__DIR__) . '/backend/models/CapHum.php');
if ($source === false) {
    fwrite(STDERR, "No se pudo leer el modelo de Capital Humano.\n");
    exit(1);
}

$inicioResumen = strpos($source, 'public static function getResumenDocumentosRrhhGlobal()');
$inicioMuestra = strpos($source, 'public static function getPersonasMuestraExpedientesRrhh(');
$finMuestra = strpos($source, 'public static function getDocumentosExpedienteRrhhPorPersonas(', $inicioMuestra);
if ($inicioResumen === false || $inicioMuestra === false || $finMuestra === false) {
    fwrite(STDERR, "No se encontraron los metodos documentales esperados.\n");
    exit(1);
}

$resumen = substr($source, $inicioResumen, $inicioMuestra - $inicioResumen);
$muestra = substr($source, $inicioMuestra, $finMuestra - $inicioMuestra);

foreach ([$resumen, $muestra] as $metodo) {
    if (strpos($metodo, "p.estatus = 'Activo'") === false
        || strpos($metodo, 'COALESCE(p.es_externo, 0) = 0') === false) {
        fwrite(STDERR, "El expediente debe consultar colaboradores internos activos.\n");
        exit(1);
    }
    if (strpos($metodo, 'INNER JOIN estado_cuenta.rrhh_plantilla_activa') !== false) {
        fwrite(STDERR, "El expediente no debe depender del padron auxiliar desactualizado.\n");
        exit(1);
    }
}

if (strpos($resumen, 'ON emp.id = p.id_empresa') === false
    || strpos($muestra, 'ON emp.id = p.id_empresa') === false) {
    fwrite(STDERR, "La empresa debe obtenerse desde la persona vigente.\n");
    exit(1);
}

echo "CapHumDocumentosRrhhActivePopulationTest OK\n";
