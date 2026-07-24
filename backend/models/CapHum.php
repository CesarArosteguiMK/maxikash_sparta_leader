<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\UsuarioFantasmaReporteria;

class CapHum extends Model
{
    private static $trayectoriaPuestoTablaAsegurada = false;
    private static $personaEsExternoColumnaAsegurada = false;
    private static $plantillaActivaExpedientesTablaAsegurada = false;
    public const MODULO_ACCESOS_CAPITAL_HUMANO = 140;
    private const MODULO_MIS_DOCUMENTOS = 141;
    private const MODULO_VACACIONES = 147;
    private const MODULO_VALIDADOR_DOCUMENTAL_CANDIDATOS = 104;
    private const MODULO_VALIDADOR_DOCUMENTAL_RRHH_CANDIDATOS = 142;
    private const MODULO_GESTION_REGISTRAR_PERSONA = 143;
    public const MODULO_VALIDAR_CARTA_COMPROMISO_GESTOR = 144;
    public const MODULO_VER_DOCUMENTOS_SENSIBLES_RRHH = 151;
    public const MODULO_RESET_TOTP_DOCUMENTOS_SENSIBLES_RRHH = 152;
    public const MODULO_VER_SALARIO_SENSIBLE_RRHH = 153;
    public const MODULO_AUDITORIA_RRHH = 154;
    public const MODULO_DESCARGAR_PLANTILLA_CRUCE_VACACIONES = 196;
    public const MODULO_DESCARGAR_MUESTRA_EXPEDIENTES_RRHH = 197;
    public const MODULO_ASISTENTE_SPARTA = 194;
    public const MODULO_DESBLOQUEAR_COMPONENTES_MOTOS_ADJUDICADAS = 195;
    public const PERSONA_LAZARO_RAUDEL = 878;
    private const MODULOS_DOCUMENTO_RRHH = [
        8 => 155,
        9 => 156,
        10 => 157,
        11 => 158,
        12 => 159,
        13 => 160,
        14 => 161,
        15 => 162,
        16 => 163,
        17 => 164,
        18 => 165,
        22 => 166,
        23 => 167,
        24 => 168,
        25 => 169,
        27 => 170,
        28 => 171,
        29 => 172,
        30 => 173,
        31 => 174,
        32 => 175,
        33 => 176,
        34 => 177,
        35 => 178,
        36 => 179,
        37 => 184,
        38 => 185,
    ];
    private const MODULOS_ACCESOS_CAPITAL_HUMANO_IDS = [
        4, 5, 13, 34, 38, 42, 44, 82, 83, 86, 87, 88, 91, 93,
        94, 95, 96, 97, 98, 99, 101, 104, 105,
        140, 141, 142, 143, 144, 147, 151, 152, 153, 154,
        155, 156, 157, 158, 159, 160, 161, 162, 163, 164,
        165, 166, 167, 168, 169, 170, 171, 172, 173, 174,
        175, 176, 177, 178, 179, 184, 185, 196, 197,
    ];
    private const MODULO_CONVENIOS_DESCARGAR_EXCEL = 92;
    private const MODULO_CONVENIOS_DESCARGAR_EXCEL_NOMBRE = 'Descargar Excel';
    private const MODULO_CONVENIOS_DESCARGAR_EXCEL_DESC = 'Convenios - Cierre de Credito - Descargar Excel';
    private const MODULO_TRACKING_CANCELAR_RUTA = 102;
    private const MODULO_TRACKING_CANCELAR_RUTA_NOMBRE = 'Cancelar rutas Tracking';
    private const MODULO_TRACKING_CANCELAR_RUTA_DESC = 'Tracking Recoleccion - Cancelar rutas registradas';

    private static function asegurarPersonaEsExterno(Database $db): void
    {
        if (self::$personaEsExternoColumnaAsegurada) {
            return;
        }

        $columnaActual = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'persona'
              AND COLUMN_NAME = 'es_externo'
            LIMIT 1
        ");
        if (!$columnaActual) {
            $db->CRUD("ALTER TABLE persona ADD COLUMN es_externo TINYINT(1) NOT NULL DEFAULT 0 AFTER codigo_contpac");
        }
        self::$personaEsExternoColumnaAsegurada = true;
        return;
    }

    /**
     * El expediente RR.HH. se consulta contra la plantilla vigente importada
     * desde AEM y Pensionamax; no contra todos los historicos marcados Activo.
     */
    private static function asegurarPlantillaActivaExpedientes(Database $db): void
    {
        if (self::$plantillaActivaExpedientesTablaAsegurada) {
            return;
        }

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_plantilla_activa (
                curp VARCHAR(18) NOT NULL,
                id_persona INT NOT NULL,
                id_empresa INT NOT NULL,
                origen VARCHAR(64) NOT NULL,
                nombre_origen VARCHAR(255) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                fecha_sincronizacion DATETIME NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (curp),
                KEY idx_plantilla_activa_persona (id_persona, activo),
                KEY idx_plantilla_activa_empresa (id_empresa, activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$plantillaActivaExpedientesTablaAsegurada = true;
    }

    public static function asegurarModuloAccesosCapitalHumano(): void
    {
        try {
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarModuloAccesosCapitalHumano -> ' . $e->getMessage());
        }
    }

    private static function asegurarModuloAccesosCapitalHumanoDb(Database $db): void
    {
        $modulos = [
            [
                'id' => self::MODULO_ACCESOS_CAPITAL_HUMANO,
                'nombre' => 'Accesos',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Acceso al modulo de administracion de permisos de Capital Humano.',
            ],
            [
                'id' => self::MODULO_MIS_DOCUMENTOS,
                'nombre' => 'Mis documentos',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Capital Humano > Mis documentos',
            ],
            [
                'id' => self::MODULO_VACACIONES,
                'nombre' => 'Vacaciones',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Capital Humano > Vacaciones',
            ],
            [
                'id' => self::MODULO_DESCARGAR_PLANTILLA_CRUCE_VACACIONES,
                'nombre' => 'Plantilla para cruce',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Permite descargar la plantilla con CURP, nombre y codigo Contpaqi para el cruce de vacaciones.',
            ],
            [
                'id' => self::MODULO_DESCARGAR_MUESTRA_EXPEDIENTES_RRHH,
                'nombre' => 'Descargar muestra de expedientes',
                'pestana' => 'Control documental RR.HH.',
                'descripcion' => 'Permite descargar hasta 10 expedientes de plantilla activa en un ZIP, protegido con Google Authenticator.',
            ],
            [
                'id' => self::MODULO_VALIDADOR_DOCUMENTAL_RRHH_CANDIDATOS,
                'nombre' => 'Validador documental RRHH',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite validar documentos de candidatos que no pertenecen a la direccion Cobranza',
            ],
            [
                'id' => self::MODULO_GESTION_REGISTRAR_PERSONA,
                'nombre' => 'Registrar persona en Gestion de Personal',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite ver y usar la opcion Persona al agregar usuarios en Gestion de Personal.',
            ],
            [
                'id' => self::MODULO_VALIDAR_CARTA_COMPROMISO_GESTOR,
                'nombre' => 'Validar Documento de Compromiso del Gestor',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Capital Humano > Validar Documento de Compromiso del Gestor',
            ],
            [
                'id' => self::MODULO_VER_DOCUMENTOS_SENSIBLES_RRHH,
                'nombre' => 'Ver documentos sensibles RR.HH.',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite abrir y descargar contratos, bancos y archivos sensibles de expedientes RR.HH.',
            ],
            [
                'id' => self::MODULO_RESET_TOTP_DOCUMENTOS_SENSIBLES_RRHH,
                'nombre' => 'Reset Google Authenticator documentos RR.HH.',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite reiniciar el segundo paso de Google Authenticator para documentos sensibles RR.HH.',
            ],
            [
                'id' => self::MODULO_VER_SALARIO_SENSIBLE_RRHH,
                'nombre' => 'Ver salario sensible RR.HH.',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite ver y actualizar salario RR.HH. protegido con Google Authenticator y cifrado.',
            ],
            [
                'id' => self::MODULO_AUDITORIA_RRHH,
                'nombre' => 'Auditoria',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Capital Humano > Auditoria',
            ],
            [
                'id' => self::MODULO_DESBLOQUEAR_COMPONENTES_MOTOS_ADJUDICADAS,
                'nombre' => 'Desbloquear componentes',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite desbloquear los componentes en Motos Adjudicadas.',
            ],
        ];

        $documentosRrhh = [
            8 => 'CURP',
            9 => 'Identificacion Oficial (INE)',
            11 => 'Comprobante de Domicilio',
            12 => 'Acta de Nacimiento',
            13 => 'Certificado de Estudios',
            14 => 'Referencias Laborales',
            15 => 'Documento baja',
            16 => 'Documento reingreso',
            17 => 'Solicitud interna',
            18 => 'CV o Solicitud de Trabajo',
            22 => 'Constancia de Situacion Fiscal',
            23 => 'Numero de Seguridad Social',
            24 => 'Hoja de Retencion FONACOT o INFONAVIT',
            25 => 'Estado de Cuenta',
            27 => 'Carta de compromiso del Gestor',
            28 => 'Contrato firmado',
            29 => 'Archivo .FAD',
            30 => 'Validacion SAT',
            31 => 'Llave vector',
            32 => 'Prueba centavo',
            33 => 'Semanas cotizadas IMSS (segundos patrones)',
            34 => 'Documento incapacidad',
            35 => 'Documento permiso',
            36 => 'Documento falta',
            37 => 'Finiquito',
            38 => 'Comprobante de pago finiquito',
        ];

        foreach ($documentosRrhh as $idDocumento => $nombreDocumento) {
            $idModulo = self::MODULOS_DOCUMENTO_RRHH[(int) $idDocumento] ?? 0;
            if ($idModulo <= 0) {
                continue;
            }
            $modulos[] = [
                'id' => $idModulo,
                'nombre' => 'Documento RRHH: ' . $nombreDocumento,
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite ver, subir, descargar y eliminar documentos RR.HH. de tipo ' . $nombreDocumento . '.',
            ];
        }

        foreach (self::MODULOS_DOCUMENTO_RRHH as $idDocumento => $idModuloNuevo) {
            self::renumerarModuloWebId($db, 3000 + (int) $idDocumento, (int) $idModuloNuevo);
        }
        foreach ([3037 => 180, 3038 => 181, 3039 => 182, 3040 => 183] as $idViejo => $idModuloNuevo) {
            self::renumerarModuloWebId($db, (int) $idViejo, (int) $idModuloNuevo);
        }

        foreach ($modulos as $datos) {
            self::asegurarModuloWeb($db, $datos);
        }
    }

    /**
     * Inserta/actualiza el modulo y resuelve duplicados por nombre sin romper el id esperado.
     */
    private static function asegurarModuloWeb(Database $db, array $datos): void
    {
        $id = (int)($datos['id'] ?? 0);
        $nombre = trim((string)($datos['nombre'] ?? ''));
        if ($id <= 0 || $nombre === '') {
            return;
        }

        $existentePorId = $db->queryOne(
            'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if ($existentePorId) {
            $db->CRUD(
                'UPDATE modulos_web
                    SET nombre = :nombre,
                        pestana = :pestana,
                        descripcion = :descripcion,
                        activo = 1
                  WHERE id = :id',
                $datos
            );
            return;
        }

        $existentePorNombre = $db->queryOne(
            'SELECT id FROM modulos_web WHERE nombre = :nombre LIMIT 1',
            ['nombre' => $nombre]
        );
        $idExistente = (int)($existentePorNombre['id'] ?? 0);
        if ($idExistente > 0 && $idExistente !== $id) {
            self::renumerarModuloWebId($db, $idExistente, $id);
            $existentePorId = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => $id]
            );
            if ($existentePorId) {
                $db->CRUD(
                    'UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = :pestana,
                            descripcion = :descripcion,
                            activo = 1
                      WHERE id = :id',
                    $datos
                );
                return;
            }
        }

        $db->CRUD(
            'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
             VALUES (:id, :nombre, :pestana, :descripcion, 1)',
            $datos
        );
    }

    private static function renumerarModuloWebId(Database $db, int $idViejo, int $idNuevo): void
    {
        if ($idViejo <= 0 || $idNuevo <= 0 || $idViejo === $idNuevo) {
            return;
        }

        try {
            $moduloViejo = $db->queryOne(
                'SELECT nombre, pestana, descripcion, activo FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => $idViejo]
            );
            if (!$moduloViejo) {
                return;
            }

            $datosNuevo = [
                'id' => $idNuevo,
                'nombre' => (string)($moduloViejo['nombre'] ?? ''),
                'pestana' => (string)($moduloViejo['pestana'] ?? ''),
                'descripcion' => (string)($moduloViejo['descripcion'] ?? ''),
                'activo' => (int)($moduloViejo['activo'] ?? 1),
            ];

            $moduloNuevo = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => $idNuevo]
            );
            if ($moduloNuevo) {
                $db->CRUD(
                    'UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = :pestana,
                            descripcion = :descripcion,
                            activo = :activo
                      WHERE id = :id',
                    $datosNuevo
                );
            } else {
                $nombreTemporal = '__renumerando_' . $idViejo . '_' . $idNuevo . '_' . bin2hex(random_bytes(4));
                $db->CRUD(
                    'UPDATE modulos_web SET nombre = :nombre_temporal WHERE id = :id_viejo',
                    ['nombre_temporal' => $nombreTemporal, 'id_viejo' => $idViejo]
                );
                $db->CRUD(
                    'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                     VALUES (:id, :nombre, :pestana, :descripcion, :activo)',
                    $datosNuevo
                );
            }

            $db->CRUD(
                'INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                 SELECT DISTINCT viejo.usuario_id, :id_nuevo
                 FROM asigna_modulo_web viejo
                 WHERE viejo.modulo_web_id = :id_viejo
                   AND NOT EXISTS (
                       SELECT 1
                       FROM asigna_modulo_web nuevo
                       WHERE nuevo.usuario_id = viejo.usuario_id
                         AND nuevo.modulo_web_id = :id_nuevo_exists
                   )',
                ['id_nuevo' => $idNuevo, 'id_viejo' => $idViejo, 'id_nuevo_exists' => $idNuevo]
            );
            $db->CRUD('DELETE FROM asigna_modulo_web WHERE modulo_web_id = :id', ['id' => $idViejo]);
            $db->CRUD('DELETE FROM modulos_web WHERE id = :id', ['id' => $idViejo]);
        } catch (\Throwable $e) {
            error_log('CapHum::renumerarModuloWebId ' . $idViejo . ' -> ' . $idNuevo . ' :: ' . $e->getMessage());
        }
    }

    private static function asegurarModuloConveniosDescargarExcel(Database $db): void
    {
        try {
            $datos = [
                'id' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL,
                'nombre' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_NOMBRE,
                'pestana' => 'Permisos especiales',
                'descripcion' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_DESC,
            ];
            $existe = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL]
            );

            if ($existe) {
                $db->CRUD(
                    'UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = :pestana,
                            descripcion = :descripcion,
                            activo = 1
                      WHERE id = :id',
                    $datos
                );
                return;
            }

            $db->CRUD(
                'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                 VALUES (:id, :nombre, :pestana, :descripcion, 1)',
                $datos
            );
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarModuloConveniosDescargarExcel -> ' . $e->getMessage());
        }
    }

    private static function asegurarModuloTrackingCancelarRuta(Database $db): void
    {
        try {
            $datos = [
                'nombre' => self::MODULO_TRACKING_CANCELAR_RUTA_NOMBRE,
                'pestana' => 'Permisos especiales',
                'descripcion' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC,
            ];
            $existe = $db->queryOne(
                'SELECT id
                   FROM modulos_web
                  WHERE pestana = :pestana
                    AND (descripcion = :descripcion OR nombre = :nombre)
                  LIMIT 1',
                $datos
            );

            if ($existe) {
                $db->CRUD(
                    'UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = :pestana,
                            descripcion = :descripcion,
                            activo = 1
                      WHERE id = :id',
                    $datos + ['id' => (int) $existe['id']]
                );
                return;
            }

            $idOcupado = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => self::MODULO_TRACKING_CANCELAR_RUTA]
            );
            if (!$idOcupado) {
                $db->CRUD(
                    'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                     VALUES (:id, :nombre, :pestana, :descripcion, 1)',
                    $datos + ['id' => self::MODULO_TRACKING_CANCELAR_RUTA]
                );
                return;
            }

            $db->CRUD(
                'INSERT INTO modulos_web (nombre, pestana, descripcion, activo)
                 VALUES (:nombre, :pestana, :descripcion, 1)',
                $datos
            );
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarModuloTrackingCancelarRuta -> ' . $e->getMessage());
        }
    }

    private static function agregarModuloConveniosDescargarExcelSiFalta(array $perfiles, int $idPersona, Database $db): array
    {
        foreach ($perfiles as $perfil) {
            if ((int) ($perfil['modulo_id'] ?? 0) === self::MODULO_CONVENIOS_DESCARGAR_EXCEL) {
                return $perfiles;
            }
        }

        $asignado = null;
        try {
            $asignado = $db->queryOne(
                'SELECT id
                   FROM asigna_modulo_web
                  WHERE usuario_id = :uid
                    AND modulo_web_id = :mid
                  LIMIT 1',
                [
                    'uid' => $idPersona,
                    'mid' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL,
                ]
            );
        } catch (\Throwable $e) {
            error_log('CapHum::agregarModuloConveniosDescargarExcelSiFalta -> ' . $e->getMessage());
        }

        $perfiles[] = [
            'usuario_id' => $idPersona,
            'modulo_id' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL,
            'modulo_nombre' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_NOMBRE,
            'pestana' => 'Permisos especiales',
            'descripcion' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_DESC,
            'activo' => 1,
            'estado' => $asignado ? 'Asignado' : 'No asignado',
            'asignado_flag' => $asignado ? 1 : 0,
        ];

        return $perfiles;
    }
    public const DOCUMENTO_CARTA_COMPROMISO_GESTOR = 27;
    private const DOCUMENTOS_RRHH_EXTRA_CATALOGO = [
        ['id' => 28, 'clave' => 'CONTRATO_FIRMADO', 'nombre' => 'Contrato firmado'],
        ['id' => 29, 'clave' => 'ARCHIVO_FAD', 'nombre' => 'Archivo .FAD'],
        ['id' => 30, 'clave' => 'VALIDACION_SAT', 'nombre' => 'Validacion SAT'],
        ['id' => 31, 'clave' => 'LLAVE_VECTOR', 'nombre' => 'Llave vector'],
        ['id' => 32, 'clave' => 'PRUEBA_CENTAVO', 'nombre' => 'Prueba centavo'],
        ['id' => 33, 'clave' => 'SEMANAS_COTIZADAS_IMSS_SEGUNDOS_PATRONES', 'nombre' => 'Semanas cotizadas IMSS (segundos patrones)'],
        ['id' => 34, 'clave' => 'DOCUMENTO_INCAPACIDAD', 'nombre' => 'Documento incapacidad'],
        ['id' => 35, 'clave' => 'DOCUMENTO_PERMISO', 'nombre' => 'Documento permiso'],
        ['id' => 36, 'clave' => 'DOCUMENTO_FALTA', 'nombre' => 'Documento falta'],
        ['id' => 37, 'clave' => 'FINIQUITO', 'nombre' => 'Finiquito'],
        ['id' => 38, 'clave' => 'COMPROBANTE_PAGO_FINIQUITO', 'nombre' => 'Comprobante de pago finiquito'],
    ];
    // RFC heredado se consolida bajo Constancia de situacion fiscal.
    // Conservamos sus cargas mediante el alias para no perder expedientes previos.
    private const DOCUMENTOS_EXCLUIDOS_RRHH = [10, 19, 20, 21];
    private const DOCUMENTOS_ALIAS_RRHH = [
        10 => 22, // RFC heredado -> Constancia de situacion fiscal
        19 => 12, // Acta de nacimiento certificada -> Acta de Nacimiento
        20 => 9,  // Identificacion oficial duplicada -> Identificacion Oficial (INE)
    ];

    /**
     * Expediente documental base para colaboradores activos.
     * Los demas tipos permanecen disponibles en el sistema, pero no se
     * contabilizan como requisito ni como faltante en Expedientes RR.HH.
     */
    private const DOCUMENTOS_EXPEDIENTE_RRHH = [
        17, // Solicitud interna
        18, // CV o Solicitud de Trabajo
        12, // Acta de Nacimiento
        8,  // CURP
        11, // Comprobante de Domicilio
        22, // Constancia de Situacion Fiscal
        9,  // Identificacion Oficial
        23, // Numero de Seguridad Social
        24, // Hoja de Retencion FONACOT o INFONAVIT / Carta de No Credito
        25, // Estado de Cuenta
        30, // Validacion SAT
        28, // Contrato firmado
        29, // Archivo .FAD
        31, // Llave vector
        32, // Prueba centavo
        33, // Semanas cotizadas IMSS
    ];

    private static function idsDocumentosExpedienteRrhh(): string
    {
        return implode(',', self::DOCUMENTOS_EXPEDIENTE_RRHH);
    }

    public static function asegurarDocumentoCartaCompromisoGestor(): void
    {
        try {
            $db = new Database();
            $documentos = array_merge([[
                'id' => self::DOCUMENTO_CARTA_COMPROMISO_GESTOR,
                'clave' => 'CARTA_COMPROMISO_GESTOR',
                'nombre' => 'Carta de compromiso del Gestor',
            ]], self::DOCUMENTOS_RRHH_EXTRA_CATALOGO);

            foreach ($documentos as $doc) {
                $datos = [
                    'id' => (int) $doc['id'],
                    'clave' => (string) $doc['clave'],
                    'nombre' => (string) $doc['nombre'],
                    'obligatorio' => 0,
                    'activo' => 1,
                ];
                $existe = $db->queryOne(
                    'SELECT id FROM estado_cuenta.documento WHERE id = :id OR clave = :clave LIMIT 1',
                    ['id' => $datos['id'], 'clave' => $datos['clave']]
                );
                if ($existe) {
                    $db->CRUD(
                        'UPDATE estado_cuenta.documento
                            SET clave = :clave,
                                nombre = :nombre,
                                obligatorio = :obligatorio,
                                activo = :activo
                          WHERE id = :id_existente',
                        [
                            'id_existente' => (int) ($existe['id'] ?? $datos['id']),
                            'clave' => $datos['clave'],
                            'nombre' => $datos['nombre'],
                            'obligatorio' => $datos['obligatorio'],
                            'activo' => $datos['activo'],
                        ]
                    );
                    continue;
                }
                $db->CRUD(
                    'INSERT INTO estado_cuenta.documento (id, clave, nombre, obligatorio, activo)
                     VALUES (:id, :clave, :nombre, :obligatorio, :activo)',
                    $datos
                );
            }
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarDocumentoCartaCompromisoGestor -> ' . $e->getMessage());
        }
    }

    public static function fechaHoraCdmx(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }

    public static function asegurarTablaTrayectoriaPuesto(Database $db): void
    {
        if (self::$trayectoriaPuestoTablaAsegurada) {
            return;
        }

        $db->CRUD("CREATE TABLE IF NOT EXISTS estado_cuenta.persona_puesto_trayectoria (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            accion VARCHAR(60) NOT NULL,
            id_puesto_anterior INT NULL,
            fecha_asignacion_anterior DATETIME NULL,
            id_puesto_nuevo INT NULL,
            fecha_asignacion_nueva DATETIME NULL,
            nombre_puesto_anterior VARCHAR(180) NULL,
            nombre_puesto_nuevo VARCHAR(180) NULL,
            id_departamento_anterior INT NULL,
            id_departamento_nuevo INT NULL,
            nombre_departamento_anterior VARCHAR(180) NULL,
            nombre_departamento_nuevo VARCHAR(180) NULL,
            nivel_anterior INT NULL,
            nivel_nuevo INT NULL,
            motivo VARCHAR(500) NULL,
            origen VARCHAR(80) NULL,
            creado_por INT NULL,
            creado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_puesto_trayectoria_persona (id_persona, creado_at),
            KEY idx_puesto_trayectoria_puesto_nuevo (id_puesto_nuevo),
            KEY idx_puesto_trayectoria_creado_por (creado_por)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        self::asegurarColumnaTrayectoriaPuesto($db, 'fecha_asignacion_anterior', "DATETIME NULL AFTER id_puesto_anterior");
        self::asegurarColumnaTrayectoriaPuesto($db, 'fecha_asignacion_nueva', "DATETIME NULL AFTER id_puesto_nuevo");
        self::$trayectoriaPuestoTablaAsegurada = true;
    }

    private static function asegurarColumnaTrayectoriaPuesto(Database $db, string $columna, string $definicion): void
    {
        $existe = $db->queryOne(
            "SHOW COLUMNS FROM estado_cuenta.persona_puesto_trayectoria LIKE :columna",
            ['columna' => $columna]
        );
        if (!$existe) {
            $db->CRUD("ALTER TABLE estado_cuenta.persona_puesto_trayectoria ADD COLUMN {$columna} {$definicion}");
        }
    }

    public static function puestosActivosTrayectoria(Database $db, int $idPersona): array
    {
        if ($idPersona <= 0) {
            return [];
        }

        return $db->queryAll("
            SELECT
                ap.id AS id_asigna_puesto,
                ap.id_puesto,
                ap.fecha_asignacion,
                pu.nombre AS nombre_puesto,
                pu.departamento_id AS id_departamento,
                dep.nombre AS nombre_departamento,
                COALESCE(pu.nivel, 0) AS nivel
            FROM estado_cuenta.asigna_puesto ap
            INNER JOIN estado_cuenta.puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN estado_cuenta.departamento dep ON dep.id = pu.departamento_id
            WHERE ap.id_persona = :id_persona
              AND COALESCE(ap.activo, 1) = 1
            ORDER BY COALESCE(pu.nivel, 0) DESC, ap.id ASC
        ", ['id_persona' => $idPersona]);
    }

    private static function indexarPuestosTrayectoria(array $puestos): array
    {
        $out = [];
        foreach ($puestos as $puesto) {
            $id = (int)($puesto['id_puesto'] ?? 0);
            if ($id > 0) {
                $out[$id] = $puesto;
            }
        }
        return $out;
    }

    public static function registrarTrayectoriaPuesto(
        Database $db,
        int $idPersona,
        string $accion,
        ?array $puestoAnterior = null,
        ?array $puestoNuevo = null,
        ?int $creadoPor = null,
        string $motivo = '',
        string $origen = 'gestion_personal'
    ): void {
        if ($idPersona <= 0 || trim($accion) === '') {
            return;
        }

        self::asegurarTablaTrayectoriaPuesto($db);
        $fechaCdmx = self::fechaHoraCdmx();
        $db->CRUD("
            INSERT INTO estado_cuenta.persona_puesto_trayectoria
                (id_persona, accion, id_puesto_anterior, fecha_asignacion_anterior, id_puesto_nuevo, fecha_asignacion_nueva,
                 nombre_puesto_anterior, nombre_puesto_nuevo,
                 id_departamento_anterior, id_departamento_nuevo,
                 nombre_departamento_anterior, nombre_departamento_nuevo,
                 nivel_anterior, nivel_nuevo, motivo, origen, creado_por, creado_at)
            VALUES
                (:id_persona, :accion, :id_puesto_anterior, :fecha_asignacion_anterior, :id_puesto_nuevo, :fecha_asignacion_nueva,
                 :nombre_puesto_anterior, :nombre_puesto_nuevo,
                 :id_departamento_anterior, :id_departamento_nuevo,
                 :nombre_departamento_anterior, :nombre_departamento_nuevo,
                 :nivel_anterior, :nivel_nuevo, :motivo, :origen, :creado_por, :creado_at)
        ", [
            'id_persona' => $idPersona,
            'accion' => mb_substr(trim($accion), 0, 60),
            'id_puesto_anterior' => $puestoAnterior['id_puesto'] ?? null,
            'fecha_asignacion_anterior' => $puestoAnterior['fecha_asignacion'] ?? null,
            'id_puesto_nuevo' => $puestoNuevo['id_puesto'] ?? null,
            'fecha_asignacion_nueva' => $puestoNuevo['fecha_asignacion'] ?? null,
            'nombre_puesto_anterior' => $puestoAnterior['nombre_puesto'] ?? null,
            'nombre_puesto_nuevo' => $puestoNuevo['nombre_puesto'] ?? null,
            'id_departamento_anterior' => $puestoAnterior['id_departamento'] ?? null,
            'id_departamento_nuevo' => $puestoNuevo['id_departamento'] ?? null,
            'nombre_departamento_anterior' => $puestoAnterior['nombre_departamento'] ?? null,
            'nombre_departamento_nuevo' => $puestoNuevo['nombre_departamento'] ?? null,
            'nivel_anterior' => isset($puestoAnterior['nivel']) ? (int)$puestoAnterior['nivel'] : null,
            'nivel_nuevo' => isset($puestoNuevo['nivel']) ? (int)$puestoNuevo['nivel'] : null,
            'motivo' => mb_substr(trim($motivo), 0, 500),
            'origen' => mb_substr(trim($origen), 0, 80),
            'creado_por' => $creadoPor && $creadoPor > 0 ? $creadoPor : null,
            'creado_at' => $fechaCdmx,
        ]);
    }

    public static function registrarCambiosTrayectoriaPuestos(
        Database $db,
        int $idPersona,
        array $puestosAntes,
        array $puestosDespues,
        ?int $creadoPor = null,
        string $origen = 'gestion_personal'
    ): void {
        if ($idPersona <= 0) {
            return;
        }

        $antes = self::indexarPuestosTrayectoria($puestosAntes);
        $despues = self::indexarPuestosTrayectoria($puestosDespues);
        $idsAgregados = array_diff(array_keys($despues), array_keys($antes));
        $idsRemovidos = array_diff(array_keys($antes), array_keys($despues));

        $principalAntes = array_values($puestosAntes)[0] ?? null;
        $principalDespues = array_values($puestosDespues)[0] ?? null;
        if ($principalAntes && $principalDespues && (int)$principalAntes['id_puesto'] !== (int)$principalDespues['id_puesto']) {
            $nivelAntes = (int)($principalAntes['nivel'] ?? 0);
            $nivelDespues = (int)($principalDespues['nivel'] ?? 0);
            $accion = $nivelDespues > $nivelAntes ? 'ascenso_puesto' : 'cambio_puesto_principal';
            self::registrarTrayectoriaPuesto(
                $db,
                $idPersona,
                $accion,
                $principalAntes,
                $principalDespues,
                $creadoPor,
                $accion === 'ascenso_puesto' ? 'Aumento de puesto principal.' : 'Cambio de puesto principal.',
                $origen
            );

            $idsAgregados = array_values(array_filter($idsAgregados, function ($idPuesto) use ($principalDespues) {
                return (int)$idPuesto !== (int)($principalDespues['id_puesto'] ?? 0);
            }));
            $idsRemovidos = array_values(array_filter($idsRemovidos, function ($idPuesto) use ($principalAntes) {
                return (int)$idPuesto !== (int)($principalAntes['id_puesto'] ?? 0);
            }));
        }

        foreach ($idsAgregados as $idPuesto) {
            $puestoNuevo = $despues[$idPuesto] ?? null;
            if (!$puestoNuevo) {
                continue;
            }
            self::registrarTrayectoriaPuesto(
                $db,
                $idPersona,
                empty($antes) ? 'alta_puesto' : 'agrego_puesto',
                null,
                $puestoNuevo,
                $creadoPor,
                empty($antes) ? 'Asignacion inicial del colaborador.' : 'Puesto agregado al colaborador.',
                $origen
            );
        }

        foreach ($idsRemovidos as $idPuesto) {
            $puestoAnterior = $antes[$idPuesto] ?? null;
            if (!$puestoAnterior) {
                continue;
            }
            self::registrarTrayectoriaPuesto(
                $db,
                $idPersona,
                'removio_puesto',
                $puestoAnterior,
                null,
                $creadoPor,
                'Puesto retirado del colaborador.',
                $origen
            );
        }
    }

    public static function sembrarTrayectoriaPuestosActuales(Database $db, ?int $creadoPor = null): int
    {
        self::asegurarTablaTrayectoriaPuesto($db);
        $fechaCdmx = self::fechaHoraCdmx();
        return $db->CRUD("
            INSERT INTO estado_cuenta.persona_puesto_trayectoria
                (id_persona, accion, id_puesto_anterior, fecha_asignacion_anterior, id_puesto_nuevo, fecha_asignacion_nueva,
                 nombre_puesto_anterior, nombre_puesto_nuevo,
                 id_departamento_anterior, id_departamento_nuevo,
                 nombre_departamento_anterior, nombre_departamento_nuevo,
                 nivel_anterior, nivel_nuevo, motivo, origen, creado_por, creado_at)
            SELECT
                ap.id_persona,
                CASE
                    WHEN NOT EXISTS (
                        SELECT 1
                        FROM estado_cuenta.asigna_puesto ap2
                        INNER JOIN estado_cuenta.puesto pu2 ON pu2.id = ap2.id_puesto
                        WHERE ap2.id_persona = ap.id_persona
                          AND COALESCE(ap2.activo, 1) = 1
                          AND (
                              COALESCE(pu2.nivel, 0) > COALESCE(pu.nivel, 0)
                              OR (COALESCE(pu2.nivel, 0) = COALESCE(pu.nivel, 0) AND ap2.id < ap.id)
                          )
                    )
                    THEN 'alta_puesto'
                    ELSE 'agrego_puesto'
                END AS accion,
                NULL AS id_puesto_anterior,
                NULL AS fecha_asignacion_anterior,
                ap.id_puesto AS id_puesto_nuevo,
                ap.fecha_asignacion AS fecha_asignacion_nueva,
                NULL AS nombre_puesto_anterior,
                pu.nombre AS nombre_puesto_nuevo,
                NULL AS id_departamento_anterior,
                pu.departamento_id AS id_departamento_nuevo,
                NULL AS nombre_departamento_anterior,
                dep.nombre AS nombre_departamento_nuevo,
                NULL AS nivel_anterior,
                COALESCE(pu.nivel, 0) AS nivel_nuevo,
                CASE
                    WHEN NOT EXISTS (
                        SELECT 1
                        FROM estado_cuenta.asigna_puesto ap3
                        INNER JOIN estado_cuenta.puesto pu3 ON pu3.id = ap3.id_puesto
                        WHERE ap3.id_persona = ap.id_persona
                          AND COALESCE(ap3.activo, 1) = 1
                          AND (
                              COALESCE(pu3.nivel, 0) > COALESCE(pu.nivel, 0)
                              OR (COALESCE(pu3.nivel, 0) = COALESCE(pu.nivel, 0) AND ap3.id < ap.id)
                          )
                    )
                    THEN 'Linea base de trayectoria creada desde el puesto activo actual.'
                    ELSE 'Linea base de puesto adicional activo actual.'
                END AS motivo,
                'semilla_estado_actual' AS origen,
                :creado_por AS creado_por,
                COALESCE(ap.fecha_asignacion, :fecha_cdmx) AS creado_at
            FROM estado_cuenta.asigna_puesto ap
            INNER JOIN estado_cuenta.persona p ON p.id = ap.id_persona
            INNER JOIN estado_cuenta.puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN estado_cuenta.departamento dep ON dep.id = pu.departamento_id
            WHERE COALESCE(ap.activo, 1) = 1
              AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
              AND NOT EXISTS (
                  SELECT 1
                  FROM estado_cuenta.persona_puesto_trayectoria t
                  WHERE t.id_persona = ap.id_persona
              )
        ", [
            'creado_por' => $creadoPor && $creadoPor > 0 ? $creadoPor : null,
            'fecha_cdmx' => $fechaCdmx,
        ]);
    }

    public static function actualizarFechasTrayectoriaDesdeAsignaPuesto(Database $db): int
    {
        self::asegurarTablaTrayectoriaPuesto($db);
        return $db->CRUD("
            UPDATE estado_cuenta.persona_puesto_trayectoria t
            SET
                t.fecha_asignacion_nueva = COALESCE(
                    t.fecha_asignacion_nueva,
                    (
                        SELECT ap.fecha_asignacion
                        FROM estado_cuenta.asigna_puesto ap
                        WHERE ap.id_persona = t.id_persona
                          AND ap.id_puesto = t.id_puesto_nuevo
                        ORDER BY COALESCE(ap.activo, 0) DESC, ap.id DESC
                        LIMIT 1
                    )
                ),
                t.fecha_asignacion_anterior = COALESCE(
                    t.fecha_asignacion_anterior,
                    (
                        SELECT ap2.fecha_asignacion
                        FROM estado_cuenta.asigna_puesto ap2
                        WHERE ap2.id_persona = t.id_persona
                          AND ap2.id_puesto = t.id_puesto_anterior
                        ORDER BY COALESCE(ap2.activo, 0) DESC, ap2.id DESC
                        LIMIT 1
                    )
                ),
                t.creado_at = CASE
                    WHEN t.origen IN ('semilla_estado_actual', 'estado_actual')
                         AND COALESCE(t.fecha_asignacion_nueva, '') <> ''
                    THEN t.fecha_asignacion_nueva
                    ELSE t.creado_at
                END
            WHERE t.fecha_asignacion_nueva IS NULL
               OR t.fecha_asignacion_anterior IS NULL
               OR (t.origen IN ('semilla_estado_actual', 'estado_actual')
                   AND t.fecha_asignacion_nueva IS NOT NULL
                   AND t.creado_at <> t.fecha_asignacion_nueva)
        ");
    }

    public static function getTrayectoriaPuestoPersona(int $idPersona): array
    {
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.', []);
        }

        try {
            $db = new Database();
            self::asegurarTablaTrayectoriaPuesto($db);
            $rows = $db->queryAll("
                SELECT
                    t.*,
                    CASE
                        WHEN t.origen IN ('semilla_estado_actual', 'estado_actual')
                            THEN COALESCE(t.fecha_asignacion_nueva, t.creado_at)
                        ELSE t.creado_at
                    END AS fecha_movimiento,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS responsable_nombre
                FROM estado_cuenta.persona_puesto_trayectoria t
                LEFT JOIN estado_cuenta.persona p ON p.id = t.creado_por
                WHERE t.id_persona = :id_persona
                ORDER BY t.creado_at DESC, t.id DESC
            ", ['id_persona' => $idPersona]);

            if (empty($rows)) {
                foreach (self::puestosActivosTrayectoria($db, $idPersona) as $puestoActual) {
                    $rows[] = [
                        'id' => 0,
                        'id_persona' => $idPersona,
                        'accion' => 'puesto_actual',
                        'id_puesto_anterior' => null,
                        'fecha_asignacion_anterior' => null,
                        'id_puesto_nuevo' => $puestoActual['id_puesto'] ?? null,
                        'fecha_asignacion_nueva' => $puestoActual['fecha_asignacion'] ?? null,
                        'nombre_puesto_anterior' => null,
                        'nombre_puesto_nuevo' => $puestoActual['nombre_puesto'] ?? '',
                        'id_departamento_anterior' => null,
                        'id_departamento_nuevo' => $puestoActual['id_departamento'] ?? null,
                        'nombre_departamento_anterior' => null,
                        'nombre_departamento_nuevo' => $puestoActual['nombre_departamento'] ?? '',
                        'nivel_anterior' => null,
                        'nivel_nuevo' => $puestoActual['nivel'] ?? null,
                        'motivo' => 'Puesto activo actual. No hay movimientos históricos registrados todavía.',
                        'origen' => 'estado_actual',
                        'creado_por' => null,
                        'creado_at' => null,
                        'fecha_movimiento' => $puestoActual['fecha_asignacion'] ?? null,
                        'responsable_nombre' => 'Sistema',
                    ];
                }
            }

            return self::resultado(true, 'Trayectoria consultada correctamente.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar trayectoria de puesto.', [], $e->getMessage());
        }
    }

    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ///
    ///

        /**
     * Consulta bajas con filtros avanzados (departamento, puesto, estatus, multipuesto)
     * @param array $filtros
     * @return array
     */

    public static function getConsultaGestoresAll($id_gestor_sesion, $tieneDepartamento = true, $incluirTransitoBaja = false)
    {
        $id_gestor_sesion = (int)$id_gestor_sesion;
        $filtroEstatusPersona = $incluirTransitoBaja
            ? "LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja')"
            : "LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')";
        $sqlExP = UsuarioFantasmaReporteria::sqlExcluirPersona('p');
        $sqlExP2 = UsuarioFantasmaReporteria::sqlExcluirPersona('p2');

        // =========================
        // Gestion de Personal se filtra por Acceso a Puestos.
        // Sin puestos asignados en privilegios_departamento, no hay usuarios visibles.
        // =========================
        $filtroPuestosSesion = "
        AND EXISTS (
            SELECT 1
            FROM privilegios_departamento pd_perm
            WHERE pd_perm.idPersona = $id_gestor_sesion
              AND pd_perm.idPuesto = ap.id_puesto
        )";

        if (true) {

            $query = <<<SQL
            SELECT
            p.id,
            p.numero_empleado,
            p.codigo_contpac,
            p.es_externo,
            EXISTS(
                SELECT 1
                FROM estado_cuenta.reingresos r_reingreso
                WHERE r_reingreso.id_persona = p.id
                LIMIT 1
            ) AS tiene_reingreso,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            pf.foto AS foto_perfil,

            pp.id AS id_puesto,
            CASE
                WHEN pp.nombre IS NULL THEN 'Sin puesto'
                ELSE pp.nombre
            END AS nombre_puesto,
            pp.nivel AS nivel_puesto,

            d.id AS id_departamento,
            CASE
                WHEN d.nombre IS NULL THEN 'Sin departamento'
                ELSE d.nombre
            END AS nombre_departamento,

            aj.id_jefe,
            aj.id_vacante_jefe,

            CASE
                WHEN pj.id IS NULL THEN 'Sin jefe'
                ELSE CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)
            END AS nombre_jefe,

            CASE
                WHEN vj.id IS NULL THEN NULL
                ELSE CONCAT('Vacante #', vj.id, ' - ', COALESCE(pvj.nombre, 'Sin puesto'))
            END AS nombre_vacante_jefe,

            p.estatus,
            CASE
                WHEN p.user_name IS NULL THEN 'Sin usuario'
                ELSE p.user_name
            END AS usuario,

            COALESCE(pais.id, 0) AS id_pais,
            COALESCE(pais.nombre, 'Sin país') AS nombre_pais,
            COALESCE(pais.codigo_iso, 'xx') AS codigo_iso_pais,

            p.fecha_ingreso,
            p.fecha_registro

        FROM persona p

        LEFT JOIN perfil pf
               ON pf.id_persona = p.id

        LEFT JOIN asigna_puesto ap
               ON p.id = ap.id_persona
              AND COALESCE(ap.activo, 1) = 1

        LEFT JOIN puesto pp
               ON pp.id = ap.id_puesto

        LEFT JOIN departamento d
               ON d.id = pp.departamento_id

        LEFT JOIN paises pais
               ON pais.id = p.id_pais

        LEFT JOIN (
            SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
            FROM asigna_jefe a
            INNER JOIN (
                SELECT id_persona, MAX(id) AS mid
                FROM asigna_jefe
                GROUP BY id_persona
            ) m ON a.id_persona = m.id_persona AND a.id = m.mid
        ) aj ON aj.id_persona = p.id

        LEFT JOIN persona pj
               ON pj.id = aj.id_jefe

        LEFT JOIN vacantes_personal vj
               ON vj.id = aj.id_vacante_jefe

        LEFT JOIN puesto pvj
               ON pvj.id = vj.id_puesto

        -- Solo Gestion de Personal conserva visible a quien esta en tramite.
        WHERE {$filtroEstatusPersona}
        {$sqlExP}
        {$filtroPuestosSesion}

        ORDER BY pp.nivel ASC;

        SQL;

        }
        // =========================
        // USUARIOS NORMALES (JERARQUÍA)
        // =========================
        else {

            $query = <<<SQL
        WITH RECURSIVE Jerarquia AS (

            -- =====================
            -- NIVEL RAÍZ
            -- =====================
            SELECT
                p.id,
                p.numero_empleado,
                p.codigo_contpac,
                p.es_externo,
                EXISTS(
                    SELECT 1
                    FROM estado_cuenta.reingresos r_reingreso
                    WHERE r_reingreso.id_persona = p.id
                    LIMIT 1
                ) AS tiene_reingreso,
                p.nombres,
                p.segundo_nombre,
                p.apellidop,
                p.apellidom,
                pf.foto AS foto_perfil,
                pp.id AS id_puesto,
                pp.nombre AS nombre_puesto,
                pp.nivel AS nivel_puesto,
                d.id AS id_departamento,
                d.nombre AS nombre_departamento,
                aj.id_jefe,
                aj.id_vacante_jefe,
                CASE
                    WHEN vj.id IS NULL THEN NULL
                    ELSE CONCAT('Vacante #', vj.id, ' - ', COALESCE(pvj.nombre, 'Sin puesto'))
                END AS nombre_vacante_jefe,
                p.estatus,
                COALESCE(pais.id, 0) AS id_pais,
                COALESCE(pais.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais.codigo_iso, 'xx') AS codigo_iso_pais,
                p.fecha_ingreso,
                p.fecha_registro,
                1 AS nivel
            FROM persona p
            LEFT JOIN perfil pf ON pf.id_persona = p.id
            LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona AND COALESCE(ap.activo, 1) = 1
            LEFT JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN paises pais ON pais.id = p.id_pais
            LEFT JOIN asigna_jefe aj
                   ON p.id = aj.id_persona
                  AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            LEFT JOIN vacantes_personal vj
                   ON vj.id = aj.id_vacante_jefe
            LEFT JOIN puesto pvj
                   ON pvj.id = vj.id_puesto
            WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
              {$sqlExP}AND (
                    aj.id_jefe = $id_gestor_sesion
                    OR aj.id_jefe IS NULL
                    OR NOT EXISTS (
                        SELECT 1
                        FROM persona jefe_activo
                        WHERE jefe_activo.id = aj.id_jefe
                          AND LOWER(TRIM(COALESCE(jefe_activo.estatus, ''))) NOT IN ('baja', 'transito de baja')
                    )
                  )

            UNION ALL

            -- =====================
            -- SUBORDINADOS
            -- =====================
            SELECT
                p2.id,
                p2.numero_empleado,
                p2.codigo_contpac,
                p2.es_externo,
                EXISTS(
                    SELECT 1
                    FROM estado_cuenta.reingresos r_reingreso2
                    WHERE r_reingreso2.id_persona = p2.id
                    LIMIT 1
                ) AS tiene_reingreso,
                p2.nombres,
                p2.segundo_nombre,
                p2.apellidop,
                p2.apellidom,
                pf2.foto AS foto_perfil,
                pp2.id AS id_puesto,
                pp2.nombre AS nombre_puesto,
                pp2.nivel AS nivel_puesto,
                d2.id AS id_departamento,
                d2.nombre AS nombre_departamento,
                aj2.id_jefe,
                aj2.id_vacante_jefe,
                CASE
                    WHEN vj2.id IS NULL THEN NULL
                    ELSE CONCAT('Vacante #', vj2.id, ' - ', COALESCE(pvj2.nombre, 'Sin puesto'))
                END AS nombre_vacante_jefe,
                p2.estatus,
                COALESCE(pais2.id, 0) AS id_pais,
                COALESCE(pais2.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais2.codigo_iso, 'xx') AS codigo_iso_pais,
                p2.fecha_ingreso,
                p2.fecha_registro,
                j.nivel + 1 AS nivel
            FROM persona p2
            LEFT JOIN perfil pf2 ON pf2.id_persona = p2.id
            LEFT JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona AND COALESCE(ap2.activo, 1) = 1
            LEFT JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            LEFT JOIN departamento d2 ON d2.id = pp2.departamento_id
            LEFT JOIN paises pais2 ON pais2.id = p2.id_pais
            LEFT JOIN asigna_jefe aj2
                   ON p2.id = aj2.id_persona
                  AND (aj2.fecha_fin IS NULL OR aj2.fecha_fin >= CURDATE())
            LEFT JOIN vacantes_personal vj2
                   ON vj2.id = aj2.id_vacante_jefe
            LEFT JOIN puesto pvj2
                   ON pvj2.id = vj2.id_puesto
            JOIN Jerarquia j
                 ON aj2.id_jefe = j.id
            WHERE LOWER(TRIM(COALESCE(p2.estatus, ''))) NOT IN ('baja', 'transito de baja')
              {$sqlExP2}
        )

        SELECT *
        FROM Jerarquia
        ORDER BY nivel_puesto ASC, nivel ASC;
        SQL;
        }

        $query = preg_replace('/\b[A-Za-z0-9_]+\.reingresos\b/', 'reingresos', $query);

        try {
            $db = new Database();
            self::asegurarPersonaEsExterno($db);
            self::asegurarAsignaJefeSoportaVacante($db);
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getDatosReasignacionBaja($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.', null);
        }

        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarAsignaJefeSoportaVacante($db);

            $puestosPersona = $db->queryAll("
                SELECT
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    pp.departamento_id,
                    d.nombre AS nombre_departamento
                FROM estado_cuenta.asigna_puesto ap
                INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN estado_cuenta.departamento d ON d.id = pp.departamento_id
                WHERE ap.id_persona = :id
                  AND COALESCE(ap.activo, 1) = 1
                ORDER BY pp.nivel DESC, ap.id DESC
            ", ['id' => $idPersona]);
            $puestoPersona = $puestosPersona[0] ?? null;

            $subordinados = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                    COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto,
                    COALESCE(d.nombre, 'Sin departamento') AS nombre_departamento
                FROM estado_cuenta.asigna_jefe aj
                INNER JOIN estado_cuenta.persona p ON p.id = aj.id_persona
                LEFT JOIN estado_cuenta.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN estado_cuenta.departamento d ON d.id = pp.departamento_id
                WHERE aj.id_jefe = :id
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                ORDER BY nombre_completo ASC
            ", ['id' => $idPersona]);

            $personas = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                    COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto,
                    COALESCE(d.nombre, 'Sin departamento') AS nombre_departamento
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN estado_cuenta.departamento d ON d.id = pp.departamento_id
                WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                  AND p.id <> :id
                ORDER BY nombre_completo ASC
            ", ['id' => $idPersona]);

            $vacantesMismoPuesto = [];
            if (!empty($puestosPersona)) {
                $paramsVacantes = [];
                $idsPuesto = [];
                $nombresPuesto = [];
                foreach ($puestosPersona as $idxPuesto => $puestoActivo) {
                    if (!empty($puestoActivo['id_puesto'])) {
                        $key = 'id_puesto_' . $idxPuesto;
                        $idsPuesto[] = ':' . $key;
                        $paramsVacantes[$key] = (int)$puestoActivo['id_puesto'];
                    }
                    if (!empty($puestoActivo['nombre_puesto'])) {
                        $key = 'nombre_puesto_' . $idxPuesto;
                        $nombresPuesto[] = ':' . $key;
                        $nombrePuesto = trim((string)$puestoActivo['nombre_puesto']);
                        $paramsVacantes[$key] = function_exists('mb_strtoupper') ? mb_strtoupper($nombrePuesto, 'UTF-8') : strtoupper($nombrePuesto);
                    }
                }

                $condicionesVacante = [];
                if (!empty($idsPuesto)) {
                    $condicionesVacante[] = 'v.id_puesto IN (' . implode(',', $idsPuesto) . ')';
                }
                if (!empty($nombresPuesto)) {
                    $condicionesVacante[] = 'UPPER(TRIM(pp.nombre)) IN (' . implode(',', $nombresPuesto) . ')';
                }

                if (!empty($condicionesVacante)) {
                    $vacantesMismoPuesto = $db->queryAll("
                    SELECT
                        v.id,
                        v.id_jefe,
                        v.id_departamento,
                        v.id_puesto,
                        v.origen,
                        v.fecha_creacion,
                        pp.nombre AS nombre_puesto,
                        d.nombre AS nombre_departamento,
                        CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom) AS nombre_jefe
                    FROM estado_cuenta.vacantes_personal v
                    INNER JOIN estado_cuenta.puesto pp ON pp.id = v.id_puesto
                    LEFT JOIN estado_cuenta.departamento d ON d.id = v.id_departamento
                    LEFT JOIN estado_cuenta.persona jefe ON jefe.id = v.id_jefe
                    WHERE v.estatus = 'Activa'
                      AND (" . implode(' OR ', $condicionesVacante) . ")
                    ORDER BY v.fecha_creacion ASC
                    ", $paramsVacantes);
                }
            }

            return self::resultado(true, 'Datos de reasignacion encontrados.', [
                'subordinados' => $subordinados,
                'personas' => $personas,
                'puesto_baja' => $puestoPersona,
                'puestos_baja' => $puestosPersona,
                'vacantes_mismo_puesto' => $vacantesMismoPuesto
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener datos de reasignacion.', null, $e->getMessage());
        }
    }

    private static function asegurarTablaVacantesPersonal(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.vacantes_personal (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_departamento INT NOT NULL,
                id_puesto INT NOT NULL,
                nombre_vacante VARCHAR(180) NULL,
                id_jefe INT NULL,
                id_persona_baja INT NULL,
                id_persona_cubre INT NULL,
                origen VARCHAR(30) NOT NULL DEFAULT 'manual',
                estatus VARCHAR(20) NOT NULL DEFAULT 'Activa',
                creado_por INT NULL,
                fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_cierre DATETIME NULL,
                INDEX idx_vacantes_personal_jefe (id_jefe),
                INDEX idx_vacantes_personal_persona_cubre (id_persona_cubre),
                INDEX idx_vacantes_personal_depto (id_departamento),
                INDEX idx_vacantes_personal_puesto (id_puesto),
                INDEX idx_vacantes_personal_estatus (estatus)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $columnaCubre = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'vacantes_personal'
              AND COLUMN_NAME = 'id_persona_cubre'
            LIMIT 1
        ");
        if (!$columnaCubre) {
            $db->CRUD("ALTER TABLE estado_cuenta.vacantes_personal ADD COLUMN id_persona_cubre INT NULL AFTER id_persona_baja");
            $db->CRUD("ALTER TABLE estado_cuenta.vacantes_personal ADD INDEX idx_vacantes_personal_persona_cubre (id_persona_cubre)");
        }

        $columnaNombreVacante = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'vacantes_personal'
              AND COLUMN_NAME = 'nombre_vacante'
            LIMIT 1
        ");
        if (!$columnaNombreVacante) {
            $db->CRUD("ALTER TABLE estado_cuenta.vacantes_personal ADD COLUMN nombre_vacante VARCHAR(180) NULL AFTER id_puesto");
        }
    }

    private static function asegurarAsignaJefeSoportaVacante(Database $db): void
    {
        $columna = $db->queryOne("
            SELECT IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'asigna_jefe'
              AND COLUMN_NAME = 'id_jefe'
            LIMIT 1
        ");

        if ($columna && strtoupper((string)$columna['IS_NULLABLE']) !== 'YES') {
            $db->CRUD("ALTER TABLE estado_cuenta.asigna_jefe MODIFY id_jefe INT NULL");
        }

        $columnaVacante = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'asigna_jefe'
              AND COLUMN_NAME = 'id_vacante_jefe'
            LIMIT 1
        ");

        if (!$columnaVacante) {
            $db->CRUD("ALTER TABLE estado_cuenta.asigna_jefe ADD COLUMN id_vacante_jefe INT NULL AFTER id_jefe");
            $db->CRUD("ALTER TABLE estado_cuenta.asigna_jefe ADD INDEX idx_vacante_jefe (id_vacante_jefe)");
        }
    }

    private static function divisionesAdministrativasApiConfig(): array
    {
        $cfg = function_exists('config_api_load_from_db') ? config_api_load_from_db() : [];
        $leerValor = static function (array $keys) use ($cfg): string {
            foreach ($keys as $key) {
                $valor = trim((string)($cfg[$key] ?? ''));
                if ($valor !== '') {
                    return $valor;
                }
                $env = getenv($key);
                if ($env !== false && trim((string)$env) !== '') {
                    return trim((string)$env);
                }
            }
            return '';
        };

        $baseUrl = $leerValor(['DIVISIONES_ADMINISTRATIVAS_API_BASE_URL', 'MOTOS_ADJUDICADAS_PUSH_BASE_URL']);
        if ($baseUrl === '') {
            $baseUrl = 'https://motosadjudicadas-601258367060.us-central1.run.app/api/divisiones-administrativas';
        }
        $baseUrl = rtrim($baseUrl, '/');
        if (!preg_match('#/api/divisiones-administrativas$#', $baseUrl)) {
            $baseUrl .= '/api/divisiones-administrativas';
        }

        $apiKey = $leerValor(['DIVISIONES_ADMINISTRATIVAS_API_KEY', 'MOTOS_ADJUDICADAS_API_KEY', 'MOTOS_ADJUDICADAS_TOKEN']);
        if ($apiKey === '') {
            $apiKey = 'ARt4a6Atn0VhiPJ_0bgXeprr9DUuSAQ7b3oKzICSTy0';
        }

        return [
            'base_url' => $baseUrl,
            'api_key' => $apiKey,
        ];
    }

    private static function divisionesAdministrativasApiGet(string $path, array $query = []): array
    {
        $cfg = self::divisionesAdministrativasApiConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || !function_exists('curl_init')) {
            return ['success' => false, 'datos' => []];
        }

        $url = $cfg['base_url'] . '/' . ltrim($path, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'X-API-Key: ' . $cfg['api_key'],
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($raw === false ? '' : (string)$raw, true);
        if ($httpCode < 200 || $httpCode >= 300 || !is_array($json) || empty($json['success'])) {
            return ['success' => false, 'datos' => []];
        }

        $datos = is_array($json['data'] ?? null) ? $json['data'] : [];
        return ['success' => true, 'datos' => $datos];
    }

    private static function normalizarDivisionAdministrativaApi(array $row): array
    {
        return [
            'id' => $row['id'] ?? null,
            'nombre' => $row['nombre'] ?? '',
            'codigo_interno' => $row['codigo_interno'] ?? null,
            'codigo_iso' => $row['codigo_iso'] ?? null,
            'id_padre' => $row['id_padre'] ?? null,
            'tipo_label' => $row['tipo_nombre'] ?? $row['tipo_label'] ?? '',
            'tipo_codigo' => $row['tipo_codigo'] ?? '',
        ];
    }

    public static function crearVacantePersonal($data)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idDepartamento = (int)($data['id_departamento'] ?? 0);
            $idPuesto = (int)($data['id_puesto'] ?? 0);
            $idJefe = !empty($data['id_jefe']) ? (int)$data['id_jefe'] : null;
            $idPersonaBaja = !empty($data['id_persona_baja']) ? (int)$data['id_persona_baja'] : null;
            $origen = trim((string)($data['origen'] ?? 'manual'));
            $creadoPor = !empty($data['creado_por']) ? (int)$data['creado_por'] : null;

            if ($idDepartamento <= 0 || $idPuesto <= 0) {
                return self::resultado(false, 'Departamento y puesto son obligatorios para registrar la vacante.');
            }

            $db->CRUD("
                INSERT INTO estado_cuenta.vacantes_personal
                    (id_departamento, id_puesto, id_jefe, id_persona_baja, origen, estatus, creado_por)
                VALUES
                    (:id_departamento, :id_puesto, :id_jefe, :id_persona_baja, :origen, 'Activa', :creado_por)
            ", [
                'id_departamento' => $idDepartamento,
                'id_puesto' => $idPuesto,
                'id_jefe' => $idJefe,
                'id_persona_baja' => $idPersonaBaja,
                'origen' => $origen !== '' ? $origen : 'manual',
                'creado_por' => $creadoPor,
            ]);

            $id = $db->lastInsertId();
            return self::resultado(true, 'Vacante registrada correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar la vacante.', null, $e->getMessage());
        }
    }

    public static function getVacantesDisponiblesParaAsignar($idDepartamento, $idPuesto)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idDepartamento = (int)$idDepartamento;
            $idPuesto = (int)$idPuesto;
            if ($idDepartamento <= 0 || $idPuesto <= 0) {
                return self::resultado(true, 'Vacantes encontradas.', []);
            }

            $rows = $db->queryAll("
                SELECT
                    v.id,
                    v.id_departamento,
                    v.id_puesto,
                    v.id_jefe,
                    v.nombre_vacante,
                    v.origen,
                    v.fecha_creacion,
                    COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto,
                    pp.nombre AS nombre_puesto_base,
                    d.nombre AS nombre_departamento,
                    CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom) AS nombre_jefe,
                    COUNT(DISTINCT ps.id) AS subordinados
                FROM estado_cuenta.vacantes_personal v
                INNER JOIN estado_cuenta.puesto pp ON pp.id = v.id_puesto
                LEFT JOIN estado_cuenta.departamento d ON d.id = v.id_departamento
                LEFT JOIN estado_cuenta.persona pj ON pj.id = v.id_jefe
                LEFT JOIN estado_cuenta.asigna_jefe ajv ON ajv.id_vacante_jefe = v.id
                LEFT JOIN estado_cuenta.persona ps ON ps.id = ajv.id_persona AND LOWER(TRIM(COALESCE(ps.estatus, ''))) NOT IN ('baja', 'transito de baja')
                WHERE v.id_departamento = :id_departamento
                  AND v.id_puesto = :id_puesto
                  AND UPPER(TRIM(v.estatus)) = 'ACTIVA'
                GROUP BY v.id, v.id_departamento, v.id_puesto, v.id_jefe, v.nombre_vacante, v.origen, v.fecha_creacion, pp.nombre, d.nombre, nombre_jefe
                ORDER BY v.fecha_creacion ASC, v.id ASC
            ", [
                'id_departamento' => $idDepartamento,
                'id_puesto' => $idPuesto,
            ]);

            return self::resultado(true, 'Vacantes encontradas.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar vacantes disponibles.', null, $e->getMessage());
        }
    }

    public static function getVacantesJefeDirecto($idDepartamento, $idPuesto = null)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idDepartamento = (int)$idDepartamento;
            $idPuesto = (int)$idPuesto;
            if ($idDepartamento <= 0) {
                return self::resultado(true, 'Vacantes encontradas.', []);
            }

            $params = ['id_departamento' => $idDepartamento];
            $whereNivel = 'AND pp.es_jefe = 1';
            if ($idPuesto > 0) {
                $whereNivel = "
                    AND pp.nivel > (
                        SELECT nivel
                        FROM estado_cuenta.puesto
                        WHERE id = :id_puesto_ref
                        LIMIT 1
                    )
                ";
                $params['id_puesto_ref'] = $idPuesto;
            }

            $rows = $db->queryAll("
                SELECT
                    v.id,
                    v.id_departamento,
                    v.id_puesto,
                    v.nombre_vacante,
                    COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto,
                    pp.nombre AS nombre_puesto_base,
                    d.nombre AS nombre_departamento,
                    COUNT(DISTINCT ps.id) AS subordinados
                FROM estado_cuenta.vacantes_personal v
                INNER JOIN estado_cuenta.puesto pp ON pp.id = v.id_puesto
                LEFT JOIN estado_cuenta.departamento d ON d.id = v.id_departamento
                LEFT JOIN estado_cuenta.asigna_jefe ajv ON ajv.id_vacante_jefe = v.id
                LEFT JOIN estado_cuenta.persona ps ON ps.id = ajv.id_persona AND LOWER(TRIM(COALESCE(ps.estatus, ''))) NOT IN ('baja', 'transito de baja')
                WHERE v.id_departamento = :id_departamento
                  AND UPPER(TRIM(v.estatus)) = 'ACTIVA'
                  $whereNivel
                GROUP BY v.id, v.id_departamento, v.id_puesto, v.nombre_vacante, pp.nombre, d.nombre
                ORDER BY pp.nivel ASC, v.fecha_creacion ASC, v.id ASC
            ", $params);

            return self::resultado(true, 'Vacantes encontradas.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar vacantes para jefe directo.', null, $e->getMessage());
        }
    }

    public static function actualizarJefeVacantePersonal($idVacante, $idJefe)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idVacante = (int)$idVacante;
            $idJefe = (int)$idJefe;

            if ($idVacante <= 0 || $idJefe <= 0) {
                return self::resultado(false, 'Seleccione la vacante y el jefe destino.');
            }

            $vacante = $db->queryOne("
                SELECT id, id_departamento, id_puesto, id_jefe, estatus
                FROM estado_cuenta.vacantes_personal
                WHERE id = :id
                LIMIT 1
            ", ['id' => $idVacante]);

            if (!$vacante || strtoupper(trim((string)($vacante['estatus'] ?? ''))) !== 'ACTIVA') {
                return self::resultado(false, 'La vacante ya no esta activa.');
            }

            $jefe = $db->queryOne("
                SELECT p.id
                FROM estado_cuenta.persona p
                WHERE p.id = :id_jefe
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                LIMIT 1
            ", ['id_jefe' => $idJefe]);

            if (!$jefe) {
                return self::resultado(false, 'El jefe seleccionado no esta activo.');
            }

            $db->CRUD("
                UPDATE estado_cuenta.vacantes_personal
                SET id_jefe = :id_jefe
                WHERE id = :id_vacante
                LIMIT 1
            ", [
                'id_jefe' => $idJefe,
                'id_vacante' => $idVacante,
            ]);

            return self::resultado(true, 'Jefe de vacante actualizado correctamente.', [
                'id_vacante' => $idVacante,
                'id_jefe' => $idJefe,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el jefe de la vacante.', null, $e->getMessage());
        }
    }

    public static function actualizarNombreVacantePersonal($idVacante, $nombreVacante)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idVacante = (int)$idVacante;
            $nombreVacante = trim(preg_replace('/\s+/', ' ', (string)$nombreVacante));
            $nombreVacante = trim(preg_replace('/\s*\(\s*vacante\s*\)\s*$/iu', '', $nombreVacante));

            if ($idVacante <= 0) {
                return self::resultado(false, 'Seleccione la vacante.');
            }
            if ($nombreVacante === '' || mb_strlen($nombreVacante) < 3) {
                return self::resultado(false, 'Escribe un nombre valido para la vacante.');
            }
            if (mb_strlen($nombreVacante) > 180) {
                return self::resultado(false, 'El nombre de la vacante no debe superar 180 caracteres.');
            }

            $vacante = $db->queryOne("
                SELECT id, estatus
                FROM estado_cuenta.vacantes_personal
                WHERE id = :id
                LIMIT 1
            ", ['id' => $idVacante]);

            if (!$vacante || strtoupper(trim((string)($vacante['estatus'] ?? ''))) !== 'ACTIVA') {
                return self::resultado(false, 'La vacante ya no esta activa.');
            }

            $db->CRUD("
                UPDATE estado_cuenta.vacantes_personal
                SET nombre_vacante = :nombre_vacante
                WHERE id = :id_vacante
                LIMIT 1
            ", [
                'nombre_vacante' => $nombreVacante,
                'id_vacante' => $idVacante,
            ]);

            return self::resultado(true, 'Nombre de vacante actualizado correctamente.', [
                'id_vacante' => $idVacante,
                'nombre_vacante' => $nombreVacante,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el nombre de la vacante.', null, $e->getMessage());
        }
    }

    public static function eliminarVacantePersonal($idVacante, $modoMovimiento, $idJefeDestino = 0)
    {
        $db = null;
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarAsignaJefeSoportaVacante($db);

            $idVacante = (int)$idVacante;
            $modoMovimiento = trim((string)$modoMovimiento);
            $idJefeDestino = (int)$idJefeDestino;

            if ($idVacante <= 0) {
                return self::resultado(false, 'Seleccione la vacante a eliminar.');
            }
            if (!in_array($modoMovimiento, ['jefe_superior', 'jefe_destino'], true)) {
                return self::resultado(false, 'Seleccione como se moveran los subordinados.');
            }

            $vacante = $db->queryOne("
                SELECT v.id, v.id_jefe, v.id_departamento, v.id_puesto, v.estatus,
                       COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto
                FROM estado_cuenta.vacantes_personal v
                INNER JOIN estado_cuenta.puesto pp ON pp.id = v.id_puesto
                WHERE v.id = :id
                LIMIT 1
            ", ['id' => $idVacante]);

            if (!$vacante || strtoupper(trim((string)($vacante['estatus'] ?? ''))) !== 'ACTIVA') {
                return self::resultado(false, 'La vacante ya no esta activa.');
            }

            $jefeDestino = $modoMovimiento === 'jefe_superior' ? (int)($vacante['id_jefe'] ?? 0) : $idJefeDestino;
            if ($jefeDestino <= 0) {
                return self::resultado(false, 'Seleccione un jefe destino para mover los subordinados.');
            }

            $jefe = $db->queryOne("
                SELECT id
                FROM estado_cuenta.persona
                WHERE id = :id_jefe
                  AND LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
                LIMIT 1
            ", ['id_jefe' => $jefeDestino]);

            if (!$jefe) {
                return self::resultado(false, 'El jefe destino no esta activo.');
            }

            $subordinadosDirectos = $db->queryAll("
                SELECT aj.id_persona
                FROM estado_cuenta.asigna_jefe aj
                INNER JOIN (
                    SELECT id_persona, MAX(id) AS mid
                    FROM estado_cuenta.asigna_jefe
                    GROUP BY id_persona
                ) ult ON ult.id_persona = aj.id_persona AND ult.mid = aj.id
                INNER JOIN estado_cuenta.persona p
                        ON p.id = aj.id_persona
                       AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                WHERE aj.id_vacante_jefe = :id_vacante
            ", ['id_vacante' => $idVacante]);

            foreach ($subordinadosDirectos as $sub) {
                if ((int)($sub['id_persona'] ?? 0) === $jefeDestino) {
                    return self::resultado(false, 'No puedes mover la vacante a una persona que depende directamente de esa misma vacante.');
                }
            }

            $db->beginTransaction();
            $subordinadosMovidos = $db->CRUD("
                UPDATE estado_cuenta.asigna_jefe aj
                INNER JOIN (
                    SELECT ult.mid
                    FROM (
                        SELECT id_persona, MAX(id) AS mid
                        FROM estado_cuenta.asigna_jefe
                        GROUP BY id_persona
                    ) ult
                ) vigente ON vigente.mid = aj.id
                SET aj.id_jefe = :id_jefe,
                    aj.id_vacante_jefe = NULL
                WHERE aj.id_vacante_jefe = :id_vacante
            ", [
                'id_jefe' => $jefeDestino,
                'id_vacante' => $idVacante,
            ]);

            $db->CRUD("
                UPDATE estado_cuenta.vacantes_personal
                SET estatus = 'Eliminada',
                    fecha_cierre = NOW()
                WHERE id = :id_vacante
                  AND UPPER(TRIM(estatus)) = 'ACTIVA'
                LIMIT 1
            ", [
                'id_vacante' => $idVacante,
            ]);
            $db->commit();

            return self::resultado(true, 'Vacante eliminada y subordinados reasignados correctamente.', [
                'id_vacante' => $idVacante,
                'jefe_destino' => $jefeDestino,
                'subordinados_movidos' => $subordinadosMovidos,
                'modo_movimiento' => $modoMovimiento,
            ]);
        } catch (\Exception $e) {
            if ($db) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al eliminar la vacante.', null, $e->getMessage());
        }
    }

    public static function actualizarJefePersonaOrganigrama($idPersona, $jefeRaw)
    {
        try {
            $db = new Database();
            self::asegurarAsignaJefeSoportaVacante($db);

            $idPersona = (int)$idPersona;
            $jefeRaw = trim((string)$jefeRaw);
            $idJefe = 0;
            $idVacanteJefe = 0;

            if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
                $idVacanteJefe = (int)$m[1];
            } else {
                $idJefe = (int)$jefeRaw;
            }

            if ($idPersona <= 0 || ($idJefe <= 0 && $idVacanteJefe <= 0)) {
                return self::resultado(false, 'Seleccione la persona y el jefe destino.');
            }

            if ($idJefe > 0 && $idPersona === $idJefe) {
                return self::resultado(false, 'Una persona no puede ser su propio jefe.');
            }

            $persona = $db->queryOne("
                SELECT id
                FROM estado_cuenta.persona
                WHERE id = :id_persona
                  AND LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if (!$persona) {
                return self::resultado(false, 'La persona seleccionada no esta activa.');
            }

            if ($idJefe > 0) {
                $jefe = $db->queryOne("
                    SELECT id
                    FROM estado_cuenta.persona
                    WHERE id = :id_jefe
                      AND LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
                    LIMIT 1
                ", ['id_jefe' => $idJefe]);

                if (!$jefe) {
                    return self::resultado(false, 'El jefe seleccionado no esta activo.');
                }

                $actual = $idJefe;
                $vistos = [];
                for ($i = 0; $i < 80 && $actual > 0; $i++) {
                    if ($actual === $idPersona) {
                        return self::resultado(false, 'No se puede asignar ese jefe porque generaria un ciclo en el organigrama.');
                    }
                    if (isset($vistos[$actual])) break;
                    $vistos[$actual] = true;

                    $rel = $db->queryOne("
                        SELECT id_jefe, id_vacante_jefe
                        FROM estado_cuenta.asigna_jefe
                        WHERE id_persona = :id_persona
                        ORDER BY id DESC
                        LIMIT 1
                    ", ['id_persona' => $actual]);

                    if (!$rel) break;
                    if (!empty($rel['id_jefe'])) {
                        $actual = (int)$rel['id_jefe'];
                        continue;
                    }
                    if (!empty($rel['id_vacante_jefe'])) {
                        $vacJefe = $db->queryOne("
                            SELECT id_jefe
                            FROM estado_cuenta.vacantes_personal
                            WHERE id = :id_vacante
                            LIMIT 1
                        ", ['id_vacante' => (int)$rel['id_vacante_jefe']]);
                        $actual = !empty($vacJefe['id_jefe']) ? (int)$vacJefe['id_jefe'] : 0;
                        continue;
                    }
                    break;
                }
            } else {
                $vacante = $db->queryOne("
                    SELECT id
                    FROM estado_cuenta.vacantes_personal
                    WHERE id = :id_vacante
                      AND UPPER(TRIM(COALESCE(estatus, ''))) = 'ACTIVA'
                    LIMIT 1
                ", ['id_vacante' => $idVacanteJefe]);

                if (!$vacante) {
                    return self::resultado(false, 'La vacante seleccionada ya no esta activa.');
                }
            }

            $asignacion = $db->queryOne("
                SELECT id
                FROM estado_cuenta.asigna_jefe
                WHERE id_persona = :id_persona
                ORDER BY id DESC
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if ($asignacion) {
                $db->CRUD("
                    UPDATE estado_cuenta.asigna_jefe
                    SET id_jefe = :id_jefe,
                        id_vacante_jefe = :id_vacante_jefe
                    WHERE id = :id
                    LIMIT 1
                ", [
                    'id_jefe' => $idJefe > 0 ? $idJefe : null,
                    'id_vacante_jefe' => $idVacanteJefe > 0 ? $idVacanteJefe : null,
                    'id' => (int)$asignacion['id'],
                ]);
            } else {
                $db->CRUD("
                    INSERT INTO estado_cuenta.asigna_jefe (id_persona, id_jefe, id_vacante_jefe)
                    VALUES (:id_persona, :id_jefe, :id_vacante_jefe)
                ", [
                    'id_persona' => $idPersona,
                    'id_jefe' => $idJefe > 0 ? $idJefe : null,
                    'id_vacante_jefe' => $idVacanteJefe > 0 ? $idVacanteJefe : null,
                ]);
            }

            return self::resultado(true, 'Jefe actualizado correctamente.', [
                'id_persona' => $idPersona,
                'id_jefe' => $idJefe > 0 ? $idJefe : null,
                'id_vacante_jefe' => $idVacanteJefe > 0 ? $idVacanteJefe : null,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el jefe.', null, $e->getMessage());
        }
    }

    public static function resolverBajaOrganigrama($idPersona, $modoReasignacion = 'sin_subordinados', $sustitutoId = null)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarAsignaJefeSoportaVacante($db);

            $idPersona = (int)$idPersona;
            $modoReasignacion = in_array($modoReasignacion, ['vacante', 'sustituto', 'sin_subordinados'], true)
                ? $modoReasignacion
                : 'sin_subordinados';
            $sustitutoId = !empty($sustitutoId) ? (int)$sustitutoId : null;

            if ($idPersona <= 0) {
                return self::resultado(false, 'Seleccione la persona en baja.');
            }

            $persona = $db->queryOne("
                SELECT id, estatus
                FROM estado_cuenta.persona
                WHERE id = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if (!$persona) {
                return self::resultado(false, 'La persona seleccionada no existe.');
            }
            if (strcasecmp(trim((string)($persona['estatus'] ?? '')), 'Baja') !== 0) {
                return self::resultado(false, 'Esta accion solo aplica para personas que ya estan en baja.');
            }

            $subordinados = $db->queryAll("
                SELECT aj.id_persona
                FROM estado_cuenta.asigna_jefe aj
                INNER JOIN estado_cuenta.persona p ON p.id = aj.id_persona
                WHERE aj.id_jefe = :id_persona
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
            ", ['id_persona' => $idPersona]);

            if (!empty($subordinados) && $modoReasignacion === 'sin_subordinados') {
                $modoReasignacion = 'vacante';
            }

            if (!empty($subordinados) && $modoReasignacion === 'sustituto') {
                if (!$sustitutoId || $sustitutoId === $idPersona) {
                    return self::resultado(false, 'Seleccione un jefe destino valido.');
                }

                $sustituto = $db->queryOne("
                    SELECT id
                    FROM estado_cuenta.persona
                    WHERE id = :id_sustituto
                      AND LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
                    LIMIT 1
                ", ['id_sustituto' => $sustitutoId]);

                if (!$sustituto) {
                    return self::resultado(false, 'El jefe destino no esta activo o no existe.');
                }
            }

            $vacanteDestinoId = null;
            $idJefeVacante = null;
            $puestoVacante = null;
            if (!empty($subordinados) && $modoReasignacion === 'vacante') {
                $puestoVacante = $db->queryOne("
                    SELECT ap.id_puesto, pp.departamento_id
                    FROM estado_cuenta.asigna_puesto ap
                    INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                    WHERE ap.id_persona = :id_persona
                      AND COALESCE(ap.activo, 1) = 1
                    ORDER BY pp.nivel DESC, ap.id ASC
                    LIMIT 1
                ", ['id_persona' => $idPersona]);

                if (empty($puestoVacante['id_puesto']) || empty($puestoVacante['departamento_id'])) {
                    $puestoVacante = $db->queryOne("
                        SELECT ap.id_puesto, pp.departamento_id
                        FROM estado_cuenta.asigna_puesto ap
                        INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                        WHERE ap.id_persona = :id_persona
                        ORDER BY COALESCE(ap.activo, 0) DESC, pp.nivel DESC, ap.id DESC
                        LIMIT 1
                    ", ['id_persona' => $idPersona]);
                }

                if (empty($puestoVacante['id_puesto']) || empty($puestoVacante['departamento_id'])) {
                    return self::resultado(false, 'No se pudo crear la vacante porque la persona no tiene puesto asignado.');
                }

                $jefeVacante = $db->queryOne("
                    SELECT id_jefe
                    FROM estado_cuenta.asigna_jefe
                    WHERE id_persona = :id_persona
                    ORDER BY id DESC
                    LIMIT 1
                ", ['id_persona' => $idPersona]);
                $idJefeVacante = !empty($jefeVacante['id_jefe']) ? (int)$jefeVacante['id_jefe'] : null;

                $vacanteActiva = $db->queryOne("
                    SELECT id
                    FROM estado_cuenta.vacantes_personal
                    WHERE id_puesto = :id_puesto
                      AND id_departamento = :id_departamento
                      AND UPPER(TRIM(COALESCE(estatus, ''))) = 'ACTIVA'
                    ORDER BY id ASC
                    LIMIT 1
                ", [
                    'id_puesto' => (int)$puestoVacante['id_puesto'],
                    'id_departamento' => (int)$puestoVacante['departamento_id'],
                ]);

                if (!empty($vacanteActiva['id'])) {
                    $vacanteDestinoId = (int)$vacanteActiva['id'];
                }
            }

            $db->beginTransaction();

            if (!empty($subordinados)) {
                if ($modoReasignacion === 'sustituto') {
                    $db->CRUD("
                        UPDATE estado_cuenta.asigna_jefe
                        SET id_jefe = :id_sustituto,
                            id_vacante_jefe = NULL
                        WHERE id_jefe = :id_persona
                    ", [
                        'id_sustituto' => $sustitutoId,
                        'id_persona' => $idPersona,
                    ]);
                } else {
                    if (!$vacanteDestinoId) {
                        $db->CRUD("
                            INSERT INTO estado_cuenta.vacantes_personal
                                (id_departamento, id_puesto, id_jefe, id_persona_baja, origen, estatus, creado_por)
                            VALUES
                                (:id_departamento, :id_puesto, :id_jefe, :id_persona_baja, 'organigrama_baja', 'Activa', :creado_por)
                        ", [
                            'id_departamento' => (int)$puestoVacante['departamento_id'],
                            'id_puesto' => (int)$puestoVacante['id_puesto'],
                            'id_jefe' => $idJefeVacante,
                            'id_persona_baja' => $idPersona,
                            'creado_por' => (int)($_SESSION['usuario_id'] ?? 0),
                        ]);
                        $vacanteDestinoId = (int)$db->lastInsertId();
                    }

                    $db->CRUD("
                        UPDATE estado_cuenta.asigna_jefe
                        SET id_jefe = NULL,
                            id_vacante_jefe = :id_vacante_jefe
                        WHERE id_jefe = :id_persona
                    ", [
                        'id_vacante_jefe' => $vacanteDestinoId,
                        'id_persona' => $idPersona,
                    ]);
                }
            }

            $db->CRUD("
                UPDATE estado_cuenta.asigna_puesto
                SET activo = 0
                WHERE id_persona = :id_persona
                  AND COALESCE(activo, 1) = 1
            ", ['id_persona' => $idPersona]);

            $db->CRUD("
                DELETE FROM estado_cuenta.asigna_jefe
                WHERE id_persona = :id_persona
            ", ['id_persona' => $idPersona]);

            $db->commit();

            return self::resultado(true, 'Baja resuelta correctamente en el organigrama.', [
                'id_persona' => $idPersona,
                'subordinados_movidos' => count($subordinados),
                'modo_reasignacion' => $modoReasignacion,
                'id_vacante_jefe' => $vacanteDestinoId,
                'id_sustituto' => $modoReasignacion === 'sustituto' ? $sustitutoId : null,
            ]);
        } catch (\Exception $e) {
            if (isset($db)) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al resolver la baja en organigrama.', null, $e->getMessage());
        }
    }

    public static function getMetaOrganigrama($idsPersonas, $idDepartamento = 0)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarAsignaJefeSoportaVacante($db);

            $ids = [];
            foreach ((array)$idsPersonas as $id) {
                $id = (int)$id;
                if ($id > 0) $ids[$id] = $id;
            }

            $ausencias = [];
            if (!empty($ids)) {
                $params = [];
                $ph = [];
                $i = 0;
                foreach ($ids as $id) {
                    $key = 'id' . $i++;
                    $ph[] = ':' . $key;
                    $params[$key] = $id;
                }
                $hoyCdmx = (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d');
                $params['hoy_cdmx'] = $hoyCdmx;
                $rowsAus = $db->queryAll("
                    SELECT
                        a.id,
                        a.id_persona,
                        a.id_razon,
                        ra.nombre AS razon_nombre,
                        a.fecha_inicio,
                        a.fecha_fin,
                        COALESCE(a.descripcion, '') AS descripcion
                    FROM estado_cuenta.ausencia a
                    INNER JOIN estado_cuenta.razon_ausencia ra ON ra.id = a.id_razon
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS max_id
                        FROM estado_cuenta.ausencia
                        WHERE activo = 1
                          AND DATE(fecha_inicio) <= :hoy_cdmx
                          AND DATE(fecha_fin) >= :hoy_cdmx
                          AND id_persona IN (" . implode(',', $ph) . ")
                        GROUP BY id_persona
                    ) latest ON latest.id_persona = a.id_persona AND latest.max_id = a.id
                ", $params);
                foreach ($rowsAus as $row) {
                    $ausencias[(int)$row['id_persona']] = $row;
                }

                if (!empty($ausencias)) {
                    $docParams = [];
                    $docPh = [];
                    $i = 0;
                    foreach (array_keys($ausencias) as $idPersonaAus) {
                        $key = 'doc_id_' . $i++;
                        $docPh[] = ':' . $key;
                        $docParams[$key] = (int)$idPersonaAus;
                    }

                    $docs = $db->queryAll("
                        SELECT
                            cdp.id,
                            cdp.id_persona,
                            cdp.archivo,
                            cdp.id_documento,
                            COALESCE(d.nombre, 'Documento de ausencia') AS documento_nombre,
                            DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                        FROM estado_cuenta.carga_documento_persona cdp
                        LEFT JOIN estado_cuenta.documento d ON d.id = cdp.id_documento
                        WHERE cdp.id_persona IN (" . implode(',', $docPh) . ")
                          AND cdp.id_documento IN (34, 35, 36)
                        ORDER BY cdp.fecha_carga DESC, cdp.id DESC
                    ", $docParams);

                    $documentosPorPersona = [];
                    foreach ($docs as $doc) {
                        $idPersonaDoc = (int)($doc['id_persona'] ?? 0);
                        if ($idPersonaDoc <= 0) {
                            continue;
                        }
                        $documentosPorPersona[$idPersonaDoc][] = $doc;
                    }

                    foreach ($ausencias as $idPersonaAus => &$ausenciaRow) {
                        $motivo = strtoupper((string)($ausenciaRow['razon_nombre'] ?? ''));
                        $idsDocumentoEsperados = [];
                        if (strpos($motivo, 'INCAPACIDAD') !== false) {
                            $idsDocumentoEsperados[] = 34;
                        } elseif (strpos($motivo, 'PERMISO') !== false) {
                            $idsDocumentoEsperados[] = 35;
                        } elseif (strpos($motivo, 'FALTA') !== false) {
                            $idsDocumentoEsperados[] = 36;
                        }

                        $docsPersona = $documentosPorPersona[$idPersonaAus] ?? [];
                        if (!empty($idsDocumentoEsperados)) {
                            $docsPersona = array_values(array_filter($docsPersona, static function ($doc) use ($idsDocumentoEsperados) {
                                return in_array((int)($doc['id_documento'] ?? 0), $idsDocumentoEsperados, true);
                            }));
                        }
                        $ausenciaRow['documentos'] = $docsPersona;
                    }
                    unset($ausenciaRow);
                }
            }

            $paramsVac = [];
            $whereDepto = '';
            $idDepartamento = (int)$idDepartamento;
            if ($idDepartamento > 0) {
                $whereDepto = ' AND v.id_departamento = :id_departamento';
                $paramsVac['id_departamento'] = $idDepartamento;
            }

            $vacantes = $db->queryAll("
                SELECT
                    v.id,
                    v.id_jefe,
                    v.id_departamento,
                    v.id_puesto,
                    v.origen,
                    v.nombre_vacante,
                    COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto,
                    pp.nombre AS nombre_puesto_base,
                    d.nombre AS nombre_departamento
                FROM estado_cuenta.vacantes_personal v
                INNER JOIN estado_cuenta.puesto pp ON pp.id = v.id_puesto
                LEFT JOIN estado_cuenta.departamento d ON d.id = v.id_departamento
                WHERE v.estatus = 'Activa'
                $whereDepto
                ORDER BY v.fecha_creacion ASC
            ", $paramsVac);

            $subordinadosVacante = $db->queryAll("
                SELECT
                    aj.id_vacante_jefe,
                    p.id,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre,
                    COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto
                FROM (
                    SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                    FROM estado_cuenta.asigna_jefe a
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS mid
                        FROM estado_cuenta.asigna_jefe
                        GROUP BY id_persona
                    ) m ON m.id_persona = a.id_persona AND m.mid = a.id
                ) aj
                INNER JOIN estado_cuenta.vacantes_personal v
                        ON v.id = aj.id_vacante_jefe
                       AND v.estatus = 'Activa'
                INNER JOIN estado_cuenta.persona p
                        ON p.id = aj.id_persona
                       AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                LEFT JOIN (
                    SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
                    FROM estado_cuenta.asigna_puesto ap
                    WHERE COALESCE(ap.activo, 1) = 1
                    GROUP BY ap.id_persona
                ) ap_sel ON ap_sel.id_persona = p.id
                LEFT JOIN estado_cuenta.puesto pp
                       ON pp.id = ap_sel.id_puesto
                WHERE aj.id_vacante_jefe IS NOT NULL
                  $whereDepto
                ORDER BY p.nombres ASC, p.apellidop ASC
            ", $paramsVac);

            return self::resultado(true, 'Meta de organigrama encontrada.', [
                'ausencias' => $ausencias,
                'vacantes' => $vacantes,
                'subordinados_vacante' => $subordinadosVacante,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener meta de organigrama.', null, $e->getMessage());
        }
    }

    private static function asegurarTablaPermisosPuesto(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.permisos_puesto (
                id INT NOT NULL AUTO_INCREMENT,
                id_puesto INT NOT NULL,
                modulo_web_id INT NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_permisos_puesto (id_puesto, modulo_web_id),
                KEY idx_permisos_puesto_puesto (id_puesto),
                KEY idx_permisos_puesto_modulo (modulo_web_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public static function getPermisosPuestoConfig($idPuesto = 0)
    {
        try {
            $db = new Database();
            self::asegurarTablaPermisosPuesto($db);
            self::asegurarModuloConveniosDescargarExcel($db);
            self::asegurarModuloAccesosCapitalHumanoDb($db);

            $puestos = $db->queryAll("
                SELECT
                    p.id,
                    p.nombre,
                    COALESCE(p.nivel, 0) AS nivel,
                    COALESCE(d.nombre, 'Sin departamento') AS departamento,
                    COALESCE(dorg.nombre, 'Sin area') AS area,
                    COALESCE(dir.nombre, 'Sin direccion') AS direccion
                FROM estado_cuenta.puesto p
                LEFT JOIN estado_cuenta.departamento d
                    ON d.id = p.departamento_id
                LEFT JOIN estado_cuenta.departamento_organizacional dorg
                    ON dorg.id = d.id_departamento_organizacional
                LEFT JOIN estado_cuenta.asigna_direcciones ad
                    ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                   AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN estado_cuenta.direcciones_organizacion dir
                    ON dir.id = ad.id_direccion
                WHERE COALESCE(p.activo, 1) = 1
                  AND COALESCE(d.activo, 1) = 1
                ORDER BY direccion ASC, area ASC, departamento ASC, p.nombre ASC
            ");

            $modulos = $db->queryAll("
                SELECT
                    m.id,
                    CASE WHEN m.id = 27 THEN 'Panel Admin' ELSE m.nombre END AS modulo_nombre,
                    COALESCE(NULLIF(TRIM(m.pestana), ''), m.nombre) AS pestana,
                    COALESCE(NULLIF(TRIM(m.descripcion), ''), '') AS descripcion
                FROM estado_cuenta.modulos_web m
                WHERE COALESCE(m.activo, 1) = 1
                  AND m.id NOT IN (25)
                  AND LOWER(TRIM(COALESCE(m.pestana, ''))) <> 'permisos especiales'
                ORDER BY modulo_nombre ASC, pestana ASC, m.id ASC
            ");

            $seleccionados = [];
            $idPuesto = (int) $idPuesto;
            if ($idPuesto > 0) {
                $seleccionados = self::modulosPlantillaPuesto($db, $idPuesto);
            }

            return self::resultado(true, 'Configuracion de permisos cargada.', [
                'puestos' => $puestos,
                'modulos' => $modulos,
                'seleccionados' => $seleccionados,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cargar permisos por puesto.', null, $e->getMessage());
        }
    }

    public static function getPermisosPuesto($idPuesto)
    {
        try {
            $db = new Database();
            self::asegurarTablaPermisosPuesto($db);

            $idPuesto = (int) $idPuesto;
            if ($idPuesto <= 0) {
                return self::resultado(false, 'ID de puesto invalido.');
            }

            return self::resultado(true, 'Permisos del puesto cargados.', [
                'id_puesto' => $idPuesto,
                'seleccionados' => self::modulosPlantillaPuesto($db, $idPuesto),
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cargar permisos del puesto.', null, $e->getMessage());
        }
    }

    public static function guardarPermisosPuesto($idPuesto, array $modulos)
    {
        $db = null;
        try {
            $db = new Database();
            self::asegurarTablaPermisosPuesto($db);
            self::asegurarModuloConveniosDescargarExcel($db);
            self::asegurarModuloAccesosCapitalHumanoDb($db);

            $idPuesto = (int) $idPuesto;
            if ($idPuesto <= 0) {
                return self::resultado(false, 'Selecciona un puesto valido.');
            }

            $puesto = $db->queryOne(
                "SELECT id FROM estado_cuenta.puesto WHERE id = :id AND COALESCE(activo, 1) = 1 LIMIT 1",
                ['id' => $idPuesto]
            );
            if (!$puesto) {
                return self::resultado(false, 'Puesto no encontrado.');
            }

            $modulos = array_values(array_unique(array_filter(array_map('intval', $modulos), function ($id) {
                return $id > 0 && $id !== 25;
            })));

            $modulosValidos = [];
            if (!empty($modulos)) {
                $placeholders = [];
                $params = [];
                foreach ($modulos as $idx => $moduloId) {
                    $key = 'm' . $idx;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $moduloId;
                }
                $rows = $db->queryAll(
                    "SELECT id
                       FROM estado_cuenta.modulos_web
                      WHERE COALESCE(activo, 1) = 1
                        AND LOWER(TRIM(COALESCE(pestana, ''))) <> 'permisos especiales'
                        AND id IN (" . implode(',', $placeholders) . ")",
                    $params
                );
                $modulosValidos = array_map('intval', array_column($rows, 'id'));
            }

            $db->beginTransaction();
            $db->CRUD(
                "UPDATE estado_cuenta.permisos_puesto SET activo = 0 WHERE id_puesto = :id_puesto",
                ['id_puesto' => $idPuesto]
            );

            foreach ($modulosValidos as $moduloId) {
                $db->CRUD(
                    "INSERT INTO estado_cuenta.permisos_puesto (id_puesto, modulo_web_id, activo)
                     VALUES (:id_puesto, :modulo_web_id, 1)
                     ON DUPLICATE KEY UPDATE activo = 1, actualizado_en = NOW()",
                    ['id_puesto' => $idPuesto, 'modulo_web_id' => $moduloId]
                );
            }

            $db->commit();

            return self::resultado(true, 'Plantilla de permisos guardada.', [
                'id_puesto' => $idPuesto,
                'seleccionados' => $modulosValidos,
            ]);
        } catch (\Exception $e) {
            if ($db) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al guardar plantilla de permisos.', null, $e->getMessage());
        }
    }

    private static function modulosPlantillaPuesto(Database $db, int $idPuesto): array
    {
        $rows = $db->queryAll(
            "SELECT pp.modulo_web_id
               FROM estado_cuenta.permisos_puesto pp
               INNER JOIN estado_cuenta.modulos_web mw
                   ON mw.id = pp.modulo_web_id
                  AND COALESCE(mw.activo, 1) = 1
                  AND LOWER(TRIM(COALESCE(mw.pestana, ''))) <> 'permisos especiales'
              WHERE pp.id_puesto = :id_puesto
                AND pp.activo = 1
              ORDER BY pp.modulo_web_id ASC",
            ['id_puesto' => $idPuesto]
        );

        return array_map('intval', array_column($rows, 'modulo_web_id'));
    }

    private static function aplicarPermisosPuestoAPersonaConDb(Database $db, int $idPersona, int $idPuesto): int
    {
        if ($idPersona <= 0 || $idPuesto <= 0) {
            return 0;
        }

        // CREATE TABLE provoca un commit implícito en MySQL. Dentro de una
        // transacción, quien invoca debe haber garantizado el esquema antes.
        if (!$db->inTransaction()) {
            self::asegurarTablaPermisosPuesto($db);
        }
        $modulos = self::modulosPlantillaPuesto($db, $idPuesto);
        if (empty($modulos)) {
            return 0;
        }

        $insertados = 0;
        foreach ($modulos as $moduloId) {
            $existe = $db->queryOne(
                "SELECT id
                   FROM estado_cuenta.asigna_modulo_web
                  WHERE usuario_id = :usuario_id
                    AND modulo_web_id = :modulo_web_id
                  LIMIT 1",
                ['usuario_id' => $idPersona, 'modulo_web_id' => $moduloId]
            );

            if ($existe) {
                continue;
            }

            $db->CRUD(
                "INSERT INTO estado_cuenta.asigna_modulo_web (usuario_id, modulo_web_id)
                 VALUES (:usuario_id, :modulo_web_id)",
                ['usuario_id' => $idPersona, 'modulo_web_id' => $moduloId]
            );
            $insertados++;
        }

        if ($insertados > 0) {
            $db->CRUD(
                "UPDATE estado_cuenta.persona
                    SET session_version = COALESCE(session_version, 1) + 1
                  WHERE id = :id",
                ['id' => $idPersona]
            );
        }

        return $insertados;
    }

    public static function aplicarPermisosPuestoAPersona(int $idPersona, int $idPuesto)
    {
        try {
            $db = new Database();
            $insertados = self::aplicarPermisosPuestoAPersonaConDb($db, $idPersona, $idPuesto);
            return self::resultado(true, 'Permisos automaticos aplicados.', ['insertados' => $insertados]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al aplicar permisos automaticos.', null, $e->getMessage());
        }
    }

    /**
     * Función optimizada para reporte de Capital Humano
     * Los filtros se aplican directamente en SQL (más rápido)
     */
    public static function getGestoresParaReporte($filtros = [])
    {
        $departamento = $filtros['departamento'] ?? null;
        $puesto = $filtros['puesto'] ?? null;
        $estatus = $filtros['estatus'] ?? null;
        $multipuesto = $filtros['multipuesto'] ?? null;
        $empresa = $filtros['empresa'] ?? null;
        $direccion = $filtros['direccion'] ?? null;
        $area = $filtros['area'] ?? null;

        $params = [];
        $whereConditions = [
            "LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')",
            UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p'),
        ];

        // Filtro por departamento
        if (!empty($empresa)) {
            $whereConditions[] = "COALESCE(emp.nombre_comercial, 'MaxiKash') = :empresa";
            $params['empresa'] = $empresa;
        }

        if (!empty($direccion)) {
            $whereConditions[] = "COALESCE(dir.nombre, '') = :direccion";
            $params['direccion'] = $direccion;
        }

        if (!empty($area)) {
            $whereConditions[] = "COALESCE(dorg.nombre, '') = :area";
            $params['area'] = $area;
        }

        // Filtro por departamento
        if (!empty($departamento)) {
            $whereConditions[] = "d.nombre = :departamento";
            $params['departamento'] = $departamento;
        }

        // Filtro por puesto
        if (!empty($puesto)) {
            $whereConditions[] = "pp.nombre = :puesto";
            $params['puesto'] = $puesto;
        }

        // Filtro por estatus
        if (!empty($estatus)) {
            $whereConditions[] = "p.estatus = :estatus";
            $params['estatus'] = $estatus;
        }

        // Filtro por multipuesto (subquery optimizada)
        $multipuestoJoin = "";
        if ($multipuesto === 'multiples') {
            $whereConditions[] = "(SELECT COUNT(*) FROM asigna_puesto ap2 WHERE ap2.id_persona = p.id AND COALESCE(ap2.activo, 1) = 1) > 1";
        } elseif ($multipuesto === 'unico') {
            $whereConditions[] = "(SELECT COUNT(*) FROM asigna_puesto ap2 WHERE ap2.id_persona = p.id AND COALESCE(ap2.activo, 1) = 1) = 1";
        }

        $whereSQL = implode(" AND ", $whereConditions);

        $query = <<<SQL
        SELECT
            p.id,
            p.numero_empleado,
            p.codigo_contpac,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
            COALESCE(p.telefono_uno, '') AS telefono,
            COALESCE(p.telefono_dos, '') AS telefono_dos,
            COALESCE(p.correo, '') AS correo,
            COALESCE(p.domicilio_calle_texto, '') AS domicilio_calle_texto,
            COALESCE(p.codigo_postal, '') AS codigo_postal,
            COALESCE(p.curp, '') AS curp,
            COALESCE(r.rfc, '') AS rfc,
            COALESCE(r.nss, '') AS nss,
            COALESCE(r.fecha_nacimiento, '') AS fecha_nacimiento,
            COALESCE(r.sexo, '') AS sexo,
            COALESCE(r.estado_civil, '') AS estado_civil,
            COALESCE(r.fecha_imss_alta, '') AS fecha_imss_alta,
            COALESCE(r.registro_patronal, '') AS registro_patronal,
            COALESCE(r.codigo_contpaq, '') AS codigo_contpaq_rrhh,
            COALESCE(ben.beneficiarios, '') AS beneficiarios,
            COALESCE(ben.porcentaje_total, 0) AS beneficiarios_porcentaje,

            pp.id AS id_puesto,
            COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto,

            d.id AS id_departamento,
            COALESCE(d.nombre, 'Sin departamento') AS nombre_departamento,
            COALESCE(emp.nombre_comercial, 'MaxiKash') AS nombre_empresa,
            COALESCE(NULLIF(dir.nombre, ''), NULLIF(r.direccion_organizacional, ''), '') AS nombre_direccion,
            COALESCE(NULLIF(dorg.nombre, ''), NULLIF(r.area_texto, ''), '') AS nombre_area,

            COALESCE(
                CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom),
                'Sin jefe'
            ) AS nombre_jefe,

            p.estatus,
            COALESCE(p.user_name, 'Sin usuario') AS usuario,

            aus_activa.razon_nombre  AS ausencia_razon,
            aus_activa.fecha_inicio  AS ausencia_fecha_inicio,
            aus_activa.fecha_fin     AS ausencia_fecha_fin,
            vac_activa.id            AS vacaciones_id,
            vac_activa.fecha_inicio  AS vacaciones_fecha_inicio,
            vac_activa.fecha_fin     AS vacaciones_fecha_fin,
            CASE
                WHEN aus_activa.id_persona IS NOT NULL OR vac_activa.id IS NOT NULL THEN 1
                ELSE 0
            END AS bloqueo_baja_activo,
            CASE
                WHEN aus_activa.id_persona IS NOT NULL THEN aus_activa.razon_nombre
                WHEN vac_activa.id IS NOT NULL THEN 'VACACIONES'
                ELSE NULL
            END AS bloqueo_baja_motivo,
            CASE
                WHEN aus_activa.id_persona IS NOT NULL THEN CONCAT('No se puede dar de baja: la persona tiene ', aus_activa.razon_nombre, ' vigente del ', DATE(aus_activa.fecha_inicio), ' al ', DATE(aus_activa.fecha_fin), '.')
                WHEN vac_activa.id IS NOT NULL THEN CONCAT('No se puede dar de baja: la persona tiene VACACIONES vigentes del ', DATE(vac_activa.fecha_inicio), ' al ', DATE(vac_activa.fecha_fin), '.')
                ELSE NULL
            END AS bloqueo_baja_mensaje

        FROM persona p

        LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona AND COALESCE(ap.activo, 1) = 1
        LEFT JOIN puesto pp ON pp.id = ap.id_puesto
        LEFT JOIN departamento d ON d.id = pp.departamento_id
        LEFT JOIN persona_datos_rrhh r ON r.id_persona = p.id
        LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
        LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = d.id_departamento_organizacional AND COALESCE(ad.activo, 1) = 1
        LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
        LEFT JOIN rrhh_empresas emp ON emp.id = COALESCE(d.id_empresa, dorg.id_empresa, dir.id_empresa, 1)
        LEFT JOIN (
            SELECT
                id_persona,
                GROUP_CONCAT(
                    TRIM(BOTH ' - ' FROM CONCAT_WS(' - ',
                        NULLIF(nombre_beneficiario, ''),
                        NULLIF(parentesco, ''),
                        NULLIF(numero, ''),
                        CASE
                            WHEN porcentaje IS NULL THEN NULL
                            ELSE CONCAT(FORMAT(porcentaje, 2), '%')
                        END
                    ))
                    ORDER BY id ASC SEPARATOR ' | '
                ) AS beneficiarios,
                SUM(COALESCE(porcentaje, 0)) AS porcentaje_total
            FROM persona_beneficiario_fallecimiento
            WHERE estatus = 'Activo'
            GROUP BY id_persona
        ) ben ON ben.id_persona = p.id

        LEFT JOIN (
            SELECT a.id_persona, a.id_jefe
            FROM asigna_jefe a
            INNER JOIN (
                SELECT id_persona, MAX(id) AS mid
                FROM asigna_jefe
                GROUP BY id_persona
            ) m ON a.id_persona = m.id_persona AND a.id = m.mid
        ) aj ON aj.id_persona = p.id

        LEFT JOIN persona pj ON pj.id = aj.id_jefe

        LEFT JOIN (
            SELECT a.id_persona, ra.nombre AS razon_nombre, a.fecha_inicio, a.fecha_fin
            FROM ausencia a
            INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
            INNER JOIN (
                SELECT id_persona, MAX(id) AS max_id
                FROM ausencia
                WHERE activo = 1
                  AND DATE(fecha_inicio) <= CURDATE()
                  AND DATE(fecha_fin)    >= CURDATE()
                GROUP BY id_persona
            ) latest ON latest.id_persona = a.id_persona AND latest.max_id = a.id
        ) aus_activa ON aus_activa.id_persona = p.id

        LEFT JOIN (
            SELECT
                s.id,
                s.id_persona,
                COALESCE(MIN(d.fecha), s.fecha_inicio) AS fecha_inicio,
                COALESCE(MAX(d.fecha), s.fecha_fin) AS fecha_fin
            FROM estado_cuenta.vacaciones_solicitudes s
            LEFT JOIN estado_cuenta.vacaciones_solicitud_dias d ON d.id_solicitud = s.id
            WHERE s.estatus IN ('aprobada', 'tomada')
              AND (
                  CURDATE() BETWEEN DATE(s.fecha_inicio) AND DATE(s.fecha_fin)
                  OR d.fecha = CURDATE()
              )
            GROUP BY s.id, s.id_persona, s.fecha_inicio, s.fecha_fin
        ) vac_activa ON vac_activa.id_persona = p.id

        WHERE {$whereSQL}

        ORDER BY d.nombre ASC, pp.nombre ASC, p.nombres ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Gestores encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener gestores.', null, $e->getMessage());
        }
    }

    public static function getDiasAcumuladosReingresos(int $anio): array
    {
        try {
            $anioActual = (int) (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y');
            if ($anio < 2000 || $anio > $anioActual) {
                return self::resultado(false, 'Ejercicio invalido.');
            }

            $tz = new \DateTimeZone('America/Mexico_City');
            $inicioEjercicio = new \DateTimeImmutable($anio . '-01-01', $tz);
            $finEjercicio = new \DateTimeImmutable($anio . '-12-31', $tz);
            $hoy = new \DateTimeImmutable('today', $tz);
            $fechaCorte = $anio === $anioActual && $hoy < $finEjercicio ? $hoy : $finEjercicio;
            $finSql = $fechaCorte->format('Y-m-d');

            $db = new Database();
            $excluir = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');

            $personas = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.apellidom,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                    p.estatus,
                    p.fecha_ingreso,
                    COALESCE(GROUP_CONCAT(DISTINCT pp.nombre ORDER BY pp.nombre SEPARATOR ', '), 'Sin puesto') AS nombre_puesto,
                    COALESCE(GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', '), 'Sin departamento') AS nombre_departamento
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN estado_cuenta.departamento d ON d.id = pp.departamento_id
                WHERE {$excluir}
                  AND (
                    (CAST(p.fecha_ingreso AS CHAR) <> '0000-00-00' AND CAST(p.fecha_ingreso AS CHAR) <= :fecha_corte)
                    OR EXISTS (
                        SELECT 1
                        FROM estado_cuenta.reingresos rpx
                        WHERE rpx.id_persona = p.id
                          AND DATE(rpx.fecha_reingreso) <= :fecha_corte
                    )
                  )
                GROUP BY
                    p.id,
                    p.numero_empleado,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.apellidom,
                    p.estatus,
                    p.fecha_ingreso
                ORDER BY nombre_departamento ASC, nombre_puesto ASC, p.nombres ASC
            ", ['fecha_corte' => $finSql]);

            if (empty($personas)) {
                return self::resultado(true, 'Sin plantilla para el ejercicio.', [
                    'anio' => $anio,
                    'fecha_corte' => $finSql,
                    'rows' => [],
                ]);
            }

            $ids = array_values(array_unique(array_map(static function ($row) {
                return (int) ($row['id'] ?? 0);
            }, $personas)));
            $ids = array_values(array_filter($ids));
            if (empty($ids)) {
                return self::resultado(true, 'Sin plantilla para el ejercicio.', [
                    'anio' => $anio,
                    'fecha_corte' => $finSql,
                    'rows' => [],
                ]);
            }

            $idsSql = implode(',', array_map('intval', $ids));
            $bajasRows = $db->queryAll("
                SELECT id_persona, fecha_baja
                FROM estado_cuenta.baja_persona
                WHERE id_persona IN ({$idsSql})
                  AND DATE(fecha_baja) <= :fecha_corte
                ORDER BY id_persona ASC, fecha_baja ASC, id ASC
            ", ['fecha_corte' => $finSql]);
            $reingresosRows = $db->queryAll("
                SELECT id_persona, fecha_reingreso
                FROM estado_cuenta.reingresos
                WHERE id_persona IN ({$idsSql})
                  AND DATE(fecha_reingreso) <= :fecha_corte
                ORDER BY id_persona ASC, fecha_reingreso ASC, id ASC
            ", ['fecha_corte' => $finSql]);

            $bajasPorPersona = [];
            foreach ($bajasRows as $row) {
                $idPersona = (int) ($row['id_persona'] ?? 0);
                $fecha = self::fechaDiaRrhh($row['fecha_baja'] ?? '', $tz);
                if ($idPersona > 0 && $fecha) {
                    $bajasPorPersona[$idPersona][] = $fecha;
                }
            }

            $reingresosPorPersona = [];
            foreach ($reingresosRows as $row) {
                $idPersona = (int) ($row['id_persona'] ?? 0);
                $fecha = self::fechaDiaRrhh($row['fecha_reingreso'] ?? '', $tz);
                if ($idPersona > 0 && $fecha) {
                    $reingresosPorPersona[$idPersona][] = $fecha;
                }
            }

            $rows = [];
            foreach ($personas as $persona) {
                $idPersona = (int) ($persona['id'] ?? 0);
                $fechaIngreso = self::fechaDiaRrhh($persona['fecha_ingreso'] ?? '', $tz);
                $reingresos = $reingresosPorPersona[$idPersona] ?? [];

                $inicios = [];
                if ($fechaIngreso && $fechaIngreso <= $fechaCorte) {
                    $inicios[] = ['fecha' => $fechaIngreso, 'tipo' => 'Ingreso inicial'];
                }
                foreach ($reingresos as $fechaReingreso) {
                    if ($fechaReingreso <= $fechaCorte) {
                        $inicios[] = ['fecha' => $fechaReingreso, 'tipo' => 'Reingreso'];
                    }
                }
                usort($inicios, static function ($a, $b) {
                    return $a['fecha'] <=> $b['fecha'];
                });

                if (empty($inicios)) {
                    continue;
                }

                $bajas = $bajasPorPersona[$idPersona] ?? [];
                $periodos = [];
                $diasAcumulados = 0;

                foreach ($inicios as $idx => $inicio) {
                    $fechaInicio = $inicio['fecha'];
                    $siguienteInicio = $inicios[$idx + 1]['fecha'] ?? null;
                    $fechaFin = $fechaCorte;
                    $finTipo = 'Corte';

                    foreach ($bajas as $fechaBaja) {
                        if ($fechaBaja < $fechaInicio) {
                            continue;
                        }
                        if ($siguienteInicio && $fechaBaja >= $siguienteInicio) {
                            break;
                        }
                        $fechaFin = $fechaBaja;
                        $finTipo = 'Baja';
                        break;
                    }
                    if ($siguienteInicio) {
                        $diaPrevioSiguiente = $siguienteInicio->modify('-1 day');
                        if ($diaPrevioSiguiente < $fechaFin) {
                            $fechaFin = $diaPrevioSiguiente;
                            $finTipo = 'Previo a reingreso';
                        }
                    }

                    $inicioSolapado = $fechaInicio < $inicioEjercicio ? $inicioEjercicio : $fechaInicio;
                    $finSolapado = $fechaFin > $fechaCorte ? $fechaCorte : $fechaFin;

                    if ($inicioSolapado > $finSolapado) {
                        continue;
                    }

                    $dias = (int) $inicioSolapado->diff($finSolapado)->days + 1;
                    $diasAcumulados += $dias;
                    $periodos[] = [
                        'tipo' => $inicio['tipo'],
                        'inicio' => $inicioSolapado->format('Y-m-d'),
                        'fin' => $finSolapado->format('Y-m-d'),
                        'fin_tipo' => $finTipo,
                        'dias' => $dias,
                    ];
                }

                if ($diasAcumulados <= 0) {
                    continue;
                }

                $reingresosEjercicio = array_values(array_filter($reingresos, static function ($fecha) use ($inicioEjercicio, $fechaCorte) {
                    return $fecha >= $inicioEjercicio && $fecha <= $fechaCorte;
                }));
                $bajasEjercicio = array_values(array_filter($bajas, static function ($fecha) use ($inicioEjercicio, $fechaCorte) {
                    return $fecha >= $inicioEjercicio && $fecha <= $fechaCorte;
                }));

                $detalle = array_map(static function ($periodo) {
                    return $periodo['tipo'] . ': ' . $periodo['inicio'] . ' a ' . $periodo['fin'] . ' (' . $periodo['dias'] . ' dias)';
                }, $periodos);

                $rows[] = [
                    'numero_empleado' => $persona['numero_empleado'] ?? '',
                    'nombre_completo' => trim((string) ($persona['nombre_completo'] ?? '')),
                    'departamento' => $persona['nombre_departamento'] ?? 'Sin departamento',
                    'puesto' => $persona['nombre_puesto'] ?? 'Sin puesto',
                    'estatus_actual' => $persona['estatus'] ?? '',
                    'fecha_ingreso_inicial' => $fechaIngreso ? $fechaIngreso->format('Y-m-d') : '',
                    'tuvo_reingreso' => count($reingresos) > 0 ? 'Si' : 'No',
                    'reingresos_historicos' => count($reingresos),
                    'reingresos_ejercicio' => count($reingresosEjercicio),
                    'bajas_ejercicio' => count($bajasEjercicio),
                    'dias_acumulados' => $diasAcumulados,
                    'periodos_contabilizados' => count($periodos),
                    'detalle_periodos' => implode(' | ', $detalle),
                ];
            }

            return self::resultado(true, 'Dias acumulados calculados.', [
                'anio' => $anio,
                'fecha_corte' => $finSql,
                'rows' => $rows,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al calcular dias acumulados.', null, $e->getMessage());
        }
    }

    private static function fechaDiaRrhh($valor, \DateTimeZone $tz): ?\DateTimeImmutable
    {
        $valor = trim((string) $valor);
        if ($valor === '' || $valor === '0000-00-00' || $valor === '0000-00-00 00:00:00') {
            return null;
        }
        try {
            return (new \DateTimeImmutable($valor, $tz))->setTime(0, 0, 0);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getPersonaDetalle($idPersona)
    {
        try {
            $db = new Database();

            $query = <<<SQL
            SELECT
                p.*,
                p.telefono_uno AS telefono,
                ap.id_puesto,
                dd.nombre as departamento,
                dd.id as id_departamento,
                CASE
                    WHEN aj.id_vacante_jefe IS NOT NULL THEN CONCAT('vacante:', aj.id_vacante_jefe)
                    ELSE aj.id_jefe
                END AS id_jefe,
                aj.id_vacante_jefe,
                p.password,
                al.id_legion
            FROM persona p
            LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
            LEFT JOIN puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN departamento dd ON dd.id = pu.departamento_id
            LEFT JOIN asigna_jefe aj ON aj.id_persona = p.id
            LEFT JOIN asigna_legion al ON al.id_persona = p.id AND al.activo = 1
            WHERE p.id = $idPersona
              AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
            LIMIT 1
        SQL;

            $persona = $db->queryOne($query);

            return self::resultado(true, 'Persona encontrada.', $persona);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function actualizarModuloPerfil($idPersona, $moduloId, $asignado)
    {
        try {
            $idPersona = (int) $idPersona;
            $moduloId = (int) $moduloId;
            $asignado = (int) $asignado;
            $accesoLeonidasPermanente = $idPersona === self::PERSONA_LAZARO_RAUDEL
                && $moduloId === self::MODULO_ASISTENTE_SPARTA;
            if ($accesoLeonidasPermanente) {
                $asignado = 1;
            }

            $db = new Database();
            if ((int) $moduloId === self::MODULO_CONVENIOS_DESCARGAR_EXCEL) {
                self::asegurarModuloConveniosDescargarExcel($db);
            }
            if ((int) $moduloId === self::MODULO_ACCESOS_CAPITAL_HUMANO) {
                self::asegurarModuloAccesosCapitalHumanoDb($db);
            }
            if ((int) $moduloId === self::MODULO_TRACKING_CANCELAR_RUTA) {
                self::asegurarModuloTrackingCancelarRuta($db);
            }

            if ($asignado === 1) {

                // 1️⃣ Validar si ya existe
                $queryExiste = <<<SQL
                SELECT id
                FROM asigna_modulo_web
                WHERE usuario_id = $idPersona
                  AND modulo_web_id = $moduloId
                LIMIT 1
            SQL;

                $existe = $db->queryOne($queryExiste);

                if (!$existe) {
                    $moduloId = (int) $moduloId;
                    $db->CRUD(
                        "INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id) VALUES (:uid, :mid)",
                        ['uid' => $idPersona, 'mid' => $moduloId]
                    );
                    if ($moduloId === 27) {
                        $db->CRUD('DELETE FROM asigna_modulo_web WHERE usuario_id = :uid AND modulo_web_id IN (25)', ['uid' => $idPersona]);
                    }
                }
                $db->CRUD(
                    "UPDATE persona SET session_version = COALESCE(session_version, 1) + 1 WHERE id = :id",
                    ['id' => (int) $idPersona]
                );

                return self::resultado(
                    true,
                    'Módulo asignado correctamente'
                );

            } else {

                // 3️⃣ Eliminar asignación (Panel Admin = 27: quitar también 25 ligado legado)
                $moduloId = (int) $moduloId;
                $db->CRUD(
                    "DELETE FROM asigna_modulo_web WHERE usuario_id = :uid AND modulo_web_id = :mid",
                    ['uid' => $idPersona, 'mid' => $moduloId]
                );
                if ($moduloId === 27) {
                    $db->CRUD(
                        'DELETE FROM asigna_modulo_web WHERE usuario_id = :uid AND modulo_web_id IN (25)',
                        ['uid' => $idPersona]
                    );
                }
                $db->CRUD(
                    "UPDATE persona SET session_version = COALESCE(session_version, 1) + 1 WHERE id = :id",
                    ['id' => (int) $idPersona]
                );

                return self::resultado(
                    true,
                    'Módulo eliminado correctamente'
                );
            }
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar módulo del perfil.', null, $e->getMessage());
        }
    }

    /**
     * Obtener documentos de una baja usando registro_baja
     */
    public static function getDocumentosBaja($registro_baja, int $id_documento)
    {
        try {
            $db = new Database();

            // Primero obtener el id_persona desde baja_persona
            $baja = $db->queryOne("
                SELECT id_persona
                FROM estado_cuenta.baja_persona
                WHERE id = :registro_baja
            ", ['registro_baja' => $registro_baja]);

            if (!$baja || !isset($baja['id_persona'])) {
                return self::resultado(false, 'Baja no encontrada.', []);
            }

            $id_persona = $baja['id_persona'];

            // Obtener documentos
            $documentos = $db->queryAll("
                SELECT
                    cdp.id,
                    cdp.archivo,
                    d.nombre AS nombre_documento,
                    DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM estado_cuenta.carga_documento_persona cdp
                INNER JOIN estado_cuenta.documento d ON d.id = cdp.id_documento
                WHERE cdp.id_persona = :id_persona
                AND cdp.id_documento = :id_documento
                ORDER BY cdp.fecha_carga DESC
            ", [
                'id_persona' => $id_persona,
                'id_documento' => $id_documento
            ]);

            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    /**
     * Guardar documentos de una baja
     */
    public static function guardarDocumentosBaja($registro_baja, int $id_documento, $archivos)
    {
        try {
            $db = new Database();

            // Obtener el id_persona desde baja_persona
            $baja = $db->queryOne("
                SELECT id_persona
                FROM estado_cuenta.baja_persona
                WHERE id = :registro_baja
            ", ['registro_baja' => $registro_baja]);

            if (!$baja || !isset($baja['id_persona'])) {
                return self::resultado(false, 'Baja no encontrada.');
            }

            $id_persona = $baja['id_persona'];

            $archivosGuardados = [];

            foreach ($archivos as $nombreArchivo) {
                $archivoEsc = addslashes($nombreArchivo);

                $db->queryOne("
                    INSERT INTO estado_cuenta.carga_documento_persona
                    (id_persona, id_documento, archivo, fecha_carga)
                    VALUES
                    (:id_persona, :id_documento, :archivo, NOW())
                ", [
                    'id_persona' => $id_persona,
                    'id_documento' => $id_documento,
                    'archivo' => $archivoEsc
                ]);

                $archivosGuardados[] = $nombreArchivo;
            }

            return self::resultado(true, 'Documentos guardados correctamente.', $archivosGuardados);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documentos.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar documento de una baja
     */
    public static function eliminarDocumentoBaja($id_documento_carga)
    {
        try {
            $db = new Database();

            // Primero obtener el nombre del archivo para eliminarlo físicamente
            $documento = $db->queryOne("
                SELECT archivo
                FROM estado_cuenta.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            if (!$documento) {
                return self::resultado(false, 'Documento no encontrado.');
            }

            $nombreArchivo = $documento['archivo'];

            // Eliminar de la base de datos
            $db->queryOne("
                DELETE FROM estado_cuenta.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            // Eliminar archivo físico
            $rutaArchivo = sparta_uploads_join('bajas', $nombreArchivo);
            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }

            return self::resultado(true, 'Documento eliminado correctamente.');

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar documento.', null, $e->getMessage());
        }
    }

    /**
     * Recupera un documento asociado a una persona que cuenta con registro de baja.
     */
    public static function obtenerDocumentoBaja(int $id_documento_carga)
    {
        try {
            $db = new Database();
            $documento = $db->queryOne("
                SELECT cdp.id, cdp.id_persona, cdp.id_documento, cdp.archivo
                FROM estado_cuenta.carga_documento_persona cdp
                INNER JOIN estado_cuenta.baja_persona bp ON bp.id_persona = cdp.id_persona
                WHERE cdp.id = :id
                LIMIT 1
            ", ['id' => $id_documento_carga]);

            if (!$documento) {
                return self::resultado(false, 'Documento no encontrado.');
            }

            return self::resultado(true, 'Documento encontrado.', $documento);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar documento.', null, $e->getMessage());
        }
    }

    /**
     * Obtener tipos de documentos disponibles desde la base de datos
     */
    public static function getTiposDocumentos()
    {
        try {
            $db = new Database();
            self::asegurarDocumentoOtros($db);
            self::asegurarDocumentoCartaCompromisoGestor();

            // Obtener documentos activos desde la base de datos
            $documentos = $db->queryAll("
                SELECT id, nombre, clave
                FROM estado_cuenta.documento
                WHERE activo = 1
                  AND id NOT IN (" . implode(',', self::DOCUMENTOS_EXCLUIDOS_RRHH) . ")
                ORDER BY nombre
            ");

            return self::resultado(true, 'Tipos de documentos encontrados.', $documentos ?? []);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener tipos de documentos.', [], $e->getMessage());
        }
    }

    private static function asegurarDocumentoOtros(Database $db): void
    {
        $otros = $db->queryOne("
            SELECT id, activo
            FROM estado_cuenta.documento
            WHERE clave = 'OTROS'
               OR LOWER(TRIM(nombre)) = 'otros'
            LIMIT 1
        ");

        if ($otros) {
            if ((int) ($otros['activo'] ?? 0) !== 1) {
                $db->CRUD("
                    UPDATE estado_cuenta.documento
                    SET activo = 1
                    WHERE id = :id
                ", ['id' => (int) $otros['id']]);
            }
            return;
        }

        $db->CRUD("
            INSERT INTO estado_cuenta.documento (clave, nombre, obligatorio, activo)
            VALUES ('OTROS', 'Otros', 0, 1)
        ");
    }

    public static function getPersonasParaImportacionDocumentos()
    {
        try {
            $db = new Database();
            $personas = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.apellidom,
                    p.curp,
                    COALESCE(p.estatus, '') AS estatus,
                    DATE_FORMAT(bp.fecha_baja, '%Y-%m-%d') AS fecha_baja
                FROM estado_cuenta.persona p
                LEFT JOIN (
                    SELECT id_persona, MAX(id) AS id_ultima_baja
                    FROM estado_cuenta.baja_persona
                    GROUP BY id_persona
                ) ub ON ub.id_persona = p.id
                LEFT JOIN estado_cuenta.baja_persona bp ON bp.id = ub.id_ultima_baja
                ORDER BY
                    CASE WHEN p.estatus = 'Baja' THEN 1 ELSE 0 END,
                    p.nombres ASC,
                    p.apellidop ASC,
                    p.apellidom ASC
            ");

            return self::resultado(true, 'Personas encontradas.', $personas ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener personas para importación.', [], $e->getMessage());
        }
    }

    public static function getPersonaParaImportacionDocumentos($idPersona)
    {
        try {
            $idPersona = (int) $idPersona;
            if ($idPersona <= 0) {
                return self::resultado(false, 'Persona invalida.', null);
            }

            $db = new Database();
            $persona = $db->queryOne("
                SELECT
                    p.id,
                    p.numero_empleado,
                    p.codigo_contpac,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.apellidom,
                    p.curp,
                    p.correo,
                    COALESCE(p.estatus, '') AS estatus,
                    DATE_FORMAT(bp.fecha_baja, '%Y-%m-%d') AS fecha_baja,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo
                FROM estado_cuenta.persona p
                LEFT JOIN (
                    SELECT id_persona, MAX(id) AS id_ultima_baja
                    FROM estado_cuenta.baja_persona
                    GROUP BY id_persona
                ) ub ON ub.id_persona = p.id
                LEFT JOIN estado_cuenta.baja_persona bp ON bp.id = ub.id_ultima_baja
                WHERE p.id = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if (!$persona) {
                return self::resultado(false, 'Persona no encontrada.', null);
            }

            return self::resultado(true, 'Persona encontrada.', $persona);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener persona para importacion.', null, $e->getMessage());
        }
    }

    public static function getCatalogoDocumentosImportacion()
    {
        try {
            $db = new Database();
            self::asegurarDocumentoOtros($db);
            $documentos = $db->queryAll("
                SELECT id, clave, nombre
                FROM estado_cuenta.documento
                WHERE activo = 1
                  AND id NOT IN (" . implode(',', self::DOCUMENTOS_EXCLUIDOS_RRHH) . ")
                ORDER BY id ASC
            ");

            return self::resultado(true, 'Catálogo de documentos encontrado.', $documentos ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener catálogo de documentos.', [], $e->getMessage());
        }
    }

    public static function getDocumentosPersonaIndex(array $idsPersonas)
    {
        try {
            $idsPersonas = array_values(array_unique(array_filter(array_map('intval', $idsPersonas))));
            if (empty($idsPersonas)) {
                return self::resultado(true, 'Sin personas para consultar.', []);
            }

            $params = [];
            $placeholders = [];
            foreach ($idsPersonas as $i => $idPersona) {
                $key = 'id_' . $i;
                $params[$key] = $idPersona;
                $placeholders[] = ':' . $key;
            }

            $db = new Database();
            $rows = $db->queryAll("
                SELECT id, id_persona, id_documento, archivo, fecha_carga
                FROM estado_cuenta.carga_documento_persona
                WHERE id_persona IN (" . implode(',', $placeholders) . ")
            ", $params);

            $index = [];
            foreach ($rows as $row) {
                $idPersona = (int) ($row['id_persona'] ?? 0);
                $idDocumento = (int) ($row['id_documento'] ?? 0);
                if ($idPersona <= 0 || $idDocumento <= 0) {
                    continue;
                }
                if (!isset($index[$idPersona])) {
                    $index[$idPersona] = [];
                }
                if (!isset($index[$idPersona][$idDocumento])) {
                    $index[$idPersona][$idDocumento] = [];
                }
                $index[$idPersona][$idDocumento][] = $row;
            }

            return self::resultado(true, 'Documentos existentes encontrados.', $index);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar documentos existentes.', [], $e->getMessage());
        }
    }

    /**
     * Obtener ID de documento por nombre (usando la BD)
     */
    public static function getIdDocumentoPorNombre($nombreDocumento)
    {
        try {
            $db = new Database();

            // Limpiar el nombre del documento (trim para espacios y caracteres especiales)
            $nombreDocumento = trim($nombreDocumento);
            $nombreDocumento = preg_replace('/\s+/', ' ', $nombreDocumento); // Normalizar espacios múltiples

            // Primero intentar búsqueda exacta (más rápida y precisa)
            $documento = $db->queryOne("
                SELECT id
                FROM estado_cuenta.documento
                WHERE nombre = :nombre
                AND activo = 1
                LIMIT 1
            ", ['nombre' => $nombreDocumento]);

            if ($documento && isset($documento['id'])) {
                return (int)$documento['id'];
            }

            // Si no se encuentra, intentar búsqueda case-insensitive con trim
            $documento = $db->queryOne("
                SELECT id
                FROM estado_cuenta.documento
                WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:nombre))
                AND activo = 1
                LIMIT 1
            ", ['nombre' => $nombreDocumento]);

            if ($documento && isset($documento['id'])) {
                return (int)$documento['id'];
            }

            return null;

        } catch (\Exception $e) {
            error_log("Error en getIdDocumentoPorNombre: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Obtener documentos de una persona por tipo
     */
    public static function getDocumentosPersona($id_persona, $id_documento = null)
    {
        try {
            $db = new Database();

            $query = "
                SELECT
                    cdp.id,
                    cdp.archivo,
                    cdp.id_documento,
                    d.nombre AS documento_nombre,
                    DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM estado_cuenta.carga_documento_persona cdp
                LEFT JOIN estado_cuenta.documento d ON d.id = cdp.id_documento
                WHERE cdp.id_persona = :id_persona
            ";

            $params = ['id_persona' => $id_persona];

            if ($id_documento) {
                $query .= " AND cdp.id_documento = :id_documento";
                $params['id_documento'] = $id_documento;
            }

            $query .= " ORDER BY cdp.fecha_carga DESC";

            $documentos = $db->queryAll($query, $params);

            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    public static function getGestoresPendientesCartaCompromiso(): array
    {
        try {
            self::asegurarDocumentoCartaCompromisoGestor();
            $db = new Database();

            $rows = $db->queryAll("
                SELECT
                    p.id AS id_persona,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.correo,
                    COALESCE(NULLIF(TRIM(p.telefono_uno), ''), NULLIF(TRIM(p.telefono_dos), ''), '') AS telefono,
                    COALESCE(p.estatus, '') AS estatus,
                    DATE_FORMAT(p.fecha_ingreso, '%Y-%m-%d') AS fecha_ingreso,
                    COALESCE(org.puestos, 'Gestor') AS puestos,
                    COALESCE(org.departamentos, 'Sin departamento') AS departamentos,
                    COALESCE(org.areas, 'Sin area') AS areas,
                    COALESCE(org.direcciones, 'Sin direccion') AS direcciones,
                    CASE
                        WHEN pj.id IS NOT NULL THEN TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom))
                        WHEN vj.id IS NOT NULL THEN CONCAT('Vacante #', vj.id, ' - ', COALESCE(pvj.nombre, 'Sin puesto'))
                        ELSE 'Sin jefe asignado'
                    END AS jefe
                FROM estado_cuenta.persona p
                INNER JOIN (
                    SELECT
                        ap.id_persona,
                        GROUP_CONCAT(DISTINCT pp.nombre ORDER BY COALESCE(pp.nivel, 999), pp.nombre SEPARATOR ', ') AS puestos,
                        GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', ') AS departamentos,
                        GROUP_CONCAT(DISTINCT dorg.nombre ORDER BY dorg.nombre SEPARATOR ', ') AS areas,
                        GROUP_CONCAT(DISTINCT dir.nombre ORDER BY dir.nombre SEPARATOR ', ') AS direcciones,
                        MAX(CASE WHEN UPPER(COALESCE(pp.nombre, '')) LIKE '%GESTOR%' THEN 1 ELSE 0 END) AS tiene_gestor
                    FROM estado_cuenta.asigna_puesto ap
                    INNER JOIN estado_cuenta.puesto pp
                        ON pp.id = ap.id_puesto
                       AND COALESCE(pp.activo, 1) = 1
                    LEFT JOIN estado_cuenta.departamento d
                        ON d.id = pp.departamento_id
                    LEFT JOIN estado_cuenta.departamento_organizacional dorg
                        ON dorg.id = d.id_departamento_organizacional
                    LEFT JOIN estado_cuenta.asigna_direcciones ad
                        ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                       AND COALESCE(ad.activo, 1) = 1
                    LEFT JOIN estado_cuenta.direcciones_organizacion dir
                        ON dir.id = ad.id_direccion
                    WHERE COALESCE(ap.activo, 1) = 1
                    GROUP BY ap.id_persona
                ) org
                    ON org.id_persona = p.id
                   AND org.tiene_gestor = 1
                LEFT JOIN (
                    SELECT aj1.id_persona, aj1.id_jefe, aj1.id_vacante_jefe
                    FROM estado_cuenta.asigna_jefe aj1
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS id_max
                        FROM estado_cuenta.asigna_jefe
                        WHERE fecha_fin IS NULL OR fecha_fin >= CURDATE()
                        GROUP BY id_persona
                    ) ult ON ult.id_persona = aj1.id_persona AND ult.id_max = aj1.id
                ) aj ON aj.id_persona = p.id
                LEFT JOIN estado_cuenta.persona pj ON pj.id = aj.id_jefe
                LEFT JOIN estado_cuenta.vacantes_personal vj ON vj.id = aj.id_vacante_jefe
                LEFT JOIN estado_cuenta.puesto pvj ON pvj.id = vj.id_puesto
                WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                  AND NOT EXISTS (
                      SELECT 1
                      FROM estado_cuenta.carga_documento_persona cdp
                      WHERE cdp.id_persona = p.id
                        AND cdp.id_documento = :id_documento
                      LIMIT 1
                  )
                ORDER BY nombre_completo ASC
            ", ['id_documento' => self::DOCUMENTO_CARTA_COMPROMISO_GESTOR]);

            return self::resultado(true, 'Gestores pendientes encontrados.', $rows ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener gestores pendientes.', [], $e->getMessage());
        }
    }

    private static function asegurarTablaSeguimientoCartaCompromisoGestor(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.carta_compromiso_gestor_correo (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_persona INT NOT NULL,
                correo VARCHAR(180) NOT NULL,
                enviado_por INT NULL,
                fecha_envio DATETIME NOT NULL,
                INDEX idx_carta_gestor_persona (id_persona),
                INDEX idx_carta_gestor_fecha (fecha_envio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public static function registrarCorreoCartaCompromisoGestorEnviado(int $idPersona, string $correo, int $enviadoPor = 0): array
    {
        try {
            if ($idPersona <= 0 || trim($correo) === '') {
                return self::resultado(false, 'Datos invalidos para registrar el correo.', null);
            }
            $db = new Database();
            self::asegurarTablaSeguimientoCartaCompromisoGestor($db);
            $db->CRUD(
                "INSERT INTO estado_cuenta.carta_compromiso_gestor_correo
                 (id_persona, correo, enviado_por, fecha_envio)
                 VALUES (:id_persona, :correo, :enviado_por, :fecha_envio)",
                [
                    'id_persona' => $idPersona,
                    'correo' => trim($correo),
                    'enviado_por' => $enviadoPor > 0 ? $enviadoPor : null,
                    'fecha_envio' => self::fechaHoraCdmx(),
                ]
            );
            return self::resultado(true, 'Correo registrado.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo registrar el envio del correo.', null, $e->getMessage());
        }
    }

    public static function getSeguimientoCartaCompromisoGestor(string $estado = 'pendientes'): array
    {
        try {
            self::asegurarDocumentoCartaCompromisoGestor();
            $db = new Database();
            self::asegurarTablaSeguimientoCartaCompromisoGestor($db);

            $rows = $db->queryAll("
                SELECT
                    p.id AS id_persona,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.correo,
                    COALESCE(NULLIF(TRIM(p.telefono_uno), ''), NULLIF(TRIM(p.telefono_dos), ''), '') AS telefono,
                    COALESCE(p.estatus, '') AS estatus,
                    DATE_FORMAT(p.fecha_ingreso, '%Y-%m-%d') AS fecha_ingreso,
                    COALESCE(org.puestos, 'Gestor') AS puestos,
                    COALESCE(org.departamentos, 'Sin departamento') AS departamentos,
                    COALESCE(org.areas, 'Sin area') AS areas,
                    COALESCE(org.direcciones, 'Sin direccion') AS direcciones,
                    CASE
                        WHEN pj.id IS NOT NULL THEN TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom))
                        WHEN vj.id IS NOT NULL THEN CONCAT('Vacante #', vj.id, ' - ', COALESCE(pvj.nombre, 'Sin puesto'))
                        ELSE 'Sin jefe asignado'
                    END AS jefe,
                    doc.archivo AS carta_archivo,
                    DATE_FORMAT(doc.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carta_subida,
                    mail.correo AS correo_envio,
                    DATE_FORMAT(mail.fecha_envio, '%Y-%m-%d %H:%i') AS fecha_correo_enviado,
                    TRIM(CONCAT_WS(' ', pu.nombres, pu.segundo_nombre, pu.apellidop, pu.apellidom)) AS correo_enviado_por
                FROM estado_cuenta.persona p
                INNER JOIN (
                    SELECT
                        ap.id_persona,
                        GROUP_CONCAT(DISTINCT pp.nombre ORDER BY COALESCE(pp.nivel, 999), pp.nombre SEPARATOR ', ') AS puestos,
                        GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', ') AS departamentos,
                        GROUP_CONCAT(DISTINCT dorg.nombre ORDER BY dorg.nombre SEPARATOR ', ') AS areas,
                        GROUP_CONCAT(DISTINCT dir.nombre ORDER BY dir.nombre SEPARATOR ', ') AS direcciones,
                        MAX(CASE WHEN UPPER(COALESCE(pp.nombre, '')) LIKE '%GESTOR%' THEN 1 ELSE 0 END) AS tiene_gestor
                    FROM estado_cuenta.asigna_puesto ap
                    INNER JOIN estado_cuenta.puesto pp
                        ON pp.id = ap.id_puesto
                       AND COALESCE(pp.activo, 1) = 1
                    LEFT JOIN estado_cuenta.departamento d
                        ON d.id = pp.departamento_id
                    LEFT JOIN estado_cuenta.departamento_organizacional dorg
                        ON dorg.id = d.id_departamento_organizacional
                    LEFT JOIN estado_cuenta.asigna_direcciones ad
                        ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                       AND COALESCE(ad.activo, 1) = 1
                    LEFT JOIN estado_cuenta.direcciones_organizacion dir
                        ON dir.id = ad.id_direccion
                    WHERE COALESCE(ap.activo, 1) = 1
                    GROUP BY ap.id_persona
                ) org
                    ON org.id_persona = p.id
                   AND org.tiene_gestor = 1
                LEFT JOIN (
                    SELECT
                        cdp.id_persona,
                        SUBSTRING_INDEX(GROUP_CONCAT(cdp.archivo ORDER BY cdp.fecha_carga DESC, cdp.id DESC), ',', 1) AS archivo,
                        MAX(cdp.fecha_carga) AS fecha_carga
                    FROM estado_cuenta.carga_documento_persona cdp
                    WHERE cdp.id_documento = :id_documento
                    GROUP BY cdp.id_persona
                ) doc ON doc.id_persona = p.id
                LEFT JOIN (
                    SELECT m1.id_persona, m1.correo, m1.fecha_envio, m1.enviado_por
                    FROM estado_cuenta.carta_compromiso_gestor_correo m1
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS id_max
                        FROM estado_cuenta.carta_compromiso_gestor_correo
                        GROUP BY id_persona
                    ) mx ON mx.id_persona = m1.id_persona AND mx.id_max = m1.id
                ) mail ON mail.id_persona = p.id
                LEFT JOIN estado_cuenta.persona pu ON pu.id = mail.enviado_por
                LEFT JOIN (
                    SELECT aj1.id_persona, aj1.id_jefe, aj1.id_vacante_jefe
                    FROM estado_cuenta.asigna_jefe aj1
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS id_max
                        FROM estado_cuenta.asigna_jefe
                        WHERE fecha_fin IS NULL OR fecha_fin >= CURDATE()
                        GROUP BY id_persona
                    ) ult ON ult.id_persona = aj1.id_persona AND ult.id_max = aj1.id
                ) aj ON aj.id_persona = p.id
                LEFT JOIN estado_cuenta.persona pj ON pj.id = aj.id_jefe
                LEFT JOIN estado_cuenta.vacantes_personal vj ON vj.id = aj.id_vacante_jefe
                LEFT JOIN estado_cuenta.puesto pvj ON pvj.id = vj.id_puesto
                WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                ORDER BY nombre_completo ASC
            ", ['id_documento' => self::DOCUMENTO_CARTA_COMPROMISO_GESTOR]);

            $estado = strtolower(trim($estado));
            $permitidos = ['todos', 'pendiente_subir', 'recibida', 'sin_correo_enviado', 'pendientes'];
            if (!in_array($estado, $permitidos, true)) {
                $estado = 'pendientes';
            }

            $filas = [];
            $resumen = [
                'total' => 0,
                'pendiente_subir' => 0,
                'recibida' => 0,
                'sin_correo_enviado' => 0,
                'pendientes' => 0,
            ];
            foreach ($rows ?? [] as $row) {
                $tieneCarta = trim((string)($row['fecha_carta_subida'] ?? '')) !== '';
                $tieneCorreo = trim((string)($row['fecha_correo_enviado'] ?? '')) !== '';
                if ($tieneCarta) {
                    $estadoCarta = 'recibida';
                    $estadoLabel = 'Carta recibida';
                } elseif ($tieneCorreo) {
                    $estadoCarta = 'pendiente_subir';
                    $estadoLabel = 'Pendiente de subir carta';
                } else {
                    $estadoCarta = 'sin_correo_enviado';
                    $estadoLabel = 'Sin correo enviado';
                }
                $row['estado_carta'] = $estadoCarta;
                $row['estado_carta_label'] = $estadoLabel;

                $resumen['total']++;
                $resumen[$estadoCarta]++;
                if ($estadoCarta !== 'recibida') {
                    $resumen['pendientes']++;
                }

                if ($estado === 'todos'
                    || $estado === $estadoCarta
                    || ($estado === 'pendientes' && $estadoCarta !== 'recibida')) {
                    $filas[] = $row;
                }
            }

            return self::resultado(true, 'Seguimiento encontrado.', [
                'rows' => $filas,
                'resumen' => $resumen,
                'filtro' => $estado,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener seguimiento de cartas.', ['rows' => [], 'resumen' => []], $e->getMessage());
        }
    }

    public static function getPersonaGestorCartaCompromiso(int $idPersona): array
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'ID de persona invalido.', null);
            }

            self::asegurarDocumentoCartaCompromisoGestor();
            $db = new Database();
            $row = $db->queryOne("
                SELECT
                    p.id AS id_persona,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.apellidom,
                    p.correo,
                    COALESCE(NULLIF(TRIM(p.telefono_uno), ''), NULLIF(TRIM(p.telefono_dos), ''), '') AS telefono,
                    COALESCE(p.estatus, '') AS estatus,
                    COALESCE(org.puestos, 'Gestor') AS puestos,
                    COALESCE(org.departamentos, 'Sin departamento') AS departamentos,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM estado_cuenta.carga_documento_persona cdp
                            WHERE cdp.id_persona = p.id
                              AND cdp.id_documento = :id_documento
                            LIMIT 1
                        ) THEN 1 ELSE 0
                    END AS carta_subida
                FROM estado_cuenta.persona p
                INNER JOIN (
                    SELECT
                        ap.id_persona,
                        GROUP_CONCAT(DISTINCT pp.nombre ORDER BY COALESCE(pp.nivel, 999), pp.nombre SEPARATOR ', ') AS puestos,
                        GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', ') AS departamentos,
                        MAX(CASE WHEN UPPER(COALESCE(pp.nombre, '')) LIKE '%GESTOR%' THEN 1 ELSE 0 END) AS tiene_gestor
                    FROM estado_cuenta.asigna_puesto ap
                    INNER JOIN estado_cuenta.puesto pp
                        ON pp.id = ap.id_puesto
                       AND COALESCE(pp.activo, 1) = 1
                    LEFT JOIN estado_cuenta.departamento d
                        ON d.id = pp.departamento_id
                    WHERE COALESCE(ap.activo, 1) = 1
                    GROUP BY ap.id_persona
                ) org
                    ON org.id_persona = p.id
                   AND org.tiene_gestor = 1
                WHERE p.id = :id_persona
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                LIMIT 1
            ", [
                'id_persona' => $idPersona,
                'id_documento' => self::DOCUMENTO_CARTA_COMPROMISO_GESTOR,
            ]);

            if (!$row) {
                return self::resultado(false, 'No se encontro un gestor activo con ese ID.', null);
            }

            return self::resultado(true, 'Gestor encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener gestor.', null, $e->getMessage());
        }
    }

    private static function coberturaDocumentoRrhh(int $idDocumento, array $mapaCargados): array
    {
        $info = $mapaCargados[$idDocumento] ?? null;
        if ($info !== null && (int) ($info['total_archivos'] ?? 0) > 0) {
            return ['cargado' => true, 'info' => $info, 'cubierto_por' => null];
        }

        return ['cargado' => false, 'info' => null, 'cubierto_por' => null];
    }

    public static function getResumenDocumentosColaborador($id_persona)
    {
        try {
            $id_persona = (int) $id_persona;
            if ($id_persona <= 0) {
                return self::resultado(false, 'No se pudo identificar al colaborador.', []);
            }

            $db = new Database();
            $persona = $db->queryOne("
                SELECT
                    p.id,
                    p.numero_empleado,
                    p.codigo_contpac,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.correo,
                    COALESCE(p.estatus, '') AS estatus
                FROM estado_cuenta.persona p
                WHERE p.id = :id_persona
                LIMIT 1
            ", ['id_persona' => $id_persona]);

            if (!$persona) {
                return self::resultado(false, 'Colaborador no encontrado.', []);
            }

            $tipos = $db->queryAll("
                SELECT id, nombre, clave, obligatorio
                FROM estado_cuenta.documento
                WHERE activo = 1
                  AND id IN (" . self::idsDocumentosExpedienteRrhh() . ")
                ORDER BY FIELD(id, " . self::idsDocumentosExpedienteRrhh() . ")
            ");

            $idsConsulta = array_values(array_unique(array_merge(
                array_map(static fn($doc) => (int) ($doc['id'] ?? 0), $tipos),
                array_keys(self::DOCUMENTOS_ALIAS_RRHH)
            )));
            $cargados = $db->queryAll("
                SELECT
                    cdp.id_documento,
                    COUNT(*) AS total_archivos,
                    MAX(cdp.fecha_carga) AS ultima_fecha,
                    GROUP_CONCAT(cdp.archivo ORDER BY cdp.fecha_carga DESC SEPARATOR '||') AS archivos
                FROM estado_cuenta.carga_documento_persona cdp
                WHERE cdp.id_persona = :id_persona
                  AND cdp.id_documento IN (" . implode(',', $idsConsulta) . ")
                GROUP BY cdp.id_documento
            ", ['id_persona' => $id_persona]);

            $mapaCargados = [];
            foreach ($cargados as $row) {
                $idDocumentoOriginal = (int) $row['id_documento'];
                $idDocumento = self::DOCUMENTOS_ALIAS_RRHH[$idDocumentoOriginal] ?? $idDocumentoOriginal;
                if (!isset($mapaCargados[$idDocumento])) {
                    $row['id_documento'] = $idDocumento;
                    $mapaCargados[$idDocumento] = $row;
                    continue;
                }

                $mapaCargados[$idDocumento]['total_archivos'] = (int) ($mapaCargados[$idDocumento]['total_archivos'] ?? 0) + (int) ($row['total_archivos'] ?? 0);
                if (strtotime((string) ($row['ultima_fecha'] ?? '')) > strtotime((string) ($mapaCargados[$idDocumento]['ultima_fecha'] ?? ''))) {
                    $mapaCargados[$idDocumento]['ultima_fecha'] = $row['ultima_fecha'];
                }
                $archivosActuales = (string) ($mapaCargados[$idDocumento]['archivos'] ?? '');
                $archivosNuevos = (string) ($row['archivos'] ?? '');
                $mapaCargados[$idDocumento]['archivos'] = trim($archivosActuales . '||' . $archivosNuevos, '|');
            }

            $documentos = [];
            $totalCargados = 0;
            foreach ($tipos as $tipo) {
                $idDocumento = (int) $tipo['id'];
                $cobertura = self::coberturaDocumentoRrhh($idDocumento, $mapaCargados);
                $info = $cobertura['info'];
                $estaCargado = (bool) ($cobertura['cargado'] ?? false);
                $cubiertoPor = (string) ($cobertura['cubierto_por'] ?? '');
                if ($estaCargado) {
                    $totalCargados++;
                }

                $documentos[] = [
                    'id_documento' => $idDocumento,
                    'nombre' => $tipo['nombre'] ?? '',
                    'clave' => $tipo['clave'] ?? '',
                    'obligatorio' => (int) ($tipo['obligatorio'] ?? 0) === 1,
                    'estatus' => $estaCargado ? ($cubiertoPor !== '' ? 'Cubierto' : 'Cargado') : 'Faltante',
                    'cargado' => $estaCargado,
                    'cubierto_por' => $cubiertoPor,
                    'total_archivos' => $estaCargado && $cubiertoPor === '' ? (int) $info['total_archivos'] : 0,
                    'ultima_fecha' => $estaCargado && $cubiertoPor === '' && !empty($info['ultima_fecha'])
                        ? date('Y-m-d H:i', strtotime((string) $info['ultima_fecha']))
                        : null,
                    'archivos' => $estaCargado && $cubiertoPor === '' && !empty($info['archivos'])
                        ? explode('||', (string) $info['archivos'])
                        : [],
                ];
            }

            $totalRequeridos = count($documentos);
            $totalFaltantes = max(0, $totalRequeridos - $totalCargados);
            $porcentaje = $totalRequeridos > 0 ? round(($totalCargados / $totalRequeridos) * 100, 1) : 0;

            return self::resultado(true, 'Resumen documental encontrado.', [
                'persona' => $persona,
                'metricas' => [
                    'total_requeridos' => $totalRequeridos,
                    'total_cargados' => $totalCargados,
                    'total_faltantes' => $totalFaltantes,
                    'porcentaje' => $porcentaje,
                ],
                'documentos' => $documentos,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener resumen documental.', [], $e->getMessage());
        }
    }

    public static function getResumenDocumentosRrhhGlobal()
    {
        try {
            $db = new Database();
            self::asegurarPersonaEsExterno($db);
            self::asegurarPlantillaActivaExpedientes($db);

            $tipos = $db->queryAll("
                SELECT id, nombre, clave, obligatorio
                FROM estado_cuenta.documento
                WHERE activo = 1
                  AND id IN (" . self::idsDocumentosExpedienteRrhh() . ")
                ORDER BY FIELD(id, " . self::idsDocumentosExpedienteRrhh() . ")
            ");

            $personas = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    p.codigo_contpac,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.correo,
                    COALESCE(p.estatus, '') AS estatus,
                    MIN(pla.id_empresa) AS id_empresa,
                    CASE
                        WHEN LOWER(COALESCE(emp.nombre_comercial, '')) LIKE '%furia%'
                            THEN 'furia_moto'
                        ELSE 'maxikash'
                    END AS empresa_clave,
                    COALESCE(NULLIF(emp.nombre_comercial, ''), 'MaxiKash') AS empresa_nombre,
                    GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', ') AS departamentos,
                    GROUP_CONCAT(DISTINCT pp.nombre ORDER BY pp.nombre SEPARATOR ', ') AS puestos
                FROM estado_cuenta.persona p
                INNER JOIN estado_cuenta.rrhh_plantilla_activa pla
                    ON pla.id_persona = p.id
                   AND pla.activo = 1
                LEFT JOIN estado_cuenta.rrhh_empresas emp
                    ON emp.id = pla.id_empresa
                LEFT JOIN estado_cuenta.asigna_puesto ap
                    ON ap.id_persona = p.id
                   AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN estado_cuenta.puesto pp
                    ON pp.id = ap.id_puesto
                LEFT JOIN estado_cuenta.departamento d
                    ON d.id = pp.departamento_id
                WHERE p.estatus = 'Activo'
                  AND COALESCE(p.es_externo, 0) = 0
                GROUP BY p.id, p.numero_empleado, p.codigo_contpac, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom, p.correo, p.estatus, emp.nombre_comercial
                ORDER BY nombre_completo ASC
            ");

            $idsDocumento = array_map(static fn($doc) => (int) ($doc['id'] ?? 0), $tipos);
            $idsDocumento = array_values(array_filter($idsDocumento));
            $idsConsulta = array_values(array_unique(array_merge($idsDocumento, array_keys(self::DOCUMENTOS_ALIAS_RRHH))));
            $cargas = [];
            if (!empty($idsConsulta)) {
                $cargas = $db->queryAll("
                    SELECT
                        cdp.id_persona,
                        cdp.id_documento,
                        COUNT(*) AS total_archivos,
                        MAX(cdp.fecha_carga) AS ultima_fecha
                    FROM estado_cuenta.carga_documento_persona cdp
                    WHERE cdp.id_documento IN (" . implode(',', $idsConsulta) . ")
                    GROUP BY cdp.id_persona, cdp.id_documento
                ");
            }

            $mapaCargas = [];
            foreach ($cargas as $row) {
                $idPersona = (int) ($row['id_persona'] ?? 0);
                $idDocumentoOriginal = (int) ($row['id_documento'] ?? 0);
                $idDocumento = self::DOCUMENTOS_ALIAS_RRHH[$idDocumentoOriginal] ?? $idDocumentoOriginal;
                if ($idPersona <= 0 || $idDocumento <= 0) {
                    continue;
                }
                if (!isset($mapaCargas[$idPersona])) {
                    $mapaCargas[$idPersona] = [];
                }
                if (!isset($mapaCargas[$idPersona][$idDocumento])) {
                    $row['id_documento'] = $idDocumento;
                    $mapaCargas[$idPersona][$idDocumento] = $row;
                    continue;
                }

                $mapaCargas[$idPersona][$idDocumento]['total_archivos'] = (int) ($mapaCargas[$idPersona][$idDocumento]['total_archivos'] ?? 0) + (int) ($row['total_archivos'] ?? 0);
                if (strtotime((string) ($row['ultima_fecha'] ?? '')) > strtotime((string) ($mapaCargas[$idPersona][$idDocumento]['ultima_fecha'] ?? ''))) {
                    $mapaCargas[$idPersona][$idDocumento]['ultima_fecha'] = $row['ultima_fecha'];
                }
            }

            $totalTipos = count($tipos);
            $colaboradores = [];
            $totalCargadosGlobal = 0;
            $colaboradoresCompletos = 0;
            $colaboradoresConFaltantes = 0;
            $colaboradoresSinDocumentos = 0;
            $colaboradoresParciales = 0;

            foreach ($personas as $persona) {
                $idPersona = (int) ($persona['id'] ?? 0);
                $cargadosLocal = 0;
                $documentos = [];
                $faltantes = [];

                foreach ($tipos as $tipo) {
                    $idDocumento = (int) ($tipo['id'] ?? 0);
                    $cobertura = self::coberturaDocumentoRrhh($idDocumento, $mapaCargas[$idPersona] ?? []);
                    $info = $cobertura['info'];
                    $estaCargado = (bool) ($cobertura['cargado'] ?? false);
                    $cubiertoPor = (string) ($cobertura['cubierto_por'] ?? '');
                    if ($estaCargado) {
                        $cargadosLocal++;
                    } else {
                        $faltantes[] = $tipo['nombre'] ?? '';
                    }

                    $documentos[] = [
                        'id_documento' => $idDocumento,
                        'nombre' => $tipo['nombre'] ?? '',
                        'clave' => $tipo['clave'] ?? '',
                        'obligatorio' => (int) ($tipo['obligatorio'] ?? 0) === 1,
                        'estatus' => $estaCargado ? ($cubiertoPor !== '' ? 'Cubierto' : 'Cargado') : 'Faltante',
                        'cargado' => $estaCargado,
                        'cubierto_por' => $cubiertoPor,
                        'total_archivos' => $estaCargado && $cubiertoPor === '' ? (int) ($info['total_archivos'] ?? 0) : 0,
                        'ultima_fecha' => $estaCargado && $cubiertoPor === '' && !empty($info['ultima_fecha'])
                            ? date('Y-m-d H:i', strtotime((string) $info['ultima_fecha']))
                            : null,
                    ];
                }

                $totalCargadosGlobal += $cargadosLocal;
                $faltantesLocal = max(0, $totalTipos - $cargadosLocal);
                $porcentajeLocal = $totalTipos > 0 ? round(($cargadosLocal / $totalTipos) * 100, 1) : 0;
                if ($faltantesLocal > 0) {
                    $colaboradoresConFaltantes++;
                    if ($cargadosLocal === 0) {
                        $colaboradoresSinDocumentos++;
                    } else {
                        $colaboradoresParciales++;
                    }
                } else {
                    $colaboradoresCompletos++;
                }

                $colaboradores[] = [
                    'id_persona' => $idPersona,
                    'numero_empleado' => $persona['numero_empleado'] ?? '',
                    'codigo_contpac' => $persona['codigo_contpac'] ?? '',
                    'nombre_completo' => $persona['nombre_completo'] ?? '',
                    'correo' => $persona['correo'] ?? '',
                    'id_empresa' => (int)($persona['id_empresa'] ?? 0),
                    'empresa_clave' => $persona['empresa_clave'] ?? 'maxikash',
                    'empresa_nombre' => $persona['empresa_nombre'] ?? 'MaxiKash',
                    'departamentos' => $persona['departamentos'] ?: 'Sin departamento',
                    'puestos' => $persona['puestos'] ?: 'Sin puesto',
                    'estatus' => $persona['estatus'] ?? '',
                    'total_requeridos' => $totalTipos,
                    'total_cargados' => $cargadosLocal,
                    'total_faltantes' => $faltantesLocal,
                    'porcentaje_local' => $porcentajeLocal,
                    'faltantes_resumen' => array_slice(array_values(array_filter($faltantes)), 0, 4),
                    'documentos' => $documentos,
                ];
            }

            usort($colaboradores, static function ($a, $b) {
                $pa = (float) ($a['porcentaje_local'] ?? 0);
                $pb = (float) ($b['porcentaje_local'] ?? 0);
                if ($pa !== $pb) {
                    return $pa <=> $pb;
                }
                return strcasecmp((string) ($a['nombre_completo'] ?? ''), (string) ($b['nombre_completo'] ?? ''));
            });

            $totalColaboradores = count($colaboradores);
            $totalRequeridosGlobal = $totalColaboradores * $totalTipos;
            $totalFaltantesGlobal = max(0, $totalRequeridosGlobal - $totalCargadosGlobal);
            $porcentajeGlobal = $totalRequeridosGlobal > 0 ? round(($totalCargadosGlobal / $totalRequeridosGlobal) * 100, 1) : 0;

            return self::resultado(true, 'Resumen documental global encontrado.', [
                'metricas' => [
                    'total_colaboradores' => $totalColaboradores,
                    'total_documentos_catalogo' => $totalTipos,
                    'total_requeridos_global' => $totalRequeridosGlobal,
                    'total_cargados_global' => $totalCargadosGlobal,
                    'total_faltantes_global' => $totalFaltantesGlobal,
                    'porcentaje_global' => $porcentajeGlobal,
                    'colaboradores_completos' => $colaboradoresCompletos,
                    'colaboradores_con_faltantes' => $colaboradoresConFaltantes,
                    'colaboradores_sin_documentos' => $colaboradoresSinDocumentos,
                    'colaboradores_parciales' => $colaboradoresParciales,
                ],
                'colaboradores' => $colaboradores,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener resumen documental global.', [], $e->getMessage());
        }
    }

    /**
     * Devuelve una muestra acotada de la plantilla vigente. La consulta vuelve a
     * validar activo, empresa y externo en servidor antes de cualquier descarga.
     */
    public static function getPersonasMuestraExpedientesRrhh(string $empresa, array $idsPersona = [], bool $aleatorio = true, int $limite = 10)
    {
        try {
            $empresa = in_array($empresa, ['maxikash', 'furia_moto', 'ambas'], true) ? $empresa : 'ambas';
            $idsPersona = array_values(array_unique(array_filter(array_map('intval', $idsPersona))));
            $limite = max(1, min(10, $limite));

            $db = new Database();
            self::asegurarPersonaEsExterno($db);
            self::asegurarPlantillaActivaExpedientes($db);

            $params = [];
            $where = [
                "p.estatus = 'Activo'",
                'COALESCE(p.es_externo, 0) = 0',
            ];
            $empresaTexto = "LOWER(COALESCE(emp.nombre_comercial, ''))";
            if ($empresa === 'furia_moto') {
                $where[] = "$empresaTexto LIKE '%furia%'";
            } elseif ($empresa === 'maxikash') {
                $where[] = "($empresaTexto NOT LIKE '%furia%' OR $empresaTexto = '')";
            }

            if (!empty($idsPersona)) {
                $placeholders = [];
                foreach ($idsPersona as $indice => $idPersona) {
                    $clave = 'persona_' . $indice;
                    $params[$clave] = $idPersona;
                    $placeholders[] = ':' . $clave;
                }
                $where[] = 'p.id IN (' . implode(', ', $placeholders) . ')';
            }

            $orden = $aleatorio && empty($idsPersona) ? 'RAND()' : 'nombre_completo ASC';
            $personas = $db->queryAll("
                SELECT
                    p.id AS id_persona,
                    p.codigo_contpac,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    pla.id_empresa,
                    CASE
                        WHEN $empresaTexto LIKE '%furia%' THEN 'furia_moto'
                        ELSE 'maxikash'
                    END AS empresa_clave,
                    COALESCE(NULLIF(emp.nombre_comercial, ''), 'MaxiKash') AS empresa_nombre
                FROM estado_cuenta.persona p
                INNER JOIN estado_cuenta.rrhh_plantilla_activa pla
                    ON pla.id_persona = p.id
                   AND pla.activo = 1
                LEFT JOIN estado_cuenta.rrhh_empresas emp
                    ON emp.id = pla.id_empresa
                WHERE " . implode(' AND ', $where) . "
                GROUP BY p.id, p.codigo_contpac, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom, pla.id_empresa, emp.nombre_comercial
                ORDER BY $orden
                LIMIT $limite
            ", $params);

            return self::resultado(true, 'Personas de plantilla encontradas.', $personas ?? []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al seleccionar la muestra de expedientes.', [], $e->getMessage());
        }
    }

    /** Obtiene unicamente los documentos que forman parte del expediente RR.HH. base. */
    public static function getDocumentosExpedienteRrhhPorPersonas(array $idsPersona)
    {
        try {
            $idsPersona = array_values(array_unique(array_filter(array_map('intval', $idsPersona))));
            if (empty($idsPersona)) {
                return self::resultado(true, 'No hay personas seleccionadas.', []);
            }

            $params = [];
            $placeholders = [];
            foreach ($idsPersona as $indice => $idPersona) {
                $clave = 'persona_' . $indice;
                $params[$clave] = $idPersona;
                $placeholders[] = ':' . $clave;
            }
            $idsDocumento = array_values(array_unique(array_merge(
                self::DOCUMENTOS_EXPEDIENTE_RRHH,
                array_keys(self::DOCUMENTOS_ALIAS_RRHH)
            )));

            $db = new Database();
            $documentos = $db->queryAll("
                SELECT
                    cdp.id,
                    cdp.id_persona,
                    cdp.id_documento,
                    cdp.archivo,
                    cdp.fecha_carga
                FROM estado_cuenta.carga_documento_persona cdp
                WHERE cdp.id_persona IN (" . implode(', ', $placeholders) . ")
                  AND cdp.id_documento IN (" . implode(',', array_map('intval', $idsDocumento)) . ")
                  AND COALESCE(cdp.archivo, '') <> ''
                ORDER BY cdp.id_persona ASC, cdp.id_documento ASC, cdp.fecha_carga ASC, cdp.id ASC
            ", $params);

            return self::resultado(true, 'Documentos de expediente encontrados.', $documentos ?? []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al consultar documentos de la muestra.', [], $e->getMessage());
        }
    }

    public static function getDocumentosPersonaPorIds(array $ids)
    {
        try {
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
            if (empty($ids)) {
                return self::resultado(false, 'No se seleccionaron documentos.', []);
            }

            $params = [];
            $placeholders = [];
            foreach ($ids as $i => $id) {
                $key = 'id_' . $i;
                $params[$key] = $id;
                $placeholders[] = ':' . $key;
            }

            $db = new Database();
            $documentos = $db->queryAll("
                SELECT
                    cdp.id,
                    cdp.id_persona,
                    cdp.archivo,
                    cdp.id_documento,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.numero_empleado,
                    DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM estado_cuenta.carga_documento_persona cdp
                LEFT JOIN estado_cuenta.persona p ON p.id = cdp.id_persona
                WHERE cdp.id IN (" . implode(',', $placeholders) . ")
                ORDER BY cdp.fecha_carga DESC, cdp.id DESC
            ", $params);

            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    public static function getDocumentoPersonaPorArchivo(string $archivo)
    {
        try {
            $archivo = basename(trim($archivo));
            if ($archivo === '') {
                return self::resultado(false, 'Archivo requerido.', null);
            }

            $db = new Database();
            $documento = $db->queryOne("
                SELECT
                    cdp.id,
                    cdp.id_persona,
                    cdp.archivo,
                    cdp.id_documento,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.numero_empleado,
                    DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM estado_cuenta.carga_documento_persona cdp
                LEFT JOIN estado_cuenta.persona p ON p.id = cdp.id_persona
                WHERE cdp.archivo = :archivo
                ORDER BY cdp.id DESC
                LIMIT 1
            ", ['archivo' => $archivo]);

            if (!$documento) {
                return self::resultado(false, 'Documento no encontrado.', null);
            }

            return self::resultado(true, 'Documento encontrado.', $documento);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al buscar documento.', null, $e->getMessage());
        }
    }

    public static function registrarAuditoriaDocumentoSensible(array $data): void
    {
        try {
            $db = new Database();
            self::asegurarAuditoriaDocumentosSensibles($db);
            $db->CRUD("
                INSERT INTO estado_cuenta.auditoria_documentos_sensibles_rrhh
                    (id_usuario, usuario_nombre, id_persona, persona_nombre, id_documento_carga, id_documento, documento_nombre, archivo, accion, resultado, ip, user_agent, fecha_hora, detalle)
                VALUES
                    (:id_usuario, :usuario_nombre, :id_persona, :persona_nombre, :id_documento_carga, :id_documento, :documento_nombre, :archivo, :accion, :resultado, :ip, :user_agent, :fecha_hora, :detalle)
            ", [
                'id_usuario' => !empty($data['id_usuario']) ? (int) $data['id_usuario'] : null,
                'usuario_nombre' => mb_substr((string) ($data['usuario_nombre'] ?? ''), 0, 191),
                'id_persona' => !empty($data['id_persona']) ? (int) $data['id_persona'] : null,
                'persona_nombre' => mb_substr((string) ($data['persona_nombre'] ?? ''), 0, 191),
                'id_documento_carga' => !empty($data['id_documento_carga']) ? (int) $data['id_documento_carga'] : null,
                'id_documento' => !empty($data['id_documento']) ? (int) $data['id_documento'] : null,
                'documento_nombre' => mb_substr((string) ($data['documento_nombre'] ?? ''), 0, 191),
                'archivo' => mb_substr((string) ($data['archivo'] ?? ''), 0, 255),
                'accion' => mb_substr((string) ($data['accion'] ?? 'ver'), 0, 50),
                'resultado' => mb_substr((string) ($data['resultado'] ?? 'desconocido'), 0, 30),
                'ip' => mb_substr((string) ($data['ip'] ?? ''), 0, 64),
                'user_agent' => mb_substr((string) ($data['user_agent'] ?? ''), 0, 255),
                'fecha_hora' => (string) ($data['fecha_hora'] ?? date('Y-m-d H:i:s')),
                'detalle' => mb_substr((string) ($data['detalle'] ?? ''), 0, 255),
            ]);
        } catch (\Throwable $e) {
            error_log('CapHum::registrarAuditoriaDocumentoSensible -> ' . $e->getMessage());
        }
    }

    private static function asegurarAuditoriaDocumentosSensibles(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.auditoria_documentos_sensibles_rrhh (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NULL,
                usuario_nombre VARCHAR(191) NULL,
                id_persona INT NULL,
                persona_nombre VARCHAR(191) NULL,
                id_documento_carga INT NULL,
                id_documento INT NULL,
                documento_nombre VARCHAR(191) NULL,
                archivo VARCHAR(255) NULL,
                accion VARCHAR(50) NOT NULL,
                resultado VARCHAR(30) NOT NULL,
                ip VARCHAR(64) NULL,
                user_agent VARCHAR(255) NULL,
                fecha_hora DATETIME NOT NULL,
                detalle VARCHAR(255) NULL,
                KEY idx_fecha_hora (fecha_hora),
                KEY idx_usuario (id_usuario),
                KEY idx_documento_carga (id_documento_carga)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        foreach ([
            'usuario_nombre' => 'VARCHAR(191) NULL AFTER id_usuario',
            'persona_nombre' => 'VARCHAR(191) NULL AFTER id_persona',
            'documento_nombre' => 'VARCHAR(191) NULL AFTER id_documento',
        ] as $columna => $definicion) {
            try {
                $db->CRUD("ALTER TABLE estado_cuenta.auditoria_documentos_sensibles_rrhh ADD COLUMN {$columna} {$definicion}");
            } catch (\Throwable $e) {
                // La columna ya existe o el motor no permite ALTER; la auditoria operativa no debe romperse.
            }
        }
    }

    public static function getTotpDocumentoSensible(int $idPersona)
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'Usuario de sesion no valido.', null);
            }

            $db = new Database();
            self::asegurarTotpDocumentosSensibles($db);
            $registro = $db->queryOne("
                SELECT id_persona, secret, confirmado, creado_en, actualizado_en, ultimo_uso_en
                FROM estado_cuenta.rrhh_documentos_sensibles_totp
                WHERE id_persona = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if ($registro && !empty($registro['secret'])) {
                $secretPlano = self::descifrarSecretoTotp((string)$registro['secret']);
                if ($secretPlano === '') {
                    $db->CRUD("
                        DELETE FROM estado_cuenta.rrhh_documentos_sensibles_totp
                        WHERE id_persona = :id_persona
                    ", ['id_persona' => $idPersona]);
                    return self::resultado(true, 'Segundo paso reiniciado por clave ilegible.', null);
                }
                if (!self::esSecretoTotpCifrado((string)$registro['secret'])) {
                    $db->CRUD("
                        UPDATE estado_cuenta.rrhh_documentos_sensibles_totp
                        SET secret = :secret,
                            actualizado_en = NOW()
                        WHERE id_persona = :id_persona
                    ", [
                        'secret' => self::cifrarSecretoTotp($secretPlano),
                        'id_persona' => $idPersona,
                    ]);
                }
                $registro['secret'] = $secretPlano;
            }

            return self::resultado(true, 'Configuracion TOTP consultada.', $registro ?: null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar configuracion TOTP.', null, $e->getMessage());
        }
    }

    public static function guardarTotpDocumentoSensible(int $idPersona, string $secret, bool $confirmado = false)
    {
        try {
            if ($idPersona <= 0 || trim($secret) === '') {
                return self::resultado(false, 'Datos TOTP incompletos.', null);
            }

            $db = new Database();
            self::asegurarTotpDocumentosSensibles($db);
            $secretCifrado = self::cifrarSecretoTotp(trim($secret));
            $db->CRUD("
                INSERT INTO estado_cuenta.rrhh_documentos_sensibles_totp
                    (id_persona, secret, confirmado, creado_en, actualizado_en)
                VALUES
                    (:id_persona, :secret, :confirmado, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    secret = VALUES(secret),
                    confirmado = VALUES(confirmado),
                    actualizado_en = NOW()
            ", [
                'id_persona' => $idPersona,
                'secret' => $secretCifrado,
                'confirmado' => $confirmado ? 1 : 0,
            ]);

            return self::resultado(true, 'Configuracion TOTP guardada.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar configuracion TOTP.', null, $e->getMessage());
        }
    }

    public static function confirmarTotpDocumentoSensible(int $idPersona)
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'Usuario de sesion no valido.', null);
            }

            $db = new Database();
            self::asegurarTotpDocumentosSensibles($db);
            $db->CRUD("
                UPDATE estado_cuenta.rrhh_documentos_sensibles_totp
                SET confirmado = 1,
                    actualizado_en = NOW(),
                    ultimo_uso_en = NOW()
                WHERE id_persona = :id_persona
            ", ['id_persona' => $idPersona]);

            return self::resultado(true, 'Segundo paso confirmado.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al confirmar segundo paso.', null, $e->getMessage());
        }
    }

    public static function resetTotpDocumentoSensible(int $idPersona)
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'Usuario no valido.', null);
            }

            $db = new Database();
            self::asegurarTotpDocumentosSensibles($db);
            $db->CRUD("
                DELETE FROM estado_cuenta.rrhh_documentos_sensibles_totp
                WHERE id_persona = :id_persona
            ", ['id_persona' => $idPersona]);

            return self::resultado(true, 'Segundo paso reiniciado. El usuario debera escanear un nuevo QR.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al reiniciar segundo paso.', null, $e->getMessage());
        }
    }

    public static function getSubdirectoresRecursosHumanos(): array
    {
        try {
            $db = new Database();
            $rows = $db->queryAll("
                SELECT DISTINCT
                    p.id,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN estado_cuenta.asigna_puesto ap ON ap.id_persona = p.id AND ap.activo = 1
                LEFT JOIN estado_cuenta.puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN estado_cuenta.departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                LEFT JOIN estado_cuenta.departamento_organizacional dorg ON dorg.id = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                WHERE p.id > 0
                  AND UPPER(COALESCE(NULLIF(TRIM(p.estatus), ''), 'ACTIVO')) NOT IN ('BAJA', 'INACTIVO')
                  AND (
                        LOWER(COALESCE(pdr.puesto_texto, '')) LIKE '%subdirector%'
                        OR LOWER(COALESCE(pu.nombre, '')) LIKE '%subdirector%'
                  )
                  AND (
                        LOWER(COALESCE(pdr.area_texto, '')) LIKE '%recursos%humanos%'
                        OR LOWER(COALESCE(pdr.departamento_texto, '')) LIKE '%recursos%humanos%'
                        OR LOWER(COALESCE(dep.nombre, '')) LIKE '%recursos%humanos%'
                        OR LOWER(COALESCE(dorg.nombre, '')) LIKE '%recursos%humanos%'
                  )
                ORDER BY nombre_completo ASC
            ");

            return array_values(array_unique(array_map('intval', array_column($rows ?: [], 'id'))));
        } catch (\Throwable $e) {
            error_log('CapHum::getSubdirectoresRecursosHumanos -> ' . $e->getMessage());
            return [];
        }
    }

    public static function getSalarioSensiblePersona(int $idPersona)
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'Persona no valida.', null);
            }

            $db = new Database();
            self::asegurarSalariosSensiblesRrhh($db);
            $registro = $db->queryOne("
                SELECT id_persona, salario_cifrado, moneda, creado_en, actualizado_en, id_usuario_actualizacion
                FROM estado_cuenta.rrhh_salarios_sensibles
                WHERE id_persona = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if (!$registro) {
                return self::resultado(true, 'Salario sensible consultado.', [
                    'tiene_salario' => false,
                    'salario' => '',
                    'moneda' => 'MXN',
                    'actualizado_en' => null,
                ]);
            }

            $salarioPlano = self::descifrarValorSensibleRrhh((string)($registro['salario_cifrado'] ?? ''));
            if ($salarioPlano === '') {
                return self::resultado(false, 'No se pudo descifrar el salario sensible.', null);
            }

            return self::resultado(true, 'Salario sensible consultado.', [
                'tiene_salario' => true,
                'salario' => $salarioPlano,
                'moneda' => $registro['moneda'] ?: 'MXN',
                'actualizado_en' => $registro['actualizado_en'] ?? null,
                'id_usuario_actualizacion' => $registro['id_usuario_actualizacion'] ?? null,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar salario sensible.', null, $e->getMessage());
        }
    }

    public static function personaTieneSalarioSensible(int $idPersona): bool
    {
        try {
            if ($idPersona <= 0) {
                return false;
            }
            $db = new Database();
            self::asegurarSalariosSensiblesRrhh($db);
            $row = $db->queryOne("
                SELECT 1 AS ok
                FROM estado_cuenta.rrhh_salarios_sensibles
                WHERE id_persona = :id_persona
                LIMIT 1
            ", ['id_persona' => $idPersona]);
            return !empty($row);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function guardarSalarioSensiblePersona(int $idPersona, $salario, int $idUsuario)
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'Persona no valida.', null);
            }

            $salarioNormalizado = self::normalizarSalarioSensible($salario);
            if ($salarioNormalizado === false) {
                return self::resultado(false, 'Captura un salario valido.', null);
            }

            $db = new Database();
            self::asegurarSalariosSensiblesRrhh($db);

            if ($salarioNormalizado === null) {
                $db->CRUD("
                    DELETE FROM estado_cuenta.rrhh_salarios_sensibles
                    WHERE id_persona = :id_persona
                ", ['id_persona' => $idPersona]);
                return self::resultado(true, 'Salario sensible eliminado.', [
                    'tiene_salario' => false,
                    'salario' => '',
                    'moneda' => 'MXN',
                ]);
            }

            $db->CRUD("
                INSERT INTO estado_cuenta.rrhh_salarios_sensibles
                    (id_persona, salario_cifrado, moneda, creado_en, actualizado_en, id_usuario_actualizacion)
                VALUES
                    (:id_persona, :salario_cifrado, 'MXN', NOW(), NOW(), :id_usuario)
                ON DUPLICATE KEY UPDATE
                    salario_cifrado = VALUES(salario_cifrado),
                    moneda = VALUES(moneda),
                    actualizado_en = NOW(),
                    id_usuario_actualizacion = VALUES(id_usuario_actualizacion)
            ", [
                'id_persona' => $idPersona,
                'salario_cifrado' => self::cifrarValorSensibleRrhh($salarioNormalizado),
                'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
            ]);

            return self::resultado(true, 'Salario sensible guardado.', [
                'tiene_salario' => true,
                'salario' => $salarioNormalizado,
                'moneda' => 'MXN',
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar salario sensible.', null, $e->getMessage());
        }
    }

    public static function importarSalariosSensiblesPorCurp(array $filas, int $idUsuario): array
    {
        try {
            $db = new Database();
            self::asegurarSalariosSensiblesRrhh($db);

            $resumen = [
                'total' => 0,
                'actualizados' => 0,
                'omitidos' => 0,
                'errores' => [],
            ];

            $db->beginTransaction();
            foreach ($filas as $fila) {
                $numeroFila = (int)($fila['fila'] ?? 0);
                $curp = strtoupper(preg_replace('/\s+/', '', (string)($fila['curp'] ?? '')));
                $salario = $fila['salario'] ?? null;
                $resumen['total']++;

                if ($curp === '') {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = ['fila' => $numeroFila, 'curp' => '', 'motivo' => 'CURP vacia.'];
                    continue;
                }

                $salarioNormalizado = self::normalizarSalarioSensible($salario);
                if ($salarioNormalizado === false || $salarioNormalizado === null) {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = ['fila' => $numeroFila, 'curp' => $curp, 'motivo' => 'Sueldo invalido o vacio.'];
                    continue;
                }

                $personas = $db->queryAll("
                    SELECT id,
                           TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre
                    FROM estado_cuenta.persona
                    WHERE UPPER(TRIM(curp)) = :curp
                ", ['curp' => $curp]);

                if (count($personas) === 0) {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = ['fila' => $numeroFila, 'curp' => $curp, 'motivo' => 'No se encontro colaborador con esa CURP.'];
                    continue;
                }

                if (count($personas) > 1) {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = ['fila' => $numeroFila, 'curp' => $curp, 'motivo' => 'CURP repetida en mas de un colaborador.'];
                    continue;
                }

                $idPersona = (int)$personas[0]['id'];
                $db->CRUD("
                    INSERT INTO estado_cuenta.rrhh_salarios_sensibles
                        (id_persona, salario_cifrado, moneda, creado_en, actualizado_en, id_usuario_actualizacion)
                    VALUES
                        (:id_persona, :salario_cifrado, 'MXN', NOW(), NOW(), :id_usuario)
                    ON DUPLICATE KEY UPDATE
                        salario_cifrado = VALUES(salario_cifrado),
                        moneda = VALUES(moneda),
                        actualizado_en = NOW(),
                        id_usuario_actualizacion = VALUES(id_usuario_actualizacion)
                ", [
                    'id_persona' => $idPersona,
                    'salario_cifrado' => self::cifrarValorSensibleRrhh($salarioNormalizado),
                    'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
                ]);

                $db->CRUD("
                    INSERT INTO estado_cuenta.auditoria_salarios_sensibles_rrhh
                        (id_usuario, usuario_nombre, id_persona, persona_nombre, accion, resultado, ip, user_agent, detalle, fecha_hora)
                    VALUES
                        (:id_usuario, :usuario_nombre, :id_persona, :persona_nombre, :accion, :resultado, :ip, :user_agent, :detalle, :fecha_hora)
                ", [
                    'id_usuario' => $idUsuario,
                    'usuario_nombre' => '',
                    'id_persona' => $idPersona,
                    'persona_nombre' => (string)($personas[0]['nombre'] ?? ''),
                    'accion' => 'importar_excel',
                    'resultado' => 'autorizado',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                    'detalle' => 'Salario actualizado por carga masiva de Excel.',
                    'fecha_hora' => date('Y-m-d H:i:s'),
                ]);

                $resumen['actualizados']++;
            }

            $db->commit();
            return self::resultado(true, 'Carga de sueldos finalizada.', $resumen);
        } catch (\Throwable $e) {
            if (isset($db) && $db instanceof Database && $db->inTransaction()) {
                $db->rollback();
            }
            return self::resultado(false, 'No se pudo importar el Excel de sueldos.', null, $e->getMessage());
        }
    }

    public static function importarDatosRrhhPorCurp(array $filas, int $idUsuario, bool $aplicar = false): array
    {
        $texto = static function ($valor, int $max = 255): ?string {
            $valor = trim((string)($valor ?? ''));
            if ($valor === '') {
                return null;
            }
            $invalidos = ['N/A', 'NA', 'S/N', 'SN', 'SIN DATO', 'SIN DATOS', 'PENDIENTE', 'PEND'];
            if (in_array(strtoupper($valor), $invalidos, true)) {
                return null;
            }
            return function_exists('mb_substr') ? mb_substr($valor, 0, $max) : substr($valor, 0, $max);
        };
        $codigoContpac = static function ($valor) use ($texto): ?string {
            $valor = $texto($valor, 40);
            if ($valor === null) {
                return null;
            }
            $valor = preg_replace('/\.0$/', '', $valor);
            if (preg_match('/^\d+$/', $valor)) {
                $valor = ltrim($valor, '0');
                return $valor === '' ? '0' : $valor;
            }
            return $valor;
        };
        $curpLimpia = static function ($valor): string {
            return strtoupper(preg_replace('/\s+/', '', (string)($valor ?? '')));
        };
        $upperSinEspacios = static function ($valor, int $max = 30) use ($texto): ?string {
            $valor = $texto($valor, $max);
            return $valor === null ? null : strtoupper(preg_replace('/\s+/', '', $valor));
        };
        $numero = static function ($valor, int $max = 30) use ($texto): ?string {
            $valor = $texto($valor, $max);
            if ($valor === null) {
                return null;
            }
            $solo = preg_replace('/[^0-9]+/', '', $valor);
            return $solo !== '' ? substr($solo, 0, $max) : $valor;
        };
        $decimal = static function ($valor): ?float {
            $valor = trim(str_replace([',', '$', ' '], ['', '', ''], (string)($valor ?? '')));
            return $valor !== '' && is_numeric($valor) ? (float)$valor : null;
        };
        $fecha = static function ($valor) use ($texto): ?string {
            if ($valor instanceof \DateTimeInterface) {
                return $valor->format('Y-m-d');
            }
            $valorTexto = $texto($valor, 40);
            if ($valorTexto === null) {
                return null;
            }
            if (is_numeric($valorTexto) && class_exists('\PhpOffice\PhpSpreadsheet\Shared\Date')) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$valorTexto)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return $valorTexto;
                }
            }
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})$/', $valorTexto, $m)) {
                $anio = (int)$m[3];
                $anio += $anio >= 30 ? 1900 : 2000;
                if (checkdate((int)$m[2], (int)$m[1], $anio)) {
                    return sprintf('%04d-%02d-%02d', $anio, (int)$m[2], (int)$m[1]);
                }
            }
            foreach (['!d/m/Y', '!d-m-Y', '!Y-m-d', '!d/m/y', '!d-m-y'] as $formato) {
                $dt = \DateTime::createFromFormat($formato, $valorTexto);
                if ($dt instanceof \DateTimeInterface) {
                    $errores = \DateTime::getLastErrors();
                    if (empty($errores['warning_count']) && empty($errores['error_count'])) {
                        return $dt->format('Y-m-d');
                    }
                }
            }
            $ts = strtotime($valorTexto);
            return $ts ? date('Y-m-d', $ts) : $valorTexto;
        };
        $registrarCambio = static function (array &$resumen, array $persona, string $tabla, string $campo, $antes, $despues): void {
            $antesTexto = $antes === null ? '' : (string)$antes;
            $desTexto = $despues === null ? '' : (string)$despues;
            if ($antesTexto === $desTexto || $desTexto === '') {
                return;
            }
            $resumen['campos'][$campo] = (int)($resumen['campos'][$campo] ?? 0) + 1;
            if (count($resumen['cambios_preview']) < 120) {
                $resumen['cambios_preview'][] = [
                    'persona_id' => (int)($persona['id'] ?? 0),
                    'persona' => (string)($persona['nombre'] ?? ''),
                    'curp' => (string)($persona['curp'] ?? ''),
                    'tabla' => $tabla,
                    'campo' => $campo,
                    'antes' => $antesTexto,
                    'despues' => $desTexto,
                ];
            }
        };

        try {
            $db = new Database();
            $resumen = [
                'total' => 0,
                'encontrados' => 0,
                'actualizables' => 0,
                'actualizados' => 0,
                'sin_cambios' => 0,
                'omitidos' => 0,
                'campos' => [],
                'cambios_preview' => [],
                'errores' => [],
            ];

            $curpsSolicitadas = [];
            $estatusPorCurp = [];
            $curpsEstatusConflictivo = [];
            foreach ($filas as $filaCurp) {
                $curpTmp = $curpLimpia($filaCurp['curp'] ?? '');
                if ($curpTmp !== '') {
                    $curpsSolicitadas[$curpTmp] = true;
                    $estatusFila = trim((string)($filaCurp['estatus_importacion'] ?? ''));
                    if (in_array($estatusFila, ['Activo', 'Baja'], true)) {
                        if (isset($estatusPorCurp[$curpTmp]) && $estatusPorCurp[$curpTmp] !== $estatusFila) {
                            $curpsEstatusConflictivo[$curpTmp] = true;
                        } else {
                            $estatusPorCurp[$curpTmp] = $estatusFila;
                        }
                    }
                }
            }
            $curpsSolicitadas = array_keys($curpsSolicitadas);
            $personasPorCurp = [];
            $curpsDuplicadasDb = [];
            $rrhhPorPersona = [];
            $cuentasPorPersona = [];

            foreach (array_chunk($curpsSolicitadas, 500) as $chunkIdx => $chunkCurps) {
                $params = [];
                $placeholders = [];
                foreach ($chunkCurps as $idx => $curpBuscar) {
                    $key = 'curp_' . $chunkIdx . '_' . $idx;
                    $params[$key] = $curpBuscar;
                    $placeholders[] = ':' . $key;
                }
                if (!$placeholders) {
                    continue;
                }
                $personas = $db->queryAll("
                    SELECT
                        id,
                        curp,
                        estatus,
                        codigo_contpac,
                        rfc,
                        correo,
                        telefono_uno,
                        codigo_postal,
                        domicilio_calle_texto,
                        TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre
                    FROM estado_cuenta.persona
                    WHERE UPPER(REPLACE(TRIM(curp), ' ', '')) IN (" . implode(',', $placeholders) . ")
                ", $params) ?: [];
                foreach ($personas as $personaEncontrada) {
                    $curpDb = $curpLimpia($personaEncontrada['curp'] ?? '');
                    if ($curpDb === '') {
                        continue;
                    }
                    if (isset($personasPorCurp[$curpDb])) {
                        $curpsDuplicadasDb[$curpDb] = true;
                    }
                    $personasPorCurp[$curpDb][] = $personaEncontrada;
                }
            }

            $idsPersonaEncontrados = [];
            foreach ($personasPorCurp as $listaPersonas) {
                foreach ($listaPersonas as $personaEncontrada) {
                    $idTmp = (int)($personaEncontrada['id'] ?? 0);
                    if ($idTmp > 0) {
                        $idsPersonaEncontrados[$idTmp] = true;
                    }
                }
            }
            $idsPersonaEncontrados = array_keys($idsPersonaEncontrados);

            foreach (array_chunk($idsPersonaEncontrados, 500) as $chunkIdx => $chunkIds) {
                $params = [];
                $placeholders = [];
                foreach ($chunkIds as $idx => $idTmp) {
                    $key = 'id_' . $chunkIdx . '_' . $idx;
                    $params[$key] = (int)$idTmp;
                    $placeholders[] = ':' . $key;
                }
                if (!$placeholders) {
                    continue;
                }
                $rrhhFilas = $db->queryAll("
                    SELECT *
                    FROM estado_cuenta.persona_datos_rrhh
                    WHERE id_persona IN (" . implode(',', $placeholders) . ")
                ", $params) ?: [];
                foreach ($rrhhFilas as $rrhhFila) {
                    $rrhhPorPersona[(int)$rrhhFila['id_persona']] = $rrhhFila;
                }

                $cuentasFilas = $db->queryAll("
                    SELECT pcb.id, pcb.id_persona, pcb.clabe, pcb.numero_cuenta, pcb.nombre_banco
                    FROM estado_cuenta.persona_cuenta_bancaria pcb
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS id
                        FROM estado_cuenta.persona_cuenta_bancaria
                        WHERE COALESCE(estatus, 'Activo') = 'Activo'
                          AND id_persona IN (" . implode(',', $placeholders) . ")
                        GROUP BY id_persona
                    ) ult ON ult.id = pcb.id
                ", $params) ?: [];
                foreach ($cuentasFilas as $cuentaFila) {
                    $cuentasPorPersona[(int)$cuentaFila['id_persona']] = $cuentaFila;
                }
            }

            if ($aplicar) {
                $db->beginTransaction();
            }

            foreach ($filas as $fila) {
                $resumen['total']++;
                $numeroFila = (int)($fila['fila'] ?? 0);
                $hoja = trim((string)($fila['hoja'] ?? ''));
                $curp = $curpLimpia($fila['curp'] ?? '');
                if ($curp === '') {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = ['fila' => $numeroFila, 'hoja' => $hoja, 'curp' => '', 'motivo' => 'CURP vacia.'];
                    continue;
                }

                if (isset($curpsEstatusConflictivo[$curp])) {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = [
                        'fila' => $numeroFila,
                        'hoja' => $hoja,
                        'curp' => $curp,
                        'motivo' => 'La CURP aparece en hojas de Activos y Bajas con estatus contradictorios.'
                    ];
                    continue;
                }

                $personas = $personasPorCurp[$curp] ?? [];

                if (count($personas) === 0) {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = ['fila' => $numeroFila, 'hoja' => $hoja, 'curp' => $curp, 'motivo' => 'No se encontro colaborador con esa CURP.'];
                    continue;
                }
                if (count($personas) > 1 || isset($curpsDuplicadasDb[$curp])) {
                    $resumen['omitidos']++;
                    $resumen['errores'][] = ['fila' => $numeroFila, 'hoja' => $hoja, 'curp' => $curp, 'motivo' => 'CURP repetida en mas de un colaborador.'];
                    continue;
                }

                $persona = $personas[0];
                $idPersona = (int)$persona['id'];
                $resumen['encontrados']++;

                $personaDatos = is_array($fila['persona'] ?? null) ? $fila['persona'] : [];
                $rrhhDatos = is_array($fila['rrhh'] ?? null) ? $fila['rrhh'] : [];
                $bancoDatos = is_array($fila['cuenta_bancaria'] ?? null) ? $fila['cuenta_bancaria'] : [];

                $personaUpdates = [
                    'codigo_contpac' => $codigoContpac($personaDatos['codigo_contpac'] ?? null),
                    'rfc' => $upperSinEspacios($personaDatos['rfc'] ?? null, 20),
                    'correo' => $texto($personaDatos['correo'] ?? null, 160),
                    'telefono_uno' => $numero($personaDatos['telefono_uno'] ?? null, 30),
                    'codigo_postal' => $numero($personaDatos['codigo_postal'] ?? null, 12),
                    'domicilio_calle_texto' => $texto($personaDatos['domicilio_calle_texto'] ?? null, 500),
                ];
                $estatusImportacion = $estatusPorCurp[$curp] ?? null;
                if (in_array($estatusImportacion, ['Activo', 'Baja'], true)) {
                    $personaUpdates['estatus'] = $estatusImportacion;
                }
                $personaUpdates = array_filter($personaUpdates, static fn($v) => $v !== null && $v !== '');

                $rrhhActual = $rrhhPorPersona[$idPersona] ?? [];

                $rrhhUpdates = [
                    'registro_patronal' => $texto($rrhhDatos['registro_patronal'] ?? null, 120),
                    'codigo_contpaq' => $codigoContpac($rrhhDatos['codigo_contpaq'] ?? $personaUpdates['codigo_contpac'] ?? null),
                    'fecha_imss_alta' => $fecha($rrhhDatos['fecha_imss_alta'] ?? null),
                    'puesto_texto' => $texto($rrhhDatos['puesto_texto'] ?? null, 180),
                    'departamento_texto' => $texto($rrhhDatos['departamento_texto'] ?? null, 180),
                    'area_texto' => $texto($rrhhDatos['area_texto'] ?? null, 180),
                    'direccion_organizacional' => $texto($rrhhDatos['direccion_organizacional'] ?? null, 180),
                    'ubicacion_laboral' => $texto($rrhhDatos['ubicacion_laboral'] ?? null, 180),
                    'municipio_laboral' => $texto($rrhhDatos['municipio_laboral'] ?? null, 180),
                    'jefe_directo_texto' => $texto($rrhhDatos['jefe_directo_texto'] ?? null, 220),
                    'sueldo_neto' => $decimal($rrhhDatos['sueldo_neto'] ?? null),
                    'sueldo_quincenal' => $decimal($rrhhDatos['sueldo_quincenal'] ?? null),
                    'sueldo_bruto' => $decimal($rrhhDatos['sueldo_bruto'] ?? null),
                    'salario_diario' => $decimal($rrhhDatos['salario_diario'] ?? null),
                    'sbc' => $decimal($rrhhDatos['sbc'] ?? null),
                    'rfc' => $upperSinEspacios($rrhhDatos['rfc'] ?? $personaUpdates['rfc'] ?? null, 20),
                    'nss' => $numero($rrhhDatos['nss'] ?? null, 20),
                    'entidad_federativa_rfc' => $texto($rrhhDatos['entidad_federativa_rfc'] ?? null, 120),
                    'anio' => $numero($rrhhDatos['anio'] ?? null, 4),
                    'mes' => $numero($rrhhDatos['mes'] ?? null, 2),
                    'dia' => $numero($rrhhDatos['dia'] ?? null, 2),
                    'fecha_nacimiento' => $fecha($rrhhDatos['fecha_nacimiento'] ?? null),
                    'sexo' => $texto($rrhhDatos['sexo'] ?? null, 20),
                    'tipo_sangre' => $texto($rrhhDatos['tipo_sangre'] ?? null, 20),
                    'alergias' => $texto($rrhhDatos['alergias'] ?? null, 5000),
                    'enfermedades_cronicas' => $texto($rrhhDatos['enfermedades_cronicas'] ?? null, 5000),
                    'enfermedades_hereditarias' => $texto($rrhhDatos['enfermedades_hereditarias'] ?? null, 5000),
                    'medicamentos_actuales' => $texto($rrhhDatos['medicamentos_actuales'] ?? null, 5000),
                    'discapacidad_condicion' => $texto($rrhhDatos['discapacidad_condicion'] ?? null, 5000),
                    'observaciones_medicas' => $texto($rrhhDatos['observaciones_medicas'] ?? null, 5000),
                    'observaciones' => $texto($rrhhDatos['observaciones'] ?? null, 5000),
                ];
                $rrhhUpdates = array_filter($rrhhUpdates, static fn($v) => $v !== null && $v !== '');

                $cuentaActual = $cuentasPorPersona[$idPersona] ?? [];
                $cuentaUpdates = [
                    'clabe' => $numero($bancoDatos['clabe'] ?? null, 30),
                    'numero_cuenta' => $numero($bancoDatos['numero_cuenta'] ?? null, 40),
                    'nombre_banco' => $texto($bancoDatos['nombre_banco'] ?? null, 120),
                ];
                $cuentaUpdates = array_filter($cuentaUpdates, static fn($v) => $v !== null && $v !== '');

                $hayCambios = false;
                foreach ($personaUpdates as $campo => $valor) {
                    if ((string)($persona[$campo] ?? '') !== (string)$valor) {
                        $hayCambios = true;
                        $registrarCambio($resumen, $persona, 'persona', $campo, $persona[$campo] ?? '', $valor);
                    }
                }
                foreach ($rrhhUpdates as $campo => $valor) {
                    if ((string)($rrhhActual[$campo] ?? '') !== (string)$valor) {
                        $hayCambios = true;
                        $registrarCambio($resumen, $persona, 'persona_datos_rrhh', $campo, $rrhhActual[$campo] ?? '', $valor);
                    }
                }
                foreach ($cuentaUpdates as $campo => $valor) {
                    if ((string)($cuentaActual[$campo] ?? '') !== (string)$valor) {
                        $hayCambios = true;
                        $registrarCambio($resumen, $persona, 'persona_cuenta_bancaria', $campo, $cuentaActual[$campo] ?? '', $valor);
                    }
                }

                if (!$hayCambios) {
                    $resumen['sin_cambios']++;
                    continue;
                }
                $resumen['actualizables']++;

                if (!$aplicar) {
                    continue;
                }

                if ($personaUpdates) {
                    $sets = [];
                    $params = ['id_persona' => $idPersona];
                    foreach ($personaUpdates as $campo => $valor) {
                        if ((string)($persona[$campo] ?? '') === (string)$valor) {
                            continue;
                        }
                        $sets[] = "{$campo} = :p_{$campo}";
                        $params["p_{$campo}"] = $valor;
                    }
                    if ($sets) {
                        $db->CRUD("UPDATE estado_cuenta.persona SET " . implode(', ', $sets) . " WHERE id = :id_persona", $params);
                    }
                }

                if ($rrhhUpdates) {
                    $columnas = ['id_persona'];
                    $valores = [':id_persona'];
                    $updates = [];
                    $params = ['id_persona' => $idPersona];
                    foreach ($rrhhUpdates as $campo => $valor) {
                        $columnas[] = $campo;
                        $valores[] = ":r_{$campo}";
                        $updates[] = "{$campo} = VALUES({$campo})";
                        $params["r_{$campo}"] = $valor;
                    }
                    $db->CRUD("
                        INSERT INTO estado_cuenta.persona_datos_rrhh
                            (" . implode(', ', $columnas) . ")
                        VALUES
                            (" . implode(', ', $valores) . ")
                        ON DUPLICATE KEY UPDATE " . implode(', ', $updates) . "
                    ", $params);
                }

                if ($cuentaUpdates) {
                    if (!empty($cuentaActual['id'])) {
                        $sets = [];
                        $params = ['id' => (int)$cuentaActual['id']];
                        foreach ($cuentaUpdates as $campo => $valor) {
                            if ((string)($cuentaActual[$campo] ?? '') === (string)$valor) {
                                continue;
                            }
                            $sets[] = "{$campo} = :b_{$campo}";
                            $params["b_{$campo}"] = $valor;
                        }
                        if ($sets) {
                            $db->CRUD("UPDATE estado_cuenta.persona_cuenta_bancaria SET " . implode(', ', $sets) . " WHERE id = :id", $params);
                        }
                    } else {
                        $db->CRUD("
                            INSERT INTO estado_cuenta.persona_cuenta_bancaria
                                (id_persona, clabe, numero_cuenta, nombre_banco, estatus)
                            VALUES
                                (:id_persona, :clabe, :numero_cuenta, :nombre_banco, 'Activo')
                        ", [
                            'id_persona' => $idPersona,
                            'clabe' => $cuentaUpdates['clabe'] ?? null,
                            'numero_cuenta' => $cuentaUpdates['numero_cuenta'] ?? null,
                            'nombre_banco' => $cuentaUpdates['nombre_banco'] ?? null,
                        ]);
                    }
                }

                if (!empty($personaUpdates['telefono_uno'])) {
                    $existeTel = $db->queryOne("
                        SELECT 1 AS ok
                        FROM estado_cuenta.telefonos_persona
                        WHERE id_persona = :id_persona AND numero = :numero
                        LIMIT 1
                    ", ['id_persona' => $idPersona, 'numero' => $personaUpdates['telefono_uno']]);
                    if (!$existeTel) {
                        $db->CRUD("
                            INSERT INTO estado_cuenta.telefonos_persona (id_persona, numero, tipo, estatus)
                            VALUES (:id_persona, :numero, 'Personal', 'Activo')
                        ", ['id_persona' => $idPersona, 'numero' => $personaUpdates['telefono_uno']]);
                    }
                }

                if (!empty($personaUpdates['correo']) && filter_var($personaUpdates['correo'], FILTER_VALIDATE_EMAIL)) {
                    $existeCorreo = $db->queryOne("
                        SELECT 1 AS ok
                        FROM estado_cuenta.correos_persona
                        WHERE id_persona = :id_persona AND correo = :correo
                        LIMIT 1
                    ", ['id_persona' => $idPersona, 'correo' => $personaUpdates['correo']]);
                    if (!$existeCorreo) {
                        $db->CRUD("
                            INSERT INTO estado_cuenta.correos_persona (id_persona, correo, tipo, estatus)
                            VALUES (:id_persona, :correo, 'Personal', 'Activo')
                        ", ['id_persona' => $idPersona, 'correo' => $personaUpdates['correo']]);
                    }
                }

                if (!empty($personaUpdates['domicilio_calle_texto'])) {
                    $existeDomicilio = $db->queryOne("
                        SELECT 1 AS ok
                        FROM estado_cuenta.domicilio_persona
                        WHERE id_persona = :id_persona AND domicilio_texto = :domicilio
                        LIMIT 1
                    ", ['id_persona' => $idPersona, 'domicilio' => $personaUpdates['domicilio_calle_texto']]);
                    if (!$existeDomicilio) {
                        $db->CRUD("
                            INSERT INTO estado_cuenta.domicilio_persona (id_persona, domicilio_texto, codigo_postal, tipo, estatus)
                            VALUES (:id_persona, :domicilio, :codigo_postal, 'Particular', 'Activo')
                        ", [
                            'id_persona' => $idPersona,
                            'domicilio' => $personaUpdates['domicilio_calle_texto'],
                            'codigo_postal' => $personaUpdates['codigo_postal'] ?? null,
                        ]);
                    }
                }

                self::registrarAuditoriaInternaRrhh([
                    'id_usuario' => $idUsuario,
                    'entidad_tipo' => 'persona',
                    'entidad_id' => $idPersona,
                    'entidad_nombre' => (string)($persona['nombre'] ?? ''),
                    'accion' => 'importar_datos_curp_rrhh',
                    'resumen' => 'Datos RR.HH. actualizados por carga masiva con CURP.',
                    'detalle' => [
                        'fila' => $numeroFila,
                        'hoja' => $hoja,
                        'estatus_importacion' => $estatusImportacion,
                        'campos_persona' => array_keys($personaUpdates),
                        'campos_rrhh' => array_keys($rrhhUpdates),
                        'campos_banco' => array_keys($cuentaUpdates),
                    ],
                ]);

                $resumen['actualizados']++;
            }

            if ($aplicar) {
                $db->commit();
            }

            return self::resultado(
                true,
                $aplicar ? 'Importacion de datos RR.HH. aplicada.' : 'Previsualizacion de datos RR.HH. lista.',
                $resumen
            );
        } catch (\Throwable $e) {
            if (isset($db) && $db instanceof Database && $db->inTransaction()) {
                $db->rollback();
            }
            return self::resultado(false, 'No se pudo importar el Excel de datos RR.HH.', null, $e->getMessage());
        }
    }

    public static function importarCambiosEstructuraPorExternalId(array $filas, int $idUsuario, bool $aplicar = false): array
    {
        try {
            $db = new Database();
            // MySQL confirma implícitamente sentencias DDL; el esquema debe
            // prepararse antes de abrir la transacción de la importación.
            self::asegurarTablaPermisosPuesto($db);
            self::asegurarAsignaJefeSoportaVacante($db);
            $plan = self::prepararCambiosEstructuraPorExternalId($db, $filas);

            if (!$aplicar) {
                return self::resultado(true, 'Prevalidacion de estructura finalizada.', $plan);
            }

            if ((int)($plan['resumen']['con_cambios'] ?? 0) <= 0) {
                return self::resultado(true, 'El archivo no contiene cambios pendientes por aplicar.', $plan);
            }

            // CREATE/ALTER TABLE cierran implicitamente una transaccion en MySQL.
            // La trayectoria se registra durante cada cambio, por eso su esquema debe
            // existir antes de iniciar la transaccion que aplica la estructura.
            self::asegurarTablaTrayectoriaPuesto($db);

            $transaccionIniciada = false;
            $db->beginTransaction();
            $transaccionIniciada = true;
            $aplicados = 0;
            foreach (($plan['detalles'] ?? []) as $detalle) {
                if (($detalle['estado'] ?? '') !== 'cambio') {
                    continue;
                }

                $acciones = $detalle['acciones'] ?? [];
                $idPersona = (int)($acciones['id_persona'] ?? 0);
                $idPuesto = (int)($acciones['id_puesto'] ?? 0);
                if ($idPersona <= 0 || $idPuesto <= 0) {
                    continue;
                }

                $seAplicoCambio = false;
                if (!empty($acciones['cambiar_puesto'])) {
                    self::asignarPuestoAdicionalCambioEstructura($db, $idPersona, $idPuesto, $idUsuario);
                    $seAplicoCambio = true;
                }

                foreach (($acciones['jefes'] ?? []) as $relacion) {
                    $idSubordinado = (int)($relacion['id_persona'] ?? 0);
                    $idJefe = (int)($relacion['id_jefe'] ?? 0);
                    if ($idSubordinado <= 0 || $idJefe <= 0) {
                        continue;
                    }
                    $resultadoJefe = self::actualizarJefePersonaConDb($db, $idSubordinado, $idJefe);
                    if (empty($resultadoJefe['success'])) {
                        throw new \RuntimeException((string)($resultadoJefe['mensaje'] ?? 'No se pudo actualizar un jefe.'));
                    }
                    $seAplicoCambio = true;
                }

                if ($seAplicoCambio) {
                    $aplicados++;
                }
            }

            if (!$db->inTransaction()) {
                throw new \RuntimeException('La transacción de la importación se cerró antes de finalizar.');
            }

            $db->commit();
            $transaccionIniciada = false;
            $plan['resumen']['aplicados'] = $aplicados;
            $errores = (int)($plan['resumen']['errores'] ?? 0);
            $omitidos = (int)($plan['resumen']['omitidos'] ?? 0);
            $mensaje = 'Cambios de estructura aplicados correctamente.';
            if ($errores > 0 || $omitidos > 0) {
                $mensaje .= ' Se omitieron ' . $errores . ' fila(s) con error y ' . $omitidos . ' fila(s) en estatus Baja.';
            }
            return self::resultado(true, $mensaje, $plan);
        } catch (\Throwable $e) {
            if (!empty($transaccionIniciada) && isset($db) && $db instanceof Database && $db->inTransaction()) {
                try { $db->rollback(); } catch (\Throwable $rollbackError) {}
            }
            return self::resultado(false, 'No se pudo importar el cambio de estructura.', null, $e->getMessage());
        }
    }

    private static function prepararCambiosEstructuraPorExternalId(Database $db, array $filas): array
    {
        $personasPorNumero = self::indicePersonasPorNumeroEmpleado($db);
        $personasPorNombre = self::indicePersonasActivasPorNombre($db);
        $puestosPorClave = self::indicePuestosCambioEstructura($db);
        $puestosActivosPorPersona = self::indicePuestosActivosCambioEstructura($db);
        $jefesActualesPorPersona = self::indiceJefesActualesCambioEstructura($db);

        $resumen = [
            'total' => 0,
            'encontrados' => 0,
            'con_cambios' => 0,
            'sin_cambios' => 0,
            'omitidos' => 0,
            'errores' => 0,
            'aplicados' => 0,
        ];
        $detalles = [];

        // Un mismo external_id puede repetirse en la plantilla solo si conserva
        // exactamente el mismo puesto y departamento. Si vienen dos destinos
        // distintos, el resultado dependia del estado anterior y cada carga podia
        // alternar la asignacion. Se bloquea el conflicto para que se corrija el
        // Excel antes de modificar personas.
        $estructurasPorExternal = [];
        foreach ($filas as $filaConflicto) {
            $externalConflicto = trim((string)($filaConflicto['external_id'] ?? ''));
            $puestoConflicto = trim((string)($filaConflicto['puesto_legacy'] ?? ''));
            $departamentoConflicto = trim((string)($filaConflicto['departamento'] ?? ''));
            if ($externalConflicto === '' || $puestoConflicto === '' || $departamentoConflicto === '') {
                continue;
            }

            $claveEstructura = self::normalizarTextoCambioEstructura($departamentoConflicto)
                . '|' . self::normalizarTextoCambioEstructura($puestoConflicto);
            $estructurasPorExternal[$externalConflicto][$claveEstructura] = [
                'fila' => (int)($filaConflicto['fila'] ?? 0),
                'puesto' => $puestoConflicto,
                'departamento' => $departamentoConflicto,
            ];
        }
        $conflictosEstructuraPorExternal = array_filter(
            $estructurasPorExternal,
            static fn(array $estructuras): bool => count($estructuras) > 1
        );

        foreach ($filas as $fila) {
            $resumen['total']++;
            $numeroFila = (int)($fila['fila'] ?? 0);
            $external = trim((string)($fila['external_id'] ?? ''));
            $puestoExcel = trim((string)($fila['puesto_legacy'] ?? ''));
            $departamentoExcel = trim((string)($fila['departamento'] ?? ''));
            $errores = [];
            $avisos = [];
            $acciones = ['jefes' => []];

            $detalle = [
                'fila' => $numeroFila,
                'external_id' => $external,
                'nombre_excel' => trim((string)($fila['nombre_completo'] ?? '')),
                'persona_id' => null,
                'persona_nombre' => '',
                'puesto_actual' => '',
                'puesto_nuevo' => $puestoExcel,
                'departamento_actual' => '',
                'departamento_nuevo' => $departamentoExcel,
                'jefe_actual' => '',
                'jefe_nuevo' => '',
                'supervisor' => trim((string)($fila['supervisor'] ?? '')),
                'subgerente' => trim((string)($fila['subgerente'] ?? '')),
                'gerente' => trim((string)($fila['gerente'] ?? '')),
                'subdirector' => trim((string)($fila['subdirector'] ?? '')),
                'estado' => 'sin_cambio',
                'mensajes' => [],
                'acciones' => [],
            ];

            if ($external === '') {
                $errores[] = 'external_id vacio.';
            }

            if ($puestoExcel === '') {
                $errores[] = 'puesto_legacy vacio.';
            }
            if ($departamentoExcel === '') {
                $errores[] = 'departamento vacio.';
            }

            if ($external !== '' && isset($conflictosEstructuraPorExternal[$external])) {
                $destinos = array_map(static function (array $estructura): string {
                    return 'fila ' . $estructura['fila'] . ': '
                        . $estructura['departamento'] . ' / ' . $estructura['puesto'];
                }, $conflictosEstructuraPorExternal[$external]);
                $errores[] = 'El external_id ' . $external
                    . ' aparece con estructuras diferentes (' . implode('; ', $destinos)
                    . '). Corrige o elimina el renglón duplicado antes de aplicar cambios.';
            }

            $personas = $personasPorNumero[$external] ?? [];
            if ($external !== '' && count($personas) === 0) {
                $errores[] = 'No existe persona con numero_empleado igual a ' . $external . '.';
            } elseif (count($personas) > 1) {
                $nombreExcelNormalizado = self::normalizarTextoCambioEstructura($detalle['nombre_excel']);
                $firmaExcel = self::firmaNombreCambioEstructura($nombreExcelNormalizado);
                $coincidenciasNombre = array_values(array_filter($personas, static function (array $candidata) use ($nombreExcelNormalizado, $firmaExcel): bool {
                    $nombreCandidata = self::normalizarTextoCambioEstructura(self::nombreCompletoPersonaCambioEstructura($candidata));
                    if ($nombreExcelNormalizado !== '' && $nombreCandidata === $nombreExcelNormalizado) {
                        return true;
                    }
                    return $firmaExcel !== '' && self::firmaNombreCambioEstructura($nombreCandidata) === $firmaExcel;
                }));
                if (count($coincidenciasNombre) === 1) {
                    $personas = $coincidenciasNombre;
                    $avisos[] = 'El numero de empleado esta duplicado; se eligio la coincidencia por Nombre completo.';
                } else {
                    $errores[] = 'Hay mas de una persona con numero_empleado ' . $external . ' y no se pudo resolver con Nombre completo.';
                }
            }

            $persona = count($personas) === 1 ? $personas[0] : null;
            if ($persona) {
                $detalle['persona_id'] = (int)$persona['id'];
                $detalle['persona_nombre'] = self::nombreCompletoPersonaCambioEstructura($persona);
                if ($detalle['nombre_excel'] !== ''
                    && self::normalizarTextoCambioEstructura($detalle['nombre_excel']) !== self::normalizarTextoCambioEstructura($detalle['persona_nombre'])) {
                    $avisos[] = 'El nombre del Excel "' . $detalle['nombre_excel'] . '" no coincide exactamente con "' . $detalle['persona_nombre'] . '", pero se identifico a la persona por external_id ' . $external . '.';
                }
                if (in_array(strtolower(trim((string)($persona['estatus'] ?? ''))), ['baja', 'transito de baja'], true)) {
                    $puestosActuales = $puestosActivosPorPersona[(int)$persona['id']] ?? [];
                    $puestoActual = array_values($puestosActuales)[0] ?? null;
                    $jefeActual = $jefesActualesPorPersona[(int)$persona['id']] ?? null;
                    $detalle['puesto_actual'] = (string)($puestoActual['nombre_puesto'] ?? 'Sin puesto');
                    $detalle['departamento_actual'] = (string)($puestoActual['nombre_departamento'] ?? 'Sin departamento');
                    $detalle['jefe_actual'] = $jefeActual ? (string)$jefeActual['nombre'] : 'Sin jefe';
                    $detalle['jefe_nuevo'] = 'No evaluado por estatus Baja o Tránsito de baja';
                    // Las bajas pueden venir en la misma plantilla que los activos. Se
                    // informan y se omiten, sin bloquear la actualizacion de los demas.
                    $detalle['estado'] = 'omitido';
                    $detalle['mensajes'] = [
                        'La persona esta en estatus Baja o Tránsito de baja; se omitio de la actualizacion de estructura.',
                        'El archivo proponia asignar ' . $detalle['puesto_nuevo'] . ' en ' . $detalle['departamento_nuevo'] . '. No se evaluo ni aplico el cambio de puesto o jefe.',
                    ];
                    $detalles[] = $detalle;
                    $resumen['omitidos']++;
                    continue;
                } else {
                    $resumen['encontrados']++;
                }
            }

            $clavePuesto = self::normalizarTextoCambioEstructura($departamentoExcel) . '|' . self::normalizarTextoCambioEstructura($puestoExcel);
            $puestos = ($puestoExcel !== '' && $departamentoExcel !== '') ? ($puestosPorClave[$clavePuesto] ?? []) : [];
            if (count($puestos) === 0 && $puestoExcel !== '' && $departamentoExcel !== '') {
                $errores[] = 'No existe una coincidencia exacta para el puesto "' . $puestoExcel . '" dentro del departamento "' . $departamentoExcel . '". No se aplican equivalencias automáticas de puesto.';
            } elseif (count($puestos) > 1) {
                $errores[] = 'El puesto y departamento coinciden con mas de un registro del catalogo.';
            }

            $puestoNuevo = count($puestos) === 1 ? $puestos[0] : null;
            if ($persona && $puestoNuevo) {
                $puestosActuales = $puestosActivosPorPersona[(int)$persona['id']] ?? [];
                $puestoActual = array_values($puestosActuales)[0] ?? null;
                $detalle['puesto_actual'] = (string)($puestoActual['nombre_puesto'] ?? 'Sin puesto');
                $detalle['departamento_actual'] = (string)($puestoActual['nombre_departamento'] ?? 'Sin departamento');
                $detalle['puesto_nuevo'] = (string)$puestoNuevo['nombre_puesto'];
                $detalle['departamento_nuevo'] = (string)$puestoNuevo['nombre_departamento'];

                $idsActivos = array_values(array_unique(array_map(static function ($row) {
                    return (int)($row['id_puesto'] ?? 0);
                }, $puestosActuales)));
                // Una persona puede tener varios puestos activos. Solo agregamos el del renglón
                // cuando aún no lo tiene, sin desactivar las asignaciones existentes.
                $cambiaPuesto = !in_array((int)$puestoNuevo['id_puesto'], $idsActivos, true);
                $acciones['id_persona'] = (int)$persona['id'];
                $acciones['id_puesto'] = (int)$puestoNuevo['id_puesto'];
                $acciones['cambiar_puesto'] = $cambiaPuesto;
            }

            // El puesto y departamento de la persona vienen de su propia fila. Las
            // columnas jerarquicas solo sirven para elegir su jefe directo segun ese
            // puesto, no para inferir el puesto de la persona.
            $rolPuesto = self::rolPuestoCambioEstructura($puestoExcel);
            $cadenaJerarquica = ['supervisor', 'subgerente', 'gerente', 'subdirector'];
            $inicioCadenaPorRol = [
                'gestor' => 0,
                'supervisor' => 1,
                'subgerente' => 2,
                'gerente' => 3,
                'subdirector' => 3,
            ];
            $inicioCadena = $inicioCadenaPorRol[$rolPuesto] ?? null;
            $jefeDirectoRol = '';
            $jefeDirectoNombre = '';
            if ($inicioCadena !== null) {
                // Si el nivel inmediato viene vacio, se busca el siguiente nivel de
                // la cadena. Un nombre informado pero invalido no se salta: debe
                // corregirse para evitar una reasignacion incorrecta.
                foreach (array_slice($cadenaJerarquica, $inicioCadena) as $rolJerarquico) {
                    $nombreJefe = trim((string)($detalle[$rolJerarquico] ?? ''));
                    if ($nombreJefe !== '') {
                        $jefeDirectoRol = $rolJerarquico;
                        $jefeDirectoNombre = $nombreJefe;
                        break;
                    }
                }
            }
            $jefeDirecto = $jefeDirectoNombre === ''
                ? null
                : self::resolverPersonaActivaPorNombreCambioEstructura($personasPorNombre, $jefeDirectoNombre, $jefeDirectoRol, $errores);

            if ($persona) {
                $jefeActual = $jefesActualesPorPersona[(int)$persona['id']] ?? null;
                $detalle['jefe_actual'] = $jefeActual ? (string)$jefeActual['nombre'] : 'Sin jefe';
                if ($jefeDirecto) {
                    $detalle['jefe_nuevo'] = self::nombreCompletoPersonaCambioEstructura($jefeDirecto);
                    if ((int)$jefeDirecto['id'] === (int)$persona['id']) {
                        $errores[] = 'La persona no puede ser su propio jefe.';
                    } else {
                        $cambiaJefe = (int)($jefeActual['id_jefe'] ?? 0) !== (int)$jefeDirecto['id'];
                        if ($cambiaJefe) {
                            $acciones['jefes'][] = ['id_persona' => (int)$persona['id'], 'id_jefe' => (int)$jefeDirecto['id'], 'orden' => 30];
                        }
                    }
                } else {
                    $detalle['jefe_nuevo'] = $detalle['jefe_actual'] ?: 'Sin jefe';
                    if ($jefeDirectoRol !== '') {
                        $avisos[] = 'No se recibio ' . $jefeDirectoRol . ' para el puesto ' . $puestoExcel . '; se conserva el jefe actual.';
                    } else {
                        $avisos[] = 'No existe una regla de jefe directo para el puesto ' . $puestoExcel . '; se conserva el jefe actual.';
                    }
                }
            }

            // Cada persona se procesa desde su propia fila: Nombre completo, Puesto legacy y
            // Departamento. Las columnas jerarquicas solo determinan su jefe directo actual.

            $tieneCambio = !empty($acciones['cambiar_puesto']) || !empty($acciones['jefes']);
            if (!empty($errores)) {
                $detalle['estado'] = 'error';
                $detalle['mensajes'] = $errores;
                $resumen['errores']++;
            } elseif ($tieneCambio) {
                usort($acciones['jefes'], static function ($a, $b) {
                    return (int)($a['orden'] ?? 99) <=> (int)($b['orden'] ?? 99);
                });
                $detalle['estado'] = 'cambio';
                $detalle['mensajes'] = array_merge(['Listo para actualizar.'], $avisos);
                $detalle['acciones'] = $acciones;
                $resumen['con_cambios']++;
            } else {
                $detalle['estado'] = 'sin_cambio';
                $detalle['mensajes'] = array_merge(['Sin cambios detectados.'], $avisos);
                $detalle['acciones'] = $acciones;
                $resumen['sin_cambios']++;
            }

            $detalles[] = $detalle;
        }

        // asigna_jefe es una relación por persona (no por puesto). Si un multipuesto
        // requiere jefes directos diferentes, detener la aplicación evita que la última
        // fila sobrescriba silenciosamente a las anteriores.
        $jefesDirectos = [];
        foreach ($detalles as $indice => $detalle) {
            if (($detalle['estado'] ?? '') === 'error') {
                continue;
            }
            foreach (($detalle['acciones']['jefes'] ?? []) as $relacion) {
                if ((int)($relacion['orden'] ?? 0) !== 30) {
                    continue;
                }
                $idPersona = (int)($relacion['id_persona'] ?? 0);
                $idJefe = (int)($relacion['id_jefe'] ?? 0);
                if ($idPersona > 0 && $idJefe > 0) {
                    $jefesDirectos[$idPersona][$idJefe][] = $indice;
                }
            }
        }
        foreach ($jefesDirectos as $asignaciones) {
            if (count($asignaciones) <= 1) {
                continue;
            }
            foreach ($asignaciones as $indices) {
                foreach ($indices as $indice) {
                    if (($detalles[$indice]['estado'] ?? '') === 'error') {
                        continue;
                    }
                    if (($detalles[$indice]['estado'] ?? '') === 'cambio') {
                        $resumen['con_cambios']--;
                    } else {
                        $resumen['sin_cambios']--;
                    }
                    $detalles[$indice]['estado'] = 'error';
                    $detalles[$indice]['mensajes'][] = 'La persona tiene varios puestos con jefes directos distintos; asigna_jefe solo admite un jefe por persona.';
                    $resumen['errores']++;
                }
            }
        }

        return ['resumen' => $resumen, 'detalles' => $detalles];
    }

    private static function indicePersonasPorNumeroEmpleado(Database $db): array
    {
        $rows = $db->queryAll("
            SELECT id, numero_empleado, nombres, segundo_nombre, apellidop, apellidom, estatus
            FROM estado_cuenta.persona
            WHERE TRIM(COALESCE(numero_empleado, '')) <> ''
        ");
        $out = [];
        foreach ($rows as $row) {
            $numero = trim((string)($row['numero_empleado'] ?? ''));
            if ($numero === '') {
                continue;
            }
            $out[$numero][] = $row;
        }
        return $out;
    }

    private static function indicePersonasActivasPorNombre(Database $db): array
    {
        $rows = $db->queryAll("
            SELECT id, numero_empleado, nombres, segundo_nombre, apellidop, apellidom, estatus
            FROM estado_cuenta.persona
            WHERE LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
        ");
        $out = [];
        foreach ($rows as $row) {
            $nombre = self::normalizarTextoCambioEstructura(self::nombreCompletoPersonaCambioEstructura($row));
            if ($nombre === '') {
                continue;
            }
            $out[$nombre][] = $row;
            $firma = self::firmaNombreCambioEstructura($nombre);
            if ($firma !== '') {
                $out['__firma__' . $firma][] = $row;
            }
        }
        return $out;
    }

    private static function indicePuestosCambioEstructura(Database $db): array
    {
        $rows = $db->queryAll("
            SELECT
                pu.id AS id_puesto,
                pu.nombre AS nombre_puesto,
                dep.id AS id_departamento,
                dep.nombre AS nombre_departamento
            FROM estado_cuenta.puesto pu
            INNER JOIN estado_cuenta.departamento dep ON dep.id = pu.departamento_id
            WHERE COALESCE(pu.activo, 1) = 1
              AND COALESCE(dep.activo, 1) = 1
        ");
        $out = [];
        foreach ($rows as $row) {
            $clave = self::normalizarTextoCambioEstructura($row['nombre_departamento'] ?? '')
                . '|'
                . self::normalizarTextoCambioEstructura($row['nombre_puesto'] ?? '');
            if ($clave === '|') {
                continue;
            }
            $out[$clave][] = $row;
            $claveRol = self::normalizarTextoCambioEstructura($row['nombre_departamento'] ?? '')
                . '|__rol__'
                . self::rolPuestoCambioEstructura($row['nombre_puesto'] ?? '');
            if ($claveRol !== '|__rol__') {
                $out[$claveRol][] = $row;
            }
        }
        return $out;
    }

    private static function resolverPersonaActivaPorNombreCambioEstructura(array $indice, string $nombre, string $rol, array &$errores): ?array
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return null;
        }
        $clave = self::normalizarTextoCambioEstructura($nombre);
        if (in_array($clave, ['vacante', 'sin jefe', 'ninguno', 'na', 'n a', 'no aplica'], true)
            || strpos($clave, 'vacante ') === 0) {
            return null;
        }
        $personas = $indice[$clave] ?? [];
        if (count($personas) === 0) {
            $firma = self::firmaNombreCambioEstructura($clave);
            if ($firma !== '') {
                $personas = $indice['__firma__' . $firma] ?? [];
            }
        }
        if (count($personas) === 0) {
            $errores[] = 'No se encontro ' . $rol . ' activo con nombre "' . $nombre . '".';
            return null;
        }
        if (count($personas) > 1) {
            $errores[] = 'El nombre "' . $nombre . '" coincide con mas de una persona activa para ' . $rol . '.';
            return null;
        }
        return $personas[0];
    }

    /** Carga los puestos activos una sola vez para la prevalidación masiva. */
    private static function indicePuestosActivosCambioEstructura(Database $db): array
    {
        $rows = $db->queryAll("
            SELECT
                ap.id_persona,
                ap.id AS id_asigna_puesto,
                ap.id_puesto,
                ap.fecha_asignacion,
                pu.nombre AS nombre_puesto,
                pu.departamento_id AS id_departamento,
                dep.nombre AS nombre_departamento,
                COALESCE(pu.nivel, 0) AS nivel
            FROM estado_cuenta.asigna_puesto ap
            INNER JOIN estado_cuenta.puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN estado_cuenta.departamento dep ON dep.id = pu.departamento_id
            WHERE COALESCE(ap.activo, 1) = 1
            ORDER BY ap.id_persona, COALESCE(pu.nivel, 0) DESC, ap.id ASC
        ");
        $out = [];
        foreach ($rows as $row) {
            $idPersona = (int)($row['id_persona'] ?? 0);
            if ($idPersona > 0) {
                $out[$idPersona][] = $row;
            }
        }
        return $out;
    }

    /** Último jefe vigente por persona, cargado una vez para evitar consultas por fila. */
    private static function indiceJefesActualesCambioEstructura(Database $db): array
    {
        $rows = $db->queryAll("
            SELECT aj.id_persona, aj.id_jefe,
                   TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)) AS nombre
            FROM estado_cuenta.asigna_jefe aj
            INNER JOIN (
                SELECT id_persona, MAX(id) AS id_actual
                FROM estado_cuenta.asigna_jefe
                GROUP BY id_persona
            ) ultimo ON ultimo.id_actual = aj.id
            LEFT JOIN estado_cuenta.persona pj ON pj.id = aj.id_jefe
        ");
        $out = [];
        foreach ($rows as $row) {
            $idPersona = (int)($row['id_persona'] ?? 0);
            if ($idPersona > 0) {
                $out[$idPersona] = $row;
            }
        }
        return $out;
    }

    private static function obtenerJefeActualCambioEstructura(Database $db, int $idPersona): ?array
    {
        return $db->queryOne("
            SELECT
                aj.id_jefe,
                TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)) AS nombre
            FROM estado_cuenta.asigna_jefe aj
            LEFT JOIN estado_cuenta.persona pj ON pj.id = aj.id_jefe
            WHERE aj.id_persona = :id_persona
            ORDER BY aj.id DESC
            LIMIT 1
        ", ['id_persona' => $idPersona]);
    }

    private static function asignarPuestoAdicionalCambioEstructura(Database $db, int $idPersona, int $idPuesto, int $idUsuario): void
    {
        $puestosAntes = self::puestosActivosTrayectoria($db, $idPersona);
        $fechaAsignacion = self::fechaHoraCdmx();

        $db->CRUD(
            "UPDATE estado_cuenta.asigna_puesto
             SET activo = 0
             WHERE id_persona = :id_persona",
            ['id_persona' => $idPersona]
        );

        $existente = $db->queryOne(
            "SELECT id
             FROM estado_cuenta.asigna_puesto
             WHERE id_persona = :id_persona
               AND id_puesto = :id_puesto
             ORDER BY id DESC
             LIMIT 1",
            ['id_persona' => $idPersona, 'id_puesto' => $idPuesto]
        );

        if ($existente) {
            $db->CRUD(
                "UPDATE estado_cuenta.asigna_puesto
                 SET activo = 1,
                     fecha_asignacion = :fecha_asignacion
                 WHERE id = :id",
                ['id' => (int)$existente['id'], 'fecha_asignacion' => $fechaAsignacion]
            );
        } else {
            $db->CRUD(
                "INSERT INTO estado_cuenta.asigna_puesto (id_persona, id_puesto, fecha_asignacion, activo)
                 VALUES (:id_persona, :id_puesto, :fecha_asignacion, 1)",
                ['id_persona' => $idPersona, 'id_puesto' => $idPuesto, 'fecha_asignacion' => $fechaAsignacion]
            );
        }

        self::aplicarPermisosPuestoAPersonaConDb($db, $idPersona, $idPuesto);
        $puestosDespues = self::puestosActivosTrayectoria($db, $idPersona);
        self::registrarCambiosTrayectoriaPuestos($db, $idPersona, $puestosAntes, $puestosDespues, $idUsuario, 'importacion_cambio_estructura');
    }

    private static function actualizarJefePersonaConDb(Database $db, int $idPersona, int $idJefe): array
    {
        if ($idPersona <= 0 || $idJefe <= 0) {
            return self::resultado(false, 'Seleccione persona y jefe destino.');
        }
        if ($idPersona === $idJefe) {
            return self::resultado(false, 'Una persona no puede ser su propio jefe.');
        }

        $persona = $db->queryOne("
            SELECT id
            FROM estado_cuenta.persona
            WHERE id = :id_persona
              AND LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
            LIMIT 1
        ", ['id_persona' => $idPersona]);
        if (!$persona) {
            return self::resultado(false, 'La persona seleccionada no esta activa.');
        }

        $jefe = $db->queryOne("
            SELECT id
            FROM estado_cuenta.persona
            WHERE id = :id_jefe
              AND LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
            LIMIT 1
        ", ['id_jefe' => $idJefe]);
        if (!$jefe) {
            return self::resultado(false, 'El jefe seleccionado no esta activo.');
        }

        $actual = $idJefe;
        $vistos = [];
        for ($i = 0; $i < 80 && $actual > 0; $i++) {
            if ($actual === $idPersona) {
                return self::resultado(false, 'No se puede asignar ese jefe porque generaria un ciclo en el organigrama.');
            }
            if (isset($vistos[$actual])) {
                break;
            }
            $vistos[$actual] = true;

            $rel = $db->queryOne("
                SELECT id_jefe
                FROM estado_cuenta.asigna_jefe
                WHERE id_persona = :id_persona
                ORDER BY id DESC
                LIMIT 1
            ", ['id_persona' => $actual]);
            $actual = !empty($rel['id_jefe']) ? (int)$rel['id_jefe'] : 0;
        }

        $asignacion = $db->queryOne("
            SELECT id
            FROM estado_cuenta.asigna_jefe
            WHERE id_persona = :id_persona
            ORDER BY id DESC
            LIMIT 1
        ", ['id_persona' => $idPersona]);

        if ($asignacion) {
            $db->CRUD("
                UPDATE estado_cuenta.asigna_jefe
                SET id_jefe = :id_jefe,
                    id_vacante_jefe = NULL
                WHERE id = :id
                LIMIT 1
            ", ['id_jefe' => $idJefe, 'id' => (int)$asignacion['id']]);
        } else {
            $db->CRUD("
                INSERT INTO estado_cuenta.asigna_jefe (id_persona, id_jefe, id_vacante_jefe)
                VALUES (:id_persona, :id_jefe, NULL)
            ", ['id_persona' => $idPersona, 'id_jefe' => $idJefe]);
        }

        return self::resultado(true, 'Jefe actualizado correctamente.');
    }

    private static function nombreCompletoPersonaCambioEstructura(array $persona): string
    {
        return trim(implode(' ', array_filter([
            trim((string)($persona['nombres'] ?? '')),
            trim((string)($persona['segundo_nombre'] ?? '')),
            trim((string)($persona['apellidop'] ?? '')),
            trim((string)($persona['apellidom'] ?? '')),
        ], static function ($valor) {
            return $valor !== '';
        })));
    }

    private static function normalizarTextoCambioEstructura($valor): string
    {
        $texto = trim((string)$valor);
        if ($texto === '') {
            return '';
        }
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
            if ($ascii !== false) {
                $texto = $ascii;
            }
        }
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto);
        return trim((string)preg_replace('/\s+/', ' ', (string)$texto));
    }

    /** Firma sin orden: permite resolver "APELLIDO APELLIDO NOMBRE" contra la persona registrada. */
    private static function firmaNombreCambioEstructura(string $nombreNormalizado): string
    {
        $tokens = preg_split('/\s+/', trim($nombreNormalizado)) ?: [];
        $tokens = array_values(array_filter($tokens, static function (string $token): bool {
            return $token !== '';
        }));
        sort($tokens, SORT_STRING);
        return implode(' ', $tokens);
    }

    /** Rol base del puesto para aceptar la etiqueta corta del Excel dentro del mismo departamento. */
    private static function rolPuestoCambioEstructura($puesto): string
    {
        $normalizado = self::normalizarTextoCambioEstructura($puesto);
        if ($normalizado === '') {
            return '';
        }
        $tokens = preg_split('/\s+/', $normalizado) ?: [];
        return (string)($tokens[0] ?? '');
    }

    public static function registrarAuditoriaSalarioSensibleRrhh(array $datos): void
    {
        try {
            $db = new Database();
            self::asegurarSalariosSensiblesRrhh($db);
            $db->CRUD("
                INSERT INTO estado_cuenta.auditoria_salarios_sensibles_rrhh
                    (id_usuario, usuario_nombre, id_persona, persona_nombre, accion, resultado, ip, user_agent, detalle, fecha_hora)
                VALUES
                    (:id_usuario, :usuario_nombre, :id_persona, :persona_nombre, :accion, :resultado, :ip, :user_agent, :detalle, :fecha_hora)
            ", [
                'id_usuario' => (int)($datos['id_usuario'] ?? 0),
                'usuario_nombre' => (string)($datos['usuario_nombre'] ?? ''),
                'id_persona' => (int)($datos['id_persona'] ?? 0),
                'persona_nombre' => (string)($datos['persona_nombre'] ?? ''),
                'accion' => (string)($datos['accion'] ?? ''),
                'resultado' => (string)($datos['resultado'] ?? ''),
                'ip' => (string)($datos['ip'] ?? ''),
                'user_agent' => (string)($datos['user_agent'] ?? ''),
                'detalle' => (string)($datos['detalle'] ?? ''),
                'fecha_hora' => (string)($datos['fecha_hora'] ?? date('Y-m-d H:i:s')),
            ]);
        } catch (\Throwable $e) {
            error_log('CapHum::registrarAuditoriaSalarioSensibleRrhh -> ' . $e->getMessage());
        }
    }

    public static function getAuditoriaSalariosSensiblesRrhh(): array
    {
        try {
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            self::asegurarSalariosSensiblesRrhh($db);

            $usuariosConPermiso = $db->queryAll("
                SELECT
                    p.id AS persona_id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.nombres), ''),
                        NULLIF(TRIM(p.segundo_nombre), ''),
                        NULLIF(TRIM(p.apellidop), ''),
                        NULLIF(TRIM(p.apellidom), '')
                    )) AS nombre,
                    p.user_name,
                    p.correo,
                    p.estatus,
                    COALESCE(NULLIF(TRIM(pdr.puesto_texto), ''), pu.nombre, '') AS puesto,
                    COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') AS departamento
                FROM asigna_modulo_web am
                INNER JOIN persona p ON p.id = am.usuario_id
                LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                WHERE am.modulo_web_id = :modulo
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                ORDER BY nombre ASC
            ", ['modulo' => self::MODULO_VER_SALARIO_SENSIBLE_RRHH]) ?: [];

            $eventos = $db->queryAll("
                SELECT
                    a.id,
                    a.fecha_hora,
                    a.id_usuario,
                    COALESCE(NULLIF(TRIM(a.usuario_nombre), ''), TRIM(CONCAT_WS(' ', u.nombres, u.segundo_nombre, u.apellidop, u.apellidom)), CONCAT('Usuario #', COALESCE(a.id_usuario, ''))) AS usuario_nombre,
                    a.id_persona,
                    COALESCE(NULLIF(TRIM(a.persona_nombre), ''), TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)), CONCAT('Persona #', COALESCE(a.id_persona, ''))) AS persona_nombre,
                    a.accion,
                    a.resultado,
                    a.ip,
                    a.detalle
                FROM estado_cuenta.auditoria_salarios_sensibles_rrhh a
                LEFT JOIN persona u ON u.id = a.id_usuario
                LEFT JOIN persona p ON p.id = a.id_persona
                ORDER BY a.fecha_hora DESC, a.id DESC
                LIMIT 200
            ") ?: [];

            $totalesEventos = [
                'lecturas' => 0,
                'guardados' => 0,
                'denegados' => 0,
                'totp_denegado' => 0,
                'eventos' => count($eventos),
            ];
            foreach ($eventos as $evento) {
                $accion = strtolower((string)($evento['accion'] ?? ''));
                $resultado = strtolower((string)($evento['resultado'] ?? ''));
                if ($accion === 'leer' && $resultado === 'autorizado') {
                    $totalesEventos['lecturas']++;
                }
                if ($accion === 'guardar' && $resultado === 'autorizado') {
                    $totalesEventos['guardados']++;
                }
                if ($resultado === 'denegado') {
                    $totalesEventos['denegados']++;
                    if ($accion === 'totp') {
                        $totalesEventos['totp_denegado']++;
                    }
                }
            }

            return self::resultado(true, 'Auditoria de salarios cargada.', [
                'usuarios_con_permiso' => $usuariosConPermiso,
                'eventos' => $eventos,
                'totales' => [
                    'usuarios_con_permiso' => count($usuariosConPermiso),
                    'lecturas' => $totalesEventos['lecturas'],
                    'guardados' => $totalesEventos['guardados'],
                    'denegados' => $totalesEventos['denegados'],
                    'totp_denegado' => $totalesEventos['totp_denegado'],
                    'eventos' => $totalesEventos['eventos'],
                ],
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo cargar la auditoria de salarios.', [
                'usuarios_con_permiso' => [],
                'eventos' => [],
                'totales' => [
                    'usuarios_con_permiso' => 0,
                    'lecturas' => 0,
                    'guardados' => 0,
                    'denegados' => 0,
                    'totp_denegado' => 0,
                    'eventos' => 0,
                ],
            ], $e->getMessage());
        }
    }

    public static function getAuditoriaRrhhSensible(): array
    {
        try {
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            self::asegurarTotpDocumentosSensibles($db);
            self::asegurarAuditoriaDocumentosSensibles($db);
            self::asegurarSalariosSensiblesRrhh($db);

            $usuariosPorModulo = static function (int $modulo) use ($db): array {
                return $db->queryAll("
                    SELECT
                        p.id AS persona_id,
                        p.numero_empleado,
                        TRIM(CONCAT_WS(' ',
                            NULLIF(TRIM(p.nombres), ''),
                            NULLIF(TRIM(p.segundo_nombre), ''),
                            NULLIF(TRIM(p.apellidop), ''),
                            NULLIF(TRIM(p.apellidom), '')
                        )) AS nombre,
                        p.user_name,
                        p.correo,
                        p.estatus,
                        COALESCE(NULLIF(TRIM(pdr.puesto_texto), ''), pu.nombre, '') AS puesto,
                        COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') AS departamento
                    FROM asigna_modulo_web am
                    INNER JOIN persona p ON p.id = am.usuario_id
                    LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                    LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                    LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                    LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                    WHERE am.modulo_web_id = :modulo
                      AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                    ORDER BY nombre ASC
                ", ['modulo' => $modulo]) ?: [];
            };

            $autenticadores = $db->queryAll("
                SELECT
                    t.id_persona AS persona_id,
                    TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.nombres), ''),
                        NULLIF(TRIM(p.segundo_nombre), ''),
                        NULLIF(TRIM(p.apellidop), ''),
                        NULLIF(TRIM(p.apellidom), '')
                    )) AS nombre,
                    p.user_name,
                    p.correo,
                    p.numero_empleado,
                    t.confirmado,
                    DATE_FORMAT(t.creado_en, '%Y-%m-%d %H:%i:%s') AS creado_en,
                    DATE_FORMAT(t.actualizado_en, '%Y-%m-%d %H:%i:%s') AS actualizado_en,
                    DATE_FORMAT(t.ultimo_uso_en, '%Y-%m-%d %H:%i:%s') AS ultimo_uso_en
                FROM estado_cuenta.rrhh_documentos_sensibles_totp t
                LEFT JOIN persona p ON p.id = t.id_persona
                ORDER BY t.confirmado DESC, t.ultimo_uso_en DESC, nombre ASC
            ") ?: [];

            $eventosDocumentos = $db->queryAll("
                SELECT
                    'documentos' AS tipo,
                    a.id,
                    DATE_FORMAT(a.fecha_hora, '%Y-%m-%d %H:%i:%s') AS fecha_hora,
                    a.id_usuario,
                    COALESCE(NULLIF(TRIM(a.usuario_nombre), ''), TRIM(CONCAT_WS(' ', u.nombres, u.segundo_nombre, u.apellidop, u.apellidom)), CONCAT('Usuario #', COALESCE(a.id_usuario, ''))) AS usuario_nombre,
                    a.id_persona,
                    COALESCE(NULLIF(TRIM(a.persona_nombre), ''), TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)), CONCAT('Persona #', COALESCE(a.id_persona, ''))) AS persona_nombre,
                    a.id_documento_carga,
                    a.id_documento,
                    COALESCE(
                        NULLIF(TRIM(a.documento_nombre), ''),
                        NULLIF(TRIM(d.nombre), ''),
                        CASE a.id_documento
                            WHEN 8 THEN 'CURP'
                            WHEN 9 THEN 'Identificacion Oficial (INE)'
                            WHEN 10 THEN 'Constancia de Situacion Fiscal'
                            WHEN 11 THEN 'Comprobante de Domicilio'
                            WHEN 12 THEN 'Acta de Nacimiento'
                            WHEN 13 THEN 'Certificado de Estudios'
                            WHEN 14 THEN 'Referencias Laborales'
                            WHEN 15 THEN 'Documento baja'
                            WHEN 16 THEN 'Documento reingreso'
                            WHEN 17 THEN 'Solicitud interna'
                            WHEN 18 THEN 'CV o Solicitud de Trabajo'
                            WHEN 22 THEN 'Constancia de Situacion Fiscal'
                            WHEN 23 THEN 'Numero de Seguridad Social'
                            WHEN 24 THEN 'Hoja de Retencion FONACOT o INFONAVIT'
                            WHEN 25 THEN 'Estado de Cuenta'
                            WHEN 27 THEN 'Carta de compromiso del Gestor'
                            WHEN 28 THEN 'Contrato firmado'
                            WHEN 29 THEN 'Archivo .FAD'
                            WHEN 30 THEN 'Validacion SAT'
                            WHEN 31 THEN 'Llave vector'
                            WHEN 32 THEN 'Prueba centavo'
                            WHEN 33 THEN 'Semanas cotizadas IMSS (segundos patrones)'
                            WHEN 34 THEN 'Documento incapacidad'
                            WHEN 35 THEN 'Documento permiso'
                            WHEN 36 THEN 'Documento falta'
                            WHEN 37 THEN 'Finiquito'
                            WHEN 38 THEN 'Comprobante de pago finiquito'
                            ELSE 'Documento RR.HH.'
                        END
                    ) AS documento_nombre,
                    COALESCE(
                        NULLIF(TRIM(a.documento_nombre), ''),
                        NULLIF(TRIM(d.nombre), ''),
                        CASE a.id_documento
                            WHEN 8 THEN 'CURP'
                            WHEN 9 THEN 'Identificacion Oficial (INE)'
                            WHEN 10 THEN 'Constancia de Situacion Fiscal'
                            WHEN 11 THEN 'Comprobante de Domicilio'
                            WHEN 12 THEN 'Acta de Nacimiento'
                            WHEN 13 THEN 'Certificado de Estudios'
                            WHEN 14 THEN 'Referencias Laborales'
                            WHEN 15 THEN 'Documento baja'
                            WHEN 16 THEN 'Documento reingreso'
                            WHEN 17 THEN 'Solicitud interna'
                            WHEN 18 THEN 'CV o Solicitud de Trabajo'
                            WHEN 22 THEN 'Constancia de Situacion Fiscal'
                            WHEN 23 THEN 'Numero de Seguridad Social'
                            WHEN 24 THEN 'Hoja de Retencion FONACOT o INFONAVIT'
                            WHEN 25 THEN 'Estado de Cuenta'
                            WHEN 27 THEN 'Carta de compromiso del Gestor'
                            WHEN 28 THEN 'Contrato firmado'
                            WHEN 29 THEN 'Archivo .FAD'
                            WHEN 30 THEN 'Validacion SAT'
                            WHEN 31 THEN 'Llave vector'
                            WHEN 32 THEN 'Prueba centavo'
                            WHEN 33 THEN 'Semanas cotizadas IMSS (segundos patrones)'
                            WHEN 34 THEN 'Documento incapacidad'
                            WHEN 35 THEN 'Documento permiso'
                            WHEN 36 THEN 'Documento falta'
                            WHEN 37 THEN 'Finiquito'
                            WHEN 38 THEN 'Comprobante de pago finiquito'
                            ELSE 'Documento RR.HH.'
                        END
                    ) AS recurso,
                    a.archivo,
                    a.accion,
                    a.resultado,
                    a.ip,
                    a.detalle
                FROM estado_cuenta.auditoria_documentos_sensibles_rrhh a
                LEFT JOIN persona u ON u.id = a.id_usuario
                LEFT JOIN persona p ON p.id = a.id_persona
                LEFT JOIN estado_cuenta.documento d ON d.id = a.id_documento
                ORDER BY a.fecha_hora DESC, a.id DESC
                LIMIT 300
            ") ?: [];

            $eventosSalarios = $db->queryAll("
                SELECT
                    'salarios' AS tipo,
                    a.id,
                    DATE_FORMAT(a.fecha_hora, '%Y-%m-%d %H:%i:%s') AS fecha_hora,
                    a.id_usuario,
                    COALESCE(NULLIF(TRIM(a.usuario_nombre), ''), TRIM(CONCAT_WS(' ', u.nombres, u.segundo_nombre, u.apellidop, u.apellidom)), CONCAT('Usuario #', a.id_usuario)) AS usuario_nombre,
                    a.id_persona,
                    COALESCE(NULLIF(TRIM(a.persona_nombre), ''), TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)), CONCAT('Persona #', a.id_persona)) AS persona_nombre,
                    NULL AS id_documento_carga,
                    NULL AS id_documento,
                    'Salario protegido' AS documento_nombre,
                    'Salario protegido' AS recurso,
                    '' AS archivo,
                    a.accion,
                    a.resultado,
                    a.ip,
                    a.detalle
                FROM estado_cuenta.auditoria_salarios_sensibles_rrhh a
                LEFT JOIN persona u ON u.id = a.id_usuario
                LEFT JOIN persona p ON p.id = a.id_persona
                ORDER BY a.fecha_hora DESC, a.id DESC
                LIMIT 300
            ") ?: [];

            $eventos = array_merge($eventosDocumentos, $eventosSalarios);
            usort($eventos, static function ($a, $b) {
                $fechaA = strtotime((string)($a['fecha_hora'] ?? '')) ?: 0;
                $fechaB = strtotime((string)($b['fecha_hora'] ?? '')) ?: 0;
                if ($fechaA === $fechaB) {
                    return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
                }
                return $fechaB <=> $fechaA;
            });
            $eventos = array_slice($eventos, 0, 300);

            $conteoDocumentos = $db->queryOne("
                SELECT
                    COUNT(*) AS eventos,
                    SUM(CASE WHEN LOWER(resultado) = 'denegado' THEN 1 ELSE 0 END) AS denegados
                FROM estado_cuenta.auditoria_documentos_sensibles_rrhh
            ") ?: [];
            $conteoSalarios = $db->queryOne("
                SELECT
                    COUNT(*) AS eventos,
                    SUM(CASE WHEN LOWER(resultado) = 'denegado' THEN 1 ELSE 0 END) AS denegados
                FROM estado_cuenta.auditoria_salarios_sensibles_rrhh
            ") ?: [];

            $totpConfirmados = 0;
            foreach ($autenticadores as $auth) {
                if ((int)($auth['confirmado'] ?? 0) === 1) {
                    $totpConfirmados++;
                }
            }

            $usuariosDocumentos = $usuariosPorModulo(self::MODULO_VER_DOCUMENTOS_SENSIBLES_RRHH);
            $usuariosSalarios = $usuariosPorModulo(self::MODULO_VER_SALARIO_SENSIBLE_RRHH);
            $usuariosResetTotp = $usuariosPorModulo(self::MODULO_RESET_TOTP_DOCUMENTOS_SENSIBLES_RRHH);
            $usuariosAuditoria = $usuariosPorModulo(self::MODULO_AUDITORIA_RRHH);

            return self::resultado(true, 'Auditoria RR.HH. cargada.', [
                'usuarios_con_permiso' => [
                    'documentos_sensibles' => $usuariosDocumentos,
                    'salarios' => $usuariosSalarios,
                    'reset_totp' => $usuariosResetTotp,
                    'auditoria' => $usuariosAuditoria,
                ],
                'autenticadores' => $autenticadores,
                'eventos' => $eventos,
                'eventos_documentos' => $eventosDocumentos,
                'eventos_salarios' => $eventosSalarios,
                'totales' => [
                    'usuarios_documentos' => count($usuariosDocumentos),
                    'usuarios_salarios' => count($usuariosSalarios),
                    'usuarios_reset_totp' => count($usuariosResetTotp),
                    'usuarios_auditoria' => count($usuariosAuditoria),
                    'totp_configurados' => count($autenticadores),
                    'totp_confirmados' => $totpConfirmados,
                    'eventos_documentos' => (int)($conteoDocumentos['eventos'] ?? 0),
                    'eventos_salarios' => (int)($conteoSalarios['eventos'] ?? 0),
                    'denegados_documentos' => (int)($conteoDocumentos['denegados'] ?? 0),
                    'denegados_salarios' => (int)($conteoSalarios['denegados'] ?? 0),
                    'eventos_recientes' => count($eventos),
                ],
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo cargar la auditoria RR.HH.', [
                'usuarios_con_permiso' => [
                    'documentos_sensibles' => [],
                    'salarios' => [],
                    'reset_totp' => [],
                    'auditoria' => [],
                ],
                'autenticadores' => [],
                'eventos' => [],
                'eventos_documentos' => [],
                'eventos_salarios' => [],
                'totales' => [],
            ], $e->getMessage());
        }
    }

    private static function asegurarAuditoriaInternaRrhh(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.caphum_auditoria_interna (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                id_usuario INT NULL,
                usuario_nombre VARCHAR(220) NULL,
                ip VARCHAR(64) NULL,
                modulo VARCHAR(80) NOT NULL DEFAULT 'Capital Humano',
                entidad_tipo VARCHAR(80) NOT NULL,
                entidad_id INT NULL,
                entidad_nombre VARCHAR(255) NULL,
                accion VARCHAR(80) NOT NULL,
                resumen VARCHAR(500) NULL,
                cambios_json LONGTEXT NULL,
                detalle_json LONGTEXT NULL,
                KEY idx_caphum_audit_fecha (fecha_hora),
                KEY idx_caphum_audit_usuario (id_usuario),
                KEY idx_caphum_audit_entidad (entidad_tipo, entidad_id),
                KEY idx_caphum_audit_accion (accion),
                KEY idx_caphum_audit_modulo (modulo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    private static function auditoriaTexto($value, int $max = 500): ?string
    {
        $text = trim((string)($value ?? ''));
        if ($text === '') {
            return null;
        }
        return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
    }

    private static function auditoriaSanitizar($value)
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $key = (string)$k;
                if (preg_match('/pass|password|contrasena|contraseña|secret|token|totp|salario/i', $key)) {
                    $out[$key] = '[PROTEGIDO]';
                    continue;
                }
                $out[$key] = self::auditoriaSanitizar($v);
            }
            return $out;
        }

        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        $text = trim((string)$value);
        if ($text === '') {
            return '';
        }
        return function_exists('mb_substr') ? mb_substr($text, 0, 800) : substr($text, 0, 800);
    }

    public static function diffAuditoria(array $antes, array $despues): array
    {
        $campos = array_values(array_unique(array_merge(array_keys($antes), array_keys($despues))));
        $ignorar = ['password', 'contrasena', 'contraseña', 'updated_at', 'fecha_actualizacion'];
        $cambios = [];
        foreach ($campos as $campo) {
            if (in_array((string)$campo, $ignorar, true)) {
                continue;
            }
            $a = $antes[$campo] ?? null;
            $d = $despues[$campo] ?? null;
            $aNorm = is_scalar($a) || $a === null ? trim((string)$a) : json_encode($a, JSON_UNESCAPED_UNICODE);
            $dNorm = is_scalar($d) || $d === null ? trim((string)$d) : json_encode($d, JSON_UNESCAPED_UNICODE);
            if ($aNorm !== $dNorm) {
                $cambios[(string)$campo] = [
                    'antes' => self::auditoriaSanitizar($a),
                    'despues' => self::auditoriaSanitizar($d),
                ];
            }
        }
        return $cambios;
    }

    public static function snapshotPersonaAuditoria(int $idPersona): array
    {
        if ($idPersona <= 0) {
            return [];
        }
        try {
            $db = new Database();
            $row = $db->queryOne("
                SELECT
                    p.id,
                    p.numero_empleado,
                    p.codigo_contpac,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.apellidom,
                    TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.nombres), ''),
                        NULLIF(TRIM(p.segundo_nombre), ''),
                        NULLIF(TRIM(p.apellidop), ''),
                        NULLIF(TRIM(p.apellidom), '')
                    )) AS nombre_completo,
                    p.curp,
                    p.correo,
                    p.telefono_uno,
                    p.telefono_dos,
                    p.user_name,
                    p.estatus,
                    p.fecha_ingreso,
                    p.id_pais,
                    p.id_div_nivel1,
                    p.id_div_nivel2,
                    p.id_div_nivel3,
                    p.domicilio_calle_texto,
                    p.domicilio_num_exterior,
                    p.domicilio_num_interior,
                    p.codigo_postal,
                    COALESCE(pdr.id_area, dep_rrhh.id_departamento_organizacional) AS id_area,
                    COALESCE(
                        NULLIF(TRIM(pdr.area_texto), ''),
                        NULLIF(TRIM(area_rrhh.nombre), '')
                    ) AS area,
                    GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ', ') AS puestos,
                    GROUP_CONCAT(DISTINCT dep.nombre ORDER BY dep.nombre SEPARATOR ', ') AS departamentos,
                    COALESCE(
                        TRIM(CONCAT_WS(' ', j.nombres, j.segundo_nombre, j.apellidop, j.apellidom)),
                        NULLIF(TRIM(v.nombre_vacante), '')
                    ) AS jefe
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN estado_cuenta.departamento dep_rrhh ON dep_rrhh.id = pdr.id_departamento
                LEFT JOIN estado_cuenta.departamento_organizacional area_rrhh ON area_rrhh.id = COALESCE(pdr.id_area, dep_rrhh.id_departamento_organizacional)
                LEFT JOIN estado_cuenta.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN estado_cuenta.puesto pu ON pu.id = ap.id_puesto
                LEFT JOIN estado_cuenta.departamento dep ON dep.id = pu.departamento_id
                LEFT JOIN (
                    SELECT aj1.id_persona, aj1.id_jefe, aj1.id_vacante_jefe
                    FROM estado_cuenta.asigna_jefe aj1
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS max_id
                        FROM estado_cuenta.asigna_jefe
                        GROUP BY id_persona
                    ) ult ON ult.id_persona = aj1.id_persona AND ult.max_id = aj1.id
                ) aj ON aj.id_persona = p.id
                LEFT JOIN estado_cuenta.persona j ON j.id = aj.id_jefe
                LEFT JOIN estado_cuenta.vacantes_personal v ON v.id = aj.id_vacante_jefe
                WHERE p.id = :id
                GROUP BY p.id
                LIMIT 1
            ", ['id' => $idPersona]);
            return is_array($row) ? $row : [];
        } catch (\Throwable $e) {
            error_log('CapHum::snapshotPersonaAuditoria -> ' . $e->getMessage());
            return [];
        }
    }

    public static function snapshotCandidatoAuditoria(int $idCandidato): array
    {
        if ($idCandidato <= 0) {
            return [];
        }
        try {
            $db = new Database();
            $row = $db->queryOne("
                SELECT
                    c.id,
                    c.nombres,
                    c.segundo_nombre,
                    c.apellidop,
                    c.apellidom,
                    TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(c.nombres), ''),
                        NULLIF(TRIM(c.segundo_nombre), ''),
                        NULLIF(TRIM(c.apellidop), ''),
                        NULLIF(TRIM(c.apellidom), '')
                    )) AS nombre_completo,
                    c.email,
                    c.telefono,
                    c.id_puesto,
                    pu.nombre AS puesto,
                    c.id_departamento,
                    dep.nombre AS departamento,
                    c.id_posible_jefe,
                    TRIM(CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom)) AS posible_jefe,
                    c.id_jefe_divisional,
                    TRIM(CONCAT_WS(' ', divi.nombres, divi.segundo_nombre, divi.apellidop, divi.apellidom)) AS jefe_divisional,
                    c.estatus,
                    c.fecha_postulacion,
                    c.fecha_ingreso_programada,
                    c.postulacion_enviada,
                    c.notas
                FROM estado_cuenta.candidatos c
                LEFT JOIN estado_cuenta.puesto pu ON pu.id = c.id_puesto
                LEFT JOIN estado_cuenta.departamento dep ON dep.id = c.id_departamento
                LEFT JOIN estado_cuenta.persona jefe ON jefe.id = c.id_posible_jefe
                LEFT JOIN estado_cuenta.persona divi ON divi.id = c.id_jefe_divisional
                WHERE c.id = :id
                LIMIT 1
            ", ['id' => $idCandidato]);
            return is_array($row) ? $row : [];
        } catch (\Throwable $e) {
            error_log('CapHum::snapshotCandidatoAuditoria -> ' . $e->getMessage());
            return [];
        }
    }

    public static function snapshotModulosAccesoCapitalHumano(int $idPersona): array
    {
        if ($idPersona <= 0) {
            return [];
        }
        try {
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            $idsSql = self::idsGestionablesAccesosCapitalHumanoSql();
            $rows = $db->queryAll("
                SELECT mw.id, mw.nombre, mw.pestana
                FROM estado_cuenta.asigna_modulo_web am
                INNER JOIN estado_cuenta.modulos_web mw ON mw.id = am.modulo_web_id
                WHERE am.usuario_id = :id
                  AND am.modulo_web_id IN ($idsSql)
                ORDER BY mw.id ASC
            ", ['id' => $idPersona]) ?: [];
            return [
                'ids' => array_map('intval', array_column($rows, 'id')),
                'nombres' => array_values(array_map(static function ($r) {
                    return trim((string)($r['nombre'] ?? ''));
                }, $rows)),
            ];
        } catch (\Throwable $e) {
            error_log('CapHum::snapshotModulosAccesoCapitalHumano -> ' . $e->getMessage());
            return [];
        }
    }

    public static function registrarAuditoriaInternaRrhh(array $data): void
    {
        try {
            $db = new Database();
            self::asegurarAuditoriaInternaRrhh($db);

            $idUsuario = (int)($data['id_usuario'] ?? ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? 0));
            $usuarioNombre = self::auditoriaTexto($data['usuario_nombre'] ?? ($_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? ''), 220);
            if ($usuarioNombre === null && $idUsuario > 0) {
                $u = $db->queryOne("
                    SELECT TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre
                    FROM estado_cuenta.persona
                    WHERE id = :id
                    LIMIT 1
                ", ['id' => $idUsuario]);
                $usuarioNombre = self::auditoriaTexto($u['nombre'] ?? '', 220);
            }

            $cambios = self::auditoriaSanitizar($data['cambios'] ?? []);
            $detalle = self::auditoriaSanitizar($data['detalle'] ?? []);
            $db->CRUD("
                INSERT INTO estado_cuenta.caphum_auditoria_interna
                    (fecha_hora, id_usuario, usuario_nombre, ip, modulo, entidad_tipo, entidad_id, entidad_nombre, accion, resumen, cambios_json, detalle_json)
                VALUES
                    (NOW(), :id_usuario, :usuario_nombre, :ip, :modulo, :entidad_tipo, :entidad_id, :entidad_nombre, :accion, :resumen, :cambios_json, :detalle_json)
            ", [
                'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
                'usuario_nombre' => $usuarioNombre,
                'ip' => self::auditoriaTexto($data['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''), 64),
                'modulo' => self::auditoriaTexto($data['modulo'] ?? 'Capital Humano', 80) ?: 'Capital Humano',
                'entidad_tipo' => self::auditoriaTexto($data['entidad_tipo'] ?? 'general', 80) ?: 'general',
                'entidad_id' => isset($data['entidad_id']) ? (int)$data['entidad_id'] : null,
                'entidad_nombre' => self::auditoriaTexto($data['entidad_nombre'] ?? '', 255),
                'accion' => self::auditoriaTexto($data['accion'] ?? 'evento', 80) ?: 'evento',
                'resumen' => self::auditoriaTexto($data['resumen'] ?? '', 500),
                'cambios_json' => !empty($cambios) ? json_encode($cambios, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'detalle_json' => !empty($detalle) ? json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ]);
        } catch (\Throwable $e) {
            error_log('CapHum::registrarAuditoriaInternaRrhh -> ' . $e->getMessage());
        }
    }

    private static function limpiarCambiosAuditoriaInternaParaMostrar(array $evento, array $cambios): array
    {
        $accion = (string)($evento['accion'] ?? '');
        if (!in_array($accion, ['editar_usuario', 'editar_usuario_parcial', 'editar_usuario_rrhh'], true)) {
            return $cambios;
        }

        $camposDomicilio = [
            'id_pais',
            'id_div_nivel1',
            'id_div_nivel2',
            'id_div_nivel3',
            'domicilio_calle_texto',
            'domicilio_num_exterior',
            'domicilio_num_interior',
            'codigo_postal',
        ];

        foreach ($camposDomicilio as $campo) {
            $despues = trim((string)($cambios[$campo]['despues'] ?? ''));
            if ($despues === '' || $despues === '-') {
                unset($cambios[$campo]);
            }
        }

        $jefeDespues = trim((string)($cambios['jefe']['despues'] ?? ''));
        if ($jefeDespues === '' || $jefeDespues === '-') {
            unset($cambios['jefe']);
        }

        return $cambios;
    }

    public static function resumenAuditoriaUsuarioDesdeCambios(array $cambios, string $sujeto = 'usuario'): string
    {
        if (empty($cambios)) {
            return 'Se guardo el ' . $sujeto . ' sin cambios detectables.';
        }

        $campos = [
            'numero_empleado' => ['texto' => 'el numero de empleado', 'lista' => 'numero de empleado'],
            'codigo_contpac' => ['texto' => 'el codigo ContPAQ', 'lista' => 'codigo ContPAQ'],
            'nombres' => ['texto' => 'el nombre', 'lista' => 'nombre'],
            'segundo_nombre' => ['texto' => 'el segundo nombre', 'lista' => 'segundo nombre'],
            'apellidop' => ['texto' => 'el apellido paterno', 'lista' => 'apellido paterno'],
            'apellidom' => ['texto' => 'el apellido materno', 'lista' => 'apellido materno'],
            'curp' => ['texto' => 'la CURP', 'lista' => 'CURP'],
            'correo' => ['texto' => 'el correo', 'lista' => 'correo'],
            'telefono_uno' => ['texto' => 'el telefono', 'lista' => 'telefono'],
            'telefono_dos' => ['texto' => 'el telefono secundario', 'lista' => 'telefono secundario'],
            'user_name' => ['texto' => 'el usuario de acceso', 'lista' => 'usuario de acceso'],
            'estatus' => ['texto' => 'el estatus', 'lista' => 'estatus'],
            'fecha_ingreso' => ['texto' => 'la fecha de ingreso', 'lista' => 'fecha de ingreso'],
            'id_pais' => ['texto' => 'el pais', 'lista' => 'pais'],
            'id_div_nivel1' => ['texto' => 'la sede', 'lista' => 'sede'],
            'id_div_nivel2' => ['texto' => 'el estado o municipio', 'lista' => 'estado o municipio'],
            'id_div_nivel3' => ['texto' => 'la colonia', 'lista' => 'colonia'],
            'domicilio_calle_texto' => ['texto' => 'el domicilio', 'lista' => 'domicilio'],
            'domicilio_num_exterior' => ['texto' => 'el numero exterior', 'lista' => 'numero exterior'],
            'domicilio_num_interior' => ['texto' => 'el numero interior', 'lista' => 'numero interior'],
            'codigo_postal' => ['texto' => 'el codigo postal', 'lista' => 'codigo postal'],
            'puestos' => ['texto' => 'el puesto', 'lista' => 'puesto'],
            'departamentos' => ['texto' => 'el departamento', 'lista' => 'departamento'],
            'jefe' => ['texto' => 'el jefe', 'lista' => 'jefe'],
        ];

        $labels = [];
        $textoUnico = null;
        foreach (array_keys($cambios) as $campo) {
            $info = $campos[(string)$campo] ?? null;
            $labels[] = $info['lista'] ?? str_replace('_', ' ', (string)$campo);
            if ($textoUnico === null) {
                $textoUnico = $info['texto'] ?? ('el campo ' . str_replace('_', ' ', (string)$campo));
            }
        }
        $labels = array_values(array_unique($labels));

        if (count($labels) === 1) {
            return 'Se edito ' . $textoUnico . ' del ' . $sujeto . '.';
        }

        return 'Se editaron estos campos del ' . $sujeto . ': ' . implode(', ', $labels) . '.';
    }

    public static function getAuditoriaInternaRrhh(array $filtros = []): array
    {
        try {
            $db = new Database();
            self::asegurarAuditoriaInternaRrhh($db);

            $where = [];
            $params = [];
            $tipo = trim((string)($filtros['tipo'] ?? ''));
            $accion = trim((string)($filtros['accion'] ?? ''));
            $q = trim((string)($filtros['q'] ?? ''));

            if ($tipo !== '' && $tipo !== 'todos') {
                $where[] = 'entidad_tipo = :tipo';
                $params['tipo'] = $tipo;
            }
            if ($accion !== '' && $accion !== 'todos') {
                $where[] = 'accion = :accion';
                $params['accion'] = $accion;
            }
            if ($q !== '') {
                $where[] = "(usuario_nombre LIKE :q OR entidad_nombre LIKE :q OR resumen LIKE :q OR accion LIKE :q OR modulo LIKE :q)";
                $params['q'] = '%' . $q . '%';
            }

            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
            $eventos = $db->queryAll("
                SELECT
                    id,
                    DATE_FORMAT(fecha_hora, '%Y-%m-%d %H:%i:%s') AS fecha_hora,
                    id_usuario,
                    usuario_nombre,
                    ip,
                    modulo,
                    entidad_tipo,
                    entidad_id,
                    entidad_nombre,
                    accion,
                    resumen,
                    cambios_json,
                    detalle_json
                FROM estado_cuenta.caphum_auditoria_interna
                $whereSql
                ORDER BY fecha_hora DESC, id DESC
                LIMIT 600
            ", $params) ?: [];

            foreach ($eventos as &$evento) {
                $evento['cambios'] = json_decode((string)($evento['cambios_json'] ?? ''), true) ?: [];
                $evento['detalle'] = json_decode((string)($evento['detalle_json'] ?? ''), true) ?: [];
                $evento['cambios'] = self::limpiarCambiosAuditoriaInternaParaMostrar($evento, $evento['cambios']);
                if (in_array((string)($evento['accion'] ?? ''), ['editar_usuario', 'editar_usuario_parcial', 'editar_usuario_rrhh'], true)) {
                    $sujeto = (string)($evento['accion'] ?? '') === 'editar_usuario_rrhh' ? 'usuario RR.HH.' : 'usuario';
                    $evento['resumen'] = self::resumenAuditoriaUsuarioDesdeCambios($evento['cambios'], $sujeto);
                }
                unset($evento['cambios_json'], $evento['detalle_json']);
            }
            unset($evento);

            $totales = $db->queryOne("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN entidad_tipo = 'persona' THEN 1 ELSE 0 END) AS personas,
                    SUM(CASE WHEN entidad_tipo = 'candidato' THEN 1 ELSE 0 END) AS candidatos,
                    SUM(CASE WHEN entidad_tipo = 'permisos' THEN 1 ELSE 0 END) AS permisos,
                    SUM(CASE WHEN fecha_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) AS ultimas_24h
                FROM estado_cuenta.caphum_auditoria_interna
            ") ?: [];

            $acciones = $db->queryAll("
                SELECT accion, COUNT(*) AS total
                FROM estado_cuenta.caphum_auditoria_interna
                GROUP BY accion
                ORDER BY total DESC, accion ASC
            ") ?: [];

            return self::resultado(true, 'Auditoria interna cargada.', [
                'eventos' => $eventos,
                'totales' => [
                    'total' => (int)($totales['total'] ?? 0),
                    'personas' => (int)($totales['personas'] ?? 0),
                    'candidatos' => (int)($totales['candidatos'] ?? 0),
                    'permisos' => (int)($totales['permisos'] ?? 0),
                    'ultimas_24h' => (int)($totales['ultimas_24h'] ?? 0),
                ],
                'acciones' => $acciones,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo cargar la auditoria interna.', [
                'eventos' => [],
                'totales' => [],
                'acciones' => [],
            ], $e->getMessage());
        }
    }

    private static function asegurarSalariosSensiblesRrhh(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_salarios_sensibles (
                id_persona INT NOT NULL PRIMARY KEY,
                salario_cifrado TEXT NOT NULL,
                moneda VARCHAR(8) NOT NULL DEFAULT 'MXN',
                creado_en DATETIME NOT NULL,
                actualizado_en DATETIME NOT NULL,
                id_usuario_actualizacion INT NULL,
                KEY idx_usuario_actualizacion (id_usuario_actualizacion),
                KEY idx_actualizado_en (actualizado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.auditoria_salarios_sensibles_rrhh (
                id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                fecha_hora DATETIME NOT NULL,
                id_usuario INT NULL,
                usuario_nombre VARCHAR(191) NULL,
                id_persona INT NULL,
                persona_nombre VARCHAR(191) NULL,
                accion VARCHAR(40) NOT NULL,
                resultado VARCHAR(40) NOT NULL,
                ip VARCHAR(80) NULL,
                user_agent VARCHAR(255) NULL,
                detalle TEXT NULL,
                KEY idx_fecha_hora (fecha_hora),
                KEY idx_usuario (id_usuario),
                KEY idx_persona (id_persona)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function normalizarSalarioSensible($salario)
    {
        $valor = trim((string)($salario ?? ''));
        if ($valor === '') {
            return null;
        }
        $valor = str_replace(['$', ' ', ','], '', $valor);
        if (!is_numeric($valor)) {
            return false;
        }
        $numero = (float)$valor;
        if ($numero < 0 || $numero > 999999999.99) {
            return false;
        }
        return number_format($numero, 2, '.', '');
    }

    private static function asegurarTotpDocumentosSensibles(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_documentos_sensibles_totp (
                id_persona INT NOT NULL PRIMARY KEY,
                secret VARCHAR(255) NOT NULL,
                confirmado TINYINT(1) NOT NULL DEFAULT 0,
                creado_en DATETIME NOT NULL,
                actualizado_en DATETIME NOT NULL,
                ultimo_uso_en DATETIME NULL,
                KEY idx_confirmado (confirmado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        try {
            $db->CRUD("ALTER TABLE estado_cuenta.rrhh_documentos_sensibles_totp MODIFY secret VARCHAR(255) NOT NULL");
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarTotpDocumentosSensibles alter secret -> ' . $e->getMessage());
        }
    }

    private static function esSecretoTotpCifrado(string $valor): bool
    {
        return strpos($valor, 'enc:v1:') === 0;
    }

    private static function claveMaestraDocumentosSensibles(): string
    {
        $env = trim((string)(getenv('RRHH_TOTP_ENCRYPTION_KEY') ?: ''));
        if ($env !== '') {
            $decoded = base64_decode($env, true);
            if (is_string($decoded) && strlen($decoded) >= 32) {
                return substr($decoded, 0, 32);
            }
            if (ctype_xdigit($env) && strlen($env) >= 64) {
                return substr(hex2bin(substr($env, 0, 64)), 0, 32);
            }
            return substr(hash('sha256', $env, true), 0, 32);
        }

        $configDir = defined('RAIZ') ? (RAIZ . DIRECTORY_SEPARATOR . 'config') : (__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config');
        $keyFile = $configDir . DIRECTORY_SEPARATOR . 'rrhh_sensitive.key';
        if (!is_dir($configDir)) {
            @mkdir($configDir, 0770, true);
        }
        if (!is_file($keyFile)) {
            @file_put_contents($keyFile, base64_encode(random_bytes(32)), LOCK_EX);
            @chmod($keyFile, 0600);
        }
        $key = trim((string)@file_get_contents($keyFile));
        $decoded = base64_decode($key, true);
        if (!is_string($decoded) || strlen($decoded) < 32) {
            $decoded = random_bytes(32);
            @file_put_contents($keyFile, base64_encode($decoded), LOCK_EX);
            @chmod($keyFile, 0600);
        }
        return substr($decoded, 0, 32);
    }

    private static function cifrarSecretoTotp(string $secret): string
    {
        if ($secret === '' || self::esSecretoTotpCifrado($secret)) {
            return $secret;
        }
        return self::cifrarValorSensibleRrhh($secret);
    }

    private static function cifrarValorSensibleRrhh(string $valor): string
    {
        if ($valor === '' || self::esSecretoTotpCifrado($valor)) {
            return $valor;
        }
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt(
            $valor,
            'aes-256-gcm',
            self::claveMaestraDocumentosSensibles(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        if ($cipher === false) {
            throw new \RuntimeException('No se pudo cifrar el valor sensible.');
        }
        return 'enc:v1:' . base64_encode($iv . $tag . $cipher);
    }

    private static function descifrarSecretoTotp(string $secret): string
    {
        return self::descifrarValorSensibleRrhh($secret);
    }

    private static function descifrarValorSensibleRrhh(string $secret): string
    {
        if ($secret === '' || !self::esSecretoTotpCifrado($secret)) {
            return $secret;
        }
        $payload = base64_decode(substr($secret, 7), true);
        if (!is_string($payload) || strlen($payload) <= 28) {
            return '';
        }
        $iv = substr($payload, 0, 12);
        $tag = substr($payload, 12, 16);
        $cipher = substr($payload, 28);
        $plain = openssl_decrypt(
            $cipher,
            'aes-256-gcm',
            self::claveMaestraDocumentosSensibles(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        return is_string($plain) ? $plain : '';
    }

    /**
     * Guardar documentos de una persona
     */
    public static function guardarDocumentosPersona($id_persona, $id_documento, $archivos)
    {
        try {
            $db = new Database();

            $archivosGuardados = [];

            foreach ($archivos as $nombreArchivo) {
                $archivoEsc = addslashes($nombreArchivo);

                $db->queryOne("
                    INSERT INTO estado_cuenta.carga_documento_persona
                    (id_persona, id_documento, archivo, fecha_carga)
                    VALUES
                    (:id_persona, :id_documento, :archivo, NOW())
                ", [
                    'id_persona' => $id_persona,
                    'id_documento' => $id_documento,
                    'archivo' => $archivoEsc
                ]);

                $archivosGuardados[] = $nombreArchivo;
            }

            return self::resultado(true, 'Documentos guardados correctamente.', $archivosGuardados);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documentos.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar documento de una persona
     */
    public static function eliminarDocumentoPersona($id_documento_carga)
    {
        try {
            $db = new Database();

            // Primero obtener el nombre del archivo para eliminarlo físicamente
            $documento = $db->queryOne("
                SELECT archivo, id_documento
                FROM estado_cuenta.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            if (!$documento) {
                return self::resultado(false, 'Documento no encontrado.');
            }

            $nombreArchivo = $documento['archivo'];
            $id_documento = $documento['id_documento'];

            // Eliminar de la base de datos
            $db->queryOne("
                DELETE FROM estado_cuenta.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            // Eliminar archivo físico (puede estar en diferentes carpetas según el tipo)
            $carpetas = [
                15 => 'bajas',    // Documento baja
                16 => 'reingresos', // Documento reingreso
                'default' => 'documentos'
            ];

            $carpeta = $carpetas[$id_documento] ?? $carpetas['default'];
            $rutaArchivo = sparta_uploads_join($carpeta, $nombreArchivo);

            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }

            return self::resultado(true, 'Documento eliminado correctamente.');

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar documento.', null, $e->getMessage());
        }
    }

    public static function getPersonaDetallePerfil($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }

        try {
            $db = new Database();

            $query = <<<SQL
            SELECT
                p.id,
                p.numero_empleado,
                p.nombres,
                p.segundo_nombre,
                p.apellidop,
                p.apellidom,
                p.correo,
                p.user_name,
                p.estatus,
                COALESCE(pais.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais.codigo_iso, 'xx') AS codigo_iso_pais
            FROM persona p
            LEFT JOIN paises pais
                   ON pais.id = p.id_pais
            WHERE p.id = $idPersona
              AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
            LIMIT 1
        SQL;

            $query_perfiles = <<<SQL
            SELECT
                $idPersona AS usuario_id,
                m.id AS modulo_id,
                CASE WHEN m.id = 27 THEN 'Panel Admin' ELSE m.nombre END AS modulo_nombre,
                m.pestana,
                m.descripcion,
                m.activo,
                CASE
                    WHEN a.usuario_id IS NOT NULL
                      OR (m.id = 194 AND $idPersona = 878) THEN 'Asignado'
                    ELSE 'No asignado'
                END AS estado,
                CASE
                    WHEN a.usuario_id IS NOT NULL
                      OR (m.id = 194 AND $idPersona = 878) THEN 1
                    ELSE 0
                END AS asignado_flag,
                CASE
                    WHEN m.id = 194 AND $idPersona = 878 THEN 1
                    ELSE 0
                END AS asignacion_forzada
            FROM modulos_web m
            LEFT JOIN asigna_modulo_web a
                ON a.usuario_id = $idPersona
                AND (a.modulo_web_id = m.id OR (m.id = 27 AND a.modulo_web_id IN (25)))
            WHERE m.activo = 1
              AND m.id NOT IN (25)
            ORDER BY m.id;
        SQL;

            $query_puestos= <<<SQL
               SELECT
                p.id AS id_puesto,
                p.nombre AS nombre_puesto,
                p.nivel as nivel,
                d.id AS id_departamento,
                d.nombre AS nombre_departamento,
                dorg.id AS id_area,
                dorg.nombre AS nombre_area,
                dir.id AS id_direccion,
                dir.nombre AS nombre_direccion,
                COALESCE(pa.nombre, 'Sin país') AS nombre_pais,
                CASE
                    WHEN p2.idPersona IS NULL THEN 'No asignado'
                    ELSE 'Asignado'
                END AS estado,
                CASE
                                WHEN p2.idPersona IS NULL THEN 0
                                ELSE 1
                            END AS asignado_flag
            FROM puesto p
            LEFT JOIN privilegios_departamento p2 ON p.id = p2.idPuesto AND p2.idPersona  = $idPersona
            LEFT JOIN departamento d ON d.id = p.departamento_id
            LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = d.id_departamento_organizacional AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
            LEFT JOIN paises pa ON pa.id = COALESCE(dorg.id_pais, d.id_pais)
            WHERE COALESCE(p.activo, 1) = 1
              AND COALESCE(d.activo, 1) = 1
              AND COALESCE(dorg.activo, 1) = 1
            ORDER BY d.id, p.nivel desc
        SQL;

            $query_asignacion_actual = <<<SQL
            SELECT
                d.nombre AS nombre_departamento,
                pp.nombre AS nombre_puesto,
                dorg.nombre AS nombre_area,
                dir.nombre AS nombre_direccion,
                COALESCE(pa.nombre, 'Sin país') AS nombre_pais
            FROM asigna_puesto ap
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = d.id_departamento_organizacional AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
            LEFT JOIN paises pa ON pa.id = COALESCE(dorg.id_pais, d.id_pais)
            WHERE ap.id_persona = $idPersona
              AND COALESCE(ap.activo, 1) = 1
            ORDER BY pp.nivel ASC
            LIMIT 1
        SQL;


            $persona = $db->queryOne($query);
            $perfiles = $db->queryAll($query_perfiles);
            $perfiles = self::agregarModuloConveniosDescargarExcelSiFalta($perfiles, $idPersona, $db);
            require_once __DIR__ . '/../config/menu_modulos_sidebar.php';
            $perfiles = enriquecerPerfilesModulosConMenuSidebar($perfiles);
            // La pestana "Acceso a Puestos" ya se alimenta de permisos_jerarquia.
            // Evitamos mandar el catalogo legacy completo para que el modal abra mas rapido.
            $puestos = [];
            $asignacionActual = $db->queryOne($query_asignacion_actual);

            return self::resultado(true, 'Persona encontrada.', [
                'persona' => $persona,
                'perfiles' => $perfiles,
                'puestos' => $puestos,
                'asignacion_actual' => $asignacionActual,
                'permisos_jerarquia' => null,
                'permisos_jerarquia_diferida' => true
            ]);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    private static function idsAccesosCapitalHumanoSql(): string
    {
        return implode(',', array_map('intval', self::MODULOS_ACCESOS_CAPITAL_HUMANO_IDS));
    }

    private static function modulosGestionablesAccesoCapitalHumano(): array
    {
        $noGestionablesDesdeModal = [
            self::MODULO_VALIDADOR_DOCUMENTAL_CANDIDATOS,
            self::MODULO_VALIDAR_CARTA_COMPROMISO_GESTOR,
        ];

        return array_values(array_filter(
            self::MODULOS_ACCESOS_CAPITAL_HUMANO_IDS,
            static fn ($id) => !in_array((int) $id, $noGestionablesDesdeModal, true)
        ));
    }

    private static function idsGestionablesAccesosCapitalHumanoSql(): string
    {
        return implode(',', array_map('intval', self::modulosGestionablesAccesoCapitalHumano()));
    }

    private static function grupoModuloAccesoCapitalHumano(int $id, string $pestana, string $nombre): array
    {
        if ($id === self::MODULO_VER_DOCUMENTOS_SENSIBLES_RRHH || in_array($id, self::MODULOS_DOCUMENTO_RRHH, true)) {
            return ['grupo' => 'Control documental RR.HH.', 'icono' => 'fa fa-folder-open', 'orden' => 28];
        }
        if (in_array($id, [self::MODULO_RESET_TOTP_DOCUMENTOS_SENSIBLES_RRHH, self::MODULO_VER_SALARIO_SENSIBLE_RRHH], true)) {
            return ['grupo' => 'Modulos Capital Humano', 'icono' => 'fa-solid fa-users', 'orden' => 10];
        }
        if ($id >= 107 && $id <= 127) {
            return ['grupo' => 'Edicion cobranza', 'icono' => 'fa-solid fa-pen-to-square', 'orden' => 30];
        }
        if (in_array($id, [94, 95, 96, 97, 98, 99, 101, 103, 143], true)) {
            return ['grupo' => 'Gestiones de personal', 'icono' => 'fa-solid fa-users-gear', 'orden' => 20];
        }
        if (in_array($id, [104, 105, 142], true)) {
            return ['grupo' => 'Seleccion de personal', 'icono' => 'fa-solid fa-user-check', 'orden' => 25];
        }
        if ($id === self::MODULO_ACCESOS_CAPITAL_HUMANO) {
            return ['grupo' => 'Administracion de accesos', 'icono' => 'fa-solid fa-user-shield', 'orden' => 40];
        }
        if (strcasecmp(trim($pestana), 'Permisos especiales') === 0) {
            return ['grupo' => 'Permisos especiales', 'icono' => 'fa-solid fa-shield-halved', 'orden' => 35];
        }

        return ['grupo' => 'Modulos Capital Humano', 'icono' => 'fa-solid fa-users', 'orden' => 10];
    }

    private static function filtroUsuariosAccesosCapitalHumanoSql(): string
    {
        $direccion = "LOWER(CONVERT(COALESCE(NULLIF(TRIM(pdr.direccion_organizacional), ''), dir.nombre, '') USING utf8mb4)) COLLATE utf8mb4_general_ci";
        $area = "LOWER(CONVERT(COALESCE(NULLIF(TRIM(pdr.area_texto), ''), dorg.nombre, '') USING utf8mb4)) COLLATE utf8mb4_general_ci";
        $departamento = "LOWER(CONVERT(COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') USING utf8mb4)) COLLATE utf8mb4_general_ci";

        return "
                  AND (
                      (
                          $direccion LIKE '%administracion%finanzas%'
                          AND $area LIKE '%recursos%humanos%'
                          AND $departamento NOT LIKE '%servicios%generales%'
                          AND $departamento NOT LIKE '%mantenimiento%'
                      )
                      -- Miembro adicional autorizado para administrar accesos sin cambiar su adscripción laboral.
                      OR p.id IN (1090)
                  )
        ";
    }

    public static function getAccesosCapitalHumano(): array
    {
        try {
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            $idsSql = self::idsGestionablesAccesosCapitalHumanoSql();
            $filtroAlcance = self::filtroUsuariosAccesosCapitalHumanoSql();

            $usuarios = $db->queryAll("
                SELECT
                    p.id AS persona_id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.nombres), ''),
                        NULLIF(TRIM(p.segundo_nombre), ''),
                        NULLIF(TRIM(p.apellidop), ''),
                        NULLIF(TRIM(p.apellidom), '')
                    )) AS nombre,
                    p.correo,
                    p.user_name,
                    p.estatus,
                    CONVERT(COALESCE(NULLIF(TRIM(p.telefono_uno), ''), NULLIF(TRIM(p.telefono_dos), ''), '') USING utf8mb4) COLLATE utf8mb4_general_ci AS telefono,
                    pf.foto AS foto_perfil,
                    COALESCE(NULLIF(TRIM(pdr.puesto_texto), ''), pu.nombre, '') AS puesto,
                    COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') AS departamento,
                    COALESCE(NULLIF(TRIM(pdr.area_texto), ''), dorg.nombre, '') AS area,
                    COALESCE(NULLIF(TRIM(pdr.direccion_organizacional), ''), dir.nombre, '') AS direccion,
                    COALESCE(pa.nombre, '') AS pais,
                    COALESCE(pa.codigo_iso, 'mx') AS codigo_iso_pais,
                    COALESCE(TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)), '') AS jefe_nombre,
                    COALESCE(am.total_modulos_ch, 0) AS total_modulos_ch,
                    COALESCE(am.tiene_accesos_ch, 0) AS tiene_accesos_ch
                FROM persona p
                LEFT JOIN perfil pf ON pf.id_persona = p.id
                LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                LEFT JOIN departamento_organizacional dorg ON dorg.id = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                   AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                LEFT JOIN paises pa ON pa.id = p.id_pais
                LEFT JOIN (
                    SELECT id_persona, MAX(id) AS id_ultimo
                    FROM asigna_jefe
                    GROUP BY id_persona
                ) aj_ult ON aj_ult.id_persona = p.id
                LEFT JOIN asigna_jefe aj ON aj.id = aj_ult.id_ultimo
                LEFT JOIN vacantes_personal vp ON vp.id = aj.id_vacante_jefe
                LEFT JOIN persona pj ON pj.id = COALESCE(aj.id_jefe, vp.id_jefe)
                LEFT JOIN (
                    SELECT
                        usuario_id,
                        COUNT(*) AS total_modulos_ch,
                        MAX(CASE WHEN modulo_web_id = " . (int) self::MODULO_ACCESOS_CAPITAL_HUMANO . " THEN 1 ELSE 0 END) AS tiene_accesos_ch
                    FROM asigna_modulo_web
                    WHERE modulo_web_id IN ($idsSql)
                    GROUP BY usuario_id
                ) am ON am.usuario_id = p.id
                WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                $filtroAlcance
                ORDER BY CASE WHEN p.estatus = 'Activo' THEN 0 ELSE 1 END, nombre ASC
            ");

            $totales = [
                'total' => count($usuarios),
                'activos' => 0,
                'inactivos' => 0,
                'con_permisos_ch' => 0,
                'sin_permisos_ch' => 0,
                'con_acceso_panel' => 0,
            ];
            foreach ($usuarios as $u) {
                if (strcasecmp((string)($u['estatus'] ?? ''), 'Activo') === 0) {
                    $totales['activos']++;
                } else {
                    $totales['inactivos']++;
                }
                if ((int)($u['total_modulos_ch'] ?? 0) > 0) {
                    $totales['con_permisos_ch']++;
                } else {
                    $totales['sin_permisos_ch']++;
                }
                if ((int)($u['tiene_accesos_ch'] ?? 0) === 1) {
                    $totales['con_acceso_panel']++;
                }
            }

            return self::resultado(true, 'Usuarios cargados.', [
                'usuarios' => $usuarios,
                'totales' => $totales,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudieron cargar los accesos de Capital Humano.', [
                'usuarios' => [],
                'totales' => ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'con_permisos_ch' => 0, 'sin_permisos_ch' => 0, 'con_acceso_panel' => 0],
            ], $e->getMessage());
        }
    }

    public static function getAccesoCapitalHumanoDetalle(int $idPersona): array
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'Usuario invalido.');
            }
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            $idsSql = self::idsGestionablesAccesosCapitalHumanoSql();
            $filtroAlcance = self::filtroUsuariosAccesosCapitalHumanoSql();

            $usuario = $db->queryOne("
                SELECT
                    p.id AS persona_id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                    p.correo,
                    p.user_name,
                    p.password,
                    p.estatus,
                    COALESCE(NULLIF(TRIM(pdr.puesto_texto), ''), pu.nombre, '') AS puesto,
                    COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') AS departamento
                FROM persona p
                LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                LEFT JOIN departamento_organizacional dorg ON dorg.id = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                   AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                WHERE p.id = :id
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                  $filtroAlcance
                LIMIT 1
            ", ['id' => $idPersona]);
            if (!$usuario) {
                return self::resultado(false, 'No se encontro el usuario en Capital Humano.');
            }

            $modulos = $db->queryAll("
                SELECT
                    m.id,
                    m.nombre,
                    m.pestana,
                    m.descripcion,
                    CASE WHEN am.usuario_id IS NULL THEN 0 ELSE 1 END AS asignado
                FROM modulos_web m
                LEFT JOIN asigna_modulo_web am
                       ON am.modulo_web_id = m.id
                      AND am.usuario_id = :persona_id
                WHERE COALESCE(m.activo, 1) = 1
                  AND m.id IN ($idsSql)
                ORDER BY FIELD(m.id, $idsSql), m.nombre ASC
            ", ['persona_id' => $idPersona]);

            foreach ($modulos as &$m) {
                $meta = self::grupoModuloAccesoCapitalHumano(
                    (int)($m['id'] ?? 0),
                    (string)($m['pestana'] ?? ''),
                    (string)($m['nombre'] ?? '')
                );
                $m['grupo_ch'] = $meta['grupo'];
                $m['grupo_icono'] = $meta['icono'];
                $m['grupo_orden'] = $meta['orden'];
            }
            unset($m);
            usort($modulos, static function (array $a, array $b): int {
                $ga = (int)($a['grupo_orden'] ?? 999);
                $gb = (int)($b['grupo_orden'] ?? 999);
                if ($ga !== $gb) {
                    return $ga <=> $gb;
                }
                return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
            });

            return self::resultado(true, 'Detalle cargado.', [
                'usuario' => $usuario,
                'modulos' => $modulos,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo cargar el detalle del acceso Capital Humano.', null, $e->getMessage());
        }
    }

    public static function guardarPermisosAccesoCapitalHumano(array $input): array
    {
        $db = null;
        try {
            $idPersona = (int)($input['id_persona'] ?? 0);
            if ($idPersona <= 0) {
                return self::resultado(false, 'Usuario invalido.');
            }
            $modulos = $input['modulos'] ?? [];
            if (!is_array($modulos)) {
                $modulos = [];
            }
            $permitidos = array_fill_keys(self::modulosGestionablesAccesoCapitalHumano(), true);
            $modulos = array_values(array_unique(array_filter(array_map('intval', $modulos), static function ($mid) use ($permitidos) {
                return isset($permitidos[$mid]);
            })));

            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            $idsSql = self::idsGestionablesAccesosCapitalHumanoSql();

            $usuario = $db->queryOne(
                "SELECT id FROM persona WHERE id = :id AND LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja') LIMIT 1",
                ['id' => $idPersona]
            );
            if (!$usuario) {
                return self::resultado(false, 'No se encontro el usuario en Capital Humano.');
            }

            if (!empty($modulos)) {
                $modulosActivos = $db->queryAll("
                    SELECT id
                    FROM modulos_web
                    WHERE COALESCE(activo, 1) = 1
                      AND id IN ($idsSql)
                      AND id IN (" . implode(',', array_map('intval', $modulos)) . ")
                ");
                $activos = array_fill_keys(array_map('intval', array_column($modulosActivos, 'id')), true);
                $modulos = array_values(array_filter($modulos, static function ($mid) use ($activos) {
                    return isset($activos[(int)$mid]);
                }));
            }

            $db->beginTransaction();
            $db->CRUD(
                "DELETE FROM asigna_modulo_web
                 WHERE usuario_id = :uid
                   AND modulo_web_id IN ($idsSql)",
                ['uid' => $idPersona]
            );
            foreach ($modulos as $moduloId) {
                $db->CRUD(
                    "INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                     VALUES (:uid, :mid)",
                    ['uid' => $idPersona, 'mid' => $moduloId]
                );
            }
            $db->CRUD(
                "UPDATE persona SET session_version = COALESCE(session_version, 1) + 1 WHERE id = :id",
                ['id' => $idPersona]
            );
            $db->commit();

            return self::resultado(true, 'Permisos de Capital Humano guardados.', [
                'persona_id' => $idPersona,
                'modulos' => $modulos,
            ]);
        } catch (\Throwable $e) {
            if ($db && $db->inTransaction()) {
                $db->rollback();
            }
            return self::resultado(false, 'No se pudieron guardar los permisos de Capital Humano.', null, $e->getMessage());
        }
    }

    private static function existeTablaPermisosJerarquicos(Database $db): bool
    {
        try {
            $row = $db->queryOne("SHOW TABLES LIKE 'privilegios_jerarquia'");
            return !empty($row);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function placeholdersIn(string $prefijo, array $valores, array &$params): string
    {
        $keys = [];
        foreach (array_values($valores) as $i => $valor) {
            $k = $prefijo . '_' . $i;
            $keys[] = ':' . $k;
            $params[$k] = (int) $valor;
        }
        return implode(', ', $keys);
    }

    private static function obtenerSeleccionesJerarquicas(Database $db, int $idPersona): array
    {
        $sel = ['pais' => [], 'empresa' => [], 'area' => [], 'departamento' => [], 'puesto' => []];
        if (!self::existeTablaPermisosJerarquicos($db)) {
            return $sel;
        }
        $rows = $db->queryAll(
            "SELECT nivel, id_nodo
             FROM privilegios_jerarquia
             WHERE id_persona = :id_persona",
            ['id_persona' => $idPersona]
        ) ?: [];
        foreach ($rows as $r) {
            $nivel = (string) ($r['nivel'] ?? '');
            if (!isset($sel[$nivel])) {
                continue;
            }
            $sel[$nivel][] = (int) ($r['id_nodo'] ?? 0);
        }
        foreach ($sel as $k => $ids) {
            $sel[$k] = array_values(array_unique(array_filter(array_map('intval', $ids))));
        }
        return $sel;
    }

    private static function sincronizarLegacyDesdeJerarquia(Database $db, int $idPersona, array $seleccion): void
    {
        $idsPais = array_values(array_unique(array_filter(array_map('intval', $seleccion['pais'] ?? []))));
        $idsEmpresa = array_values(array_unique(array_filter(array_map('intval', $seleccion['empresa'] ?? []))));
        $idsArea = array_values(array_unique(array_filter(array_map('intval', $seleccion['area'] ?? []))));
        $idsDepartamento = array_values(array_unique(array_filter(array_map('intval', $seleccion['departamento'] ?? []))));
        $idsPuesto = array_values(array_unique(array_filter(array_map('intval', $seleccion['puesto'] ?? []))));

        $puestosFinales = [];
        foreach ($idsPuesto as $idp) {
            $puestosFinales[$idp] = true;
        }

        if (!empty($idsDepartamento)) {
            $params = [];
            $in = self::placeholdersIn('dep', $idsDepartamento, $params);
            $rows = $db->queryAll(
                "SELECT p.id
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 WHERE p.departamento_id IN ($in)
                   AND COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1",
                $params
            ) ?: [];
            foreach ($rows as $r) {
                $puestosFinales[(int) ($r['id'] ?? 0)] = true;
            }
        }

        if (!empty($idsArea)) {
            $params = [];
            $in = self::placeholdersIn('area', $idsArea, $params);
            $rows = $db->queryAll(
                "SELECT p.id
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE d.id_departamento_organizacional IN ($in)
                   AND COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1",
                $params
            ) ?: [];
            foreach ($rows as $r) {
                $puestosFinales[(int) ($r['id'] ?? 0)] = true;
            }
        }

        if (!empty($idsEmpresa)) {
            $params = [];
            $in = self::placeholdersIn('emp', $idsEmpresa, $params);
            $rows = $db->queryAll(
                "SELECT p.id
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 LEFT JOIN asigna_direcciones ad
                        ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                       AND COALESCE(ad.activo, 1) = 1
                 LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                 WHERE COALESCE(d.id_empresa, a.id_empresa, dir.id_empresa, 1) IN ($in)
                   AND COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1",
                $params
            ) ?: [];
            foreach ($rows as $r) {
                $puestosFinales[(int) ($r['id'] ?? 0)] = true;
            }
        }

        if (!empty($idsPais)) {
            $params = [];
            $in = self::placeholdersIn('pais', $idsPais, $params);
            $rows = $db->queryAll(
                "SELECT p.id
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 INNER JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE a.id_pais IN ($in)
                   AND COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1",
                $params
            ) ?: [];
            foreach ($rows as $r) {
                $puestosFinales[(int) ($r['id'] ?? 0)] = true;
            }
        }

        $db->CRUD(
            "DELETE FROM privilegios_departamento WHERE idPersona = :id_persona",
            ['id_persona' => $idPersona]
        );

        foreach (array_keys($puestosFinales) as $idPuestoInsert) {
            if ($idPuestoInsert <= 0) {
                continue;
            }
            $db->CRUD(
                "INSERT INTO privilegios_departamento (idPersona, idPuesto)
                 VALUES (:id_persona, :id_puesto)",
                ['id_persona' => $idPersona, 'id_puesto' => (int) $idPuestoInsert]
            );
        }
    }

    public static function getPermisosJerarquicosPerfil(int $idPersona, ?Database $db = null): array
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }

        try {
            $db = $db ?: new Database();
            $seleccion = self::obtenerSeleccionesJerarquicas($db, $idPersona);

            // Fallback de compatibilidad: si aún no existe selección jerárquica,
            // preseleccionar puestos desde la tabla legacy.
            $totalSel = count($seleccion['pais']) + count($seleccion['empresa']) + count($seleccion['area']) + count($seleccion['departamento']) + count($seleccion['puesto']);
            if ($totalSel === 0) {
                $rowsLegacy = $db->queryAll(
                    "SELECT pd.idPuesto
                     FROM privilegios_departamento pd
                     INNER JOIN puesto p ON p.id = pd.idPuesto
                     INNER JOIN departamento d ON d.id = p.departamento_id
                     LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                     WHERE pd.idPersona = :id_persona
                       AND COALESCE(p.activo, 1) = 1
                       AND COALESCE(d.activo, 1) = 1
                       AND COALESCE(a.activo, 1) = 1",
                    ['id_persona' => $idPersona]
                ) ?: [];
                foreach ($rowsLegacy as $r) {
                    $idP = (int) ($r['idPuesto'] ?? 0);
                    if ($idP > 0) {
                        $seleccion['puesto'][] = $idP;
                    }
                }
                $seleccion['puesto'] = array_values(array_unique($seleccion['puesto']));
            }

            $paises = $db->queryAll(
                "SELECT id, nombre, COALESCE(codigo_iso, 'xx') AS codigo_iso
                 FROM paises
                 ORDER BY nombre ASC"
            ) ?: [];
            $areas = $db->queryAll(
                "SELECT
                    a.id,
                    a.nombre,
                    a.id_pais,
                    COALESCE(a.id_empresa, dir.id_empresa, 1) AS id_empresa,
                    COALESCE(emp.nombre_comercial, 'MaxiKash') AS nombre_empresa,
                    COALESCE(emp.razon_social, '') AS razon_social_empresa,
                    COALESCE(pa.nombre, 'Sin país') AS nombre_pais
                 FROM departamento_organizacional a
                 LEFT JOIN asigna_direcciones ad
                        ON ad.id_departamento_organizacional = a.id
                       AND COALESCE(ad.activo, 1) = 1
                 LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                 LEFT JOIN rrhh_empresas emp ON emp.id = COALESCE(a.id_empresa, dir.id_empresa, 1)
                 LEFT JOIN paises pa ON pa.id = a.id_pais
                 WHERE COALESCE(a.activo, 1) = 1
                 ORDER BY pa.nombre ASC, nombre_empresa ASC, a.nombre ASC"
            ) ?: [];
            $empresas = $db->queryAll(
                "SELECT DISTINCT
                    COALESCE(a.id_empresa, dir.id_empresa, 1) AS id,
                    COALESCE(emp.nombre_comercial, 'MaxiKash') AS nombre,
                    COALESCE(emp.razon_social, '') AS razon_social,
                    a.id_pais
                 FROM departamento_organizacional a
                 LEFT JOIN asigna_direcciones ad
                        ON ad.id_departamento_organizacional = a.id
                       AND COALESCE(ad.activo, 1) = 1
                 LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                 LEFT JOIN rrhh_empresas emp ON emp.id = COALESCE(a.id_empresa, dir.id_empresa, 1)
                 WHERE COALESCE(a.activo, 1) = 1
                 ORDER BY nombre ASC"
            ) ?: [];
            $departamentos = $db->queryAll(
                "SELECT
                    d.id,
                    d.nombre,
                    d.id_departamento_organizacional AS id_area
                 FROM departamento d
                 LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1
                 ORDER BY d.nombre ASC"
            ) ?: [];
            $puestos = $db->queryAll(
                "SELECT
                    p.id,
                    p.nombre,
                    p.nivel,
                    p.departamento_id AS id_departamento
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1
                 ORDER BY p.nivel DESC, p.nombre ASC"
            ) ?: [];

            return self::resultado(true, 'Permisos jerárquicos cargados.', [
                'paises' => $paises,
                'empresas' => $empresas,
                'areas' => $areas,
                'departamentos' => $departamentos,
                'puestos' => $puestos,
                'seleccion' => [
                    'pais' => array_values($seleccion['pais']),
                    'empresa' => array_values($seleccion['empresa']),
                    'area' => array_values($seleccion['area']),
                    'departamento' => array_values($seleccion['departamento']),
                    'puesto' => array_values($seleccion['puesto']),
                ],
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al obtener permisos jerárquicos.', null, $e->getMessage());
        }
    }

    public static function guardarPermisosJerarquicosPerfil(int $idPersona, array $seleccion): array
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }
        $permitidos = ['pais', 'empresa', 'area', 'departamento', 'puesto'];
        $limpia = ['pais' => [], 'empresa' => [], 'area' => [], 'departamento' => [], 'puesto' => []];
        foreach ($permitidos as $nivel) {
            $vals = $seleccion[$nivel] ?? [];
            if (!is_array($vals)) {
                $vals = [];
            }
            $limpia[$nivel] = array_values(array_unique(array_filter(array_map('intval', $vals))));
        }

        $db = new Database();
        try {
            $db->beginTransaction();

            if (self::existeTablaPermisosJerarquicos($db)) {
                $db->CRUD(
                    "DELETE FROM privilegios_jerarquia WHERE id_persona = :id_persona",
                    ['id_persona' => $idPersona]
                );
                foreach ($permitidos as $nivel) {
                    foreach ($limpia[$nivel] as $idNodo) {
                        $db->CRUD(
                            "INSERT INTO privilegios_jerarquia (id_persona, nivel, id_nodo)
                             VALUES (:id_persona, :nivel, :id_nodo)",
                            ['id_persona' => $idPersona, 'nivel' => $nivel, 'id_nodo' => (int) $idNodo]
                        );
                    }
                }
            }

            self::sincronizarLegacyDesdeJerarquia($db, $idPersona, $limpia);
            $db->commit();

            return self::resultado(true, 'Permisos jerárquicos guardados correctamente.', [
                'seleccion' => $limpia
            ]);
        } catch (\Throwable $e) {
            $db->rollback();
            return self::resultado(false, 'Error al guardar permisos jerárquicos.', null, $e->getMessage());
        }
    }

    public static function actualizarPermisoJerarquicoPerfil(int $idPersona, string $nivel, int $idNodo, int $asignado): array
    {
        $idPersona = (int) $idPersona;
        $idNodo = (int) $idNodo;
        $asignado = ((int) $asignado) === 1 ? 1 : 0;
        $nivel = trim(strtolower($nivel));
        if ($idPersona <= 0 || $idNodo <= 0 || !in_array($nivel, ['pais', 'empresa', 'area', 'departamento', 'puesto'], true)) {
            return self::resultado(false, 'Parámetros inválidos.', null);
        }

        try {
            $db = new Database();
            $seleccion = self::obtenerSeleccionesJerarquicas($db, $idPersona);
            $ids = array_values(array_unique(array_filter(array_map('intval', $seleccion[$nivel] ?? []))));

            if ($asignado === 1) {
                if (!in_array($idNodo, $ids, true)) {
                    $ids[] = $idNodo;
                }
            } else {
                $ids = array_values(array_filter($ids, function ($v) use ($idNodo) {
                    return (int) $v !== (int) $idNodo;
                }));
            }
            $seleccion[$nivel] = $ids;

            return self::guardarPermisosJerarquicosPerfil($idPersona, $seleccion);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al actualizar permiso jerárquico.', null, $e->getMessage());
        }
    }

    /**
     * Marca persona.force_logout = 1. SessionGuard cerrará la sesión en la próxima validación (~20 s).
     */
    public static function forzarLogoutPersona($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.');
        }

        try {
            $db = new Database();
            $persona = $db->queryOne(
                "SELECT id, estatus, force_logout
                   FROM persona
                  WHERE id = :id
                  LIMIT 1",
                ['id' => $idPersona]
            );

            if (!$persona) {
                return self::resultado(false, 'No se encontro el usuario indicado.');
            }

            $estatus = strtolower(trim((string) ($persona['estatus'] ?? '')));
            if (in_array($estatus, ['baja', 'transito de baja'], true)) {
                return self::resultado(false, 'No se puede forzar cierre porque el usuario esta en baja o en trámite de baja.');
            }

            if ((int) ($persona['force_logout'] ?? 0) === 1) {
                return self::resultado(
                    true,
                    'El cierre de sesion ya estaba solicitado. Se aplicara en cuanto el sistema valide la sesion del usuario.'
                );
            }

            $n = $db->CRUD(
                "UPDATE persona
                    SET force_logout = 1
                  WHERE id = :id",
                ['id' => $idPersona]
            );
            if ($n < 1) {
                return self::resultado(
                    false,
                    'No se pudo actualizar. Verifique que el usuario exista y no esté dado de baja.'
                );
            }

            return self::resultado(
                true,
                'Cierre de sesión solicitado. Se aplicará en cuanto el sistema valide la sesión del usuario (unos segundos).'
            );
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    public static function getComboDepartamentos($perfil_id = null)
    {
        $where = '';

        if (!empty($perfil_id)) {
            $perfil_id = intval($perfil_id); // 🔐 seguridad
            $where = "WHERE d.id = $perfil_id";
        }

        $query = <<<SQL
        SELECT DISTINCT d.*
        FROM privilegios_departamento pd
        INNER JOIN puesto p
            ON p.id = pd.idPuesto
        INNER JOIN departamento d
            ON d.id = p.departamento_id
        $where
        ORDER BY d.nombre ASC
    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaPuestos($departamento)
    {
        // Query base
        $query = <<<SQL
        SELECT
            p.id, p.nombre, p.nivel, d.nombre as departamento
        FROM puesto p
        INNER JOIN departamento d ON d.id = p.departamento_id
    SQL;

        $params = [];

        // Agregar filtro si se envió un departamento
        if ($departamento != null) {
            $query .= " WHERE d.id = :departamento";
            $params['departamento'] = $departamento;
        }

        try {
            $db = new Database();
            // Pasar parámetros si existen
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Puestos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /**
     * Puestos de un departamento que la persona puede asignar: solo los que tiene en privilegios_departamento.
     * Usado en Gestión de Usuarios (Agregar/Editar Puesto) para que solo se listen puestos a los que el usuario en sesión tiene acceso.
     */
    public static function getConsultaPuestosParaGestor($departamento, $id_persona)
    {
        $departamento = $departamento !== null ? (int) $departamento : 0;
        $id_persona = (int) $id_persona;
        if ($departamento <= 0 || $id_persona <= 0) {
            return self::resultado(true, 'Puestos encontrados.', []);
        }

        // Mismo criterio que getConsultaDepartamentoGestor: pd.idPersona y puesto por departamento
        $query = <<<SQL
        SELECT DISTINCT
            p.id, p.nombre, p.nivel, d.nombre as departamento
        FROM privilegios_departamento pd
        INNER JOIN puesto p ON p.id = pd.idPuesto
        INNER JOIN departamento d ON d.id = p.departamento_id
        WHERE pd.idPersona = $id_persona AND d.id = :departamento
        ORDER BY p.nivel ASC, p.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query, ['departamento' => $departamento]);
            return self::resultado(true, 'Puestos encontrados.', $r ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getRazonesAusencia()
    {
        self::asegurarRazonAusenciaFalta();

        // Query base
        $query = <<<SQL
        SELECT
            id,
            clave,
            nombre,
            descripcion
        FROM razon_ausencia
        WHERE activo = 1
        ORDER BY nombre
    SQL;

        $params = [];

        try {
            $db = new Database();

            // Ejecutar query (no requiere parámetros)
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Razones de ausencia encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener razones de ausencia.',
                null,
                $e->getMessage()
            );
        }
    }

    private static function asegurarRazonAusenciaFalta(): void
    {
        try {
            $db = new Database();
            $existe = $db->queryOne("
                SELECT id
                FROM estado_cuenta.razon_ausencia
                WHERE clave = 'FALTA'
                   OR UPPER(TRIM(nombre)) = 'FALTA'
                LIMIT 1
            ");

            if ($existe && !empty($existe['id'])) {
                $db->CRUD("
                    UPDATE estado_cuenta.razon_ausencia
                    SET clave = 'FALTA',
                        nombre = 'FALTA',
                        activo = 1
                    WHERE id = :id
                ", ['id' => (int) $existe['id']]);
                return;
            }

            $db->CRUD("
                INSERT INTO estado_cuenta.razon_ausencia
                    (clave, nombre, descripcion, activo)
                VALUES
                    ('FALTA', 'FALTA', 'Ausencia por falta', 1)
            ");
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarRazonAusenciaFalta -> ' . $e->getMessage());
        }
    }

    public static function getAusenciasPersona($idPersona)
    {
        $query = <<<SQL
        SELECT
            a.id,
            r.nombre AS razon,
            a.fecha_inicio,
            a.fecha_fin,
            a.descripcion,
            a.activo
        FROM ausencia a
        INNER JOIN razon_ausencia r ON r.id = a.id_razon
        WHERE a.id_persona = :idPersona
        ORDER BY a.fecha_inicio DESC
    SQL;

        $params = ['idPersona' => $idPersona];

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Ausencias encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener ausencias.',
                null,
                $e->getMessage()
            );
        }
    }




    public static function getConsultaJefe($id_departamento)
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $query = <<<SQL
          SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS nombre_puesto
        FROM asigna_puesto ap
        INNER JOIN persona per
            ON per.id = ap.id_persona
        INNER JOIN puesto pu
            ON pu.id = ap.id_puesto
        WHERE
            COALESCE(ap.activo, 1) = 1
            AND pu.es_jefe = 1 AND LOWER(TRIM(COALESCE(per.estatus, ''))) NOT IN ('baja', 'transito de baja')
            AND {$predPer}
            AND pu.departamento_id = $id_departamento
        ORDER BY per.nombres ASC;
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            $r = self::agregarJefasFuriaMotoSiAplica($db, $r, null, (int)$id_departamento);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    private static function obtenerEmpresaDepartamentoJefe(Database $db, ?int $idPuesto = null, ?int $idDepartamento = null): int
    {
        $params = [];
        $where = '';
        if ($idPuesto && $idPuesto > 0) {
            $where = 'pu.id = :id_puesto';
            $params['id_puesto'] = $idPuesto;
        } elseif ($idDepartamento && $idDepartamento > 0) {
            $where = 'dep.id = :id_departamento';
            $params['id_departamento'] = $idDepartamento;
        } else {
            return 0;
        }

        $row = $db->queryOne("
            SELECT COALESCE(dorg.id_empresa, dir.id_empresa, 1) AS id_empresa
            FROM estado_cuenta.departamento dep
            LEFT JOIN estado_cuenta.puesto pu ON pu.departamento_id = dep.id
            LEFT JOIN estado_cuenta.departamento_organizacional dorg
                   ON dorg.id = dep.id_departamento_organizacional
            LEFT JOIN estado_cuenta.asigna_direcciones ad
                   ON ad.id_departamento_organizacional = dorg.id
            LEFT JOIN estado_cuenta.direcciones_organizacion dir
                   ON dir.id = ad.id_direccion
            WHERE {$where}
            LIMIT 1
        ", $params);

        return (int)($row['id_empresa'] ?? 0);
    }

    private static function agregarJefasFuriaMotoSiAplica(Database $db, array $rows, ?int $idPuesto = null, ?int $idDepartamento = null): array
    {
        $idEmpresa = self::obtenerEmpresaDepartamentoJefe($db, $idPuesto, $idDepartamento);
        if ($idEmpresa !== 2) {
            return $rows;
        }

        $vistos = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id > 0) {
                $vistos[$id] = true;
            }
        }

        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $extra = $db->queryAll("
            SELECT
                per.id,
                CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
                COALESCE(MIN(pu.nombre), '') AS nombre_puesto,
                COALESCE(MIN(pu.nombre), '') AS puesto
            FROM estado_cuenta.persona per
            LEFT JOIN estado_cuenta.asigna_puesto ap
                   ON ap.id_persona = per.id
                  AND COALESCE(ap.activo, 1) = 1
            LEFT JOIN estado_cuenta.puesto pu ON pu.id = ap.id_puesto
            WHERE LOWER(TRIM(COALESCE(per.estatus, ''))) NOT IN ('baja', 'transito de baja')
              AND {$predPer}
              AND (
                    UPPER(TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom))) = 'ZAIRA YAEL TORRES DIAZ'
                 OR UPPER(TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom))) = 'IRMA NALLELY AGUILAR ISLAS'
                    OR UPPER(TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom))) = 'MONICA GABRIELA GARRIDO ORTEGA'
              )
            GROUP BY per.id, per.nombres, per.segundo_nombre, per.apellidop, per.apellidom
            ORDER BY nombre_completo ASC
        ");

        foreach ($extra as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id > 0 && empty($vistos[$id])) {
                $rows[] = $row;
                $vistos[$id] = true;
            }
        }

        usort($rows, static function ($a, $b) {
            return strcasecmp((string)($a['nombre_completo'] ?? ''), (string)($b['nombre_completo'] ?? ''));
        });

        return $rows;
    }

    /** Personas con puesto en el departamento (para combo jefe cuando no hay es_jefe ni por nivel) */
    public static function getPersonasPorDepartamento($id_departamento)
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $query = <<<SQL
          SELECT DISTINCT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS nombre_puesto
          FROM asigna_puesto ap
          INNER JOIN persona per ON per.id = ap.id_persona
          INNER JOIN puesto pu ON pu.id = ap.id_puesto
          WHERE COALESCE(ap.activo, 1) = 1
            AND LOWER(TRIM(COALESCE(per.estatus, ''))) NOT IN ('baja', 'transito de baja')
            AND {$predPer}
            AND pu.departamento_id = $id_departamento
          ORDER BY per.nombres ASC
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /** Personas activas de la empresa para escoger jefe cuando el puesto no tiene jefe jerarquico configurado. */
    public static function getPersonasActivasEmpresaParaJefe($idDepartamento = null)
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $db = new Database();
        $idEmpresa = self::obtenerEmpresaDepartamentoJefe($db, null, (int)$idDepartamento);
        $whereEmpresa = '';
        $params = ['id_departamento_objetivo' => (int)$idDepartamento];
        if ($idEmpresa > 0) {
            $whereEmpresa = "AND (
                dep.id = :id_departamento_objetivo
                OR (
                    dep_objetivo.id_departamento_organizacional IS NOT NULL
                    AND dorg.id = dep_objetivo.id_departamento_organizacional
                )
                OR (
                    :id_empresa_furia = 2
                    AND (
                        UPPER(TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom))) = 'ZAIRA YAEL TORRES DIAZ'
                        OR UPPER(TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom))) = 'IRMA NALLELY AGUILAR ISLAS'
                        OR UPPER(TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom))) = 'MONICA GABRIELA GARRIDO ORTEGA'
                    )
                )
            )";
            $params['id_empresa_furia'] = $idEmpresa;
        }
        $query = <<<SQL
          SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            COALESCE(MIN(pu.nombre), '') AS nombre_puesto,
            COALESCE(MIN(dep.nombre), '') AS departamento,
            COALESCE(MIN(dorg.nombre), '') AS area
          FROM persona per
          LEFT JOIN asigna_puesto ap
            ON ap.id_persona = per.id
           AND COALESCE(ap.activo, 1) = 1
          LEFT JOIN puesto pu
            ON pu.id = ap.id_puesto
          LEFT JOIN departamento dep
            ON dep.id = pu.departamento_id
          INNER JOIN departamento dep_objetivo
            ON dep_objetivo.id = :id_departamento_objetivo
          LEFT JOIN departamento_organizacional dorg
            ON dorg.id = dep.id_departamento_organizacional
          LEFT JOIN asigna_direcciones ad
            ON ad.id_departamento_organizacional = dorg.id
          LEFT JOIN direcciones_organizacion dir
            ON dir.id = ad.id_direccion
          WHERE LOWER(TRIM(COALESCE(per.estatus, ''))) NOT IN ('baja', 'transito de baja')
            AND {$predPer}
            {$whereEmpresa}
          GROUP BY per.id, per.nombres, per.segundo_nombre, per.apellidop, per.apellidom
          ORDER BY nombre_completo ASC
        SQL;
        try {
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Personas activas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /** Jefe por defecto cuando no hay resultados (ej. Legal/Abogado): JONNATHAN MARLON FLORES RODRIGUEZ */
    public static function getJefeDefault()
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $query = <<<SQL
          SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            COALESCE(pu.nombre, '') AS nombre_puesto
          FROM persona per
          LEFT JOIN asigna_puesto ap ON ap.id_persona = per.id AND COALESCE(ap.activo, 1) = 1
          LEFT JOIN puesto pu ON pu.id = ap.id_puesto
          WHERE LOWER(TRIM(COALESCE(per.estatus, ''))) NOT IN ('baja', 'transito de baja')
            AND {$predPer}
            AND per.nombres LIKE '%JONNATHAN%'
            AND (per.apellidop LIKE '%FLORES%' OR per.apellidop LIKE '%FLÓRES%')
            AND (per.apellidom LIKE '%RODRIGUEZ%' OR per.apellidom LIKE '%RODRÍGUEZ%')
          LIMIT 1
        SQL;
        try {
            $db = new Database();
            $r = $db->queryOne($query);
            return $r ? self::resultado(true, 'Jefe por defecto.', [$r]) : self::resultado(true, 'Sin resultados.', []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getAusenciaById($idAusencia)
    {
        $query = <<<SQL
        SELECT
            a.id,
            a.id_persona,
            a.id_razon,
            r.nombre AS razon,
            a.fecha_inicio,
            a.fecha_fin,
            a.descripcion,
            a.activo
        FROM ausencia a
        INNER JOIN razon_ausencia r ON r.id = a.id_razon
        WHERE a.id = :idAusencia
        LIMIT 1
    SQL;

        try {
            $db = new Database();

            $r = $db->queryOne($query, [
                'idAusencia' => $idAusencia
            ]);

            if (!$r) {
                return self::resultado(false, 'Ausencia no encontrada.', null);
            }

            return self::resultado(true, 'Ausencia encontrada.', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener la ausencia.', null, $e->getMessage());
        }
    }





    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    public static function getConsultaGestoresPorPuesto($id_puesto, ?int $idArea = null)
    {
        $predP = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');
        $query = <<<SQL
        SELECT DISTINCT
            p.id,
            CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
            pp.nombre AS puesto,
            pp.nombre AS nombre_puesto,
            pp.nivel,
            d.nombre AS departamento,
            dorg.nombre AS area
        FROM persona p
        INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
        INNER JOIN puesto pp ON pp.id = ap.id_puesto
        INNER JOIN departamento d ON d.id = pp.departamento_id
        LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
        WHERE COALESCE(LOWER(TRIM(p.estatus)), 'activo') NOT IN ('baja', 'transito de baja')
          AND {$predP}
          AND pp.nivel > (
                SELECT nivel
                FROM puesto
                WHERE id = :id_puesto_nivel
            )
          AND pp.departamento_id = (
                SELECT departamento_id
                FROM puesto
                WHERE id = :id_puesto_departamento
            )
          AND (:id_area_nula IS NULL OR d.id_departamento_organizacional = :id_area)
        ORDER BY pp.nivel ASC, nombre_completo
    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query, [
                'id_puesto_nivel' => (int)$id_puesto,
                'id_puesto_departamento' => (int)$id_puesto,
                'id_area_nula' => $idArea,
                'id_area' => $idArea,
            ]);
            // Las jefas especiales de Furia son un respaldo histórico. En el editor
            // RR.HH. se requiere coincidencia estricta de área/departamento/puesto.
            if ($idArea === null) {
                $r = self::agregarJefasFuriaMotoSiAplica($db, $r, (int)$id_puesto, null);
            }
            return self::resultado(true, 'Jefes encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener jefes.', null, $e->getMessage());
        }
    }

    public static function getPersonasOrganigrama($departamento, $id_persona)
    {
        try {
            $db = new Database();
            // -------------------------------------------------------
            // 1) Puestos activos del departamento
            // -------------------------------------------------------
            $queryPuestos = <<<SQL
            SELECT
                p.id,
                p.nombre,
                p.nivel
            FROM puesto p
            WHERE p.activo = 1 AND es_jefe = 1
              AND p.departamento_id = :departamento
        SQL;

            $puestos = $db->queryAll($queryPuestos, [
                'departamento' => $departamento
            ]);

            if (!$puestos) {
                $queryPuestos = <<<SQL
                SELECT
                    p.id,
                    p.nombre,
                    p.nivel
                FROM puesto p
                WHERE p.activo = 1
                  AND p.departamento_id = :departamento
                SQL;

                $puestos = $db->queryAll($queryPuestos, [
                    'departamento' => $departamento
                ]);
            }

            if (!$puestos) {
                return self::resultado(true, 'No hay puestos activos en este departamento.', []);
            }

            // -------------------------------------------------------
            // 2) Mayor nivel jerárquico
            // -------------------------------------------------------
            $puestosTopIds = array_column($puestos, 'id');



            // -------------------------------------------------------
            // 3) Crear placeholders con nombre (:p0, :p1, ...)
            // -------------------------------------------------------
            $params = [];
            $placeholders = [];

            foreach ($puestosTopIds as $i => $id) {
                $key = "p$i";
                $placeholders[] = ":$key";
                $params[$key] = $id;
            }

            $placeholdersStr = implode(',', $placeholders);


            // -------------------------------------------------------
            // 4) Personas por puestos top
            // -------------------------------------------------------
                $predOrg = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');
                $queryPersonas = <<<SQL
                SELECT
                p.id,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre,
                ap.id_puesto
            FROM persona p
            INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND ap.activo = 1
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            WHERE ap.id_puesto IN ($placeholdersStr)
              AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja')
              AND {$predOrg}
            ORDER BY
                pp.nivel DESC,
                nombre ASC
        SQL;

                $personas = $db->queryAll($queryPersonas, $params);

            // Una sola entrada por persona (evitar duplicados cuando tiene varios puestos en el departamento)
            $byPersonId = [];
            foreach ($personas as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id && !isset($byPersonId[$id])) {
                    $byPersonId[$id] = $row;
                }
            }
            $personas = array_values($byPersonId);

            return self::resultado(true, 'Personas de mayor rango encontradas.', $personas);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
    public static function getConsultaPersonasJerarquia($id_persona, $id_departamento = 0)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }
        $id_departamento = (int) $id_departamento;
        $filtroDepto = '';
        if ($id_departamento > 0) {
            $filtroDepto = " AND p.id IN (SELECT ap_in.id_persona FROM asigna_puesto ap_in INNER JOIN puesto pp_in ON pp_in.id = ap_in.id_puesto WHERE ap_in.activo = 1 AND pp_in.departamento_id = $id_departamento)";
        }
        $filtroDepto2 = '';
        if ($id_departamento > 0) {
            $filtroDepto2 = " AND p2.id IN (SELECT ap_in.id_persona FROM asigna_puesto ap_in INNER JOIN puesto pp_in ON pp_in.id = ap_in.id_puesto WHERE ap_in.activo = 1 AND pp_in.departamento_id = $id_departamento)";
        }
        // Un puesto por persona: si hay departamento, solo puestos de ese departamento y el de mayor rango (menor nivel)
        // Sin departamento: cualquier puesto, desempate por MIN(id_puesto)
        // Con departamento: el puesto de MAYOR nivel (mayor rango) en ese departamento; desempate por MIN(id_puesto)
        // Solo asigna_puesto activo (activo=1); si no, filas históricas/inactivas sesgan MAX(nivel) y el título en organigrama.
        $subqueryPuesto = "SELECT id_persona, MIN(id_puesto) AS id_puesto FROM asigna_puesto WHERE activo = 1 GROUP BY id_persona";
        if ($id_departamento > 0) {
            $subqueryPuesto = "SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto FROM asigna_puesto ap "
                . "INNER JOIN puesto pp ON pp.id = ap.id_puesto AND pp.departamento_id = $id_departamento "
                . "INNER JOIN (SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel FROM asigna_puesto ap2 INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto AND pp2.departamento_id = $id_departamento WHERE ap2.activo = 1 GROUP BY ap2.id_persona) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel "
                . "WHERE ap.activo = 1 "
                . "GROUP BY ap.id_persona";
        }
        $filtroPuestoRaiz = $id_departamento > 0 ? " AND pp.departamento_id = $id_departamento" : '';
        $orderPuestoRaiz = $id_departamento > 0 ? " ORDER BY pp.nivel DESC" : '';
        $exJP = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');
        $exJP2 = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p2');
        $subqueryJefeActual = "
            SELECT id_persona, id_jefe, id_vacante_jefe
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
            ) jefe_actual
            WHERE rn = 1
        ";

        $query = <<<SQL
               WITH RECURSIVE Jerarquia AS (

                -- NIVEL 1: un solo puesto por persona (del departamento si se filtró); solo personas con puesto en el departamento
                SELECT
                    p.id,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.estatus,
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    aj.id_jefe,
                    1 AS nivel
                FROM persona p
                JOIN ($subqueryPuesto) ap ON p.id = ap.id_persona
                JOIN puesto pp ON pp.id = ap.id_puesto
                JOIN ($subqueryJefeActual) aj ON p.id = aj.id_persona
                WHERE {$exJP}
                  AND aj.id_jefe = $id_persona
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja')
                  $filtroDepto

                UNION ALL

                -- NIVELES 2–4: un solo puesto por persona (del departamento si se filtró); solo personas del departamento
                SELECT
                    p2.id,
                    p2.nombres,
                    p2.segundo_nombre,
                    p2.apellidop,
                    p2.estatus,
                    ap2.id_puesto,
                    pp2.nombre AS nombre_puesto,
                    aj2.id_jefe,
                    j.nivel + 1 AS nivel
                FROM persona p2
                JOIN ($subqueryPuesto) ap2 ON p2.id = ap2.id_persona
                JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                JOIN ($subqueryJefeActual) aj2 ON p2.id = aj2.id_persona
                JOIN Jerarquia j ON aj2.id_jefe = j.id
                WHERE {$exJP2}
                  AND j.nivel < 4
                  AND LOWER(TRIM(COALESCE(p2.estatus, ''))) NOT IN ('baja')
                  $filtroDepto2
            )

            SELECT JSON_OBJECT(
                'id_jefe', $id_persona,
                'nombre_jefe', (
                    SELECT CONCAT_WS(' ', nombres, segundo_nombre, apellidop)
                    FROM persona
                    WHERE id = $id_persona
                ),
                'nombre_puesto', (
                    SELECT pp.nombre
                    FROM persona p
                    INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND ap.activo = 1
                    INNER JOIN puesto pp ON pp.id = ap.id_puesto
                    WHERE p.id = $id_persona $filtroPuestoRaiz
                    $orderPuestoRaiz
                    LIMIT 1
                ),
                'subordinados', (
                    SELECT COALESCE(JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'id', j1.id,
                            'nombre', CONCAT_WS(' ', j1.nombres, j1.segundo_nombre, j1.apellidop),
                            'estatus', j1.estatus,
                            'id_puesto', j1.id_puesto,
                            'nombre_puesto', j1.nombre_puesto,
                            'nivel', j1.nivel,

                            'subordinados', (
                                SELECT COALESCE(JSON_ARRAYAGG(
                                    JSON_OBJECT(
                                        'id', j2.id,
                                        'nombre', CONCAT_WS(' ', j2.nombres, j2.segundo_nombre, j2.apellidop),
                                        'estatus', j2.estatus,
                                        'id_puesto', j2.id_puesto,
                                        'nombre_puesto', j2.nombre_puesto,
                                        'nivel', j2.nivel,

                                        'subordinados', (
                                            SELECT COALESCE(JSON_ARRAYAGG(
                                                JSON_OBJECT(
                                                    'id', j3.id,
                                                    'nombre', CONCAT_WS(' ', j3.nombres, j3.segundo_nombre, j3.apellidop),
                                                    'estatus', j3.estatus,
                                                    'id_puesto', j3.id_puesto,
                                                    'nombre_puesto', j3.nombre_puesto,
                                                    'nivel', j3.nivel,

                                                    'subordinados', (
                                                        SELECT COALESCE(JSON_ARRAYAGG(
                                                            JSON_OBJECT(
                                                                'id', j4.id,
                                                                'nombre', CONCAT_WS(' ', j4.nombres, j4.segundo_nombre, j4.apellidop),
                                                                'estatus', j4.estatus,
                                                                'id_puesto', j4.id_puesto,
                                                                'nombre_puesto', j4.nombre_puesto,
                                                                'nivel', j4.nivel
                                                            )
                                                        ), JSON_ARRAY())
                                                        FROM Jerarquia j4
                                                        WHERE j4.id_jefe = j3.id
                                                          AND j4.nivel = 4
                                                    )
                                                )
                                            ), JSON_ARRAY())
                                            FROM Jerarquia j3
                                            WHERE j3.id_jefe = j2.id
                                              AND j3.nivel = 3
                                        )
                                    )
                                ), JSON_ARRAY())
                                FROM Jerarquia j2
                                WHERE j2.id_jefe = j1.id
                                  AND j2.nivel = 2
                            )
                        )
                    ), JSON_ARRAY())
                    FROM Jerarquia j1
                    WHERE j1.id_jefe = $id_persona
                      AND j1.nivel = 1
                )
            ) AS organigrama_json;


    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
    /** Departamentos que el usuario puede usar (asignar/cambiar): solo los que tienen puestos en privilegios_departamento para ESE usuario. */
    public static function getConsultaDepartamentoGestor($perfil_id)
    {
        $perfil_id = (int) $perfil_id;
        $condiciones = [
            'COALESCE(d.activo, 1) = 1',
            'COALESCE(p.activo, 1) = 1',
            'COALESCE(dorg.activo, 1) = 1'
        ];
        if ($perfil_id > 0) {
            $condiciones[] = 'pd.idPersona = ' . $perfil_id;
        }
        $complet = 'WHERE ' . implode(' AND ', $condiciones);

        $query = <<<SQL
           SELECT DISTINCT
                d.*,
                d.id_departamento_organizacional,
                COALESCE(dorg.nombre, 'Sin departamento') AS departamento_organizacional_nombre,
                COALESCE(dorg.activo, 1) AS departamento_organizacional_activo,
                COALESCE(dir.id, 0) AS id_direccion,
                COALESCE(dir.nombre, 'Sin dirección') AS direccion_nombre,
                COALESCE(dir.activo, 1) AS direccion_activo,
                COALESCE(d.id_empresa, dorg.id_empresa, dir.id_empresa, 1) AS id_empresa,
                COALESCE(emp.nombre_comercial, 'MaxiKash') AS nombre_empresa,
                pa.nombre AS nombre_pais,
                COALESCE(pa.codigo_iso, 'xx') AS codigo_iso_pais
            FROM privilegios_departamento pd
            INNER JOIN puesto p
                    ON p.id = pd.idPuesto
            INNER JOIN departamento d
                    ON d.id = p.departamento_id
            LEFT JOIN departamento_organizacional dorg
                    ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad
                    ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                   AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir
                    ON dir.id = ad.id_direccion
            LEFT JOIN rrhh_empresas emp
                    ON emp.id = COALESCE(d.id_empresa, dorg.id_empresa, dir.id_empresa, 1)
            LEFT JOIN paises pa
                    ON pa.id = d.id_pais
            $complet
            ORDER BY FIELD(pa.codigo_iso, 'mx', 'gt', 'co'), nombre_empresa, direccion_nombre, departamento_organizacional_nombre, d.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /** Lista de personas para select "Posible jefe" en candidatos. */
    public static function getListaPersonasParaJefe()
    {
        $pred = UsuarioFantasmaReporteria::sqlPredicadoExcluirUserNameSinAlias();
        $query = <<<SQL
            SELECT id, CONCAT(TRIM(COALESCE(nombres,'')), ' ', TRIM(COALESCE(apellidop,'')), ' ', TRIM(COALESCE(apellidom,''))) AS nombre
            FROM persona
            WHERE (estatus IS NULL OR LOWER(TRIM(estatus)) NOT IN ('baja', 'transito de baja'))
              AND ({$pred})
            ORDER BY nombres, apellidop, apellidom
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return is_array($r) ? $r : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Para organigrama: devuelve todos los departamentos (sin filtrar por gestor). */
    public static function getTodosDepartamentos()
    {
        $query = <<<SQL
            SELECT id, nombre
            FROM departamento
            WHERE COALESCE(activo, 1) = 1
            ORDER BY nombre ASC
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener departamentos.', null, $e->getMessage());
        }
    }

    /** Para Gestion de Personal: departamentos completos por pais, sin depender de privilegios por puesto. */
    public static function getTodosDepartamentosGestion()
    {
        $query = <<<SQL
            SELECT
                d.*,
                d.id_departamento_organizacional,
                COALESCE(dorg.nombre, 'Sin departamento') AS departamento_organizacional_nombre,
                COALESCE(dorg.activo, 1) AS departamento_organizacional_activo,
                COALESCE(dir.id, 0) AS id_direccion,
                COALESCE(dir.nombre, 'Sin dirección') AS direccion_nombre,
                COALESCE(dir.activo, 1) AS direccion_activo,
                COALESCE(d.id_empresa, dorg.id_empresa, dir.id_empresa, 1) AS id_empresa,
                COALESCE(emp.nombre_comercial, 'MaxiKash') AS nombre_empresa,
                pa.nombre AS nombre_pais,
                COALESCE(pa.codigo_iso, 'xx') AS codigo_iso_pais
            FROM departamento d
            LEFT JOIN departamento_organizacional dorg
                   ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad
                   ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                  AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir
                   ON dir.id = ad.id_direccion
            LEFT JOIN rrhh_empresas emp
                   ON emp.id = COALESCE(d.id_empresa, dorg.id_empresa, dir.id_empresa, 1)
            LEFT JOIN paises pa
                   ON pa.id = d.id_pais
            WHERE COALESCE(d.activo, 1) = 1
              AND COALESCE(dorg.activo, 1) = 1
            ORDER BY FIELD(pa.codigo_iso, 'mx', 'gt', 'co'), nombre_empresa, direccion_nombre, departamento_organizacional_nombre, d.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener departamentos.', null, $e->getMessage());
        }
    }

    /** Puestos asignados a una persona (para organigrama). Si id_departamento se pasa, solo puestos de ese departamento. */
    public static function getPuestosPorPersona($id_persona, $id_departamento = 0)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }
        $id_departamento = (int) $id_departamento;
        $params = ['id_persona' => $id_persona];
        $filtroDepto = '';
        if ($id_departamento > 0) {
            $filtroDepto = ' AND pp.departamento_id = :id_departamento';
            $params['id_departamento'] = $id_departamento;
        }
        $query = <<<SQL
            SELECT pp.id, pp.nombre
            FROM asigna_puesto ap
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            WHERE ap.id_persona = :id_persona
              AND ap.activo = 1
            $filtroDepto
            ORDER BY pp.nombre ASC
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Puestos encontrados.', $r ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener puestos.', null, $e->getMessage());
        }
    }

    public static function getPuestosActivosPersonaParaEdicion($id_persona)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona invalido.', []);
        }

        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    pp.departamento_id AS id_departamento,
                    d.nombre AS nombre_departamento,
                    pp.nivel
                 FROM estado_cuenta.asigna_puesto ap
                 INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                 LEFT JOIN estado_cuenta.departamento d ON d.id = pp.departamento_id
                 WHERE ap.id_persona = :id_persona
                   AND COALESCE(ap.activo, 1) = 1
                 ORDER BY pp.nivel DESC, ap.id ASC",
                ['id_persona' => $id_persona]
            );
            return self::resultado(true, 'Puestos activos encontrados.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener puestos activos.', [], $e->getMessage());
        }
    }

    /** Nombre del puesto por ID (para organigrama por cargo). */
    public static function getNombrePuesto($id_puesto)
    {
        $id_puesto = (int) $id_puesto;
        if ($id_puesto <= 0) return null;
        $query = "SELECT nombre FROM puesto WHERE id = :id";
        try {
            $db = new Database();
            $r = $db->queryAll($query, ['id' => $id_puesto]);
            return isset($r[0]['nombre']) ? $r[0]['nombre'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    ////////////////////////////////////////
    public static function getConsultaDepartamentoGestorOrganigrama($departamento)
    {

        $query = <<<SQL
           SELECT *
            FROM  puesto p
            WHERE p.departamento_id  = $departamento
            ORDER BY p.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    ////////////////////////////////////////
    public static function getPersonasBaja()
    {
        $query = <<<SQL
            SELECT
                id,
                nombres,
                apellidop,
                apellidom,
                numero_empleado,
                estatus,
                user_name
            FROM estado_cuenta.persona
            WHERE estatus = 'Baja'
            ORDER BY numero_empleado ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas dadas de baja encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener las personas dadas de baja.', null, $e->getMessage());
        }
    }

    /**
     * Siguiente número de empleado libre: toma el máximo entre valores puramente numéricos,
     * suma 1 y avanza hasta encontrar un valor que no exista en persona.numero_empleado.
     */
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
            $ex = $db->queryOne(
                'SELECT 1 AS ok FROM estado_cuenta.persona WHERE numero_empleado = :n LIMIT 1',
                ['n' => $candidate]
            );
            if (empty($ex)) {
                return $candidate;
            }
            $next++;
        }

        return 'NEO' . strtoupper(bin2hex(random_bytes(4)));
    }

    public static function insertPersona($data)
    {
        // 🔹 Escapamos valores
        $nombres = addslashes((string) ($data['nombres'] ?? ''));
        $segundo_nombre = addslashes((string) ($data['segundo_nombre'] ?? ''));
        $apellidop = addslashes((string) ($data['apellidop'] ?? ''));
        $apellidom = addslashes((string) ($data['apellidom'] ?? ''));
        // Si no viene número de empleado, se genera en BD (max numérico + 1, sin colisiones).
        $numeroEmpleadoEntrada = trim((string) ($data['numero_empleado'] ?? ''));
        $autoNumeroEmpleado = $numeroEmpleadoEntrada === ''
            || strcasecmp($numeroEmpleadoEntrada, 'PEND') === 0
            || strcasecmp($numeroEmpleadoEntrada, 'PENDIENTE') === 0;
        $correo = addslashes((string) ($data['correo'] ?? ''));
        $telefono_uno = addslashes((string) ($data['telefono'] ?? $data['telefono_uno'] ?? ''));
        $telefono_dos = addslashes((string) ($data['telefono_dos'] ?? ''));
        $estatus = addslashes((string) ($data['estatus'] ?? 'Activo'));
        $id_puesto = (int) ($data['id_puesto'] ?? 0);
        $id_departamento = (int) ($data['departamento_id'] ?? $data['id_departamento'] ?? 0);
        $vacante_existente_id = (int) ($data['vacante_existente_id'] ?? 0);
        $user_name = addslashes((string) ($data['usuario'] ?? ''));
        $password = addslashes((string) ($data['contrasena'] ?? ''));
        $fecha_ingreso = !empty($data['fecha_ingreso']) ? addslashes($data['fecha_ingreso']) : null;
        $id_pais = (int) ($data['id_pais'] ?? 1);
        if ($id_pais <= 0) {
            $id_pais = 1;
        }
        // FK a divisiones_administrativas.id por nivel (null/""/0 del JSON → NULL SQL, no 0).
        $id_div_nivel1 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel1'] ?? null);
        $id_div_nivel2 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel2'] ?? null);
        $id_div_nivel3 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel3'] ?? null);
        $curp_norm = strtoupper(preg_replace('/\s+/', '', (string) ($data['curp'] ?? '')));
        $curp_norm = mb_substr($curp_norm, 0, 18);
        $curp_sql = $curp_norm !== '' ? "'" . addslashes($curp_norm) . "'" : 'NULL';
        $dom_ext = trim((string) ($data['domicilio_num_exterior'] ?? ''));
        $dom_int = trim((string) ($data['domicilio_num_interior'] ?? ''));
        $cp = trim((string) ($data['codigo_postal'] ?? ''));
        $dom_ext_sql = $dom_ext !== '' ? "'" . addslashes($dom_ext) . "'" : 'NULL';
        $dom_int_sql = $dom_int !== '' ? "'" . addslashes($dom_int) . "'" : 'NULL';
        $cp_sql = $cp !== '' ? "'" . addslashes($cp) . "'" : 'NULL';

        try {
            $db = new Database();
            self::asegurarPersonaEsExterno($db);
            $vacanteSeleccionada = null;
            if ($vacante_existente_id > 0) {
                self::asegurarTablaVacantesPersonal($db);
                self::asegurarAsignaJefeSoportaVacante($db);
                $vacanteSeleccionada = $db->queryOne("
                    SELECT id, id_jefe, id_departamento, id_puesto
                    FROM estado_cuenta.vacantes_personal
                    WHERE id = :id
                      AND UPPER(TRIM(estatus)) = 'ACTIVA'
                    LIMIT 1
                ", ['id' => $vacante_existente_id]);

                if (!$vacanteSeleccionada) {
                    return self::resultado(false, 'La vacante seleccionada ya no esta disponible.');
                }
                if ((int)$vacanteSeleccionada['id_puesto'] !== $id_puesto || ($id_departamento > 0 && (int)$vacanteSeleccionada['id_departamento'] !== $id_departamento)) {
                    return self::resultado(false, 'La vacante seleccionada no corresponde al departamento y puesto elegidos.');
                }
            }

            if ($autoNumeroEmpleado) {
                $numero_raw = self::siguienteNumeroEmpleadoLibre($db);
            } else {
                $numero_raw = $numeroEmpleadoEntrada;
            }
            $numero_empleado = addslashes($numero_raw);
            $esExterno = isset($data['es_externo']) && (bool)$data['es_externo'];
            $es_externo_sql = $esExterno ? 1 : 0;
            $codigoContpacRaw = $esExterno ? '' : trim((string)($data['codigo_contpac'] ?? ''));
            $codigo_contpac_sql = $codigoContpacRaw !== '' ? "'" . addslashes($codigoContpacRaw) . "'" : 'NULL';

            if ($cp === '' && $id_div_nivel3 !== 'NULL') {
                $crow = $db->queryOne(
                    'SELECT NULLIF(TRIM(codigo_interno), \'\') AS cp FROM estado_cuenta.divisiones_administrativas WHERE id = :id AND activo = 1 LIMIT 1',
                    ['id' => (int) $id_div_nivel3]
                );
                if (!empty($crow['cp'])) {
                    $cp = trim((string) $crow['cp']);
                    $cp_sql = "'" . addslashes($cp) . "'";
                }
            }

            $dom_calle = self::domicilioCalleTextoParaGuardar($db, $data);
            $dom_calle_sql = $dom_calle !== '' ? "'" . addslashes($dom_calle) . "'" : 'NULL';

            $fecha_ingreso_sql = $fecha_ingreso !== null ? "'$fecha_ingreso'" : 'NULL';

            $tz = new \DateTimeZone('America/Mexico_City');
            $fechaRegistro = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
            $fechaRegistro = addslashes($fechaRegistro);

            $db->queryOne("
            INSERT INTO estado_cuenta.persona
            (nombres, segundo_nombre, apellidop, apellidom, curp, numero_empleado, codigo_contpac, es_externo, correo, telefono_uno, telefono_dos, estatus, user_name, password, fecha_ingreso, fecha_registro, id_pais, id_div_nivel1, id_div_nivel2, id_div_nivel3, domicilio_calle_texto, domicilio_num_exterior, domicilio_num_interior, codigo_postal)
            VALUES
            ('$nombres', '$segundo_nombre', '$apellidop', '$apellidom', $curp_sql, '$numero_empleado', $codigo_contpac_sql, $es_externo_sql, '$correo', '$telefono_uno', '$telefono_dos', '$estatus', '$user_name', '$password', $fecha_ingreso_sql, '$fechaRegistro', $id_pais, $id_div_nivel1, $id_div_nivel2, $id_div_nivel3, $dom_calle_sql, $dom_ext_sql, $dom_int_sql, $cp_sql)
        ");


            // 2️⃣ Obtenemos el ID insertado con queryOne()
            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            if (is_array($result)) {
                $result['numero_empleado'] = $numero_raw;
            }

            $id_persona = isset($result['id']) ? intval($result['id']) : null;

            // Si no tiene jefe, él mismo será su jefe
            $jefeRaw = trim((string)($data['id_jefe'] ?? ''));
            $id_vacante_jefe = 0;
            if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
                self::asegurarAsignaJefeSoportaVacante($db);
                $id_vacante_jefe = (int)$m[1];
                $id_jefe = null;
            } else {
                $id_jefe = $jefeRaw !== '' ? (int)$jefeRaw : null;
            }
            if ($vacanteSeleccionada && !empty($vacanteSeleccionada['id_jefe'])) {
                $id_jefe = (int)$vacanteSeleccionada['id_jefe'];
                $id_vacante_jefe = 0;
            }

            if ($result)
            {
                $fechaAsignacionCdmx = self::fechaHoraCdmx();
                $db->queryOne("
                    INSERT INTO estado_cuenta.asigna_puesto
                        (id, id_persona, id_puesto, fecha_asignacion, activo)
                    VALUES
                        (DEFAULT, $id_persona, $id_puesto, '$fechaAsignacionCdmx', 1)
                ");

                self::aplicarPermisosPuestoAPersonaConDb($db, (int) $id_persona, (int) $id_puesto);

                $puestosDespuesAlta = self::puestosActivosTrayectoria($db, (int)$id_persona);
                self::registrarCambiosTrayectoriaPuestos(
                    $db,
                    (int)$id_persona,
                    [],
                    $puestosDespuesAlta,
                    isset($data['usuario_edita']) ? (int)$data['usuario_edita'] : (int)($_SESSION['usuario_id'] ?? 0),
                    'alta_gestion_personal'
                );

                if ($id_vacante_jefe > 0) {
                    $db->queryOne("
                    INSERT INTO estado_cuenta.asigna_jefe
                        (id, id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
                    VALUES
                        (DEFAULT, $id_persona, NULL, $id_vacante_jefe, CURDATE(), NULL)
                ");
                } else {
                    $idJefeSql = $id_jefe !== null ? (int)$id_jefe : 'NULL';
                    $db->queryOne("
                    INSERT INTO estado_cuenta.asigna_jefe
                        (id, id_persona, id_jefe, fecha_inicio, fecha_fin)
                    VALUES
                        (DEFAULT, $id_persona, $idJefeSql, CURDATE(), NULL)
                ");
                }

                // 4️⃣ Auto-registrar en despachos si el puesto lo requiere
                $id_celula_despacho = self::resolverCelulaDespacho($db, (int)$id_puesto);
                if ($id_celula_despacho !== null) {
                    $existeDespacho = $db->queryOne(
                        "SELECT id FROM despachos WHERE id_persona = :idp AND estatus = 'Activo' LIMIT 1",
                        ['idp' => $id_persona]
                    );
                    if (!$existeDespacho) {
                        $db->queryOne(
                            "INSERT INTO despachos (id_persona, estatus, fecha_alta, id_celula) VALUES (:idp, 'Activo', NOW(), :cel)",
                            ['idp' => $id_persona, 'cel' => $id_celula_despacho]
                        );
                    }
                }

                // 5️⃣ Asignar legión si se marcó el checkbox
                if (isset($data['asignar_legion']) && $data['asignar_legion'] && isset($data['id_legion']) && $data['id_legion']) {
                    $id_legion = (int)$data['id_legion'];

                    // Desactivar cualquier legión activa previa para esta persona
                    $db->queryOne("
                        UPDATE estado_cuenta.asigna_legion
                        SET activo = 0, fecha_fin = NOW()
                        WHERE id_persona = $id_persona AND activo = 1
                    ");

                    // Insertar la nueva asignación de legión
                    $db->queryOne("
                        INSERT INTO estado_cuenta.asigna_legion
                            (id, id_persona, id_legion, fecha_asignacion, activo)
                        VALUES
                            (DEFAULT, $id_persona, $id_legion, NOW(), 1)
                    ");
                }

                if ($vacanteSeleccionada) {
                    $db->CRUD("
                        UPDATE estado_cuenta.vacantes_personal
                        SET estatus = 'Ocupada',
                            id_persona_cubre = :id_persona,
                            fecha_cierre = NOW()
                        WHERE id = :id_vacante
                          AND UPPER(TRIM(estatus)) = 'ACTIVA'
                    ", [
                        'id_persona' => $id_persona,
                        'id_vacante' => $vacante_existente_id,
                    ]);

                    $db->CRUD("
                        UPDATE estado_cuenta.asigna_jefe
                        SET id_jefe = :id_persona,
                            id_vacante_jefe = NULL
                        WHERE id_vacante_jefe = :id_vacante
                    ", [
                        'id_persona' => $id_persona,
                        'id_vacante' => $vacante_existente_id,
                    ]);
                }
            }

            if (!empty($id_persona)) {
                self::registrarAuditoriaInternaRrhh([
                    'id_usuario' => isset($data['usuario_edita']) ? (int)$data['usuario_edita'] : (int)($_SESSION['usuario_id'] ?? 0),
                    'modulo' => 'Gestion de Personal',
                    'entidad_tipo' => 'persona',
                    'entidad_id' => (int)$id_persona,
                    'entidad_nombre' => trim(($data['nombres'] ?? '') . ' ' . ($data['apellidop'] ?? '') . ' ' . ($data['apellidom'] ?? '')),
                    'accion' => 'crear_usuario',
                    'resumen' => 'Se registro una persona desde Gestion de Personal.',
                    'detalle' => self::snapshotPersonaAuditoria((int)$id_persona),
                ]);
            }

            return self::resultado(true, 'Persona insertada correctamente.', $result);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al insertar persona.', null, $e->getMessage());
        }
    }

    /** ID del módulo Onboarding en modulos_web (solo acceso a menú Onboarding para nuevos incorporados). */
    const MODULO_ONBOARDING_ID = 44;

    /**
     * Asigna únicamente el módulo Onboarding al usuario (para nuevos colaboradores desde candidatos).
     * Elimina cualquier otro módulo asignado y deja solo Onboarding.
     *
     * @param int $id_persona ID de persona en estado_cuenta.persona
     * @return array { success, mensaje }
     */
    public static function asignarSoloModuloOnboarding($id_persona)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD('DELETE FROM asigna_modulo_web WHERE usuario_id = :uid', ['uid' => $id_persona]);
            $db->CRUD(
                'INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id) VALUES (:uid, :mid)',
                ['uid' => $id_persona, 'mid' => self::MODULO_ONBOARDING_ID]
            );
            return self::resultado(true, 'Módulo Onboarding asignado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al asignar módulo Onboarding.', null, $e->getMessage());
        }
    }

    public static function guardarAusencia($data)
    {
        $db = new Database();

        $id_ausencia  = isset($data['idAusencia']) && $data['idAusencia'] !== ''
            ? (int)$data['idAusencia']
            : null;

        $id_persona   = (int)$data['idPersona'];
        $id_razon     = (int)$data['idRazon'];
        $descripcion  = addslashes($data['descripcion'] ?? '');
        $fecha_inicio = addslashes($data['fechaInicio']);
        $fecha_fin    = addslashes($data['fechaFin']);
        $creado_por   = addslashes($_SESSION['usuario'] ?? 'sistema');

        try {
            $razon = $db->queryOne("
                SELECT nombre
                FROM estado_cuenta.razon_ausencia
                WHERE id = $id_razon
                LIMIT 1
            ");
            $razon_nombre = strtoupper(trim((string)($razon['nombre'] ?? '')));
            $es_vacaciones = strpos($razon_nombre, 'VACACION') !== false || strpos($razon_nombre, 'VACACIONES') !== false;
            $mensaje_registro = $es_vacaciones ? 'Vacaciones registradas correctamente.' : 'Ausencia registrada correctamente.';
            $mensaje_actualizacion = $es_vacaciones ? 'Vacaciones actualizadas correctamente.' : 'Ausencia actualizada correctamente.';

            // 🔄 UPDATE
            if ($id_ausencia) {

                $db->queryOne("
                UPDATE estado_cuenta.ausencia
                SET
                    id_razon     = $id_razon,
                    descripcion  = '$descripcion',
                    fecha_inicio = '$fecha_inicio',
                    fecha_fin    = '$fecha_fin'
                WHERE id = $id_ausencia
                LIMIT 1
            ");

                return self::resultado(
                    true,
                    $mensaje_actualizacion,
                    ['id' => $id_ausencia]
                );
            }

            // ➕ INSERT
            $db->queryOne("
            INSERT INTO estado_cuenta.ausencia
                (id_persona, id_razon, descripcion, fecha_inicio, fecha_fin, creado_por, activo)
            VALUES
                ($id_persona, $id_razon, '$descripcion', '$fecha_inicio', '$fecha_fin', '$creado_por', 1)
        ");

            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");

            return self::resultado(
                true,
                $mensaje_registro,
                $result
            );

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al guardar ausencia.',
                null,
                $e->getMessage()
            );
        }
    }



    public static function UpdatePersona($data)
    {
        $id_persona      = (int)$data['id'];
        $numero_empleado = trim((string)($data['numero_empleado'] ?? ''));
        $nombres         = addslashes($data['nombres']);
        $segundo_nombre  = addslashes($data['segundo_nombre'] ?? '');
        $apellidop       = addslashes($data['apellidop']);
        $apellidom       = addslashes($data['apellidom']);
        $preservarCorreoActual = !empty($data['_preservar_correo_actual']);
        $correoRaw       = trim((string)($data['correo'] ?? ''));
        if (!$preservarCorreoActual && $correoRaw !== '' && !filter_var($correoRaw, FILTER_VALIDATE_EMAIL)) {
            return self::resultado(false, 'El correo electrÃ³nico no tiene un formato valido.');
        }
        $correo_sql      = $preservarCorreoActual ? 'correo' : ($correoRaw !== '' ? "'" . addslashes($correoRaw) . "'" : 'NULL');
        $telefono_uno    = addslashes($data['telefono_uno'] ?? $data['telefono'] ?? '');
        $jefeRaw         = trim((string)($data['jefe_id'] ?? ''));
        $preservarJefeActual = $jefeRaw === '';
        $id_jefe = null;
        $id_vacante_jefe = 0;
        if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
            $id_jefe = null;
            $id_vacante_jefe = (int)$m[1];
        } elseif ($jefeRaw !== '') {
            $id_jefe = (int)$jefeRaw;
        }
        $id_puesto       = (int)$data['puesto_id'];
        $puestosAdicionalesEntrada = $data['puestos_adicionales'] ?? null;
        $sincronizarPuestosDesdeLista = is_array($puestosAdicionalesEntrada);
        $puestosEliminadosEntrada = is_array($data['puestos_eliminados'] ?? null) ? $data['puestos_eliminados'] : [];
        $idPuestoPrincipalOriginal = (int)($data['puesto_principal_original'] ?? 0);
        $idPuestoPrincipalEliminado = 0;
        foreach ($puestosEliminadosEntrada as $puestoEliminadoEntrada) {
            if (!empty($puestoEliminadoEntrada['era_principal'])) {
                $idPuestoPrincipalEliminado = (int)($puestoEliminadoEntrada['id_puesto'] ?? 0);
                break;
            }
        }
        $idsPuestosEntrada = [];
        if (is_array($puestosAdicionalesEntrada)) {
            foreach ($puestosAdicionalesEntrada as $puestoEntrada) {
                $puestoEntradaId = (int)($puestoEntrada['id_puesto'] ?? 0);
                if ($puestoEntradaId > 0) {
                    $idsPuestosEntrada[$puestoEntradaId] = true;
                }
            }
        }
        $idsPuestosEntrada = array_keys($idsPuestosEntrada);
        if (!empty($idsPuestosEntrada)) {
            $id_puesto = (int)$idsPuestosEntrada[0];
        }
        $user_name       = addslashes($data['usuario']);
        $password        = addslashes($data['contrasena']);
        $id_div_nivel1 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel1'] ?? null);
        $id_div_nivel2 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel2'] ?? null);
        $id_div_nivel3 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel3'] ?? null);
        $curp_norm = strtoupper(preg_replace('/\s+/', '', (string) ($data['curp'] ?? '')));
        $curp_norm = mb_substr($curp_norm, 0, 18);
        $curp_sql = $curp_norm !== '' ? "'" . addslashes($curp_norm) . "'" : 'NULL';
        $dom_ext = trim((string) ($data['domicilio_num_exterior'] ?? ''));
        $dom_int = trim((string) ($data['domicilio_num_interior'] ?? ''));
        $cp = trim((string) ($data['codigo_postal'] ?? ''));
        $dom_ext_sql = $dom_ext !== '' ? "'" . addslashes($dom_ext) . "'" : 'NULL';
        $dom_int_sql = $dom_int !== '' ? "'" . addslashes($dom_int) . "'" : 'NULL';
        $cp_sql = $cp !== '' ? "'" . addslashes($cp) . "'" : 'NULL';
        $preservarDomicilioActual = !empty($data['_preservar_domicilio_actual']);
        $transaccionActiva = false;

        try {
            $db = new Database();
            self::asegurarAsignaJefeSoportaVacante($db);
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarTablaTrayectoriaPuesto($db);

            $actualNumero = $db->queryOne(
                "SELECT
                    numero_empleado,
                    id_pais,
                    id_div_nivel1,
                    id_div_nivel2,
                    id_div_nivel3,
                    domicilio_calle_texto,
                    domicilio_num_exterior,
                    domicilio_num_interior,
                    codigo_postal
                FROM estado_cuenta.persona
                WHERE id = :id
                LIMIT 1",
                ['id' => $id_persona]
            );
            $numeroEmpleadoActual = trim((string)($actualNumero['numero_empleado'] ?? ''));

            if ($preservarDomicilioActual) {
                $id_pais = (int)($actualNumero['id_pais'] ?? $id_pais);
                if ($id_pais <= 0) {
                    $id_pais = 1;
                }
                $id_div_nivel1 = self::sqlIdDivisionAdministrativaFk($actualNumero['id_div_nivel1'] ?? null);
                $id_div_nivel2 = self::sqlIdDivisionAdministrativaFk($actualNumero['id_div_nivel2'] ?? null);
                $id_div_nivel3 = self::sqlIdDivisionAdministrativaFk($actualNumero['id_div_nivel3'] ?? null);
                $domExtActual = trim((string)($actualNumero['domicilio_num_exterior'] ?? ''));
                $domIntActual = trim((string)($actualNumero['domicilio_num_interior'] ?? ''));
                $cp = trim((string)($actualNumero['codigo_postal'] ?? ''));
                $dom_ext_sql = $domExtActual !== '' ? "'" . addslashes($domExtActual) . "'" : 'NULL';
                $dom_int_sql = $domIntActual !== '' ? "'" . addslashes($domIntActual) . "'" : 'NULL';
                $cp_sql = $cp !== '' ? "'" . addslashes($cp) . "'" : 'NULL';
            }

            if ($numero_empleado === '') {
                $numero_empleado = $numeroEmpleadoActual;
            }

            $numeroEmpleadoCambio = $numero_empleado !== $numeroEmpleadoActual;
            if ($numeroEmpleadoCambio && $numero_empleado !== '') {
                $duplicadoNumero = $db->queryOne(
                    "SELECT id FROM estado_cuenta.persona WHERE numero_empleado = :numero_empleado AND id <> :id LIMIT 1",
                    ['numero_empleado' => $numero_empleado, 'id' => $id_persona]
                );
                if ($duplicadoNumero) {
                    return self::resultado(false, 'El numero de empleado ya existe en otro usuario.');
                }
            }
            $numero_empleado_sql = "'" . addslashes($numero_empleado) . "'";

            $puestoAnterior = $db->queryOne("
                SELECT
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    pp.departamento_id,
                    pp.nivel,
                    aj.id_jefe AS id_jefe_anterior,
                    aj.id_vacante_jefe AS id_vacante_jefe_anterior
                FROM estado_cuenta.asigna_puesto ap
                INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN (
                    SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                    FROM estado_cuenta.asigna_jefe a
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS mid
                        FROM estado_cuenta.asigna_jefe
                        GROUP BY id_persona
                    ) m ON m.id_persona = a.id_persona AND m.mid = a.id
                ) aj ON aj.id_persona = ap.id_persona
                WHERE ap.id_persona = :id_persona
                  AND COALESCE(ap.activo, 1) = 1
                ORDER BY pp.nivel DESC, ap.id ASC
                LIMIT 1
            ", ['id_persona' => $id_persona]);

            $puestoNuevo = $id_puesto > 0 ? $db->queryOne("
                SELECT id, nombre AS nombre_puesto, departamento_id, nivel
                FROM estado_cuenta.puesto
                WHERE id = :id_puesto
                LIMIT 1
            ", ['id_puesto' => $id_puesto]) : null;

            $principalFueEliminado = $idPuestoPrincipalEliminado > 0
                && !in_array($idPuestoPrincipalEliminado, array_map('intval', $idsPuestosEntrada), true);
            $principalCambio = $idPuestoPrincipalOriginal > 0
                && $id_puesto > 0
                && $idPuestoPrincipalOriginal !== (int)$id_puesto;
            $idPuestoPrincipalAnterior = $principalFueEliminado
                ? $idPuestoPrincipalEliminado
                : ($principalCambio ? $idPuestoPrincipalOriginal : 0);
            if ($idPuestoPrincipalAnterior > 0) {
                $puestoPrincipalEliminado = $db->queryOne("
                    SELECT
                        ap.id_puesto,
                        pp.nombre AS nombre_puesto,
                        pp.departamento_id,
                        pp.nivel,
                        aj.id_jefe AS id_jefe_anterior,
                        aj.id_vacante_jefe AS id_vacante_jefe_anterior
                    FROM estado_cuenta.asigna_puesto ap
                    INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                    LEFT JOIN (
                        SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                        FROM estado_cuenta.asigna_jefe a
                        INNER JOIN (
                            SELECT id_persona, MAX(id) AS mid
                            FROM estado_cuenta.asigna_jefe
                            GROUP BY id_persona
                        ) m ON m.id_persona = a.id_persona AND m.mid = a.id
                    ) aj ON aj.id_persona = ap.id_persona
                    WHERE ap.id_persona = :id_persona
                      AND ap.id_puesto = :id_puesto
                      AND COALESCE(ap.activo, 1) = 1
                    ORDER BY ap.id DESC
                    LIMIT 1
                ", [
                    'id_persona' => $id_persona,
                    'id_puesto' => $idPuestoPrincipalAnterior,
                ]);
                if ($puestoPrincipalEliminado) {
                    $puestoAnterior = $puestoPrincipalEliminado;
                }
            }

            $subordinadosPuestoAnterior = [];
            $esDegradacionConHueco = false;
            if ($puestoAnterior && $principalFueEliminado) {
                $esDegradacionConHueco = true;
            } elseif ((!$sincronizarPuestosDesdeLista || $principalCambio) && $puestoAnterior && $puestoNuevo && (int)$puestoAnterior['id_puesto'] !== (int)$id_puesto) {
                $nivelAnterior = (int)($puestoAnterior['nivel'] ?? 0);
                $nivelNuevo = (int)($puestoNuevo['nivel'] ?? 0);
                $esDegradacionConHueco = $nivelAnterior > $nivelNuevo;
            }
            if ($esDegradacionConHueco) {
                $subordinadosPuestoAnterior = $db->queryAll("
                    SELECT p.id, CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo
                    FROM estado_cuenta.asigna_jefe aj
                    INNER JOIN estado_cuenta.persona p ON p.id = aj.id_persona
                    WHERE aj.id_jefe = :id_persona
                      AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                    ORDER BY p.nombres ASC, p.apellidop ASC
                ", ['id_persona' => $id_persona]);
            }

            $resolverPuestoAnterior = trim((string)($data['resolver_puesto_anterior'] ?? ''));
            $idSustitutoPuestoAnterior = (int)($data['id_sustituto_puesto_anterior'] ?? 0);
            if ($esDegradacionConHueco && !empty($subordinadosPuestoAnterior) && !in_array($resolverPuestoAnterior, ['vacante', 'sustituto'], true)) {
                $sustitutos = $db->queryAll("
                    SELECT
                        p.id,
                        CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                        pp.nombre AS nombre_puesto,
                        MAX(pp.nivel) AS nivel_orden
                    FROM estado_cuenta.persona p
                    INNER JOIN estado_cuenta.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                    INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                    WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                      AND p.id <> :id_persona
                      AND pp.departamento_id = :id_departamento
                    GROUP BY p.id, nombre_completo, pp.nombre
                    ORDER BY nivel_orden DESC, nombre_completo ASC
                ", [
                    'id_persona' => $id_persona,
                    'id_departamento' => (int)$puestoAnterior['departamento_id'],
                ]);
                return self::resultado(false, 'El puesto anterior tiene subordinados. Indique si desea crear una vacante o asignar un sustituto antes de continuar.', [
                    'requiere_resolucion_puesto_anterior' => true,
                    'puesto_anterior' => $puestoAnterior,
                    'puesto_nuevo' => $puestoNuevo,
                    'subordinados_count' => count($subordinadosPuestoAnterior),
                    'sustitutos' => $sustitutos,
                ]);
            }

            if ($esDegradacionConHueco && !empty($subordinadosPuestoAnterior) && $resolverPuestoAnterior === 'sustituto') {
                if ($idSustitutoPuestoAnterior <= 0 || $idSustitutoPuestoAnterior === $id_persona) {
                    return self::resultado(false, 'Seleccione una persona valida para sustituir el puesto anterior.');
                }
                $sustitutoValido = $db->queryOne("
                    SELECT p.id
                    FROM estado_cuenta.persona p
                    INNER JOIN estado_cuenta.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                    INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                    WHERE p.id = :id_sustituto
                      AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                      AND pp.departamento_id = :id_departamento
                    LIMIT 1
                ", [
                    'id_sustituto' => $idSustitutoPuestoAnterior,
                    'id_departamento' => (int)$puestoAnterior['departamento_id'],
                ]);
                if (!$sustitutoValido) {
                    return self::resultado(false, 'El sustituto seleccionado no pertenece al departamento del puesto anterior o no esta activo.');
                }
            }

            $puestosTrayectoriaAntes = self::puestosActivosTrayectoria($db, $id_persona);
            $db->beginTransaction();
            $transaccionActiva = true;

            if ($cp === '' && $id_div_nivel3 !== 'NULL') {
                $crow = $db->queryOne(
                    'SELECT NULLIF(TRIM(codigo_interno), \'\') AS cp FROM estado_cuenta.divisiones_administrativas WHERE id = :id AND activo = 1 LIMIT 1',
                    ['id' => (int) $id_div_nivel3]
                );
                if (!empty($crow['cp'])) {
                    $cp = trim((string) $crow['cp']);
                    $cp_sql = "'" . addslashes($cp) . "'";
                }
            }

            $dom_calle = $preservarDomicilioActual
                ? trim((string)($actualNumero['domicilio_calle_texto'] ?? ''))
                : self::domicilioCalleTextoParaGuardar($db, $data);
            $dom_calle_sql = $dom_calle !== '' ? "'" . addslashes($dom_calle) . "'" : 'NULL';

            // 1️⃣ UPDATE PERSONA
            $db->queryOne("
            UPDATE estado_cuenta.persona
            SET
                numero_empleado = $numero_empleado_sql,
                nombres       = '$nombres',
                segundo_nombre = '$segundo_nombre',
                apellidop     = '$apellidop',
                apellidom     = '$apellidom',
                curp          = $curp_sql,
                correo        = $correo_sql,
                telefono_uno  = '$telefono_uno',
                user_name     = '$user_name',
                password      = '$password',
                id_div_nivel1  = $id_div_nivel1,
                id_div_nivel2  = $id_div_nivel2,
                id_div_nivel3  = $id_div_nivel3,
                domicilio_calle_texto = $dom_calle_sql,
                domicilio_num_exterior = $dom_ext_sql,
                domicilio_num_interior = $dom_int_sql,
                codigo_postal = $cp_sql
            WHERE id = $id_persona
        ");

            // 2️⃣ ASIGNA JEFE (si existe UPDATE, si no INSERT)
            if (!$preservarJefeActual) {
                $idJefeSql = ($id_jefe !== null && (int)$id_jefe > 0) ? (string)(int)$id_jefe : 'NULL';

                $existeJefe = $db->queryOne("
            SELECT id
            FROM asigna_jefe
            WHERE id_persona = $id_persona
            LIMIT 1
        ");

                if ($existeJefe) {
                    if ($id_vacante_jefe > 0) {
                        $db->queryOne("
                    UPDATE asigna_jefe
                    SET id_jefe = NULL,
                        id_vacante_jefe = $id_vacante_jefe
                    WHERE id_persona = $id_persona
                ");
                    } else {
                        $db->queryOne("
                    UPDATE asigna_jefe
                    SET id_jefe = $idJefeSql,
                        id_vacante_jefe = NULL
                    WHERE id_persona = $id_persona
                ");
                    }
                } else {
                    if ($id_vacante_jefe > 0) {
                        $db->queryOne("
                    INSERT INTO asigna_jefe (id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
                    VALUES ($id_persona, NULL, $id_vacante_jefe, CURDATE(), NULL)
                ");
                    } else {
                        $db->queryOne("
                    INSERT INTO asigna_jefe (id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
                    VALUES ($id_persona, $idJefeSql, NULL, CURDATE(), NULL)
                ");
                    }
                }
            }

            // 3️⃣ ASIGNA PUESTO(S) - Manejo de múltiples puestos
            // Si viene el array puestos_adicionales, usamos ese; si no, usamos el puesto_id tradicional
            $idsPuestosGuardar = [];

            if ($sincronizarPuestosDesdeLista) {
                foreach ($idsPuestosEntrada as $puestoId) {
                    $idsPuestosGuardar[(int)$puestoId] = true;
                }
            } elseif ($id_puesto > 0) {
                $idsPuestosGuardar[$id_puesto] = true;
            }

            $idsPuestosGuardar = array_keys($idsPuestosGuardar);
            if (!$sincronizarPuestosDesdeLista && empty($idsPuestosGuardar)) {
                throw new \Exception('Debe quedar al menos un puesto asignado.');
            }
            $fechaAsignacionCdmx = self::fechaHoraCdmx();

            $idsPuestosActivosAntes = [];
            foreach ($puestosTrayectoriaAntes as $puestoActivoAntes) {
                $idPuestoActivoAntes = (int)($puestoActivoAntes['id_puesto'] ?? 0);
                if ($idPuestoActivoAntes > 0) {
                    $idsPuestosActivosAntes[$idPuestoActivoAntes] = true;
                }
            }

            $db->CRUD(
                "UPDATE estado_cuenta.asigna_puesto
                 SET activo = 0
                 WHERE id_persona = :id_persona",
                ['id_persona' => $id_persona]
            );

            foreach ($idsPuestosGuardar as $puestoId) {
                $asignacionExistente = $db->queryOne(
                    "SELECT id
                     FROM estado_cuenta.asigna_puesto
                     WHERE id_persona = :id_persona
                       AND id_puesto = :id_puesto
                     ORDER BY id DESC
                     LIMIT 1",
                    ['id_persona' => $id_persona, 'id_puesto' => $puestoId]
                );

                if ($asignacionExistente) {
                    $actualizarFechaAsignacion = !isset($idsPuestosActivosAntes[(int)$puestoId]);
                    $db->CRUD(
                        "UPDATE estado_cuenta.asigna_puesto
                         SET activo = 1" . ($actualizarFechaAsignacion ? ", fecha_asignacion = :fecha_asignacion" : "") . "
                         WHERE id = :id",
                        $actualizarFechaAsignacion
                            ? ['id' => (int)$asignacionExistente['id'], 'fecha_asignacion' => $fechaAsignacionCdmx]
                            : ['id' => (int)$asignacionExistente['id']]
                    );
                } else {
                    $db->CRUD(
                        "INSERT INTO estado_cuenta.asigna_puesto (id_persona, id_puesto, fecha_asignacion, activo)
                         VALUES (:id_persona, :id_puesto, :fecha_asignacion, 1)",
                        ['id_persona' => $id_persona, 'id_puesto' => $puestoId, 'fecha_asignacion' => $fechaAsignacionCdmx]
                    );
                }
            }

            // 4️⃣ ASIGNA LEGIÓN
            foreach ($idsPuestosGuardar as $puestoIdAutoPermiso) {
                self::aplicarPermisosPuestoAPersonaConDb($db, (int) $id_persona, (int) $puestoIdAutoPermiso);
            }

            if ($esDegradacionConHueco && !empty($subordinadosPuestoAnterior) && in_array($resolverPuestoAnterior, ['vacante', 'sustituto'], true)) {
                $idsSubordinados = array_values(array_map(function ($row) {
                    return (int)$row['id'];
                }, $subordinadosPuestoAnterior));
                $phSubordinados = [];
                $paramsSubordinados = ['id_persona' => $id_persona];
                foreach ($idsSubordinados as $idxSub => $idSubordinado) {
                    $keySub = 'sub_' . $idxSub;
                    $phSubordinados[] = ':' . $keySub;
                    $paramsSubordinados[$keySub] = $idSubordinado;
                }

                if (!empty($phSubordinados)) {
                    if ($resolverPuestoAnterior === 'vacante') {
                        $idJefeVacanteAnterior = !empty($puestoAnterior['id_jefe_anterior'])
                            ? (int)$puestoAnterior['id_jefe_anterior']
                            : null;
                        $db->CRUD("
                            INSERT INTO estado_cuenta.vacantes_personal
                                (id_departamento, id_puesto, id_jefe, id_persona_baja, origen, estatus, creado_por)
                            VALUES
                                (:id_departamento, :id_puesto, :id_jefe, NULL, 'degradacion', 'Activa', :creado_por)
                        ", [
                            'id_departamento' => (int)$puestoAnterior['departamento_id'],
                            'id_puesto' => (int)$puestoAnterior['id_puesto'],
                            'id_jefe' => $idJefeVacanteAnterior,
                            'creado_por' => !empty($data['usuario_edita']) ? (int)$data['usuario_edita'] : null,
                        ]);
                        $idVacantePuestoAnterior = $db->lastInsertId();

                        $db->CRUD("
                            UPDATE estado_cuenta.asigna_jefe
                            SET id_jefe = NULL,
                                id_vacante_jefe = :id_vacante_jefe
                            WHERE id_jefe = :id_persona
                              AND id_persona IN (" . implode(',', $phSubordinados) . ")
                        ", array_merge($paramsSubordinados, [
                            'id_vacante_jefe' => $idVacantePuestoAnterior,
                        ]));
                    } else {
                        if (in_array($idSustitutoPuestoAnterior, $idsSubordinados, true)) {
                            if (!empty($puestoAnterior['id_vacante_jefe_anterior'])) {
                                $db->CRUD("
                                    UPDATE estado_cuenta.asigna_jefe
                                    SET id_jefe = NULL,
                                        id_vacante_jefe = :id_vacante_jefe
                                    WHERE id_persona = :id_sustituto
                                ", [
                                    'id_vacante_jefe' => (int)$puestoAnterior['id_vacante_jefe_anterior'],
                                    'id_sustituto' => $idSustitutoPuestoAnterior,
                                ]);
                            } else {
                                $db->CRUD("
                                    UPDATE estado_cuenta.asigna_jefe
                                    SET id_jefe = :id_jefe_anterior,
                                        id_vacante_jefe = NULL
                                    WHERE id_persona = :id_sustituto
                                ", [
                                    'id_jefe_anterior' => !empty($puestoAnterior['id_jefe_anterior']) ? (int)$puestoAnterior['id_jefe_anterior'] : null,
                                    'id_sustituto' => $idSustitutoPuestoAnterior,
                                ]);
                            }
                        }

                        $idsParaSustituto = array_values(array_filter($idsSubordinados, function ($idSubordinado) use ($idSustitutoPuestoAnterior) {
                            return (int)$idSubordinado !== (int)$idSustitutoPuestoAnterior;
                        }));
                        if (!empty($idsParaSustituto)) {
                            $phSustituto = [];
                            $paramsSustituto = [
                                'id_persona' => $id_persona,
                                'id_sustituto' => $idSustitutoPuestoAnterior,
                            ];
                            foreach ($idsParaSustituto as $idxSustituto => $idSubordinado) {
                                $keySustituto = 'sust_' . $idxSustituto;
                                $phSustituto[] = ':' . $keySustituto;
                                $paramsSustituto[$keySustituto] = (int)$idSubordinado;
                            }
                            $db->CRUD("
                                UPDATE estado_cuenta.asigna_jefe
                                SET id_jefe = :id_sustituto,
                                    id_vacante_jefe = NULL
                                WHERE id_jefe = :id_persona
                                  AND id_persona IN (" . implode(',', $phSustituto) . ")
                            ", $paramsSustituto);
                        }
                    }
                }
            }

            if (empty($data['_preservar_legion'])) {
                $asignarLegion = isset($data['asignar_legion']) && $data['asignar_legion'];
                $id_legion = isset($data['id_legion']) && $data['id_legion'] !== '' && $data['id_legion'] !== null
                    ? (int)$data['id_legion']
                    : null;

                $db->queryOne("
                    UPDATE estado_cuenta.asigna_legion
                    SET activo = 0, fecha_fin = NOW()
                    WHERE id_persona = $id_persona AND activo = 1
                ");

                if ($asignarLegion && $id_legion) {
                    $db->queryOne("
                        INSERT INTO estado_cuenta.asigna_legion
                            (id, id_persona, id_legion, fecha_asignacion, activo)
                        VALUES
                            (DEFAULT, $id_persona, $id_legion, NOW(), 1)
                    ");
                }
            }

            // Auto-sincronizar despachos según los puestos actualizados
            $idCelulaDespacho = null;
            if (!empty($idsPuestosGuardar)) {
                foreach ($idsPuestosGuardar as $puestoIdDespacho) {
                    $cel = self::resolverCelulaDespacho($db, (int)$puestoIdDespacho);
                    if ($cel !== null) {
                        $idCelulaDespacho = $cel;
                        break;
                    }
                }
            }

            $existeDespachoActivo = $db->queryOne(
                "SELECT id FROM despachos WHERE id_persona = :idp AND estatus = 'Activo' LIMIT 1",
                ['idp' => $id_persona]
            );

            if ($idCelulaDespacho !== null) {
                if ($existeDespachoActivo) {
                    $db->queryOne(
                        'UPDATE despachos SET id_celula = :cel WHERE id = :id',
                        ['cel' => $idCelulaDespacho, 'id' => $existeDespachoActivo['id']]
                    );
                } else {
                    $db->queryOne(
                        "INSERT INTO despachos (id_persona, estatus, fecha_alta, id_celula) VALUES (:idp, 'Activo', NOW(), :cel)",
                        ['idp' => $id_persona, 'cel' => $idCelulaDespacho]
                    );
                }
            } elseif ($existeDespachoActivo) {
                $db->queryOne(
                    "UPDATE despachos SET estatus = 'Inactivo' WHERE id = :id",
                    ['id' => $existeDespachoActivo['id']]
                );
            }

            $puestosTrayectoriaDespues = self::puestosActivosTrayectoria($db, $id_persona);
            self::registrarCambiosTrayectoriaPuestos(
                $db,
                $id_persona,
                $puestosTrayectoriaAntes,
                $puestosTrayectoriaDespues,
                !empty($data['usuario_edita']) ? (int)$data['usuario_edita'] : (int)($_SESSION['usuario_id'] ?? 0),
                'edicion_gestion_personal'
            );

            if ($transaccionActiva && $db->inTransaction()) {
                $db->commit();
            }
            $transaccionActiva = false;

            return self::resultado(true, 'Persona actualizada correctamente.', null);

        } catch (\Exception $e) {
            if (isset($db) && $transaccionActiva && $db->inTransaction()) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al actualizar persona.', null, $e->getMessage());
        }
    }

    /**
     * Resuelve el id_celula para la tabla despachos según el nombre del puesto.
     * Devuelve 1 (Despacho), 2 (Call Center) o null si el puesto no aplica.
     *
     * Puestos cubiertas:
     *  id_celula = 1: Gestor Despacho, Supervisor Despacho
     *  id_celula = 2: Agente Call Center, Supervisora Call Center
     */
    private static function resolverCelulaDespacho(Database $db, int $id_puesto): ?int
    {
        if ($id_puesto <= 0) {
            return null;
        }
        $row = $db->queryOne(
            'SELECT nombre FROM puesto WHERE id = :id LIMIT 1',
            ['id' => $id_puesto]
        );
        if (!$row) {
            return null;
        }
        $nombre = strtolower(trim((string)($row['nombre'] ?? '')));
        if (in_array($nombre, ['agente call center', 'supervisora call center'], true)) {
            return 2;
        }
        if (in_array($nombre, ['gestor despacho', 'supervisor despacho'], true)) {
            return 1;
        }
        return null;
    }

    private static function obtenerBloqueoBajaActivo(Database $db, int $idPersona): ?array
    {
        if ($idPersona <= 0) {
            return [
                'motivo' => 'Persona invalida',
                'mensaje' => 'No se puede validar la baja porque la persona no es valida.',
            ];
        }

        $ausencia = $db->queryOne("
            SELECT
                ra.nombre AS motivo,
                a.fecha_inicio,
                a.fecha_fin
            FROM estado_cuenta.ausencia a
            INNER JOIN estado_cuenta.razon_ausencia ra ON ra.id = a.id_razon
            WHERE a.id_persona = :id_persona
              AND a.activo = 1
              AND DATE(a.fecha_inicio) <= CURDATE()
              AND DATE(a.fecha_fin) >= CURDATE()
            ORDER BY a.fecha_fin DESC, a.id DESC
            LIMIT 1
        ", ['id_persona' => $idPersona]);

        if ($ausencia) {
            $motivo = trim((string)($ausencia['motivo'] ?? 'ausencia'));
            return [
                'motivo' => $motivo,
                'fecha_inicio' => $ausencia['fecha_inicio'] ?? null,
                'fecha_fin' => $ausencia['fecha_fin'] ?? null,
                'mensaje' => 'No se puede dar de baja: la persona tiene ' . $motivo . ' vigente del '
                    . date('d/m/Y', strtotime((string)$ausencia['fecha_inicio'])) . ' al '
                    . date('d/m/Y', strtotime((string)$ausencia['fecha_fin'])) . '.',
            ];
        }

        $vacaciones = $db->queryOne("
            SELECT
                s.id,
                COALESCE(MIN(d.fecha), s.fecha_inicio) AS fecha_inicio,
                COALESCE(MAX(d.fecha), s.fecha_fin) AS fecha_fin
            FROM estado_cuenta.vacaciones_solicitudes s
            LEFT JOIN estado_cuenta.vacaciones_solicitud_dias d ON d.id_solicitud = s.id
            WHERE s.id_persona = :id_persona
              AND s.estatus IN ('aprobada', 'tomada')
              AND (
                  CURDATE() BETWEEN DATE(s.fecha_inicio) AND DATE(s.fecha_fin)
                  OR d.fecha = CURDATE()
              )
            GROUP BY s.id, s.fecha_inicio, s.fecha_fin
            ORDER BY fecha_fin DESC, s.id DESC
            LIMIT 1
        ", ['id_persona' => $idPersona]);

        if ($vacaciones) {
            return [
                'motivo' => 'VACACIONES',
                'fecha_inicio' => $vacaciones['fecha_inicio'] ?? null,
                'fecha_fin' => $vacaciones['fecha_fin'] ?? null,
                'mensaje' => 'No se puede dar de baja: la persona tiene VACACIONES vigentes del '
                    . date('d/m/Y', strtotime((string)$vacaciones['fecha_inicio'])) . ' al '
                    . date('d/m/Y', strtotime((string)$vacaciones['fecha_fin'])) . '.',
            ];
        }

        return null;
    }

    /**
     * Asegura las columnas que permiten separar el trámite de baja de la baja definitiva.
     * Los registros anteriores se conservan como bajas finalizadas.
     */
    private static function asegurarSeguimientoTransitoBaja(Database $db): void
    {
        static $columnasVerificadas = false;
        if ($columnasVerificadas) {
            return;
        }

        $columnaEstatus = $db->queryOne("
            SHOW COLUMNS FROM estado_cuenta.baja_persona LIKE 'estatus_tramite'
        ");
        if (!$columnaEstatus) {
            $db->CRUD("
                ALTER TABLE estado_cuenta.baja_persona
                ADD COLUMN estatus_tramite VARCHAR(30) NOT NULL DEFAULT 'Finalizada'
                AFTER usuario_baja
            ");
        }

        $columnaFecha = $db->queryOne("
            SHOW COLUMNS FROM estado_cuenta.baja_persona LIKE 'fecha_transito'
        ");
        if (!$columnaFecha) {
            $db->CRUD("
                ALTER TABLE estado_cuenta.baja_persona
                ADD COLUMN fecha_transito DATETIME NULL
                AFTER fecha_baja
            ");
        }

        $columnasSeguimiento = [
            'estatus_anterior' => "VARCHAR(30) NULL AFTER estatus_tramite",
            'despachos_activos_previos' => "INT NOT NULL DEFAULT 0 AFTER estatus_anterior",
            'fecha_cancelacion' => "DATETIME NULL AFTER fecha_transito",
            'usuario_cancelacion' => "INT NULL AFTER fecha_cancelacion",
            'fecha_finalizacion' => "DATETIME NULL AFTER usuario_cancelacion",
            'usuario_finalizacion' => "INT NULL AFTER fecha_finalizacion",
            'tipo_documento_final' => "VARCHAR(50) NULL AFTER usuario_finalizacion",
        ];
        foreach ($columnasSeguimiento as $nombreColumna => $definicionColumna) {
            $columna = $db->queryOne("SHOW COLUMNS FROM estado_cuenta.baja_persona LIKE '" . $nombreColumna . "'");
            if (!$columna) {
                $db->CRUD("ALTER TABLE estado_cuenta.baja_persona ADD COLUMN " . $nombreColumna . " " . $definicionColumna);
            }
        }

        $db->CRUD("
            UPDATE estado_cuenta.baja_persona
            SET estatus_tramite = 'Finalizada'
            WHERE estatus_tramite IS NULL OR TRIM(estatus_tramite) = ''
        ");

        $columnasVerificadas = true;
    }

    /**
     * Primer paso de baja: bloquea a la persona para operación sin ejecutar aún
     * la baja definitiva, las reasignaciones ni la sincronización al sistema legado.
     */
    public static function registrarBajaGestor($data)
    {
        $db = null;

        try {
            $db = new Database();
            self::asegurarSeguimientoTransitoBaja($db);

            $idPersona = (int)($data['id_gestor'] ?? 0);
            $motivo = trim((string)($data['motivo'] ?? ''));
            $descripcion = trim((string)($data['descripcion'] ?? ''));
            $fechaTransito = (string)($data['fecha_baja'] ?? '');
            $usuarioBaja = (int)($data['usuario_baja'] ?? 0);
            $archivos = is_array($data['archivos'] ?? null) ? $data['archivos'] : [];

            if ($idPersona < 1 || $motivo === '' || $descripcion === '') {
                return self::resultado(false, 'Faltan datos obligatorios para iniciar el trámite de baja.');
            }

            if ($fechaTransito === '') {
                $fechaTransito = (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
            }

            $db->beginTransaction();

            $personaActual = $db->queryOne("
                SELECT id, COALESCE(estatus, '') AS estatus
                FROM estado_cuenta.persona
                WHERE id = :id_persona
                FOR UPDATE
            ", ['id_persona' => $idPersona]);

            if (!$personaActual) {
                $db->rollback();
                return self::resultado(false, 'La persona indicada no existe.');
            }

            $estatusActual = strtolower(trim((string)$personaActual['estatus']));
            if ($estatusActual === 'baja') {
                $db->rollback();
                return self::resultado(false, 'Esta persona ya se encuentra dada de baja definitivamente.');
            }
            if ($estatusActual === 'transito de baja') {
                $db->rollback();
                return self::resultado(false, 'Esta persona ya tiene un trámite de baja pendiente de documentos.');
            }

            $bloqueoBaja = self::obtenerBloqueoBajaActivo($db, $idPersona);
            if ($bloqueoBaja) {
                $db->rollback();
                return self::resultado(false, $bloqueoBaja['mensaje']);
            }

            $despachosPrevios = $db->queryOne("
                SELECT COUNT(*) AS total
                FROM estado_cuenta.despachos
                WHERE id_persona = :id_persona AND estatus = 'Activo'
            ", ['id_persona' => $idPersona]);
            $despachosActivosPrevios = (int)($despachosPrevios['total'] ?? 0);

            $db->CRUD("
                INSERT INTO estado_cuenta.baja_persona
                    (id_persona, motivo, fecha_baja, fecha_transito, descripcion, usuario_baja, estatus_tramite, estatus_anterior, despachos_activos_previos)
                VALUES
                    (:id_persona, :motivo, :fecha_baja, :fecha_transito, :descripcion, :usuario_baja, 'Transito', :estatus_anterior, :despachos_activos_previos)
            ", [
                'id_persona' => $idPersona,
                'motivo' => $motivo,
                'fecha_baja' => $fechaTransito,
                'fecha_transito' => $fechaTransito,
                'descripcion' => $descripcion,
                'usuario_baja' => $usuarioBaja,
                'estatus_anterior' => trim((string)$personaActual['estatus']) ?: 'Activo',
                'despachos_activos_previos' => $despachosActivosPrevios,
            ]);

            $idBaja = (int)$db->lastInsertId();
            if ($idBaja < 1) {
                throw new \RuntimeException('No se pudo crear el trámite de baja.');
            }

            foreach ($archivos as $archivo) {
                $archivo = trim((string)$archivo);
                if ($archivo === '') {
                    continue;
                }
                $db->CRUD("
                    INSERT INTO estado_cuenta.carga_documento_persona
                        (id_persona, id_documento, archivo, fecha_carga)
                    VALUES
                        (:id_persona, :id_documento, :archivo, :fecha_carga)
                ", [
                    'id_persona' => $idPersona,
                    'id_documento' => 15,
                    'archivo' => $archivo,
                    'fecha_carga' => $fechaTransito,
                ]);
            }

            $db->CRUD("
                UPDATE estado_cuenta.persona
                SET estatus = 'Transito de baja'
                WHERE id = :id_persona
            ", ['id_persona' => $idPersona]);

            // El despacho se inhabilita de inmediato para impedir nuevas asignaciones operativas.
            $db->CRUD("
                UPDATE estado_cuenta.despachos
                SET estatus = 'Inactivo'
                WHERE id_persona = :id_persona
                  AND estatus = 'Activo'
            ", ['id_persona' => $idPersona]);

            $db->commit();

            return self::resultado(true, 'Trámite de baja iniciado. La persona queda fuera de cartera y asignaciones hasta completar sus documentos.', [
                'id_persona' => $idPersona,
                'id_baja' => $idBaja,
                'estatus_tramite' => 'Transito',
            ]);
        } catch (\Exception $e) {
            if ($db) {
                try {
                    $db->rollback();
                } catch (\Exception $rollbackError) {
                }
            }

            return self::resultado(false, 'Error al iniciar el trámite de baja.', null, $e->getMessage());
        }
    }

    /**
     * Flujo definitivo reservado para cuando el expediente de baja esté completo.
     */
    /** Cancela un trámite pendiente y devuelve a la persona a su estado anterior. */
    public static function cancelarTransitoBajaGestor($data)
    {
        $db = null;
        try {
            $db = new Database();
            self::asegurarSeguimientoTransitoBaja($db);
            $idPersona = (int)($data['id_gestor'] ?? 0);
            $usuario = (int)($data['usuario_cancelacion'] ?? 0);
            $fecha = trim((string)($data['fecha_cancelacion'] ?? ''));
            if ($idPersona < 1) return self::resultado(false, 'La persona indicada no es válida.');
            if ($fecha === '') $fecha = (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');

            $db->beginTransaction();
            $persona = $db->queryOne("SELECT id, COALESCE(estatus, '') AS estatus FROM estado_cuenta.persona WHERE id = :id_persona FOR UPDATE", ['id_persona' => $idPersona]);
            if (!$persona || strtolower(trim((string)$persona['estatus'])) !== 'transito de baja') {
                $db->rollback();
                return self::resultado(false, 'La persona ya no tiene un trámite de baja que se pueda cancelar.');
            }
            $tramite = $db->queryOne("
                SELECT id, COALESCE(estatus_anterior, '') AS estatus_anterior, COALESCE(despachos_activos_previos, 0) AS despachos_activos_previos
                FROM estado_cuenta.baja_persona
                WHERE id_persona = :id_persona AND estatus_tramite = 'Transito'
                ORDER BY id DESC LIMIT 1 FOR UPDATE
            ", ['id_persona' => $idPersona]);
            if (!$tramite) {
                $db->rollback();
                return self::resultado(false, 'No se encontró el registro del trámite de baja pendiente.');
            }

            $estatusAnterior = trim((string)$tramite['estatus_anterior']);
            if ($estatusAnterior === '' || in_array(strtolower($estatusAnterior), ['baja', 'transito de baja'], true)) $estatusAnterior = 'Activo';
            $db->CRUD("UPDATE estado_cuenta.persona SET estatus = :estatus WHERE id = :id_persona", ['estatus' => $estatusAnterior, 'id_persona' => $idPersona]);
            if ((int)$tramite['despachos_activos_previos'] > 0) {
                $db->CRUD("UPDATE estado_cuenta.despachos SET estatus = 'Activo' WHERE id_persona = :id_persona AND estatus = 'Inactivo'", ['id_persona' => $idPersona]);
            }
            $db->CRUD("UPDATE estado_cuenta.baja_persona SET estatus_tramite = 'Cancelado', fecha_cancelacion = :fecha, usuario_cancelacion = :usuario WHERE id = :id", ['fecha' => $fecha, 'usuario' => $usuario ?: null, 'id' => (int)$tramite['id']]);
            $db->commit();
            return self::resultado(true, 'Se canceló el trámite de baja y la persona volvió a su estado anterior.', ['id_persona' => $idPersona, 'estatus' => $estatusAnterior]);
        } catch (\Exception $e) {
            if ($db) { try { $db->rollback(); } catch (\Exception $ignored) {} }
            return self::resultado(false, 'No se pudo cancelar el trámite de baja.', null, $e->getMessage());
        }
    }

    public static function finalizarBajaGestor($data)
    {
        try {
            $db = new Database();
            self::asegurarSeguimientoTransitoBaja($db);

            // 🔹 Escapamos valores
            $id_persona  = addslashes((string)($data['id_gestor'] ?? ''));
            $motivo      = addslashes((string)($data['motivo'] ?? ''));
            $descripcion = addslashes((string)($data['descripcion'] ?? ''));
            $fecha_baja  = addslashes((string)($data['fecha_baja'] ?? ''));
            $usuario_baja  = addslashes((string)($data['usuario_baja'] ?? ''));
            $archivos    = $data['archivos'] ?? [];
            $modoReasignacion = $data['modo_reasignacion'] ?? '';
            $tipoDocumentoFinal = strtolower(trim((string)($data['tipo_documento_final'] ?? '')));
            $sustitutoId = !empty($data['sustituto_id']) ? (int) $data['sustituto_id'] : null;
            $subordinadosSeleccionadosRaw = $data['subordinados_seleccionados'] ?? null;
            $asignacionesJefeRaw = is_array($data['asignaciones_jefe'] ?? null) ? $data['asignaciones_jefe'] : [];
            $asignacionesJefe = [];
            $vacanteExistenteId = !empty($data['vacante_existente_id']) ? (int)$data['vacante_existente_id'] : null;

            // 0️⃣ Guard de idempotencia: solo bloquear si la persona YA está de baja actualmente.
            // No se usa baja_persona como guard porque puede tener registros históricos (bajas previas
            // antes de un reingreso), y esos casos deben permitir una nueva baja.
            $personaActual = $db->queryOne("
                SELECT estatus FROM estado_cuenta.persona WHERE id = '$id_persona' LIMIT 1
            ");
            if (!$personaActual || strtolower(trim((string)$personaActual['estatus'])) !== 'transito de baja') {
                return self::resultado(false, 'La baja definitiva solo se puede completar desde un trámite de baja pendiente.');
            }
            if (!in_array($modoReasignacion, ['vacante', 'sustituto'], true)) {
                return self::resultado(false, 'Debe elegir si la posición queda como vacante o con sustituto.');
            }
            if (!in_array($tipoDocumentoFinal, ['renuncia', 'aviso_rescision'], true)) {
                return self::resultado(false, 'Debe elegir Renuncia o Aviso de rescisión como documento de baja.');
            }
            if (empty($archivos)) {
                return self::resultado(false, 'Debe adjuntar obligatoriamente el documento de baja en PDF.');
            }

            $bloqueoBaja = self::obtenerBloqueoBajaActivo($db, (int)$id_persona);
            if ($bloqueoBaja) {
                return self::resultado(false, $bloqueoBaja['mensaje']);
            }

            $subordinadosActivos = $db->queryAll("
                SELECT aj.id_persona
                FROM estado_cuenta.asigna_jefe aj
                INNER JOIN estado_cuenta.persona p ON p.id = aj.id_persona
                WHERE aj.id_jefe = :id_persona
                  AND LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
            ", ['id_persona' => (int) $id_persona]);

            if (!empty($subordinadosActivos) && is_array($subordinadosSeleccionadosRaw)) {
                $seleccionados = [];
                foreach ($subordinadosSeleccionadosRaw as $idSeleccionado) {
                    $idSeleccionado = (int)$idSeleccionado;
                    if ($idSeleccionado > 0) $seleccionados[$idSeleccionado] = true;
                }
                $subordinadosActivos = array_values(array_filter($subordinadosActivos, function ($row) use ($seleccionados) {
                    return isset($seleccionados[(int)($row['id_persona'] ?? 0)]);
                }));
                if (empty($subordinadosActivos)) {
                    return self::resultado(false, 'Debe seleccionar al menos un subordinado para reasignar.');
                }
            }

            if (!empty($subordinadosActivos)) {
                if (!in_array($modoReasignacion, ['vacante', 'sustituto'], true)) {
                    return self::resultado(false, 'Debe seleccionar si los subordinados quedan como vacante o pasan a un sustituto.');
                }

                if ($modoReasignacion === 'sustituto') {
                    $idsSubordinadosValidos = [];
                    foreach ($subordinadosActivos as $rowSubordinado) {
                        $idsSubordinadosValidos[(int)$rowSubordinado['id_persona']] = true;
                    }

                    foreach ($asignacionesJefeRaw as $idSubordinado => $idJefeDestino) {
                        $idSubordinado = (int)$idSubordinado;
                        $idJefeDestino = (int)$idJefeDestino;
                        if ($idSubordinado > 0 && $idJefeDestino > 0 && isset($idsSubordinadosValidos[$idSubordinado])) {
                            $asignacionesJefe[$idSubordinado] = $idJefeDestino;
                        }
                    }

                    if (empty($asignacionesJefe) && $sustitutoId) {
                        foreach ($idsSubordinadosValidos as $idSubordinado => $_) {
                            $asignacionesJefe[$idSubordinado] = $sustitutoId;
                        }
                    }

                    foreach ($idsSubordinadosValidos as $idSubordinado => $_) {
                        if (empty($asignacionesJefe[$idSubordinado])) {
                            return self::resultado(false, 'Debe asignar un jefe destino a todas las personas seleccionadas.');
                        }
                        if ((int)$asignacionesJefe[$idSubordinado] === (int)$id_persona) {
                            return self::resultado(false, 'El jefe destino no puede ser la persona que se dara de baja.');
                        }
                    }

                    $idsJefesDestino = array_values(array_unique(array_map('intval', array_values($asignacionesJefe))));
                    $phJefes = [];
                    $paramsJefes = [];
                    foreach ($idsJefesDestino as $idxJefe => $idJefeDestino) {
                        $keyJefe = 'jefe_' . $idxJefe;
                        $phJefes[] = ':' . $keyJefe;
                        $paramsJefes[$keyJefe] = $idJefeDestino;
                    }
                    $jefesActivosRows = $db->queryAll("
                        SELECT id
                        FROM estado_cuenta.persona
                        WHERE LOWER(TRIM(COALESCE(estatus, ''))) NOT IN ('baja', 'transito de baja')
                          AND id IN (" . implode(',', $phJefes) . ")
                    ", $paramsJefes);
                    $jefesActivos = [];
                    foreach ($jefesActivosRows as $rowJefe) {
                        $jefesActivos[(int)$rowJefe['id']] = true;
                    }
                    foreach ($idsJefesDestino as $idJefeDestino) {
                        if (empty($jefesActivos[$idJefeDestino])) {
                            return self::resultado(false, 'Uno de los jefes destino no esta activo o no existe.');
                        }
                    }
                }
            }

            $puestoVacante = null;
            $idJefeVacante = null;
            $vacanteDestinoId = null;
            if ($modoReasignacion === 'vacante') {
                self::asegurarTablaVacantesPersonal($db);
                self::asegurarAsignaJefeSoportaVacante($db);

                $puestoVacante = $db->queryOne("
                    SELECT ap.id_puesto, pp.departamento_id
                    FROM estado_cuenta.asigna_puesto ap
                    INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                    WHERE ap.id_persona = :id_persona
                      AND COALESCE(ap.activo, 1) = 1
                    ORDER BY pp.nivel DESC, ap.id ASC
                    LIMIT 1
                ", ['id_persona' => (int) $id_persona]);

                if (empty($puestoVacante['id_puesto']) || empty($puestoVacante['departamento_id'])) {
                    $puestoVacante = $db->queryOne("
                        SELECT ap.id_puesto, pp.departamento_id
                        FROM estado_cuenta.asigna_puesto ap
                        INNER JOIN estado_cuenta.puesto pp ON pp.id = ap.id_puesto
                        WHERE ap.id_persona = :id_persona
                        ORDER BY COALESCE(ap.activo, 0) DESC, pp.nivel DESC, ap.id DESC
                        LIMIT 1
                    ", ['id_persona' => (int) $id_persona]);
                }

                $jefeVacante = $db->queryOne("
                    SELECT id_jefe
                    FROM estado_cuenta.asigna_jefe
                    WHERE id_persona = :id_persona
                    ORDER BY id DESC
                    LIMIT 1
                ", ['id_persona' => (int)$id_persona]);
                $idJefeVacante = !empty($jefeVacante['id_jefe']) ? (int)$jefeVacante['id_jefe'] : null;

                if (empty($puestoVacante['id_puesto']) || empty($puestoVacante['departamento_id'])) {
                    return self::resultado(false, 'No se pudo crear la vacante porque la persona no tiene un puesto activo asignado.');
                }

                if (!$vacanteExistenteId) {
                    $vacanteActiva = $db->queryOne("
                        SELECT id
                        FROM estado_cuenta.vacantes_personal
                        WHERE id_puesto = :id_puesto
                          AND id_departamento = :id_departamento
                          AND UPPER(TRIM(estatus)) = 'ACTIVA'
                        ORDER BY id ASC
                        LIMIT 1
                    ", [
                        'id_puesto' => (int)$puestoVacante['id_puesto'],
                        'id_departamento' => (int)$puestoVacante['departamento_id'],
                    ]);
                    if (!empty($vacanteActiva['id'])) {
                        return self::resultado(false, 'Ya existe una vacante activa para este puesto. Seleccione esa vacante antes de confirmar la baja.');
                    }
                }
            }

            $db->beginTransaction();

            // 1️⃣ Insertar la baja en baja_persona
            // Se finaliza el mismo trámite iniciado antes; no se duplica el historial de baja.
            $tramite = $db->queryOne("
                SELECT id
                FROM estado_cuenta.baja_persona
                WHERE id_persona = :id_persona
                  AND estatus_tramite = 'Transito'
                ORDER BY id DESC
                LIMIT 1
                FOR UPDATE
            ", ['id_persona' => (int)$id_persona]);
            $id_baja = (int)($tramite['id'] ?? 0);
            if ($id_baja < 1) {
                throw new \RuntimeException('No se encontró el trámite de baja pendiente para finalizar.');
            }

            $db->CRUD("
                UPDATE estado_cuenta.baja_persona
                SET motivo = CASE WHEN :motivo = '' THEN motivo ELSE :motivo END,
                    fecha_baja = :fecha_baja,
                    descripcion = CASE WHEN :descripcion = '' THEN descripcion ELSE :descripcion END,
                    usuario_baja = :usuario_baja,
                    estatus_tramite = 'Finalizada',
                    fecha_finalizacion = :fecha_finalizacion,
                    usuario_finalizacion = :usuario_finalizacion,
                    tipo_documento_final = :tipo_documento_final
                WHERE id = :id_baja
            ", [
                'motivo' => stripslashes($motivo),
                'fecha_baja' => stripslashes($fecha_baja),
                'descripcion' => stripslashes($descripcion),
                'usuario_baja' => (int)$usuario_baja,
                'fecha_finalizacion' => stripslashes($fecha_baja),
                'usuario_finalizacion' => (int)$usuario_baja,
                'tipo_documento_final' => $tipoDocumentoFinal,
                'id_baja' => $id_baja,
            ]);

            // 2️⃣ Insertar cada archivo en carga_documento_persona
            foreach ($archivos as $archivo) {

                // Asumimos que el documento 'Documento baja' ya existe con id = 15
                $id_documento = 15;

                $archivoEsc = addslashes($archivo);

                $db->queryOne("
                INSERT INTO estado_cuenta.carga_documento_persona
                (id_persona, id_documento, archivo, fecha_carga)
                VALUES
                ('$id_persona', '$id_documento', '$archivoEsc', '$fecha_baja')
            ");
            }

            // 3️⃣ Actualizar estatus de la persona a 'Baja'
            $db->queryOne("
            UPDATE estado_cuenta.persona
            SET estatus = 'Baja'
            WHERE id = '$id_persona'
        ");

            // 4️⃣ Inhabilitar en despachos si el gestor estaba registrado
            $db->queryOne("
            UPDATE estado_cuenta.despachos
            SET estatus = 'Inactivo'
            WHERE id_persona = '$id_persona' AND estatus = 'Activo'
        ");

            if ($modoReasignacion === 'vacante' && !$vacanteExistenteId) {
                $db->CRUD("
                    INSERT INTO estado_cuenta.vacantes_personal
                        (id_departamento, id_puesto, id_jefe, id_persona_baja, origen, estatus, creado_por)
                    VALUES
                        (:id_departamento, :id_puesto, :id_jefe, :id_persona_baja, 'baja', 'Activa', :creado_por)
                ", [
                    'id_departamento' => (int)$puestoVacante['departamento_id'],
                    'id_puesto' => (int)$puestoVacante['id_puesto'],
                    'id_jefe' => $idJefeVacante,
                    'id_persona_baja' => (int)$id_persona,
                    'creado_por' => (int)$usuario_baja,
                ]);
                $vacanteDestinoId = $db->lastInsertId();
            } elseif ($modoReasignacion === 'vacante') {
                $vacanteDestinoId = (int)$vacanteExistenteId;
                $db->CRUD("
                    UPDATE estado_cuenta.vacantes_personal
                    SET id_jefe = :id_jefe
                    WHERE id = :id_vacante
                ", [
                    'id_jefe' => $idJefeVacante,
                    'id_vacante' => $vacanteDestinoId,
                ]);
            }

            if (!empty($subordinadosActivos)) {
                $idsSubordinadosReasignar = array_values(array_map(function ($row) {
                    return (int)$row['id_persona'];
                }, $subordinadosActivos));
                $phSubordinados = [];
                $paramsSubordinados = ['id_persona' => (int)$id_persona];
                foreach ($idsSubordinadosReasignar as $idxSub => $idSubordinado) {
                    $keySub = 'sub_' . $idxSub;
                    $phSubordinados[] = ':' . $keySub;
                    $paramsSubordinados[$keySub] = $idSubordinado;
                }

                if ($modoReasignacion === 'sustituto') {
                    $porJefe = [];
                    foreach ($idsSubordinadosReasignar as $idSubordinado) {
                        $idJefeDestino = (int)($asignacionesJefe[$idSubordinado] ?? 0);
                        if ($idJefeDestino > 0) $porJefe[$idJefeDestino][] = $idSubordinado;
                    }
                    foreach ($porJefe as $idJefeDestino => $idsGrupo) {
                        $phGrupo = [];
                        $paramsGrupo = [
                            'id_persona' => (int)$id_persona,
                            'jefe_destino' => (int)$idJefeDestino,
                        ];
                        foreach ($idsGrupo as $idxGrupo => $idGrupoSubordinado) {
                            $keyGrupo = 'grupo_' . $idxGrupo;
                            $phGrupo[] = ':' . $keyGrupo;
                            $paramsGrupo[$keyGrupo] = (int)$idGrupoSubordinado;
                        }
                        $db->CRUD("
                            UPDATE estado_cuenta.asigna_jefe
                            SET id_jefe = :jefe_destino
                            WHERE id_jefe = :id_persona
                              AND id_persona IN (" . implode(',', $phGrupo) . ")
                        ", $paramsGrupo);
                    }
                } else {
                    $db->CRUD("
                        UPDATE estado_cuenta.asigna_jefe
                        SET id_jefe = NULL,
                            id_vacante_jefe = :id_vacante_jefe
                        WHERE id_jefe = :id_persona
                          AND id_persona IN (" . implode(',', $phSubordinados) . ")
                    ", array_merge($paramsSubordinados, [
                        'id_vacante_jefe' => $vacanteDestinoId ?: null,
                    ]));
                }
            }

            $db->commit();

            $legacySync = LegacyUserSync::sincronizarBajaDesdeSpartan((int)$id_persona, (int)$usuario_baja);

            return self::resultado(true, 'Baja registrada correctamente con archivos.', [
                'id_persona' => (int)$id_persona,
                'legacy_sync' => $legacySync,
            ]);

        } catch (\Exception $e) {
            if (isset($db)) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al registrar la baja.', null, $e->getMessage());
        }
    }

    /** ID de documento para "Documento Reingreso" en carga_documento_persona */
    const ID_DOCUMENTO_REINGRESO = 16;

    /**
     * Registrar reingreso de un gestor (pasar de Baja a Activo).
     * Inserta en reingresos, guarda PDFs en carga_documento_persona (id_documento=16) y actualiza persona.estatus = 'Activo'.
     */
    public static function registrarReingresoGestor($data)
    {
        try {
            $db = new Database();

            $id_persona   = (int)($data['id_gestor'] ?? 0);
            $motivo       = (string)($data['motivo_reingreso'] ?? '');
            $descripcion  = (string)($data['descripcion_reingreso'] ?? '');
            $fecha_reingreso = (string)($data['fecha_reingreso'] ?? date('Y-m-d H:i:s'));
            $usuario_reingreso = (string)($data['usuario_reingreso'] ?? 'sistema');
            $archivos    = $data['archivos'] ?? [];

            if ($id_persona < 1) {
                return self::resultado(false, 'ID de persona inválido.');
            }

            // 1) Insertar en reingresos (consultas preparadas para evitar errores por caracteres especiales)
            $db->queryOne("
                INSERT INTO estado_cuenta.reingresos
                (id_persona, fecha_reingreso, motivo_reingreso, descripcion_reingreso, usuario_reingreso)
                VALUES
                (:id_persona, :fecha_reingreso, :motivo_reingreso, :descripcion_reingreso, :usuario_reingreso)
            ", [
                'id_persona' => $id_persona,
                'fecha_reingreso' => $fecha_reingreso,
                'motivo_reingreso' => $motivo,
                'descripcion_reingreso' => $descripcion,
                'usuario_reingreso' => $usuario_reingreso
            ]);

            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id_reingreso = isset($result['id']) ? (int)$result['id'] : null;
            if (!$id_reingreso) {
                return self::resultado(false, 'No se pudo obtener el ID del reingreso.');
            }

            // 2) Guardar cada archivo en carga_documento_persona (Documento Reingreso = 16)
            $id_documento = self::ID_DOCUMENTO_REINGRESO;
            foreach ($archivos as $archivo) {
                $db->queryOne("
                    INSERT INTO estado_cuenta.carga_documento_persona
                    (id_persona, id_documento, archivo, fecha_carga)
                    VALUES
                    (:id_persona, :id_documento, :archivo, NOW())
                ", [
                    'id_persona' => $id_persona,
                    'id_documento' => $id_documento,
                    'archivo' => (string)$archivo
                ]);
            }

            // 3) Pasar a la plantilla: estatus = 'Activo'
            $db->queryOne("
                UPDATE estado_cuenta.persona
                SET estatus = 'Activo'
                WHERE id = :id_persona
            ", ['id_persona' => $id_persona]);

            return self::resultado(true, 'Reingreso registrado correctamente. La persona ha sido reactivada en la plantilla.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar el reingreso.', null, $e->getMessage());
        }
    }

    /**
     * Obtener documentos de un reingreso (por id de registro reingreso).
     */
    public static function getDocumentosReingreso($registro_reingreso)
    {
        try {
            $db = new Database();
            $reingreso = $db->queryOne("
                SELECT id_persona FROM estado_cuenta.reingresos WHERE id = :id
            ", ['id' => $registro_reingreso]);
            if (!$reingreso || !isset($reingreso['id_persona'])) {
                return self::resultado(false, 'Reingreso no encontrado.', []);
            }
            $id_persona = $reingreso['id_persona'];
            $id_documento = self::ID_DOCUMENTO_REINGRESO;
            $documentos = $db->queryAll("
                SELECT cdp.id, cdp.archivo, DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM estado_cuenta.carga_documento_persona cdp
                WHERE cdp.id_persona = :id_persona AND cdp.id_documento = :id_documento
                ORDER BY cdp.fecha_carga DESC
            ", ['id_persona' => $id_persona, 'id_documento' => $id_documento]);
            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    /**
     * Elimina por completo una persona y sus datos relacionados (solo si no tiene dependencias críticas).
     * Orden: anular referencias en ticket, borrar tablas hijas, luego persona.
     */
    public static function eliminarPersonaCompleto($id_persona)
    {
        $id = (int) $id_persona;
        if ($id < 1) {
            return self::resultado(false, 'ID de persona inválido.');
        }
        try {
            $db = new Database();

            // Iniciar transacción para garantizar integridad
            $db->beginTransaction();

            try {
                // ========== TICKETS (actualizar en lugar de eliminar para no perder historial) ==========
                // 1) Tickets: dejar de referenciar a esta persona como creador
                $db->CRUD("UPDATE ticket SET id_persona_creador = NULL WHERE id_persona_creador = $id");

                // 2) ticket_historico: desvincular gestor y asignado
                try {
                    $db->CRUD("UPDATE estado_cuenta.ticket_historico SET gestor_id = NULL WHERE gestor_id = $id");
                    $db->CRUD("UPDATE estado_cuenta.ticket_historico SET usuario_asignado = NULL WHERE usuario_asignado = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // 3) Asignaciones de ticket
                $db->CRUD("DELETE FROM asignacion_ticket WHERE id_persona_asignada = $id");

                // ========== MÓDULOS Y PERMISOS ==========
                // 4) Módulos web
                $db->CRUD("DELETE FROM asigna_modulo_web WHERE usuario_id = $id");

                // ========== JERARQUÍA Y ORGANIGRAMA ==========
                // 5) asigna_jefe: eliminar como persona subordinada
                try {
                    $db->CRUD("DELETE FROM estado_cuenta.asigna_jefe WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // 6) asigna_jefe: eliminar como jefe de otros (reasignar subordinados a NULL o eliminar)
                try {
                    $db->CRUD("DELETE FROM estado_cuenta.asigna_jefe WHERE id_jefe = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // ========== ASIGNACIONES ==========
                // 7) Puestos
                $db->CRUD("DELETE FROM estado_cuenta.asigna_puesto WHERE id_persona = $id");

                // 8) Bajas y reingresos
                $db->CRUD("DELETE FROM estado_cuenta.baja_persona WHERE id_persona = $id");
                $db->CRUD("DELETE FROM estado_cuenta.reingresos WHERE id_persona = $id");

                // 9) Legión
                $db->CRUD("DELETE FROM estado_cuenta.asigna_legion WHERE id_persona = $id");

                // ========== DOCUMENTOS ==========
                // 10) Documentos cargados
                $db->CRUD("DELETE FROM estado_cuenta.carga_documento_persona WHERE id_persona = $id");

                // 11) documentos_persona
                try {
                    $db->CRUD("DELETE FROM estado_cuenta.documentos_persona WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // ========== PERFIL ==========
                // 12) Perfil (si existe la tabla)
                try {
                    $db->CRUD("DELETE FROM estado_cuenta.perfil WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // ========== SABUESO / CHAT ==========
                // 13) Chat / dictamen / evidencias
                try {
                    $db->CRUD("DELETE FROM chat WHERE id_persona = $id");
                    $db->CRUD("DELETE FROM dictamen WHERE id_persona = $id");
                    $db->CRUD("DELETE FROM ticket_evidencia WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existen */ }

                // ========== FINALMENTE: ELIMINAR PERSONA ==========
                // 14) Persona
                $db->CRUD("DELETE FROM estado_cuenta.persona WHERE id = $id");

                // Confirmar transacción
                $db->commit();

                return self::resultado(true, 'Usuario eliminado del sistema correctamente.');

            } catch (\Exception $innerEx) {
                // Revertir todo si algo falla
                $db->rollback();
                throw $innerEx;
            }

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar el usuario.', null, $e->getMessage());
        }
    }

    /**
     * Lista personas en estatus Baja: una fila por persona (solo la baja más reciente).
     */
    public static function getConsultaBajas($fecha_inicio = null, $fecha_fin = null)
    {
        $exB = UsuarioFantasmaReporteria::sqlExcluirPersona('p');
        $query = <<<SQL
        SELECT
            p.id,
            p.id AS numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            p.codigo_contpac,
            p.numero_empleado AS external_id,
            pf.foto AS foto_perfil,
            d.nombre AS departamento,
            pu.nombre AS nombre_puesto,
            bp.fecha_baja,
            bp.id AS registro_baja,
            bp.motivo,
            bp.descripcion,
            p.user_name,
            c_reingreso.id AS id_candidato_reingreso,
            c_reingreso.estatus AS estatus_candidato_reingreso,
            TRIM(CONCAT_WS(' ', c_reingreso.nombres, c_reingreso.segundo_nombre, c_reingreso.apellidop, c_reingreso.apellidom)) AS nombre_candidato_reingreso
        FROM estado_cuenta.persona p
        INNER JOIN estado_cuenta.baja_persona bp ON p.id = bp.id_persona
        INNER JOIN (
            SELECT id_persona, MAX(id) AS id_ultima_baja
            FROM estado_cuenta.baja_persona
            GROUP BY id_persona
        ) ult ON bp.id_persona = ult.id_persona AND bp.id = ult.id_ultima_baja
        LEFT JOIN estado_cuenta.asigna_puesto ap ON ap.id = (
            SELECT ap2.id
            FROM estado_cuenta.asigna_puesto ap2
            WHERE ap2.id_persona = p.id
            ORDER BY COALESCE(ap2.fecha_asignacion, '0000-00-00 00:00:00') DESC, ap2.id DESC
            LIMIT 1
        )
        LEFT JOIN estado_cuenta.puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN estado_cuenta.departamento d ON pu.departamento_id = d.id
        LEFT JOIN estado_cuenta.perfil pf ON pf.id_persona = p.id
        LEFT JOIN estado_cuenta.candidatos c_reingreso ON c_reingreso.id = (
            SELECT c2.id
            FROM estado_cuenta.candidatos c2
            WHERE c2.id_persona_reingreso = p.id
              AND COALESCE(c2.es_reingreso, 0) = 1
              AND COALESCE(c2.estatus, '') NOT IN ('Proceso cerrado', 'Eliminado')
            ORDER BY COALESCE(c2.fecha_actualizacion, c2.fecha_registro) DESC, c2.id DESC
            LIMIT 1
        )
        WHERE p.estatus = 'Baja'
        {$exB}
        SQL;

        // Agregar filtro de fecha si se proporciona
        if ($fecha_inicio && $fecha_fin) {
            $query .= " AND DATE(bp.fecha_baja) BETWEEN :fecha_inicio AND :fecha_fin";
        } elseif ($fecha_inicio) {
            $query .= " AND DATE(bp.fecha_baja) >= :fecha_inicio";
        } elseif ($fecha_fin) {
            $query .= " AND DATE(bp.fecha_baja) <= :fecha_fin";
        }

        $query .= " ORDER BY bp.fecha_baja DESC";

        try {
            $db = new Database();

            // Si hay filtros de fecha, usar parámetros preparados
            // NOTA: Las claves NO deben incluir el ':' porque Database::runQuery lo agrega automáticamente
            if ($fecha_inicio || $fecha_fin) {
                $params = [];
                if ($fecha_inicio) $params['fecha_inicio'] = $fecha_inicio;
                if ($fecha_fin) $params['fecha_fin'] = $fecha_fin;
                $r = $db->queryAll($query, $params);
            } else {
                $r = $db->queryAll($query);
            }

            return self::resultado(true, 'Bajas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /**
     * Lista personas en estatus Baja (avanzado): una fila por persona (solo la baja más reciente).
     */
    public static function getConsultaBajasAvanzado($filtros = [])
    {
        $exB = UsuarioFantasmaReporteria::sqlExcluirPersona('p');
        $query = <<<SQL
        SELECT
            p.id,
            p.id AS numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            p.codigo_contpac,
            p.numero_empleado AS external_id,
            pf.foto AS foto_perfil,
            d.nombre AS departamento,
            pu.nombre AS nombre_puesto,
            bp.fecha_baja,
            bp.id AS registro_baja,
            bp.motivo,
            bp.descripcion,
            p.user_name,
            c_reingreso.id AS id_candidato_reingreso,
            c_reingreso.estatus AS estatus_candidato_reingreso,
            TRIM(CONCAT_WS(' ', c_reingreso.nombres, c_reingreso.segundo_nombre, c_reingreso.apellidop, c_reingreso.apellidom)) AS nombre_candidato_reingreso
        FROM estado_cuenta.persona p
        INNER JOIN estado_cuenta.baja_persona bp ON p.id = bp.id_persona
        INNER JOIN (
            SELECT id_persona, MAX(id) AS id_ultima_baja
            FROM estado_cuenta.baja_persona
            GROUP BY id_persona
        ) ult ON bp.id_persona = ult.id_persona AND bp.id = ult.id_ultima_baja
        LEFT JOIN estado_cuenta.asigna_puesto ap ON ap.id = (
            SELECT ap2.id
            FROM estado_cuenta.asigna_puesto ap2
            WHERE ap2.id_persona = p.id
            ORDER BY COALESCE(ap2.fecha_asignacion, '0000-00-00 00:00:00') DESC, ap2.id DESC
            LIMIT 1
        )
        LEFT JOIN estado_cuenta.puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN estado_cuenta.departamento d ON pu.departamento_id = d.id
        LEFT JOIN estado_cuenta.perfil pf ON pf.id_persona = p.id
        LEFT JOIN estado_cuenta.candidatos c_reingreso ON c_reingreso.id = (
            SELECT c2.id
            FROM estado_cuenta.candidatos c2
            WHERE c2.id_persona_reingreso = p.id
              AND COALESCE(c2.es_reingreso, 0) = 1
              AND COALESCE(c2.estatus, '') NOT IN ('Proceso cerrado', 'Eliminado')
            ORDER BY COALESCE(c2.fecha_actualizacion, c2.fecha_registro) DESC, c2.id DESC
            LIMIT 1
        )
        WHERE p.estatus = 'Baja'
        {$exB}
        SQL;

        $params = [];

        // Fechas eliminadas como filtro

        // Departamento
        if (!empty($filtros['departamento'])) {
            $query .= " AND d.id = :departamento";
            $params['departamento'] = $filtros['departamento'];
        }

        // Puesto
        if (!empty($filtros['puesto'])) {
            $query .= " AND pu.id = :puesto";
            $params['puesto'] = $filtros['puesto'];
        }

        // Estatus (por si se requiere filtrar por otro estatus de baja)
        if (!empty($filtros['estatus'])) {
            $query .= " AND bp.motivo = :estatus";
            $params['estatus'] = $filtros['estatus'];
        }

        // Multipuesto (si se requiere filtrar por empleados con más de un puesto)
        if (isset($filtros['multipuesto']) && $filtros['multipuesto'] !== '' && $filtros['multipuesto'] !== null) {
            if ($filtros['multipuesto'] === 'multiples') {
                $query .= " AND (SELECT COUNT(*) FROM estado_cuenta.asigna_puesto ap2 WHERE ap2.id_persona = p.id) > 1";
            } elseif ($filtros['multipuesto'] === 'unico') {
                $query .= " AND (SELECT COUNT(*) FROM estado_cuenta.asigna_puesto ap2 WHERE ap2.id_persona = p.id) = 1";
            }
        }

        $query .= " ORDER BY bp.fecha_baja DESC";

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Bajas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar persona de forma segura (elimina todas las dependencias primero)
     * Solo para administradores - usar con precaución
     * @param int $id_persona
     * @param bool $confirmar Si es false, solo muestra las dependencias sin eliminar
     * @return array
     */
    public static function eliminarPersonaSeguro($id_persona, $confirmar = false)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.');
        }

        try {
            $db = new Database();

            // Verificar que la persona existe
            $persona = $db->queryOne("
                SELECT
                    id,
                    nombres AS nombre,
                    apellidop AS apellido_paterno,
                    apellidom AS apellido_materno
                FROM estado_cuenta.persona
                WHERE id = :id
            ", ['id' => $id_persona]);
            if (!$persona) {
                return self::resultado(false, 'Persona no encontrada.');
            }

            $nombreCompleto = trim($persona['nombre'] . ' ' . $persona['apellido_paterno'] . ' ' . ($persona['apellido_materno'] ?? ''));

            // Buscar todas las dependencias
            $dependencias = [];
            $columnasTicketHistorico = [];
            try {
                $colsTicket = $db->queryAll("
                    SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = 'estado_cuenta'
                      AND TABLE_NAME = 'ticket_historico'
                      AND COLUMN_NAME IN ('gestor_id', 'usuario_asignado')
                ");
                foreach ($colsTicket ?: [] as $colTicket) {
                    $columnasTicketHistorico[(string) ($colTicket['COLUMN_NAME'] ?? '')] = true;
                }
            } catch (\Throwable $e) {
                $columnasTicketHistorico = [];
            }
            $tablasOpcionales = [];
            try {
                $rowsTablas = $db->queryAll("
                    SELECT TABLE_NAME
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = 'estado_cuenta'
                      AND TABLE_NAME IN (
                          'documentos_persona',
                          'carga_documento_persona',
                          'persona_datos_rrhh',
                          'perfil',
                          'privilegios_departamento',
                          'asigna_modulo_web',
                          'reingresos',
                          'asigna_creditos_adjudicacion',
                          'personal_adjudicacion'
                      )
                ");
                foreach ($rowsTablas ?: [] as $rowTabla) {
                    $tablasOpcionales[(string) ($rowTabla['TABLE_NAME'] ?? '')] = true;
                }
            } catch (\Throwable $e) {
                $tablasOpcionales = [];
            }

            // 1. asigna_puesto
            $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.asigna_puesto WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_puesto'] = (int)$count['c'];

            // 2. asigna_jefe (como persona)
            $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.asigna_jefe WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_jefe_persona'] = (int)$count['c'];

            // 3. asigna_jefe (como jefe de otros)
            $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.asigna_jefe WHERE id_jefe = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_jefe_jefe'] = (int)$count['c'];

            // 4. asigna_legion
            $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.asigna_legion WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_legion'] = (int)$count['c'];

            // 5. baja_persona
            $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.baja_persona WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['baja_persona'] = (int)$count['c'];
            // 5b. reingresos
            if (!empty($tablasOpcionales['reingresos'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.reingresos WHERE id_persona = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['reingresos'] = (int)$count['c'];
            }

            // 6. ticket_historico (gestor_id)
            if (!empty($columnasTicketHistorico['gestor_id'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.ticket_historico WHERE gestor_id = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['ticket_historico_gestor'] = (int)$count['c'];
            }

            // 7. ticket_historico (usuario_asignado)
            if (!empty($columnasTicketHistorico['usuario_asignado'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.ticket_historico WHERE usuario_asignado = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['ticket_historico_asignado'] = (int)$count['c'];
            }

            // 8. documentos_persona
            if (!empty($tablasOpcionales['documentos_persona'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.documentos_persona WHERE id_persona = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['documentos_persona'] = (int)$count['c'];
            }

            // 9. carga_documento_persona
            if (!empty($tablasOpcionales['carga_documento_persona'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.carga_documento_persona WHERE id_persona = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['carga_documento_persona'] = (int)$count['c'];
            }

            // 10. persona_datos_rrhh
            if (!empty($tablasOpcionales['persona_datos_rrhh'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.persona_datos_rrhh WHERE id_persona = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['persona_datos_rrhh'] = (int)$count['c'];
            }

            // 11. perfil
            if (!empty($tablasOpcionales['perfil'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.perfil WHERE id_persona = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['perfil'] = (int)$count['c'];
            }

            // 12. privilegios_departamento
            if (!empty($tablasOpcionales['privilegios_departamento'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.privilegios_departamento WHERE idPersona = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['privilegios_departamento'] = (int)$count['c'];
            }

            // 13. asigna_modulo_web
            if (!empty($tablasOpcionales['asigna_modulo_web'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.asigna_modulo_web WHERE usuario_id = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['asigna_modulo_web'] = (int)$count['c'];
            }

            // 14. asigna_creditos_adjudicacion (historial de alta)
            if (!empty($tablasOpcionales['asigna_creditos_adjudicacion'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.asigna_creditos_adjudicacion WHERE alta = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['asigna_creditos_adjudicacion_alta'] = (int)$count['c'];
            }

            // 15. personal_adjudicacion
            if (!empty($tablasOpcionales['personal_adjudicacion'])) {
                $count = $db->queryOne("SELECT COUNT(*) as c FROM estado_cuenta.personal_adjudicacion WHERE id_persona = :id", ['id' => $id_persona]);
                if ($count['c'] > 0) $dependencias['personal_adjudicacion'] = (int)$count['c'];
                if (!empty($tablasOpcionales['asigna_creditos_adjudicacion'])) {
                    $count = $db->queryOne("
                        SELECT COUNT(*) as c
                        FROM estado_cuenta.asigna_creditos_adjudicacion aca
                        INNER JOIN estado_cuenta.personal_adjudicacion pa ON pa.id = aca.id_personal_adj
                        WHERE pa.id_persona = :id
                    ", ['id' => $id_persona]);
                    if ($count['c'] > 0) $dependencias['asigna_creditos_adjudicacion_personal_adj'] = (int)$count['c'];
                }
            }

            // Si solo es consulta (no confirmar), devolver dependencias
            if (!$confirmar) {
                return self::resultado(true, 'Dependencias encontradas para: ' . $nombreCompleto, [
                    'id' => $id_persona,
                    'nombre' => $nombreCompleto,
                    'dependencias' => $dependencias,
                    'total_dependencias' => array_sum($dependencias)
                ]);
            }

            // ELIMINAR - ejecutar en transacción
            $db->beginTransaction();

            try {
                // Eliminar en orden de dependencias
                if (!empty($tablasOpcionales['documentos_persona'])) $db->CRUD("DELETE FROM estado_cuenta.documentos_persona WHERE id_persona = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['carga_documento_persona'])) $db->CRUD("DELETE FROM estado_cuenta.carga_documento_persona WHERE id_persona = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['persona_datos_rrhh'])) $db->CRUD("DELETE FROM estado_cuenta.persona_datos_rrhh WHERE id_persona = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['perfil'])) $db->CRUD("DELETE FROM estado_cuenta.perfil WHERE id_persona = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['privilegios_departamento'])) $db->CRUD("DELETE FROM estado_cuenta.privilegios_departamento WHERE idPersona = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['asigna_modulo_web'])) $db->CRUD("DELETE FROM estado_cuenta.asigna_modulo_web WHERE usuario_id = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['personal_adjudicacion']) && !empty($tablasOpcionales['asigna_creditos_adjudicacion'])) {
                    $db->CRUD("
                        DELETE aca
                        FROM estado_cuenta.asigna_creditos_adjudicacion aca
                        INNER JOIN estado_cuenta.personal_adjudicacion pa ON pa.id = aca.id_personal_adj
                        WHERE pa.id_persona = :id
                    ", ['id' => $id_persona]);
                }
                if (!empty($tablasOpcionales['personal_adjudicacion'])) $db->CRUD("DELETE FROM estado_cuenta.personal_adjudicacion WHERE id_persona = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['asigna_creditos_adjudicacion'])) $db->CRUD("UPDATE estado_cuenta.asigna_creditos_adjudicacion SET alta = NULL WHERE alta = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM estado_cuenta.asigna_legion WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM estado_cuenta.asigna_jefe WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM estado_cuenta.asigna_jefe WHERE id_jefe = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM estado_cuenta.asigna_puesto WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM estado_cuenta.baja_persona WHERE id_persona = :id", ['id' => $id_persona]);
                if (!empty($tablasOpcionales['reingresos'])) $db->CRUD("DELETE FROM estado_cuenta.reingresos WHERE id_persona = :id", ['id' => $id_persona]);

                // Para tickets, en lugar de eliminar, ponemos NULL (para no perder historial)
                if (!empty($columnasTicketHistorico['gestor_id'])) {
                    $db->CRUD("UPDATE estado_cuenta.ticket_historico SET gestor_id = NULL WHERE gestor_id = :id", ['id' => $id_persona]);
                }
                if (!empty($columnasTicketHistorico['usuario_asignado'])) {
                    $db->CRUD("UPDATE estado_cuenta.ticket_historico SET usuario_asignado = NULL WHERE usuario_asignado = :id", ['id' => $id_persona]);
                }

                // Finalmente eliminar la persona
                $db->CRUD("DELETE FROM estado_cuenta.persona WHERE id = :id", ['id' => $id_persona]);

                $db->commit();

                return self::resultado(true, 'Persona eliminada correctamente: ' . $nombreCompleto, [
                    'id' => $id_persona,
                    'nombre' => $nombreCompleto,
                    'dependencias_eliminadas' => $dependencias
                ]);

            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar persona: ' . $e->getMessage());
        }
    }

    /**
     * Calle en persona: una sola columna domicilio_calle_texto (texto libre o nombre desde catálogo si el front envía id_div_nivel4).
     */
    private static function domicilioCalleTextoParaGuardar(Database $db, array $data): string
    {
        $txt = mb_substr(trim((string) ($data['domicilio_calle_texto'] ?? '')), 0, 200);
        if ($txt !== '') {
            return $txt;
        }
        $idFk = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel4'] ?? null);
        if ($idFk === 'NULL') {
            return '';
        }
        $nr = $db->queryOne(
            'SELECT nombre FROM estado_cuenta.divisiones_administrativas WHERE id = :id AND activo = 1 LIMIT 1',
            ['id' => (int) $idFk]
        );

        return mb_substr(trim((string) ($nr['nombre'] ?? '')), 0, 200);
    }

    /**
     * Valor SQL para FK id → divisiones_administrativas.id.
     * El front puede enviar null, "" o omitir la clave; no debe guardarse 0.
     */
    private static function sqlIdDivisionAdministrativaFk($value): string
    {
        if ($value === null || $value === false) {
            return 'NULL';
        }
        if (is_string($value) && trim($value) === '') {
            return 'NULL';
        }
        if ($value === '') {
            return 'NULL';
        }
        $i = (int) $value;

        return $i > 0 ? (string) $i : 'NULL';
    }

    /**
 * Obtener estados/divisiones nivel 1 por país
 */
public static function getEstadosPorPais($id_pais)
{
    $id_pais = (int) $id_pais;
    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_pais = $id_pais
      AND da.nivel   = 1
      AND da.activo  = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        if (!empty($r) || $id_pais !== 1) {
            return self::resultado(true, 'Estados encontrados.', $r);
        }
    } catch (\Exception $e) {
        $localError = $e->getMessage();
    }

    if ($id_pais === 1) {
        $api = self::divisionesAdministrativasApiGet('estados');
        if (!empty($api['success']) && !empty($api['datos'])) {
            $datos = array_map([self::class, 'normalizarDivisionAdministrativaApi'], $api['datos']);
            usort($datos, static function ($a, $b) {
                return strcasecmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? ''));
            });
            return self::resultado(true, 'Estados encontrados.', $datos);
        }
    }

    return self::resultado(false, 'Error al obtener estados.', null, $localError ?? 'Catalogo local y remoto sin datos.');
}

/**
 * Obtener municipios/alcaldías nivel 2 por estado/división padre
 */
public static function getMunicipiosPorEstado($id_estado)
{
    $id_estado = (int) $id_estado;
    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_padre = $id_estado
      AND da.nivel    = 2
      AND da.activo   = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        if (!empty($r)) {
            return self::resultado(true, 'Municipios encontrados.', $r);
        }
    } catch (\Exception $e) {
        $localError = $e->getMessage();
    }

    if ($id_estado > 0 && $id_estado <= 32) {
        $api = self::divisionesAdministrativasApiGet('municipios', ['id_padre' => $id_estado]);
        if (!empty($api['success']) && !empty($api['datos'])) {
            $datos = array_map([self::class, 'normalizarDivisionAdministrativaApi'], $api['datos']);
            usort($datos, static function ($a, $b) {
                return strcasecmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? ''));
            });
            return self::resultado(true, 'Municipios encontrados.', $datos);
        }
    }

    return self::resultado(true, 'Municipios encontrados.', []);
}

public static function getEstadosMunicipiosMexico()
{
    $query = <<<SQL
    SELECT
        da.id,
        da.id_padre,
        da.nivel,
        da.nombre,
        da.codigo_interno,
        dat.nombre AS tipo_label,
        dat.codigo AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_pais = 1
      AND da.nivel IN (1, 2)
      AND da.activo = 1
    ORDER BY da.nivel ASC, da.id_padre ASC, da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $rows = $db->queryAll($query);
        $estados = [];
        $municipiosPorEstado = [];

        foreach ($rows as $row) {
            $nivel = (int)($row['nivel'] ?? 0);
            if ($nivel === 1) {
                $estados[] = $row;
                continue;
            }

            if ($nivel === 2) {
                $idPadre = (string)($row['id_padre'] ?? '');
                if ($idPadre === '') {
                    continue;
                }
                if (!isset($municipiosPorEstado[$idPadre])) {
                    $municipiosPorEstado[$idPadre] = [];
                }
                $municipiosPorEstado[$idPadre][] = $row;
            }
        }

        return self::resultado(true, 'Catalogo de Mexico encontrado.', [
            'estados' => $estados,
            'municipios_por_estado' => $municipiosPorEstado,
        ]);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener catalogo de Mexico.', null, $e->getMessage());
    }
}

/**
 * Colonias (nivel 3) bajo un municipio/alcaldía (nivel 2).
 * codigo_postal devuelto desde codigo_interno del catálogo cuando aplica.
 */
public static function getColoniasPorMunicipio($id_municipio)
{
    $id_municipio = (int) $id_municipio;
    if ($id_municipio <= 0) {
        return self::resultado(false, 'ID de municipio inválido.', []);
    }

    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        NULLIF(TRIM(da.codigo_interno), '') AS codigo_postal,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_padre = $id_municipio
      AND da.nivel    = 3
      AND da.activo   = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        return self::resultado(true, 'Colonias encontradas.', $r);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener colonias.', null, $e->getMessage());
    }
}

/**
 * Calles (nivel 4) bajo una colonia (nivel 3).
 */
public static function getCallesPorColonia($id_colonia)
{
    $id_colonia = (int) $id_colonia;
    if ($id_colonia <= 0) {
        return self::resultado(false, 'ID de colonia inválido.', []);
    }

    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_padre = $id_colonia
      AND da.nivel    = 4
      AND da.activo   = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        return self::resultado(true, 'Calles encontradas.', $r);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener calles.', null, $e->getMessage());
    }
}

public static function asegurarMapaOrganizacionalPuesto(?Database $db = null): void
{
    $db = $db ?: new Database();

    $db->CRUD("
        CREATE TABLE IF NOT EXISTS `mapa_organizacional_puesto` (
          `id` int NOT NULL AUTO_INCREMENT,
          `id_pais` int NOT NULL COMMENT 'FK a paises.id',
          `id_puesto` int NOT NULL COMMENT 'FK a puesto.id',
          `id_puesto_padre` int DEFAULT NULL COMMENT 'Puesto superior jerarquico dentro del mapa',
          `posicion_x` int DEFAULT NULL COMMENT 'Coordenada X en el canvas',
          `posicion_y` int DEFAULT NULL COMMENT 'Coordenada Y en el canvas',
          `estatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Activo, 0 = Inactivo',
          `id_pais_activo_key` int DEFAULT NULL,
          `id_puesto_activo_key` int DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_mapa_pais_puesto_activo` (`id_pais_activo_key`, `id_puesto_activo_key`),
          KEY `idx_mapa_pais` (`id_pais`),
          KEY `idx_mapa_puesto` (`id_puesto`),
          KEY `idx_mapa_puesto_padre` (`id_puesto_padre`),
          KEY `idx_mapa_estatus` (`estatus`),
          CONSTRAINT `fk_mapa_pais`
            FOREIGN KEY (`id_pais`)
            REFERENCES `paises` (`id`)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
          CONSTRAINT `fk_mapa_puesto`
            FOREIGN KEY (`id_puesto`)
            REFERENCES `puesto` (`id`)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
          CONSTRAINT `fk_mapa_puesto_padre`
            FOREIGN KEY (`id_puesto_padre`)
            REFERENCES `puesto` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Mapa jerarquico manual de puestos por pais'
    ");

    $db->CRUD("DROP TRIGGER IF EXISTS `bi_mapa_org_puesto_activo_key`");
    $db->CRUD("DROP TRIGGER IF EXISTS `bu_mapa_org_puesto_activo_key`");
    $db->CRUD("
        CREATE TRIGGER `bi_mapa_org_puesto_activo_key`
        BEFORE INSERT ON `mapa_organizacional_puesto`
        FOR EACH ROW
        BEGIN
            SET NEW.id_pais_activo_key = IF(NEW.estatus = 1, NEW.id_pais, NULL);
            SET NEW.id_puesto_activo_key = IF(NEW.estatus = 1, NEW.id_puesto, NULL);
        END
    ");
    $db->CRUD("
        CREATE TRIGGER `bu_mapa_org_puesto_activo_key`
        BEFORE UPDATE ON `mapa_organizacional_puesto`
        FOR EACH ROW
        BEGIN
            SET NEW.id_pais_activo_key = IF(NEW.estatus = 1, NEW.id_pais, NULL);
            SET NEW.id_puesto_activo_key = IF(NEW.estatus = 1, NEW.id_puesto, NULL);
        END
    ");
}

public static function getConstructorEstructuraOrganizacional($idPais = 0): array
{
    try {
        $db = new Database();
        if (class_exists('\\Models\\EstadoCuenta') && method_exists('\\Models\\EstadoCuenta', 'asegurarNivelOrganizacionalPuesto')) {
            \Models\EstadoCuenta::asegurarNivelOrganizacionalPuesto($db);
        }
        self::asegurarMapaOrganizacionalPuesto($db);

        $idPais = (int) $idPais;
        $paises = $db->queryAll("
            SELECT id, nombre, codigo_iso
            FROM paises
            WHERE activo = 1
            ORDER BY nombre ASC
        ");

        if ($idPais <= 0) {
            $paisMexico = null;
            foreach ($paises as $pais) {
                $nombrePais = mb_strtolower((string) ($pais['nombre'] ?? ''), 'UTF-8');
                if ($nombrePais === 'méxico' || $nombrePais === 'mexico') {
                    $paisMexico = $pais;
                    break;
                }
            }
            $idPais = (int) (($paisMexico['id'] ?? null) ?: ($paises[0]['id'] ?? 0));
        }

        $niveles = $db->queryAll("
            SELECT id, clave, nombre, orden
            FROM nivel_organizacional
            WHERE activo = 1
            ORDER BY orden ASC, nombre ASC
        ");

        $areas = [];
        $departamentos = [];
        $puestos = [];
        $mapa = [];

        if ($idPais > 0) {
            $areas = $db->queryAll("
                SELECT id, nombre
                FROM departamento_organizacional
                WHERE activo = 1
                  AND id_pais = :id_pais
                ORDER BY nombre ASC
            ", ['id_pais' => $idPais]);

            $departamentos = $db->queryAll("
                SELECT
                    d.id,
                    d.nombre,
                    d.id_departamento_organizacional
                FROM departamento d
                LEFT JOIN departamento_organizacional dor
                    ON dor.id = d.id_departamento_organizacional
                WHERE d.activo = 1
                  AND COALESCE(dor.id_pais, d.id_pais) = :id_pais
                ORDER BY d.nombre ASC
            ", ['id_pais' => $idPais]);

            $puestos = $db->queryAll("
                SELECT
                    p.id AS id_puesto,
                    p.clave,
                    p.nombre AS puesto,
                    d.id AS id_departamento,
                    d.nombre AS departamento,
                    dor.id AS id_area_organizacional,
                    dor.nombre AS area_organizacional,
                    no.id AS id_nivel_organizacional,
                    no.nombre AS nivel_organizacional,
                    CASE WHEN mop.id IS NULL THEN 0 ELSE 1 END AS en_mapa
                FROM puesto p
                INNER JOIN departamento d
                    ON d.id = p.departamento_id
                LEFT JOIN departamento_organizacional dor
                    ON dor.id = d.id_departamento_organizacional
                LEFT JOIN asigna_nivel_organizacional_puesto anop
                    ON anop.id_puesto = p.id
                   AND anop.estatus = 1
                LEFT JOIN nivel_organizacional no
                    ON no.id = anop.id_nivel_organizacional
                LEFT JOIN mapa_organizacional_puesto mop
                    ON mop.id_pais = :id_pais
                   AND mop.id_puesto = p.id
                   AND mop.estatus = 1
                WHERE p.activo = 1
                  AND COALESCE(dor.id_pais, d.id_pais) = :id_pais
                ORDER BY COALESCE(no.orden, 999), dor.nombre ASC, d.nombre ASC, p.nombre ASC
            ", ['id_pais' => $idPais]);

            $mapa = $db->queryAll("
                SELECT
                    mop.id,
                    mop.id_pais,
                    mop.id_puesto,
                    p.clave,
                    p.nombre AS puesto,
                    d.id AS id_departamento,
                    d.nombre AS departamento,
                    dor.id AS id_area_organizacional,
                    dor.nombre AS area_organizacional,
                    no.id AS id_nivel_organizacional,
                    no.nombre AS nivel_organizacional,
                    mop.id_puesto_padre,
                    COALESCE(mop.posicion_x, 120) AS posicion_x,
                    COALESCE(mop.posicion_y, 120) AS posicion_y
                FROM mapa_organizacional_puesto mop
                INNER JOIN puesto p
                    ON p.id = mop.id_puesto
                LEFT JOIN departamento d
                    ON d.id = p.departamento_id
                LEFT JOIN departamento_organizacional dor
                    ON dor.id = d.id_departamento_organizacional
                LEFT JOIN asigna_nivel_organizacional_puesto anop
                    ON anop.id_puesto = p.id
                   AND anop.estatus = 1
                LEFT JOIN nivel_organizacional no
                    ON no.id = anop.id_nivel_organizacional
                WHERE mop.id_pais = :id_pais
                  AND mop.estatus = 1
                ORDER BY COALESCE(no.orden, 999), mop.posicion_y ASC, mop.posicion_x ASC
            ", ['id_pais' => $idPais]);
        }

        return self::resultado(true, 'Estructura organizacional cargada.', [
            'id_pais' => $idPais,
            'paises' => $paises,
            'niveles' => $niveles,
            'areas' => $areas,
            'departamentos' => $departamentos,
            'puestos' => $puestos,
            'mapa' => $mapa,
        ]);
    } catch (\Throwable $e) {
        return self::resultado(false, 'Error al cargar estructura organizacional.', null, $e->getMessage());
    }
}

public static function guardarConstructorEstructuraOrganizacional($idPais, array $nodos): array
{
    $idPais = (int) $idPais;
    if ($idPais <= 0) {
        return self::resultado(false, 'Selecciona un pais para guardar el mapa.');
    }

    $limpios = [];
    $vistos = [];
    foreach ($nodos as $nodo) {
        $idPuesto = (int) ($nodo['id_puesto'] ?? 0);
        if ($idPuesto <= 0 || isset($vistos[$idPuesto])) {
            continue;
        }
        $vistos[$idPuesto] = true;
        $padre = (int) ($nodo['id_puesto_padre'] ?? 0);
        $limpios[] = [
            'id_puesto' => $idPuesto,
            'id_puesto_padre' => $padre > 0 ? $padre : null,
            'posicion_x' => max(0, (int) ($nodo['posicion_x'] ?? 120)),
            'posicion_y' => max(0, (int) ($nodo['posicion_y'] ?? 120)),
        ];
    }

    foreach ($limpios as &$nodo) {
        if ($nodo['id_puesto_padre'] !== null && !isset($vistos[$nodo['id_puesto_padre']])) {
            $nodo['id_puesto_padre'] = null;
        }
        if ($nodo['id_puesto_padre'] === $nodo['id_puesto']) {
            $nodo['id_puesto_padre'] = null;
        }
    }
    unset($nodo);

    $padres = [];
    foreach ($limpios as $nodo) {
        $padres[$nodo['id_puesto']] = $nodo['id_puesto_padre'];
    }
    foreach ($padres as $idPuesto => $idPadre) {
        $visitados = [];
        while ($idPadre !== null) {
            if (isset($visitados[$idPadre]) || (int) $idPadre === (int) $idPuesto) {
                return self::resultado(false, 'La jerarquia no puede guardarse porque contiene un ciclo.');
            }
            $visitados[$idPadre] = true;
            $idPadre = $padres[$idPadre] ?? null;
        }
    }

    try {
        $db = new Database();
        self::asegurarMapaOrganizacionalPuesto($db);
        $db->beginTransaction();

        $db->CRUD("
            UPDATE mapa_organizacional_puesto
            SET estatus = 0
            WHERE id_pais = :id_pais
              AND estatus = 1
        ", ['id_pais' => $idPais]);

        foreach ($limpios as $nodo) {
            $db->CRUD("
                INSERT INTO mapa_organizacional_puesto
                    (id_pais, id_puesto, id_puesto_padre, posicion_x, posicion_y, estatus)
                VALUES
                    (:id_pais, :id_puesto, :id_puesto_padre, :posicion_x, :posicion_y, 1)
            ", [
                'id_pais' => $idPais,
                'id_puesto' => $nodo['id_puesto'],
                'id_puesto_padre' => $nodo['id_puesto_padre'],
                'posicion_x' => $nodo['posicion_x'],
                'posicion_y' => $nodo['posicion_y'],
            ]);
        }

        $db->commit();
        return self::resultado(true, 'Mapa organizacional guardado.', [
            'total_nodos' => count($limpios),
        ]);
    } catch (\Throwable $e) {
        if (isset($db)) {
            $db->rollback();
        }
        return self::resultado(false, 'Error al guardar el mapa organizacional.', null, $e->getMessage());
    }
}

}
