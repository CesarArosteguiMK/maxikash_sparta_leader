<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

$db = new Core\Database();
$hasExpira = false;
try {
    $row = $db->queryOne("
        SELECT COUNT(*) AS c
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'candidato_documento_token'
          AND COLUMN_NAME = 'expira'
    ");
    $hasExpira = (int)($row['c'] ?? 0) > 0;
} catch (Throwable $e) {
    $hasExpira = false;
}
$expiraSelect = $hasExpira ? 't.expira' : 'NULL AS expira';
$expiraGroup = $hasExpira ? ', t.expira' : '';
$sql = "
    SELECT
        c.id,
        c.nombres,
        c.apellidop,
        c.apellidom,
        c.email,
        t.token,
        {$expiraSelect},
        COUNT(d.id) AS docs
    FROM candidatos c
    LEFT JOIN candidato_documento_token t ON t.id_candidato = c.id
    LEFT JOIN candidato_documento d ON d.id_candidato = c.id
    WHERE UPPER(CONCAT_WS(' ', c.nombres, c.apellidop, c.apellidom, c.email)) LIKE '%PRUEBA%'
       OR UPPER(CONCAT_WS(' ', c.nombres, c.apellidop, c.apellidom, c.email)) LIKE '%TEST%'
       OR UPPER(CONCAT_WS(' ', c.nombres, c.apellidop, c.apellidom, c.email)) LIKE '%SIMUL%'
    GROUP BY c.id, c.nombres, c.apellidop, c.apellidom, c.email, t.token{$expiraGroup}
    ORDER BY c.id DESC
    LIMIT 25
";

$rows = $db->queryAll($sql);
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
