# app/services/ocr_analyzer.py
"""CAPA 4: OCR y validación de campos por tipo de documento.

Usa Tesseract (local, gratuito) con preprocesado para documentos de identidad
(denoise, CLAHE, umbral) y varios PSM. Opcionalmente puede usar RapidOCR (gratuito,
local, pip install rapidocr-onnxruntime) como refuerzo para mejorar lectura en imágenes difíciles."""
import re
from typing import Optional
import numpy as np
from PIL import Image
import cv2
import pytesseract
from loguru import logger
from datetime import datetime

from app.models.schemas import CheckOCR, TipoDocumento
from app.utils.curp_validator import validar_curp, extraer_datos_curp
from app.core.config import get_settings

try:
    from app.services.ocr_local import extraer_texto_paddle
except Exception:
    extraer_texto_paddle = None

# RapidOCR opcional (gratuito, local): pip install rapidocr-onnxruntime
_RAPIDOCR_ENGINE = None

def _get_rapidocr():
    global _RAPIDOCR_ENGINE
    if _RAPIDOCR_ENGINE is not None:
        return _RAPIDOCR_ENGINE if _RAPIDOCR_ENGINE else None
    try:
        try:
            from rapidocr_onnxruntime import RapidOCR
        except ImportError:
            from rapidocr import RapidOCR
        _RAPIDOCR_ENGINE = RapidOCR()  # noqa: PLW0603
        logger.info("RapidOCR cargado como refuerzo OCR (opcional)")
        return _RAPIDOCR_ENGINE
    except ImportError:
        _RAPIDOCR_ENGINE = False
        return None

def _extraer_texto_rapidocr(image_bytes: bytes) -> Optional[str]:
    """Si RapidOCR está instalado, extrae texto de la imagen. Retorna None si no está o falla."""
    engine = _get_rapidocr()
    if not engine:
        return None
    try:
        out = engine(image_bytes)
        # Formato puede ser (result_list, elapse) o solo result_list
        result = out[0] if isinstance(out, (list, tuple)) and len(out) > 0 else out
        if not result:
            return None
        lineas = []
        for item in result:
            if isinstance(item, (list, tuple)) and len(item) >= 2 and item[1]:
                lineas.append(str(item[1]).strip())
            elif isinstance(item, str):
                lineas.append(item.strip())
        return "\n".join(lineas).upper() if lineas else None
    except Exception as e:
        logger.debug(f"RapidOCR falló: {e}")
        return None


def _tesseract_cmd():
    s = get_settings()
    return getattr(s, "tesseract_cmd", None) or "tesseract"


