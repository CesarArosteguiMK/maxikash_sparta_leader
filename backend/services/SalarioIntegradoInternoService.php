<?php

namespace Services;

use Core\Database;
use DateTimeImmutable;
use DateTimeZone;
use Models\CapHum;

/**
 * Cálculo interno de SDI: no persiste el resultado ni debe exponerse desde
 * controladores o respuestas HTTP. Lee el sueldo bruto cifrado sólo durante
 * la operación y lo descarta al finalizar.
 */
final class SalarioIntegradoInternoService
{
    private const TZ = 'America/Mexico_City';

    /**
     * @return array{factor_integracion: float, salario_diario: float, salario_integrado: float}|null
     */
    public static function calcularParaPersona(int $idPersona, ?DateTimeImmutable $referencia = null): ?array
    {
        if ($idPersona <= 0) {
            return null;
        }

        $salario = CapHum::getSalarioSensiblePersona($idPersona);
        $datosSalario = is_array($salario['datos'] ?? null) ? $salario['datos'] : [];
        if (empty($salario['success']) || empty($datosSalario['tiene_salario'])) {
            return null;
        }

        $mensual = self::normalizarMonto($datosSalario['salario'] ?? null);
        if ($mensual === null) {
            return null;
        }

        $db = new Database();
        $persona = $db->queryOne(
            'SELECT fecha_ingreso FROM estado_cuenta.persona WHERE id = :id_persona LIMIT 1',
            ['id_persona' => $idPersona]
        );
        $ingreso = self::fecha((string) ($persona['fecha_ingreso'] ?? ''));
        if (!$ingreso) {
            return null;
        }

        $hoy = $referencia ?: new DateTimeImmutable('today', new DateTimeZone(self::TZ));
        $aniversario = max(0, (int) $ingreso->diff($hoy)->y);
        $integrado = FactorIntegracionService::calcularSalarioIntegradoDesdeMensual($mensual, $aniversario);

        return [
            'factor_integracion' => (float) $integrado['factor_integracion'],
            'salario_diario' => (float) $integrado['salario_diario'],
            'salario_integrado' => (float) $integrado['salario_integrado'],
        ];
    }

    private static function fecha(string $valor): ?DateTimeImmutable
    {
        $valor = trim($valor);
        if ($valor === '' || $valor === '0000-00-00') {
            return null;
        }
        try {
            return new DateTimeImmutable($valor, new DateTimeZone(self::TZ));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function normalizarMonto($valor): ?float
    {
        $limpio = preg_replace('/[^0-9.\-]/', '', (string) $valor) ?? '';
        if ($limpio === '' || !is_numeric($limpio)) {
            return null;
        }
        $monto = (float) $limpio;
        return is_finite($monto) && $monto >= 0 ? $monto : null;
    }
}
