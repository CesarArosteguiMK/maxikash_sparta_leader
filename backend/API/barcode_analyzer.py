# app/services/barcode_analyzer.py
"""
CAPA 5: Detección y validación de códigos QR, barcodes y PDF417.
Los documentos oficiales mexicanos tienen códigos verificables.
"""
import io
import numpy as np
from PIL import Image
import cv2
from pyzbar import pyzbar
from loguru import logger
from typing import Optional, List

from app.models.schemas import CheckCodigos, TipoDocumento


# Dominios oficiales esperados en QR de documentos mexicanos
DOMINIOS_OFICIALES_INM = [
    "inm.gob.mx",
    "gobmx.mx",
    "tramitesyservicios.inm.gob.mx",
]

DOMINIOS_OFICIALES_INE = [
    "listanominal.ife.org.mx",
    "verificavotante.ife.org.mx",
    "ife.org.mx",
    "ine.mx",
]


class BarcodeAnalyzer:
    """Detecta y verifica códigos QR, barcodes y PDF417 en documentos."""

    def analyze(self, image_bytes: bytes, tipo_doc: TipoDocumento) -> CheckCodigos:
        """
        Detecta y valida códigos en el documento.
        """
        alertas = []
        score = 0.5  # Score neutro si no hay códigos (no todos los docs tienen QR)

        try:
            img_cv = cv2.imdecode(
                np.frombuffer(image_bytes, np.uint8),
                cv2.IMREAD_COLOR
            )

            # Intentar decodificar en múltiples preprocesados
            codigos = self._detectar_todos_codigos(img_cv)

            qr_detectado = False
            barcode_detectado = False
            pdf417_detectado = False
            contenido_valido = False
            url_oficial = None

            for codigo in codigos:
                tipo = codigo.type
                datos = codigo.data.decode("utf-8", errors="ignore")

                if tipo == "QRCODE":
                    qr_detectado = True
                    # Verificar si el QR apunta a dominio oficial
                    url_oficial, es_valido = self._verificar_url_oficial(datos, tipo_doc)
                    if es_valido:
                        contenido_valido = True
                        score = 1.0
                        alertas.append(f"✅ QR oficial verificado: {url_oficial}")
                    else:
                        alertas.append(f"⚠️ QR no apunta a dominio oficial: {datos[:50]}")
                        score = 0.4

                elif tipo in ["CODE128", "CODE39", "EAN13", "EAN8", "UPCA"]:
                    barcode_detectado = True
                    if datos:
                        contenido_valido = True
                        score = min(score + 0.3, 1.0)
                        alertas.append(f"✅ Código de barras detectado y legible")

                elif tipo == "PDF417":
                    pdf417_detectado = True
                    if datos:
                        contenido_valido = True
                        score = min(score + 0.4, 1.0)
                        alertas.append(f"✅ PDF417 detectado (documentos INM)")

            if not codigos:
                # Documentos viejos o algunos tipos no tienen QR
                if tipo_doc in [TipoDocumento.INE_ANTERIOR]:
                    alertas.append("ℹ️ INE anterior puede no tener QR/código")
                    score = 0.5  # Neutro, no penalizar
                else:
                    alertas.append("⚠️ No se detectaron códigos QR ni barcode")
                    score = 0.35

            return CheckCodigos(
                ok=score >= 0.5,
                qr_detectado=qr_detectado,
                barcode_detectado=barcode_detectado,
                pdf417_detectado=pdf417_detectado,
                contenido_valido=contenido_valido,
                url_oficial=url_oficial,
                alertas=alertas,
                score=round(score, 3)
            )

        except Exception as e:
            logger.error(f"Error en BarcodeAnalyzer: {e}")
            return CheckCodigos(
                ok=False,
                alertas=[f"Error en análisis de códigos: {str(e)}"],
                score=0.4
            )

    def _detectar_todos_codigos(self, img: np.ndarray) -> list:
        """Intenta detectar códigos con múltiples preprocesados."""
        codigos = []

        # Intentar 1: imagen original
        codigos.extend(pyzbar.decode(img))

        if not codigos:
            # Intentar 2: escala de grises
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            codigos.extend(pyzbar.decode(gray))

        if not codigos:
            # Intentar 3: umbralización
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            _, thresh = cv2.threshold(gray, 127, 255, cv2.THRESH_BINARY)
            codigos.extend(pyzbar.decode(thresh))

        if not codigos:
            # Intentar 4: escalar la imagen
            h, w = img.shape[:2]
            if w < 1000:
                scale = 1000 / w
                img_scaled = cv2.resize(img, None, fx=scale, fy=scale)
                codigos.extend(pyzbar.decode(img_scaled))

        # Eliminar duplicados
        vistos = set()
        unicos = []
        for c in codigos:
            key = (c.type, c.data)
            if key not in vistos:
                vistos.add(key)
                unicos.append(c)

        return unicos

    def _verificar_url_oficial(
        self, datos: str, tipo_doc: TipoDocumento
    ) -> tuple:
        """
        Verifica si el contenido del QR apunta a un dominio oficial.
        
        Returns:
            Tuple[url_encontrada, es_oficial]
        """
        datos_lower = datos.lower()

        if tipo_doc in [TipoDocumento.INE_NUEVA, TipoDocumento.INE_ANTERIOR]:
            dominios = DOMINIOS_OFICIALES_INE
        else:
            dominios = DOMINIOS_OFICIALES_INM

        for dominio in dominios:
            if dominio in datos_lower:
                return datos, True

        # Verificar si es una URL aunque no sea de dominio esperado
        if datos.startswith("http") or datos.startswith("https"):
            return datos, False

        return None, False
