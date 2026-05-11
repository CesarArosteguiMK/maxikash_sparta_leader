<?php

namespace Models;

use Core\Model;
use Core\Database;

class Candidatos extends Model
{
    private static function candidatoTokenTieneColumnaExpira(Database $db): bool
    {
        try {
            $row = $db->queryOne(
                "SELECT COUNT(*) AS c
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'candidato_documento_token'
                   AND COLUMN_NAME = 'expira'"
            );
            return (int) ($row['c'] ?? 0) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    private static function storageRoot(): string
    {
        return defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');
    }

    private static function filtrarDocumentosConArchivo(array $documentos): array
    {
        $storageRoot = self::storageRoot();
        $filtrados = [];
        foreach ($documentos as $doc) {
            $ruta = trim((string) ($doc['ruta_archivo'] ?? ''));
            if ($ruta !== '' && !is_file($storageRoot . '/' . $ruta)) {
                continue;
            }
            $filtrados[] = $doc;
        }

        return $filtrados;
    }

    /**
     * Listar todos los candidatos con puesto y departamento de interés.
     */
    public static function getAll($estatus = null, $id_departamento = null, $id_puesto = null)
    {
        $query = <<<SQL
            SELECT
                c.id,
                c.nombres,
                c.segundo_nombre,
                c.apellidop,
                c.apellidom,
                c.email,
                c.telefono,
                c.id_div_nivel1,
                c.id_div_nivel2,
                c.id_div_nivel3,
                c.domicilio_calle_texto,
                c.domicilio_num_exterior,
                c.domicilio_num_interior,
                c.id_puesto,
                c.id_departamento,
                c.estatus,
                c.notas,
                c.estatus,
                c.notas,
                c.postulacion_enviada,
                c.fecha_registro,
                c.fecha_actualizacion,
                pais.nombre AS nombre_pais,
                div1.nombre AS nombre_div_nivel1,
                div2.nombre AS nombre_div_nivel2,
                div3.nombre AS nombre_div_nivel3,
                p.nombre AS nombre_puesto,
                d.nombre AS nombre_departamento
            FROM candidatos c
            LEFT JOIN paises pais ON pais.id = c.id_pais
            LEFT JOIN divisiones_administrativas div1 ON div1.id = c.id_div_nivel1
            LEFT JOIN divisiones_administrativas div2 ON div2.id = c.id_div_nivel2
            LEFT JOIN divisiones_administrativas div3 ON div3.id = c.id_div_nivel3
            LEFT JOIN puesto p ON p.id = c.id_puesto
            LEFT JOIN departamento d ON d.id = c.id_departamento
            WHERE 1=1
        SQL;
        $params = [];

        if ($estatus !== null && $estatus !== '') {
            $query .= " AND c.estatus = :estatus";
            $params['estatus'] = $estatus;
        }
        if ($id_departamento !== null && $id_departamento !== '') {
            $query .= " AND c.id_departamento = :id_departamento";
            $params['id_departamento'] = (int) $id_departamento;
        }
        if ($id_puesto !== null && $id_puesto !== '') {
            $query .= " AND c.id_puesto = :id_puesto";
            $params['id_puesto'] = (int) $id_puesto;
        }

        $query .= " ORDER BY c.fecha_registro DESC";

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Candidatos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener candidatos.', null, $e->getMessage());
        }
    }

    /**
     * Obtener un candidato por ID.
     */
    public static function getById($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        $query = <<<SQL
            SELECT
                c.id,
                c.nombres,
                c.segundo_nombre,
                c.apellidop,
                c.apellidom,
                c.email,
                c.telefono,
                c.id_pais,
                c.id_div_nivel1,
                c.id_div_nivel2,
                c.id_div_nivel3,
                c.domicilio_calle_texto,
                c.domicilio_num_exterior,
                c.domicilio_num_interior,
                c.id_puesto,
                c.id_departamento,
                c.id_posible_jefe,
                c.fecha_postulacion,
                c.id_legion,
                c.usuario,
                c.contrasena,
                c.estatus,
                c.notas,
                c.postulacion_enviada,
                c.fecha_postulacion_enviada,
                c.fecha_registro,
                c.fecha_actualizacion,
                p.nombre AS nombre_puesto,
                d.nombre AS nombre_departamento
            FROM candidatos c
            LEFT JOIN puesto p ON p.id = c.id_puesto
            LEFT JOIN departamento d ON d.id = c.id_departamento
            WHERE c.id = :id
        SQL;
        try {
            $db = new Database();
            $r = $db->queryOne($query, ['id' => $id]);
            if (!$r) {
                return self::resultado(false, 'Candidato no encontrado.', null);
            }
            return self::resultado(true, 'Candidato encontrado.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener candidato.', null, $e->getMessage());
        }
    }

    /**
     * Fecha y hora actual en Ciudad de México (valor guardado como datetime “naive” interpretado siempre en CDMX).
     */
    private static function fechaHoraActualMexicoCiudad(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }

    /**
     * Registra el momento en que se envió por correo la postulación/enlace de documentos (hora CDMX).
     */
    public static function registrarFechaEnvioCorreoPostulacion($id_candidato, $fechaHoraCdmxMysql)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || $fechaHoraCdmxMysql === null || trim((string) $fechaHoraCdmxMysql) === '') {
            return;
        }
        try {
            $db = new Database();
            $fe = trim((string) $fechaHoraCdmxMysql);
            $db->CRUD(
                'UPDATE candidatos SET fecha_postulacion_enviada = :fe, postulacion_enviada = 1, fecha_actualizacion = :fe WHERE id = :id',
                ['id' => $id_candidato, 'fe' => $fe]
            );
        } catch (\Exception $e) {
        }
    }

    /**
     * Insertar nuevo candidato (con postulación enviada y datos de postulación).
     */
    public static function insert($data)
    {
        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

        $postulacionEnviada = !empty($data['postulacion_enviada']) ? 1 : 0;
        $fechaEnviada = $postulacionEnviada ? self::fechaHoraActualMexicoCiudad() : null;

        $query = <<<SQL
            INSERT INTO candidatos (
                nombres, segundo_nombre, apellidop, apellidom,
                email, telefono, id_pais, id_div_nivel1, id_div_nivel2, id_div_nivel3, domicilio_calle_texto, domicilio_num_exterior, domicilio_num_interior,
                id_puesto, id_departamento, id_posible_jefe,
                fecha_postulacion, id_legion, usuario, contrasena,
                postulacion_enviada, fecha_postulacion_enviada, estatus, notas
            ) VALUES (
                :nombres, :segundo_nombre, :apellidop, :apellidom,
                :email, :telefono, :id_pais, :id_div_nivel1, :id_div_nivel2, :id_div_nivel3, :domicilio_calle_texto, :domicilio_num_exterior, :domicilio_num_interior,
                :id_puesto, :id_departamento, :id_posible_jefe,
                :fecha_postulacion, :id_legion, :usuario, :contrasena,
                :postulacion_enviada, :fecha_postulacion_enviada, :estatus, :notas
            )
        SQL;
        $params = [
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'id_pais' => !empty($data['id_pais']) ? (int) $data['id_pais'] : null,
            'id_div_nivel1' => !empty($data['id_div_nivel1']) ? (int) $data['id_div_nivel1'] : null,
            'id_div_nivel2' => !empty($data['id_div_nivel2']) ? (int) $data['id_div_nivel2'] : null,
            'id_div_nivel3' => !empty($data['id_div_nivel3']) ? (int) $data['id_div_nivel3'] : null,
            'domicilio_calle_texto' => trim($data['domicilio_calle_texto'] ?? '') ?: null,
            'domicilio_num_exterior' => trim($data['domicilio_num_exterior'] ?? '') ?: null,
            'domicilio_num_interior' => trim($data['domicilio_num_interior'] ?? '') ?: null,
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
            'id_posible_jefe' => !empty($data['id_posible_jefe']) ? (int) $data['id_posible_jefe'] : null,
            'fecha_postulacion' => !empty($data['fecha_postulacion']) ? $data['fecha_postulacion'] : null,
            'id_legion' => !empty($data['id_legion']) ? (int) $data['id_legion'] : null,
            'usuario' => trim($data['usuario'] ?? '') ?: null,
            'contrasena' => trim($data['contrasena'] ?? '') ?: null,
            'postulacion_enviada' => $postulacionEnviada,
            'fecha_postulacion_enviada' => $fechaEnviada,
            'estatus' => trim($data['estatus'] ?? '') ?: 'Por evaluar',
            'notas' => trim($data['notas'] ?? '') ?: null,
        ];

        try {
            $db = new Database();
            $db->CRUD($query, $params);
            $newId = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id = (int) ($newId['id'] ?? 0);
            return self::resultado(true, 'Candidato registrado correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar candidato.', null, $e->getMessage());
        }
    }

    /**
     * Actualizar candidato.
     */
    public static function update($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }

        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

        $query = <<<SQL
            UPDATE candidatos SET
                nombres = :nombres,
                segundo_nombre = :segundo_nombre,
                apellidop = :apellidop,
                apellidom = :apellidom,
                email = :email,
                telefono = :telefono,
                id_pais = :id_pais,
                id_div_nivel1 = :id_div_nivel1,
                id_div_nivel2 = :id_div_nivel2,
                id_div_nivel3 = :id_div_nivel3,
                domicilio_calle_texto = :domicilio_calle_texto,
                domicilio_num_exterior = :domicilio_num_exterior,
                domicilio_num_interior = :domicilio_num_interior,
                id_puesto = :id_puesto,
                id_departamento = :id_departamento,
                id_posible_jefe = :id_posible_jefe,
                fecha_postulacion = :fecha_postulacion,
                id_legion = :id_legion,
                usuario = :usuario,
                contrasena = :contrasena,
                estatus = :estatus,
                notas = :notas
            WHERE id = :id
        SQL;
        $params = [
            'id' => $id,
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'id_pais' => !empty($data['id_pais']) ? (int) $data['id_pais'] : null,
            'id_div_nivel1' => !empty($data['id_div_nivel1']) ? (int) $data['id_div_nivel1'] : null,
            'id_div_nivel2' => !empty($data['id_div_nivel2']) ? (int) $data['id_div_nivel2'] : null,
            'id_div_nivel3' => !empty($data['id_div_nivel3']) ? (int) $data['id_div_nivel3'] : null,
            'domicilio_calle_texto' => trim($data['domicilio_calle_texto'] ?? '') ?: null,
            'domicilio_num_exterior' => trim($data['domicilio_num_exterior'] ?? '') ?: null,
            'domicilio_num_interior' => trim($data['domicilio_num_interior'] ?? '') ?: null,
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
            'id_posible_jefe' => !empty($data['id_posible_jefe']) ? (int) $data['id_posible_jefe'] : null,
            'fecha_postulacion' => !empty($data['fecha_postulacion']) ? $data['fecha_postulacion'] : null,
            'id_legion' => !empty($data['id_legion']) ? (int) $data['id_legion'] : null,
            'usuario' => isset($data['usuario']) ? (trim($data['usuario']) ?: null) : null,
            'contrasena' => isset($data['contrasena']) ? (trim($data['contrasena']) ?: null) : null,
            'estatus' => trim($data['estatus'] ?? '') ?: 'Por evaluar',
            'notas' => trim($data['notas'] ?? '') ?: null,
        ];

        try {
            $db = new Database();
            $db->CRUD($query, $params);
            return self::resultado(true, 'Candidato actualizado correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar candidato.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar candidato.
     */
    public static function delete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidatos WHERE id = :id", ['id' => $id]);
            return self::resultado(true, 'Candidato eliminado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar candidato.', null, $e->getMessage());
        }
    }

    /** Días hábiles para plazo de documentación (sección [mail] de config.ini). */
    private static function diasHabilesLimiteDocumentosDesdeIni(): int
    {
        $n = 2;
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (is_file($configFile)) {
            $full = @parse_ini_file($configFile, true);
            $mailSection = is_array($full['mail'] ?? null) ? $full['mail'] : [];
            $n = (int) ($mailSection['dias_habiles_limite_documentos'] ?? 2);
        }
        return $n >= 1 ? $n : 2;
    }

    /** Correo de contacto para mensaje de enlace vencido (misma lógica que CapHum::enviarPostulacionCandidato). */
    private static function mailContactoDocumentacion(): string
    {
        $contacto = '';
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (is_file($configFile)) {
            $full = @parse_ini_file($configFile, true);
            $mailSection = is_array($full['mail'] ?? null) ? $full['mail'] : [];
            $contacto = trim($mailSection['mail_contacto'] ?? '');
            if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
                $contacto = trim($mailSection['smtp_user'] ?? $mailSection['mail_from'] ?? '');
            }
        }
        if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
            $contacto = 'reporteria__SPARTA_SECRET_REDACTED__@gmail.com';
        }

        return $contacto;
    }

    private static function zonaMexico(): \DateTimeZone
    {
        return new \DateTimeZone('America/Mexico_City');
    }

    private static function ahoraMexicoCiudadImmutable(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', self::zonaMexico());
    }

    private static function parseFechaHoraMexicoCiudad(?string $mysql): ?\DateTimeImmutable
    {
        if ($mysql === null || trim($mysql) === '') {
            return null;
        }
        $tz = self::zonaMexico();
        $raw = trim($mysql);
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $raw, $tz);
        if ($dt !== false) {
            return $dt;
        }
        try {
            return new \DateTimeImmutable($raw, $tz);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fin de plazo para documentación: día calendario siguiente en CDMX + N días hábiles (lun–vie), cierre 23:59:59 CDMX.
     * Misma regla que CapHum::documentacionLimiteFinDesdeReferencia.
     */
    private static function documentacionLimiteFinDesdeReferencia(\DateTimeImmutable $referenciaCdmx, int $diasHabiles): \DateTimeImmutable
    {
        $tz = self::zonaMexico();
        $ref = $referenciaCdmx->setTimezone($tz);
        $d = $ref->setTime(0, 0, 0)->modify('+1 day');
        $rest = max(1, $diasHabiles);
        while ($rest > 0) {
            $n = (int) $d->format('N');
            if ($n >= 1 && $n <= 5) {
                $rest--;
                if ($rest === 0) {
                    return $d->setTime(23, 59, 59);
                }
            }
            $d = $d->modify('+1 day');
        }

        return $d->setTime(23, 59, 59);
    }

    /**
     * Fecha/hora límite del token (misma base que el correo: referencia = fecha_postulacion_enviada si existe, si no fecha_registro, si no ahora).
     */
    public static function calcularExpiraTokenMysqlDesdeCandidato(array $c): string
    {
        $dias = self::diasHabilesLimiteDocumentosDesdeIni();
        $refStr = trim((string) ($c['fecha_postulacion_enviada'] ?? ''));
        if ($refStr === '') {
            $refStr = trim((string) ($c['fecha_registro'] ?? ''));
        }
        $ref = self::parseFechaHoraMexicoCiudad($refStr !== '' ? $refStr : null);
        if ($ref === null) {
            $ref = self::ahoraMexicoCiudadImmutable();
        }

        return self::documentacionLimiteFinDesdeReferencia($ref, $dias)->format('Y-m-d H:i:s');
    }

    /**
     * Actualiza la fecha de vencimiento del enlace de documentos (p. ej. al enviar el correo con fecha límite exacta).
     */
    public static function actualizarExpiraTokenDocumentos(int $id_candidato, string $expiraYmdHis): bool
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($expiraYmdHis) === '') {
            return false;
        }
        try {
            $db = new Database();
            $db->CRUD(
                'UPDATE candidato_documento_token SET expira = :e WHERE id_candidato = :id',
                ['e' => $expiraYmdHis, 'id' => $id_candidato]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener o crear token único para link de subida de documentos del candidato.
     * Retorna el token (string) para construir la URL.
     * La columna expira debe existir en la tabla candidato_documento (enlace de subida de documentos).
     */
    public static function getOrCreateTokenDocumentos($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }
        try {
            $db = new Database();
            $cand = $db->queryOne(
                'SELECT id, fecha_postulacion_enviada, fecha_registro FROM candidatos WHERE id = :id LIMIT 1',
                ['id' => $id_candidato]
            );
            if (!$cand) {
                return self::resultado(false, 'Candidato no encontrado.', null);
            }
            $expiraMysql = self::calcularExpiraTokenMysqlDesdeCandidato($cand);
            $usaExpira = self::candidatoTokenTieneColumnaExpira($db);
            $row = $db->queryOne(
                $usaExpira
                    ? 'SELECT token, expira FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1'
                    : 'SELECT token FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1',
                ['id' => $id_candidato]
            );
            if ($row && !empty($row['token'])) {
                $expCol = $row['expira'] ?? null;
                if ($usaExpira && ($expCol === null || trim((string) $expCol) === '')) {
                    self::actualizarExpiraTokenDocumentos($id_candidato, $expiraMysql);
                }

                return self::resultado(true, 'Token existente.', $row['token']);
            }
            $token = bin2hex(random_bytes(32));
            if ($usaExpira) {
                $db->CRUD(
                    'INSERT INTO candidato_documento_token (id_candidato, token, expira) VALUES (:id_candidato, :token, :expira)',
                    ['id_candidato' => $id_candidato, 'token' => $token, 'expira' => $expiraMysql]
                );
            } else {
                $db->CRUD(
                    'INSERT INTO candidato_documento_token (id_candidato, token) VALUES (:id_candidato, :token)',
                    ['id_candidato' => $id_candidato, 'token' => $token]
                );
            }

            return self::resultado(true, 'Token generado.', $token);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al generar token.', null, $e->getMessage());
        }
    }

    /**
     * Obtener id_candidato y datos básicos a partir del token (para la vista pública de subida).
     * Rechaza tokens vencidos (expira en hora local CDMX, fin de día 23:59:59).
     */
    public static function getCandidatoPorToken($token)
    {
        $token = trim($token ?? '');
        if ($token === '') {
            return self::resultado(false, 'Token inválido.', null);
        }
        try {
            $db = new Database();
            $usaExpira = self::candidatoTokenTieneColumnaExpira($db);
            $row = $db->queryOne(
                $usaExpira
                    ? ('SELECT t.id_candidato, t.expira, c.nombres, c.apellidop, c.apellidom, c.email, c.fecha_postulacion_enviada, c.fecha_registro '
                        . 'FROM candidato_documento_token t INNER JOIN candidatos c ON c.id = t.id_candidato WHERE t.token = :token LIMIT 1')
                    : ('SELECT t.id_candidato, c.nombres, c.apellidop, c.apellidom, c.email, c.fecha_postulacion_enviada, c.fecha_registro '
                        . 'FROM candidato_documento_token t INNER JOIN candidatos c ON c.id = t.id_candidato WHERE t.token = :token LIMIT 1'),
                ['token' => $token]
            );
            if (!$row) {
                return self::resultado(false, 'Enlace no válido o expirado.', null);
            }
            $expiraMysql = $row['expira'] ?? null;
            if ($usaExpira && ($expiraMysql === null || trim((string) $expiraMysql) === '')) {
                $expiraMysql = self::calcularExpiraTokenMysqlDesdeCandidato($row);
                self::actualizarExpiraTokenDocumentos((int) $row['id_candidato'], $expiraMysql);
            }
            if ($usaExpira) {
                $limite = self::parseFechaHoraMexicoCiudad((string) $expiraMysql);
                $ahora = self::ahoraMexicoCiudadImmutable();
                if ($limite instanceof \DateTimeImmutable && $ahora > $limite) {
                    $mailCt = self::mailContactoDocumentacion();

                    return self::resultado(
                        false,
                        'Este enlace ha vencido. El plazo para subir la documentación finalizó. Si necesita ayuda, escríbanos a '
                        . $mailCt . '.',
                        null
                    );
                }
            }
            unset($row['expira'], $row['fecha_postulacion_enviada'], $row['fecha_registro']);

            return self::resultado(true, 'Candidato encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al validar enlace.', null, $e->getMessage());
        }
    }

    /**
     * Crea token para confirmación de alta en nómina (enlace en correo a RRHH).
     * Retorna token y expira en 7 días.
     */
    public static function createTokenConfirmacionAlta($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }
        try {
            $db = new Database();
            $token = bin2hex(random_bytes(32));
            $db->CRUD(
                "INSERT INTO candidato_confirmacion_alta_token (token, id_candidato, expira) VALUES (:token, :id_candidato, DATE_ADD(NOW(), INTERVAL 7 DAY))",
                ['token' => $token, 'id_candidato' => $id_candidato]
            );
            return self::resultado(true, 'Token creado.', $token);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al crear token.', null, $e->getMessage());
        }
    }

    /**
     * Obtiene id_candidato por token de confirmación alta nómina. Válido si no usado y no expirado.
     */
    public static function getPorTokenConfirmacionAlta($token)
    {
        $token = trim($token ?? '');
        if ($token === '') {
            return self::resultado(false, 'Enlace no válido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id_candidato FROM candidato_confirmacion_alta_token WHERE token = :token AND usado = 0 AND expira > NOW() LIMIT 1",
                ['token' => $token]
            );
            if (!$row) {
                return self::resultado(false, 'Enlace no válido, ya usado o expirado.', null);
            }
            return self::resultado(true, 'OK', (int) $row['id_candidato']);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al validar enlace.', null, $e->getMessage());
        }
    }

    /**
     * Marca token de confirmación alta nómina como usado (respuesta si o no).
     */
    public static function marcarTokenConfirmacionAltaUsado($token, $respuesta)
    {
        $token = trim($token ?? '');
        $respuesta = strtolower(trim($respuesta ?? '')) === 'si' ? 'si' : 'no';
        if ($token === '') {
            return false;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE candidato_confirmacion_alta_token SET usado = 1, respuesta = :respuesta, fecha_uso = NOW() WHERE token = :token",
                ['respuesta' => $respuesta, 'token' => $token]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Registrar un documento subido por el candidato (vía link).
     * Si se pasan $contenido y $mime_type, el archivo se guarda en la BD (contenido LONGBLOB)
     * y se sirve desde ahí para que cargue más rápido (como carga_documento_persona).
     *
     * @param string $tipo_documento Nombre del tipo (ej. SOLICITUD INTERNA, CURP, etc.)
     * @param string|null $contenido Contenido binario del archivo (opcional). Si se pasa, se guarda en BD.
     * @param string|null $mime_type application/pdf, image/jpeg, etc. (opcional, recomendado si hay contenido)
     * @param string|null $verificacion_fiscal_json JSON con resultado de verificación constancia fiscal (solo tipo CONSTANCIA DE SITUACION FISCAL)
     * @param string|null $verificacion_calidad_json JSON con notas de revisión para identificación oficial (ej. exceso de brillo)
     */
    public static function guardarDocumento($id_candidato, $nombre_archivo, $ruta_archivo, $tipo_documento = '', $contenido = null, $mime_type = null, $verificacion_fiscal_json = null, $verificacion_calidad_json = null)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($nombre_archivo ?? '') === '') {
            return self::resultado(false, 'Datos incompletos.');
        }
        if ($contenido === null && trim($ruta_archivo ?? '') === '') {
            return self::resultado(false, 'Indica ruta_archivo o contenido.');
        }
        try {
            $db = new Database();
            if ($contenido !== null) {
                $ruta = trim($ruta_archivo ?? '');
                $sql = "INSERT INTO candidato_documento (id_candidato, tipo_documento, nombre_archivo, ruta_archivo, contenido, mime_type, verificacion_fiscal_json, verificacion_calidad_json) VALUES (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo, :contenido, :mime_type, :verificacion_fiscal_json, :verificacion_calidad_json)";
                $params = [
                    'id_candidato' => $id_candidato,
                    'tipo_documento' => trim($tipo_documento ?? ''),
                    'nombre_archivo' => $nombre_archivo,
                    'ruta_archivo' => $ruta,
                    'contenido' => $contenido,
                    'mime_type' => $mime_type !== null ? trim($mime_type) : null,
                    'verificacion_fiscal_json' => $verificacion_fiscal_json,
                    'verificacion_calidad_json' => $verificacion_calidad_json,
                ];
                $db->queryOne($sql, $params);
            } else {
                $db->CRUD(
                    "INSERT INTO candidato_documento (id_candidato, tipo_documento, nombre_archivo, ruta_archivo, verificacion_fiscal_json, verificacion_calidad_json) VALUES (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo, :verificacion_fiscal_json, :verificacion_calidad_json)",
                    [
                        'id_candidato' => $id_candidato,
                        'tipo_documento' => trim($tipo_documento ?? ''),
                        'nombre_archivo' => $nombre_archivo,
                        'ruta_archivo' => $ruta_archivo,
                        'verificacion_fiscal_json' => $verificacion_fiscal_json,
                        'verificacion_calidad_json' => $verificacion_calidad_json
                    ]
                );
            }
            self::invalidateDocumentacionCache($id_candidato);
            return self::resultado(true, 'Documento guardado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documento.', null, $e->getMessage());
        }
    }

    /**
     * Actualizar resultados de verificación OCR/API de un documento ya guardado.
     */
    public static function updateVerificacionDocumento($id_documento, $verificacion_fiscal_json = null, $verificacion_calidad_json = null)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $doc = $db->queryOne("SELECT id_candidato FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            if (!$doc) {
                return self::resultado(false, 'Documento no encontrado.');
            }
            $db->CRUD(
                "UPDATE candidato_documento SET verificacion_fiscal_json = :vf, verificacion_calidad_json = :vc WHERE id = :id",
                [
                    'id' => $id_documento,
                    'vf' => $verificacion_fiscal_json,
                    'vc' => $verificacion_calidad_json,
                ]
            );
            self::invalidateDocumentacionCache((int) ($doc['id_candidato'] ?? 0));
            return self::resultado(true, 'Verificación actualizada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar verificación.', null, $e->getMessage());
        }
    }

    /**
     * Obtener solo ruta y nombre de un documento (sin contenido). Para servir desde disco sin cargar LONGBLOB.
     */
    public static function getDocumentoRutaSolo($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT nombre_archivo, ruta_archivo FROM candidato_documento WHERE id = :id",
                ['id' => $id_documento]
            );
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Obtener contenido de un documento para servirlo (ver/descargar).
     * Devuelve nombre_archivo, contenido (LONGBLOB), mime_type.
     * Si el registro tiene contenido en BD se usa; si no, contenido será null (servir desde ruta_archivo en disco).
     */
    public static function getDocumentoContenidoParaVer($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id, nombre_archivo, contenido, mime_type, ruta_archivo FROM candidato_documento WHERE id = :id",
                ['id' => $id_documento]
            );
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Listar documentos ya subidos por un candidato.
     */
    public static function getDocumentosCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID inválido.', []);
        }
        try {
            $db = new Database();
            $lista = $db->queryAll("SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado, verificacion_fiscal_json FROM candidato_documento WHERE id_candidato = :id ORDER BY fecha_carga DESC", ['id' => $id_candidato]);
            return self::resultado(true, 'Documentos encontrados.', self::filtrarDocumentosConArchivo($lista ?: []));
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al listar documentos.', [], $e->getMessage());
        }
    }

    /**
     * Obtener un documento por ID (para verificar y servir/eliminar).
     */
    public static function getDocumentoById($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, validado FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar un documento del expediente (por ID).
     */
    public static function eliminarDocumento($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            return self::resultado(true, 'Documento eliminado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar todos los documentos de un candidato (solo registros en BD).
     * Los archivos en disco deben borrarse desde el controlador.
     */
    public static function eliminarDocumentosDeCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento WHERE id_candidato = :id", ['id' => $id_candidato]);
            return self::resultado(true, 'Documentación eliminada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar documentación.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar el token de enlace de documentos del candidato (para no dejar huérfanos).
     */
    public static function eliminarTokenDocumentosCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento_token WHERE id_candidato = :id", ['id' => $id_candidato]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Guardar el último resultado de verificación de expediente (API validar-expediente).
     * @param int $id_candidato
     * @param string|null $jsonResultado JSON del resultado (checks_ok, alertas, todo_coincide, etc.)
     */
    public static function updateVerificacionExpediente($id_candidato, $jsonResultado)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE candidatos SET ultima_verificacion_expediente = :json WHERE id = :id",
                ['id' => $id_candidato, 'json' => $jsonResultado === null ? null : (is_string($jsonResultado) ? $jsonResultado : json_encode($jsonResultado))]
            );
        } catch (\Exception $e) {
            // Columna puede no existir si no se ejecutó la migración
        }
        self::invalidateDocumentacionCache($id_candidato);
    }

    /**
     * Obtener el último resultado de verificación de expediente (JSON decodificado o null).
     */
    public static function getVerificacionExpediente($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT ultima_verificacion_expediente FROM candidatos WHERE id = :id", ['id' => $id_candidato]);
            if (!$row || empty($row['ultima_verificacion_expediente'])) {
                return null;
            }
            $decoded = json_decode($row['ultima_verificacion_expediente'], true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Documentos + verificación en una sola conexión (para listado Documentación, optimizado).
     * @return array{documentos: array, verificacion: array|null}
     */
    public static function getDocumentosYVerificacion($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return ['documentos' => [], 'verificacion' => null];
        }
        try {
            $db = new Database();
            $documentos = $db->queryAll(
                "SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado, verificacion_fiscal_json, verificacion_calidad_json FROM candidato_documento WHERE id_candidato = :id ORDER BY fecha_carga DESC",
                ['id' => $id_candidato]
            );
            $documentos = self::filtrarDocumentosConArchivo($documentos ?: []);
            foreach ($documentos as &$d) {
                if (!empty($d['verificacion_fiscal_json'])) {
                    $dec = json_decode($d['verificacion_fiscal_json'], true);
                    $d['verificacion_fiscal'] = is_array($dec) ? $dec : null;
                } else {
                    $d['verificacion_fiscal'] = null;
                }
                if (!empty($d['verificacion_calidad_json'])) {
                    $dec = json_decode($d['verificacion_calidad_json'], true);
                    $d['verificacion_calidad'] = is_array($dec) ? $dec : null;
                } else {
                    $d['verificacion_calidad'] = null;
                }
            }
            unset($d);
            $row = $db->queryOne("SELECT ultima_verificacion_expediente FROM candidatos WHERE id = :id", ['id' => $id_candidato]);
            $verificacion = null;
            if ($row && !empty($row['ultima_verificacion_expediente'])) {
                $decoded = json_decode($row['ultima_verificacion_expediente'], true);
                $verificacion = is_array($decoded) ? $decoded : null;
            }
            return ['documentos' => $documentos, 'verificacion' => $verificacion];
        } catch (\Exception $e) {
            return ['documentos' => [], 'verificacion' => null];
        }
    }

    /**
     * Invalidar caché de listado documentación para un candidato (al subir/eliminar doc o actualizar verificación).
     */
    public static function invalidateDocumentacionCache($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        $cacheDir = defined('RAIZ') ? (RAIZ . '/storage/cache') : (__DIR__ . '/../storage/cache');
        $file = $cacheDir . '/doc_candidato_' . $id_candidato . '.json';
        if (is_file($file)) {
            @unlink($file);
        }
        if (function_exists('apcu_delete')) {
            @apcu_delete('doc_candidato_' . $id_candidato);
        }
    }

    /**
     * Marcar/desmarcar un documento como validado por Capital Humano.
     */
    public static function toggleValidadoDocumento($id_documento, $validado)
    {
        $id_documento = (int) $id_documento;
        $validado = $validado ? 1 : 0;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE candidato_documento SET validado = :v, fecha_validado = " . ($validado ? "NOW()" : "NULL") . " WHERE id = :id",
                ['id' => $id_documento, 'v' => $validado]
            );
            return self::resultado(true, $validado ? 'Documento validado.' : 'Validación retirada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    /**
     * Contar documentos validados vs total de un candidato.
     */
    public static function contarValidados($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return ['total' => 0, 'validados' => 0];
        }
        try {
            $db = new Database();
            $r = $db->queryOne(
                "SELECT COUNT(*) AS total, SUM(validado) AS validados FROM candidato_documento WHERE id_candidato = :id",
                ['id' => $id_candidato]
            );
            return ['total' => (int) ($r['total'] ?? 0), 'validados' => (int) ($r['validados'] ?? 0)];
        } catch (\Exception $e) {
            return ['total' => 0, 'validados' => 0];
        }
    }

    /**
     * Actualizar estatus del candidato.
     */
    public static function updateEstatus($id_candidato, $estatus)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($estatus) === '') {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD("UPDATE candidatos SET estatus = :e, fecha_actualizacion = NOW() WHERE id = :id", ['id' => $id_candidato, 'e' => trim($estatus)]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Cerrar proceso del candidato: guarda motivo, descripción y actualiza estatus a "Proceso cerrado".
     * Requiere que existan las columnas proceso_cerrado, motivo_cierre, descripcion_cierre, fecha_cierre.
     *
     * @param int $id_candidato
     * @param string $motivo Clave del motivo (ej. no_cubre_perfil, desistio, sin_info_a_tiempo, otro)
     * @param string|null $descripcion Descripción opcional
     * @return array { success, mensaje, datos?, error? }
     */
    public static function cerrarProceso($id_candidato, $motivo, $descripcion = null)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }
        $motivo = trim($motivo ?? '');
        if ($motivo === '') {
            return self::resultado(false, 'El motivo del cierre es obligatorio.', null);
        }
        $descripcion = trim($descripcion ?? '') ?: null;
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE candidatos SET proceso_cerrado = 1, motivo_cierre = :motivo, descripcion_cierre = :descripcion, fecha_cierre = NOW(), estatus = 'Proceso cerrado', fecha_actualizacion = NOW() WHERE id = :id",
                ['id' => $id_candidato, 'motivo' => $motivo, 'descripcion' => $descripcion]
            );
            return self::resultado(true, 'Proceso cerrado correctamente.', ['id' => $id_candidato]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cerrar el proceso.', null, $e->getMessage());
        }
    }

    /**
     * Tabla de auditoría cuando RRHH elimina un documento del expediente (motivo + correo al candidato).
     */
    private static function ensureTablaEliminacionDocumentoCandidato(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $db = new Database();
            $db->CRUD(
                'CREATE TABLE IF NOT EXISTS candidato_documento_eliminacion (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_candidato INT NOT NULL,
                    id_documento_eliminado INT NULL,
                    tipo_documento VARCHAR(255) NULL,
                    nombre_archivo VARCHAR(500) NULL,
                    comentario TEXT NOT NULL,
                    id_usuario_rrhh INT NULL,
                    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_candidato (id_candidato),
                    INDEX idx_fecha (fecha_registro)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        } catch (\Exception $e) {
        }
    }

    /**
     * @param int|null $id_usuario_rrhh ID en sesión de quien elimina (opcional)
     */
    public static function registrarEliminacionDocumentoCandidato(
        $id_candidato,
        $id_documento_eliminado,
        $tipo_documento,
        $nombre_archivo,
        $comentario,
        $id_usuario_rrhh = null
    ) {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($comentario ?? '') === '') {
            return self::resultado(false, 'Datos inválidos para registrar la eliminación.');
        }
        self::ensureTablaEliminacionDocumentoCandidato();
        try {
            $db = new Database();
            $db->CRUD(
                'INSERT INTO candidato_documento_eliminacion
                    (id_candidato, id_documento_eliminado, tipo_documento, nombre_archivo, comentario, id_usuario_rrhh, fecha_registro)
                 VALUES (:idc, :idd, :tipo, :nom, :com, :usr, NOW())',
                [
                    'idc' => $id_candidato,
                    'idd' => $id_documento_eliminado > 0 ? $id_documento_eliminado : null,
                    'tipo' => $tipo_documento !== null && $tipo_documento !== '' ? substr(trim((string) $tipo_documento), 0, 255) : null,
                    'nom' => $nombre_archivo !== null && $nombre_archivo !== '' ? substr(trim((string) $nombre_archivo), 0, 500) : null,
                    'com' => trim((string) $comentario),
                    'usr' => $id_usuario_rrhh !== null && (int) $id_usuario_rrhh > 0 ? (int) $id_usuario_rrhh : null,
                ]
            );
            return self::resultado(true, 'Registro guardado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo guardar el motivo de eliminación.', null, $e->getMessage());
        }
    }
}
