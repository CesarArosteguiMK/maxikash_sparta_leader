#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Pruebas por consola de la verificación ligera (verificar-calidad).
Ejecutar desde backend/API:
  python prueba_verificar_calidad_consola.py
  python prueba_verificar_calidad_consola.py ruta/a/imagen.jpg   # opcional: imagen concreta
"""
import asyncio
import sys
import os

# Permitir importar app desde el directorio backend/API
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))


def prueba_servicio_directo():
    """Prueba VerificacionService.verificar_calidad sin levantar HTTP."""
    from PIL import Image
    import io
    from app.services.verificacion import VerificacionService

    print("=== Prueba 1: Imagen blanca (debe dar ok=False por brillo) ===")
    img = Image.new("RGB", (400, 300), color=(255, 255, 255))
    buf = io.BytesIO()
    img.save(buf, "JPEG")
    image_bytes = buf.getvalue()
    service = VerificacionService()
    result = asyncio.run(service.verificar_calidad(image_bytes))
    print(f"  ok: {result.ok}")
    print(f"  mensaje: {result.mensaje}")
    print(f"  alertas: {result.alertas}")
    assert not result.ok, "Imagen blanca debería rechazarse por brillo"
    print("  OK\n")

    print("=== Prueba 2: Imagen gris (estructura de respuesta) ===")
    img2 = Image.new("RGB", (400, 300), color=(120, 120, 120))
    buf2 = io.BytesIO()
    img2.save(buf2, "JPEG", quality=85)
    result2 = asyncio.run(service.verificar_calidad(buf2.getvalue()))
    print(f"  ok: {result2.ok}")
    print(f"  mensaje: {result2.mensaje}")
    print(f"  alertas: {result2.alertas}")
    print("  OK\n")


def prueba_con_imagen(ruta: str):
    """Prueba con un archivo de imagen dado."""
    if not os.path.isfile(ruta):
        print(f"Archivo no encontrado: {ruta}")
        return
    ext = os.path.splitext(ruta)[1].lower().lstrip(".")
    if ext not in ("jpg", "jpeg", "png", "webp", "tiff"):
        print("Solo se aceptan imágenes JPG, PNG, WEBP, TIFF.")
        return
    with open(ruta, "rb") as f:
        image_bytes = f.read()
    from app.services.verificacion import VerificacionService
    service = VerificacionService()
    print(f"=== Verificación calidad: {ruta} ({len(image_bytes)} bytes) ===")
    result = asyncio.run(service.verificar_calidad(image_bytes))
    print(f"  ok: {result.ok}")
    print(f"  mensaje: {result.mensaje}")
    print(f"  alertas: {result.alertas}")


def main():
    if len(sys.argv) > 1:
        prueba_con_imagen(sys.argv[1])
        return
    print("Pruebas internas verificar-calidad (servicio directo)\n")
    prueba_servicio_directo()
    print("Todas las pruebas de consola pasaron.")


if __name__ == "__main__":
    main()
