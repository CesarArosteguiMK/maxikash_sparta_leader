<?php

namespace Services;

/**
 * Especialista de lectura para Estado de Cuenta y cobranza.
 *
 * Las respuestas generales salen de reglas operativas deterministas. Cuando
 * existe un credito, consulta las mismas fuentes de Sparta/S2 para explicar el
 * caso concreto. Este servicio nunca modifica datos.
 */
final class LeonidasEstadoCuentaService
{
    /** @var callable(int): array */
    private $consultarS2;

    /** @var callable(int): array */
    private $consultarGastos;

    /** @var callable(int): array */
    private $consultarGestionExterna;

    /** @var callable(int): mixed */
    private $consultarUltimoPago;

    /** @var callable(): array */
    private $consultarMotivosCondonacion;

    /** @var callable(int,array): ?array */
    private $consultarBucket;

    public function __construct(
        ?callable $consultarS2 = null,
        ?callable $consultarGastos = null,
        ?callable $consultarGestionExterna = null,
        ?callable $consultarUltimoPago = null,
        ?callable $consultarMotivosCondonacion = null,
        ?callable $consultarBucket = null
    ) {
        $this->consultarS2 = $consultarS2
            ?? static fn(int $credito): array => (new LeonidasS2Service())->consultarCredito($credito);
        $this->consultarGastos = $consultarGastos
            ?? static fn(int $credito): array => \Models\EstadoCuenta::getGastosCobranza($credito);
        $this->consultarGestionExterna = $consultarGestionExterna
            ?? static fn(int $credito): array => \Models\EstadoCuenta::getDatosGestionExternaCredito($credito);
        $this->consultarUltimoPago = $consultarUltimoPago
            ?? static fn(int $credito) => \Models\EstadoCuenta::obtenerUltimoPagoEfectivoSegundometroParaCredito($credito);
        $this->consultarMotivosCondonacion = $consultarMotivosCondonacion
            ?? static fn(): array => \Models\EstadoCuenta::getCatalogoMotivosCondonacion();
        $this->consultarBucket = $consultarBucket
            ?? static function (int $credito, array $contexto): ?array {
                $consulta = "explica en que bucket esta el credito {$credito}";
                return (new LeonidasAnaliticaService())->resolver($consulta, $consulta, $contexto);
            };
    }

    public function resolver(string $mensaje, array $contexto, ?string $normalizado = null): ?array
    {
        $normalizado = $this->normalizar($normalizado ?? $mensaje);
        if (!$this->esConsulta($normalizado) || $this->esOrdenDeModificacion($normalizado)) {
            return null;
        }

        $credito = $this->extraerCredito($normalizado);

        if ($this->esConsultaCargosMoratorios($normalizado)) {
            if (!$this->permitido($contexto, 'gastos_cobranza')) {
                return $this->denegado('Gastos de Cobranza');
            }
            return $this->resolverCargosMoratorios($credito);
        }

        if ($this->esConsultaAclaracion($normalizado)) {
            if (
                !$this->permitido($contexto, 'estado_cuenta')
                && !$this->permitido($contexto, 'aclaraciones_credito')
            ) {
                return $this->denegado('Estado de Cuenta y Aclaraciones');
            }
            return $this->resolverAclaracion($credito);
        }

        if ($this->esConsultaCondonacion($normalizado)) {
            if (!$this->permitido($contexto, 'gastos_cobranza')) {
                return $this->denegado('Gastos de Cobranza');
            }
            return $this->resolverMotivosCondonacion();
        }

        if ($this->esConsultaDomiciliacion($normalizado)) {
            if (!$this->permitido($contexto, 'estado_cuenta')) {
                return $this->denegado('Estado de Cuenta');
            }
            return $this->resolverDatoS2($credito, 'domiciliado');
        }

        if ($this->esConsultaAdeudo($normalizado)) {
            if (!$this->permitido($contexto, 'estado_cuenta')) {
                return $this->denegado('Estado de Cuenta');
            }
            return $this->resolverDatoS2($credito, 'adeudo');
        }

        if ($this->esConsultaCuentaConcentradora($normalizado)) {
            if (!$this->permitido($contexto, 'estado_cuenta')) {
                return $this->denegado('Estado de Cuenta');
            }
            return $this->resolverDatoS2($credito, 'cuenta_concentradora');
        }

        if ($this->esConsultaBucket($normalizado)) {
            if (!$this->permitido($contexto, 'analitica') || !$this->permitido($contexto, 'bucket')) {
                return $this->denegado('Analitica de Bucket');
            }
            if ($credito === null) {
                return $this->respuesta(
                    'bucket',
                    'El bucket es la subarea operativa de cobranza determinada por la mora y por el corte de la fuente consultada. Para decirte si un credito esta en 1-7, 8-30, 30+ u otra celula necesito su ID; con el ID comparare el corte, los dias de mora y la fuente que coloco el credito en ese bucket.',
                    'reglas_operativas_bucket'
                );
            }

            try {
                $respuesta = ($this->consultarBucket)($credito, $contexto);
                if (is_array($respuesta)) {
                    return $respuesta;
                }
            } catch (\Throwable $error) {
                // Se devuelve un diagnostico controlado debajo.
            }

            return $this->respuesta(
                'bucket_error',
                "No pude completar el diagnostico de bucket del credito {$credito}. La fuente operativa no estuvo disponible; no se realizo ningun cambio.",
                'diagnostico_bucket',
                ['credito' => $credito]
            );
        }

        return null;
    }

