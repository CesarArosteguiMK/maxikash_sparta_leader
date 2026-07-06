<?php

declare(strict_types=1);

use Core\Database;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

ini_set('memory_limit', '768M');
set_time_limit(0);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/backend/config/config.php';
require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

const FURIA_EMPRESA_ID = 2;
const FURIA_EMPRESA_CLAVE = 'FURIA_MOTOS';
const FURIA_EMPLOYEE_PREFIX = 'FM-';
const HEADER_ROW = 11;

function usage(): void
{
    fwrite(STDERR, "Uso: php scripts/importar_furia_motos_pensionamax.php <archivo.xlsx> [--apply]\n");
    fwrite(STDERR, "Por defecto corre en dry-run y no escribe en la base.\n");
}

function text_value(mixed $value): string
{
    if ($value instanceof RichText) {
        $value = $value->getPlainText();
    }
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    $text = trim((string)($value ?? ''));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function norm_key(mixed $value): string
{
    $text = text_value($value);
    if ($text === '') {
        return '';
    }
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace('&', 'Y', $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function clean_text(mixed $value, int $max = 255): string
{
    $text = text_value($value);
    if (str_starts_with($text, '=')) {
        return '';
    }
    $key = norm_key($text);
    if ($key === '' || in_array($key, ['N A', 'NA', 'S N', 'SN', 'NO APLICA', 'NULL', 'NONE'], true)) {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') > $max) {
        return mb_substr($text, 0, $max, 'UTF-8');
    }
    return $text;
}

function canonical_org_text(mixed $value): string
{
    $text = clean_text($value, 180);
    return $text === '' ? '' : mb_strtoupper($text, 'UTF-8');
}

function parse_date_value(mixed $value): ?string
{
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    if (is_numeric($value)) {
        try {
            return ExcelDate::excelToDateTimeObject((float)$value)->format('Y-m-d');
        } catch (Throwable $e) {
            return null;
        }
    }
    $text = clean_text($value, 40);
    if ($text === '') {
        return null;
    }
    foreach (['Y-m-d H:i:s', 'Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
        $dt = DateTime::createFromFormat($format, $text);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }
    $ts = strtotime($text);
    return $ts ? date('Y-m-d', $ts) : null;
}

function header_map(Worksheet $ws): array
{
    $map = [];
    $max = Coordinate::columnIndexFromString($ws->getHighestColumn());
    for ($col = 1; $col <= $max; $col++) {
        $key = norm_key($ws->getCell(Coordinate::stringFromColumnIndex($col) . HEADER_ROW)->getValue());
        if ($key !== '') {
            $map[$key] = $col;
        }
    }
    return $map;
}

function col_value(Worksheet $ws, array $header, int $row, array|string $names): mixed
{
    foreach ((array)$names as $name) {
        $key = norm_key($name);
        if (isset($header[$key])) {
            return $ws->getCell(Coordinate::stringFromColumnIndex($header[$key]) . $row)->getValue();
        }
    }
    return null;
}

function split_names(string $names): array
{
    $parts = preg_split('/\s+/u', trim($names)) ?: [];
    if (!$parts) {
        return ['', ''];
    }
    $first = array_shift($parts);
    return [$first, trim(implode(' ', $parts))];
}

function date_parts(?string $date): array
{
    if (!$date) {
        return [null, null, null];
    }
    [$year, $month, $day] = array_map('intval', explode('-', $date));
    return [$year ?: null, $month ?: null, $day ?: null];
}

function table_index_exists(Database $db, string $table, string $index): bool
{
    $row = $db->queryOne(
        "SELECT 1 AS ok
           FROM information_schema.STATISTICS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND INDEX_NAME = :idx
          LIMIT 1",
        ['table' => $table, 'idx' => $index]
    );
    return !empty($row);
}

function ensure_empresa_catalog_schema(Database $db, bool $apply, array &$stats): void
{
    $empresa = $db->queryOne("SELECT id FROM __SPARTA_SECRET_REDACTED__.rrhh_empresas WHERE id = :id LIMIT 1", ['id' => FURIA_EMPRESA_ID]);
    if (!$empresa) {
        throw new RuntimeException('No existe rrhh_empresas.id=2 para Furia Motos. Ejecuta primero migration_rrhh_empresas_fase1.php.');
    }

    if (table_index_exists($db, 'direcciones_organizacion', 'ux_direcciones_pais_nombre')) {
        $stats['schema_ajustes'][] = 'Cambiar unique direcciones_organizacion de (id_pais,nombre) a (id_empresa,id_pais,nombre)';
        if ($apply) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.direcciones_organizacion DROP INDEX ux_direcciones_pais_nombre");
        }
    }

    if (!table_index_exists($db, 'direcciones_organizacion', 'ux_direcciones_empresa_pais_nombre')) {
        $stats['schema_ajustes'][] = 'Crear unique direcciones_organizacion (id_empresa,id_pais,nombre)';
        if ($apply) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.direcciones_organizacion ADD UNIQUE KEY ux_direcciones_empresa_pais_nombre (id_empresa, id_pais, nombre)");
        }
    }
}

function find_id_by_name(Database $db, string $sql, array $params, string $name): int
{
    $target = norm_key($name);
    foreach ($db->queryAll($sql, $params) as $row) {
        if (norm_key($row['nombre'] ?? '') === $target) {
            return (int)($row['id'] ?? 0);
        }
    }
    return 0;
}

function unique_puesto_clave(Database $db, string $puesto, int $departamentoId): string
{
    $base = strtolower(norm_key($puesto));
    $base = preg_replace('/[^a-z0-9]+/', '-', $base) ?: 'puesto';
    $base = trim($base, '-');
    $base = 'furia-' . $base . '-' . $departamentoId;
    $clave = substr($base, 0, 50);
    for ($i = 2; ; $i++) {
        $exists = $db->queryOne("SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto WHERE clave = :clave LIMIT 1", ['clave' => $clave]);
        if (!$exists) {
            return $clave;
        }
        $suffix = '-' . $i;
        $clave = substr($base, 0, 50 - strlen($suffix)) . $suffix;
    }
}

function puesto_level(string $puesto): int
{
    return match (norm_key($puesto)) {
        'GERENTE' => 60,
        'SUBGERENTE' => 50,
        'SUPERVISOR' => 40,
        'ESPECIALISTA' => 30,
        'ASESOR', 'GESTOR' => 20,
        default => 10,
    };
}

function ensure_catalog(Database $db, array $record, bool $apply, array &$stats): array
{
    $idPais = 1;
    $direccion = $record['direccion'] ?: 'SIN DIRECCION';
    $area = $record['area'] ?: 'SIN AREA';
    $departamento = $record['departamento'] ?: 'SIN DEPARTAMENTO';
    $puesto = $record['puesto'] ?: 'SIN PUESTO';

    $idDireccion = find_id_by_name(
        $db,
        "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion WHERE id_empresa = :id_empresa AND id_pais = :id_pais",
        ['id_empresa' => FURIA_EMPRESA_ID, 'id_pais' => $idPais],
        $direccion
    );
    if ($idDireccion <= 0) {
        $stats['catalogo_direcciones_crear']++;
        if ($apply) {
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.direcciones_organizacion (id_empresa, nombre, id_pais, activo)
                 VALUES (:id_empresa, :nombre, :id_pais, 1)",
                ['id_empresa' => FURIA_EMPRESA_ID, 'nombre' => $direccion, 'id_pais' => $idPais]
            );
            $idDireccion = $db->lastInsertId();
        }
    }

    $idArea = find_id_by_name(
        $db,
        "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional WHERE id_empresa = :id_empresa AND id_pais = :id_pais",
        ['id_empresa' => FURIA_EMPRESA_ID, 'id_pais' => $idPais],
        $area
    );
    if ($idArea <= 0) {
        $stats['catalogo_areas_crear']++;
        if ($apply) {
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.departamento_organizacional (id_empresa, nombre, activo, id_pais)
                 VALUES (:id_empresa, :nombre, 1, :id_pais)",
                ['id_empresa' => FURIA_EMPRESA_ID, 'nombre' => $area, 'id_pais' => $idPais]
            );
            $idArea = $db->lastInsertId();
        }
    }

    if ($apply && $idDireccion > 0 && $idArea > 0) {
        $link = $db->queryOne(
            "SELECT id FROM __SPARTA_SECRET_REDACTED__.asigna_direcciones WHERE id_departamento_organizacional = :id_area LIMIT 1",
            ['id_area' => $idArea]
        );
        if ($link) {
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.asigna_direcciones SET id_direccion = :id_direccion, activo = 1 WHERE id = :id",
                ['id_direccion' => $idDireccion, 'id' => (int)$link['id']]
            );
        } else {
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
                 VALUES (:id_direccion, :id_area, 1)",
                ['id_direccion' => $idDireccion, 'id_area' => $idArea]
            );
        }
    }

    $idDepartamento = find_id_by_name(
        $db,
        "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.departamento WHERE id_empresa = :id_empresa AND id_departamento_organizacional = :id_area",
        ['id_empresa' => FURIA_EMPRESA_ID, 'id_area' => $idArea],
        $departamento
    );
    if ($idDepartamento <= 0) {
        $stats['catalogo_departamentos_crear']++;
        if ($apply) {
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.departamento (id_empresa, nombre, activo, id_pais, id_departamento_organizacional)
                 VALUES (:id_empresa, :nombre, 1, :id_pais, :id_area)",
                ['id_empresa' => FURIA_EMPRESA_ID, 'nombre' => $departamento, 'id_pais' => $idPais, 'id_area' => $idArea]
            );
            $idDepartamento = $db->lastInsertId();
        }
    }

    $idPuesto = find_id_by_name(
        $db,
        "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.puesto WHERE id_empresa = :id_empresa AND departamento_id = :id_departamento",
        ['id_empresa' => FURIA_EMPRESA_ID, 'id_departamento' => $idDepartamento],
        $puesto
    );
    if ($idPuesto <= 0) {
        $stats['catalogo_puestos_crear']++;
        if ($apply) {
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.puesto (id_empresa, clave, nombre, nivel, activo, departamento_id, es_jefe, descripcion)
                 VALUES (:id_empresa, :clave, :nombre, :nivel, 1, :id_departamento, :es_jefe, NULL)",
                [
                    'id_empresa' => FURIA_EMPRESA_ID,
                    'clave' => unique_puesto_clave($db, $puesto, $idDepartamento),
                    'nombre' => $puesto,
                    'nivel' => puesto_level($puesto),
                    'id_departamento' => $idDepartamento,
                    'es_jefe' => puesto_level($puesto) >= 40 ? 1 : 0,
                ]
            );
            $idPuesto = $db->lastInsertId();
        }
    }

    return [
        'id_direccion' => $idDireccion,
        'id_area' => $idArea,
        'id_departamento' => $idDepartamento,
        'id_puesto' => $idPuesto,
    ];
}

