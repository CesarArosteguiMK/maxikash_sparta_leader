<?php

namespace Services;

final class GestionCampoEvidenciaPolicy
{
    public const PERFIL_LEGACY = 'legacy';
    public const PERFIL_ETAPA2 = 'etapa2_2026';

    private const BASE = [
        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
        'fis_vin', 'fis_frontal', 'fis_lateral_der', 'fis_trasera',
        'fis_lateral_izq', 'fis_tacometro', 'fis_360_encendida',
        'fis_video_vuelta_prueba', 'fis_checklist',
    ];

    public static function slotsRequeridos(string $perfil): array
    {
        $slots = self::BASE;
        if (self::normalizarPerfil($perfil) === self::PERFIL_ETAPA2) {
            array_splice($slots, 2, 0, ['fis_ine_frente', 'fis_ine_reverso']);
        } else {
            array_splice($slots, 8, 0, ['fis_video_cliente_acuerdo']);
        }
        return $slots;
    }

    public static function evaluar(array $slotsPresentes, string $perfil): array
    {
        $presentes = array_fill_keys(array_values(array_unique(array_filter(array_map('strval', $slotsPresentes)))), true);
        $requeridos = self::slotsRequeridos($perfil);
        $faltantes = array_values(array_filter($requeridos, static fn(string $slot): bool => empty($presentes[$slot])));
        return [
            'perfil' => self::normalizarPerfil($perfil),
            'completo' => $faltantes === [],
            'requeridos' => $requeridos,
            'faltantes' => $faltantes,
            'video_cliente_requerido' => self::normalizarPerfil($perfil) === self::PERFIL_LEGACY,
        ];
    }

    public static function normalizarPerfil(string $perfil): string
    {
        return strtolower(trim($perfil)) === self::PERFIL_ETAPA2 ? self::PERFIL_ETAPA2 : self::PERFIL_LEGACY;
    }
}