    private function resolverCargosMoratorios(?int $credito): array
    {
        if ($credito === null) {
            return $this->respuesta(
                'cargos_moratorios',
                'Sparta puede impedir aplicar cargos moratorios cuando el credito esta en gestion externa restringida, no tiene un gasto pendiente aplicable, el cargo ya fue pagado o condonado, el importe no es valido o el perfil no tiene acceso a Gastos de Cobranza. Con el ID del credito puedo identificar cual de esas reglas lo esta bloqueando y mostrar el saldo pendiente real.',
                'reglas_gastos_cobranza'
            );
        }

        try {
            $gestion = ($this->consultarGestionExterna)($credito);
            $gastos = ($this->consultarGastos)($credito);
        } catch (\Throwable $error) {
            return $this->respuesta(
                'cargos_moratorios_error',
                "No pude consultar los cargos del credito {$credito}. La fuente de Gastos de Cobranza no estuvo disponible; no se realizo ningun cambio.",
                'modelo_estado_cuenta',
                ['credito' => $credito]
            );
        }

        $restringida = !empty($gestion['activa']);
        $etiqueta = trim((string) ($gestion['etiqueta_celula'] ?? 'gestion externa'));
        $resumen = $this->resumirGastos($gastos);

        if ($restringida) {
            $mensaje = "El credito {$credito} no permite aplicar cargos moratorios porque esta asignado a {$etiqueta}. "
                . 'Sparta bloquea los gastos mientras exista esa gestion externa activa para evitar cobros duplicados.';
        } elseif ($resumen['cantidad'] === 0 || $resumen['pendiente'] <= 0.0) {
            $mensaje = "El credito {$credito} no tiene cargos de cobranza pendientes aplicables. "
                . 'Los cargos consultados estan pagados, condonados o no existe un gasto activo con saldo pendiente.';
        } else {
            $mensaje = "El credito {$credito} tiene {$resumen['cantidad']} cargo(s) activo(s) con "
                . $this->moneda($resumen['pendiente'])
                . ' pendientes. No aparece una restriccion por gestion externa; si la pantalla aun impide aplicarlos, debe revisarse el permiso del usuario y el importe capturado.';
        }

        return $this->respuesta('cargos_moratorios', $mensaje, 'modelo_estado_cuenta', [
            'credito' => $credito,
            'gestion_externa' => $gestion,
            'gastos' => $resumen,
        ]);
    }

