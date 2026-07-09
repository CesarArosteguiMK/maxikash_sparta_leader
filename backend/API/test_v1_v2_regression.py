import pytest

from app.api import routes
from app.services.alibaba_document_ai import validate_quick_extracted


CANDIDATE = "MIGUEL ANGEL CORONA CRUZ"
CURP = "GOCA850612HDFMPL09"
RFC = "GOCA850612ABC"
NSS = "03239629730"


def _summary(key, detected_type, **values):
    previous = {
        "valido": True,
        "rechazado": False,
        "revision_manual": False,
        "tipo_documento_detectado": detected_type,
        **values,
    }
    pages = previous.pop("paginas_pdf", 1)
    return {
        "key": key,
        "tipo_documento": key,
        "archivo": f"{key}.pdf",
        "paginas_pdf": pages,
        "motor_ia": "alibaba",
        "modelo_ia": "qwen3.5-flash",
        "fuente_lectura": "motor_v2_prefinal",
        "validacion_previa": {**previous, "paginas_pdf": pages},
    }


def _consistent_documents(curp_document_without_curp=False):
    curp_values = {"nombre": CANDIDATE}
    if curp_document_without_curp:
        curp_values.update({
            "valido": False,
            "revision_manual": True,
            "mensaje": (
                "CURP detectado. Revisa: No se pudo leer la CURP completa, "
                "pero se leyo el nombre del documento."
            ),
        })
    else:
        curp_values["curp"] = CURP

    summaries = {
        "solicitud_interna": _summary(
            "solicitud_interna",
            "solicitud___SPARTA_SECRET_REDACTED__",
            nombre=CANDIDATE,
            curp=CURP,
            rfc=RFC,
            nss=NSS,
            paginas_pdf=2,
        ),
        "cv": _summary("cv", "cv", nombre=CANDIDATE),
        "acta_nacimiento": _summary(
            "acta_nacimiento",
            "acta_nacimiento",
            nombre=CANDIDATE,
            fecha_nacimiento="1985-06-12",
        ),
        "curp": _summary("curp", "curp", **curp_values),
        "identificacion_oficial": _summary(
            "identificacion_oficial",
            "ine",
            nombre=CANDIDATE,
            curp=CURP,
            fecha_vencimiento="2035-12-31",
            clave_elector="CRCRMG85061200H100",
        ),
        "comprobante_domicilio": _summary(
            "comprobante_domicilio",
            "comprobante_domicilio",
            domicilio="CALLE PRUEBA 123",
            fecha_emision="2026-07-01",
        ),
        "constancia_fiscal": _summary(
            "constancia_fiscal",
            "constancia_fiscal",
            nombre=CANDIDATE,
            curp=CURP,
            rfc=RFC,
            regimen_sueldos_salarios=True,
            regimen_fiscal="Sueldos y Salarios",
            paginas_pdf=2,
        ),
        "nss": _summary(
            "nss",
            "nss",
            nombre=CANDIDATE,
            curp=CURP,
            nss=NSS,
        ),
        "hoja_retencion": _summary(
            "hoja_retencion",
            "infonavit_fonacot",
            nombre=CANDIDATE,
        ),
        "__SPARTA_SECRET_REDACTED__": _summary(
            "__SPARTA_SECRET_REDACTED__",
            "__SPARTA_SECRET_REDACTED__",
            nombre=CANDIDATE,
            banco_detectado="BBVA",
            clabe="0__SPARTA_PASSWORD_REDACTED__01234567",
        ),
    }
    return [
        {
            "key": key,
            "label": key,
            "filename": f"{key}.pdf",
            "summary": summary,
        }
        for key, summary in summaries.items()
    ]


def test_ine_validity_range_uses_final_year():
    extracted = {
        "tipo_documento": "ine",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {
            "nombre_completo": "LOPEZ TENORIO ARNALDO",
            "vigencia": "2025 - 2035",
            "fecha_vencimiento": "2025-12-31",
        },
        "frente_reverso": {
            "frente_detectado": True,
            "reverso_detectado": True,
        },
        "observaciones": [],
    }

    result = validate_quick_extracted(extracted, "identificacion_oficial")

    assert result["aprobado"] is True
    assert extracted["campos"]["fecha_vencimiento"] == "2035-12-31"


