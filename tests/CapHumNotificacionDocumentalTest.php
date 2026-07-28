<?php

require_once __DIR__ . '/../backend/core/Model.php';
require_once __DIR__ . '/../backend/models/CapHumNotificacionDocumental.php';

use Models\CapHumNotificacionDocumental;

$esperados = [
    [2026, 1, 'Semanas cotizadas 2026 - 1 semestre'],
    [2026, 2, 'Semanas cotizadas 2026 - 2 semestre'],
    [2027, 1, 'Semanas cotizadas 2027 - 1 semestre'],
];

foreach ($esperados as [$anio, $semestre, $esperado]) {
    $actual = CapHumNotificacionDocumental::nombrePeriodo($anio, $semestre);
    if ($actual !== $esperado) {
        fwrite(STDERR, "Nombre incorrecto: {$actual}\n");
        exit(1);
    }
}

$tiposEsperados = [
    'semanas_cotizadas' => [33, 'Semanas cotizadas del IMSS (segundos patrones)'],
    'solicitud_interna' => [17, 'Solicitud interna'],
    'cv_solicitud_trabajo' => [18, 'CV o solicitud de trabajo'],
    'acta_nacimiento' => [12, 'Acta de nacimiento'],
    'curp' => [8, 'CURP'],
    'comprobante_domicilio' => [11, 'Comprobante de domicilio'],
    'constancia_situacion_fiscal' => [22, 'Constancia de situación fiscal'],
    'identificacion_oficial' => [9, 'Identificación oficial'],
    'numero_seguridad_social' => [23, 'Número de seguridad social (NSS)'],
    'retencion_fonacot_infonavit' => [24, 'Hoja de retención FONACOT o INFONAVIT'],
    'estado_cuenta' => [25, 'Estado de cuenta'],
];

$catalogo = [];
foreach (CapHumNotificacionDocumental::catalogoTipos() as $tipo) {
    $catalogo[$tipo['clave']] = $tipo;
}
foreach ($tiposEsperados as $clave => [$documentoId, $nombre]) {
    if (($catalogo[$clave]['documento_id'] ?? null) !== $documentoId
        || ($catalogo[$clave]['nombre'] ?? null) !== $nombre
        || trim((string)($catalogo[$clave]['mensaje_predeterminado'] ?? '')) === '') {
        fwrite(STDERR, "Catálogo incorrecto para {$clave}\n");
        exit(1);
    }
}

if (CapHumNotificacionDocumental::nombrePeriodo(2026, 2, 'curp') !== 'CURP 2026 - 2 semestre') {
    fwrite(STDERR, "Nombre de periodo incorrecto para CURP\n");
    exit(1);
}

if (($catalogo['solicitud_interna']['url_descarga'] ?? '') !== '/CapHum/llenarSolicitudInternaNotificacion') {
    fwrite(STDERR, "La solicitud interna no usa el generador en línea\n");
    exit(1);
}

echo "CapHumNotificacionDocumentalTest OK\n";