    private function resolverAclaracion(?int $credito): array
    {
        $regla = 'Sparta no usa un SLA fijo para afirmar que todo pago ya impacto. Para la validacion de "falta aplicar", de martes a domingo el ultimo pago efectivo debe ser de ayer o de hoy; los lunes solo se acepta un pago del mismo lunes. Los pagos realizados en fin de semana normalmente deben revisarse el martes. La aclaracion y el reflejo final dependen de que S2 concilie el movimiento.';
        if ($credito === null) {
            return $this->respuesta('tiempo_aclaracion', $regla, 'reglas_aclaraciones_estado_cuenta');
        }

        try {
            $ultimoPago = ($this->consultarUltimoPago)($credito);
        } catch (\Throwable $error) {
            $ultimoPago = null;
        }

        $fecha = $this->extraerFecha($ultimoPago);
        $detalle = $fecha !== null
            ? " Para el credito {$credito}, Segundometro reporta como ultimo pago efectivo el {$fecha}."
            : " Para el credito {$credito}, Segundometro no devolvio una fecha de ultimo pago efectivo; no puedo afirmar que ya impacto.";

        return $this->respuesta(
            'tiempo_aclaracion',
            $regla . $detalle,
            'reglas_aclaraciones_y_segundometro',
            ['credito' => $credito, 'ultimo_pago_efectivo' => $fecha]
        );
    }

    private function resolverMotivosCondonacion(): array
    {
        try {
            $catalogo = ($this->consultarMotivosCondonacion)();
        } catch (\Throwable $error) {
            $catalogo = [];
        }

        $motivos = [];
        foreach ($catalogo as $item) {
            if (is_array($item)) {
                $motivo = trim((string) ($item['motivo'] ?? $item['nombre'] ?? ''));
                if ($motivo !== '') {
                    $motivos[] = $motivo;
                }
            } elseif (is_string($item) && trim($item) !== '') {
                $motivos[] = trim($item);
            }
        }
        $motivos = array_values(array_unique($motivos));

        $texto = $motivos
            ? implode(', ', $motivos)
            : 'Campana Call Center, credito liquidado, Convenios, Siniestros y error de sistema';

        return $this->respuesta(
            'motivos_condonacion',
            "Los motivos vigentes para condonar cargos de cobranza son: {$texto}. La condonacion debe corresponder al caso real, requiere permiso y queda auditada. En una condonacion parcial el importe debe ser mayor que cero, menor que el saldo pendiente y la justificacion debe tener al menos 25 caracteres.",
            'catalogo_motivos_condonacion',
            ['motivos' => $motivos]
        );
    }

