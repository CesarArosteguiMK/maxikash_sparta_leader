<?php

namespace Services;

/**
 * Audits the breadth, freshness and governance of Leonidas knowledge.
 */
final class LeonidasKnowledgeAuditService
{
    /** @return array<string, mixed> */
    public function resumen(): array
    {
        $root = dirname(__DIR__, 2);
        $docsDirectory = $root . '/public/assets/docs/leonidas';
        $manifestPath = $docsDirectory . '/REVISIONES.json';
        $manifest = is_file($manifestPath)
            ? json_decode((string) file_get_contents($manifestPath), true)
            : null;
        $manifest = is_array($manifest) ? $manifest : ['dominios' => [], 'politica' => []];

        $catalog = (new LeonidasCapabilityRegistry())->catalogoPublico();
        $registryDomains = array_values(array_map(
            static fn(array $domain): string => (string) ($domain['id'] ?? ''),
            $catalog
        ));
        $documentedDomains = [];
        $markdownFiles = glob($docsDirectory . '/*.md') ?: [];
        $allDocumentation = '';
        $latestModification = 0;
        foreach ($markdownFiles as $file) {
            $content = (string) file_get_contents($file);
            $allDocumentation .= "\n" . $content;
            $latestModification = max($latestModification, (int) (@filemtime($file) ?: 0));
            if (preg_match('/^Dominio:\s*`([^`]+)`\./m', $content, $match)) {
                $documentedDomains[] = (string) $match[1];
            }
        }
        $documentedDomains = array_values(array_unique($documentedDomains));

        $inventory = (new LeonidasCodeKnowledgeService())->inventory();
        $curatedModules = (new LeonidasKnowledgeService())->catalogoModulos();
        $counts = ['controladores' => 0, 'modelos' => 0, 'servicios' => 0];
        $explicitControllers = 0;
        foreach ($inventory as $component) {
            $type = (string) ($component['tipo'] ?? '');
            if (isset($counts[$type])) {
                $counts[$type]++;
            }
            if ($type === 'controladores'
                && stripos($allDocumentation, (string) ($component['clase'] ?? '')) !== false) {
                $explicitControllers++;
            }
        }

        $governedDomains = array_keys((array) ($manifest['dominios'] ?? []));
        $pendingBusinessReview = [];
        foreach ((array) ($manifest['dominios'] ?? []) as $domainId => $review) {
            if (($review['validacion_negocio'] ?? 'pendiente') !== 'validada') {
                $pendingBusinessReview[] = [
                    'dominio' => (string) $domainId,
                    'area_responsable' => (string) ($review['area_responsable'] ?? ''),
                    'estado' => (string) ($review['validacion_negocio'] ?? 'pendiente'),
                ];
            }
        }

        $validityDays = max(1, (int) ($manifest['politica']['vigencia_dias'] ?? 120));
        $staleBefore = time() - ($validityDays * 86400);
        $staleDocuments = [];
        foreach ($markdownFiles as $file) {
            if ((int) (@filemtime($file) ?: 0) < $staleBefore) {
                $staleDocuments[] = basename($file);
            }
        }

        return [
            'dominios_registrados' => count($registryDomains),
            'dominios_documentados' => count(array_intersect($registryDomains, $documentedDomains)),
            'dominios_sin_documento' => array_values(array_diff($registryDomains, $documentedDomains)),
            'dominios_sin_gobernanza' => array_values(array_diff($registryDomains, $governedDomains)),
            'documentos_markdown_leonidas' => count($markdownFiles),
            'documentos_complementarios' => max(0, count($markdownFiles) - count($documentedDomains) - 1),
            'modulos_en_catalogo_curado' => count($curatedModules),
            'inventario_codigo' => $counts,
            'controladores_reconocibles_por_codigo' => $counts['controladores'],
            'controladores_mencionados_explicitamente_en_documentos' => $explicitControllers,
            'revisiones_negocio_pendientes' => $pendingBusinessReview,
            'documentos_fuera_de_vigencia' => $staleDocuments,
            'ultima_actualizacion_documental' => $latestModification > 0
                ? date(DATE_ATOM, $latestModification)
                : null,
            'criterio' => 'La cobertura de codigo reconoce componentes; la validacion de negocio requiere firma humana.',
        ];
    }
}
