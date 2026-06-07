<?php

namespace Models;

use Core\Database;
use Core\Model;

class Atlas extends Model
{
    public static function getSucursales(): array
    {
        try {
            $db = new Database();
            $datos = $db->queryAll(
                "
                SELECT
                    s.id,
                    s.fk_sucursal,
                    s.distribuidor_id,
                    d.nombre AS distribuidor_nombre,
                    s.sucursal,
                    s.diversificacion_id,
                    s.diversificacion,
                    dv.nombre AS diversificacion_nombre,
                    COALESCE(NULLIF(TRIM(s.direccion_sucursal), ''), dir.direccion, '') AS direccion,
                    s.coordenadas,
                    s.latitud,
                    s.longitud,
                    s.divisional_id,
                    dvl.nombre AS divisional_nombre,
                    s.division_id,
                    divs.nombre AS division_nombre,
                    s.regional_id,
                    reg.nombre AS regional_nombre,
                    s.supervisor_id,
                    sup.nombre AS supervisor_nombre,
                    s.asesor_id,
                    ase.nombre AS asesor_nombre,
                    s.clasificacion_id,
                    c.nombre AS clasificacion_nombre,
                    c.icon_font AS clasificacion_icon_font,
                    c.color_hex AS clasificacion_color_hex,
                    s.activo,
                    s.fecha_alta,
                    s.fecha_actualizacion,
                    tel.numero_telefono,
                    tel.nombre_contacto
                FROM atlas_catalogo_sucursales s
                INNER JOIN atlas_catalogo_clasificaciones c
                        ON c.id = s.clasificacion_id
                       AND c.activo = 1
                INNER JOIN atlas_catalogo_distribuidores d
                        ON d.id = s.distribuidor_id
                       AND d.activo = 1
                INNER JOIN atlas_catalogo_diversificaciones dv
                        ON dv.id = s.diversificacion_id
                       AND dv.activo = 1
                LEFT JOIN atlas_catalogo_divisionales dvl
                       ON dvl.id = s.divisional_id
                      AND dvl.activo = 1
                LEFT JOIN atlas_catalogo_divisiones divs
                       ON divs.id = s.division_id
                      AND divs.activo = 1
                LEFT JOIN atlas_catalogo_regionales reg
                       ON reg.id = s.regional_id
                      AND reg.activo = 1
                LEFT JOIN atlas_catalogo_supervisores sup
                       ON sup.id = s.supervisor_id
                      AND sup.activo = 1
                LEFT JOIN atlas_catalogo_asesores ase
                       ON ase.id = s.asesor_id
                      AND ase.activo = 1
                LEFT JOIN atlas_asigna_direccion_sucursal dir
                       ON dir.fk_sucursal = s.fk_sucursal
                      AND dir.activo = 1
                      AND dir.es_principal = 1
                LEFT JOIN atlas_asigna_telefono_sucursal tel
                       ON tel.fk_sucursal = s.fk_sucursal
                      AND tel.activo = 1
                      AND tel.es_principal = 1
                ORDER BY s.activo DESC, s.sucursal ASC, s.id ASC
                "
            );

            $totales = [
                'total' => count($datos),
                'activas' => 0,
                'inactivas' => 0,
                'con_coordenadas' => 0,
            ];

            foreach ($datos as &$row) {
                $row['activo'] = (int)($row['activo'] ?? 0);
                $row['estado'] = $row['activo'] === 1 ? 'Activa' : 'Inactiva';
                if ($row['activo'] === 1) {
                    $totales['activas']++;
                } else {
                    $totales['inactivas']++;
                }
                if (trim((string)($row['latitud'] ?? '')) !== '' && trim((string)($row['longitud'] ?? '')) !== '') {
                    $totales['con_coordenadas']++;
                }
            }
            unset($row);

            return [
                'success' => true,
                'mensaje' => 'Sucursales obtenidas.',
                'datos' => $datos,
                'totales' => $totales,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudieron obtener las sucursales.',
                'error' => $e->getMessage(),
                'datos' => [],
                'totales' => [
                    'total' => 0,
                    'activas' => 0,
                    'inactivas' => 0,
                    'con_coordenadas' => 0,
                ],
            ];
        }
    }

