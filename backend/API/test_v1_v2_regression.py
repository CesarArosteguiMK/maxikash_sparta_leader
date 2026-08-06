from io import BytesIO
from pathlib import Path

import pytest
from starlette.datastructures import UploadFile

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


@pytest.mark.asyncio
async def test_identification_quality_without_findings_has_no_manual_review_note(monkeypatch):
    class ForensicResult:
        calidad_foto = "ok"
        brillo_excesivo = False
        porcentaje_sobreexpuesto = 0
        borroso = False
        alertas = []

    class ForensicAnalyzer:
        @staticmethod
        def analyze(_image):
            return ForensicResult()

    class FakeVerificationService:
        forense_analyzer = ForensicAnalyzer()

    monkeypatch.setattr(routes, "pdf_paginas_a_png_bytes", lambda *_args, **_kwargs: [b"image"])
    monkeypatch.setattr(routes, "VerificacionService", FakeVerificationService)

    document = UploadFile(filename="identificacion.pdf", file=BytesIO(b"%PDF-1.4"))
    result = await routes.verificar_calidad_identificacion_pdf(document, api_key="test")

    assert result["aceptado"] is True
    assert result["notas"] == []


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
        "estado_cuenta": _summary(
            "estado_cuenta",
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


def test_hoja_retencion_accepts_signed_carta_no_adeudo():
    extracted = {
        "tipo_documento": "carta_no_adeudo",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {
            "nombre_completo": CANDIDATE,
            "nombre_y_firma_lleno": True,
            "firma_detectada": True,
        },
        "evidencia_insuficiente": False,
        "observaciones": [],
    }

    result = validate_quick_extracted(extracted, "hoja_retencion")

    assert result["aprobado"] is True
    assert result["errores"] == []


def test_signed_carta_without_name_reports_only_missing_name():
    extracted = {
        "tipo_documento": "carta_no_adeudo",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {
            "nombre_completo": None,
            "nombre_y_firma_lleno": False,
            "firma_detectada": True,
        },
        "evidencia_insuficiente": True,
        "observaciones": [],
    }

    result = validate_quick_extracted(extracted, "hoja_retencion")

    assert result["aprobado"] is False
    assert result["errores"] == ["La carta de no adeudo no tiene el nombre completo del candidato"]
    assert not any("no esta firmada" in error for error in result["errores"])
    assert not any("nombre y firma" in error for error in result["errores"])


def test_v2_carta_con_firma_sin_nombre_keeps_signature_as_present():
    out = {
        "tipo_detectado": "carta_no_adeudo",
        "nombre": None,
        "firma_detectada": True,
    }

    ok, severity, message = routes._v2_document_contribution(
        "hoja_retencion",
        "Hoja de retencion FONACOT o INFONAVIT",
        out,
    )

    assert ok is False
    assert severity == "aviso"
    assert "falta nombre completo" in message
    assert "falta firma" not in message
    assert "no se pudo confirmar la firma" not in message


def test_v2_crosscheck_does_not_report_missing_signature_when_present():
    documents = _consistent_documents()
    carta = next(doc for doc in documents if doc["key"] == "hoja_retencion")
    carta["summary"] = _summary(
        "hoja_retencion",
        "carta_no_adeudo",
        nombre=None,
        firma_detectada=True,
        nombre_y_firma_lleno=False,
        evidencia_insuficiente=True,
    )

    raw = routes._resultado_v2_reglas_expediente(documents, CANDIDATE)
    doc_carta = raw["analysis"]["documentos"]["hoja_retencion"]
    comparaciones_carta = [
        comparison
        for comparison in raw["analysis"]["comparaciones"]
        if comparison.get("documento_a") == "hoja_retencion"
    ]
    textos_carta = [
        str(doc_carta.get("mensaje") or ""),
        *(str(item) for item in doc_carta.get("observaciones") or []),
        *(str(item.get("mensaje") or "") for item in comparaciones_carta),
    ]

    assert any("falta el nombre completo del declarante" in texto for texto in textos_carta)
    assert not any("falta la firma" in texto for texto in textos_carta)
    assert not any("no se pudo confirmar la firma" in texto for texto in textos_carta)
    assert doc_carta["firma_detectada"] is True


def test_hoja_retencion_keeps_name_and_signature_as_critical_fields():
    extracted = {
        "tipo_documento": "carta_no_adeudo",
        "confianza_lectura": "alta",
        "calidad_imagen": "buena",
        "campos": {
            "nombre_completo": None,
            "nombre_y_firma_lleno": False,
            "firma_detectada": False,
        },
        "evidencia_insuficiente": True,
        "observaciones": [],
    }

    result = validate_quick_extracted(extracted, "hoja_retencion")

    assert result["aprobado"] is False
    assert any("nombre completo" in error for error in result["errores"])
    assert any("no esta firmada" in error for error in result["errores"])


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


def test_saved_precheck_page_count_wins_over_degraded_legacy_metadata():
    documents = _consistent_documents()
    solicitud = next(doc for doc in documents if doc["key"] == "solicitud_interna")
    solicitud["paginas_pdf"] = 1
    solicitud["summary"]["paginas_pdf"] = 1
    solicitud["summary"]["validacion_previa"]["paginas_pdf"] = 2

    raw = routes._resultado_v2_reglas_expediente(documents, CANDIDATE)
    result = routes._respuesta_alibaba_expediente(raw, CANDIDATE)

    assert result["documentos_analizados_v2"]["solicitud_interna"]["paginas_pdf"] == 2
    assert not any(
        comparison.get("etiqueta") == "Solicitud interna minimo 2 hojas"
        and comparison.get("coincide") is False
        for comparison in result["comparaciones_v2"]
    )


def test_real_pdf_page_tree_wins_over_all_stale_metadata():
    import fitz

    pdf = fitz.open()
    pdf.new_page()
    pdf.new_page()
    pdf_bytes = pdf.tobytes()
    pdf.close()

    count = routes._v2_pdf_page_count(
        {
            "filename": "solicitud_interna.pdf",
            "bytes": pdf_bytes,
            "paginas_pdf": 1,
        },
        {
            "paginas_pdf": 1,
            "validacion_previa": {"paginas_pdf": 1},
        },
    )

    assert count == 2


def test_real_one_page_internal_application_keeps_critical_alert():
    documents = _consistent_documents()
    solicitud = next(doc for doc in documents if doc["key"] == "solicitud_interna")
    solicitud["paginas_pdf"] = 1
    solicitud["summary"]["paginas_pdf"] = 1
    solicitud["summary"]["validacion_previa"]["paginas_pdf"] = 1

    raw = routes._resultado_v2_reglas_expediente(documents, CANDIDATE)
    result = routes._respuesta_alibaba_expediente(raw, CANDIDATE)

    assert result["documentos_analizados_v2"]["solicitud_interna"]["paginas_pdf"] == 1
    assert any(
        comparison.get("etiqueta") == "Solicitud interna minimo 2 hojas"
        and comparison.get("coincide") is False
        and comparison.get("severidad") == "critico"
        for comparison in result["comparaciones_v2"]
    )


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
    assert result["modo_verificacion"] == (
        f"v2_{routes._doc_ai_provider_tag()}_crosscheck_error_recuperable"
    )
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
def test_all_ten_types_start_with_compact_original_reading(
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

    assert call_sizes[0] == 1
    assert result["assisted_preprocessing_enabled"] is False
    assert result["assisted_initial_views"] == 0


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
    assert calls[0]["assisted_prompt"] is False
    assert calls[1]["assisted_prompt"] is True
    assert result["assisted_preprocessing_enabled"] is True
    assert result["assisted_retry_attempted"] is True
    assert result["assisted_retry_used"] is True
    assert result["assisted_views"] == 1
    assert result["extraction"]["campos"]["curp"] == CURP
    assert result["validation"]["aprobado"] is True
    assert result["usage"]["total_tokens"] == 20


def test_modal_reevaluation_preserves_useful_document_readings():
    controller_path = Path(__file__).resolve().parents[1] / "controllers" / "CapHum.php"
    source = controller_path.read_text(encoding="utf-8")
    start = source.index("private function encolarVerificacionDocumentalCandidato")
    end = source.index("private function psQuoteVerificacionDocumental", start)
    reevaluation_flow = source[start:end]

    assert "$tiposSubidos = range(1, 10)" not in reevaluation_flow
    assert "solo se revisarán nuevamente los documentos pendientes o dudosos" in reevaluation_flow
    assert "|| $motor === 'gemini'" in source
    assert "'nombre_titular'" in source
    assert "'domicilio', 'direccion'" in source
    assert "['key' => 'comprobante_domicilio', 'campo' => 'comprobante_domicilio'" in source
    assert "['key' => 'estado_cuenta', 'campo' => 'estado_cuenta'" in source


def test_canonical_estado_cuenta_is_included_in_crosscheck():
    documents = _consistent_documents()

    raw = routes._resultado_v2_reglas_expediente(documents, CANDIDATE)
    result = routes._respuesta_alibaba_expediente(raw, CANDIDATE)

    assert len(result["documentos_analizados_v2"]) == 10
    assert result["documentos_analizados_v2"]["estado_cuenta"]["estado"] == "coincide"
    assert any(
        comparison.get("documento_a") == "estado_cuenta"
        and comparison.get("etiqueta") == "Banco fisico aceptado"
        and comparison.get("coincide") is True
        for comparison in result["comparaciones_v2"]
    )


def test_internal_application_type_only_reading_is_rescued_again():
    document = {
        "key": "solicitud_interna",
        "summary": _summary(
            "solicitud_interna",
            "solicitud___SPARTA_SECRET_REDACTED__",
        ),
    }

    assert routes._v2_summary_needs_pdf_text_rescue(document) is True

    document["summary"]["validacion_previa"]["nombre"] = CANDIDATE
    assert routes._v2_summary_needs_pdf_text_rescue(document) is False


def test_ine_name_match_with_three_noisy_curp_chars_is_yellow_warning():
    documents = _consistent_documents()
    identity = next(doc for doc in documents if doc["key"] == "identificacion_oficial")
    identity["summary"]["validacion_previa"]["curp"] = "GOCA850612HDFMPX76"

    rules_result = routes._resultado_v2_reglas_expediente(documents, CANDIDATE)
    response = routes._respuesta_alibaba_expediente(rules_result, CANDIDATE)
    warning_comparisons = [
        comp
        for comp in response["comparaciones_v2"]
        if str(comp.get("severidad") or "").lower() == "aviso"
    ]

    assert response["dictamen_ia"] == "requiere_revision"
    assert response["checks_ok"] == response["checks_totales"] - 1
    assert warning_comparisons
    assert all(comp.get("severidad") == "aviso" for comp in warning_comparisons)
    assert any("MISMA PERSONA" in routes.normalize_text(comp.get("mensaje")) for comp in warning_comparisons)
    assert any(CANDIDATE in str(alerta) for alerta in response["alertas"])
    assert "NO SE DETECTARON DIFERENCIAS CRITICAS" in routes.normalize_text(response["resumen_ia"])


def test_document_observations_distinguish_yellow_warnings_from_red_alerts():
    controller_path = Path(__file__).resolve().parents[1] / "controllers" / "CapHum.php"
    source = controller_path.read_text(encoding="utf-8")

    assert "function esObservacionAvisoMotorV2" in source
    assert 'esAviso ? "text-warning fw-semibold" : "text-danger fw-semibold"' in source
