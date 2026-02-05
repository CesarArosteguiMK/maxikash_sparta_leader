<?php

namespace Models;

use Core\Model;
use Core\Database;

class Despachos extends Model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Obtener lista de despachos (Gestores y Supervisores)
     * IDs de puesto: 24 = Gestor, 36 = Supervisor
     * Un despacho = Una persona con cualquiera de estos 2 puestos
     */
    public function obtenerDespachos()
    {
        $query = <<<SQL
        SELECT 
            ap.id_persona,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.id AS id_puesto,
            pu.nombre AS nombre_puesto,
            ap.activo,
            per.estatus,
            per.correo,
            per.telefono_uno
        FROM asigna_puesto ap
        INNER JOIN persona per ON per.id = ap.id_persona
        INNER JOIN puesto pu ON pu.id = ap.id_puesto
        WHERE ap.id_puesto IN (24, 36)
        ORDER BY pu.id, per.nombres
SQL;

        return $this->db->queryAll($query);
    }

    /**
     * Obtener datos de un despacho específico usando la tabla persona directamente
     * Si existe en despachos, traemos esos datos adicionales, sino solo de persona
     */
    public function obtenerDatosDespacho($idPersona)
    {
        $query = <<<SQL
        SELECT 
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS puesto,
            COALESCE(d.numero_tel1, per.telefono_uno) AS telefono,
            COALESCE(d.correo_1, per.correo) AS correo,
            COALESCE(d.direccion, 'Sin dirección registrada') AS direccion,
            d.tipo_persona,
            d.numero_tel2,
            d.correo_2,
            d.id AS id_despacho
        FROM persona per
        INNER JOIN asigna_puesto ap ON per.id = ap.id_persona
        INNER JOIN puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN despachos d ON per.id = d.id_persona AND d.estatus = 'Activo'
        WHERE per.id = :idPersona 
          AND ap.id_puesto IN (24, 36)
          AND ap.activo = 1
        LIMIT 1
SQL;

        $result = $this->db->queryOne($query, ['idPersona' => $idPersona]);
        
        if (!$result) {
            return [
                'datos' => [
                    'nombre_completo' => '',
                    'puesto' => '',
                    'telefono' => '',
                    'correo' => '',
                    'direccion' => '',
                    'tipo_persona' => ''
                ],
                'comentarios' => '',
                'id_despacho' => null
            ];
        }
        
        // Obtener el último comentario (si existe y tiene id_despacho)
        $comentario = '';
        if (!empty($result['id_despacho'])) {
            $queryComentario = <<<SQL
            SELECT comentario 
            FROM comentarios_despacho 
            WHERE id_despacho = :idDespacho 
            ORDER BY fecha_comentario DESC 
            LIMIT 1
SQL;
            $comentarioResult = $this->db->queryOne($queryComentario, ['idDespacho' => $result['id_despacho']]);
            $comentario = $comentarioResult['comentario'] ?? '';
        }
        
        return [
            'datos' => $result,
            'comentarios' => $comentario,
            'id_despacho' => $result['id_despacho'] ?? null
        ];
    }

    /**
     * Obtener comentarios de un despacho
     */
    public function obtenerComentarios($idDespacho)
    {
        $query = <<<SQL
        SELECT 
            c.id,
            c.comentario,
            c.fecha_comentario,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_persona
        FROM comentarios_despacho c
        LEFT JOIN persona per ON c.id_persona_comenta = per.id
        WHERE c.id_despacho = ?
        ORDER BY c.fecha_comentario DESC
SQL;

        return $this->db->queryAll($query, [$idDespacho]);
    }

    /**
     * Obtener métricas de un despacho
     */
    public function obtenerMetricasDespacho($idPersona)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return [
                'creditos_asignados' => 0,
                'saldo_total' => 0,
                'recuperacion' => 0,
                'promedio_mora' => 0
            ];
        }
        
        $idDespacho = $despacho['id'];
        
        // Créditos asignados activos
        $queryCreditos = <<<SQL
        SELECT COUNT(*) as total 
        FROM asigna_creditos_despacho 
        WHERE id_despacho = :idDespacho AND estatus = 'Activo'
