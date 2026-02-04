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
     * Obtener datos de un despacho específico
     */
    public function obtenerDatosDespacho($idDespacho)
    {
        $query = <<<SQL
        SELECT 
            d.id,
            d.id_persona,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            d.tipo_persona,
            d.numero_tel1,
            d.numero_tel2,
            d.correo_1,
            d.correo_2,
            d.direccion,
            pu.nombre AS puesto
        FROM despachos d
        INNER JOIN persona per ON d.id_persona = per.id
        INNER JOIN asigna_puesto ap ON per.id = ap.id_persona
        INNER JOIN puesto pu ON ap.id_puesto = pu.id
        WHERE d.id = ? AND ap.activo = 1
SQL;

        $result = $this->db->queryOne($query, [$idDespacho]);
        
        if (!$result) {
            return [
                'nombre_despacho' => '',
                'informacion' => '',
                'comentarios' => []
            ];
        }
        
        // Construir información del despacho
        $informacion = "Tipo: {$result['tipo_persona']}\n";
        $informacion .= "Teléfono 1: {$result['numero_tel1']}\n";
        if ($result['numero_tel2']) {
            $informacion .= "Teléfono 2: {$result['numero_tel2']}\n";
        }
        $informacion .= "Email: {$result['correo_1']}\n";
        if ($result['correo_2']) {
            $informacion .= "Email 2: {$result['correo_2']}\n";
        }
        if ($result['direccion']) {
            $informacion .= "Dirección: {$result['direccion']}";
        }
        
        // Obtener comentarios
        $comentarios = $this->obtenerComentarios($idDespacho);
        
        return [
            'nombre_despacho' => $result['nombre_completo'],
            'informacion' => $informacion,
            'comentarios' => $comentarios
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
    public function obtenerMetricasDespacho($idDespacho)
    {
        // Créditos asignados activos
        $queryCreditos = <<<SQL
        SELECT COUNT(*) as total 
        FROM asigna_creditos_despacho 
        WHERE id_despacho = ? AND estatus = 'Activo'
SQL;
        $creditos = $this->db->queryOne($queryCreditos, [$idDespacho]);
        
        // Saldo total - esto dependerá de tu tabla de créditos
        // Por ahora devuelvo 0, ajusta según tu tabla real
        $querySaldo = <<<SQL
        SELECT COALESCE(SUM(c.saldo_actual), 0) as total_saldo
        FROM asigna_creditos_despacho acd
        INNER JOIN creditos c ON acd.id_credito = c.id_credito
        WHERE acd.id_despacho = ? AND acd.estatus = 'Activo'
SQL;
        $saldo = $this->db->queryOne($querySaldo, [$idDespacho]);
        
        // Recuperación - ajusta según tu lógica de negocio
        $queryRecuperacion = <<<SQL
        SELECT 
            COALESCE(
                (SUM(c.monto_pagado) / NULLIF(SUM(c.saldo_original), 0)) * 100, 
                0
            ) as porcentaje
        FROM asigna_creditos_despacho acd
        INNER JOIN creditos c ON acd.id_credito = c.id_credito
        WHERE acd.id_despacho = ? AND acd.estatus = 'Activo'
SQL;
        $recuperacion = $this->db->queryOne($queryRecuperacion, [$idDespacho]);
        
        // Promedio días mora
        $queryMora = <<<SQL
        SELECT COALESCE(AVG(c.dias_mora), 0) as promedio
        FROM asigna_creditos_despacho acd
        INNER JOIN creditos c ON acd.id_credito = c.id_credito
        WHERE acd.id_despacho = ? AND acd.estatus = 'Activo'
SQL;
        $mora = $this->db->queryOne($queryMora, [$idDespacho]);
        
        return [
            'creditos_asignados' => $creditos['total'] ?? 0,
            'saldo_total' => $saldo['total_saldo'] ?? 0,
            'recuperacion' => round($recuperacion['porcentaje'] ?? 0, 2),
            'promedio_mora' => round($mora['promedio'] ?? 0, 0)
        ];
    }

    /**
     * Buscar crédito por ID
     */
    public function buscarCredito($tipo, $valor)
    {
        // Por ahora solo por ID, ajusta según tu tabla de créditos
        $query = <<<SQL
        SELECT 
            c.id_credito,
            CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS nombre_cliente,
            c.saldo_actual,
            c.dias_mora,
            c.curp,
            c.telefono
        FROM creditos c
        WHERE c.id_credito = ?
        LIMIT 1
SQL;

        return $this->db->queryOne($query, [$valor]);
    }

    /**
     * Verificar si un crédito ya está asignado a un despacho (activo)
     */
    public function verificarAsignacion($idCredito)
    {
        $query = <<<SQL
        SELECT COUNT(*) as total 
        FROM asigna_creditos_despacho 
        WHERE id_credito = ? AND estatus = 'Activo'
SQL;
        $result = $this->db->queryOne($query, [$idCredito]);
        return ($result['total'] ?? 0) > 0;
    }

    /**
     * Asignar crédito a un despacho
     */
    public function asignarCredito($idDespacho, $idCredito)
    {
        $query = <<<SQL
        INSERT INTO asigna_creditos_despacho 
        (id_despacho, id_credito, fecha_alta, persona_que_lo_asigna, estatus)
        VALUES (?, ?, NOW(), ?, 'Activo')
SQL;

        $usuarioAsignacion = $_SESSION['usuario_id'] ?? $_SESSION['id_persona'] ?? 0;
        
        return $this->db->query($query, [$idDespacho, $idCredito, $usuarioAsignacion]);
    }

    /**
     * Desasignar crédito (marcar como Finalizado)
     */
    public function desasignarCredito($idCredito)
    {
        $query = <<<SQL
        UPDATE asigna_creditos_despacho 
        SET estatus = 'Finalizado', 
            fecha_baja = NOW()
        WHERE id_credito = ? AND estatus = 'Activo'
SQL;
        
        return $this->db->query($query, [$idCredito]);
    }

    /**
     * Obtener créditos asignados a un despacho
     */
    public function obtenerCreditosAsignados($idDespacho)
    {
        $query = <<<SQL
        SELECT 
            c.id_credito,
            CONCAT(c.nombre, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS nombre_cliente,
            c.saldo_actual as saldo,
            c.dias_mora,
            acd.estatus as estado,
            DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d') as fecha_asignacion,
            CONCAT_WS(' ', per.nombres, per.apellidop) as asignado_por
        FROM asigna_creditos_despacho acd
        INNER JOIN creditos c ON acd.id_credito = c.id_credito
        LEFT JOIN persona per ON acd.persona_que_lo_asigna = per.id
        WHERE acd.id_despacho = ? AND acd.estatus = 'Activo'
        ORDER BY acd.fecha_alta DESC
SQL;

        return $this->db->queryAll($query, [$idDespacho]);
    }

    /**
     * Guardar comentario sobre un despacho
     */
    public function guardarComentario($idDespacho, $comentario)
    {
        $query = <<<SQL
        INSERT INTO comentarios_despacho 
        (id_despacho, comentario, id_persona_comenta, fecha_comentario)
        VALUES (?, ?, ?, NOW())
SQL;

        $idPersona = $_SESSION['id_persona'] ?? $_SESSION['usuario_id'] ?? 0;
        
        return $this->db->query($query, [$idDespacho, $comentario, $idPersona]);
    }
}
