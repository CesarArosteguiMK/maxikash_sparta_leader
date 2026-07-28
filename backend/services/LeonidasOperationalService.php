<?php

namespace Services;

/**
 * Ejecutor operativo transversal de Leonidas.
 *
 * Flujo: intención -> datos faltantes -> permiso -> estado actual -> vista previa
 * -> confirmación -> idempotencia -> modelo oficial -> verificación -> comprobante.
 */
final class LeonidasOperationalService
{
    private const TASK_KEY = 'leonidas_operational_task';
    private const TASK_TTL = 1800;
    public const APPROVAL_ACTION = 'operacion_sensible_aprobar';

    private array $adapters;
    private $store;
    private ?LeonidasAttachmentService $attachments = null;
    private ?LeonidasFinancialWorkflowService $financial = null;

    public function __construct(array $adapters = [])
    {
        $this->store = $adapters['operation_store'] ?? null;
        $this->attachments = ($adapters['attachment_service'] ?? null) instanceof LeonidasAttachmentService
            ? $adapters['attachment_service']
            : null;
        $this->financial = ($adapters['financial_service'] ?? null) instanceof LeonidasFinancialWorkflowService
            ? $adapters['financial_service']
            : null;
        unset($adapters['operation_store'], $adapters['attachment_service'], $adapters['financial_service']);
        $this->adapters = $adapters;
    }

    public static function accionesEjecutables(): array
    {
        $acciones = array_keys(self::definiciones());
        $acciones = array_values(array_filter($acciones, static fn(string $accion): bool =>
            empty(self::definiciones()[$accion]['consulta'])
        ));
        $acciones[] = self::APPROVAL_ACTION;
        return $acciones;
    }

    public static function puedeEjecutar(string $accion): bool
    {
        return in_array($accion, self::accionesEjecutables(), true);
    }

    public static function mensajeUsaAdjuntoOperativo(string $mensaje): bool
    {
        $n = self::normalizarEstatico($mensaje);
        return preg_match(
            '/\b(ticket|evidencia|comprobante|despacho|almacen|tracking|traspaso|viatico|condonacion|cierre)\b/u',
            $n
        ) === 1;
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $normalizado = self::normalizarEstatico($mensaje);
        $actorId = (int) ($contexto['actor_id'] ?? 0);
        $tarea = $this->tareaActual($actorId);
        if ($tarea !== null) {
            if (preg_match('/\b(cancelar|cancela|olvida|detener|salir)\b/u', $normalizado)) {
                $this->limpiarTarea();
                return $this->respuesta('Operación cancelada. No se modificó ningún dato.', 'agente_cancelado');
            }
            return $this->continuar($mensaje, $tarea, $contexto);
        }

        if (preg_match('/\b(?:aprobar|autorizar|segunda autorizacion)\b.*\b(leo-[a-f0-9]{10})\b/i', $mensaje, $m)) {
            return $this->prepararSegundaAutorizacion(strtoupper($m[1]), $contexto);
        }

        foreach (self::definiciones() as $accion => $def) {
            foreach ($def['patrones'] as $patron) {
                if (preg_match($patron, $normalizado) !== 1) {
                    continue;
                }
                if (!$this->tienePermiso($def, $contexto)) {
                    return $this->respuesta(
                        'No puedo preparar ' . $def['etiqueta'] . ' porque tu perfil no tiene el permiso operativo requerido.',
                        'agente_denegado'
                    );
                }

                $datos = is_array($def['defaults'] ?? null) ? $def['defaults'] : [];
                $datos = array_replace($datos, $this->extraerValoresNombrados($mensaje, $def));
                $datos = array_replace($datos, $this->extraerValoresDirectos($mensaje, $def));
                $datos = $this->aplicarAdjuntoContexto($datos, $def, $contexto);

                if (!empty($def['consulta'])) {
                    $faltante = $this->primerFaltante($def, $datos);
                    if ($faltante !== null) {
                        $this->guardarTarea($accion, $datos, $contexto);
                        return $this->pregunta($def, $faltante);
                    }
                    return $this->resolverConsulta($accion, $datos, $contexto);
                }

                $faltante = $this->primerFaltante($def, $datos);
                if ($faltante !== null) {
                    $this->guardarTarea($accion, $datos, $contexto);
                    return $this->pregunta($def, $faltante);
                }
                return $this->preparar($accion, $datos, $contexto);
            }
        }

        return null;
    }

    public function ejecutar(string $accion, array $payload, array $contexto): array
    {
        if ($accion === self::APPROVAL_ACTION) {
            return $this->ejecutarSegundaAutorizacion($payload, $contexto);
        }
        $def = self::definiciones()[$accion] ?? null;
        if (!$def || !empty($def['consulta'])) {
            throw new \RuntimeException('Leonidas no reconoce el ejecutor operativo solicitado.');
        }
        if (!$this->tienePermiso($def, $contexto)) {
            throw new \DomainException('Tus permisos cambiaron y ya no permiten ejecutar esta operación.');
        }
        $this->validarPayload($def, $payload);
        $this->validarReglasAccion($accion, $payload, $contexto);
        $this->validarAdjuntos($accion, $payload, $def, $contexto);

        if (!empty($def['sensible']) && empty($payload['_autorizacion_reforzada'])) {
            $autorizacion = $this->store()->crearAutorizacion(
                $accion,
                $this->limpiarInternos($payload),
                (int) $contexto['actor_id'],
                $this->resumen($def, $payload)
            );
            return [
                'mensaje' => 'Primera confirmación registrada. Por ser una operación sensible, otra persona con el mismo permiso debe escribir '
                    . '"aprobar operación ' . $autorizacion['codigo'] . '". Hasta entonces no se modificará la fuente.',
                'tipo' => 'agente_autorizacion_pendiente',
                'autorizacion' => [
                    'codigo' => $autorizacion['codigo'],
                    'estado' => 'pendiente',
                    'requiere_actor_distinto' => true,
                    'expira_horas' => 24,
                ],
            ];
        }

        return $this->ejecutarDirecto($accion, $this->limpiarInternos($payload), $contexto);
    }

    public function limpiarTarea(): void
    {
        unset($_SESSION[self::TASK_KEY]);
    }

    private function continuar(string $mensaje, array $tarea, array $contexto): array
    {
        $accion = (string) ($tarea['accion'] ?? '');
        $def = self::definiciones()[$accion] ?? null;
        if (!$def) {
            $this->limpiarTarea();
            return $this->respuesta('La operación perdió su contexto. Vuelve a solicitarla.', 'agente_error');
        }
        if (!$this->tienePermiso($def, $contexto)) {
            $this->limpiarTarea();
            return $this->respuesta('Tus permisos cambiaron. La operación fue cancelada.', 'agente_denegado');
        }
        $datos = is_array($tarea['datos'] ?? null) ? $tarea['datos'] : [];
        $nombrados = $this->extraerValoresNombrados($mensaje, $def);
        if ($nombrados) {
            $datos = array_replace($datos, $nombrados);
        } else {
            $faltante = $this->primerFaltante($def, $datos);
            if ($faltante !== null) {
                $valor = $this->valorConversacional($mensaje, $def['campos'][$faltante] ?? []);
                if ($valor !== null) {
                    $datos[$faltante] = $valor;
                }
            }
        }
        $datos = $this->aplicarAdjuntoContexto($datos, $def, $contexto);
        $faltante = $this->primerFaltante($def, $datos);
        if ($faltante !== null) {
            $this->guardarTarea($accion, $datos, $contexto);
            return $this->pregunta($def, $faltante);
        }
        $this->limpiarTarea();
        return !empty($def['consulta'])
            ? $this->resolverConsulta($accion, $datos, $contexto)
            : $this->preparar($accion, $datos, $contexto);
    }

    private function preparar(string $accion, array $datos, array $contexto): array
    {
        $def = self::definiciones()[$accion];
        try {
            $this->validarPayload($def, $datos);
            $this->validarReglasAccion($accion, $datos, $contexto);
            $this->validarAdjuntos($accion, $datos, $def, $contexto);
            $estado = $this->inspeccionar($accion, $datos, $contexto);
        } catch (\Throwable $e) {
            return $this->respuesta('No pude preparar la vista previa: ' . $e->getMessage(), 'agente_error');
        }
        if (isset($estado['success']) && empty($estado['success'])) {
            return $this->respuesta(
                'No pude preparar la vista previa: ' . $this->mensajeResultado($estado),
                'agente_error'
            );
        }
        $resumen = $this->resumen($def, $datos);
        $mensaje = 'Vista previa: ' . $resumen . '.';
        if ($estado) {
            $mensaje .= "\nEstado actual verificado: " . $this->resumenEstado($estado) . '.';
        }
        if (!empty($def['sensible'])) {
            $mensaje .= "\nEsta acción requiere dos personas autorizadas distintas.";
        }
        $mensaje .= "\nConfirma para ejecutar o cancela para salir.";
        return $this->respuesta($mensaje, 'agente_vista_previa') + [
            'propuesta_especificacion' => [
                'accion' => $accion,
                'resumen' => $resumen,
                'payload' => $datos,
            ],
            'estado_actual' => $this->sanitizarComprobante($estado),
            'reversion' => $def['reversion'] ?? null,
        ];
    }

