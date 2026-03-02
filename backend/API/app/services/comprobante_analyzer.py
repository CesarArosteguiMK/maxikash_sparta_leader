# app/services/comprobante_analyzer.py
"""Análisis de comprobantes de domicilio (recibos de servicios, estados de cuenta)."""
import re
import io
from datetime import datetime, timedelta
from loguru import logger
from typing import Optional

try:
    import fitz  # PyMuPDF
    PYMUPDF_AVAILABLE = True
except ImportError:
    PYMUPDF_AVAILABLE = False

from app.models.schemas import CheckComprobante, TipoComprobante


EMPRESAS_CONOCIDAS = {
    TipoComprobante.CFE_LUZ: {
        "patrones": [r"COMISI[OÓ]N FEDERAL DE ELECTRICIDAD", r"CFE\s*3708", r"RFC:\s*CFE"],
        "nombre": "CFE / Luz",
    },
    TipoComprobante.AGUA: {
        "patrones": [r"SISTEMA DE AGUAS", r"SACMEX", r"SIAPA", r"COMISI[OÓ]N\s+ESTATAL\s+DE\s+AGUA",
                      r"ORGANISMO\s+DE\s+AGUA", r"JUNTA\s+DE\s+AGUA"],
        "nombre": "Agua",
    },
    TipoComprobante.GAS: {
        "patrones": [r"GAS NATURAL", r"NATURGY", r"CALMEX\s+GAS"],
        "nombre": "Gas",
    },
    TipoComprobante.TELEFONO_INTERNET: {
        "patrones": [r"TEL[EÉ]FONOS DE M[EÉ]XICO", r"TELMEX", r"MEGACABLE", r"IZZI",
                      r"TOTALPLAY", r"AXTEL"],
        "nombre": "Teléfono / Internet",
    },
    TipoComprobante.BANCO: {
        "patrones": [r"ESTADO DE CUENTA", r"BBVA", r"SANTANDER", r"BANORTE", r"HSBC",
                      r"SCOTIABANK", r"CITIBANAMEX", r"BANCO AZTECA", r"INBURSA"],
        "nombre": "Banco",
    },
    TipoComprobante.PREDIAL: {
        "patrones": [r"PREDIAL", r"IMPUESTO PREDIAL", r"TESORER[IÍ]A"],
        "nombre": "Predial",
    },
}

MESES_ES = {
    "ENE": 1, "FEB": 2, "MAR": 3, "ABR": 4, "MAY": 5, "JUN": 6,
    "JUL": 7, "AGO": 8, "SEP": 9, "OCT": 10, "NOV": 11, "DIC": 12,
    "ENERO": 1, "FEBRERO": 2, "MARZO": 3, "ABRIL": 4, "MAYO": 5, "JUNIO": 6,
    "JULIO": 7, "AGOSTO": 8, "SEPTIEMBRE": 9, "OCTUBRE": 10, "NOVIEMBRE": 11, "DICIEMBRE": 12,
}


