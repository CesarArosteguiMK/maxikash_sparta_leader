# app/services/verificacion.py
"""Orquestador: combina todas las capas y calcula score final."""
import asyncio
import re
import time
from loguru import logger
from typing import Optional

from app.models.schemas import (
    VerificacionResponse,
    VerificacionCalidadResponse,
    TipoDocumento,
    ResultadoVerificacion,
    Checks,
    CheckML,
    CheckMetadatos,
    CheckForense,
    CheckGeometria,
    CheckOCR,
    CheckCodigos,
)
from app.services.metadata_analyzer import MetadataAnalyzer
from app.services.forense_analyzer import ForenseAnalyzer
from app.services.geometry_analyzer import GeometryAnalyzer
from app.services.ocr_analyzer import OCRAnalyzer
from app.services.barcode_analyzer import BarcodeAnalyzer
from app.services.comprobante_analyzer import ComprobanteAnalyzer
from app.core.config import get_settings


class VerificacionService:
    def __init__(self):
        self.settings = get_settings()
        self.metadata_analyzer = MetadataAnalyzer()
        self.forense_analyzer = ForenseAnalyzer()
        self.geometry_analyzer = GeometryAnalyzer()
        self.ocr_analyzer = OCRAnalyzer()
        self.barcode_analyzer = BarcodeAnalyzer()
        self.comprobante_analyzer = ComprobanteAnalyzer()
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

        # Rechazar si el documento subido no es identificación (comprobante, constancia fiscal, CURP, NSS, acta)
        try:
            texto_ocr = await asyncio.to_thread(self.ocr_analyzer.extraer_texto_rapido, image_bytes)
            msg_no_id = self.comprobante_analyzer.parece_que_no_es_identificacion(texto_ocr or "")
            if msg_no_id:
                tiempo_ms = int((time.time() - inicio) * 1000)
                return VerificacionResponse(
                    documento_tipo=tipo_doc,
                    score_autenticidad=0,
                    resultado=ResultadoVerificacion.RECHAZADO,
                    confianza="ALTA",
                    tiempo_proceso_ms=tiempo_ms,
                    checks=Checks(
                        metadatos=CheckMetadatos(ok=False, score=0.0),
                        forense=CheckForense(ok=False, score=0.0),
                        geometria=CheckGeometria(ok=False, score=0.0),
                        ocr_campos=CheckOCR(ok=False, alertas=[msg_no_id], score=0.0),
                        codigo_barras=CheckCodigos(ok=False, score=0.0),
                        ml_classifier=CheckML(ok=False, score=0.0),
                    ),
                    alertas_globales=[msg_no_id],
                    recomendacion=msg_no_id,
                )
        except Exception as e:
            logger.warning(f"Comprobación de tipo de documento (no-ID) falló: {e}")

        def _safe_meta():
            try:
                return self.metadata_analyzer.analyze(image_bytes)
            except Exception as e:
                logger.exception(f"Metadata analyzer falló: {e}")
                return CheckMetadatos(ok=False, score=0.5, alertas=[f"Error: {str(e)}"])
        def _safe_forense():
            try:
                return self.forense_analyzer.analyze(image_bytes)
            except Exception as e:
                logger.exception(f"Forense analyzer falló: {e}")
                return CheckForense(ok=False, score=0.5, alertas=[f"Error: {str(e)}"])
        def _safe_geo():
            try:
                return self.geometry_analyzer.analyze(image_bytes, tipo_doc)
            except Exception as e:
                logger.exception(f"Geometry analyzer falló: {e}")
                return CheckGeometria(ok=False, score=0.5, alertas=[f"Error: {str(e)}"])
        def _safe_ocr():
            try:
                return self.ocr_analyzer.analyze(image_bytes, tipo_doc)
            except Exception as e:
                logger.exception(f"OCR analyzer falló: {e}")
                return CheckOCR(ok=False, score=0.3, alertas=[f"Error OCR: {str(e)}"])
        def _safe_barcode():
            try:
                return self.barcode_analyzer.analyze(image_bytes, tipo_doc)
            except Exception as e:
                logger.exception(f"Barcode analyzer falló: {e}")
                return CheckCodigos(ok=False, score=0.5, alertas=[f"Error: {str(e)}"])

        check_meta, check_forense, check_geo, check_ocr, check_barcode = await asyncio.gather(
            asyncio.to_thread(_safe_meta),
            asyncio.to_thread(_safe_forense),
            asyncio.to_thread(_safe_geo),
            asyncio.to_thread(_safe_ocr),
            asyncio.to_thread(_safe_barcode),
        )

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
        # Imágenes que no son documento (ej. foto genérica, dibujo) suelen quedar < 75
        if score_final < 75:
            resultado = ResultadoVerificacion.RECHAZADO
            recomendacion = "El documento no fue reconocido como identificación oficial válida. Sube una imagen clara del frente o reverso de tu INE o residencia."

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

    async def verificar_calidad(
        self,
        image_bytes: bytes,
        lado_esperado: Optional[str] = None,
    ) -> VerificacionCalidadResponse:
        """
        Revisión ligera: forense (brillo, borroso) y opcionalmente comprueba frente/reverso.
        Si lado_esperado es 'frente' o 'reverso', se hace un OCR rápido al inicio para rechazar
        de inmediato lo que no sea identificación (evita timeouts con fotos de videojuegos, etc.).
        """
        inicio = time.time()
        texto_ocr_previo: Optional[str] = None

        if lado_esperado in ("frente", "reverso"):
            try:
                texto_ocr_previo = await asyncio.to_thread(
                    self.ocr_analyzer.extraer_texto_rapido, image_bytes
                )
            except Exception as e:
                logger.warning(f"OCR previo (verificación ligera) falló: {e}")
            if texto_ocr_previo and len(texto_ocr_previo.strip()) >= 30:
                msg_no_id = self.comprobante_analyzer.parece_que_no_es_identificacion(texto_ocr_previo)
                if msg_no_id:
                    return VerificacionCalidadResponse(
                        ok=False,
                        mensaje=msg_no_id,
                        alertas=[],
                        lado_detectado=None,
                    )
                if not self._parece_identificacion(texto_ocr_previo):
                    return VerificacionCalidadResponse(
                        ok=False,
                        mensaje="No se detectó un documento de identificación. Sube el frente o reverso de tu INE o residencia.",
                        alertas=[],
                        lado_detectado=None,
                    )
            else:
                return VerificacionCalidadResponse(
                    ok=False,
                    mensaje="No se detectó un documento de identificación. Sube el frente o reverso de tu INE o residencia.",
                    alertas=[],
                    lado_detectado=None,
                )

        try:
            check_forense = await asyncio.to_thread(
                self.forense_analyzer.analyze, image_bytes
            )
        except Exception as e:
            logger.exception(f"Verificación calidad (forense) falló: {e}")
            return VerificacionCalidadResponse(
                ok=False,
                mensaje="No se pudo analizar la imagen. Intenta de nuevo.",
                alertas=[str(e)],
            )
        ok = check_forense.ok and not check_forense.brillo_excesivo and not check_forense.borroso
        if check_forense.brillo_excesivo:
            mensaje = (
                "Imagen con demasiado brillo o reflejo. "
                "Repite la captura evitando luz directa y reflejos."
            )
        elif check_forense.borroso:
            mensaje = (
                "Imagen borrosa o desenfocada. "
                "Repite la captura asegurando que el documento se vea nítido."
            )
        elif check_forense.moire_detectado:
            ok = False
            mensaje = "La imagen parece ser una foto de pantalla. Captura el documento directamente."
        elif ok:
            mensaje = "Imagen aceptada. Puedes subirla."
        else:
            mensaje = check_forense.alertas[0] if check_forense.alertas else "Calidad de imagen insuficiente. Repite la captura."

        lado_detectado: Optional[str] = None
        if ok and lado_esperado in ("frente", "reverso"):
            try:
                if texto_ocr_previo and len(texto_ocr_previo.strip()) >= 30:
                    lado_detectado = self._detectar_lado_desde_texto(texto_ocr_previo)
                else:
                    lado_detectado = await asyncio.to_thread(
                        self._detectar_lado_rapido, image_bytes
                    )
                if lado_detectado == "reverso" and lado_esperado == "frente":
                    ok = False
                    mensaje = "Debe subir la parte de frente de la identificación, no el reverso."
                elif lado_detectado == "frente" and lado_esperado == "reverso":
                    ok = False
                    mensaje = "Debe subir la parte de atrás (reverso) de la identificación, no el frente."
                elif lado_detectado == "indeterminado":
                    ok = False
                    mensaje = "No se detectó un documento de identificación. Sube el frente o reverso de tu INE o residencia."
            except Exception as e:
                logger.warning(f"Detección frente/reverso falló: {e}")

        tiempo_ms = int((time.time() - inicio) * 1000)
        logger.info(f"Verificación calidad - ok={ok} - lado={lado_detectado} - {tiempo_ms}ms")
        return VerificacionCalidadResponse(
            ok=ok,
            mensaje=mensaje,
            alertas=check_forense.alertas or [],
            lado_detectado=lado_detectado,
        )

    def _parece_identificacion(self, texto: str) -> bool:
        """True si el texto tiene indicios de ser INE o residencia (MRZ, CURP, CREDENCIAL, INM, etc.)."""
        if not texto or len(texto.strip()) < 30:
            return False
        t = texto.upper()
        if re.search(r"[A-Z0-9<]{20,}", t) and re.search(r"<{2,}|[A-Z]{2,}<+[A-Z]", t):
            return True
        if re.search(r"CURP|CLAVE\s+DE\s+ELECTOR|CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL", t):
            return True
        if re.search(r"RESIDENCIA\s+TEMPORAL|RESIDENTE\s+TEMPORAL|INM\s|MIGRACION|NUE\s*[\.:\s]*\d", t):
            return True
        if re.search(r"SECCION\s*\d|SECCI[OÓ]N\s*\d", t) and re.search(r"ELECTORAL|CREDENCIAL", t):
            return True
        return False

    def _detectar_lado_desde_texto(self, texto: str) -> str:
        """Misma lógica que _detectar_lado_rapido pero usando texto ya extraído (evita doble OCR)."""
        if not texto or len(texto.strip()) < 30:
            return "indeterminado"
        texto_upper = texto.upper()
        lineas_mrz = re.findall(r"[A-Z0-9<]{20,}", texto_upper)
        tiene_mrz = False
        if lineas_mrz:
            concatenado = "".join(lineas_mrz)
            if concatenado.count("<") >= 2 and ("<<" in concatenado or re.search(r"[A-Z]{2,}<+[A-Z]", concatenado)):
                tiene_mrz = True
        if tiene_mrz:
            return "reverso"
        tiene_curp = bool(re.search(r"CURP|CLAVE\s+UNICA\s+DE\s+POBLACION", texto_upper))
        tiene_clave_elector = bool(re.search(r"CLAVE\s+DE\s+ELECTOR|CLAVE\s+ELECTOR", texto_upper))
        tiene_seccion = bool(re.search(r"SECCION\s*\d|SECCI[OÓ]N\s*\d", texto_upper))
        tiene_ine_header = bool(re.search(
            r"CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL|ELECTORAL", texto_upper
        ))
        tiene_residencia = bool(re.search(
            r"RESIDENCIA\s+TEMPORAL|RESIDENTE\s+TEMPORAL|INM\s|MIGRACION|NUE\s*[\.:\s]*\d", texto_upper
        ))
        if tiene_curp or tiene_clave_elector or (tiene_seccion and tiene_ine_header):
            return "frente"
        if tiene_residencia and not tiene_mrz:
            return "frente"
        return "indeterminado"

    def _detectar_lado_rapido(self, image_bytes: bytes) -> str:
        """
        OCR rápido para saber si la imagen es frente o reverso.
        Reverso: tiene zona MRZ (líneas largas con < y alfanuméricos).
        Frente: tiene marcadores típicos del frente (CURP, CLAVE DE ELECTOR, SECCION, etc.)
        y no MRZ claro. No basta con INSTITUTO/CREDENCIAL porque el reverso también los trae.
        """
        try:
            texto = self.ocr_analyzer.extraer_texto_raw(image_bytes)
        except Exception:
            return "indeterminado"
        return self._detectar_lado_desde_texto(texto or "")

    def _auto_detectar_tipo(self, image_bytes: bytes) -> TipoDocumento:
        try:
            import numpy as np
            import cv2
            from PIL import Image
            import pytesseract
            img_cv = cv2.imdecode(np.frombuffer(image_bytes, np.uint8), cv2.IMREAD_COLOR)
            if img_cv is None:
                return TipoDocumento.DESCONOCIDO
            h, w = img_cv.shape[:2]
            # Escalar solo si es muy pequeño; evitar escalar de más (puede empeorar OCR en algunos escaneos)
            if w < 1000:
                scale = 1200 / w
                img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
            gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
            clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
            enhanced = clahe.apply(gray)
            # Varias pasadas para no perder palabras clave en escaneos difíciles
            textos = []
            for img in [gray, enhanced]:
                for psm in (3, 6):
                    t = pytesseract.image_to_string(
                        Image.fromarray(img),
                        config="--oem 3 --psm %d -l spa+eng" % psm
                    ).upper()
                    if t.strip():
                        textos.append(t)
            texto = " ".join(textos)
            # Palabras clave INE (incluir variantes por OCR defectuoso)
            if any(k in texto for k in ("INSTITUTO NACIONAL ELECTORAL", "INE ", "CREDENCIAL", "CREDEN", "ELECTORAL", "VOTAR")):
                if "2014" in texto or any(str(y) in texto for y in range(2014, 2030)):
                    return TipoDocumento.INE_NUEVA
                return TipoDocumento.INE_ANTERIOR
            if "PERMANENTE" in texto:
                return TipoDocumento.RESIDENCIA_PERMANENTE
            if "ACUMULATIVA" in texto:
                return TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA
            if "TEMPORAL" in texto or "INM" in texto or "MIGRACION" in texto or "MIGRACIÓN" in texto:
                return TipoDocumento.RESIDENCIA_TEMPORAL
        except Exception as e:
            logger.warning(f"Auto-detección falló: {e}")
        return TipoDocumento.DESCONOCIDO
