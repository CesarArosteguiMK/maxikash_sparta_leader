<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseLegacy;
use Core\DatabaseSegundometro;
use Core\UsuarioFantasmaReporteria;

class Adjudicacion extends Model
{
    private const MODULO_DESBLOQUEAR_COMPONENTES_MOTOS_ADJUDICADAS = 195;
    private const LEGACY_CAMP_MOTOS_ADJUDICADAS = 432;
    private const LEGACY_DICTAMEN_MOTO_ADJUDICADA = 13;

    private $db;
    private $dbSeg;

    public function __construct()
    {
        $this->db    = new Database();
        $this->dbSeg = new DatabaseSegundometro();
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    /**
     * Fecha/hora actual en zona Ciudad de México.
     */
    private function fechaHoraCdmx(): string
    {
        $dt = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        return $dt->format('Y-m-d H:i:s');
    }

    private function normalizarFechaHoraCdmx($raw): string
    {
        $valor = trim((string) $raw);
        if ($valor === '') {
            return $this->fechaHoraCdmx();
        }

        $valor = str_replace('T', ' ', $valor);
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/', $valor, $m)) {
            $valor = sprintf(
                '%04d-%02d-%02d %02d:%02d:%02d',
                (int) $m[3],
                (int) $m[2],
                (int) $m[1],
                (int) ($m[4] ?? 0),
                (int) ($m[5] ?? 0),
                (int) ($m[6] ?? 0)
            );
        } elseif (preg_match('/^(\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2})(?::\d{2})?$/', $valor, $m)) {
            $valor = strlen($valor) === 16 ? $valor . ':00' : $valor;
        }

        try {
            $dt = new \DateTime($valor, new \DateTimeZone('America/Mexico_City'));
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Fecha y hora de gestion invalida.');
        }
    }

    /**
     * Indica si una fila de asignación está cerrada (estatus 0 + fecha_baja registrada).
     */
    private function asignacionEstaCerrada(?array $row): bool
    {
        if ($row === null || $row === []) {
            return false;
        }
        $st = $row['estatus'] ?? '';
        if ($st !== '0' && $st !== 0) {
            return false;
        }
        $fb = $row['fecha_baja'] ?? null;
        if ($fb === null || trim((string) $fb) === '' || trim((string) $fb) === '0000-00-00 00:00:00') {
            return false;
        }
        return true;
    }

    /**
     * Genera el siguiente folio para adj_operacion: ADJ-YYYY-NNNN.
     */
    private function generarFolioOperacion(): string
    {
        $anio = date('Y');
        $row  = $this->db->queryOne(
            "SELECT MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)) AS ultimo
             FROM adj_operacion
             WHERE folio LIKE :prefijo",
            ['prefijo' => "ADJ-{$anio}-%"]
        );

        $siguiente = (int)($row['ultimo'] ?? 0) + 1;
        return sprintf('ADJ-%s-%04d', $anio, $siguiente);
    }

    private function resolverNombreClienteOperacion(int $idCredito): string
    {
        $fallback = "Crédito #{$idCredito}";

        try {
            $sky = $this->db->queryOne(
                "SELECT TRIM(COALESCE(nombre_completo_cliente, nombre_cliente, '')) AS nombre
                 FROM base_clientes
                 WHERE id_credito = :id
                 ORDER BY fecha_dispositivo DESC, id DESC
                 LIMIT 1",
                ['id' => $idCredito]
            );
            $nombre = trim((string) ($sky['nombre'] ?? ''));
            if ($nombre !== '') {
                return $nombre;
            }
        } catch (\Throwable $e) {
        }

        try {
            $s2 = $this->dbSeg->queryOne(
                "SELECT Nombre_cliente FROM tbl_segundometro_semana WHERE Id_credito = :id LIMIT 1",
                ['id' => $idCredito]
            );
            $nombre = trim((string) ($s2['Nombre_cliente'] ?? ''));
            if ($nombre !== '') {
                return $nombre;
            }

            $s2 = $this->dbSeg->queryOne(
                "SELECT MAX(Nombre_cliente) AS Nombre_cliente
                 FROM tbl_segundometro_histo
                 WHERE Id_credito = :id",
                ['id' => $idCredito]
            );
            $nombre = trim((string) ($s2['Nombre_cliente'] ?? ''));
            if ($nombre !== '') {
                return $nombre;
            }
        } catch (\Throwable $e) {
        }

        return $fallback;
    }

    /**
     * Determina si la ultima operacion debe quedarse como historica y dar paso a una nueva.
     */
    private function estatusOperacionRequiereNuevaOperacion(?string $estatus): bool
    {
        $normalizado = strtolower(trim(preg_replace('/\s+/', ' ', (string) $estatus)));

        return in_array($normalizado, [
            'blacklist',
            'visto bueno denegado',
            'cancelado',
            'cancelada',
            'concluido',
            'concluida',
            'completado',
            'completada',
            'finalizado',
            'finalizada',
        ], true);
    }

