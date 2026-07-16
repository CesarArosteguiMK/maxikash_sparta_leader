<?php

namespace Services;

/**
 * Stateful, deterministic workflows for actions Leonidas may execute.
 * The language model never supplies authoritative business values.
 */
class LeonidasAgentService
{
    private const TASK_KEY = 'leonidas_agent_task';
    private const TASK_TTL = 1200;

    /** @var array<string, callable> */
    private array $adapters;

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters + [
            'convenio_ofertas' => static fn(int $idCredito): array => \Models\Convenios::getOfertasElegibles($idCredito),
            'convenio_guardar' => static fn(array $datos): array => \Models\Convenios::guardarConvenio($datos),
            'moto_buscar' => static fn(int $idCredito): array => (new \Models\Adjudicacion())->buscarCreditoPorId($idCredito),
            'moto_responsables' => static fn(): array => (new \Models\Adjudicacion())->obtenerResponsables(),
            'moto_asignar' => static fn(int $idPersona, int $idCredito, int $actorId): array => (new \Models\Adjudicacion())->asignarCredito($idPersona, $idCredito, $actorId),
            'moto_responsable_activo' => static fn(int $idPersona): bool => (new \Models\Adjudicacion())->idPersonaEsResponsableActivo($idPersona),
        ];
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $tarea = $this->tareaActual((int) ($contexto['actor_id'] ?? 0));
        if ($tarea !== null) {
            if (preg_match('/\b(cancelar|cancela|olvida|deten|detener)\b/u', $normalizado)) {
                $this->limpiarTarea();
                return $this->respuesta('Tarea cancelada. No se modificó ningún dato.', 'agente_cancelado');
            }

            return ($tarea['tipo'] ?? '') === 'convenio'
                ? $this->continuarConvenio($mensaje, $normalizado, $tarea, $contexto)
                : $this->continuarMoto($mensaje, $normalizado, $tarea, $contexto);
        }

        if ($this->solicitaConvenio($normalizado)) {
            if (empty($contexto['permisos_agente']['convenio'])) {
                return $this->respuesta(
                    'No puedo iniciar el convenio porque tu perfil necesita acceso a Crear Convenio y el permiso especial Registrar convenio. Solicita ambos permisos a un administrador.',
                    'agente_denegado'
                );
            }
            $this->guardarTarea('convenio', 'credito', [], $contexto);
            return $this->respuesta('Vamos a preparar el convenio. ¿Cuál es el ID del crédito?', 'agente_pregunta');
        }

        if ($this->solicitaMoto($normalizado)) {
            if (empty($contexto['permisos_agente']['motos'])) {
                return $this->respuesta(
                    'No puedo iniciar la adjudicación porque tu perfil no tiene acceso administrativo a Motos Adjudicadas.',
                    'agente_denegado'
                );
            }
            $this->guardarTarea('moto', 'credito', [], $contexto);
            return $this->respuesta('Vamos a asignar el crédito para adjudicación de moto. ¿Cuál es el ID del crédito?', 'agente_pregunta');
        }

