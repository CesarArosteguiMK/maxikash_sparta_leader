<?php

namespace Services;

use Core\Database;
use Core\DatabaseAWS;
use Core\DatabaseGeo;
use Core\DatabaseLegacy;
use Core\DatabaseMaxiGuat;
use Core\DatabaseMaxiProd;
use Core\DatabaseSegundometro;

/**
 * Read-only discovery/query gateway for the SQL sources already configured in
 * Sparta. Qwen returns a structured plan; this class validates every
 * identifier, operator and limit before compiling a SELECT statement.
 */
class LeonidasUniversalQueryService
{
    private const MAX_TABLES_IN_CONTEXT = 24;
    private const MAX_COLUMNS_PER_TABLE = 45;
    private const MAX_LIST_ROWS = 30;

    /** @var array<string, array<string, mixed>> */
    private array $schemaCache = [];

    /** @return array<string, mixed>|null */
    public function resolver(string $question, int $actorId): ?array
    {
        if (!$this->isDataQuestion($question)) {
            return null;
        }

        $source = $this->selectSource($question);
        if ($source === null) {
            return null;
        }

        try {
            $db = $this->connection($source);
            $schema = $this->discoverSchema($db, $source);
            $context = $this->relevantSchema($schema, $question);
            if ($context === []) {
                return null;
            }

            $plan = $this->plan($question, $source, $context);
            if (!is_array($plan) || ($plan['accion'] ?? '') !== 'consultar') {
                return null;
            }

            return $this->execute($db, $source, $schema, $plan, $actorId);
        } catch (\InvalidArgumentException $error) {
            error_log('[Leonidas universal] Rejected plan: ' . $error->getMessage());
            return null;
        } catch (\Throwable $error) {
            error_log('[Leonidas universal] Source ' . $source . ' failed: ' . $error->getMessage());
            return [
                'mensaje' => 'Localicé la fuente ' . $this->sourceLabel($source)
                    . ', pero no respondió correctamente. No se modificó ningún dato.',
                'tipo' => 'consulta_semantica_error',
                'fuente' => $source,
                'metricas' => ['dataset' => 'consulta_universal'],
            ];
        }
    }

    private function isDataQuestion(string $question): bool
    {
        $text = $this->normalize($question);
        if (preg_match('/\b(actualiza|modifica|cambia|elimina|borra|inserta|asigna|otorga|envia|manda)\b/', $text)) {
            return false;
        }

        return (bool) preg_match(
            '/\b(cuanto|cuantos|cuantas|total|conteo|cantidad|lista|listar|muestra|mostrar|reporte|detalle|consulta|consultar|busca|buscar|quien|quienes|dime)\b/',
            $text
        );
    }

    private function selectSource(string $question): ?string
    {
        $text = $this->normalize($question);
        $rules = [
            'segundometro' => '/\b(segundometro|bucket|buckets|avance de bucket)\b/',
            'geografia' => '/\b(geografia|pais|paises|estado|estados|municipio|municipios|colonia|colonias|codigo postal)\b/',
            'maxi_guatemala' => '/\b(guatemala|maxi guat)\b/',
            'maxi_produccion' => '/\b(maxi produccion|base de produccion|produccion maxi)\b/',
            'aws_operativa' => '/\b(aws|rds|base operativa aws)\b/',
            'legacy' => '/\b(legacy|credito|creditos|gestion|gestiones|cobranza|distribuidor|distribuidores|cliente|clientes|cartera|pago|pagos)\b/',
            'sparta_principal' => '/\b(sparta|capital humano|persona|personas|usuario|usuarios|candidato|candidatos|plantilla|empleado|empleados|vacacion|vacaciones|baja|bajas|permiso|permisos|modulo|modulos|documento|documentos)\b/',
        ];
        foreach ($rules as $source => $pattern) {
            if (preg_match($pattern, $text)) {
                return $source;
            }
        }

        $catalog = array_values(array_filter(
            (new LeonidasDataSourceRegistry())->catalogoPublico(),
            static fn(array $item): bool => ($item['tipo'] ?? '') === 'sql'
        ));
        $choice = (new LeonidasQwenClient())->json(
            'Seleccionas una fuente SQL de Sparta. No respondes al usuario ni generas SQL.',
            'Elige una sola fuente para contestar la pregunta. Si ninguna corresponde usa fuente="". '
                . 'Devuelve exclusivamente JSON: {"fuente":"","confianza":0.0}. '
                . "\nFUENTES:\n" . json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                . "\nPREGUNTA:\n" . $question,
            350
        );
        $source = (string) ($choice['fuente'] ?? '');
        return (float) ($choice['confianza'] ?? 0) >= 0.65 && isset($this->sourceClasses()[$source])
            ? $source
            : null;
    }