    private function operacionTieneBloqueoActivo(int $idOperacion): bool
    {
        if ($idOperacion <= 0) {
            return false;
        }

        try {
            $row = $this->db->queryOne(
                "SELECT id
                 FROM adj_operacion_blacklist
                 WHERE id_operacion = :id_operacion
                   AND activo = 1
                 LIMIT 1",
                ['id_operacion' => $idOperacion]
            );

            return !empty($row);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Crea adj_operacion en etapa Evidencias (en_transito) o actualiza fecha si existe una operacion viva.
     * Si la ultima operacion quedo cancelada/denegada/bloqueada, la reasignacion crea folio nuevo.
     */
    private function asegurarOperacionTrasAsignacionCredito(
        int $idCredito,
        int $idUsuario,
        string $fecha,
        bool $crearNuevaSiOperacionCerrada = true
    ): void
    {
        $op = $this->db->queryOne(
            "SELECT id, estatus FROM adj_operacion WHERE id_credito = :id ORDER BY id DESC LIMIT 1",
            ['id' => $idCredito]
        );

        if ($op) {
            $idOperacion = (int) $op['id'];
            $debeCrearNueva = $crearNuevaSiOperacionCerrada
                && (
                    $this->estatusOperacionRequiereNuevaOperacion((string) ($op['estatus'] ?? ''))
                    || $this->operacionTieneBloqueoActivo($idOperacion)
                );

            if (!$debeCrearNueva) {
                $this->db->CRUD(
                    'UPDATE adj_operacion SET fecha_actualizacion = :fecha WHERE id = :id',
                    ['fecha' => $fecha, 'id' => $idOperacion]
                );

                return;
            }
        }

        $nombreCliente = "Crédito #{$idCredito}";

        try {
            $s2 = $this->dbSeg->queryOne(
                "SELECT Nombre_cliente FROM tbl_segundometro_semana WHERE Id_credito = :id LIMIT 1",
                ['id' => $idCredito]
            );

            if (!$s2) {
                $s2 = $this->dbSeg->queryOne(
                    "SELECT MAX(Nombre_cliente) AS Nombre_cliente
                     FROM tbl_segundometro_histo
                     WHERE Id_credito = :id",
                    ['id' => $idCredito]
                );
            }

            if (!empty($s2['Nombre_cliente'])) {
                $nombreCliente = trim((string) $s2['Nombre_cliente']);
            }
        } catch (\Exception $e) {
            // Si S2 no responde, se crea con nombre mínimo para no frenar el flujo.
        }

        $nombreCliente = $this->resolverNombreClienteOperacion($idCredito);

        $this->db->CRUD(
            "INSERT INTO adj_operacion
                (folio, id_credito, nombre_cliente, estatus, id_usuario_alta, fecha_alta, fecha_actualizacion)
             VALUES
                (:folio, :id_credito, :nombre_cliente, 'en_transito', :id_usuario_alta, :fecha_alta, :fecha_actualizacion)",
            [
                'folio'               => $this->generarFolioOperacion(),
                'id_credito'          => $idCredito,
                'nombre_cliente'      => $nombreCliente,
                'id_usuario_alta'     => $idUsuario ?: null,
                'fecha_alta'          => $fecha,
                'fecha_actualizacion' => $fecha,
            ]
        );
    }

    // =========================================================================
    // LISTA DE RESPONSABLES
    // =========================================================================

    /**
     * Devuelve todos los responsables activos en personal_adjudicacion con
     * nombre completo obtenido desde persona y puesto desde asigna_puesto.
     */
    public function obtenerResponsables(): array
    {
        $query = <<<SQL
        SELECT
            pa.id                                                                           AS id_personal_adj,
            pa.id_persona,
            TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_completo,
            TRIM(COALESCE(per.numero_empleado, ''))                                         AS numero_empleado,
            TRIM(COALESCE(per.codigo_contpac, ''))                                          AS codigo_contpac,
            GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ')            AS puesto,
            pa.numero_tel1,
            pa.correo_1,
            pa.estatus
        FROM personal_adjudicacion pa
        INNER JOIN persona per ON per.id = pa.id_persona
        LEFT JOIN asigna_puesto ap ON ap.id_persona = pa.id_persona AND ap.activo = 1
        LEFT JOIN puesto pu ON pu.id = ap.id_puesto
        WHERE pa.estatus = 'Activo'
        GROUP BY pa.id, pa.id_persona, per.nombres, per.segundo_nombre, per.apellidop, per.apellidom,
                 per.numero_empleado, per.codigo_contpac,
                 pa.numero_tel1, pa.correo_1, pa.estatus
        ORDER BY nombre_completo
        SQL;

        return $this->db->queryAll($query) ?: [];
    }

    /**
     * Comprueba si id_persona corresponde a un responsable activo de adjudicación.
     */
    public function idPersonaEsResponsableActivo(int $idPersona): bool
    {
        if ($idPersona <= 0) {
            return false;
        }
        $row = $this->db->queryOne(
            "SELECT 1 AS ok
             FROM personal_adjudicacion
             WHERE id_persona = :id AND estatus = 'Activo'
             LIMIT 1",
            ['id' => $idPersona]
        );

        return !empty($row);
    }

    // =========================================================================
    // DATOS DE UN RESPONSABLE
    // =========================================================================

    /**
     * Datos completos de un responsable por su id_persona.
     * Crea automáticamente la fila en personal_adjudicacion si aún no existe.
     */
    public function obtenerDatosResponsable(int $idPersona): ?array
    {
        $query = <<<SQL
        SELECT
            pa.id                                                                           AS id_personal_adj,
            pa.id_persona,
            TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_completo,
            GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ')            AS puesto,
            COALESCE(pa.numero_tel1, per.telefono_uno) AS telefono,
            COALESCE(pa.correo_1,   per.correo)        AS correo,
            pa.direccion,
            pa.estatus
        FROM personal_adjudicacion pa
        INNER JOIN persona per ON per.id = pa.id_persona
        LEFT JOIN asigna_puesto ap ON ap.id_persona = pa.id_persona AND ap.activo = 1
        LEFT JOIN puesto pu ON pu.id = ap.id_puesto
        WHERE pa.id_persona = :idPersona
        GROUP BY pa.id, pa.id_persona, per.nombres, per.segundo_nombre, per.apellidop, per.apellidom,
                 per.telefono_uno, per.correo, pa.numero_tel1, pa.correo_1, pa.direccion, pa.estatus
        LIMIT 1
        SQL;

        return $this->db->queryOne($query, ['idPersona' => $idPersona]) ?: null;
    }

    /**
     * Registra a una persona en personal_adjudicacion si todavía no tiene fila.
     * Retorna el id de la fila (existente o recién creada).
     */
    public function asegurarPersonalAdjudicacion(int $idPersona): int
    {
        $row = $this->db->queryOne(
            'SELECT id FROM personal_adjudicacion WHERE id_persona = :idPersona LIMIT 1',
            ['idPersona' => $idPersona]
        );

        if ($row) {
            return (int) $row['id'];
        }

        $this->db->CRUD(
            "INSERT INTO personal_adjudicacion (id_persona, estatus, fecha_alta) VALUES (:idPersona, 'Activo', :fechaAlta)",
            ['idPersona' => $idPersona, 'fechaAlta' => $this->fechaHoraCdmx()]
        );

        return (int) $this->db->lastInsertId();
    }

    // =========================================================================
    // ASIGNACIÓN DE CRÉDITOS
    // =========================================================================

    /**
     * Asigna un crédito a un responsable de adjudicación.
     *
     * Lógica:
     *  - Si el crédito ya tiene asignación activa → error.
     *  - Si la última fila está cerrada (estatus 0 + fecha_baja) → nueva INSERT.
     *  - Si no existe fila → INSERT.
     *
     * @return array{success:bool, message:string}
     */
    public function asignarCredito(int $idPersona, int $idCredito, int $usuarioAlta): array
    {
        $fechaAlta = $this->fechaHoraCdmx();

        // Garantizar que exista fila en personal_adjudicacion
        $idPersonalAdj = $this->asegurarPersonalAdjudicacion($idPersona);

        // ¿Existe asignación activa para este crédito?
        $activa = $this->db->queryOne(
            "SELECT id, id_personal_adj FROM asigna_creditos_adjudicacion
             WHERE id_credito = :idCredito AND estatus = '1' LIMIT 1",
            ['idCredito' => $idCredito]
        );

        if ($activa) {
            // Ya está asignado (quizás al mismo responsable u otro)
            if ((int) $activa['id_personal_adj'] === $idPersonalAdj) {
                $this->asegurarOperacionTrasAsignacionCredito($idCredito, $usuarioAlta, $fechaAlta, false);
                return ['success' => false, 'message' => 'Este crédito ya está asignado a este responsable.'];
            }
            return ['success' => false, 'message' => 'Este crédito ya está asignado a otro responsable. Libérelo primero.'];
        }

        // Insertar nueva asignación
        $n = $this->db->CRUD(
            "INSERT INTO asigna_creditos_adjudicacion
                (id_personal_adj, id_credito, fecha_alta, alta, estatus)
             VALUES (:idPersonalAdj, :idCredito, :fechaAlta, :alta, '1')",
            [
                'idPersonalAdj' => $idPersonalAdj,
                'idCredito'     => $idCredito,
                'fechaAlta'     => $fechaAlta,
                'alta'          => $usuarioAlta > 0 ? $usuarioAlta : null,
            ]
        );

        if ($n > 0) {
            $this->asegurarOperacionTrasAsignacionCredito($idCredito, $usuarioAlta, $fechaAlta);
            return ['success' => true, 'message' => 'Crédito asignado correctamente.'];
        }

        return ['success' => false, 'message' => 'No se pudo registrar la asignación.'];
    }

    /**
     * Registra o cambia la asignación activa de forma atómica.
     * Si ya pertenece al responsable solicitado, solo revalida la operación asociada.
     *
     * @return array{success:bool,message:string,reassigned?:bool,already_assigned?:bool}
     */
    public function reasignarCredito(int $idPersona, int $idCredito, int $usuarioCambio): array
    {
        if ($idPersona <= 0 || $idCredito <= 0) {
            return ['success' => false, 'message' => 'La persona o el crédito no son válidos.'];
        }

        $fecha = $this->fechaHoraCdmx();
        $idPersonalAdj = $this->asegurarPersonalAdjudicacion($idPersona);

        try {
            $this->db->beginTransaction();
            $activa = $this->db->queryOne(
                "SELECT id, id_personal_adj
                 FROM asigna_creditos_adjudicacion
                 WHERE id_credito = :idCredito AND estatus = '1'
                 ORDER BY id DESC
                 LIMIT 1
                 FOR UPDATE",
                ['idCredito' => $idCredito]
            );

            if ($activa && (int) $activa['id_personal_adj'] === $idPersonalAdj) {
                $this->asegurarOperacionTrasAsignacionCredito($idCredito, $usuarioCambio, $fecha, false);
                $this->db->commit();
                return [
                    'success' => true,
                    'message' => 'El crédito ya estaba asignado al responsable solicitado; se revalidó la operación.',
                    'reassigned' => false,
                    'already_assigned' => true,
                ];
            }

            $reasignado = false;
            if ($activa) {
                $this->db->CRUD(
                    "UPDATE asigna_creditos_adjudicacion
                     SET estatus = '0', fecha_baja = :fechaBaja, baja = :baja
                     WHERE id = :id AND estatus = '1'",
                    [
                        'fechaBaja' => $fecha,
                        'baja' => $usuarioCambio > 0 ? $usuarioCambio : null,
                        'id' => (int) $activa['id'],
                    ]
                );
                $reasignado = true;
            }

            $insertados = $this->db->CRUD(
                "INSERT INTO asigna_creditos_adjudicacion
                    (id_personal_adj, id_credito, fecha_alta, alta, estatus)
                 VALUES (:idPersonalAdj, :idCredito, :fechaAlta, :alta, '1')",
                [
                    'idPersonalAdj' => $idPersonalAdj,
                    'idCredito' => $idCredito,
                    'fechaAlta' => $fecha,
                    'alta' => $usuarioCambio > 0 ? $usuarioCambio : null,
                ]
            );
            if ($insertados <= 0) {
                throw new \RuntimeException('No se pudo registrar la nueva asignación.');
            }

            $this->asegurarOperacionTrasAsignacionCredito($idCredito, $usuarioCambio, $fecha);
            $this->db->commit();
            return [
                'success' => true,
                'message' => $reasignado
                    ? 'Crédito reasignado correctamente.'
                    : 'Crédito asignado correctamente.',
                'reassigned' => $reasignado,
                'already_assigned' => false,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            return ['success' => false, 'message' => 'No se pudo completar la asignación: ' . $e->getMessage()];
        }
    }

    /**
     * Desasigna (cierra) la asignación activa de un crédito.
     *
     * @return array{success:bool, message:string}
     */
    public function desasignarCredito(int $idCredito, int $usuarioBaja): array
    {
        $fechaBaja = $this->fechaHoraCdmx();

        $activa = $this->db->queryOne(
            "SELECT id FROM asigna_creditos_adjudicacion
             WHERE id_credito = :idCredito AND estatus = '1' LIMIT 1",
            ['idCredito' => $idCredito]
        );

        if (!$activa) {
            return ['success' => false, 'message' => 'No se encontró asignación activa para este crédito.'];
        }

        $n = $this->db->CRUD(
            "UPDATE asigna_creditos_adjudicacion
             SET estatus = '0', fecha_baja = :fechaBaja, baja = :baja
             WHERE id = :id",
            [
                'fechaBaja' => $fechaBaja,
                'baja'      => $usuarioBaja,
                'id'        => (int) $activa['id'],
            ]
        );

        if ($n > 0) {
            return ['success' => true, 'message' => 'Crédito desasignado correctamente.'];
        }

        return ['success' => false, 'message' => 'No se pudo actualizar la asignación.'];
    }

    // =========================================================================
    // CRÉDITOS ASIGNADOS A UN RESPONSABLE
    // =========================================================================

    /**
     * Lista de créditos actualmente asignados a un responsable (estatus 1).
     * Incluye datos básicos del crédito para la tabla principal.
     */
    public function obtenerCreditosAsignados(int $idPersona): array
    {
        $query = <<<SQL
        SELECT
            aca.id_credito                                          AS id_credito,
            IF(aca.estatus = '1', 'Activo', 'Inactivo')            AS estado,
            DATE_FORMAT(aca.fecha_alta, '%Y-%m-%d %H:%i')          AS fecha_asignacion,
            DATE_FORMAT(aca.fecha_baja, '%Y-%m-%d %H:%i')          AS fecha_desasignacion,
            COALESCE(op.nombre_cliente, '—')                       AS nombre_cliente,
            TRIM(CONCAT_WS(' ', per_alta.nombres, per_alta.apellidop)) AS asignado_por,
            aca.id                                                  AS id_asignacion
        FROM asigna_creditos_adjudicacion aca
        INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
        LEFT JOIN persona per_alta ON per_alta.id = aca.alta
        LEFT JOIN (
            SELECT ao1.id_credito, ao1.nombre_cliente
            FROM adj_operacion ao1
            INNER JOIN (
                SELECT id_credito, MAX(id) AS id_max
                FROM adj_operacion
                GROUP BY id_credito
            ) aom ON aom.id_max = ao1.id
        ) op ON op.id_credito = aca.id_credito
        WHERE pa.id_persona = :idPersona
          AND aca.estatus = '1'
        ORDER BY aca.fecha_alta DESC
        SQL;

        return $this->db->queryAll($query, ['idPersona' => $idPersona]) ?: [];
    }

    // =========================================================================
    // HISTORIAL DE UN CRÉDITO
    // =========================================================================

    /**
     * Historial completo de asignaciones de adjudicación para un crédito
     * (todos los responsables que lo tuvieron, activos e inactivos).
     */
    public function obtenerHistorialCredito(int $idCredito): array
    {
        $query = <<<SQL
        SELECT
            aca.id_credito,
            aca.estatus,
            DATE_FORMAT(aca.fecha_alta, '%Y-%m-%d %H:%i')          AS fecha_asignacion,
            DATE_FORMAT(aca.fecha_baja, '%Y-%m-%d %H:%i')          AS fecha_baja,
            pa.id_persona,
            TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_despacho,
            GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ')                 AS puesto_despacho,
            TRIM(CONCAT_WS(' ', per_alta.nombres, per_alta.apellidop))                          AS asignado_por
        FROM asigna_creditos_adjudicacion aca
        INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
        INNER JOIN persona per ON per.id = pa.id_persona
        LEFT JOIN asigna_puesto ap ON ap.id_persona = pa.id_persona AND ap.activo = 1
        LEFT JOIN puesto pu ON pu.id = ap.id_puesto
        LEFT JOIN persona per_alta ON per_alta.id = aca.alta
        WHERE aca.id_credito = :idCredito
        GROUP BY aca.id, aca.id_credito, aca.estatus, aca.fecha_alta, aca.fecha_baja,
                 pa.id_persona, per.nombres, per.segundo_nombre, per.apellidop, per.apellidom,
                 per_alta.nombres, per_alta.apellidop
        ORDER BY aca.fecha_alta DESC
        SQL;

        return $this->db->queryAll($query, ['idCredito' => $idCredito]) ?: [];
    }

    // =========================================================================
    // BUSCAR CRÉDITO (API S2 — misma fuente que EstadoCuenta y Despachos)
    // =========================================================================

    /**
     * Busca un crédito por su ID en la API S2 y retorna sus datos junto con
     * la asignación activa en adjudicación (si existe).
     *
     * El campo `status_credito` se incluye en la respuesta para que el
     * controller pueda validar que sea "Vencido" antes de permitir asignación.
     *
     * @return array{success:bool, credito?:array, asignacion?:array|null,
     *               status_credito?:string, message?:string}
     */
    public function buscarCreditoPorId(int $idCredito): array
    {
        // ── 1. Llamada a la API S2 ────────────────────────────────────────────
        $url     = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
        $payload = json_encode([
            'idCredito'  => $idCredito,
            'fechaCorte' => date('Y-m-d'),
        ]);
        $headers = [
            'Token: ' . (defined('TOKEN') ? TOKEN : (getenv('S2_ESTADO_CUENTA_TOKEN') ?: '')),
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST,            true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,      $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER,      $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($ch, CURLOPT_TIMEOUT,         20);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return ['success' => false, 'message' => 'No se pudo conectar con el servicio de créditos (S2).'];
        }

        $json = json_decode($response, true);

        if (!isset($json['estadoCuenta'])) {
            return ['success' => false, 'message' => 'Respuesta inválida del servicio de créditos.'];
        }

        $ec = $json['estadoCuenta'];

        if (empty($ec['idCredito'])) {
            return ['success' => false, 'message' => 'Crédito no encontrado en el sistema.'];
        }

        // ── 2. Construir objeto crédito normalizado ───────────────────────────
        $cliente = $ec['datosCliente'] ?? [];

        $direccionParts = array_filter([
            $cliente['calle']           ?? '',
            $cliente['numeroExterior']  ?? '',
            $cliente['numeroInterior']  ?? '',
            $cliente['colonia']         ?? '',
            $cliente['municipio']       ?? '',
            $cliente['estado']          ?? '',
            $cliente['codigoPostal']    ?? '',
        ]);
        $direccionApi = !empty($direccionParts) ? implode(', ', $direccionParts) : null;

        $credito = [
            'id_credito'       => $ec['idCredito'],
            'nombre_cliente'   => $cliente['nombreCliente']  ?? 'Sin nombre',
            'telefono'         => $cliente['celular']        ?? 'Sin teléfono',
            'curp'             => $cliente['curp']           ?? 'Sin CURP',
            'email'            => $cliente['email']          ?? '',
            'genero'           => $cliente['genero']         ?? '',
            'direccion'        => $direccionApi ?: 'Sin dirección registrada',
            'sucursal'         => $ec['secursal']            ?? 'Sin sucursal',
            'fecha_desembolso' => $ec['fechaInicio']         ?? 'Sin fecha',
            'saldo_actual'     => $ec['datosSaldos']['saldoTotalVencido'] ?? 0,
            'dias_mora'        => $ec['datosSaldos']['diasMoraMaximo']    ?? 0,
            'status_credito'   => $ec['statusCredito']       ?? '',
            'producto'         => $ec['producto']            ?? '',
            'periodicidad'     => $ec['periodicidad']        ?? '',
            'bucket'           => '',
        ];

        // ── 2b. Bucket de morosidad (tbl_segundometro_semana) ─────────────────
        try {
            $rowBucket = $this->dbSeg->queryOne(
                'SELECT Bucket_Morosidad_Real AS bucket
                 FROM tbl_segundometro_semana
                 WHERE Id_credito = :idCredito
                 LIMIT 1',
                ['idCredito' => $idCredito]
            );
            if ($rowBucket) {
                $credito['bucket'] = trim((string) ($rowBucket['bucket'] ?? ''));
            }
        } catch (\Throwable $e) {
            // Si la DB de segundometro no responde, continuamos sin bucket
            $credito['bucket'] = '';
        }

        // ── 3. Asignación activa en adjudicación ──────────────────────────────
        $asignacion = $this->db->queryOne(
            <<<SQL
            SELECT
                aca.id,
                pa.id_persona,
                aca.estatus,
                DATE_FORMAT(aca.fecha_alta, '%Y-%m-%d %H:%i') AS fecha_asignacion,
                TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_despacho,
                GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ')                 AS puesto_despacho
            FROM asigna_creditos_adjudicacion aca
            INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
            INNER JOIN persona per ON per.id = pa.id_persona
            LEFT JOIN asigna_puesto ap ON ap.id_persona = pa.id_persona AND ap.activo = 1
            LEFT JOIN puesto pu ON pu.id = ap.id_puesto
            WHERE aca.id_credito = :idCredito
              AND aca.estatus = '1'
            GROUP BY aca.id, pa.id_persona, aca.estatus, aca.fecha_alta,
                     per.nombres, per.segundo_nombre, per.apellidop, per.apellidom
            LIMIT 1
            SQL,
            ['idCredito' => $idCredito]
        );

        return [
            'success'        => true,
            'credito'        => $credito,
            'asignacion'     => $asignacion ?: null,
            'status_credito' => $credito['status_credito'],
        ];
    }

    /**
     * Mapa operativo del flujo web para replicar la app movil directo en Legacy:
     * campaigns(432) -> tasks -> task_user_assignments -> dictums(13, form_response).
     */
    public function diagnosticarDictamenWebMoto(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de credito invalido.'];
        }

        $segundometro = $this->buscarCreditoSegundometroLocal($idCredito);
        $s2 = $this->buscarCreditoPorId($idCredito);
        $operacion = $this->db->queryOne(
            "SELECT id, folio, id_credito, nombre_cliente, estatus, fecha_alta, fecha_actualizacion
             FROM adj_operacion
             WHERE id_credito = :id
             ORDER BY id DESC
             LIMIT 1",
            ['id' => $idCredito]
        );

        $legacy = [
            'campaign_id' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS,
            'task' => null,
            'dictamen' => null,
            'error' => null,
        ];

        try {
            $legacyDb = new DatabaseLegacy();
            $task = $legacyDb->queryOne(
                "SELECT id, campaign_id, current_user_id, client_name, credit_number, address, lat, lng,
                        form_answered, status, created_at, updated_at
                 FROM tasks
                 WHERE campaign_id = :campaign
                   AND TRIM(CAST(credit_number AS CHAR)) = :credito
                   AND deleted_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1",
                ['campaign' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS, 'credito' => (string) $idCredito]
            );
            $legacy['task'] = $task ?: null;

            if ($task) {
                $dictamen = $legacyDb->queryOne(
                    "SELECT id, task_id, opciondictamen_id, user_id, created_at, sent_at, lat, lng
                     FROM dictums
                     WHERE task_id = :task_id
                       AND opciondictamen_id = :opcion
                     ORDER BY id DESC
                     LIMIT 1",
                    [
                        'task_id' => (int) $task['id'],
                        'opcion' => self::LEGACY_DICTAMEN_MOTO_ADJUDICADA,
                    ]
                );
                $legacy['dictamen'] = $dictamen ?: null;
            }
        } catch (\Throwable $e) {
            $legacy['error'] = $e->getMessage();
        }

        $bloqueos = [];
        $statusS2 = trim((string) ($s2['status_credito'] ?? $s2['credito']['status_credito'] ?? ''));
        $saldoS2 = $s2['credito']['saldo_actual'] ?? null;
        if (empty($s2['success'])) {
            $bloqueos[] = 'No se pudo validar el credito en S2; no se permite avanzar sin estado de cuenta.';
        } elseif ($this->creditoEstaLiquidadoS2($statusS2, $saldoS2)) {
            $bloqueos[] = 'El credito esta saldado/liquidado en S2; no se permite adjudicar algo que ya esta pagado.';
        }
        if ($operacion) {
            $bloqueos[] = 'Ya existe en __SPARTA_SECRET_REDACTED__.adj_operacion; ya esta gestionado en tracking.';
        }
        if (!empty($legacy['dictamen'])) {
            $bloqueos[] = 'Ya existe dictamen Legacy con opciondictamen_id = 13 para la tarea de campana 432.';
        }

        $resultado = [
            'success' => true,
            'id_credito' => $idCredito,
            's2' => $s2,
            'segundometro' => $segundometro,
            'operacion' => $operacion ?: null,
            'legacy' => $legacy,
            'puede_simular' => $bloqueos === [] && empty($legacy['error']),
            'bloqueos' => $bloqueos,
            'mapa' => [
                'campaigns.id' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS,
                'tasks.campaign_id' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS,
                'tasks.credit_number' => (string) $idCredito,
                'task_user_assignments.task_id' => 'tasks.id',
                'task_user_assignments.user_id' => 'tasks.current_user_id',
                'dictums.task_id' => 'tasks.id',
                'dictums.opciondictamen_id' => self::LEGACY_DICTAMEN_MOTO_ADJUDICADA,
                'dictums.form_response' => 'JSON del formulario contestado',
            ],
        ];

        $resultado['desbloqueo_s2_disponible'] = $this->diagnosticoPermiteDesbloqueoS2($resultado);

        return $resultado;
    }

    private function asegurarTablasDesbloqueoComponentes(): void
    {
        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS adj_desbloqueo_componentes_nip (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                nip_hash VARCHAR(255) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL,
                created_by INT NULL,
                UNIQUE KEY uq_adj_desbloqueo_usuario (id_usuario),
                KEY idx_adj_desbloqueo_activo (activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS adj_desbloqueo_componentes_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                id_credito INT NOT NULL,
                legacy_tasks_deleted INT NOT NULL DEFAULT 0,
                legacy_assignments_deleted INT NOT NULL DEFAULT 0,
                legacy_dictums_deleted INT NOT NULL DEFAULT 0,
                local_operaciones_deleted INT NOT NULL DEFAULT 0,
                ip VARCHAR(45) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_adj_desbloqueo_log_credito (id_credito),
                KEY idx_adj_desbloqueo_log_usuario (id_usuario)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function usuarioPuedeDesbloquearComponentes(int $idUsuario): array
    {
        if ($idUsuario <= 0) {
            return ['success' => true, 'authorized' => false];
        }

        $this->asegurarTablasDesbloqueoComponentes();
        $permisoModulo = $this->db->queryOne(
            "SELECT id
             FROM asigna_modulo_web
             WHERE usuario_id = :id_usuario
               AND modulo_web_id = :modulo_id
             LIMIT 1",
            [
                'id_usuario' => $idUsuario,
                'modulo_id' => self::MODULO_DESBLOQUEAR_COMPONENTES_MOTOS_ADJUDICADAS,
            ]
        );
        $nipConfigurado = $this->db->queryOne(
            "SELECT id
             FROM adj_desbloqueo_componentes_nip
             WHERE id_usuario = :id_usuario
               AND activo = 1
             LIMIT 1",
            ['id_usuario' => $idUsuario]
        );

        return [
            'success' => true,
            'authorized' => !empty($permisoModulo) && !empty($nipConfigurado),
            'permiso_modulo' => !empty($permisoModulo),
            'nip_configurado' => !empty($nipConfigurado),
        ];
    }

    public function guardarNipDesbloqueoComponentes(int $idUsuario, string $nip, int $createdBy): array
    {
        if ($idUsuario <= 0) {
            return ['success' => false, 'message' => 'Usuario invalido para configurar NIP.'];
        }
        if (!preg_match('/^\d{6}$/', $nip)) {
            return ['success' => false, 'message' => 'El NIP debe tener 6 digitos.'];
        }

        $this->asegurarTablasDesbloqueoComponentes();
        $hash = password_hash($nip, PASSWORD_DEFAULT);
        $this->db->CRUD(
            "INSERT INTO adj_desbloqueo_componentes_nip
                (id_usuario, nip_hash, activo, created_at, updated_at, created_by)
             VALUES
                (:id_usuario, :nip_hash, 1, :created_at, :updated_at, :created_by)
             ON DUPLICATE KEY UPDATE
                nip_hash = VALUES(nip_hash),
                activo = 1,
                updated_at = VALUES(updated_at),
                created_by = VALUES(created_by)",
            [
                'id_usuario' => $idUsuario,
                'nip_hash' => $hash,
                'created_at' => $this->fechaHoraCdmx(),
                'updated_at' => $this->fechaHoraCdmx(),
                'created_by' => $createdBy ?: null,
            ]
        );

        return ['success' => true, 'message' => 'NIP de desbloqueo configurado.'];
    }

    private function validarNipDesbloqueoComponentes(int $idUsuario, string $nip): array
    {
        if ($idUsuario <= 0) {
            return ['success' => false, 'message' => 'Sesion invalida para desbloquear.'];
        }
        if (!preg_match('/^\d{6}$/', $nip)) {
            return ['success' => false, 'message' => 'El NIP debe tener 6 digitos.'];
        }

        $this->asegurarTablasDesbloqueoComponentes();
        $permisoModulo = $this->db->queryOne(
            "SELECT id
             FROM asigna_modulo_web
             WHERE usuario_id = :id_usuario
               AND modulo_web_id = :modulo_id
             LIMIT 1",
            [
                'id_usuario' => $idUsuario,
                'modulo_id' => self::MODULO_DESBLOQUEAR_COMPONENTES_MOTOS_ADJUDICADAS,
            ]
        );
        if (!$permisoModulo) {
            return ['success' => false, 'message' => 'Tu usuario no tiene el permiso especial para desbloquear componentes.'];
        }
        $permiso = $this->db->queryOne(
            "SELECT nip_hash
             FROM adj_desbloqueo_componentes_nip
             WHERE id_usuario = :id_usuario
               AND activo = 1
             LIMIT 1",
            ['id_usuario' => $idUsuario]
        );
        if (!$permiso || !password_verify($nip, (string) ($permiso['nip_hash'] ?? ''))) {
            return ['success' => false, 'message' => 'NIP incorrecto o usuario sin permiso de desbloqueo.'];
        }

        return ['success' => true];
    }

    private function diagnosticoPermiteDesbloqueoS2(array $diag): bool
    {
        $bloqueos = array_values(array_filter(array_map('strval', $diag['bloqueos'] ?? [])));
        if (count($bloqueos) !== 1) {
            return false;
        }

        return stripos($bloqueos[0], 'No se pudo validar el credito en S2') !== false
            && empty($diag['legacy']['error'])
            && empty($diag['operacion'])
            && empty($diag['legacy']['dictamen']);
    }

    private function aplicarDesbloqueoS2SiProcede(array $diag, string $nip, int $idUsuario): array
    {
        if (!$this->diagnosticoPermiteDesbloqueoS2($diag)) {
            return [
                'success' => false,
                'message' => 'Este bloqueo no corresponde solo a S2; no se puede desbloquear por esta via.',
                'diagnostico' => $diag,
            ];
        }

        $validacionNip = $this->validarNipDesbloqueoComponentes($idUsuario, $nip);
        if (empty($validacionNip['success'])) {
            return $validacionNip + ['diagnostico' => $diag];
        }

        $diag['puede_simular'] = true;
        $diag['desbloqueo_s2'] = [
            'autorizado' => true,
            'id_usuario' => $idUsuario,
            'fecha' => $this->fechaHoraCdmx(),
            'motivo' => 'S2 no validado, pero credito libre en Segundometro/Tracking/Legacy.',
        ];
        $diag['bloqueos_originales'] = $diag['bloqueos'] ?? [];
        $diag['bloqueos'] = [];

        return ['success' => true, 'diagnostico' => $diag];
    }

    public function desbloquearValidacionS2DictamenWebMoto(int $idCredito, string $nip, int $idUsuario): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de credito invalido.'];
        }

        $diag = $this->diagnosticarDictamenWebMoto($idCredito);
        if (empty($diag['success'])) {
            return $diag;
        }

        $desbloqueo = $this->aplicarDesbloqueoS2SiProcede($diag, $nip, $idUsuario);
        if (empty($desbloqueo['success'])) {
            return $desbloqueo;
        }

        return [
            'success' => true,
            'message' => 'Validacion S2 desbloqueada para este credito. Ya puedes guardar o enviar la tarea.',
            'diagnostico' => $desbloqueo['diagnostico'],
        ];
    }

    public function desbloquearComponentesDictamenWebMoto(int $idCredito, string $nip, int $idUsuario, string $ip = ''): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de credito invalido.'];
        }
        $validacionNip = $this->validarNipDesbloqueoComponentes($idUsuario, $nip);
        if (empty($validacionNip['success'])) {
            return $validacionNip;
        }

        $legacyTasksDeleted = 0;
        $legacyAssignmentsDeleted = 0;
        $legacyDictumsDeleted = 0;
        $localOperacionesDeleted = 0;
        $localDetalle = [];

        $legacyDb = new DatabaseLegacy();
        $legacyTaskIds = [];
        $legacyRows = $legacyDb->queryAll(
            "SELECT id
             FROM tasks
             WHERE campaign_id = :campaign
               AND TRIM(CAST(credit_number AS CHAR)) = :credito",
            ['campaign' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS, 'credito' => (string) $idCredito]
        );
        foreach ($legacyRows ?: [] as $row) {
            $legacyTaskIds[] = (int) ($row['id'] ?? 0);
        }
        $legacyTaskIds = array_values(array_filter(array_unique($legacyTaskIds)));

        try {
            $legacyDb->beginTransaction();
            if ($legacyTaskIds !== []) {
                $in = $this->placeholdersIn('task', $legacyTaskIds, $params);
                $legacyDictumsDeleted = $legacyDb->CRUD("DELETE FROM dictums WHERE task_id IN ($in)", $params);
                $legacyAssignmentsDeleted = $legacyDb->CRUD("DELETE FROM task_user_assignments WHERE task_id IN ($in)", $params);
                $legacyTasksDeleted = $legacyDb->CRUD("DELETE FROM tasks WHERE id IN ($in)", $params);
            }
            $legacyDb->commit();
        } catch (\Throwable $e) {
            try {
                $legacyDb->rollback();
            } catch (\Throwable $ignored) {
            }
            return ['success' => false, 'message' => 'No se pudo borrar en Legacy: ' . $e->getMessage()];
        }

        try {
            $this->db->beginTransaction();
            $ops = $this->db->queryAll(
                'SELECT id FROM adj_operacion WHERE id_credito = :id_credito',
                ['id_credito' => $idCredito]
            );
            $opIds = [];
            foreach ($ops ?: [] as $op) {
                $opIds[] = (int) ($op['id'] ?? 0);
            }
            $opIds = array_values(array_filter(array_unique($opIds)));

            if ($opIds !== []) {
                $inOps = $this->placeholdersIn('op', $opIds, $paramsOps);

                if ($this->tablaExiste('adj_evidencia_rechazo_historial') && $this->tablaExiste('adj_evidencia')) {
                    $localDetalle['adj_evidencia_rechazo_historial'] = $this->db->CRUD(
                        "DELETE FROM adj_evidencia_rechazo_historial
                         WHERE id_evidencia IN (
                             SELECT id FROM adj_evidencia WHERE id_operacion IN ($inOps)
                         )",
                        $paramsOps
                    );
                }
                if ($this->tablaExiste('adj_evidencia')) {
                    $localDetalle['adj_evidencia'] = $this->db->CRUD(
                        "DELETE FROM adj_evidencia WHERE id_operacion IN ($inOps)",
                        $paramsOps
                    );
                }
                if ($this->tablaExiste('adj_dictamen')) {
                    $localDetalle['adj_dictamen'] = $this->db->CRUD(
                        "DELETE FROM adj_dictamen WHERE id_operacion IN ($inOps)",
                        $paramsOps
                    );
                }
                if ($this->tablaExiste('adj_historial_estatus')) {
                    $localDetalle['adj_historial_estatus'] = $this->db->CRUD(
                        "DELETE FROM adj_historial_estatus WHERE id_operacion IN ($inOps)",
                        $paramsOps
                    );
                }

                $localOperacionesDeleted = $this->db->CRUD(
                    "DELETE FROM adj_operacion WHERE id IN ($inOps)",
                    $paramsOps
                );
            }

            $this->db->CRUD(
                "INSERT INTO adj_desbloqueo_componentes_log
                    (id_usuario, id_credito, legacy_tasks_deleted, legacy_assignments_deleted,
                     legacy_dictums_deleted, local_operaciones_deleted, ip, created_at)
                 VALUES
                    (:id_usuario, :id_credito, :legacy_tasks_deleted, :legacy_assignments_deleted,
                     :legacy_dictums_deleted, :local_operaciones_deleted, :ip, :created_at)",
                [
                    'id_usuario' => $idUsuario,
                    'id_credito' => $idCredito,
                    'legacy_tasks_deleted' => $legacyTasksDeleted,
                    'legacy_assignments_deleted' => $legacyAssignmentsDeleted,
                    'legacy_dictums_deleted' => $legacyDictumsDeleted,
                    'local_operaciones_deleted' => $localOperacionesDeleted,
                    'ip' => substr($ip, 0, 45),
                    'created_at' => $this->fechaHoraCdmx(),
                ]
            );
            $this->db->commit();
        } catch (\Throwable $e) {
            try {
                $this->db->rollback();
            } catch (\Throwable $ignored) {
            }
            return [
                'success' => false,
                'message' => 'Legacy ya fue limpiado, pero fallo la limpieza local: ' . $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Componentes desbloqueados. El credito quedo listo para volver a dictaminar.',
            'deleted' => [
                'legacy_dictums' => $legacyDictumsDeleted,
                'legacy_task_user_assignments' => $legacyAssignmentsDeleted,
                'legacy_tasks' => $legacyTasksDeleted,
                'adj_operacion' => $localOperacionesDeleted,
            ],
            'local_detalle' => $localDetalle,
        ];
    }

    private function placeholdersIn(string $prefix, array $values, ?array &$params): string
    {
        $params = [];
        $ph = [];
        foreach (array_values($values) as $i => $value) {
            $key = $prefix . $i;
            $ph[] = ':' . $key;
            $params[$key] = $value;
        }
        return implode(',', $ph);
    }

    private function tablaExiste(string $tabla): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla)) {
            return false;
        }

