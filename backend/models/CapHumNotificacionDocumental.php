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
                cargado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_rrhh_notif_entrega_persona (id_campania, id_persona),
                UNIQUE KEY uq_rrhh_notif_entrega_documento (id_documento_carga),
                KEY idx_rrhh_notif_entrega_persona (id_persona, cargado_en),
                KEY idx_rrhh_notif_entrega_campania (id_campania, cargado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
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
            LOWER(TRIM(COALESCE({$alias}.estatus, 'activo'))) NOT IN ('baja', 'transito de baja', 'inactivo')
            AND COALESCE({$alias}.es_externo, 0) = 0
            AND NULLIF(TRIM(COALESCE({$alias}.user_name, '')), '') IS NOT NULL
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
            $rows = $db->queryAll("
                SELECT
                    p.id AS id_persona,
                    p.numero_empleado,
                    p.user_name,
                    p.correo,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre
                FROM estado_cuenta.persona p
                LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
                WHERE LOWER(TRIM(COALESCE(p.estatus, 'activo'))) NOT IN ('baja', 'transito de baja', 'inactivo')
                  AND COALESCE(p.es_externo, 0) = 0
                  AND NULLIF(TRIM(COALESCE(p.user_name, '')), '') IS NOT NULL
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
                    e.nombre_logico,
                    e.nombre_original,
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
            return self::resultado(true, 'Personas encontradas.', $rows ?: []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo consultar el avance de la campaña.', [], $e->getMessage());
        }
    }

    private static function obtenerPersonaElegible(Database $db, int $idPersona, string $codigoPais): ?array
    {
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
              AND LOWER(TRIM(COALESCE(p.estatus, 'activo'))) NOT IN ('baja', 'transito de baja', 'inactivo')
              AND COALESCE(p.es_externo, 0) = 0
              AND NULLIF(TRIM(COALESCE(p.user_name, '')), '') IS NOT NULL
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
                    DATE_FORMAT(c.fecha_limite, '%Y-%m-%d') AS fecha_limite
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
        string $sha256
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
                         nombre_original, archivo, tamanio_bytes, sha256, cargado_en)
                    VALUES
                        (:campania, :persona, :documento_carga, :nombre_logico,
                         :nombre_original, :archivo, :tamanio, :sha256, NOW())
                ", [
                    'campania' => $idCampania,
                    'persona' => $idPersona,
                    'documento_carga' => $idDocumentoCarga,
                    'nombre_logico' => $nombreLogico,
                    'nombre_original' => mb_substr($nombreOriginal, 0, 255),
                    'archivo' => $archivo,
                    'tamanio' => max(0, $tamanio),
                    'sha256' => strtolower($sha256),
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
}
