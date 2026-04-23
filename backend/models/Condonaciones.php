<?php

namespace models;

use Core\Model;
use Core\DatabaseSegundometro;

class Condonaciones extends Model
{
    /**
     * Obtiene todas las condonaciones registradas
     */
    public static function getConsultaCondonaciones()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll("
                SELECT 
                    cc.id_condonacion,
                    cc.id_credito,
                    cc.id_motivo_condonacion,
                    COALESCE(MAX(m.motivo), 'Campaña Call Center') AS motivo_condonacion,
                    cc.comentario,
                    cc.total_condonado,
                    cc.created_at as fecha_solicitud,
                    cc.usuario as nombre_usuario,
                    cc.id_usuario,
                    ts.Nombre_cliente as nombre_colaborador,
                    ts.Id_cliente as id_persona,
                    ts.Id_cliente as id_cliente,
                    COUNT(ccd.id) as total_detalles
                FROM condonaciones_cobranza cc
                LEFT JOIN condonaciones_cobranza_detalle ccd ON ccd.id_condonacion = cc.id_condonacion
                LEFT JOIN catalogo_motivos_condonacion m ON m.id = cc.id_motivo_condonacion
                LEFT JOIN tbl_segundometro_semana ts ON ts.Id_credito = cc.id_credito
                GROUP BY cc.id_condonacion
                ORDER BY cc.created_at DESC, cc.id_condonacion DESC
            ");
            
            $datos = is_array($r) ? $r : [];

            echo json_encode([
                "success" => true,
                "mensaje" => "Condonaciones encontradas.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Obtiene el detalle de una condonación específica
     */
    public static function getDetalleCondonacion($id_condonacion)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new DatabaseSegundometro();
            
            // Datos principales de la condonación
            $condonacion = $db->queryOne("
                SELECT 
                    cc.id_condonacion,
                    cc.id_credito,
                    cc.id_motivo_condonacion,
                    COALESCE(m.motivo, 'Campaña Call Center') AS motivo_condonacion,
                    cc.comentario,
                    cc.total_condonado,
                    cc.created_at as fecha_solicitud,
                    cc.usuario as nombre_usuario,
                    cc.id_usuario,
                    ts.Nombre_cliente as nombre_colaborador,
                    ts.Id_cliente as id_persona,
                    ts.Id_cliente as id_cliente,
                    ts.Domicilio_Completo as domicilio,
                    ts.Bucket_Morosidad_Real as bucket,
                    ts.Dias_mora as dias_mora,
                    ts.saldo_vencido_inicio as saldo_vencido
                FROM condonaciones_cobranza cc
                LEFT JOIN catalogo_motivos_condonacion m ON m.id = cc.id_motivo_condonacion
                LEFT JOIN tbl_segundometro_semana ts ON ts.Id_credito = cc.id_credito
                WHERE cc.id_condonacion = :id_condonacion
            ", ['id_condonacion' => $id_condonacion]);

            if (!$condonacion) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Condonación no encontrada.",
                    "datos" => null
                ]);
                exit;
            }

            // Obtener detalles de gastos condonados
            $detalles = $db->queryAll("
                SELECT 
                    ccd.id,
                    ccd.id_gastos_cobranza,
                    ccd.monto
                FROM condonaciones_cobranza_detalle ccd
                WHERE ccd.id_condonacion = :id_condonacion
                ORDER BY ccd.id
            ", ['id_condonacion' => $id_condonacion]);

            $condonacion['detalles'] = is_array($detalles) ? $detalles : [];

            echo json_encode([
                "success" => true,
                "mensaje" => "Detalle encontrado.",
                "datos" => $condonacion
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => null,
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Cambia el estado de una condonación (aprobar/rechazar)
     * Nota: Las tablas actuales no tienen campo de estado, 
     * se podría agregar o manejar de otra forma según necesidad
     */
    public static function cambiarEstado($id_condonacion, $estado)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            echo json_encode([
                "success" => false,
                "mensaje" => "Funcionalidad pendiente: las tablas actuales no tienen campo de estado."
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Crea una nueva condonación
     */
    public static function crear($datos)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new DatabaseSegundometro();
            
            // Obtener datos del usuario de sesión
            $id_usuario = $_SESSION['user_id'] ?? 1;
            $nombre_usuario = $_SESSION['nombre'] ?? 'Sistema';

            $id_motivo = \Models\EstadoCuenta::normalizarIdMotivoCondonacionCobranza($datos['id_motivo_condonacion'] ?? 1);

            // Insertar condonación principal
            $resultado = $db->execute("
                INSERT INTO condonaciones_cobranza 
                (id_credito, id_motivo_condonacion, comentario, id_usuario, usuario, total_condonado, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $datos['id_credito'],
                $id_motivo,
                $datos['comentario'],
                $id_usuario,
                $nombre_usuario,
                $datos['total_condonado']
            ]);

            if ($resultado) {
                $id_condonacion = $db->lastInsertId();

                // Insertar detalles si existen
                if (!empty($datos['detalles']) && is_array($datos['detalles'])) {
                    foreach ($datos['detalles'] as $detalle) {
                        $db->execute("
                            INSERT INTO condonaciones_cobranza_detalle 
                            (id_condonacion, id_gastos_cobranza, monto)
                            VALUES (?, ?, ?)
                        ", [
                            $id_condonacion,
                            $detalle['id_gastos_cobranza'],
                            $detalle['monto']
                        ]);
                    }
                }

                echo json_encode([
                    "success" => true,
                    "mensaje" => "Condonación creada correctamente.",
                    "id_condonacion" => $id_condonacion
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "No se pudo crear la condonación."
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Obtiene el historial de condonaciones de un crédito
     */
    public static function getHistorialCredito($id_credito)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll("
                SELECT 
                    cc.id_condonacion,
                    cc.comentario,
                    cc.total_condonado,
                    cc.created_at,
                    cc.usuario,
                    COUNT(ccd.id) as total_detalles
                FROM condonaciones_cobranza cc
                LEFT JOIN condonaciones_cobranza_detalle ccd ON ccd.id_condonacion = cc.id_condonacion
                WHERE cc.id_credito = :id_credito
                GROUP BY cc.id_condonacion
                ORDER BY cc.created_at DESC
            ", ['id_credito' => $id_credito]);
            
            $datos = is_array($r) ? $r : [];

            echo json_encode([
                "success" => true,
                "mensaje" => "Historial encontrado.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Obtiene estadísticas de condonaciones
     */
    public static function getEstadisticas()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new DatabaseSegundometro();
            
            // Total de condonaciones y montos
            $stats = $db->queryOne("
                SELECT 
                    COUNT(*) as total,
                    SUM(total_condonado) as monto_total,
                    AVG(total_condonado) as monto_promedio,
                    MAX(total_condonado) as monto_maximo,
                    MIN(total_condonado) as monto_minimo
                FROM condonaciones_cobranza
            ");

            // Condonaciones por mes
            $porMes = $db->queryAll("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as mes,
                    COUNT(*) as cantidad,
                    SUM(total_condonado) as monto
                FROM condonaciones_cobranza
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY mes
                ORDER BY mes DESC
            ");

            $stats['por_mes'] = is_array($porMes) ? $porMes : [];

            echo json_encode([
                "success" => true,
                "mensaje" => "Estadísticas obtenidas.",
                "datos" => $stats
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => null,
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Obtiene los gastos de cobranza disponibles para condonar de un crédito
     */
    public static function getGastosCobranza($id_credito)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll("
                SELECT 
                    gc.id,
                    gc.concepto,
                    gc.monto,
                    gc.fecha,
                    gc.observaciones,
                    COALESCE(SUM(ccd.monto), 0) as monto_condonado,
                    (gc.monto - COALESCE(SUM(ccd.monto), 0)) as monto_disponible
                FROM gastos_cobranza gc
                LEFT JOIN condonaciones_cobranza_detalle ccd ON ccd.id_gastos_cobranza = gc.id
                WHERE gc.id_credito = ?
                GROUP BY gc.id
                HAVING monto_disponible > 0
                ORDER BY gc.fecha DESC
            ", [$id_credito]);
            
            $datos = is_array($r) ? $r : [];

            echo json_encode([
                "success" => true,
                "mensaje" => "Gastos Cobranza encontrados.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }
}