        return null;
    }

    public function ejecutar(string $accion, array $payload, array $contexto): array
    {
        if ($accion === 'convenio_crear') {
            return $this->ejecutarConvenio($payload, $contexto);
        }
        if ($accion === 'moto_asignar') {
            return $this->ejecutarMoto($payload, $contexto);
        }

        throw new \RuntimeException('Leónidas no reconoce el ejecutor solicitado.');
    }

    public function limpiarTarea(): void
    {
        unset($_SESSION[self::TASK_KEY]);
    }

    private function continuarConvenio(string $mensaje, string $normalizado, array $tarea, array $contexto): array
    {
        if (empty($contexto['permisos_agente']['convenio'])) {
            $this->limpiarTarea();
            return $this->respuesta('Tus permisos cambiaron y ya no permiten crear convenios. La tarea fue cancelada.', 'agente_denegado');
        }

        $paso = (string) ($tarea['paso'] ?? 'credito');
        $datos = is_array($tarea['datos'] ?? null) ? $tarea['datos'] : [];
        if ($paso === 'credito') {
            $idCredito = $this->extraerEntero($mensaje);
            if ($idCredito <= 0) {
                return $this->respuesta('Necesito un ID de crédito numérico válido para consultar las ofertas.', 'agente_pregunta');
            }

            $resultado = $this->llamar('convenio_ofertas', $idCredito);
            if (empty($resultado['success'])) {
                return $this->respuesta('No pude preparar el convenio del crédito ' . $idCredito . ': ' . $this->mensajeResultado($resultado), 'agente_error');
            }
            $contenedor = is_array($resultado['datos'] ?? null) ? $resultado['datos'] : [];
            $ofertas = array_values(array_filter(
                is_array($contenedor['ofertas'] ?? null) ? $contenedor['ofertas'] : [],
                static fn(array $oferta): bool => ($oferta['tipo_calendario'] ?? 'semanal') === 'semanal'
            ));
            if (!$ofertas) {
                $razon = ($contenedor['razon'] ?? '') === 'convenio_completado'
                    ? 'el crédito ya tiene un convenio completado y no ofrece otro producto'
                    : 'no hay ofertas semanales elegibles vigentes';
                $this->limpiarTarea();
                return $this->respuesta('No se puede continuar: ' . $razon . '.', 'agente_error');
            }

            $opciones = [];
            foreach ($ofertas as $indice => $oferta) {
                $opciones[] = [
                    'indice' => $indice + 1,
                    'id_producto' => (int) ($oferta['id_producto'] ?? 0),
                    'id_detalle' => (int) ($oferta['id_detalle'] ?? 0),
                    'nombre' => trim((string) ($oferta['nombre'] ?? 'Oferta')),
                    'total' => (float) ($oferta['total_a_pagar'] ?? 0),
                    'descuento' => (float) ($oferta['porcentaje_descuento'] ?? 0),
                    'minimo' => max(1, (int) ($oferta['periodo_inicio'] ?? 1)),
                    'maximo' => max(1, (int) ($oferta['semanas_max'] ?? $oferta['periodo_fin_producto'] ?? 1)),
                ];
            }
            $credito = is_array($contenedor['credito'] ?? null) ? $contenedor['credito'] : [];
            $datos = [
                'id_credito' => $idCredito,
                'cliente' => trim((string) ($credito['Nombre_cliente'] ?? 'Sin nombre')),
                'opciones' => $opciones,
            ];
            $this->guardarTarea('convenio', 'oferta', $datos, $contexto);

            $lineas = ['Encontré estas ofertas para ' . $datos['cliente'] . ' (crédito ' . $idCredito . '):'];
            foreach ($opciones as $opcion) {
                $lineas[] = $opcion['indice'] . '. ' . $opcion['nombre']
                    . ' | descuento ' . $this->numero($opcion['descuento']) . '%'
                    . ' | total $' . $this->dinero($opcion['total'])
                    . ' | plazo de ' . $opcion['minimo'] . ' a ' . $opcion['maximo'] . ' semanas';
            }
            $lineas[] = 'Indica el número de la oferta que deseas usar.';
            return $this->respuesta(implode("\n", $lineas), 'agente_opciones');
        }

        if ($paso === 'oferta') {
            $seleccion = $this->extraerEntero($mensaje);
            $opciones = is_array($datos['opciones'] ?? null) ? $datos['opciones'] : [];
            $oferta = null;
            foreach ($opciones as $opcion) {
                if ((int) ($opcion['indice'] ?? 0) === $seleccion) {
                    $oferta = $opcion;
                    break;
                }
            }
            if (!$oferta) {
                return $this->respuesta('Esa opción no existe. Elige un número entre 1 y ' . count($opciones) . '.', 'agente_pregunta');
            }
            $datos['oferta'] = $oferta;
            $this->guardarTarea('convenio', 'semanas', $datos, $contexto);
            return $this->respuesta(
                'Elegiste ' . $oferta['nombre'] . '. ¿En cuántas semanas quieres pagarlo? El plazo permitido es de '
                . $oferta['minimo'] . ' a ' . $oferta['maximo'] . ' semanas.',
                'agente_pregunta'
            );
        }

        $semanas = $this->extraerEntero($mensaje);
        $oferta = is_array($datos['oferta'] ?? null) ? $datos['oferta'] : [];
        $minimo = (int) ($oferta['minimo'] ?? 1);
        $maximo = (int) ($oferta['maximo'] ?? 0);
        if ($semanas < $minimo || $semanas > $maximo) {
            return $this->respuesta('El plazo debe estar entre ' . $minimo . ' y ' . $maximo . ' semanas.', 'agente_pregunta');
        }

        $this->limpiarTarea();
        $pago = $semanas > 0 ? round(((float) ($oferta['total'] ?? 0)) / $semanas, 2) : 0;
        return [
            'mensaje' => 'Vista previa del convenio:'
                . "\nCrédito: " . (int) $datos['id_credito']
                . "\nCliente: " . (string) $datos['cliente']
                . "\nOferta: " . (string) $oferta['nombre']
                . "\nPlazo: " . $semanas . ' semanas'
                . "\nPago semanal aproximado: $" . $this->dinero($pago)
                . "\nTotal: $" . $this->dinero((float) ($oferta['total'] ?? 0))
                . "\nConfirmaré nuevamente la oferta y los importes justo antes de guardarlo.",
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => 'convenio_crear',
                'resumen' => 'crear el convenio del crédito ' . (int) $datos['id_credito'],
                'payload' => [
                    'id_credito' => (int) $datos['id_credito'],
                    'id_producto' => (int) $oferta['id_producto'],
                    'id_detalle' => (int) $oferta['id_detalle'],
                    'semanas' => $semanas,
                ],
            ],
        ];
    }

    private function continuarMoto(string $mensaje, string $normalizado, array $tarea, array $contexto): array
    {
        if (empty($contexto['permisos_agente']['motos'])) {
            $this->limpiarTarea();
            return $this->respuesta('Tus permisos cambiaron y ya no permiten administrar Motos Adjudicadas. La tarea fue cancelada.', 'agente_denegado');
        }

        $paso = (string) ($tarea['paso'] ?? 'credito');
        $datos = is_array($tarea['datos'] ?? null) ? $tarea['datos'] : [];
        if ($paso === 'credito') {
            $idCredito = $this->extraerEntero($mensaje);
            if ($idCredito <= 0) {
                return $this->respuesta('Necesito un ID de crédito numérico válido.', 'agente_pregunta');
            }
            $resultado = $this->llamar('moto_buscar', $idCredito);
            if (empty($resultado['success'])) {
                return $this->respuesta('No pude consultar el crédito ' . $idCredito . ': ' . $this->mensajeResultado($resultado), 'agente_error');
            }
            if (!empty($resultado['asignacion'])) {
                $nombre = trim((string) ($resultado['asignacion']['nombre_despacho'] ?? 'otro responsable'));
                $this->limpiarTarea();
                return $this->respuesta('El crédito ' . $idCredito . ' ya está asignado a ' . $nombre . '. No hice cambios.', 'agente_error');
            }
            if (!$this->creditoEsVencido($resultado)) {
                $estatus = $this->estatusCredito($resultado);
                $this->limpiarTarea();
                return $this->respuesta(
                    'El crédito ' . $idCredito . ' no puede asignarse en Motos Adjudicadas. '
                    . 'Su estatus actual es ' . ($estatus !== '' ? $estatus : 'no identificado')
                    . ' y este proceso solo admite créditos con estatus Vencido. No hice cambios.',
                    'agente_error'
                );
            }
            $responsables = $this->responsablesNormalizados();
            if (!$responsables) {
                $this->limpiarTarea();
                return $this->respuesta('No hay responsables activos de adjudicación disponibles.', 'agente_error');
            }
            $credito = is_array($resultado['credito'] ?? null) ? $resultado['credito'] : [];
            $datos = [
                'id_credito' => $idCredito,
                'cliente' => trim((string) ($credito['nombre_cliente'] ?? 'Sin nombre')),
                'responsables' => $responsables,
            ];
            $this->guardarTarea('moto', 'responsable', $datos, $contexto);
            $lineas = ['Crédito ' . $idCredito . ' de ' . $datos['cliente'] . '. Elige al responsable:'];
            foreach ($responsables as $indice => $responsable) {
                $lineas[] = ($indice + 1) . '. ' . $responsable['nombre'] . ($responsable['puesto'] !== '' ? ' | ' . $responsable['puesto'] : '');
            }
            return $this->respuesta(implode("\n", $lineas), 'agente_opciones');
        }

        $responsables = is_array($datos['responsables'] ?? null) ? $datos['responsables'] : [];
        $coincidencias = $this->resolverResponsable($mensaje, $normalizado, $responsables);
        if (count($coincidencias) !== 1) {
            $texto = count($coincidencias) > 1
                ? 'Encontré más de un responsable con ese nombre. Escribe el número exacto de la lista.'
                : 'No identifiqué al responsable. Escribe el número de la lista o su nombre completo.';
            return $this->respuesta($texto, 'agente_pregunta');
        }
        $responsable = $coincidencias[0];
        $this->limpiarTarea();
        return [
            'mensaje' => 'Vista previa de la asignación:'
                . "\nCrédito: " . (int) $datos['id_credito']
                . "\nCliente: " . (string) $datos['cliente']
                . "\nResponsable: " . $responsable['nombre']
                . "\nAntes de asignar volveré a comprobar que el crédito siga libre y el responsable continúe activo.",
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => 'moto_asignar',
                'resumen' => 'asignar el crédito ' . (int) $datos['id_credito'] . ' a ' . $responsable['nombre'],
                'payload' => [
                    'id_credito' => (int) $datos['id_credito'],
                    'id_persona' => (int) $responsable['id_persona'],
                    'responsable' => $responsable['nombre'],
                ],
            ],
        ];
    }

    private function ejecutarConvenio(array $payload, array $contexto): array
    {
        if (empty($contexto['permisos_agente']['convenio'])) {
            throw new \RuntimeException('Tu perfil ya no tiene los permisos necesarios para crear convenios.');
        }
        $idCredito = (int) ($payload['id_credito'] ?? 0);
        $idProducto = (int) ($payload['id_producto'] ?? 0);
        $idDetalle = (int) ($payload['id_detalle'] ?? 0);
        $semanas = (int) ($payload['semanas'] ?? 0);
        if ($idCredito <= 0 || $idProducto <= 0 || $idDetalle <= 0 || $semanas <= 0) {
            throw new \RuntimeException('La propuesta del convenio está incompleta. Vuelve a prepararla antes de confirmar.');
        }
        $resultado = $this->llamar('convenio_ofertas', $idCredito);
        if (empty($resultado['success'])) {
            throw new \RuntimeException('No se pudo volver a validar el crédito: ' . $this->mensajeResultado($resultado));
        }
        $contenedor = is_array($resultado['datos'] ?? null) ? $resultado['datos'] : [];
        $credito = is_array($contenedor['credito'] ?? null) ? $contenedor['credito'] : [];
        $oferta = null;
        foreach ((array) ($contenedor['ofertas'] ?? []) as $candidata) {
            if ((int) ($candidata['id_producto'] ?? 0) === $idProducto
                && (int) ($candidata['id_detalle'] ?? 0) === $idDetalle
                && ($candidata['tipo_calendario'] ?? 'semanal') === 'semanal') {
                $oferta = $candidata;
                break;
            }
        }
        if (!$oferta) {
            throw new \RuntimeException('La oferta elegida ya no está disponible. No se creó el convenio.');
        }
        $minimo = max(1, (int) ($oferta['periodo_inicio'] ?? 1));
        $maximo = max(1, (int) ($oferta['semanas_max'] ?? $oferta['periodo_fin_producto'] ?? 1));
        if ($semanas < $minimo || $semanas > $maximo) {
            throw new \RuntimeException('El plazo dejó de ser válido para la oferta actual. No se creó el convenio.');
        }
        $total = (float) ($oferta['total_a_pagar'] ?? 0);
        $datos = [
            'id_credito' => $idCredito,
            'id_producto_convenio' => $idProducto,
            'id_producto_convenio_detalle' => $idDetalle,
            'nombre_cliente' => (string) ($credito['Nombre_cliente'] ?? 'Sin nombre'),
            'bucket_morosidad_real' => (string) ($credito['Bucket_Morosidad_Real'] ?? ''),
            'dias_mora' => (int) ($credito['Dias_mora'] ?? 0),
            'avance_pago_plazo' => (string) ($credito['Avance_Pago_Plazo'] ?? ''),
            'adeudo_total_original' => (float) ($credito['Adeudo_total'] ?? 0),
            'porcentaje_descuento' => (float) ($oferta['porcentaje_descuento'] ?? 0),
            'descuento_monto' => (float) ($oferta['descuento_monto'] ?? 0),
            'total_a_pagar' => $total,
            'pago_inicial_monto' => $oferta['pago_inicial_monto'] ?? null,
            'numero_semanas' => $semanas,
            'pago_semanal' => round($total / $semanas, 2),
            'fecha_acuerdo' => date('Y-m-d'),
            'usuario_alta' => (int) $contexto['actor_id'],
            'tipo_calendario' => 'semanal',
            'base_calculo' => $oferta['base_calculo'] ?? null,
            'id_peticion_reactivacion' => $oferta['id_peticion_reactivacion'] ?? null,
            'id_convenio_origen' => $oferta['id_convenio_origen'] ?? null,
            'reactivacion_numero' => $oferta['reactivacion_numero'] ?? null,
            'id_celula' => $contexto['permisos_agente']['id_celula'] ?? null,
        ];
        $guardado = $this->llamar('convenio_guardar', $datos);
        if (empty($guardado['success'])) {
            throw new \RuntimeException($this->mensajeResultado($guardado));
        }
        $idConvenio = (int) (($guardado['datos']['id_convenio'] ?? 0));
        return $this->respuesta(
            'Convenio creado correctamente para el crédito ' . $idCredito
            . ($idConvenio > 0 ? ' con folio interno ' . $idConvenio : '')
            . '. Total: $' . $this->dinero($total) . ' en ' . $semanas . ' semanas.',
            'agente_ejecutado'
        ) + ['ejecucion' => ['accion' => 'convenio_crear', 'id_convenio' => $idConvenio, 'id_credito' => $idCredito]];
    }

    private function ejecutarMoto(array $payload, array $contexto): array
    {
        if (empty($contexto['permisos_agente']['motos'])) {
            throw new \RuntimeException('Tu perfil ya no tiene permiso para administrar Motos Adjudicadas.');
        }
        $idCredito = (int) ($payload['id_credito'] ?? 0);
        $idPersona = (int) ($payload['id_persona'] ?? 0);
        if ($idCredito <= 0 || $idPersona <= 0) {
            throw new \RuntimeException('La propuesta de asignación está incompleta. Vuelve a prepararla antes de confirmar.');
        }
        $credito = $this->llamar('moto_buscar', $idCredito);
        if (empty($credito['success'])) {
            throw new \RuntimeException($this->mensajeResultado($credito));
        }
        if (!empty($credito['asignacion'])) {
            throw new \RuntimeException('El crédito fue asignado mientras esperábamos tu confirmación. No se duplicó la asignación.');
        }
        if (!$this->creditoEsVencido($credito)) {
            $estatus = $this->estatusCredito($credito);
            throw new \RuntimeException(
                'El crédito cambió de estatus mientras esperábamos tu confirmación. '
                . 'Ahora está como ' . ($estatus !== '' ? $estatus : 'no identificado')
                . '; no se realizó la asignación.'
            );
        }
        if (!$this->llamar('moto_responsable_activo', $idPersona)) {
            throw new \RuntimeException('El responsable seleccionado ya no está activo para adjudicación.');
        }
        $resultado = $this->llamar('moto_asignar', $idPersona, $idCredito, (int) $contexto['actor_id']);
        if (empty($resultado['success'])) {
            throw new \RuntimeException($this->mensajeResultado($resultado));
        }
        $responsable = trim((string) ($payload['responsable'] ?? 'el responsable seleccionado'));
        return $this->respuesta(
            'Crédito ' . $idCredito . ' asignado correctamente a ' . $responsable . '. La operación de Motos Adjudicadas quedó preparada.',
            'agente_ejecutado'
        ) + ['ejecucion' => ['accion' => 'moto_asignar', 'id_credito' => $idCredito, 'id_persona' => $idPersona]];
    }

    private function responsablesNormalizados(): array
    {
        $salida = [];
        foreach ((array) $this->llamar('moto_responsables') as $responsable) {
            $id = (int) ($responsable['id_persona'] ?? 0);
            $nombre = trim((string) ($responsable['nombre_completo'] ?? ''));
            if ($id > 0 && $nombre !== '') {
                $salida[] = ['id_persona' => $id, 'nombre' => $nombre, 'puesto' => trim((string) ($responsable['puesto'] ?? ''))];
            }
        }
        return $salida;
    }

    private function resolverResponsable(string $mensaje, string $normalizado, array $responsables): array
    {
        if (preg_match('/^\s*(\d+)\s*$/', $mensaje, $m)) {
            $indice = (int) $m[1] - 1;
            return isset($responsables[$indice]) ? [$responsables[$indice]] : [];
        }
        $busqueda = $this->normalizar($normalizado);
        return array_values(array_filter($responsables, function (array $responsable) use ($busqueda): bool {
            $nombre = $this->normalizar((string) $responsable['nombre']);
            return $busqueda === $nombre || str_contains($nombre, $busqueda) || str_contains($busqueda, $nombre);
        }));
    }

    private function creditoEsVencido(array $resultado): bool
    {
        return $this->normalizar($this->estatusCredito($resultado)) === 'vencido';
    }

    private function estatusCredito(array $resultado): string
    {
        $credito = is_array($resultado['credito'] ?? null) ? $resultado['credito'] : [];
        return trim((string) ($resultado['status_credito'] ?? $credito['status_credito'] ?? ''));
    }

    private function tareaActual(int $actorId): ?array
    {
        $tarea = is_array($_SESSION[self::TASK_KEY] ?? null) ? $_SESSION[self::TASK_KEY] : null;
        if (!$tarea || (int) ($tarea['actor_id'] ?? 0) !== $actorId || (int) ($tarea['expira_en'] ?? 0) < time()) {
            $this->limpiarTarea();
            return null;
        }
        return $tarea;
    }

    private function guardarTarea(string $tipo, string $paso, array $datos, array $contexto): void
    {
        $_SESSION[self::TASK_KEY] = [
            'actor_id' => (int) ($contexto['actor_id'] ?? 0),
            'tipo' => $tipo,
            'paso' => $paso,
            'datos' => $datos,
            'expira_en' => time() + self::TASK_TTL,
        ];
    }

    private function solicitaConvenio(string $mensaje): bool
    {
        return preg_match('/\b(convenio)\b/u', $mensaje) === 1
            && preg_match('/\b(crear|crea|hacer|haz|levantar|levanta|registrar|registra|quiero|necesito)\b/u', $mensaje) === 1;
    }

    private function solicitaMoto(string $mensaje): bool
    {
        return preg_match('/\b(moto|motos|adjudicacion|adjudicar)\b/u', $mensaje) === 1
            && preg_match('/\b(asignar|asigna|adjudicar|adjudica|quiero|necesito)\b/u', $mensaje) === 1;
    }

    private function extraerEntero(string $mensaje): int
    {
        return preg_match('/\b(\d+)\b/', $mensaje, $m) ? (int) $m[1] : 0;
    }

    private function respuesta(string $mensaje, string $tipo): array
    {
        return ['mensaje' => $mensaje, 'tipo' => $tipo];
    }

    private function llamar(string $adaptador, ...$argumentos)
    {
        return ($this->adapters[$adaptador])(...$argumentos);
    }

    private function mensajeResultado(array $resultado): string
    {
        return trim((string) ($resultado['mensaje'] ?? $resultado['message'] ?? $resultado['error'] ?? 'La operación no pudo completarse.'));
    }

    private function dinero(float $valor): string
    {
        return number_format($valor, 2, '.', ',');
    }

    private function numero(float $valor): string
    {
        return rtrim(rtrim(number_format($valor, 2, '.', ''), '0'), '.');
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return preg_replace('/\s+/', ' ', $ascii !== false ? $ascii : $texto) ?? $texto;
    }
}
