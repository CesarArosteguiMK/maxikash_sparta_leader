# app/utils/curp_validator.py
"""
Validador de CURP (Clave Única de Registro de Población)
Implementa el algoritmo oficial de la SEGOB / RENAPO.

Estructura de 18 posiciones:
  Pos 1-4:   Letras (iniciales del nombre)
  Pos 5-10:  Dígitos (fecha AAMMDD)
  Pos 11:    Sexo (H / M)
  Pos 12-13: Entidad federativa (2 letras)
  Pos 14-16: Consonantes internas (letras)
  Pos 17:    Homoclave – dígito (0-9) para nacidos <2000,
             letra (A-Z) para nacidos >=2000
  Pos 18:    Dígito verificador (0-9, puede ser A-Z en CURPs recientes)
"""
import re
from typing import Tuple, Optional

try:
    import fitz
    PYMUPDF_AVAILABLE = True
except ImportError:
    PYMUPDF_AVAILABLE = False


PALABRAS_INCONVENIENTES = [
    "BACA", "BAKA", "BUEI", "BUEY", "CACA", "CACO", "CAGA", "CAGO",
    "CAKA", "CAKO", "COGE", "COGI", "COJA", "COJE", "COJI", "COJO",
    "COLA", "CULO", "FALO", "FETO", "GETA", "GUEI", "GUEY", "JETA",
    "JOTO", "KACA", "KACO", "KAGA", "KAGO", "KAKA", "KAKO", "KOGE",
    "KOGI", "KOJA", "KOJE", "KOJI", "KOJO", "KOLA", "KULO", "LELO",
    "LOCA", "LOCO", "LOKA", "LOKO", "MAME", "MAMO", "MEAR", "MEAS",
    "MEON", "MIAR", "MION", "MOCO", "MOKO", "MULA", "MULO", "NACA",
    "NACO", "PEDA", "PEDO", "PENE", "PIPI", "PITO", "POPO", "PUTA",
    "PUTO", "QULO", "RATA", "ROBA", "ROBE", "ROBO", "RUIN", "SENO",
    "TETA", "VACA", "VAGA", "VAGO", "VAKA", "VUEI", "VUEY", "WUEI", "WUEY"
]

PATRON_CURP = re.compile(
    r'^[A-Z]{1}[AEIOU]{1}[A-Z]{2}'       # Pos 1-4: letras nombre
    r'\d{2}'                                # Pos 5-6: año
    r'(0[1-9]|1[0-2])'                     # Pos 7-8: mes
    r'(0[1-9]|[12]\d|3[01])'               # Pos 9-10: día
    r'[HM]{1}'                              # Pos 11: sexo
    r'(AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)'
    r'[B-DF-HJ-NP-TV-Z]{3}'               # Pos 14-16: consonantes internas
    r'[A-Z0-9]{1}'                          # Pos 17: homoclave (dígito o letra según siglo)
    r'[A-Z0-9]{1}'                          # Pos 18: dígito verificador
    r'$'
)

_UMBRAL_SIGLO_XXI = 30


def _es_nacido_2000_plus(curp: str) -> bool:
    """Determina si el CURP corresponde a alguien nacido en 2000+.
    Códigos de año 00-30 son ambiguos; la posición 17 (letra vs dígito)
    desambigua el siglo."""
    year_code = int(curp[4:6])
    if year_code > _UMBRAL_SIGLO_XXI:
        return False
    return curp[16].isalpha()