class ComprobanteAnalyzer:

    def analyze(self, file_bytes: bytes, filename: str = "") -> CheckComprobante:
        try:
            texto = self._extraer_texto(file_bytes, filename)
            if not texto or len(texto.strip()) < 50:
                return CheckComprobante(
                    ok=False, alertas=["No se pudo extraer texto del documento"], score=0.2
                )
            texto_upper = texto.upper()

            tipo, empresa = self._detectar_tipo_empresa(texto_upper)
            nombre = self._extraer_nombre_titular(texto_upper, tipo)
            direccion = self._extraer_direccion(texto_upper)
            fecha_doc, fecha_obj = self._extraer_fecha_documento(texto_upper)
            es_reciente, meses_antiguedad = self._verificar_antiguedad(fecha_obj)

            alertas = []
            score = 1.0
            campos_detectados = 0
            campos_validos = 0

            if tipo:
                campos_detectados += 1
                campos_validos += 1
            else:
                alertas.append("No se identificó el tipo de comprobante (CFE, agua, gas, etc.)")
                score -= 0.20

            if nombre:
                campos_detectados += 1
                campos_validos += 1
            else:
                alertas.append("Nombre del titular no detectado")
                score -= 0.10

            if direccion:
                campos_detectados += 1
                campos_validos += 1
            else:
                alertas.append("Dirección no detectada en el documento")
                score -= 0.15

            if fecha_doc:
                campos_detectados += 1
                if es_reciente:
                    campos_validos += 1
                else:
                    alertas.append(
                        f"Documento con antigüedad de {meses_antiguedad} meses. "
                        "Se requiere máximo 3 meses."
                    )
                    score -= 0.35
            else:
                alertas.append("No se detectó fecha del documento")
                score -= 0.15

            score = max(0.0, min(1.0, score))
            return CheckComprobante(
                ok=score >= 0.5,
                tipo_comprobante=tipo.value if tipo else None,
                empresa_detectada=empresa,
                nombre_titular=nombre,
                direccion_detectada=direccion,
                fecha_documento=fecha_doc,
                es_reciente=es_reciente,
                meses_antiguedad=meses_antiguedad,
                campos_detectados=campos_detectados,
                campos_validos=campos_validos,
                alertas=alertas,
                score=round(score, 3),
            )
        except Exception as e:
            logger.error(f"Error en ComprobanteAnalyzer: {e}")
            return CheckComprobante(
                ok=False, alertas=[f"Error procesando comprobante: {str(e)}"], score=0.2
            )

    def _extraer_texto(self, file_bytes: bytes, filename: str) -> str:
        es_pdf = filename.lower().endswith(".pdf") or file_bytes[:5] == b"%PDF-"
        if es_pdf:
            return self._extraer_texto_pdf(file_bytes)
        return self._extraer_texto_imagen(file_bytes)

    def _extraer_texto_pdf(self, file_bytes: bytes) -> str:
        if not PYMUPDF_AVAILABLE:
            raise RuntimeError("PyMuPDF no disponible para procesar PDFs")
        doc = fitz.open(stream=file_bytes, filetype="pdf")
        texto_total = ""
        for page in doc:
            texto_total += page.get_text() + "\n"
        doc.close()
        return texto_total

    def _extraer_texto_imagen(self, file_bytes: bytes) -> str:
        import pytesseract
        import cv2
        import numpy as np
        from PIL import Image
        from app.core.config import get_settings

        s = get_settings()
        cmd = getattr(s, "tesseract_cmd", "")
        if cmd and cmd != "/usr/bin/tesseract":
            pytesseract.pytesseract.tesseract_cmd = cmd

        img_cv = cv2.imdecode(np.frombuffer(file_bytes, np.uint8), cv2.IMREAD_COLOR)
        if img_cv is None:
            return ""
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        enhanced = clahe.apply(gray)
        pil_img = Image.fromarray(enhanced)
        return pytesseract.image_to_string(pil_img, config="--oem 3 --psm 6 -l spa+eng")

    def _detectar_tipo_empresa(self, texto: str):
        for tipo, info in EMPRESAS_CONOCIDAS.items():
            for patron in info["patrones"]:
                if re.search(patron, texto, re.IGNORECASE):
                    return tipo, info["nombre"]
        return None, None

    def _extraer_nombre_titular(self, texto: str, tipo: Optional[TipoComprobante]) -> Optional[str]:
        patterns = [
            r"(?:NOMBRE|TITULAR|USUARIO|RAZ[OÓ]N\s+SOCIAL|CLIENTE)\s*[:.]?\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,50})",
            r"(?:RFC:\s*[A-Z0-9]+\s+.*?)?([A-ZÁÉÍÓÚÑ]{3,}\s+[A-ZÁÉÍÓÚÑ]{3,}\s+[A-ZÁÉÍÓÚÑ]{3,}(?:\s+[A-ZÁÉÍÓÚÑ]{3,})?)\n",
        ]
        if tipo == TipoComprobante.CFE_LUZ:
            cfe_match = re.search(
                r"(?:Ciudad de M[eé]xico|C\.?P\.?\s*\d{5})[.\s\n]+([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,50})\n",
                texto, re.IGNORECASE
            )
            if cfe_match:
                nombre = cfe_match.group(1).strip()
                if len(nombre.split()) >= 2 and not re.search(r"CALLE|AV\.|COL\.|C\.P\.", nombre):
                    return nombre

        for pat in patterns:
            m = re.search(pat, texto)
            if m:
                nombre = m.group(1).strip()
                if len(nombre) >= 6 and not re.search(r"CALLE|AVENIDA|COLONIA|C\.P\.|CODIGO|TOTAL", nombre):
                    return nombre
        return None

    def _extraer_direccion(self, texto: str) -> Optional[str]:
        patterns = [
            r"((?:CALLE|AV\.?|AVENIDA|BLVD\.?|BOULEVARD|PRIV\.?|PRIVADA|CERRADA|AND\.?|ANDADOR)\s+.{10,80}(?:C\.?P\.?\s*\d{5}))",
            r"((?:CALLE|AV\.?)\s+[A-Z0-9\s]{5,40}\n[A-Z0-9\s,]+\n[A-Z\s]+C\.?P\.?\s*\d{5})",
        ]
        for pat in patterns:
            m = re.search(pat, texto, re.IGNORECASE)
            if m:
                dir_raw = m.group(1).strip()
                dir_clean = re.sub(r"\s+", " ", dir_raw.replace("\n", ", "))
                return dir_clean

        cp_match = re.search(r"C\.?P\.?\s*(\d{5})", texto)
        if cp_match:
            pos = cp_match.start()
            ventana = texto[max(0, pos - 200):pos + 20]
            lines = [l.strip() for l in ventana.split("\n") if l.strip() and len(l.strip()) > 5]
            if lines:
                dir_lines = []
                for line in lines[-4:]:
                    if not re.search(r"RFC|COMISI|FEDERAL|ALCALD", line, re.IGNORECASE):
                        dir_lines.append(line)
                if dir_lines:
                    return ", ".join(dir_lines)
        return None

    def _extraer_fecha_documento(self, texto: str):
        patterns = [
            (r"L[IÍ]MITE\s+DE\s+PAGO\s*:\s*(\d{1,2})\s+([A-Z]{3,})\s+(\d{4})", "dmy"),
            (r"FECHA\s+DE\s+(?:EMISI[OÓ]N|CORTE|FACTURACI[OÓ]N)\s*:\s*(\d{1,2})[/-](\d{1,2})[/-](\d{4})", "dmy_num"),
            (r"PERIODO\s+(?:FACTURADO|DE\s+CONSUMO)\s*:\s*\d{1,2}\s+[A-Z]{3}\s+\d{2,4}\s*[-aA]\s*(\d{1,2})\s+([A-Z]{3,})\s+(\d{2,4})", "dmy"),
            (r"CORTE\s+A\s+PARTIR\s+(?:DEL?\s+)?(\d{1,2})\s+([A-Z]{3,})\s+(\d{4})", "dmy"),
            (r"(\d{1,2})[/-](\d{1,2})[/-](\d{4})", "dmy_num"),
        ]
        for pat, fmt in patterns:
            m = re.search(pat, texto, re.IGNORECASE)
            if m:
                try:
                    if fmt == "dmy":
                        dia = int(m.group(1))
                        mes_str = m.group(2).upper()[:3]
                        anio = int(m.group(3))
                        if anio < 100:
                            anio += 2000
                        mes = MESES_ES.get(mes_str)
                        if mes:
                            fecha = datetime(anio, mes, dia)
                            return f"{dia:02d}/{mes:02d}/{anio}", fecha
                    elif fmt == "dmy_num":
                        dia = int(m.group(1))
                        mes = int(m.group(2))
                        anio = int(m.group(3))
                        if anio < 100:
                            anio += 2000
                        fecha = datetime(anio, mes, dia)
                        return f"{dia:02d}/{mes:02d}/{anio}", fecha
                except (ValueError, KeyError):
                    continue
        return None, None

    def _verificar_antiguedad(self, fecha: Optional[datetime]):
        if not fecha:
            return None, None
        ahora = datetime.now()
        diff = ahora - fecha
        meses = diff.days / 30.44
        es_reciente = meses <= 3.5
        return es_reciente, round(meses, 1)
