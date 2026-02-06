<?php

/**
 * Carga de API keys desde BD (tabla config_api, valor en texto plano).
 * Para uso interno (Gemini, OpenAI, etc.) se lee el valor completo.
 * Para mostrar en pantalla usar config_api_for_display() y no exponer el valor completo.
 */

if (!function_exists('config_api_load_from_db')) {
    /**
     * Carga todas las claves desde config_api (valor en texto plano).
     * Solo para uso interno (llamadas a APIs). No usar para mostrar en pantalla.
     *
     * @return array [ clave => valor, ... ]
     */
    function config_api_load_from_db(): array
    {
        try {
            $db = new \Core\Database();
            $rows = $db->queryAll('SELECT clave, valor FROM config_api');
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $clave = $row['clave'] ?? '';
            $valor = $row['valor'] ?? '';
            if ($clave !== '') {
                $out[$clave] = (string) $valor;
            }
        }
        return $out;
    }
}

if (!function_exists('config_api_for_display')) {
    /**
     * Devuelve la config para mostrar en pantalla: valores enmascarados (no se muestra la key completa).
     * Útil para listar qué claves están configuradas sin exponer el valor.
     *
     * @param int $visibleChars Últimos N caracteres visibles (resto ***). 0 = todo oculto.
     * @return array [ clave => '***ultimos4', ... ]
     */
    function config_api_for_display(int $visibleChars = 4): array
    {
        $raw = config_api_load_from_db();
        $out = [];
        foreach ($raw as $clave => $valor) {
            if ($valor === '') {
                $out[$clave] = '(vacío)';
            } elseif ($visibleChars <= 0 || strlen($valor) <= $visibleChars) {
                $out[$clave] = '***';
            } else {
                $out[$clave] = '***' . substr($valor, -$visibleChars);
            }
        }
        return $out;
    }
}
