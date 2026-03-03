# app/services/geometry_analyzer.py
"""CAPA 3: Geometría del documento - proporciones y dimensiones."""
import numpy as np
import cv2
from loguru import logger
from typing import Optional

from app.models.schemas import CheckGeometria, TipoDocumento

ASPECTO_RATIO_OFICIAL = 85.60 / 53.98
TOLERANCIA_RATIO = 0.08

DIMENSIONES_OFICIALES = {
    TipoDocumento.INE_NUEVA: {"ratio": 1.586},
    TipoDocumento.INE_ANTERIOR: {"ratio": 1.586},
    TipoDocumento.RESIDENCIA_TEMPORAL: {"ratio": 1.586},
    TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA: {"ratio": 1.586},
    TipoDocumento.RESIDENCIA_PERMANENTE: {"ratio": 1.586},
}


class GeometryAnalyzer:
    def analyze(
        self,
        image_bytes: bytes,
        tipo_doc: TipoDocumento = TipoDocumento.INE_NUEVA
    ) -> CheckGeometria:
        alertas = []
        score = 1.0
        try:
            img_cv = cv2.imdecode(
                np.frombuffer(image_bytes, np.uint8),
                cv2.IMREAD_COLOR
            )
            if img_cv is None:
                return CheckGeometria(ok=False, alertas=["Imagen no válida"], score=0.5)

            contorno = self._detectar_documento(img_cv)
            ratio_detectado = None
            aspecto_str = None
            proporcion_correcta = False
            perspectiva_valida = True

            if contorno is not None:
                rect = cv2.minAreaRect(contorno)
                ancho, alto = rect[1]
                if alto > 0 and ancho > 0:
                    if ancho < alto:
                        ancho, alto = alto, ancho
                    ratio_detectado = ancho / alto
                    aspecto_str = f"{ancho:.0f}x{alto:.0f}px (ratio: {ratio_detectado:.3f})"
                    ratio_esperado = DIMENSIONES_OFICIALES.get(
                        tipo_doc, {"ratio": ASPECTO_RATIO_OFICIAL}
                    )["ratio"]
                    diferencia = abs(ratio_detectado - ratio_esperado)
                    proporcion_correcta = diferencia <= TOLERANCIA_RATIO
                    if not proporcion_correcta:
                        alertas.append(
                            f"Proporciones incorrectas: ratio {ratio_detectado:.3f} (esperado ~{ratio_esperado:.3f})"
                        )
                        score -= 0.40
                    angulo = abs(rect[2])
                    if angulo > 15 and angulo < 75:
                        perspectiva_valida = False
                        alertas.append(f"Documento inclinado {angulo:.1f}°")
                        score -= 0.05
            else:
                alertas.append("No se pudo detectar el contorno del documento")
                score -= 0.30
                h, w = img_cv.shape[:2]
                ratio_img = max(w, h) / min(w, h)
                if abs(ratio_img - ASPECTO_RATIO_OFICIAL) <= TOLERANCIA_RATIO * 2:
                    proporcion_correcta = True
                    aspecto_str = f"{w}x{h}px"
                    score += 0.15

            score = max(0.0, min(1.0, score))
            return CheckGeometria(
                ok=score >= 0.5,
                aspecto_ratio_detectado=aspecto_str,
                aspecto_ratio_esperado="85.6x54mm (ratio ~1.586)",
                proporcion_correcta=proporcion_correcta,
                perspectiva_valida=perspectiva_valida,
                alertas=alertas,
                score=round(score, 3)
            )
        except Exception as e:
            logger.error(f"Error en GeometryAnalyzer: {e}")
            return CheckGeometria(ok=False, alertas=[f"Error: {str(e)}"], score=0.5)

    def _detectar_documento(self, img: np.ndarray) -> Optional[np.ndarray]:
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        blur = cv2.GaussianBlur(gray, (5, 5), 0)
        _, thresh = cv2.threshold(blur, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        contornos, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        if not contornos:
            return None
        area_imagen = img.shape[0] * img.shape[1]
        area_minima = area_imagen * 0.20
        candidatos = []
        for contorno in contornos:
            area = cv2.contourArea(contorno)
            if area < area_minima:
                continue
            perimetro = cv2.arcLength(contorno, True)
            aprox = cv2.approxPolyDP(contorno, 0.02 * perimetro, True)
            if 4 <= len(aprox) <= 6:
                candidatos.append((area, contorno))
        if not candidatos:
            return max(contornos, key=cv2.contourArea)
        candidatos.sort(key=lambda x: x[0], reverse=True)
        return candidatos[0][1]
