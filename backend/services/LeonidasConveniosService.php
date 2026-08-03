<?php

namespace Services;

/**
 * Conocimiento y diagnostico de lectura del modulo de Convenios.
 *
 * Las reglas generales son deterministas. Cuando la pregunta contiene un
 * credito, el servicio consulta el mismo modelo usado por la pantalla de
 * Convenios para explicar el resultado real, sin pedirle al LLM que lo infiera.
 */
class LeonidasConveniosService
{
    /** @var callable(int): array */
    private $consultarOfertas;

    /** @var callable(int): array */
    private $consultarConvenio;

    /** @var callable(int): array */
    private $consultarHistorial;

    public function __construct(
        ?callable $consultarOfertas = null,
        ?callable $consultarConvenio = null,
        ?callable $consultarHistorial = null
    ) {
        $this->consultarOfertas = $consultarOfertas
            ?? static fn(int $credito): array => \Models\Convenios::getOfertasElegibles($credito);
        $this->consultarConvenio = $consultarConvenio
            ?? static fn(int $credito): array => \Models\Convenios::getConvenioCualquierEstatus($credito);
        $this->consultarHistorial = $consultarHistorial
            ?? static fn(int $credito): array => \Models\Convenios::getHistorialConvenios($credito);
    }

    public function resolver(string $mensaje, ?string $normalizado = null): ?array
    {
        $normalizado = $normalizado !== null
            ? $this->normalizar($normalizado)
            : $this->normalizar($mensaje);

        if (!$this->esConsultaDeConvenios($normalizado) || !$this->esPregunta($normalizado)) {
            return null;
        }

        $credito = $this->extraerCredito($normalizado);
        if ($credito !== null) {
            return $this->diagnosticarCredito($credito, $normalizado);
        }

        if ($this->contiene($normalizado, ['pendiente de conciliar', 'pendiente conciliar', 'conciliacion', 'conciliar pago'])) {
            return $this->respuesta(
                'pago_pendiente_conciliar',
                '"Pendiente de conciliar" significa que ya se cargo un comprobante para una cuota pendiente, vencida o parcial, pero el pago todavia no esta confirmado como pagado. Subir el comprobante no liquida la cuota. Para dejarla conciliada deben registrarse el monto pagado, el monto aplicado, cualquier sobrante, la fecha y el usuario conciliador. Cuando se cruza con S2, Sparta busca pagos desde 3 dias antes de la fecha de la cuota hasta 6 dias despues.'
            );
        }

        if ($this->contiene($normalizado, ['plazo', 'semanas', 'cuantas semanas', 'cuanto tiempo'])
            && !$this->contiene($normalizado, ['cancelar', 'cancelacion'])) {
            return $this->respuesta(
                'plazo_maximo',
                'No existe un maximo unico de semanas. Sparta toma el producto de convenio que aplica al credito y busca un rango para su adeudo en producto_convenio_plazos_monto. Si encuentra el rango usa semanas_max; si no, usa periodo_fin del producto. Por eso el maximo solo puede darse con un credito concreto: al consultarlo te mostrare cada oferta vigente y su plazo real.'
            );
        }

        if ($this->contiene($normalizado, ['reactivar', 'reactivacion', 'incumplido'])) {
            return $this->respuesta(
                'reactivacion',
                'Un convenio incumplido no revive como el mismo registro. Se solicita reactivar la oferta del producto y, si se autoriza, Sparta recalcula las condiciones actuales para crear un convenio nuevo ligado al convenio anterior. No debe existir otro convenio activo, debe haber historial del producto y la solicitud no puede estar ya pendiente o aprobada. La solicitud, autorizacion y nuevo convenio quedan auditados.'
            );
        }

        if ($this->contiene($normalizado, ['cancelar', 'cancelacion', 'cancela'])) {
            if ($this->contiene($normalizado, ['tiempo', 'cuando', 'cuantos dias', 'cuanto tarda', 'plazo'])) {
                return $this->respuesta(
                    'tiempo_cancelacion',
                    'La cancelacion automatica por vencimiento esta desactivada. Una cuota puede permanecer vencida sin que el paso de los dias cancele el convenio; Sparta conserva su seguimiento y conciliacion contra S2. Para cancelar se debe usar el flujo manual, que requiere motivo, permiso y auditoria, ya sea mediante solicitud autorizada o cancelacion directa con permiso especial.'
                );
            }

            return $this->respuesta(
                'causas_cancelacion',
                'Sparta no cancela automaticamente un convenio por cuotas vencidas ni por el paso del tiempo. La cancelacion se realiza manualmente: un usuario registra el motivo y, segun sus permisos, genera una solicitud para autorizacion o cancela directamente. El seguimiento de pagos y la conciliacion contra S2 siguen activos, y un convenio completado o ya cancelado no vuelve a cancelarse.'
            );
        }

        if ($this->contiene($normalizado, ['modificar', 'modifico', 'editar', 'cambiar convenio', 'actualizar convenio'])) {
            return $this->respuesta(
                'modificacion',
                'Las condiciones comerciales y el calendario de un convenio activo no se editan libremente. El PDF puede reemplazarse y los comprobantes o conciliaciones se administran por cuota. Para cambiar producto, descuento, plazo, pago inicial o fechas, se cancela el convenio con motivo y permiso, se recalcula la oferta vigente y se genera uno nuevo; si el producto quedo bloqueado por un convenio cancelado, primero debe autorizarse la reactivacion de la oferta.'
            );
        }

        if ($this->contiene($normalizado, ['requiere', 'requisitos', 'obtener', 'elegible', 'puede tener', 'aplica para'])) {
            return $this->respuesta(
                'elegibilidad',
                'El credito debe existir en Segundometro o en el respaldo local, no tener un convenio activo ni un convenio completado que lo bloquee, y debe coincidir con un producto activo. Para una oferta normal, el bucket debe estar entre 8-14 y 121+ dias y pertenecer a los buckets configurados en el producto; tambien debe cumplir el avance minimo cuando ese producto lo exige. Si el ultimo convenio de ese producto fue cancelado, la oferta queda bloqueada hasta autorizar una reactivacion. El permiso del usuario se valida despues y no sustituye estas reglas del credito.'
            );
        }

        return $this->respuesta(
            'general',
            'Convenios calcula ofertas con el credito actual, productos activos y reglas de bucket, avance, monto, descuento y plazo. Puedo explicarte una regla general o diagnosticar un credito concreto; por ejemplo: "¿Puede el credito 1600 obtener convenio y a cuantas semanas?".'
        );
    }

