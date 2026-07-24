<?php

namespace Services;

class LeonidasDiagramRenderer
{
    /** @param array<string, mixed> $diagram */
    public function render(array $diagram): string
    {
        $nodes = $this->normalizeNodes((array) ($diagram['nodes'] ?? []));
        if ($nodes === []) {
            throw new \InvalidArgumentException('El diagrama no contiene nodos validos.');
        }

        $edges = $this->normalizeEdges((array) ($diagram['edges'] ?? []), array_keys($nodes));
        $levels = $this->buildLevels($nodes, $edges);
        $maxInLevel = max(array_map('count', $levels));
        $nodeWidth = 230;
        $nodeHeight = 84;
        $gapX = 58;
        $gapY = 92;
        $margin = 54;
        $width = max(760, ($maxInLevel * $nodeWidth) + (($maxInLevel - 1) * $gapX) + ($margin * 2));
        $height = max(360, (count($levels) * $nodeHeight) + ((count($levels) - 1) * $gapY) + ($margin * 2) + 72);

        $positions = [];
        foreach ($levels as $level => $ids) {
            $rowWidth = (count($ids) * $nodeWidth) + ((count($ids) - 1) * $gapX);
            $startX = ($width - $rowWidth) / 2;
            foreach ($ids as $index => $id) {
                $positions[$id] = [
                    'x' => $startX + ($index * ($nodeWidth + $gapX)),
                    'y' => $margin + 72 + ($level * ($nodeHeight + $gapY)),
                ];
            }
        }

        $title = $this->escape((string) ($diagram['title'] ?? 'Diagrama generado por Leonidas'));
        $subtitle = $this->escape((string) ($diagram['subtitle'] ?? 'Sparta'));
        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg .= '<defs><marker id="arrow" markerWidth="10" markerHeight="8" refX="9" refY="4" orient="auto"><path d="M0,0 L10,4 L0,8 z" fill="#6b7d94"/></marker>';
        $svg .= '<filter id="shadow" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#1f3555" flood-opacity=".12"/></filter></defs>';
        $svg .= '<rect width="100%" height="100%" fill="#f7f9fc"/>';
        $svg .= '<text x="' . $margin . '" y="38" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#1f3555">' . $title . '</text>';
        $svg .= '<text x="' . $margin . '" y="61" font-family="Arial, sans-serif" font-size="13" fill="#728096">' . $subtitle . '</text>';

        foreach ($edges as $edge) {
            $from = $positions[$edge['from']];
            $to = $positions[$edge['to']];
            $x1 = $from['x'] + ($nodeWidth / 2);
            $y1 = $from['y'] + $nodeHeight;
            $x2 = $to['x'] + ($nodeWidth / 2);
            $y2 = $to['y'];
            $midY = ($y1 + $y2) / 2;
            $svg .= '<path d="M' . $x1 . ' ' . $y1 . ' C' . $x1 . ' ' . $midY . ' ' . $x2 . ' ' . $midY . ' ' . $x2 . ' ' . $y2 . '" fill="none" stroke="#8797aa" stroke-width="2" marker-end="url(#arrow)"/>';
            if ($edge['label'] !== '') {
                $labelX = ($x1 + $x2) / 2;
                $svg .= '<text x="' . $labelX . '" y="' . ($midY - 5) . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" fill="#53647a">' . $this->escape($edge['label']) . '</text>';
            }
        }

        foreach ($nodes as $id => $node) {
            $position = $positions[$id];
            [$fill, $stroke] = $this->colors($node['type']);
            $svg .= '<g filter="url(#shadow)"><rect x="' . $position['x'] . '" y="' . $position['y'] . '" width="' . $nodeWidth . '" height="' . $nodeHeight . '" rx="8" fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="1.5"/></g>';
            $lines = $this->wrap($node['label'], 30, 3);
            $startY = $position['y'] + 34 - ((count($lines) - 1) * 9);
            $svg .= '<text x="' . ($position['x'] + ($nodeWidth / 2)) . '" y="' . $startY . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" font-weight="700" fill="#233a5d">';
            foreach ($lines as $index => $line) {
                $svg .= '<tspan x="' . ($position['x'] + ($nodeWidth / 2)) . '" dy="' . ($index === 0 ? '0' : '19') . '">' . $this->escape($line) . '</tspan>';
            }
            $svg .= '</text>';
        }

        $svg .= '<text x="' . ($width - $margin) . '" y="' . ($height - 20) . '" text-anchor="end" font-family="Arial, sans-serif" font-size="10" fill="#9aa6b5">Generado por Leonidas</text>';
        $svg .= '</svg>';
        return $svg;
    }

