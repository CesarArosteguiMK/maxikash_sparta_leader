<?php

namespace Services;

/**
 * Applies the authenticated user's Sparta permissions to Leonidas domains.
 */
class LeonidasDomainAccessService
{
    /** @var array<string, list<string>> */
    private const DOMAIN_PERMISSION_KEYS = [
        'creditos' => ['estado_cuenta', 'aclaraciones_credito'],
        'capital_humano' => [
            'rrhh_lectura',
            'auditoria_rrhh',
            'candidatos',
            'documentos',
            'vacaciones',
        ],
        'convenios' => ['convenio'],
        'motos_adjudicadas' => ['motos'],
        'direcciones' => ['direcciones'],
        'legacy' => ['legacy'],
        'atlas' => ['atlas'],
        'tickets' => ['tickets'],
        'analitica' => [
            'analitica',
            'bucket',
            'comparativas',
            'segundometro',
            'primeros_pagos',
        ],
        'gastos_cobranza' => ['gastos_cobranza'],
        'organizacion' => ['organizacion', 'estructura'],
        'servicios' => ['servicios', 'servicios_locales'],
    ];

    /**
     * @param array<string, mixed>|null $domain
     * @param array<string, mixed> $permissions
     * @return array{autorizado: bool, respuesta?: array<string, mixed>}
     */
    public function verificar(?array $domain, array $permissions): array
    {
        if ($domain === null) {
            return ['autorizado' => true];
        }

        $domainId = (string) ($domain['id'] ?? '');
        $keys = self::DOMAIN_PERMISSION_KEYS[$domainId] ?? [];
        foreach ($keys as $key) {
            if (!empty($permissions[$key])) {
                return ['autorizado' => true];
            }
        }

        $name = trim((string) ($domain['nombre'] ?? $domainId));
        return [
            'autorizado' => false,
            'respuesta' => [
                'mensaje' => 'No puedo consultar ' . $name . ' con este perfil porque no tiene '
                    . 'un modulo o permiso autorizado para ese dominio. No se consultaron datos '
                    . 'ni se realizo ningun cambio. Solicita el acceso correspondiente a un administrador.',
                'tipo' => 'dominio_permiso_denegado',
                'dominio' => $domainId,
                'fuente' => 'control_permisos_sparta',
                'motivo' => 'permiso_de_dominio_no_asignado',
            ],
        ];
    }
}
