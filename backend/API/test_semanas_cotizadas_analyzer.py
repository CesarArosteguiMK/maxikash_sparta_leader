import fitz

from app.services.semanas_cotizadas_analyzer import analizar_semanas_cotizadas


def _constancia(registros):
    doc = fitz.open()
    page = doc.new_page(width=612, height=792)
    page.insert_text((40, 35), "Constancia de Semanas Cotizadas en el IMSS")
    y = 70
    for indice, (nombre, registro, baja) in enumerate(registros, start=1):
        page.insert_text((40, y), f"Nombre del patron\n{nombre}")
        page.insert_text((40, y + 34), f"Registro Patronal\n{registro}")
        page.insert_text((40, y + 82), "Entidad federativa MEXICO")
        page.insert_text((40, y + 102), f"Fecha de alta\nFecha de baja\n0{indice}/01/2026")
        page.insert_text((315, y + 102), baja)
        page.insert_text((450, y + 102), "Salario Base de Cotizacion $ 500")
        y += 150
    contenido = doc.tobytes()
    doc.close()
    return contenido


def test_cuenta_solo_patrones_vigentes():
    resultado = analizar_semanas_cotizadas(_constancia([
        ("EMPRESA UNO", "A123456789", "Vigente"),
        ("EMPRESA DOS", "B123456789", "Vigente"),
        ("EMPLEO ANTERIOR", "C123456789", "31/12/2025"),
    ]))

    assert resultado["valido"] is True
    assert resultado["patrones_vigentes"] == 2
    assert resultado["patrones_historial"] == 3


def test_marca_pagina_error_portal_como_documento_incorrecto():
    doc = fitz.open()
    page = doc.new_page(width=612, height=792)
    page.insert_text((70, 120), "Access denied")
    page.insert_text((70, 150), "Error 17")
    page.insert_text((70, 180), "serviciosdigitales.imss.gob.mx")
    page.insert_text((70, 220), "This request was blocked by our security service")
    page.insert_text((70, 260), "Powered by imperva")
    contenido = doc.tobytes()
    doc.close()

    resultado = analizar_semanas_cotizadas(contenido)

    assert resultado["valido"] is False
    assert resultado["clasificacion"] == "documento_incorrecto"
    assert resultado["codigo_resultado"] == "error_portal_imss"
    assert resultado["patrones_vigentes"] is None
