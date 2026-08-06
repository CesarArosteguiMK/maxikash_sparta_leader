#!/usr/bin/env python3
"""Genera contratos FAD RRHH desde los machotes oficiales sin tocar el original."""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import shutil
import tempfile
import zipfile
from pathlib import Path

from lxml import etree


W_NS = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
NS = {"w": W_NS}
W = "{%s}" % W_NS
ROOT = Path(__file__).resolve().parents[1]

TEMPLATES = {
    "AMIGO_GENERAL_NUEVO": {
        "path": ROOT / "backend/storage/fad_rrhh_templates/amigo_general_nuevo.docx",
        "sha256": "4ebb3a6d9eba638cad8996ae99cbf063d6b8c0813fa6f4becdc0ef593c831399",
    },
    "PENSIONAMAX_NUEVO": {
        "path": ROOT / "backend/storage/fad_rrhh_templates/pensionamax_nuevo.docx",
        "sha256": "eaceeb2db9599a4ca3d16122748807bf27bda74df641a243fa5a0c04835dd847",
    },
    "AMIGO_ACTUALIZACION": {
        "path": ROOT / "backend/storage/fad_rrhh_templates/amigo_actualizacion.docx",
        "sha256": "70309f41fc5e88d035e85e2aa4669795aa5a4f7e3f1a30eb8f6b287bb04bd635",
    },
}


def sha256(path: Path) -> str:
    h = hashlib.sha256()
    with path.open("rb") as stream:
        for block in iter(lambda: stream.read(1024 * 1024), b""):
            h.update(block)
    return h.hexdigest()


def text_nodes(element: etree._Element) -> list[etree._Element]:
    return list(element.xpath(".//w:t", namespaces=NS))


def element_text(element: etree._Element) -> str:
    return "".join(node.text or "" for node in text_nodes(element))


def preserve_space(node: etree._Element) -> None:
    value = node.text or ""
    xml_space = "{http://www.w3.org/XML/1998/namespace}space"
    if value.startswith(" ") or value.endswith(" "):
        node.set(xml_space, "preserve")
    else:
        node.attrib.pop(xml_space, None)


def replace_across_nodes(element: etree._Element, old: str, new: str, required: bool = True) -> int:
    count = 0
    while True:
        nodes = text_nodes(element)
        full = "".join(node.text or "" for node in nodes)
        start = full.find(old)
        if start < 0:
            break
        end = start + len(old)
        cursor = 0
        first_index = last_index = None
        first_offset = last_offset = 0
        for index, node in enumerate(nodes):
            length = len(node.text or "")
            if first_index is None and start <= cursor + length:
                first_index = index
                first_offset = start - cursor
            if end <= cursor + length:
                last_index = index
                last_offset = end - cursor
                break
            cursor += length
        if first_index is None or last_index is None:
            raise RuntimeError(f"No se pudo reemplazar el marcador {old!r}.")
        first = nodes[first_index]
        last = nodes[last_index]
        first_value = first.text or ""
        last_value = last.text or ""
        if first_index == last_index:
            first.text = first_value[:first_offset] + new + first_value[last_offset:]
            preserve_space(first)
        else:
            first.text = first_value[:first_offset] + new
            preserve_space(first)
            for index in range(first_index + 1, last_index):
                nodes[index].text = ""
            last.text = last_value[last_offset:]
            preserve_space(last)
        count += 1
        break
    if required and count == 0:
        raise RuntimeError(f"No se encontró el marcador requerido {old!r}.")
    return count


def replace_whole_text(element: etree._Element, value: str) -> None:
    nodes = text_nodes(element)
    if not nodes:
        run = etree.SubElement(element, W + "r")
        node = etree.SubElement(run, W + "t")
        nodes = [node]
    nodes[0].text = value
    preserve_space(nodes[0])
    for node in nodes[1:]:
        node.text = ""


def accept_tracked_changes(root: etree._Element) -> None:
    for tag in ("del", "moveFrom"):
        for node in list(root.xpath(f".//w:{tag}", namespaces=NS)):
            parent = node.getparent()
            if parent is not None:
                parent.remove(node)
    for tag in ("ins", "moveTo"):
        for node in list(root.xpath(f".//w:{tag}", namespaces=NS)):
            parent = node.getparent()
            if parent is None:
                continue
            index = parent.index(node)
            for child in list(node):
                parent.insert(index, child)
                index += 1
            parent.remove(node)


