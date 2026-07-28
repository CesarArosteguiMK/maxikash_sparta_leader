<?php

namespace Services;

/**
 * Builds a safe, read-only map of Sparta's PHP components.
 *
 * It exposes class names and public capabilities only. Method bodies, SQL,
 * configuration values, credentials and private implementation details never
 * leave the server.
 */
final class LeonidasCodeKnowledgeService
{
    private const MAX_COMPONENTS = 6;
    private const MAX_METHODS_PER_COMPONENT = 24;

    /** @var array<int, array<string, mixed>>|null */
    private static ?array $cachedInventory = null;

    /** @return array<string, mixed> */
    public function contextoPara(string $pregunta): array
    {
        $terms = $this->terms($pregunta);
        $inventory = $this->inventory();
        $counts = ['controladores' => 0, 'modelos' => 0, 'servicios' => 0];
        foreach ($inventory as $component) {
            $type = (string) ($component['tipo'] ?? '');
            if (isset($counts[$type])) {
                $counts[$type]++;
            }
        }

        if ($terms === []) {
            return [
                'alcance' => $this->scopeDescription(),
                'inventario' => $counts,
                'componentes_relevantes' => [],
            ];
        }

        $scored = [];
        $prefersController = preg_match('/\b(modulo|pantalla|controlador)\b/u', $this->normalize($pregunta)) === 1;
        foreach ($inventory as $component) {
            $classText = $this->normalize($this->humanize((string) ($component['clase'] ?? '')));
            $fileText = $this->normalize((string) ($component['archivo'] ?? ''));
            $methodText = $this->normalize(implode(' ', (array) ($component['metodos_publicos'] ?? [])));
            $score = 0;
            foreach ($terms as $term) {
                if (str_contains($classText, $term)) {
                    $score += 24;
                }
                if (str_contains($fileText, $term)) {
                    $score += 14;
                }
                $score += min(12, substr_count($methodText, $term) * 3);
            }
            $phrase = $this->normalize($pregunta);
            if ($classText !== '' && str_contains($phrase, $classText)) {
                $score += 35;
            }
            if ($prefersController && ($component['tipo'] ?? '') === 'controladores') {
                $score += 20;
            }
            if ($score <= 0) {
                continue;
            }
            $scored[] = ['puntaje' => $score] + $component;
        }

        usort($scored, static function (array $a, array $b): int {
            $score = $b['puntaje'] <=> $a['puntaje'];
            return $score !== 0 ? $score : strcmp((string) $a['archivo'], (string) $b['archivo']);
        });

        $relevant = array_map(static function (array $component): array {
            unset($component['puntaje']);
            return $component;
        }, array_slice($scored, 0, self::MAX_COMPONENTS));

        return [
            'alcance' => $this->scopeDescription(),
            'inventario' => $counts,
            'componentes_relevantes' => $relevant,
        ];
    }

