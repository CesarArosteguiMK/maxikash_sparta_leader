# app/services/geometry_analyzer.py
"""
CAPA 3: Análisis de Geometría del Documento.
Verifica que las proporciones y dimensiones coincidan con documentos oficiales.

Medidas oficiales:
- INE: 85.6mm × 53.98mm (tarjeta bancaria ISO/IEC 7810 ID-1)
- Residencias INM: 85.6mm × 53.98mm (mismo formato)
"""
import io
import numpy as np
from PIL import Image
import cv2
from loguru import logger
from typing import Tuple, Optional

from app.models.schemas import CheckGeometria, TipoDocumento


# Aspecto ratio oficial de documentos de identidad (ancho/alto)
# ISO/IEC 7810 ID-1: 85.60mm × 53.98mm
ASPECTO_RATIO_OFICIAL = 85.60 / 53.98  # ≈ 1.5857
TOLERANCIA_RATIO = 0.08  # 8% de tolerancia

DIMENSIONES_OFICIALES = {
    TipoDocumento.INE_NUEVA: {"ancho_mm": 85.6, "alto_mm": 53.98, "ratio": 1.586},
    TipoDocumento.INE_ANTERIOR: {"ancho_mm": 85.6, "alto_mm": 53.98, "ratio": 1.586},
    TipoDocumento.RESIDENCIA_TEMPORAL: {"ancho_mm": 85.6, "alto_mm": 53.98, "ratio": 1.586},
    TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA: {"ancho_mm": 85.6, "alto_mm": 53.98, "ratio": 1.586},
    TipoDocumento.RESIDENCIA_PERMANENTE: {"ancho_mm": 85.6, "alto_mm": 53.98, "ratio": 1.586},
}


class GeometryAnalyzer:
    """Verifica las proporciones geométricas del documento en la imagen."""

    def analyze(
        self,
        image_bytes: bytes,
        tipo_doc: TipoDocumento = TipoDocumento.INE_NUEVA
    ) -> CheckGeometria:
        """
        Detecta el documento en la imagen y verifica sus proporciones.
        """
        alertas = []
        score = 1.0

        try:
            img_cv = cv2.imdecode(
                np.frombuffer(image_bytes, np.uint8),
                cv2.IMREAD_COLOR
            )

            # Detectar el contorno del documento
            contorno = self._detectar_documento(img_cv)
            ratio_detectado = None
            aspecto_str = None
            proporcion_correcta = False
            perspectiva_valida = True

            if contorno is not None:
                # Calcular aspecto ratio del contorno detectado
                rect = cv2.minAreaRect(contorno)
                ancho, alto = rect[1]

                if alto > 0 and ancho > 0:
                    # Asegurar que ancho > alto (orientación landscape)
                    if ancho < alto:
                        ancho, alto = alto, ancho

                    ratio_detectado = ancho / alto
                    aspecto_str = f"{ancho:.0f}x{alto:.0f}px (ratio: {ratio_detectado:.3f})"

                    # Comparar con ratio oficial
                    ratio_esperado = DIMENSIONES_OFICIALES.get(
                        tipo_doc, {"ratio": ASPECTO_RATIO_OFICIAL}
                    )["ratio"]

                    diferencia = abs(ratio_detectado - ratio_esperado)
                    proporcion_correcta = diferencia <= TOLERANCIA_RATIO

                    if not proporcion_correcta:
                        alertas.append(
                            f"⚠️ Proporciones incorrectas: ratio {ratio_detectado:.3f} "
                            f"(esperado ~{ratio_esperado:.3f})"
                        )
                        score -= 0.40

                    # Verificar perspectiva (skew excesivo)
                    angulo = abs(rect[2])
                    if angulo > 15 and angulo < 75:
                        perspectiva_valida = False
                        alertas.append(f"ℹ️ Documento inclinado {angulo:.1f}°")
                        score -= 0.05

            else:
                # No se detectó el documento
                alertas.append("⚠️ No se pudo detectar el contorno del documento")
                score -= 0.30

                # Verificar ratio de la imagen completa como fallback
                h, w = img_cv.shape[:2]
                ratio_img = max(w, h) / min(w, h)
                ratio_diferencia = abs(ratio_img - ASPECTO_RATIO_OFICIAL)

                if ratio_diferencia <= TOLERANCIA_RATIO * 2:
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
            return CheckGeometria(
                ok=False,
                alertas=[f"Error en análisis geométrico: {str(e)}"],
                score=0.5
            )

    def _detectar_documento(self, img: np.ndarray) -> Optional[np.ndarray]:
        """
        Detecta el contorno del documento en la imagen.
        Busca el rectángulo más grande con proporciones de documento de identidad.
        """
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Preprocesamiento
        blur = cv2.GaussianBlur(gray, (5, 5), 0)
        _, thresh = cv2.threshold(blur, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)

        # Encontrar contornos
        contornos, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

        if not contornos:
            return None

        # Filtrar por área mínima (al menos 20% de la imagen)
        area_imagen = img.shape[0] * img.shape[1]
        area_minima = area_imagen * 0.20

        candidatos = []
        for contorno in contornos:
            area = cv2.contourArea(contorno)
            if area < area_minima:
                continue

            # Aproximar a polígono
            perimetro = cv2.arcLength(contorno, True)
            aprox = cv2.approxPolyDP(contorno, 0.02 * perimetro, True)

            # Buscar cuadriláteros
            if 4 <= len(aprox) <= 6:
                candidatos.append((area, contorno))

        if not candidatos:
            # Retornar el contorno más grande
            return max(contornos, key=cv2.contourArea)

        # Retornar el más grande entre los cuadriláteros
        candidatos.sort(key=lambda x: x[0], reverse=True)
        return candidatos[0][1]
