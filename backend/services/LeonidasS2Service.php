<?php

namespace Services;

use Controllers\EstadoCuenta;

/**
 * Read-only adapter for the S2 Estado de Cuenta API already used by Sparta.
 */
class LeonidasS2Service
{
    /** @return array<string, mixed> */
    public function consultarCredito(int $idCredito, ?string $fechaCorte = null): array
    {
        if ($idCredito <= 0) {
            throw new \InvalidArgumentException('El id de credito debe ser mayor que cero.');
        }

        $fechaCorte = $this->normalizarFecha($fechaCorte);
        $controller = new EstadoCuenta();
        $method = $this->resolverMetodoEstadoCuenta($controller);
        if ($method === null) {
            throw new \RuntimeException('El conector de S2 no esta disponible en esta version de Sparta.');
        }

        $response = $controller->{$method}($idCredito, $fechaCorte, 15);
        if (empty($response['ok'])) {
            $message = trim((string) ($response['error'] ?? 'S2 no devolvio informacion para el credito solicitado.'));
            throw new \RuntimeException($message !== '' ? $message : 'S2 no respondio correctamente.');
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $payments = $this->buscarLista($data, ['datospagos', 'pagos', 'paymentdata']);
        $paymentSummary = $this->resumirPagos($payments);
        $paymentsTotal = $this->sumarPagos($payments);

        $metrics = [
            'id_credito' => $idCredito,
            'fecha_corte' => $fechaCorte,
            'pagos_registrados' => count($payments),
            'ultimo_pago_fecha' => $paymentSummary['fecha'],
            'ultimo_pago_monto' => $paymentSummary['monto'],
            'saldo' => $this->buscarEscalar($data, ['saldoparaliquidarv2', 'saldoparaliquidar', 'saldototal', 'saldoactual', 'saldo', 'saldocapital']),
            'adeudo_total' => $this->buscarEscalar($data, ['adeudototal']),
            'saldo_vencido' => $this->buscarEscalar($data, ['saldototalvencido', 'saldovencido', 'montovencido', 'vencido']),
            'mora' => $this->buscarEscalar($data, ['mora', 'diasmora', 'diasatraso']),
            'estatus' => $this->buscarEscalar($data, ['statuscredito', 'estatuscredito', 'estadocredito', 'estatus', 'estado']),
            'cliente' => $this->buscarEscalar($data, ['nombrecliente', 'cliente', 'nombrecompleto']),
            'cuotas_contratadas' => $this->buscarEscalar($data, ['numeroamortizaciones', 'cuotascontratadas', 'numerocuotas']),
            'cuotas_pagadas' => $this->buscarEscalar($data, ['numcuotaspagadas', 'cuotaspagadas', 'numeroabonos']),
            'abonos_total' => $paymentsTotal,
            'monto_otorgado' => $this->buscarEscalar($data, ['montootorgado', 'montoasignado', 'montoentregado', 'montoautorizado', 'capitalotorgado']),
            'cuota' => $this->buscarEscalar($data, ['cuota', 'montocuota', 'pagoperiodico']),
            'periodicidad' => $this->buscarEscalar($data, ['periodicidad', 'frecuenciapago']),
            'producto' => $this->buscarEscalar($data, ['producto', 'nombreproducto', 'tipocredito']),
            'sucursal' => $this->buscarEscalar($data, ['sucursal', 'secursal', 'nombresucursal']),
            'fecha_credito' => $this->buscarEscalar($data, ['fechainicio', 'fechacredito', 'fechaotorgamiento', 'fechaapertura']),
            'fecha_liquidacion' => $this->buscarEscalar($data, ['fechaliquidacion', 'fechafiniquito']),
            'siguiente_pago' => $this->buscarEscalar($data, ['fechasiguientepago', 'siguientepago']),
        ];

        return [
            'fuente' => 's2_estado_cuenta',
            'consultado_at' => (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s'),
            'metricas' => array_filter($metrics, static fn($value): bool => $value !== null && $value !== ''),
            'pagos' => $this->normalizarPagos($payments),
        ];
    }

    private function resolverMetodoEstadoCuenta(EstadoCuenta $controller): ?string
    {
        foreach (get_class_methods($controller) as $method) {
            if (strpos($method, 'api_') !== 0 || substr($method, -9) === '_parallel') {
                continue;
            }

            try {
                $reflection = new \ReflectionMethod($controller, $method);
                if (
                    $reflection->isPublic()
                    && $reflection->getNumberOfParameters() === 3
                    && $reflection->getNumberOfRequiredParameters() <= 2
                ) {
                    return $method;
                }
            } catch (\ReflectionException $error) {
                continue;
            }
        }

        return null;
    }

    private function normalizarFecha(?string $fecha): string
    {
        if ($fecha === null || trim($fecha) === '') {
            return (new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d');
        }
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($fecha));
        if (!$parsed || $parsed->format('Y-m-d') !== trim($fecha)) {
            throw new \InvalidArgumentException('La fecha de corte debe tener formato YYYY-MM-DD.');
        }
        return $parsed->format('Y-m-d');
    }

    /** @param array<string, mixed> $data @param string[] $keys @return array<int, array<string, mixed>> */
    private function buscarLista(array $data, array $keys): array
    {
        foreach ($data as $key => $value) {
            if (in_array($this->normalizarClave((string) $key), $keys, true) && is_array($value)) {
                return array_values(array_filter($value, 'is_array'));
            }
            if (is_array($value)) {
                $found = $this->buscarLista($value, $keys);
                if ($found !== []) {
                    return $found;
                }
            }
        }
        return [];
    }

    /** @param array<string, mixed> $data @param string[] $keys */
    private function buscarEscalar(array $data, array $keys)
    {
        foreach ($data as $key => $value) {
            if (in_array($this->normalizarClave((string) $key), $keys, true) && is_scalar($value)) {
                return $value;
            }
        }
        foreach ($data as $value) {
            if (!is_array($value)) {
                continue;
            }
            $found = $this->buscarEscalar($value, $keys);
            if ($found !== null && $found !== '') {
                return $found;
            }
        }
        return null;
    }

    /** @param array<int, array<string, mixed>> $payments @return array{fecha:?string,monto:mixed} */
    private function resumirPagos(array $payments): array
    {
        $latest = null;
        $latestTimestamp = 0;
        foreach ($payments as $payment) {
            $date = $this->buscarEscalar($payment, ['fechadeposito', 'fecharegistro', 'fechavalor', 'fecha']);
            $timestamp = is_numeric($date) ? (int) $date : strtotime((string) $date);
            if ($timestamp && $timestamp >= $latestTimestamp) {
                $latestTimestamp = $timestamp;
                $latest = $payment;
            }
        }
        if ($latest === null) {
            return ['fecha' => null, 'monto' => null];
        }
        return [
            'fecha' => date('Y-m-d', $latestTimestamp),
            'monto' => $this->buscarEscalar($latest, ['montopago', 'monto', 'importe', 'cantidad', 'pagototal']),
        ];
    }

    /** @param array<int, array<string, mixed>> $payments */
    private function sumarPagos(array $payments): float
    {
        $total = 0.0;
        foreach ($payments as $payment) {
            $amount = $this->buscarEscalar($payment, ['montopago', 'monto', 'importe', 'cantidad', 'pagototal']);
            if (is_numeric($amount)) {
                $total += (float) $amount;
            }
        }
        return round($total, 2);
    }

    /** @param array<int, array<string, mixed>> $payments @return list<array<string,mixed>> */
    private function normalizarPagos(array $payments, int $limite = 10): array
    {
        $normalizados = [];
        foreach ($payments as $payment) {
            $fecha = $this->buscarEscalar($payment, ['fechadeposito', 'fecharegistro', 'fechavalor', 'fecha']);
            $timestamp = is_numeric($fecha) ? (int) $fecha : strtotime((string) $fecha);
            $normalizados[] = [
                'fecha' => $timestamp ? date('Y-m-d H:i:s', $timestamp) : trim((string) $fecha),
                'monto' => $this->buscarEscalar($payment, ['montopago', 'monto', 'importe', 'cantidad', 'pagototal']),
                'referencia' => $this->buscarEscalar($payment, ['referencia', 'foliopago', 'folio', 'idpago']),
                'estatus' => $this->buscarEscalar($payment, ['estatuspago', 'statuspago', 'estatus', 'status']),
                '_timestamp' => $timestamp ?: 0,
            ];
        }

        usort($normalizados, static fn(array $a, array $b): int => ($b['_timestamp'] ?? 0) <=> ($a['_timestamp'] ?? 0));
        $normalizados = array_slice($normalizados, 0, max(1, min(25, $limite)));
        foreach ($normalizados as &$pago) {
            unset($pago['_timestamp']);
            $pago = array_filter($pago, static fn($value): bool => $value !== null && $value !== '');
        }
        unset($pago);

        return array_values($normalizados);
    }

    private function normalizarClave(string $key): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', mb_strtolower($key, 'UTF-8'));
        return preg_replace('/[^a-z0-9]/', '', $ascii === false ? $key : $ascii) ?: '';
    }
}
