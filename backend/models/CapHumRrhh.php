<?php

namespace Models;

use Core\Database;
use Core\Model;

class CapHumRrhh extends Model
{
    private const MODULO_ACTUALIZAR_DATOS_RRHH = 82;
    private const MODULO_REVISION_ACTUALIZACIONES_RRHH = 83;
    private const MODULO_AGREGAR_USUARIO_RRHH = 87;
    private const MODULO_EDITAR_USUARIO_RRHH = 88;
    private const MODULO_GESTION_VISUALIZAR_CONTRASENA = 101;

    private static function usuarioTieneModuloWeb(int $moduloId): bool
    {
        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        return in_array($moduloId, $modulos, true);
    }

    private static function pushLegacyConfig(): array
    {
        $cfg = function_exists('config_api_load_from_db') ? config_api_load_from_db() : [];
        $leerValor = static function (array $keys) use ($cfg): string {
            foreach ($keys as $key) {
                $valor = trim((string) ($cfg[$key] ?? ''));
                if ($valor !== '') {
                    return $valor;
                }
                $env = getenv($key);
                if ($env !== false && trim((string) $env) !== '') {
                    return trim((string) $env);
                }
            }
            return '';
        };

        $baseUrl = $leerValor(['MOTOS_ADJUDICADAS_PUSH_BASE_URL']);
        if ($baseUrl === '') {
            $baseUrl = 'https://motosadjudicadas-601258367060.us-central1.run.app';
        }

        return [
            'base_url' => rtrim($baseUrl, '/'),
            'api_key' => $leerValor(['MOTOS_ADJUDICADAS_API_KEY', 'MOTOS_ADJUDICADAS_TOKEN']),
        ];
    }

