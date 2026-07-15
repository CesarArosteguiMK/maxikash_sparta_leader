<?php

namespace Services;

/**
 * Public inventory of the data sources already integrated with Sparta.
 *
 * Connection details and credentials deliberately stay in the server config.
 * Leonidas only receives logical names and approved read capabilities.
 */
class LeonidasDataSourceRegistry
{
    /** @return array<int, array<string, mixed>> */
    public function catalogoPublico(): array
    {
        return [
            [
                'id' => 'sparta_principal',
                'nombre' => 'Sparta principal',
                'tipo' => 'base_de_datos',
                'alcance' => ['capital humano', 'candidatos', 'estructura', 'modulos', 'permisos', 'auditoria'],
            ],
            [
                'id' => 'legacy',
                'nombre' => 'Legacy',
                'tipo' => 'base_de_datos',
                'alcance' => ['usuarios', 'creditos', 'gestiones', 'cartera', 'operacion historica'],
            ],
            [
                'id' => 'geografia',
                'nombre' => 'Geografia',
                'tipo' => 'base_de_datos',
                'alcance' => ['paises', 'estados', 'municipios', 'colonias', 'codigos postales'],
            ],
            [
                'id' => 'segundometro',
                'nombre' => 'Segundometro',
                'tipo' => 'base_de_datos',
                'alcance' => ['buckets', 'transiciones', 'morosidad', 'cortes de cartera'],
            ],
            [
                'id' => 'maxi_produccion',
                'nombre' => 'Maxi produccion',
                'tipo' => 'base_de_datos',
                'alcance' => ['operacion productiva', 'cartera', 'creditos', 'pagos'],
            ],
            [
                'id' => 'maxi_guatemala',
                'nombre' => 'Maxi Guatemala',
                'tipo' => 'base_de_datos',
                'alcance' => ['cartera Guatemala', 'creditos Guatemala', 'estado de cuenta Guatemala'],
            ],
            [
                'id' => 'aws_operativa',
                'nombre' => 'AWS operativa',
                'tipo' => 'base_de_datos',
                'alcance' => ['servicios operativos alojados en AWS'],
            ],
            [
                'id' => 's2_estado_cuenta',
                'nombre' => 'API S2 Estado de Cuenta',
                'tipo' => 'api',
                'alcance' => ['estado de cuenta por credito', 'pagos', 'saldos', 'mora', 'fecha de corte'],
                'requisitos' => ['id de credito', 'fecha de corte opcional'],
            ],
            [
                'id' => 'sabueso',
                'nombre' => 'Sabueso',
                'tipo' => 'servicio_operativo',
                'alcance' => ['tickets', 'pagos puntuales', 'verificaciones de cobranza'],
            ],
            [
                'id' => 'gastos_cobranza',
                'nombre' => 'Gastos de Cobranza',
                'tipo' => 'servicio_operativo',
                'alcance' => ['cargos generados', 'recuperado', 'pendiente', 'condonado'],
            ],
        ];
    }

    public function resumen(): string
    {
        $nombres = array_map(
            static fn(array $fuente): string => (string) $fuente['nombre'],
            $this->catalogoPublico()
        );

        return 'Tengo acceso de consulta mediante adaptadores auditados a: ' . implode(', ', $nombres)
            . '. Las credenciales, direcciones internas y consultas SQL no se exponen en el chat.';
    }

    /** @return string[] */
    public function idsBasesDeDatos(): array
    {
        return array_values(array_map(
            static fn(array $fuente): string => (string) $fuente['id'],
            array_filter(
                $this->catalogoPublico(),
                static fn(array $fuente): bool => ($fuente['tipo'] ?? '') === 'base_de_datos'
            )
        ));
    }
}
