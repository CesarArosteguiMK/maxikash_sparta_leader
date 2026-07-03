<?php
declare(strict_types=1);
$pdo = new PDO('mysql:host=__SPARTA_HOST_REDACTED__;port=3306;dbname=__SPARTA_SECRET_REDACTED__;charset=utf8mb4', '__SPARTA_SECRET_REDACTED__', '__SPARTA_PASSWORD_REDACTED__', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function out($title, $rows) {
    echo "=== $title (" . count($rows) . ") ===" . PHP_EOL;
    foreach ($rows as $r) echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

$sinCurp = $pdo->query("
    SELECT p.id, CONCAT_WS(' ',p.nombres,p.segundo_nombre,p.apellidop,p.apellidom) nombre,
           p.estatus, p.numero_empleado, p.codigo_contpac, p.curp, p.rfc,
           p.correo, p.telefono_uno, p.codigo_postal,
           rr.nss, rr.registro_patronal, rr.codigo_contpaq, rr.fecha_imss_alta,
           (SELECT COUNT(*) FROM persona_cuenta_bancaria b WHERE b.id_persona=p.id) bancos,
           (SELECT COUNT(*) FROM persona_beneficiario_fallecimiento bf WHERE bf.id_persona=p.id) beneficiarios,
           (SELECT COUNT(*) FROM contacto_persona_emergencia ce WHERE ce.id_persona=p.id) contactos,
           (SELECT COUNT(*) FROM correos_persona c WHERE c.id_persona=p.id) correos_extra,
           (SELECT COUNT(*) FROM telefonos_persona t WHERE t.id_persona=p.id) telefonos_extra
    FROM persona p
    LEFT JOIN persona_datos_rrhh rr ON rr.id_persona=p.id
    WHERE p.curp IS NULL OR p.curp=''
    ORDER BY p.estatus DESC, p.id
")->fetchAll();

$sinCurpRfc = array_values(array_filter($sinCurp, fn($r) => trim((string)$r['rfc']) === ''));
$sinCurpRfcNss = array_values(array_filter($sinCurp, fn($r) => trim((string)$r['rfc']) === '' && trim((string)$r['nss']) === ''));

$anomalias = [];
$anomalias['numero_empleado_no_numerico'] = $pdo->query("
    SELECT id, CONCAT_WS(' ',nombres,segundo_nombre,apellidop,apellidom) nombre, estatus, numero_empleado, codigo_contpac
    FROM persona
    WHERE numero_empleado IS NOT NULL AND numero_empleado<>'' AND numero_empleado NOT REGEXP '^[0-9]+$'
    ORDER BY id
")->fetchAll();
$anomalias['codigo_contpac_no_numerico'] = $pdo->query("
    SELECT id, CONCAT_WS(' ',nombres,segundo_nombre,apellidop,apellidom) nombre, estatus, numero_empleado, codigo_contpac
    FROM persona
    WHERE codigo_contpac IS NOT NULL AND codigo_contpac<>'' AND codigo_contpac NOT REGEXP '^[0-9]+$'
    ORDER BY id
")->fetchAll();
$anomalias['correo_invalido'] = $pdo->query("
    SELECT id, CONCAT_WS(' ',nombres,segundo_nombre,apellidop,apellidom) nombre, estatus, correo
    FROM persona
    WHERE correo IS NOT NULL AND correo<>'' AND correo NOT REGEXP '^[^@[:space:]]+@[^@[:space:]]+\\.[^@[:space:]]+$'
    ORDER BY id
")->fetchAll();
$anomalias['telefono_raro'] = $pdo->query("
    SELECT id, CONCAT_WS(' ',nombres,segundo_nombre,apellidop,apellidom) nombre, estatus, telefono_uno
    FROM persona
    WHERE telefono_uno IS NOT NULL AND telefono_uno<>'' AND telefono_uno NOT REGEXP '^[0-9]{10,13}$'
    ORDER BY id
")->fetchAll();
$anomalias['sin_rrhh'] = $pdo->query("
    SELECT p.id, CONCAT_WS(' ',p.nombres,p.segundo_nombre,p.apellidop,p.apellidom) nombre, p.estatus, p.curp, p.rfc
    FROM persona p
    LEFT JOIN persona_datos_rrhh rr ON rr.id_persona=p.id
    WHERE rr.id_persona IS NULL
    ORDER BY p.id
")->fetchAll();
$anomalias['duplicados_nombre'] = $pdo->query("
    SELECT nombre, COUNT(*) c, GROUP_CONCAT(id ORDER BY id SEPARATOR ',') ids
    FROM (
      SELECT id, UPPER(TRIM(REGEXP_REPLACE(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom), '[[:space:]]+', ' '))) nombre
      FROM persona
    ) x
    GROUP BY nombre
    HAVING c > 1
    ORDER BY c DESC, nombre
")->fetchAll();

echo json_encode([
    'resumen' => [
        'sin_curp' => count($sinCurp),
        'sin_curp_y_sin_rfc' => count($sinCurpRfc),
        'sin_curp_sin_rfc_sin_nss' => count($sinCurpRfcNss),
        'numero_empleado_no_numerico' => count($anomalias['numero_empleado_no_numerico']),
        'codigo_contpac_no_numerico' => count($anomalias['codigo_contpac_no_numerico']),
        'correo_invalido' => count($anomalias['correo_invalido']),
        'telefono_raro' => count($anomalias['telefono_raro']),
        'sin_rrhh' => count($anomalias['sin_rrhh']),
        'duplicados_nombre' => count($anomalias['duplicados_nombre']),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;

out('SIN_CURP', $sinCurp);
out('SIN_CURP_Y_SIN_RFC', $sinCurpRfc);
out('SIN_CURP_SIN_RFC_SIN_NSS', $sinCurpRfcNss);
foreach ($anomalias as $k => $rows) out(strtoupper($k), $rows);