    private function resolverDatoS2(?int $credito, string $dato): array
    {
        if ($credito === null) {
            $solicitud = match ($dato) {
                'domiciliado' => 'si esta domiciliado',
                'adeudo' => 'el saldo total adeudado',
                default => 'la cuenta o CLABE concentradora',
            };
            return $this->respuesta(
                $dato,
                "Necesito el ID del credito para consultar {$solicitud} en S2. No voy a inferir ese dato por nombre ni a inventar una cuenta bancaria.",
                's2_estado_cuenta'
            );
        }

        try {
            $resultado = ($this->consultarS2)($credito);
            $metricas = is_array($resultado['metricas'] ?? null) ? $resultado['metricas'] : [];
        } catch (\Throwable $error) {
            return $this->respuesta(
                $dato . '_error',
                "No pude consultar el credito {$credito} en S2: " . trim($error->getMessage()) . ' No se realizo ningun cambio.',
                's2_estado_cuenta',
                ['credito' => $credito]
            );
        }

        if ($dato === 'domiciliado') {
            $valor = $metricas['domiciliado'] ?? null;
            $mensaje = $valor !== null && $valor !== ''
                ? "S2 reporta que el credito {$credito} tiene domiciliacion: " . $this->siNo($valor)
                    . '. Este resultado proviene del estado de cuenta consultado; no se deduce por el producto ni por pagos anteriores.'
                : "S2 respondio para el credito {$credito}, pero no devolvio el campo de domiciliacion. No puedo afirmar que sea domiciliado solo por su producto o historial.";
        } elseif ($dato === 'adeudo') {
            $valor = $metricas['adeudo_total'] ?? $metricas['saldo'] ?? null;
            $mensaje = is_numeric($valor)
                ? "El saldo total adeudado del credito {$credito}, al corte consultado en S2, es " . $this->moneda((float) $valor) . '.'
                : "S2 respondio para el credito {$credito}, pero no devolvio un saldo o adeudo total utilizable. No puedo calcularlo sin esa fuente.";
        } else {
            $cuenta = trim((string) ($metricas['cuenta_concentradora'] ?? ''));
            $clabe = trim((string) ($metricas['clabe_concentradora'] ?? ''));
            $banco = trim((string) ($metricas['banco_concentrador'] ?? ''));
            $referencia = trim((string) ($metricas['referencia_stp'] ?? ''));

            $partes = [];
            if ($banco !== '') {
                $partes[] = "banco {$banco}";
            }
            if ($cuenta !== '') {
                $partes[] = "cuenta {$cuenta}";
            }
            if ($clabe !== '') {
                $partes[] = "CLABE {$clabe}";
            }

            if ($partes) {
                $mensaje = "Para el credito {$credito}, S2 devolvio " . implode(', ', $partes) . '.';
                if ($referencia !== '') {
                    $mensaje .= " La referencia individual de pago es {$referencia}.";
                }
            } elseif ($referencia !== '') {
                $mensaje = "S2 no devolvio una cuenta concentradora para el credito {$credito}, pero si la referencia individual {$referencia}. La referencia no sustituye una cuenta o CLABE; confirma el canal de pago antes de indicarlo al cliente.";
            } else {
                $mensaje = "S2 respondio para el credito {$credito}, pero no devolvio cuenta, CLABE ni referencia de pago. No voy a inventar datos bancarios; deben consultarse en el Estado de Cuenta vigente.";
            }
        }

        return $this->respuesta($dato, $mensaje, 's2_estado_cuenta', [
            'credito' => $credito,
            'metricas' => $metricas,
        ]);
    }

    /** @return array{cantidad:int,pendiente:float} */
    private function resumirGastos(array $resultado): array
    {
        $filas = $resultado['data'] ?? $resultado['datos'] ?? $resultado;
        if (!is_array($filas)) {
            return ['cantidad' => 0, 'pendiente' => 0.0];
        }

        if (isset($filas['monto_pendiente_real']) || isset($filas['monto_pendiente'])) {
            $filas = [$filas];
        }

        $cantidad = 0;
        $pendiente = 0.0;
        foreach ($filas as $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $cantidad++;
            $valor = $fila['monto_pendiente_real']
                ?? $fila['monto_pendiente']
                ?? $fila['pendiente']
                ?? null;
            if (is_numeric($valor)) {
                $pendiente += (float) $valor;
                continue;
            }
            $original = (float) ($fila['monto_original'] ?? $fila['monto'] ?? 0);
            $condonado = (float) ($fila['condonacion_parcial_monto'] ?? 0);
            $pagado = (float) ($fila['monto_parcial_pagado'] ?? 0);
            $pendiente += max(0.0, $original - $condonado - $pagado);
        }

        return ['cantidad' => $cantidad, 'pendiente' => round($pendiente, 2)];
    }

