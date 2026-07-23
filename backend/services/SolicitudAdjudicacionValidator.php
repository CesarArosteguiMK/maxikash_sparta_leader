<?php

namespace Services;

final class SolicitudAdjudicacionValidator
{
    public const CANAL_ATC = 'ATC';
    public const CANAL_CALLCENTER = 'CALLCENTER';
    public const CANAL_DESPACHOS = 'DESPACHOS';
    private const CANALES_PERMITIDOS = ['ATC', 'CALLCENTER', 'DESPACHOS', 'CAMPO'];

    /**
     * @return array{valid:bool, errors:array<string,string>, data:array<string,mixed>}
     */
    public static function validarCreacion(array $input, string $canal = self::CANAL_ATC): array
    {
        $canal = strtoupper(trim($canal));
        $errors = [];
        if (!in_array($canal, self::CANALES_PERMITIDOS, true)) {
            $errors['canal'] = 'El canal de origen no es válido.';
        }
        $idCredito = (int) ($input['id_credito'] ?? 0);
        if ($idCredito <= 0) {
            $errors['id_credito'] = 'El ID de credito es obligatorio.';
        }

        $titular = self::normalizarBooleano($input['entregara_titular'] ?? null);
        if ($titular === null) {
            $errors['entregara_titular'] = 'Indica si entregara el titular.';
        }

        $nombreEntregante = self::texto($input['nombre_entregante'] ?? '', 180);
        $telefono = self::telefono($input['telefono_actual'] ?? '');
        $direccion = self::texto($input['direccion_resguardo'] ?? '', 500);
        $motivo = self::texto($input['motivo'] ?? '', 1000);
        $kilometraje = self::normalizarKilometraje($input['kilometraje'] ?? null);
        $vin = strtoupper(self::texto($input['vin'] ?? '', 17));
        $tipoAsignacion = strtoupper(self::texto($input['tipo_asignacion'] ?? '', 30));
        $idPersonaGestor = (int) ($input['id_persona_gestor'] ?? 0);
        $nombreGestor = self::texto($input['nombre_gestor'] ?? '', 180);

        if ($titular === false) {
            if ($nombreEntregante === '') {
                $errors['nombre_entregante'] = 'Captura el nombre de quien entregara la moto.';
            }
            if ($canal === self::CANAL_ATC && $kilometraje === null) {
                $errors['kilometraje'] = 'Captura un kilometraje valido entre 0 y 999999.';
            }
            if ($telefono === '') {
                $errors['telefono_actual'] = 'Captura un telefono de 10 a 15 digitos.';
            }
            if ($canal === self::CANAL_ATC && $direccion === '') {
                $errors['direccion_resguardo'] = 'Captura la direccion actual de resguardo.';
            }
            if ($motivo === '') {
                $errors['motivo'] = 'Captura el motivo de la solicitud.';
            }
        } else {
            $nombreEntregante = '';
            $kilometraje = null;
            $telefono = '';
            $direccion = '';
            $motivo = '';
        }

        if ($canal === self::CANAL_DESPACHOS) {
            if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
                $errors['vin'] = 'Captura un VIN valido de 17 caracteres.';
            }
            if (!in_array($tipoAsignacion, ['DESPACHO', 'EQUIPO_MAXIKASH'], true)) {
                $errors['tipo_asignacion'] = 'Selecciona Despacho o Equipo Maxikash.';
            }
            if ($tipoAsignacion === 'DESPACHO' && ($idPersonaGestor <= 0 || $nombreGestor === '')) {
                $errors['id_persona_gestor'] = 'Selecciona el gestor del despacho.';
            }
            if ($tipoAsignacion === 'EQUIPO_MAXIKASH') {
                $idPersonaGestor = 0;
                $nombreGestor = '';
            }
        } else {
            $vin = '';
            $tipoAsignacion = '';
            $idPersonaGestor = 0;
            $nombreGestor = '';
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'data' => [
                'id_credito' => $idCredito,
                'canal' => $canal,
                'entregara_titular' => $titular,
                'nombre_entregante' => $nombreEntregante !== '' ? $nombreEntregante : null,
                'kilometraje' => $kilometraje,
                'telefono_actual' => $telefono !== '' ? $telefono : null,
                'direccion_resguardo' => $direccion !== '' ? $direccion : null,
                'motivo' => $motivo !== '' ? $motivo : null,
                'vin' => $vin !== '' ? $vin : null,
                'tipo_asignacion' => $tipoAsignacion !== '' ? $tipoAsignacion : null,
                'id_persona_gestor' => $idPersonaGestor > 0 ? $idPersonaGestor : null,
                'nombre_gestor' => $nombreGestor !== '' ? $nombreGestor : null,
            ],
        ];
    }

    private static function normalizarBooleano($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 1 || $value === '1') {
            return true;
        }
        if ($value === 0 || $value === '0') {
            return false;
        }
        $value = strtolower(trim((string) $value));
        if (in_array($value, ['si', 'sí', 'yes', 'true'], true)) {
            return true;
        }
        if (in_array($value, ['no', 'false'], true)) {
            return false;
        }
        return null;
    }

    private static function normalizarKilometraje($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (preg_match('/^\s*-/', (string) $value)) {
            return null;
        }
        $raw = preg_replace('/[^0-9]/', '', (string) $value);
        if ($raw === '') {
            return null;
        }
        $km = (int) $raw;
        return $km >= 0 && $km <= 999999 ? $km : null;
    }

    private static function telefono($value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : '';
    }

    private static function texto($value, int $max): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
        return function_exists('mb_substr')
            ? mb_substr($value, 0, $max, 'UTF-8')
            : substr($value, 0, $max);
    }
}