    /** @return array<string, mixed> */
    public function conocimiento(): array
    {
        return [
            'plazo' => 'Dinamico por producto y rango de adeudo; sin rango usa periodo_fin.',
            'elegibilidad' => 'Credito existente, sin convenio activo ni completado bloqueante, producto activo, bucket permitido y avance minimo cuando aplique.',
            'reactivacion' => 'Reactiva la oferta para crear un convenio nuevo; no revive el convenio anterior.',
            'cancelacion_automatica' => 'Desactivada; las cuotas vencidas permanecen en seguimiento y conciliacion.',
            'cancelacion_manual' => 'Requiere motivo, permiso y auditoria; puede pasar por solicitud o ejecutarse directamente.',
            'pendiente_conciliar' => 'Existe comprobante, pero el pago aun no esta confirmado ni conciliado.',
            'modificacion' => 'Las condiciones de un convenio activo no se editan libremente; se cancela y se genera uno nuevo.',
            'ventana_s2_pago' => 'Desde 3 dias antes de la cuota hasta 6 dias despues.',
        ];
    }

    /** @return array<string, mixed> */
    private function diagnosticarCredito(int $creditoId, string $mensaje): array
    {
        try {
            $ofertasResultado = ($this->consultarOfertas)($creditoId);
            $convenioResultado = ($this->consultarConvenio)($creditoId);
            $historialResultado = ($this->consultarHistorial)($creditoId);
        } catch (\Throwable $error) {
            return $this->respuestaDiagnostico(
                'credito_error',
                "No pude completar el diagnostico del credito {$creditoId}. La consulta operativa de Convenios fallo; no se realizo ningun cambio.",
                $creditoId,
                ['error' => 'consulta_operativa_no_disponible']
            );
        }

        $datosOfertas = is_array($ofertasResultado['datos'] ?? null) ? $ofertasResultado['datos'] : [];
        $convenio = is_array($convenioResultado['datos'] ?? null) ? $convenioResultado['datos'] : null;
        $historial = is_array($historialResultado['datos'] ?? null) ? $historialResultado['datos'] : [];

        if (empty($ofertasResultado['success']) && !$convenio) {
            $detalle = trim((string) ($ofertasResultado['mensaje'] ?? 'Credito no encontrado.'));
            return $this->respuestaDiagnostico(
                'credito_no_encontrado',
                "No encontre el credito {$creditoId} en las fuentes que usa Convenios. {$detalle} Verifica el ID o confirma que ya exista en Segundometro.",
                $creditoId,
                ['encontrado' => false]
            );
        }

        $credito = is_array($datosOfertas['credito'] ?? null) ? $datosOfertas['credito'] : [];
        $nombre = trim((string) ($credito['Nombre_cliente'] ?? $convenio['nombre_cliente'] ?? ''));
        $bucket = trim((string) ($credito['Bucket_Morosidad_Real'] ?? ''));
        $diasMora = $credito['Dias_mora'] ?? null;
        $avance = trim((string) ($credito['Avance_Pago_Plazo'] ?? ''));
        $ofertas = is_array($datosOfertas['ofertas'] ?? null) ? $datosOfertas['ofertas'] : [];
        $reactivables = is_array($datosOfertas['ofertas_reactivables'] ?? null) ? $datosOfertas['ofertas_reactivables'] : [];

        $cabecera = "Credito {$creditoId}" . ($nombre !== '' ? " - {$nombre}" : '') . '.';
        $contexto = [];
        if ($bucket !== '') {
            $contexto[] = "Bucket: {$bucket}";
        }
        if ($diasMora !== null && $diasMora !== '') {
            $contexto[] = 'Dias de mora: ' . (int) $diasMora;
        }
        if ($avance !== '') {
            $contexto[] = "Avance de pago: {$avance}";
        }
        $contextoTexto = $contexto ? ' ' . implode('. ', $contexto) . '.' : '';

        if ($convenio && in_array((string) ($convenio['estatus'] ?? ''), ['activo', 'completado'], true)) {
            $estatus = (string) $convenio['estatus'];
            $producto = trim((string) ($convenio['nombre_producto'] ?? 'sin nombre'));
            $semanas = (int) ($convenio['numero_semanas'] ?? 0);
            $mensajeFinal = $cabecera . $contextoTexto
                . " Ya tiene un convenio {$estatus} del producto {$producto}"
                . ($semanas > 0 ? " a {$semanas} semanas" : '')
                . '. Por eso no puede generarse otro convenio mientras conserve ese estado.';

            return $this->respuestaDiagnostico('credito_con_convenio', $mensajeFinal, $creditoId, [
                'encontrado' => true,
                'credito' => $credito,
                'convenio' => $convenio,
                'historial' => $historial,
                'elegible' => false,
            ]);
        }

        if ($ofertas) {
            $resumenOfertas = [];
            foreach ($ofertas as $oferta) {
                $nombreOferta = trim((string) ($oferta['nombre'] ?? 'Producto'));
                $maximo = (int) ($oferta['semanas_max'] ?? 0);
                $total = $this->moneda($oferta['total_a_pagar'] ?? null);
                $descuento = $this->porcentaje($oferta['porcentaje_descuento'] ?? null);
                $detalle = $nombreOferta;
                if ($maximo > 0) {
                    $detalle .= ": hasta {$maximo} semanas";
                }
                if ($total !== null) {
                    $detalle .= ", total {$total}";
                }
                if ($descuento !== null) {
                    $detalle .= ", descuento {$descuento}";
                }
                $resumenOfertas[] = $detalle;
            }

            $mensajeFinal = $cabecera . $contextoTexto
                . ' Si es elegible hoy. Ofertas vigentes: ' . implode('; ', $resumenOfertas) . '.';
            if ($this->contiene($mensaje, ['plazo', 'semanas'])) {
                $mensajeFinal .= ' El plazo mostrado es el maximo real calculado para el adeudo actual; el usuario puede elegir un plazo menor dentro de la configuracion del producto.';
            }

            return $this->respuestaDiagnostico('credito_elegible', $mensajeFinal, $creditoId, [
                'encontrado' => true,
                'credito' => $credito,
                'ofertas' => $ofertas,
                'historial' => $historial,
                'elegible' => true,
            ]);
        }

        $razon = (string) ($datosOfertas['razon'] ?? '');
        if ($razon === 'convenio_completado') {
            $explicacion = 'No es elegible porque existe un convenio completado que bloquea nuevas ofertas.';
        } elseif ($reactivables) {
            $productos = array_values(array_filter(array_map(
                static fn(array $oferta): string => trim((string) ($oferta['nombre'] ?? '')),
                $reactivables
            )));
            $explicacion = 'No tiene una oferta normal disponible porque el producto ya tuvo un convenio cancelado. '
                . 'Puede solicitarse la reactivacion de la oferta'
                . ($productos ? ': ' . implode(', ', $productos) : '')
                . '; despues de autorizarla se recalculan las condiciones para crear un convenio nuevo.';
        } elseif (!$this->bucketGeneralElegible($bucket)) {
            $explicacion = $bucket === ''
                ? 'No es elegible porque la fuente operativa no devolvio un bucket de morosidad utilizable.'
                : "No es elegible porque su bucket ({$bucket}) esta fuera del rango general de Convenios, que inicia en 8-14 dias de mora.";
        } else {
            $explicacion = 'El credito existe, pero ningun producto activo coincide hoy con todas sus reglas de bucket y avance minimo. No es un problema de permiso del usuario: la oferta no fue generada por las reglas comerciales vigentes.';
        }

        return $this->respuestaDiagnostico(
            'credito_no_elegible',
            $cabecera . $contextoTexto . ' ' . $explicacion,
            $creditoId,
            [
                'encontrado' => true,
                'credito' => $credito,
                'ofertas_reactivables' => $reactivables,
                'historial' => $historial,
                'elegible' => false,
                'razon' => $razon,
            ]
        );
    }