function record_from_sheet(Worksheet $ws, array $header, int $row): ?array
{
    $item = clean_text(col_value($ws, $header, $row, 'ITEM'), 20);
    $namesRaw = clean_text(col_value($ws, $header, $row, 'NOMBRE (S)'), 120);
    $apellidoP = clean_text(col_value($ws, $header, $row, 'A. PATERNO'), 100);
    $apellidoM = clean_text(col_value($ws, $header, $row, 'A. MATERNO'), 100);
    $fullName = trim($namesRaw . ' ' . $apellidoP . ' ' . $apellidoM);
    if ($item === '' || $fullName === '') {
        return null;
    }

    [$firstName, $secondName] = split_names($namesRaw);
    $codigo = clean_text(col_value($ws, $header, $row, 'CODIGO CONTPAC'), 30);
    $employeeNumber = FURIA_EMPLOYEE_PREFIX . str_pad((string)(int)$item, 4, '0', STR_PAD_LEFT);
    $fechaNacimiento = parse_date_value(col_value($ws, $header, $row, 'FECHA DE NACIMIENTO'));
    [$anio, $mes, $dia] = date_parts($fechaNacimiento);

    return [
        'excel_row' => $row,
        'item' => $item,
        'numero_empleado' => $employeeNumber,
        'codigo_contpac' => $codigo !== '' ? $codigo : null,
        'nombre_completo' => $fullName,
        'nombres' => $firstName,
        'segundo_nombre' => $secondName,
        'apellidop' => $apellidoP,
        'apellidom' => $apellidoM,
        'fecha_ingreso' => parse_date_value(col_value($ws, $header, $row, 'FECHA DE INGRESO')),
        'puesto' => canonical_org_text(col_value($ws, $header, $row, 'PUESTO')),
        'departamento' => canonical_org_text(col_value($ws, $header, $row, 'DEPARTAMENTO')),
        'area' => canonical_org_text(col_value($ws, $header, $row, 'AREA')),
        'direccion' => canonical_org_text(col_value($ws, $header, $row, 'DIRECCION ORGANIZACIONAL')),
        'ubicacion_laboral' => clean_text(col_value($ws, $header, $row, 'UBICACION LABORAL'), 180),
        'municipio_laboral' => clean_text(col_value($ws, $header, $row, 'MUNICIPIO'), 180),
        'jefe_directo_texto' => clean_text(col_value($ws, $header, $row, 'JEFE DIRECTO'), 220),
        'curp' => strtoupper(clean_text(col_value($ws, $header, $row, 'CURP'), 18)),
        'nss' => clean_text(col_value($ws, $header, $row, 'NSS'), 20),
        'rfc' => strtoupper(clean_text(col_value($ws, $header, $row, 'RFC'), 20)),
        'entidad_federativa_rfc' => clean_text(col_value($ws, $header, $row, 'ENTIDAD FEDERATIVA / RFC'), 120),
        'codigo_postal' => clean_text(col_value($ws, $header, $row, 'CP'), 12),
        'anio' => $anio,
        'mes' => $mes,
        'dia' => $dia,
        'fecha_nacimiento' => $fechaNacimiento,
        'sexo' => match (norm_key(col_value($ws, $header, $row, 'SEXO'))) {
            'F', 'FEMENINO' => 'Femenino',
            'M', 'MASCULINO' => 'Masculino',
            default => clean_text(col_value($ws, $header, $row, 'SEXO'), 20),
        },
        'telefono' => preg_replace('/\D+/', '', clean_text(col_value($ws, $header, $row, 'NO. TELEFONICO'), 30)) ?: '',
        'registro_patronal' => clean_text(col_value($ws, $header, $row, 'REGISTRO PATRONAL'), 120),
    ];
}