    /** @param array<mixed> $raw @return array<string, array{label:string,type:string}> */
    private function normalizeNodes(array $raw): array
    {
        $nodes = [];
        foreach (array_slice($raw, 0, 30) as $index => $node) {
            if (!is_array($node)) {
                continue;
            }
            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($node['id'] ?? 'n' . ($index + 1)));
            $label = trim((string) ($node['label'] ?? ''));
            if ($id === '' || $label === '' || isset($nodes[$id])) {
                continue;
            }
            $type = strtolower(trim((string) ($node['type'] ?? 'process')));
            $nodes[$id] = [
                'label' => mb_substr($label, 0, 180, 'UTF-8'),
                'type' => in_array($type, ['start', 'process', 'decision', 'end'], true) ? $type : 'process',
            ];
        }
        return $nodes;
    }

    /** @param array<mixed> $raw @param list<string> $nodeIds @return list<array{from:string,to:string,label:string}> */
    private function normalizeEdges(array $raw, array $nodeIds): array
    {
        $allowed = array_fill_keys($nodeIds, true);
        $edges = [];
        foreach (array_slice($raw, 0, 50) as $edge) {
            if (!is_array($edge)) {
                continue;
            }
            $from = (string) ($edge['from'] ?? '');
            $to = (string) ($edge['to'] ?? '');
            if ($from === $to || !isset($allowed[$from], $allowed[$to])) {
                continue;
            }
            $edges[] = [
                'from' => $from,
                'to' => $to,
                'label' => mb_substr(trim((string) ($edge['label'] ?? '')), 0, 60, 'UTF-8'),
            ];
        }
        return $edges;
    }

    /**
     * @param array<string, array{label:string,type:string}> $nodes
     * @param list<array{from:string,to:string,label:string}> $edges
     * @return array<int, list<string>>
     */
    private function buildLevels(array $nodes, array $edges): array
    {
        $levels = array_fill_keys(array_keys($nodes), 0);
        for ($pass = 0; $pass < count($nodes); $pass++) {
            $changed = false;
            foreach ($edges as $edge) {
                $candidate = min(count($nodes) - 1, $levels[$edge['from']] + 1);
                if ($candidate > $levels[$edge['to']]) {
                    $levels[$edge['to']] = $candidate;
                    $changed = true;
                }
            }
            if (!$changed) {
                break;
            }
        }

        $grouped = [];
        foreach (array_keys($nodes) as $id) {
            $grouped[$levels[$id]][] = $id;
        }
        ksort($grouped);
        return array_values($grouped);
    }

    /** @return array{0:string,1:string} */
    private function colors(string $type): array
    {
        return match ($type) {
            'start' => ['#e7f8ef', '#31a66a'],
            'decision' => ['#fff6df', '#d39b2f'],
            'end' => ['#fdecec', '#d65b5b'],
            default => ['#edf4ff', '#5a86c8'],
        };
    }

    /** @return list<string> */
    private function wrap(string $text, int $length, int $maxLines): array
    {
        $lines = explode("\n", wordwrap(preg_replace('/\s+/u', ' ', trim($text)) ?: '', $length, "\n", true));
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], '. ') . '...';
        }
        return $lines === [] ? [''] : $lines;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
