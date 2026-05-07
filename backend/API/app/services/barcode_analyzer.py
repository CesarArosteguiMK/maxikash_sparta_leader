# app/services/barcode_analyzer.py
"""CAPA 5: QR, barcode y PDF417 en documentos."""
from __future__ import annotations

import os
import shutil
import sys
from pathlib import Path

import numpy as np
import cv2
from loguru import logger


def _bootstrap_local_zbar_for_pyzbar() -> None:
    """Asegura DLLs de zbar dentro del proyecto (sin depender de System32).

    pyzbar en Windows intenta cargar libzbar/libiconv desde el cwd o desde su
    propia carpeta site-packages\\pyzbar. Para un despliegue portable en esta API,
    copiamos DLLs desde backend/API/tools/zbar/bin (o tools/) hacia la carpeta
    de pyzbar si faltan.
    """
    if sys.platform != "win32":
        return

    api_root = Path(__file__).resolve().parents[2]
    # Ubicacion canonical dentro del proyecto.
    project_zbar_dirs = [
        api_root / "tools" / "zbar" / "bin",
        api_root / "tools" / "zbar",
        api_root / "tools",
    ]

    try:
        import pyzbar as _pyzbar_pkg  # type: ignore
    except Exception:
        # Si pyzbar no esta instalado, este bootstrap no aplica.
        return

    pyzbar_dir = Path(getattr(_pyzbar_pkg, "__file__", "")).resolve().parent
    if not pyzbar_dir.is_dir():
        return

    # pyzbar espera estos nombres en Windows x64.
    required = ("libzbar-64.dll", "libiconv.dll")
    for dll_name in required:
        target = pyzbar_dir / dll_name
        if target.is_file():
            continue
        copied = False
        for src_dir in project_zbar_dirs:
            candidate = src_dir / dll_name
            if candidate.is_file():
                try:
                    shutil.copy2(str(candidate), str(target))
                    copied = True
                except Exception as ex:
                    logger.warning(f"No se pudo copiar {dll_name} a pyzbar: {ex}")
                break
        if not copied:
            logger.debug(
                f"DLL faltante para pyzbar ({dll_name}); "
                "buscada en tools/zbar/bin, tools/zbar y tools/."
            )


_bootstrap_local_zbar_for_pyzbar()

try:
    from pyzbar import pyzbar
    PYZBAR_AVAILABLE = True
except Exception:
    pyzbar = None
    PYZBAR_AVAILABLE = False
    logger.warning("pyzbar no disponible (DLLs faltantes). Barcode check deshabilitado.")

from app.models.schemas import CheckCodigos, TipoDocumento

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
    def analyze(self, image_bytes: bytes, tipo_doc: TipoDocumento) -> CheckCodigos:
        if not PYZBAR_AVAILABLE:
            return CheckCodigos(
                ok=True, alertas=["Barcode check deshabilitado (pyzbar no disponible)"], score=0.5
            )
        alertas = []
        score = 0.5
        try:
            img_cv = cv2.imdecode(np.frombuffer(image_bytes, np.uint8), cv2.IMREAD_COLOR)
            if img_cv is None:
                return CheckCodigos(ok=False, alertas=["Imagen no válida"], score=0.4)

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
                    url_oficial, es_valido = self._verificar_url_oficial(datos, tipo_doc)
                    if es_valido:
                        contenido_valido = True
                        score = 1.0
                        alertas.append(f"QR oficial verificado: {url_oficial}")
                    else:
                        alertas.append(f"QR no apunta a dominio oficial: {datos[:50]}")
                        score = 0.4
                elif tipo in ["CODE128", "CODE39", "EAN13", "EAN8", "UPCA"]:
                    barcode_detectado = True
                    if datos:
                        contenido_valido = True
                        score = min(score + 0.3, 1.0)
                        alertas.append("Código de barras detectado y legible")
                elif tipo == "PDF417":
                    pdf417_detectado = True
                    if datos:
                        contenido_valido = True
                        score = min(score + 0.4, 1.0)
                        alertas.append("PDF417 detectado (documentos INM)")

            if not codigos:
                if tipo_doc == TipoDocumento.INE_ANTERIOR:
                    alertas.append("INE anterior puede no tener QR/código")
                    score = 0.5
                else:
                    alertas.append("No se detectaron códigos QR ni barcode")
                    score = 0.35

            return CheckCodigos(
                ok=score >= 0.5,
                qr_detectado=qr_detectado,
                barcode_detectado=barcode_detectado,
                pdf417_detectado=pdf417_detectado,
                contenido_valido=contenido_valido,
                url_oficial=url_oficial,
                alertas=alertas,
                score=round(score, 3),
            )
        except Exception as e:
            logger.error(f"Error en BarcodeAnalyzer: {e}")
            return CheckCodigos(ok=False, alertas=[f"Error: {str(e)}"], score=0.4)

    def _detectar_todos_codigos(self, img: np.ndarray) -> list:
        codigos = list(pyzbar.decode(img))
        if not codigos:
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            codigos = list(pyzbar.decode(gray))
        if not codigos:
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            _, thresh = cv2.threshold(gray, 127, 255, cv2.THRESH_BINARY)
            codigos = list(pyzbar.decode(thresh))
        if not codigos and img.shape[1] < 1000:
            scale = 1000 / img.shape[1]
            img_scaled = cv2.resize(img, None, fx=scale, fy=scale)
            codigos = list(pyzbar.decode(img_scaled))
        vistos = set()
        return [c for c in codigos if (c.type, c.data) not in vistos and not vistos.add((c.type, c.data))]

    def _verificar_url_oficial(self, datos: str, tipo_doc: TipoDocumento) -> tuple:
        datos_lower = datos.lower()
        dominios = DOMINIOS_OFICIALES_INE if tipo_doc in [TipoDocumento.INE_NUEVA, TipoDocumento.INE_ANTERIOR] else DOMINIOS_OFICIALES_INM
        for dominio in dominios:
            if dominio in datos_lower:
                return datos, True
        if datos.startswith("http") or datos.startswith("https"):
            return datos, False
        return None, False
