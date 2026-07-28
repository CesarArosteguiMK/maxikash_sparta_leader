<?php

namespace Models;

use Core\Database;
use Core\DatabaseSegundometro;

class Direcciones
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    private static function texto($value, int $max = 255): ?string
    {
        $value = trim((string)($value ?? ''));
        if ($value === '' || $value === '-') {
            return null;
        }
        return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
    }

    private static function tipoPorOrden(int $orden): string
    {
        if ($orden <= 1) return 'principal';
        if ($orden === 2) return 'secundaria';
        if ($orden === 3) return 'terciaria';
        return 'adicional';
    }

    public function buscarPorCredito(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'mensaje' => 'ID de credito invalido.', 'direcciones' => []];
        }

        $rows = $this->db->queryAll(
            "SELECT *
             FROM direcciones
             WHERE id_credito = :id_credito AND activo = 1
             ORDER BY orden_direccion ASC, id ASC",
            ['id_credito' => $idCredito]
        ) ?: [];

        return [
            'success' => true,
            'mensaje' => $rows ? 'Direcciones encontradas.' : 'No hay direcciones registradas para este credito.',
            'id_credito' => $idCredito,
            'direcciones' => $rows,
        ];
    }

    public function guardarDireccion(array $data): array
    {
        $idCredito = (int)($data['id_credito'] ?? 0);
        if ($idCredito <= 0) {
            return ['success' => false, 'mensaje' => 'ID de credito invalido.'];
        }

        $direccion = self::texto($data['direccion'] ?? '', 500);
        if (!$direccion) {
            return ['success' => false, 'mensaje' => 'La direccion es obligatoria.'];
        }

        $rowOrden = $this->db->queryOne(
            'SELECT COALESCE(MAX(orden_direccion), 0) + 1 AS siguiente FROM direcciones WHERE id_credito = :id_credito AND activo = 1',
            ['id_credito' => $idCredito]
        );
        $orden = max(1, (int)($rowOrden['siguiente'] ?? 1));
        $tipo = self::tipoPorOrden($orden);

        $this->db->CRUD(
            "INSERT INTO direcciones (
                id_credito, orden_direccion, tipo_direccion, es_principal,
                codigo_postal, calle_numero, direccion, colonia, ciudad, estado,
                telefono_celular, referencia_1, parentesco_referencia_1, telefono_referencia_1,
                referencia_2, parentesco_referencia_2, telefono_referencia_2, etapa,
                origen, origen_detalle, activo
             ) VALUES (
                :id_credito, :orden_direccion, :tipo_direccion, 0,
                :codigo_postal, :calle_numero, :direccion, :colonia, :ciudad, :estado,
                :telefono_celular, :referencia_1, :parentesco_referencia_1, :telefono_referencia_1,
                :referencia_2, :parentesco_referencia_2, :telefono_referencia_2, :etapa,
                'captura_manual', 'manual', 1
             )",
            [
                'id_credito' => $idCredito,
                'orden_direccion' => $orden,
                'tipo_direccion' => $tipo,
                'codigo_postal' => self::texto($data['codigo_postal'] ?? '', 20),
                'calle_numero' => self::texto($data['calle_numero'] ?? '', 255),
                'direccion' => $direccion,
                'colonia' => self::texto($data['colonia'] ?? '', 255),
                'ciudad' => self::texto($data['ciudad'] ?? '', 255),
                'estado' => self::texto($data['estado'] ?? '', 255),
                'telefono_celular' => self::texto($data['telefono_celular'] ?? '', 40),
                'referencia_1' => self::texto($data['referencia_1'] ?? '', 255),
                'parentesco_referencia_1' => self::texto($data['parentesco_referencia_1'] ?? '', 120),
                'telefono_referencia_1' => self::texto($data['telefono_referencia_1'] ?? '', 40),
                'referencia_2' => self::texto($data['referencia_2'] ?? '', 255),
                'parentesco_referencia_2' => self::texto($data['parentesco_referencia_2'] ?? '', 120),
                'telefono_referencia_2' => self::texto($data['telefono_referencia_2'] ?? '', 40),
                'etapa' => self::texto($data['etapa'] ?? '', 120),
            ]
        );

        return ['success' => true, 'mensaje' => 'Direccion agregada correctamente.', 'id_credito' => $idCredito];
    }

    public function corregirDireccion(array $data): array
    {
        $idCredito = (int) ($data['id_credito'] ?? 0);
        $idDireccion = (int) ($data['id_direccion'] ?? $data['id'] ?? 0);
        if ($idCredito <= 0 || $idDireccion <= 0) {
            return ['success' => false, 'mensaje' => 'Crédito y dirección son obligatorios.'];
        }

        $actual = $this->db->queryOne(
            "SELECT id, direccion FROM direcciones
             WHERE id = :id AND id_credito = :credito AND activo = 1
             LIMIT 1",
            ['id' => $idDireccion, 'credito' => $idCredito]
        );
        if (!$actual) {
            return ['success' => false, 'mensaje' => 'La dirección no pertenece al crédito o ya no está activa.'];
        }

        $permitidos = [
            'codigo_postal' => 20,
            'calle_numero' => 255,
            'direccion' => 500,
            'colonia' => 255,
            'ciudad' => 255,
            'estado' => 255,
            'etapa' => 120,
        ];
        $sets = [];
        $params = ['id' => $idDireccion, 'credito' => $idCredito];
        foreach ($permitidos as $campo => $max) {
            if (!array_key_exists($campo, $data)) {
                continue;
            }
            $valor = self::texto($data[$campo], $max);
            if ($campo === 'direccion' && $valor === null) {
                return ['success' => false, 'mensaje' => 'La dirección corregida no puede quedar vacía.'];
            }
            $sets[] = "{$campo} = :{$campo}";
            $params[$campo] = $valor;
        }
        if (!$sets) {
            return ['success' => false, 'mensaje' => 'No se recibió ningún campo geográfico para corregir.'];
        }

        $sets[] = "origen = 'correccion_manual'";
        $sets[] = "origen_detalle = 'Leonidas'";
        $this->db->CRUD(
            "UPDATE direcciones SET " . implode(', ', $sets) . "
             WHERE id = :id AND id_credito = :credito AND activo = 1",
            $params
        );

        $verificada = $this->db->queryOne(
            "SELECT * FROM direcciones WHERE id = :id AND id_credito = :credito AND activo = 1 LIMIT 1",
            ['id' => $idDireccion, 'credito' => $idCredito]
        );
        return [
            'success' => true,
            'mensaje' => 'Información geográfica corregida.',
            'direccion' => $verificada ?: [],
        ];
    }

    public function reordenarDirecciones(int $idCredito, array $ids): array
    {
        if ($idCredito <= 0 || empty($ids)) {
            return ['success' => false, 'mensaje' => 'Datos incompletos para reordenar.'];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn($id) => $id > 0)));
        if (!$ids) {
            return ['success' => false, 'mensaje' => 'No se recibieron direcciones validas.'];
        }

        $existentes = $this->db->queryAll(
            'SELECT id FROM direcciones WHERE id_credito = :id_credito AND activo = 1',
            ['id_credito' => $idCredito]
        ) ?: [];
        $validos = array_map('intval', array_column($existentes, 'id'));
        sort($validos);
        $recibidos = $ids;
        sort($recibidos);
        if ($validos !== $recibidos) {
            return ['success' => false, 'mensaje' => 'La lista de direcciones no coincide con el credito. Recarga e intenta de nuevo.'];
        }

        $this->db->beginTransaction();
        try {
            foreach ($ids as $idx => $id) {
                $orden = $idx + 1;
                $this->db->CRUD(
                    "UPDATE direcciones
                     SET orden_direccion = :orden,
                         tipo_direccion = :tipo,
                         es_principal = :principal
                     WHERE id = :id AND id_credito = :id_credito",
                    [
                        'orden' => $orden,
                        'tipo' => self::tipoPorOrden($orden),
                        'principal' => $orden === 1 ? 1 : 0,
                        'id' => $id,
                        'id_credito' => $idCredito,
                    ]
                );
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'mensaje' => 'No se pudo actualizar el orden.', 'error' => $e->getMessage()];
        }

        return ['success' => true, 'mensaje' => 'Orden actualizado. La primera direccion ahora es principal.'];
    }

    public function sincronizarDesdeSegundometro(): array
    {
        $dbSeg = new DatabaseSegundometro();
        $rows = $dbSeg->queryAll(
            "SELECT s.Id_credito AS id_credito,
                    s.Domicilio_Completo,
                    s.Codigo_postal_1,
                    s.Estado_1,
                    s.Celular,
                    s.Direccion,
                    s.Calle_numero,
                    s.Colonia_adicional_2,
                    s.Ciudad_adicional_2,
                    s.Estado_adicional_3,
                    s.Calle_numero_adic,
                    s.Codigo_postal_adic,
                    s.Adicionales_colonia,
                    s.Municipio_delegacion,
                    s.Entidad_1,
                    s.Calle_adicional_1,
                    s.Num_exterior_adicional_1,
                    s.Num_interior_adicional_1,
                    s.Cp_adicional_2,
                    s.Colonia_adicional_1,
                    s.Estado_adicional_2,
                    s.Ciudad_adicional_1,
                    s.Municipio_adicional_1,
                    s.Calle_adicional_2,
                    s.Num_exterior_adicional_2,
                    s.Num_interior_adicional_2,
                    s.Cp_adicional_3,
                    s.Colonia_adicional_3,
                    s.Estado_adicional_4,
                    s.Ciudad_adicional_3,
                    s.Municipio_adicional_2,
                    s.Bucket_Morosidad,
                    s.SEMANA
             FROM tbl_segundometro_semana s
             WHERE s.Id_credito IS NOT NULL
               AND NOT EXISTS (
                   SELECT 1
                   FROM __SPARTA_SECRET_REDACTED__.direcciones d
                   WHERE d.id_credito = s.Id_credito
                     AND d.activo = 1
               )
             GROUP BY s.Id_credito
             ORDER BY s.Id_credito"
        ) ?: [];

        $ids = array_values(array_filter(array_map(static fn($r) => (int)($r['id_credito'] ?? 0), $rows)));
        if (!$ids) {
            return ['success' => true, 'mensaje' => 'No hay creditos pendientes por sincronizar.', 'insertados' => 0, 'revisados' => 0];
        }

        $insertados = 0;
        $conDirecciones = 0;
        $maxiPorCredito = $this->consultarDireccionesMaxiProd($ids);
        foreach ($rows as $row) {
            $idCredito = (int)($row['id_credito'] ?? 0);
            $fuentes = array_merge(
                $this->direccionesCandidatasMaxiProd($maxiPorCredito[$idCredito] ?? null),
                $this->direccionesCandidatasSegundometro($row)
            );
            $insertadasCredito = $this->insertarDireccionesCandidatas($idCredito, $fuentes, $row);
            $insertados += $insertadasCredito;
            if ($insertadasCredito > 0) {
                $conDirecciones++;
            }
        }

        return [
            'success' => true,
            'mensaje' => 'Sincronizacion terminada.',
            'revisados' => count($ids),
            'insertados' => $insertados,
            'sin_datos' => max(0, count($ids) - $conDirecciones),
        ];
    }

    private function direccionesCandidatasSegundometro(array $row): array
    {
        return [
            [
                'origen' => 'segundometro_semana',
                'origen_detalle' => 'domicilio_completo',
                'direccion' => self::texto($row['Domicilio_Completo'] ?? '', 500),
                'codigo_postal' => self::texto($row['Codigo_postal_1'] ?? '', 20),
                'estado' => self::texto($row['Estado_1'] ?? '', 255),
                'telefono_celular' => self::texto($row['Celular'] ?? '', 40),
            ],
            [
                'origen' => 'segundometro_semana',
                'origen_detalle' => 'direccion',
                'direccion' => self::texto($row['Direccion'] ?? '', 500),
                'calle_numero' => self::texto($row['Calle_numero'] ?? '', 255),
                'colonia' => self::texto($row['Colonia_adicional_2'] ?? '', 255),
                'ciudad' => self::texto($row['Ciudad_adicional_2'] ?? '', 255),
                'estado' => self::texto($row['Estado_adicional_3'] ?? '', 255),
                'telefono_celular' => self::texto($row['Celular'] ?? '', 40),
            ],
            [
                'origen' => 'segundometro_semana',
                'origen_detalle' => 'direccion_adicional',
                'direccion' => self::texto($row['Calle_numero_adic'] ?? '', 500),
                'codigo_postal' => self::texto($row['Codigo_postal_adic'] ?? '', 20),
                'colonia' => self::texto($row['Adicionales_colonia'] ?? '', 255),
                'ciudad' => self::texto($row['Municipio_delegacion'] ?? '', 255),
                'estado' => self::texto($row['Entidad_1'] ?? '', 255),
                'telefono_celular' => self::texto($row['Celular'] ?? '', 40),
            ],
            [
                'origen' => 'segundometro_semana',
                'origen_detalle' => 'calle_adicional_1',
                'direccion' => self::texto($row['Calle_adicional_1'] ?? '', 500),
                'calle_numero' => self::texto(trim((string)($row['Num_exterior_adicional_1'] ?? '') . ' ' . (string)($row['Num_interior_adicional_1'] ?? '')), 255),
                'codigo_postal' => self::texto($row['Cp_adicional_2'] ?? '', 20),
                'colonia' => self::texto($row['Colonia_adicional_1'] ?? '', 255),
                'ciudad' => self::texto($row['Ciudad_adicional_1'] ?? $row['Municipio_adicional_1'] ?? '', 255),
                'estado' => self::texto($row['Estado_adicional_2'] ?? '', 255),
                'telefono_celular' => self::texto($row['Celular'] ?? '', 40),
            ],
            [
                'origen' => 'segundometro_semana',
                'origen_detalle' => 'calle_adicional_2',
                'direccion' => self::texto($row['Calle_adicional_2'] ?? '', 500),
                'calle_numero' => self::texto(trim((string)($row['Num_exterior_adicional_2'] ?? '') . ' ' . (string)($row['Num_interior_adicional_2'] ?? '')), 255),
                'codigo_postal' => self::texto($row['Cp_adicional_3'] ?? '', 20),
                'colonia' => self::texto($row['Colonia_adicional_3'] ?? '', 255),
                'ciudad' => self::texto($row['Ciudad_adicional_3'] ?? $row['Municipio_adicional_2'] ?? '', 255),
                'estado' => self::texto($row['Estado_adicional_4'] ?? '', 255),
                'telefono_celular' => self::texto($row['Celular'] ?? '', 40),
            ],
        ];
    }

    private function direccionesCandidatasMaxiProd(?array $row): array
    {
        if (!$row) return [];
        return [
            [
                'origen' => 'maxi_prod',
                'origen_detalle' => 'direccion_2',
                'codigo_postal' => self::texto($row['codigo_postal_2'] ?? '', 20),
                'calle_numero' => self::texto($row['calle_numero_2'] ?? '', 255),
                'direccion' => self::texto($row['direccion_2'] ?? '', 500),
                'colonia' => self::texto($row['colonia_2'] ?? '', 255),
                'ciudad' => self::texto($row['ciudad_2'] ?? '', 255),
                'estado' => self::texto($row['estado_2'] ?? '', 255),
                'telefono_celular' => self::texto($row['telefono_celular'] ?? '', 40),
                'referencia_1' => self::texto($row['referencia_1'] ?? '', 255),
                'parentesco_referencia_1' => self::texto($row['parentezco_referencia1'] ?? '', 120),
                'telefono_referencia_1' => self::texto($row['telefono_referencia1'] ?? '', 40),
                'referencia_2' => self::texto($row['referencia_2'] ?? '', 255),
                'parentesco_referencia_2' => self::texto($row['parentezco_referencia2'] ?? '', 120),
                'telefono_referencia_2' => self::texto($row['telefono_referencia2'] ?? '', 40),
                'etapa' => self::texto($row['etapa'] ?? '', 120),
            ],
            [
                'origen' => 'maxi_prod',
                'origen_detalle' => 'direccion_1',
                'codigo_postal' => self::texto($row['codigo_postal_1'] ?? '', 20),
                'calle_numero' => self::texto($row['calle_numero_1'] ?? '', 255),
                'direccion' => self::texto($row['direccion_1'] ?? '', 500),
                'colonia' => self::texto($row['colonia_1'] ?? '', 255),
                'ciudad' => self::texto($row['ciudad_1'] ?? '', 255),
                'estado' => self::texto($row['estado_1'] ?? '', 255),
                'telefono_celular' => self::texto($row['telefono_celular'] ?? '', 40),
                'referencia_1' => self::texto($row['referencia_1'] ?? '', 255),
                'parentesco_referencia_1' => self::texto($row['parentezco_referencia1'] ?? '', 120),
                'telefono_referencia_1' => self::texto($row['telefono_referencia1'] ?? '', 40),
                'referencia_2' => self::texto($row['referencia_2'] ?? '', 255),
                'parentesco_referencia_2' => self::texto($row['parentezco_referencia2'] ?? '', 120),
                'telefono_referencia_2' => self::texto($row['telefono_referencia2'] ?? '', 40),
                'etapa' => self::texto($row['etapa'] ?? '', 120),
            ],
        ];
    }

    private function insertarDireccionesCandidatas(int $idCredito, array $candidatas, array $rowContexto): int
    {
        if ($idCredito <= 0) return 0;

        $insertadas = 0;
        $vistas = [];
        foreach ($candidatas as $direccion) {
            $textoDireccion = self::texto($direccion['direccion'] ?? '', 500);
            if (!$textoDireccion) {
                continue;
            }
            $clave = strtolower(preg_replace('/\s+/', ' ', trim($textoDireccion . ' ' . ($direccion['codigo_postal'] ?? ''))));
            if (isset($vistas[$clave])) {
                continue;
            }
            $vistas[$clave] = true;
            $orden = $insertadas + 1;
            $this->insertarDireccionFuente($idCredito, $orden, $direccion, $rowContexto);
            $insertadas++;
        }

        return $insertadas;
    }

    private function insertarDireccionFuente(int $idCredito, int $orden, array $direccion, array $row): void
    {
        $this->db->CRUD(
            "INSERT INTO direcciones (
                id_credito, orden_direccion, tipo_direccion, es_principal,
                codigo_postal, calle_numero, direccion, colonia, ciudad, estado,
                telefono_celular, referencia_1, parentesco_referencia_1, telefono_referencia_1,
                referencia_2, parentesco_referencia_2, telefono_referencia_2, etapa,
                origen, origen_detalle, activo
             ) VALUES (
                :id_credito, :orden, :tipo, :principal,
                :codigo_postal, :calle_numero, :direccion, :colonia, :ciudad, :estado,
                :telefono_celular, :referencia_1, :parentesco_referencia_1, :telefono_referencia_1,
                :referencia_2, :parentesco_referencia_2, :telefono_referencia_2, :etapa,
                :origen, :origen_detalle, 1
             )",
            [
                'id_credito' => $idCredito,
                'orden' => $orden,
                'tipo' => self::tipoPorOrden($orden),
                'principal' => $orden === 1 ? 1 : 0,
                'codigo_postal' => self::texto($direccion['codigo_postal'] ?? '', 20),
                'calle_numero' => self::texto($direccion['calle_numero'] ?? '', 255),
                'direccion' => self::texto($direccion['direccion'] ?? '', 500),
                'colonia' => self::texto($direccion['colonia'] ?? '', 255),
                'ciudad' => self::texto($direccion['ciudad'] ?? '', 255),
                'estado' => self::texto($direccion['estado'] ?? '', 255),
                'telefono_celular' => self::texto($direccion['telefono_celular'] ?? '', 40),
                'referencia_1' => self::texto($direccion['referencia_1'] ?? '', 255),
                'parentesco_referencia_1' => self::texto($direccion['parentesco_referencia_1'] ?? '', 120),
                'telefono_referencia_1' => self::texto($direccion['telefono_referencia_1'] ?? '', 40),
                'referencia_2' => self::texto($direccion['referencia_2'] ?? '', 255),
                'parentesco_referencia_2' => self::texto($direccion['parentesco_referencia_2'] ?? '', 120),
                'telefono_referencia_2' => self::texto($direccion['telefono_referencia_2'] ?? '', 40),
                'etapa' => self::texto($direccion['etapa'] ?? $row['Bucket_Morosidad'] ?? $row['SEMANA'] ?? '', 120),
                'origen' => self::texto($direccion['origen'] ?? 'segundometro_semana', 80),
                'origen_detalle' => self::texto($direccion['origen_detalle'] ?? 'direccion', 120),
            ]
        );
    }

    private function consultarDireccionesMaxiProd(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) return [];

        $porCredito = [];
        foreach (array_chunk($ids, 500) as $bloque) {
            $idsSql = implode(',', $bloque);
            $sql = "
                SELECT o.id_oferta, 
                    pe.codigo_postal as codigo_postal_1,
                    pe.calle_numero AS calle_numero_1,
                    pe.direccion AS direccion_1,
                    pe.colonia AS colonia_1,
                    pe.ciudad AS ciudad_1,
                    pe.estado AS estado_1,
                    pe.telefono_celular,
                    pa.codigo_postal_adic AS codigo_postal_2,
                    pa.calle_numero_adic AS calle_numero_2,
                    pa.calle AS direccion_2,
                    pa.adicionales_colonia AS colonia_2,
                    pa.municipio_delegacion AS ciudad_2,
                    pa.entidad AS estado_2,
                    concat(pa.nombre_referencia1,' ',pa.apellido_paterno_referencia1,' ',pa.apellido_materno_referencia1) as referencia_1,
                    pa.parentezco_referencia1,
                    pa.telefono_referencia1,
                    concat(pa.nombre_referencia2,' ',pa.apellido_paterno_referencia2,' ',pa.apellido_materno_referencia2) as referencia_2,
                    pa.parentezco_referencia2,
                    pa.telefono_referencia2,
                    o.etapa
                FROM oferta o 
                LEFT JOIN persona_adicionales pa ON pa.fk_persona = o.fk_persona 
                LEFT JOIN persona pe ON pe.id_persona = o.fk_persona
                WHERE o.id_oferta IN ({$idsSql})";
            $dbMaxi = new \core\DatabaseMaxiProd();
            foreach (($dbMaxi->queryAll($sql) ?: []) as $row) {
                $porCredito[(int)($row['id_oferta'] ?? 0)] = $row;
            }
        }

        return $porCredito;
    }

}
