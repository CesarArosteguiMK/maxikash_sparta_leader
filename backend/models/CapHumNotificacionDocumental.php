<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Campañas obligatorias de documentos para Capital Humano.
 *
 * La primera campaña disponible es la constancia de semanas cotizadas IMSS
 * (segundos patrones), con dos entregas independientes por año.
 */
class CapHumNotificacionDocumental extends Model
{
    public const TIPO_SEMANAS_COTIZADAS = 'semanas_cotizadas';
    public const DOCUMENTO_SEMANAS_COTIZADAS = 33;
    public const URL_IMSS_SEMANAS_COTIZADAS = 'https://serviciosdigitales.imss.gob.mx/semanascotizadas-web/usuarios/IngresoAsegurado';

    private static bool $tablasAseguradas = false;
    private static bool $plantillaActivaExpedientesTablaAsegurada = false;

    public static function asegurarTablas(): void
    {
        if (self::$tablasAseguradas) {
            return;
        }

        $db = new Database();
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_notificacion_documental_campania (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                tipo VARCHAR(60) NOT NULL,
                anio SMALLINT UNSIGNED NOT NULL,
                semestre TINYINT UNSIGNED NOT NULL,
                titulo VARCHAR(180) NOT NULL,
                mensaje TEXT NOT NULL,
                url_descarga VARCHAR(500) NOT NULL,
                codigo_pais VARCHAR(8) NOT NULL DEFAULT 'mx',
                obligatoria TINYINT(1) NOT NULL DEFAULT 1,
                activa TINYINT(1) NOT NULL DEFAULT 1,
                fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_limite DATETIME NULL,
                creado_por INT NULL,
                actualizado_por INT NULL,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_rrhh_notif_tipo_periodo_pais (tipo, anio, semestre, codigo_pais),
                KEY idx_rrhh_notif_activa (activa, codigo_pais, fecha_inicio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_notificacion_documental_entrega (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_campania BIGINT UNSIGNED NOT NULL,
                id_persona INT NOT NULL,
                id_documento_carga INT NOT NULL,
                nombre_logico VARCHAR(220) NOT NULL,
                nombre_original VARCHAR(255) NOT NULL,
                archivo VARCHAR(255) NOT NULL,
                tamanio_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
                sha256 CHAR(64) NOT NULL,
                patrones_vigentes TINYINT UNSIGNED NULL,
                patrones_historial SMALLINT UNSIGNED NULL,
                patrones_fuente VARCHAR(50) NULL,
                patrones_analisis_json LONGTEXT NULL,
                patrones_analizado_en DATETIME NULL,
                cargado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_rrhh_notif_entrega_persona (id_campania, id_persona),
                UNIQUE KEY uq_rrhh_notif_entrega_documento (id_documento_carga),
                KEY idx_rrhh_notif_entrega_persona (id_persona, cargado_en),
                KEY idx_rrhh_notif_entrega_campania (id_campania, cargado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $columnasAnalisis = [
            'patrones_vigentes' => 'TINYINT UNSIGNED NULL AFTER sha256',
            'patrones_historial' => 'SMALLINT UNSIGNED NULL AFTER patrones_vigentes',
            'patrones_fuente' => 'VARCHAR(50) NULL AFTER patrones_historial',
            'patrones_analisis_json' => 'LONGTEXT NULL AFTER patrones_fuente',
            'patrones_analizado_en' => 'DATETIME NULL AFTER patrones_analisis_json',
        ];
        foreach ($columnasAnalisis as $columna => $definicion) {
            $existe = $db->queryOne("
                SELECT 1
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = 'estado_cuenta'
                  AND TABLE_NAME = 'rrhh_notificacion_documental_entrega'
                  AND COLUMN_NAME = :columna
                LIMIT 1
            ", ['columna' => $columna]);
            if (!$existe) {
                $db->CRUD("
                    ALTER TABLE estado_cuenta.rrhh_notificacion_documental_entrega
                    ADD COLUMN {$columna} {$definicion}
                ");
            }
        }
        $columnaAlcance = $db->queryOne("
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = 'estado_cuenta'
              AND TABLE_NAME = 'rrhh_notificacion_documental_campania'
              AND COLUMN_NAME = 'alcance'
            LIMIT 1
        ");
        if (!$columnaAlcance) {
            $db->CRUD("
                ALTER TABLE estado_cuenta.rrhh_notificacion_documental_campania
                ADD COLUMN alcance VARCHAR(20) NOT NULL DEFAULT 'todos' AFTER codigo_pais
            ");
        }
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_notificacion_documental_destinatario (
                id_campania BIGINT UNSIGNED NOT NULL,
                id_persona INT NOT NULL,
                agregado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id_campania, id_persona),
                KEY idx_rrhh_notif_dest_persona (id_persona, id_campania)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$tablasAseguradas = true;
    }

    /**
     * Usa la misma fuente de plantilla activa que Expedientes RR.HH. para que
     * los totales de las campañas no incluyan usuarios fuera de plantilla.
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
                KEY idx_rrhh_plantilla_activa_persona (id_persona, activo),
                KEY idx_rrhh_plantilla_activa_empresa (id_empresa, activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::$plantillaActivaExpedientesTablaAsegurada = true;
    }

    public static function catalogoTipos(): array
    {
        return [
            self::tipo(self::TIPO_SEMANAS_COTIZADAS, 'Semanas cotizadas del IMSS (segundos patrones)', 33,
                'Descarga la constancia detallada de semanas cotizadas correspondiente al periodo solicitado.',
                self::URL_IMSS_SEMANAS_COTIZADAS, 'Abrir portal del IMSS',
                ['Ten a la mano tu CURP, NSS y correo electrónico personal.', 'Selecciona el reporte detallado y descarga el PDF.']),
            self::tipo('solicitud_interna', 'Solicitud interna', 17,
                'Completa la solicitud interna de MaxiKash con información vigente, revísala y súbela en formato PDF.',
                '/CapHum/llenarSolicitudInternaNotificacion', 'Llenar solicitud interna en línea',
                ['Completa todos los apartados del formulario en línea.', 'Genera el PDF, fírmalo y regresa a esta ventana para cargarlo.']),
            self::tipo('cv_solicitud_trabajo', 'CV o solicitud de trabajo', 18,
                'Prepara un CV o una solicitud de trabajo actualizada que incluya tus datos de contacto, experiencia laboral y formación.',
                '', '', ['Verifica que los datos y las fechas sean correctos.', 'Guarda el documento completo en formato PDF.']),
            self::tipo('acta_nacimiento', 'Acta de nacimiento', 12,
                'Obtén una copia certificada legible de tu acta de nacimiento y carga el archivo completo en formato PDF.',
                'https://www.gob.mx/actas', 'Obtener acta de nacimiento',
                ['Verifica que el nombre, la fecha de nacimiento y los datos de filiación sean correctos.', 'Carga todas las páginas del documento.']),
            self::tipo('curp', 'CURP', 8,
                'Consulta y descarga la constancia vigente de tu Clave Única de Registro de Población (CURP).',
                'https://www.gob.mx/curp/', 'Consultar y descargar CURP',
                ['Comprueba que la CURP esté certificada y que tus datos coincidan con tu acta de nacimiento.', 'Descarga la constancia en PDF.']),
            self::tipo('comprobante_domicilio', 'Comprobante de domicilio', 11,
                'Carga un comprobante de domicilio reciente, completo y legible, donde se identifique la dirección.',
                '', '', ['Puede ser un recibo de agua, luz, gas, teléfono o servicio equivalente.', 'Conserva visibles el domicilio, la fecha de emisión y el nombre del emisor.']),
            self::tipo('constancia_situacion_fiscal', 'Constancia de situación fiscal', 22,
                'Genera tu Constancia de situación fiscal con los datos actuales registrados ante el SAT y carga el PDF completo.',
                'https://sat.gob.mx/portal/public/tramites/constancia-de-situacion-fiscal', 'Obtener constancia en el SAT',
                ['Ingresa con contraseña, e.firma o el medio habilitado por el SAT.', 'Revisa que el RFC, nombre, régimen y domicilio fiscal sean correctos.']),
            self::tipo('identificacion_oficial', 'Identificación oficial', 9,
                'Digitaliza una identificación oficial vigente y legible. Si tiene información en ambos lados, intégralos en un solo PDF.',
                'https://ine.mx/credencial/', 'Consultar trámites de la credencial',
                ['No recortes bordes, fotografía, firma, códigos ni datos de vigencia.', 'La identificación debe estar vigente.']),
            self::tipo('numero_seguridad_social', 'Número de seguridad social (NSS)', 23,
                'Obtén la constancia de asignación o localización de tu Número de Seguridad Social y carga el PDF emitido por el IMSS.',
                'https://serviciosdigitales.imss.gob.mx/gestionAsegurados-web-externo/home/asegurado', 'Obtener NSS en el IMSS',
                ['Ten a la mano tu CURP y correo electrónico personal.', 'Verifica que el nombre y el NSS estén completos y sean legibles.']),
            self::tipo('retencion_fonacot_infonavit', 'Hoja de retención FONACOT o INFONAVIT', 24,
                'Carga la hoja o notificación de retención vigente. Si no tienes un crédito activo, llena y firma la carta de no adeudo.',
                '/CapHum/descargarPlantillaNotificacionDocumental/carta_no_adeudo', 'Descargar carta de no adeudo',
                ['Para INFONAVIT, consulta la notificación de descuentos en Mi Cuenta Infonavit.', 'Para FONACOT, consulta el estado de cuenta o los movimientos en sus servicios en línea.'],
                [
                    ['label' => 'Servicios en línea FONACOT', 'url' => 'https://www.fonacot.gob.mx/serviciosenlinea/Paginas/default.aspx'],
                    ['label' => 'Mi Cuenta Infonavit', 'url' => 'https://micuenta.infonavit.org.mx/'],
                ]),
            self::tipo('estado_cuenta', 'Estado de cuenta', 25,
                'Descarga desde tu banca un estado de cuenta reciente y completo de una cuenta a tu nombre.',
                '', '', ['Deben verse tu nombre, la institución bancaria y la CLABE o los datos necesarios para identificar la cuenta.', 'Puedes ocultar movimientos o saldos innecesarios, sin cubrir los datos de titularidad.']),
        ];
    }

    private static function tipo(
        string $clave,
        string $nombre,
        int $documentoId,
        string $mensaje,
        string $urlDescarga,
        string $textoEnlace,
        array $instrucciones,
        array $enlacesAdicionales = []
    ): array {
        $enlaces = $urlDescarga !== '' ? [['label' => $textoEnlace, 'url' => $urlDescarga]] : [];
        return [
            'clave' => $clave,
            'nombre' => $nombre,
            'documento_id' => $documentoId,
            'mensaje_predeterminado' => $mensaje,
            'url_descarga' => $urlDescarga,
            'texto_enlace' => $textoEnlace,
            'instrucciones' => array_values($instrucciones),
            'enlaces' => array_merge($enlaces, $enlacesAdicionales),
        ];
    }

    private static function catalogoPorClave(string $clave): ?array
    {
        foreach (self::catalogoTipos() as $tipo) {
            if ($tipo['clave'] === $clave) {
                return $tipo;
            }
        }
        return null;
    }

    public static function nombrePeriodo(int $anio, int $semestre, string $tipo = self::TIPO_SEMANAS_COTIZADAS): string
    {
        if ($tipo === self::TIPO_SEMANAS_COTIZADAS) {
            return sprintf('Semanas cotizadas %d - %d semestre', $anio, $semestre);
        }
        $catalogo = self::catalogoPorClave($tipo);
        return sprintf('%s %d - %d semestre', (string)($catalogo['nombre'] ?? 'Documento'), $anio, $semestre);
    }

    private static function validarPeriodo(int $anio, int $semestre): void
    {
        if ($anio < 2020 || $anio > 2100) {
            throw new \InvalidArgumentException('El año debe estar entre 2020 y 2100.');
        }
        if (!in_array($semestre, [1, 2], true)) {
            throw new \InvalidArgumentException('El semestre debe ser 1 o 2.');
        }
    }

    private static function mensajePredeterminado(string $tipo, int $anio, int $semestre): string
    {
        $catalogo = self::catalogoPorClave($tipo);
        $detalle = trim((string)($catalogo['mensaje_predeterminado'] ?? 'Carga el documento solicitado en formato PDF.'));
        if ($catalogo) {
            return 'Capital Humano solicita el documento «' . (string)$catalogo['nombre'] . '» para '
                . $anio . ', ' . $semestre . ' semestre. ' . $detalle
                . ' Esta entrega es obligatoria para mantener actualizado tu expediente laboral.';
        }
        return 'Capital Humano solicita tu constancia detallada de semanas cotizadas del IMSS correspondiente a '
            . $anio . ', ' . $semestre . ' semestre. Este documento es obligatorio para mantener actualizado '
            . 'tu expediente laboral. Descárgalo desde el portal oficial del IMSS y súbelo en formato PDF.';
    }

    private static function condicionPersonaElegible(string $alias = 'p', string $aliasPais = 'pa'): string
    {
        return "
            {$alias}.estatus = 'Activo'
            AND COALESCE({$alias}.es_externo, 0) = 0
            AND EXISTS (
                SELECT 1
                FROM estado_cuenta.rrhh_plantilla_activa pla_elegible
                WHERE pla_elegible.id_persona = {$alias}.id
                  AND pla_elegible.activo = 1
            )
            AND LOWER(TRIM(COALESCE({$aliasPais}.codigo_iso, 'mx'))) = LOWER(TRIM(c.codigo_pais))
        ";
    }

    public static function buscarPersonasElegibles(string $buscar = '', int $limite = 50): array
    {
        try {
            self::asegurarTablas();
            $buscar = trim($buscar);
            $limite = max(1, min($limite, 100));
            $params = [];
            $filtro = '';
            if ($buscar !== '') {
                $filtro = " AND (
                    p.numero_empleado LIKE :buscar
                    OR p.user_name LIKE :buscar
                    OR p.correo LIKE :buscar
                    OR CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) LIKE :buscar
                )";
                $params['buscar'] = '%' . mb_substr($buscar, 0, 80) . '%';
            }
            $db = new Database();
            self::asegurarPlantillaActivaExpedientes($db);
            $rows = $db->queryAll("
                SELECT
                    p.id AS id_persona,
                    p.numero_empleado,
                    p.user_name,
                    p.correo,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
                WHERE p.estatus = 'Activo'
                  AND COALESCE(p.es_externo, 0) = 0
                  AND EXISTS (
                      SELECT 1
                      FROM estado_cuenta.rrhh_plantilla_activa pla_elegible
                      WHERE pla_elegible.id_persona = p.id
                        AND pla_elegible.activo = 1
                  )
                  AND LOWER(TRIM(COALESCE(pa.codigo_iso, 'mx'))) = 'mx'
                  {$filtro}
                ORDER BY nombre ASC
                LIMIT {$limite}
            ", $params);
            return self::resultado(true, 'Colaboradores encontrados.', $rows ?: []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudieron consultar los colaboradores.', [], $e->getMessage());
        }
    }

    public static function guardarCampania(array $datos, int $idUsuario): array
    {
        try {
            self::asegurarTablas();
            $tipo = trim((string)($datos['tipo'] ?? ''));
            $catalogo = self::catalogoPorClave($tipo);
            if (!$catalogo) {
                return self::resultado(false, 'Tipo de notificación no disponible.');
            }

            $anio = (int)($datos['anio'] ?? 0);
            $semestre = (int)($datos['semestre'] ?? 0);
            self::validarPeriodo($anio, $semestre);
            $titulo = trim((string)($datos['titulo'] ?? ''));
            if ($titulo === '') {
                $titulo = self::nombrePeriodo($anio, $semestre, $tipo);
            }
            $mensaje = trim((string)($datos['mensaje'] ?? ''));
            if ($mensaje === '') {
                $mensaje = self::mensajePredeterminado($tipo, $anio, $semestre);
            }
            $url = trim((string)($datos['url_descarga'] ?? ''));
            if ($url === '') {
                $url = (string)($catalogo['url_descarga'] ?? '');
            }
            $urlValida = $url === ''
                || str_starts_with($url, '/')
                || (filter_var($url, FILTER_VALIDATE_URL) && stripos($url, 'https://') === 0);
            if (!$urlValida) {
                return self::resultado(false, 'El enlace debe ser una ruta interna o una URL HTTPS válida.');
            }
            $fechaLimite = trim((string)($datos['fecha_limite'] ?? ''));
            if ($fechaLimite !== '') {
                $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $fechaLimite);
                if (!$dt || $dt->format('Y-m-d') !== $fechaLimite) {
                    return self::resultado(false, 'La fecha límite no es válida.');
                }
                $fechaLimite .= ' 23:59:59';
            } else {
                $fechaLimite = null;
            }
            $activa = !empty($datos['activa']) ? 1 : 0;
            $alcance = (($datos['alcance'] ?? 'todos') === 'seleccionados') ? 'seleccionados' : 'todos';
            $personaIds = array_values(array_unique(array_filter(array_map(
                'intval',
                is_array($datos['persona_ids'] ?? null) ? $datos['persona_ids'] : []
            ), static fn (int $id): bool => $id > 0)));
            if ($alcance === 'seleccionados' && !$personaIds) {
                return self::resultado(false, 'Selecciona al menos un colaborador.');
            }

            $db = new Database();
            if ($alcance === 'seleccionados') {
                foreach ($personaIds as $idPersona) {
                    if (!self::obtenerPersonaElegible($db, $idPersona, 'mx')) {
                        return self::resultado(false, 'La selección contiene un colaborador inactivo o no aplicable.');
                    }
                }
            }
            $db->CRUD("
                INSERT INTO estado_cuenta.rrhh_notificacion_documental_campania
                    (tipo, anio, semestre, titulo, mensaje, url_descarga, codigo_pais, alcance,
                     obligatoria, activa, fecha_inicio, fecha_limite, creado_por, actualizado_por)
                VALUES
                    (:tipo, :anio, :semestre, :titulo, :mensaje, :url, 'mx', :alcance,
                     1, :activa, NOW(), :fecha_limite, :usuario, :usuario)
                ON DUPLICATE KEY UPDATE
                    id = LAST_INSERT_ID(id),
                    titulo = VALUES(titulo),
                    mensaje = VALUES(mensaje),
                    url_descarga = VALUES(url_descarga),
                    alcance = VALUES(alcance),
                    obligatoria = 1,
                    fecha_inicio = IF(VALUES(activa) = 1 AND activa = 0, NOW(), fecha_inicio),
                    activa = VALUES(activa),
                    fecha_limite = VALUES(fecha_limite),
                    actualizado_por = VALUES(actualizado_por),
                    actualizado_en = NOW()
            ", [
                'tipo' => $tipo,
                'anio' => $anio,
                'semestre' => $semestre,
                'titulo' => mb_substr($titulo, 0, 180),
                'mensaje' => mb_substr($mensaje, 0, 5000),
                'url' => mb_substr($url, 0, 500),
                'alcance' => $alcance,
                'activa' => $activa,
                'fecha_limite' => $fechaLimite,
                'usuario' => $idUsuario > 0 ? $idUsuario : null,
            ]);
            $id = $db->lastInsertId();
            if ($id <= 0) {
                $fila = $db->queryOne("
                    SELECT id
                    FROM estado_cuenta.rrhh_notificacion_documental_campania
                    WHERE tipo = :tipo AND anio = :anio AND semestre = :semestre AND codigo_pais = 'mx'
                    LIMIT 1
                ", ['tipo' => $tipo, 'anio' => $anio, 'semestre' => $semestre]);
                $id = (int)($fila['id'] ?? 0);
            }
            $db->CRUD("
                DELETE FROM estado_cuenta.rrhh_notificacion_documental_destinatario
                WHERE id_campania = :campania
            ", ['campania' => $id]);
            if ($alcance === 'seleccionados') {
                foreach ($personaIds as $idPersona) {
                    $db->CRUD("
                        INSERT INTO estado_cuenta.rrhh_notificacion_documental_destinatario
                            (id_campania, id_persona, agregado_en)
                        VALUES (:campania, :persona, NOW())
                    ", ['campania' => $id, 'persona' => $idPersona]);
                }
            }

            return self::resultado(true, $activa
                ? 'Notificación obligatoria activada.'
                : 'Campaña guardada como inactiva.', ['id' => $id]);
        } catch (\InvalidArgumentException $e) {
            return self::resultado(false, $e->getMessage());
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo guardar la campaña documental.', null, $e->getMessage());
        }
    }

    public static function cambiarEstadoCampania(int $idCampania, bool $activa, int $idUsuario): array
    {
        try {
            self::asegurarTablas();
            if ($idCampania <= 0) {
                return self::resultado(false, 'Campaña no válida.');
            }
            $db = new Database();
            $afectadas = $db->CRUD("
                UPDATE estado_cuenta.rrhh_notificacion_documental_campania
                SET fecha_inicio = IF(:activa = 1 AND activa = 0, NOW(), fecha_inicio),
                    activa = :activa,
                    actualizado_por = :usuario,
                    actualizado_en = NOW()
                WHERE id = :id
            ", [
                'activa' => $activa ? 1 : 0,
                'usuario' => $idUsuario > 0 ? $idUsuario : null,
                'id' => $idCampania,
            ]);
            if ($afectadas < 1) {
                return self::resultado(false, 'No se encontró la campaña.');
            }
            return self::resultado(true, $activa ? 'Campaña activada.' : 'Campaña pausada.');
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo cambiar el estado de la campaña.', null, $e->getMessage());
        }
    }

    public static function listarCampanias(): array
    {
        try {
            self::asegurarTablas();
            $db = new Database();
            self::asegurarPlantillaActivaExpedientes($db);
            $condicion = self::condicionPersonaElegible();
            $rows = $db->queryAll("
                SELECT
                    c.id,
                    c.tipo,
                    c.anio,
                    c.semestre,
                    c.titulo,
                    c.mensaje,
                    c.url_descarga,
                    c.codigo_pais,
                    c.alcance,
                    c.obligatoria,
                    c.activa,
                    DATE_FORMAT(c.fecha_inicio, '%Y-%m-%d %H:%i') AS fecha_inicio,
                    DATE_FORMAT(c.fecha_limite, '%Y-%m-%d') AS fecha_limite,
                    DATE_FORMAT(c.creado_en, '%Y-%m-%d %H:%i') AS creado_en,
                    COUNT(DISTINCT p.id) AS total_personas,
                    COUNT(DISTINCT e.id_persona) AS entregados,
                    GREATEST(COUNT(DISTINCT p.id) - COUNT(DISTINCT e.id_persona), 0) AS pendientes
                FROM estado_cuenta.rrhh_notificacion_documental_campania c
                LEFT JOIN estado_cuenta.persona p ON 1 = 1
                LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
                LEFT JOIN estado_cuenta.rrhh_notificacion_documental_entrega e
                    ON e.id_campania = c.id AND e.id_persona = p.id
                WHERE {$condicion}
                  AND (
                      c.alcance = 'todos'
                      OR EXISTS (
                          SELECT 1
                          FROM estado_cuenta.rrhh_notificacion_documental_destinatario td
                          WHERE td.id_campania = c.id AND td.id_persona = p.id
                      )
                  )
                GROUP BY c.id
                ORDER BY c.anio DESC, c.semestre DESC, c.id DESC
            ");
            $empresasBase = [
                'maxikash' => [
                    'clave' => 'maxikash',
                    'nombre' => 'MaxiKash',
                    'total_personas' => 0,
                    'entregados' => 0,
                    'pendientes' => 0,
                ],
                'furia_motos' => [
                    'clave' => 'furia_motos',
                    'nombre' => 'Furia Motos',
                    'total_personas' => 0,
                    'entregados' => 0,
                    'pendientes' => 0,
                ],
            ];
            foreach ($rows as &$campania) {
                $porEmpresa = $empresasBase;
                $idCampania = (int)($campania['id'] ?? 0);
                $desglose = $db->queryAll("
                    SELECT
                        CASE
                            WHEN LOWER(COALESCE(emp.nombre_comercial, '')) LIKE '%furia%' THEN 'furia_motos'
                            ELSE 'maxikash'
                        END AS clave,
                        COUNT(DISTINCT p.id) AS total_personas,
                        COUNT(DISTINCT e.id_persona) AS entregados,
                        GREATEST(COUNT(DISTINCT p.id) - COUNT(DISTINCT e.id_persona), 0) AS pendientes
                    FROM estado_cuenta.rrhh_notificacion_documental_campania c
                    INNER JOIN estado_cuenta.persona p ON 1 = 1
                    LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
                    INNER JOIN (
                        SELECT id_persona, MIN(id_empresa) AS id_empresa
                        FROM estado_cuenta.rrhh_plantilla_activa
                        WHERE activo = 1
                        GROUP BY id_persona
                    ) pla ON pla.id_persona = p.id
                    LEFT JOIN estado_cuenta.rrhh_empresas emp ON emp.id = pla.id_empresa
                    LEFT JOIN estado_cuenta.rrhh_notificacion_documental_entrega e
                        ON e.id_campania = c.id AND e.id_persona = p.id
                    WHERE c.id = {$idCampania}
                      AND {$condicion}
                      AND (
                          c.alcance = 'todos'
                          OR EXISTS (
                              SELECT 1
                              FROM estado_cuenta.rrhh_notificacion_documental_destinatario td
                              WHERE td.id_campania = c.id AND td.id_persona = p.id
                          )
                      )
                    GROUP BY clave
                ");
                foreach ($desglose ?: [] as $empresa) {
                    $clave = (string)($empresa['clave'] ?? 'maxikash');
                    $porEmpresa[$clave]['total_personas'] = (int)($empresa['total_personas'] ?? 0);
                    $porEmpresa[$clave]['entregados'] = (int)($empresa['entregados'] ?? 0);
                    $porEmpresa[$clave]['pendientes'] = (int)($empresa['pendientes'] ?? 0);
                }
                $campania['por_empresa'] = array_values($porEmpresa);
            }
            unset($campania);
            return self::resultado(true, 'Campañas encontradas.', $rows ?: []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudieron consultar las campañas.', [], $e->getMessage());
        }
    }

    public static function personasCampania(int $idCampania, string $estado = 'todos', string $buscar = ''): array
    {
        try {
            self::asegurarTablas();
            if ($idCampania <= 0) {
                return self::resultado(false, 'Campaña no válida.', []);
            }
            $estado = in_array($estado, ['todos', 'pendientes', 'entregados'], true) ? $estado : 'todos';
            $params = ['campania' => $idCampania];
            $filtroEstado = '';
            if ($estado === 'pendientes') {
                $filtroEstado = ' AND e.id IS NULL';
            } elseif ($estado === 'entregados') {
                $filtroEstado = ' AND e.id IS NOT NULL';
            }
            $filtroBuscar = '';
            $buscar = trim($buscar);
            if ($buscar !== '') {
                $filtroBuscar = " AND (
                    p.numero_empleado LIKE :buscar
                    OR p.user_name LIKE :buscar
                    OR CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) LIKE :buscar
                )";
                $params['buscar'] = '%' . mb_substr($buscar, 0, 80) . '%';
            }

            $db = new Database();
            self::asegurarPlantillaActivaExpedientes($db);
            $condicion = self::condicionPersonaElegible();
            $rows = $db->queryAll("
                SELECT
                    p.id AS id_persona,
                    p.numero_empleado,
                    p.user_name,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                    p.correo,
                    COALESCE(p.estatus, '') AS estatus,
                    CASE WHEN e.id IS NULL THEN 'Pendiente' ELSE 'Entregado' END AS estado_entrega,
                    e.id_documento_carga,
                    e.nombre_logico,
                    e.nombre_original,
                    e.archivo,
                    e.patrones_vigentes,
                    e.patrones_historial,
                    e.patrones_fuente,
                    e.patrones_analisis_json,
                    CASE
                        WHEN e.id IS NULL THEN NULL
                        WHEN e.patrones_analizado_en IS NULL THEN 'pendiente'
                        WHEN e.patrones_vigentes IS NULL THEN 'sin_lectura'
                        ELSE 'analizado'
                    END AS estado_analisis_patrones,
                    DATE_FORMAT(e.cargado_en, '%Y-%m-%d %H:%i') AS cargado_en
                FROM estado_cuenta.rrhh_notificacion_documental_campania c
                INNER JOIN estado_cuenta.persona p ON 1 = 1
                LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
                LEFT JOIN estado_cuenta.rrhh_notificacion_documental_entrega e
                    ON e.id_campania = c.id AND e.id_persona = p.id
                WHERE c.id = :campania
                  AND {$condicion}
                  AND (
                      c.alcance = 'todos'
                      OR EXISTS (
                          SELECT 1
                          FROM estado_cuenta.rrhh_notificacion_documental_destinatario td
                          WHERE td.id_campania = c.id AND td.id_persona = p.id
                      )
                  )
                  {$filtroEstado}
                  {$filtroBuscar}
                ORDER BY (e.id IS NULL) DESC, nombre ASC
                LIMIT 1000
            ", $params);
            foreach ($rows ?: [] as &$row) {
                $row['mensaje_analisis_patrones'] = '';
                $analisis = json_decode((string)($row['patrones_analisis_json'] ?? ''), true);
                if (($row['estado_analisis_patrones'] ?? '') === 'sin_lectura'
                    && is_array($analisis)
                    && array_key_exists('valido', $analisis)
                    && $analisis['valido'] === false) {
                    $clasificacion = (string)($analisis['clasificacion'] ?? '');
                    if ($clasificacion === 'documento_incorrecto'
                        || (string)($analisis['codigo_resultado'] ?? '') === 'error_portal_imss') {
                        $row['estado_analisis_patrones'] = 'documento_incorrecto';
                    } elseif (!empty($analisis['mensaje'])
                        && stripos((string)$analisis['mensaje'], 'no se reconoci') !== false) {
                        // Compatibilidad con lecturas generadas antes de que
                        // existiera la clasificación explícita.
                        $row['estado_analisis_patrones'] = 'documento_incorrecto';
                    }
                    $row['mensaje_analisis_patrones'] = mb_substr(
                        trim((string)($analisis['mensaje'] ?? '')),
                        0,
                        350
                    );
                }
                unset($row['patrones_analisis_json']);
            }
            unset($row);
            return self::resultado(true, 'Personas encontradas.', $rows ?: []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo consultar el avance de la campaña.', [], $e->getMessage());
        }
    }

    private static function obtenerPersonaElegible(Database $db, int $idPersona, string $codigoPais): ?array
    {
        self::asegurarPlantillaActivaExpedientes($db);
        return $db->queryOne("
            SELECT
                p.id,
                p.numero_empleado,
                p.user_name,
                TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                LOWER(TRIM(COALESCE(pa.codigo_iso, 'mx'))) AS codigo_pais
            FROM estado_cuenta.persona p
            LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
            WHERE p.id = :id
              AND p.estatus = 'Activo'
              AND COALESCE(p.es_externo, 0) = 0
              AND EXISTS (
                  SELECT 1
                  FROM estado_cuenta.rrhh_plantilla_activa pla_elegible
                  WHERE pla_elegible.id_persona = p.id
                    AND pla_elegible.activo = 1
              )
              AND LOWER(TRIM(COALESCE(pa.codigo_iso, 'mx'))) = LOWER(TRIM(:pais))
            LIMIT 1
        ", ['id' => $idPersona, 'pais' => $codigoPais]);
    }

    public static function obtenerPendientePersona(int $idPersona): array
    {
        try {
            self::asegurarTablas();
            if ($idPersona <= 0) {
                return self::resultado(true, 'Sin persona asociada.', null);
            }
            $db = new Database();
            $campania = $db->queryOne("
                SELECT
                    c.id,
                    c.tipo,
                    c.anio,
                    c.semestre,
                    c.titulo,
                    c.mensaje,
                    c.url_descarga,
                    c.codigo_pais,
                    c.obligatoria,
                    DATE_FORMAT(c.fecha_limite, '%Y-%m-%d') AS fecha_limite,
                    CASE
                        WHEN c.fecha_limite IS NULL THEN 1
                        WHEN CURDATE() >= DATE(c.fecha_limite) THEN 1
                        ELSE 0
                    END AS bloqueo_obligatorio
                FROM estado_cuenta.rrhh_notificacion_documental_campania c
                LEFT JOIN estado_cuenta.rrhh_notificacion_documental_entrega e
                    ON e.id_campania = c.id AND e.id_persona = :persona
                WHERE c.activa = 1
                  AND c.obligatoria = 1
                  AND c.fecha_inicio <= NOW()
                  AND (
                      c.alcance = 'todos'
                      OR EXISTS (
                          SELECT 1
                          FROM estado_cuenta.rrhh_notificacion_documental_destinatario td
                          WHERE td.id_campania = c.id AND td.id_persona = :persona
                      )
                  )
                  AND e.id IS NULL
                ORDER BY c.anio ASC, c.semestre ASC, c.id ASC
                LIMIT 1
            ", ['persona' => $idPersona]);
            if (!$campania) {
                return self::resultado(true, 'No hay documentos obligatorios pendientes.', null);
            }
            $persona = self::obtenerPersonaElegible($db, $idPersona, (string)$campania['codigo_pais']);
            if (!$persona) {
                return self::resultado(true, 'La campaña no aplica a esta persona.', null);
            }
            $campania['nombre_documento'] = self::nombrePeriodo(
                (int)$campania['anio'],
                (int)$campania['semestre'],
                (string)$campania['tipo']
            );
            $catalogo = self::catalogoPorClave((string)$campania['tipo']);
            $campania['instrucciones'] = $catalogo['instrucciones'] ?? [];
            $campania['enlaces'] = $catalogo['enlaces'] ?? [];
            $urlGuardada = trim((string)($campania['url_descarga'] ?? ''));
            if ($urlGuardada !== '') {
                $enlacePrincipal = [
                    'label' => (string)($catalogo['texto_enlace'] ?? 'Abrir enlace'),
                    'url' => $urlGuardada,
                ];
                if (!empty($campania['enlaces'])) {
                    $campania['enlaces'][0] = $enlacePrincipal;
                } else {
                    $campania['enlaces'][] = $enlacePrincipal;
                }
            }
            $campania['formatos_aceptados'] = ['PDF'];
            $campania['tamanio_maximo_mb'] = 15;
            return self::resultado(true, 'Documento obligatorio pendiente.', $campania);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo comprobar la obligación documental.', null, $e->getMessage());
        }
    }

    public static function registrarEntrega(
        int $idCampania,
        int $idPersona,
        string $archivo,
        string $nombreOriginal,
        int $tamanio,
        string $sha256,
        ?array $analisisPatrones = null
    ): array {
        try {
            self::asegurarTablas();
            $db = new Database();
            $campania = $db->queryOne("
                SELECT
                    id,
                    tipo,
                    anio,
                    semestre,
                    codigo_pais,
                    activa,
                    obligatoria,
                    fecha_inicio,
                    (fecha_inicio <= NOW()) AS iniciada
                FROM estado_cuenta.rrhh_notificacion_documental_campania
                WHERE id = :id
                LIMIT 1
            ", ['id' => $idCampania]);
            if (!$campania || (int)$campania['activa'] !== 1 || (int)$campania['obligatoria'] !== 1) {
                return self::resultado(false, 'La solicitud ya no está activa.');
            }
            if ((int)($campania['iniciada'] ?? 0) !== 1) {
                return self::resultado(false, 'La solicitud todavía no ha iniciado.');
            }
            $catalogo = self::catalogoPorClave((string)$campania['tipo']);
            if (!$catalogo) {
                return self::resultado(false, 'Tipo de documento no compatible.');
            }
            if (!self::obtenerPersonaElegible($db, $idPersona, (string)$campania['codigo_pais'])) {
                return self::resultado(false, 'Esta obligación no corresponde al colaborador.');
            }
            $existente = $db->queryOne("
                SELECT id, nombre_logico, cargado_en
                FROM estado_cuenta.rrhh_notificacion_documental_entrega
                WHERE id_campania = :campania AND id_persona = :persona
                LIMIT 1
            ", ['campania' => $idCampania, 'persona' => $idPersona]);
            if ($existente) {
                return self::resultado(true, 'El documento de este periodo ya fue entregado.', [
                    'ya_entregado' => true,
                    'nombre_documento' => $existente['nombre_logico'],
                ]);
            }

            $nombreLogico = self::nombrePeriodo(
                (int)$campania['anio'],
                (int)$campania['semestre'],
                (string)$campania['tipo']
            );
            $db->beginTransaction();
            try {
                $db->CRUD("
                    INSERT INTO estado_cuenta.carga_documento_persona
                        (id_persona, id_documento, archivo, fecha_carga)
                    VALUES (:persona, :documento, :archivo, NOW())
                ", [
                    'persona' => $idPersona,
                    'documento' => (int)$catalogo['documento_id'],
                    'archivo' => $archivo,
                ]);
                $idDocumentoCarga = $db->lastInsertId();
                if ($idDocumentoCarga <= 0) {
                    throw new \RuntimeException('No se obtuvo el identificador del documento.');
                }
                $db->CRUD("
                    INSERT INTO estado_cuenta.rrhh_notificacion_documental_entrega
                        (id_campania, id_persona, id_documento_carga, nombre_logico,
                         nombre_original, archivo, tamanio_bytes, sha256,
                         patrones_vigentes, patrones_historial, patrones_fuente,
                         patrones_analisis_json, patrones_analizado_en, cargado_en)
                    VALUES
                        (:campania, :persona, :documento_carga, :nombre_logico,
                         :nombre_original, :archivo, :tamanio, :sha256,
                         :patrones_vigentes, :patrones_historial, :patrones_fuente,
                         :patrones_json, :patrones_analizado_en, NOW())
                ", [
                    'campania' => $idCampania,
                    'persona' => $idPersona,
                    'documento_carga' => $idDocumentoCarga,
                    'nombre_logico' => $nombreLogico,
                    'nombre_original' => mb_substr($nombreOriginal, 0, 255),
                    'archivo' => $archivo,
                    'tamanio' => max(0, $tamanio),
                    'sha256' => strtolower($sha256),
                    'patrones_vigentes' => isset($analisisPatrones['patrones_vigentes'])
                        ? max(0, (int)$analisisPatrones['patrones_vigentes'])
                        : null,
                    'patrones_historial' => isset($analisisPatrones['patrones_historial'])
                        ? max(0, (int)$analisisPatrones['patrones_historial'])
                        : null,
                    'patrones_fuente' => $analisisPatrones !== null
                        ? mb_substr((string)($analisisPatrones['fuente_lectura'] ?? 'motor_v1_pdf_text_ocr'), 0, 50)
                        : null,
                    'patrones_json' => $analisisPatrones !== null
                        ? json_encode($analisisPatrones, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : null,
                    'patrones_analizado_en' => $analisisPatrones !== null ? date('Y-m-d H:i:s') : null,
                ]);
                $db->commit();
            } catch (\Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollback();
                }
                throw $e;
            }

            return self::resultado(true, 'Documento recibido correctamente.', [
                'ya_entregado' => false,
                'nombre_documento' => $nombreLogico,
                'id_documento_carga' => $idDocumentoCarga,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo registrar la entrega documental.', null, $e->getMessage());
        }
    }

    public static function entregasPendientesAnalisisPatrones(int $idCampania, int $limite = 6): array
    {
        try {
            self::asegurarTablas();
            $limite = max(1, min(1000, $limite));
            $db = new Database();
            $rows = $db->queryAll("
                SELECT e.id, e.archivo
                FROM estado_cuenta.rrhh_notificacion_documental_entrega e
                INNER JOIN estado_cuenta.rrhh_notificacion_documental_campania c
                    ON c.id = e.id_campania
                WHERE e.id_campania = :campania
                  AND c.tipo = :tipo
                  AND e.patrones_analizado_en IS NULL
                ORDER BY e.id ASC
                LIMIT {$limite}
            ", [
                'campania' => $idCampania,
                'tipo' => self::TIPO_SEMANAS_COTIZADAS,
            ]);
            return self::resultado(true, 'Entregas pendientes encontradas.', $rows ?: []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudieron consultar las lecturas pendientes.', [], $e->getMessage());
        }
    }

    public static function guardarAnalisisPatronesEntrega(int $idEntrega, ?array $analisis): bool
    {
        self::asegurarTablas();
        $db = new Database();
        $vigentes = isset($analisis['patrones_vigentes'])
            ? max(0, (int)$analisis['patrones_vigentes'])
            : null;
        $historial = isset($analisis['patrones_historial'])
            ? max(0, (int)$analisis['patrones_historial'])
            : null;
        $fuente = $analisis !== null
            ? mb_substr((string)($analisis['fuente_lectura'] ?? 'motor_v1_pdf_text_ocr'), 0, 50)
            : 'motor_v1_sin_lectura';
        $json = json_encode(
            $analisis ?? [
                'valido' => false,
                'revision_manual' => true,
                'mensaje' => 'No fue posible leer el PDF con el Motor V1.',
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $db->CRUD("
            UPDATE estado_cuenta.rrhh_notificacion_documental_entrega
            SET patrones_vigentes = :vigentes,
                patrones_historial = :historial,
                patrones_fuente = :fuente,
                patrones_analisis_json = :analisis,
                patrones_analizado_en = NOW()
            WHERE id = :id
            LIMIT 1
        ", [
            'vigentes' => $vigentes,
            'historial' => $historial,
            'fuente' => $fuente,
            'analisis' => $json,
            'id' => $idEntrega,
        ]);
        return true;
    }

    public static function reiniciarAnalisisPatronesDocumento(int $idDocumentoCarga): array
    {
        try {
            self::asegurarTablas();
            if ($idDocumentoCarga <= 0) {
                return self::resultado(false, 'Documento no válido.');
            }
            $db = new Database();
            $entrega = $db->queryOne("
                SELECT e.id, e.id_campania
                FROM estado_cuenta.rrhh_notificacion_documental_entrega e
                INNER JOIN estado_cuenta.rrhh_notificacion_documental_campania c
                    ON c.id = e.id_campania
                WHERE e.id_documento_carga = :documento
                  AND c.tipo = :tipo
                  AND e.patrones_analizado_en IS NOT NULL
                  AND e.patrones_vigentes IS NULL
                LIMIT 1
            ", [
                'documento' => $idDocumentoCarga,
                'tipo' => self::TIPO_SEMANAS_COTIZADAS,
            ]);
            if (!$entrega) {
                return self::resultado(false, 'Esta entrega no requiere un reintento de lectura.');
            }
            $db->CRUD("
                UPDATE estado_cuenta.rrhh_notificacion_documental_entrega
                SET patrones_vigentes = NULL,
                    patrones_historial = NULL,
                    patrones_fuente = NULL,
                    patrones_analisis_json = NULL,
                    patrones_analizado_en = NULL
                WHERE id = :id
                LIMIT 1
            ", ['id' => (int)$entrega['id']]);
            return self::resultado(true, 'El PDF fue enviado nuevamente al Motor V1.', [
                'id_campania' => (int)$entrega['id_campania'],
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo reiniciar la lectura del documento.', null, $e->getMessage());
        }
    }

    public static function analizarArchivoPatronesMotorV1(string $rutaPdf): ?array
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile) || !is_file($rutaPdf)) {
            return null;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim((string)($config['doc_verificacion']['api_url'] ?? ''));
        $apiKey = trim((string)($config['doc_verificacion']['api_key'] ?? ''));
        if ($apiUrl === '' || $apiKey === '') {
            return null;
        }
        $baseUrl = preg_replace('#/verificar/?\s*$#', '', $apiUrl);
        $url = rtrim((string)$baseUrl, '/') . '/analizar-semanas-cotizadas';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'documento' => new \CURLFile($rutaPdf, 'application/pdf', basename($rutaPdf)),
            ],
            CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false || $httpCode !== 200) {
            return null;
        }
        $data = json_decode((string)$body, true);
        return is_array($data) ? $data : null;
    }

    public static function motorV1PatronesDisponible(): bool
    {
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (!is_file($configFile)) {
            return false;
        }
        $config = @parse_ini_file($configFile, true);
        $apiUrl = trim((string)($config['doc_verificacion']['api_url'] ?? ''));
        if ($apiUrl === '') {
            return false;
        }
        $baseUrl = preg_replace('#/verificar/?\s*$#', '', $apiUrl);
        $ch = curl_init(rtrim((string)$baseUrl, '/') . '/health');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $body !== false && $httpCode === 200;
    }

    public static function contarEntregasPendientesAnalisisPatrones(int $idCampania): int
    {
        self::asegurarTablas();
        $db = new Database();
        $row = $db->queryOne("
            SELECT COUNT(*) AS total
            FROM estado_cuenta.rrhh_notificacion_documental_entrega e
            INNER JOIN estado_cuenta.rrhh_notificacion_documental_campania c
                ON c.id = e.id_campania
            WHERE e.id_campania = :campania
              AND c.tipo = :tipo
              AND e.patrones_analizado_en IS NULL
        ", [
            'campania' => $idCampania,
            'tipo' => self::TIPO_SEMANAS_COTIZADAS,
        ]);
        return (int)($row['total'] ?? 0);
    }

    public static function campaniasPendientesAnalisisPatrones(int $limite = 20): array
    {
        self::asegurarTablas();
        $limite = max(1, min(100, $limite));
        $db = new Database();
        $rows = $db->queryAll("
            SELECT DISTINCT e.id_campania
            FROM estado_cuenta.rrhh_notificacion_documental_entrega e
            INNER JOIN estado_cuenta.rrhh_notificacion_documental_campania c
                ON c.id = e.id_campania
            WHERE c.tipo = :tipo
              AND e.patrones_analizado_en IS NULL
            ORDER BY e.id_campania ASC
            LIMIT {$limite}
        ", ['tipo' => self::TIPO_SEMANAS_COTIZADAS]);

        return array_values(array_filter(array_map(
            static fn(array $row): int => (int)($row['id_campania'] ?? 0),
            $rows ?: []
        )));
    }
}
