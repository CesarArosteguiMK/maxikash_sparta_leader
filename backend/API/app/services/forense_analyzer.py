# app/services/forense_analyzer.py
"""CAPA 2: Análisis Forense - ELA, moiré, ruido."""
import io
import numpy as np
from PIL import Image
import cv2
from loguru import logger
from app.models.schemas import CheckForense


class ForenseAnalyzer:
    ELA_THRESHOLD_OK = 15.0
    ELA_THRESHOLD_WARN = 30.0
    ELA_COMPRESS_QUALITY = 90

    def analyze(self, image_bytes: bytes) -> CheckForense:
        alertas = []
        score = 1.0
        try:
            img_pil = Image.open(io.BytesIO(image_bytes)).convert("RGB")
            img_cv = cv2.imdecode(np.frombuffer(image_bytes, np.uint8), cv2.IMREAD_COLOR)
            ela_score = self._error_level_analysis(img_pil)
            compresion_uniforme = ela_score < self.ELA_THRESHOLD_OK
            if ela_score > self.ELA_THRESHOLD_WARN:
                alertas.append(f"ELA alto ({ela_score:.1f}): Posible manipulación digital")
                score -= 0.40
            elif ela_score > self.ELA_THRESHOLD_OK:
                alertas.append(f"ELA moderado ({ela_score:.1f}): Revisión recomendada")
                score -= 0.20
            moire_detectado = self._detectar_moire(img_cv)
            if moire_detectado:
                alertas.append("Patrón moiré detectado: Foto de pantalla o monitor")
                score -= 0.45
            pixeles_pantalla = self._detectar_pixeles_pantalla(img_cv)
            if pixeles_pantalla:
                alertas.append("Patrón de píxeles de pantalla detectado")
                score -= 0.40
            ruido_natural = self._analizar_ruido(img_cv)
            if not ruido_natural:
                alertas.append("Ruido de imagen no natural (posible imagen digital)")
                score -= 0.15
            brillo_excesivo, pct_sobreexpuesto = self._detectar_brillo_excesivo(img_cv)
            if brillo_excesivo:
                alertas.append(
                    f"BRILLO EXCESIVO: {pct_sobreexpuesto:.1f}% de la imagen está sobreexpuesta. "
                    "Repita la captura en un lugar con menos luz directa o reflejo."
                )
                score -= 0.50
            borroso = self._detectar_borroso(img_cv)
            if borroso:
                alertas.append(
                    "Imagen borrosa o desenfocada. Repita la captura asegurando que el documento se vea nítido."
                )
                score -= 0.30
            calidad_foto = self._resumen_calidad_foto(
                brillo_excesivo, borroso, pct_sobreexpuesto
            )
            score = max(0.0, min(1.0, score))
            return CheckForense(
                ok=score >= 0.5,
                ela_score=round(ela_score, 2),
                moire_detectado=moire_detectado,
                pixeles_pantalla=pixeles_pantalla,
                ruido_natural=ruido_natural,
                compresion_uniforme=compresion_uniforme,
                brillo_excesivo=brillo_excesivo,
                porcentaje_sobreexpuesto=round(pct_sobreexpuesto, 2),
                borroso=borroso,
                calidad_foto=calidad_foto,
                alertas=alertas,
                score=round(score, 3)
            )
        except Exception as e:
            logger.error(f"Error en ForenseAnalyzer: {e}")
            return CheckForense(ok=False, ela_score=0.0, alertas=[f"Error: {str(e)}"], score=0.5)

    def _error_level_analysis(self, img: Image.Image) -> float:
        img = img.convert("RGB")
        buffer = io.BytesIO()
        img.save(buffer, "JPEG", quality=self.ELA_COMPRESS_QUALITY)
        buffer.seek(0)
        img_recomprimida = Image.open(buffer).convert("RGB")
        a = np.array(img, dtype=float)
        b = np.array(img_recomprimida, dtype=float)
        if a.shape != b.shape:
            return 0.0
        ela_img = np.abs(a - b)
        return float(np.mean(ela_img))

    def _detectar_moire(self, img: np.ndarray) -> bool:
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        f = np.fft.fft2(gray)
        fshift = np.fft.fftshift(f)
        magnitude = np.abs(fshift)
        magnitude_log = np.log1p(magnitude)
        magnitude_norm = magnitude_log / (magnitude_log.max() or 1)
        h, w = magnitude_norm.shape
        centro_h, centro_w = h // 2, w // 2
        radio_centro = min(h, w) // 8
        mascara = np.ones_like(magnitude_norm)
        cv2.circle(mascara, (centro_w, centro_h), radio_centro, 0, -1)
        periferia = magnitude_norm * mascara
        max_periferia = periferia.max()
        media_periferia = periferia[periferia > 0].mean() if periferia.any() else 0
        return bool(max_periferia > 0.85 and media_periferia > 0.1)

    def _detectar_pixeles_pantalla(self, img: np.ndarray) -> bool:
        h, w = img.shape[:2]
        muestra = img[h//4:3*h//4, w//4:3*w//4]
        gray = cv2.cvtColor(muestra, cv2.COLOR_BGR2GRAY)
        grad_x = cv2.Sobel(gray, cv2.CV_64F, 1, 0, ksize=3)
        grad_y = cv2.Sobel(gray, cv2.CV_64F, 0, 1, ksize=3)
        grad_magnitude = np.sqrt(grad_x**2 + grad_y**2)
        std_grad = np.std(grad_magnitude)
        mean_grad = np.mean(grad_magnitude)
        if mean_grad == 0:
            return False
        cv_gradiente = std_grad / mean_grad
        return bool(cv_gradiente < 0.8 and mean_grad > 5)

    def _detectar_brillo_excesivo(self, img: np.ndarray) -> tuple:
        """Detecta glare/brillo excesivo. Retorna (es_excesivo, porcentaje_sobreexpuesto).
        Analiza tanto el porcentaje de pixeles saturados como zonas de glare concentradas."""
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        h, w = gray.shape

        total_pixels = h * w
        saturados = np.sum(gray >= 245)
        pct_saturado = (saturados / total_pixels) * 100

        muy_brillantes = np.sum(gray >= 230)
        pct_brillante = (muy_brillantes / total_pixels) * 100

        _, thresh = cv2.threshold(gray, 240, 255, cv2.THRESH_BINARY)
        kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (15, 15))
        thresh = cv2.morphologyEx(thresh, cv2.MORPH_CLOSE, kernel)
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        glare_grande = any(cv2.contourArea(c) > (total_pixels * 0.02) for c in contours)

        es_excesivo = (
            pct_saturado > 15.0 or
            pct_brillante > 30.0 or
            (glare_grande and pct_saturado > 5.0)
        )
        return es_excesivo, pct_saturado

    def _detectar_borroso(self, img: np.ndarray) -> bool:
        """Detecta si la imagen está borrosa/desenfocada (Laplacian variance baja)."""
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
        return bool(laplacian_var < 100.0)

    def _resumen_calidad_foto(
        self, brillo_excesivo: bool, borroso: bool, pct_sobreexpuesto: float
    ) -> str:
        """Resumen de calidad de foto para revisión (sin identificar persona)."""
        if brillo_excesivo and borroso:
            return "revisar_brillo_y_borroso"
        if brillo_excesivo or pct_sobreexpuesto > 20:
            return "revisar_brillo"
        if borroso:
            return "revisar_borroso"
        return "ok"

    def _analizar_ruido(self, img: np.ndarray) -> bool:
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY).astype(float)
        suavizada = cv2.GaussianBlur(gray, (5, 5), 0).astype(float)
        ruido = gray - suavizada
        std_ruido = np.std(ruido)
        return bool(1.5 <= std_ruido <= 15.0)
