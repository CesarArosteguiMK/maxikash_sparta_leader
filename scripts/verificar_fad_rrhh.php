<?php

declare(strict_types=1);

use Core\Database;
use Core\EnvLoader;
use Services\FadRrhhService;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../backend/core/EnvLoader.php';
require_once __DIR__ . '/../backend/core/Database.php';
require_once __DIR__ . '/../backend/services/FadRrhhPortalClient.php';
require_once __DIR__ . '/../backend/services/FadRrhhService.php';

EnvLoader::load();

$result = [
    'configuracion' => null,
    'base_de_datos' => [
        'conexion' => 'NO_VERIFICADA',
        'tabla_seguimiento_existe' => false,
        'estructura_completa' => false,
    ],
];

try {
    $config = (new FadRrhhService())->configuracion();
    $result['configuracion'] = [
        'enabled' => $config['enabled'],
        'api_ready' => $config['api_ready'],
        'flow_ready' => $config['flow_ready'],
        'flow_missing' => $config['flow_missing'],
        'enforce_signed' => $config['enforce_signed'],
    ];
} catch (Throwable $e) {
    $result['configuracion'] = ['error' => 'No fue posible validar la configuracion.'];
}

try {
    $db = new Database();
    $row = $db->queryOne(
        "SELECT COUNT(*) AS total
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name = 'candidato_fad_rrhh_solicitud'"
    );
    $exists = (int) ($row['total'] ?? 0) === 1;
    $structureComplete = false;
    if ($exists) {
        $columns = $db->queryOne(
            "SELECT COUNT(DISTINCT column_name) AS total
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = 'candidato_fad_rrhh_solicitud'
               AND column_name IN (
                   'id', 'id_candidato', 'referencia', 'document_id', 'signer_id',
                   'requisition_id', 'signing_url', 'estatus', 'pdf_firmado_ruta',
                   'pdf_firmado_sha256', 'fad_archivo_ruta', 'ultimo_error',
                   'intentos_sync', 'creado_por', 'actualizado_por', 'enviado_en',
                   'firmado_en', 'ultima_sync_en', 'creado_en', 'actualizado_en'
               )"
        );
        $uniqueIndexes = $db->queryOne(
            "SELECT COUNT(DISTINCT index_name) AS total
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = 'candidato_fad_rrhh_solicitud'
               AND non_unique = 0
               AND index_name IN (
                   'uq_fad_rrhh_candidato', 'uq_fad_rrhh_referencia', 'uq_fad_rrhh_requisition'
               )"
        );
        $structureComplete = (int) ($columns['total'] ?? 0) === 20
            && (int) ($uniqueIndexes['total'] ?? 0) === 3;
    }
    $result['base_de_datos'] = [
        'conexion' => 'OK',
        'tabla_seguimiento_existe' => $exists,
        'estructura_completa' => $structureComplete,
    ];
} catch (Throwable $e) {
    $result['base_de_datos'] = [
        'conexion' => 'ERROR',
        'tabla_seguimiento_existe' => false,
        'estructura_completa' => false,
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
