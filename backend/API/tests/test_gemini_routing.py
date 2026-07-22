import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.services.gemini_document_ai import GeminiDocumentAI


class GeminiRoutingTest(unittest.TestCase):
    def test_new_models_omit_legacy_sampling_controls(self) -> None:
        flash = GeminiDocumentAI._generation_config("gemini-3.6-flash", 1200, False)
        lite = GeminiDocumentAI._generation_config("gemini-3.5-flash-lite", 800)

        self.assertNotIn("temperature", flash)
        self.assertNotIn("temperature", lite)
        self.assertEqual(flash["responseMimeType"], "application/json")
        self.assertEqual(flash["thinkingConfig"]["thinkingLevel"], "LOW")

    def test_legacy_fallback_keeps_sampling_compatibility(self) -> None:
        legacy = GeminiDocumentAI._generation_config("gemini-3.1-flash-lite", 800)

        self.assertEqual(legacy["temperature"], 0)


if __name__ == "__main__":
    unittest.main()
