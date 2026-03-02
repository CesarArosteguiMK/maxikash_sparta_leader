# app/utils/curp_validator.py
"""
Validador de CURP (Clave Única de Registro de Población)
Implementa el algoritmo oficial de la SEGOB.
"""
import re
from typing import Tuple


# Palabras inconvenientes que el algoritmo CURP reemplaza
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

# Patrón regex del CURP
PATRON_CURP = re.compile(
    r'^[A-Z]{1}[AEIOU]{1}[A-Z]{2}'   # Apellido paterno (4 chars)
    r'\d{2}'                            # Año nacimiento
    r'(0[1-9]|1[0-2])'                 # Mes nacimiento
    r'(0[1-9]|[12]\d|3[01])'           # Día nacimiento
    r'[HM]{1}'                          # Sexo
    r'(AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)'  # Estado
    r'[B-DF-HJ-NP-TV-Z]{3}'           # Consonantes internas
    r'[A-Z0-9]{1}'                      # Dígito verificador
    r'$'
)


def validar_curp(curp: str) -> Tuple[bool, str]:
    """
    Valida un CURP mexicano.
    
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

    # Verificar que las primeras 4 letras no sean palabra inconveniente
    primeras_4 = curp[:4]
    if primeras_4 in PALABRAS_INCONVENIENTES:
        # Esto es válido en CURP, el algoritmo las reemplaza con X en 2da posición
        # No es error, solo informativo
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

    # Determinar siglo
    año_completo = int(año)
    if año_completo >= 0 and año_completo <= 24:
        año_completo += 2000
    else:
        año_completo += 1900

    return {
        "fecha_nacimiento": f"{dia}/{mes}/{año_completo}",
        "mes_nacimiento": meses.get(mes, "Desconocido"),
        "año_nacimiento": año_completo,
        "sexo": "Masculino" if sexo_code == "H" else "Femenino",
        "estado_nacimiento": estados.get(estado_code, "Desconocido"),
    }
