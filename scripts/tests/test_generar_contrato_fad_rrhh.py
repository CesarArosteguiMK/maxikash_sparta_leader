from __future__ import annotations

import importlib.util
import tempfile
import unittest
import zipfile
from pathlib import Path

from lxml import etree


ROOT = Path(__file__).resolve().parents[2]
SPEC = importlib.util.spec_from_file_location(
    "generar_contrato_fad_rrhh",
    ROOT / "scripts/generar_contrato_fad_rrhh.py",
)
GENERATOR = importlib.util.module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(GENERATOR)
NS = {"w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main"}


def candidate_data() -> dict:
    return {
        "full_name": "PERSONA CANDIDATA DE PRUEBA",
        "nationality": "MEXICANA",
        "sex": "FEMENINO",
        "gender": "MUJER",
        "age": 30,
        "marital_status": "SOLTERA",
        "rfc": "TEST000000XXX",
        "curp": "TEST000000MDFXXX00",
        "nss": "00000000000",
        "address": "DOMICILIO DE PRUEBA 123, C.P. 00000",
        "emergency_contacts": "CONTACTO DE PRUEBA - 5500000000",
        "clabe": "000000000000000000",
        "account_number": "0000000000",
        "bank": "BANCO DE PRUEBA",
        "position": "ANALISTA DE PRUEBA",
        "activities": ["actividad uno", "actividad dos", "actividad tres"],
        "salary": 20000.50,
        "salary_words": "VEINTE MIL",
        "salary_cents": 50,
        "start_date_text": "cinco de agosto de dos mil veintiséis",
        "signature_date_text": "cinco de agosto de dos mil veintiséis",
        "beneficiaries": [
            {"name": "BENEFICIARIO UNO", "relationship": "MADRE", "percentage": 60},
            {"name": "BENEFICIARIO DOS", "relationship": "PADRE", "percentage": 40},
        ],
    }


def employee_update_data() -> dict:
    data = candidate_data()
    data.update({
        "position": "COORDINADORA DE OPERACIONES",
        "activities": [
            "coordinar la operación diaria",
            "validar reportes de resultados",
            "dar seguimiento al equipo",
            "documentar incidencias",
            "proponer mejoras de proceso",
        ],
        "salary": 24500.75,
        "salary_words": "VEINTICUATRO MIL QUINIENTOS",
        "salary_cents": 75,
        "original_start_date_text": "diez de enero de dos mil veintitrés",
        "original_position": "ANALISTA DE OPERACIONES",
        "original_salary": 18000.25,
        "original_salary_words": "DIECIOCHO MIL",
        "original_salary_cents": 25,
    })
    return data


class ContractGeneratorTest(unittest.TestCase):
    def test_generates_supported_templates_without_example_markers(self) -> None:
        for code in ("AMIGO_GENERAL_NUEVO", "PENSIONAMAX_NUEVO", "AMIGO_ACTUALIZACION"):
            with self.subTest(template=code), tempfile.TemporaryDirectory() as directory:
                output = Path(directory) / "contrato.docx"
                data = employee_update_data() if code == "AMIGO_ACTUALIZACION" else candidate_data()
                GENERATOR.generate(code, data, output, demo=False)
                self.assertTrue(output.is_file())
                with zipfile.ZipFile(output) as package:
                    root = etree.fromstring(package.read("word/document.xml"))
                text = "".join(root.xpath(".//w:t/text()", namespaces=NS))
                self.assertIn("PERSONA CANDIDATA DE PRUEBA", text)
                expected_position = "COORDINADORA DE OPERACIONES" if code == "AMIGO_ACTUALIZACION" else "ANALISTA DE PRUEBA"
                self.assertIn(expected_position, text)
                self.assertIn("brutos", text)
                expected_cents = "75/100 M.N." if code == "AMIGO_ACTUALIZACION" else "50/100 M.N."
                self.assertIn(expected_cents, text)
                self.assertNotIn("ALEXIS CONRADO FITZMAURICE SOLIS", text)
                self.assertNotIn("COORDINADOR DE SEMINUEVAS", text)
                self.assertNotRegex(text, r"\*{3,}")
                self.assertFalse(root.xpath(".//w:highlight", namespaces=NS))
                self.assertFalse(root.xpath(".//w:ins|.//w:del", namespaces=NS))
                struck_text = []
                for run in root.xpath(".//w:r[w:rPr/w:strike or w:rPr/w:dstrike]", namespaces=NS):
                    marks = run.xpath("./w:rPr/w:strike|./w:rPr/w:dstrike", namespaces=NS)
                    active = any(
                        mark.get("{%s}val" % NS["w"], "true").lower() not in {"0", "false", "off", "none"}
                        for mark in marks
                    )
                    value = "".join(run.xpath(".//w:t/text()", namespaces=NS)).strip()
                    if active and value:
                        struck_text.append(value)
                self.assertFalse(struck_text)

                if code == "AMIGO_ACTUALIZACION":
                    self.assertIn("diez de enero de dos mil veintitrés", text)
                    self.assertIn("ANALISTA DE OPERACIONES", text)
                    self.assertIn("DIECIOCHO MIL PESOS 25/100 M.N.", text)
                    self.assertIn("proponer mejoras de proceso", text)

    def test_rejects_incomplete_contract_data(self) -> None:
        data = candidate_data()
        data["activities"] = []
        with tempfile.TemporaryDirectory() as directory:
            with self.assertRaisesRegex(RuntimeError, "Faltan datos contractuales"):
                GENERATOR.generate(
                    "AMIGO_GENERAL_NUEVO",
                    data,
                    Path(directory) / "contrato.docx",
                    demo=False,
                )


if __name__ == "__main__":
    unittest.main()
