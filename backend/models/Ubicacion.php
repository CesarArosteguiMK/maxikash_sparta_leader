<?php

namespace Models;

use Core\Database;
use Core\DatabaseAWS;
use Core\DatabaseSegundometro;

/**
 * Ubicaciones del cliente desde BD externa (AWS).
 * Obtiene idCliente desde idCredito, consulta tabla ubicacion,
 * filtra duplicados y agrupa por proximidad (Haversine 30-50m).
 */
class Ubicacion
{
    /** Radio de la Tierra en metros para Haversine */
    private const RADIO_TIERRA_M = 6371000;

    /** Distancia mínima (m) para considerar dos puntos como la misma ubicación */
    private const DISTANCIA_AGRUPAR_M = 50;

    /** Si tras agrupar a 50m hay pocos puntos, reagrupar con esta distancia (m) para permitir más ubicaciones */
    private const DISTANCIA_AGRUPAR_STRICT_M = 30;

    /** Número mínimo de ubicaciones a intentar mostrar cuando hay pocas tras el primer agrupado */
    private const MIN_PUNTOS_SI_POCOS = 6;

    /** Umbral de repeticiones en el tiempo para marcar "Punto de Interés" */
    private const UMBRAL_PUNTO_INTERES = 3;

    /**
     * Obtiene id_cliente a partir de id_credito (desde segundometro).
     */
    public static function getIdClientePorIdCredito($idCredito)
    {
        $idCredito = (int) $idCredito;
        if ($idCredito < 1) {
            return null;
        }
        try {
            $db = new DatabaseSegundometro();
            $row = $db->queryOne(
                "SELECT Id_cliente FROM tbl_segundometro_semana WHERE Id_credito = :id LIMIT 1",
                ['id' => $idCredito]
            );
            return isset($row['Id_cliente']) ? (int) $row['Id_cliente'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Consulta tabla ubicacion en BD externa AWS por id_cliente.
     * Espera columnas: id_cliente, latitud, longitud, y opcionalmente fecha_creacion / created_at.
     * Si la tabla está en otro esquema (__SPARTA_SECRET_REDACTED__), configurar esa conexión en Core\DatabaseAWS o crear Core\DatabaseMovil.
     */
    public static function getUbicacionesBrutasPorIdCliente($idCliente)
    {
        $idCliente = (int) $idCliente;
        if ($idCliente < 1) {
            return [];
        }
        try {
            $db = new DatabaseAWS();
            // Ajustar nombre de tabla si en tu BD es distinto (ej. ubicaciones). Columnas: latitud, longitud, opcional fecha_creacion
            $sql = "SELECT latitud, longitud, fecha_creacion AS fecha FROM ubicacion WHERE idCliente = :id_cliente ORDER BY fecha_creacion ASC";
            $rows = $db->queryAll($sql, ['id_cliente' => $idCliente]);
            return is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            // Si la tabla no existe o está en otro esquema, devolver vacío
            return [];
        }
    }

    /**
     * Distancia entre dos puntos en metros (fórmula de Haversine).
     */
    public static function haversineMetros($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = (float) $lat1;
        $lon1 = (float) $lon1;
        $lat2 = (float) $lat2;
        $lon2 = (float) $lon2;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return self::RADIO_TIERRA_M * $c;
    }

    /**
     * Elimina duplicados exactos (misma lat/lon).
     */
    private static function eliminarDuplicadosExactos(array $puntos)
    {
        $vistos = [];
        $out = [];
        foreach ($puntos as $p) {
            $lat = round((float) ($p['latitud'] ?? 0), 6);
            $lon = round((float) ($p['longitud'] ?? 0), 6);
            $k = "{$lat}_{$lon}";
            if (!isset($vistos[$k])) {
                $vistos[$k] = true;
                $out[] = $p;
            }
        }
        return $out;
    }

    /**
     * Agrupa puntos que estén entre 30 y 50m (usa centro del grupo).
     */
    private static function filtrarPorProximidad(array $puntos, $distanciaMetros = 50)
    {
        if (empty($puntos)) {
            return [];
        }
        $grupos = [];
        foreach ($puntos as $p) {
            $lat = (float) ($p['latitud'] ?? 0);
            $lon = (float) ($p['longitud'] ?? 0);
            $encontrado = false;
            foreach ($grupos as &$g) {
                $d = self::haversineMetros($lat, $lon, $g['latitud'], $g['longitud']);
                if ($d <= $distanciaMetros) {
                    $g['contador']++;
                    $g['latitud'] = ($g['latitud'] * ($g['contador'] - 1) + $lat) / $g['contador'];
                    $g['longitud'] = ($g['longitud'] * ($g['contador'] - 1) + $lon) / $g['contador'];
                    if (isset($p['fecha'])) {
                        $g['fechas'][] = $p['fecha'];
                    }
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                $grupos[] = [
                    'latitud' => $lat,
                    'longitud' => $lon,
                    'contador' => 1,
                    'fechas' => isset($p['fecha']) ? [$p['fecha']] : [],
                ];
            }
        }
        $resultado = [];
        foreach ($grupos as $g) {
            $resultado[] = [
                'latitud' => $g['latitud'],
                'longitud' => $g['longitud'],
                'cantidad_registros' => $g['contador'],
                'punto_de_interes' => $g['contador'] >= self::UMBRAL_PUNTO_INTERES,
            ];
        }
        return $resultado;
    }

    /**
     * Obtiene ubicaciones filtradas para un id_credito: idCliente desde segundometro,
     * raw desde AWS, sin duplicados y agrupadas por proximidad; puntos de interés marcados.
     *
     * Lógica de agrupado:
     * 1) General (50m): ver el bosque completo; si el cliente se movió por una manzana, todo es un solo lugar.
     * 2) Si quedan 3 o menos puntos: "3 puntos son muy pocos para rastrear", se relaja.
     * 3) Zoom (30m): reagrupar a 30m para distinguir dos casas o casa + tienda en la misma esquina.
     */
    public static function getUbicacionesFiltradasPorIdCredito($idCredito)
    {
        $idCliente = self::getIdClientePorIdCredito($idCredito);
        if ($idCliente === null) {
            return ['success' => true, 'mensaje' => 'Sin id_cliente para este crédito.', 'id_cliente' => null, 'direcciones_resumen' => [], 'puntos_mapa' => []];
        }
        $brutos = self::getUbicacionesBrutasPorIdCliente($idCliente);
        if (empty($brutos)) {
            return ['success' => true, 'mensaje' => 'Sin ubicaciones en BD externa.', 'id_cliente' => $idCliente, 'direcciones_resumen' => [], 'puntos_mapa' => []];
        }
        $sinDuplicados = self::eliminarDuplicadosExactos($brutos);
        $puntosMapa = self::filtrarPorProximidad($sinDuplicados, self::DISTANCIA_AGRUPAR_M);
        // Si hay 3 o menos ubicaciones, reagrupar con distancia menor para permitir más puntos (hasta 5-6)
        if (count($puntosMapa) <= 3 && count($sinDuplicados) > count($puntosMapa)) {
            $puntosMapaStrict = self::filtrarPorProximidad($sinDuplicados, self::DISTANCIA_AGRUPAR_STRICT_M);
            if (count($puntosMapaStrict) >= self::MIN_PUNTOS_SI_POCOS || count($puntosMapaStrict) > count($puntosMapa)) {
                $puntosMapa = $puntosMapaStrict;
            }
        }
        // Ordenar por más visitados (cantidad_registros) y quedarnos con las 5-6 más usadas
        usort($puntosMapa, function ($a, $b) {
            return ($b['cantidad_registros'] ?? 0) <=> ($a['cantidad_registros'] ?? 0);
        });
        $puntosMapa = array_slice($puntosMapa, 0, self::MIN_PUNTOS_SI_POCOS);
        $direccionesResumen = [];
        foreach ($puntosMapa as $i => $p) {
            $etiqueta = ($p['punto_de_interes'] ?? false) ? 'Punto de interés' : 'Menos frecuente';
            $visitas = (int) ($p['cantidad_registros'] ?? 1);
            $direccionesResumen[] = [
                'orden' => $i + 1,
                'lat' => $p['latitud'],
                'lng' => $p['longitud'],
                'texto' => $etiqueta,
                'punto_de_interes' => $p['punto_de_interes'] ?? false,
                'cantidad_registros' => $visitas,
            ];
        }
        return [
            'success' => true,
            'id_cliente' => $idCliente,
            'direcciones_resumen' => $direccionesResumen,
            'puntos_mapa' => $puntosMapa,
        ];
    }
}