    /** @param mixed $valor */
    private function extraerFecha($valor): ?string
    {
        if (is_array($valor)) {
            $valor = $valor['fecha'] ?? $valor['Fecha_ultimo_pago_efectivo'] ?? null;
        }
        if (!is_string($valor) || trim($valor) === '') {
            return null;
        }
        $timestamp = strtotime($valor);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function esConsulta(string $mensaje): bool
    {
        return $this->contiene($mensaje, [
            'cargo moratorio', 'cargos moratorios', 'cargo de cobranza', 'gastos de cobranza',
            'aclaracion', 'falta aplicar', 'impactado', 'reflejado', 'conciliar',
            'condonar', 'condonacion', 'domiciliado', 'domiciliacion',
            'saldo total', 'total adeudado', 'adeudo total', 'cuenta concentrada',
            'cuenta concentradora', 'banca movil', 'subarea de cobranza', 'bucket',
        ]);
    }

    private function esOrdenDeModificacion(string $mensaje): bool
    {
        return preg_match('/^\s*(aplica|agrega|crea|condona|cancela|elimina|borra|modifica|cambia|mueve|actualiza|registra)\b/u', $mensaje) === 1;
    }

    private function esConsultaCargosMoratorios(string $mensaje): bool
    {
        return $this->contiene($mensaje, ['cargo moratorio', 'cargos moratorios', 'cargo de cobranza', 'gastos de cobranza'])
            && $this->contiene($mensaje, ['por que', 'porque', 'puedo aplicar', 'permite aplicar', 'no puedo']);
    }

    private function esConsultaAclaracion(string $mensaje): bool
    {
        return $this->contiene($mensaje, ['aclaracion', 'falta aplicar'])
            && $this->contiene($mensaje, ['tiempo', 'tarda', 'impact', 'reflej', 'pago']);
    }

    private function esConsultaCondonacion(string $mensaje): bool
    {
        return $this->contiene($mensaje, ['condonar', 'condonacion'])
            && $this->contiene($mensaje, ['motivo', 'razon', 'cuando', 'por que', 'porque', 'cuales']);
    }

    private function esConsultaDomiciliacion(string $mensaje): bool
    {
        return $this->contiene($mensaje, ['domiciliado', 'domiciliacion']);
    }

    private function esConsultaAdeudo(string $mensaje): bool
    {
        return $this->contiene($mensaje, ['saldo total', 'total adeudado', 'adeudo total']);
    }

    private function esConsultaCuentaConcentradora(string $mensaje): bool
    {
        return $this->contiene($mensaje, ['cuenta concentrada', 'cuenta concentradora', 'banca movil']);
    }

    private function esConsultaBucket(string $mensaje): bool
    {
        return $this->contiene($mensaje, ['subarea de cobranza', 'bucket']);
    }

    private function extraerCredito(string $mensaje): ?int
    {
        if (preg_match('/\b(?:credito|id)\s*(?:#|:|>|=|-)?\s*(\d{2,12})\b/u', $mensaje, $match) === 1) {
            return (int) $match[1];
        }
        return null;
    }

    private function permitido(array $contexto, string $permiso): bool
    {
        return !empty($contexto['permisos_agente'][$permiso]);
    }

    private function denegado(string $modulo): array
    {
        return [
            'mensaje' => "No puedo consultar {$modulo} con este perfil porque no tiene el acceso correspondiente. No se realizo ningun cambio.",
            'tipo' => 'permiso_denegado',
            'tema' => 'permiso',
            'fuente' => 'sesion_y_permisos_sparta',
        ];
    }

    /** @param array<string,mixed> $datos */
    private function respuesta(string $tema, string $mensaje, string $fuente, array $datos = []): array
    {
        return [
            'mensaje' => $mensaje,
            'tipo' => 'consulta_estado_cuenta',
            'tema' => $tema,
            'fuente' => $fuente,
            'datos' => $datos,
        ];
    }

    private function moneda(float $valor): string
    {
        return '$' . number_format($valor, 2, '.', ',');
    }

    /** @param mixed $valor */
    private function siNo($valor): string
    {
        $texto = $this->normalizar((string) $valor);
        if (in_array($texto, ['1', 'si', 'true', 'activo', 'domiciliado'], true)) {
            return 'SI';
        }
        if (in_array($texto, ['0', 'no', 'false', 'inactivo'], true)) {
            return 'NO';
        }
        return strtoupper(trim((string) $valor));
    }

    private function contiene(string $mensaje, array $terminos): bool
    {
        foreach ($terminos as $termino) {
            if (str_contains($mensaje, $termino)) {
                return true;
            }
        }
        return false;
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n', '¿' => '', '?' => '',
        ]);
        return preg_replace('/\s+/u', ' ', $texto) ?? $texto;
    }
}
