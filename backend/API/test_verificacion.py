# tests/test_verificacion.py
"""
Tests del sistema de verificación.
Ejecutar con: pytest tests/ -v --cov=app
"""
import pytest
from httpx import AsyncClient
from app.main import app
from app.utils.curp_validator import validar_curp, extraer_datos_curp


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
# TESTS DE API
# ============================================================

@pytest.mark.asyncio
class TestAPIEndpoints:
    """Tests de los endpoints REST."""

    async def test_health_check(self):
        """El endpoint de health debe responder 200."""
        async with AsyncClient(app=app, base_url="http://test") as client:
            response = await client.get("/api/v1/health")
        assert response.status_code == 200
        assert response.json()["status"] == "ok"

    async def test_tipos_documento(self):
        """Endpoint de tipos de documento."""
        async with AsyncClient(app=app, base_url="http://test") as client:
            response = await client.get("/api/v1/tipos-documento")
        assert response.status_code == 200
        data = response.json()
        assert len(data["tipos_soportados"]) == 5

    async def test_verificar_sin_api_key(self):
        """Sin API key debe retornar 403."""
        async with AsyncClient(app=app, base_url="http://test") as client:
            response = await client.post("/api/v1/verificar")
        assert response.status_code == 403

    async def test_verificar_sin_imagen(self, api_headers):
        """Sin imagen debe retornar error."""
        async with AsyncClient(app=app, base_url="http://test") as client:
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
