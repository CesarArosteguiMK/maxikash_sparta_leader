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

    // =========================================================================
    // ENTRANTES  (estatus = 'Retenciones')
    // =========================================================================

    public function obtenerEntrantes(): array
    {
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
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
        LEFT JOIN asigna_creditos_adjudicacion aca
               ON aca.id_credito = o.id_credito AND aca.estatus = '1'
        LEFT JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
        LEFT JOIN persona per              ON per.id = pa.id_persona
        WHERE o.estatus = 'Retenciones'
        ORDER BY o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // DICTAMINADOS  (estatus = 'en_transito' o 'cancelado')
    // =========================================================================

    public function obtenerDictaminados(): array
    {
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
        LEFT JOIN asigna_creditos_adjudicacion aca
               ON aca.id_credito = o.id_credito AND aca.estatus = '1'
        LEFT JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
        LEFT JOIN persona per              ON per.id = pa.id_persona
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
        return $this->db->queryOne(
            "SELECT *,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt
             FROM adj_dictamen
             WHERE id_operacion = :id
             ORDER BY fecha_alta DESC
             LIMIT 1",
            ['id' => $idOperacion]
        );
    }
}
