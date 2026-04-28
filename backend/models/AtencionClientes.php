<?php
namespace Models;

use Core\Database;

/**
 * Modelo para el módulo de Atención a Clientes (adjudicaciones).
 *
 * Tabla requerida (crear antes de usar este módulo):
 * ─────────────────────────────────────────────────────────────────────
 * CREATE TABLE adj_dictamen (
 *     id                  INT          AUTO_INCREMENT PRIMARY KEY,
 *     id_operacion        INT          NOT NULL,
 *     llamada_a           VARCHAR(100) NOT NULL DEFAULT '',
 *     numero              VARCHAR(30)  NOT NULL DEFAULT '',
 *     persona_contactada  VARCHAR(200) NOT NULL DEFAULT '',
 *     tipo_contacto       VARCHAR(100) NOT NULL DEFAULT '',
 *     resultado           VARCHAR(100) NOT NULL DEFAULT '',
 *     dictamen            VARCHAR(100) NOT NULL DEFAULT '',
 *     plataforma          VARCHAR(100) DEFAULT NULL,
 *     comentarios         TEXT         DEFAULT NULL,
 *     id_usuario          INT          DEFAULT NULL,
 *     fecha_alta          DATETIME     NOT NULL,
 *     INDEX idx_id_operacion (id_operacion)
 * );
 * ─────────────────────────────────────────────────────────────────────
 */
