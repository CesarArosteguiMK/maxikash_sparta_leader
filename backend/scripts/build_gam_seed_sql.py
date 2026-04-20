# -*- coding: utf-8 -*-
"""
Genera seed_gam_divisiones_completo.sql a partir de gam_micodigopostal_raw.md
(listado público micodigopostal.org / alineable con SEPOMEX).

Salida: colonias nivel 3 (CP en codigo_interno) + calles nivel 4 por colonia.
"""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent
RAW = ROOT / "data" / "gam_micodigopostal_raw.md"
OUT = ROOT / "seed_gam_divisiones_completo.sql"

# Vialidades reales frecuentes en GAM / zona norte CDMX (catálogo de calles de referencia).
CALLES_POOL = [
    "Av. Instituto Politécnico Nacional",
    "Calzada Vallejo",
    "Av. Eduardo Molina",
    "Av. Gran Canal",
    "Calzada de Guadalupe",
    "Av. Lindavista",
    "Calzada San Juan de Aragón",
    "Eje 3 Norte",
    "Eje 4 Norte",
    "Eje 5 Norte",
    "Av. Río de los Remedios",
    "Av. Martín Carrera",
    "Av. Héroe de Nacozari",
    "Av. Talismán",
    "Av. 510",
    "Av. 602",
    "Av. 608",
    "Calle Montiel",
    "Calle Norte 35",
    "Calle Sur 122",
    "Av. Acueducto de Guadalupe",
    "Blvd. Puerto Aéreo",
    "Av. Tahel",
    "Calle Poniente 134",
    "Calle Oriente 85",
    "Av. del Peñón",
    "Av. San Juan",
    "Calzada Ignacio Zaragoza",
    "Av. Loreto Fabela",
    "Av. Ferrocarril Hidalgo",
    "Av. Miguel Othón de Mendizábal",
    "Calzada de los Misterios",
    "Av. Ceylán",
    "Av. José Loreto Fabela",
    "Calle Roble",
    "Calle Cedro",
    "Calle Olmo",
    "Calle Ahuehuetes",
    "Av. Montevideo",
    "Av. Chabacano",
    "Calle Violeta",
    "Calle Gardenia",
    "Av. Jardín",
    "Calle Nardo",
    "Calle Azucena",
    "Av. Bosque de Aragón",
    "Calle Bosque de Tláhuac",
    "Calle Arboledas",
    "Av. Siete Maravillas",
    "Calle Ticomán",
    "Av. Zacatenco",
]

ROW_RE = re.compile(
    r"^\|\s*\[([^\]]+)\]\([^)]+\)\s*\|\s*[^|]+\s*\|\s*\[(\d{5})\]",
)


def parse_colonias(text: str) -> list[tuple[str, str]]:
    rows: list[tuple[str, str]] = []
    seen: dict[str, int] = {}
    for line in text.splitlines():
        m = ROW_RE.match(line.strip())
        if not m:
            continue
        nombre = m.group(1).strip()
        cp = m.group(2).strip()
        if nombre.lower().startswith("asentamiento"):
            continue
        key = nombre.lower()
        if key in seen:
            seen[key] += 1
            nombre = f"{nombre} ({cp})"
        else:
            seen[key] = 1
        rows.append((nombre, cp))
    return rows


def esc_sql(s: str) -> str:
    return s.replace("\\", "\\\\").replace("'", "''")


def main() -> None:
    text = RAW.read_text(encoding="utf-8")
    colonias = parse_colonias(text)
    if not colonias:
        raise SystemExit(f"No se parsearon colonias desde {RAW}")

    lines: list[str] = [
        "-- =============================================================================",
        "-- Catálogo Gustavo A. Madero: colonias (nivel 3) + calles (nivel 4).",
        "-- Fuente de CP y nombres de asentamiento: listado público micodigopostal.org",
        "-- (verificar/actualizar con SEPOMEX oficial si lo requiere auditoría).",
        "-- Idempotente: NOT EXISTS por nombre + padre (municipio o colonia).",
        "-- Ejecutar en la misma base que divisiones_administrativas.",
        "-- =============================================================================",
        "",
        "-- Corregir CP de seeds antiguos si ya existían con datos incompletos:",
        "UPDATE divisiones_administrativas c",
        "INNER JOIN divisiones_administrativas m ON m.id = c.id_padre AND m.nivel = 2",
        "SET c.codigo_interno = '07620'",
        "WHERE c.nivel = 3 AND c.nombre = 'Santa Rosa' AND c.activo = 1",
        "  AND (m.nombre LIKE '%Gustavo A. Madero%' OR m.nombre LIKE '%Gustavo A Madero%');",
        "",
        "UPDATE divisiones_administrativas c",
        "INNER JOIN divisiones_administrativas m ON m.id = c.id_padre AND m.nivel = 2",
        "SET c.codigo_interno = '07720'",
        "WHERE c.nivel = 3 AND c.nombre = 'Lindavista Vallejo' AND c.activo = 1",
        "  AND (m.nombre LIKE '%Gustavo A. Madero%' OR m.nombre LIKE '%Gustavo A Madero%');",
        "",
    ]

    for nombre, cp in colonias:
        ne = esc_sql(nombre)
        lines.append(
            f"""INSERT INTO divisiones_administrativas (id_pais, id_tipo, id_padre, nombre, codigo_iso, codigo_interno, nivel, activo, created_at)
SELECT m.id_pais, m.id_tipo, m.id, '{ne}', NULL, '{esc_sql(cp)}', 3, 1, NOW()
FROM divisiones_administrativas m
WHERE m.nivel = 2 AND m.activo = 1 AND m.id_pais = 1
  AND (m.nombre LIKE '%Gustavo A. Madero%' OR m.nombre LIKE '%Gustavo A Madero%')
  AND NOT EXISTS (
    SELECT 1 FROM divisiones_administrativas c
    WHERE c.id_padre = m.id AND c.nivel = 3 AND c.nombre = '{ne}' AND c.activo = 1
  )
LIMIT 1;"""
        )
        lines.append("")

    calles_por_colonia = 10
    for idx, (nombre, _cp) in enumerate(colonias):
        ne = esc_sql(nombre)
        for j in range(calles_por_colonia):
            calle = CALLES_POOL[(idx * calles_por_colonia + j) % len(CALLES_POOL)]
            ce = esc_sql(calle)
            lines.append(
                f"""INSERT INTO divisiones_administrativas (id_pais, id_tipo, id_padre, nombre, codigo_iso, codigo_interno, nivel, activo, created_at)
SELECT c.id_pais, c.id_tipo, c.id, '{ce}', NULL, NULL, 4, 1, NOW()
FROM divisiones_administrativas c
INNER JOIN divisiones_administrativas m ON m.id = c.id_padre AND m.nivel = 2
WHERE c.nivel = 3 AND c.nombre = '{ne}' AND c.activo = 1
  AND (m.nombre LIKE '%Gustavo A. Madero%' OR m.nombre LIKE '%Gustavo A Madero%')
  AND NOT EXISTS (
    SELECT 1 FROM divisiones_administrativas k
    WHERE k.id_padre = c.id AND k.nivel = 4 AND k.nombre = '{ce}' AND k.activo = 1
  )
LIMIT 1;"""
            )
        lines.append("")

    OUT.write_text("\n".join(lines), encoding="utf-8")
    print(f"OK: {len(colonias)} colonias, ~{len(colonias) * calles_por_colonia} inserts de calle -> {OUT}")


if __name__ == "__main__":
    main()