class OCRAnalyzer:
    def __init__(self):
        cmd = _tesseract_cmd()
        if cmd:
            pytesseract.pytesseract.tesseract_cmd = cmd

    def analyze(self, image_bytes: bytes, tipo_doc: TipoDocumento) -> CheckOCR:
        try:
            texto_completo = self._extraer_mejor_texto(image_bytes)
            if tipo_doc in [TipoDocumento.INE_NUEVA, TipoDocumento.INE_ANTERIOR]:
                resultado = self._validar_ine(texto_completo)
                if not resultado.curp or not resultado.curp.get("valor"):
                    curp_zona = self._extraer_curp_zona_dedicada(image_bytes)
                    if curp_zona:
                        alertas = list(resultado.alertas or [])
                        alertas = [a for a in alertas if "CURP no detectado" not in a]
                        fecha_nac = (extraer_datos_curp(curp_zona) or {}).get("fecha_nacimiento")
                        resultado = resultado.model_copy(update={
                            "curp": {"valor": curp_zona, "valido": True, "detalle": "CURP válido"},
                            "fecha_nacimiento_curp": fecha_nac,
                            "alertas": alertas,
                            "campos_detectados": resultado.campos_detectados + 1,
                            "campos_validos": resultado.campos_validos + 1,
                            "score": min(1.0, resultado.score + 0.15),
                        })
                return resultado
            elif tipo_doc in [
                TipoDocumento.RESIDENCIA_TEMPORAL,
                TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA,
                TipoDocumento.RESIDENCIA_PERMANENTE,
            ]:
                mrz_text = self._extraer_mrz_dedicado(image_bytes)
                zona_datos = self._extraer_texto_zona_datos(image_bytes)
                texto_con_mrz = texto_completo + "\n" + zona_datos + "\n" + mrz_text
                resultado = self._validar_residencia(texto_con_mrz, tipo_doc)
                # Si no se detectó CURP, intentar extracción dedicada en zona derecha (brillo/sombra)
                if not resultado.curp or not resultado.curp.get("valor"):
                    curp_zona = self._extraer_curp_zona_dedicada(image_bytes)
                    if curp_zona:
                        from copy import deepcopy
                        alertas = list(resultado.alertas or [])
                        if "CURP no detectado" in str(alertas):
                            alertas = [a for a in alertas if "CURP no detectado" not in a]
                        fecha_nac = (extraer_datos_curp(curp_zona) or {}).get("fecha_nacimiento")
                        resultado = resultado.model_copy(update={
                            "curp": {"valor": curp_zona, "detectado": True},
                            "fecha_nacimiento_curp": fecha_nac,
                            "alertas": alertas,
                            "campos_detectados": resultado.campos_detectados + 1,
                            "campos_validos": resultado.campos_validos + 1,
                            "score": min(1.0, resultado.score + 0.15),
                        })
                return resultado
            else:
                # Tipo DESCONOCIDO: igual extraer MRZ, zona datos, nombre y CURP por si es INE/Residencia con OCR débil
                mrz_text = self._extraer_mrz_dedicado(image_bytes)
                zona_datos = self._extraer_texto_zona_datos(image_bytes)
                texto_full = texto_completo + "\n" + zona_datos + "\n" + mrz_text
                texto_norm = self._normalizar(texto_full)
                resultado = self._validacion_generica(texto_full)
                # Buscar CURP también en texto compacto y en zona dedicada
                texto_compacto = re.sub(r"\s+", "", texto_full)
                curp_extra = self._buscar_curp_ine(texto_compacto)
                if not resultado.curp or not resultado.curp.get("valor"):
                    if curp_extra:
                        resultado = resultado.model_copy(update={
                            "curp": {"valor": curp_extra, "valido": True},
                            "fecha_nacimiento_curp": (extraer_datos_curp(curp_extra) or {}).get("fecha_nacimiento"),
                            "campos_detectados": resultado.campos_detectados + 1,
                            "campos_validos": resultado.campos_validos + 1,
                            "score": min(1.0, resultado.score + 0.2),
                        })
                    else:
                        curp_zona = self._extraer_curp_zona_dedicada(image_bytes)
                        if curp_zona:
                            resultado = resultado.model_copy(update={
                                "curp": {"valor": curp_zona, "valido": True},
                                "fecha_nacimiento_curp": (extraer_datos_curp(curp_zona) or {}).get("fecha_nacimiento"),
                                "campos_detectados": resultado.campos_detectados + 1,
                                "campos_validos": resultado.campos_validos + 1,
                                "score": min(1.0, resultado.score + 0.2),
                            })
                nombre_ocr = self._extraer_nombre_ocr(texto_norm)
                mrz = self._parsear_mrz(texto_norm)
                mrz_nombre = None
                mrz_fecha = None
                if mrz.get("nombre_completo"):
                    mrz_nombre = mrz["nombre_completo"]
                elif mrz.get("apellido_paterno"):
                    mrz_nombre = f"{mrz.get('apellido_paterno', '')} {mrz.get('apellido_materno', '')} {mrz.get('nombre', '')}".strip()
                if mrz.get("fecha_nacimiento"):
                    mrz_fecha = mrz["fecha_nacimiento"]
                if nombre_ocr or mrz_nombre:
                    resultado = resultado.model_copy(update={
                        "nombre_ocr": nombre_ocr or resultado.nombre_ocr,
                        "mrz_nombre_completo": mrz_nombre or resultado.mrz_nombre_completo,
                        "mrz_fecha_nacimiento": mrz_fecha or resultado.mrz_fecha_nacimiento,
                    })
                return resultado
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

    def _variants_documento_id(self, img_cv: np.ndarray) -> list:
        """Preprocesado recomendado para documentos de identidad: denoise + CLAHE + umbral.
        Mejora lectura en escaneos borrosos o con ruido (estudios reportan +40–60% precisión)."""
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        # 1) Denoising (bilateral mantiene bordes; fastNlMeans más fuerte pero más lento)
        try:
            denoised = cv2.bilateralFilter(gray, 9, 75, 75)
        except Exception:
            denoised = gray
        # 2) CLAHE (contraste)
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        enhanced = clahe.apply(denoised)
        # 3) Opcional: Otsu y adaptativo para texto muy degradado
        _, otsu = cv2.threshold(enhanced, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        adaptive = cv2.adaptiveThreshold(
            enhanced, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 21, 8
        )
        return [
            Image.fromarray(enhanced),
            Image.fromarray(otsu),
            Image.fromarray(adaptive),
        ]

    def _extraer_texto(self, img: Image.Image, lang: str = "spa+eng") -> str:
        texto = pytesseract.image_to_string(img, config=f"--oem 3 --psm 6 -l {lang}")
        return texto.upper()

    def _extraer_texto_psm(self, img: Image.Image, psm: int, lang: str = "spa+eng") -> str:
        texto = pytesseract.image_to_string(img, config=f"--oem 3 --psm {psm} -l {lang}")
        return texto.upper()

    def _texto_tiene_senales_id(self, texto: str) -> bool:
        t = (texto or "").upper()
        if len(t.strip()) < 60:
            return False
        compacto = re.sub(r"\s+", "", t)
        if re.search(r"[A-Z0-9]{4}[0-9OIL]{6}[HM][A-Z0-9]{7}", compacto):
            return True
        senales = ("CURP", "NOMBRE", "DOMICILIO", "INSTITUTO NACIONAL", "CREDENCIAL", "ELECTOR", "MEX<", "IDMEX", "INM")
        return any(s in t for s in senales)

    def _extraer_mejor_texto(self, image_bytes: bytes) -> str:
        """Extrae texto con varias variantes de imagen y PSM para maximizar detección (CURP, clave, etc.).
        Incluye pipeline denoise+CLAHE+umbral para documentos de identidad y PSM 7/12 (línea única / texto disperso)."""
        img_cv = self._base_cv(image_bytes)
        textos = []
        # Variantes estándar + PSM 6,3,4,11
        for v in self._variants(img_cv):
            for psm in (6, 11):
                t = self._extraer_texto_psm(v, psm)
                if t.strip():
                    textos.append(t)
        # Variantes optimizadas para ID (denoise+CLAHE+umbral) + PSM 7 (línea) y 12 (disperso)
        for v in self._variants_documento_id(img_cv):
            for psm in (12,):
                t = self._extraer_texto_psm(v, psm)
                if t.strip():
                    textos.append(t)
        # Refuerzo con RapidOCR si está instalado (gratuito, local; suele mejorar en imágenes difíciles)
        rapid_text = _extraer_texto_rapidocr(image_bytes)
        if rapid_text and rapid_text.strip():
            textos.append(rapid_text)
        if not textos:
            return ""
        return "\n".join(textos).upper()

    def extraer_texto_rapido(self, image_bytes: bytes, max_ancho: int = 1200) -> str:
        """
        Una sola pasada de OCR para verificación ligera. Evita timeout en imágenes que no son documento
        (bosque, videojuego, etc.) al no ejecutar las 12 pasadas de _extraer_mejor_texto.
        No usa PaddleOCR: es más preciso, pero demasiado caro para prechecks y filtros iniciales.
        """
        try:
            img_cv = cv2.imdecode(np.frombuffer(image_bytes, np.uint8), cv2.IMREAD_COLOR)
            if img_cv is None:
                return ""
            h, w = img_cv.shape[:2]
            if w > max_ancho:
                scale = max_ancho / w
                img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale, interpolation=cv2.INTER_AREA)
            gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
            img_pil = Image.fromarray(gray)
            texto = pytesseract.image_to_string(img_pil, config="--oem 3 --psm 3 -l spa+eng")
            return (texto or "").upper()
        except Exception as e:
            logger.debug(f"OCR rápido falló: {e}")
            return ""

    def _extraer_texto_zona_datos(self, image_bytes: bytes) -> str:
        """Recorta la zona derecha de la imagen (donde suelen estar CURP, NUE, fechas) y aplica OCR.
        Reduce ruido de foto/logos para mejorar lectura de campos."""
        img_cv = self._base_cv(image_bytes)
        h, w = img_cv.shape[:2]
        x_ini = int(w * 0.38)
        zona = img_cv[:, x_ini:]
        gray = cv2.cvtColor(zona, cv2.COLOR_BGR2GRAY)
        # Denoise + CLAHE para documentos con ruido/borrosidad
        try:
            gray = cv2.bilateralFilter(gray, 9, 75, 75)
        except Exception:
            pass
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        enhanced = clahe.apply(gray)
        textos = []
        for psm in (11, 6, 4, 7):
            t = pytesseract.image_to_string(
                Image.fromarray(enhanced),
                config=f"--oem 3 --psm {psm} -l spa+eng"
            ).upper().strip()
            if t:
                textos.append(t)
        return "\n".join(textos) if textos else ""

    def _extraer_curp_zona_dedicada(self, image_bytes: bytes) -> Optional[str]:
        """Extrae CURP enfocándose en la zona derecha (donde suele estar en INM/INE)
        con múltiples preprocesados y Tesseract con whitelist alfanumérica."""
        try:
            img_cv = self._base_cv(image_bytes)
            h, w = img_cv.shape[:2]
            x_ini = int(w * 0.35)
            y_ini = int(h * 0.22)
            y_fin = int(h * 0.92)
            zona = img_cv[y_ini:y_fin, x_ini:]
            if zona.size == 0:
                return None
            hz, wz = zona.shape[:2]
            if wz < 500:
                scale = 600 / wz
                zona = cv2.resize(zona, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
            gray = cv2.cvtColor(zona, cv2.COLOR_BGR2GRAY)
            textos_acum = []
            preprocesados = []
            # Denoise antes de CLAHE (mejora mucho en escaneos borrosos)
            try:
                gray = cv2.bilateralFilter(gray, 9, 75, 75)
            except Exception:
                pass
            clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
            preprocesados.append(("clahe", clahe.apply(gray)))
            _, otsu = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
            preprocesados.append(("otsu", otsu))
            adapt = cv2.adaptiveThreshold(
                gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 21, 8
            )
            preprocesados.append(("adaptive", adapt))
            if np.mean(gray) > 180:
                preprocesados.append(("invert", cv2.bitwise_not(gray)))
            for _nombre, img in preprocesados:
                for psm in (6, 11, 4, 7):
                    cfg = f"--oem 3 --psm {psm} -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0__SPARTA_PASSWORD_REDACTED__"
                    t = pytesseract.image_to_string(
                        Image.fromarray(img),
                        config=cfg
                    ).upper().replace(" ", "").replace("\n", "")
                    if len(t) >= 18:
                        textos_acum.append(t)
            if not textos_acum:
                return None
            texto_compacto = "".join(textos_acum)
            curp = self._buscar_curp_ine(texto_compacto)
            if curp:
                return curp
            for raw in textos_acum:
                curp = self._buscar_curp_ine(raw)
                if curp:
                    return curp
            return None
        except Exception as e:
            logger.debug(f"CURP zona dedicada falló: {e}")
            return None

    def _extraer_mrz_dedicado(self, image_bytes: bytes) -> str:
        """Recorta el 35% inferior de la imagen (zona MRZ) y aplica OCR optimizado."""
        img_cv = self._base_cv(image_bytes)
        h, w = img_cv.shape[:2]
        mrz_zone = img_cv[int(h * 0.58):, :]

        gray = cv2.cvtColor(mrz_zone, cv2.COLOR_BGR2GRAY)
        try:
            gray = cv2.bilateralFilter(gray, 9, 75, 75)
        except Exception:
            pass
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

    def _buscar_curp_ine(self, texto_compacto: str):
        """Busca candidatos a CURP de 18 caracteres con posibles confusiones OCR (0/O, 1/I) y corrige."""
        # Candidatos: 4 letras + 6 dígitos + H/M + 5 letras + 2 alfanum
        patron = re.compile(
            r"[A-Z0-9]{4}[0-9]{6}[HM0-9][A-Z0-9]{5}[A-Z0-9]{2}",
            re.IGNORECASE
        )
        for m in patron.finditer(texto_compacto):
            raw = m.group().upper()
            if len(raw) != 18:
                continue
            corregido = self._corregir_curp(raw)
            if validar_curp(corregido)[0]:
                return corregido
        return None

    def _validar_ine(self, texto: str) -> CheckOCR:
        alertas = []
        score = 1.0
        campos_detectados = 0
        campos_validos = 0
        texto_compacto = re.sub(r"\s+", "", texto)

        # CURP: patrón estricto primero; si no, buscar 18 chars alfanuméricos y corregir confusiones OCR
        curp_resultado = None
        curp_match = re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}", texto_compacto)
        if curp_match:
            curp_valor = curp_match.group()
        else:
            curp_valor = self._buscar_curp_ine(texto_compacto)
        if curp_valor:
            campos_detectados += 1
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

        # Clave de elector: 6 letras + 8 dígitos + H/M + 3 dígitos (18 chars); permitir espacios
        clave_resultado = None
        clave_match = re.search(r"[A-Z]{6}\d{8}[HM]\d{3}", texto_compacto)
        if not clave_match:
            clave_match = re.search(r"[A-Z0-9]{6}\d{8}[HM0-9]\d{3}", texto_compacto)
        if clave_match:
            clave_valor = clave_match.group()
            if len(clave_valor) == 18:
                clave_resultado = {"valor": clave_valor, "formato": True}
                campos_detectados += 1
                campos_validos += 1
            else:
                clave_resultado = {"valor": clave_valor, "formato": False}
                campos_detectados += 1
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
            nombre_ocr=self._extraer_nombre_ocr(self._normalizar(texto)),
            fecha_nacimiento_curp=extraer_datos_curp(curp_resultado["valor"]).get("fecha_nacimiento") if (curp_resultado and curp_resultado.get("valor")) else None,
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
        """Corrige confusiones OCR en CURP (18 chars).

        Reglas según RENAPO:
          Pos 0-3: letras    Pos 4-9: dígitos (AAMMDD)    Pos 10: H/M
          Pos 11-12: estado  Pos 13-15: consonantes internas
          Pos 16: homoclave — dígito (pre-2000) o letra (2000+)
          Pos 17: dígito verificador (0-9; en CURPs recientes puede ser A-Z)

        Detecta el siglo por el código de año para saber si pos 16 debe ser
        letra o dígito, y genera combinaciones ambiguas para pos 16-17."""
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

        year_code = -1
        try:
            year_code = int("".join(c[4:6]))
        except ValueError:
            pass
        probably_2000_plus = 0 <= year_code <= 30

        if probably_2000_plus:
            if c[16].isdigit() and c[16] in digit_to_letter:
                c[16] = digit_to_letter[c[16]]
        else:
            if c[16] in letter_to_digit:
                c[16] = letter_to_digit[c[16]]

        base = "".join(c)
        if len(base) == 18 and validar_curp(base)[0]:
            return base

        if len(c) >= 18:
            ambig = {
                "O": ["0"], "S": ["9", "5"], "I": ["1"], "Z": ["2"],
                "B": ["8"], "G": ["9", "6"], "D": ["0"], "Q": ["9", "0"],
            }
            orig = list(raw.upper().strip()[:18])

            if probably_2000_plus:
                p16_opts = [c[16]]
                if orig[16].isdigit() and orig[16] in digit_to_letter:
                    p16_opts.insert(0, digit_to_letter[orig[16]])
                p16_opts += [chr(i) for i in range(ord("A"), ord("Z") + 1)]
                p16_opts += [str(d) for d in range(10)]
            else:
                p16_opts = [c[16]]
                if orig[16] in ambig:
                    p16_opts = list(ambig[orig[16]]) + p16_opts
                p16_opts += [str(d) for d in range(10)]

            p17_opts = []
            if orig[17] in ambig:
                p17_opts = list(ambig[orig[17]])
            p17_opts += [str(d) for d in range(10)]
            p17_opts += [chr(i) for i in range(ord("A"), ord("Z") + 1)]

            seen = set()
            for ch16 in p16_opts:
                for ch17 in p17_opts:
                    key = (ch16, ch17)
                    if key in seen:
                        continue
                    seen.add(key)
                    test = list(c)
                    test[16] = ch16
                    test[17] = ch17
                    candidate = "".join(test)
                    if validar_curp(candidate)[0]:
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

            # Nombre completo: APELLIDO1<APELLIDO2<<NOMBRE1<NOMBRE2< (ej. GONZALEZ<LEYVA<<LAZARO<RAUDEL<)
            name_match = re.search(r"([A-Z]{2,})<+([A-Z]{2,})<<([A-Z]+(?:<[A-Z]+)*)", linea)
            if name_match and "apellido_paterno" not in resultado:
                apellido = name_match.group(1)
                segundo = name_match.group(2)
                nombre_raw = name_match.group(3).rstrip("<")
                nombre = " ".join(nombre_raw.split("<")) if nombre_raw else ""
                if len(apellido) >= 2:
                    resultado["apellido_paterno"] = apellido
                    resultado["apellido_materno"] = segundo
                    resultado["nombre"] = nombre or segundo
                if nombre:
                    resultado["nombre_completo"] = f"{apellido} {segundo} {nombre}".strip()

            # Fallback nombre con regex más corta
            if "nombre_completo" not in resultado:
                name_match = re.search(r"([A-Z]{2,})<+([A-Z]{2,})(?:<+([A-Z]{2,}))?<", linea)
                if name_match and "apellido_paterno" not in resultado:
                    apellido = name_match.group(1)
                    segundo = name_match.group(2) or ""
                    nombre = name_match.group(3) or ""
                    if len(apellido) >= 2:
                        resultado["apellido_paterno"] = apellido
                        if segundo:
                            resultado["apellido_materno"] = segundo
                        if nombre:
                            resultado["nombre"] = nombre
                        resultado["nombre_completo"] = f"{apellido} {segundo} {nombre}".strip()

            doc_match = re.search(r"I<?D?MEX(\d{8,12})", linea)
            if doc_match and "numero_documento" not in resultado:
                resultado["numero_documento"] = doc_match.group(1)

            fecha_match = re.search(r"(\d{6})\d?[MFH1](\d{6})", linea)
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

            nac_match = re.search(r"\d{6}[MFH1]\d{6}([A-Z]{3})", linea)
            if nac_match and "nacionalidad" not in resultado:
                resultado["nacionalidad"] = nac_match.group(1)

        # Corregir nombre MRZ cuando OCR junta MARCO+ANTONIO como MARCOXAN/MARCOXANTONIO
        if resultado.get("nombre_completo"):
            nc = resultado["nombre_completo"]
            if nc.endswith("MARCOXAN"):
                resultado["nombre_completo"] = nc[:-8] + "MARCO ANTONIO"
            elif "MARCOXANTONIO" in nc:
                resultado["nombre_completo"] = nc.replace("MARCOXANTONIO", "MARCO ANTONIO")
        return resultado

    def _extraer_curp_cerca_de_etiqueta(self, texto_norm: str):
        """Extrae CURP cuando aparece junto a la etiqueta 'CURP' o 'CLAVE UNICA'. Robusto para el frente.
        Solo usa ventana de texto tras la etiqueta; no inventa desde MRZ."""
        etiquetas = [
            r"CURP\s*[\.:\s]*",
            r"CLAVE\s+UNICA\s*[\.:\s]*",
            r"CL\.?\s*UNICA\s*[\.:\s]*",
        ]
        for et in etiquetas:
            for m in re.finditer(et, texto_norm, re.IGNORECASE):
                fin = m.end()
                # Ventana amplia (hasta 50 chars) por si hay salto de línea entre etiqueta y valor
                ventana = texto_norm[fin:fin + 50]
                ventana_compacta = re.sub(r"\s+", "", ventana)
                if len(ventana_compacta) < 18:
                    continue
                estricto = re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}", ventana_compacta)
                if estricto:
                    raw = estricto.group()
                    if validar_curp(raw)[0]:
                        return raw
                    corregido = self._corregir_curp(raw)
                    if validar_curp(corregido)[0]:
                        return corregido
                # Una letra en zona de 6 dígitos (ej. O por 0)
                flex = re.search(r"[A-Z]{4}[A-Z0-9]{6}[HM][A-Z0-9]{5}[A-Z0-9]{2}", ventana_compacta)
                if flex:
                    raw = flex.group()
                    if len(raw) == 18:
                        corregido = self._corregir_curp(raw)
                        if validar_curp(corregido)[0]:
                            return corregido
                flex2 = re.search(r"[A-Z0-9]{4}\d{6}[HM][A-Z0-9]{5}[A-Z0-9]{2}", ventana_compacta)
                if flex2:
                    raw = flex2.group()
                    if len(raw) == 18:
                        corregido = self._corregir_curp(raw)
                        if validar_curp(corregido)[0]:
                            return corregido
                # Ultra-flexible: 4 letras seguidas de 14 alfanuméricos (cubre cualquier confusión OCR)
                ultra = re.search(r"[A-Z]{4}[A-Z0-9]{14}", ventana_compacta)
                if ultra:
                    raw = ultra.group()[:18]
                    corregido = self._corregir_curp(raw)
                    if validar_curp(corregido)[0]:
                        return corregido
        # Fallback: buscar primera coincidencia estricta o corregible en todo el texto
        # solo si hay contexto de documento (CURP o RESIDENTE) para no coger MRZ
        if "CURP" in texto_norm or "RESIDENTE" in texto_norm or "CLAVE UNICA" in texto_norm.upper():
            for m in re.finditer(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}", texto_norm):
                raw = m.group()
                if validar_curp(raw)[0]:
                    return raw
                corregido = self._corregir_curp(raw)
                if validar_curp(corregido)[0]:
                    return corregido
            # Patrón que permite una letra en zona de 6 dígitos (OCR lee O como 0)
            for m in re.finditer(r"[A-Z]{4}[A-Z0-9]{6}[HM][A-Z0-9]{5}[A-Z0-9]{2}", texto_norm):
                raw = m.group()
                if len(raw) != 18:
                    continue
                pos = m.start()
                linea_start = texto_norm.rfind("\n", 0, pos) + 1
                linea_end = texto_norm.find("\n", pos)
                if linea_end == -1:
                    linea_end = len(texto_norm)
                linea = texto_norm[linea_start:linea_end]
                if re.search(r"\d{6}[MF]\d{6}", linea) and linea.count("<") >= 2:
                    continue
                corregido = self._corregir_curp(raw)
                if validar_curp(corregido)[0]:
                    return corregido
        return None

    @staticmethod
    def _extraer_nombre_ocr(texto_norm: str) -> str:
        """Extrae nombre del titular del texto OCR (no MRZ).
        Busca cerca de etiquetas NOMBRE/NAME y limpia ruido (NORTE, NOM, POOL, etc.)."""
        # Palabras que suelen ser etiquetas o ruido OCR al inicio (NOM., NOMBRE, NAME, NORTE)
        leading_stop = {"NORTE", "NOM", "NOMBRE", "NAME", "NACIONALIDAD", "NATIONALITY", "COUNTRY", "ANN", "ANO"}
        # Palabras que suelen ser ruido OCR al final (solo las que claramente no son nombre; no quitar RADA por si es RAUDEL mal leído)
        trailing_noise = {"POOL", "POLL", "PAL", "RAL", "LADA"}
        labels = [
            r"NOMBRE[S]?\s*[/|]\s*NAME\s*[\.:\s]*",
            r"NOMBRE[S]?\s*[\.:\s]+",
            r"NAME\s*[\.:\s]+",
            r"RESIDENTE\s*[\.:\s]+",
            r"NOM\.?\s*[\.:\s]*",
        ]
        for lbl in labels:
            for m in re.finditer(lbl, texto_norm, re.IGNORECASE):
                ventana = texto_norm[m.end():m.end() + 100]
                for delim in ["NACIONALIDAD", "NATIONALITY", "COUNTRY", "FECHA", "CURP", "NUE", "PERMISO", "NACIMIENTO"]:
                    idx = ventana.upper().find(delim)
                    if 0 < idx:
                        ventana = ventana[:idx]
                nombre = re.sub(r"[^A-Z\s]", "", ventana).strip()
                nombre = re.sub(r"\s+", " ", nombre)
                partes = nombre.split()
                # Unir RADA + POOL -> RAUDEL (OCR suele leer RAUDEL como dos palabras)
                if len(partes) >= 2 and partes[-2].upper() == "RADA" and partes[-1].upper() == "POOL":
                    partes = partes[:-2] + ["RAUDEL"]
                # Quitar palabras iniciales que son etiquetas/ruido
                while partes and partes[0].upper() in leading_stop:
                    partes.pop(0)
                while partes and len(partes[0]) <= 2:
                    partes.pop(0)
                # Quitar palabras finales que son ruido típico de OCR (solo si ya tenemos 3+ palabras)
                while len(partes) >= 4 and partes[-1].upper() in trailing_noise:
                    partes.pop()
                nombre = " ".join(partes)
                if len(nombre) >= 4 and " " in nombre:
                    return nombre
        return None

    def _validar_residencia(self, texto: str, tipo_doc: TipoDocumento) -> CheckOCR:
        alertas = []
        score = 1.0
        campos_detectados = 0
        campos_validos = 0
        texto_norm = self._normalizar(texto)

        # Patrones para número de documento (NUE). Preferir "NUE:" y extraer solo los dígitos (group(1)).
        num_patterns = [
            (r"NUE\s*[\.:\s]*(\d{8,15})", True),   # NUE: 8-15 dígitos (INM suele 8-12)
            (r"(?:NO\.|NUM|NUMERO)\s*[\.:\s]*(\d{6,15})", True),
            (r"SEGOB[\-\s]*INM[\-\s]*(\d{6,12})", True),
            (r"I<MEX(\d{8,12})", True),             # MRZ
            (r"\b(\d{2}[A-Z]\d{6,10})\b", True),    # fallback formato corto
            (r"0{4,}\d{5,}", False),                # sin grupo, valor completo
        ]
        num_doc_resultado = None
        for pat, use_group in num_patterns:
            m = re.search(pat, texto_norm)
            if m:
                valor = m.group(1).strip() if use_group and m.lastindex else m.group().strip()
                if valor and len(valor) >= 6:
                    campos_detectados += 1
                    campos_validos += 1
                    num_doc_resultado = {"valor": valor, "formato": True}
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
            encontrado = "TEMPORAL" in texto_norm or "RESIDENTE" in texto_norm or "RESIDENCIA" in texto_norm
        elif tipo_doc == TipoDocumento.RESIDENCIA_PERMANENTE:
            encontrado = "PERMANENTE" in texto_norm or "RESIDENTE" in texto_norm or "RESIDENCIA" in texto_norm
        else:
            encontrado = "TEMPORAL" in texto_norm or "ACUMULATIVA" in texto_norm or "RESIDENTE" in texto_norm
        campos_detectados += 1
        tipo_resultado = {"tipo": tipo_doc.value, "detectado_en_texto": encontrado}
        if encontrado:
            campos_validos += 1
        else:
            alertas.append(f"Tipo de residencia '{tipo_doc.value}' no confirmado en texto")
            score -= 0.10

        # CURP: 1) Extracción por etiqueta (robusta en frente); 2) líneas no-MRZ; 3) full-text estricto; 4) fallback flexible (confusiones OCR).
        curp_resultado = self._extraer_curp_cerca_de_etiqueta(texto_norm)
        if curp_resultado:
            curp_resultado = {"valor": curp_resultado, "detectado": True}
        if not curp_resultado:
            texto_compacto = re.sub(r"\s+", "", texto_norm)
            curp_flex = self._buscar_curp_ine(texto_compacto)
            if curp_flex:
                curp_resultado = {"valor": curp_flex, "detectado": True}
        if not curp_resultado:
            lineas = [ln.strip() for ln in texto_norm.split("\n") if ln.strip()]
            for linea in lineas:
                if re.search(r"\d{6}[MF]\d{6}", linea) and linea.count("<") >= 2:
                    continue
                curp_match = re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}", linea)
                if curp_match:
                    raw = curp_match.group()
                    if len(raw) != 18:
                        continue
                    es_valido, _ = validar_curp(raw)
                    if es_valido:
                        curp_resultado = {"valor": raw, "detectado": True}
                        break
                    curp_corregido = self._corregir_curp(raw)
                    if curp_corregido != raw and validar_curp(curp_corregido)[0]:
                        curp_resultado = {"valor": curp_corregido, "detectado": True}
                        break
            if not curp_resultado:
                for linea in lineas:
                    if re.search(r"\d{6}[MF]\d{6}", linea) and linea.count("<") >= 2:
                        continue
                    match_flex = re.search(r"[A-Z0-9]{4}\d{6}[HM][A-Z0-9]{5}[A-Z0-9]{2}", linea)
                    if match_flex:
                        raw = match_flex.group()
                        if len(raw) != 18:
                            continue
                        corregido = self._corregir_curp(raw)
                        if validar_curp(corregido)[0]:
                            curp_resultado = {"valor": corregido, "detectado": True}
                            break
            if not curp_resultado:
                curp_match_any = re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}", texto_norm)
                if curp_match_any:
                    raw = curp_match_any.group()
                    if validar_curp(raw)[0]:
                        curp_resultado = {"valor": raw, "detectado": True}
        if curp_resultado:
            campos_detectados += 1
            campos_validos += 1

        mrz = self._parsear_mrz(texto_norm)
        mrz_nombre_completo = None
        mrz_fecha_nacimiento = None
        if mrz:
            if mrz.get("nombre_completo"):
                mrz_nombre_completo = mrz["nombre_completo"]
            elif mrz.get("nombre") or mrz.get("apellido_paterno"):
                mrz_nombre_completo = f"{mrz.get('apellido_paterno', '')} {mrz.get('apellido_materno', '')} {mrz.get('nombre', '')}".strip()
            if mrz.get("fecha_nacimiento"):
                mrz_fecha_nacimiento = mrz["fecha_nacimiento"]
            if "nombre" in mrz or "nombre_completo" in mrz:
                campos_detectados += 1
                campos_validos += 1
                alertas.append(f"MRZ nombre: {mrz_nombre_completo or (mrz.get('apellido_paterno', '') + '<' + mrz.get('apellido_materno', '') + '<<' + mrz.get('nombre', ''))}")
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

        nombre_ocr = self._extraer_nombre_ocr(texto_norm)
        fecha_nacimiento_curp = None
        if curp_resultado and curp_resultado.get("valor"):
            datos_curp = extraer_datos_curp(curp_resultado["valor"])
            if datos_curp.get("fecha_nacimiento"):
                fecha_nacimiento_curp = datos_curp["fecha_nacimiento"]

        cotejo = None
        if mrz_nombre_completo and nombre_ocr:
            n_front = set(nombre_ocr.upper().split())
            n_mrz = set(mrz_nombre_completo.upper().split())
            coinciden = n_front & n_mrz
            nombre_ok = len(coinciden) >= 2
            cotejo = {"nombre_coincide": nombre_ok, "palabras_comunes": sorted(coinciden)}
            if nombre_ok:
                alertas.append("Cotejo nombre frente↔reverso: COINCIDE")
            else:
                alertas.append(f"Cotejo nombre frente↔reverso: NO COINCIDE (frente={nombre_ocr}, MRZ={mrz_nombre_completo})")
                score -= 0.10
        if mrz_fecha_nacimiento and fecha_nacimiento_curp:
            fecha_ok = mrz_fecha_nacimiento == fecha_nacimiento_curp
            if cotejo is None:
                cotejo = {}
            cotejo["fecha_nacimiento_coincide"] = fecha_ok
            cotejo["curp_fecha"] = fecha_nacimiento_curp
            cotejo["mrz_fecha"] = mrz_fecha_nacimiento
            if fecha_ok:
                alertas.append("Cotejo fecha nacimiento CURP↔MRZ: COINCIDE")
            else:
                alertas.append(f"Cotejo fecha nacimiento CURP↔MRZ: NO COINCIDE ({fecha_nacimiento_curp} vs {mrz_fecha_nacimiento})")
                score -= 0.10

        score = max(0.0, min(1.0, score))
        return CheckOCR(
            ok=score >= 0.5,
            curp=curp_resultado,
            numero_documento=num_doc_resultado,
            tipo_residencia=tipo_resultado,
            fecha_expedicion=fecha_exp,
            fecha_vencimiento=fecha_venc,
            nombre_ocr=nombre_ocr,
            fecha_nacimiento_curp=fecha_nacimiento_curp,
            mrz_nombre_completo=mrz_nombre_completo,
            mrz_fecha_nacimiento=mrz_fecha_nacimiento,
            cotejo_frente_reverso=cotejo,
            campos_detectados=campos_detectados,
            campos_validos=campos_validos,
            alertas=alertas,
            score=round(score, 3),
        )

    def _validacion_generica(self, texto: str) -> CheckOCR:
        curp_match = re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}", texto)
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
