# app/services/ocr_analyzer.py
"""CAPA 4: OCR y validación de campos por tipo de documento."""
import re
import numpy as np
from PIL import Image
import cv2
import pytesseract
from loguru import logger
from datetime import datetime

from app.models.schemas import CheckOCR, TipoDocumento
from app.utils.curp_validator import validar_curp
from app.core.config import get_settings


def _tesseract_cmd():
    s = get_settings()
    c = getattr(s, "tesseract_cmd", None) or ""
    if c and c != "/usr/bin/tesseract":
        return c
    try:
        pytesseract.get_tesseract_version()
        return None
    except Exception:
        return "tesseract"


class OCRAnalyzer:
    def __init__(self):
        cmd = _tesseract_cmd()
        if cmd:
            pytesseract.pytesseract.tesseract_cmd = cmd

    def analyze(self, image_bytes: bytes, tipo_doc: TipoDocumento) -> CheckOCR:
        try:
            texto_completo = self._extraer_mejor_texto(image_bytes)
            if tipo_doc in [TipoDocumento.INE_NUEVA, TipoDocumento.INE_ANTERIOR]:
                return self._validar_ine(texto_completo)
            elif tipo_doc in [
                TipoDocumento.RESIDENCIA_TEMPORAL,
                TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA,
                TipoDocumento.RESIDENCIA_PERMANENTE,
            ]:
                mrz_text = self._extraer_mrz_dedicado(image_bytes)
                texto_con_mrz = texto_completo + "\n" + mrz_text
                return self._validar_residencia(texto_con_mrz, tipo_doc)
            else:
                return self._validacion_generica(texto_completo)
        except Exception as e:
            logger.error(f"Error en OCRAnalyzer: {e}")
            return CheckOCR(ok=False, alertas=[f"Error en OCR: {str(e)}"], score=0.3)

    def _base_cv(self, image_bytes: bytes) -> np.ndarray:
        img_cv = cv2.imdecode(np.frombuffer(image_bytes, np.uint8), cv2.IMREAD_COLOR)
        if img_cv is None:
            raise ValueError("No se pudo decodificar la imagen")
        h, w = img_cv.shape[:2]
        target_w = 1400
        if w < target_w:
            scale = target_w / w
            img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
        elif w > 3000:
            scale = 2000 / w
            img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale, interpolation=cv2.INTER_AREA)
        return img_cv

    def _variants(self, img_cv: np.ndarray) -> list:
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        enhanced = clahe.apply(gray)
        _, otsu = cv2.threshold(enhanced, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        adaptive = cv2.adaptiveThreshold(
            enhanced, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 15, 4
        )
        return [
            Image.fromarray(enhanced),
            Image.fromarray(otsu),
            Image.fromarray(adaptive),
        ]

    def _extraer_texto(self, img: Image.Image, lang: str = "spa+eng") -> str:
        texto = pytesseract.image_to_string(img, config=f"--oem 3 --psm 6 -l {lang}")
        return texto.upper()

    def _extraer_mejor_texto(self, image_bytes: bytes) -> str:
        img_cv = self._base_cv(image_bytes)
        variantes = self._variants(img_cv)
        mejor = ""
        for v in variantes:
            t = self._extraer_texto(v)
            if len(t.strip()) > len(mejor.strip()):
                mejor = t
        return mejor

    def _extraer_mrz_dedicado(self, image_bytes: bytes) -> str:
        """Recorta el 35% inferior de la imagen (zona MRZ) y aplica OCR optimizado."""
        img_cv = self._base_cv(image_bytes)
        h, w = img_cv.shape[:2]
        mrz_zone = img_cv[int(h * 0.58):, :]

        gray = cv2.cvtColor(mrz_zone, cv2.COLOR_BGR2GRAY)
        _, binary = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        inv = cv2.bitwise_not(binary)

        dark_pct = np.sum(gray < 100) / gray.size
        target = inv if dark_pct > 0.5 else binary

        configs = [
            "--oem 3 --psm 6 -l eng -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0__SPARTA_PASSWORD_REDACTED__<",
            "--oem 3 --psm 4 -l eng -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0__SPARTA_PASSWORD_REDACTED__<",
        ]
        mejor = ""
        for cfg in configs:
            for img in [Image.fromarray(target), Image.fromarray(binary)]:
                t = pytesseract.image_to_string(img, config=cfg).upper().strip()
                mrz_chars = sum(1 for c in t if c in "ABCDEFGHIJKLMNOPQRSTUVWXYZ0__SPARTA_PASSWORD_REDACTED__<")
                if mrz_chars > len(mejor.replace("\n", "").replace(" ", "")):
                    mejor = t
        return mejor

    def extraer_texto_raw(self, image_bytes: bytes) -> str:
        return self._extraer_mejor_texto(image_bytes)

    def _validar_ine(self, texto: str) -> CheckOCR:
        alertas = []
        score = 1.0
        campos_detectados = 0
        campos_validos = 0

        curp_match = re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d", texto)
        curp_resultado = None
        if curp_match:
            campos_detectados += 1
            curp_valor = curp_match.group()
            es_valido, mensaje = validar_curp(curp_valor)
            curp_resultado = {"valor": curp_valor, "valido": es_valido, "detalle": mensaje}
            if es_valido:
                campos_validos += 1
            else:
                alertas.append(f"CURP inválido: {mensaje}")
                score -= 0.20
        else:
            alertas.append("CURP no detectado")
            score -= 0.15

        clave_match = re.search(r"[A-Z]{6}\d{8}[HM]\d{3}", texto)
        clave_resultado = None
        if clave_match:
            campos_detectados += 1
            clave_valor = clave_match.group()
            es_valida = len(clave_valor) == 18
            clave_resultado = {"valor": clave_valor, "formato": es_valida}
            if es_valida:
                campos_validos += 1
            else:
                alertas.append("Clave de elector formato incorrecto")
                score -= 0.15
        else:
            alertas.append("Clave de elector no detectada")
            score -= 0.10

        seccion_match = re.search(r"SECCION[:\s]+(\d{4})", texto) or re.search(r"SECCIÓN[:\s]+(\d{4})", texto)
        seccion_resultado = None
        if seccion_match:
            campos_detectados += 1
            seccion_num = int(seccion_match.group(1))
            es_valida = 1 <= seccion_num <= 65000
            seccion_resultado = {"valor": seccion_num, "valido": es_valida}
            if es_valida:
                campos_validos += 1
            else:
                alertas.append(f"Sección electoral inválida: {seccion_num}")
                score -= 0.10

        vigencia_match = re.search(r"VIGENCIA[:\s]+(\d{4})", texto) or re.search(r"VIGE[:\s]+(\d{4})", texto)
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
                alertas.append(f"Vigencia incoherente: {año_vigencia}")
                score -= 0.15

        if "INSTITUTO NACIONAL ELECTORAL" in texto or "INE" in texto:
            campos_detectados += 1
            campos_validos += 1
        else:
            alertas.append("No se detectó 'INSTITUTO NACIONAL ELECTORAL'")
            score -= 0.10

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
            score=round(score, 3),
        )

    @staticmethod
    def _normalizar(texto: str) -> str:
        reemplazos = {"Á": "A", "É": "E", "Í": "I", "Ó": "O", "Ú": "U", "Ñ": "N",
                       "À": "A", "È": "E", "Ì": "I", "Ò": "O", "Ù": "U"}
        for k, v in reemplazos.items():
            texto = texto.replace(k, v)
        return texto

    @staticmethod
    def _corregir_curp(raw: str) -> str:
        """Corrige confusiones comunes de Tesseract en CURP (18 chars).
        Estructura: AAAA######H/MAAAAA##
        Posiciones 0-3: letras, 4-9: dígitos, 10: H/M, 11-15: letras, 16: dígito, 17: alfanum.
        Usa el validador oficial para elegir la mejor corrección."""
        c = list(raw.upper().strip())
        if len(c) < 17:
            return raw
        c = c[:18] if len(c) > 18 else c

        letter_to_digit = {"O": "0", "I": "1", "Z": "2", "S": "5", "B": "8", "G": "6"}
        digit_to_letter = {"0": "O", "1": "I", "5": "S", "8": "B", "6": "G", "2": "Z"}

        for i in [4, 5, 6, 7, 8, 9]:
            if i < len(c) and c[i] in letter_to_digit:
                c[i] = letter_to_digit[c[i]]
        for i in [0, 1, 2, 3, 11, 12, 13, 14, 15]:
            if i < len(c) and c[i] in digit_to_letter:
                c[i] = digit_to_letter[c[i]]
        if len(c) >= 17 and c[16] in letter_to_digit:
            c[16] = letter_to_digit[c[16]]

        base = "".join(c)
        if len(base) == 18:
            es_valido, _ = validar_curp(base)
            if es_valido:
                return base

        if len(c) >= 18:
            ambiguous_digit_options = {
                "O": ["0"], "S": ["9", "5"], "I": ["1"], "Z": ["2"],
                "B": ["8"], "G": ["9", "6"], "D": ["0"], "Q": ["9", "0"],
            }
            orig_17 = raw.upper().strip()[17] if len(raw.strip()) >= 18 else c[17]
            candidates = [orig_17]
            if orig_17 in ambiguous_digit_options:
                candidates = ambiguous_digit_options[orig_17]
            candidates.extend([str(d) for d in range(10)])
            seen = set()
            for ch17 in candidates:
                if ch17 in seen:
                    continue
                seen.add(ch17)
                test = list(c)
                test[17] = ch17
                candidate = "".join(test)
                es_valido, _ = validar_curp(candidate)
                if es_valido:
                    return candidate

        return base

    @staticmethod
    def _parsear_mrz(texto: str) -> dict:
        """Extrae datos de la zona MRZ de documentos INM.
        Reconstruye líneas MRZ fragmentadas por Tesseract."""
        resultado = {}

        lineas_mrz = re.findall(r"[A-Z0-9<]{20,}", texto)

        if not lineas_mrz:
            fragmentos = re.findall(r"[A-Z0-9<]{5,}", texto)
            if fragmentos:
                reconstruida = ""
                for frag in fragmentos:
                    frag_clean = frag.replace(" ", "")
                    if "<" in frag_clean or re.match(r"^[A-Z]+$", frag_clean):
                        reconstruida += frag_clean
                if len(reconstruida) >= 20:
                    lineas_mrz = [reconstruida]

        all_mrz = " ".join(lineas_mrz)

        for linea in lineas_mrz + [all_mrz]:
            linea = linea.replace(" ", "")

            name_match = re.search(r"([A-Z]{2,})<+([A-Z]{2,})(?:<+([A-Z]{2,}))?<", linea)
            if name_match and "apellido_paterno" not in resultado:
                apellido = name_match.group(1)
                segundo = name_match.group(2) or ""
                nombre = name_match.group(3) or ""
                if len(apellido) >= 2:
                    resultado["apellido_paterno"] = apellido
                    if segundo and nombre:
                        resultado["apellido_materno"] = segundo
                        resultado["nombre"] = nombre
                    elif segundo:
                        resultado["nombre"] = segundo

            doc_match = re.search(r"I<MEX(\d{8})", linea)
            if doc_match and "numero_documento" not in resultado:
                resultado["numero_documento"] = doc_match.group(1)

            fecha_match = re.search(r"(\d{6})[MF](\d{6})", linea)
            if fecha_match and "fecha_nacimiento" not in resultado:
                nac_raw = fecha_match.group(1)
                venc_raw = fecha_match.group(2)
                try:
                    y, m, d = int(nac_raw[:2]), int(nac_raw[2:4]), int(nac_raw[4:6])
                    siglo = 1900 if y > 50 else 2000
                    resultado["fecha_nacimiento"] = f"{d:02d}/{m:02d}/{siglo + y}"
                except ValueError:
                    pass
                try:
                    y, m, d = int(venc_raw[:2]), int(venc_raw[2:4]), int(venc_raw[4:6])
                    resultado["fecha_vencimiento_mrz"] = f"{d:02d}/{m:02d}/20{y:02d}"
                except ValueError:
                    pass

            nac_match = re.search(r"\d{6}[MF]\d{6}([A-Z]{3})", linea)
            if nac_match and "nacionalidad" not in resultado:
                resultado["nacionalidad"] = nac_match.group(1)

        return resultado

    def _validar_residencia(self, texto: str, tipo_doc: TipoDocumento) -> CheckOCR:
        alertas = []
        score = 1.0
        campos_detectados = 0
        campos_validos = 0
        texto_norm = self._normalizar(texto)

        num_patterns = [
            r"\d{2}[A-Z]\d{6,10}",
            r"SEGOB[\-\s]*INM[\-\s]*(\d{6,12})",
            r"(?:NO|NUM|NUE)[\.:\s]+(\d{6,15})",
            r"0{4,}\d{5,}",
        ]
        num_doc_resultado = None
        for pat in num_patterns:
            m = re.search(pat, texto_norm)
            if m:
                campos_detectados += 1
                campos_validos += 1
                num_doc_resultado = {"valor": m.group(), "formato": True}
                break
        if not num_doc_resultado:
            alertas.append("Número de documento INM no detectado")
            score -= 0.10

        textos_inm = [
            "INSTITUTO NACIONAL DE MIGRACION",
            "SECRETARIA DE GOBERNACION",
            "RESIDENCIA TEMPORAL", "RESIDENTE TEMPORAL",
            "RESIDENCIA PERMANENTE", "RESIDENTE PERMANENTE",
            "TARJETA DE RESIDENCIA",
            "ESTADOS UNIDOS MEXICANOS",
            "GOBIERNO DE MEXICO",
        ]
        if any(t in texto_norm for t in textos_inm):
            campos_detectados += 1
            campos_validos += 1
        else:
            alertas.append("No se detectaron textos oficiales del INM")
            score -= 0.15

        if tipo_doc == TipoDocumento.RESIDENCIA_TEMPORAL:
            encontrado = "TEMPORAL" in texto_norm
        elif tipo_doc == TipoDocumento.RESIDENCIA_PERMANENTE:
            encontrado = "PERMANENTE" in texto_norm
        else:
            encontrado = "TEMPORAL" in texto_norm or "ACUMULATIVA" in texto_norm
        campos_detectados += 1
        tipo_resultado = {"tipo": tipo_doc.value, "detectado_en_texto": encontrado}
        if encontrado:
            campos_validos += 1
        else:
            alertas.append(f"Tipo de residencia '{tipo_doc.value}' no confirmado en texto")
            score -= 0.10

        curp_match = re.search(r"[A-Z0-9]{4}\d{4,6}[A-Z0-9]{2}[HM][A-Z0-9]{5}[A-Z0-9]{1,2}", texto_norm)
        curp_resultado = None
        if curp_match:
            campos_detectados += 1
            curp_val = self._corregir_curp(curp_match.group())
            curp_resultado = {"valor": curp_val, "detectado": True}
            campos_validos += 1

        mrz = self._parsear_mrz(texto_norm)
        if mrz:
            if "nombre" in mrz:
                campos_detectados += 1
                campos_validos += 1
                alertas.append(f"MRZ: {mrz.get('apellido_paterno', '')}<{mrz.get('apellido_materno', '')}<<{mrz.get('nombre', '')}")
            if "numero_documento" in mrz and not num_doc_resultado:
                campos_detectados += 1
                campos_validos += 1
                num_doc_resultado = {"valor": mrz["numero_documento"], "formato": True, "fuente": "MRZ"}
            if "nacionalidad" in mrz:
                campos_detectados += 1
                campos_validos += 1

        fechas = re.findall(
            r"\d{1,2}/\d{1,2}/\d{4}", texto_norm
        )
        fecha_exp = None
        fecha_venc = None
        if len(fechas) >= 2:
            parsed = []
            for f in fechas:
                try:
                    parts = f.split("/")
                    d = datetime(int(parts[2]), int(parts[1]), int(parts[0]))
                    parsed.append((f, d))
                except Exception:
                    pass
            future = [(f, d) for f, d in parsed if d.year >= 2027]
            past_recent = [(f, d) for f, d in parsed if 2020 <= d.year <= datetime.now().year + 1 and d.year < 2027]
            if past_recent:
                fecha_exp = {"valor": past_recent[-1][0], "detectada": True}
                campos_detectados += 1
                campos_validos += 1
            if future:
                fecha_venc = {"valor": future[0][0], "detectada": True}
                campos_detectados += 1
                campos_validos += 1
            if not fecha_exp and not fecha_venc and len(parsed) >= 2:
                fecha_exp = {"valor": parsed[0][0], "detectada": True}
                fecha_venc = {"valor": parsed[-1][0], "detectada": True}
                campos_detectados += 2
                campos_validos += 2
        if not fecha_exp and not fecha_venc:
            alertas.append("Fechas de expedición/vencimiento no detectadas")
            score -= 0.10

        score = max(0.0, min(1.0, score))
        return CheckOCR(
            ok=score >= 0.5,
            curp=curp_resultado,
            numero_documento=num_doc_resultado,
            tipo_residencia=tipo_resultado,
            fecha_expedicion=fecha_exp,
            fecha_vencimiento=fecha_venc,
            campos_detectados=campos_detectados,
            campos_validos=campos_validos,
            alertas=alertas,
            score=round(score, 3),
        )

    def _validacion_generica(self, texto: str) -> CheckOCR:
        curp_match = re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d", texto)
        curp_resultado = None
        if curp_match:
            es_valido, _ = validar_curp(curp_match.group())
            curp_resultado = {"valor": curp_match.group(), "valido": es_valido}
        return CheckOCR(
            ok=True,
            curp=curp_resultado,
            campos_detectados=1 if curp_resultado else 0,
            campos_validos=1 if (curp_resultado and curp_resultado.get("valido")) else 0,
            alertas=["Validación genérica (tipo de documento desconocido)"],
            score=0.5,
        )
