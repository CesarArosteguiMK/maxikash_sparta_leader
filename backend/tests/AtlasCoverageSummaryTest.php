<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/controllers/Atlas.php';

final class AtlasCoverageSummaryTest extends TestCase
{
    public function testNormalizesTheSameCoverageSummaryContractUsedByTheApp(): void
    {
        $reflection = new ReflectionClass(\controllers\Atlas::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalizarResumenCoberturaAsistencia');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            'resumen' => [
                'mes' => '2026-07',
                'agencias_asignadas' => 19,
                'total_agencias_en_ruta' => 10,
                'agencias_visitadas' => 9,
                'agencias_pendientes' => 1,
                'porcentaje_cobertura' => 90,
                'total_visitas_programadas' => 27,
                'visitas_realizadas' => 9,
                'visitas_vencidas' => 12,
            ],
            'dias' => [[
                'visitas' => [
                    ['fk_sucursal' => 190, 'estatus_visita' => 'vencida', 'checkin_at' => null],
                    ['fk_sucursal' => 190, 'estatus_visita' => 'vencida', 'checkin_at' => null],
                    ['fk_sucursal' => 191, 'estatus_visita' => 'vencida', 'checkin_at' => null],
                    ['fk_sucursal' => 192, 'estatus_visita' => 'vencida', 'checkin_at' => '2026-07-10T09:00:00'],
                    ['fk_sucursal' => 193, 'estatus_visita' => 'pendiente', 'checkin_at' => null],
                ],
            ]],
        ], '2026-07');

        $this->assertSame([
            'mes' => '2026-07',
            'agencias_asignadas' => 19,
            'agencias_en_ruta' => 10,
            'agencias_visitadas' => 9,
            'agencias_pendientes' => 1,
            'porcentaje_cobertura' => 90,
            'total_visitas_programadas' => 27,
            'visitas_realizadas' => 9,
            'visitas_vencidas' => 12,
            'agencias_agendadas_sin_checkin' => 2,
        ], $result);
    }

    public function testAssignedAgenciesCannotBeLowerThanAgenciesAlreadyInRoutes(): void
    {
        $reflection = new ReflectionClass(\controllers\Atlas::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalizarResumenCoberturaAsistencia');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            'resumen' => [
                'agencias_asignadas' => 3,
                'total_agencias_en_ruta' => 5,
            ],
        ], '2026-07');

        $this->assertSame(5, $result['agencias_asignadas']);
        $this->assertSame(5, $result['agencias_en_ruta']);
    }

    public function testNormalizesMobileCreditStatusesByBranchAndMonth(): void
    {
        $reflection = new ReflectionClass(\controllers\Atlas::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalizarCreditosSucursalesAsistencia');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            'periodo' => [
                'fecha_inicio' => '2026-07-01',
                'fecha_fin' => '2026-07-31',
            ],
            'total_formula' => 'COUNT DISTINCT id_oferta',
            'registros' => [
                [
                    'fk_sucursal' => 1895,
                    'sucursal' => 'ITALIKA TAPACHULA',
                    'mes' => '2026-07',
                    'creditos_pendientes' => 45,
                    'creditos_rezagados' => 3,
                    'creditos_gestionados' => 0,
                    'creditos_dictaminados' => 0,
                    'creditos_vendidos' => 8,
                    'total_creditos' => 56,
                ],
                [
                    'fk_sucursal' => 0,
                    'mes' => '2026-07',
                ],
                [
                    'fk_sucursal' => 190,
                    'mes' => 'julio',
                ],
            ],
        ]);

        $this->assertSame([
            'periodo' => [
                'fecha_inicio' => '2026-07-01',
                'fecha_fin' => '2026-07-31',
            ],
            'registros' => [[
                'fk_sucursal' => 1895,
                'sucursal' => 'ITALIKA TAPACHULA',
                'mes' => '2026-07',
                'creditos_pendientes' => 45,
                'creditos_rezagados' => 3,
                'creditos_gestionados' => 0,
                'creditos_dictaminados' => 0,
                'creditos_vendidos' => 8,
                'total_creditos' => 56,
            ]],
            'total' => 1,
            'total_formula' => 'COUNT DISTINCT id_oferta',
        ], $result);

        $differentStatuses = $method->invoke($controller, [
            'registros' => [[
                'fk_sucursal' => 190,
                'mes' => '2026-07',
                'creditos_gestionados' => 4,
                'creditos_dictaminados' => 2,
            ]],
        ]);
        $this->assertSame(4, $differentStatuses['registros'][0]['creditos_gestionados']);
        $this->assertSame(2, $differentStatuses['registros'][0]['creditos_dictaminados']);
    }

    public function testGroupsAttendanceByCollaboratorAndCountsOneAttendancePerDay(): void
    {
        $reflection = new ReflectionClass(\controllers\Atlas::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('agruparAsistenciasPorColaborador');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            [
                'colaborador_persona_id' => 10,
                'colaborador' => 'ANA GESTORA',
                'numero_empleado' => 'A-10',
                'puesto' => 'ASESOR',
                'fecha' => '2026-07-20',
                'fk_sucursal' => 190,
                'hora_llegada' => null,
                'gestiones_realizadas' => 2,
                'pendientes_por_gestionar' => 5,
                'es_visita' => true,
            ],
            [
                'colaborador_persona_id' => 10,
                'colaborador' => 'ANA GESTORA',
                'fecha' => '2026-07-20',
                'fk_sucursal' => 191,
                'hora_llegada' => '09:30:00',
                'gestiones_realizadas' => 3,
                'pendientes_por_gestionar' => 7,
                'es_visita' => true,
            ],
            [
                'colaborador_persona_id' => 10,
                'colaborador' => 'ANA GESTORA',
                'fecha' => '2026-07-21',
                'fk_sucursal' => 190,
                'hora_llegada' => null,
                'gestiones_realizadas' => 0,
                'pendientes_por_gestionar' => 6,
                'es_visita' => true,
            ],
            [
                'colaborador_persona_id' => 10,
                'colaborador' => 'ANA GESTORA',
                'fecha' => '2026-07-22',
                'fk_sucursal' => 190,
                'hora_gestion' => '11:00:00',
                'gestiones_realizadas' => 1,
                'pendientes_por_gestionar' => 99,
                'es_visita' => false,
            ],
        ]);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['dias_con_asistencia']);
        $this->assertSame(1, $result[0]['dias_sin_asistencia']);
        $this->assertSame(6, $result[0]['gestiones_realizadas']);
        $this->assertSame(13, $result[0]['gestiones_pendientes']);
    }

    public function testDownloadUsesAnExplicitDateRangeModal(): void
    {
        $view = (string)file_get_contents(dirname(__DIR__) . '/views/atlas_asistencias.php');

        $this->assertStringContainsString('id="atlasAttendanceDownloadStart"', $view);
        $this->assertStringContainsString('id="atlasAttendanceDownloadEnd"', $view);
        $this->assertStringContainsString('Descargar archivo', $view);
        $this->assertStringContainsString('exportQuery(downloadStartInput.value, downloadEndInput.value)', $view);
    }

    public function testVisitImageActionExistsOnlyInAttendanceDetail(): void
    {
        $backend = dirname(__DIR__);
        $view = (string)file_get_contents($backend . '/views/atlas_asistencias.php');
        $controller = (string)file_get_contents($backend . '/controllers/Atlas.php');
        $exportStart = strpos($controller, 'public function descargarReporteAsistencias');
        $exportEnd = strpos($controller, 'private function normalizarReporteAsistencias');
        $exportMethod = substr($controller, $exportStart, $exportEnd - $exportStart);

        $this->assertStringContainsString('const rowImageEvidence = (row)', $view);
        $this->assertStringContainsString('Ver imagen', $view);
        $this->assertStringContainsString('Sin imagen', $view);
        $this->assertStringContainsString('/Atlas/verEvidenciaAsistencia?id=', $view);
        $this->assertStringNotContainsString("['Imagen'", $exportMethod);
        $this->assertStringNotContainsString("['Fotografía'", $exportMethod);
    }

    public function testAttendanceDetailConsolidatesManagementColumnsWithoutPortfolioSection(): void
    {
        $backend = dirname(__DIR__);
        $view = (string)file_get_contents($backend . '/views/atlas_asistencias.php');
        $controller = (string)file_get_contents($backend . '/controllers/Atlas.php');
        $detailStart = strpos($view, 'const attendanceDetailTableHtml = (rows)');
        $detailEnd = strpos($view, 'const evidenceMedia = (evidence)');
        $detailCode = substr($view, $detailStart, $detailEnd - $detailStart);

        $this->assertStringContainsString('Gestiones realizadas', $detailCode);
        $this->assertStringContainsString('Pendientes', $detailCode);
        $this->assertStringContainsString('Totales', $detailCode);
        $this->assertStringContainsString('creditMetricCellsHtml(row)', $detailCode);
        $this->assertStringContainsString('metric.creditos_pendientes', $view);
        $this->assertStringContainsString('metric.creditos_rezagados', $view);
        $this->assertStringContainsString('metric.creditos_dictaminados', $view);
        $this->assertStringContainsString('metric.creditos_vendidos', $view);
        $this->assertStringContainsString('/Atlas/getCreditosSucursalesAsistencia?', $view);
        $this->assertStringContainsString(
            '/api/atlas/admin/reportes/asistencias/creditos-sucursales',
            $controller
        );
        $this->assertStringNotContainsString('pendientes_por_gestionar', $detailCode);
        $this->assertStringNotContainsString('localStorage', substr(
            $view,
            strpos($view, 'const loadAttendanceCreditMetrics = async'),
            strpos($view, 'const groupEvidenceEntries = (group)') - strpos($view, 'const loadAttendanceCreditMetrics = async')
        ));
        $this->assertStringNotContainsString('Cartera por sucursal', $view);
        $this->assertStringNotContainsString('atlasAttendancePortfolioSection', $view);
        $this->assertStringNotContainsString('portfolioDetailHtml', $view);
    }
}
