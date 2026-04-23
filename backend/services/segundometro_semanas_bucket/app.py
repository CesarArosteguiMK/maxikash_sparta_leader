"""
Servicio ligero: «Primeros pagos — Histórico por semana» (equivalente a
PrimerosPagosHistoricoSegundometro en PHP): solo consultas agregadas sobre
Fecha_primer_vencimiento + SEMANA; no trae decenas de miles de filas.

Uso: pip install -r requirements.txt, copiar .env, python app.py
Prueba: http://127.0.0.1:5055/  — API: GET /api/primeros-pagos-historico
"""
from __future__ import annotations

import os
import sys

from dotenv import load_dotenv
from flask import Flask, jsonify, Response
from pp_histo import build_primeros_pagos_payload

load_dotenv()

app = Flask(__name__, static_folder=None)

PAGE_HTML = """
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>[Prueba] Primeros pagos histórico (agregado)</title>
  <style>
    body { font-family: system-ui, Segoe UI, sans-serif; margin: 1rem; background: #f5f5f5; }
    h1 { font-size: 1.1rem; }
    .descartable { background: #fff8e1; border: 1px solid #ffcc80; color: #5d4037; padding: 0.4rem 0.6rem; font-size: 0.8rem; margin: 0 0 0.6rem; }
    .ayuda { background: #e3f2fd; border: 1px solid #90caf9; color: #0d47a1; padding: 0.6rem; font-size: 0.82rem; line-height: 1.4; margin-bottom: 0.8rem; }
    .ayuda strong { display: block; margin-bottom: 0.25rem; }
    .err { color: #b71c1c; background: #ffebee; padding: 0.75rem; border-radius: 6px; }
    .cargando { color: #333; padding: 0.5rem; }
    table { border-collapse: collapse; width: 100%; max-width: 70rem; background: #fff; font-size: 0.82rem; margin-top: 0.4rem; }
    th, td { border: 1px solid #ccc; padding: 0.4rem 0.5rem; text-align: left; }
    th { background: #eceff1; }
    .n { text-align: right; font-variant-numeric: tabular-nums; }
    .meta { color: #555; font-size: 0.82rem; margin: 0.25rem 0; line-height: 1.4; }
    a { color: #1565c0; }
    h2 { font-size: 0.98rem; margin: 1.15rem 0 0.35rem; }
    .tag { font-size: 0.75rem; color: #333; }
    tr.fila-cobrado { background: #e8f5e9; }
  </style>
</head>
<body>
  <div class="descartable">Vista de prueba. El JSON con los mismos datos: <a id="apilink" href="/api/primeros-pagos-historico">/api/primeros-pagos-historico</a></div>
  <h1>Primeros pagos — Histórico (tabla de apoyo, no el sitio productivo)</h1>
  <div class="ayuda" id="leyenda">
    <strong>Cómo leer esto (resumen de una fracción de cartera, no 69.000 filas crudas):</strong>
    Te mostramos solo <strong>conteos</strong> por cruces. La columna «1» es la
    <strong>morosidad con la que nació</strong> el crédito (al primer vencimiento, bucket). La «2» es la
    <strong>mora al corte del lunes</strong> (Dias_mora_Lunes_* en la base). La «3»
    <strong>¿Cobrado? / mejora</strong> es «Sí» si al corte la etapa es <em>estrictamente mejor</em> que al nacimiento
    (igual que en el resumen de PHP: cobrado = corte &lt; nacimiento en severidad). Eso explica
    pares raros (p. ej. 1a7 a Current) como recuperaciones.
  </div>
  <p class="tag">Cartera mostrada: créditos con <code>Fecha_primer_vencimiento</code> en el criterio
    <code>lunes_cierre</code> o <code>martes_domingo</code> de esa semana (mismo criterio que en Sparta). El total
    de filas del snapshot (≈69k) <strong>no se calcula</strong> por defecto: haría lenta la carga. Activar
    con variable de entorno (ver <code>PP_HISTO_INCLUIR_CONTEO_ETIQUETA</code> en <code>.env.example</code>).
  </p>
  <div id="root" class="cargando">Cargando… (varias semanas en paralelo)</div>
  <script>
  fetch("/api/primeros-pagos-historico")
    .then(r => { if (!r.ok) throw new Error("HTTP " + r.status); return r.json(); })
    .then(d => {
      const el = document.getElementById("root");
      if (d.mensaje && (!d.semanas || d.semanas.length === 0)) {
        el.className = "";
        el.innerHTML = "<p>" + escapeHtml(d.mensaje) + "</p>";
        return;
      }
      if (d.error) { el.innerHTML = "<div class='err'>" + escapeHtml(d.mensaje || d.error) + "</div>"; return; }
      if (!d.semanas || !d.semanas.length) {
        el.className = "";
        el.innerHTML = "<p>Sin semanas.</p>";
        return;
      }
      if (d.parametros) {
        var t = (d.parametros.incluir_conteo_todas_filas_etiqueta) ? "ON" : "OFF";
        var w = d.parametros.workers_semanas_paralelo;
        var ley = document.getElementById("leyenda");
        if (ley) { ley.innerHTML += " <em style='color:#6d4c41'>(Hilos: " + w + ", conteo 69k: " + t + ")</em>"; }
      }
      let h = "";
      d.semanas.forEach(s => {
        h += "<h2>" + escapeHtml(s.semana) + "</h2>";
        h += "<p class='meta'>Créditos en <strong>esta</strong> cartera (Fecha_primer_vencimiento): " +
          "<strong>" + s.total_creditos_cartera_filtro_pv + "</strong>. Criterio de rango: <code>" + escapeHtml(s.criterio_fecha) + "</code>.</p>";
        h += "<p class='meta'><small>" + (s.ultima_fecha_hora_insert_muestra ? ("Última carga (muestra) en histórico: " + escapeHtml(s.ultima_fecha_hora_insert_muestra) + " · ") : "");
        h += (s.registros_todas_filas_por_semana_histo !== undefined) ? (typeof s.registros_todas_filas_por_semana_histo === "string" ? escapeHtml(s.registros_todas_filas_por_semana_histo) : ( "Filas en el excel/corte de esa <code>SEMANA</code> (toda la tabla, sin filtro de PV): " + s.registros_todas_filas_por_semana_histo) ) : "";
        h += "</small></p>";
        h += "<table><thead><tr><th>1) Moro. al inicio (nac.)</th><th>2) Mora corte lunes</th><th>3) Cobrado / mejora</th><th class='n'>Créditos</th></tr></thead><tbody>";
        (s.agregado_nacimiento_x_mora_lunes || []).forEach(p => {
          var c = p.cobrado_mejora_al_corte || "";
          var si = c.indexOf("Sí,") === 0;
          var rowClass = si ? " class='fila-cobrado'" : "";
          h += "<tr" + rowClass + "><td>" + escapeHtml(p.morosidad_al_nacimiento) + "</td><td>" + escapeHtml(p.mora_al_lunes_corte) + "</td><td>" + escapeHtml(p.cobrado_mejora_al_corte) + "</td><td class='n'>" + p.conteo_creditos + "</td></tr>";
        });
        h += "</tbody></table>";
      });
      el.className = "";
      el.innerHTML = h;
    })
    .catch(e => {
      document.getElementById("root").innerHTML = "<div class='err'>" + escapeHtml(e && e.message ? e.message : "Error al cargar") + "</div>";
    });
  function escapeHtml(s) { return (s+"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;"); }
  </script>
</body>
</html>
"""


@app.route("/")
def index():
    return Response(PAGE_HTML, mimetype="text/html; charset=utf-8")


@app.route("/api/primeros-pagos-historico", methods=["GET"])
def api_primeros_pagos_historico():
    try:
        return jsonify(build_primeros_pagos_payload())
    except Exception as e:
        return jsonify({"error": "db", "mensaje": str(e)}), 500


@app.route("/api/aggregates", methods=["GET"])
def api_aggregates_legacy():
    return api_primeros_pagos_historico()


@app.route("/healthz")
def healthz():
    return jsonify({"ok": True})


def main():
    port = int(os.environ.get("PORT", "5055"))
    print("Open http://127.0.0.1:" + str(port) + "/  (API: /api/primeros-pagos-historico)", file=sys.stderr)
    app.run(host="0.0.0.0", port=port, debug=False, use_reloader=False)


if __name__ == "__main__":
    main()
