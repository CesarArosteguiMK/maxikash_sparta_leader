# app/utils/curp_validator.py
"""
Validador de CURP (Clave Única de Registro de Población)
Implementa el algoritmo oficial de la SEGOB.
"""
import re
from typing import Tuple


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
    r'^[A-Z]{1}[AEIOU]{1}[A-Z]{2}'
    r'\d{2}'
    r'(0[1-9]|1[0-2])'
    r'(0[1-9]|[12]\d|3[01])'
    r'[HM]{1}'
    r'(AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)'
    r'[B-DF-HJ-NP-TV-Z]{3}'
    r'[A-Z0-9]{1}'
    r'[0-9]{1}'
    r'$'
)


def validar_curp(curp: str) -> Tuple[bool, str]:
    if not curp:
        return False, "CURP vacío"
    curp = curp.upper().strip()
    if len(curp) != 18:
        return False, f"CURP debe tener 18 caracteres, tiene {len(curp)}"
    if not PATRON_CURP.match(curp):
        return False, "CURP no coincide con el formato oficial"
    return True, "CURP válido"


def extraer_datos_curp(curp: str) -> dict:
    if len(curp) != 18:
        return {}
    meses = {
        "01": "Enero", "02": "Febrero", "03": "Marzo", "04": "Abril", "05": "Mayo", "06": "Junio",
        "07": "Julio", "08": "Agosto", "09": "Septiembre", "10": "Octubre", "11": "Noviembre", "12": "Diciembre"
    }
    estados = {
        "AS": "Aguascalientes", "BC": "Baja California", "BS": "Baja California Sur",
        "CC": "Campeche", "CL": "Colima", "CM": "Campeche", "CS": "Chiapas", "CH": "Chihuahua",
        "DF": "CDMX", "DG": "Durango", "GT": "Guanajuato", "GR": "Guerrero", "HG": "Hidalgo",
        "JC": "Jalisco", "MC": "Estado de México", "MN": "Michoacán", "MS": "Morelos",
        "NT": "Nayarit", "NL": "Nuevo León", "OC": "Oaxaca", "PL": "Puebla", "QT": "Querétaro",
        "QR": "Quintana Roo", "SP": "San Luis Potosí", "SL": "Sinaloa", "SR": "Sonora",
        "TC": "Tabasco", "TS": "Tamaulipas", "TL": "Tlaxcala", "VZ": "Veracruz", "YN": "Yucatán",
        "ZS": "Zacatecas", "NE": "Nacido en el extranjero"
    }
    año = curp[4:6]
    mes = curp[6:8]
    dia = curp[8:10]
    sexo_code = curp[10]
    estado_code = curp[11:13]
    año_completo = int(año)
    if 0 <= año_completo <= 24:
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
