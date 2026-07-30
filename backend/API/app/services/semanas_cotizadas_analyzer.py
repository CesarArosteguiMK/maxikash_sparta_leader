"""Lectura local y gratuita de constancias de semanas cotizadas del IMSS."""

from __future__ import annotations

import re
import unicodedata
from typing import Any, Dict, List

import fitz

from app.services.document_crosscheck import (
    _texto_ocr_imagen_ligero,
    pdf_paginas_a_png_bytes,
)


def _normalizar(texto: str) -> str:
    texto = unicodedata.normalize("NFKD", texto or "")
    texto = "".join(char for char in texto if not unicodedata.combining(char))
    texto = texto.upper().replace("\x00", " ")
    return re.sub(r"[ \t]+", " ", texto)


def _valor_despues_etiqueta(texto: str, etiqueta: str) -> str:
    lineas = [linea.strip() for linea in texto.splitlines() if linea.strip()]
    for indice, linea in enumerate(lineas):
        if re.search(etiqueta, _normalizar(linea), re.IGNORECASE):
            resto = re.sub(etiqueta, "", _normalizar(linea), flags=re.IGNORECASE).strip(" :-")
            if resto:
                return resto
            if indice + 1 < len(lineas):
                return _normalizar(lineas[indice + 1]).strip(" :-")
    return ""


def _registros_desde_geometria(pdf_bytes: bytes) -> List[Dict[str, Any]]:
    registros: List[Dict[str, Any]] = []
    documento = fitz.open(stream=pdf_bytes, filetype="pdf")
    try:
        for pagina in documento:
            bloques = [
                {"x": float(b[0]), "y": float(b[1]), "texto": str(b[4] or "")}
                for b in pagina.get_text("blocks", sort=True)
                if str(b[4] or "").strip()
            ]
            anclas = [
                indice for indice, bloque in enumerate(bloques)
                if "NOMBRE DEL PATRON" in _normalizar(bloque["texto"])
            ]
            for posicion, indice_ancla in enumerate(anclas):
                ancla = bloques[indice_ancla]
                siguiente_y = (
                    bloques[anclas[posicion + 1]]["y"]
                    if posicion + 1 < len(anclas)
                    else float(pagina.rect.height)
                )
                zona = [
                    bloque for bloque in bloques
                    if bloque["y"] >= ancla["y"] - 1 and bloque["y"] < siguiente_y - 1
                ]
                nombre = _valor_despues_etiqueta(ancla["texto"], r"NOMBRE DEL PATRON")
                registro = ""
                for bloque in zona:
                    if "REGISTRO PATRONAL" in _normalizar(bloque["texto"]):
                        registro = _valor_despues_etiqueta(bloque["texto"], r"REGISTRO PATRONAL")
                        break

                fila_fechas = [
                    bloque for bloque in zona
                    if "FECHA DE ALTA" in _normalizar(bloque["texto"])
                    or "FECHA DE BAJA" in _normalizar(bloque["texto"])
                ]
                if not fila_fechas:
                    continue
                y_fecha = min(bloque["y"] for bloque in fila_fechas)
                celdas_fecha = sorted(
                    [
                        bloque for bloque in zona
                        if abs(bloque["y"] - y_fecha) <= 4
                    ],
                    key=lambda bloque: bloque["x"],
                )
                contenido_fila = " ".join(_normalizar(bloque["texto"]) for bloque in celdas_fecha)
                fechas = re.findall(r"\b\d{2}/\d{2}/\d{4}\b", contenido_fila)
                vigente = bool(re.search(r"\bVIGENTE\b", contenido_fila))
                alta = fechas[0] if fechas else ""
                baja = "Vigente" if vigente else (fechas[1] if len(fechas) > 1 else "")
                if not registro or (not baja and not vigente):
                    continue
                registros.append({
                    "nombre": nombre[:180],
                    "registro_patronal": registro[:30],
                    "fecha_alta": alta,
                    "fecha_baja": baja,
                    "vigente": vigente,
                })
    finally:
        documento.close()
    return registros