def remove_struck_deletions(root: etree._Element) -> None:
    """Elimina texto que el machote conserva visiblemente tachado."""
    for run in list(root.xpath(".//w:r[w:rPr/w:strike or w:rPr/w:dstrike]", namespaces=NS)):
        marks = run.xpath("./w:rPr/w:strike|./w:rPr/w:dstrike", namespaces=NS)
        active = any(mark.get(W + "val", "true").lower() not in {"0", "false", "off", "none"} for mark in marks)
        if active:
            parent = run.getparent()
            if parent is not None:
                parent.remove(run)


def remove_highlights(root: etree._Element) -> None:
    for node in list(root.xpath(".//w:highlight", namespaces=NS)):
        parent = node.getparent()
        if parent is not None:
            parent.remove(node)


def body_paragraphs(root: etree._Element) -> list[etree._Element]:
    body = root.find("w:body", namespaces=NS)
    return list(body.findall("w:p", namespaces=NS)) if body is not None else []


def find_body_paragraph(root: etree._Element, needle: str) -> etree._Element:
    matches = [p for p in body_paragraphs(root) if needle in element_text(p)]
    if len(matches) != 1:
        raise RuntimeError(f"Se esperaban 1 y se encontraron {len(matches)} párrafos para {needle!r}.")
    return matches[0]


def set_table_value(root: etree._Element, label: str, value: str) -> None:
    matches = []
    for row in root.xpath(".//w:tbl/w:tr", namespaces=NS):
        cells = row.findall("w:tc", namespaces=NS)
        if len(cells) >= 2 and label in element_text(cells[0]):
            matches.append(cells[1])
    if len(matches) != 1:
        raise RuntimeError(f"No se pudo localizar de forma única el campo de tabla {label!r}.")
    paragraph = matches[0].find("w:p", namespaces=NS)
    if paragraph is None:
        paragraph = etree.SubElement(matches[0], W + "p")
    replace_whole_text(paragraph, value)


def set_worker_signature_name(root: etree._Element, value: str) -> None:
    tables = root.xpath(".//w:tbl", namespaces=NS)
    if not tables:
        raise RuntimeError("La plantilla no contiene tabla de firmas.")
    rows = tables[-1].findall("w:tr", namespaces=NS)
    cells = rows[-1].findall("w:tc", namespaces=NS)
    if len(cells) < 2:
        raise RuntimeError("La tabla final no contiene la celda del trabajador.")
    paragraphs = cells[1].findall("w:p", namespaces=NS)
    if not paragraphs:
        paragraph = etree.SubElement(cells[1], W + "p")
        paragraphs = [paragraph]
    replace_whole_text(paragraphs[0], "EL TRABAJADOR")
    if len(paragraphs) < 2:
        paragraphs.append(etree.SubElement(cells[1], W + "p"))
    replace_whole_text(paragraphs[1], value)
    # Algunos machotes conservan una segunda línea compuesta únicamente por
    # asteriscos. Es un marcador del ejemplo, no un dato contractual.
    for paragraph in paragraphs[2:]:
        if re.fullmatch(r"\s*\*{3,}\s*", element_text(paragraph)):
            replace_whole_text(paragraph, "")


def beneficiary_text(data: dict, count: int) -> str:
    values = list(data.get("beneficiaries") or [])[:count]
    if len(values) < count:
        raise RuntimeError(f"Se requieren {count} beneficiarios para esta plantilla.")
    if count == 1:
        item = values[0]
        return (
            f"5.- En términos del artículo 25 fracción X de la Ley Federal del Trabajo, “EL TRABAJADOR” "
            f"designa como beneficiario de sus derechos laborales a {item['name']}, quien es {item['relationship']}, "
            f"con un {item['percentage']}% de sus derechos laborales."
        )
    first, second = values
    return (
        f"5.- En términos del artículo 25 fracción X de la Ley Federal del Trabajo, “EL TRABAJADOR” "
        f"designa como beneficiarios de sus derechos laborales a {first['name']}, quien es su "
        f"{first['relationship']}, con un {first['percentage']}%, y a {second['name']}, quien es su "
        f"{second['relationship']}, con un {second['percentage']}% de sus derechos laborales."
    )


