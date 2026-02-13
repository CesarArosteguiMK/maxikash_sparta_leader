<?php

namespace Models;

use Core\DatabaseGeo;

/**
 * Direcciones alternas desde __SPARTA_SECRET_REDACTED__.oferta_coordenada.
 * fk_oferta = id_credito; columnas: coordenada_fat (DMS), direccion_maps, donde_firma.
 */
class OfertaCoordenada
{
    /**
     * Convierte coordenada en formato DMS (ej. 19°40'23.3"N 99°09'53.3"W) a decimal [lat, lng].
     * @param string $dms
     * @return array|null ['lat' => float, 'lng' => float] o null si no se puede parsear
     */
    public static function dmsToDecimal($dms)
    {
        $dms = trim((string) $dms);
        if ($dms === '') {
            return null;
        }
        // Formato: 19°40'23.3"N 99°09'53.3"W (grados ° minutos ' segundos " N/S espacio grados ° minutos ' segundos " E/W). Espacios opcionales.
        if (preg_match('/^\s*(\d+)\s*°\s*(\d+)\s*[\'′]\s*([\d.]+)\s*["″]\s*([NS])\s+(\d+)\s*°\s*(\d+)\s*[\'′]\s*([\d.]+)\s*["″]\s*([EW])\s*$/iu', $dms, $m)) {
            $latDeg = (float) $m[1];
            $latMin = (float) $m[2];
            $latSec = (float) $m[3];
            $latSign = strtoupper($m[4]) === 'S' ? -1 : 1;
            $lat = $latSign * ($latDeg + $latMin / 60 + $latSec / 3600);

            $lngDeg = (float) $m[5];
            $lngMin = (float) $m[6];
            $lngSec = (float) $m[7];
            $lngSign = strtoupper($m[8]) === 'W' ? -1 : 1;
            $lng = $lngSign * ($lngDeg + $lngMin / 60 + $lngSec / 3600);

            return ['lat' => round($lat, 6), 'lng' => round($lng, 6)];
        }
        return null;
    }

    /**
     * Obtiene filas de oferta_coordenada por id_credito (fk_oferta).
     * Devuelve array de [ 'lat' => float, 'lng' => float, 'direccion_maps' => string, 'donde_firma' => string ].
     */
    public static function getPorIdCredito($idCredito)
    {
        $idCredito = (int) $idCredito;
        if ($idCredito < 1) {
            return [];
        }
        try {
            $db = new DatabaseGeo();
            $sql = "SELECT coordenada_fat, direccion_maps, donde_firma FROM oferta_coordenada WHERE fk_oferta = :id ORDER BY idoferta_coordenada ASC";
            $rows = $db->queryAll($sql, ['id' => $idCredito]);
            if (empty($rows)) {
                return [];
            }
            $out = [];
            foreach ($rows as $row) {
                $coords = self::dmsToDecimal($row['coordenada_fat'] ?? '');
                if ($coords === null) {
                    continue;
                }
                $out[] = [
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'direccion_maps' => trim((string) ($row['direccion_maps'] ?? '')),
                    'donde_firma' => trim((string) ($row['donde_firma'] ?? '')),
                ];
            }
            return $out;
        } catch (\Exception $e) {
            return [];
        }
    }
}
