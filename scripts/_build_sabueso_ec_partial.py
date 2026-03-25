# -*- coding: utf-8 -*-
src = r"c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\views\sabueso_paneladmin.php"
out = r"c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\views\partials\sabueso_rastreo___SPARTA_SECRET_REDACTED___bundle.php"
with open(src, encoding="utf-8") as f:
    lines = f.readlines()
part_a = lines[0:796]
# Hasta </script> que cierra el IIFE (línea 2406 en sabueso_paneladmin); sin esto el HTML siguiente se parsea como JS.
part_b = lines[910:2406]
header = """<?php
/**
 * Mismo CSS + modales + JS inline inicial del panel Sabueso (rastreo), para Estado de cuenta.
 * El <script> grande (maps, APIs) va aparte vía Sabueso::getPaneladminScriptSoloConsultaParaEstadoCuenta() en el footer.
 */
if (!isset($panel_admin_modulo_urls_json) || $panel_admin_modulo_urls_json === '') {
    $urlsModPanelEc = [];
    foreach (\\Core\\TicketsPanelModuloHelper::MODULOS as $k => $m) {
        $urlsModPanelEc[$k] = $m['url'];
    }
    $panel_admin_modulo_urls_json = json_encode($urlsModPanelEc, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
$panel_admin_solo_consulta_credito = true;
$panel_admin_chromeless_embed = false;
?>
"""
with open(out, "w", encoding="utf-8") as w:
    w.write(header)
    w.writelines(part_a)
    w.writelines(part_b)
print("lines", len(part_a) + len(part_b), "->", out)
