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

    # Patrones que indican que el documento es una IDENTIFICACIÓN, no un comprobante de domicilio
    PATRONES_IDENTIFICACION = [
        r"CREDENCIAL\s+PARA\s+VOTAR",
        r"INSTITUTO\s+NACIONAL\s+ELECTORAL",
        r"INE\s*[0-9]",
        r"IDENTIFICACI[OÓ]N\s+OFICIAL",
        r"CURP\s*[A-Z]{4}\d{6}",  # CURP seguido de patrón
        r"[A-Z]{2}[A-Z0-9]{6}[0-9][A-Z][A-Z0-9][A-Z][A-Z0-9]\d",  # MRZ línea 2 (pasaporte/ID)
        r"MRZ|MACHINE\s+READABLE",
        r"REP[UÚ]BLICA\s+MEXICANA.*VOTAR",
        r"CLAVE\s+DE\s+ELECTOR",
        r"ANR\s+[A-Z0-9]+",
        r"SEGURO\s+SOCIAL\s+NACIONAL",  # a veces en IDs
        r"RESIDENCIA\s+(TEMPORAL|PERMANENTE)",
        r"INM\s*M[EÉ]XICO",
        r"PASAPORTE|PASSPORT",
    ]

    # Patrones que indican CONSTANCIA FISCAL (SAT), no comprobante de domicilio
    PATRONES_CONSTANCIA_FISCAL = [
        r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL",
        r"SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA",
        r"SAT\s*M[EÉ]XICO",
        r"CLAVE\s+DE\s+REGISTRO\s+FEDERAL",
        r"RFC\s*:\s*[A-Z&Ñ]{3,4}\d{6}[A-Z0-9]{3}",
        r"ADMINISTRACI[OÓ]N\s+TRIBUTARIA",
    ]

    # Patrones que indican CONSTANCIA CURP (RENAPO), no comprobante
    PATRONES_CONSTANCIA_CURP = [
        r"CONSTANCIA\s+.*CURP",
        r"CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO",
        r"RENAPO",
        r"REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
        r"PRESENTE\s+ANTE\s+LA\s+SECRETAR",
    ]

    # Patrones que indican CONSTANCIA NSS (IMSS), no comprobante
    PATRONES_CONSTANCIA_NSS = [
        r"CONSTANCIA\s+.*(?:NSS|SEGURO\s+SOCIAL)",
        r"INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL",
        r"IMSS\s*\-",
        r"N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL",
        r"NSS\s*:\s*\d{11}",
    ]

    # Patrones que indican ACTA DE NACIMIENTO, no comprobante
    PATRONES_ACTA_NACIMIENTO = [
        r"ACTA\s+DE\s+NACIMIENTO",
        r"CERTIFICADO\s+DE\s+NACIMIENTO",
        r"REGISTRO\s+CIVIL",
        r"LIBRO.*FOJA.*ACTA",
        r"SE\s+EXTIENDE\s+LA\s+PRESENTE",
    ]

    def _parece_otro_documento(self, texto: str) -> Optional[str]:
        """
        Devuelve mensaje de error si el documento NO es un comprobante de domicilio
        (es identificación, constancia fiscal, CURP, NSS o acta). None si sí parece comprobante.
        """
        if not texto or len(texto) < 20:
            return None
        texto_upper = texto.upper()
        for pat in self.PATRONES_IDENTIFICACION:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento es una identificación oficial (INE, pasaporte, etc.), "
                    "no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial."
                )
        if re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}", texto_upper):
            return (
                "Este documento es una identificación oficial, no un comprobante de domicilio. "
                "Sube un recibo de luz, agua, gas, teléfono, banco o predial."
            )
        for pat in self.PATRONES_CONSTANCIA_FISCAL:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento es una constancia de situación fiscal (SAT), no un comprobante de domicilio. "
                    "Sube un recibo de luz, agua, gas, teléfono, banco o predial."
                )
        for pat in self.PATRONES_CONSTANCIA_CURP:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento es una constancia de CURP (RENAPO), no un comprobante de domicilio. "
                    "Sube un recibo de luz, agua, gas, teléfono, banco o predial."
                )
        for pat in self.PATRONES_CONSTANCIA_NSS:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento es una constancia de NSS (IMSS), no un comprobante de domicilio. "
                    "Sube un recibo de luz, agua, gas, teléfono, banco o predial."
                )
        for pat in self.PATRONES_ACTA_NACIMIENTO:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento es un acta de nacimiento, no un comprobante de domicilio. "
                    "Sube un recibo de luz, agua, gas, teléfono, banco o predial."
                )
        return None

    def parece_que_no_es_identificacion(self, texto: str) -> Optional[str]:
        """
        Devuelve mensaje de error si el documento NO es una identificación oficial
        (es comprobante de domicilio, constancia fiscal, CURP, NSS o acta). None si sí parece ID.
        Usado en el endpoint de verificación de identificación para rechazar documentos equivocados.
        """
        if not texto or len(texto) < 30:
            return None
        texto_upper = texto.upper()
        # Comprobante de domicilio (recibos)
        for tipo_info in EMPRESAS_CONOCIDAS.values():
            for pat in tipo_info["patrones"]:
                if re.search(pat, texto_upper, re.IGNORECASE):
                    return (
                        "Este documento no es una identificación oficial. "
                        "Sube el frente o reverso de tu INE, pasaporte o residencia."
                    )
        # Constancia fiscal, CURP, NSS, acta
        for pat in self.PATRONES_CONSTANCIA_FISCAL:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento no es una identificación oficial. "
                    "Sube el frente o reverso de tu INE, pasaporte o residencia."
                )
        for pat in self.PATRONES_CONSTANCIA_CURP:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento no es una identificación oficial. "
                    "Sube el frente o reverso de tu INE, pasaporte o residencia."
                )
        for pat in self.PATRONES_CONSTANCIA_NSS:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento no es una identificación oficial. "
                    "Sube el frente o reverso de tu INE, pasaporte o residencia."
                )
        for pat in self.PATRONES_ACTA_NACIMIENTO:
            if re.search(pat, texto_upper, re.IGNORECASE):
                return (
                    "Este documento no es una identificación oficial. "
                    "Sube el frente o reverso de tu INE, pasaporte o residencia."
                )
        return None

    def analyze(self, file_bytes: bytes, filename: str = "") -> CheckComprobante:
        try:
            texto = self._extraer_texto(file_bytes, filename)
            if not texto or len(texto.strip()) < 50:
                return CheckComprobante(
                    ok=False, alertas=["No se pudo extraer texto del documento"], score=0.2
                )
            texto_upper = texto.upper()

            msg_no_comprobante = self._parece_otro_documento(texto)
            if msg_no_comprobante:
                return CheckComprobante(
                    ok=False,
                    tipo_comprobante=None,
                    empresa_detectada=None,
                    nombre_titular=None,
                    direccion_detectada=None,
                    fecha_documento=None,
                    es_reciente=None,
                    meses_antiguedad=None,
                    campos_detectados=0,
                    campos_validos=0,
                    alertas=[msg_no_comprobante],
                    score=0.0,
                )

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
        """
        Extrae la fecha del comprobante usando solo fechas con contexto de vigencia:
        límite de pago, fecha de impresión/generado, corte, periodo facturado (fin).
        Usa la MÁS RECIENTE de esas para no rechazar por números que parezcan fechas (ej. 16-07-28).
        """
        texto_upper = texto.upper()
        candidatos = []

        def add_dmy(dia: int, mes_str: str, anio: int, etiqueta: str):
            mes = MESES_ES.get(mes_str)
            if mes and 1 <= dia <= 31 and 2015 <= anio <= 2030:
                fecha = datetime(anio, mes, dia)
                candidatos.append((f"{dia:02d}/{mes:02d}/{anio}", fecha, etiqueta))

        def add_dmy_num(dia: int, mes: int, anio: int, etiqueta: str):
            if 1 <= mes <= 12 and 1 <= dia <= 31 and 2015 <= anio <= 2030:
                if anio < 100:
                    anio += 2000
                fecha = datetime(anio, mes, dia)
                candidatos.append((f"{dia:02d}/{mes:02d}/{anio}", fecha, etiqueta))

        # Límite de pago: 28 NOV 2025
        m = re.search(r"L[IÍ]MITE\s+DE\s+PAGO\s*:\s*(\d{1,2})\s+([A-Z]{3,})\s+(\d{4})", texto_upper)
        if m:
            try:
                add_dmy(int(m.group(1)), m.group(2).upper()[:3], int(m.group(3)), "limite_pago")
            except (ValueError, KeyError):
                pass

        # Fecha de impresión / generado: 18/11/2025
        m = re.search(r"(?:FECHA\s+DE\s+IMPRESI[OÓ]N|GENERADO|IMPRESO|FECHA\s+DE\s+EMISI[OÓ]N)\s*:\s*(\d{1,2})[/-](\d{1,2})[/-](\d{4})", texto_upper)
        if m:
            try:
                add_dmy_num(int(m.group(1)), int(m.group(2)), int(m.group(3)), "impresion")
            except (ValueError, KeyError):
                pass
        m = re.search(r"(\d{1,2})[/-](\d{1,2})[/-](\d{4})\s*(?:\n|$|\.)", texto_upper)
        if m:
            try:
                d, me, a = int(m.group(1)), int(m.group(2)), int(m.group(3))
                if 2015 <= (a if a > 100 else a + 2000) <= 2030:
                    add_dmy_num(d, me, a, "fecha_numerica")
            except (ValueError, KeyError):
                pass

        # Corte a partir del / Fecha de corte
        m = re.search(r"CORTE\s+(?:A\s+PARTIR\s+(?:DEL?\s+)?|:)\s*(\d{1,2})\s+([A-Z]{3,})\s+(\d{4})", texto_upper)
        if m:
            try:
                add_dmy(int(m.group(1)), m.group(2).upper()[:3], int(m.group(3)), "corte")
            except (ValueError, KeyError):
                pass

        # Periodo facturado: fin del periodo (ej. 10 NOV 25)
        m = re.search(r"PERIODO\s+(?:FACTURADO|DE\s+CONSUMO)\s*:\s*\d{1,2}\s+[A-Z]{3}\s+\d{2,4}\s*[-aA]\s*(\d{1,2})\s+([A-Z]{3,})\s+(\d{2,4})", texto_upper)
        if m:
            try:
                anio = int(m.group(3))
                if anio < 100:
                    anio += 2000
                add_dmy(int(m.group(1)), m.group(2).upper()[:3], anio, "periodo_fin")
            except (ValueError, KeyError):
                pass

        # Fecha de facturación / emisión con números
        m = re.search(r"FECHA\s+DE\s+(?:EMISI[OÓ]N|CORTE|FACTURACI[OÓ]N)\s*:\s*(\d{1,2})[/-](\d{1,2})[/-](\d{4})", texto_upper)
        if m:
            try:
                add_dmy_num(int(m.group(1)), int(m.group(2)), int(m.group(3)), "emision")
            except (ValueError, KeyError):
                pass

        if not candidatos:
            # Fallback: cualquier fecha razonable en el documento y usar la más reciente
            for m in re.finditer(r"(\d{1,2})[/-](\d{1,2})[/-](\d{4})", texto_upper):
                try:
                    add_dmy_num(int(m.group(1)), int(m.group(2)), int(m.group(3)), "fallback")
                except (ValueError, KeyError):
                    pass
            for m in re.finditer(r"(\d{1,2})\s+([A-Z]{3,})\s+(\d{4})", texto_upper):
                try:
                    add_dmy(int(m.group(1)), m.group(2).upper()[:3], int(m.group(3)), "fallback")
                except (ValueError, KeyError):
                    pass

        if not candidatos:
            return None, None
        # Usar la fecha MÁS RECIENTE entre las de contexto de vigencia
        fecha_str, fecha_obj, _ = max(candidatos, key=lambda x: x[1])
        return fecha_str, fecha_obj

    def _verificar_antiguedad(self, fecha: Optional[datetime]):
        if not fecha:
            return None, None
        ahora = datetime.now()
        diff = ahora - fecha
        meses = diff.days / 30.44
        es_reciente = meses <= 3.0
        return es_reciente, round(meses, 1)
