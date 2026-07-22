<?php

namespace Services;

require_once __DIR__ . '/LeonidasCapitalHumanoService.php';
require_once __DIR__ . '/LeonidasSpreadsheetService.php';
require_once __DIR__ . '/LeonidasLocalAgentService.php';
require_once __DIR__ . '/LeonidasMotosAdjudicadasService.php';
require_once __DIR__ . '/../core/DatabaseLegacy.php';

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
    private LeonidasCapitalHumanoService $capitalHumano;
    private LeonidasLocalAgentService $agentesLocales;
    private LeonidasMotosAdjudicadasService $motosAdjudicadas;

    public function __construct(array $adapters = [])
    {
        $capitalHumano = $adapters['capital_humano_service'] ?? null;
        $agentesLocales = $adapters['local_agent_service'] ?? null;
        $motosAdjudicadas = $adapters['motos_adjudicadas_service'] ?? null;
        unset($adapters['capital_humano_service'], $adapters['local_agent_service'], $adapters['motos_adjudicadas_service']);
        $this->capitalHumano = $capitalHumano instanceof LeonidasCapitalHumanoService
            ? $capitalHumano
            : new LeonidasCapitalHumanoService();
        $this->agentesLocales = $agentesLocales instanceof LeonidasLocalAgentService
            ? $agentesLocales
            : new LeonidasLocalAgentService();
        $this->motosAdjudicadas = $motosAdjudicadas instanceof LeonidasMotosAdjudicadasService
            ? $motosAdjudicadas
            : new LeonidasMotosAdjudicadasService();
        $this->adapters = $adapters + [
            'convenio_ofertas' => static fn(int $idCredito): array => \Models\Convenios::getOfertasElegibles($idCredito),
            'convenio_guardar' => static fn(array $datos): array => \Models\Convenios::guardarConvenio($datos),
            'moto_buscar' => static fn(int $idCredito): array => (new \Models\Adjudicacion())->buscarCreditoPorId($idCredito),
            'moto_responsables' => static fn(): array => (new \Models\Adjudicacion())->obtenerResponsables(),
            'moto_asignar' => static function (int $idPersona, int $idCredito, int $actorId): array {
                $adjudicacion = new \Models\Adjudicacion();
                $local = $adjudicacion->reasignarCredito($idPersona, $idCredito, $actorId);
                if (empty($local['success'])) {
                    return $local;
                }

                $asignacionLocal = null;
                foreach ($adjudicacion->obtenerHistorialCredito($idCredito) as $fila) {
                    if ((string) ($fila['estatus'] ?? '') === '1' && (int) ($fila['id_persona'] ?? 0) === $idPersona) {
                        $asignacionLocal = $fila;
                        break;
                    }
                }
                if ($asignacionLocal === null) {
                    return [
                        'success' => false,
                        'partial' => true,
                        'message' => 'La asignacion se registro, pero no pudo verificarse en Sparta. No se intento crear la tarea Legacy.',
                        'local' => ['success' => false],
                    ];
                }

                $legacy = (new \Models\MotosAdjudicadas())->crearTaskLegacyMotoAutorizada(
                    $idCredito,
                    $idPersona,
                    ['nombre_cliente' => trim((string) ($asignacionLocal['nombre_cliente'] ?? ''))]
                );
                $completo = !empty($legacy['success']) && !empty($legacy['verificacion']['success']);

                return [
                    'success' => $completo,
                    'partial' => !$completo,
                    'message' => $completo
                        ? 'Asignacion verificada en Sparta y sincronizada con Legacy.'
                        : 'La asignacion quedo activa en Sparta, pero fallo la sincronizacion o verificacion en Legacy: '
                            . trim((string) ($legacy['message'] ?? 'motivo no informado')),
                    'local' => [
                        'success' => true,
                        'id_credito' => $idCredito,
                        'id_persona' => $idPersona,
                    ],
                    'legacy' => $legacy,
                ];
            },
            'moto_responsable_activo' => static fn(int $idPersona): bool => (new \Models\Adjudicacion())->idPersonaEsResponsableActivo($idPersona),
            'dictamen_diagnosticar' => static fn(int $idCredito): array => (new \Models\Adjudicacion())->diagnosticarDictamenWebMoto($idCredito),
            'dictamen_autorizacion' => static fn(int $idUsuario): array => (new \Models\Adjudicacion())->usuarioPuedeDesbloquearComponentes($idUsuario),
            'dictamen_desbloquear_s2' => static fn(int $idCredito, string $nip, int $idUsuario): array =>
                (new \Models\Adjudicacion())->desbloquearValidacionS2DictamenWebMoto($idCredito, $nip, $idUsuario),
            'dictamen_desbloquear_componentes' => static fn(int $idCredito, string $nip, int $idUsuario, string $ip): array =>
                (new \Models\Adjudicacion())->desbloquearComponentesDictamenWebMoto($idCredito, $nip, $idUsuario, $ip),
        ];
    }

    public static function accionesEjecutables(): array
    {
        return array_merge(
            ['convenio_crear', 'moto_asignar', 'excel_aplicar', 'cartera_reactivar_tarea_movil'],
            LeonidasCapitalHumanoService::accionesEjecutables(),
            LeonidasLocalAgentService::accionesEjecutables(),
            LeonidasMotosAdjudicadasService::accionesEjecutables()
        );
    }

    public static function puedeEjecutar(string $accion): bool
    {
        return in_array($accion, self::accionesEjecutables(), true);
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        // Do not depend on the caller to normalize casing or accents before routing an action.
        $normalizado = $this->normalizar($normalizado !== '' ? $normalizado : $mensaje);

        $capitalHumano = $this->capitalHumano->resolver($mensaje, $normalizado, $contexto);
        if ($capitalHumano !== null) {
            return $capitalHumano;
        }

        $agenteLocal = $this->agentesLocales->resolver($mensaje, $normalizado, $contexto);
        if ($agenteLocal !== null) {
            return $agenteLocal;
        }

        $motosAdjudicadas = $this->motosAdjudicadas->resolver($mensaje, $normalizado, $contexto);
        if ($motosAdjudicadas !== null) {
            return $motosAdjudicadas;
        }

        if ($this->solicitaDiagnosticoTareaMovil($normalizado)) {
            return $this->resolverDiagnosticoTareaMovil($mensaje, $contexto);
        }

        $tarea = $this->tareaActual((int) ($contexto['actor_id'] ?? 0));
        if ($tarea !== null) {
            if (preg_match('/\b(cancelar|cancela|olvida|deten|detener)\b/u', $normalizado)) {
                $this->limpiarTarea();
                return $this->respuesta('Tarea cancelada. No se modificó ningún dato.', 'agente_cancelado');
            }

            return match ((string) ($tarea['tipo'] ?? '')) {
                'convenio' => $this->continuarConvenio($mensaje, $normalizado, $tarea, $contexto),
                'dictamen_moto' => $this->continuarDictamenMoto($mensaje, $normalizado, $tarea, $contexto),
                default => $this->continuarMoto($mensaje, $normalizado, $tarea, $contexto),
            };
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

        if ($this->solicitaDictamenMoto($normalizado)) {
            return $this->resolverDictamenMoto($mensaje, $normalizado, $contexto);
        }

        if ($this->solicitaMoto($normalizado)) {
            if (empty($contexto['permisos_agente']['motos'])) {
                return $this->respuesta(
                    'No puedo iniciar la adjudicación porque tu perfil no tiene acceso administrativo a Motos Adjudicadas.',
                    'agente_denegado'
                );
            }
            $directa = $this->prepararMotoDesdeMensajeCompleto($mensaje, $contexto);
            if ($directa !== null) {
                return $directa;
            }
            $this->guardarTarea('moto', 'credito', [], $contexto);
            return $this->respuesta('Vamos a asignar el crédito para adjudicación de moto. ¿Cuál es el ID del crédito?', 'agente_pregunta');
        }

        return null;
    }

    private function resolverDictamenMoto(string $mensaje, string $normalizado, array $contexto): array
    {
        if (empty($contexto['permisos_agente']['motos'])) {
            return $this->respuesta(
                'No puedo consultar ni cambiar el dictamen porque tu perfil no tiene acceso administrativo a Motos Adjudicadas.',
                'agente_denegado'
            );
        }

        $idCredito = $this->extraerCredito($mensaje);
        if ($idCredito <= 0) {
            return $this->respuesta('Indica el ID numerico del credito que debo diagnosticar.', 'agente_pregunta');
        }

        $diagnostico = (array) $this->llamar('dictamen_diagnosticar', $idCredito);
        if (empty($diagnostico['success'])) {
            return $this->respuesta(
                'No pude diagnosticar el credito ' . $idCredito . ': ' . $this->mensajeResultado($diagnostico),
                'agente_error'
            );
        }

        $accion = $this->accionDictamenMoto($normalizado);
        if ($accion === 'diagnosticar') {
            return $this->respuesta($this->resumenDiagnosticoDictamen($diagnostico), 'agente_diagnostico');
        }

        $autorizacion = (array) $this->llamar('dictamen_autorizacion', (int) ($contexto['actor_id'] ?? 0));
        if (empty($autorizacion['authorized'])) {
            return $this->respuesta($this->mensajeAutorizacionDictamen($autorizacion), 'agente_denegado');
        }

        if ($accion === 'desbloquear_s2') {
            if (empty($diagnostico['desbloqueo_s2_disponible'])) {
                return $this->respuesta(
                    $this->resumenDiagnosticoDictamen($diagnostico)
                    . "\n\nNo puedo usar el desbloqueo S2 porque el diagnostico presenta otros bloqueos o no cumple la regla de bloqueo exclusivo por S2.",
                    'agente_denegado'
                );
            }

            $this->guardarTarea('dictamen_moto', 'nip', [
                'id_credito' => $idCredito,
                'accion' => 'desbloquear_s2',
            ], $contexto);

            return $this->respuesta(
                $this->resumenDiagnosticoDictamen($diagnostico)
                . "\n\nEl credito esta bloqueado unicamente por validacion S2. Para autorizar este cambio necesito tu NIP de desbloqueo de 6 digitos.",
                'agente_nip_requerido'
            ) + ['entrada_segura' => 'nip'];
        }

        $this->guardarTarea('dictamen_moto', 'confirmar_destructivo', [
            'id_credito' => $idCredito,
            'accion' => 'desbloquear_componentes',
        ], $contexto);

        return $this->respuesta(
            $this->resumenDiagnosticoDictamen($diagnostico)
            . "\n\nEsta operacion es destructiva: puede eliminar tareas y asignaciones Legacy, dictamenes Legacy y la operacion local de Sparta del credito. La accion queda auditada. Escribe CONFIRMAR LIMPIEZA para continuar o CANCELAR para salir.",
            'agente_confirmacion_destructiva'
        );
    }

    private function continuarDictamenMoto(string $mensaje, string $normalizado, array $tarea, array $contexto): array
    {
        $datos = is_array($tarea['datos'] ?? null) ? $tarea['datos'] : [];
        $idCredito = (int) ($datos['id_credito'] ?? 0);
        $accion = (string) ($datos['accion'] ?? '');
        $paso = (string) ($tarea['paso'] ?? '');

        if ($idCredito <= 0 || !in_array($accion, ['desbloquear_s2', 'desbloquear_componentes'], true)) {
            $this->limpiarTarea();
            return $this->respuesta('La solicitud de dictamen perdio su contexto. Inicia nuevamente con el ID del credito.', 'agente_error');
        }

        $autorizacion = (array) $this->llamar('dictamen_autorizacion', (int) ($contexto['actor_id'] ?? 0));
        if (empty($autorizacion['authorized'])) {
            $this->limpiarTarea();
            return $this->respuesta($this->mensajeAutorizacionDictamen($autorizacion), 'agente_denegado');
        }

        if ($paso === 'confirmar_destructivo') {
            if ($normalizado !== 'confirmar limpieza') {
                return $this->respuesta(
                    'Aun no se ejecuto ningun cambio. Escribe CONFIRMAR LIMPIEZA si aceptas borrar los componentes existentes del credito '
                    . $idCredito . ', o CANCELAR para salir.',
                    'agente_confirmacion_destructiva'
                );
            }

            $this->guardarTarea('dictamen_moto', 'nip', $datos, $contexto);
            return $this->respuesta(
                'Confirmacion destructiva registrada para el credito ' . $idCredito
                . '. Ahora captura tu NIP de desbloqueo de 6 digitos. El NIP se validara de forma segura y no aparecera en el chat.',
                'agente_nip_requerido'
            ) + ['entrada_segura' => 'nip'];
        }

        if ($paso !== 'nip') {
            $this->limpiarTarea();
            return $this->respuesta('El flujo de dictamen no reconoce el paso actual. No se modifico ningun dato.', 'agente_error');
        }

        $nip = trim($mensaje);
        if (preg_match('/^\d{6}$/', $nip) !== 1) {
            return $this->respuesta(
                'El NIP debe contener exactamente 6 digitos. No se realizo ningun cambio; vuelve a capturarlo.',
                'agente_nip_invalido'
            ) + ['entrada_segura' => 'nip'];
        }

        if ($accion === 'desbloquear_s2') {
            $resultado = (array) $this->llamar(
                'dictamen_desbloquear_s2',
                $idCredito,
                $nip,
                (int) ($contexto['actor_id'] ?? 0)
            );
        } else {
            $resultado = (array) $this->llamar(
                'dictamen_desbloquear_componentes',
                $idCredito,
                $nip,
                (int) ($contexto['actor_id'] ?? 0),
                (string) ($_SERVER['REMOTE_ADDR'] ?? '')
            );
        }
        unset($nip);

        if (empty($resultado['success'])) {
            return $this->respuesta(
                'No se pudo autorizar el cambio: ' . $this->mensajeResultado($resultado)
                . ' No se realizo ningun desbloqueo.',
                'agente_nip_invalido'
            ) + ['entrada_segura' => 'nip'];
        }

        $this->limpiarTarea();
        if ($accion === 'desbloquear_s2') {
            return $this->respuesta(
                'Listo. La validacion S2 del credito ' . $idCredito
                . ' fue desbloqueada con autorizacion verificada. Ya puede continuar la simulacion o el envio de la tarea; no se eliminaron componentes.',
                'agente_ejecutado'
            ) + ['ejecucion' => ['accion' => $accion, 'id_credito' => $idCredito]];
        }

        $eliminados = is_array($resultado['deleted'] ?? null) ? $resultado['deleted'] : [];
        return $this->respuesta(
            'Listo. Se desbloquearon los componentes del credito ' . $idCredito . ' y la accion quedo auditada. '
            . 'Eliminados: ' . (int) ($eliminados['legacy_tasks'] ?? 0) . ' tarea(s) Legacy, '
            . (int) ($eliminados['legacy_task_user_assignments'] ?? 0) . ' asignacion(es), '
            . (int) ($eliminados['legacy_dictums'] ?? 0) . ' dictamen(es) y '
            . (int) ($eliminados['adj_operacion'] ?? 0) . ' operacion(es) local(es).',
            'agente_ejecutado'
        ) + ['ejecucion' => ['accion' => $accion, 'id_credito' => $idCredito, 'deleted' => $eliminados]];
    }

    private function resumenDiagnosticoDictamen(array $diagnostico): string
    {
        $idCredito = (int) ($diagnostico['id_credito'] ?? 0);
        $segundometro = !empty($diagnostico['segundometro']);
        $trackingLibre = empty($diagnostico['operacion']);
        $legacyError = trim((string) ($diagnostico['legacy']['error'] ?? ''));
        $legacyTask = !empty($diagnostico['legacy']['task']);
        $legacyDictamen = !empty($diagnostico['legacy']['dictamen']);
        $s2Valido = !empty($diagnostico['s2']['success']);
        $bloqueos = array_values(array_filter(array_map('strval', $diagnostico['bloqueos'] ?? [])));

        $lineas = [
            'Diagnostico del credito ' . $idCredito . ':',
            '- S2: ' . ($s2Valido ? 'validado' : 'sin validacion utilizable'),
            '- Segundometro: ' . ($segundometro ? 'credito localizado' : 'credito no localizado'),
            '- Tracking Sparta: ' . ($trackingLibre ? 'libre' : 'con operacion existente'),
            '- Legacy: ' . ($legacyError !== '' ? 'error de consulta: ' . $legacyError : ($legacyTask ? 'tarea localizada' : 'sin tarea activa')),
            '- Dictamen 13: ' . ($legacyDictamen ? 'ocupado' : 'libre'),
        ];
        if ($bloqueos === []) {
            $lineas[] = '- Bloqueos: ninguno; el credito puede continuar por el flujo normal.';
        } else {
            $lineas[] = '- Bloqueos detectados:';
            foreach ($bloqueos as $bloqueo) {
                $lineas[] = '  * ' . $bloqueo;
            }
        }
        return implode("\n", $lineas);
    }

    private function mensajeAutorizacionDictamen(array $autorizacion): string
    {
        if (empty($autorizacion['permiso_modulo'])) {
            return 'No puedo continuar: tu usuario no tiene el permiso especial para desbloquear componentes de dictamen.';
        }
        if (empty($autorizacion['nip_configurado'])) {
            return 'No puedo continuar: tu usuario tiene el permiso, pero no cuenta con un NIP activo de desbloqueo configurado.';
        }
        return 'No puedo continuar porque la autorizacion de desbloqueo de tu usuario no esta activa.';
    }

    public function ejecutar(string $accion, array $payload, array $contexto): array
    {
        if ($accion === 'excel_aplicar') {
            return (new LeonidasSpreadsheetService())->ejecutar($payload, $contexto);
        }
        if ($accion === 'convenio_crear') {
            return $this->ejecutarConvenio($payload, $contexto);
        }
        if ($accion === 'moto_asignar') {
            return $this->ejecutarMoto($payload, $contexto);
        }
        if ($accion === 'cartera_reactivar_tarea_movil') {
            return $this->ejecutarReactivacionTareaMovil($payload, $contexto);
        }
        if ($accion === LeonidasLocalAgentService::ACTION) {
            return $this->agentesLocales->ejecutar($accion, $payload, $contexto);
        }
        if (LeonidasMotosAdjudicadasService::puedeEjecutar($accion)) {
            return $this->motosAdjudicadas->ejecutar($accion, $payload, $contexto);
        }
        if (LeonidasCapitalHumanoService::puedeEjecutar($accion)) {
            return $this->capitalHumano->ejecutar($accion, $payload, $contexto);
        }

        throw new \RuntimeException('Leónidas no reconoce el ejecutor solicitado.');
    }

    public function limpiarTarea(): void
    {
        unset($_SESSION[self::TASK_KEY]);
        $this->capitalHumano->limpiarTarea();
    }

    public function entradaSeguraPendiente(int $actorId): ?string
    {
        $tarea = is_array($_SESSION[self::TASK_KEY] ?? null) ? $_SESSION[self::TASK_KEY] : null;
        if ($tarea === null) {
            return null;
        }
        if ((int) ($tarea['expira_en'] ?? 0) < time()) {
            $this->limpiarTarea();
            return null;
        }
        if ((int) ($tarea['actor_id'] ?? 0) !== $actorId) {
            return null;
        }
        if (
            (string) ($tarea['tipo'] ?? '') === 'dictamen_moto'
            && (string) ($tarea['paso'] ?? '') === 'nip'
        ) {
            return 'nip';
        }

        return null;
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
            if (!$this->creditoPermiteAsignacionMoto($resultado)) {
                $estatus = $this->estatusCredito($resultado);
                $this->limpiarTarea();
                return $this->respuesta(
                    'El crédito ' . $idCredito . ' no puede asignarse en Motos Adjudicadas. '
                    . 'Su estatus actual es ' . ($estatus !== '' ? $estatus : 'no identificado')
                    . ' e indica que ya está pagado o cerrado. No hice cambios.',
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
                'asignacion_actual' => is_array($resultado['asignacion'] ?? null) ? $resultado['asignacion'] : [],
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
        return $this->propuestaMoto(
            (int) $datos['id_credito'],
            (string) $datos['cliente'],
            $responsable,
            is_array($datos['asignacion_actual'] ?? null) ? $datos['asignacion_actual'] : []
        );
    }

    /**
     * Interpreta una solicitud completa como:
     * credito + numero de empleado + nombre + external id.
     * Regresa null cuando faltan esos datos para conservar el flujo guiado.
     */
    private function prepararMotoDesdeMensajeCompleto(string $mensaje, array $contexto): ?array
    {
        $limpio = str_replace(['**', '__', '`'], '', $mensaje);
        $mensajeNormalizado = $this->normalizar($limpio);
        if (!preg_match('/\b(?:credito(?:\s+(?:numero|no\.?|id))?|id)\s*[:#>\-]?\s*(\d+)\b/i', $mensajeNormalizado, $creditoMatch)) {
            return null;
        }
        $idCredito = (int) $creditoMatch[1];

        $numeroEmpleado = '';
        if (preg_match('/\b(?:no\.?|numero)\s*(?:de\s*)?empleado\s*:?\s*([A-Z0-9_-]+)/i', $mensajeNormalizado, $empleadoMatch)) {
            $numeroEmpleado = trim((string) $empleadoMatch[1]);
        }
        $externalId = '';
        if (preg_match('/\bexternal\s*id\s*:?\s*([A-Z0-9_-]+)/i', $mensajeNormalizado, $externalMatch)) {
            $externalId = trim((string) $externalMatch[1]);
        }
        if ($numeroEmpleado !== '' && $externalId !== '' && $numeroEmpleado !== $externalId) {
            return $this->respuesta(
                'Los identificadores no coinciden: el No. empleado es ' . $numeroEmpleado
                . ' y el External id es ' . $externalId . '. No preparé ninguna asignación.',
                'agente_error'
            );
        }

        $nombreIndicado = '';
        if (preg_match(
            '/\b(?:no\.?|n[uú]mero)\s*(?:de\s*)?empleado\s*:?\s*(?:\R+\s*)?[A-Z0-9_-]+\s*\R+\s*([^\r\n]+)/iu',
            $limpio,
            $nombreMatch
        )) {
            $candidatoNombre = trim((string) $nombreMatch[1]);
            if (!preg_match('/^external\s*id\b/iu', $candidatoNombre)) {
                $nombreIndicado = $candidatoNombre;
            }
        }

        if ($nombreIndicado === '' && preg_match(
            '/\s+al\s+(?:gestor|usuario|responsable)\s*[:>\-]*\s*([^\r\n]+?)\s*[.!?]*$/iu',
            trim($limpio),
            $nombreMatch
        )) {
            $nombreIndicado = trim((string) $nombreMatch[1], " \t\n\r\0\x0B.!?");
        }

        if ($nombreIndicado === '' && preg_match(
            '/\bcredito(?:\s+(?:numero|no\.?|id))?\s*[:#>\-]?\s*\d+\b.*?\s+a\s+([^\r\n]+?)\s*[.!?]*$/iu',
            trim($mensajeNormalizado),
            $nombreMatch
        )) {
            $nombreIndicado = trim((string) $nombreMatch[1], " \t\n\r\0\x0B.!?");
        }

        if ($numeroEmpleado === '' && $externalId === '' && $nombreIndicado === '') {
            return null;
        }

        $identificador = $numeroEmpleado !== '' ? $numeroEmpleado : $externalId;
        $responsablesDisponibles = $this->responsablesNormalizados();
        $responsables = $identificador !== ''
            ? array_values(array_filter(
                $responsablesDisponibles,
                static fn(array $responsable): bool =>
                    (string) ($responsable['numero_empleado'] ?? '') === $identificador
                    || (string) ($responsable['external_id'] ?? '') === $identificador
            ))
            : array_values(array_filter(
                $responsablesDisponibles,
                fn(array $responsable): bool =>
                    $this->normalizar((string) ($responsable['nombre'] ?? '')) === $this->normalizar($nombreIndicado)
            ));
        if (count($responsables) !== 1) {
            if ($identificador !== '') {
                $detalle = count($responsables) > 1
                    ? 'El identificador corresponde a más de un responsable activo.'
                    : 'No existe un responsable activo de Motos Adjudicadas con ese No. empleado / External id.';
            } else {
                $detalle = count($responsables) > 1
                    ? 'Encontré más de un responsable activo con el nombre ' . $nombreIndicado . '.'
                    : 'No encontré un responsable activo de Motos Adjudicadas con el nombre ' . $nombreIndicado . '.';
            }
            return $this->respuesta($detalle . ' No preparé ninguna asignación.', 'agente_error');
        }
        $responsable = $responsables[0];
        if ($nombreIndicado !== '' && $this->normalizar($nombreIndicado) !== $this->normalizar((string) $responsable['nombre'])) {
            return $this->respuesta(
                'Los identificadores ' . $identificador . ' pertenecen a ' . $responsable['nombre']
                . ', no a ' . $nombreIndicado . '. Revisa los datos; no preparé ninguna asignación.',
                'agente_error'
            );
        }

        $resultado = $this->llamar('moto_buscar', $idCredito);
        if (empty($resultado['success'])) {
            return $this->respuesta('No pude consultar el crédito ' . $idCredito . ': ' . $this->mensajeResultado($resultado), 'agente_error');
        }
        if (!$this->creditoPermiteAsignacionMoto($resultado)) {
            $estatus = $this->estatusCredito($resultado);
            return $this->respuesta(
                'El crédito ' . $idCredito . ' no puede asignarse: su estatus es '
                . ($estatus !== '' ? $estatus : 'no identificado')
                . ' e indica que ya está pagado o cerrado. No hice cambios.',
                'agente_error'
            );
        }

        $credito = is_array($resultado['credito'] ?? null) ? $resultado['credito'] : [];
        $cliente = trim((string) ($credito['nombre_cliente'] ?? 'Sin nombre'));
        return $this->propuestaMoto(
            $idCredito,
            $cliente,
            $responsable,
            is_array($resultado['asignacion'] ?? null) ? $resultado['asignacion'] : []
        );
    }

    private function propuestaMoto(int $idCredito, string $cliente, array $responsable, array $asignacionActual = []): array
    {
        $numeroEmpleado = trim((string) ($responsable['numero_empleado'] ?? ''));
        $externalId = trim((string) ($responsable['external_id'] ?? $numeroEmpleado));
        $idPersonaActual = (int) ($asignacionActual['id_persona'] ?? 0);
        $nombreActual = trim((string) ($asignacionActual['nombre_despacho'] ?? ''));
        $esMismoResponsable = $idPersonaActual > 0 && $idPersonaActual === (int) $responsable['id_persona'];
        $tipoCambio = !$asignacionActual
            ? 'nueva asignación'
            : ($esMismoResponsable ? 'verificación y sincronización' : 'reasignación');
        $detalleActual = !$asignacionActual
            ? "\nAsignación actual: ninguna."
            : "\nAsignación actual: " . ($nombreActual !== '' ? $nombreActual : 'responsable no identificado') . '.';
        $confirmacion = !$asignacionActual
            ? 'Al confirmar registraré la asignación y verificaré Sparta y Legacy.'
            : ($esMismoResponsable
                ? 'Al confirmar revalidaré y sincronizaré la asignación existente con Legacy.'
                : '¿Confirmas que deseas cambiar la asignación al nuevo responsable?');

        return [
            'mensaje' => 'Vista previa de ' . $tipoCambio . ':'
                . "\nCrédito: " . $idCredito
                . "\nCliente: " . $cliente
                . $detalleActual
                . "\nNuevo responsable: " . (string) $responsable['nombre']
                . "\nNo. empleado: " . ($numeroEmpleado !== '' ? $numeroEmpleado : 'sin dato')
                . "\nExternal id esperado en Legacy: " . ($externalId !== '' ? $externalId : 'sin dato')
                . "\n" . $confirmacion,
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => 'moto_asignar',
                'resumen' => 'asignar el crédito ' . $idCredito . ' a ' . (string) $responsable['nombre'],
                'payload' => [
                    'id_credito' => $idCredito,
                    'id_persona' => (int) $responsable['id_persona'],
                    'responsable' => (string) $responsable['nombre'],
                    'cliente' => $cliente,
                    'numero_empleado' => $numeroEmpleado,
                    'external_id' => $externalId,
                    'reasignar' => !empty($asignacionActual),
                    'asignacion_anterior_id' => (int) ($asignacionActual['id'] ?? 0),
                    'asignacion_anterior_persona' => $idPersonaActual,
                    'asignacion_anterior_nombre' => $nombreActual,
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
        $asignacionActual = is_array($credito['asignacion'] ?? null) ? $credito['asignacion'] : [];
        $idPersonaActual = (int) ($asignacionActual['id_persona'] ?? 0);
        $idAsignacionActual = (int) ($asignacionActual['id'] ?? 0);
        $esperabaReasignacion = !empty($payload['reasignar']);
        $idPersonaAnterior = (int) ($payload['asignacion_anterior_persona'] ?? 0);
        $idAsignacionAnterior = (int) ($payload['asignacion_anterior_id'] ?? 0);
        if ($esperabaReasignacion) {
            $objetivoYaAplicado = $idPersonaActual === $idPersona && $idPersonaActual > 0;
            $coincideVistaPrevia = $idPersonaActual === $idPersonaAnterior
                && ($idAsignacionAnterior <= 0 || $idAsignacionActual === $idAsignacionAnterior);
            if (!$objetivoYaAplicado && !$coincideVistaPrevia) {
                $nombreActual = trim((string) ($asignacionActual['nombre_despacho'] ?? 'ningún responsable'));
                throw new \RuntimeException(
                    'La asignación cambió después de la vista previa. Ahora figura ' . $nombreActual
                    . '. Vuelve a solicitar la reasignación para confirmar con datos actuales.'
                );
            }
        } elseif ($idPersonaActual > 0 && $idPersonaActual !== $idPersona) {
            $nombreActual = trim((string) ($asignacionActual['nombre_despacho'] ?? 'otro responsable'));
            throw new \RuntimeException(
                'El crédito fue asignado a ' . $nombreActual
                . ' mientras esperábamos tu confirmación. Vuelve a solicitarlo para ver la reasignación antes de cambiarla.'
            );
        }
        if (!$this->creditoPermiteAsignacionMoto($credito)) {
            $estatus = $this->estatusCredito($credito);
            throw new \RuntimeException(
                'El crédito cambió de estatus mientras esperábamos tu confirmación. '
                . 'Ahora está como ' . ($estatus !== '' ? $estatus : 'no identificado')
                . ' y ya no permite una asignación; no se realizó el cambio.'
            );
        }
        if (!$this->llamar('moto_responsable_activo', $idPersona)) {
            throw new \RuntimeException('El responsable seleccionado ya no está activo para adjudicación.');
        }

        $responsables = array_values(array_filter(
            $this->responsablesNormalizados(),
            static fn(array $responsable): bool => (int) ($responsable['id_persona'] ?? 0) === $idPersona
        ));
        if (count($responsables) !== 1) {
            throw new \RuntimeException('No pude revalidar de forma única al responsable. No se realizó la asignación.');
        }
        $responsableActual = $responsables[0];
        $numeroEmpleadoActual = trim((string) ($responsableActual['numero_empleado'] ?? ''));
        $externalIdActual = trim((string) ($responsableActual['external_id'] ?? $numeroEmpleadoActual));
        $numeroEmpleadoPropuesto = trim((string) ($payload['numero_empleado'] ?? ''));
        $externalIdPropuesto = trim((string) ($payload['external_id'] ?? ''));
        if (($numeroEmpleadoPropuesto !== '' && $numeroEmpleadoPropuesto !== $numeroEmpleadoActual)
            || ($externalIdPropuesto !== '' && $externalIdPropuesto !== $externalIdActual)) {
            throw new \RuntimeException(
                'Los identificadores del responsable cambiaron desde la vista previa. '
                . 'Actualiza la solicitud antes de confirmar; no se realizó la asignación.'
            );
        }

        $resultado = $this->llamar('moto_asignar', $idPersona, $idCredito, (int) $contexto['actor_id']);
        if (empty($resultado['success']) && empty($resultado['partial'])) {
            throw new \RuntimeException($this->mensajeResultado($resultado));
        }

        $responsable = trim((string) ($responsableActual['nombre'] ?? $payload['responsable'] ?? 'el responsable seleccionado'));
        $cliente = trim((string) ($payload['cliente'] ?? ''));
        if ($cliente === '') {
            $datosCredito = is_array($credito['credito'] ?? null) ? $credito['credito'] : [];
            $cliente = trim((string) ($datosCredito['nombre_cliente'] ?? 'cliente no identificado'));
        }

        if (!empty($resultado['partial'])) {
            return $this->respuesta(
                'La asignación del crédito ' . $idCredito . ' a ' . $responsable
                . ' quedó activa en Sparta, pero no pude completar o verificar Motos Adjudicadas Legacy. '
                . 'Detalle: ' . $this->mensajeResultado($resultado),
                'agente_ejecucion_parcial'
            ) + ['ejecucion' => [
                'accion' => 'moto_asignar',
                'id_credito' => $idCredito,
                'id_persona' => $idPersona,
                'sparta_verificado' => true,
                'legacy_verificado' => false,
            ]];
        }

        $legacy = is_array($resultado['legacy'] ?? null) ? $resultado['legacy'] : [];
        $verificacion = is_array($legacy['verificacion'] ?? null) ? $legacy['verificacion'] : [];
        $taskId = (int) ($verificacion['task_id'] ?? $legacy['task_id'] ?? 0);
        $legacyVerificado = !empty($verificacion['success'])
            && !empty($verificacion['responsable_correcto'])
            && !empty($verificacion['asignacion_activa'])
            && !empty($verificacion['asignacion_exclusiva'])
            && !empty($verificacion['cliente_correcto'])
            && $taskId > 0;
        if (!$legacyVerificado) {
            return $this->respuesta(
                'La asignación del crédito ' . $idCredito . ' a ' . $responsable
                . ' quedó activa en Sparta, pero la verificación final de Legacy quedó incompleta. '
                . 'No afirmaré que la tarea o el responsable quedaron vinculados hasta poder comprobarlo.',
                'agente_ejecucion_parcial'
            ) + ['ejecucion' => [
                'accion' => 'moto_asignar',
                'id_credito' => $idCredito,
                'id_persona' => $idPersona,
                'sparta_verificado' => true,
                'legacy_verificado' => false,
            ]];
        }

        $clienteLegacy = trim((string) ($verificacion['client_name'] ?? ''));
        if ($clienteLegacy !== '') {
            $cliente = $clienteLegacy;
        }
        $responsableLegacy = trim((string) ($verificacion['responsable'] ?? ''));
        $externalId = trim((string) ($verificacion['external_id'] ?? $externalIdActual));
        $legacyUserId = (int) ($verificacion['legacy_user_id'] ?? 0);
        $campania = trim((string) ($verificacion['campaign_name'] ?? ''));
        return $this->respuesta(
            'Revisé el crédito ' . $idCredito . ': ya quedó asignado correctamente a **' . $responsable . '**.'
            . "\nCliente: " . $cliente
            . "\nEmpleado: " . ($numeroEmpleadoActual !== '' ? $numeroEmpleadoActual : ($externalId !== '' ? $externalId : 'sin dato'))
            . "\nUsuario Legacy: " . ($legacyUserId > 0 ? $legacyUserId : 'no identificado')
            . "\nCampaña vigente: " . ($campania !== '' ? $campania : 'no identificada')
            . "\nTarea vigente: " . $taskId
            . "\nLa asignación activa también existe en task_user_assignments."
            . ($responsableLegacy !== '' && $responsableLegacy !== $responsable
                ? "\nResponsable registrado en Legacy: " . $responsableLegacy . '.'
                : ''),
            'agente_ejecutado'
        ) + ['ejecucion' => [
            'accion' => 'moto_asignar',
            'id_credito' => $idCredito,
            'id_persona' => $idPersona,
            'task_id' => $taskId,
            'sparta_verificado' => true,
            'legacy_verificado' => true,
        ]];
    }

    private function resolverDiagnosticoTareaMovil(string $mensaje, array $contexto): array
    {
        $idCredito = $this->extraerEntero($mensaje);
        if ($idCredito <= 0) {
            return $this->respuesta(
                'Indica el ID del credito para revisar su tarea de dictaminacion en MaxikashApp.',
                'agente_pregunta'
            );
        }

        $tarea = $this->buscarTareaMovil($idCredito);
        if ($tarea === null) {
            return $this->respuesta(
                'No encontre una tarea Legacy para el credito ' . $idCredito
                . '. No puedo reactivarla ni afirmar que esta asignado sin una tarea existente.',
                'agente_diagnostico'
            );
        }

        $gestor = trim((string) ($tarea['gestor_nombre'] ?? ''));
        $gestor = $gestor !== '' ? $gestor : 'sin gestor vigente';
        $asignacionActiva = (int) ($tarea['asignacion_activa'] ?? 0) === 1;
        $usuarioActivo = empty($tarea['gestor_deleted_at']);

        if ((int) ($tarea['current_user_id'] ?? 0) <= 0 || !$asignacionActiva || !$usuarioActivo) {
            return $this->respuesta(
                'La tarea ' . (int) $tarea['task_id'] . ' del credito ' . $idCredito
                . ' no esta lista para mostrarse en MaxikashApp. '
                . 'Gestor: ' . $gestor . '. '
                . 'Asignacion activa: ' . ($asignacionActiva ? 'si' : 'no') . '. '
                . 'Usuario Legacy activo: ' . ($usuarioActivo ? 'si' : 'no') . '. '
                . 'No se realizo ningun cambio.',
                'agente_diagnostico'
            );
        }

        $tareaEliminada = !empty($tarea['task_deleted_at']);
        $campanaEliminada = !empty($tarea['campaign_deleted_at']);
        if (!$tareaEliminada && !$campanaEliminada) {
            return $this->respuesta(
                'La tarea ' . (int) $tarea['task_id'] . ' del credito ' . $idCredito
                . ' ya esta activa para ' . $gestor . ' en la campana '
                . (string) ($tarea['campaign_name'] ?? 'vigente') . '. '
                . 'Si aun no aparece en MaxikashApp, actualiza la lista o vuelve a iniciar sesion; '
                . 'el siguiente paso es validar la respuesta de la API movil.',
                'agente_diagnostico'
            );
        }

        $causas = [];
        if ($campanaEliminada) {
            $causas[] = 'la campana ' . (int) $tarea['campaign_id'] . ' fue eliminada logicamente el '
                . (string) $tarea['campaign_deleted_at'];
        }
        if ($tareaEliminada) {
            $causas[] = 'la tarea ' . (int) $tarea['task_id'] . ' fue eliminada logicamente el '
                . (string) $tarea['task_deleted_at'];
        }
        $detalleCausa = implode(' y ', $causas);

        if (empty($contexto['permisos_agente']['asignaciones_movil'])) {
            return $this->respuesta(
                'Encontre la causa: ' . $detalleCausa
                . ', por eso MaxikashApp no la muestra aunque siga asignada a ' . $gestor . '. '
                . 'Tu perfil no tiene el permiso Asignacion de Creditos para reactivarla.',
                'agente_diagnostico'
            );
        }

        return [
            'mensaje' => 'Encontre la causa: ' . $detalleCausa
                . '. Por eso no aparece para dictaminar en MaxikashApp, aunque conserva la asignacion a '
                . $gestor . '. Preparare la reactivacion; al confirmar volvere a validar tarea, gestor y asignacion activa.',
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => 'cartera_reactivar_tarea_movil',
                'resumen' => 'reactivar la tarea ' . (int) $tarea['task_id'] . ' del credito ' . $idCredito
                    . ' para ' . $gestor . ' en MaxikashApp',
                'payload' => [
                    'task_id' => (int) $tarea['task_id'],
                    'id_credito' => $idCredito,
                    'legacy_user_id' => (int) $tarea['current_user_id'],
                    'gestor' => $gestor,
                    'deleted_at_esperado' => (string) $tarea['task_deleted_at'],
                    'campaign_id' => (int) $tarea['campaign_id'],
                    'campaign_deleted_at_esperado' => (string) $tarea['campaign_deleted_at'],
                ],
            ],
        ];
    }

    private function ejecutarReactivacionTareaMovil(array $payload, array $contexto): array
    {
        if (empty($contexto['permisos_agente']['asignaciones_movil'])) {
            throw new \RuntimeException('Tu perfil ya no tiene permiso de Asignacion de Creditos para reactivar tareas moviles.');
        }

        $idCredito = (int) ($payload['id_credito'] ?? 0);
        $taskId = (int) ($payload['task_id'] ?? 0);
        $legacyUserId = (int) ($payload['legacy_user_id'] ?? 0);
        $campaignId = (int) ($payload['campaign_id'] ?? 0);
        if ($idCredito <= 0 || $taskId <= 0 || $legacyUserId <= 0 || $campaignId <= 0) {
            throw new \RuntimeException('La propuesta de reactivacion esta incompleta. Vuelve a revisar el credito antes de confirmar.');
        }

        $tarea = $this->buscarTareaMovil($idCredito);
        if ($tarea === null || (int) $tarea['task_id'] !== $taskId || (int) $tarea['campaign_id'] !== $campaignId) {
            throw new \RuntimeException('La tarea del credito cambio desde la vista previa. No se realizo ningun cambio.');
        }
        if ((int) ($tarea['current_user_id'] ?? 0) !== $legacyUserId
            || (int) ($tarea['asignacion_activa'] ?? 0) !== 1
            || !empty($tarea['gestor_deleted_at'])) {
            throw new \RuntimeException('La asignacion o el gestor cambiaron desde la vista previa. No se realizo ningun cambio.');
        }

        $seReactivo = !empty($tarea['task_deleted_at']);
        $campanaReactivada = !empty($tarea['campaign_deleted_at']);
        $db = new \Core\DatabaseLegacy();
        if ($campanaReactivada) {
            $actualizadas = $db->CRUD(
                'UPDATE campaigns
                 SET deleted_at = NULL, updated_at = NOW()
                 WHERE id = :campaign_id
                   AND deleted_at IS NOT NULL',
                ['campaign_id' => $campaignId]
            );
            if ($actualizadas !== 1) {
                throw new \RuntimeException('La campana no pudo reactivarse porque cambio durante la confirmacion. No se realizo ningun cambio adicional.');
            }
        }
        if ($seReactivo) {
            $actualizadas = $db->CRUD(
                'UPDATE tasks
                 SET deleted_at = NULL, updated_at = NOW()
                 WHERE id = :task_id
                   AND credit_number = :credito
                   AND current_user_id = :usuario
                   AND deleted_at IS NOT NULL',
                ['task_id' => $taskId, 'credito' => (string) $idCredito, 'usuario' => $legacyUserId]
            );
            if ($actualizadas !== 1) {
                throw new \RuntimeException('La tarea no pudo reactivarse porque cambio durante la confirmacion. No se realizo ningun cambio adicional.');
            }
        }

        $verificada = $this->buscarTareaMovil($idCredito);
        if ($verificada === null
            || (int) $verificada['task_id'] !== $taskId
            || !empty($verificada['task_deleted_at'])
            || !empty($verificada['campaign_deleted_at'])
            || (int) ($verificada['asignacion_activa'] ?? 0) !== 1) {
            throw new \RuntimeException('La reactivacion no pudo verificarse completamente. Revisa la tarea Legacy antes de informar que esta disponible.');
        }

        $gestor = trim((string) ($verificada['gestor_nombre'] ?? $payload['gestor'] ?? 'el gestor'));
        return $this->respuesta(
            ($seReactivo || $campanaReactivada)
                ? 'Listo. Reactive ' . ($campanaReactivada ? 'la campana y ' : '') . ($seReactivo ? 'la tarea ' . $taskId : 'la campana')
                    . ' del credito ' . $idCredito . ' para ' . $gestor
                    . '. La tarea, el gestor y la asignacion activa fueron verificados. En MaxikashApp debe aparecer tras actualizar la lista o volver a iniciar sesion.'
                : 'La tarea ' . $taskId . ' del credito ' . $idCredito . ' ya estaba activa para ' . $gestor
                    . '. Verifique tarea, gestor y asignacion activa sin modificar datos. Si aun no aparece en MaxikashApp, hay que revisar la respuesta de la API movil.',
            'agente_ejecutado'
        ) + ['ejecucion' => [
            'accion' => 'cartera_reactivar_tarea_movil',
            'id_credito' => $idCredito,
            'task_id' => $taskId,
            'campaign_id' => $campaignId,
            'legacy_user_id' => $legacyUserId,
            'legacy_verificado' => true,
        ]];
    }

    private function buscarTareaMovil(int $idCredito): ?array
    {
        $db = new \Core\DatabaseLegacy();
        return $db->queryOne(
            'SELECT t.id AS task_id, t.credit_number, t.current_user_id, t.status,
                    t.deleted_at AS task_deleted_at, t.updated_at AS task_updated_at,
                    c.id AS campaign_id, c.name AS campaign_name, c.deleted_at AS campaign_deleted_at,
                    c.start_date, c.end_date,
                    u.name AS gestor_nombre, u.deleted_at AS gestor_deleted_at,
                    EXISTS(
                        SELECT 1 FROM task_user_assignments a
                        WHERE a.task_id = t.id
                          AND a.user_id = t.current_user_id
                          AND a.unassigned_at IS NULL
                    ) AS asignacion_activa
             FROM tasks t
             INNER JOIN campaigns c ON c.id = t.campaign_id
             LEFT JOIN users u ON u.id = t.current_user_id
             WHERE CAST(t.credit_number AS CHAR) = :credito
             ORDER BY
                CASE WHEN c.start_date <= CURDATE() AND c.end_date >= CURDATE() THEN 0 ELSE 1 END,
                c.end_date DESC, t.id DESC
             LIMIT 1',
            ['credito' => (string) $idCredito]
        );
    }

    private function responsablesNormalizados(): array
    {
        $salida = [];
        foreach ((array) $this->llamar('moto_responsables') as $responsable) {
            $id = (int) ($responsable['id_persona'] ?? 0);
            $nombre = trim((string) ($responsable['nombre_completo'] ?? ''));
            if ($id > 0 && $nombre !== '') {
                $numeroEmpleado = trim((string) ($responsable['numero_empleado'] ?? ''));
                $salida[] = [
                    'id_persona' => $id,
                    'nombre' => $nombre,
                    'puesto' => trim((string) ($responsable['puesto'] ?? '')),
                    'numero_empleado' => $numeroEmpleado,
                    'external_id' => $numeroEmpleado,
                    'codigo_contpac' => trim((string) ($responsable['codigo_contpac'] ?? '')),
                ];
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

    private function creditoPermiteAsignacionMoto(array $resultado): bool
    {
        $estatus = $this->normalizar($this->estatusCredito($resultado));
        foreach (['liquidado', 'liquidada', 'saldado', 'saldada', 'cerrado', 'cerrada'] as $terminal) {
            if ($estatus !== '' && str_contains($estatus, $terminal)) {
                return false;
            }
        }
        return true;
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
        $tieneAccion = preg_match('/\b(asignar|asigna|adjudicar|adjudica|quiero|necesito)\b/u', $mensaje) === 1;
        $tieneContexto = preg_match('/\b(moto|motos|adjudicacion|adjudicar)\b/u', $mensaje) === 1;
        $tieneCredito = preg_match('/\b(?:credito|id)\b\s*[:#>\-]?\s*\d{4,}\b/u', $mensaje) === 1;
        $tieneGestor = preg_match('/\bgestor\b/u', $mensaje) === 1;

        return $tieneAccion && ($tieneContexto || ($tieneCredito && $tieneGestor));
    }

    private function solicitaDictamenMoto(string $mensaje): bool
    {
        return preg_match('/\b\d{4,}\b/', $mensaje) === 1
            && preg_match('/\b(dictamen|dictaminar|desbloquear|desbloquea|desbloquee|desbloqueo|bloqueo|validacion s2|componentes)\b/u', $mensaje) === 1;
    }

    private function accionDictamenMoto(string $mensaje): string
    {
        $destructiva = preg_match('/\b(componente|componentes|limpiar|limpieza|borrar|eliminar)\b/u', $mensaje) === 1
            && preg_match('/\b(desbloquear|desbloquea|desbloquee|desbloqueo|limpiar|limpia|limpieza|borrar|borra|eliminar|elimina)\b/u', $mensaje) === 1;
        if ($destructiva) {
            return 'desbloquear_componentes';
        }

        if (preg_match('/\bs2\b/u', $mensaje) === 1
            && preg_match('/\b(desbloquear|desbloquea|desbloquee|desbloqueo|liberar|libera|autorizar|autoriza|continuar|continua)\b/u', $mensaje) === 1) {
            return 'desbloquear_s2';
        }

        return 'diagnosticar';
    }

    private function extraerCredito(string $mensaje): int
    {
        return preg_match('/\b(\d{4,})\b/', $mensaje, $m) ? (int) $m[1] : 0;
    }

    private function solicitaDiagnosticoTareaMovil(string $mensaje): bool
    {
        $incluyeCredito = preg_match('/\b\d{4,}\b/', $mensaje) === 1;
        $incluyeContextoMovil = preg_match(
            '/\b(maxikashapp|app movil|movil|dictaminar|no aparece|no aparec|no sale|no lo ven|no lo ve)\b/u',
            $mensaje
        ) === 1;
        $incluyeAccion = preg_match('/\b(revisar|revisa|validar|valida|verificar|verifica|reactivar|reactiva|asignad|tarea)\b/u', $mensaje) === 1;

        return $incluyeCredito && $incluyeContextoMovil && $incluyeAccion;
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
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return preg_replace('/\s+/', ' ', $ascii !== false ? $ascii : $texto) ?? $texto;
    }
}
