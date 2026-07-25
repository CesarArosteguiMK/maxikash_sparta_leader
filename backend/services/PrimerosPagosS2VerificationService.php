<?php

namespace Services;

use Core\DatabaseSegundometro;
use Core\EnvLoader;

/**
 * Hilo maestro para validar en S2 los primeros pagos de Segundometro.
 *
 * Solo toma la cohorte cuyo primer vencimiento cae entre martes y lunes.
 * El acumulado de pagos S2 de ese mismo periodo se conserva en la tabla base.
 */
final class PrimerosPagosS2VerificationService
{
    private const LOCK_NAME = 'sparta_primeros_pagos_s2_v1';
    private const TRACKING_TABLE = 'tbl_segundometro_primeros_pagos_s2';

    /** @return array<string, mixed> */
    public function ejecutar(int $limite = 250): array
    {
        $limite = max(1, min(1000, $limite));
        $db = new DatabaseSegundometro();
        $lock = $db->queryOne('SELECT GET_LOCK(:lock_name, 0) AS acquired', ['lock_name' => self::LOCK_NAME]);
        if ((int) ($lock['acquired'] ?? 0) !== 1) {
            return ['ok' => true, 'estado' => 'omitido', 'mensaje' => 'Ya hay una ejecucion en curso.'];
        }

        try {
            $this->asegurarTablaSeguimiento($db);
            $this->asegurarColumnaTotalPagadoSemana($db);
            [$inicioSemana, $finSemana] = $this->periodoSemanaActual();
            $pendientes = $db->queryAll(
                'SELECT p.`id_primeros_pagos`, p.`Id_credito`, p.`Fecha_inicio`,
                        p.`Fecha_primer_vencimiento`, p.`Cuota`
                   FROM `tbl_segundometro_primeros_pagos` p
                  WHERE DATE(p.`Fecha_primer_vencimiento`) BETWEEN :inicio_semana AND :fin_semana
                  ORDER BY p.`Fecha_primer_vencimiento`, p.`id_primeros_pagos`
                  LIMIT ' . $limite,
                ['inicio_semana' => $inicioSemana, 'fin_semana' => $finSemana]
            );

            $resumen = [
                'ok' => true, 'estado' => 'completado',
                'periodo_inicio' => $inicioSemana, 'periodo_fin' => $finSemana,
                'seleccionados' => count($pendientes), 'pagados' => 0, 'pendientes' => 0, 'errores' => 0,
            ];
            foreach ($pendientes as $fila) {
                try {
                    $resultado = $this->verificarFila($fila, $inicioSemana, $finSemana);
                    $this->guardarResultado($db, $fila, $resultado);
                    $this->guardarTotalPagadoSemana($db, $fila, $resultado['total_pagado_semana']);
                    $resumen[$resultado['pagado'] ? 'pagados' : 'pendientes']++;
                } catch (\Throwable $e) {
                    $this->guardarError($db, $fila, $e->getMessage());
                    $resumen['errores']++;
                    error_log('[PrimerosPagosS2] credito=' . ($fila['Id_credito'] ?? '?') . ' ' . $e->getMessage());
                }
            }

            return $resumen;
        } finally {
            try {
                $db->queryOne('SELECT RELEASE_LOCK(:lock_name) AS released', ['lock_name' => self::LOCK_NAME]);
            } catch (\Throwable $e) {
                error_log('[PrimerosPagosS2] no se pudo liberar lock: ' . $e->getMessage());
            }
        }
    }