function find_person(Database $db, array $record): ?array
{
    $queries = [];
    if ($record['curp'] !== '') {
        $queries[] = ["SELECT * FROM __SPARTA_SECRET_REDACTED__.persona WHERE id_empresa = :id_empresa AND curp = :curp LIMIT 1", ['id_empresa' => FURIA_EMPRESA_ID, 'curp' => $record['curp']]];
    }
    if ($record['rfc'] !== '') {
        $queries[] = ["SELECT p.* FROM __SPARTA_SECRET_REDACTED__.persona p LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh r ON r.id_persona = p.id WHERE p.id_empresa = :id_empresa AND (p.rfc = :rfc OR r.rfc = :rfc) LIMIT 1", ['id_empresa' => FURIA_EMPRESA_ID, 'rfc' => $record['rfc']]];
    }
    if ($record['nss'] !== '') {
        $queries[] = ["SELECT p.* FROM __SPARTA_SECRET_REDACTED__.persona p INNER JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh r ON r.id_persona = p.id WHERE p.id_empresa = :id_empresa AND r.nss = :nss LIMIT 1", ['id_empresa' => FURIA_EMPRESA_ID, 'nss' => $record['nss']]];
    }
    if ($record['codigo_contpac']) {
        $queries[] = ["SELECT * FROM __SPARTA_SECRET_REDACTED__.persona WHERE id_empresa = :id_empresa AND codigo_contpac = :codigo LIMIT 1", ['id_empresa' => FURIA_EMPRESA_ID, 'codigo' => $record['codigo_contpac']]];
    }
    $queries[] = ["SELECT * FROM __SPARTA_SECRET_REDACTED__.persona WHERE id_empresa = :id_empresa AND numero_empleado = :numero LIMIT 1", ['id_empresa' => FURIA_EMPRESA_ID, 'numero' => $record['numero_empleado']]];

    foreach ($queries as [$sql, $params]) {
        $row = $db->queryOne($sql, $params);
        if ($row) {
            return $row;
        }
    }
    return null;
}