    public static function getCatalogos(): array
    {
        try {
            $db = new Database();
            return [
                'success' => true,
                'mensaje' => 'Catálogos obtenidos.',
                'datos' => [
                    'divisiones' => $db->queryAll("
                        SELECT
                            divs.id,
                            divs.divisional_id,
                            dvl.nombre AS divisional_nombre,
                            divs.nombre,
                            divs.activo,
                            divs.fecha_alta,
                            divs.fecha_actualizacion
                        FROM atlas_catalogo_divisiones divs
                        INNER JOIN atlas_catalogo_divisionales dvl
                                ON dvl.id = divs.divisional_id
                               AND dvl.activo = 1
                        ORDER BY divs.activo DESC, dvl.nombre ASC, divs.nombre ASC, divs.id ASC
                    "),
                    'divisionales' => $db->queryAll("
                        SELECT id, nombre, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_divisionales
                        WHERE activo = 1
                        ORDER BY nombre ASC, id ASC
                    "),
                    'regionales' => $db->queryAll("
                        SELECT reg.id, reg.division_id, reg.nombre, reg.activo, reg.fecha_alta, reg.fecha_actualizacion
                        FROM atlas_catalogo_regionales reg
                        INNER JOIN atlas_catalogo_divisiones divs
                                ON divs.id = reg.division_id
                               AND divs.activo = 1
                        WHERE reg.activo = 1
                        ORDER BY reg.nombre ASC, reg.id ASC
                    "),
                    'supervisores' => $db->queryAll("
                        SELECT sup.id, sup.regional_id, sup.nombre, sup.activo, sup.fecha_alta, sup.fecha_actualizacion
                        FROM atlas_catalogo_supervisores sup
                        INNER JOIN atlas_catalogo_regionales reg
                                ON reg.id = sup.regional_id
                               AND reg.activo = 1
                        WHERE sup.activo = 1
                        ORDER BY sup.nombre ASC, sup.id ASC
                    "),
                    'asesores' => $db->queryAll("
                        SELECT ase.id, ase.supervisor_id, ase.nombre, ase.activo, ase.fecha_alta, ase.fecha_actualizacion
                        FROM atlas_catalogo_asesores ase
                        INNER JOIN atlas_catalogo_supervisores sup
                                ON sup.id = ase.supervisor_id
                               AND sup.activo = 1
                        WHERE ase.activo = 1
                        ORDER BY ase.nombre ASC, ase.id ASC
                    "),
                    'distribuidores' => $db->queryAll("
                        SELECT id, nombre, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_distribuidores
                        ORDER BY activo DESC, nombre ASC, id ASC
                    "),
                    'diversificaciones' => $db->queryAll("
                        SELECT id, nombre, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_diversificaciones
                        ORDER BY activo DESC, nombre ASC, id ASC
                    "),
                    'clasificaciones' => $db->queryAll("
                        SELECT id, nombre, icon_font, color_hex, orden, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_clasificaciones
                        ORDER BY COALESCE(orden, 999999), nombre ASC, id ASC
                    "),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudieron obtener los catálogos.',
                'error' => $e->getMessage(),
                'datos' => [
                    'divisiones' => [],
                    'divisionales' => [],
                    'regionales' => [],
                    'supervisores' => [],
                    'asesores' => [],
                    'distribuidores' => [],
                    'diversificaciones' => [],
                    'clasificaciones' => [],
                ],
            ];
        }
    }

    public static function guardarSucursal(array $input): array
    {
        try {
            $db = new Database();
            $id = self::intVal($input['id'] ?? 0);
            $sucursal = self::strVal($input['sucursal'] ?? '');
            $distribuidorId = self::intVal($input['distribuidor_id'] ?? 0);
            $diversificacionId = self::intVal($input['diversificacion_id'] ?? 0);
            $fkSucursal = 0;

            if ($id > 0) {
                $actual = $db->queryOne(
                    "SELECT fk_sucursal FROM atlas_catalogo_sucursales WHERE id = :id LIMIT 1",
                    ['id' => $id]
                );
                if (!$actual) {
                    return ['success' => false, 'mensaje' => 'No se encontró la sucursal a actualizar.'];
                }
                $fkSucursal = self::intVal($actual['fk_sucursal'] ?? 0);
            } else {
                $siguiente = $db->queryOne(
                    "SELECT COALESCE(MAX(fk_sucursal), 0) + 1 AS fk_sucursal FROM atlas_catalogo_sucursales"
                );
                $fkSucursal = self::intVal($siguiente['fk_sucursal'] ?? 0);
            }

            if ($fkSucursal <= 0 || $sucursal === '' || $distribuidorId <= 0 || $diversificacionId <= 0) {
                return ['success' => false, 'mensaje' => 'Captura sucursal, distribuidor y diversificación.'];
            }

            $obligatorios = [
                'clasificacion_id' => 'clasificación',
                'divisional_id' => 'divisional',
                'division_id' => 'división',
                'regional_id' => 'regional',
                'supervisor_id' => 'supervisor',
                'asesor_id' => 'asesor',
                'direccion_sucursal' => 'dirección',
                'estado' => 'estado',
                'municipio' => 'municipio',
                'localidad' => 'localidad',
                'codigo_postal' => 'código postal',
                'latitud' => 'latitud',
                'longitud' => 'longitud',
            ];
            $faltantes = [];
            foreach ($obligatorios as $campo => $label) {
                $valor = $input[$campo] ?? null;
                if (is_numeric($valor)) {
                    if ((float)$valor == 0.0 && !in_array($campo, ['latitud', 'longitud'], true)) {
                        $faltantes[] = $label;
                    }
                    continue;
                }
                if (self::strVal($valor) === '') {
                    $faltantes[] = $label;
                }
            }
            if ($faltantes) {
                return ['success' => false, 'mensaje' => 'Completa todos los campos obligatorios: ' . implode(', ', $faltantes) . '.'];
            }

            $diversificacion = $db->queryOne(
                "SELECT id, nombre FROM atlas_catalogo_diversificaciones WHERE id = :id AND activo = 1 LIMIT 1",
                ['id' => $diversificacionId]
            );
            if (!$diversificacion) {
                return ['success' => false, 'mensaje' => 'La diversificación seleccionada no está activa.'];
            }

            $datos = [
                'fk_sucursal' => $fkSucursal,
                'distribuidor_id' => $distribuidorId,
                'sucursal' => $sucursal,
                'diversificacion_id' => $diversificacionId,
                'diversificacion' => self::nullableStr($diversificacion['nombre'] ?? null),
                'direccion_sucursal' => self::nullableStr($input['direccion_sucursal'] ?? null),
                'coordenadas' => self::nullableStr($input['coordenadas'] ?? null),
                'latitud' => self::nullableDecimal($input['latitud'] ?? null),
                'longitud' => self::nullableDecimal($input['longitud'] ?? null),
                'estado' => self::nullableStr($input['estado'] ?? null),
                'municipio' => self::nullableStr($input['municipio'] ?? null),
                'localidad' => self::nullableStr($input['localidad'] ?? null),
                'codigo_postal' => self::nullableStr($input['codigo_postal'] ?? null),
                'divisional_id' => self::nullableInt($input['divisional_id'] ?? null),
                'division_id' => self::nullableInt($input['division_id'] ?? null),
                'regional_id' => self::nullableInt($input['regional_id'] ?? null),
                'supervisor_id' => self::nullableInt($input['supervisor_id'] ?? null),
                'asesor_id' => self::nullableInt($input['asesor_id'] ?? null),
                'clasificacion_id' => self::nullableInt($input['clasificacion_id'] ?? null),
                'activo' => self::activoVal($input['activo'] ?? 1),
            ];
            if ($datos['coordenadas'] === null && $datos['latitud'] !== null && $datos['longitud'] !== null) {
                $datos['coordenadas'] = $datos['latitud'] . ',' . $datos['longitud'];
            }

            if ($id > 0) {
                $datos['id'] = $id;
                $db->CRUD("
                    UPDATE atlas_catalogo_sucursales
                    SET fk_sucursal = :fk_sucursal,
                        distribuidor_id = :distribuidor_id,
                        sucursal = :sucursal,
                        diversificacion_id = :diversificacion_id,
                        diversificacion = :diversificacion,
                        direccion_sucursal = :direccion_sucursal,
                        coordenadas = :coordenadas,
                        latitud = :latitud,
                        longitud = :longitud,
                        estado = :estado,
                        municipio = :municipio,
                        localidad = :localidad,
                        codigo_postal = :codigo_postal,
                        divisional_id = :divisional_id,
                        division_id = :division_id,
                        regional_id = :regional_id,
                        supervisor_id = :supervisor_id,
                        asesor_id = :asesor_id,
                        clasificacion_id = :clasificacion_id,
                        activo = :activo
                    WHERE id = :id
                ", $datos);
                return ['success' => true, 'mensaje' => 'Sucursal actualizada.'];
            }

            $db->CRUD("
                INSERT INTO atlas_catalogo_sucursales
                    (fk_sucursal, distribuidor_id, sucursal, diversificacion_id, diversificacion, direccion_sucursal, coordenadas,
                     latitud, longitud, estado, municipio, localidad, codigo_postal, divisional_id, division_id,
                     regional_id, supervisor_id, asesor_id, clasificacion_id, activo)
                VALUES
                    (:fk_sucursal, :distribuidor_id, :sucursal, :diversificacion_id, :diversificacion, :direccion_sucursal, :coordenadas,
                     :latitud, :longitud, :estado, :municipio, :localidad, :codigo_postal, :divisional_id, :division_id,
                     :regional_id, :supervisor_id, :asesor_id, :clasificacion_id, :activo)
            ", $datos);
            return ['success' => true, 'mensaje' => 'Sucursal agregada.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la sucursal.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarDivision(array $input): array
    {
        $divisionalId = self::nullableInt($input['divisional_id'] ?? null);
        if ($divisionalId === null) {
            return ['success' => false, 'mensaje' => 'Selecciona un divisional activo.'];
        }

        try {
            $db = new Database();
            $existe = $db->queryOne(
                "SELECT id FROM atlas_catalogo_divisionales WHERE id = :id AND activo = 1 LIMIT 1",
                ['id' => $divisionalId]
            );
            if (!$existe) {
                return ['success' => false, 'mensaje' => 'El divisional seleccionado no está activo.'];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo validar el divisional.', 'error' => $e->getMessage()];
        }

        return self::guardarSimple('atlas_catalogo_divisiones', [
            'divisional_id' => $divisionalId,
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ], self::intVal($input['id'] ?? 0), ['nombre'], 'división');
    }

    public static function guardarDistribuidor(array $input): array
    {
        return self::guardarSimple('atlas_catalogo_distribuidores', [
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ], self::intVal($input['id'] ?? 0), ['nombre'], 'distribuidor');
    }

    public static function guardarDiversificacion(array $input): array
    {
        return self::guardarSimple('atlas_catalogo_diversificaciones', [
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ], self::intVal($input['id'] ?? 0), ['nombre'], 'diversificación');
    }

    public static function guardarClasificacion(array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        $datos = [
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'icon_font' => self::nullableStr($input['icon_font'] ?? null),
            'color_hex' => self::normalizarColor($input['color_hex'] ?? null),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ];
        if ($id <= 0) {
            try {
                $db = new Database();
                $row = $db->queryOne("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM atlas_catalogo_clasificaciones");
                $datos['orden'] = (int)($row['siguiente'] ?? 1);
            } catch (\Throwable $e) {
                $datos['orden'] = null;
            }
        }

        return self::guardarSimple('atlas_catalogo_clasificaciones', $datos, $id, ['nombre'], 'clasificación');
    }

    public static function guardarOrdenClasificaciones(array $input): array
    {
        $ids = $input['ids'] ?? [];
        if (!is_array($ids) || count($ids) < 1) {
            return ['success' => false, 'mensaje' => 'No hay clasificaciones para ordenar.'];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($id) {
            return $id > 0;
        })));
        if (!$ids) {
            return ['success' => false, 'mensaje' => 'Orden inválido.'];
        }

        try {
            $db = new Database();
            $db->beginTransaction();
            foreach ($ids as $idx => $id) {
                $db->CRUD(
                    "UPDATE atlas_catalogo_clasificaciones SET orden = :orden WHERE id = :id",
                    ['orden' => $idx + 1, 'id' => $id]
                );
            }
            $db->commit();
            return ['success' => true, 'mensaje' => 'Orden actualizado.'];
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar el orden.', 'error' => $e->getMessage()];
        }
    }

    private static function guardarSimple(string $tabla, array $datos, int $id, array $requeridos, string $nombreEntidad): array
    {
        try {
            foreach ($requeridos as $campo) {
                if (trim((string)($datos[$campo] ?? '')) === '') {
                    return ['success' => false, 'mensaje' => 'Captura el nombre.'];
                }
            }

            $db = new Database();
            if ($id > 0) {
                $datos['id'] = $id;
                $sets = [];
                foreach (array_keys($datos) as $campo) {
                    if ($campo === 'id') {
                        continue;
                    }
                    $sets[] = "$campo = :$campo";
                }
                $db->CRUD("UPDATE $tabla SET " . implode(', ', $sets) . " WHERE id = :id", $datos);
                return ['success' => true, 'mensaje' => ucfirst($nombreEntidad) . ' actualizado.', 'id' => $id];
            }

            $campos = array_keys($datos);
            $db->CRUD(
                "INSERT INTO $tabla (" . implode(', ', $campos) . ") VALUES (:" . implode(', :', $campos) . ")",
                $datos
            );
            return ['success' => true, 'mensaje' => ucfirst($nombreEntidad) . ' agregado.', 'id' => $db->lastInsertId()];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudo guardar el catálogo.',
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function strVal($v): string
    {
        return trim((string)($v ?? ''));
    }

    private static function nullableStr($v): ?string
    {
        $s = self::strVal($v);
        return $s === '' ? null : $s;
    }

    private static function intVal($v): int
    {
        return (int)($v ?? 0);
    }

    private static function nullableInt($v): ?int
    {
        $n = (int)($v ?? 0);
        return $n > 0 ? $n : null;
    }

    private static function nullableDecimal($v): ?string
    {
        $s = self::strVal($v);
        if ($s === '' || !is_numeric($s)) {
            return null;
        }
        return $s;
    }

    private static function activoVal($v): int
    {
        return (int)$v === 1 ? 1 : 0;
    }

    private static function normalizarColor($v): ?string
    {
        $s = self::nullableStr($v);
        if ($s === null) {
            return null;
        }
        if ($s[0] !== '#') {
            $s = '#' . $s;
        }
        return preg_match('/^#[0-9a-f]{6}$/i', $s) ? strtoupper($s) : null;
    }
}