    /** @return array<string, mixed>|null */
    public function resolver(string $mensaje, string $normalizado = ''): ?array
    {
        $text = $normalizado !== '' ? $normalizado : $this->normalize($mensaje);
        $isExplanation = preg_match(
            '/\b(que es|que hace|como funciona|como se usa|para que sirve|explica|explicame|'
                . 'cuentame|platica|platicame|hablame|quiero saber|necesito entender|'
                . 'modulo|pantalla|controlador|componente|donde esta|donde veo|que permite)\b/u',
            $text
        ) === 1;
        if (!$isExplanation) {
            return null;
        }

        $context = $this->contextoPara($mensaje);
        $components = (array) ($context['componentes_relevantes'] ?? []);
        if ($components === []) {
            return null;
        }

        $component = $components[0];
        $methods = array_slice((array) ($component['metodos_publicos'] ?? []), 0, 12);
        $capabilities = array_map(fn(string $method): string => $this->humanize($method), $methods);
        $message = 'En el codigo actual de Sparta encontre el componente '
            . (string) ($component['clase'] ?? 'desconocido')
            . ' como ' . rtrim((string) ($component['tipo'] ?? 'componente'), 's')
            . '. Expone ' . (int) ($component['total_metodos_publicos'] ?? count($methods))
            . ' capacidades publicas';
        if ($capabilities !== []) {
            $message .= ', entre ellas: ' . implode(', ', $capabilities);
        }
        $message .= '. Este inventario confirma que la capacidad existe en la aplicacion, '
            . 'pero no concede permisos ni convierte sus operaciones en ejecutores de Leonidas. '
            . 'Para consultar datos o modificar algo se conservan los permisos y confirmaciones del modulo.';

        return [
            'mensaje' => $message,
            'tipo' => 'modulo_codigo_sparta',
            'fuente' => 'inventario_codigo_seguro',
            'componente' => $component,
            'mapa_codigo' => $context,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function inventory(): array
    {
        if (self::$cachedInventory !== null) {
            return self::$cachedInventory;
        }

        $projectRoot = dirname(__DIR__, 2);
        $roots = [
            'controladores' => $projectRoot . '/backend/controllers',
            'modelos' => $projectRoot . '/backend/models',
            'servicios' => $projectRoot . '/backend/services',
        ];
        $inventory = [];
        foreach ($roots as $type => $directory) {
            foreach (glob($directory . '/*.php') ?: [] as $path) {
                $content = @file_get_contents($path);
                if (!is_string($content) || $content === '') {
                    continue;
                }
                $class = basename($path, '.php');
                if (preg_match('/\b(?:final\s+|abstract\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/', $content, $match)) {
                    $class = (string) $match[1];
                }
                preg_match_all(
                    '/\bpublic\s+(?:static\s+)?function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/',
                    $content,
                    $methodMatches
                );
                $methods = array_values(array_unique(array_map('strval', $methodMatches[1] ?? [])));
                $relative = str_replace('\\', '/', substr($path, strlen($projectRoot) + 1));
                $inventory[] = [
                    'tipo' => $type,
                    'clase' => $class,
                    'archivo' => $relative,
                    'metodos_publicos' => array_slice($methods, 0, self::MAX_METHODS_PER_COMPONENT),
                    'total_metodos_publicos' => count($methods),
                    'metodos_truncados' => count($methods) > self::MAX_METHODS_PER_COMPONENT,
                ];
            }
        }

        usort($inventory, static fn(array $a, array $b): int => strcmp(
            (string) $a['archivo'],
            (string) $b['archivo']
        ));
        self::$cachedInventory = $inventory;
        return $inventory;
    }

    private function scopeDescription(): string
    {
        return 'Mapa generado desde nombres de clases y metodos publicos de controladores, modelos y servicios. '
            . 'No contiene cuerpos de codigo, SQL, valores de configuracion, credenciales ni metodos privados.';
    }

    /** @return list<string> */
    private function terms(string $text): array
    {
        $normalized = $this->normalize($this->humanize($text));
        $words = preg_split('/[^a-z0-9]+/', $normalized) ?: [];
        $ignored = [
            'como', 'cual', 'donde', 'este', 'esta', 'estos', 'estas', 'para', 'sirve',
            'hace', 'hacer', 'funciona', 'usar', 'explica', 'explicame', 'modulo', 'pantalla',
            'controlador', 'componente', 'sparta', 'leonidas', 'quiero', 'puede', 'puedes',
        ];
        $terms = [];
        foreach ($words as $word) {
            if (strlen($word) < 3 || in_array($word, $ignored, true)) {
                continue;
            }
            $terms[$word] = true;
        }
        return array_keys($terms);
    }

    private function humanize(string $value): string
    {
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $value) ?? $value;
        $value = str_replace(['_', '-'], ' ', $value);
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return trim(preg_replace('/\s+/', ' ', $ascii === false ? $value : $ascii) ?? $value);
    }
}