function upsert_person(Database $db, array $record, array $catalog, bool $apply, array &$stats): int
{
    $existing = find_person($db, $record);
    $now = date('Y-m-d H:i:s');
    $idPersona = (int)($existing['id'] ?? 0);

    if ($idPersona > 0) {
        $stats['personas_actualizar']++;
        if ($apply) {
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.persona
                    SET nombres = :nombres,
                        segundo_nombre = :segundo_nombre,
                        apellidop = :apellidop,
                        apellidom = :apellidom,
                        numero_empleado = :numero_empleado,
                        codigo_contpac = :codigo_contpac,
                        curp = :curp,
                        rfc = :rfc,
                        telefono_uno = :telefono,
                        estatus = 'Activo',
                        fecha_ingreso = :fecha_ingreso,
                        id_empresa = :id_empresa,
                        id_pais = 1,
                        codigo_postal = :codigo_postal
                  WHERE id = :id",
                [
                    'nombres' => $record['nombres'],
                    'segundo_nombre' => $record['segundo_nombre'],
                    'apellidop' => $record['apellidop'],
                    'apellidom' => $record['apellidom'],
                    'numero_empleado' => $record['numero_empleado'],
                    'codigo_contpac' => $record['codigo_contpac'],
                    'curp' => $record['curp'] ?: null,
                    'rfc' => $record['rfc'] ?: null,
                    'telefono' => $record['telefono'],
                    'fecha_ingreso' => $record['fecha_ingreso'],
                    'id_empresa' => FURIA_EMPRESA_ID,
                    'codigo_postal' => $record['codigo_postal'],
                    'id' => $idPersona,
                ]
            );
        }
    } else {
        $stats['personas_insertar']++;
        if ($apply) {
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.persona
                    (id_empresa, nombres, segundo_nombre, apellidop, apellidom, numero_empleado, codigo_contpac,
                     correo, telefono_uno, telefono_dos, estatus, user_name, password, fecha_ingreso, fecha_registro,
                     id_pais, codigo_postal, curp, rfc)
                 VALUES
                    (:id_empresa, :nombres, :segundo_nombre, :apellidop, :apellidom, :numero_empleado, :codigo_contpac,
                     '', :telefono, '', 'Activo', :user_name, :password, :fecha_ingreso, :fecha_registro,
                     1, :codigo_postal, :curp, :rfc)",
                [
                    'id_empresa' => FURIA_EMPRESA_ID,
                    'nombres' => $record['nombres'],
                    'segundo_nombre' => $record['segundo_nombre'],
                    'apellidop' => $record['apellidop'],
                    'apellidom' => $record['apellidom'],
                    'numero_empleado' => $record['numero_empleado'],
                    'codigo_contpac' => $record['codigo_contpac'],
                    'telefono' => $record['telefono'],
                    'user_name' => 'FURIA' . str_pad((string)(int)$record['item'], 4, '0', STR_PAD_LEFT),
                    'password' => 'FuriaMotos2026',
                    'fecha_ingreso' => $record['fecha_ingreso'],
                    'fecha_registro' => $now,
                    'codigo_postal' => $record['codigo_postal'],
                    'curp' => $record['curp'] ?: null,
                    'rfc' => $record['rfc'] ?: null,
                ]
            );
            $idPersona = $db->lastInsertId();
        }
    }

    if ($apply && $idPersona <= 0) {
        throw new RuntimeException('No se obtuvo id_persona para ' . $record['nombre_completo']);
    }

    if ($apply) {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh
                (id_persona, registro_patronal, codigo_contpaq, fecha_imss_alta, id_departamento, id_area, id_puesto,
                 puesto_texto, departamento_texto, area_texto, direccion_organizacional, ubicacion_laboral,
                 municipio_laboral, jefe_directo_texto, rfc, nss, entidad_federativa_rfc, anio, mes, dia,
                 fecha_nacimiento, sexo, observaciones)
             VALUES
                (:id_persona, :registro_patronal, :codigo_contpaq, :fecha_imss_alta, :id_departamento, :id_area, :id_puesto,
                 :puesto_texto, :departamento_texto, :area_texto, :direccion_organizacional, :ubicacion_laboral,
                 :municipio_laboral, :jefe_directo_texto, :rfc, :nss, :entidad_federativa_rfc, :anio, :mes, :dia,
                 :fecha_nacimiento, :sexo, :observaciones)
             ON DUPLICATE KEY UPDATE
                 registro_patronal = VALUES(registro_patronal),
                 codigo_contpaq = VALUES(codigo_contpaq),
                 fecha_imss_alta = VALUES(fecha_imss_alta),
                 id_departamento = VALUES(id_departamento),
                 id_area = VALUES(id_area),
                 id_puesto = VALUES(id_puesto),
                 puesto_texto = VALUES(puesto_texto),
                 departamento_texto = VALUES(departamento_texto),
                 area_texto = VALUES(area_texto),
                 direccion_organizacional = VALUES(direccion_organizacional),
                 ubicacion_laboral = VALUES(ubicacion_laboral),
                 municipio_laboral = VALUES(municipio_laboral),
                 jefe_directo_texto = VALUES(jefe_directo_texto),
                 rfc = VALUES(rfc),
                 nss = VALUES(nss),
                 entidad_federativa_rfc = VALUES(entidad_federativa_rfc),
                 anio = VALUES(anio),
                 mes = VALUES(mes),
                 dia = VALUES(dia),
                 fecha_nacimiento = VALUES(fecha_nacimiento),
                 sexo = VALUES(sexo),
                 observaciones = VALUES(observaciones),
                 updated_at = NOW()",
            [
                'id_persona' => $idPersona,
                'registro_patronal' => $record['registro_patronal'],
                'codigo_contpaq' => $record['codigo_contpac'],
                'fecha_imss_alta' => $record['fecha_ingreso'],
                'id_departamento' => $catalog['id_departamento'],
                'id_area' => $catalog['id_area'],
                'id_puesto' => $catalog['id_puesto'],
                'puesto_texto' => $record['puesto'],
                'departamento_texto' => $record['departamento'],
                'area_texto' => $record['area'],
                'direccion_organizacional' => $record['direccion'],
                'ubicacion_laboral' => $record['ubicacion_laboral'],
                'municipio_laboral' => $record['municipio_laboral'],
                'jefe_directo_texto' => $record['jefe_directo_texto'],
                'rfc' => $record['rfc'],
                'nss' => $record['nss'],
                'entidad_federativa_rfc' => $record['entidad_federativa_rfc'],
                'anio' => $record['anio'],
                'mes' => $record['mes'],
                'dia' => $record['dia'],
                'fecha_nacimiento' => $record['fecha_nacimiento'],
                'sexo' => $record['sexo'],
                'observaciones' => 'Importado/actualizado desde activos furia motos pensionamax.xlsx',
            ]
        );

        $samePosition = $db->queryOne(
            "SELECT id FROM __SPARTA_SECRET_REDACTED__.asigna_puesto WHERE id_persona = :id_persona AND id_puesto = :id_puesto AND COALESCE(activo, 1) = 1 LIMIT 1",
            ['id_persona' => $idPersona, 'id_puesto' => $catalog['id_puesto']]
        );
        if (!$samePosition) {
            $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.asigna_puesto SET activo = 0 WHERE id_persona = :id_persona AND COALESCE(activo, 1) = 1", ['id_persona' => $idPersona]);
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto (id, id_persona, id_puesto, fecha_asignacion, activo)
                 VALUES (DEFAULT, :id_persona, :id_puesto, NOW(), 1)",
                ['id_persona' => $idPersona, 'id_puesto' => $catalog['id_puesto']]
            );
        }

        if ($record['telefono'] !== '') {
            $tel = $db->queryOne(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.telefonos_persona WHERE id_persona = :id_persona AND numero = :numero LIMIT 1",
                ['id_persona' => $idPersona, 'numero' => $record['telefono']]
            );
            if (!$tel) {
                $db->CRUD(
                    "INSERT INTO __SPARTA_SECRET_REDACTED__.telefonos_persona (id_persona, numero, tipo, estatus) VALUES (:id_persona, :numero, 'Personal', 'Activo')",
                    ['id_persona' => $idPersona, 'numero' => $record['telefono']]
                );
            }
        }
    }

    return $idPersona;
}

