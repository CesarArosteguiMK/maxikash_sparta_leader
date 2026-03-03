# app/services/ocr_analyzer.py
"""
CAPA 4: OCR + Validación de Campos.
Extrae y valida los campos específicos de cada tipo de documento.
"""
import re
import io
import numpy as np
from PIL import Image
import cv2
import pytesseract
from loguru import logger
from datetime import datetime
from typing import Optional, Dict, Any

from app.models.schemas import CheckOCR, TipoDocumento
from app.utils.curp_validator import validar_curp, extraer_datos_curp
from app.core.config import get_settings


class OCRAnalyzer:
    """Extrae y valida campos de documentos usando OCR."""

    def __init__(self):
        settings = get_settings()
        pytesseract.pytesseract.tesseract_cmd = settings.tesseract_cmd

    def analyze(self, image_bytes: bytes, tipo_doc: TipoDocumento) -> CheckOCR:
        """
        Análisis OCR completo según el tipo de documento.
        """
        try:
            img = self._preprocesar_imagen(image_bytes)
            texto_completo = self._extraer_texto(img)

            if tipo_doc in [TipoDocumento.INE_NUEVA, TipoDocumento.INE_ANTERIOR]:
                return self._validar_ine(texto_completo)
            elif tipo_doc in [
                TipoDocumento.RESIDENCIA_TEMPORAL,
                TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA,
                TipoDocumento.RESIDENCIA_PERMANENTE
            ]:
                return self._validar_residencia(texto_completo, tipo_doc)
            else:
                return self._validacion_generica(texto_completo)

        except Exception as e:
            logger.error(f"Error en OCRAnalyzer: {e}")
            return CheckOCR(
                ok=False,
                alertas=[f"Error en OCR: {str(e)}"],
                score=0.3
            )

    def _preprocesar_imagen(self, image_bytes: bytes) -> Image.Image:
        """Preprocesa la imagen para mejorar OCR."""
        img_cv = cv2.imdecode(
            np.frombuffer(image_bytes, np.uint8),
            cv2.IMREAD_COLOR
        )

        # Escalar si es muy pequeña
        h, w = img_cv.shape[:2]
        if w < 800:
            scale = 800 / w
            img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)

        # Convertir a escala de grises
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

        # Mejorar contraste
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        gray = clahe.apply(gray)

        # Umbralización adaptativa
        thresh = cv2.adaptiveThreshold(
            gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2
        )

        return Image.fromarray(thresh)

    def _extraer_texto(self, img: Image.Image) -> str:
        """Extrae texto con Tesseract (español)."""
        config = '--oem 3 --psm 3 -l spa'
        texto = pytesseract.image_to_string(img, config=config)
        return texto.upper()

    def _validar_ine(self, texto: str) -> CheckOCR:
        """Valida campos específicos de INE."""
        alertas = []
        score = 1.0
        campos_detectados = 0
        campos_validos = 0

        # ---- CURP ----
        curp_match = re.search(
            r'[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d', texto
        )
        curp_resultado = None
        if curp_match:
            campos_detectados += 1
            curp_valor = curp_match.group()
            es_valido, mensaje = validar_curp(curp_valor)
            curp_resultado = {"valor": curp_valor, "valido": es_valido, "detalle": mensaje}
            if es_valido:
                campos_validos += 1
            else:
                alertas.append(f"⚠️ CURP inválido: {mensaje}")
                score -= 0.20
        else:
            alertas.append("⚠️ CURP no detectado")
            score -= 0.15

        # ---- Clave de Elector ----
        clave_match = re.search(r'[A-Z]{6}\d{8}[HM]\d{3}', texto)
        clave_resultado = None
        if clave_match:
            campos_detectados += 1
            clave_valor = clave_match.group()
            es_valida = len(clave_valor) == 18
            clave_resultado = {"valor": clave_valor, "formato": es_valida}
            if es_valida:
                campos_validos += 1
            else:
                alertas.append("⚠️ Clave de elector formato incorrecto")
                score -= 0.15
        else:
            alertas.append("⚠️ Clave de elector no detectada")
            score -= 0.10

        # ---- Sección Electoral (4 dígitos) ----
        seccion_match = re.search(r'SECCION[:\s]+(\d{4})', texto) or re.search(r'SECCIÓN[:\s]+(\d{4})', texto)
        seccion_resultado = None
        if seccion_match:
            campos_detectados += 1
            seccion_num = int(seccion_match.group(1))
            es_valida = 1 <= seccion_num <= 65000
            seccion_resultado = {"valor": seccion_num, "valido": es_valida}
            if es_valida:
                campos_validos += 1
            else:
                alertas.append(f"⚠️ Sección electoral inválida: {seccion_num}")
                score -= 0.10

        # ---- Vigencia ----
        vigencia_match = re.search(r'VIGENCIA[:\s]+(\d{4})', texto) or re.search(r'VIGE[:\s]+(\d{4})', texto)
        vigencia_resultado = None
        if vigencia_match:
            campos_detectados += 1
            año_vigencia = int(vigencia_match.group(1))
            año_actual = datetime.now().year
            es_valida = año_actual <= año_vigencia <= año_actual + 10
            vigencia_resultado = {"valor": año_vigencia, "coherente": es_valida}
            if es_valida:
                campos_validos += 1
            else:
                alertas.append(f"⚠️ Vigencia incoherente: {año_vigencia}")
                score -= 0.15

        # ---- Texto "INSTITUTO NACIONAL ELECTORAL" ----
        if "INSTITUTO NACIONAL ELECTORAL" in texto or "INE" in texto:
            campos_detectados += 1
            campos_validos += 1
        else:
            alertas.append("⚠️ No se detectó 'INSTITUTO NACIONAL ELECTORAL'")
            score -= 0.10

        # Calcular score final
        if campos_detectados > 0:
            tasa_validez = campos_validos / max(campos_detectados, 4)
            score = min(score, 0.4 + (tasa_validez * 0.6))

        score = max(0.0, min(1.0, score))

        return CheckOCR(
            ok=score >= 0.5,
            curp=curp_resultado,
            clave_elector=clave_resultado,
            seccion_electoral=seccion_resultado,
            vigencia=vigencia_resultado,
            campos_detectados=campos_detectados,
            campos_validos=campos_validos,
            alertas=alertas,
            score=round(score, 3)
        )

    def _validar_residencia(self, texto: str, tipo_doc: TipoDocumento) -> CheckOCR:
        """Valida campos de documentos de residencia del INM."""
        alertas = []
        score = 1.0
        campos_detectados = 0
        campos_validos = 0

        # ---- Número de documento INM ----
        # Formato típico: letras y números del INM
        num_doc_match = re.search(r'\d{2}[A-Z]\d{6,10}', texto)
        num_doc_resultado = None
        if num_doc_match:
            campos_detectados += 1
            campos_validos += 1
            num_doc_resultado = {"valor": num_doc_match.group(), "formato": True}
        else:
            alertas.append("⚠️ Número de documento INM no detectado")
            score -= 0.15

        # ---- Texto oficial INM ----
        textos_inm = [
            "INSTITUTO NACIONAL DE MIGRACION",
            "SECRETARIA DE GOBERNACION",
            "RESIDENCIA TEMPORAL",
            "RESIDENCIA PERMANENTE",
            "TARJETA DE RESIDENCIA"
        ]
        texto_inm_encontrado = any(t in texto for t in textos_inm)
        if texto_inm_encontrado:
            campos_detectados += 1
            campos_validos += 1
        else:
            alertas.append("⚠️ No se detectaron textos oficiales del INM")
            score -= 0.20

        # ---- Tipo de residencia en el texto ----
        tipo_resultado = None
        if tipo_doc == TipoDocumento.RESIDENCIA_TEMPORAL:
            encontrado = "TEMPORAL" in texto
        elif tipo_doc == TipoDocumento.RESIDENCIA_PERMANENTE:
            encontrado = "PERMANENTE" in texto
        else:
            encontrado = "TEMPORAL" in texto or "ACUMULATIVA" in texto

        campos_detectados += 1
        tipo_resultado = {"tipo": tipo_doc.value, "detectado_en_texto": encontrado}
        if encontrado:
            campos_validos += 1
        else:
            alertas.append(f"⚠️ Tipo de residencia '{tipo_doc.value}' no confirmado en texto")
            score -= 0.15

        # ---- Fechas ----
        fechas = re.findall(r'\d{1,2}[/\-\.]\d{1,2}[/\-\.]\d{4}|\d{4}[/\-\.]\d{1,2}[/\-\.]\d{1,2}', texto)
        fecha_exp = None
        fecha_venc = None

        if len(fechas) >= 2:
            campos_detectados += 2
            try:
                # Asumir orden: expedición, vencimiento
                fecha_exp = {"valor": fechas[0], "detectada": True}
                fecha_venc = {"valor": fechas[-1], "detectada": True}
                campos_validos += 2
            except Exception:
                alertas.append("⚠️ No se pudieron parsear las fechas")
        else:
            alertas.append("⚠️ Fechas de expedición/vencimiento no detectadas")
            score -= 0.10

        score = max(0.0, min(1.0, score))

        return CheckOCR(
            ok=score >= 0.5,
            numero_documento=num_doc_resultado,
            tipo_residencia=tipo_resultado,
            fecha_expedicion=fecha_exp,
            fecha_vencimiento=fecha_venc,
            campos_detectados=campos_detectados,
            campos_validos=campos_validos,
            alertas=alertas,
            score=round(score, 3)
        )

    def _validacion_generica(self, texto: str) -> CheckOCR:
        """Validación básica cuando no se conoce el tipo de documento."""
        # Intentar detectar CURP en cualquier documento mexicano
        curp_match = re.search(r'[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d', texto)
        curp_resultado = None
        if curp_match:
            es_valido, mensaje = validar_curp(curp_match.group())
            curp_resultado = {"valor": curp_match.group(), "valido": es_valido}

        return CheckOCR(
            ok=True,
            curp=curp_resultado,
            campos_detectados=1 if curp_resultado else 0,
            campos_validos=1 if (curp_resultado and curp_resultado.get("valido")) else 0,
            alertas=["ℹ️ Validación genérica (tipo de documento desconocido)"],
            score=0.5
        )
