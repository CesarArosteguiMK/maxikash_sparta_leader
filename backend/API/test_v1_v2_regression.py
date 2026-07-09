import pytest

from app.api import routes
from app.services import alibaba_document_ai as document_ai
from app.services.alibaba_document_ai import (
    AlibabaDocumentAI,
    RenderedPage,
    quick_assistance_reason,
    render_documento_assistido_generico,
    validate_quick_extracted,
)


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


def test_generic_assistance_builds_rotated_and_cropped_views():
    import io
    from PIL import Image, ImageDraw

    image = Image.new("RGB", (1200, 800), "white")
    draw = ImageDraw.Draw(image)
    draw.rectangle((160, 120, 1040, 680), outline="black", width=5)
    draw.text((230, 260), "DOCUMENTO DE PRUEBA", fill="black")
    draw.text((230, 340), CANDIDATE, fill="black")
    image = image.rotate(90, expand=True, fillcolor="white")
    buffer = io.BytesIO()
    image.save(buffer, format="PNG")

    views = render_documento_assistido_generico(
        buffer.getvalue(),
        "documento.png",
        150,
        "acta_nacimiento",
    )

    assert len(views) >= 6
    for view in views:
        assert isinstance(view, RenderedPage)
        assert view.data.startswith(b"\xff\xd8")


@pytest.mark.parametrize(
    "expected_type",
    [
        "solicitud___SPARTA_SECRET_REDACTED__",
        "cv",
        "acta_nacimiento",
        "curp",
        "identificacion_oficial",
        "comprobante_domicilio",
        "constancia_fiscal",
        "nss",
        "infonavit_fonacot",
        "__SPARTA_SECRET_REDACTED__",
    ],
)
def test_all_ten_document_types_can_request_assisted_reading(expected_type):
    extracted = {
        "tipo_documento": "desconocido",
        "confianza_lectura": "baja",
        "calidad_imagen": "mala",
        "campos": {},
        "evidencia_insuficiente": True,
    }
    validation = validate_quick_extracted(extracted, expected_type)

    assert quick_assistance_reason(extracted, validation, expected_type) is not None


@pytest.mark.parametrize(
    "expected_type,detected_type,fields,extra",
    [
        ("solicitud___SPARTA_SECRET_REDACTED__", "solicitud___SPARTA_SECRET_REDACTED__", {"nombre_completo": CANDIDATE}, {}),
        ("cv", "cv", {"nombre_completo": CANDIDATE}, {}),
        ("acta_nacimiento", "acta_nacimiento", {"nombre_completo": CANDIDATE}, {}),
        ("curp", "curp", {"nombre_completo": CANDIDATE, "curp": CURP, "fecha_emision": "2026-07-01"}, {}),
        (
            "identificacion_oficial",
            "ine",
            {"nombre_completo": CANDIDATE, "curp": CURP, "fecha_vencimiento": "2035-12-31"},
            {"frente_reverso": {"frente_detectado": True, "reverso_detectado": True}},
        ),
        (
            "comprobante_domicilio",
            "comprobante_domicilio",
            {"domicilio": "CALLE PRUEBA 123", "fecha_emision": "2026-07-01"},
            {},
        ),
        (
            "constancia_fiscal",
            "constancia_fiscal",
            {
                "rfc": RFC,
                "fecha_emision": "2026-07-01",
                "actividad_economica": "Asalariado",
                "regimen_fiscal": "Sueldos y Salarios",
            },
            {},
        ),
        ("nss", "nss", {"nombre_completo": CANDIDATE, "nss": NSS}, {}),
        ("infonavit_fonacot", "infonavit_fonacot", {"nombre_completo": CANDIDATE}, {}),
        (
            "__SPARTA_SECRET_REDACTED__",
            "__SPARTA_SECRET_REDACTED__",
            {"banco": "BBVA", "clabe": "0__SPARTA_PASSWORD_REDACTED__01234567", "titular_cuenta": CANDIDATE},
            {},
        ),
    ],
)
def test_all_ten_types_receive_assisted_views_on_first_call(
    monkeypatch,
    expected_type,
    detected_type,
    fields,
    extra,
):
    import copy

    extraction = {
        "tipo_documento": detected_type,
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": fields,
        "observaciones": [],
        **extra,
    }
    call_sizes = []
    ai = AlibabaDocumentAI(
        api_key="test",
        base_url="https://example.invalid/v1",
        model="qwen-test",
        timeout_seconds=35,
    )

    monkeypatch.setattr(
        document_ai,
        "render_input",
        lambda *args, **kwargs: ([RenderedPage(b"base-view")], 1),
    )
    monkeypatch.setattr(
        document_ai,
        "render_documento_assistido_generico",
        lambda *args, **kwargs: [RenderedPage(b"assisted-view")],
    )
    monkeypatch.setattr(document_ai, "render_identificacion_assistida", lambda *args, **kwargs: [])
    monkeypatch.setattr(document_ai, "render_solicitud_assistida", lambda *args, **kwargs: [])

    def fake_call(pages, prompt, deadline=None):
        call_sizes.append(len(pages))
        return copy.deepcopy(extraction), {}, "qwen-test", False

    monkeypatch.setattr(ai, "_call", fake_call)
    result = ai.quick_verify(b"document", "documento.pdf", expected_type, CANDIDATE)

    assert call_sizes[0] == 2
    assert result["assisted_preprocessing_enabled"] is True
    assert result["assisted_initial_views"] == 1


