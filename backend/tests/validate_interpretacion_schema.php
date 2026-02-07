<?php
/**
 * Valida que el JSON de respuesta de GET /api/analitica/interpretacion cumpla el schema esperado.
 * Uso: curl -s 'http://localhost/api/analitica/interpretacion?id_credito=NNN' -b cookies.txt | php validate_interpretacion_schema.php
 *   o: php validate_interpretacion_schema.php < response.json
 */
$raw = file_get_contents('php://stdin');
$data = json_decode($raw, true);
if (!is_array($data)) {
    fwrite(STDERR, "Error: entrada no es JSON válido\n");
    exit(1);
}
$errors = [];
if (!isset($data['success']) || $data['success'] !== true) {
    $errors[] = 'success debe ser true';
}
if (!array_key_exists('overall_confidence', $data)) {
    $errors[] = 'Falta overall_confidence';
} else {
    $c = $data['overall_confidence'];
    if (!is_numeric($c) || $c < 0 || $c > 100) {
        $errors[] = 'overall_confidence debe ser número 0..100';
    }
}
if (!array_key_exists('summary', $data)) {
    $errors[] = 'Falta summary';
}
if (!isset($data['sections']) || !is_array($data['sections'])) {
    $errors[] = 'Falta sections (objeto)';
} else {
    $sec = $data['sections'];
    foreach (['cliente', 'gestion', 'pagos'] as $key) {
        if (!isset($sec[$key]) || !is_array($sec[$key])) {
            $errors[] = "sections.{$key} debe ser objeto";
        } else {
            $block = $sec[$key];
            if (!array_key_exists('state', $block)) $errors[] = "sections.{$key}.state";
            if (!array_key_exists('pct', $block)) $errors[] = "sections.{$key}.pct";
            if (!array_key_exists('text', $block)) $errors[] = "sections.{$key}.text";
        }
    }
    if (isset($sec['gestion']) && is_array($sec['gestion']) && !isset($sec['gestion']['gestores'])) {
        $errors[] = 'sections.gestion.gestores (array de gestores)';
    } elseif (isset($sec['gestion']['gestores']) && !is_array($sec['gestion']['gestores'])) {
        $errors[] = 'sections.gestion.gestores debe ser array';
    }
}
if (!array_key_exists('missing_data', $data)) {
    $errors[] = 'Falta missing_data';
} elseif (!is_array($data['missing_data'])) {
    $errors[] = 'missing_data debe ser array';
}
if (!array_key_exists('recommended_actions', $data)) {
    $errors[] = 'Falta recommended_actions';
} elseif (!is_array($data['recommended_actions'])) {
    $errors[] = 'recommended_actions debe ser array';
}
if (!array_key_exists('status', $data)) {
    $errors[] = 'Falta status';
} elseif (!in_array($data['status'], ['ok', 'fixed_by_rule', 'fallback'], true)) {
    $errors[] = 'status debe ser ok|fixed_by_rule|fallback';
}
if (!empty($errors)) {
    fwrite(STDERR, "Schema inválido:\n" . implode("\n", $errors) . "\n");
    exit(1);
}
echo "OK: schema válido (overall_confidence=" . ($data['overall_confidence'] ?? '?') . ", status=" . ($data['status'] ?? '?') . ")\n";
exit(0);
