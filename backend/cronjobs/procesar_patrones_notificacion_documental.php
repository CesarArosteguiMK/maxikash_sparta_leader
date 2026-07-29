<?php

use Core\Database;
use Models\CapHumNotificacionDocumental;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

define('RAIZ', dirname(__DIR__));
require_once RAIZ . '/config/config.php';

spl_autoload_register(static function (string $clase): void {
    $ruta = RAIZ . '/' . str_replace('\\', '/', $clase) . '.php';
    if (is_readable($ruta)) {
        require_once $ruta;
    }
});

$idCampania = max(0, (int)($argv[1] ?? 0));
if ($idCampania <= 0) {
    exit(2);
}

$db = new Database();
$nombreLock = 'rrhh_patrones_campania_' . $idCampania;
$lock = $db->queryOne(
    'SELECT GET_LOCK(:nombre, 0) AS adquirido',
    ['nombre' => $nombreLock]
);
if ((int)($lock['adquirido'] ?? 0) !== 1) {
    exit(0);
}

try {
    $pendientes = CapHumNotificacionDocumental::entregasPendientesAnalisisPatrones(
        $idCampania,
        1000
    );
    if (!empty($pendientes['success'])) {
        foreach (($pendientes['datos'] ?? []) as $entrega) {
            $idEntrega = (int)($entrega['id'] ?? 0);
            $archivo = basename((string)($entrega['archivo'] ?? ''));
            $ruta = $archivo !== '' ? sparta_uploads_join('documentos', $archivo) : '';
            if ($ruta === '' || !is_file($ruta)) {
                // La base de datos puede compartirse entre entornos sin que el
                // almacenamiento físico de documentos también esté disponible.
                // La ausencia local nunca debe convertirse en un dictamen.
                continue;
            }
            $analisis = CapHumNotificacionDocumental::analizarArchivoPatronesMotorV1($ruta);
            if ($analisis === null) {
                break;
            }
            CapHumNotificacionDocumental::guardarAnalisisPatronesEntrega($idEntrega, $analisis);
        }
    }
} catch (Throwable $e) {
    // El trabajo es reanudable; la siguiente consulta volverá a lanzarlo.
} finally {
    $db->queryOne(
        'SELECT RELEASE_LOCK(:nombre) AS liberado',
        ['nombre' => $nombreLock]
    );
}