def test_clear_wrong_document_does_not_force_assisted_retry():
    extracted = {
        "tipo_documento": "solicitud___SPARTA_SECRET_REDACTED__",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {"nombre_completo": CANDIDATE},
        "observaciones": ["SOLICITUD DE EMPLEO FURIAMOTOS"],
    }
    validation = validate_quick_extracted(extracted, "cv")

    assert validation["aprobado"] is False
    assert quick_assistance_reason(extracted, validation, "cv") is None


def test_ine_without_curp_requests_assisted_retry():
    extracted = {
        "tipo_documento": "ine",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {"nombre_completo": "LOPEZ TENORIO ARNALDO"},
        "frente_reverso": {
            "frente_detectado": True,
            "reverso_detectado": True,
        },
        "observaciones": [],
    }
    validation = validate_quick_extracted(extracted, "identificacion_oficial")

    assert quick_assistance_reason(
        extracted,
        validation,
        "identificacion_oficial",
    ) == "falta_curp_identificacion"


def test_quick_verify_uses_better_assisted_reading(monkeypatch):
    import io
    from PIL import Image

    image = Image.new("RGB", (800, 600), "white")
    buffer = io.BytesIO()
    image.save(buffer, format="PNG")

    first = {
        "tipo_documento": "curp",
        "confianza_lectura": "media",
        "calidad_imagen": "regular",
        "campos": {"nombre_completo": CANDIDATE},
        "observaciones": ["No se pudo leer la CURP completa"],
    }
    assisted = {
        "tipo_documento": "curp",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {
            "nombre_completo": CANDIDATE,
            "curp": CURP,
            "fecha_emision": "2026-07-01",
        },
        "observaciones": [],
    }
    calls = []
    responses = [first, assisted]

    ai = AlibabaDocumentAI(
        api_key="test",
        base_url="https://example.invalid/v1",
        model="qwen-test",
        timeout_seconds=35,
    )

    def fake_call(pages, prompt, deadline=None):
        calls.append({"pages": len(pages), "assisted_prompt": "vistas del mismo archivo" in prompt})
        return responses.pop(0), {"total_tokens": 10}, "qwen-test", False

    monkeypatch.setattr(ai, "_call", fake_call)
    monkeypatch.setattr(
        document_ai,
        "render_documento_assistido_generico",
        lambda *args, **kwargs: [RenderedPage(b"assisted-view")],
    )

    result = ai.quick_verify(
        buffer.getvalue(),
        "curp.png",
        "curp",
        CANDIDATE,
    )

    assert len(calls) == 2
    assert calls[0]["assisted_prompt"] is True
    assert calls[1]["assisted_prompt"] is True
    assert result["assisted_preprocessing_enabled"] is True
    assert result["assisted_retry_attempted"] is True
    assert result["assisted_retry_used"] is True
    assert result["assisted_views"] == 1
    assert result["extraction"]["campos"]["curp"] == CURP
    assert result["validation"]["aprobado"] is True
    assert result["usage"]["total_tokens"] == 20