    /** @return array<string, mixed> */
    private function respuesta(string $tema, string $mensaje): array
    {
        return [
            'mensaje' => $mensaje,
            'tipo' => 'consulta_convenios',
            'tema' => $tema,
            'fuente' => 'reglas_operativas_convenios',
            'datos' => $this->conocimiento(),
        ];
    }

    /** @param array<string, mixed> $datos */
    private function respuestaDiagnostico(string $tema, string $mensaje, int $credito, array $datos): array
    {
        return [
            'mensaje' => $mensaje,
            'tipo' => 'consulta_convenios',
            'tema' => $tema,
            'fuente' => 'modelo_convenios_tiempo_real',
            'credito' => $credito,
            'datos' => $datos,
        ];
    }

    private function extraerCredito(string $mensaje): ?int
    {
        if (preg_match('/\bcredito\s*(?:id\s*)?(?:#|:|>|=|-)?\s*(\d{2,12})\b/', $mensaje, $match) === 1) {
            return (int) $match[1];
        }
        return null;
    }

    private function esConsultaDeConvenios(string $mensaje): bool
    {
        return $this->contiene($mensaje, [
            'convenio',
            'oferta de convenio',
            'acuerdo de pago',
            'plan de pago',
            'pendiente de conciliar',
            'pendiente conciliar',
            'conciliacion de pago',
        ]);
    }

    private function esPregunta(string $mensaje): bool
    {
        if ($this->contiene($mensaje, [
            'que ', 'como ', 'cual ', 'cuanto ', 'cuantos ', 'cuando ',
            'se puede', 'significa', 'explica', 'dime', 'por que', 'porque',
            'puede el credito', 'puede tener', 'tiene convenio', 'tiene oferta',
        ])) {
            return true;
        }
        return str_contains($mensaje, '?');
    }

    private function bucketGeneralElegible(string $bucket): bool
    {
        return preg_match('/\b(?:8\s*a\s*14|15\s*a\s*21|22\s*a\s*30|31\s*a\s*60|61\s*a\s*90|91\s*a\s*120|121\+)\b/', $bucket) === 1;
    }

    private function moneda(mixed $valor): ?string
    {
        return is_numeric($valor) ? '$' . number_format((float) $valor, 2, '.', ',') : null;
    }

    private function porcentaje(mixed $valor): ?string
    {
        return is_numeric($valor) ? rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.') . '%' : null;
    }

    /** @param list<string> $terminos */
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
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return $convertido === false ? $texto : $convertido;
    }
}