    /** @return object */
    private function connection(string $source)
    {
        $classes = $this->sourceClasses();
        if (!isset($classes[$source])) {
            throw new \InvalidArgumentException('Unknown source.');
        }
        $class = $classes[$source];
        return new $class();
    }

    /** @return array<string, class-string> */
    private function sourceClasses(): array
    {
        return [
            'sparta_principal' => Database::class,
            'legacy' => DatabaseLegacy::class,
            'geografia' => DatabaseGeo::class,
            'segundometro' => DatabaseSegundometro::class,
            'maxi_produccion' => DatabaseMaxiProd::class,
            'maxi_guatemala' => DatabaseMaxiGuat::class,
            'aws_operativa' => DatabaseAWS::class,
        ];
    }

    /** @param object $db @return array<string, array<string, string>> */
    private function discoverSchema(object $db, string $source): array
    {
        if (isset($this->schemaCache[$source])) {
            return $this->schemaCache[$source];
        }

        $rows = $db->queryAll(
            'SELECT TABLE_NAME AS tabla, COLUMN_NAME AS columna, DATA_TYPE AS tipo '
            . 'FROM INFORMATION_SCHEMA.COLUMNS '
            . 'WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME, ORDINAL_POSITION'
        );
        $schema = [];
        foreach ($rows as $row) {
            $table = (string) ($row['tabla'] ?? '');
            $column = (string) ($row['columna'] ?? '');
            $type = strtolower((string) ($row['tipo'] ?? ''));
            if (!$this->validIdentifier($table) || !$this->validIdentifier($column) || $this->isSensitiveColumn($column)) {
                continue;
            }
            if (in_array($type, ['blob', 'tinyblob', 'mediumblob', 'longblob', 'binary', 'varbinary'], true)) {
                continue;
            }
            $schema[$table][$column] = $type;
        }
        $this->schemaCache[$source] = $schema;
        return $schema;
    }

    /**
     * @param array<string, array<string, string>> $schema
     * @return array<string, array<string, string>>
     */
    private function relevantSchema(array $schema, string $question): array
    {
        $tokens = array_values(array_filter(
            preg_split('/\s+/', $this->normalize($question)) ?: [],
            static fn(string $token): bool => strlen($token) >= 3
                && !in_array($token, ['que', 'del', 'los', 'las', 'una', 'uno', 'para', 'con', 'por', 'hoy', 'este', 'esta', 'dime'], true)
        ));
        $scored = [];
        foreach ($schema as $table => $columns) {
            $haystack = $this->normalize($table . ' ' . implode(' ', array_keys($columns)));
            $score = 0;
            foreach ($tokens as $token) {
                if (str_contains($this->normalize($table), $token)) {
                    $score += 5;
                } elseif (str_contains($haystack, $token)) {
                    $score += 2;
                }
            }
            $scored[] = ['table' => $table, 'columns' => $columns, 'score' => $score];
        }
        usort($scored, static fn(array $a, array $b): int => $b['score'] <=> $a['score'] ?: strcmp($a['table'], $b['table']));

        $result = [];
        foreach (array_slice($scored, 0, self::MAX_TABLES_IN_CONTEXT) as $item) {
            $result[$item['table']] = array_slice($item['columns'], 0, self::MAX_COLUMNS_PER_TABLE, true);
        }
        return $result;
    }

