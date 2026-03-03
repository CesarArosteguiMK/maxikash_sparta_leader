# app/services/metadata_analyzer.py
"""
CAPA 1: Análisis de Metadatos del archivo imagen.
Detecta si fue editada en Photoshop, si es screenshot, software sospechoso, etc.
"""
import io
from typing import Optional
from PIL import Image
from PIL.ExifTags import TAGS
import piexif
from loguru import logger

from app.models.schemas import CheckMetadatos


# Software sospechoso que indica edición
SOFTWARE_SOSPECHOSO = [
    "photoshop", "gimp", "paint", "paint.net", "inkscape",
    "affinity", "pixelmator", "canva", "illustrator",
    "adobe", "corel", "lightroom", "snapseed", "vsco",
    "facetune", "picsart", "retouche"
]

# Software de captura de pantalla
SOFTWARE_SCREENSHOT = [
    "screenshot", "snip", "snipping", "greenshot", "lightshot",
    "sharex", "gyazo", "nimbus", "monosnap", "grab"
]

# Software legítimo de cámaras y escáneres
SOFTWARE_LEGITIMO = [
    "canon", "nikon", "sony", "fujifilm", "olympus", "pentax",
    "scanner", "epson", "hp scan", "brother", "samsung",
    "apple", "android", "iphone", "pixel", "galaxy"
]


class MetadataAnalyzer:
    """Analiza metadatos EXIF y del archivo para detectar manipulación."""

    def analyze(self, image_bytes: bytes) -> CheckMetadatos:
        """
        Analiza los metadatos de la imagen.
        
        Args:
            image_bytes: Bytes de la imagen a analizar
            
        Returns:
            CheckMetadatos con el resultado del análisis
        """
        alertas = []
        score = 1.0  # Empezamos con score perfecto y descontamos

        try:
            img = Image.open(io.BytesIO(image_bytes))
            exif_data = self._extract_exif(img)

            editor_detectado = None
            es_screenshot = False
            software = exif_data.get("Software", "")
            dispositivo = exif_data.get("Model", exif_data.get("Make", ""))
            fecha_creacion = exif_data.get("DateTime", exif_data.get("DateTimeOriginal", ""))

            # ---- Verificar software ----
            if software:
                software_lower = software.lower()

                # ¿Es screenshot?
                if any(s in software_lower for s in SOFTWARE_SCREENSHOT):
                    es_screenshot = True
                    editor_detectado = software
                    alertas.append(f"⚠️ Software de captura de pantalla: {software}")
                    score -= 0.60  # Penalización fuerte

                # ¿Es editor de imágenes?
                elif any(s in software_lower for s in SOFTWARE_SOSPECHOSO):
                    editor_detectado = software
                    alertas.append(f"⚠️ Imagen editada con: {software}")
                    score -= 0.45

            # ---- Verificar si hay metadatos de cámara/escáner (buena señal) ----
            tiene_metadatos_camara = bool(
                exif_data.get("Make") or
                exif_data.get("Model") or
                exif_data.get("ExifIFD.LensModel") or
                exif_data.get("ExifIFD.FocalLength")
            )

            if tiene_metadatos_camara:
                score = min(1.0, score + 0.10)  # Bonus por tener datos de cámara

            # ---- Sin metadatos = sospechoso ----
            if not exif_data and not software:
                alertas.append("⚠️ Imagen sin metadatos EXIF (posible edición o screenshot)")
                score -= 0.20

            # ---- Verificar formato ----
            if img.format == "PNG":
                # PNG es común en screenshots
                alertas.append("ℹ️ Formato PNG - común en capturas de pantalla")
                score -= 0.10

            score = max(0.0, min(1.0, score))

            return CheckMetadatos(
                ok=score >= 0.5,
                editor_detectado=editor_detectado,
                es_screenshot=es_screenshot,
                software=software or None,
                fecha_creacion=fecha_creacion or None,
                dispositivo=dispositivo or None,
                alertas=alertas,
                score=round(score, 3)
            )

        except Exception as e:
            logger.error(f"Error en MetadataAnalyzer: {e}")
            return CheckMetadatos(
                ok=False,
                alertas=[f"Error procesando metadatos: {str(e)}"],
                score=0.5  # Score neutro en caso de error
            )

    def _extract_exif(self, img: Image.Image) -> dict:
        """Extrae datos EXIF de forma segura."""
        try:
            exif_raw = img._getexif()
            if not exif_raw:
                return {}
            return {
                TAGS.get(tag, tag): value
                for tag, value in exif_raw.items()
                if tag in TAGS
            }
        except Exception:
            return {}