def _texto_pdf_rapido(pdf_bytes: bytes, max_paginas: int = 20) -> tuple[str, str]:
    """Extrae texto sin inicializar motores OCR pesados.

    Las constancias normales traen capa de texto. Para PDF de imagen se usa
    Tesseract/RapidOCR ligero en pocas páginas, suficiente para reconocer una
    constancia o una página de error sin exceder el timeout del worker.
    """
    documento = fitz.open(stream=pdf_bytes, filetype="pdf")
    try:
        texto_nativo = "\n".join(
            pagina.get_text()
            for indice, pagina in enumerate(documento)
            if indice < max_paginas
        )
    finally:
        documento.close()
    if texto_nativo.strip():
        return texto_nativo, "motor_v1_pdf_texto"

    textos_ocr: List[str] = []
    imagenes = pdf_paginas_a_png_bytes(pdf_bytes, dpi=170, max_paginas=3)
    for imagen in imagenes:
        texto = _texto_ocr_imagen_ligero(imagen)
        if texto and texto.strip():
            textos_ocr.append(texto.strip())
    return "\n".join(textos_ocr), "motor_v1_ocr_ligero"


def analizar_semanas_cotizadas(pdf_bytes: bytes) -> Dict[str, Any]:
    """Cuenta patrones vigentes a partir de los bloques oficiales del IMSS.

    Primero usa la capa de texto del PDF. RapidOCR/Tesseract solo interviene
    como respaldo cuando el archivo es una digitalización sin texto.
    """
    texto_original, fuente_lectura = _texto_pdf_rapido(pdf_bytes, max_paginas=20)
    texto = _normalizar(texto_original)
    es_constancia = (
        "CONSTANCIA DE SEMANAS COTIZADAS" in texto
        and "REGISTRO PATRONAL" in texto
        and "FECHA DE BAJA" in texto
    )
    if not es_constancia:
        texto_detectado = bool(texto.strip())
        es_error_portal = (
            ("ACCESS DENIED" in texto and "ERROR 17" in texto)
            or "THIS REQUEST WAS BLOCKED BY OUR SECURITY SERVICE" in texto
            or ("SERVICIOSDIGITALES.IMSS.GOB.MX" in texto and "POWERED BY IMPERVA" in texto)
        )
        return {
            "valido": False,
            "revision_manual": True,
            "patrones_vigentes": None,
            "patrones_historial": 0,
            "patrones": [],
            "motor_ia": "motor_v1",
            "fuente_lectura": fuente_lectura,
            "clasificacion": "documento_incorrecto" if texto_detectado else "revision_manual",
            "codigo_resultado": (
                "error_portal_imss"
                if es_error_portal
                else ("documento_no_reconocido" if texto_detectado else "imagen_sin_texto_legible")
            ),
            "mensaje": (
                "El PDF contiene una página de error del portal del IMSS, no la constancia de semanas cotizadas."
                if es_error_portal
                else (
                    "El archivo no corresponde a una constancia de semanas cotizadas del IMSS."
                    if texto_detectado
                    else "El PDF es una imagen y no se pudo leer con suficiente claridad; requiere revisión manual."
                )
            ),
        }

    patrones = _registros_desde_geometria(pdf_bytes)
    if not patrones:
        total_bloques = len(re.findall(r"NOMBRE DEL PATRON", texto))
        total_vigentes = len(re.findall(r"\bVIGENTE\b", texto))
        patrones = [
            {
                "nombre": "",
                "registro_patronal": "",
                "fecha_alta": "",
                "fecha_baja": "Vigente",
                "vigente": True,
            }
            for _ in range(min(total_bloques, total_vigentes))
        ]

    vigentes = [patron for patron in patrones if patron["vigente"]]
    return {
        "valido": bool(patrones),
        "revision_manual": not bool(patrones),
        "patrones_vigentes": len(vigentes) if patrones else None,
        "patrones_historial": len(patrones),
        "patrones": patrones,
        "motor_ia": "motor_v1",
        "fuente_lectura": fuente_lectura,
        "mensaje": (
            f"Motor V1 detectó {len(vigentes)} patrón vigente"
            + ("" if len(vigentes) == 1 else "es")
            + f" en {len(patrones)} registro"
            + ("" if len(patrones) == 1 else "s")
            + " patronal"
            + ("" if len(patrones) == 1 else "es")
            + "."
        ) if patrones else "La constancia requiere revisión manual.",
    }
