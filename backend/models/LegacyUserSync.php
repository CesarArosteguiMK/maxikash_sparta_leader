<?php

namespace Models;

use Core\Database;
use Core\DatabaseLegacy;
use Core\Model;

class LegacyUserSync extends Model
{
    private const MODEL_TYPE = 'App\\Models\\User';
    private const ORIGEN_EDITAR_USUARIO = 'editar_usuario_rrhh';
    private const ORIGEN_BAJA_USUARIO = 'baja_usuario_spartan';

    public static function sincronizarDesdeEditarUsuario(int $idPersona, int $idSesion = 0): array
    {
        $db = new Database();
        self::asegurarBitacora($db);

        try {
            $ctx = self::obtenerContextoPersona($db, $idPersona);
            if (!$ctx) {
                return self::registrar($db, [
                    'id_persona' => $idPersona,
                    'id_usuario' => $idSesion,
                    'resultado' => 'error',
                    'mensaje' => 'Persona no encontrada en Spartan.',
                ]);
            }

            if (!self::estaEnAlcance($ctx)) {
                return self::registrar($db, [
                    'id_persona' => $idPersona,
                    'external_id' => $ctx['external_id'] ?? '',
                    'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                    'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                    'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                    'id_usuario' => $idSesion,
                    'resultado' => 'omitido',
                    'mensaje' => 'Usuario fuera de alcance para sincronización Legacy.',
                    'detalle' => ['motivo' => 'No pertenece a segmentos Campo 1-7 / Campo 8-30 de Cobranza.'],
                ]);
            }

            $roleClave = trim((string)($ctx['role_legacy'] ?? ''));
            if ($roleClave === '') {
                return self::registrar($db, [
                    'id_persona' => $idPersona,
                    'external_id' => $ctx['external_id'] ?? '',
                    'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                    'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                    'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                    'id_usuario' => $idSesion,
                    'resultado' => 'error',
                    'mensaje' => 'Puesto Spartan sin equivalencia Legacy configurada.',
                ]);
            }

            $externalId = trim((string)($ctx['external_id'] ?? ''));
            if ($externalId === '') {
                return self::registrar($db, [
                    'id_persona' => $idPersona,
                    'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                    'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                    'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                    'id_usuario' => $idSesion,
                    'resultado' => 'error',
                    'mensaje' => 'La persona no tiene número de empleado para buscar en Legacy.',
                ]);
            }
            if (!self::externalIdValido($externalId)) {
                return self::registrar($db, [
                    'id_persona' => $idPersona,
                    'external_id' => $externalId,
                    'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                    'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                    'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                    'role_legacy' => $roleClave,
                    'id_usuario' => $idSesion,
                    'resultado' => 'omitido',
                    'mensaje' => 'Número de empleado inválido para sincronizar con Legacy.',
                    'detalle' => ['motivo' => 'external_id no numerico'],
                ]);
            }

            $legacy = new DatabaseLegacy();
            $legacyUser = self::buscarUsuarioLegacy($legacy, $externalId);
            $legacyUserCreado = false;
            $legacyUserReactivado = false;
            if (!$legacyUser) {
                $legacyUserBaja = self::buscarUsuarioLegacyIncluyendoBaja($legacy, $externalId);
                if ($legacyUserBaja && !empty($legacyUserBaja['deleted_at'])) {
                    $legacyUser = $legacyUserBaja;
                    $legacyUserReactivado = true;
                }
            }

            $roleLegacy = self::buscarRoleLegacy($legacy, $roleClave);
            if (!$roleLegacy) {
                return self::registrar($db, [
                    'id_persona' => $idPersona,
                    'external_id' => $externalId,
                    'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                    'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                    'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                    'role_legacy' => $roleClave,
                    'id_usuario' => $idSesion,
                    'resultado' => 'error',
                    'mensaje' => 'Role Legacy no existe en tabla roles.',
                ]);
            }

            $legacyUserCreado = !$legacyUser;

            $jerarquia = self::calcularJerarquiaSpartan($db, $idPersona);
            $jerarquiaLegacy = self::resolverJerarquiaLegacy($legacy, $jerarquia);

            $legacy->beginTransaction();
            if ($legacyUserCreado) {
                $legacyUser = self::crearUsuarioLegacyDesdeSpartan($legacy, $ctx, $externalId);
            }
            $estadoAnterior = self::estadoLegacyActual($legacy, (int)$legacyUser['id']);
            if ($legacyUserReactivado) {
                self::reactivarUsuarioLegacy($legacy, (int)$legacyUser['id']);
            }
            $credencialesLegacy = self::resolverCredencialesLegacy($legacy, (int)$legacyUser['id'], $ctx, $externalId);
            self::actualizarUsuarioLegacy($legacy, (int)$legacyUser['id'], $jerarquiaLegacy['ids'], $credencialesLegacy);
            self::actualizarRoleLegacy($legacy, (int)$legacyUser['id'], (int)$roleLegacy['id']);
            $legacy->commit();

            $estadoNuevo = self::estadoLegacyActual($legacy, (int)$legacyUser['id']);
            $passwordActualizado = !empty($credencialesLegacy['password_actualizado']);
            $resultado = $legacyUserCreado || $legacyUserReactivado || $passwordActualizado || !self::sinCambios($estadoAnterior, $estadoNuevo) ? 'actualizado' : 'sin_cambios';
            $mensaje = $legacyUserCreado
                ? 'Usuario creado en Legacy y sincronizado correctamente.'
                : ($legacyUserReactivado
                    ? 'Usuario reactivado en Legacy y sincronizado correctamente.'
                    : ($resultado === 'sin_cambios'
                        ? 'Legacy ya estaba sincronizado.'
                        : 'Usuario Legacy sincronizado correctamente.'));
            if (!empty($jerarquiaLegacy['faltantes'])) {
                $mensaje .= ' Jerarquía incompleta: hay jefes sin usuario Legacy.';
            }

            return self::registrar($db, [
                'id_persona' => $idPersona,
                'external_id' => $externalId,
                'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                'id_usuario_legacy' => (int)$legacyUser['id'],
                'role_legacy' => $roleClave,
                'id_usuario' => $idSesion,
                'resultado' => $resultado,
                'mensaje' => $mensaje,
                'detalle' => [
                    'usuario_legacy_creado' => $legacyUserCreado,
                    'usuario_legacy_reactivado' => $legacyUserReactivado,
                    'antes' => $estadoAnterior,
                    'despues' => $estadoNuevo,
                    'credenciales' => [
                        'username_sincronizado' => $credencialesLegacy['username'] ?? '',
                        'password_actualizado' => $passwordActualizado,
                    ],
                    'jerarquia_spartan' => $jerarquia,
                    'jefes_sin_usuario_legacy' => $jerarquiaLegacy['faltantes'],
                ],
            ]);
        } catch (\Throwable $e) {
            if (isset($legacy)) {
                try { $legacy->rollback(); } catch (\Throwable $rollbackError) {}
            }

            return self::registrar($db, [
                'id_persona' => $idPersona,
                'id_usuario' => $idSesion,
                'resultado' => 'error',
                'mensaje' => 'Error al sincronizar usuario con Legacy.',
                'detalle' => ['error' => $e->getMessage()],
            ]);
        }
    }