def test_company_application_is_not_accepted_as_generic_cv():
    extracted = {
        "tipo_documento": "solicitud___SPARTA_SECRET_REDACTED__",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {"nombre_completo": CANDIDATE},
        "observaciones": ["SOLICITUD DE EMPLEO FURIAMOTOS"],
    }

    result = validate_quick_extracted(extracted, "cv")

    assert result["aprobado"] is False
    assert "se esperaba CV o solicitud de trabajo" in result["mensaje_usuario"]


def test_generic_application_is_accepted_as_cv():
    extracted = {
        "tipo_documento": "cv",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {"nombre_completo": CANDIDATE},
        "observaciones": [],
    }

    result = validate_quick_extracted(extracted, "cv")

    assert result["aprobado"] is True


@pytest.mark.asyncio
async def test_v2_runtime_failure_allows_v1_fallback(monkeypatch):
    class FailingAI:
        model = "qwen-test"

        @staticmethod
        def enabled():
            return True

        @staticmethod
        def quick_verify(*args, **kwargs):
            raise TimeoutError("simulated provider timeout")

    monkeypatch.setattr(routes, "_doc_ai_alibaba_activo", lambda: True)
    monkeypatch.setattr(routes, "_crear_alibaba_ai", lambda: FailingAI())
    monkeypatch.setattr(routes, "_doc_ai_quick_cache_read", lambda cache_key: None)

    result = await routes._validar_rapido_alibaba_o_none(
        b"fake-pdf",
        "documento.pdf",
        "cv",
        CANDIDATE,
    )

    assert result is None


def test_v1_can_rescue_pdf_text_when_v2_is_unavailable():
    import fitz

    pdf = fitz.open()
    page = pdf.new_page()
    page.insert_text(
        (72, 72),
        "CONSTANCIA DE CURP RENAPO\n"
        "CLAVE UNICA DE REGISTRO DE POBLACION\n"
        f"{CURP}\n"
        f"{CANDIDATE}",
    )
    pdf_bytes = pdf.tobytes()
    pdf.close()

    summary = routes._v2_structured_summary_from_pdf({
        "key": "curp",
        "label": "CURP",
        "filename": "curp.pdf",
        "bytes": pdf_bytes,
    })

    assert summary is not None
    assert summary["motor_ia"] == "motor_v1"
    assert summary["fuente_lectura"] == "motor_v1_pdf_text_ocr"
    assert summary["validacion_previa"]["curp"].startswith(CURP[:16])


def test_consistent_ten_document_expediente_is_approved():
    raw = routes._resultado_v2_reglas_expediente(
        _consistent_documents(),
        CANDIDATE,
    )
    result = routes._respuesta_alibaba_expediente(raw, CANDIDATE)

    assert len(result["documentos_analizados_v2"]) == 10
    assert result["dictamen_ia"] == "aprobado"
    assert result["todo_coincide"] is True
    assert result["checks_ok"] == result["checks_totales"]


def test_curp_document_name_can_be_supported_by_other_documents():
    raw = routes._resultado_v2_reglas_expediente(
        _consistent_documents(curp_document_without_curp=True),
        CANDIDATE,
    )
    result = routes._respuesta_alibaba_expediente(raw, CANDIDATE)

    assert len(result["documentos_analizados_v2"]) == 10
    assert result["dictamen_ia"] == "aprobado"
    assert result["documentos_analizados_v2"]["curp"]["estado"] == "coincide"


def test_internal_crosscheck_error_is_recoverable():
    result = routes._respuesta_v2_error_recuperable(
        _consistent_documents(),
        CANDIDATE,
        NameError("simulated internal error"),
        25,
        fase="cruce_reglas_local",
    )

    assert result["dictamen_ia"] == "requiere_revision"
    assert result["modo_verificacion"] == "v2_alibaba_crosscheck_error_recuperable"
    assert result["api_pendiente"] is True
    assert len(result["documentos_analizados_v2"]) == 10