def fill_common_tables(root: etree._Element, data: dict, include_gender: bool) -> None:
    values = [
        ("SER DE NACIONALIDAD", data["nationality"]),
        ("SEXO", data["sex"]),
        ("EDAD", str(data["age"])),
        ("ESTADO CIVIL", data["marital_status"]),
        ("R.F.C.", data["rfc"]),
        ("CURP", data["curp"]),
        ("NÚMERO DE SEGURO SOCIAL IMSS", data["nss"]),
        ("DOMICILIO PARTICULAR", data["address"]),
        ("NÚMERO DE CONTACTOS DE EMERGENCIA", data["emergency_contacts"]),
    ]
    if include_gender:
        values.insert(2, ("GENERO", data["gender"]))
    for label, value in values:
        set_table_value(root, label, str(value))


def fill_amigo(root: etree._Element, data: dict) -> None:
    replace_across_nodes(find_body_paragraph(root, "POR LA OTRA PARTE EL C."), "*****************", data["full_name"])
    fill_common_tables(root, data, include_gender=True)
    replace_whole_text(find_body_paragraph(root, "designa como beneficiarios"), beneficiary_text(data, 2))
    replace_whole_text(
        find_body_paragraph(root, "autoriza que su salario sea pagado"),
        f"6.- “EL TRABAJADOR” autoriza que su salario sea pagado por transferencia electrónica a la CLABE "
        f"{data['clabe']}, cuenta {data['account_number']}, de {data['bank']}.",
    )
    temporal = find_body_paragraph(root, "TEMPORALIDAD DEL CONTRATO")
    paragraphs = body_paragraphs(root)
    index = paragraphs.index(temporal)
    clause = next(p for p in paragraphs[index + 1:index + 4] if "El presente contrato" in element_text(p))
    replace_across_nodes(clause, "COORDINADOR DE SEMINUEVAS", data["position"])
    replace_across_nodes(clause, "***** de **** de dos mil veintiséis", data["start_date_text"])
    position = find_body_paragraph(root, "teniendo como actividades principales")
    replace_across_nodes(position, "*******************", data["position"])
    replace_across_nodes(
        position,
        "teniendo como actividades principales las siguientes:",
        "teniendo como actividades principales las siguientes: " + "; ".join(data["activities"]) + ".",
    )
    salary = find_body_paragraph(root, "El salario mensual será por la cantidad")
    replace_whole_text(
        salary,
        f"11.- El salario mensual será por la cantidad de ${data['salary']:,.2f} "
        f"({data['salary_words']} PESOS {int(data.get('salary_cents', round((data['salary'] % 1) * 100))):02d}/100 M.N.) brutos, "
        "y se pagará conforme a lo establecido con anterioridad.",
    )
    replace_whole_text(
        find_body_paragraph(root, "se firma de forma electrónica"),
        "Ambas partes manifiestan que en el presente contrato no existe error, dolo, mala fe o vicio del consentimiento; "
        f"se firma electrónicamente para constancia y efectos legales el {data['signature_date_text']}.",
    )
    set_worker_signature_name(root, data["full_name"])


def fill_pensionamax(root: etree._Element, data: dict) -> None:
    intro = find_body_paragraph(root, "POR LA OTRA PARTE EL C.")
    replace_across_nodes(intro, "________________________", data["full_name"])
    fill_common_tables(root, data, include_gender=False)
    replace_whole_text(find_body_paragraph(root, "designa como beneficiarios"), beneficiary_text(data, 1))
    replace_whole_text(
        find_body_paragraph(root, "autoriza que su salario sea pagado"),
        f"6.- “EL TRABAJADOR” autoriza que su salario sea pagado por transferencia electrónica a la CLABE "
        f"{data['clabe']}, cuenta {data['account_number']}, de {data['bank']}.",
    )
    position = find_body_paragraph(root, "teniendo como actividades principales")
    replace_across_nodes(position, "_____________________", data["position"])
    paragraphs = body_paragraphs(root)
    blank_activity_paragraphs = [p for p in paragraphs if element_text(p).strip() == "_____________________________"]
    if len(blank_activity_paragraphs) != 3:
        raise RuntimeError("La plantilla Pensionamax no contiene las tres líneas de actividades esperadas.")
    for paragraph, activity in zip(blank_activity_paragraphs, data["activities"][:3]):
        replace_whole_text(paragraph, activity)
    salary = find_body_paragraph(root, "El salario mensual será por la cantidad")
    replace_whole_text(
        salary,
        f"12.- El salario mensual será por la cantidad de ${data['salary']:,.2f} "
        f"({data['salary_words']} PESOS {int(data.get('salary_cents', round((data['salary'] % 1) * 100))):02d}/100 M.N.) brutos, "
        "y se pagará conforme a lo establecido con anterioridad.",
    )
    replace_whole_text(
        find_body_paragraph(root, "para constancia y efectos legales"),
        "Ambas partes manifiestan que en el contrato no existe error, dolo, mala fe o vicio del consentimiento; "
        f"se firma electrónicamente para constancia y efectos legales el {data['signature_date_text']}.",
    )
    set_worker_signature_name(root, data["full_name"])


