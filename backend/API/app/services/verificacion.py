# app/services/verificacion.py
"""Orquestador: combina todas las capas y calcula score final."""
import asyncio
import time
from loguru import logger
from typing import Optional

from app.models.schemas import (
    VerificacionResponse,
    TipoDocumento,
    ResultadoVerificacion,
    Checks,
    CheckML,
)
from app.services.metadata_analyzer import MetadataAnalyzer
from app.services.forense_analyzer import ForenseAnalyzer
from app.services.geometry_analyzer import GeometryAnalyzer
from app.services.ocr_analyzer import OCRAnalyzer
from app.services.barcode_analyzer import BarcodeAnalyzer
from app.core.config import get_settings


class VerificacionService:
    def __init__(self):
        self.settings = get_settings()
        self.metadata_analyzer = MetadataAnalyzer()
        self.forense_analyzer = ForenseAnalyzer()
        self.geometry_analyzer = GeometryAnalyzer()
        self.ocr_analyzer = OCRAnalyzer()
        self.barcode_analyzer = BarcodeAnalyzer()
        self.ml_classifier = None
        if self.settings.use_ml_classifier:
            try:
                from app.services.ml_classifier import MLClassifier
                self.ml_classifier = MLClassifier()
                logger.info("ML Classifier cargado")
            except Exception as e:
                logger.warning(f"ML Classifier no disponible: {e}")

    async def verificar(
        self,
        image_bytes: bytes,
        tipo_doc: Optional[TipoDocumento] = None,
    ) -> VerificacionResponse:
        inicio = time.time()
        pesos = self.settings.pesos_score
        logger.info(f"Iniciando verificación - tipo: {tipo_doc}")

        if tipo_doc is None:
            tipo_doc = await asyncio.to_thread(self._auto_detectar_tipo, image_bytes)
            logger.info(f"Tipo auto-detectado: {tipo_doc}")

        check_meta = self.metadata_analyzer.analyze(image_bytes)
        check_forense = self.forense_analyzer.analyze(image_bytes)
        check_geo = self.geometry_analyzer.analyze(image_bytes, tipo_doc)
        check_ocr = self.ocr_analyzer.analyze(image_bytes, tipo_doc)
        check_barcode = self.barcode_analyzer.analyze(image_bytes, tipo_doc)

        if self.ml_classifier:
            check_ml = self.ml_classifier.analyze(image_bytes)
        else:
            check_ml = CheckML(
                ok=True,
                probabilidad_real=0.0,
                probabilidad_falso=0.0,
                clase_predicha=None,
                modelo_disponible=False,
                score=0.5,
            )

        score_ponderado = (
            check_meta.score * pesos["metadatos"]
            + check_forense.score * pesos["forense"]
            + check_geo.score * pesos["geometria"]
            + check_ocr.score * pesos["ocr_campos"]
            + check_barcode.score * pesos["codigo_barras"]
            + check_ml.score * pesos["ml_classifier"]
        )
        if not self.ml_classifier:
            peso_ml = pesos["ml_classifier"]
            score_ponderado = (
                check_meta.score * pesos["metadatos"]
                + check_forense.score * pesos["forense"]
                + check_geo.score * pesos["geometria"]
                + check_ocr.score * (pesos["ocr_campos"] + peso_ml * 0.6)
                + check_barcode.score * (pesos["codigo_barras"] + peso_ml * 0.4)
            )
        score_final = round(score_ponderado * 100)

        if score_final >= self.settings.umbral_real:
            resultado = ResultadoVerificacion.ORIGINAL
            confianza = "ALTA" if score_final >= 85 else "MEDIA"
            recomendacion = "Documento con alta probabilidad de ser original. Puede procesarse."
        elif score_final >= self.settings.umbral_revision:
            resultado = ResultadoVerificacion.REVISION_MANUAL
            confianza = "MEDIA"
            recomendacion = "Documento requiere revisión manual por un agente antes de procesar."
        else:
            resultado = ResultadoVerificacion.RECHAZADO
            confianza = "ALTA" if score_final < 30 else "MEDIA"
            recomendacion = "Documento rechazado. Alta probabilidad de ser copia, alterado o falso."

        alertas_globales = []
        if check_meta.es_screenshot:
            alertas_globales.append("ALERTA ALTA: Documento es captura de pantalla")
        if check_meta.editor_detectado:
            alertas_globales.append(f"ALERTA: Imagen editada con {check_meta.editor_detectado}")
        if check_forense.moire_detectado:
            alertas_globales.append("ALERTA ALTA: Patrón de moiré detectado (foto de pantalla)")
        if check_forense.brillo_excesivo:
            alertas_globales.append(
                f"RECHAZADO: Brillo/glare excesivo ({check_forense.porcentaje_sobreexpuesto:.1f}% sobreexpuesto). "
                "Repita la captura evitando luz directa y reflejos."
            )
            resultado = ResultadoVerificacion.RECHAZADO
            recomendacion = "Imagen con demasiado brillo o reflejo. Debe retomarse la foto."

        tiempo_ms = int((time.time() - inicio) * 1000)
        logger.info(f"Verificación completada - Score: {score_final} - Resultado: {resultado} - {tiempo_ms}ms")

        return VerificacionResponse(
            documento_tipo=tipo_doc,
            score_autenticidad=score_final,
            resultado=resultado,
            confianza=confianza,
            tiempo_proceso_ms=tiempo_ms,
            checks=Checks(
                metadatos=check_meta,
                forense=check_forense,
                geometria=check_geo,
                ocr_campos=check_ocr,
                codigo_barras=check_barcode,
                ml_classifier=check_ml,
            ),
            alertas_globales=alertas_globales,
            recomendacion=recomendacion,
        )

    def _auto_detectar_tipo(self, image_bytes: bytes) -> TipoDocumento:
        try:
            import pytesseract
            import numpy as np
            import cv2

            img_cv = cv2.imdecode(np.frombuffer(image_bytes, np.uint8), cv2.IMREAD_COLOR)
            if img_cv is None:
                return TipoDocumento.DESCONOCIDO
            gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
            texto = pytesseract.image_to_string(gray, config="--oem 3 --psm 3 -l spa").upper()
            if "INSTITUTO NACIONAL ELECTORAL" in texto or "INE" in texto or "CREDENCIAL" in texto:
                if "2014" in texto or any(str(y) in texto for y in range(2014, 2030)):
                    return TipoDocumento.INE_NUEVA
                return TipoDocumento.INE_ANTERIOR
            if "PERMANENTE" in texto:
                return TipoDocumento.RESIDENCIA_PERMANENTE
            if "ACUMULATIVA" in texto:
                return TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA
            if "TEMPORAL" in texto or "INM" in texto or "MIGRACION" in texto:
                return TipoDocumento.RESIDENCIA_TEMPORAL
        except Exception as e:
            logger.warning(f"Auto-detección falló: {e}")
        return TipoDocumento.DESCONOCIDO
