<?php

namespace Services;

require_once __DIR__ . '/../core/DatabaseCliSupport.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/DatabaseLegacy.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/MotosAdjudicadas.php';

/**
 * Diagnostico cruzado de Motos Adjudicadas entre Sparta y Legacy.
 *
 * Las consultas del modelo de lenguaje nunca son la fuente de verdad. Este
 * servicio vuelve a leer ambas bases antes de proponer y antes de ejecutar.
 */
class LeonidasMotosAdjudicadasService
{
    public const ACTION_ENVIAR_EVIDENCIAS = 'moto_enviar_evidencias';
    public const ACTION_FORZAR_EVIDENCIAS = 'moto_forzar_evidencias';

    private \Core\Database $db;
    private ?\Core\DatabaseLegacy $legacy = null;
    private ?string $legacyError = null;

    public function __construct(?\Core\Database $db = null, ?\Core\DatabaseLegacy $legacy = null)
    {
        $this->db = $db ?? new \Core\Database();
        if ($legacy !== null) {
            $this->legacy = $legacy;
            return;
        }

        try {
            $this->legacy = new \Core\DatabaseLegacy();
        } catch (\Throwable $e) {
            $this->legacyError = $e->getMessage();
            error_log('[LeonidasMotosAdjudicadasService] Legacy no disponible: ' . $e->getMessage());
        }
    }

    public static function accionesEjecutables(): array
    {
        return [self::ACTION_ENVIAR_EVIDENCIAS, self::ACTION_FORZAR_EVIDENCIAS];
    }

