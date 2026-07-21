"""Google Gemini transport for the shared document-analysis pipeline."""
import json
import time
import urllib.error
import urllib.request
from typing import Any, Dict, List, Optional
from urllib.parse import quote

from app.services.alibaba_document_ai import (
    AlibabaDocumentAI,
    extract_json,
    is_transient_error,
    parse_model_chain,
)


class GeminiDocumentAI(AlibabaDocumentAI):
    """Reuse the proven document prompts and validation with Gemini transport."""

    provider = "gemini"

    @staticmethod
    def _gemini_parts(content: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        parts: List[Dict[str, Any]] = []
        for item in content:
            kind = str(item.get("type") or "")
            if kind == "text":
                text = str(item.get("text") or "")
                if text:
                    parts.append({"text": text})
                continue
            if kind != "image_url":
                continue
            data_url = str((item.get("image_url") or {}).get("url") or "")
            if not data_url.startswith("data:") or ";base64," not in data_url:
                continue
            header, encoded = data_url.split(",", 1)
            mime_type = header[5:].split(";", 1)[0] or "image/jpeg"
            parts.append({
                "inline_data": {
                    "mime_type": mime_type,
                    "data": encoded,
                }
            })
        return parts

    @staticmethod
    def _response_text(body: Dict[str, Any]) -> str:
        texts: List[str] = []
        candidates = body.get("candidates") or []
        if candidates:
            for part in ((candidates[0].get("content") or {}).get("parts") or []):
                value = part.get("text")
                if value:
                    texts.append(str(value))
        return "".join(texts).strip()

    def _call_content(
        self,
        content: List[Dict[str, Any]],
        max_tokens: int = 1600,
        deadline: Optional[float] = None,
        enable_thinking: Optional[bool] = None,
    ) -> tuple[Dict[str, Any], Dict[str, Any], str, bool]:
        del enable_thinking
        last_exc: Optional[Exception] = None
        model_chain = parse_model_chain(self.model, self.fallback_models)
        parts = self._gemini_parts(content)
        if not parts:
            raise RuntimeError("Gemini request has no usable content")

        for model_idx, current_model in enumerate(model_chain):
            endpoint = f"{self.base_url}/models/{quote(current_model, safe='')}:generateContent"
            for attempt, delay in enumerate(self.retry_delays, start=1):
                if delay:
                    if deadline is not None and time.monotonic() + delay >= deadline:
                        last_exc = TimeoutError("Tiempo agotado antes del siguiente intento de IA")
                        break
                    time.sleep(delay)
                try:
                    request_timeout = float(self.timeout_seconds)
                    if deadline is not None:
                        remaining = deadline - time.monotonic()
                        if remaining < 1.5:
                            raise TimeoutError("Tiempo agotado para completar la lectura IA")
                        request_timeout = max(1.0, min(request_timeout, remaining))
                    payload = {
                        "contents": [{"role": "user", "parts": parts}],
                        "generationConfig": {
                            "temperature": 0,
                            "maxOutputTokens": max_tokens,
                            "responseMimeType": "application/json",
                        },
                    }
                    request = urllib.request.Request(
                        endpoint,
                        data=json.dumps(payload).encode("utf-8"),
                        headers={
                            "Content-Type": "application/json",
                            "x-goog-api-key": self.api_key,
                        },
                        method="POST",
                    )
                    with urllib.request.urlopen(request, timeout=request_timeout) as response:
                        body = json.loads(response.read().decode("utf-8"))
                    text = self._response_text(body)
                    if not text:
                        reason = str((body.get("promptFeedback") or {}).get("blockReason") or "")
                        raise RuntimeError("Gemini empty response content" + (f": {reason}" if reason else ""))
                    try:
                        parsed = extract_json(text)
                    except Exception as exc:
                        raise RuntimeError(f"Gemini invalid JSON response: {exc}") from exc
                    return parsed, body.get("usageMetadata") or {}, current_model, model_idx > 0
                except urllib.error.HTTPError as exc:
                    try:
                        detail = exc.read().decode("utf-8", errors="replace")
                    except Exception:
                        detail = str(exc)
                    last_exc = RuntimeError(f"Gemini HTTP {exc.code}: {detail}")
                except Exception as exc:
                    last_exc = exc
                if last_exc and (not is_transient_error(last_exc) or attempt >= len(self.retry_delays)):
                    break
        raise last_exc or RuntimeError("No se pudo llamar a Gemini")
