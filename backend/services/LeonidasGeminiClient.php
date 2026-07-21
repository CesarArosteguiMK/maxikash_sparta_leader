<?php

namespace Services;

/** Gemini JSON planner used by Leonidas read-only gateways. */
class LeonidasGeminiClient
{
    /** @return array<string, mixed>|null */
    public function json(string $system, string $prompt, int $maxTokens = 900): ?array
    {
        $result = (new GeminiClient())->generate(
            $system,
            [['text' => $prompt]],
            max(200, min($maxTokens, 1800)),
            true,
            0.05
        );
        if (empty($result['success'])) {
            error_log('[Leonidas] Gemini planner unavailable. ' . (string) ($result['mensaje'] ?? ''));
            return null;
        }

        $content = trim((string) ($result['texto'] ?? ''));
        $content = trim(preg_replace('/^```(?:json)?|```$/mi', '', $content) ?? $content);
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            error_log('[Leonidas] Gemini planner returned invalid JSON.');
            return null;
        }
        $decoded['_modelo'] = (string) ($result['modelo'] ?? 'Gemini');
        return $decoded;
    }
}