function validate_records(array $records): array
{
    $issues = [];
    $seen = ['curp' => [], 'rfc' => [], 'nss' => [], 'numero' => []];
    foreach ($records as $record) {
        foreach (['curp', 'rfc', 'nss'] as $field) {
            if ($record[$field] === '') {
                $issues[] = "Fila {$record['excel_row']} {$record['nombre_completo']}: falta {$field}";
                continue;
            }
            if (isset($seen[$field][$record[$field]])) {
                $issues[] = "Duplicado en Excel {$field}: {$record[$field]} ({$seen[$field][$record[$field]]} / {$record['nombre_completo']})";
            }
            $seen[$field][$record[$field]] = $record['nombre_completo'];
        }
        if (isset($seen['numero'][$record['numero_empleado']])) {
            $issues[] = "Duplicado en Excel numero_empleado: {$record['numero_empleado']}";
        }
        $seen['numero'][$record['numero_empleado']] = $record['nombre_completo'];
    }
    return $issues;
}

$args = $_SERVER['argv'];
array_shift($args);
$apply = false;
$file = null;
foreach ($args as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif ($file === null) {
        $file = $arg;
    }
}

if (!$file || !is_file($file)) {
    usage();
    exit(2);
}

$db = new Database();
$stats = [
    'modo' => $apply ? 'APPLY' : 'DRY-RUN',
    'filas_excel' => 0,
    'registros_validos' => 0,
    'personas_insertar' => 0,
    'personas_actualizar' => 0,
    'catalogo_direcciones_crear' => 0,
    'catalogo_areas_crear' => 0,
    'catalogo_departamentos_crear' => 0,
    'catalogo_puestos_crear' => 0,
    'schema_ajustes' => [],
    'sin_banco_en_excel' => 0,
    'sin_beneficiarios_en_excel' => 0,
    'sin_contactos_emergencia_en_excel' => 0,
];