def fill_amigo_actualizacion(root: etree._Element, data: dict) -> None:
    replace_across_nodes(find_body_paragraph(root, "POR LA OTRA PARTE EL C."), "*****************", data["full_name"])
    fill_common_tables(root, data, include_gender=True)
    replace_whole_text(find_body_paragraph(root, "designa como beneficiarios"), beneficiary_text(data, 2))
    replace_whole_text(
        find_body_paragraph(root, "autoriza que su salario sea pagado"),
        f"6.- “EL TRABAJADOR” autoriza que su salario sea pagado por transferencia electrónica a la CLABE "
        f"{data['clabe']}, cuenta {data['account_number']}, de {data['bank']}.",
    )
    original_salary_cents = int(data.get("original_salary_cents", round((data["original_salary"] % 1) * 100)))
    replace_whole_text(
        find_body_paragraph(root, "reconoce la antigüedad"),
        f"8.- “EL PATRÓN” en este acto reconoce la antigüedad de “EL TRABAJADOR”, quien ingresó el "
        f"{data['original_start_date_text']}, con el puesto de {data['original_position']}, con un salario de "
        f"${data['original_salary']:,.2f} ({data['original_salary_words']} PESOS {original_salary_cents:02d}/100 M.N.), "
        f"y actualmente desempeña el puesto de {data['position']} con un salario de ${data['salary']:,.2f} "
        f"({data['salary_words']} PESOS {int(data.get('salary_cents', round((data['salary'] % 1) * 100))):02d}/100 M.N.).",
    )
    position = find_body_paragraph(root, "teniendo como actividades principales")
    replace_across_nodes(position, "*******************", data["position"])
    replace_across_nodes(
        position,
        "teniendo como actividades principales las siguientes: _________________________",
        "teniendo como actividades principales las siguientes: " + "; ".join(data["activities"]) + ".",
    )
    replace_whole_text(
        find_body_paragraph(root, "El salario mensual será por la cantidad"),
        f"11.- El salario mensual será por la cantidad de ${data['salary']:,.2f} "
        f"({data['salary_words']} PESOS {int(data.get('salary_cents', round((data['salary'] % 1) * 100))):02d}/100 M.N.) brutos, "
        "y se pagará conforme a lo establecido con anterioridad.",
    )
    replace_whole_text(
        find_body_paragraph(root, "se firma de forma electrónica"),
        "Ambas partes manifiestan que en el contrato no existe error, dolo, mala fe o algún vicio del consentimiento; "
        f"se firma electrónicamente para constancia y efectos legales el {data['signature_date_text']}.",
    )
    set_worker_signature_name(root, data["full_name"])


def add_demo_footer(root: etree._Element) -> None:
    paragraph = etree.SubElement(root, W + "p")
    ppr = etree.SubElement(paragraph, W + "pPr")
    etree.SubElement(ppr, W + "jc").set(W + "val", "center")
    run = etree.SubElement(paragraph, W + "r")
    rpr = etree.SubElement(run, W + "rPr")
    etree.SubElement(rpr, W + "b")
    etree.SubElement(rpr, W + "color").set(W + "val", "C00000")
    etree.SubElement(rpr, W + "sz").set(W + "val", "16")
    text = etree.SubElement(run, W + "t")
    text.text = "DATOS FICTICIOS - DOCUMENTO DE DEMOSTRACIÓN - SIN VALIDEZ"


