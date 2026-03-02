# app/services/metadata_analyzer.py
"""CAPA 1: Análisis de Metadatos. Detecta edición, screenshot, etc."""
import io
from PIL import Image
from PIL.ExifTags import TAGS
from loguru import logger
from app.models.schemas import CheckMetadatos

SOFTWARE_SOSPECHOSO = [
    "photoshop", "gimp", "paint", "paint.net", "inkscape",
    "affinity", "pixelmator", "canva", "illustrator",
    "adobe", "corel", "lightroom", "snapseed", "vsco",
    "facetune", "picsart", "retouche"
]
SOFTWARE_SCREENSHOT = [
    "screenshot", "snip", "snipping", "greenshot", "lightshot",
    "sharex", "gyazo", "nimbus", "monosnap", "grab"
]


class MetadataAnalyzer:
    def analyze(self, image_bytes: bytes) -> CheckMetadatos:
        alertas = []
        score = 1.0
        try:
            img = Image.open(io.BytesIO(image_bytes))
            exif_data = self._extract_exif(img)
            editor_detectado = None
            es_screenshot = False
            software = exif_data.get("Software", "")
            dispositivo = exif_data.get("Model", exif_data.get("Make", ""))
            fecha_creacion = exif_data.get("DateTime", exif_data.get("DateTimeOriginal", ""))
            if software:
                software_lower = software.lower()
                if any(s in software_lower for s in SOFTWARE_SCREENSHOT):
                    es_screenshot = True
                    editor_detectado = software
                    alertas.append(f"Software de captura de pantalla: {software}")
                    score -= 0.60
                elif any(s in software_lower for s in SOFTWARE_SOSPECHOSO):
                    editor_detectado = software
                    alertas.append(f"Imagen editada con: {software}")
                    score -= 0.45
            tiene_metadatos_camara = bool(
                exif_data.get("Make") or exif_data.get("Model") or
                exif_data.get("ExifIFD.LensModel") or exif_data.get("ExifIFD.FocalLength")
            )
            if tiene_metadatos_camara:
                score = min(1.0, score + 0.10)
            if not exif_data and not software:
                alertas.append("Imagen sin metadatos EXIF (posible edición o screenshot)")
                score -= 0.20
            if img.format == "PNG":
                alertas.append("Formato PNG - común en capturas de pantalla")
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
            return CheckMetadatos(ok=False, alertas=[f"Error: {str(e)}"], score=0.5)

    def _extract_exif(self, img: Image.Image) -> dict:
        try:
            exif_raw = img._getexif()
            if not exif_raw:
                return {}
            return {TAGS.get(tag, tag): value for tag, value in exif_raw.items() if tag in TAGS}
        except Exception:
            return {}
