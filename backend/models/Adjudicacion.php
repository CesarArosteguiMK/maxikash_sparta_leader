<?php

namespace Models;

use Core\Model;
use Core\Database;

class Adjudicacion extends Model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
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
                 pa.numero_tel1, pa.correo_1, pa.estatus
        ORDER BY nombre_completo
        SQL;

        return $this->db->queryAll($query) ?: [];
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
                'alta'          => $usuarioAlta,
            ]
        );

        if ($n > 0) {
            return ['success' => true, 'message' => 'Crédito asignado correctamente.'];
        }

        return ['success' => false, 'message' => 'No se pudo registrar la asignación.'];
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
            TRIM(CONCAT_WS(' ', per_alta.nombres, per_alta.apellidop)) AS asignado_por,
            aca.id                                                  AS id_asignacion
        FROM asigna_creditos_adjudicacion aca
        INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
        LEFT JOIN persona per_alta ON per_alta.id = aca.alta
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
            'Token: __SPARTA_TOKEN_REDACTED__',
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
        ];

        // ── 3. Asignación activa en adjudicación ──────────────────────────────
        $asignacion = $this->db->queryOne(
            <<<SQL
            SELECT
                aca.id,
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
            GROUP BY aca.id, aca.estatus, aca.fecha_alta,
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
}