    private function resolverConsulta(string $accion, array $datos, array $contexto): array
    {
        $resultado = $this->inspeccionar($accion, $datos, $contexto);
        if (isset($resultado['success']) && empty($resultado['success'])) {
            return $this->respuesta($this->mensajeResultado($resultado), 'agente_error');
        }
        return $this->respuesta(
            $this->mensajeResultado($resultado, 'Consulta verificada en la fuente oficial.'),
            'agente_consulta_operativa'
        ) + ['reporte' => $this->sanitizarComprobante($resultado), 'fuente' => 'modelo_oficial'];
    }

    private function prepararSegundaAutorizacion(string $codigo, array $contexto): array
    {
        $autorizacion = $this->store()->obtenerAutorizacion($codigo);
        if (!$autorizacion || (string) ($autorizacion['estado'] ?? '') !== 'pendiente') {
            return $this->respuesta('La autorización no existe, expiró o ya fue atendida.', 'agente_error');
        }
        if ((int) ($autorizacion['primer_actor_id'] ?? 0) === (int) ($contexto['actor_id'] ?? 0)) {
            return $this->respuesta('La segunda autorización debe realizarla otra persona con permiso.', 'agente_denegado');
        }
        $accionOriginal = (string) ($autorizacion['accion'] ?? '');
        $def = self::definiciones()[$accionOriginal] ?? null;
        if (!$def || !$this->tienePermiso($def, $contexto)) {
            return $this->respuesta('Tu perfil no tiene permiso para otorgar la segunda autorización.', 'agente_denegado');
        }
        $estado = $this->inspeccionar($accionOriginal, (array) ($autorizacion['payload'] ?? []), $contexto);
        return $this->respuesta(
            'Segunda vista previa de ' . $codigo . ': ' . (string) ($autorizacion['resumen'] ?? $def['etiqueta'])
                . '. Estado actual: ' . $this->resumenEstado($estado)
                . '. Confirma para autorizar y ejecutar, o cancela.',
            'agente_vista_previa_reforzada'
        ) + [
            'propuesta_especificacion' => [
                'accion' => self::APPROVAL_ACTION,
                'resumen' => 'Segunda autorización ' . $codigo . ': ' . (string) ($autorizacion['resumen'] ?? ''),
                'payload' => ['codigo' => $codigo],
            ],
            'estado_actual' => $this->sanitizarComprobante($estado),
        ];
    }