    private static function pushLegacyPost(string $path, array $payload): array
    {
        $cfg = self::pushLegacyConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '') {
            return ['success' => false, 'message' => 'Servicio de notificaciones no configurado.'];
        }
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'cURL no esta disponible en este servidor.'];
        }

        $ch = curl_init($cfg['base_url'] . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: ' . $cfg['api_key'],
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($raw === false ? '' : (string) $raw, true);
        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'message' => is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? 'No se pudo enviar la notificacion.')
                    : ($err ?: 'No se pudo enviar la notificacion.'),
                'http_code' => $httpCode,
            ];
        }

        return [
            'success' => true,
            'http_code' => $httpCode,
            'response' => is_array($decoded) ? $decoded : null,
        ];
    }

    private static function notificarSolicitudActualizacionInfo(array $persona, int $idSolicitud, int $totalCampos): array
    {
        $externalId = trim((string) ($persona['numero_empleado'] ?? ''));
        if ($externalId === '') {
            return ['success' => false, 'omitir' => true, 'message' => 'La persona no tiene numero de empleado para notificar.'];
        }

        return self::pushLegacyPost('/api/push-notifications/legacy/send', [
            'external_id' => $externalId,
            'titulo' => 'Actualización de información requerida',
            'mensaje' => 'Es necesario actualizar tu información. Entra a la app para completar los datos solicitados.',
            'evento' => 'rrhh_actualizacion_info',
            'data' => [
                'type' => 'rrhh_actualizacion_info',
                'notification_type' => 'rrhh_actualizacion_info',
                'screen' => 'ActualizacionInformacionScreen',
                'id_solicitud' => $idSolicitud,
                'id_persona' => (int) ($persona['id'] ?? 0),
                'numero_empleado' => $externalId,
                'campos' => $totalCampos,
            ],
        ]);
    }

    public static function asegurarTablas(Database $db): void
    {
        CapHum::asegurarTablaTrayectoriaPuesto($db);

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_datos_rrhh (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            registro_patronal VARCHAR(120) NULL,
            codigo_contpaq VARCHAR(80) NULL,
            fecha_contpaq DATE NULL,
            fecha_imss_alta DATE NULL,
            id_departamento INT NULL,
            id_area INT NULL,
            id_puesto INT NULL,
            id_jefe INT NULL,
            puesto_texto VARCHAR(180) NULL,
            departamento_texto VARCHAR(180) NULL,
            area_texto VARCHAR(180) NULL,
            direccion_organizacional VARCHAR(180) NULL,
            ubicacion_laboral VARCHAR(180) NULL,
            municipio_laboral VARCHAR(180) NULL,
            jefe_directo_texto VARCHAR(220) NULL,
            sueldo_neto DECIMAL(12,2) NULL,
            sueldo_quincenal DECIMAL(12,2) NULL,
            sueldo_bruto DECIMAL(12,2) NULL,
            salario_diario DECIMAL(12,2) NULL,
            sbc DECIMAL(12,2) NULL,
            rfc VARCHAR(20) NULL,
            nss VARCHAR(20) NULL,
            entidad_federativa_rfc VARCHAR(120) NULL,
            anio INT NULL,
            mes TINYINT NULL,
            dia TINYINT NULL,
            fecha_nacimiento DATE NULL,
            sexo VARCHAR(20) NULL,
            estado_civil VARCHAR(60) NULL,
            tipo_sangre VARCHAR(20) NULL,
            alergias TEXT NULL,
            enfermedades_cronicas TEXT NULL,
            enfermedades_hereditarias TEXT NULL,
            medicamentos_actuales TEXT NULL,
            discapacidad_condicion TEXT NULL,
            observaciones_medicas TEXT NULL,
            carta_no_credito VARCHAR(120) NULL,
            carta_no_nomina_bbva VARCHAR(120) NULL,
            sueldo_bruto_letra VARCHAR(255) NULL,
            observaciones TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_persona_datos_rrhh_persona (id_persona),
            KEY idx_persona_datos_rrhh_rfc (rfc),
            KEY idx_persona_datos_rrhh_nss (nss)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        self::asegurarColumnasDatosRrhh($db);

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.telefonos_persona (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            numero VARCHAR(30) NOT NULL,
            tipo VARCHAR(40) NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_telefonos_persona_persona (id_persona),
            KEY idx_telefonos_persona_numero (numero)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.correos_persona (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            correo VARCHAR(160) NOT NULL,
            tipo VARCHAR(40) NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_correos_persona_persona (id_persona),
            KEY idx_correos_persona_correo (correo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.domicilio_persona (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            domicilio_texto VARCHAR(500) NOT NULL,
            codigo_postal VARCHAR(12) NULL,
            tipo VARCHAR(40) NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_domicilio_persona_persona (id_persona),
            KEY idx_domicilio_persona_estatus (estatus)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_cuenta_bancaria (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            clabe VARCHAR(30) NULL,
            numero_cuenta VARCHAR(40) NULL,
            id_banco INT NULL,
            nombre_banco VARCHAR(120) NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_cuenta_bancaria_persona (id_persona),
            KEY idx_cuenta_bancaria_clabe (clabe),
            KEY idx_cuenta_bancaria_cuenta (numero_cuenta)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_credito_laboral (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            tipo_credito VARCHAR(80) NULL,
            numero_credito VARCHAR(80) NULL,
            monto_descontar DECIMAL(12,2) NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_credito_laboral_persona (id_persona)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.contacto_persona_emergencia (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            nombre_contacto VARCHAR(220) NOT NULL,
            parentesco VARCHAR(80) NULL,
            numero VARCHAR(30) NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_contacto_emergencia_persona (id_persona)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_beneficiario_fallecimiento (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            nombre_beneficiario VARCHAR(220) NOT NULL,
            parentesco VARCHAR(80) NULL,
            numero VARCHAR(30) NULL,
            porcentaje DECIMAL(5,2) NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_beneficiario_persona (id_persona)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.catalogo_observacion_persona (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(160) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_catalogo_observacion_nombre (nombre)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_observacion (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            id_catalogo_observacion INT NULL,
            observacion TEXT NOT NULL,
            estatus ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_persona_observacion_persona (id_persona),
            KEY idx_persona_observacion_catalogo (id_catalogo_observacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_actualizacion_info (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            id_solicita INT NULL,
            origen VARCHAR(80) NOT NULL DEFAULT 'gestion_personal',
            estatus ENUM('Pendiente','Enviada','Respondida','EnRevision','Aprobada','Rechazada','Procesada','Cancelada','Error') NOT NULL DEFAULT 'Pendiente',
            observaciones TEXT NULL,
            enviado_app TINYINT(1) NOT NULL DEFAULT 0,
            enviado_app_at DATETIME NULL,
            respuesta_app TEXT NULL,
            ultimo_error_app TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_actualizacion_info_persona (id_persona),
            KEY idx_actualizacion_info_estatus (estatus),
            KEY idx_actualizacion_info_enviado_app (enviado_app)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_actualizacion_info_detalle (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_solicitud INT NOT NULL,
            campo VARCHAR(80) NOT NULL,
            etiqueta VARCHAR(160) NOT NULL,
            tipo_campo VARCHAR(40) NULL,
            grupo VARCHAR(80) NULL,
            servicio_catalogo VARCHAR(120) NULL,
            valor_anterior TEXT NULL,
            valor_nuevo TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_actualizacion_info_detalle_solicitud (id_solicitud),
            KEY idx_actualizacion_info_detalle_campo (campo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        self::asegurarColumnasActualizacionInfoDetalle($db);

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_actualizacion_info_respuesta (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_solicitud INT NOT NULL,
            id_detalle INT NOT NULL,
            campo VARCHAR(120) NOT NULL,
            valor_anterior TEXT NULL,
            valor_nuevo TEXT NULL,
            comentario TEXT NULL,
            estatus ENUM('Pendiente','EnRevision','Aprobada','Rechazada') NOT NULL DEFAULT 'EnRevision',
            recibido_app TINYINT(1) NOT NULL DEFAULT 1,
            recibido_app_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_actualizacion_info_respuesta_detalle (id_detalle),
            KEY idx_actualizacion_info_respuesta_solicitud (id_solicitud)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        self::asegurarEstatusActualizacionInfo($db);
    }

    private static function asegurarEstatusActualizacionInfo(Database $db): void
    {
        $columna = $db->queryOne("SHOW COLUMNS FROM estado_cuenta.persona_actualizacion_info LIKE 'estatus'");
        $tipo = strtolower((string)($columna['Type'] ?? $columna['type'] ?? ''));
        if ($tipo && strpos($tipo, 'respondida') === false) {
            $db->CRUD("ALTER TABLE estado_cuenta.persona_actualizacion_info
                MODIFY estatus ENUM('Pendiente','Enviada','Respondida','EnRevision','Aprobada','Rechazada','Procesada','Cancelada','Error')
                NOT NULL DEFAULT 'Pendiente'");
        }
    }

    private static function asegurarColumnasActualizacionInfoDetalle(Database $db): void
    {
        $columnas = [
            'tipo_campo' => 'VARCHAR(40) NULL AFTER etiqueta',
            'grupo' => 'VARCHAR(80) NULL AFTER tipo_campo',
            'servicio_catalogo' => 'VARCHAR(120) NULL AFTER grupo',
        ];

        foreach ($columnas as $nombre => $definicion) {
            $existe = $db->queryOne("SHOW COLUMNS FROM estado_cuenta.persona_actualizacion_info_detalle LIKE :columna", [
                'columna' => $nombre,
            ]);
            if (!$existe) {
                $db->CRUD("ALTER TABLE estado_cuenta.persona_actualizacion_info_detalle ADD COLUMN {$nombre} {$definicion}");
            }
        }
    }

    private static function asegurarColumnasDatosRrhh(Database $db): void
    {
        $columnas = [
            'id_departamento' => 'INT NULL AFTER fecha_imss_alta',
            'id_area' => 'INT NULL AFTER id_departamento',
            'id_puesto' => 'INT NULL AFTER id_area',
            'id_jefe' => 'INT NULL AFTER id_puesto',
            'puesto_texto' => 'VARCHAR(180) NULL AFTER id_jefe',
            'departamento_texto' => 'VARCHAR(180) NULL AFTER puesto_texto',
            'area_texto' => 'VARCHAR(180) NULL AFTER departamento_texto',
            'anio' => 'INT NULL AFTER entidad_federativa_rfc',
            'mes' => 'TINYINT NULL AFTER anio',
            'dia' => 'TINYINT NULL AFTER mes',
            'estado_civil' => 'VARCHAR(60) NULL AFTER sexo',
            'tipo_sangre' => 'VARCHAR(20) NULL AFTER sexo',
            'alergias' => 'TEXT NULL AFTER tipo_sangre',
            'enfermedades_cronicas' => 'TEXT NULL AFTER alergias',
            'enfermedades_hereditarias' => 'TEXT NULL AFTER enfermedades_cronicas',
            'medicamentos_actuales' => 'TEXT NULL AFTER enfermedades_hereditarias',
            'discapacidad_condicion' => 'TEXT NULL AFTER medicamentos_actuales',
            'observaciones_medicas' => 'TEXT NULL AFTER discapacidad_condicion',
        ];

        foreach ($columnas as $nombre => $definicion) {
            $existe = $db->queryOne("SHOW COLUMNS FROM estado_cuenta.persona_datos_rrhh LIKE :columna", [
                'columna' => $nombre,
            ]);
            if (!$existe) {
                $db->CRUD("ALTER TABLE estado_cuenta.persona_datos_rrhh ADD COLUMN {$nombre} {$definicion}");
            }
        }
    }

    private static function texto($value, int $max = 255): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') return null;
        return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
    }

    private static function decimal($value): ?float
    {
        $value = trim(str_replace([',', '$'], ['', ''], (string) ($value ?? '')));
        return ($value !== '' && is_numeric($value)) ? (float) $value : null;
    }

    private static function fecha($value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') return null;
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private static function partesFechaNacimiento(?string $fecha): array
    {
        if (!$fecha) {
            return [null, null, null];
        }
        $ts = strtotime($fecha);
        if (!$ts) {
            return [null, null, null];
        }
        return [(int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts)];
    }

    private static function lista($items): array
    {
        if (!is_array($items)) return [];
        return array_values(array_filter($items, function ($item) {
            if (!is_array($item)) return false;
            foreach ($item as $value) {
                if (trim((string) $value) !== '') return true;
            }
            return false;
        }));
    }

    private static function estatus($value): string
    {
        return ((string) $value === 'Inactivo') ? 'Inactivo' : 'Activo';
    }

    private static function siguienteNumeroEmpleadoLibre(Database $db): string
    {
        $row = $db->queryOne(
            "SELECT COALESCE(MAX(CAST(numero_empleado AS UNSIGNED)), 0) AS mx
             FROM estado_cuenta.persona
             WHERE TRIM(numero_empleado) <> ''
               AND TRIM(numero_empleado) REGEXP '^[0-9]+$'"
        );

        $next = isset($row['mx']) ? (int) $row['mx'] + 1 : 1;
        for ($i = 0; $i < 100000; $i++) {
            $candidate = (string) $next;
            $existe = $db->queryOne(
                'SELECT 1 AS ok FROM estado_cuenta.persona WHERE numero_empleado = :numero LIMIT 1',
                ['numero' => $candidate]
            );
            if (!$existe) {
                return $candidate;
            }
            $next++;
        }

        return 'RRHH' . strtoupper(bin2hex(random_bytes(4)));
    }

    private static function datosBase(array $data): array
    {
        return [
            'persona' => is_array($data['persona'] ?? null) ? $data['persona'] : [],
            'rrhh' => is_array($data['rrhh'] ?? null) ? $data['rrhh'] : [],
            'nomina' => is_array($data['nomina'] ?? null) ? $data['nomina'] : [],
            'telefonos' => self::lista($data['telefonos'] ?? []),
            'correos' => self::lista($data['correos'] ?? []),
            'domicilios' => self::lista($data['domicilios'] ?? []),
            'cuentas' => self::lista($data['cuentas_bancarias'] ?? []),
            'contactos' => self::lista($data['contactos_emergencia'] ?? []),
            'beneficiarios' => self::lista($data['beneficiarios'] ?? []),
        ];
    }

    private static function validarPersonaBasica(array $persona): array
    {
        $nombres = self::texto($persona['nombres'] ?? '', 120);
        $apellidop = self::texto($persona['apellidop'] ?? '', 120);
        if (!$nombres || !$apellidop) {
            return [false, 'Nombre y apellido paterno son obligatorios.'];
        }

        $idPais = (int) ($persona['id_pais'] ?? 0);
        if ($idPais <= 0) {
            return [false, 'Debe seleccionar el pais de la persona.'];
        }

        return [true, null];
    }

    private static function jefeSeleccionado(array $rrhh): array
    {
        $jefeRaw = trim((string) ($rrhh['jefe_id'] ?? ''));
        $idJefe = null;
        $idVacanteJefe = null;
        if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
            $idVacanteJefe = (int) $m[1];
        } elseif ($jefeRaw !== '') {
            $idJefe = (int) $jefeRaw;
        }
        return [$idJefe, $idVacanteJefe];
    }

    /**
     * Valida una asignación de jefe con las mismas reglas del organigrama.
     * Las jefaturas vigentes pueden cruzar departamentos: la relación directa
     * registrada es la fuente de verdad, no el puesto actual de cada persona.
     */
    private static function validarJefeDirectoEstructura(Database $db, int $idPersona, array $rrhh): array
    {
        [$idJefe, $idVacanteJefe] = self::jefeSeleccionado($rrhh);
        if (!$idJefe && !$idVacanteJefe) {
            return [true, null];
        }

        if ($idJefe && $idJefe === $idPersona) {
            return [false, 'Una persona no puede asignarse como su propio jefe directo.'];
        }

        if ($idVacanteJefe) {
            $vacante = $db->queryOne("
                SELECT v.id
                FROM estado_cuenta.vacantes_personal v
                WHERE v.id = :id_vacante
                  AND UPPER(TRIM(v.estatus)) = 'ACTIVA'
                LIMIT 1
            ", [
                'id_vacante' => $idVacanteJefe,
            ]);
            return $vacante
                ? [true, null]
                : [false, 'La vacante seleccionada ya no esta activa.'];
        }

        $jefe = $db->queryOne("
            SELECT jefe.id
            FROM estado_cuenta.persona jefe
            WHERE jefe.id = :id_jefe
              AND COALESCE(LOWER(TRIM(jefe.estatus)), 'activo') NOT IN ('baja', 'transito de baja')
            LIMIT 1
        ", [
            'id_jefe' => $idJefe,
        ]);

        if (!$jefe) {
            return [false, 'El jefe directo seleccionado no esta activo.'];
        }

        if (self::jefeGeneraCiclo($db, $idPersona, $idJefe)) {
            return [false, 'No se puede asignar ese jefe porque generaria un ciclo en el organigrama.'];
        }

        return [true, null];
    }

    private static function jefeGeneraCiclo(Database $db, int $idPersona, int $idJefe): bool
    {
        if ($idPersona <= 0 || $idJefe <= 0) {
            return false;
        }

        $actual = $idJefe;
        $vistos = [];
        for ($i = 0; $i < 80 && $actual > 0; $i++) {
            if ($actual === $idPersona) {
                return true;
            }
            if (isset($vistos[$actual])) {
                return false;
            }
            $vistos[$actual] = true;

            $relacion = $db->queryOne("
                SELECT id_jefe, id_vacante_jefe
                FROM estado_cuenta.asigna_jefe
                WHERE id_persona = :id_persona
                ORDER BY id DESC
                LIMIT 1
            ", ['id_persona' => $actual]);
            if (!$relacion) {
                return false;
            }
            if (!empty($relacion['id_jefe'])) {
                $actual = (int)$relacion['id_jefe'];
                continue;
            }
            if (!empty($relacion['id_vacante_jefe'])) {
                $vacante = $db->queryOne("
                    SELECT id_jefe
                    FROM estado_cuenta.vacantes_personal
                    WHERE id = :id_vacante
                    LIMIT 1
                ", ['id_vacante' => (int)$relacion['id_vacante_jefe']]);
                $actual = (int)($vacante['id_jefe'] ?? 0);
                continue;
            }
            return false;
        }

        return false;
    }

    private static function guardarDatosRrhh(Database $db, int $idPersona, array $persona, array $rrhh, array $nomina): void
    {
        [$idJefe] = self::jefeSeleccionado($rrhh);
        $idArea = !empty($rrhh['area_id']) ? (int) $rrhh['area_id'] : null;
        $idPuesto = !empty($rrhh['puesto_id']) ? (int) $rrhh['puesto_id'] : null;
        $fechaNacimiento = self::fecha($persona['fecha_nacimiento'] ?? '');
        [$anioNacimiento, $mesNacimiento, $diaNacimiento] = self::partesFechaNacimiento($fechaNacimiento);

        $params = [
            'id_persona' => $idPersona,
            'registro_patronal' => self::texto($rrhh['registro_patronal'] ?? '', 120),
            'codigo_contpaq' => self::texto($rrhh['codigo_contpaq'] ?? '', 80),
            'fecha_contpaq' => self::fecha($rrhh['fecha_contpaq'] ?? ''),
            'fecha_imss_alta' => self::fecha($rrhh['fecha_imss_alta'] ?? ''),
            'id_departamento' => !empty($rrhh['departamento_id']) ? (int) $rrhh['departamento_id'] : null,
            'id_area' => $idArea,
            'id_puesto' => $idPuesto,
            'id_jefe' => $idJefe,
            'puesto_texto' => self::texto($rrhh['puesto_texto'] ?? '', 180),
            'departamento_texto' => self::texto($rrhh['departamento_texto'] ?? '', 180),
            'area_texto' => self::texto($rrhh['area_texto'] ?? '', 180),
            'direccion_organizacional' => self::texto($rrhh['direccion_organizacional'] ?? '', 180),
            'ubicacion_laboral' => self::texto($rrhh['ubicacion_laboral'] ?? '', 180),
            'municipio_laboral' => self::texto($rrhh['municipio_laboral'] ?? '', 180),
            'jefe_directo_texto' => self::texto($rrhh['jefe_directo_texto'] ?? '', 220),
            'sueldo_neto' => self::decimal($nomina['sueldo_neto'] ?? null),
            'sueldo_quincenal' => self::decimal($nomina['sueldo_quincenal'] ?? null),
            'sueldo_bruto' => self::decimal($nomina['sueldo_bruto'] ?? null),
            'salario_diario' => self::decimal($nomina['salario_diario'] ?? null),
            'sbc' => self::decimal($nomina['sbc'] ?? null),
            'rfc' => self::texto($persona['rfc'] ?? '', 20),
            'nss' => self::texto($persona['nss'] ?? '', 20),
            'entidad_federativa_rfc' => self::texto($persona['entidad_federativa_rfc'] ?? '', 120),
            'anio' => $anioNacimiento,
            'mes' => $mesNacimiento,
            'dia' => $diaNacimiento,
            'fecha_nacimiento' => $fechaNacimiento,
            'sexo' => self::texto($persona['sexo'] ?? '', 20),
            'estado_civil' => self::texto($persona['estado_civil'] ?? '', 60),
            'tipo_sangre' => self::texto($rrhh['tipo_sangre'] ?? '', 20),
            'alergias' => self::texto($rrhh['alergias'] ?? '', 5000),
            'enfermedades_cronicas' => self::texto($rrhh['enfermedades_cronicas'] ?? '', 5000),
            'enfermedades_hereditarias' => self::texto($rrhh['enfermedades_hereditarias'] ?? '', 5000),
            'medicamentos_actuales' => self::texto($rrhh['medicamentos_actuales'] ?? '', 5000),
            'discapacidad_condicion' => self::texto($rrhh['discapacidad_condicion'] ?? '', 5000),
            'observaciones_medicas' => self::texto($rrhh['observaciones_medicas'] ?? '', 5000),
            'carta_no_credito' => self::texto($rrhh['carta_no_credito'] ?? '', 120),
            'carta_no_nomina_bbva' => self::texto($rrhh['carta_no_nomina_bbva'] ?? '', 120),
            'sueldo_bruto_letra' => self::texto($nomina['sueldo_bruto_letra'] ?? '', 255),
            'observaciones' => self::texto($GLOBALS['rrhh_observaciones_actual'] ?? '', 5000),
        ];

        $db->CRUD("INSERT INTO estado_cuenta.persona_datos_rrhh
            (id_persona, registro_patronal, codigo_contpaq, fecha_contpaq, fecha_imss_alta, id_departamento, id_area,
             id_puesto, id_jefe, puesto_texto, departamento_texto, area_texto, direccion_organizacional,
             ubicacion_laboral, municipio_laboral, jefe_directo_texto, sueldo_neto, sueldo_quincenal, sueldo_bruto,
             salario_diario, sbc, rfc, nss, entidad_federativa_rfc, anio, mes, dia, fecha_nacimiento, sexo, estado_civil,
             tipo_sangre, alergias, enfermedades_cronicas, enfermedades_hereditarias, medicamentos_actuales,
             discapacidad_condicion, observaciones_medicas, carta_no_credito, carta_no_nomina_bbva,
             sueldo_bruto_letra, observaciones)
            VALUES
            (:id_persona, :registro_patronal, :codigo_contpaq, :fecha_contpaq, :fecha_imss_alta, :id_departamento, :id_area,
             :id_puesto, :id_jefe, :puesto_texto, :departamento_texto, :area_texto, :direccion_organizacional,
             :ubicacion_laboral, :municipio_laboral, :jefe_directo_texto, :sueldo_neto, :sueldo_quincenal, :sueldo_bruto,
             :salario_diario, :sbc, :rfc, :nss, :entidad_federativa_rfc, :anio, :mes, :dia, :fecha_nacimiento, :sexo, :estado_civil,
             :tipo_sangre, :alergias, :enfermedades_cronicas, :enfermedades_hereditarias, :medicamentos_actuales,
             :discapacidad_condicion, :observaciones_medicas, :carta_no_credito, :carta_no_nomina_bbva,
             :sueldo_bruto_letra, :observaciones)
            ON DUPLICATE KEY UPDATE
             registro_patronal = VALUES(registro_patronal), codigo_contpaq = VALUES(codigo_contpaq),
             fecha_contpaq = VALUES(fecha_contpaq), fecha_imss_alta = VALUES(fecha_imss_alta),
             id_departamento = VALUES(id_departamento), id_area = VALUES(id_area), id_puesto = VALUES(id_puesto),
             id_jefe = VALUES(id_jefe), puesto_texto = VALUES(puesto_texto), departamento_texto = VALUES(departamento_texto),
             area_texto = VALUES(area_texto), direccion_organizacional = VALUES(direccion_organizacional),
             ubicacion_laboral = VALUES(ubicacion_laboral), municipio_laboral = VALUES(municipio_laboral),
             jefe_directo_texto = VALUES(jefe_directo_texto), sueldo_neto = VALUES(sueldo_neto),
             sueldo_quincenal = VALUES(sueldo_quincenal), sueldo_bruto = VALUES(sueldo_bruto),
             salario_diario = VALUES(salario_diario), sbc = VALUES(sbc), rfc = VALUES(rfc), nss = VALUES(nss),
             entidad_federativa_rfc = VALUES(entidad_federativa_rfc), anio = VALUES(anio), mes = VALUES(mes),
             dia = VALUES(dia), fecha_nacimiento = VALUES(fecha_nacimiento), sexo = VALUES(sexo),
             estado_civil = VALUES(estado_civil),
             tipo_sangre = VALUES(tipo_sangre), alergias = VALUES(alergias),
             enfermedades_cronicas = VALUES(enfermedades_cronicas),
             enfermedades_hereditarias = VALUES(enfermedades_hereditarias),
             medicamentos_actuales = VALUES(medicamentos_actuales),
             discapacidad_condicion = VALUES(discapacidad_condicion),
             observaciones_medicas = VALUES(observaciones_medicas),
             carta_no_credito = VALUES(carta_no_credito), carta_no_nomina_bbva = VALUES(carta_no_nomina_bbva),
             sueldo_bruto_letra = VALUES(sueldo_bruto_letra), observaciones = VALUES(observaciones)", $params);
    }

    private static function reemplazarListas(Database $db, int $idPersona, array $datos): void
    {
        foreach ([
            'telefonos_persona',
            'correos_persona',
            'domicilio_persona',
            'persona_cuenta_bancaria',
            'persona_credito_laboral',
            'contacto_persona_emergencia',
            'persona_beneficiario_fallecimiento',
            'persona_observacion',
        ] as $tabla) {
            $db->CRUD("DELETE FROM estado_cuenta.{$tabla} WHERE id_persona = :id_persona", ['id_persona' => $idPersona]);
        }

        foreach ($datos['telefonos'] as $tel) {
            $numero = self::texto($tel['numero'] ?? '', 30);
            if (!$numero) continue;
            $db->CRUD("INSERT INTO estado_cuenta.telefonos_persona (id_persona, numero, tipo, estatus) VALUES (:id_persona, :numero, :tipo, :estatus)", [
                'id_persona' => $idPersona,
                'numero' => $numero,
                'tipo' => self::texto($tel['tipo'] ?? 'Personal', 40),
                'estatus' => self::estatus($tel['estatus'] ?? 'Activo'),
            ]);
        }

        foreach ($datos['correos'] as $mail) {
            $correo = self::texto($mail['correo'] ?? '', 160);
            if (!$correo) continue;
            $db->CRUD("INSERT INTO estado_cuenta.correos_persona (id_persona, correo, tipo, estatus) VALUES (:id_persona, :correo, :tipo, :estatus)", [
                'id_persona' => $idPersona,
                'correo' => $correo,
                'tipo' => self::texto($mail['tipo'] ?? 'Personal', 40),
                'estatus' => self::estatus($mail['estatus'] ?? 'Activo'),
            ]);
        }

        foreach ($datos['domicilios'] as $dom) {
            $domicilio = self::texto($dom['domicilio_texto'] ?? '', 500);
            if (!$domicilio) continue;
            $db->CRUD("INSERT INTO estado_cuenta.domicilio_persona (id_persona, domicilio_texto, codigo_postal, tipo, estatus) VALUES (:id_persona, :domicilio_texto, :codigo_postal, :tipo, :estatus)", [
                'id_persona' => $idPersona,
                'domicilio_texto' => $domicilio,
                'codigo_postal' => self::texto($dom['codigo_postal'] ?? '', 12),
                'tipo' => self::texto($dom['tipo'] ?? 'Particular', 40),
                'estatus' => self::estatus($dom['estatus'] ?? 'Activo'),
            ]);
        }

        foreach ($datos['cuentas'] as $cuenta) {
            if (!self::texto($cuenta['clabe'] ?? '') && !self::texto($cuenta['numero_cuenta'] ?? '') && !self::texto($cuenta['nombre_banco'] ?? '')) continue;
            $db->CRUD("INSERT INTO estado_cuenta.persona_cuenta_bancaria (id_persona, clabe, numero_cuenta, id_banco, nombre_banco, estatus) VALUES (:id_persona, :clabe, :numero_cuenta, :id_banco, :nombre_banco, :estatus)", [
                'id_persona' => $idPersona,
                'clabe' => self::texto($cuenta['clabe'] ?? '', 30),
                'numero_cuenta' => self::texto($cuenta['numero_cuenta'] ?? '', 40),
                'id_banco' => !empty($cuenta['id_banco']) ? (int) $cuenta['id_banco'] : null,
                'nombre_banco' => self::texto($cuenta['nombre_banco'] ?? '', 120),
                'estatus' => self::estatus($cuenta['estatus'] ?? 'Activo'),
            ]);
        }

        $tipoCredito = self::texto($datos['nomina']['credito_infonavit_fonacot'] ?? '', 80);
        $numeroCredito = self::texto($datos['nomina']['no_credito'] ?? '', 80);
        $montoDescontar = self::decimal($datos['nomina']['monto_descontar'] ?? null);
        if ($tipoCredito || $numeroCredito || $montoDescontar !== null) {
            $db->CRUD("INSERT INTO estado_cuenta.persona_credito_laboral (id_persona, tipo_credito, numero_credito, monto_descontar, estatus) VALUES (:id_persona, :tipo_credito, :numero_credito, :monto_descontar, 'Activo')", [
                'id_persona' => $idPersona,
                'tipo_credito' => $tipoCredito,
                'numero_credito' => $numeroCredito,
                'monto_descontar' => $montoDescontar,
            ]);
        }

        foreach ($datos['contactos'] as $contacto) {
            $nombre = self::texto($contacto['nombre_contacto'] ?? '', 220);
            if (!$nombre) continue;
            $db->CRUD("INSERT INTO estado_cuenta.contacto_persona_emergencia (id_persona, nombre_contacto, parentesco, numero, estatus) VALUES (:id_persona, :nombre_contacto, :parentesco, :numero, :estatus)", [
                'id_persona' => $idPersona,
                'nombre_contacto' => $nombre,
                'parentesco' => self::texto($contacto['parentesco'] ?? '', 80),
                'numero' => self::texto($contacto['numero'] ?? '', 30),
                'estatus' => self::estatus($contacto['estatus'] ?? 'Activo'),
            ]);
        }

        foreach ($datos['beneficiarios'] as $beneficiario) {
            $nombre = self::texto($beneficiario['nombre_beneficiario'] ?? '', 220);
            if (!$nombre) continue;
            $db->CRUD("INSERT INTO estado_cuenta.persona_beneficiario_fallecimiento (id_persona, nombre_beneficiario, parentesco, numero, porcentaje, estatus) VALUES (:id_persona, :nombre_beneficiario, :parentesco, :numero, :porcentaje, :estatus)", [
                'id_persona' => $idPersona,
                'nombre_beneficiario' => $nombre,
                'parentesco' => self::texto($beneficiario['parentesco'] ?? '', 80),
                'numero' => self::texto($beneficiario['numero'] ?? '', 30),
                'porcentaje' => self::decimal($beneficiario['porcentaje'] ?? null),
                'estatus' => self::estatus($beneficiario['estatus'] ?? 'Activo'),
            ]);
        }

        $observacion = self::texto($GLOBALS['rrhh_observaciones_actual'] ?? '', 5000);
        if ($observacion) {
            $db->CRUD("INSERT INTO estado_cuenta.catalogo_observacion_persona (nombre, activo) VALUES ('Observacion general', 1) ON DUPLICATE KEY UPDATE activo = 1");
            $cat = $db->queryOne("SELECT id FROM estado_cuenta.catalogo_observacion_persona WHERE nombre = 'Observacion general' LIMIT 1");
            $db->CRUD("INSERT INTO estado_cuenta.persona_observacion (id_persona, id_catalogo_observacion, observacion, estatus) VALUES (:id_persona, :id_catalogo_observacion, :observacion, 'Activo')", [
                'id_persona' => $idPersona,
                'id_catalogo_observacion' => $cat['id'] ?? null,
                'observacion' => $observacion,
            ]);
        }
    }

    private static function completarPuestoRrhhDesdeTexto(Database $db, array &$rrhh): void
    {
        $idPuesto = !empty($rrhh['puesto_id']) ? (int) $rrhh['puesto_id'] : 0;
        if ($idPuesto > 0) {
            if (empty($rrhh['departamento_id'])) {
                $puesto = $db->queryOne(
                    "SELECT departamento_id FROM estado_cuenta.puesto WHERE id = :id_puesto LIMIT 1",
                    ['id_puesto' => $idPuesto]
                );
                if (!empty($puesto['departamento_id'])) {
                    $rrhh['departamento_id'] = (int) $puesto['departamento_id'];
                }
            }
            return;
        }

        $puestoTexto = self::texto($rrhh['puesto_texto'] ?? $rrhh['puesto'] ?? '', 180);
        if ($puestoTexto === '') {
            return;
        }

        $params = ['puesto' => strtolower(trim($puestoTexto))];
        $whereDepartamento = '';
        if (!empty($rrhh['departamento_id'])) {
            $whereDepartamento = ' AND departamento_id = :id_departamento';
            $params['id_departamento'] = (int) $rrhh['departamento_id'];
        }

        if ($whereDepartamento !== '') {
            $puesto = $db->queryOne(
                "SELECT id, departamento_id
                 FROM estado_cuenta.puesto
                 WHERE activo = 1
                   AND LOWER(TRIM(nombre)) = :puesto
                   {$whereDepartamento}
                 ORDER BY id DESC
                 LIMIT 1",
                $params
            );
        } else {
            $coincidencias = $db->queryAll(
                "SELECT id, departamento_id
                 FROM estado_cuenta.puesto
                 WHERE activo = 1
                   AND LOWER(TRIM(nombre)) = :puesto
                 ORDER BY id DESC
                 LIMIT 2",
                ['puesto' => $params['puesto']]
            );
            $puesto = count($coincidencias) === 1 ? $coincidencias[0] : null;
        }

        if (!empty($puesto['id'])) {
            $rrhh['puesto_id'] = (int) $puesto['id'];
            if (empty($rrhh['departamento_id']) && !empty($puesto['departamento_id'])) {
                $rrhh['departamento_id'] = (int) $puesto['departamento_id'];
            }
        }
    }

    private static function sincronizarAsignaciones(Database $db, int $idPersona, array $rrhh, int $idSesion = 0): void
    {
        self::completarPuestoRrhhDesdeTexto($db, $rrhh);
        $idPuesto = !empty($rrhh['puesto_id']) ? (int) $rrhh['puesto_id'] : null;
        [$idJefe, $idVacanteJefe] = self::jefeSeleccionado($rrhh);
        $puestosAntes = CapHum::puestosActivosTrayectoria($db, $idPersona);

        if ($idPuesto) {
            $existe = $db->queryOne("SELECT id FROM estado_cuenta.asigna_puesto WHERE id_persona = :id_persona AND id_puesto = :id_puesto AND activo = 1 LIMIT 1", [
                'id_persona' => $idPersona,
                'id_puesto' => $idPuesto,
            ]);
            if (!$existe) {
                $fechaAsignacionCdmx = CapHum::fechaHoraCdmx();
                $db->CRUD("UPDATE estado_cuenta.asigna_puesto SET activo = 0 WHERE id_persona = :id_persona AND activo = 1", ['id_persona' => $idPersona]);
                $db->CRUD("INSERT INTO estado_cuenta.asigna_puesto (id, id_persona, id_puesto, fecha_asignacion, activo) VALUES (DEFAULT, :id_persona, :id_puesto, :fecha_asignacion, 1)", [
                    'id_persona' => $idPersona,
                    'id_puesto' => $idPuesto,
                    'fecha_asignacion' => $fechaAsignacionCdmx,
                ]);
            }
        }

        CapHum::registrarCambiosTrayectoriaPuestos(
            $db,
            $idPersona,
            $puestosAntes,
            CapHum::puestosActivosTrayectoria($db, $idPersona),
            $idSesion,
            'edicion_rrhh'
        );

        if ($idJefe || $idVacanteJefe) {
            $actual = $db->queryOne("SELECT id_jefe, id_vacante_jefe FROM estado_cuenta.asigna_jefe WHERE id_persona = :id_persona ORDER BY id DESC LIMIT 1", ['id_persona' => $idPersona]);
            $mismo = $actual
                && (int)($actual['id_jefe'] ?? 0) === (int)($idJefe ?? 0)
                && (int)($actual['id_vacante_jefe'] ?? 0) === (int)($idVacanteJefe ?? 0);
            if (!$mismo) {
                $db->CRUD("INSERT INTO estado_cuenta.asigna_jefe (id, id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin) VALUES (DEFAULT, :id_persona, :id_jefe, :id_vacante_jefe, NOW(), NOW())", [
                    'id_persona' => $idPersona,
                    'id_jefe' => $idJefe,
                    'id_vacante_jefe' => $idVacanteJefe,
                ]);
            }
        }
    }

    public static function registrarUsuario(array $data, int $idSesion)
    {
        if (!self::usuarioTieneModuloWeb(self::MODULO_AGREGAR_USUARIO_RRHH)) {
            return self::resultado(false, 'No tienes permiso para registrar usuarios RR.HH.');
        }

        $persona = is_array($data['persona'] ?? null) ? $data['persona'] : [];
        $rrhh = is_array($data['rrhh'] ?? null) ? $data['rrhh'] : [];
        $nomina = is_array($data['nomina'] ?? null) ? $data['nomina'] : [];
        $telefonos = self::lista($data['telefonos'] ?? []);
        $correos = self::lista($data['correos'] ?? []);
        $domicilios = self::lista($data['domicilios'] ?? []);
        $cuentas = self::lista($data['cuentas_bancarias'] ?? []);
        $contactos = self::lista($data['contactos_emergencia'] ?? []);
        $beneficiarios = self::lista($data['beneficiarios'] ?? []);

        $nombres = self::texto($persona['nombres'] ?? '', 120);
        $apellidop = self::texto($persona['apellidop'] ?? '', 120);
        $apellidom = self::texto($persona['apellidom'] ?? '', 120);

        if (!$nombres || !$apellidop) {
            return self::resultado(false, 'Nombre y apellido paterno son obligatorios.');
        }

        $idPais = (int) ($persona['id_pais'] ?? 0);
        if ($idPais <= 0) {
            return self::resultado(false, 'Debe seleccionar el pais de la persona.');
        }

        try {
            $db = new Database();
            self::asegurarTablas($db);
            self::completarPuestoRrhhDesdeTexto($db, $rrhh);

            [$jefeValido, $mensajeJefe] = self::validarJefeDirectoEstructura($db, 0, $rrhh);
            if (!$jefeValido) {
                return self::resultado(false, $mensajeJefe);
            }

            $numeroEmpleado = self::texto($persona['numero_empleado'] ?? '', 40);
            if (!$numeroEmpleado || strcasecmp($numeroEmpleado, 'PEND') === 0 || strcasecmp($numeroEmpleado, 'PENDIENTE') === 0) {
                $numeroEmpleado = self::siguienteNumeroEmpleadoLibre($db);
            } elseif ($db->queryOne('SELECT id FROM estado_cuenta.persona WHERE numero_empleado = :numero LIMIT 1', ['numero' => $numeroEmpleado])) {
                return self::resultado(false, 'Ya existe una persona con ese numero de empleado.');
            }

            $usuario = self::texto($persona['usuario'] ?? '', 40);
            if ($usuario && $db->queryOne('SELECT id FROM estado_cuenta.persona WHERE user_name = :usuario LIMIT 1', ['usuario' => $usuario])) {
                return self::resultado(false, 'Ya existe una persona con ese usuario.');
            }

            $telefonoPrincipal = self::texto($telefonos[0]['numero'] ?? $persona['telefono_uno'] ?? '', 30);
            $correoPrincipal = self::texto($correos[0]['correo'] ?? $persona['correo'] ?? '', 160);
            $domicilioPrincipal = self::texto($domicilios[0]['domicilio_texto'] ?? $persona['domicilio'] ?? '', 500);
            $cpPrincipal = self::texto($domicilios[0]['codigo_postal'] ?? $persona['codigo_postal'] ?? '', 12);

            $db->beginTransaction();
            $db->CRUD("INSERT INTO estado_cuenta.persona
                (nombres, segundo_nombre, apellidop, apellidom, numero_empleado, codigo_contpac, correo, telefono_uno, telefono_dos,
                 estatus, user_name, password, fecha_ingreso, fecha_registro, id_pais, domicilio_calle_texto, codigo_postal, curp)
                VALUES
                (:nombres, :segundo_nombre, :apellidop, :apellidom, :numero_empleado, :codigo_contpac, :correo, :telefono_uno, :telefono_dos,
                 'Activo', :user_name, :password, :fecha_ingreso, NOW(), :id_pais, :domicilio_calle_texto, :codigo_postal, :curp)", [
                'nombres' => $nombres,
                'segundo_nombre' => self::texto($persona['segundo_nombre'] ?? '', 120),
                'apellidop' => $apellidop,
                'apellidom' => $apellidom,
                'numero_empleado' => $numeroEmpleado,
                'codigo_contpac' => self::texto($persona['codigo_contpac'] ?? '', 40),
                'correo' => $correoPrincipal,
                'telefono_uno' => $telefonoPrincipal,
                'telefono_dos' => self::texto($persona['telefono_dos'] ?? '', 30),
                'user_name' => $usuario,
                'password' => self::texto($persona['contrasena'] ?? '', 120),
                'fecha_ingreso' => self::fecha($rrhh['fecha_ingreso'] ?? $persona['fecha_ingreso'] ?? ''),
                'id_pais' => $idPais,
                'domicilio_calle_texto' => $domicilioPrincipal,
                'codigo_postal' => $cpPrincipal,
                'curp' => self::texto($persona['curp'] ?? '', 18),
            ]);

            $idPersona = $db->lastInsertId();
            if ($idPersona <= 0) {
                throw new \RuntimeException('No se pudo obtener el ID de la persona registrada.');
            }

            $idArea = !empty($rrhh['area_id']) ? (int) $rrhh['area_id'] : null;
            $idPuesto = !empty($rrhh['puesto_id']) ? (int) $rrhh['puesto_id'] : null;
            $jefeRaw = trim((string) ($rrhh['jefe_id'] ?? ''));
            $idJefe = null;
            $idVacanteJefe = null;
            if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
                $idVacanteJefe = (int) $m[1];
            } elseif ($jefeRaw !== '') {
                $idJefe = (int) $jefeRaw;
            }
            $fechaNacimiento = self::fecha($persona['fecha_nacimiento'] ?? '');
            [$anioNacimiento, $mesNacimiento, $diaNacimiento] = self::partesFechaNacimiento($fechaNacimiento);

            $db->CRUD("INSERT INTO estado_cuenta.persona_datos_rrhh
                (id_persona, registro_patronal, codigo_contpaq, fecha_contpaq, fecha_imss_alta, id_departamento, id_area,
                 id_puesto, id_jefe, puesto_texto, departamento_texto,
                 area_texto, direccion_organizacional,
                 ubicacion_laboral, municipio_laboral, jefe_directo_texto, sueldo_neto, sueldo_quincenal, sueldo_bruto,
                 salario_diario, sbc, rfc, nss, entidad_federativa_rfc, anio, mes, dia, fecha_nacimiento, sexo, estado_civil,
                 tipo_sangre, alergias, enfermedades_cronicas, enfermedades_hereditarias, medicamentos_actuales,
                 discapacidad_condicion, observaciones_medicas, carta_no_credito, carta_no_nomina_bbva,
                 sueldo_bruto_letra, observaciones)
                VALUES
                (:id_persona, :registro_patronal, :codigo_contpaq, :fecha_contpaq, :fecha_imss_alta, :id_departamento, :id_area,
                 :id_puesto, :id_jefe, :puesto_texto, :departamento_texto,
                 :area_texto, :direccion_organizacional,
                 :ubicacion_laboral, :municipio_laboral, :jefe_directo_texto, :sueldo_neto, :sueldo_quincenal, :sueldo_bruto,
                 :salario_diario, :sbc, :rfc, :nss, :entidad_federativa_rfc, :anio, :mes, :dia, :fecha_nacimiento, :sexo, :estado_civil,
                 :tipo_sangre, :alergias, :enfermedades_cronicas, :enfermedades_hereditarias, :medicamentos_actuales,
                 :discapacidad_condicion, :observaciones_medicas, :carta_no_credito, :carta_no_nomina_bbva,
                 :sueldo_bruto_letra, :observaciones)", [
                'id_persona' => $idPersona,
                'registro_patronal' => self::texto($rrhh['registro_patronal'] ?? '', 120),
                'codigo_contpaq' => self::texto($rrhh['codigo_contpaq'] ?? '', 80),
                'fecha_contpaq' => self::fecha($rrhh['fecha_contpaq'] ?? ''),
                'fecha_imss_alta' => self::fecha($rrhh['fecha_imss_alta'] ?? ''),
                'id_departamento' => !empty($rrhh['departamento_id']) ? (int) $rrhh['departamento_id'] : null,
                'id_area' => $idArea,
                'id_puesto' => $idPuesto,
                'id_jefe' => $idJefe,
                'puesto_texto' => self::texto($rrhh['puesto_texto'] ?? '', 180),
                'departamento_texto' => self::texto($rrhh['departamento_texto'] ?? '', 180),
                'area_texto' => self::texto($rrhh['area_texto'] ?? '', 180),
                'direccion_organizacional' => self::texto($rrhh['direccion_organizacional'] ?? '', 180),
                'ubicacion_laboral' => self::texto($rrhh['ubicacion_laboral'] ?? '', 180),
                'municipio_laboral' => self::texto($rrhh['municipio_laboral'] ?? '', 180),
                'jefe_directo_texto' => self::texto($rrhh['jefe_directo_texto'] ?? '', 220),
                'sueldo_neto' => self::decimal($nomina['sueldo_neto'] ?? null),
                'sueldo_quincenal' => self::decimal($nomina['sueldo_quincenal'] ?? null),
                'sueldo_bruto' => self::decimal($nomina['sueldo_bruto'] ?? null),
                'salario_diario' => self::decimal($nomina['salario_diario'] ?? null),
                'sbc' => self::decimal($nomina['sbc'] ?? null),
                'rfc' => self::texto($persona['rfc'] ?? '', 20),
                'nss' => self::texto($persona['nss'] ?? '', 20),
                'entidad_federativa_rfc' => self::texto($persona['entidad_federativa_rfc'] ?? '', 120),
                'anio' => $anioNacimiento,
                'mes' => $mesNacimiento,
                'dia' => $diaNacimiento,
                'fecha_nacimiento' => $fechaNacimiento,
                'sexo' => self::texto($persona['sexo'] ?? '', 20),
                'estado_civil' => self::texto($persona['estado_civil'] ?? '', 60),
                'tipo_sangre' => self::texto($rrhh['tipo_sangre'] ?? '', 20),
                'alergias' => self::texto($rrhh['alergias'] ?? '', 5000),
                'enfermedades_cronicas' => self::texto($rrhh['enfermedades_cronicas'] ?? '', 5000),
                'enfermedades_hereditarias' => self::texto($rrhh['enfermedades_hereditarias'] ?? '', 5000),
                'medicamentos_actuales' => self::texto($rrhh['medicamentos_actuales'] ?? '', 5000),
                'discapacidad_condicion' => self::texto($rrhh['discapacidad_condicion'] ?? '', 5000),
                'observaciones_medicas' => self::texto($rrhh['observaciones_medicas'] ?? '', 5000),
                'carta_no_credito' => self::texto($rrhh['carta_no_credito'] ?? '', 120),
                'carta_no_nomina_bbva' => self::texto($rrhh['carta_no_nomina_bbva'] ?? '', 120),
                'sueldo_bruto_letra' => self::texto($nomina['sueldo_bruto_letra'] ?? '', 255),
                'observaciones' => self::texto($data['observaciones'] ?? '', 5000),
            ]);

            if ($idPuesto) {
                $fechaAsignacionCdmx = CapHum::fechaHoraCdmx();
                $db->CRUD("INSERT INTO estado_cuenta.asigna_puesto
                    (id, id_persona, id_puesto, fecha_asignacion, activo)
                    VALUES (DEFAULT, :id_persona, :id_puesto, :fecha_asignacion, 1)", [
                    'id_persona' => $idPersona,
                    'id_puesto' => $idPuesto,
                    'fecha_asignacion' => $fechaAsignacionCdmx,
                ]);
                CapHum::registrarCambiosTrayectoriaPuestos(
                    $db,
                    $idPersona,
                    [],
                    CapHum::puestosActivosTrayectoria($db, $idPersona),
                    $idSesion,
                    'alta_rrhh'
                );
            }

            if ($idJefe || $idVacanteJefe) {
                $db->CRUD("INSERT INTO estado_cuenta.asigna_jefe
                    (id, id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
                    VALUES (DEFAULT, :id_persona, :id_jefe, :id_vacante_jefe, NOW(), NOW())", [
                    'id_persona' => $idPersona,
                    'id_jefe' => $idJefe,
                    'id_vacante_jefe' => $idVacanteJefe,
                ]);
            }

            foreach ($telefonos as $tel) {
                $numero = self::texto($tel['numero'] ?? '', 30);
                if (!$numero) continue;
                $db->CRUD("INSERT INTO estado_cuenta.telefonos_persona (id_persona, numero, tipo, estatus) VALUES (:id_persona, :numero, :tipo, :estatus)", [
                    'id_persona' => $idPersona,
                    'numero' => $numero,
                    'tipo' => self::texto($tel['tipo'] ?? 'Personal', 40),
                    'estatus' => self::estatus($tel['estatus'] ?? 'Activo'),
                ]);
            }

            foreach ($correos as $mail) {
                $correo = self::texto($mail['correo'] ?? '', 160);
                if (!$correo) continue;
                $db->CRUD("INSERT INTO estado_cuenta.correos_persona (id_persona, correo, tipo, estatus) VALUES (:id_persona, :correo, :tipo, :estatus)", [
                    'id_persona' => $idPersona,
                    'correo' => $correo,
                    'tipo' => self::texto($mail['tipo'] ?? 'Personal', 40),
                    'estatus' => self::estatus($mail['estatus'] ?? 'Activo'),
                ]);
            }

            foreach ($domicilios as $dom) {
                $domicilio = self::texto($dom['domicilio_texto'] ?? '', 500);
                if (!$domicilio) continue;
                $db->CRUD("INSERT INTO estado_cuenta.domicilio_persona (id_persona, domicilio_texto, codigo_postal, tipo, estatus) VALUES (:id_persona, :domicilio_texto, :codigo_postal, :tipo, :estatus)", [
                    'id_persona' => $idPersona,
                    'domicilio_texto' => $domicilio,
                    'codigo_postal' => self::texto($dom['codigo_postal'] ?? '', 12),
                    'tipo' => self::texto($dom['tipo'] ?? 'Particular', 40),
                    'estatus' => self::estatus($dom['estatus'] ?? 'Activo'),
                ]);
            }

            foreach ($cuentas as $cuenta) {
                $db->CRUD("INSERT INTO estado_cuenta.persona_cuenta_bancaria (id_persona, clabe, numero_cuenta, id_banco, nombre_banco, estatus) VALUES (:id_persona, :clabe, :numero_cuenta, :id_banco, :nombre_banco, :estatus)", [
                    'id_persona' => $idPersona,
                    'clabe' => self::texto($cuenta['clabe'] ?? '', 30),
                    'numero_cuenta' => self::texto($cuenta['numero_cuenta'] ?? '', 40),
                    'id_banco' => !empty($cuenta['id_banco']) ? (int) $cuenta['id_banco'] : null,
                    'nombre_banco' => self::texto($cuenta['nombre_banco'] ?? '', 120),
                    'estatus' => self::estatus($cuenta['estatus'] ?? 'Activo'),
                ]);
            }

            $tipoCredito = self::texto($nomina['credito_infonavit_fonacot'] ?? '', 80);
            $numeroCredito = self::texto($nomina['no_credito'] ?? '', 80);
            $montoDescontar = self::decimal($nomina['monto_descontar'] ?? null);
            if ($tipoCredito || $numeroCredito || $montoDescontar !== null) {
                $db->CRUD("INSERT INTO estado_cuenta.persona_credito_laboral (id_persona, tipo_credito, numero_credito, monto_descontar, estatus) VALUES (:id_persona, :tipo_credito, :numero_credito, :monto_descontar, 'Activo')", [
                    'id_persona' => $idPersona,
                    'tipo_credito' => $tipoCredito,
                    'numero_credito' => $numeroCredito,
                    'monto_descontar' => $montoDescontar,
                ]);
            }

            foreach ($contactos as $contacto) {
                $nombre = self::texto($contacto['nombre_contacto'] ?? '', 220);
                if (!$nombre) continue;
                $db->CRUD("INSERT INTO estado_cuenta.contacto_persona_emergencia (id_persona, nombre_contacto, parentesco, numero, estatus) VALUES (:id_persona, :nombre_contacto, :parentesco, :numero, :estatus)", [
                    'id_persona' => $idPersona,
                    'nombre_contacto' => $nombre,
                    'parentesco' => self::texto($contacto['parentesco'] ?? '', 80),
                    'numero' => self::texto($contacto['numero'] ?? '', 30),
                    'estatus' => self::estatus($contacto['estatus'] ?? 'Activo'),
                ]);
            }

            foreach ($beneficiarios as $beneficiario) {
                $nombre = self::texto($beneficiario['nombre_beneficiario'] ?? '', 220);
                if (!$nombre) continue;
                $db->CRUD("INSERT INTO estado_cuenta.persona_beneficiario_fallecimiento (id_persona, nombre_beneficiario, parentesco, numero, porcentaje, estatus) VALUES (:id_persona, :nombre_beneficiario, :parentesco, :numero, :porcentaje, :estatus)", [
                    'id_persona' => $idPersona,
                    'nombre_beneficiario' => $nombre,
                    'parentesco' => self::texto($beneficiario['parentesco'] ?? '', 80),
                    'numero' => self::texto($beneficiario['numero'] ?? '', 30),
                    'porcentaje' => self::decimal($beneficiario['porcentaje'] ?? null),
                    'estatus' => self::estatus($beneficiario['estatus'] ?? 'Activo'),
                ]);
            }

            $observacion = self::texto($data['observaciones'] ?? '', 5000);
            if ($observacion) {
                $db->CRUD("INSERT INTO estado_cuenta.catalogo_observacion_persona (nombre, activo) VALUES ('Observación general', 1) ON DUPLICATE KEY UPDATE activo = 1");
                $cat = $db->queryOne("SELECT id FROM estado_cuenta.catalogo_observacion_persona WHERE nombre = 'Observación general' LIMIT 1");
                $db->CRUD("INSERT INTO estado_cuenta.persona_observacion (id_persona, id_catalogo_observacion, observacion, estatus) VALUES (:id_persona, :id_catalogo_observacion, :observacion, 'Activo')", [
                    'id_persona' => $idPersona,
                    'id_catalogo_observacion' => $cat['id'] ?? null,
                    'observacion' => $observacion,
                ]);
            }

            $db->commit();

            $legacySync = LegacyUserSync::sincronizarDesdeEditarUsuario($idPersona, $idSesion);
            return self::resultado(true, 'Usuario RR.HH. registrado correctamente.', [
                'id_persona' => $idPersona,
                'numero_empleado' => $numeroEmpleado,
                'legacy_sync' => $legacySync,
            ]);
        } catch (\Exception $e) {
            if (isset($db)) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al registrar usuario RR.HH.', null, $e->getMessage());
        }
    }

    public static function obtenerUsuario(int $idPersona, int $idSesion): array
    {
        if (!self::usuarioTieneModuloWeb(self::MODULO_EDITAR_USUARIO_RRHH)) {
            return self::resultado(false, 'No tienes permiso para editar usuarios RR.HH.');
        }
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.');
        }

        try {
            $db = new Database();

            $persona = $db->queryOne("
                SELECT p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom, p.numero_empleado, p.codigo_contpac,
                       p.correo, p.telefono_uno, p.telefono_dos, p.user_name, p.password, p.fecha_ingreso, p.id_pais,
                       p.domicilio_calle_texto, p.codigo_postal, p.curp,
                       r.rfc, r.nss, r.entidad_federativa_rfc, r.anio, r.mes, r.dia,
                       r.fecha_nacimiento, r.sexo, r.estado_civil
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.persona_datos_rrhh r ON r.id_persona = p.id
                WHERE p.id = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if (!$persona) {
                return self::resultado(false, 'No se encontro la persona solicitada.');
            }

            $rrhh = $db->queryOne("
                SELECT registro_patronal, codigo_contpaq, fecha_contpaq, fecha_imss_alta,
                       id_departamento AS departamento_id, id_area AS area_id, id_puesto AS puesto_id,
                       id_jefe AS jefe_id, puesto_texto, departamento_texto, area_texto,
                       direccion_organizacional, ubicacion_laboral, municipio_laboral,
                       jefe_directo_texto, tipo_sangre, alergias, enfermedades_cronicas,
                       enfermedades_hereditarias, medicamentos_actuales, discapacidad_condicion,
                       observaciones_medicas, carta_no_credito, carta_no_nomina_bbva, observaciones
                FROM estado_cuenta.persona_datos_rrhh
                WHERE id_persona = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]) ?: [];

            $asignacionPuesto = $db->queryOne("
                SELECT
                    ap.id_puesto AS puesto_id,
                    pu.nombre AS puesto_texto,
                    pu.departamento_id AS departamento_id,
                    dep.nombre AS departamento_texto,
                    dorg.id AS area_id,
                    dorg.nombre AS area_texto,
                    dir.id AS direccion_id,
                    dir.nombre AS direccion_texto,
                    COALESCE(dep.id_empresa, dorg.id_empresa, dir.id_empresa, 1) AS empresa_id,
                    COALESCE(emp.nombre_comercial, 'MaxiKash') AS empresa_texto
                FROM estado_cuenta.asigna_puesto ap
                INNER JOIN estado_cuenta.puesto pu ON pu.id = ap.id_puesto
                LEFT JOIN estado_cuenta.departamento dep ON dep.id = pu.departamento_id
                LEFT JOIN estado_cuenta.departamento_organizacional dorg ON dorg.id = dep.id_departamento_organizacional
                LEFT JOIN estado_cuenta.asigna_direcciones ad
                       ON ad.id_departamento_organizacional = dep.id_departamento_organizacional
                      AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN estado_cuenta.direcciones_organizacion dir ON dir.id = ad.id_direccion
                LEFT JOIN estado_cuenta.rrhh_empresas emp
                       ON emp.id = COALESCE(dep.id_empresa, dorg.id_empresa, dir.id_empresa, 1)
                WHERE ap.id_persona = :id_persona
                  AND COALESCE(ap.activo, 1) = 1
                ORDER BY COALESCE(pu.nivel, 0) DESC, ap.id DESC
                LIMIT 1
            ", ['id_persona' => $idPersona]) ?: [];

            if ($asignacionPuesto) {
                foreach (['departamento_id', 'area_id', 'puesto_id', 'direccion_id', 'empresa_id'] as $campo) {
                    if (empty($rrhh[$campo]) && !empty($asignacionPuesto[$campo])) {
                        $rrhh[$campo] = $asignacionPuesto[$campo];
                    }
                }
                foreach (['departamento_texto', 'area_texto', 'puesto_texto', 'direccion_texto', 'empresa_texto'] as $campo) {
                    if (empty($rrhh[$campo]) && !empty($asignacionPuesto[$campo])) {
                        $rrhh[$campo] = $asignacionPuesto[$campo];
                    }
                }
            }
            if (empty($rrhh['fecha_ingreso']) && !empty($persona['fecha_ingreso'])) {
                $rrhh['fecha_ingreso'] = $persona['fecha_ingreso'];
            }

            $jefe = $db->queryOne("
                SELECT id_jefe, id_vacante_jefe
                FROM estado_cuenta.asigna_jefe
                WHERE id_persona = :id_persona
                ORDER BY id DESC
                LIMIT 1
            ", ['id_persona' => $idPersona]);
            if ($jefe) {
                if (!empty($jefe['id_vacante_jefe'])) {
                    $rrhh['jefe_id'] = 'vacante:' . (int)$jefe['id_vacante_jefe'];
                } elseif (!empty($jefe['id_jefe'])) {
                    $rrhh['jefe_id'] = (int)$jefe['id_jefe'];
                }
            }

            $credito = $db->queryOne("
                SELECT tipo_credito AS credito_infonavit_fonacot, numero_credito AS no_credito, monto_descontar
                FROM estado_cuenta.persona_credito_laboral
                WHERE id_persona = :id_persona
                ORDER BY id DESC
                LIMIT 1
            ", ['id_persona' => $idPersona]) ?: [];

            $observacion = $db->queryOne("
                SELECT observacion
                FROM estado_cuenta.persona_observacion
                WHERE id_persona = :id_persona
                ORDER BY id DESC
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            $datos = [
                'persona' => [
                    'id_persona' => $idPersona,
                    'nombres' => $persona['nombres'] ?? '',
                    'segundo_nombre' => $persona['segundo_nombre'] ?? '',
                    'apellidop' => $persona['apellidop'] ?? '',
                    'apellidom' => $persona['apellidom'] ?? '',
                    'numero_empleado' => $persona['numero_empleado'] ?? '',
                    'codigo_contpac' => $persona['codigo_contpac'] ?? '',
                    'correo' => $persona['correo'] ?? '',
                    'telefono_uno' => $persona['telefono_uno'] ?? '',
                    'telefono_dos' => $persona['telefono_dos'] ?? '',
                    'usuario' => $persona['user_name'] ?? '',
                    'contrasena' => self::usuarioTieneModuloWeb(self::MODULO_GESTION_VISUALIZAR_CONTRASENA) ? ($persona['password'] ?? '') : '',
                    'fecha_ingreso' => $persona['fecha_ingreso'] ?? '',
                    'id_pais' => $persona['id_pais'] ?? '',
                    'domicilio' => $persona['domicilio_calle_texto'] ?? '',
                    'codigo_postal' => $persona['codigo_postal'] ?? '',
                    'curp' => $persona['curp'] ?? '',
                    'rfc' => $persona['rfc'] ?? '',
                    'nss' => $persona['nss'] ?? '',
                    'entidad_federativa_rfc' => $persona['entidad_federativa_rfc'] ?? '',
                    'fecha_nacimiento' => $persona['fecha_nacimiento'] ?? '',
                    'sexo' => $persona['sexo'] ?? '',
                    'estado_civil' => $persona['estado_civil'] ?? '',
                ],
                'rrhh' => $rrhh,
                'nomina' => $credito,
                'telefonos' => $db->queryAll("SELECT numero, tipo, estatus FROM estado_cuenta.telefonos_persona WHERE id_persona = :id_persona ORDER BY id ASC", ['id_persona' => $idPersona]),
                'correos' => $db->queryAll("SELECT correo, tipo, estatus FROM estado_cuenta.correos_persona WHERE id_persona = :id_persona ORDER BY id ASC", ['id_persona' => $idPersona]),
                'domicilios' => $db->queryAll("SELECT domicilio_texto, codigo_postal, tipo, estatus FROM estado_cuenta.domicilio_persona WHERE id_persona = :id_persona ORDER BY id ASC", ['id_persona' => $idPersona]),
                'cuentas_bancarias' => $db->queryAll("SELECT id_banco, nombre_banco, numero_cuenta, clabe, estatus FROM estado_cuenta.persona_cuenta_bancaria WHERE id_persona = :id_persona ORDER BY id ASC", ['id_persona' => $idPersona]),
                'contactos_emergencia' => $db->queryAll("SELECT nombre_contacto, parentesco, numero, estatus FROM estado_cuenta.contacto_persona_emergencia WHERE id_persona = :id_persona ORDER BY id ASC", ['id_persona' => $idPersona]),
                'beneficiarios' => $db->queryAll("SELECT nombre_beneficiario, parentesco, numero, porcentaje, estatus FROM estado_cuenta.persona_beneficiario_fallecimiento WHERE id_persona = :id_persona ORDER BY id ASC", ['id_persona' => $idPersona]),
                'salario_sensible' => [
                    'tiene_salario' => CapHum::personaTieneSalarioSensible($idPersona),
                ],
                'observaciones' => $observacion['observacion'] ?? ($rrhh['observaciones'] ?? ''),
            ];

            if (empty($datos['telefonos']) && !empty($persona['telefono_uno'])) {
                $datos['telefonos'][] = ['numero' => $persona['telefono_uno'], 'tipo' => 'Personal', 'estatus' => 'Activo'];
            }
            if (empty($datos['correos']) && !empty($persona['correo'])) {
                $datos['correos'][] = ['correo' => $persona['correo'], 'tipo' => 'Personal', 'estatus' => 'Activo'];
            }
            if (empty($datos['domicilios']) && !empty($persona['domicilio_calle_texto'])) {
                $datos['domicilios'][] = [
                    'domicilio_texto' => $persona['domicilio_calle_texto'],
                    'codigo_postal' => $persona['codigo_postal'] ?? '',
                    'tipo' => 'Actual',
                    'estatus' => 'Activo',
                ];
            }

            return self::resultado(true, 'Datos RR.HH. encontrados.', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cargar datos RR.HH.', null, $e->getMessage());
        }
    }

    public static function obtenerDatosActualizacionInfo(int $idPersona, int $idSesion): array
    {
        if (!self::usuarioTieneModuloWeb(self::MODULO_ACTUALIZAR_DATOS_RRHH)) {
            return self::resultado(false, 'No tienes permiso para actualizar informacion.');
        }
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.');
        }

        try {
            $db = new Database();

            $persona = $db->queryOne("
                SELECT p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom, p.numero_empleado,
                       p.correo, p.telefono_uno, p.telefono_dos, p.domicilio_calle_texto,
                       p.codigo_postal, p.curp, p.user_name,
                       r.tipo_sangre, r.alergias, r.enfermedades_cronicas, r.enfermedades_hereditarias,
                       r.medicamentos_actuales, r.discapacidad_condicion, r.observaciones_medicas,
                       (
                           SELECT t.numero
                           FROM estado_cuenta.telefonos_persona t
                           WHERE t.id_persona = p.id AND t.estatus = 'Activo'
                           ORDER BY t.id ASC
                           LIMIT 1
                       ) AS telefono_lista,
                       (
                           SELECT d.domicilio_texto
                           FROM estado_cuenta.domicilio_persona d
                           WHERE d.id_persona = p.id AND d.estatus = 'Activo'
                           ORDER BY CASE d.tipo
                               WHEN 'Actual' THEN 1
                               WHEN 'Particular' THEN 2
                               WHEN 'Rentado' THEN 3
                               WHEN 'Familiar' THEN 4
                               WHEN 'Fiscal' THEN 5
                               WHEN 'Laboral' THEN 6
                               ELSE 7
                           END, d.id ASC
                           LIMIT 1
                       ) AS domicilio_lista,
                       (
                           SELECT d.codigo_postal
                           FROM estado_cuenta.domicilio_persona d
                           WHERE d.id_persona = p.id AND d.estatus = 'Activo'
                           ORDER BY CASE d.tipo
                               WHEN 'Actual' THEN 1
                               WHEN 'Particular' THEN 2
                               WHEN 'Rentado' THEN 3
                               WHEN 'Familiar' THEN 4
                               WHEN 'Fiscal' THEN 5
                               WHEN 'Laboral' THEN 6
                               ELSE 7
                           END, d.id ASC
                           LIMIT 1
                       ) AS codigo_postal_lista,
                       (
                           SELECT CONCAT_WS(' / ', c.nombre_contacto, c.parentesco, c.numero)
                           FROM estado_cuenta.contacto_persona_emergencia c
                           WHERE c.id_persona = p.id AND c.estatus = 'Activo'
                           ORDER BY c.id ASC
                           LIMIT 1
                       ) AS contacto_emergencia_texto
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.persona_datos_rrhh r ON r.id_persona = p.id
                WHERE p.id = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if (!$persona) {
                return self::resultado(false, 'No se encontro la persona solicitada.');
            }

            $nombreCompleto = trim(implode(' ', array_filter([
                $persona['nombres'] ?? '',
                $persona['segundo_nombre'] ?? '',
                $persona['apellidop'] ?? '',
                $persona['apellidom'] ?? '',
            ])));

            $actuales = [
                'telefono_principal' => $persona['telefono_uno'] ?: ($persona['telefono_lista'] ?? ''),
                'telefono_secundario' => $persona['telefono_dos'] ?? '',
                'correo' => $persona['correo'] ?? '',
                'codigo_postal' => $persona['codigo_postal'] ?: ($persona['codigo_postal_lista'] ?? ''),
                'domicilio' => $persona['domicilio_calle_texto'] ?: ($persona['domicilio_lista'] ?? ''),
                'calle_avenida' => $persona['domicilio_calle_texto'] ?: ($persona['domicilio_lista'] ?? ''),
                'numero_exterior' => '',
                'numero_interior' => '',
                'colonia' => '',
                'municipio' => '',
                'estado' => '',
                'contacto_emergencia' => trim((string)($persona['contacto_emergencia_texto'] ?? ''), " /"),
                'tipo_sangre' => $persona['tipo_sangre'] ?? '',
                'alergias' => $persona['alergias'] ?? '',
                'enfermedades_cronicas' => $persona['enfermedades_cronicas'] ?? '',
                'enfermedades_hereditarias' => $persona['enfermedades_hereditarias'] ?? '',
                'medicamentos_actuales' => $persona['medicamentos_actuales'] ?? '',
                'observaciones_medicas' => $persona['observaciones_medicas'] ?? '',
            ];

            return self::resultado(true, 'Datos actuales encontrados.', [
                'persona' => [
                    'id_persona' => $idPersona,
                    'nombre' => $nombreCompleto,
                    'numero_empleado' => $persona['numero_empleado'] ?? '',
                    'usuario' => $persona['user_name'] ?? '',
                ],
                'actuales' => $actuales,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cargar datos actuales.', null, $e->getMessage());
        }
    }

    public static function obtenerDatosActualizacionInfoLote(array $idsPersona, int $idSesion): array
    {
        if (!self::usuarioTieneModuloWeb(self::MODULO_ACTUALIZAR_DATOS_RRHH)) {
            return self::resultado(false, 'No tienes permiso para actualizar informacion.');
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $idsPersona), function ($id) {
            return $id > 0;
        })));
        $ids = array_slice($ids, 0, 20);

        if (!$ids) {
            return self::resultado(true, 'Sin personas para cargar.', ['personas' => []]);
        }

        $params = [];
        $placeholders = [];
        foreach ($ids as $index => $id) {
            $key = 'id' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        try {
            $db = new Database();
            $rows = $db->queryAll("
                SELECT p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom, p.numero_empleado,
                       p.correo, p.telefono_uno, p.telefono_dos, p.domicilio_calle_texto,
                       p.codigo_postal, p.curp, p.user_name,
                       r.tipo_sangre, r.alergias, r.enfermedades_cronicas, r.enfermedades_hereditarias,
                       r.medicamentos_actuales, r.discapacidad_condicion, r.observaciones_medicas,
                       (
                           SELECT t.numero
                           FROM estado_cuenta.telefonos_persona t
                           WHERE t.id_persona = p.id AND t.estatus = 'Activo'
                           ORDER BY t.id ASC
                           LIMIT 1
                       ) AS telefono_lista,
                       (
                           SELECT d.domicilio_texto
                           FROM estado_cuenta.domicilio_persona d
                           WHERE d.id_persona = p.id AND d.estatus = 'Activo'
                           ORDER BY CASE d.tipo
                               WHEN 'Actual' THEN 1
                               WHEN 'Particular' THEN 2
                               WHEN 'Rentado' THEN 3
                               WHEN 'Familiar' THEN 4
                               WHEN 'Fiscal' THEN 5
                               WHEN 'Laboral' THEN 6
                               ELSE 7
                           END, d.id ASC
                           LIMIT 1
                       ) AS domicilio_lista,
                       (
                           SELECT d.codigo_postal
                           FROM estado_cuenta.domicilio_persona d
                           WHERE d.id_persona = p.id AND d.estatus = 'Activo'
                           ORDER BY CASE d.tipo
                               WHEN 'Actual' THEN 1
                               WHEN 'Particular' THEN 2
                               WHEN 'Rentado' THEN 3
                               WHEN 'Familiar' THEN 4
                               WHEN 'Fiscal' THEN 5
                               WHEN 'Laboral' THEN 6
                               ELSE 7
                           END, d.id ASC
                           LIMIT 1
                       ) AS codigo_postal_lista,
                       (
                           SELECT CONCAT_WS(' / ', c.nombre_contacto, c.parentesco, c.numero)
                           FROM estado_cuenta.contacto_persona_emergencia c
                           WHERE c.id_persona = p.id AND c.estatus = 'Activo'
                           ORDER BY c.id ASC
                           LIMIT 1
                       ) AS contacto_emergencia_texto
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.persona_datos_rrhh r ON r.id_persona = p.id
                WHERE p.id IN (" . implode(',', $placeholders) . ")
            ", $params);

            $personas = [];
            foreach ($rows as $persona) {
                $nombreCompleto = trim(implode(' ', array_filter([
                    $persona['nombres'] ?? '',
                    $persona['segundo_nombre'] ?? '',
                    $persona['apellidop'] ?? '',
                    $persona['apellidom'] ?? '',
                ])));

                $personas[(string)($persona['id'] ?? '')] = [
                    'persona' => [
                        'id_persona' => (int)($persona['id'] ?? 0),
                        'nombre' => $nombreCompleto,
                        'numero_empleado' => $persona['numero_empleado'] ?? '',
                        'usuario' => $persona['user_name'] ?? '',
                    ],
                    'actuales' => [
                        'telefono_principal' => $persona['telefono_uno'] ?: ($persona['telefono_lista'] ?? ''),
                        'telefono_secundario' => $persona['telefono_dos'] ?? '',
                        'correo' => $persona['correo'] ?? '',
                        'codigo_postal' => $persona['codigo_postal'] ?: ($persona['codigo_postal_lista'] ?? ''),
                        'domicilio' => $persona['domicilio_calle_texto'] ?: ($persona['domicilio_lista'] ?? ''),
                        'calle_avenida' => $persona['domicilio_calle_texto'] ?: ($persona['domicilio_lista'] ?? ''),
                        'numero_exterior' => '',
                        'numero_interior' => '',
                        'colonia' => '',
                        'municipio' => '',
                        'estado' => '',
                        'contacto_emergencia' => trim((string)($persona['contacto_emergencia_texto'] ?? ''), " /"),
                        'tipo_sangre' => $persona['tipo_sangre'] ?? '',
                        'alergias' => $persona['alergias'] ?? '',
                        'enfermedades_cronicas' => $persona['enfermedades_cronicas'] ?? '',
                        'enfermedades_hereditarias' => $persona['enfermedades_hereditarias'] ?? '',
                        'medicamentos_actuales' => $persona['medicamentos_actuales'] ?? '',
                        'observaciones_medicas' => $persona['observaciones_medicas'] ?? '',
                    ],
                ];
            }

            return self::resultado(true, 'Datos actuales encontrados.', ['personas' => $personas]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cargar datos actuales.', null, $e->getMessage());
        }
    }

    public static function guardarSolicitudActualizacionInfo(array $data, int $idSesion): array
    {
        if (!self::usuarioTieneModuloWeb(self::MODULO_ACTUALIZAR_DATOS_RRHH)) {
            return self::resultado(false, 'No tienes permiso para actualizar informacion.');
        }

        $idPersona = (int)($data['id_persona'] ?? 0);
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.');
        }

        $campos = is_array($data['campos'] ?? null) ? $data['campos'] : [];
        $campos = array_values(array_filter(array_map(function ($campo) {
            if (!is_array($campo)) return null;
            $nombreCampo = self::texto($campo['campo'] ?? '', 80);
            $etiqueta = self::texto($campo['etiqueta'] ?? '', 160);
            $valorNuevo = self::texto($campo['valor_nuevo'] ?? '', 5000);
            if (!$nombreCampo || !$etiqueta) {
                return null;
            }
            return [
                'campo' => $nombreCampo,
                'etiqueta' => $etiqueta,
                'tipo_campo' => self::texto($campo['tipo'] ?? $campo['tipo_campo'] ?? 'text', 40) ?: 'text',
                'grupo' => self::texto($campo['grupo'] ?? 'General', 80) ?: 'General',
                'servicio_catalogo' => self::texto($campo['servicio_catalogo'] ?? '', 120),
                'valor_anterior' => self::texto($campo['valor_anterior'] ?? '', 5000),
                'valor_nuevo' => $valorNuevo,
            ];
        }, $campos)));

        if (!$campos) {
            return self::resultado(false, 'Selecciona al menos un campo para enviar a la app.');
        }

        try {
            $db = new Database();
            self::asegurarTablas($db);

            $existePersona = $db->queryOne("SELECT
                    id,
                    TRIM(COALESCE(numero_empleado, '')) AS numero_empleado,
                    CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) AS nombre_persona
                FROM estado_cuenta.persona
                WHERE id = :id_persona
                LIMIT 1", [
                'id_persona' => $idPersona,
            ]);
            if (!$existePersona) {
                return self::resultado(false, 'No se encontro la persona solicitada.');
            }

            $db->beginTransaction();
            $db->CRUD("INSERT INTO estado_cuenta.persona_actualizacion_info
                (id_persona, id_solicita, origen, estatus, observaciones, enviado_app)
                VALUES (:id_persona, :id_solicita, 'gestion_personal', 'Pendiente', :observaciones, 0)", [
                'id_persona' => $idPersona,
                'id_solicita' => $idSesion,
                'observaciones' => self::texto($data['observaciones'] ?? '', 5000),
            ]);
            $idSolicitud = $db->lastInsertId();

            foreach ($campos as $campo) {
                $db->CRUD("INSERT INTO estado_cuenta.persona_actualizacion_info_detalle
                    (id_solicitud, campo, etiqueta, tipo_campo, grupo, servicio_catalogo, valor_anterior, valor_nuevo)
                    VALUES (:id_solicitud, :campo, :etiqueta, :tipo_campo, :grupo, :servicio_catalogo, :valor_anterior, :valor_nuevo)", [
                    'id_solicitud' => $idSolicitud,
                    'campo' => $campo['campo'],
                    'etiqueta' => $campo['etiqueta'],
                    'tipo_campo' => $campo['tipo_campo'],
                    'grupo' => $campo['grupo'],
                    'servicio_catalogo' => $campo['servicio_catalogo'],
                    'valor_anterior' => $campo['valor_anterior'],
                    'valor_nuevo' => $campo['valor_nuevo'],
                ]);
            }

            $db->commit();
            $push = self::notificarSolicitudActualizacionInfo($existePersona, (int) $idSolicitud, count($campos));
            return self::resultado(true, 'Solicitud de actualizacion guardada para enviar a la app.', [
                'id_solicitud' => $idSolicitud,
                'campos' => count($campos),
                'push_notificacion' => [
                    'success' => (bool) ($push['success'] ?? false),
                    'omitida' => (bool) ($push['omitir'] ?? false),
                    'message' => (string) ($push['message'] ?? ''),
                ],
            ]);
        } catch (\Exception $e) {
            if (isset($db)) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al guardar la solicitud de actualizacion.', null, $e->getMessage());
        }
    }

    public static function listarRespuestasActualizacionInfo(int $idSesion): array
    {
        if (!self::usuarioTieneModuloWeb(self::MODULO_REVISION_ACTUALIZACIONES_RRHH)) {
            return self::resultado(false, 'No tienes permiso para revisar actualizaciones.');
        }

        try {
            $db = new Database();
            self::asegurarTablas($db);

            $rows = $db->queryAll("SELECT
                    s.id AS id_solicitud,
                    s.id_persona,
                    s.estatus AS estatus_solicitud,
                    s.observaciones,
                    s.created_at AS solicitud_creada,
                    p.numero_empleado,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_persona,
                    d.id AS id_detalle,
                    d.campo,
                    d.etiqueta,
                    d.valor_anterior,
                    r.valor_nuevo,
                    r.comentario,
                    r.estatus AS estatus_respuesta,
                    r.recibido_app_at
                FROM estado_cuenta.persona_actualizacion_info_respuesta r
                INNER JOIN estado_cuenta.persona_actualizacion_info s ON s.id = r.id_solicitud
                INNER JOIN estado_cuenta.persona_actualizacion_info_detalle d ON d.id = r.id_detalle
                LEFT JOIN estado_cuenta.persona p ON p.id = s.id_persona
                WHERE r.estatus = 'EnRevision'
                ORDER BY r.recibido_app_at DESC, s.id DESC, d.id ASC");

            $solicitudes = [];
            foreach ($rows as $row) {
                $idSolicitud = (int)($row['id_solicitud'] ?? 0);
                if (!isset($solicitudes[$idSolicitud])) {
                    $solicitudes[$idSolicitud] = [
                        'id_solicitud' => $idSolicitud,
                        'id_persona' => (int)($row['id_persona'] ?? 0),
                        'numero_empleado' => $row['numero_empleado'] ?? '',
                        'nombre_persona' => trim((string)($row['nombre_persona'] ?? 'Trabajador')),
                        'estatus_solicitud' => $row['estatus_solicitud'] ?? '',
                        'observaciones' => $row['observaciones'] ?? '',
                        'solicitud_creada' => $row['solicitud_creada'] ?? '',
                        'recibido_app_at' => $row['recibido_app_at'] ?? '',
                        'comentario' => $row['comentario'] ?? '',
                        'campos' => [],
                    ];
                }

                $solicitudes[$idSolicitud]['campos'][] = [
                    'id_detalle' => (int)($row['id_detalle'] ?? 0),
                    'campo' => $row['campo'] ?? '',
                    'etiqueta' => $row['etiqueta'] ?? '',
                    'valor_anterior' => $row['valor_anterior'] ?? '',
                    'valor_nuevo' => $row['valor_nuevo'] ?? '',
                    'estatus_respuesta' => $row['estatus_respuesta'] ?? '',
                ];
            }

            return self::resultado(true, 'Actualizaciones recibidas.', array_values($solicitudes));
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar actualizaciones recibidas.', [], $e->getMessage());
        }
    }

    public static function actualizarUsuario(array $data, int $idSesion): array
    {
        if (!self::usuarioTieneModuloWeb(self::MODULO_EDITAR_USUARIO_RRHH)) {
            return self::resultado(false, 'No tienes permiso para editar usuarios RR.HH.');
        }

        $idPersona = (int)($data['id_persona'] ?? 0);
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.');
        }

        $datos = self::datosBase($data);
        $persona = $datos['persona'];
        $rrhh = $datos['rrhh'];
        $nomina = $datos['nomina'];
        [$ok, $mensaje] = self::validarPersonaBasica($persona);
        if (!$ok) {
            return self::resultado(false, $mensaje);
        }

        try {
            $db = new Database();
            self::asegurarTablas($db);
            self::completarPuestoRrhhDesdeTexto($db, $rrhh);

            [$jefeValido, $mensajeJefe] = self::validarJefeDirectoEstructura($db, $idPersona, $rrhh);
            if (!$jefeValido) {
                return self::resultado(false, $mensajeJefe);
            }

            $existePersona = $db->queryOne("SELECT id FROM estado_cuenta.persona WHERE id = :id_persona LIMIT 1", ['id_persona' => $idPersona]);
            if (!$existePersona) {
                return self::resultado(false, 'No se encontro la persona solicitada.');
            }

            $usuario = self::texto($persona['usuario'] ?? '', 40);
            if ($usuario && $db->queryOne('SELECT id FROM estado_cuenta.persona WHERE user_name = :usuario AND id <> :id_persona LIMIT 1', ['usuario' => $usuario, 'id_persona' => $idPersona])) {
                return self::resultado(false, 'Ya existe otra persona con ese usuario.');
            }

            $telefonoPrincipal = self::texto($datos['telefonos'][0]['numero'] ?? $persona['telefono_uno'] ?? '', 30);
            $correoPrincipal = self::texto($datos['correos'][0]['correo'] ?? $persona['correo'] ?? '', 160);
            $domicilioPrincipal = self::texto($datos['domicilios'][0]['domicilio_texto'] ?? $persona['domicilio'] ?? '', 500);
            $cpPrincipal = self::texto($datos['domicilios'][0]['codigo_postal'] ?? $persona['codigo_postal'] ?? '', 12);

            $params = [
                'id_persona' => $idPersona,
                'nombres' => self::texto($persona['nombres'] ?? '', 120),
                'segundo_nombre' => self::texto($persona['segundo_nombre'] ?? '', 120),
                'apellidop' => self::texto($persona['apellidop'] ?? '', 120),
                'apellidom' => self::texto($persona['apellidom'] ?? '', 120),
                'correo' => $correoPrincipal,
                'telefono_uno' => $telefonoPrincipal,
                'telefono_dos' => self::texto($persona['telefono_dos'] ?? '', 30),
                'codigo_contpac' => self::texto($persona['codigo_contpac'] ?? '', 40),
                'user_name' => $usuario,
                'fecha_ingreso' => self::fecha($rrhh['fecha_ingreso'] ?? $persona['fecha_ingreso'] ?? ''),
                'id_pais' => (int)$persona['id_pais'],
                'domicilio_calle_texto' => $domicilioPrincipal,
                'codigo_postal' => $cpPrincipal,
                'curp' => self::texto($persona['curp'] ?? '', 18),
            ];
            $setPassword = self::texto($persona['contrasena'] ?? '', 120);
            $passwordSql = '';
            if ($setPassword) {
                $passwordSql = ', password = :password';
                $params['password'] = $setPassword;
            }

            $db->beginTransaction();
            $db->CRUD("UPDATE estado_cuenta.persona
                SET nombres = :nombres, segundo_nombre = :segundo_nombre, apellidop = :apellidop,
                    apellidom = :apellidom, correo = :correo, telefono_uno = :telefono_uno,
                    telefono_dos = :telefono_dos, codigo_contpac = :codigo_contpac, user_name = :user_name, fecha_ingreso = :fecha_ingreso,
                    id_pais = :id_pais, domicilio_calle_texto = :domicilio_calle_texto,
                    codigo_postal = :codigo_postal, curp = :curp {$passwordSql}
                WHERE id = :id_persona", $params);

            $GLOBALS['rrhh_observaciones_actual'] = self::texto($data['observaciones'] ?? '', 5000);
            self::guardarDatosRrhh($db, $idPersona, $persona, $rrhh, $nomina);
            self::reemplazarListas($db, $idPersona, $datos);
            self::sincronizarAsignaciones($db, $idPersona, $rrhh, $idSesion);
            unset($GLOBALS['rrhh_observaciones_actual']);

            $db->commit();

            $legacySync = LegacyUserSync::sincronizarDesdeEditarUsuario($idPersona, $idSesion);
            return self::resultado(true, 'Datos RR.HH. actualizados correctamente.', [
                'id_persona' => $idPersona,
                'legacy_sync' => $legacySync,
            ]);
        } catch (\Exception $e) {
            if (isset($db)) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            unset($GLOBALS['rrhh_observaciones_actual']);
            return self::resultado(false, 'Error al actualizar datos RR.HH.', null, $e->getMessage());
        }
    }
}
