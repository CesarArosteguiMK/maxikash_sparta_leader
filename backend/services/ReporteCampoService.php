<?php

namespace Services;

use Core\Database;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteCampoService
{
    private const ROLES_JERARQUIA = ['supervisor', 'subgerente', 'gerente', 'subdirector'];

    /**
     * @return array{spreadsheet: Spreadsheet, total: int}
     */
    public function generarExcel(): array
    {
        $db = new Database();

        $personas = $this->cargarPersonasCampo($db);
        $ausencias = $this->cargarAusenciasActivas($db);
        $personasJerarquia = $this->cargarPersonasJerarquia($db);
        $jefes = $this->cargarJefes($db);
        $legacy = $this->cargarPuestosLegacy($db);
        $deptMap = $this->cargarDepartamentosCampoPorPersona($db);

        $rows = [];
        foreach ($personas as $persona) {
            $personaId = (int)($persona['persona_id'] ?? 0);
            $deptId = (int)($persona['dept_id'] ?? 0);
            $jerarquia = $this->resolverJerarquia($personaId, $deptId, $personasJerarquia, $jefes, $legacy, $ausencias, $deptMap);
            $puestoLegacy = (string)($legacy[$personaId] ?? ($persona['puesto_legacy'] ?? ''));

            $rows[] = [
                'external_id' => (string)($persona['numero_empleado'] ?? ''),
                'nombre_completo' => $this->armarNombre($persona),
                'estatus' => $this->calcularEstatus($personaId, (string)($persona['estatus'] ?? ''), $ausencias),
                'fecha_baja' => (string)($persona['fecha_baja'] ?? ''),
                'es_gestor' => strtolower(trim($puestoLegacy)) === 'gestor' ? 'Si' : 'No',
                'puesto_legacy' => $puestoLegacy,
                'puesto_actual' => (string)($persona['puesto_nombre'] ?? ''),
                'departamento' => (string)($persona['dept_nombre'] ?? ''),
                'supervisor' => $jerarquia['supervisor'],
                'supervisor_estatus' => $jerarquia['supervisor_estatus'],
                'subgerente' => $jerarquia['subgerente'],
                'subgerente_estatus' => $jerarquia['subgerente_estatus'],
                'gerente' => $jerarquia['gerente'],
                'gerente_estatus' => $jerarquia['gerente_estatus'],
                'subdirector' => $jerarquia['subdirector'],
                'subdirector_estatus' => $jerarquia['subdirector_estatus'],
            ];
        }

        usort($rows, static function (array $a, array $b): int {
            return strnatcasecmp((string)$a['external_id'], (string)$b['external_id']);
        });

        return [
            'spreadsheet' => $this->crearSpreadsheet($rows),
            'total' => count($rows),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function cargarPersonasCampo(Database $db): array
    {
        return $db->queryAll(
            "
            SELECT p.id AS persona_id,
                   p.numero_empleado,
                   p.nombres,
                   p.segundo_nombre,
                   p.apellidop,
                   p.apellidom,
                   p.estatus,
                   bp.fecha_baja,
                   puesto_sel.id_puesto,
                   pp.nombre AS puesto_nombre,
                   pp.departamento_id AS dept_id,
                   d.nombre AS dept_nombre,
                   LOWER(TRIM(COALESCE(pl.clave, ''))) AS puesto_legacy
            FROM persona p
            INNER JOIN (
                SELECT activo.id_persona, activo.dept_id, activo.id_puesto
                FROM (
                    SELECT ap.id_persona, pp.departamento_id AS dept_id, MIN(ap.id_puesto) AS id_puesto
                    FROM asigna_puesto ap
                    INNER JOIN puesto pp ON pp.id = ap.id_puesto
                    INNER JOIN departamento d ON d.id = pp.departamento_id
                    INNER JOIN (
                        SELECT ap2.id_persona, pp2.departamento_id, MAX(pp2.nivel) AS max_nivel
                        FROM asigna_puesto ap2
                        INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                        INNER JOIN departamento d2 ON d2.id = pp2.departamento_id
                        WHERE ap2.activo = 1
                          AND (d2.nombre LIKE 'Campo 1-7%' OR d2.nombre LIKE 'Campo 8-30%' OR d2.nombre LIKE 'Campo 30+%')
                        GROUP BY ap2.id_persona, pp2.departamento_id
                    ) sel ON sel.id_persona = ap.id_persona
                         AND sel.departamento_id = pp.departamento_id
                         AND sel.max_nivel = pp.nivel
                    WHERE ap.activo = 1
                      AND (d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-30%' OR d.nombre LIKE 'Campo 30+%')
                    GROUP BY ap.id_persona, pp.departamento_id
                ) activo

                UNION

                SELECT todos.id_persona, todos.dept_id, todos.id_puesto
                FROM (
                    SELECT ap.id_persona, pp.departamento_id AS dept_id, MIN(ap.id_puesto) AS id_puesto
                    FROM asigna_puesto ap
                    INNER JOIN puesto pp ON pp.id = ap.id_puesto
                    INNER JOIN departamento d ON d.id = pp.departamento_id
                    INNER JOIN (
                        SELECT ap2.id_persona, pp2.departamento_id, MAX(pp2.nivel) AS max_nivel
                        FROM asigna_puesto ap2
                        INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                        INNER JOIN departamento d2 ON d2.id = pp2.departamento_id
                        WHERE d2.nombre LIKE 'Campo 1-7%' OR d2.nombre LIKE 'Campo 8-30%' OR d2.nombre LIKE 'Campo 30+%'
                        GROUP BY ap2.id_persona, pp2.departamento_id
                    ) sel ON sel.id_persona = ap.id_persona
                         AND sel.departamento_id = pp.departamento_id
                         AND sel.max_nivel = pp.nivel
                    WHERE d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-30%' OR d.nombre LIKE 'Campo 30+%'
                    GROUP BY ap.id_persona, pp.departamento_id
                ) todos
                LEFT JOIN (
                    SELECT DISTINCT ap.id_persona
                    FROM asigna_puesto ap
                    INNER JOIN puesto pp ON pp.id = ap.id_puesto
                    INNER JOIN departamento d ON d.id = pp.departamento_id
                    WHERE ap.activo = 1
                      AND (d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-30%' OR d.nombre LIKE 'Campo 30+%')
                ) activo_keys ON activo_keys.id_persona = todos.id_persona
                WHERE activo_keys.id_persona IS NULL
            ) puesto_sel ON puesto_sel.id_persona = p.id
            INNER JOIN puesto pp ON pp.id = puesto_sel.id_puesto
            INNER JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN (
                SELECT bp1.id_persona, bp1.fecha_baja
                FROM baja_persona bp1
                INNER JOIN (
                    SELECT id_persona, MAX(id) AS max_id
                    FROM baja_persona
                    GROUP BY id_persona
                ) ult_bp ON ult_bp.id_persona = bp1.id_persona AND ult_bp.max_id = bp1.id
            ) bp ON bp.id_persona = p.id
            LEFT JOIN equivalencias_legacy_puestos el ON el.id_puesto = pp.id
            LEFT JOIN puestos_legacy pl ON pl.id = el.id_puesto_legacy
            WHERE p.estatus IN ('Activo', 'Baja')
              AND UPPER(TRIM(COALESCE(p.user_name, ''))) <> 'REPORTERIA'
              AND (d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-30%' OR d.nombre LIKE 'Campo 30+%')
            "
        );
    }

    /**
     * @return array<int, string>
     */
    private function cargarAusenciasActivas(Database $db): array
    {
        $rows = $db->queryAll(
            "
            SELECT a.id_persona, ra.nombre AS razon_ausencia
            FROM ausencia a
            INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
            INNER JOIN (
                SELECT id_persona, MIN(id) AS min_id
                FROM ausencia
                WHERE activo = 1
                  AND NOW() BETWEEN fecha_inicio AND fecha_fin
                GROUP BY id_persona
            ) pick ON pick.min_id = a.id
            "
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id_persona']] = strtolower(trim((string)$row['razon_ausencia']));
        }
        return $map;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cargarPersonasJerarquia(Database $db): array
    {
        $rows = $db->queryAll(
            "
            SELECT id, nombres, segundo_nombre, apellidop, apellidom, estatus
            FROM persona
            WHERE UPPER(TRIM(COALESCE(user_name, ''))) <> 'REPORTERIA'
            "
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id']] = $row;
        }
        return $map;
    }

    /**
     * Mapa de departamentos de campo por persona para resolver jefes aunque el jefe
     * este de baja pero conserve subordinados visibles, igual que el organigrama.
     *
     * @return array<int, array<int, bool>>
     */
    private function cargarDepartamentosCampoPorPersona(Database $db): array
    {
        $rows = $db->queryAll(
            "
            SELECT activo.id_persona, activo.dept_id
            FROM (
                SELECT DISTINCT ap.id_persona, pp.departamento_id AS dept_id
                FROM asigna_puesto ap
                INNER JOIN puesto pp ON pp.id = ap.id_puesto
                INNER JOIN departamento d ON d.id = pp.departamento_id
                WHERE ap.activo = 1
                  AND (d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-30%' OR d.nombre LIKE 'Campo 30+%')
            ) activo

            UNION

            SELECT historico.id_persona, historico.dept_id
            FROM (
                SELECT DISTINCT ap.id_persona, pp.departamento_id AS dept_id
                FROM asigna_puesto ap
                INNER JOIN puesto pp ON pp.id = ap.id_puesto
                INNER JOIN departamento d ON d.id = pp.departamento_id
                WHERE d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-30%' OR d.nombre LIKE 'Campo 30+%'
            ) historico
            LEFT JOIN (
                SELECT DISTINCT ap.id_persona
                FROM asigna_puesto ap
                INNER JOIN puesto pp ON pp.id = ap.id_puesto
                INNER JOIN departamento d ON d.id = pp.departamento_id
                WHERE ap.activo = 1
                  AND (d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-30%' OR d.nombre LIKE 'Campo 30+%')
            ) activo_keys ON activo_keys.id_persona = historico.id_persona
            WHERE activo_keys.id_persona IS NULL
            "
        );

        $map = [];
        foreach ($rows as $row) {
            $personaId = (int)($row['id_persona'] ?? 0);
            $deptId = (int)($row['dept_id'] ?? 0);
            if ($personaId > 0 && $deptId > 0) {
                if (!isset($map[$personaId])) {
                    $map[$personaId] = [];
                }
                $map[$personaId][$deptId] = true;
            }
        }
        return $map;
    }

    /**
     * @return array<int, array{id_jefe:?int,id_jefe_vacante:?int}>
     */
    private function cargarJefes(Database $db): array
    {
        $rows = $db->queryAll(
            "
            SELECT aj.id_persona,
                   aj.id_jefe,
                   v.id_jefe AS id_jefe_vacante
            FROM (
                SELECT
                    a.id_persona,
                    a.id_jefe,
                    a.id_vacante_jefe,
                    ROW_NUMBER() OVER (
                        PARTITION BY a.id_persona
                        ORDER BY
                            CASE
                                WHEN (a.fecha_inicio IS NULL OR a.fecha_inicio <= CURDATE())
                                 AND (a.fecha_fin IS NULL OR a.fecha_fin >= CURDATE())
                                THEN 1 ELSE 0
                            END DESC,
                            CASE
                                WHEN a.fecha_inicio IS NULL OR a.fecha_inicio <= CURDATE()
                                THEN 1 ELSE 0
                            END DESC,
                            COALESCE(a.fecha_inicio, '1000-01-01') DESC,
                            a.id DESC
                    ) AS rn
                FROM asigna_jefe a
            ) aj
            LEFT JOIN vacantes_personal v ON v.id = aj.id_vacante_jefe
            WHERE aj.rn = 1
            "
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id_persona']] = [
                'id_jefe' => isset($row['id_jefe']) ? (int)$row['id_jefe'] : null,
                'id_jefe_vacante' => isset($row['id_jefe_vacante']) ? (int)$row['id_jefe_vacante'] : null,
            ];
        }
        return $map;
    }

    /**
     * @return array<int, string>
     */
    private function cargarPuestosLegacy(Database $db): array
    {
        $activo = $db->queryAll(
            "
            SELECT x.id_persona, LOWER(TRIM(pl.clave)) AS puesto_legacy_clave
            FROM (
                SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
                FROM asigna_puesto ap
                INNER JOIN puesto pp ON pp.id = ap.id_puesto
                INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = ap.id_puesto
                INNER JOIN (
                    SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel
                    FROM asigna_puesto ap2
                    INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                    INNER JOIN equivalencias_legacy_puestos el2 ON el2.id_puesto = ap2.id_puesto
                    WHERE ap2.activo = 1
                    GROUP BY ap2.id_persona
                ) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel
                WHERE ap.activo = 1
                GROUP BY ap.id_persona
            ) x
            INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = x.id_puesto
            INNER JOIN puestos_legacy pl ON pl.id = el.id_puesto_legacy
            "
        );
        $fallback = $db->queryAll(
            "
            SELECT x.id_persona, LOWER(TRIM(pl.clave)) AS puesto_legacy_clave
            FROM (
                SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
                FROM asigna_puesto ap
                INNER JOIN puesto pp ON pp.id = ap.id_puesto
                INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = ap.id_puesto
                INNER JOIN (
                    SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel
                    FROM asigna_puesto ap2
                    INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                    INNER JOIN equivalencias_legacy_puestos el2 ON el2.id_puesto = ap2.id_puesto
                    GROUP BY ap2.id_persona
                ) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel
                GROUP BY ap.id_persona
            ) x
            INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = x.id_puesto
            INNER JOIN puestos_legacy pl ON pl.id = el.id_puesto_legacy
            "
        );

        $map = [];
        foreach ($activo as $row) {
            $map[(int)$row['id_persona']] = (string)$row['puesto_legacy_clave'];
        }
        foreach ($fallback as $row) {
            $pid = (int)$row['id_persona'];
            if (!isset($map[$pid])) {
                $map[$pid] = (string)$row['puesto_legacy_clave'];
            }
        }
        return $map;
    }

    /**
     * @param array<int, array<string, mixed>> $personas
     * @param array<int, array{id_jefe:?int,id_jefe_vacante:?int}> $jefes
     * @param array<int, string> $legacy
     * @param array<int, string> $ausencias
     * @param array<int, array<int, bool>> $deptMap
     * @return array<string, string>
     */
    private function resolverJerarquia(int $personaId, int $deptObjetivo, array $personas, array $jefes, array $legacy, array $ausencias, array $deptMap): array
    {
        $resultado = [];
        foreach (self::ROLES_JERARQUIA as $rol) {
            $resultado[$rol] = '';
            $resultado[$rol . '_estatus'] = '';
        }

        $actual = $personaId;
        $visitados = [];
        $deptObjetivo = $deptObjetivo > 0 ? $deptObjetivo : null;

        for ($i = 0; $i < 8; $i++) {
            if ($actual < 1 || isset($visitados[$actual])) {
                break;
            }
            $visitados[$actual] = true;

            $jefeInfo = $jefes[$actual] ?? null;
            if ($jefeInfo === null) {
                break;
            }

            $jefeId = (int)($jefeInfo['id_jefe'] ?? 0);
            if ($jefeId < 1) {
                $jefeId = (int)($jefeInfo['id_jefe_vacante'] ?? 0);
            }
            if ($jefeId < 1 || !isset($personas[$jefeId])) {
                break;
            }
            if ($deptObjetivo !== null && empty($deptMap[$jefeId][$deptObjetivo])) {
                break;
            }

            $rolJefe = strtolower(trim((string)($legacy[$jefeId] ?? '')));
            if (in_array($rolJefe, self::ROLES_JERARQUIA, true) && $resultado[$rolJefe] === '') {
                $resultado[$rolJefe] = $this->armarNombre($personas[$jefeId]);
                $resultado[$rolJefe . '_estatus'] = $this->calcularEstatus(
                    $jefeId,
                    (string)($personas[$jefeId]['estatus'] ?? ''),
                    $ausencias
                );
            }

            $actual = $jefeId;
        }

        return $resultado;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function armarNombre(array $row): string
    {
        $partes = [
            $row['apellidop'] ?? '',
            $row['apellidom'] ?? '',
            trim((string)($row['nombres'] ?? '') . ' ' . (string)($row['segundo_nombre'] ?? '')),
        ];
        $partes = array_filter(array_map(static function ($v): string {
            return mb_strtoupper(trim((string)$v), 'UTF-8');
        }, $partes));

        return implode(' ', $partes);
    }

    /**
     * @param array<int, string> $ausencias
     */
    private function calcularEstatus(int $personaId, string $estatus, array $ausencias): string
    {
        if ($estatus === 'Baja') {
            return 'baja';
        }
        if (isset($ausencias[$personaId]) && $ausencias[$personaId] !== '') {
            return $ausencias[$personaId];
        }
        if ($estatus === 'Activo') {
            return 'activo';
        }

        return strtolower(trim($estatus));
    }

    /**
     * @param list<array<string, string>> $rows
     */
    private function crearSpreadsheet(array $rows): Spreadsheet
    {
        $headers = [
            'external_id' => 'external_id',
            'nombre_completo' => 'nombre_completo',
            'estatus' => 'estatus',
            'fecha_baja' => 'fecha_baja',
            'es_gestor' => 'es_gestor',
            'puesto_legacy' => 'puesto_legacy',
            'puesto_actual' => 'puesto_actual',
            'departamento' => 'departamento',
            'supervisor' => 'supervisor',
            'supervisor_estatus' => 'supervisor_estatus',
            'subgerente' => 'subgerente',
            'subgerente_estatus' => 'subgerente_estatus',
            'gerente' => 'gerente',
            'gerente_estatus' => 'gerente_estatus',
            'subdirector' => 'subdirector',
            'subdirector_estatus' => 'subdirector_estatus',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Campo');

        $col = 1;
        foreach ($headers as $label) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '1', $label);
            $col++;
        }
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));

        $rowNum = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach (array_keys($headers) as $key) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . $rowNum, (string)($row[$key] ?? ''));
                $col++;
            }
            if (strtolower(trim((string)($row['estatus'] ?? ''))) === 'baja') {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCE4E4']],
                    'font' => ['color' => ['rgb' => '7F1D1D']],
                ]);
            }
            $rowNum++;
        }

        $lastRow = max(1, $rowNum - 1);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2F4D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9E2EF']]],
        ]);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
