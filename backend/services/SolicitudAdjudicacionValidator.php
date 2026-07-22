<?php

namespace Services;

final class SolicitudAdjudicacionValidator
{
    public const CANAL_ATC = 'ATC';
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

        if ($titular === false) {
            if ($nombreEntregante === '') {
                $errors['nombre_entregante'] = 'Captura el nombre de quien entregara la moto.';
            }
            if ($kilometraje === null) {
                $errors['kilometraje'] = 'Captura un kilometraje valido entre 0 y 999999.';
            }
            if ($telefono === '') {
                $errors['telefono_actual'] = 'Captura un telefono de 10 a 15 digitos.';
            }
            if ($direccion === '') {
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