class AtencionClientes
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    private function fechaHoraCdmx(): string
    {
        $dt = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Evita duplicar filas si hay más de un registro activo en asigna_creditos_adjudicacion por crédito.
     */
    private function sqlJoinUnaAsignacionActivaPorCredito(): string
    {
        return <<<'SQL'
LEFT JOIN asigna_creditos_adjudicacion aca
       ON aca.id = (
            SELECT a2.id
            FROM asigna_creditos_adjudicacion a2
            WHERE a2.id_credito = o.id_credito
              AND a2.estatus = '1'
            ORDER BY a2.id DESC
            LIMIT 1
        )
LEFT JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
LEFT JOIN persona per              ON per.id = pa.id_persona
SQL;
    }

    // =========================================================================
    // ENTRANTES  (estatus = 'Retenciones')
    // =========================================================================

    public function obtenerEntrantes(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE o.estatus = 'Retenciones'
        ORDER BY o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // 2.- EVIDENCIAS — listas por estatus de pipeline
    // =========================================================================

    /**
     * Lista operaciones por estatus de pipeline (shape uniforme para evidencias y recuperación).
     */
    private function listarOperacionesAdjPorEstatus(string $estatus): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            (SELECT COUNT(*) FROM adj_evidencia e WHERE e.id_operacion = o.id) AS evidencias_count,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE o.estatus = :estatus
        ORDER BY o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql, ['estatus' => $estatus]) ?: [];
    }

    /**
     * Pestaña Bandeja de entrada — operaciones aún no enviadas desde Atención.
     * Si la columna atencion_envio_validado no existe, usa bitácora como respaldo.
     */
    public function obtenerRecibidos(): array
    {
        $ma = new MotosAdjudicadas();
        $tieneEnvio = $ma->adjOperacionTieneColumnaEnvioAtencion();

        $where = $tieneEnvio
            ? "(o.estatus IN ('Recibido', 'en_transito') OR (o.estatus = 'Procesando IA' AND IFNULL(o.atencion_envio_validado, 0) = 0))"
            : "(o.estatus IN ('Recibido', 'en_transito') OR (o.estatus = 'Procesando IA' AND NOT EXISTS (
                    SELECT 1
                    FROM adj_bitacora b
                    WHERE b.id_operacion = o.id
                      AND b.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
                )))";

        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            (SELECT COUNT(*) FROM adj_evidencia e WHERE e.id_operacion = o.id) AS evidencias_count,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE {$where}
        ORDER BY o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    /**
     * Misma lista que Evidencias → Aprobados: Procesando IA tras «Enviar evidencias validadas».
     * Usada también por 3.- Recuperación → Bandeja de entrada.
     */
    private function listarOperacionesAprobadasEvidenciasAtencion(): array
    {
        $ma = new MotosAdjudicadas();
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        if (!$ma->adjOperacionTieneColumnaEnvioAtencion()) {
            $sql = <<<SQL
            SELECT
                o.id,
                o.folio,
                o.id_credito,
                o.nombre_cliente,
                o.telefono_contacto,
                o.estatus,
                o.saldo_capital,
                o.adeudo_total,
                DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
                (SELECT COUNT(*) FROM adj_evidencia e WHERE e.id_operacion = o.id) AS evidencias_count,
                TRIM(CONCAT_WS(' ',
                    per.nombres,
                    per.segundo_nombre,
                    per.apellidop,
                    per.apellidom
                )) AS gestor_nombre,
                DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
            FROM adj_operacion o
            {$joinAsig}
            WHERE o.estatus = 'Procesando IA'
              AND EXISTS (
                    SELECT 1
                    FROM adj_bitacora b
                    WHERE b.id_operacion = o.id
                      AND b.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
              )
            ORDER BY o.fecha_alta ASC
            SQL;

            return $this->db->queryAll($sql) ?: [];
        }
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            (SELECT COUNT(*) FROM adj_evidencia e WHERE e.id_operacion = o.id) AS evidencias_count,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE o.estatus = 'Procesando IA'
          AND IFNULL(o.atencion_envio_validado, 0) = 1
        ORDER BY o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    /**
     * Pestaña Aprobados — solo operaciones enviadas con «Enviar evidencias validadas».
     * Si la columna atencion_envio_validado no existe, usa bitácora como respaldo.
     */
    public function obtenerEvidenciasAprobadas(): array
    {
        return $this->listarOperacionesAprobadasEvidenciasAtencion();
    }

    /**
     * Pestaña Correcciones — operaciones en etapa Revisión Recuperaciones.
     */
    public function obtenerEvidenciasCorrecciones(): array
    {
        return $this->listarOperacionesAdjPorEstatus('Revisión Recuperaciones');
    }

    /**
     * 3.- Recuperación — etapas posteriores a la validación de evidencias en pipeline.
     */
    public function obtenerRecuperacionCierreDocumentado(): array
    {
        return $this->listarOperacionesAdjPorEstatus('Cierre Documentado');
    }

    /**
     * 5.- Recepción — bandeja: operaciones ya en etapa Recepción y también las que siguen en
     * Cierre documentado pero ya tienen dictamen (mismas que Dictaminado en vista 4), para gestionar
     * ingreso a almacén antes del cierre formal en S2 si aplica.
     */
    public function obtenerRecuperacionRecepcion(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            (SELECT COUNT(*) FROM adj_evidencia e WHERE e.id_operacion = o.id) AS evidencias_count,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE (
            o.estatus = 'Recepción'
            OR (
                o.estatus = 'Cierre Documentado'
                AND EXISTS (
                    SELECT 1 FROM adj_dictamen d WHERE d.id_operacion = o.id
                )
            )
        )
        ORDER BY o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    /**
     * 3.- Recuperación — Bandeja de entrada: mismas operaciones que Evidencias → Aprobados
     * (Procesando IA con envío validado desde Atención), no solo estatus en_transito.
     */
    public function obtenerRecuperacionEnTransito(): array
    {
        return $this->listarOperacionesAprobadasEvidenciasAtencion();
    }

    /**
     * Pestaña Dictaminado en vistas 3–5: operaciones en la etapa indicada con último registro en adj_dictamen.
     */
    public function obtenerOperacionesDictamenPorEstatusPipeline(string $estatus): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            d.llamada_a,
            d.numero,
            d.persona_contactada,
            d.tipo_contacto,
            d.resultado,
            d.dictamen,
            d.plataforma,
            d.comentarios,
            DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre
        FROM adj_operacion o
        INNER JOIN adj_dictamen d ON d.id = (
            SELECT d2.id
            FROM adj_dictamen d2
            WHERE d2.id_operacion = o.id
            ORDER BY d2.fecha_alta DESC, d2.id DESC
            LIMIT 1
        )
        {$joinAsig}
        WHERE o.estatus = :estatus
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql, ['estatus' => $estatus]) ?: [];
    }

    /**
     * 3.- Recuperación — Pestaña Dictaminado: operaciones en tránsito o ya enviadas a Cartera (Cierre documentado).
     * Incluye las que aún no tienen fila en adj_dictamen (LEFT JOIN al último dictamen si existe).
     */
    public function obtenerDictaminadosRecuperacionLista(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            d.llamada_a,
            d.numero,
            d.persona_contactada,
            d.tipo_contacto,
            d.resultado,
            d.dictamen,
            d.plataforma,
            d.comentarios,
            DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre
        FROM adj_operacion o
        LEFT JOIN adj_dictamen d ON d.id = (
            SELECT d2.id
            FROM adj_dictamen d2
            WHERE d2.id_operacion = o.id
            ORDER BY d2.fecha_alta DESC, d2.id DESC
            LIMIT 1
        )
        {$joinAsig}
        WHERE o.estatus IN ('en_transito', 'Cierre Documentado')
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // DICTAMINADOS  (estatus = 'en_transito' o 'cancelado')
    // =========================================================================

    public function obtenerDictaminados(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            d.llamada_a,
            d.numero,
            d.persona_contactada,
            d.tipo_contacto,
            d.resultado,
            d.dictamen,
            d.plataforma,
            d.comentarios,
            DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre
        FROM adj_operacion o
        LEFT JOIN adj_dictamen d ON d.id_operacion = o.id
        {$joinAsig}
        WHERE o.estatus IN ('en_transito', 'cancelado')
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // REGISTRAR DICTAMEN
    // =========================================================================

    /**
     * Guarda el dictamen en adj_dictamen y actualiza el estatus de adj_operacion.
     * Estatus resultante:
     *   'Autorizado para recolección'  → 'en_transito'
     *   'Cancelado, promesa de pago'   → 'cancelado'
     *   otros valores                  → sin cambio de estatus
     */
    public function registrarDictamen(int $idOperacion, array $data, int $idUsuario): array
    {
        $op = $this->db->queryOne(
            "SELECT id, estatus FROM adj_operacion WHERE id = :id",
            ['id' => $idOperacion]
        );

        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }
        if ($op['estatus'] !== 'Retenciones') {
            return ['success' => false, 'message' => 'La operación ya fue dictaminada.'];
        }

        $dictamen     = trim($data['dictamen'] ?? '');
        $ahora        = $this->fechaHoraCdmx();
        $nuevoEstatus = null;

        if ($dictamen === 'Autorizado para recolección') {
            $nuevoEstatus = 'en_transito';
        } elseif ($dictamen === 'Cancelado, promesa de pago') {
            $nuevoEstatus = 'cancelado';
        }

        $this->db->CRUD(
            "INSERT INTO adj_dictamen
                (id_operacion, llamada_a, numero, persona_contactada, tipo_contacto,
                 resultado, dictamen, plataforma, comentarios, id_usuario, fecha_alta)
             VALUES
                (:id_operacion, :llamada_a, :numero, :persona_contactada, :tipo_contacto,
                 :resultado, :dictamen, :plataforma, :comentarios, :id_usuario, :fecha_alta)",
            [
                'id_operacion'       => $idOperacion,
                'llamada_a'          => trim($data['llamada_a']           ?? ''),
                'numero'             => trim($data['numero']              ?? ''),
                'persona_contactada' => trim($data['persona_contactada']  ?? ''),
                'tipo_contacto'      => trim($data['tipo_contacto']       ?? ''),
                'resultado'          => trim($data['resultado']           ?? ''),
                'dictamen'           => $dictamen,
                'plataforma'         => trim($data['plataforma']          ?? '') ?: null,
                'comentarios'        => trim($data['comentarios']         ?? '') ?: null,
                'id_usuario'         => $idUsuario ?: null,
                'fecha_alta'         => $ahora,
            ]
        );

        if ($nuevoEstatus !== null) {
            $this->db->CRUD(
                "UPDATE adj_operacion
                    SET estatus = :estatus, fecha_actualizacion = :fecha
                  WHERE id = :id",
                ['estatus' => $nuevoEstatus, 'fecha' => $ahora, 'id' => $idOperacion]
            );

            $this->db->CRUD(
                "INSERT INTO adj_historial_estatus
                    (id_operacion, estatus_anterior, estatus_nuevo, id_usuario, fecha)
                 VALUES (:id_op, 'Retenciones', :nuevo, :id_usr, :fecha)",
                [
                    'id_op'  => $idOperacion,
                    'nuevo'  => $nuevoEstatus,
                    'id_usr' => $idUsuario ?: null,
                    'fecha'  => $ahora,
                ]
            );
        }

        return ['success' => true, 'estatus_nuevo' => $nuevoEstatus];
    }

    // =========================================================================
    // OBTENER DICTAMEN DE UNA OPERACIÓN
    // =========================================================================

    public function obtenerDictamen(int $idOperacion): ?array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        return $this->db->queryOne(
            "SELECT d.*,
                    DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                    o.id_credito,
                    o.folio,
                    o.nombre_cliente,
                    o.estatus AS op_estatus,
                    TRIM(CONCAT_WS(' ',
                        per.nombres,
                        per.segundo_nombre,
                        per.apellidop,
                        per.apellidom
                    )) AS gestor_nombre
             FROM adj_dictamen d
             JOIN adj_operacion o ON o.id = d.id_operacion
             {$joinAsig}
             WHERE d.id_operacion = :id
             ORDER BY d.fecha_alta DESC
             LIMIT 1",
            ['id' => $idOperacion]
        );
    }
}
