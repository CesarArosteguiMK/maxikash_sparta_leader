# app/services/forense_analyzer.py
"""
CAPA 2: Análisis Forense de Imagen.
- Error Level Analysis (ELA): detecta zonas manipuladas
- Detección de moiré: indica foto de pantalla
- Análisis de ruido natural: cámaras reales tienen ruido uniforme
"""
import io
import numpy as np
from PIL import Image
import cv2
from loguru import logger

from app.models.schemas import CheckForense


class ForenseAnalyzer:
    """Análisis forense digital de la imagen."""

    # ELA: Score < 15 = natural, 15-30 = sospechoso, > 30 = manipulado
    ELA_THRESHOLD_OK = 15.0
    ELA_THRESHOLD_WARN = 30.0
    ELA_COMPRESS_QUALITY = 90

    def analyze(self, image_bytes: bytes) -> CheckForense:
        """
        Análisis forense completo de la imagen.
        
        Returns:
            CheckForense con scores de cada análisis
        """
        alertas = []
        score = 1.0

        try:
            img_pil = Image.open(io.BytesIO(image_bytes)).convert("RGB")
            img_cv = cv2.imdecode(
                np.frombuffer(image_bytes, np.uint8),
                cv2.IMREAD_COLOR
            )

            # ---- ELA (Error Level Analysis) ----
            ela_score = self._error_level_analysis(img_pil)
            compresion_uniforme = ela_score < self.ELA_THRESHOLD_OK

            if ela_score > self.ELA_THRESHOLD_WARN:
                alertas.append(f"🔴 ELA alto ({ela_score:.1f}): Posible manipulación digital")
                score -= 0.40
            elif ela_score > self.ELA_THRESHOLD_OK:
                alertas.append(f"🟡 ELA moderado ({ela_score:.1f}): Revisión recomendada")
                score -= 0.20

            # ---- Detección de moiré ----
            moire_detectado = self._detectar_moire(img_cv)
            if moire_detectado:
                alertas.append("🔴 Patrón moiré detectado: Foto de pantalla o monitor")
                score -= 0.45

            # ---- Detección de píxeles de pantalla ----
            pixeles_pantalla = self._detectar_pixeles_pantalla(img_cv)
            if pixeles_pantalla:
                alertas.append("🔴 Patrón de píxeles de pantalla detectado")
                score -= 0.40

            # ---- Análisis de ruido natural ----
            ruido_natural = self._analizar_ruido(img_cv)
            if not ruido_natural:
                alertas.append("🟡 Ruido de imagen no natural (posible imagen digital)")
                score -= 0.15

            score = max(0.0, min(1.0, score))

            return CheckForense(
                ok=score >= 0.5,
                ela_score=round(ela_score, 2),
                moire_detectado=moire_detectado,
                pixeles_pantalla=pixeles_pantalla,
                ruido_natural=ruido_natural,
                compresion_uniforme=compresion_uniforme,
                alertas=alertas,
                score=round(score, 3)
            )

        except Exception as e:
            logger.error(f"Error en ForenseAnalyzer: {e}")
            return CheckForense(
                ok=False,
                ela_score=0.0,
                alertas=[f"Error en análisis forense: {str(e)}"],
                score=0.5
            )

    def _error_level_analysis(self, img: Image.Image) -> float:
        """
        Error Level Analysis (ELA).
        Recomprime la imagen y mide diferencias.
        Zonas manipuladas tienen diferente nivel de error.
        
        Returns:
            Score ELA (menor = más uniforme = más natural)
        """
        # Guardar en buffer con calidad específica
        buffer = io.BytesIO()
        img.save(buffer, "JPEG", quality=self.ELA_COMPRESS_QUALITY)
        buffer.seek(0)
        img_recomprimida = Image.open(buffer)

        # Calcular diferencia píxel a píxel
        ela_img = np.array(img, dtype=float) - np.array(img_recomprimida, dtype=float)
        ela_img = np.abs(ela_img)

        # Score = media de las diferencias (normalizado)
        return float(np.mean(ela_img))

    def _detectar_moire(self, img: np.ndarray) -> bool:
        """
        Detecta patrón de moiré (rejilla que aparece al fotografiar pantallas).
        Analiza el espectro de frecuencias de la imagen.
        """
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # FFT para analizar frecuencias
        f = np.fft.fft2(gray)
        fshift = np.fft.fftshift(f)
        magnitude = np.abs(fshift)

        # Normalizar
        magnitude_log = np.log1p(magnitude)
        magnitude_norm = magnitude_log / magnitude_log.max()

        # En imágenes de pantalla hay picos de frecuencia en la FFT
        h, w = magnitude_norm.shape
        centro_h, centro_w = h // 2, w // 2
        radio_centro = min(h, w) // 8

        # Crear máscara que excluye el centro (frecuencias bajas)
        mascara = np.ones_like(magnitude_norm)
        cv2.circle(mascara, (centro_w, centro_h), radio_centro, 0, -1)

        # Si hay picos fuertes FUERA del centro = posible moiré
        periferia = magnitude_norm * mascara
        max_periferia = periferia.max()
        media_periferia = periferia[periferia > 0].mean() if periferia.any() else 0

        # Umbral heurístico
        return bool(max_periferia > 0.85 and media_periferia > 0.1)

    def _detectar_pixeles_pantalla(self, img: np.ndarray) -> bool:
        """
        Detecta si la imagen fue tomada de una pantalla.
        Las pantallas tienen subpíxeles RGB muy regulares.
        """
        # Analizar la regularidad de colores en una muestra
        h, w = img.shape[:2]

        # Tomar muestra del centro
        muestra = img[h//4:3*h//4, w//4:3*w//4]

        # Calcular gradiente
        gray = cv2.cvtColor(muestra, cv2.COLOR_BGR2GRAY)
        grad_x = cv2.Sobel(gray, cv2.CV_64F, 1, 0, ksize=3)
        grad_y = cv2.Sobel(gray, cv2.CV_64F, 0, 1, ksize=3)

        # Regularidad del gradiente (pantallas tienen patrón muy regular)
        grad_magnitude = np.sqrt(grad_x**2 + grad_y**2)
        std_grad = np.std(grad_magnitude)
        mean_grad = np.mean(grad_magnitude)

        if mean_grad == 0:
            return False

        cv_gradiente = std_grad / mean_grad  # Coeficiente de variación

        # Baja variación = patrón regular = sospechoso de pantalla
        return bool(cv_gradiente < 0.8 and mean_grad > 5)

    def _analizar_ruido(self, img: np.ndarray) -> bool:
        """
        Analiza si el ruido de la imagen es natural (cámara real).
        Las imágenes digitales puras tienen muy poco ruido.
        """
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY).astype(float)

        # Aplicar filtro gaussiano y calcular diferencia (= ruido)
        suavizada = cv2.GaussianBlur(gray, (5, 5), 0).astype(float)
        ruido = gray - suavizada

        std_ruido = np.std(ruido)

        # Ruido natural de cámara: std entre 1.5 y 8.0
        # Muy poco ruido (<1.5) = imagen digital o screenshot
        # Mucho ruido (>15) = imagen muy comprimida o modificada
        return bool(1.5 <= std_ruido <= 15.0)