        try {
            $row = $this->db->queryOne("SHOW TABLES LIKE :tabla", ['tabla' => $tabla]);
            return !empty($row);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function creditoEstaLiquidadoS2(string $status, $saldo = null): bool
    {
        $s = strtolower(trim($status));
        if ($s !== '' && (
            strpos($s, 'liquidado') !== false
            || strpos($s, 'liquidada') !== false
            || strpos($s, 'saldado') !== false
            || strpos($s, 'saldada') !== false
            || strpos($s, 'cerrado') !== false
        )) {
            return true;
        }

        return false;
    }

    public function simularDictamenWebMoto(array $data, int $idUsuarioSesion): array
    {
        $idCredito = (int) ($data['id_credito'] ?? 0);
        $idUsuarioLegacy = (int) ($data['id_usuario_legacy'] ?? 0);
        $fechaGestion = $this->normalizarFechaHoraCdmx($data['fecha_gestion'] ?? '');

        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de credito invalido.'];
        }
        if ($idUsuarioLegacy <= 0) {
            return ['success' => false, 'message' => 'Indica el id_usuario Legacy que recibira/dictaminara la tarea.'];
        }

        $faltantes = $this->validarCamposObligatoriosDictamenWeb($data);
        if ($faltantes !== []) {
            return [
                'success' => false,
                'message' => 'Faltan campos obligatorios: ' . implode(', ', $faltantes) . '.',
                'faltantes' => $faltantes,
            ];
        }

        $diag = $this->diagnosticarDictamenWebMoto($idCredito);
        if (empty($diag['success'])) {
            return $diag;
        }
        if (empty($diag['puede_simular'])) {
            if (!empty($data['desbloqueo_s2_autorizado'])) {
                $desbloqueo = $this->aplicarDesbloqueoS2SiProcede(
                    $diag,
                    trim((string) ($data['desbloqueo_s2_nip'] ?? '')),
                    $idUsuarioSesion
                );
                if (!empty($desbloqueo['success'])) {
                    $diag = $desbloqueo['diagnostico'];
                }
            }
        }
        if (empty($diag['puede_simular'])) {
            return [
                'success' => false,
                'message' => 'No se puede guardar: ' . implode(' ', $diag['bloqueos'] ?: ['Legacy no esta disponible.']),
                'diagnostico' => $diag,
            ];
        }

        try {
            $legacyDb = new DatabaseLegacy();
            $user = $legacyDb->queryOne(
                "SELECT id, name, external_id
                 FROM users
                 WHERE id = :id
                   AND deleted_at IS NULL
                 LIMIT 1",
                ['id' => $idUsuarioLegacy]
            );
            if (!$user) {
                return ['success' => false, 'message' => 'No existe usuario Legacy activo con ese id_usuario.'];
            }

            $datos = $this->datosBaseTaskDictamenWeb($idCredito, $data, $diag);
            $ahora = $fechaGestion;

            $legacyDb->beginTransaction();
            $taskId = $this->asegurarTaskLegacyDictamenWeb($legacyDb, $idCredito, $idUsuarioLegacy, $datos, $ahora);
            if ($taskId <= 0) {
                throw new \RuntimeException('No se pudo obtener el ID de la tarea Legacy.');
            }
            $this->asegurarAsignacionTaskLegacyDictamenWeb($legacyDb, $taskId, $idUsuarioLegacy, $ahora);

            $dictamenExistente = $legacyDb->queryOne(
                "SELECT id FROM dictums WHERE task_id = :task_id AND opciondictamen_id = :opcion ORDER BY id DESC LIMIT 1",
                ['task_id' => $taskId, 'opcion' => self::LEGACY_DICTAMEN_MOTO_ADJUDICADA]
            );
            if ($dictamenExistente) {
                $legacyDb->rollback();
                return [
                    'success' => false,
                    'message' => 'La tarea se creo/asigno, pero ya tenia dictamen 13. No se duplico.',
                    'task_id' => $taskId,
                    'dictum_id' => (int) $dictamenExistente['id'],
                ];
            }

            $formResponse = $this->formResponseLegacyDictamenWeb($data);
            $legacyDb->CRUD(
                "INSERT INTO dictums
                    (task_id, opciondictamen_id, form_response, created_at, updated_at, lat, lng,
                     valid_geofencing, user_id, handling_time, sent_at)
                 VALUES
                    (:task_id, :opcion, :form_response, :created_at, :updated_at, :lat, :lng,
                     0, :user_id, :handling_time, :sent_at)",
                [
                    'task_id' => $taskId,
                    'opcion' => self::LEGACY_DICTAMEN_MOTO_ADJUDICADA,
                    'form_response' => $formResponse,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                    'lat' => $datos['lat'],
                    'lng' => $datos['lng'],
                    'user_id' => $idUsuarioLegacy,
                    'handling_time' => max(1, (int) ($data['handling_time'] ?? 60)),
                    'sent_at' => $ahora,
                ]
            );
            $row = $legacyDb->queryOne('SELECT LAST_INSERT_ID() AS id');
            $dictumId = (int) ($row['id'] ?? 0);

            $legacyDb->CRUD(
                "UPDATE tasks
                 SET form_answered = :answered, status = :status, current_user_id = :user_id, updated_at = :updated_at
                 WHERE id = :task_id",
                [
                    'answered' => $formResponse,
                    'status' => 1,
                    'user_id' => $idUsuarioLegacy,
                    'updated_at' => $ahora,
                    'task_id' => $taskId,
                ]
            );

            $legacyDb->commit();

            $operacionPipeline = null;
            $pipelineWarning = '';
            try {
                $motosPipeline = new MotosAdjudicadas();
                $opRes = $motosPipeline->obtenerOCrearOperacion($idCredito, (string) ($datos['client_name'] ?? ''), $idUsuarioSesion, $ahora);
                if (!empty($opRes['success'])) {
                    $operacionPipeline = $opRes['detalle'] ?? null;
                } else {
                    $pipelineWarning = (string) ($opRes['message'] ?? 'No se pudo sincronizar adj_operacion.');
                }
            } catch (\Throwable $syncError) {
                $pipelineWarning = $syncError->getMessage();
            }

            return [
                'success' => true,
                'message' => $pipelineWarning === ''
                    ? 'Dictamen guardado directo en Legacy y sincronizado al pipeline.'
                    : 'Dictamen guardado directo en Legacy, pero no se pudo sincronizar al pipeline: ' . $pipelineWarning,
                'task_id' => $taskId,
                'dictum_id' => $dictumId,
                'operacion_pipeline' => $operacionPipeline,
                'pipeline_warning' => $pipelineWarning,
                'campaign_id' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS,
                'opciondictamen_id' => self::LEGACY_DICTAMEN_MOTO_ADJUDICADA,
                'id_usuario_legacy' => $idUsuarioLegacy,
                'id_usuario_sesion' => $idUsuarioSesion,
                'fecha_gestion' => $ahora,
            ];
        } catch (\Throwable $e) {
            try {
                if (isset($legacyDb)) {
                    $legacyDb->rollback();
                }
            } catch (\Throwable $ignored) {
            }

            return ['success' => false, 'message' => 'No se pudo guardar el dictamen: ' . $e->getMessage()];
        }
    }