    /**
     * @param array<string, array<string, string>> $context
     * @return array<string, mixed>|null
     */
    private function plan(string $question, string $source, array $context): ?array
    {
        $prompt = 'Construye un plan de consulta de solo lectura usando una sola tabla del esquema. No escribas SQL. '
            . 'Operaciones: conteo, lista, agrupacion, suma, promedio, minimo, maximo. '
            . 'Operadores de filtro: igual, distinto, contiene, mayor, menor, mayor_igual, menor_igual, nulo, no_nulo, en. '
            . 'Para conteo usa campo_valor="". Para lista elige hasta 8 campos. Para agrupacion indica agrupar_por. '
            . 'No inventes tablas, columnas ni valores. Si el esquema no permite responder usa accion="ninguna". '
            . 'Devuelve exclusivamente JSON: '
            . '{"accion":"consultar|ninguna","tabla":"","operacion":"conteo|lista|agrupacion|suma|promedio|minimo|maximo",'
            . '"campos":[],"campo_valor":"","agrupar_por":"","filtros":[{"campo":"","operador":"igual","valor":""}],'
            . '"limite":20,"titulo":"","confianza":0.0}. '
            . "\nFECHA ACTUAL: " . date('Y-m-d')
            . "\nFUENTE: " . $source
            . "\nESQUEMA AUTORIZADO:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . "\nPREGUNTA:\n" . $question;

        $plan = (new LeonidasQwenClient())->json(
            'Eres el planificador seguro de consultas de Leonidas. Solo produces planes JSON de lectura.',
            $prompt,
            1100
        );
        if (!is_array($plan) || (float) ($plan['confianza'] ?? 0) < 0.62) {
            return null;
        }
        return $plan;
    }

