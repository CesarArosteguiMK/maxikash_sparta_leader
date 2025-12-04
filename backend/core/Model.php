<?php

namespace Core;

class Model
{
    public static function resultado($exito, $mensaje = null, $datos = null, $error = null)
    {
        $resultado = [
            'success' => $exito
        ];

        if ($mensaje !== null) $resultado['mensaje'] = $mensaje;
        if ($datos !== null) $resultado['datos'] = $datos;
        if ($error !== null) $resultado['error'] = $error;
        return $resultado;
    }

    public static function getCatalogoEmpresas()
    {
        $qry = <<<SQL
            SELECT
                ID,
                RAZON_SOCIAL
            FROM
                EMPRESA
            WHERE
                ESTATUS = 1
            ORDER BY
                RAZON_SOCIAL
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($qry);
            return self::resultado(true, 'Empresas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener las empresas.', null, $e->getMessage());
        }
    }

    public static function getCatalogoSucursales($filtroEmpresas = [], $ignorarRegion = false)
    {
        $query = <<<SQL
            SELECT
                *
            FROM
                SUCURSALES_REGIONES
            WHERE
                1 = 1
                FILTROS
            ORDER BY
                EMPRESA, REGION_NOMBRE, SUCURSAL_NOMBRE
        SQL;

        if (is_array($filtroEmpresas) && count($filtroEmpresas) > 0) {
            $empresas = implode(',', $filtroEmpresas);
            $filtro = " AND EMPRESA IN ($empresas) ";
            $query = str_replace('FILTROS', $filtro, $query);
        }

        $query = str_replace('FILTROS', '', $query);
        if ($ignorarRegion) $query = str_replace('REGION_NOMBRE, ', '', $query);

        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Sucursales obtenidas.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener las sucursales.', null, $e->getMessage());
        }
    }

    public static function getCatalogoBancos()
    {
        $query = "SELECT * FROM CAT_BANCO ORDER BY NOMBRE";
        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Bancos obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener los bancos.', null, $e->getMessage());
        }
    }

    public static function getPerfiles()
    {
        $query = "SELECT * FROM PERFIL ORDER BY NOMBRE";
        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Perfiles obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener los perfiles.', null, $e->getMessage());
        }
    }

    public static function getCatalogoConceptosViaticos()
    {
        $query = <<<SQL
            SELECT
                VMM.EMPRESA,
                VMM.REGION,
                VMM.SUCURSAL,
                C.ID,
                C.NOMBRE,
                C.DESCRIPCION AS DESCRIPCION,
                VMM.MONTO_MAXIMO
            FROM 
                CAT_VIATICOS_CONCEPTO C
                JOIN CAT_VIATICOS_MONTO_MAXIMO VMM ON C.ID = VMM.CONCEPTO
            ORDER BY 
                C.ID
        SQL;
        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Conceptos de viáticos obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener los conceptos de viáticos.', null, $e->getMessage());
        }
    }

    public static function getCatalogoMetodosEntrega()
    {
        $query = "SELECT * FROM CAT_VIATICOS_METODO_ENTREGA ORDER BY ID";
        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Métodos de entrega obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener los métodos de entrega.', null, $e->getMessage());
        }
    }

    public static function getPersonalSucursal($datos)
    {
        $query = <<<SQL
            SELECT
                PERSONA,
                GET_NOMBRE_PERSONA(PERSONA) AS NOMBRE
            FROM
                NOMINA
            WHERE
                ESTATUS = 1
                AND REGION = :regionId
                AND SUCURSAL = :sucursalId
        SQL;

        $params = [
            'regionId' => $datos['region'],
            'sucursalId' => $datos['sucursal']
        ];

        if ($datos['region'] !== '333' && $datos['sucursal'] !== '666') {
            $query = $query . " AND EMPRESA = :empresaId";
            $params['empresaId'] = $datos['empresa'];
        }

        $query = $query . " ORDER BY NOMBRE";

        try {
            $db = new Database();
            $result = $db->queryAll($query, $params);
            return self::resultado(true, 'Personal de la sucursal obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el personal de la sucursal.', null, $e->getMessage());
        }
    }

    public static function getCatalogoParentescos()
    {
        $query = "SELECT * FROM CAT_PARENTESCO_EMERGENCIA ORDER BY ID_PARENTESCO";
        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Parentescos obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener los parentescos.', null, $e->getMessage());
        }
    }

    public static function getCatalogoPuestos()
    {
        $query = "SELECT * FROM CAT_PUESTOS ORDER BY DESCRIPCION";
        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Puestos obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener los puestos.', null, $e->getMessage());
        }
    }

    public static function getCatalogoNominaProveedores()
    {
        $query = "SELECT * FROM CAT_NOMINAS_PROVEEDOR ORDER BY NOMBRE_PROVEEDOR";
        try {
            $db = new Database();
            $result = $db->queryAll($query);
            return self::resultado(true, 'Proveedores de nómina obtenidos.', $result);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener los proveedores de nómina.', null, $e->getMessage());
        }
    }
}