def validate_data(data: dict, template_code: str) -> None:
    required = [
        "full_name", "nationality", "sex", "age", "marital_status", "rfc", "curp", "nss",
        "address", "emergency_contacts", "clabe", "account_number", "bank", "position", "activities",
        "salary", "salary_words", "start_date_text", "signature_date_text", "beneficiaries",
    ]
    missing = [key for key in required if data.get(key) in (None, "", [])]
    if missing:
        raise RuntimeError("Faltan datos contractuales: " + ", ".join(missing))
    if len(data["activities"]) < 3:
        raise RuntimeError("Se requieren al menos tres actividades del puesto.")
    if template_code == "AMIGO_ACTUALIZACION":
        update_required = ["original_start_date_text", "original_position", "original_salary", "original_salary_words"]
        update_missing = [key for key in update_required if data.get(key) in (None, "", [])]
        if update_missing:
            raise RuntimeError("Faltan datos originales del colaborador: " + ", ".join(update_missing))


def generate(template_code: str, data: dict, output: Path, demo: bool) -> dict:
    config = TEMPLATES.get(template_code)
    if not config:
        raise RuntimeError("Plantilla no soportada por el generador.")
    source = Path(config["path"])
    if sha256(source) != config["sha256"]:
        raise RuntimeError("El machote cambió; se requiere revisar nuevamente sus campos y formato.")
    validate_data(data, template_code)
    output.parent.mkdir(parents=True, exist_ok=True)

    with zipfile.ZipFile(source, "r") as source_zip, tempfile.TemporaryDirectory() as temp_dir:
        temp = Path(temp_dir)
        source_zip.extractall(temp)
        document_path = temp / "word/document.xml"
        parser = etree.XMLParser(remove_blank_text=False)
        root = etree.parse(str(document_path), parser).getroot()
        accept_tracked_changes(root)
        remove_struck_deletions(root)
        remove_highlights(root)
        if template_code == "AMIGO_GENERAL_NUEVO":
            fill_amigo(root, data)
        elif template_code == "PENSIONAMAX_NUEVO":
            fill_pensionamax(root, data)
        else:
            fill_amigo_actualizacion(root, data)
        document_path.write_bytes(etree.tostring(root, xml_declaration=True, encoding="UTF-8", standalone="yes"))

        if demo:
            footer_paths = sorted((temp / "word").glob("footer*.xml"))
            if not footer_paths:
                raise RuntimeError("El machote no contiene un pie de página para marcar la demostración.")
            # Word puede usar pies distintos para la primera página y para el
            # resto. Marcamos todos para que ninguna hoja de demo pueda
            # confundirse con un contrato válido.
            for footer_path in footer_paths:
                footer_root = etree.parse(str(footer_path), parser).getroot()
                add_demo_footer(footer_root)
                footer_path.write_bytes(
                    etree.tostring(footer_root, xml_declaration=True, encoding="UTF-8", standalone="yes")
                )

        with zipfile.ZipFile(output, "w", zipfile.ZIP_DEFLATED) as target_zip:
            for path in sorted(temp.rglob("*")):
                if path.is_file():
                    target_zip.write(path, path.relative_to(temp).as_posix())

    with zipfile.ZipFile(output, "r") as final_zip:
        final_xml = final_zip.read("word/document.xml").decode("utf-8", "ignore")
    plain = re.sub(r"<[^>]+>", "", final_xml)
    forbidden = [
        "COORDINADOR DE SEMINUEVAS",
        "$35,000.00",
        "quien ingresó el día ___",
        "con un salario de $_________________",
        "teniendo como actividades principales las siguientes: _________________________",
        "a los _______________ días",
    ]
    remaining = [value for value in forbidden if value in plain]
    if (remaining or re.search(r"\*{3,}", plain) or "w:highlight" in final_xml
            or re.search(r"<w:ins\b", final_xml)
            or re.search(r"<w:del\b", final_xml)):
        raise RuntimeError("El contrato conservó marcadores o revisiones: " + ", ".join(remaining))
    if data["full_name"] not in plain:
        raise RuntimeError("El nombre del candidato no quedó incorporado al contrato.")
    return {"output": str(output), "sha256": sha256(output), "template": template_code, "demo": demo}


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--template", required=True, choices=sorted(TEMPLATES))
    parser.add_argument("--data", required=True)
    parser.add_argument("--output", required=True)
    parser.add_argument("--demo", action="store_true")
    args = parser.parse_args()
    data = json.loads(Path(args.data).read_text(encoding="utf-8"))
    result = generate(args.template, data, Path(args.output).resolve(), args.demo)
    print(json.dumps(result, ensure_ascii=False))


if __name__ == "__main__":
    main()
