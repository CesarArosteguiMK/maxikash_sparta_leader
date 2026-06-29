<?php

namespace Services;

class FactorIntegracionService
{
    private const DIAS_ANIO = 365;
    private const AGUINALDO_DIAS_DEFAULT = 15;
    private const PRIMA_VACACIONAL_DEFAULT = 0.25;

    public static function diasVacacionesPorAniversario(int $aniversario): int
    {
        if ($aniversario <= 0) return 0;
        if ($aniversario === 1) return 12;
        if ($aniversario === 2) return 14;
        if ($aniversario === 3) return 16;
        if ($aniversario === 4) return 18;
        if ($aniversario === 5) return 20;
        if ($aniversario <= 10) return 22;
        if ($aniversario <= 15) return 24;
        if ($aniversario <= 20) return 26;
        if ($aniversario <= 25) return 28;
        if ($aniversario <= 30) return 30;
        return 32;
    }

    public static function calcularFactor(
        int $aniversario,
        ?int $diasAguinaldo = null,
        ?int $diasVacaciones = null,
        ?float $primaVacacional = null
    ): array {
        $aniversario = max(0, $aniversario);
        $aguinaldo = $diasAguinaldo ?? self::AGUINALDO_DIAS_DEFAULT;
        $vacaciones = $diasVacaciones ?? self::diasVacacionesPorAniversario($aniversario);
        $prima = $primaVacacional ?? self::PRIMA_VACACIONAL_DEFAULT;

        $factor = 1 + ($aguinaldo / self::DIAS_ANIO) + (($vacaciones * $prima) / self::DIAS_ANIO);

        return [
            'aniversario' => $aniversario,
            'aguinaldo_dias' => $aguinaldo,
            'vacaciones_dias' => $vacaciones,
            'prima_vacacional' => $prima,
            'prima_vacacional_porcentaje' => $prima * 100,
            'factor_integracion' => round($factor, 6),
            'salario_diario_variable' => 'X',
            'formula_factor' => '((aguinaldo_dias / 365) + ((vacaciones_dias * prima_vacacional) / 365) + 1)',
            'formula_sdi' => 'X * factor_integracion',
        ];
    }

    public static function calcularSalarioIntegrado(float $salarioDiario, int $aniversario): array
    {
        $datos = self::calcularFactor($aniversario);
        $datos['salario_diario'] = round($salarioDiario, 2);
        $datos['salario_integrado'] = round($salarioDiario * (float) $datos['factor_integracion'], 2);

        return $datos;
    }
}
