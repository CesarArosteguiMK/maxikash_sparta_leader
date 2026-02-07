<?php
/**
 * Test rápido del endpoint GET /api/analitica/interpretacion?id_credito=NNN
 * Valida que la respuesta tenga el schema esperado (overall_confidence 0..100, sections, status, etc.).
 *
 * Uso desde CLI (simulando sesión):
 *   php backend/tests/test_interpretacion_endpoint.php [id_credito]
 * O con curl (requiere sesión activa o cookie):
 *   curl -s "http://localhost/api/analitica/interpretacion?id_credito=1600" -b "PHPSESSID=..."
 */

$idCredito = isset($argv[1]) ? (int) $argv[1] : 1600;
$baseUrl = getenv('BASE_URL') ?: 'http://localhost';

// Si se ejecuta por CLI sin sesión, el endpoint puede devolver 302 a Login; entonces usamos curl con -L o probamos solo el schema
$url = $baseUrl . '/api/analitica/interpretacion?id_credito=' . $idCredito . '&id_ticket=0';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 90);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "HTTP $httpCode (si 302, inicia sesión en el navegador y usa la misma URL con cookie PHPSESSID)\n";
    exit(1);
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Respuesta no es JSON válido\n";
    exit(1);
}

$ok = true;

if (!isset($data['success']) || !$data['success']) {
    echo "Campo success debe ser true\n";
    $ok = false;
}

$conf = $data['overall_confidence'] ?? null;
if ($conf === null || !is_numeric($conf) || $conf < 0 || $conf > 100) {
    echo "overall_confidence debe ser número 0..100, obtuvo: " . var_export($conf, true) . "\n";
    $ok = false;
}

if (!isset($data['summary']) || !is_string($data['summary'])) {
    echo "summary debe ser string\n";
    $ok = false;
}

$sections = $data['sections'] ?? [];
if (!is_array($sections)) {
    echo "sections debe ser objeto\n";
    $ok = false;
} else {
    foreach (['cliente', 'gestion', 'pagos'] as $key) {
        if (!isset($sections[$key]) || !is_array($sections[$key])) {
            echo "sections.$key debe existir y ser objeto\n";
            $ok = false;
        } elseif (!isset($sections[$key]['state']) || !isset($sections[$key]['pct']) || !isset($sections[$key]['text'])) {
            echo "sections.$key debe tener state, pct, text\n";
            $ok = false;
        }
    }
    if (isset($sections['gestion']) && is_array($sections['gestion']) && !isset($sections['gestion']['gestores'])) {
        echo "sections.gestion debe tener gestores (array)\n";
        $ok = false;
    }
}

$status = $data['status'] ?? null;
$allowed = ['ok', 'fixed_by_rule', 'fallback'];
if (!in_array($status, $allowed, true)) {
    echo "status debe ser uno de: " . implode(', ', $allowed) . ", obtuvo: " . var_export($status, true) . "\n";
    $ok = false;
}

if (!array_key_exists('missing_data', $data)) {
    echo "missing_data debe existir (puede ser array vacío)\n";
    $ok = false;
}

if (!array_key_exists('recommended_actions', $data)) {
    echo "recommended_actions debe existir (puede ser array vacío)\n";
    $ok = false;
}

if ($ok) {
    echo "Schema válido. overall_confidence=" . $data['overall_confidence'] . ", status=" . $data['status'] . "\n";
    $gestores = $sections['gestion']['gestores'] ?? [];
    echo "Gestores: " . count($gestores) . "\n";
    foreach (array_slice($gestores, 0, 3) as $g) {
        echo "  - " . ($g['nombre'] ?? '—') . ": visitas=" . ($g['visitas_totales'] ?? '—') . ", fuera_rango=" . ($g['visitas_fuera_rango'] ?? '—') . ", dist_km=" . (isset($g['distancia_promedio']) ? round($g['distancia_promedio'], 1) : '—') . ", cumplimiento=" . (isset($g['cumplimiento_individual']) ? round($g['cumplimiento_individual'], 1) . '%' : '—') . "\n";
    }
    exit(0);
}

exit(1);