    public static function sincronizarPendientes(int $limite = 100, int $idSesion = 0, bool $forzarTodos = false): array
    {
        $limite = max(1, min(300, $limite));
        $db = new Database();
        self::asegurarBitacora($db);

        try {
            $legacy = new DatabaseLegacy();
            $deteccion = self::detectarPendientesSincronizacion($db, $legacy, $limite, $forzarTodos);
            [$resumen, $resultados] = self::procesarPendientesSincronizacion($deteccion['pendientes'], $idSesion);
            $resumen['revisados'] = $deteccion['revisados'];
            $resumen['pendientes_detectados'] = count($deteccion['pendientes']);
            $resumen['lotes'] = count($deteccion['pendientes']) > 0 ? 1 : 0;

            return [
                'success' => $resumen['errores'] === 0,
                'tipo_respuesta' => 'legacy_sync_lote',
                'mensaje' => $resumen['pendientes_detectados'] > 0
                    ? ($forzarTodos
                        ? 'Sincronización masiva Legacy terminada.'
                        : 'Reproceso de sincronización Legacy terminado.')
                    : 'No se detectaron usuarios pendientes de sincronizar con Legacy.',
                'resumen' => $resumen,
                'datos' => $resultados,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'tipo_respuesta' => 'legacy_sync_lote',
                'mensaje' => 'Error al reprocesar pendientes de sincronización Legacy.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function sincronizarTodosPendientes(int $idSesion = 0, bool $forzarTodos = false, int $tamanoLote = 100): array
    {
        $tamanoLote = max(25, min(300, $tamanoLote));
        $db = new Database();
        self::asegurarBitacora($db);

        try {
            $legacy = new DatabaseLegacy();
            $deteccion = self::detectarPendientesSincronizacion($db, $legacy, 50000, $forzarTodos);
            $pendientes = $deteccion['pendientes'];
            [$resumen, $resultados] = self::procesarPendientesSincronizacion($pendientes, $idSesion, $tamanoLote);
            $resumen['revisados'] = $deteccion['revisados'];
            $resumen['pendientes_detectados'] = count($pendientes);
            $resumen['lotes'] = count($pendientes) > 0 ? (int)ceil(count($pendientes) / $tamanoLote) : 0;
            $resumen['tamano_lote'] = $tamanoLote;

            return [
                'success' => $resumen['errores'] === 0,
                'tipo_respuesta' => 'legacy_sync_todos',
                'mensaje' => $resumen['pendientes_detectados'] > 0
                    ? 'Sincronización completa Legacy terminada.'
                    : 'No se detectaron usuarios pendientes de sincronizar con Legacy.',
                'resumen' => $resumen,
                'datos' => $resultados,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'tipo_respuesta' => 'legacy_sync_todos',
                'mensaje' => 'Error al ejecutar sincronización completa Legacy.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function planSincronizacionTodosPendientes(bool $forzarTodos = false): array
    {
        $db = new Database();
        self::asegurarBitacora($db);

        try {
            $legacy = new DatabaseLegacy();
            $deteccion = self::detectarPendientesSincronizacion($db, $legacy, 50000, $forzarTodos);
            $plan = array_map([self::class, 'pendienteAPlan'], $deteccion['pendientes']);

            return [
                'success' => true,
                'tipo_respuesta' => 'legacy_sync_plan',
                'mensaje' => count($plan) > 0
                    ? 'Usuarios detectados para sincronizar.'
                    : 'No se detectaron usuarios pendientes de sincronizar con Legacy.',
                'resumen' => [
                    'revisados' => $deteccion['revisados'],
                    'pendientes_detectados' => count($plan),
                    'lotes' => count($plan),
                ],
                'pendientes' => $plan,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'tipo_respuesta' => 'legacy_sync_plan',
                'mensaje' => 'Error al detectar usuarios para sincronización Legacy.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function sincronizarPendientesPlan(array $pendientes, int $idSesion = 0): array
    {
        $pendientes = array_values(array_filter($pendientes, function ($item) {
            return is_array($item) && (int)($item['id_persona'] ?? 0) > 0;
        }));
        $resultados = [];
        $resumen = [
            'revisados' => 0,
            'pendientes_detectados' => count($pendientes),
            'actualizados' => 0,
            'sin_cambios' => 0,
            'omitidos' => 0,
            'errores' => 0,
            'lotes' => count($pendientes),
        ];

        foreach ($pendientes as $idx => $item) {
            $tipo = (string)($item['tipo'] ?? 'activo');
            $idPersona = (int)($item['id_persona'] ?? 0);
            $sync = $tipo === 'baja'
                ? self::sincronizarBajaDesdeSpartan($idPersona, $idSesion)
                : self::sincronizarDesdeEditarUsuario($idPersona, $idSesion);
            $resultado = (string)($sync['resultado'] ?? '');
            if ($resultado === 'actualizado') {
                $resumen['actualizados']++;
            } elseif ($resultado === 'sin_cambios') {
                $resumen['sin_cambios']++;
            } elseif ($resultado === 'omitido') {
                $resumen['omitidos']++;
            } elseif ($resultado === 'error') {
                $resumen['errores']++;
            }

            $resultados[] = [
                'lote' => (int)($item['lote'] ?? ($idx + 1)),
                'id_persona' => $idPersona,
                'external_id' => trim((string)($item['external_id'] ?? '')),
                'nombre' => trim((string)($item['nombre'] ?? '')),
                'puesto' => trim((string)($item['puesto'] ?? '')),
                'departamento' => trim((string)($item['departamento'] ?? '')),
                'role_legacy' => trim((string)($item['role_legacy'] ?? '')),
                'motivos' => is_array($item['motivos'] ?? null) ? $item['motivos'] : [],
                'sync' => $sync,
            ];
        }

        return [
            'success' => $resumen['errores'] === 0,
            'tipo_respuesta' => 'legacy_sync_lote',
            'mensaje' => $resumen['errores'] > 0
                ? 'El lote se proceso con errores.'
                : 'Lote sincronizado correctamente.',
            'resumen' => $resumen,
            'datos' => $resultados,
        ];
    }

    private static function pendienteAPlan(array $pendiente): array
    {
        $ctx = $pendiente['ctx'] ?? [];
        return [
            'tipo' => (string)($pendiente['tipo'] ?? 'activo'),
            'id_persona' => (int)($ctx['id_persona'] ?? 0),
            'external_id' => trim((string)($ctx['external_id'] ?? '')),
            'nombre' => trim((string)($ctx['nombre_completo'] ?? '')),
            'puesto' => trim((string)($ctx['puesto_nombre'] ?? '')),
            'departamento' => trim((string)($ctx['departamento_nombre'] ?? '')),
            'role_legacy' => trim((string)($ctx['role_legacy'] ?? '')),
            'motivos' => is_array($pendiente['motivos'] ?? null) ? $pendiente['motivos'] : [],
        ];
    }

    private static function detectarPendientesSincronizacion(Database $db, DatabaseLegacy $legacy, int $limite, bool $forzarTodos): array
    {
        $limite = max(1, min(50000, $limite));
        $candidatos = self::obtenerCandidatosSincronizacion($db, $limite * 4);
        $pendientes = [];
        $bajasRevisadas = 0;
        $activosEnAlcance = [];

        foreach ($candidatos as $ctx) {
            if (!self::estaEnAlcance($ctx)) {
                continue;
            }
            $externalId = trim((string)($ctx['external_id'] ?? ''));
            $roleClave = trim((string)($ctx['role_legacy'] ?? ''));
            if ($externalId === '' || $roleClave === '' || !self::externalIdValido($externalId)) {
                continue;
            }
            $activosEnAlcance[] = $ctx;
        }

        $estadosActivos = self::estadosLegacyPorExternalIds($legacy, array_column($activosEnAlcance, 'external_id'));
        foreach ($activosEnAlcance as $ctx) {
            $externalId = trim((string)($ctx['external_id'] ?? ''));
            $roleClave = trim((string)($ctx['role_legacy'] ?? ''));
            $estadoLegacy = $estadosActivos[$externalId] ?? null;
            $motivos = [];
            if (!$estadoLegacy) {
                $motivos[] = 'no_existe_en_legacy';
            } elseif (!empty($estadoLegacy['deleted_at'])) {
                $motivos[] = 'baja_en_legacy_con_spartan_activo';
            } elseif (trim((string)($estadoLegacy['role_name'] ?? '')) !== $roleClave) {
                $motivos[] = 'role_desalineado';
            } elseif (trim((string)($estadoLegacy['username'] ?? '')) !== self::usernameLegacyDesdeSpartan($ctx, $externalId)) {
                $motivos[] = 'usuario_desalineado';
            } elseif (trim((string)($estadoLegacy['name'] ?? '')) !== self::nombreLegacyDesdeSpartan($ctx, $externalId)) {
                $motivos[] = 'nombre_desalineado';
            }

            if (!$motivos && $forzarTodos) {
                $motivos[] = 'sincronizacion_forzada';
            }

            if (!$motivos) {
                continue;
            }

            $pendientes[] = [
                'tipo' => 'activo',
                'ctx' => $ctx,
                'motivos' => $motivos,
                'estado_legacy' => $estadoLegacy,
            ];
            if (count($pendientes) >= $limite) {
                break;
            }
        }

        if (count($pendientes) < $limite) {
            $bajas = self::obtenerCandidatosBajaSincronizacion($db, ($limite - count($pendientes)) * 4);
            $bajasRevisadas = count($bajas);
            $bajasEnAlcance = [];
            foreach ($bajas as $ctx) {
                if (!self::estaEnAlcance($ctx)) {
                    continue;
                }
                $externalId = trim((string)($ctx['external_id'] ?? ''));
                if ($externalId === '' || !self::externalIdValido($externalId)) {
                    continue;
                }
                $bajasEnAlcance[] = $ctx;
            }

            $estadosBajas = self::estadosLegacyPorExternalIds($legacy, array_column($bajasEnAlcance, 'external_id'));
            foreach ($bajasEnAlcance as $ctx) {
                $externalId = trim((string)($ctx['external_id'] ?? ''));
                $estadoLegacy = $estadosBajas[$externalId] ?? null;
                if (!$estadoLegacy || !empty($estadoLegacy['deleted_at'])) {
                    continue;
                }

                $pendientes[] = [
                    'tipo' => 'baja',
                    'ctx' => $ctx,
                    'motivos' => ['baja_spartan_activa_en_legacy'],
                    'estado_legacy' => $estadoLegacy,
                ];
                if (count($pendientes) >= $limite) {
                    break;
                }
            }
        }

        return [
            'revisados' => count($candidatos) + $bajasRevisadas,
            'pendientes' => $pendientes,
        ];
    }

    private static function procesarPendientesSincronizacion(array $pendientes, int $idSesion, int $tamanoLote = 300): array
    {
        $tamanoLote = max(1, min(300, $tamanoLote));
        $resultados = [];
        $resumen = [
            'revisados' => 0,
            'pendientes_detectados' => count($pendientes),
            'actualizados' => 0,
            'sin_cambios' => 0,
            'omitidos' => 0,
            'errores' => 0,
            'lotes' => 0,
        ];

        $lotes = array_chunk($pendientes, $tamanoLote);
        $resumen['lotes'] = count($lotes);
        foreach ($lotes as $idxLote => $lote) {
            foreach ($lote as $pendiente) {
                $ctx = $pendiente['ctx'];
                $tipo = (string)($pendiente['tipo'] ?? 'activo');
                $sync = $tipo === 'baja'
                    ? self::sincronizarBajaDesdeSpartan((int)$ctx['id_persona'], $idSesion)
                    : self::sincronizarDesdeEditarUsuario((int)$ctx['id_persona'], $idSesion);
                $resultado = (string)($sync['resultado'] ?? '');
                if ($resultado === 'actualizado') {
                    $resumen['actualizados']++;
                } elseif ($resultado === 'sin_cambios') {
                    $resumen['sin_cambios']++;
                } elseif ($resultado === 'omitido') {
                    $resumen['omitidos']++;
                } elseif ($resultado === 'error') {
                    $resumen['errores']++;
                }

                $resultados[] = [
                    'lote' => $idxLote + 1,
                    'id_persona' => (int)($ctx['id_persona'] ?? 0),
                    'external_id' => trim((string)($ctx['external_id'] ?? '')),
                    'nombre' => trim((string)($ctx['nombre_completo'] ?? '')),
                    'puesto' => trim((string)($ctx['puesto_nombre'] ?? '')),
                    'departamento' => trim((string)($ctx['departamento_nombre'] ?? '')),
                    'role_legacy' => trim((string)($ctx['role_legacy'] ?? '')),
                    'motivos' => $pendiente['motivos'],
                    'sync' => $sync,
                ];
            }
        }

        return [$resumen, $resultados];
    }

    public static function sincronizarBajaDesdeSpartan(int $idPersona, int $idSesion = 0): array
    {
        $db = new Database();
        self::asegurarBitacora($db);

        try {
            $ctx = self::obtenerContextoPersonaBaja($db, $idPersona);
            if (!$ctx) {
                return self::registrar($db, [
                    'origen' => self::ORIGEN_BAJA_USUARIO,
                    'id_persona' => $idPersona,
                    'id_usuario' => $idSesion,
                    'resultado' => 'error',
                    'mensaje' => 'Persona no encontrada en Spartan para baja Legacy.',
                ]);
            }

            $externalId = trim((string)($ctx['external_id'] ?? ''));
            if ($externalId === '') {
                return self::registrar($db, [
                    'origen' => self::ORIGEN_BAJA_USUARIO,
                    'id_persona' => $idPersona,
                    'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                    'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                    'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                    'id_usuario' => $idSesion,
                    'resultado' => 'error',
                    'mensaje' => 'La persona no tiene número de empleado para dar de baja en Legacy.',
                ]);
            }

            $legacy = new DatabaseLegacy();
            $legacyUser = self::buscarUsuarioLegacyIncluyendoBaja($legacy, $externalId);
            if (!$legacyUser) {
                return self::registrar($db, [
                    'origen' => self::ORIGEN_BAJA_USUARIO,
                    'id_persona' => $idPersona,
                    'external_id' => $externalId,
                    'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                    'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                    'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                    'role_legacy' => $ctx['role_legacy'] ?? '',
                    'id_usuario' => $idSesion,
                    'resultado' => 'omitido',
                    'mensaje' => 'Usuario no encontrado en Legacy; no había registro que dar de baja.',
                ]);
            }

            $estadoAnterior = self::estadoLegacyActual($legacy, (int)$legacyUser['id']);
            $yaBaja = !empty($legacyUser['deleted_at']);
            if (!$yaBaja) {
                $legacy->beginTransaction();
                self::darBajaUsuarioLegacy($legacy, (int)$legacyUser['id']);
                $legacy->commit();
            }
            $estadoNuevo = self::estadoLegacyActual($legacy, (int)$legacyUser['id']);

            return self::registrar($db, [
                'origen' => self::ORIGEN_BAJA_USUARIO,
                'id_persona' => $idPersona,
                'external_id' => $externalId,
                'id_puesto' => (int)($ctx['id_puesto'] ?? 0),
                'puesto_nombre' => $ctx['puesto_nombre'] ?? '',
                'departamento_nombre' => $ctx['departamento_nombre'] ?? '',
                'id_usuario_legacy' => (int)$legacyUser['id'],
                'role_legacy' => $estadoAnterior['role_name'] ?? ($ctx['role_legacy'] ?? ''),
                'id_usuario' => $idSesion,
                'resultado' => $yaBaja ? 'sin_cambios' : 'actualizado',
                'mensaje' => $yaBaja
                    ? 'Usuario Legacy ya estaba dado de baja.'
                    : 'Usuario dado de baja en Legacy correctamente.',
                'detalle' => [
                    'accion' => 'soft_delete_legacy_user',
                    'antes' => $estadoAnterior,
                    'despues' => $estadoNuevo,
                ],
            ]);
        } catch (\Throwable $e) {
            if (isset($legacy)) {
                try { $legacy->rollback(); } catch (\Throwable $rollbackError) {}
            }

            return self::registrar($db, [
                'origen' => self::ORIGEN_BAJA_USUARIO,
                'id_persona' => $idPersona,
                'id_usuario' => $idSesion,
                'resultado' => 'error',
                'mensaje' => 'Error al dar de baja el usuario en Legacy.',
                'detalle' => ['error' => $e->getMessage()],
            ]);
        }
    }

    private static function asegurarBitacora(Database $db): void
    {
        $db->CRUD("CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.legacy_user_sync_bitacora (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            origen VARCHAR(80) NOT NULL,
            id_persona INT NULL,
            external_id VARCHAR(80) NULL,
            id_puesto INT NULL,
            puesto_nombre VARCHAR(180) NULL,
            departamento_nombre VARCHAR(180) NULL,
            id_usuario_legacy BIGINT UNSIGNED NULL,
            role_legacy VARCHAR(80) NULL,
            resultado ENUM('actualizado','sin_cambios','omitido','error') NOT NULL DEFAULT 'omitido',
            mensaje VARCHAR(500) NULL,
            detalle_json JSON NULL,
            creado_por INT NULL,
            creado_por_usuario VARCHAR(80) NULL,
            creado_por_nombre VARCHAR(220) NULL,
            ip_origen VARCHAR(80) NULL,
            user_agent VARCHAR(500) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_legacy_sync_persona (id_persona),
            KEY idx_legacy_sync_external (external_id),
            KEY idx_legacy_sync_resultado (resultado),
            KEY idx_legacy_sync_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        self::asegurarColumnasBitacora($db);
    }

    private static function asegurarColumnasBitacora(Database $db): void
    {
        $columnas = [
            'creado_por_usuario' => 'VARCHAR(80) NULL AFTER creado_por',
            'creado_por_nombre' => 'VARCHAR(220) NULL AFTER creado_por_usuario',
            'ip_origen' => 'VARCHAR(80) NULL AFTER creado_por_nombre',
            'user_agent' => 'VARCHAR(500) NULL AFTER ip_origen',
        ];

        foreach ($columnas as $nombre => $definicion) {
            $existe = $db->queryOne("SHOW COLUMNS FROM __SPARTA_SECRET_REDACTED__.legacy_user_sync_bitacora LIKE :columna", [
                'columna' => $nombre,
            ]);
            if (!$existe) {
                $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.legacy_user_sync_bitacora ADD COLUMN {$nombre} {$definicion}");
            }
        }
    }

    private static function obtenerContextoPersona(Database $db, int $idPersona): ?array
    {
        return $db->queryOne("
            SELECT
                per.id AS id_persona,
                TRIM(COALESCE(per.numero_empleado, '')) AS external_id,
                TRIM(COALESCE(per.nombres, '')) AS nombres,
                TRIM(COALESCE(per.segundo_nombre, '')) AS segundo_nombre,
                TRIM(COALESCE(per.apellidop, '')) AS apellidop,
                TRIM(COALESCE(per.apellidom, '')) AS apellidom,
                TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_completo,
                TRIM(COALESCE(per.correo, '')) AS correo,
                TRIM(COALESCE(per.user_name, '')) AS username,
                COALESCE(per.password, '') AS password_spartan,
                per.estatus,
                p.id AS id_puesto,
                p.nombre AS puesto_nombre,
                d.id AS id_departamento,
                d.nombre AS departamento_nombre,
                pl.clave AS role_legacy
            FROM __SPARTA_SECRET_REDACTED__.persona per
            LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = per.id AND ap.activo = 1
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = p.departamento_id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.equivalencias_legacy_puestos el ON el.id_puesto = p.id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puestos_legacy pl ON pl.id = el.id_puesto_legacy
            WHERE per.id = :id_persona
              AND COALESCE(per.estatus, '') <> 'Baja'
            ORDER BY ap.id DESC
            LIMIT 1
        ", ['id_persona' => $idPersona]) ?: null;
    }

    private static function obtenerCandidatosSincronizacion(Database $db, int $limite): array
    {
        $limite = max(1, min(50000, $limite));
        return $db->queryAll("
            SELECT
                per.id AS id_persona,
                TRIM(COALESCE(per.numero_empleado, '')) AS external_id,
                TRIM(COALESCE(per.nombres, '')) AS nombres,
                TRIM(COALESCE(per.segundo_nombre, '')) AS segundo_nombre,
                TRIM(COALESCE(per.apellidop, '')) AS apellidop,
                TRIM(COALESCE(per.apellidom, '')) AS apellidom,
                TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_completo,
                TRIM(COALESCE(per.correo, '')) AS correo,
                TRIM(COALESCE(per.user_name, '')) AS username,
                COALESCE(per.password, '') AS password_spartan,
                per.estatus,
                p.id AS id_puesto,
                p.nombre AS puesto_nombre,
                d.id AS id_departamento,
                d.nombre AS departamento_nombre,
                pl.clave AS role_legacy
            FROM __SPARTA_SECRET_REDACTED__.persona per
            INNER JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = per.id AND ap.activo = 1
            INNER JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = p.departamento_id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.equivalencias_legacy_puestos el ON el.id_puesto = p.id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puestos_legacy pl ON pl.id = el.id_puesto_legacy
            WHERE COALESCE(per.estatus, '') <> 'Baja'
              AND TRIM(COALESCE(per.numero_empleado, '')) <> ''
              AND TRIM(COALESCE(per.numero_empleado, '')) REGEXP '^[0-9]+$'
              AND TRIM(COALESCE(pl.clave, '')) <> ''
            ORDER BY per.id DESC
            LIMIT {$limite}
        ");
    }

    private static function obtenerCandidatosBajaSincronizacion(Database $db, int $limite): array
    {
        $limite = max(1, min(50000, $limite));
        return $db->queryAll("
            SELECT
                per.id AS id_persona,
                TRIM(COALESCE(per.numero_empleado, '')) AS external_id,
                TRIM(COALESCE(per.nombres, '')) AS nombres,
                TRIM(COALESCE(per.segundo_nombre, '')) AS segundo_nombre,
                TRIM(COALESCE(per.apellidop, '')) AS apellidop,
                TRIM(COALESCE(per.apellidom, '')) AS apellidom,
                TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_completo,
                TRIM(COALESCE(per.correo, '')) AS correo,
                TRIM(COALESCE(per.user_name, '')) AS username,
                COALESCE(per.password, '') AS password_spartan,
                per.estatus,
                p.id AS id_puesto,
                p.nombre AS puesto_nombre,
                d.id AS id_departamento,
                d.nombre AS departamento_nombre,
                pl.clave AS role_legacy
            FROM __SPARTA_SECRET_REDACTED__.persona per
            LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = per.id AND ap.activo = 1
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = p.departamento_id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.equivalencias_legacy_puestos el ON el.id_puesto = p.id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puestos_legacy pl ON pl.id = el.id_puesto_legacy
            WHERE COALESCE(per.estatus, '') = 'Baja'
              AND TRIM(COALESCE(per.numero_empleado, '')) <> ''
              AND TRIM(COALESCE(per.numero_empleado, '')) REGEXP '^[0-9]+$'
            ORDER BY per.id DESC
            LIMIT {$limite}
        ");
    }

    private static function obtenerContextoPersonaBaja(Database $db, int $idPersona): ?array
    {
        return $db->queryOne("
            SELECT
                per.id AS id_persona,
                TRIM(COALESCE(per.numero_empleado, '')) AS external_id,
                TRIM(COALESCE(per.nombres, '')) AS nombres,
                TRIM(COALESCE(per.segundo_nombre, '')) AS segundo_nombre,
                TRIM(COALESCE(per.apellidop, '')) AS apellidop,
                TRIM(COALESCE(per.apellidom, '')) AS apellidom,
                TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_completo,
                TRIM(COALESCE(per.correo, '')) AS correo,
                TRIM(COALESCE(per.user_name, '')) AS username,
                per.estatus,
                p.id AS id_puesto,
                p.nombre AS puesto_nombre,
                d.id AS id_departamento,
                d.nombre AS departamento_nombre,
                pl.clave AS role_legacy
            FROM __SPARTA_SECRET_REDACTED__.persona per
            LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = per.id AND ap.activo = 1
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = p.departamento_id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.equivalencias_legacy_puestos el ON el.id_puesto = p.id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puestos_legacy pl ON pl.id = el.id_puesto_legacy
            WHERE per.id = :id_persona
            ORDER BY ap.id DESC
            LIMIT 1
        ", ['id_persona' => $idPersona]) ?: null;
    }

    private static function estaEnAlcance(array $ctx): bool
    {
        $puesto = self::normalizarTexto($ctx['puesto_nombre'] ?? '');
        $departamento = self::normalizarTexto($ctx['departamento_nombre'] ?? '');
        $texto = $puesto . ' ' . $departamento;

        $esCobranza = strpos($departamento, 'cobranza') !== false
            || strpos($departamento, 'campo') !== false
            || strpos($texto, 'cobranza') !== false;
        if (!$esCobranza) {
            return false;
        }

        return (bool)(
            preg_match('/\b1\s*[-_ ]\s*7\b/', $texto)
            || preg_match('/\b8\s*[-_ ]\s*21\b/', $texto)
            || preg_match('/\b22\s*[-_ ]\s*29\b/', $texto)
            || preg_match('/\b8\s*[-_ ]\s*30\b/', $texto)
        );
    }

    private static function normalizarTexto(string $texto): string
    {
        $texto = trim(strtolower($texto));
        $map = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'];
        return strtr($texto, $map);
    }

    private static function externalIdValido(string $externalId): bool
    {
        return (bool)preg_match('/^[0-9]+$/', trim($externalId));
    }

    private static function calcularJerarquiaSpartan(Database $db, int $idPersona): array
    {
        $rows = $db->queryAll("
            WITH RECURSIVE
            aj_actual AS (
                SELECT aj.id_persona,
                       COALESCE(aj.id_jefe, vp.id_jefe) AS id_jefe
                FROM __SPARTA_SECRET_REDACTED__.asigna_jefe aj
                INNER JOIN (
                    SELECT id_persona, MAX(id) AS max_id
                    FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                    GROUP BY id_persona
                ) ult ON ult.max_id = aj.id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.vacantes_personal vp ON vp.id = aj.id_vacante_jefe
            ),
            jerarquia AS (
                SELECT :id_persona_base AS persona_id, aj.id_jefe, 1 AS lvl
                FROM aj_actual aj
                WHERE aj.id_persona = :id_persona_anchor

                UNION ALL

                SELECT j.persona_id, aj2.id_jefe, j.lvl + 1
                FROM jerarquia j
                INNER JOIN aj_actual aj2 ON aj2.id_persona = j.id_jefe
                WHERE j.id_jefe IS NOT NULL
                  AND j.lvl < 10
            )
            SELECT
                j.id_jefe,
                j.lvl,
                TRIM(COALESCE(per.numero_empleado, '')) AS external_id,
                TRIM(CONCAT_WS(' ', per.apellidop, per.apellidom, per.nombres, per.segundo_nombre)) AS nombre,
                pl.clave AS role_legacy
            FROM jerarquia j
            INNER JOIN __SPARTA_SECRET_REDACTED__.persona per ON per.id = j.id_jefe
            LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = per.id AND ap.activo = 1
            LEFT JOIN __SPARTA_SECRET_REDACTED__.equivalencias_legacy_puestos el ON el.id_puesto = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puestos_legacy pl ON pl.id = el.id_puesto_legacy
            WHERE j.id_jefe IS NOT NULL
            ORDER BY j.lvl ASC
        ", ['id_persona_base' => $idPersona, 'id_persona_anchor' => $idPersona]);

        $map = [
            'supervisor' => null,
            'subgerente' => null,
            'gerente' => null,
            'subdirector' => null,
        ];

        foreach ($rows as $row) {
            $role = trim((string)($row['role_legacy'] ?? ''));
            if (array_key_exists($role, $map) && $map[$role] === null) {
                $map[$role] = [
                    'id_persona' => (int)($row['id_jefe'] ?? 0),
                    'external_id' => trim((string)($row['external_id'] ?? '')),
                    'nombre' => trim((string)($row['nombre'] ?? '')),
                    'nivel' => (int)($row['lvl'] ?? 0),
                    'role_legacy' => $role,
                ];
            }
        }

        return $map;
    }

    private static function buscarUsuarioLegacy(DatabaseLegacy $legacy, string $externalId): ?array
    {
        return $legacy->queryOne("
            SELECT id, external_id, name
            FROM users
            WHERE TRIM(COALESCE(external_id, '')) = :external_id
              AND deleted_at IS NULL
            LIMIT 1
        ", ['external_id' => $externalId]) ?: null;
    }

    private static function buscarUsuarioLegacyIncluyendoBaja(DatabaseLegacy $legacy, string $externalId): ?array
    {
        return $legacy->queryOne("
            SELECT id, external_id, name, deleted_at
            FROM users
            WHERE TRIM(COALESCE(external_id, '')) = :external_id
            ORDER BY deleted_at IS NULL DESC, id DESC
            LIMIT 1
        ", ['external_id' => $externalId]) ?: null;
    }

    private static function estadoLegacyPorExternalId(DatabaseLegacy $legacy, string $externalId): ?array
    {
        return $legacy->queryOne("
            SELECT
                u.id,
                u.external_id,
                u.name,
                u.username,
                u.deleted_at,
                r.id AS role_id,
                r.name AS role_name
            FROM users u
            LEFT JOIN model_has_roles m ON m.model_id = u.id AND m.model_type = :model_type
            LEFT JOIN roles r ON r.id = m.role_id
            WHERE TRIM(COALESCE(u.external_id, '')) = :external_id
            ORDER BY u.deleted_at IS NULL DESC, u.id DESC
            LIMIT 1
        ", ['external_id' => $externalId, 'model_type' => self::MODEL_TYPE]) ?: null;
    }

    private static function estadosLegacyPorExternalIds(DatabaseLegacy $legacy, array $externalIds): array
    {
        $externalIds = array_values(array_unique(array_filter(array_map(function ($id) {
            return trim((string)$id);
        }, $externalIds))));
        if (!$externalIds) {
            return [];
        }

        $map = [];
        foreach (array_chunk($externalIds, 400) as $chunkIdx => $chunk) {
            $params = ['model_type' => self::MODEL_TYPE];
            $placeholders = [];
            foreach ($chunk as $idx => $externalId) {
                $key = 'external_id_' . $chunkIdx . '_' . $idx;
                $placeholders[] = ':' . $key;
                $params[$key] = $externalId;
            }

            $rows = $legacy->queryAll("
                SELECT
                    u.id,
                    u.external_id,
                    u.name,
                    u.username,
                    u.deleted_at,
                    r.id AS role_id,
                    r.name AS role_name
                FROM users u
                LEFT JOIN model_has_roles m ON m.model_id = u.id AND m.model_type = :model_type
                LEFT JOIN roles r ON r.id = m.role_id
                WHERE TRIM(COALESCE(u.external_id, '')) IN (" . implode(',', $placeholders) . ")
                ORDER BY u.deleted_at IS NULL DESC, u.id DESC
            ", $params);

            foreach ($rows as $row) {
                $externalId = trim((string)($row['external_id'] ?? ''));
                if ($externalId !== '' && !isset($map[$externalId])) {
                    $map[$externalId] = $row;
                }
            }
        }

        return $map;
    }

    private static function crearUsuarioLegacyDesdeSpartan(DatabaseLegacy $legacy, array $ctx, string $externalId): array
    {
        $nombre = self::nombreLegacyDesdeSpartan($ctx, $externalId);
        $username = self::usernameLegacyDesdeSpartan($ctx, $externalId);

        $email = self::resolverEmailLegacyDisponible($legacy, trim((string)($ctx['correo'] ?? '')), $externalId);
        $password = self::passwordLegacy($ctx['password_spartan'] ?? '');

        $legacy->CRUD("
            INSERT INTO users
                (legion_id, name, email, password, external_id, username, created_at, updated_at)
            VALUES
                (1, :name, :email, :password, :external_id, :username, NOW(), NOW())
        ", [
            'name' => $nombre,
            'email' => $email,
            'password' => $password,
            'external_id' => $externalId,
            'username' => $username,
        ]);

        $id = $legacy->lastInsertId();
        if ($id <= 0) {
            throw new \RuntimeException('No se pudo obtener el ID del usuario creado en Legacy.');
        }

        return [
            'id' => $id,
            'external_id' => $externalId,
            'name' => $nombre,
            'email' => $email,
            'username' => $username,
        ];
    }

    private static function resolverEmailLegacyDisponible(DatabaseLegacy $legacy, string $emailSpartan, string $externalId): string
    {
        $candidatos = [];
        $emailSpartan = strtolower(trim($emailSpartan));
        if ($emailSpartan !== '' && filter_var($emailSpartan, FILTER_VALIDATE_EMAIL)) {
            $candidatos[] = $emailSpartan;
        }
        $base = preg_replace('/[^a-zA-Z0-9._-]+/', '', $externalId);
        if ($base === '') {
            $base = 'usuario';
        }
        $candidatos[] = strtolower($base) . '@legacy-sync.local';
        $candidatos[] = 'spartan-' . strtolower($base) . '@legacy-sync.local';

        foreach ($candidatos as $email) {
            if (!self::emailLegacyExiste($legacy, $email)) {
                return $email;
            }
        }

        for ($i = 2; $i <= 20; $i++) {
            $email = 'spartan-' . strtolower($base) . '-' . $i . '@legacy-sync.local';
            if (!self::emailLegacyExiste($legacy, $email)) {
                return $email;
            }
        }

        throw new \RuntimeException('No se pudo generar un correo único para Legacy.');
    }

    private static function emailLegacyExiste(DatabaseLegacy $legacy, string $email): bool
    {
        return (bool)$legacy->queryOne("
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ", ['email' => $email]);
    }

    private static function passwordLegacy($passwordSpartan): string
    {
        $raw = trim((string)$passwordSpartan);
        if ($raw !== '' && self::esPasswordHash($raw)) {
            return $raw;
        }
        if ($raw === '') {
            $raw = bin2hex(random_bytes(24));
        }
        return password_hash($raw, PASSWORD_BCRYPT);
    }

    private static function esPasswordHash(string $valor): bool
    {
        $info = password_get_info($valor);
        $algo = $info['algo'] ?? 0;
        $algoName = (string)($info['algoName'] ?? 'unknown');
        return $valor !== '' && $algo !== 0 && $algo !== null && $algoName !== 'unknown';
    }

    private static function usernameLegacyDesdeSpartan(array $ctx, string $externalId): string
    {
        $username = trim((string)($ctx['username'] ?? ''));
        return $username !== '' ? $username : $externalId;
    }

    private static function nombreLegacyDesdeSpartan(array $ctx, string $externalId): string
    {
        $apellidoPaterno = self::normalizarNombreLegacy($ctx['apellidop'] ?? '');
        $apellidoMaterno = self::normalizarNombreLegacy($ctx['apellidom'] ?? '');
        $primerNombre = self::normalizarNombreLegacy($ctx['nombres'] ?? '');
        $segundoNombre = self::normalizarNombreLegacy($ctx['segundo_nombre'] ?? '');

        $partes = array_filter([$apellidoPaterno, $apellidoMaterno, $primerNombre, $segundoNombre], fn($v) => $v !== '');
        $nombre = trim(implode(' ', $partes));

        if ($nombre === '') {
            $nombre = self::normalizarNombreLegacy($ctx['nombre_completo'] ?? '');
        }
        return $nombre !== '' ? $nombre : 'USUARIO ' . $externalId;
    }

    private static function normalizarNombreLegacy($valor): string
    {
        $texto = preg_replace('/\s+/u', ' ', trim((string)$valor));
        if ($texto === '') {
            return '';
        }
        return function_exists('mb_strtoupper')
            ? mb_strtoupper($texto, 'UTF-8')
            : strtoupper($texto);
    }

    private static function resolverCredencialesLegacy(DatabaseLegacy $legacy, int $legacyUserId, array $ctx, string $externalId): array
    {
        $actual = $legacy->queryOne("
            SELECT username, password
            FROM users
            WHERE id = :id
            LIMIT 1
        ", ['id' => $legacyUserId]) ?: [];

        $passwordSpartan = trim((string)($ctx['password_spartan'] ?? ''));
        $passwordActual = (string)($actual['password'] ?? '');
        $passwordNuevo = null;
        $passwordActualizar = false;

        if ($passwordSpartan !== '') {
            if (self::esPasswordHash($passwordSpartan)) {
                $passwordActualizar = $passwordSpartan !== $passwordActual;
            } else {
                $passwordActualizar = $passwordActual === '' || !password_verify($passwordSpartan, $passwordActual);
            }
            if ($passwordActualizar) {
                $passwordNuevo = self::passwordLegacy($passwordSpartan);
            }
        }

        return [
            'name' => self::nombreLegacyDesdeSpartan($ctx, $externalId),
            'username' => self::usernameLegacyDesdeSpartan($ctx, $externalId),
            'password' => $passwordNuevo,
            'password_actualizado' => $passwordActualizar,
        ];
    }

    private static function buscarRoleLegacy(DatabaseLegacy $legacy, string $roleClave): ?array
    {
        return $legacy->queryOne("
            SELECT id, name
            FROM roles
            WHERE name = :name
              AND guard_name = 'web'
            LIMIT 1
        ", ['name' => $roleClave]) ?: null;
    }

    private static function resolverJerarquiaLegacy(DatabaseLegacy $legacy, array $jerarquia): array
    {
        $ids = [
            'supervisor_id' => null,
            'subgerente_id' => null,
            'gerente_id' => null,
            'subdirector_id' => null,
        ];
        $faltantes = [];
        $mapCampos = [
            'supervisor' => 'supervisor_id',
            'subgerente' => 'subgerente_id',
            'gerente' => 'gerente_id',
            'subdirector' => 'subdirector_id',
        ];

        foreach ($mapCampos as $role => $campo) {
            $jefe = $jerarquia[$role] ?? null;
            if (!$jefe || trim((string)($jefe['external_id'] ?? '')) === '') {
                continue;
            }

            $legacyUser = self::buscarUsuarioLegacy($legacy, trim((string)$jefe['external_id']));
            if ($legacyUser) {
                $ids[$campo] = (int)$legacyUser['id'];
            } else {
                $faltantes[] = $jefe;
            }
        }

        return ['ids' => $ids, 'faltantes' => $faltantes];
    }

    private static function estadoLegacyActual(DatabaseLegacy $legacy, int $legacyUserId): array
    {
        $row = $legacy->queryOne("
            SELECT
                u.name,
                u.username,
                u.supervisor_id,
                u.subgerente_id,
                u.gerente_id,
                u.subdirector_id,
                u.deleted_at,
                r.id AS role_id,
                r.name AS role_name
            FROM users u
            LEFT JOIN model_has_roles m ON m.model_id = u.id AND m.model_type = :model_type
            LEFT JOIN roles r ON r.id = m.role_id
            WHERE u.id = :id
            LIMIT 1
        ", ['id' => $legacyUserId, 'model_type' => self::MODEL_TYPE]);

        return [
            'name' => $row['name'] ?? null,
            'username' => $row['username'] ?? null,
            'role_id' => isset($row['role_id']) ? (int)$row['role_id'] : null,
            'role_name' => $row['role_name'] ?? null,
            'supervisor_id' => isset($row['supervisor_id']) ? (int)$row['supervisor_id'] : null,
            'subgerente_id' => isset($row['subgerente_id']) ? (int)$row['subgerente_id'] : null,
            'gerente_id' => isset($row['gerente_id']) ? (int)$row['gerente_id'] : null,
            'subdirector_id' => isset($row['subdirector_id']) ? (int)$row['subdirector_id'] : null,
            'deleted_at' => $row['deleted_at'] ?? null,
        ];
    }

    private static function actualizarUsuarioLegacy(DatabaseLegacy $legacy, int $legacyUserId, array $ids, array $credenciales): void
    {
        $setPassword = !empty($credenciales['password_actualizado']) && !empty($credenciales['password']);
        $legacy->CRUD("
            UPDATE users
            SET name = :name,
                username = :username,
                " . ($setPassword ? "password = :password,\n                remember_token = NULL,\n                " : "") . "supervisor_id = :supervisor_id,
                subgerente_id = :subgerente_id,
                gerente_id = :gerente_id,
                subdirector_id = :subdirector_id,
                updated_at = NOW()
            WHERE id = :id
            LIMIT 1
        ", [
            'id' => $legacyUserId,
            'name' => $credenciales['name'] ?? null,
            'username' => $credenciales['username'] ?? null,
            'supervisor_id' => $ids['supervisor_id'] ?? null,
            'subgerente_id' => $ids['subgerente_id'] ?? null,
            'gerente_id' => $ids['gerente_id'] ?? null,
            'subdirector_id' => $ids['subdirector_id'] ?? null,
        ] + ($setPassword ? ['password' => $credenciales['password']] : []));
    }

    private static function actualizarRoleLegacy(DatabaseLegacy $legacy, int $legacyUserId, int $roleId): void
    {
        $legacy->CRUD("
            DELETE FROM model_has_roles
            WHERE model_id = :model_id
              AND model_type = :model_type
        ", ['model_id' => $legacyUserId, 'model_type' => self::MODEL_TYPE]);

        $legacy->CRUD("
            INSERT INTO model_has_roles (role_id, model_type, model_id)
            VALUES (:role_id, :model_type, :model_id)
        ", [
            'role_id' => $roleId,
            'model_type' => self::MODEL_TYPE,
            'model_id' => $legacyUserId,
        ]);
    }

    private static function darBajaUsuarioLegacy(DatabaseLegacy $legacy, int $legacyUserId): void
    {
        $legacy->CRUD("
            UPDATE users
            SET deleted_at = COALESCE(deleted_at, NOW()),
                remember_token = NULL,
                updated_at = NOW()
            WHERE id = :id
            LIMIT 1
        ", ['id' => $legacyUserId]);
    }

    private static function reactivarUsuarioLegacy(DatabaseLegacy $legacy, int $legacyUserId): void
    {
        $legacy->CRUD("
            UPDATE users
            SET deleted_at = NULL,
                updated_at = NOW()
            WHERE id = :id
            LIMIT 1
        ", ['id' => $legacyUserId]);
    }

    private static function sinCambios(array $antes, array $despues): bool
    {
        ksort($antes);
        ksort($despues);
        return $antes === $despues;
    }

    private static function registrar(Database $db, array $data): array
    {
        $detalle = $data['detalle'] ?? null;
        $detalleJson = $detalle === null
            ? null
            : json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.legacy_user_sync_bitacora
                (origen, id_persona, external_id, id_puesto, puesto_nombre, departamento_nombre,
                 id_usuario_legacy, role_legacy, resultado, mensaje, detalle_json, creado_por,
                 creado_por_usuario, creado_por_nombre, ip_origen, user_agent)
            VALUES
                (:origen, :id_persona, :external_id, :id_puesto, :puesto_nombre, :departamento_nombre,
                 :id_usuario_legacy, :role_legacy, :resultado, :mensaje, :detalle_json, :creado_por,
                 :creado_por_usuario, :creado_por_nombre, :ip_origen, :user_agent)
        ", [
            'origen' => $data['origen'] ?? self::ORIGEN_EDITAR_USUARIO,
            'id_persona' => (int)($data['id_persona'] ?? 0) ?: null,
            'external_id' => trim((string)($data['external_id'] ?? '')) ?: null,
            'id_puesto' => (int)($data['id_puesto'] ?? 0) ?: null,
            'puesto_nombre' => trim((string)($data['puesto_nombre'] ?? '')) ?: null,
            'departamento_nombre' => trim((string)($data['departamento_nombre'] ?? '')) ?: null,
            'id_usuario_legacy' => (int)($data['id_usuario_legacy'] ?? 0) ?: null,
            'role_legacy' => trim((string)($data['role_legacy'] ?? '')) ?: null,
            'resultado' => $data['resultado'] ?? 'omitido',
            'mensaje' => trim((string)($data['mensaje'] ?? '')) ?: null,
            'detalle_json' => $detalleJson,
            'creado_por' => (int)($data['id_usuario'] ?? 0) ?: null,
            'creado_por_usuario' => trim((string)($_SESSION['usuario'] ?? '')) ?: null,
            'creado_por_nombre' => trim((string)($_SESSION['usuario_nombre'] ?? '')) ?: null,
            'ip_origen' => trim((string)($_SERVER['REMOTE_ADDR'] ?? '')) ?: null,
            'user_agent' => substr(trim((string)($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 500) ?: null,
        ]);

        return [
            'success' => !in_array(($data['resultado'] ?? ''), ['error'], true),
            'resultado' => $data['resultado'] ?? 'omitido',
            'mensaje' => $data['mensaje'] ?? '',
            'external_id' => trim((string)($data['external_id'] ?? '')),
            'role_legacy' => trim((string)($data['role_legacy'] ?? '')),
            'detalle' => $detalle,
        ];
    }
}