SQL;
        $creditos = $this->db->queryOne($queryCreditos, ['idDespacho' => $idDespacho]);
        
        // Por ahora, saldo, recuperación y mora se calculan cuando tengas acceso a la API de créditos
        // o cuando definas de dónde obtienes esta información
        
        return [
            'creditos_asignados' => $creditos['total'] ?? 0,
            'saldo_total' => 0,
            'recuperacion' => 0,
            'promedio_mora' => 0
        ];
    }

    /**
     * Buscar crédito por ID usando la API externa (como en EstadoCuenta)
     */
    public function buscarCredito($tipo, $valor)
    {
        // Usamos la API externa para obtener información del crédito
        // Similar a como se hace en EstadoCuenta
        $url = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta";
        
        $payload = json_encode([
            "idCredito" => intval($valor),
            "fechaCorte" => date('Y-m-d')
        ]);
        
        $headers = [
            "Token: __SPARTA_TOKEN_REDACTED__",
            "Content-Type: application/json"
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode !== 200) {
            return null;
        }
        
        $json = json_decode($response, true);
        
        if (!isset($json["estadoCuenta"])) {
            return null;
        }
        
        $estadoCuenta = $json["estadoCuenta"];
        $cliente = $estadoCuenta["datosCliente"] ?? [];
        
        // Construir dirección completa desde los campos del cliente
        $direccionParts = array_filter([
            $cliente["calle"] ?? '',
            $cliente["numeroExterior"] ?? '',
            $cliente["numeroInterior"] ?? '',
            $cliente["colonia"] ?? '',
            $cliente["municipio"] ?? '',
            $cliente["estado"] ?? '',
            $cliente["codigoPostal"] ?? ''
        ]);
        $direccion = !empty($direccionParts) ? implode(', ', $direccionParts) : 'Sin dirección';
        
        return [
            'id_credito' => $estadoCuenta["idCredito"] ?? $valor,
            'nombre_cliente' => $cliente["nombreCliente"] ?? 'Sin nombre',
            'saldo_actual' => $estadoCuenta["datosSaldos"]["saldoTotalVencido"] ?? 0,
            'dias_mora' => $estadoCuenta["datosSaldos"]["diasMoraMaximo"] ?? 0,
            'telefono' => $cliente["celular"] ?? 'Sin teléfono',
            'curp' => $cliente["curp"] ?? 'Sin CURP',
            'direccion' => $direccion,
            'sucursal' => $cliente["sucursal"] ?? 'Sin sucursal',
            'fecha_desembolso' => $estadoCuenta["fechaDesembolso"] ?? 'Sin fecha'
        ];
    }

    /**
     * Verificar si un crédito ya está asignado a un despacho (activo)
     */
    public function verificarAsignacion($idCredito)
    {
        $query = <<<SQL
        SELECT COUNT(*) as total 
        FROM asigna_creditos_despacho 
        WHERE id_credito = :idCredito AND estatus = 'Activo'
SQL;
        $result = $this->db->queryOne($query, ['idCredito' => $idCredito]);
        return ($result['total'] ?? 0) > 0;
    }

    /**
     * Asignar crédito a un despacho
     */
    public function asignarCredito($idPersona, $idCredito)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return false;
        }
        
        $query = <<<SQL
        INSERT INTO asigna_creditos_despacho 
        (id_despacho, id_credito, fecha_alta, persona_que_lo_asigna, estatus)
        VALUES (:idDespacho, :idCredito, CURDATE(), :usuarioAsignacion, 'Activo')
SQL;

        $usuarioAsignacion = $_SESSION['usuario_id'] ?? 1;
        
        return $this->db->query($query, [
            'idDespacho' => $despacho['id'],
            'idCredito' => $idCredito,
            'usuarioAsignacion' => $usuarioAsignacion
        ]);
    }

    /**
     * Cambiar estatus de crédito (activar/desactivar)
     */
    public function cambiarEstatusCredito($idCredito, $nuevoEstatus)
    {
        $fechaBaja = $nuevoEstatus === '0' ? 'NOW()' : 'NULL';
        
        $query = <<<SQL
        UPDATE asigna_creditos_despacho 
        SET estatus = :nuevoEstatus, 
            fecha_baja = $fechaBaja
        WHERE id_credito = :idCredito
SQL;
        
        return $this->db->query($query, [
            'idCredito' => $idCredito,
            'nuevoEstatus' => $nuevoEstatus
        ]);
    }

    /**
     * Obtener créditos asignados a un despacho
     * Consulta directamente desde asigna_creditos_despacho usando id_persona
     */
    public function obtenerCreditosAsignados($idPersona)
    {
        $query = <<<SQL
        SELECT 
            acd.id_credito,
            acd.estatus as estado,
            DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d %H:%i') as fecha_asignacion,
            CONCAT_WS(' ', per.nombres, per.apellidop) as asignado_por
        FROM asigna_creditos_despacho acd
        INNER JOIN despachos d ON acd.id_despacho = d.id
        LEFT JOIN persona per ON acd.alta = per.id
        WHERE d.id_persona = :idPersona
        ORDER BY acd.fecha_alta DESC
SQL;

        return $this->db->queryAll($query, ['idPersona' => $idPersona]);
    }

    /**
     * Guardar comentario sobre un despacho
     */
    public function guardarComentario($idPersona, $comentario)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return false;
        }
        
        $query = <<<SQL
        INSERT INTO comentarios_despacho 
        (id_despacho, comentario, id_persona_comenta, fecha_comentario)
        VALUES (:idDespacho, :comentario, :idPersona, NOW())
SQL;

        $idPersonaComenta = $_SESSION['usuario_id'] ?? 1;
        
        return $this->db->query($query, [
            'idDespacho' => $despacho['id'],
            'comentario' => $comentario,
            'idPersona' => $idPersonaComenta
        ]);
    }
}