    /**
     * @param object $db
     * @param array<string, array<string, string>> $schema
     * @param array<string, mixed> $plan
     * @return array<string, mixed>
     */
    private function execute(object $db, string $source, array $schema, array $plan, int $actorId): array
    {
        $table = (string) ($plan['tabla'] ?? '');
        $operation = (string) ($plan['operacion'] ?? '');
        if (!isset($schema[$table]) || !in_array($operation, ['conteo', 'lista', 'agrupacion', 'suma', 'promedio', 'minimo', 'maximo'], true)) {
            throw new \InvalidArgumentException('Table or operation not allowed.');
        }
        $columns = $schema[$table];
        $params = [];
        $where = [];
        foreach (array_slice((array) ($plan['filtros'] ?? []), 0, 8) as $index => $filter) {
            if (is_array($filter)) {
                $compiled = $this->compileFilter($filter, $columns, $params, $index);
                if ($compiled !== '') {
                    $where[] = $compiled;
                }
            }
        }
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $quotedTable = $this->quoteIdentifier($table);
        $title = trim((string) ($plan['titulo'] ?? '')) ?: 'Consulta de ' . str_replace('_', ' ', $table);
        $dataset = $source . '.' . $table;

        if ($operation === 'conteo') {
            $row = $db->queryOne('SELECT COUNT(*) AS total FROM ' . $quotedTable . $whereSql, $params);
            $total = (int) ($row['total'] ?? 0);
            return $this->countResponse($source, $dataset, $title, $total);
        }

        if ($operation === 'agrupacion') {
            $group = (string) ($plan['agrupar_por'] ?? '');
            $this->assertColumn($group, $columns);
            $quotedGroup = $this->quoteIdentifier($group);
            $rows = $db->queryAll(
                'SELECT ' . $quotedGroup . ' AS etiqueta, COUNT(*) AS total FROM ' . $quotedTable . $whereSql
                . ' GROUP BY ' . $quotedGroup . ' ORDER BY total DESC LIMIT 50',
                $params
            );
            $normalized = array_map(static fn(array $row): array => [
                'nombre' => (string) ($row['etiqueta'] ?? 'Sin dato'),
                'detalle' => number_format((int) ($row['total'] ?? 0)) . ' registros',
                'total' => (int) ($row['total'] ?? 0),
            ], $rows);
            return $this->reportResponse($source, $dataset, $title, $normalized, array_sum(array_column($normalized, 'total')));
        }

        if (in_array($operation, ['suma', 'promedio', 'minimo', 'maximo'], true)) {
            $field = (string) ($plan['campo_valor'] ?? '');
            $this->assertColumn($field, $columns);
            if (!$this->isNumericType((string) $columns[$field])) {
                throw new \InvalidArgumentException('Aggregate field is not numeric.');
            }
            $function = ['suma' => 'SUM', 'promedio' => 'AVG', 'minimo' => 'MIN', 'maximo' => 'MAX'][$operation];
            $row = $db->queryOne(
                'SELECT ' . $function . '(' . $this->quoteIdentifier($field) . ') AS valor FROM ' . $quotedTable . $whereSql,
                $params
            );
            $value = is_numeric($row['valor'] ?? null) ? (float) $row['valor'] : 0.0;
            return [
                'mensaje' => $title . ': ' . number_format($value, 2) . '. Fuente: ' . $this->sourceLabel($source) . ' > ' . $table . '.',
                'tipo' => 'consulta_semantica',
                'fuente' => $source,
                'metricas' => ['dataset' => $dataset, 'operacion' => $operation, 'valor' => $value, 'actor_id' => $actorId],
                'ia_disponible' => true,
                'modelo_ia' => 'Qwen + pasarela de lectura de Sparta',
            ];
        }

        $fields = array_values(array_unique(array_filter(array_map('strval', (array) ($plan['campos'] ?? [])))));
        $fields = array_slice($fields, 0, 8);
        if ($fields === []) {
            $fields = array_slice(array_keys($columns), 0, 6);
        }
        foreach ($fields as $field) {
            $this->assertColumn($field, $columns);
        }
        $select = implode(', ', array_map([$this, 'quoteIdentifier'], $fields));
        $limit = max(1, min((int) ($plan['limite'] ?? 20), self::MAX_LIST_ROWS));
        $count = $db->queryOne('SELECT COUNT(*) AS total FROM ' . $quotedTable . $whereSql, $params);
        $rows = $db->queryAll('SELECT ' . $select . ' FROM ' . $quotedTable . $whereSql . ' LIMIT ' . $limit, $params);
        $rows = array_map([$this, 'normalizeRow'], $rows);
        return $this->reportResponse($source, $dataset, $title, $rows, (int) ($count['total'] ?? count($rows)));
    }

    /** @param array<string, mixed> $filter @param array<string, string> $columns @param array<string, mixed> $params */
    private function compileFilter(array $filter, array $columns, array &$params, int $index): string
    {
        $field = (string) ($filter['campo'] ?? '');
        $operator = (string) ($filter['operador'] ?? '');
        if ($field === '' || $operator === '') {
            return '';
        }
        $this->assertColumn($field, $columns);
        $column = $this->quoteIdentifier($field);
        if ($operator === 'nulo') {
            return $column . ' IS NULL';
        }
        if ($operator === 'no_nulo') {
            return $column . ' IS NOT NULL';
        }
        $value = $filter['valor'] ?? '';
        if ($operator === 'en') {
            $values = is_array($value) ? array_slice($value, 0, 20) : array_filter(array_map('trim', explode(',', (string) $value)));
            if ($values === []) {
                return '';
            }
            $holders = [];
            foreach ($values as $position => $item) {
                $key = 'uf_' . $index . '_' . $position;
                $params[$key] = $item;
                $holders[] = ':' . $key;
            }
            return $column . ' IN (' . implode(', ', $holders) . ')';
        }
        $sqlOperators = [
            'igual' => '=', 'distinto' => '<>', 'mayor' => '>', 'menor' => '<',
            'mayor_igual' => '>=', 'menor_igual' => '<=', 'contiene' => 'LIKE',
        ];
        if (!isset($sqlOperators[$operator])) {
            throw new \InvalidArgumentException('Filter operator not allowed.');
        }
        $key = 'uf_' . $index;
        $params[$key] = $operator === 'contiene' ? '%' . (string) $value . '%' : $value;
        return $column . ' ' . $sqlOperators[$operator] . ' :' . $key;
    }