def validar_curp(curp: str) -> Tuple[bool, str]:
    """
    Valida un CURP mexicano (18 caracteres).

    Returns:
        Tuple[bool, str]: (es_valido, mensaje)
    """
    if not curp:
        return False, "CURP vacío"

    curp = curp.upper().strip()

    if len(curp) != 18:
        return False, f"CURP debe tener 18 caracteres, tiene {len(curp)}"

    if not PATRON_CURP.match(curp):
        return False, "CURP no coincide con el formato oficial"

    year_code = int(curp[4:6])
    if year_code > _UMBRAL_SIGLO_XXI:
        if not curp[16].isdigit():
            return False, (
                f"Posición 17 debe ser dígito para nacidos antes del 2000 "
                f"(año={year_code}, encontrado='{curp[16]}')"
            )
        if not curp[17].isdigit():
            return False, (
                f"Posición 18 debe ser dígito verificador numérico "
                f"(año={year_code}, encontrado='{curp[17]}')"
            )

    primeras_4 = curp[:4]
    if primeras_4 in PALABRAS_INCONVENIENTES:
        pass

    return True, "CURP válido"


def extraer_datos_curp(curp: str) -> dict:
    """Extrae datos demográficos del CURP."""
    if len(curp) != 18:
        return {}

    meses = {
        "01": "Enero", "02": "Febrero", "03": "Marzo",
        "04": "Abril", "05": "Mayo", "06": "Junio",
        "07": "Julio", "08": "Agosto", "09": "Septiembre",
        "10": "Octubre", "11": "Noviembre", "12": "Diciembre"
    }

    estados = {
        "AS": "Aguascalientes", "BC": "Baja California", "BS": "Baja California Sur",
        "CC": "Campeche", "CL": "Colima", "CM": "Campeche", "CS": "Chiapas",
        "CH": "Chihuahua", "DF": "CDMX", "DG": "Durango", "GT": "Guanajuato",
        "GR": "Guerrero", "HG": "Hidalgo", "JC": "Jalisco", "MC": "Estado de México",
        "MN": "Michoacán", "MS": "Morelos", "NT": "Nayarit", "NL": "Nuevo León",
        "OC": "Oaxaca", "PL": "Puebla", "QT": "Querétaro", "QR": "Quintana Roo",
        "SP": "San Luis Potosí", "SL": "Sinaloa", "SR": "Sonora", "TC": "Tabasco",
        "TS": "Tamaulipas", "TL": "Tlaxcala", "VZ": "Veracruz", "YN": "Yucatán",
        "ZS": "Zacatecas", "NE": "Nacido en el extranjero"
    }

    año = curp[4:6]
    mes = curp[6:8]
    dia = curp[8:10]
    sexo_code = curp[10]
    estado_code = curp[11:13]

    año_completo = int(año)
    if _es_nacido_2000_plus(curp):
        año_completo += 2000
    elif año_completo <= _UMBRAL_SIGLO_XXI:
        año_completo += 2000 if curp[16].isalpha() else 1900
    else:
        año_completo += 1900

    return {
        "fecha_nacimiento": f"{dia}/{mes}/{año_completo}",
        "mes_nacimiento": meses.get(mes, "Desconocido"),
        "año_nacimiento": año_completo,
        "sexo": "Masculino" if sexo_code == "H" else "Femenino",
        "estado_nacimiento": estados.get(estado_code, "Desconocido"),
    }


def extraer_curp_de_pdf(pdf_bytes: bytes) -> Optional[str]:
    """
    Extrae el primer CURP válido encontrado en un PDF (ej. constancia RENAPO CURP.pdf).
    Usa el texto extraído del PDF y busca candidatos de 18 caracteres que pasen validar_curp.
    Retorna el CURP como string o None si no se encuentra.
    """
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto = ""
        for page in doc:
            texto += page.get_text() + "\n"
        doc.close()
        texto = texto.upper().replace(" ", "").replace("\n", "").replace("\r", "")
        for m in re.finditer(r"[A-Z]{4}[A-Z0-9]{14}", texto):
            candidato = m.group()
            if len(candidato) == 18 and validar_curp(candidato)[0]:
                return candidato
        for m in re.finditer(r"[A-Z0-9]{18}", texto):
            candidato = m.group()
            if validar_curp(candidato)[0]:
                return candidato
        return None
    except Exception:
        return None