    public static function puedeEjecutar(string $accion): bool
    {
        return in_array($accion, self::accionesEjecutables(), true);
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $consultaSemana = $this->extraerSemanaMotos($normalizado);
        $consultaAsignacion = preg_match(
            '/\b(quien|a quien|asignado|asignada|responsable)\b.*\b(credito|id)\b|\b(credito|id)\b.*\b(asignado|asignada|responsable)\b/u',
            $normalizado
        ) === 1;
        $moverEvidencias = str_contains($normalizado, 'evidencia')
            && preg_match('/\b(mueve|mover|envia|enviar|pasa|pasar|manda|mandar|forzar|fuerza)\b/u', $normalizado) === 1;

        if ($consultaSemana === null && !$consultaAsignacion && !$moverEvidencias) {
            return null;
        }
        if (empty($contexto['permisos_agente']['motos'])) {
            return $this->respuesta(
                'No puedo consultar ni modificar Motos Adjudicadas porque tu perfil no tiene acceso a ese modulo.',
                'agente_denegado'
            );
        }

        if ($consultaSemana !== null) {
            return $this->resolverCreditosSemana($consultaSemana['semana'], $consultaSemana['anio']);
        }

        $idCredito = $this->extraerCredito($mensaje);
        if ($idCredito <= 0) {
            return $this->respuesta('Necesito el ID numerico del credito para consultar Sparta y Legacy.', 'agente_pregunta');
        }

        $diagnostico = $this->diagnosticar($idCredito);
        if (empty($diagnostico['operacion']) && empty($diagnostico['legacy']['tasks'])) {
            return $this->respuesta(
                'No encontre el credito ' . $idCredito . ' en Motos Adjudicadas de Sparta ni en las tareas de Legacy. No realice cambios.',
                'agente_diagnostico'
            );
        }

        $mensajeDiagnostico = $this->formatearDiagnostico($diagnostico);
        if ($consultaAsignacion && !$moverEvidencias) {
            return $this->respuesta($mensajeDiagnostico . "\n\nNo realice cambios.", 'agente_diagnostico');
        }
        if (empty($diagnostico['legacy']['disponible'])) {
            return $this->respuesta(
                $mensajeDiagnostico
                    . "\n\nNo puedo preparar ni forzar el movimiento mientras Legacy no este disponible. "
                    . 'Debo revisar tasks, task_user_assignments, campaigns, users y dictums antes de proponer el cambio. No realice cambios.',
                'agente_diagnostico'
            );
        }

        $operacion = is_array($diagnostico['operacion'] ?? null) ? $diagnostico['operacion'] : [];
        if (!$operacion) {
            return $this->respuesta(
                $mensajeDiagnostico . "\n\nNo existe una operacion local de Motos Adjudicadas que pueda moverse. No realice cambios.",
                'agente_diagnostico'
            );
        }

        $tieneDatosMoto = !empty($operacion['datos_moto_at']);
        $evidencias = (int) ($operacion['evidencias_total'] ?? 0);
        if ($tieneDatosMoto && $evidencias > 0) {
            return [
                'mensaje' => $mensajeDiagnostico
                    . "\n\nEl credito cumple el flujo normal: tiene datos de motocicleta y " . $evidencias
                    . ' evidencia(s). Puedo enviar sus evidencias despues de tu confirmacion.',
                'tipo' => 'agente_propuesta',
                'propuesta_especificacion' => [
                    'accion' => self::ACTION_ENVIAR_EVIDENCIAS,
                    'resumen' => 'enviar a Evidencias el credito ' . $idCredito . ' por el flujo normal',
                    'payload' => $this->payload($diagnostico),
                ],
            ];
        }

        $faltantes = [];
        if (!$tieneDatosMoto) {
            $faltantes[] = 'datos de la motocicleta y ubicacion';
        }
        if ($evidencias <= 0) {
            $faltantes[] = 'evidencias cargadas';
        }
        $mensajeBloqueo = $mensajeDiagnostico
            . "\n\nNo puede enviarse a Evidencias por el flujo normal porque faltan "
            . implode(' y ', $faltantes) . '. No realice cambios.';

        if (empty($contexto['permisos_agente']['motos_override_estatus'])) {
            return $this->respuesta(
                $mensajeBloqueo
                    . "\n\nEl movimiento solicitado requiere forzar el estatus. Tu perfil no tiene el permiso especial "
                    . 'Posicionamiento de estatus (Override), por lo que no puedo ejecutarlo ni omitir el control. '
                    . 'Un administrador debe asignarte ese permiso y despues puedes repetir: "mueve el credito '
                    . $idCredito . ' a Evidencias".',
                'agente_denegado'
            );
        }

        $mensajeBloqueo .= "\n\nQuieres que fuerce la opcion de moverlo a Evidencias? "
            . 'La operacion quedara auditada y los datos faltantes seguiran marcados como pendientes.';

        return [
            'mensaje' => $mensajeBloqueo,
            /*
                . "\n\n¿Quieres que fuerce la opcion de moverlo a Evidencias? La operacion quedara auditada y los datos faltantes seguiran marcados como pendientes.",
            */
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => self::ACTION_FORZAR_EVIDENCIAS,
                'resumen' => 'forzar el movimiento a Evidencias del credito ' . $idCredito,
                'payload' => $this->payload($diagnostico),
            ],
        ];
    }

    private function resolverCreditosSemana(int $semana, int $anio): array
    {
        if ($this->legacy === null) {
            return $this->respuesta(
                'No pude consultar las motos adjudicadas porque Legacy no esta disponible. '
                    . 'Detalle tecnico: ' . ($this->legacyError ?: 'conexion no inicializada') . '.',
                'agente_error'
            );
        }

        if ($semana < 1 || $semana > 53 || $anio < 2020 || $anio > 2100) {
            return $this->respuesta(
                'La semana o el anio no son validos. Indica una semana del 1 al 53 y el anio con cuatro digitos.',
                'agente_pregunta'
            );
        }

        $inicio = (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))
            ->setISODate($anio, $semana, 1)
            ->setTime(0, 0, 0);
        $fin = $inicio->modify('+7 days');

        try {
            $filas = $this->legacy->queryAll(
                "SELECT TRIM(t.credit_number) AS id_credito,
                        MAX(t.client_name) AS cliente,
                        MAX(t.id) AS task_id,
                        MAX(t.created_at) AS fecha_ingreso
                   FROM tasks t
                   INNER JOIN campaigns c ON c.id = t.campaign_id
                  WHERE LOWER(TRIM(c.name)) = 'motos adjudicadas'
                    AND c.deleted_at IS NULL
                    AND t.deleted_at IS NULL
                    AND t.created_at >= :inicio
                    AND t.created_at < :fin
                    AND TRIM(COALESCE(t.credit_number, '')) <> ''
                  GROUP BY TRIM(t.credit_number)
                  ORDER BY CAST(id_credito AS UNSIGNED), id_credito",
                [
                    'inicio' => $inicio->format('Y-m-d H:i:s'),
                    'fin' => $fin->format('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
            error_log('[LeonidasMotosAdjudicadasService] Error reporte semanal: ' . $e->getMessage());
            return $this->respuesta(
                'No pude generar el reporte de Motos Adjudicadas de la semana ' . $semana
                    . ' de ' . $anio . ' porque fallo la consulta de Legacy. No invente resultados.',
                'agente_error'
            );
        }

        $inicioTexto = $this->fechaCorta($inicio);
        $finTexto = $this->fechaCorta($fin->modify('-1 day'));
        if (!$filas) {
            return [
                'mensaje' => 'No encontre creditos ingresados a Motos Adjudicadas durante la semana '
                    . $semana . ' de ' . $anio . ' (' . $inicioTexto . ' al ' . $finTexto . '). '
                    . 'Consulte las tareas activas de la campania Motos Adjudicadas en Legacy.',
                'tipo' => 'agente_reporte',
                'reporte' => [
                    'modulo' => 'Motos Adjudicadas',
                    'semana' => $semana,
                    'anio' => $anio,
                    'total' => 0,
                    'filas' => [],
                ],
            ];
        }

        $ids = array_values(array_map(
            static fn(array $fila): string => trim((string) ($fila['id_credito'] ?? '')),
            $filas
        ));
        $lineas = [
            'Motos Adjudicadas de la semana ' . $semana . ' de ' . $anio
                . ' (' . $inicioTexto . ' al ' . $finTexto . ').',
            'Creditos unicos: ' . count($ids) . '.',
            'IDs: ' . implode(', ', $ids) . '.',
            '',
            'Fuente: tareas activas de Legacy pertenecientes a la campania Motos Adjudicadas, '
                . 'clasificadas por la fecha de creacion de la tarea. No mezcle campanias de asignacion masiva.',
        ];

        return [
            'mensaje' => implode("\n", $lineas),
            'tipo' => 'agente_reporte',
            'reporte' => [
                'modulo' => 'Motos Adjudicadas',
                'semana' => $semana,
                'anio' => $anio,
                'desde' => $inicio->format('Y-m-d'),
                'hasta' => $fin->modify('-1 day')->format('Y-m-d'),
                'total' => count($ids),
                'ids' => $ids,
                'filas' => $filas,
            ],
        ];
    }

    private function extraerSemanaMotos(string $normalizado): ?array
    {
        if (!str_contains($normalizado, 'moto') || !str_contains($normalizado, 'semana')) {
            return null;
        }
        if (preg_match('/\bsemana\s*(\d{1,2})(?:\s*(?:del|de)?\s*(20\d{2}))?\b/u', $normalizado, $match) !== 1) {
            return null;
        }

        return [
            'semana' => (int) $match[1],
            'anio' => isset($match[2]) && $match[2] !== '' ? (int) $match[2] : (int) date('o'),
        ];
    }

    private function fechaCorta(\DateTimeImmutable $fecha): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];
        return (int) $fecha->format('j') . ' de ' . $meses[(int) $fecha->format('n')];
    }

    public function ejecutar(string $accion, array $payload, array $contexto): array
    {
        if (!self::puedeEjecutar($accion)) {
            throw new \RuntimeException('Accion de Motos Adjudicadas no reconocida.');
        }
        if (empty($contexto['permisos_agente']['motos'])) {
            throw new \RuntimeException('Tu perfil ya no tiene acceso a Motos Adjudicadas.');
        }
        if ($accion === self::ACTION_FORZAR_EVIDENCIAS && empty($contexto['permisos_agente']['motos_override_estatus'])) {
            throw new \RuntimeException('Tu perfil ya no tiene el permiso Posicionamiento de estatus (Override).');
        }

        $idCredito = (int) ($payload['id_credito'] ?? 0);
        $idOperacionEsperado = (int) ($payload['id_operacion'] ?? 0);
        if ($idCredito <= 0 || $idOperacionEsperado <= 0) {
            throw new \RuntimeException('La confirmacion no contiene un credito y una operacion validos.');
        }

        $diagnostico = $this->diagnosticar($idCredito);
        $operacion = is_array($diagnostico['operacion'] ?? null) ? $diagnostico['operacion'] : [];
        if (!$operacion || (int) ($operacion['id'] ?? 0) !== $idOperacionEsperado) {
            throw new \RuntimeException('La operacion cambio despues de la vista previa. Vuelve a solicitar el diagnostico.');
        }

        $modelo = new \Models\MotosAdjudicadas();
        $actorId = (int) ($contexto['actor_id'] ?? 0);
        $actorNombre = trim((string) ($contexto['nombre'] ?? 'Leonidas'));

        if ($accion === self::ACTION_ENVIAR_EVIDENCIAS) {
            if (empty($operacion['datos_moto_at']) || (int) ($operacion['evidencias_total'] ?? 0) <= 0) {
                throw new \RuntimeException('El credito dejo de cumplir los requisitos del flujo normal. Vuelve a solicitar el diagnostico.');
            }
            $resultado = $modelo->enviarEvidencias($idOperacionEsperado, $actorId, $actorNombre);
            if (empty($resultado['success'])) {
                throw new \RuntimeException(trim((string) ($resultado['message'] ?? 'No se pudieron enviar las evidencias.')));
            }
            return $this->respuesta(
                'Listo. Envie las evidencias del credito ' . $idCredito . ' por el flujo normal y registre la accion en la bitacora.',
                'agente_ejecutado'
            );
        }

        $resultado = $modelo->cambiarEstatus($idOperacionEsperado, 'Recibido', $actorId, $actorNombre, 'monitoreo');
        if (empty($resultado['success'])) {
            throw new \RuntimeException(trim((string) ($resultado['message'] ?? 'No se pudo forzar el movimiento.')));
        }

        return $this->respuesta(
            'Listo. Force el movimiento a Evidencias del credito ' . $idCredito
                . '. La accion quedo auditada. No se inventaron datos de motocicleta, evidencias ni dictamenes; esos faltantes siguen pendientes.',
            'agente_ejecutado'
        );
    }

    public function diagnosticar(int $idCredito): array
    {
        $operacion = $this->db->queryOne(
            "SELECT o.*,
                    COUNT(e.id) AS evidencias_total,
                    SUM(CASE WHEN e.estatus = 'pendiente_envio' THEN 1 ELSE 0 END) AS evidencias_pendientes,
                    SUM(CASE WHEN e.estatus = 'recibido' THEN 1 ELSE 0 END) AS evidencias_recibidas
               FROM adj_operacion o
               LEFT JOIN adj_evidencia e ON e.id_operacion = o.id
              WHERE o.id_credito = :id_credito
              GROUP BY o.id
              ORDER BY o.id DESC
              LIMIT 1",
            ['id_credito' => $idCredito]
        );
        $asignacion = $this->db->queryOne(
            "SELECT aca.id, aca.id_credito, aca.id_personal_adj, aca.estatus, aca.fecha_alta,
                    pa.id_persona,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                    p.numero_empleado
               FROM asigna_creditos_adjudicacion aca
               INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
               INNER JOIN persona p ON p.id = pa.id_persona
              WHERE aca.id_credito = :id_credito AND aca.estatus = '1'
              ORDER BY aca.id DESC
              LIMIT 1",
            ['id_credito' => $idCredito]
        );

        $legacy = [
            'disponible' => $this->legacy !== null,
            'error' => $this->legacyError,
            'tasks' => [],
            'assignments' => [],
            'dictums' => [],
            'motos_task' => null,
            'otra_task_activa' => null,
        ];
        if ($this->legacy !== null) {
            try {
                $legacy = array_merge($legacy, $this->consultarLegacy($idCredito));
            } catch (\Throwable $e) {
                $legacy['disponible'] = false;
                $legacy['error'] = $e->getMessage();
                error_log('[LeonidasMotosAdjudicadasService] Error consultando credito ' . $idCredito . ': ' . $e->getMessage());
            }
        }

        return [
            'id_credito' => $idCredito,
            'operacion' => $operacion,
            'asignacion_local' => $asignacion,
            'legacy' => $legacy,
        ];
    }

    private function consultarLegacy(int $idCredito): array
    {
        $tasks = $this->legacy->queryAll(
            "SELECT t.id AS task_id, t.credit_number, t.client_name,
                    t.status AS task_status, t.current_user_id,
                    t.deleted_at AS task_deleted_at,
                    t.created_at AS task_created_at, t.updated_at AS task_updated_at,
                    c.id AS campaign_id, c.name AS campaign_name,
                    c.start_date, c.end_date, c.deleted_at AS campaign_deleted_at,
                    cu.name AS current_user_name, cu.external_id AS current_user_external_id
               FROM tasks t
               LEFT JOIN campaigns c ON c.id = t.campaign_id
               LEFT JOIN users cu ON cu.id = t.current_user_id
              WHERE TRIM(t.credit_number) = :credito
              ORDER BY t.id DESC",
            ['credito' => (string) $idCredito]
        );

        $assignments = [];
        $dictums = [];
        $taskIds = array_values(array_filter(array_map('intval', array_column($tasks, 'task_id'))));
        if ($taskIds) {
            [$in, $params] = $this->placeholders($taskIds, 'task');
            $assignments = $this->legacy->queryAll(
                "SELECT tua.id, tua.task_id, tua.user_id,
                        u.name AS user_name, u.external_id, u.username,
                        tua.assigned_at, tua.unassigned_at
                   FROM task_user_assignments tua
                   LEFT JOIN users u ON u.id = tua.user_id
                  WHERE tua.task_id IN ({$in})
                  ORDER BY tua.task_id DESC, tua.id DESC",
                $params
            );
            $dictums = $this->legacy->queryAll(
                "SELECT d.id, d.task_id, d.opciondictamen_id, d.user_id,
                        u.name AS user_name, d.created_at, d.updated_at,
                        d.sent_at, d.valid_geofencing, d.handling_time,
                        CASE WHEN d.form_response IS NULL OR TRIM(d.form_response) = '' THEN 0 ELSE 1 END AS tiene_respuesta
                   FROM dictums d
                   LEFT JOIN users u ON u.id = d.user_id
                  WHERE d.task_id IN ({$in})
                  ORDER BY d.task_id DESC, d.id DESC",
                $params
            );
        }

        $asignacionesPorTarea = [];
        foreach ($assignments as $assignment) {
            $taskId = (int) ($assignment['task_id'] ?? 0);
            $asignacionesPorTarea[$taskId][] = $assignment;
        }
        foreach ($tasks as &$task) {
            $taskId = (int) ($task['task_id'] ?? 0);
            $task['active_assignment'] = $this->asignacionActiva($asignacionesPorTarea[$taskId] ?? []);
        }
        unset($task);

        $activas = array_values(array_filter($tasks, fn(array $task): bool => $this->tareaActiva($task)));
        $motosTask = null;
        $otraTask = null;
        foreach ($activas as $task) {
            if ($motosTask === null && $this->esCampaniaMotos((string) ($task['campaign_name'] ?? ''))) {
                $motosTask = $task;
                continue;
            }
            if ($otraTask === null && !$this->esCampaniaMotos((string) ($task['campaign_name'] ?? ''))) {
                $otraTask = $task;
            }
        }

        return [
            'disponible' => true,
            'error' => null,
            'tasks' => $tasks,
            'assignments' => $assignments,
            'dictums' => $dictums,
            'motos_task' => $motosTask,
            'otra_task_activa' => $otraTask,
        ];
    }

    private function formatearDiagnostico(array $diagnostico): string
    {
        $idCredito = (int) ($diagnostico['id_credito'] ?? 0);
        $operacion = is_array($diagnostico['operacion'] ?? null) ? $diagnostico['operacion'] : [];
        $local = is_array($diagnostico['asignacion_local'] ?? null) ? $diagnostico['asignacion_local'] : [];
        $legacy = is_array($diagnostico['legacy'] ?? null) ? $diagnostico['legacy'] : [];
        $motosTask = is_array($legacy['motos_task'] ?? null) ? $legacy['motos_task'] : [];
        $otraTask = is_array($legacy['otra_task_activa'] ?? null) ? $legacy['otra_task_activa'] : [];

        $cliente = trim((string) ($operacion['nombre_cliente'] ?? ''));
        if ($cliente === '') {
            $cliente = trim((string) ($motosTask['client_name'] ?? ($legacy['tasks'][0]['client_name'] ?? '')));
        }
        $lineas = ['Encontre el credito ' . $idCredito . ($cliente !== '' ? ' de ' . $this->nombre($cliente) : '') . '.'];
        $lineas[] = '';
        $lineas[] = 'Diagnostico cruzado:';

        if ($local) {
            $lineas[] = '- Sparta / Motos Adjudicadas: asignado a ' . $this->nombre((string) $local['nombre'])
                . ' (No. empleado ' . ($local['numero_empleado'] ?: 'sin dato') . ').';
        } else {
            $lineas[] = '- Sparta / Motos Adjudicadas: sin asignacion local activa.';
        }
        if ($operacion) {
            $lineas[] = '- Operacion Sparta: ' . (int) $operacion['id'] . ', estatus '
                . $this->etiquetaEstatus((string) ($operacion['estatus'] ?? '')) . ', '
                . (!empty($operacion['datos_moto_at']) ? 'con datos de motocicleta' : 'sin datos de motocicleta')
                . ' y ' . (int) ($operacion['evidencias_total'] ?? 0) . ' evidencia(s).';
        }

        if (empty($legacy['disponible'])) {
            $lineas[] = '- Legacy: no estuvo disponible durante esta consulta. La respuesta local se conserva, pero el diagnostico no esta completo.';
            return implode("\n", $lineas);
        }

        if ($motosTask) {
            $responsable = $this->responsableLegacy($motosTask);
            $lineas[] = '- Legacy / Motos Adjudicadas: tarea ' . (int) $motosTask['task_id']
                . ', campania ' . (string) ($motosTask['campaign_name'] ?? 'sin nombre')
                . ', estatus ' . ((string) ($motosTask['task_status'] ?? '') ?: 'sin dato')
                . ', usuario Legacy ' . ($responsable['id'] ?: 'sin dato') . ' (' . $responsable['nombre'] . ')'
                . ($responsable['assignment_id'] > 0 ? ', asignacion activa ' . $responsable['assignment_id'] : '') . '.';
        } else {
            $lineas[] = '- Legacy / Motos Adjudicadas: no encontre una tarea activa de esta campania.';
        }
        if ($otraTask) {
            $responsable = $this->responsableLegacy($otraTask);
            $lineas[] = '- Legacy / otro flujo activo: tarea ' . (int) $otraTask['task_id'] . ', campania '
                . (string) ($otraTask['campaign_name'] ?? 'sin nombre') . ', asignada a ' . $responsable['nombre']
                . '. Es otro flujo y no sustituye al responsable de Motos Adjudicadas.';
        }

        $dictums = is_array($legacy['dictums'] ?? null) ? $legacy['dictums'] : [];
        $lineas[] = '- Historial Legacy consultado: ' . count($legacy['tasks'] ?? []) . ' tarea(s), '
            . count($legacy['assignments'] ?? []) . ' asignacion(es) y ' . count($dictums) . ' dictamen(es).';
        $dictumsMotos = 0;
        $motosTaskId = (int) ($motosTask['task_id'] ?? 0);
        foreach ($dictums as $dictum) {
            if ((int) ($dictum['task_id'] ?? 0) === $motosTaskId && $motosTaskId > 0) {
                $dictumsMotos++;
            }
        }
        $lineas[] = '- Dictamenes Legacy: la tarea vigente de Motos tiene ' . $dictumsMotos
            . '; el credito acumula ' . count($dictums) . ' dictamen(es) en todas sus tareas historicas.';

        return implode("\n", $lineas);
    }

    private function payload(array $diagnostico): array
    {
        $operacion = $diagnostico['operacion'];
        $motosTask = $diagnostico['legacy']['motos_task'] ?? [];
        return [
            'id_credito' => (int) $diagnostico['id_credito'],
            'id_operacion' => (int) ($operacion['id'] ?? 0),
            'estatus_esperado' => (string) ($operacion['estatus'] ?? ''),
            'legacy_motos_task_id' => (int) ($motosTask['task_id'] ?? 0),
        ];
    }

    private function responsableLegacy(array $task): array
    {
        $assignment = is_array($task['active_assignment'] ?? null) ? $task['active_assignment'] : [];
        $nombre = trim((string) ($assignment['user_name'] ?? $task['current_user_name'] ?? ''));
        return [
            'id' => (int) ($assignment['user_id'] ?? $task['current_user_id'] ?? 0),
            'nombre' => $nombre !== '' ? $this->nombre($nombre) : 'sin responsable identificado',
            'assignment_id' => (int) ($assignment['id'] ?? 0),
        ];
    }

    private function asignacionActiva(array $assignments): ?array
    {
        foreach ($assignments as $assignment) {
            if (empty($assignment['unassigned_at'])) {
                return $assignment;
            }
        }
        return null;
    }

    private function tareaActiva(array $task): bool
    {
        return empty($task['task_deleted_at']) && empty($task['campaign_deleted_at']);
    }

    private function esCampaniaMotos(string $campaign): bool
    {
        return str_contains($this->normalizar($campaign), 'motos adjudicadas');
    }

    private function placeholders(array $ids, string $prefix): array
    {
        $params = [];
        $tokens = [];
        foreach ($ids as $index => $id) {
            $key = $prefix . '_' . $index;
            $tokens[] = ':' . $key;
            $params[$key] = (int) $id;
        }
        return [implode(',', $tokens), $params];
    }

    private function extraerCredito(string $mensaje): int
    {
        if (preg_match('/\b\d{4,12}\b/', str_replace([',', '.'], '', $mensaje), $match) !== 1) {
            return 0;
        }
        return (int) $match[0];
    }

    private function normalizar(string $valor): string
    {
        $valor = mb_strtolower(trim($valor), 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        if ($ascii !== false) {
            return $ascii;
        }
        return strtr($valor, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
    }

    private function nombre(string $valor): string
    {
        $valor = preg_replace('/\s+/u', ' ', trim($valor)) ?? trim($valor);
        return mb_convert_case(mb_strtolower($valor, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function etiquetaEstatus(string $estatus): string
    {
        return match (mb_strtolower(trim($estatus), 'UTF-8')) {
            'en_transito' => 'En transito',
            'recibido' => 'Recibido / Evidencias',
            default => $estatus !== '' ? $estatus : 'sin estatus',
        };
    }

    private function respuesta(string $mensaje, string $tipo): array
    {
        return ['mensaje' => $mensaje, 'tipo' => $tipo];
    }
}