    public function enviarCampaniaGestorLegacy(array $data, int $idUsuarioSesion): array
    {
        $idCredito = (int) ($data['id_credito'] ?? 0);
        $idUsuarioLegacy = (int) ($data['id_usuario_legacy'] ?? 0);

        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de credito invalido.'];
        }
        if ($idUsuarioLegacy <= 0) {
            return ['success' => false, 'message' => 'Indica el id_usuario Legacy que recibira la tarea.'];
        }

        $diag = $this->diagnosticarDictamenWebMoto($idCredito);
        if (empty($diag['success'])) {
            return $diag;
        }
        if (!empty($diag['legacy']['error'])) {
            return ['success' => false, 'message' => 'Legacy no esta disponible: ' . $diag['legacy']['error']];
        }
        if (!empty($diag['operacion'])) {
            return ['success' => false, 'message' => 'Ya existe adj_operacion para este credito; no se envio a gestor.'];
        }
        if (!empty($diag['legacy']['dictamen'])) {
            return ['success' => false, 'message' => 'Ya existe dictamen Legacy para este credito; no se envio a gestor.'];
        }
        if (empty($diag['puede_simular'])) {
            if (!empty($data['desbloqueo_s2_autorizado'])) {
                $desbloqueo = $this->aplicarDesbloqueoS2SiProcede(
                    $diag,
                    trim((string) ($data['desbloqueo_s2_nip'] ?? '')),
                    $idUsuarioSesion
                );
                if (!empty($desbloqueo['success'])) {
                    $diag = $desbloqueo['diagnostico'];
                }
            }
        }
        if (empty($diag['puede_simular'])) {
            return [
                'success' => false,
                'message' => 'No se puede enviar a gestor: ' . implode(' ', $diag['bloqueos'] ?: ['Validacion incompleta.']),
                'diagnostico' => $diag,
            ];
        }

