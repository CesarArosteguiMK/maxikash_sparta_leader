# tests/test_verificacion.py
"""
Tests del sistema de verificación.
Ejecutar con: pytest tests/ -v --cov=app
"""
import pytest
import httpx
from app.main import app
from app.utils.curp_validator import validar_curp, extraer_datos_curp
from app.utils.nss_validator import validar_nss, extraer_nss_de_pdf
from app.services.ocr_analyzer import OCRAnalyzer


# ============================================================
# TESTS DE NSS
# ============================================================

class TestNSSValidator:
    """Tests del validador de NSS."""

    def test_nss_valido_03239629730(self):
        """NSS de pruebas_OCR/NSS.pdf es válido."""
        es_valido, mensaje = validar_nss("03239629730")
        assert es_valido, f"NSS debería ser válido: {mensaje}"

    def test_nss_invalido_digito_verificador(self):
        """NSS con dígito verificador incorrecto."""
        es_valido, mensaje = validar_nss("__SPARTA_PASSWORD_REDACTED__01")
        assert not es_valido
        assert "Dígito verificador" in mensaje

    def test_nss_vacio(self):
        """NSS vacío."""
        es_valido, mensaje = validar_nss("")
        assert not es_valido

    def test_extraer_nss_de_pdf(self):
        """Extracción de NSS desde PDF (NSS.pdf)."""
        import os
        base = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
        path_pdf = os.path.join(base, "pruebas_OCR", "NSS.pdf")
        if not os.path.isfile(path_pdf):
            pytest.skip("pruebas_OCR/NSS.pdf no encontrado")
        with open(path_pdf, "rb") as f:
            pdf_bytes = f.read()
        nss = extraer_nss_de_pdf(pdf_bytes)
        assert nss == "03239629730", f"Esperado 03239629730, obtuvo {nss}"
        assert validar_nss(nss)[0]


# ============================================================
# TESTS DE CURP
# ============================================================

class TestCURPValidator:
    """Tests del validador de CURP."""

    def test_curp_valido(self):
        """CURP en formato correcto."""
        curp = "GOCA850612HDFMPL09"
        es_valido, mensaje = validar_curp(curp)
        assert es_valido, f"CURP debería ser válido: {mensaje}"

    def test_curp_longitud_incorrecta(self):
        """CURP muy corto."""
        es_valido, mensaje = validar_curp("GOCA850612")
        assert not es_valido
        assert "18 caracteres" in mensaje

    def test_curp_vacio(self):
        """CURP vacío."""
        es_valido, mensaje = validar_curp("")
        assert not es_valido

    def test_curp_extrae_datos(self):
        """Extracción correcta de datos del CURP."""
        curp = "GOCA850612HDFMPL09"
        datos = extraer_datos_curp(curp)
        assert datos["sexo"] == "Masculino"
        assert datos["año_nacimiento"] == 1985

    def test_curp_femenino(self):
        """CURP femenino."""
        curp = "MAGL900101MDFRMR09"
        datos = extraer_datos_curp(curp)
        assert datos["sexo"] == "Femenino"


# ============================================================
# TESTS DE EXTRACCIÓN CURP POR ETIQUETA (RESIDENCIA)
# ============================================================

class TestCURPEtiquetaResidencia:
    """Extracción robusta de CURP junto a etiqueta 'CURP' en documento residencia."""

    def test_curp_tras_etiqueta(self):
        """CURP justo después de 'CURP:'."""
        analyzer = OCRAnalyzer()
        texto = "RESIDENTE TEMPORAL\nCURP: GOLL960112HNENYZ09\nNUE: 0000002848625"
        resultado = analyzer._extraer_curp_cerca_de_etiqueta(texto.upper())
        assert resultado == "GOLL960112HNENYZ09"

    def test_curp_tras_etiqueta_con_o_por_cero(self):
        """CURP con O por 0 (OCR) se corrige."""
        analyzer = OCRAnalyzer()
        texto = "CURP GOLL96O112HNENYZ09"
        resultado = analyzer._extraer_curp_cerca_de_etiqueta(texto.upper())
        assert resultado == "GOLL960112HNENYZ09"

    def test_no_inventar_curp_sin_contexto(self):
        """Sin etiqueta CURP/RESIDENTE no se devuelve CURP (evitar MRZ)."""
        analyzer = OCRAnalyzer()
        texto = "9601123M2512196CUB<<<<<<<<<<<4\nGONZALEZ<LEYVA<<LAZARO"
        resultado = analyzer._extraer_curp_cerca_de_etiqueta(texto.upper())
        assert resultado is None


# ============================================================
# TESTS DE API
# ============================================================

@pytest.mark.asyncio
class TestAPIEndpoints:
    """Tests de los endpoints REST."""

    async def test_health_check(self):
        """El endpoint de health debe responder 200."""
        transport = httpx.ASGITransport(app=app)
        async with httpx.AsyncClient(transport=transport, base_url="http://testserver") as client:
            response = await client.get("/api/v1/health")
        assert response.status_code == 200
        assert response.json()["status"] == "ok"

    async def test_tipos_documento(self):
        """Endpoint de tipos de documento."""
        transport = httpx.ASGITransport(app=app)
        async with httpx.AsyncClient(transport=transport, base_url="http://testserver") as client:
            response = await client.get("/api/v1/tipos-documento")
        assert response.status_code == 200
        data = response.json()
        assert len(data["tipos_soportados"]) == 5

    async def test_verificar_sin_api_key(self):
        """Sin API key debe retornar 403."""
        transport = httpx.ASGITransport(app=app)
        async with httpx.AsyncClient(transport=transport, base_url="http://testserver") as client:
            response = await client.post("/api/v1/verificar")
        assert response.status_code == 403

    async def test_verificar_sin_imagen(self, api_headers):
        """Sin imagen debe retornar error."""
        transport = httpx.ASGITransport(app=app)
        async with httpx.AsyncClient(transport=transport, base_url="http://testserver") as client:
            response = await client.post(
                "/api/v1/verificar",
                headers=api_headers
            )
        assert response.status_code == 422  # Validation error


# ============================================================
# FIXTURES
# ============================================================

@pytest.fixture
def api_headers():
    """Headers con API key para tests."""
    from app.core.config import get_settings
    settings = get_settings()
    return {settings.api_key_header: settings.master_api_key}


@pytest.fixture
def imagen_test_bytes():
    """Imagen de prueba simple (cuadrado blanco)."""
    from PIL import Image
    import io
    img = Image.new("RGB", (856, 540), color=(255, 255, 255))
    buffer = io.BytesIO()
    img.save(buffer, "JPEG")
    return buffer.getvalue()
