<?php

namespace Services;

use Models\Login;

final class LeonidasAccessService
{
    public const MODULO_ASISTENTE_SPARTA = 194;
    public const PERSONA_LAZARO_RAUDEL = 878;

    private static array $cacheAcceso = [];

    public static function esAccesoPermanente(int $personaId): bool
    {
        return $personaId === self::PERSONA_LAZARO_RAUDEL;
    }

    public static function tieneAcceso(int $personaId): bool
    {
        if ($personaId <= 0) {
            return false;
        }

        if (self::esAccesoPermanente($personaId)) {
            return true;
        }

        if (array_key_exists($personaId, self::$cacheAcceso)) {
            return self::$cacheAcceso[$personaId];
        }

        $modulos = Login::getModulosUsuario($personaId);
        if (!is_array($modulos)) {
            $modulos = [];
        }
        $modulos = array_values(array_unique(array_map('intval', $modulos)));
        $autorizado = in_array(self::MODULO_ASISTENTE_SPARTA, $modulos, true);

        $personaSesion = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaSesion === $personaId) {
            $_SESSION['modulos'] = $modulos;
        }

        self::$cacheAcceso[$personaId] = $autorizado;
        return $autorizado;
    }

    public static function actorAutorizado(): array
    {
        $actor = LeonidasMessagingService::actorSesion();
        if (!self::tieneAcceso((int) $actor['actor_id'])) {
            throw new \DomainException('Tu usuario no tiene asignado el permiso especial Asistente de Sparta.');
        }

        return $actor;
    }
}
