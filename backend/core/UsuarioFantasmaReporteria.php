<?php

namespace Core;

/**
 * Usuario de solo lectura para el módulo Analítica → Comparativas (modulo_web 60).
 * No debe contarse en métricas agregadas de capital humano / paneles de usuarios.
 */
class UsuarioFantasmaReporteria
{
    public const USER_NAME = 'REPORTERIA';

    /** Módulo web «Comparativas» (rutas comparativas + comparativasAvanceSemanal + JSON). */
    public const MODULO_COMPARATIVAS = 60;

    /** Tras login o al abrir Inicio: tablero «Comparativas — Avance por cortes». */
    public const URL_INICIO_SESION = '/analitica/comparativasAvanceSemanal';

    public static function matchUsername(?string $userName): bool
    {
        return strtoupper(trim((string) $userName)) === self::USER_NAME;
    }

    public static function es(): bool
    {
        return !empty($_SESSION['usuario_fantasma_reporteria'])
            || self::matchUsername($_SESSION['usuario'] ?? null);
    }

    /**
     * Predicado SQL (sin "AND" inicial) para usar en WHERE o en ON de JOIN.
     *
     * @param string $aliasPersona Alias de la tabla persona (p, per, p2, …)
     */
    public static function sqlPredicadoExcluirPersona(string $aliasPersona = 'p'): string
    {
        $a = preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $aliasPersona) ? $aliasPersona : 'p';

        return "UPPER(TRIM({$a}.user_name)) <> '" . self::USER_NAME . "'";
    }

    /**
     * Cuando la tabla es `persona` sin alias: … WHERE … AND esta condición.
     */
    public static function sqlPredicadoExcluirUserNameSinAlias(): string
    {
        return "UPPER(TRIM(user_name)) <> '" . self::USER_NAME . "'";
    }

    /**
     * Condición SQL para excluir la fila persona del usuario fantasma (alias de tabla persona).
     *
     * Uso: concatenar al final del WHERE existente: `WHERE …` . sqlExcluirPersona('p')
     * Para armar arrays de condiciones use {@see sqlPredicadoExcluirPersona}.
     */
    public static function sqlExcluirPersona(string $aliasPersona = 'p'): string
    {
        return ' AND ' . self::sqlPredicadoExcluirPersona($aliasPersona) . ' ';
    }
}
