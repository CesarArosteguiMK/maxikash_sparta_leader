<?php

namespace Core;

/**
 * Columnas DataTables del panel admin de tickets (compartida entre módulos).
 */
class PanelAdminTicketTable
{
    public static function getTitulosColumnasPanelAdminPorCategoria(string $categoria): array
    {
        $c = strtolower(preg_replace('/[^a-z0-9_]/', '', $categoria));
        $base = [
            'folio' => 'Folio / Tipo',
            'estado' => 'Estado',
            'prioridad' => 'Prioridad',
            'ref' => 'Crédito',
            'fechas' => 'Fechas',
            'creador' => 'Quién levantó',
            'asignado' => 'Asignado a',
            'tiempo' => 'Tiempo para visitar / Prórroga',
            'ds' => 'Resultado DS',
        ];
        $porModulo = [
            '' => ['folio' => 'Folio / Tipo', 'ref' => 'Crédito / referencia'],
            'sabueso' => ['ref' => 'Crédito', 'fechas' => 'Creación y vencimiento'],
            'validaciones' => [
                'folio' => 'Folio / Validación',
                'prioridad' => 'Urgencia',
                'ref' => 'Nota / enlace',
                'fechas' => 'Alta y vencimiento',
                'tiempo' => 'Tiempo para validar',
            ],
            'viaticos' => [
                'folio' => 'Folio / Viático',
                'ref' => 'Concepto / tipo',
                'fechas' => 'Fechas del trámite',
            ],
            'aplicaciones_de_pago' => [
                'folio' => 'Folio / solicitud',
                'ref' => 'Referencia de pago',
            ],
            'plantilla' => [
                'folio' => 'Folio / plantilla',
                'ref' => 'Plantilla / asunto',
            ],
            'atencion_cliente' => [
                'folio' => 'Folio / caso',
                'ref' => 'Asunto / canal',
            ],
            'credito_problematico' => [
                'folio' => 'Folio / reporte',
                'ref' => 'Crédito',
                'prioridad' => 'Severidad',
            ],
            'aclaracion_credito' => [
                'folio' => 'Folio / aclaración',
                'ref' => 'Crédito',
            ],
        ];
        if ($c === '') {
            return array_merge($base, $porModulo['']);
        }

        return array_merge($base, $porModulo[$c] ?? []);
    }

    /**
     * Índices de columna DataTables (0-based) a ocultar en panel por módulo.
     * Debe coincidir con getColumnsConfig(true): 0 control, 1 fecha oculta, 2 folio, 3 estado, 4 prioridad,
     * 5 ref/crédito, 6 fechas, 7 creador, 8 asignado, 9 tiempo visita, 10 DS, 11 dictamen/visto, 12 acciones.
     */
    public static function getIndicesColumnasOcultasModulo(string $categoria): array
    {
        $c = strtolower(preg_replace('/[^a-z0-9_]/', '', $categoria));
        if ($c === 'validaciones') {
            return [5, 9, 10, 11];
        }

        return [10, 11];
    }

    public static function getColumnsConfig(bool $esAdmin, string $categoriaPanel = ''): array
    {
        $T = $esAdmin ? self::getTitulosColumnasPanelAdminPorCategoria($categoriaPanel) : null;
        $base = [
            ['data' => null, 'defaultContent' => '', 'className' => 'control', 'orderable' => false],
            ['data' => '_fecha_creacion', 'title' => '', 'visible' => false, 'orderable' => true],
            ['data' => 'folio_tipo', 'title' => $esAdmin ? $T['folio'] : 'Folio / Tipo'],
        ];
        if (!$esAdmin) {
            $base[] = ['data' => 'gestion', 'title' => 'Gestión', 'orderable' => false];
        }
        $base = array_merge($base, [
            ['data' => 'estado', 'title' => $esAdmin ? $T['estado'] : 'Estado'],
            ['data' => 'prioridad', 'title' => $esAdmin ? $T['prioridad'] : 'Prioridad'],
            ['data' => 'credito', 'title' => $esAdmin ? $T['ref'] : 'Crédito'],
            ['data' => 'fechas', 'title' => $esAdmin ? $T['fechas'] : 'Fechas'],
        ]);
        if ($esAdmin) {
            $base[] = ['data' => 'creador', 'title' => $T['creador']];
            $base[] = ['data' => 'asignado', 'title' => $T['asignado']];
            $base[] = ['data' => 'tiempo_visitar', 'title' => $T['tiempo'], 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'ds_resultado', 'title' => $T['ds'], 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'dictamen_visto', 'title' => '', 'orderable' => false, 'className' => 'text-end'];
        } else {
            $base[] = ['data' => 'tiempo_visitar', 'title' => 'Tiempo para visitar / Prórroga', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'ds_resultado', 'title' => 'Resultado DS', 'orderable' => false, 'className' => 'text-center'];
            $base[] = ['data' => 'dictamen_visto', 'title' => '', 'orderable' => false, 'className' => 'text-end'];
        }
        $base[] = ['data' => 'acciones', 'title' => 'Acciones', 'orderable' => false];

        return [
            'esAdminJs' => $esAdmin ? 'true' : 'false',
            'columnsJs' => json_encode($base, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP),
        ];
    }
}