        try {
            $legacyDb = new DatabaseLegacy();
            $user = $legacyDb->queryOne(
                "SELECT id, name, external_id
                 FROM users
                 WHERE id = :id
                   AND deleted_at IS NULL
                 LIMIT 1",
                ['id' => $idUsuarioLegacy]
            );
            if (!$user) {
                return ['success' => false, 'message' => 'No existe usuario Legacy activo con ese id_usuario.'];
            }

            $datos = $this->datosBaseTaskDictamenWeb($idCredito, $data, $diag);
            $fecha = $this->fechaHoraCdmx();

            $legacyDb->beginTransaction();
            $taskId = $this->asegurarTaskLegacyDictamenWeb($legacyDb, $idCredito, $idUsuarioLegacy, $datos, $fecha);
            if ($taskId <= 0) {
                throw new \RuntimeException('No se pudo obtener el ID de la tarea Legacy.');
            }
            $this->asegurarAsignacionTaskLegacyDictamenWeb($legacyDb, $taskId, $idUsuarioLegacy, $fecha);
            $legacyDb->commit();

            $asignacionSparta = $this->asignarCreditoSpartaDesdeUsuarioLegacy($user, $idCredito, $idUsuarioSesion);

            return [
                'success' => true,
                'message' => !empty($diag['legacy']['task'])
                    ? 'Campana reasignada al gestor en Legacy. La tarea existente quedo apuntando al gestor seleccionado.'
                    : 'Campana enviada al gestor en Legacy. Se creo la tarea para que cargue la gestion.',
                'task_id' => $taskId,
                'campaign_id' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS,
                'id_usuario_legacy' => $idUsuarioLegacy,
                'id_usuario_sesion' => $idUsuarioSesion,
                'fecha_envio' => $fecha,
                'asignacion_sparta' => $asignacionSparta,
            ];
        } catch (\Throwable $e) {
            try {
                if (isset($legacyDb)) {
                    $legacyDb->rollback();
                }
            } catch (\Throwable $ignored) {
            }

            return ['success' => false, 'message' => 'No se pudo enviar la campana a gestor: ' . $e->getMessage()];
        }
    }

    private function asignarCreditoSpartaDesdeUsuarioLegacy(array $usuarioLegacy, int $idCredito, int $idUsuarioSesion): array
    {
        $externalId = trim((string) ($usuarioLegacy['external_id'] ?? ''));
        if ($externalId === '') {
            return [
                'success' => false,
                'message' => 'No se pudo reflejar en Mis adjudicaciones: el usuario Legacy no tiene external_id.',
            ];
        }

        $persona = $this->db->queryOne(
            "SELECT id, TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre
             FROM persona
             WHERE TRIM(COALESCE(numero_empleado, '')) = :external_id
               AND COALESCE(estatus, 'Activo') <> 'Baja'
             ORDER BY id DESC
             LIMIT 1",
            ['external_id' => $externalId]
        );

        if (!$persona) {
            return [
                'success' => false,
                'message' => 'No se pudo reflejar en Mis adjudicaciones: no existe persona activa en Sparta con numero_empleado ' . $externalId . '.',
            ];
        }

        $res = $this->asignarCredito((int) $persona['id'], $idCredito, $idUsuarioSesion);
        if (empty($res['success']) && stripos((string) ($res['message'] ?? ''), 'ya est') !== false) {
            $res['informativo'] = true;
        }
        $res['id_persona'] = (int) $persona['id'];
        $res['persona'] = (string) ($persona['nombre'] ?? '');
        $res['external_id'] = $externalId;

        return $res;
    }

    public function usuariosActivosLegacy(): array
    {
        try {
            $legacyDb = new DatabaseLegacy();
            $rows = $legacyDb->queryAll(
                "SELECT id, name, username, external_id
                 FROM users
                 WHERE deleted_at IS NULL
                 ORDER BY name ASC, id ASC"
            );

            $datos = array_map(static function (array $row): array {
                $id = (int)($row['id'] ?? 0);
                $name = trim((string)($row['name'] ?? ''));
                $username = trim((string)($row['username'] ?? ''));
                $externalId = trim((string)($row['external_id'] ?? ''));
                $parts = [];
                if ($name !== '') {
                    $parts[] = $name;
                }
                if ($username !== '') {
                    $parts[] = '@' . $username;
                }
                if ($externalId !== '') {
                    $parts[] = 'Ext. ' . $externalId;
                }

                return [
                    'id' => $id,
                    'name' => $name,
                    'username' => $username,
                    'external_id' => $externalId,
                    'label' => '#' . $id . ' - ' . implode(' - ', $parts),
                ];
            }, $rows);

            $datos = array_values(array_filter($datos, static fn(array $row): bool => (int)($row['id'] ?? 0) > 0));

            return ['success' => true, 'datos' => $datos];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'No se pudieron cargar usuarios Legacy: ' . $e->getMessage(), 'datos' => []];
        }
    }

    private function validarCamposObligatoriosDictamenWeb(array $data): array
    {
        $campos = [
            'fecha_gestion' => 'Fecha y hora real de gestion',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'ano' => 'Ano',
            'color' => 'Color',
            'no_de_serie_vin' => 'VIN / Serie',
            'no_de_motor' => 'No. motor',
            'placas' => 'Placas',
            'kilometraje' => 'Kilometraje',
            'tiene_llave_fisica' => 'Llave fisica',
            'tiene_tarjeta_de_circulacion_en_fisico' => 'Tarjeta circulacion',
            'la_moto_tiene_placa_fisica' => 'Placa fisica',
            'donde_resguardaras_la_moto' => 'Lugar de resguardo',
            'estado_resguardo' => 'Estado resguardo',
            'ciudad_resguardo' => 'Ciudad / Municipio',
            'direccion_resguardo' => 'Direccion resguardo',
            'responsable_resguardo' => 'Responsable',
            'telefono_contacto' => 'Telefono contacto',
            'lat' => 'Latitud',
            'lng' => 'Longitud',
            'direccion' => 'Direccion task',
        ];

        $faltantes = [];
        foreach ($campos as $key => $label) {
            $valor = trim((string) ($data[$key] ?? ''));
            if ($valor === '') {
                $faltantes[] = $label;
            }
        }

        return $faltantes;
    }

    private function buscarCreditoSegundometroLocal(int $idCredito): ?array
    {
        try {
            $row = $this->dbSeg->queryOne(
                "SELECT Id_credito, Nombre_cliente, Status_credito, Dias_mora, Saldo_total_capital,
                        Bucket_Morosidad_Real, Sucursal
                 FROM tbl_segundometro_semana
                 WHERE Id_credito = :id
                 LIMIT 1",
                ['id' => $idCredito]
            );
            if ($row) {
                return $row;
            }

            return $this->dbSeg->queryOne(
                "SELECT MAX(Id_credito) AS Id_credito, MAX(Nombre_cliente) AS Nombre_cliente, MAX(Status_credito) AS Status_credito,
                        MAX(Dias_mora) AS Dias_mora, MAX(Saldo_total_capital) AS Saldo_total_capital,
                        MAX(Bucket_Morosidad_Real) AS Bucket_Morosidad_Real, MAX(Sucursal) AS Sucursal
                 FROM tbl_segundometro_histo
                 WHERE Id_credito = :id",
                ['id' => $idCredito]
            ) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function datosBaseTaskDictamenWeb(int $idCredito, array $data, array $diag): array
    {
        $seg = is_array($diag['segundometro'] ?? null) ? $diag['segundometro'] : [];
        $s2Credito = is_array($diag['s2']['credito'] ?? null) ? $diag['s2']['credito'] : [];
        $nombre = trim((string) ($data['nombre_cliente'] ?? $s2Credito['nombre_cliente'] ?? $seg['Nombre_cliente'] ?? ''));
        if ($nombre === '') {
            $nombre = 'Credito #' . $idCredito;
        }

        return [
            'client_name' => $nombre,
            'address' => trim((string) ($data['direccion'] ?? $s2Credito['direccion'] ?? '')),
            'lat' => is_numeric($data['lat'] ?? null) ? (string) $data['lat'] : null,
            'lng' => is_numeric($data['lng'] ?? null) ? (string) $data['lng'] : null,
        ];
    }

    private function asegurarTaskLegacyDictamenWeb(DatabaseLegacy $legacyDb, int $idCredito, int $idUsuarioLegacy, array $datos, string $fecha): int
    {
        $task = $legacyDb->queryOne(
            "SELECT id
             FROM tasks
             WHERE campaign_id = :campaign
               AND TRIM(CAST(credit_number AS CHAR)) = :credito
               AND deleted_at IS NULL
             ORDER BY id DESC
             LIMIT 1",
            ['campaign' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS, 'credito' => (string) $idCredito]
        );

        if ($task) {
            $taskId = (int) $task['id'];
            $legacyDb->CRUD(
                "UPDATE tasks
                 SET current_user_id = :user_id, updated_at = :updated_at
                 WHERE id = :task_id",
                ['user_id' => $idUsuarioLegacy, 'updated_at' => $fecha, 'task_id' => $taskId]
            );
            return $taskId;
        }

        $legacyDb->CRUD(
            "INSERT INTO tasks
                (campaign_id, current_user_id, client_name, credit_number, address, lat, lng,
                 form_data, form_answered, status, deleted_at, created_at, updated_at)
             VALUES
                (:campaign_id, :current_user_id, :client_name, :credit_number, :address, :lat, :lng,
                 :form_data, NULL, 0, NULL, :created_at, :updated_at)",
            [
                'campaign_id' => self::LEGACY_CAMP_MOTOS_ADJUDICADAS,
                'current_user_id' => $idUsuarioLegacy,
                'client_name' => $datos['client_name'],
                'credit_number' => (string) $idCredito,
                'address' => $datos['address'],
                'lat' => $datos['lat'],
                'lng' => $datos['lng'],
                'form_data' => $this->formDataLegacyDictamenWeb(),
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]
        );
        $row = $legacyDb->queryOne('SELECT LAST_INSERT_ID() AS id');
        return (int) ($row['id'] ?? 0);
    }

    private function asegurarAsignacionTaskLegacyDictamenWeb(DatabaseLegacy $legacyDb, int $taskId, int $idUsuarioLegacy, string $fecha): void
    {
        $asignacion = $legacyDb->queryOne(
            "SELECT id
             FROM task_user_assignments
             WHERE task_id = :task_id
               AND user_id = :user_id
               AND unassigned_at IS NULL
             ORDER BY id DESC
             LIMIT 1",
            ['task_id' => $taskId, 'user_id' => $idUsuarioLegacy]
        );
        if ($asignacion) {
            return;
        }

        $legacyDb->CRUD(
            "UPDATE task_user_assignments
             SET unassigned_at = :unassigned_at, updated_at = :updated_at
             WHERE task_id = :task_id
               AND user_id <> :user_id
               AND unassigned_at IS NULL",
            [
                'unassigned_at' => $fecha,
                'updated_at' => $fecha,
                'task_id' => $taskId,
                'user_id' => $idUsuarioLegacy,
            ]
        );

        $legacyDb->CRUD(
            "INSERT INTO task_user_assignments
                (task_id, user_id, assigned_at, unassigned_at, created_at, updated_at)
             VALUES
                (:task_id, :user_id, :assigned_at, NULL, :created_at, :updated_at)",
            [
                'task_id' => $taskId,
                'user_id' => $idUsuarioLegacy,
                'assigned_at' => $fecha,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]
        );
    }

    private function formDataLegacyDictamenWeb(): string
    {
        $fields = $this->camposLegacyDictamenWeb(false, []);
        $json = json_encode($fields, JSON_UNESCAPED_UNICODE);
        return json_encode($json, JSON_UNESCAPED_UNICODE) ?: (string) $json;
    }

    private function formResponseLegacyDictamenWeb(array $data): string
    {
        $fields = $this->camposLegacyDictamenWeb(true, $data);
        $json = json_encode($fields, JSON_UNESCAPED_UNICODE);
        return json_encode($json, JSON_UNESCAPED_UNICODE) ?: (string) $json;
    }

    private function camposLegacyDictamenWeb(bool $answered, array $data): array
    {
        $val = static fn(string $k): string => trim((string) ($data[$k] ?? ''));
        $select = static function (string $label, string $name, string $uuid, array $opts, string $selected) use ($answered): array {
            $values = [];
            foreach ($opts as $value => $text) {
                $values[] = ['label' => $text, 'value' => $value, 'selected' => (string) $value === (string) $selected];
            }
            return [
                'type' => 'select', 'required' => false, 'label' => $label, 'className' => 'form-control',
                'name' => $name, 'editable' => true, 'section' => 'questions', 'conditional' => false,
                'uuid' => $uuid, 'typeApp' => 'select', 'values' => $values, 'value' => $answered ? null : '',
                'error' => false,
            ];
        };
        $textarea = static function (string $label, string $name, string $uuid, string $value = '', string $section = 'questions'): array {
            return [
                'type' => 'textarea', 'required' => true, 'label' => $label, 'className' => 'form-control',
                'name' => $name, 'subtype' => 'textarea', 'editable' => true, 'section' => $section,
                'conditional' => false, 'uuid' => $uuid, 'typeApp' => 'textarea', 'value' => $value,
                'error' => false,
            ];
        };
        $media = static function (string $label, string $name, string $uuid, string $typeApp, string $formKey) use ($data, $val): array {
            $value = $val($formKey);
            $out = [
                'type' => 'text', 'required' => true, 'label' => $label, 'className' => 'form-control',
                'name' => $name, 'subtype' => 'text', 'editable' => true, 'section' => 'questions',
                'conditional' => false, 'uuid' => $uuid, 'typeApp' => $typeApp, 'value' => $value,
                'error' => false,
            ];
            if ($value !== '') {
                $out['status'] = 'uploaded';
                $firebasePath = $val($formKey . '_firebasePath');
                $localFile = $val($formKey . '_localFile');
                $fileName = $val($formKey . '_fileName');
                $out['firebasePath'] = $firebasePath !== '' ? $firebasePath : (parse_url($value, PHP_URL_PATH) ?: $value);
                $out['localFile'] = $localFile !== '' ? $localFile : $value;
                $out['fileName'] = $fileName !== '' ? $fileName : basename(parse_url($value, PHP_URL_PATH) ?: $value);
            }
            return $out;
        };

        $resguardo = $val('donde_resguardaras_la_moto') ?: 'cedis-__SPARTA_SECRET_REDACTED__';

        return [
            $select('Tiene llave fisica?', 'tiene_llave_fisica', 'dfcd6ca6-f1ae-4807-a462-3d9c346f0b16', ['si' => 'Si', 'no' => 'No'], $val('tiene_llave_fisica') ?: 'si'),
            $select('Tiene tarjeta de circulacion en fisico?', 'tiene_tarjeta_de_circulacion_en_fisico', '5446560b-ac6a-4827-9c6b-cc3c74954a40', ['si' => 'Si', 'no' => 'No'], $val('tiene_tarjeta_de_circulacion_en_fisico') ?: 'si'),
            $select('La moto tiene placa fisica?', 'la_moto_tiene_placa_fisica', 'b07276c9-cc0c-42ac-b38c-adfefc42913e', ['si' => 'Si', 'no' => 'No'], $val('la_moto_tiene_placa_fisica') ?: 'si'),
            $textarea('Marca', 'marca', '0d57879b-69c0-41f1-bb7c-cfe1833aa1aa', $val('marca'), 'customer'),
            $textarea('Modelo', 'modelo', '7f896bdd-3c9c-40e8-8c13-4b3925b1c8bc', $val('modelo'), 'customer'),
            $textarea('Ano', 'ano', '0743a74d-9c7e-4512-a5ed-9cd8a5ad60b8', $val('ano'), 'customer'),
            $textarea('Color', 'color', '5dfd8e41-7468-4ea4-a9dc-293a357f8294', $val('color'), 'customer'),
            $textarea('No. de Serie VIN', 'no_de_serie_vin', 'c692709e-6f49-434f-bdbb-85d0186098a7', $val('no_de_serie_vin'), 'customer'),
            $textarea('No. de Motor', 'no_de_motor', '46d9e3a1-7ee4-4230-836a-a22f7e2524fd', $val('no_de_motor'), 'customer'),
            $textarea('Placas', 'placas', 'b2f01f91-b1fa-4c0a-a0ca-5e22f3233587', $val('placas'), 'customer'),
            $textarea('Kilometraje', 'kilometraje', '2fdcef10-973b-408e-a0f0-46aceef4afab', $val('kilometraje')),
            $select('Donde resguardaras la moto?', 'donde_resguardaras_la_moto', '73eddd54-4de5-4054-babd-5f5766b18703', [
                'cedis-__SPARTA_SECRET_REDACTED__' => 'CEDIS Maxikash',
                'centro-de-acopio' => 'Centro de acopio',
                'agencia' => 'Agencia',
                'otro' => 'Otro',
            ], $resguardo),
            $textarea('Estado de lugar de resguardo', 'estado_de_lugar_de_resguardo_ejemplo_ciudad_de_mex', '3666bb60-bb6b-460e-9bbf-ddea362801af', $val('estado_resguardo')),
            $textarea('Ciudad / Municipio de lugar de Resguardo', 'ciudad_municipio_de_lugar_de_resguardo', '68bea6ea-8e99-4588-b20a-dcc2c959e8fb', $val('ciudad_resguardo')),
            $textarea('Calle y numero de lugar de resguardo', 'calle_y_numero_de_lugar_de_resguardo', 'f7de67b6-27bd-4bf9-9839-0f76c349fb72', $val('direccion_resguardo')),
            $textarea('Responsable de Resguardo', 'responsable_de_resguardo', '688c766b-d427-4c65-bef3-66e07ba1a931', $val('responsable_resguardo')),
            ['type' => 'number', 'required' => true, 'label' => 'Telefono de contacto', 'className' => 'form-control', 'name' => 'telefono_de_contacto', 'subtype' => 'number', 'editable' => true, 'section' => 'questions', 'conditional' => false, 'uuid' => '6ffb46d9-a417-4370-b531-efb5167f56fd', 'typeApp' => 'number', 'value' => $val('telefono_contacto'), 'error' => false],
            $media('Foto dacion hoja 1', 'foto_dacion_hoja_1', 'c90bb291-0701-4d2a-a18e-8d53b5fbbb27', 'photo', 'foto_dacion_hoja_1'),
            $media('Foto dacion hoja 2', 'foto_dacion_hoja_2', '2a939c63-df8d-4f81-bb2d-7feb2c4bcb20', 'photo', 'foto_dacion_hoja_2'),
            $media('Foto de Tacometro', 'text-1778722329133-0', 'be225e92-cc45-42b8-98a3-27f8a9244f14', 'photo', 'foto_tacometro'),
            $media('Foto de Numero de Serie', 'foto_de_numero_de_serie_foto_legible_donde_se_lea_', 'a0e34323-191e-40a9-9f47-0e03338be6a3', 'photo', 'foto_serie'),
            $media('Foto frontal de la moto', 'foto_frontal_de_la_moto_la_foto_debe_estar_visible', 'f8efc9e4-3d18-4abc-b222-2e8324844bd7', 'photo', 'foto_frontal'),
            $media('Foto trasera de la moto', 'foto_trasera_de_la_moto_la_foto_debe_ser_visible_t', 'abb8ebb0-713e-4f3a-8223-539c8bebd687', 'photo', 'foto_trasera'),
            $media('Foto lateral izquierda de la moto', 'foto_lateral_izquierda_de_la_moto_foto_legible_de_', 'a572d294-6fee-495c-a9c2-52e5f1ead6d9', 'photo', 'foto_lateral_izq'),
            $media('Foto lateral derecha de la moto', 'foto_lateral_derecha_de_la_moto_foto_legible_de_pr', '750b170c-8afe-4a4f-bbb5-a7cc68541255', 'photo', 'foto_lateral_der'),
            $media('Foto de check list', 'foto_de_check_list', 'dddb74c9-578b-41b8-b459-50fab0c209c8', 'photo', 'foto_checklist'),
            $media('Inspeccion 360 de Moto', 'inspeccion_360_de_moto_el_video_debe_evidenciar_el', '086f9488-15c5-4695-99cf-26184524f739', 'video', 'video_360'),
            $media('Video cliente de acuerdo', 'video_cliente_de_acuerdo', '7697e3ac-aa9e-48e5-825c-b4ea4f0afe21', 'video', 'video_cliente_acuerdo'),
            $media('Video vuelta de prueba', 'video_vuelta_de_prueba', '4fce9cf9-d409-4387-842f-059ae483af00', 'video', 'video_vuelta_prueba'),
            $select('CONTACTO', 'contacto', '1dd4fc5e-1257-463e-a465-e94fad0641b6', [
                'campo' => 'CAMPO',
                'telefono' => 'TELEFONO',
                'whatsapp' => 'WHATSAPP',
            ], 'campo'),
            $select('Dictamen', 'dictamen', '5b0aa81d-eb7b-445f-a8d7-c77a5f1e4520', [
                '0' => 'Atendido',
                '3' => 'Pago recibido',
                '6' => 'Contacto con Tercero',
                '9' => 'No contesta la llamada',
                '13' => 'Moto adjudicada',
                '14' => 'Convenio Pago Parcial',
                '15' => 'Promesa de Pago',
                '17' => 'Cambio de Domicilio (Renta/ Familiares)',
                '19' => 'Pago No Identificado',
                '21' => 'Incontactable',
                '22' => 'Negativa de pago',
                '23' => 'Ilocalizable',
                '24' => 'Prestanombre',
                '25' => 'Moto Siniestrada',
                '27' => 'Cliente fallecido',
                '29' => 'Usurpacion',
                '30' => 'Domicilio no localizado',
                '31' => 'Acceso Restringido',
                '32' => 'Zona de dificil acceso',
                '33' => 'Zona de riesgo',
                '34' => 'Agresion (TT o terceros)',
                '35' => 'Negativo expresa de pago',
                '36' => 'Moto no localizada',
            ], (string) self::LEGACY_DICTAMEN_MOTO_ADJUDICADA),
            $select('MEDIO DE CONTACTO (CAMPO)', 'medio_de_contacto_campo', 'dea75234-136d-4b8b-9bfc-bbf56ed50a72', [
                'domicilio-del-cliente' => 'DOMICILIO DEL CLIENTE',
                'geolocalizacion' => 'GEOLOCALIZACION',
                'domicilio-laboral' => 'DOMICILIO LABORAL',
                'domicilio-app-sabuesos' => 'DOMICILIO APP (SABUESOS)',
            ], 'domicilio-del-cliente'),
        ];
    }

    // =========================================================================
    // COMENTARIOS
    // =========================================================================

    /**
     * Guarda/actualiza el comentario interno de un responsable.
     * Usa la columna `direccion` de personal_adjudicacion como campo de notas
     * mientras no exista una columna dedicada; cambiar si se agrega `comentarios`.
     */
    public function guardarComentarios(int $idPersona, string $comentario): bool
    {
        $row = $this->db->queryOne(
            'SELECT id FROM personal_adjudicacion WHERE id_persona = :idPersona LIMIT 1',
            ['idPersona' => $idPersona]
        );

        if (!$row) {
            return false;
        }

        $n = $this->db->CRUD(
            'UPDATE personal_adjudicacion SET direccion = :comentario WHERE id = :id',
            ['comentario' => $comentario, 'id' => (int) $row['id']]
        );

        return $n > 0;
    }

    // =========================================================================
    // TELÉFONOS ADICIONALES DEL GESTOR
    // =========================================================================

    /** Retorna los teléfonos activos registrados para un gestor. */
    public function obtenerTelefonosGestor(int $idPersona): array
    {
        return $this->db->queryAll(
            'SELECT id, numero FROM asigna_telefono_persona
             WHERE id_persona = :idPersona AND estatus = 1
             ORDER BY id',
            ['idPersona' => $idPersona]
        ) ?: [];
    }

    /**
     * Registra un nuevo teléfono para el gestor (máximo 10 activos).
     * @return array{success:bool, message:string}
     */
    public function registrarTelefonoGestor(int $idPersona, string $numero): array
    {
        $total = (int) ($this->db->queryOne(
            'SELECT COUNT(*) AS cnt FROM asigna_telefono_persona
             WHERE id_persona = :idPersona AND estatus = 1',
            ['idPersona' => $idPersona]
        )['cnt'] ?? 0);

        if ($total >= 10) {
            return ['success' => false, 'message' => 'Límite de 10 teléfonos alcanzado.'];
        }

        $n = $this->db->CRUD(
            'INSERT INTO asigna_telefono_persona (id_persona, numero) VALUES (:idPersona, :numero)',
            ['idPersona' => $idPersona, 'numero' => $numero]
        );

        return $n > 0
            ? ['success' => true,  'message' => 'Teléfono registrado correctamente.']
            : ['success' => false, 'message' => 'No se pudo guardar el teléfono.'];
    }

    /** Elimina (desactiva) un teléfono adicional. */
    public function eliminarTelefonoGestor(int $idTelefono, int $idPersona): array
    {
        $n = $this->db->CRUD(
            'UPDATE asigna_telefono_persona SET estatus = 0
             WHERE id = :id AND id_persona = :idPersona',
            ['id' => $idTelefono, 'idPersona' => $idPersona]
        );

        return $n > 0
            ? ['success' => true,  'message' => 'Teléfono eliminado.']
            : ['success' => false, 'message' => 'Teléfono no encontrado.'];
    }

    // =========================================================================
    // CORREOS ADICIONALES DEL GESTOR
    // =========================================================================

    /** Retorna los correos activos registrados para un gestor. */
    public function obtenerCorreosGestor(int $idPersona): array
    {
        return $this->db->queryAll(
            'SELECT id, correo FROM asigna_correo_persona
             WHERE id_persona = :idPersona AND estatus = 1
             ORDER BY id',
            ['idPersona' => $idPersona]
        ) ?: [];
    }

    /**
     * Registra un nuevo correo para el gestor (máximo 10 activos).
     * @return array{success:bool, message:string}
     */
    public function registrarCorreoGestor(int $idPersona, string $correo): array
    {
        $total = (int) ($this->db->queryOne(
            'SELECT COUNT(*) AS cnt FROM asigna_correo_persona
             WHERE id_persona = :idPersona AND estatus = 1',
            ['idPersona' => $idPersona]
        )['cnt'] ?? 0);

        if ($total >= 10) {
            return ['success' => false, 'message' => 'Límite de 10 correos alcanzado.'];
        }

        $n = $this->db->CRUD(
            'INSERT INTO asigna_correo_persona (id_persona, correo) VALUES (:idPersona, :correo)',
            ['idPersona' => $idPersona, 'correo' => $correo]
        );

        return $n > 0
            ? ['success' => true,  'message' => 'Correo registrado correctamente.']
            : ['success' => false, 'message' => 'No se pudo guardar el correo.'];
    }

    /** Elimina (desactiva) un correo adicional. */
    public function eliminarCorreoGestor(int $idCorreo, int $idPersona): array
    {
        $n = $this->db->CRUD(
            'UPDATE asigna_correo_persona SET estatus = 0
             WHERE id = :id AND id_persona = :idPersona',
            ['id' => $idCorreo, 'idPersona' => $idPersona]
        );

        return $n > 0
            ? ['success' => true,  'message' => 'Correo eliminado.']
            : ['success' => false, 'message' => 'Correo no encontrado.'];
    }

    // =========================================================================
    // PERSONAS (para registro de nuevos gestores)
    // =========================================================================

    /**
     * Todas las personas del catálogo, para el desplegable de registro de gestores.
     */
    public function obtenerTodasPersonas(): array
    {
        $predUn = UsuarioFantasmaReporteria::sqlPredicadoExcluirUserNameSinAlias();
        $query = <<<SQL
        SELECT
            id,
            TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre_completo,
            telefono_uno AS telefono,
            correo
        FROM persona
        WHERE ({$predUn})
        ORDER BY nombres, apellidop, apellidom
        SQL;

        return $this->db->queryAll($query) ?: [];
    }

    /**
     * Registra una persona como gestor de adjudicación.
     * Devuelve error si ya existe un registro previo (cualquier estatus).
     *
     * @return array{success:bool, message:string}
     */
    public function registrarGestor(int $idPersona, string $tel1, string $correo1): array
    {
        $existe = $this->db->queryOne(
            'SELECT id FROM personal_adjudicacion WHERE id_persona = :idPersona LIMIT 1',
            ['idPersona' => $idPersona]
        );

        if ($existe) {
            return ['success' => false, 'message' => 'Esta persona ya está registrada como gestor de adjudicación.'];
        }

        $n = $this->db->CRUD(
            "INSERT INTO personal_adjudicacion (id_persona, estatus, fecha_alta, numero_tel1, correo_1)
             VALUES (:idPersona, 'Activo', :fechaAlta, :tel1, :correo1)",
            [
                'idPersona' => $idPersona,
                'fechaAlta' => $this->fechaHoraCdmx(),
                'tel1'      => $tel1,
                'correo1'   => $correo1,
            ]
        );

        if ($n > 0) {
            return ['success' => true, 'message' => 'Gestor registrado correctamente.'];
        }

        return ['success' => false, 'message' => 'No se pudo registrar el gestor.'];
    }

    /**
     * Actualiza el campo numero_tel1 en personal_adjudicacion para el gestor dado.
     */
    public function actualizarTelefono1(int $idPersona, string $numero): array
    {
        $n = $this->db->CRUD(
            'UPDATE personal_adjudicacion SET numero_tel1 = :numero WHERE id_persona = :idPersona',
            ['numero' => $numero, 'idPersona' => $idPersona]
        );

        return $n > 0
            ? ['success' => true,  'message' => 'Teléfono actualizado.']
            : ['success' => false, 'message' => 'No se encontró el registro del gestor.'];
    }

    /**
     * Actualiza el campo correo_1 en personal_adjudicacion para el gestor dado.
     */
    public function actualizarCorreo1(int $idPersona, string $correo): array
    {
        $n = $this->db->CRUD(
            'UPDATE personal_adjudicacion SET correo_1 = :correo WHERE id_persona = :idPersona',
            ['correo' => $correo, 'idPersona' => $idPersona]
        );

        return $n > 0
            ? ['success' => true,  'message' => 'Correo actualizado.']
            : ['success' => false, 'message' => 'No se encontró el registro del gestor.'];
    }
}