    private function asegurarTablaSeguimiento(DatabaseSegundometro $db): void
    {
        $db->CRUD('CREATE TABLE IF NOT EXISTS `' . self::TRACKING_TABLE . '` (
            `id_primeros_pagos` BIGINT NOT NULL,
            `id_credito` BIGINT NOT NULL,
            `fecha_inicio` DATE NULL,
            `fecha_primer_vencimiento` DATE NULL,
            `cuota_esperada` DECIMAL(12,2) NULL,
            `estatus` ENUM(\'pendiente\', \'pagado\', \'error\') NOT NULL DEFAULT \'pendiente\',
            `pago_fecha_s2` DATETIME NULL,
            `pago_monto_s2` DECIMAL(12,2) NULL,
            `pago_referencia_s2` VARCHAR(191) NULL,
            `pagos_encontrados` INT NOT NULL DEFAULT 0,
            `ultimo_consultado_at` DATETIME NOT NULL,
            `confirmado_at` DATETIME NULL,
            `ultimo_error` VARCHAR(1000) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_primeros_pagos`),
            KEY `idx_pp_s2_estatus_consulta` (`estatus`, `ultimo_consultado_at`),
            KEY `idx_pp_s2_credito` (`id_credito`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    /** Crea la columna directamente en la fuente si aún no fue desplegada. */
    private function asegurarColumnaTotalPagadoSemana(DatabaseSegundometro $db): void
    {
        // SHOW COLUMNS no admite marcadores PDO en MySQL; el nombre es constante.
        $columna = $db->queryOne("SHOW COLUMNS FROM `tbl_segundometro_primeros_pagos` LIKE 'Total_pagado_semana_s2'");
        if ($columna === null) {
            $db->CRUD('ALTER TABLE `tbl_segundometro_primeros_pagos`
                ADD COLUMN `Total_pagado_semana_s2` DECIMAL(12,2) NOT NULL DEFAULT 0.00
                COMMENT \'Total de pagos S2 en la semana martes-lunes del primer vencimiento\'
                AFTER `Abonos_total`');
        }
    }

    /** @return array{0:string,1:string} */
    private function periodoSemanaActual(): array
    {
        $hoy = new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City'));
        // N: lunes=1 ... domingo=7. El inicio operativo es martes=2.
        $diasDesdeMartes = ((int) $hoy->format('N') - 2 + 7) % 7;
        $inicio = $hoy->modify('-' . $diasDesdeMartes . ' days');
        return [$inicio->format('Y-m-d'), $inicio->modify('+6 days')->format('Y-m-d')];
    }

    /** @param array<string,mixed> $fila @return array{pagado:bool,fecha:?string,monto:?float,referencia:?string,pagos_encontrados:int,total_pagado_semana:float} */
    private function verificarFila(array $fila, string $inicioSemana, string $finSemana): array
    {
        $idCredito = (int) ($fila['Id_credito'] ?? 0);
        if ($idCredito <= 0) {
            throw new \InvalidArgumentException('Id_credito invalido.');
        }

        $payload = $this->consultarS2($idCredito);
        $pagos = $this->extraerPagos($payload);
        $candidato = null;
        $totalPagadoSemana = 0.0;

        foreach ($pagos as $pago) {
            $fecha = $this->fechaYmd($pago['fecha'] ?? null);
            $monto = (float) ($pago['monto'] ?? 0);
            // Solo suma pagos realizados dentro del martes-lunes de la cohorte.
            if ($fecha === null || $fecha < $inicioSemana || $fecha > $finSemana || $monto <= 0) {
                continue;
            }
            $totalPagadoSemana += $monto;
            if ($candidato === null || $fecha < $candidato['fecha']) {
                $candidato = ['fecha' => $fecha, 'monto' => $monto, 'referencia' => $pago['referencia'] ?? null];
            }
        }

        $totalPagadoSemana = round($totalPagadoSemana, 2);
        $pagado = $totalPagadoSemana > 0;
        return [
            'pagado' => $pagado,
            'fecha' => $candidato['fecha'] ?? null,
            'monto' => $candidato['monto'] ?? null,
            'referencia' => $candidato['referencia'] ?? null,
            'pagos_encontrados' => count($pagos),
            'total_pagado_semana' => $totalPagadoSemana,
        ];
    }

    /** @return array<string,mixed> */
    private function consultarS2(int $idCredito): array
    {
        EnvLoader::load();
        $token = trim((string) (getenv('S2_ESTADO_CUENTA_TOKEN') ?: getenv('TOKEN') ?: ''));
        if ($token === '') {
            throw new \RuntimeException('Falta S2_ESTADO_CUENTA_TOKEN.');
        }
        $endpoint = trim((string) (getenv('ENDPOINT') ?: 'https://servicios.s2movil.net/s2maxikash/estadocuenta'));
        $ch = curl_init($endpoint);
        if ($ch === false) {
            throw new \RuntimeException('No se pudo inicializar cURL para S2.');
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['idCredito' => $idCredito, 'fechaCorte' => date('Y-m-d')]),
            CURLOPT_HTTPHEADER => ['Token: ' . $token, 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($raw === false || $code !== 200) {
            throw new \RuntimeException('S2 no respondio correctamente (HTTP ' . $code . '): ' . ($error ?: 'sin detalle'));
        }
        $json = json_decode($raw, true);
        if (!is_array($json) || !is_array($json['estadoCuenta'] ?? null)) {
            throw new \RuntimeException('S2 no devolvio estadoCuenta valido.');
        }
        return $json['estadoCuenta'];
    }

    /** @param array<string,mixed> $data @return list<array{fecha:mixed,monto:mixed,referencia:mixed}> */
    private function extraerPagos(array $data): array
    {
        $lista = $this->buscarLista($data, ['datospagos', 'pagos', 'paymentdata']);
        $salida = [];
        foreach ($lista as $pago) {
            $salida[] = [
                'fecha' => $this->buscarEscalar($pago, ['fechadeposito', 'fecharegistro', 'fechavalor', 'fecha']),
                'monto' => $this->buscarEscalar($pago, ['montopago', 'monto', 'importe', 'cantidad', 'pagototal']),
                'referencia' => $this->buscarEscalar($pago, ['referencia', 'foliopago', 'folio', 'idpago']),
            ];
        }
        return $salida;
    }

    /** @param array<string,mixed> $data @param list<string> $keys @return list<array<string,mixed>> */
    private function buscarLista(array $data, array $keys): array
    {
        foreach ($data as $key => $value) {
            if (in_array($this->clave((string) $key), $keys, true) && is_array($value)) {
                return array_values(array_filter($value, 'is_array'));
            }
            if (is_array($value)) {
                $encontrado = $this->buscarLista($value, $keys);
                if ($encontrado !== []) return $encontrado;
            }
        }
        return [];
    }

    /** @param array<string,mixed> $data @param list<string> $keys */
    private function buscarEscalar(array $data, array $keys)
    {
        foreach ($data as $key => $value) {
            if (in_array($this->clave((string) $key), $keys, true) && is_scalar($value)) return $value;
        }
        foreach ($data as $value) {
            if (is_array($value)) {
                $encontrado = $this->buscarEscalar($value, $keys);
                if ($encontrado !== null && $encontrado !== '') return $encontrado;
            }
        }
        return null;
    }

    private function guardarResultado(DatabaseSegundometro $db, array $fila, array $resultado): void
    {
        $now = date('Y-m-d H:i:s');
        $db->CRUD('INSERT INTO `' . self::TRACKING_TABLE . '` (
                id_primeros_pagos, id_credito, fecha_inicio, fecha_primer_vencimiento, cuota_esperada,
                estatus, pago_fecha_s2, pago_monto_s2, pago_referencia_s2, pagos_encontrados,
                ultimo_consultado_at, confirmado_at, ultimo_error, created_at, updated_at
            ) VALUES (
                :id, :credito, :inicio, :vencimiento, :cuota, :estatus, :fecha_pago, :monto_pago, :referencia, :pagos,
                :consultado, :confirmado, NULL, :created, :updated
            ) ON DUPLICATE KEY UPDATE
                id_credito=VALUES(id_credito), estatus=VALUES(estatus), pago_fecha_s2=VALUES(pago_fecha_s2),
                pago_monto_s2=VALUES(pago_monto_s2), pago_referencia_s2=VALUES(pago_referencia_s2),
                pagos_encontrados=VALUES(pagos_encontrados), ultimo_consultado_at=VALUES(ultimo_consultado_at),
                confirmado_at=COALESCE(confirmado_at, VALUES(confirmado_at)), ultimo_error=NULL, updated_at=VALUES(updated_at)', [
            'id' => (int) $fila['id_primeros_pagos'], 'credito' => (int) $fila['Id_credito'],
            'inicio' => $this->fechaYmd($fila['Fecha_inicio'] ?? null), 'vencimiento' => $this->fechaYmd($fila['Fecha_primer_vencimiento'] ?? null),
            'cuota' => (float) ($fila['Cuota'] ?? 0), 'estatus' => $resultado['pagado'] ? 'pagado' : 'pendiente',
            'fecha_pago' => $resultado['fecha'], 'monto_pago' => $resultado['monto'], 'referencia' => $resultado['referencia'],
            'pagos' => $resultado['pagos_encontrados'], 'consultado' => $now, 'confirmado' => $resultado['pagado'] ? $now : null,
            'created' => $now, 'updated' => $now,
        ]);
    }

    private function guardarTotalPagadoSemana(DatabaseSegundometro $db, array $fila, float $total): void
    {
        $db->CRUD('UPDATE `tbl_segundometro_primeros_pagos`
                      SET `Total_pagado_semana_s2` = :total
                    WHERE `id_primeros_pagos` = :id', [
            'total' => $total,
            'id' => (int) $fila['id_primeros_pagos'],
        ]);
    }

    private function guardarError(DatabaseSegundometro $db, array $fila, string $error): void
    {
        $now = date('Y-m-d H:i:s');
        $db->CRUD('INSERT INTO `' . self::TRACKING_TABLE . '` (id_primeros_pagos, id_credito, estatus, ultimo_consultado_at, ultimo_error, created_at, updated_at)
            VALUES (:id, :credito, \'error\', :now, :error, :now2, :now3)
            ON DUPLICATE KEY UPDATE estatus=\'error\', ultimo_consultado_at=VALUES(ultimo_consultado_at), ultimo_error=VALUES(ultimo_error), updated_at=VALUES(updated_at)', [
            'id' => (int) $fila['id_primeros_pagos'], 'credito' => (int) $fila['Id_credito'], 'now' => $now,
            'error' => substr($error, 0, 1000), 'now2' => $now, 'now3' => $now,
        ]);
    }

    private function fechaYmd($valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') return null;
        $ts = strtotime((string) $valor);
        return $ts === false ? null : date('Y-m-d', $ts);
    }

    private function clave(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', strtolower($value));
        return preg_replace('/[^a-z0-9]/', '', $ascii === false ? $value : $ascii) ?: '';
    }
}
