<?php

namespace Models;

use Core\Model;
use Core\Database;

class Paises extends Model
{
    private static $paisesIso = [
        'afghanistan' => 'af', 'albania' => 'al', 'alemania' => 'de', 'andorra' => 'ad',
        'angola' => 'ao', 'argentina' => 'ar', 'armenia' => 'am', 'australia' => 'au',
        'austria' => 'at', 'azerbaiyan' => 'az', 'bahamas' => 'bs', 'barbados' => 'bb',
        'belgica' => 'be', 'belice' => 'bz', 'benin' => 'bj', 'bolivia' => 'bo',
        'brasil' => 'br', 'bulgaria' => 'bg', 'canada' => 'ca', 'chile' => 'cl',
        'china' => 'cn', 'colombia' => 'co', 'corea del sur' => 'kr', 'corea del norte' => 'kp',
        'costa rica' => 'cr', 'croacia' => 'hr', 'cuba' => 'cu', 'dinamarca' => 'dk',
        'ecuador' => 'ec', 'egipto' => 'eg', 'el salvador' => 'sv', 'emiratos arabes unidos' => 'ae',
        'eslovaquia' => 'sk', 'eslovenia' => 'si', 'espana' => 'es', 'españa' => 'es',
        'estados unidos' => 'us', 'estonia' => 'ee', 'etiopia' => 'et', 'filipinas' => 'ph',
        'finlandia' => 'fi', 'francia' => 'fr', 'georgia' => 'ge', 'ghana' => 'gh',
        'grecia' => 'gr', 'guatemala' => 'gt', 'guyana' => 'gy', 'haiti' => 'ht',
        'honduras' => 'hn', 'hungria' => 'hu', 'india' => 'in', 'indonesia' => 'id',
        'irak' => 'iq', 'iran' => 'ir', 'irlanda' => 'ie', 'islandia' => 'is',
        'israel' => 'il', 'italia' => 'it', 'jamaica' => 'jm', 'japon' => 'jp',
        'jordania' => 'jo', 'kazajistan' => 'kz', 'kenia' => 'ke', 'letonia' => 'lv',
        'libano' => 'lb', 'lituania' => 'lt', 'luxemburgo' => 'lu', 'malasia' => 'my',
        'marruecos' => 'ma', 'mexico' => 'mx', 'méxico' => 'mx', 'moldavia' => 'md',
        'mongolia' => 'mn', 'mozambique' => 'mz', 'myanmar' => 'mm', 'nicaragua' => 'ni',
        'nigeria' => 'ng', 'noruega' => 'no', 'nueva zelanda' => 'nz', 'paises bajos' => 'nl',
        'pakistan' => 'pk', 'panama' => 'pa', 'panamá' => 'pa', 'paraguay' => 'py',
        'peru' => 'pe', 'perú' => 'pe', 'polonia' => 'pl', 'portugal' => 'pt',
        'puerto rico' => 'pr', 'reino unido' => 'gb', 'republica checa' => 'cz',
        'republica dominicana' => 'do', 'república dominicana' => 'do',
        'rumania' => 'ro', 'rusia' => 'ru', 'senegal' => 'sn', 'serbia' => 'rs',
        'singapur' => 'sg', 'sudafrica' => 'za', 'suecia' => 'se', 'suiza' => 'ch',
        'surinam' => 'sr', 'tailandia' => 'th', 'taiwan' => 'tw', 'trinidad y tobago' => 'tt',
        'tunez' => 'tn', 'turquia' => 'tr', 'ucrania' => 'ua', 'uruguay' => 'uy',
        'venezuela' => 've', 'vietnam' => 'vn',
    ];

    public static function detectarCodigoIso($nombre)
    {
        $nombre = mb_strtolower(trim($nombre));
        $nombre = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $nombre
        );

        if (isset(self::$paisesIso[$nombre])) {
            return self::$paisesIso[$nombre];
        }

        $nombreOriginal = mb_strtolower(trim($nombre));
        if (isset(self::$paisesIso[$nombreOriginal])) {
            return self::$paisesIso[$nombreOriginal];
        }

        foreach (self::$paisesIso as $key => $iso) {
            $keyNorm = str_replace(['á','é','í','ó','ú','ñ','ü'], ['a','e','i','o','u','n','u'], $key);
            if ($keyNorm === $nombre || $key === $nombreOriginal) {
                return $iso;
            }
        }

        return 'xx';
    }

    public static function getAll()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $r = $db->queryAll(
                "SELECT 
                    p.id,
                    p.nombre,
                    p.codigo_iso,
                    p.activo,
                    COUNT(DISTINCT per.id) AS total_personas,
                    COUNT(DISTINCT d.id) AS total_departamentos
                FROM paises p
                LEFT JOIN persona per ON per.id_pais = p.id AND per.estatus = 'Activo'
                LEFT JOIN departamento d ON d.id_pais = p.id
                GROUP BY p.id
                ORDER BY FIELD(p.codigo_iso, 'mx', 'gt', 'co'), p.nombre"
            );
            $datos = is_array($r) ? $r : [];

            echo json_encode([
                'success' => true,
                'mensaje' => 'Países encontrados.',
                'datos' => $datos
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al consultar países.',
                'datos' => [],
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    public static function insertPais($nombre)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $nombre = trim($nombre);
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'mensaje' => 'El nombre del país es requerido.']);
                exit;
            }

            $db = new Database();

            $existe = $db->queryOne(
                "SELECT id FROM paises WHERE LOWER(nombre) = LOWER(:nombre)",
                ['nombre' => $nombre]
            );
            if ($existe) {
                echo json_encode(['success' => false, 'mensaje' => 'Ya existe un país con ese nombre.']);
                exit;
            }

            $codigoIso = self::detectarCodigoIso($nombre);

            $db->CRUD(
                "INSERT INTO paises (nombre, codigo_iso, activo) VALUES (:nombre, :codigo_iso, 1)",
                ['nombre' => $nombre, 'codigo_iso' => $codigoIso]
            );

            echo json_encode([
                'success' => true,
                'mensaje' => 'País agregado correctamente.',
                'codigo_iso' => $codigoIso
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al insertar el país.',
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    public static function toggleActivo($id)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = (int) $id;
            if ($id < 1) {
                echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
                exit;
            }

            $db = new Database();
            $db->CRUD(
                "UPDATE paises SET activo = IF(activo = 1, 0, 1) WHERE id = :id",
                ['id' => $id]
            );

            echo json_encode([
                'success' => true,
                'mensaje' => 'Estado actualizado correctamente.'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al actualizar estado.',
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    public static function getPaisesActivos()
    {
        try {
            $db = new Database();
            $r = $db->queryAll(
                "SELECT id, nombre, codigo_iso FROM paises WHERE activo = 1 ORDER BY FIELD(codigo_iso, 'mx', 'gt', 'co'), nombre"
            );
            return is_array($r) ? $r : [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
