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
    'SELECT GET_LOCK(:nombre, 3) AS adquirido',
    ['nombre' => $nombreLock]
);
if ((int)($lock['adquirido'] ?? 0) !== 1) {
    exit(0);
}

try {
    if (!CapHumNotificacionDocumental::motorV1PatronesDisponible()) {
        throw new RuntimeException('Motor V1 no disponible.');
    }
    $intentados = [];
    $consultasSinTrabajo = 0;
    while ($consultasSinTrabajo < 2) {
        $pendientes = CapHumNotificacionDocumental::entregasPendientesAnalisisPatrones(
            $idCampania,
            1000
        );
        if (empty($pendientes['success'])) {
            break;
        }

        $trabajoDisponible = false;
        foreach (($pendientes['datos'] ?? []) as $entrega) {
            $idEntrega = (int)($entrega['id'] ?? 0);
            if ($idEntrega <= 0 || isset($intentados[$idEntrega])) {
                continue;
            }
            $intentados[$idEntrega] = true;
            $archivo = basename((string)($entrega['archivo'] ?? ''));
            $ruta = $archivo !== '' ? sparta_uploads_join('documentos', $archivo) : '';
            if ($ruta === '' || !is_file($ruta)) {
                // La base de datos puede compartirse entre entornos sin que el
                // almacenamiento físico de documentos también esté disponible.
                // La ausencia local nunca debe convertirse en un dictamen.
                continue;
            }
            $trabajoDisponible = true;
            $analisis = CapHumNotificacionDocumental::analizarArchivoPatronesMotorV1($ruta);
            if ($analisis === null) {
                // La disponibilidad global ya se validó antes de empezar. Si una
                // petición individual agota el tiempo, ese PDF no debe permanecer
                // eternamente en cola ni bloquear el resto de la campaña.
                CapHumNotificacionDocumental::guardarAnalisisPatronesEntrega($idEntrega, [
                    'valido' => false,
                    'revision_manual' => true,
                    'patrones_vigentes' => null,
                    'patrones_historial' => 0,
                    'patrones' => [],
                    'motor_ia' => 'motor_v1',
                    'fuente_lectura' => 'motor_v1_timeout',
                    'clasificacion' => 'revision_manual',
                    'codigo_resultado' => 'tiempo_lectura_agotado',
                    'mensaje' => 'El Motor V1 no pudo terminar la lectura dentro del tiempo permitido; requiere revisión manual.',
                ]);
                continue;
            }
            CapHumNotificacionDocumental::guardarAnalisisPatronesEntrega($idEntrega, $analisis);
        }

        if ($trabajoDisponible) {
            $consultasSinTrabajo = 0;
            continue;
        }

        // Ventana corta para absorber documentos cargados mientras este worker
        // ya tenía el candado. Así no dependen de abrir el modal para reactivarse.
        $consultasSinTrabajo++;
        if ($consultasSinTrabajo < 2) {
            usleep(500000);
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