$reader = IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);
$ws = $spreadsheet->getSheetByName('Hoja1') ?: $spreadsheet->getActiveSheet();
$header = header_map($ws);
$highestRow = min($ws->getHighestRow(), 300);

$records = [];
$blankRows = 0;
for ($row = HEADER_ROW + 1; $row <= $highestRow; $row++) {
    $record = record_from_sheet($ws, $header, $row);
    if (!$record) {
        $blankRows++;
        if ($blankRows >= 25 && count($records) > 0) {
            break;
        }
        continue;
    }
    $blankRows = 0;
    $stats['filas_excel']++;
    $records[] = $record;
}

$stats['registros_validos'] = count($records);
$stats['sin_banco_en_excel'] = count($records);
$stats['sin_beneficiarios_en_excel'] = count($records);
$stats['sin_contactos_emergencia_en_excel'] = count($records);

$issues = validate_records($records);
if ($issues) {
    echo "Modo: {$stats['modo']}\n";
    echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "Problemas detectados:\n";
    foreach ($issues as $issue) {
        echo "- {$issue}\n";
    }
    exit(3);
}

ensure_empresa_catalog_schema($db, $apply, $stats);

if ($apply) {
    $db->beginTransaction();
}

try {
    foreach ($records as $record) {
        $catalog = ensure_catalog($db, $record, $apply, $stats);
        upsert_person($db, $record, $catalog, $apply, $stats);
    }

    if ($apply) {
        $db->commit();
    }
} catch (Throwable $e) {
    if ($apply && $db->inTransaction()) {
        $db->rollback();
    }
    throw $e;
}

echo "Modo: {$stats['modo']}\n";
echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