    private function ejecutarSegundaAutorizacion(array $payload, array $contexto): array
    {
        $codigo = strtoupper(trim((string) ($payload['codigo'] ?? '')));
        if ($codigo === '') {
            throw new \InvalidArgumentException('Falta el código de autorización reforzada.');
        }
        $autorizacion = $this->store()->reclamarAutorizacion($codigo, (int) $contexto['actor_id']);
        $accion = (string) ($autorizacion['accion'] ?? '');
        $def = self::definiciones()[$accion] ?? null;
        if (!$def || !$this->tienePermiso($def, $contexto)) {
            $this->store()->marcarAutorizacion($codigo, 'fallida', (int) $contexto['actor_id']);
            throw new \DomainException('No tienes permiso para ejecutar la operación autorizada.');
        }
        try {
            $datos = (array) ($autorizacion['payload'] ?? []);
            $datos['_autorizacion_reforzada'] = $codigo;
            $resultado = $this->ejecutarDirecto($accion, $this->limpiarInternos($datos), $contexto);
            $this->store()->marcarAutorizacion(
                $codigo,
                'ejecutada',
                (int) $contexto['actor_id'],
                (array) ($resultado['comprobante'] ?? [])
            );
            $resultado['autorizacion'] = [
                'codigo' => $codigo,
                'primer_actor_id' => (int) ($autorizacion['primer_actor_id'] ?? 0),
                'segundo_actor_id' => (int) $contexto['actor_id'],
                'estado' => 'ejecutada',
            ];
            return $resultado;
        } catch (\Throwable $e) {
            $this->store()->marcarAutorizacion($codigo, 'fallida', (int) $contexto['actor_id'], [
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);
            throw $e;
        }
    }

    private function ejecutarDirecto(string $accion, array $payload, array $contexto): array
    {
        $def = self::definiciones()[$accion];
        $clave = hash('sha256', $accion . '|' . $this->canonicalJson($payload) . '|' . date('Y-m-d'));
        $inicio = $this->store()->iniciar(
            $clave,
            $accion,
            (int) $contexto['actor_id'],
            hash('sha256', $this->canonicalJson($payload))
        );
        if (empty($inicio['nueva'])) {
            $previa = (array) ($inicio['ejecucion'] ?? []);
            if ((string) ($previa['estado'] ?? '') === 'verificada') {
                return [
                    'mensaje' => 'La operación ya había sido ejecutada y verificada. Devuelvo el comprobante existente; no la repetí.',
                    'tipo' => 'agente_idempotente',
                    'ejecucion' => ['accion' => $accion, 'duplicada' => true],
                    'comprobante' => (array) ($previa['comprobante'] ?? []),
                ];
            }
            throw new \RuntimeException('Existe una ejecución igual en curso o fallida. Revisa su comprobante antes de reintentar.');
        }

        try {
            $estadoConfirmacion = $this->inspeccionar($accion, $payload, $contexto);
            if (isset($estadoConfirmacion['success']) && empty($estadoConfirmacion['success'])) {
                throw new \RuntimeException(
                    'El estado cambió antes de confirmar: ' . $this->mensajeResultado($estadoConfirmacion)
                );
            }
            $resultado = $this->ejecutarModelo($accion, $payload, $contexto);
            if (empty($resultado['success'])) {
                throw new \RuntimeException($this->mensajeResultado($resultado));
            }
            $verificacion = $this->verificar($accion, $payload, $resultado, $contexto);
            if (empty($verificacion['success'])) {
                throw new \RuntimeException(
                    'El modelo respondió, pero la verificación posterior falló: ' . $this->mensajeResultado($verificacion)
                );
            }
            $comprobante = [
                'accion' => $accion,
                'etiqueta' => $def['etiqueta'],
                'actor_id' => (int) $contexto['actor_id'],
                'fecha' => date('c'),
                'estado_previo_confirmacion' => $this->sanitizarComprobante($estadoConfirmacion),
                'resultado' => $this->sanitizarComprobante($resultado),
                'verificacion' => $this->sanitizarComprobante($verificacion),
                'idempotency_key' => $clave,
                'reversion' => $def['reversion'] ?? null,
            ];
            $this->store()->completar($clave, $comprobante);
            return [
                'mensaje' => 'Listo. ' . $this->mensajeResultado($resultado, $def['etiqueta'] . ' completada.')
                    . ' El resultado fue verificado en la fuente correspondiente.',
                'tipo' => 'agente_ejecutado',
                'ejecucion' => ['accion' => $accion, 'duplicada' => false],
                'comprobante' => $comprobante,
            ];
        } catch (\Throwable $e) {
            $this->store()->fallar($clave, $e->getMessage());
            throw $e;
        }
    }

    private function inspeccionar(string $accion, array $payload, array $contexto): array
    {
        if (isset($this->adapters[$accion . '_inspect'])) {
            return (array) ($this->adapters[$accion . '_inspect'])($payload, $contexto);
        }
        return $this->inspeccionarDefault($accion, $payload, $contexto);
    }

    private function ejecutarModelo(string $accion, array $payload, array $contexto): array
    {
        if (isset($this->adapters[$accion . '_execute'])) {
            return $this->normalizarResultado((array) ($this->adapters[$accion . '_execute'])($payload, $contexto));
        }
        return $this->normalizarResultado($this->ejecutarDefault($accion, $payload, $contexto));
    }

    private function verificar(string $accion, array $payload, array $resultado, array $contexto): array
    {
        if (isset($this->adapters[$accion . '_verify'])) {
            return $this->normalizarResultado((array) ($this->adapters[$accion . '_verify'])($payload, $resultado, $contexto));
        }
        return $this->normalizarResultado($this->verificarDefault($accion, $payload, $resultado, $contexto));
    }

    private function inspeccionarDefault(string $accion, array $p, array $contexto): array
    {
        if (str_starts_with($accion, 'ticket_')) {
            if ($accion === 'ticket_crear') {
                return ['success' => true, 'message' => 'Se creará un ticket nuevo.', 'id_credito' => (int) ($p['id_credito'] ?? 0)];
            }
            $estado = \Models\Ticket::obtenerEstadoOperativo((int) ($p['id_ticket'] ?? 0));
            return $estado ? ['success' => true, 'ticket' => $estado] : ['success' => false, 'message' => 'Ticket no encontrado.'];
        }
        if (str_starts_with($accion, 'direccion_')) {
            if ($accion === 'direccion_sincronizar') {
                return ['success' => true, 'message' => 'Sincronización global preparada.'];
            }
            return (new \Models\Direcciones())->buscarPorCredito((int) ($p['id_credito'] ?? 0));
        }
        if (str_starts_with($accion, 'despacho_')) {
            if ($accion === 'despacho_importar_excel') {
                $ruta = $this->attachments()->rutaTemporal((string) $p['archivo_token'], (int) $contexto['actor_id'], ['hoja_calculo']);
                $celula = $contexto['permisos_agente']['despachos_celula'] ?? null;
                return $this->previsualizarExcelDespachos($ruta, $celula !== null ? (int) $celula : null);
            }
            if ($accion === 'despacho_adjuntar_documento') {
                $meta = $this->attachments()->metadata((string) $p['archivo_token'], (int) $contexto['actor_id']);
                return ['success' => true, 'message' => 'Documento verificado.', 'archivo' => $meta['nombre'] ?? ''];
            }
            $asignacion = (new \Models\Despachos())->obtenerAsignacionCredito((int) ($p['id_credito'] ?? 0));
            return ['success' => true, 'message' => $asignacion ? 'Crédito con historial de asignación.' : 'Crédito sin asignación.', 'asignacion' => $asignacion];
        }
        if (str_starts_with($accion, 'almacen_')) {
            $modelo = new \Models\AlmacenVirtual();
            if ($accion === 'almacen_confirmar_entrega') {
                $rows = $modelo->listarTraspasos(['id_traspaso' => (int) ($p['id_traspaso'] ?? 0)]);
                return ['success' => true, 'traspasos' => $rows];
            }
            return $modelo->obtenerFichaUnidad((int) ($p['id_unidad'] ?? 0));
        }
        if (str_starts_with($accion, 'tracking_')) {
            $detalle = (new \Models\TrackingRecoleccion())->obtenerDetalleRuta((int) ($p['id_ruta'] ?? 0));
            if ($accion === 'tracking_crear_ruta') {
                return ['success' => true, 'message' => 'Se creará una ruta nueva.'];
            }
            return $detalle ? ['success' => true, 'ruta' => $detalle] : ['success' => false, 'message' => 'Ruta no encontrada.'];
        }
        if (str_starts_with($accion, 'viatico_')) {
            if ($accion === 'viatico_crear') {
                return ['success' => true, 'message' => 'Se creará una solicitud de viáticos en borrador.'];
            }
            $row = $this->financial()->consultarViatico((int) ($p['id_viatico'] ?? 0));
            return $row ? ['success' => true, 'message' => 'Solicitud localizada.', 'viatico' => $row] : ['success' => false, 'message' => 'Solicitud no encontrada.'];
        }
        if (str_starts_with($accion, 'condonacion_')) {
            if ($accion === 'condonacion_preparar') {
                return ['success' => true, 'message' => 'Se preparará un borrador sin afectar la fuente financiera.'];
            }
            if ($accion === 'condonacion_verificar') {
                return $this->financial()->verificarCondonacion((string) ($p['codigo'] ?? ''));
            }
            $row = $this->financial()->consultarCondonacion((string) ($p['codigo'] ?? ''));
            return $row ? ['success' => true, 'message' => 'Solicitud localizada.', 'condonacion' => $row] : ['success' => false, 'message' => 'Solicitud no encontrada.'];
        }
        if (str_starts_with($accion, 'cierre_')) {
            if ($accion === 'cierre_preparar') {
                return ['success' => true, 'message' => 'Se preparará el seguimiento de cierre para el crédito.'];
            }
            return \Models\CierreCredito::getDetalleCierre((int) ($p['id_cierre'] ?? 0));
        }
        return ['success' => true, 'message' => 'Estado previo disponible.'];
    }

    private function ejecutarDefault(string $accion, array $p, array $c): array
    {
        $actor = (int) $c['actor_id'];
        $nombre = (string) ($c['nombre'] ?? 'Leonidas');
        switch ($accion) {
            case 'ticket_crear':
                $catalogos = \Models\Ticket::getCatalogosTicket();
                $tipos = (array) ($catalogos['datos']['tipos'] ?? []);
                $origenes = (array) ($catalogos['datos']['origenes'] ?? []);
                if (!$tipos || !$origenes) return ['success' => false, 'message' => 'No hay catálogos activos de ticket.'];
                return \Models\Ticket::crear([
                    'id_tipo_ticket' => (int) ($p['id_tipo_ticket'] ?? $tipos[0]['id']),
                    'id_origen_ticket' => (int) ($p['id_origen_ticket'] ?? $origenes[0]['id']),
                    'id_credito' => (int) $p['id_credito'],
                    'descripcion_inicial' => (string) $p['descripcion'],
                    'categoria_gestion' => 'sabueso',
                ], $actor);
            case 'ticket_asignar':
                $resultado = \Models\Ticket::asignar((int) $p['id_ticket'], (int) $p['id_persona'], $actor);
                if (!empty($resultado['success'])) {
                    $idCredito = \Models\Ticket::getIdCreditoPorTicket((int) $p['id_ticket']);
                    if ($idCredito !== null) {
                        $nombre = \Models\Ticket::getNombrePersona((int) $p['id_persona']);
                        $historial = \Models\RegistroAsignacion::registrarAsignacion(
                            $idCredito,
                            $nombre !== '' ? $nombre : 'Persona #' . (int) $p['id_persona']
                        );
                        if (empty($historial['success'])) {
                            $resultado['warning'] = 'El ticket quedó asignado, pero no fue posible actualizar el historial auxiliar por crédito.';
                            $resultado['historial_asignacion'] = $historial;
                        }
                    }
                }
                return $resultado;
            case 'ticket_desasignar':
                $resultado = \Models\Ticket::quitarAsignacion((int) $p['id_ticket']);
                if (!empty($resultado['success'])) {
                    $idCredito = \Models\Ticket::getIdCreditoPorTicket((int) $p['id_ticket']);
                    if ($idCredito !== null) {
                        $historial = \Models\RegistroAsignacion::cerrarAsignacionActiva($idCredito);
                        if (empty($historial['success'])) {
                            $resultado['warning'] = 'El ticket quedó sin responsable, pero no fue posible cerrar el historial auxiliar por crédito.';
                            $resultado['historial_asignacion'] = $historial;
                        }
                    }
                }
                return $resultado;
            case 'ticket_seguimiento':
                return \Models\Ticket::agregarChat((int) $p['id_ticket'], $actor, (string) $p['seguimiento']);
            case 'ticket_adjuntar_evidencia':
                $a = $this->attachments()->materializar((string) $p['archivo_token'], $actor, 'tickets');
                return \Models\Ticket::guardarEvidencia((int) $p['id_ticket'], $actor, $a['ruta_publica'], $a['nombre_original']);
            case 'ticket_cerrar':
                return \Models\Ticket::cerrar((int) $p['id_ticket'], $actor);
            case 'ticket_reabrir':
                return \Models\Ticket::reabrir((int) $p['id_ticket'], $actor);
            case 'direccion_registrar':
                return (new \Models\Direcciones())->guardarDireccion($p);
            case 'direccion_prioridad':
                return (new \Models\Direcciones())->reordenarDirecciones((int) $p['id_credito'], (array) $p['orden_ids']);
            case 'direccion_sincronizar':
                return (new \Models\Direcciones())->sincronizarDesdeSegundometro();
            case 'direccion_corregir':
                $cambios = is_array($p['cambios'] ?? null) ? $p['cambios'] : [];
                unset($p['cambios']);
                return (new \Models\Direcciones())->corregirDireccion(array_replace($p, $cambios));
            case 'despacho_asignar_credito':
                $ok = (new \Models\Despachos())->asignarCredito((int) $p['id_persona'], (int) $p['id_credito'], (int) $p['id_celula']);
                return ['success' => (bool) $ok, 'message' => $ok ? 'Crédito asignado.' : 'No se pudo asignar el crédito.'];
            case 'despacho_desasignar_credito':
                $ok = (new \Models\Despachos())->desasignarCredito((int) $p['id_credito']);
                return ['success' => (bool) $ok, 'message' => $ok ? 'Crédito desasignado.' : 'El crédito no tenía una asignación activa.'];
            case 'despacho_cambiar_estatus':
                $ok = (new \Models\Despachos())->cambiarEstatusCredito((int) $p['id_credito'], (string) $p['estatus']);
                return ['success' => (bool) $ok, 'message' => $ok ? 'Estatus actualizado.' : 'No se actualizó el estatus.'];
            case 'despacho_importar_excel':
                $ruta = $this->attachments()->rutaTemporal((string) $p['archivo_token'], $actor, ['hoja_calculo']);
                return (new \Models\Despachos())->importarAsignaCreditosDesdeExcel((int) $p['id_persona'], $ruta);
            case 'despacho_adjuntar_documento':
                $a = $this->attachments()->materializar((string) $p['archivo_token'], $actor, 'despachos');
                $ok = (new \Models\Despachos())->subirDocumento(
                    (int) $p['id_persona'],
                    (int) $p['id_catalogo_documento'],
                    $a['nombre_original'],
                    $a['ruta_publica']
                );
                return ['success' => (bool) $ok, 'message' => $ok ? 'Documento adjuntado.' : 'No se pudo adjuntar el documento.'];
            case 'almacen_confirmar_recepcion':
                $modelo = new \Models\AlmacenVirtual();
                $docs = [
                    'doc_factura_moto' => $this->attachments()->materializar((string) $p['archivo_token_factura'], $actor, 'almacen_recepcion'),
                    'doc_tarjeta_circulacion' => $this->attachments()->materializar((string) $p['archivo_token_tarjeta'], $actor, 'almacen_recepcion'),
                ];
                return $modelo->confirmarRecepcionAlmacen((int) $p['id_unidad'], $p, $docs, $actor, $nombre);
            case 'almacen_iniciar_revision':
                return (new \Models\AlmacenVirtual())->iniciarRevisionMecanica((int) $p['id_unidad'], $actor, $nombre);
            case 'almacen_finalizar_revision':
                $ev = [];
                if (!empty($p['archivo_token'])) {
                    $ev[(string) ($p['slot_evidencia'] ?? 'revision_mecanica')] =
                        $this->attachments()->materializar((string) $p['archivo_token'], $actor, 'almacen_revision');
                }
                $datos = $p;
                $datos['items'] = is_array($p['items'] ?? null) ? $p['items'] : [];
                return (new \Models\AlmacenVirtual())->finalizarRevisionMecanica((int) $p['id_unidad'], $datos, $ev, $actor, $nombre);
            case 'almacen_crear_traspaso':
                $ev = ['traspaso_origen' => $this->attachments()->materializar((string) $p['archivo_token'], $actor, 'almacen_traspasos')];
                return (new \Models\AlmacenVirtual())->crearOrdenTraspaso((int) $p['id_unidad'], $p, $ev, $actor, $nombre);
            case 'almacen_confirmar_entrega':
                $ev = ['traspaso_destino' => $this->attachments()->materializar((string) $p['archivo_token'], $actor, 'almacen_traspasos')];
                return (new \Models\AlmacenVirtual())->confirmarRecepcionTraspaso((int) $p['id_traspaso'], $p, $ev, $actor, $nombre);
            case 'tracking_crear_ruta':
                return (new \Models\TrackingRecoleccion())->guardarRuta($p, $actor);
            case 'tracking_actualizar_ruta':
                return (new \Models\TrackingRecoleccion())->actualizarRutaOperativa($p, $actor);
            case 'tracking_cancelar_ruta':
                return (new \Models\TrackingRecoleccion())->cancelarRuta((int) $p['id_ruta'], (string) $p['motivo'], $actor, true);
            case 'tracking_adjuntar_evidencia':
                $a = $this->attachments()->materializar((string) $p['archivo_token'], $actor, 'tracking');
                return (new LeonidasTrackingEvidenceService())->adjuntarRuta((int) $p['id_ruta'], $a, (string) ($p['mensaje'] ?? ''));
            case 'viatico_crear':
                return $this->financial()->crearViatico($p, $c);
            case 'viatico_adjuntar_comprobante':
                return $this->financial()->adjuntarComprobante($p, $c);
            case 'viatico_enviar_autorizacion':
                return $this->financial()->enviarViatico((int) $p['id_viatico'], $actor);
            case 'viatico_aprobar':
                return $this->financial()->aprobarViatico((int) $p['id_viatico'], $actor);
            case 'viatico_rechazar':
                return $this->financial()->rechazarViatico((int) $p['id_viatico'], $actor, (string) $p['motivo']);
            case 'viatico_registrar_pago':
                return $this->financial()->registrarPagoViatico((int) $p['id_viatico'], $actor, (string) $p['referencia_pago']);
            case 'condonacion_preparar':
                return $this->financial()->prepararCondonacion($p, $actor);
            case 'condonacion_enviar':
                return $this->financial()->enviarCondonacion((string) $p['codigo'], $actor);
            case 'condonacion_aprobar':
                return $this->financial()->aplicarCondonacion((string) $p['codigo'], $c);
            case 'condonacion_rechazar':
                return $this->financial()->rechazarCondonacion((string) $p['codigo'], $actor, (string) $p['motivo']);
            case 'cierre_preparar':
                return \Models\CierreCredito::crear([
                    'id_credito' => (int) $p['id_credito'],
                    'nombre_cliente' => (string) $p['nombre_cliente'],
                    'estatus' => 'en_proceso',
                    'usuario_alta' => $nombre,
                ]);
            case 'cierre_enviar_autorizacion':
                $archivo = null;
                if (!empty($p['archivo_token'])) {
                    $archivo = $this->attachments()->materializar((string) $p['archivo_token'], $actor, 'cierre_credito')['ruta_publica'];
                }
                return \Models\CierreCredito::enviarAVoBo((int) $p['id_cierre'], $nombre, (string) $p['comentario'], $archivo);
            case 'cierre_aprobar':
                return \Models\CierreCredito::aprobarVoBo((int) $p['id_cierre'], $nombre, (string) ($p['comentario'] ?? ''));
            case 'cierre_rechazar':
                return \Models\CierreCredito::rechazarVoBo((int) $p['id_cierre'], $nombre, (string) $p['comentario']);
            case 'cierre_enviar_cartera':
                return \Models\CierreCredito::enviarACartera((int) $p['id_cierre'], $nombre, (string) ($p['estatus_origen'] ?? 'en_proceso'));
        }
        return ['success' => false, 'message' => 'Ejecutor no implementado.'];
    }

    private function verificarDefault(string $accion, array $p, array $r, array $contexto): array
    {
        if (str_starts_with($accion, 'ticket_')) {
            $id = (int) ($p['id_ticket'] ?? $r['datos']['id_ticket'] ?? $r['id_ticket'] ?? 0);
            if ($accion === 'ticket_crear') {
                return $id > 0 && \Models\Ticket::obtenerEstadoOperativo($id)
                    ? ['success' => true, 'message' => 'Ticket localizado.', 'id_ticket' => $id]
                    : ['success' => false, 'message' => 'No se localizó el ticket creado.'];
            }
            if ($accion === 'ticket_seguimiento') {
                $chat = \Models\Ticket::getChatPorTicket($id);
                return ['success' => !empty($chat['success']), 'message' => 'Seguimiento consultado.', 'total' => count((array) ($chat['datos'] ?? []))];
            }
            if ($accion === 'ticket_adjuntar_evidencia') {
                $e = \Models\Ticket::getEvidenciasPorTicket($id);
                return ['success' => !empty($e['success']), 'message' => 'Evidencia consultada.', 'total' => count((array) ($e['datos'] ?? []))];
            }
            $estado = \Models\Ticket::obtenerEstadoOperativo($id);
            $ok = (bool) $estado;
            if ($accion === 'ticket_asignar') $ok = $ok && (int) ($estado['id_persona_asignada'] ?? 0) === (int) $p['id_persona'];
            if ($accion === 'ticket_desasignar') $ok = $ok && (int) ($estado['id_persona_asignada'] ?? 0) === 0;
            if ($accion === 'ticket_cerrar') $ok = $ok && (int) ($estado['activo'] ?? 1) === 0;
            if ($accion === 'ticket_reabrir') $ok = $ok && (int) ($estado['activo'] ?? 0) === 1 && empty($estado['fecha_eliminacion']);
            return ['success' => $ok, 'message' => $ok ? 'Estado de ticket verificado.' : 'El estado del ticket no coincide.', 'ticket' => $estado];
        }
        if (str_starts_with($accion, 'direccion_')) {
            if ($accion === 'direccion_sincronizar') return ['success' => true, 'message' => 'La fuente devolvió el resumen de sincronización.', 'resumen' => $r];
            $actual = (new \Models\Direcciones())->buscarPorCredito((int) $p['id_credito']);
            return ['success' => !empty($actual['success']), 'message' => 'Direcciones consultadas después del cambio.', 'direcciones' => $actual['direcciones'] ?? []];
        }
        if (str_starts_with($accion, 'despacho_')) {
            if (in_array($accion, ['despacho_importar_excel', 'despacho_adjuntar_documento'], true)) {
                return ['success' => !empty($r['success']), 'message' => 'El modelo oficial confirmó la operación.', 'resultado' => $r];
            }
            $m = new \Models\Despachos();
            $activa = $m->verificarAsignacion((int) $p['id_credito']);
            $esperada = !in_array($accion, ['despacho_desasignar_credito'], true)
                && !($accion === 'despacho_cambiar_estatus' && (string) $p['estatus'] === '0');
            return ['success' => $activa === $esperada, 'message' => 'Asignación verificada.', 'asignacion_activa' => $activa];
        }
        if (str_starts_with($accion, 'almacen_')) {
            if ($accion === 'almacen_confirmar_entrega') {
                return ['success' => !empty($r['success']), 'message' => 'El traspaso confirmó su cierre.', 'resultado' => $r];
            }
            $ficha = (new \Models\AlmacenVirtual())->obtenerFichaUnidad((int) $p['id_unidad']);
            return ['success' => !empty($ficha['success']), 'message' => 'Unidad consultada después del cambio.', 'ficha' => $ficha];
        }
        if (str_starts_with($accion, 'tracking_')) {
            if ($accion === 'tracking_adjuntar_evidencia') return ['success' => !empty($r['success']), 'message' => 'API oficial confirmó la evidencia.', 'api' => $r];
            $id = (int) ($p['id_ruta'] ?? $r['id_ruta'] ?? 0);
            $detalle = (new \Models\TrackingRecoleccion())->obtenerDetalleRuta($id);
            return ['success' => (bool) $detalle, 'message' => 'Ruta localizada después del cambio.', 'ruta' => $detalle];
        }
        if (str_starts_with($accion, 'viatico_')) {
            $id = (int) ($p['id_viatico'] ?? $r['id_viatico'] ?? 0);
            $row = $this->financial()->consultarViatico($id);
            return ['success' => (bool) $row, 'message' => 'Solicitud de viáticos verificada.', 'viatico' => $row];
        }
        if (str_starts_with($accion, 'condonacion_')) {
            $codigo = (string) ($p['codigo'] ?? $r['codigo'] ?? '');
            if ($accion === 'condonacion_preparar') {
                $codigo = (string) ($r['codigo'] ?? '');
                return ['success' => (bool) $this->financial()->consultarCondonacion($codigo), 'message' => 'Borrador localizado.', 'codigo' => $codigo];
            }
            if ($accion === 'condonacion_aprobar') return $this->financial()->verificarCondonacion($codigo);
            $row = $this->financial()->consultarCondonacion($codigo);
            return ['success' => (bool) $row, 'message' => 'Flujo de condonación verificado.', 'condonacion' => $row];
        }
        if (str_starts_with($accion, 'cierre_')) {
            if ($accion === 'cierre_preparar') {
                return ['success' => !empty($r['success']), 'message' => 'El modelo de cierre confirmó la preparación.', 'resultado' => $r];
            }
            $detalle = \Models\CierreCredito::getDetalleCierre((int) $p['id_cierre']);
            return ['success' => !empty($detalle['success']), 'message' => 'Cierre verificado.', 'detalle' => $detalle['datos'] ?? []];
        }
        return ['success' => !empty($r['success']), 'message' => 'Resultado confirmado por el modelo.'];
    }

    private static function definiciones(): array
    {
        $i = static fn(string $label, string $prompt): array => ['tipo' => 'int', 'label' => $label, 'prompt' => $prompt];
        $t = static fn(string $label, string $prompt): array => ['tipo' => 'text', 'label' => $label, 'prompt' => $prompt];
        $token = static fn(string $label, string $prompt): array => ['tipo' => 'token', 'label' => $label, 'prompt' => $prompt];
        return [
            'ticket_crear' => self::d('Crear ticket', 'tickets', ['/\\b(?:crear|levantar|registrar|abrir)\\b.*\\bticket\\b/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'id_tipo_ticket' => $i('tipo de ticket', '¿Cuál es el ID del tipo de ticket del catálogo?'),
                'id_origen_ticket' => $i('origen', '¿Cuál es el ID del origen del ticket?'),
                'descripcion' => $t('descripción', 'Describe con claridad el motivo del ticket.'),
            ]),
            'ticket_asignar' => self::d('Asignar responsable al ticket', 'tickets', ['/\\basignar\\b.*\\bticket\\b/u'], [
                'id_ticket' => $i('ticket', '¿Cuál es el ID del ticket?'),
                'id_persona' => $i('responsable', '¿Cuál es el ID de la persona responsable?'),
            ], [], false, 'ticket_desasignar'),
            'ticket_desasignar' => self::d('Quitar responsable del ticket', 'tickets', ['/\\b(?:desasignar|quitar\\b.*\\basignacion)\\b.*\\bticket\\b/u'], [
                'id_ticket' => $i('ticket', '¿Cuál es el ID del ticket?'),
            ]),
            'ticket_seguimiento' => self::d('Agregar seguimiento al ticket', 'tickets', ['/\\b(?:agregar|registrar|anadir)\\b.*\\bseguimiento\\b.*\\bticket\\b/u'], [
                'id_ticket' => $i('ticket', '¿Cuál es el ID del ticket?'),
                'seguimiento' => $t('seguimiento', '¿Qué seguimiento debo registrar?'),
            ]),
            'ticket_adjuntar_evidencia' => self::d('Adjuntar evidencia al ticket', 'tickets', ['/\\badjuntar\\b.*\\b(?:evidencia|archivo|documento)\\b.*\\bticket\\b/u'], [
                'id_ticket' => $i('ticket', '¿Cuál es el ID del ticket?'),
                'archivo_token' => $token('archivo', 'Adjunta la evidencia desde el clip.'),
            ]),
            'ticket_cerrar' => self::d('Cerrar ticket', 'tickets', ['/\\bcerrar\\b.*\\bticket\\b/u'], [
                'id_ticket' => $i('ticket', '¿Cuál es el ID del ticket?'),
            ], [], false, 'ticket_reabrir'),
            'ticket_reabrir' => self::d('Reabrir ticket', 'tickets', ['/\\breabrir\\b.*\\bticket\\b/u'], [
                'id_ticket' => $i('ticket', '¿Cuál es el ID del ticket cerrado?'),
            ], [], false, 'ticket_cerrar'),
            'direccion_registrar' => self::d('Registrar dirección', 'direcciones', ['/\\b(?:registrar|agregar|crear)\\b.*\\bdireccion\\b/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'direccion' => $t('dirección', 'Escribe la dirección completa.'),
            ]),
            'direccion_prioridad' => self::d('Cambiar prioridad de direcciones', 'direcciones', ['/\\b(?:cambiar|reordenar)\\b.*\\b(?:prioridad|orden)\\b.*\\bdireccion/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'orden_ids' => ['tipo' => 'csv_int', 'label' => 'orden', 'prompt' => 'Indica todos los IDs de dirección en el orden deseado, separados por comas.'],
            ]),
            'direccion_sincronizar' => self::d('Sincronizar direcciones con Segundómetro', 'direcciones', ['/\\bsincronizar\\b.*\\bdireccion.*\\bsegundometro\\b/u'], []),
            'direccion_corregir' => self::d('Corregir información geográfica', 'direcciones', ['/\\b(?:corregir|actualizar)\\b.*\\b(?:direccion|geograf)/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'id_direccion' => $i('dirección', '¿Cuál es el ID de la dirección?'),
                'cambios' => [
                    'tipo' => 'json',
                    'label' => 'cambios geográficos',
                    'prompt' => 'Pega un objeto JSON con los campos a corregir: direccion, codigo_postal, calle_numero, colonia, ciudad, estado o etapa.',
                ],
            ]),
            'despacho_asignar_credito' => self::d('Asignar crédito a Despachos', 'despachos', ['/\\basignar\\b.*\\bcredito\\b.*\\bdespacho/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'id_persona' => $i('responsable', '¿Cuál es el ID de la persona del despacho?'),
                'id_celula' => $i('célula', 'Indica la célula: 1 Despacho, 2 Call Center o 3 Campo.'),
            ], ['id_celula' => 1], false, 'despacho_desasignar_credito'),
            'despacho_desasignar_credito' => self::d('Desasignar crédito de Despachos', 'despachos', ['/\\bdesasignar\\b.*\\bcredito/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
            ]),
            'despacho_cambiar_estatus' => self::d('Cambiar estatus del crédito en Despachos', 'despachos', ['/\\bcambiar\\b.*\\bestatus\\b.*\\bcredito/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'estatus' => $t('estatus', 'Indica 1 para activo o 0 para inactivo.'),
            ]),
            'despacho_importar_excel' => self::d('Importar asignaciones de Despachos desde Excel', 'despachos', ['/\\bimportar\\b.*\\bexcel\\b.*\\bdespacho/u'], [
                'id_persona' => ['tipo' => 'int', 'allow_zero' => true, 'label' => 'despacho', 'prompt' => '¿Cuál es el ID de la persona destino? Usa 0 si el Excel contiene id_despacho por fila.'],
                'archivo_token' => $token('Excel', 'Adjunta el Excel desde el clip.'),
            ]),
            'despacho_adjuntar_documento' => self::d('Adjuntar documento de Despachos', 'despachos', ['/\\badjuntar\\b.*\\bdocumento\\b.*\\bdespacho/u'], [
                'id_persona' => $i('persona', '¿Cuál es el ID de la persona del despacho?'),
                'id_catalogo_documento' => $i('tipo documental', '¿Cuál es el ID del catálogo documental?'),
                'archivo_token' => $token('documento', 'Adjunta el documento desde el clip.'),
            ]),
            'almacen_confirmar_recepcion' => self::d('Confirmar recepción en Almacén Virtual', 'almacen', ['/\\bconfirmar\\b.*\\brecepcion\\b.*\\balmacen/u'], [
                'id_unidad' => $i('unidad', '¿Cuál es el ID de la unidad?'),
                'id_ubicacion' => $i('ubicación', '¿Cuál es el ID de la ubicación de recepción?'),
                'codigo_verificacion' => $t('código', 'Captura el código de verificación de ingreso.'),
                'vin' => $t('VIN', 'Captura el VIN/NIV de 17 caracteres.'),
                'kilometraje' => $i('kilometraje', 'Captura el kilometraje actual.'),
                'vin_coincide' => ['tipo' => 'bool', 'label' => 'VIN coincide', 'prompt' => '¿El VIN coincide? Responde sí o no.'],
                'evidencia_4_angulos' => ['tipo' => 'bool', 'label' => '4 ángulos', 'prompt' => '¿La evidencia de cuatro ángulos está completa?'],
                'evidencia_vin' => ['tipo' => 'bool', 'label' => 'evidencia VIN', 'prompt' => '¿La evidencia del VIN está completa?'],
                'documentos_completos' => ['tipo' => 'bool', 'label' => 'documentos', 'prompt' => '¿Los documentos están completos?'],
                'arranque_motor' => ['tipo' => 'bool', 'label' => 'arranque', 'prompt' => '¿El motor arranca?'],
                'sin_danos_mayores' => ['tipo' => 'bool', 'label' => 'daños', 'prompt' => '¿La unidad está sin daños mayores?'],
                'archivo_token_factura' => $token('factura', 'Adjunta la factura de la moto.'),
                'archivo_token_tarjeta' => $token('tarjeta', 'Adjunta la tarjeta de circulación.'),
            ]),
            'almacen_iniciar_revision' => self::d('Iniciar revisión mecánica', 'almacen', ['/\\biniciar\\b.*\\brevision\\b.*\\bmecanica/u'], [
                'id_unidad' => $i('unidad', '¿Cuál es el ID de la unidad?'),
            ]),
            'almacen_finalizar_revision' => self::d('Finalizar revisión mecánica', 'almacen', ['/\\bfinalizar\\b.*\\brevision\\b.*\\bmecanica/u'], [
                'id_unidad' => $i('unidad', '¿Cuál es el ID de la unidad?'),
                'dictamen' => $t('dictamen', 'Indica: reparada, fuera_presupuesto o irreparable.'),
                'diagnostico_general' => $t('diagnóstico', 'Escribe el diagnóstico general.'),
                'archivo_token' => $token('evidencia', 'Adjunta una evidencia de la revisión.'),
            ], ['slot_evidencia' => 'revision_mecanica', 'items' => []]),
            'almacen_crear_traspaso' => self::d('Crear traspaso de Almacén', 'almacen', ['/\\bcrear\\b.*\\btraspaso/u'], [
                'id_unidad' => $i('unidad', '¿Cuál es el ID de la unidad?'),
                'id_ubicacion_destino' => $i('destino', '¿Cuál es el ID de la ubicación destino?'),
                'tipo_transportista' => $t('tipo transportista', 'Indica interno o externo.'),
                'transportista_nombre' => $t('transportista', 'Escribe el nombre del transportista.'),
                'fecha_salida_estimada' => $t('salida', 'Indica fecha y hora estimada, por ejemplo 2026-08-01 10:00.'),
                'archivo_token' => $token('evidencia', 'Adjunta la evidencia fotográfica de origen.'),
            ], [], false, 'tracking_cancelar_ruta'),
            'almacen_confirmar_entrega' => self::d('Confirmar entrega de traspaso', 'almacen', ['/\\bconfirmar\\b.*\\b(?:entrega|recepcion)\\b.*\\btraspaso/u'], [
                'id_traspaso' => $i('traspaso', '¿Cuál es el ID del traspaso?'),
                'observaciones_destino' => $t('observaciones', 'Escribe las observaciones de recepción.'),
                'archivo_token' => $token('evidencia', 'Adjunta la evidencia fotográfica de destino.'),
            ]),
            'tracking_crear_ruta' => self::d('Crear ruta de Tracking', 'tracking', ['/\\bcrear\\b.*\\bruta\\b.*\\btracking/u'], [
                'nombre_ruta' => $t('nombre', '¿Cómo se llamará la ruta?'),
                'estado' => $t('estado', '¿En qué estado operará?'),
                'municipio' => $t('municipio', '¿En qué municipio operará?'),
                'fecha_programada' => $t('fecha', 'Indica la fecha programada YYYY-MM-DD.'),
                'tipo_transportista' => $t('tipo de transportista', 'Indica interno o externo.'),
                'id_transportista' => $i('transportista', '¿Cuál es el ID del transportista activo?'),
                'id_agencia_tracking' => ['tipo' => 'int', 'allow_zero' => true, 'label' => 'CEDIS relacionado', 'prompt' => 'Indica el ID del CEDIS relacionado; usa 0 si el transportista interno no requiere uno explícito.'],
                'id_cedis_destino' => $i('CEDIS destino', '¿Cuál es el ID del CEDIS destino?'),
                'creditos' => ['tipo' => 'json', 'label' => 'créditos', 'prompt' => 'Pega el arreglo JSON de créditos confirmados para la ruta.'],
            ], ['modo' => 'enviar']),
            'tracking_actualizar_ruta' => self::d('Actualizar ruta de Tracking', 'tracking', ['/\\b(?:actualizar|administrar)\\b.*\\bruta\\b.*\\btracking/u'], [
                'id_ruta' => $i('ruta', '¿Cuál es el ID de la ruta?'),
                'nombre_ruta' => $t('nombre', 'Escribe el nombre actualizado.'),
                'estado' => $t('estado', 'Escribe el estado.'),
                'municipio' => $t('municipio', 'Escribe el municipio.'),
                'fecha_programada' => $t('fecha', 'Indica la fecha programada YYYY-MM-DD.'),
            ]),
            'tracking_cancelar_ruta' => self::d('Cancelar ruta de Tracking', 'tracking_cancelar', ['/\\bcancelar\\b.*\\bruta\\b.*\\btracking/u'], [
                'id_ruta' => $i('ruta', '¿Cuál es el ID de la ruta?'),
                'motivo' => $t('motivo', 'Escribe el motivo de cancelación.'),
            ]),
            'tracking_adjuntar_evidencia' => self::d('Adjuntar evidencia de Tracking', 'tracking', ['/\\badjuntar\\b.*\\bevidencia\\b.*\\btracking/u'], [
                'id_ruta' => $i('ruta', '¿Cuál es el ID de la ruta?'),
                'archivo_token' => $token('evidencia', 'Adjunta la evidencia desde el clip.'),
            ]),
            'viatico_crear' => self::d('Crear solicitud de viáticos', 'viaticos', ['/\\bcrear\\b.*\\b(?:solicitud\\b.*)?viatico/u'], [
                'tipo_viatico' => $t('tipo', '¿Qué tipo de viático es?'),
                'descripcion' => $t('descripción', 'Describe el motivo del viático.'),
                'monto' => ['tipo' => 'decimal', 'label' => 'monto', 'prompt' => '¿Cuál es el monto solicitado?'],
            ], ['moneda' => 'MXN']),
            'viatico_adjuntar_comprobante' => self::d('Adjuntar comprobante de viáticos', 'viaticos', ['/\\badjuntar\\b.*\\bcomprobante\\b.*\\bviatico/u'], [
                'id_viatico' => $i('viático', '¿Cuál es el ID de la solicitud?'),
                'archivo_token' => $token('comprobante', 'Adjunta el comprobante desde el clip.'),
            ]),
            'viatico_enviar_autorizacion' => self::d('Enviar viático a autorización', 'viaticos', ['/\\benviar\\b.*\\bviatico\\b.*\\bautorizacion/u'], [
                'id_viatico' => $i('viático', '¿Cuál es el ID de la solicitud?'),
            ]),
            'viatico_consultar_avance' => self::d('Consultar avance de viáticos', 'viaticos', ['/\\b(?:consultar|ver)\\b.*\\b(?:avance|estatus)\\b.*\\bviatico/u'], [
                'id_viatico' => $i('viático', '¿Cuál es el ID de la solicitud?'),
            ], [], false, null, true),
            'viatico_aprobar' => self::d('Aprobar viático', 'viaticos_autorizar', ['/\\baprobar\\b.*\\bviatico/u'], [
                'id_viatico' => $i('viático', '¿Cuál es el ID de la solicitud?'),
            ], [], true),
            'viatico_rechazar' => self::d('Rechazar viático', 'viaticos_autorizar', ['/\\brechazar\\b.*\\bviatico/u'], [
                'id_viatico' => $i('viático', '¿Cuál es el ID de la solicitud?'),
                'motivo' => $t('motivo', 'Escribe el motivo del rechazo.'),
            ]),
            'viatico_registrar_pago' => self::d('Registrar pago de viáticos', 'viaticos_pagar', ['/\\b(?:registrar|confirmar)\\b.*\\bpago\\b.*\\bviatico/u'], [
                'id_viatico' => $i('viático', '¿Cuál es el ID de la solicitud?'),
                'referencia_pago' => $t('referencia', 'Escribe la referencia del pago realizado por el sistema financiero.'),
            ], [], true),
            'condonacion_preparar' => self::d('Preparar solicitud de condonación', 'condonaciones', ['/\\bpreparar\\b.*\\bcondonacion/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'total_condonado' => ['tipo' => 'decimal', 'label' => 'total', 'prompt' => '¿Cuál es el total a condonar?'],
                'comentario' => $t('justificación', 'Escribe la justificación de negocio.'),
            ], ['id_motivo_condonacion' => 1, 'detalles' => []]),
            'condonacion_enviar' => self::d('Enviar condonación a autorización', 'condonaciones', ['/\\benviar\\b.*\\bcondonacion\\b.*\\bautorizacion/u'], [
                'codigo' => ['tipo' => 'code', 'label' => 'solicitud', 'prompt' => 'Indica el código CON- de la solicitud.'],
            ]),
            'condonacion_aprobar' => self::d('Aprobar y aplicar condonación', 'condonaciones_autorizar', ['/\\baprobar\\b.*\\bcondonacion/u'], [
                'codigo' => ['tipo' => 'code', 'label' => 'solicitud', 'prompt' => 'Indica el código CON- de la solicitud.'],
            ], [], true),
            'condonacion_rechazar' => self::d('Rechazar condonación', 'condonaciones_autorizar', ['/\\brechazar\\b.*\\bcondonacion/u'], [
                'codigo' => ['tipo' => 'code', 'label' => 'solicitud', 'prompt' => 'Indica el código CON- de la solicitud.'],
                'motivo' => $t('motivo', 'Escribe el motivo de rechazo.'),
            ]),
            'condonacion_verificar' => self::d('Verificar resultado financiero de condonación', 'condonaciones', ['/\\bverificar\\b.*\\b(?:resultado\\b.*)?condonacion/u'], [
                'codigo' => ['tipo' => 'code', 'label' => 'solicitud', 'prompt' => 'Indica el código CON- de la solicitud.'],
            ], [], false, null, true),
            'cierre_preparar' => self::d('Preparar cierre de crédito', 'cierre_validar', ['/\\bpreparar\\b.*\\bcierre\\b.*\\bcredito/u'], [
                'id_credito' => $i('crédito', '¿Cuál es el ID del crédito?'),
                'nombre_cliente' => $t('cliente', 'Escribe el nombre del cliente.'),
            ]),
            'cierre_enviar_autorizacion' => self::d('Enviar cierre a Vo.Bo.', 'cierre_proceso', ['/\\benviar\\b.*\\bcierre\\b.*\\b(?:autorizacion|vo bo|vobo)/u'], [
                'id_cierre' => $i('cierre', '¿Cuál es el ID del seguimiento de cierre?'),
                'comentario' => $t('comentario', 'Escribe el comentario para Dirección de Cobranza.'),
            ]),
            'cierre_aprobar' => self::d('Aprobar Vo.Bo. de cierre', 'cierre_autorizar', ['/\\baprobar\\b.*\\b(?:cierre|vo bo|vobo)/u'], [
                'id_cierre' => $i('cierre', '¿Cuál es el ID del seguimiento de cierre?'),
            ], ['comentario' => 'Aprobado mediante Leonidas'], true),
            'cierre_rechazar' => self::d('Rechazar Vo.Bo. de cierre', 'cierre_autorizar', ['/\\brechazar\\b.*\\b(?:cierre|vo bo|vobo)/u'], [
                'id_cierre' => $i('cierre', '¿Cuál es el ID del seguimiento de cierre?'),
                'comentario' => $t('motivo', 'Escribe el motivo del rechazo.'),
            ]),
            'cierre_enviar_cartera' => self::d('Enviar cierre a Cartera', 'cierre_proceso', ['/\\benviar\\b.*\\bcierre\\b.*\\bcartera/u'], [
                'id_cierre' => $i('cierre', '¿Cuál es el ID del seguimiento de cierre?'),
            ], ['estatus_origen' => 'en_proceso'], true),
            'cierre_verificar' => self::d('Verificar resultado financiero del cierre', 'cierre_consultar', ['/\\bverificar\\b.*\\b(?:resultado\\b.*)?cierre\\b.*\\bcredito/u'], [
                'id_cierre' => $i('cierre', '¿Cuál es el ID del seguimiento de cierre?'),
            ], [], false, null, true),
        ];
    }

    private static function d(
        string $etiqueta,
        string $permiso,
        array $patrones,
        array $campos,
        array $defaults = [],
        bool $sensible = false,
        ?string $reversion = null,
        bool $consulta = false
    ): array {
        return compact('etiqueta', 'permiso', 'patrones', 'campos', 'defaults', 'sensible', 'reversion', 'consulta');
    }

    private function primerFaltante(array $def, array $datos): ?string
    {
        foreach ((array) ($def['campos'] ?? []) as $campo => $meta) {
            if (!array_key_exists($campo, $datos) || $datos[$campo] === '' || $datos[$campo] === null || $datos[$campo] === []) {
                return $campo;
            }
            if (($meta['tipo'] ?? '') === 'int'
                && (int) $datos[$campo] <= 0
                && empty($meta['allow_zero'])
            ) {
                return $campo;
            }
            if (($meta['tipo'] ?? '') === 'decimal' && (float) $datos[$campo] <= 0) {
                return $campo;
            }
        }
        return null;
    }

    private function validarPayload(array $def, array $payload): void
    {
        $faltante = $this->primerFaltante($def, $payload);
        if ($faltante !== null) {
            throw new \InvalidArgumentException('Falta el campo obligatorio ' . $faltante . '.');
        }
    }

    private function validarReglasAccion(string $accion, array $payload, array $contexto): void
    {
        if ($accion === 'despacho_asignar_credito'
            && !in_array((int) ($payload['id_celula'] ?? 0), [1, 2, 3], true)
        ) {
            throw new \InvalidArgumentException('La célula debe ser 1 Despacho, 2 Call Center o 3 Campo.');
        }
        $celulaPermitida = $contexto['permisos_agente']['despachos_celula'] ?? null;
        if ($accion === 'despacho_asignar_credito'
            && $celulaPermitida !== null
            && (int) $payload['id_celula'] !== (int) $celulaPermitida
        ) {
            throw new \DomainException('Tu perfil solo permite operar la célula ' . (int) $celulaPermitida . '.');
        }
        if ($accion === 'despacho_cambiar_estatus'
            && !in_array((string) ($payload['estatus'] ?? ''), ['0', '1'], true)
        ) {
            throw new \InvalidArgumentException('El estatus de Despachos solo puede ser 0 o 1.');
        }
        if ($accion === 'almacen_finalizar_revision'
            && !in_array((string) ($payload['dictamen'] ?? ''), ['reparada', 'fuera_presupuesto', 'irreparable'], true)
        ) {
            throw new \InvalidArgumentException('El dictamen debe ser reparada, fuera_presupuesto o irreparable.');
        }
        if ($accion === 'tracking_crear_ruta'
            && !in_array(strtolower((string) ($payload['tipo_transportista'] ?? '')), ['interno', 'externo'], true)
        ) {
            throw new \InvalidArgumentException('El tipo de transportista debe ser interno o externo.');
        }
        if ($accion === 'direccion_corregir') {
            $permitidos = ['direccion', 'codigo_postal', 'calle_numero', 'colonia', 'ciudad', 'estado', 'etapa'];
            $cambios = is_array($payload['cambios'] ?? null) ? $payload['cambios'] : [];
            if (!$cambios || array_diff(array_keys($cambios), $permitidos)) {
                throw new \InvalidArgumentException('Los cambios geográficos contienen campos vacíos o no permitidos.');
            }
        }
    }

    private function validarAdjuntos(string $accion, array $payload, array $def, array $contexto): void
    {
        if (isset($this->adapters['attachment_validate'])) {
            ($this->adapters['attachment_validate'])($accion, $payload, $def, $contexto);
            return;
        }
        $actorId = (int) ($contexto['actor_id'] ?? 0);
        $imagenes = ['jpg', 'jpeg', 'png', 'webp'];
        $videos = ['mp4', 'mov', 'webm'];
        foreach ((array) ($def['campos'] ?? []) as $campo => $metaCampo) {
            if (($metaCampo['tipo'] ?? '') !== 'token' || empty($payload[$campo])) {
                continue;
            }
            $meta = $this->attachments()->metadata((string) $payload[$campo], $actorId);
            $extension = strtolower((string) ($meta['extension'] ?? ''));
            if ($accion === 'despacho_importar_excel' && !in_array($extension, ['xls', 'xlsx'], true)) {
                throw new \InvalidArgumentException('La importación de Despachos requiere un Excel .xls o .xlsx.');
            }
            if (in_array($accion, ['almacen_crear_traspaso', 'almacen_confirmar_entrega'], true)
                && !in_array($extension, $imagenes, true)
            ) {
                throw new \InvalidArgumentException('La evidencia de traspaso debe ser una imagen.');
            }
            if ($accion === 'almacen_finalizar_revision'
                && !in_array($extension, array_merge($imagenes, $videos), true)
            ) {
                throw new \InvalidArgumentException('La evidencia mecánica debe ser imagen o video.');
            }
        }
    }

    private function previsualizarExcelDespachos(string $ruta, ?int $celulaPermitida = null): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            require_once dirname(__DIR__) . '/bootstrap_composer.php';
            sparta_require_composer_autoload();
        }
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($ruta);
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($ruta)->getActiveSheet();
            $totalFilas = (int) $sheet->getHighestDataRow();
            $highestRow = min($totalFilas, 2501);
            $highestColumn = min(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn()),
                50
            );
            $normalizarHeader = static function (string $value): string {
                $value = mb_strtolower($value, 'UTF-8');
                $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
                return trim((string) preg_replace('/[^a-z0-9]+/', '_', $ascii), '_');
            };
            $headers = [];
            $normalizados = [];
            $headerRow = 0;
            for ($candidateRow = 1; $candidateRow <= min(60, $highestRow); $candidateRow++) {
                $candidateHeaders = [];
                $candidateNormalized = [];
                for ($column = 1; $column <= $highestColumn; $column++) {
                    $value = trim((string) $sheet->getCell([$column, $candidateRow])->getValue());
                    $candidateHeaders[$column] = $value;
                    $candidateNormalized[$column] = $normalizarHeader($value);
                }
                if (in_array('id_credito', $candidateNormalized, true)
                    || in_array('idcredito', $candidateNormalized, true)
                ) {
                    $headerRow = $candidateRow;
                    $headers = $candidateHeaders;
                    $normalizados = $candidateNormalized;
                    break;
                }
            }
            if ($headerRow === 0) {
                return ['success' => false, 'message' => 'El Excel no contiene una columna id_credito reconocible.'];
            }
            $indiceCelula = array_search('id_celula', $normalizados, true);
            if ($indiceCelula === false) {
                $indiceCelula = array_search('idcelula', $normalizados, true);
            }
            if ($celulaPermitida !== null && $indiceCelula !== false) {
                for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                    $celulaFila = (int) $sheet->getCell([(int) $indiceCelula, $row])->getValue();
                    if ($celulaFila > 0 && $celulaFila !== $celulaPermitida) {
                        return [
                            'success' => false,
                            'message' => 'El Excel contiene la célula ' . $celulaFila
                                . ' en la fila ' . $row . ', pero tu perfil solo permite la célula ' . $celulaPermitida . '.',
                        ];
                    }
                }
            }
            return [
                'success' => true,
                'message' => 'Excel prevalidado sin aplicar cambios.',
                'archivo_hash' => hash_file('sha256', $ruta),
                'hoja' => $sheet->getTitle(),
                'fila_encabezado' => $headerRow,
                'encabezados' => array_values(array_filter($headers, static fn(string $value): bool => $value !== '')),
                'filas_estimadas' => max(0, $highestRow - $headerRow),
                'truncado' => $totalFilas > 2501,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'No se pudo prevalidar el Excel: ' . $e->getMessage()];
        }
    }

    private function extraerValoresNombrados(string $mensaje, array $def): array
    {
        $out = [];
        if (preg_match_all('/(?:^|[;\\n])\\s*([a-zA-Z_áéíóúñ]+)\\s*=\\s*(.*?)(?=\\s*(?:;|\\n|$))/u', $mensaje, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $clave = self::normalizarEstatico($m[1]);
                $clave = str_replace(' ', '_', $clave);
                if (!isset($def['campos'][$clave]) && !array_key_exists($clave, (array) ($def['defaults'] ?? []))) continue;
                $meta = $def['campos'][$clave] ?? ['tipo' => 'text'];
                $valor = $this->convertir(trim($m[2]), $meta);
                if ($valor !== null) $out[$clave] = $valor;
            }
        }
        return $out;
    }

    private function extraerValoresDirectos(string $mensaje, array $def): array
    {
        $out = [];
        $map = [
            'id_ticket' => '/\\b(?:ticket|tck)\\s*#?\\s*(\\d+)\\b/i',
            'id_credito' => '/\\bcredito\\s*#?\\s*(\\d+)\\b/i',
            'id_persona' => '/\\b(?:persona|responsable|gestor|despacho)\\s*#?\\s*(\\d+)\\b/i',
            'id_unidad' => '/\\bunidad\\s*#?\\s*(\\d+)\\b/i',
            'id_traspaso' => '/\\btraspaso\\s*#?\\s*(\\d+)\\b/i',
            'id_ruta' => '/\\bruta\\s*#?\\s*(\\d+)\\b/i',
            'id_viatico' => '/\\bviatico\\s*#?\\s*(\\d+)\\b/i',
            'id_cierre' => '/\\bcierre\\s*#?\\s*(\\d+)\\b/i',
            'codigo' => '/\\b((?:CON|LEO)-[A-Z0-9]+)\\b/i',
        ];
        foreach ($map as $campo => $regex) {
            if (isset($def['campos'][$campo]) && preg_match($regex, $mensaje, $m)) {
                $out[$campo] = str_starts_with($campo, 'id_') ? (int) $m[1] : strtoupper($m[1]);
            }
        }
        $campos = array_keys((array) ($def['campos'] ?? []));
        if (count($campos) === 1 && ($def['campos'][$campos[0]]['tipo'] ?? '') === 'int' && !isset($out[$campos[0]])
            && preg_match('/\\b(\\d+)\\b/', $mensaje, $m)
        ) {
            $out[$campos[0]] = (int) $m[1];
        }
        return $out;
    }

    private function aplicarAdjuntoContexto(array $datos, array $def, array $contexto): array
    {
        $token = trim((string) ($contexto['archivo_token'] ?? ''));
        if ($token === '') return $datos;
        foreach ((array) ($def['campos'] ?? []) as $campo => $meta) {
            if (($meta['tipo'] ?? '') === 'token' && empty($datos[$campo])) {
                $datos[$campo] = $token;
                break;
            }
        }
        return $datos;
    }

    private function valorConversacional(string $mensaje, array $meta)
    {
        return $this->convertir(trim($mensaje), $meta);
    }

    private function convertir(string $valor, array $meta)
    {
        $tipo = (string) ($meta['tipo'] ?? 'text');
        if ($tipo === 'int') {
            return preg_match('/-?\\d+/', $valor, $m) ? (int) $m[0] : null;
        }
        if ($tipo === 'decimal') {
            $n = preg_replace('/[^0-9.\\-]/', '', str_replace(',', '', $valor));
            return is_numeric($n) ? round((float) $n, 2) : null;
        }
        if ($tipo === 'csv_int') {
            $ids = array_values(array_unique(array_filter(array_map('intval', preg_split('/[^0-9]+/', $valor) ?: []))));
            return $ids ?: null;
        }
        if ($tipo === 'json') {
            $json = json_decode($valor, true);
            return is_array($json) ? $json : null;
        }
        if ($tipo === 'bool') {
            $n = self::normalizarEstatico($valor);
            if (preg_match('/^(si|sí|true|1|correcto|completo)$/u', $n)) return true;
            if (preg_match('/^(no|false|0|incorrecto|incompleto)$/u', $n)) return false;
            return null;
        }
        if ($tipo === 'code') {
            return preg_match('/\\b((?:CON|LEO)-[A-Z0-9]+)\\b/i', $valor, $m) ? strtoupper($m[1]) : null;
        }
        if ($tipo === 'token') {
            return preg_match('/^[a-f0-9]{20,}$/i', $valor) ? $valor : null;
        }
        return $valor !== '' ? mb_substr($valor, 0, 5000) : null;
    }

    private function pregunta(array $def, string $campo): array
    {
        $meta = $def['campos'][$campo] ?? [];
        return $this->respuesta(
            (string) ($meta['prompt'] ?? 'Indica ' . ($meta['label'] ?? $campo) . '.'),
            ($meta['tipo'] ?? '') === 'token' ? 'agente_adjunto_requerido' : 'agente_pregunta'
        ) + ['campo_pendiente' => $campo];
    }

    private function guardarTarea(string $accion, array $datos, array $contexto): void
    {
        $_SESSION[self::TASK_KEY] = [
            'accion' => $accion,
            'datos' => $datos,
            'actor_id' => (int) ($contexto['actor_id'] ?? 0),
            'expira_en' => time() + self::TASK_TTL,
        ];
    }

    private function tareaActual(int $actorId): ?array
    {
        $tarea = is_array($_SESSION[self::TASK_KEY] ?? null) ? $_SESSION[self::TASK_KEY] : null;
        if (!$tarea) return null;
        if ((int) ($tarea['expira_en'] ?? 0) < time() || (int) ($tarea['actor_id'] ?? 0) !== $actorId) {
            $this->limpiarTarea();
            return null;
        }
        return $tarea;
    }

    private function tienePermiso(array $def, array $contexto): bool
    {
        return !empty($contexto['permisos_agente'][(string) ($def['permiso'] ?? '')]);
    }

    private function resumen(array $def, array $payload): string
    {
        $partes = [];
        foreach ((array) ($def['campos'] ?? []) as $campo => $meta) {
            if (!array_key_exists($campo, $payload)) continue;
            $valor = $payload[$campo];
            if (($meta['tipo'] ?? '') === 'token') {
                $valor = 'archivo validado';
            } elseif (is_array($valor)) {
                $valor = count($valor) . ' elemento(s)';
            } elseif (is_bool($valor)) {
                $valor = $valor ? 'sí' : 'no';
            } else {
                $valor = mb_substr((string) $valor, 0, 100);
            }
            $partes[] = ($meta['label'] ?? $campo) . ': ' . $valor;
        }
        return $def['etiqueta'] . ($partes ? ' (' . implode(', ', $partes) . ')' : '');
    }

    private function resumenEstado(array $estado): string
    {
        $mensaje = $this->mensajeResultado($estado, 'fuente consultada');
        return mb_substr(preg_replace('/\\s+/', ' ', $mensaje) ?: 'fuente consultada', 0, 350);
    }

    private function mensajeResultado(array $resultado, string $fallback = 'Operación procesada.'): string
    {
        return trim((string) ($resultado['message'] ?? $resultado['mensaje'] ?? $fallback)) ?: $fallback;
    }

    private function normalizarResultado(array $resultado): array
    {
        if (!array_key_exists('success', $resultado)) {
            $resultado['success'] = !empty($resultado['exito']) || !empty($resultado['ok']);
        }
        if (!isset($resultado['message']) && isset($resultado['mensaje'])) {
            $resultado['message'] = (string) $resultado['mensaje'];
        }
        return $resultado;
    }

    private function sanitizarComprobante(array $valor): array
    {
        $bloqueadas = ['password', 'token', 'archivo_token', 'nip', 'secret', 'api_key', 'ruta_absoluta', 'payload_json'];
        $out = [];
        foreach ($valor as $k => $v) {
            $key = strtolower((string) $k);
            if (in_array($key, $bloqueadas, true) || str_contains($key, 'password') || str_contains($key, 'secret')) {
                continue;
            }
            if (is_array($v)) {
                $out[$k] = count($v) > 100
                    ? ['total' => count($v), 'muestra' => array_slice($v, 0, 20)]
                    : $this->sanitizarComprobante($v);
            } elseif (is_string($v)) {
                $out[$k] = mb_substr($v, 0, 1000);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    private function limpiarInternos(array $payload): array
    {
        unset($payload['_autorizacion_reforzada']);
        return $payload;
    }

    private function canonicalJson(array $payload): string
    {
        ksort($payload);
        foreach ($payload as &$value) {
            if (is_array($value)) {
                $value = json_decode($this->canonicalJson($value), true);
            }
        }
        unset($value);
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function store()
    {
        if ($this->store === null) $this->store = new LeonidasOperationStore();
        return $this->store;
    }

    private function attachments(): LeonidasAttachmentService
    {
        if ($this->attachments === null) $this->attachments = new LeonidasAttachmentService();
        return $this->attachments;
    }

    private function financial(): LeonidasFinancialWorkflowService
    {
        if ($this->financial === null) {
            $this->financial = new LeonidasFinancialWorkflowService(null, $this->attachments());
        }
        return $this->financial;
    }

    private function respuesta(string $mensaje, string $tipo): array
    {
        return ['mensaje' => $mensaje, 'tipo' => $tipo];
    }

    private static function normalizarEstatico(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if (is_string($convertido) && $convertido !== '') $texto = $convertido;
        return trim(preg_replace('/\\s+/', ' ', preg_replace('/[^a-z0-9#=_;.,\\-\\s\\[\\]{}":]/', ' ', $texto) ?: $texto) ?: $texto);
    }
}