    /** @param array<string, string> $columns */
    private function assertColumn(string $column, array $columns): void
    {
        if (!isset($columns[$column]) || $this->isSensitiveColumn($column)) {
            throw new \InvalidArgumentException('Column not allowed.');
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (!$this->validIdentifier($identifier)) {
            throw new \InvalidArgumentException('Invalid identifier.');
        }
        return '`' . $identifier . '`';
    }

    private function validIdentifier(string $identifier): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_]+$/', $identifier);
    }

    private function isSensitiveColumn(string $column): bool
    {
        return (bool) preg_match(
            '/(?:password|contrasena|passwd|token|secret|api_?key|private_?key|session|cookie|hash|curp|rfc|nss|clabe|cuenta_?banc|salario|sueldo|benefici|salud|diagnost|foto|archivo|contenido|documento_binario)/i',
            $column
        );
    }

    private function isNumericType(string $type): bool
    {
        return in_array(strtolower($type), ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'numeric', 'float', 'double', 'real'], true);
    }

    /** @return array<string, mixed> */
    private function countResponse(string $source, string $dataset, string $title, int $total): array
    {
        return [
            'mensaje' => $title . ': ' . number_format($total) . '. Fuente: ' . $this->sourceLabel($source)
                . ' > ' . substr($dataset, strrpos($dataset, '.') + 1) . '.',
            'tipo' => 'consulta_semantica',
            'fuente' => $source,
            'metricas' => ['dataset' => $dataset, 'total' => $total],
            'ia_disponible' => true,
            'modelo_ia' => 'Qwen + pasarela de lectura de Sparta',
        ];
    }

    /** @param array<int, array<string, mixed>> $rows @return array<string, mixed> */
    private function reportResponse(string $source, string $dataset, string $title, array $rows, int $total): array
    {
        return [
            'mensaje' => 'Preparé ' . number_format($total) . ' registros para "' . $title . '". Fuente: '
                . $this->sourceLabel($source) . ' > ' . substr($dataset, strrpos($dataset, '.') + 1) . '.',
            'tipo' => 'consulta_semantica',
            'fuente' => $source,
            'reporte' => ['titulo' => $title, 'total' => $total, 'filas' => $rows],
            'metricas' => ['dataset' => $dataset, 'total' => $total],
            'ia_disponible' => true,
            'modelo_ia' => 'Qwen + pasarela de lectura de Sparta',
        ];
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function normalizeRow(array $row): array
    {
        $result = [];
        foreach ($row as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $text = $value === null ? '' : (string) $value;
                $result[(string) $key] = mb_strlen($text) > 240 ? mb_substr($text, 0, 237) . '...' : $text;
            }
        }
        return $result;
    }

    private function sourceLabel(string $source): string
    {
        return [
            'sparta_principal' => 'Sparta principal',
            'legacy' => 'Legacy',
            'geografia' => 'Geografía',
            'segundometro' => 'Segundómetro',
            'maxi_produccion' => 'Maxi producción',
            'maxi_guatemala' => 'Maxi Guatemala',
            'aws_operativa' => 'AWS operativa',
        ][$source] ?? $source;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $converted !== false ? $converted : $value;
    }
}
