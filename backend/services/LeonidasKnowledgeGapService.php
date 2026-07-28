<?php

namespace Services;

/**
 * Records anonymized knowledge-gap patterns for later documentation review.
 *
 * Raw prompts, actor identifiers and extracted entities are never persisted.
 */
final class LeonidasKnowledgeGapService
{
    private string $storagePath;

    /** @var array<string, bool> */
    private array $seen = [];

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath
            ?? dirname(__DIR__) . '/storage/leonidas/knowledge_gaps.jsonl';
    }

    /** @param array<string, mixed> $response */
    public function registrar(string $question, array $response): bool
    {
        $type = (string) ($response['tipo'] ?? '');
        if (!in_array($type, [
            'dominio_ayuda',
            'dominio_requiere_criterio',
            'dominio_fuente_error',
            'consulta_semantica_error',
            'modulo_codigo_sparta',
        ], true)) {
            return false;
        }

        $hash = hash('sha256', trim($question));
        if (isset($this->seen[$hash])) {
            return false;
        }
        $this->seen[$hash] = true;

        $pattern = $this->patronSeguro($question);
        if ($pattern === '') {
            $pattern = '[consulta no clasificable]';
        }
        $record = [
            'fecha' => date(DATE_ATOM),
            'pregunta_hash' => $hash,
            'patron_anonimizado' => $pattern,
            'tipo_respuesta' => $type,
            'dominio_detectado' => (string) ($response['dominio'] ?? ''),
            'fuente_prevista' => (string) ($response['fuente'] ?? ''),
        ];

        $directory = dirname($this->storagePath);
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            error_log('[Leonidas] No se pudo crear el directorio de brechas de conocimiento.');
            return false;
        }
        $encoded = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($encoded)) {
            return false;
        }

        $handle = @fopen($this->storagePath, 'ab');
        if ($handle === false) {
            error_log('[Leonidas] No se pudo abrir el registro de brechas de conocimiento.');
            return false;
        }
        try {
            if (!flock($handle, LOCK_EX)) {
                return false;
            }
            $written = fwrite($handle, $encoded . PHP_EOL);
            fflush($handle);
            flock($handle, LOCK_UN);
            return $written !== false;
        } finally {
            fclose($handle);
        }
    }

    /** @return array<string, mixed> */
    public function resumen(): array
    {
        $records = $this->readRecords();
        $byType = [];
        $byDomain = [];
        $patterns = [];
        foreach ($records as $record) {
            $type = (string) ($record['tipo_respuesta'] ?? 'sin_tipo');
            $domain = (string) ($record['dominio_detectado'] ?? '');
            $pattern = (string) ($record['patron_anonimizado'] ?? '');
            $byType[$type] = ($byType[$type] ?? 0) + 1;
            if ($domain !== '') {
                $byDomain[$domain] = ($byDomain[$domain] ?? 0) + 1;
            }
            if ($pattern !== '') {
                $patterns[$pattern] = ($patterns[$pattern] ?? 0) + 1;
            }
        }
        arsort($byType);
        arsort($byDomain);
        arsort($patterns);

        return [
            'total' => count($records),
            'por_tipo' => $byType,
            'por_dominio' => $byDomain,
            'patrones_frecuentes' => array_slice($patterns, 0, 25, true),
            'privacidad' => 'Solo contiene patrones con vocabulario tecnico permitido; no guarda prompts ni actores.',
        ];
    }

    private function patronSeguro(string $question): string
    {
        $normalized = mb_strtolower($question, 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        $normalized = $ascii === false ? $normalized : $ascii;
        $normalized = preg_replace('#https?://\S+|www\.\S+#i', ' [enlace] ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b[\w.+-]+@[\w.-]+\.[a-z]{2,}\b/i', ' [correo] ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b[A-Z&Ñ]{3,4}\d{6}[A-Z0-9]{3}\b/i', ' [rfc] ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d\b/i', ' [curp] ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b\d[\d\s().+-]{5,}\d\b/', ' [id] ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b\d+\b/', ' [id] ', $normalized) ?? $normalized;
        $words = preg_split('/[^a-z0-9\[\]]+/', $normalized) ?: [];
        $allowed = $this->safeVocabulary();
        $result = [];
        $previousWasData = false;
        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }
            if (in_array($word, ['[id]', '[correo]', '[rfc]', '[curp]', '[enlace]'], true)) {
                $result[] = $word;
                $previousWasData = false;
                continue;
            }
            if (isset($allowed[$word])) {
                $result[] = $word;
                $previousWasData = false;
                continue;
            }
            if (!$previousWasData) {
                $result[] = '[dato]';
                $previousWasData = true;
            }
        }
        return mb_substr(trim(implode(' ', $result)), 0, 240, 'UTF-8');
    }

    /** @return array<string, bool> */
    private function safeVocabulary(): array
    {
        $base = [
            'que', 'como', 'cual', 'cuales', 'cuando', 'donde', 'porque', 'para', 'por',
            'de', 'del', 'la', 'las', 'el', 'los', 'un', 'una', 'mi', 'mis', 'su', 'sus',
            'quiero', 'necesito', 'puedo', 'puedes', 'explica', 'explicame', 'consulta',
            'consultar', 'buscar', 'mostrar', 'muestra', 'dame', 'crear', 'guardar', 'cambiar',
            'actualizar', 'eliminar', 'cerrar', 'abrir', 'asignar', 'revisar', 'auditar',
            'comparar', 'grafica', 'reporte', 'descargar', 'importar', 'exportar', 'enviar',
            'estado', 'estatus', 'historial', 'detalle', 'modulo', 'pantalla', 'permiso',
            'error', 'falla', 'funciona', 'sirve', 'hacer', 'hace', 'tiene', 'tengo',
            'activo', 'activos', 'pendiente', 'pendientes', 'semana', 'mes', 'fecha',
            'hoy', 'ayer', 'actual', 'anterior', 'siguiente', 'sparta', 'leonidas',
        ];
        $vocabulary = array_fill_keys($base, true);
        foreach ((new LeonidasCapabilityRegistry())->catalogoPublico() as $domain) {
            foreach ([
                (string) ($domain['id'] ?? ''),
                (string) ($domain['nombre'] ?? ''),
                implode(' ', (array) ($domain['submodulos'] ?? [])),
                implode(' ', (array) ($domain['acciones_ejecutables'] ?? [])),
            ] as $text) {
                foreach ($this->technicalWords($text) as $word) {
                    $vocabulary[$word] = true;
                }
            }
        }
        foreach ((new LeonidasCodeKnowledgeService())->inventory() as $component) {
            foreach ([
                (string) ($component['clase'] ?? ''),
                implode(' ', (array) ($component['metodos_publicos'] ?? [])),
            ] as $text) {
                foreach ($this->technicalWords($text) as $word) {
                    $vocabulary[$word] = true;
                }
            }
        }
        return $vocabulary;
    }

    /** @return list<string> */
    private function technicalWords(string $value): array
    {
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $value) ?? $value;
        $value = mb_strtolower(str_replace(['_', '-'], ' ', $value), 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $words = preg_split('/[^a-z0-9]+/', $ascii === false ? $value : $ascii) ?: [];
        return array_values(array_filter($words, static fn(string $word): bool => strlen($word) >= 3));
    }

    /** @return list<array<string, mixed>> */
    private function readRecords(): array
    {
        if (!is_file($this->storagePath)) {
            return [];
        }
        $lines = @file($this->storagePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $records = [];
        foreach ($lines as $line) {
            $record = json_decode($line, true);
            if (is_array($record)) {
                $records[] = $record;
            }
        }
        return $records;
    }
}
